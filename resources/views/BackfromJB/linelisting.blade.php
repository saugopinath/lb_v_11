@extends('layouts.app-template-datatable')
@push('styles')

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
</style>

@section('content')
  <div class="container-fluid py-4">

    {{-- Alerts --}}
    <div class="row mb-4">
      @foreach (['message', 'success', 'error'] as $msg)
        @if(Session::get($msg))
          <div
            class="alert alert-{{ $msg == 'error' ? 'danger' : 'success' }} alert-dismissible fade show w-100 shadow-lg border-0 rounded-3"
            role="alert">
            <div class="d-flex align-items-center">
              <i class="bi bi-info-circle-fill me-2 fs-5"></i>
              <strong class="me-auto">{{ Session::get($msg) }}</strong>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
      @endforeach

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show w-100 shadow-lg border-0 rounded-3" role="alert">
          <h6 class="alert-heading fw-bold">Validation Error:</h6>
          <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
    </div>

    {{-- Filter Card --}}
    <div class="card shadow-lg border-0 mb-4 rounded-3">
      <div class="card-header card-header-custom">
        <h4 class="mb-0 fw-bold">Application Filtering </h4>
      </div>
      <div class="card-body p-4">
        <form method="post" id="register_form" action="{{ url('BulkApprovePds') }}" class="submit-once">
          @csrf
          <input type="hidden" id="scheme_id" name="scheme_id" value="{{ $scheme_id }}">
          <input type="hidden" name="action_type" id="action_type" value="1">
          <input type="hidden" name="dist_code" id="dist_code" value="{{ $district_code }}">

          <div class="row g-4 align-items-end">
            {{-- Application Type --}}
            <div class="col-md-4 col-lg-3">
              <label for="application_type" class="form-label fw-semibold mb-1">Application Type</label>
              <select name="application_type" id="application_type" class="form-select shadow-sm rounded-2">
                <option value="1" selected>Pending</option>
                @if($designation_id == 'Verifier')
                  <option value="2">Verified but Approval Pending</option>
                @endif
                <option value="3">Verified and Approved</option>
              </select>
              <small id="error_application_type" class="text-danger mt-1 d-block"></small>
            </div>

            {{-- Block/Subdivision / GP / Ward Controls --}}
            @if($verifier_type == 'Block')
              <div class="col-md-4 col-lg-3">
                <label for="gp_ward_code" class="form-label fw-semibold mb-1">Gram Panchayat</label>
                <select name="gp_ward_code" id="gp_ward_code" class="form-select shadow-sm rounded-2">
                  <option value="">--- Select GP ---</option>
                  @foreach ($gps as $gp)
                    <option value="{{ $gp->gram_panchyat_code }}">{{ $gp->gram_panchyat_name }}</option>
                  @endforeach
                </select>
              </div>
              <input type="hidden" id="block_ulb_code" name="block_ulb_code">
              <input type="hidden" id="rural_urban_code" name="rural_urban_code" value="{{ $is_rural }}">
              <input type="hidden" id="created_by_local_body_code" name="created_by_local_body_code"
                value="{{ $created_by_local_body_code }}">
            @endif

            @if($verifier_type == 'Subdiv')
              <div class="col-md-3">
                <label for="block_ulb_code" class="form-label fw-semibold mb-1">Municipality</label>
                <select name="block_ulb_code" id="block_ulb_code" class="form-select shadow-sm select2 rounded-2">
                  <option value="">--- Select Municipality ---</option>
                  @foreach ($urban_bodys as $urban_body)
                    <option value="{{ $urban_body->urban_body_code }}">{{ $urban_body->urban_body_name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <label for="gp_ward_code" class="form-label fw-semibold mb-1">Ward</label>
                <select name="gp_ward_code" id="gp_ward_code" class="form-select shadow-sm select2 rounded-2">
                  <option value="">--- Select Ward ---</option>
                </select>
              </div>

              <input type="hidden" id="rural_urban_code" name="rural_urban_code" value="{{ $is_rural }}">
              <input type="hidden" id="created_by_local_body_code" name="created_by_local_body_code"
                value="{{ $created_by_local_body_code }}">
            @endif

            {{-- Approver Filters --}}
            @if($designation_id == 'Approver')
              <div class="col-md-3">
                <label for="rural_urban_code" class="form-label fw-semibold mb-1">Urban / Rural</label>
                <select name="rural_urban_code" id="rural_urban_code" class="form-select shadow-sm rounded-2">
                  <option value="">--- All ---</option>
                  @foreach(Config::get('constants.rural_urban') as $key => $val)
                    <option value="{{ $key }}">{{ $val }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <label id="blk_sub_txt" for="created_by_local_body_code" class="form-label fw-semibold mb-1">Block/Sub
                  Division</label>
                <select name="created_by_local_body_code" id="created_by_local_body_code"
                  class="form-select shadow-sm select2 rounded-2">
                  <option value="">--- Select ---</option>
                </select>
              </div>
            @else
              <input type="hidden" name="process_type" id="process_type">
            @endif

            {{-- Buttons --}}
            <div class="col-md-12 col-lg-3 d-flex pt-2">
              <button type="button" id="filter" class="btn btn-primary px-4 me-3 shadow-sm rounded-2 fw-semibold">
                <i class="bi bi-funnel me-1"></i> Filter
              </button>
              <button type="button" id="reset" class="btn btn-outline-secondary px-4 shadow-sm rounded-2 fw-semibold">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
              </button>
            </div>
          </div>
        </form>

        {{-- Bulk Action Button (For District/Approver) --}}
        {{-- @if($verifier_type == 'District' && $designation_id == 'Approver')
          <button id="confirm" class="btn btn-success mt-4 px-5 shadow-lg rounded-3 fw-bold" disabled>
            <i class="bi bi-check-circle-fill me-2"></i> Bulk Approve
          </button>
        @endif --}}
      </div>
    </div>

    {{-- Table Card --}}
    <div class="card shadow-lg border-0 mt-4 rounded-3">
      <div class="card-header card-header-custom">
        <h5 class="mb-0 fw-bold text-dark">Application List</h5>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive">
          <table id="example" class="data-table">
            <thead>
              <tr>
                <th>Application ID</th>
                <th>Beneficiary Name</th>
                <th>Mobile Number</th>
                <th>DOB</th>
                @if($verifier_type == 'Subdiv' || $verifier_type == 'District')
                  <th>Block / Municipality</th>
                @endif
                <th>GP / Ward Name</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Confirmation Modal --}}
    <div id="modalConfirm" class="modal fade" tabindex="-1" aria-labelledby="modalConfirmLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header bg-success text-white rounded-top-4">
            <h5 class="modal-title" id="modalConfirmLabel">Confirm Approval</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center py-4 px-4">
            <div class="text-success mb-3 fs-1">
              <i class="bi bi-shield-check"></i>
            </div>
            <h4 class="fw-bold mb-0">Do you really want to Approve?</h4>
            <p class="text-muted mt-1">This action cannot be undone.</p>
          </div>
          <div class="modal-footer justify-content-center border-0 pt-0 pb-3">
            <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="confirm_yes" class="btn btn-success px-4 rounded-pill fw-semibold">Approve
              Now</button>

            {{-- Loader for submitting state --}}
            <button id="submittingapprove" class="btn btn-success px-4 rounded-pill fw-semibold d-none" disabled>
              <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Submitting...
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
  $(document).ready(function() {
    $("#confirm").hide();
    $("#submittingapprove").hide();
    
    var base_url='{{ url('/') }}';
    var block_ulb_code=$("#block_ulb_code").val();
    var gp_ward_code=$("#gp_ward_code").val();
    var application_type=$("#application_type").val();
  fill_datatable(block_ulb_code,gp_ward_code,application_type);
  function fill_datatable(block_ulb_code = '',gp_ward_code = '',application_type = ''){
    //console.log(process_type);
       var scheme_id=$("#scheme_id").val();
        var dataTable=$('#example').DataTable( {
      dom: 'Blfrtip',
      paging: true,
      pageLength:20,
      ordering: false,
      lengthMenu: [[20, 50,100,500,1000, -1], [20, 50,100,500,1000, 'All']],
      processing: true,
      serverSide: true,
      ajax:{
            url: "{{ url('backfromjb') }}",
            type: "GET",
            data:function(d){
                 d.block_ulb_code= block_ulb_code,
                 d.gp_ward_code= gp_ward_code,
                 d.scheme_id= scheme_id,
                 d.type= $('#type').val(),
                 d.application_type= application_type,
                 d._token= "{{csrf_token()}}"
            },
            error: function (ex) {
              //console.log(ex);
             alert('Session time out..Please login again');
            window.location.href=base_url;
           }                       
      },
      columns: [
                
        { "data": "application_id" },
        { "data": "name" },
        { "data": "mobile_no" },
        { "data": "dob"},
        @if($verifier_type=='Subdiv' || $verifier_type=='District')
        { "data": "block_ulb_name" },
        @endif
        { "data": "gp_ward_name" },
        { "data": "action" },
       
       // { "data": "check" },
              
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
            extend: 'csv',
            footer: true,
            exportOptions: {
              columns: [0, 1, 2, 3]
            },
            className: 'table-action-btn'
          }

          ],        
    
    } );
   }

    $('#filter').click(function(){
        var block_ulb_code = $('#block_ulb_code').val();
        var gp_ward_code = $('#gp_ward_code').val();
        var application_type = $('#application_type').val();
        var designation_id = $('#designation_id').val();
        var error_application_type='';
        var error_process_type='';
        if(application_type=='')
        {
          error_application_type = 'Application Type is required';
          $('#error_application_type').text(error_application_type);
          $('#application_type').addClass('has-error');
        }
        else
        {
          error_application_type = '';
          $('#error_application_type').text(error_application_type);
          $('#application_type').removeClass('has-error');
        }
        
        if(error_application_type=='' ){
          //console.log(process_type);
          $('#example').DataTable().destroy();
          fill_datatable(block_ulb_code,gp_ward_code,application_type);
        }
        
       
    });
    $('#block_ulb_code').change(function() {
      var municipality_code=$(this).val();
       if(municipality_code!=''){
        $('#gp_ward').html('<option value="">--All --</option>');
        var htmlOption='<option value="">--All--</option>';
          $.each(ulb_wards, function (key, value) {
                if(value.urban_body_code==municipality_code){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        $('#gp_ward_code').html(htmlOption);
       }
       else{
          $('#gp_ward_code').html('<option value="">--All --</option>');
       } 
    });
    $('#rural_urban_code').change(function() {
       var urban_code=$(this).val();
        if(urban_code==''){
          $('#created_by_local_body_code').html('<option value="">--All --</option>'); 
        }
        $('#created_by_local_body_code').html('<option value="">--All --</option>'); 
        select_district_code= $('#dist_code').val();
       //console.log(select_district_code);
        
        select_body_type= urban_code;
        var htmlOption='<option value="">--All--</option>';
        if(select_body_type==2){
            $("#blk_sub_txt").text('Block');
            $.each(blocks, function (key, value) {
                if(value.district_code==select_district_code){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        }else if(select_body_type==1){
            $("#blk_sub_txt").text('Subdivision');
            $.each(subDistricts, function (key, value) {
                if(value.district_code==select_district_code){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        } 
        else{
          $("#blk_sub_txt").text('Block/Subdivision');
        }   
        $('#created_by_local_body_code').html(htmlOption);
        

    });
      $('#reset').click(function(){
        $('#application_type').val('');
        $('#gp_code').val('');
        $('#gp_code').val('');
        $('#example').DataTable().destroy();
        fill_datatable();
    });
    $('#confirm').click(function(){      
      $('#modalConfirm').modal();
    });
    $('#confirm_yes').on('click',function(){
        $("#confirm_yes").hide();
        $("#submittingapprove").show();
        $("#register_form").submit();
        
       
      });

  } );
  function controlCheckBox(){
    //console.log('ok');
    var anyBoxesChecked = false;
    $(' input[type="checkbox"]').each(function() {
      if ($(this).is(":checked")) {
        anyBoxesChecked = true;
      }
    });
    if (anyBoxesChecked == true) {
      $("#confirm").show();
      document.getElementById('confirm').disabled = false;
    } else{
      $("#confirm").hide();
      document.getElementById('confirm').disabled = true;
    }
  }
</script>
@endpush