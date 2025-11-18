<style type="text/css">
    .required-field::after {
        content: "*";
        color: red;
    }

    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }

    .preloader1 {
        position: fixed;
        top: 40%;
        left: 52%;
        z-index: 999;
    }

    .preloader1 {
        background: transparent !important;
    }

    .panel-heading {
        padding: 0;
        border: 0;
    }

    .panel-title>a,
    .panel-title>a:active {
        display: block;
        padding: 10px;
        color: #555;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        word-spacing: 3px;
        text-decoration: none;
    }

    .panel-heading a:before {
        font-family: 'Glyphicons Halflings';
        content: "\e114";
        float: right;
        transition: all 0.5s;
    }

    .panel-heading.active a:before {
        -webkit-transform: rotate(180deg);
        -moz-transform: rotate(180deg);
        transform: rotate(180deg);
    }

    .modal {
        overflow: auto !important;
    }

    #enCloserTable tbody tr td {
        padding: 10px 10px 10px 10px;
    }

    .modal-open {
        overflow: visible !important;
    }

    .required:after {
        color: red;
        content: '*';
        font-weight: bold;
        margin-left: 5px;
        float: right;
        margin-top: 5px;
    }

    #loadingDivModal {
        position: absolute;
        top: 0px;
        right: 0px;
        width: 100%;
        height: 100%;
        background-color: #fff;
        background-image: url('images/ajaxgif.gif');
        background-repeat: no-repeat;
        background-position: center;
        z-index: 10000000;
        opacity: 0.4;
        filter: alpha(opacity=40);
        /* For IE8 and earlier */
    }
</style>

@extends('layouts.app-template-datatable_new')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Janma Mrityu Death Cases in LB <span class="label label-info" style="font-size: 14px;">(These beneficiaries were de-activated as per death incidents received from Janma Mrityu Portal.)</span>
            </h1>

        </section>
        <section class="content">
            <div class="box box-default">
                <div class="box-body">


                    <div class="panel panel-default">
                        <div class="panel-heading"><span id="panel-icon">Filter Here</div>
                        <div class="panel-body" style="padding: 5px;">
                            <div class="row">
                                @if ($message = Session::get('success'))
                                    <div class="alert alert-success alert-block">
                                        <button type="button" class="close" data-dismiss="alert">×</button>
                                        <strong>{{ $message }}</strong>

                                    </div>
                                @endif
                                @if (count($errors) > 0)
                                    <div class="alert alert-danger alert-block">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li><strong> {{ $error }}</strong></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group col-md-4">
                                <label class="">District</label>
                                <select name="district" id="district" class="form-control" tabindex="6">
                                    <option value="">--- All ---</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->district_code }}">{{ $district->district_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span id="error_district" class="text-danger"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">&nbsp;</label><br />
                                <button type="button" name="filter" id="filter" class="btn btn-primary"><i
                                        class="fa fa-search"></i> Search</button>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <button type="button" name="excel_btn" id="excel_btn" class="btn btn-success"><i
                                        class="fa fa-file-excel-o"></i> Get Excel</button>
                                {{-- <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button> --}}
                            </div>

                        </div>
                    </div>

                    <div class="panel panel-default" id="list_div" style="display: none;">
                        <div class="panel-heading" id="panel_head">List of beneficiaries
                            {{-- &nbsp;&nbsp;[ Total completed:- <span id="completed_bank"></span>, Total Pending:- <span id="pending_bank_edit"></span>] --}}
                        </div>
                        <div class="panel-body" style="padding: 5px; font-size: 14px;">
                            <div id="loadingDiv">
                            </div>
                            <div class="table-responsive">
                                {{-- <div class="form-group" style="font-weight:bold; font-size:25px;">
                <label class="control-label">Check All</label>
              <input type="checkbox" id='check_all_btn' style="width:48px;">
              </div> --}}
                                <table id="example" class="display" cellspacing="0" width="100%">
                                    <thead style="font-size: 12px;">
                                        <th>Sl No</th>
                                        <th>Application ID</th>
                                        <th>Beneficiary ID</th>
                                        <th>Name</th>
                                        <th>District</th>
                                        <th>Block/Municipality</th>
                                        <th>GP/Ward</th>
                                        <th>Mobile No.</th>
                                    </thead>
                                    <tbody style="font-size: 14px;"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


@endsection
@section('script')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
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
                    "columns": [
                        {
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
                        //     @endphp ",
                        //     messageTop: "Date: @php
                        //         date_default_timezone_set('Asia/Kolkata');
                        //         $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                        //         $date = $date->format('F j, Y g:i:a');
                        //         echo $date;
                        //     @endphp",

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
                        //     @endphp ",
                        //     messageTop: "Date: @php
                        //         date_default_timezone_set('Asia/Kolkata');
                        //         $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                        //         $date = $date->format('F j, Y g:i:a');
                        //         echo $date;
                        //     @endphp",
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
@stop
