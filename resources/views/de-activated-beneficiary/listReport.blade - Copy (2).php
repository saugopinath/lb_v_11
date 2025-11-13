
<style>
  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }
  
  
</style>
@extends('layouts.app-template-datatable')
@section('content')

      <!-- Main content -->
     
        <div class="box">
        <div class="box-header">
          <div class="row">
              <div class="col-sm-8">
	
              </div>
          </div>
        </div>
        <div class="box-body">
					@if(count($errors) > 0)
					<div class="alert alert-danger alert-block">
						<ul>
						@foreach($errors->all() as $error)
						<li><strong> {{ $error }}</strong></li>
						@endforeach
						</ul>
					</div>
					@endif  
          <form name="formReport" id="formReport">
           
        <div class="row" style="margin-bottom:1%">
            <div class="form-group col-md-3">
                          <label class="">Rejection Type</label>
                          <select class="form-control" name="report_type" id="report_type" tabindex="70">
                          <option value="">--All--</option>
                          <option value="R">Name Validation Rejection</option>
                          <option value="D">Deactivated</option>

                          </select>
                          <span id="error_report_type" class="text-danger"></span>
              </div>
            @if(count($ds_phase_list)>0)
           <div class="form-group col-md-3">
                          <label class="">Duare Sarkar Phase</label>
                          <select class="form-control" name="ds_phase" id="ds_phase" tabindex="70">
                          <option value="">--All--</option>
                          @foreach($ds_phase_list as $key=>$val)
                            <option value="{{$key}}">{{$val}}</option>
                          @endforeach 
                          </select>
                          <span id="error_ds_phase" class="text-danger"></span>
              </div>
              @else
              <input type="hidden" name="ds_phase" id="ds_phase" value=""/>

            @endif
           
            @if($is_rural_visible)
            <div class="col-md-3">
            <label class="control-label">Rural/Urban </label>
            <select name="rural_urbanid" id="rural_urbanid" class="form-control">
                                        <option value="">-----All----</option>
                                        @foreach (Config::get('constants.rural_urban') as $key=>$value)
                                        <option value="{{$key}}"> {{$value}}</option>
                                        @endforeach
                </select>

              </div>
              @else
              <input type="hidden" name="rural_urbanid" id="rural_urbanid" value="{{$is_urban}}"/>
              @endif
               @if($urban_visible)
              <div class="col-md-3">
                                    <label class="control-label" id="blk_sub_txt">Block/Subdivision</label>
                                    <select name="urban_body_code" id="urban_body_code" class="form-control">
                                        <option value="">-----All----</option>

              </select>

            </div>
            @else
                          <input type="hidden" name="urban_body_code" id="urban_body_code" value="{{$urban_body_code}}"/>

             @endif
              @if($munc_visible)
              @if($mappingLevel=='District')
              </div>
              <div class="row" style="margin-bottom:1%">
              @endif
						<div class="col-md-3" id="municipality_div">
                                    <label class="control-label">Municipality</label>
                                    <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                                        <option value="">-----All----</option>
                                        @if(count($muncList)>0){
                                        @foreach ($muncList as $muncArr)
                                        <option value="{{$muncArr->urban_body_code}}">{{trim($muncArr->urban_body_name)}}</option>
                                        @endforeach
                                        }
                                        @endif

              </select>

            </div>
             @endif
              @if($gp_ward_visible)
             <div class="form-group col-md-3" id="gp_ward_div">
                <label class="" id="gp_ward_txt">GP/Ward</label>
                
                <select name="gp_ward_code" id="gp_ward_code" class="form-control" tabindex="17" >
                  <option value="">--All --</option>
                  @if(count($gpwardList)>0){
                                        @foreach ($gpwardList as $gp_ward_arr)
                                        <option value="{{$gp_ward_arr->gram_panchyat_code}}">{{trim($gp_ward_arr->gram_panchyat_name)}}</option>
                                        @endforeach
                                        }
                  @endif
                   
                </select>
                  <span id="error_gp_ward_code" class="text-danger"></span>
             
             </div>
              @endif
            </div>
             <div class="row">
          
					 <div class="col-md-2" style="margin-top: 28px;">
                                    <label class=" control-label">&nbsp; </label>
                                    <button type="button" name="filter" id="filter"
                                        class="btn btn-success">Filter</button>


          </div>
          <div class="col-md-offset-2" style="margin-top: 28px;">
                                    <label class=" control-label">&nbsp; </label>

                                    <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>

            </div>

					</div> 
          </form> 
          @if($download_excel==1)
          <form action="deacivated-list-Excel" method="post" id="excel_form">
          <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
           <input type="submit" name="submit" class="btn btn-info" id="excel-download" value="Export All Data to Excel"/>
          </form>  
          @endif      
					<div id="example2_wrapper" class="col-md-12 dataTables_wrapper form-inline dt-bootstrap js-report-form">
					
				
					<div class="col-md-offset-3 col-md-3">
						
					<h4><span class="label label-primary">{{$report_type_name}}</span></h4>
			  
					</div>
					<!-- <div class="col-md-offset-1 col-md-5 btn-group" role="group" >
						<button class="btn btn-success clsbulk_approve" id="bulk_approve" disabled>Approve Selected Beneficiaries</button>
					</div> -->
        <div class="col-md-12 text-center" id="loaderdiv" hidden>
          <img src="{{ asset('images/ZKZg.gif') }}" width="100px" height="100px"/>
        </div>  

        <div class="col-md-12" id="reportbody" style="margin-top: 2%;">
        <table id="example" class="display" cellspacing="0" width="100%">
          <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
          <input type="hidden" name="district_code" id="district_code" value="{{ $district_code }}">
          <thead>
                <tr role="row"> 
                <th>Application ID</th>
                <th>Applicant Name</th>
                <th>Father's Name</th>
                <th>Block/Municipality</th>
                <th>GP/Ward</th>
                <th>Rejection Type</th>
                <th>Rejection Details</th> 
              </tr>
          </thead>
          <tfoot>
              <tr>
               <th>Application ID</th>
                <th>Applicant Name</th>
                <th>Father's Name</th>
                <th>Block/Municipality</th>
                <th>GP/Ward</th>
                <th>Rejection Type</th>
                <th>Rejection Details</th>
              </tr>
          </tfoot>   
            
      </table>  
      <div class="row">
              
              <div class="col-sm-7">
                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                  
                </div>
              </div>
        </div>  

        </div>

      </div>
    




	

   
		@endsection
	



	<script src='{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}'></script>
  <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>


  function display_c(){
    var refresh=1000; // Refresh rate in milli seconds
    mytime=setTimeout('display_ct()',refresh)
  }

  function display_ct() {
    var x = new Date()
    document.getElementById('ct').innerHTML = x.toUTCString();
    display_c();
  } 
	
  $(document).ready(function(){ 
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #benListReport').addClass("active");         
    
    $('.sidebar-menu #Deactivated_NameValidation').addClass("active");         
    
   var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
   var base_url='{{ url('/') }}';  

  display_ct();	
	 
  $(".dataTables_scrollHeadInner").css({"width":"100%"});

  $(".table ").css({"width":"100%"});  

$('#rural_urbanid').change(function() {
       var rural_urbanid=$(this).val();
       if(rural_urbanid!=''){
        $('#urban_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>');
        $('#gp_ward_code').html('<option value="">--All --</option>');

        select_district_code= $('#district_code').val();
        //console.log(select_district_code);
        var htmlOption='<option value="">--All--</option>';
        if(rural_urbanid==1){
            $("#municipality_div").show();
            $("#blk_sub_txt").text('Subdivision');
            $("#gp_ward_txt").text('Ward');
            $.each(subDistricts, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        }
        else if(rural_urbanid==2){
          $("#municipality_div").hide();
          $("#blk_sub_txt").text('Block');
          $("#gp_ward_txt").text('GP');
          $.each(blocks, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        }
        $('#urban_body_code').html(htmlOption);
       }
       else{
          $("#blk_sub_txt").text('Block/Subdivision');
          $("#gp_ward_txt").text('GP/Ward');
          $('#urban_body_code').html('<option value="">--All --</option>');
          $('#block_ulb_code').html('<option value="">--All --</option>');
          $('#gp_ward_code').html('<option value="">--All --</option>');
       }     
  });
$('#urban_body_code').change(function() {
       var rural_urbanid= $('#rural_urbanid').val();
       if(rural_urbanid==1){
       var sub_district_code=$(this).val();
      
        $('#block_ulb_code').html('<option value="">--All --</option>');
        select_district_code= $('#district_code').val();
        var htmlOption='<option value="">--All--</option>';
       // console.log(sub_district_code);
        //console.log(select_district_code);

          $.each(ulbs, function (key, value) {
                if((value.district_code==select_district_code) && (value.sub_district_code==sub_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        $('#block_ulb_code').html(htmlOption);
       }else if(rural_urbanid==2){
          $('#muncid').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
          var block_code=$(this).val();
          select_district_code= $('#district_code').val();

          var htmlOption='<option value="">--All--</option>';
          $.each(gps, function (key, value) {
                if((value.district_code==select_district_code) && (value.block_code==block_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
          });
          //console.log(htmlOption);
          $('#gp_ward_code').html(htmlOption);
         
          $("#gp_ward_div").show();
       }
       else{
          $('#block_ulb_code').html('<option value="">--All --</option>');
          $('#gp_ward_code').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
          $("#gp_ward_div").hide();
       } 
       
      
  });

$('#block_ulb_code').change(function() {
      var muncid=$(this).val();
      var district=$("#district_code").val();
      var urban_code=$("#rural_urbanid").val();
      if(district==''){
        $('#rural_urbanid').val('');
        $('#urban_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        alert('Please Select District First');
        $("#district_code").focus();
        
    }
    if(urban_code==''){
        alert('Please Select Rural/Urban First');
        $('#urban_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        $("#urban_body_code").focus();
    }
    if(muncid!=''){
        var rural_urbanid= $('#rural_urbanid').val();
      if(rural_urbanid==1){
      
     
        $('#gp_ward_code').html('<option value="">--All --</option>');
        var htmlOption='<option value="">--All--</option>';
          $.each(ulb_wards, function (key, value) {
                if(value.urban_body_code==muncid){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        $('#gp_ward_code').html(htmlOption);
       
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
      table.clear().draw();
      table.ajax.reload();
  
  });
$('#reset').click(function(){
       window.location.href='deacivated-list';  
    });
    $('#excel-download').on('click',function(){
      
      var report_type=$("#report_type").val();
      $('#excel_form').append('<input type="hidden" name="report_type" id="report_type" value="'+report_type+'">');
      $("#excel_form").submit();
});
  var table=$('#example').DataTable( {
        dom: "Blfrtip",
        "paging": true,
        "pageLength":20,
        "lengthMenu": [[20, 50, 80, 120, 150], [20, 50, 80, 120, 150]],
		"serverSide": true,
		"deferRender": true,
        "processing":true,
        "bRetrieve": true,
        "scrollX": true,
        "ordering":false,
        "language": {
          "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
        },
        "ajax": 
        {
			url: "{{ url('deacivated-list') }}",
			type: "POST",
        data:function(d){
          d.report_type =$('#report_type').val(),
          d.ds_phase =$('#ds_phase').val(),
          d.district_code= "{{ $district_code }}",
          d.rural_urbanid =$('#rural_urbanid').val(),
          d.urban_body_code=$('#urban_body_code').val(),
          d.block_ulb_code= $('#block_ulb_code').val(),
          d.gp_ward_code= $('#gp_ward_code').val(),
          d._token= "{{csrf_token()}}",
          d.scheme = "{{ $scheme }}"
			  },
        error: function (ex) { 
                  alert(sessiontimeoutmessage);
                 // window.location.href=base_url;  
                 //console.log(ex);
      }
		  } ,
        "columns": [
          
           { "data": "application_id" },
           { "data": "ben_fname" },
           { "data": "father_name" },
           { "data": "block_ulb_name" },
           { "data": "gp_ward_name" },
           { "data": "rejected_type" },
           { "data": "rejected_by" }
           	 
          ],        
      
        "buttons": [
        {
		  extend: 'pdf',
		  exportOptions: {
                 columns: ':visible:not(:last-child)'
			},	
          title: "{{$report_type_name}}",
          messageTop: function () {
           var message = 'Report Renerated on: <?php echo date("l jS \of F Y h:i:s A"); ?>';               
            return message;
          },
          footer: true,
          pageSize:'A4',
          orientation: 'portrait',
          pageMargins: [ 40, 60, 40, 60 ],
        },
        {
		  extend: 'excel',
		
          title: "{{$report_type_name}}",
          messageTop: function () {
            var message = 'Report Renerated on: <?php echo date("l jS \of F Y h:i:s A"); ?>';            
            return message;
          },
          footer: true,
          pageSize:'A4',
          //orientation: 'landscape',
          pageMargins: [ 40, 60, 40, 60 ],
        },
        {
		  extend: 'print',
		  exportOptions: {
        columns: ':visible:not(:last-child)'
			},
          title: "{{$report_type_name}}",
          messageTop: function () {
            var message = 'Report Renerated on: <?php echo date("l jS \of F Y h:i:s A"); ?>';               
            return message;
          },
          footer: true,
          pageSize:'A4',
          //orientation: 'landscape',
          pageMargins: [ 40, 60, 40, 60 ],
        },
        ],
      } );
  });

  </script>
