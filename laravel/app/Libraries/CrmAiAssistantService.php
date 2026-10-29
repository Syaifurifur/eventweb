<?php

namespace App\Libraries;

use Config\Gemini;

class CrmAiAssistantService
{
    private Gemini $config;

    public function __construct(?Gemini $config = null)
    {
        $this->config = $config ?? new Gemini();
    }

    public function isConfigured(): bool
    {
        return trim($this->config->apiKey) !== '';
    }

    public function model(): string
    {
        return $this->config->model ?: 'gemini-2.5-flash';
    }

    public function generateMessage(array $lead, string $intent = 'sapaan_awal', string $customPrompt = '', array $chatHistory = []): array
    {
        $studentName = trim((string) ($lead['student_name'] ?? 'Calon Mahasiswa'));
        $school = trim((string) ($lead['school_origin'] ?? 'Sekolah Anda'));
        $campus = trim((string) ($lead['campus_name'] ?? 'Kampus UBSI'));
        $eventTitle = trim((string) ($lead['event_title'] ?? 'Kegiatan Kampus'));
        $stage = trim((string) ($lead['stage'] ?? 'Tahap 1'));
        $label = trim((string) ($lead['label'] ?? 'Belum dilabeli'));
        $reason = trim((string) ($lead['rejection_reason'] ?? $lead['notes'] ?? ''));
        $prospectStatus = trim((string) ($lead['prospect_status'] ?? ''));
        $prospectNote = match ($prospectStatus) {
            'priority' => '⭐ PRIORITAS TINGGI (Siswa memiliki niat kuliah tinggi/berencana kuliah tahun ini/membutuhkan info beasiswa segera dari kuesioner event. Utamakan ajakan pendaftaran langsung, kuota beasiswa, dan kemudahan pembayaran).',
            'nurture' => '🕒 FOLLOW-UP LANJUTAN (Siswa masih tahap penjajakan/mempertimbangkan. Gunakan pendekatan konsultatif ramah, edukatif, dan jangan terlalu mendesak).',
            default => 'Standard Follow-Up',
        };

        $fallbackVariants = $this->buildContextualMessageVariants($studentName, $school, $campus, $eventTitle, $stage, $label, $reason, $intent, $customPrompt);

        if ($this->isConfigured()) {
            try {
                $historyLines = [];
                foreach (array_slice($chatHistory, -6) as $msg) {
                    $role = ($msg['sender_type'] ?? 'agent') === 'student' ? 'Siswa' : 'Agent';
                    $historyLines[] = $role . ': ' . trim((string) ($msg['message'] ?? ''));
                }
                $historyText = $historyLines !== [] ? implode("\n", $historyLines) : 'Belum ada riwayat chat.';

                $prompt = <<<PROMPT
Anda adalah AI CRM WhatsApp Specialist untuk tim Admisi & Marketing Komunikasi (MARKOM) {$campus}.
Buatkan 3 variasi pesan WhatsApp dalam Bahasa Indonesia yang ramah, natural, sopan, persuasif, dan siap kirim ke calon mahasiswa berikut:
- Nama Siswa: {$studentName}
- Asal Sekolah: {$school}
- Kampus Tujuan: {$campus}
- Event yang Diikuti: {$eventTitle}
- Prioritas Prospek: {$prospectNote}
- Status CRM Saat Ini: {$stage} | Label: {$label}
- Catatan/Alasan Sebelumnya: {$reason}
- Tujuan Pesan (Intent): {$intent}
- Instruksi Tambahan User: {$customPrompt}
- Riwayat Percakapan Terakhir:
{$historyText}

Kembalikan HANYA format JSON murni tanpa markdown codeblock dengan struktur:
{
  "primary_message": "pesan utama terbaik siap kirim dengan emoji secukupnya",
  "variants": [
    {"title": "Ramah & Personal", "text": "..."},
    {"title": "Singkat & To-The-Point", "text": "..."},
    {"title": "Fokus Beasiswa & Solusi", "text": "..."}
  ],
  "strategy_tip": "tips singkat 1 kalimat untuk agent saat menghubungi siswa ini"
}
PROMPT;

                $raw = $this->callGemini($prompt, 900, 0.55);
                $decoded = $this->extractJsonObject($raw);
                if (is_array($decoded) && ! empty($decoded['primary_message'])) {
                    return [
                        'ok' => true,
                        'ai_source' => 'Gemini AI (' . $this->model() . ')',
                        'primary_message' => (string) $decoded['primary_message'],
                        'variants' => is_array($decoded['variants'] ?? null) && $decoded['variants'] !== []
                            ? $decoded['variants']
                            : $fallbackVariants['variants'],
                        'strategy_tip' => (string) ($decoded['strategy_tip'] ?? $fallbackVariants['strategy_tip']),
                    ];
                }
            } catch (\Throwable) {
                // Fallback gracefully to smart contextual generator
            }
        }

        return [
            'ok' => true,
            'ai_source' => 'Smart Contextual AI Engine',
            'primary_message' => $fallbackVariants['primary_message'],
            'variants' => $fallbackVariants['variants'],
            'strategy_tip' => $fallbackVariants['strategy_tip'],
        ];
    }

    public function analyzeConversation(array $lead, array $chatHistory = [], string $latestReply = ''): array
    {
        $studentName = trim((string) ($lead['student_name'] ?? 'Calon Mahasiswa'));
        $school = trim((string) ($lead['school_origin'] ?? '-'));
        $campus = trim((string) ($lead['campus_name'] ?? 'Kampus'));
        $currentStage = trim((string) ($lead['stage'] ?? 'Tahap 1'));
        $currentLabel = trim((string) ($lead['label'] ?? ''));

        $allTextParts = [];
        foreach ($chatHistory as $msg) {
            if (($msg['sender_type'] ?? '') === 'student') {
                $allTextParts[] = (string) ($msg['message'] ?? '');
            }
        }
        if (trim($latestReply) !== '') {
            $allTextParts[] = trim($latestReply);
        }
        $combinedStudentText = trim(implode(' | ', $allTextParts));

        $heuristic = $this->heuristicAnalyze($studentName, $school, $campus, $currentStage, $currentLabel, $combinedStudentText, $chatHistory);

        if ($this->isConfigured() && ($combinedStudentText !== '' || $chatHistory !== [])) {
            try {
                $historyLines = [];
                foreach (array_slice($chatHistory, -8) as $msg) {
                    $role = ($msg['sender_type'] ?? 'agent') === 'student' ? 'Siswa' : 'Agent';
                    $historyLines[] = $role . ': ' . trim((string) ($msg['message'] ?? ''));
                }
                if (trim($latestReply) !== '') {
                    $historyLines[] = 'Siswa (Terbaru): ' . trim($latestReply);
                }
                $transcript = implode("\n", $historyLines);

                $prompt = <<<PROMPT
Analisis percakapan WhatsApp CRM penerimaan mahasiswa baru berikut untuk siswa {$studentName} ({$school}) di {$campus}:
{$transcript}

Tentukan rekomendasi pembaruan status CRM yang paling akurat. Pilihan yang valid:
- recommended_stage: "Tahap 1", "Tahap 2", atau "Tahap 3"
- recommended_label: "Closing", "Minat/Tertarik", "Ragu-ragu", "Menolak", atau "Tidak Merespon"

Kembalikan HANYA JSON murni tanpa markdown:
{
  "recommended_stage": "Tahap 2",
  "recommended_label": "Minat/Tertarik",
  "recommended_reason": "Ringkasan jelas alasan/minat/kendala siswa (maks 120 karakter)",
  "sentiment": "Positif / Hangat / Ragu-ragu / Menolak",
  "analysis_summary": "Evaluasi singkat 1-2 kalimat tentang respons siswa dan peluang closing",
  "suggested_reply": "Pesan balasan WhatsApp terbaik yang siap dikirim agent sekarang"
}
PROMPT;

                $raw = $this->callGemini($prompt, 700, 0.35);
                $decoded = $this->extractJsonObject($raw);
                if (is_array($decoded) && ! empty($decoded['recommended_stage'])) {
                    $validStages = ['Tahap 1', 'Tahap 2', 'Tahap 3'];
                    $validLabels = ['Closing', 'Minat/Tertarik', 'Ragu-ragu', 'Menolak', 'Tidak Merespon'];
                    $recStage = in_array($decoded['recommended_stage'] ?? '', $validStages, true)
                        ? $decoded['recommended_stage']
                        : $heuristic['recommended_stage'];
                    $recLabel = in_array($decoded['recommended_label'] ?? '', $validLabels, true)
                        ? $decoded['recommended_label']
                        : $heuristic['recommended_label'];
                    if ($recLabel === 'Closing') {
                        $recStage = 'Tahap 3';
                    }

                    return [
                        'ok' => true,
                        'ai_source' => 'Gemini AI (' . $this->model() . ')',
                        'recommended_stage' => $recStage,
                        'recommended_label' => $recLabel,
                        'recommended_reason' => (string) ($decoded['recommended_reason'] ?? $heuristic['recommended_reason']),
                        'sentiment' => (string) ($decoded['sentiment'] ?? $heuristic['sentiment']),
                        'analysis_summary' => (string) ($decoded['analysis_summary'] ?? $heuristic['analysis_summary']),
                        'suggested_reply' => (string) ($decoded['suggested_reply'] ?? $heuristic['suggested_reply']),
                    ];
                }
            } catch (\Throwable) {
                // Use heuristic fallback
            }
        }

        return $heuristic;
    }

    public function generateWorkspaceInsight(array $stats, string $customQuestion = ''): array
    {
        $total = (int) ($stats['total'] ?? 0);
        $unassigned = (int) ($stats['unassigned'] ?? 0);
        $stage1 = (int) ($stats['stage1'] ?? 0);
        $stage2 = (int) ($stats['stage2'] ?? 0);
        $stage3 = (int) ($stats['stage3'] ?? 0);
        $closing = (int) ($stats['closing'] ?? 0);
        $interested = (int) ($stats['interested'] ?? 0);
        $hesitant = (int) ($stats['hesitant'] ?? 0);
        $rejected = (int) ($stats['rejected'] ?? 0);
        $noResponse = (int) ($stats['no_response'] ?? 0);

        $warmCount = $interested + $hesitant;
        $convRate = $total > 0 ? round(($closing / $total) * 100, 1) : 0.0;

        $fallback = [
            'ok' => true,
            'ai_source' => 'Smart Contextual AI Engine',
            'headline' => "Total {$total} Leads aktif (Konversi Closing {$convRate}%). Fokus utama hari ini: eksekusi {$warmCount} Prospek Hangat (Minat & Ragu-ragu) dan percepat sapaan pada {$stage1} data Tahap 1.",
            'priorities' => [
                $warmCount > 0
                    ? "Prioritas 1 (Quick Wins): Hubungi {$interested} siswa 'Minat/Tertarik' & {$hesitant} siswa 'Ragu-ragu' menggunakan fitur Chat WA & AI dengan template Info Beasiswa / Diskon Gelombang."
                    : "Prioritas 1 (Inisiasi Tahap 1): Lakukan sapaan awal WhatsApp pada {$stage1} data Fresh Tahap 1 agar segera berpindah ke Tahap 2.",
                $unassigned > 0
                    ? "Prioritas 2 (Distribusi Beban): Terdapat {$unassigned} data belum memiliki agent. Gunakan tombol 'Auto Assign' agar seluruh prospek langsung ditangani."
                    : "Prioritas 2 (Re-engagement): Kirim pesan follow-up ke {$noResponse} prospek 'Tidak Merespon' pada jam prime-time (15.30 - 19.30 WIB).",
                "Prioritas 3 (Dokumentasi Bukti Chat): Gunakan tombol 'Simpan Chat Jadi Gambar Bukti CRM' di ruang chat aplikasi agar seluruh percakapan terdokumentasi otomatis sebagai gambar.",
            ],
            'custom_answer' => $customQuestion !== ''
                ? "Untuk pertanyaan Anda (\"{$customQuestion}\"): Fokuskan pendekatan personal berbasis asal sekolah dan event yang pernah diikuti siswa, tawarkan konsultasi jurusan gratis via WA, lalu simpan bukti tangkapan chat langsung dari panel Workspace."
                : '',
        ];

        if ($this->isConfigured()) {
            try {
                $prompt = <<<PROMPT
Anda adalah AI Coach & Strategist CRM Admisi Perguruan Tinggi.
Berikut adalah statistik antrean CRM saat ini:
- Total Leads: {$total}
- Belum Assign: {$unassigned}
- Tahap 1 (Fresh): {$stage1} | Tahap 2 (Respon): {$stage2} | Tahap 3 (Lanjut): {$stage3}
- Closing: {$closing} ({$convRate}%) | Minat/Tertarik: {$interested} | Ragu-ragu: {$hesitant} | Menolak: {$rejected} | Tidak Merespon: {$noResponse}
- Pertanyaan Spesifik User: {$customQuestion}

Kembalikan HANYA JSON murni tanpa markdown:
{
  "headline": "Ringkasan analisis taktis 1-2 kalimat berdasarkan angka di atas",
  "priorities": [
    "Langkah prioritas 1 yang konkret",
    "Langkah prioritas 2 yang konkret",
    "Langkah prioritas 3 yang konkret"
  ],
  "custom_answer": "Jawaban taktis atas pertanyaan spesifik user (kosongkan jika tidak ada pertanyaan)"
}
PROMPT;

                $raw = $this->callGemini($prompt, 700, 0.45);
                $decoded = $this->extractJsonObject($raw);
                if (is_array($decoded) && ! empty($decoded['headline'])) {
                    return [
                        'ok' => true,
                        'ai_source' => 'Gemini AI (' . $this->model() . ')',
                        'headline' => (string) $decoded['headline'],
                        'priorities' => is_array($decoded['priorities'] ?? null) && $decoded['priorities'] !== []
                            ? array_values(array_map('strval', $decoded['priorities']))
                            : $fallback['priorities'],
                        'custom_answer' => (string) ($decoded['custom_answer'] ?? $fallback['custom_answer']),
                    ];
                }
            } catch (\Throwable) {
                // Fallback
            }
        }

        return $fallback;
    }

    private function buildContextualMessageVariants(
        string $studentName,
        string $school,
        string $campus,
        string $eventTitle,
        string $stage,
        string $label,
        string $reason,
        string $intent,
        string $customPrompt
    ): array {
        $schoolMention = $school !== '' && $school !== '-' ? " dari *{$school}*" : '';
        $eventMention = $eventTitle !== '' && $eventTitle !== '-' ? " pada kegiatan *{$eventTitle}*" : '';

        switch ($intent) {
            case 'follow_up_minat':
                $primary = "Halo Kak *{$studentName}*{$schoolMention} 👋😊\n\nTerima kasih sudah antusias mengikuti informasi dari *{$campus}*{$eventMention}. Karena Kakak kemarin sudah tertarik untuk lanjut, saat ini kuota pendaftaran gelombang berjalan masih dibuka dan ada potongan biaya pendidikan khusus lho! 🎓✨\n\nKira-kira Kak {$studentName} ingin ambil program studi/jurusan apa nih? Yuk kita bantu proses pendaftarannya dari sekarang 😊";
                $tip = 'Siswa sudah menunjukkan minat; arahkan langsung ke pemilihan jurusan dan langkah pengisian formulir PMB.';
                break;

            case 'atasi_ragu':
                $reasonNote = $reason !== '' ? " terkait *{$reason}*" : ' soal pilihan jurusan maupun rincian biaya kuliah';
                $primary = "Halo Kak *{$studentName}*{$schoolMention} 👋\n\nApa kabar Kak? Menindaklanjuti obrolan kita sebelumnya{$reasonNote}, tim admisi *{$campus}* siap banget bantu kasih solusi terbaik buat Kakak 😊💡\n\nDi {$campus} tersedia skema cicilan biaya kuliah yang sangat ringan, jalur beasiswa, serta pilihan kelas Reguler maupun Karyawan. Kalau Kak {$studentName} ada yang masih ingin ditanyakan atau didiskusikan bareng orang tua, boleh banget balas pesan ini ya Kak! 🙏";
                $tip = 'Gunakan pendekatan empati & konsultatif; tawarkan rincian cicilan atau brosur digital untuk meyakinkan orang tua.';
                break;

            case 'info_beasiswa':
                $primary = "Halo Kak *{$studentName}*{$schoolMention}! 🌟🎓\n\nKabar gembira dari *{$campus}*! Sebagai peserta{$eventMention}, Kak {$studentName} berkesempatan mendapatkan *Program Beasiswa & Voucher Potongan Dana Pengembangan Pendidikan* untuk pendaftaran mahasiswa baru tahun ini.\n\nKuota voucher terbatas untuk pendaftar minggu ini. Mau saya kirimkan info syarat dan cara klaim beasiswanya sekarang, Kak? 😊";
                $tip = 'Program beasiswa/potongan biaya sangat efektif untuk mengubah status Ragu-ragu atau Tidak Merespon menjadi Minat.';
                break;

            case 'reengage':
                $primary = "Halo Kak *{$studentName}*{$schoolMention}, selamat siang 👋😊\n\nSemoga Kak {$studentName} sehat dan lancar selalu kegiatannya di sekolah ya! Saya dari tim informasi *{$campus}* ingin menyapa kembali setelah kegiatan{$eventMention} kemarin.\n\nApakah Kakak sudah punya rencana lanjut kuliah di jurusan apa setelah lulus nanti? Balas *\"INFO\"* aja ya Kak kalau mau saya kirimkan e-brosur jurusan & rincian beasiswa terbaru dari {$campus} 🙌";
                $tip = 'Berikan Call-to-Action (CTA) yang sangat mudah dijawab (misal balas 1 kata) untuk memancing respons pertama.';
                break;

            case 'closing_daftar':
                $primary = "Selamat Kak *{$studentName}*{$schoolMention}! 🎉🎓\n\nLangkah Kakak untuk bergabung di *{$campus}* sudah hampir selesai. Untuk mengamankan kuota jurusan dan potongan gelombang saat ini, berikut panduan singkat penyelesaian pendaftaran / daftar ulangnya ya Kak.\n\nJika bukti registrasi sudah siap, boleh langsung dikirimkan di chat ini supaya langsung kami verifikasi di sistem PMB {$campus}. Terima kasih Kak! 🙏✨";
                $tip = 'Pastikan siswa mengirimkan bukti daftar/pembayaran, lalu simpan percakapan ini sebagai gambar bukti Closing.';
                break;

            default:
                $customLine = $customPrompt !== '' ? "\n\nCatatan khusus untuk Kakak: {$customPrompt}" : '';
                $primary = "Halo Kak *{$studentName}*{$schoolMention}! 👋😊\n\nPerkenalkan saya dari tim Admisi *{$campus}*. Terima kasih banyak ya sudah berpartisipasi{$eventMention} bersama kami! 🙌\n\nKami ingin berbagi informasi seputar peluang kuliah, pilihan jurusan favorit, serta jalur Beasiswa PMB di *{$campus}*.{$customLine}\n\nNgomong-ngomong, setelah lulus nanti Kak {$studentName} paling tertarik di bidang atau jurusan apa nih? Yuk sharing sebentar Kak! 😊";
                $tip = 'Sapaan awal yang menyebutkan nama siswa, asal sekolah, dan nama event meningkatkan rasio balasan hingga 3x lipat.';
                break;
        }

        $shortVariant = "Halo Kak *{$studentName}*{$schoolMention} 👋 Saya dari tim PMB *{$campus}*. Terima kasih sudah ikut{$eventMention}! Saat ini pendaftaran gelombang spesial & jalur beasiswa {$campus} sedang dibuka. Apakah Kakak berminat jika saya kirimkan info jurusan & rincian biayanya? 😊";
        $scholarshipVariant = "Halo Kak *{$studentName}*{$schoolMention} 🎓✨ Spesial untuk alumni peserta{$eventMention}, *{$campus}* menyediakan jalur prioritas & keringanan biaya masuk kuliah. Boleh tahu jurusan impian Kak {$studentName} apa supaya saya cek kuota beasiswanya? 🙏";

        return [
            'primary_message' => $primary,
            'variants' => [
                ['title' => 'Ramah & Personal (Rekomendasi)', 'text' => $primary],
                ['title' => 'Singkat & To-The-Point', 'text' => $shortVariant],
                ['title' => 'Fokus Beasiswa & Prioritas Event', 'text' => $scholarshipVariant],
            ],
            'strategy_tip' => $tip,
        ];
    }

    private function heuristicAnalyze(
        string $studentName,
        string $school,
        string $campus,
        string $currentStage,
        string $currentLabel,
        string $studentText,
        array $chatHistory
    ): array {
        $lower = strtolower($studentText);

        if ($studentText === '') {
            return [
                'ok' => true,
                'ai_source' => 'Smart Contextual AI Engine',
                'recommended_stage' => $currentStage ?: 'Tahap 1',
                'recommended_label' => $currentLabel ?: 'Tidak Merespon',
                'recommended_reason' => 'Belum ada balasan dari siswa di ruang chat.',
                'sentiment' => 'Belum Merespon',
                'analysis_summary' => "Siswa {$studentName} belum tercatat mengirimkan balasan di ruang chat. Kirim pesan sapaan atau re-engagement untuk memancing respons.",
                'suggested_reply' => "Halo Kak *{$studentName}*, izin menyapa kembali dari tim PMB *{$campus}* 😊 Apakah informasi kegiatan dan pendaftaran yang kami kirimkan sebelumnya sudah diterima Kak? Jika ada yang ingin ditanyakan seputar jurusan atau beasiswa, silakan balas pesan ini ya Kak 🙏",
            ];
        }

        if (preg_match('/(sudah daftar|sudah bayar|bukti bayar|daftar ulang|lunas|sudah registrasi|nim saya|sudah transfer)/i', $lower)) {
            return [
                'ok' => true,
                'ai_source' => 'Smart Contextual AI Engine',
                'recommended_stage' => 'Tahap 3',
                'recommended_label' => 'Closing',
                'recommended_reason' => 'Siswa konfirmasi sudah melakukan pendaftaran/pembayaran PMB.',
                'sentiment' => 'Sangat Positif (Closing)',
                'analysis_summary' => "Balasan siswa menunjukkan konfirmasi pendaftaran/pembayaran. Direkomendasikan mengubah status ke Tahap 3 - Closing dan menyimpan gambar chat sebagai bukti.",
                'suggested_reply' => "Alhamdulillah, terima kasih banyak Kak *{$studentName}*! 🎉 Selamat bergabung menjadi bagian dari keluarga besar *{$campus}*. Data pendaftaran Kakak segera kami catat dan verifikasi ya. Jangan ragu hubungi kami jika butuh bantuan jadwal orientasi/perkuliahan! 🙏✨",
            ];
        }

        if (preg_match('/(tidak minat|gak minat|nggak minat|sudah kuliah|kampus lain|sudah diterima|langsung kerja|tidak lanjut|maaf belum)/i', $lower)) {
            $reason = str_contains($lower, 'kerja')
                ? 'Memilih langsung bekerja setelah lulus sekolah'
                : (str_contains($lower, 'kampus lain') || str_contains($lower, 'diterima')
                    ? 'Sudah diterima / mendaftar di perguruan tinggi lain'
                    : 'Belum berminat melanjutkan pendaftaran saat ini');

            return [
                'ok' => true,
                'ai_source' => 'Smart Contextual AI Engine',
                'recommended_stage' => 'Tahap 2',
                'recommended_label' => 'Menolak',
                'recommended_reason' => $reason,
                'sentiment' => 'Menolak / Negatif',
                'analysis_summary' => "Siswa menyampaikan penolakan ({$reason}). Catat alasan penolakan di kolom CRM dan berikan penutupan yang sopan (atau tawarkan kelas karyawan jika memilih bekerja).",
                'suggested_reply' => "Baik Kak *{$studentName}*, terima kasih banyak atas konfirmasinya ya 🙏 Semoga sukses selalu untuk langkah Kakak ke depannya! Sebagai informasi, di *{$campus}* juga tersedia *Kelas Karyawan / Sore-Malam* yang fleksibel sambil bekerja. Jika sewaktu-waktu Kakak atau teman membutuhkan info kuliah, nomor ini selalu aktif ya Kak 😊",
            ];
        }

        if (preg_match('/(ragu|bingung|tanya orang tua|ortu|mahal|biaya|cicilan|pikir-pikir|nanti dulu|belum tahu)/i', $lower)) {
            $reason = str_contains($lower, 'biaya') || str_contains($lower, 'mahal') || str_contains($lower, 'cicil')
                ? 'Masih mempertimbangkan rincian biaya kuliah & skema cicilan'
                : (str_contains($lower, 'ortu') || str_contains($lower, 'orang tua')
                    ? 'Menunggu diskusi dan persetujuan orang tua'
                    : 'Masih ragu memilih jurusan / waktu pendaftaran');

            return [
                'ok' => true,
                'ai_source' => 'Smart Contextual AI Engine',
                'recommended_stage' => 'Tahap 2',
                'recommended_label' => 'Ragu-ragu',
                'recommended_reason' => $reason,
                'sentiment' => 'Hangat / Ragu-ragu',
                'analysis_summary' => "Siswa merespon tetapi memiliki keraguan ({$reason}). Berikan solusi skema pembayaran bertahap atau brosur resmi untuk didiskusikan bersama orang tua.",
                'suggested_reply' => "Siap Kak *{$studentName}*, tidak apa-apa banget untuk didiskusikan dulu bersama orang tua 😊 Sebagai bahan pertimbangan, di *{$campus}* biaya kuliahnya sangat terjangkau, bisa dicicil bulanan, dan ada potongan khusus gelombang ini. Mau saya kirimkan rincian tabel biaya & brosur PDF-nya supaya mudah ditunjukkan ke orang tua, Kak? 🙏",
            ];
        }

        return [
            'ok' => true,
            'ai_source' => 'Smart Contextual AI Engine',
            'recommended_stage' => $currentStage === 'Tahap 3' ? 'Tahap 3' : 'Tahap 2',
            'recommended_label' => 'Minat/Tertarik',
            'recommended_reason' => 'Siswa merespon positif dan menanyakan info pendaftaran/jurusan.',
            'sentiment' => 'Positif (Prospek Hangat)',
            'analysis_summary' => "Siswa memberikan respons positif terhadap informasi kampus. Segera arahkan ke pemilihan program studi dan panduan link/formulir pendaftaran.",
            'suggested_reply' => "Terima kasih responsnya Kak *{$studentName}*! 😊 Senang sekali mendengar Kakak tertarik bergabung di *{$campus}*. Untuk langkah selanjutnya, Kakak bisa mendaftar secara online maupun dibantu langsung oleh tim kami agar mendapat potongan gelombang saat ini. Kira-kira Kakak ingin masuk di jurusan apa dan kelas Reguler Pagi atau Sore nih Kak? 🎓✨",
        ];
    }

    private function callGemini(string $prompt, int $maxTokens = 800, float $temperature = 0.45): string
    {
        $url = rtrim($this->config->baseURL, '/') . '/models/' . rawurlencode($this->model()) . ':generateContent';
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
                'temperature' => $temperature,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->config->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 18,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($body === false || $error !== '' || $status < 200 || $status >= 300) {
            throw new \RuntimeException('Gemini request failed: ' . ($error ?: 'HTTP ' . $status));
        }

        $decoded = json_decode((string) $body, true);
        $parts = $decoded['candidates'][0]['content']['parts'] ?? [];
        $text = '';
        foreach ($parts as $part) {
            $text .= (string) ($part['text'] ?? '');
        }

        return trim($text);
    }

    private function extractJsonObject(string $raw): ?array
    {
        $clean = trim($raw);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s*```$/', '', $clean) ?? $clean;

        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/', $clean, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}

