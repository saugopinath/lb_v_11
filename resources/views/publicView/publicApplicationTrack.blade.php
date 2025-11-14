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
        /* padding: 16px 8px; */
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
                 <input type="hidden" name="scheme_code" id="scheme_code" value="{{ $scheme_id }}">
                <input type="hidden" name="ben_id_hidden" id="ben_id_hidden" value="20">

                 
                <div class="row g-3 align-items-end">
                    <!-- Search Type Dropdown -->
                    <div class="col-md-3">
                        <label for="select_type" class="form-label fw-semibold">Search Using <span class="text-danger">*</span></label>
                        <select class="form-select" name="select_type" id="select_type">
                            <option value="">-- Select --</option>
                            <option value="1">Application ID</option>
                            <option value="3">Mobile Number</option>
                            <option value="4">Aadhaar Number</option>
                            <option value="5">Bank Account Number</option>
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
                                <span id="captcha-container">{!! captcha_img('flat') !!}</span>
                                <a href="javascript:void(0)" onclick="refreshCaptcha()">
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
                        <button type="button" class="btn btn-primary shadow-sm px-4" id="searchbtn">
                            <i class="fa fa-search me-1"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="alert alert-danger" role="alert" id="error_msg_div" style="display:none;"></div>
    <!-- Application Status Section -->
    <div class="card shadow-sm mb-4 card-outline card-primary" id="ajaxData" style="display:none;">
        <div class="card-header bg-light border-0">
            <h5 class="card-title mb-0 text-primary fw-bold">Application Status(Name - <span id="span_ben_name"></span> , Beneficiary Id- <span class="span_ben_id"></span> , Application Id -<span id="span_app_id"></span> )</h5>
        </div>
        <div class="card-body p-0">
            <div class="timeline-wrap">
                <div class="timeline-scroller">
                    <div class="timeline" id="timeline">
                        <!-- cards -->
                      
                       
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>


     <h4 class="text-center fw-bold text-success mb-3 paymentStatusDiv" style="display:none;">Payment Status (Beneficiary Id- <span class="span_ben_id"></span>)</h4>
     <div class="alert alert-danger" role="alert" id="payment_error_msg_div" style="display:none;"></div>

    <div class="accordion paymentStatusDiv" id="paymentAccordion" style="display:none;">
            <div class="accordion-item">
              <h2 class="accordion-header">
                
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
                       
                    </div>
                    <div class="col-md-6">
                      <select class="form-select w-auto d-inline-block"  onchange="changeFinancialYear(this.value)" id="fin_year">
                            <?php
                                                            foreach (Config::get('constants.fin_year') as $key => $fin_year) {
                                                                //echo $fin_year;
                                                                if ($key == $currentFinYear) {
                                                                    $selected = 'selected';
                                                                } else {
                                                                    $selected = '';
                                                                }
                                                                echo '<option value="' . $key . '" ' . $selected . '>' . $fin_year . '</option>';
                                                            }

                                                            ?>
                      </select>
                    </div>
                  </div>

                  <p class="fw-semibold text-success mb-1">
                    Bank Account Status :<span id="span_ben_status_msg"></span>
                  </p>
                  <p class="fw-semibold text-success mb-1">
                    Beneficiary Status : <span id="span_bank_acc_validation_msg"></span>
                  </p>
                  <p class="mb-3">
                    Bank A/C No : <span id="span_bank_account_no"></span>, IFSC : <span id="span_bank_ifsc"></span>
                  </p>

                  <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="div_payment_list">
                      <thead class="table-light">
                        <tr>
                          <th>Month</th>
                          <th>Payment Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
 <script type="text/javascript">
        $(document).ready(function() {
            $('#loaderDiv').hide();

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
                   // $("#ajaxData").html('');
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
                    //alert(status1);  alert(status2);
                    if (status1 && status2) {
                        var url = '{{ url('ajaxApplicationTrack') }}';
                        //$('#ajaxData').html('');
                        $('#loaderDiv').show();
                        $("#span_ben_name").text('');
                        $(".span_ben_id").text('');
                        $("#span_app_id").text('');
                        $("#ben_id_hidden").val('');
                        $.ajax({
                            type: 'get',
                            dataType: 'json',
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
                               
                                 if (data.return_status) {
                                     $('#error_msg_div').hide();
                                        $("#ajaxData").show();
                                        $("#span_ben_name").text(data.ben_name);
                                        $(".span_ben_id").text(data.beneficiary_id);
                                        $("#span_app_id").text(data.f_application_id);
                                        $("#ben_id_hidden").val(data.beneficiary_id);

                                         $('#timeline').empty();
                                        if(data.accept_reject_info.length>0){
                                             
                                            $accept_reject_info_data = '';
                                            $.each(data.accept_reject_info, function(i, item) {
                                            console.log(i);
                                             $("#timeline").append('<div class="tl-card"><div class="tl-date">'+item.created_at+'</div><div class="tl-text">'+item.action_description+' by '+item.location_description+' '+item.mapping_level+' ('+item.role_description+': '+item.mobile_no+').</div><div class="tick"><svg width="40" height="40" viewBox="0 0 48 48"> <circle cx="24" cy="24" r="20" fill="#ffffff" stroke="#3aa0d2" stroke-width="4" /><path d="M14 25.5l6 6 14-14" fill="none" stroke="#3aa0d2" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg></div></div>');
                                            });
                                        }
                                        $('#loaderDiv').hide();
                                        $("#modal_data").html('');
                                        //$("#ajaxData").html(data);
                                        $("#applicant_id").val('');
                                        $("#captcha").val('');
                                        refreshCaptcha();
                                        // $(".paymentStatusDiv").show();
                                         var fin_year = $("#fin_year").val();
                                        changeFinancialYear(fin_year);
                                 }else {
                                    $('#error_msg_div').show();
                                    $('#error_msg_div').html(data.return_msg);
                                 }
                                // console.log(data);
                               
                            },
                            error: function(ex) {
                                $('#loaderDiv').hide();
                                $("#modal_data").html('');
                                //$('#ajaxData').html('');
                                ////alert('Timeout ..Please try again.');
                               // location.reload();
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
        function changeFinancialYear(fin_year) {
             var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
             //$(".paymentStatusDiv").hide();
              $("#span_ben_status_msg").text('');
              $("#span_bank_acc_validation_msg").text('');
              $("#span_bank_account_no").text('');
              $("#span_bank_ifsc").text('');
              var base_url = '{{ url('/') }}';
              $('#loaderDiv').show();
              var finYear = fin_year;
              var ben_id = $('#ben_id_hidden').val();
             
            $.ajax({
                type: "GET",
                url: "{{ route('getPaymentDetailsFinYearWiseInTrackApplication') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    ben_id: ben_id,
                    fin_year: finYear
                },
                success: function(data) {
                  
                    $('#loaderDiv').hide();
                   if (data.return_status) {
                      $(".paymentStatusDiv").show();
                      $("#span_ben_status_msg").text(data.ben_status_msg);
                      $("#span_bank_acc_validation_msg").text(data.bank_acc_validation_msg);
                      $("#span_bank_account_no").text(data.bank_account_no);
                      $("#span_bank_ifsc").text(data.bank_ifsc);
                      if(data.payment_data.length>0){
                                             $('#div_payment_list tbody').empty();
                                            $payment_data_data = '';
                                            $.each(data.payment_data, function(i, item) {
                                            //console.log(i);
                                             $("#div_payment_list tbody").append('<tr><td>'+item.Month+'</td><td>'+item.PaymentStatus+'</td></tr>');
                                            });
                     }
                   }
                   else{
                       $('#payment_error_msg_div').show();
                       $('#payment_error_msg_div').html(data.return_msg);
                   }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#loaderDiv').hide();
                    $('.ben_view_modal').modal('hide');
                    // ajax_error(jqXHR, textStatus, errorThrown);
                  
                }
            });

        }

        function refreshCaptcha() {

            $.ajax({
                url: '{{ url('refresh-captcha') }}',
                type: 'get',
                dataType: 'html',
                success: function(json) {
                    $('#captcha-container').html(json);
                },
                error: function(data) {
                    alert('Try Again.');
                }
            });
        }
   
        function printDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
   


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


</script>
@endpush

@endsection