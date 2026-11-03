<?php

namespace App\Services\Religious;

final class AppAdminSchema
{
    public static function fields(string $section, array $catalog, array $managed): array
    {
        $f = fn ($key, $label, $type = 'text', $extra = []) => ['key' => $key, 'label' => $label, 'type' => $type, ...$extra];
        $choices = fn ($rows) => array_map(fn ($row) => ['value' => (string) $row['id'], 'label' => $row['title'] ?? $row['name'] ?? $row['id']], $rows);
        $select = fn ($key, $label, $options) => $f($key, $label, 'select', ['options' => $options]);
        $pairs = fn ($values) => array_map(fn ($value, $label) => ['value' => $value, 'label' => $label], array_values($values), array_keys($values));
        $items = array_map(fn ($item) => ['value' => $item['id'], 'label' => $item['title'], 'text' => $item['text'], 'source' => $item['source']], $catalog['items']);
        $textSource = [$f('title', 'العنوان'), $f('text', 'النص الأصلي', 'multiline'), $f('source', 'المصدر والتخريج'), $f('sourceUrl', 'رابط المصدر')];
        $publish = $f('published', 'إظهار للمستخدمين', 'bool');
        $id = $f('id', 'معرّف ثابت', 'id');

        return match ($section) {
            'tafsirs' => [$id, $f('title', 'اسم مصدر التفسير'), $publish],
            'messages', 'stories' => [$f('title', 'العنوان'), $select('kind', 'نوع المنشور', $pairs(['قرآن' => 'quran', 'حديث' => 'hadith', 'دعاء' => 'dua', 'ذكر' => 'dhikr', 'تسبيح' => 'tasbih', 'فيديو' => 'video', 'رابط' => 'link'])),
                $select('content_id', 'مادة موثقة من المكتبة', $items), $f('body', 'وصف للفيديو أو الرابط', 'multiline'), $f('youtube_id', 'معرّف فيديو YouTube'), $f('link', 'رابط خارجي'), $f('media', 'صورة أو فيديو', 'media'), $f('clear_media', 'إزالة الوسائط الحالية', 'bool'),
                $select('status', 'حالة النشر', $pairs(['مسودة' => 'draft', 'مجدول' => 'scheduled', 'منشور' => 'published', 'مخفي' => 'hidden'])), $f('publish_at', 'موعد النشر', 'datetime'), $f('expires_at', 'موعد الانتهاء', 'datetime')],
            'circles' => [$f('title', 'اسم الحلقة'), $select('content_id', 'الذكر الموثق', $items), $f('goal', 'الهدف الجماعي التنظيمي', 'number'), $publish],
            'library' => [$id, $select('type', 'نوع المادة', $pairs(['دعاء' => 'dua', 'حديث' => 'hadith', 'سنة' => 'sunnah', 'آيات' => 'quran', 'اسم من أسماء الله' => 'name', 'قائمة استماع' => 'playlist'])), ...$textSource, $f('summary', 'ملخص', 'multiline'),
                $f('categories', 'التصنيفات', 'multi', ['options' => $choices($catalog['categories'])]), $f('tags', 'وسوم', 'lines'), $f('verseKeys', 'الآيات المرتبطة (مثل 2:201)', 'lines'),
                $f('related', 'المواد والمقاطع المرتبطة', 'multi', ['options' => $items]), $f('sections', 'الشروح والأدلة والفوائد', 'records', ['fields' => [$f('title', 'العنوان'), $f('text', 'النص', 'multiline'), $f('source', 'المصدر')]]),
                $select('image', 'الصورة', $pairs(array_combine(ReligiousCatalog::IMAGES, ReligiousCatalog::IMAGES))), $f('number', 'الرقم', 'number'), $f('audioUrl', 'رابط التسجيل'), $f('reciter', 'القارئ أو المصدر الصوتي'), $f('transliteration', 'الاسم بالحروف اللاتينية'), $publish],
            'categories' => [$id, $f('title', 'عنوان التصنيف'), $f('description', 'الوصف', 'multiline'), $select('parent', 'التصنيف الأب', $choices($catalog['categories'])),
                $select('image', 'الصورة', $pairs(array_combine(ReligiousCatalog::IMAGES, ReligiousCatalog::IMAGES))), $select('header', 'الخلفية', $pairs(['المسجد' => 'home', 'المصحف والفانوس' => 'ruqya', 'السنن' => 'sunnah', 'القوس الأخضر' => 'quran', 'القراءة' => 'reading', 'المدينة' => 'medina', 'النوم' => 'sleep'])),
                $select('kind', 'طريقة العرض', $pairs(['قائمة' => 'list', 'تصنيفات' => 'categories', 'أسماء' => 'names', 'رقية' => 'ruqya', 'آيات' => 'verses'])), $f('position', 'الترتيب', 'number'), $publish],
            'adhkar-items' => [$id, $f('title', 'العنوان'), $f('text', 'النص الموثق', 'multiline'), $f('target', 'التكرار الوارد في المصدر', 'number'), $f('source', 'المصدر والتخريج'), $f('source_url', 'رابط المصدر'), $f('audio_url', 'تسجيل حصن المسلم')],
            'adhkar-groups' => [$id, $f('title', 'اسم المجموعة'), $f('icon', 'رمز المجموعة'), $f('position', 'ترتيب المجموعة', 'number'), $f('item_ids', 'الأذكار بالترتيب', 'multi', ['options' => $choices($managed['adhkar']['items'])])],
            'sakinah' => [$id, $f('name', 'اسم المسار'), $f('tagline', 'الوصف'), $f('dhikr', 'أذكار الجلسة، كل ذكر بسطر', 'lines'), $f('verses', 'الآيات المرتبطة، كل مرجع بسطر', 'lines'), $f('closing_dua', 'الدعاء الختامي', 'multiline'), $f('closing_source', 'المصدر والتخريج')],
            'daily' => [$id, $f('date', 'يوم الظهور', 'date'), $f('verse_key', 'مرجع الآية (مثل 65:4)'), $publish],
            'audio' => [$id, $f('title', 'اسم التسجيل'), $select('type', 'نوع التسجيل', $pairs(['أذان' => 'adhan', 'تنبيه' => 'reminder', 'تلاوة' => 'recitation', 'محتوى ديني' => 'religious'])), $f('url', 'رابط التسجيل، أو مجلد التلاوة'), $f('source', 'المصدر أو القارئ'), $f('source_url', 'رابط المصدر'),
                $f('surahs', 'السور المتاحة للتلاوة', 'multi', ['options' => array_map(fn ($n) => ['value' => (string) $n, 'label' => 'سورة '.$n], range(1, 114))]), $publish],
            'moods' => [$id, $f('title', 'عنوان الحالة'), $f('item_ids', 'المواد بالترتيب', 'multi', ['options' => $items])],
            'ramadan' => [$f('day', 'اليوم', 'number'), $select('item_id', 'المادة الموثقة', $items)],
            'service-settings' => [$f('daily_limit', 'الحد اليومي للأسئلة', 'number'), $f('monthly_limit', 'الحد الشهري للأسئلة', 'number'), $f('budget_usd', 'سقف الميزانية الشهرية بالدولار', 'number')],
            default => [],
        };
    }
}
