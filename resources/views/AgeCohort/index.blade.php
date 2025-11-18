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
                                @if (count($ds_phase_list) > 0)
                                    <div class="form-group col-md-4">
                                        <label class="">Duare Sarkar Phase</label>
                                        <select class="form-control" name="ds_phase" id="ds_phase" tabindex="70">
                                            <option value="">--All--</option>
                                            @foreach ($ds_phase_list as $key => $val)
                                                <option value="{{ $key }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                        <span id="error_ds_phase" class="text-danger"></span>
                                    </div>
                                @else
                                    <input type="hidden" name="ds_phase" id="ds_phase" value="" />
                                @endif

                                <div class="form-group col-md-3" style="margin-top: 32px;">
                                    <button type="button" id="submitting" value="Submit"
                                        class="btn btn-success success btn-lg modal-search form-submitted btn-action">
                                        <i class="fas fa-search"></i> Search
                                    </button>

                                    <div class=""><img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1"
                                            width="50px" height="50px" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive resultDiv mt-3" id="search_details" style="display:none;">
                    <button class="btn btn-info exportToExcel btn-action" type="button">Export to
                        Excel</button><br /><br /><br />
                    <table id="example" class="display data-table table2excel" cellspacing="0" width="100%"
                        style="border: 1px solid ghostwhite;">
                        <thead style="font-size: 12px;">
                            <tr role="row">
                                <th id="">Sl No</th>
                                <th id="location_id">District</th>
                                <th>Age 25 to 35 years</th>
                                <th>Age 35+ to 45 years</th>
                                <th>Age 45+ to 55 years</th>
                                <th>Age 55+ to 60 years</th>
                                <th>Age 60+ years</th>
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
        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #mis-report').addClass("active");
            //loadDataTable();
            $(".exportToExcel").click(function(e) {
                // alert('ok');
                $(".table2excel").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Worksheet Name",
                    filename: "Age Cohort Lakshmir Bhandar Mis Report", //do not include extension
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




            $('.modal-search').on('click', function() {

                loadDataTable();


            });
        });

        function loadDataTable() {
            var ds_phase = $('#ds_phase').val();


            $("#submit_loader1").show();
            $("#submitting").hide();
            $('#search_details').hide();
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '{{ url('ageCohortPost') }}',
                data: {
                    ds_phase: ds_phase,
                    _token: '{{ csrf_token() }}',
                },
                success: function(data) {

                    //alert(data.title);
                    if (data.return_status) {
                        $('#search_details').show();
                        $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
                        $("#heading_excel").html("<b>" + data.heading_msg + "</b>");
                        $("#fotter_excel").html("<b>" + $('#report_generation_text').text() + "</b>");
                        $("#location_id").text(data.column);
                        $("#example > tbody").html("");
                        var table = $("#example tbody");
                        var slno = 1;
                        var fotter_1 = 0;
                        var fotter_2 = 0;
                        var fotter_3 = 0;
                        var fotter_4 = 0;
                        var fotter_5 = 0;
                        $.each(data.row_data, function(i, item) {
                            var age_25_35 = isNaN(parseInt(item.age_25_35)) ? 0 : parseInt(item
                                .age_25_35);
                            var age_35_45 = isNaN(parseInt(item.age_35_45)) ? 0 : parseInt(item
                                .age_35_45);
                            var age_45_55 = isNaN(parseInt(item.age_45_55)) ? 0 : parseInt(item
                                .age_45_55);
                            var age_55_60 = isNaN(parseInt(item.age_55_60)) ? 0 : parseInt(item
                                .age_55_60);
                            var age_60 = isNaN(parseInt(item.age_60)) ? 0 : parseInt(item.age_60);
                            fotter_1 = fotter_1 + age_25_35;
                            fotter_2 = fotter_2 + age_35_45;
                            fotter_3 = fotter_3 + age_45_55;
                            fotter_4 = fotter_4 + age_55_60;
                            fotter_5 = fotter_5 + age_60;
                            table.append("<tr><td>" + (i + 1) + "</td><td>" + item.location_name +
                                "</td><td>" + age_25_35 + "</td><td>" + age_35_45 + "</td><td>" +
                                age_45_55 + "</td><td>" + age_55_60 + "</td><td>" + age_60 +
                                "</td></tr>");
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
