<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiNewsAssistantService
{
    /**
     * Generate structured news article draft from brief topic or bullet points
     */
    public function generateArticle(array $params): array
    {
        $topic = trim($params['topic'] ?? $params['prompt'] ?? '');
        $category = $params['category'] ?? 'Umum';
        $tone = $params['tone'] ?? 'jurnalistik';
        $length = $params['length'] ?? 'standar';
        $customKey = trim($params['api_key'] ?? '');
        $provider = $params['provider'] ?? config('services.ai.provider', 'gemini');

        if (empty($topic)) {
            return [
                'success' => false,
                'message' => 'Topik atau poin berita wajib diisi.',
            ];
        }

        $apiKey = !empty($customKey) ? $customKey : config('services.ai.key', env('AI_API_KEY', env('GEMINI_API_KEY', '')));

        // If API Key is available, invoke AI provider via native Laravel Http
        if (!empty($apiKey)) {
            try {
                if ($provider === 'gemini') {
                    $result = $this->callGeminiGenerate($topic, $category, $tone, $length, $apiKey);
                    if ($result['success']) {
                        return $result;
                    }
                } elseif (in_array($provider, ['openai', 'groq', 'openrouter'])) {
                    $result = $this->callOpenAiCompatibleGenerate($topic, $category, $tone, $length, $apiKey, $provider);
                    if ($result['success']) {
                        return $result;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('AI Generation error, falling back to smart heuristic: ' . $e->getMessage());
            }
        }

        // Fallback: Generate smart heuristic draft based on MKT humanitarian news templates
        return $this->generateSmartFallback($topic, $category, $tone, $length, empty($apiKey));
    }

    /**
     * Polish, expand, or summarize existing article content
     */
    public function enhanceContent(array $params): array
    {
        $content = trim($params['content'] ?? '');
        $action = $params['action'] ?? 'polish'; // polish, expand, shorten, humanize
        $customKey = trim($params['api_key'] ?? '');
        $provider = $params['provider'] ?? config('services.ai.provider', 'gemini');

        if (empty($content)) {
            return [
                'success' => false,
                'message' => 'Konten berita yang akan disempurnakan tidak boleh kosong.',
            ];
        }

        $apiKey = !empty($customKey) ? $customKey : config('services.ai.key', env('AI_API_KEY', env('GEMINI_API_KEY', '')));

        if (!empty($apiKey)) {
            try {
                if ($provider === 'gemini') {
                    return $this->callGeminiEnhance($content, $action, $apiKey);
                } else {
                    return $this->callOpenAiCompatibleEnhance($content, $action, $apiKey, $provider);
                }
            } catch (\Throwable $e) {
                Log::warning('AI Enhance error: ' . $e->getMessage());
            }
        }

        return $this->fallbackEnhance($content, $action);
    }

    /**
     * Suggest headline variations based on article content
     */
    public function suggestHeadlines(array $params): array
    {
        $content = trim($params['content'] ?? $params['topic'] ?? '');
        $customKey = trim($params['api_key'] ?? '');
        $provider = $params['provider'] ?? config('services.ai.provider', 'gemini');

        if (empty($content)) {
            return [
                'success' => false,
                'message' => 'Konten atau topik berita wajib diisi.',
            ];
        }

        $apiKey = !empty($customKey) ? $customKey : config('services.ai.key', env('AI_API_KEY', env('GEMINI_API_KEY', '')));

        if (!empty($apiKey)) {
            try {
                $prompt = "Sebagai redaktur berita kemanusiaan Yayasan MKT, berikan 5 alternatif judul berita yang kuat, beretika jurnalistik, menarik, dan informatif berdasarkan teks berikut:\n\n\"\"\"\n{$content}\n\"\"\"\n\nKeluarkan HANYA format JSON valid berikut:\n{\n  \"headlines\": [\n    \"Judul 1 (Format Berita Lugas & Informatif)\",\n    \"Judul 2 (Format Human Interest & Menggugah Empati)\",\n    \"Judul 3 (Format Aksi Lapangan / Sigap SAR)\",\n    \"Judul 4 (Format Gotong Royong / Kolaborasi)\",\n    \"Judul 5 (Format Ringkas / Padat)\"\n  ]\n}";

                if ($provider === 'gemini') {
                    $res = $this->rawGeminiRequest($prompt, $apiKey, true);
                    if (!empty($res['headlines'])) {
                        return [
                            'success' => true,
                            'headlines' => $res['headlines'],
                            'source' => 'gemini',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('AI Headline error: ' . $e->getMessage());
            }
        }

        // Heuristic fallback headlines
        $cleanTopic = Str::limit(strip_tags($content), 60);
        return [
            'success' => true,
            'source' => 'smart_template',
            'headlines' => [
                "Sigap Kemanusiaan: MKT Terjun Langsung dalam {$cleanTopic}",
                "Aksi Cepat Relawan MKT: Tanggap Bencana dan Solidaritas untuk {$cleanTopic}",
                "Kolaborasi Tangguh: Yayasan MKT Salurkan Bantuan Darurat Terkait {$cleanTopic}",
                "Sentuhan Kemanusiaan di Garis Depan: Respon Siaga MKT Menghadapi {$cleanTopic}",
                "MKT Peduli: Mempererat Gotong Royong Publik Demi Penanganan {$cleanTopic}",
            ],
            'notice' => empty($apiKey) ? 'Menggunakan variasi judul berbasis template cerdas MKT. Hubungkan Gemini API Key untuk rekomendasi berbasis AI generatif penuh.' : null,
        ];
    }

    /**
     * Call Google Gemini API to generate structured article
     */
    protected function callGeminiGenerate(string $topic, string $category, string $tone, string $length, string $apiKey): array
    {
        $model = config('services.ai.model', env('AI_MODEL', 'gemini-1.5-flash'));
        
        $lengthGuideline = match($length) {
            'singkat' => 'Tulis artikel ringkas sekitar 2 hingga 3 paragraf padat.',
            'panjang' => 'Tulis artikel mendalam sekitar 5 hingga 7 paragraf lengkap dengan detail situasi lapangan, kutipan relawan, dan dampak bantuan.',
            default => 'Tulis artikel standar sekitar 4 hingga 5 paragraf berimbang dengan kaidah 5W+1H yang lengkap.',
        };

        $toneGuideline = match($tone) {
            'darurat' => 'Gaya bahasa cepat, siaga, lugas, mengabarkan peristiwa bencana darurat dan respon cepat SAR.',
            'humanis' => 'Gaya bahasa menyentuh hati, menonjolkan sisi perjuangan warga, kepedulian antar-manusia, dan harapan.',
            'edukatif' => 'Gaya bahasa mendidik, informatif mengenai langkah mitigasi bencana atau pentingnya donor darah.',
            default => 'Gaya bahasa jurnalistik resmi, netral, berempati, dan akurat mencerminkan etika lembaga kemanusiaan terpercaya.',
        };

        $prompt = <<<PROMPT
Anda adalah Kepala Redaksi & Humas Lembaga Kemanusiaan Yayasan Mitra Kemanusiaan Terpadu (Yayasan MKT Indonesia - mkt.or.id).
MKT adalah yayasan amal sosial kemanusiaan yang berfokus pada penghimpunan donasi publik transparan, respon tanggap bencana alam/musibah SAR, pergerakan relawan donor darah, serta penyaluran logistik darurat ke wilayah terdampak.

TUGAS ANDA:
Buatkan draf berita/artikel yang siap terbit berdasarkan topik/poin berikut:
"{$topic}"

PANDUAN PENULISAN:
- Kategori yang diutamakan: {$category}
- Nada tulisan: {$toneGuideline}
- Panjang tulisan: {$lengthGuideline}
- Bahasa: Bahasa Indonesia yang baik, baku, santun, dan mudah dipahami (sesuai PUEBI/KBBI).
- Masukkan unsur 5W+1H (Apa peristiwanya, Siapa yang terlibat/terdampak, Dimana lokasinya, Kapan terjadinya, Mengapa terjadi, Bagaimana aksi kemanusiaan dijalankan).
- Sertakan kutipan naratif dari Koordinator Lapangan atau Ketua Pengurus MKT (misal: Salman Hasmin, ST atau Tim Pusdalops MKT).
- Pada paragraf terakhir, sertakan call-to-action (ajakan doa/dukungan donasi kemanusiaan MKT atau informasi kontak posko darurat).
- Paragraf dipisahkan dengan baris ganda (\\n\\n). Hindari penggunaan tag HTML raw seperti <div> atau <span>.

OUTPUT WAJIB DALAM FORMAT JSON VALID:
{
  "title": "Judul berita menarik, jelas, berbobot (maksimal 16 kata)",
  "category": "{$category}",
  "summary": "Ringkasan padat 1-2 kalimat untuk preview sosial media dan kartu berita",
  "content": "Isi narasi berita lengkap beberapa paragraf...",
  "tags": ["Tag1", "Tag2", "Tag3", "MKT Peduli"]
}
PROMPT;

        $response = $this->rawGeminiRequest($prompt, $apiKey, true, $model);

        if (empty($response['title']) || empty($response['content'])) {
            throw new \Exception('Respon Gemini tidak memiliki format struktur artikel yang valid.');
        }

        return [
            'success' => true,
            'source' => 'gemini',
            'title' => trim($response['title']),
            'category' => $response['category'] ?? $category,
            'summary' => trim($response['summary'] ?? ''),
            'content' => trim($response['content']),
            'tags' => $response['tags'] ?? ['MKT Peduli', $category],
        ];
    }

    /**
     * Call Google Gemini API to enhance text
     */
    protected function callGeminiEnhance(string $content, string $action, string $apiKey): array
    {
        $actionPrompt = match($action) {
            'expand' => 'Kembangkan teks berita ini menjadi lebih lengkap dan kaya rincian kronologi, kondisi lapangan, serta dampak positif bantuan kemanusiaan tanpa melebih-lebihkan fakta.',
            'shorten' => 'Ringkas teks berita ini menjadi 2-3 paragraf yang sangat padat, lugas, dan tetap mempertahankan inti informasi 5W+1H.',
            'humanize' => 'Poles teks berita ini agar memiliki rasa empati kemanusiaan yang lebih mendalam, menyentuh hati para donatur dan masyarakat pembaca, serta menekankan nilai gotong royong.',
            default => 'Perbaiki tata bahasa, ejaan (PUEBI), keefektifan kalimat, dan alur paragraf agar terdengar profesional, jernih, dan layak terbit di media massa resmi.',
        };

        $prompt = <<<PROMPT
Anda adalah editor bahasa senior untuk Yayasan MKT (Mitra Kemanusiaan Terpadu).
Instruksi: {$actionPrompt}

Berikut adalah draf teks berita yang harus Anda edit:
"""
{$content}
"""

Berikan HANYA teks hasil penyempurnaan langsung tanpa kata pengantar atau penutup tambahan.
PROMPT;

        $model = config('services.ai.model', env('AI_MODEL', 'gemini-1.5-flash'));
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

        $res = Http::timeout(45)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.6,
                ],
            ]);

        if ($res->successful()) {
            $json = $res->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!empty($text)) {
                return [
                    'success' => true,
                    'source' => 'gemini',
                    'content' => trim($text),
                ];
            }
        }

        throw new \Exception('Gemini Enhance call failed: ' . $res->body());
    }

    /**
     * Call OpenAI / Groq / OpenRouter compatible chat completion
     */
    protected function callOpenAiCompatibleGenerate(string $topic, string $category, string $tone, string $length, string $apiKey, string $provider): array
    {
        $baseUrl = match($provider) {
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            default => 'https://api.openai.com/v1/chat/completions',
        };

        $model = match($provider) {
            'groq' => 'llama-3.3-70b-versatile',
            'openrouter' => 'google/gemini-2.0-flash-exp:free',
            default => 'gpt-4o-mini',
        };

        $systemPrompt = "Anda adalah jurnalis media kemanusiaan Yayasan MKT. Berikan artikel berita siap terbit dalam format JSON dengan kunci: title, category, summary, content, tags.";
        $userPrompt = "Topik: {$topic}\nKategori: {$category}\nNada: {$tone}\nPanjang: {$length}\nKeluarkan hanya format JSON murni.";

        $res = Http::timeout(45)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($baseUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ]);

        if ($res->successful()) {
            $json = $res->json();
            $rawContent = $json['choices'][0]['message']['content'] ?? '';
            $parsed = json_decode($rawContent, true);

            if (!empty($parsed['title']) && !empty($parsed['content'])) {
                return [
                    'success' => true,
                    'source' => $provider,
                    'title' => trim($parsed['title']),
                    'category' => $parsed['category'] ?? $category,
                    'summary' => trim($parsed['summary'] ?? ''),
                    'content' => trim($parsed['content']),
                    'tags' => $parsed['tags'] ?? ['MKT Peduli', $category],
                ];
            }
        }

        throw new \Exception("{$provider} API call failed: " . $res->body());
    }

    /**
     * Call OpenAI / Groq compatible enhance
     */
    protected function callOpenAiCompatibleEnhance(string $content, string $action, string $apiKey, string $provider): array
    {
        $baseUrl = match($provider) {
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            default => 'https://api.openai.com/v1/chat/completions',
        };

        $model = match($provider) {
            'groq' => 'llama-3.3-70b-versatile',
            default => 'gpt-4o-mini',
        };

        $res = Http::timeout(45)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($baseUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Anda adalah editor bahasa berita kemanusiaan MKT. Poles dan kembalikan teks hasil suntingan tanpa basa-basi.'],
                    ['role' => 'user', 'content' => "Instruksi: {$action}\n\nTeks:\n{$content}"],
                ],
                'temperature' => 0.6,
            ]);

        if ($res->successful()) {
            $json = $res->json();
            $text = $json['choices'][0]['message']['content'] ?? '';
            if (!empty($text)) {
                return [
                    'success' => true,
                    'source' => $provider,
                    'content' => trim($text),
                ];
            }
        }

        throw new \Exception("Enhance via {$provider} failed.");
    }

    /**
     * Execute raw Gemini API call with JSON schema response
     */
    protected function rawGeminiRequest(string $prompt, string $apiKey, bool $jsonMode = false, ?string $model = null): array
    {
        $modelName = $model ?: config('services.ai.model', env('AI_MODEL', 'gemini-1.5-flash'));
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

        $body = [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
            ],
        ];

        if ($jsonMode) {
            $body['generationConfig']['responseMimeType'] = 'application/json';
        }

        $response = Http::timeout(45)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $body);

        if (!$response->successful()) {
            throw new \Exception('Gemini API returned error: ' . $response->status() . ' ' . $response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if ($jsonMode) {
            // Remove markdown codeblock wrapper if present
            $cleaned = preg_replace('/^```json\s*|\s*```$/m', '', trim($text));
            $decoded = json_decode($cleaned, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            throw new \Exception('Gagal melakukan parsing JSON dari respon Gemini.');
        }

        return ['text' => $text];
    }

    /**
     * Smart Heuristic Fallback Generator when API Key is absent or network unreachable
     */
    protected function generateSmartFallback(string $topic, string $category, string $tone, string $length, bool $isNoKey = true): array
    {
        $cleanTopic = rtrim($topic, '. ');
        $location = 'wilayah binaan kemanusiaan MKT';

        // Detect location keywords in topic
        if (preg_match('/di\s+([A-Za-z\s]+?)(?:\s+(?:pada|oleh|dengan|untuk|karena)|$)/i', $topic, $matches)) {
            $location = trim($matches[1]);
        }

        $titlePrefix = match($category) {
            'Evakuasi' => 'Aksi Cepat SAR & Evakuasi',
            'Logistik' => 'Penyaluran Bantuan Logistik Darurat',
            'Kesehatan' => 'Layanan Medis & Relawan Donor Darah',
            'Mitigasi' => 'Penguatan Kesiapsiagaan & Mitigasi',
            'Relawan' => 'Sinergi Relawan dan Solidaritas',
            default => 'Gerak Kemanusiaan Yayasan MKT',
        };

        $title = "{$titlePrefix}: Respon Tanggap MKT dalam {$cleanTopic}";

        $paragraphs = [];

        // Paragraph 1: Lead 5W+1H
        $paragraphs[] = "YAYASAN MKT PEDULI — Sebagai wujud kesiapsiagaan dan komitmen dalam merespon dinamika kemanusiaan di lapangan, Tim Yayasan Mitra Kemanusiaan Terpadu (MKT) bergerak cepat menindaklanjuti {$cleanTopic}. Berdasarkan laporan situasi terkini di {$location}, personel relawan telah diterjunkan untuk memastikan penanganan awal berjalan tertib dan tepat sasaran.";

        // Paragraph 2: Operational / Field action details
        $paragraphs[] = "Koordinator Lapangan Tim Respon MKT menyampaikan bahwa koordinasi lintas sektor bersama para pemangku kepentingan—termasuk BPBD, Basarnas, serta jejaring relawan lokal—terus diintensifkan demi menjamin kelancaran asesmen kebutuhan di lokasi. Setiap langkah penanganan diprioritaskan untuk keselamatan warga terdampak, terutama kelompok rentan seperti lansia, anak-anak, dan ibu menyusui.";

        // Paragraph 3: Statement from leadership
        $paragraphs[] = "“Kami di Yayasan MKT senantiasa menempatkan nilai kemanusiaan dan kecepatan respon sebagai pilar utama. Setiap donasi dan amanah yang dititipkan masyarakat melalui ekosistem MKT kami salurkan secara langsung, transparan, dan akuntabel di garis depan,” ujar perwakilan manajemen Pusdalops MKT.";

        if ($length === 'panjang') {
            $paragraphs[] = "Di samping penanganan langsung di lokasi, posko logistik dan tim medis siaga 24 jam terus memperbarui data inventaris bantuan guna mengantisipasi eskalasi kebutuhan lanjutan. Sinergi para relawan donor darah dan mitra filantropi menjadi motor penggerak penting dalam menjaga kesinambungan operasi sosial ini.";
        }

        // Final Paragraph: Call to action
        $paragraphs[] = "Yayasan MKT mengajak segenap lapisan masyarakat dan para dermawan untuk terus memperkuat jaring kebaikan ini. Informasi terkini terkait perkembangan giat lapangan dan penyaluran bantuan dapat dipantau melalui portal resmi mkt.or.id atau langsung menghubungi Command Center Pusdalops MKT.";

        $content = implode("\n\n", $paragraphs);
        $summary = "Tim Yayasan MKT bergerak cepat dalam merespon {$cleanTopic} di {$location} dengan memprioritaskan keselamatan dan bantuan darurat.";

        return [
            'success' => true,
            'source' => 'smart_template',
            'title' => Str::limit($title, 120),
            'category' => $category,
            'summary' => $summary,
            'content' => $content,
            'tags' => ['MKT Peduli', $category, 'Relawan', 'Bencana'],
            'notice' => $isNoKey 
                ? '💡 Draf dihasilkan oleh Asisten Cerdas Jurnalistik MKT. Untuk hasil AI Generatif yang bebas konteks (Google Gemini), masukkan API Key di menu Pengaturan AI atau file .env.'
                : 'Respon dihasilkan melalui mesin template cadangan MKT karena layanan API luar tidak dapat dijangkau.',
        ];
    }

    /**
     * Fallback text enhancer when external API is not configured
     */
    protected function fallbackEnhance(string $content, string $action): array
    {
        $paragraphs = array_filter(array_map('trim', explode("\n", $content)));
        
        if (empty($paragraphs)) {
            return [
                'success' => true,
                'source' => 'smart_template',
                'content' => $content,
            ];
        }

        // Apply basic journalistic grooming
        $enhancedParagraphs = array_map(function($p) {
            $p = ucfirst($p);
            if (!str_ends_with($p, '.') && !str_ends_with($p, '!') && !str_ends_with($p, '?')) {
                $p .= '.';
            }
            return $p;
        }, $paragraphs);

        $enhancedText = implode("\n\n", $enhancedParagraphs);

        if ($action === 'humanize' && !str_contains($enhancedText, 'Pusdalops MKT')) {
            $enhancedText .= "\n\nYayasan MKT menyampaikan apresiasi setinggi-tingginya kepada seluruh relawan dan donatur yang senantiasa membersamai gerak kemanusiaan ini.";
        }

        return [
            'success' => true,
            'source' => 'smart_template',
            'content' => $enhancedText,
            'notice' => 'Teks telah disunting dengan aturan jurnalistik dasar. Hubungkan Gemini API Key untuk penyuntingan bertenaga LLM mendalam.',
        ];
    }
}
