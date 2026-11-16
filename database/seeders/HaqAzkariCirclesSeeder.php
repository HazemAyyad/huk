<?php

namespace Database\Seeders;

use App\Services\Religious\CommunityAdministration;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Public circle names and goals observed in Athkari, with independently sourced remembrance. */
final class HaqAzkariCirclesSeeder extends Seeder
{
    private const CIRCLES = [
        [
            'title' => 'حلقة الحوقلة', 'id' => 'haq-circle-hawqala', 'goal' => 5000000,
            'text' => 'لَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللَّهِ',
            'source' => 'صحيح البخاري • 6384', 'sourceUrl' => 'https://sunnah.com/bukhari:6384',
            'category' => 'glorification',
        ],
        [
            'title' => 'حلقة الاستغفار', 'id' => 'haq-circle-istighfar', 'goal' => 7000000,
            'text' => 'أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ',
            'source' => 'صحيح البخاري • 6307 • استغفار النبي في اليوم والليلة', 'sourceUrl' => 'https://sunnah.com/bukhari:6307',
            'category' => 'istighfar',
        ],
        [
            'title' => 'حلقة دعاء ما بين السجدتين', 'id' => 'haq-circle-between-prostrations', 'goal' => 1000000,
            'text' => 'رَبِّ اغْفِرْ لِي، رَبِّ اغْفِرْ لِي',
            'source' => 'سنن أبي داود • 874؛ حصن المسلم • دعاء الجلسة بين السجدتين', 'sourceUrl' => 'https://sunnah.com/abudawud:874',
            'category' => 'prophetic-duas',
        ],
        [
            'title' => 'حلقة الصلاة على النبي', 'id' => 'haq-circle-salawat', 'goal' => 10000000,
            'text' => 'اللَّهُمَّ صَلِّ وَسَلِّمْ وَبَارِكْ عَلَى نَبِيِّنَا مُحَمَّدٍ',
            'source' => 'الصلاة على النبي • أصل المشروعية: سورة الأحزاب، الآية 56', 'sourceUrl' => 'https://quran.com/33/56',
            'category' => 'prophetic-duas',
        ],
        [
            'title' => 'حلقة التكبير', 'id' => 'haq-circle-takbir', 'goal' => 5000000,
            'text' => 'اللَّهُ أَكْبَرُ',
            'source' => 'صحيح مسلم • 2137 • أحب الكلام إلى الله', 'sourceUrl' => 'https://sunnah.com/muslim:2137a',
            'category' => 'glorification',
        ],
    ];

    public function run(ReligiousCatalog $catalog, CommunityAdministration $admin): void
    {
        $added = Cache::lock('haq-athkari-circles-import', 120)->block(10, function () use ($catalog, $admin): int {
            $added = 0;
            foreach (self::CIRCLES as $reference) {
                if (DB::table('religious_circles')->where('title', $reference['title'])->exists()) {
                    continue;
                }
                $state = $catalog->all();
                if (! in_array($reference['id'], array_column($state['items'], 'id'), true)) {
                    $catalog->saveItem([
                        'id' => $reference['id'], 'type' => 'dua', 'title' => $reference['title'],
                        'text' => $reference['text'], 'source' => $reference['source'], 'sourceUrl' => $reference['sourceUrl'],
                        'image' => 'beads', 'categories' => [$reference['category']], 'published' => true,
                    ], $state['version']);
                }
                $admin->circle(Request::create('/', 'POST', [
                    'title' => $reference['title'], 'content_id' => $reference['id'], 'goal' => $reference['goal'],
                    'voice_phrase' => $reference['text'], 'published' => true,
                ]));
                $added++;
            }

            return $added;
        });
        $this->command?->info("Athkari circle references imported: {$added}.");
    }
}
