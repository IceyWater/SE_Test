<?php

use App\Services\HonorariumTrackingProgress;

it('does not report unsubmitted receipts or unprepared payments as completed', function () {
    $result = (new HonorariumTrackingProgress)->summarize([], []);
    expect($result['percent'])->toBe(0)->and($result['label'])->toBe('Next: Receipt sent');
});

it('waits for every recipient before completing a payment milestone', function () {
    $documents = [['document_type' => 'payment_receipt', 'status' => 'verified'], ['document_type' => 'panel_breakdown', 'status' => 'verified']];
    $result = (new HonorariumTrackingProgress)->summarize($documents, [['status' => 'claimed'], ['status' => 'prepared']]);
    expect($result['percent'])->toBe(63)->and($result['label'])->toBe('Next: All payments approved');
    $result = (new HonorariumTrackingProgress)->summarize($documents, [['status' => 'claimed'], ['status' => 'claimed'], ['status' => 'voided']]);
    expect($result['percent'])->toBe(100)->and($result['label'])->toBe('All payments claimed');
});

it('does not claim payment completion when Finance evidence needs review', function () {
    $documents = [['document_type' => 'payment_receipt', 'status' => 'verified'], ['document_type' => 'panel_breakdown', 'status' => 'rejected']];
    $result = (new HonorariumTrackingProgress)->summarize($documents, [['status' => 'claimed']]);
    expect($result['label'])->toBe('Returned by Finance')->and($result['steps'][7]['done'])->toBeFalse();
    $result = (new HonorariumTrackingProgress)->summarize($documents, [['status' => 'claimed', 'requires_review' => true]]);
    expect($result['label'])->toBe('Requires Finance review')->and($result['steps'][5]['done'])->toBeFalse();
});
