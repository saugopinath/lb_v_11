<div class="row">
    <!-- <div class="col-md-12">
        <hr>
    </div> -->

    {{-- Scheme ID 2: Disability Details --}}
    @if($scheme_id == 2)
        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Type of Disability</label>
                <select class="form-control form-select" name="disablity_type" id="disablity_type">
                    @if ($type == $op_type)
                        @foreach(Config::get('constants.disablity_type') as $key => $val)
                            <option value="{{ $key }}" @selected($row->type_disability == $key)>{{ $val }}</option>
                        @endforeach
                    @else
                        <option value="">--Select--</option>
                        @foreach(Config::get('constants.disablity_type') as $key => $val)
                            <option value="{{$key}}" @selected(old('disablity_type') == $key)>{{$val}}</option>
                        @endforeach
                    @endif
                </select>
                <small id="error_disablity_type" class="text-danger"></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Percentage of Disability</label>
                <input type="text" name="disablity_type_percentage" id="disablity_type_percentage" 
                       class="form-control" placeholder="Percentage" maxlength="5"
                       value="{{ $type == $op_type ? $row->percentage_disability : old('disablity_type_percentage') }}">
                <small id="error_disablity_type_percentage" class="text-danger"></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Authority Name</label>
                <input type="text" name="disablity_type_authority" id="disablity_type_authority" 
                       class="form-control txtOnly" placeholder="Certifying Authority" maxlength="200"
                       value="{{ $type == $op_type ? $row->certifying_auth : old('disablity_type_authority') }}">
                <small id="error_disablity_type_authority" class="text-danger"></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Authority Designation</label>
                <input type="text" name="disability_designation" id="disability_designation" 
                       class="form-control txtOnly" placeholder="Designation Name" maxlength="200"
                       value="{{ $type == $op_type ? $row->disability_designation : old('disability_designation') }}">
                <small id="error_disability_designation" class="text-danger"></small>
            </div>
        </div>
    @endif

    {{-- Scheme ID 5: Fisherman/Physically Handicapped --}}
    @if ($scheme_id == 5)
        <div class="col-md-4">
            <div class="form-group">
                <label>Belongs to Fisherman Community</label>
                <select class="form-control form-select" name="fisherman_comm" id="fisherman_comm" tabindex="14">
                    @if ($type == $op_type)
                        <option value="YES" @selected($row->fisherman_comm == 'YES')>Yes</option>
                        <option value="NO" @selected($row->fisherman_comm == 'NO')>No</option>
                    @else
                        <option value="">--Select--</option>
                        <option value="YES" @selected(old('fisherman_comm') == 'YES')>Yes</option>
                        <option value="NO" @selected(old('fisherman_comm') == 'NO')>No</option>
                    @endif
                </select>
                <small id="error_fisherman_comm" class="text-danger"></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Physically Handicapped</label>
                <select class="form-control form-select" name="phy_hadi_status" id="phy_hadi_status" tabindex="15">
                    @if ($type == $op_type)
                        <option value="No" @selected($row->phy_hadi_status == 'No')>No</option>
                        <option value="Yes" @selected($row->phy_hadi_status == 'Yes')>Yes</option>
                    @else
                        <option value="">--Select--</option>
                        <option value="No" @selected(old('phy_hadi_status') == 'No')>No</option>
                        <option value="Yes" @selected(old('phy_hadi_status') == 'Yes')>Yes</option>
                    @endif
                </select>
                <small id="error_phy_hadi_status" class="text-danger"></small>
            </div>
        </div>
    @endif

    {{-- Scheme ID 17: Temple Priest --}}
    @if ($scheme_id == 17)
        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Select Application Phase</label>
                <select class="form-control form-select" name="app_phase" id="app_phase">
                    @if ($type == $op_type)
                        @foreach(Config::get('constants.purohit_phase') as $key => $val)
                            <option value="{{ $key }}" @selected($row->app_phase == $key)>{{ $val }}</option>
                        @endforeach
                    @else
                        <option value="">--Select--</option>
                        @foreach(Config::get('constants.purohit_phase') as $key => $val)
                            <option value="{{$key}}" @selected(old('app_phase') == $key)>{{$val}}</option>
                        @endforeach
                    @endif
                </select>
                <small id="error_app_phase" class="text-danger"></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Temple Type</label>
                <select class="form-control form-select" name="temple_type" id="temple_type">
                    @if ($type == $op_type)
                        <option value="Temple Purohit" @selected($row->temple_type == 'Temple Purohit')>Temple Purohit</option>
                        <option value="Tribal Religious Place Purohit" @selected($row->temple_type == 'Tribal Religious Place Purohit')>Tribal Religious Place Purohit</option>
                        <option value="Community Purohit" @selected($row->temple_type == 'Community Purohit')>Community Purohit</option>
                    @else
                        <option value="">--Select--</option>
                        <option value="Temple Purohit" @selected(old('temple_type') == 'Temple Purohit')>Temple Purohit</option>
                        <option value="Tribal Religious Place Purohit" @selected(old('temple_type') == 'Tribal Religious Place Purohit')>Tribal Religious Place Purohit</option>
                        <option value="Community Purohit" @selected(old('temple_type') == 'Community Purohit')>Community Purohit</option>
                    @endif
                </select>
                <small id="error_temple_type" class="text-danger"></small>
            </div>
        </div>
    @endif

    {{-- Scheme ID 11: Husband’s Name --}}
    @if ($scheme_id == 11)
        <div class="col-md-12">
            <h6 class="mt-3 mb-2 font-weight-bold">Husband's Name</h6>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">First Name</label>
                <input type="text" name="husband_first_name" id="husband_first_name" 
                       class="form-control txtOnly" placeholder="First Name" maxlength="200"
                       value="{{ $type == $op_type ? $row->husband_fname : old('husband_first_name') }}" tabindex="4">
                <small id="error_husband_first_name" class="text-danger"></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="husband_middle_name" id="husband_middle_name" 
                       class="form-control txtOnly" placeholder="Middle Name" maxlength="100"
                       value="{{ $type == $op_type ? $row->husband_mname : old('husband_middle_name') }}" tabindex="5">
                <small id="error_husband_middle_name" class="text-danger"></small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="required-field">Last Name</label>
                <input type="text" name="husband_last_name" id="husband_last_name" 
                       class="form-control txtOnly" placeholder="Last Name" maxlength="200"
                       value="{{ $type == $op_type ? $row->husband_lname : old('husband_last_name') }}" tabindex="6">
                <small id="error_husband_last_name" class="text-danger"></small>
            </div>
        </div>
    @endif
</div>
