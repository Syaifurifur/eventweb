# EventHub / EventWeb - Aplikasi Penjadwalan & Manajemen Event Kampus

Aplikasi Enterprise Penjadwalan, Manajemen Event, Task Manager, CRM Marketing Akademik, Dokumen AI, dan Program **One Campus** (Five OJT, Sixty School, Five Spanduk) berbasis **Full Laravel 12**:
- **Aplikasi Utama**: `http://127.0.0.1:8080` (Laravel Framework 12)
- **Apache XAMPP**: `http://localhost/eventweb/public` (atau via VirtualHost)

---

## Menjalankan Aplikasi

Pastikan layanan MySQL di XAMPP telah berjalan:
```powershell
# 1. Jalankan MySQL (Port 3306)
& 'C:\xampp\mysql\bin\mysqld.exe' --defaults-file='C:\xampp\mysql\bin\my.ini' --standalone

# 2. Jalankan EventHub Laravel (Port 8080)
& 'C:\xampp\php\php.exe' artisan serve --host=127.0.0.1 --port=8080
# atau cukup jalankan shortcut script:
.\serve-laravel.ps1
```

Kredensial Default Login:
- **Admin Sistem / Super Admin**: `admin` / `password` (atau `admin@eventhub.test`)

4. **Tabel `accreditation_documents`**:
   - `id` (INT UNSIGNED, AUTO_INCREMENT PRIMARY KEY)
   - `event_id` (INT UNSIGNED, FK to `events.id`)
   - `campus_id` (INT UNSIGNED, FK to `campuses.id`)
   - `title` (VARCHAR 255)
   - `event_year` (INT)
   - `accreditation_standard` (VARCHAR 120)
   - `doc_type` (VARCHAR 50) - all, report, proposal, assignment_letter
   - `status` (VARCHAR 40) - menunggu_kelengkapan, menunggu_verifikasi, terverifikasi
   - `admin_notes` (TEXT)
   - `campus_notes` (TEXT)
   - `additional_evidence_path` (VARCHAR 255)
   - `additional_evidence_name` (VARCHAR 255)
   - `external_evidence_link` (VARCHAR 255)
   - `designated_by`, `completed_by`, `verified_by` (INT UNSIGNED)
   - `designated_at`, `completed_at`, `verified_at` (DATETIME)
   - `created_at`, `updated_at` (DATETIME)

---

## Rangkuman Update Fitur Terbaru (November 2026)

### 1. One Campus Sixty School - Klasterisasi Tahun PMB, Waktu PMB Berjalan & Klaster Baru Khusus Superadmin
- **Klaster Berjalan & Sebelumnya**: Secara default menampilkan periode PMB yang sedang berjalan (dinamis sesuai pengaturan default aktif, bawaan: **`September 2026 - September 2027`**) dan periode-periode sebelumnya (**`September 2025 - September 2026`**, **`September 2024 - September 2025`**, dst.).
- **Fitur Jadikan Default untuk Waktu PMB Berjalan**:
  - Super Admin / Admin Sistem dapat menetapkan klaster mana pun sebagai **Waktu PMB Berjalan (Default)** via tombol cepat **`? Jadikan Default (Waktu Berjalan)`** di bilah klaster Sixty School.
  - Klaster yang ditetapkan sebagai default otomatis mendapatkan label dinamis **`(Berjalan)`** pada seluruh pill filter dan dropdown.
  - Dialog pop-up Superadmin dilengkapi opsi checkbox **`Langsung jadikan sebagai Klaster Default (Waktu PMB Berjalan)`** saat menambah klaster baru, serta menu pemilih **`? Tetapkan Klaster Default / Waktu PMB Berjalan`**.
  - Nilai tersimpan secara persisten pada tabel `system_settings` (`one_campus_default_pmb_period`).
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
  - Kolom **Perolehan PMB** pada tabel rekap menampilkan badge interaktif (*contoh: `15 Mhs ??`*).
  - Mengklik badge langsung membuka modal rincian mahasiswa baru via AJAX (`/one-campus/schools/{id}/students`) lengkap dengan kolom **Kampus / Kode** (*contoh: `[A1] Fatmawati (02)`*).
- **Tabel Rincian di Halaman Edit Sekolah**:
  - Pada halaman khusus edit sekolah (`/one-campus/schools/{id}/edit`), tabel rincian mahasiswa baru menampilkan kolom **Kampus / Kode** berlabel badge warna, nomor pendaftaran, prodi, dan opsi hapus siswa.

### 4. Fasilitas Sortir Data PMB (Banyak ke Sedikit)
- **Kontrol Sortir Cepat**:
  - Tombol aksi **"? PMB Terbanyak (Banyak ke Sedikit)"**, **"? PMB Tersedikit"**, dan **"Nama Sekolah (A-Z)"**.
  - Header kolom **"Perolehan PMB"** dapat diklik langsung untuk membalikkan urutan (toggle DESC/ASC) disertai indikator panah (?/?).
  - Ketika diurutkan dari terbanyak ke tersedikit, tabel menampilkan badge medali peringkat (?? Juara 1, ?? Juara 2, ?? Juara 3, dan #4, #5, dst.).

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
  - Untuk data sekolah yang sudah memiliki file MOU lokal namun belum tersinkronisasi ke Google Drive, tersedia tombol **"? Kirim ke Drive"** pada tabel rekap dan halaman edit untuk mengunggahnya secara instan.
- **Drawer Pengaturan Google Drive Admin**:
  - Email Akun Google Drive Admin MOU (`gdrive_admin_email`).
  - URL / ID Folder Google Drive MOU (`gdrive_mou_folder_url`).
  - Label Nama Folder Google Drive Admin (`gdrive_mou_folder_name`).
  - URL Webhook Google Apps Script (`gdrive_webhook_url`).
  - Checkbox toggle aktivasi upload otomatis (`gdrive_auto_upload_enabled`).
  - Tombol **"? Uji Koneksi Webhook"** untuk validasi koneksi ke Apps Script.
  - Template Script Apps Script siap pakai dengan fungsi `setupOtorisasi()` untuk mencegah error izin DriveApp.
- **Endpoint Streaming PDF**: Route `GET /one-campus/schools/{id}/mou` menampilkan PDF secara inline langsung di browser.
- Tabel rekap menampilkan indikator status dokumen: **"Lihat PDF MOU"** (merah), **"Buka di Drive ?"** (biru), **"Kirim ke Drive"** (teal), atau **"Belum ada MOU"**.

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
  - Tersedia tombol cepat **"? Jadikan Default"** pada bilah klaster saat Super Admin membuka klaster non-default.
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

### 8. Fasilitas Dokumen Akreditasi Kampus (BAN-PT / LAM-INFOKOM)
- **Fitur Mencari dari Dokumen Event & Tombol Jadikan Data Akreditasi**:
  - **Pencarian Cerdas Dokumen Event (Live Search & Filter)**: Di halaman Dokumen Akreditasi (`/documents/accreditation`), tombol **`?? Cari Dokumen Event & Jadikan Akreditasi`** membuka modal pencarian interaktif yang dilengkapi:
    - Input live search realtime untuk menyaring dokumen event berdasarkan judul, kode event (`EVT-...`), nama kampus, atau kategori event.
    - Filter dropdown kampus dan filter ketersediaan berkas (*Semua Event*, *Hanya yang Memiliki Proposal/LPJ*, *Hanya yang Belum Akreditasi*).
    - Menampilkan status ketersediaan berkas asli dokumen event: Proposal (`?? Proposal ?`), LPJ/Laporan (`?? LPJ ?`), dan Surat Tugas.
    - Pada setiap baris hasil pencarian dokumen event, tersedia tombol mencolok: **`? Jadikan Data Akreditasi`** yang sekali klik langsung memilih event tersebut dan membuka form penetapan akreditasi (Kriteria 1-9, jenis berkas, instruksi ke Kepala Kampus).
  - **Tombol Satu Klik di Halaman Dokumen Event (`/documents`)**:
    - Pada tabel rekap seluruh dokumen event di halaman `/documents`, setiap baris event kini dilengkapi tombol aksi **`?? Jadikan Akreditasi`** khusus Admin.
    - Ketika diklik, dialog pop-up konfirmasi langsung muncul untuk menetapkan dokumen event tersebut menjadi data akreditasi tanpa perlu berpindah halaman.
    - Jika event sudah pernah didaftarkan ke akreditasi, sistem secara otomatis menampilkan badge informatif **`?? Terdaftar Akreditasi`** yang dapat diklik langsung menuju berkas akreditasi terkait.
- **Penetapan Dokumen Event untuk Akreditasi oleh Admin**:
  - Admin (Admin Sistem, Super Admin, DMER) dapat memilih dan menetapkan dokumen event kegiatan kampus sebagai Dokumen Bukti Akreditasi via tombol **"+ Pilih Dokumen Event untuk Akreditasi"**.
  - Admin menentukan Kriteria Akreditasi (BAN-PT / LAM-INFOKOM Kriteria 1-9), judul berkas, jenis dokumen wajib (*all*, *report*, *proposal*, *assignment_letter*), serta instruksi khusus untuk Kepala Kampus.
- **Workflow Kelengkapan Data oleh Kepala Kampus**:
  - Kepala Kampus dibatasi secara ketat (*campus-scoped*) hanya dapat melihat dokumen akreditasi milik kampusnya masing-masing (`campus_id`).
  - Kepala Kampus melengkapi data pemenuhan: mengunggah berkas bukti fisik tambahan (PDF/Word/ZIP/Gambar hingga 25 MB), melampirkan tautan Google Drive / Cloud bukti kegiatan, serta mengisi catatan keterangan pemenuhan bukti.
  - Setelah lengkap, Kepala Kampus dapat mengubah status menjadi **"Menunggu Verifikasi"** untuk ditinjau oleh Admin.
- **Tinjauan & Verifikasi Admin**:
  - Admin dapat mereview bukti yang dikirimkan Kepala Kampus dan memutuskan: **"Setujui & Tandai Terverifikasi Lengkap"** atau **"Kembalikan ke Kepala Kampus (Perlu Revisi)"** beserta catatan evaluasi feedback.
- **Filter Bar Interaktif & Pencarian**:
  - Filter berdasarkan **Kampus Pelaksana** (seluruh kampus untuk Admin, terkunci pada kampus sendiri untuk Kepala Kampus).
  - Filter berdasarkan **Tahun Pelaksanaan** (dinamis dari seluruh tahun kegiatan event).
  - Filter berdasarkan **Status Kelengkapan** (*Menunggu Kelengkapan*, *Menunggu Verifikasi*, *Terverifikasi Lengkap*).
  - Filter berdasarkan **Kriteria / Standar Akreditasi** (Kriteria 1 - Kriteria 9).
  - Pencarian kata kunci live (`q`) pada judul event, kode event, nama kampus, dan catatan.
- **Dukungan Cetak Lembar Kendali**:
  - Tersedia tombol **"Cetak Lembar Kendali"** dengan CSS print-friendly untuk pelaporan audit instrumen akreditasi.
- **Dual-Engine Parity 100%**:
  - Berjalan identik pada CodeIgniter 4 (`/documents/accreditation`) dan Laravel (`/documents/accreditation`).

4. **Tabel `accreditation_documents`**:
   - `id` (INT UNSIGNED, AUTO_INCREMENT PRIMARY KEY)
   - `event_id` (INT UNSIGNED, FK to `events.id`)
   - `campus_id` (INT UNSIGNED, FK to `campuses.id`)
   - `title` (VARCHAR 255)
   - `event_year` (INT)
   - `accreditation_standard` (VARCHAR 120)
   - `doc_type` (VARCHAR 50) - all, report, proposal, assignment_letter
   - `status` (VARCHAR 40) - menunggu_kelengkapan, menunggu_verifikasi, terverifikasi
   - `admin_notes` (TEXT)
   - `campus_notes` (TEXT)
   - `additional_evidence_path` (VARCHAR 255)
   - `additional_evidence_name` (VARCHAR 255)
   - `external_evidence_link` (VARCHAR 255)
   - `designated_by`, `completed_by`, `verified_by` (INT UNSIGNED)
   - `designated_at`, `completed_at`, `verified_at` (DATETIME)
   - `created_at`, `updated_at` (DATETIME)

---

### 8. Fasilitas Dokumen Akreditasi Kampus (BAN-PT / LAM-INFOKOM)
- **Fitur Mencari dari Dokumen Event & Tombol Jadikan Data Akreditasi**:
  - **Pencarian Cerdas Dokumen Event (Live Search & Filter)**: Di halaman Dokumen Akreditasi (`/documents/accreditation`), tombol **`?? Cari Dokumen Event & Jadikan Akreditasi`** membuka modal pencarian interaktif yang dilengkapi:
    - Input live search realtime untuk menyaring dokumen event berdasarkan judul, kode event (`EVT-...`), nama kampus, atau kategori event.
    - Filter dropdown kampus dan filter ketersediaan berkas (*Semua Event*, *Hanya yang Memiliki Proposal/LPJ*, *Hanya yang Belum Akreditasi*).
    - Menampilkan status ketersediaan berkas asli dokumen event: Proposal (`?? Proposal ?`), LPJ/Laporan (`?? LPJ ?`), dan Surat Tugas.
    - Pada setiap baris hasil pencarian dokumen event, tersedia tombol mencolok: **`? Jadikan Data Akreditasi`** yang sekali klik langsung memilih event tersebut dan membuka form penetapan akreditasi (Kriteria 1-9, jenis berkas, instruksi ke Kepala Kampus).
  - **Tombol Satu Klik di Halaman Dokumen Event (`/documents`)**:
    - Pada tabel rekap seluruh dokumen event di halaman `/documents`, setiap baris event kini dilengkapi tombol aksi **`?? Jadikan Akreditasi`** khusus Admin.
    - Ketika diklik, dialog pop-up konfirmasi langsung muncul untuk menetapkan dokumen event tersebut menjadi data akreditasi tanpa perlu berpindah halaman.
    - Jika event sudah pernah didaftarkan ke akreditasi, sistem secara otomatis menampilkan badge informatif **`?? Terdaftar Akreditasi`** yang dapat diklik langsung menuju berkas akreditasi terkait.
- **Penetapan Dokumen Event untuk Akreditasi oleh Admin**:
  - Admin (Admin Sistem, Super Admin, DMER) dapat memilih dan menetapkan dokumen event kegiatan kampus sebagai Dokumen Bukti Akreditasi via tombol **"+ Pilih Dokumen Event untuk Akreditasi"**.
  - Admin menentukan Kriteria Akreditasi (BAN-PT / LAM-INFOKOM Kriteria 1-9), judul berkas, jenis dokumen wajib (*all*, *report*, *proposal*, *assignment_letter*), serta instruksi khusus untuk Kepala Kampus.
- **Workflow Kelengkapan Data oleh Kepala Kampus**:
  - Kepala Kampus dibatasi secara ketat (*campus-scoped*) hanya dapat melihat dokumen akreditasi milik kampusnya masing-masing (`campus_id`).
  - Kepala Kampus melengkapi data pemenuhan: mengunggah berkas bukti fisik tambahan (PDF/Word/ZIP/Gambar hingga 25 MB), melampirkan tautan Google Drive / Cloud bukti kegiatan, serta mengisi catatan keterangan pemenuhan bukti.
  - Setelah lengkap, Kepala Kampus dapat mengubah status menjadi **"Menunggu Verifikasi"** untuk ditinjau oleh Admin.
- **Tinjauan & Verifikasi Admin**:
  - Admin dapat mereview bukti yang dikirimkan Kepala Kampus dan memutuskan: **"Setujui & Tandai Terverifikasi Lengkap"** atau **"Kembalikan ke Kepala Kampus (Perlu Revisi)"** beserta catatan evaluasi feedback.
- **Filter Bar Interaktif & Pencarian**:
  - Filter berdasarkan **Kampus Pelaksana** (seluruh kampus untuk Admin, terkunci pada kampus sendiri untuk Kepala Kampus).
  - Filter berdasarkan **Tahun Pelaksanaan** (dinamis dari seluruh tahun kegiatan event).
  - Filter berdasarkan **Status Kelengkapan** (*Menunggu Kelengkapan*, *Menunggu Verifikasi*, *Terverifikasi Lengkap*).
  - Filter berdasarkan **Kriteria / Standar Akreditasi** (Kriteria 1 - Kriteria 9).
  - Pencarian kata kunci live (`q`) pada judul event, kode event, nama kampus, dan catatan.
- **Dukungan Cetak Lembar Kendali**:
  - Tersedia tombol **"Cetak Lembar Kendali"** dengan CSS print-friendly untuk pelaporan audit instrumen akreditasi.
- **Dual-Engine Parity 100%**:
  - Berjalan identik pada CodeIgniter 4 (`/documents/accreditation`) dan Laravel (`/documents/accreditation`).

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

4. **Tabel `accreditation_documents`**:
   - `id` (INT UNSIGNED, AUTO_INCREMENT PRIMARY KEY)
   - `event_id` (INT UNSIGNED, FK to `events.id`)
   - `campus_id` (INT UNSIGNED, FK to `campuses.id`)
   - `title` (VARCHAR 255)
   - `event_year` (INT)
   - `accreditation_standard` (VARCHAR 120)
   - `doc_type` (VARCHAR 50) - all, report, proposal, assignment_letter
   - `status` (VARCHAR 40) - menunggu_kelengkapan, menunggu_verifikasi, terverifikasi
   - `admin_notes` (TEXT)
   - `campus_notes` (TEXT)
   - `additional_evidence_path` (VARCHAR 255)
   - `additional_evidence_name` (VARCHAR 255)
   - `external_evidence_link` (VARCHAR 255)
   - `designated_by`, `completed_by`, `verified_by` (INT UNSIGNED)
   - `designated_at`, `completed_at`, `verified_at` (DATETIME)
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
- `app/Database/Migrations/2026-11-04-000006_CreateNationalSchools.php`: Migrasi tabel database `national_schools`.
- `app/Models/NationalSchoolModel.php`: Model pencarian referensi data satuan pendidikan nasional se-Indonesia.
- `app/Views/pages/one_campus.php`: Form pendaftaran Sixty School dengan autocomplete real-time & auto-fill data sekolah dari NPSN.
- `app/Views/pages/one_campus_school_edit.php`: Form edit Sixty School dengan autocomplete real-time & auto-fill data sekolah dari NPSN.
- `app/Views/pages/crm_dashboard.php`: UI bilah klaster masa PMB CRM, modal Superadmin tambah klaster baru, filter klaster toolbar, badge baris siswa, dan select klaster pada form upload Excel.

### Laravel (Parity):
- `laravel/routes/web.php`: Route MOU download, edit school, Google Drive settings, test webhook, sync drive, PMB clusters, PMB template, PMB Excel upload, school students JSON, delete student, verify BJS, & CRM PMB cluster creation.
- `laravel/app/Controllers/EventSuite.php`: Paritas controller lengkap dengan CodeIgniter 4.
- `laravel/app/Controllers/CrmController.php`: Paritas controller CRM lengkap dengan CodeIgniter 4.
- `laravel/app/Models/CrmLeadModel.php`: Field fillable `pmb_period`.
- `laravel/app/Models/CrmUploadBatchModel.php`: Field fillable `pmb_period`.
- `laravel/app/Models/NationalSchoolModel.php`: Model paritas Laravel untuk tabel `national_schools`.
- `laravel/resources/views/pages/one_campus.blade.php`: Paritas view Blade One Campus Sixty School dengan auto-fill NPSN.
- `laravel/resources/views/pages/one_campus_school_edit.blade.php`: Paritas view Blade edit data sekolah Sixty School dengan auto-fill NPSN.
- `laravel/resources/views/pages/crm_dashboard.blade.php`: Paritas view CRM lengkap dengan bilah klaster masa PMB, filter toolbar, badge tabel, dialog Superadmin, dan form upload PMB.
- `laravel/app/Libraries/GoogleDriveUploadService.php`: Service cURL Google Apps Script webhook uploader.
- `laravel/app/Models/OneCampusSchoolReportModel.php`: Field allowed Sixty School (`bjs_status`, `bjs_verified_at`, `bjs_verified_by`, `bjs_notes`).
- `laravel/app/Models/OneCampusPmbStudentModel.php`: Model data mahasiswa baru hasil import Excel PMB.
- `laravel/app/Models/SystemSettingModel.php`: Model setting konfigurasi Google Drive admin & custom PMB periods.
- `laravel/resources/views/pages/one_campus.blade.php`: Paritas view Blade lengkap dengan CodeIgniter 4.
- `laravel/resources/views/pages/one_campus_school_edit.blade.php`: Halaman khusus kelola / edit data sekolah, MOU, verifikasi BJS, dan tabel mahasiswa baru Blade.
- `laravel/resources/views/pages/dashboard.blade.php`: Widget Rekomendasi Cerdas AI Analisis Interaksi & Konversi Sekolah Blade (posisi paling bawah dashboard & dukungan penuh Dark Mode).


### 9. Dokumen Akreditasi & Penyelarasan Dokumen Utama Event
- **Fasilitas Dokumen Akreditasi (`/documents/accreditation`)**:
  - Penetapan dokumen event kampus menjadi Dokumen Akreditasi sesuai standar BAN-PT / LAM-INFOKOM (Kriteria 1 hingga Kriteria 9).
  - Filter lengkap berdasarkan Kampus, Tahun Pelaksanaan, Standar Akreditasi, dan Status Dokumen.
  - Modal Pencarian Dokumen Event bagi Admin untuk memilih dan menetapkan event sebagai data akreditasi.
  - Alur kelengkapan data oleh Kepala Kampus masing-masing (catatan pemenuhan, unggah berkas fisik tambahan, tautan folder Google Drive).
  - Alur verifikasi dan feedback oleh Admin Sistem.
- **Penyelarasan Dokumen Utama Event (100% Identik dengan `/documents`)**:
  - Kolom **Dokumen Utama Event** pada tabel Akreditasi menampilkan format, data, dan tautan yang identik dengan halaman Dokumen Event.
  - Menampilkan Proposal dan LPJ/Laporan dengan kode berkas resmi (`PRP-...` / `REP-...`), status chip badge (`dx-chip-green`, `dx-chip-amber`, dll), nama berkas fisik/Google Drive asli, waktu unggah, dan nama PIC pengunggah.
  - Tautan berkas langsung terhubung ke dokumen unggahan asli atau Google Drive publik via `events/{code}/documents/{type}/file` (bukan sekadar template preview AI).
  - Otomatis menampilkan Surat Tugas / SK Tim Pelaksana jika tersedia.
  - Dukungan visual responsif dan integrasi Dark Mode yang optimal di kedua engine (CodeIgniter 4 dan Laravel).


### 10. Database Satuan Pendidikan Nasional & Auto-Fill Rekomendasi NPSN (One Campus Sixty School)
- **Integrasi Database 42.856 Satuan Pendidikan se-Indonesia**:
  - Diimpor dari Data Induk Satuan Pendidikan Nasional Kemendikdasmen & Kemenag (Jenjang Dikmen formal: SMA, SMK, MA, SLB, dll).
  - Tersimpan di tabel `national_schools` lengkap dengan indeks performa tinggi pada kolom `npsn`, `name`, dan `city`.
- **Auto-Fill & Rekomendasi Real-Time pada Form Sixty School**:
  - Ketika memasukkan atau mengetik 8 digit **NPSN** pada form Sixty School (baik tambah sekolah maupun edit sekolah), sistem secara cerdas dan otomatis mengisi:
    1. **Nama Sekolah** (`school_name`)
    2. **Kota / Kabupaten** (`city`)
    3. **Jenis SKL** (`school_type`: otomatis memilih opsi `SMA`, `SMK`, `MA`, atau `Lainnya`)
    4. **Status SKL** (`school_status`: otomatis memilih opsi `Negeri` atau `Swasta`)
  - **Dropdown Rekomendasi Autocomplete Cerdas & Pencocokan Presisi (Prefix-Matching)**:
    * **Pencarian NPSN Presisi Tinggi**: Ketika mengetik angka pada kolom NPSN, sistem melakukan *prefix-matching* ketat (`npsn LIKE '$q%'`), bukan pencocokan acak di tengah atau akhir digit. Menghilangkan hasil yang tidak relevan (seperti NPSN yang hanya berakhiran angka pencarian).
    * **Highlighting Real-Time**: Teks angka NPSN atau nama sekolah yang cocok langsung ditandai dengan sorotan (*highlight*) warna kuning kontras di dalam dropdown.
    * **Edukasi & Panduan Input Otomatis**: Jika angka NPSN yang diketik tidak memiliki awalan valid (misalnya `123`), dropdown menampilkan panduan ramah bahwa format 8 digit NPSN di Indonesia diawali kode wilayah 10 s.d 70 (contoh: `20100104`, `202xxxxx`, `697xxxxx`).
    * Memberikan indikator kilat visual hijau dan notifikasi bahwa data sekolah telah terisi otomatis dari referensi resmi nasional.
- **100% Strict Dual-Engine Parity**: Didukung penuh pada CodeIgniter 4 dan Laravel melalui endpoint API `/one-campus/schools/lookup-npsn`, lengkap dengan dukungan parameter `$side` (`before`, `after`, `both`, `none`) pada query builder `LegacyQuery`.

### 11. Notifikasi Email Otomatis ke markom851@bsi.ac.id & Lonceng Notifikasi Sistem (One Campus Sixty School)
- **Pengiriman Email Otomatis ke `markom851@bsi.ac.id`**:
  - Setiap kali ada pengisian data sekolah baru pada modul **One Campus Sixty School**, sistem secara otomatis mengirimkan email notifikasi ke alamat tujuan: **`markom851@bsi.ac.id`**.
  - **Desain Template Email HTML Modern & Responsif**:
    * Menggunakan branding visual resmi EventHub Markom dengan skema warna biru-cyan.
    * Subjek email terstruktur: `[One Campus Sixty School] Data Sekolah Baru: {Nama Sekolah} ({Kota})`.
    * Memuat rincian lengkap: Nama Sekolah, NPSN, Kota/Kabupaten, Bentuk/Status Sekolah, Kepala Sekolah, Kontak/Email Sekolah.
    * Memuat rincian program & kampus: Kampus Pengusul, Periode PMB, Status BJS, Target Mahasiswa PMB, Nama & Kontak PIC Guru.
    * Memuat identitas penginput (Nama User & Email) serta stempel waktu pendaftaran (*timestamp* WIB).
    * Dilengkapi tombol Call to Action (CTA) interaktif untuk langsung membuka data terkait di EventHub.
    * Disertai format pesan alternatif plain text (*multipart MIME*) untuk kompatibilitas klien email.
    * Mekanisme *fail-safe* dengan try-catch & fallback PHP `mail()` sehingga proses input data sekolah tidak pernah terganggu meskipun mail server lokal tidak aktif.
- **Integrasi Lonceng Notifikasi Sistem (In-App Bell Notification)**:
  - Menyisipkan entri notifikasi baru bertipe `sixty-school` ke tabel `notifications` untuk Admin Sistem (User ID 1), seluruh akun dengan role administratif (`Admin Sistem`, `Super Admin`, `DMER`, `Admin`), dan user penginput.
  - **Badge Indikator Real-Time pada Ikon Lonceng Navbar**:
    * Navbar menampilkan lencana merah menyala (*pulse badge*) dengan angka penghitung notifikasi belum dibaca (*unread counter*).
    * Bekerja konsisten di semua halaman pada dashboard layout CodeIgniter 4 (`app/Views/layouts/dashboard.php`) dan Laravel Blade (`laravel/resources/views/components/navbar.blade.php`).
- **Penyempurnaan Halaman Inbox Notifikasi (`/notifications`)**:
  - Menampilkan badge kategori khusus `Sixty School` dengan warna cyan yang khas.
  - Tombol **"Buka ?"** pada setiap notifikasi Sixty School langsung mengarahkan user ke halaman manajemen Sixty School (`/one-campus/schools`).
  - Dilengkapi penanda status visual (`Baru`, `Belum dibaca`, tanggal & waktu pendaftaran).
  - Dilengkapi tombol cepat **"Tandai semua dibaca"** yang memperbarui status baca di tabel `notifications`.
  - State kosong (*empty state*) yang informatif dan elegan ketika tidak ada notifikasi.
- **100% Dual-Engine Parity**:
  - Diimplementasikan secara identik pada CodeIgniter 4 dan Laravel, termasuk dukungan mailer `LegacyMailer` yang mendukung HTML dan `setAltMessage`.

### 12. Tombol Lihat Data Sekolah & Fitur Salin Tiap Field (One Campus Sixty School)
- **Tombol "Lihat Data" Interaktif**:
  - Tersedia tombol cepat **"Lihat Data"** (dengan ikon mata `solar:eye-outline`) pada kolom aksi tabel Sixty School (`/one-campus/schools`) dan bilah atas halaman edit sekolah (`/one-campus/schools/{id}/edit`).
  - Membuka dialog modal responsif **"Detail Data Sekolah"** tanpa reload (*0-latency instant popup*) dengan tema visual modern dan dukungan penuh Dark Mode.
- **Rincian Data Lengkap Terstruktur**:
  - **Identitas Sekolah**: Nama Sekolah, NPSN, Kota/Kabupaten, Jenis Satuan Pendidikan (SKL: SMA/SMK/MA/Lainnya), Status Sekolah (Negeri/Swasta), Nama Kepala Sekolah, No. Telepon Sekolah, Email Resmi Sekolah.
  - **Kontak PIC & Guru**: Nama Guru/PIC, Jabatan/Posisi, No. HP/WhatsApp PIC (dilengkapi tombol jalan pintas WhatsApp chat), Email Kontak PIC.
  - **Program PMB & Beasiswa**: Kampus Pengusul (dengan kode kampus & kode UBSI), Periode PMB (Klaster), Status Kerjasama, Status Beasiswa Juara Sekolah (BJS), Capaian Mahasiswa PMB, Tautan/Berkas Dokumen MoU (Google Drive & PDF lokal), serta Catatan Tambahan.
  - **Metadata Sistem**: Nama penginput dan waktu pendaftaran resmi (WIB).
- **Fitur Salin Cerdas per Tiap Field (Copy to Clipboard)**:
  - Setiap field dilengkapi **icon tombol salin mandiri** (`solar:copy-outline`).
  - Ketika diklik, teks field seketika tersalin ke clipboard pengguna.
  - Memberikan umpan balik visual (*micro-interaction feedback*): icon berganti menjadi centang hijau (`solar:check-circle-bold`), label berubah menjadi `"Tersalin!"`, serta notifikasi toast halus muncul di bagian bawah dialog.
  - Mekanisme salin menggunakan `navigator.clipboard` modern dengan fail-safe fallback elemen `textarea` untuk kompatibilitas di seluruh peramban.
- **Tombol "Salin Semua Data"**:
  - Tombol master di header modal untuk menyalin rangkuman seluruh informasi sekolah dalam satu blok teks terformat rapi dan siap dibagikan ke WhatsApp/Email.
- **100% Strict Dual-Engine Parity**:
  - Diimplementasikan secara identik pada CodeIgniter 4 (`app/Views/pages/one_campus.php`, `app/Views/pages/one_campus_school_edit.php`) dan Laravel Blade (`laravel/resources/views/pages/one_campus.blade.php`, `laravel/resources/views/pages/one_campus_school_edit.blade.php`).

### 13. Fitur Pusat Unduhan & Repositori Dokumen (Upload oleh Admin, Download oleh Seluruh User)
- **Akses Terpusat & Terbuka untuk Seluruh User**:
  - Menu baru **"Pusat Unduhan & Dokumen"** (URL `/shared-documents` atau alias `/downloads`) disematkan pada sidebar kelompok menu *Dokumen & Laporan*.
  - Terbuka untuk **seluruh pengguna** dari berbagai role (`Admin Sistem`, `Kepala Kampus`, `Marketing Akademik`, `MARKOM`, `Event Creator`, `Pimpinan`, `Staff Operasional`, `Tim Design`).
  - Menyediakan direktori terpusat untuk panduan operasional (SOP), format proposal baku, format LPJ kegiatan, SK Rektorat, brosur/flyer promosi, dan formulir resmi.
- **Wewenang Pengelolaan Berkas oleh Admin**:
  - Role berwewenang (`Admin Sistem`, `Super Admin`, `DMER`, `Admin`) dilengkapi tombol **"+ Upload Dokumen Baru"** untuk mengunggah berkas.
  - Mendukung aneka format berkas: PDF (`.pdf`), Word (`.docx`, `.doc`), Excel (`.xlsx`, `.xls`, `.csv`), PowerPoint (`.pptx`), Gambar (`.png`, `.jpg`, `.webp`), dan Arsip (`.zip`, `.rar`, `.7z`) dengan batas berkas hingga 50 MB.
  - Admin dapat memperbarui rincian dokumen (*Edit Informasi*) atau mengganti file yang telah diunggah.
  - Admin dapat mengarsipkan/mengaktifkan status berkas serta menghapus dokumen secara permanen dari basis data dan media penyimpanan server (`writable/uploads/shared_documents/`).
- **Fitur Download & Pratinjau Interaktif (User Experience)**:
  - Tombol **"Unduh Berkas"** sekali klik langsung memulai pengunduhan file dengan nama asli dan tipe MIME yang tepat.
  - Penghitung unduhan (*real-time download counter*) otomatis mencatat total berapa kali sebuah dokumen telah diunduh oleh civitas kampus.
  - Tombol **"Preview"** untuk berkas PDF dan gambar langsung membuka pratinjau inline peramban di tab baru.
  - Tombol **"Salin Tautan Unduh"** untuk kemudahan membagikan direct download link melalui WhatsApp atau email disertai toast notifikasi otomatis.
- **Pencarian, Filter, & Statistik Lengkap**:
  - Filter kategori fleksibel: *Panduan & SOP*, *Template & Format*, *SK & Regulasi*, *Brosur & Materi Promosi*, *Dokumen Akreditasi*, *Formulir & Administrasi*, dan *Lainnya*.
  - Filter jenis ekstensi berkas dan sortir berdasarkan dokumen terbaru, paling populer (terbanyak diunduh), nama A-Z/Z-A, dan ukuran berkas.
  - Panel statistik atas menampilkan: *Total Dokumen Aktif*, *Total Diunduh (All-time)*, *Jumlah Kategori*, dan *Total Kapasitas Berkas*.
- **100% Strict Dual-Engine Parity**:
  - **CodeIgniter 4**: `app/Models/SharedDocumentModel.php`, `app/Controllers/EventSuite.php`, `app/Views/pages/shared_documents.php`, `app/Config/Routes.php`, `app/Views/layouts/dashboard.php`.
  - **Laravel**: `laravel/app/Models/SharedDocumentModel.php`, `laravel/app/Controllers/EventSuite.php`, `laravel/resources/views/pages/shared_documents.blade.php`, `laravel/routes/web.php`, `laravel/resources/views/components/sidebar.blade.php`.


### 14. Migrasi Penuh Full Laravel (100% Single-Engine Architecture)
- **Konsolidasi Penuh Codebase**:
  - Menghentikan pemeliharaan ganda dual-engine (CodeIgniter 4 + Laravel) dan mentransisikan 100% arsitektur proyek ke **Laravel 12**.
  - Seluruh struktur inti Laravel kini berada di direktori root (`app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`).
  - Berkas unggahan dipindahkan dari `writable/uploads/` ke `storage/uploads/` dengan symlink publik `public/storage`.
  - Riwayat arsitektur CI4 tersimpan aman di branch `backup-ci4-dual-engine`.

### 15. Laporan CRM Real-Time: Progres Kampus, Detail Event Ber-Leads, & Grafik Tanggapan Respon
- **Pemantauan Progres Hasil CRM 35 Kampus Secara Detail**:
  - Mengintegrasikan data prospek langsung dari database aktif MySQL (`crm_leads`, `events`, `campuses`, `crm_lead_logs`, `crm_users`) yang digabungkan secara dinamis dengan benchmark 20 kampus regional (total 35 kampus, 8.794+ prospek, 46 event aktif).
  - Metrik operasional lengkap per kampus: Total Leads, Belum Di-assign (SLA Alert), Fresh di Agent, Status Respon (Closing, Minat, Ragu-ragu, Menolak, Tidak Merespon), serta persentase konversi Closing PMB.
- **Rincian Event Apa Saja yang Telah Ditambahkan Leads**:
  - Tabel terstruktur interaktif daftar event ber-leads di setiap kampus pelaksana.
  - Memuat: Kode Event (`EVT-...`), Nama Kegiatan, Kategori Event, Tanggal & Lokasi Pelaksanaan, Jumlah Leads yang Dikumpulkan, dan Distribusi Status Follow-up Respon Calon Mahasiswa (Closing, Minat, Ragu-ragu, Menolak, Tidak Merespon, Fresh).
  - Dilengkapi perhitungan otomatis tingkat konversi event (%) serta rekomendasi tindak lanjut event berbasis hasil interaksi.
- **Grafik Tanggapan Hasil Respon Per Kampus (Real-Time Vector SVG)**:
  - Menggunakan engine grafik bawaan anti-black canvas (`public/assets/js/lib/svg-chart-engine.js`) yang kompatibel sempurna di semua browser modern dan desktop (termasuk Firefox & Safari).
  - **Chart 1 (Donut Chart)**: Visualisasi proporsi tanggapan calon mahasiswa di kampus terpilih (Closing PMB, Minat/Tertarik, Ragu-ragu/Pertimbangan, Menolak, Tidak Merespon, Fresh Belum Dihubungi).
  - **Chart 2 (Horizontal Stacked Bar Chart)**: Komparasi performa dan respon calon mahasiswa antar-event yang telah dilaksanakan di kampus tersebut.
  - **Chart 3 (Macro Comparative Bar Chart)**: Komparasi volume dan persentase respon lintas seluruh kampus di Indonesia untuk level Admin Pusat.
- **Grafik & Analisis Alasan Tidak Berminat / Menolak Bergabung UBSI (Real-Time SVG)**:
  - **Grafik Penolakan di View Kampus Pelaksana**: Menampilkan Donut Chart interaktif dan rincian faktor penolakan siswa di kampus terkait (*Sudah Diterima di PTN/Kampus Lain*, *Kendala Biaya Kuliah & Menunggu Beasiswa*, *Memilih Langsung Bekerja*, *Jurusan Diminati Belum Tersedia*, *Jarak Kampus Terlalu Jauh*, *Tidak Mendapat Izin Orang Tua*), dilengkapi progress bar persentase dan kotak rekomendasi taktis tindak lanjut penolakan (skema cicilan kuliah bulanan, beasiswa KIP/prestasi, kelas malam/karyawan Blended Learning).
  - **Grafik Penolakan Makro Nasional (View Admin Pusat)**: Agregasi nasional seluruh data penolakan dari 35 kampus se-Indonesia untuk kompas strategis manajemen dalam merancang kebijakan PMB dan promosi beasiswa.
  - Penanganan status adaptif: menampilkan ilustrasi hijau *Nihil Penolakan* jika kampus belum memiliki catatan penolakan.
- **Sinkronisasi & Pembaruan Data Real-Time**:
  - Indikator status `🟢 Real-Time Sync Aktif` dengan penunjuk timestamp WIB presisi (`H:i:s WIB`).
  - Tombol interaktif **"🔄 Refresh Real-Time"** untuk memperbarui data seketika melalui endpoint AJAX `/laporan-crm/realtime-data` tanpa memuat ulang (reload) halaman browser.
  - Fitur latar belakang auto-polling berkala setiap 30 detik untuk menjamin akurasi data saat tim CRM memperbarui progres prospek di CRM Workspace.
- **Live Activity Feed Log Follow-Up Terkini**:
  - Panel log interaksi real-time yang menampilkan rekaman follow-up terbaru oleh tim CRM di kampus terhadap calon mahasiswa (dari tabel `crm_lead_logs`).
- **Skema Tata Kelola Hak Akses Terisolasi**:
  - **Admin Pusat**: Memiliki kontrol penuh lintas kampus, agregasi funnel nasional, filter klaster kinerja kampus, dan ekspor lembar kerja nasional (CSV).
  - **Tim CRM Kampus**: Akses terfokus dan terisolasi khusus data kampus internal untuk menjaga privasi data (UU PDP) serta fokus eksekusi follow-up harian.


### 16. Fitur Klasterisasi & Hapus Klaster untuk Program One Campus Five OJT & Five Spanduk serta Ekspor Laporan Dinamis
- **Fungsi Klasterisasi Penuh untuk One Campus Five OJT**:
  - Menyediakan pengelompokan berbasis periode OJT (Semester Ganjil, Semester Genap, atau rentang bulan/tahun kustom).
  - Pilihan klaster default aktif berjalan (`Juli 2026 - Desember 2026 (Semester Ganjil 2026/2027)`).
  - Modal interaktif Superadmin untuk membuat klaster OJT baru (`dialog-add-ojt-cluster`), menetapkan klaster default aktif berjalan, serta **fitur menghapus klaster OJT** (`one-campus/ojt-clusters/delete`).
  - Dropdown pemilihan klaster OJT pada form input data OJT baru.
  - Filter klaster OJT pada tabel rekap dengan badge jumlah peserta per periode.
- **Fungsi Klasterisasi Penuh untuk One Campus Five Spanduk**:
  - Kolom basis data baru `banner_period` pada tabel `one_campus_banner_reports`.
  - Pengelompokan periode spanduk berdasarkan tahun atau tema kampanye promosi (`Tahun 2026 (Periode Berjalan)`, `Tahun 2025`, `Tahun 2024`, dll).
  - Modal interaktif Superadmin untuk membuat klaster Spanduk baru (`dialog-add-banner-cluster`), menetapkan klaster default aktif berjalan, serta **fitur menghapus klaster Spanduk** (`one-campus/banner-clusters/delete`).
  - Dropdown pemilihan klaster pada form input data spanduk baru.
  - Filter klaster Spanduk pada tabel rekap dengan badge jumlah titik spanduk per periode.
- **Fitur Hapus Klaster untuk Sixty School (PMB Clusters)**:
  - Tombol **"🗑️ Hapus Klaster"** disematkan pada bar klaster PMB dan modal kelola klaster PMB (`one-campus/pmb-clusters/delete`) untuk menghapus klaster PMB yang tidak lagi digunakan.
- **Unduh / Download Laporan One Campus Mengikuti Klaster Aktif Berjalan**:
  - Seluruh tombol ekspor laporan One Campus (Five OJT, Sixty School, Five Spanduk) secara otomatis mengikuti klaster yang sedang aktif berjalan atau yang sedang dipilih oleh pengguna.
  - Filter query dinamis (`?ojt_period=...`, `?banner_period=...`, `?pmb_period=...`) disematkan pada link unduh.
  - File XLSX yang diunduh menyertakan kolom identitas klaster dan nama berkas yang mencantumkan slug periode klaster yang sedang diunduh.
