@extends('layouts.app-template-datatable')
@push('styles')
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
        padding: 5px;
        color: #555;
        font-size: 12px;
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
        margin-top: 0px;
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

    .disabledcontent {
        pointer-events: none;
        opacity: 0.4;
    }
</style>
@endpush
@section('content')
<!-- <div class="content-wrapper"> -->

    <!-- Content Header -->
    <section class="content-header">
        <h1>Beneficiary List of Approved & Verification Pending</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <!-- Alerts Section -->
                <div>
                    @if (($message = Session::get('success')) && ($id = Session::get('id')))
                        <div class="alert alert-success alert-dismissible fade show">
                            <strong>{{ $message }} with Application ID: {{ $id }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong>{{ $message }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (count($errors) > 0)
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li><strong>{{ $error }}</strong></li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                </div>

                <!-- Search Criteria -->
                <div class="card card-default mt-3">
                    <div class="card-header">
                        <h4 class="card-title"><b>Search Criteria</b></h4>
                    </div>

                    <div class="card-body">
                        <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}">

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="required">Search For</label>
                                <select name="search_for" id="search_for" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="1">Approved</option>
                                    <option value="2">Verification Pending</option>
                                </select>
                                <span id="error_search_for" class="text-danger"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="required" id="gp_ward_txt">GP/Ward</label>
                                <select name="gp_ward" id="gp_ward" class="form-control">
                                    @foreach ($gpWardLists as $gp)
                                        <option value="{{ $gp->urban_body_ward_code }}">
                                            {{ $gp->urban_body_ward_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span id="error_gp_ward" class="text-danger"></span>
                            </div>

                            <div class="form-group col-md-4 d-flex align-items-end">
                                <button type="button" id="submitting"
                                    class="btn btn-success btn-lg modal-search">Search</button>
                                <img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1"
                                     width="50" height="50" class="ms-3" style="display:none;">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Error Box -->
                <div class="alert alert-warning print-error-msg mt-3" id="errorDiv" style="display:none;">
                    <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
                    <ul></ul>
                </div>

                <!-- Search Result Section -->
                <div class="card card-default mt-3" id="search_details" style="display:none;">
                    <div class="card-header">
                        <h4><b>Search Result</b></h4>
                    </div>

                    <div class="card-body">

                        <div class="text-end" id="report_generation_text">
                            Report Generated on:
                            <b>{{ date('l jS \of F Y h:i:s A') }}</b>
                        </div>

                        <button class="btn btn-info exportToExcel mt-2">Export To Excel</button>

                        <div id="divScrool" class="mt-3">
                            <table id="example" class="table table-striped table-bordered table2excel w-100">
                                <thead>
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Beneficiary Name</th>
                                        <th>Father Name</th>
                                        <th>Aadhaar No.</th>
                                        <th>DOB</th>
                                        <th>Mobile No.</th>
                                        <th>Block/Municipality</th>
                                        <th>GP/Ward</th>
                                        <th>Address</th>
                                        <th>A/C No.</th>
                                        <th>IFSC</th>
                                        <th>Bank Name</th>
                                        <th>Branch Name</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr id="fotter_id"></tr>
                                    <tr>
                                        <td colspan="21" class="text-center" style="display:none;" id="fotter_excel">
                                            Heading
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Download Modal -->
        <div class="modal fade" id="modalDownloadCSV" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Beneficiary List Download</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <div id="loadingDivModal" style="display:none;"></div>

                        <input type="hidden" id="pension_id">
                        <input type="hidden" id="ben_application_id">
                        <input type="hidden" id="update_scheme_id">

                        <table class="table table-bordered table-condensed">
                            <tr>
                                <th>Download Reason: <span class="text-danger">*</span></th>
                                <td>
                                    <input type="text" name="download_reason" id="download_reason" class="form-control"
                                           maxlength="100">
                                    <span class="small text-muted">Max 100 characters allowed</span><br>
                                    <span class="text-danger fw-bold" id="error_download_reason"></span>
                                </td>
                            </tr>
                        </table>

                        <div class="text-center">
                            <button type="button" id="downloadToExcel" class="btn btn-success btn-lg">Download</button>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </section>

<!-- </div> -->

@endsection
@push('scripts')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script src="{{ URL::asset("js/jquery.table2excel.js") }}"></script>
    <script>
       
        //alert(base_date);

        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            // $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #approvedPendingBeneficiaryList').addClass("active");
            //loadDataTable();
            $(".exportToExcel").click(function(e) {
                $('#download_reason').val('');
                $("#modalDownloadCSV").modal('show');
            });

            $("#downloadToExcel").on('click', function() {
                var error_download_reason = '';
                var download_reason = $('#download_reason').val();
                var search_for = $('#search_for').val();
                var gp_ward = $('#gp_ward').val();
                var district = $('#dist_code').val();

                
                if ($.trim($('#download_reason').val()).length == 0) {
                    error_download_reason = 'Download reason is required';
                    $('#error_download_reason').text(error_download_reason);
                    $('#download_reason').addClass('has-error');
                } else {
                    error_download_reason = '';
                    $('#error_download_reason').text(error_download_reason);
                    $('#download_reason').removeClass('has-error');
                }
                if (error_download_reason != '') {
                    return false;
                } else {
                    // alert('Download Reason: ' + download_reason);
                    $("#loadingDivModal").show();
                    var token = "{{ csrf_token() }}";
                    var data = {
                        '_token': token,
                        'download_reason': download_reason,
                        'district': district,
                        'search_for': search_for,
                        'gp_ward': gp_ward
                    };
                    redirectPost('generateExcelApprovedVerificationPendingList', data);
                    $("#loadingDivModal").hide();
                    $("#modalDownloadCSV").modal('hide');
                }
            });
            
            $('.modal-search').on('click', function() {
                // alert('Table Load');
                // loadDataTable();
                var error_search_for = '';
                var error_gp_ward = '';
                var searchFor = $('#search_for').val();
                var district = $('#dist_code').val();
                var gp_ward = $('#gp_ward').val();
                
                if ($.trim($('#search_for').val()).length == 0) {
                    error_search_for = 'Search field is required';
                    $('#error_search_for').text(error_search_for);
                    $('#search_for').addClass('has-error');
                } else {
                    error_search_for = '';
                    $('#error_search_for').text(error_search_for);
                    $('#search_for').removeClass('has-error');
                }
                if (error_search_for != '') {
                    return false;
                } else {
                    // Load DataTable
                    $("#submit_loader1").show();
                    $("#submitting").hide();
                    $('#search_details').show();
                    if ($.fn.DataTable.isDataTable('#example')) {
                        $('#example').DataTable().destroy();
                    }
                    var table = $('#example').DataTable({
                        dom: 'Blfrtip',
                        "scrollX": true,
                        "paging": true,
                        "searchable": true,
                        "ordering": false,
                        "bFilter": true,
                        "bInfo": true,
                        "pageLength": 25,
                        'lengthMenu': [
                            [10, 20, 25, 50, 100, -1],
                            [10, 20, 25, 50, 100, 'All']
                        ],
                        "serverSide": true,
                        "processing": true,
                        "bRetrieve": true,
                        "oLanguage": {
                            "sProcessing": '<div class="preloader1" align="center"><font style="font-size: 20px; font-weight: bold; color: green;">Processing...</font></div>'
                        },
                        "ajax": {
                            url: "{{ route('getApprovedVerificationPendingList') }}",
                            type: "post",
                            data: function(d) {
                                d.district = district,
                                d.searchFor = searchFor,
                                d.gp_ward = gp_ward,
                                d._token = "{{ csrf_token() }}"
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                $('#submit_btn').attr('disabled', false);
                                $('#loadingDiv').hide();
                                $('.preloader1').hide();
                                ajax_error(jqXHR, textStatus, errorThrown);
                            }
                        },
                        "initComplete": function() {
                            $('#loadingDiv').hide();
                            $("#submit_loader1").hide();
                            $("#submitting").show();
                            //console.log('Data rendered successfully');
                        },
                        "columns": [
                            {
                                "data": "application_id"
                            },
                            {
                                "data": "name"
                            },
                            {
                                "data": "father_name"
                            },
                            {
                                "data": "aadhar_no"
                            },
                            {
                                "data": "dob"
                            },
                            {
                                "data": "mobile_no"
                            },
                            {
                                "data": "block_ulb_name"
                            },
                            {
                                "data": "gp_ward_name"
                            },
                            {
                                "data": "address"
                            },
                            {
                                "data": "mask_bank_code"
                            },
                            {
                                "data": "bank_ifsc"
                            },
                            {
                                "data": "bank_name"
                            },
                            {
                                "data": "branch_name"
                            }
                        ],
                        "buttons": [
                            
                            // 'pdf'
                        ],
                    });
                }
            });
        });

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

