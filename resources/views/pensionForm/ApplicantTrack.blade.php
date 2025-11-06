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
      <form method="post" id="register_form"  class="submit-once">
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
                    <div class="form-row align-items-end">
                    <input type="hidden" name="scheme_code" id="scheme_code" value="{{ $scheme_id }}">
                     <div class="form-group col-md-6">
                          <label class="">Search Using</label>
                          <select class="form-control" name="select_type" id="select_type" >
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
                   <div class="form-group col-md-3 mb-0">
                        <button type="button" name="submit" value="Submit" class="btn btn-success table-action-btn" id="searchbtn">
                          <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
              </div>
               
             <div id="ajaxData"></div>
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

<script type="text/javascript">
    $(document).ready(function() {
        $('#loaderDiv').hide();
        $('.sidebar-menu li').removeClass('active');
        $('.sidebar-menu #lk-main').addClass("active");
        $('.sidebar-menu #appplicantTrack').addClass("active");
        var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
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
                $('#search_msg').html('Search using with '+selectedValueUsing+' - '+applicant_id);

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
                //console.log(status1); console.log(status2);
                if (status1 && status2) {
                    var url = '{{ url('ajaxApplicationTrack') }}';
                    var role_code = $('#role_code').val();
                    // $('#ajaxData').html('<img align="center" src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
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
</script>

@endpush