<!-- <div class="tab-pane fade" id="land_details"> -->
    <div class="card card-default">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><b>Land Details</b></h4>
            <button type="button" name="btn_add_land" id="btn_add_land"
                class="btn btn-light text-primary btn-sm"
                data-toggle="modal" data-target="#addLandModal">
                <i class="fas fa-plus-circle mr-1"></i> Add Land Details
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="landList" class="table table-bordered table-striped table-hover" width="100%">
                    <thead class="thead-dark">
                        <tr style="font-size: 12px;">
                            <th>Serial No</th>
                            <th>Block</th>
                            <th>Mouza</th>
                            <th>JL NO.</th>
                            <th>Khatian No.</th>
                            <th>Daag No.</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($type == $op_type && $row->land_json != '')
                            @php
                                $land_list_arr = json_decode($row->land_json);
                                $i = 1;
                            @endphp
                            @foreach ($land_list_arr as $result)
                                <tr id="id_{{ $i }}">
                                    <td>{{ $i }}</td>
                                    <td>{{ trim($result->block_name) }}</td>
                                    <td>{{ $result->mouza }}</td>
                                    <td>{{ $result->jl_no }}</td>
                                    <td>{{ $result->khatian_no }}</td>
                                    <td>{{ $result->daag_no }}</td>
                                    <td>{{ $result->quantity }}</td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="delete_land({{ $i }})">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                @php $i++; @endphp
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="form-group mt-3">
                <span id="error_land_count" class="text-danger"></span>
            </div>

            <div class="row mt-2">
                <div class="form-group col-md-4">
                    <label class="required-field">Select Cultivation by Applicant (Yes/No)</label>
                    <select class="form-control" name="cultivation_by_applicant" id="cultivation_by_applicant">
                        @if ($type == $op_type)
                            <option value="Yes" @if ($row->cultivation_by_applicant == 'Yes') selected @endif>Yes</option>
                            <option value="No" @if ($row->cultivation_by_applicant == 'No') selected @endif>No</option>
                        @else
                            <option value="Yes" @if (old('cultivation_by_applicant') == 'Yes') selected @endif>Yes</option>
                            <option value="No" @if (old('cultivation_by_applicant') == 'No') selected @endif>No</option>
                        @endif
                    </select>
                    <span id="error_cultivation_by_applicant" class="text-danger"></span>
                </div>

                <div class="form-group col-md-4">
                    <label>Source of Present Income</label>
                    <input type="text" name="source_income" id="source_income" class="form-control special-char"
                        placeholder="Source of Present Income"
                        value="{{ $type == $op_type ? $row->source_income : old('source_income') }}" maxlength="255"
                        tabindex="3" />
                    <span id="error_source_income" class="text-danger"></span>
                </div>

                <div class="form-group col-md-4">
                    <label>Any other Benefits received</label>
                    <input type="text" name="any_other_benefitis" id="any_other_benefitis"
                        class="form-control special-char" placeholder="Any other Benefits received"
                        value="{{ $type == $op_type ? $row->any_other_benefitis : old('any_other_benefitis') }}"
                        maxlength="255" tabindex="3" />
                    <span id="error_any_other_benefitis" class="text-danger"></span>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="button" name="previous_btn_land_details" id="previous_btn_land_details"
                    class="btn btn-info btn-lg">
                    <i class="fas fa-arrow-left mr-1"></i> Previous
                </button>
                <button type="button" name="btn_land_details" id="btn_land_details"
                    class="btn btn-success btn-lg">
                    Next <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
<!-- </div> -->

<input type="hidden" name="f_member_array" id="f_member_array">
<input type="hidden" name="f_land_array" id="f_land_array">

<!-- Modal -->
<div class="modal fade" id="addLandModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exampleModalLabel">Add Land Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>Select Own Land / Barga Land</label>
                        <select class="form-control" name="land_type" id="land_type">
                            <option value="Own Land" @if (old('land_type') == 'Own Land') selected @endif>Own Land</option>
                            <option value="Barga Land" @if (old('land_type') == 'Barga Land') selected @endif>Barga Land</option>
                        </select>
                        <span id="error_land_type" class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="required-field">Block</label>
                        <input type="text" name="block_name" id="block_name" class="form-control special-char"
                            placeholder="Block" maxlength="100" value="" tabindex="4" />
                        <span id="error_block_name" class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="required-field">Mouza</label>
                        <input type="text" name="mouza" id="mouza" class="form-control special-char"
                            placeholder="Mouza" maxlength="100" value="" tabindex="4" />
                        <span id="error_mouza" class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="required-field">JL NO.</label>
                        <input type="text" name="jl_no" id="jl_no" class="form-control NumOnly"
                            placeholder="JL NO." maxlength="100" value="" tabindex="4" />
                        <span id="error_jl_no" class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="required-field">Khatian No.</label>
                        <input type="text" name="khatian_no" id="khatian_no" class="form-control NumOnly"
                            placeholder="Khatian No." maxlength="100" value="" tabindex="4" />
                        <span id="error_khatian_no" class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="required-field">Daag No.</label>
                        <input type="text" name="daag_no" id="daag_no" class="form-control NumOnly"
                            placeholder="Daag No." maxlength="100" value="" tabindex="4" />
                        <span id="error_daag_no" class="text-danger"></span>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="required-field">Quantity</label>
                        <input type="text" name="quantity" id="quantity" class="form-control price-field"
                            placeholder="Quantity" maxlength="100" value="" tabindex="4" />
                        <span id="error_quantity" class="text-danger"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="btn_addLand">
                    <i class="fas fa-plus-circle mr-1"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/FormEntry/land_oap.js') }}"></script>

<script>
    var type = $("#type").val();
    if (type == 2 || type == 3) {
        function delete_land(sl_no) {
            if (confirm('Are you sure?')) {
                var row = $('#landList tr#id_' + sl_no);
                var siblings = row.siblings();
                row.remove();
                siblings.each(function (index) {
                    $(this).children('td').first().text(index + 1);
                });
            }
        }
    }
</script>
