<?php

namespace Tests\Unit;

use App\Services\AiNewsAssistantService;
use Tests\TestCase;

class AiNewsAssistantServiceTest extends TestCase
{
    protected AiNewsAssistantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AiNewsAssistantService();
    }

    public function test_it_rejects_empty_topic(): void
    {
        $result = $this->service->generateArticle(['topic' => '']);
        $this->assertFalse($result['success']);
        $this->assertEquals('Topik atau poin berita wajib diisi.', $result['message']);
    }

    public function test_it_generates_smart_fallback_article_successfully(): void
    {
        $result = $this->service->generateArticle([
            'topic' => 'Penyaluran 500 paket beras di Maros oleh tim SAR MKT',
            'category' => 'Logistik',
            'tone' => 'jurnalistik',
            'length' => 'standar',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['title']);
        $this->assertEquals('Logistik', $result['category']);
        $this->assertNotEmpty($result['content']);
        $this->assertStringContainsString('Penyaluran 500 paket beras di Maros', $result['content']);
        $this->assertNotEmpty($result['summary']);
        $this->assertIsArray($result['tags']);
    }

    public function test_it_enhances_content_with_journalistic_rules(): void
    {
        $result = $this->service->enhanceContent([
            'content' => 'tim rescue mkt bantu warga kena musibah banjir',
            'action' => 'humanize',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['content']);
        $this->assertStringContainsString('Yayasan MKT', $result['content']);
    }

    public function test_it_suggests_headlines(): void
    {
        $result = $this->service->suggestHeadlines([
            'content' => 'Aksi donor darah MKT bersama PMI terkumpul 200 kantong',
        ]);

        $this->assertTrue($result['success']);
        $this->assertIsArray($result['headlines']);
        $this->assertCount(5, $result['headlines']);
    }
}
