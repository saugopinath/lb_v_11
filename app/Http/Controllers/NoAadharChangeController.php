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
use App\models\DataSourceCommon;

use App\models\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use App\models\RejectRevertReason;
use App\AadharDuplicateTrail;
use App\SubDistrict;
use App\models\Taluka;
use App\models\DocumentType;
use Illuminate\Support\Facades\Storage;
use App\SchemeDocMap;
use File;
use App\BankDetails;
use App\UrbanBody;
use App\Ward;
use App\Models\GP;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\AcceptRejectInfo;
use App\Models\MapLavel;
use App\DsPhase;
use App\Traits\TraitAadharUpdate;
use App\Helpers\DupCheck;
class NoAadharChangeController extends Controller
{
  use TraitAadharUpdate;

    public function __construct()
    {

         $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
        $this->ben_status = -97;
        $this->doc_type_id = 6;
        
    }
    
   
    public function list(Request $request)
    {
      try{
        $this->middleware('auth');
      //return redirect('/')->with('error', 'Not Allowded');
        $this->middleware('auth');
        $designation_id = Auth::user()->designation_id;
        //dd($designation_id);
        $user_id = Auth::user()->id;
    
        $scheme_id = $this->scheme_id;
        
        $duty_obj = Configduty::where('user_id', $user_id)->where('scheme_id', $scheme_id)->first();
        //dd($duty_obj);
        if (empty($duty_obj)) {
          return redirect("/")->with('danger', 'Not Allowed');
        }
        
        $mapArr = MapLavel::where('scheme_id', $duty_obj->scheme_id)->where('role_name','Verifier')->first();
        $next_level_role_id=$mapArr->parent_id;
        $type_des='Approved Beneficiary List With No Aadhaar';
        
        //dd($type_des);
        $district_code = $duty_obj->district_code;
        $urban_bodys = collect([]);
        $gps = collect([]);
        $district_list_obj = collect([]);
        $application_type=$request->application_type;
        $where_condition=' where no_aadhar IN (0,1) AND next_level_role_id = 0';
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
          $where_condition .= " AND A.created_by_dist_code=".$district_code." AND A.created_by_local_body_code=" . $created_by_local_body_code . " ";
          $where_condition .= " AND B.created_by_dist_code=".$district_code." AND B.created_by_local_body_code=" . $created_by_local_body_code . " ";
  
          if (!empty($request->block_ulb_code)) {
              $where_condition .= " AND B.block_ulb_code=" . $request->block_ulb_code . " ";
          }
          if (!empty($request->gp_ward_code)) {
              $where_condition .= " AND  B.gp_ward_code=" . $request->gp_ward_code . "";
          }
          if($application_type==1)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id IS NULL";
          }
          else if($application_type==2)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id=1";
          }
          else if($application_type==3)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id=2";
          }
        }
        if ($duty_obj->mapping_level == "Block") {
          $created_by_local_body_code = $duty_obj->taluka_code;
          $is_rural = 2;
          $verifier_type = 'Block';
          $urban_bodys = collect([]);
          $taluka_code = $duty_obj->taluka_code;
          $gps = GP::where('block_code', $taluka_code)->select('gram_panchyat_code', 'gram_panchyat_name')->get();
          $where_condition .= " AND A.created_by_dist_code=".$district_code." AND A.created_by_local_body_code=" . $created_by_local_body_code . " ";
          $where_condition .= " AND B.created_by_dist_code=".$district_code." AND B.created_by_local_body_code=" . $created_by_local_body_code . " ";
          if (!empty($request->gp_ward_code)) {
              $where_condition .= " AND  B.gp_ward_code=" . $request->gp_ward_code . "";
          }
          if($application_type==1)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id IS NULL";
          }
          else if($application_type==2)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id=1";
          }
          else if($application_type==3)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id=2";
          }
        }
        if ($duty_obj->mapping_level == "District") {
          $district_list_obj = District::get();
          $verifier_type = 'District';
          $is_rural = NULL;
          $created_by_local_body_code = NULL;
          $where_condition .= " AND A.created_by_dist_code=".$district_code;
          $where_condition .= " AND B.created_by_dist_code=".$district_code;
          $where_condition .= " AND A.no_aadhar_next_level_role_id=1";
          if($application_type==1)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id=1";
          }
          else if($application_type==3)
          {
            $where_condition .= " AND  no_aadhar_next_level_role_id=2";
          }
         
        }
        if (request()->ajax()) {
          $query = "select * from
          (
          select 
          A.next_level_role_id,A.application_id,
          A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name
          ,mobile_no,aadhar_no,A.jnmp_marked,
          B.gp_ward_code,B.gp_ward_name,B.block_ulb_code,B.block_ulb_name,no_aadhar_next_level_role_id,'0' as is_faulty
          from lb_scheme.ben_personal_details as A JOIN lb_scheme.ben_contact_details as B 
          ON A.application_id=B.application_id 
          ".$where_condition." 
          UNION
          select 
          A.next_level_role_id,A.application_id,
          A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name,
          mobile_no,aadhar_no,A.jnmp_marked,
          B.gp_ward_code,B.gp_ward_name,B.block_ulb_code,B.block_ulb_name,no_aadhar_next_level_role_id,'1' as is_faulty
          from lb_scheme.faulty_ben_personal_details as A LEFT JOIN lb_scheme.faulty_ben_contact_details as B 
          ON A.application_id=B.application_id 
          ".$where_condition." 
         ) as K order by application_id";
        
          // dd($query);
          $data = DB::connection('pgsql_appread')->select($query);
        
  
          // print_r($data);die;
          return datatables()->of($data)
              ->addIndexColumn()
              ->addColumn('action', function ($data) use ($scheme_id, $designation_id,$next_level_role_id) {
                if($designation_id=='Verifier' || $designation_id == 'Delegated Verifier'){
                    $action = '';
                    if(is_null($data->no_aadhar_next_level_role_id)){
                      if($data->jnmp_marked == 1){
                        $action ='Mark due to Janma Mrityu Thathya';
                      }else{
                        $action = '<a href="Viewnoaadhar?application_id=' . $data->application_id  . '&is_faulty=' . $data->is_faulty . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';
                      }
                    }
                    else if($data->no_aadhar_next_level_role_id==1){
                      $action ='Approval Pending';
                    }
                    else if($data->no_aadhar_next_level_role_id==2){
                      $action ='Approved';
                    }
                  } 
                  if($designation_id=='Approver' ||  $designation_id == 'Delegated Approver'){
                    $action = '';
                    
                     if($data->no_aadhar_next_level_role_id==1){
                      $action = '<a href="Viewnoaadhar?application_id=' . $data->application_id  . '&is_faulty=' . $data->is_faulty . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';

                    }
                    else if($data->no_aadhar_next_level_role_id==2){
                      $action ='Approved';
                    }
                  }
                    
                
                return $action;
              })->addColumn('id', function ($data) {
                  return $data->beneficiary_id;
              })
              ->addColumn('name', function ($data) {
                  return $data->ben_name;
              })
              ->addColumn('block_ulb_name', function ($data) {
                  return $data->block_ulb_name;
              })
              ->addColumn('gp_ward_name', function ($data) {
                  return $data->gp_ward_name;
              })
          
              ->addColumn('mobile_no', function ($data) {
  
                  return $data->mobile_no;
              })
              ->addColumn('application_id', function ($data) {
  
                  return $data->application_id;
              }) ->addColumn('aadhar_no', function ($data) {
  
                return $data->aadhar_no;
               })->addColumn('check', function ($data) use ($designation_id) {
                if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
                  if ($data->no_aadhar_next_level_role_id == 1) {
                    // return '<input type="checkbox" name="approvalcheck[]" onClick="controlCheckBox()" value="' . $data->application_id . '">';
                    return '';
                  } else
                    return '';
                } else {
                  return '';
                }
              })
          
              // ->with('completed', $complete)
              ->rawColumns(['id', 'name', 'block_ulb_name', 'gp_ward_name', 'action', 'mobile_no', 'application_id', 'aadhar_no', 'check'])
              ->make(true);
            }
    
        return view(
          'NoAadhaar.List',
          [
            'designation_id' => $designation_id,
            'verifier_type' => $verifier_type,
            'created_by_local_body_code' => $created_by_local_body_code,
            'is_rural' => $is_rural,
            'scheme_id' => $scheme_id,
            'gps' => $gps,
            'urban_bodys' => $urban_bodys,
            'gps' => $gps,
            'district_code' => $district_code,
            'type_des' => $type_des
          ]
        );
      } catch (\Exception $e) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
    }
    public function Viewnoaadhar(Request $request)
    {
      // dd($request->all());
    try{
      $this->middleware('auth');
      $designation_id = Auth::user()->designation_id;
      $user_id = Auth::user()->id;
      $is_faulty = $request->is_faulty;
      
      if (empty($request->application_id)) {
        return redirect("/")->with('danger', 'Application ID Not Found');
      }
    
      if (!is_numeric($request->application_id)) {
        return redirect("/")->with('danger', 'Application ID Not Valid');
      }
      $duty_obj = Configduty::where('user_id', $user_id)->first();
      // dd($duty_obj);
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $type_des='Approved Beneficiary With No Aadhaar';
      $district_code = $duty_obj->district_code;
      if($is_faulty==1)
      {
        $query = DB::table('lb_scheme.faulty_ben_personal_details' . ' AS bp')
        ->leftjoin('lb_scheme.faulty_ben_contact_details' . ' AS bc', 'bc.application_id', '=', 'bp.application_id')
        ->leftjoin('lb_scheme.ben_aadhar_details' . ' AS a', 'a.application_id', '=', 'bp.application_id')
        ->where('bp.created_by_dist_code', $district_code) ->where('bp.application_id', $request->application_id);

      }
      else{
            $query = DB::table('lb_scheme.ben_personal_details' . ' AS bp')
              ->leftjoin('lb_scheme.ben_contact_details' . ' AS bc', 'bc.application_id', '=', 'bp.application_id')
              ->leftjoin('lb_scheme.ben_aadhar_details' . ' AS a', 'a.application_id', '=', 'bp.application_id')
            
              ->where('bp.created_by_dist_code', $district_code)
              ->where('bp.application_id', $request->application_id);
      }
      $query = $query->where('bp.no_aadhar', 1)->where('next_level_role_id', 0);  
      $row = $query->first();
        //  dd($row);
      if (empty($row)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      if($row->jnmp_marked == 1){
        return redirect("/")->with('danger', 'Mark due to JNMP');
      }
      if( $designation_id=='Verifier' || $designation_id == 'Delegated Verifier'){
        // dd($row->aadhar_hash);
          if(!empty($row->aadhar_hash)){
          $encoded_aadhar_old=$row->encoded_aadhar;
          $old_aadhar = Crypt::decryptString($encoded_aadhar_old);
          $new_aadhar='';
          }
        else{
          $old_aadhar='';
          $new_aadhar='';
        }
      }
      else{
        // dd('ok');
        if(!empty($row->old_aadhar_encoded)){
          $encoded_aadhar_old=$row->old_aadhar_encoded;
          $old_aadhar= Crypt::decryptString($encoded_aadhar_old);
          }
        else{
          $old_aadhar='';
        }
        // dd('ok');
        $encoded_aadhar_new=$row->encoded_aadhar;
        //  dd($encoded_aadhar_new);
         $new_aadhar= Crypt::decryptString($encoded_aadhar_new);
        //  dd($new_aadhar);
      }
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
      $doc_man = DocumentType::get(['id', 'doc_name', 'doc_type', 'doc_mime_type', 'doc_size_kb'])->where("id", $doc_type_id)->first();
      $image='';
      $row_image='';
      $image_extension='';
      $decrypt_aadhar_old='';
      $decrypt_aadhar_new='';
      $docs_new='';
      $docs_new='';
      $EncloserModel = new DataSourceCommon;
      $getModelFunc = new getModelFunc();
      $Table = $getModelFunc->getTable('', $this->source_type, 6,0);
      $EncloserModel->setConnection('pgsql_encread');
      $EncloserModel->setTable('' . $Table);
      $encolserdata = $EncloserModel->where('application_id', $request->application_id)->first();
       
      if (!empty($encolserdata)) {
        $mime_type = $encolserdata->document_mime_type;
        $image_extension = $encolserdata->document_extension;
        if ($image_extension != 'png' && $image_extension != 'jpg' && $image_extension != 'jpeg') {
            if ($mime_type == 'image/png') {
                $image_extension = 'png';
            } else if ($mime_type == 'image/jpeg') {
                $image_extension = 'jpg';
            }
        }
        $resultimg = str_replace("data:image/" . $image_extension . ";base64,", "", $encolserdata->attched_document);
        $row_image = "data:image/".$image_extension.";base64,".$encolserdata->attched_document;
        $file_name = $encolserdata->document_type . '_' . $encolserdata->application_id;
        $image= base64_decode($row_image);
      }

      return view(
        'NoAadhaar.ViewBeneficiary',
        [
          'designation_id' => $designation_id,
          'row' => $row,
          'application_id' => $request->application_id,
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
          'is_faulty'=> $is_faulty,
          'reject_revert_cause_list' => $reject_revert_cause_list,
          'old_aadhar' => $old_aadhar,
          'new_aadhar' => $new_aadhar,
        ]
      );
    }
    catch (\Exception $e) {
      return redirect("/")->with('danger', 'Not Allowed');
    }
    }
    public function noaadharPost(Request $request)
    {
      // dd($request->all());
      try{
        $this->middleware('auth');
        $doc_type_id = $this->doc_type_id;
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $is_faulty = $request->is_faulty;
        if (empty($request->application_id)) {
          return redirect("/")->with('danger', 'Application ID Not Found');
        }
        if (!is_numeric($request->application_id)) {
          return redirect("/")->with('danger', 'Application ID Not Valid');
        }
        $duty_obj = Configduty::where('user_id', $user_id)->first();
        if (empty($duty_obj)) {
          return redirect("/")->with('danger', 'Not Allowed');
        }
        $type_des='Approved Beneficiary With No Aadhaar';
        $district_code = $duty_obj->district_code;
        $condition = array();
        $condition['bp.application_id'] = $request->application_id;
        $condition['bp.next_level_role_id'] = 0;
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
        // dd($is_faulty);
        if($is_faulty==0)
        {
          $query = DB::table('lb_scheme.ben_personal_details AS bp')
          ->leftjoin('lb_scheme.ben_aadhar_details' . ' AS a', 'a.application_id', '=', 'bp.application_id')
          ->where($condition);
        }
        else{
          $query = DB::table('lb_scheme.faulty_ben_personal_details AS bp')
          ->leftjoin('lb_scheme.ben_aadhar_details' . ' AS a', 'a.application_id', '=', 'bp.application_id')
          ->where($condition);
        }
        $row = $query->first();
        //  dd( $row);
        if (empty($row)) {
           return back()->withErrors(['Not Allowed']);
          
        }
        if(empty(trim($request->aadhaar_no))){
           return back()->withErrors(['Aadhaar Number Required']);
        }
        else{
          if (strlen(trim($request->aadhaar_no)) !=12)  {
            return back()->withErrors(['Aadhaar Number Invalid']);
          }
          if ($this->isAadharValid(trim($request->aadhaar_no)) == false) {
             return back()->withErrors(['Aadhaar Number Invalid']);
        }
      }
      if(!empty(trim($request->aadhaar_no))){
        $aadhar_hash=md5($request->aadhaar_no);
        $count = DB::table('lb_scheme.ben_aadhar_details')->where('aadhar_hash', trim($aadhar_hash))->count();
        // dd($count);
        if($count > 0)
        {  
          // New Duplicate Check Redirect (within the scheme)
          return back()->withInput()->withErrors(['Duplicate Aadhaar Number present within the scheme']);
        }
        $aadharDupCheckOap = DupCheck::getDupCheckAadhar(10,$request->aadhaar_no);
        if(!empty($aadharDupCheckOap)){
           return back()->withInput()
           ->withErrors(['Duplicate Aadhaar Number present in Old Age Pension Scheme with Beneficiary ID- '.$aadharDupCheckOap.'']);
        }
        $aadharDupCheckJohar = DupCheck::getDupCheckAadhar(1,$request->aadhaar_no);
        if(!empty($aadharDupCheckJohar)){
           return back()->withInput()
           ->withErrors(['Duplicate Aadhaar Number present Jai Johar Pension Scheme with Beneficiary ID- '.$aadharDupCheckJohar.'']);
        }
        $aadharDupCheckBandhu = DupCheck::getDupCheckAadhar(3,$request->aadhaar_no);
        if(!empty($aadharDupCheckBandhu)){
           return back()->withInput()
           ->withErrors(['Duplicate Aadhaar Number present Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- '.$aadharDupCheckBandhu.'']);
        }
      }
      $doc_row = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', $doc_type_id)->first();
      $doc_man = DocumentType::get(['id', 'doc_name', 'doc_type', 'doc_mime_type', 'doc_size_kb'])->where("id", $doc_type_id)->first();
      
      if ($request->file('doc_' . $doc_type_id)) {
        $image_file = $request->file('doc_' . $doc_type_id);
        $img_data = file_get_contents($image_file);
        $image_extension = $image_file->getClientOriginalExtension();
        $mime_type = $image_file->getMimeType();
        $image_size = $image_file->getSize(); 
        $image_size = $image_size / 1024; // Get file size in KB
        if($image_size >  $doc_man->doc_size_kb)
        {
             return back()->withErrors(['File Size must be ' . $doc_man->doc_size_kb . 'KB']);
        } 
        if ($mime_type == 'image/png' || $mime_type == 'image/jpeg' || $mime_type == 'image/jpg'|| $mime_type == 'application/pdf' ) {
          // echo "IF";die;
            $base64 = base64_encode($img_data);
            $is_error=0;
        }
        else{
          $is_error=1;
          return back()->withErrors(['File must be proper format']);
              
        }
        if($is_error==0){
          //dd($row->aadhar_hash);
          if(!empty($row->aadhar_hash)){
            $pre_aadhar=1;
          }
          else{
            $pre_aadhar=0;
          }
          
          $c_time = date('Y-m-d H:i:s', time());
          $getModelFunc = new getModelFunc();
          $pension_details_aadhar = new DataSourceCommon;
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 2, 1);
          $pension_details_aadhar->setTable('' . $Table);
          $pension_details_aadhar->setKeyName('application_id');
          $modelNameAcceptReject = new DataSourceCommon;
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
          $modelNameAcceptReject->setTable('' . $Table);
          $pension_details_encloser2 = new DataSourceCommon;
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 6, 1);
          $pension_details_encloser2->setConnection('pgsql_encwrite');
          $pension_details_encloser2->setTable('' . $Table);
          DB::beginTransaction();
          DB::connection('pgsql_encwrite')->beginTransaction();
            if($pre_aadhar==1){  
                 $is_inserted_arch = DB::connection('pgsql_encwrite')->statement("INSERT INTO lb_scheme.ben_attach_documents_arch(
                application_id, beneficiary_id, document_type, attched_document, 
	              created_by_level, created_at, updated_at, deleted_at, created_by, 
	              ip_address, document_extension, document_mime_type, created_by_dist_code, 
	              created_by_local_body_code,action_by,action_ip_address,action_type)
              SELECT application_id, beneficiary_id, document_type, attched_document, 
	            created_by_level, created_at, updated_at, deleted_at, created_by, 
	            ip_address, document_extension, document_mime_type, created_by_dist_code, 
	            created_by_local_body_code,action_by,action_ip_address,action_type FROM lb_scheme.ben_attach_documents where document_type='".$doc_type_id."' and application_id='".$request->application_id."'");
             try{
              $update_aadhar_arr = array();
              $update_aadhar_arr['encoded_aadhar'] = Crypt::encryptString(trim($request->aadhaar_no));
              $update_aadhar_arr['aadhar_hash'] = md5(trim($request->aadhaar_no));
              $update_aadhar_arr['created_by_level'] = $duty_obj->mapping_level;
              $update_aadhar_arr['created_by'] = $user_id;
              $update_aadhar_arr['ip_address'] = $request->ip();
              $update_aadhar_arr['old_aadhar_encoded'] = $row->encoded_aadhar;
              $update_aadhar_arr['action_by'] = Auth::user()->id;
              $update_aadhar_arr['action_ip_address'] = request()->ip();
              $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
              $is_saved_aadhar=$pension_details_aadhar->where('created_by_dist_code', $district_code)->where('application_id', $request->application_id)->update($update_aadhar_arr);
           
             }catch (\Exception $e) {
              // dd($e);
              DB::rollback();
              DB::connection('pgsql_encwrite')->rollBack();
              return back()->withErrors(['Something Went Wrong!']);
            }
              // dd( $is_saved_aadhar);
        
              $enc_details = array();
              $enc_details['updated_at'] = $c_time;
              $enc_details['attched_document'] = $base64;
              $enc_details['document_extension'] = $image_extension;
              $enc_details['document_mime_type'] = $mime_type;
              $enc_details['ip_address'] = $request->ip();
              $enc_details['action_by'] = Auth::user()->id;
              $enc_details['action_ip_address'] = request()->ip();
              $enc_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
              $enc_status = $pension_details_encloser2->where('application_id',$request->application_id)->where('document_type',$doc_type_id)->update($enc_details);
              $enc_status=1;
            }
            else{
            try{
            $is_inserted_arch =1;
            $pension_details_aadhar->created_at= $c_time;
            $pension_details_aadhar->encoded_aadhar = Crypt::encryptString(trim($request->aadhaar_no));
            $pension_details_aadhar->aadhar_hash = md5(trim($request->aadhaar_no));
            $pension_details_aadhar->application_id =   $request->application_id;
            $pension_details_aadhar->created_by_level = $duty_obj->mapping_level;
            $pension_details_aadhar->created_by = $user_id;
            $pension_details_aadhar->ip_address = $request->ip();
            $pension_details_aadhar->created_by_dist_code = $district_code;
            $pension_details_aadhar->created_by_local_body_code = $created_by_local_body_code;
            $is_saved_aadhar = $pension_details_aadhar->save();
              }catch (\Exception $e) {
                //  dd($e);
                DB::rollback();
                DB::connection('pgsql_encwrite')->rollBack();
                 return back()->withErrors(['Something Went Wrong!!']);
              }
            $enc_details = array();
            $enc_details['application_id'] =  $request->application_id;
              $enc_details['created_at'] = $c_time;
              $enc_details['document_type'] = $doc_type_id;
              $enc_details['attched_document'] = $base64;
              $enc_details['document_extension'] = $image_extension;
              $enc_details['document_mime_type'] = $mime_type;
              $enc_details['created_by_level'] = $duty_obj->mapping_level;
              $enc_details['created_by'] = $user_id;
              $enc_details['ip_address'] = $request->ip();
              $enc_details['created_by_dist_code'] = $district_code;
              $enc_details['created_by_local_body_code'] = $created_by_local_body_code;
              $enc_details['action_by'] = Auth::user()->id;
              $enc_details['action_ip_address'] = request()->ip();
              $enc_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
              $enc_status = $pension_details_encloser2->insert($enc_details);
            }
           
            $inputMain=array();
            $inputMain['action_by'] = Auth::user()->id;
            $inputMain['action_ip_address'] = request()->ip();
            $inputMain['action_type'] = class_basename(request()->route()->getAction()['controller']);
            $inputMain['no_aadhar_next_level_role_id']=1;
            $inputMain['aadhar_no']='********' . substr(trim($request->aadhaar_no), -4);
            //dd($is_faulty);
            if($is_faulty==0)
              {
                  $upadated_main = DB::table('lb_scheme.ben_personal_details')->where(['application_id' => $request->application_id, 'created_by_local_body_code' => $created_by_local_body_code, 'created_by_dist_code' => $district_code])->update($inputMain);

            }
            else{
              try {
                  $upadated_main = DB::table('lb_scheme.faulty_ben_personal_details')->where(['application_id' => $request->application_id, 'created_by_local_body_code' => $created_by_local_body_code, 'created_by_dist_code' => $district_code])->update($inputMain);
              } catch (\Exception $e) {
                dd($e);
              }

            }
        
            $op_type = 'NOAADHARUPDATE';
            $modelNameAcceptReject->created_at =  $c_time;
            $modelNameAcceptReject->op_type =  $op_type;
            $modelNameAcceptReject->application_id = $request->application_id;
            $modelNameAcceptReject->created_by = $user_id;
            $modelNameAcceptReject->created_by_level = $duty_obj->mapping_level;
            $modelNameAcceptReject->created_by_dist_code = $district_code;
            $modelNameAcceptReject->created_by_local_body_code = $created_by_local_body_code;
            $modelNameAcceptReject->ip_address = request()->ip();
            $is_accept_reject = $modelNameAcceptReject->save();
            $ben_fullname=$request->first_name .' '.$request->middle_name.' '.$request->last_name;
            if($is_faulty==0){

              $api_code=4;
            }
            else{

              $api_code=2;
            }
            
            // dump($is_saved_aadhar);dump($upadated_main);dump($is_accept_reject);dd($is_inserted_arch);
            if($is_saved_aadhar && $upadated_main && $is_accept_reject && $enc_status && $is_inserted_arch){
              DB::commit();
              DB::connection('pgsql_encwrite')->commit();
              try {
                $session_lb_aadhaar_no = $this->RationcheckInsertUpdate($district_code,$request->application_id,$ben_fullname,$request->ip(),$request->aadhaar_no,$created_by_local_body_code,$user_id,$request->dob,$api_code);
              } catch (\Exception $e) {
                $inputMain['bio_aadhar_checked_api_failed'] = -1;
                    $upadated_main = DB::table('lb_scheme.ben_personal_details')
                    ->where([
                'application_id' => $request->application_id, 'created_by_local_body_code' => $created_by_local_body_code,
                'created_by_dist_code' => $district_code
              ])->update($inputMain);
              }
              $ben_details =DB::table('lb_scheme.ben_personal_details')->where('application_id',$request->application_id)->first();
              if($ben_details){
                $aadhaar_no_checked=$ben_details->aadhaar_no_checked;
                $aadhaar_no_checked_lastdatetime=$ben_details->aadhaar_no_checked_lastdatetime;
                $aadhaar_no_checked_pass=$ben_details->aadhaar_no_checked_pass;
                $aadhaar_no_validation_msg=$ben_details->aadhaar_no_validation_msg;
              $errors=array();
             $return_text = 'Application with Id:'.$request->application_id.' Aadhaar has been changed Successfully and Sent to Approver for Approval';
            return redirect("noaadharlist")
                ->with('success', $return_text)
                ->with('aadhaar_no_checked', $aadhaar_no_checked)
                ->with('aadhaar_no_checked_lastdatetime', $aadhaar_no_checked_lastdatetime)
                ->with('aadhaar_no_checked_pass', $aadhaar_no_checked_pass)
                ->with('aadhaar_no_validation_msg', $aadhaar_no_validation_msg);
              }

            }
            else{
              DB::rollback();
              DB::connection('pgsql_encwrite')->rollBack();
              // $errorMsg = 'Aadhaar Information Modification Faild.. Please try different.';
              //  array_push($errors, $errorMsg);
              return back()->withErrors(['Aadhaar Information Modification Faild.. Please try different.']);
            }
        }

      }
      else{
        // $errors = array();
        // $errorMsg = $doc_row->doc_name.' Required';
        // array_push($errors, $errorMsg);
        // return redirect("/Viewnoaadhar?appliation_id=".$request->appliation_id."&is_faulty=".$is_faulty)->with('errors', $errorMsg);
         return back()->withInput()
    ->withErrors([$doc_row->doc_name . ' Required']);

      }
      }    
      catch (\Exception $e) {
        // dd($e);
        return redirect("/")->with('danger', 'Not Allowed');
      } 
      
    }
    public function bulkApprove(Request $request)
    {
      try{
      // dd($request->all());
      $this->middleware('auth');
      $designation_id = Auth::user()->designation_id;
      if ($designation_id!='Approver' ||  $designation_id == 'Delegated Approver') {
        return redirect("/")->with('error', 'Not Allowed');
      }
      $user_id = Auth::user()->id;
      $duty_obj = Configduty::where('user_id', $user_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $district_code = $duty_obj->district_code;
      $is_faulty = $request->is_faulty;
      $action_type= $request->action_type;
      $applicationid_arr = array();
      $inputs = request()->input('approvalcheck');
      $c_time = date('Y-m-d H:i:s', time());
      foreach ($inputs as $input) {
        array_push($applicationid_arr, $input);
        
      }
      $back_url = 'noaadharlist'; 
      $comments = NULL;
      $i=0;
      $getModelFunc = new getModelFunc();
      $modelNameAcceptReject = new DataSourceCommon;
      $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
      $modelNameAcceptReject->setTable('' . $Table);
      // dd($Table);
      //  dd($applicationid_arr);
      DB::beginTransaction();
      foreach($applicationid_arr as $application_item){
        if($action_type == 1){
          $op_type = 'NOAADHARAPPROVE';
          $modelNameAcceptReject->created_at =  $c_time;
          $modelNameAcceptReject->op_type =  $op_type;
          $modelNameAcceptReject->application_id = $application_item;
          $modelNameAcceptReject->created_by = $user_id;
          $modelNameAcceptReject->created_by_level = $duty_obj->mapping_level;
          $modelNameAcceptReject->created_by_dist_code = $district_code;
          $modelNameAcceptReject->ip_address = request()->ip();
          $is_accept_reject = $modelNameAcceptReject->save();
            if($is_accept_reject){
              $i++;
            }
        }
        if($action_type == 2){
          $op_type = 'NOAADHARREVERT';
          $modelNameAcceptReject->created_at =  $c_time;
          $modelNameAcceptReject->op_type =  $op_type;
          $modelNameAcceptReject->application_id = $application_item;
          $modelNameAcceptReject->created_by = $user_id;
          $modelNameAcceptReject->created_by_level = $duty_obj->mapping_level;
          $modelNameAcceptReject->created_by_dist_code = $district_code;
          $modelNameAcceptReject->ip_address = request()->ip();
          $is_accept_reject = $modelNameAcceptReject->save();
            if($is_accept_reject){
              $i++;
            }
        }
     
      }
      if($i==count($applicationid_arr)){
        $is_accept_reject = 1;
      }
      else{
        $is_accept_reject = 0;
 
      }
      if($action_type == 1){
        $inputMain['no_aadhar_next_level_role_id']=2;
        $inputMain['no_aadhar']=0;
      }
      if($action_type == 2){
        $inputMain['no_aadhar_next_level_role_id']= NULL;
      }
      $inputMain['action_by'] = Auth::user()->id;
      $inputMain['action_ip_address'] = request()->ip();
      $inputMain['action_type'] = class_basename(request()->route()->getAction()['controller']);
      $upadated_1 = DB::table('lb_scheme.faulty_ben_personal_details')->whereIn('application_id',$applicationid_arr)
          ->where('created_by_dist_code',$district_code)->where('no_aadhar',1)->where('no_aadhar_next_level_role_id',1)->update($inputMain);

      $upadated_2 = DB::table('lb_scheme.ben_personal_details')->whereIn('application_id',$applicationid_arr)
      ->where('created_by_dist_code',$district_code)->where('no_aadhar',1)->where('no_aadhar_next_level_role_id',1)->update($inputMain);
      $upadated_main=$upadated_1+$upadated_2;
    if($upadated_main && $is_accept_reject){
      DB::commit();
      if($action_type == 1){
        // return redirect($back_url)->with('message', 'Applications Aadhaar information change request has been Approved Succesfully!');
        return redirect()->route('noaadharlist')
        ->with('success', 'Applications Aadhaar information change request has been approved successfully!');

      }
      if($action_type == 2){
        return redirect()->route('noaadharlist')
        ->with('success', 'Applications Aadhaar information change request has been sent back to verifier successfully!');

        // return redirect($back_url)->with('message', 'Applications Aadhaar information change request has been Back To Verifier Succesfully!');
      }

    }
    else{
      DB::rollback();
      // return redirect($back_url)->with('error', 'Error! Please try again.');
      return redirect()->route('noaadharlist')
    ->withErrors(['Error! Please try again.']);

    }
      }catch (\Exception $e) {
        dd($e);
        DB::rollback();
        // return redirect($back_url)->with('error', 'Error! Please try again.');
        return redirect()->route('noaadharlist')
        ->withErrors(['Error! Please try again.']);

    }
  }

  public function pdf(Request $request)
  {

    $this->middleware('auth');
        
      $application_id=$request->application_id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
    //$user_id = Auth::user()->id;
    $duty_obj = Configduty::where('user_id', $user_id)->first();
    $district_code = $duty_obj->district_code;
    $getModelFunc = new getModelFunc();
        $DraftPfImageTable = new DataSourceCommon;
      // $Table = $getModelFunc->getTable($district_code, $this->source_type, 11, 1);
      $Table = 'lb_scheme.ben_attach_documents';

        $DraftPfImageTable->setConnection('pgsql_encwrite');
        $DraftPfImageTable->setTable('' . $Table);

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        //$condition['created_by_local_body_code'] = $blockCode;
        //$profileImagedata = DB::table('lb_scheme.ben_attach_documents_temp')->where('application_id', $app_id)->where($condition)->first();
      //$profileImagedata = $DraftPfImageTable->where('application_id', $app_id)->where($condition)->first();
      $profileImagedata = $DraftPfImageTable->where('document_type', $this->doc_type_id)->where('application_id', $application_id)->where($condition)->first();
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
    
    function misReport(Request $request)
    {
      $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
      $base_date  = '2020-01-01';
      $c_time = Carbon::now();
      $c_date = $c_time->format("Y-m-d");
      $is_active = 0;
      $roleArray = $request->session()->get('role');
      $designation_id = Auth::user()->designation_id;
      $district_visible = $is_urban_visible = $block_visible = 1;
      $municipality_visible = 0;
      $gp_ward_visible = 0;
      $muncList = collect([]);
      $gpList = collect([]);
      if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' ||  $designation_id == 'Dashboard') {
          $district_visible = $is_urban_visible = $block_visible = 1;
      } else if ($designation_id == 'Delegated Verifier' ||  $designation_id == 'Delegated Approver' || $designation_id == 'Approver' || $designation_id == 'Verifier') {
          $district_code = NULL;
          $is_urban = NULL;
          $blockCode = NULL;
          foreach ($roleArray as $roleObj) {
              if ($roleObj['scheme_id'] == $this->scheme_id) {
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
      $districts = District::get();
      //$is_urban_visible=0;
      $block_visible=0;
      $municipality_visible=0;
      $gp_ward_visible=0;
      return view(
          'NoAadhaar.misreport',
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
              'muncList' => $muncList,
              'ds_phase_list' => $ds_phase_list,
              'designation_id' => $designation_id
          ]
      );
    }
    public function misReportPost(Request $request)
    {
        //$ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $ds_phase = $request->ds_phase;
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;
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
        }

        if (!empty($block)) {

            if ($urban_code == 1) {
                $block_ulb = SubDistrict::where('sub_district_code', '=', $block)->first();
                $blk_munc_name = $block_ulb->sub_district_name;
                //$block_condition = " and rural_urban_id=1 and created_by_local_body_code=" . $block;
            } else {
                $block_ulb = Taluka::where('block_code', '=', $block)->first();
                $blk_munc_name = $block_ulb->block_name;
                // $block_condition = " and rural_urban_id=2 and  created_by_local_body_code=" . $block;
            }
        } else {
            // $block_condition = "";
        }
        if (!empty($gp_ward)) {

            if ($urban_code == 1) {
                $gp_ward_row = Ward::where('urban_body_ward_code', '=', $gp_ward)->first();
                $gp_ward_name = $gp_ward_row->urban_body_ward_name;
                //$block_condition = " and rural_urban_id=1 and created_by_local_body_code=" . $block;
            } else {
                $gp_ward_row = GP::where('gram_panchyat_code', '=', $gp_ward)->first();
                $gp_ward_name = $gp_ward_row->gram_panchyat_name;
                // $block_condition = " and rural_urban_id=2 and  created_by_local_body_code=" . $block;
            }
        }
        $rules = [
            'ds_phase' => 'nullable|integer',
            'district' => 'nullable|integer',
            'urban_code' => 'nullable|integer',
            'block' => 'nullable|integer',
            'muncid' => 'nullable|integer',
            'gp_ward' => 'nullable|integer',
            'from_date'    => 'nullable|date|after_or_equal:' . $base_date . '|before_or_equal:' . $c_date,
            'to_date'      => 'nullable|date|after_or_equal:from_date|before_or_equal:' . $c_date,
        ];
        $data = array();
        $column = "";
        $attributes = array();
        $messages = array();
        $attributes['ds_phase'] = 'Duare Sarkar Phase';
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['block'] = 'Block/Sub Division';
        $attributes['muncid'] = 'Municipality';
        $attributes['gp_ward'] = 'GP/Ward';
        $attributes['from_date'] = 'From Date';
        $attributes['to_date'] = 'To Date';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $user_msg = "No Aadhaar Mis Report";
            $title = $user_msg;
            //dd($title);

            $data = array();
            $return_status = 1;
            $return_msg = '';
            $heading_msg = '';
            $external = 0;
            $external_arr = array();
            $external_filter = array();
            if (!empty($gp_ward)) {
                if ($urban_code == 1) {
                    $column = "Ward";
                    $heading_msg =  $user_msg . ' of the Ward ' . $gp_ward_name;
                    $data = $this->getWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                } else {
                    $column = "GP";
                    $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                }
            } else if (!empty($muncid)) {
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $caste, $ds_phase);
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                } else if ($urban_code == 2) {
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $data2 = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $data = array_merge($data1, $data2);
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWise(NULL, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);

                    $external = 0;
                }
            }
            if (!empty($caste)) {
                $heading_msg = $heading_msg . " for the Caste  " . $caste;
            }
            if (!empty($ds_phase)) {
                $heading_msg = $heading_msg . " of the " . $ds_phase_list[$ds_phase];
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
   
  
    
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
        
        $query = "select main.location_id,main.location_name,
        COALESCE(bp_main.total,0) as total,
        COALESCE(bp_main.action_pending,0) as action_pending,
        COALESCE(bp_main.approval_pending,0) as approval_pending,
        COALESCE(bp_main.approved,0) as approved,
        COALESCE(rej.rejected,0) as rejected
        from
        (
          select block_code as location_id,block_name as location_name
          from public.m_block  " . $whereMain . "
        ) as main LEFT JOIN
        (
          select 
          sum(total) as total,
          sum(action_pending) as action_pending,
          sum(approval_pending) as approval_pending,
          sum(approved) as approved,
          created_by_local_body_code
          from
          (
          select count(1)  as total,
              count(1) filter(where no_aadhar_next_level_role_id IS NULL and no_aadhar=1) as action_pending,
              count(1) filter(where no_aadhar_next_level_role_id=1 and  no_aadhar=1) as approval_pending,
              count(1) filter(where no_aadhar_next_level_role_id=2 and no_aadhar=0) as approved,
              created_by_local_body_code
              from lb_scheme.ben_personal_details where pre_no_aadhar=1 and next_level_role_id = 0 and created_by_dist_code=".$district_code."
              group by created_by_local_body_code
          UNION
          select count(1)  as total,
              count(1) filter(where no_aadhar_next_level_role_id IS NULL and no_aadhar=1) as action_pending,
              count(1) filter(where no_aadhar_next_level_role_id=1 and  no_aadhar=1) as approval_pending,
              count(1) filter(where no_aadhar_next_level_role_id=2 and no_aadhar=0) as approved,
              created_by_local_body_code 
              from lb_scheme.faulty_ben_personal_details where pre_no_aadhar=1 and created_by_dist_code=".$district_code."
              group by created_by_local_body_code
          ) as P group by created_by_local_body_code
        ) as bp_main ON main.location_id=bp_main.created_by_local_body_code
        left join
        (
            select count(1) as rejected,
            created_by_local_body_code
            from lb_scheme.ben_reject_details where pre_no_aadhar=1 and created_by_dist_code=".$district_code."
            group by created_by_local_body_code
        ) as rej ON main.location_id=rej.created_by_local_body_code
        order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
        
        $query = "select main.location_id,main.location_name,
        COALESCE(bp_main.total,0) as total,
        COALESCE(bp_main.action_pending,0) as action_pending,
        COALESCE(bp_main.approval_pending,0) as approval_pending,
        COALESCE(bp_main.approved,0) as approved,
        COALESCE(rej.rejected,0) as rejected
        from
        (
          select sub_district_code as location_id,sub_district_name as location_name
          from public.m_sub_district  " . $whereMain . " 
        ) as main LEFT JOIN
        (
          select 
          sum(total) as total,
          sum(action_pending) as action_pending,
          sum(approval_pending) as approval_pending,
          sum(approved) as approved,
          created_by_local_body_code
          from
          (
          select count(1)  as total,
              count(1) filter(where no_aadhar_next_level_role_id IS NULL and no_aadhar=1) as action_pending,
              count(1) filter(where no_aadhar_next_level_role_id=1 and  no_aadhar=1) as approval_pending,
              count(1) filter(where no_aadhar_next_level_role_id=2 and no_aadhar=0) as approved,
              created_by_local_body_code
              from lb_scheme.ben_personal_details where pre_no_aadhar=1 and next_level_role_id = 0 and created_by_dist_code=".$district_code."
              group by created_by_local_body_code
          UNION
          select count(1)  as total,
              count(1) filter(where no_aadhar_next_level_role_id IS NULL and no_aadhar=1) as action_pending,
              count(1) filter(where no_aadhar_next_level_role_id=1 and  no_aadhar=1) as approval_pending,
              count(1) filter(where no_aadhar_next_level_role_id=2 and no_aadhar=0) as approved,
              created_by_local_body_code
              from lb_scheme.faulty_ben_personal_details where pre_no_aadhar=1 and created_by_dist_code=".$district_code."
              group by created_by_local_body_code
          ) as P group by created_by_local_body_code
        ) as bp_main ON main.location_id=bp_main.created_by_local_body_code
        left join
        (
            select count(1) as rejected,
            created_by_local_body_code
            from lb_scheme.ben_reject_details where pre_no_aadhar=1 and created_by_dist_code=".$district_code."
            group by created_by_local_body_code
        ) as rej ON main.location_id=rej.created_by_local_body_code
        order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        
        $query = "select main.location_id,main.location_name,
        COALESCE(bp_main.total,0) as total,
        COALESCE(bp_main.action_pending,0) as action_pending,
        COALESCE(bp_main.approval_pending,0) as approval_pending,
        COALESCE(bp_main.approved,0) as approved,
        COALESCE(rej.rejected,0) as rejected
        from
        (
        select district_code as location_id,district_name as location_name
        from public.m_district  
        ) as main LEFT JOIN
        (
          select 
          sum(total) as total,
          sum(action_pending) as action_pending,
          sum(approval_pending) as approval_pending,
          sum(approved) as approved,
          created_by_dist_code
          from
          (
          select count(1)  as total,
              count(1) filter(where no_aadhar_next_level_role_id IS NULL and no_aadhar=1) as action_pending,
              count(1) filter(where no_aadhar_next_level_role_id=1 and  no_aadhar=1) as approval_pending,
              count(1) filter(where no_aadhar_next_level_role_id=2 and no_aadhar=0) as approved,
              created_by_dist_code
              from lb_scheme.ben_personal_details where pre_no_aadhar=1 and next_level_role_id = 0 
              group by created_by_dist_code
          UNION
          select count(1)  as total,
              count(1) filter(where no_aadhar_next_level_role_id IS NULL and no_aadhar=1) as action_pending,
              count(1) filter(where no_aadhar_next_level_role_id=1 and  no_aadhar=1) as approval_pending,
              count(1) filter(where no_aadhar_next_level_role_id=2 and no_aadhar=0) as approved,
              created_by_dist_code
              from lb_scheme.faulty_ben_personal_details where pre_no_aadhar=1
              group by created_by_dist_code
          ) as P group by created_by_dist_code
        ) as bp_main ON main.location_id=bp_main.created_by_dist_code
        left join
        (
            select count(1) as rejected,
            created_by_dist_code
            from lb_scheme.ben_reject_details where pre_no_aadhar=1
            group by created_by_dist_code 
        ) as rej ON main.location_id=rej.created_by_dist_code
        order by main.location_name";

        // echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    public function generate_excel(Request $request)
    {
      try{
      $user_id = Auth::user()->id;
      //dd($user_id);
      $duty_obj = Configduty::where('user_id', $user_id)->where('scheme_id', $this->scheme_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $district_code=$duty_obj->district_code;
      if (empty($district_code)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $query = "select * from
      (
      select A.application_id,A.beneficiary_id,ben_fname,ben_mname,ben_lname,dob,father_fname,father_mname,father_lname,mother_fname,mother_mname,mother_lname,
      mobile_no,
      gp_ward_name,block_ulb_name,house_premise_no,village_town_city,no_aadhar_next_level_role_id,no_aadhar,'1' as status from lb_scheme.ben_personal_details as A LEFT JOIN lb_scheme.ben_contact_details as B ON A.application_id=B.application_id 
      where A.created_by_dist_code=".$district_code." and B.created_by_dist_code=".$district_code." and A.pre_no_aadhar=1 AND A.next_level_role_id = 0
      UNION
      select A.application_id,A.beneficiary_id,ben_fname,ben_mname,ben_lname,dob,father_fname,father_mname,father_lname,mother_fname,mother_mname,mother_lname,mobile_no,
      gp_ward_name,block_ulb_name,house_premise_no,village_town_city,no_aadhar_next_level_role_id,no_aadhar,'2' as status from lb_scheme.faulty_ben_personal_details as A 
      LEFT JOIN lb_scheme.faulty_ben_contact_details as B ON A.application_id=B.application_id 
      where A.created_by_dist_code=".$district_code." and A.pre_no_aadhar=1
      UNION
      select application_id,beneficiary_id,ben_fname,ben_mname,ben_lname,dob,father_fname,father_mname,father_lname,mother_fname,mother_mname,mother_lname,mobile_no,
      gp_ward_name,block_ulb_name,house_premise_no,village_town_city,'0' as no_aadhar_next_level_role_id,'0' as no_aadhar,'3' as status from lb_scheme.ben_reject_details 
      where created_by_dist_code=".$district_code." and pre_no_aadhar=1
        ) as P order by gp_ward_name,block_ulb_name,ben_fname";
      $result = DB::connection('pgsql_appwrite')->select($query);
      //dd($result);
      $filename = 'NoAadhaar Beneficiary List'.$district_code . "-" . date('d/m/Y') . '-' . time() . ".xls";
      header("Content-Type: application/xls");
      header("Content-Disposition: attachment; filename=" . $filename);
      header("Pragma: no-cache");
      header("Expires: 0");
      echo '<table border="1">';
      echo '<tr><td colspan="10">Lakshmir Bhandar No Aadhaar Beneficiary List</td></tr>';
      echo '<tr><th>Applicant Id</th><th>Beneficiary Name</th><th>Mobile No.</th><th>DOB.</th><th>Father\'s Name</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>House Premise No</th><th>Status</th></tr>';
      if (count($result) > 0) {
        foreach ($result as $row) {
          if (!empty($row->ben_fname)) {
            $ben_fname = trim($row->ben_fname);
        } else {
            $ben_fname = '';
        }
        if (!empty($row->ben_mname)) {
            $ben_mname = trim($row->ben_mname);
        } else {
            $ben_mname = '';
        }
        if (!empty($row->ben_lname)) {
          $ben_lname = trim($row->ben_lname);
       } else {
          $ben_lname = '';
      }
      $ben_fullname = $ben_fname . " " . $ben_mname . " " . $ben_lname;
      if (!empty($row->mobile_no)) {
        $ben_mobile_no = $row->mobile_no;
    
      } else {
        $ben_mobile_no = '';
      }
      if (!empty($row->dob)) {
        $ben_dob = $row->dob;
      } else {
        $ben_dob = '';
        $ben_age='';
      }
       
      if (!empty($row->father_fname)) {
          $father_fname = trim($row->father_fname);
      } else {
          $father_fname = '';
      }
      if (!empty($row->father_mname)) {
          $father_mname = trim($row->father_mname);
      } else {
          $father_mname = '';
      }
      if (!empty($row->father_lname)) {
          $father_lname = trim($row->father_lname);
      } else {
          $father_lname = '';
      }
      $father_fullname = $father_fname . " " . $father_mname . " " . $father_lname;
      if (!empty($row->block_ulb_name)) {
        $block_ulb_name = trim($row->block_ulb_name);
      }
      else{
        $block_ulb_name='';
      }
      if (!empty($row->gp_ward_name)) {
        $gp_ward_name = trim($row->gp_ward_name);
      }
      else{
        $gp_ward_name='';
      }
      if (!empty($row->village_town_city)) {
        $village_town_city = trim($row->village_town_city);
      }
      else{
        $village_town_city='';
      }
      if (!empty($row->house_premise_no)) {
        $house_premise_no = trim($row->house_premise_no);
      }
      else{
        $house_premise_no='';
      }
      $status='';
      if($row->status==3){
        $status='Rejected';
      }
      else{
         if(is_null($row->no_aadhar_next_level_role_id) && $row->no_aadhar==1){
          $status='Yet to be Action';
         }
         else if($row->no_aadhar_next_level_role_id==1 && $row->no_aadhar==1){
          $status='Verified but Approval Pending';
         }
         else if($row->no_aadhar_next_level_role_id==2 && $row->no_aadhar==0){
          $status='Verified and Approved';
         }
      }
      echo "<tr><td>" . $row->application_id . "</td><td>" . $ben_fullname . "</td><td>" . $ben_mobile_no . "</td><td>" . $ben_dob . "</td><td>" . $father_fullname . "</td><td>" . trim($block_ulb_name) . "</td><td>" . trim($gp_ward_name) . "</td><td>" . trim($village_town_city) . "</td><td>" . trim($house_premise_no) . "</td><td>" . $status . "</td></tr>";

      }
      }else {
        echo '<tr><td colspan="10">No Records found</td></tr>';
      }
      echo '</table>';
    } catch (\Exception $e) {
      dd($e);
      return redirect("/")->with('danger', 'Not Allowed');
    }
    }
   
    
}
