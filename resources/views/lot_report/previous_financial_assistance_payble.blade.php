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
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Financial Assistance Payable</h1>
                </div>
                {{-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <span style="font-size: 12px; font-weight: bold;">
                            <i class="fa fa-clock-o"></i> Date :
                            <span class="date-part"></span>
                            <span class="time-part"></span>
                        </span>
                    </ol>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Card -->
            <div class="card card-default">
                <div class="card-body">

                    <div id="loadingDiv"></div>

                    {{-- Success Message --}}
                    @if (($message = Session::get('success')))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>{{ $message }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if(count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="m-0">
                                @foreach($errors->all() as $error)
                                    <li><strong>{{ $error }}</strong></li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="row filterDiv mt-3">
                        <div class="col-md-12 d-flex flex-wrap">

                            <!-- Financial Year -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Select Financial Year <span class="text-danger">*</span></label>
                                <select class="form-select" name="financial_year" id="financial_year">
                                    <option value="">-- Select --</option>
                                    @foreach($fin_year as $year)
                                        <option value="{{ $year->db_schema_name }}">{{ $year->financial_year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Phase -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Select Phase</label>
                                <select class="form-select" name="phase_code" id="phase_code">
                                    <option value="">All</option>
                                    @foreach($ds_phase as $phase)
                                        <option value="{{ $phase->ds_phase }}">{{ $phase->ds_phase_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search Button -->
                            <div class="col-md-2 offset-md-1 mb-3 d-flex align-items-end">
                                <button type="button" name="filter" id="filter" class="btn btn-success w-100">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Result Section -->
                    <div class="table-responsive mt-4 resultDiv" id="result_div">

                        <div class="float-end text-sm mb-2" id="report_generation_text">
                            Report Generated on:
                            <b>
                                <?php
    date_default_timezone_set('Asia/Kolkata');
    echo date("l jS \of F Y h:i:s A");
                                    ?>
                            </b>
                        </div>

                        <button class="btn btn-info btn-sm exportToExcel mb-3" type="button">
                            Export to Excel
                        </button>

                        <p id="phase_month_list"></p>
                        <p id="caste_amount_list"></p>

                        <table id="example" class="table table-bordered table-hover table-striped table2excel"
                            cellspacing="0">

                            <thead class="table-light text-center" style="font-size: 13px;">
                                <tr>
                                    <th colspan="2"></th>
                                    <th colspan="2">Payment Lot Generation Pending</th>
                                </tr>
                                <tr>
                                    <th>Duare Sarkar<br>Phase<br>[1]</th>
                                    <th>Category<br>[2]</th>
                                    <th>Currently Active<br>Beneficiaries<br>[3]</th>
                                    <th>Amount<br>[4]</th>
                                </tr>
                            </thead>

                            <tbody style="font-size: 14px; text-align:right;"></tbody>

                            <tfoot style="font-size: 14px; font-weight: bold; text-align:right;">
                                <tr id="fotter_id"></tr>
                                <tr id="grand_total_fotter"></tr>
                                <tr>
                                    <td colspan="4" style="display:none;" id="fotter_excel">Heading</td>
                                </tr>
                            </tfoot>

                        </table>

                    </div> <!-- End resultDiv -->

                </div> <!-- card-body -->
            </div> <!-- card -->

        </div>
    </section>
@endsection


@push('scripts')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#loadingDiv').hide();
            $('#result_div').hide();
            $('#filter_div').removeClass('disabledcontent');
            $('#submit_btn').removeAttr('disabled');
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #paymentReportMain').addClass("active");
            $('.sidebar-menu #lotCreationPendingListReport').addClass("active");

            $('#filter').click(function () {
                var fin_year_schema_name = $('#financial_year').val();
                if (fin_year_schema_name != '') {
                    loadTable();
                } else {
                    alert('Please select financial year');
                }
            });

            $(".exportToExcel").click(function (e) {
                $(".table2excel").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Worksheet Name",
                    filename: "Monthwise Lot Creation Pending", //do not include extension
                    fileext: ".xls" // file extension
                });
            });

        });

        function loadTable() {
            $('#loadingDiv').show();
            var ds_phase = $('#phase_code').val();
            var fin_year_schema_name = $('#financial_year').val();

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: "{{ route('getPreviousFinancialAssistancePayable') }}",
                data: {
                    ds_phase: ds_phase,
                    db_scheme_name: fin_year_schema_name,
                    _token: '{{ csrf_token() }}',
                },
                success: function (data) {
                    $('#loadingDiv').hide();
                    // $('#result_div').show();
                    // console.log(data);
                    // console.log(JSON.stringify(data.payble_month));
                    // console.log(JSON.stringify(data.payble_month[2]));
                    if ($.fn.DataTable.isDataTable('#example')) {
                        $('#example').DataTable().destroy();
                    }
                    $("#example > tbody").html("");
                    var table = $("#example tbody");
                    $("#fotter_excel").html("<b>" + $('#report_generation_text').text() + "</b>");
                    var fotter_1 = 0; var fotter_2 = 0; var fotter_3 = 0; var fotter_4 = 0; var fotter_13 = 0; var fotter_18 = 0;
                    $.each(data.row_data, function (i, item) {
                        var total_beneficiary_payment = isNaN(parseInt(item.total_beneficiary_payment)) ? 0 : parseInt(item.total_beneficiary_payment);
                        var total_amount = isNaN(parseInt(item.total_amount)) ? 0 : parseInt(item.total_amount);
                        fotter_18 = fotter_18 + total_beneficiary_payment;
                        fotter_13 = fotter_13 + total_amount;

                        if (item.caste == 'OT') {
                            var caste = 'Others';
                        } else {
                            var caste = item.caste;
                        }

                        table.append("<tr class='" + item.ds_phase + "' id='" + item.caste + "_" + item.ds_phase + "'><td style='text-align:left;'>Phase " + item.ds_phase + "</td><td style='text-align:left;'>" + caste + "</td><td>" + total_beneficiary_payment + "</td><td>" + total_amount + "</td></tr>");

                    });

                    // var varPhaseMonthList='Assistance payable for no. of months : ';
                    // $.each(data.payble_month, function(i, item4) {
                    //   varPhaseMonthList += 'Phase '+item4.ds_phase+' - '+item4.pay_month+', ';
                    // });
                    // $('#phase_month_list').html('<span class="text-primary"><b>'+varPhaseMonthList.slice(0,-2)+'</b></span>');

                    $("#example > tfoot #fotter_id").html("<th style='text-align:left;'>Total</th><th style='text-align:center;'>-</th><th style='text-align:right;'>" + fotter_18 + "</th><th style='text-align:right;'>" + fotter_13 + "</th>");
                    var grand_total = fotter_13;
                    $("#example > tfoot #grand_total_fotter").html("<th style='text-align:left;'>Approx. Grand Total :- </th><th colspan='9' style='text-align:left;'>" + grand_total + "</th>");
                    $('#result_div').show();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingDiv').hide();
                    ajax_error(jqXHR, textStatus, errorThrown);
                }
            });
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
