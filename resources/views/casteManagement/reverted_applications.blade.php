<style>
  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }
  
  #example_wrapper {
    width: 100% !important;
  }
  /* Ensure proper DataTable layout */
  .dt-container {
    /* display: block !important; */
    width: 100% !important;
  }
  .dt-layout-table {
    display: block !important;
    width: 100% !important;
  }
  /* Remove any flex classes that might break the layout */
  .dt-container.d-flex {
    display: block !important;
  }
  .full-width{
    width:100%!important;
  }
  
  .bg-blue{
    background-image: linear-gradient(to right top, #0073b7, #0086c0, #0097c5, #00a8c6, #00b8c4)!important;
  }
  
  .bg-red{
    background-image: linear-gradient(to right bottom, #dd4b39, #ec6f65, #d21a13, #de0d0b, #f3060d)!important;
  }
  
  .bg-yellow{
    background-image: linear-gradient(to right bottom, #dd4b39, #e65f31, #ed7328, #f1881e, #f39c12)!important;
  }
  
  .bg-green{
    background-image: linear-gradient(to right bottom, #04736d, #008f73, #00ab6a, #00c44f, #5ddc0c)!important;
  }

  .bg-verify{
    background-image: linear-gradient(to right top, #f39c12, #f8b005, #fac400, #fad902, #f8ee15)!important;
  }
  
  .small-box-footer-custom{
    position: relative;
    text-align: center;
    padding: 3px 0;
    color: #fff;
    color: rgba(255,255,255,0.8);
    display: block;
    z-index: 10;
    background: rgba(0,0,0,0.1);
    text-decoration: none;
    font-family: 'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;
    font-weight: 400;
    width:100%;
  }
  
  .small-box-footer-custom:hover {
    color: #fff;
    background: rgba(0,0,0,0.15);
  }
  
  .searchPosition{
    margin:70px;
  }
  
  .submitPosition{
    margin: 25px 0px 0px 0px;
  }
  
  .criteria1{
    text-transform: uppercase;
    font-weight: bold;
  }
  
  .item_header{
    font-weight: bold;
  }
  
  .required-field::after {
    content: "*";
    color: red;
  }
  
  .table-responsive-custom {
    overflow-x: auto;
  }
  
  @media print {
    body * {
      visibility: hidden;
    }
    #ben_view_modal #ben_view_modal * {
      visibility: visible;
    }
    #ben_view_modal{
      position:absolute;
      left:0;
      top:0;
    }
    [class*="col-md-"] {
      float: none;
      display:table-cell;
    }
    [class*="col-lg-"] {
      float: none;
      display:table-cell;
    }
    .pagebreak { page-break-before: always; } 
  }
</style>

@extends('casteManagement.base')
@section('content')
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <!-- Main Card -->
        <div class="card mt-4">
          <div class="card-header card-header-custom">
            <h4 class="card-title mb-0"><b>Caste Reverted Applications List</b></h4>
          </div>
          <div class="card-body">
            <!-- Alert Messages -->
            <div class="alert-section">
              @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ $message }} for Beneficiary ID: {{$id}}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif
              
              @if ( ($message = Session::get('error')))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ $message }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif
              
              @if(count($errors) > 0)
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                  @foreach($errors->all() as $error)
                  <li><strong>{{ $error }}</strong></li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif
            </div>

            <!-- Filter Form -->
            <form name="formReport" id="formReport">
              <div class="row mb-3">
                @if(count($ds_phase_list)>0)
                <div class="col-md-4 mb-3">
                  <label for="ds_phase" class="form-label">Duare Sarkar Phase</label>
                  <select class="form-select" name="ds_phase" id="ds_phase" tabindex="70">
                    <option value="">--All--</option>
                    @foreach(Config::get('constants.ds_phase.phaselist') as $key=>$val)
                    <option value="{{$key}}">{{$val}}</option>
                    @endforeach 
                  </select>
                  <span id="error_ds_phase" class="text-danger small"></span>
                </div>
                @else
                <input type="hidden" name="ds_phase" id="ds_phase" value=""/>
                @endif
                
                @if($is_rural_visible)
                <div class="col-md-3 mb-3">
                  <label for="rural_urbanid" class="form-label">Rural/Urban</label>
                  <select name="rural_urbanid" id="rural_urbanid" class="form-select">
                    <option value="">-----All----</option>
                    @foreach (Config::get('constants.rural_urban') as $key=>$value)
                    <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                  </select>
                </div>
                @else
                <input type="hidden" name="rural_urbanid" id="rural_urbanid" value="{{$is_urban}}"/>
                @endif
                
                @if($urban_visible)
                <div class="col-md-3 mb-3">
                  <label for="urban_body_code" class="form-label" id="blk_sub_txt">Block/Subdivision</label>
                  <select name="urban_body_code" id="urban_body_code" class="form-select">
                    <option value="">-----All----</option>
                  </select>
                </div>
                @else
                <input type="hidden" name="urban_body_code" id="urban_body_code" value="{{$urban_body_code}}"/>
                @endif
                
                @if($munc_visible)
                <div class="col-md-3 mb-3" id="municipality_div">
                  <label for="block_ulb_code" class="form-label">Municipality</label>
                  <select name="block_ulb_code" id="block_ulb_code" class="form-select">
                    <option value="">-----All----</option>
                    @if(count($muncList)>0)
                      @foreach ($muncList as $muncArr)
                      <option value="{{$muncArr->urban_body_code}}">{{trim($muncArr->urban_body_name)}}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
                @endif
                
                @if($gp_ward_visible)
                <div class="col-md-3 mb-3" id="gp_ward_div">
                  <label for="gp_ward_code" class="form-label" id="gp_ward_txt">GP/Ward</label>
                  <select name="gp_ward_code" id="gp_ward_code" class="form-select" tabindex="17">
                    <option value="">--All --</option>
                    @if(count($gpwardList)>0)
                      @foreach ($gpwardList as $gp_ward_arr)
                      <option value="{{$gp_ward_arr->gram_panchyat_code}}">{{trim($gp_ward_arr->gram_panchyat_name)}}</option>
                      @endforeach
                    @endif
                  </select>
                  <span id="error_gp_ward_code" class="text-danger small"></span>
                </div>
                @endif
              </div>
              
              <div class="row mb-4">
                <div class="col-md-4 mb-3">
                  <label for="caste_category" class="form-label">New Caste</label>
                  <select class="form-select" name="caste_category" id="caste_category" tabindex="70">
                    <option value="">--All--</option>
                    @foreach(Config::get('constants.caste_lb') as $key=>$val)
                    <option value="{{$key}}">{{$val}}</option>
                    @endforeach 
                  </select>
                  <span id="error_caste_category" class="text-danger small"></span>
                </div>
                
                <div class="col-md-4 mb-3 d-flex align-items-end gap-2">
                  <button type="button" name="filter" id="filter" class="btn btn-success table-action-btn">
                    <i class="fas fa-filter me-1"></i> Filter
                  </button>
                  <button type="button" name="reset" id="reset" class="btn btn-warning table-action-btn">
                    <i class="fas fa-redo me-1"></i> Reset
                  </button>
                </div>
              </div>
            </form>
            <hr/>

            @if($download_excel==1)
            <form action="applicationListExcelCasteChange" method="post" class="mb-3">
              <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
              <button type="submit" name="submit" class="btn btn-info table-action-btn">
                <i class="fas fa-file-excel me-1"></i> Export All Data to Excel
              </button>
            </form>  
            @endif

            <!-- Report Section -->
            <div class="row text-center">
              <div class="col-md-12">
                <h4><span class="badge bg-primary">{{$report_type_name}}</span></h4>
              </div>
            </div>

            <div class="col-md-12 text-center" id="loaderdiv" hidden>
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>  

            <div class="table-responsive-custom" id="reportbody">
              <table id="example" class="data-table" style="width:100%">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <input type="hidden" name="district_code" id="district_code" value="{{ $district_code }}">
                <thead>
                  <tr> 
                    <th>Beneficiary ID</th>
                    <th>Beneficiary Name</th>
                    <th>Beneficiary Mobile No.</th>
                    <th width="12%">Edit</th>  
                  </tr>
                </thead>
              </table>  
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
@endsection

@push('scripts')
<script>
  function display_c(){
    var refresh=1000; // Refresh rate in milli seconds
    mytime=setTimeout('display_ct()',refresh)
  }

  function display_ct() {
    var x = new Date()
    if(document.getElementById('ct')) {
      document.getElementById('ct').innerHTML = x.toUTCString();
    }
    display_c();
  } 
  
  $(document).ready(function(){ 
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lb-caste').addClass("active");         
    $('.sidebar-menu #caste-revert').addClass("active");         
    
    var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
    var base_url='{{ url('/') }}';  

    display_ct();	
     
    // Initialize DataTable
    var table = $('#example').DataTable({
      dom: "Blfrtip",
      paging: true,
      pageLength: 20,
      lengthMenu: [[20, 50, 80, 120, 150], [20, 50, 80, 120, 150]],
      serverSide: true,
      deferRender: true,
      processing: true,
      bRetrieve: true,
      scrollX: true,
      ordering: false,
      language: {
        processing: '<div class="text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Processing...'
      },
      ajax: {
        url: "{{ url('lb-caste-reverted-list') }}",
        type: "POST",
        data: function(d) {
          d.ds_phase = $('#ds_phase').val(),
          d.district_code= "{{ $district_code }}",
          d.rural_urbanid = $('#rural_urbanid').val(),
          d.urban_body_code = $('#urban_body_code').val(),
          d.block_ulb_code = $('#block_ulb_code').val(),
          d.gp_ward_code = $('#gp_ward_code').val(),
          d.caste = $('#caste_category').val(),
          d.search_value = d.search.value,
            d.search = '',
          d._token = "{{csrf_token()}}"
        },
        error: function (ex) { 
          console.error('DataTables error:', ex);
          // alert(sessiontimeoutmessage);
          // window.location.href = base_url;  
        }
      },
      columns: [
        { "data": "beneficiary_id" },
        { "data": "ben_name" },
        { "data": "mobile_no" },
        { 
          "data": "Edit",
          "className": "text-center",
          "orderable": false,
          "searchable": false
        }  			 
      ],        
      buttons: [
        {
          extend: 'pdf',
          className: 'btn btn-secondary table-action-btn',
          exportOptions: {
            columns: ':visible:not(:last-child)'
          },	
          title: "{{$report_type_name}}",
          messageTop: function () {
            var message = 'Report Generated on: ' + new Date().toLocaleString();               
            return message;
          },
          footer: true,
          pageSize: 'A4',
          orientation: 'portrait',
        },
        {
          extend: 'csv',
          className: 'btn btn-success table-action-btn',
          exportOptions: {
            columns: ':visible:not(:last-child)',
            format: {
              body: function (data, row, column, node) {
                return column === 4 ? "\0" + data : data;
              }
            }
          },
          title: "{{$report_type_name}}",
          messageTop: function () {
            var message = 'Report Generated on: ' + new Date().toLocaleString();            
            return message;
          },
          footer: true,
          pageSize: 'A4',
          
        },
        {
          extend: 'print',
          className: 'btn btn-info table-action-btn',
          exportOptions: {
            columns: ':visible:not(:last-child)'
          },
          title: "{{$report_type_name}}",
          messageTop: function () {
            var message = 'Report Generated on: ' + new Date().toLocaleString();               
            return message;
          },
          footer: true,
          pageSize: 'A4',
          
        },
      ],
    });

    // Dropdown change handlers (original logic preserved)
    $('#rural_urbanid').change(function() {
      var rural_urbanid = $(this).val();
      if(rural_urbanid != ''){
        $('#urban_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>');
        $('#gp_ward_code').html('<option value="">--All --</option>');

        var select_district_code = $('#district_code').val();
        var htmlOption = '<option value="">--All--</option>';
        
        if(rural_urbanid == 1){
          $("#municipality_div").show();
          $("#blk_sub_txt").text('Subdivision');
          $("#gp_ward_txt").text('Ward');
          $.each(subDistricts, function (key, value) {
            if((value.district_code == select_district_code)){
              htmlOption += '<option value="'+value.id+'">'+value.text+'</option>';
            }
          });
        }
        else if(rural_urbanid == 2){
          $("#municipality_div").hide();
          $("#blk_sub_txt").text('Block');
          $("#gp_ward_txt").text('GP');
          $.each(blocks, function (key, value) {
            if((value.district_code == select_district_code)){
              htmlOption += '<option value="'+value.id+'">'+value.text+'</option>';
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
      var rural_urbanid = $('#rural_urbanid').val();
      if(rural_urbanid == 1){
        var sub_district_code = $(this).val();
        $('#block_ulb_code').html('<option value="">--All --</option>');
        var select_district_code = $('#district_code').val();
        var htmlOption = '<option value="">--All--</option>';

        $.each(ulbs, function (key, value) {
          if((value.district_code == select_district_code) && (value.sub_district_code == sub_district_code)){
            htmlOption += '<option value="'+value.id+'">'+value.text+'</option>';
          }
        });
        $('#block_ulb_code').html(htmlOption);
      } else if(rural_urbanid == 2){
        $('#muncid').html('<option value="">--All --</option>');
        $("#municipality_div").hide();
        var block_code = $(this).val();
        var select_district_code = $('#district_code').val();
        var htmlOption = '<option value="">--All--</option>';
        
        $.each(gps, function (key, value) {
          if((value.district_code == select_district_code) && (value.block_code == block_code)){
            htmlOption += '<option value="'+value.id+'">'+value.text+'</option>';
          }
        });
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
      var muncid = $(this).val();
      var district = $("#district_code").val();
      var urban_code = $("#rural_urbanid").val();
      
      if(district == ''){
        $('#rural_urbanid').val('');
        $('#urban_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        alert('Please Select District First');
        $("#district_code").focus();
      }
      
      if(urban_code == ''){
        alert('Please Select Rural/Urban First');
        $('#urban_body_code').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>'); 
        $("#urban_body_code").focus();
      }
      
      if(muncid != ''){
        var rural_urbanid = $('#rural_urbanid').val();
        if(rural_urbanid == 1){
          $('#gp_ward_code').html('<option value="">--All --</option>');
          var htmlOption = '<option value="">--All--</option>';
          $.each(ulb_wards, function (key, value) {
            if(value.urban_body_code == muncid){
              htmlOption += '<option value="'+value.id+'">'+value.text+'</option>';
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

    // Filter and Reset buttons
    $('#filter').click(function(){
      table.ajax.reload();
    });
    
    $('#reset').click(function(){
      window.location.href = 'lb-caste-application-list';  
    });
  });
</script>
@endpush