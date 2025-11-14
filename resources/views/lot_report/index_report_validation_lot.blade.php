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
        <h1>Districtwise Validation Report</h1>
    </section>
    <div class="card card-primary card-outline">
        <div class="card-body">

            <div id="loadingDiv"></div>

            <!-- Filter Section -->
            <div class="filterDiv mb-3">

                <div class="row">

                    {{-- Success Message --}}
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <strong>{{ $message }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Error Messages --}}
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

                <!-- Filter Form Row -->
                <div class="row mt-3">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Select Phase</label>
                        <select class="form-select" name="phase_code" id="phase_code" required>
                            <option value="">--Select Phase--</option>

                            @foreach($vlp as $key)
                                @if($key->ds_phase == 0)
                                    <optgroup label="Normal Entry">
                                        <option value="{{ $key->ds_phase }}">
                                            {{ $key->ds_phase_name }}
                                        </option>
                                    </optgroup>
                                @endif
                            @endforeach

                            <optgroup label="Form through Duare Sarkar Camp">
                                @foreach($vlp as $key)
                                    @if($key->ds_phase != 0)
                                        <option value="{{ $key->ds_phase }}">
                                            {{ $key->ds_phase_name }}
                                        </option>
                                    @endif
                                @endforeach
                            </optgroup>

                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Select District</label>
                        <select class="form-select" name="district_code" id="district_code" required>
                            <option value="">--Select District--</option>
                            @foreach($districts as $val)
                                <option value="{{ $val->district_code }}">
                                    {{ $val->district_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="button" name="filter" id="filter" class="btn btn-success me-2">
                            Search
                        </button>

                        <button type="button" name="reset" id="reset" class="btn btn-warning">
                            Reset
                        </button>
                    </div>

                </div>

            </div>

            <!-- Result Table Section -->
            <div class="table-responsive resultDiv" id="validation_lot_div">

                <table id="tableForValidation" class="data-table"
                    style="border:1px solid ghostwhite; font-size:13px;">

                    <thead class="table-light">
                        <tr>
                            <th>Serial No</th>
                            <th>District Name</th>
                            <th>Total Beneficiary</th>
                            <th>SC Beneficiary</th>
                            <th>ST Beneficiary</th>
                            <th>Others Beneficiary</th>
                            <th>Validation Initiated</th>
                            <th>Validation Not Yet Initiated</th>
                            <th>Validation Complete</th>
                            <th>Success</th>
                            <th>Failed<br>(Included Naming Failed)</th>
                            <th>Others Success</th>
                            <th>SC Success</th>
                            <th>ST Success</th>
                            <th>Validation Pending</th>
                            <th>Naming Failed</th>
                        </tr>
                    </thead>

                    <tbody style="font-size:14px;"></tbody>

                    <tfoot class="fw-bold text-end">
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
                        </tr>
                    </tfoot>

                </table>

            </div>

        </div>
    </div>
@endsection

@push("scripts")
    <script>
        $(document).ready(function () {
            // Live Clock
            var interval = setInterval(function () {
                var momentNow = moment();
                $('.date-part').html(momentNow.format('DD-MMMM-YYYY'));
                $('.time-part').html(momentNow.format('hh:mm:ss A'));
            }, 100);
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
            $('.sidebar-menu #lotValidationReport').addClass("active");

        });


        $(document).ready(function () {
            //$('#loadingDiv').hide();
            var table = $('#tableForValidation').DataTable({

                dom: 'Blfrtip',
                // "scrollX": true,
                "paging": false, // Disable Pagination
                "searchable": true,
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
                    url: "{{ url('report-validation-lot') }}",
                    type: "POST",
                    data: function (d) {
                        d.phase_code = $("#phase_code").val(),
                            d.district_code = $("#district_code").val(),
                            d._token = "{{csrf_token()}}"
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('.preloader1').hide();
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
                    { "data": "district" },
                    { "data": "total_beneficiary", "defaultContent": "0" },
                    { "data": "sc_beneficiary", "defaultContent": "0" },
                    { "data": "st_beneficiary", "defaultContent": "0" },
                    { "data": "ot_beneficiary", "defaultContent": "0" },
                    { "data": "validation_initiated", "defaultContent": "0" },
                    { "data": "validation_not_inititated", "defaultContent": "0" },
                    { "data": "validation_complete", "defaultContent": "0" },

                    { "data": "validation_success", "defaultContent": "0" },
                    { "data": "validation_failed", "defaultContent": "0" },
                    { "data": "ot_success", "defaultContent": "0" },
                    { "data": "sc_success", "defaultContent": "0" },
                    { "data": "st_success", "defaultContent": "0" },
                    { "data": "validation_pending", "defaultContent": "0" },
                    { "data": "naming_failed", "defaultContent": "0" },
                ],
                'columnDefs': [
                    {
                        "targets": [2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
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
                    total_ben = api
                        .column(2, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    sc_ben = api
                        .column(3, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    st_ben = api
                        .column(4, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    ot_ben = api
                        .column(5, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_val_ben = api
                        .column(6, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_pending = api
                        .column(7, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_validate_completed = api
                        .column(8, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_success = api
                        .column(9, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    total_failed = api
                        .column(10, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    ot_success = api
                        .column(11, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    sc_success = api
                        .column(12, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    st_success = api
                        .column(13, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    validation_pending = api
                        .column(14, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    naming_failed = api
                        .column(15, { page: 'current' })
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
                        total_ben
                    );
                    $(api.column(3).footer()).html(
                        sc_ben
                    );
                    $(api.column(4).footer()).html(
                        st_ben
                    );
                    $(api.column(5).footer()).html(
                        ot_ben
                    );
                    $(api.column(6).footer()).html(
                        total_val_ben
                    );
                    $(api.column(7).footer()).html(
                        total_pending
                    );
                    $(api.column(8).footer()).html(
                        total_validate_completed
                    );
                    $(api.column(9).footer()).html(
                        total_success
                    );
                    $(api.column(10).footer()).html(
                        total_failed
                    );
                    $(api.column(11).footer()).html(
                        ot_success
                    );
                    $(api.column(12).footer()).html(
                        sc_success
                    );
                    $(api.column(13).footer()).html(
                        st_success
                    );
                    $(api.column(14).footer()).html(
                        validation_pending
                    );
                    $(api.column(15).footer()).html(
                        naming_failed
                    );
                },

                buttons: [
                    {
                        extend: 'pdfHtml5',
                        //  extend: 'pdf',
                        title: 'Validation  Report- District Wise @php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp',
                        messageTop: 'Date:@php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp',
                        footer: true,
                        pageSize: 'A4',

                        orientation: 'landscape',
                        // pageSize : 'LEGAL',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],

                        }
                    },

                    {
                        extend: 'excel',
                        title: 'District Wise Validation  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp ',


                        messageTop: 'Date: @php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp',
                        footer: true,
                        pageSize: 'A4',
                        //orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
                            stripHtml: true,
                        }
                    },

                ]
            });


            $('#filter').click(function () {
                //$('#loadingDiv').show();
                var district_code = $('#district_code').val();
                var phase_code = $('#phase_code').val();
                if (district_code != '' || phase_code != '') {
                    $('#tableForValidation').DataTable().ajax.reload();
                }
                else {
                    // alert('Please select district');
                    $.alert({
                        title: 'Alert!',
                        type: 'red',
                        icon: 'fa fa-warning',

                        content: 'Please select district or phase ',
                    });
                }
            });

            $('#reset').click(function () {
                //$('#loadingDiv').show();
                $('#district_code').val("");
                $('#phase_code').val("");
                $('#tableForValidation').DataTable().ajax.reload();
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
