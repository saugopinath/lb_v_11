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
  </style>
@endpush
@section('content')
  <!-- <div class="content-wrapper"> -->
  <!-- Page Header -->
  <section class="content-header">
    <h1>Process Application</h1>
  </section>

  <section class="content">
    <div class="card card-default">
      <div class="card-body">

        <!-- Filters Panel -->
        <div class="card card-primary shadow-sm">
          <div class="card-header">
            <h3 class="card-title" id="panel-icon">Applications Yet To Be Verified</h3>
          </div>

          <div class="card-body p-3">
            <div class="row">
              @if(($message = Session::get('success')))
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

            <div class="row">
              <div class="col-md-3 mb-3">
                <label>Please Choose:</label>
                <select name="ds_phase" id="ds_phase" class="form-select">
                  <optgroup label="Normal Entry">
                    <option value="0">Normal Entry</option>
                  </optgroup>
                  <optgroup label="Form through Duare Sarkar camp">
                    @foreach($ds_phase_list as $ds_row)
                      <option value="{{$ds_row->phase_code}}" @if($ds_row->is_current == TRUE) selected @endif>
                        {{$ds_row->phase_des}}
                      </option>
                    @endforeach
                  </optgroup>
                </select>
                <span id="error_ds_phase" class="text-danger"></span>
              </div>

              <div class="col-md-3 mb-3">
                <label>Gram Panchayat</label>
                <select name="gp_code" id="gp_code" class="form-select">
                  <option value="">-----Select----</option>
                  @foreach($gps as $gp)
                    <option value="{{$gp->gram_panchyat_code}}">{{$gp->gram_panchyat_name}}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 mb-3">
                <label>Caste</label>
                <select class="form-select" name="caste_category" id="caste_category">
                  <option value="">--All--</option>
                  @foreach(Config::get('constants.caste_lb') as $key => $val)
                    <option value="{{$key}}">{{$val}}</option>
                  @endforeach
                </select>
                <span id="error_caste_category" class="text-danger"></span>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-2 offset-md-1">
                <button type="button" id="filter" class="btn btn-success w-100">Search</button>
              </div>

              <div class="col-md-2 offset-md-1">
                <button type="button" id="reset" class="btn btn-warning w-100">Reset</button>
              </div>
            </div>

          </div>
        </div>

        <!-- DataTable Panel -->
        <div class="card card-secondary mt-3 shadow-sm">
          <div class="card-header">
            <h3 class="card-title" id="panel_head">List of New Applicants</h3>
          </div>

          <div class="card-body p-2" style="font-size:14px;">
            <div class="table-responsive">
              <table id="example" class="data-table ">
                <thead >
                  <tr>
                    <th>Application ID</th>
                    <th>Applicant Name</th>
                    <th>Age</th>
                    <th>Swasthya Sathi Card No.</th>
                    <th>Mobile No</th>
                    <th>Duare Sarkar/Samasyaa Samadhan</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody style="font-size:14px;"></tbody>
              </table>
            </div>

            <p id="age_restricted" class="text-warning mt-2">
              ** This Swastha Sathi Card Number Previously Rejected.
            </p>
          </div>
        </div>

      </div>
    </div>

    <!-- MAIN APPLICANT MODAL -->
    <div class="modal fade ben_view_modal" id="benViewModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <h3 class="modal-title">
              Applicant Details (<span class="applicant_id_modal"></span>)
            </h3>

            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <center>
            <h4 id="dupBankText" class="mt-1" style="color:red; display:none;">
              N.B: Can't verify due to duplicate bank account
            </h4>
          </center>

          <div class="modal-body ben_view_body">

            <!-- PERSONAL DETAILS — Converted from panel-group to accordion -->
            <div class="accordion mb-2" id="accordionPersonal">
              <div class="card">
                <div class="card-header" id="headingPersonal">
                  <div class="preloader1">
                    <img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_personal">
                  </div>

                  <h4 class="card-title">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                      data-bs-target="#collapsePersonal">
                      Personal Details
                    </button>
                  </h4>
                </div>

                <div id="collapsePersonal" class="accordion-collapse collapse show">
                  <div class="card-body">

                    <table class="table table-bordered">
                      <tbody>

                        <tr>
                          <th>Swasthya Sathi Card No.</th>
                          <td id="sws_card_txt"></td>

                          <th>Aadhaar No.</th>
                          <td id="aadhar_no_encrypt"></td>
                          <td id="aadhar_no_original" style="display:none;"></td>

                          <td id="aadhar_no_or">
                            <button class="btn btn-info showhideAadhar" id="show_hide_aadhar">
                              Show Original Aadhaar
                            </button>
                          </td>
                        </tr>

                        <tr>
                          <th>Duare Sarkar/Samasyaa Samadhan Registration no.</th>
                          <td id="duare_sarkar_registration_no"></td>

                          <th>Duare Sarkar/Samasyaa Samadhan Date:</th>
                          <td id="duare_sarkar_date"></td>
                        </tr>

                        <tr>
                          <th>Name</th>
                          <td id="ben_fullname"></td>
                        </tr>

                        <tr>
                          <th>Mobile No.</th>
                          <td id="mobile_no"></td>

                          <th>Email</th>
                          <td id="email_id"></td>
                        </tr>

                        <tr>
                          <th>Gender</th>
                          <td id="gender"></td>

                          <th>DOB</th>
                          <td id="dob"></td>

                          <th>Age (as on {{$dob_base_date}})</th>
                          <td id="ben_age"></td>
                        </tr>

                        <tr>
                          <th>Father Name</th>
                          <td id="father_fullname"></td>
                        </tr>

                        <tr>
                          <th>Mother Name</th>
                          <td id="mother_fullname"></td>
                        </tr>

                        <tr>
                          <th>Spouse Name</th>
                          <td id="spouse_name"></td>
                        </tr>

                        <tr>
                          <th>Caste</th>
                          <td id="caste"></td>

                          <th class="caste">SC/ST Certificate No.</th>
                          <td id="caste_certificate_no" class="caste"></td>
                        </tr>

                      </tbody>
                    </table>

                  </div>
                </div>
              </div>
            </div>

            <!-- ADDRESS DETAILS -->
            <div class="accordion mb-2" id="accordionAddress">
              <div class="card">
                <div class="card-header" id="headingAddress">
                  <div class="preloader1">
                    <img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_contact">
                  </div>

                  <h4 class="card-title">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                      data-bs-target="#collapseContact">
                      Address Details
                    </button>
                  </h4>
                </div>

                <div id="collapseContact" class="accordion-collapse collapse">
                  <div class="card-body">

                    <table class="table table-bordered">
                      <tbody>

                        <tr>
                          <th>District Name</th>
                          <td id="dist_name"></td>

                          <th>Police Station</th>
                          <td id="police_station"></td>
                        </tr>

                        <tr>
                          <th>Block/Municipality Name</th>
                          <td id="block_ulb_name"></td>

                          <th>Gp Ward Name</th>
                          <td id="gp_ward_name"></td>
                        </tr>

                        <tr>
                          <th>Village/Town/City</th>
                          <td id="village_town_city"></td>

                          <th>House / Premise No</th>
                          <td id="house_premise_no"></td>
                        </tr>

                        <tr>
                          <th>Post Office</th>
                          <td id="post_office"></td>

                          <th>Pincode</th>
                          <td id="pincode"></td>
                        </tr>

                      </tbody>
                    </table>

                  </div>
                </div>
              </div>
            </div>

            <!-- BANK DETAILS -->
            <div class="accordion mb-2" id="accordionBank">
              <div class="card">
                <div class="card-header" id="headingBank">
                  <div class="preloader1">
                    <img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_bank">
                  </div>

                  <h4 class="card-title">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                      data-bs-target="#collapseBank">
                      Bank Details
                    </button>
                  </h4>
                </div>

                <div id="collapseBank" class="accordion-collapse collapse">
                  <div class="card-body">

                    <table class="table table-bordered">
                      <tbody>
                        <tr>
                          <th>Bank Name</th>
                          <td id="bank_name"></td>

                          <th>Branch Name</th>
                          <td id="branch_name"></td>
                        </tr>

                        <tr>
                          <th>Bank IFSC</th>
                          <td id="bank_ifsc"></td>

                          <th>Bank Account No.</th>
                          <td id="bank_code"></td>
                        </tr>
                      </tbody>
                    </table>

                  </div>
                </div>
              </div>
            </div>

            <!-- ENCLOSURE DETAILS -->
            <div class="accordion mb-2" id="accordionEnclosure">
              <div class="card">
                <div class="card-header" id="headingEnclosure">
                  <div class="preloader1">
                    <img src="{{ asset('images/ZKZg.gif') }}" class="loader_img" width="150px" id="loader_img_encolser">
                  </div>

                  <h4 class="card-title">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                      data-bs-target="#collapseEncloser">
                      Enclosure Details
                    </button>
                  </h4>
                </div>

                <div id="collapseEncloser" class="accordion-collapse collapse">
                  <div class="card-body">
                    <table id="enCloserTable">
                      <tbody></tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- ACTION SECTION -->
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Action</h4>
              </div>

              <div class="card-body">
                <div class="row">

                  <div class="col-md-4">
                    <label>Select Operation</label>
                    <select name="opreation_type" id="opreation_type" class="form-select">
                      <option value="">--Select--</option>
                      @if($verification_allowded)
                        <option value="V">Verify</option>
                      @endif
                      <option value="R">Rejected</option>
                      <option value="T">Reverted</option>
                    </select>
                  </div>

                  <div class="col-md-4" id="div_rejection" style="display:none;">
                    <label>Select Reject/Reverted Cause</label>
                    <select id="reject_cause" name="reject_cause" class="form-select">
                      <option value="">--Select--</option>
                      @foreach($reject_revert_reason as $r_arr)
                        <option value="{{$r_arr->id}}">{{$r_arr->reason}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label>Enter Remarks</label>
                    <textarea class="form-control" id="accept_reject_comments" name="accept_reject_comments"
                      rows="2"></textarea>
                  </div>

                </div>
              </div>
            </div>

          </div>

          <div class="modal-footer">

            <form method="POST" action="{{ route('application-details-read-only') }}" target="_blank" name="fullForm"
              id="fullForm">
              @csrf

              <input type="hidden" id="scheme_id" name="scheme_id" value="{{$scheme_id}}" />
              <input type="hidden" id="id" name="id" />
              <input type="hidden" id="application_id" name="application_id" />
              <input type="hidden" id="is_draft" name="is_draft" value="1" />

              <button type="submit" class="btn btn-primary">View Full Details</button>

              <button type="button" class="btn btn-success" id="verifyReject">
                Submit
              </button>

              <button type="button" style="display:none;" id="submitting" class="btn btn-success" disabled>
                Processing Please Wait
              </button>

            </form>

          </div>

        </div>
      </div>
    </div>


    <!-- ENCLOSURE VIEW MODAL -->
    <div class="modal fade" id="encolser_modal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title" id="encolser_name"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div id="encolser_content" class="p-3"></div>

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
      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var base_url = '{{ url('/') }}';
      // $('.panel-collapse').on('show.bs.collapse', function () {
      $(document).on('show.bs.collapse', '.panel-collapse', function () {



        var id = $(this).attr('id');
        var application_id = $('#fullForm #application_id').val();
        //alert(application_id);
        if (id == 'collapsePersonal') {
          $('#sws_card_txt').text('');
          $('#duare_sarkar_registration_no').text('');
          $('#duare_sarkar_date').text('');
          $('#ben_fullname').text('');
          $('#father_fullname').text('');
          $('#mother_fullname').text('');
          $('#caste').text('');
          $('#gender').text('');
          $('#ben_age').text('');
          $('#mobile_no').text('');
          $('#email').text('');
          $('#aadhar_no_encrypt').text('');
          $('#loader_img_personal').show();
          $.ajax({
            type: 'post',
            url: "{{route('getBenViewPersonalData')}}",
            data: { _token: '{{csrf_token()}}', benid: application_id },
            dataType: 'json',
            success: function (response) {
              // alert(response.personaldata.ss_card_no);
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled', true);
              $('#sws_card_txt').text(response.personaldata.ss_card_no);
              $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
              $('#duare_sarkar_registration_no').text(response.personaldata.duare_sarkar_registration_no);
              $('#duare_sarkar_date').text(response.personaldata.formatted_duare_sarkar_date);
              $('#ben_fullname').text(response.personaldata.ben_fname);
              $('#mobile_no').text(response.personaldata.mobile_no);
              $('#email').text(response.personaldata.email);
              $('#gender').text(response.personaldata.gender);
              $('#dob').text(response.personaldata.formatted_dob);
              $('#ben_age').text(response.personaldata.age_ason_01012021);
              $('#father_fullname').text(response.personaldata.father_fname + ' ' + response.personaldata.father_mname + ' ' + response.personaldata.father_lname);
              $('#mother_fullname').text(response.personaldata.mother_fname + ' ' + response.personaldata.mother_mname + ' ' + response.personaldata.mother_lname);
              $('#caste').text(response.personaldata.caste);
              if (response.personaldata.caste == 'SC' || response.personaldata.caste == 'ST') {
                $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
                $('.caste').show();
              }
              else {
                $('.caste').hide();
              }
              $('#spouse_name').text(response.personaldata.spouse_fname + ' ' + response.personaldata.spouse_mname + ' ' + response.personaldata.spouse_lname);

            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_personal').hide();
              $('.ben_view_button').removeAttr('disabled', true);
              alert(sessiontimeoutmessage);
              window.location.href = base_url;
            }
          });
        }
        else if (id == 'collapseContact') {
          $('#loader_img_contact').show();
          $('#dist_name').text('');
          $('#block_ulb_name').text('');
          $('#gp_ward_name').text('');
          $('#village_town_city').text('');
          $('#police_station').text('');
          $('#post_office').text('');
          $('#pincode').text('');
          $.ajax({
            type: 'post',
            url: "{{route('getBenViewContactData')}}",
            data: { _token: '{{csrf_token()}}', benid: application_id },
            dataType: 'json',
            success: function (response) {
              //console.log(response.contactdata);
              $('#loader_img_contact').hide();
              $('.ben_view_button').removeAttr('disabled', true);
              $('#dist_name').text(response.contactdata.dist_name);
              $('#block_ulb_name').text(response.contactdata.block_ulb_name);
              $('#gp_ward_name').text(response.contactdata.gp_ward_name);
              $('#village_town_city').text(response.contactdata.village_town_city);
              $('#police_station').text(response.contactdata.police_station);
              $('#post_office').text(response.contactdata.post_office);
              $('#pincode').text(response.contactdata.pincode);
              $('#house_premise_no').text(response.contactdata.house_premise_no);

            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_contact').hide();
              $('.ben_view_button').removeAttr('disabled', true);
              alert(sessiontimeoutmessage);
              window.location.href = base_url;
            }
          });
        }
        else if (id == 'collapseBank') {
          $('#loader_img_bank').show();
          $('#bank_name').text('');
          $('#branch_name').text('');
          $('#bank_ifsc').text('');
          $('#bank_code').text('');
          $.ajax({
            type: 'post',
            url: "{{route('getBenViewBankData')}}",
            data: { _token: '{{csrf_token()}}', benid: application_id },
            dataType: 'json',
            success: function (response) {
              $('#loader_img_bank').hide();
              $('.ben_view_button').removeAttr('disabled', true);
              $('#bank_name').text(response.bankdata.bank_name);
              $('#branch_name').text(response.bankdata.branch_name);
              $('#bank_ifsc').text(response.bankdata.bank_ifsc);
              $('#bank_code').text(response.bankdata.bank_code);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_bank').hide();
              $('.ben_view_button').removeAttr('disabled', true);
              alert(sessiontimeoutmessage);
              window.location.href = base_url;
            }
          });
        } else if (id == 'collapseEncloser') {
          $('#loader_img_encolser').show();
          $("#enCloserTable tbody").empty();
          $.ajax({
            type: 'post',
            url: "{{route('getBenViewEncloserData')}}",
            data: { _token: '{{csrf_token()}}', benid: application_id },
            dataType: 'json',
            success: function (response) {
              $('#loader_img_encolser').hide();
              //console.log(response.html);
              $('.ben_view_button').removeAttr('disabled', true);
              $("#enCloserTable tbody").html(response.html);
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#loader_img_encolser').hide();
              $('.ben_view_button').removeAttr('disabled', true);
              alert(sessiontimeoutmessage);
              window.location.href = base_url;
            }
          });
        }
        $(this).siblings('.panel-heading').addClass('active');

      });
      $(document).on('hide.bs.collapse', '.panel-collapse', function () {
        //$('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).siblings('.panel-heading').removeClass('active');

      });
      $('.loader_img').hide();
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #lk-main').addClass("active");
      $('.sidebar-menu #processApplication').addClass("active");
      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var base_url = '{{ url('/') }}';
      var verification_allowded = '{{$verification_allowded}}';
      if (verification_allowded == 1) {
        $('#opreation_type').val('');
        $("#verifyReject").html("Submit");
      }
      else {
        // alert(verification_allowded);
        $('#opreation_type').val('R');
        $("#verifyReject").html("Reject");
        $('#div_rejection').show();
      }


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
        'lengthMenu': [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        "serverSide": true,
        "processing": true,
        "bRetrieve": true,
        "oLanguage": {
          "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
        },
        "ajax":
        {
          url: "{{ url('workflow') }}",
          type: "post",
          data: function (d) {
            d.filter_1 = $("#gp_code").val(),
              d.caste_category = $("#caste_category").val(),
              d.ds_phase = $("#ds_phase").val(),
              d._token = "{{csrf_token()}}"
          },
          error: function (jqXHR, textStatus, errorThrown) {
            $('.preloader1').hide();
            alert(sessiontimeoutmessage);
            window.location.href = base_url;
          }
        },
        "initComplete": function () {
          //console.log('Data rendered successfully');
        },
        "columns": [
          { "data": "id" },
          { "data": "name" },
          { "data": "age" },
          { "data": "ss_card_no" },
          { "data": "mobile_no" },
          { "data": "duare_sarkar_registration_no" },
          { "data": "view" }
        ],
        "columnDefs": [
          {
            "targets": [4, 5],
            "visible": false,
            "searchable": true
          },
        ],
        "buttons": [
          {
            extend: 'pdf',

            title: 'Process Application Report  <?php echo date('d-m-Y');  ?>',
            messageTop: 'Date:<?php echo date('d/m/Y');  ?>',
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

            title: 'Process Application Report <?php echo date('d-m-Y');  ?>',
            messageTop: 'Date:<?php echo date('d/m/Y');  ?>',
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
              columns: [0, 1, 2, 3, 4, 5],
              stripHtml: false,
            }
          },
        ],
        "rowCallback": function (row, data, index) {
          if (data['eariler_rejected'] == 1) {
            $('td', row).css('background-color', 'Orange');
          }

        }
      });

      $(document).on('click', '.ben_view_button', function () {
        $('#loader_img_personal').show();
        $('.ben_view_button').attr('disabled', true);
        var benid = $(this).val();
        $('#fullForm #application_id').val(benid);
        $('.applicant_id_modal').html(benid);
        $("#collapseContact").collapse('hide');
        $("#collapseBank").collapse('hide');
        $("#collapseEncloser").collapse('hide');
        $('#duare_sarkar_registration_no').text('');
        $('#duare_sarkar_date').text('');
        $.ajax({
          type: 'post',
          url: "{{route('getBenViewPersonalData')}}",
          data: { _token: '{{csrf_token()}}', benid: benid },

          dataType: 'json',
          success: function (response) {
            $('#loader_img_personal').hide();
            $('.ben_view_button').removeAttr('disabled', true);
            $('#sws_card_txt').text(response.personaldata.ss_card_no);
            $('#aadhar_no_encrypt').text(response.personaldata.aadhar_no);
            $('#duare_sarkar_registration_no').text(response.personaldata.duare_sarkar_registration_no);
            $('#duare_sarkar_date').text(response.personaldata.formatted_duare_sarkar_date);
            if (response.personaldata.ben_mname !== undefined && response.personaldata.ben_mname !== null) {
              //console.log((response.personaldata.ben_mname);
              var ben_mname = response.personaldata.ben_mname;
            }
            else {
              var ben_mname = "";
            }
            if (response.personaldata.ben_lname !== undefined && response.personaldata.ben_lname !== null) {
              var ben_lname = response.personaldata.ben_lname;
            }
            else {
              var ben_lname = "";
            }
            $('#ben_fullname').text(response.personaldata.ben_fname);
            $('#mobile_no').text(response.personaldata.mobile_no);
            $('#email').text(response.personaldata.email);
            $('#gender').text(response.personaldata.gender);
            $('#dob').text(response.personaldata.formatted_dob);
            $('#ben_age').text(response.personaldata.age_ason_01012021);
            if (response.personaldata.father_mname !== undefined && response.personaldata.father_mname !== null) {
              var father_mname = response.personaldata.father_mname;
            }
            else {
              var father_mname = "";
            }
            if (response.personaldata.father_lname !== undefined && response.personaldata.father_lname != null) {
              var father_lname = response.personaldata.father_lname;
            }
            else {
              var father_lname = "";
            }
            $('#father_fullname').text(response.personaldata.father_fname + ' ' + father_mname + ' ' + father_lname);
            if (response.personaldata.mother_mname !== undefined && response.personaldata.mother_mname != null) {
              var mother_mname = response.personaldata.mother_mname;
            }
            else {
              var mother_mname = "";
            }
            if (response.personaldata.mother_lname !== undefined && response.personaldata.mother_lname != null) {
              var mother_lname = response.personaldata.mother_lname;
            }
            else {
              var mother_lname = "";
            }
            $('#mother_fullname').text(response.personaldata.mother_fname + ' ' + mother_mname + ' ' + mother_lname);
            $('#caste').text(response.personaldata.caste);
            if (response.personaldata.caste == 'SC' || response.personaldata.caste == 'ST') {
              $('#caste_certificate_no').text(response.personaldata.caste_certificate_no);
              $('.caste').show();
            }
            else {
              $('.caste').hide();
            }
            if (response.personaldata.spouse_fname !== undefined && response.personaldata.spouse_fname !== null) {
              var spouse_fname = response.personaldata.spouse_fname;
            }
            else {
              var spouse_fname = "";
            }
            if (response.personaldata.spouse_mname !== undefined && response.personaldata.spouse_mname !== null) {
              //console.log((response.personaldata.ben_mname);
              var spouse_mname = response.personaldata.spouse_mname;
            }
            else {
              var spouse_mname = "";
            }
            if (response.personaldata.spouse_lname !== undefined && response.personaldata.spouse_lname !== null) {
              var spouse_lname = response.personaldata.spouse_lname;
            }
            else {
              var spouse_lname = "";
            }
            // alert(response.personaldata.is_bank_dup);
            if (response.personaldata.is_bank_dup == '1') {
              $('#dupBankText').show();
              $("#opreation_type option[value='V']").prop("disabled", true);
            }
            else {
              $('#dupBankText').hide();
              $("#opreation_type option[value='V']").prop("disabled", false);
            }
            $('#spouse_name').text(spouse_fname + ' ' + spouse_mname + ' ' + spouse_lname);

            $('#fullForm #id').val(response.benid);

          },
          complete: function () {

          },
          error: function (jqXHR, textStatus, errorThrown) {
            $('#loader_img_personal').hide();
            $('.ben_view_button').removeAttr('disabled', true);
            alert(sessiontimeoutmessage);
            window.location.href = base_url;
          }
        });
        $('.ben_view_modal').modal('show');

      });




      $('#filter').click(function () {
        var filter_1 = $('#filter_1').val();
        if (filter_1 != '') {
          dataTable.ajax.reload();

        }
        else {
          alert('Please select two Filter Criterias');
        }
      });

      $('#reset').click(function () {

        location.reload();
      });
      $('.showhideAadhar').click(function () {
        var ButtonText = $(this).text();
        if (ButtonText == 'Show Original Aadhaar') {
          $("#aadhar_no_encrypt").hide();
          var applicant_id_modal = $(".applicant_id_modal").text();
          $("#aadhar_no_original").show();
          $('#aadhar_no_original').html('<img  src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');

          $.ajax({
            type: 'post',
            url: "{{route('getBenViewAadharData')}}",
            data: { _token: '{{csrf_token()}}', benid: applicant_id_modal },
            dataType: 'json',
            success: function (response) {
              // alert(response.aadhar_no);
              $('#aadhar_no_original').html('');
              $('#aadhar_no_original').html(response.aadhar_no);
              $("#show_hide_aadhar").text('Show Encrypted Aadhaar');
              $("#aadhar_no_original").show();

            },
            complete: function () {
              //$('#aadhar_no_original').html('');
            },
            error: function (jqXHR, textStatus, errorThrown) {
              $('#aadhar_no_original').html('');
              $('.ben_view_button').removeAttr('disabled', true);
              alert(sessiontimeoutmessage);
              window.location.href = base_url;
            }
          });
        }
        else if (ButtonText == 'Show Encrypted Aadhaar') {
          $(this).text('Show Original Aadhaar');
          $("#aadhar_no_encrypt").show();
          $("#aadhar_no_original").hide();
        }
      });
      $('#encolser_modal').on('hidden.bs.modal', function (e) {
        $('.ben_view_modal').css({
          'overflow': 'auto',
        });
        //$(".ben_view_modal").animate({ scrollTop: 0 }, "slow");
      });
      $(document).on('click', '.opreation_type', function () {
        if ($(this).val() == 'T' || $(this).val() == 'R') {
          $('#div_rejection').show();
          if ($(this).val() == 'T')
            $("#verifyReject").html("Revert");
          else if ($(this).val() == 'R')
            $("#verifyReject").html("Reject");
        }
        else if ($(this).val() == 'V') {
          $("#verifyReject").html("Verify");
          $('#div_rejection').hide();
          $("#reject_cause").val('');
        } else {
          $("#verifyReject").html("Submit");
          $('#div_rejection').hide();
          $("#reject_cause").val('');
        }
      });


      $(document).on('click', '#verifyReject', function () {

        var scheme_id = $('#scheme_id').val();
        var reject_cause = $('#reject_cause').val();
        var opreation_type = $('#opreation_type').val();
        var accept_reject_comments = $('#accept_reject_comments').val();
        var valid = 0;
        if (opreation_type == 'V') {
          var valid = 1;
        }
        if (opreation_type == 'R' || opreation_type == 'T') {
          var valid = 0;
          if (reject_cause != '') {
            var valid = 1;

          }
          else {
            Swal.fire({
              title: 'Error!!',
              html: '<strong>Please Select Cause</strong>',
              icon: 'error',
              iconColor: '#ff0000',
              confirmButtonText: 'OK'
            });

            return false;
          }

        }
        if (valid == 1) {
          $.confirm({
            title: 'Warning',
            type: 'orange',
            icon: 'fa fa-warning',
            content: '<strong>Are you sure to proceed?</strong>',
            buttons: {
              Ok: function () {
                $("#submitting").show();
                $("#verifyReject").hide();
                var id = $('#id').val();
                var ds_phase = $('#ds_phase').val();
                if (ds_phase == 0) {
                  var url = "{{ url('verifyDatawtSws') }}";
                }
                else {
                  // if(ds_phase<=6){
                  //   var url="{{ url('verifyData') }}";
                  // }
                  // else{
                  var url = "{{ url('verifyDatawtSws') }}";
                  // }
                }
                $.ajax({
                  type: 'POST',
                  url: url,
                  data: {
                    scheme_id: scheme_id,
                    reject_cause: reject_cause,
                    opreation_type: opreation_type,
                    accept_reject_comments: accept_reject_comments,
                    id: id,
                    _token: '{{ csrf_token() }}',
                  },
                  success: function (data) {
                    if (data.return_status) {
                      dataTable.ajax.reload(null, false);
                      $('.ben_view_modal').modal('hide');
                      $.confirm({
                        title: 'Success',
                        type: 'green',
                        icon: 'fa fa-check',
                        content: data.return_msg,
                        buttons: {
                          Ok: function () {

                            $("#submitting").hide();
                            $("#verifyReject").show();
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                          }
                        }
                      });
                      //printMsg(data.return_msg,'1','errorDivMain');



                    }
                    else {
                      if (data.return_msg == 'Aadhaar No. is Duplicate..') {
                        alert(data.return_msg);
                        window.location.href = 'workflow?pr1=lb_wcd';
                      }
                      else {
                        $("#submitting").hide();
                        $("#verifyReject").show();
                        $('#errorDiv').animate({ scrollTop: 0 }, 'slow');
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                      }
                    }
                  },
                  error: function (jqXHR, textStatus, errorThrown) {
                    $.confirm({
                      title: 'Error',
                      type: 'red',
                      icon: 'fa fa-warning',
                      content: sessiontimeoutmessage,
                      buttons: {
                        Ok: function () {
                          location.reload();
                        }
                      }
                    });

                  }
                });
              },
              Cancel: function () {

              },
            }
          });

        } else {
          $.alert({
            title: 'Error!!',
            type: 'red',
            icon: 'fa fa-warning',
            content: '<strong>Please Select Operation Type</strong>',
          });
          return false;
        }
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
        $("#encolser_modal").modal();
      }).fail(function (jqXHR, textStatus, errorThrown) {
        $('#encolser_content').html('');
        alert(sessiontimeoutmessage);
        window.location.href = base_url;
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