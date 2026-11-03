<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppAdminSchema;
use App\Services\Religious\AppManagedContent;
use App\Services\Religious\CommunityAdministration;
use App\Services\Religious\ReligiousCatalog;
use App\Services\Religious\ReligiousCommunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class AppWorkspaceController extends Controller
{
    private const PERMISSIONS = [
        'messages' => 'publications.manage', 'stories' => 'publications.manage', 'schedule' => 'publications.manage',
        'circles' => 'community.manage', 'entries' => 'community.moderate', 'reports' => 'community.moderate',
        'library' => 'library.manage', 'categories' => 'library.manage', 'moods' => 'library.manage', 'ramadan' => 'library.manage',
        'adhkar-items' => 'catalogs.manage', 'adhkar-groups' => 'catalogs.manage', 'sakinah' => 'catalogs.manage',
        'daily' => 'catalogs.manage', 'audio' => 'catalogs.manage',
        'tafsirs' => 'catalogs.manage',
        'accounts' => 'accounts.manage', 'push' => 'services.manage', 'assistant' => 'services.manage',
        'subscriptions' => 'services.manage', 'health' => 'services.manage', 'service-settings' => 'services.manage',
    ];

    private const TABLES = ['messages' => 'religious_publications', 'stories' => 'religious_publications', 'schedule' => 'religious_publications',
        'circles' => 'religious_circles', 'entries' => 'religious_community_entries', 'reports' => 'religious_community_reports'];

    public function __construct(private AppAdminAccess $access, private AppManagedContent $managed, private ReligiousCatalog $catalog) {}

    private function requireSection(Request $request, string $section): void
    {
        $permission = self::PERMISSIONS[$section] ?? abort(404);
        $grant = $this->access->require($request->user());
        $managed = in_array($section, ['adhkar-items', 'adhkar-groups', 'sakinah', 'daily', 'audio', 'tafsirs']);
        $readOrAction = $request->isMethod('get') || $request->route('id') !== null;
        abort_unless(in_array($permission, $grant->effective_permissions, true) || ($managed && $readOrAction && array_intersect(['catalogs.review', 'catalogs.publish'], $grant->effective_permissions)), 403);
        abort_unless(json_decode($grant->locales, true) === [], 403, 'هذه الإدارة تحتاج صلاحية عامة للتطبيق');
    }

    private function table(string $table): void
    {
        abort_unless(Schema::hasTable($table), 503, 'خدمة هذا القسم تحتاج تهيئة على الخادم');
    }

    public function overview(Request $request)
    {
        $grant = $this->access->require($request->user());
        $sections = json_decode($grant->locales, true) === []
            ? array_keys(array_filter(self::PERMISSIONS, fn ($p) => in_array($p, $grant->effective_permissions, true) || ($p === 'catalogs.manage' && array_intersect(['catalogs.review', 'catalogs.publish'], $grant->effective_permissions)))) : [];

        return response()->json(['data' => ['sections' => $sections]])->header('Cache-Control', 'no-store');
    }

    public function publicContent()
    {
        $payload = json_encode($this->managed->published(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return response()->json(['data' => ['schema' => 1, 'payload' => $payload, 'bytes' => strlen($payload), 'sha256' => hash('sha256', $payload)]])->header('Cache-Control', 'no-store');
    }

    public function index(Request $request, string $section)
    {
        $this->requireSection($request, $section);
        $query = $request->validate(['q' => 'nullable|string|max:100', 'page' => 'nullable|integer|min:1|max:100000', 'status' => 'nullable|string|max:40']);
        $state = $this->managed->state();
        $catalog = $this->catalog->all();
        $fields = AppAdminSchema::fields($section === 'schedule' ? 'messages' : $section, $catalog, $state['draft']);
        $data = ['fields' => $fields, 'items' => [], 'can_create' => count($fields) > 0 && ! in_array($section, ['sakinah', 'moods', 'service-settings', 'schedule', 'tafsirs']),
            'version' => $catalog['version'], 'revision' => $state['revision'], 'reviewed' => $state['reviewed_revision'] === $state['revision'],
            'managed' => in_array($section, ['adhkar-items', 'adhkar-groups', 'sakinah', 'daily', 'audio', 'tafsirs'])];
        $data['can_edit'] = in_array(self::PERMISSIONS[$section], $this->access->require($request->user())->effective_permissions, true);
        $data['can_create'] = $data['can_create'] && $data['can_edit'];
        if (isset(self::TABLES[$section])) {
            $table = self::TABLES[$section];
            $this->table($table);
            $rows = DB::table($table);
            if ($section === 'schedule') {
                $rows->whereIn('status', ['scheduled', 'published'])->where('publish_at', '>', now())->orderBy('publish_at');
            }
            if (in_array($section, ['messages', 'stories'])) {
                $rows->where('channel', $section === 'messages' ? 'message' : 'story');
            }
            if (! empty($query['status'])) {
                abort_unless(in_array($section, ['messages', 'stories', 'entries', 'reports']), 422);
                $rows->where('status', $query['status']);
            }
            if (! empty($query['q']) && in_array($section, ['messages', 'stories', 'circles'])) {
                $rows->where('title', 'like', '%'.$query['q'].'%');
            }
            $page = $rows->orderByDesc('id')->paginate(30);
            $data['items'] = $page->items();
            $data['total'] = $page->total();
            $data['has_more'] = $page->hasMorePages();
            $data['items'] = array_map(function ($row) use ($section) {
                $item = (array) $row;
                $item['_revision'] = hash('sha256', json_encode($item, JSON_THROW_ON_ERROR));
                if (in_array($section, ['messages', 'stories', 'schedule'])) {
                    $item['media_url'] = $row->media_path ? Storage::disk('public')->url($row->media_path) : null;
                    foreach (['publish_at', 'expires_at'] as $dateField) {
                        $item[$dateField] = $row->{$dateField} ? \Illuminate\Support\Carbon::parse($row->{$dateField}, config('app.timezone'))->toIso8601String() : null;
                    }
                }
                if ($section === 'circles') {
                    $item['contributions'] = (int) DB::table('religious_circle_counts')->where('circle_id', $row->id)->sum('amount');
                    $item['participants'] = DB::table('religious_circle_members')->where('circle_id', $row->id)->where('active', true)->count();
                }
                if ($section === 'entries' && $row->parent_id) {
                    $item['parent_body'] = DB::table('religious_community_entries')->where('id', $row->parent_id)->value('body');
                }
                if ($section === 'reports') {
                    $item['target_body'] = $row->target_type === 'entry' ? DB::table('religious_community_entries')->where('id', $row->target_id)->value('body') : null;
                }

                return $item;
            }, $data['items']);
        } elseif (in_array($section, ['library', 'categories', 'moods', 'ramadan'])) {
            $data['items'] = $catalog[match ($section) {
                'library' => 'items', 'ramadan' => 'ramadan_days', default => $section
            }];
        } elseif ($data['managed']) {
            $data['items'] = $this->managed->records($state['draft'], $section);
        } elseif ($section === 'accounts') {
            $this->table('app_accounts');
            $page = DB::table('app_accounts')->select('id', 'religious_account_id', 'active', 'association_enabled', 'religious_enabled', 'created_at')->orderByDesc('id')->paginate(30);
            $data['items'] = $page->items();
            $data['has_more'] = $page->hasMorePages();
            foreach ($data['items'] as $item) {
                $item->name = $item->religious_account_id
                    ? DB::table('religious_accounts')->where('id', $item->religious_account_id)->value('name')
                    : DB::table('app_accounts')->join('users', 'users.id', '=', 'app_accounts.user_id')->where('app_accounts.id', $item->id)->value('users.name');
                $item->suspended = Schema::hasTable('religious_community_accounts') && $item->religious_account_id
                    ? (bool) DB::table('religious_community_accounts')->where('account_id', $item->religious_account_id)->value('suspended') : false;
            }
        } elseif ($section === 'push') {
            $this->table('religious_push_outbox');
            $page = DB::table('religious_push_outbox')->select('id', 'publication_id', 'status', 'attempts', 'created_at', 'updated_at')->orderByDesc('id')->paginate(30);
            $data['items'] = $page->items();
            $data['has_more'] = $page->hasMorePages();
            $data['notice'] = 'قبول المزود لا يثبت وصول الجهاز. الحالات غير المؤكدة لا يعاد إرسالها تلقائيًا.';
        } elseif ($section === 'subscriptions') {
            $this->table('religious_subscriptions');
            $page = DB::table('religious_subscriptions')->select('id', 'account_id', 'platform', 'product', 'state', 'active', 'expires_at', 'verified_at')->orderByDesc('id')->paginate(30);
            $data['items'] = $page->items();
            $data['has_more'] = $page->hasMorePages();
        } elseif ($section === 'assistant') {
            $this->table('religious_assistant_budgets');
            $data['items'] = DB::table('religious_assistant_budgets')->orderByDesc('month')->limit(12)->get(['month', 'spent', 'reserved', 'halted'])->all();
            $data['notice'] = 'تقرير الاستخدام والميزانية بالدولار. لا يتضمن الأسئلة أو الأجوبة الخاصة.';
        } elseif ($section === 'service-settings') {
            $data['items'] = [['id' => 'limits', ...($state['settings'] ?? ['daily_limit' => config('religious_assistant.daily_limit'), 'monthly_limit' => config('religious_assistant.monthly_limit'), 'budget_usd' => (int) (config('religious_assistant.budget_micro_usd') / 1000000)])]];
            $data['notice'] = 'تغيير الحدود يحافظ على الاستخدام والمصروف المسجّل؛ تخفيض السقف قد يوقف الطلبات الجديدة.';
        } elseif ($section === 'health') {
            $data['items'] = [
                ['title' => 'مجتمع حق', 'status' => app(ReligiousCommunity::class)->ready() ? 'جاهز' : 'بانتظار التهيئة'],
                ['title' => 'إشعارات الرسائل', 'status' => config('religious_push.enabled') && Schema::hasTable('religious_push_outbox') && is_file((string) config('religious_push.credentials_path')) ? 'مهيأ للإرسال؛ راجع الطابور' : 'بانتظار التهيئة'],
                ['title' => 'المساعد', 'status' => app(\App\Services\Religious\ReligiousAssistant::class)->ready() ? 'جاهز' : 'بانتظار التهيئة'],
                ['title' => 'متجر Android', 'status' => config('religious_assistant.android.enabled') ? 'مفعّل في إعدادات الخادم' : 'بانتظار التهيئة'],
                ['title' => 'متجر iOS', 'status' => config('religious_assistant.ios.enabled') ? 'مفعّل في إعدادات الخادم' : 'بانتظار التهيئة'],
                ['title' => 'مزامنة النشاط', 'status' => config('religious_modules.sync_enabled') ? 'مفعّلة في إعدادات الخادم' : 'غير مفعّلة'],
            ];
        }
        if (in_array($section, ['categories', 'adhkar-groups'])) {
            $data['items'] = array_map(fn ($item, $index) => [...$item, 'position' => $index + 1], $data['items'], array_keys($data['items']));
        }
        if (! isset(self::TABLES[$section]) && ! empty($query['q'])) {
            $data['items'] = array_values(array_filter($data['items'], fn ($item) => str_contains(mb_strtolower(json_encode($item, JSON_UNESCAPED_UNICODE)), mb_strtolower($query['q']))));
        }

        return response()->json(['data' => $data])->header('Cache-Control', 'no-store');
    }

    public function save(Request $request, string $section)
    {
        $this->requireSection($request, $section);
        if (isset(self::TABLES[$section])) {
            abort_unless(in_array($section, ['messages', 'stories', 'circles']), 405);
            $this->table(self::TABLES[$section]);
            if (in_array($section, ['messages', 'stories'])) {
                $request->merge(['channel' => $section === 'messages' ? 'message' : 'story']);
                if ($request->input('status') !== 'draft') {
                    $this->access->require($request->user(), 'publications.publish');
                }
            }
            $id = $request->input('id');
            DB::transaction(function () use ($request, $section, $id): void {
                if ($id) {
                    $row = DB::table(self::TABLES[$section])->where('id', $id)->lockForUpdate()->first() ?? abort(404);
                    if (in_array($section, ['messages', 'stories'])) {
                        abort_unless($row->channel === $request->input('channel'), 404);
                        // Editing a visible/scheduled publication also requires publishing rights.
                        if ($row->status !== 'draft') {
                            $this->access->require($request->user(), 'publications.publish');
                        }
                    }
                    abort_unless(hash_equals(hash('sha256', json_encode((array) $row, JSON_THROW_ON_ERROR)), (string) $request->input('_revision')), 409, 'تغيّر السجل؛ حدّث الصفحة');
                }
                $service = app(CommunityAdministration::class);
                $section === 'circles' ? $service->circle($request) : $service->publication($request);
                $this->access->audit($request->user()->id, 'workspace.saved', $section.':'.($id ?: 'new'));
            });
        } elseif (in_array($section, ['library', 'categories', 'moods', 'ramadan'])) {
            $data = $request->validate(['record' => 'required|array', 'version' => 'required|string']);
            if ($section === 'library') {
                $this->catalog->saveItem($data['record'], $data['version']);
            } elseif ($section === 'categories') {
                $this->catalog->saveCategory($data['record'], $data['version']);
            } else {
                $this->catalog->savePlacement($section, $data['record'], $data['version']);
            }
            $this->access->audit($request->user()->id, 'workspace.saved', $section);
        } elseif ($section === 'service-settings') {
            $data = $request->validate(['revision' => 'required|integer|min:0', 'record.daily_limit' => 'required|integer|min:1|max:100',
                'record.monthly_limit' => 'required|integer|min:1|max:1000|gte:record.daily_limit', 'record.budget_usd' => 'sometimes|integer|min:3|max:100']);
            $this->managed->change($data['revision'], function ($state) use ($data) {
                $state['settings'] = array_replace($state['settings'] ?? [], $data['record']);
                $state['revision']++;
                $state['reviewed_revision'] = null;

                return $state;
            });
            $this->access->audit($request->user()->id, 'service.limits.updated', 'assistant', $data['record']);
            foreach (['daily_limit', 'monthly_limit'] as $key) {
                config(['religious_assistant.'.$key => $data['record'][$key]]);
            }
            if (isset($data['record']['budget_usd'])) {
                config(['religious_assistant.budget_micro_usd' => $data['record']['budget_usd'] * 1000000]);
            }
        } else {
            $data = $request->validate(['record' => 'required|array', 'revision' => 'required|integer|min:0']);
            $this->managed->save($section, $data['record'], $data['revision']);
            $this->access->audit($request->user()->id, 'catalog.draft.saved', $section);
        }

        return response()->json(['data' => ['saved' => true]]);
    }

    public function action(Request $request, string $section, string $id)
    {
        $this->requireSection($request, $section);
        $data = $request->validate(['action' => 'required|string', 'revision' => 'nullable|integer|min:0']);
        $action = $data['action'];
        if (in_array($section, ['adhkar-items', 'adhkar-groups', 'sakinah', 'daily', 'audio', 'tafsirs'])) {
            $this->access->require($request->user(), $action === 'review' ? 'catalogs.review' : 'catalogs.publish');
            abort_unless(in_array($action, ['review', 'publish']) && isset($data['revision']), 422);
            $action === 'review' ? $this->managed->review($data['revision']) : $this->managed->publish($data['revision']);
        } elseif (in_array($section, ['entries', 'reports'])) {
            $this->table(self::TABLES[$section]);
            DB::transaction(function () use ($section, $request, $id, $action): void {
                if ($section === 'entries') {
                    $request->merge(['status' => $action]);
                    app(CommunityAdministration::class)->review($request, (int) $id);
                } else {
                    app(CommunityAdministration::class)->report($request, (int) $id);
                }
                $this->access->audit($request->user()->id, 'community.'.$action, $section.':'.$id);
            });

            return response()->json(['data' => ['saved' => true]]);
        } elseif ($section === 'accounts') {
            $this->access->require($request->user(), 'community.moderate');
            $this->table('religious_community_accounts');
            abort_unless(in_array($action, ['suspend', 'restore']), 422);
            $account = DB::table('app_accounts')->find($id) ?? abort(404);
            abort_unless($account->religious_account_id, 422, 'لا يوجد حساب ديني مرتبط');
            DB::table('religious_community_accounts')->updateOrInsert(['account_id' => $account->religious_account_id], ['suspended' => $action === 'suspend']);
        } else {
            abort(405);
        }
        $this->access->audit($request->user()->id, 'workspace.'.$action, $section.':'.$id);

        return response()->json(['data' => ['saved' => true]]);
    }
}
