<?php

namespace Tests\Support;

use App\Services\Religious\HadithSourceArchive;

final class HadithSourceFixture
{
    public static function csv(string $locale = 'en', string $id = '123'): string
    {
        $handle = fopen('php://temp', 'w+b');
        fputcsv($handle, array_pad(["# Translated Prophetic Hadiths\r\n# Source: https://hadeethenc.com/$locale\r\n# Last update: 2026-05-10 17:43:35 (v1.25.0)\r\n# Check for updates: https://hadeethenc.com/en/check/$locale/v1.25.0\r\n# PLEASE DON'T REMOVE THIS IMPORTANT INFORMATION!"], count(HadithSourceArchive::COLUMNS), ''), ',', '"', '');
        fputcsv($handle, HadithSourceArchive::COLUMNS, ',', '"', '');
        fputcsv($handle, [$id, 'عنوان المصدر للاختبار فقط', 'Publisher fixture title', 'نص المصدر العربي للاختبار فقط',
            "  Fixture text with \"quoted\" words, comma\nand a new line  ", 'شرح عربي للاختبار', '<p>Exact explanation &amp; formatting</p>',
            'فوائد للاختبار', 'Original benefits', 'درجة للاختبار', 'تخريج للاختبار', 'Fixture grade', 'Fixture reference',
            $locale, "https://hadeethenc.com/$locale/browse/hadith/$id"], ',', '"', '');
        rewind($handle);
        $raw = stream_get_contents($handle);
        fclose($handle);

        return $raw;
    }
}
