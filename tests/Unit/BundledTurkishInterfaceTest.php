<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class BundledTurkishInterfaceTest extends TestCase
{
    public function test_turkish_covers_every_interface_key_and_preserves_arguments_and_links(): void
    {
        $registry = json_decode(file_get_contents(dirname(__DIR__, 2).'/resources/haq-app/ui.json'), true, 512, JSON_THROW_ON_ERROR);
        foreach ($registry as $key => $values) {
            $this->assertNotEmpty(trim($values['tr'] ?? ''), $key);
            preg_match_all('/\{[A-Za-z][A-Za-z0-9_]*\}/', $values['ar'], $expected);
            preg_match_all('/\{[A-Za-z][A-Za-z0-9_]*\}/', $values['tr'], $actual);
            sort($expected[0]);
            sort($actual[0]);
            $this->assertSame($expected[0], $actual[0], $key);
            preg_match_all('~https?://[^\s]+~u', $values['en'], $links);
            foreach ($links[0] as $url) {
                $this->assertStringContainsString($url, $values['tr'], $key);
            }
        }
        $this->assertSame('Esmâ-i Hüsnâ', $registry['feature.names']['tr']);
        $this->assertSame('Öğle', $registry['prayer.name.dhuhr']['tr']);
        $this->assertSame('İkindi', $registry['prayer.name.asr']['tr']);
        $this->assertSame('Sabah zikirleri', $registry['reminder.morning']['tr']);
    }
}
