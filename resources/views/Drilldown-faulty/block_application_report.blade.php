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
      Block/Sub-division Wise Faulty Application Report<small>[Without Document]</small>
    </h1>
   
  </section>
  <section class="content">
    <div class="box box-default">
      <div class="box-body">
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
            <input type="hidden" name="dist_code" id="dist_code" value="{{ $distCode }}">
            <div class="row">
              <div class="col-md-12">
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
                <div class="form-group col-md-3" style="margin-top: 24px;">
                  <button type="button" name="filter" id="filter" class="btn btn-success"><i class="fa fa-search"></i> Search</button>&nbsp;&nbsp;
                  <button type="button" name="reset" id="reset" class="btn btn-warning"><i class="fa fa-refresh"></i> Reset</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading" id="panel_head">Block/Sub-division Wise Faulty Application Report</div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;">
            <div class="table-responsive">
              <table id="example" class="display" cellspacing="0" width="100%"> 
                <thead style="font-size: 12px;">
                  <th>Block/Sub-division Names</th>
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
@section('script')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
$(document).ready(function() {
  $('.sidebar-menu li').removeClass('active');
  $('.sidebar-menu #faultyMisReport').addClass("active"); 
  $('.sidebar-menu #faultyMisReportWithoutDocument').addClass("active");
  loadDatatable();
  $('#filter').click(function(){
    // dataTable.ajax.reload();
    loadDatatable();
  });
  $('#reset').click(function(){
    $('#filter_1').val('');
    loadDatatable();
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
      url: "{{ url('getFaultyBlockSubdivAppData') }}", 
      type: "POST",
      data:function(d){
        d.district_code = $('#dist_code').val(),
        d.filter_1 = $('#filter_1').val(),
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
      { "data": "bsm" },          
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

    "buttons": [
      {
        extend: 'pdf',
        title: "Faulty Block/Sub-division Wise Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
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
        title: "Faulty Block/Sub-division Wise Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
        footer: true,
        pageSize:'A4',
        //orientation: 'landscape',
        pageMargins: [ 40, 60, 40, 60 ],
        exportOptions: {
          columns: [0,1,2,3,4,5,6],
          stripHtml: false,
        }
      }   
    ],
  });
}
</script>
@stop