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

    .disabledcontent {
      opacity: 0.6;
      pointer-events: none;
    }
  </style>

@endpush
@section('content')
  <!-- <div class="content-wrapper"> -->

    <!-- Page Header -->
    <section class="content-header">
      <h1>Account Verified</h1>
    </section>

    <section class="content">
      <div class="card card-default">
        <div class="card-body">

          <input type="hidden" id="dist_code" name="dist_code" value="{{ $dist_code }}" class="js-district_1">

          <!-- FILTER CARD -->
          <div class="card card-default">
            <div class="card-header">
              <span id="panel-icon">Filter Here</span>
            </div>

            <div class="card-body p-2">

              <!-- ALERTS -->
              <div class="row">
                @if ($message = Session::get('success'))
                  <div class="alert alert-success alert-dismissible fade show">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                @endif

                @if (count($errors) > 0)
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

              <!-- SUBDIV VERIFIER -->
              @if($duty_level == 'SubdivVerifier' || $duty_level == 'SubdivDelegated Verifier')
                <div class="row">

                  <div class="col-md-2">
                    <label class="form-label">Municipality</label>
                    <select id="filter_1" name="filter_1" class="form-control js-municipality">
                      <option value="">-----Select----</option>
                      @foreach ($ulb_gp as $urban_body)
                        <option value="{{ $urban_body->urban_body_code }}">
                          {{ $urban_body->urban_body_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label" id="blk_sub_txt">Wards</label>
                    <select id="filter_2" name="filter_2" class="form-control">
                      <option value="">-----Select----</option>
                    </select>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Failed Type</label>
                    <select id="failed_type" name="failed_type" class="form-control">
                      <option value="">-----All----</option>
                      @foreach(Config::get('globalconstants.failed_type') as $key => $val)
                        @if($key <> 4)
                          <option value="{{ $key }}">{{ $val }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">&nbsp;</label><br>
                    <button id="filter" class="btn btn-success">
                      <i class="fa fa-search"></i> Search
                    </button>
                    <button id="reset" class="btn btn-warning">
                      <i class="fa fa-refresh"></i> Reset
                    </button>
                  </div>

                </div>

                <!-- BLOCK VERIFIER -->
              @elseif($duty_level == 'BlockVerifier' || $duty_level == 'BlockDelegated Verifier')
                <div class="row">

                  <div class="col-md-3">
                    <label class="form-label">Gram Panchayat</label>
                    <select id="filter_1" name="filter_1" class="form-control">
                      <option value="">-----Select----</option>
                      @foreach ($ulb_gp as $gp)
                        <option value="{{ $gp->gram_panchyat_code }}">
                          {{ $gp->gram_panchyat_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Failed Type</label>
                    <select id="failed_type" name="failed_type" class="form-control">
                      <option value="">-----All----</option>
                      @foreach(Config::get('globalconstants.failed_type') as $key => $val)
                        @if($key <> 4)
                          <option value="{{ $key }}">{{ $val }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">&nbsp;</label><br>
                    <button id="filter" class="btn btn-success">
                      <i class="fa fa-search"></i> Search
                    </button>
                    <button id="reset" class="btn btn-warning">
                      <i class="fa fa-refresh"></i> Reset
                    </button>
                  </div>

                </div>
              @endif

            </div>
          </div>

          <!-- BENEFICIARY LIST CARD -->
          <div class="card card-default mt-3">
            <div class="card-header" id="panel_head">
              List of Account Verified Beneficiaries
            </div>

            <div class="card-body p-2" style="font-size:14px;">
              <div class="table-responsive">
                <table id="example" class="table table-bordered table-striped w-100">
                  <thead style="font-size:12px;">
                    <tr>
                      <th>Serial No</th>
                      <th>Beneficiary ID</th>
                      <th>Beneficiary Name</th>
                      <th>Block/ Municipality Name</th>
                      <th>GP/Ward Name</th>
                      <th>New Account No</th>
                      <th>New IFSC Code</th>
                      <th>Failure Type</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody style="font-size:14px;"></tbody>
                </table>
              </div>
            </div>
          </div>

        </div><!-- card-body -->
      </div><!-- card -->
    </section>
  <!-- </div> -->

  <!-- MODAL -->
  <div class="modal fade ben_bank_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header singleInfo">
          <h3 class="modal-title">Beneficiary Details (<span id="ben_id_modal"></span>)</h3>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div id="loadingDivModal"></div>

          <input type="hidden" id="benId" name="benId">
          <input type="hidden" id="faildTableId" name="faildTableId">

          <!-- PERSONAL DETAILS -->
          <div class="card card-default mb-3">
            <div class="card-header">
              <a data-bs-toggle="collapse" href="#collapsePersonal" class="text-decoration-none">
                Personal Details
              </a>
            </div>

            <div id="collapsePersonal" class="collapse show">
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
                      <th>DOB</th>
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
          </div>

          <!-- BANK DETAILS -->
          <div class="card card-default">
            <div class="card-header">
              <a data-bs-toggle="collapse" href="#collapseBank" class="text-decoration-none">
                Bank Details
              </a>
            </div>

            <div id="collapseBank" class="collapse show">
              <div class="card-body">

                <div class="text-center mb-3">
                  <h4 class="text-danger">Failed Reason: <span id="failed_reason"></span></h4>
                </div>

                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <th>Mobile Number</th>
                      <td><input id="mobile_no" name="mobile_no" class="form-control" disabled></td>

                      <th>Bank IFSC Code</th>
                      <td>
                        <input id="bank_ifsc" name="bank_ifsc" class="form-control"
                          onkeyup="this.value=this.value.toUpperCase();">
                        <img src="{{ asset('images/ajaxgif.gif') }}" width="60" id="ifsc_loader" style="display:none;">
                        <span id="error_bank_ifsc_code" class="text-danger"></span>
                      </td>
                    </tr>

                    <tr>
                      <th>Bank Name</th>
                      <td>
                        <input id="bank_name" class="form-control" readonly>
                        <span id="error_name_of_bank" class="text-danger"></span>
                      </td>

                      <th>Bank Branch Name</th>
                      <td>
                        <input id="branch_name" class="form-control" readonly>
                        <span id="error_bank_branch" class="text-danger"></span>
                      </td>
                    </tr>

                    <tr>
                      <th>Bank Account Number</th>
                      <td>
                        <input id="bank_account_number" class="form-control" maxlength="20">
                        <span id="error_bank_account_number" class="text-danger"></span>
                      </td>
                    </tr>

                  </tbody>
                </table>

              </div>
            </div>
          </div>

        </div><!-- modal-body -->

        <div class="modal-footer">
          <button type="button" class="btn btn-primary btnUpdate">Update</button>
        </div>

      </div>
    </div>
  </div>

@endsection
@push('scripts')
  <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
    $(document).ready(function () {
      // $('#loadingDiv').hide();
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #bankTrFailed').addClass("active");
      $('.sidebar-menu #accValverified').addClass("active");
      $('.ben_bank_modal').on('hidden.bs.modal', function (e) {
        $('#mobile_no').val('');
        $('#bank_ifsc').val('');
        $('#bank_name').val('');
        $('#branch_name').val('');
        $('#bank_account_number').val('');
        $('#benId').val('');
        $('#faildTableId').val('');

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
            $('#benId').val(response.ben_id);
            $('#faildTableId').val(response.failedid);
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



      $('.content').addClass('disabledcontent');
      var dataTable = "";
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
        "pageLength": 20,
        'lengthMenu': [[20, 50, 100], [20, 50, 100]],
        "serverSide": true,
        "processing": true,
        "bRetrieve": true,
        "oLanguage": {
          "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
        },
        ajax: {
          url: "{{ url('completedBankValidationVerified') }}",
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
            $('.content').removeClass('disabledcontent');
            ajax_error(jqXHR, textStatus, errorThrown)
          }
        },
        "initComplete": function () {
          $('.content').removeClass('disabledcontent');
          //console.log('Data rendered successfully');
          $('#loadingDiv').hide();
        },
        "columns": [
          { "data": "DT_RowIndex" },
          { "data": "beneficiary_id" },
          { "data": "name" },
          { "data": "block_ulb_name" },
          { "data": "gp_ward_name" },
          { "data": "accno" },
          { "data": "ifsc" },
          { "data": "type" },
          { "data": "status" },

        ],
        //    "columnDefs": [
        //         {
        //             "targets": [ 4,5 ],
        //             "visible": false,
        //             "searchable": true
        //         },
        //         {
        //   "targets": [ 7 ],
        //   "orderable": false,
        //   "searchable": true
        // }
        //    ],
        "buttons": [
          {
            extend: 'pdf',

            title: 'Accounts  Verified Report  <?php echo date('d-m-Y'); ?>',
            messageTop: 'Date:<?php echo date('d/m/Y'); ?>',
            footer: true,
            orientation: 'landscape',
            // pageSize : 'LEGAL',
            pageMargins: [40, 60, 40, 60],
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],

            }
          },
          {
            extend: 'excel',

            title: 'Accounts Verified Failed <?php echo date('d-m-Y'); ?>',
            messageTop: 'Date:<?php echo date('d/m/Y'); ?>',
            footer: true,
            pageSize: 'A4',
            //orientation: 'landscape',
            pageMargins: [40, 60, 40, 60],
            exportOptions: {
              format: {
                body: function (data, row, column, node) {
                  return column === 5 || column === 3 ? "\0" + data : data;
                }
              },
              columns: [0, 1, 2, 3, 4, 5, 6, 7],
              stripHtml: true,
            }
          },

        ],
      });

      $('#filter').click(function () {
        // $('#loadingDiv').show();
        // if($('#filter_1').val() == '') {
        //    $.alert({
        //      title : "Alert!!",
        //      content: "Please Select Filter Criteria"
        //    });
        //  }
        //  else {
        dataTable.ajax.reload();
        // }        
      });
      $('#reset').click(function () {
        $('#filter_1').val('').trigger('change');
        $('#filter_2').val('').trigger('change');
        $('#failed_type').val('').trigger('change');
        $('#pay_mode').val('').trigger('change');
        dataTable.ajax.reload();
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

          $.ajax({
            type: 'POST',
            url: "{{ url('legacy/getBankDetails') }}",
            data: {
              ifsc: $ifsc_data,
              _token: '{{ csrf_token() }}',
            },
            success: function (data) {
              $('#ifsc_loader').hide();
              if (!data || data.length === 0) {
                $('#error_bank_ifsc_code').text('No data found with the IFSC');
                $('#bank_ifsc').addClass('has-error');
                return;
              }
              data = JSON.parse(data);
              // console.log(data);
              $('#bank_name').val(data.bank);
              $('#branch_name').val(data.branch);
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
        var error_bank_ifsc_code = '';
        var error_mobile_no = '';

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


        if (error_name_of_bank != '' || error_bank_branch != '' || error_bank_account_number != '' || error_bank_ifsc_code != '')
        // if(error_name_of_bank !='' )
        {
          return false;
        }
        else {
          var bank_ifsc = $('#bank_ifsc').val();
          var bank_name = $('#bank_name').val();
          var bank_account_number = $('#bank_account_number').val();
          var branch_name = $('#branch_name').val();
          var benId = $('#benId').val();
          var faildTableId = $('#faildTableId').val();
          $('#loadingDivModal').show();
          $('.btnUpdate').attr('disabled', true);
          $.ajax({
            type: 'post',
            url: "{{route('updateBankDetails')}}",
            data: { _token: '{{csrf_token()}}', benId: benId, faildTableId: faildTableId, bank_ifsc: bank_ifsc, bank_name: bank_name, bank_account_number: bank_account_number, branch_name: branch_name, },
            dataType: 'json',
            success: function (response) {
              $('#loadingDivModal').hide();
              $.confirm({
                title: response.title,
                type: response.type,
                icon: response.icon,
                content: response.msg,
                buttons: {
                  Ok: function () {
                    $('.btnUpdate').removeAttr('disabled', true);
                    $('.ben_bank_modal').modal('hide');
                    //$('#loadingDiv').show();
                    dataTable.ajax.reload();
                  }
                }
              });
            },
            complete: function () {

            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loadingDivModal').hide();
              ajax_error(jqXHR, textStatus, errorThrown)

            }
          });


        }

      });

    });
  </script>
@endpush