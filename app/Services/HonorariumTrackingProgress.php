<?php

namespace App\Services;

class HonorariumTrackingProgress
{
    public function summarize(array $documents, array $payments): array
    {
        $receipt = collect($documents)->firstWhere('document_type', 'payment_receipt');
        $panel = collect($documents)->firstWhere('document_type', 'panel_breakdown');
        $current = collect($payments)->where('status', '!=', 'voided');
        $review = $current->contains(fn ($row) => (bool) ($row['requires_review'] ?? false));
        $allAt = fn (array $statuses) => $current->isNotEmpty() && ! $review && $current->every(fn ($row) => in_array($row['status'], $statuses, true));
        $steps = [
            ['label' => 'Receipt sent', 'done' => in_array($receipt['status'] ?? '', ['submitted', 'verified', 'rejected'], true)],
            ['label' => 'Receipt verified', 'done' => ($receipt['status'] ?? '') === 'verified'],
            ['label' => 'Panel breakdown sent', 'done' => in_array($panel['status'] ?? '', ['submitted', 'verified', 'rejected'], true)],
            ['label' => 'Panel breakdown verified', 'done' => ($panel['status'] ?? '') === 'verified'],
            ['label' => 'Payments prepared', 'done' => $current->isNotEmpty()],
            ['label' => 'All payments approved', 'done' => $allAt(['approved', 'disbursed', 'claimed'])],
            ['label' => 'All payments disbursed', 'done' => $allAt(['disbursed', 'claimed'])],
            ['label' => 'All payments claimed', 'done' => $allAt(['claimed'])],
        ];
        // A changed/rejected Finance document invalidates later payment milestones.
        if (! $steps[1]['done'] || ! $steps[3]['done'] || $review) {
            foreach ([5, 6, 7] as $index) {
                $steps[$index]['done'] = false;
            }
        }
        $next = collect($steps)->firstWhere('done', false);
        $returned = collect($documents)->contains('status', 'rejected');

        return [
            'steps' => $steps,
            'percent' => (int) round(collect($steps)->where('done', true)->count() / count($steps) * 100),
            'label' => $review ? 'Requires Finance review' : ($returned ? 'Returned by Finance' : ($next ? 'Next: '.$next['label'] : 'All payments claimed')),
        ];
    }
}
