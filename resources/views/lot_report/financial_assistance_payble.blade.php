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

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Financial Assistance Payable</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="card card-default">
                <div class="card-body">

                    <div id="loadingDiv"></div>

                    {{-- FILTER SECTION (HIDDEN AS IN ORIGINAL) --}}
                    {{--
                    <div class="filterDiv">
                        <div class="row">
                            @if(($message = Session::get('success')))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
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
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <label class="form-label">Select Phase</label>
                                    <select class="form-select" name="phase_code" id='phase_code'>
                                        <option value="">All</option>
                                        @foreach($ds_phase as $phase)
                                        <option value="{{$phase->ds_phase}}">{{$phase->ds_phase_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 offset-md-1" style="margin-top: 26px;">
                                    <button type="button" name="filter" id="filter" class="btn btn-success">
                                        <i class="fa fa-search"></i> Search
                                    </button>

                                    <button type="button" name="reset" id="reset" class="btn btn-warning">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    --}}

                    <div class="table-responsive resultDiv" id="result_div">

                        <div class="text-end mb-2" id="report_generation_text" style="font-size: 12px;">
                            Report Generated on:
                            <b>
                                <?php date_default_timezone_set('Asia/Kolkata');
    echo date("l jS \\o\\f F Y h:i:s A"); ?>
                            </b>
                        </div>

                        <button class="btn btn-info mb-3 exportToExcel" type="button">
                            Export to Excel
                        </button>

                        <p id="caste_amount_list"></p>

                        <table id="example" class="data-table  table2excel">
                            <thead class="table-light" style="font-size: 12px;">
                                <tr>
                                    <th colspan="1"></th>
                                    <th colspan="2" class="text-center">Payment Lot Generation Pending</th>
                                    <th colspan="2" class="text-center">Bank Account Validation Pending</th>
                                    <th colspan="4" class="text-center">Application Approval Pending</th>
                                </tr>

                                <tr>
                                    <th>Category<br>[2]</th>

                                    <th>Currently Active<br>Beneficiaries<br>[3]</th>
                                    <th>Amount<br>[4]</th>

                                    <th>Currently Active<br>Beneficiaries<br>[5]</th>
                                    <th>Amount <br>(Approx.)<br>[6]</th>

                                    <th>Approval <br>Pending<br> Beneficiaries<br>[9]</th>
                                    <th>Amount <br>(Approx. Only <br>for one month)<br>[10]</th>
                                </tr>
                            </thead>

                            <tbody style="font-size: 14px; text-align:right;"></tbody>

                            <tfoot style="font-size: 14px; font-weight: bold; text-align:right;">
                                <tr id="fotter_id"></tr>
                                <tr id="grand_total_fotter"></tr>
                                <tr>
                                    <td colspan="10" style="display:none;" id="fotter_excel">Heading</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </section>

@endsection

@push("scripts")
    <script>
        $(document).ready(function () {
            $('#loadingDiv').hide();
            // $('#result_div').hide();
            $('#filter_div').removeClass('disabledcontent');
            $('#submit_btn').removeAttr('disabled');
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #paymentReportMain').addClass("active");
            $('.sidebar-menu #lotCreationPendingListReport').addClass("active");

            // $('#filter').click(function(){
            //   loadTable();
            //   // var ds_phase = $('#phase_code').val();
            //   // if(ds_phase != '') {

            //   // }
            //   // else {
            //   //   alert('Select Duare Sarkar Phase');
            //   // }
            // });

            loadTable();

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
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: "{{ route('lotGeneratedPendingAmountReport') }}",
                data: {
                    ds_phase: ds_phase,
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
                    var fotter_1 = 0; var fotter_2 = 0; var fotter_3 = 0; var fotter_4 = 0; var fotter_5 = 0; var fotter_6 = 0; var fotter_7 = 0;
                    var fotter_8 = 0; var fotter_9 = 0; var fotter_10 = 0; var fotter_11 = 0; var fotter_12 = 0; var fotter_13 = 0; var fotter_14 = 0;
                    var fotter_15 = 0; var fotter_16 = 0; var fotter_17 = 0; var fotter_18 = 0;
                    $.each(data.row_data, function (i, item) {
                        // console.log(item.total_amount);
                        // var apr = isNaN(parseInt(item.apr_status)) ? 0 : parseInt(item.apr_status);
                        // var may = isNaN(parseInt(item.may_status)) ? 0 : parseInt(item.may_status);
                        // var jun = isNaN(parseInt(item.jun_status)) ? 0 : parseInt(item.jun_status);
                        // var jul = isNaN(parseInt(item.jul_status)) ? 0 : parseInt(item.jul_status);
                        // var aug = isNaN(parseInt(item.aug_status)) ? 0 : parseInt(item.aug_status);
                        // var sep = isNaN(parseInt(item.sep_status)) ? 0 : parseInt(item.sep_status);
                        // var oct = isNaN(parseInt(item.oct_status)) ? 0 : parseInt(item.oct_status);
                        // var nov = isNaN(parseInt(item.nov_status)) ? 0 : parseInt(item.nov_status);
                        // var dec = isNaN(parseInt(item.dec_status)) ? 0 : parseInt(item.dec_status);
                        // var jan = isNaN(parseInt(item.jan_status)) ? 0 : parseInt(item.jan_status);
                        // var feb = isNaN(parseInt(item.feb_status)) ? 0 : parseInt(item.feb_status);
                        // var mar = isNaN(parseInt(item.mar_status)) ? 0 : parseInt(item.mar_status);
                        var total_beneficiary_payment = isNaN(parseInt(item.total_beneficiary_payment)) ? 0 : parseInt(item.total_beneficiary_payment);
                        var total_amount = isNaN(parseInt(item.total_amount)) ? 0 : parseInt(item.total_amount);

                        // fotter_1 = fotter_1+apr;
                        // fotter_2 = fotter_2+may;
                        // fotter_3 = fotter_3+jun;
                        // fotter_4 = fotter_4+jul;
                        // fotter_5 = fotter_5+aug;
                        // fotter_6 = fotter_6+sep;
                        // fotter_7 = fotter_7+oct;
                        // fotter_8= fotter_8+nov;
                        // fotter_9 = fotter_9+dec;
                        // fotter_10 = fotter_10+jan;
                        // fotter_11 = fotter_11+feb;
                        // fotter_12 = fotter_12+mar;
                        fotter_18 = fotter_18 + total_beneficiary_payment;
                        fotter_13 = fotter_13 + total_amount;

                        if (item.caste == 'OT') {
                            var caste = 'Others';
                        } else {
                            var caste = item.caste;
                        }

                        // Filtering the object with ds_phase equal to 5
                        // var filteredData = $.grep(data.payble_month, function(element, index) {
                        //     return element.ds_phase === item.ds_phase;
                        // });

                        // Accessing ds_phase_name from the filtered data
                        // if (item.ds_phase == null) {
                        //   var phase_name_val = 'Normal Entry';
                        //   var item_ds_phase = 0;
                        // } else {
                        //   var phase_name_val = filteredData[0].ds_phase_name_val;
                        //   var item_ds_phase = item.ds_phase;
                        // }

                        // console.log(item_ds_phase, item.caste, item_ds_phase, phase_name_val, caste, total_beneficiary_payment, total_amount);

                        // table.append("<tr class='"+item.ds_phase+"' id='"+item.caste+"_"+item.ds_phase+"'><td style='text-align:left;'>Phase "+item.ds_phase+"</td><td style='text-align:left;'>"+caste+"</td><td>"+apr+"</td><td>"+may+"</td><td>"+jun+"</td><td>"+jul+"</td><td>"+aug+"</td><td>"+sep+"</td><td>"+oct+"</td><td>"+nov+"</td><td>"+dec+"</td><td>"+jan+"</td><td>"+feb+"</td><td>"+mar+"</td><td>"+total_beneficiary_payment+"</td><td>"+total_amount+"</td></tr>");

                        table.append("<tr class='" + item.caste + "' id='" + item.caste + "'><td style='text-align:left;'>" + caste + "</td><td>" + total_beneficiary_payment + "</td><td>" + total_amount + "</td></tr>");

                    });

                    $.each(data.val_data, function (i, item1) {
                        // console.log(item.total_amount);
                        var validation_pending = isNaN(parseInt(item1.validation_pending)) ? 0 : parseInt(item1.validation_pending);
                        var validation_pending_amount = isNaN(parseInt(item1.validation_pending_amount)) ? 0 : parseInt(item1.validation_pending_amount);

                        fotter_14 = fotter_14 + validation_pending;
                        fotter_15 = fotter_15 + validation_pending_amount;

                        // alert(item1.validation_pending);
                        // Accessing ds_phase_name from the filtered data
                        // if (item1.ds_phase == null) {
                        //   var item1_ds_phase = 0;
                        // } else {
                        //   var item1_ds_phase = item1.ds_phase;
                        // }

                        // console.log(item1.caste, item1_ds_phase, validation_pending, validation_pending_amount);

                        $('#' + item1.caste + '').append("<td>" + validation_pending + "</td><td>" + validation_pending_amount + "</td>");

                    });

                    var varAmountMaster = 'Financial assistance payable (Rs.) : ';
                    $.each(data.amount_master_data, function (i, item2) {
                        if (item2.caste == 'OT') {
                            var temp_caste = 'Others';
                        } else {
                            var temp_caste = item2.caste;
                        }
                        varAmountMaster += temp_caste + ' - ' + item2.amount + ', ';
                    });
                    $('#caste_amount_list').html('<span class="text-primary"><b>' + varAmountMaster.slice(0, -2) + '</b></span>');

                    var varPhaseMonthList = 'Assistance payable for no. of months : ';
                    // $.each(data.payble_month, function(i, item4) {
                    //   varPhaseMonthList += ''+item4.ds_phase_name_val+' - '+item4.pay_month+', ';
                    // });
                    // console.log(data.approval_pending);
                    // console.log(typeof(varPhaseMonthList));
                    // $('#phase_month_list').html('<span class="text-primary"><b>'+varPhaseMonthList.slice(0,-2)+'</b></span>');

                    $.each(data.approval_pending, function (i, item3) {
                        var total = isNaN(parseInt(item3.total)) ? 0 : parseInt(item3.total);
                        var caste_amount_item3 = isNaN(parseInt(item3.phase_amount)) ? 0 : parseInt(item3.phase_amount);
                        /*var total_pay_month=0;
                        $.each(data.payble_month, function(i, item5) {
                          if (item5.ds_phase == item3.ds_phase) {
                            total_pay_month=item5.pay_month;
                          }
                        });*/
                        var phase_amount_master = 0;
                        $.each(data.amount_master_data, function (i, item6) {
                            if (item6.caste == item3.caste) {
                                phase_amount_master = item6.amount;
                            }
                        });
                        // console.log(phase_amount_master);
                        // console.log(total);


                        var total_pay_approval_pending = total * phase_amount_master;

                        fotter_16 = fotter_16 + total;
                        fotter_17 = fotter_17 + total_pay_approval_pending;

                        // console.log(item3.caste, item3.ds_phase, total, total_pay_approval_pending);

                        $('#' + item3.caste + '').append("<td>" + total + "</td><td>" + total_pay_approval_pending + "</td>");

                    });

                    // $("#example > tfoot #fotter_id").html("<th style='text-align:left;'>Total</th><th style='text-align:center;'>-</th><th>"+fotter_1+"</th><th>"+fotter_2+"</th><th>"+fotter_3+"</th><th>"+fotter_4+"</th><th>"+fotter_5+"</th><th>"+fotter_6+"</th><th>"+fotter_7+"</th><th>"+fotter_8+"</th><th>"+fotter_9+"</th><th>"+fotter_10+"</th><th>"+fotter_11+"</th><th>"+fotter_12+"</th><th>"+fotter_18+"</th><th>"+fotter_13+"</th>");
                    $("#example > tfoot #fotter_id").html("<th style='text-align:left;'>Total</th><th style='text-align:right;'>" + fotter_18 + "</th><th style='text-align:right;'>" + fotter_13 + "</th>");
                    $("#example > tfoot #fotter_id").append("<th style='text-align:right;'>" + fotter_14 + "</th><th style='text-align:right;'>" + fotter_15 + "</th>");
                    // $("#example > tfoot #fotter_id").append("<th style='text-align:center;'>-</th><th style='text-align:center;'>-</th>");
                    $("#example > tfoot #fotter_id").append("<th style='text-align:right;'>" + fotter_16 + "</th><th style='text-align:right;'>" + fotter_17 + "</th>");
                    var grand_total = fotter_13 + fotter_15 + fotter_17;
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
