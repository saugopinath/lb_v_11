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

@extends('layouts.app-template-datatable_new')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Beneficiary Log Report
            </h1>

        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div>
                        <div>
                            @if (($message = Session::get('success')) && ($id = Session::get('id')))
                                <div class="alert alert-success alert-block">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    <strong>{{ $message }} with Application ID: {{ $id }}</strong>


                                </div>
                            @endif
                            @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-block">
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


                        <input type="hidden" name="district_flag" id="district_flag" class="form-control" value="{{ $district_code_fk }}">


                        <div class="tab-content" style="margin-top:16px;">






                            <div class="tab-pane active" id="personal_details">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4><b>Search Criteria</b></h4>
                                    </div>
                                    <div class="panel-body">



                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label class="">Is Faulty</label>
                                                <select name="is_faulty" id="is_faulty" class="form-control"
                                                    tabindex="6">
                                                    <option value="0">Normal</option>
                                                    <option value="1">Faulty</option>
                                                </select>
                                                <span id="error_is_faulty" class="text-danger"></span>

                                            </div>

                                            <div class="form-group col-md-4">
                                                <label class="">Search Type</label>
                                                <select name="search_type" id="search_type" class="form-control"
                                                    tabindex="6">
                                                    <option value="1">Approved</option>
                                                    <option value="2">Verified</option>
                                                    <option value="3">Reject</option>
                                                </select>
                                                <span id="error_search_type" class="text-danger"></span>

                                            </div>

                                            @if ($district_visible)
                                                <div class="form-group col-md-4">
                                                    <label class="">District</label>
                                                    <select name="district" id="district" class="form-control"
                                                        tabindex="6">
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
                                                <input type="hidden" name="district" id="district"
                                                    value="{{ $district_code_fk }}" />
                                            @endif
                                            @if ($is_urban_visible)
                                                <div class="form-group col-md-4" id="divUrbanCode">
                                                    <label class="">Rural/ Urban</label>

                                                    <select name="urban_code" id="urban_code" class="form-control"
                                                        tabindex="11">
                                                        <option value="">--All --</option>
                                                        @foreach (Config::get('constants.rural_urban') as $key => $val)
                                                            <option value="{{ $key }}"
                                                                @if (old('urban_code') == $key) selected @endif>
                                                                {{ $val }}</option>
                                                        @endforeach

                                                    </select>
                                                    <span id="error_urban_code" class="text-danger"></span>
                                                </div>
                                            @else
                                                <input type="hidden" name="urban_code" id="urban_code"
                                                    value="{{ $rural_urban_fk }}" />

                                            @endif
                                            @if ($block_visible)
                                                <div class="form-group col-md-4" id="divBodyCode">
                                                    <label class="" id="blk_sub_txt">Block/Sub
                                                        Division.</label>

                                                    <select name="block" id="block" class="form-control"
                                                        tabindex="16">
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
                                                        <option value="{{ $munc->urban_body_code }}">
                                                            {{ $munc->urban_body_name }}</option>
                                                    @endforeach

                                                </select>
                                                <span id="error_muncid" class="text-danger"></span>
                                            </div>


                                            <div class="form-group col-md-4" id="gp_ward_div"
                                                style="{{ $gp_ward_visible ? '' : 'display:none' }}">
                                                <label class="" id="gp_ward_txt">GP/Ward</label>

                                                <select name="gp_ward" id="gp_ward" class="form-control"
                                                    tabindex="17">
                                                    <option value="">--All --</option>
                                                    @foreach ($gpList as $gp)
                                                        <option value="{{ $gp->gram_panchyat_code }}">
                                                            {{ $gp->gram_panchyat_name }}</option>
                                                    @endforeach

                                                </select>
                                                <span id="error_gp_ward" class="text-danger"></span>
                                            </div>


                                        </div>
                                        <div class="col-md-12" align="center">

                                            <button type="button" id="submitting" value="Submit"
                                                class="btn btn-success success btn-lg modal-search form-submitted">Search
                                            </button>

                                            <div class=""><img src="{{ asset('images/ZKZg.gif') }}"
                                                    id="submit_loader1" width="50px" height="50px"
                                                    style="display:none;"></div>

                                            <!--<button type="button" name="btn_personal_details" id="btn_personal_details" class="btn btn-info btn-lg">Next</button>-->
                                        </div>
                                        <br />
                                    </div>
                                </div>
                            </div>

                            <div class="tab-content" style="margin-top:16px;">


                                <div class="alert print-error-msg" style="display:none;" id="errorDiv">
                                    <button type="button" class="close" aria-label="Close"
                                        onclick="closeError('errorDiv')"><span aria-hidden="true">&times;</span></button>
                                    <ul></ul>
                                </div>



                                <div class="tab-pane active" id="search_details" style="display:none;">
                                    <div class="panel panel-default">
                                        <div class="panel-heading" id="heading_msg">
                                            <h4><b>Search Result</b></h4>
                                        </div>
                                        <div class="panel-body">

                                            <div class="pull-right" id="report_generation_text">Report
                                                Generated on:<b><?php echo date('l jS \of F Y h:i:s A'); ?></b></div>

                                            <!-- <button class="btn btn-info exportToExcel" type="button">Export
                                                to Excel</button><br /><br /><br /> -->
                                            <div id="divScrool">
                                                <table id="example"
                                                    class="table table-striped table-bordered table2excel"
                                                    style="width:100%">
                                                    <thead style="font-size: 12px;">
                                                        <th>Application ID</th>
                                                        <th>Name</th>
                                                        <th>Father Name</th>
                                                        <th>Entry Details</th>
                                                        <th>Verification Details</th>
                                                        <th>Approval Details</th>
                                                    </thead>
                                                    <tbody style="font-size: 14px;">

                                                    </tbody>
                                                    <tfoot>
                                                        <tr id="fotter_id"></tr>
                                                        <tr>
                                                            <td colspan="21" align="center" style="display:none;"
                                                                id="fotter_excel">
                                                                Heading</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>




                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>





                </div>






            </div>
            <!-- /.box -->
    </div>
    <!--/.col (left) -->

    </div>
  </div>
    </section>
    </div>


@endsection
@section('script')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script>
        
        //alert(base_date);

        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            // $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #beneficiaryLog').addClass("active");
            //loadDataTable();
                      
            $('#district').change(function() {
                var district = $(this).val();
                //alert(district);
                $('#urban_code').val('');
                $('#block').html('<option value="">--All --</option>');
                $('#muncid').html('<option value="">--All --</option>');
            });

            $('#urban_code').change(function() {
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
                        $.each(blocks, function(key, value) {
                            if (value.district_code == select_district_code) {
                                htmlOption += '<option value="' + value.id + '">' + value.text +
                                    '</option>';
                            }
                        });
                    } else if (select_body_type == 1) {
                        $("#blk_sub_txt").text('Subdivision');
                        $("#gp_ward_txt").text('Ward');
                        $("#municipality_div").show();
                        $.each(subDistricts, function(key, value) {
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
            $('#block').change(function() {
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
                            $.each(ulbs, function(key, value) {
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
                        $.each(gps, function(key, value) {
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
            $('#muncid').change(function() {
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
                            $.each(ulb_wards, function(key, value) {
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
            $('.modal-search').on('click', function() {
                // alert('Table Load');
                // loadDataTable();
                var district = $('#district').val();
                var district_flag = $('#district_flag').val();
                var urban_code = $('#urban_code').val();
                var block = $('#block').val();
                var gp_ward = $('#gp_ward').val();
                var muncid = $('#muncid').val();
                var search_type = $('#search_type').val();
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
                    "pageLength": 10,
                    'lengthMenu': [
                        [10, 20, 25, 50, 100, -1],
                        [10, 20, 25, 50, 100, 'All']
                    ],
                    "serverSide": true,
                    "processing": true,
                    "bRetrieve": true,
                    "oLanguage": {
                        "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
                    },
                    "ajax": {
                        url: "{{ route('getBeneficiaryLog') }}",
                        type: "post",
                        data: function(d) {
                            d.district = district,
                            d.district_flag = district_flag,
                            d.urban_code = $('#urban_code').val(),
                            d.block = $('#block').val(),
                            d.gp_ward = $('#gp_ward').val(),
                            d.muncid = $('#muncid').val(),
                            d.search_type = search_type,
                            d.is_faulty = is_faulty,
                            d._token = "{{ csrf_token() }}"
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#submit_btn').attr('disabled', false);
                            $('#loadingDiv').hide();
                            $('.preloader1').hide();
                            ajax_error(jqXHR, textStatus, errorThrown);
                        }
                    },
                    "initComplete": function() {
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
                            "data": "name"
                        },
                        {
                            "data": "father_name"
                        },
                        {
                            "data": "entry_details"
                        },
                        {
                            "data": "verification_details"
                        },
                        {
                            "data": "approval_details"
                        }
                    ],
                    "buttons": [{
                            extend: 'pdf',
                            footer: true,
                            pageSize: 'A4',
                            orientation: 'landscape',
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
        });
        
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
