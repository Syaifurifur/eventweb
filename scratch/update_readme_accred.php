<?php
$file = __DIR__ . '/../README.md';
$content = file_get_contents($file);

$target = "### 8. Fasilitas Dokumen Akreditasi Kampus (BAN-PT / LAM-INFOKOM)\n- **Penetapan Dokumen Event untuk Akreditasi oleh Admin**:";

$replacement = "### 8. Fasilitas Dokumen Akreditasi Kampus (BAN-PT / LAM-INFOKOM)
- **Fitur Mencari dari Dokumen Event & Tombol Jadikan Data Akreditasi**:
  - **Pencarian Cerdas Dokumen Event (Live Search & Filter)**: Di halaman Dokumen Akreditasi (`/documents/accreditation`), tombol **`🔍 Cari Dokumen Event & Jadikan Akreditasi`** membuka modal pencarian interaktif yang dilengkapi:
    - Input live search realtime untuk menyaring dokumen event berdasarkan judul, kode event (`EVT-...`), nama kampus, atau kategori event.
    - Filter dropdown kampus dan filter ketersediaan berkas (*Semua Event*, *Hanya yang Memiliki Proposal/LPJ*, *Hanya yang Belum Akreditasi*).
    - Menampilkan status ketersediaan berkas asli dokumen event: Proposal (`📄 Proposal ✓`), LPJ/Laporan (`📑 LPJ ✓`), dan Surat Tugas.
    - Pada setiap baris hasil pencarian dokumen event, tersedia tombol mencolok: **`⭐ Jadikan Data Akreditasi`** yang sekali klik langsung memilih event tersebut dan membuka form penetapan akreditasi (Kriteria 1-9, jenis berkas, instruksi ke Kepala Kampus).
  - **Tombol Satu Klik di Halaman Dokumen Event (`/documents`)**:
    - Pada tabel rekap seluruh dokumen event di halaman `/documents`, setiap baris event kini dilengkapi tombol aksi **`🎓 Jadikan Akreditasi`** khusus Admin.
    - Ketika diklik, dialog pop-up konfirmasi langsung muncul untuk menetapkan dokumen event tersebut menjadi data akreditasi tanpa perlu berpindah halaman.
    - Jika event sudah pernah didaftarkan ke akreditasi, sistem secara otomatis menampilkan badge informatif **`🎓 Terdaftar Akreditasi`** yang dapat diklik langsung menuju berkas akreditasi terkait.
- **Penetapan Dokumen Event untuk Akreditasi oleh Admin**:";

if (strpos($content, $target) !== false) {
    file_put_contents($file, str_replace($target, $replacement, $content));
    echo "README updated successfully.\n";
} else {
    // Check CRLF
    $targetCRLF = str_replace("\n", "\r\n", $target);
    $replacementCRLF = str_replace("\n", "\r\n", $replacement);
    if (strpos($content, $targetCRLF) !== false) {
        file_put_contents($file, str_replace($targetCRLF, $replacementCRLF, $content));
        echo "README updated successfully (CRLF).\n";
    } else {
        echo "Target not found.\n";
    }
}

