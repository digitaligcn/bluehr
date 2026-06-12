# Accurate Integration Notes

BlueHR uses integration option A:

HRIS calculates payroll details, generates payroll summary and payroll journal, then posts accounting summary to Accurate Online.

Public Accurate integration notes used for this build:

- Accurate Online API uses OAuth 2.0.
- API access uses Bearer access token.
- Scope controls which modules can be accessed.
- Applications must determine/select the Accurate database before using user data APIs.
- Accurate API has a documented rate limit of 8 API calls per second and 8 parallel running processes.
- Accurate may return HTTP 308 Permanent Redirect if endpoint host changes; clients should handle and update host.

Important: Exact journal voucher endpoint and scope names must be verified in Accurate Developer API Docs after login at account.accurate.id/developer/api-docs.do.
