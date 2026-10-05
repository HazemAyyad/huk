<?php
namespace Database\Seeders;

use App\Models\CaseProfile;
use Illuminate\Database\Seeder;

class CaseProfileSeeder extends Seeder
{
    public function run()
    {
        $cases = [
            ['key' => 'orphans',  'name' => 'الأيتام'],
            ['key' => 'amputees', 'name' => 'حالات البتر'],
            ['key' => 'housing',  'name' => 'مساكن العبور'],
            ['key' => 'family',   'name' => 'أسرة بدون معيل'],
            ['key' => 'stranded', 'name' => 'العالقين'],
            ['key' => 'injured',  'name' => 'المصابين'],
            ['key' => 'patients', 'name' => 'المرضى'],
        ];

        foreach ($cases as $case) {
            CaseProfile::firstOrCreate(['key' => $case['key']], $case);
        }
    }
}
