<style>
  .box {
    width: 800px;
    margin: 0 auto;
  }

  .active_tab1 {
    background-color: #fff;
    color: #333;
    font-weight: 600;
  }

  .inactive_tab1 {
    background-color: #f5f5f5;
    color: #333;
    cursor: not-allowed;
  }

  .has-error {
    border-color: #cc0000;
    background-color: #ffff99;
  }

  .select2 {
    width: 100% !important;
  }

  .select2 .has-error {
    border-color: #cc0000;
    background-color: #ffff99;
  }

  .modal_field_name {
    float: left;
    font-weight: 700;
    margin-right: 1%;
    padding-top: 1%;
    margin-top: 1%;
  }

  .modal_field_value {
    margin-right: 1%;
    padding-top: 1%;
    margin-top: 1%;
  }

  .row {
    margin-right: 0px !important;
    margin-left: 0px !important;
    margin-top: 1% !important;
  }

  .section1 {
    border: 1.5px solid #9187878c;
    margin: 2%;
    padding: 2%;
  }

  .color1 {
    margin: 0% !important;
    background-color: #5f9ea061;
  }

  .modal-header {
    background-color: #7fffd4;
  }

  .required-field::after {
    content: "*";
    color: red;
  }

  .imageSize {
    font-size: 9px;
    color: #333;
  }

  #divScrool {
    overflow-x: scroll;
  }
</style>




<!-- Content Header (Page header) -->
@extends('layouts.app-template-datatable')
@section('content')

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">

        {{-- Success Message --}}
        @if(($message = Session::get('success')) && ($id = Session::get('id')))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong>{{ $message }} with Application ID: {{$id}}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- Error Message --}}
        @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>{{ $message }}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- Validation Errors --}}
        @if(count($errors) > 0)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li><strong>{{ $error }}</strong></li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

      </div>
    </div>

    {{-- Form --}}
    <form method="post" id="register_form" action="{{ url('wcd20210202ReportPost') }}" class="submit-once">
      {{ csrf_field() }}

      <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="personal_details">

          <div class="card card-primary card-outline">
            <div class="card-header">
              <h4 class="mb-0"><b>CMO Grievance MIS Report</b></h4>
            </div>

            <div class="card-body">

              <div class="row">

                {{-- District --}}
                @if($district_visible)
                <div class="col-md-3 mb-3">
                  <label class="form-label">District</label>
                  <select name="district" id="district" class="form-select" tabindex="6">
                    <option value="">--All--</option>
                    @foreach ($districts as $district)
                    <option value="{{ $district->district_code }}" @if(old('district')==$district->district_code) selected @endif>
                      {{ $district->district_name }}
                    </option>
                    @endforeach
                  </select>
                  <span id="error_district" class="text-danger small"></span>
                </div>
                @else
                <input type="hidden" name="district" id="district" value="{{ $district_code_fk }}" />
                @endif

                {{-- Rural / Urban --}}
                @if($is_urban_visible)
                <div class="col-md-3 mb-3" id="divUrbanCode">
                  <label class="form-label">Rural / Urban</label>
                  <select name="urban_code" id="urban_code" class="form-select" tabindex="11">
                    <option value="">--All--</option>
                    @foreach(Config::get('constants.rural_urban') as $key => $val)
                    <option value="{{ $key }}" @if(old('urban_code')==$key) selected @endif>{{ $val }}</option>
                    @endforeach
                  </select>
                  <span id="error_urban_code" class="text-danger small"></span>
                </div>
                @else
                <input type="hidden" name="urban_code" id="urban_code" value="{{ $rural_urban_fk }}" />
                @endif

                {{-- Submit Button --}}
                <div class="col-md-12 text-center mt-3">
                  <button type="button" id="submitting" value="Submit" class="btn btn-success modal-search form-submitted">
                    Search
                  </button>
                  <div class="mt-2">
                    <img src="{{ asset('images/ZKZg.gif') }}" id="submit_loader1" width="50" height="50" style="display:none;">
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Error Div --}}
          <div class="alert alert-danger print-error-msg mt-3" style="display:none;" id="errorDiv">
            <button type="button" class="btn-close" aria-label="Close" onclick="closeError('errorDiv')"></button>
            <ul class="mb-0"></ul>
          </div>

          {{-- Search Results --}}
          <div class="tab-pane fade show active" id="search_details" style="display:none;">
            <div class="card card-info card-outline mt-3">
              <div class="card-header" id="heading_msg">
                <h4 class="mb-0"><b>Search Result</b></h4>
              </div>

              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div id="report_generation_text">
                    Report Generated on:
                    <b>{{ date('l jS \of F Y h:i:s A') }}</b>
                  </div>
                  <button class="btn btn-info exportToExcel" type="button">Export to Excel</button>
                </div>

                <div class="table-responsive" id="divScrool">
                  <table id="example" class="table table-striped table-bordered table-hover table2excel w-100">
                    <thead>
                      <tr>
                        <td colspan="21" align="center" style="display:none;" id="heading_excel">Heading</td>
                      </tr>
                      <tr>
                        <th>Sl No. (A)</th>
                        <th id="location_id">District</th>
                        <th>Total Grievance (C)</th>
                        <th>Total Action Pending (D)</th>
                        <th>Total Approval Pending Among Action taken (E)</th>
                        <th>Total Approved but Pushed To CMO pending (F)</th>
                        <th>Total Pushed To CMO (G)</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                      <tr id="fotter_id"></tr>
                      <tr>
                        <td colspan="7" align="center" style="display:none;" id="fotter_excel">Heading</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>

              </div>
            </div>
          </div>

        </div>
      </div>

    </form>
  </div>
</section>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lk-main').addClass("active");
    $('.sidebar-menu #cmoMis').addClass("active");
    //loadDataTable();
    $(".exportToExcel").click(function(e) {
      // alert('ok');
      $(".table2excel").table2excel({
        // exclude CSS class
        exclude: ".noExl",
        name: "Worksheet Name",
        filename: "CMO Grievance Mis Report For Lakshmir Bhandar", //do not include extension
        fileext: ".xls" // file extension
      });
    });
    // $("#from_date").on('blur',function(){ 
    //     var from_date = $('#from_date').val();
    //     if(from_date!=''){
    //      //alert(from_date);
    //      document.getElementById("to_date").setAttribute("min", from_date);
    //     }
    //     else{
    //       //alert(c_date);
    //       document.getElementById("to_date").setAttribute("min", base_date);
    //     }
    //   });

    $('#district').change(function() {
      var district = $(this).val();
      //alert(district);
      $('#urban_code').val('');
      $('#block').html('<option value="">--All --</option>');
      $('#muncid').html('<option value="">--All --</option>');
    });

    $('#urban_code').change(function() {
      var urban_code = $(this).val();
      if (urban_code == '') {
        $('#muncid').html('<option value="">--All --</option>');
      }
      $('#muncid').html('<option value="">--All --</option>');
      $('#block').html('<option value="">--All --</option>');
      $('#gp_ward').html('<option value="">--All --</option>');
      select_district_code = $('#district').val();
      if (select_district_code == '') {
        alert('Please Select District First');
        $("#district").focus();
        $("#urban_code").val('');
      } else {
        select_body_type = urban_code;
        var htmlOption = '<option value="">--All--</option>';
        $("#gp_ward_div").show();
        if (select_body_type == 2) {
          $("#blk_sub_txt").text('Block');
          $("#gp_ward_txt").text('GP');
          $("#municipality_div").hide();
          $.each(blocks, function(key, value) {
            if (value.district_code == select_district_code) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
        } else if (select_body_type == 1) {
          $("#blk_sub_txt").text('Subdivision');
          $("#gp_ward_txt").text('Ward');
          $("#municipality_div").show();
          $.each(subDistricts, function(key, value) {
            if (value.district_code == select_district_code) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
        } else {
          $("#blk_sub_txt").text('Block/Subdivision');
        }
        $('#block').html(htmlOption);
      }

    });
    $('#block').change(function() {
      var block = $(this).val();
      var district = $("#district").val();
      var urban_code = $("#urban_code").val();
      if (district == '') {
        $('#urban_code').val('');
        $('#block').html('<option value="">--All --</option>');
        $('#muncid').html('<option value="">--All --</option>');
        alert('Please Select District First');
        $("#district").focus();

      }
      if (urban_code == '') {
        alert('Please Select Rural/Urban First');
        $('#block').html('<option value="">--All --</option>');
        $('#muncid').html('<option value="">--All --</option>');
        $("#urban_code").focus();
      }
      if (block != '') {
        var rural_urbanid = $('#urban_code').val();
        if (rural_urbanid == 1) {
          var sub_district_code = $(this).val();
          if (sub_district_code != '') {
            $('#muncid').html('<option value="">--All --</option>');
            select_district_code = $('#district').val();
            var htmlOption = '<option value="">--All--</option>';
            $.each(ulbs, function(key, value) {
              if ((value.district_code == select_district_code) && (value.sub_district_code == sub_district_code)) {
                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
              }
            });
            $('#muncid').html(htmlOption);
          } else {
            $('#muncid').html('<option value="">--All --</option>');
          }
        } else if (rural_urbanid == 2) {
          $('#muncid').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
          var block_code = $(this).val();
          select_district_code = $('#district').val();

          var htmlOption = '<option value="">--All--</option>';
          $.each(gps, function(key, value) {
            if ((value.district_code == select_district_code) && (value.block_code == block_code)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
          $('#gp_ward').html(htmlOption);
          $("#gp_ward_div").show();


        } else {
          $('#muncid').html('<option value="">--All --</option>');
          $("#municipality_div").hide();
        }
      } else {
        $('#muncid').html('<option value="">--All --</option>');
        $('#gp_ward').html('<option value="">--All --</option>');
      }

    });
    $('#muncid').change(function() {
      var muncid = $(this).val();
      var district = $("#district").val();
      var urban_code = $("#urban_code").val();
      if (district == '') {
        $('#urban_code').val('');
        $('#block').html('<option value="">--All --</option>');
        $('#muncid').html('<option value="">--All --</option>');
        alert('Please Select District First');
        $("#district").focus();

      }
      if (urban_code == '') {
        alert('Please Select Rural/Urban First');
        $('#block').html('<option value="">--All --</option>');
        $('#muncid').html('<option value="">--All --</option>');
        $("#urban_code").focus();
      }
      if (muncid != '') {
        var rural_urbanid = $('#urban_code').val();
        if (rural_urbanid == 1) {
          var municipality_code = $(this).val();
          if (municipality_code != '') {
            $('#gp_ward').html('<option value="">--All --</option>');
            var htmlOption = '<option value="">--All--</option>';
            $.each(ulb_wards, function(key, value) {
              if (value.urban_body_code == municipality_code) {
                htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
              }
            });
            $('#gp_ward').html(htmlOption);
          } else {
            $('#gp_ward').html('<option value="">--All --</option>');
          }
        } else {
          $('#gp_ward').html('<option value="">--All --</option>');
          $("#gp_ward_div").hide();
        }
      } else {
        $('#gp_ward').html('<option value="">--All --</option>');
      }

    });
    loadDataTable();
    $('.modal-search').on('click', function() {

      loadDataTable();
    });
  });

  function loadDataTable() {
    var district = $('#district').val();
    var urban_code = $('#urban_code').val();
    var block = $('#block').val();
    var gp_ward = $('#gp_ward').val();
    var muncid = $('#muncid').val();
    var process_type = $('#process_type').val();

    $("#submit_loader1").show();
    $("#submitting").hide();
    $('#search_details').hide();
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: '{{ route('get-mis-report1') }}',
      data: {
        // scheme_id: scheme_id,
        district: district,
        urban_code: urban_code,
        block: block,
        gp_ward: gp_ward,
        muncid: muncid,
        // process_type: process_type,
        _token: '{{ csrf_token() }}',
      },
      success: function(data) {
        //  console.log(data);
        //alert(data.title);
        if (data.return_status) {
          $('#search_details').show();
          $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
          $("#heading_excel").html("<b>" + data.heading_msg + "</b>");
          $("#fotter_excel").html("<b>" + $('#report_generation_text').text() + "</b>");
          $("#location_id").text(data.column + '(B)');
          $("#example > tbody").html("");
          var table = $("#example tbody");
          var slno = 1;
          var fotter_1 = 0;
          var fotter_2 = 0;
          var fotter_3 = 0;
          var fotter_4 = 0;
          var fotter_5 = 0;
          $.each(data.row_data, function(i, item) {
            var total_grievance = isNaN(parseInt(item.total_grievance)) ? 0 : parseInt(item.total_grievance);
            var total_verification_pending = isNaN(parseInt(item.total_verification_pending)) ? 0 : parseInt(item.total_verification_pending);
            var total_verified = isNaN(parseInt(item.total_verified)) ? 0 : parseInt(item.total_verified);
            var total_approved = isNaN(parseInt(item.total_approved)) ? 0 : parseInt(item.total_approved);
            var total_grievance_back = isNaN(parseInt(item.total_grievance_back)) ? 0 : parseInt(item.total_grievance_back);
            fotter_1 = fotter_1 + total_grievance;
            fotter_2 = fotter_2 + total_verification_pending;
            fotter_3 = fotter_3 + total_verified;
            fotter_4 = fotter_4 + total_approved;
            fotter_5 = fotter_5 + total_grievance_back;
            table.append("<tr><td>" + (i + 1) + "</td><td>" + item.location_name + "</td><td>" + total_grievance + "</td><td>" + total_verification_pending + "</td><td>" + total_verified + "</td><td>" + total_approved + "</td><td>" + total_grievance_back + "</td></tr>");
            //slno++;

          });

          $("#example > tfoot #fotter_id").html("<th></th><th>Total</th><th>" + fotter_1 + "</th><th>" + fotter_2 + "</th><th>" + fotter_3 + "</th><th>" + fotter_4 + "</th><th>" + fotter_5 + "</th>");
          //$('#example tbody').empty();
          $("#example").show();


        } else {
          $('#search_details').hide();
          $("#example").hide();
          printMsg(data.return_msg, '0', 'errorDiv');
        }
        $("#submit_loader1").hide();
        $("#submitting").show();

      },
      error: function(ex) {
        //console.log(ex);
        $("#submit_loader1").hide();
        //$("#submitting").hide();
        $("#submitting").show();
        /// alert('Something wrong..may be session timeout. please logout and then login again');
        //  location.reload();

      }
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
    } else {
      $("#" + divid).removeClass('alert-warning');
      $("#" + divid).addClass('alert-success');
    }
    if (Array.isArray(msg)) {
      $.each(msg, function(key, value) {
        $("#" + divid).find("ul").append('<li>' + value + '</li>');
      });
    } else {
      $("#" + divid).find("ul").append('<li>' + msg + '</li>');
    }
  }

  function closeError(divId) {
    $('#' + divId).hide();
  }
</script>
@endpush