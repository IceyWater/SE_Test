<?php

use App\Models\DefenseRequest;
use App\Models\FinanceDocumentSubmission;
use App\Models\HonorariumDisbursement;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\HonorariumTrackingDemoSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-26 09:00:00', 'Asia/Manila'));
    Storage::fake('public');
    $this->admin = User::factory()->create(['role' => 'Super Admin']);
    $this->assistant = User::factory()->create(['role' => 'Administrative Assistant']);
    $this->finance = User::factory()->create(['role' => 'Finance']);
    $this->seed(HonorariumTrackingDemoSeeder::class);
});

it('provides mock workflow, schedule, rates, records, and payment data to the tracking screens', function () {
    Storage::disk('public')->assertExists('mock-honorarium/receipt.txt');

    $this->actingAs($this->admin)
        ->get('/honorarium-workspace')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('honorarium/workspace/Index')
            ->where('role', 'super admin')
            ->where('canManageDocuments', true)
            ->where('canSubmitToFinance', false)
            ->where('canVerifyFinance', false)
            ->has('items', 5)
            ->has('rates', 6));

    $this->actingAs($this->assistant)
        ->get('/honorarium-documents')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('honorarium/documents/Index')
            ->where('canSubmit', true)
            ->where('canReview', false)
            ->has('items', 5));

    $this->actingAs($this->finance)
        ->get('/honorarium-documents')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('honorarium/documents/Index')
            ->where('canSubmit', false)
            ->where('canReview', true)
            ->has('items', 5));

    $this->actingAs($this->admin)
        ->get('/honorarium-ledger')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('honorarium/ledger/Index')
            ->where('canPrepare', true)
            ->where('canRecord', true)
            ->has('records', 4)
            ->has('requests', 5));

    expect(HonorariumDisbursement::whereIn('status', ['prepared', 'approved', 'disbursed', 'claimed'])->count())->toBe(4);

    $this->seed(HonorariumTrackingDemoSeeder::class);

    expect(DefenseRequest::where('school_id', 'like', 'MOCK-HONORARIUM-%')->count())->toBe(5)
        ->and(HonorariumDisbursement::count())->toBe(4);
});

it('supports receipt review and the complete honorarium payment lifecycle', function () {
    $defense = DefenseRequest::where('school_id', 'MOCK-HONORARIUM-01')->firstOrFail();

    $this->actingAs($this->assistant)
        ->postJson("/honorarium-documents/{$defense->id}/submit-one")
        ->assertOk();

    $receipt = FinanceDocumentSubmission::where('defense_request_id', $defense->id)
        ->where('document_type', 'payment_receipt')
        ->firstOrFail();
    $breakdown = FinanceDocumentSubmission::where('defense_request_id', $defense->id)
        ->where('document_type', 'panel_breakdown')
        ->firstOrFail();

    expect($receipt->status)->toBe('submitted')
        ->and($breakdown->status)->toBe('locked');

    $this->actingAs($this->finance)
        ->postJson("/honorarium-documents/finance/{$receipt->id}/review", ['status' => 'verified'])
        ->assertOk();

    $schoolYear = now('Asia/Manila')->year.'-'.(now('Asia/Manila')->year + 1);
    $this->actingAs($this->assistant)
        ->postJson("/honorarium-ledger/{$defense->id}/prepare", [
            'school_year' => $schoolYear,
            'semester' => '1st',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('workflow');

    $this->travelTo(Carbon::parse('2026-10-27 09:00:00', 'Asia/Manila'));
    $this->actingAs($this->assistant)->get('/honorarium-documents')->assertOk();
    $breakdown->refresh();
    expect($breakdown->status)->toBe('ready');

    $this->actingAs($this->assistant)
        ->postJson("/honorarium-documents/{$defense->id}/submit-two")
        ->assertOk();
    $breakdown->refresh();
    expect($breakdown->status)->toBe('submitted');

    $this->actingAs($this->finance)
        ->postJson("/honorarium-documents/finance/{$breakdown->id}/review", ['status' => 'verified'])
        ->assertOk();
    $breakdown->refresh();
    expect($breakdown->status)->toBe('verified');

    $this->actingAs($this->assistant)
        ->postJson("/honorarium-ledger/{$defense->id}/prepare", [
            'school_year' => now('Asia/Manila')->year.'-'.(now('Asia/Manila')->year + 2),
            'semester' => '1st',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('school_year');

    $this->actingAs($this->assistant)
        ->postJson("/honorarium-ledger/{$defense->id}/prepare", [
            'school_year' => $schoolYear,
            'semester' => '1st',
        ])
        ->assertOk();

    $payments = HonorariumDisbursement::where('defense_request_id', $defense->id)->orderBy('id')->get();
    expect($payments)->toHaveCount(4)
        ->and($payments->every(fn (HonorariumDisbursement $payment) => $payment->status === 'prepared'))->toBeTrue()
        ->and((float) $payments->firstWhere('recipient_role', 'Panel Chair')->amount)->toBe(2000.0)
        ->and((float) $payments->firstWhere('recipient_role', 'Panel Member')->amount)->toBe(1500.0);

    $this->actingAs($this->assistant)
        ->postJson("/honorarium-ledger/{$defense->id}/prepare", [
            'school_year' => $schoolYear,
            'semester' => '1st',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('workflow');

    $payment = $payments->firstOrFail();
    $this->actingAs($this->finance)
        ->patchJson("/honorarium-ledger/{$payment->id}", ['status' => 'approved'])
        ->assertOk();
    $payment->refresh();
    expect($payment->status)->toBe('approved');

    $today = now('Asia/Manila')->toDateString();
    $this->actingAs($this->finance)
        ->patchJson("/honorarium-ledger/{$payment->id}", [
            'status' => 'disbursed',
            'event_date' => $today,
            'payment_reference' => 'MOCK-TEST-REF-001',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('workflow');

    DefenseRequest::withoutEvents(fn () => $defense->forceFill(['workflow_state' => 'completed'])->save());

    $this->actingAs($this->finance)
        ->patchJson("/honorarium-ledger/{$payment->id}", [
            'status' => 'disbursed',
            'event_date' => $today,
            'payment_reference' => 'MOCK-TEST-REF-001',
        ])
        ->assertOk();
    $payment->refresh();
    expect($payment->status)->toBe('disbursed')
        ->and($payment->payment_reference)->toBe('MOCK-TEST-REF-001')
        ->and($payment->disbursed_on->toDateString())->toBe($today);

    $this->actingAs($this->finance)
        ->patchJson("/honorarium-ledger/{$payment->id}", [
            'status' => 'claimed',
            'event_date' => Carbon::parse($today)->subDay()->toDateString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('event_date');

    $this->actingAs($this->finance)
        ->patchJson("/honorarium-ledger/{$payment->id}", [
            'status' => 'claimed',
            'event_date' => $today,
        ])
        ->assertOk();
    $payment->refresh();
    expect($payment->status)->toBe('claimed')
        ->and($payment->claimed_on->toDateString())->toBe($today)
        ->and(collect($payment->history)->pluck('action')->all())->toBe(['prepared', 'approved', 'disbursed', 'claimed']);
});

it('requires a reason to reject documents and prevents unauthorized payment actions', function () {
    $defense = DefenseRequest::where('school_id', 'MOCK-HONORARIUM-05')->firstOrFail();
    $receipt = FinanceDocumentSubmission::where('defense_request_id', $defense->id)
        ->where('document_type', 'payment_receipt')
        ->firstOrFail();

    $this->actingAs($this->assistant)
        ->postJson("/honorarium-documents/{$defense->id}/submit-one")
        ->assertOk();
    $receipt->refresh();
    expect($receipt->status)->toBe('submitted');

    $this->actingAs($this->finance)
        ->postJson("/honorarium-documents/finance/{$receipt->id}/review", ['status' => 'rejected'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('finance_notes');

    $this->actingAs($this->finance)
        ->postJson("/honorarium-documents/finance/{$receipt->id}/review", [
            'status' => 'rejected',
            'finance_notes' => 'Please upload a readable mock receipt.',
        ])
        ->assertOk();
    expect($receipt->refresh()->finance_notes)->toBe('Please upload a readable mock receipt.');

    $this->actingAs($this->assistant)
        ->postJson("/honorarium-documents/{$defense->id}/submit-one")
        ->assertOk();
    expect($receipt->refresh()->status)->toBe('submitted');

    $payment = HonorariumDisbursement::where('status', 'prepared')->firstOrFail();
    $this->actingAs($this->assistant)
        ->patchJson("/honorarium-ledger/{$payment->id}", ['status' => 'approved'])
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->patchJson("/honorarium-ledger/{$payment->id}", ['status' => 'disbursed'])
        ->assertUnprocessable();
});
