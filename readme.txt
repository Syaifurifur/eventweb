EVENTWEB - CATATAN PERUBAHAN
Tanggal update: 4 November 2026 (Update: Interactive AI School Recommendation Modals, PMB Clustering & Dual-Engine Parity)

Ringkasan
Project EventHub/EventWeb telah diperbarui pada beberapa area utama:
approval event, CRM, rentang tanggal event, logo aplikasi, favicon/taskbar icon,
import rekap event Excel, filter event, analytics, Analisis Khusus untuk Admin Sistem
dan DMER, One Campus, sinkronisasi Google Calendar, export laporan Excel,
paging laporan keberhasilan, Task Manager penugasan user, serta dokumentasi lokal ini.


1. Approval Event
- Halaman Approval sekarang memprioritaskan event berstatus "diajukan" agar muncul di bagian atas.
- Status event dinormalisasi menjadi lowercase dan trim agar data seperti "Diajukan" tetap dihitung sebagai "diajukan".
- Informasi urutan halaman Approval diubah menjadi "Pengajuan menunggu review ditampilkan lebih dulu".
- Perbaikan ini mencegah event yang perlu review tertutup oleh event disetujui/selesai pada pagination.


2. CRM - Label Closing dan Penilaian
- Label CRM baru "Closing" ditambahkan ke pilihan label.
- Validasi model CRM sudah menerima label "Closing".
- Jika label "Closing" dipilih, stage otomatis diset ke "Tahap 3".
- Statistik CRM sekarang menghitung jumlah Closing.
- Monitoring Agent mendapat kolom Closing.
- Conversion rate Agent sekarang dihitung dari Closing.
- Bobot skor Agent diubah agar Closing menjadi nilai tertinggi:
  Closing 60, Follow Up 20, Progress 10, Minat 5, Tahap Lanjut 5,
  dikurangi penalti Menolak dan Tidak Merespon.
- Laporan/analytics CRM ikut menghitung Closing sebagai indikator keberhasilan tertinggi.
- Tabel Data Siswa CRM pada dashboard CRM memakai pagination server-side.
- Data Siswa CRM menampilkan 25 record per halaman.
- Pagination Data Siswa CRM mempertahankan filter aktif seperti kampus, event, tahap,
  label, agent, sekolah, batch, tanggal, dan status unassigned.


3. CRM - Normalisasi Nomor WhatsApp
- Import Excel/CSV sekarang menerima nomor WhatsApp lokal seperti 081282750318.
- Nomor lokal otomatis dinormalisasi menjadi format internasional WhatsApp:
  081282750318 -> 6281282750318
  81282750318  -> 6281282750318
  +6281282750318 -> 6281282750318
- Link wa.me di dashboard CRM ikut menormalisasi data lama saat ditampilkan.
- Data CRM lama yang masih berawalan 08 di database lokal sudah dikonversi ke 62.


4. Event - Rentang Tanggal Pelaksanaan
- Form Buat Event dan Update Event sekarang mendukung event lebih dari satu hari.
- Default form hanya menampilkan "Tanggal Pelaksanaan".
- Jika checkbox "Event berlangsung lebih dari satu hari" dicentang:
  label tanggal awal menjadi "Tanggal Pelaksanaan Awal"
  field "Tanggal Akhir Pelaksanaan" muncul dengan format input date.
- Backend menyimpan tanggal awal di event_date dan tanggal akhir di event_end_date.
- Jika checkbox tidak dicentang, tanggal akhir diabaikan dan disimpan null.
- Validasi mencegah tanggal akhir lebih awal dari tanggal awal.
- Kolom baru event_end_date ditambahkan ke tabel events lewat migration:
  app/Database/Migrations/2026-06-01-000001_AddEventEndDateToEvents.php
- Migration sudah dijalankan di database lokal.
- Tampilan daftar event, approval, dashboard, detail event, calendar, dokumen,
  generate dokumen, reports, analytics, dan export menampilkan rentang tanggal bila ada.
- Google Calendar sync memakai tanggal akhir jika event dibuat sebagai rentang hari.
- Event yang melewati tanggal akhir akan dianggap selesai berdasarkan event_end_date.


5. Logo EventHub
- Logo EventHub bawaan teks/kotak "EH" diganti dengan logo baru dari file gambar user.
- Asset baru dibuat:
  public/assets/img/eventhub-logo.png
  public/assets/img/eventhub-mark.png
- Sidebar memakai mark logo agar tetap rapi di ukuran kecil.
- Halaman login, register, forgot password, reset password, dan login CRM memakai logo full.
- Header CRM memakai mark logo EventHub.
- CSS sudah disesuaikan agar ukuran logo proporsional.
- CSS dan JS diberi cache-buster agar browser mengambil asset terbaru.


6. Favicon / Icon Taskbar
- Favicon CodeIgniter bawaan diganti dengan logo baru.
- Asset yang diperbarui/dibuat:
  public/favicon.ico
  public/assets/img/taskbar-icon.png
- Favicon dibuat multi-size: 16, 32, 48, 64, 128, dan 256 px.
- Layout utama sudah diberi tag rel="icon" dan rel="apple-touch-icon".
- Jika browser masih menampilkan icon lama, lakukan Ctrl + F5 atau tutup dan buka ulang browser.


7. Event - Import Rekap Excel
- Role Admin Sistem pada halaman Event mendapat fitur import data event dari Excel/CSV.
- Tombol "Template Import" dan "Download Template" tersedia di halaman daftar event.
- Template import disesuaikan dengan struktur tabel events dan kebutuhan relasi kampus.
- Kolom penting template:
  code, title, category, campus, kampus_pelaksana, event_date, event_end_date,
  location, pic, participants, status, description.
- Kolom kampus_pelaksana dipakai untuk sinkronisasi ke data kampus yang sudah ada.
- Nama kampus dapat ditulis sebagian selama unik dan masih bisa dikenali, contoh:
  UBSI Bekasi dapat diklasifikasi ke data kampus UBSI KAMPUS BEKASI CUT MUTIA
  bila tidak ada kandidat lain yang ambigu.
- Jika code dikosongkan, sistem otomatis membuat kode event baru yang unik.
- Jika code diisi manual dan sudah ada di database, import akan ditolak agar tidak menduplikasi data.
- Import menerima title pendek mulai 3 karakter untuk data rekap seperti "Tes" atau "BKOT".
- Baris kosong atau baris penanda yang tidak berisi data event akan dilewati.
- Validasi tanggal tetap memastikan event_end_date tidak lebih awal dari event_date.
- Saat import berhasil, data proposal, checklist, dan report dasar ikut dibuat agar event siap muncul di modul lain.


8. Event - Filter Daftar Event
- Filter pencarian daftar event sekarang diproses dari backend/server-side.
- Pencarian dapat memakai judul, kampus, lokasi, atau PIC.
- Filter kategori dan status tetap tersedia bersama kolom pencarian.
- Pagination mempertahankan parameter filter yang sedang dipakai.
- Tombol Reset tersedia untuk mengembalikan daftar event ke kondisi awal.
- Perubahan ini memperbaiki kondisi saat pencarian kampus menampilkan jumlah data tidak sesuai.


9. Analytics - Filter Tahun
- Halaman Analytics sekarang memiliki pilihan tahun.
- Data analytics bisa difilter berdasarkan tahun dan bulan.
- Daftar tahun dibuat dari data event yang tersedia.
- Label periode analytics mengikuti filter yang dipilih agar pembacaan laporan lebih jelas.


10. Analisis Khusus DMER dan Admin Sistem
- Menu baru "Analisis Khusus" ditambahkan untuk role Admin Sistem dan DMER.
- URL fitur:
  http://127.0.0.1/eventweb/public/analisis-khusus
- Route yang dipakai:
  GET analisis-khusus -> EventSuite::specialAnalytics
- Akses route dibatasi untuk role Admin Sistem dan DMER.
- Role DMER ditambahkan melalui migration:
  app/Database/Migrations/2026-06-03-000001_AddDmerRoleForSpecialAnalytics.php
- Analisis Khusus menyediakan satu dashboard untuk membaca pergerakan event setiap kampus.
- Filter tersedia dalam dua mode:
  Per Tahun
  Per Bulan
- Target event per kampus:
  2 event OCTE/non-Open House per bulan
  2 event Hangout/Open House per bulan
  total 4 event per bulan atau 48 event per tahun.
- Kategori "Aplikasi PMB" tidak dihitung sebagai One Campus Two Event/OCTE.
- Kategori "Aplikasi PMB" tidak dipakai sebagai pemenuhan target One Campus Two Event
  pada Analisis Khusus, Analytics, maupun download laporan 5 Strategi DMER.
- Dashboard menampilkan ringkasan kampus mencapai target, kampus belum target,
  total event periode, dan capaian rata-rata.
- Panel notifikasi menampilkan kampus yang belum mencapai target yang diharapkan.
- Tabel pergerakan kampus menampilkan jumlah event, gap target, persentase capaian,
  dan grafik bar bulanan.
- Laporan CRM juga ditambahkan di Analisis Khusus.
- Laporan CRM menampilkan total data CRM terkait event periode, score CRM, Closing,
  Tahap 2/merespon, Tidak Merespon, Menolak, serta daftar ringkas event CRM.
- Download laporan 5 Strategi DMER mengikuti aturan terbaru:
  OCTS / One Campus Thirty School dihitung tanpa melihat bulan input data.
  OCFS / One Campus Five Spanduk dihitung tanpa melihat bulan input data.
  OCFO / One Campus Five OJT hanya dihitung jika peserta masih aktif pada bulan laporan.
  OCTE / One Campus Two Event tidak menghitung event kategori Aplikasi PMB.
- Jika masa OJT sudah selesai, data OJT tidak dimasukkan lagi ke laporan bulan berjalan.


11. One Campus Five OJT - Periode Bulan Awal dan Akhir
- Form One Campus Five OJT tidak lagi memakai input teks bebas "Periode OJT".
- Periode OJT diganti menjadi dua isian datepicker:
  Bulan Awal OJT
  Bulan Akhir OJT
- User memilih tanggal lewat datepicker, lalu sistem membaca bulan dari tanggal tersebut
  untuk kebutuhan laporan.
- Backend memvalidasi tanggal akhir agar tidak lebih awal dari tanggal awal.
- Sistem tetap mengisi teks ojt_period otomatis untuk tampilan rekap lama, contoh:
  Mei 2026 - Juli 2026
- Kolom baru ditambahkan lewat migration:
  app/Database/Migrations/2026-06-04-000001_AddOjtMonthRangeToOneCampusOjtReports.php
- Kolom baru pada tabel one_campus_ojt_reports:
  ojt_start_month
  ojt_end_month
- Model OneCampusOjtReportModel sudah menerima field baru tersebut.
- Data OJT lama yang masih berupa teks periode tetap dicoba dibaca saat laporan 5 Strategi
  dengan pola seperti "Mei - Juli 2026".
- Fitur One Campus sekarang memiliki CRUD pada tabel rekap.
- Data One Campus Thirty School, One Campus Five Spanduk, dan One Campus Five OJT
  dapat diedit melalui tombol "Kelola" pada tiap baris tabel.
- Setiap baris rekap juga memiliki tombol hapus untuk menghapus data yang sudah tidak dipakai.
- Semua tabel rekap One Campus memakai pagination.
- Setiap halaman rekap One Campus berisi 5 data.
- Setiap tabel rekap One Campus memiliki pencarian sendiri:
  pencarian rekap school berdasarkan school, guru, kampus, jabatan, PMB, dan keterangan
  pencarian rekap spanduk berdasarkan lokasi, tema, kampus, waktu, dan link foto
  pencarian rekap OJT berdasarkan nama, sekolah/kampus asal, jurusan, periode, kampus, dan catatan
- Pencarian rekap One Campus memakai data-search-text khusus agar isi dropdown edit tidak ikut
  mengganggu hasil pencarian.
- Route CRUD One Campus yang ditambahkan:
  POST one-campus/schools/{id}/update
  POST one-campus/schools/{id}/delete
  POST one-campus/banners/{id}/update
  POST one-campus/banners/{id}/delete
  POST one-campus/ojt/{id}/update
  POST one-campus/ojt/{id}/delete
- Untuk role Kepala Kampus, update dan delete dibatasi hanya pada data kampusnya sendiri.


12. CRM - Delete Akun Setelah Nonaktif
- Akun CRM sekarang harus dinonaktifkan terlebih dahulu sebelum bisa dihapus.
- Tombol "Hapus Akun CRM" hanya muncul jika status akun CRM sudah Inactive.
- Backend menolak request delete jika status akun masih Active atau Review.
- Pesan validasi:
  Nonaktifkan akun CRM terlebih dahulu sebelum menghapus.
- Setelah akun dihapus secara soft delete, admin masih dapat:
  mengaktifkan ulang akun CRM
  menghapus permanen dari database
- Hapus permanen tetap hanya bisa dilakukan setelah akun sudah terhapus/nonaktif.


13. Google Calendar - Pencegahan Duplikasi Sinkronisasi
- Sinkronisasi event lokal ke Google Calendar dibuat idempotent.
- Jika event sudah pernah tersinkron dan memiliki external_event_id, sync berikutnya
  melakukan update ke event Google yang sama, bukan membuat event baru.
- GoogleCalendarService sekarang memakai PATCH untuk memperbarui event yang sudah ada.
- Jika external_event_id belum tersimpan, sistem mencari event Google yang memiliki
  teks "EventHub code: EVT-..." di deskripsi pada rentang tanggal event.
- Jika event Google dengan kode EventHub yang sama ditemukan, sistem akan update event itu.
- Event baru di Google Calendar hanya dibuat jika belum ada external_event_id dan tidak ada
  event Google lama dengan kode EventHub yang sama.
- Manual sync, sync semua approved, dan auto sync saat event dibuat sudah memakai logika ini.
- Sync Google Calendar dari halaman kalender sekarang mengikuti bulan yang sedang dipilih,
  sehingga tidak langsung mengirim semua event approved/selesai.
- Event lokal yang memiliki rentang tanggal dan menyentuh bulan terpilih tetap ikut disinkronkan.
- Role Event Creator sekarang dapat connect Google Calendar, mengambil event Google,
  sync bulan terpilih, download Excel, dan sync event satuan.
- Jika sync gagal atau masih pending connect, external_event_id lama tidak dihapus dari database.


14. Laporan - Paging Keberhasilan CRM
- Panel "Keberhasilan follow up" pada fitur Laporan dibuat memakai sistem paging sendiri.
- Paging panel keberhasilan memakai parameter:
  crm_page
- Paging keberhasilan tidak bentrok dengan paging tabel laporan yang memakai parameter:
  page
- Data keberhasilan CRM diurutkan berdasarkan score tertinggi ke terendah.
- Event dengan tingkat keberhasilan paling bagus tampil pada ranking paling atas.
- Setiap kartu keberhasilan menampilkan nomor ranking seperti #1, #2, dan seterusnya.
- Ringkasan paging menampilkan jumlah data, contoh:
  1-5 dari 20 performa CRM.
- Tombol "Download Hasil CRM" ditambahkan pada fitur Laporan.
- URL export hasil CRM:
  GET reports/crm-export -> EventSuite::exportCrmResults
- File export berbentuk Excel .xls dan berisi data CRM seluruh kampus yang sudah menjalankan CRM.
- Kolom export hasil CRM mencakup kampus, event, kategori, tanggal, lokasi, nama siswa,
  asal sekolah, WhatsApp, tahap CRM, label CRM, hasil CRM, skor hasil, agent, catatan,
  tanggal follow up, batch upload, file sumber, tanggal input, dan terakhir update.


15. Users - Pencarian Daftar Akun Berdasarkan Kampus
- Pencarian pada tabel "Daftar akun" diperbaiki agar bisa mencari berdasarkan nama kampus.
- Pencarian akun sekarang memakai data-search-text khusus per baris.
- Data pencarian hanya berisi data akun yang relevan:
  nama, username, email, role, status, kampus, akses, dan status terhapus.
- Perbaikan ini mencegah dropdown kampus di panel "Kelola" ikut terbaca sebagai semua kampus
  pada setiap baris akun.
- Pencarian pada tabel "Akun CRM Agent" juga memakai data-search-text agar pencarian kampus
  CRM lebih akurat.


16. Update 17 Juni 2026 - Calendar, Laporan, dan One Campus
- Google Calendar import sekarang membersihkan cache event Google yang sudah tidak ada lagi
  di Google Calendar pada bulan yang sedang disinkronkan.
- Event Google yang berasal dari event sistem dan memiliki "EventHub code: EVT-..."
  tidak lagi ditampilkan dobel sebagai event Google terpisah di kalender aplikasi.
- Saat event Google yang memiliki kode EventHub ditemukan, data calendar_integrations
  ikut diperbarui dengan external_event_id dan external_link yang benar.
- Saat event lokal diperbarui, sistem mencoba memperbarui event Google yang sama.
- Saat event lokal dihapus, sistem membersihkan calendar_integrations dan cache
  google_calendar_events terkait, serta mencoba menghapus event tersebut dari Google Calendar.
- Runtime upsert calendar_integrations sekarang membersihkan record duplikat untuk kombinasi
  event_id dan provider yang sama.
- Migration baru ditambahkan untuk membersihkan duplikasi lama dan menambahkan unique key:
  app/Database/Migrations/2026-06-17-000001_DedupeCalendarIntegrations.php
- Download Excel Google Calendar tetap tersedia dengan range bulan awal dan akhir.
- Di fitur Kalender ditambahkan satu tombol:
  Download Tanpa Akademik/Non Akademik
- Tombol tersebut memakai category_scope=without_academic_non_academic dan mengunduh data
  kalender selain kategori Akademik dan Non Akademik.
- Tombol download Akademik dan Non Akademik pada fitur Kalender dan Laporan sudah dihapus
  sesuai permintaan terakhir.
- Export Excel Kalender menggabungkan event pada tanggal yang sama menjadi satu baris.
- Export Excel Kalender menampilkan nama kampus pelaksana pada teks event jika event berasal
  dari data EventHub yang tertaut lewat EventHub code.
- Export laporan kegiatan pada fitur Laporan selalu menyertakan kolom Kampus Pelaksana,
  termasuk saat filter hanya memilih satu kampus.
- Export laporan kegiatan menggabungkan laporan dengan tanggal pelaksanaan yang sama
  menjadi satu baris.
- Jika beberapa laporan digabung dalam satu tanggal, nilai berbeda seperti event, kampus,
  catatan, link, dan status digabung unik dengan pemisah titik koma.
- Backend export Laporan masih mendukung parameter category_scope untuk kebutuhan export
  kategori, namun tombol kategori Akademik/Non Akademik tidak ditampilkan di UI.
- Fitur One Campus sekarang memakai akses kampus untuk pengelolaan data.
- User yang memiliki akses kampus yang sama dapat melihat, menambah, mengubah, dan menghapus
  data One Campus pada kampus tersebut.
- Dropdown kampus pada fitur One Campus dibatasi ke kampus yang boleh diakses user
  untuk user yang memiliki scope kampus.
- Request manual update/delete One Campus juga divalidasi agar user tidak bisa mengelola
  data kampus lain.
- Role Markom dan Marketing Akademik yang memiliki campus_id dapat mengelola data One Campus
  pada kampusnya masing-masing.
- Admin Sistem dan DMER tetap memiliki akses global One Campus.
- Markom global dengan kampus Wilayah 1 Jabodetabek tetap mengikuti aturan global yang
  sudah ada di sistem.


17. Update 26 Juni 2026 - Task Manager Buat Tugas User
- Halaman Task Manager sekarang memiliki tombol baru:
  Buat Tugas
- Tombol tersebut membuka halaman baru:
  GET task-manager/new -> EventSuite::newTask
- Halaman Buat Tugas berisi form untuk membuat tugas baru dan menugaskannya ke user.
- Field form Buat Tugas:
  Event
  Nama tugas
  Kepada siapa tugas diberikan
  Fase
  Deadline
  Deskripsi tugas
  Link bahan tugas
- Submit form memakai route:
  POST task-manager/tasks -> EventSuite::storeTask
- Tugas baru disimpan sebagai data event_checklist_items agar tetap menyatu dengan sistem
  checklist event yang sudah ada.
- Tugas langsung diberi is_assigned=1 dan assigned_at saat berhasil dibuat.
- PIC/user penerima tugas disimpan ke pic_user_id.
- Deskripsi tugas disimpan ke kolom notes.
- Link bahan tugas disimpan ke kolom material_drive_link.
- Link bahan tugas divalidasi agar hanya menerima URL http/https yang valid.
- User penerima tugas wajib berstatus Aktif.
- Event yang bisa dipilih hanya event yang boleh dikelola oleh user yang sedang login.
- Admin Sistem dapat membuat tugas untuk event secara global.
- Pembuat event dapat membuat tugas untuk event miliknya.
- User dengan scope kampus tetap dibatasi hanya pada event kampusnya.
- Setelah tugas dibuat, sistem membuat notifikasi checklist untuk user penerima tugas.
- Setelah tugas dibuat, progress event dihitung ulang dari checklist.
- Tugas yang dibuat dari halaman baru akan muncul di Task Manager user penerima seperti
  tugas checklist lain.
- Halaman Task Manager tetap memiliki pencarian dan tab Tugas Berjalan/Tugas Selesai.
- Styling baru ditambahkan agar tombol Buat Tugas, toolbar pencarian, dan form tugas baru
  rapi di desktop maupun mobile.
- Role Admin Sistem pada Task Manager sekarang dapat melihat semua tugas yang sudah
  ditugaskan ke user.
- Tab Tugas Berjalan dan Tugas Selesai untuk Admin Sistem menghitung seluruh tugas assigned,
  bukan hanya tugas milik akun admin tersebut.
- Kartu tugas pada tampilan Admin Sistem menampilkan nama PIC agar tugas mudah dipantau.
- Pencarian Task Manager sekarang juga dapat mencari berdasarkan nama PIC.
- Event lokal yang tampil di halaman Kalender sekarang dapat diklik untuk membuka detail event.
- Link detail event pada Kalender mengarah ke:
  GET events/{kode_event}
- Kotak tanggal yang memiliki event lokal juga diberi fallback klik ke detail event pertama pada tanggal tersebut.
- Klik event Google lainnya tidak diarahkan ke detail event lokal.
- Perilaku klik kalender diperbaiki di public/assets/js/app.js agar klik event tidak hanya memilih tanggal.


18. Menjalankan Website Lokal
- Website berjalan lewat XAMPP Apache pada URL:
  http://127.0.0.1/eventweb/public/login
- Database lokal menggunakan MySQL eventweb.
- Command migration yang dipakai:
  C:\xampp\php\php.exe spark migrate


19. Verifikasi yang Sudah Dilakukan
- Syntax check PHP dilakukan dengan php -l pada file controller, model, migration, dan view yang diubah.
- Endpoint login lokal sudah merespons HTTP 200.
- Asset favicon.ico dan taskbar-icon.png sudah merespons HTTP 200.
- Kolom event_end_date sudah ada di tabel events.
- Route import event dan template import sudah ditambahkan.
- Parsing Excel/CSV import event sudah diberi guard error agar tidak masuk halaman Whoops.
- Kode event otomatis dibuat unik saat kolom code dikosongkan.
- Filter daftar event sudah dipindah ke server-side dan pagination menjaga query filter.
- Filter tahun analytics sudah ditambahkan.
- Migration role DMER untuk Analisis Khusus sudah dijalankan di database lokal.
- Menu Analisis Khusus sudah tersedia untuk Admin Sistem dan DMER.
- Syntax check PHP berhasil untuk perubahan One Campus OJT, delete akun CRM,
  Google Calendar anti-duplikasi, dan paging keberhasilan laporan.
- Syntax check PHP berhasil untuk export Excel data hasil CRM seluruh kampus.
- Syntax check PHP berhasil untuk perbaikan pencarian daftar akun berdasarkan kampus.
- Syntax check PHP berhasil untuk pagination 25 record per halaman pada Data Siswa CRM.
- Syntax check PHP berhasil untuk CRUD One Campus School, Spanduk, dan OJT.
- Syntax check PHP berhasil untuk pagination 5 data dan pencarian pada semua rekap One Campus.
- Syntax check PHP berhasil untuk pengecualian kategori Aplikasi PMB dari pemenuhan OCTE.
- Syntax check PHP berhasil untuk sync Google Calendar per bulan dan akses route Google Calendar
  bagi role Event Creator.
- Syntax check PHP berhasil untuk perbaikan sinkronisasi Google Calendar terbaru,
  export Excel Kalender, export Excel Laporan, dan pembatasan akses One Campus per kampus.
- Syntax check PHP berhasil untuk fitur Task Manager Buat Tugas pada:
  app/Controllers/EventSuite.php
  app/Config/Routes.php
  app/Models/EventChecklistItemModel.php
  app/Views/pages/task_manager.php
  app/Views/pages/task_form.php
- Route Task Manager baru sudah dicek dengan command:
  C:\xampp\php\php.exe spark routes
- Route GET task-manager/new dan POST task-manager/tasks sudah terdaftar.
- Syntax check PHP berhasil untuk perubahan halaman Kalender:
  app/Views/pages/calendar.php
- Route Google Calendar dan Kalender sudah dicek ulang setelah perubahan export.
- Migration dedupe calendar_integrations sudah dibuat, tetapi perlu dijalankan saat MySQL/XAMPP aktif
  jika database lokal belum menerima unique key calendar_integrations_event_provider_unique.
- Migration OJT month range sudah dibuat, tetapi perlu dijalankan saat MySQL/XAMPP aktif
  jika database lokal belum menerima kolom ojt_start_month dan ojt_end_month.


34. One Campus Sixty School - Klasterisasi Tahun PMB
- Menambahkan sistem klasterisasi data sekolah mitra berdasarkan siklus tahun PMB (September ke September).
- Default periode PMB saat ini terset ke "September 2026 - September 2027 (Berjalan)".
- Klaster periode yang didukung:
  * September 2026 - September 2027 (Default)
  * September 2025 - September 2026
  * September 2024 - September 2025
  * September 2027 - September 2028 (Mendatang)
  * Semua Periode PMB
- Setiap tombol klaster menampilkan badge jumlah perolehan sekolah secara real-time.
- Memilih klaster otomatis memfilter data tabel Sixty School dan ekspor hasil XLSX.


35. One Campus Sixty School - Upgrade Form Input Data Sekolah Penerima BJS
- Proses input data Sixty School ditingkatkan sesuai layout form Beasiswa Jalur Undangan (BJS):
  * Baris 1: NPSN, Nama Sekolah, Kota, Jenis Skl (SMA/SMK/MA/Lainnya), Status Skl (Negeri/Swasta).
  * Baris 2: Nama Kepala Sekolah, Telp Sekolah, Email Sekolah.
  * Baris 3: Nama Kontak (PIC), Telp Kontak, Email Kontak.
  * Baris 4: Status (Aktif/Nonaktif), Tahun PMB, Kampus Pelaksana, Perolehan Siswa PMB.
- Tampilan form dilengkapi header "Data Sekolah Penerima Beasiswa", tombol hijau "+ Kembali",
  dan tombol simpan berwarna teal "Simpan Data Sekolah".
- Backwards compatibility: kolom lama teacher_name dan phone otomatis disinkronkan dengan contact_name dan contact_phone.
- Tombol "Kelola" pada tabel rekap Sixty School sekarang berpindah page langsung ke halaman khusus edit (/one-campus/schools/{id}/edit).
- Halaman khusus edit menampilkan form lengkap dengan nilai tersimpan, pratinjau dokumen MOU PDF, link Google Drive, tombol kembali, tombol simpan perubahan, serta opsi hapus data sekolah.


36. One Campus Sixty School - Media Upload Dokumen MOU (PDF) & Upload Otomatis ke Google Drive Admin
- Ditambahkan Media Upload Dokumen MOU resmi format PDF (.pdf) dengan batas ukuran file maksimal 15 MB.
- File PDF disimpan aman di folder terpusat writable/uploads/mou_documents/.
- Ditambahkan fitur Upload Otomatis Langsung ke Google Drive Admin via Google Apps Script Webhook:
  * GoogleDriveUploadService.php menangani proses transfer file PDF secara aman menggunakan multipart Base64 cURL.
  * Opsi toggle "Otomatis upload file MOU ke Google Drive saat form disimpan".
  * Saat diunggah, file langsung terkirim ke folder Google Drive Admin dan URL file Drive otomatis disimpan ke database (mou_drive_link).
- Ditambahkan tombol sinkronisasi instan "Kirim ke Drive" pada tabel rekap dan halaman edit sekolah untuk file MOU yang belum terunggah ke Drive.
- Ditambahkan drawer konfigurasi Google Drive Akun Admin:
  * Email Akun Google Drive Admin MOU (gdrive_admin_email)
  * URL atau ID Folder Google Drive MOU (gdrive_mou_folder_url)
  * Label Nama Folder Google Drive (gdrive_mou_folder_name)
  * Webhook URL Google Apps Script (gdrive_webhook_url)
  * Toggle Upload Otomatis (gdrive_auto_upload_enabled)
  * Tombol "Uji Koneksi Webhook" untuk memverifikasi kesiapan Apps Script.
  * Template kode Google Apps Script siap pakai dengan tombol salin cepat.
- Ditambahkan route streaming/download: GET one-campus/schools/{id}/mou untuk membuka dokumen PDF MOU langsung di browser.
- Kolom "Dokumen MOU" pada tabel rekap menampilkan badge interaktif:
  * Tombol merah "Lihat PDF MOU" (membuka PDF file terupload)
  * Tombol biru "Buka di Drive" (membuka link Google Drive)
  * Tombol teal "Kirim ke Drive" (sinkronisasi manual ke Google Drive)
  * Status "Belum ada MOU" jika dokumen belum tersedia.


37. Super Admin - Fasilitas Impersonate Seluruh Akun User
- Super Admin / Admin Sistem memiliki fasilitas login sementara (impersonate) ke seluruh akun user
  untuk memantau kegiatan, mengecek fitur, dan memverifikasi tampilan tiap peran secara langsung.
- Dilengkapi banner persistent "Mode Impersonate" di header atas dengan tombol "Kembali ke Akun Super Admin".


38. AI - Rekomendasi Chat Siswa yang Lebih Natural & Auto-Kampus
- Rekomendasi template chat follow-up siswa CRM disesuaikan agar lebih natural, ramah, dan manusiawi tanpa icon/emoji berlebihan serta tanpa formatting bold yang kaku.
- Form brief AI event secara otomatis mengambil data kampus pelaksana dari event terkait tanpa menampilkan status "Belum ditentukan".


39. Tampilan - Optimalisasi Kontras Dark & Light Mode
- Semua teks, tabel data, modal popup, kartu statistik, dan elemen input form telah disesuaikan dengan CSS variable bertingkat
  sehingga terbaca jelas dan memiliki kontras tajam pada template Dark Mode maupun Light Mode.


40. Paritas Ganda Penuh: CodeIgniter 4 dan Laravel
- Seluruh modul, migrasi tabel, controller, model, view, routing, dan validasi dijaga dalam keselarasan 100%
  antara CodeIgniter 4 (port 8080) dan Laravel (port 8081).


41. One Campus Sixty School - Klaster Baru Superadmin, Upload Excel PMB, Detail Mahasiswa & Fasilitas Sortir
- Klasterisasi PMB disesuaikan: hanya menampilkan klaster yang sedang berjalan (September 2026 - September 2027) dan klaster sebelumnya.
- Khusus Super Admin / Admin Sistem: ditambahkan tombol "+ Buat Klasterisasi Baru" untuk menambah klaster tahun mendatang atau custom secara dinamis.
- Ditambahkan fitur "Upload Data PMB (Excel)" & Integrasi 27 Data Kode Kampus UBSI:
  * Master data 27 kampus resmi UBSI terintegrasi dengan field kode_ubsi & campus_code (Margonda 01/D2, Fatmawati 02/A1, Tangerang 03/C1, Bekasi 04/E1, Kaliabang 05/E2, Kalimalang 06/F3, Kramat 98 07/B2/B4, Jogja 09/K1, Jatiwaringin 11/F1, Ciledug 12/G1, Bogor B 13/H1, Karawang 14/E5, Cikampek 15/E6, Tasikmalaya 17/J1, Purwokerto 21/K6, Cikarang 24/E4, Cengkareng 25/M1, Slipi 26/M2, BSD 27/N1, Sukabumi 28/O1, Ciputat 29/C3, Pontianak 30/P1, DWS B 31/F4, Tegal 35/K7, Pemuda 37/B7, Cibitung 38/E3, Bogor A 39/H2).
  * Template Excel PMB multi-sheet: Sheet 1 (Data Mahasiswa PMB) mencakup kolom Kode Kampus, Kode UBSI, Kampus, dsb; Sheet 2 (Referensi Kode Kampus) menyajikan tabel 27 kampus lengkap.
  * Mesin pencocokan cerdas: mencocokkan kampus mahasiswa via Kode UBSI, Kode Kampus, nama/alias kampus, atau default kampus modal.
  * Sistem otomatis mencocokkan asal sekolah atau NPSN. Jika sekolah belum ada di Sixty School, otomatis dibuatkan data sekolah baru.
  * Data mahasiswa baru disimpan ke tabel one_campus_pmb_students lengkap dengan kode_ubsi & campus_code.
  * Perolehan PMB sekolah (pmb_count) otomatis terhitung dan terupdate secara real-time.
- Ditambahkan route dan tombol "Template Excel PMB" (GET one-campus/pmb-template) untuk mengunduh template resmi multi-sheet.
- Ditambahkan fasilitas melihat detail mahasiswa baru per sekolah:
  * Kolom PMB di tabel Sixty School dapat diklik untuk membuka modal rincian nama mahasiswa baru, nomor pendaftaran, prodi, kampus & kode kampus, nomor kontak, dan status.
  * Halaman edit sekolah (/one-campus/schools/{id}/edit) dilengkapi tabel rincian mahasiswa baru lengkap dengan kolom Kampus/Kode beserta opsi hapus data mahasiswa.
- Ditambahkan fasilitas sortir tabel Sixty School:
  * Tombol aksi cepat: "PMB Terbanyak (Banyak ke Sedikit)", "PMB Tersedikit", dan "Nama Sekolah (A-Z)".
  * Kolom Perolehan PMB dapat diklik langsung untuk toggle urutan banyak ke sedikit (DESC/ASC) dengan indikator panah.
  * Ketika diurutkan terbanyak, sistem menampilkan badge medali peringkat (1st, 2nd, 3rd, dst.).


42. Dashboard Utama - Rekomendasi Cerdas AI Analisis Interaksi & Konversi Sekolah (Role-Based Scoping)
- Ditambahkan widget cerdas AI di dashboard utama (/dashboard) yang mengintegrasikan 4 pilar data:
  1. Perolehan Mahasiswa PMB
  2. Riwayat Kunjungan Sekolah (Visit / Sosialisasi)
  3. Event Kampus yang terafiliasi
  4. Status Dokumen MoU Sekolah
- Personalisasi Sesuai Hak Akses & Peran User (Role-Based Scoping):
  * Kepala Kampus: Sistem secara dinamis membatasi evaluasi rekomendasi AI, metrik ringkasan, dan event/kunjungan hanya untuk sekolah-sekolah di bawah kampusnya sendiri (session user.campus_id). Dilengkapi badge "Khusus Kampus: [Nama Kampus]".
  * Super Admin / Admin Sistem / DMER: Evaluasi mencakup seluruh sekolah dan kampus di sistem, dilengkapi badge "Semua Kampus (Super Admin)".
- Menampilkan 4 kartu ringkasan metrik: Total Sekolah Mitra, Peluang MoU Kunci, Mitra Unggulan (Champion), dan Evaluasi Sosialisasi.
- Menghitung skor interaksi (0-100) dan mengelompokkan sekolah ke kategori strategis (Peluang MoU Kunci, Champion, Evaluasi Sosialisasi, Aktivasi MoU, Prospek Baru).
- Memberikan teks saran strategis AI yang kontekstual dan tombol aksi langsung (Kelola Sixty School).


43. CRM Workspace & Sixty School - Klasterisasi Masa PMB & Fitur Set Klaster Default
- Data prospek CRM (crm_leads) kini terklasterisasi berdasarkan masa PMB (`pmb_period`) dengan default aktif 'September 2025 - September 2026'.
- Seluruh 146 prospek yang sudah tersimpan di database lokal otomatis di-backfill ke klaster 'September 2025 - September 2026'.
- Bilah navigasi klaster masa PMB di bagian atas CRM Workspace menampilkan jumlah prospek per klaster secara real-time dan opsi "Semua Klaster PMB".
- Khusus Super Admin / Admin Sistem: Disediakan tombol dan modal dialog "+ Buat Klasterisasi PMB Baru (Superadmin)" untuk membuat klaster tahun akademik baru secara dinamis yang tersimpan ke system_settings (`crm_custom_pmb_periods` & `one_campus_custom_pmb_periods`).
- Fitur Menjadikan Klaster Default (Set as Default / Waktu PMB Berjalan):
  * Pada One Campus Sixty School: Tombol cepat "⭐ Jadikan Default (Waktu Berjalan)" muncul di samping badge status saat Super Admin membuka klaster non-default.
  * Label "(Berjalan)" kini terpasang secara dinamis pada klaster yang berstatus default di seluruh pill filter dan dropdown Sixty School.
  * Pada CRM Workspace: Tombol cepat "⭐ Jadikan Default" muncul di samping badge status saat Super Admin membuka klaster non-default.
  * Opsi checkbox "Langsung jadikan sebagai Klaster Default (Waktu PMB Berjalan)" saat membuat klaster baru.
  * Form pemilih langsung "Tetapkan Klaster Default / Waktu PMB Berjalan" di dalam dialog modal Super Admin.
  * Nilai default tersimpan secara persisten ke database `system_settings` (`crm_default_pmb_period` & `one_campus_default_pmb_period`).
- Form upload data leads Excel/CSV kini dilengkapi dropdown pilihan klaster masa PMB tujuan.
- Filter pencarian toolbar CRM Workspace dilengkapi filter dropdown Klaster Masa PMB.
- Export Excel CRM (`/crm/export-excel`) menyertakan kolom "Masa PMB".
- Kompatibilitas Dark Mode penuh untuk seluruh komponen klaster dan dialog modal.
- Paritas 100% konsisten antara CodeIgniter 4 dan Laravel.


Catatan File Penting yang Diubah/Ditambahkan
- app/Database/Migrations/2026-11-04-000004_AddPmbPeriodToCrmLeads.php
- app/Config/Routes.php
- app/Controllers/EventSuite.php
- app/Controllers/CrmController.php
- app/Controllers/GoogleCalendarController.php
- app/Models/EventModel.php
- app/Models/CrmLeadModel.php
- app/Models/GoogleCalendarEventModel.php
- app/Models/OneCampusOjtReportModel.php
- app/Models/OneCampusSchoolReportModel.php
- app/Models/OneCampusPmbStudentModel.php
- app/Models/SystemSettingModel.php
- app/Libraries/GoogleDriveUploadService.php
- app/Libraries/GoogleCalendarService.php
- app/Libraries/CalendarExcelExporter.php
- app/Libraries/GeminiDocumentService.php
- app/Database/Migrations/2026-11-04-000001_CreateOneCampusPmbStudents.php
- app/Views/layouts/dashboard.php
- app/Views/pages/dashboard.php
- app/Views/pages/one_campus.php
- app/Views/pages/one_campus_school_edit.php
- laravel/routes/web.php
- laravel/app/Controllers/EventSuite.php
- laravel/app/Models/OneCampusSchoolReportModel.php
- laravel/app/Models/OneCampusPmbStudentModel.php
- laravel/app/Models/SystemSettingModel.php
- laravel/app/Libraries/GoogleDriveUploadService.php
- laravel/resources/views/pages/dashboard.blade.php
- laravel/resources/views/pages/one_campus.blade.php
- laravel/resources/views/pages/one_campus_school_edit.blade.php
- app/Controllers/GoogleCalendarController.php
- app/Models/EventModel.php
- app/Models/CrmLeadModel.php
- app/Models/GoogleCalendarEventModel.php
- app/Models/OneCampusOjtReportModel.php
- app/Models/OneCampusSchoolReportModel.php
- app/Models/SystemSettingModel.php
- app/Libraries/GoogleDriveUploadService.php
- app/Libraries/GoogleCalendarService.php
- app/Libraries/CalendarExcelExporter.php
- app/Libraries/GeminiDocumentService.php
- app/Views/layouts/dashboard.php
- app/Views/pages/event_form.php
- app/Views/pages/events.php
- app/Views/pages/approvals.php
- app/Views/pages/dashboard.php
- app/Views/pages/calendar.php
- app/Views/pages/event_detail.php
- app/Views/pages/task_manager.php
- app/Views/pages/task_form.php
- app/Views/pages/reports.php
- app/Views/pages/analytics.php
- app/Views/pages/dashboard.php (Widget Rekomendasi AI Interaksi Sekolah di posisi paling bawah & full Dark Mode support)
- app/Views/pages/special_analytics.php
- app/Views/pages/one_campus.php
- app/Views/pages/one_campus_school_edit.php
- app/Views/pages/users.php
- app/Views/pages/documents.php
- app/Views/pages/generate_documents.php
- app/Views/pages/login.php
- app/Views/pages/register.php
- app/Views/pages/forgot_password.php
- app/Views/pages/reset_password.php
- app/Views/pages/crm_dashboard.php
- app/Views/pages/crm_agent_monitoring.php
- app/Views/pages/crm_login.php
- laravel/routes/web.php
- laravel/app/Controllers/EventSuite.php
- laravel/app/Models/OneCampusSchoolReportModel.php
- laravel/app/Models/SystemSettingModel.php
- laravel/app/Libraries/GoogleDriveUploadService.php
- laravel/resources/views/pages/one_campus.blade.php
- laravel/resources/views/pages/one_campus_school_edit.blade.php
- public/assets/css/app.css
- public/assets/js/app.js
- public/assets/img/eventhub-logo.png
- public/assets/img/eventhub-mark.png
- public/assets/img/taskbar-icon.png
- public/favicon.ico
- app/Database/Migrations/2026-06-01-000001_AddEventEndDateToEvents.php
- app/Database/Migrations/2026-06-03-000001_AddDmerRoleForSpecialAnalytics.php
- app/Database/Migrations/2026-06-04-000001_AddOjtMonthRangeToOneCampusOjtReports.php
- app/Database/Migrations/2026-06-17-000001_DedupeCalendarIntegrations.php
- app/Database/Migrations/2026-11-04-000005_CreateAccreditationDocuments.php
- app/Database/Migrations/2026-11-04-000006_CreateNationalSchools.php
- app/Models/NationalSchoolModel.php
- laravel/app/Models/NationalSchoolModel.php

- app/Models/AccreditationDocumentModel.php
- laravel/app/Models/AccreditationDocumentModel.php
- app/Views/pages/accreditation_documents.php
- laravel/resources/views/pages/accreditation_documents.blade.php

FASILITAS DOKUMEN AKREDITASI (BAN-PT / LAM-INFOKOM):
1. Admin (Admin Sistem, Super Admin, DMER):
   - Fitur Pencarian Cerdas dari Dokumen Event: Live search realtime pada modal penetapan akreditasi untuk mencari dokumen event berdasarkan judul, kode EVT, kampus, kategori, atau ketersediaan berkas (Proposal / LPJ).
   - Tombol Satu Klik "⭐ Jadikan Data Akreditasi": Tersedia tombol interaktif pada setiap baris hasil pencarian dokumen event untuk langsung memilih dan menetapkannya menjadi Dokumen Akreditasi.
   - Integrasi Halaman Dokumen Event (/documents): Menambahkan tombol "🎓 Jadikan Akreditasi" di setiap baris event dan status badge "🎓 Terdaftar Akreditasi" jika sudah didaftarkan, memudahkan Admin mempromosikan dokumen event ke akreditasi secara langsung dari halaman Dokumen Event.
   - Menentukan Kriteria/Standar Akreditasi (Kriteria 1-9), jenis dokumen wajib (all, report, proposal, assignment_letter), dan instruksi catatan untuk Kepala Kampus.
   - Filter komprehensif berdasarkan Kampus Pelaksana, Tahun Pelaksanaan, Status Kelengkapan, Kriteria Akreditasi, dan pencarian kata kunci.
   - Mereview data yang dilengkapi Kepala Kampus, memberikan persetujuan (Terverifikasi Lengkap) atau mengembalikan untuk revisi.
2. Kepala Kampus:
   - Terkunci otomatis sesuai cakupan kampus masing-masing (campus_id).
   - Melihat daftar dokumen event yang ditetapkan oleh Admin untuk akreditasi kampusnya beserta instruksi tim akreditasi.
   - Melengkapi bukti pendukung: unggah berkas fisik tambahan (PDF/DOC/ZIP hingga 25MB), tautan Google Drive / Cloud bukti kegiatan, dan catatan keterangan pemenuhan.
   - Mengirimkan berkas untuk verifikasi Admin.
3. Sinkronisasi & Dual Engine:
   - 100% Paritas antara CodeIgniter 4 (/documents/accreditation) dan Laravel (/documents/accreditation).


[UPDATE] Penyelarasan Dokumen Utama Event pada Dokumen Akreditasi:
- Kolom Dokumen Utama Event pada tabel Akreditasi kini 100% identik dengan Dokumen Event (/documents).
- Menampilkan kode dokumen (PRP/REP), status chip badge, nama file berkas/Drive asli, waktu unggah, dan nama PIC.
- Tautan berkas terhubung langsung ke file upload asli event atau Drive via events/{code}/documents/{type}/file.
- Dual-engine parity terjaga 100% pada CodeIgniter 4 dan Laravel.


[UPDATE] Database Satuan Pendidikan Nasional & Auto-Fill Rekomendasi NPSN (Sixty School):
- Telah mengimpor 42.856 data sekolah se-Indonesia dari berkas Data Induk Satuan Pendidikan (Kemendikdasmen/Kemenag) ke tabel national_schools.
- Mengetik atau memilih NPSN di form Sixty School (tambah & edit) akan otomatis mengisi Nama Sekolah, Kota, Jenis SKL (SMA/SMK/MA/Lainnya), dan Status SKL (Negeri/Swasta).
- Dilengkapi autocomplete dropdown saran real-time & dukungan penuh Dark Mode.
- Paritas penuh 100% pada CodeIgniter 4 dan Laravel via route /one-campus/schools/lookup-npsn.


================================================================================
10. DATABASE SATUAN PENDIDIKAN NASIONAL & AUTO-FILL REKOMENDASI NPSN (SIXTY SCHOOL)
================================================================================
A. Sumber Data & Struktur Database:
   - Berkas: 'Data Induk Satuan Pendidikan - DAFTAR Nasional 360 - kemendikdasmen,kemenag - dikmen - formal - ASC - 30 Oktober 2026.xlsx'
   - Total: 42.856 data satuan pendidikan jenjang Pendidikan Menengah (SMA, SMK, MA, SLB, dll.) di seluruh provinsi & kabupaten se-Indonesia.
   - Tabel database: 'national_schools' dengan kolom lengkap:
     id, npsn, name, school_type, education_type, school_status, stage, city, district, subdistrict, address, track, supervising_ministry, created_at, updated_at.
   - Indeks berkecepatan tinggi: idx_ns_npsn (npsn), idx_ns_name (name), idx_ns_city (city) untuk pencarian instan (< 15 ms).

B. Mekanisme Rekomendasi & Auto-Fill Cerdas:
   - Saat pengguna mengetik atau menempelkan (paste) 8 digit NPSN pada form Sixty School, sistem langsung mencocokkan data referensi secara otomatis.
   - Form secara otomatis mengisi:
     1. Nama Sekolah   -> Terisi nama lengkap resmi satuan pendidikan (contoh: 'SMA NEGERI 1 TAMBANG').
     2. Kota           -> Terisi nama kabupaten/kota lokasi sekolah (contoh: 'KAB. KAMPAR').
     3. Jenis Skl      -> Otomatis memilih opsi select yang sesuai ('SMA', 'SMK', 'MA', atau 'Lainnya').
     4. Status Skl     -> Otomatis memilih opsi select 'Negeri' atau 'Swasta'.
   - Efek Visual: Kolom input yang terisi memancarkan kilau hijau (glow feedback) disertai pesan konfirmasi:
     '✅ [Nama Sekolah] terisi otomatis dari Data Kemendikdasmen/Kemenag!'.

C. Dropdown Autocomplete Real-Time:
   - Pengguna cukup mengetik beberapa digit NPSN (mulai 3 digit) untuk melihat daftar rekomendasi sekolah.
   - Setiap saran menampilkan badge NPSN monospace, nama sekolah, badge jenis & status sekolah, serta lokasi kota & kecamatan.
   - Klik salah satu saran langsung mengisikan seluruh data form dalam 1 kali klik.
   - Pengguna juga dapat mengetik nama sekolah pada kolom Nama Sekolah untuk menemukan NPSN dan data lengkapnya.

D. Cakupan Halaman & Mode Tampilan:
   - Halaman Tambah Sekolah Sixty School (/one-campus/schools)
   - Halaman Khusus Edit Sekolah Sixty School (/one-campus/schools/{id}/edit)
   - Dukungan penuh Dark Mode responsif (warna dropdown kontras, batas rapi, dan mudah dibaca).

E. Dual-Engine Parity (CodeIgniter 4 & Laravel):
   - Endpoint API: GET /one-campus/schools/lookup-npsn?q={npsn_atau_nama}
   - Berjalan identik di CodeIgniter 4 (port 8080) dan Laravel (port 8081).
================================================================================

================================================================================
11. NOTIFIKASI EMAIL KE markom851@bsi.ac.id & LONCENG NOTIFIKASI (SIXTY SCHOOL)
================================================================================
A. Pengiriman Email Otomatis:
   - Alamat tujuan pengiriman: markom851@bsi.ac.id
   - Pemicu (Trigger): Penyimpanan data sekolah baru pada modul One Campus Sixty School.
   - Format Email: HTML Modern & Responsif + Plain Text Alternate Message.
   - Subjek Email: [One Campus Sixty School] Data Sekolah Baru: {Nama Sekolah} ({Kota})
   - Rincian Informasi yang Dikirimkan:
     * Informasi Sekolah: Nama Sekolah, NPSN, Kota/Kabupaten, Bentuk & Status Sekolah, Nama Kepala Sekolah, No. Telp & Email Sekolah.
     * Rincian Program & Kampus: Kampus Pengusul, Periode PMB, Status BJS, Target Mahasiswa PMB, Nama & No Kontak PIC Guru.
     * Informasi Penginput: Nama Penginput, Email Penginput, Waktu Pendaftaran (WIB).
     * Call to Action (CTA): Tombol langsung untuk membuka rincian data di EventHub.
   - Keamanan & Ketahanan (Fail-safe):
     * Dibungkus blok try-catch dengan pencatatan log error via log_message().
     * Mekanisme fallback otomatis ke PHP mail() lokal.
     * Kegagalan koneksi SMTP tidak mengganggu atau membatalkan proses penyimpanan data sekolah.

B. Integrasi Lonceng Notifikasi Sistem (In-App Notification):
   - Tabel Database: notifications (kolom: user_id, type='sixty-school', message, read_at, created_at, updated_at).
   - Penerima Notifikasi:
     * User ID 1 (Admin Sistem default).
     * Seluruh pengguna dengan role administratif (Admin Sistem, Super Admin, DMER, Admin).
     * Akun pengguna yang sedang melakukan penginputan data.
   - Indikator Lonceng Navbar:
     * Menampilkan counter badge merah menyala (pulse badge) di atas ikon lonceng pada navbar atas.
     * Counter otomatis memperhitungkan total notifikasi yang belum dibaca (read_at is null) milik user aktif dan notifikasi sistem umum.
     * Berjalan seragam di seluruh halaman aplikasi pada kedua engine (CI4 & Laravel).

C. Halaman Inbox Notifikasi (/notifications):
   - Badge tipe khusus 'Sixty School' berwarna biru-cyan cerah.
   - Penanda status 'Baru' berwarna merah untuk notifikasi yang belum dibaca.
   - Tombol 'Buka →' yang mengarahkan pengguna secara langsung ke halaman Sixty School (/one-campus/schools).
   - Tombol 'Tandai semua dibaca' yang memperbarui status read_at ke waktu sekarang.
   - Tampilan empty state yang ramah saat tidak ada notifikasi.
   - Dukungan penuh tema Gelap (Dark Mode) dan tema Terang (Light Mode).

D. Paritas Dual Engine (CodeIgniter 4 & Laravel):
   - CI4: app/Controllers/EventSuite.php, app/Views/layouts/dashboard.php, app/Views/pages/notifications.php.
   - Laravel: laravel/app/Controllers/EventSuite.php, laravel/resources/views/layouts/dashboard.blade.php, laravel/resources/views/components/navbar.blade.php, laravel/resources/views/pages/notifications.blade.php, laravel/app/Support/LegacyMailer.php.
================================================================================
