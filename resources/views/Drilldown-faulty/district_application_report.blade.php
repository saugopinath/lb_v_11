<style type="text/css">
  .required-field::after {
    content: "*";
    color: red;
  }
  .has-error
  {
    border-color:#dc3545;
    background-color:#f8d7da;
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

.card-header {
  padding: 0;
    border:0;
}
.card-title>a, .card-title>a:active{
  display:block;
  padding:5px;
  color:#555;
  font-size:8px;
  font-weight:bold;
    text-transform:uppercase;
    letter-spacing:1px;
  word-spacing:3px;
    text-decoration:none;
}
.card-header  a:before {
   font-family: 'Glyphicons Halflings';
   content: "\e114";
   float: right;
   transition: all 0.5s;
}
.card-header.active a:before {
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


<div class="container-fluid">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Districtwise Faulty Application Report<small>[Without Document]</small>
    </h1>
   
  </section>
  <section class="content">
    <div class="card card-default">
      <div class="card-body">
        <div class="card card-default">
          <div class="card-header card-header-custom">Filter Here</div>
          <div class="card-body" style="padding: 5px;">
            <div class="row">
              @if ( ($message = Session::get('success')))
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong>{{ $message }}</strong>
        
              </div>
              @endif
              @if(count($errors) > 0)
              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <ul>
                  @foreach($errors->all() as $error)
                  <li><strong> {{ $error }}</strong></li>
                  @endforeach
                </ul>
              </div>
              @endif
            </div>

            <div class="row">
              
                <div class="form-group col-md-3">
                  <label class="control-label">District </label>
                  <select name="dist_code" id="dist_code" class="form-control">
                    <option value="">-----All----</option>
                    @foreach ($district as $k)
                    <option value="{{ $k->district_code }}"> {{ $k->district_name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-3">
                  <label class="control-label">Rural/Urban </label>
                  <select name="filter_1" id="filter_1" class="form-control">
                    <option value="">-----All----</option>
                    @foreach ($levels as $key=>$value)
                    <option value="{{$key}}"> {{$value}}</option>
                    @endforeach
                  </select>
                </div>
                {{-- <div class="form-group col-md-3">
                  <label class="control-label" id="blk_sub_txt">Block/Sub Division </label>
                  <select name="filter_2" id="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div> --}}

                <div class="col-md-4 mb-3 d-flex align-items-end gap-2">
                  <button type="button" name="filter" id="filter" class="btn btn-success table-action-btn">
                    <i class="fas fa-search me-1"></i> Search
                  </button>
                  <button type="button" name="reset" id="reset" class="btn btn-warning table-action-btn">
                    <i class="fas fa-redo me-1"></i> Reset
                  </button>
                </div>

                <!-- <div class="form-group col-md-3" style="margin-top: 24px;">
                  <button type="button" name="filter" id="filter" class="btn btn-success"><i class="fa fa-search"></i> Search</button>&nbsp;&nbsp;
                  <button type="button" name="reset" id="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>
                </div> -->
              
            </div>
          </div>
        </div>

        <div class="card card-default">
          <div class="card-header card-header-custom" id="panel_head">Districtwise Faulty Application Report</div>
          <div class="card-body" style="padding: 5px; font-size: 14px;">
            <div class="table-responsive">
              <table id="example" class="display data-table" cellspacing="0" width="100%"> 
                <thead style="font-size: 12px;">
                  <th>District/Block/Sub-divsion</th>
                  <th>Total Unedited Faulty</th>
                  <th>Total Edited</th>
                  <th>Verification Pending</th>
                  <th>Total Verified</th>
                  <th>Approval Pending</th>
                  <th>Total Approved</th>
                </thead>
                <tbody style="font-size: 14px;"></tbody>
                <tfoot style="font-size: 14px;">
                  <tr>
                    <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                  </tr>
                </tfoot>   
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>

@endsection
@push('scripts')
<script>
$(document).ready(function() {
  $('.nav-sidebar li').removeClass('active');
  $('.nav-sidebar #faultyMisReport').addClass("active"); 
  $('.nav-sidebar #faultyMisReportWithoutDocument').addClass("active");
  
  loadDatatable();
  $('#filter').click(function(){
      loadDatatable();
  });
  $('#reset').click(function(){
    $('#dist_code').val('');
    $('#filter_1').val('');
    loadDatatable();
  });
  $('#filter_1').change(function(){
    var dist_code = $('#dist_code').val();
    if (dist_code == '') {
      $.alert({
        title : 'Alert', 
        content: 'Please select district first'
      });
      $('#filter_1').val('');
    }
  });
});
function loadDatatable(){
  if ( $.fn.DataTable.isDataTable('#example')) {
    $('#example').DataTable().destroy();
  }
  var dataTable=$('#example').DataTable({
    dom: 'Blfrtip',
    paging: false,
    pageLength:30,
    lengthMenu: [[30, 50], [30, 50]],
    // lengthMenu: [[20, 50,100,500,1000, -1], [20, 50,100,500,1000, 'All']],
    processing: true,
    serverSide: true,
    "oLanguage": {
      "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>' 
    },
    ajax:{
      url: "{{ url('getFaultyDistAppData') }}", 
      type: "POST",
      data:function(d){
        d.district_code = $('#dist_code').val(),
        d.rural_urban = $('#filter_1').val(),
        d._token= "{{csrf_token()}}"
      },
      error: function (jqXHR, textStatus, errorThrown) {
        // $('.content').removeClass('disabledcontent');
        $('.preloader1').hide();
        ajax_error(jqXHR, textStatus, errorThrown);
      }                
    },
    "initComplete":function(){
      // $('.content').removeClass('disabledcontent');
      //console.log('Data rendered successfully');
    },
    columns: [
      { "data": "district_name" },          
      { "data": "total_applicant" },
      { "data": "total_edited" },
      { "data": "ver_pending" },
      { "data": "verified" },
      { "data": "app_pending" },
      { "data": "approved" },
    ],
    "footerCallback": function ( row, data, start, end, display ) {
      var api = this.api(), data;
      // converting to interger to find total
      var intVal = function ( i ) {
          return typeof i === 'string' ?
              i.replace(/[\$,]/g, '')*1 :
              typeof i === 'number' ?
                  i : 0;
      };
            
      // computing column Total of the complete result 
      var applicantTotal = api
        .column( 1 )
        .data()
        .reduce( function (a, b) {
            return intVal(a) + intVal(b);
        }, 0 );
                    
      var editedTotal = api
        .column( 2 )
        .data()
        .reduce( function (a, b) {
            return intVal(a) + intVal(b);
        }, 0 );
      var pendingVerifiedTotal = api
        .column( 3 )
        .data()
        .reduce( function (a, b) {
            return intVal(a) + intVal(b);
        }, 0 ); 
      var verifiedTotal = api
        .column( 4 )
        .data()
        .reduce( function (a, b) {
            return intVal(a) + intVal(b);
        }, 0 );
      var pendingApprovalTotal = api
        .column( 5 )
        .data()
        .reduce( function (a, b) {
            return intVal(a) + intVal(b);
        }, 0 );   
      var approvedTotal = api
        .column( 6 )
        .data()
        .reduce( function (a, b) {
            return intVal(a) + intVal(b);
        }, 0 );  
                    
      // Update footer by showing the total with the reference of the column index 
      $( api.column( 0 ).footer() ).html('Total');
      $( api.column( 1 ).footer() ).html(applicantTotal);
      $( api.column( 2 ).footer() ).html(editedTotal);
      $( api.column( 3 ).footer() ).html(pendingVerifiedTotal);
      $( api.column( 4 ).footer() ).html(verifiedTotal);
      $( api.column( 5 ).footer() ).html(pendingApprovalTotal);
      $( api.column( 6 ).footer() ).html(approvedTotal);
    },

    "buttons": [{
        extend: 'pdf',
        title: "Faulty District Wise Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
        footer: true,
        pageSize:'A4',
         orientation: 'landscape',
        pageMargins: [ 40, 60, 40, 60 ],
        exportOptions: {
          columns: [0,1,2,3,4,5,6],
        }
      },
      {
        extend: 'excel', 
        title: "Faulty District Wise Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
        footer: true,
        pageSize:'A4',
        //orientation: 'landscape',
        pageMargins: [ 40, 60, 40, 60 ],
        exportOptions: {
          columns: [0,1,2,3,4,5,6],
          stripHtml: false,
        },
      }  
    ],
  });
}
</script>
@endpush