@extends('layouts.app-template-datatable')

@push('styles')
  <style>
    .card-header-custom {
      background: linear-gradient(to right, #4e73df, #6f9bff);
      color: #fff;
      padding: 15px;
      font-size: 18px;
      font-weight: bold;
      border-radius: 5px 5px 0 0;
    }

    .dt-container,
    #example_wrapper {
      width: 100% !important;
    }

    .modal-confirm .modal-content {
      padding: 20px;
      border-radius: 8px;
    }

    .modal-title {
      font-size: 20px;
      font-weight: bold;
    }

    .required-field::after {
      content: "*";
      color: red;
      margin-left: 4px;
    }

    .custom-short-btn {
      padding: 4px 12px !important;
      font-size: 13px !important;
      height: 40px;
      line-height: 1.2 !important;
      min-height: 0 !important;
      width: auto !important;
    }
  </style>
@endpush


@section('content')
  <section class="content">
    <div class="container-fluid">

      {{-- ===================== ALERT MESSAGES ===================== --}}
      <div class="row mt-2">
        @if(session('success'))
          <div class="col-md-12">
            <div class="alert alert-success alert-dismissible fade show">
              <strong>{{ session('success') }}</strong>
              <button type="button" class="close" data-dismiss="alert">×</button>
            </div>
          </div>
        @endif

        @if(session('error'))
          <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show">
              <strong>{{ session('error') }}</strong>
              <button type="button" class="close" data-dismiss="alert">×</button>
            </div>
          </div>
        @endif

        @if($errors->any())
          <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li><strong>{{ $error }}</strong></li>
                @endforeach
              </ul>
              <button type="button" class="close" data-dismiss="alert">×</button>
            </div>
          </div>
        @endif
      </div>
      {{-- ===================== END ALERTS ===================== --}}



      {{-- ===================== FILTER CARD ===================== --}}
      <div class="card shadow-lg mt-3">
        <div class="card-header card-header-custom">
          <h5 class="mb-0"><i class="fas fa-filter"></i> Application Filtering</h5>
        </div>

        <div class="card-body">

          <form method="POST" action="{{ route('BulkApprovenoaadhar') }}" name="form" id="form">
            @csrf
            <input type="hidden" id="scheme_id" name="scheme_id" value="{{ $scheme_id }}">
            <input type="hidden" name="dist_code" id="dist_code" value="{{ $district_code }}">
            <input type="hidden" name="application_id" id="application_id">

            <div class="row">

              {{-- Application Type --}}
              <div class="form-group col-md-4">
                <label class="required-field">Application Type</label>
                <select name="application_type" id="application_type" class="form-control select2">
                  <option value="1">Pending</option>

                  @if($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier')
                    <option value="2">Verified but Approval Pending</option>
                  @endif

                  <option value="3">Verified and Approved</option>
                </select>
              </div>

              {{-- Gram Panchayat (Block Users) --}}
              @if($verifier_type == 'Block')
                <div class="form-group col-md-4">
                  <label>Gram Panchayat</label>
                  <select name="gp_ward_code" id="gp_ward_code" class="form-control select2">
                    <option value="">-- Select --</option>
                    @foreach ($gps as $gp)
                      <option value="{{ $gp->gram_panchyat_code }}">{{ $gp->gram_panchyat_name }}</option>
                    @endforeach
                  </select>
                </div>
              @endif

              {{-- Municipality & Ward (Subdivision Users) --}}
              @if($verifier_type == 'Subdiv')
                <div class="form-group col-md-3">
                  <label>Municipality</label>
                  <select name="block_ulb_code" id="block_ulb_code" class="form-control select2">
                    <option value="">-- Select --</option>
                    @foreach ($urban_bodys as $urban)
                      <option value="{{ $urban->urban_body_code }}">{{ $urban->urban_body_name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-3">
                  <label>Wards</label>
                  <select name="gp_ward_code" id="gp_ward_code" class="form-control select2">
                    <option value="">-- Select --</option>
                  </select>
                </div>
              @endif

              {{-- Urban / Rural + Block for Approver --}}
              @if($designation_id == 'Approver' || $designation_id == 'Delegated Approver')

                <div class="form-group col-md-3">
                  <label>Urban / Rural</label>
                  <select name="rural_urban_code" id="rural_urban_code" class="form-control select2">
                    <option value="">-- All --</option>
                    @foreach(Config::get('constants.rural_urban') as $key => $val)
                      <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-3">
                  <label>Block / Sub Division</label>
                  <select name="created_by_local_body_code" id="created_by_local_body_code" class="form-control select2">
                    <option value="">-- Select --</option>
                  </select>
                </div>
              @endif


              {{-- Filter Buttons --}}
              <div class="form-group col-md-4 mt-4 p-2">
                <button type="button" name="filter" id="filter" class="btn btn-info"><i class="fas fa-search"></i>
                  Filter</button>
                <button type="button" name="reset" id="reset" class="btn btn-secondary ml-2"><i class="fas fa-undo"></i>
                  Reset</button>
                @if($verifier_type == 'District')

                  <input type="hidden" name="_token" value="{{ csrf_token() }}">
                  {{-- <button type="button" name="bulk_approve" id="confirm" value="approve"
                    class="btn btn-primary btn-margin btn-sm custom-short-btn" disabled>
                    Bulk Approve
                  </button> --}}
                @endif
              </div>


            </div>
          </form>

        </div>
      </div>
      {{-- ===================== END FILTER CARD ===================== --}}



      {{-- ===================== DATATABLE ===================== --}}
      <div class="card shadow-lg border-0 mt-4 rounded-3">
        <div class="card-header card-header-custom">
          <h5 class="mb-0 fw-bold text-dark">Application List</h5>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table id="example" class="data-table">
              <thead>
                <tr class="text-center">
                  <th>Application ID</th>
                  <th>Beneficiary Name</th>
                  <th>Mobile No</th>

                  @if($verifier_type == 'Subdiv' || $verifier_type == 'District')
                    <th>Block / Municipality</th>
                  @endif

                  <th>GP / Ward</th>
                  <th>Aadhaar No</th>
                  <th>Action</th>

                  {{-- @if($verifier_type == 'District')
                    <th>Select</th>
                  @endif --}}
                </tr>
              </thead>

              <tbody></tbody>
            </table>
          </div>


        </div>
      </div>
      {{-- ===================== END DATATABLE ===================== --}}



      {{-- ===================== APPROVE MODAL ===================== --}}
      <div id="modalConfirm" class="modal fade">
        <div class="modal-dialog modal-confirm">
          <div class="modal-content">

            <div class="modal-body text-center">
              <h4 class="modal-title">Do you really want to Approve?</h4>
            </div>

            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

              <button type="submit" class="btn btn-primary" id="confirm_yes">
                <i class="fas fa-check"></i> OK
              </button>

              <button type="button" class="btn btn-success" id="submittingapprove" disabled>
                <i class="fas fa-spinner fa-spin"></i> Submitting...
              </button>
            </div>

          </div>
        </div>
      </div>
      {{-- ===================== END MODAL ===================== --}}

    </div>
  </section>
@endsection
@push('scripts')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
  <script>
    $(document).ready(function () {
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #lb-aadhar').addClass("active");
      $('.sidebar-menu #noaadharlist').addClass("active");
      $("#confirm").hide();
      $("#submittingapprove").hide();

      var base_url = '{{ url('/') }}';
      var block_ulb_code = $("#block_ulb_code").val();
      var gp_ward_code = $("#gp_ward_code").val();
      var application_type = $("#application_type").val();
      fill_datatable(block_ulb_code, gp_ward_code, application_type);
      function fill_datatable(block_ulb_code = '', gp_ward_code = '', application_type = '') {
        //console.log(process_type);
        var scheme_id = $("#scheme_id").val();
        var dataTable = $('#example').DataTable({
          dom: 'Blfrtip',
          oLanguage: {
            "sSearch": "Search using Application Id/Mobile Number:"
          },
          paging: true,
          searching: true,
          pageLength: 100,
          ordering: false,
          lengthMenu: [[20, 50, 100, 500, 1000, -1], [20, 50, 100, 500, 1000, 'All']],
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ url('noaadharlist') }}",
            type: "GET",
            data: function (d) {
              d.application_type = application_type,
                d.block_ulb_code = block_ulb_code,
                d.gp_ward_code = gp_ward_code,
                d.scheme_id = scheme_id,
                d.type = $('#type').val(),
                d._token = "{{csrf_token()}}"
            },
            error: function (ex) {
              //console.log(ex);
              //alert('Session time out..Please login again');
              // window.location.href=base_url;
            }
          },
          columns: [

            { "data": "application_id" },
            { "data": "name" },
            { "data": "mobile_no" },
            @if($verifier_type == 'Subdiv' || $verifier_type == 'District')
              { "data": "block_ulb_name" },
            @endif
            { "data": "gp_ward_name" },
            { "data": "aadhar_no" },
            { "data": "action" },
            @if($verifier_type == 'District')
              { "data": "check" },
            @endif
                   // { "data": "check" },


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
            extend: 'csv',
            footer: true,
            exportOptions: {
              columns: [0, 1, 2, 3]
            },
            className: 'table-action-btn'
          }

          ],


        });


      }

      $('#filter').click(function () {
        var block_ulb_code = $('#block_ulb_code').val();
        var gp_ward_code = $('#gp_ward_code').val();
        var application_type = $('#application_type').val();
        var designation_id = $('#designation_id').val();
        var error_application_type = '';
        var error_process_type = '';
        if (application_type == '') {
          error_application_type = 'Application Type is required';
          $('#error_application_type').text(error_application_type);
          $('#application_type').addClass('has-error');
        }
        else {
          error_application_type = '';
          $('#error_application_type').text(error_application_type);
          $('#application_type').removeClass('has-error');
        }

        if (error_application_type == '') {
          //console.log(process_type);
          $('#example').DataTable().destroy();
          fill_datatable(block_ulb_code, gp_ward_code, application_type);
        }


      });
      $('#block_ulb_code').change(function () {
        var municipality_code = $(this).val();
        if (municipality_code != '') {
          $('#gp_ward').html('<option value="">--All --</option>');
          var htmlOption = '<option value="">--All--</option>';
          $.each(ulb_wards, function (key, value) {
            if (value.urban_body_code == municipality_code) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
          $('#gp_ward_code').html(htmlOption);
        }
        else {
          $('#gp_ward_code').html('<option value="">--All --</option>');
        }
      });
      $('#rural_urban_code').change(function () {
        var urban_code = $(this).val();
        if (urban_code == '') {
          $('#created_by_local_body_code').html('<option value="">--All --</option>');
        }
        $('#created_by_local_body_code').html('<option value="">--All --</option>');
        select_district_code = $('#dist_code').val();
        //console.log(select_district_code);

        select_body_type = urban_code;
        var htmlOption = '<option value="">--All--</option>';
        if (select_body_type == 2) {
          $("#blk_sub_txt").text('Block');
          $.each(blocks, function (key, value) {
            if (value.district_code == select_district_code) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
        } else if (select_body_type == 1) {
          $("#blk_sub_txt").text('Subdivision');
          $.each(subDistricts, function (key, value) {
            if (value.district_code == select_district_code) {
              htmlOption += '<option value="' + value.id + '">' + value.text + '</option>';
            }
          });
        }
        else {
          $("#blk_sub_txt").text('Block/Subdivision');
        }
        $('#created_by_local_body_code').html(htmlOption);


      });
      $(document).on('click', '.validate', function () {
        $('#form #application_id').val('');
        $('#application_text_approve').text('');
        $('.validate').attr('disabled', false);
        var benid = $(this).val();
        var split_id = benid.split('_');
        $('#validatebtn_' + benid).attr('disabled', true);
        $('#form #application_id').val(split_id[0]);
        $('#form #is_faulty').val(split_id[1]);
        $('#application_text_approve').text(split_id[0]);
        $('#modalConfirm').modal();
      });
      $('#reset').click(function () {
        $('#application_type').val('');
        $('#gp_code').val('');
        $('#gp_code').val('');
        $('#example').DataTable().destroy();
        fill_datatable();
      });
      $('#confirm').click(function () {
        $('#modalConfirm').modal('show');
      });
      $('#confirm_yes').on('click', function () {
        $("#confirm_yes").hide();
        $("#submittingapprove").show();
        $("#form").submit();


      });

    });
    function controlCheckBox() {
      // 1. Get the button element
      // alert('1');
      var confirmButton = document.getElementById('confirm');
      if (confirmButton) {
        var anyBoxesChecked = false;
        $('input[type="checkbox"]').each(function () {
          if ($(this).is(":checked")) {
            anyBoxesChecked = true;
          }
        });

        if (anyBoxesChecked == true) {
          $("#confirm").show();
          confirmButton.disabled = false;
        } else {
          $("#confirm").hide();
          confirmButton.disabled = true;
        }
      }
    }
  </script>
@endpush