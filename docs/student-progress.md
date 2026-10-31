# Adviser student progress

Open **My Students → Assigned → View Progress** as the student’s accepted adviser.

The page shows the current defense stage and workflow, Proposal/Prefinal/Final milestones, and defense history. Defense information comes from existing requests. A scheduled or automatically completed defense is not proof of attendance or graduation; a matching service confirmation is required to label a defense as held.

Advisers may record enrollment year, program completion year, semester, and subjects after checking official school records. Each save requires a source/reference note and confirmation that school records were checked. Missing years stay unavailable; they are never inferred from account dates or student numbers. Completion year must be on or after enrollment year, and future years are rejected.

These are adviser-recorded references, not automatic Registrar certifications. Program completion is separate from final defense completion. Cached school-portal subjects are shown separately with their last-sync date and do not establish live enrollment.

Only the accepted adviser can read or update this page. Pending, rejected, revoked, unrelated advisers and other roles cannot access it. Prior record values are preserved in the database history; concurrent stale edits are rejected so they cannot silently overwrite newer information.

## Setup after pulling

Run `php artisan migrate` before opening My Students. The new migration creates only `student_progress_records`; it does not overwrite student or defense records. Use `npm install` and `npm run build` when rebuilding assets, or the normal development command.

## Validation

`php artisan test --filter=AdviserStudentProgressTest` covers authorization, honest missing values, record validation, history, stale edits, defense service evidence, current stage selection, and school-subject privacy.

## Student list filters

The Assigned tab combines name/ID/email/program search with defense stage, enrollment year, completion year, recorded semester, and subject name/code filters. Subject and semester filters use adviser-recorded information, not cached portal subjects. The default sort is newest progress update first (latest defense update or academic record update). Enrollment year can be sorted in either direction; missing years and update dates stay last. Name A–Z, matching counts, and Reset filters are available. Pending assignments are not filtered by academic progress.
