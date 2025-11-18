<style type="text/css">
    .full-width {
        width: 100% !important;
    }

    .bg-blue {
        background-image: linear-gradient(to right top, #0073b7, #0086c0, #0097c5, #00a8c6, #00b8c4) !important;
    }

    .bg-red {
        background-image: linear-gradient(to right bottom, #dd4b39, #ec6f65, #d21a13, #de0d0b, #f3060d) !important;
    }

    .bg-yellow {
        background-image: linear-gradient(to right bottom, #dd4b39, #e65f31, #ed7328, #f1881e, #f39c12) !important;
    }

    .bg-green {
        background-image: linear-gradient(to right bottom, #04736d, #008f73, #00ab6a, #00c44f, #5ddc0c) !important;
    }

    .bg-verify {
        background-image: linear-gradient(to right top, #f39c12, #f8b005, #fac400, #fad902, #f8ee15) !important;
    }

    .info-box {
        display: block;
        min-height: 90px;
        background: #b6d0ca33 !important;
        width: 100%;
        box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, 0.30) !important;
        border-radius: 2px;
        margin-bottom: 15px;
    }

    .small-box .icon {
        margin-top: 7%;
    }

    .small-box>.inner {
        padding: 10px;
        color: white;
    }

    .small-box p {
        font-size: 18px !important;
    }

    .select2 .select2-container {}

    .link-button {
        background: none;
        border: none;
        color: blue;
        text-decoration: underline;
        cursor: pointer;
        font-size: 1em;
        font-family: serif;
    }

    .link-button:focus {
        outline: none;
    }

    .link-button:active {
        color: red;
    }

    .small-box-footer-custom {
        position: relative;
        text-align: center;
        padding: 3px 0;
        color: #fff;
        color: rgba(255, 255, 255, 0.8);
        display: block;
        z-index: 10;
        background: rgba(0, 0, 0, 0.1);
        text-decoration: none;
        font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-weight: 400;
        width: 100%;
    }

    .small-box-footer-custom:hover {
        color: #fff;
        background: rgba(0, 0, 0, 0.15);
    }

    th.sorting::after,
    th.sorting_asc::after,
    th.sorting_desc::after {
        content: "" !important;
    }

    .errorField {
        border-color: #990000;
    }

    .searchPosition {
        margin: 70px;
    }

    .submitPosition {
        margin: 25px 0px 0px 0px;
    }


    .typeahead {
        border: 2px solid #FFF;
        border-radius: 4px;
        padding: 8px 12px;
        max-width: 300px;
        min-width: 290px;
        background: rgba(66, 52, 52, 0.5);
        color: #FFF;
    }

    .tt-menu {
        width: 300px;
    }

    ul.typeahead {
        margin: 0px;
        padding: 10px 0px;
    }

    ul.typeahead.dropdown-menu li a {
        padding: 10px !important;
        border-bottom: #CCC 1px solid;
        color: #FFF;
    }

    ul.typeahead.dropdown-menu li:last-child a {
        border-bottom: 0px !important;
    }

    .bgcolor {
        max-width: 550px;
        min-width: 290px;
        max-height: 340px;
        background: url("world-contries.jpg") no-repeat center center;
        padding: 100px 10px 130px;
        border-radius: 4px;
        text-align: center;
        margin: 10px;
    }

    .demo-label {
        font-size: 1.5em;
        color: #686868;
        font-weight: 500;
        color: #FFF;
    }

    .dropdown-menu>.active>a,
    .dropdown-menu>.active>a:focus,
    .dropdown-menu>.active>a:hover {
        text-decoration: none;
        background-color: #1f3f41;
        outline: 0;
    }

    table.dataTable thead {
        padding-right: 20px;
    }

    table.dataTable thead>tr>th {
        padding-right: 20px;
    }

    table.dataTable thead th {
        padding: 10px 18px 10px 18px;
        white-space: nowrap;
        border-right: 1px solid #dddddd;
    }

    table.dataTable tfoot th {
        padding: 10px 18px 10px 18px;
        white-space: nowrap;
        border-right: 1px solid #dddddd;
    }

    table.dataTable tbody td {
        padding: 10px 18px 10px 18px;
        border-right: 1px solid #dddddd;
        white-space: nowrap;
        -webkit-box-sizing: content-box;
        -moz-box-sizing: content-box;
        box-sizing: content-box;
    }

    .criteria1 {
        text-transform: uppercase;
        font-weight: bold;
    }

    .item_header {
        font-weight: bold;
    }

    #example_length {
        margin-left: 40%;
        margin-top: 2px;
    }

    @keyframes spinner {
        to {
            transform: rotate(360deg);
        }
    }

    .spinner:before {
        content: '';
        box-sizing: border-box;
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin-top: -10px;
        margin-left: -10px;
        border-radius: 50%;
        border: 2px solid #ccc;
        border-top-color: #333;
        animation: spinner .6s linear infinite;
    }

    .required-field::after {
        content: "*";
        color: red;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        #ben_view_modal #ben_view_modal * {
            visibility: visible;
        }

        #ben_view_modal {
            position: absolute;
            left: 0;
            top: 0;
        }

        [class*="col-md-"] {
            float: none;
            display: table-cell;
        }

        [class*="col-lg-"] {
            float: none;
            display: table-cell;
        }

        .pagebreak {
            page-break-before: always;
        }
    }
</style>

@extends('pensionreport.base1')
@section('action-content')

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-8">

                    </div>
                </div>
            </div>
            <div class="box-body">
                @if (count($errors) > 0)
                    <div class="alert alert-danger alert-block">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li><strong> {{ $error }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
              
                <div id="example2_wrapper" class="col-md-12 dataTables_wrapper form-inline dt-bootstrap js-report-form">


                    
                    <!-- <div class="col-md-offset-1 col-md-5 btn-group" role="group" >
        <button class="btn btn-success clsbulk_approve" id="bulk_approve" disabled>Approve Selected Beneficiaries</button>
       </div> -->
                    <div class="col-md-12 text-center" id="loaderdiv" hidden>
                        <img src="{{ asset('images/ZKZg.gif') }}" width="100px" height="100px" />
                    </div>

                    <div class="tab-pane active" id="search_details" style="margin-top: 40px;">
                        <div class="panel panel-default">
                            <div class="panel-heading" id="heading_msg">
                                <h4><b>Search Result</b></h4>
                            </div>
                            <div class="panel-body">

                                <div class="pull-right" id="report_generation_text">Report
                                    Generated on:<b><?php echo date('l jS \of F Y h:i:s A'); ?></b></div>

                                {{-- <button class="btn btn-info exportToExcel" type="button">Export
                                    to Excel</button><br /><br /><br /> --}}
                                <div id="divScrool">
                                    <table id="example"
                                        class="table table-striped table-bordered table2excel"
                                        style="width:100%">
                                        <thead style="font-size: 12px;">
                                            <th>Application ID</th>
                                            <th>Name</th>
                                            <th>Father Name</th>
                                            <th>Block/Municipality</th>
                                            <th>GP/Ward</th>
                                            <th>Mobile No.</th>
                                            <th>Action</th>
                                        </thead>
                                        <tbody style="font-size: 14px;">
                                            @foreach($row as $benDetails)
                                                <td>{{ $benDetails->application_id }}</td>
                                                <td>{{ $benDetails->name }}</td>
                                                <td>{{ $benDetails->father_name }}</td>
                                                <td>{{ $benDetails->block_ulb_name }}</td>
                                                <td>{{ $benDetails->gp_ward_name }}</td>
                                                <td>{{ $benDetails->mobile_no }}</td>
                                                @if((($benDetails->created_by_local_body_code == $blockCode) || ($benDetails->created_by_local_body_code == $subDiv)) && $benDetails->ds_mark_ix == 0)
                                                    <td><button class="btn btn-warning" id="duareSarkarMarkBtn">Mark As Duare Sarkar IX</button></td>
                                                @endif
                                                @if($benDetails->ds_phase == 11)
                                                    <td><b><span style="font-size: 14px;" class="text text-success">Already Marked as Duare Sarkar IX</span></b></td>
                                                @endif
                                            @endforeach
                                        </tbody>
                                        <tfoot style="font-size: 12px;">
                                            <th>Application ID</th>
                                            <th>Name</th>
                                            <th>Father Name</th>
                                            <th>Block/Municipality</th>
                                            <th>GP/Ward</th>
                                            <th>Mobile No.</th>
                                            <th>Action</th>
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
                <!--   </div> -->
    </section>
    <!-- /.content -->
    </div>

    <!-- Start Duare Sarkar Mark Model -->

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
                                <h4 style="text-align: center;" class="text-primary">Application ID:{{$row[0]->application_id}} <span
                                        id="application_id"></span></h4>
                            </div>
                        </div>
                        <table class="table table-bordered table-responsive table-condensed table-striped"
                            style="font-size: 14px;">
                            <tr>
                                <td>
                                    <strong>Name: </strong>{{$row[0]->name}}
                                </td>
                                <td>
                                    <strong>Father Name: </strong>{{$row[0]->father_name}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Block/Municipality : </strong>{{$row[0]->block_ulb_name}}
                                </td>
                                <td>
                                    <strong>GP/Ward: </strong>{{$row[0]->gp_ward_name}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Mobile NO.: </strong>{{$row[0]->mobile_no}}
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
                                    <th>Marking Date: <span class="text-danger">*</span></th>
                                    <td>
                                        <input type="date" name="marking_date" id="marking_date" class="form-control">
                                        <span class="text-danger" id="error_marking_date"
                                            style="font-size: 12px; font-weight: bold;"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Duare Sarkar Registration No.: <span class="text-danger">*</span></th>
                                    <td>
                                        <input type="text" name="ds_registration_no" id="ds_registration_no" class="form-control"
                                            value="">
                                        <span class="text-danger" id="error_ds_registration_no"
                                            style="font-size: 12px; font-weight: bold;"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="row">
                            <form method="POST" action="#" target="_blank" name="fullForm" id="fullForm" style="text-align: center; align-content: center;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" id="app_id" name="app_id" value="{{$row[0]->application_id}}">
                                <button type="button" class="btn btn-info btn-lg" id="verifyReject">Mark DS IX</button>
                                <button style="display:none;" type="button" id="submitting" value="Submit" class="btn btn-success success" disabled>Processing Please Wait</button>
                            </form>
                        </div>
                        <!-- </div> -->
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- End Duare Sarkar Mark Model -->

    <!-- Start Revert Model -->

    
    <!-- End Revert Model -->


@endsection




<script src='{{ asset('/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}'></script>
<script src="{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script>
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
    function display_c() {
        var refresh = 1000; // Refresh rate in milli seconds
        mytime = setTimeout('display_ct()', refresh)
    }

    function display_ct() {
        var x = new Date()
        document.getElementById('ct').innerHTML = x.toUTCString();
        display_c();
    }

    $(document).ready(function() {
        var application_type = '{{ $type }}';
       
        
        var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
        var base_url = '{{ url('/') }}';

        display_ct();

        $(".dataTables_scrollHeadInner").css({
            "width": "100%"
        });

        $(".table ").css({
            "width": "100%"
        });

        $('#rural_urbanid').change(function() {
            var rural_urbanid = $(this).val();
            if (rural_urbanid != '') {
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $('#gp_ward_code').html('<option value="">--All --</option>');

                select_district_code = $('#district_code').val();
                //console.log(select_district_code);
                var htmlOption = '<option value="">--All--</option>';
                if (rural_urbanid == 1) {
                    $("#municipality_div").show();
                    $("#blk_sub_txt").text('Subdivision');
                    $("#gp_ward_txt").text('Ward');
                    $.each(subDistricts, function(key, value) {
                        if ((value.district_code == select_district_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                } else if (rural_urbanid == 2) {
                    $("#municipality_div").hide();
                    $("#blk_sub_txt").text('Block');
                    $("#gp_ward_txt").text('GP');
                    $.each(blocks, function(key, value) {
                        if ((value.district_code == select_district_code)) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                }
                $('#urban_body_code').html(htmlOption);
            } else {
                $("#blk_sub_txt").text('Block/Subdivision');
                $("#gp_ward_txt").text('GP/Ward');
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $('#gp_ward_code').html('<option value="">--All --</option>');
            }
        });
        $('#urban_body_code').change(function() {
            var rural_urbanid = $('#rural_urbanid').val();
            if (rural_urbanid == 1) {
                var sub_district_code = $(this).val();

                $('#block_ulb_code').html('<option value="">--All --</option>');
                select_district_code = $('#district_code').val();
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
            } else if (rural_urbanid == 2) {
                $('#muncid').html('<option value="">--All --</option>');
                $("#municipality_div").hide();
                var block_code = $(this).val();
                select_district_code = $('#district_code').val();

                var htmlOption = '<option value="">--All--</option>';
                $.each(gps, function(key, value) {
                    if ((value.district_code == select_district_code) && (value.block_code ==
                            block_code)) {
                        htmlOption += '<option value="' + value.id + '">' + value.text +
                            '</option>';
                    }
                });
                //console.log(htmlOption);
                $('#gp_ward_code').html(htmlOption);

                $("#gp_ward_div").show();
            } else {
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $('#gp_ward_code').html('<option value="">--All --</option>');
                $("#municipality_div").hide();
                $("#gp_ward_div").hide();
            }


        });

        $('#block_ulb_code').change(function() {
            var muncid = $(this).val();
            var district = $("#district_code").val();
            var urban_code = $("#rural_urbanid").val();
            if (district == '') {
                $('#rural_urbanid').val('');
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                alert('Please Select District First');
                $("#district_code").focus();

            }
            if (urban_code == '') {
                alert('Please Select Rural/Urban First');
                $('#urban_body_code').html('<option value="">--All --</option>');
                $('#block_ulb_code').html('<option value="">--All --</option>');
                $("#urban_body_code").focus();
            }
            if (muncid != '') {
                var rural_urbanid = $('#rural_urbanid').val();
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

                } else {
                    $('#gp_ward_code').html('<option value="">--All --</option>');
                    $("#gp_ward_div").hide();
                }
            } else {
                $('#gp_ward_code').html('<option value="">--All --</option>');
            }

        });

        $("#duareSarkarMarkBtn").click(function() {
            $("#modalUpdateAadhar").modal('show');
        });

        $("#verifyReject").click(function(){
            var error_marking_date ='';
            var error_ds_registration_no = '';
            var entryForm_url = "{{route('lb-entry-draft-edit')}}";
            var application_id = $('#app_id').val();
            var ds_registration_no = $('#ds_registration_no').val();
            var marking_date = $('#marking_date').val();
            // alert(application_id);
            if($.trim($('#marking_date').val()).length == 0){
                error_marking_date = 'Please Enter Marking Date';
                $('#error_marking_date').text(error_marking_date);
                $('#marking_date').addClass('has-error');
            }
            else{
                error_marking_date = '';
                $('#error_marking_date').text(error_marking_date);
                $('#marking_date').removeClass('has-error');
            }
            if($.trim($('#ds_registration_no').val()).length == 0){
                error_ds_registration_no = 'Please Enter Duare Sarkar Registration No.';
                $('#error_ds_registration_no').text(error_ds_registration_no);
                $('#ds_registration_no').addClass('has-error');
            }
            else{
                error_ds_registration_no = '';
                $('#error_ds_registration_no').text(error_ds_registration_no);
                $('#ds_registration_no').removeClass('has-error');
            }
            if (error_ds_registration_no != '' || error_ds_registration_no != '') {
                return false;
            } else {
                $.ajax({
                            type: 'POST',
                            url: "{{ route('markDuareSarkarPost') }}",
                            data: {
                                ds_registration_no: ds_registration_no,
                                application_id: application_id,
                                marking_date: marking_date,
                                _token: '{{ csrf_token() }}',
                            },
                            success: function (data) {
                            // console.log(data);
                            console.log(JSON.stringify(data));
                            if(data.status==1){
                                $('#modalUpdateAadhar').modal('hide');
                                alert(data.msg);
                                window.location.href = entryForm_url;
                            }
                            else{
                                $("#submitting").hide();
                                $("#verifyReject").show();
                                $('#modalUpdateAadhar').modal('hide');
                                alert(sessiontimeoutmessage);
                                window.location.href=base_url;
                            }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                $("#submitting").hide();
                                $("#verifyReject").show();
                                $('#modalUpdateAadhar').modal('hide');
                                alert(sessiontimeoutmessage);
                                window.location.href=base_url;
                            }           
                        });
                // $.confirm({
                //     title: 'Warning',
                //     type: 'orange',
                //     icon: 'fa fa-warning',
                //     content: '<strong>Are you sure to proceed?</strong>',
                //     buttons: {
                //         Ok: function(){
                //         $("#submitting").show();
                //         $("#verifyReject").hide();
                //         // var ds_reg_no = $('#ds_no').val();
                //         // var application_id = $('#application_id').val();
                        
                //         },
                //         Cancel: function () {
            
                //         },
                //     }
                // }); 
            }
        });

        $('#filter').click(function() {
            table.clear().draw();
            table.ajax.reload();

        });
        $('#reset').click(function() {
            window.location.href = 'application-list-common?type={{ $type }}';
        });
        // var table = $('#example').DataTable({
        //             dom: 'Blfrtip',
        //             "scrollX": true,
        //             "paging": true,
        //             "searchable": true,
        //             "ordering": false,
        //             "bFilter": true,
        //             "bInfo": true,
        //             "pageLength": 25,
        //             'lengthMenu': [
        //                 [10, 20, 25, 50, 100, -1],
        //                 [10, 20, 25, 50, 100, 'All']
        //             ],
        //             "serverSide": true,
        //             "processing": true,
        //             "bRetrieve": true,
        //             "oLanguage": {
        //                 "sProcessing": '<div class="preloader1" align="center"><font style="font-size: 20px; font-weight: bold; color: green;">Processing...</font></div>'
        //             },
        //             "ajax": {
        //                 url: "{{ route('mark-duare-sarkar') }}",
        //                 type: "post",
        //                 data: function(d) {
        //                         // d.dup_mobile_no = $('#dup_mobile_no').val(),
        //                         // d.dup_bank_code = $('#dup_bank_code').val(),
        //                         d.gp_ward = $('#gp_ward_code').val(),
        //                         d.muncid = $('#block_ulb_code').val(),
        //                         // d.dup_type = $('#dup_type').val(),
        //                         // d.ds_phase_mark = $('#ds_phase_mark').val(),
        //                         d._token = "{{ csrf_token() }}"
        //                 },
        //                 error: function(jqXHR, textStatus, errorThrown) {
        //                     $('#submit_btn').attr('disabled', false);
        //                     $('#loadingDiv').hide();
        //                     $('.preloader1').hide();
        //                     // ajax_error(jqXHR, textStatus, errorThrown);
        //                 }
        //             },
        //             "initComplete": function() {
        //                 $('#loadingDiv').hide();
        //                 $("#submit_loader1").hide();
        //                 $("#submitting").show();
        //                 //console.log('Data rendered successfully');
        //             },
        //             "columns": [
        //                 {
        //                     "data": "application_id"
        //                 },
        //                 {
        //                     "data": "ben_fname"
        //                 },
        //                 {
        //                     "data": "father_name"
        //                 },
        //                 {
        //                     "data": "block_ulb_name"
        //                 },
        //                 {
        //                     "data": "gp_ward_name"
        //                 },
        //                 {
        //                     "data": "mobile_no"
        //                 },
        //                 {
        //                     "data": "action"
        //                 }
        //             ],
        //             "buttons": [{
        //                     extend: 'pdf',
        //                     footer: true,
        //                     pageSize: 'A4',
        //                     //orientation: 'landscape',
        //                     pageMargins: [40, 60, 40, 60],
        //                     exportOptions: {
        //                         columns: [0, 1, 2, 3, 4, 5],

        //                     }
        //                 },
        //                 {
        //                     extend: 'excel',
        //                     footer: true,
        //                     pageSize: 'A4',
        //                     //orientation: 'landscape',
        //                     pageMargins: [40, 60, 40, 60],
        //                     exportOptions: {
        //                         columns: [0, 1, 2, 3, 4, 5],
        //                         stripHtml: false,
        //                     }
        //                 },
        //                 // 'pdf'
        //             ],
        //         });
        // var table = $('#example').DataTable({
        //     dom: "Blfrtip",
        //     "paging": true,
        //     "pageLength": 20,
        //     "lengthMenu": [
        //         [20, 50, 80, 120, 150],
        //         [20, 50, 80, 120, 150]
        //     ],
        //     "serverSide": true,
        //     "deferRender": true,
        //     "processing": true,
        //     "bRetrieve": true,
        //     "scrollX": true,
        //     "ordering": false,
        //     "language": {
        //         "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
        //     },
        //     "ajax": {
        //         url: "{{ url('application-list-common') }}",
        //         type: "POST",
        //         data: function(d) {
        //             d.ds_phase = $('#ds_phase').val(),
        //                 d.district_code = "{{ $district_code }}",
        //                 d.rural_urbanid = $('#rural_urbanid').val(),
        //                 d.urban_body_code = $('#urban_body_code').val(),
        //                 d.block_ulb_code = $('#block_ulb_code').val(),
        //                 d.gp_ward_code = $('#gp_ward_code').val(),
        //                 d.caste = $('#caste_category').val(),
        //                 d._token = "{{ csrf_token() }}",
        //                 d.scheme = "{{ $scheme }}",
        //                 d.type = "{{ $type }}"
        //         },
        //         error: function(ex) {
        //             //alert(sessiontimeoutmessage);
        //             // window.location.href=base_url;  
        //             //console.log(ex);
        //         }
        //     },
        //     "columns": [{
        //             "data": "mobile_no"
        //         },
        //         {
        //             "data": "duare_sarkar_registration_no"
        //         },
        //         @if ($type == 'A')
        //             {
        //                 "data": "beneficiary_id"
        //             },
        //         @endif {
        //             "data": "application_id"
        //         },
        //         {
        //             "data": "ben_fname"
        //         },
        //         {
        //             "data": "father_name"
        //         },
        //         {
        //             "data": "ben_age"
        //         },
        //         {
        //             "data": "ss_card_no"
        //         },
        //         @if ($type == 'F')
        //             {
        //                 "data": "enter_by_mobile_no"
        //             }, {
        //                 "data": "faulty_reason"
        //             }, {
        //                 "data": "mobile_no"
        //             },
        //         @endif
        //         @if ($type == 'R')
        //             {
        //                 "data": "mobile_no"
        //             }, {
        //                 "data": "enter_by_mobile_no"
        //             }, {
        //                 "data": "rejected_by"
        //             }, {
        //                 "data": "rejected_reason"
        //             },
        //         @endif
        //         @if ($type == 'T')
        //             {
        //                 "data": "mobile_no"
        //             }, {
        //                 "data": "enter_by_mobile_no"
        //             }, {
        //                 "data": "rejected_reason"
        //             },
        //         @endif
        //         @if ($type == 'PEL')
        //             {
        //                 "data": "mobile_no"
        //             }, {
        //                 "data": "enter_by_mobile_no"
        //             },
        //         @endif {
        //             "data": "action"
        //         }
        //     ],
        //     "columnDefs": [{
        //         "targets": [0, 1],
        //         "visible": false,
        //         "searchable": true
        //     }, ],
        //     "buttons": [{
        //             extend: 'pdf',
        //             exportOptions: {
        //                 columns: ':visible:not(:last-child)'
        //             },
        //             title: "{{ $report_type_name }}",
        //             messageTop: function() {
        //                 var message = 'Report Renerated on: <?php echo date('l jS \of F Y h:i:s A'); ?>';
        //                 return message;
        //             },
        //             footer: true,
        //             pageSize: 'A4',
        //             orientation: 'portrait',
        //             pageMargins: [40, 60, 40, 60],
        //         },
        //         {
        //             extend: 'excel',
        //             exportOptions: {
        //                 columns: ':visible:not(:last-child)',
        //                 format: {
        //                     body: function(data, row, column, node) {
        //                         return column === 4 ? "\0" + data : data;
        //                     }
        //                 }
        //             },
        //             title: "{{ $report_type_name }}",
        //             messageTop: function() {
        //                 var message = 'Report Renerated on: <?php echo date('l jS \of F Y h:i:s A'); ?>';
        //                 return message;
        //             },
        //             footer: true,
        //             pageSize: 'A4',
        //             //orientation: 'landscape',
        //             pageMargins: [40, 60, 40, 60],
        //         },
        //         {
        //             extend: 'print',
        //             exportOptions: {
        //                 columns: ':visible:not(:last-child)'
        //             },
        //             title: "{{ $report_type_name }}",
        //             messageTop: function() {
        //                 var message = 'Report Renerated on: <?php echo date('l jS \of F Y h:i:s A'); ?>';
        //                 return message;
        //             },
        //             footer: true,
        //             pageSize: 'A4',
        //             //orientation: 'landscape',
        //             pageMargins: [40, 60, 40, 60],
        //         },
        //     ],
        // });
        $('#excel').click(function() {
            $('#formexcel').append('<input type="hidden" name="ds_phase" id="ds_phase" value=' + $(
                '#ds_phase').val() + '>');
            $('#formexcel').append('<input type="hidden"  name="rural_urban" id="rural_urban" value=' +
                $('#rural_urbanid').val() + '>');
            $('#formexcel').append(
                '<input type="hidden"  name="urban_block_code_app" id="urban_block_code_app" value=' +
                $('#urban_body_code').val() + '>');
            $('#formexcel').append(
                '<input type="hidden"  name="municipality_code" id="municipality_code" value=' + $(
                    '#block_ulb_code').val() + '>');
            $('#formexcel').append(
                '<input type="hidden" name="gp_ward_code_app" id="gp_ward_code_app" value=' + $(
                    '#gp_ward_code').val() + '>');
            $('#formexcel').submit();
        });
    });
</script>
