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
    // Mock AnnouncementService to avoid MySQL FIELD() incompatibility with SQLite
    $mock = Mockery::mock(AnnouncementService::class);
    $mock->shouldReceive('hasPendingMandatory')->andReturn(false);
    app()->instance(AnnouncementService::class, $mock);

    // Default test time: 08:55 AM (before late_mark_after of 09:15)
    $this->travelTo(today()->setTimeFromTimeString('08:55:00'));
});

// ── Helpers ──

function seedAttendanceContext(array $overrides = []): array
{
    $company = Company::create(['name' => 'Test Company']);

    // Store with GPS coordinates (Mumbai office)
    $store = Store::create(array_merge([
        'company_id' => $company->id,
        'name' => 'Mumbai Office',
        'office_lat' => 19.0760000,
        'office_lng' => 72.8777000,
        'gps_radius_meters' => 200,
    ], $overrides['store'] ?? []));

    // Shift: 9 AM to 6 PM
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

function scanStorePayload(Store $store, array $overrides = []): array
{
    return array_merge([
        'store_id' => $store->id,
        'latitude' => $store->office_lat,   // Exact store location
        'longitude' => $store->office_lng,
    ], $overrides);
}

// ── Success: Check-In ──

test('employee can check in via static qr scan', function () {
    $ctx = seedAttendanceContext();

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Attendance marked successfully.',
        ]);

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'store_id' => $ctx['store']->id,
        'status' => Attendance::STATUS_PRESENT,
        'check_in_method' => Attendance::METHOD_QR,
    ]);
});

test('successful check-in creates attendance log', function () {
    $ctx = seedAttendanceContext();

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $this->assertDatabaseHas('attendance_logs', [
        'employee_id' => $ctx['employee']->id,
        'action' => AttendanceLog::ACTION_CHECK_IN,
        'is_valid' => true,
    ]);
});

// ── Shift Rules: Late & Half Day ──

test('check-in after late threshold marks as late with message', function () {
    $ctx = seedAttendanceContext();

    // Travel to 09:20 (after late_mark_after of 09:15)
    $this->travelTo(today()->setTimeFromTimeString('09:20:00'));

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Attendance marked. You are 20 minutes late.');

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'status' => Attendance::STATUS_LATE,
    ]);
});

test('check-in after half day threshold marks as half day', function () {
    $ctx = seedAttendanceContext();

    // Travel to 10:45 (after half_day_after of 10:30)
    $this->travelTo(today()->setTimeFromTimeString('10:45:00'));

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('message', 'Marked as half day. You are 105 minutes late.');

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'status' => Attendance::STATUS_HALF_DAY,
    ]);
});

test('check-in without shift assigned marks as present', function () {
    $ctx = seedAttendanceContext(['employee' => ['shift_id' => null]]);

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $response->assertOk()
        ->assertJsonPath('message', 'Attendance marked successfully.');

    $this->assertDatabaseHas('attendances', [
        'employee_id' => $ctx['employee']->id,
        'status' => Attendance::STATUS_PRESENT,
    ]);
});

// ── Rejection: Already Checked In ──

test('second scan on same day returns already checked in', function () {
    $ctx = seedAttendanceContext();

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    // First scan — success
    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']))
        ->assertOk();

    // Second scan — rejected
    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Already checked in.',
        ]);

    // Verify rejection is logged
    $this->assertDatabaseHas('attendance_logs', [
        'employee_id' => $ctx['employee']->id,
        'is_valid' => false,
        'rejection_reason' => 'Already checked in for today.',
    ]);
});

// ── Rejection: Unauthorized Store ──

test('scan at unassigned store is rejected', function () {
    $ctx = seedAttendanceContext();

    // Create a second store the user is NOT assigned to
    $otherStore = Store::create([
        'company_id' => $ctx['company']->id,
        'name' => 'Delhi Office',
        'office_lat' => 28.6139000,
        'office_lng' => 77.2090000,
        'gps_radius_meters' => 200,
    ]);

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($otherStore));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthorized store.',
        ]);

    $this->assertDatabaseHas('attendance_logs', [
        'employee_id' => $ctx['employee']->id,
        'is_valid' => false,
        'rejection_reason' => 'Unauthorized store access.',
    ]);
});

// ── Rejection: Outside GPS Radius ──

test('scan outside gps radius is rejected', function () {
    $ctx = seedAttendanceContext();

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    // Send coordinates far from store (Delhi coords vs Mumbai store)
    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store'], [
            'latitude' => 28.6139000,
            'longitude' => 77.2090000,
        ]));

    $response->assertStatus(422)
        ->assertJsonPath('success', false);

    expect($response->json('message'))->toContain('You are outside allowed location');
});

// ── Rejection: Not Active Employee ──

test('inactive employee cannot scan', function () {
    $ctx = seedAttendanceContext(['employee' => ['status' => Employee::STATUS_INACTIVE]]);

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are not registered as an active employee.',
        ]);
});

// ── Rejection: User Without Employee Record ──

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

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($user)
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($store));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'You are not registered as an active employee.',
        ]);
});

// ── Validation: Request-Level ──

test('scan request requires store_id latitude and longitude', function () {
    $ctx = seedAttendanceContext();

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['store_id', 'latitude', 'longitude']);
});

test('scan request rejects store from different company', function () {
    $ctx = seedAttendanceContext();

    $otherCompany = Company::create(['name' => 'Other Company']);
    $otherStore = Store::create([
        'company_id' => $otherCompany->id,
        'name' => 'Other Store',
    ]);

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), [
            'store_id' => $otherStore->id,
            'latitude' => 19.0760000,
            'longitude' => 72.8777000,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['store_id']);
});

// ── GPS: Allowed When Not Configured ──

test('scan is allowed when store has no gps configured', function () {
    $ctx = seedAttendanceContext(['store' => [
        'office_lat' => null,
        'office_lng' => null,
        'gps_radius_meters' => null,
    ]]);

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $response = $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), [
            'store_id' => $ctx['store']->id,
            'latitude' => 28.6139000,
            'longitude' => 77.2090000,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);
});

// ── Tenant Isolation ──

test('attendance is created with correct company_id', function () {
    $ctx = seedAttendanceContext();

    $this->withoutMiddleware([CheckSubscription::class, EnsureValidStoreSession::class, CheckPendingAnnouncements::class]);

    $this->actingAs($ctx['user'])
        ->postJson(route('admin.hrm.attendance.scan-store'), scanStorePayload($ctx['store']));

    $this->assertDatabaseHas('attendances', [
        'company_id' => $ctx['company']->id,
        'employee_id' => $ctx['employee']->id,
    ]);
});
