<!-- <div class="tab-pane fade" id="land_details_p"> -->
    <div class="card card-default">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><b>Land Details (In case of Dwelling House)</b></h4>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="mouza_name">Name of the Mouza</label>
                    <input type="text" name="mouza_name" id="mouza_name" class="form-control special-char"
                        placeholder="Mouza Name"
                        value="{{ $type == $op_type ? $row->mouza_name : old('mouza_name') }}"
                        maxlength="200" tabindex="1" />
                    <span id="error_mouza_name" class="text-danger"></span>
                </div>

                <div class="form-group col-md-4">
                    <label for="land_jlno">J.L.No.</label>
                    <input type="text" name="land_jlno" id="land_jlno" class="form-control special-char"
                        placeholder="J.L.No."
                        value="{{ $type == $op_type ? $row->land_jlno : old('land_jlno') }}"
                        maxlength="200" tabindex="1" />
                    <span id="error_land_jlno" class="text-danger"></span>
                </div>

                <div class="form-group col-md-4">
                    <label for="khatian_no">Khatian No.</label>
                    <input type="text" name="khatian_no" id="khatian_no" class="form-control special-char"
                        placeholder="Khatian No"
                        value="{{ $type == $op_type ? $row->khatian_no : old('khatian_no') }}"
                        maxlength="200" tabindex="1" />
                    <span id="error_khatian_no" class="text-danger"></span>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-4">
                    <label for="plot_no">Plot No.</label>
                    <input type="text" name="plot_no" id="plot_no" class="form-control special-char"
                        placeholder="Plot No"
                        value="{{ $type == $op_type ? $row->plot_no : old('plot_no') }}"
                        maxlength="200" tabindex="1" />
                    <span id="error_plot_no" class="text-danger"></span>
                </div>

                <div class="form-group col-md-4">
                    <label for="land_area">Area</label>
                    <input type="text" name="land_area" id="land_area" class="form-control special-char"
                        placeholder="Area"
                        value="{{ $type == $op_type ? $row->land_area : old('land_area') }}"
                        maxlength="200" tabindex="2" />
                    <span id="error_land_area" class="text-danger"></span>
                </div>

                <div class="form-group col-md-4">
                    <label for="land_holdername">In the Name of</label>
                    <input type="text" name="land_holdername" id="land_holdername" class="form-control special-char"
                        placeholder="Name"
                        value="{{ $type == $op_type ? $row->land_holdername : old('land_holdername') }}"
                        maxlength="200" tabindex="4" />
                    <span id="error_land_holdername" class="text-danger"></span>
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

<script src="{{ asset('js/FormEntry/land_p.js') }}"></script>
