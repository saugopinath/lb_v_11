<style>
  .card-header-custom {
    font-size: 16px;
    background: linear-gradient(to right, #c9d6ff, #e2e2e2);
    font-weight: bold;
    font-style: italic;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }
</style>
@extends('layouts.app-template-datatable')
@section('content')
<!-- Main content -->
<div class="container-fluid">
  <div class="row">
    <div class="col-12 mt-4">
      

        <div class="tab-content" style="margin-top:16px;">
          <div class="tab-pane active" id="personal_details">
            <!-- Card with your design -->
            <div class="card" id="res_div">
              <div class="card-header card-header-custom">
                <h4 class="card-title mb-0"><b> Applications List</b></h4>
              </div>
              <div class="card-body" style="padding: 20px;">
                <!-- Alert Messages -->
                <div class="alert-section">
                  @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ $message }} with Application ID: {{$id}}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if ($message = Session::get('error') )
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if(count($errors) > 0)
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul>
                      @foreach($errors->all() as $error)
                  <li><strong> {{ $error }}</strong></li>
                  @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  <div class="alert print-error-msg" style="display:none;" id="errorDivMain">
                    <button type="button" class="close" aria-label="Close" onclick="closeError('errorDivMain')">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <ul></ul>
                  </div>
                </div>

                <!-- Search Section -->
              <form name="casteManagement" id="casteManagement" method="post" action="{{url('casteManagement')}}" onsubmit="return validat1e();" >
              {{ csrf_field() }}
                <div class="row mb-4">
                  <div class="col-md-12">
                    <div class="form-row align-items-end">
                    
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
                  </div>
                   <div class="form-group col-md-3 mb-0">
                      <input class="btn btn-success" type="submit" name="btnSubmit" value="Search">
                        
                    </div>
                </div>
              </div>
               </form>
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
             
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>



@endsection

@push('scripts')

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
  //console.log('ok');
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
        return false;
      }
      else {
         return false;
      }
}


</script>
@endpush