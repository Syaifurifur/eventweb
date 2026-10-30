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

    /**
     * Membersihkan teks dari emoji/icon dan format cetak tebal (bold asterisk *)
     * agar pesan terdengar natural, bersih, dan manusiawi.
     */
    public static function sanitizeNaturalText(string $text): string
    {
        // 1. Hapus tanda bintang formatting bold / italic (*teks* -> teks)
        $text = preg_replace('/\*+([^*]+)\*+/', '$1', $text);
        $text = str_replace('*', '', $text);

        // 2. Hapus seluruh icon / emoji dan simbol grafis
        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{200D}\x{20E3}\x{2B50}\x{231A}\x{23F0}\x{23E9}-\x{23EC}\x{2934}\x{2935}]/u';
        $text = preg_replace($emojiPattern, '', $text);

        // 3. Normalisasi spasi dan tanda baca
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
        $text = preg_replace('/ +([,.?!])/', '$1', $text);
        $text = preg_replace('/[ \t]+\n/', "\n", $text);
        $text = preg_replace('/(\n[ \t]*){3,}/', "\n\n", $text);

        return trim($text);
    }

    public function generateMessage(array $lead, string $intent = 'sapaan_awal', string $customPrompt = '', array $chatHistory = []): array
    {
        $studentName = trim((string) ($lead['student_name'] ?? 'Calon Mahasiswa'));
        $school = trim((string) ($lead['school_origin'] ?? 'Sekolah'));
        $campus = trim((string) ($lead['campus_name'] ?? 'UBSI'));
        $eventTitle = trim((string) ($lead['event_title'] ?? 'Kegiatan Kampus'));
        $stage = trim((string) ($lead['stage'] ?? 'Tahap 1'));
        $label = trim((string) ($lead['label'] ?? 'Belum dilabeli'));
        $reason = trim((string) ($lead['rejection_reason'] ?? $lead['notes'] ?? ''));
        $prospectStatus = trim((string) ($lead['prospect_status'] ?? ''));
        $prospectNote = match ($prospectStatus) {
            'priority' => 'Prioritas Tinggi (Siswa memiliki minat kuliah tinggi atau membutuhkan informasi beasiswa segera dari kuesioner. Utamakan ajakan pendaftaran langsung, kuota beasiswa, dan kemudahan pembayaran).',
            'nurture' => 'Follow-up Lanjutan (Siswa masih tahap penjajakan/mempertimbangkan. Gunakan pendekatan konsultatif ramah, edukatif, dan santai).',
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
Anda adalah asisten pesan WhatsApp untuk tim Admisi dan Marketing Komunikasi {$campus}.
Buatkan 3 variasi pesan WhatsApp dalam Bahasa Indonesia yang ramah, sopan, natural, mengalir santai seperti percakapan sehari-hari staf kampus/kakak admisi dengan calon mahasiswa:
- Nama Siswa: {$studentName}
- Asal Sekolah: {$school}
- Kampus: {$campus}
- Event yang Diikuti: {$eventTitle}
- Prioritas Prospek: {$prospectNote}
- Status CRM Saat Ini: {$stage} | Label: {$label}
- Catatan/Alasan Sebelumnya: {$reason}
- Tujuan Pesan (Intent): {$intent}
- Instruksi Tambahan: {$customPrompt}
- Riwayat Percakapan Terakhir:
{$historyText}

PANDUAN FORMAT DAN GAYA BAHASA (MUTLAK HARUS DIIKUTI):
1. TANPA ICON / EMOJI: Dilarang keras menggunakan icon, emotikon, atau emoji apapun di seluruh teks pesan.
2. TANPA BOLD: Dilarang keras menggunakan tanda bintang (*) atau format cetak tebal (bold). Tulis nama siswa, sekolah, kampus, dan seluruh kata dengan tulisan biasa yang bersih.
3. NATURAL DAN MENGALIR: Buat pesan terasa hangat, bersahabat, manusiawi, dan tidak kaku seperti template broadcast robotik.

Kembalikan HANYA format JSON murni tanpa markdown codeblock dengan struktur:
{
  "primary_message": "pesan utama terbaik siap kirim tanpa emoji dan tanpa bintang/bold",
  "variants": [
    {"title": "Ramah & Personal", "text": "pesan ramah mengalir natural tanpa emoji tanpa bold"},
    {"title": "Singkat & To-The-Point", "text": "pesan singkat langsung ke inti tanpa emoji tanpa bold"},
    {"title": "Fokus Beasiswa & Solusi", "text": "pesan fokus beasiswa dan solusi tanpa emoji tanpa bold"}
  ],
  "strategy_tip": "tips singkat 1 kalimat untuk agent saat menghubungi siswa ini (tanpa emoji)"
}
PROMPT;

                $raw = $this->callGemini($prompt, 900, 0.55);
                $decoded = $this->extractJsonObject($raw);
                if (is_array($decoded) && ! empty($decoded['primary_message'])) {
                    $rawVariants = is_array($decoded['variants'] ?? null) && $decoded['variants'] !== []
                        ? $decoded['variants']
                        : $fallbackVariants['variants'];

                    $cleanVariants = array_map(function ($item) {
                        return [
                            'title' => self::sanitizeNaturalText((string) ($item['title'] ?? 'Variasi')),
                            'text' => self::sanitizeNaturalText((string) ($item['text'] ?? '')),
                        ];
                    }, $rawVariants);

                    return [
                        'ok' => true,
                        'ai_source' => 'Gemini AI (' . $this->model() . ')',
                        'primary_message' => self::sanitizeNaturalText((string) $decoded['primary_message']),
                        'variants' => $cleanVariants,
                        'strategy_tip' => self::sanitizeNaturalText((string) ($decoded['strategy_tip'] ?? $fallbackVariants['strategy_tip'])),
                    ];
                }
            } catch (\Throwable) {
                // Fallback gracefully to smart contextual generator
            }
        }

        return [
            'ok' => true,
            'ai_source' => 'Smart Contextual AI Engine',
            'primary_message' => self::sanitizeNaturalText($fallbackVariants['primary_message']),
            'variants' => array_map(function ($item) {
                return [
                    'title' => self::sanitizeNaturalText((string) ($item['title'] ?? 'Variasi')),
                    'text' => self::sanitizeNaturalText((string) ($item['text'] ?? '')),
                ];
            }, $fallbackVariants['variants']),
            'strategy_tip' => self::sanitizeNaturalText($fallbackVariants['strategy_tip']),
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

ATURAN FORMAT:
- DILARANG menggunakan icon/emoji.
- DILARANG menggunakan tanda bintang (*) atau bold.
- Tulis saran balasan (suggested_reply) secara natural dan sopan.

Kembalikan HANYA JSON murni tanpa markdown:
{
  "recommended_stage": "Tahap 2",
  "recommended_label": "Minat/Tertarik",
  "recommended_reason": "Ringkasan jelas alasan/minat/kendala siswa (maks 120 karakter)",
  "sentiment": "Positif / Hangat / Ragu-ragu / Menolak",
  "analysis_summary": "Evaluasi singkat 1-2 kalimat tentang respons siswa dan peluang closing",
  "suggested_reply": "Pesan balasan WhatsApp terbaik yang siap dikirim agent sekarang tanpa icon dan tanpa bold"
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
                        'recommended_reason' => self::sanitizeNaturalText((string) ($decoded['recommended_reason'] ?? $heuristic['recommended_reason'])),
                        'sentiment' => self::sanitizeNaturalText((string) ($decoded['sentiment'] ?? $heuristic['sentiment'])),
                        'analysis_summary' => self::sanitizeNaturalText((string) ($decoded['analysis_summary'] ?? $heuristic['analysis_summary'])),
                        'suggested_reply' => self::sanitizeNaturalText((string) ($decoded['suggested_reply'] ?? $heuristic['suggested_reply'])),
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
                    ? "Prioritas 1 (Peluang Utama): Hubungi {$interested} siswa yang berminat dan {$hesitant} siswa yang masih ragu-ragu menggunakan template info beasiswa atau potongan gelombang."
                    : "Prioritas 1 (Inisiasi Tahap 1): Lakukan sapaan awal WhatsApp pada {$stage1} data baru Tahap 1 agar segera berpindah ke Tahap 2.",
                $unassigned > 0
                    ? "Prioritas 2 (Distribusi Beban): Terdapat {$unassigned} data belum memiliki agent. Gunakan tombol Auto Assign agar seluruh prospek langsung ditangani."
                    : "Prioritas 2 (Follow-up Lanjutan): Kirim pesan follow-up ke {$noResponse} prospek yang belum merespon pada jam senggang siswa sekolah (15.30 - 19.30 WIB).",
                "Prioritas 3 (Dokumentasi Chat): Gunakan tombol Simpan Chat Jadi Gambar Bukti CRM di ruang chat aplikasi agar seluruh percakapan tersimpan otomatis sebagai arsip.",
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

ATURAN FORMAT:
- DILARANG menggunakan icon/emoji.
- DILARANG menggunakan tanda bintang (*) atau bold.

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
                    $priorities = is_array($decoded['priorities'] ?? null) && $decoded['priorities'] !== []
                        ? array_values(array_map('strval', $decoded['priorities']))
                        : $fallback['priorities'];

                    return [
                        'ok' => true,
                        'ai_source' => 'Gemini AI (' . $this->model() . ')',
                        'headline' => self::sanitizeNaturalText((string) $decoded['headline']),
                        'priorities' => array_map([self::class, 'sanitizeNaturalText'], $priorities),
                        'custom_answer' => self::sanitizeNaturalText((string) ($decoded['custom_answer'] ?? $fallback['custom_answer'])),
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
        $schoolMention = $school !== '' && $school !== '-' ? " dari {$school}" : '';
        $eventMention = $eventTitle !== '' && $eventTitle !== '-' ? " di kegiatan {$eventTitle}" : '';

        switch ($intent) {
            case 'follow_up_minat':
                $primary = "Halo Kak {$studentName}{$schoolMention}, apa kabar? Terima kasih sudah antusias mengikuti informasi dari {$campus}{$eventMention}. Karena kemarin Kakak tertarik untuk lanjut kuliah, saat ini kuota pendaftaran gelombang berjalan masih dibuka dan ada potongan biaya pendidikan khusus. Kira-kira Kak {$studentName} berencana ambil program studi atau jurusan apa? Boleh sharing ya Kak, supaya bisa kami bantu proses pendaftarannya.";
                $tip = 'Siswa sudah menunjukkan minat. Arahkan langsung ke pemilihan jurusan dan panduan pengisian formulir PMB.';
                break;

            case 'atasi_ragu':
                $reasonNote = $reason !== '' ? " seputar {$reason}" : ' mengenai pilihan jurusan maupun rincian biaya kuliah';
                $primary = "Halo Kak {$studentName}{$schoolMention}, apa kabar? Menindaklanjuti obrolan kita sebelumnya{$reasonNote}, tim admisi {$campus} siap bantu carikan solusi terbaik buat Kakak. Di {$campus} tersedia skema cicilan biaya kuliah yang terjangkau, program beasiswa, serta pilihan kelas reguler maupun kelas karyawan. Kalau Kak {$studentName} ada yang masih ingin ditanyakan atau didiskusikan bersama orang tua, silakan kabari ya Kak.";
                $tip = 'Gunakan pendekatan empati dan konsultatif. Tawarkan rincian cicilan atau brosur digital untuk didiskusikan bersama orang tua.';
                break;

            case 'info_beasiswa':
                $primary = "Halo Kak {$studentName}{$schoolMention}, kabar baik dari {$campus}. Sebagai peserta{$eventMention}, Kak {$studentName} berkesempatan mendapatkan program beasiswa dan voucher potongan biaya pendidikan untuk pendaftaran mahasiswa baru tahun ini. Kuota voucher ini terbatas untuk periode pendaftaran minggu ini. Apakah Kakak ingin saya kirimkan info syarat dan cara klaim beasiswanya sekarang?";
                $tip = 'Program beasiswa atau potongan biaya sangat efektif untuk mengajak siswa yang ragu atau belum merespon.';
                break;

            case 'reengage':
                $primary = "Halo Kak {$studentName}{$schoolMention}, selamat siang. Semoga kegiatan Kak {$studentName} di sekolah selalu lancar ya. Saya dari tim informasi {$campus} ingin menyapa kembali setelah kegiatan{$eventMention} kemarin. Apakah Kakak sudah memiliki rencana pilihan jurusan setelah lulus nanti? Kalau Kakak berminat, saya bisa kirimkan brosur jurusan dan rincian beasiswa terbaru dari {$campus}.";
                $tip = 'Berikan pertanyaan santai dan mudah dijawab untuk membuka percakapan kembali.';
                break;

            case 'closing_daftar':
                $primary = "Halo Kak {$studentName}{$schoolMention}, langkah Kakak untuk bergabung di {$campus} sudah hampir selesai. Untuk mengamankan kuota jurusan dan potongan gelombang saat ini, berikut panduan singkat penyelesaian pendaftaran dan daftar ulangnya ya Kak. Jika bukti registrasi sudah ada, boleh langsung dikirimkan di sini agar bisa kami bantu verifikasi di sistem PMB {$campus}. Terima kasih banyak ya Kak.";
                $tip = 'Pastikan siswa mengirimkan konfirmasi pendaftaran atau bukti transfer untuk verifikasi data.';
                break;

            default:
                $customLine = $customPrompt !== '' ? "\n\nCatatan khusus: {$customPrompt}" : '';
                $primary = "Halo Kak {$studentName}{$schoolMention}, perkenalkan saya dari tim admisi {$campus}. Terima kasih banyak ya sudah berpartisipasi{$eventMention} bersama kami. Kami ingin berbagi informasi seputar peluang kuliah, pilihan jurusan favorit, serta jalur beasiswa PMB di {$campus}.{$customLine}\n\nSetelah lulus nanti, Kak {$studentName} paling tertarik di bidang atau jurusan apa? Boleh ngobrol santai sebentar ya Kak.";
                $tip = 'Sapaan awal yang menyebutkan nama siswa, sekolah, dan event membuat percakapan terasa personal dan bersahabat.';
                break;
        }

        $shortVariant = "Halo Kak {$studentName}{$schoolMention}, saya dari tim PMB {$campus}. Terima kasih sudah ikut{$eventMention} kemarin ya. Saat ini pendaftaran gelombang baru dan jalur beasiswa {$campus} sedang dibuka. Apakah Kakak berminat kalau saya kirimkan info jurusan dan rincian biayanya?";
        $scholarshipVariant = "Halo Kak {$studentName}{$schoolMention}, khusus untuk peserta{$eventMention}, {$campus} menyediakan jalur prioritas dan keringanan biaya kuliah. Boleh tahu jurusan impian Kak {$studentName} apa supaya bisa kami bantu cek kuota beasiswanya?";

        return [
            'primary_message' => self::sanitizeNaturalText($primary),
            'variants' => [
                ['title' => 'Ramah & Personal (Rekomendasi)', 'text' => self::sanitizeNaturalText($primary)],
                ['title' => 'Singkat & To-The-Point', 'text' => self::sanitizeNaturalText($shortVariant)],
                ['title' => 'Fokus Beasiswa & Prioritas Event', 'text' => self::sanitizeNaturalText($scholarshipVariant)],
            ],
            'strategy_tip' => self::sanitizeNaturalText($tip),
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
                'suggested_reply' => self::sanitizeNaturalText("Halo Kak {$studentName}, izin menyapa kembali dari tim PMB {$campus}. Apakah informasi kegiatan dan pendaftaran yang kami kirimkan sebelumnya sudah diterima Kak? Jika ada yang ingin ditanyakan seputar jurusan atau beasiswa, silakan kabari kami ya Kak."),
            ];
        }

        if (preg_match('/(sudah daftar|sudah bayar|bukti bayar|daftar ulang|lunas|sudah registrasi|nim saya|sudah transfer)/i', $lower)) {
            return [
                'ok' => true,
                'ai_source' => 'Smart Contextual AI Engine',
                'recommended_stage' => 'Tahap 3',
                'recommended_label' => 'Closing',
                'recommended_reason' => 'Siswa konfirmasi sudah melakukan pendaftaran atau pembayaran PMB.',
                'sentiment' => 'Sangat Positif (Closing)',
                'analysis_summary' => "Balasan siswa menunjukkan konfirmasi pendaftaran/pembayaran. Direkomendasikan mengubah status ke Tahap 3 - Closing dan menyimpan gambar chat sebagai bukti.",
                'suggested_reply' => self::sanitizeNaturalText("Alhamdulillah, terima kasih banyak Kak {$studentName}. Selamat bergabung menjadi bagian dari keluarga besar {$campus}. Data pendaftaran Kakak segera kami catat dan verifikasi. Jangan ragu hubungi kami jika butuh bantuan informasi perkuliahan ya Kak."),
            ];
        }

        if (preg_match('/(tidak minat|gak minat|nggak minat|sudah kuliah|kampus lain|sudah diterima|langsung kerja|tidak lanjut|maaf belum)/i', $lower)) {
            $reason = str_contains($lower, 'kerja')
                ? 'Memilih langsung bekerja setelah lulus sekolah'
                : (str_contains($lower, 'kampus lain') || str_contains($lower, 'diterima')
                    ? 'Sudah diterima atau mendaftar di perguruan tinggi lain'
                    : 'Belum berminat melanjutkan pendaftaran saat ini');

            return [
                'ok' => true,
                'ai_source' => 'Smart Contextual AI Engine',
                'recommended_stage' => 'Tahap 2',
                'recommended_label' => 'Menolak',
                'recommended_reason' => $reason,
                'sentiment' => 'Menolak / Negatif',
                'analysis_summary' => "Siswa menyampaikan penolakan ({$reason}). Catat alasan penolakan di kolom CRM dan berikan penutupan yang sopan.",
                'suggested_reply' => self::sanitizeNaturalText("Baik Kak {$studentName}, terima kasih banyak atas konfirmasinya ya. Semoga sukses selalu untuk langkah Kakak ke depan. Di {$campus} juga tersedia kelas karyawan sore atau malam yang fleksibel sambil bekerja. Jika sewaktu-waktu Kakak atau teman membutuhkan info kuliah, nomor ini selalu aktif ya Kak."),
            ];
        }

        if (preg_match('/(ragu|bingung|tanya orang tua|ortu|mahal|biaya|cicilan|pikir-pikir|nanti dulu|belum tahu)/i', $lower)) {
            $reason = str_contains($lower, 'biaya') || str_contains($lower, 'mahal') || str_contains($lower, 'cicil')
                ? 'Masih mempertimbangkan rincian biaya kuliah dan skema cicilan'
                : (str_contains($lower, 'ortu') || str_contains($lower, 'orang tua')
                    ? 'Menunggu diskusi dan persetujuan orang tua'
                    : 'Masih ragu memilih jurusan atau waktu pendaftaran');

            return [
                'ok' => true,
                'ai_source' => 'Smart Contextual AI Engine',
                'recommended_stage' => 'Tahap 2',
                'recommended_label' => 'Ragu-ragu',
                'recommended_reason' => $reason,
                'sentiment' => 'Hangat / Ragu-ragu',
                'analysis_summary' => "Siswa merespon tetapi memiliki keraguan ({$reason}). Berikan solusi skema pembayaran bertahap atau brosur resmi untuk didiskusikan bersama orang tua.",
                'suggested_reply' => self::sanitizeNaturalText("Siap Kak {$studentName}, silakan didiskusikan terlebih dahulu bersama orang tua ya. Sebagai bahan pertimbangan, di {$campus} biaya kuliahnya terjangkau, bisa dicicil per bulan, dan ada potongan khusus gelombang ini. Mau saya kirimkan tabel rincian biaya dan brosur digitalnya agar lebih mudah dibaca bersama orang tua, Kak?"),
            ];
        }

        return [
            'ok' => true,
            'ai_source' => 'Smart Contextual AI Engine',
            'recommended_stage' => $currentStage === 'Tahap 3' ? 'Tahap 3' : 'Tahap 2',
            'recommended_label' => 'Minat/Tertarik',
            'recommended_reason' => 'Siswa merespon positif dan menanyakan info pendaftaran/jurusan.',
            'sentiment' => 'Positif (Prospek Hangat)',
            'analysis_summary' => "Siswa memberikan respons positif terhadap informasi kampus. Segera arahkan ke pemilihan program studi dan panduan pendaftaran.",
            'suggested_reply' => self::sanitizeNaturalText("Terima kasih responsnya Kak {$studentName}. Senang sekali mendengar Kakak tertarik bergabung di {$campus}. Untuk langkah selanjutnya, Kakak bisa mendaftar secara online maupun dibantu langsung oleh tim kami agar mendapat potongan gelombang saat ini. Kira-kira Kakak ingin masuk di jurusan apa nih Kak?"),
        ];
    }

    private function callGemini(string $prompt, int $maxTokens = 800, float $temperature = 0.45): string
    {
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

        $url = rtrim($this->config->baseURL, '/') . '/models/' . rawurlencode($this->model()) . ':generateContent';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->config->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => (int) ($this->config->timeout ?? 20),
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($body === false || $error !== '' || $status < 200 || $status >= 300) {
            throw new \RuntimeException('Gemini API call failed: ' . ($error ?: 'HTTP ' . $status));
        }

        $decoded = json_decode((string) $body, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid Gemini response.');
        }

        $candidates = $decoded['candidates'] ?? [];
        $text = $candidates[0]['content']['parts'][0]['text'] ?? '';

        return trim((string) $text);
    }

    private function extractJsonObject(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $raw, $matches)) {
            $raw = $matches[1];
        } elseif (preg_match('/(\{.*\})/s', $raw, $matches)) {
            $raw = $matches[1];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }
}