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

    #divScrool {
        overflow-x: scroll;
    }
</style>
@extends('layouts.app-template-datatable')

@section('content')

<!-- AdminLTE 3 layout body class -->
<body class="hold-transition sidebar-mini">

    <div class="container-fluid">

        <!-- Main Card -->
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><b>Name Validation Mis Report</b></h3>
            </div>

            <div class="card-body">

                {{-- SUCCESS & ERROR MESSAGES --}}
                @if (($message = Session::get('success')) && ($id = Session::get('id')))
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>{{ $message }} with Application ID: {{$id}}</strong>
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
                    <ul>
                        @foreach($errors->all() as $error)
                        <li><strong>{{ $error }}</strong></li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- FORM -->
                <form method="post" id="register_form" action="{{url('wcd20210202ReportPost')}}" class="submit-once">
                    {{ csrf_field() }}

                    <div class="tab-content mt-3">
                        <div class="tab-pane active" id="personal_details">

                            <!-- SEARCH CRITERIA CARD -->
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h4><b>Search Criteria</b></h4>
                                </div>

                                <div class="card-body">

                                    <div class="row">

                                        {{-- DISTRICT --}}
                                        @if($district_visible)
                                        <div class="col-md-4">
                                            <label class="form-label">District</label>
                                            <select name="district" id="district" class="form-select" tabindex="6">
                                                <option value="">--All--</option>
                                                @foreach ($districts as $district)
                                                <option value="{{$district->district_code}}" @if(old('district') == $district->district_code) selected @endif>
                                                    {{$district->district_name}}
                                                </option>
                                                @endforeach
                                            </select>
                                            <span id="error_district" class="text-danger"></span>
                                        </div>
                                        @else
                                        <input type="hidden" name="district" id="district" value="{{$district_code_fk}}" />
                                        @endif

                                        {{-- RURAL/URBAN --}}
                                        @if($is_urban_visible)
                                        <div class="col-md-4" id="divUrbanCode">
                                            <label class="form-label">Rural / Urban</label>
                                            <select name="urban_code" id="urban_code" class="form-select" tabindex="11">
                                                <option value="">--All--</option>
                                                @foreach(Config::get('constants.rural_urban') as $key => $val)
                                                <option value="{{$key}}" @if(old('urban_code') == $key) selected @endif>
                                                    {{$val}}
                                                </option>
                                                @endforeach
                                            </select>
                                            <span id="error_urban_code" class="text-danger"></span>
                                        </div>
                                        @else
                                        <input type="hidden" name="urban_code" id="urban_code" value="{{$rural_urban_fk}}" />
                                        @endif

                                        {{-- BLOCK --}}
                                        @if($block_visible)
                                        <div class="col-md-4" id="divBodyCode">
                                            <label class="form-label" id="blk_sub_txt">Block / Sub Division</label>
                                            <select name="block" id="block" class="form-select" tabindex="16">
                                                <option value="">--All--</option>
                                            </select>
                                            <span id="error_block" class="text-danger"></span>
                                        </div>
                                        @else
                                        <input type="hidden" name="block" id="block" value="{{$block_munc_corp_code_fk}}" />
                                        @endif

                                        <!-- SEARCH BUTTON -->
                                        <div class="col-md-12 text-center mt-4">
                                            <button type="button" id="submitting" class="btn btn-success btn-lg modal-search form-submitted">
                                                Search
                                            </button>

                                            <div>
                                                <img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1"
                                                     width="50px" height="50px" style="display:none;">
                                            </div>
                                        </div>

                                    </div> <!-- row -->
                                </div> <!-- card-body -->
                            </div> <!-- card -->

                            {{-- ERROR MESSAGE BOX --}}
                            <div class="alert alert-danger print-error-msg mt-3 d-none" id="errorDiv">
                                <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
                                <ul></ul>
                            </div>

                            {{-- SEARCH RESULT SECTION --}}
                            <div class="tab-pane active mt-3" id="search_details" style="display:none;">

                                <div class="card card-info card-outline">
                                    <div class="card-header" id="heading_msg">
                                        <h4><b>Search Result</b></h4>
                                    </div>

                                    <div class="card-body">

                                        <div class="float-end" id="report_generation_text">
                                            Report Generated on:
                                            <b>{{ date("l jS \\of F Y h:i:s A") }}</b>
                                        </div>

                                        <button class="btn btn-info exportToExcel" type="button">Export to Excel</button>
                                        <br><br>

                                        <div id="divScrool">
                                            <table id="example" class="data-table table2excel" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <td colspan="21" class="text-center d-none" id="heading_excel">Heading</td>
                                                    </tr>
                                                    <tr>
                                                        <th rowspan="2">Sl No.(A)</th>
                                                        <th rowspan="2" id="location_id">District</th>
                                                        <th rowspan="2">Total Name Mismatched Applications (C)</th>

                                                        <th colspan="3">Kept existing bank info (Minor mismatch)</th>
                                                        <th colspan="3">Processed with new bank info</th>
                                                        <th colspan="3">Rejected due to major mismatch</th>

                                                        <th rowspan="2">Total Deactivated (M)</th>
                                                        <th rowspan="2">Total verification pending (Legacy)</th>
                                                        <th rowspan="2">Total verification pending (Non-Legacy)</th>
                                                        <th rowspan="2">Total Approval pending (O)</th>
                                                    </tr>

                                                    <tr>
                                                        <th>Verified (D=E+F)</th>
                                                        <th>Approved (E)</th>
                                                        <th>Pending Approval (F)</th>

                                                        <th>Verified (G=H+I)</th>
                                                        <th>Approved (H)</th>
                                                        <th>Pending Approval (I)</th>

                                                        <th>Verified (J=K+L)</th>
                                                        <th>Approved (K)</th>
                                                        <th>Pending Approval (L)</th>
                                                    </tr>
                                                </thead>

                                                <tbody></tbody>

                                                <tfoot>
                                                    <tr id="fotter_id"></tr>
                                                    <tr>
                                                        <td colspan="18" class="text-center d-none" id="fotter_excel">Heading</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                    </div> <!-- card-body -->
                                </div> <!-- card -->

                            </div> <!-- tab-pane -->

                        </div> <!-- tab -->

                    </div> <!-- tab content -->

                </form>

            </div> <!-- card-body -->
        </div> <!-- card -->

    </div> <!-- container -->


@endsection
        @push('scripts')
       <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
            <script>

                $(document).ready(function () {
                    $('.sidebar-menu li').removeClass('active');
                    @if($designation_id == 'HOD')
                        $('.sidebar-menu #paymentReportMain').addClass("active");
                        $('.sidebar-menu #mis-report-nameValidation').addClass("active");
                    @else
                        $('.sidebar-menu #bankTrFailed').addClass("active");
                        $('.sidebar-menu #mis-report-nameValidation').addClass("active");
                    @endif
                    //loadDataTable();
                    $(".exportToExcel").click(function (e) {
                        // alert('ok');
                        $(".table2excel").table2excel({
                            // exclude CSS class
                            exclude: ".noExl",
                            name: "Worksheet Name",
                            filename: "Lakshmir Bhandar Name Validation Mis Report", //do not include extension
                            fileext: ".xls" // file extension
                        });
                    });


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
                                $("#municipality_div").hide();
                                $.each(blocks, function (key, value) {
                                    if (value.district_code == select_district_code) {
                                        htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                                    }
                                });
                            } else if (select_body_type == 1) {
                                $("#blk_sub_txt").text('Subdivision');
                                $("#gp_ward_txt").text('Ward');
                                $("#municipality_div").show();
                                $.each(subDistricts, function (key, value) {
                                    if (value.district_code == select_district_code) {
                                        htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                                    }
                                });
                            }
                            else {
                                $("#blk_sub_txt").text('Block/Subdivision');
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
                                $("#municipality_div").hide();
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
                                $("#municipality_div").hide();
                            }
                        }
                        else {
                            $('#muncid').html('<option value="">--All --</option>');
                            $('#gp_ward').html('<option value="">--All --</option>');
                        }

                    });
                    $('#muncid').change(function () {
                        var muncid = $(this).val();
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
                        if (muncid != '') {
                            var rural_urbanid = $('#urban_code').val();
                            if (rural_urbanid == 1) {
                                var municipality_code = $(this).val();
                                if (municipality_code != '') {
                                    $('#gp_ward').html('<option value="">--All --</option>');
                                    var htmlOption = '<option value="">--All--</option>';
                                    $.each(ulb_wards, function (key, value) {
                                        if (value.urban_body_code == municipality_code) {
                                            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                                        }
                                    });
                                    $('#gp_ward').html(htmlOption);
                                }
                                else {
                                    $('#gp_ward').html('<option value="">--All --</option>');
                                }
                            }

                            else {
                                $('#gp_ward').html('<option value="">--All --</option>');
                                $("#gp_ward_div").hide();
                            }
                        }
                        else {
                            $('#gp_ward').html('<option value="">--All --</option>');
                        }

                    });
                    $('.modal-search').on('click', function () {

                        loadDataTable();


                    });
                });
                function loadDataTable() {

                    var district = $('#district').val();
                    var urban_code = $('#urban_code').val();
                    var block = $('#block').val();
                    var gp_ward = $('#gp_ward').val();
                    var muncid = $('#muncid').val();


                    $("#submit_loader1").show();
                    $("#submitting").hide();
                    $('#search_details').hide();
                    $.ajax({
                        type: 'get',
                        dataType: 'json',
                        url: '{{ url('misReport-nameValidation-Post') }}',
                        data: {
                            district: district,
                            urban_code: urban_code,
                            block: block,
                            gp_ward: gp_ward,
                            muncid: muncid,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function (data) {

                            //alert(data.title);
                            if (data.return_status) {
                                $('#search_details').show();
                                $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
                                $("#heading_excel").html("<b>" + data.heading_msg + "</b>");
                                $("#fotter_excel").html("<b>" + $('#report_generation_text').text() + "</b>");
                                $("#location_id").text(data.column + '(B)');
                                $("#example > tbody").html("");
                                var table = $("#example tbody");
                                var slno = 1;
                                var fotter_1 = 0; var fotter_2 = 0; var fotter_3 = 0; var fotter_4 = 0; var fotter_5 = 0; var fotter_6 = 0;
                                var fotter_7 = 0; var fotter_8 = 0; var fotter_9 = 0; var fotter_10 = 0;
                                var fotter_11 = 0; var fotter_12 = 0; var fotter_13 = 0; var fotter_14 = 0;
                                $.each(data.row_data, function (i, item) {
                                    var total = isNaN(parseInt(item.total)) ? 0 : parseInt(item.total);

                                    var total_same_edited = isNaN(parseInt(item.total_same_edited)) ? 0 : parseInt(item.total_same_edited);
                                    var total_same_approved = isNaN(parseInt(item.total_same_approved)) ? 0 : parseInt(item.total_same_approved);
                                    var total_differ_edited = isNaN(parseInt(item.total_differ_edited)) ? 0 : parseInt(item.total_differ_edited);
                                    var total_differ_approved = isNaN(parseInt(item.total_differ_approved)) ? 0 : parseInt(item.total_differ_approved);
                                    var total_rej_edited = isNaN(parseInt(item.total_rej_edited)) ? 0 : parseInt(item.total_rej_edited);
                                    var total_rej_approved = isNaN(parseInt(item.total_rej_approved)) ? 0 : parseInt(item.total_rej_approved);
                                    var total_deactivate = isNaN(parseInt(item.total_deactivate)) ? 0 : parseInt(item.total_deactivate);
                                    var verification_pending_non_legacy = isNaN(parseInt(item.verification_pending_non_legacy)) ? 0 : parseInt(item.verification_pending_non_legacy);
                                    var verification_pending_legacy = isNaN(parseInt(item.verification_pending_legacy)) ? 0 : parseInt(item.verification_pending_legacy);

                                    var same_pending = total_same_edited;
                                    var same_approve = total_same_approved;
                                    var same_submitted = same_pending + same_approve;

                                    var differ_pending = total_differ_edited;
                                    var differ_approve = total_differ_approved;
                                    var differ_submitted = differ_pending + differ_approve;

                                    var rej_pending = total_rej_edited;
                                    var rej_approve = total_rej_approved;
                                    var rej_submitted = rej_pending + rej_approve;
                                    //var total1 = total+same_submitted+differ_submitted+rej_submitted;

                                    var approval_pending = (same_pending + differ_pending + rej_pending) - total_deactivate;
                                    if (approval_pending < 0) {
                                        approval_pending = 0;
                                    }
                                    fotter_1 = fotter_1 + total;
                                    fotter_2 = fotter_2 + same_submitted;
                                    fotter_3 = fotter_3 + same_approve;
                                    fotter_4 = fotter_4 + same_pending;
                                    fotter_5 = fotter_5 + differ_submitted;
                                    fotter_6 = fotter_6 + differ_approve;
                                    fotter_7 = fotter_7 + differ_pending;
                                    fotter_8 = fotter_8 + rej_submitted;
                                    fotter_9 = fotter_9 + rej_approve;
                                    fotter_10 = fotter_10 + rej_pending;
                                    fotter_11 = fotter_11 + verification_pending_legacy;
                                    fotter_12 = fotter_12 + approval_pending;
                                    fotter_13 = fotter_13 + total_deactivate;
                                    fotter_14 = fotter_14 + verification_pending_non_legacy;
                                    table.append("<tr><td>" + (i + 1) + "</td><td>" + item.location_name + "</td><td>" + total + "</td><td>" + same_submitted + "</td><td>" + same_approve + "</td><td>" + same_pending + "</td><td>" + differ_submitted + "</td><td>" + differ_approve + "</td><td>" + differ_pending + "</td><td>" + rej_submitted + "</td><td>" + rej_approve + "</td><td>" + rej_pending + "</td><td>" + total_deactivate + "</td><td>" + verification_pending_legacy + "</td><td>" + verification_pending_non_legacy + "</td><td>" + approval_pending + "</td></tr>");
                                    //slno++;

                                });

                                $("#example > tfoot #fotter_id").html("<th></th><th>Total</th><th>" + fotter_1 + "</th><th>" + fotter_2 + "</th><th>" + fotter_3 + "</th><th>" + fotter_4 + "</th><th>" + fotter_5 + "</th><th>" + fotter_6 + "</th><th>" + fotter_7 + "</th><th>" + fotter_8 + "</th><th>" + fotter_9 + "</th><th>" + fotter_10 + "</th><th>" + fotter_13 + "</th><th>" + fotter_11 + "</th><th>" + fotter_14 + "</th><th>" + fotter_12 + "</th>");
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
