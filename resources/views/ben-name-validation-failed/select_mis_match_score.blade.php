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
</style>


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
@extends('layouts.app-template-datatable')
@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-12">

                {{-- SUCCESS MESSAGE --}}
                @if (($message = Session::get('success')) && ($id = Session::get('id')))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong>{{ $message }} with Application ID: {{ $id }}</strong>
                </div>
                @endif

                {{-- ERROR MESSAGE --}}
                @if ($message = Session::get('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong>{{ $message }}</strong>
                </div>
                @endif

                {{-- VALIDATION ERRORS --}}
                @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li><strong>{{ $error }}</strong></li>
                        @endforeach
                    </ul>
                </div>
                @endif

            </div>

            {{-- FORM START --}}
            <form method="" id="register_form" action="#" class="submit-once">
                {{ csrf_field() }}

                <div class="tab-content mt-3">

                    <div class="tab-pane active" id="personal_details">

                        <div class="card card-default">
                            <div class="card-header">
                                <h4 class="card-title mb-0"><b>Name Minor Mismatch</b></h4>
                            </div>

                            <div class="card-body">

                                <div class="row">

                                    <div class="form-group col-md-4">
                                        <label class="required-field">Select Matching Score</label>
                                        <select name="matching_score" id="matching_score" class="form-control">
                                            <option value="1" selected>90% - 100%</option>
                                            <option value="2">40% - 89%</option>
                                        </select>
                                        <span id="error_phase_code" class="text-danger"></span>
                                    </div>

                                    <div class="col-md-12 text-center mt-3">
                                        <button type="button" id="submitting" class="btn btn-success btn-lg modal-search form-submitted">GO</button>

                                        <div>
                                            <img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1" width="50px" height="50px" style="display:none;">
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>


                        {{-- ERROR DIV --}}
                        <div class="tab-content mt-3">
                            <div class="alert alert-danger print-error-msg d-none" id="errorDiv">
                                <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
                                <ul></ul>
                            </div>

                            {{-- SEARCH RESULT --}}
                            <div class="tab-pane active" id="search_details" style="display:none;">
                                <div class="card card-default">
                                    <div class="card-header" id="heading_msg">
                                        <h4 class="card-title mb-0"><b>Search Result</b></h4>
                                    </div>

                                    <div class="card-body">

                                        <div class="float-end mb-2">
                                            Report Generated on:
                                            <b><?php echo date('l jS \of F Y h:i:s A'); ?></b>
                                        </div>

                                        <table id="example" class="table table-striped table-bordered table-hover w-100">
                                            <thead>
                                                <tr>
                                                    <th>Sl No.</th>
                                                    <th width="25%">District</th>
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
</section>

@endsection


@push('scripts')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.sidebar-menu li').removeClass('active');
        $('.sidebar-menu #minor-mismatch-main').addClass("active");
        $('.sidebar-menu #select-minor-mismatch').addClass("active");

        $('.modal-search').on('click', function() {
            var error_phase_code = '';
            if ($.trim($('#matching_score').val()).length == 0) {
                error_phase_code = 'Matching Score is required';
                $('#error_phase_code').text(error_phase_code);
                $('#matching_score').addClass('has-error');
            } else {
                error_phase_code = '';
                $('#error_phase_code').text(error_phase_code);
                $('#matching_score').removeClass('has-error');
            }

            var matching_score = $('#matching_score').val();
            if (matching_score == 1) {
                window.location.href = "edit-name-failed-90-to-100?type=1";
            }
            if (matching_score == 2) {
                window.location.href = "edit-name-failed-90-to-100?type=2";
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
        } else {
            $("#" + divid).removeClass('alert-warning');
            $("#" + divid).addClass('alert-success');
        }
        if (Array.isArray(msg)) {
            $.each(msg, function(key, value) {
                $("#" + divid).find("ul").append('<li>' + value + '</li>');
            });
        } else {
            $("#" + divid).find("ul").append('<li>' + msg + '</li>');
        }
    }

    function closeError(divId) {
        $('#' + divId).hide();
    }
</script>
@endpush