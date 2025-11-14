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
    opacity: 0.6;
    pointer-events: none;
  }  
  </style>
  
  @extends('layouts.app-template-datatable_new')
  @section('content')
  
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Bank Failed List
      </h1>
  
    </section>
    <section class="content">
      <div class="box box-default">
        <div class="box-body">
          <input type="hidden" name="dist_code" id="dist_code" value="{{ $dist_code }}" class="js-district_1">
  
          <div class="panel panel-default">
            <div class="panel-heading"><span id="panel-icon">Filter Here</div>
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
                <div class="form-group col-md-2">
                  <label class="control-label">Rural/Urban </label>
                  <select name="filter_1" id="filter_1" class="form-control">
                    <option value="">-----Select----</option>
                    @foreach ($levels as $key=>$value)
                    <option value="{{$key}}"> {{$value}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-3">
                  <label class="control-label" id="blk_sub_txt">Block/Sub Division </label>
                  <select name="filter_2" id="filter_2" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div>
                 {{-- <div class="form-group col-md-2" id="municipality_div" style="display:none;">
                  <label class="control-label">Municipality</label>
                  <select name="block_ulb_code" id="block_ulb_code" class="form-control">
                    <option value="">-----All----</option>
                  </select>
                </div>
                <div class="form-group col-md-3" style="display:none;" id="gp_ward_div">
                  <label class=" control-label" id="gp_ward_txt">GP/Ward</label>
                  <select name="gp_ward_code" id="gp_ward_code" class="form-control">
                    <option value="">-----Select----</option>
                  </select>
                </div> --}}
                <div class="form-group col-md-5" style="margin-top: 24px;">
                  <button type="button" name="filter" id="filter" class="btn btn-success">Search</button>&nbsp;&nbsp;&nbsp;&nbsp;
                  <button type="button" name="excel_btn" id="excel_btn" class="btn btn-primary">Get Excel</button>&nbsp;&nbsp;&nbsp;&nbsp;
                  <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>
                </div>
              </div>
  
            </div>
          </div>
  
          <div class="panel panel-default">
            <div class="panel-heading" id="panel_head">List of Beneficiaries Account Yet To be Approved</div>
            <div class="panel-body" style="padding: 5px; font-size: 14px;">
              {{-- <div id="loadingDiv">
              </div> --}}
              <div class="table-responsive">
                {{-- <div class="form-group" style="font-weight:bold; font-size:25px;">
                  <label class="control-label">Check All</label>
                <input type="checkbox" id='check_all_btn' style="width:48px;">
                </div> --}}
                <table id="example" class="display" cellspacing="0" width="100%">
                  <thead style="font-size: 12px;">
                    <th>Serial No</th>
                    <th>Beneficiary ID</th>
                    <th>Beneficiary Name</th>
                    <th>Block/ Sub-Division Names</th>
                    <th>Swasthya Sathi Card No</th>
                    <th>Mobile No</th>
                    <th>Failed Type</th>
                    <th>Status</th>
                  </thead>
                  <tbody style="font-size: 14px;"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade bd-example-modal-lg ben_bank_modal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header singleInfo">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <h3 class="modal-title">Beneficiary Details (<span id="ben_id_modal"></span>)</h3>
            </div>
            <div class="modal-body ">
              <div id="loadingDivModal">
              </div>
            <input type="hidden"  id="benId" name="benId" value="">
            <input type="hidden"  id="faildTableId" name="faildTableId" value="">
              <div class="panel-group singleInfo">
                <div class="panel panel-default">
                  <div class="panel-heading" role="tab" id="contact">
  
  
                    <h4 class="panel-title">
                      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapsePersonal"
                        aria-expanded="true" aria-controls="collapsePersonal">Personal Details</a> </h4>
                  </div>
                  <div id="collapsePersonal" class="panel-collapse collapse in" role="tabpanel"
                    aria-labelledby="personal">
                    <div class="panel-body">
                      <table class="table table-bordered">
                        <tbody>
                          <tr>
                            <th scope="row">Beneficiary Name</th>
                            <td id="ben_name_text"></td>
                            <th scope="row">Father's Name</th>
                            <td id="father_name_text"></td>
                          </tr>
                          <tr>
                            <th scope="row">Gender</th>
                            <td id="gender_text"></td>
                            <th scope="row">Date of Birth :(DD-MM-YYYY)</th>
                            <td id="dob_text"></td>
                          </tr>
                          <tr>
                            <th scope="row">Caste</th>
                            <td id="caste_text"></td>
  
                          </tr>
  
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="panel-group singleInfo">
                <div class="panel panel-default">
                  <div class="panel-heading" role="tab" id="contact">
  
  
                    <h4 class="panel-title">
                      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseBank"
                        aria-expanded="true" aria-controls="collapseBank">Bank Details</a> </h4>
                  </div>
                  <div id="collapseBank" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="banks">
                    <div class="panel-body">
                      <div class="form-group">
                        <h4 style="text-align: center; color:firebrick;">Failed Reason:- <span id="failed_reason"></span></h4>
                      </div>
                      <table class="table table-bordered">
                        <tbody>
                          <tr>
                            <th scope="row" class="required">Mobile Number</th>
                            <td id="mobile_text"><input type="text" value="" name="mobile_no" maxlength="10"
                                id="mobile_no" disabled></td>
                            <th scope="row" class="required">Bank IFSC Code</th>
                            <td id="bank_ifsc_text"><input type="text" value="" name="bank_ifsc"
                                onkeyup="this.value = this.value.toUpperCase();" id="bank_ifsc">
                                <img src="{{ asset('images/ajaxgif.gif') }}" width="60px" id="ifsc_loader" style="display: none;">
                              <span id="error_bank_ifsc_code" class="text-danger"></span></td>
                          </tr>
                          <tr>
                            <th scope="row" class="required">Bank Name</th>
                            <td id="bank_text"><input type="text" value="" name="bank_name" maxlength="200" id="bank_name"
                                readonly>
                              <span id="error_name_of_bank" class="text-danger"></span></td>
                            <th scope="row" class="required">Bank Branch Name</th>
                            <td id="branch_text"><input type="text" value="" name="branch_name" id="branch_name" readonly>
                              <span id="error_bank_branch" class="text-danger"></span></td>
                          </tr>
                          <tr>
                            <th scope="row" class="required">Bank Account Number</th>
                            <td id="bank_acc_text"> <input type="text" value="" name="bank_account_number" maxlength='20'
                                id="bank_account_number">
                              <span id="error_bank_account_number" class="text-danger"></span></td>
  
                          </tr>
  
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
  
  
  
            </div>
            <div class="modal-footer">
              <button type="button" style="float: center" class="btn btn-primary btnUpdate">Update</button>
            </div>
  
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
     // $('#loadingDiv').hide();
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #bankTrFailed').addClass("active"); 
    $('.sidebar-menu #accValTrFailedApproved').addClass("active"); 
    
    $('#excel_btn').click(function(){
      var token = "{{csrf_token()}}";
      var filter_1= $('#filter_1').val();
      var filter_2= $('#filter_2').val();

             var  data= {'_token': token, 'filter_1': filter_1, 'filter_2': filter_2 };
              redirectPost('getBankFailedexcel',data); 
    });
  
  
  
    $('.content').addClass('disabledcontent');
     var dataTable = "";
     if ( $.fn.DataTable.isDataTable('#example') ) {
            $('#example').DataTable().destroy();
          }
           dataTable=$('#example').DataTable( {
            dom: 'Blfrtip',
            "scrollX": true,
            "paging": true,
            "searchable": true,
            "ordering":false,
            "bFilter": true,
            "bInfo": true,
            "pageLength":20,
            'lengthMenu': [[20,50,100], [20,50,100]],
            "serverSide": true,
            "processing":true,
            "bRetrieve": true,
            "oLanguage": {
              "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
            },
            ajax:{
          url: "{{ url('completedBankValidationApproved') }}", 
          type: "POST",
          data:function(d){
            d.filter_1 = $('#filter_1').val(),
            d.filter_2 = $('#filter_2').val(),
            d.block_ulb_code = $('#block_ulb_code').val(),
            d.gp_ward_code = $('#gp_ward_code').val(),
            d._token= "{{csrf_token()}}"
          },
        
          error: function (jqXHR, textStatus, errorThrown) {
            $('#loadingDiv').hide();
            $('.content').removeClass('disabledcontent');
               ajax_error(jqXHR, textStatus, errorThrown)
              }                
        },
            "initComplete":function(){
              //console.log('Data rendered successfully');
              $('.content').removeClass('disabledcontent');
              $('#loadingDiv').hide();
            },
            "columns": [
              { "data": "DT_RowIndex" },
              { "data": "beneficiary_id" },
          { "data": "name" },
          { "data": "block_ulb_name"},
          { "data": "ss_card_no"},
          { "data": "mobile_no" },
          { "data": "type" },
            { "data": "status" },
         
            ],
      //    "columnDefs": [
      //         {
      //             "targets": [ 4,5 ],
      //             "visible": false,
      //             "searchable": true
      //         },
      //         {
      //   "targets": [ 7 ],
      //   "orderable": false,
      //   "searchable": true
      // }
      //    ],
            "buttons": [
              {
                extend: 'pdf',
           
                title: "Account Approved  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ; @endphp ",
             footer: true,
             orientation : 'landscape',
           // pageSize : 'LEGAL',
             pageMargins: [ 40, 60, 40, 60 ],
             exportOptions: {
              columns: [0,1,2,3,4,5,6],
  
              }
         },
              {
             extend: 'excel',
           
             title: "Account Approved  Report Generated On-@php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp ",   
        messageTop: "Date: @php date_default_timezone_set('Asia/Kolkata');$date = \Carbon\Carbon::createFromFormat('F j, Y g:i:a', date('F j, Y g:i:a'));$date = $date->format('F j, Y g:i:a'); echo    $date ;@endphp",
             footer: true,
             pageSize:'A4',
             //orientation: 'landscape',
             pageMargins: [ 40, 60, 40, 60 ],
             exportOptions: {
            
              columns: [0,1,2,3,4,5,6],
  
                  stripHtml: true,
              }
         },
            
            ],
          });
   // --------------- Filter Section -------------------- //
   $('#filter').click(function(){
    if($('#filter_1').val() == '') {
      $.alert({
        title : "Alert!!",
        content: "Please Select Filter Criteria"
      });
    }
    else {
      dataTable.ajax.reload();
    }
  });
   
  $('#reset').click(function(){
    $('#filter_1').val('').trigger('change');
    $('#filter_2').val('').trigger('change');
    $('#block_ulb_code').val('').trigger('change');
    $('#gp_ward_code').val('').trigger('change');
    dataTable.ajax.reload();
  });
    // ------------ Master DropDown Section Start-------------------- //
  $('#filter_1').change(function() {
    var filter_1=$(this).val();
     
    $('#filter_2').html('<option value="">--All --</option>');
    $('#block_ulb_code').html('<option value="">--All --</option>');
    select_district_code= $('#dist_code').val();
     
    var htmlOption='<option value="">--All--</option>';
    $('#gp_ward_code').html('<option value="">--All --</option>');
    if(filter_1==1){
      $.each(subDistricts, function (key, value) {
          if((value.district_code==select_district_code)){
              htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
          }
      });
      $("#blk_sub_txt").text('Subdivision');
      $("#gp_ward_txt").text('Ward');
      $("#municipality_div").show();
      $("#gp_ward_div").show();
    }
    else if(filter_1==2){
     // console.log(filter_1);
      $.each(blocks, function (key, value) {
        if((value.district_code==select_district_code)){
            htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
        }
      });
      $("#blk_sub_txt").text('Block');
      $("#gp_ward_txt").text('GP');
      $("#municipality_div").hide();
      $("#gp_ward_div").show();
    }
    else{
      $("#blk_sub_txt").text('Block/Subdivision');
      $("#gp_ward_txt").text('GP/Ward');
      $("#municipality_div").hide();
    }
    $('#filter_2').html(htmlOption);
     
  });
  $('#filter_2').change(function() {
    var rural_urbanid= $('#filter_1').val();
    $('#gp_ward_code').html('<option value="">--All --</option>');
    if(rural_urbanid==1){
      var sub_district_code=$(this).val();
      if(sub_district_code!=''){
        $('#block_ulb_code').html('<option value="">--All --</option>');
        select_district_code= $('#dist_code').val();
        var htmlOption='<option value="">--All--</option>';
        $.each(ulbs, function (key, value) {
          if((value.district_code==select_district_code) && (value.sub_district_code==sub_district_code)){
            htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
          }
        });
        $('#block_ulb_code').html(htmlOption);
      }
      else{
        $('#block_ulb_code').html('<option value="">--All --</option>');
      }   
    } 
    else if(rural_urbanid==2){
      $('#muncid').html('<option value="">--All --</option>');
      $("#municipality_div").hide();
      var block_code=$(this).val();
      select_district_code= $('#dist_code').val();
      var htmlOption='<option value="">--All--</option>';
      $.each(gps, function (key, value) {
        if((value.district_code==select_district_code) && (value.block_code==block_code)){
          htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
        }
      });
      $('#gp_ward_code').html(htmlOption);
      $("#gp_ward_div").show();
    }
    else{
      $('#block_ulb_code').html('<option value="">--All --</option>');
    } 
  });
  $('#block_ulb_code').change(function() {
    var muncid=$(this).val();
    var district=$("#dist_code").val();
    var urban_code=$("#filter_1").val();
    if(district==''){
      $('#filter_1').val('');
      $('#filter_2').html('<option value="">--All --</option>');
      $('#block_ulb_code').html('<option value="">--All --</option>'); 
    }
    if(urban_code==''){
      // alert('Please Select Rural/Urban First');
      $('#filter_2').html('<option value="">--All --</option>');
      $('#block_ulb_code').html('<option value="">--All --</option>'); 
      $("#filter_1").focus();
    }
    if(muncid!=''){
      var rural_urbanid= $('#filter_1').val();   
      if(rural_urbanid==1){
        $('#gp_ward_code').html('<option value="">--All --</option>');
        var htmlOption='<option value="">--All--</option>';
        $.each(ulb_wards, function (key, value) {
          if(value.urban_body_code==muncid){
            htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
          }
        });
        $('#gp_ward_code').html(htmlOption);
        //console.log(htmlOption);
      } 
      else{
        $('#gp_ward_code').html('<option value="">--All --</option>');
        $("#gp_ward_div").hide();
      } 
    }
    else{
      $('#gp_ward_code').html('<option value="">--All --</option>');
    }  
  });
  // ------------ Master DropDown Section End-------------------- // 
  
  });
  </script>
  @stop