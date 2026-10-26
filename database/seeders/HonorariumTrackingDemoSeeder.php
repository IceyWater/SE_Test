<?php

namespace Database\Seeders;

use App\Models\DefenseRequest;
use App\Models\FinanceDocumentSubmission;
use App\Models\HonorariumActivity;
use App\Models\HonorariumDisbursement;
use App\Models\Panelist;
use App\Models\PaymentRate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HonorariumTrackingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()
            ->whereIn('role', ['Super Admin', 'Dean', 'Administrative Assistant', 'Finance'])
            ->first() ?? User::query()->first();

        if (! $actor) {
            throw new \RuntimeException('Create at least one user account before seeding Honorarium Tracking demo data.');
        }

        Storage::disk('public')->put(
            'mock-honorarium/receipt.txt',
            "MOCK PAYMENT RECEIPT\nFor Honorarium Tracking testing only. This is not proof of a real payment.\n"
        );

        DB::transaction(function () use ($actor): void {
            $panelists = collect([
                ['chair', 'Dr. Mock Chairperson', 'Chairperson'],
                ['panel-1', 'Mock Panelist One', 'Panel Member'],
                ['panel-2', 'Mock Panelist Two', 'Panel Member'],
                ['panel-3', 'Mock Panelist Three', 'Panel Member'],
            ])->mapWithKeys(function (array $person) {
                [$key, $name, $role] = $person;
                $panelist = Panelist::updateOrCreate(
                    ['email' => "honorarium-demo-{$key}@example.invalid"],
                    ['name' => $name, 'role' => $role, 'status' => 'Assigned']
                );

                return [$key => $panelist];
            });

            $defenseTypes = ['Proposal', 'Pre-final', 'Final'];
            foreach ($defenseTypes as $defenseType) {
                PaymentRate::firstOrCreate(
                    ['program_level' => 'Masteral', 'type' => 'Panel Chair', 'defense_type' => $defenseType],
                    ['amount' => 2000]
                );
                PaymentRate::firstOrCreate(
                    ['program_level' => 'Masteral', 'type' => 'Panel Member', 'defense_type' => $defenseType],
                    ['amount' => 1500]
                );
            }

            $today = now('Asia/Manila')->startOfDay();
            $definitions = [
                [
                    'key' => '01',
                    'first_name' => 'Alex',
                    'last_name' => 'Mock',
                    'program' => 'Master of Arts in Educational Management',
                    'defense_type' => 'Proposal',
                    'workflow_state' => 'scheduled',
                    'date' => $today->copy()->addDay(),
                    'documents' => [],
                ],
                [
                    'key' => '02',
                    'first_name' => 'Taylor',
                    'last_name' => 'Sample',
                    'program' => 'Master of Arts in Educational Management',
                    'defense_type' => 'Pre-final',
                    'workflow_state' => 'scheduled',
                    'date' => $today->copy()->addDays(2),
                    'documents' => ['payment_receipt' => 'submitted', 'panel_breakdown' => 'locked'],
                ],
                [
                    'key' => '03',
                    'first_name' => 'Jordan',
                    'last_name' => 'Example',
                    'program' => 'Master of Arts in Educational Management',
                    'defense_type' => 'Final',
                    'workflow_state' => 'scheduled',
                    'date' => $today->copy()->addDays(3),
                    'documents' => ['payment_receipt' => 'verified', 'panel_breakdown' => 'verified'],
                ],
                [
                    'key' => '04',
                    'first_name' => 'Morgan',
                    'last_name' => 'Demo',
                    'program' => 'Master of Arts in Educational Management',
                    'defense_type' => 'Final',
                    'workflow_state' => 'completed',
                    'date' => $today->copy()->subDay(),
                    'documents' => ['payment_receipt' => 'verified', 'panel_breakdown' => 'verified'],
                ],
                [
                    'key' => '05',
                    'first_name' => 'Casey',
                    'last_name' => 'Test',
                    'program' => 'Master of Arts in Educational Management',
                    'defense_type' => 'Proposal',
                    'workflow_state' => 'scheduled',
                    'date' => $today->copy()->addDays(4),
                    'documents' => ['payment_receipt' => 'rejected', 'panel_breakdown' => 'locked'],
                ],
            ];

            foreach ($definitions as $definition) {
                $schoolId = "MOCK-HONORARIUM-{$definition['key']}";
                $existing = DefenseRequest::where('school_id', $schoolId)->first();
                if ($existing) {
                    FinanceDocumentSubmission::where('defense_request_id', $existing->id)->delete();
                    HonorariumDisbursement::where('defense_request_id', $existing->id)->delete();
                    HonorariumActivity::where('defense_request_id', $existing->id)->delete();
                }

                $date = $definition['date'];
                $mode = $definition['key'] % 2 === 0 ? 'Online' : 'Face-to-Face';
                $defense = DefenseRequest::withoutEvents(fn () => DefenseRequest::updateOrCreate(
                    ['school_id' => $schoolId],
                    [
                        'submitted_by' => null,
                        'first_name' => $definition['first_name'],
                        'middle_name' => null,
                        'last_name' => $definition['last_name'],
                        'program' => $definition['program'],
                        'thesis_title' => "[MOCK] {$definition['defense_type']} defense - {$definition['first_name']} {$definition['last_name']}",
                        'date_of_defense' => $date->toDateString(),
                        'scheduled_date' => $date->copy()->setTime(9, 0),
                        'scheduled_time' => '09:00:00',
                        'scheduled_end_time' => '10:00:00',
                        'defense_duration_minutes' => 60,
                        'formatted_time_range' => '9:00 AM - 10:00 AM',
                        'mode_defense' => $mode,
                        'defense_mode' => $mode,
                        'defense_venue' => $mode === 'Online' ? 'Mock Online Meeting Room' : 'Mock Graduate School Room',
                        'defense_type' => $definition['defense_type'],
                        'proof_of_payment' => 'mock-honorarium/receipt.txt',
                        'defense_adviser' => 'Dr. Mock Adviser',
                        'defense_chairperson' => $panelists['chair']->name,
                        'defense_panelist1' => $panelists['panel-1']->name,
                        'defense_panelist2' => $panelists['panel-2']->name,
                        'defense_panelist3' => $panelists['panel-3']->name,
                        'status' => $definition['workflow_state'] === 'completed' ? 'Completed' : 'Approved',
                        'priority' => 'Medium',
                        'workflow_state' => $definition['workflow_state'],
                        'scheduling_status' => $definition['workflow_state'],
                        'adviser_status' => 'Approved',
                        'coordinator_status' => 'Approved',
                        'submitted_at' => now(),
                    ]
                ));

                $receiptPath = 'mock-honorarium/receipt.txt';
                $panelNames = [
                    $panelists['chair']->name,
                    $panelists['panel-1']->name,
                    $panelists['panel-2']->name,
                    $panelists['panel-3']->name,
                ];

                foreach ($definition['documents'] as $type => $status) {
                    $document = FinanceDocumentSubmission::create([
                        'defense_request_id' => $defense->id,
                        'document_type' => $type,
                        'status' => $status,
                        'payload' => $type === 'payment_receipt'
                            ? ['receipt_path' => $receiptPath]
                            : ['panel_names' => $panelNames],
                        'finance_notes' => $status === 'rejected' ? 'Mock rejection: please review the receipt and resubmit.' : null,
                        'ready_on' => $status === 'locked' ? $today->copy()->addDay()->toDateString() : $today->toDateString(),
                        'submitted_at' => in_array($status, ['submitted', 'verified', 'rejected'], true) ? now() : null,
                        'reviewed_at' => in_array($status, ['verified', 'rejected'], true) ? now() : null,
                    ]);

                    HonorariumActivity::create([
                        'defense_request_id' => $defense->id,
                        'finance_document_submission_id' => $document->id,
                        'activity_type' => $status === 'verified' ? 'finance_review' : 'document_submitted',
                        'title' => $status === 'rejected' ? 'Mock document rejected' : 'Mock finance document activity',
                        'description' => "Demo-only {$type} document status: {$status}.",
                        'occurred_at' => now(),
                    ]);
                }

                if ($definition['key'] === '04') {
                    $statuses = ['prepared', 'approved', 'disbursed', 'claimed'];
                    foreach (array_values($panelNames) as $index => $recipientName) {
                        $panelist = $panelists->firstWhere('name', $recipientName);
                        $status = $statuses[$index];
                        $amount = $index === 0 ? 2000 : 1500;
                        $history = [[
                            'action' => 'prepared',
                            'actor_id' => $actor->id,
                            'actor_name' => $actor->full_name,
                            'at' => now()->toIso8601String(),
                            'notes' => 'Demo record prepared from the configured payment rate.',
                        ]];
                        if ($status !== 'prepared') {
                            $history[] = [
                                'action' => $status,
                                'actor_id' => $actor->id,
                                'actor_name' => $actor->full_name,
                                'at' => now()->toIso8601String(),
                                'notes' => 'Demo payment lifecycle sample.',
                            ];
                        }

                        HonorariumDisbursement::create([
                            'defense_request_id' => $defense->id,
                            'recipient_key' => "panel:{$panelist->id}:".($index === 0 ? 'Panel Chair' : 'Panel Member'),
                            'recipient_name' => $recipientName,
                            'recipient_email' => $panelist->email,
                            'recipient_role' => $index === 0 ? 'Panel Chair' : 'Panel Member',
                            'amount' => $amount,
                            'school_year' => now()->year.'-'.(now()->year + 1),
                            'semester' => '1st',
                            'status' => $status,
                            'payment_reference' => in_array($status, ['disbursed', 'claimed'], true) ? "MOCK-PAY-{$index}" : null,
                            'disbursed_on' => in_array($status, ['disbursed', 'claimed'], true) ? $today->copy()->subDay()->toDateString() : null,
                            'claimed_on' => $status === 'claimed' ? $today->toDateString() : null,
                            'history' => $history,
                            'prepared_by' => $actor->id,
                        ]);
                    }
                }
            }
        });

        $this->command?->info('Seeded five mock Honorarium Tracking cases and four payment records. All receipt files and payments are demo-only.');
    }
}
