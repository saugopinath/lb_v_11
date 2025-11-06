@extends('layouts.public')
@push('styles')
  <link rel="stylesheet" href="{{ asset('AdminLTE_3/dist/css/adminlte.min.css') }}">
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
        <div class="card-header card-header-custom">Track Applicant using Beneficiary Id/Mobile No./Aadhaar No.</div>
        <div class="card-body">
            <div id="loaderDiv"></div> <!-- Your loader div -->

            <form method="post" id="publick_track_applicant" action="#" class="submit-once">
                @csrf
                <div class="row g-3 align-items-end">

                    <!-- Scheme Dropdown -->
                    <div class="col-md-3">
                        <label for="scheme_code" class="form-label fw-semibold">Scheme <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="scheme_code" id="scheme_code">
                            <option value="">-- Select --</option>
                            <option value="1">WCD Old Age Pension</option>
                            <option value="2">WCD Widow Pension</option>
                        </select>
                        <div class="text-danger small mt-1" id="error_scheme_code"></div>
                    </div>

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
                    <div class="col-12 d-flex justify-content-center mt-3">
                        <button type="submit" class="btn btn-primary shadow-sm px-4">
                            <i class="fa fa-search me-1"></i> Search
                        </button>
                    </div>


                </div>

            </form>
        </div>
    </div>

    <!-- @if(true) -->
    <div id="ajaxData">
        <div class="card shadow-sm mb-4 card-outline card-success">
            <div class="card-header">List of Beneficiary</div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="example" class="table table-bordered table-striped" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Beneficiary ID</th>
                                <th>Applicant Name</th>
                                <th>Address</th>
                                <th>Current Banking Information</th>
                                <th>Current Status</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Your PHP foreach loop for row_list --}}
                            <tr>
                                <td>9029906</td>
                                <td>MADAN MOHAN KARMAKAR</td>
                                <td>
                                    District - PURBA BARDHAMAN<br>
                                    Block/Municipality - KHANDAGHOSH<br>
                                    Gp/Ward - GOPALBERA
                                </td>
                                <td>
                                    Bank Name - PUNJAB NATIONAL BANK<br>
                                    Branch - EKALAKSHMI<br>
                                    A/c No - ************6363<br>
                                    IFSC - PUNB00X52410
                                </td>
                                <td><span class="badge bg-success">Verified (Approval Pending)</span></td>
                                <td>
                                    <a href="#ben_payment_view_modal" class="btn btn-info btn-sm" data-bs-toggle="modal">
                                        <i class="fa fa-eye me-1"></i> View
                                    </a>

                                </td>
                            </tr>
                            {{-- End of PHP foreach --}}
                            <!-- @if (false) {{-- If no records, show this --}} -->
                            <!-- <tr>
                                <td colspan="6" class="text-center">No Record Found</td>
                            </tr> -->
                            <!-- @endif -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<div class="modal fade" id="ben_payment_view_modal" tabindex="-1" aria-labelledby="paymentStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white"> <!-- Using bg-info for header color -->
                <h5 class="modal-title" id="paymentStatusModalLabel">View Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header card-header-custom"><span id="panel-icon">View Payment Status</span></div>
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-8">
                                <label class="form-label mb-0">Which financial year you want to view payment status ?</label>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="select_type" id="select_type">
                            <option value="">-- Select --</option>
                            <option value="1">2025-2026</option>
                            <option value="2">2024-2025</option>
                            <option value="3">2023-2024</option>
                            <option value="4">2022-2023</option>
                        </select>
                            </div>
                        </div>
                        <hr />
                        <div class="col-md-12">
                            <span>Bank Account Status : Validation Success. Ready For Payment</span>
                        </div>
                        <!-- <div id="loader_data" class="text-center py-3" style="display:none;">
                            <img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px" alt="Loading..." />
                        </div> -->
                        <!-- <div id="payment_details_view" class="table-responsive">
                            <span> </span>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection