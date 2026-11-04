<?php

namespace Database\Seeders;

use App\Models\StudentRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoStudentRecordsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo student records may only be added to a local or testing database.');
        }
        $samples = [
            ['Mara', 'Santos', 'MAED', '2026', 'Proposal'],
            ['Luis', 'Reyes', 'MIT', '2026', 'Prefinal'],
            ['Ana', 'Cruz', 'MBA', '2025', 'Final'],
            ['Paolo', 'Garcia', 'MCS', '2025', 'Proposal'],
            ['Nina', 'Torres', 'MAED', '2024', 'Prefinal'],
            ['Carlo', 'Ramos', 'MIT', '2024', 'Final'],
            ['Lara', 'Mendoza', 'MBA', '2026', 'Not yet defended'],
            ['Marco', 'Flores', 'MCS', '2026', 'For revision'],
        ];
        DB::transaction(function () use ($samples) {
            foreach ($samples as $index => [$first, $last, $program, $year, $stage]) {
                StudentRecord::firstOrCreate(['student_id' => 'DEMO-RECORD-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)], [
                    'first_name' => 'Demo '.$first, 'last_name' => $last, 'program' => $program,
                    'school_year' => $year, 'course_section' => 'DEMO DATA',
                    'academic_status' => 'DEMO ONLY — '.$stage,
                    'or_number' => null, 'payment_date' => null,
                ]);
            }
        });
        $this->command?->info('Eight demo student records are available. No accounts, receipts, defense requests, or payment transactions were created.');
    }
}
