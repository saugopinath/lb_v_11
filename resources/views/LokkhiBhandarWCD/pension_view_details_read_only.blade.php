@extends('employees-mgmt.base_pension')

@section('action-content')
  <!-- <link href="{{ asset("/bower_components/AdminLTE/dist/new_css/lightbox.min.css")}}" rel="stylesheet" type="text/css" /> -->
  <section class="content" id="divToPrint">

    <div class="card card-primary card-outline">
      <!-- HEADER -->
      <div class="card-header bg-primary text-white d-flex justify-content-between">
        <h2 class="card-title mb-0">
          @if($designation_id == 'Operator')
            <a href="{{ url('lb-application-list') }}">
              <img width="50px" src="{{ asset('images/back.png') }}" alt="Back">
            </a>
          @endif
          View Application Form
        </h2>
      </div>

      <!-- BODY -->
      <div class="card-body">

        <!-- APPLICATION ID -->
        <div class="text-center">
          <h3 class="text-danger">
            @if($is_draft == 1)
              Application ID: {{$row->application_id}}
            @else
              Beneficiary ID: {{$ben_id}}
            @endif
          </h3>
        </div>

        <!-- REJECT INFO -->
        @if($is_reject == 1)
          <h3 class="text-center text-primary">Reject Reason: {{$row->rejected_cause}}</h3>
          <h3 class="text-center text-purple">Remark: {{$row->comments}}</h3>
        @endif

        <!-- REVERT INFO -->
        @if($row->next_level_role_id == -50)
          <h3 class="text-center text-primary">Reverted Reason: {{$row->rejected_cause}}</h3>
          @if(!empty($row->comments))
            <h3 class="text-center" style="color:purple;">Remark: {{$row->comments}}</h3>
          @endif
        @endif


        <!-- PERSONAL DETAILS CARD -->
        <div class="card card-primary card-outline mt-4">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title">Personal Details</h3>
          </div>

          <div class="card-body">
            <div class="row mb-2">
              @if(!empty($row->sws_card_no))
                <div class="col-md-6"><strong>Swasthyasathi Card No:</strong> {{$row->sws_card_no}}</div>
              @endif
              <div class="col-md-6"><strong>Aadhaar No:</strong> {{$row->aadhar_no}}</div>
            </div>

            <div class="row mb-2">
              @if(!empty($row->duare_sarkar_registration_no))
                <div class="col-md-6"><strong>Duare Sarkar Reg No:</strong> {{$row->duare_sarkar_registration_no}}</div>
              @endif
              @if(!empty($row->duare_sarkar_date))
                <div class="col-md-6"><strong>Date:</strong> {{date('d/m/Y', strtotime($row->duare_sarkar_date))}}</div>
              @endif
            </div>

            <div class="row mb-2">
              <div class="col-md-12"><strong>Name:</strong> {{$row->ben_fname}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><strong>Mobile:</strong> {{$row->mobile_no}}</div>
              <div class="col-md-6"><strong>Email:</strong> {{$row->email}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><strong>Gender:</strong> {{ ($row->gender == 'Other') ? 'Transgender' : $row->gender }}
              </div>
              @if(!is_null($row->dob))
                <div class="col-md-6"><strong>DOB:</strong> {{date('d/m/Y', strtotime($row->dob))}}</div>
              @endif
              <div class="col-md-12"><strong>Age:</strong> {{$row->ben_age}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-12"><strong>Father Name:</strong> {{$row->father_fname}} {{$row->father_mname}}
                {{$row->father_lname}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-12"><strong>Mother Name:</strong> {{$row->mother_fname}} {{$row->mother_mname}}
                {{$row->mother_lname}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-12"><strong>Spouse Name:</strong> {{$row->spouse_fname}} {{$row->spouse_mname}}
                {{$row->spouse_lname}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><strong>Caste:</strong> {{$row->caste}}</div>
              @if(trim($row->caste) == 'SC' || trim($row->caste) == 'ST')
                <div class="col-md-6"><strong>SC/ST Certificate No:</strong> {{$row->caste_certificate_no}}</div>
              @endif
            </div>
          </div>
        </div>


        <!-- CONTACT DETAILS -->
        <div class="card card-secondary card-outline mt-4">
          <div class="card-header bg-secondary text-white">
            <h3 class="card-title">Contact Details</h3>
          </div>

          <div class="card-body">
            <div class="row mb-2">
              <div class="col-md-6"><strong>State:</strong> West Bengal</div>
              <div class="col-md-6"><strong>District:</strong> {{$row->dist_name}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><strong>Police Station:</strong> {{$row->police_station}}</div>
              <div class="col-md-6"><strong>Block/Municipality:</strong> {{$row->block_ulb_name}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><strong>GP/Ward:</strong> {{$row->gp_ward_name}}</div>
              <div class="col-md-6"><strong>Village/Town/City:</strong> {{$row->village_town_city}}</div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><strong>House No:</strong> {{$row->house_premise_no}}</div>
              <div class="col-md-6"><strong>Post Office:</strong> {{$row->post_office}}</div>
            </div>

            <div class="row">
              <div class="col-md-6"><strong>Pincode:</strong> {{$row->pincode}}</div>
            </div>
          </div>
        </div>


        <!-- BANK DETAILS -->
        <div class="card card-success card-outline mt-4">
          <div class="card-header bg-success text-white">
            <h3 class="card-title">Bank Details</h3>
          </div>

          <div class="card-body">
            <div class="row mb-2">
              <div class="col-md-6"><strong>Bank Name:</strong> {{$row->bank_name}}</div>
              <div class="col-md-6"><strong>Branch Name:</strong> {{$row->branch_name}}</div>
            </div>

            <div class="row">
              <div class="col-md-6"><strong>Account No:</strong> {{$row->bank_code}}</div>
              <div class="col-md-6"><strong>IFSC:</strong> {{$row->bank_ifsc}}</div>
            </div>
          </div>
        </div>


        <!-- ENCLOSURES -->
        <div class="card card-info card-outline mt-4">
          <div class="card-header bg-info text-white">
            <h3 class="card-title">Enclosure List (Self Attested)</h3>
          </div>

          <div class="card-body">
            @if(isset($encloser_list))
              @foreach($encloser_list as $doc_all)
                <div class="mb-3">
                  <label>{{ $doc_all['doc_name'] }}</label>
                  <a class="btn btn-primary btn-sm" href="javascript:void(0);"
                    onclick="View_encolser_modal('{{$doc_all['doc_name']}}', {{$doc_all['id']}}, {{intval($doc_all['is_profile_pic'])}}, {{$row->application_id}})">
                    View
                  </a>
                </div>
              @endforeach
            @endif
          </div>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="card-footer text-center">
        <button class="btn btn-success btn-lg" onclick="printfunction()">Print</button>
      </div>

    </div>


    <!-- ENCLOSURE MODAL -->
    <div class="modal fade" id="encolser_modal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="encolser_name">Document View</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div id="encolser_content" class="modal-body"></div>

        </div>
      </div>
    </div>

  </section>

@endsection
@push('scripts')
  <script>
    var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
    var base_url = '{{ url('/') }}';
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

    function printfunction() {
      // var content=document.getElementById('divToPrint');
      // window.document.write('<html><head><style>.row{ margin-right: 0px!important; margin-left: 0px!important; margin-top: 1%!important;}.section1{border:1.5pxsolid#9187878c;margin:2%;padding:2%;}.color1{margin:0%!important;background-color: #5f9ea061;}.modal_field_name{ float:left;font-weight: 700;margin-right:1%;padding-top:1%;margin-top:1%;}.modal_field_value{margin-right:1%;padding-top:1%;margin-top:1%;}</style></head><body>'+content.innerHTML+'</body></html>');
      window.print();
    }
  </script>
@endpush