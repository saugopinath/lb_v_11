@extends('layouts.public')
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
            min-width: 220px;
            max-width: 240px;
        }
    }
</style>
@endpush
@section('content')
<div class="p-4 mt-4">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 p-2  px-4 rounded shadow-sm"
        style="background: linear-gradient(90deg, #007bff 0%, #00c6ff 100%); color: white;">
        <div>
            <h1 class="h4 mb-0 fw-bold">Track Applicant & View Payment Status</h1>
        </div>

        <a href="{{ url('/login') }}"
            class="btn btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm"
            style="width: 36px; height: 36px;"
            title="Back to Home">
            <i class="fas fa-arrow-left text-primary fa-lg"></i>
        </a>
    </div>


    <!-- Main Card for Search -->
    <div class="card shadow-sm mb-4 card-outline card-primary">
        <div class="card-header card-header-custom bg-light">Track Applicant using Beneficiary Id/Mobile No./Aadhaar No.</div>
        <div class="card-body">
            <div id="loaderDiv"></div> <!-- Your loader div -->

            <form method="post" id="publick_track_applicant" action="#" class="submit-once">
                @csrf
                <div class="row g-3 align-items-end">
                    <!-- Search Type Dropdown -->
                    <div class="col-md-3">
                        <label for="select_type" class="form-label fw-semibold">Search Using <span class="text-danger">*</span></label>
                        <select class="form-select" name="select_type" id="select_type">
                            <option value="">-- Select --</option>
                            <option value="1">Beneficiary ID</option>
                            <option value="2">Mobile Number</option>
                            <option value="3">Aadhaar Number</option>
                        </select>
                        <div class="text-danger small mt-1" id="error_select_type"></div>
                    </div>

                    <!-- Search Input -->
                    <div class="col-md-3">
                        <label for="applicant_id" class="form-label fw-semibold">Enter Value <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="applicant_id"
                            id="applicant_id"
                            class="form-control"
                            placeholder="Enter Beneficiary ID"
                            autocomplete="off" />
                        <div class="text-danger small mt-1" id="error_applicant_id"></div>
                    </div>

                    <!-- Captcha Section -->
                    <div class="col-md-3">
                        <label for="captcha" class="form-label fw-semibold">Captcha <span class="text-danger">*</span></label>
                        <div class="input-group gap-2">
                            <div class="captcha d-flex justify-content-center align-items-center gap-2">
                                <span>{!! captcha_img('flat') !!}</span>
                                <a href="#">
                                    <img src="{{ asset('images/refresh1.png') }}" alt="Refresh Captcha" width="22" height="22">
                                </a>
                            </div>

                            <input
                                type="text"
                                id="captcha"
                                name="captcha"
                                class="form-control rounded"
                                placeholder="Enter Captcha"
                                style="font-size: 15px;">
                        </div>
                        <div id="error_captcha" class="text-danger small mt-1"></div>
                    </div>

                    <!-- Search Button -->
                    <div class="col-md-3 d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary shadow-sm px-4">
                            <i class="fa fa-search me-1"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Application Status Section -->
    <div class="card shadow-sm mb-4 card-outline card-primary">
        <div class="card-header bg-light border-0">
            <h5 class="card-title mb-0 text-primary fw-bold">Application Status</h5>
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
    <hr>
    <!-- Payment Status Section -->
     <h4 class="text-center fw-bold text-success mb-3">Payment Status</h4>

    <div class="accordion" id="paymentAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button
                  class="accordion-button fw-bold"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapseOne"
                  aria-expanded="true"
                  aria-controls="collapseOne"
                >
                  Name – APARNA KARMAKAR, Beneficiary Id – 208789445,
                  Application Id – 124458094
                </button>
              </h2>
              <div
                id="collapseOne"
                class="accordion-collapse collapse show"
                data-bs-parent="#paymentAccordion"
              >
                <div class="accordion-body">
                  <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                      <label
                        >Which financial year you want to view payment
                        status?</label
                      >
                    </div>
                    <div class="col-md-6">
                      <select class="form-select w-auto d-inline-block">
                        <option>2025-2026</option>
                        <option>2024-2025</option>
                      </select>
                    </div>
                  </div>

                  <p class="fw-semibold text-success mb-1">
                    Bank Account Status : Validation Success. Ready For Payment
                  </p>
                  <p class="fw-semibold text-success mb-1">
                    Beneficiary Status : Active beneficiary
                  </p>
                  <p class="mb-3">
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
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.open-payment-modal-btn').on('click', function() {
            $('#ben_payment_view_modal').modal('show');
        });
    });
</script>
@endpush
@endsection