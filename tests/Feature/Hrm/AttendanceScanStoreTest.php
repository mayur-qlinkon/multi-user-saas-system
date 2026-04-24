<?php

use App\Http\Middleware\CheckPendingAnnouncements;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureValidStoreSession;
use App\Models\Company;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\AttendanceLog;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Shift;
use App\Models\Store;
use App\Models\User;
use App\Services\Hrm\AnnouncementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $mock = Mockery::mock(AnnouncementService::class);
    $mock->shouldReceive('hasPendingMandatory')->andReturn(false);
    app()->instance(AnnouncementService::class, $mock);

    $this->travelTo(today()->setTimeFromTimeString('08:55:00'));
});

function seedAttendanceContext(array $overrides = []): array
{
    $company = Company::create(['name' => 'Test Company']);

    $store = Store::create(array_merge([
        'company_id' => $company->id,
        'name' => 'Mumbai Office',
        'office_lat' => 19.0760000,
        'office_lng' => 72.8777000,
        'gps_radius_meters' => 200,
    ], $overrides['store'] ?? []));

    $shift = Shift::create([
        'company_id' => $company->id,
        'name' => 'General Shift',
        'code' => 'GEN',
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'late_mark_after' => '09:15:00',
        'half_day_after' => '10:30:00',
        'early_leave_before' => '17:30:00',
        'min_working_hours_minutes' => 480,
        'overtime_after_minutes' => 540,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);
    $user->stores()->attach($store->id);

    $employee = Employee::create(array_merge([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'store_id' => $store->id,
        'shift_id' => $shift->id,
        'employee_code' => 'EMP-0001',
        'date_of_joining' => now()->subYear(),
        'status' => Employee::STATUS_ACTIVE,
    ], $overrides['employee'] ?? []));

    return compact('company', 'store', 'shift', 'user', 'employee');
}

function scanPayload(Store $store, array $overrides = []): array
{
    return array_merge([
        'store_id' => $store->id,
        'latitude' => $store->office_lat,
        'longitude' => $store->office_lng,
    ], $overrides);
}

function withoutAttendanceMiddleware($testCase): void
{
    $testCase->withoutMiddleware([
        CheckSubscription::class,
        EnsureValidStoreSession::class,
        CheckPendingAnnouncements::class,
    ]);
}

test('employee can check in via the single attendance scan endpoint', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('action', AttendanceLog::ACTION_CHECK_IN)
        ->assertJsonPath('message', 'Check-in recorded successfully.');

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'store_id' => $ctx['store']->id,
        'status' => Attendance::STATUS_PRESENT,
        'check_in_method' => Attendance::METHOD_QR,
    ]);
});

test('successful check-in creates an attendance log', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']))
        ->assertOk();

    $this->assertDatabaseHas('attendance_logs', [
        'employee_id' => $ctx['employee']->id,
        'action' => AttendanceLog::ACTION_CHECK_IN,
        'is_valid' => true,
    ]);
});

test('check-in after late threshold marks employee as late', function () {
    $ctx = seedAttendanceContext();

    $this->travelTo(today()->setTimeFromTimeString('09:20:00'));
    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('message', 'Check-in recorded. You have been marked late.')
        ->assertJsonPath('type', 'warning');

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'status' => Attendance::STATUS_LATE,
    ]);
});

test('check-in after half day threshold marks employee as half day', function () {
    $ctx = seedAttendanceContext();

    $this->travelTo(today()->setTimeFromTimeString('10:45:00'));
    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('message', 'Check-in recorded. You have been marked half day.')
        ->assertJsonPath('type', 'warning');

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'status' => Attendance::STATUS_HALF_DAY,
    ]);
});

test('scan is rejected when no shift is assigned', function () {
    $ctx = seedAttendanceContext(['employee' => ['shift_id' => null]]);

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'No shift is assigned to your employee profile.',
        ]);
});

test('second scan after shift end performs check out', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']))
        ->assertOk();

    $this->travelTo(today()->setTimeFromTimeString('18:35:00'));

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('action', AttendanceLog::ACTION_CHECK_OUT)
        ->assertJsonPath('message', 'Check-out recorded successfully.');

    $attendance = Attendance::first();

    expect((float) $attendance->overtime_hours)->toBeGreaterThan(0.5);
    expect($attendance->check_out_time)->not->toBeNull();
});

test('early checkout requires confirmation and force checkout completes it', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']))
        ->assertOk();

    $this->travelTo(today()->setTimeFromTimeString('16:00:00'));

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('requires_confirmation', true)
        ->assertJsonPath('action', AttendanceLog::ACTION_CHECK_OUT);

    $confirmed = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store'], [
            'force_checkout' => true,
        ]));

    $confirmed->assertOk()
        ->assertJsonPath('action', AttendanceLog::ACTION_CHECK_OUT)
        ->assertJsonPath('type', 'warning');

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'status' => Attendance::STATUS_HALF_DAY,
        'check_out_method' => Attendance::METHOD_QR,
    ]);
});

test('third scan after completed attendance is rejected', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']))
        ->assertOk();

    $this->travelTo(today()->setTimeFromTimeString('18:10:00'));

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']))
        ->assertOk();

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Attendance already completed for this shift.',
        ]);
});

test('scan at unassigned store is rejected', function () {
    $ctx = seedAttendanceContext();

    $otherStore = Store::create([
        'company_id' => $ctx['company']->id,
        'name' => 'Delhi Office',
        'office_lat' => 28.6139000,
        'office_lng' => 77.2090000,
        'gps_radius_meters' => 200,
    ]);

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($otherStore));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are not assigned to this store.',
        ]);

    $this->assertDatabaseHas('attendance_logs', [
        'employee_id' => $ctx['employee']->id,
        'is_valid' => false,
        'rejection_reason' => 'You are not assigned to this store.',
    ]);
});

test('scan outside store gps radius is rejected', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store'], [
            'latitude' => 28.6139000,
            'longitude' => 77.2090000,
        ]));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are outside the allowed attendance radius for this store.',
        ]);
});

test('scan is rejected when store gps is not configured', function () {
    $ctx = seedAttendanceContext(['store' => [
        'office_lat' => null,
        'office_lng' => null,
        'gps_radius_meters' => null,
    ]]);

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), [
            'store_id' => $ctx['store']->id,
            'latitude' => 19.0760000,
            'longitude' => 72.8777000,
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'This store does not have attendance GPS settings configured.',
        ]);
});

test('inactive employee cannot scan', function () {
    $ctx = seedAttendanceContext(['employee' => ['status' => Employee::STATUS_INACTIVE]]);

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are not registered as an active employee.',
        ]);
});

test('user without employee record cannot scan', function () {
    $company = Company::create(['name' => 'Test Company']);
    $store = Store::create([
        'company_id' => $company->id,
        'name' => 'Test Store',
        'office_lat' => 19.0760000,
        'office_lng' => 72.8777000,
        'gps_radius_meters' => 200,
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
        'status' => 'active',
    ]);
    $user->stores()->attach($store->id);

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($user)
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($store));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are not registered as an active employee.',
        ]);
});

test('scan request requires store id latitude and longitude', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['store_id', 'latitude', 'longitude']);
});

test('scan request rejects store from another company', function () {
    $ctx = seedAttendanceContext();

    $otherCompany = Company::create(['name' => 'Other Company']);
    $otherStore = Store::create([
        'company_id' => $otherCompany->id,
        'name' => 'Other Store',
    ]);

    withoutAttendanceMiddleware($this);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), [
            'store_id' => $otherStore->id,
            'latitude' => 19.0760000,
            'longitude' => 72.8777000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['store_id']);
});

test('attendance record is created with the correct company id', function () {
    $ctx = seedAttendanceContext();

    withoutAttendanceMiddleware($this);

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan'), scanPayload($ctx['store']))
        ->assertOk();

    $this->assertDatabaseHas('attendances', [
        'company_id' => $ctx['company']->id,
        'employee_id' => $ctx['employee']->id,
    ]);
});
