@extends('layouts.app-template-datatable')
@push('styles')
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

@endpush
@section('content')
  <!-- <section class="content"> -->
  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-sm-8"></div>
      </div>
    </div>

    <div class="card-body">

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

      <form name="formReport" id="formReport">

        <div class="row mb-3">

          <div class="form-group col-md-3">
            <label>Type</label>
            <select class="form-select" name="report_type" id="report_type" tabindex="70">
              <option value="1">Account Validation Failed</option>
              <option value="2">Payment Validation Failed</option>
              <option value="3">Duplicate Bank</option>
              <option value="4">Duplicate Aadhaar</option>
              <option value="5">Under Caste Modification</option>
              <option value="6">Deactivated</option>
              <option value="7">Name Validation Rejection</option>
              <option value="8">Duplicate Bank Rejection</option>
            </select>
            <span id="error_report_type" class="text-danger"></span>
          </div>

          @if(count($ds_phase_list) > 0)
            <div class="form-group col-md-3">
              <label>Duare Sarkar Phase</label>
              <select class="form-select" name="ds_phase" id="ds_phase" tabindex="70">
                <option value="">--All--</option>
                @foreach($ds_phase_list as $key => $val)
                  <option value="{{$key}}">{{$val}}</option>
                @endforeach
              </select>
              <span id="error_ds_phase" class="text-danger"></span>
            </div>
          @else
            <input type="hidden" name="ds_phase" id="ds_phase" value="" />
          @endif

          @if($is_rural_visible)
            <div class="col-md-3">
              <label class="control-label">Rural/Urban</label>
              <select name="rural_urbanid" id="rural_urbanid" class="form-select">
                <option value="">-----All----</option>
                @foreach (Config::get('constants.rural_urban') as $key => $value)
                  <option value="{{$key}}">{{$value}}</option>
                @endforeach
              </select>
            </div>
          @else
            <input type="hidden" name="rural_urbanid" id="rural_urbanid" value="{{$is_urban}}" />
          @endif

          @if($urban_visible)
            <div class="col-md-3">
              <label class="control-label" id="blk_sub_txt">Block/Subdivision</label>
              <select name="urban_body_code" id="urban_body_code" class="form-select">
                <option value="">-----All----</option>
              </select>
            </div>
          @else
            <input type="hidden" name="urban_body_code" id="urban_body_code" value="{{$urban_body_code}}" />
          @endif

          @if($munc_visible)
            @if($mappingLevel == 'District')
              </div>
              <div class="row mb-3">
            @endif
            <div class="col-md-3" id="municipality_div">
              <label class="control-label">Municipality</label>
              <select name="block_ulb_code" id="block_ulb_code" class="form-select">
                <option value="">-----All----</option>
                @if(count($muncList) > 0)
                  @foreach ($muncList as $muncArr)
                    <option value="{{$muncArr->urban_body_code}}">{{trim($muncArr->urban_body_name)}}</option>
                  @endforeach
                @endif
              </select>
            </div>
          @endif

          @if($gp_ward_visible)
            <div class="form-group col-md-3" id="gp_ward_div">
              <label id="gp_ward_txt">GP/Ward</label>
              <select name="gp_ward_code" id="gp_ward_code" class="form-select" tabindex="17">
                <option value="">--All--</option>
                @if(count($gpwardList) > 0)
                  @foreach ($gpwardList as $gp_ward_arr)
                    <option value="{{$gp_ward_arr->gram_panchyat_code}}">{{trim($gp_ward_arr->gram_panchyat_name)}}</option>
                  @endforeach
                @endif
              </select>
              <span id="error_gp_ward_code" class="text-danger"></span>
            </div>
          @endif

        </div>

        <div class="row">
          <div class="col-md-2 mt-3">
            <button type="button" name="filter" id="filter" class="btn btn-success w-100">Filter</button>
          </div>

          <div class="col-md-2 offset-md-2 mt-3">
            <button type="button" name="reset" id="reset" class="btn btn-warning w-100">Reset</button>
          </div>
        </div>

      </form>

      @if($download_excel == 1)
        <form action="stop-list-excel" method="post" id="excel_form">
          <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
          <input type="submit" class="btn btn-info mt-2" id="excel-download" value="Export All Data to Excel" />
        </form>
      @endif

      <div id="example2_wrapper" class="col-md-12 dataTables_wrapper form-inline dt-bootstrap js-report-form">

        <div class="col-md-3 offset-md-3">
          <h4>
            <span class="badge bg-primary" id="report_type_name">{{$report_type_name}}</span>
          </h4>
        </div>

        <div class="col-md-12 text-center" id="loaderdiv" hidden>
          <img src="{{ asset('images/ZKZg.gif') }}" width="100px" height="100px" />
        </div>

        <div class="col-md-12 mt-3" id="reportbody">

          <table id="example" class="table table-bordered table-striped w-100">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <input type="hidden" name="district_code" id="district_code" value="{{ $district_code }}">

            <thead>
              <tr>
                <th>Application ID</th>
                <th>Applicant Name</th>
                <th>Mobile Number</th>
                <th>Block/Municipality</th>
                <th>GP/Ward</th>
                <th>Status</th>
              </tr>
            </thead>

            <tfoot>
              <tr>
                <th>Application ID</th>
                <th>Applicant Name</th>
                <th>Mobile Number</th>
                <th>Block/Municipality</th>
                <th>GP/Ward</th>
                <th>Status</th>
              </tr>
            </tfoot>
          </table>

          <div class="row">
            <div class="col-sm-7">
              <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate"></div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </div>
  <!-- </section> -->

@endsection
@push('scripts')
  <script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
    $(document).ready(function () {
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #benListReport').addClass("active");

      $('.sidebar-menu #Stp_Rejection').addClass("active");

      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var base_url = '{{ url('/') }}';


      $(".dataTables_scrollHeadInner").css({ "width": "100%" });

      $(".table ").css({ "width": "100%" });

      $('#rural_urbanid').change(function () {
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
            $.each(subDistricts, function (key, value) {
              if ((value.district_code == select_district_code)) {
                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
              }
            });
          }
          else if (rural_urbanid == 2) {
            $("#municipality_div").hide();
            $("#blk_sub_txt").text('Block');
            $("#gp_ward_txt").text('GP');
            $.each(blocks, function (key, value) {
              if ((value.district_code == select_district_code)) {
                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
              }
            });
          }
          $('#urban_body_code').html(htmlOption);
        }
        else {
          $("#blk_sub_txt").text('Block/Subdivision');
          $("#gp_ward_txt").text('GP/Ward');
          $('#urban_body_code').html('<option value="">--All --</option>');
          $('#block_ulb_code').html('<option value="">--All --</option>');
          $('#gp_ward_code').html('<option value="">--All --</option>');
        }
      });
      $('#urban_body_code').change(function () {
        var rural_urbanid = $('#rural_urbanid').val();
        if (rural_urbanid == 1) {
          var sub_district_code = $(this).val();

          $('#block_ulb_code').html('<option value="">--All --</option>');
          select_district_code = $('#district_code').val();
          var htmlOption = '<option value="">--All--</option>';
          // console.log(sub_district_code);
          //console.log(select_district_code);

          $.each(ulbs, function (key, value) {
            if ((value.district_code == select_district_code) && (value.sub_district_code == sub_district_code)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
          $('#block_ulb_code').html(htmlOption);
        } else if (rural_urbanid == 2) {
          $('#muncid').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
          var block_code = $(this).val();
          select_district_code = $('#district_code').val();

          var htmlOption = '<option value="">--All--</option>';
          $.each(gps, function (key, value) {
            if ((value.district_code == select_district_code) && (value.block_code == block_code)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
          //console.log(htmlOption);
          $('#gp_ward_code').html(htmlOption);

          $("#gp_ward_div").show();
        }
        else {
          $('#block_ulb_code').html('<option value="">--All --</option>');
          $('#gp_ward_code').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
          $("#gp_ward_div").hide();
        }


      });

      $('#block_ulb_code').change(function () {
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
            $.each(ulb_wards, function (key, value) {
              if (value.urban_body_code == muncid) {
                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
              }
            });
            $('#gp_ward_code').html(htmlOption);

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
        $('#report_type_name').text($("#report_type :selected").text());
        table.clear().draw();
        table.ajax.reload();

      });
      $('#reset').click(function () {
        window.location.href = 'stop-list';
      });
      $('#excel-download').on('click', function () {

        var report_type = $("#report_type").val();
        var ds_phase = $("#ds_phase").val();
        var rural_urbanid = $("#rural_urbanid").val();
        var urban_body_code = $("#urban_body_code").val();
        var block_ulb_code = $("#block_ulb_code").val();
        var gp_ward_code = $("#gp_ward_code").val();
        $('#excel_form').append('<input type="hidden" name="report_type" id="report_type" value="' + report_type + '">');
        $('#excel_form').append('<input type="hidden" name="ds_phase" id="ds_phase" value="' + ds_phase + '">');
        $('#excel_form').append('<input type="hidden" name="rural_urbanid" id="rural_urbanid" value="' + rural_urbanid + '">');
        $('#excel_form').append('<input type="hidden" name="urban_body_code" id="urban_body_code" value="' + urban_body_code + '">');
        $('#excel_form').append('<input type="hidden" name="block_ulb_code" id="block_ulb_code" value="' + block_ulb_code + '">');
        $('#excel_form').append('<input type="hidden" name="gp_ward_code" id="gp_ward_code" value="' + gp_ward_code + '">');
        $("#excel_form").submit();
      });
      // Add this to disable DataTables automatic search processing
      // Keep searching enabled but disable server-side processing for search
      var table = $('#example').DataTable({
        dom: "Blfrtip",
        "paging": true,
        "pageLength": 20,
        "lengthMenu": [[20, 50, 80, 120, 150], [20, 50, 80, 120, 150]],
        "serverSide": true,
        "deferRender": true,
        "processing": true,
        "bRetrieve": true,
        "scrollX": true,
        "ordering": false,
        "searching": true,  // KEEP search box visible
        "language": {
          "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
        },
        "ajax": {
          url: "{{ url('stop-list') }}",
          type: "POST",
          data: function (d) {
            d.report_type = $('#report_type').val();
            d.ds_phase = $('#ds_phase').val();
            d.district_code = "{{ $district_code }}";
            d.rural_urbanid = $('#rural_urbanid').val();
            d.urban_body_code = $('#urban_body_code').val();
            d.block_ulb_code = $('#block_ulb_code').val();
            d.gp_ward_code = $('#gp_ward_code').val();
            d._token = "{{csrf_token()}}";
            d.scheme = "{{ $scheme }}";
            d.search_value = d.search.value;
            d.search = '';
          },
          error: function (ex) {
            console.log(ex);
          }
        },
        "columns": [
          { "data": "application_id", "searchable": true },
          { "data": "ben_name", "searchable": true },
          { "data": "mobile_no", "searchable": true },
          { "data": "block_munc_name", "searchable": true },
          { "data": "gp_ward_name", "searchable": true },
          { "data": "status", "searchable": true }
        ],
        "buttons": [
          {
            extend: 'pdf',
            exportOptions: {
              columns: ':visible:not(:last-child)'
            },
            title: "{{$report_type_name}}",
            messageTop: function () {
              var message = 'Report Generated on: <?php echo date("l jS \of F Y h:i:s A"); ?>';
              return message;
            },
            footer: true,
            pageSize: 'A4',
            orientation: 'portrait',
            pageMargins: [40, 60, 40, 60],
            className: 'table-action-btn',
          },
          {
            extend: 'excel',
            title: "{{$report_type_name}}",
            messageTop: function () {
              var message = 'Report Generated on: <?php echo date("l jS \of F Y h:i:s A"); ?>';
              return message;
            },
            footer: true,
            pageSize: 'A4',
            pageMargins: [40, 60, 40, 60],
            className: 'table-action-btn',
          },
          {
            extend: 'print',
            exportOptions: {
              columns: ':visible:not(:last-child)'
            },
            title: "{{$report_type_name}}",
            messageTop: function () {
              var message = 'Report Generated on: <?php echo date("l jS \of F Y h:i:s A"); ?>';
              return message;
            },
            footer: true,
            pageSize: 'A4',
            pageMargins: [40, 60, 40, 60],
            className: 'table-action-btn',
          },
        ],
      });
    });

  </script>

@endpush