import assert from 'node:assert/strict';
import { Buffer } from 'node:buffer';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import ts from 'typescript';
const source = readFileSync(new URL('../resources/js/utils/honorarium-report.ts', import.meta.url), 'utf8');
const js = ts.transpileModule(source, { compilerOptions: { module: ts.ModuleKind.ESNext } }).outputText;
const { summarizeHonorarium } = await import('data:text/javascript;base64,' + Buffer.from(js).toString('base64'));
const record = (extra = {}) => ({ amount: '1200.10', school_year: '2026-2027', semester: '1st', status: 'prepared', requires_review: false, defense_request: { program: 'MA Education' }, ...extra });
test('keeps each program, school year and semester separate', () => {
    const rows = [record(), record({ semester: '2nd' }), record({ school_year: '2025-2026' }), record({ defense_request: { program: 'DBM' } })];
    assert.equal(summarizeHonorarium(rows).length, 4);
    assert.equal(summarizeHonorarium(rows.filter((row) => row.semester === '2nd')).length, 1);
    assert.deepEqual(summarizeHonorarium([]), []);
});
test('counts money once and excludes review and voided records from payment buckets', () => {
    const [group] = summarizeHonorarium([record(), record({ amount: '0.20' }), record({ status: 'claimed' }), record({ status: 'disbursed', requires_review: true }), record({ status: 'voided', requires_review: true })]);
    assert.equal(group.records, 5);
    assert.equal(group.prepared, 120030);
    assert.equal(group.claimed, 120010);
    assert.equal(group.review, 120010);
    assert.equal(group.voided, 120010);
    assert.equal(group.disbursed, 0);
    assert.equal(group.approved, 0);
});
