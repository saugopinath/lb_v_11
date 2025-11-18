<style>
  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }
</style>
@extends('layouts.app-template-datatable')
@section('content')
<!-- Main content -->
<div class="container-fluid">
  <div class="row">
    <div class="col-12 mt-4">
      <form method="post" id="register_form" class="submit-once">
        {{ csrf_field() }}

        <div class="tab-content" style="margin-top:16px;">
          <div class="tab-pane active" id="personal_details">
            <!-- Card with your design -->
            <div class="card" id="res_div">
              <div class="card-header card-header-custom">
                <h4 class="card-title mb-0"><b>{{$report_type_name}}</b></h4>
              </div>
              <div class="card-body" style="padding: 20px;">
                <!-- Alert Messages -->
                <div class="alert-section">
                  @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ $message }} with Application ID: {{$id}}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if ($message = Session::get('error') )
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if(count($errors) > 0)
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul>
                      @foreach($errors as $error)
                      <li><strong> {{ $error }}</strong></li>
                      @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  <div class="alert print-error-msg" style="display:none;" id="errorDivMain">
                    <button type="button" class="close" aria-label="Close" onclick="closeError('errorDivMain')">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <ul></ul>
                  </div>
                </div>

                <!-- Search Section -->
                <form name="formReport" id="formReport">
                  <div class="form-row col-md-12">


                    @if(count($ds_phase_list)>0)
                    <div class="form-group col-md-4">
                      <label class="">Duare Sarkar Phase</label>
                      <select class="form-control" name="ds_phase" id="ds_phase" tabindex="70">
                        <option value="">--All--</option>
                        @foreach(Config::get('constants.ds_phase.phaselist') as $key=>$val)
                        <option value="{{$key}}">{{$val}}</option>
                        @endforeach
                      </select>
                      <span id="error_ds_phase" class="text-danger"></span>
                    </div>
                    @else
                    <input type="hidden" name="ds_phase" id="ds_phase" value="" />

                    @endif
                    @if($is_rural_visible)
                    <div class="col-md-4">
                      <label class="control-label">Rural/Urban </label>
                      <select name="rural_urbanid" id="rural_urbanid" class="form-control">
                        <option value="">-----All----</option>
                        @foreach (Config::get('constants.rural_urban') as $key=>$value)
                        <option value="{{$key}}"> {{$value}}</option>
                        @endforeach
                      </select>

                    </div>
                    @else
                    <input type="hidden" name="rural_urbanid" id="rural_urbanid" value="{{$is_urban}}" />
                    @endif
                    @if($urban_visible)
                    <div class="col-md-4">
                      <label class="control-label" id="blk_sub_txt">Block/Subdivision</label>
                      <select name="urban_body_code" id="urban_body_code" class="form-control">
                        <option value="">-----All----</option>

                      </select>

                    </div>
                    @else
                    <input type="hidden" name="urban_body_code" id="urban_body_code" value="{{$urban_body_code}}" />

                    @endif
                    @if($munc_visible)
                    <div class="col-md-4" id="municipality_div">
                      <label class="control-label">Municipality</label>
                      <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                        <option value="">-----All----</option>
                        @if(count($muncList)>0){
                        @foreach ($muncList as $muncArr)
                        <option value="{{$muncArr->urban_body_code}}">{{trim($muncArr->urban_body_name)}}</option>
                        @endforeach
                        }
                        @endif

                      </select>

                    </div>
                    @endif
                    @if($gp_ward_visible)
                    <div class="form-group col-md-4" id="gp_ward_div">
                      <label class="" id="gp_ward_txt">GP/Ward</label>

                      <select name="gp_ward_code" id="gp_ward_code" class="form-control" tabindex="17">
                        <option value="">--All --</option>
                        @if(count($gpwardList)>0){
                        @foreach ($gpwardList as $gp_ward_arr)
                        <option value="{{$gp_ward_arr->gram_panchyat_code}}">{{trim($gp_ward_arr->gram_panchyat_name)}}</option>
                        @endforeach
                        }
                        @endif

                      </select>
                      <span id="error_gp_ward_code" class="text-danger"></span>

                    </div>
                    @endif

                  </div>
                  <div class="form-group col-md-3 mb-0">
                    <button type="button" name="submit" value="Submit" class="btn btn-success table-action-btn" id="filter">
                      <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" name="reset" id="reset" class="btn btn-warning table-action-btn">
                    <i class="fas fa-redo me-1"></i> Reset
                  </button>
                  </div>
              </div>
            </div>
      </form>
      @if($download_excel==1)
      <form action="applicationListExcelCasteChange" method="post">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        
        <input type="submit" name="submit" class="btn btn-info" value="Export All Data to Excel" />
      </form>
      @endif

      <!-- DataTable Section -->
      <div class="table-container">
        <div class="table-responsive">
          <table id="example" class="display data-table" cellspacing="0" width="100%">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <input type="hidden" name="district_code" id="district_code" value="{{ $district_code }}">
            <thead>
              <tr role="row">


                <th class="text-center">Beneficiary ID</th>

                <th class="text-center">Application ID</th>

                <th class="text-center">Applicant Name</th>

                <th class="text-center">Swasthyasathi Card No.</th>

                <th class="text-center">Applicant Mobile No.</th>

                <th width="20%" class="text-center">Status</th>

              </tr>
            </thead>


          </table>

        </div>
      </div>

    </div>
  </div>
</div>
</div>
</form>
</div>
</div>
</div>



@endsection

@push('scripts')

<script>
  $(document).ready(function() {
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lb-caste').addClass("active");
    $('.sidebar-menu #caste-sb-mt').addClass("active");
    var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
    var base_url = '{{ url(' / ') }}';

    //display_ct();	

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
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
        } else if (rural_urbanid == 2) {
          $("#municipality_div").hide();
          $("#blk_sub_txt").text('Block');
          $("#gp_ward_txt").text('GP');
          $.each(blocks, function(key, value) {
            // alert(select_district_code);
            if ((value.district_code == select_district_code)) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
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
        $.each(gps, function(key, value) {
          if ((value.district_code == select_district_code) && (value.block_code == block_code)) {
            htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
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
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
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
      window.location.href = 'lb-caste-application-list';
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
        "processing": "Processing...",
        "emptyTable": "No data available in table",
        "zeroRecords": "No matching records found"
      },
      "ajax": {
        url: "{{ url('lb-caste-application-list') }}",
        type: "POST",
        data: function(d) {
          d.ds_phase = $('#ds_phase').val(),
            d.district_code = "{{ $district_code }}",
            d.rural_urbanid = $('#rural_urbanid').val(),
            d.urban_body_code = $('#urban_body_code').val(),
            d.block_ulb_code = $('#block_ulb_code').val(),
            d.gp_ward_code = $('#gp_ward_code').val(),
            d.caste = $('#caste_category').val(),
            d.search_value = d.search.value;
            d.search = '';
            d._token = "{{csrf_token()}}"
        },
        error: function(ex) {

          // alert(sessiontimeoutmessage);
          // window.location.href = base_url;
          console.log(ex);
        }
      },
      "columns": [

        {
          "data": "beneficiary_id"
        },
        {
          "data": "application_id"
        },
        {
          "data": "ben_name"
        },
        {
          "data": "ss_card_no"
        },
        {
          "data": "mobile_no"
        },
        {
          "data": "status"
        }
      ],

      "buttons": [{
          extend: 'pdf',
          footer: true,
          exportOptions: {
            columns: [0, 1, 2, 3]
          },
          className: 'table-action-btn'
        },
        {
          extend: 'print',
          footer: true,
          exportOptions: {
            columns: [0, 1, 2, 3]
          },
          className: 'table-action-btn'
        },
        {
          extend: 'excel',
          footer: true,
          exportOptions: {
            columns: [0, 1, 2, 3]
          },
          className: 'table-action-btn'
        },
        {
          extend: 'copy',
          footer: true,
          exportOptions: {
            columns: [0, 1, 2, 3]
          },
          className: 'table-action-btn'
        },
        {
          extend: 'csv',
          footer: true,
          exportOptions: {
            columns: [0, 1, 2, 3]
          },
          className: 'table-action-btn'
        }
      ]
    });
  });
</script>


@endpush