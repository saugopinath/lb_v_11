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
      <form method="post" id="register_form" action="{{url('lb-wcd-search')}}" class="submit-once">
        {{ csrf_field() }}

        <div class="tab-content" style="margin-top:16px;">
          <div class="tab-pane active" id="personal_details">
            <!-- Card with your design -->
            <div class="card" id="res_div">
              <div class="card-header card-header-custom">
                <h4 class="card-title mb-0"><b>Lakhasmir Bhandar Draft Applications List</b></h4>
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
                      @if(count($ds_phase_list)>0)
                      <div class="form-group col-md-4 mb-0">
                        <label class="form-label font-weight-bold">Duare Sarkar Phase</label>
                        <select class="form-control" name="ds_phase" id="ds_phase">
                          @foreach($ds_phase_list as $ds_row)
                          <option value="{{$ds_row->phase_code}}">{{$ds_row->phase_des}}</option>
                          @endforeach
                          <option value="0">Normal Entry</option>
                        </select>
                        <span id="error_ds_phase" class="text-danger"></span>
                      </div>
                      <div class="form-group col-md-3 mb-0">
                        <button type="button" name="submit" value="Submit" class="btn btn-success table-action-btn" id="search_sws">
                          <i class="fas fa-search"></i> Search
                        </button>
                      </div>
                      @else
                      <input type="hidden" name="ds_phase" id="ds_phase" value="" />
                      @endif
                    </div>
                  </div>
                </div>

                <!-- DataTable Section -->
                <div class="table-container">
                  <div class="table-responsive">
                    <table id="example" class="display data-table" cellspacing="0" width="100%">
                      <thead class="table-header-spacing">
                        <tr role="row">
                          <th style="width:25%;text-align: center">Application Id</th>
                          <th style="width:25%;text-align: center">Applicant Name</th>
                          <th style="width:25%;text-align: center">Mobile Number</th>
                          <th style="width:25%;text-align: center">Action</th>
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

<!-- Reject Modal -->
<div id="modalReject" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Application</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="{{ route('partialReject')}}" name="faultyReject" id="faultyReject">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" id="application_id" name="application_id" />
        <div class="modal-body">
          <p>Do you really want to Reject the application (<span id="application_text_approve"></span>)?</p>
          <div class="form-group">
            <label class="required-field" for="reject_cause">Select Reject Cause</label>
            <select name="reject_cause" id="reject_cause" class="form-control">
              <option value="">--Select--</option>
              @foreach($reject_revert_reason as $r_arr)
              <option value="{{$r_arr->id}}">{{$r_arr->reason}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger modal-submitapprove table-action-btn">Reject</button>
          <button type="button" id="submittingapprove" class="btn btn-success" disabled style="display:none;">
            Submitting please wait...
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
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

    $('#modalReject .close').on('click', function() {
      $('#modalReject').modal('hide');
    });

    $(document).on('click', '#modalReject .btn-secondary[data-dismiss="modal"]', function(e) {
      e.preventDefault();
      $('#modalReject').modal('hide');
    });

    // Initialize DataTable
    function initializeDataTable() {
      if ($.fn.DataTable.isDataTable('#example')) {
        $('#example').DataTable().destroy();
      }

      dataTable = $('#example').DataTable({
        dom: 'Blfrtip',
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
          "url": "{{ url('lb-draft-list') }}",
          "type": "GET",
          "data": function(d) {
            d.ds_phase = $("#ds_phase").val();
            d._token = "{{csrf_token()}}";
          },
          "error": function(xhr, error, thrown) {
            console.error("DataTables AJAX error:", thrown);
            if (xhr.status === 401 || xhr.status === 419) {
              alert(sessiontimeoutmessage);
              window.location.href = base_url;
            } else {
              alert("An error occurred while loading data: " + thrown);
            }
          }
        },
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
      });
    }

    initializeDataTable();

    $('#search_sws').click(function() {
      dataTable.ajax.reload();
    });
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