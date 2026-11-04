import assert from 'node:assert/strict';
import { Buffer } from 'node:buffer';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import ts from 'typescript';
const source = readFileSync(new URL('../resources/js/utils/defense-history.ts', import.meta.url), 'utf8');
const js = ts.transpileModule(source, { compilerOptions: { module: ts.ModuleKind.ESNext } }).outputText;
const { defenseHistoryLabel: label, defenseHistoryDescription: description } = await import('data:text/javascript;base64,' + Buffer.from(js).toString('base64'));
const classified = { action: 'panel-classified', to_state: 'scheduled', comment: JSON.stringify({ previous: [], current: { 'panel:1:Panel Member': 'internal', 'panel:2:Panel Chair': 'external' }, notes: 'Confirmed affiliations.' }) };
test('classification event does not masquerade as a scheduling action', () => {
    assert.equal(label(classified), 'Panel classifications updated');
    assert.equal(label(classified, 'Scheduled'), 'Panel classifications updated');
});
test('renders classification counts and optional notes without audit keys', () => {
    assert.equal(description(classified), 'Panel classifications saved: 1 internal, 1 external.\nConfirmed affiliations.');
    assert.doesNotMatch(description(classified), /panel:|previous|current/);
});
test('supports older structured notes and missing notes', () => {
    const entry = { to_state: 'scheduled', notes: JSON.stringify({ previous: {}, current: { a: 'internal' }, notes: null }) };
    assert.equal(label(entry), 'Panel classifications updated');
    assert.equal(description(entry), 'Panel classifications saved: 1 internal, 0 external.');
});
test('malformed classification data has a readable fallback', () => {
    assert.equal(description({ action: 'panel-classified', comment: '{broken' }), 'Panel classifications saved.');
});
test('preserves reviewer reasons, corrections and actual schedule notes', () => {
    assert.equal(description({ action: 'adviser-rejected', comment: 'Revise chapter 2.' }), 'Revise chapter 2.');
    assert.equal(description({ student_response: 'Replaced the manuscript.' }), 'Replaced the manuscript.');
    assert.equal(label({ action: 'scheduled', to_state: 'scheduled' }), 'Scheduled');
    assert.equal(description({ action: 'scheduled', comment: 'Be on time.' }), 'Be on time.');
});
test('service confirmation stays distinct from academic completion', () => {
    assert.equal(label({ action: 'service-confirmed', to_state: 'completed' }, 'Completed'), 'Defense service confirmed');
});
