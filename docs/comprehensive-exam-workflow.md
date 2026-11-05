# Comprehensive Exam Workflow

## Workflow

Student submission is reviewed by the Administrative Assistant, then approved or rejected by a dedicated Registrar. The student can resubmit an AA-lacking or Registrar-rejected application. Registrar-approved students may submit a fee payment; rejected receipts create a new application-scoped attempt. AA or a program-scoped Coordinator verifies the receipt. Coordinators then schedule every subject, assign a room, and invite one or two Advisers to proctor. Advisers accept or decline their own invitations.

Application, requirement, payment, scheduling, and proctor decisions are checked server-side and recorded in workflow/event tables. Receipt files are stored on the private local disk and downloaded only through an authenticated, authorized route. Room and proctor time overlaps are rejected.

## Configuration and deployment

- Set `COMPRE_EXAM_FEE` to the required fee. A missing or zero fee disables payment submission.
- Keep `EXAM_DEMO_ELIGIBILITY=false` except for explicitly marked local demo accounts. The demo path is local-only.
- `EXAM_MAX_RECEIPT_KB`, `EXAM_AA_UNDO_MINUTES`, and `EXAM_SNAPSHOT_MAX_AGE_HOURS` control upload size, AA undo window, and snapshot freshness.
- SMS is disabled by default. When enabled globally and for an exam setting, the current adapter only writes a masked-recipient message to application logs; no SMS provider is configured.
- The application queue worker must be running to deliver queued workflow email. The default mail transport in `.env.example` logs email rather than sending it externally.
- Apply migrations using the repository’s normal deployment process. The additive payment migration preserves legacy rows and links them to an application only when the mapping is unambiguous. Its rollback intentionally refuses to remove attempt history when repeated payments exist.
- Existing receipt files may still reside under the legacy public storage path. Review a backup and run `php artisan exam:secure-payment-receipts` first, then `php artisan exam:secure-payment-receipts --apply` during a controlled deployment. The apply mode copies, verifies, and removes public copies; the authenticated receipt route serves private-disk files only.

## Operational limitations

Eligibility is fail-closed and reads verified `student_academic_snapshots` plus an `exam_settings` window. This implementation does not yet include a production MIS snapshot synchronization job or admin UI for exam windows and room maintenance beyond Coordinator room creation. Until trusted snapshots and settings are provisioned, regular students remain ineligible. The legacy request-time scraping endpoint now returns HTTP 410.

The workflow does not yet include downloadable final exam permits, a Dean/Finance approval tier, comprehensive-exam reporting exports, reminders for stale applications, or a production SMS provider. These items require the corresponding approved policy and operational integrations.
