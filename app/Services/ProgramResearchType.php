<?php

namespace App\Services;

class ProgramResearchType
{
    // Based on curricula linked at https://graduateschool.uic.edu.ph/program-offerings (AY 2026-2027).
    // Program classification is not evidence of current enrollment in subject 1 or 2.
    public function forProgram(?string $program): array
    {
        $name = strtolower(trim(preg_replace('/\s+/', ' ', $program ?? '')));
        $code = preg_replace('/[^a-z0-9]/', '', $name);
        $type = null;
        if (preg_match('/\bdoctor(?:al|ate)?\b|\bph\.?\s?d\.?\b|\b(?:dbm|dba|dpfil)\b/', $name)
            || str_starts_with($code, 'phd') || in_array($code, ['dbmis'])) {
            $type = 'Dissertation';
        } elseif (str_contains($name, 'business administration') || str_starts_with($code, 'mba')) {
            $type = match (true) {
                str_contains($name, 'health professional') => 'Integrating Course',
                (bool) preg_match('/non[ -]?thesis/', $name), str_contains($name, 'business plan') => 'Business Plan',
                str_contains($name, 'with thesis'), (bool) preg_match('/(?<!non-)\bthesis\b/', $name) => 'Thesis',
                default => null,
            };
        } elseif (str_contains($name, 'pastoral') || in_array($code, ['mpm', 'mpmp'])) {
            $type = 'Capstone';
        } elseif (preg_match('/\bmasters? (?:in|of) information (?:technology|systems?)\b/', $name) || in_array($code, ['mit', 'mis'])) {
            $type = 'Capstone 1/2';
        } elseif (preg_match('/\bmasters? (?:in|of) counsel(?:l)?ing\b/', $name) || in_array($code, ['mc', 'mic'])) {
            $type = 'Capstone';
        } elseif (preg_match('/non[ -]?thesis/', $name)) {
            $type = null;
        } elseif (preg_match('/\bmasters? (?:of|in) (?:arts|science|engineering education)\b/', $name)
            || preg_match('/^(?:maed|maem|maee|mave|mate|mee|msph|msmt|mac|ma|ms)(?:[ -]|$)/', $name)) {
            $type = 'Thesis';
        }
        $options = match ($type) {
            'Thesis' => ['Thesis 1', 'Thesis 2'],
            'Dissertation' => ['Dissertation 1', 'Dissertation 2'],
            'Capstone 1/2' => ['Capstone 1', 'Capstone 2'],
            'Capstone' => ['Capstone'],
            'Business Plan' => ['Business Plan'],
            'Integrating Course' => ['Integrating Course'],
            default => DefenseAcademicProgress::SUBJECTS,
        };

        return ['label' => $type === 'Capstone 1/2' ? 'Capstone' : ($type ?? 'Needs program / track confirmation'),
            'determined' => $type !== null, 'subject_options' => $options,
            'source_url' => 'https://graduateschool.uic.edu.ph/program-offerings'];
    }
}
