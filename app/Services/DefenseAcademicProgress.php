<?php

namespace App\Services;

use App\Models\DefenseAcademicOutcome;
use App\Models\DefenseRequest;
use Carbon\CarbonImmutable;

class DefenseAcademicProgress
{
    public const SUBJECTS = ['Thesis 1', 'Thesis 2', 'Dissertation 1', 'Dissertation 2', 'Capstone 1', 'Capstone 2', 'Capstone', 'Business Plan', 'Integrating Course'];

    public function sessionStatus(DefenseRequest $dr): string
    {
        if (in_array(strtolower($dr->status ?? ''), ['cancelled', 'rejected'])
            || in_array($dr->workflow_state, ['cancelled', 'rejected', 'adviser-rejected', 'coordinator-rejected'], true)) {
            return 'Closed';
        }
        $outcome = DefenseAcademicOutcome::where('defense_request_id', $dr->id)->first();
        if ($this->current($dr, $outcome)) {
            if ($outcome->completion === 'done') {
                $result = ['passed' => 'Passed', 'for-revision' => 'For Revision', 'repeat-defense' => 'Repeat Defense'];

                return 'Finished'.(isset($result[$outcome->result]) ? ' / '.$result[$outcome->result] : ' / Result Not Recorded');
            }
            if ($outcome->completion === 'did-not-defend') {
                return 'Did Not Defend';
            }
        }
        if (! $dr->scheduled_date || ! $dr->scheduled_time || ! $dr->scheduled_end_time) {
            return 'Schedule Incomplete';
        }
        $date = $dr->scheduled_date->format('Y-m-d');
        $start = CarbonImmutable::parse($date.' '.$dr->scheduled_time, 'Asia/Manila');
        $end = CarbonImmutable::parse($date.' '.$dr->scheduled_end_time, 'Asia/Manila');
        $now = CarbonImmutable::now('Asia/Manila');
        if ($end->lessThanOrEqualTo($start)) {
            return 'Schedule Incomplete';
        }
        if ($now->lessThan($start)) {
            return 'Upcoming';
        }

        return $now->lessThan($end) ? 'Ongoing (Scheduled Time)' : 'Session Ended / Outcome Awaiting Confirmation';
    }

    public function outcomeLockReason(DefenseRequest $dr): ?string
    {
        if (! in_array($dr->workflow_state, ['scheduled', 'completed'], true)
            || in_array(strtolower($dr->status ?? ''), ['cancelled', 'rejected'])) {
            return 'An active scheduled defense is required before recording an outcome.';
        }
        if (! $dr->scheduled_date || ! $dr->scheduled_time || ! $dr->scheduled_end_time) {
            return 'Set a complete defense date, start time, and end time before recording an outcome.';
        }
        $date = $dr->scheduled_date->format('Y-m-d');
        $start = CarbonImmutable::parse($date.' '.$dr->scheduled_time, 'Asia/Manila');
        $end = CarbonImmutable::parse($date.' '.$dr->scheduled_end_time, 'Asia/Manila');
        if ($end->lessThanOrEqualTo($start)) {
            return 'Correct the defense time range before recording an outcome.';
        }
        $now = CarbonImmutable::now('Asia/Manila');
        if ($now->lessThan($end)) {
            $state = $now->lessThan($start) ? 'upcoming' : 'ongoing';

            return 'This defense is '.$state.'. Record its outcome only after '.$end->format('M j, Y g:i A').' (Philippine time). Refresh the page after the session ends.';
        }

        return null;
    }

    public function snapshot(DefenseRequest $dr): string
    {
        return hash('sha256', json_encode([$dr->submitted_by, $dr->defense_type, $dr->scheduled_date?->format('Y-m-d'), $dr->scheduled_time, $dr->scheduled_end_time, $dr->adviser_user_id, ...array_map(fn ($field) => $dr->$field, DefenseConflictService::PANEL_FIELDS)]));
    }

    public function current(DefenseRequest $dr, ?DefenseAcademicOutcome $row): bool
    {
        return $row && hash_equals($row->snapshot_hash, $this->snapshot($dr)) && ! in_array(strtolower($dr->status ?? ''), ['cancelled', 'rejected']) && in_array($dr->workflow_state, ['scheduled', 'completed']);
    }

    public function studentWorkflowState(DefenseRequest $dr, ?DefenseAcademicOutcome $row): ?string
    {
        if ($this->current($dr, $row)) {
            return $row->completion === 'done' ? 'completed' : 'scheduled';
        }

        return $dr->workflow_state;
    }

    public function eligible(DefenseRequest $dr, ?DefenseAcademicOutcome $row = null): bool
    {
        $row ??= DefenseAcademicOutcome::where('defense_request_id', $dr->id)->first();

        return $this->current($dr, $row) && $row->completion === 'done' && $row->result === 'passed';
    }

    public function view(DefenseRequest $dr, ?DefenseAcademicOutcome $row): ?array
    {
        return $row ? $row->only(['completion', 'result', 'subject', 'reason', 'next_stage', 'version', 'history']) + ['completed_on' => $row->completed_on?->format('Y-m-d'), 'current' => $this->current($dr, $row), 'recorded_at' => $row->updated_at->toIso8601String()] : null;
    }
}
