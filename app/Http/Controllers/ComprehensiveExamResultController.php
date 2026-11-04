<?php

namespace App\Http\Controllers;

use App\Models\ComprehensiveExamResult;
use App\Models\Notification;
use App\Models\User;
use App\Services\AcademicProgressAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComprehensiveExamResultController extends Controller
{
    public function update(Request $request, User $student)
    {
        app(AcademicProgressAccess::class)->edit($request->user(), $student);
        $data = $request->validate([
            'result' => 'required|in:passed,failed,not-recorded',
            'exam_date' => 'required|date_format:Y-m-d|before_or_equal:'.now('Asia/Manila')->toDateString(),
            'record_reference' => 'required|string|min:5|max:1500',
            'verified_against_school_records' => 'required|accepted',
            'version' => 'required|integer|min:0',
        ]);
        abort_unless(filled($student->program), 422, 'Record the student program first.');
        DB::transaction(function () use ($request, $student, $data) {
            $student = User::whereKey($student->id)->lockForUpdate()->firstOrFail();
            app(AcademicProgressAccess::class)->edit($request->user(), $student);
            $record = ComprehensiveExamResult::where('student_id', $student->id)->where('program', $student->program)->lockForUpdate()->first();
            if (($record?->version ?? 0) !== (int) $data['version']) {
                throw ValidationException::withMessages(['version' => 'The exam result changed. Refresh before saving again.']);
            }
            $history = $record?->history ?? [];
            if ($record) {
                $history[] = $record->only(['result', 'exam_date', 'record_reference', 'recorded_by', 'version', 'updated_at']);
            }
            $record ??= new ComprehensiveExamResult(['student_id' => $student->id, 'program' => $student->program]);
            $record->fill([
                'result' => $data['result'], 'exam_date' => $data['exam_date'], 'record_reference' => $data['record_reference'],
                'recorded_by' => $request->user()->id, 'version' => ($record->version ?? 0) + 1, 'history' => $history,
            ])->save();
            Notification::create([
                'user_id' => $student->id, 'type' => 'comprehensive-result', 'title' => 'Comprehensive exam result recorded',
                'message' => 'Your Coordinator recorded your comprehensive exam result as '.str_replace('-', ' ', $data['result']).'.',
                'link' => '/defense-requirements', 'read' => false,
            ]);
        });

        return back()->with('success', 'Verified comprehensive exam result saved.');
    }
}
