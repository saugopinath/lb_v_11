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
      border-color: #dc3545;
      background-color: #f8d7da;
    }

    .select2 {
      width: 100% !important;
    }

    .select2 .has-error {
      border-color: #dc3545;
      background-color: #f8d7da;
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
    .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }
  </style>
    
@extends('../layouts.app-template-datatable')
@section('content')
      <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div>
              <!-- class="box box-primary" -->
              <div class="card-header">
                <h3 class="card-title">
                    Faulty Mis Report
                  </h3>
                <!-- <p><h3 class="card-title"><b>Bandhu Prakalpa (for SC)</b></h3></p> -->
              </div>

              <div>
                @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
                <div class="alert alert-success alert-dismissible">
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  <strong>{{ $message }} with Application ID: {{$id}}</strong>
                </div>
                @endif
                @if ($message = Session::get('error') )
                <div class="alert alert-danger alert-dismissible">
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  <strong>{{ $message }}</strong>
                </div>
                @endif
                @if(count($errors) > 0)
                <div class="alert alert-danger alert-dismissible">
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  <ul>
                    @foreach($errors->all() as $error)
                    <li><strong> {{ $error }}</strong></li>
                    @endforeach
                  </ul>
                </div>
                @endif
                <!--   @if ($message = Session::get('failure'))
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button> 
                      <strong>{{ $message }}</strong>
              </div>
              @endif -->
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" id="register_form" action="{{url('wcd20210202ReportPost')}}" class="submit-once">
                {{ csrf_field() }}
                <div class="tab-content" style="margin-top:16px;">
                  <div class="tab-pane active" id="personal_details">
                    <div class="card">
                      <div class="card-header card-header-custom">
                        <h5>Search Criteria</h5>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          @if($district_visible)
                          <div class="form-group col-md-4">
                            <label class="">District</label>
                            <select name="district" id="district" class="form-control" tabindex="6">
                              <option value="">--All --</option>
                              @foreach ($districts as $district)
                              <option value="{{$district->district_code}}" @if(old('district')==$district->district_code) selected @endif> {{$district->district_name}}</option>
                              @endforeach
                            </select>
                            <span id="error_district" class="text-danger"></span>

                          </div>
                          @else
                          <input type="hidden" name="district" id="district" value="{{$district_code_fk}}" />
                          @endif
                          @if($is_urban_visible)
                          <div class="form-group col-md-4" id="divUrbanCode">
                            <label class="">Rural/ Urban</label>

                            <select name="urban_code" id="urban_code" class="form-control" tabindex="11">
                              <option value="">--All --</option>
                              @foreach(Config::get('constants.rural_urban') as $key=>$val)
                              <option value="{{$key}}" @if( old('urban_code')==$key) selected @endif>{{$val}}</option>
                              @endforeach

                            </select>
                            <span id="error_urban_code" class="text-danger"></span>
                          </div>
                          @else
                          <input type="hidden" name="urban_code" id="urban_code" value="{{$rural_urban_fk}}" />

                          @endif
                          @if($block_visible)
                          <div class="form-group col-md-4" id="divBodyCode">
                            <label class="" id="blk_sub_txt">Block/Sub Division.</label>

                            <select name="block" id="block" class="form-control" tabindex="16">
                              <option value="">--All --</option>


                            </select>
                            <span id="error_block" class="text-danger"></span>
                          </div>
                          @else
                          <input type="hidden" name="block" id="block" value="{{$block_munc_corp_code_fk}}" />

                          @endif

                          <div class="form-group col-md-4" id="municipality_div" style="{{$municipality_visible?'':'display:none'}}">
                            <label class="">Municipality</label>

                            <select name="muncid" id="muncid" class="form-control" tabindex="16">
                              <option value="">--All --</option>
                              @foreach ($muncList as $munc)
                              <option value="{{$munc->urban_body_code}}"> {{$munc->urban_body_name}}</option>
                              @endforeach

                            </select>
                            <span id="error_muncid" class="text-danger"></span>
                          </div>


                          <div class="form-group col-md-4" id="gp_ward_div" style="{{$gp_ward_visible?'':'display:none'}}">
                            <label class="" id="gp_ward_txt">GP/Ward</label>

                            <select name="gp_ward" id="gp_ward" class="form-control" tabindex="17">
                              <option value="">--All --</option>
                              @foreach ($gpList as $gp)
                              <option value="{{$gp->gram_panchyat_code}}"> {{$gp->gram_panchyat_name}}</option>
                              @endforeach

                            </select>
                            <span id="error_gp_ward" class="text-danger"></span>
                          </div>


                        </div>
                        <div class="row">
                          <div class="col-md-12 text-center">
                            <button type="button" id="submitting" value="Submit" class="btn btn-success table-action-btn modal-search form-submitted"> <i class="fas fa-search me-1"></i>Search </button>

                            <div class=""><img src="{{ asset('images/ZKZg.gif')}}" id="submit_loader1" width="50px" height="50px" style="display:none;"></div>

                            <!--<button type="button" name="btn_personal_details" id="btn_personal_details" class="btn btn-info btn-lg">Next</button>-->
                          </div>
                          <br />
                        </div>
                      </div>
                    </div>
                     <div class="tab-pane active" id="search_details" style="display:none;">
                                    <div class="card">
                                        <div class="card-header card-header-custom">
                                            <h5 class="card-title mb-0" id="heading_msg">
                                                Search Result
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 text-end">
                                                <small class="text-muted">
                                                    Report Generated on: <b><?php echo date("l jS \of F Y h:i:s A"); ?></b>
                                                </small>
                                            </div>

                                            <div class="table-responsive pt-1">
                                                <table id="example" class="data-table" style="width:100%">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Sl No.</th>
                                                            <th id="location_id" width="20%" test->District</th>
                                                            <th width="20%">Total Faulty Applications</th>
                                                            <th width="20%">Faulty cases without AADHAR</th>
                                                            <th width="20%">Faulty Cases without Bank A/c</th>
                                                            <th width="20%">Faulty Cases without Swasthya Sathi</th>
                                                            <th width="20%">Faulty Cases without Caste</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Data will be populated by JavaScript -->
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <th></th>
                                                            <th>Total</th>
                                                            <th>Total Faulty Applications</th>
                                                            <th>Faulty cases without AADHAR</th>
                                                            <th>Faulty Cases without Bank A/c</th>
                                                            <th>Faulty Cases without Swasthya Sathi</th>
                                                            <th>Faulty Cases without Caste</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                  </div>
                </div>
            </div>
            </form>
          </div>
          <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
    @endsection

@push('scripts')
  
  <script>
    var base_date = '{{$base_date}}';
    var c_date = '{{$c_date}}';
    //alert(base_date);
    $(document).ready(function() {
      $('.nav-sidebar li').removeClass('active');
      $('.nav-sidebar #faultyMisReport').addClass("active"); 
      $('.nav-sidebar #mis-report-faulty').addClass("active");
      loadDataTable();
      $("#from_date").on('blur', function() {
        var from_date = $('#from_date').val();
        if (from_date != '') {
          //alert(from_date);
          document.getElementById("to_date").setAttribute("min", from_date);
        } else {
          //alert(c_date);
          document.getElementById("to_date").setAttribute("min", base_date);
        }
      });

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
      var from_date = $('#from_date').val();
      var to_date = $('#to_date').val();
      var caste_category = $('#caste_category').val();

      $("#submit_loader1").show();
      $("#submitting").hide();
      $('#search_details').hide();
      $.ajax({
        type: 'post',
        dataType: 'json',
        url: '{{ url('misReport-faulty-Post') }}',
        data: {
          district: district,
          urban_code: urban_code,
          block: block,
          gp_ward: gp_ward,
          from_date: from_date,
          to_date: to_date,
          muncid: muncid,
          caste_category: caste_category,
          _token: '{{ csrf_token() }}',
        },
        success: function(data) {

          //alert(data.title);
          if (data.return_status) {
            $('#search_details').show();
            $("#heading_msg").html("<h4><b>" + data.heading_msg + "</b></h4>");
            $("#location_id").text(data.column);
            if ($.fn.DataTable.isDataTable('#example')) {
              $('#example').DataTable().destroy();
            }
            $("#example > tbody").html("");
            var table = $("#example tbody");
            var slno = 1;
            $.each(data.row_data, function(i, item) {
              var total_faulty = isNaN(parseInt(item.total_faulty)) ? 0 : parseInt(item.total_faulty);
              var faulty_wt_aadhar = isNaN(parseInt(item.faulty_wt_aadhar)) ? 0 : parseInt(item.faulty_wt_aadhar);
              var faulty_wt_bank_account = isNaN(parseInt(item.faulty_wt_bank_account)) ? 0 : parseInt(item.faulty_wt_bank_account);
              var faulty_wt_sws_card_no = isNaN(parseInt(item.faulty_wt_sws_card_no)) ? 0 : parseInt(item.faulty_wt_sws_card_no);
              var faulty_wt_cast_certificate = isNaN(parseInt(item.faulty_wt_cast_certificate)) ? 0 : parseInt(item.faulty_wt_cast_certificate);
              var faulty_wt_aadhar = total_faulty - faulty_wt_aadhar;
              var faulty_wt_bank_account = total_faulty - faulty_wt_bank_account;
              var faulty_wt_sws_card_no = total_faulty - faulty_wt_sws_card_no;
              var faulty_wt_cast_certificate = total_faulty - faulty_wt_cast_certificate;
              table.append("<tr><td>" + (i + 1) + "</td><td>" + item.location_name + "</td><td>" + total_faulty + "</td><td>" + faulty_wt_aadhar + "</td><td>" + faulty_wt_bank_account + "</td><td>" + faulty_wt_sws_card_no + "</td><td>" + faulty_wt_cast_certificate + "</td></tr>");
              //slno++;

            });


            //$('#example tbody').empty();
            $("#example").show();
            $('#example').dataTable({
              "paging": false,
              "ordering": false,
              "searching": false,
              "info": false,
              "scrollX": true,
              "dom": 'Bfrtip',
              "buttons": [
                
                // {
                  // extend: 'csv',
                  // footer: true,
                  // title: data.title,
                  // messageTop: data.heading_msg,
                  // className: 'table-action-btn'
                // },
                {
                  extend: 'pdf',
                  title: data.title,
                  footer: true,
                  messageTop: data.heading_msg
                }

              ],
              "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                  data;

                // converting to interger to find total
                var intVal = function(i) {
                  return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                    typeof i === 'number' ?
                    i : 0;
                };

                // computing column Total of the complete result 
                var fotter_2 = api
                  .column(2)
                  .data()
                  .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);

                var fotter_3 = api
                  .column(3)
                  .data()
                  .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);
                var fotter_4 = api
                  .column(4)
                  .data()
                  .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);

                var fotter_5 = api
                  .column(5)
                  .data()
                  .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);

                 var fotter_6 = api
                  .column(6)
                  .data()
                  .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                  }, 0);
                // Update footer by showing the total with the reference of the column index 
                $(api.column(0).footer()).html('');
                $(api.column(1).footer()).html('Total');
                $(api.column(2).footer()).html(fotter_2);
                $(api.column(3).footer()).html(fotter_3);
                $(api.column(4).footer()).html(fotter_4);
                $(api.column(5).footer()).html(fotter_5);
                $(api.column(6).footer()).html(fotter_6);
              }
            });
          } else {
            $('#search_details').hide();
            $("#example").hide();
            printMsg(data.return_msg, '0', 'errorDiv');
          }
          $("#submit_loader1").hide();
          $("#submitting").show();

        },
        error: function(ex) {
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