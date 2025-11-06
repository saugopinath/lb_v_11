<link href="{{ asset("css/select2.min.css") }}" rel="stylesheet">

<style>
  #searchbtn
  {
   margin:20px auto;
  }
  #loader{
    margin:0px 0px 0px 350px;;
  }
  .select2{
    width:100%!important;
  }
  .select2 .has-error {
    border-color:#cc0000;
   background-color:#ffff99;
   }
  .requied{
  color:red;
}

@import url(https://fonts.googleapis.com/css?family=Cinzel:700);

/* Timeline */
.timeline,
.timeline-horizontal {
  list-style: none;
  padding: 20px;
  position: relative;
}
.timeline:before {
  top: 40px;
  bottom: 0;
  position: absolute;
  content: " ";
  width: 3px;
  background-color: #eeeeee;
  left: 50%;
  margin-left: -1.5px;
}
.timeline .timeline-item {
  margin-bottom: 20px;
  position: relative;
}
.timeline .timeline-item:before,
.timeline .timeline-item:after {
  content: "";
  display: table;
}
.timeline .timeline-item:after {
  clear: both;
}
.timeline .timeline-item .timeline-badge {
  color: #fff;
  width: 54px;
  height: 54px;
  line-height: 52px;
  font-size: 22px;
  text-align: center;
  position: absolute;
  top: 18px;
  left: 50%;
  margin-left: -25px;
  background-color: #bbdefb;
  border: 3px solid #ffffff;
  z-index: 100;
  border-top-right-radius: 50%;
  border-top-left-radius: 50%;
  border-bottom-right-radius: 50%;
  border-bottom-left-radius: 50%;
}
.timeline .timeline-item .timeline-badge i,
.timeline .timeline-item .timeline-badge .fa,
.timeline .timeline-item .timeline-badge .glyphicon {
  top: 2px;
  left: 0px;
}
.timeline .timeline-item .timeline-badge.primary {
  background-color: #bbdefb;
}
.timeline .timeline-item .timeline-badge.info {
  background-color: #26c6da;
}
.timeline .timeline-item .timeline-badge.success {
  background-color: #80DEEA;
}
.timeline .timeline-item .timeline-badge.warning {
  background-color: #a7ffeb;
}
.timeline .timeline-item .timeline-badge.danger {
  background-color: #42a5f5;
}
.timeline .timeline-item .timeline-panel {
  position: relative;
  width: 46%;
  float: left;
  right: 16px;
  border: 1px solid #c0c0c0;
  background: #ffffff;
  border-radius: 2px;
  padding: 20px;
  -webkit-box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
  box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
}
.timeline .timeline-item .timeline-panel:before {
  position: absolute;
  top: 26px;
  right: -16px;
  display: inline-block;
  border-top: 16px solid transparent;
  border-left: 16px solid #c0c0c0;
  border-right: 0 solid #c0c0c0;
  border-bottom: 16px solid transparent;
  content: " ";
}
.timeline .timeline-item .timeline-panel .timeline-title {
  margin-top: 0;
  font-size: 25px;
  font-family: 'Waiting for the Sunrise', cursive; 
  color: #0c0c0c
}
.timeline .timeline-item .timeline-panel .timeline-body > p,
.timeline .timeline-item .timeline-panel .timeline-body > ul {
  margin-bottom: 0;
  font-family: 'Cinzel',sans-serif;
  color: #a79898;
}
.timeline .timeline-item .timeline-panel .timeline-body > p + p {
  margin-top: 0px;
}
.timeline .timeline-item:last-child:nth-child(even) {
  float: right;
}
.timeline .timeline-item:nth-child(even) .timeline-panel {
  float: right;
  left: 16px;
}
.timeline .timeline-item:nth-child(even) .timeline-panel:before {
  border-left-width: 0;
  border-right-width: 14px;
  left: -14px;
  right: auto;
}
.timeline-horizontal {
  list-style: none;
  position: relative;
  padding: 20px 0px 20px 0px;
  display: inline-block;
}
.timeline-horizontal:before {
  height: 3px;
  top: auto;
  bottom: 26px;
  left: 56px;
  right: 0;
  width: 100%;
  margin-bottom: 20px;
}
.timeline-horizontal .timeline-item {
  display: table-cell;
  height: 280px;
  width: 20%;
  min-width: 320px;
  float: none !important;
  padding-left: 0px;
  padding-right: 20px;
  margin: 0 auto;
  vertical-align: bottom;
}
.timeline-horizontal .timeline-item .timeline-panel {
  top: auto;
  bottom: 64px;
  display: inline-block;
  float: none !important;
  left: 0 !important;
  right: 0 !important;
  width: 100%;
  margin-bottom: 20px;
}
.timeline-horizontal .timeline-item .timeline-panel:before {
  top: auto;
  bottom: -16px;
  left: 28px !important;
  right: auto;
  border-right: 16px solid transparent !important;
  border-top: 16px solid #c0c0c0 !important;
  border-bottom: 0 solid #c0c0c0 !important;
  border-left: 16px solid transparent !important;
}
.timeline-horizontal .timeline-item:before,
.timeline-horizontal .timeline-item:after {
  display: none;
}
.timeline-horizontal .timeline-item .timeline-badge {
  top: auto;
  bottom: 0px;
  left: 43px;
}


</style>
@extends('pensionForm.base')

@section('action-content')

    <!-- Main content -->
    <section class="content">
    
      <div class="box">
      @if ( ($crud_status = Session::get('crud_status')))
              <div class="alert alert-{{$crud_status=='success'?'success':'danger'}} alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button> 
                      <strong>{{ Session::get('crud_msg') }} @if($crud_status=='success') with Application ID: {{ Session::get('id') }}@endif</strong>
                 
               
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
  <div class="box-header">
  <form method="post" id="register_form" action="{{url('ajaxgetApplicationListVerifier')}}"  class="submit-once" >
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <input type="hidden" name="user_id" id="user_id" value="{{$user_id}}">
    <input type="hidden" name="scheme_code" id="scheme_code" value="{{$scheme_id}}">

   <div class="row">
                                                                    
         
     
    <div class="form-group col-md-4">
             <label class="required-field">Application Id/Mobile No./Swasthyasathi Card No./Aadhaar No.<span class="requied">*</span></label>
          <input type="text" name="applicant_id" id="applicant_id" class="form-control" placeholder="Application Id/Mobile No./Swasthyasathi Card No./Aadhaar No." autocomplete="off"/>                                                      
          <span id="error_applicant_id" class="text-danger"></span>
         </div>
   
    
                                                
        
     
   

    
   
                                                                                                              
          <div class="form-group col-md-4"  id="search_div">
        <button type="button" class="btn btn-primary btn-lg" id="searchbtn">Search</button> 
        </div>                                                                                                                                          
    </div>                                                                    
  </div>
    <div class="alert print-error-msg"  style="display:none;" id="crud_msg_Crud">
      <button type="button" class="close"  aria-label="Close" onclick="closeError('crud_msg_Crud')"><span aria-hidden="true">&times;</span></button>
      <ul></ul></div>
  </form>
  
  <!-- /.box-header -->
  <div class="box-body" id="ajaxData">



</div>
   
  <!-- /.box-body -->
</div>

     
    
    <!-- /.content -->
  </div>
<script src="{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script>
<script type="text/javascript">
 $('.sidebar-menu li').removeClass('active');
 $('.sidebar-menu #lk-main').addClass("active");  
 $('.sidebar-menu #appplicantTrack').addClass("active");  
 var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
 var base_url='{{ url('/') }}';       
var PleaseSelectScheme='@lang('lang.PleaseSelectScheme')';
var PleaseEnterApplicationId='@lang('lang.PleaseEnterApplicationId')';
 $("#searchbtn").click(function(){
  var scheme_code=$("#scheme_code").val(); 
  var applicant_id=$("#applicant_id").val(); 
  //console.log(application_type); 
  var status1=status2=status3=0;
  if(scheme_code=='' || typeof(scheme_code) === "undefined" || scheme_code===null){
    $('#error_scheme_code').text(PleaseSelectScheme);
    status1=0;
  }
  else{
    $('#error_scheme_code').text('');
      status1=1;
  }
   if(applicant_id=='' || typeof(applicant_id) === "undefined" || applicant_id===null){
    $('#error_applicant_id').text(PleaseEnterApplicationId);
    status1=0;
  }
  else{
    $('#error_application_type').text('');
      status2=1;
  }
  if(status1 && status2){
     var url='{{ url('ajaxApplicationTrack') }}';
     var role_code=$('#role_code').val();
     $('#ajaxData').html('<img align="center" src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
       $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                       scheme_code: scheme_code,
                       applicant_id: applicant_id,
                      _token: '{{ csrf_token() }}',
                    },
                    success: function (data) {
                       $("#modal_data").html('');
                       $("#ajaxData").html(data);
                   

                   

                        
                    },
                    error: function (ex) {
                       $("#modal_data").html('');
                       $('#ajaxData').html('');
                       alert('Timeout ..Please try again.');
                       location.reload();
                    }
                  });
  }             
  });
</script>

@endsection
