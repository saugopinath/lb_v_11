<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\District;
use App\Models\Scheme;
use Redirect;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use DateTime;
use Config;
use App\Models\Configduty;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataSourceCommon;

use App\Models\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use App\Models\RejectRevertReason;
use App\AadharDuplicateTrail;
use App\Models\SubDistrict;
use App\Models\Taluka;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Storage;
use App\Models\SchemeDocMap;
use File;
use App\Models\BankDetails;
use App\Models\UrbanBody;
use App\Models\Ward;
use App\Models\GP;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\AcceptRejectInfo;
use App\Traits\TraitAadharUpdate;
use phpDocumentor\Reflection\PseudoTypes\True_;
use App\Helpers\DupCheck;

class wBPdsChangeController extends Controller
{
   use TraitAadharUpdate;
    public function __construct()
    {

         $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
        $this->ben_status = -97;
        $this->doc_type_id = 6;
        
    }
    
   
    // function selectschemeOp(Request $request)
    // {
    //     $this->middleware('auth');
    //     $roleArray = $request->session()->get('role');
    //     $designation_id = Auth::user()->designation_id;
    //     $userId = Auth::user()->id;
    //     $type = $request->type;
    //     if(!in_array($type,array(1,2))){
    //       return redirect("/")->with('danger', 'Input Not Valid');
    //     }
    //     $scheme_list = DB::select(DB::raw("select id,scheme_name from m_scheme where id IN (2,10,11) and  id in (select scheme_id from duty_assignement where user_id=" . $userId . " and is_active=1) and is_active=1 order by scheme_name"));
    //     //dd($scheme_list);
    //     return view(
    //         'wbpds.selectSchemeOp',
    //         [
    //             'scheme_list' => $scheme_list,
    //             'designation_id' => $designation_id,
    //             'type' => $type
    //         ]
    //     );
    // }
    public function namemismatchdlist(Request $request)
    {
      // dd($request->all());
      $this->middleware('auth');
      $designation_id = Auth::user()->designation_id;
     //dd($designation_id);
      $user_id = Auth::user()->id;
  
      $duty_obj = Configduty::where('user_id', $user_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $type = $request->type;
      $faulty_type=$request->faulty_type;
      if(!in_array($type,array(1,2))){
        return redirect("/")->with('danger', 'Input Not Valid');
      }
      if($type==1){
        $type_des='Beneficiary with name Validation Failed from WBPDS';
      }
      else if($type==2){
        $type_des='Beneficiary with Name Validation Failed from WBPDS';

      }
      
     
      $district_code = $duty_obj->district_code;
     
      $urban_bodys = collect([]);
      $gps = collect([]);
      $district_list_obj = collect([]);
      
      if ($duty_obj->mapping_level == "Subdiv") {
        $created_by_local_body_code = $duty_obj->urban_body_code;
        $is_rural = 1;
        $verifier_type = 'Subdiv';
        $gps = collect([]);
        $urban_body_code = $duty_obj->urban_body_code;
        $urban_bodys = UrbanBody::where('sub_district_code', $urban_body_code)->select('urban_body_code', 'urban_body_name')->get();
        $urban_body_codes = [];
        $i = 0;
        foreach ($urban_bodys as $urban_body) {
  
          $urban_body_codes[$i] = $urban_body->urban_body_code;
          $i++;
        }
      }
      if ($duty_obj->mapping_level == "Block") {
        $created_by_local_body_code = $duty_obj->taluka_code;
        $is_rural = 2;
        $verifier_type = 'Block';
        $urban_bodys = collect([]);
        $taluka_code = $duty_obj->taluka_code;
        $gps = GP::where('block_code', $taluka_code)->select('gram_panchyat_code', 'gram_panchyat_name')->get();
      }
      if ($duty_obj->mapping_level == "District") {
        $district_list_obj = District::get();
        $verifier_type = 'District';
        $is_rural = NULL;
        $created_by_local_body_code = NULL;
      }
      
      if (request()->ajax()) {

       // dd($duty_obj->mapping_level);
        $limit = $request->input('length');
        $offset = $request->input('start');
        $application_type=$request->application_type;
        $process_type=$request->process_type;
        // dump($application_type);dd($process_type);
        $faulty_type=$request->faulty_type;
       //dd($faulty_type);

       if($application_type==4)
       {

        if($faulty_type==0)
        {
          $query = DB::table('lb_scheme.ben_reject_details' . ' AS bp')
          ->where('bp.created_by_dist_code', $district_code)->where('bp.is_faulty', FALSE);

        }
        else{
          $query = DB::table('lb_scheme.ben_reject_details' . ' AS bp')
          ->where('bp.created_by_dist_code', $district_code)->where('bp.is_faulty', TRUE);
        }
          
       }else{

          if($faulty_type==0)
          {
              $query = DB::table('lb_scheme.ben_personal_details' . ' AS bp')
              ->join('lb_scheme.ben_bank_details' . ' AS bb', 'bb.beneficiary_id', '=', 'bp.beneficiary_id')
              ->join('lb_scheme.ben_contact_details' . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
              ->where('bp.created_by_dist_code', $district_code)->where('bp.next_level_role_id',0);

          }
          if($faulty_type==1)
          {
              $query = DB::table('lb_scheme.faulty_ben_personal_details' . ' AS bp')
              ->join('lb_scheme.faulty_ben_bank_details' . ' AS bb', 'bb.beneficiary_id', '=', 'bp.beneficiary_id')
              ->join('lb_scheme.faulty_ben_contact_details' . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
              ->where('bp.created_by_dist_code', $district_code)->where('bp.next_level_role_id',0);
          }
          // dd($query);
       }
         
         
          if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
           // dd(123);
            $query = $query->where('bp.created_by_local_body_code', $created_by_local_body_code);
            
            
            if (!empty($application_type)) {
             // dd($application_type);
              if($application_type==1)
               $query = $query->whereNull('bp.next_level_role_id_aadhar_validation');
              
              if($application_type==2)
               $query = $query->where('bp.next_level_role_id_aadhar_validation', 1);
              
              if($application_type==3)
               $query = $query->where('bp.next_level_role_id_aadhar_validation', 0);
              
               if($application_type==4)
               $query = $query->where('bp.next_level_role_id_aadhar_validation', -57);
              
            }
          }
        if ($type == 1) {
          if($application_type!=3){
            $query = $query->where('bp.acc_validated_aadhar', -1);
           
          }
         
        }
   
        if ($type == 2) {
          if($application_type!=3){
          $query = $query->where('bp.acc_validated_aadhar', -2);
          
          }
        }
        if ($duty_obj->mapping_level == "Subdiv") {
         // dd(123);
          if (!empty($request->block_ulb_code)) {
            $query = $query->where('bc.block_ulb_code', $request->block_ulb_code);
           
          }
        }
        if (!empty($request->gp_ward_code)) {
          //dd(123);
          $query = $query->where('bc.gp_ward_code', $request->gp_ward_code);
          
        }

        if (!empty($request->created_by_local_body_code)) {
          //dd($request->created_by_local_body_code);
          $query = $query->where('bp.created_by_local_body_code', $request->created_by_local_body_code);
         
        }
        // dd($query);
        if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
          //dd($application_type);
          if ($application_type!='') {
            if($application_type==1)
             $query = $query->where('bp.next_level_role_id_aadhar_validation', 1);
            
            if($application_type==3)
             $query = $query->where('bp.next_level_role_id_aadhar_validation', 0);
            
             if($application_type==4)
             $query = $query->where('bp.process_acc_validated_aadhar', -57);
             
          }
          //dd($process_type);
          if (!empty($process_type)) {
              if($process_type==1)
               $query = $query->where('bp.failed_process_type_aadhaar',1);
             
              if($process_type==2)
              $query = $query->where('bp.failed_process_type_aadhaar',2);
             
              if($process_type==3)
              $query = $query->where('bp.failed_process_type_aadhaar',3);
             
             
          }
        }
        // dump($process_type);dump($application_type);dd($type);
        // dd($query->toSql());
        $serachvalue = $request->search['value'];
       
        if (empty($serachvalue)) {

          $totalRecords = $query->count();

          if($application_type==4)
          {
            // echo 2;die;
            $data = $query->orderBy('bp.beneficiary_id', 'ASC')->offset($offset)->limit($limit)->get([
              'bp.beneficiary_id', 'bp.created_by_dist_code', 'bp.dob', 'bp.application_id','bp.bank_name','bp.bank_code','bp.bank_ifsc',
               'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender', 'bp.block_ulb_code','bp.block_ulb_name','bp.gp_ward_code','bp.gp_ward_name',
                'bp.next_level_role_id_aadhar_validation', 'bp.next_level_role_id','bp.acc_validated_aadhar',
              'bp.process_acc_validated_aadhar','bp.mobile_no','bp.wbpds_name_as_in_aadhar','bp.wbpds_name_as_in_aadhar_sr','bp.jnmp_marked'
            ]);


          }else{
            // echo 1;die;
          $data = $query->orderBy('bp.beneficiary_id', 'ASC')->offset($offset)->limit($limit)->get([
            'bp.beneficiary_id', 'bp.created_by_dist_code', 'bp.dob', 'bp.application_id','bb.bank_name','bb.bank_code','bb.bank_ifsc',
             'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender', 'bc.block_ulb_code','bc.block_ulb_name','bc.gp_ward_code','bc.gp_ward_name',
             'bp.next_level_role_id', 'bp.next_level_role_id_aadhar_validation', 
            'bp.process_acc_validated_aadhar','bp.mobile_no', 'bp.acc_validated_aadhar','bp.wbpds_name_as_in_aadhar','bp.wbpds_name_as_in_aadhar_sr','bp.jnmp_marked'
          ]);
           
        }
        
      
          $filterRecords = count($data);
          // dd(count($data));
        } else {
          if (is_numeric($serachvalue)) {
           

            if($application_type==4)
            {
              $ben_id = $serachvalue;
           
            $query = $query->where(function ($query1) use ($ben_id, $serachvalue) {
              $query1->where('bp.beneficiary_id', $ben_id)
                ->orWhere('bp.bank_code', $serachvalue);
            });
            $totalRecords = $query->count();
              $data = $query->orderBy('bp.beneficiary_id', 'ASC')->offset($offset)->limit($limit)->get([
              'bp.beneficiary_id', 'bp.created_by_dist_code', 'bp.dob', 'bp.application_id','bp.bank_name','bp.bank_code','bp.bank_ifsc',
              'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender', 'bp.block_ulb_code','bp.block_ulb_name','bp.gp_ward_code','bp.gp_ward_name',
              'bp.next_level_role_id_aadhar_validation', 'bp.next_level_role_id','bp.acc_validated_aadhar',
              'bp.process_acc_validated_aadhar','bp.mobile_no','bp.wbpds_name_as_in_aadhar','bp.wbpds_name_as_in_aadhar_sr','bp.jnmp_marked'
              ]);
            }else{

              $ben_id = $serachvalue;
            $query = $query->where(function ($query1) use ($ben_id, $serachvalue) {
              $query1->where('bp.beneficiary_id', $ben_id)
                ->orWhere('bb.bank_code', $serachvalue);
            });
            $totalRecords = $query->count();
              $data = $query->orderBy('bp.beneficiary_id', 'ASC')->offset($offset)->limit($limit)->get(
              [
              'bp.beneficiary_id', 'bp.created_by_dist_code', 'bp.dob', 'bp.application_id','bb.bank_name','bb.bank_code','bb.bank_ifsc',
              'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender', 'bc.block_ulb_code','bc.block_ulb_name','bc.gp_ward_code','bc.gp_ward_name',
              'bp.next_level_role_id', 'bp.next_level_role_id_aadhar_validation', 
              'bp.process_acc_validated_aadhar','bp.mobile_no', 'bp.acc_validated_aadhar','bp.wbpds_name_as_in_aadhar','bp.wbpds_name_as_in_aadhar_sr','bp.jnmp_marked'
              ]);
            }
          } else {

            if($application_type==4)
            {
              $query = $query->where(function ($query1) use ($serachvalue) {
              $query1->where('bp.ben_fname', 'like', $serachvalue . '%')
              ->orWhere('bp.block_ulb_name', 'like', $serachvalue . '%')
              ->orWhere('bp.gp_ward_name', 'like', $serachvalue . '%')
              ->orWhere('bp.bank_ifsc', 'like', $serachvalue . '%');
              });
              $totalRecords = $query->count();
              $data = $query->orderBy('bp.beneficiary_id', 'ASC')->offset($offset)->limit($limit)->get([
              'bp.beneficiary_id', 'bp.created_by_dist_code', 'bp.dob', 'bp.application_id','bp.bank_name','bp.bank_code','bp.bank_ifsc',
              'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender', 'bp.block_ulb_code','bp.block_ulb_name','bp.gp_ward_code','bp.gp_ward_name',
              'bp.next_level_role_id_aadhar_validation', 'bp.next_level_role_id','bp.acc_validated_aadhar',
              'bp.process_acc_validated_aadhar','bp.mobile_no','bp.wbpds_name_as_in_aadhar','bp.wbpds_name_as_in_aadhar_sr','bp.jnmp_marked'
              ]);
            }else{

              $query = $query->where(function ($query1) use ($serachvalue) {
              $query1->where('bp.ben_fname', 'like', $serachvalue . '%')
              ->orWhere('bc.block_ulb_name', 'like', $serachvalue . '%')
              ->orWhere('bc.gp_ward_name', 'like', $serachvalue . '%')
              ->orWhere('bb.bank_ifsc', 'like', $serachvalue . '%');
              });
              $totalRecords = $query->count();
              $data = $query->orderBy('bp.beneficiary_id', 'ASC')->offset($offset)->limit($limit)->get(
              [
              'bp.beneficiary_id', 'bp.created_by_dist_code', 'bp.dob', 'bp.application_id','bb.bank_name','bb.bank_code','bb.bank_ifsc',
              'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender', 'bc.block_ulb_code','bc.block_ulb_name','bc.gp_ward_code','bc.gp_ward_name',
              'bp.next_level_role_id', 'bp.next_level_role_id_aadhar_validation', 
              'bp.process_acc_validated_aadhar','bp.mobile_no', 'bp.acc_validated_aadhar','bp.wbpds_name_as_in_aadhar','bp.wbpds_name_as_in_aadhar_sr','bp.jnmp_marked'
              ]
              );
            }
          }
          $filterRecords = count($data);

          $faulty_type=$request->faulty_type;
          // dd($faulty_type);
          //dd($filterRecords);
        }
        return datatables()->of($data)->setTotalRecords($totalRecords)
          ->setFilteredRecords($filterRecords)
          ->skipPaging()
          ->addColumn('application_id', function ($data) {
            
  
            $app_id = $data->application_id ;
  
            return $app_id;
          })->addColumn('view', function ($data) use ($designation_id,$type,$faulty_type) {
            
           
            if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
                if($data->process_acc_validated_aadhar==-57){
                 $action ='Rejected';
                }
                else if($data->acc_validated_aadhar==-2 && $data->next_level_role_id_aadhar_validation==1){
                  $action ='Approval Pending';
                 }
                //  else if( $data->next_level_role_id_aadhar_validation==0){
                //   $action ='Approved';
                //  }
                 else if($data->acc_validated_aadhar==-2 && is_null($data->next_level_role_id_aadhar_validation)){
                  if($data->jnmp_marked == 1){
                    $action ='Mark due to Janma Mrityu Thathya';
                  }else{
                   $action = '<a href="Viewpdsnamemismatch?id=' . $data->beneficiary_id  . '&type=' . $type . '&faulty_type=' . $faulty_type . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';
                  }
                 }
                  else if( $data->acc_validated_aadhar==-2 && $data->next_level_role_id_aadhar_validation==0){
                  $action ='Approved';
                 }
                 else{
                  $action ='';
                }
              
            }
            if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
              
              if($data->next_level_role_id_aadhar_validation==-57){
                $action ='Rejected';
               }
               else if($data->next_level_role_id_aadhar_validation==0){
                 $action ='Approved';
                }
                else if($data->acc_validated_aadhar==-2 && $data->next_level_role_id_aadhar_validation==1){
                  if($data->jnmp_marked == 1){
                    $action ='Mark due to Janma Mrityu Thathya';
                  }else{
                    $action = '<a href="Viewpdsnamemismatch?id=' . $data->beneficiary_id . '&type=' . $type .'&faulty_type='.$faulty_type. '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';
                  }
                }
                else{
                  $action ='';
                }
              
            
             }
   
            return $action;
          })->addColumn('check', function ($data) use ($designation_id) {
            if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
              if ($data->next_level_role_id_aadhar_validation == 1) {
                return '<input type="checkbox" name="approvalcheck[]" onClick="controlCheckBox()" value="' . $data->beneficiary_id . '">';
              } else
                return '';
            } else {
              return '';
            }
          })
          ->addColumn('beneficiary_id', function ($data) {
          //dd($data->beneficiary_id);
          
            $beneficiary_id=$data->beneficiary_id;
            return $beneficiary_id;
          })->addColumn('application_id', function ($data) {
  
            // $app_id = $data->created_by_dist_code . substr('0' . $data->scheme_id, -$scheme_length) . substr('0000000' . $data->id, -$id_length);
            $app_id = $data->application_id ;
  
            return $app_id;
          })
          ->addColumn('name', function ($data) {
            return $data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname;
          })->addColumn('name_as_in_aadhar', function ($data) {
            if (!empty($data->wbpds_name_as_in_aadhar_sr)) {
              $av_name_response = trim($data->wbpds_name_as_in_aadhar_sr);
            } else {
              $av_name_response = '';
            }
            return $av_name_response;
           
          })->addColumn('bank_ifsc', function ($data) {

            //$bank_ifsc=$data->bank_ifsc;
            if (!empty($data->bank_ifsc)) {
              $bank_ifsc =trim($data->bank_ifsc);
            } else {
              $bank_ifsc = '';
            }
            return $bank_ifsc;
          })->addColumn('bank_code', function ($data) {
            if (!empty($data->bank_code)) {
              $bank_code =trim($data->bank_code);
            } else {
              $bank_code = '';
            }
            
            return $bank_code;
          })->addColumn('mobile_no', function ($data) {
            if (!empty($data->mobile_no)) {
              $ben_mobile_no =trim($data->mobile_no);
            } else {
              $ben_mobile_no = '';
            }
            return $ben_mobile_no;
          })->addColumn('failed_type', function ($data) {
            $failed_type = '';
            if (!empty($data->acc_validated_aadhar)) {
             if($data->acc_validated_aadhar=='-2'){
                $failed_type = 'Name';
             } else if($data->acc_validated_aadhar=='-1'){
                $failed_type = 'Aadhaar';
             }
            } else {
              $failed_type = '';
            }
            return $failed_type;
          })
          ->rawColumns(['view', 'id', 'name', 'mask_aadhaar_no', 'bank_ifsc','bank_code','bank_ifsc','bank_ifsc', 'check'])
          ->make(true);
      }
  
      return view(
        'wbpds.linelistingmismatch',
        [
          'designation_id' => $designation_id,
          'verifier_type' => $verifier_type,
          'created_by_local_body_code' => $created_by_local_body_code,
          'is_rural' => $is_rural,
          'gps' => $gps,
          'urban_bodys' => $urban_bodys,
          'gps' => $gps,
          'district_code' => $district_code,
          'type' => $type,
          'faulty_type'=> $faulty_type,
          'type_des' => $type_des
        ]
      );
    }
    public function ViewMismatchName(Request $request)
    {
    // dd($request->all());


      $this->middleware('auth');
      $designation_id = Auth::user()->designation_id;
      $user_id = Auth::user()->id;
      $scheme_id = $request->scheme_id;
      $faulty_type = $request->faulty_type;
      
      if (empty($request->id)) {
        return redirect("/")->with('danger', 'Beneficiary ID Not Found');
      }
      if (!is_numeric($request->id)) {
        return redirect("/")->with('danger', 'Beneficiary ID Not Valid');
      }
      
      $duty_obj = Configduty::where('user_id', $user_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $type = $request->type;
      if(!in_array($type,array(1,2))){
        return redirect("/")->with('danger', 'Input Not Valid');
      }
      if($type==1){
        $type_des='Beneficiary with Account Validation Failed';
      }
      else if($type==2){
        $type_des='Beneficiary with Name Validation Failed WBPDS';

      }
      $district_code = $duty_obj->district_code;
      
if($faulty_type==1)
{
  $query = DB::table('lb_scheme.faulty_ben_personal_details' . ' AS bp')
  ->join('lb_scheme.faulty_ben_bank_details' . ' AS bb', 'bb.beneficiary_id', '=', 'bp.beneficiary_id')
  ->join('lb_scheme.faulty_ben_contact_details' . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
  ->join('lb_scheme.ben_aadhar_details' . ' AS a', 'a.beneficiary_id', '=', 'bp.beneficiary_id')
  ->where('bp.created_by_dist_code', $district_code) ->where('bp.beneficiary_id', $request->id);

}
else{
      $query = DB::table('lb_scheme.ben_personal_details' . ' AS bp')
      ->join('lb_scheme.ben_bank_details' . ' AS bb', 'bb.beneficiary_id', '=', 'bp.beneficiary_id')
        ->join('lb_scheme.ben_contact_details' . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
         ->join('lb_scheme.ben_aadhar_details' . ' AS a', 'a.beneficiary_id', '=', 'bp.beneficiary_id')
      
        ->where('bp.created_by_dist_code', $district_code)
        ->where('bp.beneficiary_id', $request->id);
}
      if ($type == 1) {
          $query = $query->where('bp.acc_validated_aadhar', -1);
      }
      if ($type == 2) {
          $query = $query->where('bp.acc_validated_aadhar', -2);
      }
  
      $row = $query->first();
      // dd($row);
      if (empty($row)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      if($row->jnmp_marked == 1){
        return redirect("/")->with('danger', 'Mark due to JNMP');
      }
      $app_id = $row->application_id;
      $row->application_id = $app_id;
      
      // if(($designation_id=='Approver') && (!empty($row->new_aadhar_encoded)))
      // {
        //$ad='703581597069';
       // $a= Crypt::encryptString($ad);
       // dd($a);
       
      if(($designation_id=='Approver' ||  $designation_id == 'Delegated Approver') && ($row->failed_process_type_aadhaar==2))
      {
      //  dd(123); 
        $encoded_aadhar_new=$row->new_aadhar_encoded;
        $encoded_aadhar_old=$row->encoded_aadhar;
        $decrypt_aadhar_old= Crypt::decryptString($encoded_aadhar_old);
      //dd(123);
      $decrypt_aadhar_new= Crypt::decryptString($encoded_aadhar_new);
        // dd($decrypt_aadhar_old);
      }
      else{



        $encoded_aadhar_old=$row->encoded_aadhar;
        $encoded_aadhar_new='';
        $decrypt_aadhar_new= '';
        $decrypt_aadhar_old= Crypt::decryptString($encoded_aadhar_old);

       
      }
   


      if(($designation_id=='Approver' ||  $designation_id == 'Delegated Approver') && ($row->failed_process_type_aadhaar==2))
      {
        
        $scheme_id = $this->scheme_id;
        $source_type = $this->source_type;
        $roleArray = $request->session()->get('role');
        $is_active = 0;
        $getModelFunc = new getModelFunc();
        $DraftPfImageTable = new DataSourceCommon;
      // $Table = $getModelFunc->getTable($district_code, $this->source_type, 11, 1);
      $Table = 'lb_scheme.ben_attach_documents_temp';

        $DraftPfImageTable->setConnection('pgsql_encwrite');
        $DraftPfImageTable->setTable('' . $Table);

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        $condition['is_active'] = TRUE;
       
      $profileImagedata = $DraftPfImageTable->where('application_id', $app_id)->where($condition)->first();
            if (empty($profileImagedata->application_id)) {

          
                $return_text = 'Parameter Not Valid';
                return redirect("/")->with('error',  $return_text);
            }

          
            $mime_type = $profileImagedata->document_mime_type;
            $image_extension = $profileImagedata->document_extension;
            if ($image_extension != 'png' && $image_extension != 'jpg' && $image_extension != 'jpeg') {
                if ($mime_type == 'image/png') {
                    $image_extension = 'png';
                } else if ($mime_type == 'image/jpeg') {
                    $image_extension = 'jpg';
                }
            }
            
            $resultimg = str_replace("data:image/" . $image_extension . ";base64,", "", $profileImagedata->attched_document);
            $row_image = "data:image/".$image_extension.";base64,".$profileImagedata->attched_document;
            $file_name = $profileImagedata->document_type . '_' . $profileImagedata->application_id;
           
             $image= base64_decode($row_image);


          }
          else{
            $image='';
            $row_image='';
            $image_extension='';
          }
       
      //////////////////// Image fetch code/////////////////////////
      

      /////////////////////////Debjit///////////////////////////////
      // $district = District::where('district_code', '=', $row->created_by_dist_code)->get(['district_code', 'district_name'])->first();
      // $district_name = $district->district_name;


      //////////////////////////Debjit//////////////////////
  
      $reject_revert_cause_list = RejectRevertReason::where('status', true)->get();
      if ($row->dist_code != "") {
        $district = District::where('district_code', '=', $row->dist_code)->get(['district_code', 'district_name'])->first();
        $district_name = $district->district_name;
      }
      $block_name = "";
      if ($row->block_ulb_code != "") {
        if ($row->rural_urban_id == 1) {
          $block = UrbanBody::where('urban_body_code', '=', $row->block_ulb_code)->first();
          if (!empty($block)) {
            $block_name = $block->urban_body_name;
          }
        } else {
          if (!empty($row->block_ulb_code)) {
            $block = Taluka::where('block_code', '=', $row->block_ulb_code)->first();
            if (!empty($block)) {
              $block_name = $block->block_name;
            } else {
              $block_name = '';
            }
          } else {
            $block_name = '';
          }
        }
      }
      $row->block_name = $block_name;
      $gp_name = "";
      if ($row->gp_ward_code != "") {
        if ($row->rural_urban_id == 1) {
          $gp_ward = Ward::where('urban_body_ward_code', '=', $row->gp_ward_code)->first();
          if (!empty($gp_ward)) {
            $gp_name =  $gp_ward->urban_body_ward_name;
          }
        } else {
          $gp = GP::where('gram_panchyat_code', '=', $row->gp_ward_code)->get(['gram_panchyat_code', 'gram_panchyat_name'])->first();
          if (!empty($gp)) {
            $gp_name =  $gp->gram_panchyat_name;
          }
        }
      }
      
      $row->gp_name = $gp_name;
      $doc_type_id = $this->doc_type_id;
      $docs='';
     
      if($designation_id=='Approver' ||  $designation_id == 'Delegated Approver'){
     
      $docs_new='';
      }
      else{
        $docs_new =collect([]);
      }
      
      
      $doc_man = DocumentType::get(['id', 'doc_name', 'doc_type', 'doc_mime_type', 'doc_size_kb'])->where("id", $doc_type_id)->first()->toArray();
     

     
      return view(
        'wbpds.ViewMismatchName',
        [
          'designation_id' => $designation_id,
          'row' => $row,
          'district_name' => $district_name,
          'block_name' => $block_name,
          'gp_name' => $gp_name,
          'doc_man' => $doc_man,
          'docs' => $docs,
          'image'=>$row_image,
          'ext'=> $image_extension,
          'decrypt_aadhar_old' => $decrypt_aadhar_old,
          'decrypt_aadhar_new' => $decrypt_aadhar_new,
          'docs_new' => $docs_new,
          'faulty_type'=> $faulty_type,
          'reject_revert_cause_list' => $reject_revert_cause_list,
          'type' => $type
        ]
      );
    
    }
    public function ViewpdsnamemismatchPost(Request $request)
    {
//dd($request->all());
      $this->middleware('auth');
      
      $doc_type_id = $this->doc_type_id;
      $designation_id = Auth::user()->designation_id;
      $user_id = Auth::user()->id;
      $id = $request->id;
      $aadhar_no = trim($request->aadhaar_no);
      $new_is_required = trim($request->new_is_required);
      $in_process_type = trim($request->process_type);
      $faulty_type=$request->faulty_type;
      
      if (empty($request->id)) {
        return redirect("/")->with('danger', 'Benificiary ID Not Found');
      }
      if (!ctype_digit($request->id)) {
        return redirect("/")->with('danger', 'Applicant ID Not Valid');
      }
      if(!in_array($new_is_required,array(1,0))){
        return redirect("/")->with('danger', 'Input Not Valid');
      }
      
      if($new_is_required==1){
        if(!empty($in_process_type)){
          $process_type=$in_process_type;
        }
        else{
          $process_type=4;
        }
      }
      else{
        $process_type=$in_process_type;
      }
    
      if(!empty($process_type)){
        if(!in_array($process_type,array(1,2,3,4))){
          return redirect("/")->with('danger', 'Process Id Not Valid');
        }
      }
      $type = $request->type;
      if(!in_array($type,array(1,2))){
        return redirect("/")->with('danger', 'Input Not Valid');
      }
      if($type==1){
        $type_des='Beneficiary with Account Validation Failed';
      }
      else if($type==2){
        $type_des='Beneficiary with Name Validation Failed from WBPDS';

      }
      
      $duty_obj = Configduty::where('user_id', $user_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $condition = array();
      $condition['bp.beneficiary_id'] = $request->id;
      $district_code = $duty_obj->district_code;
     
      if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
        if ($duty_obj->mapping_level == "Subdiv") {
          $created_by_local_body_code = $duty_obj->urban_body_code;
        }
        if ($duty_obj->mapping_level == "Block") {
          $created_by_local_body_code = $duty_obj->taluka_code;
        }
        $condition['bp.created_by_local_body_code'] = $created_by_local_body_code;
      }
      
      if($faulty_type==0)
      {
        $query = DB::table('lb_scheme.ben_personal_details AS bp')
        ->join('lb_scheme.ben_aadhar_details' . ' AS a', 'a.beneficiary_id', '=', 'bp.beneficiary_id')
        ->where($condition);

      }
      else{
        $query = DB::table('lb_scheme.faulty_ben_personal_details AS bp')
        ->join('lb_scheme.ben_aadhar_details' . ' AS a', 'a.beneficiary_id', '=', 'bp.beneficiary_id')
        ->where($condition);

      }
      
      $row = $query->first();
      $app_id = $row->application_id;
      $row->application_id = $app_id;
      $encoded_aadhar=$row->encoded_aadhar;
      $decrypt_aadhar= Crypt::decryptString($encoded_aadhar);

      if (empty($row)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $c_time = date('Y-m-d H:i:s', time());
      if($new_is_required==1){

        
       
        if ($this->isAadharValid(trim($request->aadhaar_no)) == false) {
          $errors = array();
          $errorMsg = "Aadhaar Number Invalid";
          array_push($errors, $errorMsg);
          return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $errorMsg);
        }
      }
      $doc_row = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', $doc_type_id)->first();

      if($new_is_required==1){
        
        
        if ($request->file('doc_' . $doc_type_id)) {
          $is_aadhar_file = 1;
          $image_file = $request->file('doc_' . $doc_type_id);
          $img_data = file_get_contents($image_file);
          $image_extension = $image_file->getClientOriginalExtension();
          $mime_type = $image_file->getMimeType();
          $image_size = $image_file->getSize(); 
          // echo $mime_type;die;
          
        if($image_size > 500000)
        {
          // echo "IF";die;
            $return_text = 'File Size must be 500KB.';
            $return_msg = array("" . $return_text);
            return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);



        }
        else{
          // echo "Else";die;
            if ($mime_type == 'image/png' || $mime_type == 'image/jpeg' || $mime_type == 'image/jpg'|| $mime_type == 'application/pdf' ) {
              // echo "IF";die;
                $base64 = base64_encode($img_data);
            }
            else{
              // echo "Else";die;
                $return_text = 'File must be jpeg/jpg/png/pdf.';
                $return_msg = array("" . $return_text);
                  return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);
            }

        }
         
          $doc_file = '';
         
          $file_profile = '';
          
          $destinationPath = '';
          $fileStore[] = '';
          
          $uploaded_doc[$doc_type_id] = '';


        } else {
        
          $base64=NULL;
          
        }
        $ag_update=1;
        if (!empty($row->aadhar_no)) {
          $sp_old_aadhar_no = Crypt::decryptString(trim($encoded_aadhar));
          
         
        }
        else{
          $sp_old_aadhar_no=NULL;
        }
        $sp_new_aadhar_no = $aadhar_no;
      
      
      }
      else{
       
        $ag_update=0;
        $file_passport = null;
      }
      if(!empty(trim($row->mobile_no))){
        $sp_mobile=$row->mobile_no;
      }
      else{
        $sp_mobile=0;  
      }
      if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
        
        $inputMain = [
          'failed_process_type_aadhaar' => $process_type,
          'failed_process_type_aadhaar' => $process_type,
          'next_level_role_id_aadhar_validation' => 1,
          'action_by' => Auth::user()->id,
          'action_ip_address' => request()->ip(),
          'action_type' => class_basename(request()->route()->getAction()['controller'])

        ];
        
        $inputFailed = [
          'failed_process_type_aadhaar' => $process_type,
          'next_level_role_id_aadhar_validation' => 1
        ];
        $new_value = [];
        $old_value = [];
        $old_value['aadhar_no'] = trim($decrypt_aadhar);
        $new_value['aadhar_no'] = $aadhar_no;
        if($process_type==1){
          $inputMain['acc_validated_aadhaar_new']=2;
          $inputMain['process_acc_validated_aadhar']=2;
          $inputFailed['acc_validated_aadhaar_new']=2;
          $inputFailed['process_acc_validated_aadhar']=2;
        }else if($process_type==2 || $process_type==4){
          $inputMain['process_acc_validated_aadhar']=0;
          $inputMain['acc_validated_aadhaar_new']=0;
          $inputFailed['acc_validated_aadhaar_new']=0;
          $inputFailed['process_acc_validated_aadhar']=0;
        }else if($process_type==3){
          $inputMain['process_acc_validated_aadhar']=-57;
          $inputMain['acc_validated_aadhaar_new']=-57;
          $inputMain['next_level_role_id_aadhar_validation']=1;
          $inputFailed['acc_validated_aadhaar_new']=-57;
          $inputFailed['process_acc_validated_aadhar']=-57;
        }
        if($new_is_required==1 && ($process_type==2 || $process_type==4)){
        // dd(123);
          $inputFailed_Benaadhar['new_aadhar_encoded']=Crypt::encryptString($aadhar_no);
          $inputFailed_Benaadhar['new_aadhar_hash']=md5($aadhar_no);
          $inputFailed_Benaadhar['aadhar_no_wbpds']='********' . substr($aadhar_no, -4);

          $inputMain_Benaadhar['new_aadhar_encoded']=Crypt::encryptString($aadhar_no);
          $inputMain_Benaadhar['new_aadhar_hash']=md5($aadhar_no);
          $inputMain_Benaadhar['aadhar_no_wbpds']='********' . substr($aadhar_no, -4);
          $inputMain_Benaadhar['action_by'] = Auth::user()->id;
          $inputMain_Benaadhar['action_ip_address'] = request()->ip();
          $inputMain_Benaadhar['action_type'] = class_basename(request()->route()->getAction()['controller']);
        }
       
        $docs_bank_pre_obj='';

        try {
         // dd($ag_update);
          $base_url = url('/');
          DB::beginTransaction();
          if($ag_update==1){

            $is_inserted_status =1;
          }
          else{
            $is_inserted_status = 1;
          }
         
          $aadhar_hash=md5($aadhar_no);

          

          
          $return_text = '';

          if($process_type==2){
              $count = DB::table('lb_scheme.ben_aadhar_details')->where('aadhar_hash', trim($aadhar_hash))->count();

             if($count > 0)
              {
              
              $is_inserted_status=2;

              }
              else{
    
              $is_inserted_status=1;

              }
        }
         
      $aadharDupCheckOap = DupCheck::getDupCheckAadhar(10,$request->aadhaar_no);  
      $aadharDupCheckJohar = DupCheck::getDupCheckAadhar(1,$request->aadhaar_no);
      $aadharDupCheckBandhu = DupCheck::getDupCheckAadhar(3,$request->aadhaar_no);
    
      if(!empty($aadharDupCheckOap)){
        DB::rollback();
        $return_text = "Duplicate Aadhaar Number present in Old Age Pension Scheme with Beneficiary ID- $aadharDupCheckOap ";
        $return_msg = array("" . $return_text);
        return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);
      }
      if(!empty($aadharDupCheckJohar)){
        DB::rollback();
        $return_text = "Duplicate Aadhaar Number present in Jai Johar Pension Scheme with Beneficiary ID- $aadharDupCheckJohar ";
        $return_msg = array("" . $return_text);
        return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);
      }
      if(!empty($aadharDupCheckBandhu)){
        DB::rollback();
        $return_text = "Duplicate Aadhaar Number present in Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- $aadharDupCheckBandhu ";
        $return_msg = array("" . $return_text);
        return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);
      }
         
          if ($is_inserted_status == 2) {
            
            DB::rollback();
            $return_text = 'Duplicate Aadhaar Information.. Please try different.';
            $return_msg = array("" . $return_text);
            return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);

          } else if ($is_inserted_status == 3) {
            
            DB::rollback();
            $return_text = 'Aadhaar Information Modification Faild.. Please try different.';
            $return_msg = array("" . $return_text);
            return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);

          }else if ($is_inserted_status == 5) {
            
            DB::rollback();
            $return_text = 'Aadhaar Information Modification Faild.. Please try different.';
            $return_msg = array("" . $return_text);
            return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);

          }else  if ($is_inserted_status == 1) {

           

          
            if($process_type==1 || $process_type==3){
              $base64=NULL;
            }

          $ben_aadhar_update=1;

           
            if ($ben_aadhar_update) {
            
              if ($base64) {
               // dd($faulty_type);
                
                $is_inserted_aadhar=DB::table('lb_scheme.ben_aadhar_details')->where(['beneficiary_id' => $id, 'created_by_local_body_code' => $created_by_local_body_code, 'created_by_dist_code' => $district_code])->update($inputMain_Benaadhar);
               
                if($faulty_type==0)
                {
                $upadated_main = DB::table('lb_scheme.ben_personal_details')->where(['beneficiary_id' => $id, 'created_by_local_body_code' => $created_by_local_body_code, 'created_by_dist_code' => $district_code])->update($inputMain);
                }
                else{
                  $upadated_main = DB::table('lb_scheme.faulty_ben_personal_details')->where(['beneficiary_id' => $id, 'created_by_local_body_code' => $created_by_local_body_code, 'created_by_dist_code' => $district_code])->update($inputMain);
                }
                $is_inserted_arch = DB::connection('pgsql_appwrite')->statement("INSERT INTO lb_scheme.ben_aadhar_details_arc(
                  application_id, beneficiary_id, aadhar_no, encoded_aadhar, encode_key, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, created_by_dist_code, created_by_local_body_code, is_dup, aadhar_hash_temp, aadhar_hash, aadhar_hash_adj, aadhar_hash_dup, dup_modification, is_azure, azure_old_encoded_aadhar, azure_old_aadhar_hash_adj, dup_accept_reject_remarks)
                SELECT application_id, beneficiary_id, aadhar_no, encoded_aadhar, encode_key, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, created_by_dist_code, created_by_local_body_code, is_dup, aadhar_hash_temp, aadhar_hash, aadhar_hash_adj, aadhar_hash_dup, dup_modification, is_azure, azure_old_encoded_aadhar, azure_old_aadhar_hash_adj, dup_accept_reject_remarks
          FROM lb_scheme.ben_aadhar_details where beneficiary_id='".$id."'");
                
               
                $i = 0;
                
                 $insert_doc_type_arr = array();
               
                  $insert_doc_type_arr[$i]['application_id'] = $app_id;
                  $insert_doc_type_arr[$i]['beneficiary_id'] = $id;
                  $insert_doc_type_arr[$i]['document_type'] = $doc_type_id;
                  $insert_doc_type_arr[$i]['attched_document'] = $base64;
                  $insert_doc_type_arr[$i]['created_at'] = date('Y-m-d H:i:s');
                  $insert_doc_type_arr[$i]['created_by'] = $user_id;
                  $insert_doc_type_arr[$i]['ip_address'] = request()->ip();
                  $insert_doc_type_arr[$i]['created_by_level']=$duty_obj->mapping_level;
                  $insert_doc_type_arr[$i]['document_extension'] = $image_extension;
                  $insert_doc_type_arr[$i]['document_mime_type'] = $mime_type;
                  $insert_doc_type_arr[$i]['created_by_dist_code'] = $district_code;
                  $insert_doc_type_arr[$i]['created_by_local_body_code'] = $created_by_local_body_code;
                  $insert_doc_type_arr[$i]['is_active'] = TRUE;

                  $getModelFunc = new getModelFunc();
                  $DraftPfImageTable = new DataSourceCommon;
                  // $Table = $getModelFunc->getTable($district_code, $this->source_type, 11, 1);
                  $Table = 'lb_scheme.ben_attach_documents_temp';
      
      
                  $DraftPfImageTable->setConnection('pgsql_encwrite');
                  $DraftPfImageTable->setTable('' . $Table);
                  //dd($DraftPfImageTable);
                 
                  // $doc_inserted = DB::table('lb_scheme.ben_attach_documents_temp')->insert($insert_doc_type_arr);

                  $doc_inserted = $DraftPfImageTable->insert($insert_doc_type_arr);
                
                
              } elseif($process_type==1 ||$process_type==3){
//dd($faulty_type);
                $doc_inserted=1;
                $is_inserted_arch=1;

                if($faulty_type==0)
                {
                  $upadated_main = DB::table('lb_scheme.ben_personal_details')->where(['beneficiary_id' => $id, 'created_by_local_body_code' => $created_by_local_body_code, 'created_by_dist_code' => $district_code])->update($inputMain);

                }
                else{
                  $upadated_main = DB::table('lb_scheme.faulty_ben_personal_details')->where(['beneficiary_id' => $id, 'created_by_local_body_code' => $created_by_local_body_code, 'created_by_dist_code' => $district_code])->update($inputMain);

                }
                
                $is_inserted_aadhar=1;
                
              }
              else{

                $doc_inserted=1;
                $is_inserted_arch=1;
                $upadated_main=1;
                $is_inserted_aadhar=1;

              }
              $failed_update = 1;
              // $failed_update = DB::table('pension.failed_payment_details')->where(['validation_type'=>2,'ben_id' => $id,'scheme_id' => $scheme_id])->update($inputFailed);///Debjit////
              //  if($process_type==3){
                 
              //       $free_pending_bank_duplicate_data=1;
              //       $reject_dup_adjustment=1;
                   
              // }
              // else{
              //   $free_pending_bank_duplicate_data=1;
              //   $reject_dup_adjustment=1;
              // }
//dd($doc_inserted);
              $ben_fullname=$request->first_name .' '.$request->middle_name.' '.$request->last_name;
              if ($failed_update  && $doc_inserted && $is_inserted_aadhar && $upadated_main && $is_inserted_arch) {
                if($faulty_type==0){

                  $api_code=4;
                }
                else{

                  $api_code=2;
                }

                $is_saved_log = 1;
                if ($is_saved_log) {
                  DB::commit();
                  try {
                    $session_lb_aadhaar_no=$this->RationcheckInsertUpdate($district_code,$app_id,$ben_fullname,$request->ip(),$aadhar_no,$created_by_local_body_code,$user_id,$request->dob,$api_code);
                  } catch (\Exception $e) {
                    $inputMain['bio_aadhar_checked_api_failed'] = -1;
                    $inputMain['action_by'] = Auth::user()->id;
                    $inputMain['action_ip_address'] = request()->ip();
                    $inputMain['action_type'] = class_basename(request()->route()->getAction()['controller']);
                    $upadated_main = DB::table('lb_scheme.ben_personal_details')
                    ->where([
                'application_id' => $app_id, 'created_by_local_body_code' => $created_by_local_body_code,
                'created_by_dist_code' => $district_code
              ])->update($inputMain);
                  }
                  $ben_details=DB::table('lb_scheme.ben_personal_details')->where('application_id',$app_id)->first();
                  if($ben_details){
                  if($process_type==3){
                  $return_text = 'Beneficiary with  Id:'.$id.' Rejected Successfully and Sent to Approver for Approval';
                  }
                  else{
                    $return_text = 'Beneficiary with  Id:'.$id.' Aadhaar Information Edited Successfully and Sent to Approver for Approval';
                    $aadhaar_no_checked=$ben_details->aadhaar_no_checked;
                    $aadhaar_no_checked_lastdatetime=$ben_details->aadhaar_no_checked_lastdatetime;
                    $aadhaar_no_checked_pass=$ben_details->aadhaar_no_checked_pass;
                    $aadhaar_no_validation_msg=$ben_details->aadhaar_no_validation_msg;

                  }
                }
                  return redirect("pdsnamemismatchlist?type=".$type)->with('success', $return_text)
                  ->with('aadhaar_no_checked', $aadhaar_no_checked)
                  ->with('aadhaar_no_checked_lastdatetime', $aadhaar_no_checked_lastdatetime)
                  ->with('aadhaar_no_checked_pass', $aadhaar_no_checked_pass)
                  ->with('aadhaar_no_validation_msg', $aadhaar_no_validation_msg);
                  
                } else {
                  DB::rollback();
                  $return_text = 'Error. Please try again';
                  $return_msg = array("" . $return_text);
                }
              } else {
                DB::rollback();
                $return_text = 'Error. Please try again';
                $return_msg = array("" . $return_text);
              }
            } else {
              DB::rollback();
              $return_text = 'Error. Please try again';
              $return_msg = array("" . $return_text);
            }
            if ($return_text != '') {

             
              return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);
            }
          }
          
        }catch (\Exception $e) {
      //dd($e);
          DB::rollback();
          $return_text = 'Error. Please try again';
          $return_msg = array("" . $return_text);
          return redirect("/Viewpdsnamemismatch?type=".$type."&id=".$request->id."&faulty_type=".$faulty_type)->with('errors', $return_msg);
        }
      }  else {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      
    }
    public function bulkApprove(Request $request)
    {
      $this->middleware('auth');
    //dd('ok');

     
      $designation_id = Auth::user()->designation_id;
      if (!in_array($designation_id, ['Approver', 'Delegated Approver'])) {
        return redirect("/")->with('error', 'Not Allowed');

    }
      
      $user_id = Auth::user()->id;
      
      $process_type = $request->process_type;
      $action_type = $request->action_type;
      $faulty_type= $request->faulty_type;
      $duty_obj = Configduty::where('user_id', $user_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $type = $request->type;
      if(!in_array($type,array(1,2))){
        return redirect("/")->with('danger', 'Input Not Valid');
      }
      if(!in_array($action_type,array(1,2,3))){
        return redirect("/")->with('danger', 'Input Not Valid');
      }
      if($type==1){
        $type_des='Beneficiary with Account Validation Failed from WBPDS';
        $check_acc_validate=-1;
      }
      else if($type==2){
        $type_des='Beneficiary with Name Validation Failed from WBPDS';
        $check_acc_validate=-2;

      }
      if(!in_array($process_type,array(1,2))){
        return redirect("/")->with('danger', 'Not Allowed');
      }
      
      $district_code = $duty_obj->district_code;
      
      $applicationid_arr = array();
      $inputs = request()->input('approvalcheck');
      $c_time = date('Y-m-d H:i:s', time());
      
      foreach ($inputs as $input) {
        array_push($applicationid_arr, $input);
        
      }

     
      if($process_type==2){
          if($faulty_type==0)
          {
            $rowcount = DB::table('lb_scheme.ben_personal_details')->where('acc_validated_aadhar',$check_acc_validate)->where('next_level_role_id_aadhar_validation',1)->where('created_by_dist_code', $district_code)->whereIn('beneficiary_id', $applicationid_arr)->count();
          }else
          {
            $rowcount = DB::table('lb_scheme.faulty_ben_personal_details')->where('acc_validated_aadhar',$check_acc_validate)->where('next_level_role_id_aadhar_validation',1)->where('created_by_dist_code', $district_code)->whereIn('beneficiary_id', $applicationid_arr)->count();
          }
          if($rowcount!=count($applicationid_arr)){
            return redirect("/")->with('danger', 'Not Allowed');
          }
      }
      $implode_application_arr = implode("','", $applicationid_arr);
      $in_pension_id = 'ARRAY[' . "'$implode_application_arr'" . ']';

      $count_elements = count($applicationid_arr);
      $back_url = 'pdsnamemismatchlist?type='.$type;
      
      
      
        if($count_elements==1 && $process_type==2 && $action_type==1)
        {

          
        $query = DB::table('lb_scheme.ben_aadhar_details AS bp')
        ->whereIn('beneficiary_id', $applicationid_arr);

        $row = $query->first();


        $aadhar_hash = $row->new_aadhar_hash;


        $count = DB::table('lb_scheme.ben_aadhar_details')->where('aadhar_hash', trim($aadhar_hash))->count();

        if($count > 0)
        {

       // $is_inserted_status=2;

        return redirect($back_url)->with('error', 'Application Aadhaar is Duplicate, Please check it!');

        }


        }


      $back_url = 'pdsnamemismatchlist?type='.$type;
      $comments = NULL;
            try {
              if($action_type==1){
              DB::beginTransaction();
              $ip_address=request()->ip();
            
              if($process_type==2){

               
                try {
                  $is_inserted_status_arr = DB::select("select lb_scheme.aadhaar_validation_request_bulk_new(in_beneficiary_id => $in_pension_id,in_district_code => $district_code,in_user_id => $user_id,faulty_type => $faulty_type,in_ip_address => '".$ip_address."',in_action_type => '".class_basename(request()->route()->getAction()['controller'])."',in_op_type => 'DT', in_custom_comment => '".$comments."')");
                
                $is_inserted_status=$is_inserted_status_arr[0]->aadhaar_validation_request_bulk_new;
                //dd($is_inserted_status);
                } catch (\Exception $th) {
                // dd($th);
                }
                

                

              }
              else if($process_type==1){

                //dd(333);
                try {
                  $is_inserted_status_arr = DB::select("select lb_scheme.aadhaar_validation_request_bulk_same(in_beneficiary_id => $in_pension_id,in_district_code => $district_code,in_user_id => $user_id,faulty_type => $faulty_type,in_ip_address => '".$ip_address."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."',in_op_type => 'DF', in_custom_comment => '".$comments."')");
                  $is_inserted_status=$is_inserted_status_arr[0]->aadhaar_validation_request_bulk_same;
                } catch (\Exception $th) {

                  //dd($th);
                  
                }



                
              }
            
            if($is_inserted_status==1){
              DB::commit();
              return redirect($back_url)->with('message', 'Applications Aadhaar information change request has been Approved Succesfully!');
            } else{
              
              DB::rollback();
              return redirect($back_url)->with('error', 'There are some may be Duplicate aadhar which you are trying to finalize.Please try to finalize one by one application.');
            }
      }
      else if($action_type==2){
      //dd($action_type);
        foreach ($applicationid_arr as $appItem) {
          $application_id=$appItem;
          
          
        }
        if($faulty_type==0)
        {
          $row = DB::table('lb_scheme.ben_personal_details')->where('acc_validated_aadhar',$check_acc_validate)->where('next_level_role_id_aadhar_validation',1)->where('created_by_dist_code', $district_code)->where('beneficiary_id', $application_id)->first();

        }
        else{
          $row = DB::table('lb_scheme.faulty_ben_personal_details')->where('acc_validated_aadhar',$check_acc_validate)->where('next_level_role_id_aadhar_validation',1)->where('created_by_dist_code', $district_code)->where('beneficiary_id', $application_id)->first();

        }
       // dd($check_acc_validate);
       

          DB::beginTransaction();
        
         $inputMain = [
          'failed_process_type_aadhaar' =>NULL, 'next_level_role_id_aadhar_validation' => NULL, 
          'process_acc_validated_aadhar' => NULL, 
           'acc_validated_aadhaar_new'=> NULL,
           'action_by' => Auth::user()->id,
           'action_ip_address' => request()->ip(),
           'action_type' => class_basename(request()->route()->getAction()['controller'])

        ];
        $inputFail = [
          'next_level_role_id_aadhar_validation' =>NULL, 
          'acc_validated_aadhaar_new' => NULL, 
          'failed_process_type_aadhaar' => NULL, 
          'process_acc_validated_aadhar' => NULL
        ];

        $inputDoc= [
          'is_active' =>FALSE
          
        ];
        $inputBenaadhar= [
          'new_aadhar_encoded' => NULL,
          'new_aadhar_hash' => NULL,
          'aadhar_no_wbpds' => NULL,
          'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])

        ];
        
        if($faulty_type==0)
        {
         $main_update = DB::table('lb_scheme.ben_personal_details')
         ->where('created_by_dist_code', $district_code)
         ->where('next_level_role_id_aadhar_validation',1)->where('beneficiary_id', $application_id)->update($inputMain);
        }
        else{
          $main_update = DB::table('lb_scheme.faulty_ben_personal_details')
         ->where('created_by_dist_code', $district_code)
         ->where('next_level_role_id_aadhar_validation',1)->where('beneficiary_id', $application_id)->update($inputMain);
        }
         $failed_update = 1;

        if($row->failed_process_type_aadhaar==2){

            $getModelFunc = new getModelFunc();
            $DraftPfImageTable = new DataSourceCommon;
            // $Table = $getModelFunc->getTable($district_code, $this->source_type, 11, 1);
            $Table = 'lb_scheme.ben_attach_documents_temp';

            $DraftPfImageTable->setConnection('pgsql_encwrite');
            $DraftPfImageTable->setTable('' . $Table);
            //dd($DraftPfImageTable);

            $delete_arch = $DraftPfImageTable->where('created_by_dist_code', $district_code)->where('beneficiary_id', $application_id)->where('document_type',6)->where('is_active', true)->update($inputDoc);
            $benaadhar_update = DB::table('lb_scheme.ben_aadhar_details')
         ->where('created_by_dist_code', $district_code)->where('beneficiary_id', $application_id)->update($inputBenaadhar);

        }
        else
        {
          $benaadhar_update=1;
            $delete_arch=1;
        }

         
            if( $main_update &&  $failed_update && $delete_arch && $benaadhar_update ){
            DB::commit();
            return redirect($back_url)->with('message', 'Applications with Beneficiary id '.$application_id.' Aadhaar information change request has been Reverted Succesfully!');
          } else{
            
            DB::rollback();
            return redirect($back_url)->with('error', 'Error! Please try again.');
          }

      }
      else if($action_type==3){
        foreach ($applicationid_arr as $appItem) {
          $application_id=$appItem;
        
          
        }

        $ben_payment_paymentServer_update=[
          'ben_status' => -57
        ];
   
        DB::beginTransaction();

        $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';

        if($faulty_type==0)
        {
        //$reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_wbpds(" . $in_pension_id . ")");
        $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_wbpds(in_beneficiary_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

        }
        else{

          $reject_fun = DB::select("select lb_scheme.beneficiary_faulty_rejected_final_wbpds(" . $in_pension_id . ")");
        }
  
    if($reject_fun ){
    $payment_update= DB::connection('pgsql_payment')->table('payment.ben_payment_details')->where('ben_id', $application_id)->update($ben_payment_paymentServer_update);
    }
         if($payment_update ){

         
          DB::commit();
          return redirect($back_url)->with('message', 'Application Rejected Succesfully!');
        } else{
          DB::rollback();
          return redirect($back_url)->with('error', 'Error! Please try again.');
        }
      }
      }catch (\Exception $e) {
        //dd($e);
        DB::rollback();
        return redirect($back_url)->with('error', 'Error! Please try again.');
    }
  }



  public function wbpdspdfdownload(Request $request)
  {

    $this->middleware('auth');
        
//dd($request->id);
$beneficiary_id=$request->id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
    //$user_id = Auth::user()->id;
    $duty_obj = Configduty::where('user_id', $user_id)->first();
    $district_code = $duty_obj->district_code;
    $getModelFunc = new getModelFunc();
        $DraftPfImageTable = new DataSourceCommon;
      // $Table = $getModelFunc->getTable($district_code, $this->source_type, 11, 1);
      $Table = 'lb_scheme.ben_attach_documents_temp';

        $DraftPfImageTable->setConnection('pgsql_encwrite');
        $DraftPfImageTable->setTable('' . $Table);

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        $condition['is_active'] = TRUE;
        //$condition['created_by_local_body_code'] = $blockCode;
        //$profileImagedata = DB::table('lb_scheme.ben_attach_documents_temp')->where('application_id', $app_id)->where($condition)->first();
      //$profileImagedata = $DraftPfImageTable->where('application_id', $app_id)->where($condition)->first();
      $profileImagedata = $DraftPfImageTable->where('beneficiary_id', $beneficiary_id)->where($condition)->first();
            if (empty($profileImagedata->application_id)) {

          
                $return_text = 'Parameter Not Valid';
                return redirect("/")->with('error',  $return_text);
            }

          
            $mime_type = $profileImagedata->document_mime_type;
            $image_extension = $profileImagedata->document_extension;
            
            try {
                 if (strtoupper($image_extension) == 'PDF') {
                    $decoded = base64_decode($profileImagedata->attched_document);
                    $file_name = $profileImagedata->document_type . '_' . $profileImagedata->application_id . '.pdf';
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . $file_name);
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . strlen($decoded));
                    ob_clean();
                    flush();
                    echo $decoded;
                    exit;
                }
            } catch (\Exception $e) {
                $return_text = 'Some error. please try again.';
                return redirect("/")->with('error',  $return_text);
            }
}




    public function isAadharValid($num)
    {
      //dd($num);
        settype($num, "string");
        $expectedDigit = substr($num, -1);
        $actualDigit = $this->CheckSumAadharDigit(substr($num, 0, -1));
        return ($expectedDigit == $actualDigit) ? $expectedDigit == $actualDigit : 0;
    }

    function CheckSumAadharDigit($partial)
    {
        $dihedral = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
            array(1, 2, 3, 4, 0, 6, 7, 8, 9, 5),
            array(2, 3, 4, 0, 1, 7, 8, 9, 5, 6),
            array(3, 4, 0, 1, 2, 8, 9, 5, 6, 7),
            array(4, 0, 1, 2, 3, 9, 5, 6, 7, 8),
            array(5, 9, 8, 7, 6, 0, 4, 3, 2, 1),
            array(6, 5, 9, 8, 7, 1, 0, 4, 3, 2),
            array(7, 6, 5, 9, 8, 2, 1, 0, 4, 3),
            array(8, 7, 6, 5, 9, 3, 2, 1, 0, 4),
            array(9, 8, 7, 6, 5, 4, 3, 2, 1, 0)
        );
        $permutation = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
            array(1, 5, 7, 6, 2, 8, 3, 0, 9, 4),
            array(5, 8, 0, 3, 7, 9, 6, 1, 4, 2),
            array(8, 9, 1, 6, 0, 4, 3, 5, 2, 7),
            array(9, 4, 5, 3, 1, 2, 6, 8, 7, 0),
            array(4, 2, 8, 6, 5, 7, 3, 9, 0, 1),
            array(2, 7, 9, 3, 8, 0, 6, 4, 1, 5),
            array(7, 0, 4, 6, 9, 1, 3, 2, 5, 8)
        );

        $inverse = array(0, 4, 3, 2, 1, 5, 6, 7, 8, 9);
        settype($partial, "string");
        $partial = strrev($partial);
        $digitIndex = 0;
        for ($i = 0; $i < strlen($partial); $i++) {
            $digitIndex = $dihedral[$digitIndex][$permutation[($i + 1) % 8][$partial[$i]]];
        }
        return $inverse[$digitIndex];
    }


// Added by ANJAN for Aadhar Name Validation
    
    function aadharNameValidMIS(Request $request)
    {


        $this->middleware('auth');
        $base_date  = '2020-01-01';
        date_default_timezone_set('Asia/Kolkata');
        $c_time = Carbon::now();
        $c_date = $c_time->format("Y-m-d");
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $userId = Auth::user()->id;
        $district_visible = $is_urban_visible = $block_visible = 1;
        $municipality_visible = 0;
        $gp_ward_visible = 0;
        $muncList = collect([]);
        $gpList = collect([]);
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' ||  $designation_id == 'Dashboard' || $designation_id == 'MisState' || $designation_id == 'DDO') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver'
        ) {
            $district_code = NULL;
            $is_urban = NULL;
            $blockCode = NULL;
            foreach ($roleArray as $roleObj) {
                if (in_array($roleObj['scheme_id'], array(20))) {
                    $is_urban = $roleObj['is_urban'];
                    $district_code = $roleObj['district_code'];
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                        $muncList = UrbanBody::select('urban_body_code', 'urban_body_name')->where('sub_district_code', $blockCode)->get();
                        $municipality_visible = 1;
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                        $gpList = GP::select('gram_panchyat_code', 'gram_panchyat_name')->where('block_code', $blockCode)->get();
                    }
                    break;
                }
            }

            if (empty($district_code))
                return redirect("/")->with('success', 'User Disabled. ');
        } else {
            return redirect("/")->with('success', 'User Disabled. ');
        }
        //dd($district_code);
        if (!empty($district_code)) {
            $district_visible = 0;
            $district_code_fk = $district_code;
        } else {
            $district_code_fk = NULL;
        }
        if (!empty($is_urban)) {
            $is_urban_visible = 0;
            $rural_urban_fk = $is_urban;
        } else {
            $rural_urban_fk = NULL;
        }
        if (!empty($blockCode)) {
            $block_visible = 0;
            $block_munc_corp_code_fk = $blockCode;
            $gp_ward_visible = 1;
        } else {
            $block_munc_corp_code_fk = NULL;
            $gp_ward_visible = 0;
        }
        $gp_ward_visible = 0;
        $municipality_visible = 0;
        $districts = District::get();
        //$scheme_list = DB::select(DB::raw("select id,scheme_name from m_scheme where id IN (3,2,10,11,8,9,17,19,1) and  id in (select scheme_id from duty_assignement where user_id=" . $userId . " and is_active=1) and is_active=1 order by scheme_name"));
        //dd($scheme_list);
        return view(
            'wbpds.aadharNameValidationReport',
            [
                'districts' => $districts,
                'district_visible' => $district_visible,
                'district_code_fk' => $district_code_fk,
                'is_urban_visible' => $is_urban_visible,
                'rural_urban_fk' => $rural_urban_fk,
                'block_visible' => $block_visible,
                'block_munc_corp_code_fk' => $block_munc_corp_code_fk,
                'municipality_visible' => $municipality_visible,
                'gp_ward_visible' => $gp_ward_visible,
                'is_urban_visible' => $is_urban_visible,
                'base_date' => $base_date,
                'c_date' => $c_date,
                'gpList' => $gpList,
                'muncList' => $muncList
            ]
        );
    }



    public function getData(Request $request)
    {
      //dd($request->all());
        //$scheme_id = $request->scheme_id;
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;
        $faulty_type = $request->faulty_type;
        // dd($gp_ward);
        $caste = $request->caste_category;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $base_date  = '2020-08-16';
        $c_time = Carbon::now();
        $c_date = $c_time->format("Y-m-d");
        $heading_msg = '';
        $title = "";
        //$block_condition = "";
        if (!empty($district)) {
            $district_row = District::where('district_code', $district)->first();
            $block_ulb_code=NULL;
        }

        if (!empty($block)) {

            if ($urban_code == 1) {
              $block_ulb = UrbanBody::where('urban_body_code', '=', $block)->first();
                // $block_ulb = SubDistrict::where('sub_district_code', '=', $block)->first();
                //$blk_munc_name = $block_ulb->sub_district_name;
                $block_ulb_code = $block_ulb->urban_body_code;
                //$block_condition = " and rural_urban_id=1 and created_by_local_body_code=" . $block;
            } else {
                $block_ulb = Taluka::where('block_code', '=', $block)->first();
                $blk_munc_name = $block_ulb->block_name;
                $block_ulb_code = $block_ulb->block_code;
                // $block_condition = " and rural_urban_id=2 and  created_by_local_body_code=" . $block;
            }
        } else {
            // $block_condition = "";
        }
        if (!empty($gp_ward)) {

            if ($urban_code == 1) {
                $gp_ward_row = Ward::where('urban_body_ward_code', '=', $gp_ward)->first();
                $gp_ward_name = $gp_ward_row->urban_body_ward_name;
                $block_ulb_code=NULL;
                //$block_condition = " and rural_urban_id=1 and created_by_local_body_code=" . $block;
            } else {
                $gp_ward_row = GP::where('gram_panchyat_code', '=', $gp_ward)->first();
                $gp_ward_name = $gp_ward_row->gram_panchyat_name;
                $block_ulb_code=NULL;
                // $block_condition = " and rural_urban_id=2 and  created_by_local_body_code=" . $block;
            }
        }
        $rules = [
            'district' => 'nullable|integer',
            'urban_code' => 'nullable|integer',
            'block' => 'nullable|integer',
            'muncid' => 'nullable|integer',
            'gp_ward' => 'nullable|integer'
        ];
        $data = array();
        $column = "";
        $attributes = array();
        $messages = array();
        $attributes['scheme_id'] = 'Scheme';
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['block'] = 'Block/Sub Division';
        $attributes['muncid'] = 'Municipality';
        $attributes['gp_ward'] = 'GP/Ward';
        // $attributes['from_date'] = 'From Date';
        // $attributes['to_date'] = 'To Date';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            // $scheme_row = Scheme::where('id', $scheme_id)->first();
            $user_msg = "PDS Aadhar Name Validation MIS Report for the Scheme Lakshmir Bhandar";
            $title = $user_msg;
            //dd($title);

            $data = array();
            $return_status = 1;
            $return_msg = '';
            $heading_msg = '';
            $external = 0;
            $external_arr = array();
            $external_filter = array();
            // if (!empty($gp_ward)) {

            //   dd(445);
            //     if ($urban_code == 1) {
            //         $column = "Ward";
            //         $heading_msg =  $user_msg . ' of the Ward ' . $gp_ward_name;
            //         $data = $this->getWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date,$faulty_type);
            //     } else {
            //         $column = "GP";
            //         $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
            //         $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $faulty_type);
            //     }
            // } else if (!empty($muncid)) {

            //   dd(66);
            //     $column = "Ward";
            //     $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
            //     $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
            //     $data = $this->getWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $faulty_type);
            // } else if (!empty($block)) {

            //   dd(77);
            //     if ($urban_code == 1) {
            //         $column = "Municipality";
            //         $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
            //         $data = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $faulty_type);
            //     } else if ($urban_code == 2) {
            //         $block_arr = Taluka::where('block_code', '=', $block)->first();
            //         $column = "GP";
            //         $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
            //         $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date,$faulty_type);
            //         $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
            //         $column = "Block";
            //         $data = $this->getBlockWise( $district, NULL, NULL, NULL, $from_date, $to_date, $faulty_type);
            //     }
            // } else {
              //dd(123);

                if (!empty($district)) {

                 // dd(123);
                    if ($urban_code == 1) {
                        $column = "Municipality";
                        $heading_msg = 'Municipality Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                       // $data = $this->getSubDivWise( $district, $block_ulb_code,$faulty_type);

                        $data = $this->getMuncWise($district, $block_ulb_code,$faulty_type);
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWise( $district, $block_ulb_code, $faulty_type);
                    } else {
                        $heading_msg = 'Block/Municipality Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Municipality";
                        $data1 = $this->getBlockWise( $district, $block_ulb_code,$faulty_type);
                        //$data2 = $this->getSubDivWise( $district, $block_ulb_code, $faulty_type);
                        $data2 = $this->getMuncWise($district, $block_ulb_code,$faulty_type);
                        $data = array_merge($data1, $data2);
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWise($district,$faulty_type);

                    $external = 0;
                }
           

            if (!empty($from_date)) {
                $form_date_formatted = \Carbon\Carbon::parse($from_date)->format('d-m-Y');
                $heading_msg = $heading_msg . " from " . $form_date_formatted;
            }
            if (!empty($to_date)) {
                $to_date_formatted = \Carbon\Carbon::parse($to_date)->format('d-m-Y');
                $heading_msg = $heading_msg . " to  " . $to_date_formatted;
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        return response()->json([
            'return_status' => $return_status,
            'return_msg' => $return_msg,
            'row_data' => $data,
            'column' => $column,
            'title' => $title,
            'heading_msg' => $heading_msg
        ]);
    }



    public function getDistrictWise( $district_code = NULL,$faulty_type)
    {
     //dd(123);
        //$dateFromat = 'DD/MM/YYYY';
        //$dateFromat = 'YYYY/MM/DD';
        //$whereCon = "where 1=1";

        //dd($faulty_type);

        if($faulty_type==0)
        {
          $table = "lb_scheme.ben_personal_details";

        }
        if($faulty_type==1)
        {
          $table = "lb_scheme.faulty_ben_personal_details ";

        }
        $query = "select A.location_id,A.location_name,
        COALESCE(C.total_name_mismatch,0) as total_name_mismatch, 
        COALESCE(C.total_yet_to_be_action_pending,0) as total_yet_to_be_action_pending, 
        COALESCE(C.total_minor_yet_to_approved,0) as total_minor_yet_to_approved,
        COALESCE(C.total_minor_approved,0) as total_minor_approved,
        COALESCE(C.total_aadhar_yet_to_approved,0) as total_aadhar_yet_to_approved,
        COALESCE(C.total_aadhar_approved,0) as total_aadhar_approved
        from(
        select district_code as location_id,district_name as location_name
         from public.m_district ) as A  
        LEFT JOIN
        (select
            count(1) filter(WHERE acc_validated_aadhar in(-2,0,2) ) as total_name_mismatch,
            count(1) filter(WHERE next_level_role_id_aadhar_validation is NULL and acc_validated_aadhar in(-2,0,2)) as total_yet_to_be_action_pending,
            count(1) filter(WHERE failed_process_type_aadhaar = 1 AND next_level_role_id_aadhar_validation= 1 and acc_validated_aadhar= -2) as total_minor_yet_to_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 1 AND next_level_role_id_aadhar_validation= 0 and acc_validated_aadhar = 2) as total_minor_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 2 AND next_level_role_id_aadhar_validation= 1 and acc_validated_aadhar= -2) as total_aadhar_yet_to_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 2 AND next_level_role_id_aadhar_validation= 0 and acc_validated_aadhar= 0) as total_aadhar_approved,
            created_by_dist_code
            from ".$table." where next_level_role_id=0 
         group by created_by_dist_code) as C ON A.location_id=C.created_by_dist_code";

        //echo $query;die;
        $result = DB::connection('pgsql_mis')->select($query);
        return $result;
    }


    public function getMuncWise( $district_code = NULL,  $block_ulb_code = NULL , $faulty_type)
    {

     
      if($faulty_type==0)
      {
        $table = "lb_scheme.ben_personal_details";
        $table1 = "lb_scheme.ben_contact_details";

      }
      if($faulty_type==1)
      {
        $table = "lb_scheme.faulty_ben_personal_details ";
        $table1 = "lb_scheme.faulty_ben_contact_details ";

      }


      if($block_ulb_code!='')
      {
      $whereCo_sub = " and  urban_body_code='" . $block_ulb_code . "'";
      }
      else{
        $whereCo_sub="";
      }

        $whereMain = "where  district_code=" . $district_code;

        $query = "select A.location_id,A.location_name,
        COALESCE(C.total_name_mismatch,0) as total_name_mismatch,
        COALESCE(C.total_yet_to_be_action_pending,0) as total_yet_to_be_action_pending,  
        COALESCE(C.total_minor_yet_to_approved,0) as total_minor_yet_to_approved,
        COALESCE(C.total_minor_approved,0) as total_minor_approved,
        COALESCE(C.total_aadhar_yet_to_approved,0) as total_aadhar_yet_to_approved,
        COALESCE(C.total_aadhar_approved,0) as total_aadhar_approved
        from(
            select sub_district_code,urban_body_code as location_id,'Municipality-'||urban_body_name as location_name
            from public.m_urban_body  " . $whereMain . " ".$whereCo_sub." 
         )
         as A  
        LEFT JOIN
        (select
           count(1) filter(WHERE acc_validated_aadhar in(-2,0,2) ) as total_name_mismatch,
            count(1) filter(WHERE next_level_role_id_aadhar_validation is NULL and acc_validated_aadhar in(-2,0,2)) as total_yet_to_be_action_pending,
            count(1) filter(WHERE failed_process_type_aadhaar = 1 AND next_level_role_id_aadhar_validation= 1 and acc_validated_aadhar= -2) as total_minor_yet_to_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 1 AND next_level_role_id_aadhar_validation= 0  and acc_validated_aadhar = 2) as total_minor_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 2 AND next_level_role_id_aadhar_validation= 1 and acc_validated_aadhar= -2) as total_aadhar_yet_to_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 2 AND next_level_role_id_aadhar_validation= 0 and acc_validated_aadhar= 0) as total_aadhar_approved,
            bc.block_ulb_code
            from ".$table." as bp 
			      INNER JOIN ".$table1." as bc ON bp.application_id=bc.application_id 
            
            where bp.created_by_dist_code= " . $district_code . "  AND bp.next_level_role_id=0 AND bc.created_by_dist_code= " . $district_code . " 
        group by block_ulb_code) as C

         ON A.location_id=C.block_ulb_code";

        //echo $query;die;

        $result = DB::connection('pgsql_mis')->select($query);
        return $result;
    }


    public function getBlockWise( $district_code = NULL,  $block_ulb_code = NULL , $faulty_type)
    {
      if($faulty_type==0)
      {
        $table = "lb_scheme.ben_personal_details";

      }
      if($faulty_type==1)
      {
        $table = "lb_scheme.faulty_ben_personal_details ";

      }

      if($block_ulb_code!='')
      {
      $whereCo_sub = " and  block_code='" . $block_ulb_code . "'";
      }
      else{
        $whereCo_sub="";
      }
        $whereMain = "where  district_code=" . $district_code;
        $query = "select A.location_id,A.location_name,
        COALESCE(C.total_name_mismatch,0) as total_name_mismatch,
        COALESCE(C.total_yet_to_be_action_pending,0) as total_yet_to_be_action_pending,  
        COALESCE(C.total_minor_yet_to_approved,0) as total_minor_yet_to_approved,
        COALESCE(C.total_minor_approved,0) as total_minor_approved,
        COALESCE(C.total_aadhar_yet_to_approved,0) as total_aadhar_yet_to_approved,
        COALESCE(C.total_aadhar_approved,0) as total_aadhar_approved
        
        from(
            select block_code as location_id,'Block-'||block_name as location_name
           from public.m_block  " . $whereMain . "  ".$whereCo_sub." 
         )
         as A  
        LEFT JOIN
        (select
            count(1) filter(WHERE acc_validated_aadhar in(-2,0,2) )as total_name_mismatch,
            count(1) filter(WHERE next_level_role_id_aadhar_validation is NULL and acc_validated_aadhar in(-2,0,2)) as total_yet_to_be_action_pending,
            count(1) filter(WHERE failed_process_type_aadhaar = 1 AND next_level_role_id_aadhar_validation= 1 and acc_validated_aadhar= -2) as total_minor_yet_to_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 1 AND next_level_role_id_aadhar_validation= 0 and acc_validated_aadhar = 2) as total_minor_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 2 AND next_level_role_id_aadhar_validation= 1 and acc_validated_aadhar= -2) as total_aadhar_yet_to_approved,
            count(1) filter(WHERE failed_process_type_aadhaar = 2 AND next_level_role_id_aadhar_validation= 0 and acc_validated_aadhar= 0) as total_aadhar_approved,
            created_by_local_body_code
            from ".$table." where created_by_dist_code= " . $district_code . "  AND next_level_role_id=0
        group by created_by_local_body_code) as C ON A.location_id=C.created_by_local_body_code";

        $result = DB::connection('pgsql_mis')->select($query);
        return $result;
    }
    
    public function getExcel(Request $request){
      // dd('123');
      $application_type=$request->application_type;
      $process_type=$request->process_type;
      $user_id = Auth::user()->id;
      $faulty_type=$request->faulty_type;
      $designation_id = Auth::user()->designation_id;
      $duty_obj = Configduty::where('user_id', $user_id)->first();
      $district_code = $duty_obj->district_code;

      $urban_bodys = collect([]);
      $gps = collect([]);
      $district_list_obj = collect([]);
      
      if ($duty_obj->mapping_level == "Subdiv") {
        $created_by_local_body_code = $duty_obj->urban_body_code;
        $is_rural = 1;
        $verifier_type = 'Subdiv';
        $gps = collect([]);
        $urban_body_code = $duty_obj->urban_body_code;
        $urban_bodys = UrbanBody::where('sub_district_code', $urban_body_code)->select('urban_body_code', 'urban_body_name')->get();
        $urban_body_codes = [];
        $i = 0;
        foreach ($urban_bodys as $urban_body) {
  
          $urban_body_codes[$i] = $urban_body->urban_body_code;
          $i++;
        }
      }
      if ($duty_obj->mapping_level == "Block") {
        $created_by_local_body_code = $duty_obj->taluka_code;
        $is_rural = 2;
        $verifier_type = 'Block';
        $urban_bodys = collect([]);
        $taluka_code = $duty_obj->taluka_code;
        $gps = GP::where('block_code', $taluka_code)->select('gram_panchyat_code', 'gram_panchyat_name')->get();
      }
      if ($duty_obj->mapping_level == "District") {
        $district_list_obj = District::get();
        $verifier_type = 'District';
        $is_rural = NULL;
        $created_by_local_body_code = NULL;
      }
      if($application_type==4)
       {

        if($faulty_type==0)
        {
          $query = DB::table('lb_scheme.ben_reject_details' . ' AS bp')
          ->where('bp.created_by_dist_code', $district_code)->where('bp.is_faulty', FALSE);

        }
        else{
          $query = DB::table('lb_scheme.ben_reject_details' . ' AS bp')
          ->where('bp.created_by_dist_code', $district_code)->where('bp.is_faulty', TRUE);
        }
          
       }else{

          if($faulty_type==0)
          {
              $query = DB::table('lb_scheme.ben_personal_details' . ' AS bp')
              ->join('lb_scheme.ben_bank_details' . ' AS bb', 'bb.beneficiary_id', '=', 'bp.beneficiary_id')
              ->join('lb_scheme.ben_contact_details' . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
              ->where('bp.created_by_dist_code', $district_code)->where('bp.next_level_role_id',0);

          }
          if($faulty_type==1)
          {
              $query = DB::table('lb_scheme.faulty_ben_personal_details' . ' AS bp')
              ->join('lb_scheme.faulty_ben_bank_details' . ' AS bb', 'bb.beneficiary_id', '=', 'bp.beneficiary_id')
              ->join('lb_scheme.faulty_ben_contact_details' . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
              ->where('bp.created_by_dist_code', $district_code)->where('bp.next_level_role_id',0);
          }
          // dd($query);
       } 
       if ($designation_id == 'Verifier'|| $designation_id == 'Delegated Verifier') {
        // dd(123);
         $query = $query->where('bp.created_by_local_body_code', $created_by_local_body_code);
         
         
         if (!empty($application_type)) {
          // dd($application_type);
           if($application_type==1)
            $query = $query->whereNull('bp.next_level_role_id_aadhar_validation');
           
           if($application_type==2)
            $query = $query->where('bp.next_level_role_id_aadhar_validation', 1);
           
           if($application_type==3)
            $query = $query->where('bp.next_level_role_id_aadhar_validation', 0);
           
            if($application_type==4)
            $query = $query->where('bp.next_level_role_id_aadhar_validation', -57);
           
         }
       }
      //  if ($type == 2) {
        if($application_type!=3){
        $query = $query->where('bp.acc_validated_aadhar', -2);
        
        }
      // }
      if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
        //dd($application_type);
        if ($application_type!='') {
          if($application_type==1)
           $query = $query->where('bp.next_level_role_id_aadhar_validation', 1);
          
          if($application_type==3)
           $query = $query->where('bp.next_level_role_id_aadhar_validation', 0);
          
           if($application_type==4)
           $query = $query->where('bp.process_acc_validated_aadhar', -57);
           
        }
        //dd($process_type);
        if (!empty($process_type)) {
            if($process_type==1)
             $query = $query->where('bp.failed_process_type_aadhaar',1);
           
            if($process_type==2)
            $query = $query->where('bp.failed_process_type_aadhaar',2);
           
            if($process_type==3)
            $query = $query->where('bp.failed_process_type_aadhaar',3);
        }
      }
      // $data = $query->orderBy('bp.beneficiary_id', 'ASC')->toSql();
      // dd($data);
      $data = $query->orderBy('bp.beneficiary_id', 'ASC')->get([
        'bp.beneficiary_id', 'bp.created_by_dist_code', 'bp.dob', 'bp.application_id','bb.bank_name','bb.bank_code','bb.bank_ifsc',
         'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender', 'bc.block_ulb_code','bc.block_ulb_name','bc.gp_ward_code','bc.gp_ward_name',
          'bp.next_level_role_id_aadhar_validation', 'bp.next_level_role_id','bp.acc_validated_aadhar',
        'bp.process_acc_validated_aadhar','bp.mobile_no','bp.wbpds_name_as_in_aadhar','bp.wbpds_name_as_in_aadhar_sr'
      ]);
      
      $excelarr[] = array(
        'Beneficiary ID', 'Beneficiary Name', 'Beneficiary Name(As Received From PDS)', 'Mobile Number', 'DOB', 'GP/Ward',
    );

    foreach ($data as $arr) {
        $excelarr[] = array(
            'Beneficiary ID' => trim($arr->beneficiary_id),
            'Beneficiary Name' => trim($arr->ben_fname . ' ' . $arr->ben_mname . ' ' . $arr->ben_lname),
            'Beneficiary Name(As Received From PDS)' => trim($arr->wbpds_name_as_in_aadhar_sr),
            'Mobile Number' => trim($arr->mobile_no),
            'DOB' => trim($arr->dob),
            'GP/Ward' => trim($arr->gp_ward_name),           
        );
    }
    $file_name = 'PDS Aadhar Name Validation'.  date('d/m/Y');
    Excel::create($file_name, function ($excel) use ($excelarr) {
        $excel->setTitle('PDS Aadhar Name Validation');
        $excel->sheet('PDS Aadhar Name Validation', function ($sheet) use ($excelarr) {
            $sheet->fromArray($excelarr, null, 'A1', false, false);
        });
    })->download('xlsx');
    }
}
