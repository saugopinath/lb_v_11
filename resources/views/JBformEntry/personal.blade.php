<!-- <div class="tab-pane active " id="personal_details"> -->
<div class="card ">
    <div class="card-header">
        <h4><b>Personal Details</b></h4>
    </div>
    <div class="card-body">

        @if($type == 1)
            <div class="form-group row">
                <div class="col-md-4">
                    <label class="col-form-label required-field">Aadhaar Number</label>
                    <input type="text" name="aadhar_no_dup_check" id="aadhar_no_dup_check"
                        class="form-control NumOnly @error('aadhar_no_dup_check') is-invalid @enderror"
                        placeholder="Aadhar No." maxlength="12" value="{{ old('aadhar_no_dup_check') }}" />
                    <div id="error_aadhar_no_dup_check" class="invalid-feedback"></div>
                </div>
                <div class="col-md-2 align-self-end">
                    <input class="btn btn-danger" type="submit" name="btnDuplicateSubmit" id="btnDuplicateSubmit"
                        value="Check Duplicate">
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-4">
                    <label class="col-form-label required-field">Application Date</label>
                    <input type="date" name="application_date" id="application_date" class="form-control"
                        max="{{ date('Y-m-d') }}">
                    <div id="error_application_date" class="invalid-feedback"></div>
                </div>
            </div>
        @endif

        @if($type == 4)
            <div class="form-group row">
                <div class="col-md-4">
                    <label class="col-form-label required-field">Aadhaar Number</label>
                    <input type="text" name="aadhar_no_dup_check_cmo" id="aadhar_no_dup_check_cmo"
                        class="form-control NumOnly @error('aadhar_no_dup_check_cmo') is-invalid @enderror"
                        placeholder="Aadhar No." maxlength="12" value="{{ old('aadhar_no_dup_check_cmo') }}" />
                    <input type="hidden" name="grievance_id" id="grievance_id" value="{{ $grievance_id }}">
                    <div id="error_aadhar_no_dup_check_cmo" class="invalid-feedback"></div>
                </div>
                <div class="col-md-2 align-self-end">
                    <input class="btn btn-danger" type="submit" name="btnDuplicateCMOSubmit" id="btnDuplicateCMOSubmit"
                        value="Check Duplicate">
                </div>
            </div>
        @endif

        @if($type == 1)
            <div class="form-group row">
                <label class="col-md-12 col-form-label required-field"><b>Application Type: </b></label>
                <div class="col-md-4">
                    <select class="form-control" name="entry_type" id="entry_type" @if(in_array('entry_type', $readonly))
                    readonly @endif>
                        @php
                            $sel_val = '';
                            if ($type == $op_type && isset($row->entry_type)) {
                                $sel_val = $row->entry_type;
                            }
                        @endphp
                        @if($normal_entry)
                            <option value="Normal" @if($sel_val == "Normal") selected @endif>Normal Entry</option>
                        @endif
                        @if($ds_allow)
                            <option value="Form through Duare Sarkar camp" @if($sel_val == "Form through Duare Sarkar camp")
                            selected @endif>
                                Form through Duare Sarkar camp ({{$ds_phase_des}})
                            </option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-12">
                    <h5>For <b>Duare Sarkar</b> entry please select <i>"Form through Duare Sarkar camp"</i></h5>
                </div>
            </div>

            <div class="form-group row duareSarkar" style="display:none;">
                <div class="col-md-4">
                    <label class="col-form-label required-field">Duare Sarkar Registration No.</label>
                    <input type="text" name="ds_registration_no" id="ds_registration_no" class="form-control"
                        placeholder="Duare Sarkar Registration No." maxlength="25"
                        value="{{ $type == $op_type ? $row->ds_registration_no : old('ds_registration_no') }}"
                        @if(in_array('ds_registration_no', $readonly)) readonly @endif />
                    <div id="error_ds_registration_no" class="invalid-feedback"></div>
                </div>
                <div class="col-md-4">
                    <label class="col-form-label required-field">Duare Sarkar Date</label>
                    <input type="date" name="ds_date" id="ds_date" class="form-control" max="{{ date('Y-m-d') }}"
                        value="{{ $type == $op_type ? $row->ds_date : old('ds_date') }}" @if(in_array('ds_date', $readonly))
                        readonly @endif />
                    <div id="error_ds_date" class="invalid-feedback"></div>
                </div>
            </div>
        @endif

        <input type="hidden" name="scheme_id" id="scheme_id" value="{{ $scheme_id }}">
        <input type="hidden" name="type" id="type" value="{{ $type }}">

        <div class="form-group row">
            <label class="col-md-12 col-form-label required-field">Beneficiary Name</label>
            <div class="col-md-4">
                <label class="col-form-label required-field">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control txtOnly"
                    placeholder="First Name" maxlength="200"
                    value="{{ $type == $op_type ? $row->ben_fname : old('first_name') }}" @if(in_array('first_name', $readonly)) readonly @endif />
                <div id="error_first_name" class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
                <label class="col-form-label">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" class="form-control txtOnly"
                    placeholder="Middle Name" maxlength="100"
                    value="{{ $type == $op_type ? $row->ben_mname : old('middle_name') }}" @if(in_array('middle_name', $readonly)) readonly @endif />
                <div id="error_middle_name" class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
                <label class="col-form-label required-field">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control txtOnly" placeholder="Last Name"
                    maxlength="200" value="{{ $type == $op_type ? $row->ben_lname : old('last_name') }}"
                    @if(in_array('last_name', $readonly)) readonly @endif />
                <div id="error_last_name" class="invalid-feedback"></div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-4">
                <label class="col-form-label required-field">Gender</label>
                <select class="form-control" name="gender" id="gender" @if(in_array('gender', $readonly)) readonly
                @endif>
                    @if($type == $op_type)
                        @foreach(Config::get('constants.gender') as $key => $val)
                            @if($scheme_id == 11 && in_array($key, ['Male', 'Other'])) @continue @endif
                            <option value="{{ $key }}" @if($row->gender == $key) selected @endif>{{ $val }}</option>
                        @endforeach
                    @else
                        <option value="">--Select--</option>
                        @foreach(Config::get('constants.gender') as $key => $val)
                            @if($scheme_id == 11 && in_array($key, ['Male', 'Other'])) @continue @endif
                            <option value="{{ $key }}" @if(old('gender') == $key) selected @endif>{{ $val }}</option>
                        @endforeach
                    @endif
                </select>
                <div id="error_gender" class="invalid-feedback"></div>
            </div>

            <div class="col-md-4">
                <label class="col-form-label">Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control"
                    value="{{ $type == $op_type ? $row->dob : old('dob') }}" @if(in_array('dob', $readonly)) readonly
                    @endif />
                <div id="error_dob" class="invalid-feedback"></div>
            </div>

            <div class="col-md-4">
                <label class="col-form-label required-field">Age (as on {{ date('d/m/Y') }})</label>
                <input type="text" id="txt_age" class="form-control"
                    value="{{ $type == $op_type ? $row->ben_age : old('txt_age') }}" readonly />
                <div id="error_txt_age" class="invalid-feedback"></div>
            </div>
        </div>

        <!-- Father's Name -->
        <div class="form-group row mt-3">
            <label class="col-md-12 col-form-label">Father's Name</label>
            <div class="col-md-4">
                <label class="col-form-label required-field">First Name</label>
                <input type="text" name="father_first_name" id="father_first_name" class="form-control txtOnly"
                    placeholder="First Name" maxlength="200"
                    value="{{ $type == $op_type ? $row->father_fname : old('father_first_name') }}"
                    @if(in_array('father_first_name', $readonly)) readonly @endif />
                <div id="error_father_first_name" class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
                <label class="col-form-label">Middle Name</label>
                <input type="text" name="father_middle_name" id="father_middle_name" class="form-control txtOnly"
                    placeholder="Middle Name" maxlength="100"
                    value="{{ $type == $op_type ? $row->father_mname : old('father_middle_name') }}"
                    @if(in_array('father_middle_name', $readonly)) readonly @endif />
                <div id="error_father_middle_name" class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
                <label class="col-form-label required-field">Last Name</label>
                <input type="text" name="father_last_name" id="father_last_name" class="form-control txtOnly"
                    placeholder="Last Name" maxlength="200"
                    value="{{ $type == $op_type ? $row->father_lname : old('father_last_name') }}"
                    @if(in_array('father_last_name', $readonly)) readonly @endif />
                <div id="error_father_last_name" class="invalid-feedback"></div>
            </div>
        </div>

        <!-- Mother's Name -->
        <div class="form-group row mt-3">
            <label class="col-md-12 col-form-label">Mother's Name</label>
            <div class="col-md-4">
                <label class="col-form-label required-field">First Name</label>
                <input type="text" name="mother_first_name" id="mother_first_name" class="form-control txtOnly"
                    placeholder="First Name" maxlength="200"
                    value="{{ $type == $op_type ? $row->mother_fname : old('mother_first_name') }}"
                    @if(in_array('mother_first_name', $readonly)) readonly @endif />
                <div id="error_mother_first_name" class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
                <label class="col-form-label">Middle Name</label>
                <input type="text" name="mother_middle_name" id="mother_middle_name" class="form-control txtOnly"
                    placeholder="Middle Name" maxlength="100"
                    value="{{ $type == $op_type ? $row->mother_mname : old('mother_middle_name') }}"
                    @if(in_array('mother_middle_name', $readonly)) readonly @endif />
                <div id="error_mother_middle_name" class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
                <label class="col-form-label required-field">Last Name</label>
                <input type="text" name="mother_last_name" id="mother_last_name" class="form-control txtOnly"
                    placeholder="Last Name" maxlength="200"
                    value="{{ $type == $op_type ? $row->mother_lname : old('mother_last_name') }}"
                    @if(in_array('mother_last_name', $readonly)) readonly @endif />
                <div id="error_mother_last_name" class="invalid-feedback"></div>
            </div>
        </div>

        <!-- Continue rest of your fields in similar style -->

        <div class="row mt-3">

            {{-- Caste --}}
            <div class="form-group col-md-4">
                <label class="required-field">Caste</label>
                <select class="form-control" name="caste_category" id="caste_category" @if(in_array('caste_category', $readonly)) readonly @endif>
                    @if ($type == $op_type)
                        @if($scheme_id == 3)
                            <option value="SC">SC</option>
                        @elseif ($scheme_id == 1)
                            <option value="ST">ST</option>
                        @else
                            @foreach(Config::get('constants.caste') as $key => $val)
                                <option value="{{$key}}" @if($row->gender == $key) selected @endif>{{$val}}</option>
                            @endforeach
                        @endif
                    @else
                        @if($scheme_id == 3)
                            <option value="SC">SC</option>
                        @elseif ($scheme_id == 1)
                            <option value="ST">ST</option>
                        @else
                            @foreach(Config::get('constants.caste') as $key => $val)
                                <option value="{{$key}}" @if(old('caste_category') == $key) selected @endif>{{$val}}</option>
                            @endforeach
                        @endif
                    @endif
                </select>
                <span id="error_caste_category" class="text-danger"></span>
            </div>

            {{-- Caste Certificate No --}}
            <div class="form-group col-md-4" id="caste_certificate_no_section">
                <label class="{{ in_array($scheme_id, [1, 3, 19]) ? 'required-field' : '' }}">Caste Certificate
                    No.</label>
                <input type="text" name="caste_certificate_no" id="caste_certificate_no" class="form-control"
                    placeholder="Caste Certificate No." maxlength="200"
                    value="{{$type == $op_type ? $row->caste_certificate_no : old('caste_certificate_no')}}"
                    @if(in_array('caste_certificate_no', $readonly)) readonly @endif />
                <span id="error_caste_certificate_no" class="text-danger"></span>
            </div>

            {{-- Marital Status --}}
            <div class="form-group col-md-4">
                <label class="required-field">Marital Status</label>
                <select class="form-control" name="marital_status" id="marital_status" @if(in_array('marital_status', $readonly)) readonly @endif>
                    @if($type == $op_type)
                        @foreach(Config::get('constants.marital_status') as $key => $val)
                            <option value="{{ $key }}" @if($row->marital_status == $key) selected @endif>{{ $val }}</option>
                        @endforeach
                    @else
                        <option value="">--Select--</option>
                        @foreach(Config::get('constants.marital_status') as $key => $val)
                            <option value="{{ $key }}" @if(old('marital_status') == $key) selected @endif>{{ $val }}</option>
                        @endforeach
                    @endif
                </select>
                <span id="error_marital_status" class="text-danger"></span>
            </div>
        </div>

        {{-- Spouse Section --}}
        <div class="row" id="spouse_section">
            <div class="col-12"><label>Spouse Name (if applicable)</label></div>

            <div class="form-group col-md-4">
                <label>First Name</label>
                <input type="text" name="spouse_first_name" id="spouse_first_name" class="form-control txtOnly"
                    placeholder="First Name" maxlength="200"
                    value="{{ $type == $op_type ? $row->spouse_fname : old('spouse_first_name') }}"
                    @if(in_array('spouse_first_name', $readonly)) readonly @endif />
                <span id="error_spouse_first_name" class="text-danger"></span>
            </div>

            <div class="form-group col-md-4">
                <label>Middle Name</label>
                <input type="text" name="spouse_middle_name" id="spouse_middle_name" class="form-control txtOnly"
                    placeholder="Middle Name" maxlength="100"
                    value="{{ $type == $op_type ? $row->spouse_mname : old('spouse_middle_name') }}"
                    @if(in_array('spouse_middle_name', $readonly)) readonly @endif />
                <span id="error_spouse_middle_name" class="text-danger"></span>
            </div>

            <div class="form-group col-md-4">
                <label>Last Name</label>
                <input type="text" name="spouse_last_name" id="spouse_last_name" class="form-control txtOnly"
                    placeholder="Last Name" maxlength="200"
                    value="{{$type == $op_type ? $row->spouse_lname : old('spouse_last_name') }}"
                    @if(in_array('spouse_last_name', $readonly)) readonly @endif />
                <span id="error_spouse_last_name" class="text-danger"></span>
            </div>
        </div>

        {{-- Monthly Family Income --}}
        <div class="form-group col-md-4">
            <label class="required-field">Monthly Family Income (In Rs)</label>
            <input type="text" name="monthly_income" id="monthly_income" class="form-control price-field"
                placeholder="Monthly Family Income(Rs.)" maxlength="9"
                value="{{$type == $op_type ? $row->mothly_income : old('monthly_income') }}"
                @if(in_array('monthly_income', $readonly)) readonly @endif>
            <span id="error_monthly_income" class="text-danger"></span>
        </div>


        @if ($scheme_id == 2 || $scheme_id == 5 || $scheme_id == 17 || $scheme_id == 11)
            <div class="additional_details mt-3">
                <hr>
                @include('JBformEntry.personal_additional')
            </div>
        @endif

        <div class="form-group row mt-4">
            <div class="col-md-12 text-center">
                <button type="button" name="btn_personal_details" id="btn_personal_details"
                    class="btn btn-success btn-lg">Next</button>
            </div>
        </div>

    </div>
</div>
<!-- </div> -->



<script src="{{ asset('js/FormEntry/personal_details.js') }}"></script>