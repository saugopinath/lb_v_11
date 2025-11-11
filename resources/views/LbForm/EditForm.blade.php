@extends('layouts.app-template')
@section('content')

<style>
  .required-field::after {
    content: " *";
    color: red;
  }

  .txt_sucess {
    color: #28a745;
  }

  .txt_danger {
    color: #dc3545;
  }

  .section1 {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
  }

  .color1 {
    background-color: #f8f9fa;
    padding: 10px;
    margin-bottom: 15px;
  }

  .modal_field_name {
    font-weight: bold;
    margin-bottom: 5px;
  }

  .modal_field_value {
    margin-bottom: 10px;
  }

  .imageSize {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
  }

  .progress {
    height: 20px;
    margin-bottom: 10px;
  }
  .nav-text{
    font-size: 18px;
    font-weight: bold;
    color: #343a40;
  }
  .nav-item{

  }
.active_tab1 { 
  background-color: #fcfcfc; 
  color: #333; 
  font-weight: 600; 
} 
.active_tab2 { 
  background-color: #e3fcfc; 
  color: #333; 
  cursor: not-allowed; 
} 
.inactive_tab1 { 
  background-color: #e3fcfc; 
  color: #333; 
  cursor: not-allowed !important; 
  }
</style>


<!-- Content Wrapper. Contains page content -->
<!-- <div class="content-wrapper"> -->
<!-- Content Header (Page header) -->


<!-- Main content -->
  <div class="container-fluid">
    <div class="row">
      <!-- left column -->
      <div class="col-md-12 mt-4">
        <!-- general form elements -->
        <div class="card card-success">
          <div class="card-header">
            <h3 class="card-title"><b>Government of West Bengal Lakshmir Bhandar Scheme</b></h3>
          </div>

          <div class="card-body">
            @if (!empty($application_id))
            <div class="alert alert-info">
              <strong> Application ID: {{$application_id}}</strong>
            </div>
            <div id="forEditInfo">
              @if($row->life_certificate_checked==1)
              @if($row->life_certificate_pass==1)
              <strong><i class="fas fa-check text-success"></i><span class="txt_sucess"> Checked Bio-auth from Kadhya Sathi - Passed @if(!empty($row->last_biometric))({{date('d/m/Y',strtotime($row->last_biometric))}})@endif</span></strong> <br />
              @else
              <strong><i class="fas fa-times text-danger"></i><span class="txt_danger"> Checked Bio-auth from Kadhya Sathi:Not Passed @if(!empty($row->life_certificate_lastdatetime)) as on {{date('d/m/Y',strtotime($row->life_certificate_lastdatetime))}} @endif</span></strong> <br />
              @endif
              @endif

              @if(($row->caste=='SC' || $row->caste=='ST') && $row->caste_certificate_checked==1)
              @if($row->caste_matched_with_certificate_no==1)
              <strong><i class="fas fa-check text-success"></i><span class="txt_sucess"> Checked Caste Certificate Status:Passed @if(!empty($row->caste_certificate_check_lastdatetime)) as on {{date('d/m/Y',strtotime($row->caste_certificate_check_lastdatetime))}}@endif</span></strong> <br />
              @else
              <strong><i class="fas fa-times text-danger"></i><span class="txt_danger"> Checked Caste Certificate Status:Not Passed @if(!empty($row->caste_certificate_check_lastdatetime)) as on {{date('d/m/Y',strtotime($row->caste_certificate_check_lastdatetime))}}@endif</span></strong> <br />
              @endif
              @endif

              @if($row->aadhaar_no_checked ==1)
              @if($row->aadhaar_no_checked_pass==1)
              <span class="text-success" style="font-size: 14px; font-weight: bold;"> <i class="fas fa-check"></i> Checked Aadhaar Card Demographic Status: Passed as on @if(!empty($row->aadhaar_no_checked_lastdatetime)) {{date('d-m-Y',strtotime($row->aadhaar_no_checked_lastdatetime))}} @endif
                @if(($row->dob_is_match_kh!=1))
                @if(!empty($row->dob_kh))
                <span class="text-warning" style="font-size: 14px; font-weight: bold;">
                  But Applicant DOB @if(!empty($row->dob)) ({{date('d-m-Y',strtotime($row->dob))}})@endif not match with DOB ({{date('d-m-Y',strtotime($row->dob_kh))}}) which is received from Khadya Sathi Api.
                </span>
                @endif
                @endif
              </span>
              @elseif($row->aadhaar_no_checked_pass==2)
              <span class="text-warning" style="font-size: 14px; font-weight: bold;"> <i class="fas fa-times"></i> Checked Aadhaar Card Demographic Status:@if(!empty($row->aadhaar_no_validation_msg)) {{$row->aadhaar_no_validation_msg}} @endif as on @if(!empty($row->aadhaar_no_checked_lastdatetime)) {{date('d-m-Y',strtotime($row->aadhaar_no_checked_lastdatetime))}}@endif</span>
              @else
              <span class="text-danger" style="font-size: 14px; font-weight: bold;"> <i class="fas fa-times"></i> Checked Aadhaar Card Demographic Status: Not Passed as on @if(!empty($row->aadhaar_no_checked_lastdatetime)) {{date('d-m-Y',strtotime($row->aadhaar_no_checked_lastdatetime))}}@endif</span>
              @endif
              @endif
            </div>
            @endif

            @if ( ($message = Session::get('success')))
            <div class="alert alert-danger alert-block">
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              <strong>{{ $message }} </strong>
            </div>
            @endif

            @if ( ($message = Session::get('error')))
            <div class="alert alert-danger alert-block">
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              <strong>{{ $message }}</strong>
            </div>
            @endif

            @if(count($errors) > 0)
            <div class="alert alert-danger alert-block">
              <ul>
                @foreach($errors as $error)
                <li><strong> {{ $error }}</strong></li>
                @endforeach
              </ul>
            </div>
            @endif

           <div class="alert print-error-msg position-relative" style="display:none;" id="errorDiv">
              <button type="button" class="btn-close position-absolute top-2 end-0 m-2" 
                      aria-label="Close" onclick="closeError('errorDiv')"></button>
              <ul class="mb-0 mt-1"></ul>
            </div>
            
            <div id="life_certificate_pass" style="display:none;">
              <strong><i class="fas fa-check text-success"></i><span class="text-success">Checked Bio-auth from Kadhya Sathi - Passed (<span id="life_certificate_pass_text"></span>)</span></strong>
            </div>

            <div id="life_certificate_n_pass" style="display:none;">
              <strong><i class="fas fa-times text-danger"></i><span class="text-danger">Checked Bio-auth from Kadhya Sathi- Not Passed as on <span id="life_certificate_n_pass_text"></span></span></strong>
            </div>

            <div id="cast_certificate_pass" style="display:none;">
              <strong><i class="fas fa-check text-success"></i><span class="text-success">Checked Caste Certificate Status:Passed as on <span id="cast_certificate_pass_text"></span></span></strong>
            </div>

            <div id="cast_certificate_n_pass" style="display:none;">
              <strong><i class="fas fa-times text-danger"></i><span class="text-danger">Checked Caste Certificate Status:Not Passed as on <span id="cast_certificate_n_pass_text"></span></span></strong>
            </div>

            <div id="aadhaar_no_checked_pass" style="display:none;">
              <strong><i class="fas fa-check text-success"></i><span class="text-success">Checked Aadhaar Card Demographic Status:Passed as on <span id="aadhaar_no_checked_pass_text"></span>
                  <span class="text-warning">
                    <span id="aadhar_message"> But Applicant DOB not match with DOB (<span id="aadhaar_no_dob_pass_text"></span>) which is received from Khadya Sathi API.
                    </span>
                  </span></span></strong>
            </div>
            <div id="aadhaar_no_checked_n_pass" style="display:none;">
              <strong><i class="fas fa-times text-danger"></i><span class="text-danger">Checked Aadhaar Card Demographic Status:Not Passed as on <span id="aadhaar_no_checked_n_pass_text"></span></span></strong>
            </div>
            <br />
          </div>

          <!-- Nav tabs -->
          <ul class="nav nav-tabs card-header-tabs pl-3 pr-4 gap-1" id="myTab" role="tablist">
            <li class="nav-item" role="presentation" id="id_1">
              <button class="nav-link inactive_tab1" id="list_personal_details" type="button" role="tab"  aria-selected="true" onclick="return tab_highlight(1)"><b class="nav-text">Personal Details</b></button>
            </li>
            <li class="nav-item" role="presentation" id="id_2">
              <button class="nav-link active_tab2" id="list_contact_details" type="button" role="tab"  aria-selected="false" onclick="return tab_highlight(2)"><b class="nav-text">Contact Details</b></button>
            </li>
            <li class="nav-item" role="presentation" id="id_3">
              <button class="nav-link inactive_tab1" id="list_bank_details" data-bs-target="#bank" type="button" role="tab"  aria-selected="false" onclick="return tab_highlight(3)"><b class="nav-text">Bank Account Details</b></button>
            </li>
            <li class="nav-item" role="presentation" id="id_4">
              <button class="nav-link inactive_tab1" id="list_experience_details" type="button" role="tab"  aria-selected="false" onclick="return tab_highlight(4)"><b class="nav-text">Enclosure List (Self Attested)</b></button>
            </li>
            <li class="nav-item" role="presentation" id="id_5">
              <button class="nav-link inactive_tab1" id="list_decl_details" type="button" role="tab" aria-controls="declaration"  onclick="return tab_highlight(5)"><b class="nav-text">Self Declaration</b></button>
            </li>
          </ul>

          <!-- Tab panes -->
          <div class="tab-content p-1">
            <form id="commonfield">
              <input type="hidden" name="max_tab_code" id="max_tab_code" value="{{ $tab_code }}">
              <input type="hidden" name="application_id" id="application_id" value="{{ $application_id }}">
              <input type="hidden" name="status" id="status" value="{{ $status }}">
            </form>

            <!-- Personal Details Tab -->
            <div class="tab-pane" id="personal_details" role="tabpanel" aria-labelledby="personal-tab">
              <div class="card card-info">
                <div class="card-header">
                  <h4><b>Personal Details</b></h4>
                </div>
                <div class="card-body">
                  <form name="personal" id="personal_form" method="get" action="{{url('ajax_personal_entry_wtSws')}}">
                    <input type="hidden" name="personalCount" id="personalCount" value="{{$personalCount }}">
                    <input type="hidden" name="type" id="type" value="{{$type}}">
                    <input type="hidden" name="grievance_id" id="grievance_id" value="{{$grievance_id}}">

                    <div class="row mb-3">
                      <div class="form-group col-md-3">
                        <label class="required-field">Aadhaar Number</label>
                        <input type="text" name="aadhar_no" id="aadhar_no" class="form-control NumOnly"
                          placeholder="Aadhar No." maxlength="12" value="{{isset($row->aadhar_no)?$row->aadhar_no:''}}" tabindex="15" />
                      </div>
                        <span id="error_aadhar_no" class="text-danger"></span>
                      <div class="form-group col-md-3 d-flex align-items-end">
                        <button type="button" id="aadhar_dup_check" class="btn btn-danger {{isset($row->aadhar_no)?'':'disabled'}}">Check Avialibility</button>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-12">
                        <label class="required-field"><b>Application Type: </b></label>
                      </div>
                      <div class="form-group col-md-4">
                        <select class="form-control " name="entry_type" id="entry_type">
                          
                           
                          @if (!empty($application_id))
                          @if($row->entry_type==1 || is_null($row->entry_type))
                          <option value="1" selected>Normal Form</option>
                          @else
                          <option value="2" selected>Form through {{$ds_phase_text}} camp</option>
                          @endif
                          @else
                          @if($scheme_details->allow_ds_entry==1)
                          <option value="2" selected>Form through {{$ds_phase_text}} camp</option>
                          @endif
                          @if($scheme_details->allow_normal_entry==1)
                          <option value="1">Normal Form</option>
                          @endif
                          @endif
                        </select>
                        <span id="error_entry_type" class="text-danger"></span>
                      </div>
                    </div>

                    @if($status == 1)
                    <div class="row mb-3">
                      <div class="form-group col-md-12">
                        <label class="required-field"><b>Application Date: </b></label>
                      </div>
                      <div class="form-group col-md-4">
                        <input type="date" name="application_date" id="application_date" class="form-control"
                          placeholder="{{$ds_phase_text}} Date" max="<?php echo date("Y-m-d"); ?>" value="{{isset($row->application_date)?$row->application_date:''}}" tabindex="10" />
                        <span id="error_application_date" class="text-danger"></span>
                      </div>
                    </div>
                    @endif

                    <div class="row mb-3 duareSarkar" style="display:none;">
                      <div class="form-group col-md-4">
                        <label class="required-field">{{$ds_phase_text}} Registration no.</label>
                        <input type="text" name="duare_sarkar_registration_no" id="duare_sarkar_registration_no" class="form-control NumOnly"
                          placeholder="{{$ds_phase_text}} Registration no." maxlength="25" value="{{isset($row->duare_sarkar_registration_no)?$row->duare_sarkar_registration_no:''}}" tabindex="5" />
                        <span id="error_duare_sarkar_registration_no" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="required-field">{{$ds_phase_text}} Date:</label>
                        <input type="date" name="duare_sarkar_date" id="duare_sarkar_date" class="form-control"
                          placeholder="{{$ds_phase_text}} Date" max="<?php echo date("Y-m-d"); ?>" value="{{isset($row->duare_sarkar_date)?$row->duare_sarkar_date:''}}" tabindex="10" />
                        <span id="error_duare_sarkar_date" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-4">
                        <label class="required-field">Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-control txtOnly"
                          placeholder="Name" maxlength="200" value="{{isset($row->ben_fname)?$row->ben_fname:''}}" tabindex="5" />
                        <span id="error_first_name" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-4">
                        <label class="required-field">Mobile Number</label>
                        <input type="text" id="mobile_no" name="mobile_no" class="form-control NumOnly"
                          placeholder="Mobile No" maxlength="10" value="{{isset($row->mobile_no)?$row->mobile_no:''}}" tabindex="185">
                        <span id="error_mobile_no" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="">Email Id </label>
                        <input type="text" id="email" name="email" class="form-control" placeholder="Email Id."
                          maxlength="200" value="{{isset($row->email)?$row->email:''}}" tabindex="190">
                        <span id="error_email" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-4">
                        <label class="required-field">Gender</label>
                        <select class="form-control " name="gender" id="gender" tabindex="20">
                          @foreach(Config::get('constants.gender') as $key=>$val)
                          @if($key=='Female')
                          <option value="{{$key}}" selected>{{$val}}</option>
                          @endif
                          @endforeach
                        </select>
                        <span id="error_gender" class="text-danger"></span>
                      </div>

                      <div class="form-group col-md-4">
                        <label class="required-field">Date of Birth</label>
                        <input type="date" name="dob" id="dob" class="form-control" tabindex="25"
                          value="{{isset($row->dob)?$row->dob:''}}" max="{{$max_dob}}" min="{{$min_dob}}" />
                        <span id="error_dob" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="">Age<span style=""> (as on {{$dob_base_date}})</span></label>
                        <input type="hidden" name="hidden_age" id="hidden_age" value="{{isset($row->ben_age)?$row->ben_age:''}}">
                        <input type="text" readonly name="txt_age" id="txt_age" class="form-control NumOnly" placeholder="Age"
                          value="{{isset($row->ben_age)?$row->ben_age:''}}" maxlength="3" tabindex="30" />
                        <span id="error_txt_age" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-12">
                        <label class="">Father's Name</label>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="required-field">First Name</label>
                        <input type="text" name="father_first_name" id="father_first_name"
                          class="form-control txtOnly" placeholder="First Name" maxlength="200"
                          value="{{isset($row->father_fname)?$row->father_fname:''}}" tabindex="35" />
                        <span id="error_father_first_name" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label>Middle Name</label>
                        <input type="text" name="father_middle_name" id="father_middle_name"
                          class="form-control txtOnly" placeholder="Middle Name" maxlength="100"
                          value="{{isset($row->father_mname)?$row->father_mname:''}}" tabindex="40" />
                        <span id="error_father_middle_name" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="">Last Name</label>
                        <input type="text" name="father_last_name" id="father_last_name" class="form-control txtOnly"
                          placeholder="Last Name" maxlength="200" value="{{isset($row->father_lname)?$row->father_lname:''}}"
                          tabindex="45" />
                        <span id="error_father_last_name" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-12">
                        <label class="">Mother's Name</label>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="required-field">First Name</label>
                        <input type="text" name="mother_first_name" id="mother_first_name"
                          class="form-control txtOnly" placeholder="First Name" maxlength="200"
                          value="{{isset($row->mother_fname)?$row->mother_fname:''}}" tabindex="50" />
                        <span id="error_mother_first_name" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label>Middle Name</label>
                        <input type="text" name="mother_middle_name" id="mother_middle_name"
                          class="form-control txtOnly" placeholder="Middle Name" maxlength="100"
                          value="{{isset($row->mother_mname)?$row->mother_mname:''}}" tabindex="55" />
                        <span id="error_mother_middle_name" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="">Last Name</label>
                        <input type="text" name="mother_last_name" id="mother_last_name" class="form-control txtOnly"
                          placeholder="Last Name" maxlength="200" value="{{isset($row->mother_lname)?$row->mother_lname:''}}"
                          tabindex="60" />
                        <span id="error_mother_last_name" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-12">
                        <label class="">Spouse Name (if applicable)</label>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="">First Name</label>
                        <input type="text" name="spouse_first_name" id="spouse_first_name"
                          class="form-control txtOnly" placeholder="First Name" maxlength="200"
                          value="{{isset($row->spouse_fname)?$row->spouse_fname:''}}" tabindex="90" />
                        <span id="error_spouse_first_name" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label>Middle Name</label>
                        <input type="text" name="spouse_middle_name" id="spouse_middle_name"
                          class="form-control txtOnly" placeholder="Middle Name" maxlength="100"
                          value="{{isset($row->spouse_mname)?$row->spouse_mname:''}}" tabindex="95" />
                        <span id="error_spouse_middle_name" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="">Last Name</label>
                        <input type="text" name="spouse_last_name" id="spouse_last_name"
                          class="form-control txtOnly" placeholder="Last Name" maxlength="200"
                          value="{{isset($row->spouse_lname)?$row->spouse_lname:''}}" tabindex="100" />
                        <span id="error_spouse_last_name" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-4">
                        <label class="required-field">Caste</label>
                        <select class="form-control" name="caste_category" id="caste_category" tabindex="70">
                          <option value="">--Select--</option>
                          @foreach(Config::get('constants.caste_lb') as $key=>$val)
                          <option value="{{$key}}" @if(isset($row->caste)) @if(trim($row->caste)==$key) selected @endif @endif>{{$val}}</option>
                          @endforeach
                        </select>
                        <span id="error_caste_category" class="text-danger"></span>
                      </div>

                      <div class="form-group col-md-4" id="caste_certificate_no_section">
                        <label class="required-field">SC/ST Certificate No.</label>
                        <input type="text" name="caste_certificate_no" id="caste_certificate_no" class="form-control"
                          placeholder="SC/ST Certificate No." maxlength="200" value="{{isset($row->caste_certificate_no)?$row->caste_certificate_no:''}}"
                          tabindex="117" />
                        <span id="error_caste_certificate_no" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="col-md-12 text-center">
                      <button type="button" name="btn_personal_details" id="btn_personal_details"
                        class="btn btn-success btn-lg {{isset($row->aadhar_no)?'':'disabled'}}">Save & Next</button>
                      <br /><br />
                      <span style="font-size:20px;" class="badge bg-info" id="dup_aadhaar_label" style="{{isset($row->aadhar_no)?'display:none':''}}">Save & Next will be Available after availability of Aadhaar</span>
                      <img style="display:none;" src="{{ asset('images/ZKZg.gif')}}" id="btn_personal_details_loader" width="150px"
                        height="150px">
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Contact Details Tab -->
            <div class="tab-pane" id="contact_details" role="tabpanel" aria-labelledby="contact-tab">
              <div class="card card-info">
                <div class="card-header">
                  <h4><b>Contact Details</b></h4>
                </div>
                <div class="card-body">
                  <form name="contact" id="contact" method="post" action="{{url('ajax_contact_entry_wtSws')}}">
                    <input type="hidden" name="contactCount" id="contactCount" value="{{ $contactCount }}">

                    <div class="row mb-3">
                      <div class="form-group col-md-12 ajax_loader" style="display:none;">
                        <img src="{{asset('images/ZKZg.gif')}}" />
                      </div>
                      <div class="form-group col-md-4">
                        <label class="required-field">State</label>
                        <input type="text" id="state" name="state" class="form-control" placeholder=""
                          value="WEST BENGAL" readonly="true" tabindex="130">
                        <span id="error_state" class="text-danger"></span>
                      </div>

                      <div class="form-group col-md-4">
                        <label class="required-field">District</label>
                        <select name="district" id="district" class="form-control"
                          tabindex="135">
                          <option value="">--Select --</option>
                          @foreach ($district_list as $dist)
                          <option value="{{$dist->district_code}}" @if(isset($row->dist_code)) @if($row->dist_code==$dist->district_code) selected @endif @endif >{{trim($dist->district_name)}}</option>
                          @endforeach
                        </select>
                        <span id="error_district" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-4">
                        <label class="required-field">Police Station</label>
                        <input type="text" id="police_station" name="police_station"
                          class="form-control special-char" placeholder="Police Station" maxlength="200"
                          value="{{isset($row->police_station)?$row->police_station:''}}" tabindex="142">
                        <span id="error_police_station" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4" id="divUrbanCode">
                        <label class="required-field">Rural/ Urban</label>
                        <select name="urban_code" id="urban_code" class="form-control" tabindex="144">
                          <option value="">--Select --</option>
                          @foreach(Config::get('constants.rural_urban') as $key=>$val)
                          <option value="{{$key}}" @if(isset($row->rural_urban_id)) @if($row->rural_urban_id==$key) selected @endif @endif>{{$val}}</option>
                          @endforeach
                        </select>
                        <span id="error_urban_code" class="text-danger"></span>
                      </div>

                      <div class="form-group col-md-4" id="divBodyCode">
                        <label class="required-field">Block/Municipality/Corp.</label>
                        <select name="block" id="block" class="form-control"
                          tabindex="150">
                          <option value="">--Select --</option>
                          @if(count($block_ulb_list)>0)
                          @foreach ($block_ulb_list as $block)
                          <option value="{{$block->block_ulb_code}}" @if(isset($row->block_ulb_code)) @if($row->block_ulb_code==$block->block_ulb_code) selected @endif @endif>{{trim($block->block_ulb_name)}}</option>
                          @endforeach
                          @endif
                        </select>
                        <span id="error_block" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-4" id="divBodyCode">
                        <label class="required-field">GP/Ward No</label>
                        <select name="gp_ward" id="gp_ward" class="form-control"
                          tabindex="155">
                          <option value="">--Select --</option>
                          @if(count($gp_ward_list)>0)
                          @foreach ($gp_ward_list as $gp)
                          <option value="{{$gp->gp_ward_code}}" @if(isset($row->gp_ward_code)) @if($row->gp_ward_code==$gp->gp_ward_code) selected @endif @endif>{{trim($gp->gp_ward_name)}}</option>
                          @endforeach
                          @endif
                        </select>
                        <span id="error_gp_ward" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="required-field">Village/Town/City</label>
                        <input type="text" id="village" name="village" class="form-control special-char"
                          placeholder="Village/Town/City" maxlength="300" value="{{isset($row->village_town_city)?$row->village_town_city:''}}" tabindex="160">
                        <span id="error_village" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="">House / Premise No.</label>
                        <input type="text" id="house_premise_no" name="house_premise_no" class="form-control special-char"
                          placeholder="House / Premise No." maxlength="300" value="{{isset($row->house_premise_no)?$row->house_premise_no:''}}" tabindex="165">
                        <span id="error_house" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-4">
                        <label class="required-field">Post Office</label>
                        <input type="text" id="post_office" name="post_office" class="form-control special-char"
                          placeholder="Post Office" maxlength="300" value="{{isset($row->post_office)?$row->post_office:''}}" tabindex="170">
                        <span id="error_post_office" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-4">
                        <label class="required-field">Pin Code</label>
                        <input type="text" id="pin_code" name="pin_code" class="form-control NumOnly"
                          placeholder="Pin Code" maxlength="6" value="{{isset($row->pincode)?$row->pincode:''}}" tabindex="175">
                        <span id="error_pin_code" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="col-md-12 d-flex justify-content-between">
                      <div>
                        <button type="button" name="previous_btn_contact_details" id="previous_btn_contact_details"
                          class="btn btn-info btn-lg">Previous</button>
                      </div>
                      <div>
                        <button type="button" name="btn_contact_details" id="btn_contact_details"
                          class="btn btn-success btn-lg">Save & Next</button>
                        <img style="display:none;" src="{{ asset('images/ZKZg.gif')}}" id="btn_contact_details_loader" width="150px"
                          height="150px">
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Bank Details Tab -->
            <div class="tab-pane" id="bank_details" role="tabpanel" aria-labelledby="bank-tab">
              <div class="card card-info">
                <div class="card-header">
                  <h4><b>Bank Account Details</b></h4>
                </div>
                <div class="card-body">
                  <form name="bank" id="bank" method="post" action="{{url('ajax_bank_entry_wtSws')}}">
                    <input type="hidden" name="bankCount" id="bankCount" value="{{ $bankCount }}">

                    <div class="row mb-3">
                      <div class="form-group col-md-6">
                        <label class="required-field">IFS Code</label>
                        <input type="text" name="bank_ifsc_code" id="bank_ifsc_code" class="form-control special-char"
                          autocomplete="off" placeholder="IFSC Code" onkeyup="this.value = this.value.toUpperCase();"
                          value="{{isset($row->bank_ifsc)?$row->bank_ifsc:''}}" maxlength='11' tabindex="200" />
                        <span id="error_bank_ifsc_code" class="text-danger"></span>
                      </div>

                      <div class="form-group col-md-6">
                        <label class="required-field">Bank Name</label>
                        <input type="text" name="name_of_bank" id="name_of_bank" class="form-control special-char"
                          placeholder="Bank Name" value="{{isset($row->bank_name)?$row->bank_name:''}}" maxlength="200" tabindex="205"
                          readonly />
                        <span id="error_name_of_bank" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-6">
                        <label class="required-field">Bank Branch Name</label>
                        <input type="text" name="bank_branch" id="bank_branch" class="form-control"
                          placeholder="Bank Branch Name" value="{{isset($row->branch_name)?$row->branch_name:''}}" maxlength="300" tabindex="210"
                          readonly />
                        <span id="error_bank_branch" class="text-danger"></span>
                      </div>

                      <div class="form-group col-md-6">
                        <label class="required-field">Bank Account Number</label>
                        <input type="password" name="bank_account_number" id="bank_account_number"
                          class="form-control NumOnly" placeholder="Bank Account No"
                          autocomplete="off" value="{{isset($row->bank_code)?$row->bank_code:''}}" maxlength='16' tabindex="215" />
                        <span id="error_bank_account_number" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="form-group col-md-6">
                        <label class="required-field">Confirm Bank Account Number</label>
                        <input type="text" name="confirm_bank_account_number" id="confirm_bank_account_number"
                          class="form-control NumOnly" placeholder="Confirm Bank Account No"
                          autocomplete="off" value="{{isset($row->bank_code)?$row->bank_code:''}}" maxlength='16' tabindex="215" />
                        <span id="error_confirm_bank_account_number" class="text-danger"></span>
                      </div>
                    </div>

                    <div class="col-md-12 d-flex justify-content-between">
                      <div>
                        <button type="button" name="previous_btn_bank_details" id="previous_btn_bank_details"
                          class="btn btn-info btn-lg">Previous</button>
                      </div>
                      <div>
                        <button type="button" name="btn_bank_details" id="btn_bank_details"
                          class="btn btn-success btn-lg">Save & Next</button>
                        <img style="display:none;" src="{{ asset('images/ZKZg.gif')}}" id="btn_bank_details_loader" width="150px"
                          height="150px">
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Enclosure List Tab -->
            <div class="tab-pane" id="experience_details" role="tabpanel" aria-labelledby="enclosure-tab">
              <div class="card card-info">
                <div class="card-header">
                  <h4><b>Enclosure List (Self Attested)</b></h4>
                </div>
                <div class="card-body">
                  @if(isset($encloser_list))
                  @foreach ($encloser_list as $doc_all)
                  <div class="form-group col-md-12 mb-3">
                    <label class="fileLable_{{$doc_all['id']}} {{$doc_all['required']==1?'required-field':''}}">{{ $doc_all['doc_name'] }}</label>
                    <div class="imageSize">(Image type must be {{ $doc_all['doc_type'] }} and image size max {{ $doc_all['doc_size_kb'] }}KB)</div>
                    <button type="button" id="doc_{{ $doc_all['id'] }}_{{ $doc_all['is_profile_pic'] }}" name="encolerModal" class="btn btn-info encloserModal btnEnc">Upload</button>
                    <span id="download_{{ $doc_all['id']}}" style="{{$doc_all['can_download']==1?'':'display:none'}}">
                      &nbsp;&nbsp;<button type="button" id="docDownload_{{ $doc_all['id'] }}_{{ $doc_all['is_profile_pic'] }}" class="btn btn-danger downloadEncloser btnEnc">Download</button>
                    </span>
                  </div>
                  @endforeach
                  @endif

                  <div class="col-md-12 d-flex justify-content-between">
                    <div>
                      <button type="button" name="previous_btn_experience_details"
                        id="previous_btn_experience_details" class="btn btn-info btn-lg">Previous</button>
                    </div>
                    <div>
                      <button type="button" name="btn_encloser_details" id="btn_encloser_details"
                        class="btn btn-success btn-lg">Next</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Self Declaration Tab -->
            <div class="tab-pane" id="decl_details" role="tabpanel" aria-labelledby="declaration-tab">
              <div class="card card-info">
                <div class="card-header">
                  <h4><b>Self Declaration</b></h4>
                </div>
                <div class="card-body">
                  <form name="declaration" id="declaration" method="post" action="{{url('ajax_declaration_entry_wtSws')}}">
                    <input type="hidden" name="otherCount" id="otherCount" value="{{ $otherCount }}">
                    {{ csrf_field() }}

                    <div class="row">
                      <div class="form-group col-md-12" tabindex="360">
                        <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" name="is_resident" id="is_resident" value="1" @if(isset($row->is_resident)) @if($row->is_resident==1) checked @endif @endif>
                          <label class="form-check-label" for="is_resident">
                            That I am a resident of West Bengal
                          </label>
                          <br />
                          <span id="error_is_resident" class="text-danger"></span>
                        </div>

                        <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" id="earn_monthly_remuneration" name="earn_monthly_remuneration" value="1" @if(isset($row->earn_monthly_remuneration)) @if($row->earn_monthly_remuneration==1) checked @endif @endif>
                          <label class="form-check-label" for="earn_monthly_remuneration">
                            That I do not earn any monthly remuneration from any regular Government job
                          </label>
                          <br />
                          <span id="error_earn_monthly_remuneration" class="text-danger"></span>
                        </div>

                        <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" id="info_genuine_decl" name="info_genuine_decl" value="1" @if(isset($row->info_genuine_decl)) @if($row->info_genuine_decl==1) checked @endif @endif>
                          <label class="form-check-label" for="info_genuine_decl">
                            That all the information and documents submitted by me are correct/ genuine. In case any of the
                            information/ document is found to be false, penal action shall be taken against me and the benefit will be
                            terminated.
                          </label>
                          <br />
                          <span id="error_info_genuine_decl" class="text-danger"></span>
                        </div>

                        <div class="form-check mb-3">
                          <input class="form-check-input" type="checkbox" id="av_status" name="av_status" value="TRUE" checked>
                          <label class="form-check-label" for="av_status">
                            I give consent to the use of the Aadhaar No.for authenticating my identity for social security pension (In case Aadhaar no. provided by the applicant).
                          </label>
                          <br />
                          <span id="error_info_genuine_decl" class="text-danger"></span>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-12 d-flex justify-content-between">
                      <div>
                        <button type="button" name="previous_btn_decl_details" id="previous_btn_decl_details"
                          class="btn btn-info btn-lg">Previous</button>
                      </div>
                      <div>
                        <button type="button" class="btn btn-success btn-lg" name="btn_submit_preview"
                          id="btn_submit_preview" value="Preview and Submit" data-bs-toggle="modal"
                          data-bs-target="#confirm-submit">Preview and Submit</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


<!-- Preview Modal -->
<div class="modal fade" id="confirm-submit" tabindex="-1" role="dialog" aria-labelledby="confirmSubmitModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title w-100 text-center">Confirm Submit</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h4 class="text-center mb-4">Are you sure you want to submit the following details?</h4>

        <div class="section1">
          <div class="row">
            <div class="col-md-3 text-center">
              <img src="{{ asset('images/Emblem_of_West_Bengal.png') }}" width="180px" height="200px">
            </div>
            <div class="col-md-6 text-center">
              <h2>Government of West Bengal</h2>
              <h2>Lakshmir Bhandar Scheme</h2>
            </div>
            <div class="col-md-3"></div>
          </div>
        </div>

        <!-- Personal Details Preview -->
        <div class="section1">
          <div class="row color1">
            <div class="col-md-12">
              <h2>Personal Details</h2>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="modal_field_name">Aadhaar No.:</div>
              <div class="modal_field_value" id="aadhar_no_modal"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="modal_field_name">{{$ds_phase_text}} Registration no.</div>
              <div class="modal_field_value" id="duare_sarkar_registration_no_modal"></div>
            </div>
            <div class="col-md-6">
              <div class="modal_field_name">{{$ds_phase_text}} Date:</div>
              <div class="modal_field_value" id="duare_sarkar_date_modal"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="modal_field_name">Mobile No.:</div>
              <div class="modal_field_value" id="mobile_no_modal"></div>
            </div>
            <div class="col-md-6">
              <div class="modal_field_name">Email Id., if available:</div>
              <div class="modal_field_value" id="email_modal"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="modal_field_name">Name:</div>
              <div class="modal_field_value" id="name_modal"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="modal_field_name">Gender:</div>
              <div class="modal_field_value" id="gender_modal"></div>
            </div>
            <div class="col-md-4">
              <div class="modal_field_name">Date of Birth:</div>
              <div class="modal_field_value" id="dob_modal"></div>
            </div>
            <div class="col-md-4">
              <div class="modal_field_name">Age (as on {{$dob_base_date}}):</div>
              <div class="modal_field_value" id="age_modal"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="modal_field_name">Father's Name:</div>
              <div class="modal_field_value" id="father_name_modal"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="modal_field_name">Mother's Name:</div>
              <div class="modal_field_value" id="mother_name_modal"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="modal_field_name">Spouse Name:</div>
              <div class="modal_field_value" id=spouse_name_modal></div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="modal_field_name">Caste:</div>
              <div class="modal_field_value" id="caste_category_modal"></div>
            </div>
            <div class="col-md-6" id="caste_certificate_no_section_modal">
              <div class="modal_field_name">SC/ST Certificate No:</div>
              <div class="modal_field_value" id="caste_certificate_no_modal"></div>
            </div>
          </div>
        </div>

        <!-- Contact Details Preview -->
        <div class="section1">
          <div class="row color1">
            <div class="col-md-12">
              <h2>Contact Details</h2>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="modal_field_name">State:</div>
              <div class="modal_field_value" id="state_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">District:</div>
              <div class="modal_field_value" id="district_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">Police Station:</div>
              <div class="modal_field_value" id="police_station_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">Block/Municipality/Corp:</div>
              <div class="modal_field_value" id="block_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">GP/Ward No.:</div>
              <div class="modal_field_value" id="gp_ward_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">Village/Town/City:</div>
              <div class="modal_field_value" id="village_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">House / Premise No:</div>
              <div class="modal_field_value" id="house_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">Post Office:</div>
              <div class="modal_field_value" id="post_office_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">Pin Code:</div>
              <div class="modal_field_value" id="pin_code_modal"></div>
            </div>
          </div>
        </div>

        <!-- Bank Details Preview -->
        <div class="section1">
          <div class="row color1">
            <div class="col-md-12">
              <h2>Bank Account Details</h2>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="modal_field_name">Bank Name:</div>
              <div class="modal_field_value" id="name_of_bank_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">Bank Branch Name:</div>
              <div class="modal_field_value" id="bank_branch_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">Bank Account No.:</div>
              <div class="modal_field_value" id="bank_account_number_modal"></div>
            </div>
            <div class="col-md-12">
              <div class="modal_field_name">IFSC Code:</div>
              <div class="modal_field_value" id="bank_ifsc_code_modal"></div>
            </div>
          </div>
        </div>

        <!-- Declaration Preview -->
        <div class="section1">
          <div class="row color1">
            <div class="col-md-12">
              <h2>Self Declaration</h2>
            </div>
          </div>
          <div class="row">
            <div class="form-group col-md-12">
              <label>
                <i class="fas fa-check" aria-hidden="true"></i> That I am a resident of West Bengal
                <br />
              </label>
              <br />
              <label>
                <i class="fas fa-check" aria-hidden="true"></i> That I do not earn any monthly remuneration from any regular Government job
                <br />
              </label>
              <br />
              <label>
                <i class="fas fa-check" aria-hidden="true"></i> That all the information and documents submitted by me are correct/ genuine. In case any of the
                information/ document is found to be false, penal action shall be taken against me and the benefit will be
                terminated. <br />
              </label>
              <br />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-center">
        <div class="text-center" id="submit_loader" style="display:none;">
          <img src="{{ asset('images/ZKZg.gif')}}" width="150px" height="150px">
        </div>

        <button type="button" class="btn btn-default btn-lg" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="submit" value="Submit" class="btn btn-success btn-lg modal-submit">Submit</button>
        <button type="button" id="submitting" value="Submit" class="btn btn-success btn-lg" style="display:none;" disabled>Submitting please wait</button>
      </div>
    </div>
  </div>
</div>

<!-- Document Upload Modal -->
<div class="modal fade" id="encolser_modal" tabindex="-1" role="dialog" aria-labelledby="encolserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="encolser_name">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="uploadForm" enctype="multipart/form-data">
        <input type="hidden" name="document_type" id="document_type" />
        <input type="hidden" name="is_profile" id="is_profile" />
        {{ csrf_field() }}
        <div class="modal-body">
          <label>Choose File:</label>
          <input type="file" name="file" id="fileInput" class="form-control">

          <div class="progress mt-3">
            <div class="progress-bar"></div>
          </div>
          <div id="uploadStatus" class="mt-3"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="submitButton" name='btnSubmit' class="btn btn-primary">Upload</button>
          <img style="display:none;" src="{{ asset('images/ZKZg.gif')}}" id="btn_encolser_loader" width="150px">
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
 <script src="{{ URL::asset('js/validateAdhar.js') }}"></script>
<script>
  var specialKeys = new Array();
  specialKeys.push(8); //Backspace
  function IsNumeric(e) {
    //alert()
    var keyCode = e.which ? e.which : e.keyCode
    var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
    document.getElementById("error").style.display = ret ? "none" : "inline";
    return ret;
  }

  $(document).ready(function() {
    // alert('hello');
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lk-main').addClass("active");
    var application_id = $("#application_id").val();
    var status = $("#status").val();
    if (application_id != '') {
      if (status == 1)
        $('.sidebar-menu #lb-draft-list').addClass("active");
      else if (status == 2)
        $('.sidebar-menu #sb-mt').addClass("active");
      else if (status == 3)
        $('.sidebar-menu #revertedList').addClass("active");
    } else {
      $('.sidebar-menu #lb-entry').addClass("active");
    }
    var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
    //alert(sessiontimeoutmessage);
    var ds_phase_text = '{{$ds_phase_text}}';


    var allow_ds_entry = {{$scheme_details -> allow_ds_entry}};

    var allow_normal_entry = {{$scheme_details -> allow_normal_entry}};

   
    if ($("#entry_type").val() == 2) {
      $(".duareSarkar").show();
    } else {
      $(".duareSarkar").hide();
    }

    $("#entry_type").on('change', function() {
      var entry_type = $("#entry_type").val();
      //alert(entry_type);
      if (entry_type == 2) {

        $(".duareSarkar").show();
      } else {
        $(".duareSarkar").hide();
      }
    });


    if ($("#caste_category").val() == "SC" || $("#caste_category").val() == "ST") {
      $("#caste_certificate_no_section").show();
      $(".fileLable_3").addClass('required-field');
    } else {
      $("#caste_certificate_no_section").hide();
      $(".fileLable_3").removeClass('required-field');

    }
    $("#caste_category").on('change', function() {

      var caste_category = $("#caste_category").val();
      if (caste_category == "SC" || caste_category == "ST") {
        $("#caste_certificate_no_section").show();
        $(".fileLable_3").addClass('required-field');
      } else {
        $("#caste_certificate_no_section").hide();
        $(".fileLable_3").removeClass('required-field');
      }
    });
    if (application_id != '') {
      var max_tab_code = $("#max_tab_code").val();
      if (status == 1) {
        if (max_tab_code == '') {
          tab_highlight(1);
        } else if(max_tab_code==5){
          tab_highlight(1);
        }
        else
          tab_highlight(parseInt(max_tab_code) + 1);
      } else if (status == 2)
        tab_highlight(1);
      else if (status == 3)
        tab_highlight(1);
    } else {
      tab_highlight(1);
    }
    var base_url = '{{ url('/') }}';




    $("#submitting").hide();
    $("#submit_loader").hide();
    $("#passport_image_view").hide();
    $("#spouse_section").hide();

    $("#bank_account_number,#confirm_bank_account_number").on("copy cut paste drop", function() {
      return false;
    });
    $('form.submit-once').submit(function(e) {
      if ($(this).hasClass('form-submitted')) {
        e.preventDefault();
        return;
      }
      $(this).addClass('form-submitted');
    });


    $("#caste_category").on('change', function() {

      var caste_category = $("#caste_category").val();
      if (caste_category == "SC" || caste_category == "ST") {
        $("#caste_certificate_no_section").show();
      } else {
        $("#caste_certificate_no_section").hide();
      }
    });




    //$(".submitting").attr("disabled", true);


    $(".receive-pension").click(function() {

      var selectedRP = new Array();
      var n1 = jQuery(".receive-pension:checked").length;
      if (n1 > 0) {

        jQuery(".receive-pension:checked").each(function() {
          selectedRP.push($(this).val());
        });
      }

      $("#receive-pension-modal").text(selectedRP)

    });


    $(".social-security-pension").click(function() {

      var selectedCategory = new Array();
      var n2 = jQuery(".social-security-pension:checked").length;
      if (n2 > 0) {

        jQuery(".social-security-pension:checked").each(function() {
          selectedCategory.push($(this).val());
        });
      }

      $("#checkbox-tick-modal").text(selectedCategory)


    });


    $("#dob").on('blur', function() {
      $dob = $('#dob').val();
      $('#error_txt_age').html('<img  src="{{ asset("images/ZKZg.gif") }}" width="50px" height="50px"/>');
      $.ajax({
        type: 'GET',
        url: '{{ url("getAge") }}',
        data: {
          dob: $dob,
          _token: '{{ csrf_token() }}',
        },
        success: function(data) {
          if (data != 0) {
            $('#txt_age').val(data);
          }
          $('#error_txt_age').html('');
        },
        error: function(ex) {
          alert(sessiontimeoutmessage);
          window.location.href = base_url;
        }
      });


    });

    $("#aadhar_no").on('blur', function() {
      var aadhar_no = $('#aadhar_no').val();
      if ($.trim($('#aadhar_no').val()).length == 0) {
        error_aadhar_no = 'Aadhar No is required';
        $('#error_aadhar_no').text(error_aadhar_no);
        $('#aadhar_no').addClass('has-error');
        $('#aadhar_dup_check').addClass('disabled');
        $('#btn_personal_details').addClass('disabled');
        //$("#dup_aadhaar_label").show();
      } else {
        if ($.trim($('#aadhar_no').val()).length != 12) {

          error_aadhar_no = 'Aadhar No should be 12 digit ';
          $('#error_aadhar_no').text(error_aadhar_no);
          $('#aadhar_no').addClass('has-error');
          $('#aadhar_dup_check').addClass('disabled');
          $('#btn_personal_details').addClass('disabled');
          //$("#dup_aadhaar_label").show();
        } else {
          var aadhar_no = $('#aadhar_no').val();
          var aadhar_valid = validate_adhar(aadhar_no);
          // aadhar_valid=1;
          if (aadhar_valid) {
            error_aadhar_no = '';
            $('#error_aadhar_no').text(error_aadhar_no);
            $('#aadhar_no').removeClass('has-error');
            $('#aadhar_dup_check').removeClass('disabled');
            // $('#btn_personal_details').removeClass('disabled');
            // $("#dup_aadhaar_label").hide();
          } else {
            error_aadhar_no = 'Invalid Aadhar No.';
            $('#error_aadhar_no').text(error_aadhar_no);
            $('#aadhar_no').addClass('has-error');
            $('#aadhar_dup_check').addClass('disabled');
            $('#btn_personal_details').addClass('disabled');
            // $("#dup_aadhaar_label").show();
          }
        }
      }



    });


    $('.txtOnly').keypress(function(e) {
      var regex = new RegExp(/^[a-zA-Z\s]+$/);
      var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (regex.test(str)) {
        return true;
      } else {
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

    $(".price-field").keyup(function() {
      var val = $(this).val();
      if (isNaN(val)) {
        val = val.replace(/[^0-9\.]/g, '');
        if (val.split('.').length > 2)
          val = val.replace(/\.+$/, "");
      }
      $(this).val(val);
    });

    //$('.client-js-district').select2({ data: districts });

    $('#btn_personal_details').click(function() {
    
      $("#errorDiv").hide();
      $("#errorDiv").find("ul").html('');
      var error_entry_type = '';
      var error_duare_sarkar_registration_no = '';
      var error_duare_sarkar_date = '';
      var error_first_name = '';
      var error_last_name = '';
      var error_husband_first_name = '';
      var error_husband_last_name = '';
      var error_gender = '';
      var error_dob = "";
      var error_txt_age = '';
      var error_father_first_name = '';
      var error_father_last_name = '';

      var error_mother_first_name = '';
      var error_mother_last_name = '';

      var error_caste_category = '';
      var error_marital_status = '';
      var error_aadhar_no = '';
      var error_mobile_no = '';
      var error_email = '';
      var error_application_date = '';

      if ($.trim($('#entry_type').val()).length == 0) {
        error_entry_type = 'Please Select Application Type';
        $('#error_entry_type').text(error_entry_type);
        $('#entry_type').addClass('has-error');
      } else {
        error_entry_type = '';
        $('#error_entry_type').text(error_entry_type);
        $('#entry_type').removeClass('has-error');
      }
      if ($('#status').val() == 1) {
        if ($.trim($('#application_date').val()).length == 0) {
          error_application_date = 'Please Enter Application Date';
          $('#error_application_date').text(error_application_date);
          $('#application_date').addClass('has-error');
        }
      } else {
        error_application_date = '';
        $('#error_application_date').text(error_application_date);
        $('#application_date').removeClass('has-error');
      }
      if ($('#entry_type').val() == 2) {
        if ($.trim($('#duare_sarkar_registration_no').val()).length == 0) {
          error_duare_sarkar_registration_no = ds_phase_text + ' Registration no. is required';
          $('#error_duare_sarkar_registration_no').text(error_duare_sarkar_registration_no);
          $('#duare_sarkar_registration_no').addClass('has-error');
        } else {
          error_duare_sarkar_registration_no = '';
          $('#error_duare_sarkar_registration_no').text(error_duare_sarkar_registration_no);
          $('#duare_sarkar_registration_no').removeClass('has-error');
        }
        if ($.trim($('#duare_sarkar_date').val()).length == 0) {
          error_duare_sarkar_date = ds_phase_text + ' Date is required';
          $('#error_duare_sarkar_date').text(error_duare_sarkar_date);
          $('#duare_sarkar_date').addClass('has-error');
        } else {
          error_duare_sarkar_date = '';
          $('#error_duare_sarkar_date').text(error_duare_sarkar_date);
          $('#duare_sarkar_date').removeClass('has-error');
        }
      }

      if ($.trim($('#first_name').val()).length == 0) {
        error_first_name = 'Name is required';
        $('#error_first_name').text(error_first_name);
        $('#first_name').addClass('has-error');
      } else {
        error_first_name = '';
        $('#error_first_name').text(error_first_name);
        $('#first_name').removeClass('has-error');
      }












      if ($.trim($('#gender').val()).length == 0) {
        error_gender = 'Gender is required';
        $('#error_gender').text(error_gender);
        $('#gender').addClass('has-error');
      } else {
        error_gender = '';
        $('#error_gender').text(error_gender);
        $('#gender').removeClass('has-error');
      }

      if ($.trim($('#dob').val()).length == 0) {
        error_dob = 'DOB is required';
        $('#error_dob').text(error_dob);
        $('#dob').addClass('has-error');
      } else {
        error_dob = '';
        $('#error_dob').text(error_dob);
        $('#dob').removeClass('has-error');
      }
      if ($.trim($('#txt_age').val()).length == 0) {
        error_txt_age = 'Age is required';
        $('#error_txt_age').text(error_txt_age);
        $('#txt_age').addClass('has-error');
      } else {
        if ($('#txt_age').val() < 25 || $('#txt_age').val() > 60) {
          error_txt_age = 'Age must be between 25 and 60';
          $('#error_txt_age').text(error_txt_age);
          $('#txt_age').addClass('has-error');
        } else
          error_txt_age = '';
        $('#error_txt_age').text(error_txt_age);
        $('#txt_age').removeClass('has-error');


      }

      if ($.trim($('#father_first_name').val()).length == 0) {
        error_father_first_name = 'First Name is required';
        $('#error_father_first_name').text(error_father_first_name);
        $('#father_first_name').addClass('has-error');
      } else {
        error_father_first_name = '';
        $('#error_father_first_name').text(error_father_first_name);
        $('#father_first_name').removeClass('has-error');
      }



      if ($.trim($('#mother_first_name').val()).length == 0) {
        error_mother_first_name = 'First Name is required';
        $('#error_mother_first_name').text(error_mother_first_name);
        $('#mother_first_name').addClass('has-error');
      } else {
        error_mother_first_name = '';
        $('#error_mother_first_name').text(error_mother_first_name);
        $('#mother_first_name').removeClass('has-error');
      }



      if ($.trim($('#caste_category').val()).length == 0) {
        error_caste_category = 'Caste is required';
        $('#error_caste_category').text(error_caste_category);
        $('#caste_category').addClass('has-error');
      } else {
        error_caste_category = '';
        $('#error_caste_category').text(error_caste_category);
        $('#caste_category').removeClass('has-error');
      }
      if ($('#caste_category').val() == 'SC' || $('#caste_category').val() == 'ST') {
        if ($.trim($('#caste_certificate_no').val()).length == 0) {
          error_caste_certificate_no = 'SC/ST Certificate No is required';
          $('#error_caste_certificate_no').text(error_caste_certificate_no);
          $('#caste_certificate_no').addClass('has-error');
        } else {
          error_caste_certificate_no = '';
          $('#error_caste_certificate_no').text(error_caste_certificate_no);
          $('#caste_certificate_no').removeClass('has-error');
        }
      } else {
        error_caste_certificate_no = '';
        $('#error_caste_certificate_no').text(error_caste_certificate_no);
        $('#caste_certificate_no').removeClass('has-error');
      }

      if ($.trim($('#aadhar_no').val()).length == 0) {
        error_aadhar_no = 'Aadhar No is required';
        $('#error_aadhar_no').text(error_aadhar_no);
        $('#aadhar_no').addClass('has-error');
        $('#aadhar_dup_check').addClass('disabled');
      } else {
        if ($.trim($('#aadhar_no').val()).length != 12) {

          error_aadhar_no = 'Aadhar No should be 12 digit ';
          $('#error_aadhar_no').text(error_aadhar_no);
          $('#aadhar_no').addClass('has-error');
          $('#aadhar_dup_check').addClass('disabled');
        } else {
          var aadhar_no = $('#aadhar_no').val();
          var aadhar_valid = validate_adhar(aadhar_no);
          // aadhar_valid=1;
          if (aadhar_valid) {
            error_aadhar_no = '';
            $('#error_aadhar_no').text(error_aadhar_no);
            $('#aadhar_no').removeClass('has-error');
            $('#aadhar_dup_check').removeClass('disabled');
          } else {
            error_aadhar_no = 'Invalid Aadhar No.';
            $('#error_aadhar_no').text(error_aadhar_no);
            $('#aadhar_no').addClass('has-error');
            $('#aadhar_dup_check').addClass('disabled');
          }
        }
      }
      if ($.trim($('#mobile_no').val()).length == 0) {
        error_mobile_no = 'Mobile Number is required';
        $('#error_mobile_no').text(error_mobile_no);
        $('#mobile_no').addClass('has-error');
      } else {


        if (ltrim($.trim($('#mobile_no').val())).length != 10) {
          error_mobile_no = 'Mobile Number must be 10 digit';
          $('#error_mobile_no').text(error_mobile_no);
          $('#mobile_no').addClass('has-error');
        } else {
          error_mobile_no = '';
          $('#error_mobile_no').text(error_mobile_no);
          $('#mobile_no').removeClass('has-error');

        }
      }
      if ($.trim($('#email').val()).length == 0) {
        error_email = '';
        $('#error_email').text(error_email);
        $('#email').removeClass('has-error');
      } else {

        if ((/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z.]{2,5}$/).exec($.trim($('#email').val())) == null) {
          error_email = 'Email Id is invalid';
          $('#error_email').text(error_email);
          $('#email').addClass('has-error');
        } else {
          error_email = '';
          $('#error_email').text(error_email);
          $('#email').removeClass('has-error');
        }

      }
      if (error_application_date != '' || error_entry_type != '' || error_duare_sarkar_registration_no != '' || error_duare_sarkar_date != '' || error_first_name != '' || error_dob != '' || error_gender != '' || error_txt_age != '' ||
        error_father_first_name != '' || error_father_last_name != '' || error_mother_first_name != '' ||
        error_mother_last_name != '' || error_caste_category != '' || error_caste_certificate_no != '' ||
        error_aadhar_no != '' || error_mobile_no != '' || error_email != '')
      //if( valid==0 )
      {
        //console.log(error_application_date);

        $("html, body").animate({
          scrollTop: 0
        }, "slow");
        return false;
      } else {

        $('#btn_personal_details').hide();
        $("#btn_personal_details_loader").show();
        var data = $.param({
          "_token": '{{ csrf_token() }}',
          "application_id": $("#commonfield #application_id").val()
        }) + "&" + $("#personal_form").serialize();
        $.ajax({
          type: 'get',
          url: '{{ url("ajax_personal_entry_wtSws") }}',
          dataType: 'json',
          data: data,
          beforeSend: function() {
            $('#btn_personal_details_loader').show();
          },
          success: function(data) {
            if (data.return_status) {
              $('#btn_personal_details_loader').hide();
              $('#btn_personal_details').show();
              $("#commonfield #max_tab_code").val(data.max_tab_code);
              $("#personal #personalCount").val(1);
              $('#forEditInfo').hide();
              $("#commonfield #application_id").val(data.application_id);
              if (data.session_lb_lifecertificate.is_error == 0) {
                $("#life_certificate_pass").show();
                if (data.session_lb_lifecertificate.last_biometric != '') {
                  $("#life_certificate_pass_text").text(data.session_lb_lifecertificate.last_biometric);
                }
              } else {
                //console.log(data.session_lb_lifecertificate);
                $("#life_certificate_n_pass").show();
                if (data.session_lb_lifecertificate.last_biometric != '') {
                  $("#life_certificate_n_pass_text").text(data.session_lb_lifecertificate.last_biometric);
                }
              }
              if (data.session_lb_castecertificate.is_error == 0) {
                $("#cast_certificate_pass").show();
                if (data.session_lb_castecertificate.lastdatetime != '') {
                  $("#cast_certificate_pass_text").text(data.session_lb_castecertificate.lastdatetime);
                }
              } else if (data.session_lb_castecertificate.is_error == 1) {
                $("#cast_certificate_n_pass").show();
                if (data.session_lb_castecertificate.last_biometric != '') {
                  $("#cast_certificate_n_pass_text").text(data.session_lb_castecertificate.lastdatetime);
                }
              }

              if (data.session_lb_aadhaar_no.is_error == 0) {
                $("#aadhaar_no_checked_pass").show();
                if (data.session_lb_aadhaar_no.lastdatetime != '') {
                  $("#aadhaar_no_checked_pass_text").text(data.session_lb_aadhaar_no.lastdatetime);
                  if (data.session_lb_aadhaar_no.dob_missmatch == 1) {
                    $("#aadhaar_no_dob_pass_text").text(data.session_lb_aadhaar_no.dob_kh);
                  } else {
                    $("#aadhar_message").hide();

                  }

                }
              } else if (data.session_lb_aadhaar_no.is_error == 1) {
                $("#aadhaar_no_checked_n_pass").show();
                if (data.session_lb_aadhaar_no.last_biometric != '') {
                  $("#aadhaar_no_checked_n_pass_text").text(data.session_lb_aadhaar_no.lastdatetime);
                }
              }

              tab_highlight(2);
              if (data.return_msg) {
                printMsg(data.return_msg, '0', 'errorDiv');
                //console.log(data.session_lb_lifecertificate.is_error);
              }
              $("html, body").animate({
                scrollTop: 0
              }, "slow");
              // $('#loaderdiv').hide();
            } else {
              //console.log(data.return_msg);
              if (data.return_msg == 'Duplicate Mobile Number.. Please try different.') {
                $("#markDuareSarkarDiv").show();
              }
              $('#btn_personal_details_loader').hide();
              $('#btn_personal_details').show();
              printMsg(data.return_msg, '0', 'errorDiv');
              $("html, body").animate({
                scrollTop: 0
              }, "slow");
              return false;
            }
          },
          error: function(ex) {
            $('#btn_personal_details').show();
             alert(sessiontimeoutmessage);
             window.location.href=base_url;
          }
        });
      }


    });
    $('#aadhar_dup_check').click(function() {
      var data = $.param({
        "_token": '{{ csrf_token() }}',
        "application_id": $("#commonfield #application_id").val(),
        "aadhar_no": $("#aadhar_no").val()
      });
      $.ajax({
        type: 'POST',
        url: '{{url("ajax_check_dup_aadhar")}}',
        dataType: 'json',
        data: data,
        beforeSend: function() {
          $('#error_aadhar_no').html('<img  src="{{asset("images/ZKZg.gif") }}" width="50px" height="50px"/>');
        },
        success: function(data) {
          if (data.return_status == 2) {
            $('#error_aadhar_no').html('');
            $('#error_aadhar_no').html(data.return_msg);
            $("#dup_aadhaar_label").show();
            $("#markDuareSarkarDiv").show();
          } else if (data.return_status == 1) {
            $('#error_aadhar_no').html(data.return_msg);
            $('#error_aadhar_no').html('');
            $("#dup_aadhaar_label").hide();
            $('#btn_personal_details').removeClass('disabled');
          }
        },
        error: function(ex) {
          $('#error_aadhar_no').html('');
          //$('#btn_encloser_details').show();
          alert(sessiontimeoutmessage);
          window.location.href = base_url;
        }
      });
    });

    function ltrim(str) {
      return str.replace(/^0+/, "");
    }
    $('#btn_contact_details').click(function() {
      $("#errorDiv").hide();
      $("#errorDiv").find("ul").html('');
      var error_district = '';
      var error_asmb_cons = '';

      var error_urban_code = '';
      var error_block = '';
      var error_gp_ward = '';

      var error_village = '';
      var error_post_office = '';
      var error_pin_code = '';
      var error_police_station = '';
      var error_residency_period = '';
      var error_mobile_no = '';

      if ($.trim($('#district').val()).length == 0) {
        error_district = 'District is required';
        $('#error_district').text(error_district);
        $('#district').addClass('has-error');
      } else {
        error_district = '';
        $('#error_district').text(error_district);
        $('#district').removeClass('has-error');
      }




      if ($.trim($('#urban_code').val()).length == 0) {
        error_urban_code = 'Rural/Urban is required';
        $('#error_urban_code').text(error_urban_code);
        $('#urban_code').addClass('has-error');
      } else {
        error_urban_code = '';
        $('#error_urban_code').text(error_urban_code);
        $('#urban_code').removeClass('has-error');
      }


      if ($.trim($('#block').val()).length == 0) {
        error_block = 'Block/Municipality is required';
        $('#error_block').text(error_block);
        $('#block').addClass('has-error');
      } else {
        error_block = '';
        $('#error_block').text(error_block);
        $('#block').removeClass('has-error');
      }


      if ($.trim($('#gp_ward').val()).length == 0) {
        error_gp_ward = 'GP/Ward No. is required';
        $('#error_gp_ward').text(error_gp_ward);
        $('#gp_ward').addClass('has-error');
      } else {
        error_gp_ward = '';
        $('#error_gp_ward').text(error_gp_ward);
        $('#gp_ward').removeClass('has-error');
      }




      if ($.trim($('#village').val()).length == 0) {
        error_village = 'Village/Town/City is required';
        $('#error_village').text(error_village);
        $('#village').addClass('has-error');
      } else {
        error_village = '';
        $('#error_village').text(error_village);
        $('#village').removeClass('has-error');
      }

      if ($.trim($('#post_office').val()).length == 0) {
        error_post_office = 'Post Office is required';
        $('#error_post_office').text(error_post_office);
        $('#post_office').addClass('has-error');
      } else {
        error_post_office = '';
        $('#error_post_office').text(error_post_office);
        $('#post_office').removeClass('has-error');
      }

      if ($.trim($('#pin_code').val()).length == 0) {
        error_pin_code = 'Pin Code is required';
        $('#error_pin_code').text(error_pin_code);
        $('#pin_code').addClass('has-error');
      } else {

        if ($.trim($('#pin_code').val()).length != 6) {
          error_pin_code = 'Pin Code must be 6 digit';
          $('#error_pin_code').text(error_pin_code);
          $('#pin_code').addClass('has-error');
        } else {
          error_pin_code = '';
          $('#error_pin_code').text(error_pin_code);
          $('#pin_code').removeClass('has-error');

        }

      }


      if ($.trim($('#police_station').val()).length == 0) {

        error_police_station = 'Police Station is required';
        $('#error_police_station').text(error_police_station);
        $('#police_station').addClass('has-error');
      } else {
        error_police_station = '';
        $('#error_police_station').text(error_police_station);
        $('#police_station').removeClass('has-error');
      }
      if (error_district != '' || error_police_station != '' || error_urban_code != '' || error_block != '' || error_gp_ward != '' || error_village != '' || error_post_office != '' || error_pin_code != '')
      // var valid=1 ;
      //if(error_asmb_cons != ''  )
      //if(valid==0)
      {
        $("html, body").animate({
          scrollTop: 0
        }, "slow");
        return false;
      } else {
        $('#btn_contact_details').hide();
        $("#btn_contact_details_loader").show();
        //alert($("#application_id").val());
        var data = $.param({
          "_token": '{{ csrf_token() }}',
          "application_id": $("#commonfield #application_id").val()
        }) + "&" + $("#contact").serialize();

        $.ajax({
          type: 'POST',
          url: '{{ url("ajax_contact_entry_wtSws") }}',
          dataType: 'json',
          data: data,
          beforeSend: function() {
            $('#btn_contact_details_loader').show();
          },
          success: function(data) {
            if (data.return_status) {
              $("#contact #contactCount").val(1);
              $("#commonfield #max_tab_code").val(data.max_tab_code);
              //alert( $("#max_tab_code").val());
              tab_highlight(3);

              if (data.return_msg) {
                printMsg(data.return_msg, '0', 'errorDiv');
              }
              $("html, body").animate({
                scrollTop: 0
              }, "slow");
              $('#btn_contact_details_loader').hide();
              $('#btn_contact_details').show();
              // $('#loaderdiv').hide();
            } else {
              //console.log(data.return_msg);
              $('#btn_contact_details_loader').hide();
              $('#btn_contact_details').show();
              printMsg(data.return_msg, '0', 'errorDiv');
              $("html, body").animate({
                scrollTop: 0
              }, "slow");
              return false;
            }
          },
          error: function(ex) {
            $('#btn_contact_details').show();
            alert(sessiontimeoutmessage);
            window.location.href = base_url;
          }
        });

      }



    });

    $('#previous_btn_contact_details').click(function() {
      tab_highlight(1);
    });



    $('#bank_ifsc_code').blur(function() {
      $ifsc_data = $.trim($('#bank_ifsc_code').val());
      $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
      $('#name_of_bank').val('');
      $('#bank_branch').val('');
      if ($ifscRGEX.test($ifsc_data)) {
        $('#bank_ifsc_code').removeClass('has-error');
        $('#error_bank_ifsc_code').text('');
        $('#error_name_of_bank').html('<img  src="{{ asset("images/ZKZg.gif") }}" width="50px" height="50px"/>');
        $('#error_bank_branch').html('<img  src="{{ asset("images/ZKZg.gif") }}" width="50px" height="50px"/>');
        $.ajax({
          type: 'POST',
          url: '{{ url("legacy/getBankDetails") }}',
          data: {
            ifsc: $ifsc_data,
            _token: '{{ csrf_token() }}',
          },
          success: function(data) {
            if (data == 'null') {
              alert("The IFSC within the West Bengal only be accepted.");
              $('#name_of_bank').val('');
              $('#bank_branch').val('');
              $('#error_name_of_bank').html('');
              $('#error_bank_branch').html('');
            } else {
              data = JSON.parse(data);
              $('#name_of_bank').val(data.bank);
              $('#bank_branch').val(data.branch);
              $('#error_name_of_bank').html('');
              $('#error_bank_branch').html('');
            }
            //   if (!data || data.length === 0) {
            //     $('#error_bank_ifsc_code').text('No data found with the IFSC');
            //     $('#bank_ifsc_code').addClass('has-error');
            //     return;
            //   }
            //   data = JSON.parse(data);
            //  // console.log(data);
            //   $('#name_of_bank').val(data.bank);
            //   $('#bank_branch').val(data.branch);
            //    $('#error_name_of_bank').html('');
            //   $('#error_bank_branch').html('');
          },
          error: function(ex) {
            alert(sessiontimeoutmessage);
            window.location.href = base_url;
          }
        });

      } else {
        $('#error_bank_ifsc_code').text('IFSC format invalid please check the code');
        $('#bank_ifsc_code').addClass('has-error');
      }
    });

    $('#btn_bank_details').click(function() {

      $("#errorDiv").hide();
      $("#errorDiv").find("ul").html('');
      var error_name_of_bank = '';
      var error_bank_branch = '';
      var error_bank_account_number = '';
      var error_bank_ifsc_code = '';
      var error_confirm_bank_account_number = '';



      if ($.trim($('#name_of_bank').val()).length == 0) {
        error_name_of_bank = 'Name of Bank is required';
        $('#error_name_of_bank').text(error_name_of_bank);
        $('#name_of_bank').addClass('has-error');
      } else {
        error_name_of_bank = '';
        $('#error_name_of_bank').text(error_name_of_bank);
        $('#name_of_bank').removeClass('has-error');
      }

      if ($.trim($('#bank_branch').val()).length == 0) {
        error_bank_branch = 'Bank Branch is required';
        $('#error_bank_branch').text(error_bank_branch);
        $('#bank_branch').addClass('has-error');
      } else {
        error_bank_branch = '';
        $('#error_bank_branch').text(error_bank_branch);
        $('#bank_branch').removeClass('has-error');
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
        error_confirm_bank_account_number = 'Confirm Bank Account Number is required';
        $('#error_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').addClass('has-error');
      } else {
        error_confirm_bank_account_number = '';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').removeClass('has-error');
      }
      if ($.trim($('#bank_account_number').val()) != $.trim($('#confirm_bank_account_number').val())) {
        error_confirm_bank_account_number = 'Confirm Bank Account Number not Match with Bank Account Number';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').addClass('has-error');
      } else {
        error_confirm_bank_account_number = '';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').removeClass('has-error');
      }
      if ($.trim($('#bank_ifsc_code').val()).length == 0) {
        error_bank_ifsc_code = 'IFS Code is required';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc_code').addClass('has-error');
      } else {
        error_bank_ifsc_code = '';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc_code').removeClass('has-error');
      }

      $ifsc_data = $.trim($('#bank_ifsc_code').val());
      $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
      if ($ifscRGEX.test($ifsc_data)) {
        error_bank_ifsc_code = '';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc_code').removeClass('has-error');
      } else {
        error_bank_ifsc_code = 'Please check IFS Code format';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc_code').addClass('has-error');
      }

      //var valid=1;
      if (error_name_of_bank != '' || error_bank_branch != '' || error_bank_account_number != '' || error_bank_ifsc_code != '' || error_confirm_bank_account_number != '')
      // if(error_name_of_bank !='' )
      //if(valid==0)
      {
        $("html, body").animate({
          scrollTop: 0
        }, "slow");
        return false;
      } else {
       
        $('#btn_bank_details').hide();
        $("#btn_bank_details_loader").show();
        var data = $.param({
          "_token": '{{ csrf_token() }}',
          "application_id": $("#commonfield #application_id").val()
        }) + "&" + $("#bank").serialize();
        $.ajax({
          type: 'POST',
          url: '{{ url("ajax_bank_entry_wtSws") }}',
          dataType: 'json',
          data: data,
          beforeSend: function() {
            $('#btn_bank_details_loader').show();
          },
          success: function(data) {
            // alert('ok');
            if (data.return_status) {
              $("#bank #bankCount").val(1);
              $('#btn_bank_details_loader').hide();
              $('#btn_bank_details').show();
              if (data.return_msg) {
                printMsg(data.return_msg, '0', 'errorDiv');
              }
              $("#commonfield #max_tab_code").val(data.max_tab_code);
              tab_highlight(4);
              $("html, body").animate({
                scrollTop: 0
              }, "slow");
              // $('#loaderdiv').hide();
            } else {
              //console.log(data.return_msg);
              if (data.return_msg == 'Duplicate Bank Account Number.. Please try different.') {
                $("#markDuareSarkarDiv").show();
              }
              $('#btn_bank_details_loader').hide();
              $('#btn_bank_details').show();
              printMsg(data.return_msg, '0', 'errorDiv');
              $("html, body").animate({
                scrollTop: 0
              }, "slow");
              return false;
            }
          },
          error: function(ex) {
            $('#btn_bank_details_loader').hide();
            $('#btn_bank_details').show();
            alert(sessiontimeoutmessage);
            window.location.href = base_url;
          }
        });

      }

    });
    $('#previous_btn_bank_details').click(function() {
      tab_highlight(2);
    });

    $('#previous_btn_experience_details').click(function() {
      tab_highlight(3);
    });
    $('#btn_experience_details').click(function() {
      var max_tab_code = $("#max_tab_code").val();
      if (parseInt(max_tab_code) >= 4)
        tab_highlight(5);
    });
    $('#btn_encloser_details').on('click', function() {
      $('#btn_encloser_details').hide();
      $("#btn_enc_details_loader").show();
      var data = $.param({
        "_token": '{{ csrf_token() }}',
        "application_id": $("#commonfield #application_id").val()
      });
      $.ajax({
        type: 'POST',
        url: '{{ url("ajax_check_encloser_wtSws") }}',
        dataType: 'json',
        data: data,
        beforeSend: function() {
          $('#btn_enc_details_loader').show();
        },
        success: function(data) {
          if (data.return_status) {
            $('#btn_enc_details_loader').hide();
            $('#btn_encloser_details').show();
            if (data.return_msg) {
              printMsg(data.return_msg, '0', 'errorDiv');
            }
            $("#commonfield #max_tab_code").val(data.max_tab_code);
            tab_highlight(5);
            $("html, body").animate({
              scrollTop: 0
            }, "slow");
            // $('#loaderdiv').hide();
          } else {
            //console.log(data.return_msg);
            $('#btn_enc_details_loader').hide();
            $('#btn_encloser_details').show();
            printMsg(data.return_msg, '0', 'errorDiv');
            $("html, body").animate({
              scrollTop: 0
            }, "slow");
            return false;
          }
        },
        error: function(ex) {
          $('#btn_enc_details_loader').hide();
          $('#btn_encloser_details').show();
          alert(sessiontimeoutmessage);
          window.location.href = base_url;
        }
      });
    });
    $('#previous_btn_decl_details').click(function() {
      tab_highlight(4);
    });




    /***************************SD*********************************/
    $('#btn_submit_preview').click(function() {
      $(".modal-submit").show();
      $("#submitting").hide();
      $("#submit_loader").hide();

      var error_is_resident = '';
      var error_earn_monthly_remuneration = '';
      var error_info_genuine_decl = '';
      if ($("#is_resident").prop('checked') == true) {
        error_is_resident = '';
        $('#error_is_resident').text(error_is_resident);
        $('#is_resident').removeClass('has-error');

      } else {
        error_is_resident = 'Please Check';
        $('#error_is_resident').text(error_is_resident);
        $('#is_resident').addClass('has-error');
      }
      if ($("#earn_monthly_remuneration").prop('checked') == true) {
        error_earn_monthly_remuneration = '';
        $('#error_is_monthly_remuneration').text(error_earn_monthly_remuneration);
        $('#earn_monthly_remuneration').removeClass('has-error');

      } else {
        error_earn_monthly_remuneration = 'Please Check';
        $('#error_earn_monthly_remuneration').text(error_earn_monthly_remuneration);
        $('#earn_monthly_remuneration').addClass('has-error');
      }
      if ($("#info_genuine_decl").prop('checked') == true) {
        error_info_genuine_decl = '';
        $('#error_info_genuine_decl').text(error_info_genuine_decl);
        $('#info_genuine_decl').removeClass('has-error');

      } else {
        error_info_genuine_decl = 'Please Check';
        $('#error_info_genuine_decl').text(error_info_genuine_decl);
        $('#info_genuine_decl').addClass('has-error');
      }
      if (error_is_resident != '' || error_earn_monthly_remuneration != '' || error_info_genuine_decl != '') {
        return false;
      } else {
        $("#confirm-submit").modal("show");
      }



    });
    $('.encloserModal').click(function() {
      $("#encolser_name").html('');
      $('#uploadStatus').html('');
      $('.progress-bar').html('');
      $("#uploadForm #document_type").val('');
      $("#uploadForm #is_profile").val('');
      $('#btn_encolser_loader').hide();
      var label = $(this).parent().find('label').text();
      $("#encolser_name").html(label);
      var id = $(this).attr("id");
      var id_split = id.split('_');
      //console.log(id_split);
      $("#uploadForm #document_type").val(id_split[1]);
      $("#uploadForm #is_profile").val(id_split[2]);
      $("#encolser_modal").modal("show");

    });
    $("#uploadForm").on('submit', function(e) {
      $('#submitButton').hide();
      $('#btn_encolser_loader').show();

      e.preventDefault();
      var form = $('#uploadForm')[0];
      var formData = new FormData(form);
      var max_tab_code = $("#commonfield #max_tab_code").val();
      var application_id = $("#commonfield #application_id").val();
      formData.append('max_tab_code', max_tab_code);
      formData.append('application_id', application_id);
      $.ajax({
        xhr: function() {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
              var percentComplete = ((evt.loaded / evt.total) * 100);
              var percentComplete = Math.ceil(percentComplete);
              $(".progress-bar").width(percentComplete + '%');
              $(".progress-bar").html(percentComplete + '%');
            }
          }, false);
          return xhr;
        },
        type: 'POST',
        dataType: 'json',
        url: '{{ url("ajax_encloser_entry_wtSws") }}',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function() {
          $(".progress-bar").width('0%');
          //$('#uploadStatus').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
        },
        error: function(ex) {
          //console.log(ex);
          $('#uploadStatus').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
          $('#btn_encolser_loader').hide();
          $('#submitButton').show();


        },
        success: function(resp) {
          //console.log(resp);
          if (resp.return_status == 1) {
            $("#max_tab_code").val(resp.max_tab_code);
            var id = $("#uploadForm #document_type").val();
            $('#uploadForm')[0].reset();
            $('#download_' + id).show();
            $('#uploadStatus').html('<p style="color:#28A74B;">File has uploaded successfully!</p>');
            //$(".progress-bar").width('0%');

          } else if (resp.return_status == 0) {
            $('#uploadStatus').html('<p style="color:#EA4335;">' + resp.return_msg + '</p>');
          }
          $('#btn_encolser_loader').hide();
          $('#submitButton').show();


        }
      });


    });


    $('#encolser_modal').on('hidden.bs.modal', function(e) {
      $("#uploadForm #document_type").val('');
      $("#uploadForm #is_profile").val('');
      $(".progress-bar").html('');

    });
    $(".downloadEncloser").click(function() {
      var id = $(this).attr("id");
      var id_split = id.split('_');
      var application_id = $("#application_id").val();
      window.open("downaloadEncloser?id=" + id_split[1] + "&is_profile_pic=" + id_split[2] + "&application_id=" + application_id);
    });
    $('#btn_submit_preview').click(function() {
      $('#aadhar_no_modal').text($('#aadhar_no').val());
      $('#duare_sarkar_registration_no_modal').text($('#duare_sarkar_registration_no').val());
      var duare_sarkar_date = $('#duare_sarkar_date').val();
      var dArr = duare_sarkar_date.split("-");
      var today1 = dArr[2] + '/' + dArr[1] + '/' + dArr[0];
      $('#duare_sarkar_date_modal').text(today1);
      $('#name_modal').text($('#first_name').val());
      $('#mobile_no_modal').text($('#mobile_no').val());
      $('#email_modal').text($('#email').val());
      $('#gender_modal').text($('#gender').val());
      var dob = $('#dob').val();
      var dArr = dob.split("-");
      var today = dArr[2] + '/' + dArr[1] + '/' + dArr[0];
      $('#dob_modal').text(today);
      $('#age_modal').text($('#txt_age').val());
      $('#father_name_modal').text($('#father_first_name').val() + ' ' + $('#father_middle_name').val() + ' ' + $('#father_last_name').val());
      $('#mother_name_modal').text($('#mother_first_name').val() + ' ' + $('#mother_middle_name').val() + ' ' + $('#mother_last_name').val());
      $('#caste_category_modal').text($('#caste_category').val());
      if ($('#caste_category').val() == 'SC' || $('#caste_category').val() == 'ST') {
        $('#caste_certificate_no_modal').text($('#caste_certificate_no').val());
        $("#caste_certificate_no_section_modal").show();
      } else {
        $("#caste_certificate_no_section_modal").hide();
      }


      $('#spouse_name_modal').text($('#spouse_first_name').val() + ' ' + $('#spouse_middle_name').val() + ' ' + $('#spouse_last_name').val());

      $('#monthly_income_modal').text($('#monthly_income').val());

      $('#state_modal').text($('#state').val());
      $('#district_modal').text($("#district :selected").text());
      $('#police_station_modal').text($('#police_station').val());
      $('#block_modal').text($("#block :selected").text());
      $('#gp_ward_modal').text($("#gp_ward :selected").text());
      $('#village_modal').text($('#village').val());
      $('#house_modal').text($('#house_premise_no').val());
      $('#pin_code_modal').text($('#pin_code').val());
      $('#post_office_modal').text($('#post_office').val());

      $('#bank_account_number_modal').text($('#bank_account_number').val());
      $('#name_of_bank_modal').text($('#name_of_bank').val());
      $('#bank_branch_modal').text($('#bank_branch').val());
      $('#bank_ifsc_code_modal').text($('#bank_ifsc_code').val());

      $('#av_status_modal').text($("#av_status option:selected").text());
      $('#text_1_modal').text($('#text_1').val());
      $('#text_2_modal').text($('#text_2').val());
      $('#receiving_pension_other_source_1_txt').text($('#receiving_pension_other_source_1').val());
      $('#receiving_pension_other_source_2_txt').text($('#receiving_pension_other_source_2').val());
      $("#memberListModal tbody").html('');
      var tr = $("#memberList").find("TR:has(td:not(:last-child))").clone();
      $("#memberListModal tbody").append(tr);
      $("#memberListModal tbody tr td.del-mem").hide();






      // $('.modal-submit').click(function(){
      // $(".modal-submit").attr("disabled", true);


      // });


    });

    $('.modal-submit').on('click', function() {

      $(".modal-submit").hide();
      $("#submitting").show();
      $("#submit_loader").show();
      var application_id = $("#commonfield #application_id").val();
      var status = $("#commonfield #status").val();
      $('#declaration').append('<input type="hidden" name="is_draft_page" id="is_draft_page" value="1">');
      $('#declaration').append('<input type="hidden" name="max_tab_code" id="max_tab_code" value="' + max_tab_code + '">');
      $('#declaration').append('<input type="hidden" name="application_id" id="application_id" value="' + application_id + '">');
      $('#declaration').append('<input type="hidden" name="status" id="status" value="' + status + '">');
      $("#declaration").submit();
    });

  $('#district').change(function() {
    if ($(this).val() != '') {
      $('#urban_code').val('');
      $('#block').html('<option value="">--Select--</option>');
      $('#gp_ward').html('<option value="">--Select--</option>');
    }
  });
  $('#urban_code').change(function() {
    if ($(this).val() != '') {
      var district_code = $('#district').val();
      //alert(district_code);
      var rural_urban = $(this).val();
      var error_found = 1;
      if (rural_urban == 2) {
        error_found = 0;
        var url = '{{ url("masterDataAjax/getTaluka") }}';
      } else if (rural_urban == 1) {
        error_found = 0;
        var url = '{{ url("masterDataAjax/getUrban") }}';
      }

      if (error_found == 0) {
        //alert('ok');
        $('#block').val('');
        $('#gp_ward').val('');
        $('#error_block').html('<img  src="{{ asset("images/ZKZg.gif") }}" width="50px" height="50px"/>');
        $.ajax({
          type: 'POST',
          url: url,
          data: {
            district_code: district_code,
            _token: '{{ csrf_token() }}',
          },
          success: function(data) {
            //console.log(data);
            var htmlOption = '<option value="">--Select--</option>';
            $.each(data, function(key, value) {
              htmlOption += '<option value="' + key + '">' + value + '</option>';
            });
            $('#block').html(htmlOption);
            $('#gp_ward').html('<option value="">--Select--</option>');
            $('#error_block').html('');
          },
          error: function(ex) {
            alert(sessiontimeoutmessage);
            window.location.href = base_url;
          }
        });
      }

    }
  });
  $('#block').change(function() {
    if ($(this).val() != '') {
      var block_code = $(this).val();
      //alert(block_code);
      var rural_urban = $('#urban_code').val();
      var error_found = 1;
      if (rural_urban == 2) {
        error_found = 0;
        var url = '{{ url("masterDataAjax/getGp") }}';
      } else if (rural_urban == 1) {
        error_found = 0;
        var url = '{{ url("masterDataAjax/getWard") }}';
      }

      if (error_found == 0) {
        $('#gp_ward').val('');
        $('#error_gp_ward').html('<img  src="{{ asset("images/ZKZg.gif") }}" width="50px" height="50px"/>');
        $.ajax({
          type: 'POST',
          url: url,
          data: {
            block_code: block_code,
            _token: '{{ csrf_token() }}',
          },
          success: function(data) {
            // console.log(data);
            var htmlOption = '<option value="">--Select--</option>';
            $.each(data, function(key, value) {
              htmlOption += '<option value="' + key + '">' + value + '</option>';
            });
            $('#gp_ward').html(htmlOption);
            $('#error_gp_ward').html('');
          },
          error: function(ex) {
            alert(sessiontimeoutmessage);
            window.location.href = base_url;
          }
        });
      }

    }
  });


    /***************************************************************/
  });


  function tab_highlight(tab_code) {

    var max_tab_code = $("#commonfield #max_tab_code").val();
    // alert(tab_code+'_'+max_tab_code);
    // alert(ff+'_'+tab_code);
    // alert(tab_code);

    if (tab_code <= (parseInt(max_tab_code) + 1)) {
      $('#list_personal_details').removeClass('active');
      $('#list_contact_details').removeClass('active');
      $('#list_bank_details').removeClass('active');
      $('#list_experience_details').removeClass('active');
      $('#list_decl_details').removeClass('active');
      $('#personal_details').removeClass('active');
      $('#contact_details').removeClass('active');
      $('#bank_details').removeClass('active');
      $('#experience_details').removeClass('active');
      $('#decl_details').removeClass('active');
      $('#list_personal_details').addClass('inactive_tab1');
      $('#list_contact_details').addClass('inactive_tab1');
      $('#list_bank_details').addClass('inactive_tab1');
      $('#list_experience_details').addClass('inactive_tab1');
      $('#list_decl_details').addClass('inactive_tab1');
      if (tab_code == 1) {
        personalTabActiveInactive(tab_code, 1);
        contactTabActiveInactive(tab_code, 0);
        bankTabActiveInactive(tab_code, 0);
        encolserTabActiveInactive(tab_code, 0);
        declarationTabActiveInactive(tab_code, 0);
      } else if (tab_code == 2) {
        contactTabActiveInactive(tab_code, 1);
        personalTabActiveInactive(tab_code, 0);
        bankTabActiveInactive(tab_code, 0);
        encolserTabActiveInactive(tab_code, 0);
        declarationTabActiveInactive(tab_code, 0);
      } else if (tab_code == 3) {
        bankTabActiveInactive(tab_code, 1);
        personalTabActiveInactive(tab_code, 0);
        contactTabActiveInactive(tab_code, 0);
        encolserTabActiveInactive(tab_code, 0);
        declarationTabActiveInactive(tab_code, 0);
      } else if (tab_code == 4) {
        encolserTabActiveInactive(tab_code, 1);
        personalTabActiveInactive(tab_code, 0);
        contactTabActiveInactive(tab_code, 0);
        bankTabActiveInactive(tab_code, 0);
        declarationTabActiveInactive(tab_code, 0);
      } else if (tab_code == 5) {
        declarationTabActiveInactive(tab_code, 1);
        personalTabActiveInactive(tab_code, 0);
        contactTabActiveInactive(tab_code, 0);
        encolserTabActiveInactive(tab_code, 0);
      }
      // alert(tab_code);
      if (parseInt(max_tab_code) >= 1) {
        $('#list_personal_details').removeClass('inactive_tab1');
        $('#list_personal_details').addClass('active_tab2');
        $('#list_contact_details').removeClass('inactive_tab1');
        // alert(tab_code);
      }
      if (parseInt(max_tab_code) >= 2) {
        $('#list_bank_details').removeClass('inactive_tab1');
        $('#list_bank_details').addClass('active_tab2');
      }
      if (parseInt(max_tab_code) >= 3) {
        $('#list_experience_details').removeClass('inactive_tab1');
        $('#list_experience_details').addClass('active_tab2');
      }
      if (parseInt(max_tab_code) >= 4) {
        $('#list_decl_details').removeClass('inactive_tab1');
        $('#list_decl_details').addClass('active_tab2');
      }
      if (parseInt(max_tab_code) >= 5) {
        
        $('#list_decl_details').removeClass('inactive_tab1');
        // $('#list_decl_details').removeClass('active_tab2');

      // $('#personal_details').addClass('active');
      $('#list_personal_details').addClass('active_tab2');
      }
    } else {
      return false;
    }
    return false;

  }

  function personalTabActiveInactive(cur_tab_code, status) {
    if (status == 1) {
      $('#personal_details').addClass('active');
      $('#list_personal_details').addClass('active active_tab2');
      $('#list_personal_details').attr('href', '#personal_details');
      $('#list_personal_details').attr('data-toggle', 'tab');
      $('#id_1').addClass('active');
    } else {
      // alert('ok');
      $('#id_1').removeClass('active');
      $('#id_1').removeClass('inactive_tab1');
      $('#list_personal_details').removeClass('active');
    }
  }

  function contactTabActiveInactive(cur_tab_code, status) {
    if (status == 1) {
      $('#contact_details').addClass('active');
      $('#list_contact_details').addClass('active active_tab2');
      $('#list_contact_details').attr('href', '#contact_details');
      $('#list_contact_details').attr('data-toggle', 'tab');
      $('#id_2').addClass('active');
    } else {
      //  alert('ok1');
      $('#id_2').removeClass('active');
      $('#id_2').removeClass('inactive_tab1');

      $('#list_contact_details').removeClass('active');
    }
  }

  function bankTabActiveInactive(cur_tab_code, status) {
    if (status == 1) {
      $('#bank_details').addClass('active');
      $('#list_bank_details').addClass('active active_tab2');
      $('#list_bank_details').attr('href', '#bank_details');
      $('#list_bank_details').attr('data-toggle', 'tab');
      $('#id_3').addClass('active');
    } else {
       $('#id_3').removeClass('active');
      $('#id_3').removeClass('inactive_tab1');
      $('#list_bank_details').removeClass('active');
    }
  }

  function encolserTabActiveInactive(cur_tab_code, status) {
    if (status == 1) {
      $('#experience_details').addClass('active');
      $('#list_experience_details').addClass('active active_tab2');
      $('#list_experience_details').attr('href', '#experience_details');
      $('#list_experience_details').attr('data-toggle', 'tab');
      $('#id_4').addClass('active');
    } else {
      $('#id_4').removeClass('active');
      $('#id_4').removeClass('inactive_tab1');
      $('#list_experience_details').removeClass('active');
    }
  }

  function declarationTabActiveInactive(cur_tab_code, status) {
    if (status == 1) {
      $('#decl_details').addClass('active');
      $('#list_decl_details').addClass('active active_tab2');
      $('#list_decl_details').attr('href', '#decl_details');
      $('#list_decl_details').attr('data-toggle', 'tab');
      $('#id_5').addClass('active');
    } else {
      $('#id_5').removeClass('active');
      $('#id_5').removeClass('inactive_tab1');
      $('#list_decl_details').removeClass('active');
    }
  }

  function printMsg(msg, msgtype, divid) {
    $("#" + divid).find("ul").html('');
    $("#" + divid).css('display', 'block');
    if (msgtype == '0') {
      //alert('error');
      $("#" + divid).removeClass('alert-success');
      //$('.print-error-msg').removeClass('alert-warning');
      $("#" + divid).addClass('alert-info');
    } else {
      $("#" + divid).removeClass('alert-info');
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