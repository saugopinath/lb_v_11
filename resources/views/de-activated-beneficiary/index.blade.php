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
    top:40%;
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
    padding: 5px;
    color: #555;
    font-size: 12px;
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
  .required:after {
      color: red;
      content:'*';
      font-weight: bold;
      margin-left: 5px;
      float:right;
      margin-top: 5px;
  }
  #loadingDivModal{
  position:absolute;
  top:0px;
  right:0px;
  width:100%;
  height:100%;
  background-color:#fff;
  background-image:url('images/ajaxgif.gif');
  background-repeat:no-repeat;
  background-position:center;
  z-index:10000000;
  opacity: 0.4;
  filter: alpha(opacity=40); /* For IE8 and earlier */
}
  .disabledcontent {
    pointer-events: none;
    opacity: 0.4;
  }
</style>

@extends('layouts.app-template-datatable_new')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Reject Approved Beneficiary
    </h1>

  </section>
  <section class="content">
    <div class="box box-default" id="full-content">
      <div class="box-body">
        <div class="panel panel-default">
          <div class="panel-heading"><span id="panel-icon">Enter Beneficiary Details Here</div>
          <div class="panel-body" style="padding: 5px;">
            <div class="row">
              @if ( ($message = Session::get('success')))
              <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>

              </div>
              @endif
              @if(count($errors) > 0)
              <div class="alert alert-danger alert-block">
                <ul>
                  @foreach($errors->all() as $error)
                  <li><strong> {{ $error }}</strong></li>
                  @endforeach
                </ul>
              </div>
              @endif
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group col-md-3">
                  <label for="select_type">Search Using <span class="text-danger">*</span></label>
                  <select class="form-control select2" name="select_type" id="select_type">
                      <option value="">--- Select ---</option>
                       @foreach(Config::get('globalconstants.search_payment_status') as $key=> $search_type)
                          <option value="{{$key}}">{{$search_type}}</option>
                       @endforeach
                  </select>
                </div>
                <div class="form-group col-md-3" id="beneficiary_id_div" style="display: none;">
                  <label for="beneficiary">Beneficiary ID <span class="text-danger">*</span></label>
                  <input type="text" name="ben_id" id="ben_id" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Beneficiary ID">
                </div>
                <div class="form-group col-md-3" id="application_id_div" style="display: none;">
                  <label for="application">Application ID <span class="text-danger">*</span></label>
                  <input type="text" name="app_id" id="app_id" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Application ID">
                </div>
                <div class="form-group col-md-3" id="sasthyasathi_card_div" style="display: none;">
                  <label for="sasthasathi">Sasthasathi Card <span class="text-danger">*</span></label>
                  <input type="text" name="ss_card" id="ss_card" class="form-control" onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Sasthyasathi Card Number">
                </div>
                <div class="form-group col-md-2" style="margin: 23px;">
                  <button class="btn btn-success" id="submit_btn" disabled><i class="fa fa-search"></i> Search</button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="panel panel-default" id="listing_div" style="display: none;">
          <div class="panel-heading" id="panel_head">List of beneficiaries
          </div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;">
            <div id="loadingDiv">
            </div>
            <div class="table-responsive">
              <table id="example" class="display" cellspacing="0" width="100%">
                <thead style="font-size: 12px;">
                  {{-- <th>Serial No</th> --}}
                  <th width="10%">Beneficiary ID</th>
                  <th width="10%">Beneficiary Name</th>
                  <th width="10%">Swasthya Sathi Card No. </th>
                  <th width="10%">Application Id</th>
                  <th width="20%">Address</th>
                  <th width="20%">Banking Information</th>
                  <th width="20%">Action</th>
                </thead>
                <tbody style="font-size: 14px;"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade bd-example-modal-lg ben_view_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Reject Approved Beneficiary</h4>
          </div>
          <div class="modal-body ben_view_body">
            <div class="panel-group singleInfo" role="tablist" aria-multiselectable="true">
              <div class="panel panel-default">
                <div class="panel-heading active" role="tab" id="personal">
                  <div id="loadingDivModal"></div>
                  <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePersonal" aria-expanded="true" aria-controls="collapsePersonal">Personal Details <span class="applicant_id_modal"></span></a> 
                  </h4> 
                </div> 
                <div id="collapsePersonal" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="personal">  
                  <div class="panel-body" style="padding: 5px;">
                    <table class="table table-bordered table-condensed" style="font-size: 14px;">  
                    <tbody>
                      <tr>
                        <th scope="row" width="20%">Swasthya Sathi Card No.</th>
                        <td id='sws_card_txt' width="30%"></td>
                        <th scope="row" width="20%">Mobile No.</th>         
                        <td id="mobile_no" width="30%"></td>
                      </tr>
                      <tr>       
                        <th scope="row" width="20%">Name</th>
                        <td id='ben_fullname' width="30%"></td>
                        <th scope="row" width="20%">Gender</th>         
                        <td id="gender" width="30%"></td>
                      </tr>
                      <tr>
                        <th scope="row" width="20%">DOB</th>         
                        <td id="dob" width="30%"></td> 
                        <th scope="row" width="20%">Age</th>         
                        <td id="ben_age" width="30%"></td>         
                      </tr>
                      <tr>
                        <th scope="row" width="20%">Caste:</th>
                        <td id="caste" width="30%"></td>
                        <th scope="row" class="caste" width="20%">SC/ST Certificate No.</th>
                        <td id="caste_certificate_no" class="caste" width="30%"></td>
                      </tr> 
                    </tbody>
                    </table>
                  </div>
                </div> 
              </div>
            </div>

            <div class="panel-group stopPaymentSection" style="display: none;">  
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFour">   
                  <h4 class="panel-title"> <a>Action</a> </h4> 
                </div> 
                <div id="collapse4" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingFour">  
                  <div class="panel-body" style="padding: 5px;">
                    <div class="form-group col-md-4">
                      <label for="doc_type">Documents for De-activation <span class="text-danger">*</span></label>
                      <select class="form-control" id="doc_type" name="doc_type">
                        <option value="">-- Select --</option>
                      </select>
                    </div> 
                    <div class="form-group col-md-4">
                      <label for="file_stop_payment">Document Upload <span class="text-danger">*</span></label>
                      <input type="file" name="file_stop_payment" class="form-control" id="file_stop_payment">
                      <small class="text-info" style="font-weight: normal;"> (Only jpeg,jpg,png,pdf file and maximum size should be less than 1024 KB)</small>
                      <span class="text-danger" id="error_file" style="font-size: 12px; font-weight: bold;"></span>
                    </div>
                    <div class="form-group col-md-4">
                      <label for="reason">Reason for De-activation <span class="text-danger">*</span></label>
                      <select class="form-control" id="reason" name="reason">
                        <option value="">-- Select --</option>
                      </select>
                    </div> 
                    <div class="form-group col-md-12" style="display: none;" id="remarks_div">
                      <label for="comments">Remarks</label>
                      <input type="text" name="comments" id="comments" class="form-control" maxlength="100" placeholder="Add some remarks(Max 100 character)">
                    </div>
                  </div> 
                </div> 
              </div>  
            </div>

            <form method="POST" action="#" target="_blank" name="fullForm" id="fullForm" style="text-align: center; align-content: center;">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <input type="hidden" id="update_type" name="update_type"/>
              <input type="hidden" id="beneficiary_id" name="beneficiary_id"/>
              <input type="hidden" id="application_id" name="application_id"/>

              <button type="button" class="btn btn-success btn-lg" id="verifyReject">Submit</button>
              <button style="display:none;" type="button" id="submitting" value="Submit" class="btn btn-success btn-lg success" disabled>Processing Please Wait</button>
            </form> 
          </div>
          {{-- <div class="modal-footer">
             
          </div> --}}
        </div>
      </div>
    </div>

  </section>
</div>


@endsection
@section('script')
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
  $(document).ready(function() {   
    $('#loadingDiv').hide();
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #deActivateBeneficiary').addClass("active"); 
    // $('.sidebar-menu #accValTrFailed').addClass("active");
    $('#submit_btn').removeAttr('disabled');
    $('#select_type').change(function(){
      var select_type = $('#select_type').val();
      if (select_type == 'B') {
        $('#beneficiary_id_div').show();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').hide();
        $('#app_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      }
      else if(select_type == 'A') {
        $('#beneficiary_id_div').hide();
        $('#application_id_div').show();
        $('#sasthyasathi_card_div').hide();
        $('#ben_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      }
      else if (select_type == 'S') {
        $('#beneficiary_id_div').hide();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').show();
        $('#ben_id').val('').trigger('change');
        $('#app_id').val('').trigger('change');
      }
      else{
        $('#beneficiary_id_div').hide();
        $('#application_id_div').hide();
        $('#sasthyasathi_card_div').hide();
        $('#ben_id').val('').trigger('change');
        $('#app_id').val('').trigger('change');
        $('#ss_card').val('').trigger('change');
      }
    });

    var ajaxData = '';
    $('#submit_btn').click(function(){
      var select_type = $('#select_type').val();
      var beneficiary_id = $('#ben_id').val();
      var application_id = $('#app_id').val();
      var ss_card_no = $('#ss_card').val();
      if (select_type == 'B') {
        if (beneficiary_id == '') {
          $.alert({
            title: 'Alert!!',
            content: 'Please Enter Beneficiary Id'
          });
        }
        else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token:"{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      }
      else if(select_type == 'A') {
        if (application_id == '') {
          $.alert({
            title: 'Alert!!',
            content: 'Please Enter Application Id'
          });
        }
        else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token:"{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      }
      else if (select_type == 'S') {
        if (ss_card_no == '') {
          $.alert({
            title: 'Alert!!',
            content: 'Please Enter Sasthyasathi Card Number'
          });
        }
        else {
          var ajaxData = {
            'beneficiary_id': beneficiary_id,
            'application_id': application_id,
            'ss_card_no': ss_card_no,
            _token:"{{csrf_token()}}"
          }
          loadDataTable(ajaxData);
        }
      }
      else{
        $.alert({
          title: 'Alert!!',
          content: 'All fields are required'
        });
      }
    });

    $('#file_stop_payment').change(function(){
      var card_file=document.getElementById("file_stop_payment");
      if(card_file.value!="")
      {
        var attachment;
        attachment = card_file.files[0];
        // console.log(attachment.type)
        var type = attachment.type;
        if(attachment.size>1048576)
        {
          document.getElementById("error_file").innerHTML="<i class='fa fa-warning'></i> Unaccepted document file size. Max size 1024 KB. Please try again";
          $('#file_stop_payment').val('');
          return false;
        }
        else if (type != 'image/jpeg' && type != 'image/png' && type != 'application/pdf') {
          document.getElementById("error_file").innerHTML="<i class='fa fa-warning'></i> Unaccepted document file format. Only jpeg,jpg,png and pdf. Please try again";
          $('#file_stop_payment').val('');
          return false;
        }
        else{
          $('#file_upload_btn').show();
          document.getElementById("error_file").innerHTML="";
        }
      }
    });
    $('#reason').change(function(){
      var reason = $('#reason').val();
      if (reason == 3) {
        $('#comments').val('');
        $('#remarks_div').show();
      } else {
        $('#remarks_div').hide();        
      }
    });
    // -------------------- Final Approve Section-------------------------- //
    $(document).on('click', '#verifyReject', function() {   
      var update_type = $('#update_type').val();
      var comments = $('#comments').val();
      var doc_type = $('#doc_type').val();
      var reason = $('#reason').val();
      var beneficiary_id = $('#fullForm #beneficiary_id').val();
      var application_id = $('#fullForm #application_id').val();
      var full_name = $('#ben_fullname').text();
      var file_sp = document.getElementById("file_stop_payment");
      var file_attachment = file_sp.files[0];
      var valid=0;
      var final_datas = '';
      if(update_type == 'SP'){
        var valid=0;
        if(file_sp.value!='' && doc_type != '' && reason != ''){
          var valid=1;
          var formData = new FormData();
          var files = $('#file_stop_payment')[0].files;
          formData.append('file_stop_payment', files[0]);
          formData.append('update_type', update_type);
          formData.append('comments', comments);
          formData.append('doc_type', doc_type);
          formData.append('reason', reason);
          formData.append('application_id', application_id);
          formData.append('beneficiary_id', beneficiary_id);
          formData.append('_token', '{{ csrf_token() }}');
        }
        else{
          $.alert({
            title: 'Error!!',
            type: 'red',
            icon: 'fa fa-warning',
            content: '<strong>All (*) fields is required</strong>',
          });
          return false;
        }
      }
      if(valid == 1 && formData != ''){
        $.confirm({
          title: 'Confirmation',
          type: 'orange',
          icon: 'fa fa-warning',
          content: 'Are you sure want to de-activate this beneficiary ?<br><strong> Name - '+full_name+'<br>Beneficiary ID - '+beneficiary_id+'</strong>',
          buttons: {
            Ok: function(){
              $("#submitting").show();
              $("#verifyReject").hide();
              $('#loadingDivModal').show();
              $.ajax({
                type: 'POST',
                url: "{{ url('updateStopPaymentFinal') }}",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                  if(data.return_status){
                    $('.ben_view_modal').modal('hide');
                    $.confirm({
                      title: 'Success',
                      type: 'green',
                      icon: 'fa fa-check',
                      content: data.return_msg,
                      buttons: {
                        Ok: function(){
                          $("#submitting").hide();
                          $("#verifyReject").show();
                          $('#loadingDivModal').hide();
                          $('#listing_div').hide();
                          $('#select_type').val('').trigger('change');
                          $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                      }
                    });
                  }
                  else{
                    $("#submitting").hide();
                    $("#verifyReject").show();
                    $('#loadingDivModal').hide();
                    $('.ben_view_modal').modal('hide');
                    $.alert({
                      title: 'Error',
                      type: 'red',
                      icon: 'fa fa-warning',
                      content: data.return_msg
                    });
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  $.confirm({
                    title: 'Error',
                    type: 'red',
                    icon: 'fa fa-warning',
                    content: 'Something went wrong in the de-activation process !!',
                    buttons: {
                      Ok: function(){
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
      }
      else {
        $.alert({
          title: 'Error',
          type: 'red',
          icon: 'fa fa-warning',
          content: 'Something went wrong !!'
        });
      }
    });
    // -------------------- Final Approve Section --------------------------//

  });

  function loadDataTable(ajaxData) {
    $('#loadingDiv').show();
    $('#listing_div').show();
    $('#submit_btn').attr('disabled',true);

    if ( $.fn.DataTable.isDataTable('#example') ) {
      $('#example').DataTable().destroy();
    }
    var dataTable = $('#example').DataTable( {
      dom: 'Blfrtip',
      "scrollX": true,
      "paging": false,
      "searchable": false,
      "ordering":false,
      "bFilter": false,
      "bInfo": false,
      "pageLength":20,
      'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
      "serverSide": true,
      "processing":true,
      "bRetrieve": true,
      "oLanguage": {
        // "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
      },
      "ajax": 
      {
        url: "{{ url('get-linelisting-deactive') }}",
        type: "post",
        data: ajaxData,
        error: function (jqXHR, textStatus, errorThrown) {
          $('#loadingDiv').hide();
          $('#submit_btn').removeAttr('disabled');
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      },
      "initComplete":function(){
        $('#loadingDiv').hide();
        $('#submit_btn').removeAttr('disabled');
        //console.log('Data rendered successfully');
      },
      "columns": [
        // { "data": "DT_RowIndex" },
        { "data": "beneficiary_id" },
        { "data": "name" },
        { "data": "ss_card_no" },
        { "data": "application_id" },
        { "data": "address" },
        { "data": "bank_info" },
        { "data": "action" },
      ],

      "buttons": [
        // 'pdf','excel'
      ],
    });
  }
  function editFunction(beneficiary_id) {
    var select_item = $('#select_item_update_'+beneficiary_id).val();
    if (select_item == '') {
      $.alert({
        title: 'Alert!!',
        type: 'red',
        icon: 'fa fa-warning',
        content: 'Please select option which one do you want to edit'
      });
    }
    else if (select_item == 'SP') {
      $('#loadingDiv').show();
      $('#loadingDivModal').show();
      $('#fullForm #beneficiary_id').val(beneficiary_id);
      $('#fullForm #update_type').val(select_item);
      $(".singleInfo").show();
      $(".stopPaymentSection").show();
      $('.applicant_id_modal').html('');
      $('#collapsePersonal').collapse('show');
      $.ajax({
        type: 'post',
        url: "{{route('getBeneficiaryPersonalData')}}",
        data: {_token:'{{csrf_token()}}', benid:beneficiary_id},
        dataType: 'json',
        success: function (response) {
          //  console.log(JSON.stringify(response));
          $('#loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('#collapsePersonal').collapse('show');
          $('#doc_type').val('');
          $('#reason').val('');
          $('#file_stop_payment').val('');
          $('#error_file').html('');
          $('#sws_card_txt').text(response.personaldata[0].ss_card_no);
          var mname=response.personaldata[0].ben_mname;
          if (!(mname)) { var mname='' }
          var lname=response.personaldata[0].ben_lname;
          if (!(lname)) { var lname='' }
          $('#ben_fullname').text(response.personaldata[0].ben_fname+' '+mname+' '+lname);
          $('#mobile_no').text(response.personaldata[0].mobile_no);
          $('#gender').text(response.personaldata[0].gender);
          $('#dob').text(response.personaldata[0].dob);
          $('#ben_age').text(response.personaldata[0].age_ason_01012021);
          $('#caste').text(response.personaldata[0].caste);
          if(response.personaldata[0].caste=='SC' || response.personaldata[0].caste=='ST'){
            $('#caste_certificate_no').text(response.personaldata[0].caste_certificate_no);
            $('.caste').show();
          }
          else{
            $('.caste').hide();
          }

          $('.applicant_id_modal').html('(Beneficiary ID - '+response.personaldata[0].beneficiary_id+' , Application ID - '+response.personaldata[0].application_id+')');
          $('#fullForm #application_id').val(response.personaldata[0].application_id);
          
          for (var  i = 0; i < response.attach_doc.length; i++) {
            $('#doc_type').append($('<option>', {
              value: response.attach_doc[i].id,
              text: response.attach_doc[i].doc_name
            }),'</option>');
          }
          $('#reason').html('<option value="">-- Select --</option>');
          @foreach(Config::get('globalconstants.de_activation_reason') as $key=>$val)  
            $('#reason').append('<option value="{{ $key}}">{{$val}}</option>');
          @endforeach
          $('.ben_view_modal').modal('show');
        },
        complete: function(){
          
        },
        error: function (jqXHR, textStatus, errorThrown) {
          $('#loadingDivModal').hide();
          $('#loadingDiv').hide();
          $('.ben_view_modal').modal('hide');
          // ajax_error(jqXHR, textStatus, errorThrown);
          $.alert({
            title: 'Error!!',
            type: 'red',
            icon: 'fa fa-warning',
            content: 'Something wrong while fetching the beneficiary data!!',
          });
        }
      });
      // $('.ben_view_modal').modal('show');
    }
    else {
      $.alert({
        title: 'Alert!!',
        type: 'red',
        icon: 'fa fa-warning',
        content: 'Something went wrong!!'
      });
    }
  }
</script>
@stop