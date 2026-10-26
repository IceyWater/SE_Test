<?php

use App\Models\DefenseRequest;
use App\Models\Panelist;
use App\Models\ProgramRecord;
use Database\Seeders\LegacyMockDefenseScheduleSeeder;

it('creates repeatable future mock schedules from the workbook student rosters', function () {
    $seeder = new LegacyMockDefenseScheduleSeeder;
    $seeder->run();

    expect(DefenseRequest::count())->toBe(134)
        ->and(Panelist::count())->toBe(9)
        ->and(ProgramRecord::whereIn('program', ['MAEM', 'MAED-PE', 'MAED-GC', 'MAEE', 'MAED-SOC', 'MAED-MATH', 'MAED-ITI', 'MAVE', 'MARE'])->count())->toBe(9);

    expect(DefenseRequest::all()->every(
        fn (DefenseRequest $request) => $request->scheduled_date->isFuture() && $request->scheduled_date->isWeekday()
    ))->toBeTrue();

    $firstRequest = DefenseRequest::where('school_id', sprintf('MOCK-%d-0001', now()->year))->firstOrFail();
    expect($firstRequest->workflow_state)->toBe('scheduled')
        ->and($firstRequest->scheduling_status)->toBe('scheduled')
        ->and($firstRequest->thesis_title)->toStartWith('[MOCK]')
        ->and($firstRequest->scheduled_date->isFuture())->toBeTrue()
        ->and($firstRequest->defense_chairperson)->toStartWith('Dr. Mock Chairperson')
        ->and($firstRequest->defense_panelist1)->toStartWith('Mock Panelist');

    $seeder->run();

    expect(DefenseRequest::count())->toBe(134)
        ->and(Panelist::count())->toBe(9);
});
