
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
                    <div class="form-row align-items-end">
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
                      <div class="form-group col-md-3">
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
                      <div class="form-group col-md-3">
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
						<div class="form-group col-md-3" id="municipality_div">
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
                   <div class="form-group col-md-3 mb-0">
                        <button type="button" name="submit" value="Submit" class="btn btn-success table-action-btn" id="search_sws">
                          <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
              </div>
              </form>
          @if($download_excel==1)
          <form action="deacivated-list-Excel" method="post" id="excel_form">
          <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
           <input type="submit" name="submit" class="btn btn-info" id="excel-download" value="Export All Data to Excel"/>
          </form>  
          @endif      
                <!-- DataTable Section -->
                <div class="table-container">
                  <div class="table-responsive">
                    <table id="example" class="display data-table" cellspacing="0" width="100%">
                      <thead class="table-header-spacing">
                        <tr role="row">
                           <th style="text-align: center">Application ID</th>
                          <th style="text-align: center">Applicant Name</th>
                          <th style="text-align: center">Father's Name</th>
                          <th style="text-align: center">Block/Municipality</th>
                          <th style="text-align: center">GP/Ward</th>
                          <th style="text-align: center">Rejection Type</th>
                          <th style="text-align: center">Rejection Details</th> 
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
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    // Sidebar menu activation
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lk-main').addClass("active");
    $('.sidebar-menu #lb-draft-list').addClass("active");

    // Variables
    var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
    var base_url = '{{ url("/") }}'; 
    var dataTable;
    $("#submitting, #ImportListMsg, .ImportLoader, #submittingapprove").hide();

 

  
    $('#rural_urbanid').change(function() {
       var rural_urbanid=$(this).val();
       if(rural_urbanid!=''){
        $('#block_ulb_code').html('<option value="">--All --</option>');
        $('#gp_ward_code').html('<option value="">--All --</option>');

        select_district_code= $('#district_code').val();
        //console.log(select_district_code);
        var htmlOption='<option value="">--All--</option>';
        if(rural_urbanid==1){
            $("#blk_munc_txt").text('Municipality');
            $("#gp_ward_txt").text('Ward');
             $.each(ulbs, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
            $('#block_ulb_code').html(htmlOption);
        }
        else if(rural_urbanid==2){
          $("#blk_munc_txt").text('Block');
          $("#gp_ward_txt").text('GP');
          $.each(blocks, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        }
        $('#block_ulb_code').html(htmlOption);
       }
       else{
          $("#blk_sub_txt").text('Block/Subdivision');
          $("#gp_ward_txt").text('GP/Ward');
          $('#urban_body_code').html('<option value="">--All --</option>');
          $('#block_ulb_code').html('<option value="">--All --</option>');
          $('#gp_ward_code').html('<option value="">--All --</option>');
       }     
     });
    $('#block_ulb_code').change(function() {
      var block_munc=$(this).val();
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
    if(block_munc!=''){
        var rural_urbanid= $('#rural_urbanid').val();
         if(rural_urbanid==1){
      
     
        $('#gp_ward_code').html('<option value="">--All --</option>');
        var htmlOption='<option value="">--All--</option>';
          $.each(ulb_wards, function (key, value) {
                if(value.urban_body_code==block_munc){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        $('#gp_ward_code').html(htmlOption);
       
       } 
       else if(rural_urbanid==2){
         var htmlOption='<option value="">--All--</option>';
         $.each(gps, function (key, value) {
                if((value.district_code==select_district_code) && (value.block_code==block_munc)){
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
    
    initializeDataTable();
    
    $(document).on('click', '.rej-btn', function() {
      var benid = $(this).val();
      $('#faultyReject #application_id').val(benid);
      $('#application_text_approve').text(benid);
      $('#modalReject').modal('show');
    });

    $('.modal-submitapprove').on('click', function(e) {
      e.preventDefault();
      var reject_cause = $("#reject_cause").val();
      if (reject_cause != '') {
        $(".modal-submitapprove").hide();
        $("#submittingapprove").show();
        $("#faultyReject").submit();
      } else {
        alert('Please Select Rejection Cause');
        $("#reject_cause").focus();
        return false;
      }
    });

    $('#modalReject').on('hidden.bs.modal', function() {
      $(".modal-submitapprove").show();
      $("#submittingapprove").hide();
      $("#reject_cause").val('');
    });
});
$('#search_sws').click(function() {
      initializeDataTable();
});
  $('#excel-download').click(function(){
      $('#excel_form').append('<input type="hidden" name="report_type" id="report_type" value="'+report_type+'">');
      $("#excel_form").submit();
     
 });     
function initializeDataTable() {
      if ($.fn.DataTable.isDataTable('#example')) {
        $('#example').DataTable().destroy();
      }

      dataTable = $('#example').DataTable({
        // dom: 'Blfrtip',
        "paging": true,
        "pageLength": 20,
        "lengthMenu": [
          [10, 20, 50, 80, 120],
          [10, 20, 50, 80, 120]
        ],
        "serverSide": true,
        "deferRender": true,
        "processing": true,
        "bRetrieve": true,
        "ordering": false,
        "searching": true,
        "language": {
          "processing": "Processing...",
          "emptyTable": "No data available in table",
          "zeroRecords": "No matching records found"
        },
        "ajax": {
        
          "url": "{{ URL('deacivated-list')}}",
          "type": "GET",
          "data": function(d) {
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
          "error": function(xhr, error, thrown) {
            console.error("DataTables AJAX error:", thrown);
            if (xhr.status === 401 || xhr.status === 419) {
              alert(sessiontimeoutmessage);
              // window.location.href = base_url;
            } else {
              alert("An error occurred while loading data: " + thrown);
            }
          }
        },
        "columns": [
          
           { "data": "application_id" },
           { "data": "ben_fname" },
           { "data": "father_name" },
           { "data": "block_ulb_name" },
           { "data": "gp_ward_name" },
           { "data": "rejected_type" },
           { "data": "rejected_by" }
           	 
          ], 
       
    });  
  }
function closeError(divId) {
    $('#' + divId).hide();
  }

  function printMsg(msg, msgtype, divid) {
    $("#" + divid).find("ul").html('');
    $("#" + divid).css('display', 'block');
    if (msgtype == '0') {
      $("#" + divid).removeClass('alert-success').addClass('alert-warning');
    } else {
      $("#" + divid).removeClass('alert-warning').addClass('alert-success');
    }

    if (Array.isArray(msg)) {
      $.each(msg, function(key, value) {
        $("#" + divid).find("ul").append('<li>' + value + '</li>');
      });
    } else {
      $("#" + divid).find("ul").append('<li>' + msg + '</li>');
    }
  }
    
</script>

@endpush
