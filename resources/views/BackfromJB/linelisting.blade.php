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
        @if($verifier_type == 'District' && $designation_id == 'Approver')
          <button id="confirm" class="btn btn-success mt-4 px-5 shadow-lg rounded-3 fw-bold" disabled>
            <i class="bi bi-check-circle-fill me-2"></i> Bulk Approve
          </button>
        @endif
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
  <script>
    $(document).ready(function () {

      let table;

      function loadTable(block_ulb_code = '', gp_ward_code = '', application_type = '') {
        let scheme_id = $("#scheme_id").val();
        let created_by_local_body_code = $('#created_by_local_body_code').val();
        let rural_urban_code = $('#rural_urban_code').val();

        // This check determines if the Block/ULB column should be displayed
        let show_block_ulb = "{{ $verifier_type }}" === 'Subdiv' || "{{ $verifier_type }}" === 'District';

        if ($.fn.DataTable.isDataTable('#example')) {
          table.destroy();
        }

        let columns = [
          { data: "application_id", searchable: true },
          { data: "ben_name" },
          { data: "mobile_no" },
          { data: "dob" }
        ];

        if (show_block_ulb) columns.push({ data: "block_ulb_name" });

        columns.push(
          { data: "gp_ward_name" },
          { data: "action", orderable: false, searchable: true }
        );
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
          ajax: {
            url: "{{ url('backfromjb') }}",
            type: "GET",
            data: {
              block_ulb_code: block_ulb_code,
              gp_ward_code: gp_ward_code,
              scheme_id: scheme_id,
              application_type: application_type,
              created_by_local_body_code: created_by_local_body_code,
              rural_urban_code: rural_urban_code,
            },
            error: function (xhr) { console.error("DataTable AJAX Error:", xhr.responseText); }
          },
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
          columns: columns
        });

        // Enable/Disable the Bulk Approve button based on loaded data / application type
        // Assuming bulk approval is only possible when 'Verified but Approval Pending' (value '2') is selected
        table.on('draw.dt', function () {
          let appType = $('#application_type').val();
          let isApproverRole = "{{ $verifier_type }}" === 'District' && "{{ $designation_id }}" === 'Approver';

          if (isApproverRole && appType == '2') {
            $('#confirm').prop('disabled', false);
          } else {
            $('#confirm').prop('disabled', true);
          }
        });
      }

      // Initial load
      // The initial load should use the default value of the dropdowns
      loadTable($('#block_ulb_code').val(), $('#gp_ward_code').val(), $('#application_type').val());

      // Filter
      $('#filter').click(function () {
        let block_ulb_code = $('#block_ulb_code').val();
        let gp_ward_code = $('#gp_ward_code').val();
        let application_type = $('#application_type').val();

        if (!application_type) {
          $('#error_application_type').text('Application Type is required');
          return;
        } else {
          $('#error_application_type').text('');
        }

        loadTable(block_ulb_code, gp_ward_code, application_type);
      });

      // Reset
      $('#reset').click(function () {
        // Reset filter inputs to default values
        $('#application_type').val('1').trigger('change');
        $('#block_ulb_code').val('').trigger('change');
        $('#gp_ward_code').val('').trigger('change');
        $('#rural_urban_code').val('').trigger('change');
        $('#created_by_local_body_code').val('').trigger('change');

        loadTable();
      });

      // Handle Municipaliy/Ward logic for Subdiv (AJAX required here)
      $('#block_ulb_code').on('change', function () {
        // TODO: Implement AJAX call here to fetch Wards based on selected Municipality (this.value)
        // and populate the #gp_ward_code dropdown.
      });

      // Confirm approve modal
      $('#confirm').click(function () {
        // Reset modal buttons before showing
        $('#confirm_yes').show();
        $("#submittingapprove").addClass('d-none');
        $('#modalConfirm').modal('show');
      });

      // Submit action from modal
      $('#confirm_yes').click(function () {
        // Show loader, hide OK button
        $(this).hide();
        $("#submittingapprove").removeClass('d-none');
        $('#action_type').val('2');
        $("#register_form").submit();
      });
      $('#rural_urban_code').on('change', function () {
        let selectedValue = $(this).val();
        if (selectedValue == 'R') {
          $('#blk_sub_txt').text('Block');
        } else if (selectedValue == 'U') {
          $('#blk_sub_txt').text('Municipality/ULB');
        } else {
          $('#blk_sub_txt').text('Block/Sub Division');
        }
      }).trigger('change'); // Trigger on load to set initial label
    });
  </script>
@endpush