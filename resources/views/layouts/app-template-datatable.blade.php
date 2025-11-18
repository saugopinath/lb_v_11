<!-- layout/app-template-datatable.blade.php -->
@extends('layouts.app-template')
@push('styles')
    <!-- <style href="{{ asset('css/datatable_custom.css') }}"></style> -->
@endpush
<!-- Push datatable CSS to library-styles stack -->
@push('library-styles')
    <link href="{{ asset('datatable/css/dataTables.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('datatable/css/datatables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/YajradatatableCustom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/adminlte.min.css') }}" rel="stylesheet" type="text/css" />
     <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}" type="text/css" />
@endpush

<!-- Push datatable JS to library-scripts stack -->
@push('library-scripts')
    <script src="{{ asset('datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('datatable/js/datatables.min.js') }}"></script>
    <script src="{{ asset('datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('datatable/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('js/select2.full.min.js') }}"></script>
    <script src="{{ asset('js/jquery.table2excel.js') }}"></script>
    <script>
        (function($) {
            $(document).ready(function() {
                $(document).off('click.bs.dropdown.data-api');
                $(document).on('click', '.dropdown-toggle', function(e) {
                    e.stopPropagation();
                    $(this).dropdown('toggle');
                });

                const dropdowns = document.querySelectorAll('.dropdown-toggle');
                dropdowns.forEach(function(el) {
                    new bootstrap.Dropdown(el);
                });
            });
        })(jQuery);
    </script>
@endpush
