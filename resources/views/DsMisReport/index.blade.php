<style type="text/css">
    .preloader1 {
        position: fixed;
        top: 40%;
        left: 52%;
        z-index: 999;
        background: transparent !important;
    }

    .disabledcontent {
        pointer-events: none;
        opacity: 0.4;
    }

    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }

    .modal {
        text-align: center;
        padding: 0 !important;
    }

    .modal:before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -4px;
    }

    .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
    }

    label.required:after {
        color: red;
        content: '*';
        font-weight: bold;
        margin-left: 5px;
        float: right;
        margin-top: 5px;
    }

    .filterDiv {
        border: 1px solid #d9d9d9;
        border-left: 3px solid deepskyblue;
        margin-bottom: 10px;
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }

    .resultDiv {
        border: 1px solid #d9d9d9;
        border-left: 3px solid seagreen;
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }

    /* Enhanced Design Styles */
    .page-header {
        background: linear-gradient(135deg, #6b89ed 0%, #605164 100%);
        color: white;
        padding: 6px 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-title i {
        font-size: 1.5rem;
    }

    /* Button Styling */
    .btn-action {
        border: none !important;
        border-radius: 6px !important;
        padding: 8px 16px !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
    }

    .btn-action:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
    }
</style>

@extends('layouts.app-template-datatable')
@section('content')
    <div class="row mb-1 ml-2">
        <div class="page-header col-sm-auto mt-4">
            {{-- <h1 class="page-title">
                <i class="fas fa-exchange-alt"></i> Districtwise Validation Report
            </h1> --}}
        </div>
    </div>
    <section class="content">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);">
                <h5 class="mb-0">Duare Sarkar Report of {{ $phase_arr->phase_des }}</h5>
            </div>
            <div class="card-body" style="padding: 15px;">
                <div id="loadingDiv"></div>
                <div class="filterDiv">
                    <div class="row">
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-block col-12">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>{{ $message }}</strong>
                            </div>
                        @endif
                        @if (count($errors) > 0)
                            <div class="alert alert-danger alert-block col-12">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li><strong> {{ $error }}</strong></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-row">
                                @if ($district_visible)
                                    <div class="form-group col-md-3">
                                        <label class="">District</label>
                                        <select name="district" id="district" class="form-control" tabindex="6">
                                            <option value="">--All --</option>
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->district_code }}"
                                                    @if (old('district') == $district->district_code) selected @endif>
                                                    {{ $district->district_name }}</option>
                                            @endforeach
                                        </select>
                                        <span id="error_district" class="text-danger"></span>

                                    </div>
                                @else
                                    <input type="hidden" name="district" id="district" value="{{ $district_code_fk }}" />
                                @endif
                                @if ($is_urban_visible)
                                    <div class="form-group col-md-3" id="divUrbanCode">
                                        <label class="">Rural/ Urban</label>

                                        <select name="urban_code" id="urban_code" class="form-control" tabindex="11">
                                            <option value="">--All --</option>
                                            @foreach (Config::get('constants.rural_urban') as $key => $val)
                                                <option value="{{ $key }}"
                                                    @if (old('urban_code') == $key) selected @endif>{{ $val }}
                                                </option>
                                            @endforeach

                                        </select>
                                        <span id="error_urban_code" class="text-danger"></span>
                                    </div>
                                @else
                                    <input type="hidden" name="urban_code" id="urban_code" value="{{ $rural_urban_fk }}" />
                                @endif

                                @if ($block_visible)
                                    <div class="form-group col-md-3" id="divBodyCode">
                                        <label class="" id="blk_sub_txt">Block/ Municipality.</label>

                                        <select name="block" id="block" class="form-control" tabindex="16">
                                            <option value="">--All --</option>


                                        </select>
                                        <span id="error_block" class="text-danger"></span>
                                    </div>
                                @else
                                    <input type="hidden" name="block" id="block"
                                        value="{{ $block_munc_corp_code_fk }}" />
                                @endif

                                <div class="form-group col-md-4" id="municipality_div"
                                    style="{{ $municipality_visible ? '' : 'display:none' }}">
                                    <label class="">Municipality</label>

                                    <select name="muncid" id="muncid" class="form-control" tabindex="16">
                                        <option value="">--All --</option>
                                        @foreach ($muncList as $munc)
                                            <option value="{{ $munc->urban_body_code }}"> {{ $munc->urban_body_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                    <span id="error_muncid" class="text-danger"></span>
                                </div>


                                <div class="form-group col-md-3">
                                    <label class="required-field">Till Date</label>
                                    @php
                                        $max_to = $c_date; // Or can put $today = date ("Y-m-d");
                                        $min_to = $base_date;
                                    @endphp
                                    <input type="hidden" name="from_date" id="from_date" class="form-control"
                                        value ="{{ $base_date }}" />
                                    <input type="date" name="to_date" id="to_date" class="form-control" tabindex="26"
                                        min="{{ $min_to }}" max="{{ $max_to }}" />
                                    <span id="error_to_date" class="text-danger"></span>


                                </div>
                                <div class="form-group col-md-3" style="margin-top: 32px;">
                                    <button type="button" id="submitting" value="Submit"
                                        class="btn btn-success success btn-lg modal-search form-submitted btn-action">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <div class=""><img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1"
                                            width="50px" height="50px" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive resultDiv mt-3" id="validation_lot_div">
                    <button class="btn btn-info exportToExcel  btn-action" type="button">Export to
                        Excel</button><br /><br /><br />
                    <table id="example" class="display data-table table2excel" cellspacing="0" width="100%"
                        style="border: 1px solid ghostwhite;">
                        <thead style="font-size: 12px;">
                            <tr role="row">
                                <th id="">Sl No. (A)</th>
                                <th id="location_id">District Name (B)</th>
                                <th>Applications Received at camp(till date) (C)</th>
                                <th>Applications under process for verification/other process (D = C-(E+F))</th>
                                <th>Applications accepted (till date) (E)</th>
                                <th>Applications rejected (till date) (F)</th>
                                <th>Services Delivered (till date) (G)</th>
                                <th>Information to Applicant once the Application approved (Over Phone) (till date) (H)</th>
                                <th>Information to Applicant once the application rejected (over Phone) (till date) (I)</th>
                                <th>Total Team formed Cummalative (till date) (J)</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px;"></tbody>
                        <tfoot>
                            <tr id="fotter_id"></tr>
                            <tr>
                                <td colspan="21" align="center" style="display:none;" id="fotter_excel">Heading</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
@endpush
