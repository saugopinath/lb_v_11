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
use Yajra\DataTables\Facades\DataTables;

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
        try {
        // dd('ok');
        if (is_null($request->list_type)) {
            return redirect("/")->with('error', 'Undefine Report');
        }
        $list_type = (int) $request->list_type;
        if (!is_int($list_type)) {
            //dd('ok');
             return redirect("/")->with('error', 'Undefine Report');
        }
        $report_type_arr=DB::table('public.m_list_report_type')->where('id',$list_type)->first();
        if (empty($report_type_arr)) {
            return redirect("/")->with('error', 'Undefine Report');
        }
       
        $ds_phase_list = DsPhase::orderBy('phase_code', 'DESC')->get();
        $cur_ds_phase_arr = $ds_phase_list->where('is_current', TRUE)->first();
        if (!empty($cur_ds_phase_arr)) {
            $cur_ds_phase = $cur_ds_phase_arr->phase_code;
        }
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $is_active = 0;
        $block_munc_list = collect([]);
        $block_munc_visible=0;
        $gp_ward_list = collect([]);
        $distCode='';
        $is_rural_visible=0;
        $block_ulb_code='';
        $block_munc_text='Block/Municipality';
        $gp_ward_text='GP/WARD';
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                if($mapping_level=='District'){
                  $is_rural_visible=1;
                  $block_munc_visible=1;
                }
                $is_urban = $roleObj['is_urban'];
                $distCode = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $block_munc_visible=1;
                    $block_munc_text='Municipality';
                    $gp_ward_text='WARD';
                    $blockCode = $roleObj['urban_body_code'];
                    $block_munc_list = UrbanBody::select('urban_body_code as code','urban_body_name as name')->where('sub_district_code', $blockCode)->get();
                } else if ($roleObj['is_urban'] == 2) {
                    $gp_ward_text='GP';
                    $blockCode = $roleObj['taluka_code'];
                    $block_ulb_code=$blockCode;
                    $gp_ward_list = GP::select('gram_panchyat_code as code','gram_panchyat_name as name')->where('block_code', $blockCode)->get();
                }
                break;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        if (in_array($list_type, array(1,2,3)) && !in_array($designation_id, array('Operator'))) {
            return redirect("/")->with('error', 'Not Allowed');
        }
        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $personal_table =  'lb_scheme.'.$report_type_arr->p_table_name;
        $contact_table =  'lb_scheme.'.$report_type_arr->c_table_name;
        $report_type = $report_type_arr->report_name;
        $base_condition = $report_type_arr->base_condition;
        $edit_button_visible = $report_type_arr->edit_button_visible;
        $modelName->setConnection('pgsql_appread');
        $modelName->setTable('' . $personal_table);
        $condition = array();
        $condition[$personal_table . ".created_by_dist_code"] = $distCode;
        if(in_array($designation_id, array('Operator','Verifier','Delegated Verifier'))){
            $condition[$personal_table . ".created_by_local_body_code"] = $blockCode;
        }
       
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        // dd($condition);
        //$entry_allowed_main = BlkUrbanlEntryMapping::where('main_entry', true)->where('block_ulb_code',  $blockCode)->count();
      
       if (request()->ajax()) {
            $searchValue = trim($request->search['value'] ?? '');
            $ds_phase    = trim($request->ds_phase ?? '');
            $rural_urbanid     = trim($request->rural_urbanid ?? '');
            $block_ulb_code     = trim($request->block_ulb_code ?? '');
            $gp_ward_code  = trim($request->gp_ward_code ?? '');
            if (in_array($list_type, array(7,10))) {
              $query = $modelName
                ->where($condition)
                ->select([
                    $personal_table . '.created_by_dist_code as created_by_dist_code',
                    $personal_table . '.application_id as application_id',
                    $personal_table . '.ben_fname as ben_fname',
                    $personal_table . '.father_fname as father_fname',
                    $personal_table . '.mobile_no as mobile_no',

                ]);
            }
           else{
                $query = $modelName
                ->where($condition)
                ->leftJoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id')
                ->select([
                    $personal_table . '.created_by_dist_code as created_by_dist_code',
                    $personal_table . '.application_id as application_id',
                    $personal_table . '.ben_fname as ben_fname',
                    $personal_table . '.father_fname as father_fname',
                    $personal_table . '.mobile_no as mobile_no',

                ]);
             }

           
            $query->whereRaw(" ($base_condition) ");
            if ($ds_phase !== '') {
                if($ds_phase==0){
                 $query->whereRaw(" (".$personal_table.".ds_phase=0 or ".$personal_table.".ds_phase IS NULL");
                }
                $query->whereRaw(" (".$personal_table.".ds_phase=".$ds_phase." or ".$personal_table.".mark_ds_phase=".$ds_phase."");
            }
            if (!empty($rural_urbanid)) {
                $query->where($contact_table . ".rural_urban_id", $rural_urbanid);
            }
            if (!empty($block_ulb_code)) {
                $query->where($contact_table . ".block_ulb_code", $block_ulb_code);
            }
            if (!empty($gp_ward_code)) {
                $query->where($contact_table . ".gp_ward_code", $gp_ward_code);
            }
// dd($query->toSql());
            // Yajra v12 DataTables (NO manual offset/limit/count)
            return DataTables::eloquent($query)
                ->filter(function ($q) use ($searchValue, $personal_table) {
                    if ($searchValue == '') {
                        return;
                    }   

                    // $sv = trim($searchValue);
                    // if (is_numeric($sv)) {
                    //     $q->where(function ($sub) use ($sv, $personal_table) {
                    //         $sub->where("{$personal_table}.application_id", (int)$sv)
                    //             ->orWhereRaw("CAST({$personal_table}.mobile_no AS TEXT)", [$sv]);
                    //     });
                    // } else {
                    //     $q->where(function ($sub) use ($sv, $personal_table) {
                    //         $sub->where("{$personal_table}.ben_fname", 'ilike', $sv . '%');
                    //     });
                    // }

                    
                    if (is_numeric($searchValue)) {
                        
                       $q->where(function ($q) use ($personal_table, $searchValue) {
                            // Cast columns to TEXT and compare as string to avoid integer overflow
                            $q->whereRaw("CAST({$personal_table}.application_id AS TEXT) = ?", [$searchValue])
                                ->orWhereRaw("CAST({$personal_table}.mobile_no AS TEXT) = ?", [$searchValue]);
                        });
                        // dd($q->tosql());
                    } else {
                        // dd('kii');
                        $q->Where(function ($q) use ($personal_table, $searchValue) {
                            $q->orWhere($personal_table . '.ben_fname', 'ilike', $searchValue . '%');
                        });
                        return $q;
                        // dd($q->tosql());
                    }
                    // dd($q->tosql());
                }, true)
                ->addColumn('name', fn($r) => trim($r->ben_fname ?? ''))
                ->addColumn('father_name', fn($r) => trim($r->father_fname ?? ''))
                ->addColumn('Action', function ($r) use ($edit_button_visible) {
                    $appId = $r->application_id ?? '';
                    if($edit_button_visible==1){
                    $action = '<a href="/lb-entry-draft-edit?application_id=' . $appId . '" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                    $action .= '&nbsp;&nbsp;&nbsp;&nbsp;<button value="' . $appId . '" id="rej_' . $appId . '" class="btn btn-danger btn-sm rej-btn" type="button">Reject</button>';
                    }
                    else{
                       $action=''; 
                    }
                    return $action;
                })
                ->rawColumns(['Action'])
                ->make(true);
        
            }


        // non-ajax: return the view
        $errormsg = Config::get('constants.errormsg');
        return view('LbAppList.appList', [
            'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
            'reject_revert_reason' => $reject_revert_reason,
            'ds_phase_list' => $ds_phase_list,
            'list_type' => $list_type,
            'report_type' => $report_type,
            'report_type' => $report_type,
            'district_code' => $distCode,
            'is_rural_visible' => $is_rural_visible,
            'is_urban' => $is_urban,
            'block_munc_list' => $block_munc_list,
            'block_munc_visible' => $block_munc_visible,
            'block_ulb_code' => $block_ulb_code,
            'gp_ward_list' => $gp_ward_list,
            'block_munc_text' => $block_munc_text,
            'gp_ward_text' => $gp_ward_text,
            'designation_id' => $designation_id,
            
        ]);
    }catch (\Exception $e) {
           // dd($e);
        }
    }

    public function viewimage(Request $request)
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
            if (is_null($request->list_type)) {
                return redirect("/")->with('error', 'Undefine Report');
            }
            $list_type = $request->list_type;
            if (!in_array($list_type, array(1, 2, 3))) {
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

            $ds_phase = $row->ds_phase;
            $url = 'lb-applicant-list/' . $list_type;

            if (empty($row->application_id)) {
                return redirect("/" . $url)->with('error', 'Application Id not valid');
            }

            $cnt = RejectRevertReason::where('id', $reject_cause)->count();
            if ($cnt == 0) {
                return redirect("/" . $url)->with('error', 'Rejection Cause not valid');
            }
            $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
            DB::beginTransaction();
            try {
                $input = [
                    'rejected_cause' => $reject_cause,
                    'action_by' => Auth::user()->id,
                    'action_ip_address' => request()->ip(),
                    'action_type' => class_basename(request()->route()->getAction()['controller'])
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
                $reject_fun_arr = DB::select("select lb_scheme.beneficiary_rejected_partial(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '" . request()->ip() . "', in_action_type => '" . class_basename(request()->route()->getAction()['controller']) . "')");

                $beneficiary_rejected_partial = $reject_fun_arr[0]->beneficiary_rejected_partial;

                if ($is_saved && $beneficiary_rejected_partial == 1) {
                    DB::commit();
                    return redirect("/" . $url)->with('success', 'Application has been successfully rejected')->with('id', $application_id);
                } else {
                    DB::rollback();
                    return redirect("/" . $url)->with('error', $errormsg['roolback']);
                }
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                return redirect("/" . $url)->with('error', $errormsg['roolback']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return redirect("/" . $url)->with('error', $errormsg['roolback']);
        }
    }
    public function applicantreadonlyview(Request $request)
    {
        $district_list =  District::select(
            'id',
            'district_code',
            'district_name',
            'rch_district_code',
            'is_revenue_district',
            'state_code',
            'district_status'
        )->get();
        $user_id = Auth::user()->id;
        //dd($request->toArray());
        $application_id = $request->application_id;
        $ben_id = $request->ben_id;
        $designation_id = Auth::user()->designation_id;
        $scheme_id = $this->scheme_id;
        $row = array();
        $is_active = 0;
        $roleArray = $request->session()->get('role');
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
        if (empty($application_id)) {
            return redirect("/")->with('error', 'Applicant ID Not Pass');
        }
        if (!empty($request->is_draft)) {
            $is_draft = 1;
        } else {
            $is_draft = NULL;
        }
        $getModelFunc = new getModelFunc();
        if ($request->is_reject == 1) {
            // dd('ok');
            $DraftPersonalTable = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 10);
            $DraftPersonalTable->setConnection('pgsql_appread');

            $DraftPersonalTable->setTable('' . $Table);
            $PersonalnData = $DraftPersonalTable->where('application_id', $application_id)->first();
            // dd($PersonalnData);
            if (empty($PersonalnData)) {
                return redirect("/")->with('error', 'Applicant ID Not Valid');
            }
            $reject_row = RejectRevertReason::where('id', $PersonalnData->rejected_cause)->first();
        } else {
            //dd('ok');
            if ($is_draft == 2) {
                $DraftPersonalTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 10);
                $DraftPersonalTable->setConnection('pgsql_appread');

                $DraftPersonalTable->setTable('' . $Table);
                $PersonalnData = $DraftPersonalTable->where('application_id', $application_id);
            } else {
                $DraftPersonalTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1,  $is_draft);
                $DraftPersonalTable->setConnection('pgsql_appread');

                $DraftPersonalTable->setTable('' . $Table);
                $PersonalnData = $DraftPersonalTable->where('application_id', $application_id);
                if ($is_draft == 1) {
                    $PersonalnData = $PersonalnData->where('is_final', TRUE);
                }
                $PersonalnData = $PersonalnData->first();
                if (empty($PersonalnData)) {
                    return redirect("/")->with('error', 'Applicant ID Not Valid1');
                }
            }
        }
        $row = collect([]);
        $row->sws_card_no = $PersonalnData->ss_card_no;
        $row->duare_sarkar_registration_no = $PersonalnData->duare_sarkar_registration_no;
        $row->duare_sarkar_date = $PersonalnData->duare_sarkar_date;
        $row->gender = $PersonalnData->gender;
        $row->application_id = $application_id;
        $row->ben_fname = $PersonalnData->ben_fname;
        $row->ben_mname = $PersonalnData->ben_mname;
        $row->ben_lname = $PersonalnData->ben_lname;
        $row->father_fname = $PersonalnData->father_fname;
        $row->father_mname = $PersonalnData->father_mname;
        $row->father_lname = $PersonalnData->father_lname;
        $row->mother_fname = $PersonalnData->mother_fname;
        $row->mother_mname = $PersonalnData->mother_mname;
        $row->mother_lname = $PersonalnData->mother_lname;
        $row->dob = $PersonalnData->dob;
        if (!empty($PersonalnData->dob)) {
            $row->ben_age = $this->ageCalculate($PersonalnData->dob);
        }
        //$row->ben_age = $PersonalnData->age_ason_01012021;
        $row->caste = $PersonalnData->caste;
        $row->caste_certificate_no = $PersonalnData->caste_certificate_no;
        $row->marital_status = $PersonalnData->marital_status;
        $row->spouse_fname = $PersonalnData->spouse_fname;
        $row->spouse_mname = $PersonalnData->spouse_mname;
        $row->spouse_lname = $PersonalnData->spouse_lname;
        $row->mobile_no = $PersonalnData->mobile_no;
        $row->aadhar_no = $PersonalnData->aadhar_no;
        $row->email = $PersonalnData->email;
        $row->next_level_role_id = $PersonalnData->next_level_role_id;
        $row->comments = $PersonalnData->comments;
        if ($request->is_reject == 1) {
            $row->dist_code = $PersonalnData->dist_code;
            $row->rural_urban_id = $PersonalnData->rural_urban_id;
            $row->police_station = $PersonalnData->police_station;
            $row->block_ulb_code = $PersonalnData->block_ulb_code;
            $row->block_ulb_name = $PersonalnData->block_ulb_name;
            $row->gp_ward_code = $PersonalnData->gp_ward_code;
            $row->gp_ward_name = $PersonalnData->gp_ward_name;
            $row->village_town_city = $PersonalnData->village_town_city;
            $row->house_premise_no = $PersonalnData->house_premise_no;
            $row->post_office = $PersonalnData->post_office;
            $row->pincode = $PersonalnData->pincode;
            $row->residency_period = $PersonalnData->residency_period;
            $row->email = $PersonalnData->email;
            $row->rejected_cause =  $reject_row->reason;
        } else {
            $DraftContactTable = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 3,  $is_draft);
            $DraftContactTable->setConnection('pgsql_appread');

            $DraftContactTable->setTable('' . $Table);
            $contactData = $DraftContactTable->select('dist_code', 'block_ulb_code', 'block_ulb_name', 'gp_ward_code', 'gp_ward_name', 'police_station', 'village_town_city', 'house_premise_no', 'post_office', 'residency_period',  'pincode', 'rural_urban_id')->where('application_id', $application_id)->first();
            $row->dist_code = $contactData->dist_code;
            $row->rural_urban_id = $contactData->rural_urban_id;
            $row->police_station = $contactData->police_station;
            $row->block_ulb_code = $contactData->block_ulb_code;
            $row->block_ulb_name = $contactData->block_ulb_name;
            $row->gp_ward_code = $contactData->gp_ward_code;
            $row->gp_ward_name = $contactData->gp_ward_name;
            $row->village_town_city = $contactData->village_town_city;
            $row->house_premise_no = $contactData->house_premise_no;
            $row->post_office = $contactData->post_office;
            $row->pincode = $contactData->pincode;
            $row->residency_period = $contactData->residency_period;
        }
        if ($request->is_reject == 1) {
            $row->bank_name = $PersonalnData->bank_name;
            $row->branch_name = $PersonalnData->branch_name;
            $row->bank_ifsc = $PersonalnData->bank_ifsc;
            $row->bank_code = $PersonalnData->bank_code;
        } else {
            $DraftBankTable = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 4,  $is_draft);
            $DraftBankTable->setConnection('pgsql_appread');

            $DraftBankTable->setTable('' . $Table);
            $bankData = $DraftBankTable->select('bank_code', 'bank_name', 'branch_name', 'bank_ifsc')->where('application_id', $application_id)->first();
            $row->bank_name = $bankData->bank_name;
            $row->branch_name = $bankData->branch_name;
            $row->bank_ifsc = $bankData->bank_ifsc;
            $row->bank_code = $bankData->bank_code;
        }
        $DraftPfImageTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 5,  $is_draft);
        $DraftPfImageTable->setConnection('pgsql_encread');
        $DraftPfImageTable->setTable('' . $Table);
        $DraftEncloserTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 6,  $is_draft);
        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);
        $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
        $profileImagedata = $DraftPfImageTable->where('image_type', $doc_profile->id)->where('application_id', $application_id)->first();
        $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type');

        $districts = $district_list;
        if ($request->is_reject == 1) {
            $district_row = $district_list->where('district_code', $PersonalnData->dist_code)->first();
            if (trim($PersonalnData->rural_urban_id == 1)) {
                $block_munc_row = Urbanbody::where('urban_body_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->urban_body_name);
                $gp_ward_row = Ward::where('urban_body_ward_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->urban_body_ward_name);
            } else {
                $block_munc_row = Taluka::where('block_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->block_name);
                $gp_ward_row = GP::where('gram_panchyat_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->gram_panchyat_name);
            }
        } else {
            $district_row = $district_list->where('district_code', $contactData->dist_code)->first();
            if (trim($contactData->rural_urban_id == 1)) {
                $block_munc_row = Urbanbody::where('urban_body_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->urban_body_name);
                $gp_ward_row = Ward::where('urban_body_ward_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->urban_body_ward_name);
            } else {
                $block_munc_row = Taluka::where('block_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->block_name);
                $gp_ward_row = GP::where('gram_panchyat_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->gram_panchyat_name);
            }
            if ($PersonalnData->next_level_role_id == -50) {
                if (!empty($PersonalnData->rejected_cause)) {
                    $reject_row = RejectRevertReason::where('id', $PersonalnData->rejected_cause)->first();
                    $row->rejected_cause =  $reject_row->reason;
                } else {
                    $row->rejected_cause =  '';
                }
            }
        }
        $row->dist_name = trim($district_row->district_name);

        $row->block_ulb_name = $block_mun_name;
        $row->gp_ward_name = $gp_ward_name;
        $row->fotter_text = '';
        $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first();

        if (!empty($doc_id_list->doc_list_man))
            $doc_list_man = DocumentType::get()->whereIn("id", json_decode($doc_id_list->doc_list_man));
        else
            $doc_list_man = collect([]);
        if (!empty($doc_id_list->doc_list_opt))
            $doc_list_opt = DocumentType::get()->whereIn("id", json_decode($doc_id_list->doc_list_opt));
        else
            $doc_list_opt = collect([]);


        $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first()->toArray();
        // dd($doc_id_list['doc_list_man']);
        if (isset($doc_id_list['doc_list_man']) && $doc_id_list['doc_list_man'] != 'null') {
            // dd($doc_id_list);
            $doc_list_man = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_man']))->get()->toArray();
        } else
            $doc_list_man = array();
        if (isset($doc_id_list['doc_list_opt']) && $doc_id_list['doc_list_opt'] != 'null') {
            $doc_list_opt = DocumentType::select('id',  'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_opt']))->get()->toArray();
        } else
            $doc_list_opt = array();
        if (count($doc_list_man) > 0 || count($doc_list_opt) > 0) {
            $doc_list = array_merge($doc_list_man, $doc_list_opt);
        } else {
            $doc_list = array();
        }
        $encloser_list = array();
        $i = 0;
        // dd($doc_list);
        if (count($doc_list) > 0) {
            foreach ($doc_list as $doc) {


                if ($doc['is_profile_pic']) {

                    if (!empty($profileImagedata->application_id)) {
                        $encloser_list[$i]['id'] = $doc['id'];
                        $encloser_list[$i]['is_profile_pic'] = $doc['is_profile_pic'];

                        $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                        $encloser_list[$i]['can_download'] = 1;
                        $i++;
                    }
                } else {

                    if (in_array($doc['id'], $encolserdata->toArray())) {
                        $encloser_list[$i]['id'] = $doc['id'];
                        $encloser_list[$i]['is_profile_pic'] = $doc['is_profile_pic'];
                        $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                        $i++;
                    }
                }
            }
        }
        //dd($encloser_list);
        $errormsg = Config::get('constants.errormsg');
        if (!empty($row->dob)) {
            $row->ben_age = $this->ageCalculate($row->dob);
        }
        return view('LokkhiBhandarWCD/pension_view_details_read_only', [
            'designation_id' => $designation_id, 'application_id' => $application_id, 'row' => $row,  'districts' => $districts, 'scheme_id' => $scheme_id, 'doc_list_man' => $doc_list_man, 'doc_list_opt' => $doc_list_opt,
            'encloser_list' => $encloser_list,
            'is_draft' => $is_draft,
            'ben_id' => $ben_id,
            'is_reject' => $request->is_reject,
            'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
        ]);
    }
}