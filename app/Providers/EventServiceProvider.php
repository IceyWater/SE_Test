<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Events\ExamStatusChanged;
use App\Listeners\SendExamWorkflowNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ExamStatusChanged::class => [
            SendExamWorkflowNotifications::class,
        ],
        'App\Events\SomeEvent' => [
            'App\Listeners\SomeListener',
        ],
    ];

    /**
     * Register any event listener closures.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // register User observer to keep advisers in sync when users are created/updated
        User::observe(UserObserver::class);
    }
}
