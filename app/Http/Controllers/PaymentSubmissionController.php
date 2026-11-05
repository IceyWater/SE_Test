<?php

namespace App\Http\Controllers;

use App\Enums\ExamApplicationStatus;
use App\Models\ExamApplication;
use App\Models\PaymentSubmission;
use App\Models\User;
use App\Services\Exam\ExamWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PaymentSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $application = $this->latestApplicationFor($user);
        $fee = (float) config('exam.fee', 0);
        $payment = $application
            ? PaymentSubmission::where('exam_application_id', $application->application_id)->latest('payment_id')->first()
            : PaymentSubmission::where('student_id', $user->id)->where('payment_type', 'exam')->latest('payment_id')->first();

        $eligibleStatus = $application && in_array($application->workflow_status, [
            ExamApplicationStatus::RegistrarApproved->value,
            ExamApplicationStatus::PaymentRejected->value,
        ], true);

        return Inertia::render('payment/Index', [
            'student' => [
                'name' => trim($user->first_name.' '.($user->middle_name ? $user->middle_name.' ' : '').$user->last_name),
                'program' => $user->program ?? null,
                'email' => $user->email ?? null,
            ],
            'canSubmit' => $user->role === 'Student' && $eligibleStatus && $fee > 0
                && (! $payment || strtolower($payment->status) === 'rejected'),
            'fee' => $fee,
            'paymentGateMessage' => $fee <= 0
                ? 'Payment submission is unavailable because the comprehensive-exam fee has not been configured.'
                : (! $eligibleStatus
                    ? 'Payment can be submitted after the Registrar approves your comprehensive-exam application.'
                    : null),
            'payment' => $payment ? [
                'payment_id' => $payment->payment_id,
                'or_number' => $payment->or_number,
                'payment_date' => optional($payment->payment_date)->toDateString(),
                'receipt_image' => $payment->receipt_image ? route('payment.receipt', $payment->payment_id) : null,
                'status' => $payment->status,
                'remarks' => $payment->remarks,
                'amount_paid' => $payment->amount_paid,
                'created_at' => optional($payment->created_at)->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }

    public function store(Request $request, ExamWorkflowService $workflow)
    {
        $user = $request->user();
        abort_unless($user->role === 'Student', 403);

        $fee = (float) config('exam.fee', 0);
        if ($fee <= 0) {
            return back()->withErrors(['form' => 'Payment submission is unavailable because the comprehensive-exam fee is not configured.']);
        }

        $application = $this->latestApplicationFor($user);
        if (! $application || ! in_array($application->workflow_status, [
            ExamApplicationStatus::RegistrarApproved->value,
            ExamApplicationStatus::PaymentRejected->value,
        ], true)) {
            return back()->withErrors(['form' => 'You can submit payment only after the Registrar approves your comprehensive application.']);
        }

        $latestPayment = PaymentSubmission::where('exam_application_id', $application->application_id)
            ->latest('payment_id')
            ->first();
        if ($latestPayment && strtolower($latestPayment->status) !== 'rejected') {
            return back()->withErrors(['form' => 'A payment for this application is already awaiting review or has been approved.']);
        }

        $validated = $request->validate([
            'or_number' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9-]+$/'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount_paid' => ['required', 'numeric', 'min:'.$fee],
            'receipt_image' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.(int) config('exam.max_receipt_kb', 5120)],
        ]);

        $file = $request->file('receipt_image');
        $path = $file->store('exam-payment-receipts', 'local');
        if (! $path) {
            throw new \RuntimeException('The payment receipt could not be stored.');
        }

        try {
            DB::transaction(function () use ($application, $user, $workflow, $validated, $file, $path) {
                $locked = ExamApplication::whereKey($application->application_id)->lockForUpdate()->firstOrFail();
                if (! in_array($locked->workflow_status, [
                    ExamApplicationStatus::RegistrarApproved->value,
                    ExamApplicationStatus::PaymentRejected->value,
                ], true)) {
                    abort(409, 'The application status changed. Refresh before submitting payment.');
                }

                $latest = PaymentSubmission::where('exam_application_id', $locked->application_id)
                    ->lockForUpdate()->latest('payment_id')->first();
                if ($latest && strtolower($latest->status) !== 'rejected') {
                    abort(409, 'A payment for this application is already awaiting review or has been approved.');
                }

                $payment = PaymentSubmission::create([
                    'student_id' => $user->id,
                    'exam_application_id' => $locked->application_id,
                    'attempt' => (int) ($latest?->attempt ?? 0) + 1,
                    'or_number' => $validated['or_number'],
                    'payment_date' => $validated['payment_date'],
                    'amount_paid' => $validated['amount_paid'],
                    'payment_type' => 'exam',
                    'receipt_image' => $path,
                    'receipt_sha256' => hash_file('sha256', $file->getRealPath()),
                    'status' => 'pending',
                    'checked_by' => null,
                    'checked_at' => null,
                    'remarks' => null,
                ]);

                $workflow->paymentSubmitted($locked, $user);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('payment.index')->with('success', 'Payment submitted successfully.');
    }

    public function receipt(Request $request, int $id): StreamedResponse
    {
        $payment = PaymentSubmission::with('examApplication')->findOrFail($id);
        $user = $request->user();

        if ($user->role === 'Student') {
            abort_unless((int) $payment->student_id === (int) $user->id, 404);
        } elseif ($payment->examApplication) {
            Gate::forUser($user)->authorize('view', $payment->examApplication);
        } else {
            $studentProgram = User::whereKey($payment->student_id)->value('program');
            $allowedPrograms = method_exists($user, 'allowedProgramNames') ? $user->allowedProgramNames() : [];
            abort_unless(
                $user->role === 'Administrative Assistant'
                || ($user->isCoordinator() && in_array($studentProgram, $allowedPrograms, true)),
                403
            );
        }

        $path = $payment->receipt_image;
        abort_unless($path, 404);

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }

        abort(404);
    }

    private function latestApplicationFor(User $user): ?ExamApplication
    {
        $studentIds = array_values(array_unique(array_filter([$user->student_id, $user->school_id, $user->id], fn ($id) => filled($id))));

        return ExamApplication::whereIn('student_id', $studentIds)
            ->latest('application_id')
            ->first();
    }
}
