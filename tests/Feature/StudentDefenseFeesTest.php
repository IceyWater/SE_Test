<?php

use App\Models\DefenseRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    Mail::fake();
    Storage::fake('public');
});

it('fills and enforces the published student fee for each program and defense stage', function ($program, $level, $stage, $amount) {
    $student = User::factory()->create(['role' => 'Student', 'program' => $program, 'school_id' => 'FEE-TEST']);
    $adviser = User::factory()->create(['role' => 'Adviser']);
    $student->advisers()->attach($adviser->id, ['status' => 'accepted']);
    $this->actingAs($student)->get('/defense-requirements')->assertOk()->assertInertia(fn (Assert $p) => $p
        ->where('defenseFees.'.$stage, $amount));
    $data = ['first_name' => $student->first_name, 'last_name' => $student->last_name,
        'school_id' => $student->school_id, 'program' => $program, 'thesis_title' => 'Fee schedule test',
        'adviser_id' => $adviser->id, 'adviser' => $adviser->name, 'defense_type' => $stage,
        'amount' => 1, 'reference_no' => 'TEST-FEE-OR'];
    foreach (['manuscript_proposal', 'similarity_index', 'proof_of_payment', 'avisee_adviser_attachment', 'rec_endorsement'] as $field) {
        $data[$field] = UploadedFile::fake()->createWithContent($field.'.pdf', "%PDF-1.4\nFee test document");
    }
    $this->postJson('/defense-requirements', $data)->assertUnprocessable()->assertJsonValidationErrors('amount');
    $this->assertDatabaseCount('defense_requests', 0);
    $data['amount'] = $amount;
    $this->post('/defense-requirements', $data)->assertSessionHasNoErrors();
    expect((float) DefenseRequest::where('submitted_by', $student->id)->firstOrFail()->amount)->toBe((float) $amount);
    expect(config('student_defense_fees.'.$level.'.'.$stage))->toBe($amount);
})->with([
    ['Master of Arts in Education', 'Masteral', 'Proposal', 11250],
    ['Master of Science in Pharmacy', 'Masteral', 'Prefinal', 12780],
    ['Master of Arts in Education', 'Masteral', 'Final', 6000],
    ['Doctor of Philosophy in Education', 'Doctorate', 'Proposal', 17150],
    ['Doctor in Business Management', 'Doctorate', 'Prefinal', 19740],
    ['PhD in Pharmacy', 'Doctorate', 'Final', 7000],
]);
