<?php

use App\Services\Association\AssociationModules;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_admin_memberships')) {
            Schema::table('app_admin_memberships', function (Blueprint $table): void {
                $table->boolean('association_global')->default(false);
                $table->json('association_categories')->nullable();
            });
        }
        Schema::create('association_identity_locks', function (Blueprint $table): void {
            $table->string('identity_key', 64)->primary();
            $table->timestamp('created_at');
        });
        Schema::create('association_profile_settings', function (Blueprint $table): void {
            $table->unsignedBigInteger('beneficiary_id')->primary();
            $table->json('editable_fields')->nullable();
            $table->timestamp('editable_until')->nullable();
            $table->string('personal_photo')->nullable();
            $table->unsignedBigInteger('opened_by')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
        });
        Schema::create('association_sequences', function (Blueprint $table): void {
            $table->string('module_key', 190)->primary();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });
        Schema::create('association_operations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->uuid('operation_id');
            $table->string('command', 150);
            $table->string('request_hash', 64);
            $table->longText('result')->nullable();
            $table->timestamps();
            $table->unique(['account_id', 'operation_id']);
        });
        Schema::create('association_deleted_records', function (Blueprint $table): void {
            $table->id();
            $table->string('module', 80);
            $table->string('record_id', 100);
            $table->unsignedBigInteger('staff_id');
            $table->timestamp('created_at');
            $table->unique(['module', 'record_id']);
        });
        Schema::create('association_legacy_receipts', function (Blueprint $table): void {
            $table->id();
            $table->string('module', 80);
            $table->unsignedBigInteger('record_id');
            $table->unsignedBigInteger('staff_id');
            $table->text('note')->nullable();
            $table->timestamp('received_at');
            $table->unique(['module', 'record_id']);
        });
        Schema::create('association_audit', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->unsignedBigInteger('staff_id')->nullable()->index();
            $table->string('module', 80)->index();
            $table->string('record_id', 100)->nullable()->index();
            $table->string('action', 60);
            $table->longText('before')->nullable();
            $table->longText('after')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->index();
        });
        Schema::create('association_event_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->unique();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('reply_deadline')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('reminder_minutes')->nullable();
            $table->json('audience')->nullable();
            $table->timestamp('reactivated_at')->nullable();
            $table->unsignedBigInteger('reactivated_by')->nullable();
            $table->text('reactivation_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('association_invitation_states', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('message_log_id')->unique();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('receipt_confirmed_at')->nullable();
            $table->string('response', 20)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
        Schema::create('association_scan_history', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id')->index();
            $table->unsignedBigInteger('staff_id')->index();
            $table->unsignedBigInteger('beneficiary_id')->nullable()->index();
            $table->unsignedBigInteger('event_id')->nullable()->index();
            $table->string('mode', 30);
            $table->string('input_hash', 64);
            $table->string('result', 40);
            $table->uuid('operation_id')->nullable()->index();
            $table->timestamp('created_at')->index();
            $table->unique(['account_id', 'operation_id']);
        });
        Schema::create('association_uploads', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('account_id')->index();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('module', 80);
            $table->string('context', 20)->default('admin');
            $table->string('record_id', 100)->nullable()->index();
            $table->string('field', 100)->nullable();
            $table->string('path');
            $table->string('name');
            $table->string('mime', 150);
            $table->unsignedBigInteger('bytes');
            $table->string('sha256', 64);
            $table->timestamps();
        });
        Schema::create('association_upload_transfers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('module', 80);
            $table->string('field', 100);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('context', 20)->default('admin');
            $table->unsignedBigInteger('form_id')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('bytes');
            $table->string('sha256', 64);
            $table->unsignedInteger('chunk_bytes');
            $table->string('status', 30)->default('uploading');
            $table->uuid('upload_id')->nullable();
            $table->string('error', 100)->nullable();
            $table->timestamps();
        });
        Schema::create('association_upload_chunks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('transfer_id')->index();
            $table->unsignedBigInteger('part');
            $table->unsignedInteger('bytes');
            $table->string('sha256', 64);
            $table->string('path');
            $table->unique(['transfer_id', 'part']);
        });
        Schema::create('association_import_previews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('module', 80);
            $table->longText('payload');
            $table->string('sha256', 64);
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::create('association_forms', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->json('fields');
            $table->json('audience')->nullable();
            $table->json('review_categories')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('requires_location')->default(false);
            $table->unsignedInteger('revision')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('association_form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('form_id')->index();
            $table->unsignedBigInteger('beneficiary_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->json('answers');
            $table->json('form_snapshot')->nullable();
            $table->json('editable_fields')->nullable();
            $table->json('submission_location')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->text('reason')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['form_id', 'beneficiary_id']);
        });
        Schema::create('association_notifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('created_account_id')->nullable()->index();
            $table->string('title');
            $table->text('body');
            $table->string('image')->nullable();
            $table->json('link')->nullable();
            $table->json('audience');
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->boolean('requires_confirmation')->default(false);
            $table->unsignedInteger('first_reminder_minutes')->nullable();
            $table->unsignedInteger('reminder_interval_minutes')->nullable();
            $table->unsignedSmallInteger('reminder_count')->default(0);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
        });
        Schema::create('association_notification_recipients', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('notification_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->unsignedBigInteger('beneficiary_id')->nullable()->index();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('reminders_sent')->default(0);
            $table->unsignedSmallInteger('reminders_queued')->default(0);
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->timestamps();
            $table->unique(['notification_id', 'account_id']);
        });
        Schema::create('association_audience_previews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('purpose', 40);
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('record_id');
            $table->string('revision', 64);
            $table->string('fingerprint', 64);
            $table->unsignedBigInteger('recipient_count');
            $table->timestamp('expires_at');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
        Schema::create('association_push_attempts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('recipient_id')->index();
            $table->unsignedBigInteger('device_id')->index();
            $table->unsignedInteger('reminder')->default(0);
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable()->index();
            $table->uuid('claim')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->string('provider_receipt')->nullable();
            $table->string('failure', 100)->nullable();
            $table->timestamps();
            $table->unique(['recipient_id', 'device_id', 'reminder'], 'association_push_recipient_device_reminder');
        });
        Schema::create('association_chat_grants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('beneficiary_account_id')->index();
            $table->unsignedBigInteger('staff_account_id')->index();
            $table->unsignedBigInteger('granted_by');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['beneficiary_account_id', 'staff_account_id']);
        });
        Schema::create('association_threads', function (Blueprint $table): void {
            $table->id();
            $table->string('kind', 20)->index();
            $table->string('subject');
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('support_category_id')->nullable()->index();
            $table->unsignedBigInteger('owner_account_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('status', 20)->default('open')->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
        });
        Schema::create('association_direct_slots', function (Blueprint $table): void {
            $table->string('pair', 100)->primary();
            $table->unsignedBigInteger('thread_id')->nullable();
            $table->timestamps();
        });
        Schema::create('association_thread_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('thread_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('role', 20)->default('member');
            $table->unsignedBigInteger('read_through')->default(0);
            $table->unsignedBigInteger('delivered_through')->default(0);
            $table->timestamp('typing_until')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['thread_id', 'account_id']);
        });
        Schema::create('association_thread_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('thread_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->uuid('client_id');
            $table->text('body')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->unique(['account_id', 'client_id']);
        });
        Schema::create('association_support_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->json('staff_accounts');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('association_support_slots', function (Blueprint $table): void {
            $table->unsignedBigInteger('account_id')->primary();
            $table->unsignedBigInteger('thread_id')->nullable();
            $table->timestamps();
        });
        Schema::create('association_calls', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('caller_id')->index();
            $table->unsignedBigInteger('callee_id')->index();
            $table->string('status', 20)->index();
            $table->string('room')->unique();
            $table->timestamp('ringing_at');
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamps();
        });
        Schema::create('association_account_policies', function (Blueprint $table): void {
            $table->unsignedBigInteger('account_id')->primary();
            $table->boolean('app_enabled')->default(true);
            $table->unsignedSmallInteger('device_limit')->nullable();
            $table->timestamps();
        });
        Schema::create('association_devices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('device_hash', 64);
            $table->string('label', 150)->nullable();
            $table->string('platform', 30)->nullable();
            $table->text('fcm_token')->nullable();
            $table->string('fcm_hash', 64)->nullable()->unique();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['account_id', 'device_hash']);
        });
        Schema::create('association_device_tokens', function (Blueprint $table): void {
            $table->unsignedBigInteger('token_id')->primary();
            $table->unsignedBigInteger('device_id')->index();
        });
        Schema::create('association_usage_days', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->date('day');
            $table->timestamp('last_seen_at');
            $table->unique(['account_id', 'day']);
        });
        Schema::create('association_preferences', function (Blueprint $table): void {
            $table->string('key', 150)->primary();
            $table->longText('value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        if (Schema::hasTable('permissions') && Schema::hasTable('roles')) {
            $timestamp = now();
            DB::table('permissions')->insertOrIgnore(array_map(fn ($name) => ['name' => $name, 'created_at' => $timestamp, 'updated_at' => $timestamp], array_values(AssociationModules::PERMISSIONS)));
            $permissionIds = DB::table('permissions')->whereIn('name', array_values(AssociationModules::PERMISSIONS))->pluck('id', 'name')->all();
            $roleIds = DB::table('roles')->pluck('id', 'name')->all();
            $bindings = [];
            foreach (AssociationModules::DEFINITIONS as $module => $definition) {
                foreach (explode(',', $definition['actions']) as $action) {
                    if (in_array($action, ['reactivate', 'purge', 'archive', 'delete'], true) && in_array($module, ['events', 'beneficiaries', 'chat'], true)) {
                        $roles = ['superadmin'];
                    } else {
                        $roles = explode('|', $definition['roles']);
                    }
                    $name = AssociationModules::PERMISSIONS["association.$module.$action"];
                    $permissionId = $permissionIds[$name];
                    foreach (array_intersect_key($roleIds, array_flip($roles)) as $roleId) {
                        $bindings[$roleId.':'.$permissionId] = ['permission_id' => $permissionId, 'role_id' => $roleId];
                    }
                }
            }
            $entry = $permissionIds[AssociationModules::PERMISSIONS['association.enter']];
            $roles = array_unique(explode('|', implode('|', array_column(AssociationModules::DEFINITIONS, 'roles'))));
            foreach (array_intersect_key($roleIds, array_flip($roles)) as $roleId) {
                $bindings[$roleId.':'.$entry] = ['permission_id' => $entry, 'role_id' => $roleId];
            }
            foreach (array_chunk(array_values($bindings), 500) as $chunk) {
                DB::table('permission_role')->insertOrIgnore($chunk);
            }
            if (Schema::hasTable('app_admin_memberships')) {
                foreach (DB::table('app_admin_memberships')->get() as $membership) {
                    if (json_decode($membership->locales, true) !== []) {
                        continue;
                    }
                    $staff = DB::table('app_accounts')->where('id', $membership->app_account_id)->value('user_id');
                    $super = DB::table('role_user')->join('roles', 'roles.id', '=', 'role_user.role_id')
                        ->where('user_id', $staff)->where('user_type', (new User)->getMorphClass())->where('roles.name', 'superadmin')->exists();
                    if ($staff && $super) {
                        DB::table('app_admin_memberships')->where('id', $membership->id)->update([
                            'association_global' => true, 'association_categories' => '[]',
                            'permissions' => json_encode(array_values(array_unique([...json_decode($membership->permissions, true), ...array_keys(AssociationModules::PERMISSIONS)]))),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Operational history, submissions, grants and messages are retained on rollback.
        // A deliberate data export/removal is required before retiring this workspace.
    }
};
