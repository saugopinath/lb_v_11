<style type="text/css">
  .required-field::after {
    content: "*";
    color: red;
  }
  .has-error
  {
    border-color:#cc0000;
    background-color:#ffff99;
  }
  .preloader1{
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
	border:0;
}
.panel-title>a, .panel-title>a:active{
	display:block;
	padding:10px;
  color:#555;
  font-size:14px;
  font-weight:bold;
	text-transform:uppercase;
	letter-spacing:1px;
  word-spacing:3px;
	text-decoration:none;
}
.panel-heading  a:before {
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
#enCloserTable tbody tr td{
  padding:10px 10px 10px 10px;
}

.modal-open {
overflow: visible !important;
}
</style>

@extends('layouts.app-template-datatable_new')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Process Application Faulty
    </h1>
   
  </section>
  <section class="content">
    <div class="box box-default">
      <div class="box-body">
              <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

        <div class="panel panel-default">
          <div class="panel-heading"><span id="panel-icon">Applications Yet To Be Approved</div>
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
             
                <div class="col-md-3">
                  <label class="control-label">Rural/Urban </label>
                  <select name="filter_1" id="filter_1" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach ($levels as $key=>$value)
                    <option value="{{$key}}"> {{$value}}</option>
                    @endforeach
                  </select>
                 
                </div>
                <div class="col-md-3">
                  <label class="control-label" id="blk_sub_txt">Block/Sub Division </label>
                  <select name="filter_2" id="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                 
                </div>
          	<div class="col-md-3" id="municipality_div" style="display:none;">
                                    <label class="control-label">Municipality</label>
                                    <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                                        <option value="">-----All----</option>
                                       

              </select>

            </div>
             <div class="form-group col-md-3" style="display:none;" id="gp_ward_div">
                  <label class=" control-label" id="gp_ward_txt">GP/Ward</label>
                  <select name="gp_ward_code" id="gp_ward_code" class="form-control">
                    <option value="">-----Select----</option>


                  </select>
                </div>
             <div class="form-group col-md-4">
                          <label class="">Caste</label>
                          <select class="form-control" name="caste_category" id="caste_category" >
                          <option value="">--All--</option>
                          @foreach(Config::get('constants.caste_lb') as $key=>$val)
                            <option value="{{$key}}">{{$val}}</option>
                          @endforeach 
                          </select>
                          <span id="error_caste_category" class="text-danger"></span>
              </div>
              </div>
              <div class="row">
                <div class="col-md-2">
                  <label class=" control-label">&nbsp; </label>
                  <button type="button" name="filter" id="filter" class="btn btn-success">Search</button>
                  
                 
                </div>
                <div class="col-md-offset-2">
                  <label class=" control-label">&nbsp; </label>
                
                  <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>
                 
                </div>
                
              
            </div>
            <br>
<hr>
            <div class="row">
             
              <div class="form-group col-md-offset-3 col-md-3 " style="display: none;" id="approve_rejdiv">
                <button type="button" name="bulk_approve" class="btn btn-success btn-lg" id="bulk_approve" value="approve">
                  Approve/Reject</button>
    
              </div>
           
            </div>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading" id="panel_head">List of New Applicants</div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;">
            <div class="table-responsive">
              {{-- <div class="form-group" style="font-weight:bold; font-size:25px;">
                <label class="control-label">Check All</label>
              <input type="checkbox" id='check_all_btn' style="width:48px;">
              </div> --}}
               <table id="example" class="display" cellspacing="0" width="100%"> 
                <thead style="font-size: 12px;">
                  <th>Application ID</th>
                  <th>Applicant Name</th>
                  <th>Age (as on {{$dob_base_date}})</th>
                  <th>Bank Account No.</th>
                  <th>Mobile No</th>
                  <th>Action</th>
                  <th >Check <input type="checkbox" id='check_all_btn' style="width:48px;"></th>
                </thead>
                <tbody style="font-size: 14px;"></tbody>   
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade bd-example-modal-lg ben_view_modal"   tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header singleInfo">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h3 class="modal-title">Applicant Details (<span class="applicant_id_modal"></span>)</h3>
          </div>
          <div class="modal-body ben_view_body">
           <div class="panel-group singleInfo" role="tablist" aria-multiselectable="true">
           <div class="panel panel-default"><div class="panel-heading active" role="tab" id="personal">
           <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_personal"></div>
           <h4 class="panel-title">
           <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePersonal" aria-expanded="true" aria-controls="collapsePersonal">Personal Details</a> </h4> </div> 
           <div id="collapsePersonal" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="personal">  
           <div class="panel-body"><table class="table table-bordered">  
          <tbody>
          
          
           <tr>       
           <th scope="row">Name</th><td id='ben_fullname'></td>
           </tr>
           <tr>
           <th scope="row">Mobile No.</th>         
           <td id="mobile_no"></td>      
          
          </tr>
           <tr>
           <th scope="row">Swasthya Sathi Card No.</th>
           <td id='sws_card_txt'></td>
           <th scope="row">Aadhaar No.</th>
            <td id="aadhar_no_encrypt"></td>
            <td id="aadhar_no_original" style="display:none;"></td>
            <td id="aadhar_no_or"><button class="btn btn-info showhideAadhar" id="show_hide_aadhar" >Show Original Aadhaar</button></td>
           </tr>
           <tr>
           
           <th scope="row">DOB</th>         
           <td id="dob"></td> 
           <th scope="row">Age (as on {{$dob_base_date}})</th>         
           <td id="ben_age"></td>         
           </tr>
         
           <tr>
           <th scope="row">Caste:</th>
           <td id="caste"></td>
           <th scope="row" class="caste">SC/ST Certificate No.</th>
           <td id="caste_certificate_no"  class="caste"></td>
           </tr> 
          
           
            </tbody>
            </table>
            </div>
            </div> 
            </div>
            </div>
            
            <div class="panel-group singleInfo"> <div class="panel panel-default">   
            <div class="panel-heading" role="tab" id="contact"> 
            <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_contact"></div>

             <h4 class="panel-title"> 
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseContact" aria-expanded="false" aria-controls="collapseContact">Address Details</a> </h4> </div>  
            <div id="collapseContact" class="panel-collapse collapse" role="tabpanel" aria-labelledby="contact"> 
             <div class="panel-body">   
             <table class="table table-bordered">  
            <tbody>
             <tr><th scope="row">District Name</th>         
             <td id="dist_name"></td>
            
             </tr>
             <tr> 
             <th scope="row">Block/Municipality Name</th>
             <td id="block_ulb_name"></td>  
            </tr>
             <tr> 
             <th scope="row">Gp/Ward Name</th>
             <td id="gp_ward_name"></td>         
             </tr>
             
             </tbody>
             </table>
             </div> 
             </div> 
             </div>  
             </div>
             <div class="panel-group singleInfo"><div class="panel panel-default">   
            <div class="panel-heading" role="tab" id="bank">
           <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_bank"></div>
             <h4 class="panel-title"> 
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseBank" aria-expanded="false" aria-controls="collapseBank">Bank Details </a> </h4> </div> 
            <div id="collapseBank" class="panel-collapse collapse" role="tabpanel" aria-labelledby="bank">  
            <div class="panel-body">   
            <table class="table table-bordered">
            <tbody>
            <tr>       
            <th scope="row">Bank Name</th>
            <td id="bank_name"></td>       
            <th scope="row">Branch Name</th>
            <td id="branch_name"></td></tr>   
            <tr><th scope="row">Bank IFSC</th>
            <td id="bank_ifsc"></td>
            <th scope="row">Bank Account No.</th>         
            <td id="bank_code"></td></tr></tbody>  
            </table> 
            </div> 
            </div> 
            </div> 
            </div> 
            <div class="panel-group singleInfo"><div class="panel panel-default">   
            <div class="panel-heading" role="tab" id="encloser">
           <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_encolser"></div>
             <h4 class="panel-title"> 
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseEncloser" aria-expanded="false" aria-controls="collapseEncloser">Enquiry Details </a> </h4> </div> 
            <div id="collapseEncloser" class="panel-collapse collapse" role="tabpanel" aria-labelledby="encloser">  
            <div class="panel-body">   
            <table id="enCloserTable" class="table table-bordered"> 
            <tbody>
            <tr>       
            <th scope="row">Enquiring Officer Name</th>
            <td id="invs_name"></td>   
            </tr>
            <tr>    
            <th scope="row">Enquiring Officer Designation</th>
            <td id="invs_designation"></td></tr>   
           
            <tr>    
           <th scope="row">Uploaded Enquiry Report</th>
          <td scope="row" class="encView">&nbsp;&nbsp;&nbsp;<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="View_encolser_modal('Uploaded Enquiry Report',500,0)">View</a></td>    
          
          </tr>
           <tr><td colspan="2"> <label>
                              <i class="fa fa-check" aria-hidden="true"></i>The Enquiring officer has been satisfied that the applicant is eligible for  Lakshmir Bhandar scheme  and for <br/>1. Swasthya sathi 2. Aadhar Card 3. Caste
                               <br/>
          </label></td>
          </tr>
           <tr><td colspan="2"> <label>
                              <i class="fa fa-check" aria-hidden="true"></i>The Verifying officer has been satisfied that the applicant is eligible for  Lakshmir Bhandar scheme  and for <br/>1. Swasthya sathi 2. Aadhar Card 3. Caste
                               <br/>
          </label></td>
          </tr>
            </tbody></table>
            </div> 
            </div> 
            </div> 
            </div> 
             <div class="panel-group">  
             <div class="panel panel-default">   <div class="panel-heading" role="tab" id="headingFour">   
             <h4 class="panel-title"> <a>Action</a> </h4> </div> <div id="collapse4" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingFour">  
             <div class="panel-body">
            
              <div class="form-group col-md-4"><label class="required" for="reject_cause">Select Operation</label>
             <select name="opreation_type" id="opreation_type" class="form-control opreation_type">
              @if($approval_allowded)
             <option value="A" selected="">Approve</option>
             @endif
             <option value="R">Rejected</option>
             <option value="T">Revert</option>
             </select>
             </div>
              <div class="form-group col-md-4" style="display:none;" id="div_rejection"><label class="required" for="reject_cause">Select Reject/Reverted Cause</label>
             <select name="reject_cause" id="reject_cause" class="form-control">
             <option value="">--Select--</option>
             @foreach($reject_revert_reason as $r_arr)
                            <option value="{{$r_arr->id}}">{{$r_arr->reason}}</option>
              @endforeach 
             </select>
             </div> 
             <div class="form-group col-md-4">
             <label class="" for="heading">Enter Remarks</label><textarea style="margin: 0px; width: 279px; height: 40px;" name="accept_reject_comments" id="accept_reject_comments"></textarea>
             </div></div> 
             </div> </div>  </div> 
          </div>
          <div class="modal-footer">
            {{-- <button style="text-align:left" type="button" class="btn btn-primary">Save changes</button>
            <button type="button" id="modal_cls" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
            <form method="POST" action="{{ route('application-details-read-only')}}" target="_blank" name="fullForm" id="fullForm">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <input type="hidden" name="is_bulk" id="is_bulk" value="0" />
               <input type="hidden" id="scheme_id" name="scheme_id" value="{{$scheme_id}}"/>
              <input type="hidden" id="id" name="id"/>
              <input type="hidden" id="application_id" name="application_id"/>
              <input type="hidden" id="is_draft" name="is_draft" value="1"/>
              <input type="hidden" id="caste" name="caste"/>

              <input type="hidden" name="applicantId[]" id="applicantId" value="" />

  
  
               <button type="button" style="float: right" class="btn btn-success" id="verifyReject">Approve</button>
  
              <button style="display:none;" type="button" id="submitting" value="Submit" class="btn btn-success success"
                            disabled>Processing Please Wait</button>
              </form> 
          </div>
        
        </div>
      </div>
    </div>
    <div class="modal" id="encolser_modal"  role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="encolser_name">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="encolser_content">  </div>
     
           
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
   var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
  var base_url='{{ url('/') }}';
 // $('.panel-collapse').on('show.bs.collapse', function () {

  // $("#check_all_btn").click(function () {
  //       $('#example tbody input[type="checkbox"]').prop('checked', this.checked);
  //   });
    $(document).on('show.bs.collapse', '.panel-collapse', function() {
   
    
  
    var id=$(this).attr('id');
    var application_id=$('#fullForm #application_id').val();
     //alert(application_id);
    if(id=='collapsePersonal'){
      $('#ben_fullname').text('');
      $('#caste').text('');
      $('#caste_certificate_no').text('');
      $('#gender').text('');
      $('#ben_age').text('');
      $('#mobile_no').text('');
      $('#sws_card_txt').text('');
      $('#aadhar_no_encrypt').text('');
      $('#dob').text('');
        $('#loader_img_personal').show();
         $.ajax({
            type: 'post',
            url: "{{route('getBenViewPersonalDataFaulty')}}",
            data: {_token:'{{csrf_token()}}',benid:application_id},    
            dataType: 'json',
            success: function (response) {
               // alert(response.personaldata.ss_card_no);
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              if(response.personaldata.ben_mname!== undefined && response.personaldata.ben_mname!==null){
                //console.log((response.personaldata.ben_mname);
                      var ben_mname=response.personaldata.ben_mname;
              }
              else{
                var ben_mname="";
              }
              if(response.personaldata.ben_lname!== undefined && response.personaldata.ben_lname!==null){
                      var ben_lname=response.personaldata.ben_lname;
              }
              else{
                var ben_lname="";
              }
              $('#ben_fullname').text(response.personaldata.ben_fname+' '+ben_mname+' '+ben_lname);              
              $('#mobile_no').text(response.personaldata.mobile_no);
              $('#sws_card_txt').text(response.personaldata.ss_card_no);
              $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
              if(response.personaldata.aadhar_no!='' && response.personaldata.aadhar_no!='********'){
                // alert('ok1');
                 $('#aadhar_no_original').show();
                 $('#aadhar_no_or').show();
              }
              else{
                //alert('ok2');
                 $('#aadhar_no_original').hide();
                 $('#aadhar_no_or').hide();
              }
              $('#gender').text(response.personaldata.gender);
              $('#dob').text(response.personaldata.formatted_dob);
              $('#ben_age').text(response.personaldata.age_ason_01012021);
              $('#caste').text(response.personaldata.caste);
              if(response.personaldata.caste=='SC' || response.personaldata.caste=='ST'){
                $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
                $('.caste').show();
              }
              else{
                $('.caste').hide();
              }
                
                
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
    }
    else if(id=='collapseContact'){
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
            url: "{{route('getBenViewContactDataFaulty')}}",
            data: {_token:'{{csrf_token()}}',benid:application_id},    
            dataType: 'json',
            success: function (response) {
              //console.log(response.contactdata);
              $('#loader_img_contact').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              $('#dist_name').text(response.contactdata.dist_name);
              $('#block_ulb_name').text(response.contactdata.block_ulb_name);
              $('#gp_ward_name').text(response.contactdata.gp_ward_name);               

            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_contact').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
    }
    else if(id=='collapseBank'){
        $('#loader_img_bank').show();
        $('#bank_name').text('');
        $('#branch_name').text('');
        $('#bank_ifsc').text('');
        $('#bank_code').text('');
         $.ajax({
            type: 'post',
            url: "{{route('getBenViewBankDataFaulty')}}",
            data: {_token:'{{csrf_token()}}',benid:application_id},    
            dataType: 'json',
            success: function (response) {
              $('#loader_img_bank').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              $('#bank_name').text(response.bankdata.bank_name);
              $('#branch_name').text(response.bankdata.branch_name);
              $('#bank_ifsc').text(response.bankdata.bank_ifsc);
              $('#bank_code').text(response.bankdata.bank_code);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_bank').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
    }else if(id=='collapseEncloser'){
        $('#loader_img_encolser').show();   
         $('#invs_name').text('');
        $('#invs_designation').text(''); 
         $.ajax({
            type: 'post',
            url: "{{route('getBenViewInvestigatorData')}}",
            data: {_token:'{{csrf_token()}}',benid:application_id},    
            dataType: 'json',
            success: function (response) {
              $('#loader_img_encolser').hide();
              //console.log(response.html);
              $('.ben_view_button').removeAttr('disabled',true);
              $('#invs_name').text(response.personaldata.enquiry_officer_name);
              $('#invs_designation').text(response.personaldata.enquiry_officer_designation);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_encolser').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              alert(sessiontimeoutmessage);
              window.location.href=base_url;
            }
            });
    }
    $(this).siblings('.panel-heading').addClass('active');
   
  });
  $(document).on('hide.bs.collapse', '.panel-collapse', function() {
  //$('.panel-collapse').on('hide.bs.collapse', function () {
    $(this).siblings('.panel-heading').removeClass('active');
   
  });
  $('.loader_img').hide();
  $('.sidebar-menu li').removeClass('active');
  $('.sidebar-menu #lk-main').addClass("active"); 
  $('.sidebar-menu #processApplicationFaulty').addClass("active"); 
  var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
  var base_url='{{ url('/') }}';
  var approval_allowded='{{$approval_allowded}}';
  if(approval_allowded==1){
  $('#opreation_type').val('A');
  $("#verifyReject").html("Approve");
  }
  else{
    // alert(verification_allowded);
    $('#opreation_type').val('R');
     $("#verifyReject").html("Reject");
    $('#div_rejection').show();
  }
   var dataTable = "";
   if ( $.fn.DataTable.isDataTable('#example') ) {
          $('#example').DataTable().destroy();
        }
         dataTable=$('#example').DataTable( {
          dom: 'Blfrtip',
          "scrollX": true,
          "paging": true,
          "searchable": true,
          "ordering":false,
          "bFilter": true,
          "bInfo": true,
          "pageLength":20,
          'lengthMenu': [[20,50,100], [20,50,100]],
          "serverSide": true,
          "processing":true,
          "bRetrieve": true,         
          "oLanguage": {
            "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>',
             "sSearch": "<span>Search by Application Id/Mobile No./Bank Account No.</span>" //search
            
          },
          "ajax": 
          {
            url: "{{ url('workflowFaulty') }}",
            type: "get",
            data:function(d){
                 d.filter_1= $("#filter_1").val(),
                 d.filter_2= $("#filter_2").val(),
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
            { "data": "id" },
            { "data": "name" },
            { "data": "age" },
            { "data": "bank_code" },
            { "data": "mobile_no" },
            { "data": "view" },
            { "data": "check" }
          ],
       "columnDefs": [
            {
                "targets": [ 4],
                "visible": false,
                "searchable": true
            },
            {
      "targets": [ 6 ],
      "orderable": false,
      "searchable": true
    }
       ],
          "buttons": [
            {
           extend: 'pdf',
         
           title: 'Process Application Faulty Report  <?php echo date('d-m-Y');  ?>',
           messageTop:'Date:<?php echo date('d/m/Y');  ?>',
           footer: true,
           pageSize:'A4',
          // orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
            columns: [0,1,2,3,4],

            }
       },
            {
           extend: 'excel',
         
           title: 'Process Application Faulty Report <?php echo date('d-m-Y');  ?>',
           messageTop:'Date:<?php echo date('d/m/Y');  ?>',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
            format: {
                     body: function (data, row, column, node ) {
                                return  column===3 ? "\0" + data : data;
                                }
              },
                 columns: [0,1,2,3,4],
                stripHtml: false,
            }
       },
          
          ],
        });
        $('#example').on( 'length.dt', function ( e, settings, len ) {
          $("#check_all_btn").prop("checked", false); 
} );
  
  $('#check_all_btn').on('change', function () {
     
     
    var checked = $(this).prop('checked');
    
    dataTable.cells(null, 6).every( function () {
      var cell = this.node();
      $(cell).find('input[type="checkbox"][name="chkbx"]').prop('checked', checked); 
    } );
    var data = dataTable
    .rows( function ( idx, data, node ) {
        return $(node).find('input[type="checkbox"][name="chkbx"]').prop('checked');
    } )
    .data()
    .toArray();
    //console.log(data);
    if(data.length === 0){
      $("input.all_checkbox").removeAttr("disabled", true);
     
    }
    else{
      $("input.all_checkbox").attr("disabled", true);
    }
    var anyBoxesChecked = false;
     var applicantId=Array();
     $('input[type="checkbox"][name="chkbx"]').each(function( index,value ) {
     
      if ($(this).is(":checked")) {
        anyBoxesChecked = true;
        applicantId.push(value.value);
      }
 
});
   
     $("#fullForm #applicantId").val($.unique(applicantId));
    if (anyBoxesChecked == true) {
      $('#approve_rejdiv').show();
       $('.ben_view_button').attr('disabled',true);
      document.getElementById('bulk_approve').disabled = false;
      // document.getElementById('bulk_blkchange').disabled = false;
    } else{
      $('#approve_rejdiv').hide();
      $('.ben_view_button').removeAttr('disabled',true);
      document.getElementById('bulk_approve').disabled = true;
      // document.getElementById('bulk_blkchange').disabled = true;
    }
    //console.log(applicantId);
  });
    $(document).on('click', '.ben_view_button', function() {
      $('#fullForm #application_id').val('');
      $('#fullForm #caste').val('');
      $('#loader_img_personal').show();
      $(".singleInfo").show();
      $('.ben_view_button').attr('disabled',true);
      var benid=$(this).val();
      $('#fullForm #application_id').val(benid);
      $('.applicant_id_modal').html(benid);
      $("#collapseContact").collapse('hide');
      $("#collapseBank").collapse('hide');
      $("#collapseEncloser").collapse('hide');      
      $('#ben_fullname').text('');
        $('#caste').text('');
        $('#caste_certificate_no').text('');
        $('#gender').text('');
        $('#ben_age').text('');
        $('#mobile_no').text('');
        $('#sws_card_txt').text('');
        $('#aadhar_no_encrypt').text('');
        $('#dob').text('');
        $('#dist_name').text('');
        $('#block_ulb_name').text('');
        $('#gp_ward_name').text('');
        $('#bank_name').text('');
        $('#branch_name').text('');
        $('#bank_ifsc').text('');
        $('#bank_code').text('');
        $('#invs_name').text('');
        $('#invs_designation').text('');
      $.ajax({
            type: 'post',
            url: "{{route('getBenViewPersonalDataFaulty')}}",
            data: {_token:'{{csrf_token()}}',benid:benid},
           
            dataType: 'json',
            success: function (response) {
            $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
             
              if(response.personaldata.ben_mname!== undefined && response.personaldata.ben_mname!==null){
                //console.log((response.personaldata.ben_mname);
                      var ben_mname=response.personaldata.ben_mname;
              }
              else{
                var ben_mname="";
              }
              if(response.personaldata.ben_lname!== undefined && response.personaldata.ben_lname!==null){
                      var ben_lname=response.personaldata.ben_lname;
              }
              else{
                var ben_lname="";
              }
               $('#ben_fullname').text(response.personaldata.ben_fname+' '+ben_mname+' '+ben_lname);              
                $('#mobile_no').text(response.personaldata.mobile_no);
                $('#sws_card_txt').text(response.personaldata.ss_card_no);
                $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
                if(response.personaldata.aadhar_no!=''){
                  // alert('ok1');
                   $('#aadhar_no_original').show();
                   $('#aadhar_no_or').show();
                }
                else{
                  //alert('ok2');
                   $('#aadhar_no_original').hide();
                   $('#aadhar_no_or').hide();
                }
                $('#gender').text(response.personaldata.gender);
                $('#dob').text(response.personaldata.formatted_dob);
                $('#ben_age').text(response.personaldata.age_ason_01012021);
                $('#caste').text(response.personaldata.caste);
                if(response.personaldata.caste=='SC' || response.personaldata.caste=='ST'){
                  $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
                  $('.caste').show();
                }
                else{
                  $('.caste').hide();
                }
               
              $('#fullForm #id').val(response.benid);
              $('#fullForm #caste').val(response.personaldata.caste);
             

            },
            complete: function(){
              
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
            alert(sessiontimeoutmessage);
          window.location.href=base_url;
            }
            });
      $('.ben_view_modal').modal('show');

    });
  

   

     $('#filter_1').change(function() {
       var filter_1=$(this).val();
       
        $('#filter_2').html('<option value="">--All --</option>');
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
        $('#filter_2').html(htmlOption);
       
    });
    $('#filter_2').change(function() {
       var rural_urbanid= $('#filter_1').val();
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
      var urban_code=$("#filter_1").val();
      if(district==''){
        $('#filter_1').val('');
        $('#filter_2').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        
    }
    if(urban_code==''){
        alert('Please Select Rural/Urban First');
        $('#filter_2').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        $("#filter_1").focus();
    }
    if(muncid!=''){
        var rural_urbanid= $('#filter_1').val();
         
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
       window.location.href='workflowFaulty?pr1=lb_wcd';  
    });
  $('.showhideAadhar').click(function(){
    var ButtonText = $(this).text();
      if(ButtonText=='Show Original Aadhaar'){
           $("#aadhar_no_encrypt").hide();
           var applicant_id_modal=$(".applicant_id_modal").text();
           $("#aadhar_no_original").show();
           $('#aadhar_no_original').html('<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');

            $.ajax({
            type: 'post',
            url: "{{route('getBenViewAadharDataFaulty')}}",
            data: {_token:'{{csrf_token()}}',benid:applicant_id_modal},
            dataType: 'json',
            success: function (response) {
             // alert(response.aadhar_no);
              $('#aadhar_no_original').html('');
              $('#aadhar_no_original').html(response.aadhar_no);
              $("#show_hide_aadhar").text('Show Encrypted Aadhaar');
              $("#aadhar_no_original").show();

            },
            complete: function(){
               //$('#aadhar_no_original').html('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
               $('#aadhar_no_original').html('');
              $('.ben_view_button').removeAttr('disabled',true);
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
    $('#encolser_modal').on('hidden.bs.modal', function (e) {
     $('.ben_view_modal').css({
    'overflow': 'auto',
    });
     //$(".ben_view_modal").animate({ scrollTop: 0 }, "slow");
   });
  $(document).on('click', '.opreation_type', function() {
       if($(this).val()=='T' || $(this).val()=='R'){
        $('#div_rejection').show();
        if($(this).val()=='T')
        $("#verifyReject").html("Revert");
        else if($(this).val()=='R')
        $("#verifyReject").html("Reject");
       }
       else{
         $("#verifyReject").html("Approve");
          $('#div_rejection').hide();
          $("#reject_cause").val('');
       }
    });

  $('#bulk_approve').click(function(){
     $(".singleInfo").hide();
     $("#fullForm #is_bulk").val(1);
     $('#fullForm #id').val('');
     $('#fullForm #application_id').val('');
     benid="";
    
     $('.ben_view_modal').modal('show');
});
      $(document).on('click', '#verifyReject', function() { 
      
        var scheme_id = $('#scheme_id').val();
        var reject_cause = $('#reject_cause').val();
        var opreation_type = $('#opreation_type').val();
        var accept_reject_comments = $('#accept_reject_comments').val();
        var is_bulk = $('#is_bulk').val();
        var applicantId = $('#applicantId').val();
        var valid=1;
        if(opreation_type=='R' || opreation_type=='T'){
          var valid=0;
          if(reject_cause!=''){
             var valid=1;
              
          }
          else{
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
        url: "{{ url('verifyDataFaulty') }}",
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
           if(data.return_status){
            dataTable.ajax.reload(null, false);
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
                    }
                }
                });
               //printMsg(data.return_msg,'1','errorDivMain');
              
               

           }
           else{
            $("#submitting").hide();
               $("#verifyReject").show();
               $('#errorDiv').animate({ scrollTop: 0 }, 'slow');
               alert(sessiontimeoutmessage);
               window.location.href=base_url;
              

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
                     location.reload();
                    }
                }
                });
        
        }           
      });
                    },
                    Cancel: function () {

},
                }
                });
            
        }
    });
  });
function View_encolser_modal(doc_name,doc_type,is_profile_pic,application_id){
$('#encolser_name').html('');
$('#encolser_content').html('');
$('#encolser_name').html(doc_name+'('+application_id+')');
$('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
var application_id= $('#fullForm #application_id').val();

  $.ajax({
            url: '{{ url('ajaxGetEncloserFaulty') }}',
            type: "POST",
             data: {
            doc_type: doc_type,
             is_profile_pic: is_profile_pic,
             application_id: application_id,
             _token: '{{ csrf_token() }}',
             },
            }).done(function( data, textStatus, jqXHR ) {
            $('#encolser_content').html('');
            $('#encolser_content').html(data);
            $("#encolser_modal").modal();
        }).fail(function( jqXHR, textStatus, errorThrown ) {
          $('#encolser_content').html('');
            alert(sessiontimeoutmessage);
           window.location.href=base_url;
        });
}
function controlCheckBox(){
    var anyBoxesChecked = false;
     var applicantId=Array();
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
       $('.ben_view_button').attr('disabled',true);
      document.getElementById('bulk_approve').disabled = false;
      // document.getElementById('bulk_blkchange').disabled = false;
    } else{
      $('#approve_rejdiv').hide();
      $('.ben_view_button').removeAttr('disabled',true);
      $("#check_all_btn").removeAttr("disabled", true);
      document.getElementById('bulk_approve').disabled = true;
      // document.getElementById('bulk_blkchange').disabled = true;
    }
    //console.log(applicantId);
  }
  function printMsg (msg,msgtype,divid) {
            $("#"+divid).find("ul").html('');
            $("#"+divid).css('display','block');
			if(msgtype=='0'){
				//alert('error');
				$("#"+divid).removeClass('alert-success');
				//$('.print-error-msg').removeClass('alert-warning');
				$("#"+divid).addClass('alert-warning');
			}
			else{
				$("#"+divid).removeClass('alert-warning');
				$("#"+divid).addClass('alert-success');
			}
			if(Array.isArray(msg)){
            $.each( msg, function( key, value ) {
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
@stop