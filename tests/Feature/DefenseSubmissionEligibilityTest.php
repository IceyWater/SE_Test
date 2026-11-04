<?php

use App\Models\ComprehensiveExamResult;
use App\Models\DefenseAcademicOutcome;
use App\Models\ExamApplication;
use App\Models\User;
use App\Services\DefenseSubmissionEligibility;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

require_once __DIR__.'/../Support/DefenseEligibilityFixtures.php';

beforeEach(function () {
    $this->withoutVite();
    Mail::fake();
    Storage::fake('public');
    $this->student = User::factory()->create(['role' => 'Student', 'program' => 'Master of Arts', 'school_id' => 'ELIGIBILITY-TEST']);
    $this->coordinator = User::factory()->create(['role' => 'Coordinator']);
    $this->adviser = User::factory()->create(['role' => 'Adviser']);
    $this->student->advisers()->attach($this->adviser->id, ['status' => 'accepted', 'requested_by' => $this->coordinator->id]);
});

it('does not mistake application approval for passing the exam and blocks both submission routes', function () {
    ExamApplication::create(['student_id' => $this->student->school_id, 'program' => $this->student->program, 'final_approval_status' => 'approved', 'permit_status' => 'approved']);
    $this->actingAs($this->student)->get('/defense-requirements')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('defenseEligibility.comprehensive_passed', false)->where('defenseEligibility.stages.Proposal.allowed', false));
    foreach (['Proposal', 'Prefinal', 'Final'] as $stage) {
        $this->postJson('/defense-requirements', ['defense_type' => $stage])->assertUnprocessable()->assertJsonValidationErrors('defense_type');
        $this->postJson('/defense-request', ['defenseType' => $stage])->assertUnprocessable()->assertJsonValidationErrors('defense_type');
    }
    $this->assertDatabaseCount('defense_requests', 0);
    expect(Storage::disk('public')->allFiles())->toBe([]);
});

it('requires each actual preceding defense outcome and does not treat a scheduled or completed workflow as passing', function () {
    passedComprehensiveFixture($this->student);
    $service = app(DefenseSubmissionEligibility::class);
    expect($service->forStudent($this->student)['stages']['Proposal']['allowed'])->toBeTrue()
        ->and($service->forStudent($this->student)['stages']['Prefinal']['allowed'])->toBeFalse();
    $proposal = passedDefenseStageFixture($this->student, 'Proposal');
    expect($service->forStudent($this->student)['stages']['Prefinal']['allowed'])->toBeTrue()
        ->and($service->forStudent($this->student)['stages']['Final']['allowed'])->toBeFalse();
    passedDefenseStageFixture($this->student, 'Prefinal');
    expect($service->forStudent($this->student)['stages']['Final']['allowed'])->toBeTrue();
    DefenseAcademicOutcome::where('defense_request_id', $proposal->id)->update(['result' => 'for-revision']);
    expect($service->forStudent($this->student)['stages']['Final']['allowed'])->toBeFalse();
});

it('rejects stale outcomes, other students and other programs as prerequisites', function () {
    passedComprehensiveFixture($this->student);
    $defense = passedDefenseStageFixture($this->student, 'Proposal');
    $service = app(DefenseSubmissionEligibility::class);
    $defense->scheduled_time = '07:00';
    $defense->saveQuietly();
    expect($service->forStudent($this->student)['stages']['Prefinal']['allowed'])->toBeFalse();
    $other = User::factory()->create(['role' => 'Student', 'program' => $this->student->program]);
    passedDefenseStageFixture($other, 'Proposal');
    expect($service->forStudent($this->student)['stages']['Prefinal']['allowed'])->toBeFalse();
    $this->student->program = 'Doctor in Business Management';
    $this->student->saveQuietly();
    expect($service->forStudent($this->student)['comprehensive_passed'])->toBeFalse();
});

it('only lets the assigned coordinator record a verified exam result and preserves revisions', function () {
    $payload = ['result' => 'passed', 'exam_date' => now('Asia/Manila')->subDay()->toDateString(), 'record_reference' => 'Verified Registrar exam record', 'verified_against_school_records' => true, 'version' => 0];
    $url = '/academic-progress/'.$this->student->id.'/comprehensive-result';
    foreach ([$this->student, $this->adviser, User::factory()->create(['role' => 'Dean']), User::factory()->create(['role' => 'Administrative Assistant']), User::factory()->create(['role' => 'Coordinator'])] as $other) {
        $this->actingAs($other)->patchJson($url, $payload)->assertForbidden();
    }
    $this->actingAs($this->coordinator)->patch($url, $payload)->assertSessionHasNoErrors();
    expect(app(DefenseSubmissionEligibility::class)->forStudent($this->student)['comprehensive_passed'])->toBeTrue();
    $this->patchJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('version');
    $payload['version'] = 1;
    $payload['result'] = 'failed';
    $this->patch($url, $payload)->assertSessionHasNoErrors();
    $result = ComprehensiveExamResult::firstOrFail();
    expect($result->history)->toHaveCount(1)->and($result->history[0]['result'])->toBe('passed')
        ->and(app(DefenseSubmissionEligibility::class)->forStudent($this->student)['comprehensive_passed'])->toBeFalse();
});

it('requires school verification and rejects future exam dates', function () {
    $payload = ['result' => 'passed', 'exam_date' => now('Asia/Manila')->addDay()->toDateString(), 'record_reference' => 'School record reference', 'version' => 0, 'verified_against_school_records' => false];
    $this->actingAs($this->coordinator)->patchJson('/academic-progress/'.$this->student->id.'/comprehensive-result', $payload)->assertUnprocessable()->assertJsonValidationErrors(['exam_date', 'verified_against_school_records']);
    $this->assertDatabaseCount('comprehensive_exam_results', 0);
});
