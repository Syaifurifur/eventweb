<?php

namespace App\Libraries;

use Config\Gemini;
use RuntimeException;

class GeminiDocumentService
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
        return $this->config->model;
    }

    public function generateDocument(string $documentType, array $event, string $template, array $structure = []): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GEMINI_API_KEY belum diisi di file .env.');
        }

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => 'Anda adalah seorang Event Planner Profesional dan Proposal Writer Universitas.'],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->prompt($documentType, $event, $template, $structure)],
                    ],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => 8192,
                'temperature' => 0.45,
            ],
        ];

        $url = $this->config->baseURL . '/models/' . rawurlencode($this->config->model) . ':generateContent';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->config->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $this->config->timeout,
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($body === false || $error !== '') {
            throw new RuntimeException('Gagal menghubungi Gemini API: ' . $error);
        }

        $decoded = json_decode($body, true);
        if ($status < 200 || $status >= 300) {
            $message = $decoded['error']['message'] ?? 'Gemini API mengembalikan status ' . $status . '.';
            throw new RuntimeException((string) $message);
        }

        $text = trim($this->extractText(is_array($decoded) ? $decoded : []));
        if ($text === '') {
            throw new RuntimeException('Gemini API tidak mengembalikan isi dokumen.');
        }

        return $text;
    }

    private function prompt(string $documentType, array $event, string $template, array $structure = []): string
    {
        $label = 'Proposal';
        $description = trim((string) ($event['description'] ?? ''));
        $autoFill = '[Data belum tersedia - buat otomatis secara realistis berdasarkan konteks acara]';
        $structureValue = static function (array $labels) use ($structure): string {
            foreach ($labels as $label) {
                foreach ($structure as $key => $value) {
                    if (strcasecmp((string) $key, $label) === 0) {
                        return trim((string) $value);
                    }
                }
            }

            return '';
        };
        $valueOrAuto = static fn ($value): string => trim((string) $value) !== '' ? trim((string) $value) : $autoFill;

        $campus = trim((string) ($event['campus_name'] ?? $event['campus'] ?? ''));
        $summary = $structureValue(['Ringkasan kegiatan']);
        $concept = $structureValue(['Konsep dan bentuk kegiatan']);
        $goals = $structureValue(['Tujuan kegiatan']);
        $targetParticipants = $structureValue(['Sasaran peserta']);
        $rundown = $structureValue(['Rencana kegiatan']);

        $eventDate = (string) ($event['event_date'] ?? $event['date'] ?? '');
        $eventEndDate = trim((string) ($event['event_end_date'] ?? $event['end_date'] ?? ''));
        $eventDateLabel = $eventDate;
        if ($eventDate !== '' && $eventEndDate !== '' && $eventEndDate !== $eventDate) {
            $eventDateLabel .= ' sampai ' . $eventEndDate;
        }

        $dataEvent = [
            'Nama Event' => $event['title'] ?? '',
            'Tema Event' => $event['category'] ?? '',
            'Tagline' => '',
            'Penyelenggara' => $campus !== '' ? $campus : 'Universitas Bina Sarana Informatika',
            'Lokasi' => $event['location'] ?? '',
            'Tanggal' => $eventDateLabel,
            'Target Peserta' => $targetParticipants,
            'Narasumber' => '',
            'Konsep Acara' => $concept !== '' ? $concept : ($summary !== '' ? $summary : $description),
            'Jumlah Peserta' => (string) ($event['participants_count'] ?? ''),
            'Tujuan Event' => $goals,
            'Rundown Acara' => $rundown,
            'Panitia' => '',
            'Estimasi Budget' => (string) ($event['budget'] ?? ''),
        ];

        $dataEventLines = [];
        foreach ($dataEvent as $key => $value) {
            $dataEventLines[] = $key . ': ' . $valueOrAuto($value);
        }

        $structureLines = [];
        foreach ($structure as $key => $value) {
            $structureLines[] = '- ' . $key . ': ' . $value;
        }

        $proposalPrompt = <<<'PROMPT'
Anda adalah seorang Event Planner Profesional dan Proposal Writer Universitas.

Tugas Anda adalah membuat proposal kegiatan lengkap dengan format formal kampus mengikuti struktur Proposal Seminar BSI Digination Universitas Bina Sarana Informatika.

Gunakan gaya bahasa:
- Formal
- Persuasif
- Profesional
- Rapih seperti proposal institusi pendidikan
- Tidak terlalu singkat
- Setiap BAB dijelaskan detail

FORMAT WAJIB:

HALAMAN COVER
- Judul Proposal
- Nama Event
- Tagline Event
- Tema Event
- Logo Kampus
- Nama Institusi
- Tahun

KATA PENGANTAR
- Awali dengan salam pembuka
- Jelaskan pentingnya tema kegiatan
- Jelaskan relevansi kegiatan dengan perkembangan teknologi/pendidikan/industri
- Tutup dengan harapan dan ucapan terima kasih

DAFTAR ISI

BAB I PENDAHULUAN
1.1 Latar Belakang
- Jelaskan kondisi saat ini
- Jelaskan masalah atau peluang
- Jelaskan alasan kegiatan dibuat
- Hubungkan dengan kebutuhan generasi muda/dunia industri/digital

1.2 Tujuan Kegiatan
Buat minimal 5 poin tujuan kegiatan

1.3 Manfaat Kegiatan
Pisahkan:
- Bagi Peserta
- Bagi Kampus
- Bagi Mitra/Sekolah

1.4 Indikator Keberhasilan
Buat indikator keberhasilan kegiatan yang terukur

BAB II PELAKSANAAN KEGIATAN

2.1 Nama dan Tema Kegiatan

2.2 Deskripsi Kegiatan
Jelaskan konsep acara secara lengkap

2.3 Penyelenggara Kegiatan

2.4 Pemateri atau Narasumber
Tuliskan profil singkat narasumber

2.5 Peserta Kegiatan
Tuliskan target peserta secara spesifik

2.6 Waktu Pelaksanaan
Format:
Hari:
Tanggal:
Tempat:
Waktu:

2.7 Susunan Acara
Buat tabel:
- Waktu
- Agenda
- Penanggungjawab

2.8 Susunan Panitia
Buat struktur panitia lengkap:
- Pelindung
- Penanggung Jawab
- Steering Committee
- Ketua Pelaksana
- Bendahara
- Sie Acara
- Multimedia
- Dokumentasi
- MC
- Moderator
- Registrasi
- Konsumsi
- LO
dll

2.9 Perencanaan Anggaran
Buat tabel anggaran profesional:
Kategori:
- Fee
- Konsumsi
- Perlengkapan
- Publikasi
- Dokumentasi
- Operasional

Gunakan format:
No | Uraian | Qty | Hari | Harga Satuan | Sub Total

Hitungkan GRAND TOTAL secara otomatis.

BAB III PENUTUP
- Ringkasan tujuan acara
- Harapan keberhasilan acara
- Ajakan kolaborasi
- Ucapan terima kasih

HALAMAN PENGESAHAN
Format:
Jakarta, [tanggal]

Mengetahui,
Steering Committee

Ketua Pelaksana

Menyetujui,
Pimpinan Kampus/Rektor

KETENTUAN OUTPUT:
- Buat proposal lengkap siap print
- Gunakan format profesional
- Gunakan heading BAB
- Rapih seperti proposal resmi kampus
- Isi harus realistis dan relevan
- Jika ada data kosong, bantu generate otomatis menggunakan AI berdasarkan konteks acara
PROMPT;

        return implode("\n", [
            $proposalPrompt,
            '',
            'ARAHAN SISTEM APLIKASI:',
            '- Jenis dokumen yang diminta: ' . $label . '.',
            '- User cukup mengisi Ringkasan kegiatan. Gunakan ringkasan tersebut untuk membuat seluruh struktur proposal secara otomatis.',
            '- Gunakan DATA EVENT sebagai sumber utama dan Ringkasan kegiatan sebagai penguat detail.',
            '- Untuk data kosong, isi otomatis secara wajar dari konteks event; jangan biarkan placeholder seperti [ISI] atau [tanggal].',
            '- Gunakan template sebagai referensi gaya dan format tambahan. Jika template bertentangan dengan FORMAT WAJIB, ikuti FORMAT WAJIB.',
            '- Gunakan markdown heading dengan pola: # untuk HALAMAN COVER/KATA PENGANTAR/DAFTAR ISI/BAB/HALAMAN PENGESAHAN, ## untuk subbab, dan ### untuk subbagian kecil.',
            '- Buat tabel markdown yang rapi untuk susunan acara dan anggaran.',
            '- Nominal anggaran harus realistis dan grand total harus sesuai dengan subtotal yang dibuat.',
            '- Kembalikan hanya isi dokumen final dalam format markdown sederhana, tanpa penjelasan tambahan.',
            '',
            'DATA EVENT:',
            implode("\n", $dataEventLines),
            '',
            'RINGKASAN KEGIATAN DARI USER:',
            $structureLines !== [] ? implode("\n", $structureLines) : '- Tidak ada ringkasan tambahan. Lengkapi dari data event dan deskripsi acara.',
            '',
            'TEMPLATE:',
            $template,
        ]);
    }

    private function extractText(array $response): string
    {
        $parts = [];
        foreach (($response['candidates'] ?? []) as $candidate) {
            foreach (($candidate['content']['parts'] ?? []) as $part) {
                if (isset($part['text'])) {
                    $parts[] = $part['text'];
                }
            }
        }

        return implode("\n", $parts);
    }
}
