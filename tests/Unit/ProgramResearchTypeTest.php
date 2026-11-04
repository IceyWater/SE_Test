<?php

use App\Services\ProgramResearchType;

it('uses the UIC curriculum research type including professional-track exceptions', function ($program, $label, $options) {
    $result = (new ProgramResearchType)->forProgram($program);
    expect($result['label'])->toBe($label)->and($result['determined'])->toBeTrue()->and($result['subject_options'])->toBe($options);
})->with([
    ['Doctor in Business Management', 'Dissertation', ['Dissertation 1', 'Dissertation 2']],
    ['Doctor of Philosophy in Counseling', 'Dissertation', ['Dissertation 1', 'Dissertation 2']],
    ['Ph.D. in Pharmacy', 'Dissertation', ['Dissertation 1', 'Dissertation 2']],
    ['DBM-IS', 'Dissertation', ['Dissertation 1', 'Dissertation 2']],
    ['PhDAL', 'Dissertation', ['Dissertation 1', 'Dissertation 2']],
    ['Master of Arts in Counseling', 'Thesis', ['Thesis 1', 'Thesis 2']],
    ['Master of Arts in Education major in Mathematics', 'Thesis', ['Thesis 1', 'Thesis 2']],
    ['MAED - English', 'Thesis', ['Thesis 1', 'Thesis 2']],
    ['Master of Science in Pharmacy', 'Thesis', ['Thesis 1', 'Thesis 2']],
    ['Master in Engineering Education major in Civil Engineering', 'Thesis', ['Thesis 1', 'Thesis 2']],
    ['Master in Business Administration With Thesis', 'Thesis', ['Thesis 1', 'Thesis 2']],
    ['MBA Non-Thesis', 'Business Plan', ['Business Plan']],
    ['Master in Business Administration (Non Thesis)', 'Business Plan', ['Business Plan']],
    ['MBA for Health Professionals', 'Integrating Course', ['Integrating Course']],
    ['Master in Information Technology', 'Capstone', ['Capstone 1', 'Capstone 2']],
    ['Master in Information System', 'Capstone', ['Capstone 1', 'Capstone 2']],
    ['MIS', 'Capstone', ['Capstone 1', 'Capstone 2']],
    ['Master in Counseling', 'Capstone', ['Capstone']],
    ['MIC', 'Capstone', ['Capstone']],
    ['Master in Pastoral Ministry with Specialization in Pastoral Management', 'Capstone', ['Capstone']],
]);

it('requests track confirmation rather than guessing for ambiguous or missing programs', function ($program) {
    $result = (new ProgramResearchType)->forProgram($program);
    expect($result['determined'])->toBeFalse()->and($result['label'])->toBe('Needs program / track confirmation');
})->with([null, '', 'MBA', 'Master in Business Administration', 'Unrecognized course', 'Master of Arts (Non-Thesis)']);
