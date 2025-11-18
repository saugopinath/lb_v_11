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

    <!-- Content Header -->

    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1>
                    Date Wise Lot Report
                    <span class="fs-6">(Based on the Lot Creation date)</span>
                </h1>
            </div>
        </div>
    </div>

    <div class="container-fluid">

        <div class="card card-default">
            <div class="card-body">

                <div id="loadingDiv"></div>

                <div class="filterDiv">

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
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li><strong>{{ $error }}</strong></li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif


                    <div class="row">

                        <div class="col-md-12 d-flex flex-wrap">

                            {{-- Financial Year --}}
                            <div class="col-md-2 me-3">
                                <label class="form-label">Financial Year <span class="text-danger">*</span></label>
                                <select class="form-select" name="lot_year" id="lot_year">

                                    @php
                                        $current_year = date('Y');
                                        $next_year = date('Y') + 1;
                                        $pre_year = date('Y') - 1;

                                        if (date('m') > 3) {
                                            $financial_year = $current_year . '-' . $next_year;
                                        } else {
                                            $financial_year = $pre_year . '-' . $current_year;
                                        }
                                    @endphp

                                    @foreach(Config::get('constants.fin_year') as $year)
                                        <option value="{{ $year }}" @if($financial_year == $year) selected @endif>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- From Date --}}
                            <div class="col-md-3 me-3">
                                <label class="form-label">From Date</label>
                                <input type="text" id="from_date" name="from_date" class="form-control" autocomplete="off"
                                    placeholder="DD/MM/YYYY">
                            </div>

                            {{-- To Date --}}
                            <div class="col-md-3 me-3">
                                <label class="form-label">To Date</label>
                                <input type="text" id="to_date" name="to_date" class="form-control" autocomplete="off"
                                    placeholder="DD/MM/YYYY">
                            </div>

                            {{-- Buttons --}}
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="filter" class="btn btn-success me-2">Search</button>
                                <button type="button" id="reset" class="btn btn-warning">Reset</button>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- Result Table --}}
                <div class="table-responsive resultDiv mt-4" id="validation_lot_div">
                    <table id="tableForLot" class="data-table"
                        style="width:100%; border: 1px solid #f8f9fa;">
                        <thead class="table-light" style="font-size:12px;">
                            <tr>
                                <th>Serial No</th>
                                <th>Date</th>
                                <th>Total Lot</th>
                                <th>Total Lot Size</th>
                                <th>Amount Required</th>
                                <th>Successful Beneficiaries</th>
                                <th>Amount Credited To Beneficiaries</th>
                            </tr>
                        </thead>

                        <tbody style="font-size:14px;"></tbody>

                        <tfoot class="table-light fw-bold">
                            <tr>
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
            // Live Clock
            $('#from_date').datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                "setDate": "today",
                "endDate": "today+1",
                //   "maxDate":  new Date(),

            });
            $('#to_date').datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                "setDate": "today",
                "endDate": "today+1",
                //   "maxDate":  new Date(),

            });
            {{--  var interval = setInterval(function () {
                var momentNow = moment();
                $('.date-part').html(momentNow.format('DD-MMMM-YYYY'));
                $('.time-part').html(momentNow.format('hh:mm:ss A'));
            }, 100);  --}}
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
            $('.sidebar-menu #datewiseLotReport').addClass("active");

        });


        $(document).ready(function () {
            //$('#loadingDiv').hide();
            var table = $('#tableForLot').DataTable({

                dom: 'Blfrtip',
                "scrollX": true,
                "paging": true, // Disable Pagination
                "searchable": false,
                "ordering": false, // Disable Ordering of all column
                "bFilter": true,
                "bInfo": false, // Disable Showing 1 to 20 of 2000 entries
                "pageLength": 20,
                'lengthMenu': [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,

                "oLanguage": {
                    "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="100px"></div>'
                },
                ajax: {
                    url: "{{ url('datewise-lot-report') }}",
                    type: "POST",
                    data: function (d) {
                        $('#loadingDiv').hide();
                        d.lot_year = $("#lot_year").val(),
                            d.from_date = $("#from_date").val(),
                            d.to_date = $("#to_date").val(),
                            d._token = "{{csrf_token()}}"
                    },

                    error: function (jqXHR, textStatus, errorThrown) {
                        $('.preloader1').hide();
                        $('#loadingDiv').hide();
                        // $.alert({
                        //   title: 'Error!!',
                        //   type: 'red',
                        //   icon: 'fa fa-warning',
                        //   content: 'Somthing went wrong may be session timeout. Please logout and login again.',
                        // });
                        ajax_error(jqXHR, textStatus, errorThrown);
                    }
                },
                columns: [

                    { "data": "DT_RowIndex" },
                    { "data": "creation_date" },
                    { "data": "total_lot", "defaultContent": "0" },
                    { "data": "total_bencount", "defaultContent": "0" },

                    { "data": "total_amount", "defaultContent": "0" },
                    { "data": "total_success", "defaultContent": "0" },
                    { "data": "total_amount_debit", "defaultContent": "0" },

                ],
                'columnDefs': [
                    {
                        "targets": [2, 3, 4],
                        //"className": "dt-body-right",
                    },
                    //hide the second & fourth column
                    { 'visible': false, 'targets': [5, 6] }
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


                    total_lot = api
                        .column(2, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_bencount = api
                        .column(3, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_amount = api
                        .column(4, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_success = api
                        .column(5, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_amount_debit = api
                        .column(6, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(0).footer()).html(
                        "Total-"
                    );
                    $(api.column(1).footer()).html(
                        ""
                    );
                    $(api.column(2).footer()).html(
                        total_lot
                    );
                    $(api.column(3).footer()).html(
                        total_bencount
                    );
                    $(api.column(4).footer()).html(
                        total_amount
                    );
                    $(api.column(5).footer()).html(
                        total_success
                    );
                    $(api.column(6).footer()).html(
                        total_amount_debit
                    );

                },

                buttons: [
                    {
                        extend: 'pdfHtml5',
                        //  extend: 'pdf',
                        title: "Date Wise Lot  Report @php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp",
                        messageTop: "Date:@php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp",
                        footer: true,
                        pageSize: 'A4',

                        orientation: 'landscape',
                        // pageSize : 'LEGAL',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4],

                        }
                    },

                    {
                        extend: 'excel',
                        title: "Date Wise Lot  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp ",


                        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp",
                        footer: true,
                        pageSize: 'A4',
                        //orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4],
                            stripHtml: true,
                        }
                    },

                ]
            });


            $('#filter').click(function () {
                $('#tableForLot').DataTable().ajax.reload();
                //$('#loadingDiv').show();

            });

            $('#reset').click(function () {
                //$('#loadingDiv').show();
                $('#from_date').val("");
                $('#to_date').val("");
                $('#tableForLot').DataTable().ajax.reload();
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


    </script>
@endpush
