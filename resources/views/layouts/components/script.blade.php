    <!-- jQuery library js -->
    <script src="{{ asset('admin/js/lib/jquery-3.7.1.min.js') }}"></script>
    <!-- Apex Chart js -->
    <script src="{{ asset('admin/js/lib/apexcharts.min.js') }}"></script>
    <!-- Data Table js -->
    <script src="{{ asset('admin/js/lib/simple-datatables.min.js') }}"></script>
    <!-- Iconify Font js -->
    <script src="{{ asset('admin/js/lib/iconify-icon.min.js') }}"></script>
    <!-- jQuery UI js -->
    <script src="{{ asset('admin/js/lib/jquery-ui.min.js') }}"></script>
    <!-- Vector Map js -->
    <script src="{{ asset('admin/js/lib/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ asset('admin/js/lib/jquery-jvectormap-world-mill-en.js') }}"></script>
    <!-- Popup js -->
    <script src="{{ asset('admin/js/lib/magnifc-popup.min.js') }}"></script>
    <!-- Slick Slider js -->
    <script src="{{ asset('admin/js/lib/slick.min.js') }}"></script>
    <!-- prism js -->
    <script src="{{ asset('admin/js/lib/prism.js') }}"></script>
    <!-- file upload js -->
    <script src="{{ asset('admin/js/lib/file-upload.js') }}"></script>
    <!-- audio player -->
    <script src="{{ asset('admin/js/lib/audioplayer.js') }}"></script>

    <script src="{{ asset('admin/js/flowbite.min.js') }}"></script>
    <!-- main js -->
    <script src="{{ asset('admin/js/app.js') }}"></script>

    <?php echo isset($script) ? $script : ''; ?>
    @stack('scripts')
