<?php

use App\Models\AaPaymentVerification;
use App\Models\DefenseRequest;
use App\Models\User;
use App\Services\AaReceiptQueue;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    $this->aa = User::factory()->create(['role' => 'Administrative Assistant']);
    $this->student = User::factory()->create(['role' => 'Student']);
    $this->defense = DefenseRequest::withoutEvents(fn () => DefenseRequest::create([
        'submitted_by' => $this->student->id, 'first_name' => 'Test', 'last_name' => 'Student',
        'school_id' => 'AA-TEST', 'program' => 'Master of Arts in Education',
        'defense_adviser' => 'Adviser Name', 'thesis_title' => 'AA actions', 'defense_type' => 'Proposal',
        'workflow_state' => 'scheduled', 'status' => 'Approved', 'coordinator_status' => 'Approved',
        'proof_of_payment' => 'defense_requirements/receipt.pdf', 'amount' => 11250,
        'scheduled_date' => '2026-11-03', 'scheduled_time' => '08:00', 'scheduled_end_time' => '12:00',
        'defense_venue' => 'Test room', 'scheduling_notes' => 'Test notes', 'defense_chairperson' => 'Test Chair',
        'workflow_history' => [['event_type' => 'scheduled', 'description' => 'Schedule saved']],
    ]));
});

it('restores missing receipt records without resetting existing decisions', function () {
    $queue = app(AaReceiptQueue::class);
    $queue->ensure($this->defense);
    $record = AaPaymentVerification::where('defense_request_id', $this->defense->id)->firstOrFail();
    $this->actingAs($this->aa)->postJson('/aa/payment-verifications/'.$record->id.'/status', ['status' => 'verified'])->assertOk();
    $queue->ensure($this->defense);
    expect($record->fresh()->status)->toBe('verified');
    expect(AaPaymentVerification::where('defense_request_id', $this->defense->id)->count())->toBe(1);
    $this->postJson('/aa/payment-verifications/'.$record->id.'/status', ['status' => 'rejected'])->assertOk();
    expect($record->fresh()->status)->toBe('rejected');
});

it('hydrates saved schedule committee history and receipt on the AA detail page', function () {
    app(AaReceiptQueue::class)->ensure($this->defense);
    $this->actingAs($this->aa)->get('/assistant/all-defense-list/'.$this->defense->id.'/details')->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('assistant/all-defense-list/details')
            ->where('defenseRequest.defense_venue', 'Test room')
            ->where('defenseRequest.scheduled_time', '08:00')
            ->where('defenseRequest.defense_chairperson', 'Test Chair')
            ->where('defenseRequest.expected_rate', 11250)
            ->has('defenseRequest.workflow_history', 1)
            ->where('defenseRequest.aa_verification_status', 'pending')
            ->where('defenseRequest.attachments.proof_of_payment', fn ($value) => str_contains($value, 'receipt.pdf')));
    $this->patchJson('/defense-requests/'.$this->defense->id.'/status', ['status' => 'Completed'])->assertForbidden();
    $this->get('/defense-register')->assertOk();
});

it('queues newly approved receipts through the shared save observer', function () {
    $this->defense->coordinator_status = 'Pending';
    $this->defense->saveQuietly();
    $this->defense->coordinator_status = 'Approved';
    $this->defense->save();
    expect(AaPaymentVerification::where('defense_request_id', $this->defense->id)->count())->toBe(1);
});

it('does not queue closed requests or requests without receipts', function () {
    $this->defense->workflow_state = 'adviser-rejected';
    app(AaReceiptQueue::class)->ensure($this->defense);
    expect(AaPaymentVerification::count())->toBe(0);
    $this->defense->workflow_state = 'scheduled';
    $this->defense->proof_of_payment = null;
    app(AaReceiptQueue::class)->ensure($this->defense);
    expect(AaPaymentVerification::count())->toBe(0);
});
