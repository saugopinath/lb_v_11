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

    #acc_reject {
      font-size: 20px;
    }
  </style>

@endpush
@section('content')
  <!-- <div class="content-wrapper"> -->

    <section class="content">

      <div class="card card-default">
        <div class="card-body">

          <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">

          <!-- Filter Box -->
          <div class="card card-default">
            <div class="card-header">
              <h4><b>Modified Duplicate Aadhaar (Approved)</b></h4>
            </div>

            <div class="card-body p-2">

              <div class="row">

                <div class="col-md-3">
                  <label class="control-label">Rural/Urban</label>
                  <select name="filter_1" id="filter_1" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach ($levels as $key => $value)
                      <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="control-label" id="blk_sub_txt">Block/Sub Division</label>
                  <select name="filter_2" id="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div>

                <div class="col-md-3" id="municipality_div" style="display:none;">
                  <label class="control-label">Municipality</label>
                  <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                    <option value="">-----All----</option>
                  </select>
                </div>

                <div class="col-md-3" id="gp_ward_div" style="display:none;">
                  <label class="control-label" id="gp_ward_txt">GP/Ward</label>
                  <select name="gp_ward_code" id="gp_ward_code" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label>Caste</label>
                  <select name="caste_category" id="caste_category" class="form-control">
                    <option value="">--All--</option>
                    @foreach(Config::get('constants.caste_lb') as $key => $val)
                      <option value="{{$key}}">{{$val}}</option>
                    @endforeach
                  </select>
                  <span id="error_caste_category" class="text-danger"></span>
                </div>

              </div>

              <div class="row mt-3">

                <div class="col-md-2">
                  <button type="button" id="filter" class="btn btn-success w-100">Search</button>
                </div>

                <div class="col-md-2">
                  <button type="button" id="reset" class="btn btn-warning w-100">Reset</button>
                </div>

              </div>

            </div>
          </div>

          <!-- Table Box -->
          <div class="card card-default">
            <div class="card-body p-2" style="font-size:14px;">

              @if (($message = Session::get('success')))
                <div class="alert alert-success alert-dismissible fade show mx-3">
                  <strong>{{ $message }}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              @endif

              @if (($error = Session::get('error')))
                <div class="alert alert-danger alert-dismissible fade show mx-3">
                  <strong>{{ $error }}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              @endif

              <div class="row">
                <div class="col-md-3" id="approve_rejdiv" style="display:none;">
                  <button type="button" id="bulk_approve" value="approve" class="btn btn-danger btn-lg">
                    Approve
                  </button>
                </div>
              </div>

              <div class="table-responsive">

                <table id="example" class="data-table" width="100%">
                  <thead style="font-size:12px;">
                    <tr>
                      <th>Mobile No</th>
                      <th>Duare Sarkar Registration No.</th>
                      <th>Application ID</th>
                      <th>Beneficiary Name</th>
                      <th>Aadhaar No.</th>
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

    </section>

  <!-- </div> -->

  <!-- Approve Modal -->
  <div id="modalApproval" class="modal fade" tabindex="-1">

    <form method="POST" action="{{ route('dupAadhaarApproved') }}" id="dupApproved">
      @csrf

      <input type="hidden" id="application_id" name="application_id" />
      <input type="hidden" id="applicantId" name="applicantId" />
      <input type="hidden" id="is_bulk" name="is_bulk" value="0" />
      <input type="hidden" id="accept_reject_type" name="accept_reject_type" value="A" />

      <div class="modal-dialog modal-confirm">
        <div class="modal-content">

          <div class="modal-header flex-column">
            <h4 class="modal-title w-100">Are you sure?</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <p>
              Do you really want to <span id="acc_reject">Approved</span>
              aadhar changes
              <span id="singleInfo">of the application (<span id="application_text_approve"></span>)</span>?
            </p>

            <div class="col-md-4">
              <label>Enter Remarks</label>
              <textarea name="accept_reject_comments" id="accept_reject_comments" class="form-control"
                style="height:40px;"></textarea>
            </div>
          </div>

          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

            <button type="submit" class="btn btn-danger modal-submitapprove">OK</button>

            <button type="button" id="submittingapprove" class="btn btn-success btn-lg" disabled style="display:none;">
              Submitting please wait
            </button>
          </div>

        </div>
      </div>

    </form>

  </div>

  <!-- Encloser Modal -->
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
  <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
    $(document).ready(function () {
      $("#submitting").hide();
      $("#submittingapprove").hide();
      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var base_url = '{{ url('/') }}';

      $('.loader_img').hide();
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #lk-main').addClass("active");
      $('.sidebar-menu #dup_aadhar_approved').addClass("active");
      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var base_url = '{{ url('/') }}';
      $('#opreation_type').val('A');
      $("#verifyReject").html("Approve");
      $('#div_rejection').hide();
      $("#dupApproved #is_bulk").val(0);
      $('#dupApproved #application_id').val('');
      $('#dupApproved #applicantId').val('');
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
        "ajax":
        {
          url: "{{ url('lb-dup-aadhar-list-approved-approver') }}",
          type: "post",
          data: function (d) {
            d.filter_1 = $("#filter_1").val(),
              d.filter_2 = $("#filter_2").val(),
              d.block_ulb_code = $('#block_ulb_code').val(),
              d.caste_category = $('#caste_category').val(),
              d.gp_ward_code = $('#gp_ward_code').val(),
              d._token = "{{csrf_token()}}"
          },
          error: function (jqXHR, textStatus, errorThrown) {
            $('.preloader1').hide();
            //alert(sessiontimeoutmessage);
            // window.location.href=base_url;
          }
        },
        "initComplete": function () {
          //console.log('Data rendered successfully');
        },
        "columns": [
          { "data": "mobile_no" },
          { "data": "duare_sarkar_registration_no" },
          { "data": "id" },
          { "data": "name" },
          { "data": "aadhar_no" },
          { "data": "view" },
          { "data": "check" }
        ],
        "columnDefs": [
          {
            "targets": [0, 1],
            "visible": false,
            "searchable": true
          },

        ],
        "buttons": [
          {
            extend: 'pdf',

            title: 'Duplicate Aadhar(Approved) Report  <?php echo date('d-m-Y'); ?>',
            messageTop: 'Date:<?php echo date('d/m/Y'); ?>',
            footer: true,
            pageSize: 'A4',
            // orientation: 'landscape',
            pageMargins: [40, 60, 40, 60],
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5],

            }
          },
          {
            extend: 'excel',

            title: 'Duplicate Aadhar(Approved) Report <?php echo date('d-m-Y'); ?>',
            messageTop: 'Date:<?php echo date('d/m/Y'); ?>',
            footer: true,
            pageSize: 'A4',
            //orientation: 'landscape',
            pageMargins: [40, 60, 40, 60],
            exportOptions: {
              format: {
                body: function (data, row, column, node) {
                  return column === 0 || column === 1 || column === 2 || column === 4 || column === 5 ? "\0" + data : data;
                }
              },
              columns: [0, 1, 2, 3, 4, 5],
              stripHtml: false,
            }
          },

        ],
      });





      $(document).on('click', '.ben_doc_button', function () {
        $('.ben_doc_button').attr('disabled', true);
        $('.ben_approve_button').attr('disabled', true);
        $('#bulk_approve').attr('disabled', true);
        var benid = $(this).val();
        View_encolser_modal('Copy of Aadhar Card', 6, 0, benid);
      });
      $(document).on('click', '.ben_approve_button', function () {
        $("#acc_reject").text('Approve');
        $('#dupApproved #application_id').val('');
        $('.ben_approve_button').attr('disabled', false);
        $('.ben_reject_button').attr('disabled', false);
        $('.ben_doc_button').attr('disabled', false);
        $('#bulk_approve').attr('disabled', true);
        var benid = $(this).val();
        $("#singleInfo").show();
        $('#btnApprove_' + benid).attr('disabled', true);
        $('#dupApproved #application_id').val(benid);
        $('#application_text_approve').text(benid);
        $("#dupApproved #is_bulk").val(0);
        $('#dupApproved #accept_reject_type').val('A');
        $('#modalApproval').modal('show');
      });
      $(document).on('click', '.ben_reject_button', function () {
        $("#acc_reject").text('Revert');
        $('#dupApproved #application_id').val('');
        $('.ben_approve_button').attr('disabled', false);
        $('.ben_reject_button').attr('disabled', false);
        $('.ben_doc_button').attr('disabled', false);
        $('#bulk_approve').attr('disabled', true);
        var benid = $(this).val();
        $("#singleInfo").show();
        $('#btnReject_' + benid).attr('disabled', true);
        $('#dupApproved #application_id').val(benid);
        $('#application_text_approve').text(benid);
        $("#dupApproved #is_bulk").val(0);
        $('#dupApproved #accept_reject_type').val('R');
        $('#modalApproval').modal('show');

      });
      $('#encolser_modal').on('hidden.bs.modal', function () {
        $('.ben_approve_button').attr('disabled', false);
        $('.ben_reject_button').attr('disabled', false);
        $('#bulk_approve').attr('disabled', false);
      });

      $('#modalApproval').on('hidden.bs.modal', function () {
        $('.ben_approve_button').attr('disabled', false);
        $('.ben_reject_button').attr('disabled', false);
        $('#bulk_approve').attr('disabled', false);
        $("#acc_reject").text('Approve');
        $("#dupApproved #is_bulk").val(0);
        $('#dupApproved #application_id').val('');
        $('#dupApproved #applicantId').val('');
        $('#dupApproved #accept_reject_type').val('A');
        $("#singleInfo").show();
      });

      $('.modal-submitapprove').on('click', function () {

        $(".modal-submitapprove").hide();
        $("#submittingapprove").show();
        $("#dupApproved").submit();
      });
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
            // console.log(sub_district_code);
            //console.log(select_district_code);

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
          alert('Please Select Rural/Urban First');
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
      $('#filter').click(function () {

        dataTable.ajax.reload();


      });

      $('#reset').click(function () {
        window.location.href = 'lb-dup-aadhar-list-approved-approver';
      });

      $('#check_all_btn').on('change', function () {


        var checked = $(this).prop('checked');

        dataTable.cells(null, 7).every(function () {
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

        $("#dupApproved #applicantId").val($.unique(applicantId));
        if (anyBoxesChecked == true) {
          $('#approve_rejdiv').show();
          $('.ben_view_button').attr('disabled', true);
          document.getElementById('bulk_approve').disabled = false;
          // document.getElementById('bulk_blkchange').disabled = false;
        } else {
          $('#approve_rejdiv').hide();
          $('.ben_view_button').removeAttr('disabled', true);
          document.getElementById('bulk_approve').disabled = true;
          // document.getElementById('bulk_blkchange').disabled = true;
        }
        //console.log(applicantId);
      });

      $('#bulk_approve').click(function () {
        $("#singleInfo").hide();
        $("#dupApproved #is_bulk").val(1);
        $('#dupApproved #id').val('');
        $('#dupApproved #application_id').val('');
        benid = "";
        $('.ben_approve_button').attr('disabled', true);
        $('#modalApproval').modal('show');
      });

    });

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
        $('.ben_approve_button').attr('disabled', false);
        $("#encolser_modal").modal('show');
      }).fail(function (jqXHR, textStatus, errorThrown) {
        $('.ben_doc_button').attr('disabled', false);
        $('.ben_approve_button').attr('disabled', false);
        $('#encolser_content').html('');
        alert(sessiontimeoutmessage);
        window.location.href = base_url;
      });
    }
    function controlCheckBox() {
      var anyBoxesChecked = false;
      var applicantId = Array();
      $(' input[type="checkbox"]').each(function () {
        if ($(this).is(":checked")) {
          anyBoxesChecked = true;
          applicantId.push($(this).val());
        }

      });
      $("#dupApproved #applicantId").val($.unique(applicantId));
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
      //console.log(applicantId);
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