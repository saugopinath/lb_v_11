
<style type="text/css">
.requied{
  color:red;
}
.hasError {
  border: 2px solid red;
  border-radius: 4px;
}
#menu_role_mapping_panel{
  margin-left:250px;
  margin-top: 120px;
}
.modal-full {
    min-width: 80%;
  
}
#duty_add #btnaddrole{
  margin-top:25px;
  margin-left:10px;
}
.row{
  margin-right: 10px!important;
  margin-left: 60px!important;
  margin-top: 1%!important;
}
.form-control{
 width:95%!important;
}
#btnSearch{
  margin-top:20px;
  margin-left:10px;
}
#bulkSearch{
  margin-top:25px;
  margin-left:10px;
}
#btnaddrole{
  margin-top:25px;
  margin-left:10px;
}
#duty_add{
  margin-top:25px;
  margin-left:10px;
}
#excel-btn{
  margin-bottom:20px;
  display:none;

}
</style>
@extends('userDutymgmt.base')
@section('action-content')
  <section class="content">
    <div class="box">
      <div class="box-header">
        <div class="row">
            <div class="col-sm-8"></div>
        </div>
      </div>
      <div class="box-body">
        @if(count($errors) > 0)
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
        <div class="alert alert-danger alert-block">
          <ul>
          @foreach($errors->all() as $error)
          <li><strong> {{ $error }}</strong></li>
          @endforeach
          </ul>
        </div>
        @endif
        <div class="panel-group">
          <div class="panel panel-default">
           
            <div id="scheme_workflow" class="panel-collapse collapse show">
              <div class="panel-body" id="level_map"> <!-- hidden by default-->
                <div id="example2_wrapper" class="col-md-12 dataTables_wrapper form-inline dt-bootstrap js-report-form">
                <div class="alert print-error-msg" style="display:none" id="crud_msg_Crud">
                <button type="button" class="close"  aria-label="Close" onclick="closeError('crud_msg_Crud')"><span aria-hidden="true">&times;</span></button>
                <ul></ul></div>
                <div class="col-md-12 pull-right" id="addButton">

                  
                
                   @if($designation_id=='Admin' || $designation_id=='Verifier' || $designation_id=='Approver' )
                  <a class="btn btn-primary" href="javascript:void(0)" onClick="addUpdateUserForm(0)">Add User</a>
                   @endif
                   @if($designation_id=='Admin')
                    <!--<a class="btn btn-primary" href="javascript:void(0)" onClick="bulkDutyForm()">Bulk Duty Assignment</a>-->
                   @endif
                 
                 
                </div>
                <hr/>
                <hr/>
                <form id="Searchmodal" class="form-inline">
                  
                      <div class="form-group col-md-3">
                            <label class="control-label">Department </label>
                            
                                <select class="form-control" name="department_id_home" id="department_id_home">
                                <option value="">Choose Department</option>
                                @foreach ($departments as $department)
                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                    @endforeach
                                </select>
                                
                            
                        </div>
                 
                  
                 
                 
                      <div class="form-group col-md-3">
                             <label class="control-label">Mapping Level </label>
                           
                                <select class="form-control" name="stake_level_home" id="stake_level_home" >
                                <option value="">Select Mapping Level</option>
                                @foreach($user_levels as $stake)
                                   <option value="{{$stake->stake_code}}" >{{$stake->stake_name}}</option>
                                   @endforeach       
                                </select>
                                
                           
                        </div>
                 
                 
                    <div class="form-group col-md-3" id="designation_id_home_div">
                            <label class="control-label">Role </label>
                            
                                <select class="form-control" name="designation_id_home" id="designation_id_home" >
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                   <option value="{{$role->name}}" >{{$role->name}}</option>
                                   @endforeach     
                                </select>
                                <div id="designation_id_home_ajax"></div>
                           
                 
                  </div>
                  
                  
                  <button type="button" id="btnSearch" class="btn btn-primary">Search</button>
                 
                  </div>
                  
                 
                 </form>
                    
                  <div class="col-md-12 text-center" id="loaderdiv" hidden>
                    <img src="{{ asset('images/ZKZg.gif') }}" width="100px" height="100px"/>
                  </div>  
                  
               <div class="col-md-12 text-center" id="excel-btn">
                <a  class="btn btn-success pull-right" href="{{url('/downloadUser?type=excel')}}">Download All User Data as Excel </a>
                </div>
               
                    <table id="example" class="display" cellspacing="0" width="100%">
                      <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                      <thead>
                        <tr role="row">
                          
                         
                          <th width="25%" class="text-left">User Name</th>  
                           <th width="7%">Role</th>   
                          <th width="7%">Mobile Number</th>
                          
                          <th width="7%">Email</th>
                          <th width="6%" >Is Active</th>
                          <th width="15%" class="text-left">Action</th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                        
                        
                          <th width="25%" class="text-left">User Name</th> 
                              <th width="7%">Role</th> 
                          <th width="7%">Mobile Number</th> 
                          
                          <th width="7%">Email</th>
                          <th width="6%" >Is Active</th>
                          <th width="15%" class="text-left">Action</th>
                        </tr>
                      </tfoot>     
                    </table>  
                    <div class="row">
                      <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                          
                        </div>
                      </div>
                    </div>  
                  </div>
                </div>
              </div>
              <!-- <div class="panel-footer">Panel Footer</div> -->
            </div>
          </div>
      
        </div>        
      </div>
    </div>


<!--Add Update Level Modal -->
<div id="UserformModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-full">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><span class="crud-txt">Add User</span></h4>
      </div>
      <div class="modal-body">
        <div class="" ><img src="{{ asset('images/ZKZg.gif')}}" class="submit_loader" width="50px" height="50px"  ></div>
        <div class="alert print-error-msg" style="display:none" id="crud_msg_CrudModal">
        <button type="button" class="close"  aria-label="Close" onclick="closeError('crud_msg_CrudModal')"><span aria-hidden="true">&times;</span></button><ul></ul></div>
        <form id="userform" class="form-horizontal">
                        
            <input type="hidden" name="id" id="id" value="">
            <input type="hidden" name="must_role_adduser" id="must_role_adduser" value="1">  
             <div class="row">
                     <div class="form-group col-md-4">
                            <label for="firstname" class="control-label">First Name <span class="requied">*</span></label>

                            
                                <input id="firstname" type="text" class="form-control txtOnly" name="firstname" value=""  >

                               
                           
                      </div>
                        <div class="form-group col-md-4">
                            <label for="middlename" class="control-label">Middle Name</label>

                            
                                <input id="middlename" type="text" class="form-control txtOnly" name="middlename" value="" >

                               
                           
                        </div>
                        <div class="form-group col-md-4">
                            <label for="lastname" class="control-label">Last Name <span class="requied">*</span></label>

                           
                                <input id="lastname" type="text" class="form-control txtOnly" name="lastname" value="" >

                               
                           
                        </div>
              </div>          
              <div class="row">   
                        <div class="form-group col-md-4">
                            <label for="address" class="control-label">Office Address</label>

                           
                                <input id="address" type="text" class="form-control special-char" name="address" value="" >

                              
                            
                        </div>
                     
                       
                        
                               


                        

                        <div class="form-group col-md-4">
                            <label for="username" class="control-label">User Name <span class="requied">*</span></label>

                           
                                <input id="username" type="text" class="form-control" name="username" value=""  >

                                
                           
                        </div>

                        <div class="form-group col-md-4">
                            <label for="email" class="control-label">E-Mail Address <span class="requied">*</span></label>

                           
                                <input id="email" type="email" class="form-control" name="email" value="">

                               
                            
                        </div>
                     </div>
                     <div class="row">
                         <div class="form-group col-md-4">
                            <label for="mobile_no" class="control-label">Mobile Number <span class="requied">*</span></label>

                            
                                <input id="mobile_no" type="text" class="form-control NumOnly" name="mobile_no" value="" maxlength="10">

                               
                           
                        </div>
                        
                       
                       
                        <div class="form-group col-md-4" id="designation_id_adduser_div">
                            <label for="designation_id_adduser" class="control-label">Primary Role <span class="requied">*</span></label>

                           
                                <select id="designation_id_adduser"  class="form-control" name="designation_id_adduser" onChange="handleChangeUserType(this.value,'adduser')">
                                    <option value="">Choose Primary Role</option>
                                    @foreach($roles as $role)
                                   <option value="{{$role->name}}" >{{$role->name}}</option>
                                   @endforeach     
                                    
                                </select>
                                <div id="designation_id_adduser_ajax"></div>
                                
                            

                        </div>
                        
                        <div class="form-group col-md-4" id="department_id_adduser_div">
                            <label class="control-label">Department </label>
                           
                                <select class="form-control" name="department_id_adduser" id="department_id_adduser" onChange="handleChangeDepartment(this.value)">
                                <option value="">Choose Department</option>
                                @foreach ($departments as $department)
                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                    @endforeach
                                </select>
                                <div id="department_id_adduser_ajax"></div>
                           
                        </div>
                     <div class="form-group col-md-4">
                            <label for="password" class="control-label">Password</label>

                            
                                <input id="password" type="text" class="form-control" name="password" value="" >

                               
                           
                        </div>
                         <div class="form-group col-md-4">
                            <label for="password_confirmation" class="control-label">Confirm Password</label>

                            
                                <input id="password_confirmation" type="text" class="form-control" name="password_confirmation" value="" >

                               
                           
                        </div>
                       </div>
                        <div class="row">
                        <div class="form-group col-md-4" id="district_code_adduser_div" style="display:none;" >
                            <label for="district_code_adduser" class="control-label">Select District <span class="requied">*</span></label>

                            
                                <select id="district_code_adduser" class="form-control" name="district_code_adduser" onChange="handleChangedistrict(this.value,'adduser')">
                                    <option value="">Choose District</option>
                                    @foreach ($districts as $district)
                                  <option value="{{$district->district_code}}"> {{$district->district_name}}</option>
                                  @endforeach
                                </select>

                                <div id="district_code_adduser_ajax"></div>
                                
                           

                        </div>
                        </div>
                        <div class="row">
                        <div class="form-group col-md-4" id="subdiv_code_adduser_div" style="display:none;">
                            <label for="subdiv_code_adduser" class="control-label">Select Sub Division <span class="requied">*</span></label>

                            
                                <select id="subdiv_code_adduser"  class="form-control" name="subdiv_code_adduser" >
                                    <option value="">Choose Sub Division</option>
                                  
                                </select>
                             <div id="subdiv_code_adduser_ajax"></div>
                                
                                
                           

                        </div>
                        <div class="form-group col-md-4" id="is_urban_adduser_div" style="display:none;">
                            <label for="is_urban_adduser" class="control-label">Rural/Urban <span class="requied">*</span></label>

                            
                                <select id="is_urban_adduser"  class="form-control" name="is_urban_adduser" onChange="handleChangeisurban(this.value,'adduser')">
                                    <option value="">--Select Rural/Urban--</option>
                                    @foreach ($levels as $key=>$value)
                                  <option value="{{$key}}" > {{$value}}</option>
                                  @endforeach
                                </select>
                             <div id="is_urban_adduser_ajax"></div>
                                
                                
                          

                        </div>
                        <div class="form-group col-md-4" id="block_munc_corp_code_adduser_div" style="display:none;">
                            <label for="block_munc_corp_code_adduser" class="control-label">Select Block/Munc/Corp <span class="requied">*</span></label>

                           
                                <select id="block_munc_corp_code_adduser"  class="form-control" name="block_munc_corp_code_adduser" >
                                    <option value="">Choose Block/Munc/Corp</option>
                                   
                                </select>

                                <div id="block_munc_corp_code_adduser_ajax"></div>
                                
                           

                        </div>
                        </div>
      <div class="forAddUserOnly">            
     <hr/>
     <div class="row">
     <div class="form-group col-sm-3" id="role_id_user_div">
              <label for="l_role_id_user" class="">Role <span class="requied">*</span></label>
              <select  id="role_id_user" class="form-control" name="role_id_user" onChange="handleChangeroleDuty(this.value,'user')">
                <option value="">--Select Role--</option>
                @foreach($roles as $role)
                                   <option value="{{$role->name}}" >{{$role->name}}</option>
                @endforeach   
              </select>
              <div id="role_id_user_ajax"></div>
               
      </div>
      <div class="form-group col-sm-3" id="maping_level_user_div">
              <label for="l_maping_level_user" class="">Mapping Level <span class="requied">*</span></label>
              <select  id="maping_level_user" class="form-control" name="maping_level_user" onChange="handleChangemappingDuty(this.value,'user')">
                <option value="">--Select Mapping Level--</option>
                @foreach($user_levels as $stake)
                <option value="{{$stake->stake_code}}" >{{$stake->stake_name}}</option>
                @endforeach    
              </select>
              <div id="maping_level_user_ajax"></div>  
      </div>

      <div class="form-group col-sm-3" id="scheme_user_div">
              <label for="l_scheme_user" class="">Scheme <span class="requied">*</span></label>
              <select  id="scheme_user" class="form-control select2" name="scheme_user" style="width:200px;" multiple="multiple">
                <option value="">--Select Scheme--</option>
                @foreach ($schemes as $scheme)
                <option value="{{$scheme->id}}">{{$scheme->scheme_name}}</option>
                @endforeach
              </select>
               
      </div>
      <div class="form-group col-sm-3" id="district_code_user_div" style="display:none;">
              <label for="district_code_user" class="">District <span class="requied">*</span></label>
              <select  id="district_code_user" class="" name="" style="width:200px;" onChange="handleChangedistrict(this.value,'user')">
                <option value="">--Select District--</option>
                @foreach ($districts as $district)
                <option value="{{$district->district_code}}"> {{$district->district_name}}</option>
                @endforeach
              </select>
              <div id="district_code_user_ajax"></div>
      </div>
     </div>
    
     <div class="row">
     
      <div class="form-group col-sm-3" id="is_urban_user_div" style="display:none;">
              <label for="is_urban_user" class="">Rural/Urban <span class="requied">*</span></label>
              <select  id="is_urban_user" class="form-control" name="is_urban_user" onChange="handleChangeisurban(this.value,'user')">
                <option value="">--Select Rural/Urban--</option>
                @foreach ($levels as $key=>$value)
                <option value="{{$key}}" > {{$value}}</option>
                @endforeach
              </select>
              <div id="is_urban_user_ajax"></div>
      </div>
      <div class="form-group col-sm-3" id="subdiv_code_user_div" style="display:none;">
              <label for="subdiv_code_user" class="">Sub Division <span class="requied">*</span></label>
              <select  id="subdiv_code_user" class="form-control" name="subdiv_code_user" style="width:200px;">
                <option value="">--Sub Division--</option>
               
              </select>
              <div id="subdiv_code_user_ajax"></div>
      </div>
      <div class="form-group col-sm-3"  id="block_munc_corp_code_user_div" style="display:none;" style="width:200px;">
              <label for="block_munc_corp_code_user" class="">Block/Municipality/Corporation <span class="requied">*</span></label>
              <select  id="block_munc_corp_code_user" style="width:200px;" class="form-control" name="block_munc_corp_code_user">
                <option value="">--Block/Municipality/Corporation--</option>
               
              </select>
              <div id="block_munc_corp_code_user_ajax"></div>
      </div>
      <button id="btnaddrole"  class="btn btn-info"  onClick="return addRoletoDatatable();">Add Role</button>
      </div>
      
      <table id="myTable" class="display" cellspacing="0" width="100%">
          
            <thead>
              <tr role="row">
                <th>Scheme Code</th>
                <th>Role Code</th>
                <th>Mapping Level Code</th>
                <th>District Code</th>
                <th>Subdiv Code</th>
                <th>Urban Code</th>
                <th>Block_Munc_Corp Code</th>
                <th width="14%" class="text-left">Scheme Name</th>
                <th width="14%">Role</th>
                <th width="14%">Mapping Level</th>
                <th width="14%" >Rural/Urban</th>
                <th width="14%" >Location</th>
                <th width="14%" >Action</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Scheme Code</th>
                <th>Role Code</th>
                <th>Mapping Level Code</th>
                <th>District Code</th>
                <th>Subdiv Code</th>
                <th>Urban Code</th>
                <th>Block_Munc_Corp Code</th>
                <th width="14%" class="text-left">Scheme Name</th>
                <th width="14%">Role</th>
                <th width="14%">Mapping Level</th>
                <th width="14%" >Rural/Urban</th>
                <th width="14%" >Location</th>
                <th width="14%" >Action</th>
              </tr>
            </tfoot>     
          </table>
		      </div>  
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn-submit" >
        <span class="crud-txt">Add</span>
        </button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div id="BulkModal" class="modal fade" role="dialog">
 <div class="modal-dialog modal-full">
   <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><span class="crud-txt">Bulk Duty Assignment</span></h4>
      </div>
      <div class="modal-body">
        <div class="" ><img src="{{ asset('images/ZKZg.gif')}}" class="submit_loader" width="50px" height="50px"  ></div>
        <div class="alert print-error-msg" style="display:none" id="Bulk_CrudModal">
        <button type="button" class="close"  aria-label="Close" onclick="closeError('Bulk_CrudModal')"><span aria-hidden="true">&times;</span></button>
        <ul></ul></div>
        <form id="userform" class="form-horizontal">
          <input type="hidden" name="old_selected_userid[]" id="old_selected_userid" value=""/>
           <input type="hidden" name="new_selected_userid[]" id="new_selected_userid" value=""/>   
           <input type="hidden" name="old_notselected_userid[]" id="old_notselected_userid" value=""/> 
           <input type="hidden" name="new_notselected_userid[]" id="new_notselected_userid" value=""/>
          <div class="row">
            <div class="form-group col-md-3">
                            <label class="control-label">Scheme <span class="requied">*</span></label>
                           
                                <select class="form-control" name="scheme_bulk" id="scheme_bulk" onChange="handleChangeScheme(this.value,'bulk')">
                                <option value="">Choose Scheme</option>
                                @foreach ($schemes as $scheme)
                                <option value="{{$scheme->id}}">{{$scheme->scheme_name}}</option>
                                @endforeach
                                </select>
                                <div id="scheme_bulk_ajax"></div>
                            
            </div> 
           
            <div class="form-group col-md-3" id="role_id_bulk_div">
                            <label for="role_id_bulk" class="control-label">Role <span class="requied">*</span></label>
                           
                                <select id="role_id_bulk"  class="form-control" name="role_id_bulk" >
                                    <option value="">Choose Role</option>
                                </select>
                                <div id="role_id_bulk_ajax"></div>
                            
            </div>
            <div class="form-group col-md-3" id="stake_level_bulk_div" style="display:none;">
                            <label for="stake_level_bulk" class="control-label">Select Stake <span class="requied">*</span></label>
                           
                                <select id="stake_level_bulk"  class="form-control" name="stake_level_bulk" onchange="getStake(this.value)">
                                    <option value="">Choose Stake Level</option>
                                    @foreach($user_levels as $stake)
                                   <option value="{{$stake->stake_code}}" >{{$stake->stake_name}}</option>
                                   @endforeach                       
                                </select>
                                <div id="stake_level_ajax"></div>          
                           
            </div>
            
                  <button type="button" class="btn btn-primary" id="bulkSearch">Search</button>
                  
        </form>
        </div>
        <hr/>
        <div class="row" id="menu_role_mapping_panel" style="display:none;">
         <div class="dual-list list-left col-md-4">
                          <div class="col-md-12 text-left"><h4><cite><u>User List</u></cite></h4></div>
                          <div class="well text-right">
                              <div class="row">
                                  <div class="col-md-2">
                                      <div class="btn-group">
                                          <a class="btn btn-default selector" title="select all"><i class="glyphicon glyphicon-unchecked"></i></a>
                                      </div>
                                  </div>
                                  <div class="col-md-10">
                                      <div class="input-group">
                                          <span class="input-group-addon glyphicon glyphicon-search"></span>
                                          <input type="text" name="SearchDualList" class="form-control" placeholder="search" />
                                      </div>
                                  </div>
                              </div>
                              <ul class="list-group" id="not_selected_menu">
                             
                              </ul>
                          </div>
         </div>
         <div class="list-arrows col-md-1 text-center">
                          <button class="btn btn-default btn-sm move-left">
                              <span class="glyphicon glyphicon-chevron-left"></span>
                          </button>
              
                          <button class="btn btn-default btn-sm move-right">
                              <span class="glyphicon glyphicon-chevron-right"></span>
                          </button>
          </div>
          <div class="dual-list list-right col-md-4">
                          <div class="col-md-12 text-left"><h4><cite><u>Already Mapped User List</u></cite></h4></div>
                          <div class="well">
                              <div class="row">
                                  <div class="col-md-2">
                                      <div class="btn-group">
                                          <a class="btn btn-default selector" title="select all"><i class="glyphicon glyphicon-unchecked"></i></a>
                                      </div>
                                  </div>
                                  <div class="col-md-10">
                                      <div class="input-group">
                                          <input type="text" name="SearchDualList" class="form-control" placeholder="search" />
                                          <span class="input-group-addon glyphicon glyphicon-search"></span>
                                      </div>
                                  </div>
                              </div>
                              <ul class="list-group" id="selected_menu">
                                  <!-- List to be loaded using AJAX-->
                              </ul>
                          </div>
                      </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary"  id="btn-mapfinalize" style="display:none;">Finalize</button>
        
      </div>
   </div>
  </div>
</div>

<div id="dutyAssignmentModal" class="modal fade">
	<div class="modal-dialog modal-full">
		<div class="modal-content">
	  <form id="schemeform" class="form-horizontal" onsubmit="return submitformscheme()">
    <input type="hidden" name="user_id" id="user_id"/>
	  {{ csrf_field() }}  
				<div class="modal-header">						
					<h4 class="modal-title">List of duties of <b><span id="user_txt">Assddd</span></b></h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				</div>
				<div class="modal-body">					
                <div class="table-responsive">
		<div class="table-wrapper">
		
			<div class="alert print-error-msg"  style="display:none;" id="crud_msg_DutyModal">
      <button type="button" class="close"  aria-label="Close" onclick="closeError('crud_msg_DutyModal')"><span aria-hidden="true">&times;</span></button>
      <ul></ul></div>
		   <div class="" ><img src="{{ asset('images/ZKZg.gif')}}" class="submit_loader" width="50px" height="50px" style="display:none;"></div>
     
    <div class="row">
      <div class="form-group col-sm-3" id="role_id_duty_div">
              <label for="l_role_id_duty" class="">Role <span class="requied">*</span></label>
              <select  id="role_id_duty" class="form-control" name="role_id_duty" onChange="handleChangeroleDuty(this.value,'duty')">
                <option value="">--Select Role--</option>
                @foreach ($roles as $role)
               
                <option value="{{$role->name}}">{{$role->name}}</option>
                @endforeach
              </select>
              <div id="role_id_duty_ajax"></div>
               
      </div>
    
      <div class="form-group col-sm-3" id="maping_level_duty_div">
              <label for="l_maping_level_duty" class="">Mapping Level <span class="requied">*</span></label>
              <select  id="maping_level_duty" class="form-control" name="maping_level_duty" onChange="handleChangemappingDuty(this.value,'duty')">
                <option value="">--Select Mapping Level--</option>
                @foreach($user_levels as $stake)
                <option value="{{$stake->stake_code}}" >{{$stake->stake_name}}</option>
                @endforeach    
              </select>
              <div id="maping_level_duty_ajax"></div>  
      </div>
      <div class="form-group col-sm-3">
              <label for="l_scheme_duty" class="">Scheme <span class="requied">*</span></label>
              <select  style="width:200px;" id="scheme_duty" class="form-control select2" name="scheme_user" multiple="multiple">
                
                @foreach ($schemes as $scheme)
                <option value="{{$scheme->id}}">{{$scheme->scheme_name}}</option>
                @endforeach
              </select>
               
      </div>
      <div class="form-group col-sm-3" id="district_code_duty_div" style="display:none;">
              <label for="l_district_code_duty" class="">District <span class="requied">*</span></label>
              <select  id="district_code_duty" style="width:200px;" class="form-control" name="" onChange="handleChangedistrict(this.value,'duty')">
              <option value="">--Select District--</option>
                @foreach ($districts as $district)
                <option value="{{$district->district_code}}"> {{$district->district_name}}</option>
                @endforeach
              </select>
              <div id="district_code_duty_ajax"></div>
      </div>
      </div>
      <div class="row">
      <div class="form-group col-sm-3" id="subdiv_code_duty_div" style="display:none;">
              <label for="l_subdiv_code_duty" class="">Sub Division <span class="requied">*</span></label>
              <select  id="subdiv_code_duty" style="width:200px;" class="form-control" name="subdiv_code_duty" >
               
               
              </select>
              <div id="subdiv_code_duty_ajax"></div>
      </div>
      <div class="form-group col-sm-3" id="is_urban_duty_div" style="display:none;">
              <label for="l_is_urban_duty" class="">Rural/Urban <span class="requied">*</span></label>
              <select  id="is_urban_duty" style="width:200px;" class="form-control" name="is_urban_duty" onChange="handleChangeisurban(this.value,'duty')">
                <option value="">--Select Rural/Urban--</option>
                @foreach ($levels as $key=>$value)
                <option value="{{$key}}" > {{$value}}</option>
                @endforeach
              </select>
              <div id="is_urban_duty_ajax"></div>
      </div>
      
      <div class="form-group col-sm-3"  id="block_munc_corp_code_duty_div" style="display:none;">
              <label for="l_block_munc_corp_code_duty" class="">Block/Municipality/Corporation <span class="requied">*</span></label>
              <select  id="block_munc_corp_code_duty" style="width:200px;" class="form-control" name="block_munc_corp_code_duty" >
                
               
              </select>
              <div id="block_munc_corp_code_duty_ajax"></div>
      </div>
      
      <button class="btn btn-info" id="duty_add" onClick="return add_dutyassignment()">Add Role</a>
      </div>
      <br/>
      <table id="itemlistview" class="display" cellspacing="0" width="100%">
          
            <thead>
              <tr role="row">
                <th width="14%" class="text-left">Scheme Name</th>
                <th width="14%">Role</th>
                <th width="14%">Mapping Level</th>
                <th width="14%" >Rural/Urban</th>
                <th width="14%" >Location</th>
                <th width="14%" >Active/Inactive</th>
                
              </tr>
            </thead>
            <tfoot>
              <tr>
              <th width="14%" class="text-left">Scheme Name</th>
                <th width="14%">Role</th>
                <th width="14%">Mapping Level</th>
                <th width="14%" >Rural/Urban</th>
                <th width="14%" >Location</th>
                <th width="14%" >Active/Inactive</th>
                
              </tr>
            </tfoot>     
          </table>
		
		</div>
	</div>
				</div>
				<div class="modal-footer">
					<input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">
				</div>
			</form>
		</div>
	</div>
</div>
<script src="{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script>
<script src="{{ asset ("js/treeview.js") }}" type="text/javascript"></script>
<script src="{{ asset ("js/duellistUser.js") }}" type="text/javascript"></script>
<link href="{{ asset ("css/treeview.css") }}" rel="stylesheet">
<link href="{{ asset ("css/duellist.css") }}" rel="stylesheet">

<script type="text/javascript"> 
 
var table=""; 
var listItemtable = "";
var myTable = "";
var sessiontimeoutmessage='{{$sessiontimeoutmessage}}';
var base_url='{{ url('/') }}';
//console.log(sessiontimeoutmessage);
$(document).ready(function(){ 
  var table=""; 
  var listItemtable = "";
  var myTable = "";
  if(table!=null && table != ''){
    $('#example').DataTable().destroy();
    //alert(service_designation_id);
  }
  $("#excel-btn").hide();
    table=$('#example').DataTable( {
      "paging": true,
      "pageLength":10,
      "lengthMenu": [[10,20, 50, 80, 120, 150, 180, 500,1000, 2000], [10,20, 50, 80, 120, 150, 180, 500,1000, 2000]],
      "serverSide": true,
      "deferRender": true,
      "processing":true,
      "bRetrieve": true,
      "ordering":false,
      "searching": true,
      "language": {
        "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
      },
      "ajax": {
        url: "{{ url('userDutymanagement/Search') }}",
        type: "GET",
         data   : function( d ) {
          d.department_id= $('#department_id_home').val();
          d.mapping_level= $('#stake_level_home').val();
          d.designation_id= $('#designation_id_home').val();
       },
       error: function (jqXHR, textStatus, errorThrown) {
         alert(sessiontimeoutmessage);
         //location.reload();
         // window.location.href=base_url;
        }
       
      } ,
      "columns": [
        
       
        { "data": "username","defaultContent":"" },
        { "data": "designation_id"},
        { "data": "mobile_no"},
        { "data": "email"},
        { "data": "is_active"},
        { "data": "action"} 
      ],
    "initComplete":function( settings, json){
           //alert('done');
            $("#excel-btn").show();
            // call your function here
    }
      
    }); 
    $('#example_filter input')
     .off()
     .on('blur', function() {
               table.search( this.value ).draw();
            // $('#example').DataTable().column(0).search(this.value.trim(), false, false).draw();
     });
  $('.txtOnly').keypress(function (e) {
            var regex = new RegExp(/^[a-zA-Z\s]+$/);
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            else {
                e.preventDefault();
                return false;
            }
    });
  $(".NumOnly").keyup(function(event){
              $(this).val($(this).val().replace(/[^\d].+/, ""));
                  if ((event.which < 48 || event.which > 57)) {
                      event.preventDefault();
       }
  }); 
  $('.special-char').keyup(function()
  {
    var yourInput = $(this).val();
    re = /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
    var isSplChar = re.test(yourInput);
    if(isSplChar)
    {
      var no_spl_char = yourInput.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
      $(this).val(no_spl_char);
    }
  });
   $(".dataTables_scrollHeadInner").css({"width":"100%"});
 
   $(".table ").css({"width":"100%"}); 

  //fill_datatable();
$("#btnSearch").click(function(){
   
  table.ajax.reload()
    //fill_datatable(department_id,service_designation_id,stake_level,district_code,subdiv_code,block_munc_corp_code);
});

});

function reset(divid){
  $('#district_code_'+divid).val('');
  $('#subdiv_code_'+divid).val('');
  $('#block_munc_corp_code_'+divid).val('');
  $('#gp_ward_code_'+divid).val('');
}

function loadSubdiv(district_code,divId){
  $('#subdiv_code_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
  $('#subdiv_code_'+divId).find('option:not(:first)').remove();
  $.ajax({
      url: "api/blocksubdiv/1/"+district_code,
      type:'GET',
      success: function(datas) {
      if (!datas || datas.length === 0) {
           return;
        }
        var subdiv_code='{{Session()->get('subdiv_code')}}';
        //alert(subdiv_code);
      for (var  i = 0; i < datas.length; i++) {
        if(subdiv_code!='' && subdiv_code!=0){
         if(datas[i].id!=subdiv_code)
         continue;
        }
      $('#subdiv_code_'+divId).append($('<option>', {
        value: datas[i].id,
        text: datas[i].name,
        id: datas[i].id
       }));
      }
      if(datas.length==2){
              // alert('ok1');
                $('#subdiv_code_'+divId).prop("selectedIndex", 1);
                $('#subdiv_code_'+divId).trigger('change', [divId]);
              }
      $('#subdiv_code_'+divId+'_ajax').html('');
      },
      error: function (ex) {
        $('#subdiv_code_'+divId+'_ajax').html('');
        alert(sessiontimeoutmessage);
       // location.reload();
        window.location.href=base_url;
      }
    });
}

function loadBlock(district_code,divId){
  //alert(district_code);
  $('#block_munc_corp_code_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
  $('#block_munc_corp_code_'+divId).find('option:not(:first)').remove();
  $.ajax({
      url: "api/blocksubdiv/2/"+district_code,
      type:'GET',
      success: function(datas) {
      if (!datas || datas.length === 0) {
           return;
        }
        var block_code='{{Session()->get('block_munc_corp_code')}}';
      for (var  i = 0; i < datas.length; i++) {
        if(block_code!=''){
         if(datas[i].id!=block_code && block_code!=0)
         continue;
        }
      $('#block_munc_corp_code_'+divId).append($('<option>', {
        value: datas[i].id,
        text: datas[i].name,
        id: datas[i].id
       }));
      }
      if(divId=='duty' || divId=='user'){
        if($("#block_munc_corp_code_"+divId+" option[value='']").length)
        $("#block_munc_corp_code_"+divId+" option[value='']").remove();
      }
      if(datas.length==2){
              if(divId!='duty'){
                $('#block_munc_corp_code_'+divId).prop("selectedIndex", 1);
                $('#block_munc_corp_code_'+divId).trigger('change', [divId]);
              }
              }
      $('#block_munc_corp_code_'+divId+'_ajax').html('');
      },
      error: function (ex) {
        $('#block_munc_corp_code_'+divId+'_ajax').html('');
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
}
function loadMuncCorp(district_code,divId){
  $('#block_munc_corp_code_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
  $('#block_munc_corp_code_'+divId).find('option:not(:first)').remove();
  $.ajax({
      url: "api/localbody/1/"+district_code,
      type:'GET',
      success: function(datas) {
      if (!datas || datas.length === 0) {
           return;
        }
        var block_code='{{Session()->get('block_munc_corp_code')}}';
      for (var  i = 0; i < datas.length; i++) {
        if(block_code!=''){
         if(datas[i].id!=block_code && block_code!=0)
         continue;
        }
      $('#block_munc_corp_code_'+divId).append($('<option>', {
        value: datas[i].id,
        text: datas[i].name,
        id: datas[i].id
       }));
      }
      if(divId=='duty' || divId=='user'){
        if($("#block_munc_corp_code_"+divId+" option[value='']").length)
        $("#block_munc_corp_code_"+divId+" option[value='']").remove();
      }
      if(datas.length==2){
        if(divId!='duty'){
                $('#block_munc_corp_code_'+divId).prop("selectedIndex", 1);
                $('#block_munc_corp_code_'+divId).trigger('change', [divId]);
        }
              }
      $('#block_munc_corp_code_'+divId+'_ajax').html('');
      },
      error: function (ex) {
        $('#block_munc_corp_code_'+divId+'_ajax').html('');
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
}
function reloadDesignation(stake_level,divId)
{
  $('#designation_id_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
  $('#designation_id_'+divId).find('option:not(:first)').remove();
  $.ajax({
      url: "{{ url('userDutymanagement/reloadDesignationOnStake') }}",
      type:'GET',
      data:{
        stake_level: stake_level
        },
     
      success: function(datas) {
      //console.log(datas);
      if (!datas || datas.length === 0) {
           return;
        }
        $('#designation_id_'+divId).html('<option value="">Select Designation</option>');
      for (var  i = 0; i < datas.length; i++) {
      $('#designation_id_'+divId).append($('<option>', {
        value: datas[i].id,
        text: datas[i].name
       }));
      }
      
      $('#designation_id_'+divId+'_ajax').html('');
      },
      error: function (ex) {
        $('#designation_id_'+divId+'_ajax').html('');
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
}



  function addUpdateUserForm(id){
    var valid=1;
    $(".print-error-msg").hide();
    $("#must_role_adduser").val(1); 
    $("#firstname").val(''); 
    $("#lastname").val(''); 
    $("#middlename").val(''); 
    $("#address").val(''); 
    $("#username").val(''); 
    $("#email").val(''); 
    $("#mobile_no").val(''); 
    $("#maping_level_adduser").val(''); 
    $("#designation_id_adduser").val(''); 
    $("#department_id_adduser").val(''); 
    $("#usertypeid_adduser").val(''); 
    $("#district_code_adduser").val(''); 
    $("#subdiv_code_adduser").val(''); 
    $("#is_urban_adduser").val(''); 
    $("#block_munc_corp_code_adduser").val(''); 
    $("#scheme_user").empty();
    $("#role_id_user").val('');
    $("#maping_level_user").val('');
    $("#district_code_user").val('');
    $("#subdiv_code_user").val('');
    $("#block_munc_corp_code_user").val('');
    $("#district_code_user_div").hide();
    $("#district_code_user_div").hide();
    $("#is_urban_user_div").hide();
    $("#subdiv_code_user_div").hide();
    $("#block_munc_corp_code_user_div").hide();
    $(".submit_loader").hide();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
    if(id){
      
      $(".submit_loader").show();
    $.ajax({
      type: 'POST',  
      url: 'getUserInfo',
      data:{
        id: id
      },
      dataType: 'json',
      success: function (data) {
        //console.log(data);
        if (data.return_status==0) {
          $(".submit_loader").hide();
          printMsg(data.return_msg,'0','crud_msg_Crud');
        }else{
          $("#firstname").val(data.userarr.firstname); 
          $("#middlename").val(data.userarr.middlename); 
          $("#lastname").val(data.userarr.lastname); 
          $("#address").val(data.userarr.address); 
          $("#username").val(data.userarr.username); 
          $("#email").val(data.userarr.email); 
          $("#mobile_no").val(data.userarr.mobile_no); 
          $("#department_id_adduser").val(data.userarr.department_id); 
          $("#designation_id_adduser").val(data.userarr.designation_id);
          $("#user_id").val(data.userarr.id); 
          $(".forAddUserOnly").hide();
          $(".submit_loader").hide(); 
          $("#UserformModal").modal();

        }
        
      },
      error: function (ex) {
        $(".submit_loader").hide();
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
    }
    if(id){
      $(".forAddUserOnly").hide();
      var crud_txt='Update User';
    }
    else{
      $(".forAddUserOnly").show();
      var crud_txt='Add User';
    }
    $("#id").val(id);
    $(".crud-txt").text(crud_txt);
   
   
    if(!id){
       $("#UserformModal").modal();
    if ( $.fn.DataTable.isDataTable('#myTable') ) {
      $('#myTable').dataTable().fnClearTable();
    }

    $('#myTable').DataTable( {
      "paging": false,
      "searching": false,
      "ordering": false,
      "info": false,
      "destroy": true,
      "columnDefs": [
            {
              
                "targets": [ 0,1,2,3,4,5,6],
                "visible": false,
                "searchable": false
            },
            { "width": "100px", "targets": 7 },
            { "width": "40px", "targets": 8 },
            { "width": "10px", "targets": 9 },
            { "width": "40px", "targets": 10 },
            { "width": "10px", "targets": 11 },
            { "width": "20px", "targets": 12 }
        ]
    } );
    }
  }
  function bulkDutyForm(){
    $(".print-error-msg").hide();
    var scheme_id=1;
    $("#scheme_type_bulk").val(''); 
    $("#scheme_code_bulk").val(''); 
    $("#designation_id_bulk").val(''); 
    $("#role_bulk").val(''); 
    $("#stake_level_bulk").val(''); 
    $("#old_notselected_userid").val('');
    $("#old_selected_userid").val('');
    $("#new_notselected_userid").val('');
    $("#new_selected_userid").val('');
    $("#menu_role_mapping_panel").hide();
    $("#btn-mapfinalize").hide();
    $("#selected_menu").html("");
    $("#not_selected_menu").html("");
    $(".submit_loader").hide();
    
    
    $("#BulkModal").modal();

  }
  function bulkDutyForm1(){
    $(".submit_loader").hide();
    
    $("#BulkModal1").modal();

  }
  function addRemoveUserIds(type,selectedid){
    var notSelectedList1=$("#new_notselected_userid").val();
    var SelectedList1=$("#new_selected_userid").val();
    if(notSelectedList1=='')
    var notSelectedList=Array();
    else
    var notSelectedList=notSelectedList1.split(",");
    if(SelectedList1=='')
    var SelectedList=Array();
    else
    var SelectedList=SelectedList1.split(",");
   // console.log("pre not:"+notSelectedList);
   // console.log("pre sel:"+SelectedList);
    //console.log(selectedid);
    //var notSelectedList=[notSelectedList1];
    //var SelectedList=[SelectedList1];
    if(selectedid.length){
    for (var  i = 0; i < selectedid.length; i++) {
      if(type=='lr'){
        var index1 = notSelectedList.indexOf(""+selectedid[i]);
        if (index1 == -1) {
          notSelectedList.push(""+selectedid[i]);
        }
        SelectedList = SelectedList.filter(item => item !== ""+selectedid[i]);
     }
    else if(type=='rl'){
      var index1 = SelectedList.indexOf(""+selectedid[i]);
      if (index1 == -1) {
      SelectedList.push(""+selectedid[i]);
      }
      //console.log(selectedid[i]);
     // console.log(notSelectedList);
     notSelectedList = notSelectedList.filter(item => item !== ""+selectedid[i]);
        
    }
      
    }
    }
    //console.log("post not:"+notSelectedList);
    //console.log("post not:"+SelectedList);
    $("#new_notselected_userid").val($.unique(notSelectedList));
    $("#new_selected_userid").val($.unique(SelectedList));
   
  }
  
  $("#btn-submit").click(function(){
    //$('#btn-submit').prop('disabled', true);
    $firstname = $("#firstname").val();
    $middlename = $("#middlename").val();
    $lastname = $("#lastname").val();
    $address = $("#address").val();
    $username = $("#username").val();
    $email = $("#email").val();
    $mobile_no = $("#mobile_no").val();
    $department_id = $("#department_id_adduser option:selected").val();
    $designation_id = $('#designation_id_adduser option:selected').val();
    //alert($service_designation_id);
    //alert($designation_id);
   
    $id = $('#id').val();
   // alert($id);
    $must_role = $('#must_role_adduser').val();
    var schemeArray=Array();
    var roleArray=Array();
    var mappinglevelArray=Array();
    var districtArray=Array();
    var subdivArray=Array();
    var isurbanArray=Array();
    var blockmunccorpArray=Array();
    if($id==0){
    var dtTable = $('#myTable').DataTable(); 
    for (var i=0;i<dtTable.rows().count();i++) {
       schemeArray.push(dtTable.cells({ row: i, column: 0 }).data()[0]); 
       roleArray.push(dtTable.cells({ row: i, column: 1 }).data()[0]); 
       mappinglevelArray.push(dtTable.cells({ row: i, column: 2 }).data()[0]); 
       districtArray.push(dtTable.cells({ row: i, column: 3 }).data()[0]); 
       subdivArray.push(dtTable.cells({ row: i, column: 4 }).data()[0]); 
       isurbanArray.push(dtTable.cells({ row: i, column: 5 }).data()[0]); 
       blockmunccorpArray.push(dtTable.cells({ row: i, column: 6 }).data()[0]); 
       //console.log(dtTable.cells({ row: i, column: 6 }).data()[0]) ;    
    }  
    }
   // console.log(roleArray) ;
    //console.log(schemeArray) ;
   //console.log(isurbanArray) ;
   //console.log(blockmunccorpArray) ;
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
    $.ajax({
      url: "{{url('userDutymanagement/addUpdate') }}",
      type:'POST',
      dataType: "json",
      data: { 
        id:$id,
        must_role:$must_role,
        firstname:$firstname,
        middlename:$middlename,
        lastname:$lastname,
        address:$address,
        username:$username,
        email:$email,
        mobile_no:$mobile_no,
        department_id:$department_id,
        designation_id:$designation_id,
        schemeArray:schemeArray,
        roleArray:roleArray,
        mappinglevelArray:mappinglevelArray,
        districtArray:districtArray,
        subdivArray:subdivArray,
        isurbanArray:isurbanArray,
        blockmunccorpArray:blockmunccorpArray
      },
      success: function(data) {
        //console.log(data);
        if(data.return_status){
          $("#UserformModal").modal('hide');
          $("html, body").animate({ scrollTop: "0" }); 
          printMsg(data.return_msg,'1','crud_msg_Crud');
          table.ajax.reload(null,false);
          
        }else{
          $('#UserformModal').animate({ scrollTop: 0 }, 'slow');
          printMsg(data.return_msg,'0','crud_msg_CrudModal');
        }
        $('#btn-submit').prop('disabled', false);
      },
      error: function (ex) {
        $('#UserformModal').animate({ scrollTop: 0 }, 'slow');
        alert(sessiontimeoutmessage);
        //location.reload();
        $('#btn-submit').prop('disabled', false);
         window.location.href=base_url;
      }
    });
  });
  $("#bulkSearch").click(function(){
    $("#old_notselected_userid").val('');
    $("#old_selected_userid").val('');
    $("#new_notselected_userid").val('');
    $("#new_selected_userid").val('');
    $("#menu_role_mapping_panel").hide();
    $("#btn-mapfinalize").hide();
    $("#selected_menu").html("");
    $("#not_selected_menu").html("");
    $scheme_code_bulk = $('#scheme_bulk option:selected').val();
    $role_bulk = $('#role_id_bulk option:selected').val();
    $designation_id_bulk = $('#designation_id_bulk option:selected').val();
    var status1=status2=status3=status4=0;
  // alert($scheme_type_bulk);
    
    if($scheme_code_bulk==''){
      document.getElementById("scheme_bulk").style.borderColor = "red";
     status2=0;
    }
    else{
      document.getElementById("scheme_bulk").style.borderColor = "";
      status2=1;
    }
    if($designation_id_bulk==''){
      document.getElementById("designation_id_bulk").style.borderColor = "red";
     status3=0;
    }
    else{
      document.getElementById("designation_id_bulk").style.borderColor = "";
      status3=1;
    }
    if($role_bulk==''){
      document.getElementById("role_id_bulk").style.borderColor = "red";
     status4=0;
    }
    else{
      document.getElementById("role_id_bulk").style.borderColor = "";
      status4=1;
    }
    if(status2==1 && status3==1 && status4==1){
      $.ajax({
      url: "{{url('userDutymanagement/dutyAssignmentListWrap') }}",
      type:'GET',
      dataType: "json",
      data: { 
        scheme_code:$scheme_code_bulk,
        designation_id:$designation_id_bulk,
        role:$role_bulk
      },
      success: function(data) {
       // console.log(data);
        if(data.return_status){
          $("#not_selected_menu").html("");
          $("#selected_menu").html("");
         var userArrAll=data.retrun_arr['userArrAll'];
         var mappedUser=data.retrun_arr['mappedUser'];
         //$("#old_notselected_userid").val(userArrAll);
        // $("#old_selected_userid").val(mappedUser);
        var userArrAllIds=Array();
        var mappedUserIds=Array();
         for (var  i = 0; i < userArrAll.length; i++) {
          $("#not_selected_menu").append("<li class='list-group-item' value="+userArrAll[i]['id']+">"+userArrAll[i]['username']+"</li>");
          userArrAllIds.push(userArrAll[i]['id']);
         }
         for (var  i = 0; i < mappedUser.length; i++) {
           $("#selected_menu").append("<li class='list-group-item' value="+mappedUser[i]['id']+">"+mappedUser[i]['username']+"</li>");
           mappedUserIds.push(mappedUser[i]['id']);
          }
         $("#old_notselected_userid").val($.unique(userArrAllIds));
         $("#old_selected_userid").val($.unique(mappedUserIds));
         $("#new_notselected_userid").val($.unique(userArrAllIds));
         $("#new_selected_userid").val($.unique(mappedUserIds));
         //alert($("#old_selected_userid").val());
         $("#menu_role_mapping_panel").show();
         $("#btn-mapfinalize").show();
        
          //printMsg(data.return_msg,'1','crud_msg_Crud');
          
        }else{
          $('#UserformModal').animate({ scrollTop: 0 }, 'slow');
          printMsg(data.return_msg,'0','Bulk_CrudModal');
        }
      },
      error: function (ex) {
        //console.log(ex);
        alert(sessiontimeoutmessage);
       //location.reload();
        window.location.href=base_url;
      }
    });
    }
    
  });
  $("#btn-mapfinalize").click(function(){
    $('#btn-mapfinalize').prop('disabled', true);
    var notSelectedListnew=$("#new_notselected_userid").val();
    var SelectedListnew=$("#new_selected_userid").val();
    var notSelectedListold=$("#old_notselected_userid").val();
    var SelectedListold=$("#old_selected_userid").val();
    $scheme_code_bulk = $('#scheme_bulk option:selected').val();
    $role_bulk = $('#role_id_bulk option:selected').val();
    $designation_id_bulk = $('#designation_id_bulk option:selected').val();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
    $.ajax({
      url: "{{url('userDutymanagement/addDutyassigmentBulk') }}",
      type:'POST',
      dataType: "json",
      data: { 
        scheme_code:$scheme_code_bulk,
        designation_id:$designation_id_bulk,
        role:$role_bulk,
        notSelectedIdsOld:notSelectedListold,
        SelectedIdsOld:SelectedListold,
        notSelectedIdsNew:notSelectedListnew,
        SelectedIdsNew:SelectedListnew,
      },
      success: function(data) {
        //console.log(data);
        if(data.return_status){
          $("#BulkModal").modal('hide');
          table.ajax.reload(null,false);
          printMsg(data.return_msg,'1','crud_msg_Crud');
          
        }else{
          printMsg(data.return_msg,'0','Bulk_CrudModal');
        }
        $('#btn-mapfinalize').prop('disabled', false);
        $('#BulkModal').animate({ scrollTop: 0 }, 'slow');
      },
      error: function (ex) {
        //console.log(ex);
        alert(sessiontimeoutmessage);
        //location.reload();
        $('#btn-mapfinalize').prop('disabled', false);
         window.location.href=base_url;
      }
    });
  });
  
  function dutyAssignment(id,username){
    $("#scheme_duty").empty();
    $("#role_id_duty").val('');
    $("#maping_level_duty").val('');
    $("#district_code_duty").val('');
    $("#subdiv_code_duty").val('');
    $("#block_munc_corp_code_duty").val('');
    $("#district_code_duty_div").hide();
    $("#district_code_duty_div").hide();
    $("#is_urban_duty_div").hide();
    $("#subdiv_code_duty_div").hide();
    $("#block_munc_corp_code_duty_div").hide();
     $("#user_id").val('');
   
    $(".print-error-msg").hide();
    $("#user_txt").text(username);
    $('#scheme_type').val('');
    $('#scheme_code').find('option:not(:first)').remove();
    $('#role').find('option:not(:first)').remove();
   // alert(username);
    listView(id);
   // alert(username);
    $("#user_id").val(id);
    $("#dutyAssignmentModal").modal();
  }
  function handleChange(e){
	var id=e.id;
	var value=e.value;
	//alert(id);
	if(value!=''){
    document.getElementById(""+id).style.borderColor = "";
	//	$('#'+id).removeClass('hasError');
	}
	else{
		document.getElementById(""+id).style.borderColor = "red";
	}
  if(id=='scheme_type'){
    if(value!=''){
      $('#scheme_code_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
        url:'ajaxschemeChnageRequest/{id}',
        type:"get",
        data:'scheme_type_Id='+ value,
        dataType: 'json',
        contentType: "application/json; charset=utf-8",
        success:function(data){
        // console.log(data);
         $('#scheme_code').empty();
         $('#scheme_code').append("<option value=''> --Select Scheme -- </option>");
         $.each(data,function(index,schemeObj){
          $('#scheme_code').append('<option value="'+schemeObj.id+'">'+schemeObj.scheme_name+'</option>')
        });
        $('#scheme_code_ajax').html('');
        }
      });
    }
  }
  if(id=='scheme_code'){
    if(value!=''){
      $('#role_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getMaplevelfromScheme') }}",
      type:'GET',
      dataType: "json",
      data: { 
        scheme_code:value
      },
      success: function(data) {
        $('#role').empty();
        $('#role').append("<option value=''> --Select Role -- </option>");
        $.each(data,function(index,roleObj){
          $('#role').append('<option value="'+roleObj.id+'">'+roleObj.role_name+'('+roleObj.stack_level+')</option>')
        });
        $('#role_ajax').html('');
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
    }
  }
  
}
function listView(userId)
{
  

    if(listItemtable!=null && listItemtable != ''){
    $('#itemlistview').DataTable().destroy();
  //  alert(userId);
  }
 
  listItemtable = $('#itemlistview').DataTable( {
    dom: "Blfrtip",
    "paging": true,
    "pageLength":10,
    "lengthMenu": [[10,20, 50, 80, 120, 150, 180, 500,1000, 2000], [10,20, 50, 80, 120, 150, 180, 500,1000, 2000]],
    "serverSide": true,
    "deferRender": true,
    "processing":true,
    "bRetrieve": true,
    "searching": false,
    "ordering":false,
    "language": {
      "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
    },
    "ajax": {
      url: "{{url('userDutymanagement/getDutyAssignmentlist') }}",
      type: "GET",
      data:{
             user_id:userId
      },
      error: function (jqXHR, textStatus, errorThrown) {
         alert(sessiontimeoutmessage);
        // location.reload();
         window.location.href=base_url;
     }
    } ,
    "columns": [
      { "data": "scheme_name","defaultContent":""},
      { "data": "role_name","defaultContent":""},
      { "data": "mapping_level","defaultContent":""},
      { "data": "is_urban","defaultContent":""},
      { "data": "location","defaultContent":""},
      { "data": "is_active"}
    ],
    "initComplete":function( settings, json){
          // alert('ok');
            // call your function here
    }
  }); 
}

function add_dutyassignment(){
  $('#duty_add').prop('disabled', true);
  $user_id=$("#user_id").val();  
  //alert($user_id);
  var district_mantory=is_urban_mantory=subdiv_code_mantory=block_munc_corp_code_mantory=0;
  var status1=status2=status3=0;
  var status4=status5=status6=status7=1;
  $scheme_duty = $("#scheme_duty").val();
  //alert($scheme_duty);
  $role_id_duty = $('#role_id_duty option:selected').val();
  $maping_level_duty = $('#maping_level_duty option:selected').val();
  $district_code_duty=$('#district_code_duty').val();
  $is_urban_duty = $('#is_urban_duty option:selected').val();
  $subdiv_code_duty = $('#subdiv_code_duty').val();
  $block_munc_corp_code_duty = $('#block_munc_corp_code_duty').val();
  var location_text=urban_txt='';
  switch($maping_level_duty){
     case "State":
                  district_mantory=0;
                  is_urban_mantory=0;
                  subdiv_code_mantory=0;
                  block_munc_corp_code_mantory=0;
                  location_text= 'State';
                  urban_txt= 'NA';
                  break;
     case "District":
                  district_mantory=1;
                  is_urban_mantory=0;
                  subdiv_code_mantory=0;
                  block_munc_corp_code_mantory=0;
                  location_text=$("#district_code_duty option:selected").text();
                  urban_txt= 'NA';
                  break;
    case "Subdiv":
                  district_mantory=1;
                  is_urban_mantory=0;
                  subdiv_code_mantory=1;
                  block_munc_corp_code_mantory=0;
                  location_text=$("#subdiv_code_duty option:selected").text();
                  urban_txt= 'NA';
                  break; 
    case "Block":
                  district_mantory=1;
                  is_urban_mantory=1;
                  subdiv_code_mantory=0;
                  block_munc_corp_code_mantory=1;
                  location_text=$("#block_munc_corp_code_duty option:selected").text();
                  urban_txt= $("#is_urban_duty option:selected").text();
                  break;                       
  }
  //alert($district_code_duty);
  if($scheme_duty=='' || typeof($scheme_duty) === "undefined" || $scheme_duty===null){
      $('#scheme_duty').siblings(".select2-container").css('border', '1px solid red');
      status1=0;
    }
  else{
    $('#scheme_duty').siblings(".select2-container").css('border', 'none');
      status1=1;
    }
  if($role_id_duty=='' || typeof($role_id_duty) === "undefined" || $role_id_duty===null){
      document.getElementById("role_id_duty").style.borderColor = "red";
      status2=0;
    }
  else{
      document.getElementById("role_id_duty").style.borderColor = "";
      status2=1;
    }
  if($maping_level_duty=='' || typeof($maping_level_duty) === "undefined" || $maping_level_duty===null){
      document.getElementById("maping_level_duty").style.borderColor = "red";
      status3=0;
    }
   else{
      document.getElementById("maping_level_duty").style.borderColor = "";
      status3=1;
    }  
  if(district_mantory && ($district_code_duty=='' || typeof($district_code_duty) === "undefined" || $district_code_duty===null)){
     $('#district_code_duty').siblings(".select2-container").css('border', '1px solid red');
     // document.getElementById("district_code_duty").style.borderColor = "red";
      status4=0;
    }
    else{
     // document.getElementById("district_code_duty").style.borderColor = "";
     $('#district_code_duty').siblings(".select2-container").css('border', 'none');
      status4=1;
    } 
    if(subdiv_code_mantory && ($subdiv_code_duty=='' || typeof($subdiv_code_duty) === "undefined" || $subdiv_code_duty===null)){
      $('#subdiv_code_duty').siblings(".select2-container").css('border', '1px solid red');
      //document.getElementById("subdiv_code_duty").style.borderColor = "red";
      status5=0;
    }
    else{
      $('#subdiv_code_duty').siblings(".select2-container").css('border', 'none');
      //document.getElementById("subdiv_code_user").style.borderColor = "";
      status5=1;
    }   
  if(is_urban_mantory && ($is_urban_duty=='' || typeof($is_urban_duty) === "undefined" || $is_urban_duty===null)){
      document.getElementById("is_urban_duty").style.borderColor = "red";
      status6=0;
    }
    else{
      document.getElementById("is_urban_duty").style.borderColor = "";
      status6=1;
    } 
  if(block_munc_corp_code_mantory && ($block_munc_corp_code_duty=='' || typeof($block_munc_corp_code_duty) === "undefined" || $block_munc_corp_code_duty===null)){
      $('#block_munc_corp_code_duty').siblings(".select2-container").css('border', '1px solid red');
     // document.getElementById("block_munc_corp_code_duty").style.borderColor = "red";
      status7=0;
    }
    else{
      $('#block_munc_corp_code_duty').siblings(".select2-container").css('border', 'none');
      //document.getElementById("block_munc_corp_code_duty").style.borderColor = "";
      status7=1;
    }   
    //alert(status4);
    if(status1 && status2 && status3 && status4 && status5 && status6 && status7){
      //alert($district_code_duty);
      $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
      });
      $.ajax({
      url: "{{url('userDutymanagement/addDutyassigment') }}",
      type:'POST',
      dataType: "json",
      data: { 
        user_id:$user_id,
        scheme_code:$scheme_duty,
        role:$role_id_duty,
        mapping_level:$maping_level_duty,
        district_code:$district_code_duty,
        subdiv_code:$subdiv_code_duty,
        is_urban:$is_urban_duty,
        block_munc_corp_code:$block_munc_corp_code_duty
      },
      success: function(data) {
        //console.log(data);
        if(data.return_status){
          //$("#UserformModal").modal('hide');
          listItemtable.clear().draw();
          listItemtable.ajax.reload();
          $("#scheme_duty").empty();
          $("#role_id_duty").val('');
          $("#maping_level_duty").val('');
          $("#district_code_duty").val('');
          $("#subdiv_code_duty").val('');
          $("#block_munc_corp_code_duty").val('');
          $("#district_code_duty_div").hide();
          $("#district_code_duty_div").hide();
          $("#is_urban_duty_div").hide();
          $("#subdiv_code_duty_div").hide();
          $("#block_munc_corp_code_duty_div").hide();
          printMsg(data.return_msg,'1','crud_msg_DutyModal');

          
        }else{
          printMsg(data.return_msg,'0','crud_msg_DutyModal');
        }
        $('#dutyAssignmentModal').animate({ scrollTop: 0 }, 'slow');
        $('#duty_add').prop('disabled', false);
        
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
       // location.reload();
        $('#duty_add').prop('disabled', false);
         window.location.href=base_url;
      }
    });
    }
    else{
      //alert('not ok');
      $('#duty_add').prop('disabled', false);
    }
    return false;

}
function handleChangeScheme(scheme_code,divId){ 
    if(scheme_code!=''){
      $('#role_id_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getRolefromScheme') }}",
      type:'GET',
      dataType: "json",
      data: { 
        scheme_code:scheme_code
      },
      success: function(data) {
        if(data.return_status){
              $('#role_id_'+divId).empty();
              $('#role_id_'+divId).append("<option value=''> --Select Role -- </option>");
             
              $.each(data.return_msg,function(index,roleObj){
                $('#role_id_'+divId).append('<option value="'+roleObj.id+'">'+roleObj.role_name+'('+roleObj.stack_level+')</option>')
              });
              $('#role_id_'+divId+'_ajax').html('');
              if(data.return_msg.length==1){
               //alert('ok');
                $('#role_id_'+divId).prop("selectedIndex", 1);
                $('#role_id_'+divId).trigger('change', [divId]);
              }
      }
      else{
        alert('Error Occur .. Please try again');
      }
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
    }
}
function handleChangerole(role_code,divId){ 
 // alert(role_code);
    if(role_code!=''){ 
      $('#maping_level_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getMappLevelfromRole') }}",
      type:'GET',
      dataType: "json",
      data: { 
        role_code:role_code
      },
      success: function(data) {
        if(data.return_status){
        $('#maping_level_'+divId).empty();
        $('#maping_level_'+divId).append("<option value=''> --Select Role -- </option>");
        $.each(data.return_msg,function(index,Obj){
          $('#maping_level_'+divId).append('<option value="'+Obj.stake_code+'">'+Obj.stake_name+'</option>')
        });
        //console.log(data.return_msg);
        if(data.return_msg.length==1){
              // alert('ok1');
                $('#maping_level_'+divId).prop("selectedIndex", 1);
                $('#maping_level_'+divId).trigger('change', [divId]);
              }
        $('#maping_level_'+divId+'_ajax').html('');
      }
      else{
        alert('Error Occur .. Please try again');
      }
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
        //location.relad();
         window.location.href=base_url;
      }
    });
    }
}
function handleChangemapping(mapping_code,divId){ 
 // alert(mapping_code);
    if(mapping_code!=''){
      //$('#maping_level_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getOtherfromMapping') }}",
      type:'GET',
      dataType: "json",
      data: { 
        mapping_code:mapping_code
      },
      success: function(data) {
        if(data.return_status){
        var district_visible=data.return_msg['district_visible'];
        var isruralurbalvisible=data.return_msg['isruralurbalvisible'];
        var subdivvisible=data.return_msg['subdivvisible'];
        var blockmunccorpvisible=data.return_msg['blockmunccorpvisible'];
        if(district_visible==1){
         $('#district_code_'+divId+'_div').show();
        }
        else{
          $('#district_code_'+divId+'_div').hide();
        }
        if(isruralurbalvisible==1){
         $('#is_urban_'+divId+'_div').show();
        }
        else{
          $('#is_urban_'+divId+'_div').hide();
        }
        if(subdivvisible==1){
         $('#subdiv_code_'+divId+'_div').show();
        }
        else{
          $('#subdiv_code_'+divId+'_div').hide();
        }
        if(blockmunccorpvisible==1){
         $('#block_munc_corp_code_'+divId+'_div').show();
        }
        else{
          $('#block_munc_corp_code_'+divId+'_div').hide();
        }
        if(divId=='adduser')
        reloadDesignation(mapping_code,divId);
        reset(divId);
      }
      else{
        alert('Error Occur .. Please try again');
      }
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
       // location.reload();
        window.location.href=base_url;
      }
    });
    }
}
function handleChangeroleDuty(role_code,divId){ 
 // alert(role_code);
    if(role_code!=''){ 
      $('#maping_level_'+divId).val("");
    }
}
function handleChangeDepartment(department_id){ 
 // alert(role_code);
    if(department_id!=''){ 
      $('#role_id_user').val("");
      $('#maping_level_user').val("");
      $('#scheme_user').val(null).trigger('change');
    }
}
function handleChangemappingDuty(mapping_code,divId){ 
  var role=$('#role_id_'+divId).val();
  if(role==''){
   alert('Please select Role First');
   $('#role_id_'+divId).focus();
   return false;
  }
  if(mapping_code!='' && role!=''){
    $('#scheme_'+divId).val(null).trigger('change');
    var user_id=$("#user_id").val();
    if(user_id!=''){
    var department_id=$("#department_id_adduser").val();
    if(department_id=='' || typeof(department_id) === "undefined" || department_id===null){
      department_id='';
    }
    }
    else{
      var department_id='';
    }
    //alert(user_id);
  $('#scheme_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getSchemefromRole') }}",
      type:'GET',
      dataType: "json",
      data: { 
         user_id:user_id,
         role_name:role,
         stack_level:mapping_code,
         department_id:department_id
      },
      success: function(data) {
        if(data.return_status){
              $('#scheme_'+divId).empty();
              //$('#scheme_'+divId).append("<option value=''> --Select Scheme -- </option>");
             
              $.each(data.return_msg,function(index,roleObj){
                $('#scheme_'+divId).append('<option value="'+roleObj.id+'">'+roleObj.scheme_name+'</option>')
              });
              $('#scheme_'+divId+'_ajax').html('');
             
              
      }
      else{
        alert('Error Occur .. Please try again');
      }
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
    }

    if(mapping_code!=''  && role!=''){
      //$('#maping_level_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getOtherfromMapping') }}",
      type:'GET',
      dataType: "json",
      data: { 
        mapping_code:mapping_code
      },
      success: function(data) {
        if(data.return_status){
        var district_visible=data.return_msg['district_visible'];
        var isruralurbalvisible=data.return_msg['isruralurbalvisible'];
        var subdivvisible=data.return_msg['subdivvisible'];
        var blockmunccorpvisible=data.return_msg['blockmunccorpvisible'];
        if(district_visible==1){
         $('#district_code_'+divId+'_div').show();
        }
        else{
          $('#district_code_'+divId+'_div').hide();
        }
        if(isruralurbalvisible==1){
         $('#is_urban_'+divId+'_div').show();
        }
        else{
          $('#is_urban_'+divId+'_div').hide();
        }
        if(subdivvisible==1){
         $('#subdiv_code_'+divId+'_div').show();
        }
        else{
          $('#subdiv_code_'+divId+'_div').hide();
        }
        if(blockmunccorpvisible==1){
         $('#block_munc_corp_code_'+divId+'_div').show();
        }
        else{
          $('#block_munc_corp_code_'+divId+'_div').hide();
        }
        switch(mapping_code){
          case 'State':
           
                        $('#district_code_'+divId).removeAttr('multiple');
                        $('#subdiv_code_'+divId).removeAttr('multiple');
                        $('#block_munc_corp_code_'+divId).removeAttr('multiple');
                        break;
          case 'District':
                        $('#district_code_'+divId).attr('multiple','multiple');
                        if($("#district_code_"+divId+" option[value='']").length)
                        $("#district_code_"+divId+" option[value='']").remove();
                        $('#district_code_'+divId).select2();
                        $('#subdiv_code_'+divId).removeAttr('multiple');
                        $('#block_munc_corp_code_'+divId).removeAttr('multiple');
                        $("#district_code_"+divId).select2("val", "0");
                        break; 
          case 'Subdiv':
                        $('#subdiv_code_'+divId).attr('multiple','multiple');
                        if($("#subdiv_code_"+divId+" option[value='']").length)
                        $("#subdiv_code_"+divId+" option[value='']").remove();
                        $('#subdiv_code_'+divId).select2();
                        if($("#district_code_"+divId+" option[value='']").length == 0)
                          $("#district_code_"+divId).append("<option value=''>--Please Select--</option>");
                        $('#district_code_'+divId).select2('destroy');
                        $('#district_code_'+divId).removeAttr('multiple');
                        $("#district_code_"+divId).val('');
                        $('#block_munc_corp_code_'+divId).select2('destroy');
                        $('#block_munc_corp_code_'+divId).removeAttr('multiple');
                        break;  
          case 'Block':
                        $('#block_munc_corp_code_'+divId).attr('multiple','multiple');
                        if($("#block_munc_corp_code_"+divId+" option[value='']").length)
                        $("#block_munc_corp_code_"+divId+" option[value='']").remove();
                        $('#block_munc_corp_code_'+divId).select2();
                        if($("#district_code_"+divId+" option[value='']").length == 0)
                          $("#district_code_"+divId).append("<option value=''>--Please Select--</option>");
                        $('#district_code_'+divId).select2('destroy');
                        $('#district_code_'+divId).removeAttr('multiple');
                        $("#district_code_"+divId).val('');
                        $('#subdiv_code_'+divId).select2('destroy');
                        $('#subdiv_code_'+divId).removeAttr('multiple');
                        break;                                       

        }
        
       // reset(divId);
      }
      else{
        alert('Error Occur .. Please try again');
      }
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
    }
}
function handleChangedistrict(district_code,divId){ 
   // alert(divId);
    if(district_code!=''){
      if(divId=='user'){
      var stake_level=$("#maping_level_user").val();
      var is_urban=$("#is_urban_user").val();
      }
      else if(divId=='adduser'){
        var stake_level=$("#maping_level_adduser").val();
        var is_urban=$("#is_urban_adduser").val();
      }
      else if(divId=='duty'){
        var stake_level=$("#maping_level_duty").val();
        var is_urban=$("#is_urban_duty").val();
      }
        if(stake_level=='Subdiv'){
          //alert('hi');
        loadSubdiv(district_code,divId);
        }
        else if(stake_level=='Block'){
        
          $("#is_urban_"+divId).val('');
          $("#block_munc_corp_code_"+divId).html('<option value="">Select Block/Munc/Corp</option>');
         // $("#is_urban_"+divId).val('');

      }
      else if(stake_level=='Gram Panchayet'){
          //loadSubdiv(district_code);
          loadMuncCorp(district_code,divId);
      }
    }
 }
function handleChangeisurban(isurbancode,divId){
  //alert(isurbancode);
  if(divId=='user'){
      var district_code=$("#district_code_user").val();
     
      }
  else if(divId=='adduser'){
        var district_code=$("#district_code_adduser").val();
      }
      else if(divId=='duty'){
        var district_code=$("#district_code_duty").val();
      }
      if(district_code!=''){
  if(isurbancode==1)
          loadMuncCorp(district_code,divId);
   else
   
         
          loadBlock(district_code,divId); 
      }
   
}
function handleChangeDesignation(designation_id,divId){
// alert(role_code);
    if(designation_id!=''){
      $('#department_id_'+divId+'_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getdeptvisiblefromSDesignation') }}",
      type:'GET',
      dataType: "json",
      data: { 
        service_designation_id:designation_id
      },
      success: function(data) {
        if(data.return_status){
        if(data.return_msg=='yes'){
          $('#department_id_'+divId+'_div').show();
          $('#department_id_'+divId+'_ajax').html('');
        }
        else{
          $('#department_id_'+divId+'_div').hide();
        }
       
      }
      else{
        alert('Error Occur .. Please try again');
      }
      },
      error: function (ex) {
       // console.log(ex);
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
    }
}
function handleChangeUserType(designation_id,divId){
 //alert(designation_id);
    if(designation_id!=''){
      $('#designation_id_adduser_ajax').html('<img src="{{ asset('images/ZKZg.gif') }}" width="50px" height="50px"/>');
      $.ajax({
      url: "{{url('userDutymanagement/getRolemantatoryfromUsertype') }}",
      type:'GET',
      dataType: "json",
      data: { 
        designation_id:designation_id
      },
      success: function(data) {
        //console.log(data);
        if(data.return_status){
        if(data.return_msg=='yes'){
          $('#must_role_'+divId).val(1);
          
        }
        else{
          $('#must_role_'+divId).val(0);
        }
        $('#designation_id_adduser_ajax').html('');
      }
      else{
        alert('Error Occur .. Please try again');
      }
      },
      error: function (ex) {
        $('#designation_id_adduser_ajax').html('');
        alert(sessiontimeoutmessage);
        //location.reload();
         window.location.href=base_url;
      }
    });
    }
}
function addRoletoDatatable(){
  var must_role_adduser=$("#must_role_adduser").val();
  var district_mantory=is_urban_mantory=subdiv_code_mantory=block_munc_corp_code_mantory=0;
  var status1=status2=status3=0;
  var status4=status5=status6=status7=1;
  $scheme_user = $("#scheme_user").val();
  $role_id_user = $('#role_id_user option:selected').val();
  $maping_level_user = $('#maping_level_user option:selected').val();
  $district_code_user = $('#district_code_user').val();
  $is_urban_user = $('#is_urban_user option:selected').val();
  $subdiv_code_user = $('#subdiv_code_user').val();
  $block_munc_corp_code_user = $('#block_munc_corp_code_user').val();
  var location_text=urban_txt='';
  var location_arr=Array();
  switch($maping_level_user){
     case "State":
                  district_mantory=0;
                  is_urban_mantory=0;
                  subdiv_code_mantory=0;
                  block_munc_corp_code_mantory=0;
                  location_text= 'State';
                  urban_txt= 'NA';
                  break;
     case "District":
                  district_mantory=1;
                  is_urban_mantory=0;
                  subdiv_code_mantory=0;
                  block_munc_corp_code_mantory=0;
                  location_text="district_code_user";
                  urban_txt= 'NA';
                  break;
    case "Subdiv":
                  district_mantory=1;
                  is_urban_mantory=0;
                  subdiv_code_mantory=1;
                  block_munc_corp_code_mantory=0;
                  location_text="subdiv_code_user";
                 // location_text=$("#subdiv_code_user option:selected").text();
                  urban_txt= 'NA';
                  break; 
    case "Block":
                  district_mantory=1;
                  is_urban_mantory=1;
                  subdiv_code_mantory=0;
                  block_munc_corp_code_mantory=1;
                  location_text="block_munc_corp_code_user";
                  urban_txt= $("#is_urban_user option:selected").text();
                  break;                       
  }
  if($scheme_user=='' || typeof($scheme_user) === "undefined" || $scheme_user===null){
      $('#scheme_user').siblings(".select2-container").css('border', '1px solid red');
      status1=0;
    }
  else{
    $('#scheme_user').siblings(".select2-container").css('border', 'none');
      status1=1;
    }
  if($role_id_user=='' || typeof($role_id_user) === "undefined" || $role_id_user===null){
      document.getElementById("role_id_user").style.borderColor = "red";
      status2=0;
    }
  else{
      document.getElementById("role_id_user").style.borderColor = "";
      status2=1;
    }
  if($maping_level_user=='' || typeof($maping_level_user) === "undefined" || $maping_level_user===null){
      document.getElementById("maping_level_user").style.borderColor = "red";
      status3=0;
    }
   else{
      document.getElementById("maping_level_user").style.borderColor = "";
      status3=1;
    }  
  if(district_mantory && ($district_code_user=='' || typeof($district_code_user) === "undefined" || $district_code_user===null)){
     $('#district_code_user').siblings(".select2-container").css('border', '1px solid red');
     // document.getElementById("district_code_user").style.borderColor = "red";
      status4=0;
    }
    else{
     // document.getElementById("district_code_user").style.borderColor = "";
     $('#district_code_user').siblings(".select2-container").css('border', 'none');
      status4=1;
    } 
    if(subdiv_code_mantory && ($subdiv_code_user=='' || typeof($subdiv_code_user) === "undefined" || $subdiv_code_user===null)){
      $('#subdiv_code_user').siblings(".select2-container").css('border', '1px solid red');
      //document.getElementById("subdiv_code_user").style.borderColor = "red";
      status5=0;
    }
    else{
      $('#subdiv_code_user').siblings(".select2-container").css('border', 'none');
      //document.getElementById("subdiv_code_user").style.borderColor = "";
      status5=1;
    }   
  if(is_urban_mantory && ($is_urban_user=='' || typeof($is_urban_user) === "undefined" || $is_urban_user===null)){
      document.getElementById("is_urban_user").style.borderColor = "red";
      status6=0;
    }
    else{
      document.getElementById("is_urban_user").style.borderColor = "";
      status6=1;
    } 
  if(block_munc_corp_code_mantory && ($block_munc_corp_code_user=='' || typeof($block_munc_corp_code_user) === "undefined" || $block_munc_corp_code_user===null)){
      $('#block_munc_corp_code_user').siblings(".select2-container").css('border', '1px solid red');
     // document.getElementById("block_munc_corp_code_user").style.borderColor = "red";
      status7=0;
    }
    else{
      $('#block_munc_corp_code_user').siblings(".select2-container").css('border', 'none');
      //document.getElementById("block_munc_corp_code_user").style.borderColor = "";
      status7=1;
    }   
    if(status1 && status2 && status3 && status4 && status5 && status6 && status7){
      var maping_level_user_text= $("#maping_level_user option:selected").text();
      var role_id_user_text= $("#role_id_user option:selected").text();
      var is_urban_user_text= $("#is_urban_user option:selected").text();
      var rowCount = $('#myTable tbody tr').length;
      var duplicate=0;
      if($scheme_user!='' || typeof($scheme_user) !== "undefined" || $scheme_user!==null){
       
      $('#scheme_user > option:selected').each(function() {
        var scheme_text=$(this).text();
        var res = $(this).val();
        var scheme_val=res;
        var role_val=$role_id_user;
        $('#'+location_text+' > option:selected').each(function() {
          var loc_text=$(this).text();
          var loc_name=$(this).val();
          var my_district_code='';
          var my_subdiv_code='';
          var my_block_munc_corp_code='';
          switch($maping_level_user){
               case "State":
                           my_district_code=null;
                           my_subdiv_code=null;
                           my_block_munc_corp_code=null;
                           break;
              case "District":
                           my_district_code=loc_name;
                           my_subdiv_code=null;
                           my_block_munc_corp_code=null;
                           break;  
              case "Subdiv":
                           my_district_code=$district_code_user;
                           my_subdiv_code=loc_name;
                           my_block_munc_corp_code=null;
                           break;               
              case "Block":
                           my_district_code=$district_code_user;
                           my_subdiv_code=null;
                           my_block_munc_corp_code=loc_name;
                           break;                          
          }
         
          $("table#myTable > tbody > tr").each(function(row, tr) {
          var  scheme_name= $('td:eq(0)',this).text();
          if(scheme_name==scheme_text){
          //  alert('hh');
            var  role_name= $('td:eq(1)',this).text();
            var  mapping_name= $('td:eq(2)',this).text();
            var  rural_name= $('td:eq(3)',this).text();
            var  location_name= $('td:eq(4)',this).text();
            //console.log('Role Dt'+role_name);
           // console.log('Role FR'+role_id_user_text);
            //console.log('Mapping Dt'+mapping_name);
            //console.log('Mapping FR'+maping_level_user_text);
            //console.log('Urban Dt'+rural_name);
           // console.log('Urban FR'+urban_txt);
            //console.log('Location Dt'+location_name);
            //console.log('Location FR'+loc_name);
            if(role_name==role_id_user_text && mapping_name==maping_level_user_text && urban_txt==rural_name && loc_text==location_name ){
           // alert('Duplicate Data');
            duplicate=1;
            return false;
            }
            else{
              duplicate=0;
            }
          }
         });
         //alert(duplicate);
         if( duplicate==0){
          var markup1 = '<tr id="tr_'+rowCount+'"><td>'+scheme_val+'</td><td>'+role_val+'</td><td>'+$maping_level_user+'</td><td>'+my_district_code+'</td><td>'+my_subdiv_code+'</td><td>'+$is_urban_user+'</td><td>'+my_block_munc_corp_code+'</td><td>'+scheme_text+'</td><td>'+role_id_user_text+'</td><td>'+maping_level_user_text+'</td><td>'+urban_txt+'</td><td>'+loc_text+'</td><td><a href="javascript:void(0)" class="delete" data-toggle="modal" onclick="return deletefromDataTable('+rowCount+')">Delete</a></td></tr>';
          $("#myTable tbody").append(markup1);
          var table = $('#myTable').DataTable();
          var rowNode=table.row.add([scheme_val,role_val,$maping_level_user,my_district_code,my_subdiv_code,$is_urban_user,my_block_munc_corp_code,scheme_text,role_id_user_text, maping_level_user_text, urban_txt, loc_text,'<a href="javascript:void(0)" class="delete" data-toggle="modal" onclick="deletefromDataTable('+rowCount+')">Delete</a>' ]).draw().node();
          $(rowNode).attr("id", "tr_"+rowCount);
         }
         else{
          // alert('Duplicate Data');
          return false; 
         }
        });
        
        });

     }
     if( duplicate==1){
          alert('Duplicate Data');
        }
    }
   else{
    //console.log('validation not done');
   }
   return false;
}
function deletefromDataTable(id){
var confirm_y_n=confirm('Are You Sure?');
if(confirm_y_n==true){
   $("#myTable tbody #tr_"+id).remove();
  var rowCount = $('#myTable tbody tr').length;
  if(rowCount==0){
					var markup1 = '<tr id="notfound"><td colspan="6">No Mapping Found</td></tr>';
					$("#myTable tbody").html(markup1);
					//$("#removeBtn").hide();
	}
  var mytable = $('#myTable').DataTable();
  mytable.row("#tr_"+id).remove().draw();
}
}
function getStakeBulk(designation_id){
  if(designation_id==7){
   $("#stake_level_bulk_div").show();
  }
  else{
    $("#stake_level_bulk_div").hide();
  }

}

function deleteUser(id,username)
  {
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
    $.ajax({
      url: "{{url('userDutymanagement/deleteUser') }}",
      type:'POST',
      dataType: 'json',
      data: {
        id:id,
        username:username
      },
      success: function(data) {
        // console.log(data);
        if(data.return_status){
          //$("#UserformModal").modal('hide');
         // table.clear().draw();
          table.ajax.reload(null,false);
          printMsg(data.return_msg,'1','crud_msg_Crud');
          
        }else{
          printMsg(data.return_msg,'0','crud_msg_Crud');
        }
      },
      error: function (ex) {
        alert(sessiontimeoutmessage);
       // location.reload();
        window.location.href=base_url;
      }
    });
  }
  function deleteDuty(id)
  {
    var confirm_y_n=confirm('Are You Sure');
    if(confirm_y_n==true){
    $('#deleteDuty_'+id).prop('disabled', true);
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
    $.ajax({
      url: "{{url('userDutymanagement/deleteDuty') }}",
      type:'POST',
      dataType: 'json',
      data: {
        id:id
      },
      success: function(data) {
        // console.log(data);
        if(data.return_status){
          //$("#UserformModal").modal('hide');
          listItemtable.clear().draw();
          listItemtable.ajax.reload();
          printMsg(data.return_msg,'1','crud_msg_DutyModal');
          
        }else{
          printMsg(data.return_msg,'0','crud_msg_DutyModal');
        }
        $('#dutyAssignmentModal').animate({ scrollTop: 0 }, 'slow');
        $('#deleteDuty_'+id).prop('disabled', false);
      },
      error: function (ex) {
        alert(sessiontimeoutmessage);
        //location.reload();
        $('#dutyAssignmentModal').animate({ scrollTop: 0 }, 'slow');
        $('#deleteDuty_'+id).prop('disabled', false);
         window.location.href=base_url;
      }
    });
    }
    return false;
  }
function toggleActivate(id)
  {
    var confirm_y_n=confirm('Are You Sure');
    if(confirm_y_n==true){
    $('#toggleActivate_'+id).prop('disabled', true);
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
    $.ajax({
      url: "{{url('userDutymanagement/toggleActivate') }}",
      type:'POST',
      dataType: 'json',
      data: {
        id:id
      },
      success: function(data) {
        
        if(data.return_status){
          //$("#UserformModal").modal('hide');
         // table.clear().draw();
          //table.ajax.reload(null, false);
          $('#example').DataTable().ajax.reload(null, false);
          printMsg(data.return_msg,'1','crud_msg_Crud');
          
          
        }else{
          printMsg(data.return_msg,'0','crud_msg_Crud');
        }
        $('#toggleActivate_'+id).prop('disabled', false);
        $("html, body").animate({ scrollTop: "0" }); 
      },
      error: function (ex) {
        alert(sessiontimeoutmessage);
        //location.reload();
        $('#toggleActivate_'+id).prop('disabled', false);
        $("html, body").animate({ scrollTop: "0" }); 
         window.location.href=base_url;
      }
    });
    }
    return false;
  }
  function toggleActivateDutyYn(id)
  {
    var confirm_y_n=confirm('Are You Sure');
    if(confirm_y_n==true){
      $('#toggleActivateDutyYn'+id).prop('disabled', true);
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': "{{csrf_token()}}"
      }
    });
    $.ajax({
      url: "{{url('userDutymanagement/toggleActivateDuty') }}",
      type:'POST',
      dataType: 'json',
      data: {
        id:id
      },
      success: function(data) {
      // alert(data.return_status);
        if(data.return_status){
          //$("#UserformModal").modal('hide');
          //$('#itemlistview').DataTable().clear().draw();
          //listItemtable.ajax.reload(null, false);
          $('#itemlistview').DataTable().ajax.reload(null, false);
          printMsg(data.return_msg,'1','crud_msg_DutyModal');
          
        }else{
          printMsg(data.return_msg,'0','crud_msg_DutyModal');
        }
        $('#dutyAssignmentModal').animate({ scrollTop: 0 }, 'slow');
        $('#toggleActivateDutyYn'+id).prop('disabled', false);
      },
      error: function (ex) {
        alert(sessiontimeoutmessage);
        //location.reload();
        $('#dutyAssignmentModal').animate({ scrollTop: 0 }, 'slow');
        $('#toggleActivateDutyYn'+id).prop('disabled', false);
         window.location.href=base_url;
      }
    });
    }
    return false;
  }
  function closeError(divId){
   $('#'+divId).hide();
  }
  function printMsg (msg,msgtype,divid) {
            $("#"+divid).find("ul").html('');
            $("#"+divid).css('display','block');
			if(msgtype=='0'){
				//alert('error');
				$("#"+divid).removeClass('alert-success');
				//$('.print-error-msg').removeClass('alert-warning');
				$("#"+divid).addClass('alert-warning');
			}
			else{
				$("#"+divid).removeClass('alert-warning');
				$("#"+divid).addClass('alert-success');
			}
			if(Array.isArray(msg)){
            $.each( msg, function( key, value ) {
                $("#"+divid).find("ul").append('<li>'+value+'</li>');
            });
			}
			else{
				$("#"+divid).find("ul").append('<li>'+msg+'</li>');
			}
  }
 
</script>
</section>
@endsection