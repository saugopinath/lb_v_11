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

@extends('layouts.app-template-datatable')
@section('content')
<style>
    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }
</style>

<!-- Content Wrapper -->

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="">
                <h1>
                    @if($processType == 1)
                    Beneficiaries With 90% - 100% Score(Name Match)
                    @else
                    Beneficiaries With 40% - 89% Score(Name Match)
                    @endif
                </h1>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-body">
                <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

                <div class="card card-default">
                    <div class="card-header">
                        <h5 class="card-title"><span id="panel-icon">Filter Here</span></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>{{ $message }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif
                            @if (count($errors) > 0)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                    <li><strong>{{ $error }}</strong></li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif
                        </div>

                        @if ($duty_level == 'SubdivVerifier' || $duty_level=='SubdivDelegated Verifier')
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label class="form-label">Municipality</label>
                                <select name="filter_1" id="filter_1" class="form-select js-municipality">
                                    <option value="">-----All----</option>
                                    @foreach ($ulb_gp as $urban_body)
                                    <option value="{{ $urban_body->urban_body_code }}">
                                        {{ $urban_body->urban_body_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" id="ward_div">Wards</label>
                                <select name="filter_2" id="filter_2" class="form-select">
                                    <option value="">-----Select----</option>
                                </select>
                            </div>
                            <div class="col-md-3 align-self-end">
                                <button type="button" name="filter" id="filter" class="btn btn-success">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                &nbsp;&nbsp;
                                <button type="button" name="excel_btn" id="excel_btn" class="btn btn-primary">
                                    <i class="fas fa-file-excel"></i> Get Excel
                                </button>
                            </div>
                        </div>
                        @elseif($duty_level == 'BlockVerifier' || $duty_level=='BlockDelegated Verifier')
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Gram Panchayat</label>
                                <select name="filter_1" id="filter_1" class="form-select">
                                    <option value="">-----All----</option>
                                    @foreach ($ulb_gp as $gp)
                                    <option value="{{ $gp->gram_panchyat_code }}">
                                        {{ $gp->gram_panchyat_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 align-self-end">
                                <button type="button" name="filter" id="filter" class="btn btn-success">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                &nbsp;&nbsp;
                                <button type="button" name="excel_btn" id="excel_btn" class="btn btn-primary">
                                    <i class="fas fa-file-excel"></i> Get Excel
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card card-default">
                    <div class="card-header">
                        <h5 class="card-title" id="panel_head">List of account validation beneficiaries</h5>
                    </div>
                    <div class="card-body">
                        <div id="loadingDiv"></div>
                        <div class="table-responsive">
                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th>Beneficiary ID</th>
                                        <th>Beneficiary Name</th>
                                        <th>Swasthya Sathi Card No.</th>
                                        <th>Block/ Municipality Name</th>
                                        <th>Application Id</th>
                                        <th>Mobile No</th>
                                        <th>Account No</th>
                                        <th>IFSC Code</th>
                                        <th>Failure Type</th>
                                        <th>Failure Month</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Update Bank Details Modal -->
<div class="modal fade ben_bank_modal" id="bankModal" tabindex="-1" aria-labelledby="bankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankModalLabel">Name Validation Failed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loadingDivModal"></div>
                <div class="card card-default">
                    <div class="card-body">
                        <table class="table table-bordered table-striped table-condensed" style="font-size: 14px;">
                            <tbody>
                                <tr>
                                    <th scope="row">Beneficiary Id</th>
                                    <td id="ben_id_text"></td>
                                    <th scope="row">Application Id</th>
                                    <td id="app_id_text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Beneficiary Name</th>
                                    <td id="ben_name_text"></td>
                                    <th scope="row">Father's Name</th>
                                    <td id="father_name_text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Gender</th>
                                    <td id="gender_text"></td>
                                    <th scope="row">Date of Birth :(DD-MM-YYYY)</th>
                                    <td id="dob_text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Caste</th>
                                    <td id="caste_text"></td>
                                    <th scope="row">Mobile No</th>
                                    <td id="mobile_no_text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Block/ Municipality</th>
                                    <td id="block_ulb_name_text"></td>
                                    <th scope="row">GP/ Ward</th>
                                    <td id="gp_ward_name_text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Bank A/c No.</th>
                                    <td id="bank_account_number_text"></td>
                                    <th scope="row">Bank Name</th>
                                    <td id="bank_name_text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Bank IFSC</th>
                                    <td id="bank_ifsc_text"></td>
                                    <th scope="row">Branch Name</th>
                                    <td id="branch_name_text"></td>
                                </tr>
                            </tbody>
                        </table>

                        <input type="hidden" id="mismatch_type" name="mismatch_type" value="{{$processType}}">
                        <input type="hidden" id="benId" name="benId" value="">
                        <input type="hidden" id="faildTableId" name="faildTableId" value="">
                        <input type="hidden" id="nameStatusCode" name="nameStatusCode" value="">
                        <input type="hidden" id="application_id" name="application_id" value="">
                        <input type="hidden" id="old_bank_ifsc" name="old_bank_ifsc" value="">
                        <input type="hidden" id="old_bank_accno" name="old_bank_accno" value="">
                        <input type="hidden" id="name_matching_score" name="name_matching_score" value="">
                        <input type="hidden" name="verify_otp_no" id="verify_otp_no" value="" />

                        <div class="mb-3 text-center">
                            <div style="font-size: 16px;">Name As in Portal :- <b><span id="name_as_in_portal" class="text-danger"></span></b></div>
                            <div style="font-size: 16px;">Name Response from Bank :- <span id="name_response" class="text-success"></span></div>
                            <div style="font-size: 16px;">Name Matching Score:- <b><span id="matching_score" class="text-info"></span>%</b></div>
                            <div style="font-size: 14px; font-weight: bold; font-style: italic; display: none;" class="text-warning" id="note_msg">
                                If minor mismatch is selected then beneficiary name as in portal will be replace with name response from bank after approval.
                            </div>
                        </div>

                        <p style="font-size: 12px; font-weight: bold; text-align:center; display: none;">
                            All (<span style="color:firebrick"> * </span>) marks filled are mandatory
                        </p>

                        <div class="card mb-3">
                            <div class="card-body bg-light">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="process_type" id="process_type" value="1">
                                    <label class="form-check-label" for="process_type_1">
                                        Minor mismatch, Keep existing bank information
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="process_type" id="process_type" value="3">
                                    <label class="form-check-label" for="process_type_3">
                                        Application is rejected due to major mismatch
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="radio_btn_confirm" style="font-size: 14px; font-weight: bold; font-style: italic;" class="text-warning text-center">
                            Please select which one do you want to process ?
                        </div>

                        <table class="table table-bordered table-responsive" style="width:100%;">
                            <tbody>
                                <tr class="bankInfoHideShow" style="display: none;">
                                    <th scope="row" class="required" style="font-size: 14px;">Bank Branch Name</th>
                                    <td id="branch_text">
                                        <input type="text" value="" name="branch_name" id="branch_name" class="form-control" readonly>
                                        <span style="font-size: 14px;" id="error_bank_branch" class="text-danger"></span>
                                    </td>
                                    <th scope="row" class="required" style="font-size: 14px;">Bank IFSC Code</th>
                                    <td id="bank_ifsc_text">
                                        <input type="text" value="" name="bank_ifsc" onkeyup="this.value = this.value.toUpperCase();" id="bank_ifsc" class="form-control">
                                        <img src="{{ asset('images/ajaxgif.gif') }}" width="60px" id="ifsc_loader" style="display: none;">
                                        <span style="font-size: 14px;" id="error_bank_ifsc_code" class="text-danger"></span>
                                    </td>
                                </tr>
                                <tr class="bankInfoHideShow" style="display: none;">
                                    <th scope="row" class="required" style="font-size: 14px;">Bank Name</th>
                                    <td id="bank_text">
                                        <input type="text" value="" name="bank_name" maxlength="200" id="bank_name" class="form-control" readonly>
                                        <span style="font-size: 14px;" id="error_name_of_bank" class="text-danger"></span>
                                    </td>
                                    <th scope="row" class="required" style="font-size: 14px;">Bank Account Number</th>
                                    <td id="bank_acc_text">
                                        <input type="text" value="" name="bank_account_number" maxlength='20' id="bank_account_number" class="form-control">
                                        <span style="font-size: 14px;" id="error_bank_account_number" class="text-danger"></span>
                                    </td>
                                </tr>
                                <tr class="documentSectionHideShow" style="display: none;">
                                    <th scope="row" class="required" style="font-size: 14px;"><span id="upload_text">Upload Enquiry Report</span></th>
                                    <td id="bank_passbook_text" colspan="3">
                                        <input type="file" name="upload_enquiry_report" accept=".jpg,.jpeg,.png,.pdf" id="upload_enquiry_report" value="" class="form-control">
                                        <small class="text-info" style="font-weight: normal;">
                                            (Only jpeg,jpg,png,pdf file and maximum size should be less than 500 KB)
                                        </small>
                                        <span style="font-size: 14px;" id="error_file" class="text-danger"></span>
                                    </td>
                                </tr>
                                <tr class="remarkSectionHideShow" style="display: none;">
                                    <th scope="row" class="required" style="font-size: 14px;">Remarks</th>
                                    <td colspan="3">
                                        <input type="text" name="remarks" maxlength="100" id="remarks" class="form-control" value="">
                                        <span style="font-size: 14px;" id="error_remarks" class="text-danger"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="row mb-3" id="otp_div" style="display: none;">
                            <div class="col-12">
                                <span class="text-warning" style="font-weight: bold;">For Rejection you have to enter your Login OTP for verification.</span>
                            </div>
                            <div class="col-md-4 mt-2">
                                <input type="text" name="otp" id="otp" maxlength="6" placeholder="Enter Last Login OTP" class="form-control">
                                <span class="text-danger" id="error_otp"></span>
                            </div>
                            <div class="col-md-4 mt-2">
                                <button class="btn btn-primary" id="otp_verify" name="otp_verify">Verify</button>
                                <span style="margin-left: 10px; font-weight: bold;" id="verification_result"></span>
                            </div>
                        </div>

                        <div class="row" id="finalUpdatediv" style="display: none;">
                            <div class="col-12 text-center">
                                <input type="button" name="submit" value="Send to Approval" class="btn btn-success btn-lg verifySubmit">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Passbook View Modal -->
<div class="modal fade encolser_modal" id="encolser_modal" tabindex="-1" aria-labelledby="encolserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="encolser_name">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="encolser_content"></div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success modalEncloseClose">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- </div> -->


@endsection
@push('scripts')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.sidebar-menu li').removeClass('active');
        $('.sidebar-menu #verifyNmeMinorMismatch').addClass("active");
        $('.sidebar-menu #verifyNameMinorMismatch').addClass("active");
        $('#upload_enquiry_report').change(function() {
            var card_file = document.getElementById("upload_enquiry_report");
            if (card_file.value != "") {
                var attachment;
                attachment = card_file.files[0];
                // console.log(attachment.type)
                var type = attachment.type;
                if (attachment.size > 1048576) {
                    document.getElementById("error_file").innerHTML =
                        "<br><i class='fa fa-warning'></i> Unaccepted document file size. Max size 500 KB. Please try again";
                    $('#upload_enquiry_report').val('');
                    return false;
                } else if (type != 'image/jpeg' && type != 'image/png' && type != 'application/pdf') {
                    document.getElementById("error_file").innerHTML =
                        "<br><i class='fa fa-warning'></i> Unaccepted document file format. Only jpeg,jpg,png and pdf. Please try again";
                    $('#upload_enquiry_report').val('');
                    return false;
                } else {
                    $('#file_upload_btn').show();
                    document.getElementById("error_file").innerHTML = "";
                }
            }
        });
        $('#excel_btn').click(function() {
            var token = "{{ csrf_token() }}";
            var filter_1 = $('#filter_1').val();
            var filter_2 = $('#filter_2').val();
            var failed_type = 3;
            var pay_mode = $('#pay_mode').val();
            var data = {
                '_token': token,
                'filter_1': filter_1,
                'filter_2': filter_2,
                'failed_type': failed_type,
                'pay_mode': pay_mode
            };
            redirectPost('getBankFailedexcel', data);
        });
        $('.modalEncloseClose').click(function() {
            $('.encolser_modal').modal('hide');
        });

        // $('#loadingDiv').hide();
        $('.sidebar-menu li').removeClass('active');
        $('.sidebar-menu #bankTrFailed').addClass("active");
        $('.sidebar-menu #nameValTrFailed').addClass("active");

        $('.ben_bank_modal').on('hidden.bs.modal', function(e) {
            $('#bank_ifsc').val('');
            $('#bank_name').val('');
            $('#branch_name').val('');
            $('#bank_account_number').val('');
            $('#benId').val('');
            $('#faildTableId').val('');
            $('#old_bank_ifsc').val('');
            $('#old_bank_accno').val('');
            $('#upload_enquiry_report').val('');
        });
        $(document).on('change', '#process_type', function() {
            var processVal = this.value;
            var nameMatchingScore = $('#name_matching_score').val();
            if (processVal == 1) {
                if (nameMatchingScore >= 40 && nameMatchingScore <= 89) {
                    $('.documentSectionHideShow').show();
                } else {
                    $('.documentSectionHideShow').hide();
                }
                $('.remarkSectionHideShow').hide();
                $('.bankInfoHideShow').hide();
                $('#note_msg').show();
                // $('#upload_text').text('Upload Field Verificiation Report');
                $('#finalUpdatediv').show();
                $('#radio_btn_confirm').hide();
                $('#upload_enquiry_report').val('');
                $('#remarks').val('');
                $('#bank_ifsc').val('');
                $('#bank_name').val('');
                $('#branch_name').val('');
                $('#bank_account_number').val('');
                $('#otp_div').hide();
            } else if (processVal == 2) {
                $('.documentSectionHideShow').show();
                $('.remarkSectionHideShow').show();
                $('.bankInfoHideShow').show();
                $('#note_msg').hide();
                $('#upload_text').text('Upload Bank Passbook');
                $('#finalUpdatediv').show();
                $('#radio_btn_confirm').hide();
                $('#upload_enquiry_report').val('');
                $('#remarks').val('');
                $('#bank_ifsc').val('');
                $('#bank_name').val('');
                $('#branch_name').val('');
                $('#bank_account_number').val('');
                $('#otp_div').hide();
            } else if (processVal == 3) {
                $('.documentSectionHideShow').hide();
                $('.remarkSectionHideShow').show();
                $('.bankInfoHideShow').hide();
                // $('#upload_text').text('Upload Field Verificiation Report');
                $('#note_msg').hide();
                $('#finalUpdatediv').hide();
                $('#radio_btn_confirm').hide();
                $('#upload_enquiry_report').val('');
                $('#remarks').val('');
                $('#bank_ifsc').val('');
                $('#bank_name').val('');
                $('#branch_name').val('');
                $('#bank_account_number').val('');
                $('#otp_div').show();
            } else {
                $('.documentSectionHideShow').hide();
                $('.remarkSectionHideShow').hide();
                $('.bankInfoHideShow').hide();
                $('#upload_text').text('');
                $('#note_msg').hide();
                $('#finalUpdatediv').hide();
                $('#radio_btn_confirm').show();
                $('#upload_enquiry_report').val('');
                $('#remarks').val('');
                $('#bank_ifsc').val('');
                $('#bank_name').val('');
                $('#branch_name').val('');
                $('#bank_account_number').val('');
                $('#otp_div').hide();
            }
        });
        $(document).on('click', '.bank_edit_btn', function() {
            $('#loadingDiv').show();
            var editvalue = this.id;

            $.ajax({
                type: 'post',
                url: "{{ route('editFailedNameDetails') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    editvalue: editvalue
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);

                    $('#loadingDiv').hide();
                    $('#loadingDivModal').hide();
                    $("input:radio").attr("checked", false);
                    $('#radio_btn_confirm').show();
                    $('.documentSectionHideShow').hide();
                    $('.remarkSectionHideShow').hide();
                    $('.bankInfoHideShow').hide();
                    $('#finalUpdatediv').hide();
                    $('#note_msg').hide();
                    $('.ben_bank_modal').modal('show');

                    $('#failed_reason').text(response.failed_reason);
                    $('#ben_id_modal').text(response.ben_id);
                    $('#name_as_in_portal').text(response.ben_name);
                    $('#ben_name_text').text(response.ben_name);
                    $('#father_name_text').text(response.benfather_name);
                    $('#gender_text').text(response.gender);
                    $('#dob_text').text(response.dob);
                    $('#caste_text').text(response.caste);
                    $('#ben_id_text').text(response.ben_id);
                    $('#app_id_text').text(response.application_id);
                    $('#matching_score').text(response.matching_score);
                    $('#name_matching_score').val(response.matching_score);
                    // $('#bank_name').val(response.bank_name);
                    // $('#branch_name').val(response.branch_name);
                    // $('#bank_account_number').val(response.bank_code);
                    $('#bank_ifsc_text').text(response.bank_ifsc);
                    $('#bank_name_text').text(response.bank_name);
                    $('#branch_name_text').text(response.branch_name);
                    $('#bank_account_number_text').text(response.bank_code);
                    $('#mobile_no_text').text(response.mobile_no);
                    $('#block_ulb_name_text').text(response.block_ulb_name);
                    $('#gp_ward_name_text').text(response.gp_ward_name);
                    $('#benId').val(response.ben_id);
                    $('#faildTableId').val(response.failedid);
                    $('#nameStatusCode').val(response.status_code);
                    $('#application_id').val(response.application_id);
                    $('#old_bank_ifsc').val(response.bank_ifsc);
                    $('#old_bank_accno').val(response.bank_code);
                    if (response.name_response == '') {
                        $('#name_response').html(
                            '<span style="font-size: small; font-style: italic;">No name received from bank.</span>'
                        );
                    } else {
                        $('#name_response').html('<span style="font-weight: bold;">' +
                            response.name_response + '</span>');
                    }
                    $('#otp_div').hide();
                    $('#verification_result').removeAttr('class');
                    $('#verification_result').html('');
                    $('#otp').val('');
                    $('#otp').removeClass('has-error');
                    $('#error_otp').html('');

                    $('#branch_name').removeClass('has-error');
                    $('#error_bank_branch').html('');
                    $('#bank_name').removeClass('has-error');
                    $('#error_name_of_bank').html('');
                    $('#bank_ifsc').removeClass('has-error');
                    $('#error_bank_ifsc_code').html('');
                    $('#bank_account_number').removeClass('has-error');
                    $('#error_bank_account_number').html('');
                    $('#otp').removeClass('has-error');
                    $('#error_otp').html('');
                    $('#remarks').removeClass('has-error');
                    $('#error_remarks').html('');
                    $('#upload_enquiry_report').removeClass('has-error');
                    $('#upload_enquiry_report').val('');
                    $('#error_file').html('');
                },
                complete: function() {

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#loadingDiv').hide();
                    $('#loadingDivModal').hide();
                    ajax_error(jqXHR, textStatus, errorThrown)

                }

            });
        });

        var dataTable = "";
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
            "pageLength": 10,
            'lengthMenu': [
                [10, 20, 30],
                [10, 20, 30]
            ],
            "serverSide": true,
            "processing": true,
            "bRetrieve": true,
            "oLanguage": {
                "sProcessing": '<div class="preloader1" align="center"><h4 class="text-success" style="font-weight:bold;font-size:22px;">Processing...</h4></div>'
            },
            ajax: {
                url: "{{ route('getDataNameValidationFailed90to100') }}",
                type: "POST",
                data: function(d) {
                    d.filter_1 = $('#filter_1').val(),
                        d.filter_2 = $('#filter_2').val(),
                        d.failed_type = $('#failed_type').val(),
                        d.pay_mode = $('#pay_mode').val(),
                        d.mismatch_type = $('#mismatch_type').val(),
                        d._token = "{{ csrf_token() }}"
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#loadingDiv').hide();
                    ajax_error(jqXHR, textStatus, errorThrown)
                }
            },
            "initComplete": function(record) {
                $('#loadingDiv').hide();
            },
            "columns": [{
                    "data": "DT_RowIndex"
                },
                {
                    "data": "id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "ss_cardno"
                },
                {
                    "data": "block_ulb_name"
                },
                {
                    "data": "application_id"
                },
                {
                    "data": "mobile_no"
                },
                {
                    "data": "accno"
                },
                {
                    "data": "ifsc"
                },
                {
                    "data": "type"
                },
                {
                    "data": "failure_month"
                },
                {
                    "data": "action"
                }
            ],
            "columnDefs": [{
                "targets": [],
                "visible": false,
                "searchable": true
            }],
            "buttons": [{
                    extend: 'pdf',
                    title: "Name Validation Failed Report - Generated On {{ date('F j, Y g:i:a') }}",
                    messageTop: "Date: {{ date('F j, Y g:i:a') }}",
                    footer: true,
                    orientation: 'landscape',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    },
                    className: 'table-action-btn'
                },
                {
                    extend: 'print',
                    title: "Name Validation Failed Report - Generated On {{ date('F j, Y g:i:a') }}",
                    messageTop: "Date: {{ date('F j, Y g:i:a') }}",
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    },
                    className: 'table-action-btn'
                },
                {
                    extend: 'excel',
                    title: "Name Validation Failed Report - Generated On {{ date('F j, Y g:i:a') }}",
                    messageTop: "Date: {{ date('F j, Y g:i:a') }}",
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    },
                    className: 'table-action-btn'
                },
                {
                    extend: 'copy',
                    title: "Name Validation Failed Report - Generated On {{ date('F j, Y g:i:a') }}",
                    messageTop: "Date: {{ date('F j, Y g:i:a') }}",
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    },
                    className: 'table-action-btn'
                },
                {
                    extend: 'csv',
                    title: "Name Validation Failed Report - Generated On {{ date('F j, Y g:i:a') }}",
                    messageTop: "Date: {{ date('F j, Y g:i:a') }}",
                    footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    },
                    className: 'table-action-btn'
                }
            ],
        });

        $('#filter').click(function() {
            dataTable.ajax.reload();
        });

        $('.js-municipality').change(function() {
            var municipality = $('.js-municipality').val();
            var district_code = $('#dist_code').val();
            var htmlOption = '<option value="">----Select----</option>';
            if (municipality != '') {
                $.each(ulb_wards, function(key, value) {
                    if ((value.urban_body_code == municipality)) {
                        htmlOption += '<option value="' + value.id + '">' + value.text +
                            '</option>';
                    }
                });
            } else {
                htmlOption = '<option value="">----Select----</option>';
            }
            $('#filter_2').html(htmlOption);
        });

        $('#bank_ifsc').blur(function() {
            $ifsc_data = $.trim($('#bank_ifsc').val());
            $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
            if ($ifscRGEX.test($ifsc_data)) {
                $('#bank_ifsc').removeClass('has-error');
                $('#error_bank_ifsc_code').text('');
                $('#ifsc_loader').show();
                $('.verifySubmit').attr('disabled', true);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('bankIfsc') }}",
                    data: {
                        ifsc: $ifsc_data,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(data) {
                        $('#ifsc_loader').hide();
                        $('.verifySubmit').removeAttr('disabled', true);
                        if (data.status == 2) {
                            Swal.fire({
                                icon: 'info',
                                title: 'IFSC Not Found!',
                                text: 'This ' + $ifsc_data + ' IFSC is not registered in our system.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'

                            });
                            $('#bank_ifsc').val('');
                            return false;
                        } else {
                            $('#bank_name').val(data.bank_details.bank);
                            $('#branch_name').val(data.bank_details.branch);
                        }


                    },
                    error: function(ex) {
                        $('#ifsc_loader').hide();
                        $('#error_bank_ifsc_code').text('Data fetch error');
                        $('#bank_ifsc').addClass('has-error');
                    }
                });

            } else {
                $('#error_bank_ifsc_code').text('IFSC format invalid please check the code');
                $('#bank_ifsc').addClass('has-error');
            }
        });

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
                            $('#verify_otp_no').val(response.login_otp);
                            $('#finalUpdatediv').show();
                            $('#verification_result').removeAttr('class');
                            $('#verification_result').addClass('text-success');
                            $('#verification_result').html(
                                '<i class="fa fa-check"></i> Verified Successfully');
                        } else {
                            $('#finalUpdatediv').hide();
                            $('#verification_result').removeAttr('class');
                            $('#verification_result').addClass('text-danger');
                            $('#verification_result').html(
                                '<i class="fa fa-close"></i> Not Verified');
                        }
                    },
                    error: function(jqXHR, textStatus,
                        errorThrown) {
                        $('#loadingDivModal').hide();
                        Swal.fire({
                            title: 'Error',
                            icon: 'error',
                            text: 'Something went wrong..!!',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                        return false;
                    }
                });
            }

        });

        $(document).on('click', '.verifySubmit', function() {
            var error_name_of_bank = '';
            var error_bank_branch = '';
            var error_bank_account_number = '';
            var error_bank_ifsc_code = '';
            var error_remarks = '';
            var error_file = '';

            // Validation functions
            if ($.trim($('#bank_name').val()).length == 0) {
                error_name_of_bank = 'Name of Bank is required';
                $('#error_name_of_bank').text(error_name_of_bank);
                $('#bank_name').addClass('is-invalid');
            } else {
                error_name_of_bank = '';
                $('#error_name_of_bank').text(error_name_of_bank);
                $('#bank_name').removeClass('is-invalid');
            }

            if ($.trim($('#branch_name').val()).length == 0) {
                error_bank_branch = 'Bank Branch is required';
                $('#error_bank_branch').text(error_bank_branch);
                $('#branch_name').addClass('is-invalid');
            } else {
                error_bank_branch = '';
                $('#error_bank_branch').text(error_bank_branch);
                $('#branch_name').removeClass('is-invalid');
            }

            if ($.trim($('#bank_account_number').val()).length == 0) {
                error_bank_account_number = 'Bank Account Number is required';
                $('#error_bank_account_number').text(error_bank_account_number);
                $('#bank_account_number').addClass('is-invalid');
            } else {
                error_bank_account_number = '';
                $('#error_bank_account_number').text(error_bank_account_number);
                $('#bank_account_number').removeClass('is-invalid');
            }

            if ($.trim($('#bank_ifsc').val()).length == 0) {
                error_bank_ifsc_code = 'IFS Code is required';
                $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
                $('#bank_ifsc').addClass('is-invalid');
            } else {
                error_bank_ifsc_code = '';
                $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
                $('#bank_ifsc').removeClass('is-invalid');
            }

            $ifsc_data = $.trim($('#bank_ifsc').val());
            $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
            if ($ifscRGEX.test($ifsc_data)) {
                error_bank_ifsc_code = '';
                $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
                $('#bank_ifsc').removeClass('is-invalid');
            } else {
                error_bank_ifsc_code = 'Please check IFS Code format';
                $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
                $('#bank_ifsc').addClass('is-invalid');
            }

            if ($.trim($('#remarks').val()).length == 0) {
                error_remarks = 'Please add some remarks';
                $('#error_remarks').text(error_remarks);
                $('#remarks').addClass('is-invalid');
            } else {
                error_remarks = '';
                $('#error_remarks').text(error_remarks);
                $('#remarks').removeClass('is-invalid');
            }

            if ($('#upload_enquiry_report')[0].files.length == 0) {
                error_file = 'Please upload required document';
                $('#error_file').text(error_file);
                $('#upload_enquiry_report').addClass('is-invalid');
            } else {
                error_file = '';
                $('#error_file').text(error_file);
                $('#upload_enquiry_report').removeClass('is-invalid');
            }

            var isFinalUpdateHappens = 0;
            var processType = $('input[name="process_type"]:checked').val();
            var matchingType = $('#mismatch_type').val();

            if (processType == 1) {
                if (matchingType == 2) {
                    if (error_file != '') {
                        return false;
                    } else {
                        isFinalUpdateHappens = 1;
                    }
                } else {
                    isFinalUpdateHappens = 1;
                }
            } else if (processType == 2) {
                if (error_name_of_bank != '' || error_bank_branch != '' || error_bank_account_number != '' || error_bank_ifsc_code != '' || error_remarks != '' || error_file != '') {
                    return false;
                } else {
                    isFinalUpdateHappens = 1;
                }
            } else if (processType == 3) {
                if (error_remarks != '' /* || error_file != ''*/ ) {
                    return false;
                } else {
                    isFinalUpdateHappens = 1;
                }
            }

            // Final Update Here
            if (isFinalUpdateHappens == 1) {
                var process_type = $('input[name="process_type"]:checked').val();
                var old_bank_ifsc = $('#old_bank_ifsc').val();
                var matchingType = $('#mismatch_type').val();
                var old_bank_accno = $('#old_bank_accno').val();
                var bank_ifsc = $('#bank_ifsc').val();
                var bank_account_number = $('#bank_account_number').val();
                var login_otp_no = $('#verify_otp_no').val();
                var name_matching_score = $('#name_matching_score').val();
                var msg = '';

                if (process_type == 1) {
                    msg = '<span class="text-danger"><b>Name minor mismatch is ' + name_matching_score + '%</b></span>.<br> Are you sure to allow it as minor mismatch?';
                } else {
                    msg = '<span class="text-danger"><b>Are you sure to allow it as rejection due to major mismatch?';
                }

                if (process_type == 2) {
                    if ((bank_account_number == old_bank_accno) && (bank_ifsc == old_bank_ifsc)) {
                        Swal.fire({
                            title: 'Alert!',
                            icon: 'warning',
                            text: 'Account number and ifsc same as previous one',
                            confirmButtonColor: '#f0ad4e',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                }

                // Helper function to map types to colors
                function getButtonColor(type) {
                    const colorMap = {
                        'red': '#d33',
                        'green': '#28a745',
                        'blue': '#3085d6',
                        'orange': '#f0ad4e',
                        'success': '#28a745',
                        'error': '#d33',
                        'warning': '#f0ad4e',
                        'info': '#3085d6'
                    };
                    return colorMap[type] || '#3085d6';
                }

                // Map process type to icon
                const iconMap = {
                    '1': 'warning',
                    '2': 'info',
                    '3': 'error'
                };

                Swal.fire({
                    title: 'Confirmation!',
                    icon: iconMap[process_type] || 'question',
                    html: msg,
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        var upload_enquiry_report = $('#upload_enquiry_report')[0].files;
                        var bank_name = $('#bank_name').val();
                        var branch_name = $('#branch_name').val();
                        var remarks = $('#remarks').val();
                        var benId = $('#benId').val();
                        var application_id = $('#application_id').val();
                        var faildTableId = $('#faildTableId').val();
                        var nameStatusCode = $('#nameStatusCode').val();
                        var token = '{{ csrf_token() }}';
                        var fd = new FormData();
                        fd.append('benId', benId);
                        fd.append('bank_ifsc', bank_ifsc);
                        fd.append('matchingType', matchingType);
                        fd.append('bank_name', bank_name);
                        fd.append('bank_account_number', bank_account_number);
                        fd.append('branch_name', branch_name);
                        fd.append('upload_enquiry_report', upload_enquiry_report[0]);
                        fd.append('_token', token);
                        fd.append('old_bank_ifsc', old_bank_ifsc);
                        fd.append('old_bank_accno', old_bank_accno);
                        fd.append('remarks', remarks);
                        fd.append('application_id', application_id);
                        fd.append('process_type', process_type);
                        fd.append('faildTableId', faildTableId);
                        fd.append('nameStatusCode', nameStatusCode);
                        fd.append('otp_login', login_otp_no);

                        $('#loadingDivModal').show();
                        $('.verifySubmit').attr('disabled', true);

                        $.ajax({
                            type: 'post',
                            url: "{{ route('updateNameValidationFailed90to100') }}",
                            data: fd,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(response) {
                                $('#loadingDivModal').hide();
                                $('.verifySubmit').removeAttr('disabled', true);

                                // Use Bootstrap 5 modal hide method
                                var modal = bootstrap.Modal.getInstance(document.querySelector('.ben_bank_modal'));
                                if (modal) {
                                    modal.hide();
                                }

                                dataTable.ajax.reload();

                                Swal.fire({
                                    title: response.title,
                                    icon: response.icon || 'success',
                                    text: response.msg,
                                    confirmButtonColor: getButtonColor(response.type),
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $("html, body").animate({
                                            scrollTop: 0
                                        }, "slow");
                                    }
                                });
                            },
                            complete: function() {
                                $('.verifySubmit').removeAttr('disabled', true);
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                $('.verifySubmit').removeAttr('disabled', true);
                                $('#loadingDivModal').hide();
                                ajax_error(jqXHR, textStatus, errorThrown);
                            }
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: 'Alert!',
                    icon: 'error',
                    text: 'Something went wrong!!',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            }
        });
        // -------------------- Final Approve Section --------------------------//

    });

    function View_encolser_modal(doc_name, doc_type, is_profile_pic) {
        var application_id = $('#application_id').val();
        var benId = $('#benId').val();
        $('#encolser_name').html('');
        $('#encolser_content').html('');
        $('#encolser_name').html(doc_name + '(' + benId + ')');
        $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
        $('#loadingDivModal').show();
        $('.verifySubmit').attr('disabled', true);
        $.ajax({
            url: "{{ route('failedNameAjaxViewPassbook') }}",
            type: "POST",
            data: {
                doc_type: doc_type,
                is_profile_pic: is_profile_pic,
                application_id: application_id,
                _token: '{{ csrf_token() }}',
            },
        }).done(function(data, textStatus, jqXHR) {
            $('.verifySubmit').removeAttr('disabled', true);
            $('#loadingDivModal').hide();
            $('#encolser_content').html('');
            $('#encolser_content').html(data);
            $("#encolser_modal").modal();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#encolser_content').html('');
            $('.verifySubmit').removeAttr('disabled', true);
            $('#loadingDivModal').hide();
            ajax_error(jqXHR, textStatus, errorThrown)
        });
    }

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