@extends('layouts.app-template')
@section('content')

    <style>
        /* .card {
            width: 800px;
            margin: 0 auto;
        } */

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

        .is-invalid {
            border-color: #cc0000;
            background-color: #ffff99;
        }

        .select2 {
            width: 100% !important;
        }

        .select2 .is-invalid {
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

        .border {
            border: 1px black solid;
            margin: 4px;
        }
    </style>

    @yield('main-content')
@endsection
