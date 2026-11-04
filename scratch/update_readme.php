<?php
$content = file_get_contents(__DIR__ . '/../README.md');
$search = '## Pembaruan Skema Database (MySQL)';
$add = "### 8. Fasilitas Dokumen Akreditasi Kampus (BAN-PT / LAM-INFOKOM)
- **Penetapan Dokumen Event untuk Akreditasi oleh Admin**:
  - Admin (Admin Sistem, Super Admin, DMER) dapat memilih dan menetapkan dokumen event kegiatan kampus sebagai Dokumen Bukti Akreditasi via tombol **\"+ Pilih Dokumen Event untuk Akreditasi\"**.
  - Admin menentukan Kriteria Akreditasi (BAN-PT / LAM-INFOKOM Kriteria 1-9), judul berkas, jenis dokumen wajib (*all*, *report*, *proposal*, *assignment_letter*), serta instruksi khusus untuk Kepala Kampus.
- **Workflow Kelengkapan Data oleh Kepala Kampus**:
  - Kepala Kampus dibatasi secara ketat (*campus-scoped*) hanya dapat melihat dokumen akreditasi milik kampusnya masing-masing (`campus_id`).
  - Kepala Kampus melengkapi data pemenuhan: mengunggah berkas bukti fisik tambahan (PDF/Word/ZIP/Gambar hingga 25 MB), melampirkan tautan Google Drive / Cloud bukti kegiatan, serta mengisi catatan keterangan pemenuhan bukti.
  - Setelah lengkap, Kepala Kampus dapat mengubah status menjadi **\"Menunggu Verifikasi\"** untuk ditinjau oleh Admin.
- **Tinjauan & Verifikasi Admin**:
  - Admin dapat mereview bukti yang dikirimkan Kepala Kampus dan memutuskan: **\"Setujui & Tandai Terverifikasi Lengkap\"** atau **\"Kembalikan ke Kepala Kampus (Perlu Revisi)\"** beserta catatan evaluasi feedback.
- **Filter Bar Interaktif & Pencarian**:
  - Filter berdasarkan **Kampus Pelaksana** (seluruh kampus untuk Admin, terkunci pada kampus sendiri untuk Kepala Kampus).
  - Filter berdasarkan **Tahun Pelaksanaan** (dinamis dari seluruh tahun kegiatan event).
  - Filter berdasarkan **Status Kelengkapan** (*Menunggu Kelengkapan*, *Menunggu Verifikasi*, *Terverifikasi Lengkap*).
  - Filter berdasarkan **Kriteria / Standar Akreditasi** (Kriteria 1 - Kriteria 9).
  - Pencarian kata kunci live (`q`) pada judul event, kode event, nama kampus, dan catatan.
- **Dukungan Cetak Lembar Kendali**:
  - Tersedia tombol **\"Cetak Lembar Kendali\"** dengan CSS print-friendly untuk pelaporan audit instrumen akreditasi.
- **Dual-Engine Parity 100%**:
  - Berjalan identik pada CodeIgniter 4 (`/documents/accreditation`) dan Laravel (`/documents/accreditation`).

---\n\n";

if (strpos($content, $search) !== false) {
    $new = str_replace($search, $add . $search, $content);
    file_put_contents(__DIR__ . '/../README.md', $new);
    echo "README.md successfully updated.\n";
} else {
    echo "Search pattern not found in README.md.\n";
}

