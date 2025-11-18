<style>
    .card-header-custom {
        font-size: 16px;
        background: linear-gradient(to right, #c9d6ff, #e2e2e2);
        font-weight: bold;
        font-style: italic;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .modal_field_name {
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    .modal_field_value {
        padding: 4px;
        background: #f8f9fa;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
</style>

@extends('layouts.app-template-datatable')
@section('content')
    <!-- Main content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mt-4">
                <div class="tab-content" style="margin-top:16px;">
                    <!-- Back Button -->
                    <div class="mb-3">
                        <a href="aadhaar-details-update-list-approver" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                    </div>

                    <!-- Alert Messages -->
                    <div class="alert-section">
                        @if (!empty($beneficiary_id))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <strong>Beneficiary ID: {{ $beneficiary_id }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (($message = Session::get('success')) && ($id = Session::get('id')))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>{{ $message }} with Application ID: {{ $id }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($message = Session::get('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>{{ $message }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $error)
                                    <strong>{{ $error }}</strong><br />
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    <!-- Main Content Card -->
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h4 class="card-title mb-0"><b>Aadhaar,Name and DOB Modification</b></h4>
                        </div>
                        <div class="card-body">
                            <form name="personal" id="personal" method="post" action="{{ url('changeAadharPost') }}"
                                enctype="multipart/form-data" autocomplete="off">
                                {{ csrf_field() }}
                                <input type="hidden" name="application_id" id="application_id"
                                    value="{{ $row->application_id }}">
                                <input type="hidden" name="is_faulty" id="is_faulty" value="{{ $row->is_faulty }}">
                                <input type="hidden" name="old_name" id="old_name" value="{{ $row->ben_name }}">
                                <input type="hidden" name="old_dob" id="old_dob" value="{{ $row->dob }}">
                                <input type="hidden" name="old_aadhar" id="old_aadhar"
                                    value="{{ $row->aadhaar_no_decode }}">

                                <!-- Personal Information -->
                                <div class="row mb-2">
                                    <div class="form-group col-md-4">
                                        <label class="">Is Faulty Application?</label>
                                        <span id="" class="text-info">{{ $row->is_faulty ? 'YES' : 'NO' }}</span>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="">Existing Name:</label>
                                        <span id="" class="text-info">{{ trim($row->ben_name) ?: 'N/A' }}</span>
                                    </div>
                                    @if (!is_null($row->dob))
                                        <div class="form-group col-md-4">
                                            <label class="">Existing DOB:</label>
                                            <span id="" class="text-info">{{ trim($row->dob) ?: 'N/A' }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="row mb-4">
                                    <div class="form-group col-md-4">
                                        <label class="">Mobile No.:</label>
                                        <span id="" class="text-info">{{ $row->mobile_no }}</span>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="">Father's Name:</label>
                                        <span id=""
                                            class="text-info">{{ trim($row->father_name) ?: 'N/A' }}</span>
                                    </div>
                                </div>

                                <hr class="my-1">

                                <!-- Address Information -->
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="modal_field_name">Police Station:</div>
                                        <div class="modal_field_value" id="police_station_modal">
                                            {{ trim($row->police_station) ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="modal_field_name">Block/Municipality/Corp:</div>
                                        <div class="modal_field_value" id="block_modal">
                                            {{ trim($row->block_ulb_name) ?: 'N/A' }}
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="modal_field_name">GP/Ward No.:</div>
                                        <div class="modal_field_value" id="gp_ward_modal">
                                            {{ trim($row->gp_ward_name) ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="modal_field_name">Village/Town/City:</div>
                                        <div class="modal_field_value" id="village_modal">
                                            {{ trim($row->village_town_city) }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="modal_field_name">House / Premise No:</div>
                                        <div class="modal_field_value" id="house_modal">
                                            {{ trim($row->house_premise_no) ?: 'N/A' }}</div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="modal_field_name">Pin Code:</div>
                                        <div class="modal_field_value" id="pin_code_modal">
                                            {{ trim($row->pincode) ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-1">

                                <!-- Caste Information -->
                                <div class="row mb-2">
                                    <div class="form-group col-md-4">
                                        <label class="">Existing Aadhaar Number:</label>
                                        <span id=""
                                            class="text-info">{{ trim($row->aadhaar_no_decode) ?: 'N/A' }}</span>
                                    </div>
                                </div>

                                <!-- New Caste Information -->
                                <div class="row mb-2">
                                    <div class="form-group col-md-4">
                                        <label class="required-field">New Name</label>
                                        <input type="text" name="first_name" id="first_name"
                                            class="form-control txtOnly" placeholder="Name" maxlength="200"
                                            value="{{ isset($row->ben_name) ? $row->ben_name : '' }}" tabindex="5" />
                                        <span id="error_first_name" class="text-danger"></span>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="required-field">New Date of Birth</label>
                                        <input type="date" name="dob" id="dob" class="form-control"
                                            tabindex="25" value="{{ isset($row->dob) ? $row->dob : '' }}"
                                            max="{{ $max_dob }}" min="{{ $min_dob }}" />
                                        <!-- <input type="text" id="dob" name="dob"class="form-control" data-inputmask="'alias': 'dd/mm/yyyy'" data-mask placeholder="dd/mm/yyyy"> -->
                                        <span id="error_dob" class="text-danger"></span>
                                    </div>

                                    <div class="form-group col-md-4 withCaste">
                                        <label class="required-field">New Aadhaar Number.</label>
                                        <input type="text" name="aadhar_no" id="aadhar_no"
                                            class="form-control NumOnly" placeholder="Aadhaar No." maxlength="12"
                                            value="{{ isset($row->aadhaar_no_decode) ? $row->aadhaar_no_decode : '' }}" />
                                        <span id="error_aadhar_no" class="text-danger"></span>
                                    </div>
                                </div>

                                <!-- Document Upload -->
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <div class="form">
                                            <label class="form-label"><strong>Enclosure List (Self
                                                    Attested)</strong></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 withCaste">
                                            <div class="form">
                                                <label class="required-field">{{ $doc_aadhaar_arr->doc_name }}</label>
                                                <input type="file" name="doc_6" id="doc_6"
                                                    class="form-control" />
                                                <span id="error_doc_6" class="text-danger"></span>
                                                <div class="imageSize">(File type must be {{ $doc_aadhaar_arr->doc_type }}
                                                    and
                                                    size max {{ $doc_aadhaar_arr->doc_size_kb }}KB)</div>
                                                <!-- <span id="download_" style="">
                                                                                                        &nbsp;&nbsp;<button type="button" id="docDownload_1"  class="btn btn-danger downloadEncloser btnEnc" >Download</button>
                                                                                                        </span> -->
                                                @if ($casteEncloserCount > 0)
                                                    <span id="download_" style="">
                                                        &nbsp;&nbsp;<a href="javascript:void(0);" id="docDownload_1"
                                                            class="btn btn-danger downloadEncloser btnEnc"
                                                            onclick="View_encolser_modal('{{ $doc_aadhaar_arr->doc_name }}',{{ $doc_aadhaar_arr->id }},0)">View</a>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label>{{ $doc_aadhaar1_arr->doc_name }}</label>



                                            <input type="file" name="doc_118" id="doc_118" class="form-control" />
                                            <span id="error_doc_118" class="text-danger"></span>
                                            <div class="imageSize">(File type must be
                                                {{ $doc_aadhaar1_arr->doc_type }}
                                                and size max {{ $doc_aadhaar1_arr->doc_size_kb }}KB)</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Section -->
                                <div class="row mt-2">

                                    <div class="col-md-12 text-center">
                                        <button type="button" name="btn_aplply" id="btn_apply"
                                            class="btn btn-success btn-lg">Update</button>
                                        <img style="display:none;" src="{{ asset('images/ZKZg.gif') }}"
                                            id="btn_personal_details_loader" width="150px" height="150px">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div class="modal fade" id="encolserModal" tabindex="-1" role="dialog" aria-labelledby="encolserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="encolser_name">Document Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="encolser_content">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading document...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ URL::asset('js/validateAdhar.js') }}"></script>
    <script>
        var specialKeys = new Array();
        specialKeys.push(8); //Backspace
        function IsNumeric(e) {
            //alert()
            var keyCode = e.which ? e.which : e.keyCode
            var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
            document.getElementById("error").style.display = ret ? "none" : "inline";
            return ret;
        }

        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #updateAadhaarDetails').addClass("active");




            var base_url = '{{ url('/') }}';


            $("#submitting").hide();
            $("#submit_loader").hide();









            $('.txtOnly').keypress(function(e) {
                var regex = new RegExp(/^[a-zA-Z\s]+$/);
                var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
                if (regex.test(str)) {
                    return true;
                } else {
                    e.preventDefault();
                    return false;
                }
            });


            $(".NumOnly").keyup(function(event) {

                $(this).val($(this).val().replace(/[^\d].+/, ""));
                if ((event.which < 48 || event.which > 57)) {
                    event.preventDefault();
                }
            });



            $('.special-char').keyup(function() {
                var yourInput = $(this).val();
                re = /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
            var isSplChar = re.test(yourInput);
            if (isSplChar) {
                var no_spl_char = yourInput.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
                    $(this).val(no_spl_char);
                }
            });







            $('#btn_apply').click(function() {
                $("#errorDiv").hide();
                $("#errorDiv").find("ul").html('');
                //var error_title ='';

                var error_first_name = '';
                var error_dob = '';
                var error_aadhar_no = "";
                var error_doc_6 = '';
                var error_doc_118 = '';
                var old_aadhar = $('#old_aadhar').val();
                var aadhar_no = $('#aadhar_no').val();
                if ($.trim($('#first_name').val()).length == 0) {
                    error_first_name = 'First Name is required';
                    $('#error_aadhar_no').text(error_first_name);
                    $('#first_name').addClass('has-error');
                } else {
                    error_first_name = '';
                    $('#error_first_name').text(error_first_name);
                    $('#first_name').removeClass('has-error');
                }
                if ($.trim($('#dob').val()).length == 0) {
                    error_dob = 'DOB is required';
                    $('#error_dob').text(error_dob);
                    $('#dob').addClass('has-error');
                } else {
                    error_dob = '';
                    $('#error_dob').text(error_dob);
                    $('#dob').removeClass('has-error');
                }
                if ($.trim($('#aadhar_no').val()).length == 0) {
                    error_aadhar_no = 'Aadhar No is required';
                    $('#error_aadhar_no').text(error_aadhar_no);
                    $('#aadhar_no').addClass('has-error');
                } else {
                    if ($.trim($('#aadhar_no').val()).length != 12) {

                        error_aadhar_no = 'Aadhar No should be 12 digit ';
                        $('#error_aadhar_no').text(error_aadhar_no);
                        $('#aadhar_no').addClass('has-error');
                    } else {
                        var aadhar_no = $('#aadhar_no').val();
                        var aadhar_valid = validate_adhar(aadhar_no);
                        // aadhar_valid=1;
                        if (aadhar_valid) {
                            error_aadhar_no = '';
                            $('#error_aadhar_no').text(error_aadhar_no);
                            $('#aadhar_no').removeClass('has-error');
                        } else {
                            error_aadhar_no = 'Invalid Aadhar No.';
                            $('#error_aadhar_no').text(error_aadhar_no);
                            $('#aadhar_no').addClass('has-error');
                        }
                    }
                }
                if ($.trim($('#doc_6').val()).length == 0) {
                    error_doc_6 = 'This field is required';
                    $('#error_doc_6').text(error_doc_6);
                    $('#doc_6').addClass('has-error');
                } else {
                    error_doc_6 = '';
                    $('#error_doc_6').text(error_doc_6);
                    $('#doc_6').removeClass('has-error');
                }
                if (old_aadhar != aadhar_no) {
                    if ($.trim($('#doc_118').val()).length == 0) {
                        error_doc_118 = 'This field is required';
                        $('#error_doc_118').text(error_doc_118);
                        $('#doc_118').addClass('has-error');
                    } else {
                        error_doc_118 = '';
                        $('#error_doc_118').text(error_doc_118);
                        $('#doc_118').removeClass('has-error');
                    }
                }


                if (error_first_name != '' || error_dob != '' || error_aadhar_no != '' || error_doc_6 !=
                    '' || error_doc_118 != '')

                //if( error_first_name !=''  )
                {
                    $("html, body").animate({
                        scrollTop: 0
                    }, "slow");
                    return false;
                } else {

                    $(".btn-lg").attr("disabled", true);

                    $("#personal").submit();



                    // return true;
                }


            });















            /***************************SD*********************************/
            // $('#btn_submit_preview').click(function(){
            // $(".modal-submit").show();
            // $("#submitting").hide();
            // $("#submit_loader").hide();
            // $("#confirm-submit").modal("show");
            // });
            // $('.encloserModal').click(function(){
            //  $("#encolser_name").html('');
            //  $('#uploadStatus').html('');
            //  $('.progress-bar').html('');
            //  $("#uploadForm #document_type").val('');
            //  $("#uploadForm #is_profile").val('');
            //  $('#btn_encolser_loader').hide();
            //  var label = $(this).parent().find('label').text();
            //  $("#encolser_name").html(label);
            //  var id= $(this).attr("id");
            //  var id_split=id.split('_');
            //  //console.log(id_split);
            //  $("#uploadForm #document_type").val(id_split[1]);
            //  $("#uploadForm #is_profile").val(id_split[2]);
            //  $("#encolser_modal").modal("show");

            // });


            // $("#uploadForm").on('submit', function(e){
            //         $('#submitButton').hide();
            //         $('#btn_encolser_loader').show();

            //         e.preventDefault();
            //         var form = $('#uploadForm')[0];
            //         var formData = new FormData(form);
            //         var add_edit_status=$("#commonfield #add_edit_status").val();
            //        // alert(add_edit_status);
            //         var scheme_id=$("#commonfield #scheme_id").val();
            //         var sws_card_no=$("#commonfield #sws_card_no").val();
            //         var source_id=$("#commonfield #source_id").val();
            //         var source_type=$("#commonfield #source_type").val();
            //         var max_tab_code=$("#commonfield #max_tab_code").val();
            //         var application_id=$("#commonfield #application_id").val();
            //         formData.append('add_edit_status', add_edit_status);
            //         formData.append('scheme_id', scheme_id);
            //         formData.append('sws_card_no', sws_card_no);
            //         formData.append('source_id', source_id);
            //         formData.append('source_type', source_type);
            //         formData.append('max_tab_code', max_tab_code);
            //         formData.append('application_id', application_id);
            //         $.ajax({
            //             xhr: function() {
            //                 var xhr = new window.XMLHttpRequest();
            //                 xhr.upload.addEventListener("progress", function(evt) {
            //                     if (evt.lengthComputable) {
            //                         var percentComplete = ((evt.loaded / evt.total) * 100);
            //                         var percentComplete = Math.ceil(percentComplete);
            //                         $(".progress-bar").width(percentComplete + '%');
            //                         $(".progress-bar").html(percentComplete+'%');
            //                     }
            //                 }, false);
            //                 return xhr;
            //             },
            //             type: 'POST',
            //             dataType: 'json',
            //             url: '{{ url('ajax_faulty_encloser_entry') }}',
            //             data: formData,
            //             contentType: false,
            //             cache: false,
            //             processData:false,
            //             beforeSend: function(){
            //                 $(".progress-bar").width('0%');
            //                 //$('#uploadStatus').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
            //             },
            //              error: function (ex){
            //                 //console.log(ex);
            //                 $('#uploadStatus').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
            //                  $('#btn_encolser_loader').hide();
            //                  $('#submitButton').show();


            //             },
            //             success: function(resp){
            //               //console.log(resp);
            //                 if(resp.return_status==1){
            //                    $("#max_tab_code").val(resp.max_tab_code);
            //                     var id=$("#uploadForm #document_type").val();
            //                     $('#uploadForm')[0].reset();
            //                     $('#download_'+id).show();
            //                     $('#uploadStatus').html('<p style="color:#28A74B;">File has uploaded successfully!</p>');
            //                      //$(".progress-bar").width('0%');

            //                 }else if(resp.return_status==0){
            //                     $('#uploadStatus').html('<p style="color:#EA4335;">'+resp.return_msg+'</p>');
            //                 }
            //                   $('#btn_encolser_loader').hide();
            //                    $('#submitButton').show();


            //             }
            //         });


            //     });


            // $('#encolser_modal').on('hidden.bs.modal', function (e) {
            //   $("#uploadForm #document_type").val('');
            //   $("#uploadForm #is_profile").val('');
            //   $(".progress-bar").html('');

            // });
            // $(".downloadEncloser").click(function(){
            //  var id= $(this).attr("id");
            //  var id_split=id.split('_');  
            //  var application_id=$("#application_id").val();
            //   window.open("downaloadEncloser_faulty?id="+id_split[1]+"&is_profile_pic="+id_split[2]+"&application_id="+application_id);
            // });


            // $('.modal-submit').on('click',function(){

            //         $(".modal-submit").hide();
            //         $("#submitting").show();
            //         $("#submit_loader").show();


            // });




            /***************************************************************/
        });
        $('#district').change(function() {
            if ($(this).val() != '') {
                $('#urban_code').val('');
                $('#block').html('<option value="">--Select--</option>');
                $('#gp_ward').html('<option value="">--Select--</option>');
            }
        });
        $('#urban_code').change(function() {
            if ($(this).val() != '') {
                var district_code = $('#district').val();
                //alert(district_code);
                var rural_urban = $(this).val();
                var error_found = 1;
                if (rural_urban == 2) {
                    error_found = 0;
                    var url = '{{ url('masterDataAjax/getTaluka') }}';
                } else if (rural_urban == 1) {
                    error_found = 0;
                    var url = '{{ url('masterDataAjax/getUrban') }}';
                }

                if (error_found == 0) {
                    //alert('ok');
                    $('#block').val('');
                    $('#gp_ward').val('');
                    $('#error_block').html(
                        '<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: {
                            district_code: district_code,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(data) {
                            //console.log(data);
                            var htmlOption = '<option value="">--Select--</option>';
                            $.each(data, function(key, value) {
                                htmlOption += '<option value="' + key + '">' + value +
                                    '</option>';
                            });
                            $('#block').html(htmlOption);
                            $('#gp_ward').html('<option value="">--Select--</option>');
                            $('#error_block').html('');
                        },
                        error: function(ex) {
                            alert(sessiontimeoutmessage);
                            window.location.href = base_url;
                        }
                    });
                }

            }
        });
        $('#block').change(function() {
            if ($(this).val() != '') {
                var block_code = $(this).val();
                //alert(block_code);
                var rural_urban = $('#urban_code').val();
                var error_found = 1;
                if (rural_urban == 2) {
                    error_found = 0;
                    var url = '{{ url('masterDataAjax/getGp') }}';
                } else if (rural_urban == 1) {
                    error_found = 0;
                    var url = '{{ url('masterDataAjax/getWard') }}';
                }

                if (error_found == 0) {
                    $('#gp_ward').val('');
                    $('#error_gp_ward').html(
                        '<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: {
                            block_code: block_code,
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(data) {
                            // console.log(data);
                            var htmlOption = '<option value="">--Select--</option>';
                            $.each(data, function(key, value) {
                                htmlOption += '<option value="' + key + '">' + value +
                                    '</option>';
                            });
                            $('#gp_ward').html(htmlOption);
                            $('#error_gp_ward').html('');
                        },
                        error: function(ex) {
                            alert(sessiontimeoutmessage);
                            window.location.href = base_url;
                        }
                    });
                }

            }
        });





        function View_encolser_modal(doc_name, doc_type, is_profile_pic) {
            var application_id = $('#personal #application_id').val();
            var is_faulty = $('#personal #is_faulty').val();
            $('#encolser_name').html('');
            $('#encolser_content').html('');
            $('#encolser_name').html(doc_name + '(' + application_id + ')');
            $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
            if (is_faulty == 1) {
                var url = '{{ url('ajaxGetEncloserFaulty') }}';
            } else {
                var url = '{{ url('ajaxGetEncloser') }}';
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

        function printMsg(msg, msgtype, divid) {
            $("#" + divid).find("ul").html('');
            $("#" + divid).css('display', 'block');
            if (msgtype == '0') {
                //alert('error');
                $("#" + divid).removeClass('alert-success');
                //$('.print-error-msg').removeClass('alert-warning');
                $("#" + divid).addClass('alert-info');
            } else {
                $("#" + divid).removeClass('alert-info');
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
@endpush
