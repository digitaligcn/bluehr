# BlueHR Operational Settings Finalization

This build changes module Settings from descriptive placeholder lists into operational configuration pages.

## What changed

Each module settings page now contains:

1. Operational parameter forms saved into `app_settings` by module group.
2. Master data forms for supported module objects.
3. Master data tables showing existing records.
4. Generic secure record-store route using an allowlist.
5. Audit log hooks for setting and master-data changes.

## New/updated routes

- `GET /settings`
- `POST /settings/save`
- `POST /settings/record/store`
- `GET /employees/settings`
- `GET /organization/settings`
- `GET /attendance/settings`
- `GET /leave/settings`
- `GET /overtime/settings`
- `GET /payroll/settings`
- `GET /performance/settings`
- `GET /approval/settings`
- `GET /facilities/settings`
- `GET /documents/settings`
- `GET /accurate/settings`
- `GET /ai/settings`
- `GET /whatsapp/settings`

## Payroll settings now include operational forms

- Operational Parameters
- BPJS/PPh21 Parameters
- Approval Workflow
- Payslip Visibility
- Salary Components master data
- Payroll Periods master data
- Accurate Journal Mapping master data

## Notes

This remains modular: settings logic is handled by the module controller and each setting is grouped by module key. Future improvements can split each module into its own controller under `Modules/<ModuleName>/Controllers` without changing the database structure.
