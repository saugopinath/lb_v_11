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
                <form method="post" id="register_form" class="submit-once">
                    {{ csrf_field() }}


                    <div class="tab-content" style="margin-top:16px;">
                        <div class="tab-pane active" id="personal_details">
                            <!-- Card with your design -->
                            <div class="card" id="res_div">
                                <div class="card-header card-header-custom">
                                    <h4 class="card-title mb-0"><b> Applications List</b></h4>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <!-- Alert Messages -->
                                    <div class="alert-section">
                                        <b>Applications which are Marked by HOD for Edit Name,DOB and Aadhar</b>

                                        <br /><br />
                                        <div class='row'>
                                            @if ($message = Session::get('message'))
                                                <div class="alert alert-success alert-block">
                                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                                    <strong>{{ $message }}</strong>


                                                </div>
                                            @endif
                                            @if ($message = Session::get('success'))
                                                <div class="alert alert-success alert-block">
                                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                                    <strong>{{ $message }}</strong>


                                                </div>
                                            @endif
                                            @if (($message = Session::get('success')) && ($application_id = Session::get('application_id')))
                                                <div class="alert alert-success alert-block">
                                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                                    <strong>{{ $message }} with Application ID:
                                                        {{ $application_id }}</strong>


                                                </div>
                                            @endif
                                            @if (count($errors) > 0)
                                                <div class="alert alert-danger alert-block">
                                                    <ul>
                                                        @foreach ($errors as $error)
                                                            <li><strong> {{ $error }}</strong></li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- DataTable Section -->
                                    <div class="table-container">
                                        <div class="table-responsive">
                                            <table id="example" class="display data-table" cellspacing="0" width="100%">
                                                <thead class="table-header-spacing">
                                                    <tr role="row">
                                                        <th style="text-align: center">Application ID</th>
                                                        <th style="text-align: center">Beneficiary Name</th>
                                                        <th style="text-align: center">Mobile Number</th>
                                                        <th style="text-align: center">DOB</th>
                                                        <th style="text-align: center">Aadhar Number</th>
                                                        <th style="text-align: center">Block/Munc Name</th>
                                                        <th style="text-align: center">GP/Ward Name</th>
                                                        <th style="text-align: center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 14px;">
                                                    <!-- DataTables will populate this dynamically -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $("#bulk_approve").hide();
            var base_url = '{{ url('/') }}';
            var block_ulb_code = $("#block_ulb_code").val();
            var gp_ward_code = $("#gp_ward_code").val();
            var application_type = $('#application_type').val();
            fill_datatable(block_ulb_code, gp_ward_code, application_type);

            function fill_datatable(block_ulb_code = '', gp_ward_code = '', application_type = '') {
                var scheme_id = $("#scheme_id").val();
                var dataTable = $('#example').DataTable({
                    dom: 'Blfrtip',
                    ordering: false,
                    paging: true,
                    pageLength: 100,
                    lengthMenu: [
                        [20, 50, 100, 500, 1000, -1],
                        [20, 50, 100, 500, 1000, 'All']
                    ],
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('aadhaar-details-update-list-approver') }}",
                        type: "GET",
                        data: function(d) {
                            d.dist_code = $("#dist_code").val(),
                                d.created_by_local_body_code = $("#created_by_local_body_code").val(),
                                d.gp_ward_code = gp_ward_code,
                                d._token = "{{ csrf_token() }}"
                        },
                        error: function(ex) {
                            //console.log(ex);
                            //alert('Session time out..Please login again');
                            //window.location.href=base_url;
                        }
                    },
                    columns: [

                        {
                            "data": "application_id"
                        },
                        {
                            "data": "name"
                        },
                        {
                            "data": "mobile_no"
                        },
                        {
                            "data": "dob"
                        },
                        {
                            "data": "aadhar_no"
                        },
                        {
                            "data": "block_ulb_name"
                        },
                        {
                            "data": "gp_ward_name"
                        },
                        {
                            "data": "view"
                        }
                        // { "data": "check" },


                    ],
                    "buttons": [{
                            extend: 'pdf',

                            title: 'Process Application Report  <?php echo date('d-m-Y'); ?>',
                            messageTop: 'Date:<?php echo date('d/m/Y'); ?>',
                            footer: true,
                            pageSize: 'A4',
                            // orientation: 'landscape',
                            pageMargins: [40, 60, 40, 60],
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5],

                            }
                        },
                        {
                            extend: 'excel',

                            title: 'Process Application Report <?php echo date('d-m-Y'); ?>',
                            messageTop: 'Date:<?php echo date('d/m/Y'); ?>',
                            footer: true,
                            pageSize: 'A4',
                            //orientation: 'landscape',
                            pageMargins: [40, 60, 40, 60],
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        return column === 5 || column === 3 ? "\0" + data : data;
                                    }
                                },
                                columns: [0, 1, 2, 3, 4, 5],
                                stripHtml: false,
                            }
                        },
                    ],

                });


            }

            $('#filter').click(function() {
                var block_ulb_code = $('#block_ulb_code').val();
                var gp_ward_code = $('#gp_ward_code').val();
                $('#example').DataTable().destroy();
                fill_datatable(block_ulb_code, gp_ward_code);

            });
            $('#block_ulb_code').change(function() {
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
                    $('#gp_ward_code').html(htmlOption);
                } else {
                    $('#gp_ward_code').html('<option value="">--All --</option>');
                }
            });
            $('#rural_urban_code').change(function() {
                var urban_code = $(this).val();
                if (urban_code == '') {
                    $('#created_by_local_body_code').html('<option value="">--All --</option>');
                }
                $('#created_by_local_body_code').html('<option value="">--All --</option>');
                select_district_code = $('#dist_code').val();


                select_body_type = urban_code;
                var htmlOption = '<option value="">--All--</option>';
                if (select_body_type == 2) {
                    $("#blk_sub_txt").text('Block');
                    $.each(blocks, function(key, value) {
                        if (value.district_code == select_district_code) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                } else if (select_body_type == 1) {
                    $("#blk_sub_txt").text('Subdivision');
                    $.each(subDistricts, function(key, value) {
                        if (value.district_code == select_district_code) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                } else {
                    $("#blk_sub_txt").text('Block/Subdivision');
                }
                $('#created_by_local_body_code').html(htmlOption);


            });
            $('#reset').click(function() {
                $('#block_ulb_code').val('');
                $('#gp_code').val('');
                $('#example').DataTable().destroy();
                fill_datatable();
            });


        });
    </script>
@endpush
