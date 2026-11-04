<?php

use App\Models\DefenseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    Http::preventStrayRequests();
    Mail::fake();
    $this->coordinator = User::factory()->create(['role' => 'Coordinator']);
    $this->student = User::factory()->create(['role' => 'Student']);
    $this->defense = DefenseRequest::withoutEvents(fn () => DefenseRequest::create([
        'submitted_by' => $this->student->id, 'coordinator_user_id' => $this->coordinator->id,
        'first_name' => 'Report', 'last_name' => 'Student', 'school_id' => 'REPORT-TEST', 'program' => 'Master of Arts in Education',
        'defense_adviser' => 'Test Adviser', 'thesis_title' => 'Report regression', 'defense_type' => 'Proposal', 'workflow_state' => 'adviser-approved',
        'status' => 'Pending', 'priority' => 'Medium', 'coordinator_status' => 'Pending', 'workflow_history' => [],
    ]));
});

it('opens the dean defense queue and details without a coordinator-only frontend restriction', function () {
    $dean = User::factory()->create(['role' => 'Dean']);
    $this->actingAs($dean)->get('/dean/defense-requests')->assertRedirect('/defense-requests');
    $this->get('/defense-requests')->assertOk()->assertInertia(fn (Assert $p) => $p
        ->component('coordinator/submissions/defense-request/Index')
        ->where('auth.user.role', 'Dean')->has('defenseRequests', 1)->where('defenseRequests.0.id', $this->defense->id));
    $this->get('/coordinator/defense-requests/'.$this->defense->id.'/details')->assertOk();
});

it('blocks AA Dean and unassigned coordinators from single and bulk coordinator changes', function () {
    foreach (['Dean', 'Administrative Assistant', 'Student', 'Adviser', 'Coordinator'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]));
        foreach (['Approved', 'Rejected', 'Pending', 'Completed'] as $status) {
            $this->patchJson('/defense-requests/'.$this->defense->id.'/status', ['status' => $status])->assertForbidden();
            $this->patchJson('/defense-requests/bulk-status', ['ids' => [$this->defense->id], 'status' => $status])->assertForbidden();
        }
        $this->postJson('/defense-requests/'.$this->defense->id.'/coordinator-decision', ['decision' => 'approve'])->assertForbidden();
        $this->patchJson('/defense-requests/'.$this->defense->id.'/priority', ['priority' => 'High'])->assertForbidden();
        $this->patchJson('/defense-requests/bulk-priority', ['ids' => [$this->defense->id], 'priority' => 'High'])->assertForbidden();
    }
    expect($this->defense->fresh()->workflow_state)->toBe('adviser-approved')
        ->and($this->defense->fresh()->priority)->toBe('Medium')
        ->and($this->defense->fresh()->workflow_history)->toBe([]);
});

it('rejects mixed-ownership bulk updates before changing any request', function () {
    $foreign = $this->defense->replicate();
    $foreign->coordinator_user_id = User::factory()->create(['role' => 'Coordinator'])->id;
    $foreign->saveQuietly();
    $this->actingAs($this->coordinator);
    $this->patchJson('/defense-requests/bulk-status', ['ids' => [$this->defense->id, $foreign->id], 'status' => 'Approved'])->assertForbidden();
    $this->patchJson('/defense-requests/bulk-priority', ['ids' => [$this->defense->id, $foreign->id], 'priority' => 'High'])->assertForbidden();
    expect($this->defense->fresh()->workflow_state)->toBe('adviser-approved')->and($foreign->fresh()->workflow_state)->toBe('adviser-approved');
});

it('still lets the assigned coordinator approve a request', function () {
    $this->actingAs($this->coordinator)->patchJson('/defense-requests/'.$this->defense->id.'/status', ['status' => 'Approved'])->assertOk();
    expect($this->defense->fresh()->workflow_state)->toBe('coordinator-approved');
});
