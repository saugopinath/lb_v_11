<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SS | {{ Config::get('constants.site_titleShort') }}</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon.ico') }}">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- Bootstrap 3.3.6 -->
    <link href="{{ asset('/bower_components/AdminLTE/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet"
        type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    {{-- <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet"> --}}

    <!-- Select2 -->

    <!-- Ionicons -->
    <!--link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css"-->
    <link href="{{ asset('css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/bower_components/AdminLTE/plugins/datatables/dataTables.bootstrap.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('/bower_components/AdminLTE/plugins/daterangepicker/daterangepicker.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('/bower_components/AdminLTE/plugins/datepicker/datepicker3.css') }}" rel="stylesheet"
        type="text/css" />
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('/bower_components/AdminLTE/plugins/select2/select2.min.css') }}">
    <!-- Theme style -->
    <link href="{{ asset('/bower_components/AdminLTE/dist/css/AdminLTE.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
      page. However, you can choose any other skin. Make sure you
      apply the skin class to the body tag so the changes take effect.
      -->
    <link href="{{ asset('/bower_components/AdminLTE/dist/css/skins/_all-skins.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('css/app-template.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/bootstrapValidator.css') }}" />
    <link href="{{ asset('css/jquery-confirm.min.css') }}" rel="stylesheet">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('/bower_components/AdminLTE/plugins/iCheck/flat/blue.css') }}">


    <!-- fancybox -->

    <link rel="stylesheet" href="{{ asset('/bower_components/AdminLTE/dist/css/jquery.fancybox.css') }}"
        type="text/css">
    <link rel="stylesheet" href="{{ asset('/bower_components/AdminLTE/dist/css/prettyPhoto.css') }}" type="text/css">

    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet"
        href="{{ asset('/bower_components/AdminLTE/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">

    <style type="text/css">
        .full-width {
            width: 100% !important;
        }

        .bg-blue {
            background-image: linear-gradient(to right top, #0073b7, #0086c0, #0097c5, #00a8c6, #00b8c4) !important;
        }

        .bg-red {
            /*background-image: linear-gradient(to right bottom, #dd4b39, #db4546, #d74052, #d13d5e, #c93d68)!important;*/
            /* background-image: linear-gradient(to right bottom, #dd4b39, #e65347, #ef5b55, #f76463, #ff6d71)!important;*/
            background-image: linear-gradient(to right bottom, #dd4b39, #ec6f65, #d21a13, #de0d0b, #f3060d) !important;
        }

        .bg-yellow {
            background-image: linear-gradient(to right bottom, #dd4b39, #e65f31, #ed7328, #f1881e, #f39c12) !important;
        }

        .bg-green {
            /*background-image: linear-gradient(to right bottom, #00837d, #008d7b, #009674, #009e69, #00a65a)!important;*/
            background-image: linear-gradient(to right bottom, #04736d, #008f73, #00ab6a, #00c44f, #5ddc0c) !important;
        }

        .bg-verify {
            background-image: linear-gradient(to right top, #f39c12, #f8b005, #fac400, #fad902, #f8ee15) !important;
        }

        .info-box {
            display: block;
            min-height: 90px;
            background: #b6d0ca33 !important;
            width: 100%;
            box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, 0.30) !important;
            border-radius: 2px;
            margin-bottom: 15px;
        }

        .small-box .icon {
            margin-top: 7%;
        }

        .small-box>.inner {
            padding: 10px;
            color: white;
        }

        .small-box p {
            font-size: 18px !important;
        }

        /* .select2 .select2-container{
  width:100%!important;
}  */

        .link-button {
            background: none;
            border: none;
            color: blue;
            text-decoration: underline;
            cursor: pointer;
            font-size: 1em;
            font-family: serif;
        }

        .link-button:focus {
            outline: none;
        }

        .link-button:active {
            color: red;
        }

        .small-box-footer-custom {
            position: relative;
            text-align: center;
            padding: 3px 0;
            color: #fff;
            color: rgba(255, 255, 255, 0.8);
            display: block;
            z-index: 10;
            background: rgba(0, 0, 0, 0.1);
            text-decoration: none;
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-weight: 400;
            width: 100%;
        }

        .small-box-footer-custom:hover {
            color: #fff;
            background: rgba(0, 0, 0, 0.15);
        }

        th.sorting::after,
        th.sorting_asc::after,
        th.sorting_desc::after {
            content: "" !important;
        }

        .errorField {
            border-color: #990000;
        }

        .searchPosition {
            margin: 70px;
        }

        .submitPosition {
            margin: 25px 0px 0px 0px;
        }


        .typeahead {
            border: 2px solid #FFF;
            border-radius: 4px;
            padding: 8px 12px;
            max-width: 300px;
            min-width: 290px;
            background: rgba(66, 52, 52, 0.5);
            color: #FFF;
        }

        .tt-menu {
            width: 300px;
        }

        ul.typeahead {
            margin: 0px;
            padding: 10px 0px;
        }

        ul.typeahead.dropdown-menu li a {
            padding: 10px !important;
            border-bottom: #CCC 1px solid;
            color: #FFF;
        }

        ul.typeahead.dropdown-menu li:last-child a {
            border-bottom: 0px !important;
        }

        .bgcolor {
            max-width: 550px;
            min-width: 290px;
            max-height: 340px;
            background: url("world-contries.jpg") no-repeat center center;
            padding: 100px 10px 130px;
            border-radius: 4px;
            text-align: center;
            margin: 10px;
        }

        .demo-label {
            font-size: 1.5em;
            color: #686868;
            font-weight: 500;
            color: #FFF;
        }

        .dropdown-menu>.active>a,
        .dropdown-menu>.active>a:focus,
        .dropdown-menu>.active>a:hover {
            text-decoration: none;
            background-color: #1f3f41;
            outline: 0;
        }

        table.dataTable thead th,
        table.dataTable thead td {
            padding: 10px 13px;
        }

        table.dataTable tfoot th,
        table.dataTable tfoot td {
            padding: 10px 5px;
        }

        .criteria1 {
            text-transform: uppercase;
            font-weight: bold;
        }

        #example_length {
            margin-left: 40%;
            margin-top: 2px;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner:before {
            content: '';
            box-sizing: border-box;
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin-top: -10px;
            margin-left: -10px;
            border-radius: 50%;
            border: 2px solid #ccc;
            border-top-color: #333;
            animation: spinner .6s linear infinite;
        }

        label.required:after {
            color: red;
            content: '*';
            font-weight: bold;
            margin-left: 5px;
            float: right;
            margin-top: 5px;
        }

        #loadingDiv {
            position: absolute;
            top: 0px;
            right: 0px;
            width: 100%;
            height: 100%;
            background-color: #fff;
            background-image: url('images/ajaxgif.gif');
            background-repeat: no-repeat;
            background-position: center;
            z-index: 10000000;
            opacity: 0.4;
            filter: alpha(opacity=40);
            /* For IE8 and earlier */
        }
    </style>
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css"> -->
    <!--data table--->
    <link rel="stylesheet" href="{{ asset('/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/buttons.dataTables.min.css') }}">

    <style>
        .box {
            width: 800px;
            margin: 0 auto;
        }

        .active_tab1 {
            background-color: #fff;
            color: #333;
            font-weight: 600;
        }

        .inactive_tab1 {
            background-color: #f5f5f5;
            color: #333;
            cursor: not-allowed;
        }

        .has-error {
            border-color: #cc0000;
            background-color: #ffff99;
        }

        .select2 {
            width: 100% !important;
        }

        .select2 .has-error {
            border-color: #cc0000;
            background-color: #ffff99;
        }

        .modal_field_name {
            float: left;
            font-weight: 700;
            margin-right: 1%;
            padding-top: 1%;
            margin-top: 1%;
        }

        .modal_field_value {
            margin-right: 1%;
            padding-top: 1%;
            margin-top: 1%;
        }

        .row {
            margin-right: 0px !important;
            margin-left: 0px !important;
            margin-top: 1% !important;
        }

        .section1 {
            border: 1.5px solid #9187878c;
            margin: 2%;
            padding: 2%;
        }

        .color1 {
            margin: 0% !important;
            background-color: #5f9ea061;
        }

        .modal-header {
            background-color: #7fffd4;
        }

        .required-field::after {
            content: "*";
            color: red;
        }

        .imageSize {
            font-size: 9px;
            color: #333;
        }

        .bg_blue {
            background-color: #003399;
            width: 330px;
            height: 39px;
            border-radius: 12px;
        }

        .bg_blue h2 {
            color: #fff;
            font-weight: 600;
            margin-left: 45px;
            padding-top: 8px;
            font-size: 20px;
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

        #searchbtn {
            margin: 5px auto;
        }

        #loader {
            margin: 0px 0px 0px 350px;
            ;
        }

        .select2 {
            width: 100% !important;
        }

        .select2 .has-error {
            border-color: #cc0000;
            background-color: #ffff99;
        }

        .requied {
            color: red;
        }

        @import url(https://fonts.googleapis.com/css?family=Cinzel:700);

        /* Timeline */
        .timeline,
        .timeline-horizontal {
            list-style: none;
            /*padding: 20px;*/
            /*OLD*/
            padding: 10px;
            /*NEW*/
            position: relative;
        }

        .timeline:before {
            top: 40px;
            bottom: 0;
            position: absolute;
            content: " ";
            width: 3px;
            background-color: #eeeeee;
            left: 50%;
            margin-left: -1.5px;
        }

        .timeline .timeline-item {
            margin-bottom: 20px;
            position: relative;
        }

        .timeline .timeline-item:before,
        .timeline .timeline-item:after {
            content: "";
            display: table;
        }

        .timeline .timeline-item:after {
            clear: both;
        }

        .timeline .timeline-item .timeline-badge {
            color: #fff;
            /*width: 54px;*/
            /*height: 54px;*/
            width: 45px;
            height: 45px;
            line-height: 52px;
            font-size: 22px;
            text-align: center;
            position: absolute;
            top: 18px;
            left: 50%;
            margin-left: -25px;
            background-color: #bbdefb;
            border: 3px solid #ffffff;
            z-index: 100;
            border-top-right-radius: 50%;
            border-top-left-radius: 50%;
            border-bottom-right-radius: 50%;
            border-bottom-left-radius: 50%;
        }

        .timeline .timeline-item .timeline-badge i,
        .timeline .timeline-item .timeline-badge .fa,
        .timeline .timeline-item .timeline-badge .glyphicon {
            top: 2px;
            left: 0px;
        }

        .timeline .timeline-item .timeline-badge.primary {
            background-color: #bbdefb;
        }

        .timeline .timeline-item .timeline-badge.info {
            background-color: #26c6da;
        }

        .timeline .timeline-item .timeline-badge.success {
            background-color: #80DEEA;
        }

        .timeline .timeline-item .timeline-badge.warning {
            background-color: #a7ffeb;
        }

        .timeline .timeline-item .timeline-badge.danger {
            background-color: #42a5f5;
        }

        .timeline .timeline-item .timeline-panel {
            position: relative;
            height: 100px;
            width: 46%;
            float: left;
            right: 16px;
            border: 1px solid #c0c0c0;
            background: #ffffff;
            border-radius: 2px;
            /*padding: 20px;*/
            padding: 5px;
            -webkit-box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
        }

        .timeline .timeline-item .timeline-panel:before {
            position: absolute;
            top: 26px;
            right: -16px;
            display: inline-block;
            border-top: 16px solid transparent;
            border-left: 16px solid #c0c0c0;
            border-right: 0 solid #c0c0c0;
            border-bottom: 16px solid transparent;
            content: " ";
        }

        .timeline .timeline-item .timeline-panel .timeline-title {
            margin-top: 0;
            /*font-size: 25px;*/
            font-size: 20px;
            font-family: 'Waiting for the Sunrise', cursive;
            color: #0c0c0c
        }

        .timeline .timeline-item .timeline-panel .timeline-body>p,
        .timeline .timeline-item .timeline-panel .timeline-body>ul {
            margin-bottom: 0;
            font-family: 'Cinzel', sans-serif;
            color: #a79898;
        }

        .timeline .timeline-item .timeline-panel .timeline-body>p+p {
            margin-top: 0px;
        }

        .timeline .timeline-item:last-child:nth-child(even) {
            float: right;
        }

        .timeline .timeline-item:nth-child(even) .timeline-panel {
            float: right;
            left: 16px;
        }

        .timeline .timeline-item:nth-child(even) .timeline-panel:before {
            border-left-width: 0;
            border-right-width: 14px;
            left: -14px;
            right: auto;
        }

        .timeline-horizontal {
            list-style: none;
            position: relative;
            padding: 20px 0px 20px 0px;
            display: inline-block;
        }

        .timeline-horizontal:before {
            height: 3px;
            top: auto;
            bottom: 26px;
            left: 56px;
            right: 0;
            width: 100%;
            margin-bottom: 20px;
        }

        .timeline-horizontal .timeline-item {
            display: table-cell;
            /*height: 280px;*/
            height: 180px;
            width: 20%;
            /*min-width: 320px;*/
            min-width: 260px;
            float: none !important;
            padding-left: 0px;
            /*padding-right: 20px;*/
            padding-right: 10px;
            margin: 0 auto;
            vertical-align: bottom;
        }

        .timeline-horizontal .timeline-item .timeline-panel {
            top: auto;
            /*bottom: 64px;*/
            bottom: 50px;
            display: inline-block;
            float: none !important;
            left: 0 !important;
            right: 0 !important;
            width: 100%;
            /*margin-bottom: 20px;*/
            margin-bottom: 10px;
        }

        .timeline-horizontal .timeline-item .timeline-panel:before {
            top: auto;
            bottom: -16px;
            left: 28px !important;
            right: auto;
            border-right: 16px solid transparent !important;
            border-top: 16px solid #c0c0c0 !important;
            border-bottom: 0 solid #c0c0c0 !important;
            border-left: 16px solid transparent !important;
        }

        .timeline-horizontal .timeline-item:before,
        .timeline-horizontal .timeline-item:after {
            display: none;
        }

        .timeline-horizontal .timeline-item .timeline-badge {
            top: auto;
            bottom: 0px;
            left: 48px;
        }

        .preloader1 {
            position: fixed;
            top: 40%;
            left: 52%;
            z-index: 999;
        }

        #loadingDivModal {
            position: absolute;
            top: 0px;
            right: 0px;
            width: 100%;
            height: 100%;
            background-color: #fff;
            background-image: url('../images/ajaxgif.gif');
            background-repeat: no-repeat;
            background-position: center;
            z-index: 10000000;
            opacity: 0.4;
            filter: alpha(opacity=40);
            /* For IE8 and earlier */
        }

        #loaderDiv {
            position: absolute;
            top: 0px;
            right: 0px;
            width: 100%;
            height: 100%;
            background-color: #fff;
            background-image: url('../images/ajaxgif.gif');
            background-repeat: no-repeat;
            background-position: center;
            z-index: 10000000;
            opacity: 0.4;
            filter: alpha(opacity=40);
            /* For IE8 and earlier */
        }

        .panel-title {
            position: relative;
        }

        .panel-title::after {
            content: "\f107";
            color: #333;
            top: -2px;
            right: 0px;
            position: absolute;
            font-family: "FontAwesome"
        }

        .panel-title[aria-expanded="true"]::after {
            content: "\f106";
        }

        /*
 * Added 12-27-20 to showcase full title clickthrough
 */

        .panel-heading-full.panel-heading {
            padding: 0;
        }

        .panel-heading-full .panel-title {
            padding: 10px 15px;
        }

        .panel-heading-full .panel-title::after {
            top: 10px;
            right: 15px;
        }

        .panel-title>a,
        .panel-title>a:active {
            display: block;
            padding: 5px;
            color: #555;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            word-spacing: 3px;
            text-decoration: none;
        }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->

<body class="hold-transition skin-blue sidebar-mini">
    <nav class="navbar navbar-default" style="background-color: #f7fcff; margin-bottom: 0;">

        {{-- <div class="navbar-header">
        <a class="navbar-brand" href="#" style="font-size: 20px; color: #fff;">Lakshmir Bhandar</a>
      </div> --}}
        <!-- <ul class="nav navbar-nav navbar-right">
        <li><a href="#" style="color: #fff;"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
      </ul> -->
        <div class="row">
            <div class="col-xs-3 col-sm-3 col-md-2">
                <img class="biswo" src="{{ asset('images/biswo.webp') }}" alt="Alternate Text" width="100px" />
            </div>
            <div class="col-xs-9  col-sm-9 col-md-10" style="margin-top: 20px; ">
                <div class="col-md-6">
                    <div class="paschimbanga_sarkar">
                        <h2>পশ্চিমবঙ্গ সরকার</h2>
                        <h3>Government Of Bengal</h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg_blue">
                        <h2>Lakshmir Bhandar Portal</h2>
                    </div>
                </div>
            </div>
        </div>

    </nav>
    <section class="content-header">
        <h1>
            Track Applicant & View Payment Status
        </h1>
        <!-- <ol class="breadcrumb">
      <span style="font-size: 12px; font-weight: bold;"><i class="fa fa-clock-o"> Date : </i><span
          class='date-part'></span>&nbsp;&nbsp;<span class='time-part'></span></span>
    </ol> -->
    </section>
    <section class="content">
        @if ($crud_status = Session::get('crud_status'))
            <div class="alert alert-{{ $crud_status == 'success' ? 'success' : 'danger' }} alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ Session::get('crud_msg') }} @if ($crud_status == 'success')
                        with Application ID: {{ Session::get('id') }}
                    @endif
                </strong>
            </div>
        @endif
        @if (count($errors) > 0)
            <div class="alert alert-danger alert-block">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li><strong> {{ $error }}</strong></li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="box box-primary">
            <div class="box-header with-border">
                <span style="font-size: 15px; font-style: italic; font-weight: bold;">Enter Application Id/Mobile
                    No./Swasthyasathi Card No./Aadhaar No.</span>
            </div>
            <div class="box-body">
                <div id="loaderDiv"></div>
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                        <input type="hidden" name="scheme_code" id="scheme_code" value="{{ $scheme_id }}">
                        <div class="col-md-3">
                            <label class="control-label"><i class="fa  fa-filter"></i> Search Using <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" name="select_type" id='select_type' required>
                                <option value="">--Select--</option>
                                <option value="1">Application Id</option>
                                {{-- <option value="2">Beneficiary Id</option> --}}
                                <option value="3">Mobile Number</option>
                                <option value="4">Aadhar Card Number</option>
                                {{-- <option value="5">Bank Account Number</option> --}}
                                <option value="6">Swasthyasathi Card No</option>
                            </select>
                            <span class="text-danger" id="error_select_type"></span>
                        </div>
                        <div class="col-md-3" id="input_val_div">
                            <label><span id="selectValueName">Value</span> <span class="text-danger">*</span></label>
                            <input type="text" name="applicant_id" id="applicant_id" class="form-control"
                                placeholder="Value" autocomplete="off" style="font-size: 16px;"
                                onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" />
                            <span id="error_applicant_id" class="text-danger"></span>
                        </div>
                        <div class="col-md-2" style="text-align: right; margin-top: 20px;">
                            <span class="refereshrecapcha">{!! captcha_img('flat') !!}</span>
                            <a href="{{ route('track-applicant') }}"><img src="{{ asset('images/refresh1.png') }}"
                                    style="height: 20px; width: 20px; border-width: 0px;"></a>
                        </div>
                        <div class="col-md-2" style="margin-top: 20px;">
                            <input type="text" id="captcha" name="captcha" placeholder="Enter Captcha"
                                class="form-control" style="font-size: 16px; margin: 5px auto;">
                            <span id="error_captcha" class="text-danger"></span>
                        </div>
                        <div class="col-md-2" id="search_div" style="margin-top: 20px;">
                            <button type="button" class="btn btn-primary" id="searchbtn"><i
                                    class="fa fa-search"></i> Search</button>
                        </div>
                    </div>
                </div>
                <div class="text-primary"
                    style="text-align: center; justify-content: center; font-size: 16px; font-weight: bold; margin-top: 10px;"
                    id="search_msg"></div>

                <!-- Result Div Showing Timeline -->
                <div id="ajaxData"></div>
            </div>

        </div>
    </section>
    <br />
    <!-- Main Footer -->
    <div>
        <footer
            style="background: #fff; padding: 15px; color: #444; border-top: 1px solid #d2d6de; background-color: ghostwhite;">
            <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="http://nicwb.nic.in">NIC</a>.</strong> All rights
            reserved.
        </footer>
    </div>

    <!-- REQUIRED JS SCRIPTS -->
    <!-- jQuery 2.1.3 -->
    <script src="{{ asset('/AdminLTE_3/plugins/jquery/jquery.min.js') }}"></script>

    <!-- Bootstrap 3.3.2 JS -->
    <script src="{{ asset('/bower_components/AdminLTE/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/datatables/jquery.dataTables.min.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/datatables/dataTables.bootstrap.min.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/fastclick/fastclick.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/moment/moment.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/input-mask/jquery.inputmask.js') }}" type="text/javascript">
    </script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/input-mask/jquery.inputmask.date.extensions.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/input-mask/jquery.inputmask.extensions.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/daterangepicker/daterangepicker.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/plugins/datepicker/bootstrap-datepicker.js') }}"
        type="text/javascript"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('/bower_components/AdminLTE/dist/js/app.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/bootstrapValidator.js') }}"></script>
    <script src="{{ asset('js/jquery-confirm.min.js') }}"></script>
    <script src="{{ asset('js/select2.full.min.js') }}"></script>
    <!-- iCheck -->
    <script src="{{ asset('/bower_components/AdminLTE/plugins/iCheck/icheck.min.js') }}"></script>

    <!-- Bootstrap WYSIHTML5 -->
    <script src="{{ asset('/bower_components/AdminLTE/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}">
    </script>


    <script src="{{ asset('/bower_components/AdminLTE/dist/js/demo.js') }}" type="text/javascript"></script>

    <!-- Select2 -->
    <script src="{{ asset('/bower_components/AdminLTE/plugins/select2/select2.full.min.js') }}"></script>

    <!-- fancybox -->

    <script src="{{ asset('/bower_components/AdminLTE/dist/js/jquery.fancybox.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('/bower_components/AdminLTE/dist/js/jquery.prettyPhoto.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/bower_components/AdminLTE/dist/js/validation_backend.js') }}" type="text/javascript"></script>
    <script src="{{ asset('frontend/js/bootstrap-filestyle.min.js') }}" type="text/javascript"></script>
    <!---data table------->
    <!--  <script src="{{ asset('js/jquery-1.12.4.js') }}"></script> -->
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('js/jszip.min.js') }}"></script>
    <script src="{{ asset('js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('js/jquery.table2excel.js') }}"></script>
    <script>
        $('.select2').select2();
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#loaderDiv').hide();

            $('#input_val_div').hide();
            $('#search_msg').html('');
            $('#select_type').change(function() {
                $('#search_msg').html('');
                if ($('#select_type').val() != "") {
                    $('#input_val_div').show();
                    $('#applicant_id').val('');
                    var selectedVal = $("#select_type option:selected").text();
                    $('#selectValueName').text(selectedVal);
                    $("#applicant_id").attr("placeholder", "Enter " + selectedVal);
                    $('#error_applicant_id').text('');
                } else {
                    $('#input_val_div').hide();
                }
            });

            var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
            var base_url = '{{ url('/') }}';
            var PleaseSelectScheme = '@lang('lang.PleaseSelectScheme')';
            var PleaseEnterApplicationId = '@lang('lang.PleaseEnterApplicationId')';

            var error_select_type = '';
            var error_applicant_id = '';
            var error_captcha = '';
            $("#searchbtn").click(function() {
                if ($.trim($('#select_type').val()).length == 0) {
                    error_select_type = 'This field is required';
                    $('#error_select_type').text(error_select_type);
                } else {
                    error_select_type = '';
                    $('#error_select_type').text(error_select_type);
                }

                if ($.trim($('#applicant_id').val()).length == 0) {
                    error_applicant_id = 'This field is required';
                    $('#error_applicant_id').text(error_applicant_id);
                } else {
                    error_applicant_id = '';
                    $('#error_applicant_id').text(error_applicant_id);
                }

                if ($.trim($('#captcha').val()).length == 0) {
                    error_captcha = 'Captcha is required';
                    $('#error_captcha').text(error_captcha);
                } else {
                    error_captcha = '';
                    $('#error_captcha').text(error_captcha);
                }

                if (error_select_type == '' && error_applicant_id == '' && error_captcha == '') {
                    $("#ajaxData").html('');
                    $('#resultDivPaymentStatus').hide();
                    var scheme_code = $("#scheme_code").val();
                    var applicant_id = $("#applicant_id").val();
                    var captcha = $("#captcha").val();
                    var scheme_type = $('#select_type').val();
                    //console.log(application_type); 
                    var status1 = status2 = status3 = 0;
                    if (scheme_code == '' || typeof(scheme_code) === "undefined" || scheme_code === null) {
                        $('#error_scheme_code').text(PleaseSelectScheme);
                        status1 = 0;
                    } else {
                        $('#error_scheme_code').text('');
                        status1 = 1;
                    }
                    if (applicant_id == '' || typeof(applicant_id) === "undefined" || applicant_id ===
                        null) {
                        $('#error_applicant_id').text(PleaseEnterApplicationId);
                        status1 = 0;
                    } else {
                        $('#error_application_type').text('');
                        status2 = 1;
                    }
                    //alert(status1);  alert(status2);
                    if (status1 && status2) {
                        var url = '{{ url('ajaxApplicationTrack') }}';
                        var role_code = $('#role_code').val();
                        // $('#ajaxData').html('<img align="center" src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
                        $('#ajaxData').html('');
                        $('#loaderDiv').show();
                        $.ajax({
                            type: 'post',
                            url: url,
                            data: {
                                is_public: 1,
                                captcha: captcha,
                                scheme_code: scheme_code,
                                applicant_id: applicant_id,
                                trackType: scheme_type,
                                _token: '{{ csrf_token() }}',
                            },
                            success: function(data) {
                                // console.log(data);
                                $('#loaderDiv').hide();
                                $("#modal_data").html('');
                                $("#ajaxData").html(data);
                                $("#applicant_id").val('');
                                $("#captcha").val('');
                                refreshCaptcha();
                            },
                            error: function(ex) {
                                $('#loaderDiv').hide();
                                $("#modal_data").html('');
                                $('#ajaxData').html('');
                                alert('Timeout ..Please try again.');
                                location.reload();
                            }
                        });
                    }
                } else {
                    return false;
                }
            });

        });

        //------------------Beneficiary Payment Status Section------------------


        //########Change Financial Year########//
        function changeFinancialYear(fin_year, beneficiary_id) {
            $('#loaderDiv').show();
            var finYear = fin_year;
            // var ben_id = $('#ben_id_hidden').val();
            $.ajax({
                type: "POST",
                url: "{{ route('getPaymentDetailsFinYearWiseInTrackApplication') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    ben_id: beneficiary_id,
                    fin_year: finYear
                },
                success: function(response) {
                    $('#loaderDiv').hide();
                    $('#payment_details_' + response.personalDetails.ben_id).html('');
                    $('#payment_details_' + response.personalDetails.ben_id).html(response.paymentDetails);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#loaderDiv').hide();
                    $('.ben_view_modal').modal('hide');
                    // ajax_error(jqXHR, textStatus, errorThrown);
                    $.alert({
                        title: 'Error!!',
                        type: 'red',
                        icon: 'fa fa-warning',
                        content: sessiontimeoutmessage,
                    });
                }
            });

        }

        function refreshCaptcha() {

            $.ajax({
                url: '{{ url('refereshcapcha') }}',
                type: 'get',
                dataType: 'html',
                success: function(json) {
                    $('.refereshrecapcha').html(json);
                },
                error: function(data) {
                    alert('Try Again.');
                }
            });
        }
    </script>
    <script>
        function printDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>


</body>

</html>
