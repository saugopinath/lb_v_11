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
use App\Models\BlkUrbanlEntryMapping;
use App\Models\DsPhase;
use App\Models\BankDetails;
use App\Models\Configduty;
use App\Models\MapLavel;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Traits\TraitCasteCertificateValidate;
use App\Traits\TraitLifeCertificateValidate;
use App\Traits\TraitAadharValidate;
use App\Helpers\DupCheck;
use Session;
class LbEntryController extends Controller
{
    // use SendsPasswordResetEmails;
    use TraitCasteCertificateValidate;
    use TraitLifeCertificateValidate;
    use TraitAadharValidate;
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set('Asia/Kolkata');
        //$phaseArr = DsPhase::where('is_current', TRUE)->first();
        //$mydate = $phaseArr->base_dob;
        $myYear =  date("Y");
        //$mydate =  $myYear.'-'.'01'.'-'.'01';
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


    public function index(Request $request)
    {
        $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
        $designation_id = Auth::user()->designation_id;
        $max_dob = $this->max_dob;
        //dd( $this->min_dob);
        $is_active = 0;
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $sel_district_code = '';
        $sel_block_code = '';
        $sel_rural_urban_id = '';
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $request->session()->put('level', $roleObj['mapping_level']);
                $dist_code = $roleObj['district_code'];
                $request->session()->put('distCode', $roleObj['district_code']);
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                    $request->session()->put('blockCode', $roleObj['urban_body_code']);
                    $urban_code = 1;
                    $sel_rural_urban_id = $urban_code;
                } else {
                    $blockCode = $roleObj['taluka_code'];
                    $request->session()->put('blockCode', $roleObj['taluka_code']);
                    $urban_code = 2;
                    $sel_block_code = $blockCode;
                    $sel_rural_urban_id = $urban_code;
                }
                break;
            }
        }
        //$is_active = 1;
        if ($is_active == 0 || empty($dist_code)) {
            return redirect("/")->with('error', 'User Disabled');
        }
        if ($is_active == 1) {
            $entry_allowed = BlkUrbanlEntryMapping::where('main_entry', true)->where('block_ulb_code',  $blockCode)->count();
            $entry_allowed_normal = BlkUrbanlEntryMapping::where('normal_entry', true)->where('block_ulb_code',  $blockCode)->count();

            if ($entry_allowed == 0 && $entry_allowed_normal==0) {
                return redirect("/")->with('error', 'Entry is disabled');
            }
            $sel_district_code = $dist_code;
            $sel_block_code = '';
            $sel_rural_urban_id = '';
            $district_list = District::select(
                'id',
                'district_code',
                'district_name',
                'rch_district_code',
                'is_revenue_district',
                'state_code',
                'district_status'
            )->get();
            if ($urban_code == 1) {
                $block_ulb_list = UrbanBody::select('urban_body_code as block_ulb_code', 'urban_body_name as block_ulb_name')->where('sub_district_code', $blockCode)->get();
                if (count($block_ulb_list) > 0) {
                    $munc_in = $block_ulb_list->pluck('block_ulb_code');
                    $gp_ward_list = Ward::select('urban_body_ward_code as gp_ward_code', 'urban_body_ward_name as gp_ward_name')->whereIn('urban_body_code', $munc_in)->get();
                }
                $sel_block_code = '';
            } else {
                $sel_block_code = $blockCode;
                $block_ulb_list = Taluka::select('block_code as block_ulb_code', 'block_name as block_ulb_name')->where('district_code', $dist_code)->get();
                $gp_ward_list = GP::select('gram_panchyat_code as gp_ward_code', 'gram_panchyat_name as gp_ward_name')->where('block_code', $blockCode)->get();
            }
            $document_msg = "";
            $doc_profile_image = DocumentType::get()
                ->where("is_profile_pic", true)->first();

            if ($doc_profile_image) {
                $doc_profile_image_id = $doc_profile_image->id;
            }
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
            $errormsg = Config::get('constants.errormsg');
            return view('LbForm/addForm', [
                'max_dob' => $this->max_dob,
                'min_dob' => $this->min_dob,
                'district_list' => $district_list,
                'block_ulb_list' => $block_ulb_list,
                'gp_ward_list' => $gp_ward_list,
                'doc_list_man' => $doc_list_man,
                'doc_list_opt' => $doc_list_opt,
                'profile_img' => $doc_profile_image_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'dob_base_date' => $dob_base_date,
                'add_edit_status' => 1,
                'max_tab_code' => 1,
                'sel_district_code' => $sel_district_code,
                'sel_rural_urban_id' => $sel_rural_urban_id,
                'sel_block_code' => $sel_block_code
            ]);
        }
    }
    public function checkDupaadhaar(Request $request)
    {
        $scheme_id = $this->scheme_id;
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
        $return_status = 0;
        $return_msg = '';
        if ($is_active == 0 || empty($distCode)) {
            $return_status = 0;
            $return_text = 'User Disabled';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
        }
        $aadhar_no = $request->aadhar_no;
        if (empty($aadhar_no)) {
            $return_status = 0;
            $return_text = 'Aadhaar Number is Required';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
        }
        //dd(isset($request->application_id));
        if (isset($request->application_id) && trim($request->application_id)!='') {
            $application_id = $request->application_id;
            if (empty($application_id)) {
                $return_status = 0;
                $return_text = 'Application Id1 Not Found';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            if (!ctype_digit($application_id)) {
                $return_status = 0;
                $return_text = 'Application Id2 Not Found';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            $getModelFunc = new getModelFunc();
            $pension_details_aadhar = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 2, 1);
            $pension_details_aadhar->setTable('' . $Table);
            $cnt = $pension_details_aadhar->where('application_id', '!=', $application_id)->where('aadhar_hash', md5($aadhar_no))->count('application_id');
        } else {
            $getModelFunc = new getModelFunc();
            $pension_details_aadhar = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 2, 1);
            $pension_details_aadhar->setTable('' . $Table);
            $cnt = $pension_details_aadhar->where('aadhar_hash', md5($aadhar_no))->count('application_id');
        }
        if ($cnt > 0) {
            
            Session::put('dup_type', 'aadhar');
            Session::put('dup_type_value', Crypt::encryptString($aadhar_no));
            $return_status = 2;
                return response()->json(['return_status' => $return_status, 'return_msg' => 'Duplicate Aadhaar Number']);  
            } else {
            $return_status = 1;
            return response()->json(['return_status' => $return_status, 'return_msg' => 'Aadhaar Number Available']);
        }
       
    }
    public function personalEntry(Request $request)
    {
        $session_lb_lifecertificate=array();
        $session_lb_aadhaar_no=array();
        $session_lb_castecertificate=array();
        $scheme_id = $this->scheme_id;
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
            $return_status = 0;
            $return_text = 'User Disabled';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
        }
        $max_dob = $this->max_dob;
        $min_dob = $this->min_dob;
        $caste_key =  array_keys(Config::get('constants.caste_lb'));
        $marital_status_key =  array_keys(Config::get('constants.marital_status'));
        $ds_cur_phase = 2;
        $phaseArr = DsPhase::where('is_current', TRUE)->first();
        if (!empty($phaseArr)) {
            $ds_cur_phase = $phaseArr->phase_code;
        }
        $return_status = '';
        $return_msg = '';
        $max_tab_code = 1;
        $application_id = '';
        $today = date("Y-m-d");
        $entry_type_arr = array(1, 2);
        $rules = [
            'application_date' => ($request->status == 1) ? 'required|date|before_or_equal:' . $this->base_dob_chk_date : 'nullable',
            'entry_type' => 'required|in:' . implode(",", $entry_type_arr),
            'duare_sarkar_registration_no' => 'required_if:entry_type,==,2|max:25',
            'duare_sarkar_date' => 'required_if:entry_type,2|nullable|date|before_or_equal:' . $today,
            'first_name' => 'required|string|max:200',
            'gender' => 'required|in:Female',
            'dob' => 'required|date|before_or_equal:' . $max_dob . '|after_or_equal:' . $min_dob,
            // 'dob' => '',
            'txt_age' => 'required|numeric',

            'father_first_name' => 'required|string|max:200',
            'father_middle_name' => 'string|nullable',
            'father_last_name' => 'nullable|string|max:200',
            'mother_first_name' => 'required|string|max:200',
            'mother_middle_name' => 'string|nullable',
            'mother_last_name' => 'nullable|string|max:200',
            'caste_category' => 'required|in:' . implode(",", $caste_key),
            'caste_certificate_no'     => 'required_if:caste_category,SC,ST',
            'aadhar_no' => 'required|numeric|digits:12',
            'mobile_no' => 'required|numeric|digits:10',
            'spouse_first_name' => 'string|nullable',
            'spouse_middle_name' => 'string|nullable',
            'spouse_last_name' => 'string|nullable',
        ];
        $attributes = array();
        $messages = array();
        $attributes['entry_type'] = 'Application Type';
        if ($request->status == 1) {
            $attributes['application_date'] = 'Application Date';
        }
        $attributes['duare_sarkar_registration_no'] = 'Duare Sarkar Registration no.';
        $attributes['duare_sarkar_date'] = 'Duare Sarkar Date';
        $attributes['first_name'] = 'Applicant Name';
        $attributes['middle_name'] = 'Applicant Middle Name';
        $attributes['last_name'] = 'Applicant Last Name';
        $attributes['gender'] = 'Gender';
        $attributes['dob'] = 'Date of Birth';
        $attributes['txt_age'] = 'Age';
        $attributes['father_first_name'] = 'Father First Name';
        $attributes['father_middle_name'] = 'Father Middle Name';
        $attributes['father_last_name'] = 'Father Last Name';
        $attributes['mother_first_name'] = 'Mother First Name';
        $attributes['mother_middle_name'] = 'Mother First Name';
        $attributes['mother_last_name'] = 'Mother First Name';
        $attributes['caste_category'] = 'Caste';
        $attributes['caste_certificate_no'] = 'SC/ST Certificate No.';
        $attributes['marital_status'] = 'Marital Status';
        $attributes['aadhar_no'] = 'Applicant Aadhaar Number';
        $attributes['mobile_no'] = 'Mobile Number';
        $attributes['spouse_first_name'] = 'Spouse First Name';
        $attributes['spouse_middle_name'] = 'Spouse Middle Name';
        $attributes['spouse_last_name'] = 'Spouse Last Name';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $diff = Carbon::parse($request->dob)->diffInYears($this->base_dob_chk_date);
            if ($diff < 25 || $diff >= 60) {
                $return_status = 0;
                $return_text = 'Date of Birth Invalid';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            $post_aadhar_no = $request->aadhar_no;
            if ($this->isAadharValid($post_aadhar_no) == false) {
                $return_status = 0;
                $return_text = 'Aadhaar Number Invalid';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            if($request->type==10){
                if(is_null($request->grievance_id) || $request->grievance_id==''){
                    $return_status = 0;
                    $return_text = 'Grivance ID is required';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);

                }
                if (!ctype_digit($request->grievance_id)) {
                    $return_status = 0;
                    $return_text = 'Grivance ID is Invalid';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                }
               $cnt= DB::table('cmo.cmo_sm_data')
               ->where('grievance_id', $request->grievance_id)
               // ->where('scheme_id', $scheme_id)
               // ->where('grievance_mobile', $grievance_mobile_no)
               ->where('is_redressed', 0) //Temporary Code
               ->where('send_to_op', 1)
               ->where('lgd_dist', $distCode)
               ->count('id');
               if($cnt==0){
                $return_status = 0;
                $return_text = 'Grivance ID is Invalid';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
               }

            }
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $getModelFunc = new getModelFunc();
            $pension_personal_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
            $pension_personal_model->setKeyName('application_id');
            $pension_personal_model->setTable('' . $Table);
            $personalCount = $request->personalCount;
            $pension_details_aadhar = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 2, 1);
            $pension_details_aadhar->setTable('' . $Table);
            $pension_details_aadhar->setKeyName('application_id');
            $SourceChk = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 8);
            $SourceChk->setTable('' . $Table);
            $modelNameAcceptReject = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 9);
            $modelNameAcceptReject->setTable('' . $Table);
            $entry_allowed = BlkUrbanlEntryMapping::where('main_entry', true)->where('block_ulb_code',  $blockCode)->count();
            $entry_allowed_normal = BlkUrbanlEntryMapping::where('normal_entry', true)->where('block_ulb_code',  $blockCode)->count();

            if ($personalCount == 0 && $entry_allowed == 0 && $entry_allowed_normal==0) {
                //dd($entry_allowed);
                $return_status = 0;
                $return_text = 'New Entry Not Allowed';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            $scheme_obj = Scheme::where('id', $scheme_id)->where('is_active', 1)->first();
            if ($request->entry_type == 2) {
                if($scheme_obj->allow_ds_entry==0 && $personalCount == 0){
                    $return_status = 0;
                    $return_text = 'Form through Duare Sarkar camp temporary suspended';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                 }
                    
            }
            if ($request->entry_type == 1) {
                if($scheme_obj->allow_normal_entry==0){
                    $return_status = 0;
                    $return_text = 'Normal Form Entry temporary suspended';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                 }
                    
            }
            DB::beginTransaction();
            $is_saved = 0;
            try {
                $pension_details = array();
                $pension_details['entry_type'] = $request->entry_type;
                if ($personalCount) {

                    $application_id = $request->application_id;
                    if (!empty($application_id)) {
                        $application_row = $pension_personal_model->select('application_id', 'created_by_local_body_code', 'tab_code', 'mobile_no')->where('application_id', $application_id)->first();
                        $application_id = $application_row->application_id;
                        if ($application_row->created_by_local_body_code != $blockCode) {
                            $return_status = 0;
                            $return_text = 'You are not allowded to do so';
                            $return_msg = array("" . $return_text);
                            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                        }
                        $pension_details = $pension_personal_model->where('application_id', $application_row->application_id)->first();
                        $max_tab_code = $pension_details->tab_code;
                        if (!empty(trim($request->mobile_no))) {
                            if ($application_row->mobile_no == trim($request->mobile_no)) {
                                $sp_mobile_new = NULL;
                                $sp_mobile_old = NULL;
                            } else {
                                $sp_mobile_new = trim($request->mobile_no);
                                if (empty(trim($application_row->mobile_no))) {
                                    $sp_mobile_old = NULL;
                                } else {
                                    $sp_mobile_old = $application_row->mobile_no;
                                }
                            }
                        } else {

                            $sp_mobile_new = NULL;
                            $sp_mobile_old = NULL;
                        }
                    } else {
                        $return_status = 0;
                        $return_text = 'Some error.Please try again';
                        $return_msg = array("" . $return_text);
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                } else {
                    $pension_details['scheme_id'] = $this->scheme_id;
                    $pension_details['tab_code'] = 1;
                    $max_tab_code = 1;
                    $sp_mobile_old = NULL;
                    $sp_mobile_new = trim($request->mobile_no);
                }
                if ($request->entry_type == 2) {
                 $pension_details['duare_sarkar_registration_no'] = trim($request->duare_sarkar_registration_no);
                 $pension_details['duare_sarkar_date'] = $request->duare_sarkar_date;
                }
                if ($request->status == 1) {
                    $pension_details['application_date'] = $request->application_date;   
                }
                $pension_details['application_date'] = $request->application_date;
                $pension_details['ben_fname'] = trim($request->first_name);
                $pension_details['gender'] = "Female";
                $pension_details['dob'] = $request->dob;
                $pension_details['age_ason_01012021'] = intval($diff);
                $pension_details['father_fname'] = trim($request->father_first_name);
                $pension_details['father_mname'] = trim($request->father_middle_name);
                $pension_details['father_lname'] = trim($request->father_last_name);
                $pension_details['mother_fname'] = trim($request->mother_first_name);
                $pension_details['mother_mname'] = trim($request->mother_middle_name);
                $pension_details['mother_lname'] = trim($request->mother_last_name);
                $pension_details['caste'] = trim($request->caste_category);
                $pension_details['caste_certificate_no'] = trim($request->caste_certificate_no);
                $pension_details['aadhar_no'] = '********' . substr($post_aadhar_no, -4);
                $pension_details['mobile_no'] = $request->mobile_no;
                $pension_details['email'] = $request->email;


                $pension_details['spouse_fname'] = trim($request->spouse_first_name);
                $pension_details['spouse_mname'] = trim($request->spouse_middle_name);
                $pension_details['spouse_lname'] = trim($request->spouse_last_name);

                $pension_details['created_by_level'] = $mapping_level;
                $pension_details['created_by_dist_code'] = $distCode;
                $pension_details['created_by_local_body_code'] = $blockCode;
                $pension_details['created_by'] = $user_id;
                $pension_details['ip_address'] = $request->ip();

                $pension_details['action_by'] = Auth::user()->id;
                $pension_details['action_ip_address'] = request()->ip();
                $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                if ($personalCount) {
                    $array = json_decode(json_encode($pension_details), true);

                    $is_saved = $pension_personal_model->where('application_id', $application_id)->update($array);
                } else {
                    if($request->type==10){
                        $pension_details['cmo_entry'] = 1;
                        $pension_details['cmo_grievance_id'] = $request->grievance_id;
                    }
                    if ($request->entry_type == 2) {
                      $pension_details['ds_phase'] = $ds_cur_phase;
                      if (!empty($phaseArr)) {
                        if($phaseArr->is_samadhan==TRUE){
                            $pension_details['is_samadhan'] = TRUE;
                        }
                        else{
                            $pension_details['is_samadhan'] = FALSE;
                        }
                      }
                      
                    }
                    $pension_personal_model1 = $pension_personal_model;
                    foreach ($pension_details as $key => $val) {
                        $pension_personal_model1->$key = $val;  //you are adding new element named 'type'
                    }
                    $is_saved = $pension_personal_model1->save();
                }
                if ($personalCount == 0) {
                    $application_row = $pension_personal_model->select('application_id')->where('application_id', $pension_personal_model1->application_id)->first();
                    $pension_details_aadhar->encoded_aadhar = Crypt::encryptString($post_aadhar_no);
                    $pension_details_aadhar->aadhar_hash = md5($post_aadhar_no);
                    $pension_details_aadhar->application_id =   $application_row->application_id;
                    $pension_details_aadhar->created_by_level = $mapping_level;
                    $pension_details_aadhar->created_by = $user_id;
                    $pension_details_aadhar->ip_address = $request->ip();
                    $pension_details_aadhar->created_by_dist_code = $distCode;
                    $pension_details_aadhar->created_by_local_body_code = $blockCode;
                    $pension_details_aadhar->action_by = Auth::user()->id;
                    $pension_details_aadhar->action_ip_address = request()->ip();
                    $pension_details_aadhar->action_type = class_basename(request()->route()->getAction()['controller']);
                    try {
                        $is_saved_aadhar = $pension_details_aadhar->save();
                        if($request->type==10){
                            $op_type='CMOENTRY';
                            $cmo_data = array();
                            $cmo_data['is_processed'] = 1;
                            $cmo_data['is_mark'] = 1;
                            $cmo_data['marked_by'] = $user_id;
                            $cmo_data['marked_date'] = date('Y-m-d H:i:s');
                            $cmo_data['lb_application_id'] = $pension_personal_model1->application_id;
                            $is_cmo_update = DB::table('cmo.cmo_sm_data')
                            ->where('grievance_id', $request->grievance_id)
                            // ->where('scheme_id', $scheme_id)
                            // ->where('pri_cont_no', $grievance_mobile_no)
                            ->where('is_processed', 0) //Temporary Code
                            ->where('send_to_op', 1)
                            ->where('lgd_dist', $distCode)
                            ->update($cmo_data);
                        }
                        else{
                            $op_type='F';
                        }
                    } catch (\Exception $e) {
                        DB::rollback();
                        $return_status = 0;
                        $return_text = 'Duplicate Aadhaar No.';
                       
                        $return_msg = array("" . $return_text);
                        $max_tab_code = 0;
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                    }
                    $application_id = $application_row->application_id;
                    $op_type = $op_type;
                    $modelNameAcceptReject->op_type =  $op_type;
                    $modelNameAcceptReject->application_id = $application_id;
                    $modelNameAcceptReject->designation_id = $designation_id;
                    $modelNameAcceptReject->scheme_id = $scheme_id;
                    $modelNameAcceptReject->mapping_level = $mapping_level;
                    $modelNameAcceptReject->created_by = $user_id;
                    $modelNameAcceptReject->created_by_level = trim($mapping_level);
                    $modelNameAcceptReject->created_by_dist_code = $distCode;
                    $modelNameAcceptReject->created_by_local_body_code = $blockCode;
                    $modelNameAcceptReject->ip_address = request()->ip();
                    $is_accept_reject = $modelNameAcceptReject->save();

                    //$request->session()->put('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id, $application_id);
                    $return_text = 'Personal details has been successfully inserted. Your Application Id:' . $application_row->application_id;
                } else {
                    $update_aadhar_arr = array();
                    $update_aadhar_arr['encoded_aadhar'] = Crypt::encryptString($post_aadhar_no);
                    $update_aadhar_arr['aadhar_hash'] = md5($post_aadhar_no);
                    $update_aadhar_arr['created_by_level'] = $mapping_level;
                    $update_aadhar_arr['created_by'] = $user_id;
                    $update_aadhar_arr['ip_address'] = $request->ip();
                    $update_aadhar_arr['action_by'] = Auth::user()->id;
                    $update_aadhar_arr['action_ip_address'] = request()->ip();
                    $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                    try {
                        $pension_details_aadhar->where('application_id', $application_id)->update($update_aadhar_arr);
                    } catch (\Exception $e) {
                        DB::rollback();
                        $return_status = 0;
                        $return_text = 'Duplicate Aadhaar No.';
                        $return_msg = array("" . $return_text);
                       
                        $max_tab_code = 0;
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                    }
                    $return_text = "Personal details has been successfully updated";
                }
                if (!empty($sp_mobile_old) ||  !empty($sp_mobile_new)) {
                    // dd('123');
                    //dump($sp_mobile_old); dd($sp_mobile_new);
                    $is_inserted_status_arr = DB::select("select lb_scheme.dup_adjustment_insert_update(old_mobile_no => '" . $sp_mobile_old . "',new_mobile_no => '" . $sp_mobile_new . "')");
                    //dd($is_inserted_status_arr);
                    $is_inserted_status = $is_inserted_status_arr[0]->dup_adjustment_insert_update;
                } else {
                    $is_inserted_status = 1;
                }
                if ($is_inserted_status == 6) {
                    DB::rollback();
                    $return_status = 0;
                    $return_text = 'Duplicate Mobile Number.. Please try different.';
                    $return_msg = array("" . $return_text);
                    Session::put('dup_type', 'mobile');
                    Session::put('dup_type_value', Crypt::encryptString($request->mobile_no));
                    $max_tab_code = 0;
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                } else if ($is_inserted_status == 7) {
                    DB::rollback();
                    $return_status = 0;
                    $return_text = 'Mobile Number Modification Faild';
                    $return_msg = array("" . $return_text);
                    $max_tab_code = 0;
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                } else if ($is_inserted_status == 1) {
                    $return_status = 1;
                    $return_msg = array("" . $return_text);
                }
            } catch (\Exception $e) {
                dd($e);
               
                DB::rollback();
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                $max_tab_code = 0;
            }
           

            if ($is_saved) {
                $api_code=1;
                $ben_fullname=trim($request->first_name);
                $return_status = 1;
                DB::commit();
               
                 if (env('APP_ENV') == 'prod'){
                try {
                $session_lb_lifecertificate=$this->bioauthcheckInsert($distCode,$application_id,$ben_fullname,$request->ip(),$post_aadhar_no,$blockCode,$user_id,$api_code);
                }catch (\Exception $e) {
                    // dd($e);
                    $session_lb_lifecertificate=array();
                }
                try {
                $session_lb_aadhaar_no=$this->RationcheckInsert($distCode,$application_id,$ben_fullname,$request->ip(),$post_aadhar_no,$blockCode,$user_id,$request->dob,$api_code);
                }catch (\Exception $e) {
                    $session_lb_aadhaar_no=array();
                }
                try {
                if($request->caste_category=='SC' || $request->caste_category=='ST'){
                $session_lb_castecertificate=$this->casteInfoCheckInsert($distCode,$application_id,$ben_fullname,$request->ip(),trim($request->caste_certificate_no),$blockCode,$user_id,$api_code);
                }
                else{
                    $session_lb_castecertificate=array();
                }
                }catch (\Exception $e) {
                    // dd($e);
                    $session_lb_castecertificate=array();
                }
            }
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        return response()->json(['return_status' => $return_status, 'application_id' => $application_id, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code,'session_lb_lifecertificate' => $session_lb_lifecertificate,'session_lb_castecertificate' => $session_lb_castecertificate,'session_lb_aadhaar_no'=> $session_lb_aadhaar_no]);
    }

    public function contactEntry(Request $request)
    {
        $district_list = District::all();
        $scheme_id = $this->scheme_id;
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
        $return_status = '';
        $return_msg = '';
        $max_tab_code = 2;
        if ($is_active == 0 || empty($distCode)) {
            $return_status = 0;
            $return_text = 'User Disabled';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }

        $rules = [
            'district' => 'required',
            'urban_code' => 'required',
            'police_station' => 'required',
            'block' => 'required',
            'gp_ward' => 'required',
            'village' => 'required|string|max:300',
            'house_premise_no' => 'string|nullable',
            'post_office' => 'required|string',
            'pin_code' => 'required|numeric|digits:6',
        ];
        $attributes = array();
        $messages = array();
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['police_station'] = 'Police Station';
        $attributes['block'] = 'Block/Municipality/Corp';
        $attributes['gp_ward'] = 'GP/Ward No.';
        $attributes['village'] = 'Village/Town/City';
        $attributes['house_premise_no'] = 'House / Premise No.';
        $attributes['post_office'] = 'Post Office';
        $attributes['pin_code'] = 'Pin Code';
        $attributes['residency_period'] = 'Number of years Dwelling in WB';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $sel_district = $request->district;
            $cnt = $district_list->where('district_code', $sel_district)->count();
            if ($cnt == 0) {
                $return_status = 0;
                $return_text = 'District Invalid';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            $sel_urban_code = $request->urban_code;
            $sel_block = $request->block;
            $sel_gp_ward = $request->gp_ward;
            if ($sel_urban_code == 1) {
                $cnt1 = UrbanBody::where('district_code', $sel_district)->where('urban_body_code', $sel_block)->count();
                if ($cnt1 == 0) {
                    $return_status = 0;
                    $return_text = 'Block/Municipality/Corp Invalid';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                }
                $cnt2 = Ward::where('urban_body_code', $sel_block)->where('urban_body_ward_code', $sel_gp_ward)->count();
                if ($cnt2 == 0) {
                    $return_status = 0;
                    $return_text = 'GP/Ward Not Invalid';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                }
            } else if ($sel_urban_code == 2) {
                $cnt1 = Taluka::where('district_code', $sel_district)->where('block_code', $sel_block)->count();
                if ($cnt1 == 0) {
                    $return_status = 0;
                    $return_text = 'Block/Municipality/Corp Invalid';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                }
                $cnt2 = GP::where('block_code', $sel_block)->where('gram_panchyat_code', $sel_gp_ward)->count();
                if ($cnt2 == 0) {
                    $return_status = 0;
                    $return_text = 'GP/Ward Not Invalid';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                }
            }
            $getModelFunc = new getModelFunc();
            $pension_details_contact = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 3, 1);
            $pension_details_contact->setTable('' . $Table);
            $pension_details = array();
            $contactCount = $request->contactCount;
            // dd($contactCount);
            // $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
            $application_id = $request->application_id;
            // dd($application_id);
            if (!empty($application_id)) {
                $personal_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
                $personal_model->setTable('' . $Table);
                $personal_details_arr = $personal_model->select('application_id', 'created_by_local_body_code', 'tab_code','ds_phase')->where('application_id', $application_id)->first();
                if ($personal_details_arr->created_by_local_body_code != $blockCode) {
                    $return_status = 0;
                    $return_text = 'You are not allowded to do so';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                }
                $max_tab_code = $personal_details_arr->tab_code;
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            DB::beginTransaction();
            $is_saved = 0;
            try {

                if (!empty($personal_details_arr) && intval($personal_details_arr->tab_code) < 2) {
                    $input = [
                        'tab_code' =>  2,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])

                    ];
                    $tab_max_code_saved = $personal_model->where('application_id', $application_id)->update($input);
                    $max_tab_code = $max_tab_code + 1;
                } else {
                    $tab_max_code_saved = 2;
                }
                if ($request->urban_code == 1) {
                    $block_ulb = UrbanBody::where('urban_body_code', $request->block)->first();
                    $gp_ward = Ward::where('urban_body_ward_code', $request->gp_ward)->first();
                    $pension_details['block_ulb_name'] = trim($block_ulb->urban_body_name);
                    $pension_details['gp_ward_name']   = trim($gp_ward->urban_body_ward_name);
                } else {
                    $block_ulb =  Taluka::where('block_code', $request->block)->first();
                    $gp_ward =  GP::where('gram_panchyat_code', $request->gp_ward)->first();
                    $pension_details['block_ulb_name'] = trim($block_ulb->block_name);
                    $pension_details['gp_ward_name']   = trim($gp_ward->gram_panchyat_name);
                }
                $pension_details['ds_phase']       =      $personal_details_arr->ds_phase;
                $pension_details['dist_code']       =      $request->district;
                $pension_details['rural_urban_id']     =      $request->urban_code;
                $pension_details['police_station']  = trim($request->police_station);
                $pension_details['block_ulb_code']  = $request->block;
                $pension_details['gp_ward_code'] = $request->gp_ward;
                $pension_details['village_town_city']  = trim($request->village);
                $pension_details['house_premise_no']  = trim($request->house_premise_no);
                $pension_details['post_office']   = trim($request->post_office);
                $pension_details['pincode']  = trim($request->pin_code);
                $pension_details['created_by']  = Auth::user()->id;
                $pension_details['created_by_level']  = $mapping_level;
                $pension_details['created_by_dist_code'] = $distCode;
                $pension_details['created_by_local_body_code'] = $blockCode;
                $pension_details['ip_address']  = $request->ip();
                $pension_details['action_by'] = Auth::user()->id;
                $pension_details['action_ip_address'] = request()->ip();
                $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                if ($contactCount) {
                    $is_saved = $pension_details_contact->where('application_id', $application_id)->update($pension_details);
                } else {

                    $pension_details['application_id']      =     $application_id;
                    $is_saved = $pension_details_contact->insert($pension_details);
                }
                $return_status = 1;
                $return_text = "Contact details has been successfully updated";
                $return_msg = array("" . $return_text);
            } catch (\Exception $e) {

                DB::rollback();
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
            }
            //dd($is_saved);
            DB::commit();

            if ($is_saved) {
                $return_status = 1;
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
    }
    public function bankEntry(Request $request)
    {
        $scheme_id = $this->scheme_id;
        $return_status = '';
        $return_msg = '';
        $max_tab_code = 3;
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
            $return_status = 0;
            $return_text = 'User Disabled';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }

        $rules = [
            'bank_ifsc_code' => 'required',
            'name_of_bank' => 'required|string|max:200',
            'bank_branch' => 'required|string|max:200',
            'bank_account_number' => 'required|numeric|required_with:confirm_bank_account_number|same:confirm_bank_account_number',
            'confirm_bank_account_number' => 'required|numeric',

        ];
        $attributes = array();
        $messages = array();
        $attributes['bank_ifsc_code'] = 'IFS Code';
        $attributes['name_of_bank'] = 'Bank Name';
        $attributes['bank_branch'] = 'Bank Branch Name';
        $attributes['bank_account_number'] = 'Bank Account Number';

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {

            $getModelFunc = new getModelFunc();
            $pension_details_bank = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 4, 1);
            $pension_details_bank->setTable('' . $Table);
            $pension_details = array();
            // $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
            $application_id = $request->application_id;
            if (!empty($application_id)) {

                $personal_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
                $personal_model->setTable('' . $Table);
                $personal_details_arr = $personal_model->select('application_id', 'created_by_local_body_code', 'tab_code')->where('application_id', $application_id)->first();
                $bank_model_pre = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 4, 1);
                $bank_model_pre->setTable('' . $Table);
                $bank_model_pre_arr = $bank_model_pre->select('application_id', 'bank_ifsc', 'bank_code')->where('application_id', $application_id)->first();
                if ($personal_details_arr->created_by_local_body_code != $blockCode) {
                    $return_status = 0;
                    $return_text = 'You are not allowded to do so';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                }
                $max_tab_code = $personal_details_arr->tab_code;
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            $bank_ifsc = trim($request->bank_ifsc_code);
            $bank_code = trim($request->bank_account_number);
            $bankCount = $request->bankCount;

            $ifsc = trim($request->bank_ifsc_code);
            $bank_branch = trim($request->bank_branch);
            $name_of_bank = trim($request->name_of_bank);
            $row_count1 = BankDetails::where('is_active', 1)->whereraw("trim(ifsc)='$ifsc'")->whereraw("trim(branch)='$bank_branch'")->whereraw("trim(bank)='$name_of_bank'")->count();
            if ($row_count1 == 0) {
                $return_status = 0;
                $return_text = 'Bank IFSC and Bank Name Not Match';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            $DupCheckBankOap = DupCheck::getDupCheckBank(10,$bank_code);
            $DupCheckBankOap=0;
            if(!empty($DupCheckBankOap)){
                $return_status = 0;
                $return_text = 'Duplicate Bank Account Number present in Old Age Pension Scheme with Beneficiary ID- '.$DupCheckBankOap.'';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            $DupCheckBankJohar = DupCheck::getDupCheckBank(1,$bank_code);
            $DupCheckBankJohar=0;
            if(!empty($DupCheckBankJohar)){
                $return_status = 0;
                $return_text = 'Duplicate Bank Account Number present Jai Johar Pension Scheme with Beneficiary ID- '.$DupCheckBankJohar.'';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            $DupCheckBankBandhu = DupCheck::getDupCheckBank(3,$bank_code);
            $DupCheckBankBandhu=0;
            if(!empty($DupCheckBankBandhu)){
                $return_status = 0;
                $return_text = 'Duplicate Bank Account Number present Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- '.$DupCheckBankBandhu.'';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            DB::beginTransaction();
            if (!empty($personal_details_arr) && intval($personal_details_arr->tab_code) < 3) {
                $input = [
                    'tab_code' =>  3,
                    'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])

                ];
                $tab_max_code_saved = $personal_model->where('application_id', $application_id)->update($input);
                $max_tab_code = $max_tab_code + 1;
            } else {
                $tab_max_code_saved = 1;
            }
            $pension_details['bank_name']  = trim($request->name_of_bank);
            $pension_details['branch_name']    = trim($request->bank_branch);
            $pension_details['bank_code']    = trim($request->bank_account_number);
            $pension_details['bank_ifsc']   = trim($request->bank_ifsc_code);
            $pension_details['created_by_level'] = $mapping_level;
            $pension_details['created_by'] = Auth::user()->id;
            $pension_details['ip_address'] = $request->ip();
            $pension_details['created_by_dist_code'] = $distCode;
            $pension_details['created_by_local_body_code'] = $blockCode;
            $pension_details['action_by'] = Auth::user()->id;
            $pension_details['action_ip_address'] = request()->ip();
            $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
            $is_saved = 0;
            try {
                if ($bankCount) {
                    if (!empty(trim($request->bank_ifsc_code)) || !empty(trim($request->bank_account_number))) {
                        //dump($request->bank_ifsc_code);dd($request->bank_account_number);
                        if (trim($bank_model_pre_arr->bank_code) == trim($request->bank_account_number) && trim($bank_model_pre_arr->bank_ifsc) == trim($request->bank_ifsc_code)) {
                            $sp_bank_ifsc_new = NULL;
                            $sp_bank_ifsc_old = NULL;
                            $sp_bank_code_new = NULL;
                            $sp_bank_code_old = NULL;
                        } else {
                            $sp_bank_ifsc_new = trim($request->bank_ifsc_code);
                            $sp_bank_code_new = trim($request->bank_account_number);
                            if (!empty(trim($bank_model_pre_arr->bank_code) && !empty(trim($bank_model_pre_arr->bank_ifsc)))) {
                                $sp_bank_ifsc_old = trim($bank_model_pre_arr->bank_ifsc);
                                $sp_bank_code_old = trim($bank_model_pre_arr->bank_code);
                                // dd($row_data->bank_ifsc);
                            } else {
                                $sp_bank_ifsc_old = NULL;
                                $sp_bank_code_old = NULL;
                            }
                        }
                    } else {
                        $sp_bank_ifsc_new = NULL;
                        $sp_bank_ifsc_old = NULL;
                        $sp_bank_code_new = NULL;
                        $sp_bank_code_old = NULL;
                    }
                    //dump($sp_bank_ifsc_new); dump($sp_bank_ifsc_old); dump($sp_bank_code_new); dd($sp_bank_code_old);
                    $is_saved = $pension_details_bank->where('application_id', $application_id)->update($pension_details);
                } else {
                    $sp_bank_ifsc_new = trim($request->bank_ifsc_code);
                    $sp_bank_ifsc_old = NULL;
                    $sp_bank_code_new = trim($request->bank_account_number);
                    $sp_bank_code_old = NULL;
                    $pension_details['application_id'] = $application_id;
                    $is_saved = $pension_details_bank->insert($pension_details);
                }
                //$id = $pension_details->id;
                $return_status = 1;
                $return_text = "Bank details has been successfully updated";
                $return_msg = array("" . $return_text);
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
            }
            if (!empty($sp_bank_ifsc_new) || !empty($sp_bank_ifsc_old)) {
                //dd('123');
                $is_inserted_status_arr = DB::select("select lb_scheme.dup_adjustment_insert_update(new_bank_ifsc => '" . $sp_bank_ifsc_new . "',new_bank_code => '" . $sp_bank_code_new . "',old_bank_ifsc => '" . $sp_bank_ifsc_old . "',old_bank_code => '" . $sp_bank_code_old . "')");
                //dd($is_inserted_status_arr);
                $is_inserted_status = $is_inserted_status_arr[0]->dup_adjustment_insert_update;
            } else {
                $is_inserted_status = 1;
            }
            if ($is_inserted_status == 2) {
                //dd('ok3');
                DB::rollback();
                $return_status = 0;
                $return_text = 'Duplicate Bank Account Number.. Please try different.';
                $return_msg = array("" . $return_text);
                Session::put('dup_type', 'bank');
                Session::put('dup_type_value', Crypt::encryptString($request->bank_account_number));
                
                $max_tab_code = 0;
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            } else if ($is_inserted_status == 3) {
                //dd('ok3');
                DB::rollback();
                $return_status = 0;
                $return_text = 'Bank Account Number Modification Faild';
                $return_msg = array("" . $return_text);
                $max_tab_code = 0;
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            } else if ($is_inserted_status == 1) {
                DB::commit();
            }

            if ($is_saved) {
                $return_status = 1;
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
    }
    public function encloserEntry(Request $request)
    {
        $scheme_id = $this->scheme_id;
        $return_status = '';
        $return_msg = '';
        $max_tab_code = 3;
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
            $return_status = 0;
            $return_text = 'User Disabled';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        
        $user_id = Auth::user()->id;

        $document_type = $request->document_type;
        // dd( $document_type);
        if (empty($document_type) || !is_int($scheme_id)) {
            $return_status = 0;
            $return_text = 'Parameter Not Valid1';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        $is_profile = $request->is_profile;
        if (!in_array($is_profile, array(0, 1))) {
            $return_status = 0;
            $return_text = 'Parameter Not Valid2';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        $session_application_id = $request->session()->get('lb_faulty_draft_app_' . $user_id);
        $application_id = $request->application_id;
        //dump($application_id);
        $getModelFunc = new getModelFunc();

        if (!empty($application_id)) {
            $personal_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
            $personal_model->setTable('' . $Table);
            $personal_details_arr = $personal_model->select('application_id', 'created_by_local_body_code', 'tab_code', 'caste')->where('application_id', $application_id)->first();
            if ($personal_details_arr->created_by_local_body_code != $blockCode) {
                $return_status = 0;
                $return_text = 'You are not allowded to do so';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            $max_tab_code = $personal_details_arr->tab_code;
        } else {
            $return_status = 0;
            $return_text = 'Some error.Please try again';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        $query = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', $document_type);
        if ($is_profile) {
            $query = $query->where('is_profile_pic', TRUE);
        }
        $doc_arr = $query->first();
        if (empty($doc_arr->doc_name)) {
            $return_status = 0;
            $return_text = 'Parameter Not Valid';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        $attributes = array();
        $messages = array();
        $valid = 0;
        // dump($request->add_edit_status);


        $required = 'required';
        $rules['file'] = $required . '|mimes:' . $doc_arr->doc_type . '|max:' . $doc_arr->doc_size_kb . ',';
        $messages['file.max'] = "The file uploaded for " . $doc_arr->doc_name . " size must be less than :max KB";
        $messages['file.mimes'] = "The file uploaded for " . $doc_arr->doc_name . " must be of type " . $doc_arr->doc_type;
        $messages['file.required'] = "Document for " . $doc_arr->doc_name . " must be uploaded";
        //dd($rules);
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $valid = 1;
        } else {
            $valid = 0;
            $return_msg = $validator->errors()->all();
            $return_status = 0;
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }


        if ($valid == 1) {
            $pension_details_encloser1 = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 5, 1);
            $pension_details_encloser1->setConnection('pgsql_encwrite');
            $pension_details_encloser1->setTable('' . $Table);
            $pension_details_encloser2 = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 6, 1);
            $pension_details_encloser2->setConnection('pgsql_encwrite');
            $pension_details_encloser2->setTable('' . $Table);

            if ($is_profile) {
                $encolserCount = $pension_details_encloser1->where('image_type', $doc_arr->id)->where('application_id', $application_id)->count();
            } else {
                $encolserCount = $pension_details_encloser2->where('document_type', $doc_arr->id)->where('application_id', $application_id)->count();
            }
            $image_file = $request->file('file');
            $img_data = file_get_contents($image_file);
            $extension = $image_file->getClientOriginalExtension();
            $mime_type = $image_file->getMimeType();
            //$type = pathinfo($image_file, PATHINFO_EXTENSION);
            $base64 = base64_encode($img_data);


            $doc_id_list = SchemeDocMap::select('doc_list_man')->where('scheme_code', $scheme_id)->first();
            if (isset($doc_id_list['doc_list_man']) && $doc_id_list['doc_list_man'] != 'null') {
                $man_doc_array = json_decode($doc_id_list->doc_list_man);
                $in_array = array();
                foreach ($man_doc_array as $arr) {
                    array_push($in_array, (int) $arr);
                }
                if (trim($personal_details_arr->caste) == 'SC' || trim($personal_details_arr->caste) == 'ST') {
                    array_push($in_array, 3);
                }
            } else
                $in_array = array();
            if (count($in_array) > 0) {
                $encolserdata1 = $pension_details_encloser1->select('image_type')->where('application_id', $application_id)->get()->pluck('image_type')->toArray();
                $encolserdata2 = $pension_details_encloser2->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type')->toArray();
                $already_uploaded = array_merge($encolserdata1, $encolserdata2);

                if (!in_array($document_type, $already_uploaded))
                    array_push($already_uploaded, (int) $document_type);


                if (count($already_uploaded) == count($in_array)  && array_diff($already_uploaded, $in_array) === array_diff($in_array, $already_uploaded)) {
                    $max_insert_tab = 4;
                } else {
                    $max_insert_tab = $max_tab_code;
                }
                //dump($already_uploaded);
                // dump($already_uploaded);
                //dump($in_array);
                //dump($max_insert_tab);
            } else {
                $max_insert_tab = $max_tab_code;
            }

            $pension_details = array();
            $pension_details['created_at'] = date('Y-m-d H:i:s');
            if ($is_profile) {
                $pension_details['image_type'] = $doc_arr->id;
                $pension_details['profile_image'] = $base64;
                $pension_details['image_extension'] = $extension;
                $pension_details['image_mime_type'] = $mime_type;
            } else {
                $pension_details['document_type'] = $doc_arr->id;
                $pension_details['attched_document'] = $base64;
                $pension_details['document_extension'] = $extension;
                $pension_details['document_mime_type'] = $mime_type;
            }
            $pension_details['created_by_level'] = $mapping_level;
            $pension_details['created_by'] = Auth::user()->id;
            $pension_details['ip_address'] = $request->ip();
            $pension_details['created_by_dist_code'] = $distCode;
            $pension_details['created_by_local_body_code'] = $blockCode;
            $pension_details['action_by'] = Auth::user()->id;
            $pension_details['action_ip_address'] = request()->ip();
            $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
            DB::beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();
            try {
                $crd_status_1 = 0;
                $crd_status_2 = 0;

                $crd_status_1 = 1;
                if ($crd_status_1 == 1) {
                    if ($encolserCount) {
                        if ($is_profile) {
                            $crd_status_2 = $pension_details_encloser1->where('image_type', $doc_arr->id)->where('application_id', $application_id)->update($pension_details);
                        } else
                            $crd_status_2 = $pension_details_encloser2->where('document_type', $doc_arr->id)->where('application_id', $application_id)->update($pension_details);
                    } else {
                        $pension_details['application_id'] = $application_id;
                        if ($is_profile) {
                            $crd_status_2 = $pension_details_encloser1->insert($pension_details);
                        } else {
                            $crd_status_2 = $pension_details_encloser2->insert($pension_details);
                        }
                    }
                    if ($crd_status_2 == 1) {
                        DB::commit();
                        DB::connection('pgsql_encwrite')->commit();
                        $return_status = 1;
                    } else {
                        DB::rollback();
                        DB::connection('pgsql_encwrite')->rollBack();
                        $return_status = 0;
                        $return_text = 'Error. Please try again.';
                        $return_msg = array("" . $return_text);
                    }
                }
            } catch (\Exception $e) {
                DB::rollback();
                DB::connection('pgsql_encwrite')->rollBack();
                $return_text = 'Error. Please try again.';
                $return_msg = array("" . $return_text);
                $return_status = 0;
            }
        }
        //dd($max_tab_code);
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
    }
    public function checkEncolser(Request $request)
    {
        $scheme_id = $this->scheme_id;
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
        $return_status = 0;
        $return_msg = '';
        $max_tab_code = 3;
        if ($is_active == 0 || empty($distCode)) {
            $return_status = 0;
            $return_text = 'User Disabled';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }

        $application_id = $request->application_id;
        if (empty($application_id)) {
            $return_status = 0;
            $return_text = 'Application Id Not Found';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        if (!ctype_digit($application_id)) {
            $return_status = 0;
            $return_text = 'Application Id Not Found';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
        $personal_model->setTable('' . $Table);
        $condition = array();
        $condition['application_id'] = $application_id;
        $condition['created_by_dist_code'] = $distCode;
        $condition['created_by_local_body_code'] = $blockCode;
        $personal_details_arr = $personal_model->where($condition)->first();
        if (empty($personal_details_arr)) {
            $return_status = 0;
            $return_text = 'Application Id Not Found';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }

        $man_doc_list = SchemeDocMap::select('doc_list_man')->where('scheme_code', $scheme_id)->first();
        $doc_list = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb', 'is_profile_pic')->get();
        $profile_image_arr = $doc_list->where('is_profile_pic', TRUE)->first();
        $pension_details_encloser1 = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 5, 1);
        $pension_details_encloser1->setConnection('pgsql_encwrite');
        $pension_details_encloser1->setTable('' . $Table);
        $pension_details_encloser2 = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 6, 1);
        $pension_details_encloser2->setConnection('pgsql_encwrite');
        $pension_details_encloser2->setTable('' . $Table);
        $profileCount = $pension_details_encloser1->where('image_type', $profile_image_arr->id)->where('application_id', $application_id)->count('image_type');
        $encolserdata = $pension_details_encloser2->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type')->toArray();
        $man_doc_array = json_decode($man_doc_list->doc_list_man);
        $in_array = array();
        foreach ($man_doc_array as $arr) {
            array_push($in_array, (int) $arr);
        }
        if (trim($personal_details_arr->caste) == 'SC' || trim($personal_details_arr->caste) == 'ST') {
            array_push($in_array, 3);
        }
        if ($profileCount) {
            array_push($encolserdata, $profile_image_arr->id);
        }
        array_push($in_array, $profile_image_arr->id);
        // dump($in_array);
        //dd($encolserdata);

        if (empty(array_diff($in_array, $encolserdata))) {
            $return_status = 1;
            if (!empty($personal_details_arr) && intval($personal_details_arr->tab_code) < 5) {
                $input = [
                    'tab_code' =>  4,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])

                ];
                $tab_max_code_saved = $personal_model->where('application_id', $application_id)->update($input);
                if ($tab_max_code_saved) {
                    $max_tab_code = 4;
                } else {
                    $max_tab_code = 3;
                }
            } else {
                $max_tab_code = 4;
            }
        } else {
            $return_msg = "Please Upload Mandatory Documents";
            $max_tab_code = $max_tab_code;
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
    }
    public function declarationEntry(Request $request)
    {
        $scheme_id = $this->scheme_id;
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

        $return_status = '';
        $return_msg = '';
        $max_tab_code = 1;
        $rules = [
            'is_resident' => 'required|in:1',
            'earn_monthly_remuneration' => 'required|in:1',
            'info_genuine_decl' => 'required|in:1'
        ];
        $attributes = array();
        $messages = array();
        $attributes['doc_is_resident.required'] = "Please check the checkbox That I am a resident of West Bengal";
        $attributes['doc_is_resident.in'] = "Please check the checkbox That I am a resident of West Bengal";
        $attributes['earn_monthly_remuneration.required'] = "Please check the checkbox That I do not earn any monthly remuneration from any regular Government job";
        $attributes['earn_monthly_remuneration.in'] = "Please check the checkbox That I do not earn any monthly remuneration from any regular Government job";
        $attributes['info_genuine_decl.required'] = "Please check the checkbox That all the information and documents submitted by me are correct/ genuine. In case any of the information/ document is found to be false, penal action shall be taken against me and the benefit will be terminated. ";
        $attributes['info_genuine_decl.in'] = "Please check the checkbox That all the information and documents submitted by me are correct/ genuine. In case any of the information/ document is found to be false, penal action shall be taken against me and the benefit will be terminated. ";
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            //$session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
            $application_id = $request->application_id;
            $getModelFunc = new getModelFunc();
            if (!empty($application_id)) {
                $personal_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
                $personal_model->setTable('' . $Table);
                $personal_details_arr = $personal_model->select('application_id', 'created_by_local_body_code', 'tab_code', 'mobile_no')->where('application_id', $application_id)->first();
                if ($personal_details_arr->created_by_local_body_code != $blockCode) {
                    $return_status = 0;
                    $return_text = 'You are not allowded to do so';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                }
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            $pension_details_other = new DataSourceCommon;
            $getModelFunc = new getModelFunc();
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 7, 1);
            $pension_details_other->setTable('' . $Table);
            $pension_details = array();
            $SourceChk = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 8);
            $SourceChk->setTable('' . $Table);
            $otherCount = $request->otherCount;
            $DraftPersonalTable = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
            $DraftPersonalTable->setTable('' . $Table);

            $modelNameAcceptReject = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 9);
            $modelNameAcceptReject->setTable('' . $Table);

            $user_id = Auth::user()->id;

            DB::beginTransaction();
            $is_saved = 0;
            try {

                if (!empty($personal_details_arr) && intval($personal_details_arr->tab_code) < 5) {
                    $input = [
                        'tab_code' =>  5,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])

                    ];
                    $tab_max_code_saved = $personal_model->where('application_id', $application_id)->update($input);
                } else {
                    $tab_max_code_saved = 1;
                }

                $pension_details['is_resident'] = trim($request->is_resident);
                $pension_details['earn_monthly_remuneration'] = trim($request->earn_monthly_remuneration);
                $pension_details['info_genuine_decl'] = trim($request->info_genuine_decl);

                $pension_details['created_by'] = Auth::user()->id;
                $pension_details['created_by_level'] = $mapping_level;
                $pension_details['ip_address'] = $request->ip();
                $pension_details['created_by_dist_code'] = $distCode;
                $pension_details['created_by_local_body_code'] = $blockCode;
                $pension_details['action_by'] = Auth::user()->id;
                $pension_details['action_ip_address'] = request()->ip();
                $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                if (isset($request->av_status)) {
                    $pension_details['av_status'] = TRUE;
                } else {
                    $pension_details['av_status'] = FALSE;
                }
                if ($otherCount) {
                    $pension_details_other->where('application_id', $application_id)->update($pension_details);
                } else {
                    $pension_details['application_id'] = $application_id;

                    $is_saved = $pension_details_other->insert($pension_details);
                }



                $updated_source_lb_id = 1;
                $update_dr_arr = array();
                $update_dr_arr['is_final'] = TRUE;
                $update_dr_arr['action_by'] = Auth::user()->id;
                $update_dr_arr['action_ip_address'] = request()->ip();
                $update_dr_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                if ($request->status == 1) {
                    $op_type = 'E';
                    $url = 'lb-applicant-list/1';
                } else if ($request->status == 2) {
                    $op_type = 'U';
                    $url = 'lb-applicant-list/2';
                } else if ($request->status == 3) {
                    $op_type = 'Q';
                    $url = 'lb-applicant-list/3';
                    $update_dr_arr['next_level_role_id'] = null;
                }
                $updated_source_lb_id = $DraftPersonalTable->where('application_id', $application_id)->update($update_dr_arr);

                $modelNameAcceptReject->op_type =  $op_type;
                $modelNameAcceptReject->application_id = $application_id;
                $modelNameAcceptReject->designation_id = Auth::user()->designation_id;
                $modelNameAcceptReject->scheme_id = $scheme_id;
                $modelNameAcceptReject->mapping_level = $mapping_level;
                $modelNameAcceptReject->created_by = Auth::user()->id;
                $modelNameAcceptReject->created_by_level = trim($mapping_level);
                $modelNameAcceptReject->created_by_dist_code = $distCode;
                $modelNameAcceptReject->created_by_local_body_code = $blockCode;
                $modelNameAcceptReject->ip_address = request()->ip();

                $is_accept_reject = $modelNameAcceptReject->save();
            } catch (\Exception $e) {
                DB::rollback();
                return redirect("/lb-entry-draft-edit?status=" . $request->status . "&application_id=" . $application_id . "&tab_code=encloser")->with('error', 'Some error.Please try again ....');
            }
            DB::commit();
            $sms_send = 1;
            if ($sms_send == 1) {
                $mobileNo = $personal_details_arr->mobile_no;
                $message = 'Your Lakshmir Bhandar application is received with application ID ' . $application_id . ' . Lakshmir Bhandar, Govt of WB';
                $url_base = url('/');
                // dd($url_base);
                
                //$this->initiateSmsActivation($mobileNo, $message);
                
            }
            //dd($url);
            return redirect('/' . $url)->with('success', 'Application Submitted Successfully')
                ->with('id',  $application_id);
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
            // use request value directly to avoid undefined variable when validation fails
            return redirect("/lb-entry-draft-edit?status=" . $request->status . "&application_id=" . $request->application_id . "&tab_code=encloser")->with('errors', $return_msg);
        }
    }
   
    public function draftedit(Request $request)
    {
       // dd('ok');
        $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
        $designation_id = Auth::user()->designation_id;
        $max_dob = $this->max_dob;
        $scheme_id = $this->scheme_id;
        //dd($sws_no);
        if ($designation_id != 'Operator') {
            return redirect("/")->with('error', 'Not Allowed');
        }
        if (isset($request->application_id)) {
            $application_id = trim($request->application_id);
            //dd($add_edit_status);
            if (empty($application_id)) {
                return redirect("/")->with('error', 'Paramenter Application id not Passed');
            }
            if (!ctype_digit($application_id)) {
                return redirect("/")->with('error', 'Paramenter Application id  not Valid');
            }
        } else {
            $application_id = NULL;
        }
        if (isset($request->type)) {
            $type = $request->type;
        } else {
            $type = 0;
        }
        if (isset($request->grievance_id)) {
            $grievance_id = $request->grievance_id;
        } else {
            $grievance_id = 0;
        }
        if (isset($request->status)) {
            $status = $request->status;
        } else {
            $status = 1;
        }
        $is_active = 0;
        // $roleArray = $request->session()->get('role');
        $roleArray=Configduty::where('user_id',Auth::user()->id)->get()->toArray();

        $rowArr = [];
        $block_ulb_list = collect([]);
        $gp_ward_list = collect([]);
        $row = collect([]);
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $dist_code = $roleObj['district_code'];
                $rowArr['dist_code'] = $dist_code;
                if ($roleObj['is_urban'] == 1) {
                    $rowArr['rural_urban_id'] = 1;
                    $blockCode = $roleObj['urban_body_code'];
                    $block_ulb_list = UrbanBody::select('urban_body_code as block_ulb_code', 'urban_body_name as block_ulb_name')->where('sub_district_code', $blockCode)->get();
                    if (count($block_ulb_list) > 0) {
                        $munc_in = $block_ulb_list->pluck('block_ulb_code');
                        $gp_ward_list = Ward::select('urban_body_ward_code as gp_ward_code', 'urban_body_ward_name as gp_ward_name')->whereIn('urban_body_code', $munc_in)->get();
                    }
                    $rowArr['block_ulb_code'] = '';
                } else {
                    $blockCode = $roleObj['taluka_code'];
                    $rowArr['block_ulb_code'] = $blockCode;
                    $rowArr['rural_urban_id'] = 2;
                    $block_ulb_list = Taluka::select('block_code as block_ulb_code', 'block_name as block_ulb_name')->where('district_code', $dist_code)->get();
                    $gp_ward_list = GP::select('gram_panchyat_code as gp_ward_code', 'gram_panchyat_name as gp_ward_name')->where('block_code', $blockCode)->get();
                }
                break;
            }
        }
        //dd( $block_ulb_list->toArray());
        //$is_active = 1;
        if ($is_active == 0 || empty($dist_code)) {
            return redirect("/")->with('error', 'User Disabled');
        }
        if ($is_active == 1) {
            $scheme_details = Scheme::where('id', $scheme_id)->first();
            if(empty($application_id)){
                if($scheme_details->allow_ds_entry==0 && $scheme_details->allow_normal_entry==0) {
                    return redirect("/")->with('error', 'New Entry is disabled');
                }
                    $entry_allowed = BlkUrbanlEntryMapping::where('main_entry', true)->where('block_ulb_code',  $blockCode)->count();
                    $entry_allowed_normal = BlkUrbanlEntryMapping::where('normal_entry', true)->where('block_ulb_code',  $blockCode)->count();

                    if ($entry_allowed == 0 && $entry_allowed_normal==0) {
                        return redirect("/")->with('error', 'New Entry is disabled');
                    }
                   
                    
           }

            $district_list = District::select(
                'id',
                'district_code',
                'district_name',
                'rch_district_code',
                'is_revenue_district',
                'state_code',
                'district_status'
            )->get();


            $personalCount = 0;
            $contactCount = 0;
            $bankCount = 0;
            $encolserCount = 0;
            $otherCount = 0;
            if (isset($request->application_id)) {
                $getModelFunc = new getModelFunc();
                $DraftPersonalTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($dist_code, $this->source_type, 1, 1);
                $DraftPersonalTable->setTable('' . $Table);

                $personal_data = $DraftPersonalTable->select(
                    'application_id',
                    'tab_code',
                    'duare_sarkar_registration_no',
                    'duare_sarkar_date',
                    'ben_fname',
                    'father_fname',
                    'father_mname',
                    'father_lname',
                    'mother_fname',
                    'mother_mname',
                    'mother_lname',
                    'dob',
                    'age_ason_01012021',
                    'caste',
                    'caste_certificate_no',
                    'marital_status',
                    'spouse_fname',
                    'spouse_mname',
                    'spouse_lname',
                    'mobile_no',
                    'life_certificate_checked',
                    'life_certificate_pass','caste_matched_with_certificate_no','caste_certificate_checked','last_biometric','life_certificate_lastdatetime','caste_certificate_check_lastdatetime','aadhaar_no_checked','aadhaar_no_checked_lastdatetime','aadhaar_no_validation_msg','aadhaar_no_checked_pass',
                    'dob_kh','dob_is_match_kh','entry_type', 'application_date'
                )->where(['created_by_local_body_code' => $blockCode, 'application_id' => $request->application_id])->first();
                if (empty($personal_data->ben_fname)) {
                    return redirect("/")->with('error', 'Application Not Found');
                }
                if (!empty($personal_data->dob)) {
                    $personal_data->ben_age = $this->ageCalculate($personal_data->dob);
                }
                $personalCount = 1;
                $DraftAadharTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($dist_code, $this->source_type, 2, 1);
                $DraftAadharTable->setConnection('pgsql_appread');
                $DraftAadharTable->setTable('' . $Table);
                $AadharData = $DraftAadharTable->select('encoded_aadhar')->where('application_id', $personal_data->application_id)->first();
                if (!empty($AadharData)) {
                    $personal_data->aadhar_no = Crypt::decryptString($AadharData->encoded_aadhar);
                } else {
                    $personal_data->aadhar_no = '';
                }
                $personal_arr = $personal_data->toArray();
                $rowArr = array_merge($rowArr, $personal_arr);
                $tab_code = $personal_data->tab_code;
                
            } else {
                $tab_code = 0;
            }
            //dd($tab_code);


            if ($tab_code > 0) {
                $DraftContactTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($dist_code, $this->source_type, 3, 1);
                $DraftAadharTable->setConnection('pgsql_appread');
                $DraftContactTable->setTable('' . $Table);
                $contactData = $DraftContactTable->select('dist_code', 'block_ulb_code', 'block_ulb_name', 'gp_ward_code', 'gp_ward_name', 'police_station', 'village_town_city', 'house_premise_no', 'post_office', 'residency_period',  'pincode', 'rural_urban_id')->where('application_id', $application_id)->first();

                if (!empty($contactData)) {
                    $contact_arr = $contactData->toArray();
                    $rowArr = array_merge($rowArr, $contact_arr);
                    $contactCount = 1;
                    if ($contactData->rural_urban_id == 1) {
                        $block_ulb_list = UrbanBody::select('urban_body_code as block_ulb_code', 'urban_body_name as block_ulb_name')->where('district_code', $contactData->dist_code)->get();
                        $gp_ward_list = Ward::select('urban_body_ward_code as gp_ward_code', 'urban_body_ward_name as gp_ward_name')->where('urban_body_code', $contactData->block_ulb_code)->get();
                    } else {
                        $block_ulb_list = Taluka::select('block_code as block_ulb_code', 'block_name as block_ulb_name')->where('district_code', $contactData->dist_code)->get();
                        $gp_ward_list = GP::select('gram_panchyat_code as gp_ward_code', 'gram_panchyat_name as gp_ward_name')->where('block_code', $contactData->block_ulb_code)->get();
                    }
                } else {
                    $contactCount = 0;
                }
                $DraftBankTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($dist_code, $this->source_type, 4, 1);
                $DraftBankTable->setConnection('pgsql_appread');
                $DraftBankTable->setTable('' . $Table);
                $bankData = $DraftBankTable->select('bank_code', 'bank_name', 'branch_name', 'bank_ifsc')->where('application_id', $application_id)->first();
                if (!empty($bankData)) {
                    $bank_arr = $bankData->toArray();
                    $rowArr = array_merge($rowArr, $bank_arr);
                    $bankCount = 1;
                } else {
                    $bankCount = 0;
                }
                $DraftPfImageTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($dist_code, $this->source_type, 5, 1);
                $DraftPfImageTable->setConnection('pgsql_encread');
                $DraftPfImageTable->setTable('' . $Table);
                $DraftEncloserTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($dist_code, $this->source_type, 6, 1);
                $DraftEncloserTable->setConnection('pgsql_encread');
                $DraftEncloserTable->setTable('' . $Table);
                $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
                $profileImagedata = $DraftPfImageTable->where('image_type', $doc_profile->id)->where('application_id', $application_id)->first();
                $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type');
                if (empty($profileImagedata) && count($encolserdata) == 0) {
                    $encolserCount = 0;
                } else {
                    $encolserCount = 1;
                }
                $DraftOtherTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($dist_code, $this->source_type, 7, 1);
                $DraftOtherTable->setConnection('pgsql_appread');
                $DraftOtherTable->setTable('' . $Table);
                $otherData = $DraftOtherTable->select('is_resident', 'earn_monthly_remuneration', 'info_genuine_decl')->where('application_id', $application_id)->first();
                if (!empty($otherData)) {
                    $otherDataArr = $otherData->toArray();
                    $rowArr = array_merge($rowArr, $otherDataArr);
                    $otherCount = 1;
                } else {
                    $otherCount = 0;
                }
            }

            //dd( $contactCount);

            //$rowArr_collection = collect([$rowArr]);
            $rowArr_collection = (object) $rowArr;
            //dd($rowArr_collection);
            $document_msg = "";
            $doc_profile_image = DocumentType::get()
                ->where("is_profile_pic", true)->first();

            if ($doc_profile_image) {
                $doc_profile_image_id = $doc_profile_image->id;
            }
            $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first()->toArray();
            //dd($doc_id_list['doc_list_man']);
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
            //dd($doc_list);
            if (count($doc_list) > 0) {
                foreach ($doc_list as $doc) {
                    $encloser_list[$i]['application_id'] = $application_id;
                    $encloser_list[$i]['id'] = $doc['id'];
                    $encloser_list[$i]['is_profile_pic'] = intval($doc['is_profile_pic']);
                    $encloser_list[$i]['doc_size_kb'] = $doc['doc_size_kb'];
                    $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                    $encloser_list[$i]['doc_type'] = $doc['doc_type'];
                    if (in_array($doc['id'], json_decode($doc_id_list['doc_list_man']))) {
                        $encloser_list[$i]['required'] = 1;
                    } else {
                        $encloser_list[$i]['required'] = 0;
                    }
                    if ($doc['is_profile_pic']) {
                        if ($encolserCount == 1) {
                            if (!empty($profileImagedata->application_id)) {
                                $encloser_list[$i]['can_download'] = 1;
                            } else {
                                $encloser_list[$i]['can_download'] = 0;
                            }
                        } else {
                            $encloser_list[$i]['can_download'] = 0;
                        }
                    } else {
                        //dd($encolserdata);
                        if ($encolserCount == 1) {
                            if (in_array($doc['id'], $encolserdata->toArray())) {
                                $encloser_list[$i]['can_download'] = 1;
                            } else {
                                $encloser_list[$i]['can_download'] = 0;
                            }
                        } else {
                            $encloser_list[$i]['can_download'] = 0;
                        }
                    }
                    $i++;
                }
            }
            $cur_ds_phase_arr = DsPhase::where('is_current', TRUE)->first();
            $ds_phase_text='';
            if (!empty($cur_ds_phase_arr)) {
                if($cur_ds_phase_arr->is_samadhan==TRUE){
                    $ds_phase_text='Samasyaa Samadhan Jan Sanjog';
                }
                else{
                    $ds_phase_text='Duare Sarkar';
                }
            }
            
            $errormsg = Config::get('constants.errormsg');

           // dd( $errormsg['sessiontimeOut']);
            return view('LbForm/EditForm', [
                'row' => $rowArr_collection,
                'application_id' => $application_id,
                'max_dob' => $max_dob,
                'min_dob' => $this->min_dob,
                'district_list' => $district_list,
                'block_ulb_list' => $block_ulb_list,
                'gp_ward_list' => $gp_ward_list,
                'doc_list_man' => $doc_list_man,
                'doc_list_opt' => $doc_list_opt,
                'encloser_list' => $encloser_list,
                'profile_img' => $doc_profile_image_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'tab_code' => $tab_code,
                'dob_base_date' => $dob_base_date,
                'dob_base_date' => $dob_base_date,
                'personalCount' => $personalCount,
                'contactCount' => $contactCount,
                'bankCount' => $bankCount,
                'otherCount' => $otherCount,
                'encolserCount' => $encolserCount,
                'status' => $status,
                'scheme_details' => $scheme_details,
                'ds_phase_text' => $ds_phase_text,
                'type' => $type,
                'grievance_id' => $grievance_id,
            ]);
        }
    }
    public function shemeSessionCheck(Request $request)
    {
        $scheme_id = 0;

        if ($request->get('pr1')) {
            if ($request->get('pr1') == "lb_wcd") {
                $scheme_id = 20;
            } else {
                return redirect("/")->with('error', ' Parameter Invalid');
            }
        } else {
            return redirect("/")->with('error', 'Method is not valid');
        }

        $is_active = 0;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $request->session()->put('level', $roleObj['mapping_level']);
                $distCode = $roleObj['district_code'];
                $request->session()->put('distCode', $roleObj['district_code']);
                $request->session()->put('scheme_id', $scheme_id);
                $request->session()->put('is_first', $roleObj['is_first']);
                $request->session()->put('is_urban', $roleObj['is_urban']);
                $request->session()->put('role_id', $roleObj['id']);
                if ($roleObj['is_urban'] == 1) {
                    $request->session()->put('bodyCode', $roleObj['urban_body_code']);
                } else {
                    $request->session()->put('bodyCode', $roleObj['taluka_code']);
                }
                break;
            }
        }
        if ($is_active == 1) {

            //  $ben_table = 'dist_' . $distCode . '.beneficiary';
            return true;
        } else {
            return false;
        }
    }
    public function forwardData(Request $request)
    {
        //dd('ok');
        $this->shemeSessionCheck($request);
        $scheme_id = $request->session()->get('scheme_id');
        $mappingLevel = $request->session()->get('level');
        $district_code = $request->session()->get('distCode');
        $is_first = $request->session()->get('is_first');
        $is_urban = $request->session()->get('is_urban');
        $urban_body_code = $request->session()->get('bodyCode');
        $taluka_code = $request->session()->get('bodyCode');
        $role_id = $request->session()->get('role_id');
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $id = $request->id;
        $Verified = "Verified";
        $Rejected = 1;
        $comments = $request->comments;
        $errormsg = Config::get('constants.errormsg');
        $duty = Configduty::where('user_id', '=', $user_id)->where('scheme_id', $scheme_id)->first();
        if ($duty->isEmpty) {
            return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
        }
        if ($designation_id == 'Delegated Verifier') {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Verifier')->where('stack_level', 'Block')->first();
        } elseif ($designation_id == 'Delegated Approver') {
            
                $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Approver')->where('stack_level', 'District')->first();
            
        }else {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', $designation_id)->where('stack_level', $duty->mapping_level)->first();
        }
      
        if ($role->isEmpty) {
            return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
        }
        if ($designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_bulk = $request->is_bulk;
            if ($is_bulk == 1) {
                $is_bulk = 1;
            } else {
                $is_bulk = 0;
                if (empty($id)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Found']);
                }
                if (!ctype_digit($id)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
            }
        } else {
            $is_bulk = 0;
            if (empty($id)) {
                return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Found']);
            }
            if (!ctype_digit($id)) {
                return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid dd']);
            }
        }


        // $modelName = new DataSourceCommon;
        // $table2 = 'dist_' . $district_code . '.beneficiary';
        // $modelName->setTable('' . $table2);

        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 1,  1);
        $personal_model->setTable('' . $Table);
        $pension_details_aadhar = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 2, 1);
        $pension_details_aadhar->setTable('' . $Table);



        $opreation_type = $request->opreation_type;
        if ($is_bulk == 1) {
           
        }
        //dd($id);
        if ($opreation_type == 'A') { //echo 1;die;
            $approval_allowded = BlkUrbanlEntryMapping::where('main_approval', true)->where('district_code',  $district_code)->count();
            $approval_allowded_normal = BlkUrbanlEntryMapping::where('normal_approval', true)->where('district_code',  $district_code)->count();

            if ($approval_allowded == 0 && $approval_allowded_normal==0) {
                return response()->json(['return_status' => 0, 'return_msg' => 'Approval is temporarily suspended']);
            }
            if ($is_bulk == 0) {
                $row = $personal_model->select('application_id')->where('application_id', $id)->where('next_level_role_id', $role->id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
                $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
                $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
                // if(($count_approved+$count_rejected)>=507002){
                //     return response()->json(['return_status' => 0, 'return_msg' => 'Approval quota has been exceeded']);

                // }
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);
                // print_r( $applicant_id_in);die;
                $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->where('next_level_role_id', $role->id)->where('created_by_dist_code', $request->session()->get('distCode'))->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
                $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
                $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
                // if(($count_approved+$count_rejected+count($applicant_id_in))>=507002){
                //     return response()->json(['return_status' => 0, 'return_msg' => 'Approval quota has been exceeded']);
    
                // }
            }
        } else if ($opreation_type == 'V') {
            return response()->json(['return_status' => 0, 'return_msg' => 'Verification is temporarily suspended']);
            $verification_allowded = BlkUrbanlEntryMapping::where('main_verification', true)->where('block_ulb_code', $request->session()->get('bodyCode'))->where('district_code',  $district_code)->count();
            $verification_allowded_normal = BlkUrbanlEntryMapping::where('normal_verification', true)->where('block_ulb_code', $request->session()->get('bodyCode'))->where('district_code',  $district_code)->count();

            if ($verification_allowded == 0 && $verification_allowded_normal==0) {
                return response()->json(['return_status' => 0, 'return_msg' => 'Verification is temporarily suspended']);
            }
            if ($is_bulk == 0) {
                $row = $personal_model->select('application_id')->where('application_id', $id)->whereNull('next_level_role_id')->where('created_by_dist_code', $request->session()->get('distCode'))->first();
                $row_aadhar = $pension_details_aadhar->select('application_id', 'encoded_aadhar', 'aadhar_hash')->where('application_id', $id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
                if (empty($row_aadhar->aadhar_hash)) {
                    $aadhar_hash = md5(Crypt::decryptString($row_aadhar->encoded_aadhar));
                    $aadhar_is_update = 1;
                } else {
                    $aadhar_is_update = 0;
                }
                $count_draft = DB::table('lb_scheme.draft_ben_personal_details')->where('next_level_role_id',43)->count();
                $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
                $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
                // if(($count_draft+$count_approved+$count_rejected)>=507002){
                //     return response()->json(['return_status' => 0, 'return_msg' => 'Verification quota has been exceeded']);
                // }
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);

                $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->whereNull('next_level_role_id')->whereNull('next_level_role_id')->where('created_by_dist_code', $request->session()->get('distCode'))->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
                $count_draft = DB::table('lb_scheme.draft_ben_personal_details')->where('next_level_role_id',43)->count();
                $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
                $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
                // if((count($applicant_id_in)+$count_draft+$count_approved+$count_rejected)>=507002){
                //     return response()->json(['return_status' => 0, 'return_msg' => 'Verification quota has been exceeded']);
                // }
                
            }
           
        } else if ($opreation_type == 'R') {
            if ($is_bulk == 0) {
                $row = $personal_model->select('application_id')->where('application_id', $id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);

                $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->where('created_by_dist_code', $request->session()->get('distCode'))->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
            }
        } else if ($opreation_type == 'T') {
            if ($is_bulk == 0) {
                $row = $personal_model->select('application_id')->where('application_id', $id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);

                $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->where('created_by_dist_code', $request->session()->get('distCode'))->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
            }
        }
        if ($is_bulk == 0 && empty($row->application_id)) {
            return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
        }
        $reject_cause = $request->reject_cause;
        $comments = trim($request->accept_reject_comments);


        if ($opreation_type == 'A') {
            $txt = 'Approved';
            $next_level_role_id = $role->parent_id;
            $rejected_cause = NULL;
            $message = 'Approved Succesfully!';
        } else if ($opreation_type == 'V') {
            $txt = 'Verified';
            $next_level_role_id = $role->parent_id;
            $rejected_cause = NULL;
            $message = 'Verified Succesfully!';
        } else if ($opreation_type == 'R') {
            $txt = 'Rejected';
            $next_level_role_id = -100;
            $rejected_cause = $reject_cause;
            $message = 'Rejected Succesfully!';
        } else if ($opreation_type == 'T') {
            $txt = 'Reverted';
            $next_level_role_id = -50;
            $rejected_cause = $reject_cause;
            $message = 'Reverted Succesfully!';
        }
        $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
,'next_level_role_id' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments];



        try {


            DB::beginTransaction();
            $applicationid_arr = array();
            $send_sms_arr = array();
            $aadhar_update_status = 1;
            if ($is_bulk == 1) {



                $is_status_updated = $personal_model->whereIn('application_id', $applicant_id_in)->update($input);
                $j = 0;
                //dd($row_list->toArray());
                foreach ($row_list as $app_row) {
                    $accept_reject_model = new DataSourceCommon;
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                    $accept_reject_model->setTable('' . $Table);
                    $accept_reject_model->op_type = $opreation_type;
                    $accept_reject_model->application_id = $app_row->application_id;
                    $accept_reject_model->designation_id = $designation_id;
                    $accept_reject_model->scheme_id = $scheme_id;
                    $accept_reject_model->user_id = $user_id;
                    $accept_reject_model->comment_message = $comments;
                    $accept_reject_model->rejected_reverted_cause = $rejected_cause;
                    $accept_reject_model->mapping_level = $mappingLevel;
                    $accept_reject_model->created_by = $user_id;
                    $accept_reject_model->created_by_level = $mappingLevel;
                    $accept_reject_model->created_by_dist_code = $district_code;
                    $accept_reject_model->created_by_local_body_code = $request->session()->get('bodyCode');
                    $accept_reject_model->ip_address = request()->ip();
                    $is_saved = $accept_reject_model->save();
                    if ($is_saved) {
                        $j++;
                    }
                }
                if (count($row_list) == $j) {
                    $remarks_status = 1;
                } else {
                    $remarks_status = 0;
                }
                $aadhar_update_status = 1;
            } else {
                $accept_reject_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                $accept_reject_model->setTable('' . $Table);
                $is_status_updated = $personal_model->where('application_id', $id)->update($input);
                $accept_reject_model->op_type = $opreation_type;
                $accept_reject_model->ben_id = $id;
                $accept_reject_model->application_id = $row->application_id;
                $accept_reject_model->designation_id = $designation_id;
                $accept_reject_model->scheme_id = $scheme_id;
                $accept_reject_model->user_id = $user_id;
                $accept_reject_model->comment_message = $comments;
                $accept_reject_model->mapping_level = $mappingLevel;
                $accept_reject_model->created_by = $user_id;
                $accept_reject_model->created_by_level = $mappingLevel;
                $accept_reject_model->created_by_dist_code = $district_code;
                $accept_reject_model->created_by_local_body_code = $request->session()->get('bodyCode');
                $accept_reject_model->rejected_reverted_cause = $rejected_cause;
                $accept_reject_model->ip_address = request()->ip();
                $is_saved = $accept_reject_model->save();

                if ($is_saved) {
                    $remarks_status = 1;
                } else {
                    $remarks_status = 0;
                }
                if ($opreation_type == 'V') {
                    if ($aadhar_is_update) {
                        $update_aadhar_arr = array();
                        $update_aadhar_arr['aadhar_hash'] = $aadhar_hash;
                        $update_aadhar_arr['action_by'] = Auth::user()->id;
                        $update_aadhar_arr['action_ip_address'] = request()->ip();
                        $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        try {
                            $aadhar_update_status = $pension_details_aadhar->where('application_id', $id)->update($update_aadhar_arr);
                        } catch (\Exception $e) {

                            DB::rollback();
                            $return_status = 0;
                            $return_msg = 'Aadhaar No. is Duplicate..';
                            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                        }
                    } else {
                        $aadhar_update_status = 1;
                    }
                } else {
                    $aadhar_update_status = 1;
                }
            }

            if ($opreation_type == 'A' || $opreation_type == 'R') {
                //echo 1;
                if ($is_bulk == 1) { //echo 1;
                    foreach ($row_list as $app_row) {
                        array_push($applicationid_arr, $app_row->application_id);
                    }
                    $implode_application_arr = implode("','", $applicationid_arr);
                    $in_pension_id = 'ARRAY[' . "'$implode_application_arr'" . ']';
                    if ($opreation_type == 'A') { //echo 2;
                       // $fun_return = DB::select("select lb_scheme.beneficiary_approve_final_wtSws(" . $in_pension_id . ")");
                       $fun_return = DB::select("select lb_scheme.beneficiary_approve_final_wtSws(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                    } else { //echo 3;
                        //$reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_wtSws(" . $in_pension_id . ")");
                        $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_wtSws(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                    }
                } else { // echo 4;
                    $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
                    if ($opreation_type == 'A') {  //echo 5;
                        //$fun_return = DB::select("select lb_scheme.beneficiary_approve_final_wtSws(" . $in_pension_id . ")");
                        $fun_return = DB::select("select lb_scheme.beneficiary_approve_final_wtSws(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                    } else { //echo 6;
                        //$reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_wtSws(" . $in_pension_id . ")");
                        $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_wtSws(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                    }
                }
                $aadhar_update_status = 1;
            }

            //  print_r($applicant_id_in);die;

            if ($is_status_updated && $remarks_status && $aadhar_update_status) {

                $return_status = 1;
                if ($is_bulk == 1) {
                    $return_msg = "Applications " . $message;
                } else
                    $return_msg = "Application with ID:" . $row->application_id . " " . $message;
            } else {

                $return_status = 0;
                $return_msg = $errormsg['roolback'];
            }
            DB::commit();
        } catch (\Exception $e) {
            if($id==136477130){
                 dd($e);

            }
            //dd($e);
            $return_status = 0;
            $return_msg = $errormsg['roolback'];
            //$return_msg = $e;
            DB::rollback();
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
    }
    public function checkCasteInfo(Request $request)
    {
        $scheme_id = $this->scheme_id;
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
       // dd($is_active);
        $return_status = 0;
        $return_msg = '';
        if ($is_active == 0 || empty($distCode)) {
            $return_status = 0;
            $return_text = 'User Disabled';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
        }
        $user_id = Auth::user()->id;
        $caste_certificate_no = $request->caste_certificate_no;
        if (empty($caste_certificate_no)) {
            $return_status = 0;
            $return_text = 'Caste Certificate Number is Required';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
        }
        $insert_arr=array();
        $c_time = date('Y-m-d H:i:s', time());
        $insert_arr['created_by_local_body_code']=$blockCode;
        $insert_arr['caste_certificate_no']=$caste_certificate_no;
        $insert_arr['api_hit_time']=$c_time;
        $insert_arr['loginid']=$user_id;
        if(!empty($request->module_type)){
        $insert_arr['module_type']=$request->module_type;
        }
        if(!empty($request->application_id)){
            $insert_arr['application_id']=$request->application_id;
        }
        $post_url = 'https://wbgw.napix.gov.in/wb/bcwd/certtificate_api/certdet';
        $curl = curl_init($post_url);
        $headers = array(
              'Content-Type: application/json',
              'client-id: ff2c12aacd4ceacbbecbaac9ab979007',
              'client-secret: ccb028f2e6aa601248a5edd74176b062',

        );
        $data = array("certno" => $caste_certificate_no);
        $data_string = json_encode($data);
        $scheme_id =$this->scheme_id;
        header("Access-Control-Allow-Origin: *");
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $post_response = curl_exec($curl);
        $c_time = date('Y-m-d H:i:s', time());
        $insert_arr['api_response_time']=$c_time;
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            $insert_arr['is_error']=1;
            $insert_arr['error_msg']=$error_msg;
        }
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
       // dd($httpcode);
        if($httpcode==200){
            $post_response=json_decode($post_response);
            $insert_arr['status']=$post_response[0]->status;
            $insert_arr['response_text']=json_encode($post_response);
            $return_status = 1;
            $return_msg='Caste Certificate Number Valid';
            
        }
        else if($httpcode==404){
            $post_response=json_decode($post_response);
            $insert_arr['response_text']=json_encode($post_response);
            $insert_arr['status']=$post_response->status;
            $insert_arr['message']=$post_response->message;
            $return_status = 2;
            $return_msg='Caste Certificate Number Not Valid';
        }else if($httpcode==401){
            $post_response=json_decode($post_response);
            $insert_arr['response_text']=json_encode($post_response);
            $insert_arr['status']=$post_response->httpMessage;
            $insert_arr['message']=$post_response->moreInformation;
            $return_status = 3;
            $return_msg='Invalid Crendential';
        }
        $insert=DB::table('lb_scheme.ben_caste_api_response_track')->insert($insert_arr);
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
       
    }
    public function isAadharValid($num)
    {
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
    function ajaxgetage(Request $request)
    {
        $diff = 0;
        if ($request->dob != '') {
            $diff = $this->ageCalculate($request->dob);
            // $diff = Carbon::parse($request->dob)->diffInYears($this->base_dob_chk_date);
        }
        return intval($diff);
    }
    function ageCalculate($dob)
    {
        $diff = 0;
        if ($dob != '') {
            // $diff = $this->ageCalculate($dob);
            $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
        }
        return intval($diff);
    }
   
}
