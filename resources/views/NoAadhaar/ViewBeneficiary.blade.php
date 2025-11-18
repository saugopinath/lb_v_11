@extends('NoAadhaar.base')
@section('action-content')
<style>
    /* Custom CSS for styling and required field indicator */
    .required-field::after {
        content: " *";
        color: red; 
    }
    .card-footer.actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        padding: 20px;
    }
    .card-outline.inner-card {
        margin-top: 15px;
        border-top: 1px solid #dee2e6;
    }
    .bg-custom-header {
        background-color: #dcdfdf !important; 
        color: #343a40; /* Dark text */
        padding: 8px 15px; 
    }
    .bg-custom-header h3, .bg-custom-header h5 {
        margin: 0 !important;
    }
    .field-value {
        padding-top: 5px;
    }
    .card-body p {
        margin-bottom: 5px;
    }

    /* Hide screen-specific elements on print */
    @media print {
        .example-screen {
            display: none;
        }
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="mb-3 example-screen">
                    <a href="{{ route('noaadharlist')}}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                   {{-- ✔ FIXED SUCCESS ALERT --}}
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Success!</strong> {{ $message }}
                    </div>
                @endif

                {{-- ERROR ALERT --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>

                        <ul>
                            @foreach($errors->all() as $error)
                                <li><strong>{{ $error }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="card card-primary card-outline">
                    <div class="card-header text-center">
                        <h3 class="card-title text-primary font-weight-bold">
                            <i class="fas fa-id-card"></i> Application ID:- {{$application_id}}
                        </h3>
                    </div>

                    <div class="card-body p-0">
                        
                        {{-- Personal Details Section --}}
                        <div class="card card-secondary card-outline mb-0 border-0 rounded-0">
                            <div class="card-header bg-custom-header">
                                <h5 class="card-title"><i class="fas fa-user"></i> Personal Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <p class="field-value"><strong>Name:</strong> {{$row->ben_fname}} {{$row->ben_mname}} {{$row->ben_lname}}</p>
                                    </div>
                                    @if(!is_null($row->dob))
                                    <div class="col-md-6 mb-2">
                                        <p class="field-value"><strong>Date of Birth (DD-MM-YYYY):</strong> {{date('d/m/Y', strtotime($row->dob)) }}</p>
                                    </div>
                                    @endif
                                    <div class="col-md-6 mb-2">
                                        <p class="field-value"><strong>Father's Name:</strong> {{$row->father_fname}} {{$row->father_mname}} {{$row->father_lname}}</p>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <p class="field-value"><strong>Mother's Name:</strong> {{$row->mother_fname}} {{$row->mother_mname}} {{$row->mother_lname}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Existing Aadhaar Details --}}
                        @if(!empty($old_aadhar))
                        <div class="card card-success card-outline inner-card mb-0 border-0 rounded-0">
                            <div class="card-header bg-custom-header">
                                <h5 class="card-title"><i class="fas fa-address-card"></i> Existing Aadhaar Details</h5>
                            </div>
                            <div class="card-body">
                                <p class="field-value"><strong>Aadhaar Number:</strong> {{trim($old_aadhar)}}</p>
                            </div>
                        </div>
                        @endif

                        {{-- New Aadhaar Details (after Verifier update) --}}
                        @if(!empty($new_aadhar))
                        <div class="card card-info card-outline inner-card mb-0 border-0 rounded-0">
                            <div class="card-header bg-custom-header">
                                <h5 class="card-title"><i class="fas fa-address-card"></i> New Aadhaar Details</h5>
                            </div>
                            <div class="card-body">
                                <p class="field-value"><strong>Aadhaar Number:</strong> {{trim($new_aadhar)}}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Approver/Delegated Approver View and Actions --}}
                        @if(($designation_id=='Approver' || $designation_id=='Delegated Approver') && $row->no_aadhar_next_level_role_id==1)
                            <div class="card card-warning card-outline inner-card mb-0 border-0 rounded-0">
                                <div class="card-header bg-custom-header">
                                    <h5 class="card-title"><i class="fas fa-file-alt"></i> Documents: **{{$doc_man->doc_name}}**</h5>
                                </div>
                                <div class="card-body text-center">
                                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'jfif', 'png', 'gif']))
                                        <div class="mb-3">
                                            <a class="example-image-link" data-lightbox="example-1">
                                                <img class="example-image img-thumbnail" src="{{$image}}" alt="Document Image" style="max-width: 100%; height: auto; max-height: 400px;"/>
                                            </a>
                                        </div>
                                    @elseif(strtolower($ext)=='pdf')
                                        <a href="{{ route('noaadharPdfDownload', ['application_id' => $application_id]) }}" target="_blank" class="btn btn-primary btn-lg">
                                            <i class="fas fa-file-pdf"></i> Download PDF Document
                                        </a>
                                        <p class="mt-2 text-muted">Click the button to view/download the attached document.</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card-footer actions">
                                <button type="button" id="confirm" value="Approve" class="btn btn-success btn-lg confirm">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" id="revert" value="Revert" class="btn btn-warning btn-lg confirm">
                                    <i class="fas fa-undo"></i> Back To Verifier
                                </button>
                            </div>
                        @endif

                        {{-- Verifier/Delegated Verifier Form --}}
                        @if($designation_id=='Verifier' || $designation_id=='Delegated Verifier')
                            <div class="card card-success card-outline inner-card mb-0 border-0 rounded-0" id="new_info_div">
                                <div class="card-header bg-custom-header text-black">
                                    <h5 class="card-title"><i class="fas fa-upload"></i> Upload Aadhaar Details</h5>
                                </div>
                                <form method="post" id="register_form" action="{{url('noaadharPost')}}" enctype="multipart/form-data" class="submit-once">
                                    @csrf
                                    <input type="hidden" name="application_id" id="application_id" value="{{$application_id}}"/>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label class="required-field" for="aadhaar_no">Aadhaar Number</label>
                                                <input type="text" name="aadhaar_no" id="aadhaar_no" class="form-control NumOnly" placeholder="Aadhaar No." value="{{trim($decrypt_aadhar_old)}}" maxlength='12'/>
                                                <span id="error_aadhaar_no" class="text-danger"></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label class="required-field" for="doc_{{ $doc_man['id']}}">{{ $doc_man['doc_name'] }}</label>
                                                <input type="file" name="doc_{{ $doc_man['id']}}" id="doc_{{ $doc_man['id'] }}" class="form-control" tabindex="1" />
                                                <small class="form-text text-muted imageSize">(Image type must be {{ $doc_man['doc_type'] }} and image size max {{ $doc_man['doc_size_kb'] }}KB)</small>
                                                <span id="error_doc_{{ $doc_man['id'] }}" class="text-danger"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer actions">
                                        {{-- MODIFIED: Changed type="submit" to type="button" and added ID for JS handler --}}
                                        <button type="button" id="verifier_submit_button" class="btn btn-success btn-lg modal-submit">
                                            <i class="fas fa-paper-plane"></i> Submit
                                        </button>
                                        <button type="button" id="submitting" value="Submit" class="btn btn-danger btn-lg" disabled style="display:none;">
                                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            Submitting please wait
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- Confirmation Modal Definition --}}
<div id="modalConfirm" class="modal fade">
    <form method="post" id="approval_form" action="{{url('BulkApprovenoaadhar')}}" class="submit-once">
        @csrf
        <input type="hidden" name="action_type" id="action_type" value=""/>
        <input type="hidden" id="approvalcheck" name="approvalcheck[]" value="{{$application_id}}">
        <input type="hidden" name="is_faulty" id="is_faulty" value="{{$is_faulty}}"/>
        {{-- NEW FIELD to track if the submission is from the Verifier (1) or Approver (0) --}}
        <input type="hidden" name="is_verifier_submit" id="is_verifier_submit" value="0"/>

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info">
                    <h5 class="modal-title"><i class="fas fa-question-circle"></i> Confirmation</h5>
                    {{-- Close Button --}}
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <h4 class="w-100">Do you really want to <span id="verify_revert_reject" class="font-weight-bold">Approve</span>?</h4> 
                </div>
                <div class="modal-footer justify-content-center">
                    {{-- Cancel Button --}}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="confirm_yes">
                        <i class="fas fa-check"></i> OK
                    </button>
                    <button type="button" id="submittingapprove" value="Submit" class="btn btn-success" disabled style="display:none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Submitting please wait
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')

<script type="text/javascript">
$(document).ready(function(){
    // Existing setup code
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lb-aadhar').addClass("active"); 
    $('.sidebar-menu #noaadharlist').addClass("active"); 
    $("#submitting").hide();
    $("#submittingapprove").hide();
    
    // Numeric-only input filter
    $(".NumOnly").keyup(function(event) {
        $(this).val($(this).val().replace(/[^\d].+/, ""));
        if ((event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    }); 

    // Existing logic for 'acc_validated_aadhar' and 'process_type'
    var acc_validated_aadhar='{{$row->acc_validated_aadhar}}';
    if(acc_validated_aadhar=='-1'){
        $("#new_is_required").val(1);
    }
    else{
        $("#new_is_required").val(0);
    }

    $(document).on('change', '#process_type', function() {
        var processVal = this.value;
        if (processVal == 1) {
            $('#new_info_div').hide();
            $('#aadhaar_no').val('');
            $("#new_is_required").val(0);
        }
        else if(processVal == 2) {
            $('#new_info_div').show();
            $('#remarks').val('');
            $("#new_is_required").val(1);
        }
        else if (processVal == 3) {
            $('#new_info_div').hide();
            $('#aadhaar_no').val('');
            $("#new_is_required").val(0);
        }
        else {
            
        }
    });
    
    // === FIX FOR MODAL DISMISSAL ===
    // Explicitly bind click handlers to ensure the modal closes, even if data-dismiss fails.
    $('#modalConfirm').on('click', '.btn-secondary, .close', function(e) {
        // Only hide the modal if the button clicked has data-dismiss="modal" or is a child of the modal
        if ($(e.target).closest('[data-dismiss="modal"]').length || $(e.target).hasClass('close') || $(e.target).hasClass('btn-secondary')) {
            // Reset loading states before hiding
            $("#confirm_yes").show();
            $("#submittingapprove").hide();
            // Finally, hide the modal
            $('#modalConfirm').modal('hide');
        }
    });
    // ===============================

    // -----------------------------------------------------------
    // Approver/Delegated Approver action (Approve / Back to Verifier)
    // -----------------------------------------------------------
    $('.confirm').click(function(){  
        $("#action_type").val('');
        $("#is_verifier_submit").val(0); // Set flag to 0 for Approver actions
        
        var button_val=$(this).val();
        $('#verify_revert_reject').text(button_val); 
        
        if(button_val=='Approve'){
            $("#action_type").val(1);
        } 
        if(button_val=='Revert'){
            $("#action_type").val(2);
        } 
        $('#modalConfirm').modal('show');
    });

    // -----------------------------------------------------------
    // Verifier/Delegated Verifier action (Submit) - NEW LOGIC
    // -----------------------------------------------------------
    $('#verifier_submit_button').click(function(e) {
        // Reset confirmation button state
        $("#confirm_yes").show();
        $("#submittingapprove").hide();
        
        // Run client-side validation first
        if (client_validation()) {
            // Validation passed. Set up the modal for submission confirmation.
            $('#verify_revert_reject').text('Submit');
            $("#action_type").val(''); 
            $("#is_verifier_submit").val(1); // Set flag to 1 for Verifier submit
            $('#modalConfirm').modal('show');
        }
    });

    // -----------------------------------------------------------
    // Modal Confirmation Handler - MODIFIED LOGIC
    // -----------------------------------------------------------
    $('#confirm_yes').on('click',function(e){
        e.preventDefault(); 
        
        var isVerifier = $("#is_verifier_submit").val();

        // Show loading state on the modal
        $("#confirm_yes").hide();
        $("#submittingapprove").show();

        if (isVerifier == 1) {
            // VERIFIER SUBMISSION: Submit the Verifier's form (#register_form)
            $('#modalConfirm').modal('hide'); // Hide confirmation modal first
            $("#submitting").show(); // Show Verifier's own loading text
            // Note: The form submission is triggered here, and the back-end will handle the redirect/response.
            $("#register_form").submit();
        } else {
            // APPROVER SUBMISSION: Submit the Approver's form (#approval_form)
            $("#approval_form").submit();
        }
    });

});

// -----------------------------------------------------------
// Client Validation Function - MODIFIED (Only returns true/false)
// -----------------------------------------------------------
function client_validation() {

    let error_aadhaar_no = '';
    let error_doc = '';

    // Clear previous errors
    $('#error_aadhaar_no').text('');
    $('#aadhaar_no').removeClass('has-error');
    let doc_id = "{{ $doc_man['id'] }}"; 
    $('#error_doc_' + doc_id).text('');
    $('#doc_' + doc_id).removeClass('has-error');

    let aadhaar_no = $.trim($('#aadhaar_no').val());
    let doc_value = $('#doc_' + doc_id).val();
    
    // Placeholder for validate_adhar if it's not defined elsewhere (You must ensure this function exists)
    if (typeof validate_adhar === 'undefined') {
        var validate_adhar = function() { return true; }; 
    }

    // Aadhaar Number Validation
    if (aadhaar_no === "") {
        error_aadhaar_no = 'Aadhaar Number is required';
        $('#error_aadhaar_no').text(error_aadhaar_no);
        $('#aadhaar_no').addClass('has-error');
    }
    else if (aadhaar_no.length != 12) {
        error_aadhaar_no = 'Aadhaar No should be 12 digits';
        $('#error_aadhaar_no').text(error_aadhaar_no);
        $('#aadhaar_no').addClass('has-error');
    }
    else {
        let aadhar_valid = validate_adhar(aadhaar_no);

        if (!aadhar_valid) {
            error_aadhaar_no = 'Invalid Aadhaar No.';
            $('#error_aadhaar_no').text(error_aadhaar_no);
            $('#aadhaar_no').addClass('has-error');
        }
    }

    // Document Upload Validation
    if (doc_value === "") {
        error_doc = "Copy of New Aadhaar Card is required";
        $('#error_doc_' + doc_id).text(error_doc);
        $('#doc_' + doc_id).addClass('has-error');
    }

    // Final Check
    if (error_aadhaar_no === "" && error_doc === "") {
        return true;
    }

    return false;
}

</script>
@endpush
