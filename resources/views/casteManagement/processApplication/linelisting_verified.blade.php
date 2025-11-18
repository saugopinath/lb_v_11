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

@extends('layouts.app-template-datatable')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Process Application for Caste Info Modification
    </h1>
   
  </section>
  <section class="content">
    <div class="box box-default">
      <div class="box-body">
      
        <div class="panel panel-default">
          <div class="panel-heading"><span id="panel-icon">Applications Yet To Be Verified</div>
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
                  <label class=" control-label">Gram Panchayat </label>
                  <select name="gp_code" id="gp_code" class="form-control full-width" >
                    <option value="">-----All----</option>
                     @foreach ($gps as $gp)
                            <option value="{{$gp->gram_panchyat_code}}" > {{$gp->gram_panchyat_name}}</option>
                    @endforeach
  
                </select>
                 
                </div>
               <div class="form-group col-md-4">
                          <label class="">New Caste</label>
                          <select class="form-control" name="caste_category" id="caste_category" >
                          <option value="">--All--</option>
                          @foreach(Config::get('constants.caste_lb') as $key=>$val)
                            <option value="{{$key}}">{{$val}}</option>
                          @endforeach 
                          </select>
                          <span id="error_caste_category" class="text-danger"></span>
              </div>
                <div class="col-md-offset-1 col-md-2" style="margin-top: 28px;">
                  <label class=" control-label">&nbsp; </label>
                  <button type="button" name="filter" id="filter" class="btn btn-success">Search</button>
                  
                 
                </div>
                <div class="col-md-offset-1" style="margin-top: 28px;">
                  <label class=" control-label">&nbsp; </label>
                
                  <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>
                 
                </div>
                
              </div>
            </div>
            <br>

          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading" id="panel_head">List of New Applicants</div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;">
            <div class="table-responsive">
              <table id="example" class="display" cellspacing="0" width="100%"> 
                <thead style="font-size: 12px;">
                  <tr role="row">
                    <th>Application ID</th>
                    <th>Beneficiary ID</th>
                    <th>Applicant Name</th>
                    <th>Swasthya Sathi Card No.</th>
                    <th>Mobile No</th>
                    <th>Action</th>
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
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h3 class="modal-title">Beneficiary Details (<span class="applicant_id_modal"></span>)</h3>
          </div>
          <div class="modal-body ben_view_body">
           <div class="panel-group" role="tablist" aria-multiselectable="true">
           <div class="panel panel-default"><div class="panel-heading active" role="tab" id="personal">
           <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_personal"></div>
           <h4 class="panel-title">
           <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePersonal" aria-expanded="true" aria-controls="collapsePersonal">Personal Details</a> </h4> </div> 
           <div id="collapsePersonal" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="personal">  
           <div class="panel-body"><table class="table table-bordered">  
           <tbody>
           <tr>
          
           <th scope="row">Aadhaar No.</th>
            <td id="aadhar_no_encrypt"></td>
            <td id="aadhar_no_original" style="display:none;"></td>
            <td id="aadhar_no_or"><button class="btn btn-info showhideAadhar" id="show_hide_aadhar" >Show Original Aadhaar</button></td>
           </tr>
         
           <tr>       
           <th scope="row">Name</th><td id='ben_fullname'></td>
           </tr>
           <tr>
           <th scope="row">Mobile No.</th>         
           <td id="mobile_no"></td>      
                
          </tr>
           <tr>
          
           <th scope="row">DOB</th>         
           <td id="dob"></td> 
           <th scope="row">Age</th>         
           <td id="ben_age"></td>         
           </tr>
          <tr>
           <th scope="row">Father Name</th>         
           <td id='father_fullname'></td>
           </tr>
           <tr>
           <th scope="row">Mother Name</th>
           <td id="mother_fullname"></td> 
           </tr> 
          
           <tr>
           <th scope="row">Existing Caste:</th>
           <td id="caste"></td>
           <th scope="row" class="caste">Existing SC/ST Certificate No.</th>
           <td id="caste_certificate_no"  class="caste"></td>
           </tr> 
          <tr>
           <th scope="row">New Caste:</th>
           <td id="caste_new"></td>
           <th scope="row" class="new_caste">New SC/ST Certificate No.</th>
           <td id="new_caste_certificate_no"  class="new_caste"></td>
           </tr> 
           
            </tbody>
            </table>
            </div>
            </div> 
            </div>
            </div>
            
            <div class="panel-group"> <div class="panel panel-default">   
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
             <th scope="row">Police Station</th>
             <td id="police_station"></td> 
             </tr>
             <tr> 
             <th scope="row">Block/Municipality Name</th>
             <td id="block_ulb_name"></td>  
             <th scope="row">Gp Ward Name</th>
             <td id="gp_ward_name"></td>         
             </tr>
             <tr>
             <th scope="row">Village/Town/City</th>
             <td id="village_town_city"></td>
             <th scope="row">House / Premise No</th>
             <td id="house_premise_no"></td>
             </tr>
             <tr>                                                                                                                                                                                                   </td>  
             <th scope="row">Post Office</th>
             <td id="post_office"></td>
             <th scope="row">Pincode</th>         
             <td id="pincode"></td>
             </tr>
             </tbody>
             </table>
             </div> 
             </div> 
             </div>  
             </div>
             <div class="panel-group"><div class="panel panel-default">   
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
            <div class="panel-group"><div class="panel panel-default">   
            <div class="panel-heading" role="tab" id="encloser">
           <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_encolser"></div>
             <h4 class="panel-title"> 
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseEncloser" aria-expanded="false" aria-controls="collapseEncloser">Encolser Details </a> </h4> </div> 
            <div id="collapseEncloser" class="panel-collapse collapse" role="tabpanel" aria-labelledby="encloser">  
            <div class="panel-body">   
            <table id="enCloserTable">
            <tbody>
              <th scope="row">New {{$doc_caste_arr->doc_name}}</th>
              <td  scope="row" class="encView" id="encView">&nbsp;&nbsp;&nbsp;<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="View_encolser_modal('{{$doc_caste_arr->doc_name}}',{{$doc_caste_arr->id}})">View</a></td>
              <td id="encGen">NA</td>
            </tbody>
            </table>
            </div> 
            </div> 
            </div> 
            </div> 
             <div class="panel-group">  
             <div class="panel panel-default">   <div class="panel-heading" role="tab" id="headingFour">   
             <h4 class="panel-title"> <a>Action</a> </h4> </div> <div id="collapse4" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingFour">  <div class="panel-body"> <div class="form-group col-md-4"><label class="required" for="reject_cause">Select Operation</label>
             <select name="opreation_type" id="opreation_type" class="form-control opreation_type">
             <option value="V" selected="">Verify</option>
             <option value="R">Reject</option>
            <option value="T">Revert</option>
             </select></div> <div class="form-group col-md-4" style="display:none;" id="div_rejection"><label class="required" for="reject_cause">Select Reject/Reverted Cause</label>
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
              <input type="hidden" id="id" name="id"/>
              <input type="hidden" id="application_id" name="application_id"/>
              <input type="hidden" id="is_faulty" name="is_faulty"/>
              <input type="hidden" id="new_caste" name="new_caste"/>

          
  
  
               <button type="button" style="float: right" class="btn btn-success" id="verifyReject">Verify</button>
  
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
    $(document).on('show.bs.collapse', '.panel-collapse', function() {
   
    var id=$(this).attr('id');
    var application_id=$('#fullForm #application_id').val();
    var is_faulty=$('#fullForm #is_faulty').val();
   // alert(application_id);
    //var application_id=benid;
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
        $('#loader_img_personal').show();
          $.ajax({
            type: 'post',
            url: '{{ url('getCasteApplieddata') }}',
            data: {_token:'{{csrf_token()}}',application_id:application_id},    
            dataType: 'json',
            success: function (response) {
             // alert(response.data);
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
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
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
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
             // alert(response.personaldata.ss_card_no);
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
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
            url: '{{ url('getContactApproved') }}',
            data: {_token:'{{csrf_token()}}',benid:application_id,is_faulty:is_faulty},    
            dataType: 'json',
            success: function (response) {
              //console.log(response.contactdata);
              $('#loader_img_contact').hide();
              $('.ben_view_button').removeAttr('disabled',true);
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
            url: '{{ url('getBankApproved') }}',
            data: {_token:'{{csrf_token()}}',benid:application_id,is_faulty:is_faulty},    
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
        //$('#loader_img_encolser').show(); 
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
    $(this).siblings('.panel-heading').addClass('active');
   
  });
  $(document).on('hide.bs.collapse', '.panel-collapse', function() {
  //$('.panel-collapse').on('hide.bs.collapse', function () {
    $(this).siblings('.panel-heading').removeClass('active');
   
  });
  $('.loader_img').hide();
  $('.sidebar-menu li').removeClass('active');
  $('.sidebar-menu #lb-caste').addClass("active"); 
  $('.sidebar-menu #caste_wrkflow').addClass("active"); 
  var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
  var base_url='{{ url('/') }}';
  $('#opreation_type').val('V');
  $("#verifyReject").html("Verify");
  $('#div_rejection').hide();
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
          'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
          "serverSide": true,
          "processing":true,
          "bRetrieve": true,
          "oLanguage": {
            "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
          },
          "ajax": 
          {
            url: "{{ url('workflowCaste') }}",
            type: "get",
            data:function(d){
                 d.gp_code= $("#gp_code").val(),
                 d.caste_category= $("#caste_category").val(),
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
        { "data": "view" }
          ],
      
          "buttons": [
            {
           extend: 'pdf',
         
           title: 'Process Application Report  <?php echo date('d-m-Y');  ?>',
           messageTop:'Date:<?php echo date('d/m/Y');  ?>',
           footer: true,
           pageSize:'A4',
          // orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
            columns: [0,1,2,3,4,5],

            }
       },
            {
           extend: 'excel',
         
           title: 'Process Application Report <?php echo date('d-m-Y');  ?>',
           messageTop:'Date:<?php echo date('d/m/Y');  ?>',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
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
     
    $(document).on('click', '.ben_view_button', function() {
      $('#loader_img_personal').show();
      $('.ben_view_button').attr('disabled',true);
      var benidArr=$(this).val();
      var benid_explode=benidArr.split('_');
      var benid=benid_explode[0];
      var is_faulty=benid_explode[1];
     // alert(is_faulty);
      $('#fullForm #application_id').val(benid);
      $('#fullForm #is_faulty').val(is_faulty);
      $('.applicant_id_modal').html(benid);
      $("#collapseContact").collapse('hide');
      $("#collapseBank").collapse('hide');
      $("#collapseEncloser").collapse('hide');
       $('#duare_sarkar_registration_no').text('');
      $('#duare_sarkar_date').text('');
          
       $.ajax({
            type: 'post',
            url: '{{ url('getCasteApplieddata') }}',
            data: {_token:'{{csrf_token()}}',application_id:benid},    
            dataType: 'json',
            success: function (response) {
             // alert(response.data);
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              $('#caste_new').text(response.data.new_caste);
              if(response.data.new_caste=='SC' || response.data.new_caste=='ST'){
                $('#new_caste_certificate_no').text(response.data.new_caste_certificate_no);
                $('.new_caste').show();
              }
              else{
                $('.new_caste').hide();
              }
              $('#fullForm #new_caste').val(response.data.new_caste);
              //$('#fullForm #is_faulty').val(is_faulty);
               
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
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
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled',true);
              $('#sws_card_txt').text(response.personaldata.ss_card_no);
              $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
              $('#duare_sarkar_registration_no').text(response.personaldata.duare_sarkar_registration_no);
              $('#duare_sarkar_date').text(response.personaldata.formatted_duare_sarkar_date);
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
              $('#email').text(response.personaldata.email);
              $('#gender').text(response.personaldata.gender);
              $('#dob').text(response.personaldata.formatted_dob);
              $('#ben_age').text(response.personaldata.age_ason_01012021);
              if(response.personaldata.father_mname!== undefined &&  response.personaldata.father_mname!==null){
                      var father_mname=response.personaldata.father_mname;
              }
              else{
                var father_mname="";
              }
              if(response.personaldata.father_lname!== undefined && response.personaldata.father_lname!=null){
                      var father_lname=response.personaldata.father_lname;
              }
              else{
                var father_lname="";
              }
              $('#father_fullname').text(response.personaldata.father_fname+' '+father_mname+' '+father_lname);
              if(response.personaldata.mother_mname!== undefined && response.personaldata.mother_mname!=null){
                      var mother_mname=response.personaldata.mother_mname;
              }
              else{
                var mother_mname="";
              }
             if(response.personaldata.mother_lname!== undefined && response.personaldata.mother_lname!=null){
                      var mother_lname=response.personaldata.mother_lname;
              }
              else{
                var mother_lname="";
              }
              $('#mother_fullname').text(response.personaldata.mother_fname+' '+mother_mname+' '+mother_lname);
              $('#caste').text(response.personaldata.caste);
              if(response.personaldata.caste=='SC' || response.personaldata.caste=='ST'){
                $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
                $('.caste').show();
              }
              else{
                $('.caste').hide();
              }
              if(response.personaldata.spouse_fname!== undefined && response.personaldata.spouse_fname!==null){
                      var spouse_fname=response.personaldata.spouse_fname;
              }
              else{
                var spouse_fname="";
              }
             if(response.personaldata.spouse_mname!== undefined && response.personaldata.spouse_mname!==null){
                //console.log((response.personaldata.ben_mname);
                      var spouse_mname=response.personaldata.spouse_mname;
              }
              else{
                var spouse_mname="";
              }
              if(response.personaldata.spouse_lname!== undefined && response.personaldata.spouse_lname!==null){
                      var spouse_lname=response.personaldata.spouse_lname;
              }
              else{
                var spouse_lname="";
              }
              $('#spouse_name').text(spouse_fname+' '+spouse_mname+' '+spouse_lname);
               
              $('#fullForm #id').val(response.benid);
              

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
  

   

    $('#filter').click(function(){
        var filter_1 = $('#filter_1').val();
        if(filter_1 != '')
        {
            dataTable.ajax.reload();
            
        }
        else{
          alert('Please select two Filter Criterias');
        }
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
         $("#verifyReject").html("Verify");
          $('#div_rejection').hide();
          $("#reject_cause").val('');
       }
    });

  
      $(document).on('click', '#verifyReject', function() { 
      
        var scheme_id = $('#scheme_id').val();
        var reject_cause = $('#reject_cause').val();
        var opreation_type = $('#opreation_type').val();
        var accept_reject_comments = $('#accept_reject_comments').val();
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
        url: "{{ url('verifyDataCaste') }}",
        data: {
          scheme_id: scheme_id,
          reject_cause: reject_cause,
          opreation_type: opreation_type,
          accept_reject_comments: accept_reject_comments,
          id: id,
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
             if(data.return_msg=='Aadhaar No. is Duplicate..'){
              alert(data.return_msg);
              window.location.href='workflowCaste?pr1=lb_wcd';
              }
              else{
               $("#submitting").hide();
               $("#verifyReject").show();
               $('#errorDiv').animate({ scrollTop: 0 }, 'slow');
               alert(sessiontimeoutmessage);
               window.location.href=base_url;
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

},
                }
                });
            
        }
    });
  });
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