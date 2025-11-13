@extends('layouts.app-template-datatable')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE_3/dist/css/adminlte.min.css') }}">
<style>
  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

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
    display: flex;
  }

  .timeline-scroller {
    overflow-x: auto;
    white-space: nowrap;
    padding: 0;
    display: flex;
    position: relative;
    width: 100%;
    padding-top: 10px;
  }

  .timeline {
    display: flex;
    gap: 24px;
    min-height: 200px !important;
    position: relative;
    align-items: flex-start;
    justify-content: center;
    padding-top: 10px;
    margin: 0% !important;
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
    word-break: break-word;
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
    line-height: 1.4;
    white-space: normal;
  }

  .tl-text {
    font-size: 0.9rem;
    color: #4a5568;
    margin: 8px 0 20px;
    line-height: 1.5;
    flex-grow: 1;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Hide vertical line completely */
  .timeline-line {
    display: none !important;
  }

  /* Add horizontal connecting line between cards */
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
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 8px;
  }

  /* Search section improvements */
  .search-section {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
  }

  /* Status section styling */
  .status-section {
    margin-top: 30px;
  }

  /* Payment status improvements */
  .payment-status-section h4 {
    color: #198754;
    margin-bottom: 20px;
  }

  /* Ensure collapse has overflow hidden so slide animations look clean */
  .accordion-collapse {
    overflow: hidden;
  }

  /* Responsive adjustments */
  @media (max-width: 992px) {
    .main-box {
      margin: 16px;
    }

    .tl-card {
      min-width: 240px;
      max-width: 260px;
    }

    .header-section {
      padding: 15px 20px;
    }
  }

  @media (max-width: 768px) {
    .timeline::before {
      display: none;
    }

    .timeline {
      flex-direction: column;
      align-items: center;
      white-space: normal;
    }

    .tl-card {
      min-width: 100%;
      max-width: 100%;
    }
  }

  @media (max-width: 576px) {
    .main-box {
      padding: 15px;
    }

    .timeline-wrap {
      padding: 24px 16px;
    }

    .timeline {
      min-height: 220px !important;
      gap: 16px;
    }

    .status-head {
      flex-direction: column;
      gap: 10px;
    }

    .tl-card {
      min-width: 100%;
      max-width: 100%;
    }

    .search-section .form-group {
      margin-bottom: 15px;
    }
  }
</style>
@endpush

@section('content')
<!-- Main content -->
<div class="container-fluid">
  <div class="row">
    <div class="col-12 mt-4">
      <form method="post" id="register_form" class="submit-once">
        {{ csrf_field() }}

        <div class="tab-content" style="margin-top:16px;">
          <div class="tab-pane active" id="personal_details">
            <!-- Card with your design -->
            <div class="card" id="res_div">
              <div class="card-header card-header-custom">
                <h4 class="card-title mb-0"><b>Track Applicant</b></h4>
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
                    <div class="form-row">
                      <input type="hidden" name="scheme_code" id="scheme_code" value="{{ $scheme_id }}">
                      <div class="form-group col-md-6">
                        <label class="">Search Using</label>
                        <select class="form-control" name="select_type" id="select_type">
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

                      <div class="form-group col-md-6" id="input_val_div" style="display: none;">
                        <label class="control-label"><span id="selectValueName">Value</span> <span
                            class="text-danger">*</span> </label>
                        <input type="text" name="applicant_id" id="applicant_id" class="form-control"
                          placeholder="Enter value" autocomplete="off" style="font-size: 16px;"
                          onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" />
                        <span id="error_applicant_id" class="text-danger"></span>
                      </div>
                    </div>
                  </div>
                  <div class="form-group col-md-3 mb-0">
                    <button type="button" name="submit" value="Submit" class="btn btn-success table-action-btn" id="searchbtn">
                      <i class="fas fa-search"></i> Search
                    </button>
                  </div>
                </div>

                <!-- <div id="ajaxData"></div> -->

              </div>
            </div>
          </div>
        </div>
      </form>

      <div class="status-section">
        <div class="card shadow-sm mb-4 card-outline card-primary">
          <div class="card-header card-header-custom border-0">
            <h5 class="card-title mb-0">Application Status</h5>
          </div>
          <div class="card-body p-0">
            <div class="timeline-wrap">
              <div class="timeline-scroller">
                <div class="timeline" id="timeline">
                  <!-- cards -->
                  <div class="tl-card">
                    <div class="tl-date">25-08-2021 03:15:46</div>
                    <div class="tl-text">
                      Application Temporary Saved by DASPUR-II Block Development Officer (Operator: ******2688).
                    </div>
                    <div class="tick">
                      <svg width="40" height="40" viewBox="0 0 48 48">
                        <circle cx="24" cy="24" r="20" fill="#ffffff" stroke="#3aa0d2" stroke-width="4" />
                        <path d="M14 25.5l6 6 14-14" fill="none" stroke="#3aa0d2" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                  </div>
                  <div class="tl-card">
                    <div class="tl-date">25-08-2021 03:18:36</div>
                    <div class="tl-text">
                      Application Final Submitted by DASPUR-II Block Development Officer (Operator: ******2688).
                    </div>
                    <div class="tick">
                      <svg width="40" height="40" viewBox="0 0 48 48">
                        <circle cx="24" cy="24" r="20" fill="#ffffff" stroke="#3aa0d2" stroke-width="4" />
                        <path d="M14 25.5l6 6 14-14" fill="none" stroke="#3aa0d2" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                  </div>
                  <div class="tl-card">
                    <div class="tl-date">29-08-2021 04:08:47</div>
                    <div class="tl-text">
                      Application Verified by DASPUR-II Block Development Officer (Verifier).
                    </div>
                    <div class="tick">
                      <svg width="40" height="40" viewBox="0 0 48 48">
                        <circle cx="24" cy="24" r="20" fill="#ffffff" stroke="#3aa0d2" stroke-width="4" />
                        <path d="M14 25.5l6 6 14-14" fill="none" stroke="#3aa0d2" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                  </div>
                  <div class="tl-card">
                    <div class="tl-date">29-08-2021 05:02:57</div>
                    <div class="tl-text">
                      Application Approved by MEDINIPUR WEST District Officer (Approver).
                    </div>
                    <div class="tick">
                      <svg width="40" height="40" viewBox="0 0 48 48">
                        <circle cx="24" cy="24" r="20" fill="#ffffff" stroke="#3aa0d2" stroke-width="4" />
                        <path d="M14 25.5l6 6 14-14" fill="none" stroke="#3aa0d2" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr>

      <!-- Payment Status Section -->
      <h4 class="text-center fw-bold text-success mb-3">Payment Status</h4>

      <div class="accordion mb-3" id="paymentAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button
              class="accordion-button fw-bold"
              type="button"
              data-target="#collapseOne"
              aria-expanded="false"
              aria-controls="collapseOne">
              Name – APARNA KARMAKAR, Beneficiary Id – 208789445,
              Application Id – 124458094
            </button>
          </h2>
          <div
            id="collapseOne"
            class="accordion-collapse collapse"
            data-parent="#paymentAccordion">
            <div class="accordion-body">
              <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                  <label>Which financial year you want to view payment
                    status?</label>
                </div>
                <div class="col-md-6">
                  <select class="form-select w-auto d-inline-block" id="fin_year_select_example">
                    <option value="2025-2026">2025-2026</option>
                    <option value="2024-2025">2024-2025</option>
                  </select>
                </div>
              </div>

              <p class="fw-semibold mb-1">
                Bank Account Status : <span class="text-success">Validation Success. Ready For Payment</span>
              </p>
              <p class="fw-semibold mb-1">
                Beneficiary Status : <span class="text-success">Active beneficiary</span>
              </p>
              <p class="mb-1">
                Bank A/C No : 38xxxxxxxx758, IFSC : SBINxxxxx65
              </p>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Month</th>
                      <th>Payment Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>January 2025</td>
                      <td>Payment Success</td>
                    </tr>
                    <tr>
                      <td>February 2025</td>
                      <td>Payment Success</td>
                    </tr>
                    <tr>
                      <td>March 2025</td>
                      <td>Payment Success</td>
                    </tr>
                    <tr>
                      <td>April 2025</td>
                      <td>Payment Pending</td>
                    </tr>
                    <tr>
                      <td>May 2025</td>
                      <td>Payment Success</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- end accordion -->

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {

    // === existing init & search logic ===
    $('#loaderDiv').hide();
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lk-main').addClass("active");
    $('.sidebar-menu #appplicantTrack').addClass("active");

    var sessiontimeoutmessage = '{{ $sessiontimeoutmessage ?? '' }}';
    var base_url = '{{ url('/') }}';

    var PleaseSelectScheme = '@lang('lang.PleaseSelectScheme')';
    var PleaseEnterApplicationId = '@lang('lang.PleaseEnterApplicationId')';

    $('#input_val_div').hide();
    $('#search_msg').html('');

    $('#select_type').change(function() {
      $('#search_msg').html('');
      if ($('#select_type').val() != "") {
        $('#input_val_div').show();
        $('#applicant_id').val('');
        var selectedVal = $("#select_type option:selected").text();
        $('#selectValueName').text(selectedVal);
        $("#applicant_id").attr("placeholder", "Enter " + selectedVal);
        $('#error_applicant_id').text('');
      } else {
        $('#input_val_div').hide();
      }
    });

    var error_select_type = '';
    var error_applicant_id = '';
    $("#searchbtn").click(function() {
      if ($.trim($('#select_type').val()).length == 0) {
        error_select_type = 'Track filter is required';
        $('#error_select_type').text(error_select_type);
      } else {
        error_select_type = '';
        $('#error_select_type').text(error_select_type);
      }

      if ($.trim($('#applicant_id').val()).length == 0) {
        error_applicant_id = 'This field is required';
        $('#error_applicant_id').text(error_applicant_id);
      } else {
        error_applicant_id = '';
        $('#error_applicant_id').text(error_applicant_id);
      }

      if (error_select_type == '' && error_applicant_id == '') {

        $('#resultDivPaymentStatus').hide();
        var scheme_code = $("#scheme_code").val();
        var applicant_id = $("#applicant_id").val();
        var scheme_type = $('#select_type').val();

        var selectedValueUsing = $("#select_type option:selected").text();
        $('#search_msg').html('Search using with ' + selectedValueUsing + ' - ' + applicant_id);

        var status1 = status2 = status3 = 0;
        if (scheme_code == '' || typeof(scheme_code) === "undefined" || scheme_code === null) {
          $('#error_scheme_code').text(PleaseSelectScheme);
          status1 = 0;
        } else {
          $('#error_scheme_code').text('');
          status1 = 1;
        }
        if (applicant_id == '' || typeof(applicant_id) === "undefined" || applicant_id === null) {
          $('#error_applicant_id').text(PleaseEnterApplicationId);
          status1 = 0;
        } else {
          $('#error_application_type').text('');
          status2 = 1;
        }

        if (status1 && status2) {
          var url = '{{ url('ajaxApplicationTrack') }}';
          var role_code = $('#role_code').val();
          $('#ajaxData').html('');
          $('#loaderDiv').show();
          $.ajax({
            type: 'GET',
            url: url,
            data: {
              is_public: 0,
              scheme_code: scheme_code,
              applicant_id: applicant_id,
              trackType: scheme_type,
              _token: '{{ csrf_token() }}',
            },
            success: function(data) {
              $('#loaderDiv').hide();
              $("#modal_data").html('');
              $("#ajaxData").html(data);
            },
            error: function(ex) {
              $('#loaderDiv').hide();
              $("#modal_data").html('');
              $('#ajaxData').html('');
              alert('Timeout ..Please try again.');
            }
          });
        }
      } else {
        return false;
      }
    });

    // ------------------ Beneficiary Payment Status Section ------------------

    // Change Financial Year (example function)
    function changeFinancialYear(fin_year, beneficiary_id) {
      $('#loaderDiv').show();
      var finYear = fin_year;
      $.ajax({
        type: "POST",
        url: "{{ route('getPaymentDetailsFinYearWiseInTrackApplication') }}",
        data: {
          _token: '{{ csrf_token() }}',
          ben_id: beneficiary_id,
          fin_year: finYear
        },
        success: function(response) {
          $('#loaderDiv').hide();
          $('#payment_details_' + response.personalDetails.ben_id).html('');
          $('#payment_details_' + response.personalDetails.ben_id).html(response.paymentDetails);
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#loaderDiv').hide();
          $('.ben_view_modal').modal('hide');
          $.alert({
            title: 'Error!!',
            type: 'red',
            icon: 'fa fa-warning',
            content: sessiontimeoutmessage,
          });
        }
      });
    }

    // Expose function if needed elsewhere
    window.changeFinancialYear = changeFinancialYear;

    // ====== Accordion click behaviour (using data-target attribute) ======
   $(function() {

    // Helper: find buttons that control a collapse pane (by id)
    function buttonsForPaneId(paneId) {
        return $('[data-target="#' + paneId + '"], [data-bs-target="#' + paneId + '"], [aria-controls="' + paneId + '"]');
    }

    // Initialize each accordion on page load
    $('#paymentAccordion .accordion-collapse').each(function() {
        const $pane = $(this);
        const paneId = this.id;

        // ensure aria/collapsed state matches visibility / .show class
        const isShown = $pane.hasClass('show') || $pane.is(':visible');
        const $buttons = buttonsForPaneId(paneId);

        if (isShown) {
            $pane.show().addClass('show');
            $buttons.removeClass('collapsed').attr('aria-expanded', 'true');
        } else {
            $pane.hide().removeClass('show');
            $buttons.addClass('collapsed').attr('aria-expanded', 'false');
        }
    });

    // Delegated click handler for accordion toggle
    $('#paymentAccordion').on('click', '.accordion-button', function(e) {
        e.preventDefault();

        const $btn = $(this);

        // Accept data-target, data-bs-target or aria-controls (with/without #)
        let targetSelector = $btn.attr('data-target') || $btn.attr('data-bs-target') || null;
        if (!targetSelector) {
            const aria = $btn.attr('aria-controls');
            if (aria) targetSelector = '#' + aria;
        }

        if (!targetSelector) return; // nothing to do
        // normalize to selector string beginning with '#'
        if (targetSelector.charAt(0) !== '#') targetSelector = '#' + targetSelector;

        const $target = $(targetSelector);
        if ($target.length === 0) return;

        // If visible -> collapse it
        if ($target.is(':visible')) {
            $target.slideUp(200, function() {
                $target.removeClass('show').hide();
            });
            $btn.addClass('collapsed').attr('aria-expanded', 'false');

        } else {
            // Close other panels in the same accordion
            const parentAccordionId = $btn.closest('.accordion').attr('id');
            if (parentAccordionId) {
                // find visible panes and hide them
                $('#' + parentAccordionId + ' .accordion-collapse').not($target).each(function() {
                    const $other = $(this);
                    if ($other.is(':visible')) {
                        $other.slideUp(200, function() {
                            $other.removeClass('show').hide();
                        });
                        buttonsForPaneId(this.id).addClass('collapsed').attr('aria-expanded', 'false');
                    }
                });
            }

            // Open the target pane
            $target.slideDown(200, function() {
                $target.addClass('show');
            });
            $btn.removeClass('collapsed').attr('aria-expanded', 'true');
        }
    });

});

  }); // end document ready
</script>
@endpush
