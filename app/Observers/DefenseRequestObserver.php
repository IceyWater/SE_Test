<?php

namespace App\Observers;

use App\Models\DefenseRequest;
use App\Services\DefenseActivityNotifications;

class DefenseRequestObserver
{
    public function created(DefenseRequest $request): void
    {
        $service = app(DefenseActivityNotifications::class);
        if (in_array($request->workflow_state, ['submitted', 'adviser-review'])) $service->submitted($request);
        if ($request->scheduled_date) $service->scheduled($request);
    }

    public function updated(DefenseRequest $request): void
    {
        $service = app(DefenseActivityNotifications::class);
        if ($request->wasChanged('workflow_state')) $service->changed($request, $request->getOriginal('workflow_state'));
        if ($request->wasChanged(['scheduled_date', 'scheduled_time', 'scheduled_end_time', 'defense_venue'])) {
            $service->clearUnreadReminders($request);
            $service->scheduled($request);
        }
        if ($request->wasChanged(['adviser_user_id', 'coordinator_user_id']) && in_array($request->workflow_state, ['submitted', 'adviser-review'])) {
            $service->submitted($request);
        }
        if ($request->wasChanged('status') && in_array($request->status, ['Cancelled', 'Completed', 'Rejected'])) $service->clearUnreadReminders($request);
        if ($request->wasChanged('workflow_state') && in_array($request->workflow_state, ['cancelled', 'completed', 'coordinator-rejected', 'adviser-rejected', 'rejected'])) {
            $service->clearUnreadReminders($request);
        }
    }
}
