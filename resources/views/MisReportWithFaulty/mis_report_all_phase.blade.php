<style type="text/css">
    .preloader1 {
        position: fixed;
        top: 40%;
        left: 52%;
        z-index: 999;
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
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }

    /* Enhanced Design Styles */
    .page-header {
        background: linear-gradient(135deg, #6b89ed 0%, #605164 100%);
        color: white;
        padding: 6px 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title i {
        font-size: 1.5rem;
    }

    /* Button Styling */
    .btn-action {
        border: none !important;
        border-radius: 6px !important;
        padding: 8px 16px !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
    }

    .btn-action:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
    }
</style>

@extends('layouts.app-template-datatable')
@section('content')
    <div class="row mb-1 ml-2">
        <div class="page-header col-sm-auto mt-4">
            {{-- <h1 class="page-title">
                <i class="fas fa-exchange-alt"></i> Districtwise Validation Report
            </h1> --}}
        </div>
    </div>
    <section class="content">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);">
                <h5 class="mb-0">Filter Options</h5>
            </div>
            <div class="card-body" style="padding: 15px;">
                <div id="loadingDiv"></div>
                <div class="filterDiv">
                    <div class="row">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-block col-12">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>{{ $message }}</strong>
                            </div>
                        @endif
                        @if (count($errors) > 0)
                            <div class="alert alert-danger alert-block col-12">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li><strong> {{ $error }}</strong></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-row">
                                @if ($district_visible)
                                    <div class="form-group col-md-4">
                                        <label class="">District</label>
                                        <select name="district" id="district" class="form-control" tabindex="6">
                                            <option value="">--All --</option>
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->district_code }}"
                                                    @if (old('district') == $district->district_code) selected @endif>
                                                    {{ $district->district_name }}</option>
                                            @endforeach
                                        </select>
                                        <span id="error_district" class="text-danger"></span>

                                    </div>
                                @else
                                    <input type="hidden" name="district" id="district" value="{{ $district_code_fk }}" />
                                @endif
                                @if ($is_urban_visible)
                                    <div class="form-group col-md-4" id="divUrbanCode">
                                        <label class="">Rural/ Urban</label>

                                        <select name="urban_code" id="urban_code" class="form-control" tabindex="11">
                                            <option value="">--All --</option>
                                            @foreach (Config::get('constants.rural_urban') as $key => $val)
                                                <option value="{{ $key }}"
                                                    @if (old('urban_code') == $key) selected @endif>{{ $val }}
                                                </option>
                                            @endforeach

                                        </select>
                                        <span id="error_urban_code" class="text-danger"></span>
                                    </div>
                                @else
                                    <input type="hidden" name="urban_code" id="urban_code" value="{{ $rural_urban_fk }}" />
                                @endif
                                <div class="form-group col-md-3" style="margin-top: 32px;">
                                    <button type="button" id="submitting" value="Submit"
                                        class="btn btn-success success btn-lg modal-search form-submitted btn-action">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    &nbsp;
                                    {{-- <button type="button" name="reset" id="reset" class="btn btn-warning btn-action">
                                        <i class="fas fa-redo"></i> Reset
                                    </button> --}}
                                    {{-- <button style="float: right;" type="button" id="submitting" value="Submit"
                                        class="btn btn-info btn-action"
                                        onclick="window.location.href='{{ route('de-Duplicate-Bank-List') }}'"><i
                                            class="fa fa-users"></i> Duplicate Bank Info Beneficiary List</button> --}}

                                    <div class=""><img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1"
                                            width="50px" height="50px" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive resultDiv mt-3" id="validation_lot_div">
                    <button class="btn btn-info exportToExcel  btn-action" type="button">Export to
                        Excel</button><br /><br /><br />
                    <table id="example" class="display data-table table2excel" cellspacing="0" width="100%"
                        style="border: 1px solid ghostwhite;">
                        <thead style="font-size: 12px;">
                            <tr role="row">
                                <th id="" rowspan="3" style="text-align:center">Sl No.(A)</th>
                                <th id="location_id" rowspan="3" style="text-align:center">District</th>
                                <th rowsapn="2" colspan="4" style="text-align:center">Total</th>
                                <th rowsapn="3" colspan="16" style="text-align:center">Application In Process</th>
                                <th rowsapn="2" colspan="4" style="text-align:center">Approved</th>
                                <th rowsapn="3" style="text-align:center;">Rejected(O)</th>
                            </tr>
                            <tr>
                                <th colspan="4">
                                </th>

                                <th colspan="4" style="text-align:center">Save as Draft</th>
                                <th colspan="4" style="text-align:center">Reverted</th>
                                <th colspan="4" style="text-align:center">Verification Pending</th>
                                <th colspan="4" style="text-align:center">Approval pending</th>

                                <th colspan="4"></th>
                            </tr>
                            <tr>
                                <th>Others(C)</th>
                                <th>SC(D)</th>
                                <th>ST(E)</th>
                                <th>Total(F=(C+D+E))</th>

                                <th>Others(G)</th>
                                <th>SC(H)</th>
                                <th>ST(I)</th>
                                <th>Total(J=(G+H+I))</th>

                                <th>Others(K)</th>
                                <th>SC(L)</th>
                                <th>ST(M)</th>
                                <th>Total(N=(K+L+M))</th>

                                <th>Others(O)</th>
                                <th>SC(P)</th>
                                <th>ST(Q)</th>
                                <th>Total(R=(O+P+Q))</th>

                                <th>Others(S)</th>
                                <th>SC(T)</th>
                                <th>ST(U)</th>
                                <th>Total(V=S+T+U)</th>

                                <th>Others(W)</th>
                                <th>SC(X)</th>
                                <th>ST(Y)</th>
                                <th>Total(Z=(W+X+Y))</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px;"></tbody>
                        <tfoot>
                            <tr id="fotter_id"></tr>
                            <tr>
                                <td colspan="27" align="center" style="display:none;" id="fotter_excel">Heading</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        var base_date = '{{ $base_date }}';
        var c_date = '{{ $c_date }}';
        //alert(base_date);

        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            // $('.sidebar-menu #lk-main').addClass("active"); 
            $('.sidebar-menu #mis-report-console-phase').addClass("active");
            $('#submit_loader1').show();
            loadDataTable();
            $(".exportToExcel").click(function(e) {
                // alert('ok');
                $(".table2excel").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Worksheet Name",
                    filename: "Lakshmir Bhandar Mis Report", //do not include extension
                    fileext: ".xls" // file extension
                });
            });
            $("#from_date").on('blur', function() {
                var from_date = $('#from_date').val();
                if (from_date != '') {
                    //alert(from_date);
                    document.getElementById("to_date").setAttribute("min", from_date);
                } else {
                    //alert(c_date);
                    document.getElementById("to_date").setAttribute("min", base_date);
                }
            });

            $('#district').change(function() {
                var district = $(this).val();
                //alert(district);
                $('#urban_code').val('');
                $('#block').html('<option value="">--All --</option>');
                $('#muncid').html('<option value="">--All --</option>');
            });

            $('#urban_code').change(function() {
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
                } else {
                    select_body_type = urban_code;
                    var htmlOption = '<option value="">--All--</option>';
                    $("#gp_ward_div").show();
                    if (select_body_type == 2) {
                        $("#blk_sub_txt").text('Block');
                        $("#gp_ward_txt").text('GP');
                        $("#municipality_div").hide();
                        $.each(blocks, function(key, value) {
                            if (value.district_code == select_district_code) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                    } else if (select_body_type == 1) {
                        $("#blk_sub_txt").text('Subdivision');
                        $("#gp_ward_txt").text('Ward');
                        $("#municipality_div").show();
                        $.each(subDistricts, function(key, value) {
                            if (value.district_code == select_district_code) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                    } else {
                        $("#blk_sub_txt").text('Block/Subdivision');
                    }
                    $('#block').html(htmlOption);
                }

            });
            $('#block').change(function() {
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
                            $.each(ulbs, function(key, value) {
                                if ((value.district_code == select_district_code) && (value
                                        .sub_district_code == sub_district_code)) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text +
                                        '</option>';
                                }
                            });
                            $('#muncid').html(htmlOption);
                        } else {
                            $('#muncid').html('<option value="">--All --</option>');
                        }
                    } else if (rural_urbanid == 2) {
                        $('#muncid').html('<option value="">--All --</option>');
                        $("#municipality_div").hide();
                        var block_code = $(this).val();
                        select_district_code = $('#district').val();

                        var htmlOption = '<option value="">--All--</option>';
                        $.each(gps, function(key, value) {
                            if ((value.district_code == select_district_code) && (value
                                    .block_code == block_code)) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                        $('#gp_ward').html(htmlOption);
                        $("#gp_ward_div").show();


                    } else {
                        $('#muncid').html('<option value="">--All --</option>');
                        $("#municipality_div").hide();
                    }
                } else {
                    $('#muncid').html('<option value="">--All --</option>');
                    $('#gp_ward').html('<option value="">--All --</option>');
                }

            });
            $('#muncid').change(function() {
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
                            $.each(ulb_wards, function(key, value) {
                                if (value.urban_body_code == municipality_code) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text +
                                        '</option>';
                                }
                            });
                            $('#gp_ward').html(htmlOption);
                        } else {
                            $('#gp_ward').html('<option value="">--All --</option>');
                        }
                    } else {
                        $('#gp_ward').html('<option value="">--All --</option>');
                        $("#gp_ward_div").hide();
                    }
                } else {
                    $('#gp_ward').html('<option value="">--All --</option>');
                }

            });
            $('.modal-search').on('click', function() {

                loadDataTable();


            });
        });

        function loadDataTable() {
            var ds_phase = $('#ds_phase').val();
            var district = $('#district').val();
            var urban_code = $('#urban_code').val();
            var block = $('#block').val();
            var gp_ward = $('#gp_ward').val();
            var muncid = $('#muncid').val();
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var caste_category = $('#caste_category').val();

            $("#submit_loader1").show();
            $("#submitting").hide();
            $('#search_details').hide();
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '{{ url('misReportAllPhasePost') }}',
                data: {
                    //   ds_phase: ds_phase,
                    district: district,
                    urban_code: urban_code,
                    block: block,
                    gp_ward: gp_ward,
                    from_date: from_date,
                    to_date: to_date,
                    muncid: muncid,
                    //   caste_category: caste_category,
                    _token: '{{ csrf_token() }}',
                },
                success: function(data) {

                    //alert(data.title);
                    if (data.return_status) {
                        $('#search_details').show();
                        $("#submit_loader1").hide();
                        $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
                        $("#heading_excel").html("<b>" + data.heading_msg + "</b>");
                        $("#report_generation_text").text(data.report_geneartion_time);
                        $("#fotter_excel").html("Report Generated On: <b>" + $('#report_generation_text')
                            .text() + "</b>");
                        $("#location_id").text(data.column + '(B)');
                        $("#example > tbody").html("");
                        var table = $("#example tbody");
                        var slno = 1;
                        var footer_1 = 0;
                        var footer_2 = 0;
                        var footer_3 = 0;
                        var footer_4 = 0;
                        var footer_5 = 0;
                        var footer_6 = 0;
                        var footer_7 = 0;
                        var footer_8 = 0;
                        var footer_9 = 0;
                        var footer_10 = 0;
                        var footer_11 = 0;
                        var footer_12 = 0;
                        var footer_13 = 0;
                        var footer_14 = 0;
                        var footer_15 = 0;
                        var footer_16 = 0;
                        var footer_17 = 0;
                        var footer_18 = 0;
                        var footer_19 = 0;
                        var footer_20 = 0;
                        var footer_21 = 0;
                        var footer_22 = 0;
                        var footer_23 = 0;
                        var footer_24 = 0;
                        var footer_25 = 0;

                        $.each(data.row_data, function(i, item) {
                            var total_ot = 0;
                            var total_sc = 0;
                            var total_st = 0;
                            var total = 0;
                            var partial_ot = isNaN(parseInt(item.partial_ot)) ? 0 : parseInt(item
                                .partial_ot);


                            var total_ot = total_ot + partial_ot;

                            var partial_sc = isNaN(parseInt(item.partial_sc)) ? 0 : parseInt(item
                                .partial_sc);
                            var total_sc = total_sc + partial_sc;
                            var partial_st = isNaN(parseInt(item.partial_st)) ? 0 : parseInt(item
                                .partial_st);
                            var total_st = total_st + partial_st;
                            var reverted_ot = isNaN(parseInt(item.reverted_ot)) ? 0 : parseInt(item
                                .reverted_ot);
                            var total_ot = total_ot + reverted_ot;
                            var reverted_sc = isNaN(parseInt(item.reverted_sc)) ? 0 : parseInt(item
                                .reverted_sc);
                            var total_sc = total_sc + reverted_sc;
                            var reverted_st = isNaN(parseInt(item.reverted_st)) ? 0 : parseInt(item
                                .reverted_st);
                            var total_st = total_st + reverted_st;
                            var verification_pending_ot = isNaN(parseInt(item
                                .verification_pending_ot)) ? 0 : parseInt(item
                                .verification_pending_ot);
                            var total_ot = total_ot + verification_pending_ot;
                            var verification_pending_sc = isNaN(parseInt(item
                                .verification_pending_sc)) ? 0 : parseInt(item
                                .verification_pending_sc);
                            var total_sc = total_sc + verification_pending_sc;
                            var verification_pending_st = isNaN(parseInt(item
                                .verification_pending_st)) ? 0 : parseInt(item
                                .verification_pending_st);
                            var total_st = total_st + verification_pending_st;

                            var approval_pending_ot = isNaN(parseInt(item.approval_pending_ot)) ? 0 :
                                parseInt(item.approval_pending_ot);
                            var total_ot = total_ot + approval_pending_ot;
                            console.log(total_ot);
                            var approval_pending_sc = isNaN(parseInt(item.approval_pending_sc)) ? 0 :
                                parseInt(item.approval_pending_sc);
                            var total_sc = total_sc + approval_pending_sc;
                            var approval_pending_st = isNaN(parseInt(item.approval_pending_st)) ? 0 :
                                parseInt(item.approval_pending_st);
                            var total_st = total_st + approval_pending_st;

                            var approved_ot = isNaN(parseInt(item.approved_ot)) ? 0 : parseInt(item
                                .approved_ot);
                            var total_ot = total_ot + approved_ot;
                            console.log(total_ot);
                            var approved_sc = isNaN(parseInt(item.approved_sc)) ? 0 : parseInt(item
                                .approved_sc);
                            var total_sc = total_sc + approved_sc;
                            var approved_st = isNaN(parseInt(item.approved_st)) ? 0 : parseInt(item
                                .approved_st);
                            var total_st = total_st + approved_st;
                            var total = total_ot + total_sc + total_st;
                            var partial_total = partial_ot + partial_sc + partial_st;
                            var reverted_total = reverted_ot + reverted_sc + reverted_st;
                            var verification_total = verification_pending_ot + verification_pending_sc +
                                verification_pending_st;
                            var approval_pending_total = approval_pending_ot + approval_pending_sc +
                                approval_pending_st;
                            var approval_total = approved_ot + approved_sc + approved_st;
                            var rejected = isNaN(parseInt(item.rejected)) ? 0 : parseInt(item.rejected);

                            footer_1 = footer_1 + total_ot;
                            footer_2 = footer_2 + total_sc;
                            footer_3 = footer_3 + total_st;
                            footer_4 = footer_4 + total;

                            footer_5 = footer_5 + partial_ot;
                            footer_6 = footer_6 + partial_sc;
                            footer_7 = footer_7 + partial_st;
                            footer_8 = footer_8 + partial_total;

                            footer_9 = footer_9 + reverted_ot;
                            footer_10 = footer_10 + reverted_sc;
                            footer_11 = footer_11 + reverted_st;
                            footer_12 = footer_12 + reverted_total;

                            footer_13 = footer_13 + verification_pending_ot;
                            footer_14 = footer_14 + verification_pending_sc;
                            footer_15 = footer_15 + verification_pending_st;
                            footer_16 = footer_16 + verification_total;

                            footer_17 = footer_17 + approval_pending_ot;
                            footer_18 = footer_18 + approval_pending_sc;
                            footer_19 = footer_19 + approval_pending_st;
                            footer_20 = footer_20 + approval_pending_total;

                            footer_21 = footer_21 + approved_ot;
                            footer_22 = footer_22 + approved_sc;
                            footer_23 = footer_23 + approved_st;
                            footer_24 = footer_24 + approval_total;

                            footer_25 = footer_25 + rejected;

                            table.append("<tr><td class='notTotal'>" + (i + 1) +
                                "</td><td class='notTotal'>" + item.location_name + "</td><td>" +
                                total_ot + "</td><td>" + total_sc + "</td><td>" + total_st +
                                "</td><td>" + total + "</td><td>" + partial_ot + "</td><td>" +
                                partial_sc + "</td><td>" + partial_st + "</td><td>" +
                                partial_total + "</td><td>" + reverted_ot + "</td><td>" +
                                reverted_sc + "</td><td>" + reverted_st + "</td><td>" +
                                reverted_total + "</td><td>" + verification_pending_ot +
                                "</td><td>" + verification_pending_sc + "</td><td>" +
                                verification_pending_st + "</td><td>" + verification_total +
                                "</td><td>" + approval_pending_ot + "</td><td>" +
                                approval_pending_sc + "</td><td>" + approval_pending_st +
                                "</td><td>" + approval_pending_total + "</td><td>" + approved_ot +
                                "</td><td>" + approved_sc + "</td><td>" + approved_st +
                                "</td><td>" + approval_total + "</td><td>" + rejected + "</td></tr>"
                            );
                            //slno++;

                        });
                        $("#example > tfoot #fotter_id").html("<th></th><th>Total</th><th>" + footer_1 +
                            "</th><th>" + footer_2 + "</th><th>" + footer_3 + "</th><th>" + footer_4 +
                            "</th><th>" + footer_5 + "</th><th>" + footer_6 + "</th><th>" + footer_7 +
                            "</th><th>" + footer_8 + "</th><th>" + footer_9 + "</th><th>" + footer_10 +
                            "</th><th>" + footer_11 + "</th><th>" + footer_12 + "</th><th>" + footer_13 +
                            "</th><th>" + footer_14 + "</th><th>" + footer_15 + "</th><th>" + footer_16 +
                            "</th><th>" + footer_17 + "</th><th>" + footer_18 + "</th><th>" + footer_19 +
                            "</th><th>" + footer_20 + "</th><th>" + footer_21 + "</th><th>" + footer_22 +
                            "</th><th>" + footer_23 + "</th><th>" + footer_24 + "</th><th>" + footer_25 +
                            "</th>");

                        $("#example").show();
                        $('#example').tableTotal({
                            totalRow: true,
                            totalCol: false,
                        });


                    } else {
                        $('#search_details').hide();
                        $("#example").hide();
                        printMsg(data.return_msg, '0', 'errorDiv');
                    }
                    $("#submit_loader1").hide();
                    $("#submitting").show();

                },
                error: function(ex) {
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
