<?php

use App\Models\CoordinatorProgram;
use App\Models\ExamApplication;
use App\Models\ExamApplicationSubject;
use App\Models\ExamProctor;
use App\Models\ExamRoom;
use App\Models\ExamSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Mail::fake();
});

function comprehensiveScheduleFixture(): array
{
    $student = User::factory()->create([
        'role' => 'Student',
        'school_id' => 'EXAM-SCHEDULE-'.Str::uuid(),
        'program' => 'Master of Arts',
    ]);
    $application = ExamApplication::create([
        'student_id' => $student->school_id,
        'school_year' => '2026-2027',
        'program' => $student->program,
        'workflow_status' => 'payment_verified',
        'payment_status' => 'verified',
        'created_at' => now(),
    ]);
    $subject = ExamApplicationSubject::create([
        'application_id' => $application->application_id,
        'subject_name' => 'Research Methods',
    ]);

    return [$student, $application, $subject];
}

function comprehensiveSchedulePayload(ExamRoom $room, User $proctor, string $start = '2030-01-15T09:00'): array
{
    return [
        'room_id' => $room->id,
        'starts_at' => $start,
        'ends_at' => substr($start, 0, 11).'11:00',
        'proctor_ids' => [$proctor->id],
    ];
}

function comprehensiveCoordinator(array $programs = ['Master of Arts']): User
{
    $coordinator = User::factory()->create(['role' => 'Coordinator']);
    foreach ($programs as $program) {
        CoordinatorProgram::create(['coordinator_id' => $coordinator->id, 'program' => $program]);
    }

    return $coordinator;
}

it('publishes the schedule only after every subject is scheduled and sends the student notice', function () {
    [$student, $application, $subject] = comprehensiveScheduleFixture();
    $coordinator = comprehensiveCoordinator();
    $room = ExamRoom::create(['code' => 'R-101', 'name' => 'Room 101', 'capacity' => 30, 'is_active' => true]);
    $proctor = User::factory()->create(['role' => 'Adviser']);

    $this->actingAs($coordinator)->post(
        route('coordinator.exam-schedules.assign', [$application->application_id, $subject->id]),
        comprehensiveSchedulePayload($room, $proctor)
    )->assertRedirect();

    $schedule = ExamSchedule::firstOrFail();
    expect($application->fresh()->workflow_status)->toBe('scheduled')
        ->and($schedule->status)->toBe('published')
        ->and(ExamProctor::firstOrFail()->status)->toBe('invited');
    $this->assertDatabaseHas('notifications', [
        'user_id' => $student->id,
        'exam_application_id' => $application->application_id,
        'title' => 'Comprehensive exam schedule published',
    ]);
    $this->actingAs($coordinator)->get(route('coordinator.exam-schedules.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('applications.0.subjects.0.schedule.room', 'Room 101'));
    $this->actingAs($student)->get(route('comprehensive-exam.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('application.subjects.0.room', 'Room 101'));

    $otherAdviser = User::factory()->create(['role' => 'Adviser']);
    $this->actingAs($proctor)->post(
        route('exam.proctor-invitations.respond', ExamProctor::firstOrFail()->id),
        ['decision' => 'accepted']
    )->assertRedirect();
    expect(ExamProctor::firstOrFail()->fresh()->status)->toBe('accepted');
    $this->actingAs($otherAdviser)->post(
        route('exam.proctor-invitations.respond', ExamProctor::firstOrFail()->id),
        ['decision' => 'declined', 'reason' => 'Not my invitation']
    )->assertNotFound();
});

it('rejects overlapping room bookings and coordinators outside the application program', function () {
    [, $firstApplication, $firstSubject] = comprehensiveScheduleFixture();
    [, $secondApplication, $secondSubject] = comprehensiveScheduleFixture();
    $coordinator = comprehensiveCoordinator();
    $outsider = comprehensiveCoordinator(['Another Program']);
    $room = ExamRoom::create(['code' => 'R-102', 'name' => 'Room 102', 'capacity' => 1, 'is_active' => true]);
    $firstProctor = User::factory()->create(['role' => 'Adviser']);
    $secondProctor = User::factory()->create(['role' => 'Adviser']);
    $payload = comprehensiveSchedulePayload($room, $firstProctor);

    $this->actingAs($coordinator)->post(
        route('coordinator.exam-schedules.assign', [$firstApplication->application_id, $firstSubject->id]),
        $payload
    )->assertRedirect();

    $this->post(
        route('coordinator.exam-schedules.assign', [$secondApplication->application_id, $secondSubject->id]),
        comprehensiveSchedulePayload($room, $secondProctor)
    )->assertConflict();
    expect(ExamSchedule::count())->toBe(1);

    $this->actingAs($outsider)->post(
        route('coordinator.exam-schedules.assign', [$secondApplication->application_id, $secondSubject->id]),
        comprehensiveSchedulePayload($room, $secondProctor, '2030-01-16T09:00')
    )->assertForbidden();
    expect(ExamSchedule::count())->toBe(1);
});

it('allows only Coordinators to create exam rooms', function () {
    $coordinator = comprehensiveCoordinator();
    $this->actingAs($coordinator)->post(route('coordinator.exam-schedules.rooms.store'), [
        'code' => 'SCI-204',
        'name' => 'Science Room 204',
        'building' => 'Science Hall',
        'capacity' => 24,
    ])->assertRedirect();
    $this->assertDatabaseHas('exam_rooms', ['code' => 'SCI-204', 'capacity' => 24]);

    $student = User::factory()->create(['role' => 'Student']);
    $this->actingAs($student)->post(route('coordinator.exam-schedules.rooms.store'), [
        'code' => 'SCI-205',
        'name' => 'Science Room 205',
        'capacity' => 24,
    ])->assertForbidden();
    $this->assertDatabaseMissing('exam_rooms', ['code' => 'SCI-205']);
});

it('keeps schedule and proctor pages within their assigned roles', function () {
    [$student, $application, $subject] = comprehensiveScheduleFixture();
    $coordinator = comprehensiveCoordinator();
    $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
    $adviser = User::factory()->create(['role' => 'Adviser']);
    $room = ExamRoom::create(['code' => 'RBAC-101', 'name' => 'RBAC Room', 'capacity' => 10, 'is_active' => true]);
    $payload = comprehensiveSchedulePayload($room, $adviser);

    $this->actingAs($coordinator)->get(route('coordinator.exam-schedules.index'))->assertOk();
    $this->actingAs($assistant)->get(route('coordinator.exam-schedules.index'))->assertForbidden();
    $this->actingAs($student)->get(route('coordinator.exam-schedules.index'))->assertForbidden();
    $this->actingAs($student)->post(
        route('coordinator.exam-schedules.assign', [$application->application_id, $subject->id]),
        []
    )->assertForbidden();
    expect(ExamSchedule::count())->toBe(0);

    $this->actingAs($adviser)->get(route('exam.proctor-invitations.index'))->assertOk();
    $this->actingAs($coordinator)->get(route('exam.proctor-invitations.index'))->assertForbidden();
    $this->actingAs($assistant)->post(
        route('coordinator.exam-schedules.assign', [$application->application_id, $subject->id]),
        $payload
    )->assertForbidden();
    expect(ExamSchedule::count())->toBe(0);

    $this->actingAs($coordinator)->post(
        route('coordinator.exam-schedules.assign', [$application->application_id, $subject->id]),
        $payload
    )->assertRedirect();
    $invitation = ExamProctor::firstOrFail();
    $this->actingAs($student)->post(route('exam.proctor-invitations.respond', $invitation), [])->assertForbidden();
    $this->actingAs($assistant)->post(route('exam.proctor-invitations.respond', $invitation), [])->assertForbidden();
});
