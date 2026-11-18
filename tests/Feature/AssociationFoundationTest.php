<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Services\Association\AssociationAccess;
use App\Services\Association\AssociationIdentities;
use App\Services\Association\AssociationOperations;
use App\Services\Association\AssociationRecords;
use App\Services\Religious\AppAdminAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\AssociationTestCase;

final class AssociationFoundationTest extends AssociationTestCase
{
    public function test_app_membership_never_replaces_underlying_staff_permissions(): void
    {
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.beneficiaries.view'], 'limited', false, [1], false);
        $this->assertNotContains('association.enter', app(AppAdminAccess::class)->grant($account)->effective_permissions);
        $this->assertFalse(app(AssociationAccess::class)->can($account, 'beneficiaries'));
    }

    public function test_scoped_staff_only_read_beneficiaries_in_their_own_categories(): void
    {
        $allowed = $this->category('Allowed');
        $hidden = $this->category('Hidden');
        $first = $this->beneficiary($allowed);
        $this->beneficiary($hidden, '987654321');
        [$account] = $this->actor(['admin.enter', 'association.enter', 'association.beneficiaries.view'], 'scoped', false, [$allowed]);
        $grant = app(AssociationAccess::class)->require($account, 'beneficiaries');
        $this->assertSame([$first], app(AssociationRecords::class)->query('beneficiaries', $grant)->pluck('id')->all());
        DB::table('user_categories')->insert(['telegram_user_id' => $first, 'category_id' => $hidden, 'created_at' => now(), 'updated_at' => now()]);
        $this->assertFalse(app(AssociationAccess::class)->can($account, 'staff'));
    }

    public function test_locale_only_membership_does_not_open_association_records(): void
    {
        [$account] = $this->actor();
        DB::table('app_admin_memberships')->where('app_account_id', $account->id)->update(['locales' => '["en"]']);
        $this->assertFalse(app(AssociationAccess::class)->can($account, 'beneficiaries'));
    }

    public function test_replaying_an_operation_has_one_effect_and_rejects_reused_ids(): void
    {
        [$account] = $this->actor();
        $category = $this->category();
        $operations = app(AssociationOperations::class);
        $input = ['operation_id' => (string) Str::uuid(), 'record' => ['b' => 2, 'a' => 1]];
        $apply = function () use ($category): array {
            DB::table('categories')->where('id', $category)->increment('last_number');

            return ['saved' => true];
        };
        $this->assertSame(['saved' => true], $operations->run($account, 'category.issue', $input, $apply));
        $input['record'] = ['a' => 1, 'b' => 2];
        $this->assertSame(['saved' => true], $operations->run($account, 'category.issue', $input, $apply));
        $this->assertSame(1, DB::table('categories')->where('id', $category)->value('last_number'));
        try {
            $operations->run($account, 'category.delete', $input, $apply);
            $this->fail('Operation identifiers must be bound to their original command.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
    }

    public function test_a_failed_operation_rolls_back_every_database_write(): void
    {
        [$account] = $this->actor();
        try {
            app(AssociationOperations::class)->run($account, 'failing', ['operation_id' => (string) Str::uuid()], function (): array {
                $this->category('Must roll back');
                throw ValidationException::withMessages(['record' => 'Invalid']);
            });
        } catch (ValidationException) {
            $this->assertSame(0, DB::table('categories')->count());
            $this->assertSame(0, DB::table('association_operations')->count());
        }
    }

    public function test_audit_logs_do_not_store_passwords_or_tokens(): void
    {
        [$account] = $this->actor();
        $grant = app(AssociationAccess::class)->require($account, 'beneficiaries');
        app(AssociationOperations::class)->audit($account, $grant, 'staff', 1, 'update', null,
            ['name' => 'Allowed', 'password' => 'never-store', 'nested' => ['access_token' => 'secret', 'value' => 4]]);
        $after = DB::table('association_audit')->value('after');
        $this->assertStringNotContainsString('never-store', $after);
        $this->assertStringNotContainsString('secret', $after);
        $this->assertSame(4, json_decode($after, true)['nested']['value']);
    }

    public function test_staff_attribution_uses_the_staff_id_instead_of_the_aggregate_account_id(): void
    {
        User::create(['name' => 'Other', 'email' => 'other@example.test', 'password' => 'testing-password']);
        [$account, $staff] = $this->actor();
        $this->assertNotSame($account->id, $staff->id);
        $this->actingAs($account);
        $category = Category::create(['name' => 'New', 'prefix' => 'N', 'is_active' => true]);
        $this->assertSame($staff->id, $category->created_by);
        $category->update(['name' => 'Changed']);
        $this->assertSame($staff->id, $category->updated_by);
    }

    public function test_identity_uniqueness_includes_family_members_and_arabic_digits(): void
    {
        $category = $this->category();
        $id = $this->beneficiary($category);
        DB::table('family_members')->insert(['telegram_user_id' => $id, 'full_name' => 'Child', 'national_id' => '987654321', 'created_at' => now(), 'updated_at' => now()]);
        $identities = app(AssociationIdentities::class);
        $this->assertSame('123456789', $identities->normalize('١٢٣٤٥٦٧٨٩'));
        DB::transaction(fn () => $identities->lock(['١٢٣٤٥٦٧٨٩', '123456789']));
        $this->assertSame(1, DB::table('association_identity_locks')->count());
        $this->expectException(ValidationException::class);
        $identities->available('٩٨٧٦٥٤٣٢١');
    }
}
