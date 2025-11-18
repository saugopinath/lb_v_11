<style>
  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }
  
  .modal_field_name {
    font-weight: bold;
    margin-bottom: 5px;
    color: #555;
  }
  
  .modal_field_value {
    padding: 4px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
  }
</style>

@extends('layouts.app-template-datatable')
@section('content')
<!-- Main content -->
<div class="container-fluid">
  <div class="row">
    <div class="col-12 mt-4">
      <div class="tab-content" style="margin-top:16px;">
        <!-- Back Button -->
        <div class="mb-3">
          <a href="casteManagement" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
          </a>
        </div>

        <!-- Alert Messages -->
        <div class="alert-section">
          @if (!empty($beneficiary_id))
          <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Beneficiary ID: {{$beneficiary_id}}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>{{ $message }} with Application ID: {{$id}}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          @if ( ($message = Session::get('error')))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ $message }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          @if ($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach ($errors->all() as $error)
            <strong>{{ $error }}</strong><br />
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif
        </div>

        <!-- Main Content Card -->
        <div class="card">
          <div class="card-header card-header-custom">
            <h4 class="card-title mb-0"><b>Caste Information Modification</b></h4>
          </div>
          <div class="card-body">
            <form name="personal" id="personal" method="post" action="{{url('changeCastePost')}}" enctype="multipart/form-data" autocomplete="off">
              {{ csrf_field() }}
              <input type="hidden" name="beneficiary_id" id="beneficiary_id" value="{{ $beneficiary_id }}">
              <input type="hidden" name="application_id" id="application_id" value="{{  $row->application_id }}">
              <input type="hidden" name="is_faulty" id="is_faulty" value="{{ $row->is_faulty }}">
              <input type="hidden" name="old_caste" id="old_caste" value="{{ $row->caste }}">
              <input type="hidden" name="old_caste_certificate_no" id="old_caste_certificate_no" value="{{ $row->caste_certificate_no }}">
              <input type="hidden" name="caste_change_type" id="caste_change_type" value="{{ $caste_change_type }}">

              <!-- Personal Information -->
              <div class="row mb-2">
                <div class="col-md-4">
                  <div class="form">
                    <label class="form-label"><strong>Is Faulty Application?</strong></label>
                    <div class="text-info">{{$row->is_faulty?'YES':'NO' }}</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form">
                    <label class="form-label"><strong>Name:</strong></label>
                    <div class="text-info">{{$row->ben_fname}}</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form">
                    <label class="form-label"><strong>Swasthyasathi Card No:</strong></label>
                    <div class="text-info">{{$row->ss_card_no }}</div>
                  </div>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="form">
                    <label class="form-label"><strong>Mobile No.:</strong></label>
                    <div class="text-info">{{$row->mobile_no }}</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form">
                    <label class="form-label"><strong>Father's Name:</strong></label>
                    <div class="text-info">
                      {{$row->father_fname }} 
                      @if(!empty($row->father_mname)) {{$row->father_mname }} @endif 
                      @if(!empty($row->father_lname)){{$row->father_lname }} @endif
                    </div>
                  </div>
                </div>
              </div>

              <hr class="my-1">

              <!-- Address Information -->
              <div class="row mb-2">
                <div class="col-md-4">
                  <div class="modal_field_name">Police Station:</div>
                  <div class="modal_field_value" id="police_station_modal">{{trim($row_contact->police_station)}}</div>
                </div>
                <div class="col-md-4">
                  <div class="modal_field_name">Block/Municipality/Corp:</div>
                  <div class="modal_field_value" id="block_modal">{{trim($row_contact->block_ulb_name)}}</div>
                </div>
                <div class="col-md-4">
                  <div class="modal_field_name">GP/Ward No.:</div>
                  <div class="modal_field_value" id="gp_ward_modal">{{trim($row_contact->gp_ward_name)}}</div>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="modal_field_name">Village/Town/City:</div>
                  <div class="modal_field_value" id="village_modal">{{trim($row_contact->village_town_city )}}</div>
                </div>
                <div class="col-md-4">
                  <div class="modal_field_name">House / Premise No:</div>
                  <div class="modal_field_value" id="house_modal">{{ trim($row_contact->house_premise_no ?? '') ?: 'N/A' }}
</div>
                </div>
                <div class="col-md-4">
                  <div class="modal_field_name">Pin Code:</div>
                  <div class="modal_field_value" id="pin_code_modal">{{trim($row_contact->pincode)}}</div>
                </div>
              </div>

              <hr class="my-1">

              <!-- Caste Information -->
              <div class="row mb-2">
                <div class="col-md-4">
                  <div class="form">
                    <label class="form-label"><strong>Existing Caste:</strong></label>
                    <div class="text-info badge bg-warning text-dark">{{$row->caste}}</div>
                  </div>
                </div>
                @if($row->caste=='SC' || $row->caste=='ST')
                <div class="col-md-4">
                  <div class="form">
                    <label class="form-label"><strong>Existing SC/ST Certificate No:</strong></label>
                    <div class="text-info badge bg-warning text-dark">{{$row->caste_certificate_no }}</div>
                  </div>
                </div>
                @endif
              </div>

              <!-- New Caste Information -->
              <div class="row mb-2">
                <div class="col-md-4">
                  <div class="form">
                    <label for="caste_category" class="form-label required-field">New Caste</label>
                    <select class="form-select" name="caste_category" id="caste_category">
                      <option value="">--Select--</option>
                      @foreach($caste_lb as $key=>$val)
                      <option value="{{$key}}" @if($row->caste==$key) selected @endif>{{$val}}</option>
                      @endforeach
                    </select>
                    <span id="error_caste_category" class="text-danger small"></span>
                  </div>
                </div>

                <div class="col-md-4 withCaste">
                  <div class="form">
                    <label for="caste_certificate_no" class="form-label required-field">New SC/ST Certificate No.</label>
                    <input type="text" name="caste_certificate_no" id="caste_certificate_no" class="form-control"
                      placeholder="SC/ST Certificate No." maxlength="200" value="{{ $row->caste_certificate_no }}" />
                    <span id="error_caste_certificate_no" class="text-danger small"></span>
                  </div>
                </div>
              </div>

              <!-- Document Upload -->
              <div class="row mb-2">
                <div class="col-12">
                  <div class="form">
                    <label class="form-label"><strong>Enclosure List (Self Attested)</strong></label>
                  </div>
                </div>
                <div class="col-md-6 withCaste">
                  <div class="form">
                    <label for="doc_3" class="form-label required-field">{{$doc_caste_arr->doc_name}}</label>
                    <input type="file" name="doc_3" id="doc_3" class="form-control" />
                    <span id="error_doc_3" class="text-danger small"></span>
                    <div class="form-text text-muted">
                      (File type must be {{$doc_caste_arr->doc_type}} and size max {{$doc_caste_arr->doc_size_kb}}KB)
                    </div>
                    @if($casteEncloserCount > 0)
                    <div class="mt-2">
                      <a href="javascript:void(0);" id="docDownload_1" class="btn btn-outline-danger btn-sm" 
                         onclick="View_encolser_modal('{{$doc_caste_arr->doc_name}}',{{$doc_caste_arr->id}},0)">
                        <i class="fas fa-eye me-1"></i> View Document
                      </a>
                    </div>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Submit Section -->
              <div class="row mt-2">
                <div class="col-md-12">
                  @if($caste_change_type==2)
                  <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Caste change will be effective only after verification and approval.
                  </div>
                  @endif
                </div>
                <div class="col-md-12 text-center">
                  <button type="button" name="btn_aplply" id="btn_apply" class="btn btn-success btn-lg">
                    <i class="fas fa-check me-2"></i>Apply
                  </button>
                  <div id="btn_personal_details_loader" class="d-none">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="encolserModal" tabindex="-1" role="dialog" aria-labelledby="encolserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="encolser_name">Document Viewer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="encolser_content">
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading document...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var specialKeys = new Array();
  specialKeys.push(8); //Backspace
  
  function IsNumeric(e) {
    var keyCode = e.which ? e.which : e.keyCode
    var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
    return ret;
  }

  $(document).ready(function() {
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lb-caste').addClass("active");
    $('.sidebar-menu #caste_search').addClass("active");
    
    var base_url = '{{ url('/') }}';
    
    // Caste category change handler
    $("#caste_category").on('change', function() {
      var caste_category = $("#caste_category").val();
      if (caste_category == "SC" || caste_category == "ST" || caste_category == "") {
        $(".withCaste").show();
      } else {
        $(".withCaste").hide();
      }
    });

    // Input validation
    $('.txtOnly').keypress(function(e) {
      var regex = new RegExp(/^[a-zA-Z\s]+$/);
      var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (!regex.test(str)) {
        e.preventDefault();
        return false;
      }
    });

    $(".NumOnly").keyup(function(event) {
      $(this).val($(this).val().replace(/[^\d].+/, ""));
      if ((event.which < 48 || event.which > 57)) {
        event.preventDefault();
      }
    });

    $('.special-char').keyup(function() {
      var yourInput = $(this).val();
      re = /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
      var isSplChar = re.test(yourInput);
      if (isSplChar) {
        var no_spl_char = yourInput.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
        $(this).val(no_spl_char);
      }
    });

    // Apply button click handler
    $('#btn_apply').click(function() {
      if (validateForm()) {
        submitForm();
      }
    });
  });

  function validateForm() {
    var error_caste_category = '';
    var error_caste_certificate_no = "";
    var error_doc_3 = '';

    // Reset errors
    $('#error_caste_category, #error_caste_certificate_no, #error_doc_3').text('');
    $('#caste_category, #caste_certificate_no, #doc_3').removeClass('is-invalid');

    // Caste validation
    if ($.trim($('#caste_category').val()).length == 0) {
      error_caste_category = 'Caste is required';
      $('#error_caste_category').text(error_caste_category);
      $('#caste_category').addClass('is-invalid');
      return false;
    }

    // SC/ST certificate validation
    if ($('#caste_category').val() == 'SC' || $('#caste_category').val() == 'ST') {
      if ($.trim($('#caste_certificate_no').val()).length == 0) {
        error_caste_certificate_no = 'SC/ST Certificate No. is required';
        $('#error_caste_certificate_no').text(error_caste_certificate_no);
        $('#caste_certificate_no').addClass('is-invalid');
        return false;
      }
      
      if ($.trim($('#doc_3').val()).length == 0) {
        error_doc_3 = 'Document upload is required';
        $('#error_doc_3').text(error_doc_3);
        $('#doc_3').addClass('is-invalid');
        return false;
      }
    }

    return true;
  }

  function submitForm() {
    var caste_category = $("#caste_category").val();
    var caste_certificate_no = $("#caste_certificate_no").val();
    var old_caste = $("#old_caste").val();
    var old_caste_certificate_no = $("#old_caste_certificate_no").val();

    // Check if caste information is same as previous
    if ((caste_category == old_caste) && (caste_certificate_no == old_caste_certificate_no)) {
      Swal.fire({
        title: 'No Changes Detected',
        text: 'Caste Information remains same as previous. Please change at least one field.',
        icon: 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6'
      });
      return false;
    }

    // Show confirmation dialog
    Swal.fire({
      title: 'Confirm Submission',
      text: 'Are you sure you want to submit the caste modification?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Submit!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        // Disable button and show loader
        $("#btn_apply").prop("disabled", true);
        $("#btn_personal_details_loader").removeClass('d-none');
        
        // Submit the form
        $("#personal").submit();
      }
    });
  }

  function View_encolser_modal(doc_name, doc_type, is_profile_pic) {
    var application_id = $('#personal #application_id').val();
    var is_faulty = $('#personal #is_faulty').val();
    
    $('#encolser_name').html(doc_name + ' (' + application_id + ')');
    $('#encolser_content').html(`
      <div class="text-center">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading document...</p>
      </div>
    `);
    
    var url = (is_faulty == 1) ? '{{ url('ajaxGetEncloserFaulty') }}' : '{{ url('ajaxGetEncloser') }}';
    
    $.ajax({
      url: url,
      type: "POST",
      data: {
        doc_type: doc_type,
        is_profile_pic: is_profile_pic,
        application_id: application_id,
        _token: '{{ csrf_token() }}',
      },
    }).done(function(data, textStatus, jqXHR) {
      $('#encolser_content').html(data);
      var encolserModal = new bootstrap.Modal(document.getElementById('encolserModal'));
      encolserModal.show();
    }).fail(function(jqXHR, textStatus, errorThrown) {
      $('#encolser_content').html(`
        <div class="alert alert-danger text-center">
          <i class="fas fa-exclamation-triangle"></i> Failed to load document
        </div>
      `);
      Swal.fire({
        title: 'Error!',
        text: 'Failed to load document. Please try again.',
        icon: 'error',
        confirmButtonText: 'OK'
      });
    });
  }

  function closeError(divId) {
    $('#' + divId).hide();
  }

  // SweetAlert helper functions
  function showSweetAlert(title, text, icon = 'success', confirmButtonText = 'OK') {
    return Swal.fire({
      title: title,
      text: text,
      icon: icon,
      confirmButtonText: confirmButtonText,
      confirmButtonColor: '#3085d6'
    });
  }
</script>
@endpush