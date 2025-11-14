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

    .modal {
      overflow: auto !important;
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
  </style>
@endpush
@section('content')
  <!-- <div class="content-wrapper"> -->

  <section class="content-header">
    <h1>Account Validation Failed</h1>
  </section>

  <section class="content">
    <div class="card card-default">
      <div class="card-body">

        <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

        <!-- Filter Card -->
        <div class="card card-default">
          <div class="card-header">
            <span id="panel-icon">Filter Here</span>
          </div>

          <div class="card-body p-2">
            <div class="row">
              @if (($message = Session::get('success')))
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

            <!-- SUBDIV Verifier -->
            @if($duty_level == 'SubdivVerifier' || $duty_level == 'SubdivDelegated Verifier')
              <div class="row">

                <div class="col-md-2">
                  <label class="form-label">Failed Type <span class="text-danger">*</span></label>
                  <select id="failed_type" name="failed_type" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach(Config::get('globalconstants.failed_type') as $key => $val)
                      @if($key == 1 || $key == 2)
                        <option value="{{ $key }}">{{ $val }}</option>
                      @endif
                    @endforeach
                  </select>
                </div>

                <div class="col-md-2">
                  <label class="form-label">Municipality</label>
                  <select id="filter_1" name="filter_1" class="form-control js-municipality">
                    <option value="">-----All----</option>
                    @foreach ($ulb_gp as $urban_body)
                      <option value="{{$urban_body->urban_body_code}}">
                        {{$urban_body->urban_body_name}}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label" id="ward_div">Wards</label>
                  <select id="filter_2" name="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label">&nbsp;</label><br>
                  <button type="button" id="filter" class="btn btn-success">
                    <i class="fa fa-search"></i> Search
                  </button>
                  <button type="button" id="excel_btn" class="btn btn-primary">
                    <i class="fa fa-file-excel-o"></i> Get Excel
                  </button>
                </div>

              </div>

              <!-- BLOCK Verifier -->
            @elseif($duty_level == 'BlockVerifier' || $duty_level == 'BlockDelegated Verifier')
              <div class="row">

                <div class="col-md-2">
                  <label class="form-label">Failed Type <span class="text-danger">*</span></label>
                  <select id="failed_type" name="failed_type" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach(Config::get('globalconstants.failed_type') as $key => $val)
                      @if($key == 1 || $key == 2)
                        <option value="{{ $key }}">{{ $val }}</option>
                      @endif
                    @endforeach
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label">Gram Panchayat</label>
                  <select id="filter_1" name="filter_1" class="form-control">
                    <option value="">-----All----</option>
                    @foreach ($ulb_gp as $gp)
                      <option value="{{$gp->gram_panchyat_code}}">
                        {{$gp->gram_panchyat_name}}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label">&nbsp;</label><br>
                  <button type="button" id="filter" class="btn btn-success"><i class="fa fa-search"></i> Search</button>
                  <button type="button" id="excel_btn" class="btn btn-primary">
                    <i class="fa fa-file-excel-o"></i> Get Excel
                  </button>
                </div>

              </div>
            @endif

          </div>
        </div>

        <!-- List Card -->
        <div class="card card-default mt-3" id="list_div" style="display:none;">
          <div class="card-header" id="panel_head">
            List of beneficiaries
          </div>

          <div class="card-body p-2">
            <div id="loadingDiv"></div>

            <div class="table-responsive">
              <table id="example" class="table table-bordered table-striped w-100">
                <thead class="table-light">
                  <tr>
                    <th>Sl No</th>
                    <th>Beneficiary ID</th>
                    <th>Beneficiary Name</th>
                    <th>Swasthya Sathi Card No.</th>
                    <th>Mobile No</th>
                    <th>Application Id</th>
                    <th>Block/ Municipality Name</th>
                    <th>GP/Ward Name</th>
                    <th>Account No</th>
                    <th>IFSC Code</th>
                    <th>Failure Type</th>
                    <th>Failure Month</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody style="font-size:14px;"></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Beneficiary Modal -->
    <div class="modal fade ben_bank_modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">

          <div class="modal-header">
            <h3 class="modal-title">Beneficiary ID (<span id="ben_id_modal"></span>)</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div id="loadingDivModal"></div>

            <!-- Hidden fields preserved -->
            <input type="hidden" id="benId" name="benId">
            <input type="hidden" id="faildTableId" name="faildTableId">
            <input type="hidden" id="statusCode" name="statusCode">
            <input type="hidden" id="application_id" name="application_id">
            <input type="hidden" id="old_bank_ifsc" name="old_bank_ifsc">
            <input type="hidden" id="old_bank_accno" name="old_bank_accno">

            <!-- PERSONAL DETAILS CARD -->
            <div class="card mb-3">
              <div class="card-header">
                Personal Details
              </div>
              <div class="card-body">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <th>Beneficiary Name</th>
                      <td id="ben_name_text"></td>
                      <th>Father's Name</th>
                      <td id="father_name_text"></td>
                    </tr>
                    <tr>
                      <th>Gender</th>
                      <td id="gender_text"></td>
                      <th>DOB (DD-MM-YYYY)</th>
                      <td id="dob_text"></td>
                    </tr>
                    <tr>
                      <th>Caste</th>
                      <td id="caste_text"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- BANK DETAILS CARD -->
            <div class="card">
              <div class="card-header">Bank Details</div>
              <div class="card-body">

                <h4 class="text-center text-danger">
                  Failed Reason: <span id="failed_reason"></span>
                </h4>

                <h5 class="text-center text-primary">
                  <b>N.B.: Account number cannot be changed.</b>
                </h5>

                <table class="table table-bordered">

                  <tbody>

                    <tr>
                      <th>Mobile Number</th>
                      <td><input type="text" id="mobile_no" class="form-control" disabled></td>

                      <th>Bank IFSC</th>
                      <td>
                        <input type="text" id="bank_ifsc" class="form-control" disabled>
                        <img src="{{ asset('images/ajaxgif.gif') }}" width="60" id="ifsc_loader" style="display:none;">
                        <span id="error_bank_ifsc_code" class="text-danger"></span>
                      </td>
                    </tr>

                    <tr>
                      <th>Bank Branch Name</th>
                      <td><input type="text" id="branch_name" class="form-control" disabled></td>

                      <th>Bank Name</th>
                      <td><input type="text" id="bank_name" class="form-control" disabled></td>
                    </tr>

                    <tr>
                      <th>Bank Account Number</th>
                      <td><input type="password" id="bank_account_number" class="form-control" disabled></td>

                      <th>Confirm Bank Account Number</th>
                      <td><input type="text" id="confirm_bank_account_number" class="form-control" disabled></td>
                    </tr>

                    <tr>
                      <th>Upload Bank Passbook (optional)</th>
                      <td>
                        <input type="file" id="upload_bank_passbook" accept=".jpg,.jpeg,.png,.pdf" class="form-control">
                        <small class="text-info">Max size 500KB</small>
                        <span id="error_file" class="text-danger"></span>
                      </td>

                      <th>Copy of Passbook</th>
                      <td>
                        <a class="btn btn-sm btn-primary" href="javascript:void(0);"
                          onclick="View_encolser_modal('Copy of Bank Pass book','10',1)">View</a>
                      </td>
                    </tr>

                  </tbody>
                </table>

              </div>
            </div>

          </div>

          <div class="modal-footer text-center">
            <button type="button" class="btn btn-success btn-lg btnUpdate">Keep Same</button>
          </div>

        </div>
      </div>
    </div>

    <!-- ENCLOSER MODAL -->
    <div class="modal fade" id="encolser_modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title" id="encolser_name">Modal title</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div id="encolser_content"></div>

          <div class="modal-footer text-center">
            <button type="button" class="btn btn-success modalEncloseClose" data-bs-dismiss="modal">Close</button>
          </div>

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
      $('#list_div').hide();
      $('#loadingDiv').hide();
      $('#upload_bank_passbook').change(function () {
        var card_file = document.getElementById("upload_bank_passbook");
        if (card_file.value != "") {
          var attachment;
          attachment = card_file.files[0];
          // console.log(attachment.type)
          var type = attachment.type;
          if (attachment.size > 512000) {
            document.getElementById("error_file").innerHTML = "<i class='fa fa-warning'></i> Unaccepted document file size. Max size 500 KB. Please try again";
            $('#upload_bank_passbook').val('');
            return false;
          }
          else if (type != 'image/jpeg' && type != 'image/png' && type != 'application/pdf') {
            document.getElementById("error_file").innerHTML = "<i class='fa fa-warning'></i> Unaccepted document file format. Only jpeg,jpg,png and pdf. Please try again";
            $('#upload_bank_passbook').val('');
            return false;
          }
          else {
            $('#file_upload_btn').show();
            document.getElementById("error_file").innerHTML = "";
          }
        }
      });
      $('#excel_btn').click(function () {
        var token = "{{csrf_token()}}";
        var filter_1 = $('#filter_1').val();
        var filter_2 = $('#filter_2').val();
        var failed_type = $('#failed_type').val();
        var pay_mode = $('#pay_mode').val();
        //    var student_roll_no = $('#student_roll_no').val();

        var data = { '_token': token, 'filter_1': filter_1, 'filter_2': filter_2, 'failed_type': failed_type, 'pay_mode': pay_mode };
        redirectPost('getBankFailedexcel', data);
      });
      $('.modalEncloseClose').click(function () {
        $('.encolser_modal').modal('hide');
      });
      $("#bank_account_number,#confirm_bank_account_number").on("copy cut paste drop", function () {
        return false;
      });
      $("#ben_fname_value").keydown(function (event) {
        var inputValue = event.which;
        // allow letters and whitespaces only.
        if (!(inputValue >= 65 && inputValue <= 120) && (inputValue != 32 && inputValue != 0)) {
          $('#error_beneficiary_fname').text('First name consists only alphabatical characters and spaces');
          $('#ben_fname_value').addClass('has-error');
        }
        else {
          $('#ben_fname_value').removeClass('has-error');
          $('#error_beneficiary_fname').text('')
        }
      });
      $("#ben_mname_value").keydown(function (event) {
        var inputValue = event.which;
        // allow letters and whitespaces only.
        if (!(inputValue >= 65 && inputValue <= 120) && (inputValue != 32 && inputValue != 0)) {
          $('#error_beneficiary_mname').text('Middle name consists only alphabatical characters and spaces');
          $('#ben_mname_value').addClass('has-error');
        }
        else {
          $('#ben_mname_value').removeClass('has-error');
          $('#error_beneficiary_mname').text('')
        }
      });
      $("#ben_lname_value").keydown(function (event) {
        var inputValue = event.which;
        // allow letters and whitespaces only.
        if (!(inputValue >= 65 && inputValue <= 120) && (inputValue != 32 && inputValue != 0)) {
          $('#error_beneficiary_lname').text('Last name consists only alphabatical characters and spaces');
          $('#ben_lname_value').addClass('has-error');
        }
        else {
          $('#ben_lname_value').removeClass('has-error');
          $('#error_beneficiary_lname').text('')
        }
      });
      // $('#loadingDiv').hide();
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #bankTrFailed').addClass("active");
      $('.sidebar-menu #accValTrFailed').addClass("active");
      $('.ben_bank_modal').on('hidden.bs.modal', function (e) {
        $('#mobile_no').val('');
        $('#bank_ifsc').val('');
        $('#bank_name').val('');
        $('#branch_name').val('');
        $('#bank_account_number').val('');
        $('#benId').val('');
        $('#faildTableId').val('');
        $('#old_bank_ifsc').val('');
        $('#old_bank_accno').val('');
        $('#upload_bank_passbook').val('');

      });
      $(document).on('click', '.bank_edit_btn', function () {
        $('#loadingDiv').show();
        var editvalue = this.id;

        $.ajax({
          type: 'post',
          url: "{{route('editBankDetails')}}",
          data: { _token: '{{csrf_token()}}', editvalue: editvalue },
          dataType: 'json',
          success: function (response) {
            $('#loadingDiv').hide();
            $('#loadingDivModal').hide();
            $('.ben_bank_modal').modal('show');

            $('#failed_reason').text(response.failed_reason);
            $('#ben_id_modal').text(response.ben_id);
            $('#ben_name_text').text(response.ben_name);
            $('#father_name_text').text(response.benfather_name);
            $('#gender_text').text(response.gender);
            $('#dob_text').text(response.dob);
            $('#caste_text').text(response.caste);
            $('#mobile_no').val(response.mobile_no);
            $('#bank_ifsc').val(response.bank_ifsc);
            $('#bank_name').val(response.bank_name);
            $('#branch_name').val(response.branch_name);
            $('#bank_account_number').val(response.bank_code);
            $('#confirm_bank_account_number').val(response.bank_code);
            $('#benId').val(response.ben_id);
            $('#faildTableId').val(response.failedid);
            $('#statusCode').val(response.status_code)
            $('#application_id').val(response.application_id)
            $('#old_bank_ifsc').val(response.bank_ifsc)
            $('#old_bank_accno').val(response.bank_code)

            if (response.status_code === '-7') {

              $('.name_div').show();
              $('#ben_fname_value').val(response.fname);
              $('#ben_mname_value').val(response.mname);
              $('#ben_lname_value').val(response.lname);

            }
            else {
              $('.name_div').hide();
            }

          },
          complete: function () {


          },
          error: function (jqXHR, textStatus, errorThrown) {
            $('#loadingDiv').hide();
            $('#loadingDivModal').hide();
            ajax_error(jqXHR, textStatus, errorThrown)

          }

        });
      });
      //$('#loadingDiv').hide();




      var dataTable = "";
      function loadDatatable() {
        $('#list_div').show();
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
          "pageLength": 10,
          'lengthMenu': [[10, 20, 30], [10, 20, 30]],
          "serverSide": true,
          "processing": true,
          "bRetrieve": true,
          "oLanguage": {
            "sProcessing": '<div class="preloader1" align="center"><h4 class="text-success" style="font-weight:bold;font-size:22px;">Processing...</h4></div>'
          },
          ajax: {
            url: "{{ url('linelisting-bank-edit') }}",
            type: "POST",
            data: function (d) {
              d.filter_1 = $('#filter_1').val(),
                d.filter_2 = $('#filter_2').val(),
                d.failed_type = $('#failed_type').val(),
                d.pay_mode = $('#pay_mode').val(),

                d._token = "{{csrf_token()}}"
            },

            error: function (jqXHR, textStatus, errorThrown) {
              $('#loadingDiv').hide();
              ajax_error(jqXHR, textStatus, errorThrown)
            }
          },
          "initComplete": function (record) {
            // console.log(record.json)
            //console.log('Data rendered successfully');
            $('#loadingDiv').hide();

            //  $('#completed_bank').text(record.json.completed[0].count);
            // $('#pending_bank_edit').text(record.json.recordsTotal);
          },
          "columns": [
            { "data": "DT_RowIndex" },
            { "data": "id" },
            { "data": "name" },
            { "data": "ss_cardno" },
            { "data": "mobile_no" },
            { "data": "application_id" },

            { "data": "block_ulb_name" },
            { "data": "gp_ward_name" },
            { "data": "accno" },
            { "data": "ifsc" },
            { "data": "type" },
            { "data": "failure_month" },
            { "data": "action" },
          ],
          "columnDefs": [
            {
              "targets": [3, 4, 5],
              "visible": false,
              "searchable": true
            },
            //         {
            //   "targets": [ 7 ],
            //   "orderable": false,
            //   "searchable": true
            // }
          ],
          "buttons": [
            {
              extend: 'pdfHtml5',
              title: "Account Validation Failed Report  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
                $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                $date = $date->format('F j, Y g:i:a');
              echo $date;@endphp ",
              messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');
                $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
                $date = $date->format('F j, Y g:i:a');
              echo $date;@endphp",

              footer: true,
              orientation: 'landscape',
              // pageSize : 'LEGAL',
              pageMargins: [40, 60, 40, 60],
              exportOptions: {
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],

              }
            },
            {
              extend: 'excel',

              title: "Account Validation Failed Report  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
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
                format: {
                  body: function (data, row, column, node) {
                    return column === 8 || column === 3 ? "\0" + data : data;
                  }
                },
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                stripHtml: false,
              }
            },

          ],
        });
      }
      $('#filter').click(function () {
        // $('#loadingDiv').show();
        // dataTable.ajax.reload();
        if ($('#failed_type').val() == '') {
          alert('Please select failed type');
          $('#failed_type').focus();
        }
        else {
          loadDatatable();
        }
      });


      $('.js-municipality').change(function () {
        var municipality = $('.js-municipality').val();
        var district_code = $('#dist_code').val();
        var htmlOption = '<option value="">----Select----</option>';
        if (municipality != '') {
          $.each(ulb_wards, function (key, value) {
            if ((value.urban_body_code == municipality)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
        }
        else {
          htmlOption = '<option value="">----Select----</option>';
        }
        $('#filter_2').html(htmlOption);
      });
      $('#bank_ifsc').blur(function () {
        $ifsc_data = $.trim($('#bank_ifsc').val());
        $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
        if ($ifscRGEX.test($ifsc_data)) {
          $('#bank_ifsc').removeClass('has-error');
          $('#error_bank_ifsc_code').text('');
          $('#ifsc_loader').show();
          $('.btnUpdate').attr('disabled', true);
          $.ajax({
            type: 'POST',
            url: "{{ route('bankIfsc') }}",
            data: {
              ifsc: $ifsc_data,
              _token: '{{ csrf_token() }}',
            },
            success: function (data) {
              $('#ifsc_loader').hide();
              $('.btnUpdate').removeAttr('disabled', true);
              if (data.status == 2) {
                $.confirm({
                  title: 'IFSC Not Found!',
                  type: 'blue',
                  icon: 'fa fa-info',
                  content: 'This ' + $ifsc_data + ' IFSC is not registered in our system.',


                });
                $('#bank_ifsc').val('');
                return false;
              }
              else {
                $('#bank_name').val(data.bank_details.bank);
                $('#branch_name').val(data.bank_details.branch);
              }


            },
            error: function (ex) {
              $('#ifsc_loader').hide();
              $('#error_bank_ifsc_code').text('Data fetch error');
              $('#bank_ifsc').addClass('has-error');
            }
          });

        } else {
          $('#error_bank_ifsc_code').text('IFSC format invalid please check the code');
          $('#bank_ifsc').addClass('has-error');
        }
      });


      $(document).on('click', '.btnUpdate', function () {

        var error_name_of_bank = '';
        var error_bank_branch = '';
        var error_bank_account_number = '';
        var error_confirm_bank_account_number = '';

        var error_bank_ifsc_code = '';
        var error_mobile_no = '';
        var error_beneficiary_fname = '';
        var error_beneficiary_mname = '';
        var error_beneficiary_lname = '';

        if ($.trim($('#mobile_no').val()).length == 0) {
          error_mobile_no = 'Mobile Number is required';
          $('#error_mobile_no').text(error_mobile_no);
          $('#mobile_no').addClass('has-error');
        }
        else if ($.trim($('#mobile_no').val()).length != 10) {
          error_mobile_no = 'Mobile Number must be 10 digit';
          $('#error_mobile_no').text(error_mobile_no);
          $('#mobile_no').addClass('has-error');
        }
        else {
          error_mobile_no = '';
          $('#error_mobile_no').text(error_mobile_no);
          $('#mobile_no').removeClass('has-error');
        }

        if ($.trim($('#bank_name').val()).length == 0) {
          error_name_of_bank = 'Name of Bank is required';
          $('#error_name_of_bank').text(error_name_of_bank);
          $('#bank_name').addClass('has-error');
        }
        else {
          error_name_of_bank = '';
          $('#error_name_of_bank').text(error_name_of_bank);
          $('#bank_name').removeClass('has-error');
        }

        if ($.trim($('#branch_name').val()).length == 0) {
          error_bank_branch = 'Bank Branch is required';
          $('#error_bank_branch').text(error_bank_branch);
          $('#branch_name').addClass('has-error');
        }
        else {
          error_bank_branch = '';
          $('#error_bank_branch').text(error_bank_branch);
          $('#branch_name').removeClass('has-error');
        }

        if ($.trim($('#bank_account_number').val()).length == 0) {
          error_bank_account_number = 'Bank Account Number is required';
          $('#error_bank_account_number').text(error_bank_account_number);
          $('#bank_account_number').addClass('has-error');
        }
        else {
          error_bank_account_number = '';
          $('#error_bank_account_number').text(error_bank_account_number);
          $('#bank_account_number').removeClass('has-error');
        }

        if ($.trim($('#confirm_bank_account_number').val()).length == 0) {
          error_confirm_bank_account_number = 'Bank Account Number is required';
          $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
          $('#confirm_bank_account_number').addClass('has-error');
        }
        else {
          error_confirm_bank_account_number = '';
          $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
          $('#confirm_bank_account_number').removeClass('has-error');
        }
        // Check Bank Account Number with Confirm Bank Account Number
        if ($.trim($('#bank_account_number').val()) != $.trim($('#confirm_bank_account_number').val())) {

          error_confirm_bank_account_number = 'Confirm Bank Account Number not Match with Bank Account Number';
          $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
          $('#confirm_bank_account_number').addClass('has-error');
        }
        else {
          error_confirm_bank_account_number = '';
          $('#error_confirm_bank_account_number').text(error_confirm_bank_account_number);
          $('#confirm_bank_account_number').removeClass('has-error');
        }


        if ($.trim($('#bank_ifsc').val()).length == 0) {
          error_bank_ifsc_code = 'IFS Code is required';
          $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
          $('#bank_ifsc').addClass('has-error');
        }
        else {
          error_bank_ifsc_code = '';
          $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
          $('#bank_ifsc').removeClass('has-error');
        }

        $ifsc_data = $.trim($('#bank_ifsc').val());
        $ifscRGEX = /^[a-z]{4}0[a-z0-9]{6}$/i;
        if ($ifscRGEX.test($ifsc_data)) {
          error_bank_ifsc_code = '';
          $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
          $('#bank_ifsc').removeClass('has-error');
        }
        else {
          error_bank_ifsc_code = 'Please check IFS Code format';
          $('#error_bank_ifsc_code').text(error_bank_ifsc_code);
          $('#bank_ifsc').addClass('has-error');
        }
        // var fname=$('#ben_fname_value').val();
        // var mname=$('#ben_mname_value').val();
        // var lname=$('#ben_lname_value').val();
        // !/^[a-zA-Z]*$/g.test(value)
        // if(!(inputValue >= 65 && inputValue <= 120) && (inputValue != 32 && inputValue != 0)) {  {
        //     error_beneficiary_fname = 'Name consists only alphabatic characters.';
        //  $('#error_beneficiary_fname').text(error_beneficiary_fname);
        //  $('#ben_fname_value').addClass('has-error');
        //       return false;
        //   }

        if (error_name_of_bank != '' || error_bank_branch != '' || error_bank_account_number != '' || error_bank_ifsc_code != '' || error_confirm_bank_account_number != '')
        // if(error_name_of_bank !='' )
        {

          return false;
        }
        else {
          var old_bank_ifsc = $('#old_bank_ifsc').val();
          var old_bank_accno = $('#old_bank_accno').val();

          var bank_ifsc = $('#bank_ifsc').val();
          var bank_account_number = $('#bank_account_number').val();
          var upload_bank_passbook = $('#upload_bank_passbook')[0].files;


          if ((old_bank_ifsc != bank_ifsc || bank_account_number != old_bank_accno) && upload_bank_passbook.length == 0) {
            $.confirm({
              title: 'Required!',
              type: 'red',
              icon: 'fa fa-warning',
              content: 'Please upload bank passbook copy.',


            });

            return false;

          }



          var bank_name = $('#bank_name').val();
          var branch_name = $('#branch_name').val();
          var benId = $('#benId').val();
          var faildTableId = $('#faildTableId').val();
          var ben_fname_value = $('#ben_fname_value').val();
          var ben_mname_value = $('#ben_mname_value').val();
          var ben_lname_value = $('#ben_lname_value').val();
          var statusCode = $('#statusCode').val();
          var application_id = $('#application_id').val();
          var token = '{{csrf_token()}}';
          var fd = new FormData();
          fd.append('benId', benId);
          fd.append('faildTableId', faildTableId);
          fd.append('bank_ifsc', bank_ifsc);
          fd.append('bank_name', bank_name);
          fd.append('bank_account_number', bank_account_number);
          fd.append('branch_name', branch_name);
          fd.append('ben_fname_value', ben_fname_value);
          fd.append('ben_mname_value', ben_mname_value);
          fd.append('ben_lname_value', ben_lname_value);
          fd.append('statusCode', statusCode);
          fd.append('upload_bank_passbook', upload_bank_passbook[0]);
          fd.append('_token', token);
          fd.append('old_bank_ifsc', old_bank_ifsc);
          fd.append('old_bank_accno', old_bank_accno);
          fd.append('application_id', application_id);
          $('#loadingDivModal').show();
          $('.btnUpdate').attr('disabled', true);
          $.ajax({
            type: 'post',
            url: "{{route('updateBankDetails')}}",
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
              $('#loadingDivModal').hide();
              showConfirm({
                title: response.title,
                type: response.type,
                icon: response.icon,
                content: response.msg,
                buttons: {
                  Ok: function () {

                    $('.btnUpdate').removeAttr('disabled', true);
                    if (response.status != 5) {
                      $('.ben_bank_modal').modal('hide');
                      dataTable.ajax.reload();
                    }
                    //$('#loadingDiv').show();

                  }
                }
              });
            },
            complete: function () {
              //  $('.btnUpdate').removeAttr('disabled',true);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('.btnUpdate').removeAttr('disabled', true);
              $('#loadingDivModal').hide();
              ajax_error(jqXHR, textStatus, errorThrown)

            }
          });


        }

      });

    });
    function View_encolser_modal(doc_name, doc_type, is_profile_pic) {
      var application_id = $('#application_id').val();
      var benId = $('#benId').val();
      $('#encolser_name').html('');
      $('#encolser_content').html('');
      $('#encolser_name').html(doc_name + '(' + benId + ')');
      $('#encolser_content').html('<img   width="50px" height="50px" src="images/ZKZg.gif"/>');
      $('#loadingDivModal').show();
      $('.btnUpdate').attr('disabled', true);
      $.ajax({
        url: "{{ route('ajaxViewPassbook') }}",
        type: "POST",
        data: {
          doc_type: doc_type,
          is_profile_pic: is_profile_pic,
          application_id: application_id,
          _token: '{{ csrf_token() }}',
        },
      }).done(function (data, textStatus, jqXHR) {
        $('.btnUpdate').removeAttr('disabled', true);
        $('#loadingDivModal').hide();
        $('#encolser_content').html('');
        $('#encolser_content').html(data);
        $("#encolser_modal").modal();
      }).fail(function (jqXHR, textStatus, errorThrown) {
        $('#encolser_content').html('');
        $('.btnUpdate').removeAttr('disabled', true);
        $('#loadingDivModal').hide();
        ajax_error(jqXHR, textStatus, errorThrown)
      });
    }
    function redirectPost(url, data, method = 'post') {
      var form = document.createElement('form');
      form.method = method;
      form.action = url;
      for (var name in data) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = data[name];
        form.appendChild(input);
      }
      $('body').append(form);
      form.submit();
    }

  </script>
@endpush