# Final Changelog

Perubahan yang difinalisasi:

1. Menghapus duplicate route `PlaceholderController` sehingga halaman operasional tidak lagi menampilkan placeholder.
2. Menambahkan route operasional untuk Approval, Facilities, Documents, Google Drive, WhatsApp, AI, dan Performance.
3. Menambahkan setting per modul.
4. Menambahkan Organization Settings untuk logo dan nama organisasi.
5. Menambahkan Performance Management System lebih lengkap.
6. Membersihkan schema SQL agar fresh install lebih stabil di XAMPP/MySQL/MariaDB.
7. Memperbaiki deteksi route agar `/public/login` dan `/public/index.php/login` bisa digunakan.
8. Memastikan asset UI memakai path `public/assets`.
9. Sidebar dibuat scrollable untuk menu panjang.
10. Menambahkan README instalasi dan troubleshooting.

## Operational Settings Finalization

- Replaced generic list-based module settings with operational forms.
- Added generic `/settings/record/store` allowlisted master-data handler.
- Added module-specific settings fields for Employee, Organization, Attendance, Leave, Overtime, Payroll, Accurate, Recruitment, Onboarding, Reimbursement, Loan, Training, Approval, Facilities, Documents, AI, and WhatsApp.
- Added master-data creation tables for branches, departments, positions, job levels, employment types, work locations, working schedules, shift templates, holidays, leave types, leave reasons, salary components, payroll periods, Accurate journal mappings, approval types, facilities, insurance, employee tags, skill types, departure reasons, and WhatsApp templates.
- Added CSS refinements for operational settings forms and tables.
