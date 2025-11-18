@extends('layouts.app-template-datatable')
@push('styles')
    <style>
        .box {
            width: 800px;
            margin: 0 auto;
        }

        .active_tab1 {
            background-color: #fff;
            color: #333;
            font-weight: 600;
        }

        .inactive_tab1 {
            background-color: #f5f5f5;
            color: #333;
            cursor: not-allowed;
        }

        .has-error {
            border-color: #cc0000;
            background-color: #ffff99;
        }

        .select2 {
            width: 100% !important;
        }

        .select2 .has-error {
            border-color: #cc0000;
            background-color: #ffff99;
        }

        .modal_field_name {
            float: left;
            font-weight: 700;
            margin-right: 1%;
            padding-top: 1%;
            margin-top: 1%;
        }

        .modal_field_value {
            margin-right: 1%;
            padding-top: 1%;
            margin-top: 1%;
        }

        .row {
            margin-right: 0px !important;
            margin-left: 0px !important;
            margin-top: 1% !important;
        }

        .section1 {
            border: 1.5px solid #9187878c;
            margin: 2%;
            padding: 2%;
        }

        .color1 {
            margin: 0% !important;
            background-color: #5f9ea061;
        }

        .modal-header {
            background-color: #7fffd4;
        }

        .required-field::after {
            content: "*";
            color: red;
        }

        .imageSize {
            font-size: 9px;
            color: #333;
        }

        #search_sws {
            margin-top: 20px;
        }

        #search_faulty {
            margin-top: 20px;
            margin-left: 100px;
        }
    </style>
@endpush
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content">
    <div class="row">
      <!-- left column -->
      <div class="col-md-12">
        <!-- general form elements -->
        <div>

          <div class="tab-content" style="margin-top:16px;">

            <div class="tab-pane active" id="personal_details">
              <div class="card card-default">
                <div class="card-header">
                  <h4><b>Lakhasmir Bhandar Process Incomplete Faulty Applications List</b></h4>
                </div>
                <div class="card-body">

                  @if (($message = Session::get('success')) && ($id = Session::get('id')))
                    <div class="alert alert-success alert-dismissible fade show">
                      <strong>{{ $message }} with Application ID: {{ $id }}</strong>
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                  @endif

                  @if ($message = Session::get('error'))
                    <div class="alert alert-danger alert-dismissible fade show" style="margin:5px;">
                      <strong>{{ $message }}</strong>
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                  @endif

                  @if ($message = Session::get('succes'))
                    <div class="alert alert-success alert-dismissible fade show">
                      <strong>{{ $message }}</strong>
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                  @endif

                  @if (count($errors) > 0)
                    <div class="alert alert-danger alert-dismissible fade show" style="margin:5px;">
                      <ul>
                        @foreach ($errors as $error)
                          <li><strong>{{ $error }}</strong></li>
                        @endforeach
                      </ul>
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                  @endif

                  <div class="card card-default mb-3">
                    <div class="card-header" style="font-size:15px; font-weight:bold; font-style:italic; padding:5px 15px;">
                      <span id="panel-icon">Find Application using Application ID or Mobile no</span>
                    </div>
                    <div class="card-body p-2">
                      <div class="row">
                        <div class="col-md-12">
                          <div class="row">
                            <form method="post" id="search_form" action="{{ url('searchfaulty-application') }}" class="submit-once">
                              {{ csrf_field() }}

                              <div class="col-md-3" id="benid_div">
                                <label class="control-label">Find Using Application ID/Mobile No.<span class="text-danger">*</span></label>
                                <input type="text" name="applicant_id_mobile" id="applicant_id_mobile" class="form-control"
                                  placeholder="Application ID/Mobile No." autocomplete="off"
                                  style="font-size:16px; margin:5px auto;"
                                  onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" />
                                <span class="text-danger" id="error_application_id_mobile"></span>

                                @if ($message = Session::get('norecord'))
                                  <div class="mt-2">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    <strong><i class="fa fa-times text-danger"></i>
                                      <span class="txt_failure" style="color:red;"> No Record Found</span></strong>
                                  </div>
                                @endif

                                @if ($message = Session::get('successrecord'))
                                  <div class="mt-2">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    <strong><span class="txt_success" style="color:rgb(37,94,46);"> Application is Sent to Approver for Approval</span></strong>
                                  </div>
                                @endif
                              </div>

                              <div class="col-md-3 align-self-end">
                                @if ($designation_id == 'Verifier')
                                  <button class="btn btn-primary" name="submit_btn" id="submit_btn" type="button">
                                    <i class="fa fa-search"></i> Find Application
                                  </button>
                                @else
                                  <button class="btn btn-primary" name="approvemodal_btn" id="approvemodal_btn" value="{{ 1235455 }}" type="button">
                                    <i class="fa fa-search"></i> Find Application
                                  </button>
                                @endif
                              </div>

                            </form>
                          </div>
                        </div>
                      </div>&nbsp;
                    </div>
                  </div>

                  <div class="card card-default">
                    <div class="card-header" style="font-size:15px; font-weight:bold; font-style:italic; padding:5px 15px;">
                      <span id="panel-icon">Faulty Application List</span>
                    </div>

                    <div class="card-body p-2">
                      <form method="post" id="register_form" action="{{ url('verifyDatafaultymigrate') }}" class="submit-once">
                        {{ csrf_field() }}

                        <input type="hidden" name="is_bulk_all" id="is_bulk_all" value="1">
                        <input type="hidden" name="opreation_type_all" id="opreation_type_all" value="A">

                        <div class="row mb-2">
                          @if ($is_rural_visible)
                            <div class="col-md-2">
                              <label class="control-label">Rural/Urban</label>
                              <select name="rural_urbanid" id="rural_urbanid" class="form-control">
                                <option value="">-----All----</option>
                                @foreach (Config::get('constants.rural_urban') as $key => $value)
                                  <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                              </select>
                            </div>
                          @else
                            <input type="hidden" name="rural_urbanid" id="rural_urbanid" value="{{ $is_urban }}" />
                          @endif

                          @if ($urban_visible)
                            <div class="col-md-3">
                              <label class="control-label" id="blk_sub_txt">Block/Subdivision</label>
                              <select name="urban_body_code" id="urban_body_code" class="form-control">
                                <option value="">-----All----</option>
                              </select>
                            </div>
                          @else
                            <input type="hidden" name="urban_body_code" id="urban_body_code" value="{{ $urban_body_code }}" />
                          @endif

                          @if ($munc_visible)
                            <div class="col-md-3" id="municipality_div">
                              <label class="control-label">Municipality</label>
                              <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                                <option value="">-----All----</option>
                                @if (count($muncList) > 0)
                                  @foreach ($muncList as $muncArr)
                                    <option value="{{ $muncArr->urban_body_code }}">{{ trim($muncArr->urban_body_name) }}</option>
                                  @endforeach
                                @endif
                              </select>
                            </div>
                          @endif

                          @if ($gp_ward_visible)
                            <div class="col-md-4" id="gp_ward_div">
                              <label class="form-label" id="gp_ward_txt">GP/Ward</label>
                              <select name="gp_ward_code" id="gp_ward_code" class="form-control" tabindex="17">
                                <option value="">--All --</option>
                                @if (count($gpwardList) > 0)
                                  @foreach ($gpwardList as $gp_ward_arr)
                                    <option value="{{ $gp_ward_arr->gram_panchyat_code }}">{{ trim($gp_ward_arr->gram_panchyat_name) }}</option>
                                  @endforeach
                                @endif
                              </select>
                              <span id="error_gp_ward_code" class="text-danger"></span>
                            </div>
                          @endif

                          <div class="col-md-4">
                            <label class="required-field">Application Type</label>
                            <select name="application_type" id="application_type" class="form-control">
                              <option value="1" selected>Pending</option>
                              @if ($designation_id == 'Verifier')
                                <option value="2">Verified but Approval Pending</option>
                              @endif
                              <option value="3">Verified and Approved</option>
                            </select>
                            <span id="error_application_type" class="text-danger"></span>
                          </div>

                          <div class="col-md-2 align-self-end">
                            <button type="button" name="filter" id="filter" class="btn btn-warning">Filter</button>
                          </div>
                        </div>

                        <div class="row mb-2">
                          <div class="col-md-2">
                            <button type="button" name="get_excel" id="get_excel" class="btn btn-success">Export All Data to Excel</button>
                          </div>
                        </div>

                        @if ($designation_id == 'Approver')
                          <input type="hidden" name="_token" value="{{ csrf_token() }}">
                          <button type="button" style="margin:0 0 2% 0;" name="bulk_approve" id="confirm" value="approve"
                            class="btn btn-info col-sm-3 col-xs-5 btn-margin" disabled>Approve</button>
                        @endif

                        <input type="hidden" name="district_code" id="district_code" value="{{ $district_code }}">

                        <div class="row">
                          <div class="col-sm-12">
                            <table id="example" class="table table-bordered table-hover dataTable w-100" role="grid" aria-describedby="example2_info">
                              <thead>
                                <tr role="row">
                                  <th width="20%">Application ID</th>
                                  <th width="20%">Beneficiary Name</th>
                                  <th width="20%">Mobile Number</th>
                                  <th width="20%">Action</th>
                                  @if ($designation_id == 'Approver')
                                    <th width="2%">Check <input type="checkbox" id="selectAll" name="selectAll" onClick="controlCheckBoxall();"></th>
                                  @endif
                                </tr>
                              </thead>
                              <tbody></tbody>
                            </table>
                          </div>
                        </div>

                        <div class="row mt-2">
                          <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate"></div>
                          </div>
                        </div>

                      </form>
                    </div>
                  </div>

                </div>
              </div>
            </div>

          </div> <!-- tab-content -->
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Beneficiary Details Modal (BS5) -->
<div class="modal fade bd-example-modal-lg ben_view_modal" id="ben_view_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header singleInfo">
        <h3 class="modal-title">Beneficiary Details (<span class="applicant_id_modal"></span>)</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body ben_view_body">
        <div class="accordion" id="benAccordion">

          <!-- Personal -->
          <div class="card mb-2">
            <div class="card-header d-flex justify-content-between align-items-center" id="personal_header">
              <a class="text-decoration-none" data-bs-toggle="collapse" href="#collapsePersonal" role="button" aria-expanded="true" aria-controls="collapsePersonal">
                Personal Details
              </a>
              <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150" id="loader_img_personal"></div>
            </div>
            <div id="collapsePersonal" class="collapse show" data-bs-parent="#benAccordion">
              <div class="card-body">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <th>Aadhaar No.</th>
                      <td id="aadhar_no_encrypt"></td>
                      <td id="aadhar_no_original" style="display:none;"></td>
                      <td><button class="btn btn-info showhideAadhar" id="show_hide_aadhar">Show Original Aadhaar</button></td>
                    </tr>
                    <tr>
                      <th>Duare Sarkar Registration no.</th><td id='duare_sarkar_registration_no'></td>
                      <th>Duare Sarkar Date:</th><td id='duare_sarkar_date'></td>
                    </tr>
                    <tr><th>Name</th><td id='ben_fullname'></td></tr>
                    <tr>
                      <th>Mobile No.</th><td id="mobile_no"></td>
                      <th>Email:</th><td id="email_id"></td>
                    </tr>
                    <tr>
                      <th>Gender</th><td id="gender"></td>
                      <th>DOB</th><td id="dob"></td>
                      <td id="ben_age"></td>
                    </tr>
                    <tr><th>Father Name</th><td id='father_fullname'></td></tr>
                    <tr><th>Mother Name</th><td id="mother_fullname"></td></tr>
                    <tr><th>Spouse Name</th><td id="spouse_name"></td></tr>
                    <tr>
                      <th>Caste:</th><td id="caste"></td>
                      <th class="caste">SC/ST Certificate No.</th><td id="caste_certificate_no" class="caste"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Address -->
          <div class="card mb-2">
            <div class="card-header d-flex justify-content-between align-items-center" id="contact_header">
              <a class="collapsed text-decoration-none" data-bs-toggle="collapse" href="#collapseContact" role="button" aria-expanded="false" aria-controls="collapseContact">
                Address Details
              </a>
              <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150" id="loader_img_contact"></div>
            </div>
            <div id="collapseContact" class="collapse" data-bs-parent="#benAccordion">
              <div class="card-body">
                <table class="table table-bordered">
                  <tbody>
                    <tr><th>District Name</th><td id="dist_name"></td><th>Police Station</th><td id="police_station"></td></tr>
                    <tr><th>Block/Municipality Name</th><td id="block_ulb_name"></td><th>Gp Ward Name</th><td id="gp_ward_name"></td></tr>
                    <tr><th>Village/Town/City</th><td id="village_town_city"></td><th>House / Premise No</th><td id="house_premise_no"></td></tr>
                    <tr><th>Post Office</th><td id="post_office"></td><th>Pincode</th><td id="pincode"></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Bank -->
          <div class="card mb-2">
            <div class="card-header d-flex justify-content-between align-items-center" id="bank_header">
              <a class="collapsed text-decoration-none" data-bs-toggle="collapse" href="#collapseBank" role="button" aria-expanded="false" aria-controls="collapseBank">
                Bank Details
              </a>
              <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150" id="loader_img_bank"></div>
            </div>
            <div id="collapseBank" class="collapse" data-bs-parent="#benAccordion">
              <div class="card-body">
                <table class="table table-bordered">
                  <tbody>
                    <tr><th>Bank Name</th><td id="bank_name"></td><th>Branch Name</th><td id="branch_name"></td></tr>
                    <tr><th>Bank IFSC</th><td id="bank_ifsc"></td><th>Bank Account No.</th><td id="bank_code"></td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Encloser -->
          <div class="card mb-2">
            <div class="card-header d-flex justify-content-between align-items-center" id="encloser_header">
              <a class="collapsed text-decoration-none" data-bs-toggle="collapse" href="#collapseEncloser" role="button" aria-expanded="false" aria-controls="collapseEncloser">
                Encolser Details
              </a>
              <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150" id="loader_img_encolser"></div>
            </div>
            <div id="collapseEncloser" class="collapse" data-bs-parent="#benAccordion">
              <div class="card-body">
                <table id="enCloserTable" class="table table-bordered">
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Action -->
          <div class="card">
            <div class="card-header"><h4 class="card-title">Action</h4></div>
            <div class="card-body">
              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="required" for="opreation_type">Select Operation</label>
                  <select name="opreation_type" id="opreation_type" class="form-control opreation_type">
                    @if ($approval_allowded)
                      <option value="A" selected>Approve</option>
                    @endif
                    <option value="T">Reverted</option>
                  </select>
                </div>

                <div class="col-md-4" id="div_rejection" style="display:none;">
                  <label class="required" for="reject_cause">Select Reverted Cause</label>
                  <select name="reject_cause" id="reject_cause" class="form-control">
                    <option value="">--Select--</option>
                    @foreach ($reject_revert_reason as $r_arr)
                      <option value="{{ $r_arr->id }}">{{ $r_arr->reason }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="accept_reject_comments">Enter Remarks</label>
                  <textarea name="accept_reject_comments" id="accept_reject_comments" class="form-control" style="height:40px;"></textarea>
                </div>
              </div>
            </div>
          </div>

        </div> <!-- accordion -->

        <div class="modal-footer">
          <form method="POST" action="{{ route('application-details-read-only') }}" target="_blank" name="fullForm" id="fullForm" class="w-100">
            @csrf
            <input type="hidden" name="is_bulk" id="is_bulk" value="0" />
            <input type="hidden" id="scheme_id" name="scheme_id" value="{{ $scheme_id }}" />
            <input type="hidden" id="id" name="id" />
            <input type="hidden" id="application_id" name="application_id" />
            <input type="hidden" id="is_draft" name="is_draft" value="1" />
            <input type="hidden" name="applicantId[]" id="applicantId" value="" />

            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-success" id="verifyReject">Approve</button>
              <button style="display:none;" type="button" id="submitting" class="btn btn-success success" disabled>Processing Please Wait</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Encloser Modal (View files) -->
<div class="modal fade" id="encolser_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="encolser_name">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div id="encolser_content" class="p-3"></div>
    </div>
  </div>
</div>

<!-- Confirm Approve Modal -->
<div id="modalConfirm" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-confirm">
    <div class="modal-content">
      <div class="modal-header flex-column"></div>
      <div class="modal-body">
        <h4 class="modal-title w-100">Do you really want to Approve?</h4>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-info" id="confirm_yes">OK</button>
        <button type="button" id="submittingapprove" class="btn btn-success btn-lg" disabled style="display:none;">Submitting please wait</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div id="modalReject" class="modal fade" tabindex="-1" aria-hidden="true">
  <form method="POST" action="{{ route('partialReject') }}" name="faultyReject" id="faultyReject">
    @csrf
    <input type="hidden" id="application_id" name="application_id" />
    <div class="modal-dialog modal-confirm">
      <div class="modal-content">
        <div class="modal-header flex-column">
          <h4 class="modal-title w-100">Do you really want to Reject the application(<span id="application_text_approve"></span>)?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="form-group col-md-12" id="div_rejection">
              <label class="required-field" for="reject_cause">Select Reject Cause</label>
              <select name="reject_cause" id="reject_cause" class="form-control">
                <option value="">--Select--</option>
                @foreach ($reject_revert_reason as $r_arr)
                  <option value="{{ $r_arr->id }}">{{ $r_arr->reason }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger modal-submitapprove">Reject</button>
          <button type="button" id="submittingapprove" class="btn btn-success success btn-lg" disabled style="display:none;">Submitting please wait</button>
        </div>
      </div>
    </div>
  </form>
</div>

@endsection
@push('scripts')
    <script src="{{ asset('js/select2.full.min.js') }}"></script>
    <script src="{{ URL::asset('js/site.js') }}"></script>
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script src="{{ asset('js/jquery-confirm.min.js') }}"></script>
    <script>
        $(document).ready(function () {

            $("#confirm").hide();
            $("#submittingapprove").hide();
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #lb-draft-list').addClass("active");
            var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
            var base_url = '{{ url('/') }}';
            var dataTable = "";
            $("#submitting").hide();
            $("#ImportListMsg").hide();
            $(".ImportLoader").hide();
            $("#submittingapprove").hide();
            loadDatatable();


            $(document).on('click', '.rej-btn', function () {
                $('#faultyReject #application_id').val('');
                $('#application_text_approve').text('');
                $('.rej-btn').attr('disabled', false);
                var benid = $(this).val();
                //alert(benid);
                $('#rej_' + benid).attr('disabled', true);
                $('#faultyReject #application_id').val(benid);
                $('#application_text_approve').text(benid);
                $('#modalReject').modal();
            });
            $('#modalReject').on('hidden.bs.modal', function () {
                $('.rej-btn').attr('disabled', false);
            });
            $('.modal-submitapprove').on('click', function () {
                var reject_cause = $("#reject_cause").val();
                if (reject_cause != '') {
                    $(".modal-submitapprove").hide();
                    $("#submittingapprove").show();
                    $("#faultyReject").submit();
                } else {
                    alert('Please Select Rejection Cause');
                    $("#reject_cause").focus();
                    return false;
                }

            });

        });

        $('#submit_btn').click(function () {
            // $('#loader_img_search').hide();

            if ($.trim($('#applicant_id_mobile').val()).length == 0) {
                error_application_id_mobile = 'Please Enter Application ID/ Mobile no.';
                $('#error_application_id_mobile').text(error_application_id_mobile);
            } else {
                error_application_id_mobile = '';
                $('#error_application_id_mobile').text(error_application_id_mobile);
            }
            if (error_application_id_mobile == '') {
                // $('#loader_img_search').show();
                $("#search_form").submit();

            }







        });

        $('#approvemodal_btn').click(function () {

            if ($.trim($('#applicant_id_mobile').val()).length == 0) {
                error_application_id_mobile = 'Please Enter Application ID/ Mobile no.';
                $('#error_application_id_mobile').text(error_application_id_mobile);
            } else {
                error_application_id_mobile = '';
                $('#error_application_id_mobile').text(error_application_id_mobile);
            }

            if (error_application_id_mobile == '') {
                var applicant_id_mobile = $("#applicant_id_mobile").val();

                $.ajax({
                    type: 'post',
                    url: "{{ route('searchfaulty-application') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        applicant_id_mobile: applicant_id_mobile
                    },
                    dataType: 'json',
                    success: function (response) {
                        // alert(response.return_status);
                        if (response.return_status == 1) {
                            // $('#loader_img_search').hide();
                            // $('.ben_view_modal').modal('show');

                            // $('.ben_view_button').load({{ route('getfaultyBenViewPersonalData') }});

                            benViewButtonModal(response.application_id);
                        } else if (response.return_status == 0) {
                            // $('#loader_img_search').show();
                            alert('No data found');
                        }
                        else {
                            alert('No data found');

                        }


                    },
                    complete: function () {
                        //$('#aadhar_no_original').html('');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {

                    }
                });

            }







        });

        $('#rural_urbanid').change(function () {

            var rural_urbanid = $(this).val();
            if (rural_urbanid != '') {
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $('#gp_ward_code').html('<option value="">--All --</option>');

                select_district_code = $('#district_code').val();
                console.log(select_district_code);
                var htmlOption = '<option value="">--All--</option>';
                if (rural_urbanid == 1) {
                    $("#municipality_div").show();
                    $("#blk_sub_txt").text('Subdivision');
                    $("#gp_ward_txt").text('Ward');
                    $.each(subDistricts, function (key, value) {
                        if ((value.district_code == select_district_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                        }
                    });
                } else if (rural_urbanid == 2) {

                    $("#municipality_div").hide();
                    $("#blk_sub_txt").text('Block');
                    $("#gp_ward_txt").text('GP');
                    $.each(blocks, function (key, value) {

                        if ((value.district_code == select_district_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                        }
                    });
                }
                $('#urban_body_code').html(htmlOption);
            } else {
                $("#blk_sub_txt").text('Block/Subdivision');
                $("#gp_ward_txt").text('GP/Ward');
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $('#gp_ward_code').html('<option value="">--All --</option>');
            }
        });
        $('#urban_body_code').change(function () {
            var rural_urbanid = $('#rural_urbanid').val();
            if (rural_urbanid == 1) {
                var sub_district_code = $(this).val();

                $('#block_ulb_code').html('<option value="">--All --</option>');
                select_district_code = $('#district_code').val();
                var htmlOption = '<option value="">--All--</option>';
                // console.log(sub_district_code);
                //console.log(select_district_code);

                $.each(ulbs, function (key, value) {
                    if ((value.district_code == select_district_code) && (value.sub_district_code ==
                        sub_district_code)) {
                        htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                    }
                });
                $('#block_ulb_code').html(htmlOption);
            } else if (rural_urbanid == 2) {
                $('#muncid').html('<option value="">--All --</option>');
                $("#municipality_div").hide();
                var block_code = $(this).val();
                select_district_code = $('#district_code').val();

                var htmlOption = '<option value="">--All--</option>';
                $.each(gps, function (key, value) {
                    if ((value.district_code == select_district_code) && (value.block_code == block_code)) {
                        htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                    }
                });
                //console.log(htmlOption);
                $('#gp_ward_code').html(htmlOption);

                $("#gp_ward_div").show();
            } else {
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $('#gp_ward_code').html('<option value="">--All --</option>');
                $("#municipality_div").hide();
                $("#gp_ward_div").hide();
            }


        });

        $('#block_ulb_code').change(function () {
            var muncid = $(this).val();
            var district = $("#district_code").val();
            var urban_code = $("#rural_urbanid").val();
            if (district == '') {
                $('#rural_urbanid').val('');
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                alert('Please Select District First');
                $("#district_code").focus();

            }
            if (urban_code == '') {
                alert('Please Select Rural/Urban First');
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $("#urban_body_code").focus();
            }
            if (muncid != '') {
                var rural_urbanid = $('#rural_urbanid').val();
                if (rural_urbanid == 1) {


                    $('#gp_ward_code').html('<option value="">--All --</option>');
                    var htmlOption = '<option value="">--All--</option>';
                    $.each(ulb_wards, function (key, value) {
                        if (value.urban_body_code == muncid) {
                            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                        }
                    });
                    $('#gp_ward_code').html(htmlOption);

                } else {
                    $('#gp_ward_code').html('<option value="">--All --</option>');
                    $("#gp_ward_div").hide();
                }
            } else {
                $('#gp_ward_code').html('<option value="">--All --</option>');
            }

        });

        function View_encolser_modal(doc_name, doc_type, is_profile_pic, application_id) {
            $('#encolser_name').html('');
            $('#encolser_content').html('');
            $('#encolser_name').html(doc_name + '(' + application_id + ')');
            $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');

            $.ajax({
                url: '{{ url('ajaxGetEncloser') }}',
                type: "POST",
                data: {
                    doc_type: doc_type,
                    is_profile_pic: is_profile_pic,
                    application_id: application_id,
                    _token: '{{ csrf_token() }}',
                },
            }).done(function (data, textStatus, jqXHR) {
                $('#encolser_content').html('');
                $('#encolser_content').html(data);
                $("#encolser_modal").modal();
            }).fail(function (jqXHR, textStatus, errorThrown) {
                $('#encolser_content').html('');
                alert(sessiontimeoutmessage);
                window.location.href = base_url;
            });
        }


        $('#filter').click(function () {
            loadDatatable();

        });

        $('#reset').click(function () {
            $('#application_type').val('');
            $('#gp_ward_code').val('');

            $('#rural_urbanid').val('');
            $('#urban_body_code').val('');
            $('#block_ulb_code').val('');

            loadDatatable();
        });

        function closeError(divId) {
            $('#' + divId).hide();
        }

        function loadDatatable() {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            var dataTable = $('#example').DataTable({
                //dom: 'Bfrtip',
                dom: 'Blfrtip',
                "paging": true,
                "pageLength": 20,
                "lengthMenu": [
                    [10, 20, 50, 80, 120],
                    [10, 20, 50, 80, 120]
                ],
                "serverSide": true,
                "deferRender": true,
                "processing": true,
                "bRetrieve": true,
                "ordering": false,
                "searching": true,
                "language": {
                    "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
                },
                ajax: {
                    url: "{{ url('faulty-lb-draft-list') }}",
                    type: "GET",
                    data: function (d) {
                        //alert($designation_id);
                        d.ds_phase = $("#ds_phase").val(),
                            d._token = "{{ csrf_token() }}",
                            d.rural_urbanid = $('#rural_urbanid').val(),
                            d.urban_body_code = $('#urban_body_code').val(),
                            d.block_ulb_code = $('#block_ulb_code').val(),
                            d.gp_ward_code = $('#gp_ward_code').val(),
                            d.application_type = $('#application_type').val()

                    },
                    error: function (ex) {

                        console.log($designation_id);
                        //alert(sessiontimeoutmessage);
                        //window.location.href=base_url;
                    }
                },
                columns: [{
                    "data": "application_id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "mobile_no"
                },
                {
                    "data": "Action"
                },

                    @if ($designation_id == 'Approver')
                                    {
                            "data": "check"
                        },
                    @endif
                    ],

                buttons: [{
                    extend: 'pdf',
                    footer: true,
                    pageSize: 'A4',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],

                    }
                },
                {
                    extend: 'print',
                    footer: true,
                    pageSize: 'A4',
                    //orientation: 'landscape',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3],
                        stripHtml: false,
                    }
                },
                {
                    extend: 'excel',
                    footer: true,
                    pageSize: 'A4',
                    //orientation: 'landscape',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3],
                        stripHtml: false,
                    }
                },
                {
                    extend: 'copy',
                    footer: true,
                    pageSize: 'A4',
                    //orientation: 'landscape',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3],
                        stripHtml: false,
                    }
                },
                {
                    extend: 'csv',
                    footer: true,
                    pageSize: 'A4',
                    //orientation: 'landscape',
                    pageMargins: [40, 60, 40, 60],
                    exportOptions: {
                        columns: [0, 1, 2, 3],
                        stripHtml: false,
                    }
                },
                    //'pdf','excel','csv','print','copy'
                ]
            });
        }


        $('#get_excel').click(function () {

            // var error_application_type = '';

            // if ($.trim($('#application_type').val()).length == 0) {
            //     error_application_type = 'Application Type is required';
            //     $('#error_application_type').text(error_application_type);
            // } else {
            //     error_application_type = '';
            //     $('#error_application_type').text(error_application_type);
            // }




            // var search_option = $('#search_for').val();
            var application_type = $('#application_type').val();
            //alert(application_type);
            var token = "{{ csrf_token() }}";
            var data = {
                '_token': token,
                application_type: application_type
            };
            redirectPost('getExcelfaulty', data);

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
                $.each(msg, function (key, value) {
                    $("#" + divid).find("ul").append('<li>' + value + '</li>');
                });
            } else {
                $("#" + divid).find("ul").append('<li>' + msg + '</li>');
            }
        }

        function closeError(divId) {
            $('#' + divId).hide();
        }




        $(document).on('click', '.opreation_type', function () {
            //alert(111);
            if ($(this).val() == 'T' || $(this).val() == 'R') {
                $('#div_rejection').show();
                if ($(this).val() == 'T')
                    $("#verifyReject").html("Revert");
                else if ($(this).val() == 'R')
                    $("#verifyReject").html("Reject");
            } else {
                $("#verifyReject").html("Approve");
                $('#div_rejection').hide();
                $("#reject_cause").val('');
            }
        });

        $(document).on('click', '#verifyReject', function () {
            //alert(111);

            var scheme_id = $('#scheme_id').val();
            var reject_cause = $('#reject_cause').val();
            var opreation_type = $('#opreation_type').val();
            var accept_reject_comments = $('#accept_reject_comments').val();
            var is_bulk = $('#is_bulk').val();
            //alert(opreation_type);
            // return false;
            var applicantId = $('#applicantId').val();
            var valid = 1;
            if (opreation_type == 'R' || opreation_type == 'T') {
                var valid = 0;
                if (reject_cause != '') {
                    var valid = 1;

                } else {
                    $.alert({
                        title: 'Error!!',
                        type: 'red',
                        icon: 'fa fa-warning',
                        content: '<strong>Please Select Cause</strong>',
                    });
                    return false;
                }

            }
            if (valid == 1) {
                // alert(555);
                // return false;

                $("#verifyReject").hide();
                var id = $('#id').val();
                var ds_phase = $('#ds_phase').val();
                var url = "{{ url('verifyDatafaultymigrate') }}";


                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        scheme_id: scheme_id,
                        reject_cause: reject_cause,
                        opreation_type: opreation_type,
                        accept_reject_comments: accept_reject_comments,
                        id: id,
                        is_bulk: is_bulk,
                        applicantId: applicantId,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {
                        // alert(data.return_msg);
                        //return false;
                        if (data.return_status == 1) {

                            loadDatatable();
                            //dataTable.ajax.reload(null, false);
                            $('.ben_view_modal').modal('hide');
                            //alert(data.return_msg);
                            // dataTable.ajax.reload(null, false);
                            $.confirm({
                                title: 'Success',
                                type: 'green',
                                icon: 'fa fa-check',
                                content: data.return_msg,
                                buttons: {
                                    Ok: function () {

                                        $("#submitting").hide();
                                        $("#verifyReject").show();
                                        $("html, body").animate({
                                            scrollTop: 0
                                        }, "slow");
                                    }
                                }
                            });
                            //printMsg(data.return_msg,'1','errorDivMain');



                        } else {
                            $("#submitting").hide();
                            $("#verifyReject").show();
                            $('#errorDiv').animate({
                                scrollTop: 0
                            }, 'slow');
                            alert('Error Occur .. Please try later...');
                            window.location.href = base_url;


                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $.confirm({
                            title: 'Error',
                            type: 'red',
                            icon: 'fa fa-warning',
                            content: 'Error Occur .. Please try later...',
                            buttons: {
                                Ok: function () {
                                    location.reload();
                                }
                            }
                        });

                    }
                });

            }
        });


        $('#confirm').click(function () {
            $('#modalConfirm').modal();
        });

        function controlCheckBoxall() {
            //$("#confirm").show();
            var items = document.getElementsByName('approvalcheck[]');


            for (var i = 0; i < items.length; i++) {

                if (items[i].type == 'checkbox') {
                    var is_checked = items[i].checked
                    items[i].checked = !items[i].checked;
                    console.log(items[i].checked);

                    if (items[i].checked == true) {
                        $("#confirm").show();
                        document.getElementById('confirm').disabled = false;

                    } else {
                        $("#confirm").hide();
                        document.getElementById('confirm').disabled = true;

                    }

                } else {
                    $("#confirm").hide();
                    document.getElementById('confirm').disabled = true;
                }
            }

        }


        $('#confirm_yes').on('click', function () {
            $("#confirm_yes").hide();
            $("#submittingapprove").show();
            $("#register_form").submit();


        });

        function controlCheckBox() {
            //console.log('ok');
            var anyBoxesChecked = false;
            $(' input[type="checkbox"]').each(function () {
                if ($(this).is(":checked")) {
                    anyBoxesChecked = true;
                }
            });
            if (anyBoxesChecked == true) {
                $("#confirm").show();
                document.getElementById('confirm').disabled = false;
            } else {
                $("#confirm").hide();
                document.getElementById('confirm').disabled = true;
            }
        }



        $(document).on('show.bs.collapse', '.panel-collapse', function () {


            //alert(555);

            var id = $(this).attr('id');
            var application_id = $('#fullForm #application_id').val();

            if (id == 'collapsePersonal') {
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
                $('#email_id').text('');
                $('#aadhar_no_encrypt').text('');
                $('#loader_img_personal').show();
                $.ajax({
                    type: 'post',
                    url: "{{ route('getfaultyBenViewPersonalData') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        benid: application_id
                    },
                    dataType: 'json',
                    success: function (response) {
                        //alert(response.personaldata.email);
                        $('#loader_img_personal').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        $('#sws_card_txt').text(response.personaldata.ss_card_no);
                        $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
                        $('#duare_sarkar_registration_no').text(response.personaldata
                            .duare_sarkar_registration_no);
                        $('#duare_sarkar_date').text(response.personaldata.formatted_duare_sarkar_date);
                        $('#ben_fullname').text(response.personaldata.ben_name);
                        $('#mobile_no').text(response.personaldata.mobile_no);
                        $('#email_id').text(response.personaldata.email);
                        $('#gender').text(response.personaldata.gender);
                        $('#dob').text(response.personaldata.formatted_dob);
                        $('#ben_age').text(response.personaldata.age_ason_01012021);
                        if (response.personaldata.father_mname !== undefined && response.personaldata
                            .father_mname !== null) {
                            var father_mname = response.personaldata.father_mname;
                        } else {
                            var father_mname = "";
                        }
                        if (response.personaldata.father_lname !== undefined && response.personaldata
                            .father_lname != null) {
                            var father_lname = response.personaldata.father_lname;
                        } else {
                            var father_lname = "";
                        }
                        $('#father_fullname').text(response.personaldata.father_fname + ' ' +
                            father_mname + ' ' + father_lname);
                        if (response.personaldata.mother_mname !== undefined && response.personaldata
                            .mother_mname != null) {
                            var mother_mname = response.personaldata.mother_mname;
                        } else {
                            var mother_mname = "";
                        }
                        if (response.personaldata.mother_lname !== undefined && response.personaldata
                            .mother_lname != null) {
                            var mother_lname = response.personaldata.mother_lname;
                        } else {
                            var mother_lname = "";
                        }
                        $('#mother_fullname').text(response.personaldata.mother_fname + ' ' +
                            mother_mname + ' ' + mother_lname);
                        $('#caste').text(response.personaldata.caste);
                        if (response.personaldata.caste == 'SC' || response.personaldata.caste ==
                            'ST') {
                            $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
                            $('.caste').show();
                        } else {
                            $('.caste').hide();
                        }


                        if (response.personaldata.spouse_fname !== undefined && response.personaldata
                            .spouse_fname !== null) {
                            var spouse_fname = response.personaldata.spouse_fname;
                        } else {
                            var spouse_fname = "";
                        }
                        if (response.personaldata.spouse_mname !== undefined && response.personaldata
                            .spouse_mname !== null) {
                            //console.log((response.personaldata.ben_mname);
                            var spouse_mname = response.personaldata.spouse_mname;
                        } else {
                            var spouse_mname = "";
                        }
                        if (response.personaldata.spouse_lname !== undefined && response.personaldata
                            .spouse_lname !== null) {
                            var spouse_lname = response.personaldata.spouse_lname;
                        } else {
                            var spouse_lname = "";
                        }
                        $('#spouse_name').text(spouse_fname + ' ' + spouse_mname + ' ' + spouse_lname);


                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#loader_img_personal').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                });
            } else if (id == 'collapseContact') {
                $('#loader_img_contact').show();
                $('#dist_name').text('');
                $('#block_ulb_name').text('');
                $('#gp_ward_name').text('');
                $('#village_town_city').text('');
                $('#police_station').text('');
                $('#post_office').text('');
                $('#pincode').text('');
                $.ajax({
                    type: 'post',
                    url: "{{ route('getfaultyBenViewContactData') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        benid: application_id
                    },
                    dataType: 'json',
                    success: function (response) {


                        //console.log(response.contactdata);
                        $('#loader_img_contact').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
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
                        $('#loader_img_contact').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                });
            } else if (id == 'collapseBank') {
                $('#loader_img_bank').show();
                $('#bank_name').text('');
                $('#branch_name').text('');
                $('#bank_ifsc').text('');
                $('#bank_code').text('');
                $.ajax({
                    type: 'post',
                    url: "{{ route('getfaultyBenViewBankData') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        benid: application_id
                    },
                    dataType: 'json',
                    success: function (response) {

                        //alert(11);
                        $('#loader_img_bank').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        $('#bank_name').text(response.bankdata.bank_name);
                        $('#branch_name').text(response.bankdata.branch_name);
                        $('#bank_ifsc').text(response.bankdata.bank_ifsc);
                        $('#bank_code').text(response.bankdata.bank_code);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#loader_img_bank').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                });
            } else if (id == 'collapseEncloser') {
                $('#loader_img_encolser').show();
                $("#enCloserTable tbody").empty();
                $.ajax({
                    type: 'post',
                    url: "{{ route('getfaultyBenViewEncloserData') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        benid: application_id
                    },
                    dataType: 'json',
                    success: function (response) {
                        $('#loader_img_encolser').hide();
                        //console.log(response.html);
                        $('.ben_view_button').removeAttr('disabled', true);
                        $("#enCloserTable tbody").html(response.html);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#loader_img_encolser').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                });
            }
            $(this).siblings('.panel-heading').addClass('active');

        });

        $(document).on('click', '.ben_view_button', function () {
            var benid = $(this).val();
            benViewButtonModal(benid);
        });

        function benViewButtonModal(benid) {
            $('#loader_img_personal').show();
            $('#loader_img_contact').hide();
            $('#loader_img_bank').hide();
            $('#loader_img_encolser').hide();

            $(".singleInfo").show();
            $('.ben_view_button').attr('disabled', true);

            $('#fullForm #application_id').val(benid);
            $('.applicant_id_modal').html(benid);
            $("#collapseContact").collapse('hide');
            $("#collapseBank").collapse('hide');
            $("#collapseEncloser").collapse('hide');
            $('#duare_sarkar_registration_no').text('');
            $('#duare_sarkar_date').text('');
            $.ajax({
                type: 'post',
                url: "{{ route('getfaultyBenViewPersonalData') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    benid: benid
                },

                dataType: 'json',
                success: function (response) {
                    $('#loader_img_personal').hide();
                    $('.ben_view_button').removeAttr('disabled', true);
                    $('#sws_card_txt').text(response.personaldata.ss_card_no);
                    $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
                    $('#duare_sarkar_registration_no').text(response.personaldata
                        .duare_sarkar_registration_no);
                    $('#duare_sarkar_date').text(response.personaldata.formatted_duare_sarkar_date);
                    $('#ben_fullname').text(response.personaldata.ben_name);
                    $('#mobile_no').text(response.personaldata.mobile_no);
                    $('#email_id').text(response.personaldata.email);
                    $('#gender').text(response.personaldata.gender);
                    $('#dob').text(response.personaldata.formatted_dob);
                    $('#ben_age').text(response.personaldata.age_ason_01012021);
                    $('#father_fullname').text(response.personaldata.father_fname + ' ' + response
                        .personaldata.father_mname + ' ' + response.personaldata.father_lname);
                    $('#mother_fullname').text(response.personaldata.mother_fname + ' ' + response
                        .personaldata.mother_mname + ' ' + response.personaldata.mother_lname);
                    $('#caste').text(response.personaldata.caste);
                    if (response.personaldata.caste == 'SC' || response.personaldata.caste == 'ST') {
                        $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
                        $('.caste').show();
                    } else {
                        $('.caste').hide();
                    }

                    $('#spouse_name').text(response.personaldata.spouse_fname + ' ' + response
                        .personaldata.spouse_mname + ' ' + response.personaldata.spouse_lname);

                    $('#fullForm #id').val(response.benid);


                },
                complete: function () {

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loader_img_personal').hide();
                    $('.ben_view_button').removeAttr('disabled', true);
                    alert(sessiontimeoutmessage);
                    window.location.href = base_url;
                }
            });
            $('.ben_view_modal').modal('show');
        }



        $('.showhideAadhar').click(function () {
            var ButtonText = $(this).text();
            if (ButtonText == 'Show Original Aadhaar') {
                $("#aadhar_no_encrypt").hide();
                var applicant_id_modal = $(".applicant_id_modal").text();
                $("#aadhar_no_original").show();
                $('#aadhar_no_original').html(
                    '<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');

                $.ajax({
                    type: 'post',
                    url: "{{ route('getfaultyBenViewAadharData') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        benid: applicant_id_modal
                    },
                    dataType: 'json',
                    success: function (response) {
                        // alert(response.aadhar_no);
                        $('#aadhar_no_original').html('');
                        $('#aadhar_no_original').html(response.aadhar_no);
                        $("#show_hide_aadhar").text('Show Encrypted Aadhaar');
                        $("#aadhar_no_original").show();

                    },
                    complete: function () {
                        //$('#aadhar_no_original').html('');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#aadhar_no_original').html('');
                        $('.ben_view_button').removeAttr('disabled', true);
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                });
            } else if (ButtonText == 'Show Encrypted Aadhaar') {
                $(this).text('Show Original Aadhaar');
                $("#aadhar_no_encrypt").show();
                $("#aadhar_no_original").hide();
            }
        });
        $('#encolser_modal').on('hidden.bs.modal', function (e) {
            $('.ben_view_modal').css({
                'overflow': 'auto',
            });
            //$(".ben_view_modal").animate({ scrollTop: 0 }, "slow");
        });
    </script>
@endpush