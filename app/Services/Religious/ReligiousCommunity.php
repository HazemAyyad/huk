<?php

namespace App\Services\Religious;

use App\Models\ReligiousAccount;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ReligiousCommunity
{
    public const TABLES = ['religious_community_accounts', 'religious_circles', 'religious_circle_members', 'religious_circle_counts', 'religious_community_entries', 'religious_community_likes', 'religious_community_blocks', 'religious_community_reports', 'religious_publications'];

    public function ready(): bool
    {
        if (! config('religious_community.enabled')) {
            return false;
        }
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    public function ensureReady(): void
    {
        abort_unless($this->ready(), 503, 'خدمات المجتمع غير مفعّلة حاليًا');
    }

    public function actor(ReligiousAccount $account, bool $lock = false): int
    {
        $this->ensureReady();
        $query = DB::table('religious_accounts')->where('id', $account->id);
        abort_unless(($lock ? $query->lockForUpdate() : $query)->first(['id']), 401);
        abort_if(DB::table('religious_community_accounts')->where('account_id', $account->id)->where('suspended', true)->exists(), 403, 'تم إيقاف المشاركة لهذا الحساب');

        return (int) $account->id;
    }

    public function content(string $id): array
    {
        $item = app(ReligiousCatalog::class)->item($id);
        abort_unless($item && ! empty($item['text']) && ! empty($item['source']), 422, 'اختر نصًا موثقًا منشورًا من مكتبة حق');

        return array_intersect_key($item, array_flip(['id', 'title', 'text', 'source', 'sourceUrl', 'verseKeys']));
    }

    public function page(Builder $query, int $cursor, int $limit, callable $map): array
    {
        if ($cursor > 0) {
            $query->where('id', '<', $cursor);
        }
        $rows = $query->orderByDesc('id')->limit($limit + 1)->get();
        $more = $rows->count() > $limit;
        $visible = $rows->take($limit);

        return ['items' => $visible->map($map)->values()->all(), 'next_cursor' => $more ? (int) $visible->last()->id : null];
    }

    public function circle(object $row, int $owner): array
    {
        return ['id' => (int) $row->id, 'title' => $row->title, 'goal' => (int) $row->goal,
            'content' => $this->content($row->content_id),
            'total' => (int) DB::table('religious_circle_counts')->where('circle_id', $row->id)->sum('amount'),
            'mine' => (int) DB::table('religious_circle_counts')->where('circle_id', $row->id)->where('account_id', $owner)->sum('amount'),
            'participants' => DB::table('religious_circle_members')->where('circle_id', $row->id)->where('active', true)->count(),
            'joined' => DB::table('religious_circle_members')->where('circle_id', $row->id)->where('account_id', $owner)->where('active', true)->exists()];
    }

    public function count(ReligiousAccount $account, int $circle, string $operation, int $amount): array
    {
        return DB::transaction(function () use ($account, $circle, $operation, $amount) {
            $owner = $this->actor($account, true);
            $row = DB::table('religious_circles')->where('id', $circle)->where('published', true)->lockForUpdate()->first();
            abort_unless($row, 404);
            $receipt = DB::table('religious_circle_counts')->where('account_id', $owner)->where('operation_id', $operation)->first();
            if ($receipt) {
                abort_if((int) $receipt->circle_id !== $circle || (int) $receipt->amount !== $amount, 409, 'معرّف المساهمة مستخدم لعملية مختلفة');

                return $this->circle($row, $owner);
            }
            abort_unless(DB::table('religious_circle_members')->where('circle_id', $circle)->where('account_id', $owner)->where('active', true)->exists(), 403, 'انضم للحلقة أولًا');
            $total = (int) DB::table('religious_circle_counts')->where('circle_id', $circle)->sum('amount');
            abort_if($total + $amount > (int) $row->goal, 409, 'اكتمل الهدف أو لم يتبق عدد كافٍ؛ حدّث الحلقة');
            DB::table('religious_circle_counts')->insert(['circle_id' => $circle, 'account_id' => $owner, 'operation_id' => $operation, 'amount' => $amount, 'created_at' => now()]);

            return $this->circle($row, $owner);
        }, 3);
    }

    public function visibleEntries(int $owner): Builder
    {
        return DB::table('religious_community_entries')->where('status', 'approved')->whereNotIn('account_id', function ($q): void {
            $q->select('account_id')->from('religious_community_accounts')->where('suspended', true);
        })->whereNotIn('account_id', function ($q) use ($owner): void {
            $q->select('blocked_id')->from('religious_community_blocks')->where('account_id', $owner);
        })->whereNotIn('account_id', function ($q) use ($owner): void {
            $q->select('account_id')->from('religious_community_blocks')->where('blocked_id', $owner);
        });
    }

    public function entry(int $id, int $owner, bool $ownPending = false): object
    {
        $row = ($ownPending ? DB::table('religious_community_entries')->where('account_id', $owner)->whereIn('status', ['pending', 'approved', 'rejected']) : $this->visibleEntries($owner))->where('id', $id)->first();
        abort_unless($row, 404);
        if ($row->parent_id) {
            abort_unless($this->visibleEntries($owner)->where('id', $row->parent_id)->exists(), 404);
        }

        return $row;
    }

    public function present(object $row, int $owner): array
    {
        return ['id' => (int) $row->id, 'account_id' => (int) $row->account_id,
            'author' => DB::table('religious_accounts')->where('id', $row->account_id)->value('username') ?? 'من مجتمع حق',
            'parent_id' => $row->parent_id === null ? null : (int) $row->parent_id,
            'body' => $row->body, 'status' => $row->status, 'created_at' => $row->created_at,
            'mine' => (int) $row->account_id === $owner,
            'likes' => DB::table('religious_community_likes')->where('entry_id', $row->id)->count(),
            'liked' => DB::table('religious_community_likes')->where('entry_id', $row->id)->where('account_id', $owner)->exists(),
            'comments' => $this->visibleEntries($owner)->where('parent_id', $row->id)->count()];
    }

    public function createEntry(ReligiousAccount $account, string $operation, string $body, ?int $parent): array
    {
        return DB::transaction(function () use ($account, $operation, $body, $parent) {
            $owner = $this->actor($account, true);
            if ($parent !== null) {
                abort_unless($this->entry($parent, $owner)->parent_id === null, 422);
            }
            $existing = DB::table('religious_community_entries')->where('account_id', $owner)->where('operation_id', $operation)->first();
            if ($existing) {
                abort_if($existing->body !== $body || ($existing->parent_id === null ? null : (int) $existing->parent_id) !== $parent, 409);

                return $this->present($existing, $owner);
            }
            $id = DB::table('religious_community_entries')->insertGetId(['account_id' => $owner, 'operation_id' => $operation, 'parent_id' => $parent, 'body' => $body, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);

            return $this->present(DB::table('religious_community_entries')->find($id), $owner);
        }, 3);
    }

    public function publications(): Builder
    {
        $this->ensureReady();

        return DB::table('religious_publications')->whereIn('status', ['scheduled', 'published'])->whereNotNull('publish_at')->where('publish_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function publication(object $row): array
    {
        return ['id' => (int) $row->id, 'channel' => $row->channel, 'kind' => $row->kind, 'title' => $row->title,
            'body' => $row->body, 'content' => $row->content_id ? $this->content($row->content_id) : null,
            'media_url' => $row->media_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($row->media_path) : null,
            'youtube_id' => $row->youtube_id, 'link' => $row->link, 'published_at' => $row->publish_at];
    }

    public function publishDue(): int
    {
        $this->ensureReady();

        return DB::table('religious_publications')->where('status', 'scheduled')->where('publish_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->update(['status' => 'published', 'updated_at' => now()]);
    }
}
