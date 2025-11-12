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
        <div class="card-header card-header-custom bg-light">Track Applicant using Beneficiary Id/Mobile No./Aadhaar No.</div>
        <div class="card-body">
            <div id="loaderDiv"></div> <!-- Your loader div -->

            <form method="get" id="publick_track_applicant" action="#" class="submit-once">
                @csrf
                                        <input type="hidden" name="scheme_code" id="scheme_code" value="{{ $scheme_id }}">

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
                                <span class="refereshrecapcha">{!! captcha_img('flat') !!}</span>
                               <a href="javascript:void(0)" onclick="refreshCaptcha()">
                                    <img  src="{{ asset('images/refresh1.png') }}" alt="Refresh Captcha" width="22" height="22">
                                </a>
                                    
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
                        <button type="buttton" class="btn btn-primary shadow-sm px-4" id="searchbtn">
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
            <div class="card-header bg-light">List of Beneficiary</div>
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
                                    <button type="button" {{-- Essential: type="button" to prevent form submission --}}
                                        class="btn btn-info btn-sm open-payment-modal-btn">
                                        <i class="fa fa-eye me-1"></i> View
                                    </button>

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
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="paymentStatusModalLabel">View Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header card-header-custom  card-outline card-primary">View Payment Status</div>
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-8">
                                <label class="form-label mb-0">Which financial year you want to view payment status ?</label>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select w-auto" name="select_financial_year" id="select_financial_year">
                                    <option value="2025-2026" selected>2025-2026</option>
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2023-2024">2023-2024</option>
                                    <option value="2022-2023">2022-2023</option>
                                </select>
                            </div>
                        </div>
                        <hr />
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped card-outline card-success">
                                <thead class="table-light">
                                    <tr>
                                        <th>Beneficiary Id</th>
                                        <th>Financial Year</th>
                                        <th>Month</th>
                                        <th>IFSC</th>
                                        <th>Account No</th>
                                        <th>Payment Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>6139186</td>
                                        <td>2025-2026</td>
                                        <td>April</td>
                                        <td>UCBA0RRBPBG</td>
                                        <td>***********4094</td>
                                        <td>Payment Success</td>
                                    </tr>
                                    <tr>
                                        <td>6139186</td>
                                        <td>2025-2026</td>
                                        <td>May</td>
                                        <td>UCBA0RRBPBG</td>
                                        <td>***********4094</td>
                                        <td>Payment Success</td>
                                    </tr>
                                </tbody>
                                <!-- <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-exclamation-circle text-info me-2"></i> no data found for this year
                                    </td>
                                </tr> -->
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
 <script type="text/javascript">
        $(document).ready(function() {
            $('#loaderDiv').hide();
            $('.open-payment-modal-btn').on('click', function() {
                    $('#ben_payment_view_modal').modal('show');
                });
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

            var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
            var base_url = '{{ url('/') }}';
            var PleaseSelectScheme = '@lang('lang.PleaseSelectScheme')';
            var PleaseEnterApplicationId = '@lang('lang.PleaseEnterApplicationId')';

            var error_select_type = '';
            var error_applicant_id = '';
            var error_captcha = '';
            $("#searchbtn").click(function() {
                if ($.trim($('#select_type').val()).length == 0) {
                    error_select_type = 'This field is required';
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

                if ($.trim($('#captcha').val()).length == 0) {
                    error_captcha = 'Captcha is required';
                    $('#error_captcha').text(error_captcha);
                } else {
                    error_captcha = '';
                    $('#error_captcha').text(error_captcha);
                }

                if (error_select_type == '' && error_applicant_id == '' && error_captcha == '') {
                    $("#ajaxData").html('');
                    $('#resultDivPaymentStatus').hide();
                    var scheme_code = $("#scheme_code").val();
                    var applicant_id = $("#applicant_id").val();
                    var captcha = $("#captcha").val();
                    var scheme_type = $('#select_type').val();
                    //console.log(application_type); 
                    var status1 = status2 = status3 = 0;
                    if (scheme_code == '' || typeof(scheme_code) === "undefined" || scheme_code === null) {
                        $('#error_scheme_code').text(PleaseSelectScheme);
                        status1 = 0;
                    } else {
                        $('#error_scheme_code').text('');
                        status1 = 1;
                    }
                    if (applicant_id == '' || typeof(applicant_id) === "undefined" || applicant_id ===
                        null) {
                        $('#error_applicant_id').text(PleaseEnterApplicationId);
                        status1 = 0;
                    } else {
                        $('#error_application_type').text('');
                        status2 = 1;
                    }
                  //  alert(status1);  alert(status2);
                    if (status1 && status2) {
                        // alert(status1);  alert(status2);
                        var url = '{{ url('ajaxApplicationTrack') }}';
                         //alert(status1);  alert(status2);
                        var role_code = $('#role_code').val();
                         //alert(status1);  alert(status2);
                        // $('#ajaxData').html('<img align="center" src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
                        $('#ajaxData').html('');
                        $('#loaderDiv').show();
                        $.ajax({
                            type: 'get',
                            url: url,
                            data: {
                                is_public: 1,
                                captcha: captcha,
                                scheme_code: scheme_code,
                                applicant_id: applicant_id,
                                trackType: scheme_type,
                                _token: '{{ csrf_token() }}',
                            },
                            success: function(data) {
                                 console.log(data);
                                $('#loaderDiv').hide();
                                $("#modal_data").html('');
                                $("#ajaxData").html(data);
                                $("#applicant_id").val('');
                                $("#captcha").val('');
                                //refreshCaptcha();
                            },
                            error: function(ex) {
                                $('#loaderDiv').hide();
                                $("#modal_data").html('');
                                $('#ajaxData').html('');
                               // alert('Timeout ..Please try again.');
                                //location.reload();
                            }
                        });
                    }
                } else {
                    return false;
                }
            });

        });

        //------------------Beneficiary Payment Status Section------------------


        //########Change Financial Year########//
        function changeFinancialYear(fin_year, beneficiary_id) {
            $('#loaderDiv').show();
            var finYear = fin_year;
            // var ben_id = $('#ben_id_hidden').val();
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
                    // ajax_error(jqXHR, textStatus, errorThrown);
                    $.alert({
                        title: 'Error!!',
                        type: 'red',
                        icon: 'fa fa-warning',
                        content: sessiontimeoutmessage,
                    });
                }
            });

        }

        function refreshCaptcha() {

            $.ajax({
                url: '{{ url('refresh-captcha') }}',
                type: 'get',
                dataType: 'html',
                success: function(json) {
                    $('.refereshrecapcha').html(json);
                },
                error: function(data) {
                    alert('Try Again.');
                }
            });
        }
    </script>
    <script>
        function printDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>

@endpush
@endsection