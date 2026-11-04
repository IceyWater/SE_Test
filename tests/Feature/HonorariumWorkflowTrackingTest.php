<?php

use App\Mail\HonorariumFollowUp;
use App\Models\DefenseRequest;
<<<<<<< Updated upstream
use App\Models\FinanceDocumentSubmission;
=======
>>>>>>> Stashed changes
use App\Models\HonorariumActivity;
use App\Models\HonorariumDisbursement;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    Mail::fake();
});

<<<<<<< Updated upstream
function trackedDefenseRequest(array $attributes = []): DefenseRequest
{
    $student = User::factory()->create(['role' => 'Student']);
    $adviser = User::factory()->create(['role' => 'Adviser']);
    $coordinator = User::factory()->create(['role' => 'Coordinator']);

    return DefenseRequest::withoutEvents(fn () => DefenseRequest::create(array_merge([
        'submitted_by' => $student->id,
        'adviser_user_id' => $adviser->id,
        'coordinator_user_id' => $coordinator->id,
        'first_name' => 'Honorarium',
        'last_name' => 'Student',
        'school_id' => 'HONORARIUM-TEST',
        'program' => 'Master of Arts in Education',
        'thesis_title' => 'Honorarium tracking request',
        'defense_type' => 'Proposal',
        'defense_adviser' => 'Tracking Adviser',
        'proof_of_payment' => 'receipt.pdf',
        'workflow_state' => 'scheduled',
        'status' => 'Approved',
    ], $attributes)));
}

it('shows defense progress, faculty and semester data, and Dean program totals', function () {
    $request = trackedDefenseRequest();
    $dean = User::factory()->create(['role' => 'Dean']);
    FinanceDocumentSubmission::create([
        'defense_request_id' => $request->id,
        'document_type' => 'payment_receipt',
        'status' => 'verified',
        'payload' => ['receipt_path' => $request->proof_of_payment],
    ]);
    FinanceDocumentSubmission::create([
        'defense_request_id' => $request->id,
        'document_type' => 'panel_breakdown',
        'status' => 'verified',
        'payload' => ['panel_names' => [], 'panel_affiliations' => []],
    ]);
    HonorariumDisbursement::create([
        'defense_request_id' => $request->id,
        'recipient_key' => 'adviser:'.$request->adviser_user_id,
        'recipient_name' => 'Tracking Adviser',
        'recipient_role' => 'Adviser',
        'amount' => 1250,
        'school_year' => '2026-2027',
        'semester' => '1st',
        'status' => 'prepared',
        'prepared_by' => $dean->id,
        'history' => [],
    ]);

    $this->actingAs($dean)->get('/honorarium-workspace')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('canViewReports', true)
        ->where('items.0.faculty_names.0', $request->adviserUser->name)
        ->where('items.0.school_year', '2026-2027')
        ->where('items.0.semester', '1st')
        ->where('items.0.progress_stage', 5)
        ->where('items.0.progress_percent', 63)
        ->where('programReports.0.recipients_count', 1)
        ->where('programReports.0.program', 'Master of Arts in Education'));
});

it('records manual follow-ups, timestamps them, creates notifications, and emails the selected role', function () {
    $request = trackedDefenseRequest();
    $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
    $finance = User::factory()->create(['role' => 'Finance', 'email' => 'finance-follow-up@example.test']);
    $notes = 'Please confirm receipt verification status.';

    $this->actingAs($assistant)->post("/honorarium-workspace/{$request->id}/follow-ups", [
        'recipient_role' => 'Finance',
        'notes' => $notes,
    ])->assertRedirect();

    $this->assertDatabaseHas('honorarium_activities', [
        'defense_request_id' => $request->id,
        'user_id' => $assistant->id,
        'activity_type' => 'follow_up',
        'recipient_role' => 'Finance',
        'description' => $notes,
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $finance->id,
        'defense_request_id' => $request->id,
        'type' => 'honorarium_follow_up',
    ]);
    expect(HonorariumActivity::where('defense_request_id', $request->id)->value('occurred_at'))->not->toBeNull();
    Mail::assertSent(HonorariumFollowUp::class, fn (HonorariumFollowUp $mail) => $mail->hasTo($finance->email));
});

it('does not allow students to create honorarium follow-ups', function () {
    $request = trackedDefenseRequest();

    $this->actingAs(User::factory()->create(['role' => 'Student']))->postJson("/honorarium-workspace/{$request->id}/follow-ups", [
        'recipient_role' => 'Finance',
        'notes' => 'Unauthorized follow-up attempt.',
    ])->assertForbidden();

    expect(HonorariumActivity::where('defense_request_id', $request->id)->count())->toBe(0);
});

it('allows Dean report downloads and denies other roles', function () {
    $request = trackedDefenseRequest();
    $dean = User::factory()->create(['role' => 'Dean']);
    HonorariumDisbursement::create([
        'defense_request_id' => $request->id,
        'recipient_key' => 'adviser:'.$request->adviser_user_id,
        'recipient_name' => 'Tracking Adviser',
        'recipient_role' => 'Adviser',
        'amount' => 1250,
        'school_year' => '2026-2027',
        'semester' => '1st',
        'status' => 'prepared',
        'prepared_by' => $dean->id,
        'history' => [],
    ]);

    $this->actingAs(User::factory()->create(['role' => 'Administrative Assistant']))
        ->get('/honorarium-workspace/reports.csv')->assertForbidden();
    $response = $this->actingAs($dean)->get('/honorarium-workspace/reports.csv')->assertOk();
    expect($response->streamedContent())->toContain('2026-2027', '1st', 'Master of Arts in Education');
});

it('requires a meaningful follow-up note', function () {
    $request = trackedDefenseRequest();
    $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
    $this->actingAs($assistant)->postJson("/honorarium-workspace/{$request->id}/follow-ups", [
        'recipient_role' => 'Finance',
        'notes' => ' ',
    ])->assertUnprocessable()->assertJsonValidationErrors('notes');

    expect(HonorariumActivity::where('defense_request_id', $request->id)->count())->toBe(0);
=======
function trackingDefense(User $student): DefenseRequest
{
    return DefenseRequest::withoutEvents(fn () => DefenseRequest::create([
        'submitted_by' => $student->id,
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'school_id' => $student->school_id ?: 'TRACKING-TEST-'.$student->id,
        'program' => 'Master of Arts in Education',
        'thesis_title' => 'Honorarium tracking test',
        'date_of_defense' => now()->toDateString(),
        'mode_defense' => 'Face-to-face',
        'defense_type' => 'Proposal',
        'defense_adviser' => 'Test Adviser',
        'defense_chairperson' => 'Test Chair',
        'defense_panelist1' => 'Test Panelist',
        'proof_of_payment' => 'defense_requirements/receipt.pdf',
        'workflow_state' => 'coordinator-approved',
        'status' => 'Approved',
    ]));
}

function trackingPayment(DefenseRequest $defense, User $preparedBy, string $status, string $key): HonorariumDisbursement
{
    return HonorariumDisbursement::create([
        'defense_request_id' => $defense->id,
        'recipient_key' => $key,
        'recipient_name' => 'Panel Member '.$key,
        'recipient_email' => 'panel-'.$key.'@example.test',
        'recipient_role' => 'Panel Member',
        'amount' => 1000,
        'school_year' => '2026-2027',
        'semester' => '1st',
        'status' => $status,
        'requires_review' => false,
        'history' => [],
        'prepared_by' => $preparedBy->id,
    ]);
}

it('records manual follow-ups, timestamps them, creates notifications, and emails the selected office', function () {
    $student = User::factory()->create(['role' => 'Student', 'first_name' => 'Test', 'last_name' => 'Student']);
    $aa = User::factory()->create(['role' => 'Administrative Assistant']);
    $finance = User::factory()->create(['role' => 'Finance']);
    $defense = trackingDefense($student);

    $this->actingAs($aa)->post('/honorarium-workspace/'.$defense->id.'/follow-ups', [
        'recipient_role' => 'Finance',
        'notes' => 'Please verify the submitted receipt.',
    ])->assertRedirect()->assertSessionHas('success', 'Follow-up emailed and recorded.');

    Mail::assertSent(HonorariumFollowUp::class, fn ($mail) => $mail->hasTo($finance->email)
        && $mail->recipientRole === 'Finance'
        && $mail->requestId === $defense->id);
    $activity = HonorariumActivity::where('activity_type', 'follow_up')->firstOrFail();
    expect($activity->recipient_role)->toBe('Finance')
        ->and($activity->description)->toBe('Please verify the submitted receipt.')
        ->and($activity->occurred_at)->not->toBeNull();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $finance->id,
        'defense_request_id' => $defense->id,
        'type' => 'honorarium',
        'title' => 'Honorarium follow-up',
    ]);
    $this->actingAs($aa)->get('/honorarium-workspace')->assertInertia(fn (Assert $page) => $page
        ->where('items.0.follow_ups.0.recipient_role', 'Finance')
        ->where('items.0.follow_ups.0.notes', 'Please verify the submitted receipt.'));
});

it('denies student follow-ups and requires a meaningful note', function () {
    $student = User::factory()->create(['role' => 'Student']);
    $finance = User::factory()->create(['role' => 'Finance']);
    $defense = trackingDefense($student);

    $this->actingAs($student)->postJson('/honorarium-workspace/'.$defense->id.'/follow-ups', [
        'recipient_role' => 'Finance',
        'notes' => 'Please check.',
    ])->assertForbidden();
    $this->actingAs($finance)->postJson('/honorarium-workspace/'.$defense->id.'/follow-ups', [
        'recipient_role' => 'Dean',
        'notes' => 'x',
    ])->assertUnprocessable()->assertJsonValidationErrors('notes');
    expect(HonorariumActivity::count())->toBe(0);
    Mail::assertNothingSent();
});

it('provides Dean program and semester summaries and restricts report downloads', function () {
    $student = User::factory()->create(['role' => 'Student']);
    $aa = User::factory()->create(['role' => 'Administrative Assistant']);
    $dean = User::factory()->create(['role' => 'Dean']);
    $defense = trackingDefense($student);
    trackingPayment($defense, $aa, 'prepared', 'one');
    trackingPayment($defense, $aa, 'claimed', 'two');
    trackingPayment($defense, $aa, 'voided', 'void');

    $this->actingAs($dean)->get('/honorarium-workspace')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('canViewReports', true)
        ->has('programReports', 1)
        ->where('programReports.0.program', 'Master of Arts in Education')
        ->where('programReports.0.school_year', '2026-2027')
        ->where('programReports.0.semester', '1st')
        ->where('programReports.0.recipients_count', 2)
        ->where('programReports.0.total_amount', 2000)
        ->where('programReports.0.prepared_amount', 1000)
        ->where('programReports.0.claimed_amount', 1000));
    $this->get('/honorarium-workspace/reports.csv')->assertDownload('honorarium-program-report.csv');

    $this->actingAs($aa)->get('/honorarium-workspace')->assertInertia(fn (Assert $page) => $page->where('canViewReports', false));
    $this->get('/honorarium-workspace/reports.csv')->assertForbidden();
>>>>>>> Stashed changes
});
