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
              <h4 class="card-title mb-0"><b>Reject Approved Beneficiary</b></h4>
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
              <div class="card mb-4">
                <div class="card-header">
                  <h5 class="card-title mb-0">Enter Beneficiary Details Here</h5>
                </div>
                <div class="card-body">
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
                      <button class="btn btn-success table-action-btn" id="submit_btn">
                        <i class="fas fa-search"></i> Search
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DataTable Section -->
              <div class="card" id="listing_div" style="display: none;">
                <div class="card-header card">
                  <h5 class="card-title mb-0">List of beneficiaries</h5>
                </div>
                <div class="card-body">
                  <div id="loadingDiv" class="text-center">
                    <div class="spinner-border" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="example" class="data-table" style="width:100% text-center">
                      <thead>
                        <tr class="text-center">
                          <th width="10%">Beneficiary ID</th>
                          <th width="10%">Beneficiary Name</th>
                          <th width="10%">Swasthya Sathi Card No.</th>
                          <th width="10%">Application Id</th>
                          <th width="20%">Address</th>
                          <th width="20%">Banking Information</th>
                          <th width="20%">Action</th>
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

<!-- Reject Beneficiary Modal -->
<div class="modal fade" id="benViewModal" tabindex="-1" role="dialog" aria-labelledby="benViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Approved Beneficiary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="loadingDivModal"></div>
        
        <!-- Personal Details Section -->
        <div class="card mb-3 singleInfo">
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

        <!-- Action Section -->
        <div class="card mb-3 stopPaymentSection" style="display: none;">
          <div class="card-header">
            <h6 class="card-title mb-0">Action</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="doc_type" class="form-label required-field">Documents for De-activation</label>
                <select class="form-select" id="doc_type" name="doc_type">
                  <option value="">-- Select --</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="file_stop_payment" class="form-label required-field">Document Upload</label>
                <input type="file" name="file_stop_payment" class="form-control" id="file_stop_payment">
                <small class="text-info">(Only jpeg,jpg,png,pdf file and maximum size should be less than 1024 KB)</small>
                <span class="text-danger small" id="error_file"></span>
              </div>
              <div class="col-md-4 mb-3">
                <label for="reason" class="form-label required-field">Reason for De-activation</label>
                <select class="form-select" id="reason" name="reason">
                  <option value="">-- Select --</option>
                </select>
              </div>
              <div class="col-12 mb-3" style="display: none;" id="remarks_div">
                <label for="comments" class="form-label">Remarks</label>
                <input type="text" name="comments" id="comments" class="form-control" maxlength="100" placeholder="Add some remarks (Max 100 character)">
              </div>
            </div>
          </div>
        </div>

        <form method="POST" action="#" target="_blank" name="fullForm" id="fullForm">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="hidden" id="update_type" name="update_type"/>
          <input type="hidden" id="beneficiary_id" name="beneficiary_id"/>
          <input type="hidden" id="application_id" name="application_id"/>

          <div class="text-center">
            <button type="button" class="btn btn-success btn-lg" id="verifyReject">Submit</button>
            <button style="display:none;" type="button" id="submitting" class="btn btn-success btn-lg" disabled>
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              Processing Please Wait...
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function() {
    $('#loadingDiv').hide();
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #deActivateBeneficiary').addClass("active");
    $('#submit_btn').removeAttr('disabled');

    // Search type change handler
    $('#select_type').change(function(){
      var select_type = $('#select_type').val();
      $('#beneficiary_id_div, #application_id_div, #sasthyasathi_card_div').hide();
      
      if (select_type == 'B') {
        $('#beneficiary_id_div').show();
      } else if(select_type == 'A') {
        $('#application_id_div').show();
      } else if (select_type == 'S') {
        $('#sasthyasathi_card_div').show();
      }
      
      // Clear other fields
      $('#ben_id, #app_id, #ss_card').val('');
    });

    // Search button click
    $('#submit_btn').click(function(){
      var select_type = $('#select_type').val();
      var beneficiary_id = $('#ben_id').val();
      var application_id = $('#app_id').val();
      var ss_card_no = $('#ss_card').val();
      
      let errorMessage = '';
      
      if (select_type == 'B' && beneficiary_id == '') {
        errorMessage = 'Please Enter Beneficiary Id';
      } else if(select_type == 'A' && application_id == '') {
        errorMessage = 'Please Enter Application Id';
      } else if (select_type == 'S' && ss_card_no == '') {
        errorMessage = 'Please Enter Sasthyasathi Card Number';
      } else if (!select_type) {
        errorMessage = 'Please select search type';
      }
      
      if (errorMessage) {
        showSweetAlert('Alert!', errorMessage, 'warning');
        return;
      }
      
      var ajaxData = {
        'beneficiary_id': beneficiary_id,
        'application_id': application_id,
        'ss_card_no': ss_card_no,
        _token: "{{csrf_token()}}"
      };
      
      loadDataTable(ajaxData);
    });

    // File upload validation
    $('#file_stop_payment').change(function(){
      var card_file = document.getElementById("file_stop_payment");
      if(card_file.value != "") {
        var attachment = card_file.files[0];
        var type = attachment.type;
        
        if(attachment.size > 1048576) {
          $('#error_file').html("<i class='fas fa-exclamation-triangle'></i> Unaccepted document file size. Max size 1024 KB. Please try again");
          $('#file_stop_payment').val('');
          return false;
        } else if (type != 'image/jpeg' && type != 'image/png' && type != 'application/pdf') {
          $('#error_file').html("<i class='fas fa-exclamation-triangle'></i> Unaccepted document file format. Only jpeg,jpg,png and pdf. Please try again");
          $('#file_stop_payment').val('');
          return false;
        } else {
          $('#error_file').html("");
        }
      }
    });

    // Reason change handler
    $('#reason').change(function(){
      var reason = $('#reason').val();
      if (reason == 3) {
        $('#comments').val('');
        $('#remarks_div').show();
      } else {
        $('#remarks_div').hide();
      }
    });

    // Verify Reject button click
    $(document).on('click', '#verifyReject', function() {
      var update_type = $('#update_type').val();
      var comments = $('#comments').val();
      var doc_type = $('#doc_type').val();
      var reason = $('#reason').val();
      var beneficiary_id = $('#fullForm #beneficiary_id').val();
      var application_id = $('#fullForm #application_id').val();
      var full_name = $('#ben_fullname').text();
      var file_sp = document.getElementById("file_stop_payment");
      var file_attachment = file_sp.files[0];
      var valid = 0;
      var formData = '';

      if(update_type == 'SP') {
        if(file_sp.value != '' && doc_type != '' && reason != '') {
          valid = 1;
          formData = new FormData();
          var files = $('#file_stop_payment')[0].files;
          formData.append('file_stop_payment', files[0]);
          formData.append('update_type', update_type);
          formData.append('comments', comments);
          formData.append('doc_type', doc_type);
          formData.append('reason', reason);
          formData.append('application_id', application_id);
          formData.append('beneficiary_id', beneficiary_id);
          formData.append('_token', '{{ csrf_token() }}');
        } else {
          showSweetAlert('Error!', 'All (*) fields are required', 'error');
          return false;
        }
      }

      if(valid == 1 && formData != '') {
        showConfirm(
          'Confirmation',
          `Are you sure want to de-activate this beneficiary?<br><strong>Name - ${full_name}<br>Beneficiary ID - ${beneficiary_id}</strong>`,
          'warning',
          'Yes, De-activate',
          'Cancel'
        ).then((result) => {
          if (result.isConfirmed) {
            $("#submitting").show();
            $("#verifyReject").hide();
            $('#loadingDivModal').show();
            
            $.ajax({
              type: 'POST',
              url: "{{ url('updateStopPaymentFinal') }}",
              data: formData,
              dataType: 'json',
              processData: false,
              contentType: false,
              success: function (data) {
                if(data.return_status) {
                  $('#benViewModal').modal('hide');
                  showSweetAlert('Success!', data.return_msg, 'success').then(() => {
                    $("#submitting").hide();
                    $("#verifyReject").show();
                    $('#loadingDivModal').hide();
                    $('#listing_div').hide();
                    $('#select_type').val('').trigger('change');
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                  });
                } else {
                  $("#submitting").hide();
                  $("#verifyReject").show();
                  $('#loadingDivModal').hide();
                  $('#benViewModal').modal('hide');
                  showSweetAlert('Error!', data.return_msg, 'error');
                }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                showSweetAlert('Error!', 'Something went wrong in the de-activation process!', 'error').then(() => {
                  location.reload();
                });
              }
            });
          }
        });
      } else {
        showSweetAlert('Error!', 'Something went wrong!', 'error');
      }
    });
  });

  function loadDataTable(ajaxData) {
    $('#loadingDiv').show();
    $('#listing_div').show();
    $('#submit_btn').attr('disabled', true);

    if ($.fn.DataTable.isDataTable('#example')) {
      $('#example').DataTable().destroy();
    }
    
    var dataTable = $('#example').DataTable({
      dom: "Bfrtip",
      scrollX: true,
      paging: false,
      searching: false,
      ordering: false,
      info: false,
      // pageLength: 20,
      // lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
      serverSide: true,
      processing: true,
      bRetrieve: true,
      language: {
        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Processing...'
      },
      ajax: {
        url: "{{ url('get-linelisting-deactive') }}",
        type: "post",
        data: ajaxData,
        error: function (jqXHR, textStatus, errorThrown) {
          $('#loadingDiv').hide();
          $('#submit_btn').removeAttr('disabled');
          showSweetAlert('Error!', 'Failed to load data. Please try again.', 'error');
        }
      },
      initComplete: function() {
        $('#loadingDiv').hide();
        $('#submit_btn').removeAttr('disabled');
      },
      columns: [
        { "data": "beneficiary_id" },
        { "data": "name" },
        { "data": "ss_card_no" },
        { "data": "application_id" },
        { "data": "address" },
        { "data": "bank_info" },
        { 
          "data": "action",
          "className": "text-center",
          "orderable": false,
          "searchable": false
        }
      ],
      buttons: [
        {
          extend: 'pdf',
          className: 'btn btn-secondary table-action-btn',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5]
          }
        },
        {
          extend: 'excel',
          className: 'btn btn-success table-action-btn',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5]
          }
        }
      ]
    });
  }

  function editFunction(beneficiary_id) {
    var select_item = $('#select_item_update_' + beneficiary_id).val();
    
    if (!select_item) {
      showSweetAlert('Alert!', 'Please select option which one do you want to edit', 'warning');
      return;
    }
    
    if (select_item == 'SP') {
      $('#loadingDiv').show();
      $('#loadingDivModal').show();
      $('#fullForm #beneficiary_id').val(beneficiary_id);
      $('#fullForm #update_type').val(select_item);
      $(".singleInfo").show();
      $(".stopPaymentSection").show();
      $('.applicant_id_modal').html('');
      $('#collapsePersonal').collapse('show');
      
      $.ajax({
        type: 'post',
        url: "{{route('getBeneficiaryPersonalData')}}",
        data: {_token: '{{csrf_token()}}', benid: beneficiary_id},
        dataType: 'json',
        success: function (response) {
          $('#loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('#collapsePersonal').collapse('show');
          $('#doc_type').val('');
          $('#reason').val('');
          $('#file_stop_payment').val('');
          $('#error_file').html('');
          
          $('#sws_card_txt').text(response.personaldata[0].ss_card_no);
          var mname = response.personaldata[0].ben_mname || '';
          var lname = response.personaldata[0].ben_lname || '';
          $('#ben_fullname').text(response.personaldata[0].ben_fname + ' ' + mname + ' ' + lname);
          $('#mobile_no').text(response.personaldata[0].mobile_no);
          $('#gender').text(response.personaldata[0].gender);
          $('#dob').text(response.personaldata[0].dob);
          $('#ben_age').text(response.personaldata[0].age_ason_01012021);
          $('#caste').text(response.personaldata[0].caste);
          
          if(response.personaldata[0].caste == 'SC' || response.personaldata[0].caste == 'ST') {
            $('#caste_certificate_no').text(response.personaldata[0].caste_certificate_no);
            $('.caste').show();
          } else {
            $('.caste').hide();
          }

          $('.applicant_id_modal').html('(Beneficiary ID - ' + response.personaldata[0].beneficiary_id + ' , Application ID - ' + response.personaldata[0].application_id + ')');
          $('#fullForm #application_id').val(response.personaldata[0].application_id);
          
          // Populate document types
          $('#doc_type').html('<option value="">-- Select --</option>');
          for (var i = 0; i < response.attach_doc.length; i++) {
            $('#doc_type').append($('<option>', {
              value: response.attach_doc[i].id,
              text: response.attach_doc[i].doc_name
            }));
          }
          
          // Populate reasons
          $('#reason').html('<option value="">-- Select --</option>');
          @foreach(Config::get('globalconstants.de_activation_reason') as $key=>$val)
          $('#reason').append('<option value="{{ $key }}">{{ $val }}</option>');
          @endforeach
          
          $('#benViewModal').modal('show');
        },
        error: function (jqXHR, textStatus, errorThrown) {
          $('#loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('#benViewModal').modal('hide');
          showSweetAlert('Error!', 'Something wrong while fetching the beneficiary data!', 'error');
        }
      });
    } else {
      showSweetAlert('Alert!', 'Something went wrong!', 'error');
    }
  }

  // SweetAlert Helper Functions
  function showSweetAlert(title, text, icon = 'success', confirmButtonText = 'OK') {
    return Swal.fire({
      title: title,
      text: text,
      icon: icon,
      confirmButtonText: confirmButtonText,
      confirmButtonColor: '#3085d6'
    });
  }

  function showConfirm(title, html, icon = 'question', confirmButtonText = 'Yes', cancelButtonText = 'Cancel') {
    return Swal.fire({
      title: title,
      html: html,
      icon: icon,
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: confirmButtonText,
      cancelButtonText: cancelButtonText
    });
  }

  function showToast(title, icon = 'success') {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
    });

    Toast.fire({
      icon: icon,
      title: title
    });
  }

  function closeError(divId) {
    $('#' + divId).hide();
  }

  function printMsg(msg, msgtype, divid) {
    $("#" + divid).find("ul").html('');
    $("#" + divid).css('display', 'block');
    
    if (msgtype == '0') {
      $("#" + divid).removeClass('alert-success').addClass('alert-warning');
    } else {
      $("#" + divid).removeClass('alert-warning').addClass('alert-success');
    }

    if (Array.isArray(msg)) {
      $.each(msg, function(key, value) {
        $("#" + divid).find("ul").append('<li>' + value + '</li>');
      });
    } else {
      $("#" + divid).find("ul").append('<li>' + msg + '</li>');
    }
  }
</script>
@endpush