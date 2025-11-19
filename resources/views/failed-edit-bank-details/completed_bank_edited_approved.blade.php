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

    <!-- Header -->
    <section class="content-header">
      <h1>Bank Failed List</h1>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="card card-default">
        <div class="card-body">

          <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

          <!-- FILTER CARD -->
          <div class="card card-default">
            <div class="card-header">
              <h3 class="card-title">Filter Here</h3>
            </div>

            <div class="card-body p-2">

              <!-- Alerts -->
              <div class="row">
                @if ($message = Session::get('success'))
                  <div class="alert alert-success alert-dismissible fade show w-100">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                @endif

                @if(count($errors) > 0)
                  <div class="alert alert-danger alert-dismissible fade show w-100">
                    <ul>
                      @foreach($errors->all() as $error)
                        <li><strong>{{ $error }}</strong></li>
                      @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                @endif
              </div>

              <!-- Filter Fields -->
              <div class="row g-2">

                <div class="form-group col-md-2">
                  <label>Rural/Urban</label>
                  <select name="filter_1" id="filter_1" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach ($levels as $key => $value)
                      <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-3">
                  <label id="blk_sub_txt">Block/Sub Division</label>
                  <select name="filter_2" id="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div>

                <div class="form-group col-md-5 d-flex align-items-end">
                  <button type="button" id="filter" class="btn btn-success me-2">Search</button>
                  <button type="button" id="excel_btn" class="btn btn-primary me-2">Get Excel</button>
                  <button type="button" id="reset" class="btn btn-warning">Reset</button>
                </div>

              </div>

            </div>
          </div>

          <!-- RESULTS CARD -->
          <div class="card card-default mt-3">
            <div class="card-header">
              <h3 class="card-title">List of Beneficiaries Account Yet To Be Approved</h3>
            </div>

            <div class="card-body p-2">

              <div class="table-responsive">
                <table id="example" class="data-table">
                  <thead style="font-size: 12px;">
                    <tr>
                      <th>Serial No</th>
                      <th>Beneficiary ID</th>
                      <th>Beneficiary Name</th>
                      <th>Block/Sub-Division Names</th>
                      <th>Swasthya Sathi Card No</th>
                      <th>Mobile No</th>
                      <th>Failed Type</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody style="font-size: 14px;"></tbody>
                </table>
              </div>

            </div>
          </div>

        </div>
      </div>

      <!-- MODAL -->
      <div class="modal fade ben_bank_modal" id="benBankModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
              <h3 class="modal-title">Beneficiary Details (<span id="ben_id_modal"></span>)</h3>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

              <div id="loadingDivModal"></div>

              <input type="hidden" id="benId" name="benId">
              <input type="hidden" id="faildTableId" name="faildTableId">

              <!-- PERSONAL DETAILS -->
              <div class="card mb-3">
                <div class="card-header">
                  <h5 class="card-title">Personal Details</h5>
                </div>

                <div class="card-body p-2">
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
                        <th>Date of Birth</th>
                        <td id="dob_text"></td>
                      </tr>
                      <tr>
                        <th>Caste</th>
                        <td id="caste_text"></td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- BANK DETAILS -->
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title">Bank Details</h5>
                </div>

                <div class="card-body p-2">

                  <h4 class="text-center text-danger">Failed Reason:- <span id="failed_reason"></span></h4>

                  <table class="table table-bordered">
                    <tbody>

                      <tr>
                        <th class="required">Mobile Number</th>
                        <td>
                          <input type="text" id="mobile_no" name="mobile_no" maxlength="10" disabled>
                        </td>

                        <th class="required">Bank IFSC Code</th>
                        <td>
                          <input type="text" id="bank_ifsc" name="bank_ifsc"
                            onkeyup="this.value=this.value.toUpperCase();">
                          <img src="{{ asset('images/ajaxgif.gif') }}" width="60px" id="ifsc_loader"
                            style="display:none;">
                          <span class="text-danger" id="error_bank_ifsc_code"></span>
                        </td>
                      </tr>

                      <tr>
                        <th class="required">Bank Name</th>
                        <td>
                          <input type="text" id="bank_name" name="bank_name" readonly>
                          <span class="text-danger" id="error_name_of_bank"></span>
                        </td>

                        <th class="required">Bank Branch Name</th>
                        <td>
                          <input type="text" id="branch_name" name="branch_name" readonly>
                          <span class="text-danger" id="error_bank_branch"></span>
                        </td>
                      </tr>

                      <tr>
                        <th class="required">Bank Account Number</th>
                        <td colspan="3">
                          <input type="text" id="bank_account_number" name="bank_account_number" maxlength="20">
                          <span class="text-danger" id="error_bank_account_number"></span>
                        </td>
                      </tr>

                    </tbody>
                  </table>

                </div>
              </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
              <button type="button" class="btn btn-primary btnUpdate">Update</button>
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
      // $('#loadingDiv').hide();
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #bankTrFailed').addClass("active");
      $('.sidebar-menu #accValTrFailedApproved').addClass("active");

      $('#excel_btn').click(function () {
        var token = "{{csrf_token()}}";
        var filter_1 = $('#filter_1').val();
        var filter_2 = $('#filter_2').val();

        var data = { '_token': token, 'filter_1': filter_1, 'filter_2': filter_2 };
        redirectPost('getBankFailedexcel', data);
      });



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
          url: "{{ url('completedBankValidationApproved') }}",
          type: "POST",
          data: function (d) {
            d.filter_1 = $('#filter_1').val(),
              d.filter_2 = $('#filter_2').val(),
              d.block_ulb_code = $('#block_ulb_code').val(),
              d.gp_ward_code = $('#gp_ward_code').val(),
              d._token = "{{csrf_token()}}"
          },

          error: function (jqXHR, textStatus, errorThrown) {
            $('#loadingDiv').hide();
            $('.content').removeClass('disabledcontent');
            ajax_error(jqXHR, textStatus, errorThrown)
          }
        },
        "initComplete": function () {
          //console.log('Data rendered successfully');
          $('.content').removeClass('disabledcontent');
          $('#loadingDiv').hide();
        },
        "columns": [
          { "data": "DT_RowIndex" },
          { "data": "beneficiary_id" },
          { "data": "name" },
          { "data": "block_ulb_name" },
          { "data": "ss_card_no" },
          { "data": "mobile_no" },
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

            title: "Account Approved  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
              $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
              $date = $date->format('F j, Y g:i:a');
            echo $date;@endphp ",
            messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');
              $date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));
              $date = $date->format('F j, Y g:i:a');
            echo $date; @endphp ",
            footer: true,
            orientation: 'landscape',
            // pageSize : 'LEGAL',
            pageMargins: [40, 60, 40, 60],
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6],

            }
          },
          {
            extend: 'excel',

            title: "Account Approved  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');
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

              columns: [0, 1, 2, 3, 4, 5, 6],

              stripHtml: true,
            }
          },

        ],
      });
      // --------------- Filter Section -------------------- //
      $('#filter').click(function () {
        if ($('#filter_1').val() == '') {
          $.alert({
            title: "Alert!!",
            content: "Please Select Filter Criteria"
          });
        }
        else {
          dataTable.ajax.reload();
        }
      });

      $('#reset').click(function () {
        $('#filter_1').val('').trigger('change');
        $('#filter_2').val('').trigger('change');
        $('#block_ulb_code').val('').trigger('change');
        $('#gp_ward_code').val('').trigger('change');
        dataTable.ajax.reload();
      });
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
  </script>
@endpush