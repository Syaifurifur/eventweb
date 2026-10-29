# Dokumentasi Lengkap Fitur & Arsitektur Sistem EventHub (EventWeb)

Dokumen ini berisi pemetaan lengkap seluruh fitur, alur kerja bisnis, modul fungsional, arsitektur teknis, integrasi eksternal, dan direktori rute pada platform **EventHub (EventWeb)**.

---

## 1. Ringkasan Eksekutif & Arsitektur Sistem

**EventHub** adalah platform enterprise manajemen tata kelola acara (*event management*) dan penjangkauan promosi kampus (*outreach marketing*) yang dirancang khusus untuk ekosistem multi-kampus universitas (Universitas Bina Sarana Informatika / UBSI, Universitas Nusa Mandiri / UNM, dan Cyber University / CU).

Platform ini mengintegrasikan seluruh siklus hidup kegiatan kampus—mulai dari perencanaan draft, penyusunan proposal otomatis berbasis **Artificial Intelligence (Google Gemini)**, persetujuan bertingkat pimpinan (*approval workflow*), penjadwalan & sinkronisasi dua arah **Google Calendar**, checklist kelengkapan kerja PIC (*Task Manager*), pelaporan pasca-acara & LPJ, program kemitraan sekolah & industri (**One Campus**), analitik eksekutif (**DMER**), hingga tindak lanjut data prospek calon mahasiswa baru (**CRM Leads Pipeline**).

```
                      +---------------------------------------+
                      |               EVENTHUB                |
                      +---------------------------------------+
                                          |
    +-------------------+-----------------+-------------------+-------------------+
    |                   |                 |                   |                   |
    v                   v                 v                   v                   v
[Manajemen Event]   [Task & Checklist] [Google Calendar]   [One Campus & DMER] [CRM Leads Pipeline]
  - Multi-Day Event   - Pra/Event/Pasca   - OAuth 2.0        - 30 Schools        - Funnel Tahap 1-3
  - Proposal AI Gen   - Penugasan PIC     - 2-Way Sync       - 5 Spanduk         - Auto/Manual Assign
  - Approval System   - Bukti Pekerjaan   - BSI Excel Export - 5 OJT Range       - Closing & WA Direct
  - LPJ & Dokumen     - Notifikasi Task   - Idempotent PATCH - 5 Strategi Export - Audit Log & Proof
```

### Karakteristik Arsitektur & Teknologi:
1. **Dual-Stack Runtime**:
   - **CodeIgniter 4 (PHP 8.2+)**: Berjalan di folder root (`/app`, `/public`), menggunakan controller MVC murni, filter otorisasi, dan database driver performa tinggi (`serve-local.ps1` pada port `8080`).
   - **Laravel 12 + Tailwind CSS 4**: Berjalan di subfolder `/laravel`, menyediakan implementasi modern dengan Blade template dan Tailwind CSS v4 (`serve-laravel.ps1` pada port `8081`).
   - **Shared Database & Storage**: Kedua runtime berbagi basis data MySQL yang sama (`eventweb`) dan direktori upload file yang sama (`/writable`), sehingga tidak memerlukan sinkronisasi data antar framework.
2. **Database Engine**:
   - MySQL / MariaDB dengan skema 22 tabel relasional, constraint foreign key, soft-deletes, dan versioned migrations.
3. **Integrasi Eksternal**:
   - **Google Gemini AI API (Model `gemini-2.5-flash`)**: Penyusun otomatis struktur proposal formal universitas lengkap dengan tabel Rundown dan RAB.
   - **Google Calendar REST API**: Menggunakan alur OAuth 2.0 native cURL tanpa dependensi pustaka pihak ketiga.
   - **Direct WhatsApp Messaging**: Normalisasi nomor telepon otomatis ke format internasional (`62...`) untuk integrasi `wa.me`.
   - **Native Spreadsheet Exporter**: Pembuat file XLSX/XLS murni menggunakan `ZipArchive` tanpa dependensi PhpSpreadsheet yang berat.

---

## 2. Struktur Peran & Hak Akses (Role-Based Access Control)

EventHub menerapkan kontrol akses berbasis peran (*RBAC*) yang fleksibel dengan opsi pembatasan cakupan kampus (*campus scoping*) serta pengaturan izin granular berbasis array JSON (`access_permissions`).

### Daftar Peran Sistem (Roles):

| Peran (Role) | Kategori | Cakupan Data (Scope) | Deskripsi & Wewenang |
| :--- | :--- | :--- | :--- |
| **Admin Sistem** | Sistem | Global (Semua Kampus) | Hak akses absolut (Superadmin): mengelola semua event, menyetujui/menolak event, impor/ekspor data massal, manajemen user, manajemen role, master data kampus & kategori, template dokumen, serta CRM global. |
| **Event Creator** | Pengguna | Sesuai Pembuat / Kampus | Membuat event baru, menyusun proposal (manual/AI), mengelola checklist event miliknya, menghubungkan & sinkronisasi Google Calendar, mengunggah LPJ, dan membuat tugas PIC. |
| **Pimpinan** | Pengguna | Global / Kampus | Meninjau pengajuan event (*approval/reject* dengan catatan), memantau kalender kegiatan, memeriksa laporan realisasi, dan melihat analitik performa kampus. |
| **Markom / Marketing Akademik** | Pengguna | Terisolasi per Kampus | Mengelola event-event pemasaran akademik pada kampusnya, mengelola laporan One Campus, dan mengelola leads CRM khusus kampusnya. |
| **Kepala Kampus** | Pengguna | Terisolasi per Kampus | Mengelola program kemitraan kampus: *One Campus Thirty School*, *One Campus Five Spanduk*, *One Campus Five OJT*, serta mendaftarkan dan memantau agen CRM kampusnya. |
| **DMER (Direktorat Marketing)** | Pengguna | Global (Semua Kampus) | Akses khusus pada menu **Analisis Khusus**: memantau pemenuhan target 4 event/bulan per kampus, mengevaluasi efektivitas promosi, dan mengunduh **Laporan 5 Strategi DMER**. |
| **CRM Agent** | Pengguna CRM | Terisolasi per Agen | Mengakses portal CRM (`/crm`): memproses prospek siswa yang ditugaskan (*assigned leads*), menghubungi via WhatsApp, memperbarui tahapan/label, dan mengunggah bukti follow-up. |

### Izin Akses Granular (`access_permissions`):
Sistem menyediakan token izin akses yang dapat dikonfigurasi per user atau default per role:
- `dashboard`: Mengakses beranda ringkasan statistik dan feed aktivitas.
- `events`: Melihat, membuat, mengedit, dan mengelola event serta checklist.
- `calendar`: Melihat kalender jadwal dan melakukan sinkronisasi agenda.
- `approvals`: Menyetujui atau menolak pengajuan event.
- `documents`: Melihat dokumen event, mengunggah template, dan generate proposal AI.
- `reports`: Mengakses rekapitulasi laporan post-event dan ekspor Excel.
- `analytics`: Membuka halaman statistik distribusi acara dan kinerja kampus.
- `notifications`: Membuka pusat notifikasi dan riwayat pemberitahuan.
- `one-campus`: Mengakses dan mengelola laporan kemitraan One Campus.
- `campuses`: Mengelola master data kampus.
- `users`: Mengelola akun pengguna, approval pendaftar baru, dan peran.
- `crm`: Mengakses portal CRM dan pengelolaan prospek siswa.

---

## 3. Fitur Autentikasi & Keamanan Sistem

Modul autentikasi dirancang dengan standar keamanan web modern untuk mencegah serangan brute force, pembajakan sesi, dan manipulasi data.

```
                    +---------------------------+
                    |       Form Login          |
                    +---------------------------+
                                  |
                                  v
                    +---------------------------+
                    | Throttling Rate Limiter   |  ---> [Blokir jika > percobaan gagal]
                    +---------------------------+
                                  |
                                  v
                    +---------------------------+
                    | Verifikasi Password Hash  |  ---> [password_verify BCRYPT]
                    +---------------------------+
                                  |
                                  v
                    +---------------------------+
                    | Regenerasi Session ID     |  ---> [Cegah Session Fixation]
                    +---------------------------+
                                  |
                                  v
                    +---------------------------+
                    | Muat Role & Permissions   |  ---> [Dashboard Sesuai Hak Akses]
                    +---------------------------+
```

### Rincian Fitur Autentikasi:
1. **Pencegahan Brute-Force & Throttling Login**:
   - Setiap upaya login dicatat ke dalam tabel `auth_login_attempts` (merekam alamat email, IP address, user-agent, status sukses/gagal, dan waktu).
   - Upaya login yang gagal berulang kali secara otomatis dibatasi (*rate-throttled*) untuk melindungi kredensial akun.
2. **Keamanan Sesi & Password**:
   - Enkripsi kata sandi menggunakan standar native PHP `password_hash()` dengan algoritma BCRYPT dan validasi via `password_verify()`.
   - Melakukan regenerasi ID sesi (`session()->regenerate(true)`) seketika saat proses login berhasil untuk mencegah eksploitasi *Session Fixation*.
3. **Alur Pendaftaran Mandiri & Review Akun Baru**:
   - Calon pengguna dapat mendaftar mandiri melalui `/register`.
   - Akun baru otomatis masuk ke status `Review` (belum aktif) dan dialirkan ke panel notifikasi Admin Sistem.
   - Admin Sistem dapat menyetujui pendaftar, menentukan role yang sesuai, menetapkan kampus pelaksana, lalu mengubah status akun menjadi `Aktif`.
4. **Alur Reset Password Aman**:
   - Fitur Lupa Password (`/forgot-password`) membuat token acak berbasis kriptografi yang di-hash dengan SHA-256 (`token_hash`) pada tabel `password_reset_tokens`.
   - Token memiliki masa kedaluwarsa waktu (otomatis hangus setelah 1 jam) dan hanya dapat digunakan satu kali (`used_at`).
   - Merekam IP pemohon dan user agent untuk audit keamanan.
5. **Perlindungan CSRF & Middleware**:
   - Seluruh route berjenis `POST` dilindungi oleh token CSRF.
   - Route protected dikawal oleh filter `auth` (atau `legacy.auth` di Laravel) dan filter granular `role` / `access`.
6. **Soft Deletes & Audit Integrity**:
   - Entitas akun, event, dan master data menerapkan mekanisme `deleted_at` (soft delete) sehingga data transaksi historis tetap utuh dan dapat dipulihkan (*restore*) jika diperlukan.

---

## 4. Fitur Dashboard Utama & Pusat Notifikasi

### Beranda Dashboard (`/dashboard`):
- **Kartu Ringkasan KPI Utama**:
  - Total event terdaftar.
  - Jumlah event menunggu persetujuan (*diajukan*).
  - Jumlah event disetujui (*disetujui*).
  - Jumlah event sedang berjalan / siap dilaksanakan.
  - Jumlah event selesai (*selesai*).
  - Total realisasi/estimasi anggaran seluruh event aktif.
  - Akumulasi estimasi jumlah peserta.
  - Total database prospek siswa pada CRM.
- **Badge Peringatan Tugas PIC**:
  - Indikator visual mencolok yang menampilkan jumlah tugas checklist penugasan (*PIC tasks*) baru yang belum dibaca atau belum diselesaikan oleh pengguna yang sedang login.
- **Tabel Event Terkini**:
  - Daftar ringkas event terbaru beserta kategori, kampus, tanggal pelaksanaan, indikator progress kelengkapan (%), dan badge status.
- **Feed Aktivitas Sistem**:
  - Log kronologis peristiwa penting: persetujuan event oleh pimpinan, pembaruan dokumen, atau sinkronisasi kalender.

### Pusat Notifikasi (`/notifications`):
- Notifikasi terpusat untuk berbagai jenis kejadian:
  - `approval`: Pengajuan event baru yang membutuhkan review pimpinan/admin.
  - `checklist`: Penugasan item checklist kerja baru kepada PIC bersangkutan.
  - `calendar`: Konfirmasi keberhasilan sinkronisasi jadwal ke Google Calendar.
  - `report`: Pengingat pengunggahan laporan LPJ pasca acara selesai.
- Fitur **Tandai Semua Telah Dibaca** (`POST /notifications/read-all`) untuk membersihkan antrean notifikasi secara instan.

---

## 5. Fitur Manajemen Event (Event Lifecycle)

EventHub menyediakan sistem siklus hidup kegiatan kampus secara menyeluruh, dari masa perencanaan awal hingga rekapitulasi akhir.

```
+------------+       Submit Draft       +---------------+       Review Pimpinan      +---------------+
|   DRAFT    |  -------------------->   |   DIAJUKAN    |  ----------------------->  |   DISETUJUI   |
+------------+                          +---------------+                            +---------------+
                                                |                                            |
                                                | Reject (dengan catatan)                    | Selesai & LPJ
                                                v                                            v
                                        +---------------+                            +---------------+
                                        |    DITOLAK    |                            |    SELESAI    |
                                        +---------------+                            +---------------+
```

### 1. Form Pembuatan & Pembaruan Event (`/events/new`, `/events/{code}/edit`):
- **Kode Event Unik**: Pembuatan otomatis pola kode (contoh: `EVT-2401`) atau input custom.
- **Judul & Deskripsi**: Nama kegiatan (minimal 3 karakter untuk mendukung singkatan seperti "BKOT") dan penjelasan komprehensif latar belakang acara.
- **Kategori Event Dinamis**: Terhubung ke master data kategori (*Seminar, Workshop, Lomba, Akademik, dll.*).
- **Kampus Pelaksana Searchable**: Komponen combobox pencarian cepat kampus dari master data kampus.
- **Dukungan Rentang Hari (Multi-Day Events)**:
  - Default form menampilkan satu tanggal (`event_date`).
  - Opsi checkbox: *"Event berlangsung lebih dari satu hari"*.
  - Jika dicentang, kolom `event_end_date` muncul dengan validasi tanggal akhir tidak boleh lebih awal dari tanggal awal.
  - Seluruh modul (kalender, detail, export, analitik) otomatis menampilkan label rentang tanggal (misal: `12 Mei 2026 sampai 14 Mei 2026`).
- **Waktu & Lokasi**: Jam mulai dan rincian lokasi fisik (*Auditorium, Lab Komputer*) maupun virtual (*Zoom Meeting*).
- **PIC Terikat Otomatis**: PIC penanggung jawab otomatis dikaitkan dengan profil akun pengguna yang sedang login.
- **Target Peserta & Estimasi Anggaran (RAB)**: Kuota peserta yang dibidik dan nominal kebutuhan anggaran kegiatan.
- **Otomatisasi Awal**: Saat event disimpan pertama kali, sistem otomatis membuatkan draft proposal awal, meng-clone item checklist standar dari template, dan menyiapkan slot laporan LPJ.

### 2. Daftar & Pencarian Event Server-Side (`/events`):
- **Filter Terpadu**:
  - Kotak pencarian terpadu (*pencarian multi-kolom: judul event, nama kampus, lokasi, atau nama PIC*).
  - Filter dropdown berdasarkan kategori kegiatan.
  - Filter dropdown berdasarkan status event (`draft`, `diajukan`, `disetujui`, `ditolak`, `selesai`).
- **Pagination Server-Side**: Memuat data secara bertahap (10 data/halaman) dengan tetap mempertahankan seluruh parameter filter aktif saat berpindah halaman.
- **Tombol Reset**: Mengembalikan tampilan filter ke kondisi default dalam satu klik.

### 3. Impor Rekap Event Massal via Excel/CSV (`/events/import`):
- Khusus untuk peran **Admin Sistem** untuk memasukkan rekap ratusan agenda kampus sekaligus.
- **Unduh Template Import** (`GET /events/template`): Template spreadsheet dengan kolom terstandar: `code`, `title`, `category`, `campus`, `kampus_pelaksana`, `event_date`, `event_end_date`, `location`, `pic`, `participants`, `status`, `description`.
- **Smart Campus Matching**: Pencocokan cerdas teks nama kampus (misal: data Excel bertuliskan "UBSI Bekasi" otomatis dipetakan ke record database `UBSI KAMPUS BEKASI CUT MUTIA` selama kandidat unik).
- **Validasi Anti-Duplikasi**: Jika kolom `code` diisi manual dan sudah ada di basis data, sistem menolak duplikasi baris tersebut. Jika dikosongkan, sistem membuatkan kode acak unik baru secara otomatis.
- **Auto-Provisioning**: Baris data yang berhasil diimpor otomatis dibuatkan checklist kelengkapan dan record proposal terkait.

### 4. Ekspor Rekap Event (`/events/export`):
- Mengunduh seluruh rekap agenda kegiatan ke format spreadsheet yang rapi dan terstruktur untuk pelaporan manajemen.

### 5. Pengajuan Draft Event (`POST /events/{code}/submit`):
- Mengubah status event dari `draft` menjadi `diajukan` agar masuk ke meja verifikasi pimpinan.

---

## 6. Fitur Checklist Kelengkapan & Task Manager PIC

Untuk memastikan setiap kegiatan siap dilaksanakan 100%, EventHub menyediakan instrumen checklist operasional berbasis 3 fase kegiatan yang terhubung langsung ke modul **Task Manager**.

```
+---------------------------------------------------------------------------------+
|                               CHECKLIST EVENT                                   |
+---------------------------------------------------------------------------------+
|  [Fase 1: Pra Event]     |  [Fase 2: Hari H / Event] |  [Fase 3: Pasca Event]   |
|  - Proposal Kegiatan     |  - Registrasi & Absensi   |  - Laporan LPJ           |
|  - Sewa Ruangan/Vendor   |  - Dokumentasi Acara      |  - Link Dokumentasi Foto |
|  - Publikasi & Spanduk   |  - Konsumsi Peserta       |  - Link Video Kegiatan   |
+---------------------------------------------------------------------------------+
```

### 1. Checklist pada Detail Event (`/events/{code}`):
- **Indikator Persentase Kesiapan**: Menghitung secara real-time rasio item checklist berstatus `selesai` (*done*) berbanding total item yang harus dipenuhi.
- **Grup Berdasarkan 3 Fase**:
  1. **Pra Event**: Pengajuan proposal, koordinasi narasumber, pengurusan izin ruangan, materi promosi, pencetakan sertifikat, konsumsi.
  2. **Event (Hari H)**: Presensi kehadiran, briefing panitia, perlengkapan sound/multimedia, operasional rundown.
  3. **Pasca Event**: Laporan Pertanggungjawaban (LPJ), pengunggahan foto kegiatan, pengunggahan video dokumentasi.
- **Penugasan PIC Spesifik**: Setiap butir kelengkapan dapat ditugaskan ke salah satu staf kampus dengan tenggat waktu (*deadline*) dan instruksi kerja (*notes*).
- **Tautan Bahan Kegiatan (`material_drive_link`)**: Pembuat event dapat melampirkan link materi awal (misal: link Google Drive template banner atau slide presentasi).
- **Tambah Kelengkapan Kustom**: Pembuat event atau admin dapat menambahkan butir checklist custom di luar template bawaan.

### 2. Modul Terpusat Task Manager (`/task-manager`):
- **Antarmuka Tugas PIC**:
  - Tab **Tugas Berjalan**: Menampilkan daftar tugas yang sedang aktif dikerjakan beserta tenggat waktunya.
  - Tab **Tugas Selesai**: Menampilkan arsip tugas yang telah selesai dilaporkan.
- **Form "Buat Tugas" Mandiri (`/task-manager/new`)**:
  - Memungkinkan admin atau pimpinan memberikan penugasan langsung kepada user tanpa harus masuk ke form edit event.
  - Form mencakup: pemilihan event terkait, judul tugas, nama PIC penerima tugas, fase acara, batas waktu penyelesaian (*deadline*), deskripsi instruksi kerja, dan link bahan pendukung.
  - Submit form otomatis membuat notifikasi checklist ke akun penerima tugas dan memperbarui bobot progress event.
- **Pelaporan Hasil Tugas oleh PIC (`POST /checklist-tasks/{itemId}/report`)**:
  - Staf/PIC yang ditugaskan dapat membuka tugasnya, menyematkan link hasil kerja (Google Drive / dokumen file), menuliskan catatan penyelesaian, dan menandai tugas selesai.
- **Tampilan Khusus Admin**:
  - Admin Sistem dapat memantau seluruh penugasan lintas PIC dan lintas kampus, dilengkapi kolom nama PIC pelaksana serta filter pencarian berbasis nama penanggung jawab.

---

## 7. Fitur Persetujuan & Verifikasi Event (Approvals)

Menu khusus pimpinan dan admin sistem (`/approvals`) untuk menjaga kontrol kualitas acara dan akuntabilitas anggaran kampus.

### Fitur Unggulan Approvals:
1. **Algoritma Prioritas Review**:
   - Sistem secara otomatis menempatkan event berstatus `diajukan` (*pending review*) pada urutan paling atas daftar, mencegah event yang butuh persetujuan tertimbun oleh event yang sudah disetujui atau telah selesai.
2. **Normalisasi Status**:
   - Menjamin variasi penulisan status (seperti "Diajukan" atau "diajukan") dinormalisasi secara seragam ke huruf kecil (*lowercase*) agar perhitungan counter badge akurat.
3. **Persetujuan Satu Klik (Approve)**:
   - Pimpinan/Admin menekan tombol *Approve*.
   - Sistem mencatat identitas penyetujui (`approved_by`) dan stempel waktu persetujuan (`approved_at`).
   - Memicu notifikasi konfirmasi ke pembuat event.
   - Otomatis mendaftarkan event ke antrean integrasi **Google Calendar Sync**.
4. **Penolakan Berita Acara (Reject)**:
   - Pimpinan dapat menolak event yang belum memenuhi standar dengan mencantumkan alasan penolakan secara spesifik (`rejected_reason`).
   - Pembuat event menerima notifikasi penolakan dan dapat membaca catatan kekurangan pada halaman detail event untuk direvisi dan diajukan ulang.

---

## 8. Fitur Kalender Kegiatan & Integrasi Google Calendar

EventHub memiliki sistem kalender interaktif komprehensif yang menjembatani jadwal internal kampus dengan ekosistem Google Workspace.

```
+---------------------------------------------------------------------------------+
|                                 KALENDER EVENT                                  |
+---------------------------------------------------------------------------------+
|  [ < Sebelumnya ]             [ Bulan Ini: Mei 2026 ]             [ Berikutnya > ]
+---------------------------------------------------------------------------------+
|  Aksi: [ Connect Google ]  [ Ambil dari Google ]  [ Sync Bulan Ini ]  [ Unduh Excel ]
+---------------------------------------------------------------------------------+
|  Legenda: ( ) UBSI    ( ) Nusa Mandiri    ( ) Cyber University    ( ) Google Ext
+---------------------------------------------------------------------------------+
|   Sen    |   Sel    |   Rab    |   Kam    |   Jum    |   Sab    |   Min         |
|          |  1 [EVT] |  2       |  3       |  4       |  5       |  6            |
|  ...     |  ...     |  ...     |  ...     |  ...     |  ...     |  ...          |
+---------------------------------------------------------------------------------+
```

### 1. Kalender Grid Bulanan Interaktif (`/calendar`):
- **Navigasi Cepat**: Navigasi ke bulan sebelumnya, bulan berikutnya, atau lompat langsung ke bulan berjalan.
- **Indikator Warna Multi-Kampus**:
  - Warna Khusus: Event Kampus UBSI.
  - Warna Khusus: Event Kampus Universitas Nusa Mandiri (UNM).
  - Warna Khusus: Event Kampus Cyber University (CU).
  - Warna Netral: Event Google eksternal lainnya.
- **Interaktivitas Klik**:
  - Mengklik nama event atau kotak tanggal pada kalender langsung mengarahkan pengguna ke halaman detail event internal (`/events/{code}`).
- **Dukungan Tampilan Rentang Hari**:
  - Event multi-hari ditampilkan memanjang pada seluruh tanggal yang bersinggungan di kalender.

### 2. Integrasi Google Calendar API (OAuth 2.0 Web-Server Flow):
- **Tanpa Library Gemuk**: Dibangun menggunakan native PHP cURL ke endpoint REST API Google v3 tanpa beban ratusan file library Google API Client Composer.
- **Token Management Aman**: Menyimpan *access_token* dan *refresh_token* terenkripsi pada tabel `google_oauth_tokens` dengan mekanisme auto-refresh saat token kedaluwarsa.
- **Pencegahan Duplikasi Sinkronisasi (Idempotent)**:
  - Event lokal yang sudah memiliki `external_event_id` akan diperbarui menggunakan metode HTTP `PATCH`, bukan membuat event baru.
  - Jika ID eksternal belum tersimpan, sistem mencari event Google yang memiliki tag deskripsi `EventHub code: EVT-XXXX` pada tanggal terkait. Jika ditemukan, event tersebut ditautkan kembali.
  - Event baru di Google Calendar hanya dibuat jika benar-benar belum ada data duplikatnya.
- **Sync Bulan Terpilih**: Mengirimkan seluruh event lokal yang telah disetujui (*approved*) pada bulan yang sedang dibuka ke Google Calendar.
- **Ambil dari Google (Import)**: Mengambil agenda Google Calendar dan membersihkan cache lokal untuk event yang telah dihapus di sisi Google.
- **Akses Role Luas**: Memungkinkan peran Admin Sistem dan Event Creator untuk menghubungkan akun Google dan menyinkronkan agenda.

### 3. Ekspor Kalender Excel Resmi (`CalendarExcelExporter`):
- **Format XLSX Murni**: Menghasilkan file Excel asli berstandar Microsoft OpenXML SpreadsheetML dengan header resmi bertuliskan *"KALENDER EVENT BSI GROUP [TAHUN]"*.
- **Rentang Bulan Fleksibel**: Pilihan input bulan awal (*start month*) s.d. bulan akhir (*end month*) untuk mengekspor jadwal per kuartal atau per semester.
- **Filter Khusus Tanpa Akademik/Non-Akademik**: Tombol filter `category_scope=without_academic_non_academic` untuk mengekspor event khusus promosi, seminar, dan lomba di luar perkuliahan rutin.
- **Merge Baris Otomatis**: Beberapa event yang jatuh pada tanggal yang sama secara otomatis digabungkan rapi ke dalam satu sel terstruktur.

---

## 9. Fitur Pembuat Dokumen & Proposal AI (Google Gemini)

Salah satu keunggulan terbesar EventHub adalah kemampuan menyusun proposal kegiatan resmi secara instan memanfaatkan kecerdasan buatan (**Google Gemini 2.5 Flash**).

```
+---------------------------+       Prompt Generator        +-----------------------------+
| Input Pengguna:           |  -------------------------->  | Google Gemini AI            |
| - Judul & Kategori        |                               | - Role: Event Planner &     |
| - Tanggal & Lokasi Acara  |                               |   Proposal Writer Kampus    |
| - Target Peserta & Dana   |                               | - Standar Format:           |
| - Ringkasan Kegiatan User |                               |   BSI Digination Seminar    |
+---------------------------+                               +-----------------------------+
                                                                           |
                                                                           v
+-----------------------------------------------------------------------------------------+
| Output Dokumen Lengkap:                                                                 |
| 1. Cover Proposal Resmi            5. Bab II: Konsep, Waktu, Susunan Panitia            |
| 2. Kata Pengantar                  6. Tabel Rundown Acara Lengkap (Waktu, Agenda, PIC)  |
| 3. Daftar Isi                      7. Tabel RAB Anggaran Otomatis (Qty, Harga, Total)   |
| 4. Bab I: Latar Belakang & Tujuan  8. Bab III: Penutup & Lembar Pengesahan Pejabat      |
+-----------------------------------------------------------------------------------------+
```

### 1. AI Proposal Generator Berstandar Kampus (`/generate-documents/proposal`):
- **Standar Format Formal**: Mengikuti struktur baku **Proposal Seminar BSI Digination Universitas Bina Sarana Informatika**.
- **Cukup Mengisi Ringkasan**: Pengguna hanya perlu menginput paragraf ringkasan konsep acara, lalu AI akan menyusun dokumen utuh multi-halaman.
- **Struktur Dokumen yang Digenerate**:
  - **Cover Proposal**: Judul kegiatan, tema, tagline, nama penyelenggara kampus, dan tahun.
  - **Kata Pengantar**: Narasi pengantar yang relevan dengan perkembangan industri dan teknologi.
  - **Daftar Isi**: Peta susunan dokumen.
  - **BAB I Pendahuluan**: Latar belakang mendalam, minimal 5 butir tujuan kegiatan, manfaat (bagi peserta, kampus, dan mitra), serta indikator keberhasilan terukur.
  - **BAB II Pelaksanaan Kegiatan**: Nama & tema, deskripsi bentuk acara, profil pemateri/narasumber, target sasaran peserta, serta waktu pelaksanaan.
  - **Tabel Susunan Acara (Rundown)**: Format tabel markdown rapi (Jam, Mata Acara, Penanggung Jawab).
  - **Struktur Kepanitiaan**: Pembagian panitia lengkap (Pelindung, Pengarah/SC, Ketua Pelaksana, Sekretaris, Bendahara, Sie Acara, Multimedia, Konsumsi, LO, dll.).
  - **Perencanaan Anggaran Biaya (RAB)**: Tabel anggaran profesional dengan kategori biaya (Fee Narasumber, Konsumsi, Perlengkapan, Publikasi, Operasional) lengkap dengan Qty, Satuan, Subtotal, dan **Grand Total** yang dihitung secara matematis.
  - **BAB III Penutup**: Ringkasan harapan dan ajakan kolaborasi.
  - **Lembar Pengesahan**: Format tanda tangan resmi (Ketua Pelaksana, Steering Committee, dan Pimpinan Kampus/Rektor).
- **Auto-Fill Data Realistis**: Jika ada rincian yang belum lengkap pada database, AI secara cerdas mengisi data yang wajar dan relevan dengan konteks tanpa meninggalkan placeholder kosong seperti `[ISI DI SINI]`.

### 2. Manajemen & Pratinjau Dokumen (`/documents`):
- **Pratinjau Cetak (Preview)**: Fitur preview berbasis web dengan rendering Markdown ke HTML siap cetak (*print-ready layout*).
- **Unduh Dokumen Word**: Konversi hasil generate ke format dokumen Microsoft Word (.docx / .doc) yang dapat diedit lebih lanjut.
- **Unggah Template Dokumen**: Admin Sistem dapat mengunggah template dokumen kustom lembaga.
- **Pengelolaan Berkas Terkait**: Mendukung penautan link eksternal (Google Docs / Google Drive) serta pengunggahan langsung berkas PDF/DOCX untuk Proposal, LPJ, Dokumentasi Foto, dan Dokumentasi Video.

---

## 10. Fitur Pelaporan Post-Event & LPJ

Modul pertanggungjawaban untuk mengevaluasi keberhasilan operasional dan finansial acara setelah terlaksana.

### Fitur Modul Pelaporan (`/reports`):
1. **Rekapitulasi Event Terlaksana**:
   - Menampilkan seluruh event yang telah melewati tanggal pelaksanaan atau berstatus `selesai`.
2. **Penilaian Kualitas & Skor Realisasi**:
   - Status realisasi: *Lengkap, Menunggu pelaksanaan, Belum lengkap, Revisi*.
   - Skor evaluasi kegiatan (skala 0 - 100) berdasarkan pencapaian target peserta dan kelengkapan dokumen.
3. **Penyimpanan Berkas Pertanggungjawaban**:
   - Link dokumen Laporan Pertanggungjawaban (LPJ).
   - Link publikasi berita media massa / website kampus.
   - Link promosi & dokumentasi media sosial.
4. **Paging Keberhasilan CRM Khusus**:
   - Panel terpisah yang menampilkan pemeringkatan event dengan tingkat keberhasilan tindak lanjut prospek CRM tertinggi.
   - Paging independen (`crm_page`) yang tidak mengganggu paging tabel laporan utama.
5. **Ekspor Hasil Laporan Kegiatan**:
   - Mengunduh rekap laporan ke Excel dengan kolom kampus pelaksana dan penggabungan tanggal otomatis.
6. **Ekspor Hasil Konversi CRM Seluruh Kampus (`GET /reports/crm-export`)**:
   - Menghasilkan file Excel (.xls) berisikan detail data siswa prospek, asal sekolah, kontak WhatsApp, riwayat tindak lanjut agen, status closing, dan skor hasil konversi untuk seluruh kampus.

---

## 11. Fitur Program Kemitraan Kampus (One Campus)

Modul terintegrasi yang dirancang khusus untuk memfasilitasi program kerja strategis **Kepala Kampus** dan tim **Markom** dalam menjaring calon mahasiswa baru.

```
+---------------------------------------------------------------------------------+
|                           PROGRAM ONE CAMPUS                                    |
+---------------------------------------------------------------------------------+
|  1. One Campus Thirty School (OCTS) -> Target kunjungan 30 sekolah & perolehan PMB
|  2. One Campus Five Spanduk (OCFS)  -> Target pemasangan 5 titik spanduk & foto bukti
|  3. One Campus Five OJT (OCFO)      -> Target penerimaan magang siswa SMK per periode
+---------------------------------------------------------------------------------+
```

### 1. One Campus Thirty School (OCTS):
- Target pelaporan kunjungan dan sosialisasi ke 30 sekolah per kampus.
- Data yang dicatat: Nama Kampus, Nama Sekolah, Nama Guru / Kontak Person, Nomor Handphone / WhatsApp, Jabatan Guru, **Jumlah Perolehan Calon Mahasiswa (PMB)** dari sekolah tersebut, dan Catatan Hasil Kunjungan.
- Dilengkapi pagination (5 baris per halaman), pencarian instan, tombol "Kelola" untuk mengedit data, dan tombol hapus data.

### 2. One Campus Five Spanduk (OCFS):
- Target pelaporan pemasangan minimal 5 titik spanduk publikasi promosi per kampus.
- Data yang dicatat: Nama Kampus, Titik Lokasi Pemasangan Spanduk, Waktu & Tanggal Pemasangan (*datetime-local*), Tema Spanduk, dan **Tautan Foto Bukti Pemasangan di Google Drive**.
- Dilengkapi verifikasi link foto, pagination, pencarian data, serta aksi update dan delete.

### 3. One Campus Five OJT (OCFO):
- Target pelaporan penerimaan siswa SMK/SMA yang menjalani Praktik Kerja Lapangan / On-the-Job Training di lingkungan kampus.
- **Pemilihan Periode Berbasis Rentang Bulan**: Menggunakan dua isian kalender (*Bulan Awal OJT* dan *Bulan Akhir OJT*) dengan validasi tanggal akhir tidak boleh lebih awal dari tanggal awal.
- Data yang dicatat: Nama Siswa Magang, Sekolah Asal, Jurusan, Nomor Telepon, Periode OJT (otomatis digenerate menjadi label bulan, misal: *Mei 2026 - Juli 2026*), dan Catatan Kinerja.
- Status aktif OJT digunakan langsung sebagai filter pemenuhan target bulanan pada Analisis Khusus DMER.

### 4. Keamanan Akses & Scoping Kampus:
- Kepala Kampus dan Markom hanya dapat melihat, menambah, mengubah, dan menghapus data pada kampusnya masing-masing.
- Admin Sistem dan DMER memiliki wewenang akses global terhadap data seluruh kampus.

---

## 12. Fitur Analitik & Analisis Khusus Eksekutif (DMER)

Menyajikan metrik intelijen bisnis bagi jajaran pimpinan untuk memantau performa antar-kampus dan efisiensi strategi promosi.

```
+---------------------------------------------------------------------------------+
|                      ANALISIS KHUSUS KAMPUS (DMER)                              |
+---------------------------------------------------------------------------------+
|  Filter Mode: [ Per Tahun / Per Bulan ]   |   Aksi: [ Unduh Laporan 5 Strategi ]|
+---------------------------------------------------------------------------------+
|  Target Bulanan per Kampus:                                                     |
|  - 2 Event OCTE (Non-Open House)  \                                             |
|  - 2 Event Hangout / Open House    > Total 4 Event / Bulan (48 Event / Tahun)   |
|  * Catatan: Kategori "Aplikasi PMB" dikecualikan dari perhitungan OCTE          |
+---------------------------------------------------------------------------------+
|  Ringkasan:                                                                     |
|  [ Kampus Capai Target ]  [ Kampus Belum Target ]  [ Total Event ]  [ Rata-rata]|
+---------------------------------------------------------------------------------+
|  Tabel Pergerakan Kampus & Grafik Tren Batang Bulanan per Cabang Kampus         |
+---------------------------------------------------------------------------------+
```

### 1. Analitik Kampus Reguler (`/analytics`):
- Filter multi-level: Kampus Pelaksana, Tahun Pelaksanaan, dan Bulan Pelaksanaan.
- Metrik efisiensi biaya (*Cost per Participant*), rasio keterlaksanaan rencana, dan grafik distribusi kategori acara.

### 2. Analisis Khusus Eksekutif DMER (`/analisis-khusus`):
- Akses terbatas hanya untuk role **Admin Sistem** dan **DMER**.
- **Indikator Target Event Kampus**:
  - 2 event OCTE (*One Campus Two Event* / non-Open House) per bulan.
  - 2 event Hangout / Open House per bulan.
  - Total target: 4 event per bulan atau 48 event per tahun per kampus.
  - **Aturan Pengecualian Kategori**: Event dengan kategori *"Aplikasi PMB"* secara otomatis **tidak dihitung** sebagai pemenuhan target OCTE.
- **Peta Gap & Notifikasi Deviasi**: Menampilkan daftar kampus yang belum mencapai target kuota event pada periode terpilih untuk langkah intervensi manajemen.
- **Performa CRM Terintegrasi**: Merangkum total leads siswa yang terjaring pada event periode terpilih, skor respon, jumlah closing, dan rasio penolakan.
- **Unduh Laporan 5 Strategi DMER (`/analisis-khusus/laporan-strategi`)**:
  - Mengunduh rekap spreadsheet komprehensif yang mengombinasikan 5 pilar strategi:
    1. *OCTS (Thirty School)*: Akumulasi kunjungan sekolah mitra.
    2. *OCFS (Five Spanduk)*: Akumulasi pemasangan materi promosi luar ruang.
    3. *OCFO (Five OJT)*: Jumlah siswa magang yang berstatus **masih aktif** pada bulan pelaporan.
    4. *OCTE (Two Event)*: Realisasi event resmi kampus di luar kategori Aplikasi PMB.
    5. *Hasil CRM*: Rasio konversi penutupan pendaftaran PMB dari leads kegiatan.

---

## 13. Fitur CRM & Pipeline Prospek Siswa (Lead Management)

Platform CRM terintegrasi yang berfungsi mengonversi peserta event dan kontak kunjungan sekolah menjadi mahasiswa baru yang terdaftar resmi.

```
+---------------------------------------------------------------------------------+
|                                PIPELINE CRM                                     |
+---------------------------------------------------------------------------------+
|  [TAHAP 1: FRESH DATA]        [TAHAP 2: SUDAH RESPON]      [TAHAP 3: TAHAP LANJUT]
|  - Data baru diupload         - Dihubungi via WhatsApp     - Negosiasi & Registrasi
|  - Belum di-assign /          - Respon prospek positif     - STATUS CLOSING (PMB)
|    Auto-Assign Round Robin    - Input Catatan Follow-up    - Bukti Bukti Pembayaran
+---------------------------------------------------------------------------------+
```

### 1. Single Sign-On (SSO) & Akses Mandiri:
- **SSO Bridge (`EventHubCrmBridge`)**: Pengguna EventHub (seperti Admin atau Markom) dapat langsung beralih ke portal CRM tanpa perlu login ulang.
- **Portal Login Mandiri (`/crm/login`)**: Tersedia untuk agen lapangan yang hanya bertugas mengelola prospek.
- **Pendaftaran Agen Mandiri**: Kepala Kampus dapat mendaftarkan akun agen CRM baru untuk kampusnya melalui `/crm/register`.

### 2. Pipeline Funnel Prospek Siswa:
- **Tahap 1 (Fresh Data)**: Prospek mentah yang baru masuk dari rekap peserta event atau formulir sekolah.
- **Tahap 2 (Sudah Respon)**: Agen telah melakukan kontak awal dan calon mahasiswa memberikan tanggapan.
- **Tahap 3 (Tahap Lanjut)**: Calon mahasiswa masuk tahap konsultasi pendaftaran, penyerahan berkas, atau pembayaran kuliah.

### 3. Klasifikasi Label & Pembobotan Skor Konversi:
- **Closing**: Label keberhasilan mutlak (PMB terdaftar). Memiliki bobot skor tertinggi (**60 poin**) dan secara otomatis mengubah status ke **Tahap 3**.
- **Minat / Tertarik**: Prospek antusias dan meminta brosur/rincian biaya.
- **Ragu-ragu**: Prospek masih membandingkan dengan kampus lain.
- **Menolak**: Prospek tidak berminat; sistem mewajibkan pengisian alasan penolakan (`rejection_reason`) sebagai data evaluasi.
- **Tidak Merespon**: Nomor tidak aktif atau pesan tidak dibalas.

### 4. Operasional Manajemen Data Prospek:
- **Impor Massal Excel/CSV**: Fasilitas upload ratusan prospek dengan pencatatan kode batch unik (`crm_upload_batches`) dan penghitungan total baris.
- **Normalisasi Nomor WhatsApp Otomatis**: Format lokal seperti `081282750318`, `81282750318`, atau `+62812...` otomatis dikonversi menjadi format internasional WhatsApp `6281282750318`.
- **Tombol WhatsApp Instan**: Klik ikon WhatsApp langsung membuka aplikasi/web WhatsApp (`https://wa.me/62...`) dengan nomor siswa yang sudah terformat rapi.
- **Distribusi Agen Otomatis & Manual**:
  - Penugasan manual (*Assign*): Memilih agen tertentu untuk sekelompok data terpilih.
  - Penugasan otomatis (*Auto-Assign*): Algoritma *Round-Robin* yang membagi rata prospek yang belum di-assign kepada seluruh agen aktif di kampus terkait.
- **Filter Komprehensif & Server-Side Paging**: Pencarian siswa, sekolah asal, kampus pelaksana, event terkait, tahapan pipa, label, batch upload, dan rentang tanggal dengan pagination server-side 25 data per halaman.

### 5. Jejak Audit & Bukti Follow-Up (`crm_lead_logs`):
- Setiap perubahan status, perubahan agen, catatan follow-up, dan alasan penolakan dicatat dalam riwayat kronologis.
- Agen dapat mengunggah foto/tangkapan layar bukti percakapan (*screenshot bukti chat WhatsApp*) yang tersimpan aman pada sistem.

### 6. Pemantauan Kinerja Agen (`/crm/monitoring`):
- Dashboard Kepala Kampus dan Admin untuk mengevaluasi kinerja masing-masing agen: total prospek yang ditangani, jumlah kontak tahap 2, jumlah closing yang dicapai, rasio konversi (%), dan akumulasi skor kinerja.

---

## 14. Fitur Master Data & Manajemen Pengguna

Panel kendali Admin Sistem untuk mengatur pondasi data operasional universitas dan tata kelola akun.

### 1. Master Data Kampus & Kategori (`/master-data`):
- **Manajemen Kampus**: Menambah kampus baru, mengubah nama cabang kampus, serta menonaktifkan/mengarsipkan kampus yang sudah tidak aktif.
- **Manajemen Kategori Event**: Menambah kategori (Seminar, Workshop, Lomba, Open House, dll.) yang digunakan sebagai parameter filter di seluruh aplikasi.

### 2. Manajemen Pengguna (`/users`):
- **Monitoring Status Akun**: Menampilkan metrik akun aktif, pendaftar baru (*review*), akun CRM, dan daftar peran.
- **Persetujuan Pendaftar Baru**: Admin meninjau data profil pendaftar, menetapkan role, menentukan kampus penempatan, dan mengaktifkan akun.
- **Pengaturan Role & Hak Akses Dinamis**: Admin dapat membuat role baru atau menyesuaikan array izin akses (*permissions*) default pada setiap peran.
- **Manajemen Akun CRM Terpusat**: Mengaktifkan, menonaktifkan, atau mereset password akun CRM agen langsung dari panel utama.
- **Siklus Hidup Akun Aman**:
  - Akun CRM harus dinonaktifkan terlebih dahulu (*Inactive*) sebelum tombol hapus soft-delete muncul.
  - Fasilitas *Restore* untuk memulihkan akun yang terhapus secara tidak sengaja.
  - Fasilitas *Force Delete* (hapus permanen) hanya tersedia setelah akun berada dalam status terhapus/nonaktif.
- **Reset Password Pengguna**: Admin dapat mengganti password akun pengguna yang lupa kata sandi secara instan.

---

## 15. Direktori Rute & Titik Masuk Aplikasi (Route Map)

Berikut adalah daftar lengkap rute HTTP publik dan terproteksi pada platform EventHub:

### Rute Autentikasi Umum & CRM:
- `GET  /login` : Halaman form masuk pengguna utama.
- `POST /login` : Proses autentikasi dengan rate throttling & proteksi brute force.
- `GET  /register` : Formulir pendaftaran akun pengguna baru.
- `POST /register` : Pengajuan pendaftaran akun (status review).
- `GET  /forgot-password` : Permintaan tautan pemulihan kata sandi.
- `POST /forgot-password` : Pembuatan dan pengiriman token reset password.
- `GET  /reset-password/{token}` : Formulir pembuatan kata sandi baru berbasis token.
- `POST /reset-password/{token}` : Pembaharuan password dan pembatalan token.
- `POST /logout` : Penghancuran sesi login akun utama.
- `GET  /crm/login` : Halaman form masuk portal CRM (mendukung SSO bridge).
- `POST /crm/login` : Autentikasi agen dan administrator CRM.
- `POST /crm/register` : Pendaftaran akun agen CRM baru oleh Kepala Kampus.
- `GET  /crm/template` : Unduh template spreadsheet data prospek siswa.

### Rute Portal CRM (Prefix `/crm`, Filter `crmAuth`):
- `GET  /crm` : Dashboard utama pipeline leads siswa, filter data, dan penugasan.
- `GET  /crm/monitoring` : Dashboard monitoring performa & skor agen CRM.
- `POST /crm/logout` : Keluar dari sesi portal CRM.
- `POST /crm/users` : Tambah akun pengguna agen CRM baru.
- `POST /crm/users/{id}/delete` : Nonaktifkan / hapus akun pengguna CRM.
- `POST /crm/leads/upload` : Unggah rekap prospek siswa via file Excel/CSV.
- `POST /crm/leads/assign` : Penugasan manual leads siswa ke agen tertentu.
- `POST /crm/leads/auto-assign` : Penugasan otomatis round-robin ke seluruh agen aktif.
- `POST /crm/leads/{id}/update` : Pembaharuan tahapan pipa, label, closing, atau penolakan.
- `POST /crm/leads/{id}/delete` : Hapus data prospek siswa.
- `GET  /crm/logs/{id}/image` : Menampilkan bukti gambar/tangkapan layar follow-up agen.
- `POST /crm/batches/{id}/delete` : Hapus batch data upload beserta leads di dalamnya.

### Rute Operasional EventHub (Filter `auth`):
- `GET  /` & `GET /dashboard` : Beranda metrik KPI, event aktif, dan aktivitas sistem.
- `GET  /task-manager` : Antarmuka daftar tugas checklist PIC (Tugas Berjalan & Selesai).
- `GET  /task-manager/new` : Formulir pembuatan tugas baru untuk PIC.
- `POST /task-manager/tasks` : Simpan penugasan tugas baru ke sistem checklist.
- `POST /checklist-tasks/{id}/report` : Kirim laporan penyelesaian tugas dan link bukti oleh PIC.
- `GET  /events` : Daftar seluruh event kampus dengan pencarian dan filter server-side.
- `GET  /events/template` : Unduh template Excel untuk impor data event massal.
- `POST /events/import` : Unggah dan impor massal event dari Excel/CSV.
- `GET  /events/export` : Ekspor seluruh data rekapitulasi event ke spreadsheet.
- `GET  /events/new` : Formulir pendaftaran event baru (single/multi-day).
- `POST /events` : Simpan draft event baru dan inisialisasi checklist.
- `GET  /events/{code}` : Workspace detail event, progres kesiapan, dan penugasan.
- `GET  /events/{code}/edit` : Formulir edit data dan anggaran event.
- `POST /events/{code}/update` : Perbarui rincian event dan tanggal pelaksanaan.
- `POST /events/{code}/delete` : Soft delete event.
- `POST /events/{code}/submit` : Ajukan draft event ke pimpinan untuk review.
- `POST /events/{code}/checklist` : Simpan pembaruan status dan deadline checklist.
- `POST /events/{code}/checklist/items` : Tambah butir kelengkapan checklist kustom.
- `POST /events/{code}/checklist/items/{id}/delete` : Hapus butir kelengkapan checklist.
- `POST /events/{code}/documents/{type}/upload` : Unggah berkas dokumen (Proposal/LPJ/Foto/Video).
- `GET  /events/{code}/documents/{type}/file` : Unduh berkas dokumen event yang tersimpan.

### Rute Kalender & Google Calendar:
- `GET  /calendar` : Tampilan kalender jadwal interaktif multi-kampus.
- `GET  /google-calendar/connect` : Inisiasi koneksi OAuth 2.0 ke Google Calendar.
- `GET  /google-calendar/callback` : Titik balik callback persetujuan OAuth 2.0 dari Google.
- `POST /google-calendar/sync` : Sinkronisasi event approved bulan terpilih ke Google Calendar.
- `POST /google-calendar/sync/{code}` : Sinkronisasi event satuan tertentu ke Google Calendar.
- `POST /google-calendar/import` : Impor agenda dari Google Calendar ke database aplikasi.
- `GET  /google-calendar/export` : Unduh jadwal kalender resmi format Excel (BSI Group styling).

### Rute Approval & Verifikasi:
- `GET  /approvals` : Meja kerja review pengajuan event pimpinan (prioritas event pending).
- `POST /approvals/{code}/approve` : Setujui event dan daftarkan ke jadwal kalender.
- `POST /approvals/{code}/reject` : Tolak event dengan catatan perbaikan untuk kreator.

### Rute Dokumen & AI Generator (Gemini):
- `GET  /documents` : Repositori proposal dan laporan event seluruh kampus.
- `GET  /documents/{type}` : Filter repositori dokumen berdasarkan jenis.
- `GET  /documents/notification/open` : Buka dokumen dari pemberitahuan notifikasi.
- `GET  /documents/{code}/{type}/preview` : Pratinjau dokumen cetak format web.
- `GET  /documents/{code}/{type}/download` : Unduh dokumen format Microsoft Word (.docx).
- `POST /documents/{code}/{type}/template` : Unggah template dokumen baru oleh Admin.
- `POST /documents/{code}/{type}/link` : Perbarui tautan dokumen eksternal Google Drive.
- `POST /documents/{code}/{type}/delete` : Hapus tautan dokumen eksternal.
- `GET  /generate-documents` : Halaman daftar event untuk penyusunan dokumen otomatis.
- `POST /generate-documents/{code}/{type}/generate` : Panggil AI Gemini untuk menyusun proposal.

### Rute Laporan & Post-Event:
- `GET  /reports` : Rekapitulasi laporan acara terlaksana dan evaluasi LPJ.
- `GET  /reports/export` : Unduh rekapitulasi laporan kegiatan ke format Excel.
- `GET  /reports/crm-export` : Unduh hasil konversi tindak lanjut prospek CRM ke Excel.
- `POST /reports/{code}` : Perbarui skor realisasi, catatan LPJ, dan link publikasi.

### Rute One Campus:
- `GET  /one-campus` : Dashboard rekap program One Campus (School, Spanduk, OJT).
- `POST /one-campus/schools` : Tambah laporan kunjungan sekolah (OCTS).
- `POST /one-campus/schools/{id}/update` : Perbarui data laporan sekolah.
- `POST /one-campus/schools/{id}/delete` : Hapus data laporan sekolah.
- `POST /one-campus/banners` : Tambah laporan pemasangan spanduk (OCFS).
- `POST /one-campus/banners/{id}/update` : Perbarui data laporan spanduk.
- `POST /one-campus/banners/{id}/delete` : Hapus data laporan spanduk.
- `POST /one-campus/ojt` : Tambah laporan siswa magang SMK/vokasi (OCFO).
- `POST /one-campus/ojt/{id}/update` : Perbarui data laporan siswa OJT.
- `POST /one-campus/ojt/{id}/delete` : Hapus data laporan siswa OJT.

### Rute Analitik & Analisis Khusus:
- `GET  /analytics` : Analitik distribusi acara dan performa per kampus.
- `GET  /analisis-khusus` : Dashboard eksekutif DMER target 4 event/bulan per kampus.
- `GET  /analisis-khusus/laporan-strategi` : Unduh Laporan 5 Strategi DMER format Excel.

### Rute Notifikasi, Master Data, & Users:
- `GET  /notifications` : Halaman pusat riwayat pemberitahuan pengguna.
- `POST /notifications/read-all` : Tandai seluruh notifikasi telah dibaca.
- `GET  /master-data` : Manajemen data master kampus dan kategori kegiatan.
- `POST /master-data/campuses` : Tambah kampus pelaksana baru.
- `POST /master-data/campuses/{id}/update` : Ubah nama kampus pelaksana.
- `POST /master-data/campuses/{id}/delete` : Nonaktifkan / arsipkan kampus.
- `POST /master-data/categories` : Tambah kategori kegiatan baru.
- `POST /master-data/categories/{id}/update` : Ubah nama kategori kegiatan.
- `POST /master-data/categories/{id}/delete` : Nonaktifkan kategori kegiatan.
- `GET  /users` : Panel kontrol pengguna, persetujuan akun, dan peran.
- `POST /users` : Tambah akun pengguna baru secara manual oleh Admin.
- `POST /users/{id}/update` : Perbarui role, kampus, atau hak akses akun pengguna.
- `POST /users/{id}/delete` : Soft delete akun pengguna.
- `POST /users/{id}/restore` : Pulihkan akun pengguna yang terhapus.
- `POST /users/{id}/force-delete` : Hapus permanen akun pengguna dari basis data.
- `POST /users/{id}/reset-password` : Reset kata sandi pengguna oleh Admin.
- `POST /admin/crm-users` : Tambah akun agen CRM dari panel admin utama.
- `POST /admin/crm-users/{id}/update` : Ubah status/data agen CRM.
- `POST /admin/crm-users/{id}/delete` : Hapus agen CRM (setelah status Inactive).
- `POST /admin/crm-users/{id}/restore` : Pulihkan agen CRM yang terhapus.
- `POST /admin/crm-users/{id}/force-delete` : Hapus permanen agen CRM.
- `POST /admin/crm-users/{id}/reset-password` : Reset kata sandi agen CRM.
- `POST /roles` : Tambah peran kustom baru ke sistem.
- `POST /roles/{id}/update` : Perbarui nama dan hak akses default peran.
- `POST /roles/{id}/delete` : Hapus peran kustom (peran sistem dilindungi).

---

## 16. Panduan Menjalankan Aplikasi & Konfigurasi Lingkungan

### 1. Prasyarat Sistem:
- **Web Server**: Apache (XAMPP for Windows disarankan) atau Nginx.
- **PHP**: Versi **8.2** atau lebih tinggi dengan ekstensi aktif:
  - `php_intl` (wajib untuk pemformatan waktu/lokalisasi CodeIgniter).
  - `php_curl` (wajib untuk Google Calendar API & Gemini AI).
  - `php_mbstring` (manipulasi string UTF-8).
  - `php_zip` (wajib untuk generator file Excel XLSX).
  - `php_mysqlnd` / `pdo_mysql` (koneksi basis data MySQL).
- **Basis Data**: MySQL 5.7+ atau MariaDB 10.4+.

### 2. Konfigurasi Lingkungan (`.env`):
Pastikan file `.env` di direktori root telah dikonfigurasi dengan parameter berikut:

```env
# Konfigurasi Basis URL
app.baseURL = "http://127.0.0.1:8080/"

# Konfigurasi Database MySQL
database.default.hostname = localhost
database.default.database = eventweb
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi

# Integrasi Google Calendar (OAuth 2.0 Web Application)
GOOGLE_CALENDAR_CLIENT_ID = "your-google-client-id.apps.googleusercontent.com"
GOOGLE_CALENDAR_CLIENT_SECRET = "your-google-client-secret"
GOOGLE_CALENDAR_REDIRECT_URI = "http://127.0.0.1:8080/google-calendar/callback"
GOOGLE_CALENDAR_ID = "primary"
GOOGLE_CALENDAR_TIMEZONE = "Asia/Jakarta"

# Integrasi Google Gemini AI
GEMINI_API_KEY = "AIzaSy..."
GEMINI_MODEL = "gemini-2.5-flash"
GEMINI_BASE_URL = "https://generativelanguage.googleapis.com/v1beta"
GEMINI_TIMEOUT = 90

# Konfigurasi Notifikasi Email SMTP (Opsional)
email.fromEmail = "syaifur.syl@gmail.com"
email.fromName = "EventHub"
email.protocol = "smtp"
email.SMTPHost = "smtp.gmail.com"
email.SMTPUser = "syaifur.syl@gmail.com"
email.SMTPPass = "app-specific-password"
email.SMTPPort = 587
email.SMTPCrypto = "tls"
```

### 3. Setup Basis Data & Migrasi:
Jalankan perintah berikut di PowerShell atau Command Prompt:

```powershell
# Buat database jika belum ada
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS eventweb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Jalankan migrasi seluruh tabel (36 migration)
C:\xampp\php\php.exe -d extension_dir=C:\xampp\php\ext -d extension=intl spark migrate

# Jalankan seed data akun demo dan master data
C:\xampp\php\php.exe -d extension_dir=C:\xampp\php\ext -d extension=intl spark db:seed EventHubSeeder
```

### 4. Akun Demo Bawaan Seeder:
Seluruh akun bawaan menggunakan kata sandi: `password`

- **Admin Sistem**: `admin@eventhub.test` (Username: `admin`)
- **Event Creator**: `raka@eventhub.test` (Username: `raka`)
- **Pimpinan**: `dimas@eventhub.test` (Username: `dimas`)
- **Marketing Akademik**: `maya@eventhub.test` (Username: `maya`)

### 5. Menjalankan Server Lokal:
- **Menjalankan CodeIgniter 4 (Default Web App)**:
  ```powershell
  .\serve-local.ps1
  ```
  Akses di peramban: `http://127.0.0.1:8080/login`

- **Menjalankan Laravel 12 Version (Tailwind CSS 4)**:
  ```powershell
  .\serve-laravel.ps1
  ```
  Akses di peramban: `http://127.0.0.1:8081/login`

---
*Dokumentasi ini dibuat otomatis secara menyeluruh berdasarkan analisis source code, skema basis data, rute sistem, dan pustaka internal EventHub.*
