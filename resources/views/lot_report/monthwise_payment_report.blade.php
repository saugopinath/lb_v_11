<style type="text/css">
  .preloader1 {
    position: fixed;
    top: 40%;
    left: 52%;
    z-index: 999;
  }

  .preloader1 {
    background: transparent !important;
  }

  .disabledcontent {
    pointer-events: none;
    opacity: 0.4;
  }

  .has-error {
    border-color: #cc0000;
    background-color: #ffff99;
  }

  .modal {
    text-align: center;
    padding: 0 !important;
  }

  .modal:before {
    content: '';
    display: inline-block;
    height: 100%;
    vertical-align: middle;
    margin-right: -4px;
  }

  .modal-dialog {
    display: inline-block;
    text-align: left;
    vertical-align: middle;
  }

  label.required:after {
    color: red;
    content: '*';
    font-weight: bold;
    margin-left: 5px;
    float: right;
    margin-top: 5px;
  }
  .filterDiv {
    border: 1px solid #d9d9d9;
    border-left: 3px solid deepskyblue;
    margin-bottom: 10px;
    padding: 8px;
    box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    /*max-width: 50%;*/
    /*margin: 0 auto;*/
  }
  .resultDiv {
    border: 1px solid #d9d9d9;
    border-left: 3px solid seagreen;
    /*margin-bottom: 10px; */
    padding: 8px;
    box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    /*max-width: 50%;*/
    /*margin: 0 auto;*/
  }
</style>

@extends('layouts.app-template-datatable')
@section('content')

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
     Monthwise Payment Report
    </h1>
    {{-- <ol class="breadcrumb">
      <span style="font-size: 12px; font-weight: bold;"><i class="fa fa-clock-o"> Date : </i><span
          class='date-part'></span>&nbsp;&nbsp;<span class='time-part'></span></span>
    </ol> --}}
  </section>
  <section class="content">
    <div class="box box-default">
      <div class="box-body">
        <div id="loadingDiv">
        </div>

        <!-- <div class="panel panel-default">
          <div class="panel-heading">Search By District, Year and Month</div>
          <div class="panel-body" style="padding: 5px;"> -->
          <div class="filterDiv">
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
                <div class="col-md-2"  >
                  <label class="control-label">Financial Year <span class="text-danger">*</span></label>
                  <select class="form-control" name="lot_year" id='lot_year' >
                  {{-- <option value="">--Select Year--</option> --}}
                  @foreach(Config::get('constants.fin_year') as $year)
                  <option value="{{ $year}}">{{$year}}</option>
                  @endforeach
                  </select>
                </div>
                {{-- <div class="col-md-2">
                  <label class="control-label">Select Phase <span class="text-danger"></span></label>
                  <select class="form-control" name="phase_code" id='phase_code' required >
                    <option value="">--Select Phase--</option>
                    @foreach(Config::get('constants.duare_sarkar_phase') as $keyphase=>$phase)
                    <option value="{{$keyphase}}" >{{$phase}}</option>
                    @endforeach
                  </select>
                </div> --}}
                <div class="col-md-2" style="margin-top: 23px;">
                  <label class=" control-label">&nbsp; </label>
                  <button type="button" name="filter" id="filter" class="btn btn-success"><i class="fa fa-search"></i> Search</button>&nbsp;&nbsp;
                  {{-- <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>  --}}
                </div>
              </div>
            </div>

          </div>
        <!-- </div> -->

        <!-- <div class="panel panel-default" id="res_div" style="display: block;">
          <div class="panel-heading" id="panel_head"
            style="font-size: 16px; background: linear-gradient(to right, #c9d6ff, #e2e2e2); font-weight: bold; font-style: italic;">Payment Lot Report
          </div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;"> -->


            <div class="table-responsive resultDiv" id="payment_div" style="display: none;">
              <table id="tableForPayment" class="table table-bordered display" cellspacing="0" width="100%" style="border: 2px solid ghostwhite;">
                <thead style="font-size: 14px;">
                  <tr role="row">
                    <th>Month</th>
                    <th>Amount in Rupees</th>
                  </tr>
                </thead>
                <tbody style="font-size: 14px;"></tbody>
                <tfoot style="font-size: 14px; font-weight: bold; text-align: left;">
                  <tr>
                    <td></td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          <!-- </div>
        </div> -->
      </div>
    </div>
  </section>
</div>
@endsection
<script src="{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script>
<script>
  $(document).ready(function() {
    // Live Clock
    var interval = setInterval(function () {
    var momentNow = moment();
      $('.date-part').html(momentNow.format('DD-MMMM-YYYY'));
      $('.time-part').html(momentNow.format('hh:mm:ss A'));
    }, 100);
    $('#loadingDiv').hide();
    $('#filter_div').removeClass('disabledcontent');
   // $('#loader_img').hide();
//    $('#res_div').hide();
    $('#submit_btn').removeAttr('disabled');
    // $('#change_msg').hide();
    // $('#lot_year_div').hide();
    // $('#lot_month_div').hide();
    // $('#lot_status_div').hide();
    $('.sidebar-menu li').removeClass('active');
 $('.sidebar-menu #paymentReportMain').addClass("active");
 $('.sidebar-menu #monthwisePaymentReport').addClass("active");

  });


  $(document).ready(function(){
    //$('#loadingDiv').hide();



    $('#filter').click(function(){
      //$('#loadingDiv').show();
     // var district_code = $('#district_code').val();
     var phase_code = $('#phase_code').val();
      var lot_year      = $('#lot_year').val();

      if(lot_year =='')
      {
        // alert('Please select financial year');
        $.alert({
          title: 'Alert!',
          content: 'Please select financial year',
          type: 'red',
          icon: 'fa fa-warning',

        });
      }
      else
      {
        $('#payment_div').show();
        list_table();
       // $('#tableForPayment').DataTable().ajax.reload();
        // if(district_code != '' || lot_year != '')
        // {
        //   $('#tableForPayment').DataTable().ajax.reload();
        // }
        // else{
        //   // alert('Please select district or financial year');
        //   $.alert({
        //     title: 'Alert!',
        //     content: 'Please select district or financial year',
        //   });
        // }

      }
    });

    $('#reset').click(function(){
      //$('#loadingDiv').show();
     // $('#district_code').val("");
     $('#phase_code').val("");
      $('#lot_year').val("");
      // $('#tableForPayment').DataTable().ajax.reload();

    });

});


  function ajax_error(jqXHR, textStatus, errorThrown){
    var msg = "<strong>Failed to Load data.</strong><br/>";
    if (jqXHR.status !== 422 && jqXHR.status !== 400) {
      msg += "<strong>" + jqXHR.status + ": " + errorThrown + "</strong>";
    }
    else {
      if (jqXHR.responseJSON.hasOwnProperty('exception')) {
        msg += "Exception: <strong>" + jqXHR.responseJSON.exception_message + "</strong>";
      }
      else {
        msg += "Error(s):<strong><ul>";
        $.each(jqXHR.responseJSON, function (key, value) {
          msg += "<li>" + value + "</li>";
        });
        msg += "</ul></strong>";
      }
    }
    $.alert({
      title: 'Error!!',
      type: 'red',
      icon: 'fa fa-warning',
      content: msg,
    });
  }

 function list_table(){
  var table = "";
  $("#tableForPayment").dataTable().fnDestroy();
 table = $('#tableForPayment').DataTable({

dom: 'Blfrtip',
// "scrollX": true,
"paging": false, // Disable Pagination
"searchable": false,
"ordering":false, // Disable Ordering of all column
"bFilter": false,
"bInfo": false, // Disable Showing 1 to 20 of 2000 entries
"pageLength":25,
'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
"serverSide": true,
"processing":true,
"bRetrieve": true,
"oLanguage": {
    "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="100px"></div>'
  },
  ajax:{
    url: "{{ url('totalMonthwisePaymentReport') }}",
    type: "POST",
    data:function(d){
     // d.district_code = $("#district_code").val(),
     d.phase_code= $("#phase_code").val(),
      d.lot_year      = $("#lot_year").val(),
      d._token        = "{{csrf_token()}}"
    },
    error: function (jqXHR, textStatus, errorThrown) {
      $('.preloader1').hide();
      $.alert({
        title: 'Error!!',
        type: 'red',
        icon: 'fa fa-warning',
        content: 'Somthing went wrong may be session timeout. Please logout and login again.',
      });
       //ajax_error(jqXHR, textStatus, errorThrown);
    }
},

columns: [
   // { "data": "DT_RowIndex" },
    { "data": "month_name" },
    { "data": "amount" },

],
// 'columnDefs': [
//   {
//     "targets": [1],
//     "className": "dt-body-right",
//   }
// ],

"footerCallback": function ( row, data, start, end, display ) {
    var api = this.api(), data;

    // Remove the formatting to get integer data for summation
    var intVal = function ( i ) {
        return typeof i === 'string' ?
            i.replace(/[\$,]/g, '')*1 :
            typeof i === 'number' ?
                i : 0;
    };

    const toIndianCurrency = (num) => {
      const curr = num.toLocaleString('en-IN', {
        style: 'currency',
        currency: 'INR'
      });
      return curr;
    };

    // Total over this page
    total_amount = api
      .column( 1, { page: 'amount'} )
      .data()
      .reduce( function (a, b) {
          return intVal(a) + intVal(b);
      }, 0 );

    $( api.column( 0 ).footer() ).html(
      'Total - '
    );
    $( api.column( 1 ).footer() ).html(
      total_amount
    );
  },

  buttons: [
    {
      extend: 'pdfHtml5',
      title: "Monthwise Payment  Report @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
       messageTop:"Date:@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp, Lot Year - "+$( "#lot_year" ).val(),
      footer: true,
      // orientation: 'landscape',
      pageSize: 'A4',
      pageMargins: [ 5, 5, 5, 5 ],
      exportOptions: {
        columns: [0,1],

      }
    },

    // {
    //   extend: 'excel',
    //   title: "Monthwise Payment  Report @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
    //   messageTop:"Date:@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp, Lot Year - "+$( "#lot_year" ).val()+" , Lot Month - "+ $( "#lot_month option:selected" ).text(),
    //   footer: true,
    //   pageSize:'A4',
    //   orientation: 'landscape',
    //   pageMargins: [ 40, 60, 40, 60 ],
    //   exportOptions: {
    //        columns: [0,1],
    //       stripHtml: true,
    //   }
    // },


    ]


  });
}
</script>
