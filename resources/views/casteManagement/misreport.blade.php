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
                                {{-- <div class="row"> --}}
                                <div class="form-group col-md-3">
                                    <label class="">From Caste</label>
                                    <select class="form-control" name="old_caste_category" id="old_caste_category">
                                        <option value="">--All--</option>
                                        @foreach (Config::get('constants.caste_lb') as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error_old_caste_category" class="text-danger"></span>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="">To Caste</label>
                                    <select class="form-control" name="new_caste_category" id="new_caste_category">
                                        <option value="">--All--</option>
                                        <option id="SCST" value="SC & ST">{{ 'SC & ST' }}</option>
                                        @foreach (Config::get('constants.caste_lb') as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error_new_caste_category" class="text-danger"></span>
                                </div>
                                {{-- </div> --}}
                                <div class="form-group col-md-3" style="margin-top: 32px;">
                                    <button type="button" id="submitting" value="Submit"
                                        class="btn btn-success success btn-lg modal-search form-submitted  btn-action">Search
                                    </button>

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
                                <th>Sl No.(A)</th>
                                <th id="location_id">District</th>
                                <th>Total beneficiaries on which caste info modification has been made(C)</th>
                                <th>Total Verified(D)</th>
                                <th>Total Yet to Be Verified(E)</th>
                                <th>Total Approved(F)</th>
                                <th>Total Yet to Be Approved(G)</th>
                                <th>Total Rejected(H)</th>
                                <th>Total Reverted(I)</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px;"></tbody>
                        <tfoot>
                            <tr id="fotter_id"></tr>
                            <tr>
                                <td colspan="21" align="center" style="display:none;" id="fotter_excel_new">Heading
                                </td>
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
        //alert(base_date);

        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #lb-caste').addClass("active");
            $('.sidebar-menu #casteInfoMis').addClass("active");
            loadDataTable();
            $(".exportToExcel").click(function(e) {
                // alert('ok');
                $(".table2excel").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Worksheet Name",
                    filename: "Lakshmir Bhandar Caste Info Modification Mis Report", //do not include extension
                    fileext: ".xls" // file extension
                });
            });


            $(".exportToExcel_new").click(function(e) {
                //alert('ok');
                $(".table3excel").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Worksheet Name",
                    filename: "Lakshmir Bhandar Caste Info Modification Mis Report", //do not include extension
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

            $('#old_caste_category').change(function() {

                var val = $(this).val();
                select_new_caste = $('#new_caste_category').val();

                // if(val=='OTHERS' )
                // {
                //   $('#SCST').prop('disabled', false);

                // }
                // else{
                //   $('#SCST').prop('disabled', true);


                // }


                if (select_new_caste == 'SC & ST' && val !== 'OTHERS') {


                    $("#new_caste_category").val('');
                    alert('Please Select TO Caste Except SC & ST ');
                    $("#new_caste_category").focus();

                } else if (val == 'OTHERS') {
                    $('#SCST').prop('disabled', false);

                } else {

                    $('#SCST').prop('disabled', true);


                }


            });

            $('#new_caste_category').change(function() {

                var val = $(this).val();

                select_old_caste = $('#old_caste_category').val();
                if (select_old_caste != 'OTHERS' && val == 'SC & ST') {
                    $("#new_caste_category").val('');
                    alert('Please Select From Caste to OTHERS');
                    $("#old_caste_category").focus();

                }

                // if(val=='SC & ST')
                // {
                //   $('#SCST').prop('disabled', false);

                // }
                // else{
                //   $('#SCST').prop('disabled', true);

                // }


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
            var district = $('#district').val();
            var urban_code = $('#urban_code').val();
            var block = $('#block').val();
            var gp_ward = $('#gp_ward').val();
            var muncid = $('#muncid').val();
            var old_caste_category = $('#old_caste_category').val();
            var new_caste_category = $('#new_caste_category').val();


            $("#submit_loader1").show();
            $("#submitting").hide();
            $('#search_details').hide();
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '{{ url('casteInfoMisPost') }}',
                data: {
                    district: district,
                    urban_code: urban_code,
                    block: block,
                    gp_ward: gp_ward,
                    muncid: muncid,
                    old_caste: old_caste_category,
                    new_caste: new_caste_category,
                    _token: '{{ csrf_token() }}',
                },
                success: function(data) {

                    // alert(data.visible);

                    // if(data.visible==1)
                    // {
                    //   $("#new_caste").show();
                    //   $("#caste").hide();

                    // }
                    // if(data.visible==0)
                    // {
                    //   $("#new_caste").hide();
                    //   $("#caste").show();

                    // }



                    if (data.visible == 0) {
                        $('#search_details').show();
                        $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
                        $("#heading_excel").html("<b>" + data.heading_msg + "</b>");
                        $("#fotter_excel").html("<b>" + $('#report_generation_text').text() + "</b>");
                        $("#location_id").text(data.column + '(B)');
                        $("#example > tbody").html("");
                        var table = $("#example tbody");
                        var slno = 1;
                        var fotter_1 = 0;
                        var fotter_2 = 0;
                        var fotter_3 = 0;
                        var fotter_4 = 0;
                        var fotter_5 = 0;
                        var fotter_6 = 0;
                        var fotter_7 = 0;
                        var fotter_8 = 0;
                        $.each(data.row_data, function(i, item) {
                            var tot_ben = isNaN(parseInt(item.tot_ben)) ? 0 : parseInt(item.tot_ben);
                            var total_verified = isNaN(parseInt(item.total_verified)) ? 0 : parseInt(
                                item.total_verified);
                            var total_yet_tobe_verified = isNaN(parseInt(item
                                .total_yet_tobe_verified)) ? 0 : parseInt(item
                                .total_yet_tobe_verified);
                            var total_approved = isNaN(parseInt(item.total_approved)) ? 0 : parseInt(
                                item.total_approved);
                            var total_yet_tobe_approved = isNaN(parseInt(item
                                .total_yet_tobe_approved)) ? 0 : parseInt(item
                                .total_yet_tobe_approved);
                            var total_rejected = isNaN(parseInt(item.total_rejected)) ? 0 : parseInt(
                                item.total_rejected);
                            var total_reverted = isNaN(parseInt(item.total_reverted)) ? 0 : parseInt(
                                item.total_reverted);
                            var total_verified1 = total_approved + total_yet_tobe_approved;
                            fotter_1 = fotter_1 + tot_ben;
                            fotter_2 = fotter_2 + total_verified1;
                            fotter_3 = fotter_3 + total_yet_tobe_verified;
                            fotter_4 = fotter_4 + total_approved;
                            fotter_5 = fotter_5 + total_yet_tobe_approved;
                            fotter_6 = fotter_6 + total_rejected;
                            fotter_8 = fotter_8 + total_reverted;
                            table.append("<tr><td>" + (i + 1) + "</td><td>" + item.location_name +
                                "</td><td>" + tot_ben + "</td><td>" + total_verified1 +
                                "</td><td>" + total_yet_tobe_verified + "</td><td>" +
                                total_approved + "</td><td>" + total_yet_tobe_approved +
                                "</td><td>" + total_rejected + "</td><td>" + total_reverted +
                                "</td></tr>");
                            //slno++;

                        });

                        $("#example > tfoot #fotter_id").html("<th></th><th>Total</th><th>" + fotter_1 +
                            "</th><th>" + fotter_2 + "</th><th>" + fotter_3 + "</th><th>" + fotter_4 +
                            "</th><th>" + fotter_5 + "</th><th>" + fotter_6 + "</th><th>" + fotter_8 +
                            "</th>");
                        //$('#example tbody').empty();
                        $("#example").show();
                        $("#example_new").hide();
                        $("#exportToExcel").show();
                        $("#exportToExcel_new").hide();


                    } else if (data.visible == 1) {


                        $('#search_details').show();
                        $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
                        $("#heading_excel_new").html("<b>" + data.heading_msg + "</b>");
                        $("#fotter_excel_new").html("<b>" + $('#report_generation_text').text() + "</b>");
                        $("#location_id_new").text(data.column + '(B)');
                        $("#example_new > tbody").html("");
                        var table = $("#example_new tbody");
                        var slno = 1;
                        var fotter_1 = 0;
                        var fotter_2 = 0;
                        var fotter_3 = 0;
                        var fotter_4 = 0;
                        var fotter_5 = 0;
                        var fotter_6 = 0;
                        var fotter_7 = 0;
                        var fotter_8 = 0;
                        var fotter_9 = 0;
                        var fotter_10 = 0;
                        var fotter_11 = 0;
                        var fotter_12 = 0;
                        var fotter_13 = 0;
                        var fotter_14 = 0;
                        var fotter_15 = 0;
                        $.each(data.row_data, function(i, item) {
                            var tot_ben_sc = isNaN(parseInt(item.tot_ben_sc)) ? 0 : parseInt(item
                                .tot_ben_sc);
                            var total_verified_sc = isNaN(parseInt(item.total_verified_sc)) ? 0 :
                                parseInt(item.total_verified_sc);
                            var total_yet_tobe_verified_sc = isNaN(parseInt(item
                                .total_yet_tobe_verified_sc)) ? 0 : parseInt(item
                                .total_yet_tobe_verified_sc);
                            var total_approved_sc = isNaN(parseInt(item.total_approved_sc)) ? 0 :
                                parseInt(item.total_approved_sc);
                            var total_yet_tobe_approved_sc = isNaN(parseInt(item
                                .total_yet_tobe_approved_sc)) ? 0 : parseInt(item
                                .total_yet_tobe_approved_sc);
                            var total_rejected_sc = isNaN(parseInt(item.total_rejected_sc)) ? 0 :
                                parseInt(item.total_rejected_sc);
                            var total_reverted_sc = isNaN(parseInt(item.total_reverted_sc)) ? 0 :
                                parseInt(item.total_reverted_sc);
                            var total_verified1_sc = total_approved_sc + total_yet_tobe_approved_sc;

                            var tot_ben_st = isNaN(parseInt(item.tot_ben_st)) ? 0 : parseInt(item
                                .tot_ben_st);
                            var total_verified_st = isNaN(parseInt(item.total_verified_st)) ? 0 :
                                parseInt(item.total_verified_st);
                            var total_yet_tobe_verified_st = isNaN(parseInt(item
                                .total_yet_tobe_verified_st)) ? 0 : parseInt(item
                                .total_yet_tobe_verified_st);
                            var total_approved_st = isNaN(parseInt(item.total_approved_st)) ? 0 :
                                parseInt(item.total_approved_st);
                            var total_yet_tobe_approved_st = isNaN(parseInt(item
                                .total_yet_tobe_approved_st)) ? 0 : parseInt(item
                                .total_yet_tobe_approved_st);
                            var total_rejected_st = isNaN(parseInt(item.total_rejected_st)) ? 0 :
                                parseInt(item.total_rejected_st);
                            var total_reverted_st = isNaN(parseInt(item.total_reverted_st)) ? 0 :
                                parseInt(item.total_reverted_st);
                            var total_verified1_st = total_approved_st + total_yet_tobe_approved_st;


                            fotter_1 = fotter_1 + tot_ben_sc;
                            fotter_2 = fotter_2 + total_verified1_sc;
                            fotter_3 = fotter_3 + total_yet_tobe_verified_sc;
                            fotter_4 = fotter_4 + total_approved_sc;
                            fotter_5 = fotter_5 + total_yet_tobe_approved_sc;
                            fotter_6 = fotter_6 + total_rejected_sc;
                            fotter_8 = fotter_8 + total_reverted_sc;

                            fotter_9 = fotter_9 + tot_ben_st;
                            fotter_10 = fotter_10 + total_verified1_st;
                            fotter_11 = fotter_11 + total_yet_tobe_verified_st;
                            fotter_12 = fotter_12 + total_approved_st;
                            fotter_13 = fotter_13 + total_yet_tobe_approved_st;
                            fotter_14 = fotter_14 + total_rejected_st;
                            fotter_15 = fotter_15 + total_reverted_st;
                            table.append("<tr><td>" + (i + 1) + "</td><td>" + item.location_name +
                                "</td><td>" + tot_ben_sc + "</td><td>" + total_verified1_sc +
                                "</td><td>" + total_yet_tobe_verified_sc + "</td><td>" +
                                total_approved_sc + "</td><td>" + total_yet_tobe_approved_sc +
                                "</td><td>" + total_rejected_sc + "</td><td>" + total_reverted_sc +
                                "</td><td>" + tot_ben_st + "</td><td>" + total_verified1_st +
                                "</td><td>" + total_yet_tobe_verified_st + "</td><td>" +
                                total_approved_st + "</td><td>" + total_yet_tobe_approved_st +
                                "</td><td>" + total_rejected_st + "</td><td>" + total_reverted_st +
                                "</td></tr>");
                            //slno++;

                        });

                        $("#example_new > tfoot #fotter_id").html("<th></th><th>Total</th><th>" + fotter_1 +
                            "</th><th>" + fotter_2 + "</th><th>" + fotter_3 + "</th><th>" + fotter_4 +
                            "</th><th>" + fotter_5 + "</th><th>" + fotter_6 + "</th><th>" + fotter_8 +
                            "</th><th>" + fotter_9 + "</th><th>" + fotter_10 + "</th><th>" + fotter_11 +
                            "</th><th>" + fotter_12 + "</th><th>" + fotter_13 + "</th><th>" + fotter_14 +
                            "</th><th>" + fotter_15 + "</th>");
                        //$('#example tbody').empty();
                        $("#example").hide();
                        $("#example_new").show();
                        $("#exportToExcel").hide();
                        $("#exportToExcel_new").show();



                    } else {
                        $('#search_details').hide();
                        $("#example").hide();
                        $("#example_new").hide();
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
