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
        Name Mismatch Validation Report
      </h1>
      {{-- <ol class="breadcrumb">
        <span style="font-size: 12px; font-weight: bold;"><i class="fa fa-clock-o"> Date : </i><span
            class='date-part'></span>&nbsp;&nbsp;<span class='time-part'></span></span>
      </ol> --}}
    </section>
    <section class="content">
      <div class="box box-default">
        <div class="box-body" >
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

                  <div class="col-md-3">
                    <label class="control-label">Select District <span class="text-danger"></span></label>
                    <select class="form-control" name="district_code" id='district_code' required >
                      <option value="">--Select District--</option>
                      @foreach($districts as $val)
                      <option value="{{$val->district_code}}">{{$val->district_name}}</option>
                     @endforeach
                    </select>
                  </div>
                  <div class="col-md-offset-1 col-md-3" style="margin-top: 26px;">
                    <label class=" control-label">&nbsp; </label>
                    <button type="button" name="filter" id="filter" class="btn btn-success">Search</button>
                    &nbsp;
                    <button type="button" name="excel_btn" id="excel_btn" class="btn btn-primary"><i class="fa fa-file-excel-o"></i> Get Excel</button>
                    &nbsp;
                    <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>

                  </div>
                </div>
              </div>
            </div>
          <!-- </div> -->

              <div class="table-responsive resultDiv" id="validation_lot_div">
                <table id="tableForNameValidation" class="display" cellspacing="0" width="100%" style="border: 1px solid ghostwhite;">
                  <thead style="font-size: 12px;">
                    <tr role="row">
                      <th>Serial No</th>
                      <th>Application ID</th>

                      <th>Bank Account No</th>
                      <th>Bank IFSC</th>
                      {{-- <th>Bank  Name/Branch</th> --}}
                      <th>Beneficiary Name</th>
                      <th>Name From Bank Validation</th>

                    </tr>
                  </thead>
                  <tbody style="font-size: 14px;"></tbody>
                  <tfoot style="font-size: 14px; font-weight: bold; text-align:right;"><tr><td></td><td></td><td></td><td></td><td></td><td></td></tr></tfoot>
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
        getTableList();
      $('#excel_btn').click(function(){
      var token = "{{csrf_token()}}";
      var  data= {'_token': token};
       redirectPost('getNameMismatchExcelList',data);
    });

      var interval = setInterval(function () {
      var momentNow = moment();
        $('.date-part').html(momentNow.format('DD-MMMM-YYYY'));
        $('.time-part').html(momentNow.format('hh:mm:ss A'));
      }, 100);
      $('#loadingDiv').hide();

   $('.sidebar-menu li').removeClass('active');
   $('.sidebar-menu #paymentReportMain').addClass("active");
   $('.sidebar-menu #nameValidationReport').addClass("active");

    });


    $(document).ready(function(){
    $('#filter').click(function(){

          var district_code = $('#district_code').val();

          if(district_code != '' )
          {
            getTableList();
          }
          else{

            $.alert({
              title: 'Alert!',
              type: 'red',
              icon: 'fa fa-warning',

              content: 'Please select district  ',
            });
          }
        });

        $('#reset').click(function(){
          //$('#loadingDiv').show();
          $('#district_code').val("");
          getTableList();

        });

  });

  function getTableList(){
    $('#loadingDiv').show();
    if ( $.fn.DataTable.isDataTable('#tableForNameValidation') ) {
          $('#tableForNameValidation').DataTable().destroy();
        }
        var dataTableValidation=$('#tableForNameValidation').DataTable( {
      dom: 'Blfrtip',
      "scrollX": true,
      "paging": true,
      "searchable": true,
      "ordering":false,
      "bFilter": true,
      "bInfo": true,
      "pageLength":20,
      'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
      "serverSide": true,
      "processing":true,
      "bRetrieve": true,
      "bStateSave": true,
      "oLanguage": {
            "sProcessing": '<div class="preloader1" align="center"><h4 class="text-success" style="font-weight:bold;font-size:22px;">Processing...</h4></div>'
          },
      "ajax":
      {
        url: "{{ route('getNameMismatchValidationList') }}",
        type: "post",
        data: {'_token':"{{csrf_token()}}", district_code:$('#district_code').val()},
        error: function (jqXHR, textStatus, errorThrown) {
       //   $('.preloader1').hide();
          $('#loadingDiv').hide();


          ajax_error(jqXHR, textStatus, errorThrown);
        }
      },



      "initComplete":function(){
        $('#loadingDiv').hide();

      },
      "columns": [
        { "data": "DT_RowIndex" },
             { "data": "application_id" },

             { "data": "last_accno"  },
             { "data": "last_ifsc"  },
             { "data": "ben_name" },
             { "data": "name_response"  },
      ],

      "buttons": [
      {
         extend: 'pdf',
         title: 'Validation Lot Report',
         footer: true,
         pageSize:'A4',
         orientation: 'landscape',
         pageMargins: [ 40, 60, 40, 60 ],
         exportOptions: {
              columns: [0,1,2,3,4,5,6,7,8,9],

          }
       },
       {
         extend: 'excel',
         title: 'Validation Lot Report',
         footer: true,
         pageSize:'A4',
         //orientation: 'landscape',
         pageMargins: [ 40, 60, 40, 60 ],
         exportOptions: {
               columns: [0,1,2,3,4,5,6,7,8,9],
              stripHtml: true,
          }
       },
        // 'pdf','excel'
      ],
    });
  }
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

    function redirectPost(url, data , method = 'post'){
    var form = document.createElement('form');
    form.method = method;
    form.action = url;
    for (var name in data) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = data[name];
      form.appendChild(input);
    }
    $('body').append(form);
    form.submit();
  }
  </script>















