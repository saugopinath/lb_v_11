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

  /* New Improved Styles */
  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .table-responsive-custom {
    overflow-x: auto;
  }

  .action-buttons {
    margin-top: 20px;
  }

  .filter-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
  }
  .accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0c63e4;
    font-weight: bold;
  }

  .table th {
    background-color: #f8f9fa;
    font-weight: 600;
  }
</style>

@extends('../layouts.app-template-datatable')
@section('content')

<div class="container-fluid">
  <!-- Content Header (Page header) -->
  <section class="content-header ">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-8">
          <h1>Process Application for Caste Info Modification</h1>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <!-- Main Card -->
          <div class="card">
            <div class="card-header card-header-custom">
              <h4 class="card-title mb-0">Applications Yet To Be Approved</h4>
            </div>
            <div class="card-body">
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
              </div>

              <!-- Filter Section -->
              <div class="filter-section">
                <div class="row mb-2">
                  <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

                  <div class="col-md-3 mb-3">
                    <label for="rural_urban_id" class="form-label">Rural/Urban</label>
                    <select name="rural_urban_id" id="rural_urban_id" class="form-select">
                      <option value="">-----Select----</option>
                      @foreach ($levels as $key=>$value)
                      <option value="{{$key}}">{{$value}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3 mb-3">
                    <label for="created_by_local_body_code" class="form-label" id="blk_sub_txt">Block/Sub Division</label>
                    <select name="created_by_local_body_code" id="created_by_local_body_code" class="form-select">
                      <option value="">-----Select----</option>
                    </select>
                  </div>

                  <div class="col-md-3 mb-3" id="municipality_div" style="display:none;">
                    <label for="block_ulb_code" class="form-label">Municipality</label>
                    <select name="block_ulb_code" id="block_ulb_code" class="form-select">
                      <option value="">-----All----</option>
                    </select>
                  </div>

                  <div class="col-md-3 mb-3" style="display:none;" id="gp_ward_div">
                    <label for="gp_ward_code" class="form-label" id="gp_ward_txt">GP/Ward</label>
                    <select name="gp_ward_code" id="gp_ward_code" class="form-select">
                      <option value="">-----Select----</option>
                    </select>
                  </div>

                  <div class="col-md-3 mb-3">
                    <label for="caste_category" class="form-label">New Caste</label>
                    <select class="form-select" name="caste_category" id="caste_category">
                      <option value="">--All--</option>
                      @foreach(Config::get('constants.caste_lb') as $key=>$val)
                      <option value="{{$key}}">{{$val}}</option>
                      @endforeach
                    </select>
                    <span id="error_caste_category" class="text-danger small"></span>
                  </div>

                  <div class="col-md-12 text-center gap-2">
                    <button type="button" name="filter" id="filter" class="btn btn-success table-action-btn">
                      <i class="fas fa-search me-1"></i> Search
                    </button>
                    <button type="button" name="reset" id="reset" class="btn btn-warning table-action-btn">
                      <i class="fas fa-redo me-1"></i> Reset
                    </button>
                  </div>
                </div>
              </div>

              <!-- Bulk Action Section -->
              <div class="row mb-1">
                <div class="col-12 text-center" style="display: none;" id="approve_rejdiv">
                  <button type="button" name="bulk_approve" class="btn btn-success table-action-btn" id="bulk_approve">
                    <i class="fas fa-check-circle me-1"></i> Bulk Approve/Reject
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Applications List -->
          <div class="card mt-3">
            <div class="card-header card-header-custom">
              <h5 class="card-title mb-0">List of New Applicants</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive-custom">
                <table id="example" class="data-table" style="width:100%">
                  <thead>
                    <tr>
                      <th>Application ID</th>
                      <th>Beneficiary ID</th>
                      <th>Applicant Name</th>
                      <th>Swasthya Sathi Card No.</th>
                      <th>Mobile No</th>
                      <th width="15%">Action</th>
                      <th width="10%">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" id="check_all_btn">
                          <label class="form-check-label small">Select All</label>
                        </div>
                      </th>
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
</div>

<!-- Beneficiary Details Modal -->
<div class="modal fade ben_view_modal" id="benViewModal" tabindex="-1" role="dialog" aria-labelledby="benViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Beneficiary Details (<span class="applicant_id_modal"></span>)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Personal Details Accordion -->
        <div class="accordion mb-3" id="beneficiaryAccordion">
          <!-- Personal Details -->
          <div class="accordion-item singleInfo">
            <h2 class="accordion-header" id="personalHeading">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePersonal" aria-expanded="true" aria-controls="collapsePersonal">
                <i class="fas fa-user me-2"></i>Personal Details
                <div class="spinner-border spinner-border-sm ms-2 d-none" id="loader_img_personal"></div>
              </button>
            </h2>
            <div id="collapsePersonal" class="accordion-collapse collapse show" aria-labelledby="personalHeading" data-bs-parent="#beneficiaryAccordion">
              <div class="accordion-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <tbody>
                      <tr>
                        <th width="20%" class="bg-light">Aadhaar No.</th>
                        <td id="aadhar_no_encrypt" width="30%">-</td>
                        <td id="aadhar_no_original" style="display:none;" width="30%">-</td>
                        <td width="20%">
                          <button class="btn btn-outline-info btn-sm showhideAadhar" id="show_hide_aadhar">Show Original Aadhaar</button>
                        </td>
                      </tr>
                      <tr>
                        <th class="bg-light">Name</th>
                        <td id='ben_fullname' colspan="3">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Mobile No.</th>
                        <td id="mobile_no" colspan="3">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">DOB</th>
                        <td id="dob">-</td>
                        <th class="bg-light">Age</th>
                        <td id="ben_age">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Father Name</th>
                        <td id='father_fullname' colspan="3">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Mother Name</th>
                        <td id="mother_fullname" colspan="3">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Existing Caste:</th>
                        <td id="caste">-</td>
                        <th class="caste bg-light">Existing SC/ST Certificate No.</th>
                        <td id="caste_certificate_no" class="caste">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">New Caste:</th>
                        <td id="caste_new">-</td>
                        <th class="new_caste bg-light">New SC/ST Certificate No.</th>
                        <td id="new_caste_certificate_no" class="new_caste">-</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Address Details -->
          <div class="accordion-item singleInfo">
            <h2 class="accordion-header" id="contactHeading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContact" aria-expanded="false" aria-controls="collapseContact">
                <i class="fas fa-home me-2"></i>Address Details
                <div class="spinner-border spinner-border-sm ms-2 d-none" id="loader_img_contact"></div>
              </button>
            </h2>
            <div id="collapseContact" class="accordion-collapse collapse" aria-labelledby="contactHeading" data-bs-parent="#beneficiaryAccordion">
              <div class="accordion-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <tbody>
                      <tr>
                        <th width="25%" class="bg-light">District Name</th>
                        <td id="dist_name" width="25%">-</td>
                        <th width="25%" class="bg-light">Police Station</th>
                        <td id="police_station" width="25%">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Block/Municipality Name</th>
                        <td id="block_ulb_name">-</td>
                        <th class="bg-light">Gp Ward Name</th>
                        <td id="gp_ward_name">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Village/Town/City</th>
                        <td id="village_town_city">-</td>
                        <th class="bg-light">House / Premise No</th>
                        <td id="house_premise_no">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Post Office</th>
                        <td id="post_office">-</td>
                        <th class="bg-light">Pincode</th>
                        <td id="pincode">-</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Bank Details -->
          <div class="accordion-item singleInfo">
            <h2 class="accordion-header" id="bankHeading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBank" aria-expanded="false" aria-controls="collapseBank">
                <i class="fas fa-university me-2"></i>Bank Details
                <div class="spinner-border spinner-border-sm ms-2 d-none" id="loader_img_bank"></div>
              </button>
            </h2>
            <div id="collapseBank" class="accordion-collapse collapse" aria-labelledby="bankHeading" data-bs-parent="#beneficiaryAccordion">
              <div class="accordion-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <tbody>
                      <tr>
                        <th width="25%" class="bg-light">Bank Name</th>
                        <td id="bank_name" width="25%">-</td>
                        <th width="25%" class="bg-light">Branch Name</th>
                        <td id="branch_name" width="25%">-</td>
                      </tr>
                      <tr>
                        <th class="bg-light">Bank IFSC</th>
                        <td id="bank_ifsc">-</td>
                        <th class="bg-light">Bank Account No.</th>
                        <td id="bank_code">-</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Enclosure Details -->
          <div class="accordion-item singleInfo">
            <h2 class="accordion-header" id="encloserHeading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEncloser" aria-expanded="false" aria-controls="collapseEncloser">
                <i class="fas fa-file-alt me-2"></i>Enclosure Details
              </button>
            </h2>
            <div id="collapseEncloser" class="accordion-collapse collapse" aria-labelledby="encloserHeading" data-bs-parent="#beneficiaryAccordion">
              <div class="accordion-body">
                <table class="table table-bordered table-hover">
                  <tbody>
                    <tr>
                      <th width="30%" class="bg-light">New {{$doc_caste_arr->doc_name}}</th>
                      <td class="encView" id="encView">
                        <a class="btn btn-primary btn-sm" href="javascript:void(0);" onclick="View_encolser_modal('{{$doc_caste_arr->doc_name}}',{{$doc_caste_arr->id}})">
                          <i class="fas fa-eye me-1"></i> View
                        </a>
                      </td>
                      <td id="encGen">NA</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Action Section -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="actionHeading">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAction" aria-expanded="true" aria-controls="collapseAction">
                <i class="fas fa-cogs me-2"></i>Action
              </button>
            </h2>
            <div id="collapseAction" class="accordion-collapse collapse show" aria-labelledby="actionHeading" data-bs-parent="#beneficiaryAccordion">
              <div class="accordion-body">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label for="opreation_type" class="form-label required-field">Select Operation</label>
                    <select name="opreation_type" id="opreation_type" class="form-select opreation_type">
                      <option value="A" selected>Approve</option>
                      <option value="R">Reject</option>
                      <option value="T">Revert</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3 d-none" id="div_rejection">
                    <label for="reject_cause" class="form-label required-field">Select Reject/Reverted Cause</label>
                    <select name="reject_cause" id="reject_cause" class="form-select">
                      <option value="">--Select--</option>
                      @foreach($reject_revert_reason as $r_arr)
                      <option value="{{$r_arr->id}}">{{$r_arr->reason}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="accept_reject_comments" class="form-label">Enter Remarks</label>
                    <textarea class="form-control" name="accept_reject_comments" id="accept_reject_comments" rows="2"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <form method="POST" action="{{ route('application-details-read-only')}}" target="_blank" name="fullForm" id="fullForm">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="hidden" id="id" name="id" />
          <input type="hidden" id="application_id" name="application_id" />
          <input type="hidden" id="is_faulty" name="is_faulty" />
          <input type="hidden" id="new_caste" name="new_caste" />
          <input type="hidden" name="is_bulk" id="is_bulk" value="0" />
          <input type="hidden" name="applicantId[]" id="applicantId" value="" />
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="verifyReject">
          <i class="fas fa-check me-1"></i>Approve
        </button>
        <button style="display:none;" type="button" id="submitting" class="btn btn-success" disabled>
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          Processing Please Wait
        </button>
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
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/master-data-v2.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
   var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
  var base_url='{{ url('/') }}';
  
  // Initialize accordion event handlers for Bootstrap 5
  $(document).on('show.bs.collapse', '.accordion-collapse', function() {
    var id=$(this).attr('id');
    var application_id=$('#fullForm #application_id').val();
    var is_faulty=$('#fullForm #is_faulty').val();
    
    if(id=='collapsePersonal'){
      $('#sws_card_txt').text('');
      $('#duare_sarkar_registration_no').text('');
      $('#duare_sarkar_date').text('');
      $('#ben_fullname').text('');
      $('#father_fullname').text('');
      $('#mother_fullname').text('');
      $('#caste').text('');
      $('#gender').text('');
      $('#ben_age').text('');
      $('#mobile_no').text('');
      $('#email').text('');
      $('#aadhar_no_encrypt').text('');
        $('#loader_img_personal').removeClass('d-none');
          $.ajax({
            type: 'post',
            url: '{{ url('getCasteApplieddata') }}',
            data: {_token:'{{csrf_token()}}',application_id:application_id},    
            dataType: 'json',
            success: function (response) {
              $('#loader_img_personal').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              $('#caste_new').text(response.data.new_caste);
              if(response.data.new_caste=='SC' || response.data.new_caste=='ST'){
                $('#new_caste_certificate_no').text(response.data.new_caste_certificate_no);
                $('.new_caste').show();
              }
              else{
                $('.new_caste').hide();
              }
              $('#fullForm #new_caste').val(response.data.new_caste);
               
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_personal').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
         $.ajax({
            type: 'post',
            url: '{{ url('getPersonalApproved') }}',
            data: {_token:'{{csrf_token()}}',benid:application_id,is_faulty:is_faulty},    
            dataType: 'json',
            success: function (response) {
              $('#loader_img_personal').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              $('#sws_card_txt').text(response.personaldata.ss_card_no);
              $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
              $('#duare_sarkar_registration_no').text(response.personaldata.duare_sarkar_registration_no);
              $('#duare_sarkar_date').text(response.personaldata.formatted_duare_sarkar_date);
              $('#ben_fullname').text(response.personaldata.ben_fname+' '+response.personaldata.ben_mname+' '+response.personaldata.ben_lname);
              $('#mobile_no').text(response.personaldata.mobile_no);
              $('#email').text(response.personaldata.email);
              $('#gender').text(response.personaldata.gender);
              $('#dob').text(response.personaldata.formatted_dob);
              $('#ben_age').text(response.personaldata.age_ason_01012021);
              $('#father_fullname').text(response.personaldata.father_fname+' '+response.personaldata.father_mname+' '+response.personaldata.father_lname);
              $('#mother_fullname').text(response.personaldata.mother_fname+' '+response.personaldata.mother_mname+' '+response.personaldata.mother_lname);
              $('#caste').text(response.personaldata.caste);
              if(response.personaldata.caste=='SC' || response.personaldata.caste=='ST'){
                $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
                $('.caste').show();
              }
              else{
                $('.caste').hide();
              }
             
                $('#spouse_name').text(response.personaldata.spouse_fname+' '+response.personaldata.spouse_mname+' '+response.personaldata.spouse_lname);
               
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_personal').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
    }
    else if(id=='collapseContact'){
          $('#loader_img_contact').removeClass('d-none');
          $('#dist_name').text('');
          $('#block_ulb_name').text('');
          $('#gp_ward_name').text('');
          $('#village_town_city').text('');
          $('#police_station').text('');
          $('#post_office').text('');  
          $('#pincode').text(''); 
         $.ajax({
            type: 'post',
            url: '{{ url('getContactApproved') }}',
            data: {_token:'{{csrf_token()}}',benid:application_id,is_faulty:is_faulty},    
            dataType: 'json',
            success: function (response) {
              $('#loader_img_contact').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              $('#dist_name').text(response.contactdata.dist_name);
              $('#block_ulb_name').text(response.contactdata.block_ulb_name);
              $('#gp_ward_name').text(response.contactdata.gp_ward_name);
              $('#village_town_city').text(response.contactdata.village_town_city);
              $('#police_station').text(response.contactdata.police_station);
              $('#post_office').text(response.contactdata.post_office);  
              $('#pincode').text(response.contactdata.pincode);              
              $('#house_premise_no').text(response.contactdata.house_premise_no);              

            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_contact').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
    }
    else if(id=='collapseBank'){
        $('#loader_img_bank').removeClass('d-none');
        $('#bank_name').text('');
        $('#branch_name').text('');
        $('#bank_ifsc').text('');
        $('#bank_code').text('');
         $.ajax({
            type: 'post',
            url: '{{ url('getBankApproved') }}',
            data: {_token:'{{csrf_token()}}',benid:application_id,is_faulty:is_faulty},    
            dataType: 'json',
            success: function (response) {
              $('#loader_img_bank').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              $('#bank_name').text(response.bankdata.bank_name);
              $('#branch_name').text(response.bankdata.branch_name);
              $('#bank_ifsc').text(response.bankdata.bank_ifsc);
              $('#bank_code').text(response.bankdata.bank_code);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_bank').addClass('d-none');
              $('.ben_view_button').prop('disabled',false);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
    }else if(id=='collapseEncloser'){
        var new_caste=$('#fullForm #new_caste').val();
         if(new_caste=='SC' || new_caste=='ST'){
           $("#encView").show();
           $("#encGen").hide();
         }
         else{
            $("#encView").hide();
           $("#encGen").show();
         }
    }
  });

  $('.loader_img').hide();
  $('.sidebar-menu li').removeClass('active');
  $('.sidebar-menu #lb-caste').addClass("active"); 
  $('.sidebar-menu #caste_wrkflow').addClass("active"); 
  var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
  var base_url='{{ url('/') }}';
  $('#opreation_type').val('A');
  $("#verifyReject").html('<i class="fas fa-check me-1"></i>Approve');
  $('#div_rejection').addClass('d-none');
  
  var dataTable = "";
  if ($.fn.DataTable.isDataTable('#example')) {
    $('#example').DataTable().destroy();
  }
  
  dataTable=$('#example').DataTable({
    dom: 'Blfrtip',
    "scrollX": true,
    "paging": true,
    "searchable": true,
    "ordering":false,
    "bFilter": true,
    "bInfo": true,
    "pageLength":20,
    'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
    "serverSide": true,
    "processing":true,
    "bRetrieve": true,
    "oLanguage": {
      "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
    },
    "ajax": {
      url: "{{ url('workflowCaste') }}",
      type: "post",
      data:function(d){
        d.rural_urban_id= $("#rural_urban_id").val(),
        d.created_by_local_body_code= $("#created_by_local_body_code").val(),
        d.block_ulb_code= $('#block_ulb_code').val(),
        d.caste_category= $('#caste_category').val(),
        d.gp_ward_code= $('#gp_ward_code').val(),
        d._token= "{{csrf_token()}}"
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $('.preloader1').hide();
        alert(sessiontimeoutmessage);
        window.location.href=base_url;
      }
    },
    "initComplete":function(){
      //console.log('Data rendered successfully');
    },
    "columns": [
      { "data": "application_id" },
      { "data": "beneficiary_id" },
      { "data": "name" },
      { "data": "ss_card_no" },
      { "data": "mobile_no" },
      { "data": "view" },
      { "data": "check" }
    ],
    "buttons": [
      {
        extend: 'pdf',
        title: 'Process Application Report  <?php echo date('d-m-Y');  ?>',
        messageTop:'Date:<?php echo date('d/m/Y');  ?>',
        footer: true,
        pageSize:'A4',
        pageMargins: [ 40, 60, 40, 60 ],
        exportOptions: {
          columns: [0,1,2,3,4,5],
        },
        className: 'table-action-btn'
      },
      {
        extend: 'excel',
        title: 'Process Application Report <?php echo date('d-m-Y');  ?>',
        messageTop:'Date:<?php echo date('d/m/Y');  ?>',
        footer: true,
        pageSize:'A4',
        pageMargins: [ 40, 60, 40, 60 ],
        className: 'table-action-btn btn-danger',
        exportOptions: {
          format: {
            body: function (data, row, column, node ) {
              return column === 5 || column===3 ? "\0" + data : data;
            }
          },
          columns: [0,1,2,3,4,5],
          stripHtml: false,
        }
      },
    ],
  });

  $('#example').on('length.dt', function (e, settings, len) {
    $("#check_all_btn").prop("checked", false); 
  });

  $('#check_all_btn').on('change', function () {
    var checked = $(this).prop('checked');
    
    dataTable.cells(null, 6).every(function() {
      var cell = this.node();
      $(cell).find('input[type="checkbox"][name="chkbx"]').prop('checked', checked); 
    });
    
    var data = dataTable
    .rows(function(idx, data, node) {
      return $(node).find('input[type="checkbox"][name="chkbx"]').prop('checked');
    })
    .data()
    .toArray();
    
    if(data.length === 0){
      $("input.all_checkbox").prop("disabled", false);
    }
    else{
      $("input.all_checkbox").prop("disabled", true);
    }
    
    var anyBoxesChecked = false;
    var applicantId=Array();
    $('input[type="checkbox"][name="chkbx"]').each(function(index, value) {
      if ($(this).is(":checked")) {
        anyBoxesChecked = true;
        applicantId.push(value.value);
      }
    });
   
    $("#fullForm #applicantId").val($.unique(applicantId));
    
    if (anyBoxesChecked == true) {
      $('#approve_rejdiv').show();
      $('.ben_view_button').prop('disabled',true);
      document.getElementById('bulk_approve').disabled = false;
    } else{
      $('#approve_rejdiv').hide();
      $('.ben_view_button').prop('disabled',false);
      document.getElementById('bulk_approve').disabled = true;
    }
  });

  // CRITICAL MISSING: Individual checkbox change handler
  $(document).on('change', 'input[type="checkbox"][name="chkbx"]', function() {
    controlCheckBox();
  });

  $(document).on('click', '.ben_view_button', function() {
    $('#loader_img_personal').removeClass('d-none');
    $('.ben_view_button').prop('disabled',true);
    var benidArr=$(this).val();
    var benid_explode=benidArr.split('_');
    var benid=benid_explode[0];
    var is_faulty=benid_explode[1];
    
    $('#fullForm #application_id').val(benid);
    $('#fullForm #is_faulty').val(is_faulty);
    $('.applicant_id_modal').html(benid);
    
    // Hide other accordion sections
    $("#collapseContact").collapse('hide');
    $("#collapseBank").collapse('hide');
    $("#collapseEncloser").collapse('hide');
    
    $('#duare_sarkar_registration_no').text('');
    $('#duare_sarkar_date').text('');
    
    // Show all info sections for single view
    $(".accordion-item").show();
    
    $.ajax({
      type: 'post',
      url: '{{ url('getCasteApplieddata') }}',
      data: {_token:'{{csrf_token()}}',application_id:benid},    
      dataType: 'json',
      success: function (response) {
        $('#loader_img_personal').addClass('d-none');
        $('.ben_view_button').prop('disabled',false);
        $('#caste_new').text(response.data.new_caste);
        if(response.data.new_caste=='SC' || response.data.new_caste=='ST'){
          $('#new_caste_certificate_no').text(response.data.new_caste_certificate_no);
          $('.new_caste').show();
        }
        else{
          $('.new_caste').hide();
        }
        $('#fullForm #new_caste').val(response.data.new_caste);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $('#loader_img_personal').addClass('d-none');
        $('.ben_view_button').prop('disabled',false);
        alert(sessiontimeoutmessage);
        window.location.href=base_url;
      }
    });

    $.ajax({
      type: 'post',
      url: '{{ url('getPersonalApproved') }}',
      data: {_token:'{{csrf_token()}}',benid:benid,is_faulty:is_faulty},
      dataType: 'json',
      success: function (response) {
        $('#loader_img_personal').addClass('d-none');
        $('.ben_view_button').prop('disabled',false);
        $('#sws_card_txt').text(response.personaldata.ss_card_no);
        $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
        $('#duare_sarkar_registration_no').text(response.personaldata.duare_sarkar_registration_no);
        $('#duare_sarkar_date').text(response.personaldata.formatted_duare_sarkar_date);
        
        var ben_mname = response.personaldata.ben_mname !== undefined && response.personaldata.ben_mname !== null ? response.personaldata.ben_mname : "";
        var ben_lname = response.personaldata.ben_lname !== undefined && response.personaldata.ben_lname !== null ? response.personaldata.ben_lname : "";
        $('#ben_fullname').text(response.personaldata.ben_fname+' '+ben_mname+' '+ben_lname);
        
        $('#mobile_no').text(response.personaldata.mobile_no);
        $('#email').text(response.personaldata.email);
        $('#gender').text(response.personaldata.gender);
        $('#dob').text(response.personaldata.formatted_dob);
        $('#ben_age').text(response.personaldata.age_ason_01012021);
        
        var father_mname = response.personaldata.father_mname !== undefined && response.personaldata.father_mname !== null ? response.personaldata.father_mname : "";
        var father_lname = response.personaldata.father_lname !== undefined && response.personaldata.father_lname != null ? response.personaldata.father_lname : "";
        $('#father_fullname').text(response.personaldata.father_fname+' '+father_mname+' '+father_lname);
        
        var mother_mname = response.personaldata.mother_mname !== undefined && response.personaldata.mother_mname != null ? response.personaldata.mother_mname : "";
        var mother_lname = response.personaldata.mother_lname !== undefined && response.personaldata.mother_lname != null ? response.personaldata.mother_lname : "";
        $('#mother_fullname').text(response.personaldata.mother_fname+' '+mother_mname+' '+mother_lname);
        
        $('#caste').text(response.personaldata.caste);
        if(response.personaldata.caste=='SC' || response.personaldata.caste=='ST'){
          $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
          $('.caste').show();
        }
        else{
          $('.caste').hide();
        }
        
        var spouse_fname = response.personaldata.spouse_fname !== undefined && response.personaldata.spouse_fname !== null ? response.personaldata.spouse_fname : "";
        var spouse_mname = response.personaldata.spouse_mname !== undefined && response.personaldata.spouse_mname !== null ? response.personaldata.spouse_mname : "";
        var spouse_lname = response.personaldata.spouse_lname !== undefined && response.personaldata.spouse_lname !== null ? response.personaldata.spouse_lname : "";
        $('#spouse_name').text(spouse_fname+' '+spouse_mname+' '+spouse_lname);
        
        $('#fullForm #id').val(response.benid);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $('#loader_img_personal').addClass('d-none');
        $('.ben_view_button').prop('disabled',false);
        alert(sessiontimeoutmessage);
        window.location.href=base_url;
      }
    });
    
    $('#benViewModal').modal('show');
  });


    $('#rural_urban_id').change(function() {
       var filter_1=$(this).val();
       
        $('#created_by_local_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>');
        select_district_code= $('#dist_code').val();
       
        var htmlOption='<option value="">--All--</option>';
         $('#gp_ward_code').html('<option value="">--All --</option>');
        if(filter_1==1){
            $.each(subDistricts, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
           $("#blk_sub_txt").text('Subdivision');
           $("#gp_ward_txt").text('Ward');
           $("#municipality_div").show();
           $("#gp_ward_div").show();

        }
        else if(filter_1==2){
         // console.log(filter_1);
          $.each(blocks, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
             $("#blk_sub_txt").text('Block');
              $("#gp_ward_txt").text('GP');
             $("#municipality_div").hide();
            $("#gp_ward_div").show();

        }
        else{
           $("#blk_sub_txt").text('Block/Subdivision');
            $("#gp_ward_txt").text('GP/Ward');
            $("#municipality_div").hide();
        }
        $('#created_by_local_body_code').html(htmlOption);
       
    });
    $('#created_by_local_body_code').change(function() {
       var rural_urbanid= $('#rural_urban_id').val();
        $('#gp_ward_code').html('<option value="">--All --</option>');
       if(rural_urbanid==1){
       var sub_district_code=$(this).val();
       if(sub_district_code!=''){
        $('#block_ulb_code').html('<option value="">--All --</option>');
        select_district_code= $('#dist_code').val();
        var htmlOption='<option value="">--All--</option>';
       // console.log(sub_district_code);
        //console.log(select_district_code);

          $.each(ulbs, function (key, value) {
                if((value.district_code==select_district_code) && (value.sub_district_code==sub_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        $('#block_ulb_code').html(htmlOption);
       }
       else{
          $('#block_ulb_code').html('<option value="">--All --</option>');
       }   
       } 
      else if(rural_urbanid==2){
         $('#muncid').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
            var block_code=$(this).val();
          select_district_code= $('#dist_code').val();

          var htmlOption='<option value="">--All--</option>';
          $.each(gps, function (key, value) {
                if((value.district_code==select_district_code) && (value.block_code==block_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
          });
          $('#gp_ward_code').html(htmlOption);
          $("#gp_ward_div").show();
      }
       else{
          $('#block_ulb_code').html('<option value="">--All --</option>');
       } 
  });
  $('#block_ulb_code').change(function() {
      var muncid=$(this).val();
     
      var district=$("#dist_code").val();
      var urban_code=$("#rural_urban_id").val();
      if(district==''){
        $('#rural_urban_id').val('');
        $('#created_by_local_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        
    }
    if(urban_code==''){
        alert('Please Select Rural/Urban First');
        $('#created_by_local_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        $("#rural_urban_id").focus();
    }
    if(muncid!=''){
        var rural_urbanid= $('#rural_urban_id').val();
         
      if(rural_urbanid==1){
      
        $('#gp_ward_code').html('<option value="">--All --</option>');
        var htmlOption='<option value="">--All--</option>';
          $.each(ulb_wards, function (key, value) {
                if(value.urban_body_code==muncid){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        $('#gp_ward_code').html(htmlOption);
          //console.log(htmlOption);
       } 
    
       else{
          $('#gp_ward_code').html('<option value="">--All --</option>');
          $("#gp_ward_div").hide();
       } 
    }
    else{
       $('#gp_ward_code').html('<option value="">--All --</option>');
    }
    
    });


  $('#filter').click(function(){
    dataTable.ajax.reload();
  });

  $('#reset').click(function(){
    location.reload();
  });

  $('.showhideAadhar').click(function(){
    var ButtonText = $(this).text();
    if(ButtonText=='Show Original Aadhaar'){
      $("#aadhar_no_encrypt").hide();
      var applicant_id_modal=$(".applicant_id_modal").text();
      var is_faulty=$("#fullForm #is_faulty").val();
      $("#aadhar_no_original").show();
      $('#aadhar_no_original').html('<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');

      $.ajax({
        type: 'post',
        url: "{{route('getAadhaarApproved')}}",
        data: {_token:'{{csrf_token()}}',benid:applicant_id_modal,is_faulty:is_faulty},
        dataType: 'json',
        success: function (response) {
          $('#aadhar_no_original').html('');
          $('#aadhar_no_original').html(response.aadhar_no);
          $("#show_hide_aadhar").text('Show Encrypted Aadhaar');
          $("#aadhar_no_original").show();
        },
        error: function (jqXHR, textStatus, errorThrown) {
          $('#aadhar_no_original').html('');
          $('.ben_view_button').prop('disabled',false);
          alert(sessiontimeoutmessage);
          window.location.href=base_url;
        }
      });
    } 
    else if(ButtonText=='Show Encrypted Aadhaar'){
      $(this).text('Show Original Aadhaar');
      $("#aadhar_no_encrypt").show();
      $("#aadhar_no_original").hide();
    }       
  });
  $('#encolserModal').on('hidden.bs.modal', function (e) {
    $('#benViewModal').css({
      'overflow': 'auto',
    });
  });

  // FIXED: Use change event instead of click for operation type
  $(document).on('change', '.opreation_type', function() {
    if($(this).val()=='T' || $(this).val()=='R'){
      $('#div_rejection').removeClass('d-none').show();
      if($(this).val()=='T')
        $("#verifyReject").html('<i class="fas fa-undo me-1"></i>Revert');
      else if($(this).val()=='R')
        $("#verifyReject").html('<i class="fas fa-times me-1"></i>Reject');
    }
    else{
      $("#verifyReject").html('<i class="fas fa-check me-1"></i>Approve');
      $('#div_rejection').addClass('d-none').hide();
      $("#reject_cause").val('');
    }
  });

  // $('#bulk_approve').click(function(){
  //   // Hide beneficiary details for bulk action
  //   $(".accordion-item").not('#actionHeading').hide();
  //   $("#fullForm #is_bulk").val(1);
  //   $('#fullForm #id').val('');
  //   $('#fullForm #application_id').val('');
  //   benid="";
    
  //   $('.ben_view_modal').modal('show');
  // });

  $('#bulk_approve').click(function(){

    // bulk mode ON
    $("#fullForm #is_bulk").val(1);

    // Remove any selected beneficiary
    $('#fullForm #id').val('');
    $('#fullForm #application_id').val('');

    // Hide all beneficiary detail sections
    $(".singleInfo").hide();

    // Clear all fields inside modal
    $('.ben_view_body').find('td').html('');
    $('.ben_view_body').find('input').val('');
    $('.ben_view_body').find('textarea').val('');

    // Reset operation panel only
    $("#opreation_type").val('A');
    $("#div_rejection").hide();
    $("#verifyReject").html("Approve");

    // Finally open modal without loading beneficiary data
    $('.ben_view_modal').modal('show');
});
  
  $(document).on('click', '#verifyReject', function() { 
    var scheme_id = $('#scheme_id').val();
    var reject_cause = $('#reject_cause').val();
    var opreation_type = $('#opreation_type').val();
    var accept_reject_comments = $('#accept_reject_comments').val();
    var applicantId = $('#applicantId').val();
    var is_bulk = $('#is_bulk').val();
    var id = $('#id').val();
    var valid=1;
    // alert(opreation_type);
    if(opreation_type=='R' || opreation_type=='T'){
      if(reject_cause==''){
        $.alert({
          title: 'Error!!',
          type: 'red',
          icon: 'fa fa-warning',
          content: '<strong>Please Select Cause</strong>',
        });
        return false;
      }
    }
    
    if(valid==1){
      // alert('ok');
      $.confirm({
        title: 'Warning',
        type: 'orange',
        icon: 'fa fa-warning',
        content: '<strong>Are you sure to proceed?</strong>',
        buttons: {
          Ok: function(){
            $("#submitting").show();
            $("#verifyReject").hide();
            var id = $('#id').val();
            $.ajax({
              type: 'POST',
              url: "{{ url('approveDataCaste') }}",
              data: {
                scheme_id: scheme_id,
                reject_cause: reject_cause,
                opreation_type: opreation_type,
                accept_reject_comments: accept_reject_comments,
                applicantId: applicantId,
                is_bulk: is_bulk,
                id: id,
                _token: '{{ csrf_token() }}',
              },
              success: function (data) {
                if(data.return_status){
                  dataTable.ajax.reload(null, false);
                  $("#fullForm #is_bulk").val(0);
                  $('#fullForm #id').val('');
                  $('#fullForm #application_id').val('');
                  document.getElementById('bulk_approve').disabled = true;
                  $("#check_all_btn").prop("checked", false).prop("disabled", false);
                  $('.ben_view_modal').modal('hide');
                  $.confirm({
                    title: 'Success',
                    type: 'green',
                    icon: 'fa fa-check',
                    content: data.return_msg,
                    buttons: {
                      Ok: function(){
                        $("#submitting").hide();
                        $("#verifyReject").show();
                        $("html, body").animate({ scrollTop: 0 }, "slow");
                        if(data.return_status==1){
                          window.location.href='workflowCaste';
                        }
                      }
                    }
                  });
                }
                else{
                  if(data.return_msg=='Aadhaar No. is Duplicate..'){
                    alert(data.return_msg);
                      // window.location.href='workflowCaste?pr1=lb_wcd';
                  }
                  else{
                    $("#submitting").hide();
                    $("#verifyReject").show();
                    $('#errorDiv').animate({ scrollTop: 0 }, 'slow');
                    alert(sessiontimeoutmessage);
                    // window.location.href=base_url;
                  }
                }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                $.confirm({
                  title: 'Error',
                  type: 'red',
                  icon: 'fa fa-warning',
                  content: sessiontimeoutmessage,
                  buttons: {
                    Ok: function(){
                      //location.reload();
                    }
                  }
                });
              }           
            });
          },
          Cancel: function () {

          }
        }
      });
    }
  });
});

function controlCheckBox(){
  var anyBoxesChecked = false;
  var applicantId=Array();
  $('input[type="checkbox"][name="chkbx"]').each(function() {
    if ($(this).is(":checked")) {
      anyBoxesChecked = true;
      applicantId.push($(this).val());
    }
  });
  
  $("#fullForm #applicantId").val($.unique(applicantId));
  
  if (anyBoxesChecked == true) {
    $('#approve_rejdiv').show();
    $("#check_all_btn").prop("disabled", true);
    $('.ben_view_button').prop('disabled',true);
    document.getElementById('bulk_approve').disabled = false;
  } else{
    $('#approve_rejdiv').hide();
    $('.ben_view_button').prop('disabled',false);
    $("#check_all_btn").prop("disabled", false);
    document.getElementById('bulk_approve').disabled = true;
  }
}

function View_encolser_modal(doc_name,doc_type){
  var application_id=$('#fullForm #application_id').val();
  $('#encolser_name').html('');
  $('#encolser_content').html('');
  $('#encolser_name').html(doc_name+'('+application_id+')');
  $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');

  $.ajax({
    url: '{{ url('ajaxModifiedCasteEncolser') }}',
    type: "POST",
    data: {
      doc_type: doc_type,
      application_id: application_id,
      _token: '{{ csrf_token() }}',
    },
  }).done(function(data, textStatus, jqXHR) {
    $('#encolser_content').html('');
    $('#encolser_content').html(data);
    $("#encolserModal").modal('show');
  }).fail(function(jqXHR, textStatus, errorThrown) {
    $('#encolser_content').html('');
    alert(sessiontimeoutmessage);
    window.location.href=base_url;
  });
}

// MISSING: Helper functions
function printMsg(msg,msgtype,divid) {
  $("#"+divid).find("ul").html('');
  $("#"+divid).css('display','block');
  if(msgtype=='0'){
    $("#"+divid).removeClass('alert-success');
    $("#"+divid).addClass('alert-warning');
  }
  else{
    $("#"+divid).removeClass('alert-warning');
    $("#"+divid).addClass('alert-success');
  }
  if(Array.isArray(msg)){
    $.each(msg, function(key, value) {
      $("#"+divid).find("ul").append('<li>'+value+'</li>');
    });
  }
  else{
    $("#"+divid).find("ul").append('<li>'+msg+'</li>');
  }
}

function closeError(divId){
  $('#'+divId).hide();
}
</script>
@endpush