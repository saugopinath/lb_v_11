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
    top:40%;
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
      color: #d9534f;
      content:'*';
      font-weight: bold;
      margin-left: 5px;
      float:right;
      margin-top: 5px;
  }
  .loadingDivModal{
  position:absolute;
  top:0px;
  right:0px;
  width:100%;
  height:100%;
  background-color:#fff;
  background-image:url('images/ajaxgif.gif');
  background-repeat:no-repeat;
  background-position:center;
  z-index:10000000;
  opacity: 0.4;
  filter: alpha(opacity=40); /* For IE8 and earlier */
}
  .disabledcontent {
    pointer-events: none;
    opacity: 0.4;
  }
</style>

@extends('layouts.app-template-datatable')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Update Bank Details For Approved Beneficiary
    </h1>

  </section>
  <section class="content">
    <div class="box box-default" id="full-content">
      <div class="box-body">
        <div class="panel panel-default">
          <div class="panel-heading"><span id="panel-icon">Enter Beneficiary Details Here</div>
          <div class="panel-body" style="padding: 5px;">
            <div class="row">
              @if ( ($message = Session::get('success')))
              <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>

              </div>
              @endif
              @if(count($errors) > 0)
              <div class="alert alert-danger alert-block">
                <ul>
                  @foreach($errors->all() as $error)
                  <li><strong> {{ $error }}</strong></li>
                  @endforeach
                </ul>
              </div>
              @endif
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group col-md-3">
                  <label for="select_type">Search Using <span class="text-danger">*</span></label>
                  <select class="form-control select2" name="select_type" id="select_type">
                      <option value="">--- Select ---</option>
                       @foreach(Config::get('globalconstants.search_payment_status') as $key=> $search_type)
                          <option value="{{$key}}">{{$search_type}}</option>
                       @endforeach
                  </select>
                </div>
                <div class="form-group col-md-3" id="beneficiary_id_div" style="display: none;">
                  <label for="beneficiary">Beneficiary ID <span class="text-danger">*</span></label>
                  <input type="text" name="ben_id" id="ben_id" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Beneficiary ID">
                </div>
                <div class="form-group col-md-3" id="application_id_div" style="display: none;">
                  <label for="application">Application ID <span class="text-danger">*</span></label>
                  <input type="text" name="app_id" id="app_id" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Application ID">
                </div>
                <div class="form-group col-md-3" id="sasthyasathi_card_div" style="display: none;">
                  <label for="sasthasathi">Sasthasathi Card <span class="text-danger">*</span></label>
                  <input type="text" name="ss_card" id="ss_card" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Sasthyasathi Card Number">
                </div>
                <div class="form-group col-md-2" style="margin: 23px;">
                  <button class="btn btn-success" id="submit_btn" disabled><i class="fa fa-search"></i> Search</button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="panel panel-default" id="listing_div" style="display: none;">
          <div class="panel-heading" id="panel_head">List of beneficiaries
          </div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;">
            <div id="loadingDiv">
            </div>
            <div class="table-responsive">
              <table id="example" class="display" cellspacing="0" width="100%">
                <thead style="font-size: 12px;">
                  {{-- <th>Serial No</th> --}}
                  <th>Beneficiary ID</th>
                  <th>Beneficiary Name</th>
                  <th>Swasthya Sathi Card No. </th>
                  <th>Application Id</th>
                  <th>Address</th>
                  <th>Banking Information</th>
                  <th width="20%">Action</th>
                </thead>
                <tbody style="font-size: 14px;"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Update Bank Details Modal -->
    <div class="modal fade bd-example-modal-lg ben_bank_modal" tabindex="-1" role="dialog"
      aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header singleInfo">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Update Bank Details For Approved Beneficiary</h4>
          </div>
          <div class="modal-body ">
            <div class="loadingDivModal">
            </div>
          <input type="hidden" id="benId" name="benId" value="">
          <input type="hidden" id="application_id" name="application_id" value="">
          <input type="hidden" id="old_bank_ifsc" name="old_bank_ifsc" value="">
          <input type="hidden" id="old_bank_accno" name="old_bank_accno" value="">
            <div class="panel-group singleInfo" role="tablist" aria-multiselectable="true">
              <div class="panel panel-default">
                <div class="panel-heading active" role="tab" id="personal">
                  <h5 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePersonal" aria-expanded="true" aria-controls="collapsePersonal">Personal Details <span class="applicant_id_modal"></span></a> 
                  </h5> 
                </div> 
                <div id="collapsePersonal" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="personal">  
                  <div class="panel-body" style="padding: 5px;">
                    <table class="table table-bordered table-condensed table-striped" style="font-size: 14px;">  
                    <tbody>
                      <tr>
                        <th scope="row" width="20%">Swasthya Sathi Card No.</th>
                        <td id='sws_card_txt' width="30%"></td>
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
                        <th scope="row" class="caste" width="20%">SC/ST Certificate No.</th>
                        <td id="caste_certificate_no" class="caste" width="30%"></td>
                      </tr> 
                    </tbody>
                    </table>
                  </div>
                </div> 
              </div>
            </div>

            <div class="panel-group singleInfo">
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="contact">
                  <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseBank" aria-expanded="true" aria-controls="collapseBank">Update Bank Details</a> </h4>
                </div>
                <div id="collapseBank" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="banks">
                  <div class="panel-body">
                    <p style="font-size: 12px;font-weight: bold; text-align:center;">All (<span style="color:firebrick"> * </span>) marks filled are mandatory</p>
                    <table class="table table-bordered table-responsive" style="width:100%">
                      <tbody>
                        <tr>
                          <th scope="row" class="required" style="font-size: 14px;">Bank Branch Name</th>
                          <td id="branch_text"><input type="text" value="" name="branch_name" id="branch_name" readonly>
                          <span style="font-size: 14px;" id="error_bank_branch" class="text-danger"></span></td>
                          <th scope="row" class="required" style="font-size: 14px;">Bank IFSC Code</th>
                          <td id="bank_ifsc_text"><input type="text" value="" name="bank_ifsc" onkeyup="this.value = this.value.toUpperCase();" id="bank_ifsc">
                          <img src="{{ asset('images/ajaxgif.gif') }}" width="60px" id="ifsc_loader" style="display: none;">
                          <span style="font-size: 14px;" id="error_bank_ifsc_code" class="text-danger"></span></td>  
                        </tr>
                        <tr>
                          <th scope="row" class="required" style="font-size: 14px;">Bank Name</th>
                          <td id="bank_text"><input type="text" value="" name="bank_name" maxlength="200" id="bank_name" readonly>
                          <span style="font-size: 14px;" id="error_name_of_bank" class="text-danger"></span></td>
                          <th scope="row" class="required" style="font-size: 14px;">Bank Account Number</th>
                          <td id="bank_acc_text"> <input type="password" value="" name="bank_account_number" maxlength='20' id="bank_account_number">
                          <span style="font-size: 14px;" id="error_bank_account_number" class="text-danger"></span></td>
                        </tr>
                        <tr>
                          <th scope="row" class="required" style="font-size: 14px;">Confirm Bank Account Number</th>
                          <td colspan="3"><input type="text" name="confirm_bank_account_number" maxlength="20" id="confirm_bank_account_number" value="" class="form-control" style="border-radius: 3px; border: 1px solid #737373;">
                          <span style="font-size: 14px;" id="error_confirm_bank_account_number" class="text-danger"></span></td>
                        </tr>
                        <!-- Document Update Section -->
                        <tr>
                          <th scope="row" class="required" style="font-size: 14px;">Upload Bank Passbook</th>
                          <td id="bank_passbook_text"> 
                            <input type="file"  name="upload_bank_passbook" accept=".jpg,.jpeg,.png,.pdf" id="upload_bank_passbook" value="">
                            <span style="font-size: 14px;" id="error_file" class="text-danger"></span>
                          </td>
                          <th scope="row" style="font-size: 14px;">Copy Of Passbook</th>
                          <td  scope="row" class="encView">&nbsp;&nbsp;&nbsp;<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="View_encolser_modal('Copy of Bank Pass book','10',1)">View</a></td>
                        </tr>
                        <tr>
                          <th scope="row" class="required" style="font-size: 14px;">Remarks</th>
                          <td colspan="3"><input type="text" name="remarks" maxlength="100" id="remarks" class="form-control" value="" style="border-radius: 3px; border: 1px solid #737373;">
                          <span style="font-size: 14px;" id="error_remarks" class="text-danger"></span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div align="center">
              <button type="button"  class="btn btn-success btn-lg btnUpdate">Update</button>
            </div>
          </div>
          {{-- <div class="modal-footer" style="text-align: center">
            
          </div> --}}

        </div>
      </div>
    </div>

    <!-- Update Mobile Number Modal -->
    <div class="modal fade bd-example-modal-lg ben_mobile_modal" tabindex="-1" role="dialog"
      aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header singleInfo">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Update Mobile Number For Approved Beneficiary</h4>
          </div>
          <div class="modal-body ">
            <div class="loadingDivModal">
            </div>
          <input type="hidden" id="mobileBenId" name="mobileBenId" value="">
          <input type="hidden" id="mobileAppId" name="mobileAppId" value="">
          <input type="hidden" id="oldMobileNumber" name="oldMobileNumber" value="">
            <div class="panel-group singleInfo" role="tablist" aria-multiselectable="true">
              <div class="panel panel-default">
                <div class="panel-heading active" role="tab" id="personal">
                  <h5 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePersonalMobile" aria-expanded="true" aria-controls="collapsePersonalMobile">Personal Details <span class="applicant_id_modal"></span></a> 
                  </h5> 
                </div> 
                <div id="collapsePersonalMobile" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="personal">  
                  <div class="panel-body" style="padding: 5px;">
                    <table class="table table-bordered table-condensed table-striped" style="font-size: 14px;">  
                    <tbody>
                      <tr>
                        <th scope="row" width="20%">Swasthya Sathi Card No.</th>
                        <td id='sws_card_txt_MU' width="30%"></td>
                        <th scope="row" width="20%">Mobile No.</th>         
                        <td id="mobile_no_MU" width="30%"></td>
                      </tr>
                      <tr>       
                        <th scope="row" width="20%">Name</th>
                        <td id='ben_fullname_MU' width="30%"></td>
                        <th scope="row" width="20%">Gender</th>         
                        <td id="gender_MU" width="30%"></td>
                      </tr>
                      <tr>
                        <th scope="row" width="20%">DOB</th>         
                        <td id="dob_MU" width="30%"></td> 
                        <th scope="row" width="20%">Age</th>         
                        <td id="ben_age_MU" width="30%"></td>         
                      </tr>
                      <tr>
                        <th scope="row" width="20%">Caste:</th>
                        <td id="caste_MU" width="30%"></td>
                        <th scope="row" class="caste" width="20%">SC/ST Certificate No.</th>
                        <td id="caste_certificate_no_MU" class="caste" width="30%"></td>
                      </tr> 
                    </tbody>
                    </table>
                  </div>
                </div> 
              </div>
            </div>

            <div class="panel-group singleInfo">
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="contact">
                  <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseMobile" aria-expanded="true" aria-controls="collapseMobile">Update Mobile Number</a> </h4>
                </div>
                <div id="collapseMobile" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="banks">
                  <div class="panel-body">
                    <p style="font-size: 12px;font-weight: bold; text-align:center;">All (<span style="color:firebrick"> * </span>) marks filled are mandatory</p>
                    <table class="table table-bordered table-responsive" style="width:100%">
                      <tbody>
                        <tr>
                          <th scope="row" class="required" style="font-size: 14px;">Mobile Number</th>
                          <td id="bank_passbook_text"> 
                            <input type="text"  name="updateMobileNo" id="updateMobileNo" value="" maxlength="10" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;">
                            <span style="font-size: 14px;" id="error_update_mobile_no" class="text-danger"></span>
                          </td>
                        </tr>
                        <tr>
                          <th scope="row" class="required" style="font-size: 14px;">Remarks</th>
                          <td><input type="text" name="updateMobileRemarks" maxlength="100" id="updateMobileRemarks" class="form-control" value="" style="border-radius: 3px; border: 1px solid #737373;">
                          <span style="font-size: 14px;" id="error_mobile_no_remarks" class="text-danger"></span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div align="center">
              <button type="button"  class="btn btn-success btn-lg" id="btnUpdateMobileNo">Update</button>
            </div>
          </div>
          {{-- <div class="modal-footer" style="text-align: center">
            
          </div> --}}

        </div>
      </div>
    </div>

    <div class="modal encolser_modal" id="encolser_modal"  role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="encolser_name">Modal title</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div id="encolser_content">  </div>
         
          <div class="modal-footer"  style="text-align: center">
            <button type="button"  class="btn btn-success modalEncloseClose" >Close</button>
     
              
               <!-- </form>  -->
           </div> 
        </div>
      </div>
    </div>

  </section>
</div>


@endsection
@section('script')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
  $(document).ready(function() {   
    $('#loadingDiv').hide();
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #updateBankDetails').addClass("active"); 
    // $('.sidebar-menu #accValTrFailed').addClass("active");
    $('#submit_btn').removeAttr('disabled');
    $('#select_type').change(function(){
      var select_type = $('#select_type').val();
      if (select_type == 'B') {
        $('#beneficiary_id_div').show();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').hide();
        $('#app_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      }
      else if(select_type == 'A') {
        $('#beneficiary_id_div').hide();
        $('#application_id_div').show();
        $('#sasthyasathi_card_div').hide();
        $('#ben_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      }
      else if (select_type == 'S') {
        $('#beneficiary_id_div').hide();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').show();
        $('#ben_id').val('').trigger('change');
        $('#app_id').val('').trigger('change');
      }
      else{
        $('#beneficiary_id_div').hide();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').hide();
        $('#ben_id').val('').trigger('change');
        $('#app_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      }
    });

    var ajaxData = '';
    $('#submit_btn').click(function(){
      var select_type = $('#select_type').val();
      var beneficiary_id = $('#ben_id').val();
      var application_id = $('#app_id').val();
      var ss_card_no = $('#ss_card').val();
      if (select_type == 'B') {
        if (beneficiary_id == '') {
          $.alert({
            title: 'Alert!!',
            content: 'Please Enter Beneficiary Id'
          });
        }
        else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token:"{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      }
      else if(select_type == 'A') {
        if (application_id == '') {
          $.alert({
            title: 'Alert!!',
            content: 'Please Enter Application Id'
          });
        }
        else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token:"{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      }
      else if (select_type == 'S') {
        if (ss_card_no == '') {
          $.alert({
            title: 'Alert!!',
            content: 'Please Enter Sasthyasathi Card Number'
          });
        }
        else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token:"{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      }
      else{
        $.alert({
          title: 'Alert!!',
          content: 'All fields are required'
        });
      }
    });

    $('.modalEncloseClose').click(function(){
      $('.encolser_modal').modal('hide');
    });

    $('#bank_ifsc').blur(function(){
      $ifsc_data = $.trim($('#bank_ifsc').val());
      $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
      if($ifscRGEX.test($ifsc_data))
      {
        $('#bank_ifsc').removeClass('has-error');
        $('#error_bank_ifsc_code').text('');
        $('#ifsc_loader').show();
        $('.btnUpdate').attr('disabled',true);
        $.ajax({
          type: 'POST',
          url: "{{ route('bankIfsc') }}",
          data: {
            ifsc: $ifsc_data,
            _token: '{{ csrf_token() }}',
          },
          success: function (data) {
            $('#ifsc_loader').hide();
            $('.btnUpdate').removeAttr('disabled',true);
            if(data.status==2){
              $.confirm({
                    title: 'IFSC Not Found!',
                    type:'blue',
                    icon: 'fa fa-info',
                    content: 'This ' + $ifsc_data+' IFSC is not registered in our system.',
                    
            
                });
                $('#bank_ifsc').val('');
                return false;
            }
            else{
              $('#bank_name').val(data.bank_details.bank);
            $('#branch_name').val(data.bank_details.branch);
            }
          
           
          },
          error: function (ex) {
            $('#ifsc_loader').hide();
            $('#error_bank_ifsc_code').text('Data fetch error');
            $('#bank_ifsc').addClass('has-error');
          }
        });

      }else{
        $('#error_bank_ifsc_code').text('IFSC format invalid please check the code');
        $('#bank_ifsc').addClass('has-error');
      }
    });

    $('#upload_bank_passbook').change(function(){
      var card_file=document.getElementById("upload_bank_passbook");
      if(card_file.value!="")
      {
        var attachment;
        attachment = card_file.files[0];
        // console.log(attachment.type)
        var type = attachment.type;
        if(attachment.size>512000)
        {
          document.getElementById("error_file").innerHTML="<i class='fa fa-warning'></i> Unaccepted document file size. Max size 500 KB. Please try again";
          $('#upload_bank_passbook').val('');
          return false;
        }
        else if (type != 'image/jpeg' && type != 'image/png' && type != 'application/pdf') {
          document.getElementById("error_file").innerHTML="<i class='fa fa-warning'></i> Unaccepted document file format. Only jpeg,jpg,png and pdf. Please try again";
          $('#upload_bank_passbook').val('');
          return false;
        }
        else{
          $('#file_upload_btn').show();
          document.getElementById("error_file").innerHTML="";
        }
      }
    });
    $( "#bank_account_number,#confirm_bank_account_number" ).on( "copy cut paste drop", function() {
        return false;
    });
    
    // -------------------- Final Approve Section-------------------------- //
    $(document).on('click', '.btnUpdate', function() { 
      var error_name_of_bank =''; 
      var error_bank_branch =''; 
      var error_bank_account_number =''; 
      var error_bank_ifsc_code ='';
      var error_remarks = '';
      var error_file = ''; 

      if($.trim($('#bank_name').val()).length == 0)
      {
       error_name_of_bank = 'Name of Bank is required';
       $('#error_name_of_bank').text(error_name_of_bank);
       $('#bank_name').addClass('has-error');
      }
      else
      {
       error_name_of_bank = '';
       $('#error_name_of_bank').text(error_name_of_bank);
       $('#bank_name').removeClass('has-error');
      }

      if($.trim($('#branch_name').val()).length == 0)
      {
       error_bank_branch = 'Bank Branch is required';
       $('#error_bank_branch').text(error_bank_branch);
       $('#branch_name').addClass('has-error');
      }
      else
      {
       error_bank_branch = '';
       $('#error_bank_branch').text(error_bank_branch);
       $('#branch_name').removeClass('has-error');
      }

      if($.trim($('#bank_account_number').val()).length == 0)
      {
       error_bank_account_number = 'Bank Account Number is required';
       $('#error_bank_account_number').text(error_bank_account_number);
       $('#bank_account_number').addClass('has-error');
      }
      else
      {
       error_bank_account_number = '';
       $('#error_bank_account_number').text(error_bank_account_number);
       $('#bank_account_number').removeClass('has-error');
      }
      if($.trim($('#confirm_bank_account_number').val()).length == 0)
      {
        error_confirm_bank_account_number = 'Bank Account Number is required';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').addClass('has-error');
      }
      else
      {
        error_confirm_bank_account_number = '';
        $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
        $('#confirm_bank_account_number').removeClass('has-error');
      }
      if($.trim($('#bank_ifsc').val()).length == 0)
      {
       error_bank_ifsc_code = 'IFS Code is required';
       $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
       $('#bank_ifsc').addClass('has-error');
      }
      else
      {
       error_bank_ifsc_code = '';
       $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
       $('#bank_ifsc').removeClass('has-error');
      }

      $ifsc_data = $.trim($('#bank_ifsc').val());
      $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
      if($ifscRGEX.test($ifsc_data))
      {
        error_bank_ifsc_code = '';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc').removeClass('has-error');
      }
      else{
        error_bank_ifsc_code = 'Please check IFS Code format';
        $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
        $('#bank_ifsc').addClass('has-error');    
      }

      if($.trim($('#remarks').val()).length == 0)
      {
       error_remarks = 'Please add some remarks';
       $('#error_remarks').text(error_remarks);
       $('#remarks').addClass('has-error');
      }
      else
      {
       error_remarks = '';
       $('#error_remarks').text(error_remarks);
       $('#remarks').removeClass('has-error');
      }

      if($('#upload_bank_passbook')[0].files.length == 0)
      {
       error_file = 'Please add bank passbook copy';
       $('#error_file').text(error_file);
       $('#upload_bank_passbook').addClass('has-error');
      }
      else
      {
       error_file = '';
       $('#error_file').text(error_file);
       $('#upload_bank_passbook').removeClass('has-error');
      }
    // Check Bank Account Number with Confirm Bank Account Number
    if($.trim($('#bank_account_number').val()) != $.trim($('#confirm_bank_account_number').val()))
    {
      error_confirm_bank_account_number = 'Confirm Bank Account Number not Match with Bank Account Number';
      $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
      $('#confirm_bank_account_number').addClass('has-error');
    }
    else
    {
      error_confirm_bank_account_number = '';
      $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
      $('#confirm_bank_account_number').removeClass('has-error');
    }
      if(error_name_of_bank !='' || error_bank_branch !=''||  error_bank_account_number !='' || error_bank_ifsc_code !='' || error_remarks != '' || error_confirm_bank_account_number != '') { 
        return false;
      }
      else {
        var old_bank_ifsc=$('#old_bank_ifsc').val();
        var old_bank_accno=$('#old_bank_accno').val();
   
        var bank_ifsc=$('#bank_ifsc').val();
        var bank_account_number=$('#bank_account_number').val();
        var upload_bank_passbook = $('#upload_bank_passbook')[0].files;
        //&& upload_bank_passbook.length==0
        // if((bank_account_number == old_bank_accno) && (bank_ifsc == old_bank_ifsc)) {
        //   $.confirm({
        //     title: 'Alert!',
        //     type:'red',
        //     icon: 'fa fa-warning',
        //     content:'Account number and ifsc same as previous one',
        //   });
        //   return false;
        // }

        var bank_name=$('#bank_name').val();
        var branch_name=$('#branch_name').val();
        var remarks=$('#remarks').val();
        var benId=$('#benId').val();
        var token =  '{{csrf_token()}}'; 
        var fd= new  FormData();
        fd.append('benId', benId);
        fd.append('bank_ifsc', bank_ifsc);
        fd.append('bank_name', bank_name);
        fd.append('bank_account_number', bank_account_number);
        fd.append('branch_name', branch_name);
        fd.append('upload_bank_passbook', upload_bank_passbook[0]);
        fd.append('_token', token);
        fd.append('old_bank_ifsc',old_bank_ifsc);
        fd.append('old_bank_accno',old_bank_accno);
        fd.append('remarks',remarks);
        $('.loadingDivModal').show();
        $('.btnUpdate').attr('disabled',true);
        $.ajax({
          type: 'post',
          url: "{{route('updateApprovedBenBankDetails')}}",
          data: fd,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function (response) {
            $('.loadingDivModal').hide();
            $.confirm({
              title: response.title,
              type:response.type,
              icon: response.icon,
              content:response.msg,
              buttons: {
                Ok: function(){
                  $('.btnUpdate').removeAttr('disabled',true);
                  $('.ben_bank_modal').modal('hide');
                  $('#listing_div').hide();
                  $('#select_type').val('').trigger('change');
                  $("html, body").animate({ scrollTop: 0 }, "slow");
                }
              }
            });
          },
          complete: function(){
          //  $('.btnUpdate').removeAttr('disabled',true);
          },
          error: function (jqXHR, textStatus, errorThrown) {
            $('.btnUpdate').removeAttr('disabled',true);
            $('.loadingDivModal').hide();
            ajax_error(jqXHR, textStatus, errorThrown) 
          }
        });
      }
    });
    // -------------------- Final Approve Section --------------------------//

    // Mobile Number Update Section
    $(document).on('click', '#btnUpdateMobileNo', function() { 
      var error_update_mobile_no =''; 
      var error_mobile_no_remarks = '';

      if($.trim($('#updateMobileNo').val()).length == 0)
      {
       error_update_mobile_no = 'Mobile number is required';
       $('#error_update_mobile_no').text(error_update_mobile_no);
       $('#updateMobileNo').addClass('has-error');
      }
      else
      {
       error_update_mobile_no = '';
       $('#error_update_mobile_no').text(error_update_mobile_no);
       $('#updateMobileNo').removeClass('has-error');
      }

      if($.trim($('#updateMobileRemarks').val()).length == 0)
      {
       error_mobile_no_remarks = 'Please add some remarks';
       $('#error_mobile_no_remarks').text(error_mobile_no_remarks);
       $('#updateMobileRemarks').addClass('has-error');
      }
      else
      {
       error_mobile_no_remarks = '';
       $('#error_mobile_no_remarks').text(error_mobile_no_remarks);
       $('#updateMobileRemarks').removeClass('has-error');
      }
  
      if(error_update_mobile_no !='' || error_mobile_no_remarks !='') { 
        return false;
      }
      else {
        var old_mobile_no = $('#oldMobileNumber').val();
        var new_mobile_no = $('#updateMobileNo').val();
        var mobile_no_remarks = $('#updateMobileRemarks').val();
        if (old_mobile_no == new_mobile_no) {
          $.confirm({
            title: 'Alert!',
            type:'red',
            icon: 'fa fa-warning',
            content:'Your entered mobile number same as previous one.',
          });
          return false;
        }
        else {
          // alert('OK');
          var mobileAppId = $('#mobileAppId').val();
          var mobileBenId = $('#mobileBenId').val();
          $('.loadingDivModal').show();
          $('#btnUpdateMobileNo').attr('disabled',true);
          $.ajax({
            type: 'post',
            url: "{{route('updateApprovedBenMobileNumber')}}",
            data: { _token:'{{csrf_token()}}', benId:mobileBenId, appId:mobileAppId, newMobileNo:new_mobile_no, remarks:mobile_no_remarks },
            dataType: 'json',
            success: function (response) {
              $('.loadingDivModal').hide();
              $.confirm({
                title: response.title,
                type:response.type,
                icon: response.icon,
                content:response.msg,
                buttons: {
                  Ok: function(){
                    $('#btnUpdateMobileNo').removeAttr('disabled',true);
                    $('.ben_mobile_modal').modal('hide');
                    $('#listing_div').hide();
                    $('#select_type').val('').trigger('change');
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                  }
                }
              });
            },
            complete: function(){
            //  $('.btnUpdate').removeAttr('disabled',true);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#btnUpdateMobileNo').removeAttr('disabled',true);
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
    $('#submit_btn').attr('disabled',true);

    if ( $.fn.DataTable.isDataTable('#example') ) {
      $('#example').DataTable().destroy();
    }
    var dataTable = $('#example').DataTable( {
      dom: 'Blfrtip',
      "scrollX": true,
      "paging": false,
      "searchable": false,
      "ordering":false,
      "bFilter": false,
      "bInfo": false,
      "pageLength":20,
      'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
      "serverSide": true,
      "processing":true,
      "bRetrieve": true,
      "oLanguage": {
        // "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
      },
      "ajax": 
      {
        url: "{{ url('getLineListBankEdit') }}",
        type: "post",
        data: ajaxData,
        error: function (jqXHR, textStatus, errorThrown) {
          $('#loadingDiv').hide();
          $('#submit_btn').removeAttr('disabled');
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      },
      "initComplete":function(){
        $('#loadingDiv').hide();
        $('#submit_btn').removeAttr('disabled');
        //console.log('Data rendered successfully');
      },
      "columns": [
        // { "data": "DT_RowIndex" },
        { "data": "beneficiary_id" },
        { "data": "name" },
        { "data": "ss_card_no" },
        { "data": "application_id" },
        { "data": "address" },
        { "data": "bank_info" },
        { "data": "action" },
      ],

      "buttons": [
        // 'pdf','excel'
      ],
    });
  }
  function editFunction(beneficiary_id) {
    var select_item = $('#select_item_update_'+beneficiary_id).val();
    if (select_item == '') {
      $.alert({
        title: 'Alert!!',
        type: 'red',
        icon: 'fa fa-warning',
        content: 'Please select option which one do you want to edit'
      });
    }
    else if(select_item == 'bank') {
      $('#loadingDiv').show();
      $('.loadingDivModal').show();

      $.ajax({
        type: 'post',
        url: "{{route('getBenDataForBankUpdate')}}",
        data: {_token:'{{csrf_token()}}', benid:beneficiary_id},
        dataType: 'json',
        success: function (response) {
          // console.log(JSON.stringify(response));
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('#sws_card_txt').text(response.personaldata.ss_card_no);
          var mname=response.personaldata.ben_mname;
          if (!(mname)) { var mname='' }
          var lname=response.personaldata.ben_lname;
          if (!(lname)) { var lname='' }
          $('#ben_fullname').text(response.personaldata.ben_fname+' '+mname+' '+lname);
          $('#mobile_no').text(response.personaldata.mobile_no);
          $('#gender').text(response.personaldata.gender);
          $('#dob').text(response.dob);
          $('#ben_age').text(response.personaldata.age_ason_01012021);
          $('#caste').text(response.personaldata.caste);
          if(response.personaldata.caste=='SC' || response.personaldata.caste=='ST'){
            $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
            $('.caste').show();
          }
          else{
            $('.caste').hide();
          }

          $('.applicant_id_modal').html('(Beneficiary ID - '+response.personaldata.beneficiary_id+' , Application ID - '+response.personaldata.application_id+')');
          $('#application_id').val(response.personaldata.application_id);
          $('#benId').val(response.personaldata.beneficiary_id);
          $('#bank_ifsc').val(response.bank_ifsc);
          $('#bank_name').val(response.bank_name);
          $('#branch_name').val(response.branch_name);
          $('#bank_account_number').val(response.bank_code);
          $('#confirm_bank_account_number').val(response.bank_code);
          $('#old_bank_ifsc').val(response.bank_ifsc)
          $('#old_bank_accno').val(response.bank_code)
          $('#upload_bank_passbook').val('');
          $('#remarks').val('');
          $('.ben_bank_modal').modal('show');
        },

        error: function (jqXHR, textStatus, errorThrown) {
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('.ben_bank_modal').modal('hide');
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      });
      // $('.ben_bank_modal').modal('show');
    }
    else if (select_item == 'mobile') {
      $('#loadingDiv').show();
      $('.loadingDivModal').show();
      $.ajax({
        type: 'post',
        url: "{{route('getBenDataForBankUpdate')}}",
        data: {_token:'{{csrf_token()}}', benid:beneficiary_id},
        dataType: 'json',
        success: function (response) {
          console.log(JSON.stringify(response));
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('#sws_card_txt_MU').text(response.personaldata.ss_card_no);
          var mname=response.personaldata.ben_mname;
          if (!(mname)) { var mname='' }
          var lname=response.personaldata.ben_lname;
          if (!(lname)) { var lname='' }
          $('#ben_fullname_MU').text(response.personaldata.ben_fname+' '+mname+' '+lname);
          $('#mobile_no_MU').text(response.personaldata.mobile_no);
          $('#gender_MU').text(response.personaldata.gender);
          $('#dob_MU').text(response.dob);
          $('#ben_age_MU').text(response.personaldata.age_ason_01012021);
          $('#caste_MU').text(response.personaldata.caste);
          if(response.personaldata.caste=='SC' || response.personaldata.caste=='ST'){
            $('#caste_certificate_no_MU').text(response.personaldata.caste_certificate_no);
            $('.caste').show();
          }
          else{
            $('.caste').hide();
          }
          $('.applicant_id_modal').html('(Beneficiary ID - '+response.personaldata.beneficiary_id+' , Application ID - '+response.personaldata.application_id+')');
          $('#oldMobileNumber').val(response.personaldata.mobile_no);
          $('#updateMobileNo').val(response.personaldata.mobile_no);
          $('#mobileAppId').val(response.personaldata.application_id);
          $('#mobileBenId').val(response.personaldata.beneficiary_id);
          $('.ben_mobile_modal').modal('show');
        },

        error: function (jqXHR, textStatus, errorThrown) {
          $('.loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('.ben_mobile_modal').modal('hide');
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      });
    }
  }
  function View_encolser_modal(doc_name,doc_type,is_profile_pic){
    var application_id=$('#application_id').val();
    var benId=$('#benId').val();
    $('#encolser_name').html('');
    $('#encolser_content').html('');
    $('#encolser_name').html(doc_name+'('+benId+')');
    $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
    $('.loadingDivModal').show();
    $('.btnUpdate').attr('disabled',true);
    $.ajax({
      url: "{{ route('ajaxViewPassbook') }}",
      type: "POST",
       data: {
       doc_type: doc_type,
       is_profile_pic: is_profile_pic,
       application_id: application_id,
       _token: '{{ csrf_token() }}',
       },
      }).done(function( data, textStatus, jqXHR ) {
        $('.btnUpdate').removeAttr('disabled',true);
        $('.loadingDivModal').hide();
      $('#encolser_content').html('');
      $('#encolser_content').html(data);
      $("#encolser_modal").modal();
      }).fail(function( jqXHR, textStatus, errorThrown ) {
        $('#encolser_content').html('');
        $('.btnUpdate').removeAttr('disabled',true);
        $('.loadingDivModal').hide();
        ajax_error(jqXHR, textStatus, errorThrown)
      });
  }
</script>
@stop