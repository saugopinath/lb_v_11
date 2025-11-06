@if (in_array($scheme_id, [2, 10, 11, 13, 17]))
<div class="row mb-3">
    <label>In case the applicant is receiving pension from other sources</label>
    <div class="form-group col-md-6 mt-2">
        <label>1.</label>
        <input type="text" name="receiving_pension_other_source_1" id="receiving_pension_other_source_1"
            class="form-control"
            value="{{ $type == $op_type ? $row->receiving_pension_other_source_1 : old('receiving_pension_other_source_1') }}"
            maxlength='300' tabindex="3" />
    </div>
    <div class="form-group col-md-6 mt-2">
        <label>2.</label>
        <input type="text" name="receiving_pension_other_source_2" id="receiving_pension_other_source_2"
            class="form-control"
            value="{{ $type == $op_type ? $row->receiving_pension_other_source_2 : old('receiving_pension_other_source_2') }}"
            maxlength='300' tabindex="3" />
    </div>
</div>
@endif

@if($scheme_id != 2 && $scheme_id != 11)
<div class="row mb-3">
    <div class="col-md-12">
        <div class="modal_field_name">
            In the event of my death, I hereby nominate (Please mention Name, Address & Relationship)
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="form-group col-md-4">
        <label>Name</label>
        <input type="text" name="nominate_name" id="nominate_name" class="form-control txtOnly" placeholder="Name"
            value="{{ $type == $op_type ? $row->nominate_name : old('nominate_name') }}" maxlength='200' />
        <span id="error_nominate_name" class="text-danger"></span>
    </div>
    <div class="form-group col-md-4">
        <label>Address</label>
        <input type="text" name="nominate_address" id="nominate_address" class="form-control special-char"
            placeholder="Address" value="{{ $type == $op_type ? $row->nominate_address : old('nominate_address') }}"
            maxlength='200' />
        <span id="error_nominate_address" class="text-danger"></span>
    </div>
    <div class="form-group col-md-4">
        <label>Relationship</label>
        <input type="text" name="nominate_relationship" id="nominate_relationship" class="form-control txtOnly"
            placeholder="Relationship"
            value="{{ $type == $op_type ? $row->nominate_relationship : old('nominate_relationship') }}" maxlength='200' />
        <span id="error_nominate_relationship" class="text-danger"></span>
    </div>
</div>

@if ($scheme_id == 17)
<div class="row mb-3">
    <div class="form-group col-md-12">
        <label>to receive the rest amount payable to me till my death</label>
    </div>
</div>
@endif
@endif

@if ($scheme_id == 17)
<div class="row mb-3">
    <div class="form-group col-md-12">
        <label>
            I
            <select name="ssp_y_n" id="ssp_y_n" class="form-control d-inline-block w-auto">
                <option value="1" @if($type == $op_type && $row->ssp_y_n == 1) selected @endif>am</option>
                <option value="0" @if($type == $op_type && $row->ssp_y_n == 0) selected @endif>am not</option>
            </select>
            a beneficiary of any other Social Security pension scheme or a recipient of Government pension or pension from any other organization.
        </label>
    </div>
</div>
<div class="row mb-3">
    <div class="form-group col-md-12">
        <label>
            I
            <select name="pucca_house_y_n" id="pucca_house_y_n" class="form-control d-inline-block w-auto">
                <option value="1" @if($type == $op_type && $row->pucca_house_y_n == 1) selected @endif>do</option>
                <option value="0" @if($type == $op_type && $row->pucca_house_y_n == 0) selected @endif>do not</option>
            </select>
            have Pucca dwelling house.
        </label>
    </div>
</div>

<div class="form-group col-md-12 mb-3" tabindex="4">
    <label>Presently, I am receiving following pension(s) from:</label><br/>
    @php
        $row_receive_pension = ($type == $op_type && $row->receive_pension) ? explode(',', $row->receive_pension) : [];
    @endphp
    @foreach(Config::get('constants.pension_body') as $key => $desc)
        <label class="d-block">
            <input type="checkbox" class="receive-pension" name="receive_pension[]" value="{{ $key }}"
                @if(in_array($key, $row_receive_pension, true) || in_array($key, $old_receive_pension, true)) checked @endif>
            {{ $desc }}
        </label>
    @endforeach
</div>

@else
<div class="row mb-3">
    <div class="form-group col-md-12" tabindex="5">
        <label>Presently, I am receiving the following social Security Pension/s (Please tick):</label><br/>
        @php
            $row_social_security_pension = ($type == $op_type && $row->social_security_pension) ? explode(',', $row->social_security_pension) : [];
        @endphp
        @foreach(Config::get('constants.social_pension_cat') as $key => $desc)
            <label class="d-block">
                <input type="checkbox" class="social-security-pension" name="social_security_pension[]" value="{{ $key }}"
                    @if(in_array($key, $row_social_security_pension, true) || in_array($key, $old_social_security_pension, true)) checked @endif>
                {{ $desc }}
            </label>
        @endforeach
    </div>
</div>

@if ($scheme_id == 11)
<div class="row mb-3">
    <div class="form-group col-md-12">
        <label>I hereby declare that I have not done remarriage</label>
    </div>
</div>
@endif
@endif

<div class="text-center col-md-12 mt-3">
    <button type="button" id="previous_btn_decl_details" class="btn btn-info btn-lg">Previous</button>
    <input type="button" class="btn btn-success btn-lg" name="btn_submit_preview" id="btn_submit_preview"
        value="Preview and Submit" data-toggle="modal" data-target="#confirm-submit_">
</div>
