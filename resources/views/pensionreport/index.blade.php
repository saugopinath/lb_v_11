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
<div class="container-fluid">
  <div class="row">
    <div class="col-12 mt-4">
      <form method="post" id="register_form"  class="submit-once">
        {{ csrf_field() }}

        <div class="tab-content" style="margin-top:16px;">
          <div class="tab-pane active" id="personal_details">
            <!-- Card with your design -->
            <div class="card" id="res_div">
              <div class="card-header card-header-custom">
                <h4 class="card-title mb-0"><b>{{$report_type_name}}</b></h4>
              </div>
              <div class="card-body" style="padding: 20px;">
                <!-- Alert Messages -->
                <div class="alert-section">
                  @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ $message }} with Application ID: {{$id}}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if ($message = Session::get('error') )
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if(count($errors) > 0)
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul>
                      @foreach($errors as $error)
                      <li><strong> {{ $error }}</strong></li>
                      @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  <div class="alert print-error-msg" style="display:none;" id="errorDivMain">
                    <button type="button" class="close" aria-label="Close" onclick="closeError('errorDivMain')">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <ul></ul>
                  </div>
                </div>

                <!-- Search Section -->
                <div class="row mb-4">
                  <div class="col-md-12">
                        <form name="formReport" id="formReport">
           
          	<div class="row" style="margin-bottom:1%">
            @if(count($ds_phase_list)>0)
           <div class="form-group col-md-4">
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
            <div class="col-md-2">
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
             <div class="form-group col-md-4" id="gp_ward_div">
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
             <div class="form-group col-md-4">
                          <label class="">Caste</label>
                          <select class="form-control" name="caste_category" id="caste_category" tabindex="70">
                          <option value="">--All--</option>
                          @foreach(Config::get('constants.caste_lb') as $key=>$val)
                            <option value="{{$key}}">{{$val}}</option>
                          @endforeach 
                          </select>
                          <span id="error_caste_category" class="text-danger"></span>
              </div>
					 <div class="col-md-2" style="margin-top: 28px;">
                                    <label class=" control-label">&nbsp; </label>
                                    <button type="button" name="filter" id="filter"
                                        class="btn btn-success">Filter</button>
                                         <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>


          </div>
    

					
          </form> 
                    
                 
                  </div>
                
                </div>
              </div>
               
                <!-- DataTable Section -->
                  @if($download_excel==1)
          <form action="applicationListExcel" method="post" id="formexcel">
           <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
           <input type="hidden" name="type" id="type" value="{{$type}}">
           <input type="submit" name="submit" id="excel" class="btn btn-info" value="Export All Data to Excel"/>
          </form>  
          @endif      
                <div class="table-container">
                  <div class="table-responsive">
                    <table id="example" class="display data-table" cellspacing="0" width="100%">
                      <thead class="table-header-spacing">
                        <tr role="row">
                          <th style="text-align: center">Application Id</th>
                          <th style="text-align: center">Applicant Name</th>
                          <th style="text-align: center">Mobile Number</th>
                          <th style="text-align: center">Father's Name</th>
                          <th style="text-align: center">Action</th>
                        </tr>
                      </thead>
                      <tbody style="font-size: 14px;">
                        <!-- DataTables will populate this dynamically -->
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>



@endsection

@push('scripts')


	<script src='{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}'></script>
  <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
  $(document).ready(function(){ 
    var application_type='{{$type}}';
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #benListReport').addClass("active");         
    if(application_type=='V'){
    $('.sidebar-menu #VerifiedApplication').addClass("active");         
    }
    else if(application_type=='A'){
      $('.sidebar-menu #ApprovedApplication').addClass("active");         
    }
    else if(application_type=='T'){
      $('.sidebar-menu #RevertedApplication').addClass("active");         
    }
   else if(application_type=='R'){
      $('.sidebar-menu #RejectedApplication').addClass("active");         
    }else if(application_type=='F'){
      $('.sidebar-menu #FaultyApplication').addClass("active");         
    }else if(application_type=='PEL'){
      $('.sidebar-menu #PartialApplication').addClass("active");         
    }
   var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
   var base_url='{{ url('/') }}';  


	 
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
       window.location.href='application-list-common?type={{$type}}';  
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
          "processing": "Processing...",
          "emptyTable": "No data available in table",
          "zeroRecords": "No matching records found"
        },
        "ajax": 
        {
			url: "{{ url('application-list-common') }}",
			type: "POST",
        data:function(d){
          d.ds_phase =$('#ds_phase').val(),
          d.district_code= "{{ $district_code }}",
          d.rural_urbanid =$('#rural_urbanid').val(),
          d.urban_body_code=$('#urban_body_code').val(),
          d.block_ulb_code= $('#block_ulb_code').val(),
          d.gp_ward_code= $('#gp_ward_code').val(),
          d.caste= $('#caste_category').val(),
          d._token= "{{csrf_token()}}",
          d.scheme = "{{ $scheme }}",
          d.type = "{{$type}}"
			  },
        error: function (ex) { 
                  //alert(sessiontimeoutmessage);
                 // window.location.href=base_url;  
                 //console.log(ex);
      }
		  } ,
        "columns": [{
            "data": "application_id",
            "className": "text-center"
          },
          {
            "data": "name",
            "className": "text-center"
          },
          {
            "data": "mobile_no",
            "className": "text-center"
          },
            {
            "data": "father_name",
            "className": "text-center"
          },
          {
            "data": "Action",
            "className": "text-center",
            "orderable": false,
            "searchable": false
          }
        ],
         "buttons": [{
            extend: 'pdf',
            footer: true,
            exportOptions: {
              columns: [0, 1, 2, 3]
            },
            className: 'table-action-btn'
          },
          {
            extend: 'print',
            footer: true,
            exportOptions: {
              columns: [0, 1, 2, 3]
            },
            className: 'table-action-btn'
          },
          {
            extend: 'excel',
            footer: true,
            exportOptions: {
              columns: [0, 1, 2, 3]
            },
            className: 'table-action-btn'
          },
          {
            extend: 'copy',
            footer: true,
            exportOptions: {
              columns: [0, 1, 2, 3]
            },
            className: 'table-action-btn'
          },
          {
            extend: 'csv',
            footer: true,
            exportOptions: {
              columns: [0, 1, 2, 3]
            },
            className: 'table-action-btn'
          }
        ]
      } );
      $('#excel').click(function(){
      $('#formexcel').append('<input type="hidden" name="ds_phase" id="ds_phase" value=' + $('#ds_phase').val() + '>');
      $('#formexcel').append('<input type="hidden"  name="rural_urban" id="rural_urban" value=' + $('#rural_urbanid').val() + '>');
      $('#formexcel').append('<input type="hidden"  name="urban_block_code_app" id="urban_block_code_app" value=' + $('#urban_body_code').val() + '>');
      $('#formexcel').append('<input type="hidden"  name="municipality_code" id="municipality_code" value=' + $('#block_ulb_code').val() + '>');
      $('#formexcel').append('<input type="hidden" name="gp_ward_code_app" id="gp_ward_code_app" value=' + $('#gp_ward_code').val() + '>');
      $('#formexcel').submit();
   });
  });

  </script>
@endpush