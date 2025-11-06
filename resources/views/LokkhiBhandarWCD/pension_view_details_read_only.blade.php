@extends('employees-mgmt.base_pension')

@section('action-content')
<link href="{{ asset("/bower_components/AdminLTE/dist/new_css/lightbox.min.css")}}" rel="stylesheet" type="text/css" />
<style>
  * {
    font-size: 15px;
  }

  .field-name {
    float: left;
    font-weight: 600;
    font-size: 17px;
    margin-right: 3%;
    padding-top: 1%;
  }

  .field-value {


    font-size: 17px;
    padding-top: 1%;

  }

  .row {
    margin-right: 0px !important;
    margin-left: 0px !important;
  }

  .section1 {
    border: 1.5px solid #9187878c;
    overflow: hidden;
    padding-bottom: 10px;


  }

  .color1 {

    background-color: #dcdfdf;
  }

  .color1 h3 {
    margin: 10px 0px 10px 0px !important;
  }

  .setPos {
    padding: 0px 0px 10px 0px;
    margin: 10px 0px 10px 0px;
    border: 1px solid #dcdfdf;
    overflow: hidden;
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

  .modal-header {
    background-color: #7fffd4;
  }

  @media print {
    .example-screen {
      display: none;
    }

    * {
      font-size: 15px;
    }

    .field-name {
      float: left;
      font-weight: 600;
      font-size: 17px;
      margin-right: 3%;
      padding-top: 1%;
    }

    .field-value {


      font-size: 17px;
      padding-top: 1%;

    }

    .row {
      margin-right: 0px !important;
      margin-left: 0px !important;
    }

    .section1 {
      border: 1.5px solid #9187878c;
      overflow: hidden;
      padding-bottom: 10px;


    }

    .color1 {

      background-color: #dcdfdf;

    }

    .color1 h3 {
      margin: 10px 0px 10px 0px !important;
    }

    .setPos {
      padding: 0px 0px 10px 0px;
      margin: 10px 0px 10px 0px;
      border: 1px solid #dcdfdf;
      overflow: hidden;
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

    .modal-header {
      background-color: #7fffd4;
    }

    /*.row{
  margin-right: 0px!important;
  margin-left: 0px!important;
}
.section1{
    border: 1.5px solid #9187878c!important;
    margin: 0.25cm!important;
    padding: 0.25cm!important;
    page-break-inside : avoid;
}
.color1{
  margin: 0%!important;
  background-color: #5f9ea061!important;
  -webkit-print-color-adjust: exact; 
}
.modal_field_name{
  float:left!important;
  font-weight: 700!important;
  margin-right:0.5cm!important;

}

.modal_field_value{
  padding-top:0.30cm!important;

}
.color1{
  margin: 0%!important;
  background-color: #7fffd4!important;
 -webkit-print-color-adjust: exact; 
}

.modal-header{
  background-color: #7fffd4!important;
 -webkit-print-color-adjust: exact; 
}
#divToPrint{
}*/
  }
</style>
<section class="content" id="divToPrint">
  <div class="modal-fade" tabindex="-1" role="document">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="example-screen">

          <!--  <button type="button" class="close" data-dismiss="modal" aria-label="Close"> -->
          <!-- <span aria-hidden="true">&times;</span> -->
          <!-- </button> -->
          <h2 class="modal-title " style="text-align: center;">@if($designation_id=='Operator')<a href="{{ url('lb-application-list') }}">
              <img width="50px;" style="pull-left" src="{{ asset("images/back.png") }}" alt="Back" /></a>@endif
            View Application Form</h2>

        </div>
        <div class="modal-body">
          <!--   <h4 class="example-screen" style="text-align: center;">Please Verify or Reject Employee's application with Comments</h4> -->


          <!-- We display the details entered by the user here -->
          <div class="section1">
            <div class="row">
              <div class="col-md-12">
                <h3 style="text-align: center; color:red;">@if($is_draft==1) Application ID:{{$row->application_id}}@else Beneficiary ID:{{$ben_id}}@endif

                </h3>

              </div>


            </div>
            @if($is_reject==1)
             <div class="row">
              <div class="col-md-12">
                <h3 style="text-align: center; color:blue;">Reject for the Reason:{{$row->rejected_cause}}

                </h3>

              </div>


            </div>
          <div class="row">
              <div class="col-md-12">
                <h3 style="text-align: center; color:pink;">Remark:{{$row->comments}}

                </h3>

              </div>


            </div>
            @endif
                @if($row->next_level_role_id==-50)
             <div class="row">
              <div class="col-md-12">
                <h3 style="text-align: center; color:blue;">Reverted for the Reason:{{$row->rejected_cause}}

                </h3>

              </div>


            </div>
            @if(!empty($row->comments))
             <div class="row">
              <div class="col-md-12">
                <h3 style="text-align: center; color:pink;">Remark:{{$row->comments}}

                </h3>

              </div>


            </div>
            @endif
            @endif
            <div class="row color1">
              <div class="col-md-12">
                <h3>Personal Details</h3>
              </div>
            </div>

            <div class="row">
              @if(!empty($row->sws_card_no))
              <div class="col-md-6">
                <div><strong>Swasthyasathi Card No:</strong> {{$row->sws_card_no}}</div>

              </div>
              @endif
              <div class="col-md-6">
                <div><strong>Aadhaar No:</strong> {{$row->aadhar_no}}</div>
              </div>
            </div>
             <div class="row">
             @if(!empty($row->duare_sarkar_registration_no))
              <div class="col-md-6">
                <div><strong>Duare Sarkar Registration no:</strong> {{$row->duare_sarkar_registration_no}}</div>

              </div>
              @endif
              @if(!empty($row->duare_sarkar_date))
              <div class="col-md-6">
                <div><strong>Duare Sarkar Date:</strong>  {{date('d/m/Y', strtotime($row->duare_sarkar_date)) }}</div>
              </div>
              @endif
            </div>
           <div class="row">
              <div class="col-md-12">
                <div><strong>Name :</strong> {{$row->ben_fname}} </div>
              </div>
            <div class="row">
              
              <div class="col-md-6">
                <div><strong>Mobile Number:</strong>{{$row->mobile_no}}</div>

              </div>
              <div class="col-md-6">
                <div><strong>Email Id:</strong> {{$row->email}}


                </div>

              </div>
            </div>
           

              <div class="row">



                <div class="col-md-6">
                  <div><strong>Gender:</strong> {{ ($row->gender=='Other') ? "Transgender" : $row->gender }}</div>

                </div>
                @if(!is_null($row->dob))
                <div class="col-md-6">
                  <div><strong>Date of Birth (DD/MM/YYYY):</strong> {{date('d/m/Y', strtotime($row->dob)) }}</div>

                </div>
                @endif

                <div class="col-md-12">
                  <div><strong>Age :</strong> {{$row->ben_age}}</div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div><strong>Father's Name :</strong> {{$row->father_fname}} {{$row->father_mname}} {{$row->father_lname}}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div><strong>Mother's Name :</strong> {{$row->mother_fname}} {{$row->mother_mname}} {{$row->mother_lname}}</div>
                </div>
              </div>
               <div class="row">
               <div class="col-md-12">
                  <div><strong>Spouse Name :</strong> {{$row->spouse_fname}} {{$row->spouse_mname}} {{$row->spouse_lname}}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div><strong>Caste:</strong> {{$row->caste}}</div>
                </div>
                @if(trim($row->caste=='SC') || trim($row->caste=='ST'))
                 <div class="col-md-6">
                  <div><strong>SC/ST Certificate No:</strong> {{$row->caste_certificate_no}}</div>
                 </div>
                 @endif
                
              </div>
             

              </div>
















              <div class="row ">
                <div class="col-md-12 color1" style="margin:10px 0px">
                  <h3>Contact Details</h3>
                </div>
              </div>

              <div class="col-md-6">
                <div><strong>State:</strong> West Bengal</div>

              </div>
              <div class="col-md-6">
                <div><strong>District:</strong> {{$row->dist_name}}</div>

              </div>

              <div class="col-md-6">
                <div><strong>Police Station:</strong>{{$row->police_station}}</div>

              </div>
              <div class="col-md-6">
                <div><strong>Block/Municipality/Corp:</strong>{{$row->block_ulb_name}}</div>

              </div>

              <div class="col-md-6">
                <div><strong>GP/Ward No.:</strong>{{$row->gp_ward_name}}</div>

              </div>

              <div class="col-md-6">
                <div><strong>Village/Town/City:</strong> {{$row->village_town_city}}</div>

              </div>



              <div class="col-md-6">
                <div><strong>House / Premise No:</strong> {{$row->house_premise_no}}</div>

              </div>

              <div class="col-md-6">
                <div><strong>Post Office:</strong> {{$row->post_office}}</div>

              </div>

              <div class="col-md-6">
                <div><strong>Pin Code:</strong> {{$row->pincode}}</div>

              </div>





              



              <div class="row ">
                <div class="col-md-12 color1" style="margin:10px 0px">
                  <h3>Bank Details</h3>
                </div>
              </div>

              <div class="col-md-6">
                <div><strong>Bank Name:</strong> {{$row->bank_name}}</div>

              </div>




              <div class="col-md-6">
                <div><strong>Bank Branch Name:</strong> {{$row->branch_name}}</div>

              </div>


              <div class="col-md-6">
                <div><strong>Bank Account No.:</strong> {{$row->bank_code}}</div>

              </div>

              <div class="col-md-6">
                <div><strong>IFS Code:</strong>{{$row->bank_ifsc}}</div>

              </div>

            </div>

            <div class="row color1">
              <div class="col-md-12">
                <h3>Enclosure List(Self Attested)</h3>
              </div>
            </div>
            @if(isset($encloser_list))
            @foreach ($encloser_list as $doc_all)
            <div class="form-group col-md-12">
              <label class="">{{ $doc_all['doc_name'] }}</label>


              <a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="View_encolser_modal('{{$doc_all['doc_name']}}',{{$doc_all['id']}},{{intval($doc_all['is_profile_pic'])}},{{$row->application_id}})" class="btn-info" >View</a>

            </div>

            @endforeach
            @endif
            <div class="row">



            </div>







          </div>


        </div>

        <div class="row">

          <div class="text-center example-screen" style="margin-top: 10px;"><button style="width:25%;" class="btn btn-success btn-lg" onclick="printfunction()">Print</button></div><br />

        </div>











      </div>


    </div>




  </div>


  </div>


  </div>
  <div class="modal" id="encolser_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="encolser_name">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="encolser_content">  </div>
     
           
    </div>
  </div>
</div>
</section>
@endsection
<script>
var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
var base_url='{{ url('/') }}';
function View_encolser_modal(doc_name,doc_type,is_profile_pic,application_id){
$('#encolser_name').html('');
$('#encolser_content').html('');
$('#encolser_name').html(doc_name+'('+application_id+')');
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
            }).done(function( data, textStatus, jqXHR ) {
            $('#encolser_content').html('');
            $('#encolser_content').html(data);
            $("#encolser_modal").modal();
        }).fail(function( jqXHR, textStatus, errorThrown ) {
          $('#encolser_content').html('');
            alert(sessiontimeoutmessage);
           window.location.href=base_url;
        });
}

  function printfunction() {
    // var content=document.getElementById('divToPrint');
    // window.document.write('<html><head><style>.row{ margin-right: 0px!important; margin-left: 0px!important; margin-top: 1%!important;}.section1{border:1.5pxsolid#9187878c;margin:2%;padding:2%;}.color1{margin:0%!important;background-color: #5f9ea061;}.modal_field_name{ float:left;font-weight: 700;margin-right:1%;padding-top:1%;margin-top:1%;}.modal_field_value{margin-right:1%;padding-top:1%;margin-top:1%;}</style></head><body>'+content.innerHTML+'</body></html>');
    window.print();
  }
</script>