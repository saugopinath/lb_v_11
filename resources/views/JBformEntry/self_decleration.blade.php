<?php
$old_receive_pension = old('receive_pension') ?? [];
$old_social_security_pension = old('social_security_pension') ?? [];

if ($type == $op_type) {
    $row_receive_pension = $row->receive_pension ? explode(',', $row->receive_pension) : [];
    $row_social_security_pension = $row->social_security_pension ? explode(',', $row->social_security_pension) : [];
}
?>

<!-- <div class="tab-pane fade" id="decl_details"> -->
    <div class="card card-default mb-3">
        <div class="card-header">
            <h4><b>Self Declaration</b></h4>
        </div>
        <div class="card-body">

            @if($scheme_id != 2)
                <div class="row mb-3">
                    <div class="form-group col-md-12 aadhar-text">
                        <label>
                            I
                            <select name="av_status" id="av_status" class="form-control d-inline-block w-auto">
                                <option value="1">give</option>
                                <option value="0">do not give</option>
                            </select>
                            consent to the use of the Aadhaar No. for authenticating my identity for social
                            security pension (In case Aadhaar no. provided by the applicant)
                        </label>
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="form-group col-md-12" tabindex="4">
                    <label>Presently, I am receiving following pension(s) from:</label>
                    <br />
                    @foreach(Config::get('constants.pension_body') as $key => $desc)
                        <label class="d-block">
                            <input type="checkbox" class="receive-pension" name="receive_pension[]" value="{{ $key }}"
                                @if($type == $op_type && in_array($key, $row_receive_pension, true)) checked
                                @elseif($type != $op_type && in_array($key, $old_receive_pension, true)) checked
                                @endif
                            >
                            {{ $desc }}
                        </label>
                    @endforeach
                </div>
            </div>

            @include('JBformEntry.self_decleration_additional')
            
        </div>
    </div>
<!-- </div> -->

<script src="{{ asset('js/FormEntry/self_dec.js') }}"></script>
