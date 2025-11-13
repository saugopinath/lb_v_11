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

@extends('layouts.app-template-datatable')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Caste Matched Report
            </h1>

        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div>
                        <!-- class="box box-primary" -->
                        <div class="box-header with-border">
                            <h3 class="box-title"><b>

                                </b></h3>
                            <!-- <p><h3 class="box-title"><b>Bandhu Prakalpa (for SC)</b></h3></p> -->
                        </div>

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





                        <div class="tab-content" style="margin-top:16px;">






                            <div class="tab-pane active" id="personal_details">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4><b>Search Criteria</b></h4>
                                    </div>
                                    <div class="panel-body">



                                        <div class="row">


                        



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
                                            <h4><b>Search Result</b> <span style="background-color: antiquewhite;">(<i class="fa fa-check text-success"></i> - <b>Name Matched</b> , <i class="fa fa-close text-danger"></i> - <b>Name Not Matched</b>) </span></h4>
                                        </div>
                                        <div class="panel-body">

                                            <div class="pull-right" id="report_generation_text">Report
                                                Generated on:<b><?php echo date('l jS \of F Y h:i:s A'); ?></b></div>

                                            <button class="btn btn-info exportToExcel" type="button">Export
                                                to Excel</button><br /><br /><br />
                                            <div id="divScrool">
                                                <table id="example"
                                                    class="table table-striped table-bordered table2excel"
                                                    style="width:100%">
                                                    <thead>
                                                        <th colspan="4" style="text-align:center;"></th>
                                                        <th colspan="3" style="text-align:center; background-color: LightGray;">Matched Report</th>
                                                        <th></th>
                                                    </thead>
                                                    <thead style="font-size: 12px;">
                                                        <th>Application ID</th>
                                                        <th>Application Name</th>
                                                        <th>Caste(SC/ST)</th>
                                                        <th>Caste Certificate NO.</th>
                                                        <th>With Aadhar No.</th>
                                                        <th>With Caste Certificate No.</th>
                                                        <th>With Khadya Sathi</th>
                                                        {{-- <th>View More</th> --}}
                                                    </thead>
                                                    <tbody style="font-size: 14px;">
                                                        <tr>
                                                            <td>100015459</td>
                                                            <td>SHANKARI GHOSH</td>
                                                            <td>ST</td>
                                                            <td>391</td>
                                                            <td><i class="fa fa-check text-success"></i></td>
                                                            <td><i class="fa fa-close text-danger"></i></td>
                                                            <td><i class="fa fa-check text-success"></i></td>
                                                            <td><button class="btn btn-info"><i class="fa fa-eye"></i> View</button></td>
                                                        </tr>
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
    <div class="modal fade" id="modalUpdateAadhar" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Beneficiary Details</h4>
                </div>
                <div class="modal-body">
                    <div id="loadingDivModal"></div>
                    <div class="" id="updateDiv">
                        <!-- <div class="panel-heading">Enter Bank Details</div>
            <div class="panel-body"> -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4 style="text-align: center;" class="text-primary">Application ID: <span
                                        id="application_id"></span></h4>
                            </div>
                        </div>
                        <table class="table table-bordered table-responsive table-condensed table-striped"
                            style="font-size: 14px;">
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
                                    <strong>Name : </strong>
                                    <span id="name_div"></span>
                                </td>
                                <td>
                                    <strong>Gender: </strong>
                                    <span id="gender_div"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Mobile NO.: </strong>
                                    <span id="mobile_div"></span>
                                </td>
                                <td>
                                    <strong>Father's Name :</strong>
                                    <span id="father_div"></span>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="pension_id" id="pension_id" value="">
                        <input type="hidden" name="ben_application_id" id="ben_application_id" value="">
                        <input type="hidden" name="update_scheme_id" id="update_scheme_id" value="">
                        {{-- <input type="hidden" name="old_aadhar_no" id="old_aadhar_no" value=""> --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-responsive table-condensed"
                                style="width:100%; font-size: 14px;">
                                <tr>
                                    <th>Upload File: <span class="text-danger">*</span></th>
                                    <td>
                                        <input type="file" name="file_stop_payment" class="form-control"
                                            id="file_stop_payment">
                                        <small class="text-info" style="font-weight: normal;"> (Only
                                            jpeg,jpg,png,pdf file and maximum size should be less than 500
                                            KB)</small>
                                        <span class="text-danger" id="error_file"
                                            style="font-size: 12px; font-weight: bold;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Remarks: </th>
                                    <td>
                                        <input type="text" name="remarks" id="remarks" class="form-control"
                                            value="" maxlength="100">
                                        <small style="font-weight: normal;">Max 100 character allowed</small>
                                        <span class="text-danger" id="error_remarks"
                                            style="font-size: 12px; font-weight: bold;"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="text-align: center;"><input type="button" name="submit"
                                    value="Active" id="verifySubmit" class="btn btn-success btn-lg"></div>
                        </div>
                        <!-- </div> -->
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    </section>
    </div>


@endsection
@section('script')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script>
        var base_date = '{{ $base_date }}';
        var c_date = '{{ $c_date }}';
        //alert(base_date);

        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #mis-report').addClass("active");
            //loadDataTable();
            $(".exportToExcel").click(function(e) {
                // alert('ok');
                $(".table2excel").table2excel({
                    // exclude CSS class
                    exclude: ".noExl",
                    name: "Worksheet Name",
                    filename: "Lakshmir Bhandar Mis Report", //do not include extension
                    fileext: ".xls" // file extension
                });
            });
            $("#from_date").on('blur', function() {
                var from_date = $('#from_date').val();
                if (from_date != '') {
                    //alert(from_date);
                    document.getElementById("to_date").setAttribute("min", from_date);
                } else {
                    //alert(c_date);
                    document.getElementById("to_date").setAttribute("min", base_date);
                }
            });

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
                var urban_code = $('#urban_code').val();
                var block = $('#block').val();
                var gp_ward = $('#gp_ward').val();
                var muncid = $('#muncid').val();

                $("#submit_loader1").show();
                $("#submitting").hide();
                $('#search_details').show();
                // if ($.fn.DataTable.isDataTable('#example')) {
                //     $('#example').DataTable().destroy();
                // }
                // var table = $('#example').DataTable({
                //     dom: 'Blfrtip',
                //     "scrollX": true,
                //     "paging": true,
                //     "searchable": true,
                //     "ordering": false,
                //     "bFilter": true,
                //     "bInfo": true,
                //     "pageLength": 25,
                //     'lengthMenu': [
                //         [10, 20, 25, 50, 100, -1],
                //         [10, 20, 25, 50, 100, 'All']
                //     ],
                //     "serverSide": true,
                //     "processing": true,
                //     "bRetrieve": true,
                //     "oLanguage": {
                //         "sProcessing": '<div class="preloader1" align="center"><font style="font-size: 20px; font-weight: bold; color: green;">Processing...</font></div>'
                //     },
                //     "ajax": {
                //         url: "{{ route('getJnmpData') }}",
                //         type: "post",
                //         data: function(d) {
                //             d.district = district,
                //                 d.urban_code = $('#urban_code').val(),
                //                 d.block = $('#block').val(),
                //                 d.gp_ward = $('#gp_ward').val(),
                //                 d.muncid = $('#muncid').val(),
                //                 d._token = "{{ csrf_token() }}"
                //         },
                //         error: function(jqXHR, textStatus, errorThrown) {
                //             $('#submit_btn').attr('disabled', false);
                //             $('#loadingDiv').hide();
                //             $('.preloader1').hide();
                //             ajax_error(jqXHR, textStatus, errorThrown);
                //         }
                //     },
                //     "initComplete": function() {
                //         $('#loadingDiv').hide();
                //         $("#submit_loader1").hide();
                //         $("#submitting").show();
                //         //console.log('Data rendered successfully');
                //     },
                //     "columns": [{
                //             "data": "ben_fname"
                //         },
                //         {
                //             "data": "father_name"
                //         },
                //         {
                //             "data": "block_ulb_name"
                //         },
                //         {
                //             "data": "gp_ward_name"
                //         },
                //         {
                //             "data": "aadhar_no"
                //         },
                //         {
                //             "data": "mobile_no"
                //         },
                //         {
                //             "data": "action"
                //         }
                //     ],
                //     "buttons": [{
                //             extend: 'pdf',
                //             footer: true,
                //             pageSize: 'A4',
                //             //orientation: 'landscape',
                //             pageMargins: [40, 60, 40, 60],
                //             exportOptions: {
                //                 columns: [0, 1, 2, 3, 4, 5],

                //             }
                //         },
                //         {
                //             extend: 'excel',
                //             footer: true,
                //             pageSize: 'A4',
                //             //orientation: 'landscape',
                //             pageMargins: [40, 60, 40, 60],
                //             exportOptions: {
                //                 columns: [0, 1, 2, 3, 4, 5],
                //                 stripHtml: false,
                //             }
                //         },
                //         // 'pdf'
                //     ],
                // });
            });
            $('#file_stop_payment').change(function() {
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
        $(document).on('click', '#verifySubmit', function() {
            var error_file = '';
            var error_remarks = '';
            var remarks = $('#remarks').val();
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

            // if (remarks != '') {
            //     error_remarks = '';
            //     $('#error_remarks').text(error_remarks);
            //     $('#remarks').removeClass('has-error');
            // } else {
            //     error_remarks = 'Remarks is required.';
            //     $('#error_remarks').text(error_remarks);
            //     $('#remarks').addClass('has-error');
            // }

            if (error_file != '') {
                return false;
            } else {
                // alert('OK');
                $.confirm({
                    type: 'orange',
                    title: 'Confirmation!',
                    content: 'Are you want to active this beneficiary ? <br> <span style="color: black;"><b>Note: After activation this beneficiary will started to get payment.</b></span>',
                    icon: 'fa fa-warning',
                    buttons: {
                        confirm: {
                            text: 'Confirm',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function() {
                                // alert('OK');
                                var beneficiary_Id = $('#pension_id').val();
                                var application_id = $('#ben_application_id').val();
                                // var updateSchemeId = $('#update_scheme_id').val();
                                //   alert(application_id);
                                var remarks = $('#remarks').val();
                                var formData = new FormData();
                                var files = $('#file_stop_payment')[0].files;
                                formData.append('file_stop_payment', files[0]);
                                formData.append('id', beneficiary_Id);
                                formData.append('application_id', application_id);
                                formData.append('remarks', remarks);
                                formData.append('_token', '{{ csrf_token() }}');
                                $('.loadingDivModal').show();
                                $.ajax({
                                    type: 'post',
                                    url: "{{ route('activeJnmpBeneficiary') }}",
                                    data: formData,
                                    dataType: 'json',
                                    processData: false,
                                    contentType: false,
                                    success: function(response) {
                                        $('.loadingDivModal').hide();
                                        if (response.return_status == 1) {
                                            $.alert({
                                                title: response.title,
                                                type: response.type,
                                                icon: response.icon,
                                                content: response.msg
                                            });
                                            $('#modalUpdateAadhar').modal('hide');
                                            $('#res_div').hide();
                                            // $('#scheme_type').val('').trigger('change');
                                            $('#submit_btn').trigger('click');
                                            $("html, body").animate({
                                                scrollTop: 0
                                            }, "slow");
                                        } else {
                                            var html = '';
                                            html += '<ul>';
                                            if (Array.isArray(response.msg)) {
                                                $.each(response.msg, function(key, value) {
                                                    html += '<li>' + value +
                                                        '</li>';
                                                });
                                            } else {
                                                html = '<li>' + response.msg + '</li>';
                                            }
                                            html += '<ul>';
                                            $.alert({
                                                title: response.title,
                                                type: response.type,
                                                icon: response.icon,
                                                content: html
                                            });
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        $('.loadingDivModal').hide();
                                        console.log(textStatus);
                                        console.log(errorThrown);
                                        ajax_error(jqXHR, textStatus, errorThrown);
                                    }
                                });
                            }
                        },
                        cancel: function() {},
                    }
                });
            }
        });

        function viewModalFunction(value) {
            // alert(value);
            $('#loadingDivModal').show();
            $.ajax({
                type: 'POST',
                url: "{{ route('modalDataView') }}",
                data: {
                    id: value,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
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
                error: function(jqXHR, textStatus, errorThrown) {
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
