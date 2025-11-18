<style>
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

  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .required:after {
    color: #d9534f;
    content: '*';
    font-weight: bold;
    margin-left: 5px;
    float: right;
    margin-top: 5px;
  }

  .loadingDivModal {
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
  }

  .disabledcontent {
    pointer-events: none;
    opacity: 0.4;
  }

  .table-action-btn {
    margin: 2px;
  }
</style>

@extends('layouts.app-template-datatable')
@section('content')
<!-- Main content -->
<div class="container-fluid">
  <div class="row">
    <div class="col-12 mt-4">
      <div class="tab-content" style="margin-top:16px;">
        <div class="tab-pane active" id="personal_details">
          <!-- Card with AdminLTE3 design -->
          <div class="card" id="res_div">
            <div class="card-header card-header-custom">
              <h4 class="card-title mb-0"><b>Update Bank Details For Approved Beneficiary</b></h4>
            </div>
            <div class="card-body" style="padding: 20px;">
              <!-- Alert Messages -->
              <div class="alert-section">
                @if ( ($message = Session::get('success')))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>{{ $message }}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(count($errors) > 0)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li><strong>{{ $error }}</strong></li>
                    @endforeach
                  </ul>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="alert alert-danger print-error-msg" style="display:none;" id="errorDivMain">
                  <button type="button" class="btn-close" aria-label="Close" onclick="closeError('errorDivMain')"></button>
                  <ul class="mb-0"></ul>
                </div>
              </div>

              <!-- Search Section -->
              <div class="row mb-4">
                <div class="col-md-12">
                  <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                      <label for="select_type" class="form-label required-field">Search Using</label>
                      <select class="form-select" name="select_type" id="select_type">
                        <option value="">--- Select ---</option>
                        @foreach(Config::get('globalconstants.search_payment_status') as $key=> $search_type)
                        <option value="{{$key}}">{{$search_type}}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-3 mb-3" id="beneficiary_id_div" style="display: none;">
                      <label for="ben_id" class="form-label required-field">Beneficiary ID</label>
                      <input type="text" name="ben_id" id="ben_id" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Beneficiary ID">
                    </div>

                    <div class="col-md-3 mb-3" id="application_id_div" style="display: none;">
                      <label for="app_id" class="form-label required-field">Application ID</label>
                      <input type="text" name="app_id" id="app_id" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Application ID">
                    </div>

                    <div class="col-md-3 mb-3" id="sasthyasathi_card_div" style="display: none;">
                      <label for="ss_card" class="form-label required-field">Sasthasathi Card</label>
                      <input type="text" name="ss_card" id="ss_card" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Sasthyasathi Card Number">
                    </div>

                    <div class="col-md-2 mb-3">
                      <button class="btn btn-success table-action-btn" id="submit_btn" disabled>
                        <i class="fas fa-search"></i> Search
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DataTable Section -->
              <div class="table-container" id="listing_div" style="display: none;">
                <div class="card">
                  <div class="card-header card-header-custom">
                    <h5 class="card-title">List of beneficiaries</h5>
                  </div>
                  <div class="card-body">
                    <div id="loadingDiv" class="text-center">
                      <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </div>
                    <div class="table-responsive">
                      <table id="example" class="data-table" style="width:100%">
                        <thead>
                          <tr>
                            <th style="width:15%">Application Id</th>
                            <th style="width:15%">Beneficiary ID</th>
                            <th style="width:20%">Beneficiary Name</th>
                            <th style="width:15%">Swasthya Sathi Card No.</th>
                            <th style="width:20%">Address</th>
                            <th style="width:15%">Banking Information</th>
                            <th style="width:20%">Action</th>
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
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Update Bank Details Modal -->
<div class="modal fade" id="benBankModal" tabindex="-1" role="dialog" aria-labelledby="benBankModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Bank Details For Approved Beneficiary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="loadingDivModal"></div>
        <input type="hidden" id="benId" name="benId" value="">
        <input type="hidden" id="application_id" name="application_id" value="">
        <input type="hidden" id="old_bank_ifsc" name="old_bank_ifsc" value="">
        <input type="hidden" id="old_bank_accno" name="old_bank_accno" value="">

        <!-- Personal Details Accordion -->
        <div class="card mb-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <a href="#collapsePersonal" data-bs-toggle="collapse">Personal Details <span class="applicant_id_modal"></span></a>
            </h6>
          </div>
          <div id="collapsePersonal" class="collapse show">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <tbody>
                    <tr>
                      <th width="20%">Swasthya Sathi Card No.</th>
                      <td id='sws_card_txt' width="30%"></td>
                      <th width="20%">Mobile No.</th>
                      <td id="mobile_no" width="30%"></td>
                    </tr>
                    <tr>
                      <th>Name</th>
                      <td id='ben_fullname'></td>
                      <th>Gender</th>
                      <td id="gender"></td>
                    </tr>
                    <tr>
                      <th>DOB</th>
                      <td id="dob"></td>
                      <th>Age</th>
                      <td id="ben_age"></td>
                    </tr>
                    <tr>
                      <th>Caste:</th>
                      <td id="caste"></td>
                      <th class="caste">SC/ST Certificate No.</th>
                      <td id="caste_certificate_no" class="caste"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Bank Details Accordion -->
        <div class="card">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <a href="#collapseBank" data-bs-toggle="collapse">Update Bank Details</a>
            </h6>
          </div>
          <div id="collapseBank" class="collapse show">
            <div class="card-body">
              <p class="text-center text-danger mb-3"><small>All (<span class="text-danger">*</span>) marks are mandatory</small></p>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Bank Branch Name</label>
                  <input type="text" name="branch_name" id="branch_name" class="form-control" readonly>
                  <span id="error_bank_branch" class="text-danger small"></span>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Bank IFSC Code</label>
                  <div class="input-group">
                    <input type="text" name="bank_ifsc" id="bank_ifsc" class="form-control" onkeyup="this.value = this.value.toUpperCase();">
                    <div class="spinner-border spinner-border-sm" id="ifsc_loader" style="display: none;" role="status"></div>
                  </div>
                  <span id="error_bank_ifsc_code" class="text-danger small"></span>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Bank Name</label>
                  <input type="text" name="bank_name" id="bank_name" class="form-control" readonly>
                  <span id="error_name_of_bank" class="text-danger small"></span>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Bank Account Number</label>
                  <input type="password" name="bank_account_number" id="bank_account_number" class="form-control" maxlength="20">
                  <span id="error_bank_account_number" class="text-danger small"></span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Confirm Bank Account Number</label>
                  <input type="text" name="confirm_bank_account_number" id="confirm_bank_account_number" class="form-control" maxlength="20">
                  <span id="error_confirm_bank_account_number" class="text-danger small"></span>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Upload Bank Passbook</label>
                  <input type="file" name="upload_bank_passbook" id="upload_bank_passbook" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                  <span id="error_file" class="text-danger small"></span>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Copy Of Passbook</label>
                  <div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="View_encolserModal('Copy of Bank Pass book','10',1)">View</button>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Remarks</label>
                  <input type="text" name="remarks" id="remarks" class="form-control" maxlength="100">
                  <span id="error_remarks" class="text-danger small"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-3">
          <button type="button" class="btn btn-success btn-lg btnUpdate">Update</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Update Mobile Number Modal -->
<div class="modal fade" id="benMobileModal" tabindex="-1" role="dialog" aria-labelledby="benMobileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Mobile Number For Approved Beneficiary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="loadingDivModal"></div>
        <input type="hidden" id="mobileBenId" name="mobileBenId" value="">
        <input type="hidden" id="mobileAppId" name="mobileAppId" value="">
        <input type="hidden" id="oldMobileNumber" name="oldMobileNumber" value="">

        <!-- Personal Details Accordion -->
        <div class="card mb-3">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <a href="#collapsePersonalMobile" data-bs-toggle="collapse">Personal Details <span class="applicant_id_modal"></span></a>
            </h6>
          </div>
          <div id="collapsePersonalMobile" class="collapse show">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <tbody>
                    <tr>
                      <th width="20%">Swasthya Sathi Card No.</th>
                      <td id='sws_card_txt_MU' width="30%"></td>
                      <th width="20%">Mobile No.</th>
                      <td id="mobile_no_MU" width="30%"></td>
                    </tr>
                    <tr>
                      <th>Name</th>
                      <td id='ben_fullname_MU'></td>
                      <th>Gender</th>
                      <td id="gender_MU"></td>
                    </tr>
                    <tr>
                      <th>DOB</th>
                      <td id="dob_MU"></td>
                      <th>Age</th>
                      <td id="ben_age_MU"></td>
                    </tr>
                    <tr>
                      <th>Caste:</th>
                      <td id="caste_MU"></td>
                      <th class="caste">SC/ST Certificate No.</th>
                      <td id="caste_certificate_no_MU" class="caste"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Number Update Section -->
        <div class="card">
          <div class="card-header">
            <h6 class="card-title mb-0">
              <a href="#collapseMobile" data-bs-toggle="collapse">Update Mobile Number</a>
            </h6>
          </div>
          <div id="collapseMobile" class="collapse show">
            <div class="card-body">
              <p class="text-center text-danger mb-3"><small>All (<span class="text-danger">*</span>) marks are mandatory</small></p>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Mobile Number</label>
                  <input type="text" name="updateMobileNo" id="updateMobileNo" class="form-control" maxlength="10" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;">
                  <span id="error_update_mobile_no" class="text-danger small"></span>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Remarks</label>
                  <input type="text" name="updateMobileRemarks" id="updateMobileRemarks" class="form-control" maxlength="100">
                  <span id="error_mobile_no_remarks" class="text-danger small"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-3">
          <button type="button" class="btn btn-success btn-lg" id="btnUpdateMobileNo">Update</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Encloser Modal -->
<div class="modal fade" id="encolserModal" tabindex="-1" role="dialog" aria-labelledby="encolserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="encolser_name">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="encolser_content"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('#loadingDiv').hide();
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #updateBankDetails').addClass("active");
    // $('.sidebar-menu #accValTrFailed').addClass("active");
    $('#submit_btn').removeAttr('disabled');
    $('#select_type').change(function() {
      var select_type = $('#select_type').val();
      if (select_type == 'B') {
        $('#beneficiary_id_div').show();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').hide();
        $('#app_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      } else if (select_type == 'A') {
        $('#beneficiary_id_div').hide();
        $('#application_id_div').show();
        $('#sasthyasathi_card_div').hide();
        $('#ben_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      } else if (select_type == 'S') {
        $('#beneficiary_id_div').hide();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').show();
        $('#ben_id').val('').trigger('change');
        $('#app_id').val('').trigger('change');
      } else {
        $('#beneficiary_id_div').hide();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').hide();
        $('#ben_id').val('').trigger('change');
        $('#app_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      }
    });

    var ajaxData = '';
    $('#submit_btn').click(function() {
      var select_type = $('#select_type').val();
      var beneficiary_id = $('#ben_id').val();
      var application_id = $('#app_id').val();
      var ss_card_no = $('#ss_card').val();
      if (select_type == 'B') {
        if (beneficiary_id == '') {
          Swal.fire({
            title: 'Alert!!',
            text: 'Please Enter Beneficiary Id',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        } else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token: "{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      } else if (select_type == 'A') {
        if (application_id == '') {
          Swal.fire({
            title: 'Alert!!',
            text: 'Please Enter Application Id',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        } else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token: "{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      } else if (select_type == 'S') {
        if (ss_card_no == '') {
          Swal.fire({
            title: 'Alert!!',
            text: 'Please Enter Sasthyasathi Card Number',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        } else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token: "{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      } else {
        Swal.fire({
          title: 'Alert!!',
          text: 'All fields are required',
          icon: 'warning',
          confirmButtonText: 'OK'
        });
      }
    });

    $('.modalEncloseClose').click(function() {
      $('.encolserModal').modal('hide');
    });

    $('#bank_ifsc').blur(function() {
      $ifsc_data = $.trim($('#bank_ifsc').val());
      $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
      if ($ifscRGEX.test($ifsc_data)) {
        $('#bank_ifsc').removeClass('has-error');
        $('#error_bank_ifsc_code').text('');
        $('#ifsc_loader').show();
        $('.btnUpdate').attr('disabled', true);
        $.ajax({
          type: 'POST',
          url: "{{ route('bankIfsc') }}",
          data: {
            ifsc: $ifsc_data,
            _token: '{{ csrf_token() }}',
          },
          success: function(data) {
            $('#ifsc_loader').hide();
            $('.btnUpdate').removeAttr('disabled', true);
            if (data.status == 2) {
              Swal.fire({
                title: 'IFSC Not Found!',
                text: 'This ' + $ifsc_data + ' IFSC is not registered in our system.',
                icon: 'info',
                confirmButtonText: 'OK'
              }).then((result) => {
                $('#bank_ifsc').val('');
              });
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

    $('#upload_bank_passbook').change(function() {
      var card_file = document.getElementById("upload_bank_passbook");
      if (card_file.value != "") {
        var attachment;
        attachment = card_file.files[0];
        // console.log(attachment.type)
        var type = attachment.type;
        if (attachment.size > 512000) {
          document.getElementById("error_file").innerHTML = "<i class='fa fa-warning'></i> Unaccepted document file size. Max size 500 KB. Please try again";
          $('#upload_bank_passbook').val('');
          return false;
        } else if (type != 'image/jpeg' && type != 'image/png' && type != 'application/pdf') {
          document.getElementById("error_file").innerHTML = "<i class='fa fa-warning'></i> Unaccepted document file format. Only jpeg,jpg,png and pdf. Please try again";
          $('#upload_bank_passbook').val('');
          return false;
        } else {
          $('#file_upload_btn').show();
          document.getElementById("error_file").innerHTML = "";
        }
      }
    });
    $("#bank_account_number,#confirm_bank_account_number").on("copy cut paste drop", function() {
      return false;
    });

    // -------------------- Final Approve Section-------------------------- //
    $(document).on('click', '.btnUpdate', function() {
      var error_name_of_bank = '';
      var error_bank_branch = '';
      var error_bank_account_number = '';
      var error_bank_ifsc_code = '';
      var error_remarks = '';
      var error_file = '';

      if ($.trim($('#bank_name').val()).length == 0) {
        error_name_of_bank = 'Name of Bank is required';
        $('#error_name_of_bank').text(error_name_of_bank);
        $('#bank_name').addClass('has-error');
      } else {
        error_name_of_bank = '';
        $('#error_name_of_bank').text(error_name_of_bank);
        $('#bank_name').removeClass('has-error');
      }

      if ($.trim($('#branch_name').val()).length == 0) {
        error_bank_branch = 'Bank Branch is required';
        $('#error_bank_branch').text(error_bank_branch);
        $('#branch_name').addClass('has-error');
      } else {
        error_bank_branch = '';
        $('#error_bank_branch').text(error_bank_branch);
        $('#branch_name').removeClass('has-error');
      }

      if ($.trim($('#bank_account_number').val()).length == 0) {
        error_bank_account_number = 'Bank Account Number is required';
        $('#error_bank_account_number').text(error_bank_account_number);
        $('#bank_account_number').addClass('has-error');
      } else {
        error_bank_account_number = '';
        $('#error_bank_account_number').text(error_bank_account_number);
        $('#bank_account_number').removeClass('has-error');
      }
      if ($.trim($('#confirm_bank_account_number').val()).length == 0) {
        error_confirm_bank_account_number = 'Bank Account Number is required';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').addClass('has-error');
      } else {
        error_confirm_bank_account_number = '';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').removeClass('has-error');
      }
      if ($.trim($('#bank_ifsc').val()).length == 0) {
        error_bank_ifsc_code = 'IFS Code is required';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc').addClass('has-error');
      } else {
        error_bank_ifsc_code = '';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc').removeClass('has-error');
      }

      $ifsc_data = $.trim($('#bank_ifsc').val());
      $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
      if ($ifscRGEX.test($ifsc_data)) {
        error_bank_ifsc_code = '';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc').removeClass('has-error');
      } else {
        error_bank_ifsc_code = 'Please check IFS Code format';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc').addClass('has-error');
      }

      if ($.trim($('#remarks').val()).length == 0) {
        error_remarks = 'Please add some remarks';
        $('#error_remarks').text(error_remarks);
        $('#remarks').addClass('has-error');
      } else {
        error_remarks = '';
        $('#error_remarks').text(error_remarks);
        $('#remarks').removeClass('has-error');
      }

      if ($('#upload_bank_passbook')[0].files.length == 0) {
        error_file = 'Please add bank passbook copy';
        $('#error_file').text(error_file);
        $('#upload_bank_passbook').addClass('has-error');
      } else {
        error_file = '';
        $('#error_file').text(error_file);
        $('#upload_bank_passbook').removeClass('has-error');
      }
      // Check Bank Account Number with Confirm Bank Account Number
      if ($.trim($('#bank_account_number').val()) != $.trim($('#confirm_bank_account_number').val())) {
        error_confirm_bank_account_number = 'Confirm Bank Account Number not Match with Bank Account Number';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').addClass('has-error');
      } else {
        error_confirm_bank_account_number = '';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').removeClass('has-error');
      }
      if (error_name_of_bank != '' || error_bank_branch != '' || error_bank_account_number != '' || error_bank_ifsc_code != '' || error_remarks != '' || error_confirm_bank_account_number != '') {
        return false;
      } else {
        var old_bank_ifsc = $('#old_bank_ifsc').val();
        var old_bank_accno = $('#old_bank_accno').val();

        var bank_ifsc = $('#bank_ifsc').val();
        var bank_account_number = $('#bank_account_number').val();
        var upload_bank_passbook = $('#upload_bank_passbook')[0].files;
        //&& upload_bank_passbook.length==0
        // if((bank_account_number == old_bank_accno) && (bank_ifsc == old_bank_ifsc)) {
        //   Swal.fire({
        //     title: 'Alert!',
        //     text: 'Account number and ifsc same as previous one',
        //     icon: 'warning',
        //     confirmButtonText: 'OK'
        //   });
        //   return false;
        // }

        var bank_name = $('#bank_name').val();
        var branch_name = $('#branch_name').val();
        var remarks = $('#remarks').val();
        var benId = $('#benId').val();
        var token = '{{csrf_token()}}';
        var fd = new FormData();
        fd.append('benId', benId);
        fd.append('bank_ifsc', bank_ifsc);
        fd.append('bank_name', bank_name);
        fd.append('bank_account_number', bank_account_number);
        fd.append('branch_name', branch_name);
        fd.append('upload_bank_passbook', upload_bank_passbook[0]);
        fd.append('_token', token);
        fd.append('old_bank_ifsc', old_bank_ifsc);
        fd.append('old_bank_accno', old_bank_accno);
        fd.append('remarks', remarks);
        $('.loadingDivModal').show();
        $('.btnUpdate').attr('disabled', true);
        $.ajax({
          type: 'post',
          url: "{{route('updateApprovedBenBankDetails')}}",
          data: fd,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(response) {
            // alert(response);
            $('.loadingDivModal').hide();
            Swal.fire({
              title: response.title,
              text: response.msg,
              icon: response.type,
              confirmButtonText: 'OK'
            }).then((result) => {
              if (result.isConfirmed) {
                $('.btnUpdate').removeAttr('disabled', true);
                $('#benBankModal').modal('hide');
                $('#listing_div').hide();
                $('#select_type').val('').trigger('change');
                $("html, body").animate({
                  scrollTop: 0
                }, "slow");
              }
            });
          },
          complete: function() {
            //  $('.btnUpdate').removeAttr('disabled',true);
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('.btnUpdate').removeAttr('disabled', true);
            $('.loadingDivModal').hide();
            ajax_error(jqXHR, textStatus, errorThrown)
          }
        });
      }
    });
    // -------------------- Final Approve Section --------------------------//

    // Mobile Number Update Section
    $(document).on('click', '#btnUpdateMobileNo', function() {
      var error_update_mobile_no = '';
      var error_mobile_no_remarks = '';

      if ($.trim($('#updateMobileNo').val()).length == 0) {
        error_update_mobile_no = 'Mobile number is required';
        $('#error_update_mobile_no').text(error_update_mobile_no);
        $('#updateMobileNo').addClass('has-error');
      } else {
        error_update_mobile_no = '';
        $('#error_update_mobile_no').text(error_update_mobile_no);
        $('#updateMobileNo').removeClass('has-error');
      }

      if ($.trim($('#updateMobileRemarks').val()).length == 0) {
        error_mobile_no_remarks = 'Please add some remarks';
        $('#error_mobile_no_remarks').text(error_mobile_no_remarks);
        $('#updateMobileRemarks').addClass('has-error');
      } else {
        error_mobile_no_remarks = '';
        $('#error_mobile_no_remarks').text(error_mobile_no_remarks);
        $('#updateMobileRemarks').removeClass('has-error');
      }

      if (error_update_mobile_no != '' || error_mobile_no_remarks != '') {
        return false;
      } else {
        var old_mobile_no = $('#oldMobileNumber').val();
        var new_mobile_no = $('#updateMobileNo').val();
        var mobile_no_remarks = $('#updateMobileRemarks').val();
        if (old_mobile_no == new_mobile_no) {
          Swal.fire({
            title: 'Alert!',
            text: 'Your entered mobile number same as previous one.',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
          return false;
        } else {
          // alert('OK');
          var mobileAppId = $('#mobileAppId').val();
          var mobileBenId = $('#mobileBenId').val();
          $('.loadingDivModal').show();
          $('#btnUpdateMobileNo').attr('disabled', true);
          $.ajax({
            type: 'post',
            url: "{{route('updateApprovedBenMobileNumber')}}",
            data: {
              _token: '{{csrf_token()}}',
              benId: mobileBenId,
              appId: mobileAppId,
              newMobileNo: new_mobile_no,
              remarks: mobile_no_remarks
            },
            dataType: 'json',
            success: function(response) {
              $('.loadingDivModal').hide();
              Swal.fire({
                title: response.title,
                text: response.msg,
                icon: response.type,
                confirmButtonText: 'OK'
              }).then((result) => {
                if (result.isConfirmed) {
                  $('#btnUpdateMobileNo').removeAttr('disabled', true);
                  $('#benMobileModal').modal('hide');
                  $('#listing_div').hide();
                  $('#select_type').val('').trigger('change');
                  $("html, body").animate({
                    scrollTop: 0
                  }, "slow");
                }
              });
            },
            complete: function() {
              //  $('.btnUpdate').removeAttr('disabled',true);
            },
            error: function(jqXHR, textStatus, errorThrown) {
              $('#btnUpdateMobileNo').removeAttr('disabled', true);
              $('.loadingDivModal').hide();
              ajax_error(jqXHR, textStatus, errorThrown)
            }
          });
        }
      }
    });
    // End Mobile Number Updae
  });

  function loadDataTable(ajaxData) {
    $('#loadingDiv').show();
    $('#listing_div').show();
    $('#submit_btn').attr('disabled', true);

    if ($.fn.DataTable.isDataTable('#example')) {
      $('#example').DataTable().destroy();
    }
    var dataTable = $('#example').DataTable({
      dom: 'Blfrtip',
      "scrollX": true,
      "paging": false,
      "searchable": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "pageLength": 20,
      'lengthMenu': [
        [10, 20, 30, 50, 100],
        [10, 20, 30, 50, 100]
      ],
      "serverSide": true,
      "processing": true,
      "bRetrieve": true,
      "oLanguage": {
        // "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
      },
      "ajax": {
        url: "{{ url('getLineListBankEdit') }}",
        type: "post",
        data: ajaxData,
        error: function(jqXHR, textStatus, errorThrown) {
          $('#loadingDiv').hide();
          $('#submit_btn').removeAttr('disabled');
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      },
      "initComplete": function() {
        $('#loadingDiv').hide();
        $('#submit_btn').removeAttr('disabled');
        //console.log('Data rendered successfully');
      },
      "columns": [
        // { "data": "DT_RowIndex" },
        {
          "data": "application_id"
        },
        {
          "data": "beneficiary_id"
        },
        {
          "data": "name"
        },
        {
          "data": "ss_card_no"
        },
        {
          "data": "address"
        },
        {
          "data": "bank_info"
        },
        {
          "data": "action"
        },
      ],

      "buttons": [
        // 'pdf','excel'
      ],
    });
  }

  function editFunction(beneficiary_id) {
    var select_item = $('#select_item_update_' + beneficiary_id).val();
    if (select_item == '') {
      Swal.fire({
        title: 'Alert!!',
        text: 'Please select option which one do you want to edit',
        icon: 'warning',
        confirmButtonText: 'OK'
      });
      return false;
    }

    if (select_item == 'bank') {
      $('#loadingDiv').show();
      $('.loadingDivModal').show();

      $.ajax({
        type: 'post',
        url: "{{route('getBenDataForBankUpdate')}}",
        data: {
          _token: '{{csrf_token()}}',
          benid: beneficiary_id
        },
        dataType: 'json',
        success: function(response) {
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();

          // Populate modal data
          $('#sws_card_txt').text(response.personaldata.ss_card_no || '');
          var mname = response.personaldata.ben_mname || '';
          var lname = response.personaldata.ben_lname || '';
          $('#ben_fullname').text(response.personaldata.ben_fname + ' ' + mname + ' ' + lname);
          $('#mobile_no').text(response.personaldata.mobile_no || '');
          $('#gender').text(response.personaldata.gender || '');
          $('#dob').text(response.dob || '');
          $('#ben_age').text(response.personaldata.age_ason_01012021 || '');
          $('#caste').text(response.personaldata.caste || '');

          if (response.personaldata.caste == 'SC' || response.personaldata.caste == 'ST') {
            $('#caste_certificate_no').text(response.personaldata.caste_certificate_no || '');
            $('.caste').show();
          } else {
            $('.caste').hide();
          }

          $('.applicant_id_modal').html('(Beneficiary ID - ' + response.personaldata.beneficiary_id + ' , Application ID - ' + response.personaldata.application_id + ')');
          $('#application_id').val(response.personaldata.application_id);
          $('#benId').val(response.personaldata.beneficiary_id);
          $('#bank_ifsc').val(response.bank_ifsc || '');
          $('#bank_name').val(response.bank_name || '');
          $('#branch_name').val(response.branch_name || '');
          $('#bank_account_number').val(response.bank_code || '');
          $('#confirm_bank_account_number').val(response.bank_code || '');
          $('#old_bank_ifsc').val(response.bank_ifsc || '');
          $('#old_bank_accno').val(response.bank_code || '');
          $('#upload_bank_passbook').val('');
          $('#remarks').val('');

          // Show modal using correct selector
          $('#benBankModal').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('#benBankModal').modal('hide');
          console.error('AJAX Error:', textStatus, errorThrown);
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      });
    } else if (select_item == 'mobile') {
      $('#loadingDiv').show();
      $('.loadingDivModal').show();

      $.ajax({
        type: 'post',
        url: "{{route('getBenDataForBankUpdate')}}",
        data: {
          _token: '{{csrf_token()}}',
          benid: beneficiary_id
        },
        dataType: 'json',
        success: function(response) {
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();

          // Populate mobile modal data
          $('#sws_card_txt_MU').text(response.personaldata.ss_card_no || '');
          var mname = response.personaldata.ben_mname || '';
          var lname = response.personaldata.ben_lname || '';
          $('#ben_fullname_MU').text(response.personaldata.ben_fname + ' ' + mname + ' ' + lname);
          $('#mobile_no_MU').text(response.personaldata.mobile_no || '');
          $('#gender_MU').text(response.personaldata.gender || '');
          $('#dob_MU').text(response.dob || '');
          $('#ben_age_MU').text(response.personaldata.age_ason_01012021 || '');
          $('#caste_MU').text(response.personaldata.caste || '');

          if (response.personaldata.caste == 'SC' || response.personaldata.caste == 'ST') {
            $('#caste_certificate_no_MU').text(response.personaldata.caste_certificate_no || '');
            $('.caste').show();
          } else {
            $('.caste').hide();
          }

          $('.applicant_id_modal').html('(Beneficiary ID - ' + response.personaldata.beneficiary_id + ' , Application ID - ' + response.personaldata.application_id + ')');
          $('#oldMobileNumber').val(response.personaldata.mobile_no || '');
          $('#updateMobileNo').val(response.personaldata.mobile_no || '');
          $('#mobileAppId').val(response.personaldata.application_id);
          $('#mobileBenId').val(response.personaldata.beneficiary_id);

          // Show mobile modal using correct selector
          $('#benMobileModal').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('#benMobileModal').modal('hide');
          console.error('AJAX Error:', textStatus, errorThrown);
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      });
    }
  }

 function View_encolserModal(doc_name, doc_type, is_profile_pic) {
    var application_id = $('#application_id').val();
    var benId = $('#benId').val();
    $('#encolser_name').html('');
    $('#encolser_content').html('');
    $('#encolser_name').html(doc_name + ' (' + benId + ')');
    $('#encolser_content').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('.loadingDivModal').show();
    $('.btnUpdate').attr('disabled', true);
    
    $.ajax({
        url: "{{ route('ajaxViewPassbook') }}",
        type: "POST",
        data: {
            doc_type: doc_type,
            is_profile_pic: is_profile_pic,
            application_id: application_id,
            _token: '{{ csrf_token() }}',
        },
    }).done(function(data, textStatus, jqXHR) {
        $('.btnUpdate').removeAttr('disabled');
        $('.loadingDivModal').hide();
        $('#encolser_content').html(data);
        
        // Use Bootstrap 5 modal method
        var encolserModal = new bootstrap.Modal(document.getElementById('encolserModal'));
        encolserModal.show();
        
    }).fail(function(jqXHR, textStatus, errorThrown) {
        $('.btnUpdate').removeAttr('disabled');
        $('.loadingDivModal').hide();
        $('#encolser_content').html('<div class="alert alert-danger">Error loading document</div>');
        ajax_error(jqXHR, textStatus, errorThrown);
    });
}
</script>
@endpush