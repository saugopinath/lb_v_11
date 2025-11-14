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

    .disabledcontent {
        opacity: 0.4;
        pointer-events: none;
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
</style>

@extends('layouts.app-template-datatable_new')
@section('content')
<style>
    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }
</style>    
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Approve Legacy Validation Name Edited
            </h1>

        </section>
        <section class="content">
            <div class="box box-default">
                <div class="box-body">
                    <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

                    <div class="panel panel-default">
                        <div class="panel-heading">Beneficiary Details Yet To Be Approved</div>
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
                                {{-- <div class="form-group col-md-4">
                                    <label class="control-label" id="edited_txt">Edited Type by Verifier <span
                                            class="text-danger">*</span></label>
                                    <select name="update_code" id="update_code" class="form-control">
                                        <option value="">-----Select----</option>
                                        <option value="41">Bank name may be taken as beneficiary name as bank name is correct</option>
                                        <option value="42">Passbook Correction Required</option>
                                        <option value="43">Bank Account is of other Family Members, New Account Number required</option>
                                        <option value="44">Bank account is of completely of other person out of family. New Account Number required</option>
                                    </select>
                                </div> --}}
                                <div class="form-group col-md-2">
                                    <label class="control-label">Rural/Urban </label>
                                    <select name="filter_1" id="filter_1" class="form-control">
                                        <option value="">-----Select----</option>
                                        @foreach ($levels as $key => $value)
                                            <option value="{{ $key }}"> {{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label" id="blk_sub_txt">Block/Sub Division </label>
                                    <select name="filter_2" id="filter_2" class="form-control">
                                        <option value="">-----Select----</option>
                                    </select>
                                </div>

                                {{-- <div class="col-md-2">
                  <label class="control-label">Failed Type </label>
                  <select name="failed_type" id="failed_type" class="form-control">
                    <option value="">-----All----</option>
                    @foreach (Config::get('globalconstants.failed_type') as $key => $val)
                      <option value="{{ $key}}">{{$val}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="control-label">Payment Mode </label>
                  <select name="pay_mode" id="pay_mode" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach (Config::get('globalconstants.pmt_mode') as $key => $val)
                      <option value="{{ $key}}">{{$val}}</option>
                    @endforeach
                  </select>
                </div> --}}
                                {{-- <div class="form-group col-md-2" id="municipality_div" style="display:none;">
                  <label class="control-label">Municipality</label>
                  <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                    <option value="">-----All----</option>
                  </select>
                </div>
                <div class="form-group col-md-3" style="display:none;" id="gp_ward_div">
                  <label class=" control-label" id="gp_ward_txt">GP/Ward</label>
                  <select name="gp_ward_code" id="gp_ward_code" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div> --}}
                                <div class="form-group col-md-3" style="margin-top: 24px;">
                                    <button type="button" name="filter" id="filter" class="btn btn-success"><i
                                            class="fa fa-search"></i> Search</button>&nbsp;&nbsp;
                                    <button type="button" name="reset" id="reset" class="btn btn-warning"><i
                                            class="fa fa-refresh"></i> Reset</button>
                                </div>
                            </div>
                            <hr />
                            <div class="row">
                                <div class="form-group col-md-offset-4 col-md-3 " style="display: none;"
                                    id="approve_rejdiv">
                                    <button type="button" name="bulk_approve" class="btn btn-success btn-lg"
                                        id="bulk_approve" value="approve">
                                        Approve</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default" id="res_div" style="display: none;">
                        <div class="panel-heading" id="panel_head">List of New Edited Naming Information</div>
                        <div class="panel-body" style="padding: 5px; font-size: 14px;">
                            <div class="table-responsive">
                                <table id="example" class="display" cellspacing="0" width="100%">
                                    <thead style="font-size: 12px;">
                                        <th>Sl No</th>
                                        <th>Beneficiary ID</th>
                                        <th>Applicant Name</th>
                                        <th>Block/Sub-Division</th>
                                        <th>Failed Type</th>
                                        <th>Action</th>
                                    </thead>
                                    <tbody style="font-size: 14px;"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Modal -->
    <div class="modal fade ben_bank_modal" id="" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Name Validation Failed</h4>
                </div>
                <div class="modal-body">
                    <div id="loadingDivModal"></div>
                    <div class="panel panel-default">
                        <!-- <div class="panel-heading">Update Mobile No. and Bank Details</div> -->
                        <div class="panel-body">
                            <table class="table table-bordered table-striped table-condensed" style="font-size: 14px;">
                                <tbody>
                                    <tr>
                                        <th scope="row">Beneficiary Id</th>
                                        <td id="ben_id_text"></td>
                                        <th scope="row">Application Id</th>
                                        <td id="app_id_text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Beneficiary Name</th>
                                        <td id="ben_name_text"></td>
                                        <th scope="row">Swasthya Sathi Card</th>
                                        <td id="ss_cardno"></td>
                                    </tr>
                                    {{-- <tr>
                                        <th scope="row">Gender</th>
                                        <td id="gender_text"></td>
                                        <th scope="row">Date of Birth :(DD-MM-YYYY)</th>
                                        <td id="dob_text"></td>
                                    </tr> --}}
                                    <tr>
                                        <th scope="row">Caste</th>
                                        <td id="caste_text"></td>
                                        <th scope="row">Mobile No</th>
                                        <td id="mobile_no_text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Block/ Municipality</th>
                                        <td id="block_ulb_name_text"></td>
                                        <th scope="row">GP/ Ward</th>
                                        <td id="gp_ward_name_text"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Bank A/c No.</th>
                                        <td id="bank_account_number_text"></td>
                                        <th scope="row">Bank IFSC</th>
                                        <td id="bank_ifsc_text"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <input type="hidden" id="benId" name="benId" value="">
                            <input type="hidden" id="faildTableId" name="faildTableId" value="">
                            <input type="hidden" id="nameStatusCode" name="nameStatusCode" value="">
                            <input type="hidden" id="application_id" name="application_id" value="">
                            <input type="hidden" id="old_bank_ifsc" name="old_bank_ifsc" value="">
                            <input type="hidden" id="old_bank_accno" name="old_bank_accno" value="">
                            <input type="hidden" name="verify_otp_no" id="verify_otp_no" value="" />
                            <!-- Showing Reason Section -->
                            {{-- <div class="text-danger" style="font-size: 16px; text-align: center;">Failed Reason :- <span id="failed_reason" style=" font-weight: bold;"></span></div> --}}
                            <div style="font-size: 16px; text-align: center; ">Name Response from Bank :- <span
                                    id="name_response" class="text-success"></span></div>

                            <p style="font-size: 12px; font-weight: bold; text-align:center; display: none;">All (<span
                                    style="color:firebrick"> * </span>) marks filled are mandatory</p>
                            {{-- <div style="padding: 5px 5px 5px 50px; border: 1px solid whitesmoke; border-radius: 5px; margin: 5px 0px; background-color: whitesmoke;"
                                class="row">
                                <label style="cursor: pointer; margin-bottom: 5px;">
                                    <input type="radio" name="process_type" id="process_type" value="1"> Minor
                                    mismatch, Keep existing bank information
                                </label><br />
                                <label style="cursor: pointer; margin-bottom: 5px;">
                                    <input type="radio" name="process_type" id="process_type" value="2"> Process
                                    with new bank information
                                </label><br />
                                <label style="cursor: pointer; margin-bottom: 5px;">
                                    <input type="radio" name="process_type" id="process_type" value="3">
                                    Application is rejected due to major mismatch
                                </label>
                            </div> --}}
                            {{-- <div id="radio_btn_confirm" style="font-size: 14px; font-weight: bold; font-style: italic;"
                                class="text-warning" align="center">Please select which one do you want to process ?</div> --}}
                            <table class="table table-bordered table-responsive" style="width:100%;">
                                <tbody>
                                    {{-- <tr class="bankInfoHideShow" style="display: none;">
                                        <th scope="row" class="required" style="font-size: 14px;">Bank Branch Name
                                        </th>
                                        <td id="branch_text"><input type="text" value="" name="branch_name"
                                                id="branch_name" readonly>
                                            <span style="font-size: 14px;" id="error_bank_branch"
                                                class="text-danger"></span>
                                        </td>
                                        <th scope="row" class="required" style="font-size: 14px;">Bank IFSC Code</th>
                                        <td id="bank_ifsc_text"><input type="text" value="" name="bank_ifsc"
                                                onkeyup="this.value = this.value.toUpperCase();" id="bank_ifsc">
                                            <img src="{{ asset('images/ajaxgif.gif') }}" width="60px" id="ifsc_loader"
                                                style="display: none;">
                                            <span style="font-size: 14px;" id="error_bank_ifsc_code"
                                                class="text-danger"></span>
                                        </td>
                                    </tr>
                                    <tr class="bankInfoHideShow" style="display: none;">
                                        <th scope="row" class="required" style="font-size: 14px;">Bank Name</th>
                                        <td id="bank_text"><input type="text" value="" name="bank_name"
                                                maxlength="200" id="bank_name" readonly>
                                            <span style="font-size: 14px;" id="error_name_of_bank"
                                                class="text-danger"></span>
                                        </td>
                                        <th scope="row" class="required" style="font-size: 14px;">Bank Account Number
                                        </th>
                                        <td id="bank_acc_text"> <input type="text" value=""
                                                name="bank_account_number" maxlength='20' id="bank_account_number">
                                            <span style="font-size: 14px;" id="error_bank_account_number"
                                                class="text-danger"></span>
                                        </td>
                                    </tr> --}}
                                    <!-- Document Update Section -->
                                    <tr class="documentSectionHideShow">
                                        <th scope="row" class="required" style="font-size: 14px;"><span
                                                id="upload_text">Upload Bank Passbook</span></th>
                                        <td id="bank_passbook_text">
                                            <input type="file" name="upload_bank_passbook"
                                                accept=".jpg,.jpeg,.png,.pdf" id="upload_bank_passbook" value="">
                                            <span style="font-size: 14px;" id="error_file" class="text-danger"></span>
                                        </td>
                                        <th scope="row" style="font-size: 14px;">Copy Of Passbook</th>
                                        <td scope="row" class="encView">&nbsp;&nbsp;&nbsp;<a
                                                class="btn btn-xs btn-primary" href="javascript:void(0);"
                                                onclick="View_encolser_modal('Copy of Bank Pass book','10',1)">View</a>
                                        </td>
                                    </tr>
                                    <tr class="remarkSectionHideShow">
                                        <th scope="row" class="required" style="font-size: 14px;">Remarks</th>
                                        <td colspan="3">
                                            <input type="text" name="remarks" maxlength="100" id="remarks" class="form-control" value="" style="border-radius: 3px; border: 1px solid #737373;">
                                            <span style="font-size: 14px;" id="error_remarks" class="text-danger"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- <div class="row" id="otp_div" style="display: none;">
                                <span class="text-warning" style="font-weight: bold; padding: 15px;">For
                                    Rejection you have to enter your Login OTP for verification.</span><br>
                                <div class="form-group col-md-4">
                                    <input type="text" name="otp" id="otp" maxlength="6"
                                        placeholder="Enter OTP" class="form-control">
                                    <span class="text-danger" id="error_otp"></span>
                                </div>
                                <div class="form-group col-md-4">
                                    <button class="btn btn-primary" id="otp_verify" name="otp_verify">Verify</button>
                                    <span style="margin-left: 10px; font-weight: bold;" id="verification_result"
                                        class="" style="font-weight: bold;"></span>
                                </div>
                            </div> --}}
                            <div class="row" id="finalUpdatediv">
                                <div class="col-md-12" style="text-align: center;">
                                    <input type="button" name="submit" value="Approve" id="" class="btn btn-info btn-lg verifySubmit">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->

        </section>
    </div>

@endsection
@section('script')
    <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #bankTrFailed').addClass("active");
            $('.sidebar-menu #nameValTrFailedVerified').addClass("active");
            $('#opreation_type').val('A');
            $("#verifyReject").html("Approve");
            $('#div_rejection').hide();
            $('#loadingDivModal').hide();
            // $('.content').addClass('disabledcontent');
            $('.content').removeClass('disabledcontent');
            // ------------------- Load Datatable Data ------------------------ //
            // function loadDatatable() {
            var dataTable = "";
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            var dataTable = $('#example').DataTable({
                dom: 'Blfrtip',
                paging: true,
                pageLength: 20,
                lengthMenu: [
                    [20, 30],
                    [20, 30]
                ],
                // lengthMenu: [[20, 50,100,500,1000, -1], [20, 50,100,500,1000, 'All']],
                processing: true,
                serverSide: true,
                "oLanguage": {
                    "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
                },
                ajax: {
                    url: "{{ url('minorMismatchNameFailedList') }}",
                    type: "POST",
                    data: function(d) {
                        d.filter_1 = $('#filter_1').val(),
                        d.filter_2 = $('#filter_2').val(),
                        d._token = "{{ csrf_token() }}"
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                        
                        $('.content').removeClass('disabledcontent');
                        $('.preloader1').hide();
                        $('#res_div').hide();
                        // ajax_error(jqXHR, textStatus, errorThrown);
                        $.alert({
                            title: 'Error!!',
                            type: 'red',
                            icon: 'fa fa-warning',
                            content: 'Something wrong when loading table!!',
                        });
                    }
                },
                "initComplete": function() {
                    $('.content').removeClass('disabledcontent');
                    var data = dataTable.rows().data();
                    if (data.length == 0) {
                        $('#res_div').hide();
                    } else {
                        $('#res_div').show();
                    }
                    // alert( 'The table has '+data.length+' records' );
                    //console.log('Data rendered successfully');
                },
                columns: [
                    {
                        "data": "DT_RowIndex"
                    },
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
                        "data": "type"
                    },
                    {
                        "data": "view"
                    },
                ],
                "columnDefs": [{
                    "targets": [0],
                    "orderable": false,
                    "searchable": false
                }],

                "buttons": [
                ],
            });
            // }
            $('#example').on('page.dt', function() {
                $('#approve_rejdiv').hide();
            });
            // ------------------- Load Datatable Data End ------------------------ //

            // ------------------- View Button Click Section -----------------------//
            $(document).on('click', '.ben_view_button', function() {
                var processType = $("#update_code").val();
                var benid = $(this).val();
                // alert(benid);
                
                $('#loader_img_personal').show();
                $('#loadingDivModal').show();
                $('#fullForm #application_id').val('');
                $('#fullForm #applicantId').val('');
                $('.ben_view_button').attr('disabled', true);
                
                $('#fullForm #application_id').val(benid);
                $("#fullForm #is_bulk").val(0);
                $('#opreation_type').val('A').trigger('change');
                $("#verifyReject").html("Approve");
                $('#div_rejection').hide();
                $(".singleInfo").show();
                $('.applicant_id_modal').html('');
                $('#accept_reject_comments').val('');
                $("#collapseBank").collapse('hide');
                $('#collapsePersonal').collapse('show');
                $('.ben_view_body').addClass('disabledcontent');
                $('#verification_result').removeAttr('class');
                $('#verification_result').html('');
                $('#otp').val('');
                $.ajax({
                    type: 'post',
                    url: "{{ route('editMinorMismatchNameFailed') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        benid: benid
                    },
                    dataType: 'json',
                    success: function(response) {
                        // console.log(JSON.stringify(response));
                        $("#panel_bank_name_text").text(response.paneltext);
                        $('.ben_view_body').removeClass('disabledcontent');
                        $("#collapseBank").collapse('show');
                        $('#loader_img_personal').hide();
                        $('#loadingDivModal').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        $('#ben_id_text').text(response.personaldata[0].ben_id);
                        $('#app_id_text').text(response.personaldata[0].application_id);
                        $('#ss_cardno').text(response.personaldata[0].ss_card_no);
                        $('#name_response').text(response.personaldata[0].name_response);
                        $('#ben_name_text').text(response.personaldata[0].ben_name);
                        $('#mobile_no_text').text(response.personaldata[0].mobile_no);
                        $('#gender').text(response.personaldata[0].gender);
                        $('#dob').text(response.personaldata[0].dob);
                        $('#ben_age').text(response.personaldata[0].age_ason_01012021);
                        $('#caste_text').text(response.personaldata[0].caste);
                        if (response.personaldata[0].caste == 'SC' || response.personaldata[0].caste == 'ST') {
                            $('#caste_certificate_no').text(response.personaldata[0]
                                .caste_certificate_no);
                            $('.caste').show();
                        } else {
                            $('.caste').hide();
                        }
                        $('#block_ulb_name_text').text(response.personaldata[0].block_name);  
                        $('#gp_ward_name_text').text(response.personaldata[0].gp_name);
                        $('#bank_ifsc_text').text(response.personaldata[0].last_ifsc);
                        $('#bank_account_number_text').text(response.personaldata[0].last_accno);  


                        $('.applicant_id_modal').html('(Beneficiary ID - ' + response
                            .personaldata[0].beneficiary_id + ' , Application ID - ' +
                            response.personaldata[0].application_id + ')');
                        $('#benId').val(response.personaldata[0].ben_id);
                    },
                    complete: function() {

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('.ben_view_body').removeClass('disabledcontent');
                        $('#loader_img_personal').hide();
                        $('#loadingDivModal').hide();
                        $('.ben_view_button').removeAttr('disabled', true);
                        $('.ben_view_modal').modal('hide');
                        // ajax_error(jqXHR, textStatus, errorThrown);
                        $.alert({
                            title: 'Error!!',
                            type: 'red',
                            icon: 'fa fa-warning',
                            content: 'Something wrong while fetching the beneficiary data!!',
                        });
                    }
                });
                $('.ben_bank_modal').modal('show');

            });
            // ------------------- View Button Click Section End -----------------------//

            // ------------------- Final Approve Section -----------------------//
            $(document).on('click', '.verifySubmit', function(){
                var error_remarks = '';
                var error_file = '';
                
                if ($.trim($('#remarks').val()).length == 0) {
                    error_remarks = 'Please add some remarks';
                    $('#error_remarks').text(error_remarks);
                    $('#remarks').addClass('has-error');
                } else {
                    error_remarks = '';
                    $('#error_remarks').text(error_remarks);
                    $('#remarks').removeClass('has-error');
                }

                if ($('#upload_bank_passbook')[0].files.length == 0) {
                    error_file = 'Please upload required document';
                    $('#error_file').text(error_file);
                    $('#upload_bank_passbook').addClass('has-error');
                } else {
                    error_file = '';
                    $('#error_file').text(error_file);
                    $('#upload_bank_passbook').removeClass('has-error');
                }
                if (error_file != '' || error_remarks != '') {
                    return false;
                } else {
                    $.confirm({
                        title: 'Confirmation!',
                        type: 'orange',
                        icon: 'fa fa-check',
                        content: 'Are you sure want to process ?',
                        buttons: {
                            confirm: {
                                text: 'confirm',
                                btnClass: 'btn-blue',
                                keys: ['enter', 'shift'],
                                action: function() {
                                    var upload_bank_passbook = $('#upload_bank_passbook')[0]
                                        .files;
                                    // var bank_name = $('#bank_name').val();
                                    // var branch_name = $('#branch_name').val();
                                    var remarks = $('#remarks').val();
                                    var benId = $('#benId').val();
                                    // var application_id = $('#application_id').val();
                                    // var faildTableId = $('#faildTableId').val();
                                    // var nameStatusCode = $('#nameStatusCode').val();
                                    var token = '{{ csrf_token() }}';
                                    var fd = new FormData();
                                    fd.append('benId', benId);
                                    // fd.append('bank_ifsc', bank_ifsc);
                                    // fd.append('bank_name', bank_name);
                                    // fd.append('bank_account_number', bank_account_number);
                                    // fd.append('branch_name', branch_name);
                                    fd.append('upload_bank_passbook', upload_bank_passbook[0]);
                                    fd.append('_token', token);
                                    // fd.append('old_bank_ifsc', old_bank_ifsc);
                                    // fd.append('old_bank_accno', old_bank_accno);
                                    fd.append('remarks', remarks);
                                    // fd.append('application_id', application_id);
                                    // fd.append('process_type', process_type);
                                    // fd.append('faildTableId', faildTableId);
                                    // fd.append('nameStatusCode', nameStatusCode);
                                    // fd.append('otp_login', login_otp_no);
                                    $('#loadingDivModal').show();
                                    $('.verifySubmit').attr('disabled', true);
                                    $.ajax({
                                        type: 'post',
                                        url: "{{ route('approveMinorMismatchNameFailed') }}",
                                        data: fd,
                                        processData: false,
                                        contentType: false,
                                        dataType: 'json',
                                        success: function(response) {
                                            $('#loadingDivModal').hide();
                                            $('.verifySubmit').removeAttr(
                                                'disabled', true);
                                            $('.ben_bank_modal').modal('hide');
                                            dataTable.ajax.reload();
                                            $.confirm({
                                                title: response.title,
                                                type: response.type,
                                                icon: response.icon,
                                                content: response.msg,
                                                buttons: {
                                                    Ok: function() {
                                                        // $('.verifySubmit').removeAttr('disabled',true);
                                                        // $('.ben_bank_modal').modal('hide');
                                                        // dataTable.ajax.reload();
                                                        $("html, body")
                                                            .animate({
                                                                    scrollTop: 0
                                                                },
                                                                "slow");
                                                    }
                                                }
                                            });
                                        },
                                        complete: function() {
                                            //  $('.verifySubmit').removeAttr('disabled',true);
                                        },
                                        error: function(jqXHR, textStatus,
                                            errorThrown) {
                                            $('.verifySubmit').removeAttr(
                                                'disabled', true);
                                            $('#loadingDivModal').hide();
                                            ajax_error(jqXHR, textStatus,
                                                errorThrown)
                                        }
                                    });
                                }
                            },
                            cancel: function() {

                            }
                        }
                    });
                }

            });
            // ------------------- Final Approve Section End -----------------------//

            

            // --------------- Filter Section -------------------- //
            $('#filter').click(function() {
                var update_t = $('#update_code').val();
                if (update_t == '') {
                    alert('Please select edited type!!');
                    $('#update_code').focus();
                } else {
                    if (update_t == 13) {
                        $('#check_all_btn').attr('disabled', true);
                    } else {
                        $('#check_all_btn').attr('disabled', false);
                    }
                    $('#res_div').show();
                    $('#approve_rejdiv').hide();
                    // loadDatatable();
                    dataTable.ajax.reload();
                }
            });

            $('#reset').click(function() {
                $('#filter_1').val('').trigger('change');
                $('#filter_2').val('').trigger('change');
                $('#block_ulb_code').val('').trigger('change');
                $('#gp_ward_code').val('').trigger('change');
                $('#failed_type').val('').trigger('change');
                $('#pay_mode').val('').trigger('change');
                $('#update_code').val('').trigger('change');
                dataTable.ajax.reload();
                // loadDatatable();
            });
            // --------------- Filter Section End-------------------- //

            // ------------ Master DropDown Section Start-------------------- //
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
                    // alert('Please Select Rural/Urban First');
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
            // ------------ Master DropDown Section End-------------------- //



        });

        function View_encolser_modal(doc_name, doc_type, is_profile_pic) {
            var application_id = $('#application_id').val();
            var benId = $('#benId').val();
            $('#encolser_name').html('');
            $('#encolser_content').html('');
            $('#encolser_name').html(doc_name + '(' + benId + ')');
            $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
            $('#loadingDivModal').show();
            $('.verifySubmit').attr('disabled', true);
            $.ajax({
                url: "{{ route('failedNameAjaxViewPassbook') }}",
                type: "POST",
                data: {
                    doc_type: doc_type,
                    is_profile_pic: is_profile_pic,
                    application_id: application_id,
                    _token: '{{ csrf_token() }}',
                },
            }).done(function(data, textStatus, jqXHR) {
                $('.verifySubmit').removeAttr('disabled', true);
                $('#loadingDivModal').hide();
                $('#encolser_content').html('');
                $('#encolser_content').html(data);
                $("#encolser_modal").modal();
            }).fail(function(jqXHR, textStatus, errorThrown) {
                $('#encolser_content').html('');
                $('.verifySubmit').removeAttr('disabled', true);
                $('#loadingDivModal').hide();
                ajax_error(jqXHR, textStatus, errorThrown)
            });
        }
    </script>
@stop
