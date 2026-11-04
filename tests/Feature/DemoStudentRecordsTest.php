<?php

use App\Models\StudentRecord;
use App\Models\User;
use Database\Seeders\DemoStudentRecordsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('adds reusable demo student records without altering accounts or creating payments', function () {
    $coordinator = User::factory()->create(['role' => 'Coordinator']);
    $existing = StudentRecord::create(['student_id' => 'EXISTING-001', 'first_name' => 'Existing', 'last_name' => 'Record', 'program' => 'MIT']);
    $this->seed(DemoStudentRecordsSeeder::class);
    $sample = StudentRecord::where('student_id', 'DEMO-RECORD-001')->firstOrFail();
    $sample->update(['first_name' => 'Edited Demo']);
    $this->seed(DemoStudentRecordsSeeder::class);
    $this->assertDatabaseCount('student_records', 9);
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('payment_records', 0);
    $this->assertDatabaseCount('defense_requests', 0);
    expect($sample->fresh()->first_name)->toBe('Edited Demo')->and($existing->fresh()->first_name)->toBe('Existing');
    $this->withoutVite();
    $this->actingAs($coordinator)->get('/student-records?program=MIT&year=2026')->assertOk()->assertInertia(fn (Assert $p) => $p
        ->has('records.data', 1)->where('records.data.0.student_id', 'DEMO-RECORD-002'));
    $this->get('/student-records?search=DEMO-RECORD-008')->assertOk()->assertInertia(fn (Assert $p) => $p->has('records.data', 1));
});

it('lets an unpaid student record be edited without inventing a receipt', function () {
    $coordinator = User::factory()->create(['role' => 'Coordinator']);
    $this->seed(DemoStudentRecordsSeeder::class);
    $record = StudentRecord::where('student_id', 'DEMO-RECORD-001')->firstOrFail();
    $this->actingAs($coordinator)->put('/student-records/'.$record->id, [
        'first_name' => 'Demo Mara Updated', 'last_name' => $record->last_name, 'program' => $record->program,
        'middle_name' => '', 'or_number' => '', 'payment_date' => '',
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect($record->fresh()->first_name)->toBe('Demo Mara Updated')
        ->and($record->fresh()->or_number)->toBeNull()->and($record->fresh()->payment_date)->toBeNull();
    $this->assertDatabaseCount('payment_records', 0);
});
