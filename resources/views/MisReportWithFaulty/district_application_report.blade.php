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

@extends('layouts.app-template-datatable_new')
@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            District Wise Application Report
        </h1>

    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-body">
              

                <div class="panel panel-default">
                    <div class="panel-heading"><span id="panel-icon">Filter Section</div>
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
                                <div class="col-md-4">
                                    <label class=" control-label">District </label>
                                    <select name="districtid" id="districtid" class="form-control">
                                        <option value="">-----Select----</option>
                                        @foreach ($districts as $key=>$value)
                                        <option value="{{$value->district_code}}"> {{$value->district_name}}</option>
                                        @endforeach
                                    </select>

                                </div>
                                {{-- <div class="col-md-4">
                                    <label class=" control-label">Urban/Rural </label>
                                    <select name="rural_urbanid" id="rural_urbanid" class="form-control">
                                        <option value="">-----Select----</option>
                                        @foreach (Config::get('constants.rural_urban') as $key=>$value)
                                        <option value="{{$key}}"> {{$value}}</option>
                                        @endforeach
                                    </select>

                                </div> --}}
                                {{-- <div class="col-md-4">
                                    <label class=" control-label">Block/Subdivision </label>
                                    <select name="blockid" id="blockid" class="form-control">
                                        <option value="">-----Select----</option>

                                    </select>

                                </div> --}}
                                {{-- <div class="col-md-4">
                                    <label class=" control-label">Gp </label>
                                    <select name="gpid" id="gpid" class="form-control">
                                        <option value="">-----Select----</option>
                                    </select>

                                </div> --}}
                            </div>
                            <br>
                            <div class="col-md-12">
                                <div class="col-md-4">
                                    <label class=" control-label">From Date </label>
                                    <input type="text" class="form-control" id="fromdate" name="fromdate"
                                        autocomplete="off" placeholder="DD/MM/YYYY">

                                </div>
                                <div class="col-md-4">
                                    <label class=" control-label">To Date </label>
                                    <input type="text" class="form-control" id="todate" name="todate" autocomplete="off"
                                        placeholder="DD/MM/YYYY">

                                </div>
                                <div class=" col-md-2" style="margin-top: 28px;">
                                    <label class=" control-label">&nbsp; </label>
                                    <button type="button" name="filter" id="filter"
                                        class="btn btn-success">Search</button>


                                </div>
                                <div class="col-md-offset-1" style="margin-top: 28px;">
                                    <label class=" control-label">&nbsp; </label>

                                    <button type="button" name="reset" id="reset" class="btn btn-warning">Reset</button>

                                </div>

                            </div>
                        </div>
                        <br>

                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading" id="panel_head">Filter Records</div>
                    <div class="panel-body" style="padding: 5px; font-size: 14px;">
                        <div class="table-responsive">
                            <table id="example" class="display" cellspacing="0" width="100%">
                                <thead style="font-size: 12px;">
                                    <th>District Name</th>
                                    <th>Partial</th>
                                    <th>Completed</th>
                                    <th>Verified</th>
                                    <th>Approved</th>
                                    <th>Reverted</th>
                                    <th>Rejected</th>
                                    <th>Faulty</th>
                                    <th>Total</th>
                                </thead>
                                <tbody style="font-size: 14px;"></tbody>
                                
                            </table>
                        </div>
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
        $('#fromdate').datepicker({
            format: 'dd/mm/yyyy',
            todayHighlight: true,
            //  endDate: new Date(),
            autoclose: true
    });
    $('#todate').datepicker({
        format: 'dd/mm/yyyy',
            todayHighlight: true,
            //  endDate: new Date(),
            autoclose: true
    });
     var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
     var base_url='{{ url('/') }}';
     
  
    $('.loader_img').hide();
   // $('.sidebar-menu li').removeClass('active');
   // $('.sidebar-menu #lk-main').addClass("active"); 
   // $('.sidebar-menu #processApplication').addClass("active"); 
    var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
    var base_url='{{ url('/') }}';
    $('#opreation_type').val('A');
    $("#verifyReject").html("Approve");
    $('#div_rejection').hide();
     var dataTable = "";
     if ( $.fn.DataTable.isDataTable('#example') ) {
            $('#example').DataTable().destroy();
          }
           dataTable=$('#example').DataTable( {
            dom: 'Blfrtip',
            "scrollX": true,
            "paging": false,
            "searchable": true,
            "ordering":false,
            "bFilter": false,
            "bInfo": true,
            "pageLength":20,
            'lengthMenu': [[10, 20, 30, 50,100], [10, 20, 30, 50,100]],
            "serverSide": true,
            "processing":true,
            "bRetrieve": true,
            "oLanguage": {
              "sProcessing": '<div class="preloader1" align="center"><img src="images/ZKZg.gif" width="150px"></div>'
            },
            "ajax": 
            {
              url: "{{ route('misReportPost') }}",
              type: "get",
              data:function(d){
                d.districtid= $("#districtid").val(),
                  // d.rural_urbanid= $("#rural_urbanid").val(),
                  // d.blockid= $("#blockid").val(),
                 //  d.gpid= $("#gpid").val(),
                   d.fromdate= $("#fromdate").val(),
                   d.todate= $("#todate").val(),
                   d._token= "{{csrf_token()}}"
              },
              error: function (jqXHR, textStatus, errorThrown) {
                $('#filter').removeAttr('disabled',true);
                $('.preloader1').hide();
                console.log(errorThrown);
               // ajax_error(jqXHR, textStatus, errorThrown);
           //   alert(sessiontimeoutmessage);
           // window.location.href=base_url;
              }
            },
            "initComplete":function(){
                $('#filter').removeAttr('disabled',true);
              //console.log('Data rendered successfully');
            },
            "columns": [
              { "data": "district_name" },
              { "data": "partial" },
              { "data": "completed" },
              { "data": "verified" },
              { "data": "approved" },
              { "data": "reverted" },
              { "data": "rejected" },
              { "data": "faulty" },
              { "data": "total" }
            ],   
            "buttons": [
              'pdf','excel','print'
            ],
          });
     
          $('#districtid').change(function() {
       var rural_urbanid=$('#rural_urbanid').val();
     
       
        $('#blockid').html('<option value="">--Select --</option>');
        select_district_code= $('#districtid').val();
       
        var htmlOption='<option value="">--Select--</option>';
        if(rural_urbanid==1){
            $.each(subDistricts, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });

        }
        else{
      
          $.each(blocks, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        }
           
         
        $('#blockid').html(htmlOption);
    
    });
      
  
          $('#rural_urbanid').change(function() {
       var rural_urbanid=$(this).val();
     
       
        $('#blockid').html('<option value="">--Select --</option>');
        select_district_code= $('#districtid').val();
       
        var htmlOption='<option value="">--Select--</option>';
        if(rural_urbanid==1){
            $.each(subDistricts, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });

        }
        else{
      
          $.each(blocks, function (key, value) {
                if((value.district_code==select_district_code)){
                    htmlOption+='<option value="'+value.id+'">'+value.text+'</option>';
                }
            });
        }
           
         
        $('#blockid').html(htmlOption);
    
    });
      $('#filter').click(function(){
           $('.preloader1').show();
        $('#filter').attr('disabled',true);
              dataTable.ajax.reload();
              $('#filter').removeAttr('disabled',true);
          
      });
  
        $('#reset').click(function(){
          $('#rural_urbanid').val('');
          $('#blockid').val('');
          $('#fromdate').val('');
          $('#todate').val('');
           dataTable.ajax.reload();
      });
    
   
  
  
       
    });
  
  
    
    
</script>
@stop