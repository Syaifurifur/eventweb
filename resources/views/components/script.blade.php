@props(['script' => ''])
    <!-- jQuery library js -->
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <!-- Apex Chart js -->
    <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
    <!-- Data Table js -->
    <script src="{{ asset('assets/js/lib/simple-datatables.min.js') }}"></script>
    <!-- Iconify Font js -->
    <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
    <!-- Flowbite UI js -->
    <script src="{{ asset('assets/js/flowbite.min.js') }}"></script>
    <!-- WowDash Main js -->
    <script src="{{ asset('assets/js/wowdash.js') }}"></script>
    <!-- EventHub Application js -->
    <script src="<?= base_url('assets/js/app.js?v=' . @filemtime(FCPATH . 'assets/js/app.js')) ?>"></script>
    {!! $script ?? '' !!}

