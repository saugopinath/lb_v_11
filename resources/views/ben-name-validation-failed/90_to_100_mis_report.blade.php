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
                                <div class="form-group col-md-3">
                                    <label for="cars" class="required-field">Choose Score:</label>
                                    <select name="minor_mismatch" id="minor_mismatch" class="form-control">
                                        <option value="1">90% - 100%</option>
                                        <option value="2">40% - 89%</option>
                                    </select>
                                    <span id="error_minor_mismatch" class="text-danger"></span>
                                </div>
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
                                        <label>Rural/ Urban</label>

                                        <select name="urban_code" id="urban_code" class="form-control" tabindex="11">
                                            <option value="">--All --</option>
                                            @foreach (Config::get('constants.rural_urban') as $key => $val)
                                                <option value="{{ $key }}"
                                                    @if (old('urban_code') == $key) selected @endif>
                                                    {{ $val }}</option>
                                            @endforeach

                                        </select>
                                        <span id="error_urban_code" class="text-danger"></span>
                                    </div>
                                @else
                                    <input type="hidden" name="urban_code" id="urban_code" value="{{ $rural_urban_fk }}" />
                                @endif
                                @if ($block_visible)
                                    <div class="form-group col-md-4" id="divBodyCode">
                                        <label class="" id="blk_sub_txt">Block/Sub
                                            Division.</label>

                                        <select name="block" id="block" class="form-control" tabindex="16">
                                            <option value="">--All --</option>


                                        </select>
                                        <span id="error_block" class="text-danger"></span>
                                    </div>
                                @else
                                    <input type="hidden" name="block" id="block"
                                        value="{{ $block_munc_corp_code_fk }}" />
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

                                    <div class=""><img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1"
                                            width="50px" height="50px" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive resultDiv mt-3" id="search_details" style="display:none;">
                    <button class="btn btn-info exportToExcel  btn-action" type="button">Export to
                        Excel</button><br /><br /><br />
                    <table id="example" class="display data-table table2excel" cellspacing="0" width="100%"
                        style="border: 1px solid ghostwhite;">
                        <thead style="font-size: 12px;">
                            <tr role="row">
                                <th id="">Sl No.(A)
                                </th>
                                <th id="location_id">District</th>
                                <th>Total</th>
                                <th>Verified as Minor Mismatch but Approval Pending</th>
                                <th>Verified & Approved as Minor Mismatch</th>
                                <th>Rejected</th>
                                <th>Pending</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px;"></tbody>
                        <tfoot>
                            <tr id="fotter_id"></tr>
                            <tr>
                                <td colspan="21" align="center" style="display:none;" id="fotter_excel">Heading</td>
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
            // $('.sidebar-menu #lk-main').addClass("active"); 
            $('.sidebar-menu #misReportWithMinorMismatch').addClass("active");
            //loadDataTable();
            $(".exportToExcel").click(function(e) {
                // alert($('#minor_mismatch').val());
                if ($('#minor_mismatch').val() == 1) {
                    var score = "90% to 100%";
                } else if ($('#minor_mismatch').val() == 2) {
                    var score = "40% to 89%";
                } else {}
                $(".table2excel").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Worksheet Name",
                    filename: "Lakshmir Bhandar Minor Mismatch(" + score +
                    ") MIS Report", //do not include extension
                    fileext: ".xls" // file extension
                });
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

                var error_minor_mismatch = '';
                // var error_urban_code = '';
                // var error_from_date = '';
                // var error_to_date = '';

                var minor_mismatch = $("#minor_mismatch").val();
                // var urban_code = $("#urban_code").val();
                // var from_date = $("#from_date").val();
                // var to_date = $("#to_date").val();
                // if (ds_phase != '') {
                //     error_ds_phase = '';
                //     $('#error_ds_phase').text(error_ds_phase);
                //     $('#error_ds_phase').removeClass('has-error');
                // } else {
                //     error_ds_phase = 'DS Phase is required.';
                //     $('#error_ds_phase').text(error_ds_phase);
                //     $('#ds_phase').addClass('has-error');
                // }

                if (minor_mismatch != '') {
                    error_minor_mismatch = '';
                    $('#error_minor_mismatch').text(error_minor_mismatch);
                    $('#error_minor_mismatch').removeClass('has-error');
                } else {
                    error_minor_mismatch = 'Minor Mismatch Score is required.';
                    $('#error_minor_mismatch').text(error_minor_mismatch);
                    $('#minor_mismatch').addClass('has-error');
                }

                // if (from_date != '') {
                //     error_from_date = '';
                //     $('#error_from_date').text(error_from_date);
                //     $('#error_from_date').removeClass('has-error');
                // } else {
                //     error_from_date = 'From date is required.';
                //     $('#error_from_date').text(error_from_date);
                //     $('#from_date').addClass('has-error');
                // }

                // if (to_date != '') {
                //     error_to_date = '';
                //     $('#error_to_date').text(error_to_date);
                //     $('#error_to_date').removeClass('has-error');
                // } else {
                //     error_to_date = 'To date is required.';
                //     $('#error_to_date').text(error_to_date);
                //     $('#to_date').addClass('has-error');
                // }
                if (error_minor_mismatch != '') {
                    return false;
                } else {
                    loadDataTable();
                }
            });
        });

        function loadDataTable() {
            var minor_mismatch = $("#minor_mismatch").val();
            var district = $('#district').val();
            var urban_code = $('#urban_code').val();
            var block = $('#block').val();
            var gp_ward = $('#gp_ward').val();
            var muncid = $('#muncid').val();

            $("#submit_loader1").show();
            $("#submitting").hide();
            $('#search_details').hide();
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: "{{ route('getMis90to100') }}",
                data: {
                    minor_mismatch: minor_mismatch,
                    district: district,
                    urban_code: urban_code,
                    block: block,
                    gp_ward: gp_ward,
                    // from_date: from_date,
                    // to_date: to_date,
                    muncid: muncid,
                    //   caste_category: caste_category,
                    _token: '{{ csrf_token() }}',
                },
                success: function(data) {

                    //alert(data.title);
                    if (data.return_status) {
                        $('#search_details').show();
                        $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
                        $("#heading_excel").html("<b>" + data.heading_msg + "</b>");
                        $("#fotter_excel").html("<b>" + $('#report_generation_text').text() + "</b>");
                        $("#location_id").text(data.column + '(B)');
                        $("#report_generation_text").text(data.report_geneartion_time);
                        $("#example > tbody").html("");
                        var table = $("#example tbody");
                        var slno = 1;
                        var fotter_1 = 0;
                        var fotter_2 = 0;
                        var fotter_3 = 0;
                        var fotter_4 = 0;
                        var fotter_5 = 0;
                        //  var fotter_6=0;var fotter_7=0;
                        $.each(data.row_data, function(i, item) {
                            var total = isNaN(parseInt(item.total)) ? 0 : parseInt(item.total);
                            var verified_approver_pending = isNaN(parseInt(item.approval_pending)) ? 0 :
                                parseInt(item.approval_pending);
                            var approved_and_verified = isNaN(parseInt(item.approved)) ? 0 : parseInt(
                                item.approved);
                            var rejected = isNaN(parseInt(item.rejected)) ? 0 : parseInt(item.rejected);
                            var pending = total - (verified_approver_pending + approved_and_verified +
                                rejected);
                            // var approved_f = isNaN(parseInt(item.approved_f)) ? 0 : parseInt(item
                            //     .approved_f);

                            // var total_minor_mismatch = yet_to_be_action + approval_pending + approved;

                            fotter_1 = fotter_1 + total;
                            fotter_2 = fotter_2 + verified_approver_pending;
                            fotter_3 = fotter_3 + approved_and_verified;
                            fotter_4 = fotter_4 + rejected;
                            fotter_5 = fotter_5 + pending;
                            //  fotter_6=fotter_6+verified_sum_n_f;
                            //  fotter_7=fotter_7+verified_sum;

                            table.append("<tr><td>" + (i + 1) + "</td><td>" + item.block_subdiv_name +
                                "</td><td>" + total + "</td><td>" + verified_approver_pending +
                                "</td><td>" + approved_and_verified + "</td><td>" + rejected +
                                "</td><td>" + pending + "</td></tr>");
                            //slno++;

                        });

                        $("#example > tfoot #fotter_id").html("<th></th><th>Total</th><th>" + fotter_1 +
                            "</th><th>" + fotter_2 + "</th><th>" + fotter_3 + "</th><th>" + fotter_4 +
                            "</th><th>" + fotter_5 + "</th>");
                        //$('#example tbody').empty();
                        $("#example").show();


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
