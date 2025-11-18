@extends('layouts.app-template-datatable')
@push('styles')
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

    .modal-confirm {
      color: #636363;
      width: 400px;
    }

    .modal-confirm .modal-content {
      padding: 20px;
      border-radius: 5px;
      border: none;
      text-align: center;
      font-size: 14px;
    }

    .modal-confirm .modal-header {
      border-bottom: none;
      position: relative;
    }

    .modal-confirm h4 {
      text-align: center;
      font-size: 26px;
      margin: 30px 0 -10px;
    }

    .modal-confirm .close {
      position: absolute;
      top: -5px;
      right: -2px;
    }

    .modal-confirm .modal-body {
      color: #999;
    }

    .modal-confirm .modal-footer {
      border: none;
      text-align: center;
      border-radius: 5px;
      font-size: 13px;
      padding: 10px 15px 25px;
    }

    .modal-confirm .modal-footer a {
      color: #999;
    }

    .modal-confirm .icon-box {
      width: 80px;
      height: 80px;
      margin: 0 auto;
      border-radius: 50%;
      z-index: 9;
      text-align: center;
      border: 3px solid #f15e5e;
    }

    .modal-confirm .icon-box i {
      color: #f15e5e;
      font-size: 46px;
      display: inline-block;
      margin-top: 13px;
    }

    .modal-confirm .btn,
    .modal-confirm .btn:active {
      color: #fff;
      border-radius: 4px;
      background: #60c7c1;
      text-decoration: none;
      transition: all 0.4s;
      line-height: normal;
      min-width: 120px;
      border: none;
      min-height: 40px;
      border-radius: 3px;
      margin: 0 5px;
    }

    .modal-confirm .btn-secondary {
      background: #c1c1c1;
    }

    .modal-confirm .btn-secondary:hover,
    .modal-confirm .btn-secondary:focus {
      background: #a8a8a8;
    }

    .modal-confirm .btn-danger {
      background: #f15e5e;
    }

    .modal-confirm .btn-danger:hover,
    .modal-confirm .btn-danger:focus {
      background: #ee3535;
    }

    .trigger-btn {
      display: inline-block;
      margin: 100px auto;
    }
  </style>

@endpush
@section('content')
  <!-- <div class="content-wrapper"> -->

  <section class="content">
    <div class="card card-default">
      <div class="card-body">

        <!-- MAIN CARD -->
        <div class="card card-default">
          <div class="card-body" style="padding:5px; font-size:14px;">

            {{-- SUCCESS --}}
            @if ($message = Session::get('success'))
              <div class="row">
                <div class="alert alert-success alert-dismissible fade show" style="margin:10px 30px;">
                  <strong>{{ $message }}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              </div>
            @endif

            {{-- ERROR --}}
            @if ($error = Session::get('error'))
              <div class="row">
                <div class="alert alert-danger alert-dismissible fade show" style="margin:10px 30px;">
                  <strong>{{ $error }}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              </div>
            @endif

            <!-- JS ERROR -->
            <div class="alert alert-danger print-error-msg" id="errorDiv" style="display:none;">
              <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
              <ul></ul>
            </div>

            <!-- HEADER ROW -->
            <div class="row mb-2">

              <div class="col-md-2">
                <a href="{{ url('lb-dup-aadhar-list-approved-verifier') }}">
                  <img width="50px" src="{{ asset('images/back.png') }}" alt="Back">
                </a>
              </div>

              <div class="col-md-8">
                <h3 class="text-center">
                  Aadhaar No:
                  <span class="text-danger">********{{ substr($aadhar_no, -4) }}</span>
                </h3>
              </div>

            </div>

            <!-- TABLE -->
            <div class="table-responsive">
              <table id="example" class="table table-bordered table-striped w-100">

                <thead style="font-size:12px;">
                  <tr>
                    <th width="5%">Application ID</th>
                    <th width="20%">Beneficiary Name</th>
                    <th width="8%">Bank Account</th>
                    <th width="5%">Aadhaar No.</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody style="font-size:14px;">
                  @if(count($data) > 0)
                    @foreach ($data as $row)
                      <tr>
                        <td>{{ $row->application_id }}</td>
                        <td>{{ $row->ben_fname }}</td>
                        <td>{{ $row->bank_code }}</td>
                        <td>{{ $row->original_aadhar }}</td>

                        <td>
                          @if(empty($row->dup_modification))

                            @if($row->payment_suspended == 1)
                              <span class="badge bg-warning text-dark">Mark due to Janma Mrityu Thathya</span>

                            @else
                              <button class="btn btn-success ben_update_button" id="btnUpdate_{{ $row->application_id }}"
                                value="{{ $row->application_id }}">
                                Click to Change Aadhaar
                              </button>

                              <button class="btn btn-warning ben_reject_button" id="btnReject_{{ $row->application_id }}"
                                value="{{ $row->application_id }}">
                                Reject
                              </button>
                            @endif

                          @elseif($row->dup_modification == 1)
                            <span class="badge bg-warning text-dark">Modified… Approval Pending</span>

                          @elseif($row->dup_modification == 2)
                            <span class="badge bg-success">Aadhaar modification approved</span>
                          @endif

                          @if(empty($row->dup_modification) || $row->dup_modification == 1)
                            <button class="btn btn-info ben_doc_button" id="btnDoc_{{ $row->application_id }}"
                              value="{{ $row->application_id }}">
                              View Doc (Aadhaar)
                            </button>
                          @endif

                        </td>
                      </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="5" class="text-center">No Duplicate Record Found.</td>
                    </tr>
                  @endif
                </tbody>

              </table>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- </div> -->


  <!-- UPDATE AADHAAR MODAL -->
  <div class="modal fade" id="update_modal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            Update Aadhaar (<span id="applicant_id_modal"></span>)
          </h5>

          <p id="age_restricted" class="text-danger" style="display:none;">
            ** This Beneficiary age exceeds 60 years.
          </p>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" id="uploadForm" action="dupAadharModify" enctype="multipart/form-data"
          onsubmit="return chk_validation()">
          {{ csrf_field() }}
          <input type="hidden" id="application_id" name="application_id">

          <div class="modal-body">

            <div class="row">
              <div class="form-group col-md-4">
                <label class="required-field">New Aadhaar No.</label>
                <input type="text" name="new_aadhar_no" maxlength="12" id="new_aadhar" class="NumOnly form-control"
                  placeholder="New Aadhaar">

                <input type="hidden" name="aadhar_no" id="aadhar_no" value="{{ $aadhar_no_encrypt }}">
                <span id="error_new_aadhar" class="text-danger"></span>
              </div>
            </div>

            <div class="row mt-3">
              <div class="form-group col-md-4">
                <label>New Aadhaar Document</label>
                <input type="file" name="aadhar_file" id="fileInput" class="form-control">
                <span id="error_file" class="text-danger"></span>
              </div>
            </div>

          </div>

          <div class="modal-footer">

            <button type="button" class="btn btn-info ben_doc_button" id="btnDoc_modal" value="">
              View Doc (Aadhaar)
            </button>

            <p class="mt-2">
              ** New Aadhaar Document upload is required only if new Aadhaar does not match with previous.
            </p>

            <button type="submit" id="submitButton" name="btnSubmit" class="btn btn-primary">
              Update Aadhaar
            </button>

            <img src="{{ asset('images/ZKZg.gif')}}" id="btn_encolser_loader" width="150px" style="display:none;">
          </div>

        </form>

      </div>
    </div>
  </div>


  <!-- REJECT FORM -->
  <form method="POST" action="{{ route('dupAadharReject') }}" id="dupReject">
    @csrf
    <input type="hidden" name="aadhar_no" id="aadhar_no" value="{{ $aadhar_no_encrypt }}">
    <input type="hidden" id="application_id" name="application_id">
  </form>


  <!-- VIEW DOC MODAL -->
  <div class="modal fade" id="encolser_modal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="encolser_name">Modal title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div id="encolser_content"></div>

      </div>
    </div>
  </div>

@endsection
@push('scripts')
  <script src="{{ URL::asset('js/validateAdhar.js') }}"></script>
  <script>
    $(document).ready(function () {
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #lk-main').addClass("active");
      $('.sidebar-menu #dup_aadhar_approved').addClass("active");
      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var age_restrict_data = '<?php echo json_encode($age_restrict_data); ?>';
      // console.log(age_restrict_data);
      var base_url = '{{ url('/') }}';
      $(".NumOnly").keyup(function (event) {
        $(this).val($(this).val().replace(/[^\d].+/, ""));
        if ((event.which < 48 || event.which > 57)) {
          event.preventDefault();
        }
      });
      $('#example').DataTable({
        "paging": true,
        "searchable": false,
        "paging": false,
        "ordering": false,
        "bFilter": false,
        "bInfo": true,
        "pageLength": 20
      });
      $(document).on('click', '.ben_doc_button', function () {
        $('.ben_doc_button').attr('disabled', true);
        //$('.ben_reject_button').attr('disabled',true); 
        $('.ben_update_button').attr('disabled', true);
        var benid = $(this).val();
        View_encolser_modal('Copy of Aadhar Card', 6, 0, benid);
      });
      $(document).on('click', '.ben_update_button', function () {

        $('#uploadForm #application_id').val('');
        $("#applicant_id_modal").html('');
        $('#age_restricted').hide();
        var benid = $(this).val();
        if (age_restrict_data.includes(benid)) {
          $('#age_restricted').show();
        }
        else {
          $('#age_restricted').hide();
        }
        $('#uploadForm #application_id').val(benid);
        $("#applicant_id_modal").html(benid);
        $("#btnDoc_modal").val(benid);
        $("#update_modal").modal();
      });
      $(document).on('click', '.ben_reject_button', function () {
        $('#dupReject #application_id').val('');
        var benid = $(this).val();
        var y_n = confirm('Are You Sure. You Want to Reject the application with application Id ' + benid + '?');
        if (y_n) {
          var benid = $(this).val();
          $('#dupReject #application_id').val(benid);
          $('.ben_reject_button').attr('disabled', true);
          $('.ben_update_button').attr('disabled', true);
          $("#dupReject").submit();
        }
      });
    });
    function chk_validation() {
      $("#btn_encolser_loader").hide();
      var aadhar_val = $("#new_aadhar").val();
      if (aadhar_val == '') {
        var error_aadhar_no = 'Aadhar No. is required';
        alert(error_aadhar_no);
        $("#new_aadhar").focus();
        return false;
      } else {
        if (aadhar_val.length != 12) {
          var error_aadhar_no = 'Aadhar No. should be 12 digit';
          alert(error_aadhar_no);
          $("#new_aadhar").focus();
          return false;
        } else {
          var aadhar_valid = validate_adhar(aadhar_val);
          if (aadhar_valid) {
            $("#btn_encolser_loader").show();
            $("#submitButton").hide();
            //$('.ben_reject_button').attr('disabled',true); 
            y_n = 1;
            return true;
          }
          else {
            error_aadhar_no = 'Invalid Aadhar No.';
            alert(error_aadhar_no);
            $("#new_aadhar").focus();
            return false;
          }
        }
      }

    }
    function View_encolser_modal(doc_name, doc_type, is_profile_pic, application_id) {
      $('#encolser_name').html('');
      $('#encolser_content').html('');
      $('#encolser_name').html(doc_name + '(' + application_id + ')');
      $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');

      $.ajax({
        url: '{{ url('ajaxGetEncloser') }}',
        type: "POST",
        data: {
          doc_type: doc_type,
          is_profile_pic: is_profile_pic,
          application_id: application_id,
          _token: '{{ csrf_token() }}',
        },
      }).done(function (data, textStatus, jqXHR) {
        $('#encolser_content').html('');
        $('#encolser_content').html(data);
        $('.ben_doc_button').attr('disabled', false);
        // $('.ben_reject_button').attr('disabled',false); 
        $('.ben_update_button').attr('disabled', false);
        $("#encolser_modal").modal();
      }).fail(function (jqXHR, textStatus, errorThrown) {
        $('.ben_doc_button').attr('disabled', false);
        //$('.ben_reject_button').attr('disabled',false); 
        $('.ben_update_button').attr('disabled', false);
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
        $("#" + divid).addClass('alert-warning');
      }
      else {
        $("#" + divid).removeClass('alert-warning');
        $("#" + divid).addClass('alert-success');
      }
      if (Array.isArray(msg)) {
        $.each(msg, function (key, value) {
          $("#" + divid).find("ul").append('<li>' + value + '</li>');
        });
      }
      else {
        $("#" + divid).find("ul").append('<li>' + msg + '</li>');
      }
    }
    function closeError(divId) {
      $('#' + divId).hide();
    }
  </script>
@endpush