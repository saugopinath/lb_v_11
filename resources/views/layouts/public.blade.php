<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lakshmir Bhandar| Government of West Bengal</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/biswofab.png') }}">

    <!-- Fonts and Bootstrap -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('bootstrap-5.3.8-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('AdminLTE_3/plugins/fontawesome-free/css/all.min.css') }}" />
    @stack('styles')
    <!-- Custom Styles -->
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        /* main expands to push footer down */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }

        .header-section {
            background-color: #f2fbfc;
            border-bottom: 3px solid #0d6efd;
            padding: 10px 0;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.1);
        }

        .bangla-emblem {
            height: 60px;
            margin-top: 20px;
        }


        .paschimbanga_sarkar h2 {
            color: #115e28;
            font-size: 35px;
            font-weight: bold;
        }

        .paschimbanga_sarkar h3 {
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
            color: #341c90;
            margin-top: -6px;
            font-size: 20px;
        }

        .bg_blue {
            background-color: #003399;
            padding: 10px;
            border-radius: 12px;
            width: auto;
            text-align: center;
            margin: 0 auto;
        }

        .bg_blue h2 {
            color: #fff;
            font-weight: 600;
            margin: 0;
        }

        .pb_wb h4 {
            font-size: 15px;
            margin-top: 10px;
        }

        .pb_wb h3 {
            margin-top: -8px;
            font-size: 17px;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: 600;
            color: #341c90;
        }

        .nic {
            color: #fff
        }

        /* Dark Mode */
        /* body.dark {
            background: url(images/testimonial-bg_dr.png) no-repeat center center fixed;
            background-size: cover;
            color: #ffff00;
        } */
        body.dark .paschimbanga_sarkar h2,
        body.dark .paschimbanga_sarkar h3,
        body.dark .pb_wb h4,
        body.dark .pb_wb h3,
        body.dark .bg_blue h2 {
            color: #ffff00;
        }

        body.dark .bg_blue {
            background-color: #000;
        }

        body.dark .footer {
            background-color: #000;
            color: #ffff00;
        }

        body.dark .nic {
            color: #ffff00
        }

        /* Responsive Adjustments */
        @media (max-width: 767px) {
            .paschimbanga_sarkar h2 {
                font-size: 24px;
            }

            .paschimbanga_sarkar h3 {
                font-size: 14px;
            }

            .bg_blue {
                width: 160px;
            }

            .bg_blue h2 {
                font-size: 16px;
            }
        }

        header {
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* footer {
            position: sticky;
            bottom: 0;
            z-index: 1020;
        } */
        footer {
            background-color: #0d6efd;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
            margin-top: 80px;
            width: 100%;
            position: sticky;
            bottom: 0;
            z-index: 1020;
        }


        #p_text {
            max-height: 70vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        #p_text::-webkit-scrollbar {
            width: 8px;
        }

        #p_text::-webkit-scrollbar-thumb {
            background-color: #0d6efd;
            border-radius: 4px;
        }

        #p_text::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* #p_text {
            min-height: 400px;
            margin-top: 60px;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        } */
        #p_text_1 {
            font-weight: 600;
            font-size: 16px;
            line-height: 1.8;
            text-align: justify;
        }
    </style>
</head>

<body>

    <header class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Left Logo -->
                <div class="col-md-2 col-sm-3 text-center">
                    <img src="{{ asset('images/lakshmir_bhandar.png') }}" alt="Logo"
                        class="img-fluid w-50 logo-hover-effect">
                </div>
                <div class="col-md-10 col-sm-9">
                    <div class="row gx-4"> 
                        <div class="col-md-6 mt-4">
                            <div class="paschimbanga_sarkar">
                                <h2>পশ্চিমবঙ্গ সরকার</h2>
                                <h3>Government Of West Bengal</h3>
                            </div>
                        </div>
                        <div class="col-md-6 text-center px-3">
                            <div class="bg_blue mt-4">
                                <h2>Lakshmir Bhandar Portal</h2>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        <strong>Copyright © 2020 <a href="#"></a>NIC</a>.</strong>
        All rights reserved.
    </footer>
    <script src="{{ asset('bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>

    @stack('scripts')
</body>

</html>