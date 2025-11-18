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
use Excel;
class FaultyLbEntryeditController extends Controller
{
    // use SendsPasswordResetEmails;
    use TraitCasteCertificateValidate;
    use TraitLifeCertificateValidate;
    use TraitAadharValidate;
    public function __construct()
    {
        $this->middleware('auth');
        $phaseArr = DsPhase::where('is_current', TRUE)->first();
        //$mydate = $phaseArr->base_dob;
        $myYear =  date("Y");
        $mydate =  $myYear.'-'.'01'.'-'.'01';
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

        //dd(123);
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
        if (isset($request->application_id)) {
            $application_id = $request->application_id;
            if (empty($application_id)) {
                $return_status = 0;
                $return_text = 'Application Id Not Found';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            if (!ctype_digit($application_id)) {
                $return_status = 0;
                $return_text = 'Application Id Not Found';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            $getModelFunc = new getModelFunc();
            $pension_details_aadhar = new DataSourceCommon;
            $Table = 'lb_scheme.ben_aadhar_details';
            $pension_details_aadhar->setTable('' . $Table);
            $cnt = $pension_details_aadhar->where('application_id', '!=', $application_id)->where('aadhar_hash', md5($aadhar_no))->count('application_id');
        } else {
            $getModelFunc = new getModelFunc();
            $pension_details_aadhar = new DataSourceCommon;
            $Table = 'lb_scheme.ben_aadhar_details';
            $pension_details_aadhar->setTable('' . $Table);
            $cnt = $pension_details_aadhar->where('aadhar_hash', md5($aadhar_no))->count('application_id');
        }
        if ($cnt > 0) {
            $return_status = 2;
            return response()->json(['return_status' => $return_status, 'return_msg' => 'Duplicate Aadhaar Number']);
        } else {
            $return_status = 1;
            return response()->json(['return_status' => $return_status, 'return_msg' => 'Aadhaar Number Available']);
        }
    }
    public function personalEntry(Request $request)
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
        $ds_cur_phase = $phaseArr->phase_code;
        $return_status = '';
        $return_msg = '';
        $max_tab_code = 1;
        $application_id = '';
        $today = date("Y-m-d");
        $entry_type_arr = array(1, 2);
        $rules = [
            'entry_type' => 'required|in:' . implode(",", $entry_type_arr),
            'duare_sarkar_registration_no' => 'required_if:entry_type,==,2|max:25',
            'duare_sarkar_date' => 'required_if:entry_type,2|nullable|date|before_or_equal:' . $today,
            'first_name' => 'required|string|max:200',
            'gender' => 'required|in:Female',
            'dob' => 'required|date|before_or_equal:' . $max_dob . '|after_or_equal:' . $min_dob,
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
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $Table='lb_scheme.faulty_ben_personal_details';
            $pension_personal_model = new DataSourceCommon;
            $pension_personal_model->setTable('' . $Table);

            $Table='lb_scheme.ben_aadhar_details';
            $pension_details_aadhar = new DataSourceCommon;
            $pension_details_aadhar->setTable('' . $Table);


            $Table='lb_scheme.ben_mobile_no_unique';
            $pension_details_contact = new DataSourceCommon;
            $pension_details_contact->setTable('' . $Table);

            $Table='lb_scheme.faulty_ben_personal_details_migrate';
            $pension_personal_migrate = new DataSourceCommon;
            $pension_personal_migrate->setTable('' . $Table);
            
            
            $scheme_obj = Scheme::where('id', $scheme_id)->where('is_active', 1)->first();
            $application_id = $request->application_id;
            $personalCount= $pension_personal_migrate->where('application_id', $application_id)->count();
         //dd($personalCount);
            
            DB::beginTransaction();
            $is_saved = 0;
            try {

               
                $pension_details = array();
                
                $pension_personal_migrate['entry_type'] = $request->entry_type;

                $application_row = $pension_personal_model->select('application_id','beneficiary_id')->where('application_id', $application_id)->first();
                $application_id = $request->application_id;
                $beneficiary_id = $application_row->beneficiary_id;
                
                if ($personalCount==0) {

                   

                   
                    if (!empty($application_id)) {
                      
                        //dd(666);
                       
                        $application_row = $pension_personal_model->select('application_id','beneficiary_id','created_by_local_body_code', 'mobile_no' )->where('application_id', $application_id)->first();

                       
                        $application_id = $application_row->application_id;
                        
                        $beneficiary_id = $application_row->beneficiary_id;
                        
                       
                        // dd($application_row->application_id);
                        if ($application_row->created_by_local_body_code != $blockCode) {
                            $return_status = 0;
                            $return_text = 'You are not allowded to do so';
                            $return_msg = array("" . $return_text);
                            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                        }
                        $pension_details = $pension_personal_model->where('application_id', $application_row->application_id)->first();
                       // $max_tab_code = $pension_details->tab_code;
                        $max_tab_code = 1;

                        // $cnt = $pension_details_contact->where('application_id', '!=', $application_id)->count('application_id');


                        // if ($cnt > 0) {
                        // $return_status = 2;
                        // return response()->json(['return_status' => $return_status, 'return_msg' => 'Duplicate mobile Number']);
                        // } 
                        if (!empty(trim($request->mobile_no))) {
                            //dd(88);
                            if ($application_row->mobile_no == trim($request->mobile_no)) {
                                //dd(123);
                                $sp_mobile_new = NULL;
                                $sp_mobile_old = NULL;
                            } else {
                                //dd(77);
                                $sp_mobile_new = trim($request->mobile_no);
                                if (empty(trim($application_row->mobile_no))) {
                                    $sp_mobile_old = NULL;
                                } else {
                                    $sp_mobile_old = $application_row->mobile_no;
                                }
                            }
                        } else {
                           // dd(666);
                            $sp_mobile_new = NULL;
                            $sp_mobile_old = NULL;
                        }
                    } else {
                       //dd(555);
                        $return_status = 0;
                        $return_text = 'Some error.Please try again';
                        $return_msg = array("" . $return_text);
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                } else {

                    $application_row = $pension_personal_migrate->select('application_id','beneficiary_id','created_by_local_body_code', 'mobile_no' )->where('application_id', $application_id)->first();
                    
                    
                    if (!empty(trim($request->mobile_no))) {
                        //dd(88);
                        if ($application_row->mobile_no == trim($request->mobile_no)) {
                           // dd(123);
                            $sp_mobile_new = NULL;
                            $sp_mobile_old = NULL;
                        } else {
                            //dd(77);
                            $sp_mobile_new = trim($request->mobile_no);
                            if (empty(trim($application_row->mobile_no))) {
                                $sp_mobile_old = NULL;
                            } else {
                                $sp_mobile_old = $application_row->mobile_no;
                            }
                        }
                    } else {
                       // dd(666);
                        $sp_mobile_new = NULL;
                        $sp_mobile_old = NULL;
                    }
                    $max_tab_code = 1;
                    // $sp_mobile_old = NULL;
                    // $sp_mobile_new = trim($request->mobile_no);
                }

               
                if ($request->entry_type == 2) {

                  //dd(123);
                 $pension_personal_migrate['duare_sarkar_registration_no'] = trim($request->duare_sarkar_registration_no);
                 $pension_personal_migrate['duare_sarkar_date'] = $request->duare_sarkar_date;
                }

                
                $pension_personal_migrate['application_id'] = $application_id;
                $pension_personal_migrate['beneficiary_id'] = $beneficiary_id;
                
                $pension_personal_migrate['ben_fname'] = trim($request->first_name);
                $pension_personal_migrate['gender'] = "Female";
                $pension_personal_migrate['dob'] = $request->dob;
                $pension_personal_migrate['age_ason_01012021'] = $diff;
                $pension_personal_migrate['father_fname'] = trim($request->father_first_name);
                $pension_personal_migrate['father_mname'] = trim($request->father_middle_name);
                $pension_personal_migrate['father_lname'] = trim($request->father_last_name);
                $pension_personal_migrate['mother_fname'] = trim($request->mother_first_name);
                $pension_personal_migrate['mother_mname'] = trim($request->mother_middle_name);
                $pension_personal_migrate['mother_lname'] = trim($request->mother_last_name);
                $pension_personal_migrate['caste'] = trim($request->caste_category);
                $pension_personal_migrate['caste_certificate_no'] = trim($request->caste_certificate_no);
                $pension_personal_migrate['aadhar_no'] = '********' . substr($post_aadhar_no, -4);
                $pension_personal_migrate['mobile_no'] = $request->mobile_no;
                $pension_personal_migrate['email'] = $request->email;
                $pension_personal_migrate['scheme_id'] =20;

                $pension_personal_migrate['spouse_fname'] = trim($request->spouse_first_name);
                $pension_personal_migrate['spouse_mname'] = trim($request->spouse_middle_name);
                $pension_personal_migrate['spouse_lname'] = trim($request->spouse_last_name);
                $pension_personal_migrate['no_aadhar'] =0;

                $pension_personal_migrate['created_by_level'] = $mapping_level;
                $pension_personal_migrate['created_by_dist_code'] = $distCode;
                $pension_personal_migrate['created_by_local_body_code'] = $blockCode;
                $pension_personal_migrate['created_by'] = $user_id;
                $pension_personal_migrate['ip_address'] = $request->ip();
                $pension_personal_migrate['tab_code'] = 1;

                $pension_personal_migrate['action_by'] = Auth::user()->id;
                $pension_personal_migrate['action_ip_address'] = request()->ip();
                $pension_personal_migrate['action_type'] = class_basename(request()->route()->getAction()['controller']);
                

                
               
                if ($personalCount==0) {
                    //dd(555);
                    $array = json_decode(json_encode($pension_personal_migrate), true);

                    try {
                        //    $is_saved_aadhar = $pension_details_aadhar->save();

                        
    
                        $is_saved = $pension_personal_migrate->where('application_id', $application_id)->insert($array);
                        } catch (\Exception $e) {
    
                           //dd($e);

                            DB::rollback();
                            $return_status = 0;
                            $return_text = 'Failed to Insert.';
                            $return_msg = array("" . $return_text);
                            $max_tab_code = 0;
                            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                           
                        }
                } else {

                  //dd(123);
                    $array = json_decode(json_encode($pension_personal_migrate), true);
                   
                    try {
                        //    $is_saved_aadhar = $pension_details_aadhar->save();
    
                        $is_saved = $pension_personal_migrate->where('application_id', $application_id)->update($array);
                        } catch (\Exception $e) {
    
                           //dd($e);

                            DB::rollback();
                            $return_status = 0;
                            $return_text = 'Failed to Update.';
                            $return_msg = array("" . $return_text);
                            $max_tab_code = 0;
                            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                           
                        }
                }

               // dd($is_saved);
                if ($personalCount == 0) {

                
                    // $application_row = $pension_personal_model->select('application_id')->where('application_id', $application_id)->first();

                    $update_aadhar_arr = array();
                    $update_aadhar_arr['application_id'] = $application_row->application_id;
                    $update_aadhar_arr['encoded_aadhar'] = Crypt::encryptString($post_aadhar_no);
                    $update_aadhar_arr['aadhar_hash'] = md5($post_aadhar_no);
                    $update_aadhar_arr['created_by_level'] = $mapping_level;
                    $update_aadhar_arr['created_by'] = $user_id;
                    $update_aadhar_arr['ip_address'] = $request->ip();
                    $update_aadhar_arr['ip_address'] = $request->ip();

                    $update_aadhar_arr['created_by_dist_code'] = $distCode;
                    $update_aadhar_arr['beneficiary_id'] = $application_row->beneficiary_id;


                    // $pension_details_aadhar->encoded_aadhar = Crypt::encryptString($post_aadhar_no);
                    // $pension_details_aadhar->aadhar_hash = md5($post_aadhar_no);
                    // $pension_details_aadhar->application_id =   $application_row->application_id;
                    // $pension_details_aadhar->created_by_level = $mapping_level;
                    // $pension_details_aadhar->created_by = $user_id;
                    // $pension_details_aadhar->ip_address = $request->ip();
                    // $pension_details_aadhar->created_by_dist_code = $distCode;
                    // $pension_details_aadhar->created_by_local_body_code = $blockCode;
                    try {
                    //    $is_saved_aadhar = $pension_details_aadhar->save();

                    $is_saved_aadhar=  $pension_details_aadhar->where('application_id', $application_id)->insert($update_aadhar_arr);
                    } catch (\Exception $e) {

                       //dd($e);
                        DB::rollback();
                        $return_status = 0;
                        $return_text = 'Duplicate Aadhaar No.';
                        $return_msg = array("" . $return_text);
                        $max_tab_code = 0;
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                    }
                    $application_id = $application_row->application_id;
                    $op_type = 'F';
                    // $modelNameAcceptReject->op_type =  $op_type;
                    // $modelNameAcceptReject->application_id = $application_id;
                    // $modelNameAcceptReject->designation_id = $designation_id;
                    // $modelNameAcceptReject->scheme_id = $scheme_id;
                    // $modelNameAcceptReject->mapping_level = $mapping_level;
                    // $modelNameAcceptReject->created_by = $user_id;
                    // $modelNameAcceptReject->created_by_level = trim($mapping_level);
                    // $modelNameAcceptReject->created_by_dist_code = $distCode;
                    // $modelNameAcceptReject->created_by_local_body_code = $blockCode;
                    // $modelNameAcceptReject->ip_address = request()->ip();
                    // $is_accept_reject = $modelNameAcceptReject->save();

                    //$request->session()->put('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id, $application_id);
                    $return_text = 'Personal details has been successfully inserted. Your Application Id:' . $application_row->application_id;
                } else {

                   //dd(123);
                    $update_aadhar_arr = array();
                    $update_aadhar_arr['application_id'] = $application_row->application_id;
                    $update_aadhar_arr['encoded_aadhar'] = Crypt::encryptString($post_aadhar_no);
                    $update_aadhar_arr['aadhar_hash'] = md5($post_aadhar_no);
                    $update_aadhar_arr['created_by_level'] = $mapping_level;
                    $update_aadhar_arr['created_by'] = $user_id;
                    $update_aadhar_arr['ip_address'] = $request->ip();
                    $update_aadhar_arr['beneficiary_id'] = $application_row->beneficiary_id;
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
                   //dd('123');
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
              
                DB::rollback();
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                $max_tab_code = 0;
            }
           

            if ($is_saved) {

               //dd(123);
                $ben_fullname=trim($request->first_name);
                // $ben_fullname='PRATIMA BISWAS';
                // $post_aadhar_no='0530171824';

                //dd($ben_fullname);
                $return_status = 1;
                DB::commit();
                
                // $session_lb_lifecertificate=1;
                //  $session_lb_aadhaar_no=1;
               
                //$session_lb_castecertificate=1;
               
                 $api_code=3;

                $session_lb_lifecertificate=$this->bioauthcheckInsert($distCode,$application_id,$ben_fullname,$request->ip(),$post_aadhar_no,$blockCode,$user_id,$api_code);
                 $session_lb_aadhaar_no=$this->RationcheckInsert($distCode,$application_id,$ben_fullname,$request->ip(),$post_aadhar_no,$blockCode,$user_id,$request->dob,$api_code);

                
            if($request->caste_category=='SC' || $request->caste_category=='ST'){

                
            $session_lb_castecertificate=$this->casteInfoCheckInsert($distCode,$application_id,$ben_fullname,$request->ip(),trim($request->caste_certificate_no),$blockCode,$user_id,$api_code);

            //dd($session_lb_castecertificate);
            }
            else{
                $session_lb_castecertificate=array();
            }


            } else {

                
                $return_status = 0;
                $return_text = 'Some error.Please try again....';
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

        //dd($request->all());
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
            // $getModelFunc = new getModelFunc();
            // $pension_details_contact = new DataSourceCommon;
            // $Table = $getModelFunc->getTable($distCode, $this->source_type, 3, 1);
            // $pension_details_contact->setTable('' . $Table);
           
            $contactCount = $request->contactCount;
             //dd($contactCount);
            // $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
            $application_id = $request->application_id;
            // dd($application_id);
            if (!empty($application_id)) {

               

                $personal_model_contact = array();
                $contactCount = $request->contactCount;
                $personal_model = new DataSourceCommon;
                $Table = 'lb_scheme.faulty_ben_personal_details';
               // $Table = 'lb_scheme.faulty_ben_contact_details';
               $personal_model->setTable('' . $Table);

               $personal_model_contact = new DataSourceCommon;
                $Table = 'lb_scheme.faulty_ben_contact_details_migrate';
                $personal_model_contact->setTable('' . $Table);

               
           

                
                
                $personal_details_arr = $personal_model->select('application_id', 'created_by_local_body_code','beneficiary_id')->where('application_id', $application_id)->first();
                $beneficiary_id = $personal_details_arr->beneficiary_id;

               
                if ($personal_details_arr->created_by_local_body_code != $blockCode) {
                    $return_status = 0;
                    $return_text = 'You are not allowded to do so';
                    $return_msg = array("" . $return_text);
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                }
                //$max_tab_code = $personal_details_arr->tab_code;
                $max_tab_code = 1;
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            DB::beginTransaction();
            $is_saved = 0;

            $contactCount= $personal_model_contact->where('application_id', $application_id)->count();

            
            try {
                $personal_model_migrate=array();
                if (!empty($personal_details_arr) && intval($personal_details_arr->tab_code) < 2) {
                    $input = [
                        'tab_code' =>  2
                    ];
                    //$tab_max_code_saved = $personal_model->where('application_id', $application_id)->update($input);
                    $max_tab_code=1;
                    $max_tab_code = $max_tab_code + 1;
                } else {
                    $tab_max_code_saved = 2;
                }
                if ($request->urban_code == 1) {
                    $block_ulb = UrbanBody::where('urban_body_code', $request->block)->first();
                    $gp_ward = Ward::where('urban_body_ward_code', $request->gp_ward)->first();
                    $personal_model_migrate['block_ulb_name'] = trim($block_ulb->urban_body_name);
                    $personal_model_migrate['gp_ward_name']   = trim($gp_ward->urban_body_ward_name);
                } else {
                    $block_ulb =  Taluka::where('block_code', $request->block)->first();
                    $gp_ward =  GP::where('gram_panchyat_code', $request->gp_ward)->first();
                    $personal_model_migrate['block_ulb_name'] = trim($block_ulb->block_name);
                    $personal_model_migrate['gp_ward_name']   = trim($gp_ward->gram_panchyat_name);
                }
                
                $personal_model_migrate['beneficiary_id'] =  $beneficiary_id;
                $personal_model_migrate['dist_code']       =      $request->district;
                $personal_model_migrate['rural_urban_id']     =      $request->urban_code;
                $personal_model_migrate['police_station']  = trim($request->police_station);
                $personal_model_migrate['block_ulb_code']  = $request->block;
                $personal_model_migrate['gp_ward_code'] = $request->gp_ward;
                $personal_model_migrate['village_town_city']  = trim($request->village);
                $personal_model_migrate['house_premise_no']  = trim($request->house_premise_no);
                $personal_model_migrate['post_office']   = trim($request->post_office);
                $personal_model_migrate['pincode']  = trim($request->pin_code);
                $personal_model_migrate['created_by']  = Auth::user()->id;
                $personal_model_migrate['created_by_level']  = $mapping_level;
                $personal_model_migrate['created_by_dist_code'] = $distCode;
                $personal_model_migrate['created_by_local_body_code'] = $blockCode;
                $personal_model_migrate['ip_address']  = $request->ip();
                $personal_model_migrate['tab_code']  = 2;
                $personal_model_migrate['action_by'] = Auth::user()->id;
                $personal_model_migrate['action_ip_address'] = request()->ip();
                $personal_model_migrate['action_type'] = class_basename(request()->route()->getAction()['controller']);
                if ($contactCount) {

                    //dd(555);
                    $is_saved = $personal_model_contact->where('application_id', $application_id)->update($personal_model_migrate);
                } else {

                    //dd(444);
//echo 555;
                    $personal_model_migrate['application_id'] = $application_id;
                    $is_saved = $personal_model_contact->insert($personal_model_migrate);
                }
                $return_status = 1;
                $return_text = "Contact details has been successfully updated";
                $return_msg = array("" . $return_text);
            } catch (\Exception $e) {

                //dd($e);

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
        // $scheme_id = $this->scheme_id;
        // $return_status = '';
        // $return_msg = '';
        // $max_tab_code = 3;
        // $roleArray = $request->session()->get('role');
        // $is_active = 0;
        // foreach ($roleArray as $roleObj) {
        //     if ($roleObj['scheme_id'] == $scheme_id) {
        //         $is_active = 1;
        //         $mapping_level = $roleObj['mapping_level'];
        //         $distCode = $roleObj['district_code'];
        //         $is_urban = $roleObj['is_urban'];
        //         if ($roleObj['is_urban'] == 1) {
        //             $blockCode = $roleObj['urban_body_code'];
        //         } else {
        //             $blockCode = $roleObj['taluka_code'];
        //         }
        //         break;
        //     }
        // }
        // if ($is_active == 0 || empty($distCode)) {
        //     $return_status = 0;
        //     $return_text = 'User Disabled';
        //     $return_msg = array("" . $return_text);
        //     return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        // }

        // $rules = [
        //     'bank_ifsc_code' => 'required',
        //     'name_of_bank' => 'required|string|max:200',
        //     'bank_branch' => 'required|string|max:200',
        //     'bank_account_number' => 'required|numeric|required_with:confirm_bank_account_number|same:confirm_bank_account_number',
        //     'confirm_bank_account_number' => 'required|numeric',

        // ];
        // $attributes = array();
        // $messages = array();
        // $attributes['bank_ifsc_code'] = 'IFS Code';
        // $attributes['name_of_bank'] = 'Bank Name';
        // $attributes['bank_branch'] = 'Bank Branch Name';
        // $attributes['bank_account_number'] = 'Bank Account Number';

        // $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        // if ($validator->passes()) {

            
        //         $return_status = 1;
        //         $return_text = 'Bank Details submited';
        //         $return_msg = array("" . $return_text);
        //         $max_tab_code=3;
           
        // } else {
        //     $return_status = 0;
        //     $return_msg = $validator->errors()->all();
        // }

        $return_status = 1;
        $return_text = 'Bank Details Verified';
        $return_msg = array("" . $return_text);
        $max_tab_code=3;
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
            $Table='lb_scheme.faulty_ben_personal_details_migrate';
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
            $Table1 = $getModelFunc->getTableFaulty($distCode, $this->source_type, 5, 1);
            $pension_details_encloser1->setConnection('pgsql_encwrite');
            $pension_details_encloser1->setTable('' . $Table1);
            $pension_details_encloser2 = new DataSourceCommon;
            $Table2 = $getModelFunc->getTableFaulty($distCode, $this->source_type, 6, 1);
            $pension_details_encloser2->setConnection('pgsql_encwrite');
            $pension_details_encloser2->setTable('' . $Table2);


            $pension_details_encloser3 = new DataSourceCommon;
            $Table3 = $getModelFunc->getTable($distCode, $this->source_type, 5, 1);
            $pension_details_encloser3->setConnection('pgsql_encwrite');
            $pension_details_encloser3->setTable('' . $Table3);
            $pension_details_encloser4 = new DataSourceCommon;
            $Table4 = $getModelFunc->getTable($distCode, $this->source_type, 6, 1);
            $pension_details_encloser4->setConnection('pgsql_encwrite');
            $pension_details_encloser4->setTable('' . $Table4);


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
                       

                    //dd($pension_details);
                    if ($is_profile) {
                       
                    // dump($pension_details_encloser1);
                    //dump($pension_details_encloser3);


                        $crd_status_2 = $pension_details_encloser1->where('image_type', $doc_arr->id)->where('application_id', $application_id)->update($pension_details);
                        $crd_status_3 = $pension_details_encloser3->where('image_type', $doc_arr->id)->where('application_id', $application_id)->update($pension_details);
                    } 
                    else
                    {
                   
                        $crd_status_2 = $pension_details_encloser2->where('document_type', $doc_arr->id)->where('application_id', $application_id)->update($pension_details);
                        $crd_status_3 = $pension_details_encloser4->where('document_type', $doc_arr->id)->where('application_id', $application_id)->update($pension_details);

                    }  
                    } else {
                   // dd(777);

                        $pension_details['application_id'] = $application_id;
                        if ($is_profile) {
                            $crd_status_2 = $pension_details_encloser1->insert($pension_details);
                            $crd_status_3 = $pension_details_encloser3->insert($pension_details);
                        } else {
                            $crd_status_2 = $pension_details_encloser2->insert($pension_details);
                            $crd_status_3 = $pension_details_encloser4->insert($pension_details);
                        }
                    }
                    if ($crd_status_2 == 1 && $crd_status_3==1)  {
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

                //dd($e);
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
        $Table='lb_scheme.faulty_ben_personal_details_migrate';
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
        $Table = $getModelFunc->getTableFaulty($distCode, $this->source_type, 5, 1);
        $pension_details_encloser1->setConnection('pgsql_encwrite');
        $pension_details_encloser1->setTable('' . $Table);
        $pension_details_encloser2 = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaulty($distCode, $this->source_type, 6, 1);
        $pension_details_encloser2->setConnection('pgsql_encwrite');
        $pension_details_encloser2->setTable('' . $Table);


        $pension_details_encloser3 = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 5, 1);
        $pension_details_encloser3->setConnection('pgsql_encwrite');
        $pension_details_encloser3->setTable('' . $Table);
        $pension_details_encloser4 = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 6, 1);
        $pension_details_encloser4->setConnection('pgsql_encwrite');
        $pension_details_encloser4->setTable('' . $Table);

        $profileCount = $pension_details_encloser1->where('image_type', $profile_image_arr->id)->where('application_id', $application_id)->count('image_type');
        $encolserdata = $pension_details_encloser2->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type')->toArray();

        $profileCount_main = $pension_details_encloser3->where('image_type', $profile_image_arr->id)->where('application_id', $application_id)->count('image_type');
    

        $profile_image_faulty = $pension_details_encloser1->where('image_type', $profile_image_arr->id)->where('application_id', $application_id)->first();

        $encolser_data_faulty = $pension_details_encloser2->select('document_type','attched_document','document_extension','document_mime_type')->where('application_id', $application_id)->get();

         //dd($encolser_data_faulty);
        $pension_details = array();

        $pension_details['created_at'] = date('Y-m-d H:i:s');
        $pension_details['application_id'] = $profile_image_faulty->application_id;
        $pension_details['image_type'] = $profile_image_arr->id;
        $pension_details['profile_image'] = $profile_image_faulty->profile_image;
        $pension_details['image_extension'] = $profile_image_faulty->image_extension;
        $pension_details['image_mime_type'] = $profile_image_faulty->image_mime_type;

        $pension_details['created_by_level'] = $mapping_level;
        $pension_details['created_by'] = Auth::user()->id;
        $pension_details['ip_address'] = $request->ip();
        $pension_details['created_by_dist_code'] = $distCode;
        $pension_details['created_by_local_body_code'] = $blockCode;


        if($profileCount_main==0)
        {
           
            $crd_status_2 = $pension_details_encloser3->insert($pension_details);


        }

        foreach ($encolser_data_faulty as $encolse_doc) {
  
            $encolser_data_faulty_main = $pension_details_encloser4->select('document_type','attched_document','document_extension','document_mime_type')->where('application_id', $application_id)->where('document_type',$encolse_doc['document_type'])->first();


            $pension_details_attach = array();

            $pension_details_attach['created_at'] = date('Y-m-d H:i:s');
            $pension_details_attach['application_id'] =$application_id;
            $pension_details_attach['document_type'] = $encolse_doc['document_type'];
            $pension_details_attach['attched_document'] = $encolse_doc['attched_document'];
            $pension_details_attach['document_extension'] = $encolse_doc['document_extension'];
            $pension_details_attach['document_mime_type'] = $encolse_doc['document_mime_type'];
    
            $pension_details_attach['created_by_level'] = $mapping_level;
            $pension_details_attach['created_by'] = Auth::user()->id;
            $pension_details_attach['ip_address'] = $request->ip();
            $pension_details_attach['created_by_dist_code'] = $distCode;
            $pension_details_attach['created_by_local_body_code'] = $blockCode;


//dd($encolser_data_faulty);
            if(empty($encolser_data_faulty_main))
            {

                $crd_status_3 = $pension_details_encloser4->insert($pension_details_attach);
            }
            
          }
        

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
                    'tab_code' =>  4,
                    'action_by' => Auth::user()->id,
                    'action_ip_address' => request()->ip(),
                    'action_type' => class_basename(request()->route()->getAction()['controller'])
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

            $return_text = 'Enclosure List (Self Attested)';
        } else {
            $return_msg = "Please Upload Mandatory Documents";
            $max_tab_code = $max_tab_code;
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
    }
    public function declarationEntry(Request $request)
    {
//dd($request->all());
        
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
                $Table='lb_scheme.faulty_ben_personal_details_migrate';
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
           
            $Table='lb_scheme.faulty_migrate_ben_declaration_details';
            
            $pension_details_other->setTable('' . $Table);
            $pension_details = array();
            $SourceChk = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 8);
            $SourceChk->setConnection('pgsql_appread');
            $SourceChk->setTable('' . $Table);
            $otherCount = $request->otherCount;
            $DraftPersonalTable = new DataSourceCommon;
            $Table='lb_scheme.faulty_ben_personal_details_migrate';
            $DraftPersonalTable->setTable('' . $Table);

            $modelNameAcceptReject = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 9);
            $modelNameAcceptReject->setConnection('pgsql_appread');
            $modelNameAcceptReject->setTable('' . $Table);

            $user_id = Auth::user()->id;

            $DraftPersonalTablefaulty = new DataSourceCommon;
            $Table='lb_scheme.faulty_ben_personal_details';
            $DraftPersonalTablefaulty->setTable('' . $Table);

            $personalCount= $pension_details_other->where('application_id', $application_id)->count();

            $personal_details_arr = $DraftPersonalTablefaulty->select('application_id', 'created_by_local_body_code','beneficiary_id')->where('application_id', $application_id)->first();
            $beneficiary_id=$personal_details_arr->beneficiary_id;

            //dd($beneficiary_id);

            DB::beginTransaction();
            $is_saved = 0;
            try {

                if (!empty($personal_details_arr) && intval($personal_details_arr->tab_code) < 5) {
                    $input = [
                        'tab_code' =>  5,
                        'action_by' => Auth::user()->id,
                        'action_ip_address' => request()->ip(),
                        'action_type' => class_basename(request()->route()->getAction()['controller'])
                    ];
                    $tab_max_code_saved = $personal_model->where('application_id', $application_id)->update($input);
                } else {
                    $tab_max_code_saved = 1;
                }

                $pension_details['is_resident'] = trim($request->is_resident);
                $pension_details['earn_monthly_remuneration'] = trim($request->earn_monthly_remuneration);
                $pension_details['info_genuine_decl'] = trim($request->info_genuine_decl);
                $pension_details['beneficiary_id'] = $beneficiary_id;



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
                if ($personalCount) {

                   // dd(123);
                    $pension_details_other->where('application_id', $application_id)->update($pension_details);
                } else {
                    //dd(33);
                    $pension_details['application_id'] = $application_id;

                    $is_saved = $pension_details_other->insert($pension_details);
                }



                $updated_source_lb_id = 1;
                $updated_main_lb_id = 1;
                $update_dr_arr = array();
                // $update_dr_arr['is_final'] = TRUE;
                $update_dr_arr['faulty_migrate'] = 1;
                $update_dr_arr['next_level_role_id'] = 0;

                $update_main_arr = array();
                //$update_main_arr['is_final'] = TRUE;
                $update_main_arr['faulty_migrate'] = 1;
                if ($request->status == 1) {
                    $op_type = 'FAULTYVERIFY';
                    //$url = 'lb-draft-list';
                    $url = 'faulty-lb-draft-list';

                    
                } 
                $update_dr_arr['action_by'] = Auth::user()->id;
                $update_dr_arr['action_ip_address'] = request()->ip();
                $update_dr_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);

                $updated_source_lb_id = $DraftPersonalTable->where('application_id', $application_id)->update($update_dr_arr);

                $updated_main_lb_id = $DraftPersonalTablefaulty->where('application_id', $application_id)->update($update_main_arr);

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

                //dd($e);
                DB::rollback();
                return redirect("/lb-entry-draft-edit?status=" . $request->status . "&application_id=" . $application_id . "&tab_code=encloser")->with('error', 'Some error.Please try again ....');
            }
            DB::commit();
            $sms_send = 1;
            if ($sms_send == 1) {
                //dd(123);
                $mobileNo = $personal_details_arr->mobile_no;
                $message = 'Your Lakshmir Bhandar application is received with application ID ' . $application_id . ' . Lakshmir Bhandar, Govt of WB';
                $url_base = url('/');
                // dd($url_base);
                
               // $this->initiateSmsActivation($mobileNo, $message);
                
            }
            //dd($url);
            return redirect('/' . $url)->with('success', 'Application Updated Successfully and Sent to Approver for Approval')
                ->with('id',  $application_id);
        } else {
            //dd(666);
            $return_status = 0;
            $return_msg = $validator->errors()->all();
            return redirect("/lb-entry-draft-edit?status=" . $request->status . "&application_id=" . $application_id . "&tab_code=encloser")->with('errors', $return_msg);
        }
    }

    public function schemeSessionCheck(Request $request)
    {
        $scheme_id = $this->scheme_id;
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
            return true;
        } else {
            return false;
        }
    }
    public function viwlist(Request $request)
    {


       
       
        $ds_phase_list = DsPhase::all();

        $cur_ds_phase_arr = $ds_phase_list->where('is_current', TRUE)->first();
        $cur_ds_phase = $cur_ds_phase_arr->phase_code;
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $mappingLevel=null;

       

        //dd($serachvalue);

        if ($this->schemeSessionCheck($request)) 
        {

            $is_active=1;
            $distCode= $request->session()->get('distCode');
            $mappingLevel = $request->session()->get('level');
            $designation_id = Auth::user()->designation_id;
            $user_id = Auth::user()->id;
            $role_name = Auth::user()->designation_id;
            $is_rural_visible = 0;
            $urban_visible = 0;
            $munc_visible = 0;
            $gp_ward_visible = 0;
            $muncList = collect([]);
            $gpwardList = collect([]);



            // dd($mappingLevel);
         if ($role_name == 'Approver') 
         {
          


            $is_urban = $request->rural_urbanid;
            $district_code = $request->session()->get('distCode');
            $urban_body_code = $request->urban_body_code;
            $block_ulb_code = $request->block_ulb_code;
            $is_rural_visible = 1;
            $urban_visible = 1;
            $munc_visible = 1;
            $gp_ward_visible = 1;

            // $is_rural_visible = 0;
            // $urban_visible = 0;
            // $munc_visible = 0;
            // $gp_ward_visible = 0;

           

        } else if ($role_name == 'Verifier') 
        {
            $district_code = $request->session()->get('distCode');
            if ($mappingLevel == 'Block') {
                $block_ulb_code = NULL;
                $is_rural_visible = 0;
                $is_urban = 2;
                $munc_visible = 0;
                $urban_body_code = $request->session()->get('bodyCode');

               
                $block_ulb_code = NULL;
                $gpwardList = GP::where('block_code', $urban_body_code)->get();
                
                $gp_ward_visible = 1;
                // $gp_ward_visible = 0;
            } else if ($mappingLevel == 'Subdiv') {
                $block_ulb_code = $request->block_ulb_code;
                $urban_body_code = $request->session()->get('bodyCode');
                $is_rural_visible = 0;
                $is_urban = 1;
                $munc_visible = 1;
                $gp_ward_visible = 1;


                // $is_urban = 0;
                // $munc_visible = 0;
                // $gp_ward_visible = 0;
                $muncList = UrbanBody::where('sub_district_code', $urban_body_code)->get();
                $block_ulb_code = $request->block_ulb_code;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        
        if (request()->ajax()) 
        {
            //dd($request->all());
            
                $block_ulb_code = $request->block_ulb_code;
                $gp_ward_code = $request->gp_ward_code;
                $application_type=$request->application_type;

                //dd($application_type);

                if (!empty($request->search['value']))
                $serachvalue = trim($request->search['value']);
            else
                $serachvalue = '';

               // dd($serachvalue);
                //$serachvalue=$request->search;

                if( $application_type==3)
                {
                $condition = array();
                $Table = 'lb_scheme.faulty_ben_personal_details_migrate';
                $contact_table='lb_scheme.faulty_ben_contact_details_migrate';
                }
                else{

                $condition = array();
                $Table = 'lb_scheme.faulty_ben_personal_details';
                $contact_table='lb_scheme.faulty_ben_contact_details';

                }


           

            
            
           
            if (!empty($district_code)) {
                $condition[$Table . ".created_by_dist_code"] = $district_code;
            }
           // dd($is_urban);
            if (!empty($is_urban)) {
                // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
                if ($is_urban == 2) {
                    if (!empty($urban_body_code)) {
                        //$condition["rural_urban_id"] = 2;
                        $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                    }
                }
                //'Urban'
                if ($is_urban == 1) {
                    if (!empty($urban_body_code)) {
                        //$condition["rural_urban_id"] = 1;
                        $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                    }
                    if (!empty($block_ulb_code)) {
                        $condition[$contact_table . ".block_ulb_code"] = $block_ulb_code;
                    }
                }
            }
            if (!empty($gp_ward_code)) {

                //dd(123);
                $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;
            }
            
                if( $application_type==3)
                {
                    $modelName = new DataSourceCommon;
                    $getModelFunc = new getModelFunc();
                    $Table = 'lb_scheme.faulty_ben_personal_details_migrate';
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                   
                    $condition[$Table .".created_by_dist_code"] = $distCode;


                }
                else{
                    $modelName = new DataSourceCommon;
                    $getModelFunc = new getModelFunc();
                    $Table = 'lb_scheme.faulty_ben_personal_details';
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                   
                    $condition[$Table .".created_by_dist_code"] = $distCode;

                    
                }

            

            if ($role_name == 'Verifier') {
            $condition[$Table .".created_by_local_body_code"] = $urban_body_code;
            }
            $condition[$Table .".next_level_role_id"] = 0;
            // if (!empty($serachvalue))
            //     $serachvalue = trim($request->search);
            // else
            //     $serachvalue = '';

               


            
            $limit = $request->input('length');
            $offset = $request->input('start');
            $totalRecords = 0;
            //$serachvalue=$request->search;
            $filterRecords = 0;
            $data = array();
 
            $query = $modelName->where($condition);
            if ($designation_id == 'Verifier') {
            if($application_type==1)
               $query = $query->whereNull($Table . '.faulty_migrate');
              
              if($application_type==2)
               $query = $query->where($Table . '.faulty_migrate', 1);
              
              if($application_type==3)
               $query = $query->where($Table . '.faulty_migrate', 2);
              
              
            }

               if ($designation_id == 'Approver') {
// dd($application_type);
                if($application_type==1)
                $query = $query->where($Table . '.faulty_migrate', 1);

               if($application_type==3)
               $query = $query->where($Table . '.faulty_migrate', 2);
               }

            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');

           

            
            if (empty($serachvalue)) {
                $totalRecords =  $query->count($Table . '.application_id');
                $data = $query->orderBy('ben_fname')->offset($offset)->limit($limit)->get([

                    $Table . '.application_id as application_id',
                    $Table . '.created_by_dist_code  as created_by_dist_code',
                    $Table . '.created_by_local_body_code  as created_by_local_body_code','ben_fname','ben_lname','ben_mname',
                     
                     'father_fname', 'faulty_migrate',
                    'father_mname', 'father_lname', 'mobile_no', 'aadhar_no'
                ]);


                // $data = $query->orderBy('ben_fname')->offset($offset)->limit($limit)->toSql();

                //dd($data);

                $filterRecords = count($data);
                
            } else {

               // $serachvalue=$request->search;
                if (is_numeric($serachvalue)) {

                    if (strlen($serachvalue) === 10) {
                        // Your code here

                        

                        $query = $query->where(function ($query1) use ($serachvalue,$Table) {
                            $query1->Where($Table . '.mobile_no', $serachvalue);
                        });
                    }
                    else{
                        $query = $query->where(function ($query1) use ($serachvalue,$Table) {
                            $query1->where($Table . '.application_id', $serachvalue);
                        });

                    }
                   
                    $totalRecords =  $query->count($Table . '.application_id'); 
                    $data = $query->orderBy($Table . '.ben_fname')->offset($offset)->limit($limit)->get(
                        [
                            $Table . '.application_id as application_id',
                    $Table . '.created_by_dist_code  as created_by_dist_code',
                    $Table . '.created_by_local_body_code  as created_by_local_body_code','ben_fname','ben_lname','ben_mname',
                     
                     'father_fname', 'faulty_migrate',
                    'father_mname', 'father_lname', 'mobile_no', 'aadhar_no'
                        ]
                    );
                } else {

                   // dd(123);
                    $query = $query->where(function ($query1) use ($serachvalue,$Table) {
                        $query1->where($Table . 'ben_fname', 'ilike', $serachvalue . '%');
                    });
                    $totalRecords = $query->count('ss_ben_id');
                    $data = $query->orderBy($Table . '.ben_fname')->offset($offset)->limit($limit)->get(
                        [
                            $Table . '.application_id as application_id',
                    $Table . '.created_by_dist_code  as created_by_dist_code',
                    $Table . '.created_by_local_body_code  as created_by_local_body_code','ben_fname','ben_lname','ben_mname',
                     
                     'father_fname', 'faulty_migrate',
                    'father_mname', 'father_lname', 'mobile_no', 'aadhar_no'
                        ]
                    );
                }

                //dd($serachvalue);
                
                // else{

                //     dd(333);
                // }
                
                
                $filterRecords = count($data);

               
            }
            // if (!empty($serachvalue) && count($data)>0  ) {

            //     //dd( $data[0]->application_id);
            //   return redirect("/faulty-lb-entry-edit?&application_id=" . $data[0]->application_id);
            //    //return redirect("/")->with('error', 'Not Allowed');
            // }
           
            return datatables()->of($data)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filterRecords)
                ->skipPaging()
                ->addColumn('name', function ($data) {
                    return trim($data->ben_fname. ' ' .$data->ben_mname. ' ' .$data->ben_lname);
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
                })->addColumn('Action', function ($data) use ($designation_id) {



                    if ($designation_id == 'Verifier') {

                       
                        if($data->faulty_migrate==-57){
                         $action ='Rejected';
                        }
                        else if($data->faulty_migrate==1 ){
                          $action ='Approval Pending';
                         }
                         
                         else if(is_null($data->faulty_migrate)){
                     $action = '<a href="faulty-lb-entry-edit?application_id=' . $data->application_id . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                         }
                          else if( $data->faulty_migrate==2){
                          $action ='Approved';
                         }
                         else{
                          $action ='';
                        }
                      
                    }

                    if ($designation_id == 'Approver') {
                       
              
                        if($data->faulty_migrate==1){
                            $action = '<button class="btn btn-primary btn-sm ben_view_button" value=' . $data->application_id . '><i class="glyphicon glyphicon-edit"></i>View</button>';
                         }
                         else if($data->faulty_migrate==2){
                           $action ='Approved';
                          }
                        //   else if($data->faulty_migrate==1 ){
                        //     //dd($data->id);
                        //     $action = '<button class="btn btn-primary btn-sm ben_view_button" value=' . $data->application_id . '><i class="glyphicon glyphicon-edit"></i>View</button>';
                        //   }
                          else  {
                            $action ='';
                          }

                       }

                     return $action;
                }) ->addColumn('mobile_no', function ($data) {
                    if (!empty($data->mobile_no)) {
                        return ($data->mobile_no);
                    } else
                        return '';
                })->addColumn('check', function ($data) use ($designation_id) {
                    if ($designation_id == 'Approver') {
                      if ($data->faulty_migrate == 1) {
                        return '<input type="checkbox" name="approvalcheck[]" onClick="controlCheckBox()" value="' . $data->application_id . '">';
                      } else
                        return '';
                    } else {
                      return '';
                    }
                  })
                ->rawColumns(['Action', 'id', 'name', 'status', 'mobile_no', 'application_id','check'])
                ->make(true);
        
        }
        $errormsg = Config::get('constants.errormsg');

        

        $approveBtnvisible = 1;
        $duty_level = 'DistrictApprover';
        $levels = [
            2 => 'Rural',
            1 => 'Urban',
          ];
        //   $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
          $approval_allowded=1;
        

        return view(
            'faultyForm.viewlist',
            [   'designation_id' => $designation_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'reject_revert_reason' => $reject_revert_reason,
                'ds_phase_list' => $ds_phase_list,
                'is_rural_visible'=> $is_rural_visible,
                'is_urban'=>$is_urban,
                'urban_visible'=>$urban_visible,
                'urban_body_code'=>$urban_body_code,
                'urban_visible'=>$urban_visible,
                'munc_visible'=>$munc_visible,
                'gp_ward_visible'=>$gp_ward_visible,
                'muncList'=>$muncList,
                'gpwardList'=>$gpwardList,
                'mappingLevel'=>$mappingLevel,
                'approval_allowded'=>$approval_allowded,
                // 'dob_base_date'=>$dob_base_date,
                'district_code'=>$district_code,
                'scheme_id'=>$scheme_id
            ]
        );


    } 
    else {
        return redirect('/')->with('error', 'User not Authorized for this scheme');
    }
    }
    // public function edit(Request $request)
    // {
    // }

    public function edit(Request $request)
    {

        //dd(123);
        
        $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
        $designation_id = Auth::user()->designation_id;
        $max_dob = $this->max_dob;
        $scheme_id = $this->scheme_id;
        //dd($sws_no);
        if ($designation_id != 'Verifier') {
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
        if (isset($request->status)) {
            $status = $request->status;
        } else {
            $status = 1;
        }
        $is_active = 0;
        $roleArray = $request->session()->get('role');
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
                $Table = 'lb_scheme.faulty_ben_personal_details';
                $DraftPersonalTable->setTable('' . $Table);

                $getModelFunc = new getModelFunc();
                $personal_migrate = new DataSourceCommon;
                $Table = 'lb_scheme.faulty_ben_personal_details_migrate';
                $personal_migrate->setTable('' . $Table);

                $personalcount= $personal_migrate->where('application_id', $request->application_id)->count();



                if($personalcount<0 || $personalcount==0)
                {

                    $personal_data = $DraftPersonalTable->select(
                        'application_id',
                        'aadhar_no',
                        'duare_sarkar_registration_no',
                        'duare_sarkar_date',
                        'ben_fname',
                        'ben_mname' ,
                        'ben_lname' ,
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
                     'life_certificate_pass',
                     'caste_matched_with_certificate_no',
                     'caste_certificate_checked',
                     'entry_type',
                     'last_biometric',
                     'email',
                'life_certificate_lastdatetime','caste_certificate_check_lastdatetime',
                        // 'dob_kh','dob_is_match_kh',
                    )->where(['created_by_local_body_code' => $blockCode, 'application_id' => $request->application_id])->first();

                }
                else{

                  //dd(333);
                    $personal_data = $personal_migrate->select(
                        'application_id',
                        'duare_sarkar_registration_no',
                        'duare_sarkar_date',
                        'ben_fname',
                        'ben_mname' ,
                        'ben_lname' ,
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
                     'life_certificate_pass',
                     'caste_matched_with_certificate_no',
                     'caste_certificate_checked',
                     'aadhaar_no_checked',
                     'entry_type',
                     'tab_code',
                     'last_biometric',
                     'email',
                'life_certificate_lastdatetime','caste_certificate_check_lastdatetime','aadhaar_no_checked_lastdatetime','aadhaar_no_validation_msg','aadhaar_no_checked_pass'
                    )->where(['created_by_local_body_code' => $blockCode, 'application_id' => $request->application_id])->first();

                    //dd($personal_data);

                }

                
            

            if(!empty($personal_data->tab_code))

            {
                $ben_name=$personal_data->ben_fname;
                $tab_code = $personal_data->tab_code;
                $aadhaar_no_checked=$personal_data->aadhaar_no_checked;

                $tab=$personal_data->tab_code;

            }
            else{

                $aadhaar_no_checked=0;
                $ben_name=$personal_data->ben_fname . ' ' . $personal_data->ben_mname . ' ' . $personal_data->ben_lname;
                $tab_code =0;
                $tab=0;

                // if($tab_code_faulty==NULL)
                // {
                //     $tab_code=0;

                // }
                // else{

                //     $tab_code=$personal_data->tab_code;

                // }
            }
                
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
                //$tab_code = $personal_data->tab_code;
                //$tab_code=0;
            } else {
                $tab_code = 0;
            }
            
//dd($tab_code);

            if ($tab==0 || $tab_code >0) {

                //dd(1111);
                $DraftContactTable = new DataSourceCommon;
                $Table = 'lb_scheme'.'.faulty_ben_contact_details';
                $DraftContactTable->setConnection('pgsql_appread');
                $DraftContactTable->setTable('' . $Table);



               
                $personal_contact_migrate = new DataSourceCommon;
                $Table = 'lb_scheme'.'.faulty_ben_contact_details_migrate';
                $personal_contact_migrate->setTable('' . $Table);

                $contactcount= $personal_contact_migrate->where('application_id', $request->application_id)->count();

                
                if($contactcount<0 ||$contactcount==0 )
                {

                    //dd(1111);

                $contactData = $DraftContactTable->select('dist_code', 'block_ulb_code', 'block_ulb_name', 'gp_ward_code', 'gp_ward_name', 'police_station', 'village_town_city', 'house_premise_no', 'post_office', 'residency_period',  'pincode', 'rural_urban_id','jnmp_marked')->where('application_id', $application_id)->first();

                }
                else{

                   // dd(33);

                $contactData = $personal_contact_migrate->select('dist_code', 'block_ulb_code', 'block_ulb_name', 'gp_ward_code', 'gp_ward_name', 'police_station', 'village_town_city', 'house_premise_no', 'post_office', 'residency_period',  'pincode', 'rural_urban_id','jnmp_marked')->where('application_id', $application_id)->first();
                }


                

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
                $Table = 'lb_scheme'.'.faulty_ben_bank_details';
                $DraftBankTable->setConnection('pgsql_appread');
                $DraftBankTable->setTable('' . $Table);
                //dd($application_id);
                $bankData = $DraftBankTable->select('bank_code', 'bank_name', 'branch_name', 'bank_ifsc','is_dup')->where('application_id', $application_id)->first();
                //dd($bankData);
                if (!empty($bankData)) {
                    $bank_arr = $bankData->toArray();
                    $rowArr = array_merge($rowArr, $bank_arr);
                    $bankCount = 1;
                } else {
                    $bankCount = 0;
                }


                $DraftPfImageTable = new DataSourceCommon;
                $Table = $getModelFunc->getTableFaulty($dist_code, $this->source_type, 5, 1);
                $DraftPfImageTable->setConnection('pgsql_encread');
                $DraftPfImageTable->setTable('' . $Table);




                $DraftEncloserTable = new DataSourceCommon;
                $Table = $getModelFunc->getTableFaulty($dist_code, $this->source_type, 6, 1);
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
                // $Table = $getModelFunc->getTableFaulty($dist_code, $this->source_type, 7, 2);
                $Table = 'lb_scheme'.'.faulty_migrate_ben_declaration_details';
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
                           // dd(123);
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
            //dd($contactCount);
            $errormsg = Config::get('constants.errormsg');

            return view('faultyForm/EditForm', [
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
                'entry_type'=>1,
                'ben_name'=>$ben_name,
                'life_certificate_checked'=>1,
                'dob_base_date' => $dob_base_date,
                'dob_base_date' => $dob_base_date,
                'personalCount' => $personalCount,
                'contactCount' => $contactCount,
                'bankCount' => $bankCount,
                'otherCount' => $otherCount,
                'encolserCount' => $encolserCount,
                'status' => $status,
                'aadhaar_no_checked'=>$aadhaar_no_checked,
                'scheme_details' => $scheme_details
            ]);
        }
    }
    public function shemeSessionCheck(Request $request)
    {
        $scheme_id = 0;

        // if ($request->get('pr1')) {
        //     if ($request->get('pr1') == "lb_wcd") {
        //         $scheme_id = 20;
        //     } else {
        //         return redirect("/")->with('error', ' Parameter Invalid');
        //     }
        // } else {
        //     return redirect("/")->with('error', 'Method is not valid');
        // }
        $scheme_id = 20;
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

    public function getBenViewBankData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {

        

        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;

        $getModelFunc = new getModelFunc();
        $bank_model = new DataSourceCommon;
        $Table = 'lb_scheme.faulty_ben_bank_details';
        $bank_model->setConnection('pgsql_appread');
        $bank_model->setTable('' . $Table);
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        //dd($district_code);
        if ($designation_id == 'Verifier')
          $condition['created_by_local_body_code'] = $body_code;
        $bankdata = $bank_model->where('application_id', $benid)->where($condition)->first()->toArray();
      }
      $response = array('bankdata' => $bankdata, 'benid' => $benid);
    } catch (\Exception $e) {
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statusCode = 400;
    } finally {
      return response()->json($response, $statusCode);
    }
  
}

public function getBenViewEncloserData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];

    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    $scheme_id = $this->scheme_id;
    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Verifier')
          $condition['created_by_local_body_code'] = $body_code;
        $is_draft = 1;
        $getModelFunc = new getModelFunc();
        $DraftPfImageTable = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaulty($district_code, $this->source_type, 5,  $is_draft);
        $DraftPfImageTable->setConnection('pgsql_encread');
        $DraftPfImageTable->setTable('' . $Table);
        $DraftEncloserTable = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6,  $is_draft);
        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);
        $doc_arr = array();
        $encloserdata_arr = array();
        $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
        $profileImageCnt = $DraftPfImageTable->where('image_type', $doc_profile->id)->where('application_id', $benid)->where($condition)->count();
        if ($profileImageCnt) {
          array_push($doc_arr, $doc_profile->id);
        }
        $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $benid)->where($condition)->get()->pluck('document_type')->toArray();
        if (count($encolserdata) > 0) {
          foreach ($encolserdata as $en) {
            array_push($doc_arr, $en);
          }
        }
        if (count($doc_arr) > 0) {
          $encloserdata = DocumentType::select('id', 'doc_name', 'is_profile_pic')->whereIn('id', $doc_arr)->get();
        }
      }
      if (count($encloserdata) > 0) {
        $p = 0;
        $html = '';
        foreach ($encloserdata as $enc) {
          if ($p == 0 || ($p % 2 == 0)) {
            $html = $html . '<tr>';
          }
          $html = $html . '    
          <th scope="row">' . $enc->doc_name . '</th>
          <td  scope="row" class="encView">&nbsp;&nbsp;&nbsp;<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="View_encolser_modal(\'' . $enc->doc_name . '\',' . $enc->id . ',' . intval($enc->is_profile_pic) . ',' . $benid . ')">View</a></td>';
          if (($p % 2 == 0) && ($p % 2 != 0)) {
            $html = $html . '</tr>';
          }
          $p++;
        }
      }

      $response = array('html' => $html, 'benid' => $benid);
    } catch (\Exception $e) {
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statusCode = 400;
    } finally {
      return response()->json($response, $statusCode);
    }
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

    public function getBenViewPersonalData(Request $request)
    {//echo 1;die;
      $statusCode = 200;
      $response = [];
      if (!$request->ajax()) {
        $statusCode = 400;
        $response = array('error' => 'Error occured in form submit.');
        return response()->json($response, $statusCode);
      }
  
  
      $benid = $request->benid;
      try {
        $html = '';
        if (!empty($benid)) {
          $this->shemeSessionCheck($request);
          $district_code = $request->session()->get('distCode');
          $is_urban = $request->session()->get('is_urban');
          if ($is_urban == 1) {
            $urban_body_code = $request->session()->get('bodyCode');
            $body_code = $urban_body_code;
          } else if ($is_urban == 2) {
            $taluka_code = $request->session()->get('bodyCode');
            $body_code = $taluka_code;
          }
          $designation_id = Auth::user()->designation_id;
          $getModelFunc = new getModelFunc();
          $personal_model = new DataSourceCommon;
          $Table = 'lb_scheme.faulty_ben_personal_details_migrate';
         
          $personal_model->setConnection('pgsql_appread');
          $personal_model->setTable('' . $Table);
          $condition = array();
          $condition['created_by_dist_code'] = $district_code;
          if ($designation_id == 'Verifier' )
            $condition['created_by_local_body_code'] = $body_code;
  
          $personaldata = $personal_model->where('application_id', $benid)->where($condition)->first()->toArray();

        //   $email=$personaldata['email'];
        //   dd($email);
          // $contactdata = $contact_table->where('application_id', $benid)->first();
          // $bankdata = $bank_table->where('application_id', $benid)->first();
          // $dist_name = District::where('district_code', $contactdata->dist_code)->value('district_name');
          //print_r( $personaldata);die;
          if (!empty($personaldata['dob'])) {
            $extract_dob = Carbon::parse($personaldata['dob'])->format('d/m/Y');
            $personaldata['formatted_dob'] = $extract_dob;
            $personaldata['age_ason_01012021'] = $this->ageCalculate($personaldata['dob']);
          } else {
            $personaldata['formatted_dob'] = '';
            $personaldata['age_ason_01012021'] = '';
          }
          if (!empty($personaldata['duare_sarkar_date'])) {
            $extract_duare_sarkar_date = Carbon::parse($personaldata['duare_sarkar_date'])->format('d/m/Y');
            $personaldata['formatted_duare_sarkar_date'] = $extract_duare_sarkar_date;
          } else {
            $personaldata['formatted_duare_sarkar_date'] = '';
          }
        }
        $response = array('personaldata' => $personaldata, 'benid' => $benid);
      } catch (\Exception $e) {
        $response = array(
          'exception' => true,
          'exception_message' => $e->getMessage(),
        );
        $statusCode = 400;
      } finally {
        return response()->json($response, $statusCode);
      }
    }

    public function getBenViewContactData(Request $request)
    { //echo 1;die;
      $statusCode = 200;
      $response = [];
      if (!$request->ajax()) {
        $statusCode = 400;
        $response = array('error' => 'Error occured in form submit.');
        return response()->json($response, $statusCode);
      }
  
  
      $benid = $request->benid;
      try {
        $html = '';
        if (!empty($benid)) {
          $this->shemeSessionCheck($request);
          $district_code = $request->session()->get('distCode');
          $is_urban = $request->session()->get('is_urban');
          if ($is_urban == 1) {
            $urban_body_code = $request->session()->get('bodyCode');
            $body_code = $urban_body_code;
          } else if ($is_urban == 2) {
            $taluka_code = $request->session()->get('bodyCode');
            $body_code = $taluka_code;
          }
          $designation_id = Auth::user()->designation_id;
          $getModelFunc = new getModelFunc();
          $contact_model = new DataSourceCommon;
          $Table = 'lb_scheme.faulty_ben_contact_details_migrate';
          
          $contact_model->setConnection('pgsql_appread');
          $contact_model->setTable('' . $Table);
          $condition = array();
          $condition['created_by_dist_code'] = $district_code;
          if ($designation_id == 'Verifier' )
            $condition['created_by_local_body_code'] = $body_code;
          $contactdata = $contact_model->where('application_id', $benid)->where($condition)->first()->toArray();
          $dist_name = District::select('district_name')->where('district_code', $contactdata['dist_code'])->first();
          $contactdata['dist_name'] = $dist_name->district_name;
        }
        $response = array('contactdata' => $contactdata, 'benid' => $benid);
      } catch (\Exception $e) {
        $response = array(
          'exception' => true,
          'exception_message' => $e->getMessage(),
        );
        $statusCode = 400;
      } finally {
        return response()->json($response, $statusCode);
      }
    }


    public function getBenViewAadharData(Request $request)
    { //echo 1;die;
      $statusCode = 200;
      $response = [];
      $benid = $request->benid;
      $scheme_id = $this->scheme_id;
      if (!$request->ajax()) {
        $statusCode = 400;
        $response = array('error' => 'Error occured in form submit.');
        return response()->json($response, $statusCode);
      }
      try {
        if (!empty($benid)) {
          $this->shemeSessionCheck($request);
          $district_code = $request->session()->get('distCode');
          $is_urban = $request->session()->get('is_urban');
          if ($is_urban == 1) {
            $urban_body_code = $request->session()->get('bodyCode');
            $body_code = $urban_body_code;
          } else if ($is_urban == 2) {
            $taluka_code = $request->session()->get('bodyCode');
            $body_code = $taluka_code;
          }
          $designation_id = Auth::user()->designation_id;
  
          $condition = array();
          $condition['created_by_dist_code'] = $district_code;
          if ($designation_id == 'Verifier' )
            $condition['created_by_local_body_code'] = $body_code;
          $getModelFunc = new getModelFunc();
  
          $AadharObj = new DataSourceCommon;
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 2);
          $AadharObj->setConnection('pgsql_appread');
          $AadharObj->setTable('' . $Table);
          $aadhardata = $AadharObj->where('application_id', $benid)->where($condition)->first();
          //dd($aadhardata);
          $aadhar_no = Crypt::decryptString($aadhardata->encoded_aadhar);
        }
        $response = array('aadhar_no' => $aadhar_no);
      } catch (\Exception $e) {
        $response = array(
          'exception' => true,
          'exception_message' => $e->getMessage(),
        );
        $statusCode = 400;
      } finally {
        return response()->json($response, $statusCode);
      }
    }
    

    
  public function forwardData(Request $request)
  {
//dd($request->all());
    $this->shemeSessionCheck($request);
    $getModelFunc = new getModelFunc();
    $schemaname = $getModelFunc->getSchemaDetails();
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
    $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', $designation_id)->where('stack_level', $duty->mapping_level)->first();
    if ($role->isEmpty) {
      return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
    }

    if($request->is_bulk_all==1)
    {
        $is_bulk=1;

    }
    else{

        $is_bulk = $request->is_bulk;
    }


    
    if($request->opreation_type_all=='A')
    {

        $opreation_type='A';

    }
    else{
        $opreation_type = $request->opreation_type;

    }

    // dump($opreation_type);

    // dump($is_bulk);

    // die;

    //dd($opreation_type);
    if ($designation_id == 'Approver') {
      
      if ($is_bulk == 1) {
        $is_bulk = 1;
      } else {
        $is_bulk = 0;
        if (empty($id)) {
          return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Found']);
        }
        
      }
    } else {

        //dd(555);
      $is_bulk = 0;
      if (empty($id)) {
        return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Found']);
      }
      
    }


    // $modelName = new DataSourceCommon;
    // $table2 = 'dist_' . $district_code . '.beneficiary';
    // $modelName->setTable('' . $table2);

    $getModelFunc = new getModelFunc();
    $personal_model = new DataSourceCommon;
    $Table = 'lb_scheme.faulty_ben_personal_details';
    $personal_model->setTable('' . $Table);
    $pension_details_aadhar = new DataSourceCommon;
    $Table = $getModelFunc->getTable($district_code, $this->source_type, 2, 1);
    $pension_details_aadhar->setTable('' . $Table);
    $personal_model_revert = new DataSourceCommon;
    $Table = 'lb_scheme.faulty_ben_personal_details_migrate';
    $personal_model_revert->setTable('' . $Table);



   

    
    if ($is_bulk == 0 && empty($id)) {
      return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
    }
    $reject_cause = $request->reject_cause;
    $comments = trim($request->accept_reject_comments);


    if ($opreation_type == 'A') {
      $txt = 'Approved';
      $next_level_role_id =0;
      $rejected_cause = NULL;
      $message = 'Approved Succesfully!';
      $status=2;
      $op_type='FAULTYAPPROVE';
    }  else if ($opreation_type == 'T') {
      $txt = 'Reverted';
      $next_level_role_id = 0;
      $rejected_cause = $reject_cause;
      $message = 'Reverted Succesfully!';
      $status=NULL;
      $op_type='FAULTYREVERT';
    }
    $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'next_level_role_id' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments,'faulty_migrate'=>$status];
    $url = 'faulty-lb-draft-list';


    try {


      DB::beginTransaction();
      $applicationid_arr = array();
      $send_sms_arr = array();
      $aadhar_update_status = 1;
      if ($is_bulk == 1) {

        //dd($request->all());

        // $is_status_updated = $personal_model->whereIn('application_id', $applicant_id_in)->update($input);
        $j = 0;
        //dd($row_list->toArray());

        //$applicant_id_post = request()->input('approvalcheck');

        $applicationid_arr = array();
            $inputs = request()->input('approvalcheck');

            foreach ($inputs as $input) {
                array_push($applicationid_arr, $input);
                
              }
            
            //echo 1;
            $implode_application_arr = implode("','", $applicationid_arr);

           // dd($implode_application_arr);

        $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicationid_arr)->where('created_by_dist_code', $request->session()->get('distCode'))->get();

       //dd($row_list->toArray());
        foreach ($row_list as $app_row) {


          $accept_reject_model = new DataSourceCommon;
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
          $accept_reject_model->setTable('' . $Table);
          $accept_reject_model->op_type = $op_type;
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

        $is_status_updated_revert=1;
        $is_status_updated=1;
        $is_inserted_status=1;
        //$is_saved=1;
      } else {
        $accept_reject_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
        $accept_reject_model->setTable('' . $Table);
        if ($opreation_type == 'T') {
        $is_status_updated_revert = $personal_model_revert->where('application_id', $id)->update($input);
        $is_status_updated = $personal_model->where('application_id', $id)->update($input);
        $is_inserted_status=1;
        }
        else{
           
            $is_status_updated_revert=1;
            $is_status_updated=1;

        }
        $accept_reject_model->op_type = $op_type;
        $accept_reject_model->ben_id = $id;
        $accept_reject_model->application_id = $id;
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
        
          $aadhar_update_status = 1;
       
      }

      if ($opreation_type == 'A' ) {
        //echo 1;
        if ($is_bulk == 1) { 
            $applicationid_arr = array();
            $inputs = request()->input('approvalcheck');

                foreach ($inputs as $input) {
                array_push($applicationid_arr, $input);

                }

                $implode_application_arr = implode("','", $applicationid_arr);
                $in_pension_id = 'ARRAY[' . "'$implode_application_arr'" . ']';
                $row_list_approve = DB::table('lb_scheme.faulty_ben_personal_details_migrate' . ' AS bp')
                ->join('lb_scheme.faulty_ben_contact_details_migrate' . ' AS bc', 'bc.application_id', '=', 'bp.application_id')
                ->whereIn('bp.application_id', $applicationid_arr)->get(['bp.application_id','bc.rural_urban_id','bc.block_ulb_code','bc.gp_ward_code','bc.created_by_local_body_code','bp.mobile_no']);

                
                foreach ($row_list_approve as $app_row) {

                $update_faulty_update = [
                    'faulty_status' => false,
                   'rural_urban_id' => $app_row->rural_urban_id,
                   'block_ulb_code' => $app_row->block_ulb_code,
                   'gp_ward_code' => $app_row->gp_ward_code,
                   'mobile_no' => $app_row->mobile_no,
                   'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
                  ];

               DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
                ->where('application_id', $app_row->application_id)
                ->update($update_faulty_update);
                }

                

               
        

                $fun_return = DB::select("select lb_scheme.faulty_migrate_main(in_application_id => $in_pension_id)");

                $is_inserted_status=$fun_return[0]->faulty_migrate_main;
                //$is_inserted_status=1;
                

          
        } else { // echo 4;
          $in_pension_id = 'ARRAY[' . "'$id'" . ']';
          if ($opreation_type == 'A') { 
            
            
            
           
            $row_list_approve = DB::table('lb_scheme.faulty_ben_personal_details_migrate' . ' AS bp')
            ->join('lb_scheme.faulty_ben_contact_details_migrate' . ' AS bc', 'bc.application_id', '=', 'bp.application_id')
            ->where('bp.application_id', $id)->get(['bp.application_id','bc.rural_urban_id','bc.block_ulb_code','bc.gp_ward_code','bc.created_by_local_body_code','bp.mobile_no']);

            
            foreach ($row_list_approve as $app_row) {

            $update_faulty_update = [
                'faulty_status' => false,
               'rural_urban_id' => $app_row->rural_urban_id,
               'block_ulb_code' => $app_row->block_ulb_code,
               'gp_ward_code' => $app_row->gp_ward_code,
               'mobile_no' => $app_row->mobile_no,
               'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
              ];

           DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
            ->where('application_id', $app_row->application_id)
            ->update($update_faulty_update);
            }


                $fun_return = DB::select("select lb_scheme.faulty_migrate_main(in_application_id => $in_pension_id)");

                $is_inserted_status=$fun_return[0]->faulty_migrate_main;
               // $is_inserted_status=1;
              

          }
        }
        $aadhar_update_status = 1;
      }

      



      
     
  
    if ($is_status_updated && $remarks_status && $aadhar_update_status && $is_status_updated_revert && $is_inserted_status) {


    $return_status = 1;
    if ($is_bulk == 1) {
    $return_msg = "Applications " . $message;
    } else
    $return_msg = "Application with ID:" . $id . " " . $message;

    // dd( $return_msg);
    } else {

    $return_status = 0;
    $return_msg = $errormsg['roolback'];
    }
  
      
      DB::commit();
    } catch (\Exception $e) {
     //dd($e);
      $return_status = 0;
      $return_msg = $errormsg['roolback'];

      
      //$return_msg = $e;
      DB::rollback();
    }

    if ($is_bulk == 1 && $is_status_updated && $remarks_status && $aadhar_update_status && $is_status_updated_revert && $is_inserted_status) { 
        $url = 'faulty-lb-draft-list';
        return redirect('/' . $url)->with('succes', 'Application Approve Successfully');
    }
    else if($is_bulk == 0){
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);

    }
    else{
        $url = 'faulty-lb-draft-list';
        return redirect('/' . $url)->with('error', 'Application Approve failed');

    }
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
        return $diff;
    }
    function ageCalculate($dob)
    {
        $diff = 0;
        if ($dob != '') {
            // $diff = $this->ageCalculate($dob);
            $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
        }
        return $diff;
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
        $Table = $getModelFunc->getTableFaulty($distCode, $this->source_type, 5, 1);
        $DraftPfImageTable->setConnection('pgsql_encread');

        $DraftPfImageTable->setTable('' . $Table);
        $DraftEncloserTable = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaulty($distCode, $this->source_type, 6, 1);
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
                    $file_name = $encolserData->document_type . '_' . $encolserData->application_id;

                    header('Content-Disposition: attachment;filename="' . $file_name . '.' . $file_extension . '"');
                    header('Content-Type: ' . $mime_type);
                    echo base64_decode($resultimg);
                } else if (strtoupper($file_extension) == 'PDF') {
                    $decoded = base64_decode($encolserData->attched_document);
                    $file_name = $encolserData->document_type . '_' . $encolserData->application_id . '.pdf';
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



    public function getExcel(Request $request){

        //dd(123);
        
        $application_type=$request->application_type;
        
        $user_id = Auth::user()->id;
        $faulty_type=0;
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
        if($application_type==3)
         {

            $query = DB::table('lb_scheme.faulty_ben_personal_details_migrate' . ' AS bp')
            ->join('lb_scheme.faulty_ben_contact_details_migrate' . ' AS bc', 'bc.application_id', '=', 'bp.application_id')
            ->where('bp.created_by_dist_code', $district_code)->where('bp.next_level_role_id',0);
  
         }else{

                $query = DB::table('lb_scheme.faulty_ben_personal_details' . ' AS bp')
                ->join('lb_scheme.faulty_ben_contact_details' . ' AS bc', 'bc.application_id', '=', 'bp.application_id')
                ->where('bp.created_by_dist_code', $district_code)->where('bp.next_level_role_id',0);
           
          // dd($query);
         }
            if ($designation_id == 'Verifier') {
            //dd($created_by_local_body_code);
            $query = $query->where('bp.created_by_local_body_code', $created_by_local_body_code);
            if (!empty($application_type)) {
            // dd($application_type);
            if($application_type==1)
            $query = $query->whereNull('bp.faulty_migrate');

            if($application_type==2)
            $query = $query->where('bp.faulty_migrate', 1);

            if($application_type==3)
            $query = $query->where('bp.faulty_migrate', 2);
            }


            }
      
        if ($designation_id == 'Approver') {
          //dd($application_type);
          if ($application_type!='') {
            if($application_type==1)
             $query = $query->where('bp.faulty_migrate', 1);
            
            if($application_type==3)
             $query = $query->where('bp.faulty_migrate', 2);
    
          }
          //dd($process_type);
          
        }
        // $data = $query->orderBy('bp.beneficiary_id', 'ASC')->toSql();
        // dd($data);
        $data = $query->orderBy('bc.gp_ward_name', 'ASC')->get([
           'bp.created_by_dist_code', 'bp.dob', 'bp.application_id',
           'bp.ben_fname', 'bp.ben_lname', 'bp.ben_mname', 'bp.gender','bc.block_ulb_name','bc.gp_ward_name','bp.mobile_no',
            'bp.next_level_role_id'
        ]);

       // $data = $query->orderBy('bp.beneficiary_id', 'ASC')->toSql();

        // dd($data);
        
        $excelarr[] = array(
          'Application ID', 'Beneficiary Name', 'Mobile Number', 'DOB','Block/Municipality','GP/Ward',
      );
  
      foreach ($data as $arr) {
          $excelarr[] = array(
              'Application ID' => trim($arr->application_id),
              'Beneficiary Name' => trim($arr->ben_fname . ' ' . $arr->ben_mname . ' ' . $arr->ben_lname),
              'Mobile Number' => trim($arr->mobile_no),
              'DOB' => trim($arr->dob),
              'Block/Municipality'  => trim($arr->block_ulb_name),
              'GP/Ward' => trim($arr->gp_ward_name),           
          );
      }
      $file_name = 'Faulty Incomplete Application list'.  date('d/m/Y');
      Excel::create($file_name, function ($excel) use ($excelarr) {
          $excel->setTitle('Faulty Incomplete Application ');
          $excel->sheet('Faulty Incomplete Application ', function ($sheet) use ($excelarr) {
              $sheet->fromArray($excelarr, null, 'A1', false, false);
          });
      })->download('xlsx');
      }

      public function searchfaultyapplication(Request $request){

        $serachvalue=$request->applicant_id_mobile;
        
        //dd($serachvalue);
        $user_id = Auth::user()->id;
        $faulty_type=0;
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

        $query = DB::table('lb_scheme.faulty_ben_personal_details')
                ->where('created_by_dist_code', $district_code)->where('next_level_role_id',0);


                if ($designation_id == 'Verifier') {
                    //dd($created_by_local_body_code);
                    $query = $query->where('created_by_local_body_code', $created_by_local_body_code);
                }

                if ($designation_id == 'Approver') {
                    //dd($created_by_local_body_code);
                    $query = $query->where('faulty_migrate', 1);
                }




                if (strlen($serachvalue) == 10) {
                    
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->Where('mobile_no', $serachvalue);
                    });
                }
                else{
                   // dd(333);
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where( 'application_id', $serachvalue);
                    });

                }

                $data = $query->get(
                    [
                         'application_id','faulty_migrate'
                
                    ]
                );



                $totalRecords_main =  $query->count('application_id'); 

                if($totalRecords_main>0)
                {
                    $application_id= $data[0]->application_id;
                    $faulty_migrate=$data[0]->faulty_migrate;

                }

               
//dd($faulty_migrate);
               
                
                

                if($totalRecords_main==1 && $faulty_migrate==NULL &&  ($designation_id == 'Verifier'))
                {

                   return redirect("/faulty-lb-entry-edit?&application_id=" . $application_id);

                   //return redirect("faulty-lb-draft-list")->with('norecord', true);

                }
                else if($totalRecords_main==1 && $faulty_migrate==1 && ($designation_id == 'Verifier'))
                {
                    //return redirect("/faulty-lb-entry-edit?&application_id=" . $application_id);
                    return redirect("faulty-lb-draft-list")->with('successrecord', true);


                }
                else if($totalRecords_main==1 && $faulty_migrate==1 && ($designation_id == 'Approver'))
                {

                    //dd(123);

                    return response()->json(['return_status' =>1, 'application_id' => $application_id ]);
                    //return redirect("faulty-lb-draft-list")->with('norecord', true);

                }

                else if($totalRecords_main==0 && ($designation_id == 'Approver'))
                {

                    //dd(123);

                    return response()->json(['return_status' =>0]);
                    //return redirect("faulty-lb-draft-list")->with('norecord', true);

                }
                else{

                    //dd(444);
                    return redirect("faulty-lb-draft-list")->with('norecord', true);


                }

                

               

      }


   
}
