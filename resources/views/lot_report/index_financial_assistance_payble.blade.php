<style type="text/css">
    .preloader1 {
        position: fixed;
        top: 40%;
        left: 52%;
        z-index: 999;
    }

    .preloader1 {
        background: transparent !important;
    }

    .disabledcontent {
        pointer-events: none;
        opacity: 0.4;
    }

    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }

    .modal {
        text-align: center;
        padding: 0 !important;
    }

    .modal:before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -4px;
    }

    .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
    }

    label.required:after {
        color: red;
        content: '*';
        font-weight: bold;
        margin-left: 5px;
        float: right;
        margin-top: 5px;
    }

    .filterDiv {
        border: 1px solid #d9d9d9;
        border-left: 3px solid deepskyblue;
        margin-bottom: 10px;
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }

    .resultDiv {
        border: 1px solid #d9d9d9;
        border-left: 3px solid seagreen;
        /*margin-bottom: 10px; */
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }

    table,
    td,
    th {
        border: 1px solid #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }
</style>

@extends('layouts.app-template-datatable')
@section('content')

    <!-- Content Header -->
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1>Financial Assistance Payable</h1>
                </div>
            </div>
        </div>

        <div class="container-fluid">

            <div class="card card-default">
                <div class="card-body">

                    <div id="loadingDiv"></div>

                    {{-- Search Panel --}}
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Search By Financial Year</h3>
                        </div>

                        <div class="card-body" style="padding: 5px;">

                            {{-- Alert Section --}}
                            <div class="row">
                                @if (($message = Session::get('success')))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <strong>{{ $message }}</strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if(count($errors) > 0)
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <ul>
                                            @foreach($errors->all() as $error)
                                                <li><strong>{{ $error }}</strong></li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif
                            </div>

                            {{-- Dropdown Row --}}
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Select Financial Year <span
                                                    class="text-danger">*</span></label>

                                            <select onchange="la(this.value)" class="form-select" name="scheme" id="scheme">
                                                <option value="">--Select--</option>
                                                @foreach ($fin_year as $fin)
                                                    <option value="{{ $fin->url }}">{{ $fin->financial_year }}</option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div><!-- panel end -->

                </div>
            </div>

        </div>

@endsection

@push("scripts")
<script src="{{ asset('js/master-data-v2.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#loadingDiv').hide();
            $('#result_div').hide();
            // $('#filter_div').removeClass('disabledcontent');
            // $('#submit_btn').removeAttr('disabled');
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #paymentReportMain').addClass("active");
            $('.sidebar-menu #lotCreationPendingListReport').addClass("active");

        });

        function la(src) {
            window.location = src;
        }

        function ajax_error(jqXHR, textStatus, errorThrown) {
            var msg = "<strong>Failed to Load data.</strong><br/>";
            if (jqXHR.status !== 422 && jqXHR.status !== 400) {
                msg += "<strong>" + jqXHR.status + ": " + errorThrown + "</strong>";
            }
            else {
                if (jqXHR.responseJSON.hasOwnProperty('exception')) {
                    msg += "Exception: <strong>" + jqXHR.responseJSON.exception_message + "</strong>";
                }
                else {
                    msg += "Error(s):<strong><ul>";
                    $.each(jqXHR.responseJSON, function (key, value) {
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
@endpush
