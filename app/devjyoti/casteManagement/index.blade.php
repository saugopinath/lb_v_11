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
      color: #d9534f;
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
      Update/Change Caste Details For Approved Beneficiary
    </h1>

  </section>
  <section class="content">
    <div class="box box-default" id="full-content">
      <div class="box-body">
        <div class="panel panel-default">
          <div class="panel-heading"><span id="panel-icon">Enter Beneficiary Details Here &nbsp;&nbsp; <span class="label label-info">
               <strong> Note:Caste may be changed for beneficiaries which are not in Payment process</strong>
                
            </span></div>
          <div class="panel-body" style="padding: 5px;">
          
            <div class="row">
              @if ( ($message = Session::get('success')))
              <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>

              </div>
              @endif
              @if(count($errors) > 0)
              <div class="col-md-12">
              <div class="alert alert-danger alert-block">
                <ul>
                  @foreach($errors->all() as $error)
                  <li><strong> {{ $error }}</strong></li>
                  @endforeach
                </ul>
              </div>
              </div>
              @endif
              @if ( ($error = Session::get('error')))
               <div class="row">
               <div class="alert alert-danger alert-block" style="margin:10px 30px 10px 30px;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $error }}</strong>
        
              </div>
               </div>
              @endif
            </div>
             <form name="casteManagement" id="casteManagement" method="post" action="{{url('casteManagement')}}" onsubmit="return validate();" >
              {{ csrf_field() }}
            <div class="row">
              <div class="col-md-12">
                <div class="form-group col-md-3">
                  <label for="select_type">Search Using <span class="text-danger">*</span></label>
                  <select class="form-control" name="select_type" id="select_type">
                       @foreach(Config::get('globalconstants.search_payment_status') as $key=> $search_type)
                          <option value="{{$key}}" @if($key==$fill_array['select_type']) selected @endif>{{$search_type}}</option>
                       @endforeach
                  </select>
                   <span style="font-size: 14px;" id="error_select_type" class="text-danger"></span>
                </div>
                <div class="form-group col-md-3" id="beneficiary_id_div">
                  <label for="beneficiary"><span id="search_text"> {{$fill_array['search_text']}}</span> <span class="text-danger">*</span></label>
                  <input type="text" name="ben_id" id="ben_id" class="form-control" 
                  onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;" placeholder="Enter Beneficiary ID" value={{$fill_array['ben_id']}}>
                   <span style="font-size: 14px;" id="error_ben_id" class="text-danger"></span>
                </div>
               
               
                <div class="form-group col-md-2" style="margin: 23px;">
                <input class="btn btn-success" type="submit" name="btnSubmit" value="Search">

                </div>
              </div>
            </div>
            </form>
          </div>
        </div>
          @if(!empty($errorMsg))
             
              <div class="alert alert-danger alert-block">
               <strong> {{ $errorMsg }}</strong></li>
                
              </div>
              
        @endif
        <br/>
          @if(count($result)>0)
        <div class="panel panel-default" id="listing_div" >
          <div class="panel-heading" id="panel_head">List of beneficiaries
          </div>
          <div class="panel-body" style="padding: 5px; font-size: 14px;">
            <div id="loadingDiv">
            </div>
            <div class="table-responsive">
              <table id="example" class="table table-striped" cellspacing="0" width="100%">
                <thead style="font-size: 12px;">
                  {{-- <th>Serial No</th> --}}
                  <th>Beneficiary ID</th>
                  <th>Beneficiary Name</th>
                  <th>Mobile No.</th>
                  <th>Application Id</th>
                  <th>Message</th>
                  <th>Action</th>
                </thead>
                <tbody style="font-size: 14px;">
                @foreach ($result as $row)
               
                  <tr> 
                  <td>{{$row['beneficiary_id']}}</td>
                  <td>{{$row['ben_fname']}}</td>
                  <td>{{$row['mobile_no']}}</td>
                  <td>{{$row['application_id']}}</td>
                  <td>{{$row['msg']}}</td>
                 
                  <td>
                   @if($row['can_update_edit']==1)
                   <a class="btn btn-info btn-sm" href="{{url('changeCaste')}}?id={{$row['beneficiary_id']}}&is_faulty={{intval($row['is_faulty'])}}&caste_change_type=1"><i class="fa fa-edit"></i> Caste Info Change</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                   
                   @endif
                   @if($row['can_update_switch']==1)
                   <a class="btn btn-danger btn-sm" href="{{url('changeCaste')}}?id={{$row['beneficiary_id']}}&is_faulty={{intval($row['is_faulty'])}}&caste_change_type=2"><i class="fa fa-edit"></i>Caste Change</a>
                  
                   @endif
                  </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            
          </div>
          
        </div>
        @endif
         @if(count($result)>0)
         <span class="label label-info">
               <strong>For Caste Info Change button we can modify caste info from SC to ST or vice versa also we can change existing caste certificate no. and document. For that case payment process will not effect.</strong>
                
            </span>
            <br/>
             <br/>
             <span class="label label-danger">
               <strong>For Change Caste button we can change caste from SC/ST to OTHERS or OTHERS to SC/ST.For that case payment process will be effect</strong>
                
            </span>
            @endif
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
    $('.sidebar-menu #lb-caste').addClass("active"); 
    $('.sidebar-menu #caste_search').addClass("active"); 
    // $('.sidebar-menu #accValTrFailed').addClass("active");
    //$('#submit_btn').removeAttr('disabled');
    $('#select_type').change(function(){
      var select_type = $('#select_type').val();
      if (select_type == 'B') {
        $('#search_text').text('Beneficiary ID');  
        $("#ben_id").attr("placeholder", 'Beneficiary ID');
      }
      else if(select_type == 'A') {
        $('#search_text').text('Application ID');  
        $("#ben_id").attr("placeholder", 'Application ID');
      }
      else if (select_type == 'S') {
         $('#search_text').text('Sasthyasathi Card No.');  
         $("#ben_id").attr("placeholder", 'Sasthyasathi Card No.');
      }
      else{
        $('#select_type').val('A');
        $('#search_text').text('Beneficiary ID');  
        $("#ben_id").attr("placeholder", 'Beneficiary ID');
      }
    });
  });
function validate(){
  var error_select_type =''; 
  var error_ben_id =''; 
    if($.trim($('#select_type').val()).length == 0)
      {
       error_select_type = 'Please Select';
       $('#error_select_type').text(error_select_type);
       $('#select_type').addClass('has-error');
      }
      else
      {
       error_select_type = '';
       $('#error_select_type').text(error_select_type);
       $('#select_type').removeClass('has-error');
      }

      if($.trim($('#ben_id').val()).length == 0)
      {
       error_ben_id = 'This field is required';
       $('#error_ben_id').text(error_ben_id);
       $('#ben_id').addClass('has-error');
      }
      else
      {
       error_ben_id = '';
       $('#error_ben_id').text(error_ben_id);
       $('#ben_id').removeClass('has-error');
      }
       if(error_select_type =='' && error_ben_id =='') { 
        return true;
      }
      else {
         return false;
      }
}


</script>
@stop