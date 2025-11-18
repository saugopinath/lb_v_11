<!DOCTYPE html>

<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SS | {{Config::get('constants.site_title')}}</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link href="{{ asset("/bower_components/AdminLTE/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet"
    type="text/css" />
  <link href="{{ asset("css/select2.min.css") }}" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css"> 
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link href="{{ asset("/bower_components/AdminLTE/dist/css/AdminLTE.min.css")}}" rel="stylesheet" type="text/css" />
  <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
        page. However, you can choose any other skin. Make sure you
        apply the skin class to the body tag so the changes take effect.
  -->
  <link href="{{ asset("/bower_components/AdminLTE/dist/css/skins/skin-blue.min.css")}}" rel="stylesheet"
    type="text/css" />

  <!-- bootstrap wysihtml5 - text editor -->
  <!-- <link rel="stylesheet" href="{{ asset("/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css")}}"> -->

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

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
   .active_tab2 {
      background-color: #f5f5f5;
      color: #333;
      cursor: pointer;
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
    .btnEnc {
      margin-top:10px;
    }
    
  </style>


</head>
<?php 
use Illuminate\Support\Facades\Input;

?>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <!-- Main Header -->
    @include('layouts.header')
    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div>

              <!-- class="box box-primary" -->
              <div class="box-header with-border">
                <h3 class="box-title"><a href="lb-caste-reverted-list" ><img  width="50px;" style="pull-left"  src="{{ asset("images/back.png") }}" alt="Back" /></a>&nbsp</h3>

              </div>

              <div>
                @if (!empty($beneficiary_id))
                <div class="alert alert-info alert-block">
                  
                  <strong> Beneficiary ID: {{$beneficiary_id}}</strong>
                

                </div>
                @endif
             
                @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
                <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button> 
                      <strong>{{ $message }} with Application ID: {{$id}}</strong>
               
               
                </div>
               @endif

             
    
               
                 @if ( ($message = Session::get('error')))
                <div class="alert alert-danger alert-block">
                  <button type="button" class="close" data-dismiss="alert">×</button>
                  <strong>{{ $message }}</strong>
                

                </div>
                @endif

                  @if ($errors->any())
                <div class="alert alert-danger alert-block">
                  <button type="button" class="close" data-dismiss="alert">×</button>
                   @foreach ($errors->all() as $error)
                  <strong>{{ $error }}</strong><br />
                   @endforeach

                </div>
                @endif


            
            
               

                
                <ul class="nav nav-tabs">

               
               


                <div class="tab-content" style="margin-top:16px;">
              
                


                 
                  <div class="tab-pane active" id="personal_details">
                    <div class="panel panel-default">
                      <div class="panel-heading">
                        <h4><b>Caste Information Modification</b></h4>
                      </div>
                      <div class="panel-body">
                     
                     <form name="personal" id="personal" method="post" action="{{url('lb-caste-revert-edit-post')}}" enctype="multipart/form-data"  autocomplete="off">
                      
                      {{ csrf_field() }}
                      <input type="hidden" name="application_id" id="application_id" value="{{$row->application_id}}">
                      <input type="hidden" name="beneficiary_id" id="beneficiary_id" value="{{  $beneficiary_id }}">
                       <input type="hidden" name="is_faulty" id="is_faulty" value="{{ $row->is_faulty }}">
                       <input type="hidden" name="old_caste" id="old_caste" value="{{ $row->caste }}">
                       <input type="hidden" name="old_caste_certificate_no" id="old_caste_certificate_no" value="{{ $row->caste_certificate_no }}">
                       <input type="hidden" name="caste_change_type" id="caste_change_type" value="{{ $caste_change_type }}">
                      <div class="row">
                       <div class="form-group col-md-4">
                          <label class="">Is Faulty Application?</label>
                          <span id="" class="text-info">{{$row->is_faulty?'YES':'NO' }}</span>
                        </div>
                      <div class="form-group col-md-4">
                          <label class="">Name:</label>
                          <span id="" class="text-info">{{$row->ben_fname}}</span>
                        </div>
                       
                         <div class="form-group col-md-4">
                          <label class="">Swasthyasathi Card No:</label>
                          <span id="" class="text-info">{{$row->ss_card_no }}</span>
                        </div>
                         </div> 
                      <div class="row">
                         <div class="form-group col-md-4">
                          <label class="">Mobile No.:</label>
                          <span id="" class="text-info">{{$row->mobile_no }}</span>
                        </div>
                        <div class="form-group col-md-4">
                          <label class="">Father's Name:</label>
                          <span id="" class="text-info">{{$row->father_fname }} @if(!empty($row->father_mname)) {{$row->father_mname }} @endif @if(!empty($row->father_lname)){{$row->father_lname }} @endif</span>
                        </div>
                    
                     
                       
                     </div> 
     
                  <hr/>
                           
                             
                            

                            <div class="row">
                            
                             
                             
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
                              <div class="row">

                              <div class="col-md-4">
                                <div class="modal_field_name">Village/Town/City:</div>
                                <div class="modal_field_value" id="village_modal">{{trim($row_contact->village_town_city)}}</div>
                              </div>
                              <div class="col-md-4">
                                <div class="modal_field_name">House / Premise No:</div>
                                <div class="modal_field_value" id="house_modal">{{trim($row_contact->house_premise_no)}}</div>
                              </div>
                             
                              <div class="col-md-4">
                                <div class="modal_field_name">Pin Code:</div>
                                <div class="modal_field_value" id="pin_code_modal">{{trim($row_contact->pincode)}}</div>
                              </div>

                             




                             

                             
                              

                            </div>

                           <hr/>

                        <div class="row">
                         <div class="form-group col-md-4">
                          <label class="">Existing Caste:</label>
                          <span id="" class="text-info label-warning">{{$row->caste}}</span>
                        </div>
                       @if($row->caste=='SC' || $row->caste=='ST')
                         <div class="form-group col-md-4" >
                          <label class="">Existing SC/ST Certificate No:</label>
                          <span id="" class="text-info label-warning">{{$row->caste_certificate_no }}</span>
                        </div>
                        @endif
                        </div>
                          <div class="row">
                         <div class="form-group col-md-4">
                          <label class="">New Caste:</label>
                          <span id="" class="text-info label-warning">{{$row_caste->new_caste}}</span>
                        </div>
                       @if($row_caste->new_caste=='SC' || $row_caste->new_caste=='ST')
                         <div class="form-group col-md-4" >
                          <label class="">New SC/ST Certificate No:</label>
                          <span id="" class="text-info label-warning">{{$row_caste->new_caste_certificate_no }}</span>
                        </div>
                        @endif
                        </div>
                        <div class="row">
                         
                          
                           <div class="form-group col-md-4">
                          <label class="required-field">New Caste</label>
                          <select class="form-control" name="caste_category" id="caste_category" >
                          @foreach($caste_lb as $key=>$val)
                         
                           
                            <option value="{{$key}}"   @if($row_caste->new_caste==$key) selected @endif>{{$val}}</option>
                          @endforeach 
                          </select>
                          <span id="error_caste_category" class="text-danger"></span>
                          </div>
                         
                     @if($row_caste->new_caste=='SC' || $row_caste->new_caste=='ST')
                        <div class="form-group col-md-4 withCaste" >
                              <label class="required-field">New SC/ST Certificate No.</label>
                           <input type="text" name="caste_certificate_no" id="caste_certificate_no" class="form-control"
                            placeholder="SC/ST Certificate No." maxlength="200" value="{{ $row_caste->new_caste_certificate_no }}"
                            />
                          <span id="error_caste_certificate_no" class="text-danger"></span>
                      </div>
                        @endif
                          
                      </div>
               
   

                   

                         
                         

                         
                       
                          
                   @if($row_caste->new_caste=='SC' || $row_caste->new_caste=='ST')

                            
                         <div class="form-group col-md-12 withCaste" id="">
                         
                            <label class="">Enclosure List (Self Attested)</label>

                       </div>
                        <div class="form-group col-md-4 withCaste">
                                  
                                    <label  class="required-field">{{$doc_caste_arr->doc_name}}</label>
                                    
                                    
                                    
                                    <input type="file" name="doc_3" id="doc_3" class="form-control"/>
                                    <span id="error_doc_3" class="text-danger"></span>
                                    <div class="imageSize">(File type must be {{$doc_caste_arr->doc_type}} and  size max {{$doc_caste_arr->doc_size_kb}}KB)</div>
                                    <!-- <span id="download_" style="">
                                    &nbsp;&nbsp;<button type="button" id="docDownload_1"  class="btn btn-danger downloadEncloser btnEnc" >Download</button>
                                    </span> -->
                                    @if($casteEncloserCount > 0)
                                    <span id="download_" style="">
                                    &nbsp;&nbsp;<a href="javascript:void(0);" id="docDownload_1"  class="btn btn-danger downloadEncloser btnEnc" onclick="View_encolser_modal('{{$doc_caste_arr->doc_name}}',{{$doc_caste_arr->id}},0)" >View</a> 
                                    </span>                                  
                                    @endif
                                    
                                    </div>
                                      
                         
                         @endif
                          
                        <div class="col-md-12" align="center">
                        
                          <div class="form-group col-md-8" >

                          @if($caste_change_type==2)
                           
                            <label>
                             Note: Caste change will be effective only after verification and approval. Payment will be stopped till approval is completed.
                               <br/>
                            </label>
                         @endif   
                          </div>
                           <div class="form-group col-md-4" >
                          <button type="button" name="btn_aplply" id="btn_apply"
                            class="btn btn-success btn-lg">Apply</button>
                          <img  style="display:none;" src="{{ asset('images/ZKZg.gif')}}" id="btn_personal_details_loader" width="150px"
                            height="150px">
                            </div>
                         </div>
                         </form>
                        <br />
                      </div>
                    </div>
                  </div>
                 
                 






                

                 

                


                 





                </div>
                
                
          <!--/.col (left) -->

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
        <!--  @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
      @endif -->
        <!-- /.row -->


      </section>

      <!-- Main content -->
      <!--  <section class="content">

      Your Page Content Here



    </section> -->
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    @include('layouts.footer')

    <!-- ./wrapper -->

    <!-- REQUIRED JS SCRIPTS -->

    <!-- jQuery 2.1.3 -->
    <script src="{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script>
       <!--  <script src="{{ URL::asset('js/jquery.form.min.js') }}"></script> -->

    <script src="{{ URL::asset('js/validateAdhar.js') }}"></script>

    <!-- Bootstrap 3.3.2 JS -->
    <script src="{{ asset ("/bower_components/AdminLTE/bootstrap/js/bootstrap.min.js") }}" type="text/javascript">
    </script>




    <!-- AdminLTE App -->
    <script src="{{ asset ("/bower_components/AdminLTE/dist/js/app.min.js") }}" type="text/javascript"></script>
    
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

$(document).ready(function(){
  $('.sidebar-menu li').removeClass('active');
  $('.sidebar-menu #lb-caste').addClass("active"); 
  $('.sidebar-menu #caste-revert').addClass("active"); 


  
 
  var base_url='{{ url('/') }}';
 

    $("#submitting").hide();
    $("#submit_loader").hide();
  



    
    
    $("#caste_category").on('change', function(){

      var caste_category =  $("#caste_category").val();
      if(caste_category == "SC" || caste_category == "ST" ||  caste_category == "")
      {
        $(".withCaste").show(); 
      } 
      else
      {
        $(".withCaste").hide();
      }
    });
 

    $('.txtOnly').keypress(function (e) {
            var regex = new RegExp(/^[a-zA-Z\s]+$/);
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            else {
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



$('.special-char').keyup(function()
  {
    var yourInput = $(this).val();
    re = /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
    var isSplChar = re.test(yourInput);
    if(isSplChar)
    {
      var no_spl_char = yourInput.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
      $(this).val(no_spl_char);
    }
  });


  



   
  $('#btn_apply').click(function(){
  $("#errorDiv").hide();
  $("#errorDiv").find("ul").html('');
  //var error_title ='';

  
  var error_caste_category = '';
  var error_caste_certificate_no ="";
  var error_doc_3 ='';  
  
  if($.trim($('#caste_category').val()).length == 0)
  {
   error_caste_category = 'Caste is required';
   $('#error_caste_category').text(error_caste_category);
   $('#caste_category').addClass('has-error');
  }
  else
  {
   error_caste_category = '';
   $('#error_caste_category').text(error_caste_category);
   $('#caste_category').removeClass('has-error');
  }
  if($('#caste_category').val() == 'SC' || $('#caste_category').val() == 'ST' || $('#caste_category').val() == '')
  { 
      if($.trim($('#caste_certificate_no').val()).length == 0)
      {
        error_caste_certificate_no = 'SC/ST Certificate No. is required';
        $('#error_caste_certificate_no').text(error_caste_certificate_no);
        $('#caste_certificate_no').addClass('has-error');
      }
      else
      {
        error_caste_certificate_no = '';
        $('#error_caste_certificate_no').text(error_caste_certificate_no);
        $('#caste_certificate_no').removeClass('has-error');
      }
  }
 


   
 
 
 
  if( error_caste_category !=''  || error_caste_certificate_no != ''   )

  //if( error_first_name !=''  )
  {
     $("html, body").animate({ scrollTop: 0 }, "slow");
   return false;
  }
  
   else{
    var caste_category =  $("#caste_category").val();
    var caste_certificate_no =  $("#caste_certificate_no").val();
    var old_caste =  $("#old_caste").val();
    var old_caste_certificate_no =  $("#old_caste_certificate_no").val();
    
    
    $(".btn-lg").attr("disabled", true);

    $("#personal").submit();
    
  
  
    // return true;
   }
   

});

 
 


 




 

 
  
  
/***************************SD*********************************/
// $('#btn_submit_preview').click(function(){
// $(".modal-submit").show();
// $("#submitting").hide();
// $("#submit_loader").hide();
// $("#confirm-submit").modal("show");
// });
// $('.encloserModal').click(function(){
//  $("#encolser_name").html('');
//  $('#uploadStatus').html('');
//  $('.progress-bar').html('');
//  $("#uploadForm #document_type").val('');
//  $("#uploadForm #is_profile").val('');
//  $('#btn_encolser_loader').hide();
//  var label = $(this).parent().find('label').text();
//  $("#encolser_name").html(label);
//  var id= $(this).attr("id");
//  var id_split=id.split('_');
//  //console.log(id_split);
//  $("#uploadForm #document_type").val(id_split[1]);
//  $("#uploadForm #is_profile").val(id_split[2]);
//  $("#encolser_modal").modal("show");

// });


// $("#uploadForm").on('submit', function(e){
//         $('#submitButton').hide();
//         $('#btn_encolser_loader').show();

//         e.preventDefault();
//         var form = $('#uploadForm')[0];
//         var formData = new FormData(form);
//         var add_edit_status=$("#commonfield #add_edit_status").val();
//        // alert(add_edit_status);
//         var scheme_id=$("#commonfield #scheme_id").val();
//         var sws_card_no=$("#commonfield #sws_card_no").val();
//         var source_id=$("#commonfield #source_id").val();
//         var source_type=$("#commonfield #source_type").val();
//         var max_tab_code=$("#commonfield #max_tab_code").val();
//         var application_id=$("#commonfield #application_id").val();
//         formData.append('add_edit_status', add_edit_status);
//         formData.append('scheme_id', scheme_id);
//         formData.append('sws_card_no', sws_card_no);
//         formData.append('source_id', source_id);
//         formData.append('source_type', source_type);
//         formData.append('max_tab_code', max_tab_code);
//         formData.append('application_id', application_id);
//         $.ajax({
//             xhr: function() {
//                 var xhr = new window.XMLHttpRequest();
//                 xhr.upload.addEventListener("progress", function(evt) {
//                     if (evt.lengthComputable) {
//                         var percentComplete = ((evt.loaded / evt.total) * 100);
//                         var percentComplete = Math.ceil(percentComplete);
//                         $(".progress-bar").width(percentComplete + '%');
//                         $(".progress-bar").html(percentComplete+'%');
//                     }
//                 }, false);
//                 return xhr;
//             },
//             type: 'POST',
//             dataType: 'json',
//             url: '{{ url('ajax_faulty_encloser_entry') }}',
//             data: formData,
//             contentType: false,
//             cache: false,
//             processData:false,
//             beforeSend: function(){
//                 $(".progress-bar").width('0%');
//                 //$('#uploadStatus').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
//             },
//              error: function (ex){
//                 //console.log(ex);
//                 $('#uploadStatus').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
//                  $('#btn_encolser_loader').hide();
//                  $('#submitButton').show();


//             },
//             success: function(resp){
//               //console.log(resp);
//                 if(resp.return_status==1){
//                    $("#max_tab_code").val(resp.max_tab_code);
//                     var id=$("#uploadForm #document_type").val();
//                     $('#uploadForm')[0].reset();
//                     $('#download_'+id).show();
//                     $('#uploadStatus').html('<p style="color:#28A74B;">File has uploaded successfully!</p>');
//                      //$(".progress-bar").width('0%');

//                 }else if(resp.return_status==0){
//                     $('#uploadStatus').html('<p style="color:#EA4335;">'+resp.return_msg+'</p>');
//                 }
//                   $('#btn_encolser_loader').hide();
//                    $('#submitButton').show();


//             }
//         });
        
        
//     });
  
    
// $('#encolser_modal').on('hidden.bs.modal', function (e) {
//   $("#uploadForm #document_type").val('');
//   $("#uploadForm #is_profile").val('');
//   $(".progress-bar").html('');

// });
// $(".downloadEncloser").click(function(){
//  var id= $(this).attr("id");
//  var id_split=id.split('_');  
//  var application_id=$("#application_id").val();
//   window.open("downaloadEncloser_faulty?id="+id_split[1]+"&is_profile_pic="+id_split[2]+"&application_id="+application_id);
// });


// $('.modal-submit').on('click',function(){

//         $(".modal-submit").hide();
//         $("#submitting").show();
//         $("#submit_loader").show();
       
      
// });




/***************************************************************/
});
 $('#district').change(function() {
      if($(this).val() !=''){
       $('#urban_code').val('');
       $('#block').html('<option value="">--Select--</option>');
       $('#gp_ward').html('<option value="">--Select--</option>');
      }
    }); 
$('#urban_code').change(function() {
      if($(this).val() !=''){
        var district_code=$('#district').val();
        //alert(district_code);
        var rural_urban=$(this).val();
        var error_found=1;
        if(rural_urban==2){
          error_found=0;
          var url='{{ url('masterDataAjax/getTaluka') }}';
        }
        else if(rural_urban==1){
          error_found=0;
          var url='{{ url('masterDataAjax/getUrban') }}';
        }
        
        if(error_found==0){
          //alert('ok');
                $('#block').val('');
                $('#gp_ward').val('');
                $('#error_block').html('<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                      district_code: district_code,
                      _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {
                        //console.log(data);
                        var htmlOption='<option value="">--Select--</option>';
                        $.each(data, function (key, value) {
                                  htmlOption+='<option value="'+key+'">'+value+'</option>';
                          });
                        $('#block').html(htmlOption);
                        $('#gp_ward').html('<option value="">--Select--</option>');
                        $('#error_block').html('');
                    },
                    error: function (ex) {
                         alert(sessiontimeoutmessage);
                        window.location.href=base_url;
                    }
                  });
        }
        
      }
    }); 
      $('#block').change(function() {
         if($(this).val() !=''){
        var block_code=$(this).val();
        //alert(block_code);
        var rural_urban= $('#urban_code').val();
        var error_found=1;
        if(rural_urban==2){
          error_found=0;
          var url='{{ url('masterDataAjax/getGp') }}';
        }
        else if(rural_urban==1){
          error_found=0;
          var url='{{ url('masterDataAjax/getWard') }}';
        }
        
        if(error_found==0){
                $('#gp_ward').val('');
                $('#error_gp_ward').html('<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                      block_code: block_code,
                      _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {
                       // console.log(data);
                        var htmlOption='<option value="">--Select--</option>';
                        $.each(data, function (key, value) {
                                  htmlOption+='<option value="'+key+'">'+value+'</option>';
                          });
                        $('#gp_ward').html(htmlOption);
                        $('#error_gp_ward').html('');
                    },
                    error: function (ex) {
                         alert(sessiontimeoutmessage);
                        window.location.href=base_url;
                    }
                  });
        }
        
      }
    });  





function View_encolser_modal(doc_name,doc_type,is_profile_pic){
var application_id= $('#personal #application_id').val();
var is_faulty= $('#personal #is_faulty').val();
$('#encolser_name').html('');
$('#encolser_content').html('');
$('#encolser_name').html(doc_name+'('+application_id+')');
$('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
if(is_faulty==1){
var url='{{ url('ajaxGetEncloserFaulty') }}';
}
else{
var url='{{ url('ajaxGetEncloser') }}';
}
  $.ajax({
            url: '{{ url('ajaxModifiedCasteEncolser') }}',
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
function printMsg (msg,msgtype,divid) {
            $("#"+divid).find("ul").html('');
            $("#"+divid).css('display','block');
      if(msgtype=='0'){
        //alert('error');
        $("#"+divid).removeClass('alert-success');
        //$('.print-error-msg').removeClass('alert-warning');
        $("#"+divid).addClass('alert-info');
      }
      else{
        $("#"+divid).removeClass('alert-info');
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






</body>

</html>