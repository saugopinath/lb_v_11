@extends('JBformEntry.base')

@section('main-content')
    <section class="content">
        <div class="container-fluid">

            {{-- HEADER --}}
            <div class="row mb-2 mt-3">
                <div class="col-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <b>Government of West Bengal Jai Bangla Pension Scheme ({{ $scheme_name }})</b>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ALERTS --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><strong>{{ $error }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (Session::get('dup_btn_visible'))
                <div class="text-right mb-3">
                    <form method="get" id="ds_phase_marking" action="{{ url('markdslist') }}">
                        <input type="hidden" name="scheme_id" value="{{ $scheme_id }}">
                        <input type="hidden" name="type" value="3">
                        <input type="hidden" name="ds_mark_phase" value="{{ $cur_ds_phase_arr->phase_code }}">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-map-marker-alt"></i> Mark as {{ $cur_ds_phase_arr->phase_des }} Camp
                        </button>
                    </form>
                </div>
            @endif

            @if (Session::get('cmo_dup_btn_visible'))
                <div class="text-right mb-3">
                    <form method="get" id="cmo_marking" action="{{ url('markcmolist') }}">
                        <input type="hidden" name="scheme_id" value="{{ $scheme_id }}">
                        <input type="hidden" name="type" value="3">
                        <input type="hidden" name="grievance_id" value="{{ $grievance_id }}">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-user-md"></i> Mark as CMO Entry
                        </button>
                    </form>
                </div>
            @endif

            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- FORM --}}
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <form method="post" id="register_form" action="{{ url('JBEntryForm') }}" enctype="multipart/form-data"
                        class="submit-once">
                        @csrf

                        <input type="hidden" name="scheme_id" value="{{ $scheme_id }}">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="app_id"
                            value="{{ $type == $op_type && isset($row->id) ? $row->id : '' }}">
                        <input type="hidden" name="op_type" value="{{ $op_type }}">
                        <input type="hidden" name="grievance_id" value="{{ $grievance_id }}">

                        {{-- STATUS DISPLAY --}}
                        @if($type == 3)
                            <div class="text-center mb-3">
                                <h4 class="text-success">Application Id: {{ $row->getBenidAttribute() }}</h4>
                                <h5 class="text-danger">Application Status: {{ $next_level_status }}</h5>
                                <h6 class="text-danger">Issue: {{ $issue_text }}</h6>
                            </div>
                        @endif

                        {{-- NAV TABS --}}
                        <ul class="nav nav-tabs" id="formTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab">
                                    <b>Personal Details</b>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="id-tab" data-toggle="tab" href="#id_details" role="tab">
                                    <b>Identification Numbers</b>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact_details" role="tab">
                                    <b>Contact Details</b>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="bank-tab" data-toggle="tab" href="#bank_details" role="tab">
                                    <b>Bank Account Details</b>
                                </a>
                            </li>

                            @if ($scheme_id == 13)
                                <li class="nav-item">
                                    <a class="nav-link" id="land-tab" data-toggle="tab" href="#land_details" role="tab">
                                        <b>Land Details</b>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="family-tab" data-toggle="tab" href="#family_details" role="tab">
                                        <b>Family Members</b>
                                    </a>
                                </li>
                            @endif

                            @if ($scheme_id == 17)
                                <li class="nav-item">
                                    <a class="nav-link" id="land_p-tab" data-toggle="tab" href="#land_details_p" role="tab">
                                        <b>Land Details (Dwelling House)</b>
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link" id="enclosure-tab" data-toggle="tab" href="#enclosure_details"
                                    role="tab">
                                    <b>Enclosure List</b>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="declaration-tab" data-toggle="tab" href="#declaration_details"
                                    role="tab">
                                    <b>Self Declaration</b>
                                </a>
                            </li>
                        </ul>

                        {{-- TAB CONTENTS --}}
                        <div class="tab-content p-3 border border-top-0" id="formTabContent">
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                @include('JBformEntry.personal')
                            </div>
                            <div class="tab-pane fade" id="id_details" role="tabpanel">
                                @include('JBformEntry.personal_id')
                            </div>
                            <div class="tab-pane fade" id="contact_details" role="tabpanel">
                                @include('JBformEntry.contact')
                            </div>
                            <div class="tab-pane fade" id="bank_details" role="tabpanel">
                                @include('JBformEntry.bank')
                            </div>

                            @if ($scheme_id == 13)
                                <div class="tab-pane fade" id="land_details" role="tabpanel">
                                    @include('JBformEntry.land_oap')
                                </div>
                                <div class="tab-pane fade" id="family_details" role="tabpanel">
                                    @include('JBformEntry.family')
                                </div>
                            @endif

                            @if ($scheme_id == 17)
                                <div class="tab-pane fade" id="land_details_p" role="tabpanel">
                                    @include('JBformEntry.land_p')
                                </div>
                            @endif

                            <div class="tab-pane fade" id="enclosure_details" role="tabpanel">
                                @include('JBformEntry.enclosure')
                            </div>
                            <div class="tab-pane fade" id="declaration_details" role="tabpanel">
                                @include('JBformEntry.self_decleration')
                            </div>
                        </div>

                        {{-- FINAL SUBMIT --}}
                        <div class="mt-4">
                            @include('JBformEntry.final_submit')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- jQuery 3.6.0    -->
    <!-- <Script src="{{ asset('AdminLTE_3/plugins/jquery/jquery.min.js') }}"></Script> -->
@endsection


<!-- @section('scripts') -->
    <script>
        $(document).ready(function () {
            const scheme_id = $('#scheme_id').val();
            let error_identificacion = 0,
                error_contact = 0,
                error_bank = 0,
                error_land = 0,
                error_family = 0,
                error_land_p = 0,
                error_enclosure = 0,
                error_sef_dec = 0,
                add_land = 0;

            const specialKeys = [8]; // Backspace

            // ✅ Numeric check
            function IsNumeric(e) {
                const keyCode = e.which || e.keyCode;
                const ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.includes(keyCode));
                $('#error').toggle(!ret);
                return ret;
            }

            // ✅ File preview
            function readURL(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        $('#passport_image_view, #passport_image_view_modal').attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // ✅ Dynamic image preview (kept your variable interpolation same)
            $(document).on('change', "#doc_{{$profile_img}}", function () {
                readURL(this);
            });

            // ✅ Handle old values after validation failure
            @if (old('district'))
                const old_districtValue = "{{ old('district') }}";
            @endif
                @if (old('asmb_cons'))
                    const old_assemblyValue = "{{ old('asmb_cons') }}";
                @endif
                @if (old('urban_code'))
                    const old_urbanValue = "{{ old('urban_code') }}";
                @endif
                @if (old('block'))
                    const old_blockValue = "{{ old('block') }}";
                @endif
                @if (old('gp_ward'))
                    const old_gpValue = "{{ old('gp_ward') }}";
                @endif

            @if (old('district'))
                $("#district").val(old_districtValue).trigger('change');
                $("#asmb_cons").val(old_assemblyValue);
                $("#urban_code").val(old_urbanValue).trigger('change');
                $("#block").val(old_blockValue).trigger('change');
                $("#gp_ward").val(old_gpValue);
            @endif

            // ✅ Text only fields
            $(document).on('keypress', '.txtOnly', function (e) {
                const regex = /^[a-zA-Z\s]+$/;
                const str = String.fromCharCode(e.which || e.keyCode);
                if (!regex.test(str)) e.preventDefault();
            });

            // ✅ Numeric only fields
            $(document).on('input', '.NumOnly', function (e) {
                this.value = this.value.replace(/[^\d]+/g, '');
            });

            // ✅ Remove special characters
            $(document).on('input', '.special-char', function () {
                this.value = this.value.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
            });

            // ✅ Decimal-safe number fields
            $(document).on('input', '.price-field', function () {
                let val = $(this).val();
                val = val.replace(/[^0-9.]/g, '');
                if ((val.match(/\./g) || []).length > 1) val = val.replace(/\.+$/, '');
                $(this).val(val);
            });

            // ✅ IFSC AJAX Validation
            $(document).on('blur', '#bank_ifsc_code', function () {
                const $ifsc_data = $.trim($(this).val());
                const $ifscRGEX = /^[A-Z]{4}0[A-Z0-9]{6}$/i;

                if ($ifscRGEX.test($ifsc_data)) {
                    $('#bank_ifsc_code').removeClass('has-error');
                    $('#error_bank_ifsc_code').text('');
                    $('#error_name_of_bank, #error_bank_branch')
                        .html('<img src="{{ asset('images/ZKZg.gif') }}" width="50" height="50"/>');

                    $.ajax({
                        type: 'POST',
                        url: '{{ url('legacy/getBankDetails') }}',
                        data: {
                            ifsc: $ifsc_data,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (!response || response === 'null') {
                                alert("Only IFSC codes from West Bengal are accepted.");
                                $('#name_of_bank, #bank_branch').val('');
                                $('#error_name_of_bank, #error_bank_branch').empty();
                                return;
                            }

                            let data;
                            try {
                                data = typeof response === 'string' ? JSON.parse(response) : response;
                            } catch (e) {
                                console.error('Invalid JSON:', response);
                                return;
                            }

                            $('#name_of_bank').val(data.bank || '');
                            $('#bank_branch').val(data.branch || '');
                            $('#error_name_of_bank, #error_bank_branch').empty();
                        },
                        error: function () {
                            $('#error_bank_ifsc_code').text('Data fetch error');
                            $('#bank_ifsc_code').addClass('has-error');
                            $('#error_name_of_bank, #error_bank_branch').empty();
                        }
                    });
                } else {
                    $('#error_bank_ifsc_code').text('Invalid IFSC format, please check the code.');
                    $('#bank_ifsc_code').addClass('has-error');
                }
            });

            // ✅ Scheme ID specific actions
            if (scheme_id == 13) {
                $(document).on('click', '.modal-submit', function () {
                    const landRows = $('#landListModal tr').not(':first');
                    const memberRows = $('#memberListModal tr').not(':first');
                    const landArray = [];
                    const memberArray = [];

                    landRows.each(function () {
                        const cols = $(this).find('td');
                        landArray.push({
                            slno: cols.eq(0).text(),
                            block_name: cols.eq(1).text(),
                            mouza: cols.eq(2).text(),
                            jl_no: cols.eq(3).text(),
                            khatian_no: cols.eq(4).text(),
                            daag_no: cols.eq(5).text(),
                            quantity: cols.eq(6).text()
                        });
                    });

                    memberRows.each(function () {
                        const cols = $(this).find('td');
                        memberArray.push({
                            slno: cols.eq(0).text(),
                            f_member_name: cols.eq(1).text(),
                            f_member_address: cols.eq(2).text(),
                            f_member_age: cols.eq(3).text(),
                            f_member_profession: cols.eq(4).text(),
                            f_member_monthly_income: cols.eq(5).text(),
                            f_member_relationship: cols.eq(6).text(),
                            f_member_dependent_by_applicant: cols.eq(6).text()
                        });
                    });

                    $('#f_land_array').val(JSON.stringify(landArray));
                    $('#f_member_array').val(JSON.stringify(memberArray));

                    console.log(JSON.stringify(memberArray));
                    $(".modal-submit").hide();
                    $("#submitting, #submit_loader").show();
                });
            }

        });

    </script>
<!-- @endsection -->