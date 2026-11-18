<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EmployeePaymentController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Support\AssociationTestCase;

final class AssociationPaymentsTest extends AssociationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('payments', function (Blueprint $table): void { $table->id(); $table->unsignedBigInteger('user_id'); $table->decimal('amount', 10, 2); $table->date('payment_date'); $table->timestamps(); });
        Schema::create('paid_salaries', function (Blueprint $table): void { $table->id(); $table->unsignedBigInteger('user_id'); $table->date('month'); $table->string('status'); $table->decimal('partial_amount', 10, 2); $table->timestamps(); });
    }
    public function test_native_payment_and_retry_use_the_existing_month_allocation_without_losing_decimal_remainders(): void
    {
        [$admin] = $this->actor();
        $employee = User::create(['name' => 'Employee', 'email' => 'employee@example.test', 'password' => 'test-password', 'basic_salary' => 100.25]);
        $this->withToken($this->token($admin));
        $input = ['operation_id' => (string) Str::uuid(), 'record' => ['user_id' => $employee->id, 'amount' => '250.75', 'payment_date' => '2026-01-10']];
        $this->postJson('/api/v2/admin/association/records/payments', $input)->assertOk();
        $this->postJson('/api/v2/admin/association/records/payments', $input)->assertOk();
        $this->assertSame(1, DB::table('payments')->count());
        $this->assertSame(2, DB::table('paid_salaries')->where('status', 'full')->count());
        $this->assertSame(50.25, (float) DB::table('paid_salaries')->where('status', 'partial')->value('partial_amount'));
        $this->assertSame(3, $employee->paidSalaries()->count());
        $this->postJson('/api/v2/admin/association/records/payments', [...$input, 'operation_id' => (string) Str::uuid(), 'record' => [...$input['record'], 'amount' => '1.999']])->assertUnprocessable();
    }
    public function test_existing_web_payment_store_calls_a_real_processor_and_accepts_only_validated_fields(): void
    {
        $employee = User::create(['name' => 'Employee', 'email' => 'employee@example.test', 'password' => 'test-password', 'basic_salary' => 100.25]);
        $response = app(EmployeePaymentController::class)->store(Request::create('/test', 'POST', ['user_id' => $employee->id, 'amount' => '100.50', 'payment_date' => '2026-01-01', 'id' => 900]));
        $this->assertSame(200, $response->status());
        $this->assertSame(1, DB::table('payments')->value('id'));
        $this->assertSame(.25, (float) DB::table('paid_salaries')->where('status', 'partial')->value('partial_amount'));
    }
}
