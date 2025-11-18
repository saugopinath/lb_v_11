<style type="text/css">
  .required-field::after {
    content: "*";
    color: red;
  }

  .has-error {
    border-color: #dc3545;
    background-color: #f8d7da;
  }

  .preloader1 {
    position: fixed;
    top: 40%;
    left: 52%;
    z-index: 999;
  }

  .preloader1 {
    background: transparent !important;
  }

  /* .card-header {
    padding: 0;
    border: 0;
  } */

  .card-title>a,
  .card-title>a:active {
    display: block;
    padding: 5px;
    color: #555;
    font-size: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    word-spacing: 3px;
    text-decoration: none;
  }

  .card-header a:before {
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

  #enCloserTable tbody tr td {
    padding: 10px 10px 10px 10px;
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
      Block/Sub-division Wise Faulty Application Report<small>[Without Document]</small>
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
            <input type="hidden" name="dist_code" id="dist_code" value="{{ $distCode }}">
            <div class="row m-2">
              
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
                <!-- <div class="form-group col-md-3" style="margin-top: 24px;"> -->
                  <div class="col-md-4 mb-3 d-flex align-items-end gap-2">
                  <button type="button" name="filter" id="filter" class="btn btn-success table-action-btn">
                    <i class="fas fa-search me-1"></i> Search
                  </button>
                  <button type="button" name="reset" id="reset" class="btn btn-warning table-action-btn">
                    <i class="fas fa-redo me-1"></i> Reset
                  </button>
                </div>
              
            </div>
          </div>
        </div>

        <div class="card card-default">
          <div class="card-header card-header-custom" id="panel_head">Block/Sub-division Wise Faulty Application Report</div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="example" class="display data-table" cellspacing="0" width="100%">
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
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
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
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #faultyMisReport').addClass('active');
    $('.sidebar-menu #faultyMisReportWithoutDocument').addClass('active');
    loadDatatable();
    $('#filter').click(function() {
      loadDatatable();
    });
    $('#reset').click(function() {
      $('#filter_1').val('');
      loadDatatable();
    });
  });

  function loadDatatable() {

    if ($.fn.DataTable.isDataTable('#example')) {
      $('#example').DataTable().destroy();
    }

    var dataTable = $('#example').DataTable({
      dom: 'Bfrtip',
      paging: false,             // <-- if you want server-side paging, set this true
      pageLength: 30,
      lengthMenu: [
        [30, 50],
        [30, 50]
      ],
      processing: true,
      serverSide: true,          // <-- if you want client-side totals use serverSide: false
      "oLanguage": {
        "sProcessing": '<div class="preloader1" align="center"><img src="/images/ZKZg.gif" width="150px"></div>'
      },
      ajax: {
        url: "{{ url('getFaultyBlockSubdivAppData') }}",
        type: "POST",
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: function(d) {
          d.district_code = $('#dist_code').val();
          d.filter_1 = $('#filter_1').val();
          // you already include _token in headers, but if you prefer to include in data too:
          d._token = "{{ csrf_token() }}";
          return d;
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('.preloader1').hide();
          if (typeof ajax_error === 'function') {
            ajax_error(jqXHR, textStatus, errorThrown);
          } else {
            console.error('Ajax error:', textStatus, errorThrown, jqXHR);
          }
        }
      },
      "initComplete": function() {
        // console.log('Data rendered successfully');
      },
      columns: [
        {"data": "bsm"},
        {"data": "total_applicant"},
        {"data": "total_edited"},
        {"data": "ver_pending"},
        {"data": "verified"},
        {"data": "app_pending"},
        {"data": "approved"}
      ],
      "footerCallback": function(row, data, start, end, display) {
        var api = this.api();

        // convert to integer
        var intVal = function(i) {
          return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 :
                 typeof i === 'number' ? i : 0;
        };

        // NOTE: with serverSide: true the column().data() will contain only rows returned by server
        // If you need grand totals across all rows, compute them on server and return in JSON
        var applicantTotal = api.column(1).data().reduce(function(a, b) {
          return intVal(a) + intVal(b);
        }, 0);

        var editedTotal = api.column(2).data().reduce(function(a, b) {
          return intVal(a) + intVal(b);
        }, 0);

        var pendingVerifiedTotal = api.column(3).data().reduce(function(a, b) {
          return intVal(a) + intVal(b);
        }, 0);

        var verifiedTotal = api.column(4).data().reduce(function(a, b) {
          return intVal(a) + intVal(b);
        }, 0);

        var pendingApprovalTotal = api.column(5).data().reduce(function(a, b) {
          return intVal(a) + intVal(b);
        }, 0);

        var approvedTotal = api.column(6).data().reduce(function(a, b) {
          return intVal(a) + intVal(b);
        }, 0);

        $(api.column(0).footer()).html('Total');
        $(api.column(1).footer()).html(applicantTotal);
        $(api.column(2).footer()).html(editedTotal);
        $(api.column(3).footer()).html(pendingVerifiedTotal);
        $(api.column(4).footer()).html(verifiedTotal);
        $(api.column(5).footer()).html(pendingApprovalTotal);
        $(api.column(6).footer()).html(approvedTotal);
      },

      buttons: [
        
      ]
    });
  }
</script>
@endpush
