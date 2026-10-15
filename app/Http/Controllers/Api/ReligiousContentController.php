<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\ReligiousCatalog;
use App\Services\Religious\ReligiousSync;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class ReligiousContentController extends Controller
{
    public function __construct(private readonly ReligiousCatalog $catalog) {}

    public function manifest()
    {
        $json = $this->catalog->json();
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return response()->json(['data' => ['version' => $data['version'], 'sha256' => hash('sha256', $json),
            'item_count' => count($data['items']), 'category_count' => count($data['categories'])]]);
    }

    public function dataset()
    {
        $json = $this->catalog->json();

        return response($json, 200, ['Content-Type' => 'application/json; charset=utf-8',
            'ETag' => '"'.hash('sha256', $json).'"', 'Cache-Control' => 'public, max-age=300']);
    }

    public function categories()
    {
        return response()->json(['data' => $this->catalog->dataset()['categories']]);
    }

    public function show(string $id)
    {
        $item = $this->catalog->item($id);
        abort_unless($item, 404);

        return response()->json(['data' => $item]);
    }

    public function index(Request $request)
    {
        $data = $this->catalog->dataset();
        $input = $request->validate(['category' => ['nullable', Rule::in(array_column($data['categories'], 'id'))],
            'q' => 'nullable|string|max:200', 'tag' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1|max:10000', 'per_page' => 'nullable|integer|min:1|max:100']);
        $ids = isset($input['category']) ? [$input['category']] : [];
        foreach ($data['categories'] as $category) {
            if (in_array($category['parent'] ?? null, $ids, true)) {
                $ids[] = $category['id'];
            }
        }
        $normalize = fn ($text) => preg_replace('/[\x{0610}-\x{061a}\x{064b}-\x{065f}\x{0670}\x{06d6}-\x{06ed}\x{0640}]/u', '',
            str_replace(['أ', 'إ', 'آ', 'ٱ', 'ى'], ['ا', 'ا', 'ا', 'ا', 'ي'], $text));
        $query = $normalize(trim($input['q'] ?? ''));
        $items = array_values(array_filter($data['items'], fn ($i) => (empty($ids) || array_intersect($ids, $i['categories'])) &&
            (empty($input['tag']) || in_array($input['tag'], $i['tags'], true)) &&
            ($query === '' || str_contains($normalize($i['title'].' '.$i['text'].' '.$i['source']), $query))));
        $page = $input['page'] ?? 1;
        $limit = $input['per_page'] ?? 50;

        return response()->json(['data' => array_slice($items, ($page - 1) * $limit, $limit),
            'meta' => ['total' => count($items), 'page' => $page, 'per_page' => $limit]]);
    }

    public function sync(Request $request, ReligiousSync $sync)
    {
        $input = $request->validate(['operations' => 'present|array|max:250',
            'operations.*' => 'array:id,key,base_revision,deleted,value',
            'operations.*.id' => 'required|uuid|distinct', 'operations.*.key' => 'required|string|max:120',
            'operations.*.base_revision' => 'required|integer|min:0', 'operations.*.deleted' => 'required|boolean',
            'operations.*.value' => 'present']);

        return response()->json(['data' => $sync->exchange($request->user(), $input['operations'])]);
    }
}
