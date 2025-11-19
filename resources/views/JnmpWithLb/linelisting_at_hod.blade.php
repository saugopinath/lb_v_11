<style>
    .card-header-custom {
        font-size: 16px;
        background: linear-gradient(to right, #c9d6ff, #e2e2e2);
        font-weight: bold;
        font-style: italic;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
</style>
@extends('layouts.app-template-datatable')
@section('content')
    <!-- Main content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mt-4">
                {{-- <form method="post" id="register_form" class="submit-once"> --}}
                {{-- {{ csrf_field() }} --}}


                <div class="tab-content" style="margin-top:16px;">
                    <div class="tab-pane active" id="personal_details">
                        <!-- Card with your design -->
                        <div class="card" id="res_div">
                            <div class="card-header card-header-custom">
                                <h4 class="card-title mb-0"><b> Janma Mrityu Death Cases in LB <span
                                            class="label label-info" style="font-size: 14px;">(These beneficiaries were
                                            de-activated as per death incidents received from Janma Mrityu
                                            Portal.)</span></b></h4>
                            </div>
                            <div class="card-body" style="padding: 20px;">
                                <!-- Alert Messages -->
                                <div class="alert-section">
                                    @if (($message = Session::get('success')) && ($id = Session::get('id')))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <strong>{{ $message }} with Application ID:
                                                {{ $id }}</strong>
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                        </div>
                                    @endif

                                    @if ($message = Session::get('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <strong>{{ $message }}</strong>
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                        </div>
                                    @endif

                                    @if (count($errors) > 0)
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <ul>
                                                @foreach ($errors as $error)
                                                    <li><strong> {{ $error }}</strong></li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                        </div>
                                    @endif

                                    <div class="alert print-error-msg" style="display:none;" id="errorDivMain">
                                        <button type="button" class="close" aria-label="Close"
                                            onclick="closeError('errorDivMain')">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <ul></ul>
                                    </div>
                                </div>

                                <form name="casteManagement" id="casteManagement" class="submit-once">
                                    {{ csrf_field() }}
                                    <!-- Search Section -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-row align-items-end">

                                                <div class="form-group col-md-4">
                                                    <label class="">District</label>
                                                    <select name="district" id="district" class="form-control"
                                                        tabindex="6">
                                                        <option value="">--- All ---</option>
                                                        @foreach ($districts as $district)
                                                            <option value="{{ $district->district_code }}">
                                                                {{ $district->district_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <span id="error_district" class="text-danger"></span>
                                                </div>

                                                <div class="form-group col-md-3">
                                                    <button type="button" name="filter" id="filter"
                                                        class="btn btn-success table-action-btn">
                                                        <i class="fas fa-search"></i> Search
                                                    </button>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </form>

                                @if (!empty($errorMsg))
                                    <div class="alert alert-danger alert-block">
                                        <strong> {{ $errorMsg }}</strong></li>

                                    </div>
                                @endif
                                <!-- DataTable Section -->
                                <div class="table-container" id="list_div" style="display: none;">
                                    <div class="table-responsive">
                                        <table id="example" class="display data-table" cellspacing="0" width="100%">
                                            <thead class="table-header-spacing">
                                                <tr role="row">
                                                    <th style="text-align: center">Sl No</th>
                                                    <th style="text-align: center">Application ID</th>
                                                    <th style="text-align: center">Beneficiary ID</th>
                                                    <th style="text-align: center">Name</th>
                                                    <th style="text-align: center">District</th>
                                                    <th style="text-align: center">Block/Municipality</th>
                                                    <th style="text-align: center">GP/Ward</th>
                                                    <th style="text-align: center">Mobile No.</th>
                                                </tr>
                                            </thead>
                                            <tbody style="font-size: 14px;"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- </form> --}}
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#list_div').hide();
            $('#loadingDiv').hide();

            $('#excel_btn').click(function() {
                var token = "{{ csrf_token() }}";
                var district = $('#district').val();

                //    var student_roll_no = $('#student_roll_no').val();

                var data = {
                    '_token': token,
                    'district_code': district
                };
                redirectPost('generateJnmpDataHodExcel', data);
            });
            $('.modalEncloseClose').click(function() {
                $('.encolser_modal').modal('hide');
            });

            // $('#loadingDiv').hide();
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #bankTrFailed').addClass("active");
            $('.sidebar-menu #JnmpDataListHod').addClass("active");

            //$('#loadingDiv').hide();
            var dataTable = "";

            function loadDatatable() {
                $('#list_div').show();
                if ($.fn.DataTable.isDataTable('#example')) {
                    $('#example').DataTable().destroy();
                }
                dataTable = $('#example').DataTable({
                    dom: 'Blfrtip',
                    "scrollX": true,
                    "paging": true,
                    "searchable": true,
                    "ordering": false,
                    "bFilter": true,
                    "bInfo": true,
                    "pageLength": 25,
                    'lengthMenu': [
                        [25, 50, 100, -1],
                        [25, 50, 100, 'All']
                    ],
                    "serverSide": true,
                    "processing": true,
                    "bRetrieve": true,
                    "oLanguage": {
                        "sProcessing": '<div class="preloader1" align="center"><h4 class="text-success" style="font-weight:bold;font-size:22px;">Processing...</h4></div>'
                    },
                    ajax: {
                        url: "{{ url('jnmpMarkedData') }}",
                        type: "POST",
                        data: function(d) {
                            d.district_code = $('#district').val(),
                                d._token = "{{ csrf_token() }}"
                        },

                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#loadingDiv').hide();
                            ajax_error(jqXHR, textStatus, errorThrown)
                        }
                    },
                    "initComplete": function(record) {
                        // console.log(record.json)
                        //console.log('Data rendered successfully');
                        $('#loadingDiv').hide();

                        //  $('#completed_bank').text(record.json.completed[0].count);
                        // $('#pending_bank_edit').text(record.json.recordsTotal);
                    },
                    "columns": [{
                            "data": "DT_RowIndex"
                        },
                        {
                            "data": "application_id"
                        },
                        {
                            "data": "beneficiary_id"
                        },
                        {
                            "data": "fullname"
                        },
                        {
                            "data": "district"
                        },
                        {
                            "data": "block_ulb_name"
                        },
                        {
                            "data": "gp_ward_name"
                        },
                        {
                            "data": "mobile_no"
                        },
                    ],
                    // "columnDefs": [{
                    //         "targets": [3, 4, 5],
                    //         "visible": false,
                    //         "searchable": true
                    //     },
                    //     //         {
                    //     //   "targets": [ 7 ],
                    //     //   "orderable": false,
                    //     //   "searchable": true
                    //     // }
                    // ],
                    "buttons": [
                        // {
                        //     extend: 'pdfHtml5',
                        //     title: "Account Validation Failed Report  Report Generated On-@php
                            //         date_default_timezone_set('Asia/Kolkata');
                            //         $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            //         $date = $date->format('F j, Y g:i:a');
                            //         echo $date;
                            //
                        @endphp ",
                        //     messageTop: "Date: @php
                            //         date_default_timezone_set('Asia/Kolkata');
                            //         $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            //         $date = $date->format('F j, Y g:i:a');
                            //         echo $date;
                            //
                        @endphp",

                        //     footer: true,
                        //     orientation: 'landscape',
                        //     // pageSize : 'LEGAL',
                        //     pageMargins: [40, 60, 40, 60],
                        //     exportOptions: {
                        //         columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],

                        //     }
                        // },
                        // {
                        //     extend: 'excel',

                        //     title: "Account Validation Failed Report  Report Generated On-@php
                            //         date_default_timezone_set('Asia/Kolkata');
                            //         $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            //         $date = $date->format('F j, Y g:i:a');
                            //         echo $date;
                            //
                        @endphp ",
                        //     messageTop: "Date: @php
                            //         date_default_timezone_set('Asia/Kolkata');
                            //         $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            //         $date = $date->format('F j, Y g:i:a');
                            //         echo $date;
                            //
                        @endphp",
                        //     footer: true,
                        //     pageSize: 'A4',
                        //     //orientation: 'landscape',
                        //     pageMargins: [40, 60, 40, 60],
                        //     exportOptions: {
                        //         format: {
                        //             body: function(data, row, column, node) {
                        //                 return column === 8 || column === 3 ? "\0" + data : data;
                        //             }
                        //         },
                        //         columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                        //         stripHtml: false,
                        //     }
                        // },

                    ],
                });
            }
            $('#filter').click(function() {

                loadDatatable();

            });
        });

        function redirectPost(url, data, method = 'post') {
            var form = document.createElement('form');
            form.method = method;
            form.action = url;
            for (var name in data) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = data[name];
                form.appendChild(input);
            }
            $('body').append(form);
            form.submit();
        }
    </script>
@endpush
