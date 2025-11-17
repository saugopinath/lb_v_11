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

    .disabledcontent {
        opacity: 0.4;
        pointer-events: none;
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

@extends('layouts.app-template-datatable')
@section('content')
<style>
    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }
</style>


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        @if($matchType == 1)
                            Verified Name Validation Failed(90% - 100%)
                        @else
                            Verified Name Validation Failed(40% - 89%)
                        @endif
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <div class="card-body">
                    <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">Beneficiary Details Yet To Be Approved</h3>
                        </div>
                        <div class="card-body" style="padding: 5px;">
                            <div class="row">
                                @if ($message = Session::get('success'))
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @endif
                                @if (count($errors) > 0)
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li><strong> {{ $error }}</strong></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="form-label" id="edited_txt">Edited Type by Verifier <span class="text-danger">*</span></label>
                                    <select name="update_code" id="update_code" class="form-select">
                                        <option value="">-----Select----</option>
                                        <option value="6">Minor mismatch, Keep existing bank information</option>
                                        {{-- <option value="12">Process with new bank account</option> --}}
                                        <option value="7">Application is rejected due to major mismatch</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="form-label">Rural/Urban </label>
                                    <select name="filter_1" id="filter_1" class="form-select">
                                        <option value="">-----Select----</option>
                                        @foreach ($levels as $key => $value)
                                            <option value="{{ $key }}"> {{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="form-label" id="blk_sub_txt">Block/Sub Division </label>
                                    <select name="filter_2" id="filter_2" class="form-select">
                                        <option value="">-----Select----</option>
                                    </select>
                                </div>

                                {{-- <div class="col-md-2">
                                    <label class="form-label">Failed Type </label>
                                    <select name="failed_type" id="failed_type" class="form-select">
                                        <option value="">-----All----</option>
                                        @foreach (Config::get('globalconstants.failed_type') as $key => $val)
                                            <option value="{{ $key}}">{{$val}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Payment Mode </label>
                                    <select name="pay_mode" id="pay_mode" class="form-select">
                                        <option value="">-----Select----</option>
                                        @foreach (Config::get('globalconstants.pmt_mode') as $key => $val)
                                            <option value="{{ $key}}">{{$val}}</option>
                                        @endforeach
                                    </select>
                                </div> --}}
                                {{-- <div class="form-group col-md-2" id="municipality_div" style="display:none;">
                                    <label class="form-label">Municipality</label>
                                    <select name="block_ulb_code" id="block_ulb_code" class="form-select">
                                        <option value="">-----All----</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3" style="display:none;" id="gp_ward_div">
                                    <label class="form-label" id="gp_ward_txt">GP/Ward</label>
                                    <select name="gp_ward_code" id="gp_ward_code" class="form-select">
                                        <option value="">-----Select----</option>
                                    </select>
                                </div> --}}
                                <div class="form-group col-md-3" style="margin-top: 24px;">
                                    <button type="button" name="filter" id="filter" class="btn btn-success"><i class="fa fa-search"></i> Search</button>&nbsp;&nbsp;
                                    <button type="button" name="reset" id="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>
                                </div>
                            </div>
                            <hr />
                            <div class="row">
                                <div class="form-group col-md-offset-4 col-md-3 " style="display: none;" id="approve_rejdiv">
                                    <button type="button" name="bulk_approve" class="btn btn-success btn-lg" id="bulk_approve" value="approve">
                                        Approve</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="loadingDiv" style="display: none;"></div>
                    <div class="card card-default" id="res_div" style="display: none;">
                        <div class="card-header">
                            <h3 class="card-title" id="panel_head">List of New Edited Naming Information</h3>
                        </div>
                        <div class="card-body" style="padding: 5px; font-size: 14px;">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped" cellspacing="0" width="100%">
                                    <thead style="font-size: 12px;">
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Beneficiary ID</th>
                                            <th>Application ID</th>
                                            <th>Applicant Name</th>
                                            <th>Bank Response Name</th>
                                            <th>Faliure Type</th>
                                            <th>Edited Type</th>
                                            <th>Action</th>
                                            @if($matchType == 1)
                                            <th> Check <input type="checkbox" id='check_all_btn' style="width:48px;"></th>
                                            @else
                                            <th></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody style="font-size: 14px;"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade bd-example-modal-lg ben_view_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div id="loadingDivModal"></div>
                        <div class="modal-header">
                            <h4 class="modal-title">Approve Edited Bank Details</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body ben_view_body">
                            <div class="accordion singleInfo" id="accordionPersonal">
                                <div class="card">
                                    <div class="card-header active" id="personal">
                                        <div class="preloader1"><img src="" class="loader_img" width="150px" id="loader_img_personal"></div>
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePersonal" aria-expanded="true" aria-controls="collapsePersonal">
                                                Personal Details <span class="applicant_id_modal"></span>
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapsePersonal" class="collapse show" aria-labelledby="personal" data-bs-parent="#accordionPersonal">
                                        <div class="card-body" style="padding: 5px;">
                                            <table class="table table-bordered table-condensed" style="font-size: 14px;">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row" width="20%">Bank Response Name</th>
                                                        <td id='bank_res_name' width="30%"></td>
                                                        <th scope="row" width="20%">Mobile No.</th>
                                                        <td id="mobile_no" width="30%"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" width="20%">Name</th>
                                                        <td id='ben_fullname' width="30%"></td>
                                                        <th scope="row" width="20%">Gender</th>
                                                        <td id="gender" width="30%"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" width="20%">DOB</th>
                                                        <td id="dob" width="30%"></td>
                                                        <th scope="row" width="20%">Age</th>
                                                        <td id="ben_age" width="30%"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" width="20%">Caste:</th>
                                                        <td id="caste" width="30%"></td>
                                                        <th scope="row" width="20%">Name Matching Score</th>
                                                        <td id="matching_score" width="30%"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Action</h5>
                                </div>
                                <div class="card-body" style="padding: 5px;">
                                    <div class="form-group col-md-6">
                                        <label for="opreation_type" class="form-label">Select Operation<span class="text-danger"> *</span></label>
                                        <select name="opreation_type" id="opreation_type" class="form-select opreation_type">
                                            <option value="A" selected>Approve</option>
                                            <option value="OR" selected>Over Rule</option>
                                            <option value="T">Revert</option>
                                        </select>
                                        <span class="text-danger" id="error_opreation_type"></span>
                                    </div>
                                    <div class="form-group col-md-6" style="display:none;" id="div_rejection">
                                        <label for="reject_cause" class="form-label">Select Reverted Cause<span class="text-danger"> *</span></label>
                                        <select name="reject_cause" id="reject_cause" class="form-select">
                                            <option value="">--Select--</option>
                                            <option value="Banking informtion">Banking informtion</option>
                                        </select>
                                    </div>
                                    <div id="radio_btn_confirm" style="font-size: 14px; font-weight: bold; font-style: italic;" class="text-warning text-center">Please select the option to process ?</div>
                                    <div class="form-group col-md-6" style="padding: 5px 5px 5px 5px; border: 1px solid whitesmoke; border-radius: 5px; margin: 5px 0px; background-color: whitesmoke; display: none;" id="div_overule">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="process_type" id="process_type_11" value="11">
                                            <label class="form-check-label minor_process" for="process_type_11" style="cursor: pointer; margin-bottom: 5px;">
                                                Minor mismatch, Keep existing bank information
                                            </label>
                                        </div>
                                        {{-- <div class="form-check">
                                            <input class="form-check-input" type="radio" name="process_type" id="process_type_12" value="12">
                                            <label class="form-check-label" for="process_type_12" style="cursor: pointer; margin-bottom: 5px;">
                                                Process with new bank information
                                            </label>
                                        </div> --}}
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="process_type" id="process_type_13" value="13">
                                            <label class="form-check-label reject_process" for="process_type_13" style="cursor: pointer; margin-bottom: 5px;">
                                                Application is rejected due to major mismatch
                                            </label>
                                        </div>
                                        <span class="text-danger" id="error_process_type"></span>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="form-label" for="heading" id="remarks_heading">Enter Remarks<span class="text-danger"> *</span></label>
                                        <textarea name="accept_reject_comments" id="accept_reject_comments" class="form-control" maxlength="100"></textarea>
                                        <span class="text-danger" id="error_accept_reject_comments"></span>
                                    </div>

                                    <div id="otp_div">
                                        <span class="text-warning" style="font-weight: bold; padding: 15px;">For Rejection you have to enter your Login OTP for verification.</span><br>
                                        <div class="form-group col-md-4">
                                            <input type="text" name="otp" id="otp" maxlength="6" placeholder="Enter OTP" class="form-control">
                                            <span class="text-danger" id="error_otp"></span>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <button class="btn btn-primary" id="otp_verify" name="otp_verify">Verify</button> 
                                            <span style="margin-left: 10px; font-weight: bold;" id="verification_result" class="" style="font-weight: bold;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="#" target="_blank" name="fullForm" id="fullForm" style="text-align: center; align-content: center; display: none;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="is_bulk" id="is_bulk" value="0" />
                                <input type="hidden" id="id" name="id" />
                                <input type="hidden" id="match_type" name="match_type" value="{{$matchType}}">
                                <input type="hidden" id="application_id" name="application_id" />
                                <input type="hidden" name="applicantId[]" id="applicantId" value="" />
                                <input type="hidden" name="verify_otp_no" id="verify_otp_no" value="" />
                                <input type="hidden" name="name_matching_score" id="name_matching_score" value="" />
                                
                                <button type="button" class="btn btn-success btn-lg" id="verifyReject">Approve</button>
                                <button style="display:none;" type="button" id="submitting" value="Submit" class="btn btn-success success" disabled>Processing Please Wait</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



@endsection
@push('scripts')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.sidebar-menu li').removeClass('active');
        $('.sidebar-menu #nmeMinorMismatch').addClass("active");
        $('.sidebar-menu #nameMinorMismatch').addClass("active");
        $('#opreation_type').val('A');
        $("#verifyReject").html("Approve");
        $('#div_rejection').hide();
        $('#loadingDivModal').hide();
        // $('.content').addClass('disabledcontent');
        $('.content').removeClass('disabledcontent');
        // ------------------- Load Datatable Data ------------------------ //
        // function loadDatatable() {
        var dataTable = "";
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        var dataTable = $('#example').DataTable({
            dom: 'Blfrtip',
            paging: true,
            pageLength: 20,
            lengthMenu: [
                [20, 30],
                [20, 30]
            ],
            // lengthMenu: [[20, 50,100,500,1000, -1], [20, 50,100,500,1000, 'All']],
            processing: true,
            serverSide: true,
            "oLanguage": {
                "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
            },
            ajax: {
                url: "{{ url('getVerifiedNameValidationFailed90to100') }}",
                type: "POST",
                data: function(d) {
                    d.filter_1 = $('#filter_1').val(),
                        d.filter_2 = $('#filter_2').val(),
                        d.block_ulb_code = $('#block_ulb_code').val(),
                        d.gp_ward_code = $('#gp_ward_code').val(),
                        d.failed_type = $('#failed_type').val(),
                        d.pay_mode = $('#pay_mode').val(),
                        d.update_code = $('#update_code').val(),
                        d.matchType = $('#match_type').val(),
                        d._token = "{{ csrf_token() }}"
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('.content').removeClass('disabledcontent');
                    $('.preloader1').hide();
                    $('#res_div').hide();
                    // ajax_error(jqXHR, textStatus, errorThrown);
                    $.alert({
                        title: 'Error!!',
                        type: 'red',
                        icon: 'fa fa-warning',
                        content: 'Something wrong when loading table!!',
                    });
                }
            },
            "initComplete": function() {
                $('.content').removeClass('disabledcontent');
                var data = dataTable.rows().data();
                if (data.length == 0) {
                    $('#res_div').hide();
                } else {
                    $('#res_div').show();
                }
                // alert( 'The table has '+data.length+' records' );
                //console.log('Data rendered successfully');
            },
            columns: [{
                    "data": "DT_RowIndex"
                },
                {
                    "data": "beneficiary_id"
                },
                {
                    "data": "application_id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "response_name"
                },
                {
                    "data": "type"
                },
                {
                    "data": "edited_type"
                },
                {
                    "data": "view"
                },
                {
                    "data": "check"
                },
            ],
            "columnDefs": [{
                "targets": [7, 8],
                "orderable": false,
                "searchable": false
            }],

            "buttons": [{
                    extend: 'pdf',
                    title: "Approve Edited Bank Details  Report Generated On-{{ date('F j, Y g:i:a') }}",
                    messageTop: "Date: {{ date('F j, Y g:i:a') }}",
                    footer: true,
                    pageSize: 'A4',
                    orientation: 'landscape',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                    }
                },
                {
                    extend: 'excel',
                    title: "Approve Edited Bank Details  Report Generated On-{{ date('F j, Y g:i:a') }}",
                    messageTop: "Date: {{ date('F j, Y g:i:a') }}",
                    footer: true,
                    pageSize: 'A4',
                    //orientation: 'landscape',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        stripHtml: false,
                        format: {
                            body: function(data, row, column, node) {
                                return column === 4 || column === 3 || column === 6 ? "\0" +
                                    data : data;
                            }
                        },
                    }
                }
            ],
        });
        // }
        $('#example').on('page.dt', function() {
            $('#approve_rejdiv').hide();
        });
        // ------------------- Load Datatable Data End ------------------------ //

        // ------------------- Checkbox Operation ---------------------------//
        $('#example').on('length.dt', function(e, settings, len) {
            $("#check_all_btn").prop("checked", false);
        });

        $('#check_all_btn').on('change', function() {
            var checked = $(this).prop('checked');

            dataTable.cells(null, 8).every(function() {
                var cell = this.node();
                $(cell).find('input[type="checkbox"][name="chkbx"]').prop('checked', checked);
            });
            var data = dataTable
                .rows(function(idx, data, node) {
                    return $(node).find('input[type="checkbox"][name="chkbx"]').prop('checked');
                })
                .data()
                .toArray();
            //console.log(data);
            if (data.length === 0) {
                $("input.all_checkbox").removeAttr("disabled", true);
            } else {
                $("input.all_checkbox").attr("disabled", true);
            }
            var anyBoxesChecked = false;
            var applicantId = Array();
            $('input[type="checkbox"][name="chkbx"]').each(function(index, value) {
                if ($(this).is(":checked")) {
                    anyBoxesChecked = true;
                    applicantId.push(value.value);
                }
            });

            $("#fullForm #applicantId").val($.unique(applicantId));
            if (anyBoxesChecked == true) {
                $('#approve_rejdiv').show();
                $('.ben_view_button').attr('disabled', true);
                document.getElementById('bulk_approve').disabled = false;
                // document.getElementById('bulk_blkchange').disabled = false;
            } else {
                $('#approve_rejdiv').hide();
                $('.ben_view_button').removeAttr('disabled', true);
                document.getElementById('bulk_approve').disabled = true;
                // document.getElementById('bulk_blkchange').disabled = true;
            }
            // console.log(applicantId);
        });
        // ------------------- End Checkbox Operation -----------------------//

        // ------------------- View Button Click Section -----------------------//
        $(document).on('click', '.ben_view_button', function() {
            $('#loadingDiv').show();
            // $("#radio_btn_confirm").hide();
            // $('#div_overule').hide();
            if ($("#update_code").val() == 13) {
                $("#opreation_type option[value='T']").show();
            } else {
                $("#opreation_type option[value='T']").hide();
            }
            // $("#opreation_type option[value='OR']").show();
            if ($("#opreation_type").val() == 'OR') {
                $("#radio_btn_confirm").hide();
                $("#div_overule").hide();
            } else {
                $("#radio_btn_confirm").hide();
                $('#div_overule').hide();
            }
            // $('#process_type').val('');
            $('#process_type').prop('checked', false);
            $('#loader_img_personal').show();
            $('#loadingDivModal').show();
            $('#radio_btn_confirm').hide();
            $('#fullForm #application_id').val('');
            $('#fullForm #applicantId').val('');
            $('.ben_view_button').attr('disabled', true);
            var benid = $(this).val();
            $('#fullForm #application_id').val(benid);
            $("#fullForm #is_bulk").val(0);
            $('#opreation_type').val('A').trigger('change');
            $("#verifyReject").html("Approve");
            $('#div_rejection').hide();
            $(".singleInfo").show();
            $('.applicant_id_modal').html('');
            $('#accept_reject_comments').val('');
            $("#collapseBank").collapse('hide');
            $('#collapsePersonal').collapse('show');
            $('.ben_view_body').addClass('disabledcontent');
            $('#verification_result').removeAttr('class');
            $('#verification_result').html('');
            $('#otp').val('');
            $.ajax({
                type: 'post',
                url: "{{ route('getEditFailedNameData90to100') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    benid: benid
                },
                dataType: 'json',
                success: function(response) {
                    // console.log(JSON.stringify(response));
                    $("#panel_bank_name_text").text(response.paneltext);
                    $('.ben_view_body').removeClass('disabledcontent');
                    $("#collapseBank").collapse('show');
                    $('#loader_img_personal').hide();
                    $('#loadingDivModal').hide();
                    $('#loadingDiv').hide();
                    $('.ben_view_button').removeAttr('disabled', true);
                    $('#bank_res_name').text(response.personaldata[0].response_name);
                    var mname = response.personaldata[0].ben_mname;
                    if (!(mname)) {
                        var mname = ''
                    }
                    var lname = response.personaldata[0].ben_lname;
                    if (!(lname)) {
                        var lname = ''
                    }
                    $('#ben_fullname').text(response.personaldata[0].ben_fname + ' ' +
                        mname + ' ' + lname);
                    $('#mobile_no').text(response.personaldata[0].mobile_no);
                    $('#gender').text(response.personaldata[0].gender);
                    $('#dob').text(response.personaldata[0].dob);
                    $('#ben_age').text(response.personaldata[0].age_ason_01012021);
                    $('#caste').text(response.personaldata[0].caste);
                    $('#matching_score').text(response.personaldata[0].matching_score);
                    $('#name_matching_score').val(response.personaldata[0].matching_score);
                    if (response.personaldata[0].caste == 'SC' || response.personaldata[0]
                        .caste == 'ST') {
                        $('#caste_certificate_no').text(response.personaldata[0]
                            .caste_certificate_no);
                        $('.caste').show();
                    } else {
                        $('.caste').hide();
                    }
                    // $('#old_acc_no').text(response.old_bank_code);  
                    // $('#old_bank_name').text(response.old_bank_name);
                    // $('#old_branch_name').text(response.old_branch_name);
                    // $('#old_ifsc').text(response.old_bank_ifsc);
                    // $('#new_acc_no').text(response.new_bank_code);  
                    // $('#new_bank_name').text(response.new_bank_name);
                    // $('#new_branch_name').text(response.new_branch_name);
                    // $('#new_ifsc').text(response.new_bank_ifsc);

                    $('.applicant_id_modal').html('(Beneficiary ID - ' + response
                        .personaldata[0].beneficiary_id + ' , Application ID - ' +
                        response.personaldata[0].application_id + ')');
                    $('#fullForm #id').val(response.personaldata[0].application_id);

                    //New for OTP rejection
                    var updateCode = $('#update_code').val();
                    if (updateCode == 7) {
                        $('#otp_div').hide();
                        $('#fullForm').show();
                    } else {
                        $('#otp_div').hide();
                        $('#fullForm').show();
                    }
                    $('#otp').removeClass('has-error');
                    $('#error_otp').html('');
                    $('#accept_reject_comments').removeClass('has-error');
                    $('#error_accept_reject_comments').html('');
                },
                complete: function() {

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('.ben_view_body').removeClass('disabledcontent');
                    $('#loader_img_personal').hide();
                    $('#loadingDivModal').hide();
                    $('.ben_view_button').removeAttr('disabled', true);
                    $('.ben_view_modal').modal('hide');
                    // ajax_error(jqXHR, textStatus, errorThrown);
                    $.alert({
                        title: 'Error!!',
                        type: 'red',
                        icon: 'fa fa-warning',
                        content: 'Something wrong while fetching the beneficiary data!!',
                    });
                }
            });
            $('.ben_view_modal').modal('show');

        });
        // ------------------- End View Button Click Section -----------------------//

        // ------------------- Process Type Change Section -----------------------//
        $(document).on('change', '#process_type', function() {
            var processVal = this.value;
            if (processVal == 11) {
                // $('#otp_div').hide();
                // $('#verifyReject').show();
                $('#radio_btn_confirm').hide();
            }
            if (processVal == 13) {
                $('#radio_btn_confirm').hide();
            }
        });
        // ------------------- End Process Type Change Section -----------------------//
        $('#bulk_approve').click(function() {
            $("#opreation_type option[value='T']").hide();
            $("#opreation_type option[value='OR']").hide();
            $("#radio_btn_confirm").hide();
            $("#div_overule").hide();
            $(".singleInfo").hide();
            $("#fullForm #is_bulk").val(1);
            $('#opreation_type').val('A').trigger('change');
            $("#verifyReject").html("Approve");
            $('#div_rejection').hide();
            $('#fullForm #id').val('');
            $('#fullForm #application_id').val('');
            $('#accept_reject_comments').val('');
            benid = "";
            if ($('#update_code').val() == 7) {
                $.alert({
                    title: 'Information!',
                    type: 'blue',
                    icon: 'fa fa-info',
                    content: 'Bluk approve not available for application rejection.',
                });
            } else {
                $('#otp_div').hide();
                $('#fullForm').show();
                $('#accept_reject_comments').removeClass('has-error');
                $('#error_accept_reject_comments').html('');
                $('.ben_view_modal').modal('show');
            }
        });

        $(document).on('click', '.opreation_type', function() {
            if ($(this).val() == 'T' || $(this).val() == 'R' || $(this).val() == 'OR') {
                // $('#div_rejection').show();
                if ($(this).val() == 'T')
                    $("#verifyReject").html("Revert");
                else if ($(this).val() == 'R')
                    $("#verifyReject").html("Reject");
                else if ($(this).val() == 'OR') {
                    $('#radio_btn_confirm').show();
                    $('#div_overule').show();
                    // $('#process_type').find('input[type="radio"]').prop('checked', false);
                    $("#verifyReject").html("Over Rule");
                    $("#remarks_heading").html('Reason<span class="text-danger">*</span>');
                }
            } else {
                $("#verifyReject").html("Approve");
                $("#remarks_heading").html('Enter Remarks<span class="text-danger">*</span>');
                $('#div_overule').hide();
                $('#radio_btn_confirm').hide();
                $('#process_type').prop('checked', false);
                $('#div_rejection').hide();
                $("#reject_cause").val('');
            }
        });
        // -------------------- View Button Click Section End -----------------------//

        // --------------- OTP Verification ------------------
        $(document).on('click', '#otp_verify', function() {
            var error_otp = '';

            if ($.trim($('#otp').val()).length == 0) {
                error_otp = 'OTP required';
                $('#error_otp').text(error_otp);
                $('#otp').addClass('has-error');
            } else {
                error_otp = '';
                $('#error_otp').text(error_otp);
                $('#otp').removeClass('has-error');
            }
            if (error_otp != '') {
                return false;
            } else {
                var otp_ver = $('#otp').val();
                $('#loadingDivModal').show();
                $.ajax({
                    type: 'POST',
                    url: "{{ url('nameMismatchRejectOtpVerify') }}",
                    data: {
                        login_otp: otp_ver,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        $('#loadingDivModal').hide();
                        if (response.status == 1) {
                            $('#fullForm #verify_otp_no').val(response.login_otp);
                            $('#fullForm').show();
                            $('#verification_result').removeAttr('class');
                            $('#verification_result').addClass('text-success');
                            $('#verification_result').html(
                                '<i class="fa fa-check"></i> Verified Successfully');
                        } else {
                            $('#fullForm').hide();
                            $('#verification_result').removeAttr('class');
                            $('#verification_result').addClass('text-danger');
                            $('#verification_result').html(
                                '<i class="fa fa-close"></i> Not Verified');
                        }
                    },
                    error: function(jqXHR, textStatus,
                        errorThrown) {
                        $('#loadingDivModal').hide();
                        $.confirm({
                            title: 'Error',
                            type: 'red',
                            icon: 'fa fa-warning',
                            content: 'Something went wrong..!!',
                            buttons: {
                                Ok: function() {
                                    location
                                        .reload();
                                }
                            }
                        });
                        return false;
                    }
                });
            }

        });

        // -------------------- Final Approve Section-------------------------- //
        $(document).on('click', '#verifyReject', function() {
            var error_opreation_type = '';
            var error_accept_reject_comments = '';
            var error_process_type = '';
            var opreation_type = $('#opreation_type').val();
            if ($.trim($('#opreation_type').val()).length == 0) {
                error_opreation_type = 'Operation type required';
                $('#error_opreation_type').text(error_opreation_type);
                $('#opreation_type').addClass('has-error');
            } else {
                error_opreation_type = '';
                $('#error_opreation_type').text(error_opreation_type);
                $('#opreation_type').removeClass('has-error');
            }

            if ($.trim($('#accept_reject_comments').val()).length == '') {
                error_accept_reject_comments = 'Remarks/Reason is required';
                $('#error_accept_reject_comments').text(error_accept_reject_comments);
                $('#accept_reject_comments').addClass('has-error');
            } else {
                error_accept_reject_comments = '';
                $('#error_accept_reject_comments').text(error_accept_reject_comments);
                $('#accept_reject_comments').removeClass('has-error');
            }
            if (opreation_type == 'OR') {
                if ($.trim($('input[name="process_type"]:checked').val()).length == '') {
                    error_process_type = 'Operation type is required';
                    $('#error_process_type').text(error_process_type);
                    $('#process_type').addClass('has-error');
                } else {
                    error_process_type = '';
                    $('#error_process_type').text(error_process_type);
                    $('#process_type').removeClass('has-error');
                }
                var valid = 0;
                if (error_opreation_type != '' || error_accept_reject_comments != '' || error_process_type != '') {
                    return false;
                } else {
                    valid = 1;
                }
            } else {
                var valid = 0;
                if (error_opreation_type != '' || error_accept_reject_comments != '') {
                    return false;
                } else {
                    valid = 1;
                }
            }
            // $('input[name="process_type"]').prop('checked', false);
            var reject_cause = $('#reject_cause').val();
            var opreation_type = $('#opreation_type').val();
            var accept_reject_comments = $('#accept_reject_comments').val();
            var is_bulk = $('#is_bulk').val();
            var single_app_id = $('#application_id').val();
            var applicantId = $('#applicantId').val();
            var update_code = $('#update_code').val();
            var processType = $('input[name="process_type"]:checked').val();
            var match_type = $('#match_type').val();
            var name_matching_score = $('#name_matching_score').val();
            var alert_msg = '';
            if (match_type == 2) {
                // alert(processType);
                if (opreation_type == 'OR') {
                    if (processType == 11) {
                        var alert_msg = '<span class="text-danger"><b>Name minor mismatch is' + name_matching_score + '%.<b><span><br>Are you sure to allow it as minor mismatch?';
                    } else if (processType == 13) {
                        var alert_msg = 'Beneficiary will be rejected.';
                    } else {
                        if (update_code == 6) {
                            var alert_msg = '<span class="text-danger"><b>Name minor mismatch is' + name_matching_score + '%.<b><span><br>Are you sure to allow it as minor mismatch?';
                        } else {
                            var alert_msg = 'Beneficiary will be rejected.';
                        }
                    }
                } else {
                    alert_msg = '<span class="text-danger"><b>Name minor mismatch is' + name_matching_score + '%.<b><span><br>Are you sure to allow it as minor mismatch?';
                }
            } else {
                if (processType == 11) {
                    var alert_msg = 'Name as in Portal will be replace with Bank Response Name.';
                } else if (processType == 13) {
                    var alert_msg = 'Beneficiary will be rejected.';
                } else {
                    if (update_code == 6) {
                        var alert_msg = 'Name as in Portal will be replace with Bank Response Name.';
                    } else {
                        var alert_msg = 'Beneficiary will be rejected.';
                    }
                }
            }
            // alert(processType);
            var op_text = $("#opreation_type option:selected").text();
            var login_otp_no = $('#verify_otp_no').val();
            if (valid == 1) {
                $.confirm({
                    title: 'Confirm',
                    type: 'blue',
                    icon: 'fa fa-check',
                    content: 'Are you sure want to ' + op_text.toLowerCase() +
                        ' these beneficiaries ?</br><b>Note:' + alert_msg + '</b>',
                    buttons: {
                        confirm: {
                            text: 'confirm',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function() {
                                $("#submitting").show();
                                $("#verifyReject").hide();
                                $('#loadingDivModal').show();
                                var id = $('#id').val();
                                $.ajax({
                                    type: 'POST',
                                    url: "{{ url('updateFailedNameApprove90to100') }}",
                                    data: {
                                        reject_cause: reject_cause,
                                        opreation_type: opreation_type,
                                        update_code: update_code,
                                        accept_reject_comments: accept_reject_comments,
                                        application_id: id,
                                        is_bulk: is_bulk,
                                        applicantId: applicantId,
                                        single_app_id: single_app_id,
                                        otp_login: login_otp_no,
                                        processType: processType,
                                        _token: '{{ csrf_token() }}',
                                    },
                                    success: function(data) {
                                        // dataTable.ajax.reload();
                                        var table_renew = $('#example')
                                            .DataTable();
                                        table_renew.ajax.reload(null, false);
                                        //$('#example').DataTable().ajax.reload()
                                        $('#loadingDivModal').hide();
                                        if (data.return_status == 1) {

                                            $('.ben_view_modal').modal('hide');
                                            $('#approve_rejdiv').hide();
                                            $.confirm({
                                                title: 'Success',
                                                type: 'green',
                                                icon: 'fa fa-check',
                                                content: data
                                                    .return_msg,
                                                buttons: {
                                                    Ok: function() {
                                                        $("#submitting")
                                                            .hide();
                                                        $("#verifyReject")
                                                            .show();
                                                        $("html, body")
                                                            .animate({
                                                                    scrollTop: 0
                                                                },
                                                                "slow"
                                                            );
                                                    }
                                                }
                                            });
                                        } else {
                                            $("#submitting").hide();
                                            $("#verifyReject").show();
                                            $('.ben_view_modal').modal('hide');
                                            $('#approve_rejdiv').hide();
                                            $.alert({
                                                title: 'Error',
                                                type: 'red',
                                                icon: 'fa fa-warning',
                                                content: data.return_msg
                                            });
                                        }
                                    },
                                    error: function(jqXHR, textStatus,
                                        errorThrown) {
                                        $('#loadingDivModal').hide();
                                        $.confirm({
                                            title: 'Error',
                                            type: 'red',
                                            icon: 'fa fa-warning',
                                            content: 'Something went wrong in the approval!!',
                                            buttons: {
                                                Ok: function() {
                                                    // $("#verifyReject").show();
                                                    //  $("#submitting").hide();
                                                    location
                                                        .reload();
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        },
                        Cancel: function() {

                        },
                    }
                });
            }
        });
        // -------------------- Final Approve Section --------------------------// 

        // --------------- Filter Section -------------------- //
        $('#filter').click(function() {
            var update_t = $('#update_code').val();
            if (update_t == '') {
                alert('Please select edited type!!');
                $('#update_code').focus();
            } else {
                if (update_t == 13) {
                    $('#check_all_btn').attr('disabled', true);
                } else {
                    $('#check_all_btn').attr('disabled', false);
                }
                $('#res_div').show();
                $('#approve_rejdiv').hide();
                // loadDatatable();
                dataTable.ajax.reload();
            }

            if (update_t == 6) {
                $('.minor_process').hide();
                $('.reject_process').show();
            }
            if (update_t == 7) {
                $('.reject_process').hide();
                $('.minor_process').show();
            }
        });

        $('#reset').click(function() {
            $('#filter_1').val('').trigger('change');
            $('#filter_2').val('').trigger('change');
            $('#block_ulb_code').val('').trigger('change');
            $('#gp_ward_code').val('').trigger('change');
            $('#failed_type').val('').trigger('change');
            $('#pay_mode').val('').trigger('change');
            $('#update_code').val('').trigger('change');
            dataTable.ajax.reload();
            // loadDatatable();
        });
        // --------------- Filter Section End-------------------- //

        // ------------ Master DropDown Section Start-------------------- //
        $('#filter_1').change(function() {
            var filter_1 = $(this).val();

            $('#filter_2').html('<option value="">--All --</option>');
            $('#block_ulb_code').html('<option value="">--All --</option>');
            select_district_code = $('#dist_code').val();

            var htmlOption = '<option value="">--All--</option>';
            $('#gp_ward_code').html('<option value="">--All --</option>');
            if (filter_1 == 1) {
                $.each(subDistricts, function(key, value) {
                    if ((value.district_code == select_district_code)) {
                        htmlOption += '<option value="' + value.id + '">' + value.text +
                            '</option>';
                    }
                });
                $("#blk_sub_txt").text('Subdivision');
                $("#gp_ward_txt").text('Ward');
                $("#municipality_div").show();
                $("#gp_ward_div").show();
            } else if (filter_1 == 2) {
                // console.log(filter_1);
                $.each(blocks, function(key, value) {
                    if ((value.district_code == select_district_code)) {
                        htmlOption += '<option value="' + value.id + '">' + value.text +
                            '</option>';
                    }
                });
                $("#blk_sub_txt").text('Block');
                $("#gp_ward_txt").text('GP');
                $("#municipality_div").hide();
                $("#gp_ward_div").show();
            } else {
                $("#blk_sub_txt").text('Block/Subdivision');
                $("#gp_ward_txt").text('GP/Ward');
                $("#municipality_div").hide();
            }
            $('#filter_2').html(htmlOption);

        });
        $('#filter_2').change(function() {
            var rural_urbanid = $('#filter_1').val();
            $('#gp_ward_code').html('<option value="">--All --</option>');
            if (rural_urbanid == 1) {
                var sub_district_code = $(this).val();
                if (sub_district_code != '') {
                    $('#block_ulb_code').html('<option value="">--All --</option>');
                    select_district_code = $('#dist_code').val();
                    var htmlOption = '<option value="">--All--</option>';
                    $.each(ulbs, function(key, value) {
                        if ((value.district_code == select_district_code) && (value
                                .sub_district_code == sub_district_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                    $('#block_ulb_code').html(htmlOption);
                } else {
                    $('#block_ulb_code').html('<option value="">--All --</option>');
                }
            } else if (rural_urbanid == 2) {
                $('#muncid').html('<option value="">--All --</option>');
                $("#municipality_div").hide();
                var block_code = $(this).val();
                select_district_code = $('#dist_code').val();
                var htmlOption = '<option value="">--All--</option>';
                $.each(gps, function(key, value) {
                    if ((value.district_code == select_district_code) && (value.block_code ==
                            block_code)) {
                        htmlOption += '<option value="' + value.id + '">' + value.text +
                            '</option>';
                    }
                });
                $('#gp_ward_code').html(htmlOption);
                $("#gp_ward_div").show();
            } else {
                $('#block_ulb_code').html('<option value="">--All --</option>');
            }
        });
        $('#block_ulb_code').change(function() {
            var muncid = $(this).val();
            var district = $("#dist_code").val();
            var urban_code = $("#filter_1").val();
            if (district == '') {
                $('#filter_1').val('');
                $('#filter_2').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
            }
            if (urban_code == '') {
                // alert('Please Select Rural/Urban First');
                $('#filter_2').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $("#filter_1").focus();
            }
            if (muncid != '') {
                var rural_urbanid = $('#filter_1').val();
                if (rural_urbanid == 1) {
                    $('#gp_ward_code').html('<option value="">--All --</option>');
                    var htmlOption = '<option value="">--All--</option>';
                    $.each(ulb_wards, function(key, value) {
                        if (value.urban_body_code == muncid) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                    $('#gp_ward_code').html(htmlOption);
                    //console.log(htmlOption);
                } else {
                    $('#gp_ward_code').html('<option value="">--All --</option>');
                    $("#gp_ward_div").hide();
                }
            } else {
                $('#gp_ward_code').html('<option value="">--All --</option>');
            }
        });
        // ------------ Master DropDown Section End-------------------- //



    });

    function controlCheckBox() {
        var anyBoxesChecked = false;
        var applicantId = Array();
        $(' input[type="checkbox"]').each(function() {
            if ($(this).is(":checked")) {
                anyBoxesChecked = true;
                applicantId.push($(this).val());
            }

        });
        $("#fullForm #applicantId").val($.unique(applicantId));
        if (anyBoxesChecked == true) {
            $('#approve_rejdiv').show();
            $("#check_all_btn").attr("disabled", true);
            $('.ben_view_button').attr('disabled', true);
            document.getElementById('bulk_approve').disabled = false;
            // document.getElementById('bulk_blkchange').disabled = false;
        } else {
            $('#approve_rejdiv').hide();
            $('.ben_view_button').removeAttr('disabled', true);
            $("#check_all_btn").removeAttr("disabled", true);
            document.getElementById('bulk_approve').disabled = true;
            // document.getElementById('bulk_blkchange').disabled = true;
        }
        // console.log(applicantId);
    }
</script>
@endpush