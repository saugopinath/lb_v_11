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
</style>

@extends('layouts.app-template-datatable')

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h2>Districtwise Beneficiary Payment Report</h2>
                </div>
            </div>
        </div>
    </section>

    <div class="container-fluid">

        <div class="card card-default">
            <div class="card-body">

                <div id="loadingDiv"></div>

                {{-- Alerts --}}
                <div class="row mb-3">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>{{ $message }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li><strong>{{ $error }}</strong></li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                </div>

                <div class="filterDiv">
                    <div class="row">

                        {{-- Financial Year --}}
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Financial Year <span class="text-danger">*</span></label>
                            <select class="form-select" name="lot_year" id="lot_year">
                                @foreach(Config::get('constants.fin_year') as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Month --}}
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <select class="form-select" name="lot_month" id="lot_month">
                                <option value="">--Select Month--</option>
                                @foreach(Config::get('constants.monthval') as $key => $month)
                                    <option value="{{ $key }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Phase --}}
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Select Phase</label>
                            <select class="form-select" name="phase_code" id="phase_code" required>
                                <option value="">--Select Phase--</option>

                                @foreach($plp as $key)
                                    @if($key->ds_phase == 0)
                                        <optgroup label="Normal Entry">
                                            <option value="{{ $key->ds_phase }}">{{ $key->ds_phase_name }}</option>
                                        </optgroup>
                                    @endif
                                @endforeach

                                <optgroup label="Form through Duare Sarkar Camp">
                                    @foreach($plp as $key)
                                        @if($key->ds_phase <> 0)
                                            <option value="{{ $key->ds_phase }}">{{ $key->ds_phase_name }}</option>
                                        @endif
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        {{-- Search Button --}}
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="button" id="filter" class="btn btn-success w-100">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Result Table --}}
                <div class="table-responsive mt-4 resultDiv" id="payment_div" style="display:none;">
                    <table id="tableForPayment" class="data-table" style="border: 2px solid ghostwhite; width:100%;">
                        <thead style="font-size: 12px;">
                            <tr>
                                <th colspan="1">District[A]</th>
                                <th colspan="4">Beneficiary Record[B]</th>
                                <th colspan="10">Payment Status[C]</th>
                                <th colspan="4">Rejected[D]</th>
                                <th colspan="4">Payment Awaited[E]</th>
                                <th colspan="4">Amount[F]</th>
                            </tr>

                            <tr>
                                <th>District Name<br><span style="font-weight: normal;">[1]</span></th>

                                <th>Total Beneficiary <br><span style="font-weight: normal;">[2]</span></th>
                                <th>SC Beneficiary <br><span style="font-weight: normal;">[3]</span></th>
                                <th>ST Beneficiary <br><span style="font-weight: normal;">[4]</span></th>
                                <th>Others Beneficiary <br><span style="font-weight: normal;">[5]</span></th>

                                <th>Lot Generated <br><span style="font-weight: normal;">[6]</span></th>
                                <th>Lot Not Generated <br><span style="font-weight: normal;">[7]</span></th>
                                <th>Send To Bank <br><span style="font-weight: normal;">[8]</span></th>
                                <th>SC Send To Bank <br><span style="font-weight: normal;">[9]</span></th>
                                <th>ST Send To Bank <br><span style="font-weight: normal;">[10]</span></th>
                                <th>Others Send To Bank <br><span style="font-weight: normal;">[11]</span></th>
                                <th>Response Received <br><span style="font-weight: normal;">[12]</span></th>
                                <th>Payment Success <br><span style="font-weight: normal;">[13]</span></th>
                                <th>Payment Failure <br><span style="font-weight: normal;">[14]</span></th>
                                <th>Approved Bank Edited <br><span style="font-weight: normal;">[15]</span></th>

                                <th>Age Above 60 Years<br><span style="font-weight: normal;">[16]</span></th>
                                <th>Deactivate <br><span style="font-weight: normal;">[17]</span></th>
                                <th>Name Validation Rejected <br><span style="font-weight: normal;">[18]</span></th>
                                <th>Duplicate Bank Rejected <br><span style="font-weight: normal;">[19]</span></th>

                                <th>Under Duplicate Bank <br><span style="font-weight: normal;">[20]</span></th>
                                <th>Under Duplicate Aadhar <br><span style="font-weight: normal;">[21]</span></th>
                                <th>Under Caste Modification <br><span style="font-weight: normal;">[22]</span></th>
                                <th>Validation Failure / Pending <br><span style="font-weight: normal;">[23]</span></th>

                                <th>Send To Bank Amount <br><span style="font-weight: normal;">[24]</span></th>
                                <th>SC Send To Bank Amount <br><span style="font-weight: normal;">[25]</span></th>
                                <th>ST Send To Bank Amount <br><span style="font-weight: normal;">[26]</span></th>
                                <th>Others Send To Bank Amount <br><span style="font-weight: normal;">[27]</span></th>
                            </tr>
                        </thead>

                        <tbody style="font-size:14px;"></tbody>

                        <tfoot style="font-size: 14px; font-weight: bold; text-align: right;">
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>

                            </tr>
                        </tfoot>

                    </table>
                </div>

            </div>
        </div>

    </div>


@endsection


@push('scripts')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#loadingDiv').hide();
            $('#filter_div').removeClass('disabledcontent');
            // $('#loader_img').hide();
            //    $('#res_div').hide();
            $('#submit_btn').removeAttr('disabled');
            // $('#change_msg').hide();
            // $('#lot_year_div').hide();
            // $('#lot_month_div').hide();
            // $('#lot_status_div').hide();
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #paymentReportMain').addClass("active");
            $('.sidebar-menu #lotPaymentReport').addClass("active");

        });


        $(document).ready(function () {
            //$('#loadingDiv').hide();



            $('#filter').click(function () {
                //$('#loadingDiv').show();
                // var district_code = $('#district_code').val();
                var phase_code = $('#phase_code').val();
                var lot_year = $('#lot_year').val();
                var lot_month = $('#lot_month').val();

                if (lot_month == '' || lot_year == '') {
                    // alert('Please select financial year');
                    $.alert({
                        title: 'Alert!',
                        content: 'Please select financial year and month',
                        type: 'red',
                        icon: 'fa fa-warning',

                    });
                }
                else {
                    $('#payment_div').show();
                    list_table();
                    // $('#tableForPayment').DataTable().ajax.reload();
                    // if(district_code != '' || lot_year != '')
                    // {
                    //   $('#tableForPayment').DataTable().ajax.reload();
                    // }
                    // else{
                    //   // alert('Please select district or financial year');
                    //   $.alert({
                    //     title: 'Alert!',
                    //     content: 'Please select district or financial year',
                    //   });
                    // }

                }
            });

            $('#reset').click(function () {
                //$('#loadingDiv').show();
                // $('#district_code').val("");
                $('#phase_code').val("");
                $('#lot_month').val("");
                $('#lot_year').val("");
                $('#tableForPayment').DataTable().ajax.reload();

            });

        });


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

        function list_table() {
            var table = "";
            $('#tableForPayment').DataTable().destroy();
            table = $('#tableForPayment').DataTable({

                dom: 'Blfrtip',
                // "scrollX": true,
                "paging": false, // Disable Pagination
                "searchable": true,
                "ordering": false, // Disable Ordering of all column
                "bFilter": true,
                "bInfo": false, // Disable Showing 1 to 20 of 2000 entries
                "pageLength": 25,
                'lengthMenu': [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,
                "oLanguage": {
                    "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="100px"></div>'
                },
                ajax: {
                    url: "{{ url('report-payment-lot') }}",
                    type: "POST",
                    data: function (d) {
                        // d.district_code = $("#district_code").val(),
                        d.phase_code = $("#phase_code").val(),
                            d.lot_year = $("#lot_year").val(),
                            d.lot_month = $("#lot_month").val(),
                            d._token = "{{csrf_token()}}"
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('.preloader1').hide();
                        $.alert({
                            title: 'Error!!',
                            type: 'red',
                            icon: 'fa fa-warning',
                            content: 'Somthing went wrong may be session timeout. Please logout and login again.',
                        });
                        //ajax_error(jqXHR, textStatus, errorThrown);
                    }
                },

                columns: [
                    // { "data": "DT_RowIndex" },
                    { "data": "district" },
                    { "data": "total_beneficiary", "defaultContent": "0" },
                    { "data": "sc_count", "defaultContent": "0" },
                    { "data": "st_count", "defaultContent": "0" },
                    { "data": "ot_count", "defaultContent": "0" },
                    // { "data": "total_amount","defaultContent":"0"  },
                    // { "data": "sc_amount","defaultContent":"0"  },
                    // { "data": "st_amount","defaultContent":"0"  },
                    // { "data": "ot_amount","defaultContent":"0"  },

                    { "data": "lot_generated", "defaultContent": "0" },
                    { "data": "lot_not_generated", "defaultContent": "0" },
                    { "data": "push_to_bank", "defaultContent": "0" },
                    { "data": "push_to_bank_sc", "defaultContent": "0" },
                    { "data": "push_to_bank_st", "defaultContent": "0" },
                    { "data": "push_to_bank_ot", "defaultContent": "0" },
                    { "data": "response_received", "defaultContent": "0" },
                    { "data": "payment_success", "defaultContent": "0" },
                    { "data": "payment_failure", "defaultContent": "0" },
                    { "data": "bank_edited", "defaultContent": "0" },

                    { "data": "age_60_above", "defaultContent": "0" },
                    { "data": "deactivate_ben", "defaultContent": "0" },
                    { "data": "name_rejected", "defaultContent": "0" },
                    { "data": "bank_rejected", "defaultContent": "0" },

                    { "data": "under_duplicate_bank", "defaultContent": "0" },
                    { "data": "under_duplicate_aadhar", "defaultContent": "0" },
                    { "data": "under_caste_change", "defaultContent": "0" },
                    { "data": "validation_payment_failure", "defaultContent": "0" },

                    { "data": "push_to_bank_amount", "defaultContent": "0" },
                    { "data": "push_to_bank_sc_amount", "defaultContent": "0" },
                    { "data": "push_to_bank_st_amount", "defaultContent": "0" },
                    { "data": "push_to_bank_ot_amount", "defaultContent": "0" },

                ],
                'columnDefs': [
                    {
                        "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26],
                        "className": "dt-body-right",
                    }
                ],

                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(), data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // Total over this page
                    total_beneficiary = api
                        .column(1, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    sc_count = api
                        .column(2, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    st_count = api
                        .column(3, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    ot_count = api
                        .column(4, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    // total_amount = api
                    // .column( 5, { page: 'current'} )
                    // .data()
                    // .reduce( function (a, b) {
                    //     return intVal(a) + intVal(b);
                    // }, 0 );
                    // sc_amount = api
                    // .column( 6, { page: 'current'} )
                    // .data()
                    // .reduce( function (a, b) {
                    //     return intVal(a) + intVal(b);
                    // }, 0 );
                    // st_amount = api
                    // .column( 7, { page: 'current'} )
                    // .data()
                    // .reduce( function (a, b) {
                    //     return intVal(a) + intVal(b);
                    // }, 0 );
                    // ot_amount = api
                    // .column( 8, { page: 'current'} )
                    // .data()
                    // .reduce( function (a, b) {
                    //     return intVal(a) + intVal(b);
                    // }, 0 );

                    lot_generated = api
                        .column(5, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    lot_not_generated = api
                        .column(6, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank = api
                        .column(7, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank_sc = api
                        .column(8, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank_st = api
                        .column(9, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank_ot = api
                        .column(10, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    response_received = api
                        .column(11, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    payment_success = api
                        .column(12, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    payment_failure = api
                        .column(13, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    bank_edited = api
                        .column(14, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    age_60_above = api
                        .column(15, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    deactivate_ben = api
                        .column(16, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    name_rejected = api
                        .column(17, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    bank_rejected = api
                        .column(18, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    under_duplicate_bank = api
                        .column(19, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    under_duplicate_aadhar = api
                        .column(20, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    under_caste_change = api
                        .column(21, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    validation_payment_failure = api
                        .column(22, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank_amount = api
                        .column(23, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank_sc_amount = api
                        .column(24, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank_st_amount = api
                        .column(25, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    push_to_bank_ot_amount = api
                        .column(26, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    // Update footer
                    $(api.column(0).footer()).html(
                        "Total-"
                    );
                    $(api.column(1).footer()).html(
                        total_beneficiary
                    );
                    $(api.column(2).footer()).html(
                        sc_count
                    );
                    $(api.column(3).footer()).html(
                        st_count
                    );
                    $(api.column(4).footer()).html(
                        ot_count
                    );
                    // $( api.column( 5 ).footer() ).html(
                    //   total_amount
                    // );
                    // $( api.column( 6 ).footer() ).html(
                    //   sc_amount
                    // );
                    // $( api.column( 7 ).footer() ).html(
                    //   st_amount
                    // );
                    // $( api.column( 8 ).footer() ).html(
                    //   ot_amount
                    // );

                    $(api.column(5).footer()).html(
                        lot_generated
                    );
                    $(api.column(6).footer()).html(
                        lot_not_generated
                    );
                    $(api.column(7).footer()).html(
                        push_to_bank
                    );
                    $(api.column(8).footer()).html(
                        push_to_bank_sc
                    );
                    $(api.column(9).footer()).html(
                        push_to_bank_st
                    );
                    $(api.column(10).footer()).html(
                        push_to_bank_ot
                    );
                    $(api.column(11).footer()).html(
                        response_received
                    );
                    $(api.column(12).footer()).html(
                        payment_success
                    );
                    $(api.column(13).footer()).html(
                        payment_failure
                    );
                    $(api.column(14).footer()).html(
                        bank_edited
                    );
                    $(api.column(15).footer()).html(
                        age_60_above
                    );
                    $(api.column(16).footer()).html(
                        deactivate_ben
                    );
                    $(api.column(17).footer()).html(
                        name_rejected
                    );
                    $(api.column(18).footer()).html(
                        bank_rejected
                    );
                    $(api.column(19).footer()).html(
                        under_duplicate_bank
                    );
                    $(api.column(20).footer()).html(
                        under_duplicate_aadhar
                    );
                    $(api.column(21).footer()).html(
                        under_caste_change
                    );
                    $(api.column(22).footer()).html(
                        validation_payment_failure
                    );
                    $(api.column(23).footer()).html(
                        push_to_bank_amount
                    );
                    $(api.column(24).footer()).html(
                        push_to_bank_sc_amount
                    );
                    $(api.column(25).footer()).html(
                        push_to_bank_st_amount
                    );
                    $(api.column(26).footer()).html(
                        push_to_bank_ot_amount
                    );
                },

                buttons: [
                    {
                        extend: 'pdfHtml5',
                        title: "Payment  Report- District Wise @php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp",
                        messageTop: "Date:@php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp, Lot Year - " + $("#lot_year").val() + " , Lot Month - " + $("#lot_month option:selected").text(),
                        footer: true,
                        orientation: 'landscape',
                        pageSize: 'A1',
                        pageMargins: [5, 5, 5, 5],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26],

                        }
                    },

                    {
                        extend: 'excel',
                        title: "Payment  Report- District Wise @php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp",
                        messageTop: "Date:@php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp, Lot Year - " + $("#lot_year").val() + " , Lot Month - " + $("#lot_month option:selected").text(),
                        footer: true,
                        pageSize: 'A4',
                        orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26],
                            stripHtml: true,
                        }
                    },


                ]


            });
        }
    </script>
@endpush
