<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = News::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }

        $news = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $categories = ['Evakuasi', 'Logistik', 'Kesehatan', 'Edukasi', 'Mitigasi', 'Relawan', 'Umum'];

        return Inertia::render('News/Index', [
            'news' => $news,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'author' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'content' => 'required|string',
            'published_at' => 'nullable|string',
        ]);

        if (empty($validated['author'])) {
            $validated['author'] = $request->user()->name ?? 'Humas MKT';
        }

        if (empty($validated['published_at'])) {
            $validated['published_at'] = date('Y-m-d');
        }

        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $this->processAndStoreImage($request->file('image_file'));
        }

        unset($validated['image_file']);

        News::create($validated);

        return redirect()->back()->with('success', 'Berita / Artikel berhasil diterbitkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'author' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'content' => 'required|string',
            'published_at' => 'nullable|string',
        ]);

        if ($request->hasFile('image_file')) {
            if ($news->image_url && str_starts_with($news->image_url, '/storage/news_images/')) {
                $oldPath = str_replace('/storage/', '', $news->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['image_url'] = $this->processAndStoreImage($request->file('image_file'));
        }

        unset($validated['image_file']);

        $news->update($validated);

        return redirect()->back()->with('success', 'Berita / Artikel berhasil diperbarui.');
    }

    /**
     * Store and optimize uploaded image for web & WhatsApp crawler compliance (< 300KB)
     */
    private function processAndStoreImage($file): string
    {
        $directory = storage_path('app/public/news_images');
        if (!file_exists($directory)) {
            @mkdir($directory, 0755, true);
        }

        $filename = \Illuminate\Support\Str::random(40) . '.jpg';
        $destinationPath = $directory . '/' . $filename;

        // If GD extension is available, optimize, resize, and convert to standard compressed JPEG
        if (extension_loaded('gd')) {
            try {
                $imageInfo = @getimagesize($file->getRealPath());
                if ($imageInfo) {
                    $mime = $imageInfo['mime'];
                    $srcImage = match ($mime) {
                        'image/png' => @imagecreatefrompng($file->getRealPath()),
                        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
                        'image/gif' => @imagecreatefromgif($file->getRealPath()),
                        default => @imagecreatefromjpeg($file->getRealPath()),
                    };

                    if ($srcImage) {
                        $origWidth = imagesx($srcImage);
                        $origHeight = imagesy($srcImage);
                        $maxWidth = 1200;

                        if ($origWidth > $maxWidth) {
                            $newWidth = $maxWidth;
                            $newHeight = (int) round(($origHeight / $origWidth) * $maxWidth);
                            $resized = imagecreatetruecolor($newWidth, $newHeight);

                            // Handle transparency for PNG/GIF
                            $white = imagecolorallocate($resized, 255, 255, 255);
                            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $white);
                            imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                            imagedestroy($srcImage);
                            $srcImage = $resized;
                        }

                        // Save as high-quality, lightweight JPEG (quality 82, typically 80-180 KB)
                        imagejpeg($srcImage, $destinationPath, 82);
                        imagedestroy($srcImage);
                        @chmod($destinationPath, 0644);

                        return '/storage/news_images/' . $filename;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to default Laravel store on exception
            }
        }

        $path = $file->store('news_images', 'public');
        return '/storage/' . $path;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        if ($news->image_url && str_starts_with($news->image_url, '/storage/news_images/')) {
            $oldPath = str_replace('/storage/', '', $news->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $news->delete();

        return redirect()->back()->with('success', 'Berita / Artikel berhasil dihapus.');
    }
}
