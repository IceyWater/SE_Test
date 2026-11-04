<?php

use App\Models\ComprehensiveExamResult;
use App\Models\DefenseAcademicOutcome;
use App\Models\DefenseRequest;
use App\Models\User;
use App\Services\DefenseAcademicProgress;

function passedComprehensiveFixture(User $student): void
{
    ComprehensiveExamResult::create([
        'student_id' => $student->id, 'program' => (string) $student->program, 'result' => 'passed',
        'exam_date' => now('Asia/Manila')->subDays(3)->toDateString(), 'record_reference' => 'Synthetic test exam result',
        'recorded_by' => $student->id, 'version' => 1,
    ]);
}

function passedDefenseStageFixture(User $student, string $stage): DefenseRequest
{
    $defense = DefenseRequest::withoutEvents(fn () => DefenseRequest::create([
        'submitted_by' => $student->id, 'first_name' => $student->first_name, 'last_name' => $student->last_name,
        'school_id' => 'PREREQUISITE-TEST', 'defense_adviser' => 'Prerequisite fixture adviser', 'program' => (string) $student->program, 'thesis_title' => 'Prerequisite fixture',
        'defense_type' => $stage, 'workflow_state' => 'scheduled', 'status' => 'Approved',
        'scheduled_date' => now('Asia/Manila')->subDay()->toDateString(), 'scheduled_time' => '08:00', 'scheduled_end_time' => '09:00',
    ]));
    DefenseAcademicOutcome::create([
        'defense_request_id' => $defense->id, 'completion' => 'done', 'result' => 'passed', 'completed_on' => now('Asia/Manila')->subDay()->toDateString(),
        'recorded_by' => $student->id, 'version' => 1, 'snapshot_hash' => app(DefenseAcademicProgress::class)->snapshot($defense),
    ]);

    return $defense;
}
