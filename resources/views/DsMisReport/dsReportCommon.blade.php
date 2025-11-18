<style>
    .box {
        width: 800px;
        margin: 0 auto;
    }

    .card-header-custom {
        font-size: 16px;
        background: linear-gradient(to right, #c9d6ff, #e2e2e2);
        font-weight: bold;
        font-style: italic;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
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
</style>
@extends('layouts.app-template-datatable')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div>
                @if (($message = Session::get('success')) && ($id = Session::get('id')))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>{{ $message }} with Application ID: {{$id}}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($message = Session::get('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{{ $message }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(count($errors) > 0)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li><strong>{{ $error }}</strong></li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>


            <!-- Form -->
            <form method="post" id="register_form" action="{{ url('dsMisReport') }}" class="submit-once">
                @csrf

                <div class="tab-content mt-3">
                    <div class="tab-pane active" id="personal_details">

                        <!-- PHASE SELECTION -->

                        <div class="card">
                            <div class="card-header card-header-custom">
                                <h4 class="mb-0"><b>Duare Sarkar Phase</b></h4>
                            </div>

                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required-field">Select Phase</label>

                                        <select name="phase_code" id="phase_code" class="form-select">
                                            @foreach ($phase_list as $phase)
                                                <option value="{{ $phase->phase_code }}"
                                                    @if($phase->is_current) selected @endif>
                                                    {{ $phase->phase_des }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <span id="error_phase_code" class="text-danger"></span>
                                    </div>

                                    <div class="col-md-12 text-center mt-4">
                                        <button type="submit" id="submitting" class="btn btn-success btn-lg form-submitted">
                                            GO
                                        </button>

                                        <div class="mt-2">
                                            <img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1"
                                                width="50" height="50" style="display:none;">
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>


                        <!-- SEARCH RESULT -->

                        <div class="tab-content mt-3">

                            <div class="alert alert-danger print-error-msg alert-dismissible fade show"
                                 id="errorDiv" style="display:none;">
                                <ul class="mb-0"></ul>
                                <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
                            </div>

                            <div class="tab-pane active" id="search_details" style="display:none;">

                                <div class="card">

                                    <div class="card-header card-header-custom">
                                        <h4 class="mb-0"><b>Search Result</b></h4>
                                    </div>

                                    <div class="card-body">

                                        <div class="text-end mb-3">
                                            Report Generated on:
                                            <b>{{ date("l jS \\of F Y h:i:s A") }}</b>
                                        </div>

                                        <table id="example" class="data-table"
                                               style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Sl No.</th>
                                                    <th id="location_id" width="25%">District</th>
                                                    <th>Total Application</th>
                                                    <th>Pending Application For Action</th>
                                                    <th>Verified</th>
                                                    <th>Approved</th>
                                                    <th>Yet to be Approved</th>
                                                    <th>Rejected</th>
                                                </tr>
                                            </thead>

                                            <tbody></tbody>

                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <th>Total</th>
                                                    <th>Total Application</th>
                                                    <th>Pending Application For Action</th>
                                                    <th>Verified</th>
                                                    <th>Approved</th>
                                                    <th>Yet to be Approved</th>
                                                    <th>Rejected</th>
                                                </tr>
                                            </tfoot>

                                        </table>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>
@endsection
        @push('scripts')
            <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
            <script>

                $(document).ready(function () {
                    $('.sidebar-menu li').removeClass('active');
                    $('.sidebar-menu #lk-main').addClass("active");
                    $('.sidebar-menu #mis-report').addClass("active");

                    $('.modal-search').on('click', function () {
                        var error_phase_code = '';
                        if ($.trim($('#phase_code').val()).length == 0) {
                            error_phase_code = 'Phase is required';
                            $('#error_phase_code').text(error_phase_code);
                            $('#phase_code').addClass('has-error');
                        }
                        else {
                            error_phase_code = '';
                            $('#error_phase_code').text(error_phase_code);
                            $('#phase_code').removeClass('has-error');
                        }

                        if (error_phase_code == '') {
                            loadDataTable();
                        }
                        else {
                            return false;
                        }
                    });
                });

                function printMsg(msg, msgtype, divid) {
                    $("#" + divid).find("ul").html('');
                    $("#" + divid).css('display', 'block');
                    if (msgtype == '0') {
                        //alert('error');
                        $("#" + divid).removeClass('alert-success');
                        //$('.print-error-msg').removeClass('alert-warning');
                        $("#" + divid).addClass('alert-warning');
                    }
                    else {
                        $("#" + divid).removeClass('alert-warning');
                        $("#" + divid).addClass('alert-success');
                    }
                    if (Array.isArray(msg)) {
                        $.each(msg, function (key, value) {
                            $("#" + divid).find("ul").append('<li>' + value + '</li>');
                        });
                    }
                    else {
                        $("#" + divid).find("ul").append('<li>' + msg + '</li>');
                    }
                }
                function closeError(divId) {
                    $('#' + divId).hide();
                }

            </script>
        @endpush
