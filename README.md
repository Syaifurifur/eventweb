# EventHub / EventWeb - Aplikasi Penjadwalan & Manajemen Event Kampus

Aplikasi Enterprise Penjadwalan, Manajemen Event, Task Manager, CRM Marketing Akademik, Dokumen AI, dan Program **One Campus** (Five OJT, Sixty School, Five Spanduk) berbasis PHP dengan **100% Strict Dual-Engine Parity**:
- **CodeIgniter 4**: `http://127.0.0.1:8080`
- **Laravel**: `http://127.0.0.1:8081`

---

## Menjalankan Aplikasi

Pastikan layanan MySQL di XAMPP telah berjalan:
```powershell
# 1. Jalankan MySQL (Port 3306)
& 'C:\xampp\mysql\bin\mysqld.exe' --defaults-file='C:\xampp\mysql\bin\my.ini' --standalone

# 2. Jalankan CodeIgniter 4 (Port 8080)
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8080 -t public server.php

# 3. Jalankan Laravel (Port 8081)
Set-Location -LiteralPath 'c:\xampp\htdocs\eventweb\laravel'
& 'C:\xampp\php\php.exe' artisan serve --host=127.0.0.1 --port=8081
```

Kredensial Default Login:
- **Admin Sistem / Super Admin**: `admin` / `password` (atau `admin@eventhub.test`)

---

## Rangkuman Update Fitur Terbaru (November 2026)

### 1. One Campus Sixty School - Klasterisasi Tahun PMB & Klaster Baru Khusus Superadmin
- **Klaster Berjalan & Sebelumnya**: Secara default hanya menampilkan periode PMB yang sedang berjalan (**`September 2026 - September 2027`**) dan periode-periode sebelumnya (**`September 2025 - September 2026`**, **`September 2024 - September 2025`**, dst.).
- **Tombol Pembuatan Klaster Baru (Khusus Superadmin)**:
  - Tersedia tombol **"+ Buat Klasterisasi Baru (Superadmin)"** yang memunculkan dialog pembuatan klaster tahun mendatang atau periode custom.
  - Klaster yang dibuat otomatis tersimpan ke `system_settings` (`one_campus_custom_pmb_periods`) dan langsung aktif sebagai filter klaster.

### 2. Fitur Upload Data PMB dari Excel, Template Multi-Sheet & Integrasi 27 Kode Kampus UBSI
- **Integrasi 27 Data Kode Kampus & Kode UBSI**:
  - Tabel database `campuses` dan `one_campus_pmb_students` kini menyimpan field resmi: `kode_ubsi` (VARCHAR 20) dan `campus_code` (VARCHAR 30).
  - Master data 27 kampus resmi UBSI telah terisi dan terintegrasi:
    1. *01 / D2*: Margonda
    2. *02 / A1*: Fatmawati
    3. *03 / C1*: Tangerang
    4. *04 / E1*: Bekasi (Cut Mutia)
    5. *05 / E2*: Kaliabang
    6. *06 / F3*: Kalimalang
    7. *07 / B2/B4*: Kramat 98 / Salemba 22
    8. *09 / K1*: Jogjakarta
    9. *11 / F1*: Jatiwaringin
    10. *12 / G1*: Ciledug
    11. *13 / H1*: Bogor B (Cilebut)
    12. *14 / E5*: Karawang
    13. *15 / E6*: Cikampek
    14. *17 / J1*: Tasikmalaya
    15. *21 / K6*: Purwokerto
    16. *24 / E4*: Cikarang
    17. *25 / M1*: Cengkareng
    18. *26 / M2*: Slipi
    19. *27 / N1*: BSD
    20. *28 / O1*: Sukabumi
    21. *29 / C3*: Ciputat
    22. *30 / P1*: Pontianak
    23. *31 / F4*: DWS B (Dewi Sartika B)
    24. *35 / K7*: Tegal
    25. *37 / B7*: Pemuda
    26. *38 / E3*: Cibitung
    27. *39 / H2*: Bogor A (Merdeka)
- **Template Excel PMB Resmi Multi-Sheet**:
  - Route `GET /one-campus/pmb-template` menghasilkan file `.xlsx` dengan 2 sheet kerja:
    - **Sheet 1 (`Data Mahasiswa PMB`)**: Kolom terstandarisasi *No, Nama Mahasiswa, Asal Sekolah, NPSN, Kode Kampus, Kode UBSI, Kampus, Program Studi, No Pendaftaran, No HP / WhatsApp, Email, Status* disertai baris contoh data riil kampus.
    - **Sheet 2 (`Referensi Kode Kampus`)**: Panduan referensi lengkap 27 kampus dengan kolom *No, Kode UBSI, Kampus, Kode Kampus*.
- **Mesin Pencocokan Otomatis Cerdas Saat Upload PMB**:
  - Saat membaca berkas Excel, sistem secara cerdas mencocokkan kampus mahasiswa dengan urutan prioritas:
    1. *Match Kode UBSI* (contoh: `'01'`, `'02'`, `'11'`, `'39'`, dll.)
    2. *Match Kode Kampus* (contoh: `'D2'`, `'A1'`, `'E1'`, `'B2/B4'`, dll.)
    3. *Match Nama/Alias Kampus* (contoh: `'Margonda'`, `'Fatmawati'`, dll.)
    4. *Fallback Kampus Default* yang dipilih pada modal upload.
  - Sekolah yang bersangkutan otomatis ditautkan ke kampus yang cocok, dan mahasiswa disimpan dengan `kode_ubsi` serta `campus_code`.
  - Jumlah perolehan mahasiswa (**`pmb_count`**) pada sekolah diperbarui secara otomatis.

### 3. Detail Data Mahasiswa Baru per Sekolah & Tampilan Kode Kampus
- **Modal Interaktif di Sixty School**:
  - Kolom **Perolehan PMB** pada tabel rekap menampilkan badge interaktif (*contoh: `15 Mhs 👁`*).
  - Mengklik badge langsung membuka modal rincian mahasiswa baru via AJAX (`/one-campus/schools/{id}/students`) lengkap dengan kolom **Kampus / Kode** (*contoh: `[A1] Fatmawati (02)`*).
- **Tabel Rincian di Halaman Edit Sekolah**:
  - Pada halaman khusus edit sekolah (`/one-campus/schools/{id}/edit`), tabel rincian mahasiswa baru menampilkan kolom **Kampus / Kode** berlabel badge warna, nomor pendaftaran, prodi, dan opsi hapus siswa.

### 4. Fasilitas Sortir Data PMB (Banyak ke Sedikit)
- **Kontrol Sortir Cepat**:
  - Tombol aksi **"⇅ PMB Terbanyak (Banyak ke Sedikit)"**, **"⇅ PMB Tersedikit"**, dan **"Nama Sekolah (A-Z)"**.
  - Header kolom **"Perolehan PMB"** dapat diklik langsung untuk membalikkan urutan (toggle DESC/ASC) disertai indikator panah (▼/▲).
  - Ketika diurutkan dari terbanyak ke tersedikit, tabel menampilkan badge medali peringkat (🥇 Juara 1, 🥈 Juara 2, 🥉 Juara 3, dan #4, #5, dst.).

### 5. Rekomendasi Cerdas AI di Dashboard Utama (Analisis Interaksi Sekolah) & Scoping Per User
- **Widget Rekomendasi Strategis AI**:
  - Muncul di dashboard utama (`/dashboard`) menggabungkan 4 sumber data: **Perolehan PMB**, **Riwayat Kunjungan Sekolah**, **Event Kampus**, dan **Status Dokumen MoU**.
  - **4 Metrik Ringkasan Interaksi**: Total Sekolah Mitra, Peluang MoU Kunci, Mitra Unggulan (Champion), dan Evaluasi Sosialisasi.
  - **Personalisasi Sesuai Hak Akses & Peran User (Role-Based AI Recommendations)**:
    - **Kepala Kampus**: AI secara dinamis membatasi evaluasi **hanya pada sekolah-sekolah di bawah naungan kampusnya masing-masing** (`campus_id`). Ditandai badge khusus *"Khusus Kampus: [Nama Kampus]"*, dan tombol *"Kelola Sixty School"* mengarahkan langsung ke filter kampus tersebut.
    - **Super Admin / Admin Sistem / DMER**: AI menganalisis data secara agregat global di **seluruh kampus** (`is_super_admin`), ditandai badge *"Semua Kampus (Super Admin)"*.
  - **Rekomendasi Aksi Cerdas**:
    - *Peluang MoU Kunci*: Sekolah dengan PMB tinggi namun belum ada MoU &rarr; rekomendasi segera ajukan draf MoU Beasiswa BJS ke Kepala Sekolah.
    - *Mitra Unggulan*: PMB tinggi dan MoU aktif &rarr; rekomendasi apresiasi Guru BK dan sponsorship kegiatan.
    - *Evaluasi Sosialisasi*: Kunjungan sudah sering dilakukan tapi PMB masih minim &rarr; rekomendasi evaluasi materi presentasi dan hadirkan testimoni alumni.
    - *Aktivasi MoU*: MoU aktif tapi belum ada agenda kunjungan tahun ini &rarr; rekomendasi jadwalkan roadshow/workshop.
  - Skor Interaksi (0 - 100) dan tombol aksi cepat (*"Kelola Sekolah &rarr;"*).

### 6. Upgrade Form Input: "Data Sekolah Penerima Beasiswa" (BJS) & Edit Dedicated Page
- Form input Sixty School ditingkatkan sesuai layout spesifik form beasiswa:
  - **Baris 1**: `NPSN`, `Nama Sekolah`, `Kota`, `Jenis Skl` (SMA/SMK/MA/Lainnya), `Status Skl` (Negeri/Swasta).
  - **Baris 2**: `Nama Kepala Sekolah`, `Telp Sekolah`, `Email Sekolah`.
  - **Baris 3**: `Nama Kontak (PIC)`, `Telp Kontak`, `Email Kontak`.
  - **Baris 4**: `Status` (Aktif/Nonaktif), `Tahun PMB`, `Kampus Pelaksana`, `Perolehan Siswa PMB`.
  - **Tombol Form**: Tombol hijau `+ Kembali` dan tombol simpan teal **"Simpan Data Sekolah"**.
- **Halaman Khusus Edit Sekolah (Dedicated Edit Page)**: Tombol **"Kelola"** pada tabel mengarahkan pengguna langsung ke halaman khusus edit (`/one-campus/schools/{id}/edit`).

### 7. Media Upload Dokumen MOU (PDF) & Fitur Upload Otomatis ke Google Drive Admin
- **Upload File MOU (.pdf)**: Upload dokumen resmi MOU format PDF (maksimal 15 MB) tersimpan secara aman di direktori lokal server `writable/uploads/mou_documents/`.
- **Upload Otomatis Langsung ke Google Drive Admin (Google Apps Script Webhook Engine)**:
  - Layanan terintegrasi `GoogleDriveUploadService.php` yang mengeksekusi upload file PDF langsung ke folder Google Drive milik Admin.
  - Menggunakan Webhook Google Apps Script (tanpa biaya GCP, tanpa library composer berat, berjalan di akun Google Admin).
  - Ketika opsi *"Otomatis upload ke Google Drive saat form disimpan"* dicentang, file PDF yang diunggah seketika dikirim ke folder Drive target dan URL berkas Google Drive otomatis tersimpan ke field `mou_drive_link`.
- **Tombol Sinkronisasi Cepat ("Kirim ke Drive")**:
  - Untuk data sekolah yang sudah memiliki file MOU lokal namun belum tersinkronisasi ke Google Drive, tersedia tombol **"☁ Kirim ke Drive"** pada tabel rekap dan halaman edit untuk mengunggahnya secara instan.
- **Drawer Pengaturan Google Drive Admin**:
  - Email Akun Google Drive Admin MOU (`gdrive_admin_email`).
  - URL / ID Folder Google Drive MOU (`gdrive_mou_folder_url`).
  - Label Nama Folder Google Drive Admin (`gdrive_mou_folder_name`).
  - URL Webhook Google Apps Script (`gdrive_webhook_url`).
  - Checkbox toggle aktivasi upload otomatis (`gdrive_auto_upload_enabled`).
  - Tombol **"⚡ Uji Koneksi Webhook"** untuk validasi koneksi ke Apps Script.
  - Template Script Apps Script siap pakai dengan fungsi `setupOtorisasi()` untuk mencegah error izin DriveApp.
- **Endpoint Streaming PDF**: Route `GET /one-campus/schools/{id}/mou` menampilkan PDF secara inline langsung di browser.
- Tabel rekap menampilkan indikator status dokumen: **"Lihat PDF MOU"** (merah), **"Buka di Drive ↗"** (biru), **"Kirim ke Drive"** (teal), atau **"Belum ada MOU"**.

### 4. Fasilitas Impersonate untuk Super Admin
- Super Admin / Admin Sistem memiliki kemampuan untuk impersonate (login sementara) ke seluruh akun user untuk memeriksa kegiatan, event, dan hak akses user tersebut.
- Dilengkapi banner persistent di bagian atas: info nama akun yang sedang diimpersonate dan tombol **"Kembali ke Akun Super Admin"**.

### 5. Rekomendasi Chat AI yang Natural & Auto-Kampus
- Rekomendasi pesan chat follow up siswa PMB dioptimasi agar lebih natural tanpa icon/emoji berlebihan dan tanpa formatting bold kaku.
- Data kampus pelaksana otomatis terambil dari data event ke form brief AI tanpa status "Belum ditentukan".

### 6. Optimalisasi Tema Tampilan (Dark & Light Mode)
- Seluruh teks, panel, tabel data, modal edit, dan kontrol form memiliki kontras tinggi yang konsisten agar mudah terbaca pada template Dark Mode maupun Light Mode.

### 7. Klasterisasi Masa PMB pada CRM Workspace & Leads (September 2025 - September 2026)
- **Klaster Default Masa PMB**: Data prospek CRM secara default dikelompokkan dan difilter pada masa PMB **`September 2025 - September 2026`**, dengan tombol cepat untuk beralih ke **Semua Klaster PMB** atau klaster periode tertentu.
- **Pembuatan Klaster Baru Khusus Super Admin & Fitur Set Klaster Default**:
  - Tombol **"+ Buat Klasterisasi PMB Baru (Superadmin)"** pada toolbar CRM Workspace membuka modal untuk membuat klaster periode baru.
  - Tersedia opsi checkbox **"Langsung jadikan sebagai klaster default aktif"** saat membuat klaster baru.
  - Tersedia tombol cepat **"⭐ Jadikan Default"** pada bilah klaster saat Super Admin membuka klaster non-default.
  - Tersedia form pemilih langsung **"Ganti Klaster Default Aktif"** di dalam modal dialog Super Admin.
  - Pengaturan klaster default tersimpan secara persisten ke database `system_settings` (`crm_default_pmb_period` dan `one_campus_default_pmb_period`) sehingga setiap user yang mengakses workspace otomatis diarahkan ke klaster default tersebut.
- **Skema Database & Migrasi**:
  - Kolom `pmb_period VARCHAR(80) NOT NULL DEFAULT 'September 2025 - September 2026'` ditambahkan pada tabel `crm_leads` dan `crm_upload_batches`.
  - Index performa `idx_crm_leads_pmb` ditambahkan pada `crm_leads(pmb_period)`.
  - Seluruh data prospek lama telah di-backfill ke `'September 2025 - September 2026'`.
- **Integrasi Upload & Export Excel CRM**:
  - Form upload file prospek Excel/CSV kini dilengkapi dropdown pemilihan klaster masa PMB.
  - Export Excel CRM (`/crm/export-excel`) menyertakan kolom `Masa PMB`.
  - Filter pencarian toolbar CRM Workspace dilengkapi filter dropdown `Klaster Masa PMB`.
  - Paritas 100% konsisten pada CodeIgniter 4 dan Laravel.

---

## Pembaruan Skema Database (MySQL)

1. **Tabel `one_campus_school_reports`**:
   - `npsn` (VARCHAR 30)
   - `city` (VARCHAR 120)
   - `school_type` (VARCHAR 40) - SMA/SMK/MA/Lainnya
   - `school_status` (VARCHAR 40) - Negeri/Swasta
   - `headmaster_name` (VARCHAR 160)
   - `school_phone` (VARCHAR 40)
   - `school_email` (VARCHAR 160)
   - `contact_name` (VARCHAR 160)
   - `contact_phone` (VARCHAR 40)
   - `contact_email` (VARCHAR 160)
   - `status` (VARCHAR 40) - Aktif/Nonaktif
   - `pmb_period` (VARCHAR 80) - Contoh: "September 2026 - September 2027"
   - `mou_file` (VARCHAR 255) - Path dokumen PDF MOU
   - `mou_drive_link` (VARCHAR 255) - URL Google Drive
   - `mou_uploaded_at` (DATETIME)

2. **Tabel `system_settings`**:
   - `setting_key` (VARCHAR 100, UNIQUE)
   - `setting_value` (TEXT)
   - `description` (VARCHAR 255)
   - Menyimpan `gdrive_admin_email`, `gdrive_mou_folder_url`, `gdrive_mou_folder_name`, `gdrive_webhook_url`, `gdrive_auto_upload_enabled`, dan `one_campus_custom_pmb_periods`.

3. **Tabel `one_campus_pmb_students`**:
   - `id` (INT UNSIGNED, AUTO_INCREMENT PRIMARY KEY)
   - `school_report_id` (INT UNSIGNED, FK to `one_campus_school_reports.id`)
   - `school_name` (VARCHAR 180)
   - `npsn` (VARCHAR 30)
   - `pmb_period` (VARCHAR 80)
   - `student_name` (VARCHAR 160)
   - `registration_no` (VARCHAR 60)
   - `student_phone` (VARCHAR 40)
   - `student_email` (VARCHAR 160)
   - `study_program` (VARCHAR 120)
   - `campus_id` (INT UNSIGNED)
   - `status` (VARCHAR 50)
   - `batch_name` (VARCHAR 120)
   - `created_by` (INT UNSIGNED)
   - `created_at`, `updated_at` (DATETIME)

---

## File Penting yang Ditambahkan / Diperbarui

### CodeIgniter 4:
- `app/Config/Routes.php`: Route MOU download, edit school, Google Drive settings, test webhook, sync drive, PMB clusters, PMB template, PMB Excel upload, school students JSON, & delete student.
- `app/Controllers/EventSuite.php`: Logika klasterisasi PMB, sorting PMB banyak ke sedikit, upload Excel PMB & auto rekap pmb_count, student modal JSON, rekomendasi AI interaksi sekolah di dashboard, upload langsung Google Drive.
- `app/Libraries/GoogleDriveUploadService.php`: Service cURL Google Apps Script webhook multipart uploader.
- `app/Models/OneCampusSchoolReportModel.php`: Field allowed baru Sixty School.
- `app/Models/OneCampusPmbStudentModel.php`: Model data mahasiswa baru hasil import Excel PMB.
- `app/Models/SystemSettingModel.php`: Model setting konfigurasi Google Drive admin & custom PMB periods.
- `app/Database/Migrations/2026-11-04-000001_CreateOneCampusPmbStudents.php`: Migrasi skema tabel mahasiswa baru.
- `app/Views/pages/one_campus.php`: UI klaster PMB, tombol buat klaster Superadmin, toolbar upload PMB & template, kontrol sorting PMB banyak ke sedikit, modal rincian mahasiswa baru.
- `app/Views/pages/one_campus_school_edit.php`: Halaman khusus kelola / edit data sekolah, MOU, dan tabel lengkap mahasiswa baru terdaftar.
- `app/Views/pages/dashboard.php`: Widget Rekomendasi Cerdas AI Analisis Interaksi & Konversi Sekolah (ditempatkan di bagian paling bawah dashboard dengan dukungan penuh Dark Mode).
- `app/Views/pages/one_campus.php`: UI klaster PMB, tombol buat klaster Superadmin, toolbar upload PMB & template, kontrol sorting PMB banyak ke sedikit, modal rincian mahasiswa baru, tombol & badge verifikasi BJS.
- `app/Views/pages/one_campus_school_edit.php`: Halaman khusus kelola / edit data sekolah, MOU, verifikasi BJS, dan tabel lengkap mahasiswa baru terdaftar.
- `app/Controllers/CrmController.php`: Logika klasterisasi masa PMB CRM (default 'September 2025 - September 2026'), pembuatan klaster baru khusus Superadmin, filter PMB toolbar & pills, tagging batch upload, dan export Excel Masa PMB.
- `app/Models/CrmLeadModel.php`: Field allowed `pmb_period`.
- `app/Models/CrmUploadBatchModel.php`: Field allowed `pmb_period`.
- `app/Database/Migrations/2026-11-04-000004_AddPmbPeriodToCrmLeads.php`: Migrasi kolom `pmb_period` & index `idx_crm_leads_pmb` pada `crm_leads` dan `crm_upload_batches`.
- `app/Views/pages/crm_dashboard.php`: UI bilah klaster masa PMB CRM, modal Superadmin tambah klaster baru, filter klaster toolbar, badge baris siswa, dan select klaster pada form upload Excel.

### Laravel (Parity):
- `laravel/routes/web.php`: Route MOU download, edit school, Google Drive settings, test webhook, sync drive, PMB clusters, PMB template, PMB Excel upload, school students JSON, delete student, verify BJS, & CRM PMB cluster creation.
- `laravel/app/Controllers/EventSuite.php`: Paritas controller lengkap dengan CodeIgniter 4.
- `laravel/app/Controllers/CrmController.php`: Paritas controller CRM lengkap dengan CodeIgniter 4.
- `laravel/app/Models/CrmLeadModel.php`: Field fillable `pmb_period`.
- `laravel/app/Models/CrmUploadBatchModel.php`: Field fillable `pmb_period`.
- `laravel/resources/views/pages/crm_dashboard.blade.php`: Paritas view CRM lengkap dengan bilah klaster masa PMB, filter toolbar, badge tabel, dialog Superadmin, dan form upload PMB.
- `laravel/app/Libraries/GoogleDriveUploadService.php`: Service cURL Google Apps Script webhook uploader.
- `laravel/app/Models/OneCampusSchoolReportModel.php`: Field allowed Sixty School (`bjs_status`, `bjs_verified_at`, `bjs_verified_by`, `bjs_notes`).
- `laravel/app/Models/OneCampusPmbStudentModel.php`: Model data mahasiswa baru hasil import Excel PMB.
- `laravel/app/Models/SystemSettingModel.php`: Model setting konfigurasi Google Drive admin & custom PMB periods.
- `laravel/resources/views/pages/one_campus.blade.php`: Paritas view Blade lengkap dengan CodeIgniter 4.
- `laravel/resources/views/pages/one_campus_school_edit.blade.php`: Halaman khusus kelola / edit data sekolah, MOU, verifikasi BJS, dan tabel mahasiswa baru Blade.
- `laravel/resources/views/pages/dashboard.blade.php`: Widget Rekomendasi Cerdas AI Analisis Interaksi & Konversi Sekolah Blade (posisi paling bawah dashboard & dukungan penuh Dark Mode).
