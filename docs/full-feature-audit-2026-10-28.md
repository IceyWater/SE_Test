# Integrated feature audit — October 28, 2026

This audit fixes reproducible defects in the local integrated project and adds regression coverage. It does not certify that every possible error or every manuscript test case has been eliminated.

## Changes

- **Honorarium tracking update (November 4, 2026):** The defense workspace now presents a table with faculty, school-year/semester, and Awaiting Finance / Verified / Receipts Available filters; a receipt-to-claim progress bar; Finance review timestamps; and panel payout states. It refreshes its progress data every 30 seconds. AA and Dean can record a staff follow-up, which is timestamped in the activity history, creates an in-app notification, and sends email to the selected staff role. Dean can view and download program-by-semester totals. These changes do not yet add Dean PDF signing or the comprehensive-exam proctor/teacher honorarium handoff.
- Administrative Assistants can review and approve or reject comprehensive exam applications and exam receipts, as requested by the project owner. Application reviews record the reviewer, review time, and remarks; rejections require a reason. Students receive an in-app application decision notice. Already reviewed applications cannot be reviewed again. Coordinator review remains limited to assigned programs.
- AA navigation distinguishes Exam Receipts from Defense Receipts. The previously missing defense receipt screen now supports verification, remarks, bulk decisions, batch creation, and CSV export. Ownership checks prevent one AA from changing another AA's assigned verifications, including through mixed bulk requests.
- Adviser and coordinator endpoints now enforce their roles. Non-adviser accounts cannot be registered as advisers; students cannot attach themselves to arbitrary advisers, and a second adviser cannot silently replace an active or pending relationship. Adviser details and generated endorsement attachments enforce ownership.
- Document generation now returns the JSON expected by the dialog, supports authorized inline PDF previews, preserves PDF page dimensions and orientation, and retains the previous document if replacement fails. Regeneration keeps the attached endorsement link current. PDF uploads are parsed before saving, actual page counts are recorded, and invalid mapping dimensions are rejected. Empty mapping lists can be saved.
- My Documents includes AI detection certificates and endorsements and uses the correct storage disk for metadata. Student records enforce staff permissions and correctly combine search with filters.
- Schedule responses handle stored string times. Broken Dean and legacy report links now redirect to the working screens. An obsolete sample-mail endpoint no longer sends to a hardcoded third-party address. Unused jsPDF was removed and compatible frontend dependency security updates were installed.
- Honorarium tracking uses the current Finance/payment milestones in a compact stepper. Authorized staff can record manual follow-ups with an emailed notice and timestamped activity; Dean reporting groups disbursements by program, school year, and semester with CSV export. No automatic reminder schedule was added.

## Verification

| Area | Evidence |
| --- | --- |
| Automated workflow and permission regressions | **303 tests passed, 2,145 assertions**, using isolated test data and fake mail/storage where needed |
| Role pages | **73 requests: 65 HTTP 200 and 8 expected redirects**; no missing route handlers |
| AA browser verification | Applications menu, comprehensive exam applications, exam receipts, and defense receipts checked in the local browser |
| Frontend | TypeScript, ESLint, Prettier checks and production build passed |
| PHP | Pint formatting check passed |
| Frontend dependencies | npm audit reported zero known vulnerabilities after compatible updates |

Covered regression areas include authentication and role boundaries, adviser assignment, defense submission and review, notifications and reminders, scheduling, comprehensive exams and receipts, payment-rate imports, generated documents, honorarium tracking and finance workflows. The role-page report is [diagram-audit-results.json](diagram-audit-results.json); GET checks alone do not prove browser rendering or workflow transitions. Positive and negative mutations were exercised in isolated automated tests, without approving real local applications or sending real emails.

## Updating another local installation

After pulling this commit, run `npm ci` and `php artisan migrate`. Restart the local development process. Compiled frontend assets are included in the repository; use the current build or restart Vite rather than retaining a stale dev process.

The earlier migration adds application review tracking and was applied to the working local database after a private SQL backup. The honorarium follow-up migration adds a nullable recipient role; it is already recorded as applied on the current local database. Existing local records were preserved. The supplied older SQL backup was not imported, and database dumps, environment credentials, and uploaded files are not included in the commit.

The November 4 honorarium restoration was checked separately: 63 focused honorarium/regression tests passed (800 assertions), and the Honorarium workspace passed TypeScript, ESLint, and Prettier checks. Manual follow-up mail was tested with fake mail; this does not verify production SMTP delivery.

## Remaining client and environment acceptance

- Verify real Google OAuth callbacks, SMTP/Gmail delivery, queue workers, and scheduled reminders on the intended deployment. Fake-mail tests do not establish real delivery.
- Confirm the school-portal data contract and eligibility rules. The reviewer list still has legacy local eligibility checks; missing local grade/document/account tables are currently shown as unmet requirements. Student submission uses the separate comprehensive eligibility service. These need a single client-approved source before treating the reviewer list as authoritative eligibility evidence.
- Confirm permit issuance rules, the order of exam approval and payment, official rates, document field positions, signature authorization, and actual finance disbursement procedures with the responsible groups/client.
- Complete user acceptance testing with representative student, adviser, coordinator, AA, Dean, and Finance records. Empty live screens confirm access and layout; populated workflows are covered by automated tests rather than live client decisions in this audit.
