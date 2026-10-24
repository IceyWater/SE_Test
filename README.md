# Integrated Graduate School System

Local integration of the existing Comprehensive Exam and Defense Request project with the Honorarium additions.

Open this folder in VS Code. Start XAMPP MySQL, then run `npm.cmd run dev`. Open http://127.0.0.1:8001.

The integration uses its own `grad_integration_db` database. Original project folders and the original `grad_db` database were preserved.

Finance demo: `finance@uic.edu.ph` / `password`. Existing local demo logins remain available.

See [integration setup and limitations](docs/integration-setup.md) for the module handoff, running workers, Google OAuth redirect configuration, and the Comprehensive Exam audit findings.

Validation on October24,2026:121 isolated tests passed with661 assertions; TypeScript and production build passed. The role-page checks include existing Defense Request pages and the new Finance pages. Live school-portal eligibility and final honorarium disbursement remain outside those validated paths.
