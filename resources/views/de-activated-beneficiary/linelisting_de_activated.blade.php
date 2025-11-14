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
	padding:5px;
  color:#555;
  font-size:12px;
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
.disabledcontent {
  opacity: 0.4;
  pointer-events: none;
}
</style>

@extends('layouts.app-template-datatable_new')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      De-activated Beneficiary List
    </h1>
   
  </section>
  <section class="content">
    <div class="box box-default">
      <div class="box-body">
        @if($mapLevel == 'State' || $mapLevel == 'Department' || $mapLevel == 'District')
        <div class="panel panel-default">
          <div class="panel-heading">Filter Here</div>
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
            @if($mapLevel == 'District')
            <input type="hidden" name="dist_code" id="dist_code" value="{{$distCode}}">
            @endif
            <div class="row">
              <div class="col-md-12">
                @if($mapLevel == 'State' || $mapLevel == 'Department')
                <div class="form-group col-md-3">
                  <label class="control-label">District</label>
                  <select name="dist_code" id="dist_code" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach($districts as $dist) 
                      <option value="{{$dist->district_code}}">{{$dist->district_name}}</option>
                    @endforeach
                  </select>
                </div>
                @endif
                @if($mapLevel == 'District')
                <div class="form-group col-md-2">
                  <label class="control-label">Rural/Urban </label>
                  <select name="filter_1" id="filter_1" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach(Config::get('constants.rural_urban') as $key=>$val) 
                      <option value="{{$key}}">{{$val}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-3">
                  <label class="control-label" id="blk_sub_txt">Block/Sub Division </label>
                  <select name="filter_2" id="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div>
                @endif
          	    {{-- <div class="form-group col-md-3" id="municipality_div" style="display:none;">
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
                </div> --}}
                <div class="form-group col-md-2" style="margin-top: 24px;">
                  <button type="button" name="filter" id="filter" class="btn btn-success">Search</button>&nbsp;&nbsp;
                  <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
        <div class="panel panel-default">
          <div class="panel-heading" id="panel_head">List of Beneficiaries</div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;">
            <div class="table-responsive">
              <table id="example" class="display" cellspacing="0" width="100%"> 
                <thead style="font-size: 12px;">
                  <th>Sl No</th>
                  <th>Beneficiary ID</th>
                  <th>Application ID</th>
                  <th>Applicant Name</th>
                  <th>Swasthya Sathi Card No</th>
                  <th>Mobile No</th>
                  <th>Block/Municipality Name</th>
                  <th>GP/Ward Name</th>
                </thead>
                <tbody style="font-size: 14px;"></tbody>   
              </table>
            </div>
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
  // $('.sidebar-menu li').removeClass('active');
  // $('.sidebar-menu #bankTrFailed').addClass("active"); 
  // $('.sidebar-menu #accValTrFailedVerified').addClass("active"); 
  // $('.content').addClass('disabledcontent');
  // ------------------- Load Datatable Data ------------------------ //
  var dataTable = "";
  if ( $.fn.DataTable.isDataTable('#example')) {
    $('#example').DataTable().destroy();
  }
  var dataTable=$('#example').DataTable({
    dom: 'Blfrtip',
    paging: true,
    pageLength:20,
    // lengthMenu: [[10, 20, 30], [10, 20, 30]],
    lengthMenu: [[20, 50,100,500,1000,2000], [20, 50,100,500,1000,2000]],
    processing: true,
    serverSide: true,
    "oLanguage": {
      "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>' 
    },
    ajax:{
      url: "{{ url('getDeActivatedBenDataList') }}", 
      type: "POST",
      data:function(d){
        d.dist_code = $('#dist_code').val(),
        d.filter_1 = $('#filter_1').val(),
        d.filter_2 = $('#filter_2').val(),
        d._token= "{{csrf_token()}}"
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $('.content').removeClass('disabledcontent');
        $('.preloader1').hide();
        ajax_error(jqXHR, textStatus, errorThrown);
      }                
    },
    "initComplete":function(){
      $('.content').removeClass('disabledcontent');
      //console.log('Data rendered successfully');
    },
    columns: [      
      { "data": "DT_RowIndex" },      
      { "data": "beneficiary_id" },
      { "data": "application_id" },
      { "data": "name" },
      { "data": "ss_card_no" },
      { "data": "mobile_no" },
      { "data": "block_ulb_name" },
      { "data": "gp_ward_name" },
    ],       

    "buttons": [
      {
        extend: 'pdf',
        title: "District Wise Validation  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
        footer: true,
        pageSize:'A4',
         orientation: 'landscape',
        pageMargins: [ 40, 60, 40, 60 ],
        exportOptions: {
          columns: [0,1,2,3,4,5,6,7],
        }
      },
      {
        extend: 'excel', 
        title: "District Wise Validation  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
        footer: true,
        pageSize:'A4',
        //orientation: 'landscape',
        pageMargins: [ 40, 60, 40, 60 ],
        exportOptions: {
          columns: [0,1,2,3,4,5,6,7],
          stripHtml: false,
        }
      }   
    ],
  });
  // ------------------- Load Datatable Data End ------------------------ //

  // --------------- Filter Section -------------------- //
  $('#filter').click(function(){
    dataTable.ajax.reload();
  });

  $('#reset').click(function(){
    $('#dist_code').val('').trigger('change');
    $('#filter_1').val('').trigger('change');
    $('#filter_2').val('').trigger('change');
    $('#block_ulb_code').val('').trigger('change');
    $('#gp_ward_code').val('').trigger('change');
    dataTable.ajax.reload();
  });
  // --------------- Filter Section End-------------------- //

  // ------------ Master DropDown Section Start-------------------- //
  $('#dist_code').change(function(){
    $('#filter_1').val('').trigger('change');
    $('#filter_2').val('').trigger('change');
    $('#block_ulb_code').val('').trigger('change');
    $('#gp_ward_code').val('').trigger('change');
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
      // alert('Please Select Rural/Urban First');
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
  // ------------ Master DropDown Section End-------------------- //

});

</script>
@stop