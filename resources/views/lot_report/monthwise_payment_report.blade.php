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
        /*max-width: 50%;*/
        /*margin: 0 auto;*/
    }

    .resultDiv {
        border: 1px solid #d9d9d9;
        border-left: 3px solid seagreen;
        /*margin-bottom: 10px; */
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
        /*max-width: 50%;*/
        /*margin: 0 auto;*/
    }
</style>

@extends('layouts.app-template-datatable')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Monthwise Payment Report</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
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
                            <div class="col-md-12 d-flex">

                                <!-- Financial Year -->
                                <div class="col-md-2 me-2">
                                    <label class="form-label">Financial Year <span class="text-danger">*</span></label>
                                    <select class="form-select" name="lot_year" id="lot_year">
                                        @foreach(Config::get('constants.fin_year') as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Search Button -->
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" name="filter" id="filter" class="btn btn-success w-100">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- Results Table --}}
                    <div class="table-responsive resultDiv mt-4" id="payment_div" style="display:none;">
                        <table id="tableForPayment" class="data-table"
                            style="border:2px solid #f8f9fa;">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Amount in Rupees</th>
                                </tr>
                            </thead>

                            <tbody></tbody>

                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>

                </div>
            </div>

        </div>
    </section>

@endsection

@push('scripts')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Live Clock
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
            $('.sidebar-menu #monthwisePaymentReport').addClass("active");

        });


        $(document).ready(function () {
            //$('#loadingDiv').hide();



            $('#filter').click(function () {
                //$('#loadingDiv').show();
                // var district_code = $('#district_code').val();
                var phase_code = $('#phase_code').val();
                var lot_year = $('#lot_year').val();

                if (lot_year == '') {
                    // alert('Please select financial year');
                    $.alert({
                        title: 'Alert!',
                        content: 'Please select financial year',
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
                $('#lot_year').val("");
                // $('#tableForPayment').DataTable().ajax.reload();

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
            {{--  $("#tableForPayment").dataTable().fnDestroy();  --}}
            $('#tableForPayment').DataTable().destroy();

            table = $('#tableForPayment').DataTable({

                dom: 'Blfrtip',
                // "scrollX": true,
                "paging": false, // Disable Pagination
                "searchable": false,
                "ordering": false, // Disable Ordering of all column
                "bFilter": false,
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
                    url: "{{ url('totalMonthwisePaymentReport') }}",
                    type: "POST",
                    data: function (d) {
                        // d.district_code = $("#district_code").val(),
                        d.phase_code = $("#phase_code").val(),
                            d.lot_year = $("#lot_year").val(),
                            d._token = "{{csrf_token()}}"
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('.preloader1').hide();
                        {{--  $.alert({
                            title: 'Error!!',
                            type: 'red',
                            icon: 'fa fa-warning',
                            content: 'Somthing went wrong may be session timeout. Please logout and login again.',
                        });  --}}
                        ajax_error(jqXHR, textStatus, errorThrown);
                    }
                },

                columns: [
                    // { "data": "DT_RowIndex" },
                    { "data": "month_name" },
                    { "data": "amount" },

                ],
                // 'columnDefs': [
                //   {
                //     "targets": [1],
                //     "className": "dt-body-right",
                //   }
                // ],

                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(), data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    const toIndianCurrency = (num) => {
                        const curr = num.toLocaleString('en-IN', {
                            style: 'currency',
                            currency: 'INR'
                        });
                        return curr;
                    };

                    // Total over this page
                    total_amount = api
                        .column(1, { page: 'amount' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    $(api.column(0).footer()).html(
                        'Total - '
                    );
                    $(api.column(1).footer()).html(
                        total_amount
                    );
                },

                buttons: [
                    {
                        extend: 'pdfHtml5',
                        title: "Monthwise Payment  Report @php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp",
                        messageTop: "Date:@php date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                        echo $date;@endphp, Lot Year - " + $("#lot_year").val(),
                        footer: true,
                        // orientation: 'landscape',
                        pageSize: 'A4',
                        pageMargins: [5, 5, 5, 5],
                        exportOptions: {
                            columns: [0, 1],

                        }
                    },

                    // {
                    //   extend: 'excel',
                    //   title: "Monthwise Payment  Report @php date_default_timezone_set('Asia/Kolkata');
                        $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                        $date = $date->format('F j, Y g:i:a');
                    echo $date;@endphp",
                    //   messageTop:"Date:@php date_default_timezone_set('Asia/Kolkata');
                        $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                        $date = $date->format('F j, Y g:i:a');
                    echo $date;@endphp, Lot Year - "+$( "#lot_year" ).val()+" , Lot Month - "+ $( "#lot_month option:selected" ).text(),
                    //   footer: true,
                    //   pageSize:'A4',
                    //   orientation: 'landscape',
                    //   pageMargins: [ 40, 60, 40, 60 ],
                    //   exportOptions: {
                    //        columns: [0,1],
                    //       stripHtml: true,
                    //   }
                    // },


                ]


            });
        }
    </script>
@endpush
