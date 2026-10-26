<?php

namespace App\Services\Religious;

final class AssistantSources
{
    private function normalize(string $value): string
    {
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', mb_strtolower($value));

        return str_replace(['أ', 'إ', 'آ', 'ى', 'ة'], ['ا', 'ا', 'ا', 'ي', 'ه'], $value);
    }

    public function find(string $question): array
    {
        $terms = array_values(array_filter(array_unique(preg_split('/[^\p{L}\p{N}]+/u', $this->normalize($question))), fn ($s) => mb_strlen($s) >= 3 && ! in_array($s, ['كيف', 'ماهو', 'ماهي', 'الذي', 'التي', 'علي', 'هذا', 'هذه', 'ماذا', 'اريد', 'بدي', 'ممكن', 'الله', 'اللهم'])));
        if (! $terms) {
            return [];
        }
        $ranked = [];
        foreach (app(ReligiousCatalog::class)->dataset()['items'] as $item) {
            if (empty($item['source']) || empty($item['text']) || mb_strlen($item['text']) > 1500) {
                continue;
            }
            $title = $this->normalize($item['title']);
            $text = $this->normalize($item['text']);
            $score = 0;
            foreach ($terms as $term) {
                $score += (str_contains($title, $term) ? 4 : 0) + (str_contains($text, $term) ? 1 : 0);
            }
            if ($score) {
                $ranked[] = ['score' => $score, 'item' => array_intersect_key($item, array_flip(['id', 'title', 'text', 'source', 'sourceUrl', 'verseKeys']))];
            }
        }
        usort($ranked, fn ($a, $b) => $b['score'] <=> $a['score'] ?: strcmp($a['item']['id'], $b['item']['id']));

        return array_column(array_slice($ranked, 0, 3), 'item');
    }
}
