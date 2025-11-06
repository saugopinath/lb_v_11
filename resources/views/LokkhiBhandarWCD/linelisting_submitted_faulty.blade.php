@extends('LokkhiBhandarWCD.base')
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
</style>
@section('action-content')

    <!-- Main content -->
    <section class="content">
      <div class="box">
     @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
              <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button> 
                      <strong>{{ $message }} with Application ID: {{$id}}</strong>
               
               
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
  <div class="box-header">
    <div class="row">
        <div class="col-sm-8">
          <h3 class="box-title">Faulty Applications</h3>
        </div>
    </div>
    <br/>
    <div class="row">
   
   @if($munc_visible)
    <div class="col-md-3">
                  <label class=" control-label">Municipality</label>
                  <select name="muncid" id="muncid" class="form-control" tabindex="16" >
                  <option value="">--All --</option>
                    @foreach ($muncList as $munc)
                  <option value="{{$munc->urban_body_code}}"> {{$munc->urban_body_name}}</option>
                  @endforeach
                   
                </select>
                 
  </div>
  @endif
  @if($gp_ward_visible)
    <div class="col-md-3">
                  <label class=" control-label">GP/Ward </label>
                  <select name="gp_ward" id="gp_ward" class="form-control select2 full-width" >
                    <option value="">-----All----</option>
                     @if(count($gpList)>0)
                  @foreach ($gpList as $gp)
                  <option value="{{$gp->gram_panchyat_code}}"> {{$gp->gram_panchyat_name}}</option>
                  @endforeach
                  @endif
  
                </select>
                 
  </div>
  @endif
  
    <div class="col-md-3">
                  <label class=" control-label">Filter</label>
                  <select name="is_reverted" id="is_reverted" class="form-control full-width" >
                  <option value="">-----All----</option>
                  <option value="1">Only Reverted</option>  
  
                </select>
                 
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
  <!-- /.box-header -->
  <div class="box-body">
      <div class="row">
        <div class="alert print-error-msg"  style="display:none;" id="errorDivMain">
      <button type="button" class="close"  aria-label="Close" onclick="closeError('errorDivMain')"><span aria-hidden="true">&times;</span></button>
      <ul></ul></div>
            
         
        
      </div>

    <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
      <div class="row">
        <div class="col-sm-12">
        
       @if ( ($message = Session::get('error')))
                <div class="alert alert-danger alert-block">
                  <button type="button" class="close" data-dismiss="alert">×</button>
                  <strong>{{ $message }}</strong>
                

                </div>
      @endif
          <table id="example" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
            <thead>
              <tr role="row">
               <th>Mobile No</th>
                  <!-- <th>Duare Sarkar</th> -->
                <th width="5%">Application ID</th>
                <th width="15%">Applicant Name</th>
                <th width="15%">Father's Name</th>
                <th width="5%">Age</th>
                <th width="5%">Swasthyasathi Card No.</th>
                <th width="5%">Reverted Reason</th>
                <th width="60%">Action</th>
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
  <!-- /.box-body -->
</div>
    </section>
  
<div id="modalReject" class="modal fade">
  <form method="POST" action="{{ route('faultyReject')}}"  name="faultyReject" id="faultyReject">
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
@endsection
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script src="{{ asset ('/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
 $('.sidebar-menu li').removeClass('active');
 $('.sidebar-menu #lk-main').addClass("active"); 
 $('.sidebar-menu #faultyList').addClass("active"); 
  $("#submitting").hide(); 
  $("#submittingapprove").hide(); 
  var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
  var base_url='{{ url('/') }}';
   var dataTable = "";
  
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
            url: "{{ url('lb-faulty-application-list') }}",
            type: "GET",
            data:function(d){
                 d.munc_code= $("#muncid").val(),
                 d.gp_ward= $("#gp_ward").val(),
                 d.is_reverted= $("#is_reverted").val(),
                 d._token= "{{csrf_token()}}"
            },  error: function (ex) {
           alert(sessiontimeoutmessage);
           window.location.href=base_url;
        }               
      },
      columns: [
          { "data": "mobile_no" },
	      // { "data": "duare_sarkar_registration_no"},  
        { "data": "id" },
        { "data": "name" },
        { "data": "father_name" },
        { "data": "ben_age" },
        { "data": "ss_card_no" },
        { "data": "rejected_reason" },
        { "data": "Edit" }
      ],          
"columnDefs": [
            {
                "targets": [ 0],
                "visible": false,
                "searchable": true
            }
 ],
      buttons: [
       {
           extend: 'pdf',
           footer: true,
           pageSize:'A4',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [2,3,4,5,6],

            }
       },
       {
           extend: 'print',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [2,3,4,5,6],
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
                columns: [2,3,4,5,6],
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
                columns: [2,3,4,5,6],
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
                columns: [2,3,4,5,6],
                stripHtml: false,
            }
       },
      //'pdf','excel','csv','print','copy'
      ]
    } );
    $('#muncid').change(function() {
      var muncid=$(this).val();
      if(muncid!=''){
      
       var municipality_code=$(this).val();
       if(municipality_code!=''){
        $('#gp_ward').html('<option value="">--All --</option>');
        var htmlOption='<option value="">--All--</option>';
          $.each(ulb_wards, function (key, value) {
                if(value.urban_body_code==municipality_code){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        $('#gp_ward').html(htmlOption);
       }
       else{
          $('#gp_ward').html('<option value="">--All --</option>');
       }   
       
    
       
    }
    else{
       $('#gp_ward').html('<option value="">--All --</option>');
    }
    
    });
      $('#filter').click(function() {
      //alert('ok');
        dataTable.ajax.reload();

     
    });
     $('#reset').click(function() {

      location.reload();
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
  } );
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
