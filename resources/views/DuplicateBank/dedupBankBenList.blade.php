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
        opacity: 0.6;
        pointer-events: none;
    }
</style>

@extends('layouts.app-template-datatable_new')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Bank De-Duplicate Pending Beneficiary List
            </h1>

        </section>
        <section class="content">
            <div class="box box-default">
                <div class="box-body">

                    <div class="panel panel-default">
                        <div class="panel-heading"><span id="panel-icon">Filter Here</div>
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
                                <div class="form-group col-md-3">
                                    <label class="">Search For</label>
                                    <select name="search_for" id="search_for" class="form-control"
                                        tabindex="6">
                                        <option value="1">Approval Pending</option>
                                        <option value="2">Verifier Pending</option>
                                        <option value="3">Rejected</option>
                                        <option value="4">Verified & Approved</option>
                                    </select>
                                    <span id="error_search_for" class="text-danger"></span>
                                </div>

                                @if ($district_visible)
                                    <div class="form-group col-md-3">
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
                                    <div class="form-group col-md-3" id="divUrbanCode">
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
                                    <div class="form-group col-md-3" id="divBodyCode">
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

                                <div class="form-group col-md-3" id="municipality_div"
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

                                <div class="form-group col-md-3" id="gp_ward_div"
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

                                <div class="form-group col-md-3">
                                    <label class="control-label">&nbsp;</label><br />
                                    <button type="button" name="filter" id="filter" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <button type="button" name="excel_btn" id="excel_btn" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export to Excel</button>
                                    {{-- <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button> --}}
                                    
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading" id="panel_head">List of Bank De-Duplication Beneficiaries</div>
                        <div class="panel-body" style="padding: 5px; font-size: 14px;">
                            {{-- <div id="loadingDiv">
              </div> --}}
                            <div class="table-responsive">
                                <table id="example" class="display" cellspacing="0" width="100%">
                                    <thead style="font-size: 12px;">
                                        <th>Beneficiary ID</th>
                                        <th>Beneficiary Name</th>
                                        <th>Block/ Municipality</th>
                                        <th>GP/Ward</th>
                                        <th>Old Account No.</th>
                                        <th>Old IFSC</th>
                                        <th>New Account No.</th>
                                        <th>New IFSC</th>
                                        <th>Mobile No</th>
                                        <th>Process Type</th>
                                    </thead>
                                    <tbody style="font-size: 14px;"></tbody>
                                </table>
                            </div>
                        </div>
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
            // $('#loadingDiv').hide();
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #bankTrFailed').addClass("active");
            $('.sidebar-menu #accValTrFailedApproved').addClass("active");

            $('#excel_btn').click(function() {
                var token = "{{ csrf_token() }}";
                var search_for = $('#search_for').val();
                var rural_urban = $('#urban_code').val();
                var block_ulb_code = $('#block').val();
                var gp_ward_code = $('#gp_ward').val();
                var muncid = $('#muncid').val();

                var data = {
                    '_token': token,
                    'search_for': search_for,
                    'rural_urban': rural_urban,
                    'block_ulb_code': block_ulb_code,
                    'gp_ward_code': gp_ward_code,
                    'muncid': muncid,
                };
                redirectPost('getBankDeduplicationListexcel', data);
            });



            $('.content').addClass('disabledcontent');
            var dataTable = "";
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            dataTable = $('#example').DataTable({
                dom: 'Blfrtip',
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
                ajax: {
                    url: "{{ route('getDeduplicationList') }}",
                    type: "POST",
                    data: function(d) {
                            d.search_for = $('#search_for').val(),
                            d.rural_urban = $('#urban_code').val(),
                            d.block_ulb_code = $('#block').val(),
                            d.gp_ward_code = $('#gp_ward').val(),
                            d.muncid = $('#muncid').val(),
                            d._token = "{{ csrf_token() }}"
                    },

                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#loadingDiv').hide();
                        $('.content').removeClass('disabledcontent');
                        ajax_error(jqXHR, textStatus, errorThrown)
                    }
                },
                "initComplete": function() {
                    //console.log('Data rendered successfully');
                    $('.content').removeClass('disabledcontent');
                    $('#loadingDiv').hide();
                },
                "columns": [
                    {
                        "data": "ben_id"
                    },
                    {
                        "data": "ben_name"
                    },
                    {
                        "data": "block_name"
                    },
                    {
                        "data": "gram_panchyat_name"
                    },
                    {
                        "data": "last_accno"
                    },
                    {
                        "data": "last_ifsc"
                    },
                    {
                        "data": "new_last_accno"
                    },
                    {
                        "data": "new_last_ifsc"
                    },
                    {
                        "data": "mobile_no"
                    },
                    {
                        "data": "status"
                    },
                ],
                //    "columnDefs": [
                //         {
                //             "targets": [ 4,5 ],
                //             "visible": false,
                //             "searchable": true
                //         },
                //         {
                //   "targets": [ 7 ],
                //   "orderable": false,
                //   "searchable": true
                // }
                //    ],
                "buttons": [{
                        extend: 'pdf',

                        title: "Account Approved  Report Generated On-@php
                            date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                            echo $date;
                        @endphp ",
                        messageTop: "Date: @php
                            date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                            echo $date;
                        @endphp ",
                        footer: true,
                        orientation: 'landscape',
                        // pageSize : 'LEGAL',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6],

                        }
                    },
                    {
                        extend: 'excel',

                        title: "Account Approved  Report Generated On-@php
                            date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                            echo $date;
                        @endphp ",
                        messageTop: "Date: @php
                            date_default_timezone_set('Asia/Kolkata');
                            $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                            $date = $date->format('F j, Y g:i:a');
                            echo $date;
                        @endphp",
                        footer: true,
                        pageSize: 'A4',
                        //orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {

                            columns: [0, 1, 2, 3, 4, 5, 6],

                            stripHtml: true,
                        }
                    },

                ],
            });
            // --------------- Filter Section -------------------- //
            $('#filter').click(function() {
                if ($('#filter_1').val() == '') {
                    $.alert({
                        title: "Alert!!",
                        content: "Please Select Filter Criteria"
                    });
                } else {
                    dataTable.ajax.reload();
                }
            });

            $('#reset').click(function() {
                $('#filter_1').val('').trigger('change');
                $('#filter_2').val('').trigger('change');
                $('#block_ulb_code').val('').trigger('change');
                $('#gp_ward_code').val('').trigger('change');
                dataTable.ajax.reload();
            });
            // ------------ Master DropDown Section Start-------------------- //
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
            // ------------ Master DropDown Section End-------------------- // 

        });
    </script>
@stop
