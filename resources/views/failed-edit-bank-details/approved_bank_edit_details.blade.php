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
  </style>
@endpush
@section('content')
  <!-- <div class="content-wrapper"> -->

    <!-- Page Header -->
    <section class="content-header">
      <h1>Approve Edited Bank Details</h1>
    </section>

    <section class="content">
      <div class="card card-default">
        <div class="card-body">

          <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}">

          <!-- FILTER CARD -->
          <div class="card card-default">
            <div class="card-header">
              Bank Details Yet To Be Approved
            </div>

            <div class="card-body" style="padding:5px;">

              <!-- ALERTS -->
              <div class="row">
                @if ($message = Session::get('success'))
                  <div class="alert alert-success alert-dismissible fade show">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                @endif

                @if(count($errors) > 0)
                  <div class="alert alert-danger alert-dismissible fade show">
                    <ul>
                      @foreach($errors->all() as $error)
                        <li><strong>{{ $error }}</strong></li>
                      @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                @endif
              </div>

              <!-- FILTER ROW -->
              <div class="row">

                <div class="form-group col-md-2">
                  <label class="control-label">Rural/Urban</label>
                  <select name="filter_1" id="filter_1" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach ($levels as $key => $value)
                      <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-3">
                  <label class="control-label">Block/Sub Division</label>
                  <select name="filter_2" id="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div>

                <div class="col-md-2">
                  <label class="control-label required-field">Failed Type</label>
                  <select name="failed_type" id="failed_type" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach(Config::get('globalconstants.failed_type') as $key => $val)
                      @if($key == 1 || $key == 2)
                        <option value="{{ $key }}">{{ $val }}</option>
                      @endif
                    @endforeach
                  </select>
                  <span id="error_failed_type" class="text-danger" style="font-size:12px; font-weight:bold;"></span>
                </div>

                <div class="form-group col-md-3" style="margin-top:25px;">
                  <button id="filter" class="btn btn-success">
                    <i class="fa fa-search"></i> Search
                  </button>
                  <button id="reset" class="btn btn-warning">
                    <i class="fa fa-refresh"></i> Reset
                  </button>
                </div>
              </div>

              <hr />

              <div class="row">
                <div class="form-group col-md-3 offset-md-4" id="approve_rejdiv" style="display:none;">
                  <button id="bulk_approve" value="approve" class="btn btn-success btn-lg">
                    Approve
                  </button>
                </div>
              </div>

            </div>
          </div>

          <!-- LIST CARD -->
          <div class="card card-default mt-3" id="failed_list" style="display:none;">
            <div class="card-header">List of New Edited Banking Information</div>

            <div class="card-body" style="padding:5px; font-size:14px;">
              <div class="table-responsive">
                <table id="example" class="table table-bordered table-striped w-100">
                  <thead style="font-size:12px;">
                    <tr>
                      <th>Sl No</th>
                      <th>Beneficiary ID</th>
                      <th>Applicant Name</th>
                      <th>Swasthya Sathi Card No</th>
                      <th>Old Bank Account No</th>
                      <th>Old IFSC</th>
                      <th>New Bank Account No</th>
                      <th>New IFSC</th>
                      <th>Failure Type</th>
                      <th>Action</th>
                      <th>Check <input type="checkbox" id="check_all_btn" style="width:48px;"></th>
                    </tr>
                  </thead>
                  <tbody style="font-size:14px;"></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- DETAILS MODAL -->
      <div class="modal fade ben_view_modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">

            <div class="modal-header">
              <h4 class="modal-title">Approve Edited Bank Details</h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body ben_view_body">

              <!-- PERSONAL DETAILS CARD -->
              <div class="card card-default mb-2">
                <div class="card-header">
                  <a data-bs-toggle="collapse" href="#collapsePersonal" class="text-decoration-none">
                    Personal Details <span class="applicant_id_modal"></span>
                  </a>
                  <div class="preloader1">
                    <img src="{{ asset('images/ZKZg.gif') }}" width="150" id="loader_img_personal">
                  </div>
                </div>

                <div id="collapsePersonal" class="collapse show">
                  <div class="card-body" style="padding:5px;">
                    <table class="table table-bordered table-sm">
                      <tbody>
                        <tr>
                          <th>Swasthya Sathi Card No.</th>
                          <td id="sws_card_txt"></td>
                          <th>Mobile No.</th>
                          <td id="mobile_no"></td>
                        </tr>
                        <tr>
                          <th>Name</th>
                          <td id="ben_fullname"></td>
                          <th>Gender</th>
                          <td id="gender"></td>
                        </tr>
                        <tr>
                          <th>DOB</th>
                          <td id="dob"></td>
                          <th>Age</th>
                          <td id="ben_age"></td>
                        </tr>
                        <tr>
                          <th>Caste</th>
                          <td id="caste"></td>
                          <th class="caste">SC/ST Certificate No.</th>
                          <td id="caste_certificate_no" class="caste"></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- BANK DETAILS CARD -->
              <div class="card card-default mb-2">
                <div class="card-header">
                  <a data-bs-toggle="collapse" href="#collapseBank" class="text-decoration-none"
                    id="panel_bank_name_text"></a>
                </div>

                <div id="collapseBank" class="collapse show">
                  <div class="card-body" style="padding:5px;">
                    <table class="table table-bordered table-sm">
                      <tbody>
                        <tr>
                          <th>Old Bank Name</th>
                          <td id="old_bank_name"></td>
                          <th>New Bank Name</th>
                          <td id="new_bank_name"></td>
                        </tr>
                        <tr>
                          <th>Old Branch Name</th>
                          <td id="old_branch_name"></td>
                          <th>New Branch Name</th>
                          <td id="new_branch_name"></td>
                        </tr>
                        <tr>
                          <th>Old Account No.</th>
                          <td id="old_acc_no"></td>
                          <th>New Account No.</th>
                          <td id="new_acc_no"></td>
                        </tr>
                        <tr>
                          <th>Old IFSC</th>
                          <td id="old_ifsc"></td>
                          <th>New IFSC</th>
                          <td id="new_ifsc"></td>
                        </tr>

                        <!-- Hidden name rows -->
                        <tr class="beneficiaryname_tr" style="display:none;">
                          <th>Old Beneficiary First Name</th>
                          <td id="old_fname"></td>
                          <th>New Beneficiary First Name</th>
                          <td id="new_fname"></td>
                        </tr>

                        <tr class="beneficiaryname_tr" style="display:none;">
                          <th>Old Beneficiary Middle Name</th>
                          <td id="old_mname"></td>
                          <th>New Beneficiary Middle Name</th>
                          <td id="new_mname"></td>
                        </tr>

                        <tr class="beneficiaryname_tr" style="display:none;">
                          <th>Old Beneficiary Last Name</th>
                          <td id="old_lname"></td>
                          <th>New Beneficiary Last Name</th>
                          <td id="new_lname"></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- ACTION CARD -->
              <div class="card card-default">
                <div class="card-header">
                  <h4 class="card-title">Action</h4>
                </div>

                <div class="card-body" style="padding:5px;">
                  <div class="row">

                    <div class="form-group col-md-4">
                      <label>Select Operation<span class="text-danger">*</span></label>
                      <select name="opreation_type" id="opreation_type" class="form-control">
                        <option value="A" selected>Approve</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4" id="div_rejection" style="display:none;">
                      <label>Select Reverted Cause<span class="text-danger">*</span></label>
                      <select name="reject_cause" id="reject_cause" class="form-control">
                        <option value="">--Select--</option>
                        <option value="Banking informtion">Banking information</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4">
                      <label>Enter Remarks</label>
                      <textarea id="accept_reject_comments" maxlength="100" class="form-control"
                        style="height:40px;"></textarea>
                    </div>

                  </div>
                </div>
              </div>

              <!-- FINAL FORM -->
              <form method="POST" action="#" id="fullForm" target="_blank" style="text-align:center;">
                @csrf
                <input type="hidden" name="is_bulk" id="is_bulk" value="0">
                <input type="hidden" id="id" name="id">
                <input type="hidden" id="application_id" name="application_id">
                <input type="hidden" name="applicantId[]" id="applicantId">

                <button type="button" id="verifyReject" class="btn btn-success btn-lg">Approve</button>
                <button type="button" id="submitting" class="btn btn-success" style="display:none;" disabled>
                  Processing Please Wait
                </button>
              </form>

            </div> <!-- modal-body -->

          </div>
        </div>
      </div>

    </section>
  <!-- </div> -->


@endsection
@push('scripts')
  <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
    $(document).ready(function () {
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #bankTrFailed').addClass("active");
      $('.sidebar-menu #accValTrFailedVerified').addClass("active");
      $('#opreation_type').val('A');
      $("#verifyReject").html("Approve");
      $('#div_rejection').hide();
      // $('.content').addClass('disabledcontent');
      // ------------------- Load Datatable Data ------------------------ //
      function loadDataTable() {
        var dataTable = "";
        if ($.fn.DataTable.isDataTable('#example')) {
          $('#example').DataTable().destroy();
        }
        var dataTable = $('#example').DataTable({
          dom: 'Blfrtip',
          paging: true,
          pageLength: 30,
          lengthMenu: [[30, 50], [30, 50]],
          // lengthMenu: [[20, 50,100,500,1000, -1], [20, 50,100,500,1000, 'All']],
          processing: true,
          serverSide: true,
          "oLanguage": {
            "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
          },
          ajax: {
            url: "{{ url('getEditedBankData') }}",
            type: "POST",
            data: function (d) {
              d.filter_1 = $('#filter_1').val(),
                d.filter_2 = $('#filter_2').val(),
                d.block_ulb_code = $('#block_ulb_code').val(),
                d.gp_ward_code = $('#gp_ward_code').val(),
                d.failed_type = $('#failed_type').val(),
                d.pay_mode = $('#pay_mode').val(),
                d._token = "{{csrf_token()}}"
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('.content').removeClass('disabledcontent');
              $('.preloader1').hide();
              // ajax_error(jqXHR, textStatus, errorThrown);
              $.alert({
                title: 'Error!!',
                type: 'red',
                icon: 'fa fa-warning',
                content: 'Something wrong when loading table!!',
              });
            }
          },
          "initComplete": function () {
            $('.content').removeClass('disabledcontent');
            //console.log('Data rendered successfully');
          },
          columns: [
            { "data": "DT_RowIndex" },
            { "data": "beneficiary_id" },
            { "data": "name" },
            { "data": "ss_card_no" },
            { "data": "old_acc_no" },
            { "data": "old_ifsc" },
            { "data": "new_acc_no" },
            { "data": "new_ifsc" },
            { "data": "type" },
            { "data": "view" },
            { "data": "check" },
          ],
          "columnDefs": [
            {
              "targets": [9, 10],
              "orderable": false,
              "searchable": false
            }
          ],

          "buttons": [
            {
              extend: 'pdf',
              title: "Approve Edited Bank Details  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
                $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                $date = $date->format('F j, Y g:i:a');
              echo $date;@endphp ",
              messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');
                $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                $date = $date->format('F j, Y g:i:a');
              echo $date;@endphp",
              footer: true,
              pageSize: 'A4',
              orientation: 'landscape',
              pageMargins: [40, 60, 40, 60],
              exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
              }
            },
            {
              extend: 'excel',
              title: "Approve Edited Bank Details  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
                $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                $date = $date->format('F j, Y g:i:a');
              echo $date;@endphp ",
              messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');
                $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                $date = $date->format('F j, Y g:i:a');
              echo $date;@endphp",
              footer: true,
              pageSize: 'A4',
              //orientation: 'landscape',
              pageMargins: [40, 60, 40, 60],
              exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                stripHtml: false,
                format: {
                  body: function (data, row, column, node) {
                    return column === 4 || column === 3 || column === 6 ? "\0" + data : data;
                  }
                },
              }
            }
          ],
        });
        $('#example').on('page.dt', function () {
          $('#approve_rejdiv').hide();
        });
      }


      // ------------------- Load Datatable Data End ------------------------ //
      $('#filter').click(function () {
        var error_failed_type = '';
        var failed_type = $('#failed_type').val();
        if (failed_type != '') {
          error_failed_type = '';
          $('#error_failed_type').text(error_failed_type);
          $('#failed_type').removeClass('has-error');
        } else {
          error_failed_type = 'Failed type is required.';
          $('#error_failed_type').text(error_failed_type);
          $('#failed_type').addClass('has-error');
        }
        if (error_failed_type != '') {
          $('#failed_list').hide();
          return false;
        } else {
          $('#failed_list').show();
          loadDataTable();
        }
        // dataTable.ajax.reload();
      });
      // ------------------- Checkbox Operation ---------------------------//
      $('#example').on('length.dt', function (e, settings, len) {
        $("#check_all_btn").prop("checked", false);
      });

      $('#check_all_btn').on('change', function () {
        var checked = $(this).prop('checked');
        var dataTable = $('#example').DataTable();
        dataTable.cells(null, 10).every(function () {
          var cell = this.node();
          $(cell).find('input[type="checkbox"][name="chkbx"]').prop('checked', checked);
        });
        var data = dataTable
          .rows(function (idx, data, node) {
            return $(node).find('input[type="checkbox"][name="chkbx"]').prop('checked');
          })
          .data()
          .toArray();
        //console.log(data);
        if (data.length === 0) {
          $("input.all_checkbox").removeAttr("disabled", true);
        }
        else {
          $("input.all_checkbox").attr("disabled", true);
        }
        var anyBoxesChecked = false;
        var applicantId = Array();
        $('input[type="checkbox"][name="chkbx"]').each(function (index, value) {
          if ($(this).is(":checked")) {
            anyBoxesChecked = true;
            applicantId.push(value.value);
          }
        });

        $("#fullForm #applicantId").val($.unique(applicantId));
        if (anyBoxesChecked == true) {
          $('#approve_rejdiv').show();
          $('.ben_view_button').attr('disabled', true);
          document.getElementById('bulk_approve').disabled = false;
          // document.getElementById('bulk_blkchange').disabled = false;
        }
        else {
          $('#approve_rejdiv').hide();
          $('.ben_view_button').removeAttr('disabled', true);
          document.getElementById('bulk_approve').disabled = true;
          // document.getElementById('bulk_blkchange').disabled = true;
        }
        // console.log(applicantId);
      });
      // ------------------- End Checkbox Operation -----------------------//

      // ------------------- View Button Click Section -----------------------//
      $(document).on('click', '.ben_view_button', function () {
        $('#loader_img_personal').show();
        $('.ben_view_button').attr('disabled', true);
        var benid = $(this).val();
        $('#fullForm #application_id').val(benid);
        $("#fullForm #is_bulk").val(0);
        $('#opreation_type').append('<option value="T">Reverted</option> ');
        $('#opreation_type').val('A').trigger('change');
        $("#verifyReject").html("Approve");
        $('#div_rejection').hide();
        $(".singleInfo").show();
        $('.applicant_id_modal').html('');
        $('#accept_reject_comments').val('');
        $("#collapseBank").collapse('hide');
        $('#collapsePersonal').collapse('hide');
        $('.ben_view_body').addClass('disabledcontent');
        $.ajax({
          type: 'post',
          url: "{{route('getUpdateEditDetailsBenData')}}",
          data: { _token: '{{csrf_token()}}', benid: benid },
          dataType: 'json',
          success: function (response) {
            // console.log(JSON.stringify(response));
            $("#panel_bank_name_text").text(response.paneltext);
            if (response.old_fname != '') {
              $('#old_fname').text(response.old_fname);
              $('#old_mname').text(response.old_mname);
              $('#old_lname').text(response.old_lname);

              $('#new_fname').text(response.new_fname);
              $('#new_mname').text(response.new_mname);
              $('#new_lname').text(response.new_lname);
              $('.beneficiaryname_tr').show();

            }
            else {
              $('.beneficiaryname_tr').hide();
            }
            $('.ben_view_body').removeClass('disabledcontent');
            $("#collapseBank").collapse('show');
            $('#loader_img_personal').hide();
            $('.ben_view_button').removeAttr('disabled', true);
            $('#sws_card_txt').text(response.personaldata[0].ss_card_no);
            var mname = response.personaldata[0].ben_mname;
            if (!(mname)) { var mname = '' }
            var lname = response.personaldata[0].ben_lname;
            if (!(lname)) { var lname = '' }
            $('#ben_fullname').text(response.personaldata[0].ben_fname + ' ' + mname + ' ' + lname);
            $('#mobile_no').text(response.personaldata[0].mobile_no);
            $('#gender').text(response.personaldata[0].gender);
            $('#dob').text(response.personaldata[0].dob);
            $('#ben_age').text(response.personaldata[0].age_ason_01012021);
            $('#caste').text(response.personaldata[0].caste);
            if (response.personaldata[0].caste == 'SC' || response.personaldata[0].caste == 'ST') {
              $('#caste_certificate_no').text(response.personaldata[0].caste_certificate_no);
              $('.caste').show();
            }
            else {
              $('.caste').hide();
            }
            $('#old_acc_no').text(response.old_bank_code);
            $('#old_bank_name').text(response.old_bank_name);
            $('#old_branch_name').text(response.old_branch_name);
            $('#old_ifsc').text(response.old_bank_ifsc);
            $('#new_acc_no').text(response.new_bank_code);
            $('#new_bank_name').text(response.new_bank_name);
            $('#new_branch_name').text(response.new_branch_name);
            $('#new_ifsc').text(response.new_bank_ifsc);

            $('.applicant_id_modal').html('(Beneficiary ID - ' + response.personaldata[0].beneficiary_id + ' , Application ID - ' + response.personaldata[0].application_id + ')');
            $('#fullForm #id').val(response.personaldata[0].application_id);
          },
          complete: function () {

          },
          error: function (jqXHR, textStatus, errorThrown) {
            $('.ben_view_body').removeClass('disabledcontent');
            $('#loader_img_personal').hide();
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
        $('.ben_view_modal').modal('show');

      });
      $('#bulk_approve').click(function () {
        $(".singleInfo").hide();
        $("#fullForm #is_bulk").val(1);
        $('#opreation_type option[value="T"]').remove();
        $('#opreation_type').val('A').trigger('change');
        $("#verifyReject").html("Approve");
        $('#div_rejection').hide();
        $('#fullForm #id').val('');
        $('#fullForm #application_id').val('');
        $('#accept_reject_comments').val('');
        benid = "";
        $('.ben_view_modal').modal('show');
      });

      $(document).on('click', '.opreation_type', function () {
        if ($(this).val() == 'T' || $(this).val() == 'R') {
          $('#div_rejection').show();
          if ($(this).val() == 'T')
            $("#verifyReject").html("Revert");
          else if ($(this).val() == 'R')
            $("#verifyReject").html("Reject");
        }
        else {
          $("#verifyReject").html("Approve");
          $('#div_rejection').hide();
          $("#reject_cause").val('');
        }
      });
      // -------------------- View Button Click Section End -----------------------//

      // -------------------- Final Approve Section-------------------------- //
      $(document).on('click', '#verifyReject', function () {
        var reject_cause = $('#reject_cause').val();
        var opreation_type = $('#opreation_type').val();
        var accept_reject_comments = $('#accept_reject_comments').val();
        var is_bulk = $('#is_bulk').val();
        var single_app_id = $('#application_id').val();
        var applicantId = $('#applicantId').val();
        var valid = 1;
        if (opreation_type == 'R' || opreation_type == 'T') {
          var valid = 0;
          if (reject_cause != '') {
            var valid = 1;
          }
          else {
            $.alert({
              title: 'Error!!',
              type: 'red',
              icon: 'fa fa-warning',
              content: '<strong>Please Select Cause</strong>',
            });
            return false;
          }
        }
        if (valid == 1) {
          $.confirm({
            title: 'Warning',
            type: 'orange',
            icon: 'fa fa-warning',
            content: '<strong>Are you sure to proceed?</strong>',
            buttons: {
              Ok: function () {
                $("#submitting").show();
                $("#verifyReject").hide();
                var id = $('#id').val();
                $.ajax({
                  type: 'POST',
                  url: "{{ url('approvedEditedBankData') }}",
                  data: {
                    reject_cause: reject_cause,
                    opreation_type: opreation_type,
                    accept_reject_comments: accept_reject_comments,
                    application_id: id,
                    is_bulk: is_bulk,
                    applicantId: applicantId,
                    single_app_id: single_app_id,
                    _token: '{{ csrf_token() }}',
                  },
                  success: function (data) {
                    // dataTable.ajax.reload();
                    var table_renew = $('#example').DataTable();
                    table_renew.ajax.reload(null, false);
                    //$('#example').DataTable().ajax.reload()
                    if (data.return_status == 1) {

                      $('.ben_view_modal').modal('hide');
                      $('#approve_rejdiv').hide();
                      $.confirm({
                        title: 'Success',
                        type: 'green',
                        icon: 'fa fa-check',
                        content: data.return_msg,
                        buttons: {
                          Ok: function () {
                            $("#submitting").hide();
                            $("#verifyReject").show();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                          }
                        }
                      });
                    }
                    else {
                      $("#submitting").hide();
                      $("#verifyReject").show();
                      $('.ben_view_modal').modal('hide');
                      $('#approve_rejdiv').hide();
                      $.alert({
                        title: 'Error',
                        type: 'red',
                        icon: 'fa fa-warning',
                        content: data.return_msg
                      });
                    }
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                    $.confirm({
                      title: 'Error',
                      type: 'red',
                      icon: 'fa fa-warning',
                      content: 'Something went wrong in the approval!!',
                      buttons: {
                        Ok: function () {
                          // $("#verifyReject").show();
                          //  $("#submitting").hide();
                          location.reload();
                        }
                      }
                    });
                  }
                });
              },
              Cancel: function () {

              },
            }
          });
        }
      });
      // -------------------- Final Approve Section --------------------------// 

      // --------------- Filter Section -------------------- //
      // $('#filter').click(function(){
      //   var failed_type = $('#failed_type').val();
      //   dataTable.ajax.reload();
      // });

      $('#reset').click(function () {
        $('#filter_1').val('').trigger('change');
        $('#filter_2').val('').trigger('change');
        $('#block_ulb_code').val('').trigger('change');
        $('#gp_ward_code').val('').trigger('change');
        $('#failed_type').val('').trigger('change');
        $('#pay_mode').val('').trigger('change');
        dataTable.ajax.reload();
      });
      // --------------- Filter Section End-------------------- //

      // ------------ Master DropDown Section Start-------------------- //
      $('#filter_1').change(function () {
        var filter_1 = $(this).val();

        $('#filter_2').html('<option value="">--All --</option>');
        $('#block_ulb_code').html('<option value="">--All --</option>');
        select_district_code = $('#dist_code').val();

        var htmlOption = '<option value="">--All--</option>';
        $('#gp_ward_code').html('<option value="">--All --</option>');
        if (filter_1 == 1) {
          $.each(subDistricts, function (key, value) {
            if ((value.district_code == select_district_code)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
          $("#blk_sub_txt").text('Subdivision');
          $("#gp_ward_txt").text('Ward');
          $("#municipality_div").show();
          $("#gp_ward_div").show();
        }
        else if (filter_1 == 2) {
          // console.log(filter_1);
          $.each(blocks, function (key, value) {
            if ((value.district_code == select_district_code)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
          $("#blk_sub_txt").text('Block');
          $("#gp_ward_txt").text('GP');
          $("#municipality_div").hide();
          $("#gp_ward_div").show();
        }
        else {
          $("#blk_sub_txt").text('Block/Subdivision');
          $("#gp_ward_txt").text('GP/Ward');
          $("#municipality_div").hide();
        }
        $('#filter_2').html(htmlOption);

      });
      $('#filter_2').change(function () {
        var rural_urbanid = $('#filter_1').val();
        $('#gp_ward_code').html('<option value="">--All --</option>');
        if (rural_urbanid == 1) {
          var sub_district_code = $(this).val();
          if (sub_district_code != '') {
            $('#block_ulb_code').html('<option value="">--All --</option>');
            select_district_code = $('#dist_code').val();
            var htmlOption = '<option value="">--All--</option>';
            $.each(ulbs, function (key, value) {
              if ((value.district_code == select_district_code) && (value.sub_district_code == sub_district_code)) {
                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
              }
            });
            $('#block_ulb_code').html(htmlOption);
          }
          else {
            $('#block_ulb_code').html('<option value="">--All --</option>');
          }
        }
        else if (rural_urbanid == 2) {
          $('#muncid').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
          var block_code = $(this).val();
          select_district_code = $('#dist_code').val();
          var htmlOption = '<option value="">--All--</option>';
          $.each(gps, function (key, value) {
            if ((value.district_code == select_district_code) && (value.block_code == block_code)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
          $('#gp_ward_code').html(htmlOption);
          $("#gp_ward_div").show();
        }
        else {
          $('#block_ulb_code').html('<option value="">--All --</option>');
        }
      });
      $('#block_ulb_code').change(function () {
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
            $.each(ulb_wards, function (key, value) {
              if (value.urban_body_code == muncid) {
                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
              }
            });
            $('#gp_ward_code').html(htmlOption);
            //console.log(htmlOption);
          }
          else {
            $('#gp_ward_code').html('<option value="">--All --</option>');
            $("#gp_ward_div").hide();
          }
        }
        else {
          $('#gp_ward_code').html('<option value="">--All --</option>');
        }
      });
      // ------------ Master DropDown Section End-------------------- //



    });
    function controlCheckBox() {
      var anyBoxesChecked = false;
      var applicantId = Array();
      $(' input[type="checkbox"]').each(function () {
        if ($(this).is(":checked")) {
          anyBoxesChecked = true;
          applicantId.push($(this).val());
        }

      });
      $("#fullForm #applicantId").val($.unique(applicantId));
      if (anyBoxesChecked == true) {
        $('#approve_rejdiv').show();
        $("#check_all_btn").attr("disabled", true);
        $('.ben_view_button').attr('disabled', true);
        document.getElementById('bulk_approve').disabled = false;
        // document.getElementById('bulk_blkchange').disabled = false;
      } else {
        $('#approve_rejdiv').hide();
        $('.ben_view_button').removeAttr('disabled', true);
        $("#check_all_btn").removeAttr("disabled", true);
        document.getElementById('bulk_approve').disabled = true;
        // document.getElementById('bulk_blkchange').disabled = true;
      }
      // console.log(applicantId);
    }
  </script>
@endpush