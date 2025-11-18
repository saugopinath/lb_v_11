<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Config;
use App\Models\District;
use App\Models\UrbanBody;
use App\Models\Assembly;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\GP;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use App\Models\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use App\Models\SchemeDocMap;
use App\Models\DocumentType;
use Illuminate\Http\Response;
use App\Models\DataSourceCommon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Models\DistrictEntryMapping;
use App\Models\BankDetails;
use App\Models\DsPhase;

class LakkhiBhandarAjaxEntry extends Controller
{
    // use SendsPasswordResetEmails;
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
        if ($phaseArr != null) {
            $ds_cur_phase = $phaseArr->phase_code;
        } else{
            $ds_cur_phase = 0;
        }
        
        $return_status = '';
        $return_msg = '';
        $max_tab_code = 1;
        $application_id = '';
        $today = date("Y-m-d");
        $rules = [
            'duare_sarkar_registration_no' => 'required_unless:ds_phase,0|string|max:20',
            'duare_sarkar_date' => 'required_unless:ds_phase,0|date|before_or_equal:' . $today,
            // 'duare_sarkar_registration_no' => 'required|string|max:20',
            // 'duare_sarkar_date' => 'required|date|before_or_equal:' . $today,
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
            $entry_allowed = DistrictEntryMapping::where('main_entry', true)->where('district_code',  $distCode)->count();
            if ($personalCount == 0 && $entry_allowed == 0) {
                //dd($entry_allowed);
                $return_status = 0;
                $return_text = 'New Entry Not Allowed';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
            }
            $modelfailedpayments = new DataSourceCommon;
            $schemaname = $getModelFunc->getSchemaDetails();
            $modelfailedpayments->setConnection('pgsql_payment');
            $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
            $row_count_mobile = $modelfailedpayments->whereIn('ben_status', array(1, -97, 0))->where('mobile_no', $request->mobile_no)->count('ben_id');
            //$row_count_mobile = 0;
            if ($row_count_mobile > 0) {
                $return_status = 0;
                $return_text = 'Duplicate Mobile No.';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
            DB::beginTransaction();
            $is_saved = 0;
            try {
                $pension_details = array();
                if ($personalCount) {
                    $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
                    // dd($session_application_id);
                    $application_id = $request->application_id;
                    if (!empty($application_id)) {
                        if ($request->add_edit_status == 4) {
                            if ($application_id != $session_application_id) {
                                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                            }
                        }
                        //dd($application_id);
                        $application_row = $pension_personal_model->select('application_id')->where('application_id', $application_id)->first();
                        //dd($application_row);
                        $application_id = $application_row->application_id;
                        $pension_details = $pension_personal_model->where('application_id', $application_row->application_id)->first();
                        $max_tab_code = $pension_details->tab_code;
                    } else {
                        $return_status = 0;
                        $return_text = 'Some error.Please try again';
                        $return_msg = array("" . $return_text);
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                } else {
                    $countCheck = $SourceChk->where('user_id', '!=', $user_id)->whereNotNull('lb_application_id')->where('ss_ben_id', $request->source_id)->where('ss_family_id', $request->sws_card_no)->count();
                    if ($countCheck > 0) {
                        $return_status = 0;
                        $return_text = 'Already Edited By Some Other User.';
                        $return_msg = array("" . $return_text);
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                    $countCheckBenId = $SourceChk->where('ss_ben_id', $request->source_id)->where('ss_family_id', $request->sws_card_no)->first();
                    if (empty($countCheckBenId)) {
                        $return_status = 0;
                        $return_text = 'Swastha Sathi Card Not Found.';
                        $return_msg = array("" . $return_text);
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                    $pension_details['ss_ben_id'] = trim($request->source_id);
                    $pension_details['scheme_id'] = $this->scheme_id;
                    $pension_details['ss_card_no'] = trim($request->sws_card_no);
                    $pension_details['tab_code'] = 1;
                    $pension_details['ss_full_name'] = trim($request->ss_full_name);
                    $pension_details['eariler_rejected'] =  intval($countCheckBenId->eariler_rejected);
                    $max_tab_code = 1;
                }
                $pension_details['duare_sarkar_registration_no'] = trim($request->duare_sarkar_registration_no);
                $pension_details['duare_sarkar_date'] = $request->duare_sarkar_date;
                $pension_details['ben_fname'] = trim($request->first_name);
                $pension_details['gender'] = trim($request->gender);
                $pension_details['dob'] = $request->dob;
                $pension_details['age_ason_01012021'] = $diff;
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
                    // dd($ds_cur_phase);
                    $pension_details['ds_phase'] = $ds_cur_phase;
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
                    try {
                        $is_saved_aadhar = $pension_details_aadhar->save();
                    } catch (\Exception $e) {
                        DB::rollback();
                        $return_status = 0;
                        $return_text = 'Duplicate Aadhaar No.';
                        $return_msg = array("" . $return_text);
                        $max_tab_code = 0;
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                    }
                    $application_id = $application_row->application_id;
                    if ($request->add_edit_status == 4) {

                        $op_type = 'F';
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
                    }
                    $request->session()->put('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id, $application_id);
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
                if ($request->add_edit_status == 4) {
                    $updated_source_lb_id = $SourceChk->where('ss_ben_id', $request->source_id)->where('ss_family_id', $request->sws_card_no)->update([
                       'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']), 'ds_phase' => $ds_cur_phase, 'status' => 1, 'user_id' => $user_id, 'lb_application_id' => $application_id, 'created_by_dist_code' => $distCode, 'created_by_local_body_code' => $blockCode
                    ]);
                }
                $return_status = 1;
                $return_msg = array("" . $return_text);
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                $max_tab_code = 0;
            }
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
        return response()->json(['return_status' => $return_status, 'application_id' => $application_id, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
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

            $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
            $application_id = $request->application_id;
            // dd($application_id);
            if (!empty($application_id)) {
                if ($request->add_edit_status == 4) {
                    if ($application_id != $session_application_id) {
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                    }
                }
                $personal_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
                $personal_model->setTable('' . $Table);
                $personal_details_arr = $personal_model->where('application_id', $application_id)->first();
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
                //dd($e);
                DB::rollback();
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
            }
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
            $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
            $application_id = $request->application_id;
            if (!empty($application_id)) {
                if ($request->add_edit_status == 4) {
                    if ($application_id != $session_application_id) {
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                    }
                }
                $personal_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
                $personal_model->setTable('' . $Table);
                $personal_details_arr = $personal_model->where('application_id', $application_id)->first();
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
            if ($bankCount) {
                $duplicate_row = DB::select("select count(1) as cnt from lb_scheme.duplicate_bank_view where application_id!=" . $application_id . " and bank_code='" . $bank_code . "'");
            } else {
                $duplicate_row = DB::select("select count(1) as cnt from lb_scheme.duplicate_bank_view where bank_code='" . $bank_code . "'");
            }
            $row_count = $duplicate_row[0]->cnt;
            if ($row_count > 0) {
                $return_status = 0;
                $return_text = 'Duplicate Bank Account Details.';
                $return_msg = array("" . $return_text);
                return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
            }
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
                    $is_saved = $pension_details_bank->where('application_id', $application_id)->update($pension_details);
                } else {
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
                $return_text = 'Some error.Please try again1';
                $return_msg = array("" . $return_text);
            }
            DB::commit();

            if ($is_saved) {
                $return_status = 1;
            } else {
                $return_status = 0;
                $return_text = 'Some error.Please try again2';
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
        $document_type = $request->document_type;
        if (empty($document_type) || !is_int($scheme_id)) {
            $return_status = 0;
            $return_text = 'Parameter Not Valid';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        $is_profile = $request->is_profile;
        if (!in_array($is_profile, array(0, 1))) {
            $return_status = 0;
            $return_text = 'Parameter Not Valid';
            $return_msg = array("" . $return_text);
            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
        }
        $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
        $application_id = $request->application_id;
        //dump($application_id);
        $getModelFunc = new getModelFunc();

        if (!empty($application_id)) {

            if ($request->add_edit_status == 4) {
                $return_status = 0;
                $return_text = 'Some error.Please try again';
                $return_msg = array("" . $return_text);
                if ($application_id != $session_application_id) {
                    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                }
            }
            $personal_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
            $personal_model->setTable('' . $Table);
            $personal_details_arr = $personal_model->where('application_id', $application_id)->first();
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
        if ($request->add_edit_status == 1 || $request->add_edit_status == 4 || $request->add_edit_status == 3 || $request->add_edit_status == 2) {
            if ($request->add_edit_status == 4 || $request->add_edit_status == 3 || $request->add_edit_status == 2) {
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
            }
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

            if ($request->add_edit_status == 4) {
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
                    //dd($in_array);
                } else
                    $in_array = array();
                if (count($in_array) > 0) {
                    $encolserdata1 = $pension_details_encloser1->select('image_type')->where('application_id', $application_id)->get()->pluck('image_type')->toArray();
                    $encolserdata2 = $pension_details_encloser2->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type')->toArray();
                    $already_uploaded = array_merge($encolserdata1, $encolserdata2);

                    if (!in_array($document_type, $already_uploaded))
                        array_push($already_uploaded, (int) $document_type);



                    if (empty(array_diff($in_array, $already_uploaded))) {
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
            }
            $pension_details = array();
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
            DB::beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();
            try {
                $crd_status_1 = 0;
                $crd_status_2 = 0;
                if (!empty($personal_details_arr) && intval($personal_details_arr->tab_code) < 4 && $max_insert_tab == 4) {
                    $input = [
                        'tab_code' =>   $max_insert_tab,
                        'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
                    ];
                    $tab_max_code_saved = $personal_model->where('application_id', $application_id)->update($input);
                    if ($tab_max_code_saved == 1) {
                        $max_tab_code = $max_tab_code + 1;
                        $crd_status_1 = 1;
                    } else {
                        $crd_status_1 = 0;
                    }
                } else {
                    $tab_max_code_saved = 1;
                    $max_tab_code = $personal_details_arr->tab_code;
                    $crd_status_1 = 1;
                }
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
                $return_status = 0;
                $return_text = 'Error. Please try again.';
                $return_msg = array("" . $return_text);
            }
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
            $session_application_id = $request->session()->get('lb_draft_app_' . $request->sws_card_no . '_' . $request->source_id);
            $application_id = $request->application_id;
            $getModelFunc = new getModelFunc();
            if (!empty($application_id)) {
                if ($request->add_edit_status == 4) {
                    if ($application_id != $session_application_id) {
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code]);
                    }
                }
                $personal_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
                $personal_model->setTable('' . $Table);
                $personal_details_arr = $personal_model->where('application_id', $application_id)->first();
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
                        'tab_code' =>  5,
                        'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
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
                //$id = $pension_details->id;
                //$application_id = $pension_details->getBenidAttribute();
                // dd($request->source_id);
                if ($request->add_edit_status == 4) {


                    $updated_source_lb_id = $SourceChk->where('ss_ben_id', $request->source_id)->where('ss_family_id', $request->sws_card_no)->update([
                        'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'status' => 2, 'user_id' => $user_id, 'lb_application_id' => $application_id, 'created_by_dist_code' => $distCode, 'created_by_local_body_code' => $blockCode
                    ]);
                    $updated_source_lb_id = $DraftPersonalTable->where('application_id', $application_id)->update([
                        'is_final' => TRUE,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
                    ]);
                    $op_type = 'E';
                }
                if ($request->add_edit_status == 3) {
                    $op_type = 'U';
                }
                if ($request->add_edit_status == 2) {
                    $op_type = 'Q';
                    $personal_next_level_role_id_update = $personal_model->where('application_id', $application_id)->update([
                        'next_level_role_id' => NULL,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
                    ]);
                }
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
                if ($request->add_edit_status == 3 || $request->add_edit_status == 2)
                    return redirect("/formEntry?scheme_slug=lb_wcd&add_edit_status=" . $request->add_edit_status . "&application_id=" . $application_id . "&tab_code=encloser")->with('error', 'Some error.Please try again ....');
                else
                    return redirect("/formEntry?scheme_slug=lb_wcd&add_edit_status=" . $request->add_edit_status . "&sws_no=" . $request->sws_card_no . "&source_id=" . $request->source_id . "&source_type=" . $request->source_type . "&tab_code=encloser")->with('error', 'Some error.Please try again .....');
            }
            DB::commit();
            $sms_send = 1;
            /*if ($distCode == 319) {
                $sms_send = 0;
            } else {
                if ($distCode == 315) {
                    $pension_details_contact = new DataSourceCommon;
                    $Table = $getModelFunc->getTable($distCode, $this->source_type, 3, 1);
                    $pension_details_contact->setTable('' . $Table);
                    $personal_contact_details_arr = $pension_details_contact->where('application_id', $application_id)->first();
                    if (in_array($personal_contact_details_arr['gp_ward_code'], array(17285, 17292, 17293, 17294, 17295, 17296, 17299, 17304))) {
                        $sms_send = 0;
                    } else {
                        $sms_send = 1;
                    }
                } else {
                    $sms_send = 1;
                }
            }*/
            if ($sms_send == 1) {
                $mobileNo = $personal_details_arr->mobile_no;
                $message = 'Your Lakshmir Bhandar application is received with application ID ' . $application_id . ' . Lakshmir Bhandar, Govt of WB';
                $this->initiateSmsActivation($mobileNo, $message);
            }
            if ($request->add_edit_status == 3) {
                return redirect("lb-application-list")->with('success', 'Application Submitted Successfully')
                    ->with('id',  $application_id);
            } else if ($request->add_edit_status == 2) {
                return redirect("reverted-list")->with('success', 'Application Submitted Successfully')
                    ->with('id',  $application_id);
            } else
                return redirect("lb-wcd-search")->with('success', 'Application Submitted Successfully')
                    ->with('id',  $application_id);
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
            if ($request->add_edit_status == 3 || $request->add_edit_status == 2)
                return redirect("/formEntry?scheme_slug=lb_wcd&add_edit_status=" . $request->add_edit_status . "&application_id=" . $request->application_id . "&tab_code=encloser")->with('errors', $return_msg);
            else
                return redirect("/formEntry?scheme_slug=lb_wcd&add_edit_status=" . $request->add_edit_status . "&sws_no=" . $request->sws_card_no . "&source_id=" . $request->source_id . "&source_type=" . $request->source_type . "&tab_code=encloser")->with('errors', $return_msg);
        }
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
    function ajaxGetEncloser(Request $request)
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
        //dd($request->toArray());
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
        $doc_type = $request->doc_type;
        $application_id = $request->application_id;
        if (empty($doc_type) || !ctype_digit($doc_type)) {
            $return_text = 'Parameter Not Valid1';
            return redirect("/")->with('error',  $return_text);
        }
        if (!in_array($is_profile_pic, array(0, 1))) {
            $return_text = 'Parameter Not Valid2';
            return redirect("/")->with('error',  $return_text);
        }
        if (empty($application_id)) {
            $return_text = 'Parameter Not Valid3';
            return redirect("/")->with('error',  $return_text);
        }
        $user_id = Auth::user()->id;
        if ($is_profile_pic == 1) {
            $profileImagedata = $DraftPfImageTable->where('image_type', $request->doc_type)->where('application_id', $request->application_id)->first();
            if (empty($profileImagedata->application_id)) {
                $return_text = 'Parameter Not Valid4';
                return redirect("/")->with('error',  $return_text);
            }
            $image_extension = $profileImagedata->image_extension;
            $mime_type = $profileImagedata->image_mimetype;
            if ($image_extension != 'png' && $image_extension != 'jpg' && $image_extension != 'jpeg') {
                if ($mime_type == 'image/png') {
                    $image_extension = 'png';
                } else if ($mime_type == 'image/jpeg') {
                    $image_extension = 'jpg';
                }
            }
            $htmlText = '<image id="image" width="50" height="100" src="data:image/' . $image_extension . ';base64, ' . $profileImagedata->profile_image . '">';
            echo $htmlText;
        } else {
            $encolserData = $DraftEncloserTable->where('document_type', $request->doc_type)->where('application_id', $request->application_id)->first();
            if (empty($encolserData->application_id)) {
                $return_text = 'Parameter Not Valid5';
                return redirect("/")->with('error',  $return_text);
            }
            $file_extension = $encolserData->document_extension;
            $mime_type = $encolserData->document_mime_type;
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
                    $htmlText = '<image id="image" width="100%" height="100%" src="data:image/' . $file_extension . ';base64, ' . $encolserData->attched_document . '">';
                    echo $htmlText;
                } else if (strtoupper($file_extension) == 'PDF') {
                    //dd($encolserData->attched_document);
                    $htmlText = '<embed type="text/html" width="100%" height="100%" src="data:application/pdf;base64, ' . $encolserData->attched_document . ' ">';


                    echo $htmlText;
                }
            } catch (\Exception $e) {
                return redirect("/")->with('error',  'Some error.please try again ......');
            }
        }
    }
}
