  <style type="text/css">
      .full-width {
          width: 100% !important;
      }

      .bg-blue {
          background-image: linear-gradient(to right top, #0073b7, #0086c0, #0097c5, #00a8c6, #00b8c4) !important;
      }

      .bg-red {
          background-image: linear-gradient(to right bottom, #dd4b39, #ec6f65, #d21a13, #de0d0b, #f3060d) !important;
      }

      .bg-yellow {
          background-image: linear-gradient(to right bottom, #dd4b39, #e65f31, #ed7328, #f1881e, #f39c12) !important;
      }

      .bg-green {
          background-image: linear-gradient(to right bottom, #04736d, #008f73, #00ab6a, #00c44f, #5ddc0c) !important;
      }

      .bg-verify {
          background-image: linear-gradient(to right top, #f39c12, #f8b005, #fac400, #fad902, #f8ee15) !important;
      }

      .info-box {
          display: block;
          min-height: 90px;
          background: #b6d0ca33 !important;
          width: 100%;
          box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, 0.30) !important;
          border-radius: 2px;
          margin-bottom: 15px;
      }

      .small-box .icon {
          margin-top: 7%;
      }

      .small-box>.inner {
          padding: 10px;
          color: white;
      }

      .small-box p {
          font-size: 18px !important;
      }

      .select2 .select2-container {}

      .link-button {
          background: none;
          border: none;
          color: blue;
          text-decoration: underline;
          cursor: pointer;
          font-size: 1em;
          font-family: serif;
      }

      .link-button:focus {
          outline: none;
      }

      .link-button:active {
          color: red;
      }

      .small-box-footer-custom {
          position: relative;
          text-align: center;
          padding: 3px 0;
          color: #fff;
          color: rgba(255, 255, 255, 0.8);
          display: block;
          z-index: 10;
          background: rgba(0, 0, 0, 0.1);
          text-decoration: none;
          font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
          font-weight: 400;
          width: 100%;
      }

      .small-box-footer-custom:hover {
          color: #fff;
          background: rgba(0, 0, 0, 0.15);
      }

      th.sorting::after,
      th.sorting_asc::after,
      th.sorting_desc::after {
          content: "" !important;
      }

      .errorField {
          border-color: #990000;
      }

      .searchPosition {
          margin: 70px;
      }

      .submitPosition {
          margin: 25px 0px 0px 0px;
      }


      .typeahead {
          border: 2px solid #FFF;
          border-radius: 4px;
          padding: 8px 12px;
          max-width: 300px;
          min-width: 290px;
          background: rgba(66, 52, 52, 0.5);
          color: #FFF;
      }

      .tt-menu {
          width: 300px;
      }

      ul.typeahead {
          margin: 0px;
          padding: 10px 0px;
      }

      ul.typeahead.dropdown-menu li a {
          padding: 10px !important;
          border-bottom: #CCC 1px solid;
          color: #FFF;
      }

      ul.typeahead.dropdown-menu li:last-child a {
          border-bottom: 0px !important;
      }

      .bgcolor {
          max-width: 550px;
          min-width: 290px;
          max-height: 340px;
          background: url("world-contries.jpg") no-repeat center center;
          padding: 100px 10px 130px;
          border-radius: 4px;
          text-align: center;
          margin: 10px;
      }

      .demo-label {
          font-size: 1.5em;
          color: #686868;
          font-weight: 500;
          color: #FFF;
      }

      .dropdown-menu>.active>a,
      .dropdown-menu>.active>a:focus,
      .dropdown-menu>.active>a:hover {
          text-decoration: none;
          background-color: #1f3f41;
          outline: 0;
      }

      table.dataTable thead {
          padding-right: 20px;
      }

      table.dataTable thead>tr>th {
          padding-right: 20px;
      }

      table.dataTable thead th {
          padding: 10px 18px 10px 18px;
          white-space: nowrap;
          border-right: 1px solid #dddddd;
      }

      table.dataTable tfoot th {
          padding: 10px 18px 10px 18px;
          white-space: nowrap;
          border-right: 1px solid #dddddd;
      }

      table.dataTable tbody td {
          padding: 10px 18px 10px 18px;
          border-right: 1px solid #dddddd;
          white-space: nowrap;
          -webkit-box-sizing: content-box;
          -moz-box-sizing: content-box;
          box-sizing: content-box;
      }

      .criteria1 {
          text-transform: uppercase;
          font-weight: bold;
      }

      .item_header {
          font-weight: bold;
      }

      #example_length {
          margin-left: 40%;
          margin-top: 2px;
      }

      @keyframes spinner {
          to {
              transform: rotate(360deg);
          }
      }

      .spinner:before {
          content: '';
          box-sizing: border-box;
          position: absolute;
          top: 50%;
          left: 50%;
          width: 20px;
          height: 20px;
          margin-top: -10px;
          margin-left: -10px;
          border-radius: 50%;
          border: 2px solid #ccc;
          border-top-color: #333;
          animation: spinner .6s linear infinite;
      }

      .required-field::after {
          content: "*";
          color: red;
      }

      @media print {
          body * {
              visibility: hidden;
          }

          #ben_view_modal #ben_view_modal * {
              visibility: visible;
          }

          #ben_view_modal {
              position: absolute;
              left: 0;
              top: 0;
          }

          [class*="col-md-"] {
              float: none;
              display: table-cell;
          }

          [class*="col-lg-"] {
              float: none;
              display: table-cell;
          }

          .pagebreak {
              page-break-before: always;
          }
      }
  </style>

  @extends('pensionreportFaulty.base')
  @section('action-content')

      <!-- Main content -->
      <section class="content">
          <div class="box">
              <div class="box-header">
                  <div class="row">
                      <div class="col-sm-8">

                      </div>
                  </div>
              </div>
              <div class="box-body">
                  @if (count($errors) > 0)
                      <div class="alert alert-danger alert-block">
                          <ul>
                              @foreach ($errors->all() as $error)
                                  <li><strong> {{ $error }}</strong></li>
                              @endforeach
                          </ul>
                      </div>
                  @endif
                  <form name="formReport" id="formReport">
                      <div class="row" style="margin-bottom:1%">
                          @if (count($ds_phase_list) > 0)
                              <div class="form-group col-md-4">
                                  <label class="">Duare Sarkar Phase</label>
                                  <select class="form-control" name="ds_phase" id="ds_phase" tabindex="70">
                                      <option value="">--All--</option>
                                      @foreach (Config::get('constants.ds_phase.phaselist') as $key => $val)
                                          <option value="{{ $key }}">{{ $val }}</option>
                                      @endforeach
                                  </select>
                                  <span id="error_ds_phase" class="text-danger"></span>
                              </div>
                          @else
                              <input type="hidden" name="ds_phase" id="ds_phase" value="" />
                          @endif
                          @if ($is_rural_visible)
                              <div class="col-md-2">
                                  <label class="control-label">Rural/Urban </label>
                                  <select name="rural_urbanid" id="rural_urbanid" class="form-control">
                                      <option value="">-----All----</option>
                                      @foreach (Config::get('constants.rural_urban') as $key => $value)
                                          <option value="{{ $key }}"> {{ $value }}</option>
                                      @endforeach
                                  </select>

                              </div>
                          @else
                              <input type="hidden" name="rural_urbanid" id="rural_urbanid" value="{{ $is_urban }}" />
                          @endif
                          @if ($urban_visible)
                              <div class="col-md-3">
                                  <label class="control-label" id="blk_sub_txt">Block/Subdivision</label>
                                  <select name="urban_body_code" id="urban_body_code" class="form-control">
                                      <option value="">-----All----</option>

                                  </select>

                              </div>
                          @else
                              <input type="hidden" name="urban_body_code" id="urban_body_code"
                                  value="{{ $urban_body_code }}" />
                          @endif
                          @if ($munc_visible)
                              <div class="col-md-3" id="municipality_div">
                                  <label class="control-label">Municipality</label>
                                  <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                                      <option value="">-----All----</option>
                                      @if (count($muncList) > 0)
                                          {
                                          @foreach ($muncList as $muncArr)
                                              <option value="{{ $muncArr->urban_body_code }}">
                                                  {{ trim($muncArr->urban_body_name) }}</option>
                                          @endforeach
                                          }
                                      @endif

                                  </select>

                              </div>
                          @endif
                          @if ($gp_ward_visible)
                              <div class="form-group col-md-4" id="gp_ward_div">
                                  <label class="" id="gp_ward_txt">GP/Ward</label>

                                  <select name="gp_ward_code" id="gp_ward_code" class="form-control" tabindex="17">
                                      <option value="">--All --</option>
                                      @if (count($gpwardList) > 0)
                                          {
                                          @foreach ($gpwardList as $gp_ward_arr)
                                              <option value="{{ $gp_ward_arr->gram_panchyat_code }}">
                                                  {{ trim($gp_ward_arr->gram_panchyat_name) }}</option>
                                          @endforeach
                                          }
                                      @endif

                                  </select>
                                  <span id="error_gp_ward_code" class="text-danger"></span>

                              </div>
                          @endif
                      </div>
                      <div class="row">
                          <div class="form-group col-md-4">
                              <label class="">Caste</label>
                              <select class="form-control" name="caste_category" id="caste_category" tabindex="70">
                                  <option value="">--All--</option>
                                  @foreach (Config::get('constants.caste_lb') as $key => $val)
                                      <option value="{{ $key }}">{{ $val }}</option>
                                  @endforeach
                              </select>
                              <span id="error_caste_category" class="text-danger"></span>
                          </div>
                          <div class="col-md-2" style="margin-top: 28px;">
                              <label class=" control-label">&nbsp; </label>
                              <button type="button" name="filter" id="filter" class="btn btn-success">Filter</button>


                          </div>
                          <div class="col-md-offset-2" style="margin-top: 28px;">
                              <label class=" control-label">&nbsp; </label>

                              <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>

                          </div>

                      </div>
                  </form>

                  <form action="applicationFaultyListExcel" method="post">
                      <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                      <input type="hidden" name="type" id="type" value="{{ $type }}">
                      <input type="submit" name="submit" class="btn btn-info" value="Export All Data to Excel" />
                  </form>

                  <div id="example2_wrapper" class="col-md-12 dataTables_wrapper form-inline dt-bootstrap js-report-form">


                      <div class="col-md-offset-3 col-md-3">

                          <h4><span class="label label-primary">{{ $report_type_name }}</span></h4>

                      </div>
                      <!-- <div class="col-md-offset-1 col-md-5 btn-group" role="group" >
              <button class="btn btn-success clsbulk_approve" id="bulk_approve" disabled>Approve Selected Beneficiaries</button>
             </div> -->
                      <div class="col-md-12 text-center" id="loaderdiv" hidden>
                          <img src="{{ asset('images/ZKZg.gif') }}" width="100px" height="100px" />
                      </div>

                      <div class="col-md-12" id="reportbody" style="margin-top: 2%;">
                          <table id="example" class="display" cellspacing="0" width="100%">
                              <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                              <input type="hidden" name="district_code" id="district_code"
                                  value="{{ $district_code }}">
                              <thead>
                                  <tr role="row">
                                      @if ($type == 'A')
                                          <th>Beneficiary ID</th>
                                      @endif
                                      <th>Application ID</th>
                                      <th>Applicant Name</th>
                                      <th>Age</th>
                                      <th>Applicant Mobile No.</th>
                                      <th>Bank IFSC.</th>
                                      <th>Bank Account No.</th>
                                      @if ($type == 'F')
                                          <th>Operator Mobile No.</th>
                                          <th>Faulty Reason</th>
                                      @endif
                                      @if ($type == 'R')
                                          <th>Operator Mobile No.</th>
                                          <th>Rejected Reason</th>
                                      @endif
                                      @if ($type == 'T')
                                          <th>Operator Mobile NO.</th>
                                          <th>Reverted Reason</th>
                                      @endif


                                  </tr>
                              </thead>
                              <tfoot>
                                  <tr>


                                      @if ($type == 'A')
                                          <th>Beneficiary ID</th>
                                      @endif
                                      <th>Application ID</th>
                                      <th>Applicant Name</th>
                                      <th>Age</th>
                                      <th>Applicant Mobile No.</th>
                                      <th>Bank IFSC.</th>
                                      <th>Bank Account No.</th>
                                      @if ($type == 'F')
                                          <th>Operator Mobile No.</th>
                                          <th>Faulty Reason</th>
                                      @endif
                                      @if ($type == 'R')
                                          <th>Operator Mobile No.</th>
                                          <th>Rejected Reason</th>
                                      @endif
                                      @if ($type == 'T')
                                          <th>Operator Mobile No.</th>
                                          <th>Reverted Reason</th>
                                      @endif

                                  </tr>
                              </tfoot>

                          </table>
                          <div class="row">

                              <div class="col-sm-7">
                                  <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">

                                  </div>
                              </div>
                          </div>

                      </div>

                  </div>
                  <!--   </div> -->
      </section>
      <!-- /.content -->
      </div>

      <!-- Start Reject Model -->

      <div class="modal fade" id="ben_reject_modal" tabindex="-1">
          <div class="modal-dialog ">
              <div class="modal-content">
                  <div class="modal-header btn-danger">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                      <h4 class="modal-title">Reject Beneficiary Application</h4>
                  </div>
                  <div class="modal-body">
                      <h4>Are you sure you want to reject the application with the beneficiary details mentioned below?</h4>
                      <hr />

                      <table style="width:100%">
                          <tr>
                              <td style="width:30%;"><span class="item_header">Beneficiary Id:</span></td>
                              <td><span class="item_value" id="reject_ben_id"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Beneficiary Name:</span></td>
                              <td><span class="item_value" id="reject_ben_name"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Father's Name:</span></td>
                              <td><span class="item_value" id="reject_ben_father_name"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Bank IFSC:</span></td>
                              <td><span class="item_value" id="reject_ben_ifsc"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Account Number: </span></td>
                              <td><span class="item_value" id="reject_ben_accno"></span></td>
                          </tr>
                          <tr>
                              <td colspan="2">
                                  <hr />
                              </td>
                          </tr>
                      </table>
                      <input type="hidden" id="reject_beneficiary_id" />
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                      <button type="button" class="btn btn-danger" id="reject_Button"
                          data-dismiss="modal">Reject</button>
                  </div>
              </div>
          </div>
      </div>
      <!-- End Reject Model -->

      <!-- Start Revert Model -->

      <div class="modal fade" id="ben_revert_modal" tabindex="-1">
          <div class="modal-dialog ">
              <div class="modal-content">
                  <div class="modal-header btn-success">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                      <h4 class="modal-title ">Revert Beneficiary Application</h4>
                  </div>
                  <div class="modal-body">
                      <h4>Are you sure you want to revert the application back to <b>Verifier</b> level with the beneficiary
                          details mentioned below?</h4>
                      <hr />

                      <table style="width:100%">
                          <tr>
                              <td style="width:30%;"><span class="item_header">Beneficiary Id:</span></td>
                              <td><span class="item_value" id="revert_ben_id"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Beneficiary Name:</span></td>
                              <td><span class="item_value" id="revert_ben_name"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Father's Name:</span></td>
                              <td><span class="item_value" id="revert_ben_father_name"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Bank IFSC:</span></td>
                              <td><span class="item_value" id="revert_ben_ifsc"></span></td>
                          </tr>
                          <tr>
                              <td><span class="item_header">Account Number: </span></td>
                              <td><span class="item_value" id="revert_ben_accno"></span></td>
                          </tr>
                          <tr>
                              <td colspan="2">
                                  <hr />
                              </td>
                          </tr>
                      </table>
                      <input type="hidden" id="revert_beneficiary_id" />
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                      <button type="button" class="btn btn-success" id="revert_Button"
                          data-dismiss="modal">Revert</button>
                  </div>
              </div>
          </div>
      </div>
      <!-- End Revert Model -->


  @endsection




  <script src='{{ asset('/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}'></script>
  <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
      function display_c() {
          var refresh = 1000; // Refresh rate in milli seconds
          mytime = setTimeout('display_ct()', refresh)
      }

      function display_ct() {
          var x = new Date()
          document.getElementById('ct').innerHTML = x.toUTCString();
          display_c();
      }

      $(document).ready(function() {
          var application_type = '{{ $type }}';
          $('.sidebar-menu li').removeClass('active');
          $('.sidebar-menu #benListReport').addClass("active");
          if (application_type == 'V') {
              $('.sidebar-menu #VerifiedApplication').addClass("active");
          } else if (application_type == 'A') {
              $('.sidebar-menu #ApprovedApplication').addClass("active");
          } else if (application_type == 'T') {
              $('.sidebar-menu #RevertedApplication').addClass("active");
          } else if (application_type == 'R') {
              $('.sidebar-menu #RejectedApplication').addClass("active");
          } else if (application_type == 'F') {
              $('.sidebar-menu #FaultyApplication').addClass("active");
          } else if (application_type == 'PEL') {
              $('.sidebar-menu #PartialApplication').addClass("active");
          }
          var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
          var base_url = '{{ url('/') }}';

          display_ct();

          $(".dataTables_scrollHeadInner").css({
              "width": "100%"
          });

          $(".table ").css({
              "width": "100%"
          });

          $('#rural_urbanid').change(function() {
              var rural_urbanid = $(this).val();
              if (rural_urbanid != '') {
                  $('#urban_body_code').html('<option value="">--All --</option>');
                  $('#block_ulb_code').html('<option value="">--All --</option>');
                  $('#gp_ward_code').html('<option value="">--All --</option>');

                  select_district_code = $('#district_code').val();
                  //console.log(select_district_code);
                  var htmlOption = '<option value="">--All--</option>';
                  if (rural_urbanid == 1) {
                      $("#municipality_div").show();
                      $("#blk_sub_txt").text('Subdivision');
                      $("#gp_ward_txt").text('Ward');
                      $.each(subDistricts, function(key, value) {
                          if ((value.district_code == select_district_code)) {
                              htmlOption += '<option value="' + value.id + '">' + value.text +
                                  '</option>';
                          }
                      });
                  } else if (rural_urbanid == 2) {
                      $("#municipality_div").hide();
                      $("#blk_sub_txt").text('Block');
                      $("#gp_ward_txt").text('GP');
                      $.each(blocks, function(key, value) {
                          if ((value.district_code == select_district_code)) {
                              htmlOption += '<option value="' + value.id + '">' + value.text +
                                  '</option>';
                          }
                      });
                  }
                  $('#urban_body_code').html(htmlOption);
              } else {
                  $("#blk_sub_txt").text('Block/Subdivision');
                  $("#gp_ward_txt").text('GP/Ward');
                  $('#urban_body_code').html('<option value="">--All --</option>');
                  $('#block_ulb_code').html('<option value="">--All --</option>');
                  $('#gp_ward_code').html('<option value="">--All --</option>');
              }
          });
          $('#urban_body_code').change(function() {
              var rural_urbanid = $('#rural_urbanid').val();
              if (rural_urbanid == 1) {
                  var sub_district_code = $(this).val();

                  $('#block_ulb_code').html('<option value="">--All --</option>');
                  select_district_code = $('#district_code').val();
                  var htmlOption = '<option value="">--All--</option>';
                  // console.log(sub_district_code);
                  //console.log(select_district_code);

                  $.each(ulbs, function(key, value) {
                      if ((value.district_code == select_district_code) && (value
                              .sub_district_code == sub_district_code)) {
                          htmlOption += '<option value="' + value.id + '">' + value.text +
                              '</option>';
                      }
                  });
                  $('#block_ulb_code').html(htmlOption);
              } else if (rural_urbanid == 2) {
                  $('#muncid').html('<option value="">--All --</option>');
                  $("#municipality_div").hide();
                  var block_code = $(this).val();
                  select_district_code = $('#district_code').val();

                  var htmlOption = '<option value="">--All--</option>';
                  $.each(gps, function(key, value) {
                      if ((value.district_code == select_district_code) && (value.block_code ==
                              block_code)) {
                          htmlOption += '<option value="' + value.id + '">' + value.text +
                              '</option>';
                      }
                  });
                  //console.log(htmlOption);
                  $('#gp_ward_code').html(htmlOption);

                  $("#gp_ward_div").show();
              } else {
                  $('#block_ulb_code').html('<option value="">--All --</option>');
                  $('#gp_ward_code').html('<option value="">--All --</option>');
                  $("#municipality_div").hide();
                  $("#gp_ward_div").hide();
              }


          });

          $('#block_ulb_code').change(function() {
              var muncid = $(this).val();
              var district = $("#district_code").val();
              var urban_code = $("#rural_urbanid").val();
              if (district == '') {
                  $('#rural_urbanid').val('');
                  $('#urban_body_code').html('<option value="">--All --</option>');
                  $('#block_ulb_code').html('<option value="">--All --</option>');
                  alert('Please Select District First');
                  $("#district_code").focus();

              }
              if (urban_code == '') {
                  alert('Please Select Rural/Urban First');
                  $('#urban_body_code').html('<option value="">--All --</option>');
                  $('#block_ulb_code').html('<option value="">--All --</option>');
                  $("#urban_body_code").focus();
              }
              if (muncid != '') {
                  var rural_urbanid = $('#rural_urbanid').val();
                  if (rural_urbanid == 1) {


                      $('#gp_ward_code').html('<option value="">--All --</option>');
                      var htmlOption = '<option value="">--All--</option>';
                      $.each(ulb_wards, function(key, value) {
                          if (value.urban_body_code == muncid) {
                              htmlOption += '<option value="' + value.id + '">' + value.text +
                                  '</option>';
                          }
                      });
                      $('#gp_ward_code').html(htmlOption);

                  } else {
                      $('#gp_ward_code').html('<option value="">--All --</option>');
                      $("#gp_ward_div").hide();
                  }
              } else {
                  $('#gp_ward_code').html('<option value="">--All --</option>');
              }

          });


          $('#filter').click(function() {
              table.clear().draw();
              table.ajax.reload();

          });
          $('#reset').click(function() {
              window.location.href = 'application-list-common?type={{ $type }}';
          });
          var table = $('#example').DataTable({
              dom: "Blfrtip",
              "paging": true,
              "pageLength": 20,
              "lengthMenu": [
                  [20, 50, 80, 120, 150],
                  [20, 50, 80, 120, 150]
              ],
              "serverSide": true,
              "deferRender": true,
              "processing": true,
              "bRetrieve": true,
              "scrollX": true,
              "ordering": false,
              "language": {
                  "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
              },
              "ajax": {
                  url: "{{ url('application-list-common-faulty') }}",
                  type: "POST",
                  data: function(d) {
                      d.ds_phase = $('#ds_phase').val(),
                          d.district_code = "{{ $district_code }}",
                          d.rural_urbanid = $('#rural_urbanid').val(),
                          d.urban_body_code = $('#urban_body_code').val(),
                          d.block_ulb_code = $('#block_ulb_code').val(),
                          d.gp_ward_code = $('#gp_ward_code').val(),
                          d.caste = $('#caste_category').val(),
                          d._token = "{{ csrf_token() }}",
                          d.scheme = "{{ $scheme }}",
                          d.type = "{{ $type }}"
                  },
                  error: function(ex) {
                      alert(sessiontimeoutmessage);
                      //window.location.href=base_url;  
                      //console.log(ex);
                  }
              },
              "columns": [
                  @if ($type == 'A')
                      {
                          "data": "beneficiary_id"
                      },
                  @endif {
                      "data": "application_id"
                  },
                  {
                      "data": "name"
                  },
                  {
                      "data": "ben_age"
                  },
                  {
                      "data": "mobile_no"
                  },
                  {
                      "data": "bank_ifsc"
                  },
                  {
                      "data": "bank_code"
                  },
                  @if ($type == 'F')
                      {
                          "data": "enter_by_mobile_no"
                      }, {
                          "data": "faulty_reason"
                      },
                  @endif
                  @if ($type == 'R')
                      {
                          "data": "enter_by_mobile_no"
                      }, {
                          "data": "rejected_reason"
                      },
                  @endif
                  @if ($type == 'T')
                      {
                          "data": "enter_by_mobile_no"
                      }, {
                          "data": "rejected_reason"
                      },
                  @endif
              ],

              "buttons": [{
                      extend: 'pdf',
                      exportOptions: {
                          columns: ':visible:not(:last-child)'
                      },
                      title: "{{ $report_type_name }}",
                      messageTop: function() {
                          var message = 'Report Renerated on: <?php echo date('l jS \of F Y h:i:s A'); ?>';
                          return message;
                      },
                      footer: true,
                      pageSize: 'A4',
                      orientation: 'portrait',
                      pageMargins: [40, 60, 40, 60],
                  },
                  {
                      extend: 'excel',
                      exportOptions: {
                          columns: ':visible:not(:last-child)',
                          format: {
                              body: function(data, row, column, node) {
                                  return column === 4 ? "\0" + data : data;
                              }
                          }
                      },
                      title: "{{ $report_type_name }}",
                      messageTop: function() {
                          var message = 'Report Renerated on: <?php echo date('l jS \of F Y h:i:s A'); ?>';
                          return message;
                      },
                      footer: true,
                      pageSize: 'A4',
                      //orientation: 'landscape',
                      pageMargins: [40, 60, 40, 60],
                  },
                  {
                      extend: 'print',
                      exportOptions: {
                          columns: ':visible:not(:last-child)'
                      },
                      title: "{{ $report_type_name }}",
                      messageTop: function() {
                          var message = 'Report Renerated on: <?php echo date('l jS \of F Y h:i:s A'); ?>';
                          return message;
                      },
                      footer: true,
                      pageSize: 'A4',
                      //orientation: 'landscape',
                      pageMargins: [40, 60, 40, 60],
                  },
              ],
          });
      });
  </script>
