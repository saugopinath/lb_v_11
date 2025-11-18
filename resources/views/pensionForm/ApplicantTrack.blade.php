@push('styles')
  <link rel="stylesheet" href="{{ asset('AdminLTE_3/dist/css/adminlte.min.css') }}">
  <style>
    :root {
      --primary: #0b3c9b;
      --secondary: #2d80b5;
      --success: #178f4f;
      --light: #f7f9ff;
    }

    body {
      background: var(--light);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Timeline Styles */
    .timeline-wrap {
      position: relative;
      background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
      border: 1px solid #e0e8f1;
      border-radius: 12px;
      padding: 32px 24px;
      overflow-x: auto;
      overflow-y: hidden;
      white-space: nowrap;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    .timeline-scroller {
      overflow-x: auto;
      white-space: nowrap;
      padding: 0;
      display: inline-block;
      position: relative;
      width: 100%;
    }

    .timeline {
      display: flex;
      gap: 24px;
      min-height: 200px !important;
      padding: 16px 8px;
      position: relative;
      align-items: flex-start;
      justify-content: center;
      margin-bottom: 10px;
    }

    .tl-card {
      min-width: 260px;
      max-width: 280px;
      background: #ffffff;
      border: 2px solid #e0e8f1;
      border-radius: 10px;
      padding: 18px 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      position: relative;
      word-wrap: break-word;
      overflow: hidden;
    }

    .tl-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 20px rgba(45, 128, 181, 0.15);
      border-color: #2d80b5;
    }

    .tl-date {
      font-weight: 700;
      color: #0b3c9b;
      margin-bottom: 12px;
      font-size: 0.95rem;
    }

    .tl-text {
      font-size: 0.9rem;
      color: #4a5568;
      margin: 8px 0 20px;
      line-height: 1.5;
      flex-grow: 1;
      overflow-wrap: break-word;
    }

    .timeline::before {
      content: '';
      position: absolute;
      top: 60px;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #2d80b5 0%, #3aa0d2 50%, #2d80b5 100%);
      z-index: 1;
      border-radius: 2px;
    }

    .tick {
      text-align: center;
    }

    @media (max-width: 768px) {
      .tl-card {
        min-width: 220px;
      }
    }
  </style>
@endpush

@extends('layouts.app-template-datatable')
@section('content')

  <div class="container-fluid">
    <div class="row">
      <div class="col-12 mt-4">
        <form id="track_form" method="POST">
          @csrf
          <div class="card">
            <div class="card-header">
              <h4 class="card-title mb-0"><b>Track Applicant</b></h4>
            </div>
            <div class="card-body">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label>Search Using</label>
                  <select class="form-control" id="select_type" name="select_type">
                    <option value="">--Select--</option>
                    <option value="1">Application Id</option>
                    <option value="2">Beneficiary Id</option>
                    <option value="3">Mobile Number</option>
                    <option value="4">Aadhar Card Number</option>
                    <option value="5">Bank Account Number</option>
                    <option value="6">Swasthyasathi Card No</option>
                  </select>
                  <span id="error_select_type" class="text-danger"></span>
                </div>

                <div class="col-md-6" id="input_val_div" style="display:none;">
                  <label id="selectValueName">Value</label>
                  <input type="text" id="applicant_id" name="applicant_id" class="form-control" placeholder="Enter value"
                    autocomplete="off" />
                  <span id="error_applicant_id" class="text-danger"></span>
                </div>
              </div>

              <input type="hidden" id="scheme_code" name="scheme_code" value="{{ $scheme_id ?? '' }}">
              <button type="button" id="searchbtn" class="btn btn-success">
                <i class="fas fa-search"></i> Search
              </button>
            </div>
          </div>

          <!-- Result Section -->
          <div id="ajaxData" class="mt-4" style="display:none;">
            <div class="card shadow-sm card-outline card-primary">
              <div class="card-header bg-light">
                <h5 class="text-primary mb-0 fw-bold">
                  Application Status (Name - <span id="span_ben_name"></span>,
                  Beneficiary Id - <span class="span_ben_id"></span>,
                  Application Id - <span id="span_app_id"></span>)
                </h5>
              </div>
              <div class="card-body p-0">
                <div id="timelineContainer">
                  <!-- Timeline HTML from controller will be inserted here -->
                </div>
              </div>
            </div>

            <!-- Payment Section -->
            <h4 class="text-center fw-bold text-success mb-3 paymentStatusDiv" style="display:none;">
              Payment Status (Beneficiary Id - <span class="span_ben_id"></span>)
            </h4>

            <div class="accordion paymentStatusDiv" id="paymentAccordion" style="display:none;">
              <div class="accordion-item">
                <div id="collapseOne" class="accordion-collapse collapse show">
                  <div class="accordion-body">
                    <div class="row mb-3 align-items-center">
                      <div class="col-md-6">
                        <label>Select Financial Year</label>
                      </div>
                      <div class="col-md-6">
                        <select class="form-select w-auto d-inline-block" onchange="changeFinancialYear(this.value)"
                          id="fin_year">
                          @foreach (Config::get('constants.fin_year') as $key => $fin_year)
                            <option value="{{ $key }}" {{ $key == $currentFinYear ? 'selected' : '' }}>
                              {{ $fin_year }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div id="div_payment_list" class="table-responsive">
                      <table class="table table-bordered align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>Month</th>
                            <th>Payment Status</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
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
  <script>
    $(document).ready(function () {
      $('#select_type').change(function () {
        if ($(this).val() !== "") {
          $('#input_val_div').show();
          $('#selectValueName').text($("#select_type option:selected").text());
        } else {
          $('#input_val_div').hide();
        }
      });

      $('#searchbtn').click(function () {
        const scheme_code = $('#scheme_code').val();
        const applicant_id = $('#applicant_id').val();
        const select_type = $('#select_type').val();

        if (!select_type) {
          $('#error_select_type').text('Please select a filter');
          return;
        } else $('#error_select_type').text('');

        if (!applicant_id) {
          $('#error_applicant_id').text('This field is required');
          return;
        } else $('#error_applicant_id').text('');

        $('#ajaxData').hide();
        $('#timelineContainer').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

        $.ajax({
          type: 'GET',
          url: '{{ url('track-applicant-status') }}',
          data: {
            is_public: 0,
            scheme_code: scheme_code,
            applicant_id: applicant_id,
            trackType: select_type,
            _token: '{{ csrf_token() }}'
          },
          success: function (data) {
            alert(data);
            $('#ajaxData').show();
            $('#timelineContainer').html(data);
          },
          error: function () {
            $('#ajaxData').show();
            $('#timelineContainer').html('<div class="alert alert-danger">Error fetching data. Please try again.</div>');
          }
        });
      });
    });

    // Payment Status AJAX
    function changeFinancialYear(fin_year) {
      const ben_id = $('.span_ben_id').text().trim();
      if (!ben_id) return;

      $.ajax({
        type: "POST",
        url: "{{ route('getPaymentDetailsFinYearWiseInTrackApplicationPost') }}",
        data: {
          _token: '{{ csrf_token() }}',
          ben_id: ben_id,
          fin_year: fin_year
        },
        success: function (response) {
          $('#div_payment_list tbody').html(response.paymentDetails || '');
        },
        error: function () {
          alert('Error loading payment details.');
        }
      });
    }
  </script>
@endpush