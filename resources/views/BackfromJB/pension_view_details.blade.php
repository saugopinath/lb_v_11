@extends('BackfromJB.base')

@section('action-content')
<style>
/* Custom styling */
* {
    font-size: 15px;
}

.field-name {
    font-weight: 600;
    font-size: 17px;
}

.required-field::after {
    content: "*";
    color: red;
}

.has-error {
    border-color: #cc0000;
    background-color: #ffff99;
}

.section1 {
    border: 1.5px solid #d1d1d1;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.color1 {
    background-color: #e0e0e0;
    padding: 10px;
    margin-bottom: 10px;
}

.modal-header {
    background-color: #7fffd4;
}
</style>

<section class="content">
    <div class="container-fluid">

        {{-- Alerts --}}
        @foreach (['message', 'success', 'error'] as $msg)
            @if(Session::get($msg))
                <div class="alert alert-{{ $msg == 'error' ? 'danger' : 'success' }} alert-dismissible fade show">
                    <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
                    <strong>{{ Session::get($msg) }}</strong>
                </div>
            @endif
        @endforeach

       @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
            <ul>
                @foreach ($errors->all() as $error)
                    <li><strong>{{ $error }}</strong></li>
                @endforeach
            </ul>
        </div>
       @endif

        {{-- Card: Application Details --}}
        <div class="card card-primary card-outline">
            <div class="card-header text-center">
                <h3 class="card-title">Application ID: <span class="text-danger">{{ $row->application_id }}</span></h3>
                <div class="card-tools">
                    <a href="{{ route('backfromjb') }}" class="btn btn-tool">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- Personal Details --}}
                <div class="section1">
                    <div class="color1"><h5>Personal Details</h5></div>
                    <div class="row">
                        <div class="col-md-6"><strong>Name:</strong> {{ $row->ben_fname }} {{ $row->ben_mname }} {{ $row->ben_lname }}</div>
                        <div class="col-md-6"><strong>Father's Name:</strong> {{ $row->father_fname }} {{ $row->father_mname }} {{ $row->father_lname }}</div>
                        <div class="col-md-6"><strong>Mother's Name:</strong> {{ $row->mother_fname }} {{ $row->mother_mname }} {{ $row->mother_lname }}</div>
                        <div class="col-md-6"><strong>Mobile Number:</strong> {{ $row->mobile_no ?? '' }}</div>
                        <div class="col-md-6"><strong>Email Id:</strong> {{ $row->email ?? '' }}</div>
                    </div>
                </div>

                {{-- Document / Image --}}
                @if(!empty($image))
                    <div class="section1">
                        <div class="color1"><h5>{{ $doc_age_dob->doc_name }}</h5></div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a data-lightbox="example-1">
                                    <img src="{{ $image }}" class="img-fluid" width="250" height="380" alt="Document Image">
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- DOB Details --}}
                <div class="section1">
                    <div class="color1"><h5>Date of Birth Details</h5></div>
                    <div class="row">
                        @if(!is_null($row->dob))
                            <div class="col-md-6"><strong>Current DOB (DD-MM-YYYY):</strong> {{ date('d/m/Y', strtotime($row->dob)) }}</div>
                        @endif
                        @if(!is_null($row->jb_dob))
                            <div class="col-md-6"><strong>Proposed DOB from JB (DD-MM-YYYY):</strong> {{ date('d/m/Y', strtotime($row->jb_dob)) }}</div>
                        @endif
                        @if(is_null($row->next_level_role_id_dob))
                            <div class="col-md-6 mt-2">
                                <label class="required-field">New Date of Birth</label>
                                <input type="date" name="dob" id="dob" class="form-control" value="{{ $row->jb_dob }}" max="{{ $max_dob }}" min="{{ $min_dob }}">
                                <span id="error_dob" class="text-danger"></span>
                            </div>
                        @elseif($row->next_level_role_id_dob == $next_level_role_id)
                            <div class="col-md-6 mt-2">
                                <label class="required-field">New Date of Birth</label>
                                <span class="form-control">{{ date('d/m/Y', strtotime($row->new_dob)) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="row text-center mt-3">
                    @if(is_null($row->next_level_role_id_dob))
                        <div class="col-md-3">
                            <button type="button" value="Verify and Forward to Approver" class="btn btn-success btn-lg btn-action">Verify & Forward</button>
                        </div>
                    @endif
                    @if($row->next_level_role_id_dob == $next_level_role_id)
                        <div class="col-md-3">
                            <button type="button" value="Approve" class="btn btn-success btn-lg btn-action">Approve</button>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</section>

{{-- Forward Modal --}}
<form method="post" id="commonfield" action="{{ route('forward-backfromjb') }}">
    @csrf
    <input type="hidden" name="application_id" value="{{ $row->application_id }}">
    <input type="hidden" name="action_type" id="action_type" value="">
    <input type="hidden" name="is_faulty" value="{{ $is_faulty }}">
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">
                    <p>Are you sure you want to <b><span id="action_txt"></span></b> the application with ID <span id="id_txt" class="text-info"></span>?</p>
                    <input type="text" name="comments" id="comments" class="form-control" placeholder="Comments" />
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="modal-submit">OK</button>
                    <button type="button" class="btn btn-success" id="submitting" disabled>Submitting... Please wait</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
</form>

{{-- Scripts --}}
@push('scripts')
<script type="text/javascript">
$(document).ready(function(){
  $("#submitting").hide();
  $("#action_type").val('');
  $("#action_txt").text('');
  $("#id_txt").text('');
  $('.btn-action').click(function(){  
    $("#action_type").val('');
    $("#action_type").val($(this).val());
    //alert($("#action_type").val());
    $("#action_txt").text($(this).val());
    $("#id_txt").text($("#application_id").val());
    
    $('#myModal').modal('show');
});
$('#modal-submit').on('click',function(){
 var action_type= $("#action_type").val();
 var error_nsap_rhs_id='';
 var error_nsap_member_id='';
 var form = $('#commonfield')[0];
 var formData = new FormData(form);
$("#commonfield").append('<input type="hidden" id="dob" name="dob" value="'+$("#dob").val()+'" /> ');

  //console.log('ok1');
   $("#modal-submit").hide();
   $("#submitting").show();
   $("#submit_loader").show();
   $("#register_form").submit();



});
$(".NumOnly").keyup(function(event) {
              
              $(this).val($(this).val().replace(/[^\d].+/, ""));
                  if ((event.which < 48 || event.which > 57)) {
                      event.preventDefault();
                  }
              });
});

$('.txtOnly').keypress(function (e) {
            var regex = new RegExp(/^[a-zA-Z\s]+$/);
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            else {
                e.preventDefault();
                return false;
            }
    });
</script>
@endpush
@endsection
