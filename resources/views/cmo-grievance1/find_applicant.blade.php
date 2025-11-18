<style type="text/css">
    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
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

    .loadingDivModal {
        position: absolute;
        top: 0px;
        right: 0px;
        width: 100%;
        height: 100%;
        background-color: #fff;
        background-image: url('images/ajaxgif.gif');
        background-repeat: no-repeat;
        background-position: center;
        z-index: 10000000;
        opacity: 0.4;
        filter: alpha(opacity=40);
        /* For IE8 and earlier */
    }

    #updateDiv {
        border: 1px solid #d9d9d9;
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }
</style>
@extends('layouts.app-template-datatable')
@section('content')
<section class="content">
    <div class="card card-outline card-default">
        <div class="card-body">
            <div id="loadingDiv"></div>

            {{-- ========== Grievance Details ========== --}}
            <div class="card card-outline card-primary mb-4">
                <div class="card-header py-2">
                    <h5 class="card-title fw-bold fst-italic mb-0">
                        <i class="fas fa-info-circle me-1"></i> Grievance Details
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-12">
                            {{-- Success/Error Messages --}}
                            @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>{{ $message }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif
                            @if ($message = Session::get('message'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>{{ $message }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif
                            @if ($message = Session::get('msg1'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>{{ $message }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            @endif

                            {{-- Details Rows --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Grievance ID: </strong>
                                    <span class="fs-6">{{ $grievance_id }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Grievance No: </strong>
                                    <span class="fs-6">{{ $row->grievance_no }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Caller Name: </strong>
                                    <span class="fs-6">{{ $row->applicant_name }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Caller Mobile No: </strong>
                                    <span class="fs-6">{{ $grievance_mobile_no }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Age: </strong>
                                    <span class="fs-6">{{ $row->applicant_age }} years</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong>Address: </strong>
                                <span class="fs-6">{{ $row->applicant_address }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>Description: </strong>
                                <span class="fs-6">{{ $row->grievance_description }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========== ATR Tagging ========== --}}
            <div class="card card-outline card-info mb-4" id="atr_tagging">
                <div class="card-header py-2">
                    <h5 class="card-title fw-bold fst-italic mb-0">
                        <i class="fas fa-tags me-1"></i> ATR Tagging
                    </h5>
                </div>
                <div class="card-body p-3">
                    <form class="row g-3">
                        {{ csrf_field() }}
                        <input type="hidden" name="scheme_id" id="scheme_id" value="20">

                        <div class="col-md-6">
                            <label class="form-label">ATR Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="atr_type" id="atr_type" {{ isset($row->atr_type) ? 'disabled' : '' }}>
                                <option value="">-----ATR Type----</option>
                                @foreach ($atr as $atrs)
                                <option value="{{ $atrs->atn_id }}" {{ (isset($row) && $row->atr_type == $atrs->atn_id) ? 'selected' : '' }}>
                                    {{ $atrs->atr_desc }}
                                </option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="error_atr_type"></span>
                        </div>

                        <div class="col-md-6" id="district_div">
                            <label class="form-label">District <span class="text-danger">*</span></label>
                            <select class="form-select" name="district" id="district">
                                <option value="">-----Select District----</option>
                                @foreach ($districtList as $districtLists)
                                <option value="{{ $districtLists->district_code }}">{{ $districtLists->district_name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="error_district"></span>
                        </div>

                        <div class="col-md-6" id="urban_code_div">
                            <label class="form-label">Rural/Urban <span class="text-danger">*</span></label>
                            <select class="form-select" name="urban_code" id="urban_code">
                                <option value="">-----Select Rural/Urban----</option>
                                @foreach (Config::get('constants.rural_urban') as $key=>$value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="error_urban_code"></span>
                        </div>

                        <div class="col-md-6" id="block_div">
                            <label class="form-label" id="blk_sub_txt">Block/Subdivision <span class="text-danger">*</span></label>
                            <select class="form-select" name="block" id="block">
                                <option value="">-----Select Block/Subdivision----</option>
                            </select>
                            <span class="text-danger" id="error_block"></span>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                oninput="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '')"
                                {{ (isset($row) && $row->atr_type == '002') ? 'disabled' : '' }}>
                            {{ isset($row->atr_type) && $row->atr_type == '002' ? $row->remarks : '' }}
                            </textarea>
                            <span class="text-danger" id="error_remarks"></span>
                            @if (isset($row->atr_type) && $row->atr_type == '002')
                            <input type="hidden" name="remarks" value="{{ $row->remarks }}">
                            @endif
                        </div>

                        <input type="hidden" name="grievance_id" id="grievance_id" value="{{ $grievance_id }}">
                        <input type="hidden" name="grievance_mobile_no" id="grievance_mobile_no" value="{{ $grievance_mobile_no }}">
                        <input type="hidden" name="grievance_mobile_no" id="grievance_mobile_no" value="{{ $grievance_mobile_no }}">
                        <input type="hidden" name="grievance_id" id="grievance_id" value={{$grievance_id}}>
                        <input type="hidden" name="grievance_mobile" id="grievance_mobile" value={{$grievance_mobile_no}}>
                        <div class="text-center mt-3">
                            <button class="btn btn-info" name="map_applicant" id="map_applicant" type="button">
                                <i class="fas fa-map-marker-alt me-1"></i> Map Applicant
                            </button>
                            <button class="btn btn-danger ms-2" name="redress" id="redress" type="button">
                                <i class="fas fa-check-circle me-1"></i> Grievance Redressed
                            </button>
                            <button class="btn btn-primary ms-2" name="send_another_block" id="send_another_block" type="button">
                                <i class="fas fa-share-square me-1"></i> Send to another Block/Subdivision
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ========== Search Section ========== --}}
            <div class="card card-outline card-warning mb-4" id="search_panel">
                <div class="card-header py-2">
                    <h5 class="card-title fw-bold fst-italic mb-0">
                        <i class="fas fa-search me-1"></i> Search using Application Id, Beneficiary name, Applicant mobile no, Aadhaar no, Bank account number
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="text-center mb-3">
                        <label class="fw-semibold d-block mb-2">Please select which one do you want to Search?</label>
                        <div class="d-inline-flex flex-wrap gap-4 justify-content-center">
                            <label class="form-check-label"><input class="form-check-input process_type_radio" type="radio" name="process_type" value="1"> Application ID</label>
                            <label class="form-check-label"><input class="form-check-input process_type_radio" type="radio" name="process_type" value="5"> Beneficiary Name</label>
                            <label class="form-check-label"><input class="form-check-input process_type_radio" type="radio" name="process_type" value="2"> Mobile Number</label>
                            <label class="form-check-label"><input class="form-check-input process_type_radio" type="radio" name="process_type" value="3"> Aadhaar Number</label>
                            <label class="form-check-label"><input class="form-check-input process_type_radio" type="radio" name="process_type" value="4"> Bank Account Number</label>
                        </div>
                    </div>

                    <div class="text-center mb-3" id="search_level">
                        <label id="input_label" class="fw-semibold"></label>
                        <input class="form-control d-inline-block w-auto bg-light border-secondary" value="" name="input_value" id="input_value" style="font-size:16px;">
                        <span id="error_input_value" class="text-danger ms-2"></span>
                        <button class="btn btn-info btn-sm ms-2" id="search_btn" type="button">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button class="btn btn-warning btn-sm ms-4" id="send_to_operator" type="button">
                            <i class="fas fa-user-cog"></i> Send To Operator For New Entry
                        </button>
                    </div>
                </div>
            </div>

            {{-- ========== Results ========== --}}
            <div id="res_div" style="display: none;">
                <div class="card card-outline card-secondary">
                    <div class="card-header py-2">
                        <h5 class="card-title fw-bold fst-italic mb-0">
                            <i class="fas fa-list me-1"></i> List of Beneficiary
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table id="example" class="table table-bordered table-striped align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Applicant Name</th>
                                        <th>Father's Name</th>
                                        <th>Mobile No</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 14px;"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
@push('scripts')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
    $(document).ready(function() {

        // $('#search_level').hide();
        $('#loadingDiv').hide();
        $('#submit_btn').removeAttr('disabled');
        $('#res_div').hide();
        $('#search_panel').hide();
        $('#map_applicant').hide();
        $('#redress').hide();
        $('#send_another_block').hide();
        $('#district_div').hide();
        $('#urban_code_div').hide();
        $('#block_div').hide();
        $('#send_to_operator').show();
        var applicant_mobile_no = '{{$row->pri_cont_no}}';
        // if(applicant_mobile_no){
        //     $('input[name="process_type"][value="2"]').attr('checked', true);
        //     $('#input_value').val(applicant_mobile_no);
        //     $("#new_process_id").val(2);
        //     var label_text = 'Mobile Number :';
        //     $("#input_label").text(label_text);
        //     performSearch();
        // }

        var error_input_value = '';
        $('#search_btn').click(function() {
            performSearch();
        });

        function performSearch() {
            if ($.trim($('#input_value').val()).length == 0) {
                error_input_value = 'Input value is required';
                $('#error_input_value').text(error_input_value);
                $('#input_value').addClass('has-error');
            } else {
                error_input_value = '';
                $('#error_input_value').text(error_input_value);
                $('#input_value').removeClass('has-error');
            }

            if (error_input_value != '') {
                return false;
            } else {
                var grievance_mobile = $('#grievance_mobile').val();
                var grievance_id = $('#grievance_id').val();
                var new_process_id = $('#new_process_id').val();
                var input_value = $('#input_value').val();

                var ajaxData = {
                    'grievance_mobile': grievance_mobile,
                    'grievance_id': grievance_id,
                    'new_process_id': new_process_id,
                    'input_value': input_value,
                    _token: "{{ csrf_token() }}"
                };
                loadBenListData(ajaxData);
            }
        }

        $(document).on('change', '.process_type_radio', function() {
            $('#res_div').hide();
            var process_type = $(this).val();
            $('#input_value').val('');
            var label_text = '';
            if (process_type == 1) {
                $("#new_process_id").val(1);
                label_text = 'Application ID :';
            } else if (process_type == 2) {
                $("#new_process_id").val(2);
                label_text = 'Mobile Number :';
            } else if (process_type == 3) {
                $("#new_process_id").val(3);
                label_text = 'Aadhaar Number :';
            } else if (process_type == 4) {
                $("#new_process_id").val(4);
                label_text = 'Bank Account Number :';
            } else if (process_type == 5) {
                $("#new_process_id").val(5);
                label_text = 'Beneficiary Name :';
            }

            $("#input_label").text(label_text);
        });

        // Map backend type/icon to SweetAlert2 compatible icon
        function mapIcon(type) {
            switch (type) {
                case 'red':
                case 'fa fa-warning':
                    return 'warning';
                case 'green':
                    return 'success';
                case 'blue':
                    return 'info';
                case 'error':
                    return 'error';
                default:
                    return 'info';
            }
        }

        $(document).on('click', '#redress', function() {
            let error_atr_type = '';
            let error_remarks = '';

            // Validation
            if ($.trim($('#atr_type').val()) === '') {
                error_atr_type = 'ATR Type is required';
                $('#error_atr_type').text(error_atr_type);
                $('#atr_type').addClass('has-error');
            } else {
                $('#error_atr_type').text('');
                $('#atr_type').removeClass('has-error');
            }

            if ($.trim($('#remarks').val()) === '') {
                error_remarks = 'Remarks is required';
                $('#error_remarks').text(error_remarks);
                $('#remarks').addClass('has-error');
            } else {
                $('#error_remarks').text('');
                $('#remarks').removeClass('has-error');
            }

            if (error_atr_type || error_remarks) return false;

            // Confirmation
            Swal.fire({
                title: 'Warning!',
                text: 'Are you sure you want to redress the grievance?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, redress it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loadingDiv').show();
                    $.ajax({
                        type: 'post',
                        url: "{{ route('cmo-grievance-redress1') }}",
                        data: {
                            grievance_mobile_no: $('#grievance_mobile').val(),
                            grievance_id: $('#grievance_id').val(),
                            atr_type: $('#atr_type').val(),
                            remarks: $('#remarks').val(),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $('#loadingDiv').hide();

                            // Map icon
                            const swalIcon = mapIcon(response.type);

                            if (response.status == 1) {
                                $('#modalUpdateatr').modal('hide');
                                $('#res_div').hide();
                                $("html, body").animate({
                                    scrollTop: 0
                                }, "slow");

                                Swal.fire({
                                    title: response.title || 'Success',
                                    text: response.msg || 'Redress successful!',
                                    icon: swalIcon,
                                    confirmButtonText: 'Ok',
                                    allowOutsideClick: false
                                }).then(() => {
                                    window.location.href = "cmo-grievance-workflow1";
                                });

                            } else {
                                // Display errors (single or multiple)
                                let html = '';
                                if (Array.isArray(response.msg)) {
                                    html = '<ul style="text-align: left;">';
                                    $.each(response.msg, function(_, value) {
                                        html += '<li>' + value + '</li>';
                                    });
                                    html += '</ul>';
                                } else {
                                    html = response.msg || 'Something went wrong!';
                                }

                                Swal.fire({
                                    title: response.title || 'Warning!',
                                    html: html,
                                    icon: swalIcon,
                                    confirmButtonText: 'Ok'
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#loadingDiv').hide();

                            // Get detailed exception info if available
                            let message = 'Something went wrong during the request.';
                            if (jqXHR.responseJSON && jqXHR.responseJSON.msg) {
                                message = jqXHR.responseJSON.msg;
                            } else if (jqXHR.responseText) {
                                message = jqXHR.responseText;
                            }

                            Swal.fire({
                                title: 'Exception!',
                                html: '<pre style="text-align:left;">' + message + '</pre>',
                                icon: 'error',
                                confirmButtonText: 'Ok'
                            });

                            console.error('AJAX Exception:', textStatus, errorThrown, jqXHR);
                        }
                    });
                }
            });
        });


        $(document).on('click', '#send_another_block', function() {
            // Validation
            if ($.trim($('#atr_type').val()).length == 0) {
                error_atr_type = 'ATR Type is required';
                $('#error_atr_type').text(error_atr_type);
                $('#atr_type').addClass('has-error');
            } else {
                error_atr_type = '';
                $('#error_atr_type').text(error_atr_type);
                $('#atr_type').removeClass('has-error');
            }

            if ($.trim($('#district').val()).length == 0) {
                error_district = 'District is required';
                $('#error_district').text(error_district);
            } else {
                error_district = '';
                $('#error_district').text(error_district);
            }

            if ($.trim($('#urban_code').val()).length == 0) {
                error_urban_code = 'Rural/Urban is required';
                $('#error_urban_code').text(error_urban_code);
            } else {
                error_urban_code = '';
                $('#error_urban_code').text(error_urban_code);
            }

            if ($.trim($('#block').val()).length == 0) {
                error_block = 'Block/Subdiv is required';
                $('#error_block').text(error_block);
            } else {
                error_block = '';
                $('#error_block').text(error_block);
            }

            if ($.trim($('#remarks').val()).length == 0) {
                error_remarks = 'Remarks is required';
                $('#error_remarks').text(error_remarks);
                $('#remarks').addClass('has-error');
            } else {
                error_remarks = '';
                $('#error_remarks').text(error_remarks);
                $('#remarks').removeClass('has-error');
            }

            if (error_atr_type != '' || error_district != '' || error_urban_code != '' || error_block != '' || error_remarks != '') {
                return false;
            } else {
                var block_subdiv_text = $('#block option:selected').text();

                Swal.fire({
                    title: 'Confirm!',
                    text: `Are you sure you want to send the grievance to block/Subdivision: ${block_subdiv_text}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Send!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#loadingDiv').show();
                        $.ajax({
                            type: 'post',
                            url: "{{ route('cmo-grievance-transfar1') }}",
                            data: {
                                grievance_mobile_no: $('#grievance_mobile').val(),
                                grievance_id: $('#grievance_id').val(),
                                atr_type: $('#atr_type').val(),
                                remarks: $('#remarks').val(),
                                district: $('#district').val(),
                                rural_urban: $('#urban_code').val(),
                                block: $('#block').val(),
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                const swalIcon = mapIcon(response.type);
                                $('.loadingDivModal').hide();
                                if (response.status == 1) {
                                    $('#modalUpdateatr').modal('hide');
                                    $('#res_div').hide();
                                    $("html, body").animate({
                                        scrollTop: 0
                                    }, "slow");

                                    Swal.fire({
                                        title: response.title,
                                        text: response.msg,
                                        icon: swalIcon,
                                        confirmButtonText: 'Ok'
                                    }).then(() => {
                                        window.location.href = "cmo-grievance-workflow1";
                                    });

                                } else {
                                    var html = '<ul>';
                                    if (Array.isArray(response.msg)) {
                                        $.each(response.msg, function(key, value) {
                                            html += '<li>' + value + '</li>';
                                        });
                                    } else {
                                        html += '<li>' + response.msg + '</li>';
                                    }
                                    html += '</ul>';

                                    Swal.fire({
                                        title: response.title,
                                        html: html,
                                        icon: swalIcon
                                    });
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                $('#loadingDiv').hide();
                                ajax_error(jqXHR, textStatus, errorThrown);
                            }
                        });
                    }
                });
            }
        });



        $(document).on('click', '.process_applicant', function() {
            var val = $(this).val();
            var atr_type = $('#atr_type').val();
            var atr_type_text = $('#atr_type option:selected').text();
            // console.log(atr_type);
            var remarks = $('#remarks').val();
            var array = val.split("_");
            var application_id = array[0];
            var grivence_mobile_no = array[1];
            var grivence_id = array[2];
            $.confirm({
                title: 'Confirm!',
                type: 'orange',
                icon: 'fa fa-warning',
                content: '<strong>Are you want to process the beneficiary with Application Id: ' + application_id + '? </strong>',
                buttons: {
                    confirm: function() {
                        $('#loadingDiv').show();
                        $.ajax({
                            type: 'post',
                            url: "{{ route('cmo-grievance-process-post1') }}",
                            data: {
                                application_id: application_id,
                                grievance_mobile_no: grivence_mobile_no,
                                grievance_id: grivence_id,
                                atr_type: atr_type,
                                remarks: $('#remarks').val(),
                                atr_type: atr_type,
                                remarks: remarks,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                $('.loadingDivModal').hide();
                                if (response.status == 1) {
                                    $('#modalUpdateatr').modal('hide');
                                    $('#res_div').hide();
                                    $("html, body").animate({
                                        scrollTop: 0
                                    }, "slow");
                                    $.confirm({
                                        title: response.title,
                                        type: response.type,
                                        icon: response.icon,
                                        content: response.msg,
                                        buttons: {
                                            Confirm: {
                                                text: 'Ok',
                                                btnClass: 'btn-green',
                                                keys: ['enter', 'shift'],
                                                action: function() {
                                                    window.location.href = "cmo-grievance-workflow1";
                                                }
                                            },
                                            // cancel: function() {}
                                        }
                                    });


                                } else {
                                    var html = '';
                                    html += '<ul>';
                                    if (Array.isArray(response.msg)) {
                                        $.each(response.msg, function(key, value) {
                                            html += '<li>' + value + '</li>';
                                        });
                                    } else {
                                        html = '<li>' + response.msg + '</li>';
                                    }
                                    html += '<ul>';
                                    $.alert({
                                        title: response.title,
                                        type: response.type,
                                        icon: response.icon,
                                        content: html
                                    });
                                }
                            },
                            complete: function() {},
                            error: function(jqXHR, textStatus, errorThrown) {
                                $('#loadingDiv').hide();
                                ajax_error(jqXHR, textStatus, errorThrown);
                            }
                        });
                    },
                    cancel: function() {}
                }
            });
        });

        $(document).on('click', '#map_applicant', function() {
            if ($.trim($('#atr_type').val()).length == 0) {
                error_atr_type = 'ATR Type is required';
                $('#error_atr_type').text(error_atr_type);
                $('#atr_type').addClass('has-error');
            } else {
                error_atr_type = '';
                $('#error_atr_type').text(error_atr_type);
                $('#atr_type').removeClass('has-error');
            }
            if ($.trim($('#remarks').val()).length == 0) {
                error_remarks = 'Remarks is required';
                $('#error_remarks').text(error_remarks);
            } else {
                error_remarks = '';
                $('#error_remarks').text(error_remarks);
            }
            if (error_atr_type != '' || error_remarks != '') {
                return false;
            } else {

                $('#search_panel').show();
                if (applicant_mobile_no) {
                    $('input[name="process_type"][value="2"]').attr('checked', true);
                    $('#input_value').val(applicant_mobile_no);
                    $("#new_process_id").val(2);
                    var label_text = 'Mobile Number :';
                    $("#input_label").text(label_text);
                    performSearch();
                }
            }
        });

        $(document).on('click', '#send_to_operator', function() {
            Swal.fire({
                title: 'Confirm!',
                text: 'Are you sure you want to send grievance details to the Operator end?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Send!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loadingDiv').show();
                    $.ajax({
                        type: 'post',
                        url: "{{ route('cmo-sent-to-operator1') }}",
                        data: {
                            scheme_id: $('#scheme_id').val(),
                            grievance_mobile_no: $('#grievance_mobile').val(),
                            grievance_id: $('#grievance_id').val(),
                            atr_type: $('#atr_type').val(),
                            remarks: $('#remarks').val(),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            const swalIcon = mapIcon(response.type);
                            $('#loadingDiv').hide();

                            if (response.status == 1) {
                                Swal.fire({
                                    title: response.title,
                                    text: response.msg,
                                    icon: swalIcon,
                                    confirmButtonText: 'Ok',
                                    customClass: {
                                        confirmButton: 'btn btn-success' // optional, similar to btn-green
                                    }
                                }).then(() => {
                                    window.location.href = "cmo-grievance-workflow1?scheme_id=" + $('#scheme_id').val() + "&type=1";
                                });

                                $('#res_div').hide();
                                $('#select_type').val('').trigger('change');
                                $('#scheme_type').val('').trigger('change');
                                $("html, body").animate({
                                    scrollTop: 0
                                }, "slow");
                            } else {
                                Swal.fire({
                                    title: response.title,
                                    html: response.msg,
                                    icon: swalIcon
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#loadingDiv').hide();
                            ajax_error(jqXHR, textStatus, errorThrown);
                        }
                    });
                }
            });
        });

        if ($('#atr_type').val() == '002') {
            $('#map_applicant').show();
        } else {
            $('#map_applicant').hide();
        }
        $('#atr_type').change(function() {
            $('#search_panel').hide();
            $('#res_div').hide();
            $('#remarks').val('');
            $('#district').val('');
            $('#urban_code').val('');
            $('#block').val('');
            if ($(this).val() == '1' || $(this).val() == '3' || $(this).val() == '5' || $(this).val() == '7' || $(this).val() == '8' || $(this).val() == '11') { // Assuming the value of the third option is '003'
                $('#redress').show();
                $('#map_applicant').hide();
                $('#send_another_block').hide();
                $('#district_div').hide();
                $('#urban_code_div').hide();
                $('#block_div').hide();
            } else if ($(this).val() == '2') {
                $('#send_another_block').show();
                $('#redress').hide();
                $('#map_applicant').hide();
                $('#district_div').show();
                $('#urban_code_div').show();
                $('#block_div').show();
            } else {
                $('#redress').hide();
                $('#map_applicant').show();
                $('#send_another_block').hide();
                $('#district_div').hide();
                $('#urban_code_div').hide();
                $('#block_div').hide();
            }
        });

        function loadBenListData(ajaxData) {
            $('#loadingDiv').show();
            $('#res_div').show();
            // var msg = 'Scheme : ' + $("#scheme_type option:selected").text();
            // $('#panel_head').text(msg);

            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            $('#example tbody').empty();

            var table = $('#example').DataTable({
                dom: 'Blfrtip',
                "scrollX": true,
                "paging": true,
                "searchable": true,
                "ordering": false,
                "bFilter": true,
                "bInfo": true,
                "pageLength": 10,
                'lengthMenu': [
                    [10, 20, 25, 50, 100, -1],
                    [10, 20, 25, 50, 100, 'All']
                ],
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,
                "oLanguage": {
                    "sProcessing": '<div class="preloader1" align="center"><font style="font-size: 20px; font-weight: bold; color: green;">Processing...</font></div>'
                },
                "ajax": {
                    url: "{{ url('cmo-grievance-benLising1') }}",
                    type: "post",
                    data: ajaxData,
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#loadingDiv').hide();
                        $('.preloader1').hide();
                        ajax_error(jqXHR, textStatus, errorThrown);
                    }
                },
                "initComplete": function() {
                    $('#loadingDiv').hide();
                },
                "columns": [{
                        "data": "application_id"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "father_name"
                    },
                    {
                        "data": "mobile_no"
                    },
                    {
                        "data": "address"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "view"
                    }
                ],
                "buttons": [{
                        extend: 'pdf',
                        footer: true,
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7],
                            stripHtml: false,
                        }
                    },
                ],
            });
        }

        $('#district').change(function() {
            var district = $(this).val();
            //alert(district);
            $('#urban_code').val('');
            $('#block').html('<option value="">--All --</option>');
            $('#muncid').html('<option value="">--All --</option>');
        });

        $('#urban_code').change(function() {
            var urban_code = $(this).val();
            if (urban_code == '') {
                $('#muncid').html('<option value="">--All --</option>');
            }
            $('#muncid').html('<option value="">--All --</option>');
            $('#block').html('<option value="">--All --</option>');
            $('#gp_ward').html('<option value="">--All --</option>');
            select_district_code = $('#district').val();
            if (select_district_code == '') {
                alert('Please Select District First');
                $("#district").focus();
                $("#urban_code").val('');
            } else {
                select_body_type = urban_code;
                var htmlOption = '<option value="">--All--</option>';
                $("#gp_ward_div").show();
                if (select_body_type == 2) {
                    $("#blk_sub_txt").text('Block');
                    $("#gp_ward_txt").text('GP');
                    $("#municipality_div").hide();
                    $.each(blocks, function(key, value) {
                        if (value.district_code == select_district_code) {
                            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                        }
                    });
                } else if (select_body_type == 1) {
                    $("#blk_sub_txt").text('Subdivision');
                    $("#gp_ward_txt").text('Ward');
                    $("#municipality_div").show();
                    $.each(subDistricts, function(key, value) {
                        if (value.district_code == select_district_code) {
                            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                        }
                    });
                } else {
                    $("#blk_sub_txt").text('Block/Subdivision');
                }
                $('#block').html(htmlOption);
            }

        });
        $('#block').change(function() {
            var block = $(this).val();
            var district = $("#district").val();
            var urban_code = $("#urban_code").val();
            if (district == '') {
                $('#urban_code').val('');
                $('#block').html('<option value="">--All --</option>');
                $('#muncid').html('<option value="">--All --</option>');
                alert('Please Select District First');
                $("#district").focus();

            }
            if (urban_code == '') {
                alert('Please Select Rural/Urban First');
                $('#block').html('<option value="">--All --</option>');
                $('#muncid').html('<option value="">--All --</option>');
                $("#urban_code").focus();
            }
            if (block != '') {
                var rural_urbanid = $('#urban_code').val();
                if (rural_urbanid == 1) {
                    var sub_district_code = $(this).val();
                    if (sub_district_code != '') {
                        $('#muncid').html('<option value="">--All --</option>');
                        select_district_code = $('#district').val();
                        var htmlOption = '<option value="">--All--</option>';
                        $.each(ulbs, function(key, value) {
                            if ((value.district_code == select_district_code) && (value.sub_district_code == sub_district_code)) {
                                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                            }
                        });
                        $('#muncid').html(htmlOption);
                    } else {
                        $('#muncid').html('<option value="">--All --</option>');
                    }
                } else if (rural_urbanid == 2) {
                    $('#muncid').html('<option value="">--All --</option>');
                    $("#municipality_div").hide();
                    var block_code = $(this).val();
                    select_district_code = $('#district').val();

                    var htmlOption = '<option value="">--All--</option>';
                    $.each(gps, function(key, value) {
                        if ((value.district_code == select_district_code) && (value.block_code == block_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                        }
                    });
                    $('#gp_ward').html(htmlOption);
                    $("#gp_ward_div").show();


                } else {
                    $('#muncid').html('<option value="">--All --</option>');
                    $("#municipality_div").hide();
                }
            } else {
                $('#muncid').html('<option value="">--All --</option>');
                $('#gp_ward').html('<option value="">--All --</option>');
            }

        });
        $('#muncid').change(function() {
            var muncid = $(this).val();
            var district = $("#district").val();
            var urban_code = $("#urban_code").val();
            if (district == '') {
                $('#urban_code').val('');
                $('#block').html('<option value="">--All --</option>');
                $('#muncid').html('<option value="">--All --</option>');
                alert('Please Select District First');
                $("#district").focus();

            }
            if (urban_code == '') {
                alert('Please Select Rural/Urban First');
                $('#block').html('<option value="">--All --</option>');
                $('#muncid').html('<option value="">--All --</option>');
                $("#urban_code").focus();
            }
            if (muncid != '') {
                var rural_urbanid = $('#urban_code').val();
                if (rural_urbanid == 1) {
                    var municipality_code = $(this).val();
                    if (municipality_code != '') {
                        $('#gp_ward').html('<option value="">--All --</option>');
                        var htmlOption = '<option value="">--All--</option>';
                        $.each(ulb_wards, function(key, value) {
                            if (value.urban_body_code == municipality_code) {
                                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
                            }
                        });
                        $('#gp_ward').html(htmlOption);
                    } else {
                        $('#gp_ward').html('<option value="">--All --</option>');
                    }
                } else {
                    $('#gp_ward').html('<option value="">--All --</option>');
                    $("#gp_ward_div").hide();
                }
            } else {
                $('#gp_ward').html('<option value="">--All --</option>');
            }

        });
    });
</script>
@endpush