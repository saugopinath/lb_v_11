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
    }
    .resultDiv {
      border: 1px solid #d9d9d9;
      border-left: 3px solid seagreen;
      /*margin-bottom: 10px; */
      padding: 8px;
      box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }
  </style>

  @extends('layouts.app-template-datatable')
  @section('content')

  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Monthly Disbursement Report <span style="font-size: 16px;">(Based on the Lot Pushed date to the bank)</span>
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
            <div class="panel-heading">Search By District</div>
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
                    @php
                    $current_year= date('Y');
                    $next_year= date('Y')+1;
                    $pre_year= date('Y')-1;
                    if(date('m')>3) {
                      $financial_year=$current_year.'-'. $next_year;
                    }
                    else {
                      $financial_year=$pre_year.'-'.$current_year;
                    }
                    @endphp
                    @foreach(Config::get('constants.fin_year') as $year)
                    <option value="{{ $year}}" @if($financial_year==$year) selected @endif>{{$year}}</option>
                    @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="control-label">From Date<span class="text-danger"></span></label>
                  <input type="text" id="from_date" class="form-control" autocomplete="off"name="from_date" placeholder="DD/MM/YYYY">
                  </div>
                  <div class="col-md-3">
                    <label class="control-label">To Date <span class="text-danger"></span></label>
                    <input type="text" id="to_date" class="form-control"  autocomplete="off"name="to_date" placeholder="DD/MM/YYYY">
                  </div>
                  <div class="col-md-offset-1 col-md-2" style="margin-top: 26px;">
                    <label class=" control-label">&nbsp; </label>
                    <button type="button" name="filter" id="filter" class="btn btn-success">Search</button>
                    &nbsp;
                    <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>
                  </div>
                </div>
              </div>
            </div>
          <!-- </div> -->

              <div class="table-responsive resultDiv" id="validation_lot_div">
                <table id="tableForLot" class="display" cellspacing="0" style="width:100%; border: 1px solid ghostwhite;">
                  <thead style="font-size: 12px;">
                    <tr role="row">
                      <th>Sl No</th>
                      <th>Lot Pushed Date</th>
                      <th>Beneficiaries Send <br/>To Bank</th>
                      <th>Beneficiaries Send To <br/>Bank Amount</th>
                      <th>Success</th>
                      <th>Amount Credited</th>
                      <th>Failed</th>
                      <th>Amount Not<br/> Credited</th>
                      <th>Beneficiaries <br/>Response Pending</th>
                    </tr>
                  </thead>
                  <tbody style="font-size: 14px;"></tbody>
                  <tfoot style="font-size: 14px; font-weight: bold; ">
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
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
      $('#from_date').datepicker({
  format: "dd/mm/yyyy",
  todayHighlight: true,
  autoclose: true,
  "setDate": "today",
  "endDate": "today+1",
  //   "maxDate":  new Date(),

  });
  $('#to_date').datepicker({
  format: "dd/mm/yyyy",
  todayHighlight: true,
  autoclose: true,
  "setDate": "today",
  "endDate": "today+1",
  //   "maxDate":  new Date(),

  });
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
   $('.sidebar-menu #monthDisbursedAmountReport').addClass("active");

    });


    $(document).ready(function(){
      //$('#loadingDiv').hide();
      var table = $('#tableForLot').DataTable({

          dom: 'Blfrtip',
           "scrollX": true,
          "paging": true, // Disable Pagination
          "searchable": false,
          "ordering":false, // Disable Ordering of all column
          "bFilter": true,
          "bInfo": false, // Disable Showing 1 to 20 of 2000 entries
          "pageLength":20,
          'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
          "serverSide": true,
          "processing":true,
          "bRetrieve": true,

          "oLanguage": {
              "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="100px"></div>'
            },
            ajax:{
              url: "{{ route('monthly-disbursement-report') }}",
              type: "POST",
              data:function(d){
                $('#loadingDiv').hide();
                d.lot_year= $("#lot_year").val(),
                d.from_date= $("#from_date").val(),
                d.to_date= $("#to_date").val(),
                d._token= "{{csrf_token()}}"
              },

              error: function (jqXHR, textStatus, errorThrown) {
                $('.preloader1').hide();
                $('#loadingDiv').hide();
                // $.alert({
                //   title: 'Error!!',
                //   type: 'red',
                //   icon: 'fa fa-warning',
                //   content: 'Somthing went wrong may be session timeout. Please logout and login again.',
                // });
                ajax_error(jqXHR, textStatus, errorThrown);
              }
          },
          columns: [
              { "data": "DT_RowIndex" },
              { "data": "pushed_date" },
              { "data": "ben_count","defaultContent":"0" },
              { "data": "debit_amount","defaultContent":"0"  },
              { "data": "success_count","defaultContent":"0"  },
              { "data": "success_amount","defaultContent":"0"  },
              { "data": "failed_count","defaultContent":"0"  },
              { "data": "failed_amount","defaultContent":"0"  },
              { "data": "pending_ben_count","defaultContent":"0"  },
          ],
      'columnDefs': [
            {
              "targets": [2,3,4],
              //"className": "dt-body-right",
            }
          ],

          "footerCallback": function ( row, data, start, end, display ) {

              var api = this.api(), data;

              // Remove the formatting to get integer data for summation
              var intVal = function ( i ) {
                  return typeof i === 'string' ?
                      i.replace(/[\$,]/g, '')*1 :
                      typeof i === 'number' ?
                          i : 0;
              };

              // Total over this page


              ben_count = api
                .column( 2, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                debit_amount = api
                .column( 3, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                success_count = api
                .column( 4, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                success_amount = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                failed_count = api
                .column( 6, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                failed_amount = api
                .column( 7, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                pending_ben_count = api
                .column( 8, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

              // Update footer
              $( api.column( 0 ).footer() ).html(
                "Total-"
              );
              $( api.column( 1 ).footer() ).html(
                ""
              );
              $( api.column( 2 ).footer() ).html(
                ben_count
              );
              $( api.column( 3 ).footer() ).html(
                debit_amount
              );
              $( api.column( 4 ).footer() ).html(
                success_count
              );
              $( api.column( 5 ).footer() ).html(
                success_amount
              );
              $( api.column( 6 ).footer() ).html(
                failed_count
              );
              $( api.column( 7 ).footer() ).html(
                failed_amount
              );
              $( api.column( 8 ).footer() ).html(
                pending_ben_count
              );
            },

          buttons: [
         {
          extend : 'pdfHtml5',
          //  extend: 'pdf',
          title: "Monthly Disbursement Report @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
             messageTop:"Date:@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
             footer: true,
             pageSize:'A4',

            orientation : 'landscape',
           // pageSize : 'LEGAL',
            pageMargins: [ 40, 60, 40, 60 ],
            exportOptions: {
                columns: [0,1,2,3,4,5,6,7,8],

            }
         },

         {
            extend: 'excel',
            title: "Monthly Disbursement Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",


             messageTop:"Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
             footer: true,
             pageSize:'A4',
            //orientation: 'landscape',
            pageMargins: [ 40, 60, 40, 60 ],
            exportOptions: {
              columns: [0,1,2,3,4,5,6,7,8],
                stripHtml: true,
            }
         },

        ]
      });


        $('#filter').click(function(){
          $('#tableForLot').DataTable().ajax.reload();
          //$('#loadingDiv').show();

        });

        $('#reset').click(function(){
          //$('#loadingDiv').show();
          $('#from_date').val("");
          $('#to_date').val("");
          $('#tableForLot').DataTable().ajax.reload();
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


  </script>















