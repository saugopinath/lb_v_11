@extends('processApplication.base')
<style type="text/css">
.required-field::after {
    content: "*";
    color: red;
  }
   #search_sws{
   margin-top:20px;
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
       @if ( ($message = Session::get('error')))
                <div class="alert alert-danger alert-block">
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
  <div class="box-header">
    <div class="row">
        <div class="col-sm-8">
          <h3 class="box-title">Reverted Applications</h3>
        </div>
       
       
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
   @if(count($ds_phase_list)>0)
             <div class="form-group col-md-3">
                          <label class="">Duare Sarkar Phase</label>
                          <select class="form-control" name="ds_phase" id="ds_phase" tabindex="70">
                          <option value="">--All--</option>
                          @foreach($ds_phase_list as $ds_row)
                            <option value="{{$ds_row->phase_code}}">{{$ds_row->phase_des}}</option>
                          @endforeach 
                          </select>
                          <span id="error_ds_phase" class="text-danger"></span>
              </div>
                               <button type="button" name="submit" value="Submit" class="btn btn-success btn-lg" id="search_sws" >Search</button>

              @else
              <input type="hidden" name="ds_phase" id="ds_phase" value=""/>

    @endif
      <div class="row">
        <div class="alert print-error-msg"  style="display:none;" id="errorDivMain">
      <button type="button" class="close"  aria-label="Close" onclick="closeError('errorDivMain')"><span aria-hidden="true">&times;</span></button>
      <ul></ul></div>
            
         
        
      </div>

    <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
      <div class="row">
        <div class="col-sm-12">
          <table id="example" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
            <thead>
              <tr role="row">
              <th>Mobile No</th>
              <th>Duare Sarkar</th>
                <th>Application ID</th>
                <th>Applicant Name</th>
                <th>Age</th>
                <th>Swasthyasathi Card No.</th>
                <th>Reverted Reason</th>
                <th>Reverted Comment</th>
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
  <!-- /.box-body -->
</div>
<div id="modalReject" class="modal fade">
  <form method="POST" action="{{ route('revertReject')}}"  name="faultyReject" id="faultyReject">
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
                            <option value="{{$r_arr['id']}}">{{$r_arr['reason']}}</option>
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
    </section>
  

@endsection

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
 $('.sidebar-menu #revertedList').addClass("active"); 
  var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
  var base_url='{{ url('/') }}';
   var dataTable = "";
   $("#submitting").hide(); 
  $("#submittingapprove").hide(); 
      var dataTable=$('#example').DataTable( {
      //dom: 'Bfrtip',
      dom: 'Blfrtip',
      paging: true,
      pageLength:20,
      lengthMenu: [[10,20, 50, 80, 120], [10,20, 50, 80, 120]],
      serverSide: true,
      deferRender: true,
      processing:true,
      bRetrieve: true,
      ordering:false,
      searching: true,
      language: {
        "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
      },
      ajax:{
            url: "{{ url('reverted-list') }}",
            type: "GET",
            data:function(d){
                 d.filter_1= $("#gp_code").val(),
                d.ds_phase= $("#ds_phase").val(),
                 d._token= "{{csrf_token()}}"
            },  error: function (ex) {
           alert(sessiontimeoutmessage);
           window.location.href=base_url;
        }               
      },
      columns: [
        { "data": "mobile_no" },
	      { "data": "duare_sarkar_registration_no"},    
        { "data": "id" },
        { "data": "name" },
        { "data": "ben_age" },
        { "data": "ss_card_no" },
        { "data": "rejected_reason" },
        { "data": "reverted_remarks" },
        { "data": "Edit" }
      ],          
 "columnDefs": [
            {
                "targets": [ 0,1 ],
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
                columns: [2,3,4,5,6,7],

            }
       },
       {
           extend: 'print',
           footer: true,
           pageSize:'A4',
           //orientation: 'landscape',
           pageMargins: [ 40, 60, 40, 60 ],
           exportOptions: {
                columns: [2,3,4,5,6,7],
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
                columns: [2,3,4,5,6,7],
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
                columns: [2,3,4,5,6,7],
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
                columns: [2,3,4,5,6,7],
                stripHtml: false,
            }
       },
      //'pdf','excel','csv','print','copy'
      ]
    } );
     $(document).on('click', '.rej-btn', function() {
      $('#faultyReject #application_id').val('');
      $('#application_text_approve').text('');
      $('.rej-btn').attr('disabled',false);
      var benid=$(this).val();
      //alert(benid);
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
      $('#search_sws').click(function(){
       
            dataTable.ajax.reload();
            
       
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
