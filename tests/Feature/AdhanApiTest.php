<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class AdhanApiTest extends TestCase
{
    private string $folder;

    private string $manifest;

    private string $audio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->folder = sys_get_temp_dir().'/haq-adhans-'.bin2hex(random_bytes(8));
        mkdir($this->folder);
        $this->manifest = $this->folder.'/catalog.json';
        $this->audio = 'ID3'.str_repeat('x', 1200);
        file_put_contents($this->folder.'/athan_1.mp3', $this->audio);
        file_put_contents($this->folder.'/takbeer_1.mp3', $this->audio);
        $this->writeManifest();
        config(['religious_adhans.manifest' => $this->manifest, 'religious_adhans.directory' => $this->folder]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->folder);
        parent::tearDown();
    }

    private function writeManifest(?string $hash = null, string $filename = 'athan_1.mp3'): void
    {
        $recording = ['file' => $filename, 'bytes' => strlen($this->audio), 'sha256' => $hash ?? hash('sha256', $this->audio)];
        file_put_contents($this->manifest, json_encode(['version' => 'test.1', 'source' => 'نسك', 'voices' => [[
            'id' => 'nusuk-1', 'name' => 'علي أحمد ملا', 'englishName' => 'Ali Ahmed Mulla', 'bundled' => true,
            'full' => $recording, 'takbeer' => [...$recording, 'file' => 'takbeer_1.mp3'],
        ]]], JSON_UNESCAPED_UNICODE));
    }

    public function test_catalog_is_public_and_exposes_urls_and_checksums_without_local_paths(): void
    {
        $response = $this->getJson('/api/v1/religious/adhans/catalog')->assertOk()
            ->assertJsonCount(1, 'data.voices')->assertJsonPath('data.voices.0.bundled', true)
            ->assertJsonPath('data.voices.0.full.sha256', hash('sha256', $this->audio));
        $this->assertStringEndsWith('/api/v1/religious/adhans/nusuk-1/takbeer', $response->json('data.voices.0.takbeer.url'));
        $this->assertStringNotContainsString($this->folder, $response->getContent());
        $this->assertArrayNotHasKey('file', $response->json('data.voices.0.full'));
    }

    public function test_both_recordings_are_public_audio_with_http_range_support(): void
    {
        foreach (['full', 'takbeer'] as $kind) {
            $this->get('/api/v1/religious/adhans/nusuk-1/'.$kind)->assertOk()
                ->assertHeader('Content-Type', 'audio/mpeg')
                ->assertHeader('X-Content-SHA256', hash('sha256', $this->audio));
        }
        $response = $this->withHeaders(['Range' => 'bytes=0-9'])
            ->get('/api/v1/religious/adhans/nusuk-1/full')->assertStatus(206)
            ->assertHeader('Content-Range', 'bytes 0-9/'.strlen($this->audio))
            ->assertHeader('Content-Length', '10');
        $this->assertSame(realpath($this->folder.'/athan_1.mp3'), $response->baseResponse->getFile()->getPathname());
    }

    public function test_unknown_or_missing_audio_and_traversal_are_not_served(): void
    {
        foreach (['nusuk-999/full', 'alafasy/full', 'nusuk-1/other', 'nusuk-0/full', '%2e%2e/full'] as $path) {
            $this->getJson('/api/v1/religious/adhans/'.$path)->assertNotFound();
        }
        unlink($this->folder.'/athan_1.mp3');
        $this->getJson('/api/v1/religious/adhans/nusuk-1/full')->assertNotFound();
        $this->writeManifest(filename: '../secret.mp3');
        $this->getJson('/api/v1/religious/adhans/nusuk-1/full')->assertNotFound();
    }

    public function test_integrity_mismatch_and_missing_manifest_are_reported_as_unavailable(): void
    {
        $this->writeManifest(str_repeat('0', 64));
        $this->getJson('/api/v1/religious/adhans/nusuk-1/full')->assertStatus(503);
        unlink($this->manifest);
        $this->getJson('/api/v1/religious/adhans/catalog')->assertStatus(503);
    }
}
