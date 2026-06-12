# BlueHR Modular Final

BlueHR Modular Final adalah HRIS berbasis Apache + PHP + MySQL dengan arsitektur modular untuk memudahkan pengembangan lanjutan.

## Cara Install di XAMPP

1. Extract folder ke:
   `C:\xampp\htdocs\blueHR\v1a`
2. Buat database kosong, contoh: `bluehr_v1a`.
3. Buka installer:
   `http://localhost/blueHR/v1a/install.php`
4. Isi App URL:
   `http://localhost/blueHR/v1a/public`
5. Isi koneksi database dan akun admin.
6. Setelah sukses, hapus `install.php`.
7. Login:
   `http://localhost/blueHR/v1a/public/login`

Jika `/login` tidak bisa diakses, coba:
`http://localhost/blueHR/v1a/public/index.php/login`

Lalu pastikan Apache `mod_rewrite` aktif dan `AllowOverride All`.

## Modul Final

- User Account Management
- Organization Settings, logo dan nama organisasi
- Employee master lengkap, photo, private contact, education, work permit
- Organization chart foundation, branch, department, position, job level, employment type, work location
- Attendance, flexible working hour, auto attendance cron foundation
- Leave management, allocation, reasons, holiday/cuti bersama
- Overtime
- Generic approval engine untuk cuti, lembur, pengadaan, dinas, dan request custom
- Email notification queue foundation
- Payroll, salary components, payroll approval, payroll journal
- Accurate API config, OAuth, account mapping, payroll journal posting skeleton
- Google Drive upload setting dan document routing
- WhatsApp contact dan template
- Facility/benefit/insurance eligibility by job level
- Recruitment, onboarding, reimbursement, employee loan, training
- Performance Management System lengkap: periods, KPI, OKR/goals, competencies, reviews, calibration, PIP, bonus rules, settings
- AI People Analytics foundation dengan audit log
- Reports dan audit trail

## Arsitektur Modular

Folder utama:

```text
Core        = app/Core, auth, router, database, helper, audit
Modules     = setiap fitur bisnis/integrasi punya folder sendiri
Controllers = entry point route operasional
Views       = UI per module
Settings    = setiap modul punya halaman pengaturan sendiri
```

Setiap fitur baru harus ditambahkan sebagai module baru atau extension module, bukan mencampur logic ke core.

## Catatan Accurate API

Integrasi Accurate sudah disiapkan untuk opsi A:

```text
HRIS menghitung payroll detail -> HRIS membuat payroll journal -> Accurate menerima jurnal/ringkasan accounting
```

Endpoint final journal voucher harus diverifikasi di Accurate Developer API Docs setelah login ke portal developer Accurate.

## Catatan Produksi

Sebelum production:

- Aktifkan HTTPS.
- Hapus `install.php`.
- Set `APP_DEBUG=false`.
- Gunakan database user khusus, bukan root.
- Setup backup database.
- Review permission/role.
- Enkripsi credential Accurate/Google Drive dengan `APP_KEY`.
- Konfigurasi SMTP untuk notification queue.

## Operational Settings

This build includes full operational settings pages. After installation, log in and open:

- Settings > Module Settings
- Payroll > Payroll Settings
- Attendance Settings
- Leave Settings
- Organization Settings
- Accurate Settings

Each page includes configurable operational parameters and master-data forms instead of placeholder text.
