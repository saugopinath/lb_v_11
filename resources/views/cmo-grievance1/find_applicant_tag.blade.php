<style type="text/css">
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

  .loadingDivModal {
    position: absolute;
    top: 0px;
    right: 0px;
    width: 100%;
    height: 100%;
    background-color: #fff;
    background-image: url('images/ajaxgif.gif');
    background-repeat: no-repeat;
    background-position: center;
    z-index: 10000000;
    opacity: 0.4;
    filter: alpha(opacity=40);
    /* For IE8 and earlier */
  }

  #updateDiv {
    border: 1px solid #d9d9d9;
    padding: 8px;
    box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
  }

 .btnJb {
  margin: 0 10px;
}

.btn-group-wrapper {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 10px;
}

</style>
@extends('layouts.app-template-datatable_new')
@section('content')
  <div class="content-wrapper">
    <section class="content-header">
    <h1>
      CMO Grievance ATR Details
    </h1>
    <ol class="breadcrumb">
      <i class="fa fa-clock-o"></i> Date : <span style="font-size: 12px; font-weight: bold;"><span
        class='date-part'></span>&nbsp;&nbsp;<span class='time-part'></span></span>
    </ol>
    </section>
    <section class="content">
    <div class="box box-default">
      <div class="box-body">
      @if (($message = Session::get('success')))
      <div class="alert alert-success alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>
      <strong>{{ $message }} </strong>
      </div>
    @endif
      @if (($message = Session::get('message')))
      <div class="alert alert-danger alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>
      <strong>{{ $message }}</strong>
      </div>
    @endif
      @if (($message = Session::get('msg1')))
      <div class="alert alert-danger alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>
      <strong>{{ $message }}</strong>
      </div>
    @endif
      @if($mapLevel == 'Department')
      <a href="{{ url('cmo-grievance-hod1') }}">
      <img width="50px;" style="pull-left" src="{{ asset("images/back.png") }}" alt="Back" /></a>
    @endif
      @if($mapLevel == 'DistrictApprover')
      <a href="{{ url('cmo-grievance-workflow1') }}">
      <img width="50px;" style="pull-left" src="{{ asset("images/back.png") }}" alt="Back" /></a>
    @endif
      <div id="loadingDiv"></div>
      <div class="panel panel-default">
        <div class="panel-heading" style="font-size: 15px; font-weight: bold; font-style: italic; padding: 5px 15px;">
        <span id="panel-icon">Grievance Details
        </div>
        <div class="panel-body" style="padding: 5px;">
        <div class="row">
          <div class="col-md-12">

          <div class="row">
            <div class="col-md-12" style="margin-bottom: 10px;">
            <div class="col-md-3">
              <strong>Grievance ID : </strong>
              <span style="font-size: 14px;">{{$grievance_id}}</span>
            </div>
            <div class="col-md-3">
              <strong>Grievance No : </strong>
              <span style="font-size: 14px;">{{$atr_details->grievance_no}}</span>
            </div>
            <div class="col-md-3">
              <strong>Caller Name : </strong>
              <span style="font-size: 14px;">{{$atr_details->applicant_name}}</span>
            </div>


            </div>
            <div class="col-md-12" style="margin-bottom: 10px;">
            <div class="col-md-3">
              <strong>Caller Mobile No. : </strong>
              <span style="font-size: 14px;">{{$atr_details->pri_cont_no}}</span>
            </div>
            <div class="col-md-3">
              <strong>Age : </strong>
              <span style="font-size: 14px;">{{$atr_details->applicant_age}} years</span>
            </div>
            </div>
            <div class="col-md-12" style="margin-bottom: 10px;">
            <div class="col-md-12">
              <strong>Address : </strong>
              <span style="font-size: 14px;">{{$atr_details->applicant_address}}</span>
            </div>
            </div>
            <div class="col-md-12" style="margin-bottom: 10px;">
            <div class="col-md-12">
              <strong>Description : </strong>
              <span style="font-size: 14px;">{{$atr_details->grievance_description}}</span>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading" style="font-size: 15px; font-weight: bold; font-style: italic; padding: 5px 15px;">
        <span id="panel-icon">ATR Type Details
        </div>
        <div class="panel-body" style="padding: 5px;">
        <div class="row">
          <div class="col-md-12">

          <div class="row">


            <div class="col-md-12" style="margin-bottom: 10px;">
            <div class="col-md-12">
              <strong>ATR Type : </strong>
              <span style="font-size: 14px;">{{$atr_details->atr_desc}}</span>
            </div>
            <div class="col-md-12">
              <strong>Remarks : </strong>
              <span style="font-size: 14px;">{{trim($atr_details->remarks)}}</span>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      @if(!is_null($atr_details->lb_application_id))
      <div class="panel panel-default">
      <div class="panel-heading" style="font-size: 15px; font-weight: bold; font-style: italic; padding: 5px 15px;">
      <span id="panel-icon">Applicant Details
      </div>
      <div class="panel-body" style="padding: 5px;">


      <div class="col-md-12" style="margin-bottom: 10px;">
        <div class="col-md-3">
        <strong>Application Id : </strong>
        <span style="font-size: 14px;">{{$ben_tag_details->application_id}}</span>
        </div>
        <div class="col-md-3">
        <strong>Name : </strong>
        <span style="font-size: 14px;">{{trim($ben_tag_details->ben_fname)}}</span>
        </div>
        <div class="col-md-3">
        <strong>Mobile Number : </strong>
        <span style="font-size: 14px;">{{$ben_tag_details->mobile_no}}</span>
        </div>


      </div>
      <div class="col-md-12" style="margin-bottom: 10px;">
        <div class="col-md-3">
        <strong>Date of Birth : </strong>
        <span style="font-size: 14px;">{{date('d/m/Y', strtotime($ben_tag_details->dob)) }}</span>
        </div>
        <div class="col-md-3">
        <strong>Father's Name : </strong>
        <span style="font-size: 14px;">{{trim($ben_tag_details->father_fname)}}
        {{trim($ben_tag_details->father_mname)}} {{trim($ben_tag_details->father_lname)}}</span>
        </div>
      </div>

      <div class="col-md-12" style="margin-bottom: 10px;">

        <div class="col-md-3">
        <strong>Block/Municipality/Corp : </strong>
        <span style="font-size: 14px;">{{trim($ben_contact_details->block_ulb_name)}}</span>
        </div>
        <div class="col-md-3">
        <strong>GP/Ward No : </strong>
        <span style="font-size: 14px;">{{trim($ben_contact_details->gp_ward_name)}}</span>
        </div>
      </div>


      <div class="col-md-12" style="margin-bottom: 10px;">
        <div class="col-md-3">
        <strong>Bank Name : </strong>
        <span style="font-size: 14px;">{{trim($ben_bank_details->bank_name)}}</span>
        </div>
        <div class="col-md-3">
        <strong>Bank Branch Name : </strong>
        <span style="font-size: 14px;">{{trim($ben_bank_details->branch_name)}}</span>
        </div>
        <div class="col-md-3">
        <strong>Bank Account No : </strong>
        <span style="font-size: 14px;">{{trim($ben_bank_details->bank_code)}}</span>
        </div>
        <div class="col-md-3">
        <strong>IFS Code : </strong>
        <span style="font-size: 14px;">{{trim($ben_bank_details->bank_ifsc)}}</span>
        </div>

      </div>


      </div>
      </div>
    @endif

      </div>


      @if($mapLevel == 'DistrictApprover')
 <div class="row text-center">
  <div class="col-md-6 col-md-offset-3 mx-auto">
    <div class="btn-group-wrapper">
      <!-- Approve Form -->
      <form method="post" action="{{ route('cmo_grivance_approve1') }}" id="grivance_approval_form" class="btnJb">
        {{ csrf_field() }}
        <input type="hidden" name="grivance_id" value="{{ $grievance_id }}">
        <button type="button" class="btn btn-success" id="confirm_yes">Approve</button>
        <button style="display:none;" type="button" id="submittingapprove" class="btn btn-info btn-lg" disabled>
          Submitting please wait
        </button>
      </form>

      <!-- Revert Form -->
      <form method="post" action="{{ route('cmo_grivance_revert1') }}" id="grivance_approval_form_revert" class="btnJb">
        {{ csrf_field() }}
        <input type="hidden" name="grivance_id" value="{{ $grievance_id }}">
        <button type="button" class="btn btn-danger" id="confirm_revert">Revert</button>
        <button style="display:none;" type="button" id="submittingapprove_revert" class="btn btn-info btn-lg" disabled>
          Submitting please wait
        </button>
      </form>
    </div>
  </div>
</div>




    @elseif($mapLevel == 'Department')
      <div class="row">
      <center>
      <button type="button" class="btn btn-success" id="verifyReject">Push To CMO</button>
      <button style="display:none;" type="button" id="submittingapprove" class="btn btn-info btn-lg" disabled>
      Submitting please wait
      </button>
      </center>
    @endif

      </div>

    </div>
    </section>
  </div>
@endsection
<script src="{{ asset("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script>
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
  $(document).ready(function () {
    var interval = setInterval(function () {
      var momentNow = moment();
      $('.date-part').html(momentNow.format('DD-MMMM-YYYY'));
      $('.time-part').html(momentNow.format('hh:mm:ss A'));
    }, 100);

    // $('#search_level').hide();
    $('#loadingDiv').hide();
    $('#submit_btn').removeAttr('disabled');
    $('#res_div').hide();
    $('#search_panel').hide();

    $('#redress').hide();
    $('#send_another_block').hide();
    $('#district_div').hide();
    $('#urban_code_div').hide();
    $('#block_div').hide();
    $('#send_to_operator').show();
    // if(applicant_mobile_no){
    //     $('input[name="process_type"][value="2"]').attr('checked', true);
    //     $('#input_value').val(applicant_mobile_no);
    //     $("#new_process_id").val(2);
    //     var label_text = 'Mobile Number :';
    //     $("#input_label").text(label_text);
    //     performSearch();
    // }

    var error_input_value = '';


    $('#confirm_yes').on('click', function () {
      var y_no = confirm('Are You Sure?');
      if (y_no) {
        $("#confirm_yes").hide();
        $("#submittingapprove").show();
        $("#grivance_approval_form").submit();
      }
    });

    $('#confirm_revert').on('click', function () {
      var y_no = confirm('Are You Sure. You want to Revert?');
      if (y_no) {
        $("#confirm_revert").hide();
        $("#submittingapprove_revert").show();
        $("#grivance_approval_form_revert").submit();
      }
    });

    $(document).on('click', '#verifyReject', function () {
      var is_bulk = $('#is_bulk').val();
      var applicantId = $('#applicantId').val();
      var grivance_id = $('#grivance_id').val();
      var valid = 1;
      // console.log(is_bulk, applicantId, grivance_id);
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

              $.ajax({
                type: 'POST',
                url: "{{ url('cmo-grievance-hod-post1') }}",
                data: {
                  is_bulk: 0,
                  applicantId: {{ $grievance_id }},
                  grivance_id: {{ $grievance_id }},
                  _token: '{{ csrf_token() }}',
                },
                success: function (data) {
                  // console.log(data);
                  //   console.log(JSON.stringify(data));
                  // dataTable.ajax.reload();
                  var table_renew = $('#example').DataTable();
                  table_renew.ajax.reload(null, false);
                  //$('#example').DataTable().ajax.reload()
                  if (data.status == 1) {
                    $('#approve_rejdiv').hide();
                    $.confirm({
                      title: 'Success',
                      type: 'green',
                      icon: 'fa fa-check',
                      content: data.msg,
                      buttons: {
                        Ok: function () {
                          window.location.href = 'cmo-grievance-hod1';
                          $("#submitting").hide();
                          $("#verifyReject").show();
                          $("html, body").animate({ scrollTop: 0 }, "slow");
                        }
                      }
                    });
                  }
                  else {
                    $("#submitting").hide();
                    $("#verifyReject").show();
                    $('.ben_view_modal').modal('hide');
                    $('#approve_rejdiv').hide();
                    $.alert({
                      title: 'Error',
                      type: 'red',
                      icon: 'fa fa-warning',
                      content: data.msg
                    });
                  }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                  $.confirm({
                    title: 'Error',
                    type: 'red',
                    icon: 'fa fa-warning',
                    content: 'Something went wrong in the approval!!',
                    buttons: {
                      Ok: function () {
                        // $("#verifyReject").show();
                        //  $("#submitting").hide();
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
    });


  });

</script>