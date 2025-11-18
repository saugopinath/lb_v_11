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

    <body class="hold-transition sidebar-mini">

        <div class="row">

            <div class="col-md-12">

                <div>



                    <div class="mt-3">
                        @if (($message = Session::get('success')) && ($id = Session::get('id')))
                            <div class="alert alert-success alert-dismissible fade show">
                                <strong>{{ $message }} with Application ID: {{ $id }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($message = Session::get('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>{{ $message }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(count($errors) > 0)
                            <div class="alert alert-danger alert-dismissible fade show">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li><strong>{{ $error }}</strong></li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    </div>

                    <form method="post" id="register_form" action="{{ url('wcd20210202ReportPost') }}" class="submit-once">
                        {{ csrf_field() }}

                        <input type="hidden" name="ds_phase" id="ds_phase" value="{{ $ds_phase }}">

                        <div class="tab-content mt-3">

                            <div class="tab-pane fade show active" id="personal_details">

                                <div class="card">
                                    <div
                                        class="card-header card-header-custom d-flex justify-content-between align-items-center">

                                        <h5 class="mb-0">
                                            <b>Duare Sarkar Report of {{ $phase_arr->phase_des }}</b>
                                        </h5>

                                        <a href="{{ url('dsreportphaseselect') }}" class="ms-auto">
                                            <img width="40px" class="img-fluid" src="{{ asset('images/back.png') }}"
                                                alt="Back">
                                        </a>

                                    </div>


                                    <div class="card-body">

                                        <div class="row">

                                            @if($district_visible)
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">District</label>
                                                    <select name="district" id="district" class="form-select">
                                                        <option value="">--All --</option>
                                                        @foreach ($districts as $district)
                                                            <option value="{{ $district->district_code }}"
                                                                @if(old('district') == $district->district_code) selected @endif>
                                                                {{ $district->district_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <span id="error_district" class="text-danger"></span>
                                                </div>
                                            @else
                                                <input type="hidden" name="district" id="district"
                                                    value="{{ $district_code_fk }}">
                                            @endif

                                            @if($is_urban_visible)
                                                <div class="col-md-3 mb-3" id="divUrbanCode">
                                                    <label class="form-label">Rural/Urban</label>
                                                    <select name="urban_code" id="urban_code" class="form-select">
                                                        <option value="">--All--</option>
                                                        @foreach(Config::get('constants.rural_urban') as $key => $val)
                                                            <option value="{{ $key }}" @if(old('urban_code') == $key) selected @endif>
                                                                {{ $val }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <span id="error_urban_code" class="text-danger"></span>
                                                </div>
                                            @else
                                                <input type="hidden" name="urban_code" id="urban_code"
                                                    value="{{ $rural_urban_fk }}">
                                            @endif

                                            @if($block_visible)
                                                <div class="col-md-3 mb-3" id="divBodyCode">
                                                    <label class="form-label" id="blk_sub_txt">Block / Municipality</label>
                                                    <select name="block" id="block" class="form-select">
                                                        <option value="">--All--</option>
                                                    </select>
                                                    <span id="error_block" class="text-danger"></span>
                                                </div>
                                            @else
                                                <input type="hidden" name="block" id="block"
                                                    value="{{ $block_munc_corp_code_fk }}">
                                            @endif

                                            <div class="col-md-4 mb-3" id="municipality_div"
                                                style="{{ $municipality_visible ? '' : 'display:none' }}">
                                                <label class="form-label">Municipality</label>
                                                <select name="muncid" id="muncid" class="form-select">
                                                    <option value="">--All--</option>
                                                    @foreach ($muncList as $munc)
                                                        <option value="{{ $munc->urban_body_code }}">
                                                            {{ $munc->urban_body_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span id="error_muncid" class="text-danger"></span>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label required-field">Till Date</label>

                                                @php
                                                    $max_to = $c_date;
                                                    $min_to = $base_date;
                                                @endphp

                                                <input type="hidden" name="from_date" id="from_date"
                                                    value="{{ $base_date }}">

                                                <input type="date" name="to_date" id="to_date" class="form-control"
                                                    min="{{ $min_to }}" max="{{ $max_to }}">
                                                <span id="error_to_date" class="text-danger"></span>
                                            </div>

                                        </div>

                                        <div class="row text-center mt-4">
                                            <div class="col-md-12">
                                                <button type="button" id="submitting"
                                                    class="btn btn-success btn-lg modal-search form-submitted">
                                                    Search
                                                </button>

                                                <div class="mt-2">
                                                    <img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1" width="50"
                                                        height="50" style="display:none;">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="tab-content mt-3">

                                    <div class="alert alert-danger print-error-msg d-none" id="errorDiv">
                                        <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
                                        <ul class="mb-0"></ul>
                                    </div>

                                    <div class="tab-pane fade show active" id="search_details" style="display:none">

                                        <div class="card">

                                            <div class="card-header card-header-custom" id="heading_msg">
                                                <h5><b>Search Result</b></h5>
                                            </div>

                                            <div class="card-body">

                                                <div class="float-start">
                                                    <b>Columns G, H, I, J data not available in Lakshmir Bhandar Portal.</b>
                                                </div>

                                                <div class="float-end">
                                                    Report Generated on:
                                                    <b><span id="report_generation_text"></span></b>
                                                </div>

                                                <div class="clearfix my-3"></div>

                                                <button class="btn btn-primary exportToExcel" type="button">
                                                    Export to Excel
                                                </button>

                                                <br><br>

                                                <table id="example"
                                                    class="data-table table2excel">

                                                    <thead>
                                                        <tr>
                                                            <td colspan="21" class="text-center d-none" id="heading_excel">
                                                                Heading
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th>Sl No. (A)</th>
                                                            <th id="location_id">District Name (B)</th>
                                                            <th>Applications Received (C)</th>
                                                            <th>Applications under process (D)</th>
                                                            <th>Applications accepted (E)</th>
                                                            <th>Applications rejected (F)</th>
                                                            <th>Services Delivered (G)</th>
                                                            <th>Info to Applicant - Approved (H)</th>
                                                            <th>Info to Applicant - Rejected (I)</th>
                                                            <th>Total Team Formed (J)</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                    </tbody>

                                                    <tfoot>
                                                        <tr id="fotter_id"></tr>
                                                        <tr>
                                                            <td colspan="21" class="text-center d-none" id="fotter_excel">
                                                                Heading
                                                            </td>
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

        </div>

@endsection

    @push('scripts')
        <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
        <script>
            var base_date = '{{$base_date}}';
            var c_date = '{{$c_date}}';
            //alert(base_date);

            $(document).ready(function () {
                $('.sidebar-menu li').removeClass('active');
                $('.sidebar-menu #lk-main').addClass("active");
                $('.sidebar-menu #mis-report').addClass("active");
                //loadDataTable();
                $(".exportToExcel").click(function (e) {
                    //alert('ok');
                    $(".table2excel").table2excel({
                        // exclude CSS class
                        exclude: ".noExl",
                        name: "Worksheet Name",
                        filename: "Lakshmir Bhandar Duare Sarkar Report", //do not include extension
                        fileext: ".xls" // file extension
                    });
                });
                $("#from_date").on('blur', function () {
                    var from_date = $('#from_date').val();
                    if (from_date != '') {
                        //alert(from_date);
                        document.getElementById("to_date").setAttribute("min", from_date);
                    }
                    else {
                        //alert(c_date);
                        document.getElementById("to_date").setAttribute("min", base_date);
                    }
                });



                // $('#ds_phase').change(function(){
                //   var ds_phase_val =$(this).val();
                // $.ajax({
                //             type: 'get',
                //             dataType:'json',
                //             url: '{{ url('dsMisReportPhase') }}',
                //             data: { ds_phase_val: ds_phase_val },
                //             success: function (data) {
                //               console.log(data.ds_phase_list[0].base_dob);
                //               //alert(data.ds_phase_list[0].base_dob);

                //             }

                //           });
                // });


                $('#district').change(function () {
                    var district = $(this).val();
                    //alert(district);
                    $('#urban_code').val('');
                    $('#block').html('<option value="">--All --</option>');
                    $('#muncid').html('<option value="">--All --</option>');
                });

                $('#urban_code').change(function () {
                    var urban_code = $(this).val();
                    if (urban_code == '') {
                        $('#muncid').html('<option value="">--All --</option>');
                    }
                    $('#muncid').html('<option value="">--All --</option>');
                    $('#block').html('<option value="">--All --</option>');
                    $('#gp_ward').html('<option value="">--All --</option>');
                    select_district_code = $('#district').val();
                    if (select_district_code == '') {
                        alert('Please Select District First');
                        $("#district").focus();
                        $("#urban_code").val('');
                    }
                    else {
                        select_body_type = urban_code;
                        var htmlOption = '<option value="">--All--</option>';
                        $("#gp_ward_div").show();
                        if (select_body_type == 2) {
                            $("#blk_sub_txt").text('Block');
                            $("#gp_ward_txt").text('GP');
                            // $("#municipality_div").hide();
                            $.each(blocks, function (key, value) {
                                if (value.district_code == select_district_code) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                                }
                            });
                        } else if (select_body_type == 1) {
                            $("#blk_sub_txt").text('Municipality');
                            $("#gp_ward_txt").text('Ward');
                            // $("#municipality_div").show();
                            $.each(ulbs, function (key, value) {
                                if (value.district_code == select_district_code) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                                }
                            });
                            // $.each(subDistricts, function (key, value) {
                            //     if(value.district_code==select_district_code){
                            //         htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                            //     }
                            // });
                        }
                        else {
                            $("#blk_sub_txt").text('Block/Municipality');
                        }
                        $('#block').html(htmlOption);
                    }

                });

                $('#block').change(function () {
                    var block = $(this).val();
                    var district = $("#district").val();
                    var urban_code = $("#urban_code").val();
                    if (district == '') {
                        $('#urban_code').val('');
                        $('#block').html('<option value="">--All --</option>');
                        $('#muncid').html('<option value="">--All --</option>');
                        alert('Please Select District First');
                        $("#district").focus();

                    }
                    if (urban_code == '') {
                        alert('Please Select Rural/Urban First');
                        $('#block').html('<option value="">--All --</option>');
                        $('#muncid').html('<option value="">--All --</option>');
                        $("#urban_code").focus();
                    }
                    if (block != '') {
                        var rural_urbanid = $('#urban_code').val();
                        if (rural_urbanid == 1) {
                            var sub_district_code = $(this).val();
                            if (sub_district_code != '') {
                                $('#muncid').html('<option value="">--All --</option>');
                                select_district_code = $('#district').val();
                                var htmlOption = '<option value="">--All--</option>';
                                $.each(ulbs, function (key, value) {
                                    if ((value.district_code == select_district_code) && (value.sub_district_code == sub_district_code)) {
                                        htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                                    }
                                });
                                $('#muncid').html(htmlOption);
                            }
                            else {
                                $('#muncid').html('<option value="">--All --</option>');
                            }
                        }
                        else if (rural_urbanid == 2) {
                            $('#muncid').html('<option value="">--All --</option>');
                            // $("#municipality_div").hide();
                            var block_code = $(this).val();
                            select_district_code = $('#district').val();

                            var htmlOption = '<option value="">--All--</option>';
                            $.each(gps, function (key, value) {
                                if ((value.district_code == select_district_code) && (value.block_code == block_code)) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                                }
                            });
                            $('#gp_ward').html(htmlOption);
                            $("#gp_ward_div").show();


                        }
                        else {
                            $('#muncid').html('<option value="">--All --</option>');
                            // $("#municipality_div").hide();
                        }
                    }
                    else {
                        $('#muncid').html('<option value="">--All --</option>');
                        $('#gp_ward').html('<option value="">--All --</option>');
                    }

                });

                $('.modal-search').on('click', function () {

                    // if($.trim($('#from_date').val()).length == 0)
                    // {
                    //  error_from_date = 'From Date is required';
                    //  $('#error_from_date').text(error_from_date);
                    //  $('#from_date').addClass('has-error');
                    // }
                    // else
                    // {
                    //  error_from_date = '';
                    //  $('#error_from_date').text(error_from_date);
                    //  $('#from_date').removeClass('has-error');
                    // }
                    if ($.trim($('#to_date').val()).length == 0) {
                        error_to_date = 'Till Date is required';
                        $('#error_to_date').text(error_to_date);
                        $('#to_date').addClass('has-error');
                    }
                    else {
                        error_to_date = '';
                        $('#error_to_date').text(error_to_date);
                        $('#to_date').removeClass('has-error');
                    }

                    if (error_to_date == '') {
                        loadDataTable();
                    }
                    else {
                        return false;
                    }


                });
            });


            function loadDataTable() {
                var ds_phase = $('#ds_phase').val();
                //var ds_phase=5;
                var district = $('#district').val();
                var urban_code = $('#urban_code').val();
                var block = $('#block').val();
                var gp_ward = $('#gp_ward').val();
                var muncid = $('#muncid').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var caste_category = $('#caste_category').val();
                //alert(ds_phase);
                $("#submit_loader1").show();
                $("#submitting").hide();
                $('#search_details').hide();
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: '{{ url('dsMisReportPost') }}',
                    data: {
                        ds_phase: ds_phase,
                        district: district,
                        urban_code: urban_code,
                        block: block,
                        gp_ward: gp_ward,
                        from_date: from_date,
                        to_date: to_date,
                        muncid: muncid,
                        caste_category: caste_category,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {

                        // alert(data.title);
                        if (data.return_status) {
                            $('#search_details').show();
                            $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
                            $("#heading_excel").html("<b>" + data.heading_msg + "</b>");
                            $("#fotter_excel").html("<b>" + $('#report_generation_text').text() + "</b>");
                            $("#report_generation_text").text(data.report_geneartion_time);
                            $("#location_id").text(data.column + '(B)');
                            $("#example > tbody").html("");
                            var table = $("#example tbody");
                            var slno = 1;
                            var fotter_1 = 0; var fotter_2 = 0; var fotter_3 = 0; var fotter_4 = 0; var fotter_5 = 0; var fotter_6 = 0;
                            var fotter_7 = 0; var fotter_8 = 0; var fotter_9 = 0; var fotter_10 = 0;
                            var fotter_11 = 0; var fotter_12 = 0; var fotter_13 = 0; var fotter_14 = 0; var fotter_15 = 0; var fotter_16 = 0;
                            var fotter_17 = 0; var fotter_18 = 0; var fotter_19 = 0;
                            $.each(data.row_data, function (i, item) {
                                var application_under_process = isNaN(parseInt(item.application_under_process)) ? 0 : parseInt(item.application_under_process);
                                var application_accepted = isNaN(parseInt(item.application_accepted)) ? 0 : parseInt(item.application_accepted);
                                var application_rejected = isNaN(parseInt(item.application_rejected)) ? 0 : parseInt(item.application_rejected);
                                var application_received_camp = application_under_process + application_accepted + application_rejected;

                                var service_delivered = '';
                                var info_application_approved = '';
                                var info_application_rejected = '';
                                var total_team_formed = '';


                                fotter_1 = fotter_1 + application_received_camp;
                                fotter_2 = fotter_2 + application_under_process;
                                fotter_3 = fotter_3 + application_accepted;
                                fotter_4 = fotter_4 + application_rejected;


                                table.append("<tr><td>" + (i + 1) + "</td><td>" + item.location_name + "</td><td>" + application_received_camp + "</td><td>" + application_under_process + "</td><td>" + application_accepted + "</td><td>" + application_rejected + "</td><td>" + service_delivered + "</td><td>" + info_application_approved + "</td><td>" + info_application_rejected + "</td><td>" + total_team_formed + "</td></tr>");
                                //slno++;

                            });

                            $("#example > tfoot #fotter_id").html("<th></th><th>Total</th><th>" + fotter_1 + "</th><th>" + fotter_2 + "</th><th>" + fotter_3 + "</th><th>" + fotter_4 + "</th>");
                            //$('#example tbody').empty();
                            $("#example").show();


                        }
                        else {
                            $('#search_details').hide();
                            $("#example").hide();
                            printMsg(data.return_msg, '0', 'errorDiv');
                        }
                        $("#submit_loader1").hide();
                        $("#submitting").show();

                    },
                    error: function (ex) {
                        //console.log(ex);
                        $("#submit_loader1").hide();
                        //$("#submitting").hide();
                        $("#submitting").show();
                        /// alert('Something wrong..may be session timeout. please logout and then login again');
                        //  location.reload();

                    }
                });

            }
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