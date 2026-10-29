@props(['title' => 'EventHub'])
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | EventHub</title>
    <link rel="icon" href="<?= base_url('favicon.ico?v=' . @filemtime(FCPATH . 'favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/taskbar-icon.png?v=' . @filemtime(FCPATH . 'assets/img/taskbar-icon.png')) ?>">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <!-- WowDash RemixIcon & Plugins CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/full-calendar.css') }}">
    <!-- WowDash Main Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @vite('resources/css/app.css')
    <!-- EventHub + WowDash Unified Styles -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css?v=' . @filemtime(FCPATH . 'assets/css/app.css')) ?>">
</head>

