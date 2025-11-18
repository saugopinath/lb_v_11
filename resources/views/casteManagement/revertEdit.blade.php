<style>
    .active_tab1 {
      background-color: #fff;
      color: #333;
      font-weight: 600;
    }
    .active_tab2 {
      background-color: #f5f5f5;
      color: #333;
      cursor: pointer;
    }
    .inactive_tab1 {
      background-color: #f5f5f5;
      color: #333;
      cursor: not-allowed;
    }

    .has-error {
      border-color: #cc0000;
      background-color: #ffff99;
    }

    .select2 {
      width: 100% !important;
    }

    .select2 .has-error {
      border-color: #cc0000;
      background-color: #ffff99;
    }

    .modal_field_name {
      font-weight: 700;
      margin-bottom: 5px;
      color: #555;
    }

    .modal_field_value {
      padding: 8px;
      background: #f8f9fa;
      border-radius: 4px;
      border: 1px solid #dee2e6;
      margin-bottom: 10px;
    }

    .section1 {
      border: 1.5px solid #9187878c;
      margin: 2%;
      padding: 2%;
    }

    .color1 {
      margin: 0% !important;
      background-color: #5f9ea061;
    }

    .modal-header {
      background-color: #7fffd4;
    }

    .required-field::after {
      content: "*";
      color: red;
    }

    .imageSize {
      font-size: 9px;
      color: #333;
    }
    .btnEnc {
      margin-top:10px;
    }
    
    .card-header-custom {
      font-size: 16px;
      background: linear-gradient(to right, #c9d6ff, #e2e2e2);
      font-weight: bold;
      font-style: italic;
      padding: 15px 20px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
</style>

<?php 
use Illuminate\Support\Facades\Input;
?>

@extends('layouts.app-template-datatable')
@section('content')
<div class="content-fluid">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Caste Information Modification</h1>
                </div>

                 <div class="col-sm-6 text-end">
                    <a href="lb-caste-reverted-list" class="btn btn-outline-secondary">
                      <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                  </div>
                <!-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('lb-caste-reverted-list') }}"><i class="fas fa-arrow-left"></i> Back</a></li>
                    </ol>
                </div> -->
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- Alert Messages -->
                    @if (!empty($beneficiary_id))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>Beneficiary ID: {{$beneficiary_id}}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>{{ $message }} with Application ID: {{$id}}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if ( ($message = Session::get('error')))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{{ $message }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        @foreach ($errors->all() as $error)
                        <strong>{{ $error }}</strong><br />
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <!-- Main Card -->
                    <div class="card">
                        <div class="card-header card-header-custom">
                            <h4 class="card-title mb-0">Caste Information Modification</h4>
                        </div>
                        <div class="card-body">
                            <form name="personal" id="personal" method="post" action="{{url('lb-caste-revert-edit-post')}}" enctype="multipart/form-data" autocomplete="off">
                                {{ csrf_field() }}
                                <input type="hidden" name="application_id" id="application_id" value="{{$row->application_id}}">
                                <input type="hidden" name="beneficiary_id" id="beneficiary_id" value="{{ $beneficiary_id }}">
                                <input type="hidden" name="is_faulty" id="is_faulty" value="{{ $row->is_faulty }}">
                                <input type="hidden" name="old_caste" id="old_caste" value="{{ $row->caste }}">
                                <input type="hidden" name="old_caste_certificate_no" id="old_caste_certificate_no" value="{{ $row->caste_certificate_no }}">
                                <input type="hidden" name="caste_change_type" id="caste_change_type" value="{{ $caste_change_type }}">

                                <!-- Basic Information Section -->
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="form">
                                            <label class="form-label">Is Faulty Application?</label>
                                            <span class="form-control-plaintext text-info">{{$row->is_faulty?'YES':'NO' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form">
                                            <label class="form-label">Name:</label>
                                            <div class="form-control-plaintext text-info">{{$row->ben_fname}}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form">
                                            <label class="form-label">Swasthyasathi Card No:</label>
                                            <div class="form-control-plaintext text-info">{{$row->ss_card_no }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="form">
                                            <label class="form-label">Mobile No.:</label>
                                            <div class="form-control-plaintext text-info">{{$row->mobile_no }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form">
                                            <label class="form-label">Father's Name:</label>
                                            <div class="form-control-plaintext text-info">
                                                {{$row->father_fname }} 
                                                @if(!empty($row->father_mname)) {{$row->father_mname }} @endif 
                                                @if(!empty($row->father_lname)){{$row->father_lname }} @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-1">

                                <!-- Address Information -->
                                <h5 class="mb-2">Address Details</h5>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="modal_field_name">Police Station:</div>
                                        <div class="modal_field_value" id="police_station_modal">{{trim($row_contact->police_station)}}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="modal_field_name">Block/Municipality/Corp:</div>
                                        <div class="modal_field_value" id="block_modal">{{trim($row_contact->block_ulb_name)}}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="modal_field_name">GP/Ward No.:</div>
                                        <div class="modal_field_value" id="gp_ward_modal">{{trim($row_contact->gp_ward_name)}}</div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="modal_field_name">Village/Town/City:</div>
                                        <div class="modal_field_value" id="village_modal">{{trim($row_contact->village_town_city)}}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="modal_field_name">House / Premise No:</div>
                                        <div class="modal_field_value" id="house_modal">{{ trim($row_contact->house_premise_no ?? '') ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="modal_field_name">Pin Code:</div>
                                        <div class="modal_field_value" id="pin_code_modal">{{trim($row_contact->pincode)}}</div>
                                    </div>
                                </div>

                                <hr class="my-1">

                                <!-- Caste Information -->
                                <div class="card-header bg-light py-3">
        <h5 class="card-title mb-0 text-primary">
            <i class="fas fa-info-circle me-2"></i>Caste Details
        </h5>
    </div>
                                <div class="row mb-2 ml-4">
                                    <div class="col-md-4">
                                        <div class="form">
                                            <label class="form-label">Existing Caste:</label>
                                            <div class="form-control-plaintext text-info"><span class="badge bg-warning">{{$row->caste}}</span></div>
                                        </div>
                                    </div>
                                    @if($row->caste=='SC' || $row->caste=='ST')
                                    <div class="col-md-4">
                                        <div class="form">
                                            <label class="form-label">Existing SC/ST Certificate No:</label>
                                            <div class="form-control-plaintext text-info"><span class="badge bg-warning">{{$row->caste_certificate_no }}</span></div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="row mb-2 ml-4">
                                    <div class="col-md-6">
                                        <div class="form">
                                            <label class="form-label">New Caste:</label>
                                            <div class="form-control-plaintext text-info"><span class="badge bg-warning">{{$row_caste->new_caste}}</span></div>
                                        </div>
                                    </div>
                                    @if($row_caste->new_caste=='SC' || $row_caste->new_caste=='ST')
                                    <div class="col-md-6">
                                        <div class="form">
                                            <label class="form-label">New SC/ST Certificate No:</label>
                                            <div class="form-control-plaintext text-info"><span class="badge bg-warning">{{$row_caste->new_caste_certificate_no }}</span></div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Caste Update Form -->
                                <div class="card-header bg-primary text-white py-3">
        <h5 class="card-title mb-0">
            <i class="fas fa-edit me-2"></i>Update Caste Information
        </h5>
    </div>
                                <div class="row mb-2 ml-4 mr-4">
                                    <div class="col-md-6">
                                        <div class="form">
                                            <label for="caste_category" class="form-label required-field">New Caste</label>
                                            <select class="form-select" name="caste_category" id="caste_category">
                                                @foreach($caste_lb as $key=>$val)
                                                <option value="{{$key}}" @if($row_caste->new_caste==$key) selected @endif>{{$val}}</option>
                                                @endforeach 
                                            </select>
                                            <span id="error_caste_category" class="text-danger small"></span>
                                        </div>
                                    </div>
                                    
                                    @if($row_caste->new_caste=='SC' || $row_caste->new_caste=='ST')
                                    <div class="col-md-6 withCaste">
                                        <div class="form">
                                            <label for="caste_certificate_no" class="form-label required-field">New SC/ST Certificate No.</label>
                                            <input type="text" name="caste_certificate_no" id="caste_certificate_no" class="form-control"
                                                placeholder="SC/ST Certificate No." maxlength="200" value="{{ $row_caste->new_caste_certificate_no }}" />
                                            <span id="error_caste_certificate_no" class="text-danger small"></span>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Document Upload Section -->
                                @if($row_caste->new_caste=='SC' || $row_caste->new_caste=='ST')
                                <div class="row mb-2 ml-4 mr-4">
                                    <div class="col-md-12">
                                        <label class="form-label">Enclosure List (Self Attested)</label>
                                    </div>
                                    <div class="row withCaste">
                                        <div class="col-md-6 form">
                                            <label for="doc_3" class="form-label required-field">{{$doc_caste_arr->doc_name}}</label>
                                            <input type="file" name="doc_3" id="doc_3" class="form-control"/>
                                            <span id="error_doc_3" class="text-danger small"></span>
                                            <div class="imageSize">(File type must be {{$doc_caste_arr->doc_type}} and size max {{$doc_caste_arr->doc_size_kb}}KB)</div>
                                            
                                          </div>
                                          <div class="col-md-6">
                                            @if($casteEncloserCount > 0)
                                            <span class="fw-semibold">Uploaded Document</span>
                                            <div class="mt-0">
                                                <a href="javascript:void(0);" id="docDownload_1" class="btn btn-danger btn-sm downloadEncloser btnEnc" 
                                                  onclick="View_encolser_modal('{{$doc_caste_arr->doc_name}}',{{$doc_caste_arr->id}},0)">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                            </div>
                                            @endif
                                          </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Note and Submit Section -->
                                <div class="row mt-1">
                                    <div class="col-md-12">
                                        @if($caste_change_type==2)
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Note: Caste change will be effective only after verification and approval. Payment will be stopped till approval is completed.
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-12 text-center">
                                        <button type="button" name="btn_aplply" id="btn_apply" class="btn btn-success btn-lg">
                                            <i class="fas fa-check me-2"></i>Apply
                                        </button>
                                        <div class="d-none" id="btn_personal_details_loader">
                                            <img src="{{ asset('images/ZKZg.gif')}}" width="150px" height="150px">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="encolserModal" tabindex="-1" role="dialog" aria-labelledby="encolserModalLabel" aria-hidden="true">
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        $('.sidebar-menu li').removeClass('active');
        $('.sidebar-menu #lb-caste').addClass("active"); 
        $('.sidebar-menu #caste-revert').addClass("active"); 

        var base_url = '{{ url('/') }}';

        $("#submitting").hide();
        $("#submit_loader").hide();

        // Caste category change handler
        $("#caste_category").on('change', function(){
            var caste_category = $("#caste_category").val();
            if(caste_category == "SC" || caste_category == "ST" || caste_category == "") {
                $(".withCaste").show(); 
            } else {
                $(".withCaste").hide();
            }
        });

        // Input validation handlers
        $('.txtOnly').keypress(function (e) {
            var regex = new RegExp(/^[a-zA-Z\s]+$/);
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (!regex.test(str)) {
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
            if(isSplChar) {
                var no_spl_char = yourInput.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
                $(this).val(no_spl_char);
            }
        });

        // Apply button click handler
        $('#btn_apply').click(function(){
            $("#errorDiv").hide();
            $("#errorDiv").find("ul").html('');

            var error_caste_category = '';
            var error_caste_certificate_no = "";
            var error_doc_3 = '';  

            // Caste category validation
            if($.trim($('#caste_category').val()).length == 0) {
                error_caste_category = 'Caste is required';
                $('#error_caste_category').text(error_caste_category);
                $('#caste_category').addClass('has-error');
            } else {
                error_caste_category = '';
                $('#error_caste_category').text(error_caste_category);
                $('#caste_category').removeClass('has-error');
            }

            // Caste certificate validation
            if($('#caste_category').val() == 'SC' || $('#caste_category').val() == 'ST' || $('#caste_category').val() == '') { 
                if($.trim($('#caste_certificate_no').val()).length == 0) {
                    error_caste_certificate_no = 'SC/ST Certificate No. is required';
                    $('#error_caste_certificate_no').text(error_caste_certificate_no);
                    $('#caste_certificate_no').addClass('has-error');
                } else {
                    error_caste_certificate_no = '';
                    $('#error_caste_certificate_no').text(error_caste_certificate_no);
                    $('#caste_certificate_no').removeClass('has-error');
                }
            }

            if(error_caste_category != '' || error_caste_certificate_no != '') {
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            } else {
                $(".btn-lg").attr("disabled", true);
                $("#personal").submit();
            }
        });
    });

    // Document viewer function
    function View_encolser_modal(doc_name, doc_type, is_profile_pic) {
        var application_id = $('#personal #application_id').val();
        var is_faulty = $('#personal #is_faulty').val();
        
        $('#encolser_name').html('');
        $('#encolser_content').html('');
        $('#encolser_name').html(doc_name + '(' + application_id + ')');
        $('#encolser_content').html('<img width="50px" height="50px" src="images/ZKZg.gif"/>');

        var url = (is_faulty == 1) ? '{{ url('ajaxGetEncloserFaulty') }}' : '{{ url('ajaxGetEncloser') }}';

        $.ajax({
            url: '{{ url('ajaxModifiedCasteEncolser') }}',
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
            $("#encolserModal").modal('show');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $('#encolser_content').html('');
            alert('Session timeout or server error occurred.');
        });
    }

    // Utility functions
    function printMsg(msg, msgtype, divid) {
        $("#" + divid).find("ul").html('');
        $("#" + divid).css('display', 'block');
        if(msgtype == '0') {
            $("#" + divid).removeClass('alert-success');
            $("#" + divid).addClass('alert-info');
        } else {
            $("#" + divid).removeClass('alert-info');
            $("#" + divid).addClass('alert-success');
        }
        if(Array.isArray(msg)) {
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