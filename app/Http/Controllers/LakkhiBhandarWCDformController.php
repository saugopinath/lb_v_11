<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheme;

use App\Models\District;
use App\Models\UrbanBody;
use App\Models\DocumentType;
use App\Models\SchemeDocMap;
use App\Models\Assembly;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\GP;
use App\Models\User;
use Redirect;
use Auth;
use Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use App\Models\RejectRevertReason;
use App\Models\DistrictEntryMapping;
use App\Models\DsPhase;

class LakkhiBhandarWCDformController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set('Asia/Kolkata');
        $phaseArr = DsPhase::where('is_current', TRUE)->first();
        //$mydate = $phaseArr->base_dob;
        $mydate = date('Y-m-d');
        $max_date = strtotime("-25 year", strtotime($mydate));
        $max_date = date("Y-m-d", $max_date);
        $min_date = strtotime("-60 year", strtotime($mydate));
        $min_date = date("Y-m-d", $min_date);
        $this->base_dob_chk_date = $mydate;
        $this->max_dob = $max_date;
        $this->min_dob = $min_date;
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
     public function applicantList(Request $request)
    {
        // dd('ok');
        if(is_null($request->list_type)){
          return redirect("/")->with('error', 'Undefine Report');
        }
        $list_type=$request->list_type;
        if (!in_array($list_type, array(1, 2,3))) {
         return redirect("/")->with('error', 'Undefine Report');
        }
        
        $ds_phase_list = DsPhase::orderBy('phase_code','DESC')->get();
        $cur_ds_phase_arr = $ds_phase_list->where('is_current', TRUE)->first();
        if (!empty($cur_ds_phase_arr)) {
            $cur_ds_phase = $cur_ds_phase_arr->phase_code;
        }
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $is_active = 0;
        $munc_list=collect([]);
        $gp_list=collect([]);
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $distCode = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                    $munc_list=UrbanBody::where('sub_district_code',$blockCode)->get();
                } else {
                    $blockCode = $roleObj['taluka_code'];
                    $gp_list=GP::where('block_code',$blockCode)->get();
                }
                break;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        if (!in_array($designation_id, array('Operator'))) {
            return redirect("/")->with('error', 'Not Allowed');
        }
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        
        $errormsg = Config::get('constants.errormsg');
        if($list_type==1){
         $report_type='Draft';
         $condition["is_final"] = false;

        }
        if($list_type==2){
          $report_type='Submitted';
          $condition["is_final"] = true;
        }
        if($list_type==3){
          $report_type='Reverted';
          $condition["is_final"] = true;
        }
        return view(
            'LbAppList.appList',
            [
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'reject_revert_reason' => $reject_revert_reason,
                'ds_phase_list' => $ds_phase_list,
                'list_type' => $list_type,
                'report_type' => $report_type,
                'is_urban' => $is_urban,
                'munc_list' => $munc_list,
                'gp_list' => $gp_list,

            ]
        );
       
    }
    public function applicantListDatatable(Request $request)
    {
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $is_active = 0;
        $munc_list=collect([]);
        $gp_list=collect([]);
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $distCode = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                    $munc_list=UrbanBody::where('sub_district_code',$blockCode)->get();
                } else {
                    $blockCode = $roleObj['taluka_code'];
                    $gp_list=GP::where('block_code',$blockCode)->get();
                }
                break;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        if (!in_array($designation_id, array('Operator'))) {
            return redirect("/")->with('error', 'Not Allowed');
        }
       if(is_null($request->list_type)){
          return redirect("/")->with('error', 'Undefine Report');
        }
        $list_type=$request->list_type;
        if (!in_array($list_type, array(1, 2,3))) {
         return redirect("/")->with('error', 'Undefine Report');
        }
        
        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $personal_table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
        $contact_table = $getModelFunc->getTable($distCode, '', 3, 1);
        $modelName->setConnection('pgsql_appread');
        $modelName->setTable('' . $personal_table);
        $condition = array();
        $condition[$personal_table . ".created_by_dist_code"] = $distCode;
        $condition[$personal_table . ".created_by_local_body_code"] = $blockCode;

    
        if($list_type==1){
         $report_type='Draft';
         $condition["is_final"] = false;

        }
        if($list_type==2){
          $report_type='Submitted';
          $condition["is_final"] = true;
        }
        if($list_type==3){
          $report_type='Reverted';
          $condition["is_final"] = true;
        }
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        
        //$entry_allowed_main = BlkUrbanlEntryMapping::where('main_entry', true)->where('block_ulb_code',  $blockCode)->count();
       
       
       
            if (!empty($request->search['value']))
                $serachvalue = trim($request->search['value']);
            else
                $serachvalue = '';
            $limit = (int) $request->input('length',10);
            $offset = (int) $request->input('start',);

            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();
            $ds_phase = trim($request->ds_phase);
            $munc_id = trim($request->munc_id);
            $gp_ward_id = trim($request->gp_ward_id);
            
            if (!empty($munc_id)) {
                            $condition[$contact_table . ".block_ulb_code"] = $munc_id;
            }
            if (!empty($gp_ward_id)) {
                            $condition[$contact_table . ".gp_ward_code"] = $gp_ward_id;
            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
            if ($ds_phase!='') {
                if($ds_phase==0){  
                 $query =$query->whereRaw(" (".$personal_table." .ds_phase IS NULL or ".$personal_table.".ds_phase=0) ");
                }
                else{
                    $query =$query->whereRaw(" (".$personal_table.".ds_phase=".$ds_phase.") ");
                    //$condition[$personal_table . ".ds_phase"] = intval($ds_phase);
                }
            }
            if($list_type==1){
             $data = $query->whereNull('next_level_role_id');
            }
            if($list_type==2){
               $data = $query->whereNull('next_level_role_id');
            }
            if($list_type==3){
                $data = $query->where('next_level_role_id',-50);
            }
            
           
            if (empty($serachvalue)) {
                $totalRecords = $query->count($personal_table . '.application_id');
                $data = $query->orderBy('ben_fname')->offset($offset)->limit($limit)->get([
                 '' . $personal_table . '.created_by_dist_code as created_by_dist_code', 
                 '' . $personal_table . '.application_id as application_id', 
                'ben_fname',  'father_fname', 'father_mname', 'father_lname', 'mobile_no'
                ]);

                $filterRecords = $totalRecords;
                //dump($limit);
                // dump($offset);
                // dump($totalRecords);
                // dump($filterRecords);
                //dd($data->toArray());
            } else {
                //dd($query->toSql());
                if (is_numeric($serachvalue)) {
                     $query = $query->where(function ($query1) use ($serachvalue,$personal_table) {
                        $query1->where($personal_table . '.application_id',  $serachvalue);
                    });
                    $totalRecords = $query->count($personal_table . '.application_id');
                    $data = $query->offset($offset)->limit($limit)->get(
                       [
                    '' . $personal_table . '.created_by_dist_code as created_by_dist_code', 
                    '' . $personal_table . '.application_id as application_id', 
                    'ben_fname',  'father_fname', 'father_mname', 'father_lname', 'mobile_no'
                    ]
                    );
                    //$filterRecords = $totalRecords;
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('ben_fname', 'ilike', $serachvalue . '%');
                    });
                    $totalRecords = $query->count('ss_ben_id');
                    $data = $query->orderBy('ben_fname')->offset($offset)->limit($limit)->get(
                        [
                                '' . $personal_table . '.created_by_dist_code as created_by_dist_code', 
                    '' . $personal_table . '.application_id as application_id', 
                    'ben_fname',  'father_fname', 'father_mname', 'father_lname', 'mobile_no'
                        ]
                    );
                    //$filterRecords = $totalRecords;
                }
                
                $filterRecords = count($data);
            }
            //dd($data->toArray());
            $datatable=datatables()->of($data)
               
                ->addColumn('name', function ($data) {
                    return trim($data->ben_fname);
                })->addColumn('application_id', function ($data) {


                    return $data->application_id;
                })->addColumn('adharcardno', function ($data) {
                    if (!empty($data->aadhar_no)) {
                        return ($data->aadhar_no);
                    } else
                        return '';
                })->addColumn('father_name', function ($data) {
                    return $data->fathername;
                })->addColumn('status', function ($data) {
                    $status_msg = 'In-Progress';
                    $status = '<span class="label label-warning">' . $status_msg . '</span>';
                    return $status;
                })->addColumn('Action', function ($data) {


                   // $action = '<a href="lb-entry-draft-edit?application_id=' . $data->application_id . '" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>&nbsp;&nbsp;&nbsp;&nbsp<button value="' . $data->application_id . '" id="rej_' . $data->application_id . '" class="btn btn-danger btn-sm rej-btn" type="button">Reject</button>';

                   $action = '<a href="/lb-entry-draft-edit?application_id=' . $data->application_id . '" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>&nbsp;&nbsp;&nbsp;&nbsp<button value="' . $data->application_id . '" id="rej_' . $data->application_id . '" class="btn btn-danger btn-sm rej-btn" type="button">Reject</button>';

                    return $action;
                })->addColumn('mobile_no', function ($data) {
                    if (!empty($data->mobile_no)) {
                        return ($data->mobile_no);
                    } else
                        return '';
                })
                ->rawColumns(['Action', 'id', 'name', 'status', 'mobile_no', 'application_id'])
                ->make(true);
               // dump($data->toArray());
                return $datatable;
            
       
    }

    function viewimage(Request $request)
    {
        $scheme_id = $this->scheme_id;
        $source_type = $this->source_type;
        $roleArray = $request->session()->get('role');
        $is_active = 0;
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                $distCode = $roleObj['district_code'];
                $is_urban = $roleObj['is_urban'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                } else {
                    $blockCode = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled');
        }
        $getModelFunc = new getModelFunc();
        $DraftPfImageTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 5, 1);
        $DraftPfImageTable->setConnection('pgsql_encread');

        $DraftPfImageTable->setTable('' . $Table);
        $DraftEncloserTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 6, 1);
        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);

        if (!empty($request->is_profile_pic))
            $is_profile_pic = $request->is_profile_pic;
        else
            $is_profile_pic = 0;
        $application_id = $request->application_id;
        //dd($request->toArray());
        if (!in_array($is_profile_pic, array(0, 1))) {
            $return_text = 'Parameter Not Valid';
            return redirect("/")->with('error',  $return_text);
        }
        if (empty($application_id)) {
            $return_text = 'Parameter Not Valid';
            return redirect("/")->with('error',  $return_text);
        }
        $user_id = Auth::user()->id;
        $condition = array();
        $condition['created_by_dist_code'] = $distCode;
        $condition['created_by_local_body_code'] = $blockCode;
        if ($is_profile_pic == 1) {

            $profileImagedata = $DraftPfImageTable->where('image_type', $request->id)->where('application_id', $request->application_id)->where($condition)->first();
            if (empty($profileImagedata->application_id)) {
                $return_text = 'Parameter Not Valid';
                return redirect("/")->with('error',  $return_text);
            }
            $mime_type = $profileImagedata->image_mimetype;
            $image_extension = $profileImagedata->image_extension;
            if ($image_extension != 'png' && $image_extension != 'jpg' && $image_extension != 'jpeg') {
                if ($mime_type == 'image/png') {
                    $image_extension = 'png';
                } else if ($mime_type == 'image/jpeg') {
                    $image_extension = 'jpg';
                }
            }
            $resultimg = str_replace("data:image/" . $image_extension . ";base64,", "", $profileImagedata->profile_image);
            $file_name = $profileImagedata->image_type . '_' . $profileImagedata->application_id;
            header('Content-Disposition: attachment;filename="' . $file_name . '.' . $image_extension . '"');
            header('Content-Type: ' . $mime_type);
            echo base64_decode($resultimg);
        } else {
            $encolserData = $DraftEncloserTable->where('document_type', $request->id)->where('application_id', $request->application_id)->where($condition)->first();
            if (empty($encolserData->application_id)) {
                $return_text = 'Parameter Not Valid';
                return redirect("/")->with('error',  $return_text);
            }
            $mime_type = $encolserData->document_mime_type;
            $file_extension = $encolserData->document_extension;
            if ($file_extension != 'png' && $file_extension != 'jpg' && $file_extension != 'jpeg' && $file_extension != 'pdf') {
                if ($mime_type == 'image/png') {
                    $file_extension = 'png';
                } else if ($mime_type == 'image/jpeg') {
                    $file_extension = 'jpg';
                } else if ($mime_type == 'application/pdf') {
                    $file_extension = 'pdf';
                }
            }
              try {
            if (strtoupper($file_extension) == 'PNG' || strtoupper($file_extension) == 'JPG' || strtoupper($file_extension) == 'JPEG') {
                $resultimg = str_replace("data:image/" . $file_extension . ";base64,", "", $encolserData->attched_document);
                //dd($resultimg);
                $file_name = $encolserData->document_type . '_' . $encolserData->beneficiary_id;
                ob_start();
                header('Content-Disposition: attachment;filename="' . $file_name . '.' . $file_extension . '"');
                header('Content-Type: ' . $mime_type);
                ob_clean();
                echo base64_decode($resultimg);
            } else if (strtoupper($file_extension) == 'PDF') {
                $decoded = base64_decode($encolserData->attched_document);
                $file_name = $encolserData->document_type . '_' . $encolserData->beneficiary_id . '.pdf';
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
    }
     public function partialReject(Request $request)
    {
        try {
            $is_active = 0;
            $scheme_id = $this->scheme_id;
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $errormsg = Config::get('constants.errormsg');
            $roleArray = $request->session()->get('role');
            foreach ($roleArray as $roleObj) {
                if ($roleObj['scheme_id'] == $scheme_id) {
                    $is_active = 1;
                    $is_urban = $roleObj['is_urban'];
                    $district_code = $roleObj['district_code'];
                    $mapping_level = $roleObj['mapping_level'];
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                        $urban_code = 1;
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                        $urban_code = 2;
                    }
                    break;
                }
            }
            if ($is_active == 0 || empty($district_code)) {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            if ($designation_id != 'Operator') {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            $application_id = $request->application_id;
            $reject_cause = $request->reject_cause;
            if (empty($application_id)) {
                return redirect("/")->with('error', ' Application Id Not Found');
            }
            if(is_null($request->list_type)){
                    return redirect("/")->with('error', 'Undefine Report');
            }
            $list_type=$request->list_type;
            if (!in_array($list_type, array(1, 2,3))) {
                    return redirect("/")->with('error', 'Undefine Report');
            }
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $personal_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
            $personal_model->setTable('' . $Table);
            $row = $personal_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $blockCode)->first();
            $bank_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 4, 1);
            $bank_model->setTable('' . $Table);
            $row_bank = $bank_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $blockCode)->first();

            $ds_phase=$row->ds_phase;
            $url='lb-applicant-list/'.$list_type;
           
            if (empty($row->application_id)) {
                return redirect("/".$url)->with('error', 'Application Id not valid');
            }
           
            $cnt = RejectRevertReason::where('id', $reject_cause)->count();
            if ($cnt == 0) {
                return redirect("/".$url)->with('error', 'Rejection Cause not valid');
            }
            $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
            DB::beginTransaction();
            try {
                $input = ['rejected_cause' => $reject_cause,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
];
                $is_status_updated = $personal_model->where('application_id', $application_id)->update($input);
                $accept_reject_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                $accept_reject_model->setTable('' . $Table);
                $accept_reject_model->op_type = 'PR';
                $accept_reject_model->application_id = $row->application_id;
                $accept_reject_model->designation_id = $designation_id;
                $accept_reject_model->scheme_id = $scheme_id;
                $accept_reject_model->user_id = $user_id;
                $accept_reject_model->mapping_level = $mapping_level;
                $accept_reject_model->created_by = $user_id;
                $accept_reject_model->created_by_level = $mapping_level;
                $accept_reject_model->created_by_dist_code = $district_code;
                $accept_reject_model->created_by_local_body_code = $blockCode;
                $accept_reject_model->rejected_reverted_cause = $reject_cause;
                $accept_reject_model->ip_address = request()->ip();
                $is_saved = $accept_reject_model->save();
                //$reject_fun_arr = DB::select("select lb_scheme.beneficiary_rejected_partial(" . $in_pension_id . ")");
                $reject_fun_arr = DB::select("select lb_scheme.beneficiary_rejected_partial(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                $beneficiary_rejected_partial = $reject_fun_arr[0]->beneficiary_rejected_partial;
                
                if($is_saved && $beneficiary_rejected_partial==1){
                    DB::commit();
                    return redirect("/".$url)->with('success', 'Application has been successfully rejected')->with('id', $application_id);
                }
               else{
                DB::rollback();
                return redirect("/".$url)->with('error', $errormsg['roolback']);
               }
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                return redirect("/".$url)->with('error', $errormsg['roolback']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return redirect("/".$url)->with('error', $errormsg['roolback']);
        }
    }

  
}
