<?php

$dirs = [
    'c:/xampp/htdocs/eventweb/public/assets/img/templates',
    'c:/xampp/htdocs/eventweb/laravel/public/assets/img/templates',
];

foreach ($dirs as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0777, true);
    }
}

$templates = [
    'proposal_sosialisasi.png' => [
        'title' => 'PROPOSAL KEGIATAN',
        'sub' => 'Sosialisasi PMB & Kunjungan Sekolah',
        'badge' => 'Sosialisasi',
        'accent' => '#2563eb',
        'accent2' => '#f59e0b',
    ],
    'proposal_seminar.png' => [
        'title' => 'PROPOSAL KEGIATAN',
        'sub' => 'Seminar & Workshop Nasional',
        'badge' => 'Seminar',
        'accent' => '#7c3aed',
        'accent2' => '#3b82f6',
    ],
    'proposal_lomba.png' => [
        'title' => 'PROPOSAL KEGIATAN',
        'sub' => 'Kompetisi, Lomba & Festival',
        'badge' => 'Lomba',
        'accent' => '#059669',
        'accent2' => '#10b981',
    ],
    'proposal_lainnya.png' => [
        'title' => 'PROPOSAL KEGIATAN',
        'sub' => 'Template Proposal Umum Event',
        'badge' => 'Umum',
        'accent' => '#64748b',
        'accent2' => '#94a3b8',
    ],
    'report_sosialisasi.png' => [
        'title' => 'LAPORAN KEGIATAN',
        'sub' => 'LPJ Sosialisasi PMB & Sekolah',
        'badge' => 'Laporan Sosialisasi',
        'accent' => '#2563eb',
        'accent2' => '#0284c7',
    ],
    'report_seminar.png' => [
        'title' => 'LAPORAN KEGIATAN',
        'sub' => 'LPJ Seminar & Workshop Kampus',
        'badge' => 'Laporan Seminar',
        'accent' => '#7c3aed',
        'accent2' => '#6366f1',
    ],
    'report_lomba.png' => [
        'title' => 'LAPORAN KEGIATAN',
        'sub' => 'LPJ Kompetisi & Kejuaraan',
        'badge' => 'Laporan Lomba',
        'accent' => '#059669',
        'accent2' => '#14b8a6',
    ],
    'report_lainnya.png' => [
        'title' => 'LAPORAN KEGIATAN',
        'sub' => 'LPJ Umum Pertanggungjawaban Event',
        'badge' => 'Laporan Umum',
        'accent' => '#475569',
        'accent2' => '#64748b',
    ],
];

foreach ($templates as $file => $cfg) {
    // Generate an SVG file
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500" width="400" height="500">
  <defs>
    <linearGradient id="grad_{$cfg['badge']}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$cfg['accent']}"/>
      <stop offset="100%" stop-color="{$cfg['accent2']}"/>
    </linearGradient>
    <filter id="shadow" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.12"/>
    </filter>
  </defs>
  
  <!-- Page background -->
  <rect width="400" height="500" fill="#ffffff" rx="12"/>
  
  <!-- Top decorative curves -->
  <path d="M 0,0 L 400,0 L 400,120 Q 250,180 0,90 Z" fill="url(#grad_{$cfg['badge']})" opacity="0.95"/>
  <path d="M 0,0 L 260,0 Q 140,160 0,140 Z" fill="#ffffff" opacity="0.18"/>
  
  <!-- Logo / Brand header -->
  <rect x="250" y="24" width="124" height="28" rx="6" fill="#ffffff" filter="url(#shadow)"/>
  <text x="312" y="42" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="11" font-weight="900" fill="#0f172a" text-anchor="middle">BSI GROUP</text>
  <text x="312" y="49" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="6.5" font-weight="600" fill="#2563eb" text-anchor="middle">Kampus Digital Kreatif</text>
  
  <!-- Badge -->
  <rect x="32" y="160" width="95" height="22" rx="11" fill="#eff6ff" stroke="#bfdbfe"/>
  <text x="79" y="175" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="9" font-weight="800" fill="{$cfg['accent']}" text-anchor="middle">{$cfg['badge']}</text>
  
  <!-- Main Titles -->
  <text x="32" y="210" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="16" font-weight="900" fill="#0f172a" letter-spacing="-0.5">{$cfg['title']}</text>
  <text x="32" y="234" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="12" font-weight="700" fill="#475569">{$cfg['sub']}</text>
  
  <!-- Decorative content placeholder lines -->
  <rect x="32" y="260" width="336" height="4" rx="2" fill="#e2e8f0"/>
  <rect x="32" y="274" width="280" height="4" rx="2" fill="#f1f5f9"/>
  <rect x="32" y="288" width="310" height="4" rx="2" fill="#f1f5f9"/>
  
  <!-- Bottom hexagon graphics -->
  <polygon points="310,340 350,360 350,405 310,425 270,405 270,360" fill="url(#grad_{$cfg['badge']})" opacity="0.85"/>
  <polygon points="250,390 280,405 280,440 250,455 220,440 220,405" fill="#f59e0b" opacity="0.75"/>
  <polygon points="350,410 375,422 375,450 350,462 325,450 325,422" fill="#e2e8f0" opacity="0.6"/>
  
  <!-- Bottom wave -->
  <path d="M 0,470 Q 150,440 400,480 L 400,500 L 0,500 Z" fill="url(#grad_{$cfg['badge']})" opacity="0.8"/>
  
  <!-- Footer text -->
  <text x="32" y="450" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="9.5" font-weight="700" fill="#64748b">Universitas BSI</text>
  <text x="32" y="465" font-family="'Plus Jakarta Sans', Arial, sans-serif" font-size="8" fill="#94a3b8">Official Template Document</text>
</svg>
SVG;

    $svgFileName = str_replace('.png', '.svg', $file);
    foreach ($dirs as $d) {
        file_put_contents($d . '/' . $svgFileName, $svg);
        // Also save SVG as png filename so browsers can render directly if referenced with .png/.svg
        file_put_contents($d . '/' . $file, $svg);
    }
}

echo "Generated template graphics successfully!\n";

