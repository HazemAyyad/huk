<?php

namespace Tests\Support;

use App\Models\AppAccount;
use App\Models\User;
use App\Services\Religious\AppAdminAccess;
use App\Services\Religious\AppSessionProof;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class AssociationTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'association_test', 'database.connections.association_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ], 'haq_app.enabled' => true, 'association.enabled' => true, 'cache.default' => 'array',
            'laratrust.cache.enabled' => false, 'queue.default' => 'sync', 'association.enforce_devices' => false]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2025_07_04_180710_laratrust_setup_tables.php');
        (new \LaratrustSetupTables)->up();
        foreach (['0001_01_01_000000_create_users_table', '2026_10_13_120000_create_religious_accounts_and_quran_sync',
            '2026_01_12_150326_create_personal_access_tokens_table', '2026_10_26_120000_extend_religious_account_identity',
            '2026_10_30_120000_create_haq_app_localization'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $this->legacyTables();
        (require database_path('migrations/2026_11_18_010000_create_association_app_workspace.php'))->up();
    }

    protected function tearDown(): void
    {
        DB::purge('association_test');
        parent::tearDown();
    }

    public function withToken($token, $type = 'Bearer')
    {
        $this->app['auth']->forgetGuards();

        return parent::withToken($token, $type);
    }

    protected function actor(?array $permissions = null, string $role = 'superadmin', bool $global = true, array $categories = [], bool $underlying = true): array
    {
        $permissions ??= array_keys(AppAdminAccess::PERMISSIONS);
        $staff = User::create(['name' => 'Staff', 'email' => Str::uuid().'@example.test', 'password' => 'testing-password']);
        $roleId = DB::table('roles')->where('name', $role)->value('id') ?? DB::table('roles')->insertGetId(['name' => $role, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $staff->id, 'user_type' => $staff->getMorphClass()]);
        foreach ($role === 'superadmin' ? [] : $permissions as $key) {
            $name = AppAdminAccess::PERMISSIONS[$key];
            DB::table('permissions')->updateOrInsert(['name' => $name], []);
            if ($underlying || $key === 'admin.enter') {
                $id = DB::table('permissions')->where('name', $name)->value('id');
                DB::table('permission_role')->updateOrInsert(['permission_id' => $id, 'role_id' => $roleId], []);
            }
        }
        $account = AppAccount::create(['user_id' => $staff->id, 'association_enabled' => true]);
        DB::table('app_admin_memberships')->insert(['app_account_id' => $account->id, 'active' => true,
            'permissions' => json_encode($permissions), 'locales' => '[]', 'association_global' => $global,
            'association_categories' => json_encode($categories), 'created_at' => now(), 'updated_at' => now()]);

        return [$account, $staff];
    }

    protected function token(AppAccount $account): string
    {
        $source = $this->personal($account);

        return $this->withToken($source)->postJson('/api/v2/admin/session')->assertOk()->json('data.token');
    }

    protected function personal(AppAccount $account): string
    {
        return $account->createToken('app-session', app(AppSessionProof::class)->abilities($account, 'association'), now()->addDay())->plainTextToken;
    }

    protected function category(string $name = 'Orphans'): int
    {
        return DB::table('categories')->insertGetId(['name' => $name, 'prefix' => $name.'-', 'last_number' => 0,
            'total_assigned' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function beneficiary(int $category, string $identity = '123456789'): int
    {
        return DB::table('telegram_users')->insertGetId(['full_name' => 'Beneficiary '.$identity, 'national_id' => $identity,
            'category_id' => $category, 'is_completed' => true, 'source' => 'system', 'benefit_count' => 0,
            'created_at' => now(), 'updated_at' => now()]);
    }

    private function legacyTables(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('telegram_user_id')->nullable();
            $table->string('title')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
        Schema::create('case_profiles', function (Blueprint $table): void {
            $table->id(); $table->string('key'); $table->string('name'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('case_profile_id')->nullable(); $table->string('name');
            $table->string('prefix'); $table->unsignedInteger('last_number')->default(0); $table->unsignedInteger('total_assigned')->default(0);
            $table->boolean('is_active')->default(true); $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable(); $table->timestamps();
        });
        Schema::create('telegram_users', function (Blueprint $table): void {
            $table->id(); $table->string('full_name'); $table->string('national_id'); $table->unsignedBigInteger('category_id')->nullable();
            $table->string('access_code')->nullable(); $table->string('source')->default('system'); $table->string('whatsapp')->nullable();
            $table->string('egypt_number')->nullable(); $table->string('telegram_number')->nullable(); $table->string('address')->nullable();
            $table->string('governorate')->nullable(); $table->string('city')->nullable(); $table->date('birth_date')->nullable();
            $table->unsignedInteger('family_members')->default(0); $table->unsignedInteger('benefit_count')->default(0);
            $table->boolean('is_completed')->default(false); $table->boolean('is_approved')->default(false); $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable(); $table->string('approval_description')->nullable();
            $table->string('personal_photo')->nullable();
            $table->string('chat_id')->nullable();
            $table->string('business_connection_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable(); $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('user_categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('telegram_user_id'); $table->unsignedBigInteger('category_id');
            $table->timestamps(); $table->unique(['telegram_user_id', 'category_id']);
        });
        Schema::create('family_members', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('telegram_user_id'); $table->string('full_name');
            $table->string('national_id')->nullable(); $table->string('relation')->nullable(); $table->date('birth_date')->nullable();
            $table->boolean('is_orphan')->default(false); $table->timestamps();
        });
        Schema::create('telegram_user_notes', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('telegram_user_id'); $table->text('note');
            $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable(); $table->timestamps();
        });
        Schema::create('events', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->date('event_date'); $table->time('event_time')->nullable();
            $table->string('aid_type')->default('in_kind'); $table->string('value_type')->default('text');
            $table->decimal('value', 12, 2)->nullable(); $table->string('value_text')->nullable(); $table->text('extras')->nullable();
            $table->string('location')->nullable(); $table->text('invitation_message')->nullable(); $table->text('default_message')->nullable();
            $table->boolean('is_archived')->default(false); $table->boolean('is_fixed')->default(false); $table->boolean('without_notification')->default(false);
            $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable(); $table->timestamps();
        });
        Schema::create('message_logs', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('telegram_user_id'); $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable(); $table->string('sender_type')->nullable(); $table->text('message');
            $table->string('message_type'); $table->string('delivery_status')->default('manual'); $table->timestamp('sent_at')->nullable();
            $table->timestamp('attended_at')->nullable(); $table->unsignedBigInteger('attended_by')->nullable();
            $table->timestamp('excused_at')->nullable(); $table->unsignedBigInteger('excused_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable(); $table->boolean('is_override')->default(false);
            $table->unsignedBigInteger('override_by')->nullable(); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('task_assignments', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('event_id'); $table->unsignedBigInteger('telegram_user_id');
            $table->string('role'); $table->string('status')->default('pending'); $table->timestamps();
        });
    }
}
