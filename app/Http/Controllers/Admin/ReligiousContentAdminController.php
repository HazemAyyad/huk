<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Religious\ReligiousCatalog;
use Illuminate\Http\Request;

final class ReligiousContentAdminController extends Controller
{
    public function __construct(private readonly ReligiousCatalog $catalog)
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function index(Request $request)
    {
        $data = $this->catalog->all();
        $query = trim((string) $request->query('q', ''));
        $items = array_filter($data['items'], fn ($i) => $query === '' || str_contains($i['title'].' '.$i['id'], $query));

        return view('admin.religious.index', ['catalog' => $data, 'items' => $items]);
    }

    public function edit(string $id)
    {
        $item = $this->catalog->item($id, false);
        abort_unless($item, 404);

        return view('admin.religious.edit', ['item' => $item, 'catalog' => $this->catalog->all(), 'creating' => false]);
    }

    public function create()
    {
        return view('admin.religious.edit', ['catalog' => $this->catalog->all(), 'creating' => true,
            'item' => ['id' => '', 'type' => 'dua', 'title' => '', 'text' => '', 'source' => '', 'sourceUrl' => '',
                'summary' => '', 'image' => 'quran', 'number' => 0, 'categories' => [], 'tags' => [], 'verseKeys' => [],
                'related' => [], 'sections' => [], 'published' => false]]);
    }

    public function save(Request $request)
    {
        $input = $request->all();
        foreach (['tags', 'verseKeys', 'related'] as $key) {
            $input[$key] = array_values(array_filter(array_map('trim', explode(',', $input[$key] ?? '')), fn ($v) => $v !== ''));
        }
        $input['sections'] = array_values(array_filter($input['sections'] ?? [],
            fn ($section) => ! empty($section['title']) || ! empty($section['text'])));
        $input['published'] = $request->boolean('published');
        $this->catalog->saveItem($input);

        return redirect()->route('admin.religious.edit', ['id' => $input['id']])->with('success', 'تم حفظ المحتوى.');
    }

    public function saveCategory(Request $request)
    {
        $input = $request->all();
        $input['published'] = $request->boolean('published');
        $input['parent'] = $input['parent'] ?: null;
        $this->catalog->saveCategory($input);

        return redirect()->route('admin.religious.index')->with('success', 'تم حفظ التصنيف.');
    }
}
