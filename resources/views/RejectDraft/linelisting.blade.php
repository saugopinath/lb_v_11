<style type="text/css">
    .required-field::after {
        content: "*";
        color: red;
    }

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

    .panel-heading {
        padding: 0;
        border: 0;
    }

    .panel-title>a,
    .panel-title>a:active {
        display: block;
        padding: 10px;
        color: #555;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        word-spacing: 3px;
        text-decoration: none;
    }

    .panel-heading a:before {
        font-family: 'Glyphicons Halflings';
        content: "\e114";
        float: right;
        transition: all 0.5s;
    }

    .panel-heading.active a:before {
        -webkit-transform: rotate(180deg);
        -moz-transform: rotate(180deg);
        transform: rotate(180deg);
    }

    #enCloserTable tbody tr td {
        padding: 10px 10px 10px 10px;
    }

    .modal-open {
        overflow: visible !important;
    }
</style>

@extends('layouts.app-template-datatable_new')
@section('content')

    <div class="content-wrapper">

        <section class="content">
            <div class="box box-default">
                <div class="box-body">

                    <div class="panel panel-default">
                        <div class="panel-heading"><span id="panel-icon">Applications Yet To Be Approved or Verified</div>
                        <div class="panel-body" style="padding: 5px;">
                            <div class="row">
                                @if ($message = Session::get('success'))
                                    <div class="alert alert-success alert-block">
                                        <button type="button" class="close" data-dismiss="alert">×</button>
                                        <strong>{{ $message }}</strong>

                                    </div>
                                @endif
                                @if (count($errors) > 0)
                                    <div class="alert alert-danger alert-block">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li><strong> {{ $error }}</strong></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="">IS Faulty?</label>
                                    <select class="form-control" name="faulty_status" id="faulty_status">
                                        <option value="0" selected>NO</option>
                                        <option value="1">YES</option>
                                    </select>
                                    <span id="error_faulty_status" class="text-danger"></span>
                                </div>
                                @if (count($ds_phase_list) > 0)
                                    <div class="form-group col-md-3">
                                        <label class="">Duare Sarkar Phase</label>
                                        <select class="form-control" name="ds_phase" id="ds_phase">
                                            <option value="">--All--</option>
                                            @foreach ($ds_phase_list as $ds_row)
                                                <option value="{{ $ds_row->phase_code }}">{{ $ds_row->phase_des }}</option>
                                            @endforeach
                                        </select>
                                        <span id="error_ds_phase" class="text-danger"></span>
                                    </div>
                                @else
                                    <input type="hidden" name="ds_phase" id="ds_phase" value="" />

                                @endif
                                <div class="form-group col-md-3">
                                    <label class="">Select District</label>
                                    <select class="form-control js-district_1" name="dist_code" id="dist_code">
                                        <option value="">--All--</option>
                                        @foreach ($district_list as $district_row)
                                            <option value="{{ $district_row->district_code }}">
                                                {{ $district_row->district_name }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error_dist_code" class="text-danger"></span>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label">Rural/Urban </label>
                                    <select name="filter_1" id="filter_1" class="form-control">
                                        <option value="">-----Select----</option>
                                        @foreach ($levels as $key => $value)
                                            <option value="{{ $key }}"> {{ $value }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="col-md-3">
                                    <label class="control-label" id="blk_sub_txt">Block/Sub Division </label>
                                    <select name="filter_2" id="filter_2" class="form-control">
                                        <option value="">-----Select----</option>
                                    </select>

                                </div>



                            </div>
                            <br>

                            <div class="row">
                                <div class="col-md-2">
                                    <label class=" control-label">&nbsp; </label>
                                    <button type="button" name="filter" id="filter"
                                        class="btn btn-success">Search</button>


                                </div>
                                <div class="col-md-offset-2">
                                    <label class=" control-label">&nbsp; </label>

                                    <button type="button" name="reset" id="reset"
                                        class="btn btn-warning">Reset</button>

                                </div>


                            </div>

                            <div class="row">

                                <div class="form-group col-md-offset-3 col-md-3 " style="display: none;"
                                    id="approve_rejdiv">
                                    <button type="button" name="bulk_approve" class="btn btn-info btn-lg" id="bulk_approve"
                                        value="approve">
                                        Bulk Reject</button>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading" id="panel_head">List of Applicants</div>
                        <div class="panel-body" style="padding: 5px; font-size: 14px;">
                            <div class="table-responsive">
                                {{-- <div class="form-group" style="font-weight:bold; font-size:25px;">
                <label class="control-label">Check All</label>
              <input type="checkbox" id='check_all_btn' style="width:48px;">
              </div> --}}
                                <table id="example" class="display" cellspacing="0" width="100%">
                                    <thead style="font-size: 12px;">
                                        <th>Application ID</th>
                                        <th>Applicant Name</th>
                                        <th>Swasthya Sathi Card No.</th>
                                        <th>Duare Sarkar Phase</th>
                                        <th>Address</th>
                                        <th>Action</th>
                                        <th>Check <input type="checkbox" id='check_all_btn' style="width:48px;"></th>
                                    </thead>
                                    <tbody style="font-size: 14px;"></tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade bd-example-modal-lg ben_view_modal" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header singleInfo">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h3 class="modal-title">Applicant Details (<span class="applicant_id_modal"></span>)</h3>
                        </div>
                        <div class="modal-body ben_view_body">
                            <div class="panel-group singleInfo" role="tablist" aria-multiselectable="true">
                                <div class="panel panel-default">
                                    <div class="panel-heading active" role="tab" id="personal">
                                        <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}"
                                                class="loader_img" width="150px" id="loader_img_personal"></div>
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" data-parent="#accordion"
                                                href="#collapsePersonal" aria-expanded="true"
                                                aria-controls="collapsePersonal">Personal Details</a>
                                        </h4>
                                    </div>
                                    <div id="collapsePersonal" class="panel-collapse collapse in" role="tabpanel"
                                        aria-labelledby="personal">
                                        <div class="panel-body">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">Swasthya Sathi Card No.</th>
                                                        <td id='sws_card_txt'></td>
                                                        <th scope="row">Aadhaar No.</th>
                                                        <td id="aadhar_no_encrypt"></td>
                                                        <td id="aadhar_no_original" style="display:none;"></td>

                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Duare Sarkar Registration no.</th>
                                                        <td id='duare_sarkar_registration_no'></td>
                                                        <th scope="row">Duare Sarkar Date:</th>
                                                        <td id='duare_sarkar_date'></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Name</th>
                                                        <td id='ben_fullname'></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Mobile No.</th>
                                                        <td id="mobile_no"></td>
                                                        <th scope="row">Email:</th>
                                                        <td id="email_id"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Gender</th>
                                                        <td id="gender"></td>
                                                        <th scope="row">DOB</th>
                                                        <td id="dob"></td>
                                                        <th scope="row">Age (as on 01-01-2021)</th>
                                                        <td id="ben_age"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Father Name</th>
                                                        <td id='father_fullname'></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Mother Name</th>
                                                        <td id="mother_fullname"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Spouse Name</th>
                                                        <td id="spouse_name"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Caste:</th>
                                                        <td id="caste"></td>
                                                        <th scope="row" class="caste">SC/ST Certificate No.</th>
                                                        <td id="caste_certificate_no" class="caste"></td>
                                                    </tr>


                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-group singleInfo">
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="contact">
                                        <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}"
                                                class="loader_img" width="150px" id="loader_img_contact"></div>

                                        <h4 class="panel-title">
                                            <a class="collapsed" role="button" data-toggle="collapse"
                                                data-parent="#accordion" href="#collapseContact" aria-expanded="false"
                                                aria-controls="collapseContact">Address Details</a>
                                        </h4>
                                    </div>
                                    <div id="collapseContact" class="panel-collapse collapse" role="tabpanel"
                                        aria-labelledby="contact">
                                        <div class="panel-body">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">District Name</th>
                                                        <td id="dist_name"></td>
                                                        <th scope="row">Police Station</th>
                                                        <td id="police_station"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Block/Municipality Name</th>
                                                        <td id="block_ulb_name"></td>
                                                        <th scope="row">Gp Ward Name</th>
                                                        <td id="gp_ward_name"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Village/Town/City</th>
                                                        <td id="village_town_city"></td>
                                                        <th scope="row">House / Premise No</th>
                                                        <td id="house_premise_no"></td>
                                                    </tr>
                                                    <tr>
                                                        </td>
                                                        <th scope="row">Post Office</th>
                                                        <td id="post_office"></td>
                                                        <th scope="row">Pincode</th>
                                                        <td id="pincode"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-group singleInfo">
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="bank">
                                        <div class="preloader1"><img src="{{ asset('images/ZKZg.gif') }}"
                                                class="loader_img" width="150px" id="loader_img_bank"></div>
                                        <h4 class="panel-title">
                                            <a class="collapsed" role="button" data-toggle="collapse"
                                                data-parent="#accordion" href="#collapseBank" aria-expanded="false"
                                                aria-controls="collapseBank">Bank Details </a>
                                        </h4>
                                    </div>
                                    <div id="collapseBank" class="panel-collapse collapse" role="tabpanel"
                                        aria-labelledby="bank">
                                        <div class="panel-body">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">Bank Name</th>
                                                        <td id="bank_name"></td>
                                                        <th scope="row">Branch Name</th>
                                                        <td id="branch_name"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Bank IFSC</th>
                                                        <td id="bank_ifsc"></td>
                                                        <th scope="row">Bank Account No.</th>
                                                        <td id="bank_code"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-group">
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="headingFour">
                                        <h4 class="panel-title"> <a>Action</a> </h4>
                                    </div>
                                    <div id="collapse4" class="panel-collapse collapse in" role="tabpanel"
                                        aria-labelledby="headingFour">
                                        <div class="panel-body">

                                            <div class="form-group col-md-4" style="display:none;" id="div_rejection">
                                                <label class="required" for="reject_cause">Select Reject Cause</label>
                                                <select name="reject_cause" id="reject_cause" class="form-control">
                                                    <option value="">--Select--</option>
                                                    @foreach ($reject_revert_reason as $r_arr)
                                                        <option value="{{ $r_arr->id }}">{{ $r_arr->reason }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="" for="heading">Enter Remarks</label>
                                                <textarea style="margin: 0px; width: 279px; height: 40px;" name="accept_reject_comments" id="accept_reject_comments"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            {{-- <button style="text-align:left" type="button" class="btn btn-primary">Save changes</button>
            <button type="button" id="modal_cls" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
                            <form method="POST" target="_blank" name="fullForm" id="fullForm">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="is_bulk" id="is_bulk" value="0" />
                                <input type="hidden" name="opreation_type" id="opreation_type" value="R" />
                                <input type="hidden" id="id" name="id" />
                                <input type="hidden" id="application_id" name="application_id" />
                                <input type="hidden" id="is_draft" name="is_draft" value="1" />

                                <input type="hidden" name="applicantId[]" id="applicantId" value="" />



                                <button type="button" style="float: right" class="btn btn-success"
                                    id="verifyReject">Approve</button>

                                <button style="display:none;" type="button" id="submitting" value="Submit"
                                    class="btn btn-success success" disabled>Processing Please Wait</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal" id="encolser_modal" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="encolser_name">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div id="encolser_content"> </div>


                    </div>
                </div>
            </div>
        </section>
    </div>


@endsection
@section('script')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script>
        $(document).ready(function() {
            var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
            var base_url = '{{ url('/') }}';
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #draftReject').addClass("active");
            $(document).on('show.bs.collapse', '.panel-collapse', function() {



                var id = $(this).attr('id');
                var application_id = $('#fullForm #application_id').val();
                //alert(application_id);
                if (id == 'collapsePersonal') {
                    $('#sws_card_txt').text('');
                    $('#duare_sarkar_registration_no').text('');
                    $('#duare_sarkar_date').text('');
                    $('#ben_fullname').text('');
                    $('#father_fullname').text('');
                    $('#mother_fullname').text('');
                    $('#caste').text('');
                    $('#gender').text('');
                    $('#ben_age').text('');
                    $('#mobile_no').text('');
                    $('#email').text('');
                    $('#aadhar_no_encrypt').text('');
                    $('#loader_img_personal').show();
                    var faulty_status = $('#faulty_status').val();
                    if (faulty_status == 1) {
                        var url = "{{ route('getBenViewPersonalDataFaultyDraft') }}";
                    } else {
                        var url = "{{ route('getBenViewPersonalDataDraft') }}";
                    }
                    $.ajax({
                        type: 'post',
                        url: url,
                        data: {
                            _token: '{{ csrf_token() }}',
                            benid: application_id
                        },
                        dataType: 'json',
                        success: function(response) {
                            // alert(response.personaldata.ss_card_no);
                            $('#loader_img_personal').hide();
                            $('.ben_view_button').removeAttr('disabled', true);
                            $('#sws_card_txt').text(response.personaldata.ss_card_no);
                            $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
                            $('#duare_sarkar_registration_no').text(response.personaldata
                                .duare_sarkar_registration_no);
                            $('#duare_sarkar_date').text(response.personaldata
                                .formatted_duare_sarkar_date);
                            $('#ben_fullname').text(response.personaldata.ben_fname + ' ' +
                                response.personaldata.ben_mname + ' ' + response
                                .personaldata.ben_lname);
                            $('#mobile_no').text(response.personaldata.mobile_no);
                            $('#email').text(response.personaldata.email);
                            $('#gender').text(response.personaldata.gender);
                            $('#dob').text(response.personaldata.formatted_dob);
                            $('#ben_age').text(response.personaldata.age_ason_01012021);
                            if (response.personaldata.father_mname !== undefined && response
                                .personaldata.father_mname !== null) {
                                var father_mname = response.personaldata.father_mname;
                            } else {
                                var father_mname = "";
                            }
                            if (response.personaldata.father_lname !== undefined && response
                                .personaldata.father_lname != null) {
                                var father_lname = response.personaldata.father_lname;
                            } else {
                                var father_lname = "";
                            }
                            $('#father_fullname').text(response.personaldata.father_fname +
                                ' ' + father_mname + ' ' + father_lname);
                            if (response.personaldata.mother_mname !== undefined && response
                                .personaldata.mother_mname != null) {
                                var mother_mname = response.personaldata.mother_mname;
                            } else {
                                var mother_mname = "";
                            }
                            if (response.personaldata.mother_lname !== undefined && response
                                .personaldata.mother_lname != null) {
                                var mother_lname = response.personaldata.mother_lname;
                            } else {
                                var mother_lname = "";
                            }
                            $('#mother_fullname').text(response.personaldata.mother_fname +
                                ' ' + mother_mname + ' ' + mother_lname);
                            $('#caste').text(response.personaldata.caste);
                            if (response.personaldata.caste == 'SC' || response.personaldata
                                .caste == 'ST') {
                                $('#caste_certificate_no').text(response.personaldata
                                    .caste_certificate_no);
                                $('.caste').show();
                            } else {
                                $('.caste').hide();
                            }


                            if (response.personaldata.spouse_fname !== undefined && response
                                .personaldata.spouse_fname !== null) {
                                var spouse_fname = response.personaldata.spouse_fname;
                            } else {
                                var spouse_fname = "";
                            }
                            if (response.personaldata.spouse_mname !== undefined && response
                                .personaldata.spouse_mname !== null) {
                                //console.log((response.personaldata.ben_mname);
                                var spouse_mname = response.personaldata.spouse_mname;
                            } else {
                                var spouse_mname = "";
                            }
                            if (response.personaldata.spouse_lname !== undefined && response
                                .personaldata.spouse_lname !== null) {
                                var spouse_lname = response.personaldata.spouse_lname;
                            } else {
                                var spouse_lname = "";
                            }
                            $('#spouse_name').text(spouse_fname + ' ' + spouse_mname + ' ' +
                                spouse_lname);


                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(errorThrown);
                            $('#loader_img_personal').hide();
                            $('.ben_view_button').removeAttr('disabled', true);
                            //alert(sessiontimeoutmessage);
                            // window.location.href=base_url;
                        }
                    });
                } else if (id == 'collapseContact') {
                    $('#loader_img_contact').show();
                    $('#dist_name').text('');
                    $('#block_ulb_name').text('');
                    $('#gp_ward_name').text('');
                    $('#village_town_city').text('');
                    $('#police_station').text('');
                    $('#post_office').text('');
                    $('#pincode').text('');
                    var faulty_status = $('#faulty_status').val();
                    if (faulty_status == 1) {
                        var url = "{{ route('getBenViewContactDataFaultyDraft') }}";
                    } else {
                        var url = "{{ route('getBenViewContactDataDraft') }}";
                    }
                    $.ajax({
                        type: 'post',
                        url: url,
                        data: {
                            _token: '{{ csrf_token() }}',
                            benid: application_id
                        },
                        dataType: 'json',
                        success: function(response) {
                            //console.log(response.contactdata);
                            $('#loader_img_contact').hide();
                            $('.ben_view_button').removeAttr('disabled', true);
                            $('#dist_name').text(response.contactdata.dist_name);
                            $('#block_ulb_name').text(response.contactdata.block_ulb_name);
                            $('#gp_ward_name').text(response.contactdata.gp_ward_name);
                            $('#village_town_city').text(response.contactdata
                                .village_town_city);
                            $('#police_station').text(response.contactdata.police_station);
                            $('#post_office').text(response.contactdata.post_office);
                            $('#pincode').text(response.contactdata.pincode);
                            $('#house_premise_no').text(response.contactdata.house_premise_no);

                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#loader_img_contact').hide();
                            $('.ben_view_button').removeAttr('disabled', true);
                            alert(sessiontimeoutmessage);
                            window.location.href = base_url;
                        }
                    });
                } else if (id == 'collapseBank') {
                    $('#loader_img_bank').show();
                    $('#bank_name').text('');
                    $('#branch_name').text('');
                    $('#bank_ifsc').text('');
                    $('#bank_code').text('');
                    var faulty_status = $('#faulty_status').val();
                    if (faulty_status == 1) {
                        var url = "{{ route('getBenViewBankDataFaultyDraft') }}";
                    } else {
                        var url = "{{ route('getBenViewBankDataDraft') }}";
                    }
                    $.ajax({
                        type: 'post',
                        url: url,
                        data: {
                            _token: '{{ csrf_token() }}',
                            benid: application_id
                        },
                        dataType: 'json',
                        success: function(response) {
                            $('#loader_img_bank').hide();
                            $('.ben_view_button').removeAttr('disabled', true);
                            $('#bank_name').text(response.bankdata.bank_name);
                            $('#branch_name').text(response.bankdata.branch_name);
                            $('#bank_ifsc').text(response.bankdata.bank_ifsc);
                            $('#bank_code').text(response.bankdata.bank_code);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#loader_img_bank').hide();
                            $('.ben_view_button').removeAttr('disabled', true);
                            alert(sessiontimeoutmessage);
                            window.location.href = base_url;
                        }
                    });
                }
                $(this).siblings('.panel-heading').addClass('active');

            });
            $(document).on('hide.bs.collapse', '.panel-collapse', function() {
                //$('.panel-collapse').on('hide.bs.collapse', function () {
                $(this).siblings('.panel-heading').removeClass('active');

            });
            $('.loader_img').hide();
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #processApplication').addClass("active");
            var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
            var base_url = '{{ url('/') }}';

            // alert(verification_allowded);
            $('#opreation_type').val('R');
            $("#verifyReject").html("Reject");
            $('#div_rejection').show();

            var dataTable = "";
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            dataTable = $('#example').DataTable({
                "scrollX": true,
                "paging": true,
                "searchable": true,
                "ordering": false,
                "bFilter": true,
                "bInfo": true,
                "pageLength": 20,
                'lengthMenu': [
                    [20, 50, 100],
                    [20, 50, 100]
                ],
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,
                "oLanguage": {
                    "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
                },
                "ajax": {
                    url: "{{ url('rejectDraft') }}",
                    type: "post",
                    data: function(d) {
                        d.filter_1 = $("#filter_1").val(),
                            d.filter_2 = $("#filter_2").val(),
                            d.block_ulb_code = $('#block_ulb_code').val(),
                            d.faulty_status = $('#faulty_status').val(),
                            d.district_code = $('#dist_code').val(),
                            d.gp_ward_code = $('#gp_ward_code').val(),
                            d.ds_phase = $("#ds_phase").val(),
                            d._token = "{{ csrf_token() }}"
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('.preloader1').hide();
                        // alert(sessiontimeoutmessage);
                        // window.location.href = base_url;
                    }
                },
                "initComplete": function() {
                    //console.log('Data rendered successfully');
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "ss_card_no"
                    },
                    {
                        "data": "ds_phase_des"
                    },
                    {
                        "data": "address"
                    },
                    {
                        "data": "view"
                    },
                    {
                        "data": "check"
                    }
                ],
                "columnDefs": [{
                    "targets": [6],
                    "orderable": false,
                    "searchable": true
                }]

            });
            $('#example').on('length.dt', function(e, settings, len) {
                $("#check_all_btn").prop("checked", false);
            });

            $('#check_all_btn').on('change', function() {


                var checked = $(this).prop('checked');

                dataTable.cells(null, 7).every(function() {
                    var cell = this.node();
                    $(cell).find('input[type="checkbox"][name="chkbx"]').prop('checked', checked);
                });
                var data = dataTable
                    .rows(function(idx, data, node) {
                        return $(node).find('input[type="checkbox"][name="chkbx"]').prop('checked');
                    })
                    .data()
                    .toArray();
                //console.log(data);
                if (data.length === 0) {
                    $("input.all_checkbox").removeAttr("disabled", true);

                } else {
                    $("input.all_checkbox").attr("disabled", true);
                }
                var anyBoxesChecked = false;
                var applicantId = Array();
                $('input[type="checkbox"][name="chkbx"]').each(function(index, value) {

                    if ($(this).is(":checked")) {
                        anyBoxesChecked = true;
                        applicantId.push(value.value);
                    }

                });

                $("#fullForm #applicantId").val($.unique(applicantId));
                if (anyBoxesChecked == true) {
                    $('#approve_rejdiv').show();
                    $('.ben_view_button').attr('disabled', true);
                    document.getElementById('bulk_approve').disabled = false;
                    // document.getElementById('bulk_blkchange').disabled = false;
                } else {
                    $('#approve_rejdiv').hide();
                    $('.ben_view_button').removeAttr('disabled', true);
                    document.getElementById('bulk_approve').disabled = true;
                    // document.getElementById('bulk_blkchange').disabled = true;
                }
                //console.log(applicantId);
            });
            $(document).on('click', '.ben_view_button', function() {
                $("#fullForm #is_bulk").val(0);
                $('#loader_img_personal').show();
                $(".singleInfo").show();
                $('.ben_view_button').attr('disabled', true);
                var benid = $(this).val();
                $('#fullForm #application_id').val(benid);
                $('.applicant_id_modal').html(benid);
                $("#collapseContact").collapse('hide');
                $("#collapseBank").collapse('hide');
                $("#collapseEncloser").collapse('hide');
                $('#duare_sarkar_registration_no').text('');
                $('#duare_sarkar_date').text('');
                var faulty_status = $('#faulty_status').val();
                if (faulty_status == 1) {
                    var url = "{{ route('getBenViewPersonalDataFaultyDraft') }}";
                } else {
                    var url = "{{ route('getBenViewPersonalDataDraft') }}";
                }
                $.ajax({
                    type: 'post',
                    url: url,
                    data: {
                        _token: '{{ csrf_token() }}',
                        benid: benid
                    },

                    dataType: 'json',
                    success: function(response) {
                        $('#loader_img_personal').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        $('#sws_card_txt').text(response.personaldata.ss_card_no);
                        $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
                        $('#duare_sarkar_registration_no').text(response.personaldata
                            .duare_sarkar_registration_no);
                        $('#duare_sarkar_date').text(response.personaldata
                            .formatted_duare_sarkar_date);
                        $('#ben_fullname').text(response.personaldata.ben_fname + ' ' + response
                            .personaldata.ben_mname + ' ' + response.personaldata.ben_lname);
                        $('#mobile_no').text(response.personaldata.mobile_no);
                        $('#email').text(response.personaldata.email);
                        $('#gender').text(response.personaldata.gender);
                        $('#dob').text(response.personaldata.formatted_dob);
                        $('#ben_age').text(response.personaldata.age_ason_01012021);
                        $('#father_fullname').text(response.personaldata.father_fname + ' ' +
                            response.personaldata.father_mname + ' ' + response.personaldata
                            .father_lname);
                        $('#mother_fullname').text(response.personaldata.mother_fname + ' ' +
                            response.personaldata.mother_mname + ' ' + response.personaldata
                            .mother_lname);
                        $('#caste').text(response.personaldata.caste);
                        if (response.personaldata.caste == 'SC' || response.personaldata
                            .caste == 'ST') {
                            $('#caste_certificate_no').text(response.personaldata
                                .caste_certificate_no);
                            $('.caste').show();
                        } else {
                            $('.caste').hide();
                        }

                        $('#spouse_name').text(response.personaldata.spouse_fname + ' ' +
                            response.personaldata.spouse_mname + ' ' + response.personaldata
                            .spouse_lname);

                        $('#fullForm #id').val(response.benid);


                    },
                    complete: function() {

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#loader_img_personal').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                });
                $('.ben_view_modal').modal('show');

            });




            $('#filter_1').change(function() {
                var filter_1 = $(this).val();

                $('#filter_2').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                select_district_code = $('#dist_code').val();

                var htmlOption = '<option value="">--All--</option>';
                $('#gp_ward_code').html('<option value="">--All --</option>');
                if (filter_1 == 1) {
                    $.each(subDistricts, function(key, value) {
                        if ((value.district_code == select_district_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                    $("#blk_sub_txt").text('Subdivision');
                    $("#gp_ward_txt").text('Ward');
                    $("#municipality_div").show();
                    $("#gp_ward_div").show();

                } else if (filter_1 == 2) {
                    // console.log(filter_1);
                    $.each(blocks, function(key, value) {
                        if ((value.district_code == select_district_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                    $("#blk_sub_txt").text('Block');
                    $("#gp_ward_txt").text('GP');
                    $("#municipality_div").hide();
                    $("#gp_ward_div").show();

                } else {
                    $("#blk_sub_txt").text('Block/Subdivision');
                    $("#gp_ward_txt").text('GP/Ward');
                    $("#municipality_div").hide();
                }
                $('#filter_2').html(htmlOption);

            });
            $('#filter_2').change(function() {
                var rural_urbanid = $('#filter_1').val();
                $('#gp_ward_code').html('<option value="">--All --</option>');
                if (rural_urbanid == 1) {
                    var sub_district_code = $(this).val();
                    if (sub_district_code != '') {
                        $('#block_ulb_code').html('<option value="">--All --</option>');
                        select_district_code = $('#dist_code').val();
                        var htmlOption = '<option value="">--All--</option>';
                        // console.log(sub_district_code);
                        //console.log(select_district_code);

                        $.each(ulbs, function(key, value) {
                            if ((value.district_code == select_district_code) && (value
                                    .sub_district_code == sub_district_code)) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                        $('#block_ulb_code').html(htmlOption);
                    } else {
                        $('#block_ulb_code').html('<option value="">--All --</option>');
                    }
                } else if (rural_urbanid == 2) {
                    $('#muncid').html('<option value="">--All --</option>');
                    $("#municipality_div").hide();
                    var block_code = $(this).val();
                    select_district_code = $('#dist_code').val();

                    var htmlOption = '<option value="">--All--</option>';
                    $.each(gps, function(key, value) {
                        if ((value.district_code == select_district_code) && (value.block_code ==
                                block_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                    $('#gp_ward_code').html(htmlOption);
                    $("#gp_ward_div").show();
                } else {
                    $('#block_ulb_code').html('<option value="">--All --</option>');
                }
            });
            $('#block_ulb_code').change(function() {
                var muncid = $(this).val();

                var district = $("#dist_code").val();
                var urban_code = $("#filter_1").val();
                if (district == '') {
                    $('#filter_1').val('');
                    $('#filter_2').html('<option value="">--All --</option>');
                    $('#block_ulb_code').html('<option value="">--All --</option>');

                }
                if (urban_code == '') {
                    alert('Please Select Rural/Urban First');
                    $('#filter_2').html('<option value="">--All --</option>');
                    $('#block_ulb_code').html('<option value="">--All --</option>');
                    $("#filter_1").focus();
                }
                if (muncid != '') {
                    var rural_urbanid = $('#filter_1').val();

                    if (rural_urbanid == 1) {

                        $('#gp_ward_code').html('<option value="">--All --</option>');
                        var htmlOption = '<option value="">--All--</option>';
                        $.each(ulb_wards, function(key, value) {
                            if (value.urban_body_code == muncid) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                        $('#gp_ward_code').html(htmlOption);
                        //console.log(htmlOption);
                    } else {
                        $('#gp_ward_code').html('<option value="">--All --</option>');
                        $("#gp_ward_div").hide();
                    }
                } else {
                    $('#gp_ward_code').html('<option value="">--All --</option>');
                }

            });
            $('#filter').click(function() {

                dataTable.ajax.reload();


            });

            $('#reset').click(function() {
                window.location.href = 'rejectDraft';
            });

            $('#encolser_modal').on('hidden.bs.modal', function(e) {
                $('.ben_view_modal').css({
                    'overflow': 'auto',
                });
                //$(".ben_view_modal").animate({ scrollTop: 0 }, "slow");
            });
            $(document).on('click', '.opreation_type', function() {
                if ($(this).val() == 'T') {
                    $('#div_rejection').show();
                    if ($(this).val() == 'T')
                        $("#verifyReject").html("Revert");
                    else if ($(this).val() == 'R')
                        $("#verifyReject").html("Reject");
                }

            });

            $('#bulk_approve').click(function() {
                $(".singleInfo").hide();
                $("#fullForm #is_bulk").val(1);
                $('#fullForm #id').val('');
                $('#fullForm #application_id').val('');
                benid = "";

                $('.ben_view_modal').modal('show');
            });
            $(document).on('click', '#verifyReject', function() {

                var scheme_id = $('#scheme_id').val();
                var reject_cause = $('#reject_cause').val();
                var opreation_type = $('#opreation_type').val();
                var accept_reject_comments = $('#accept_reject_comments').val();
                var is_bulk = $('#is_bulk').val();
                var applicantId = $('#applicantId').val();
                var faulty_status = $('#faulty_status').val();
                var valid = 1;
                if (opreation_type == 'R' || opreation_type == 'T') {
                    var valid = 0;
                    if (reject_cause != '') {
                        var valid = 1;

                    } else {
                        $.alert({
                            title: 'Error!!',
                            type: 'red',
                            icon: 'fa fa-warning',
                            content: '<strong>Please Select Cause</strong>',
                        });
                        return false;
                    }

                }
                if (valid == 1) {
                    $.confirm({
                        title: 'Warning',
                        type: 'orange',
                        icon: 'fa fa-warning',
                        content: '<strong>Are you sure to proceed?</strong>',
                        buttons: {
                            Ok: function() {
                                $("#submitting").show();
                                $("#verifyReject").hide();
                                var id = $('#id').val();
                                $.ajax({
                                    type: 'POST',
                                    url: "{{ url('rejectDraftPost') }}",
                                    data: {
                                        scheme_id: scheme_id,
                                        reject_cause: reject_cause,
                                        opreation_type: opreation_type,
                                        accept_reject_comments: accept_reject_comments,
                                        id: id,
                                        is_bulk: is_bulk,
                                        applicantId: applicantId,
                                        faulty_status: faulty_status,
                                        _token: '{{ csrf_token() }}',
                                    },
                                    success: function(data) {
                                        if (data.return_status) {
                                            dataTable.ajax.reload(null, false);
                                            $('.ben_view_modal').modal('hide');
                                            $.confirm({
                                                title: 'Success',
                                                type: 'green',
                                                icon: 'fa fa-check',
                                                content: data.return_msg,
                                                buttons: {
                                                    Ok: function() {

                                                        $("#submitting")
                                                            .hide();
                                                        $("#verifyReject")
                                                            .show();
                                                        $("html, body")
                                                            .animate({
                                                                    scrollTop: 0
                                                                },
                                                                "slow");
                                                    }
                                                }
                                            });
                                            //printMsg(data.return_msg,'1','errorDivMain');



                                        } else {
                                            $("#submitting").hide();
                                            $("#verifyReject").show();
                                            $('#errorDiv').animate({
                                                scrollTop: 0
                                            }, 'slow');
                                            alert(sessiontimeoutmessage);
                                            window.location.href = base_url;


                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        $.confirm({
                                            title: 'Error',
                                            type: 'red',
                                            icon: 'fa fa-warning',
                                            content: sessiontimeoutmessage,
                                            buttons: {
                                                Ok: function() {
                                                    //location.reload();
                                                }
                                            }
                                        });

                                    }
                                });
                            },
                            Cancel: function() {

                            },
                        }
                    });

                }
            });
        });

        function View_encolser_modal(doc_name, doc_type, is_profile_pic, application_id) {
            $('#encolser_name').html('');
            $('#encolser_content').html('');
            $('#encolser_name').html(doc_name + '(' + application_id + ')');
            $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
            var faulty_status = $('#faulty_status').val();
            if (faulty_status == 1) {
                var url = "{{ route('getBenViewPersonalDataFaultyDraft') }}";
            } else {
                var url = "{{ route('getBenViewPersonalDataDraft') }}";
            }
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    doc_type: doc_type,
                    is_profile_pic: is_profile_pic,
                    application_id: application_id,
                    _token: '{{ csrf_token() }}',
                },
            }).done(function(data, textStatus, jqXHR) {
                $('#encolser_content').html('');
                $('#encolser_content').html(data);
                $("#encolser_modal").modal();
            }).fail(function(jqXHR, textStatus, errorThrown) {
                $('#encolser_content').html('');
                alert(sessiontimeoutmessage);
                window.location.href = base_url;
            });
        }

        function controlCheckBox() {
            var anyBoxesChecked = false;
            var applicantId = Array();
            $(' input[type="checkbox"]').each(function() {
                if ($(this).is(":checked")) {
                    anyBoxesChecked = true;
                    applicantId.push($(this).val());
                }

            });
            $("#fullForm #applicantId").val($.unique(applicantId));
            if (anyBoxesChecked == true) {
                $('#approve_rejdiv').show();
                $("#check_all_btn").attr("disabled", true);
                $('.ben_view_button').attr('disabled', true);
                document.getElementById('bulk_approve').disabled = false;
                // document.getElementById('bulk_blkchange').disabled = false;
            } else {
                $('#approve_rejdiv').hide();
                $('.ben_view_button').removeAttr('disabled', true);
                $("#check_all_btn").removeAttr("disabled", true);
                document.getElementById('bulk_approve').disabled = true;
                // document.getElementById('bulk_blkchange').disabled = true;
            }
            //console.log(applicantId);
        }

        function printMsg(msg, msgtype, divid) {
            $("#" + divid).find("ul").html('');
            $("#" + divid).css('display', 'block');
            if (msgtype == '0') {
                //alert('error');
                $("#" + divid).removeClass('alert-success');
                //$('.print-error-msg').removeClass('alert-warning');
                $("#" + divid).addClass('alert-warning');
            } else {
                $("#" + divid).removeClass('alert-warning');
                $("#" + divid).addClass('alert-success');
            }
            if (Array.isArray(msg)) {
                $.each(msg, function(key, value) {
                    $("#" + divid).find("ul").append('<li>' + value + '</li>');
                });
            } else {
                $("#" + divid).find("ul").append('<li>' + msg + '</li>');
            }
        }

        function closeError(divId) {
            $('#' + divId).hide();
        }
    </script>
@stop
