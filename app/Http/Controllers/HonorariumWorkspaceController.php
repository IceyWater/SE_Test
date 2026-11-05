<?php

namespace App\Http\Controllers;

use App\Mail\HonorariumFollowUp;
use App\Models\DefenseRequest;
use App\Models\FinanceDocumentSubmission;
use App\Models\FinanceHonorariumSubmission;
use App\Models\HonorariumActivity;
use App\Models\HonorariumDisbursement;
use App\Models\Notification;
use App\Models\User;
use App\Services\HonorariumAccess;
use App\Services\HonorariumTrackingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class HonorariumWorkspaceController extends Controller
{
    public function index(Request $request, HonorariumAccess $access)
    {
        $user = $request->user();
        $requests = $access->scope(DefenseRequest::query(), $user)->with(['student', 'adviserUser'])
            ->where(function ($q) {
                $q->whereNotNull('proof_of_payment')->orWhereHas('honorariumPayments');
            })
            ->latest('updated_at')->limit(500)->get();
        $requestIds = $requests->pluck('id');
        $docs = FinanceDocumentSubmission::with(['submitter:id,first_name,last_name,role', 'reviewer:id,first_name,last_name,role'])
            ->whereIn('defense_request_id', $requestIds)->get()->groupBy('defense_request_id');
        $disbursements = HonorariumDisbursement::whereIn('defense_request_id', $requestIds)
            ->where('status', '!=', 'voided')->get()->groupBy('defense_request_id');
        $activities = HonorariumActivity::with('user:id,first_name,last_name,role')
            ->whereIn('defense_request_id', $requestIds)->where('activity_type', 'follow_up')
            ->latest('occurred_at')->get()->groupBy('defense_request_id');
        $items = $requests->map(function ($r) use ($docs, $disbursements, $activities, $access) {
            $documents = $docs->get($r->id, collect());
            $documentsByType = $documents->keyBy('document_type');
            $recipientRecords = $disbursements->get($r->id, collect());
            $receipt = $documentsByType->get('payment_receipt');
            $breakdown = $documentsByType->get('panel_breakdown');
            $status = $documents->contains('status', 'rejected')
                ? 'rejected'
                : ($receipt?->status === 'verified' && $breakdown?->status === 'verified'
                    ? 'verified'
                    : ($documents->contains('status', 'submitted') ? 'submitted' : 'not_submitted'));
            $financeDocuments = $documents->map(fn ($doc) => [
                'type' => $doc->document_type,
                'status' => $doc->status,
                'submitted_at' => $doc->submitted_at?->toIso8601String(),
                'submitted_by' => $doc->submitter?->full_name,
                'reviewed_at' => $doc->reviewed_at?->toIso8601String(),
                'reviewed_by' => $doc->reviewer?->full_name,
                'reviewer_role' => $doc->reviewer?->role,
                'finance_notes' => $doc->finance_notes,
            ])->values();
            $followUps = $activities->get($r->id, collect())->map(fn ($activity) => [
                'id' => $activity->id,
                'recipient_role' => $activity->recipient_role,
                'notes' => $activity->description,
                'actor' => $activity->user?->full_name ?? 'Staff',
                'occurred_at' => $activity->occurred_at?->toIso8601String(),
            ])->values();
            $panelNames = $access->panelNames($r);
            $facultyNames = collect([$r->adviserUser?->full_name ?: $r->defense_adviser])->merge($panelNames)->filter()->unique()->values();
            $tracking = app(HonorariumTrackingProgress::class)->summarize($documents->toArray(), $recipientRecords->toArray());
            $progress = collect($tracking['steps'])->search(fn ($step) => ! $step['done']);
            $progress = $progress === false ? 8 : $progress;
            $latestDisbursement = $recipientRecords->sortByDesc('updated_at')->first();

            return [
                'faculty' => $facultyNames,
                'periods' => $recipientRecords->map(fn ($row) => ['school_year' => $row->school_year, 'semester' => $row->semester])->unique(fn ($period) => $period['school_year'].'/'.$period['semester'])->values(),
                'requested_at' => ($r->submitted_at ?? $r->created_at)?->toIso8601String(),
                'progress' => $tracking,
                'archived' => $access->isArchived($r),
                'id' => $r->id, 'student_name' => $r->student?->full_name ?: trim($r->first_name.' '.$r->last_name),
                'student_number' => $r->student?->school_id ?: $r->school_id, 'program' => $r->program,
                'defense_type' => $r->defense_type, 'workflow_state' => $r->workflow_state,
                'scheduled_date' => $r->scheduled_date?->format('Y-m-d'), 'scheduled_time' => $r->scheduled_time,
                'receipt_url' => $access->receiptUrl($r->proof_of_payment, $r), 'panel_names' => $panelNames,
                'faculty_names' => $facultyNames,
                'finance_status' => $status, 'finance_notes' => $documents->where('status', 'rejected')->first()?->finance_notes,
                'finance_documents' => $financeDocuments,
                'honorarium_recipients' => $recipientRecords->map(fn ($record) => [
                    'name' => $record->recipient_name,
                    'role' => $record->recipient_role,
                    'status' => $record->status,
                    'requires_review' => $record->requires_review,
                    'school_year' => $record->school_year,
                    'semester' => $record->semester,
                    'amount' => (float) $record->amount,
                    'updated_at' => $record->updated_at?->toIso8601String(),
                    'history' => $record->history ?? [],
                ])->values(),
                'school_year' => $latestDisbursement?->school_year,
                'semester' => $latestDisbursement?->semester,
                'progress_stage' => $progress,
                'progress_percent' => (int) round($progress * 100 / 8),
                'follow_ups' => $followUps,
                'submitted_at' => $documents->max('submitted_at')?->toIso8601String(),
                'last_updated_at' => collect([
                    $documents->max('reviewed_at'),
                    $documents->max('submitted_at'),
                    $recipientRecords->max('updated_at'),
                    $activities->get($r->id, collect())->max('occurred_at'),
                ])->filter()->max()?->toIso8601String(),
                'finance_submission_id' => null,
            ];
        });
        $facultyOptions = $items->flatMap(fn ($item) => $item['faculty_names'])->unique()->sort()->values();
        $terms = $disbursements->flatten(1)->map(fn ($record) => [
            'school_year' => $record->school_year,
            'semester' => $record->semester,
            'value' => $record->school_year.'|'.$record->semester,
        ])->unique('value')->sortByDesc('value')->values();
        $isDean = in_array($access->role($user), ['dean', 'super admin', 'superadmin'], true);

        return Inertia::render('honorarium/workspace/Index', [
            'role' => $access->role($user), 'canSubmitToFinance' => false, 'canVerifyFinance' => false,
            'canManageDocuments' => $access->canSubmit($user) || $access->canReview($user), 'items' => $items,
            'rates' => $access->canSubmit($user) || $access->canReview($user) ? DB::table('payment_rates')->orderBy('program_level')->get() : [],
            'facultyOptions' => $facultyOptions,
            'terms' => $terms,
            'canFollowUp' => in_array($access->role($user), ['administrative assistant', 'assistant', 'aa', 'dean', 'super admin', 'superadmin'], true),
            'canViewReports' => $isDean,
            'programReports' => $isDean ? $this->programReports() : [],
        ]);
    }

    private function programReports()
    {
        return HonorariumDisbursement::query()
            ->join('defense_requests', 'defense_requests.id', '=', 'honorarium_disbursements.defense_request_id')
            ->where('honorarium_disbursements.status', '!=', 'voided')
            ->select('defense_requests.program', 'honorarium_disbursements.school_year', 'honorarium_disbursements.semester')
            ->selectRaw('COUNT(*) as recipients_count')
            ->selectRaw('SUM(honorarium_disbursements.amount) as total_amount')
            ->selectRaw("SUM(CASE WHEN honorarium_disbursements.status = 'prepared' THEN honorarium_disbursements.amount ELSE 0 END) as prepared_amount")
            ->selectRaw("SUM(CASE WHEN honorarium_disbursements.status = 'approved' THEN honorarium_disbursements.amount ELSE 0 END) as approved_amount")
            ->selectRaw("SUM(CASE WHEN honorarium_disbursements.status = 'disbursed' THEN honorarium_disbursements.amount ELSE 0 END) as disbursed_amount")
            ->selectRaw("SUM(CASE WHEN honorarium_disbursements.status = 'claimed' THEN honorarium_disbursements.amount ELSE 0 END) as claimed_amount")
            ->groupBy('defense_requests.program', 'honorarium_disbursements.school_year', 'honorarium_disbursements.semester')
            ->orderByDesc('honorarium_disbursements.school_year')
            ->orderBy('honorarium_disbursements.semester')
            ->orderBy('defense_requests.program')
            ->get();
    }

    public function downloadReport(Request $request)
    {
        abort_unless(in_array(app(HonorariumAccess::class)->role($request->user()), ['dean', 'super admin', 'superadmin'], true), 403);
        $reports = $this->programReports();

        return response()->streamDownload(function () use ($reports) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['School Year', 'Semester', 'Program', 'Recipients', 'Prepared', 'Approved', 'Disbursed', 'Claimed', 'Total']);
            foreach ($reports as $report) {
                $row = [
                    $report->school_year,
                    $report->semester,
                    $report->program,
                    $report->recipients_count,
                    $report->prepared_amount,
                    $report->approved_amount,
                    $report->disbursed_amount,
                    $report->claimed_amount,
                    $report->total_amount,
                ];
                fputcsv($stream, array_map(function ($value) {
                    $value = (string) $value;

                    return preg_match('/^[\s]*[=+\-@]/', $value) ? "'".$value : $value;
                }, $row));
            }
            fclose($stream);
        }, 'honorarium-progress-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function followUp(Request $request, DefenseRequest $defenseRequest, HonorariumAccess $access)
    {
        abort_unless(in_array($access->role($request->user()), [
            'administrative assistant', 'assistant', 'aa', 'dean', 'super admin', 'superadmin',
        ], true), 403);

        $defenseRequest = $access->scope(DefenseRequest::query(), $request->user())->findOrFail($defenseRequest->id);
        $data = $request->validate([
            'recipient_role' => 'required|in:Finance,Administrative Assistant,Dean',
            'notes' => 'required|string|min:3|max:2000',
        ]);
        $recipientsByRole = [
            'Finance' => ['Finance', 'Finance Office', 'Finance Officer'],
            'Administrative Assistant' => ['Administrative Assistant', 'Assistant', 'AA'],
            'Dean' => ['Dean'],
        ];
        $recipients = User::query()->whereIn('role', $recipientsByRole[$data['recipient_role']])
            ->whereNotNull('email')->where('email', '!=', '')->get(['id', 'email']);
        if ($recipients->isEmpty()) {
            return back()->withErrors(['recipient_role' => 'No email recipients are configured for that role.']);
        }

        $actor = $request->user();
        $description = $data['notes'];
        DB::transaction(function () use ($defenseRequest, $data, $recipients, $description, $actor) {
            HonorariumActivity::create([
                'defense_request_id' => $defenseRequest->id,
                'user_id' => $actor->id,
                'activity_type' => 'follow_up',
                'title' => 'Honorarium follow-up sent',
                'description' => $description,
                'recipient_role' => $data['recipient_role'],
                'occurred_at' => now(),
            ]);
            foreach ($recipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'defense_request_id' => $defenseRequest->id,
                    'type' => 'honorarium_follow_up',
                    'title' => 'Honorarium follow-up',
                    'message' => 'A follow-up was recorded for defense request #'.$defenseRequest->id.'.',
                    'link' => route('honorarium-workspace.index'),
                    'read' => false,
                ]);
            }
        });

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(new HonorariumFollowUp(
                $defenseRequest,
                $actor->full_name,
                $data['recipient_role'],
                $description,
                route('honorarium-workspace.index'),
            ));
        }

        return back()->with('success', 'Follow-up recorded and email sent to '.$data['recipient_role'].'.');
    }

    public function submitToFinance(Request $request, DefenseRequest $defenseRequest)
    {
        return app(FinanceDocumentWorkflowController::class)->submitFirst($request, $defenseRequest);
    }

    public function updateFinanceStatus(Request $request, FinanceHonorariumSubmission $submission)
    {
        // Compatibility with the draft's single-stage URL; use the canonical document record.
        $doc = FinanceDocumentSubmission::where('defense_request_id', $submission->defense_request_id)->where('document_type', 'payment_receipt')->firstOrFail();

        return app(FinanceDocumentWorkflowController::class)->review($request, $doc);
    }
}
