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

    .all_checkbox {
      padding-left: 20px;

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

              {{-- SUCCESS MESSAGE --}}
              @if ($message = Session::get('success'))
                <div class="row">
                  <div class="alert alert-success alert-dismissible fade show" style="margin:10px 30px;">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                </div>
              @endif

              {{-- ERROR MESSAGE --}}
              @if ($error = Session::get('error'))
                <div class="row">
                  <div class="alert alert-danger alert-dismissible fade show" style="margin:10px 30px;">
                    <strong>{{ $error }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                </div>
              @endif

              {{-- AJAX ERROR --}}
              <div class="alert alert-danger print-error-msg fade show" id="errorDiv" style="display:none;">
                <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
                <ul></ul>
              </div>

              <!-- HEADER -->
              <div class="row mt-2">
                <div class="col-md-2">
                  <a href="{{ url('dedupBankListView') }}">
                    <img width="50px" src="{{ asset('images/back.png') }}" alt="Back">
                  </a>
                </div>
                <div class="col-md-8">
                  <h3 class="text-center">
                    Bank Account No.: <span class="text-danger">{{ $bank_code }}</span>
                  </h3>
                </div>
              </div>

              <!-- BULK REJECT BUTTON -->
              <div class="row mt-3">
                <div class="form-group col-md-3 offset-md-3" id="approve_rejdiv" style="display:none;">
                  <button id="bulk_reject" name="bulk_reject" value="approve" class="btn btn-danger btn-lg">
                    Bulk Reject
                  </button>
                </div>
              </div>

              <!-- TABLE -->
              <div class="table-responsive mt-3">
                <table id="example" class="table table-bordered table-striped w-100">

                  <thead style="font-size:12px;">
                    <tr>
                      <th width="2%">Application Id</th>
                      <th width="10%">Beneficiary name</th>
                      <th width="2%">Mobile No.</th>
                      <th width="10%">Block/Municipality</th>
                      <th width="10%">GP/Ward</th>
                      <th width="50%">Action</th>
                      <th width="2%">Check <input type="checkbox" id="select_all"></th>
                    </tr>
                  </thead>

                  <tbody style="font-size:14px;">
                    @if(count($data) > 0)
                      @foreach($data as $row)
                        <tr>
                          <td>{{ $row['application_id'] }}</td>
                          <td>{{ $row['ben_name'] }}</td>
                          <td>{{ $row['mobile_no'] }}</td>
                          <td>{{ $row['local_body_name'] }}</td>
                          <td>{{ $row['gp_ward_name'] }}</td>

                          <td>
                            <button class="btn btn-info btn-sm ben_doc_button" id="btnDoc_{{ $row['application_id'] }}"
                              value="{{ $row['application_id'] }}_{{ $row['faulty_status'] }}">
                              View Bank Passbook
                            </button>

                            @if($row['allowed'] == 1)
                              <button class="btn btn-warning btn-sm ben_reject_button"
                                id="btnUpdate_{{ $row['application_id'] }}"
                                value="{{ $row['application_id'] }}_{{ $row['faulty_status'] }}">
                                Reject
                              </button>

                              <a class="btn btn-danger btn-sm"
                                href="{{ url('/dedupBankUpdate') }}?bank_code={{ $bank_code }}&application_id={{ $row['application_id'] }}&is_faulty={{ $row['faulty_status'] }}">
                                Edit
                              </a>
                            @endif
                          </td>

                          <td>
                            @if($row['allowed'] == 1)
                              <input type="checkbox" class="all_checkbox" onclick="controlCheckBox();"
                                value="{{ $row['application_id'] }}_{{ $row['faulty_status'] }}">
                            @else
                              <span class="badge bg-success">Related to other</span>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    @else
                      <tr>
                        <td colspan="7" class="text-center">No Duplicate Record Found.</td>
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

  <!-- REJECT CONFIRM MODAL -->
  <div id="modalApproval" class="modal fade" tabindex="-1">
    <form method="POST" action="{{ route('dupBankReject') }}" id="dupReject">
      @csrf

      <input type="hidden" id="application_id" name="application_id">
      <input type="hidden" id="applicantId" name="applicantId">
      <input type="hidden" id="is_bulk" name="is_bulk" value="0">
      <input type="hidden" id="bank_code" name="bank_code" value="{{ $bank_code }}">

      <div class="modal-dialog modal-confirm">
        <div class="modal-content">

          <div class="modal-header flex-column">
            <h4 class="modal-title w-100">Are you sure?</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <p>
              Do you really want to Reject
              <span id="singleInfo">
                of the application (<span id="application_text_approve"></span>)
              </span>?
            </p>

            <div class="form-group col-md-12" id="div_rejection">
              <label class="required" for="reject_cause">Select Reject Cause</label>
              <select name="reject_cause" id="reject_cause" class="form-control">
                <option value="">--Select--</option>
                @foreach($reject_revert_reason as $r_arr)
                  <option value="{{ $r_arr->id }}">{{ $r_arr->reason }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

            <button type="submit" class="btn btn-danger modal-submitapprove">OK</button>

            <button type="button" id="submittingapprove" class="btn btn-success btn-lg" style="display:none;" disabled>
              Submitting please wait
            </button>
          </div>

        </div>
      </div>
    </form>
  </div>

  <!-- VIEW DOCUMENT MODAL -->
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
  <script src="{{ asset("js/jquery.table2excel.js") }}"></script>
  <script>
    $(document).ready(function () {
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #lk-main').addClass("active");
      $('.sidebar-menu #dup_bank_code_approved').addClass("active");
      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var base_url = '{{ url('/') }}';
      $("#submittingapprove").hide();
      $(".NumOnly").keyup(function (event) {
        $(this).val($(this).val().replace(/[^\d].+/, ""));
        if ((event.which < 48 || event.which > 57)) {
          event.preventDefault();
        }
      });
      $('#example').DataTable({
        "paging": true,
        "searchable": true,
        "paging": false,
        "ordering": false,
        "bFilter": true,
        "bInfo": true,
        "pageLength": 20
      });
      $('#select_all').on('click', function () {
        if (this.checked) {
          $('.all_checkbox').each(function () {
            this.checked = true;
          });
        } else {
          $('.all_checkbox').each(function () {
            this.checked = false;
          });
        }
        controlCheckBox();
      });
      $('.all_checkbox').on('click', function () {
        if ($('.all_checkbox:checked').length == $('.all_checkbox').length) {
          $('#select_all').prop('checked', true);

        } else {
          $('#select_all').prop('checked', false);
        }
      });


      $(document).on('click', '.ben_doc_button', function () {
        $('.ben_doc_button').attr('disabled', true);
        //$('.ben_reject_button').attr('disabled',true); 
        $('.ben_reject_button').attr('disabled', true);
        var benidArr = $(this).val();
        var benid_explode = benidArr.split('_');
        var benid = benid_explode[0];
        var is_faulty = benid_explode[1];
        // console.log(benid);
        // console.log(is_faulty);
        View_encolser_modal('Copy of Bank Pass book', 10, 0, benid, is_faulty);
      });

      $(document).on('click', '.ben_reject_button', function () {
        // alert('ok1');
        $('#dupReject #application_id').val('');
        $('.ben_reject_button').attr('disabled', false);
        var benidArr = $(this).val();
        //console.log(benidArr);
        var benidArr_explode = benidArr.split('_');
        //console.log(benidArr_explode);
        $("#reject_cause").val('');
        //var benid=benidArr_explode[0];
        $('#dupReject #application_id').val(benidArr);
        $('.ben_reject_button').attr('disabled', true);
        $('.ben_doc_button').attr('disabled', true);
        $('#bulk_reject').attr('disabled', true);
        $("#dupReject #is_bulk").val(0);
        $('#application_text_approve').text(benidArr_explode[0]);
        $('#modalApproval').modal('show');

      });
      $('#bulk_reject').click(function () {
        $("#reject_cause").val('');
        if ($('.all_checkbox:checked').length == $('.all_checkbox').length) {
          alert('You Cannot reject all the beneficiaries. Please retain at least one beneficiary and uncheck this beneficiary from the list');
          return false;
        }
        else {
          $("#singleInfo").hide();
          $("#dupReject #is_bulk").val(1);
          $('#dupReject #application_id').val('');
          $('.ben_reject_button').attr('disabled', true);
          $('.ben_doc_button').attr('disabled', true);
          $('#modalApproval').modal('show');
        }
      });
      $('#encolser_modal').on('hidden.bs.modal', function () {
        $('.ben_reject_button').attr('disabled', false);
        $('#bulk_approve').attr('disabled', false);
      });

      $('#modalApproval').on('hidden.bs.modal', function () {
        $('.ben_reject_button').attr('disabled', false);
        $('#bulk_reject').attr('disabled', false);
        $('.ben_doc_button').attr('disabled', false);
        $("#dupReject #is_bulk").val(0);
        $('#dupReject #application_id').val('');
        $('#dupReject #applicantId').val('');
        $("#singleInfo").show();
      });
      $('.modal-submitapprove').on('click', function () {
        var reject_reason = $("#reject_cause").val();
        if (reject_reason != '') {
          $(".modal-submitapprove").hide();
          $("#submittingapprove").show();
          $("#dupReject").submit();
        }
        else {
          alert('Please Select Reject Cause');
          $("#reject_cause").focus();
          return false;
        }
      });
    });
    function controlCheckBox() {
      var anyBoxesChecked = false;
      var applicantId = Array();
      $('.all_checkbox').each(function () {
        if ($(this).is(":checked")) {
          anyBoxesChecked = true;
          applicantId.push($(this).val());
        }

      });
      $("#dupReject #applicantId").val($.unique(applicantId));
      if (anyBoxesChecked == true) {
        $('#approve_rejdiv').show();
        document.getElementById('bulk_approve').disabled = false;
      } else {
        $('#approve_rejdiv').hide();
        document.getElementById('bulk_approve').disabled = true;
      }
    }

    function View_encolser_modal(doc_name, doc_type, is_profile_pic, application_id, is_faulty) {
      $('#encolser_name').html('');
      $('#encolser_content').html('');
      $('#encolser_name').html(doc_name + '(' + application_id + ')');
      $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
      if (is_faulty == 1) {
        var url = '{{ url('ajaxGetEncloserFaulty') }}';
      }
      else {
        var url = '{{ url('ajaxGetEncloser') }}';
      }
      //alert(url);
      $.ajax({
        url: url,
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
        $('.ben_reject_button').attr('disabled', false);
        $("#encolser_modal").modal('show');
      }).fail(function (jqXHR, textStatus, errorThrown) {
        $('.ben_doc_button').attr('disabled', false);
        $('.ben_reject_button').attr('disabled', false);
        $('#encolser_content').html('');
        alert(sessiontimeoutmessage);
        window.location.href = base_url;
      });
    }
    function printMsg(msg, msgtype, divid) {
      $("#" + divid).find("ul").html('');
      $("#" + divid).css('display', 'block');
      if (msgtype == '0') {
        $("#" + divid).removeClass('alert-success');
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