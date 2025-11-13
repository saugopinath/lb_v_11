<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
<style>
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
        background-color: aliceblue;
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

@extends('layouts.app-template-datatable')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
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
            <div class="box box-default">
                <div class="box-header with-border">
                    <span style="font-size: 15px; font-style: italic; font-weight: bold;">Enter Application Id/Beneficiary Id/Mobile
                        No./Bank Account No./Swasthyasathi Card No./Aadhaar No.</span>
                </div>
                <div class="box-body">
                    <div id="loaderDiv"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                            <input type="hidden" name="user_id" id="user_id" value="{{ $user_id }}">
                            <input type="hidden" name="scheme_code" id="scheme_code" value="{{ $scheme_id }}">
                            <div class="col-md-3">
                                <label class="control-label"><i class="fa  fa-filter"></i> Search Using <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="select_type" id='select_type' required>
                                    <option value="">--Select--</option>
                                    <option value="1">Application Id</option>
                                    <option value="2">Beneficiary Id</option>
                                    <option value="3">Mobile Number</option>
                                    <option value="4">Aadhar Card Number</option>
                                    <option value="5">Bank Account Number</option>
                                    <option value="6">Swasthyasathi Card No</option>
                                </select>
                                <span class="text-danger" id="error_select_type"></span>
                            </div>
                            <div class="col-md-4" id="input_val_div" style="display: none;">
                                <label class="control-label"><span id="selectValueName">Value</span> <span
                                        class="text-danger">*</span> </label>
                                <input type="text" name="applicant_id" id="applicant_id" class="form-control"
                                    placeholder="Enter value" autocomplete="off" style="font-size: 16px;"
                                    onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" />
                                <span id="error_applicant_id" class="text-danger"></span>
                            </div>
                            <div class="col-md-5" id="search_div" style="margin-top: 20px;">
                                <button type="button" class="btn btn-primary" id="searchbtn"><i class="fa fa-search"></i>
                                    Search</button>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="alert print-error-msg"  style="display:none;" id="crud_msg_Crud">
              <button type="button" class="close"  aria-label="Close" onclick="closeError('crud_msg_Crud')"><span aria-hidden="true">&times;</span></button>
              <ul></ul>
            </div> --}}

                    <!-- Result Div Showing Timeline -->
                    <div id="ajaxData"></div>

                </div>
            </div>
        </section>
    </div>
@endsection
<script src="{{ asset('/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#loaderDiv').hide();
        $('.sidebar-menu li').removeClass('active');
        $('.sidebar-menu #lk-main').addClass("active");
        $('.sidebar-menu #appplicantTrack').addClass("active");
        var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
        var base_url = '{{ url('/') }}';
        var PleaseSelectScheme = '@lang('lang.PleaseSelectScheme')';
        var PleaseEnterApplicationId = '@lang('lang.PleaseEnterApplicationId')';
        $('#input_val_div').hide();

        $('#select_type').change(function() {
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

        var error_select_type = '';
        var error_applicant_id = '';
        $("#searchbtn").click(function() {
            if ($.trim($('#select_type').val()).length == 0) {
                error_select_type = 'Track filter is required';
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

            if (error_select_type == '' && error_applicant_id == '') {
                $('#resultDivPaymentStatus').hide();
                var scheme_code = $("#scheme_code").val();
                var applicant_id = $("#applicant_id").val();
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
                if (status1 && status2) {
                    var url = '{{ url('ajaxApplicationTrack') }}';
                    var role_code = $('#role_code').val();
                    // $('#ajaxData').html('<img align="center" src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
                    $('#ajaxData').html('');
                    $('#loaderDiv').show();
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: {
                            is_public: 0,
                            scheme_code: scheme_code,
                            applicant_id: applicant_id,
                            trackType: scheme_type,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(data) {
                            $('#loaderDiv').hide();
                            $("#modal_data").html('');
                            $("#ajaxData").html(data);
                        },
                        error: function(ex) {
                            $('#loaderDiv').hide();
                            $("#modal_data").html('');
                            $('#ajaxData').html('');
                            alert('Timeout ..Please try again.');
                            //location.reload();
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
</script>
