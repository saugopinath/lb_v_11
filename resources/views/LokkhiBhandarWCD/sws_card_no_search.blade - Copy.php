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
  <link href="{{ asset("/bower_components/AdminLTE/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
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
   <link href="{{ asset("/bower_components/AdminLTE/dist/css/skins/skin-blue.min.css")}}" rel="stylesheet" type="text/css" />

   <!-- bootstrap wysihtml5 - text editor -->
  <!-- <link rel="stylesheet" href="{{ asset("/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css")}}"> -->

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <link href="{{ asset("/bower_components/AdminLTE/plugins/datatables/dataTables.bootstrap.css")}}" rel="stylesheet" type="text/css" />

  <style>
  .box
  {
   width:800px;
   margin:0 auto;
  }
  .active_tab1
  {
   background-color:#fff;
   color:#333;
   font-weight: 600;
  }
  .inactive_tab1
  {
   background-color: #f5f5f5;
   color: #333;
   cursor: not-allowed;
  }
  .has-error
  {
   border-color:#cc0000;
   background-color:#ffff99;
  }
  .select2{
    width:100%!important;
  }
  .select2 .has-error {
    border-color:#cc0000;
   background-color:#ffff99;
}
.modal_field_name{
  float:left;
  font-weight: 700;
  margin-right:1%;
  padding-top:1%;
  margin-top:1%;
}

.modal_field_value{
  margin-right:1%;
  padding-top:1%;
  margin-top:1%;
}
.row{
  margin-right: 0px!important;
  margin-left: 0px!important;
  margin-top: 1%!important;
}

.section1{
    border: 1.5px solid #9187878c;
    margin: 2%;
    padding: 2%;
}
.color1{
  margin: 0%!important;
  background-color: #5f9ea061;
}

.modal-header{
  background-color: #7fffd4;
}
.required-field::after {
    content: "*";
    color: red;
}
 .imageSize{
  font-size: 9px;
  color: #333;
 }
 #search_sws{
   margin-top:20px;
 }
 #search_faulty{
   margin-top:20px;
   margin-left:100px;
 }
 #import_sws{
   margin-top:20px;
   margin-left:100px;
 }

  </style>


</head>
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
          <div> <!-- class="box box-primary" -->
           

            <div>
          
             <!--   @if ($message = Session::get('failure'))
              <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button> 
                      <strong>{{ $message }}</strong>
              </div>
              @endif -->
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form method="post" id="register_form" action="{{url('lb-wcd-search')}}"  class="submit-once" >
              {{ csrf_field() }}
              
            <div class="tab-content" style="margin-top:16px;">

              




             <div class="tab-pane active" id="personal_details">
              <div class="panel panel-default">
               <div class="panel-heading"><h4><b>Lakhasmir Bhandar Form</b></h4></div>
               <div class="panel-body">
                
               <div class="row">
               
                 @if($entry_allowed_main)
               <div class="form-group col-md-3" id="divBodyCode">
                            <label class="required-field">Swasthyasathi Card No.</label>

                              <input type="text" name="sws_card_no" id="sws_card_no" class="form-control special-char"
                              placeholder="Swasthyasathi Card No." maxlength="50" value=""
                               tabindex="11"  autocomplete="off"/>
                            <span id="error_sws_card_no" class="text-danger"></span>
                          </div>        
                 <button type="button" name="submit" value="Submit" class="btn btn-success btn-lg" id="search_sws" >Search</button>
                  @endif
                  @if($entry_allowed_faulty)
                 <button type="button" name="faulty" value="Submit" class="btn btn-warning btn-lg" id="search_faulty" >Faulty Application Entry</button>
                @endif

               
                 <button type="button" name="import" class="btn btn-info btn-lg" id="import_sws"  style="display:none;">Import</button>
               
                  </div>
                 
          </div>
         
            @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
              <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button> 
                      <strong>{{ $message }} with Application ID: {{$id}}</strong>
               
               
              </div>
              @endif
               @if ($message = Session::get('error') )
              <div class="alert alert-danger alert-block" style="margin:5px;">
                <button type="button" class="close" data-dismiss="alert">×</button> 
                      <strong>{{ $message }}</strong>
              
               
              </div>
              @endif
            @if(count($errors) > 0)
            <div class="alert alert-danger alert-block" style="margin:5px;">
              <ul>
               @foreach($errors as $error)
               <li><strong> {{ $error }}</strong></li>
               @endforeach
              </ul>
            </div>
            @endif
            
     
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
      <div class="row">
        <div class="col-sm-12">
          <table id="example" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
            <thead>
              <tr role="row">
                <th width="20%">Applicant Name</th>
                <th width="20%">Father's Name</th>
                <th width="10%">Swasthyasathi Card No.</th>
                <th width="5%">Application Id</th>
                <th width="5%">Status</th>
                <th width="20%">Action</th>
              </tr>
            </thead>
            <tbody>
           
            </tbody>
            
          </table>
        </div>
      </div>
        <div class="row">
            
            <div class="col-sm-7">
               <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
              </div>
            </div>
          </div>
      </div>
    </div>

               
              </div>
             </div>

          </form>
          <!-- /.box -->
        </div>
        <!--/.col (left) -->
        
      </div>
    
<div id="modalReject" class="modal fade">
  <form method="POST" action="{{ route('partialReject')}}"  name="faultyReject" id="faultyReject">
 <input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" id="application_id" name="application_id"/>
	<div class="modal-dialog modal-confirm">
		<div class="modal-content">
			<div class="modal-header flex-column">
								
				<h4 class="modal-title w-100">Do you really want to Reject the application(<span id="application_text_approve"></span>)?</h4>	
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<p></p>
        <div class="row">
         <div class="form-group col-md-12" id="div_rejection">
             <label class="required-field" for="reject_cause">Select Reject Cause</label>
             <select name="reject_cause" id="reject_cause" class="form-control">
             <option value="">--Select--</option>
             @foreach($reject_revert_reason as $r_arr)
                            <option value="{{$r_arr->id}}">{{$r_arr->reason}}</option>
              @endforeach 
             </select>
             </div> 
        </div>
         
			</div>
			<div class="modal-footer justify-content-center">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-danger modal-submitapprove" >Reject</button>
         <button type="button" id="submittingapprove" value="Submit" class="btn btn-success success btn-lg"
                          disabled>Submitting please wait</button>
			</div>
		</div>
	</div>
  </form>
</div>

<div class="modal fade" id="import_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Import Swasthyasathi Card Number</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <span id="errorDiv"></span>
      <h5>Swasthyasathi Card Number: <span id="import_sws_text"></span></h5>
          <table class="table table-condensed" id="ImportTable">
        <thead>
          <tr>
            <th>Applicant Name</th>
            <th>Father's Name</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          
        </tbody>
      </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



  
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
<script  src="{{ asset ("/bower_components/AdminLTE/plugins/datatables/jquery.dataTables.min.js") }}" type="text/javascript" ></script>
<script  src="{{ asset ("/bower_components/AdminLTE/plugins/datatables/dataTables.bootstrap.min.js") }}" type="text/javascript" ></script>
<script src="{{ asset("js/select2.full.min.js") }}"></script>

<!-- Bootstrap 3.3.2 JS -->
<script src="{{ asset ("/bower_components/AdminLTE/bootstrap/js/bootstrap.min.js") }}" type="text/javascript"></script>

<script src="{{ URL::asset('js/site.js') }}"></script>

<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset ("/bower_components/AdminLTE/dist/js/app.min.js") }}" type="text/javascript"></script>
<script>
$(document).ready(function(){
$('.sidebar-menu li').removeClass('active');
$('.sidebar-menu #lk-main').addClass("active"); 
$('.sidebar-menu #edit-update').addClass("active"); 
var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
var base_url='{{ url('/') }}';
var dataTable = "";
   $("#submitting").hide(); 
  $("#submittingapprove").hide(); 
      var dataTable=$('#example').DataTable( {
      //dom: 'Bfrtip',
      dom: 'Blfrtip',
      "paging": true,
      "pageLength":20,
      "lengthMenu": [[10,20, 50, 80, 120], [10,20, 50, 80, 120]],
      "serverSide": true,
      "deferRender": true,
      "processing":true,
      "bRetrieve": true,
      "ordering":false,
      "searching": true,
      "language": {
        "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
      },
      ajax:{
            url: "{{ url('lb-wcd-search') }}",
            type: "GET",
            data:function(d){
                 d.ds_phase= $("#ds_phase").val(),
                 d.sws_card_no= $("#sws_card_no").val(),
                 d._token= "{{csrf_token()}}"
            },  error: function (ex) {
           alert(sessiontimeoutmessage);
           window.location.href=base_url;
        }               
      },
     
      columns: [
                
        { "data": "name" },
        { "data": "father_name" },
        { "data": "sws_card_no" },
        { "data": "application_id" },
        { "data": "status" },
        { "data": "Action" }
      ],          

      buttons: [
       {
           extend: 'pdf',
           footer: true,
           pageSize:'A4',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [0,1,2,3,4],

            }
       },
       {
           extend: 'print',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [0,1,2,3,4],
                stripHtml: false,
            }
       },
       {
           extend: 'excel',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [0,1,2,3,4],
                stripHtml: false,
            }
       },
        {
           extend: 'copy',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [0,1,2,3,4],
                stripHtml: false,
            }
       },
       {
           extend: 'csv',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [0,1,2,3,4],
                stripHtml: false,
            }
       },
      //'pdf','excel','csv','print','copy'
      ]
    } );
     $('#search_sws').click(function(){
        var sws_card_no = $('#sws_card_no').val();
        if(sws_card_no != '')
        {
         
            $("#import_sws").show();
            dataTable.ajax.reload();
            
        }
        else{
          alert('Please Enter Swasthyasathi Card No.');
        }
    });
     $('#search_faulty').click(function(){
       window.location='faulty_entry?add_edit_status=1'
    });
     $(document).on('click', '.rej-btn', function() {
      $('#faultyReject #application_id').val('');
      $('#application_text_approve').text('');
      $('.rej-btn').attr('disabled',false);
      var benid=$(this).val();
      $('#rej_'+benid).attr('disabled',true);
      $('#faultyReject #application_id').val(benid);
      $('#application_text_approve').text(benid);
      $('#modalReject').modal();
    });
    $('#modalReject').on('hidden.bs.modal', function () {
      $('.rej-btn').attr('disabled',false);
    }); 
     $('.modal-submitapprove').on('click',function(){
        var reject_cause=$("#reject_cause").val();
        if(reject_cause!=''){
         $(".modal-submitapprove").hide();
        $("#submittingapprove").show();
        $("#faultyReject").submit();
        }
        else{
          alert('Please Select Rejection Cause');
          $("#reject_cause").focus();
          return false;
        }
       
      });
      $('#import_sws').click(function(){
        var sws_card_no = $('#sws_card_no').val();
        $("#import_sws_text").text(sws_card_no);
        jQuery.ajax({
                  url: "{{ url('import-sws-list') }}",
                  method: 'get',
                  data: {
                    sws_card_no: sws_card_no,
                    _token: "{{csrf_token()}}"
                  },
                  success: function(result){
                    $("#ImportTable tbody").html(result.result);
                    $('.importData').on('click',function(){
                      var slno=$(this).attr('value');
                      $("#impotrId_"+slno).hide();
                      $("#impotrIdLoader_"+slno).show();
                      jQuery.ajax({
                                  url: "{{ url('import-sws-post') }}",
                                  method: 'get',
                                  data: {
                                    sws_card_no: sws_card_no,
                                    slno: slno,
                                    _token: "{{csrf_token()}}"
                                  },
                                  success: function(result){
                                    if(result.isValid){
                                      $("#impotrId_"+slno).html(result.Msg);
                                    }
                                    else{

                                    }
                                    
                                  },
                                  error: function (jqXhr, textStatus, errorMessage) {
                                    //alert(sessiontimeoutmessage);
                                  //window.location.href=base_url;
                                }
                                });
                    });
                  },
                  error: function (jqXhr, textStatus, errorMessage) {
                    //alert(sessiontimeoutmessage);
                   //window.location.href=base_url;
                }
                });
              $('#import_modal').modal();
      });
      
});
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
</body>
</html>


