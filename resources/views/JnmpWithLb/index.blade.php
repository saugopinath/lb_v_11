<style type="text/css">
    .required-field::after {
        content: "*";
        color: red;
    }

    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }

    .card-header-custom {
        font-size: 16px;
        background: linear-gradient(to right, #c9d6ff, #e2e2e2);
        font-weight: bold;
        font-style: italic;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
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
        padding: 5px;
        color: #555;
        font-size: 12px;
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

    .required:after {
        color: red;
        content: '*';
        font-weight: bold;
        margin-left: 5px;
        float: right;
        margin-top: 5px;
    }

    #loadingDivModal {
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

    .disabledcontent {
        pointer-events: none;
        opacity: 0.4;
    }
</style>

@extends('layouts.app-template-datatable')

@section('content')

    <div class="content-header">
        <h1>
            Re Activate Death Incident
            <span class="badge bg-info" style="font-size: 14px;">
                (These beneficiaries were de-activated as per death incidents received from Janma Mrityu Portal.)
            </span>
        </h1>
    </div>
    <div class="container-fluid">
        <!-- Alerts -->
        <div>
            @if (($message = Session::get('success')) && ($id = Session::get('id')))
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>{{ $message }} with Application ID: {{ $id }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($message = Session::get('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><strong>{{ $error }}</strong></li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Search Criteria -->
        <div class="card mt-4">
            <div class="card-header card-header-custom">
                <h4 class="mb-0"><b>Search Criteria</b></h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="is_faulty" class="form-label">Is Faulty</label>
                        <select name="is_faulty" id="is_faulty" class="form-select">
                            <option value="1">Yes</option>
                            <option value="2">No</option>
                        </select>
                        <span id="error_is_faulty" class="text-danger"></span>
                    </div>

                    @if ($district_visible)
                        <div class="col-md-4">
                            <label for="district" class="form-label">District</label>
                            <select name="district" id="district" class="form-select">
                                <option value="">-- All --</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->district_code }}" @if (old('district') == $district->district_code)
                                    selected @endif>
                                        {{ $district->district_name }}
                                    </option>
                                @endforeach
                            </select>
                            <span id="error_district" class="text-danger"></span>
                        </div>
                    @else
                        <input type="hidden" name="district" id="district" value="{{ $district_code_fk }}">
                    @endif

                    @if ($is_urban_visible)
                        <div class="col-md-4" id="divUrbanCode">
                            <label class="form-label">Rural / Urban</label>
                            <select name="urban_code" id="urban_code" class="form-select">
                                <option value="">-- All --</option>
                                @foreach (Config::get('constants.rural_urban') as $key => $val)
                                    <option value="{{ $key }}" @if (old('urban_code') == $key) selected @endif>
                                        {{ $val }}
                                    </option>
                                @endforeach
                            </select>
                            <span id="error_urban_code" class="text-danger"></span>
                        </div>
                    @else
                        <input type="hidden" name="urban_code" id="urban_code" value="{{ $rural_urban_fk }}">
                    @endif

                    @if ($block_visible)
                        <div class="col-md-4" id="divBodyCode">
                            <label for="block" id="blk_sub_txt" class="form-label">Block/Sub Division</label>
                            <select name="block" id="block" class="form-select">
                                <option value="">-- All --</option>
                            </select>
                            <span id="error_block" class="text-danger"></span>
                        </div>
                    @else
                        <input type="hidden" name="block" id="block" value="{{ $block_munc_corp_code_fk }}">
                    @endif

                    <div class="col-md-4" id="municipality_div" style="{{ $municipality_visible ? '' : 'display:none' }}">
                        <label class="form-label">Municipality</label>
                        <select name="muncid" id="muncid" class="form-select">
                            <option value="">-- All --</option>
                            @foreach ($muncList as $munc)
                                <option value="{{ $munc->urban_body_code }}">{{ $munc->urban_body_name }}</option>
                            @endforeach
                        </select>
                        <span id="error_muncid" class="text-danger"></span>
                    </div>

                    <div class="col-md-4" id="gp_ward_div" style="{{ $gp_ward_visible ? '' : 'display:none' }}">
                        <label id="gp_ward_txt" class="form-label">GP/Ward</label>
                        <select name="gp_ward" id="gp_ward" class="form-select">
                            <option value="">-- All --</option>
                            @foreach ($gpList as $gp)
                                <option value="{{ $gp->gram_panchyat_code }}">
                                    {{ $gp->gram_panchyat_name }}
                                </option>
                            @endforeach
                        </select>
                        <span id="error_gp_ward" class="text-danger"></span>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" id="submitting" class="btn btn-success btn-lg modal-search">
                        Search
                    </button>
                    <img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1" width="50" height="50"
                        style="display:none;">
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div id="search_details" class="card mt-4" style="display:none;">
            <div class="card-header card-header-custom  d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><b>Search Result</b></h4>
                <div>Report Generated on: <b>{{ now()->format('l jS \\of F Y h:i:s A') }}</b></div>
            </div>
            <div class="card-body">
                <button class="btn btn-info mb-3 exportToExcel" type="button">Export to Excel</button>
                <div class="table-responsive" id="divScrool">
                    <table id="example" class="data-table" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>Application ID</th>
                                <th>Beneficiary ID</th>
                                <th>Name</th>
                                <th>Father Name</th>
                                <th>Block/Municipality</th>
                                <th>GP/Ward</th>
                                <th>Aadhar No.</th>
                                <th>Mobile No.</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px;"></tbody>
                        <tfoot>
                            <tr id="fotter_id"></tr>
                            <tr>
                                <td colspan="9" id="fotter_excel" style="display:none;">Heading</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUpdateAadhar" tabindex="-1" aria-labelledby="modalUpdateAadharLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalUpdateAadharLabel">Beneficiary Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <div id="loadingDivModal"></div>

                    <div id="updateDiv">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h4 class="text-center text-primary">
                                    Beneficiary ID: <span id="application_id"></span>
                                </h4>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped" style="font-size:14px;">
                            <tr>
                                <td>
                                    <strong>Name as JNMP Portal: </strong>
                                    <span id="name_jnmp_div"></span>
                                </td>
                                <td>
                                    <strong>Date of Death: </strong>
                                    <span id="death_div"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Name: </strong>
                                    <span id="name_div"></span>
                                </td>
                                <td>
                                    <strong>Gender: </strong>
                                    <span id="gender_div"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Mobile No.: </strong>
                                    <span id="mobile_div"></span>
                                </td>
                                <td>
                                    <strong>Father's Name: </strong>
                                    <span id="father_div"></span>
                                </td>
                            </tr>
                        </table>

                        <!-- Hidden Fields -->
                        <input type="hidden" name="pension_id" id="pension_id" value="">
                        <input type="hidden" name="ben_application_id" id="ben_application_id" value="">
                        <input type="hidden" name="update_scheme_id" id="update_scheme_id" value="">

                        <!-- File & Inputs -->
                        <div class="table-responsive">
                            <table class="table table-bordered" style="font-size:14px;">
                                <tr>
                                    <th>Upload File: <span class="text-danger">*</span></th>
                                    <td>
                                        <input type="file" id="file_stop_payment" class="form-control">
                                        <small class="text-muted">(Only jpeg, jpg, png, pdf — Max size: 500 KB)</small>
                                        <span class="text-danger" id="error_file"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Re-active Reason: <span class="text-danger">*</span></th>
                                    <td>
                                        <select id="reactive_reason" class="form-select">
                                            @foreach($reactive_reasons as $reactive_reason)
                                                <option value="{{ $reactive_reason->id }}">
                                                    {{ $reactive_reason->reactive_reason }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger" id="error_reactive_reason"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Remarks: <span class="text-danger">*</span></th>
                                    <td>
                                        <input type="text" id="remarks" class="form-control" maxlength="100">
                                        <small class="text-muted">Max 100 characters allowed</small>
                                        <span class="text-danger" id="error_remarks"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mt-3 mb-2">
                            <button id="verifySubmit" class="btn btn-success btn-lg">
                                Save as Alive
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@push("scripts")
    <script src="{{ asset('js/master-data-v2.js') }}"></script>
    <script>
        var base_date = '{{ $base_date }}';
        var c_date = '{{ $c_date }}';
        //alert(base_date);

        $(document).ready(function () {
            $('.sidebar-menu li').removeClass('active');
            // $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #jnmpData').addClass("active");
            //loadDataTable();
            $(".exportToExcel").click(function (e) {

                // Collect filter values
                var district = $('#district').val();
                var urban_code = $('#urban_code').val();
                var block = $('#block').val();
                var gp_ward = $('#gp_ward').val();
                var muncid = $('#muncid').val();
                var is_faulty = $('#is_faulty').val();

                // CSRF token
                var token = "{{ csrf_token() }}";

                // Data to send
                var data = {
                    _token: token,
                    district: district,
                    urban_code: urban_code,
                    block: block,
                    gp_ward: gp_ward,
                    muncid: muncid,
                    is_faulty: is_faulty
                };

                // Local redirectPost function (merged here)
                function redirectPost(url, postData = {}) {
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // Ensure CSRF is included
                    if (!postData._token) {
                        postData._token = "{{ csrf_token() }}";
                    }

                    for (let key in postData) {
                        if (postData.hasOwnProperty(key)) {
                            let input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = postData[key];
                            form.appendChild(input);
                        }
                    }

                    document.body.appendChild(form);
                    form.submit();
                }

                // Call the function
                redirectPost('generateExcel', data);
            });

            $("#from_date").on('blur', function () {
                var from_date = $('#from_date').val();
                if (from_date != '') {
                    //alert(from_date);
                    document.getElementById("to_date").setAttribute("min", from_date);
                } else {
                    //alert(c_date);
                    document.getElementById("to_date").setAttribute("min", base_date);
                }
            });

            $('#district').change(function () {
                var district = $(this).val();
                //alert(district);
                $('#urban_code').val('');
                $('#block').html('<option value="">--All --</option>');
                $('#muncid').html('<option value="">--All --</option>');
            });

            $('#urban_code').change(function () {
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
                        $.each(blocks, function (key, value) {
                            if (value.district_code == select_district_code) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                    } else if (select_body_type == 1) {
                        $("#blk_sub_txt").text('Subdivision');
                        $("#gp_ward_txt").text('Ward');
                        $("#municipality_div").show();
                        $.each(subDistricts, function (key, value) {
                            if (value.district_code == select_district_code) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                    } else {
                        $("#blk_sub_txt").text('Block/Subdivision');
                    }
                    $('#block').html(htmlOption);
                }

            });
            $('#block').change(function () {
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
                            $.each(ulbs, function (key, value) {
                                if ((value.district_code == select_district_code) && (value
                                    .sub_district_code == sub_district_code)) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text +
                                        '</option>';
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
                        $.each(gps, function (key, value) {
                            if ((value.district_code == select_district_code) && (value
                                .block_code == block_code)) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
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
            $('#muncid').change(function () {
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
                            $.each(ulb_wards, function (key, value) {
                                if (value.urban_body_code == municipality_code) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text +
                                        '</option>';
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
            $('.modal-search').on('click', function () {
                // loadDataTable();
                var district = $('#district').val();
                var urban_code = $('#urban_code').val();
                var block = $('#block').val();
                var gp_ward = $('#gp_ward').val();
                var muncid = $('#muncid').val();
                var is_faulty = $('#is_faulty').val();
                // alert(is_faulty);

                $("#submit_loader1").show();
                $("#submitting").hide();
                $('#search_details').show();
                if ($.fn.DataTable.isDataTable('#example')) {
                    $('#example').DataTable().destroy();
                }
                var table = $('#example').DataTable({
                    dom: 'Blfrtip',
                    "scrollX": true,
                    "paging": true,
                    "searchable": true,
                    "ordering": false,
                    "bFilter": true,
                    "bInfo": true,
                    "pageLength": 25,
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
                        url: "{{ route('getJnmpData') }}",
                        type: "post",
                        data: function (d) {
                            d.district = district,
                                d.urban_code = $('#urban_code').val(),
                                d.block = $('#block').val(),
                                d.gp_ward = $('#gp_ward').val(),
                                d.muncid = $('#muncid').val(),
                                d.is_faulty = $('#is_faulty').val(),
                                d._token = "{{ csrf_token() }}"
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $('#submit_btn').attr('disabled', false);
                            $('#loadingDiv').hide();
                            $('.preloader1').hide();
                            ajax_error(jqXHR, textStatus, errorThrown);
                        }
                    },
                    "initComplete": function () {
                        $('#loadingDiv').hide();
                        $("#submit_loader1").hide();
                        $("#submitting").show();
                        //console.log('Data rendered successfully');
                    },
                    "columns": [
                        {
                            "data": "application_id"
                        },
                        {
                            "data": "beneficiary_id"
                        },
                        {
                            "data": "ben_fname"
                        },
                        {
                            "data": "father_name"
                        },
                        {
                            "data": "block_ulb_name"
                        },
                        {
                            "data": "gp_ward_name"
                        },
                        {
                            "data": "aadhar_no"
                        },
                        {
                            "data": "mobile_no"
                        },
                        {
                            "data": "action"
                        }
                    ],
                    "buttons": [{
                        extend: 'pdf',
                        footer: true,
                        pageSize: 'A4',
                        //orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5],

                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        pageSize: 'A4',
                        //orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5],
                            stripHtml: false,
                        }
                    },
                        // 'pdf'
                    ],
                });
            });
            $('#file_stop_payment').change(function () {
                var card_file = document.getElementById("file_stop_payment");
                if (card_file.value != "") {
                    var attachment;
                    attachment = card_file.files[0];
                    // console.log(attachment.type)
                    var type = attachment.type;
                    if (attachment.size > 1048576) {
                        document.getElementById("error_file").innerHTML =
                            "<br><i class='fa fa-warning'></i> Unaccepted document file size. Max size 500 KB. Please try again";
                        $('#file_stop_payment').val('');
                        return false;
                    } else if (type != 'image/jpeg' && type != 'image/png' && type != 'application/pdf') {
                        document.getElementById("error_file").innerHTML =
                            "<br><i class='fa fa-warning'></i> Unaccepted document file format. Only jpeg,jpg,png and pdf. Please try again";
                        $('#file_stop_payment').val('');
                        return false;
                    } else {
                        $('#file_upload_btn').show();
                        document.getElementById("error_file").innerHTML = "";
                    }
                }
            });
        });
        $(document).on('click', '#verifySubmit', function () {
            var error_file = '';
            var error_remarks = '';
            var remarks = $('#remarks').val();
            var reactive_reason = $('#reactive_reason').val();
            var file_sp = document.getElementById("file_stop_payment");
            var file_attachment = file_sp.files[0];

            if (file_sp.value != '') {
                error_file = '';
                $('#error_file').text(error_file);
                $('#file_stop_payment').removeClass('has-error');
            } else {
                error_file = 'Supproting Document is required';
                $('#error_file').text(error_file);
                $('#file_stop_payment').addClass('has-error');
            }

            if (reactive_reason != '') {
                error_reactive_reason = '';
                $('#error_reactive_reason').text(error_reactive_reason);
                $('#reactive_reason').removeClass('has-error');
            } else {
                error_reactive_reason = 'Re-active reason is required.';
                $('#error_reactive_reason').text(error_reactive_reason);
                $('#reactive_reason').addClass('has-error');
            }

            if (remarks != '') {
                error_remarks = '';
                $('#error_remarks').text(error_remarks);
                $('#remarks').removeClass('has-error');
            } else {
                error_remarks = 'Remarks is required.';
                $('#error_remarks').text(error_remarks);
                $('#remarks').addClass('has-error');
            }

            if (error_file != '' || error_reactive_reason != '' || error_remarks != '') {
                return false;
            } else {
                Swal.fire({
                    title: 'Confirmation!',
                    html: 'Are you sure you want to activate this beneficiary?<br><br><span style="color: black;"><b>Note:</b> After activation, this beneficiary will start receiving payment.</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        var beneficiary_Id = $('#pension_id').val();
                        var application_id = $('#ben_application_id').val();
                        var is_faulty = $('#is_faulty').val();
                        var remarks = $('#remarks').val();
                        var reactive_reason = $('#reactive_reason').val();
                        var formData = new FormData();
                        var files = $('#file_stop_payment')[0].files;
                        formData.append('file_stop_payment', files[0]);
                        formData.append('id', beneficiary_Id);
                        formData.append('application_id', application_id);
                        formData.append('remarks', remarks);
                        formData.append('reactive_reason', reactive_reason);
                        formData.append('is_faulty', is_faulty);
                        formData.append('_token', '{{ csrf_token() }}');

                        $('.loadingDivModal').show();

                        $.ajax({
                            type: 'post',
                            url: "{{ route('activeJnmpBeneficiary') }}",
                            data: formData,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                $('.loadingDivModal').hide();

                                if (response.return_status == 1) {
                                    Swal.fire({
                                        title: response.title,
                                        icon: response.type,
                                        html: response.msg,
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#3085d6'
                                    }).then(() => {
                                        $('#modalUpdateAadhar').modal('hide');
                                        $('#res_div').hide();
                                        $('#submit_btn').trigger('click');
                                        $("html, body").animate({ scrollTop: 0 }, "slow");
                                    });
                                } else {
                                    var html = '';
                                    html += '<ul>';
                                    if (Array.isArray(response.msg)) {
                                        $.each(response.msg, function (key, value) {
                                            html += '<li>' + value + '</li>';
                                        });
                                    } else {
                                        html = '<li>' + response.msg + '</li>';
                                    }
                                    html += '</ul>';

                                    Swal.fire({
                                        title: response.title,
                                        icon: response.type,
                                        html: html,
                                        confirmButtonColor: '#d33',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                $('.loadingDivModal').hide();
                                console.log(textStatus);
                                console.log(errorThrown);
                                ajax_error(jqXHR, textStatus, errorThrown);
                            }
                        });
                    }
                });


            }
        });

        function viewModalFunction(value) {

            $('#loadingDivModal').show();
            $.ajax({
                type: 'POST',
                url: "{{ route('modalDataView') }}",
                data: {
                    id: value,
                    is_faulty: $('#is_faulty').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#loadingDivModal').hide();
                    if (response.status == 1) {
                        $.alert({
                            title: response.title,
                            type: response.type,
                            icon: response.icon,
                            content: response.msg
                        });
                        $('#submit_btn').trigger('click');
                        $("html, body").animate({
                            scrollTop: 0
                        }, "slow");
                    } else {
                        $('#update_scheme_id').val('');
                        $('#pension_id').val('');
                        $('#ben_application_id').val('');
                        $('#application_id').val('');
                        $('#old_aadhar_no').val('');
                        $('#file_stop_payment').val('');
                        $('#remarks').val('');
                        $('#file_stop_payment').removeClass('has-error');
                        $('#error_file_stop_payment').text('');
                        $('#name_div').text(response.ben_name);
                        $('#father_div').text(response.father_name);
                        $('#mobile_div').text(response.mobile_no);
                        $('#gender_div').text(response.gender);
                        $('#update_scheme_id').val(response.scheme_id);
                        $('#pension_id').val(response.beneficiary_id);
                        $('#ben_application_id').val(response.application_id);
                        $('#application_id').text(response.beneficiary_id);
                        $('#name_jnmp_div').text(response.jnmp_fullname);
                        $('#death_div').text(response.jnmp_date_of_death);
                        var file_msg = '(Image type must be ' + response.doc_type + ' and image size max ' +
                            response.doc_size_kb + ' KB)';
                        $('#file_msg').text(file_msg);
                        $('#loadingDivModal').hide();
                        $('#file_stop_payment').removeClass('has-error');
                        $('#error_file').text('');
                        $('#modalUpdateAadhar').modal('show');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingDivModal').hide();
                    ajax_error(jqXHR, textStatus, errorThrown);
                }
            });
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
                $.each(msg, function (key, value) {
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
@endpush
