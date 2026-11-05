<?php

use App\Models\ExamApplication;
use App\Models\ExamRequirementCheck;
use App\Models\PaymentSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    config(['exam.fee' => 500]);
    Storage::fake('local');
    Mail::fake();
});

function paymentWorkflowFixture(string $status = 'registrar_approved'): array
{
    $student = User::factory()->create([
        'role' => 'Student',
        'program' => 'Master of Arts',
        'school_id' => 'EXAM-PAYMENT-'.Str::uuid(),
    ]);
    $application = ExamApplication::create([
        'student_id' => $student->school_id,
        'school_year' => '2026-2027',
        'program' => $student->program,
        'workflow_status' => $status,
        'permit_status' => 'approved',
        'final_approval_status' => 'approved',
        'created_at' => now(),
    ]);

    return [$student, $application];
}

function paymentWorkflowPayload(float $amount = 500): array
{
    return [
        'or_number' => 'OR-2026-001',
        'payment_date' => today()->toDateString(),
        'amount_paid' => $amount,
        'receipt_image' => UploadedFile::fake()->image('receipt.png'),
    ];
}

it('requires registrar approval and the configured fee before accepting payment', function () {
    [$student] = paymentWorkflowFixture('aa_evaluated');
    $this->actingAs($student)->post('/payment', paymentWorkflowPayload())
        ->assertSessionHasErrors('form');
    expect(PaymentSubmission::count())->toBe(0);

    [$approvedStudent] = paymentWorkflowFixture();
    $this->actingAs($approvedStudent)->post('/payment', paymentWorkflowPayload(499.99))
        ->assertSessionHasErrors('amount_paid');
    expect(PaymentSubmission::count())->toBe(0);

    [$noFeeStudent] = paymentWorkflowFixture();
    config(['exam.fee' => 0]);
    $this->actingAs($noFeeStudent)->post('/payment', paymentWorkflowPayload())
        ->assertSessionHasErrors('form');
    expect(PaymentSubmission::count())->toBe(0);
});

it('stores payment receipts privately and advances the application to payment pending', function () {
    [$student, $application] = paymentWorkflowFixture();

    $this->actingAs($student)->post('/payment', paymentWorkflowPayload())->assertRedirect('/payment');

    $payment = PaymentSubmission::firstOrFail();
    expect($payment->exam_application_id)->toBe($application->application_id)
        ->and($payment->attempt)->toBe(1)
        ->and($payment->receipt_sha256)->toHaveLength(64)
        ->and($application->fresh()->workflow_status)->toBe('payment_pending');

    Storage::disk('local')->assertExists($payment->receipt_image);
    Storage::disk('public')->assertMissing($payment->receipt_image);
    $this->get(route('payment.receipt', $payment->payment_id))->assertOk();

    $otherStudent = User::factory()->create(['role' => 'Student']);
    $this->actingAs($otherStudent)->get(route('payment.receipt', $payment->payment_id))->assertNotFound();
});

it('records rejected payment retries as new attempts and verifies payment in the workflow', function () {
    [$student, $application] = paymentWorkflowFixture();
    $this->actingAs($student)->post('/payment', paymentWorkflowPayload())->assertRedirect('/payment');
    $first = PaymentSubmission::firstOrFail();

    $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
    $this->actingAs($assistant)->post(
        "/coordinator/compre-payment/{$first->payment_id}/reject",
        ['remarks' => 'Receipt is unreadable']
    )->assertSessionHasNoErrors();
    expect($application->fresh()->workflow_status)->toBe('payment_rejected');

    $this->actingAs($student)->post('/payment', paymentWorkflowPayload())->assertRedirect('/payment');
    $second = PaymentSubmission::where('exam_application_id', $application->application_id)
        ->orderByDesc('attempt')
        ->firstOrFail();
    expect($second->attempt)->toBe(2)
        ->and($second->status)->toBe('pending')
        ->and($application->fresh()->workflow_status)->toBe('payment_pending');

    $this->actingAs($assistant)->post(
        "/coordinator/compre-payment/{$second->payment_id}/approve"
    )->assertRedirect('/coordinator/compre-payment');
    expect($application->fresh()->workflow_status)->toBe('payment_verified')
        ->and($second->fresh()->status)->toBe('approved');
    $this->assertDatabaseHas('notifications', [
        'user_id' => $assistant->id,
        'exam_application_id' => $application->application_id,
        'title' => 'Comprehensive exam payment submitted',
    ]);
});

it('allows only the owning student to correct and resubmit a returned application', function () {
    [$student, $application] = paymentWorkflowFixture('aa_lacking');
    ExamRequirementCheck::create([
        'exam_application_id' => $application->application_id,
        'requirement_key' => 'documents',
        'status' => 'lacking',
        'remarks' => 'Please correct the submitted record.',
    ]);

    $payload = [
        'schoolYear' => '2026-2027',
        'program' => $student->program,
        'officeAddress' => 'Updated office',
        'mobileNo' => '09123456789',
        'telephoneNo' => '',
        'subjects' => [['subject' => 'Research Methods']],
    ];
    $this->actingAs($student)->post(
        route('comprehensive-exam.resubmit', $application->application_id),
        $payload
    )->assertRedirect(route('comprehensive-exam.index'));

    expect($application->fresh()->workflow_status)->toBe('submitted')
        ->and($application->fresh()->office_address)->toBe('Updated office')
        ->and($application->subjects()->first()->subject_name)->toBe('Research Methods')
        ->and($application->requirementChecks()->first()->status)->toBe('pending');
    $this->assertDatabaseHas('exam_application_events', [
        'exam_application_id' => $application->application_id,
        'action' => 'student_resubmitted',
        'to_status' => 'submitted',
    ]);

    $otherStudent = User::factory()->create(['role' => 'Student']);
    $this->actingAs($otherStudent)->post(
        route('comprehensive-exam.resubmit', $application->application_id),
        $payload
    )->assertForbidden();
});
