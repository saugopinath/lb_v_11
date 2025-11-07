<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, minimum-scale=0.1, initial-scale=1.0" />
    <title>Lakshmir Bhandar | Government of West Bengal</title>
    <link rel="icon" type="image/png" sizes="32x32" href="images/biswofab.png" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('bootstrap-5.3.8-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- <link rel="stylesheet" href="{{ asset('css/fontawesome-free-5.15.4-web/css/all.min.css') }}" /> -->
    <link rel="stylesheet" href="{{ asset('AdminLTE_3/plugins/fontawesome-free/css/all.min.css') }}" />
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: url(images/background-cover.jpg) no-repeat center center fixed;
            background-size: cover;
            transition: background 0.5s ease, color 0.3s ease;
        }

        /* Container Styles */
        .inner-container {
            margin-top: 50px;
            width: 100%;
            background: url(images/Login_Page_new.png) no-repeat center center;
            background-size: cover;
            padding: 20px;
            border-radius: 8px;
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
            font-weight: 500;
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


        /* Font Controls + Dark Toggle */
        .bar {
            display: inline-flex;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .bar li {
            padding: 5px 7px;
            border: 1px solid #fff;
            margin-right: 5px;
            color: #fff;
            cursor: pointer;
            border-radius: 4px;
        }

        .bar li.last_one {
            border: none;
        }

        .bar li.last_one a {
            color: #fff;
            text-decoration: none;
        }

        .float-end {
            margin-top: -25px;
        }

        /* Toggle Switch */
        .checkbox {
            opacity: 0;
            position: absolute;
        }

        .label {
            width: 45px;
            height: 22px;
            background-color: #20425f;
            display: flex;
            border-radius: 50px;
            align-items: center;
            justify-content: space-between;
            padding: 5px;
            position: relative;
            cursor: pointer;
        }

        .ball {
            width: 15px;
            height: 15px;
            background-color: white;
            position: absolute;
            top: 3px;
            left: 3px;
            border-radius: 50%;
            transition: transform 0.2s linear;
        }

        .checkbox:checked+.label .ball {
            transform: translateX(24px);
        }

        /* Dark Mode */
        body.dark {
            background: url(images/testimonial-bg_dr.png) no-repeat center center fixed;
            background-size: cover;
            color: #ffff00;
        }

        body.dark .inner-container {
            background: url(images/Login_Page_dr.png) no-repeat center center;
            background-size: cover;
        }

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

        body.dark .form-control {
            background-color: #555;
            color: #ffff00;
        }

        body.dark .btn {
            background-color: #000;
            color: #ffff00;
            border-color: #ffff00;
        }

        body.dark .bar li {
            border: 1px solid #ffff00;
            color: #ffff00;
        }

        body.dark .bar li.last_one a {
            color: #ffff00;
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

        /* Footer */
        .footer {
            background-color: blue;
            color: #fff;
            text-align: center;
            padding: 8px;
            font-size: 12px;
        }

        .footer a {
            text-decoration: none;
        }

        .fa-sun {
            color: yellow;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="inner-container">
            <div class="d-flex justify-content-between align-items-center">
                <ul class="bar">
                    <li id="largerTextLink">A+</li>
                    <li id="smallerTextLink">A-</li>
                    <li id="resetFont">A</li>
                    <li class="last_one"><a href="#">Screen Reader</a></li>
                </ul>
                <div class="float-end">
                    <input type="checkbox" class="checkbox" id="checkbox">
                    <label for="checkbox" class="label">
                        <i class="fas fa-moon"></i>
                        <i class="fas fa-sun"></i>
                        <div class="ball"></div>
                    </label>
                </div>
            </div>

            <div class="row align-items-center mt-4">
                <div class="col-md-2 col-sm-3 text-center">
                    <img src="images/lakshmir_bhandar.png"
                        alt="Logo"
                        class="img-fluid w-75 rounded shadow-lg p-2 logo-hover-effect">
                </div>

                <div class="col-md-10 col-sm-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="paschimbanga_sarkar">
                                <h2>পশ্চিমবঙ্গ সরকার</h2>
                                <h3>Government Of West Bengal</h3>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="bg_blue">
                                <h2>Lakshmir Bhandar Portal</h2>
                            </div>

                            <div class="text-center mt-4">
                                <a href="{{route('track-applicant')}}" class="btn btn-info d-inline-flex align-items-center justify-content-center gap-2 px-4 py-2 rounded-pill shadow-sm fw-semibold" style="min-width: 230px;">
                                    <i class="fa fa-map-marker"></i>
                                    Track Applicant Status
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{ $slot }}
        </div>
    </div>
    <div class="container">
        <footer class="footer">
            <span>Site Designed & Developed by </span>
            <a href="http://www.nic.in/" target="_blank" class="nic">National Informatics Centre</a>
            <br>
            <span id="Label1">Best Viewed in Google Chrome</span> |
            <a href="#exampleModal" data-bs-toggle="modal" style="color: yellow;">Legal Disclaimer</a> |
            <a href="{{ route('copyright-policy') }}" target="_blank" style="color: pink;">Copyright Policy</a> |
            <a href="{{ route('privacy-policy') }}" target="_blank" style="color: #3af207;">Privacy Policy</a> |
            <a href="{{ route('hyperlink-policy') }}" target="_blank" style="color: #f25574;">Hyperlink Policy</a> |
            <a href="{{ route('terms-policy') }}" target="_blank" style="color: #bbf207;">Terms & Condition</a>
        </footer>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Legal Disclaimer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        All efforts have been made to make the information as accurate as possible. The respective Departments,
                        Govt of West Bengal or Department of Finance as Nodal or NIC will not be responsible for any loss due to
                        inaccuracy. Any discrepancy found may be brought to the notice of the concerned department.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>

    <script>
        // Input validation
        $('#mobile_no').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Font size control with localStorage
        let fontSize = localStorage.getItem('fontSize') || 14;
        $('body, a, p, input[type=text]').css('font-size', fontSize + 'px');

        function updateFontSize(type) {
            fontSize = parseInt(fontSize);
            if (type === 'increase' && fontSize < 20) fontSize += 2;
            else if (type === 'decrease' && fontSize > 10) fontSize -= 2;
            else if (type === 'reset') fontSize = 14;

            $('body, a, p, input[type=text]').css('font-size', fontSize + 'px');
            localStorage.setItem('fontSize', fontSize);
        }

        $('#largerTextLink').click(() => updateFontSize('increase'));
        $('#smallerTextLink').click(() => updateFontSize('decrease'));
        $('#resetFont').click(() => updateFontSize('reset'));

        // Dark mode with memory
        const checkbox = document.getElementById('checkbox');
        const savedDarkMode = localStorage.getItem('darkMode') === 'true';
        if (savedDarkMode) document.body.classList.add('dark');
        checkbox.checked = savedDarkMode;

        checkbox.addEventListener('change', () => {
            document.body.classList.toggle('dark');
            localStorage.setItem('darkMode', document.body.classList.contains('dark'));
        });
    </script>
</body>

</html>