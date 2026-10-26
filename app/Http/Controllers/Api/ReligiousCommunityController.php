<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\ReligiousCommunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ReligiousCommunityController extends Controller
{
    public function __construct(private readonly ReligiousCommunity $community) {}

    public function capabilities()
    {
        return response()->json(['data' => ['schema' => 1, 'enabled' => $this->community->ready(), 'review_required' => true, 'max_text' => 180, 'max_count_batch' => 100, 'page_size' => 20]]);
    }

    private function page(Request $request): array
    {
        $data = $request->validate(['cursor' => 'sometimes|integer|min:0', 'limit' => 'sometimes|integer|min:1|max:50']);

        return [(int) ($data['cursor'] ?? 0), (int) ($data['limit'] ?? 20)];
    }

    public function circles(Request $request)
    {
        $owner = $this->community->actor($request->user());
        [$cursor,$limit] = $this->page($request);

        return response()->json(['data' => $this->community->page(DB::table('religious_circles')->where('published', true), $cursor, $limit, fn ($row) => $this->community->circle($row, $owner))]);
    }

    public function circle(Request $request, int $circle)
    {
        $owner = $this->community->actor($request->user());
        $row = DB::table('religious_circles')->where('published', true)->find($circle);
        abort_unless($row, 404);

        return response()->json(['data' => $this->community->circle($row, $owner)]);
    }

    public function membership(Request $request, int $circle)
    {
        $data = $request->validate(['joined' => 'required|boolean']);
        $result = DB::transaction(function () use ($request, $circle, $data) {
            $owner = $this->community->actor($request->user(), true);
            $row = DB::table('religious_circles')->where('published', true)->where('id', $circle)->lockForUpdate()->first();
            abort_unless($row, 404);
            DB::table('religious_circle_members')->updateOrInsert(['circle_id' => $circle, 'account_id' => $owner], ['active' => $data['joined'], 'created_at' => now(), 'updated_at' => now()]);

            return $this->community->circle($row, $owner);
        }, 3);

        return response()->json(['data' => $result]);
    }

    public function count(Request $request, int $circle)
    {
        $data = $request->validate(['operation_id' => 'required|uuid', 'amount' => 'required|integer|min:1|max:100']);

        return response()->json(['data' => $this->community->count($request->user(), $circle, $data['operation_id'], (int) $data['amount'])]);
    }

    public function entries(Request $request)
    {
        $owner = $this->community->actor($request->user());
        [$cursor,$limit] = $this->page($request);
        $data = $request->validate(['mine' => 'sometimes|boolean', 'parent_id' => 'sometimes|integer|min:1']);
        $mine = $request->boolean('mine');
        $parent = $data['parent_id'] ?? null;
        if ($parent !== null) {
            abort_unless($this->community->entry($parent, $owner)->parent_id === null, 422);
        }
        $query = $mine ? DB::table('religious_community_entries')->where('account_id', $owner)->whereIn('status', ['pending', 'approved', 'rejected']) : $this->community->visibleEntries($owner);
        $query->where('parent_id', $parent);

        return response()->json(['data' => $this->community->page($query, $cursor, $limit, fn ($row) => $this->community->present($row, $owner))]);
    }

    public function entry(Request $request, int $entry)
    {
        $owner = $this->community->actor($request->user());

        return response()->json(['data' => $this->community->present($this->community->entry($entry, $owner), $owner)]);
    }

    public function create(Request $request)
    {
        $request->merge(['body' => trim((string) $request->input('body'))]);
        $data = $request->validate(['operation_id' => 'required|uuid', 'body' => 'required|string|max:180', 'parent_id' => 'nullable|integer|min:1']);

        return response()->json(['data' => $this->community->createEntry($request->user(), $data['operation_id'], $data['body'], $data['parent_id'] ?? null)], 201);
    }

    public function destroy(Request $request, int $entry)
    {
        DB::transaction(function () use ($request, $entry) {
            $owner = $this->community->actor($request->user(), true);
            abort_unless(DB::table('religious_community_entries')->where('account_id', $owner)->where('id', $entry)->exists(), 404);
            // Keep the idempotency receipt; a timed-out retry cannot republish it.
            DB::table('religious_community_entries')->where('id', $entry)->orWhere('parent_id', $entry)->update(['status' => 'deleted', 'updated_at' => now()]);
        });

        return response()->json(['data' => ['deleted' => true]]);
    }

    public function like(Request $request, int $entry)
    {
        $data = $request->validate(['liked' => 'required|boolean']);
        $result = DB::transaction(function () use ($request, $entry, $data) {
            $owner = $this->community->actor($request->user(), true);
            $row = $this->community->entry($entry, $owner);
            $query = DB::table('religious_community_likes')->where('entry_id', $entry)->where('account_id', $owner);
            if ($data['liked']) {
                DB::table('religious_community_likes')->insertOrIgnore(['entry_id' => $entry, 'account_id' => $owner]);
            } else {
                $query->delete();
            }

            return $this->community->present($row, $owner);
        });

        return response()->json(['data' => $result]);
    }

    public function report(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:entry,account', 'target_id' => 'required|integer|min:1', 'reason' => 'required|string|max:180']);
        $owner = $this->community->actor($request->user());
        if ($data['type'] === 'entry') {
            $this->community->entry((int) $data['target_id'], $owner);
        } else {
            abort_unless(DB::table('religious_accounts')->where('id', $data['target_id'])->where('id', '!=', $owner)->exists(), 404);
        }
        DB::table('religious_community_reports')->insertOrIgnore(['account_id' => $owner, 'target_type' => $data['type'], 'target_id' => $data['target_id'], 'reason' => $data['reason'], 'status' => 'open', 'created_at' => now(), 'updated_at' => now()]);

        return response()->json(['data' => ['received' => true]]);
    }

    public function block(Request $request, int $account)
    {
        $data = $request->validate(['blocked' => 'required|boolean']);
        $owner = $this->community->actor($request->user());
        abort_unless($account !== $owner && DB::table('religious_accounts')->where('id', $account)->exists(), 404);
        if ($data['blocked']) {
            DB::table('religious_community_blocks')->insertOrIgnore(['account_id' => $owner, 'blocked_id' => $account]);
        } else {
            DB::table('religious_community_blocks')->where('account_id', $owner)->where('blocked_id', $account)->delete();
        }

        return response()->json(['data' => ['blocked' => (bool) $data['blocked']]]);
    }

    public function blocks(Request $request)
    {
        $owner = $this->community->actor($request->user());
        [$cursor,$limit] = $this->page($request);

        return response()->json(['data' => $this->community->page(DB::table('religious_community_blocks')->where('account_id', $owner), $cursor, $limit, fn ($r) => ['id' => (int) $r->id, 'account_id' => (int) $r->blocked_id, 'author' => DB::table('religious_accounts')->where('id', $r->blocked_id)->value('username') ?? 'من مجتمع حق'])]);
    }

    public function publications(Request $request)
    {
        [$cursor,$limit] = $this->page($request);
        $data = $request->validate(['channel' => 'required|in:message,story', 'kind' => 'sometimes|in:quran,hadith,dua,dhikr,tasbih,video,link', 'ids' => 'sometimes|array|max:10000', 'ids.*' => 'integer|min:1|distinct']);
        $query = $this->community->publications()->where('channel', $data['channel']);
        if (array_key_exists('ids', $data)) {
            $query->whereIn('id', $data['ids']);
        }
        if (isset($data['kind'])) {
            $query->where('kind', $data['kind']);
        }

        return response()->json(['data' => $this->community->page($query, $cursor, $limit, fn ($row) => $this->community->publication($row))]);
    }
}
