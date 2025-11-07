<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lakshmir Bhandar @yield('title-content')</title>

    <!-- Footer Fix CSS -->
    <style>
        html, body {
            height: 100%;
        }
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
        }
    </style>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('AdminLTE_3/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('AdminLTE_3/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bootstrap-5/css/bootstrap.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('css/jquery-confirm.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('css/global.css') }}" type="text/css" />

    <!-- STACK 1: Global Styles -->
    @stack('styles')

    <!-- STACK 2: Library/Plugin Styles (css from middle-level templates) -->
    @stack('library-styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @include('layouts.header')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <div class="content">
                @yield('content')
            </div>
        </div>

        @include('layouts.footer')
    </div>

    <!-- Core JS -->
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('bootstrap-5/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('AdminLTE_3/dist/js/adminlte.js') }}"></script>
    <script src="{{ asset('js/jquery-confirm.min.js') }}"></script>

    {{-- Dynamic JS --}}
    <!-- STACK 3: Library/Plugin Scripts (js from middle-level templates) -->
    @stack('library-scripts')

    <!-- STACK 4: Page-Specific Scripts (js from page-level templates) -->
    @stack('scripts')
    
    <script>
        $(document).ready(function() {
            function updateDateTime() {
                const now = new Date();
                const options = {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                const dateTimeString = now.toLocaleString('en-IN', options);
                $('#navbar-datetime').text(dateTimeString);
            }

            // Update immediately and then every second
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        function ajax_error(jqXHR, textStatus, errorThrown) {
            $('#loadingDiv').hide();
            var msg = "<strong>Failed to Load data.</strong><br/>";
            if (jqXHR.status !== 422 && jqXHR.status !== 400) {
                msg += "<strong>" + jqXHR.status + ": " + errorThrown + "</strong>";
            } else {
                if (jqXHR.responseJSON.hasOwnProperty('exception')) {
                    msg += "Exception: <strong>" + jqXHR.responseJSON.exception_message + "</strong>";
                } else {
                    msg += "Error(s):<strong><ul>";
                    $.each(jqXHR.responseJSON, function(key, value) {
                        msg += "<li>" + value + "</li>";
                    });
                    msg += "</ul></strong>";
                }
            }
            $.alert({
                title: 'Error!!',
                type: 'red',
                icon: 'fa fa-warning',
                content: msg,
            });
        }
    </script>
</body>
</html>