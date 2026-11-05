<?php

use App\Models\CoordinatorProgram;
use App\Models\ExamApplication;
use App\Models\PaymentSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExamReviewPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
    }

    private function application(): array
    {
        $student = User::factory()->create(['role' => 'Student', 'program' => 'Master of Arts', 'school_id' => 'EXAM-REVIEW-STUDENT']);
        $application = ExamApplication::create([
            'student_id' => $student->school_id,
            'school_year' => '2026-2027',
            'program' => $student->program,
            'permit_status' => 'pending',
            'final_approval_status' => 'pending',
            'submitted_at' => now(),
            'created_at' => now(),
        ]);

        return [$application, $student];
    }

    private function receipt(User $student): PaymentSubmission
    {
        return PaymentSubmission::create(['student_id' => $student->id, 'or_number' => 'OR-AUDIT', 'amount_paid' => 500, 'payment_date' => today(), 'payment_type' => 'exam', 'receipt_image' => 'test-receipt.png', 'status' => 'pending']);
    }

    public function test_aa_evaluation_is_a_separate_step_before_registrar_approval(): void
    {
        [$application, $student] = $this->application();
        $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
        $this->actingAs($assistant)->get('/exam/aa/reviews')->assertOk()->assertInertia(fn ($page) => $page
            ->where('applications.0.id', $application->application_id));

        foreach (['grades', 'clearance', 'tuition', 'documents', 'program'] as $requirement) {
            $this->post("/exam/aa/reviews/{$application->application_id}/requirements", [
                'requirement_key' => $requirement,
                'status' => 'passed',
            ])->assertRedirect();
        }
        $this->post("/exam/aa/reviews/{$application->application_id}/evaluated")->assertRedirect();
        $this->assertSame('aa_evaluated', $application->fresh()->workflow_status);
        $this->assertSame('pending', $application->fresh()->final_approval_status);
        $this->assertDatabaseHas('exam_application_events', [
            'exam_application_id' => $application->application_id,
            'action' => 'aa_evaluated',
            'to_status' => 'aa_evaluated',
        ]);
        $this->postJson('/coordinator/compre-exam/'.$application->application_id.'/review', ['decision' => 'approved'])->assertStatus(410);

        $registrar = User::factory()->create(['role' => 'Registrar']);
        $this->actingAs($registrar)->post("/exam/registrar/reviews/{$application->application_id}/approve")->assertRedirect();
        $this->assertDatabaseHas('exam_application', [
            'application_id' => $application->application_id,
            'workflow_status' => 'registrar_approved',
            'final_approval_status' => 'approved',
        ]);
        $this->assertDatabaseHas('exam_application_events', [
            'exam_application_id' => $application->application_id,
            'action' => 'registrar_approved',
            'actor_id' => $registrar->id,
        ]);
    }

    public function test_assistant_application_rejection_requires_and_preserves_a_reason(): void
    {
        [$application] = $this->application();
        $this->actingAs(User::factory()->create(['role' => 'Administrative Assistant']))->postJson(
            "/exam/aa/reviews/{$application->application_id}/lacking",
            ['remarks' => 'Incomplete supporting documents']
        )->assertConflict();
        $this->post("/exam/aa/reviews/{$application->application_id}/requirements", [
            'requirement_key' => 'documents',
            'status' => 'lacking',
            'remarks' => 'Missing signed documents',
        ])->assertRedirect();
        $this->post("/exam/aa/reviews/{$application->application_id}/lacking", [
            'remarks' => 'Incomplete supporting documents',
        ])->assertRedirect();
        $this->assertSame('aa_lacking', $application->fresh()->workflow_status);
        $this->assertSame('Incomplete supporting documents', $application->fresh()->aa_remarks);
    }

    public function test_coordinator_cannot_bypass_the_aa_and_registrar_review_steps(): void
    {
        [$application] = $this->application();
        $coordinator = User::factory()->create(['role' => 'Coordinator']);
        $this->actingAs($coordinator)->postJson('/coordinator/compre-exam/'.$application->application_id.'/review', ['decision' => 'approved'])->assertForbidden();
        CoordinatorProgram::create(['coordinator_id' => $coordinator->id, 'program' => 'Master of Arts']);
        $this->postJson('/coordinator/compre-exam/'.$application->application_id.'/review', ['decision' => 'approved'])->assertForbidden();
    }

    public function test_students_cannot_review_exam_applications_or_receipts(): void
    {
        [$application, $student] = $this->application();
        $receipt = $this->receipt($student);
        $this->actingAs($student)->get('/exam/aa/reviews')->assertForbidden();
        $this->postJson('/coordinator/compre-exam/'.$application->application_id.'/review', ['decision' => 'approved'])->assertForbidden();
        $this->post('/coordinator/compre-payment/'.$receipt->payment_id.'/approve')->assertForbidden();
    }

    public function test_exam_review_tabs_are_limited_to_their_assigned_staff_roles(): void
    {
        $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
        $coordinator = User::factory()->create(['role' => 'Coordinator']);
        $registrar = User::factory()->create(['role' => 'Registrar']);

        $this->actingAs($coordinator)->get('/exam/aa/reviews')->assertForbidden();
        $this->actingAs($assistant)->get('/exam/registrar/reviews')->assertForbidden();
        $this->actingAs($registrar)->get('/exam/aa/reviews')->assertForbidden();
    }

    public function test_assistant_can_review_receipts_and_records_the_reviewer(): void
    {
        [$application, $student] = $this->application();
        $receipt = $this->receipt($student);
        $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
        $this->actingAs($assistant)->get('/coordinator/compre-payment')->assertOk()->assertInertia(fn ($page) => $page->where('counts.pending', 1));
        $this->post('/coordinator/compre-payment/'.$receipt->payment_id.'/approve')->assertRedirect('/coordinator/compre-payment');
        $this->assertSame('approved', $receipt->fresh()->status);
        $this->assertEquals($assistant->id, $receipt->fresh()->checked_by);
    }

    public function test_coordinator_cannot_review_a_receipt_outside_their_programs(): void
    {
        [$application, $student] = $this->application();
        $receipt = $this->receipt($student);
        $coordinator = User::factory()->create(['role' => 'Coordinator']);
        CoordinatorProgram::create(['coordinator_id' => $coordinator->id, 'program' => 'Another Program']);
        $this->actingAs($coordinator)->post('/coordinator/compre-payment/'.$receipt->payment_id.'/approve')->assertSessionHasErrors('status');
        $this->assertSame('pending', $receipt->fresh()->status);
    }

    public function test_assistant_can_reject_a_receipt_with_a_reason(): void
    {
        [$application, $student] = $this->application();
        $receipt = $this->receipt($student);
        $assistant = User::factory()->create(['role' => 'Administrative Assistant']);
        $this->actingAs($assistant)->post('/coordinator/compre-payment/'.$receipt->payment_id.'/reject', ['remarks' => 'Receipt number does not match'])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $receipt->fresh()->status);
        $this->assertSame('Receipt number does not match', $receipt->fresh()->remarks);
        $this->assertEquals($assistant->id, $receipt->fresh()->checked_by);
    }
}
