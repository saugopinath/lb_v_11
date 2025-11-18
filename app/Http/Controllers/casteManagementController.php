<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheme;

use App\Models\District;
use App\Models\UrbanBody;
use App\Models\DocumentType;
use App\Models\Assembly;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\GP;
use App\Models\User;
use Redirect;
use Auth;
use DateTime;
use Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use App\Helpers\Helper;
use App\Models\RejectRevertReason;
use App\Models\Configduty;
use App\Models\MapLavel;
use App\Models\DsPhase;
use App\Models\SubDistrict;
use Illuminate\Support\Facades\Crypt;
use App\Traits\TraitCasteCertificateValidate;
class casteManagementController extends Controller
{
    use TraitCasteCertificateValidate;

    public function __construct()
    {
        
        $this->base_dob_chk_date = '2021-01-01';
        $this->max_dob = '1996-01-01';
        $this->min_dob = '1961-01-01';
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }

    public function index(Request $request)
    {
        $this->middleware('auth');
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if ($designation_id != 'Operator') {
            return redirect("/")->with('error', 'Not Allowded');
        }
        $scheme_id = $this->scheme_id;
        $errormsg = Config::get('constants.errormsg');
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $fill_array = array();
        $result = array();
        $errorMsg = '';
        $valid = 1;
        $issubmitted = 0;
        $fill_array['select_type'] = 'B';
        $fill_array['search_text'] = 'Beneficiary ID';
        $fill_array['ben_id'] = '';
        if (isset($request->btnSubmit)) {
            if (!empty($request->select_type)) {
                $fill_array['select_type'] = $request->select_type;
            }
            if (!empty($request->ben_id)) {
                $fill_array['ben_id'] = $request->ben_id;
            }
            $issubmitted = 1;
            $rules = [
                'select_type' => 'required',
                'ben_id' => 'required|numeric'
            ];
            $attributes = array();
            $messages = array();
            $attributes['select_type'] = 'Search By Selection';
            $attributes['ben_id'] = 'Search By Text';
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
                $select_type = $request->select_type;
                $ben_id = $request->ben_id;
                $getModelFunc = new getModelFunc();
                $schemaname = $getModelFunc->getSchemaDetails();
                $personal_table_m = $getModelFunc->getTable($district_code, '', 1);
                $contact_table_m = $getModelFunc->getTable($district_code, '', 3);
                $bank_table_m = $getModelFunc->getTable($district_code, '', 4);
                $personal_table_f = $getModelFunc->getTablefaulty($district_code, '', 1);
                $contact_table_f = $getModelFunc->getTablefaulty($district_code, '', 3);
                $bank_table_f = $getModelFunc->getTablefaulty($district_code, '', 4);
                $condition = array();
                $condition['dist_code'] = $district_code;
                $condition['local_body_code'] = $urban_body_code;
                if ($select_type == 'B') {
                    $condition['ben_id'] = $ben_id;
                    $fill_array['search_text'] = 'Beneficiary ID';
                } else if ($select_type == 'A') {
                    $condition['application_id'] = $ben_id;
                    $fill_array['search_text'] = 'Application ID';
                } else if ($select_type == 'S') {
                    $condition['ss_card_no'] = $ben_id;
                    $fill_array['search_text'] = 'Sasthyasathi Card';
                }
                $query = "(select bp.ds_phase,md.district_name, bl_div.block_subdiv_name, bp.caste,0 as is_faulty,bp.ben_fname,bp.ben_mname, bp.ben_lname,bp.beneficiary_id, bp.mobile_no,bp.ss_card_no, bp.application_id, bp.next_level_role_id, bc.block_ulb_name,bc.gp_ward_name, bc.rural_urban_id, bb.bank_code,bb.bank_ifsc from " . $personal_table_m . " bp
                JOIN " . $contact_table_m . " bc ON bp.beneficiary_id=bc.beneficiary_id
                JOIN " . $bank_table_m . " bb ON bb.beneficiary_id=bp.beneficiary_id 
                JOIN public.m_district md ON md.district_code=bp.created_by_dist_code 
                JOIN (select block_code as block_subdiv_code,block_name as block_subdiv_name from public.m_block UNION ALL
                  select sub_district_code as block_subdiv_code, sub_district_name as block_subdiv_name from public.m_sub_district
                ) bl_div ON bl_div.block_subdiv_code=bp.created_by_local_body_code
                where bp.next_level_role_id=0 and bp.created_by_dist_code=" . $district_code . " ";
                if ($designation_id == 'Operator') {
                    $query .= " and bp.created_by_local_body_code=" . $urban_body_code;
                }
                if ($select_type == 'B') {
                    $query .= " and bp.beneficiary_id=" . $ben_id;
                } else if ($select_type == 'A') {
                    $query .= " and bp.application_id=" . $ben_id;
                } else if ($select_type == 'S') {
                    $query .= " and bp.ss_card_no='" . $ben_id . "'";
                }
                $query .= ') UNION ALL ';
                $query .= "(select bp.ds_phase,md.district_name, bl_div.block_subdiv_name, bp.caste,1 as is_faulty,bp.ben_fname,bp.ben_mname, bp.ben_lname,bp.beneficiary_id, bp.mobile_no,bp.ss_card_no, bp.application_id, bp.next_level_role_id, bc.block_ulb_name,bc.gp_ward_name,bc.rural_urban_id, bb.bank_code,bb.bank_ifsc from " . $personal_table_f . " bp
                JOIN " . $contact_table_f . " bc ON bp.beneficiary_id=bc.beneficiary_id
                JOIN " . $bank_table_f . " bb ON bb.beneficiary_id=bp.beneficiary_id 
                JOIN public.m_district md ON md.district_code=bp.created_by_dist_code 
                JOIN (select block_code as block_subdiv_code,block_name as block_subdiv_name from public.m_block UNION ALL
                  select sub_district_code as block_subdiv_code, sub_district_name as block_subdiv_name from public.m_sub_district
                ) bl_div ON bl_div.block_subdiv_code=bp.created_by_local_body_code
                where bp.next_level_role_id=0 and bp.created_by_dist_code=" . $district_code . " ";
                if ($designation_id == 'Operator') {
                    $query .= " and bp.created_by_local_body_code=" . $urban_body_code;
                }
                if ($select_type == 'B') {
                    $query .= " and bp.beneficiary_id=" . $ben_id;
                } else if ($select_type == 'A') {
                    $query .= " and bp.application_id=" . $ben_id;
                } else if ($select_type == 'S') {
                    $query .= " and bp.ss_card_no='" . $ben_id . "'";
                }
                $query .= ')';
                // dd($query);
                $data = DB::connection('pgsql_appread')->select($query);
                // dd($data);
                $return_arr = array();
                if (empty($data)) {
                    $errorMsg = 'No Record Found';
                }
                if (count($data) == 0) {
                    $errorMsg = 'No Record Found';
                }
                if (empty($errorMsg)) {
                    // $return_arr = array();
                    $i = 0;
                    $benStatus = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where($condition)->get();
                    $benStatus_caste = DB::connection('pgsql_appread')->table('lb_scheme.ben_caste_modification_track')->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $urban_body_code)->where('is_final', FALSE)->get();

                    foreach ($data as $my_row) {
                        $return_arr[$i]['is_faulty'] = $my_row->is_faulty;
                        $return_arr[$i]['beneficiary_id'] = $my_row->beneficiary_id;
                        $return_arr[$i]['ben_fname'] = $my_row->ben_fname;
                        $return_arr[$i]['ss_card_no'] = $my_row->ss_card_no;
                        $return_arr[$i]['application_id'] = $my_row->application_id;
                        $return_arr[$i]['mobile_no'] = $my_row->mobile_no;
                        $benCasteCount = $benStatus_caste->where('is_final', FALSE)->where('beneficiary_id', $my_row->beneficiary_id)->count();
                        // dd($benCasteCount);
                        if ($benCasteCount == 0) {
                            $return_arr[$i]['can_update_switch'] = 1;
                            $return_arr[$i]['can_update_edit'] = 1;
                            $return_arr[$i]['msg'] = 'NA';
                        } else {
                            $return_arr[$i]['msg'] = 'Caste info Modification is under process';
                            $return_arr[$i]['can_update_switch'] = 0;
                            $return_arr[$i]['can_update_edit'] = 0;
                        }
                        if ($my_row->caste == 'SC' || $my_row->caste == 'ST') {
                            $return_arr[$i]['can_update_edit'] = 1;
                        } else {
                            $return_arr[$i]['can_update_edit'] = 0;
                        }
                        if ($return_arr[$i]['can_update_switch'] == 1) {
                            $benPaymentObj = $benStatus->where('ben_id', $my_row->beneficiary_id)->first();
                            if (empty($benPaymentObj)) {
                                //$return_arr[$i]['can_update_switch'] = 1;
                                //$return_arr[$i]['msg'] = 'NA';
                                $return_arr[$i]['can_update_switch'] = 0;
                                $return_arr[$i]['msg'] = 'This beneficiary is under validation process';
                            } else {
                                $acc_validate = $benPaymentObj->acc_validated;
                                $ben_status = $benPaymentObj->ben_status;
                                $is_processing = $benPaymentObj->payment_process;
                                if ($is_processing == 0 &&  $ben_status == 1) {
                                    $return_arr[$i]['can_update_switch'] = 1;
                                    $return_arr[$i]['msg'] = 'NA';
                                } else {
                                    if ($ben_status != 1) {
                                        $msg = 'This beneficiary is inactive';
                                        $return_arr[$i]['msg'] = $msg;
                                        $return_arr[$i]['can_update_switch'] = 0;
                                    } else {
                                        if ($acc_validate == '1') {
                                            $msg = 'This beneficiary is under validation process.';
                                        } else {
                                            $msg = 'This beneficiary is under payment process.';
                                        }
                                        $return_arr[$i]['msg'] = $msg;
                                        $return_arr[$i]['can_update_switch'] = 0;
                                    }
                                }
                            }
                        }
                        $i++;
                    }
                }

                $result = $return_arr;
                // dd($result);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        return view(
            'casteManagement/index',
            [
                'result'        => $result,
                'fill_array'        => $fill_array,
                'errorMsg'        => $errorMsg,
                'valid'        => $valid,
                'issubmitted'        => $issubmitted,
            ]
        );
    }

    public function change(Request $request)
    {
        
        $this->middleware('auth');
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $caste_lb = Config::get('constants.caste_lb');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if ($designation_id != 'Operator') {
            return redirect("/")->with('error', 'Not Allowded');
        }
        $scheme_id = $this->scheme_id;
        $doc_id = 3;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $beneficiary_id = $request->id;
        $is_faulty = $request->is_faulty;
        $caste_change_type = $request->caste_change_type;
        //dd($is_faulty);
        if (empty($beneficiary_id)) {
            return redirect("/casteManagement")->with('error', 'Beneficiary ID Not Found');
        }
        if (!ctype_digit($beneficiary_id)) {
            return redirect("/casteManagement")->with('error', 'Beneficiary ID Not Valid');
        }
        if (!in_array($is_faulty, array(0, 1))) {
            return redirect("/casteManagement")->with('error', 'Parameter Not Valid');
        }
        if (!in_array($caste_change_type, array(2, 1))) {
            return redirect("/casteManagement")->with('error', 'Parameter Not Valid');
        }
        $getModelFunc = new getModelFunc();
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
        $TableFaultyContact = $getModelFunc->getTableFaulty($district_code, $this->source_type, 3);
        $TableEnclosermain = $getModelFunc->getTable($district_code, $this->source_type, 6);
        $TableEncloserfaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaultyPersonal);
        $contact_model = new DataSourceCommon;
        $contact_model->setTable('' . $TableContact);
        $contact_model_f = new DataSourceCommon;
        $contact_model_f->setTable('' . $TableFaultyContact);
        $encolser_model = new DataSourceCommon;
        $encolser_model->setConnection('pgsql_encread');
        $condition = array();
        //$condition['next_level_role_id'] = 0;
        $condition['created_by_dist_code'] = $district_code;
        $condition['created_by_local_body_code'] = $urban_body_code;

        if ($is_faulty == 1) {
            $query = $personal_model_f->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)->where($condition);
            $row = $query->first();
            $row_contact = $contact_model_f->where('beneficiary_id', $beneficiary_id)->where($condition)->first();
            $encolser_model->setTable('' . $TableEncloserfaulty);
        } else if ($is_faulty == 0) {
            $query = $personal_model->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)->where($condition);
            $row = $query->first();
            $row_contact = $contact_model->where('beneficiary_id', $beneficiary_id)->where($condition)->first();
            $encolser_model->setTable('' . $TableEnclosermain);
        }
        //dd($row);
        if (empty($row)) {
            return redirect("/casteManagement")->with('error', ' Application Id Not found in Db');
        }
        $row->is_faulty = $is_faulty;
        if (trim($row->caste) == 'OTHERS') {
            if ($caste_change_type == 1) {
                return redirect("/casteManagement")->with('error', 'Not Allowded');
            } else if ($caste_change_type == 2) {
                $caste_lb = array_diff($caste_lb, array('OTHERS'));
            }
        } else if (trim($row->caste) == 'SC' || trim($row->caste) == 'ST') {
            if ($caste_change_type == 1) {
                if (trim($row->caste) == 'SC') {
                    //$caste_lb = array_diff($caste_lb, array('SC'));
                    $caste_lb = array_diff($caste_lb, array('OTHERS'));
                } else if (trim($row->caste) == 'ST') {
                    $caste_lb = array_diff($caste_lb, array('OTHERS'));
                }
            } else if ($caste_change_type == 2) {
                if (trim($row->caste) == 'SC') {
                    $caste_lb = array_diff($caste_lb, array('SC', 'ST'));
                } else if (trim($row->caste) == 'ST') {
                    $caste_lb = array_diff($caste_lb, array('ST', 'SC'));
                }
            }
        }
        $doc_caste_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id)->first();
        $casteEncloserCount = $encolser_model->where('application_id', $row->application_id)->where($condition)->where('document_type', $doc_id)->count('application_id');
        return view(
            'casteManagement/change',
            [
                'beneficiary_id'        => $beneficiary_id,
                'casteEncloserCount'        => $casteEncloserCount,
                'row'        => $row,
                'row_contact'        => $row_contact,
                'doc_caste_arr'        => $doc_caste_arr,
                'caste_change_type'        => $caste_change_type,
                'caste_lb'        => $caste_lb,
            ]
        );
    }
    public function changePost(Request $request)
    {
        //  dd($request->all());
        $this->middleware('auth');
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if ($designation_id != 'Operator') {
            return redirect("/")->with('error', 'Not Allowded');
        }
        $scheme_id = $this->scheme_id;
        $doc_id = 3;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $beneficiary_id = $request->beneficiary_id;
        $is_faulty = $request->is_faulty;
        $caste_change_type = $request->caste_change_type;
        if (empty($beneficiary_id)) {
            return redirect("/casteManagement")->with('error', 'Beneficiary ID Not Found');
        }
        if (!ctype_digit($beneficiary_id)) {
            return redirect("/casteManagement")->with('error', 'Beneficiary ID Not Valid');
        }
        if (!in_array($is_faulty, array(0, 1))) {
            return redirect("/casteManagement")->with('error', 'Parameter Not Valid');
        }
        if (!in_array($caste_change_type, array(2, 1))) {
            return redirect("/casteManagement")->with('error', 'Parameter Not Valid');
        }
        $getModelFunc = new getModelFunc();
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $TableEnclosermain = $getModelFunc->getTable($district_code, $this->source_type, 6);
        $TableEncloserfaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6);
        $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
        $TableContact_f = $getModelFunc->getTableFaulty($district_code, $this->source_type, 3);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaultyPersonal);

        $contact_model = new DataSourceCommon;
        $contact_model->setTable('' . $TableContact);
        $contact_model_f = new DataSourceCommon;
        $contact_model_f->setTable('' . $TableContact_f);

        $encolser_model = new DataSourceCommon;
        $modelNameAcceptReject = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
        $modelNameAcceptReject->setTable('' . $Table);

        $modelmain = new DataSourceCommon;
        $modelmain->setTable('lb_scheme.ben_caste_modification_track');
        $modelmain->setConnection('pgsql_appwrite');
        $modelmain->setKeyName('id');
        $encloser_enquiry_model = new DataSourceCommon;
        $encloser_enquiry_model->setTable('lb_scheme.ben_attach_documents_caste_modification');
        $encloser_enquiry_model->setConnection('pgsql_encwrite');
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        $condition['created_by_local_body_code'] = $urban_body_code;
        //$condition['next_level_role_id'] = 0;
        if ($is_faulty == 1) {
            $query = $personal_model_f->where('beneficiary_id', $beneficiary_id)->where($condition)->where('next_level_role_id', 0);
            $row = $query->first();
            $query_contact = $contact_model_f->where('beneficiary_id', $beneficiary_id)->where($condition);
            $row_contact = $query_contact->first();
            $encolser_model->setTable('' . $TableEncloserfaulty);
        } else if ($is_faulty == 0) {
            $query = $personal_model->where('beneficiary_id', $beneficiary_id)->where($condition)->where('next_level_role_id', 0);
            $row = $query->first();
            $query_contact = $contact_model->where('beneficiary_id', $beneficiary_id)->where($condition);
            $row_contact = $query_contact->first();
            $encolser_model->setTable('' . $TableEnclosermain);
        }
        if (empty($row)) {
            return redirect("/casteManagement")->with('error', ' Application Id Not found in Db');
        }
        $doc_caste_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id)->first()->toArray();
        $caste_key =  array_keys(Config::get('constants.caste_lb'));
        $rules = [
            'caste_category' => 'required|in:' . implode(",", $caste_key),
            'caste_certificate_no'     => 'required_if:caste_category,SC,ST',
        ];
        $attributes = array();
        $messages = array();
        $attributes['caste_category'] = 'New Caste';
        $attributes['caste_certificate_no'] = 'New SC/ST Certificate No.';
        if ($request->caste_category == 'SC' || $request->caste_category == 'ST') {
            $required = 'required';
            $rules['doc_3'] = $required . '|mimes:' . $doc_caste_arr['doc_type'] . '|max:' . $doc_caste_arr['doc_size_kb'] . ',';
            $messages['doc_3.max'] = "The file uploaded for " . $doc_caste_arr['doc_name'] . " size must be less than " . $doc_caste_arr['doc_size_kb'] . " KB";
            $messages['doc_3.mimes'] = "The file uploaded for " . $doc_caste_arr['doc_name'] . " must be of type " . $doc_caste_arr['doc_type'];
            $messages['doc_3.required'] = "Document for " . $doc_caste_arr['doc_name'] . " must be uploaded";
        }
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if (!$validator->passes()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        } else {
            $errormsg = Config::get('constants.errormsg');
            $old_caste = trim($request->old_caste);
            $old_caste_certificate_no = trim($request->old_caste_certificate_no);
            $caste_category = trim($request->caste_category);
            $caste_certificate_no = trim($request->caste_certificate_no);
            if (($old_caste == $caste_category) && ($old_caste_certificate_no == $caste_certificate_no)) {
                $return_text = 'Caste Information remains same as previous ..please change at least one.';
                return redirect('changeCaste?id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
            }
            if (($old_caste != trim($row->caste))) {
                $return_text = $errormsg['roolback'];
                return redirect('changeCaste?id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
            }
            if ($caste_change_type == 1) {
                if (trim($row->caste) == 'OTHERS') {
                    $return_text = $errormsg['roolback'];
                    return redirect('changeCaste?id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
                }
            }

            $now = Carbon::now();
            $cur_year = $now->year;
            $cur_year_2 = substr($cur_year, -2);
            $cur_month = $now->month;
            $schemaname = $getModelFunc->getSchemaDetails();
            $modelfailedpayments = new DataSourceCommon;
            $modelfailedpayments->setConnection('pgsql_payment');
            $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
            $check_payment_exists = $modelfailedpayments->where('local_body_code', $urban_body_code)->where('dist_code', $district_code)->where('ben_status', 1)->where('ben_id', $beneficiary_id)->count('ben_id');
            if ($caste_change_type == 2) {
                $benPaymentObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('local_body_code', $urban_body_code)->where('dist_code', $district_code)->where('ben_id', $beneficiary_id)->first();
                $ds_phase = DsPhase::where('phase_code', $row->ds_phase)->first();
                if (!empty($benPaymentObj)) {
                    $acc_validate = $benPaymentObj->acc_validated;
                    $ben_status = $benPaymentObj->ben_status;
                    $is_processing = $benPaymentObj->payment_process;

                    if ($is_processing == 0 && $ben_status == 1) {
                        if($row->ds_phase == NULL){
                            // $effect_year = substr($benPaymentObj->start_yymm, 0, 2);
                            // $effect_month =  substr($benPaymentObj->start_yymm, 2, 4);
                            $lot_base_yymm = $benPaymentObj->start_yymm;

                        }else{
                            $lot_base_yymm = $ds_phase->lot_base_yymm;
                        }
                        if (!empty($benPaymentObj->apr_lot_status) && $benPaymentObj->apr_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '05';
                        }
                        if (!empty($benPaymentObj->may_lot_status) && $benPaymentObj->may_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '06';
                        }
                        if (!empty($benPaymentObj->jun_lot_status) && $benPaymentObj->jun_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '07';
                        }
                        if (!empty($benPaymentObj->jul_lot_status) && $benPaymentObj->jul_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '08';
                        }
                        if (!empty($benPaymentObj->aug_lot_status) && $benPaymentObj->aug_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '09';
                        }
                        if (!empty($benPaymentObj->sep_lot_status) && $benPaymentObj->sep_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '10';
                        }
                        if (!empty($benPaymentObj->oct_lot_status) && $benPaymentObj->oct_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '11';
                        }
                        if (!empty($benPaymentObj->nov_lot_status) && $benPaymentObj->nov_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2);
                            $effect_month =  '12';
                        }
                        if (!empty($benPaymentObj->dec_lot_status) && $benPaymentObj->dec_lot_status != 'R') {
                            $effect_year =  substr($lot_base_yymm, 0, 2) + 1;
                            $effect_month =  '01';
                        }
                        if (!empty($benPaymentObj->jan_lot_status) && $benPaymentObj->jan_lot_status != 'R') {
                            $effect_year =  $cur_year_2;
                            $effect_month =  '02';
                        }
                        if (!empty($benPaymentObj->feb_lot_status) && $benPaymentObj->feb_lot_status != 'R') {
                            $effect_year =  $cur_year_2;
                            $effect_month =  '03';
                        }
                        if (!empty($benPaymentObj->mar_lot_status) && $benPaymentObj->mar_lot_status != 'R') {
                            $effect_year =  $cur_year_2;
                            $effect_month =  '04';
                        }
                        if (in_array($cur_month, array(1, 2, 3))) {
                            if ((!empty($benPaymentObj->mar_lot_status) && $benPaymentObj->mar_lot_status != 'R') || (!empty($benPaymentObj->feb_lot_status) && $benPaymentObj->feb_lot_status != 'R') || (!empty($benPaymentObj->jan_lot_status)) && $benPaymentObj->jan_lot_status != 'R') {
                                $effect_year =  $cur_year_2;
                                $effect_month =  $effect_month;
                            } else {
                                if (!empty($effect_year)) {
                                    $effect_year =  $effect_year;
                                    //$effect_year =  $cur_year_2;

                                } else {
                                    $effect_year = substr($lot_base_yymm, 0, 2);
                                }
                                if (!empty($effect_month)) {
                                    // $effect_month = sprintf("%02d", $cur_month);
                                    $effect_month =  $effect_month;
                                } else {
                                    $effect_month = sprintf("%02d", substr($lot_base_yymm, -2));
                                }
                            }
                        }
                        
                        if (empty($effect_month)) {
                            $effect_month = sprintf("%02d", $cur_month);
                        }
                        if (empty($effect_year)) {
                            $effect_year =  $cur_year_2;
                        }
                        //  dump($effect_month);dd($effect_year);
                    } else {
                        if ($ben_status != 1) {
                            $msg = 'This beneficiary is inactive';
                        } else {
                            if ($acc_validate == '1') {
                                $msg = 'This beneficiary is under validation process(validation response pending from bank end), please try after some days.';
                            } else {
                                $msg = 'This beneficiary is under payment process, please try after some days.';
                            }
                        }
                        $return_text =  $msg;
                        return redirect('casteManagement')->with('error', $return_text);
                    }
                } else {
                    $effect_year =  $cur_year_2;
                    $effect_month = sprintf("%02d", $cur_month);
                }
            } else {
                $effect_year =  $cur_year_2;
                $effect_month = sprintf("%02d", $cur_month);
            }
            $benCasteCount = DB::connection('pgsql_appread')->table('lb_scheme.ben_caste_modification_track')->where('created_by_local_body_code', $urban_body_code)->where('created_by_dist_code', $district_code)->where('is_final', FALSE)->where('beneficiary_id', $beneficiary_id)->count();
            if ($benCasteCount > 0) {
                $return_text =  'Caste info Modification is under process';
                // dd('ok');
                return redirect('lb-caste-application-list')->with('error', $return_text);
            }
            $today = date("Y-m-d h:i:s");
            $new_value = [];
            $modelmainArch = new DataSourceCommon;
            $modelmainArch->setTable('lb_scheme.update_ben_details');
            $modelmainArch->setConnection('pgsql_appwrite');
            try {
                DB::connection('pgsql_appwrite')->beginTransaction();
                DB::connection('pgsql_encwrite')->beginTransaction();
                DB::connection('pgsql_payment')->beginTransaction();

                if ($is_faulty == 1) {
                    $modelmain->is_faulty = TRUE;
                } else
                $modelmain->is_faulty = FALSE;
                $modelmain->ip_address = request()->ip();
                $modelmain->created_by = $user_id;
                $modelmain->scheme_id = $scheme_id;
                $modelmain->ds_phase  = $row->ds_phase;
                $modelmain->created_by_level = trim($mapping_level);
                $modelmain->application_id  = $row->application_id;
                $modelmain->beneficiary_id  = $row->beneficiary_id;
                $modelmain->ben_name  = $row->ben_fname;
                $modelmain->mobile_no  = $row->mobile_no;
                $modelmain->ss_card_no  = $row->ss_card_no;
                $modelmain->next_level_role_id_caste  =  NULL;
                $modelmain->next_level_role_id  =  $row->next_level_role_id;
                $modelmain->created_by_dist_code  =  $row->created_by_dist_code;
                $modelmain->created_by_local_body_code  =  $row->created_by_local_body_code;
                $modelmain->ben_created_at  =  $row->created_at;
                $modelmain->created_at  =  $today;
                $modelmain->old_caste_certificate_no  =  $row->caste_certificate_no;
                $modelmain->new_caste_certificate_no  = $caste_certificate_no;
                $modelmain->old_caste  =  $row->caste;
                $modelmain->new_caste  = $caste_category;
                $modelmain->caste_change_type  = $caste_change_type;
                $modelmain->is_final  = FALSE;
                if (!empty($row_contact)) {
                    $modelmain->rural_urban_id  =  $row_contact->rural_urban_id;
                    $modelmain->block_ulb_code  =  $row_contact->block_ulb_code;
                    $modelmain->block_ulb_name  =  $row_contact->block_ulb_name;
                    $modelmain->gp_ward_code  =  $row_contact->gp_ward_code;
                    $modelmain->gp_ward_name  =  $row_contact->gp_ward_name;
                }
                $modelmain->effective_yymm  =  $effect_year . $effect_month;
                $save_2 = $modelmain->save();
                $op_type = 'CU';
                $modelNameAcceptReject->op_type =  $op_type;
                $modelNameAcceptReject->application_id = $row->application_id;
                $modelNameAcceptReject->designation_id = $designation_id;
                $modelNameAcceptReject->scheme_id = $scheme_id;
                $modelNameAcceptReject->mapping_level = $mapping_level;
                $modelNameAcceptReject->created_by = $user_id;
                $modelNameAcceptReject->created_by_level = trim($mapping_level);
                $modelNameAcceptReject->created_by_dist_code = $district_code;
                $modelNameAcceptReject->created_by_local_body_code = $urban_body_code;
                $modelNameAcceptReject->ip_address = request()->ip();
                $modelNameAcceptReject->ip_address = request()->ip();
                $save_1 = $modelNameAcceptReject->save();
                if ($request->hasFile('doc_' . $doc_id)) {
                    $image_file = $request->file('doc_' . $doc_id);
                    $img_data = file_get_contents($image_file);
                    $extension = $image_file->getClientOriginalExtension();
                    $mime_type = $image_file->getMimeType();
                    //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                    $base64 = base64_encode($img_data);
                    $encolser_enquiry = array();
                    $encolser_enquiry['created_by_level'] = $mapping_level;
                    $encolser_enquiry['created_by'] = $user_id;
                    $encolser_enquiry['ip_address'] = $request->ip();
                    $encolser_enquiry['created_by_dist_code'] = $row->created_by_dist_code;
                    $encolser_enquiry['created_by_local_body_code'] = $row->created_by_local_body_code;
                    $encolser_enquiry['document_type'] = $doc_id;
                    $encolser_enquiry['attched_document'] = $base64;
                    $encolser_enquiry['document_extension'] = $extension;
                    $encolser_enquiry['document_mime_type'] = $mime_type;
                    $encolser_enquiry['application_id'] = $row->application_id;
                    $encolser_enquiry['action_by'] = Auth::user()->id;
                    $encolser_enquiry['action_ip_address'] = request()->ip();
                    $encolser_enquiry['action_type'] = class_basename(request()->route()->getAction()['controller']);                    
                    $encolser_entry_status = $encloser_enquiry_model->insert($encolser_enquiry);
                } else {
                    $encolser_entry_status = 1;
                }
                if ($caste_change_type == 2) {
                    if ($check_payment_exists) {
                        $payments_arr = array();
                        $payments_arr['ben_status']    = -102;
                        $is_saved_bank_payment = $modelfailedpayments->where('local_body_code', $urban_body_code)->where('dist_code', $district_code)->where('ben_status', 1)->where('ben_id', $beneficiary_id)->update($payments_arr);
                    } else {
                        $is_saved_bank_payment = 1;
                    }
                } else {
                    $is_saved_bank_payment = 1;
                }

                $input = ['is_caste_changed' => 1,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])];
                if ($caste_change_type == 2) {
                    if ($is_faulty == 1) {
                        $is_status_updated = $personal_model_f->where($condition)->where('beneficiary_id', $beneficiary_id)->update($input);
                    } else {
                        $is_status_updated = $personal_model->where($condition)->where('beneficiary_id', $beneficiary_id)->update($input);
                    }
                } else
                $is_status_updated = 1;
                $modelmainArch->update_code  = 501;
                $modelmainArch->application_id  = $row->application_id;
                $modelmainArch->beneficiary_id  = $row->beneficiary_id;
                $modelmainArch->dist_code  =  $row->created_by_dist_code;
                $modelmainArch->local_body_code  =  $row->created_by_local_body_code;
                $modelmainArch->rural_urban_id  =  $row_contact->rural_urban_id;
                $modelmainArch->block_ulb_code  =  $row_contact->block_ulb_code;
                $modelmainArch->gp_ward_code  =  $row_contact->gp_ward_code;
                $modelmainArch->created_at  =  $today;
                $modelmainArch->user_id  =  $user_id;
                $modelmainArch->ticket_id  = $modelmain->id;
                $modelmainArch->ip_address  = request()->ip();
                $modelmainArchStatus = $modelmainArch->save();
                if ($save_1 && $modelmain->id &&  $encolser_entry_status &&  $is_saved_bank_payment && $is_status_updated && $modelmainArchStatus) {
                    DB::connection('pgsql_appwrite')->commit();
                    DB::connection('pgsql_encwrite')->commit();
                    DB::connection('pgsql_payment')->commit();
                    $return_text = "Beneficiary Caste Modified informations successfully send to higher level for verfication and approval";
                    return redirect('/lb-caste-application-list')->with('success', $return_text)->with('id', $beneficiary_id);
                    // return redirect('lb-caste-application-list')->with('error', $return_text);
                    //return redirect("/dedupBankView?last_ifsc=" . $old_bank_ifsc . "&last_accno=" . $old_bank_code)->with('success', $return_text);
                } else {
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect('changeCaste?id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
                }
            } catch (\Exception $e) {
                // dd($e);
                DB::connection('pgsql_appwrite')->rollBack();
                DB::connection('pgsql_encwrite')->rollBack();
                DB::connection('pgsql_payment')->rollBack();
                //dd($e);
                $return_text = $errormsg['roolback'];
                return redirect('changeCaste?id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
            }
        }
    }

    public function applicationStatusList(Request $request)
    {
        $this->middleware('auth');
        //$ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $is_rural_visible = 0;
        $urban_visible = 0;
        $munc_visible = 0;
        $gp_ward_visible = 0;
        $muncList = collect([]);
        $gpwardList = collect([]);
        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $caste = $request->caste;
        $block_ulb_code = $request->block_ulb_code;
        $gp_ward_code = $request->gp_ward_code;
        $report_type = $request->report_type;
        $download_excel = 1;
        $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
        if ($designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_urban = $request->rural_urbanid;
            $district_code = $district_code;
            $urban_body_code = $request->urban_body_code;
            $block_ulb_code = $request->block_ulb_code;
            $is_rural_visible = 1;
            $urban_visible = 1;
            $munc_visible = 1;
            $gp_ward_visible = 1;
        } else if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Operator') {
            $district_code = $district_code;
            if ($mapping_level == 'Block') {
                $block_ulb_code = NULL;
                $is_rural_visible = 0;
                $is_urban = 2;
                $munc_visible = 0;
                $urban_body_code = $urban_body_code;
                $block_ulb_code = NULL;
                $gpwardList = GP::where('block_code', $urban_body_code)->get();
                $gp_ward_visible = 1;
            } else if ($mapping_level == 'Subdiv') {
                $block_ulb_code = $request->block_ulb_code;
                $urban_body_code = $urban_body_code;
                $is_rural_visible = 0;
                $is_urban = 1;
                $munc_visible = 1;
                $gp_ward_visible = 1;
                $muncList = UrbanBody::where('sub_district_code', $urban_body_code)->get();
                $block_ulb_code = $request->block_ulb_code;
            }
        }
        $condition = array();
        if (!empty($request->ds_phase)) {
            $condition["ds_phase"] = $request->ds_phase;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' ||  $designation_id == 'Operator') {
            $condition["created_by_local_body_code"] = $urban_body_code;
        }


        $report_type_name = 'Beneficiary List';
        //$contact_table = $getModelFunc->getTableFaulty($district_code, '', 3, 1);
        $query = DB::connection('pgsql_appread')->table('lb_scheme.ben_caste_modification_track');

        if (!empty($district_code)) {
            $condition["created_by_dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
            // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
            if ($is_urban == 2) {
                if (!empty($urban_body_code)) {
                    //$condition["rural_urban_id"] = 2;
                    $condition["created_by_local_body_code"] = $urban_body_code;
                }
            }
            //'Urban'
            if ($is_urban == 1) {
                if (!empty($urban_body_code)) {
                    //$condition["rural_urban_id"] = 1;
                    $condition["created_by_local_body_code"] = $urban_body_code;
                }
                if (!empty($block_ulb_code)) {
                    $condition["block_ulb_code"] = $block_ulb_code;
                }
            }
        }
        if (!empty($gp_ward_code)) {
            $condition["gp_ward_code"] = $gp_ward_code;
        }
        if (!empty($caste)) {
            $condition["new_caste"] = $caste;
        }
        if (request()->ajax()) {
            $query = $query->where($condition);
            $serachvalue = $request->search['value'];
            $limit = $request->input('length');
            $offset = $request->input('start');

            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();
            if (empty($serachvalue)) {
                $totalRecords = $query->count();
                // dd($query);
                $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get([
                    'application_id', 'beneficiary_id', 'ben_name',  'mobile_no', 'ss_card_no', 'next_level_role_id_caste', 'rejected_cause', 'is_final'

                ]);
            } else {
                if (preg_match('/^[0-9]*$/', $serachvalue)) {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        if (strlen($serachvalue) < 10) {
                            $query1->where('application_id', $serachvalue)->orWhere('beneficiary_id', $serachvalue);
                        } else if (strlen($serachvalue) == 10) {
                            $query1->where('mobile_no', $serachvalue);
                        } else if (strlen($serachvalue) == 17) {
                            $query1->where('ss_card_no', $serachvalue);
                        }
                    });
                    $totalRecords = $query->count();
                    $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get(
                        [
                            'application_id', 'beneficiary_id', 'ben_name', 'mobile_no', 'ss_card_no', 'next_level_role_id_caste', 'rejected_cause', 'is_final'

                        ]
                    );
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('ben_name', 'like', $serachvalue . '%');
                    });
                    $totalRecords = $query->count();
                    $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get(
                        [

                            'application_id', 'beneficiary_id', 'ben_name', 'mobile_no', 'ss_card_no', 'next_level_role_id_caste', 'rejected_cause', 'is_final'

                        ]
                    );
                }
                $filterRecords = count($data);
            }
            return datatables()
                ->of($data)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filterRecords)
                ->skipPaging()
                ->addColumn('application_id', function ($data) use ($report_type) {

                    return $data->application_id;
                })->addColumn('beneficiary_id', function ($data) use ($report_type) {

                    return $data->beneficiary_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->ben_name;
                })->addColumn('ss_card_no', function ($data) {
                    return $data->ss_card_no;
                })->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                })->addColumn('applicant_mobile_no', function ($data) use ($report_type) {
                    return $data->mobile_no;
                })->addColumn('status', function ($data) use ($report_type, $rejection_cause_list) {
                    if (is_int($data->next_level_role_id_caste) && trim($data->next_level_role_id_caste) == 0 && $data->is_final == TRUE) {
                        $status = 'Caste Modification has been approved';
                    } else if (trim($data->next_level_role_id_caste) > 0 && $data->is_final == FALSE) {
                        $status = 'Caste Modification has been verified..but yet to be approved';
                    } else if ($data->next_level_role_id_caste == -100 && $data->is_final == TRUE) {
                        $description = '';
                        foreach ($rejection_cause_list as $rejArr) {
                            if ($rejArr['id'] == $data->rejected_cause) {
                                $description = $rejArr['reason'];
                                break;
                            }
                        }
                        $status = 'Caste Modification has been rejected for the reason ' . $description;
                    } else if ($data->next_level_role_id_caste == -50 && $data->is_final == FALSE) {
                        $description = '';
                        foreach ($rejection_cause_list as $rejArr) {
                            if ($rejArr['id'] == $data->rejected_cause) {
                                $description = $rejArr['reason'];
                                break;
                            }
                        }
                        $status = 'Caste Modification has been reverted for the reason ' . $description;
                    } else {
                        $status = 'Caste Modification  yet to be verified and approved.';
                    }
                    return $status;
                })
                ->make(true);
        } else {
            $errormsg = Config::get('constants.errormsg');
            $report_type_name = 'Caste Modification Report';
            return view(
                'casteManagement/report',
                [
                    'district_code'        => $district_code,
                    'is_rural_visible'        => $is_rural_visible,
                    'is_urban'        => $is_urban,
                    'urban_visible'        => $urban_visible,
                    'urban_body_code'        => $urban_body_code,
                    'munc_visible'        => $munc_visible,
                    'gp_ward_visible'        => $gp_ward_visible,
                    'muncList'        => $muncList,
                    'gpwardList'        => $gpwardList,
                    'mappingLevel'        => $mapping_level,
                    'ds_phase_list'        => $ds_phase_list,
                    'sessiontimeoutmessage'        => $errormsg['sessiontimeOut'],
                    'download_excel'        => $download_excel,
                    'report_type_name'        => $report_type_name
                ]
            );
        }
    }
    public function generate_excel(Request $request)
    {
        $this->middleware('auth');

        $designation_id = Auth::user()->designation_id;
        $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
        $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                $district_code = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        $scheme_name_row = Scheme::where('id', $scheme_id)->first();
        $scheme_name = $scheme_name_row->scheme_name;
        $report_type_name = 'Caste_Modification';
        $query = DB::connection('pgsql_appread')->table('lb_scheme.ben_caste_modification_track');
        $query = $query->where('created_by_dist_code', $district_code)->orderBy('ben_name')->orderBy('gp_ward_name');
        if ($designation_id == 'Operator' || $designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $query = $query->where('created_by_local_body_code', $urban_body_code);
        }
        $data = $query->get();
        $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . ".xls";
        header("Content-Type: application/xls");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<table border="1">';
        echo '<tr><th>Applicant Id</th><th>Beneficiary Id</th><th>Beneficiary Name</th><th>Beneficiary Mobile No.</th><th>Old Caste</th><th>New Caste</th><th>Old SC/ST Certificate No.</th><th>New SC/ST Certificate No.</th><th>Swasthyasathi Card No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Status</th></tr>';
        if (count($data) > 0) {
            foreach ($data as $row) {
                $sws_card_no = (string) $row->ss_card_no;
                if (!empty($sws_card_no))
                    $ss_card_no = "'$sws_card_no'";
                else
                    $ss_card_no = $sws_card_no;
                if (is_int($row->next_level_role_id_caste) && trim($row->next_level_role_id_caste) == 0 && $row->is_final == TRUE) {
                    $status = 'Caste Modification has been approved';
                } else if ($row->next_level_role_id_caste > 0 && $row->is_final == FALSE) {
                    $status = 'Caste Modification has been verified..but yet to be approved';
                } else if ($row->next_level_role_id_caste == -100 && $row->is_final == TRUE) {
                    $description = '';
                    foreach ($rejection_cause_list as $rejArr) {
                        if ($rejArr['id'] == $row->rejected_cause) {
                            $description = $rejArr['reason'];
                            break;
                        }
                    }
                    $status = 'Caste Modification has been rejected for the reason ' . $description;
                } else if ($row->next_level_role_id_caste == -50 && $row->is_final == FALSE) {
                    $description = '';
                    foreach ($rejection_cause_list as $rejArr) {
                        if ($rejArr['id'] == $row->rejected_cause) {
                            $description = $rejArr['reason'];
                            break;
                        }
                    }
                    $status = 'Caste Modification has been reverted for the reason ' . $description;
                } else {
                    $status = 'Caste Modification yet to be verified and approved.';
                }
                echo "<tr><td>" . $row->application_id . "</td><td>" . $row->beneficiary_id . "</td><td>" . trim($row->ben_name) . "</td><td>" . $row->mobile_no . "</td><td>" . $row->old_caste . "</td><td>" . $row->new_caste . "</td><td>" . $row->old_caste_certificate_no . "</td><td>" . $row->new_caste_certificate_no . "</td><td>" . $ss_card_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . $status . "</td></tr>";
            }
        } else {
            echo '<tr><td colspan="13">No Records found</td></tr>';
        }
        echo '</table>';
    }
    public function workflow(Request $request)
    {
        $this->middleware('auth');
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        //$ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                $role_id = $roleObj['id'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        $doc_id = 3;
        $doc_caste_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id)->first();
        $rows = collect([]);
        $errormsg = Config::get('constants.errormsg');
        $condition = array();
        $condition["created_by_dist_code"] = $district_code;
        $condition["is_final"] = FALSE;
        $query = DB::connection('pgsql_appread')->table('lb_scheme.ben_caste_modification_track')->where($condition);
        if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
            //  dd('ok');
            $query = $query->where('next_level_role_id_caste', $role_id);
            if (!empty($request->rural_urban_id)) {
                // $query = $query->where('rural_urban_id', $request->filter_1);
                $query = $query->where('rural_urban_id', $request->rural_urban_id);
            }
            if (!empty($request->created_by_local_body_code)) {

                $query = $query->where('created_by_local_body_code', $request->created_by_local_body_code);
            }
            if (!empty($request->block_ulb_code)) {

                $query = $query->where('block_ulb_code', $request->block_ulb_code);
            }
            if (!empty($request->gp_ward_code)) {

                $query = $query->where('gp_ward_code', $request->gp_ward_code);
            }
            if (!empty($request->caste_category)) {

                $query = $query->where('new_caste', $request->caste_category);
            }
        } else if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $query = $query->whereNull('next_level_role_id_caste');
            $query = $query->where('created_by_local_body_code', $urban_body_code);
            if ($mapping_level == 'Subdiv') {
                if (!empty($request->block_ulb_code)) {
                    $query = $query->where('block_ulb_code', $request->block_ulb_code);
                }
                if (!empty($request->gp_ward_code)) {
                    $query = $query->where('gp_ward_code', $request->gp_ward_code);
                }
                if (!empty($request->caste_category)) {

                    $query = $query->where('new_caste', $request->caste_category);
                }
            } else {
                if (!empty($request->caste_category)) {

                    $query = $query->where('new_caste', $request->caste_category);
                }
                if (!empty($request->gp_code)) {

                    $query = $query->where('gp_ward_code', $request->gp_code);
                }
            }
        }

        if (request()->ajax()) {
            $serachvalue = $request->search['value'];
            $limit = $request->input('length');
            $offset = $request->input('start');

            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();
            if (empty($serachvalue)) {
                $totalRecords = $query->count();
                // dd($query);
                $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get();
            } else {
                if (preg_match('/^[0-9]*$/', $serachvalue)) {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        if (strlen($serachvalue) < 10) {
                            $query1->where('application_id', $serachvalue)->orWhere('beneficiary_id', $serachvalue);
                        } else if (strlen($serachvalue) == 10) {
                            $query1->where('mobile_no', $serachvalue);
                        } else if (strlen($serachvalue) == 17) {
                            $query1->where('ss_card_no', $serachvalue);
                        }
                    });
                    $totalRecords = $query->count();
                    $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get();
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('ben_name', 'like', $serachvalue . '%');
                    });
                    $totalRecords = $query->count();
                    $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get();
                }
                $filterRecords = count($data);
            }
            return datatables()
                ->of($data)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filterRecords)
                ->skipPaging()
                ->addColumn('application_id', function ($data) {

                    return $data->application_id;
                })->addColumn('beneficiary_id', function ($data) {

                    return $data->beneficiary_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->ben_name;
                })->addColumn('ss_card_no', function ($data) {
                    return $data->ss_card_no;
                })->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                })->addColumn('applicant_mobile_no', function ($data) {
                    return $data->mobile_no;
                })->addColumn('check', function ($data) {

                    return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->application_id . '">';
                })->addColumn('view', function ($data) {

                    $action = '<button class="btn btn-primary btn-sm ben_view_button" value="' . $data->application_id . '_' . intval($data->is_faulty) . '"><i class="glyphicon glyphicon-edit"></i>View</button>';

                    return $action;
                })->rawColumns(['view', 'check'])->make(true);
        }

        if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
            $levels = [
                2 => 'Rural',
                1 => 'Urban',
            ];
            return view(
                'casteManagement/processApplication/linelisting_approved',
                [
                    'dist_code' => $district_code,
                    'rows' => $rows,
                    'reject_revert_reason' => $reject_revert_reason,
                    'levels' => $levels,
                    'reject_revert_reason' => $reject_revert_reason,
                    'doc_caste_arr'        => $doc_caste_arr,
                    'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
                ]
            );
        } else if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            if ($mapping_level == 'Subdiv') {
                $urban_bodys = UrbanBody::where('sub_district_code', $urban_body_code)->select('urban_body_code', 'urban_body_name')->get();


                return view(
                    'casteManagement/processApplication/linelisting_verified_subdiv',
                    [
                        'rows' => $rows,
                        'reject_revert_reason' => $reject_revert_reason,
                        'doc_caste_arr'        => $doc_caste_arr,
                        'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                        'urban_bodys' => $urban_bodys,
                        'rows' => $rows,
                    ]
                );
            } else {
                $gps = GP::where('block_code', $urban_body_code)->select('gram_panchyat_code', 'gram_panchyat_name')->get();

                return view(
                    'casteManagement/processApplication/linelisting_verified',
                    [
                        'rows' => $rows,
                        'reject_revert_reason' => $reject_revert_reason,
                        'district_code' => $district_code,
                        'gps' => $gps,
                        'doc_caste_arr'        => $doc_caste_arr,
                        'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
                    ]
                );
            }
        }
    }
    public function getCastedata(Request $request)
    { //echo 1;die;
        $this->middleware('auth');
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statusCode);
        }
        $application_id = $request->application_id;
        try {
            $scheme_id = $this->scheme_id;
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
                $statusCode = 400;
                $response = array('error' => 'Not Allowded');
                return response()->json($response, $statusCode);
            }

            if (!empty($application_id)) {
                $Table = 'lb_scheme.ben_caste_modification_track';
                $model = new DataSourceCommon;
                $model->setTable('' . $Table);
                $model->setConnection('pgsql_appread');

                $designation_id = Auth::user()->designation_id;
                $condition = array();
                $condition['created_by_dist_code'] = $distCode;
                $condition['is_final'] = FALSE;
                if ($designation_id == 'Verifier' || $designation_id == 'Operator' || $designation_id == 'Delegated Verifier')
                    $condition['created_by_local_body_code'] = $blockCode;
                $personaldata = $model->where('application_id', $application_id)->where($condition)->first()->toArray();
                //dd();
            }
            $response = array('data' => $personaldata);
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
    function ajaxModifiedCasteEncolser(Request $request)
    {
        $this->middleware('auth');
        //dd('ok');
        $scheme_id = $this->scheme_id;
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
        $is_faulty = $request->is_faulty;
        if (!in_array($is_faulty, array(0, 1))) {
            $return_text = 'Parameter Not Valid';
            return redirect("/")->with('error',  $return_text);
        }
        $getModelFunc = new getModelFunc();
        $model = new DataSourceCommon;
        $Table = 'lb_scheme.ben_attach_documents_caste_modification';
        $model->setConnection('pgsql_encread');
        $model->setTable('' . $Table);
        $is_profile_pic = 0;
        $doc_type = $request->doc_type;
        $application_id = $request->application_id;
        if (empty($doc_type) || !ctype_digit($doc_type)) {
            $return_text = 'Parameter Not Valid';
            return redirect("/")->with('error',  $return_text);
        }

        if (empty($application_id)) {
            $return_text = 'Parameter Not Valid';
            return redirect("/")->with('error',  $return_text);
        }
        $user_id = Auth::user()->id;

        $encolserData = $model->where('document_type', $request->doc_type)->where('application_id', $request->application_id)->first();
        if (empty($encolserData->application_id)) {
            $return_text = 'Parameter Not Valid';
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
            //dd($e);
            return redirect("/")->with('error',  'Some error.please try again ......');
        }
    }
    public function verifydata(Request $request)
    {
        $this->middleware('auth');
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $scheme_id = $this->scheme_id;
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $id = $request->id;
        $Verified = "Verified";
        $Rejected = 1;
        $comments = $request->comments;
        $errormsg = Config::get('constants.errormsg');
        $duty = Configduty::where('user_id', '=', $user_id)->where('scheme_id', $scheme_id)->first();
        $district_code = $duty->district_code;
        if ($duty['is_urban'] == 1) {
            $urban_body_code = $duty['urban_body_code'];
        } else {
            $urban_body_code = $duty['taluka_code'];
        }
        if ($duty->isEmpty) {
            return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
        }
        if ($designation_id == 'Delegated Approver') {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Approver')->where('stack_level', $duty->mapping_level)->first();
        } else if ($designation_id == 'Delegated Verifier') {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Verifier')->where('stack_level', $duty->mapping_level)->first();
        } else {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', $designation_id)->where('stack_level', $duty->mapping_level)->first();
        }
        
        if ($role->isEmpty) {
            return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
        }
        if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
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




        $model = new DataSourceCommon;
        $TableCaste = 'lb_scheme.ben_caste_modification_track';
        $TableCasteEnc = 'lb_scheme.ben_attach_documents_caste_modification';
        $model->setTable('' . $TableCaste);
        $model->setConnection('pgsql_appwrite');
        $opreation_type = $request->opreation_type;
        if ($is_bulk == 1) {
        }
        //dd($id);
        if ($opreation_type == 'V') {
            if ($is_bulk == 0) {
                $row = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'application_id', 'caste_change_type', 'new_caste', 'old_caste', 'is_faulty')->where('application_id', $id)->where('is_final', FALSE)->whereNull('next_level_role_id_caste')->where('created_by_dist_code', $district_code)->first();
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);

                $row_list = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'application_id', 'caste_change_type', 'new_caste', 'old_caste', 'is_faulty')->where('is_final', FALSE)->whereIn('application_id', $applicant_id_in)->whereNull('next_level_role_id_caste')->where('created_by_dist_code', $district_code)->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
            }
        } else if ($opreation_type == 'R') {
            if ($is_bulk == 0) {
                $row = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'application_id', 'caste_change_type', 'new_caste', 'old_caste', 'is_faulty')->where('is_final', FALSE)->where('application_id', $id)->where('created_by_dist_code', $district_code)->first();
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);

                $row_list = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'application_id', 'caste_change_type', 'new_caste', 'old_caste', 'is_faulty')->where('is_final', FALSE)->whereIn('application_id', $applicant_id_in)->where('created_by_dist_code', $district_code)->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
            }
        } else if ($opreation_type == 'T') {
            if ($is_bulk == 0) {
                $row = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'application_id', 'caste_change_type', 'new_caste', 'old_caste', 'is_faulty')->where('is_final', FALSE)->where('application_id', $id)->where('created_by_dist_code', $district_code)->first();
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);

                $row_list = $model->select('application_id')->where('is_final', FALSE)->whereIn('application_id', $applicant_id_in)->where('created_by_dist_code', $district_code)->get();
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
        $today = date("Y-m-d h:i:s");
        if ($opreation_type == 'V') {
            $txt = 'Verified';
            $next_level_role_id = $role->parent_id;
            $rejected_cause = NULL;
            $message = 'Verified Succesfully!';
            $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'next_level_role_id_caste' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments, 'ben_verified_at' => $today];
        } else if ($opreation_type == 'R') {
            $txt = 'Rejected';
            $next_level_role_id = -100;
            $rejected_cause = $reject_cause;
            $message = 'Rejected Succesfully!';
            $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'is_final' => TRUE, 'next_level_role_id_caste' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments, 'ben_rejected_at' => $today];
        } else if ($opreation_type == 'T') {
            $txt = 'Reverted';
            $next_level_role_id = -50;
            $rejected_cause = $reject_cause;
            $message = 'Reverted Succesfully!';
            $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'next_level_role_id_caste' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments];
        }
        //  $input = ['next_level_role_id_caste' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments];

        if ($opreation_type == 'R') {
            $payment_update_status_arr = array();
            $encloser_arch_caste_status_arr = array();
            $ben_master_status_arr = array();
            $ben_master_status_faulty_arr = array();
            if ($row->new_caste == 'SC' || $row->new_caste == 'ST') {
                array_push($encloser_arch_caste_status_arr, $row->application_id);
            }
            if ($row->caste_change_type == 2) {
                if ($row->is_faulty) {
                    array_push($ben_master_status_faulty_arr, $row->application_id);
                } else {
                    array_push($ben_master_status_arr, $row->application_id);
                }
                array_push($payment_update_status_arr, $row->application_id);
            }
        }

        try {
            $doc_id = 3;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $TablePersonalMain = $getModelFunc->getTable($district_code, $this->source_type, 1);
            $TablePersonalFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();

            $accept_reject_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
            $accept_reject_model->setTable('' . $Table);
            $accept_reject_model->setConnection('pgsql_appwrite');
            $is_status_updated = $model->where('application_id', $id)->where('is_final', FALSE)->update($input);
            $accept_reject_model->op_type = 'C' . $opreation_type;
            $accept_reject_model->ben_id = $id;
            $accept_reject_model->application_id = $row->application_id;
            $accept_reject_model->designation_id = $designation_id;
            $accept_reject_model->scheme_id = $scheme_id;
            $accept_reject_model->user_id = $user_id;
            $accept_reject_model->comment_message = $comments;
            $accept_reject_model->mapping_level = $mapping_level;
            $accept_reject_model->created_by = $user_id;
            $accept_reject_model->created_by_level = $mapping_level;
            $accept_reject_model->created_by_dist_code = $district_code;
            $accept_reject_model->created_by_local_body_code = $urban_body_code;
            $accept_reject_model->rejected_reverted_cause = $rejected_cause;
            $accept_reject_model->ip_address = request()->ip();
            $is_saved = $accept_reject_model->save();

            if ($is_saved) {
                $remarks_status = 1;
            } else {
                $remarks_status = 0;
            }
            if ($opreation_type == 'R') {
                $paymentArrear_arr = DB::connection('pgsql_payment')->table('' . $schemaname . '.ben_payment_details')->where('dist_code', $district_code)->where('application_id', $row->application_id)->first();
                //dd($paymentArrear_arr);
                if (!empty($paymentArrear_arr)) {
                    // dd($row->rejected_arrear);
                    if (!empty($row->rejected_arrear)) {
                        //dd('ok');
                        $payment_model_arraer = new DataSourceCommon;
                        $payment_model_arraer->setTable('' . $schemaname . '.ben_payment_details');
                        $payment_model_arraer->setConnection('pgsql_payment');
                        try {
                            $arear_status = $payment_model_arraer->where('application_id', $row->application_id)->update(['arrear_caste_month' => $row->rejected_arrear, 'openning_due_amt' => $row->rejected_due_amt, 'openning_due_count' => $row->rejected_due_count]);
                        } catch (\Exception $e) {
                            $return_status = 0;
                            $return_msg = $errormsg['roolback'];
                            // $return_msg = $e;
                            DB::connection('pgsql_appwrite')->rollBack();
                            DB::connection('pgsql_encwrite')->rollBack();
                            DB::connection('pgsql_payment')->rollBack();
                            return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                        }
                    } else {
                        $arear_status = 1;
                    }
                } else {
                    $arear_status = 1;
                }
                if (count($ben_master_status_arr) > 0) {
                    try {
                        $master_status_main = DB::connection('pgsql_appwrite')->statement("update " . $TablePersonalMain . "  set is_caste_changed=NULL where  is_caste_changed=1 and application_id=" . $row->application_id);
                    } catch (\Exception $e) {
                        // dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($ben_master_status_faulty_arr) > 0) {
                    try {
                        $master_status_main = DB::connection('pgsql_appwrite')->statement("update " . $TablePersonalFaulty . "  set is_caste_changed=NULL where  is_caste_changed=1 and application_id=" . $row->application_id);
                    } catch (\Exception $e) {
                        // dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($payment_update_status_arr) > 0) {
                    try {
                        $payment_status = DB::connection('pgsql_payment')->statement("update " . $schemaname . ".ben_payment_details   set ben_status=1 
                        where  application_id=" . $row->application_id . "   and  ben_status=-102");
                    } catch (\Exception $e) {
                        // dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($encloser_arch_caste_status_arr) > 0) {
                    try {
                        $encolser_arch_status_caste = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_caste_modification_arch(
                            application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                            select application_id,  document_type, attched_document, created_by_level, created_at, 
                            updated_at, deleted_at, created_by, ip_address, document_extension, 
                            document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                            from  " . $TableCasteEnc . " where application_id=" . $row->application_id);
                        $del_status_enc_caste = DB::connection('pgsql_encwrite')->statement("delete from  " . $TableCasteEnc . "  where document_type=" . $doc_id . " and application_id=" . $row->application_id);
                    } catch (\Exception $e) {
                        // dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
            } else {
                $arear_status = 1;
            }

            if ($arear_status && $is_status_updated && $remarks_status) {

                $return_status = 1;
                if ($is_bulk == 1) {
                    $return_msg = "Applications " . $message;
                } else
                    $return_msg = "Application with ID:" . $row->application_id . " " . $message;
            } else {

                $return_status = 0;
                $return_msg = $errormsg['roolback'];
            }
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_encwrite')->commit();
            DB::connection('pgsql_payment')->commit();
        } catch (\Exception $e) {
            //  dd($e);
            $return_status = 0;
            $return_msg = $errormsg['roolback'];
            //$return_msg = $e;
            DB::connection('pgsql_appwrite')->rollBack();
            DB::connection('pgsql_encwrite')->rollBack();
            DB::connection('pgsql_payment')->rollBack();
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
    }
    public function approvedata(Request $request)
    {
        $this->middleware('auth');
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $designation_id = Auth::user()->designation_id;
        $errormsg = Config::get('constants.errormsg');
        if (!in_array($designation_id, ['Approver', 'Delegated Approver'])) {
            return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);

        }
       
         
        $scheme_id = $this->scheme_id;
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                //$role_id = $roleObj['id'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
        }
        $id = $request->id;
        $comments = $request->comments;
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

        $model = new DataSourceCommon;
        $TableCaste = 'lb_scheme.ben_caste_modification_track';
        $TableCasteEnc = 'lb_scheme.ben_attach_documents_caste_modification';
        $model->setTable('' . $TableCaste);
        $model->setConnection('pgsql_appwrite');
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $opreation_type = $request->opreation_type;
        if($designation_id == 'Delegated Approver')
        {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Approver')->where('stack_level', $mapping_level)->first();
        } elseif ($designation_id == 'Approver') {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Approver')->where('stack_level', $mapping_level)->first();
        } elseif ($designation_id == 'Delegated Verifier') {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Verifier')->where('stack_level', $mapping_level)->first();
        } elseif ($designation_id == 'Verifier') {
            $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Verifier')->where('stack_level', $mapping_level)->first();
        }
        // $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', $designation_id)->where('stack_level', $mapping_level)->first();
        $ds_phase = DsPhase::where('is_current', TRUE)->first();

        if ($opreation_type == 'A') { //echo 1;die;
            if ($is_bulk == 0) {
                $row = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'is_faulty', 'application_id', 'beneficiary_id', 'caste_change_type', 'new_caste', 'old_caste', 'effective_yymm')->where('is_final', FALSE)->where('application_id', $id)->where('next_level_role_id_caste', $role->id)->where('created_by_dist_code', $district_code)->first();
                $applicant_id_in = array($row->application_id);
                // if ($id == 135023166) {
                //     dd($row);
                // }
                $paymentDataChk = DB::connection('pgsql_payment')->table('' . $schemaname . '.ben_payment_details')->where('dist_code', $district_code)->where('application_id', $id)->where('is_caste_changed', 1)->get();
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);
                // print_r( $applicant_id_in);die;
                $row_list = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'is_faulty', 'application_id', 'beneficiary_id', 'caste_change_type', 'new_caste', 'old_caste', 'effective_yymm')->where('is_final', FALSE)->whereIn('application_id', $applicant_id_in)->where('next_level_role_id_caste', $role->id)->where('created_by_dist_code', $district_code)->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
                $paymentDataChk = DB::connection('pgsql_payment')->table('' . $schemaname . '.ben_payment_details')->where('dist_code', $district_code)->whereIn('application_id', $applicant_id_in)->where('is_caste_changed', 1)->get();
            }
        } else if ($opreation_type == 'R' || $opreation_type == 'T') {
            if ($is_bulk == 0) {
                $row = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'is_faulty', 'application_id', 'caste_change_type', 'new_caste', 'old_caste', 'effective_yymm')->where('is_final', FALSE)->where('application_id', $id)->where('created_by_dist_code', $district_code)->first();
                $applicant_id_in = array($row->application_id);
            } else {
                $applicant_id_post = request()->input('applicantId');

                $applicant_id_in = explode(',', $applicant_id_post);

                $row_list = $model->select('approved_due_amt', 'approved_due_count', 'rejected_due_amt', 'rejected_due_count', 'approved_arrear', 'rejected_arrear', 'is_faulty', 'application_id', 'caste_change_type', 'new_caste', 'old_caste', 'effective_yymm')->where('is_final', FALSE)->whereIn('application_id', $applicant_id_in)->where('created_by_dist_code', $district_code)->get();
                if (count($row_list) != count($applicant_id_in)) {
                    return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
                }
            }
        }
        if ($is_bulk == 0 && empty($row->application_id)) {
            return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
        }
        // if ($id == 135023166) {
        //     dump('2nd'); dd($applicant_id_in);
        // }
        $reject_cause = $request->reject_cause;
        $comments = trim($request->accept_reject_comments);
        $today = date("Y-m-d h:i:s");
        $doc_id = 3;
        if ($opreation_type == 'A') {
            $txt = 'Approved';
            $next_level_role_id = 0;
            $rejected_cause = NULL;
            $message = 'Approved Succesfully!';
            $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'is_final' => TRUE, 'next_level_role_id_caste' => $next_level_role_id, 'comments' => $comments, 'ben_approved_at' => $today];
        } else if ($opreation_type == 'R') {
            $txt = 'Rejected';
            $next_level_role_id = -100;
            $rejected_cause = $reject_cause;
            $message = 'Rejected Succesfully!';
            $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'is_final' => TRUE, 'next_level_role_id_caste' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments, 'ben_rejected_at' => $today];
        } else if ($opreation_type == 'T') {
            $txt = 'Reverted';
            $next_level_role_id = -50;
            $rejected_cause = $reject_cause;
            $message = 'Reverted Succesfully!';
            $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'next_level_role_id_caste' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments, 'ben_reverted_at' => $today];
        }
        try {

            if ($is_bulk == 1) {

                $payment_update_status_arr = array();
                $encloser_arch_main_status_arr = array();
                //$encloser_update_status_arr = array();
                $encloser_arch_main_status_faulty_arr = array();
                // $encloser_update_status_faulty_arr = array();
                $encloser_arch_caste_status_arr = array();
                //$encloser_del_caste_status_arr = array();
                $payment_master_status_arr = array();
                $ben_master_status_arr = array();
                $ben_master_status_faulty_arr = array();
                $fin_year_status = array();
                foreach ($row_list as $app_row) {
                    array_push($fin_year_status, $app_row->application_id);
                    if ($app_row->is_faulty) {
                        array_push($ben_master_status_faulty_arr, $app_row->application_id);
                    } else {
                        array_push($ben_master_status_arr, $app_row->application_id);
                    }
                    if ($app_row->new_caste == 'SC' || $app_row->new_caste == 'ST') {
                        array_push($encloser_arch_caste_status_arr, $app_row->application_id);
                        //array_push($encloser_del_caste_status_arr, $app_row->application_id);
                        if ($app_row->is_faulty) {
                            array_push($encloser_arch_main_status_faulty_arr, $app_row->application_id);
                            //array_push($encloser_delete_status_faulty_arr, $row->application_id);
                            //array_push($encloser_insert_status_faulty_arr, $row->application_id);
                        } else {
                            array_push($encloser_arch_main_status_arr, $app_row->application_id);
                            //array_push($encloser_delete_status_arr, $row->application_id);
                            // array_push($encloser_insert_status_arr, $row->application_id);
                        }
                    }
                    if ($app_row->caste_change_type == 2) {
                        array_push($payment_master_status_arr, $app_row->application_id);
                        array_push($payment_update_status_arr, $app_row->application_id);
                    }else if($app_row->caste_change_type == 1){
                        if(($app_row->new_caste == 'SC' && $app_row->old_caste == 'ST') || ($app_row->new_caste == 'ST' && $app_row->old_caste == 'SC')){
                            array_push($payment_master_status_arr, $app_row->application_id);
    
                        }
    
                    }
                }
            } else {
                $payment_update_status_arr = array();
                $encloser_arch_main_status_arr = array();
                $encloser_delete_status_arr = array();
                $encloser_insert_status_arr = array();
                $encloser_arch_main_status_faulty_arr = array();
                $encloser_delete_status_faulty_arr = array();
                $encloser_insert_status_faulty_arr = array();
                $encloser_arch_caste_status_arr = array();
                $encloser_del_caste_status_arr = array();
                $payment_master_status_arr = array();
                $ben_master_status_arr = array();
                $ben_master_status_faulty_arr = array();
                $fin_year_status = array();
                array_push($fin_year_status, $row->application_id);
                if ($row->is_faulty) {
                    array_push($ben_master_status_faulty_arr, $row->application_id);
                } else {
                    array_push($ben_master_status_arr, $row->application_id);
                }
                if ($row->new_caste == 'SC' || $row->new_caste == 'ST') {
                    array_push($encloser_arch_caste_status_arr, $row->application_id);
                    //array_push($encloser_del_caste_status_arr, $row->application_id);
                    if ($row->is_faulty) {
                        array_push($encloser_arch_main_status_faulty_arr, $row->application_id);
                        // array_push($encloser_delete_status_faulty_arr, $row->application_id);
                        // array_push($encloser_insert_status_faulty_arr, $row->application_id);
                    } else {
                        array_push($encloser_arch_main_status_arr, $row->application_id);
                        //array_push($encloser_delete_status_arr, $row->application_id);
                        // array_push($encloser_insert_status_arr, $row->application_id);
                    }
                }
                if ($row->caste_change_type == 2) {

                    array_push($payment_update_status_arr, $row->application_id);
                    array_push($payment_master_status_arr, $row->application_id);
                }else if($row->caste_change_type == 1){
                    if(($row->new_caste == 'SC' && $row->old_caste == 'ST') || ($row->new_caste == 'ST' && $row->old_caste == 'SC')){
                        array_push($payment_master_status_arr, $row->application_id);

                    }

                }
            }
            //dd($ben_master_status_arr);
            $getModelFunc = new getModelFunc();
            $TablePersonalMain = $getModelFunc->getTable($district_code, $this->source_type, 1);
            $TablePersonalFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
            $TableEncMain = $getModelFunc->getTable($district_code, $this->source_type, 6);
            $TableEncFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6);
            $payment_model_already = new DataSourceCommon;
            $Table_a = 'lb_main.ben_caste_modification_track_payments';
            $payment_model_already->setTable('' . $Table_a);
            $payment_model_already->setConnection('pgsql_payment');
            $schemaname = $getModelFunc->getSchemaDetails();
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
            
            if ($is_bulk == 1) {


                $j = 0;

                foreach ($row_list as $app_row) {
                    $accept_reject_model = new DataSourceCommon;
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                    $accept_reject_model->setTable('' . $Table);
                    $accept_reject_model->setConnection('pgsql_appwrite');
                    $accept_reject_model->op_type = 'C' . $opreation_type;
                    $accept_reject_model->application_id = $app_row->application_id;
                    $accept_reject_model->designation_id = $designation_id;
                    $accept_reject_model->scheme_id = $scheme_id;
                    $accept_reject_model->user_id = $user_id;
                    $accept_reject_model->comment_message = $comments;
                    $accept_reject_model->rejected_reverted_cause = $rejected_cause;
                    $accept_reject_model->mapping_level = $mapping_level;
                    $accept_reject_model->created_by = $user_id;
                    $accept_reject_model->created_by_level = $mapping_level;
                    $accept_reject_model->created_by_dist_code = $district_code;
                    $accept_reject_model->created_by_local_body_code = $urban_body_code;
                    $accept_reject_model->ip_address = request()->ip();
                    $is_saved = $accept_reject_model->save();
                    if ($opreation_type == 'A') {
                        if ($app_row->caste_change_type == 2) {
                            // $effective_month =  substr($app_row->effective_yymm, -2);
                            // $effective_year = substr($app_row->effective_yymm, 0, 2);
                            $effective_month =  substr(date('m'), -2);
                            $effective_year = substr(date('ym'), 0, 2);
                            if ($effective_month == '01') {
                                $new_effective_year =  sprintf("%02d", intval($effective_year) - 1);
                                $new_effective_month = '12';
                            } else {
                                $new_effective_year =  sprintf("%02d", $effective_year);
                                $new_effective_month = sprintf("%02d", intval($effective_month) - 1);
                            }
                            if ($app_row->is_faulty == false) {
                                $approval_date = DB::connection('pgsql_appread')->table('lb_scheme.ben_personal_details')->select('approval_date')->where('application_id', $app_row->application_id)->first();
                            }
                            if ($app_row->is_faulty == true) {
                                $approval_date = DB::connection('pgsql_appread')->table('lb_scheme.faulty_ben_personal_details')->select('approval_date')->where('application_id', $app_row->application_id)->first();
                            }
                            $date = new DateTime($approval_date->approval_date);
                            $benaleady_category_changed = $payment_model_already->where('application_id', $app_row->application_id)->count();
                            if ($benaleady_category_changed == 0) {
                                $payment_model_effective1 = new DataSourceCommon;
                                $Table = 'lb_main.ben_caste_modification_track_payments';
                                $payment_model_effective1->setTable('' . $Table);
                                $payment_model_effective1->setConnection('pgsql_payment');
                                $payment_model_effective1->application_id = $app_row->application_id;
                                $payment_model_effective1->beneficiary_id = $app_row->beneficiary_id;
                                $payment_model_effective1->from_effective_yymm =  $date->format('ym'); //Approval Date in YYMM format
                                $payment_model_effective1->to_effective_yymm =  (int) ($new_effective_year . $new_effective_month);
                                
                                $payment_model_effective1->caste = $app_row->old_caste;
                                $payment_model_effective1->is_faulty = $app_row->is_faulty;
                                $is_saved_effective1 = $payment_model_effective1->save();
                            } else {
                                $payment_model_effective1 = new DataSourceCommon;
                                $Table = 'lb_main.ben_caste_modification_track_payments';
                                $payment_model_effective1->setTable('' . $Table);
                                $payment_model_effective1->setConnection('pgsql_payment');
                                $to_effective_yymm = (int) ($new_effective_year . $new_effective_month);
                                
                                $is_saved_effective1 = $payment_model_effective1->whereNull('to_effective_yymm')->where('application_id', $app_row->application_id)->update(['to_effective_yymm' => $to_effective_yymm]);
                            }
                            $payment_model_effective = new DataSourceCommon;
                            $Table = 'lb_main.ben_caste_modification_track_payments';
                            $payment_model_effective->setTable('' . $Table);
                            $payment_model_effective->setConnection('pgsql_payment');
                            $payment_model_effective->application_id = $app_row->application_id;
                            $payment_model_effective->beneficiary_id = $app_row->beneficiary_id;
                            $payment_model_effective->from_effective_yymm = date('ym');
                            $payment_model_effective->to_effective_yymm = NULL;
                            $payment_model_effective->caste = $app_row->new_caste;
                            $payment_model_effective->is_faulty = $app_row->is_faulty;
                            $is_saved_effective = $payment_model_effective->save();
                            $paymentArrear_arr = DB::connection('pgsql_payment')->table('' . $schemaname . '.ben_payment_details')->where('dist_code', $district_code)->where('application_id', $app_row->application_id)->first();
                            if (!empty($paymentArrear_arr)) {
                                if (!empty($app_row->approved_arrear)) {
                                    $payment_model_arraer = new DataSourceCommon;
                                    $payment_model_arraer->setTable('' . $schemaname . '.ben_payment_details');
                                    $payment_model_arraer->setConnection('pgsql_payment');
                                    $arear_status = $payment_model_arraer->where('application_id', $app_row->application_id)->update(['arrear_caste_month' => $app_row->approved_arrear, 'openning_due_amt' => $app_row->approved_due_amt, 'openning_due_count' => $app_row->approved_due_count]);
                                } else {
                                    $arear_status = 1;
                                }
                            } else {
                                $arear_status = 1;
                            }
                        } else {
                            $is_saved_effective = 1;
                            $is_saved_effective1 = 1;
                            $arear_status = 1;
                        }
                    } else {
                        $is_saved_effective = 1;
                        $is_saved_effective1 = 1;
                        if ($opreation_type == 'R') {
                            $paymentArrear_arr = DB::connection('pgsql_payment')->table('' . $schemaname . '.ben_payment_details')->where('dist_code', $district_code)->where('application_id', $app_row->application_id)->first();
                            if (!empty($paymentArrear_arr)) {
                                if (!empty($app_row->rejected_arrear)) {
                                    $payment_model_arraer = new DataSourceCommon;
                                    $payment_model_arraer->setTable('' . $schemaname . '.ben_payment_details');
                                    $payment_model_arraer->setConnection('pgsql_payment');
                                    $arear_status = $payment_model_arraer->where('application_id', $app_row->application_id)->update(['arrear_caste_month' => $app_row->rejected_arrear, 'openning_due_amt' => $app_row->rejected_due_amt, 'openning_due_count' => $app_row->rejected_due_count]);
                                } else {
                                    $arear_status = 1;
                                }
                            } else {
                                $arear_status = 1;
                            }
                        } else
                            $arear_status = 1;
                    }
                    if ($is_saved && $is_saved_effective && $is_saved_effective1 &&  $arear_status) {
                        $j++;
                    }
                }
                if (count($row_list) == $j) {
                    $remarks_status = 1;
                } else {
                    $remarks_status = 0;
                }
            } else {
                $accept_reject_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                $accept_reject_model->setConnection('pgsql_appwrite');
                $accept_reject_model->setTable('' . $Table);
                $accept_reject_model->op_type = 'C' . $opreation_type;
                $accept_reject_model->ben_id = $id;
                $accept_reject_model->application_id = $row->application_id;
                $accept_reject_model->designation_id = $designation_id;
                $accept_reject_model->scheme_id = $scheme_id;
                $accept_reject_model->user_id = $user_id;
                $accept_reject_model->comment_message = $comments;
                $accept_reject_model->mapping_level = $mapping_level;
                $accept_reject_model->created_by = $user_id;
                $accept_reject_model->created_by_level = $mapping_level;
                $accept_reject_model->created_by_dist_code = $district_code;
                $accept_reject_model->created_by_local_body_code = $urban_body_code;
                $accept_reject_model->rejected_reverted_cause = $rejected_cause;
                $accept_reject_model->ip_address = request()->ip();
                $is_saved = $accept_reject_model->save();
                // dump($row->effective_yymm);
                if ($opreation_type == 'A') {
                    //dump($row->caste_change_type);
                    if ($row->caste_change_type == 2) {
                        //dump($row->caste_change_type);
                        // $effective_month = substr($row->effective_yymm, -2);
                        // dump($effective_month);
                        // $effective_year = substr($row->effective_yymm, 0, 2);
                        // dump($effective_year);
                        $effective_month =  substr(date('m'), -2);
                        $effective_year = substr(date('ym'), 0, 2);
                        if ($effective_month == '01') {
                            $new_effective_year =  sprintf("%02d", intval($effective_year) - 1);
                            $new_effective_month = '12';
                        } else {
                            $new_effective_year =  sprintf("%02d", $effective_year);
                            $new_effective_month = sprintf("%02d", intval($effective_month) - 1);
                        }
                        // dump($new_effective_year);
                        // dd($row);
                        if ($row->is_faulty == false) {
                            $approval_date = DB::connection('pgsql_appread')->table('lb_scheme.ben_personal_details')->select('approval_date')->where('application_id', $row->application_id)->first();
                        }
                        if ($row->is_faulty == true) {
                            $approval_date = DB::connection('pgsql_appread')->table('lb_scheme.faulty_ben_personal_details')->select('approval_date')->where('application_id', $row->application_id)->first();
                        }
                        // if ($row->application_id == 135023166) {
                        //     dd($approval_date->approval_date);
                        // }
                        $date = new DateTime($approval_date->approval_date);
                        $benaleady_category_changed = $payment_model_already->where('application_id', $row->application_id)->count();
                        //dd($benaleady_category_changed);
                        if ($benaleady_category_changed == 0) {
                            // if ($row->application_id == 135023166) {
                            //     dd('ENter');
                            // }
                            $payment_model_effective1 = new DataSourceCommon;
                            $Table = 'lb_main.ben_caste_modification_track_payments';
                            $payment_model_effective1->setTable('' . $Table);
                            $payment_model_effective1->setConnection('pgsql_payment');
                            $payment_model_effective1->application_id = $row->application_id;
                            $payment_model_effective1->beneficiary_id = $row->beneficiary_id;
                            // $payment_model_effective1->from_effective_yymm =  $ds_phase->lot_base_yymm;
                            $payment_model_effective1->from_effective_yymm = $date->format('ym'); //approval date in YYMM format;
                            $payment_model_effective1->to_effective_yymm =  (int) ($new_effective_year . $new_effective_month);

                            $payment_model_effective1->caste = $row->old_caste;
                            $payment_model_effective1->is_faulty = $row->is_faulty;
                            $is_saved_effective1 = $payment_model_effective1->save();
                        } else {
                            $payment_model_effective1 = new DataSourceCommon;
                            $Table = 'lb_main.ben_caste_modification_track_payments';
                            $payment_model_effective1->setTable('' . $Table);
                            $payment_model_effective1->setConnection('pgsql_payment');
                            $to_effective_yymm = (int) ($new_effective_year . $new_effective_month);
                           
                            $is_saved_effective1 = $payment_model_effective1->whereNull('to_effective_yymm')->where('application_id', $row->application_id)->update(['to_effective_yymm' => $to_effective_yymm]);
                        }
                        $payment_model_effective = new DataSourceCommon;
                        $Table = 'lb_main.ben_caste_modification_track_payments';
                        $payment_model_effective->setTable('' . $Table);
                        $payment_model_effective->setConnection('pgsql_payment');
                        $payment_model_effective->application_id = $row->application_id;
                        $payment_model_effective->beneficiary_id = $row->beneficiary_id;
                        $payment_model_effective->from_effective_yymm = $row->effective_yymm;
                        $payment_model_effective->to_effective_yymm = NULL;
                        $payment_model_effective->caste = $row->new_caste;
                        $payment_model_effective->is_faulty = $row->is_faulty;
                        $is_saved_effective = $payment_model_effective->save();
                    } else {
                        $is_saved_effective = 1;
                        $is_saved_effective1 = 1;
                    }
                    $paymentArrear_arr = DB::connection('pgsql_payment')->table('' . $schemaname . '.ben_payment_details')->where('dist_code', $district_code)->where('application_id', $row->application_id)->first();
                    // dd($paymentArrear_arr);
                    if (!empty($paymentArrear_arr)) {
                        if (!empty($row->approved_arrear)) {
                            $payment_model_arraer = new DataSourceCommon;
                            $payment_model_arraer->setTable('' . $schemaname . '.ben_payment_details');
                            $payment_model_arraer->setConnection('pgsql_payment');
                            $arear_status = $payment_model_arraer->where('application_id', $row->application_id)->update(['arrear_caste_month' => $row->approved_arrear, 'openning_due_amt' => $row->approved_due_amt, 'openning_due_count' => $row->approved_due_count]);
                        } else {
                            $arear_status = 1;
                        }
                    } else {
                        $arear_status = 1;
                    }
                    //dd($arear_status);
                } else {
                    $is_saved_effective = 1;
                    $is_saved_effective1 = 1;
                    //$arear_status = 1;
                    if ($opreation_type == 'R') {
                        $paymentArrear_arr = DB::connection('pgsql_payment')->table('' . $schemaname . '.ben_payment_details')->where('dist_code', $district_code)->where('application_id', $row->application_id)->first();
                        if (!empty($paymentArrear_arr)) {
                            if (!empty($row->rejected_arrear)) {
                                $payment_model_arraer = new DataSourceCommon;
                                $payment_model_arraer->setTable('' . $schemaname . '.ben_payment_details');
                                $payment_model_arraer->setConnection('pgsql_payment');
                                $arear_status = $payment_model_arraer->where('application_id', $row->application_id)->update(['arrear_caste_month' => $row->rejected_arrear, 'openning_due_amt' => $row->rejected_due_amt, 'openning_due_count' => $row->rejected_due_count]);
                            } else {
                                $arear_status = 1;
                            }
                        } else {
                            $arear_status = 1;
                        }
                    } else
                        $arear_status = 1;
                }

                if ($is_saved && $is_saved_effective && $is_saved_effective1 &&  $arear_status) {
                    $remarks_status = 1;
                } else {
                    $remarks_status = 0;
                }
            }
            //dd($arear_status);
            if ($opreation_type == 'A') {
                if (count($ben_master_status_arr) > 0) {
                    try {
                        $master_status_main = DB::connection('pgsql_appwrite')->statement("update " . $TablePersonalMain . " as A set is_caste_changed=2,effective_yymm=B.effective_yymm,caste=B.new_caste,caste_certificate_no=B.new_caste_certificate_no from " . $TableCaste . " as B where  B.is_final=FALSE and A.application_id=B.application_id and A.application_id IN(" . implode(',', $ben_master_status_arr) . ")  and  B.application_id IN(" . implode(',', $ben_master_status_arr) . ") ");
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        // $return_msg = '1' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($ben_master_status_faulty_arr) > 0) {
                    try {
                        $master_status_faulty = DB::connection('pgsql_appwrite')->statement("update " . $TablePersonalFaulty . " as A set is_caste_changed=2,effective_yymm=B.effective_yymm,caste=B.new_caste,caste_certificate_no=B.new_caste_certificate_no from " . $TableCaste . " as B where  B.is_final=FALSE and A.application_id=B.application_id and A.application_id IN(" . implode(',', $ben_master_status_faulty_arr) . ")  and  B.application_id IN(" . implode(',', $ben_master_status_faulty_arr) . ") ");
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = '2' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($payment_master_status_arr) > 0) {
                    try {
                        foreach ($payment_master_status_arr as $p_arr) {
                            $my_row = $model->where('application_id', $p_arr)->where('is_final', FALSE)->first();
                            $payment_status = DB::connection('pgsql_payment')->statement("update " . $schemaname . ".ben_payment_details set is_caste_changed=1,effective_yymm=" . $my_row->effective_yymm . ",caste=SUBSTRING(upper('" . $my_row->new_caste . "'),1,2) where application_id=" . $p_arr);
                        }
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = '3' . $e;
                        //dd($e);
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($payment_update_status_arr) > 0) {
                    try {
                        $payment_status = DB::connection('pgsql_payment')->statement("update " . $schemaname . ".ben_payment_details   set ben_status=1 
                    where  application_id IN(" . implode(',', $payment_update_status_arr) . ")  and  ben_status=-102");
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = '4' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($encloser_arch_main_status_arr) > 0) {
                    try {
                        $encolser_arch_status_main = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_arch(
                        application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                        select application_id,  document_type, attched_document, created_by_level, created_at, 
                        updated_at, deleted_at, created_by, ip_address, document_extension, 
                        document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                        from  " . $TableEncMain . " as A  where A.document_type=" . $doc_id . " and A.application_id IN(" . implode(',', $encloser_arch_main_status_arr) . ")");
                        $del_status_enc_main = DB::connection('pgsql_encwrite')->statement("delete from  " . $TableEncMain . " where document_type=" . $doc_id . " and application_id IN(" . implode(',', $encloser_arch_main_status_arr) . ")");
                        $insert_status_enc_main = DB::connection('pgsql_encwrite')->statement("INSERT INTO  " . $TableEncMain . "(
                        application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                        select application_id,  document_type, attched_document, created_by_level, created_at, 
                        updated_at, deleted_at, created_by, ip_address, document_extension, 
                        document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type
                        from  " . $TableCasteEnc . "
                      where document_type=" . $doc_id . " and application_id IN(" . implode(',', $encloser_arch_main_status_arr) . ")");
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = '5' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($encloser_arch_main_status_faulty_arr) > 0) {
                    try {
                        $encolser_arch_status_faulty = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_arch(
                        application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                        select application_id,  document_type, attched_document, created_by_level, created_at, 
                        updated_at, deleted_at, created_by, ip_address, document_extension, 
                        document_mime_type, created_by_dist_code, created_by_local_body_code ,action_by,action_ip_address,action_type
                        from  " . $TableEncFaulty . " as A  where A.document_type=" . $doc_id . " and A.application_id IN(" . implode(',', $encloser_arch_main_status_faulty_arr) . ")");
                        $del_status_enc_faulty = DB::connection('pgsql_encwrite')->statement("delete from  " . $TableEncFaulty . " where document_type=" . $doc_id . " and application_id IN(" . implode(',', $encloser_arch_main_status_faulty_arr) . ")");
                        $insert_status_enc_faulty = DB::connection('pgsql_encwrite')->statement("INSERT INTO  " . $TableEncFaulty . "(
                        application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                        select application_id,  document_type, attched_document, created_by_level, created_at, 
                        updated_at, deleted_at, created_by, ip_address, document_extension, 
                        document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                        from  " . $TableCasteEnc . "
                      where document_type=" . $doc_id . " and application_id IN(" . implode(',', $encloser_arch_main_status_faulty_arr) . ")");
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = '6' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($encloser_arch_caste_status_arr) > 0) {
                    try {
                        $encolser_arch_status_caste = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_caste_modification_arch(
                        application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                        select application_id,  document_type, attched_document, created_by_level, created_at, 
                        updated_at, deleted_at, created_by, ip_address, document_extension, 
                        document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                        from  " . $TableEncMain . " as A  where A.application_id IN(" . implode(',', $encloser_arch_caste_status_arr) . ")");
                        $del_status_enc_caste = DB::connection('pgsql_encwrite')->statement("delete from  " . $TableCasteEnc . "  where document_type=" . $doc_id . " and application_id IN(" . implode(',', $encloser_arch_caste_status_arr) . ")");
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        // $return_msg = '7' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($fin_year_status) > 0) {
                    try {
                        $payment_status_fin_year = DB::connection('pgsql_payment')->statement("update " . $schemaname . ".ben_payment_details set arrear_lot_status='R',arrear_lot_type='A' where arrear_lot_status IS NULL and arrear_lot_type IS NULL and application_id IN(" . implode(',', $fin_year_status) . ")");
                    } catch (\Exception $e) {
                        dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        // $return_msg = '1' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
            }
            if ($opreation_type == 'R') {
                if (count($ben_master_status_arr) > 0) {
                    try {
                        $master_status_main = DB::connection('pgsql_appwrite')->statement("update " . $TablePersonalMain . "  set is_caste_changed=NULL where  is_caste_changed=1 and application_id IN(" . implode(',', $applicant_id_in) . ")");
                    } catch (\Exception $e) {
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = '8' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($ben_master_status_faulty_arr) > 0) {
                    try {
                        $master_status_main = DB::connection('pgsql_appwrite')->statement("update " . $TablePersonalFaulty . "  set is_caste_changed=NULL where  is_caste_changed=1 and application_id IN(" . implode(',', $applicant_id_in) . ")");
                    } catch (\Exception $e) {
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        //$return_msg = '9' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($payment_update_status_arr) > 0) {
                    try {
                        $payment_status = DB::connection('pgsql_payment')->statement("update " . $schemaname . ".ben_payment_details   set ben_status=1 
                        where   ben_status=-102 and application_id IN(" . implode(',', $applicant_id_in) . ")");
                    } catch (\Exception $e) {
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        // $return_msg = '10' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
                if (count($encloser_arch_caste_status_arr) > 0) {
                    try {
                        $encolser_arch_status_caste = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_caste_modification_arch(
                            application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                            select application_id,  document_type, attched_document, created_by_level, created_at, 
                            updated_at, deleted_at, created_by, ip_address, document_extension, 
                            document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type
                            from  " . $TableCasteEnc . "  where application_id IN(" . implode(',', $applicant_id_in) . ")");
                        $del_status_enc_caste = DB::connection('pgsql_encwrite')->statement("delete from  " . $TableCasteEnc . "  where document_type=" . $doc_id . " and application_id IN(" . implode(',', $applicant_id_in) . ")");
                    } catch (\Exception $e) {
                        //dd($e);
                        $return_status = 0;
                        $return_msg = $errormsg['roolback'];
                        // $return_msg = '11' . $e;
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
                    }
                }
            }
            if ($is_bulk == 1) {
                $is_status_updated = $model->where('is_final', FALSE)->whereIn('application_id', $applicant_id_in)->update($input);
            } else {
                $is_status_updated = $model->where('is_final', FALSE)->where('application_id', $id)->update($input);
            }
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_encwrite')->commit();
            DB::connection('pgsql_payment')->commit();
            $return_status = 1;
            if ($is_bulk == 1) {
                $return_msg = "Beneficiary Caste Modification " . $message;
            } else
                $return_msg = "Beneficiary Caste Modification with Application Id:" . $row->application_id . " " . $message;
        } catch (\Exception $e) {
            dd($e);
            $return_status = 0;
            $return_msg = $errormsg['roolback'];
            $return_msg = $e;
            DB::connection('pgsql_appwrite')->rollBack();
            DB::connection('pgsql_encwrite')->rollBack();
            DB::connection('pgsql_payment')->rollBack();
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
    }
    function casteInfoMis(Request $request)
    {
        $this->middleware('auth');
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' ||  $designation_id == 'HOP' || $designation_id == 'Dashboard' || $designation_id == 'MisState') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if (($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') || ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier')) {
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
        $gp_ward_visible = 0;
        $municipality_visible = 0;
        $districts = District::get();
        return view(
            'casteManagement.misreport',
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

       
        $this->middleware('auth');
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;
        $old_caste = $request->old_caste;
        $new_caste = $request->new_caste;
        $caste = $request->caste_category;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $base_date  = '2020-08-16';
        $c_time = Carbon::now();
        $c_date = $c_time->format("Y-m-d");
        $heading_msg = '';
        $title = "";

        if($new_caste=='SC & ST')
        {
            $visible=1;

        }
        else{

            $visible=0;
        }
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
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['block'] = 'Block/Sub Division';
        $attributes['muncid'] = 'Municipality';
        $attributes['gp_ward'] = 'GP/Ward';
        $attributes['from_date'] = 'From Date';
        $attributes['to_date'] = 'To Date';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $user_msg = "Caste Info Modification Mis Report";
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
                    $data = $this->getWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date, $old_caste, $new_caste);
                } else {
                    $column = "GP";
                    $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $old_caste, $new_caste);
                }
            } else if (!empty($muncid)) {
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $old_caste, $new_caste);
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $old_caste, $new_caste);
                } else if ($urban_code == 2) {
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $old_caste, $new_caste);
                    $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $column = "Block";
                    $data = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $old_caste, $new_caste);
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $old_caste, $new_caste);
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $old_caste, $new_caste);
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $old_caste, $new_caste);
                        $data2 = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $old_caste, $new_caste);
                        $data = array_merge($data1, $data2);
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWise(NULL, NULL, NULL, NULL, $from_date, $to_date, $old_caste, $new_caste);

                    $external = 0;
                }
            }
            if (!empty($old_caste)) {
                $heading_msg = $heading_msg . " from the Caste  " . $old_caste;
            }
            if (!empty($new_caste)) {
                $heading_msg = $heading_msg . " to the Caste  " . $new_caste;
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
            'heading_msg' => $heading_msg,
            'visible' =>$visible
        ]);
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $old_caste = NULL, $new_caste = NULL)
    {
        $this->middleware('auth');

        if($new_caste=='SC & ST')
        {
                    $whereCon = "where 1=1";
                    if (!empty($old_caste)) {
                        $whereCon .= "  and old_caste='" . $old_caste . "'";
                    }
                    // if (!empty($new_caste)) {
                    //     $whereCon .= "  and new_caste='" . $new_caste . "'";
                    // }
                   

                    $query="select main.location_id,main.location_name,
                    COALESCE(caste.tot_ben_sc,0) as tot_ben_sc,
                    COALESCE(caste.total_verified_sc,0) as total_verified_sc,
                    COALESCE(caste.total_yet_tobe_verified_sc,0) as total_yet_tobe_verified_sc,
                    COALESCE(caste.total_approved_sc,0) as total_approved_sc,
                    COALESCE(caste.total_yet_tobe_approved_sc,0) as total_yet_tobe_approved_sc,
                    COALESCE(caste.total_rejected_sc,0) as total_rejected_sc,
                    COALESCE(caste.total_reverted_sc,0) as total_reverted_sc,
            
                    COALESCE(caste.tot_ben_st,0) as tot_ben_st,
                    COALESCE(caste.total_verified_st,0) as total_verified_st,
                    COALESCE(caste.total_yet_tobe_verified_st,0) as total_yet_tobe_verified_st,
                    COALESCE(caste.total_approved_st,0) as total_approved_st,
                    COALESCE(caste.total_yet_tobe_approved_st,0) as total_yet_tobe_approved_st,
                    COALESCE(caste.total_rejected_st,0) as total_rejected_st,
                    COALESCE(caste.total_reverted_st,0) as total_reverted_st
                    from
                    (
                    select district_code as location_id,district_name as location_name
                    from public.m_district  
                    ) as main LEFT JOIN
                    (
                    select count(1) filter(where new_caste='SC' ) as  tot_ben_sc,
                    count(1) filter(where next_level_role_id_caste>0 and new_caste='SC' ) as total_verified_sc,
                    count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE  and new_caste='SC' ) as total_yet_tobe_verified_sc,
                    count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE and new_caste='SC' ) as total_approved_sc,
                    count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE and new_caste='SC' ) as total_yet_tobe_approved_sc,
                    count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE and new_caste='SC' ) as total_rejected_sc,
                    count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE and new_caste='SC') as total_reverted_sc,
            
                    count(1) filter(where new_caste='ST' ) as  tot_ben_st,
                    count(1) filter(where next_level_role_id_caste>0 and new_caste='ST') as total_verified_st,
                    count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE and new_caste='ST') as total_yet_tobe_verified_st,
                    count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE and new_caste='ST') as total_approved_st,
                    count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE and new_caste='ST') as total_yet_tobe_approved_st,
                    count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE and new_caste='ST') as total_rejected_st,
                    count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE and new_caste='ST') as total_reverted_st,
                    created_by_dist_code 
                    from lb_scheme.ben_caste_modification_track    " . $whereCon . " and  new_caste in('SC','ST')
                    group by created_by_dist_code
                    ) as caste ON main.location_id=caste.created_by_dist_code"
;
                //dd($query);
                   
                    $result = DB::connection('pgsql_appread')->select($query);
            }
            else{

                $whereCon = "where 1=1";
                if (!empty($old_caste)) {
                    $whereCon .= "  and old_caste='" . $old_caste . "'";
                }
                if (!empty($new_caste)) {
                    $whereCon .= "  and new_caste='" . $new_caste . "'";
                }
                $query = "select main.location_id,main.location_name,
                COALESCE(caste.tot_ben,0) as tot_ben,
                COALESCE(caste.total_verified,0) as total_verified,
                COALESCE(caste.total_yet_tobe_verified,0) as total_yet_tobe_verified,
                COALESCE(caste.total_approved,0) as total_approved,
                COALESCE(caste.total_yet_tobe_approved,0) as total_yet_tobe_approved,
                COALESCE(caste.total_rejected,0) as total_rejected,
                COALESCE(caste.total_reverted,0) as total_reverted
                from
                (
                select district_code as location_id,district_name as location_name
                from public.m_district  
                ) as main LEFT JOIN
                (
                    select count(1) tot_ben,
                    count(1) filter(where next_level_role_id_caste>0) as total_verified,
                    count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE) as total_yet_tobe_verified,
                    count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE) as total_approved,
                    count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE) as total_yet_tobe_approved,
                    count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE) as total_rejected,
                    count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE) as total_reverted,
                    created_by_dist_code 
                    from lb_scheme.ben_caste_modification_track   " . $whereCon . " 
                    group by created_by_dist_code
                ) as caste ON main.location_id=caste.created_by_dist_code";

                $result = DB::connection('pgsql_appread')->select($query);
            }
        // echo $query;die;
        
        return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $old_caste = NULL, $new_caste = NULL)
    {
        $this->middleware('auth');

        if($new_caste=='SC & ST')
        {
            $whereMain = "where  district_code=" . $district_code;
            $whereCon = "where  created_by_dist_code=" . $district_code;
            if (!empty($old_caste)) {
                $whereCon .= "  and old_caste='" . $old_caste . "'";
            }
            // if (!empty($new_caste)) {
            //     $whereCon .= "  and new_caste='" . $new_caste . "'";
            // }
    
            $query = "select main.location_id,main.location_name||'-SubDivision' as location_name,
            COALESCE(caste.tot_ben_sc,0) as tot_ben_sc,
                    COALESCE(caste.total_verified_sc,0) as total_verified_sc,
                    COALESCE(caste.total_yet_tobe_verified_sc,0) as total_yet_tobe_verified_sc,
                    COALESCE(caste.total_approved_sc,0) as total_approved_sc,
                    COALESCE(caste.total_yet_tobe_approved_sc,0) as total_yet_tobe_approved_sc,
                    COALESCE(caste.total_rejected_sc,0) as total_rejected_sc,
                    COALESCE(caste.total_reverted_sc,0) as total_reverted_sc,
            
                    COALESCE(caste.tot_ben_st,0) as tot_ben_st,
                    COALESCE(caste.total_verified_st,0) as total_verified_st,
                    COALESCE(caste.total_yet_tobe_verified_st,0) as total_yet_tobe_verified_st,
                    COALESCE(caste.total_approved_st,0) as total_approved_st,
                    COALESCE(caste.total_yet_tobe_approved_st,0) as total_yet_tobe_approved_st,
                    COALESCE(caste.total_rejected_st,0) as total_rejected_st,
                    COALESCE(caste.total_reverted_st,0) as total_reverted_st
            from
            (
                select sub_district_code as location_id,sub_district_name as location_name
                from public.m_sub_district  " . $whereMain . " 
            ) as main LEFT JOIN
            (
                select count(1) filter(where new_caste='SC' ) as  tot_ben_sc,
                count(1) filter(where next_level_role_id_caste>0 and new_caste='SC' ) as total_verified_sc,
                count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE  and new_caste='SC' ) as total_yet_tobe_verified_sc,
                count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE and new_caste='SC' ) as total_approved_sc,
                count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE and new_caste='SC' ) as total_yet_tobe_approved_sc,
                count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE and new_caste='SC' ) as total_rejected_sc,
                count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE and new_caste='SC') as total_reverted_sc,
        
                count(1) filter(where new_caste='ST' ) as  tot_ben_st,
                count(1) filter(where next_level_role_id_caste>0 and new_caste='ST') as total_verified_st,
                count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE and new_caste='ST') as total_yet_tobe_verified_st,
                count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE and new_caste='ST') as total_approved_st,
                count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE and new_caste='ST') as total_yet_tobe_approved_st,
                count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE and new_caste='ST') as total_rejected_st,
                count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE and new_caste='ST') as total_reverted_st,
                created_by_local_body_code 
                from lb_scheme.ben_caste_modification_track  " . $whereCon . " and  new_caste in('SC','ST')
                group by created_by_local_body_code
            ) as caste ON main.location_id=caste.created_by_local_body_code";


        }
        else{

            $whereMain = "where  district_code=" . $district_code;
            $whereCon = "where  created_by_dist_code=" . $district_code;
            if (!empty($old_caste)) {
                $whereCon .= "  and old_caste='" . $old_caste . "'";
            }
            if (!empty($new_caste)) {
                $whereCon .= "  and new_caste='" . $new_caste . "'";
            }
    
            $query = "select main.location_id,main.location_name||'-SubDivision' as location_name,
            COALESCE(caste.tot_ben,0) as tot_ben,
            COALESCE(caste.total_verified,0) as total_verified,
            COALESCE(caste.total_yet_tobe_verified,0) as total_yet_tobe_verified,
            COALESCE(caste.total_approved,0) as total_approved,
            COALESCE(caste.total_yet_tobe_approved,0) as total_yet_tobe_approved,
            COALESCE(caste.total_rejected,0) as total_rejected,
            COALESCE(caste.total_reverted,0) as total_reverted
            from
            (
                select sub_district_code as location_id,sub_district_name as location_name
                from public.m_sub_district  " . $whereMain . " 
            ) as main LEFT JOIN
            (
                select count(1) tot_ben,
                count(1) filter(where next_level_role_id_caste>0) as total_verified,
                count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE) as total_yet_tobe_verified,
                count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE) as total_approved,
                count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE) as total_yet_tobe_approved,
                count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE) as total_reverted,
                count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE) as total_rejected,
                created_by_local_body_code 
                from lb_scheme.ben_caste_modification_track  " . $whereCon . " 
                group by created_by_local_body_code
            ) as caste ON main.location_id=caste.created_by_local_body_code";


        }
        //$whereCon = "where A.dist_code=" . $district_code;
      

        // echo $query;die;
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $old_caste = NULL, $new_caste = NULL)
    {
        $this->middleware('auth');

        if($new_caste=='SC & ST')
        {

            $whereMain = "where  district_code=" . $district_code;
            $whereCon = "where  created_by_dist_code=" . $district_code;
            if (!empty($old_caste)) {
                $whereCon .= "  and old_caste='" . $old_caste . "'";
            }
            // if (!empty($new_caste)) {
            //     $whereCon .= "  and new_caste='" . $new_caste . "'";
            // }
            $query = "select main.location_id,main.location_name||'-Block' as location_name,
            COALESCE(caste.tot_ben_sc,0) as tot_ben_sc,
            COALESCE(caste.total_verified_sc,0) as total_verified_sc,
            COALESCE(caste.total_yet_tobe_verified_sc,0) as total_yet_tobe_verified_sc,
            COALESCE(caste.total_approved_sc,0) as total_approved_sc,
            COALESCE(caste.total_yet_tobe_approved_sc,0) as total_yet_tobe_approved_sc,
            COALESCE(caste.total_rejected_sc,0) as total_rejected_sc,
            COALESCE(caste.total_reverted_sc,0) as total_reverted_sc,
    
            COALESCE(caste.tot_ben_st,0) as tot_ben_st,
            COALESCE(caste.total_verified_st,0) as total_verified_st,
            COALESCE(caste.total_yet_tobe_verified_st,0) as total_yet_tobe_verified_st,
            COALESCE(caste.total_approved_st,0) as total_approved_st,
            COALESCE(caste.total_yet_tobe_approved_st,0) as total_yet_tobe_approved_st,
            COALESCE(caste.total_rejected_st,0) as total_rejected_st,
            COALESCE(caste.total_reverted_st,0) as total_reverted_st
           from
           (
               select block_code as location_id,block_name as location_name
               from public.m_block  " . $whereMain . " 
           ) as main LEFT JOIN
           (
            select  count(1) filter(where new_caste='SC' ) as  tot_ben_sc,
            count(1) filter(where next_level_role_id_caste>0 and new_caste='SC' ) as total_verified_sc,
            count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE  and new_caste='SC' ) as total_yet_tobe_verified_sc,
            count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE and new_caste='SC' ) as total_approved_sc,
            count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE and new_caste='SC' ) as total_yet_tobe_approved_sc,
            count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE and new_caste='SC' ) as total_rejected_sc,
            count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE and new_caste='SC') as total_reverted_sc,
    
            count(1) filter(where new_caste='ST' ) as  tot_ben_st,
            count(1) filter(where next_level_role_id_caste>0 and new_caste='ST') as total_verified_st,
            count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE and new_caste='ST') as total_yet_tobe_verified_st,
            count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE and new_caste='ST') as total_approved_st,
            count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE and new_caste='ST') as total_yet_tobe_approved_st,
            count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE and new_caste='ST') as total_rejected_st,
            count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE and new_caste='ST') as total_reverted_st,
                created_by_local_body_code 
               from lb_scheme.ben_caste_modification_track   " . $whereCon . "  and  new_caste in('SC','ST')
               group by created_by_local_body_code
           ) as caste ON main.location_id=caste.created_by_local_body_code";



        }
        else{


      


        //$whereCon = "where A.dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereCon = "where  created_by_dist_code=" . $district_code;
        if (!empty($old_caste)) {
            $whereCon .= "  and old_caste='" . $old_caste . "'";
        }
        if (!empty($new_caste)) {
            $whereCon .= "  and new_caste='" . $new_caste . "'";
        }
        $query = "select main.location_id,main.location_name||'-Block' as location_name,
        COALESCE(caste.tot_ben,0) as tot_ben,
        COALESCE(caste.total_verified,0) as total_verified,
        COALESCE(caste.total_yet_tobe_verified,0) as total_yet_tobe_verified,
        COALESCE(caste.total_approved,0) as total_approved,
        COALESCE(caste.total_yet_tobe_approved,0) as total_yet_tobe_approved,
        COALESCE(caste.total_rejected,0) as total_rejected,
        COALESCE(caste.total_reverted,0) as total_reverted
       from
       (
           select block_code as location_id,block_name as location_name
           from public.m_block  " . $whereMain . " 
       ) as main LEFT JOIN
       (
        select count(1) tot_ben,
            count(1) filter(where next_level_role_id_caste>0) as total_verified,
            count(1) filter(where next_level_role_id_caste IS NULL and is_final=FALSE) as total_yet_tobe_verified,
            count(1) filter(where next_level_role_id_caste=0 and is_final=TRUE) as total_approved,
            count(1) filter(where next_level_role_id_caste>0 and is_final=FALSE) as total_yet_tobe_approved,
            count(1) filter(where next_level_role_id_caste=-100 and is_final=TRUE) as total_rejected,
            count(1) filter(where next_level_role_id_caste=-50 and is_final=FALSE) as total_reverted,
            created_by_local_body_code 
           from lb_scheme.ben_caste_modification_track   " . $whereCon . "  
           group by created_by_local_body_code
       ) as caste ON main.location_id=caste.created_by_local_body_code";
        }

        // echo $query;die;
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }

    public function applicationRevertedList(Request $request)
    {
        $this->middleware('auth');
        // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $is_rural_visible = 0;
        $urban_visible = 0;
        $munc_visible = 0;
        $gp_ward_visible = 0;
        $muncList = collect([]);
        $gpwardList = collect([]);
        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $caste = $request->caste;
        $block_ulb_code = $request->block_ulb_code;
        $gp_ward_code = $request->gp_ward_code;
        $report_type = $request->report_type;
        $download_excel = 1;
        $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
        if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
            $is_urban = $request->rural_urbanid;
            $district_code = $district_code;
            $urban_body_code = $request->urban_body_code;
            $block_ulb_code = $request->block_ulb_code;
            $is_rural_visible = 1;
            $urban_visible = 1;
            $munc_visible = 1;
            $gp_ward_visible = 1;
        } else if ($designation_id == 'Verifier' || $designation_id == 'Operator' || $designation_id == 'Delegated Verifier') {
            $district_code = $district_code;
            if ($mapping_level == 'Block') {
                $block_ulb_code = NULL;
                $is_rural_visible = 0;
                $is_urban = 2;
                $munc_visible = 0;
                $urban_body_code = $urban_body_code;
                $block_ulb_code = NULL;
                $gpwardList = GP::where('block_code', $urban_body_code)->get();
                $gp_ward_visible = 1;
            } else if ($mapping_level == 'Subdiv') {
                $block_ulb_code = $request->block_ulb_code;
                $urban_body_code = $urban_body_code;
                $is_rural_visible = 0;
                $is_urban = 1;
                $munc_visible = 1;
                $gp_ward_visible = 1;
                $muncList = UrbanBody::where('sub_district_code', $urban_body_code)->get();
                $block_ulb_code = $request->block_ulb_code;
            }
        }
        $condition = array();
        $condition["next_level_role_id_caste"] = -50;
        $condition["is_final"] = FALSE;
        if ($designation_id == 'Verifier' || $designation_id == 'Operator' || $designation_id == 'Delegated Verifier') {
            $condition["created_by_local_body_code"] = $urban_body_code;
        }


        $report_type_name = 'Reverted List';
        //$contact_table = $getModelFunc->getTableFaulty($district_code, '', 3, 1);
        $query = DB::connection('pgsql_appread')->table('lb_scheme.ben_caste_modification_track');
        if (!empty($request->ds_phase)) {
            $condition["ds_phase"] = $request->ds_phase;
        }
        if (!empty($district_code)) {
            $condition["created_by_dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
            // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
            if ($is_urban == 2) {
                if (!empty($urban_body_code)) {
                    //$condition["rural_urban_id"] = 2;
                    $condition["created_by_local_body_code"] = $urban_body_code;
                }
            }
            //'Urban'
            if ($is_urban == 1) {
                if (!empty($urban_body_code)) {
                    //$condition["rural_urban_id"] = 1;
                    $condition["created_by_local_body_code"] = $urban_body_code;
                }
                if (!empty($block_ulb_code)) {
                    $condition["block_ulb_code"] = $block_ulb_code;
                }
            }
        }
        if (!empty($gp_ward_code)) {
            $condition["gp_ward_code"] = $gp_ward_code;
        }
        if (!empty($caste)) {
            $condition["new_caste"] = $caste;
        }
        if (request()->ajax()) {
            $query = $query->where($condition);
            $serachvalue = $request->search['value'];
            $limit = $request->input('length');
            $offset = $request->input('start');

            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();
            if (empty($serachvalue)) {
                $totalRecords = $query->count();
                // dd($query);
                $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get([
                    'application_id', 'beneficiary_id', 'ben_name',  'mobile_no', 'ss_card_no', 'next_level_role_id_caste', 'rejected_cause', 'is_final', 'is_faulty'

                ]);
            } else {
                if (preg_match('/^[0-9]*$/', $serachvalue)) {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        if (strlen($serachvalue) < 10) {
                            $query1->where('application_id', $serachvalue)->orWhere('beneficiary_id', $serachvalue);
                        } else if (strlen($serachvalue) == 10) {
                            $query1->where('mobile_no', $serachvalue);
                        } else if (strlen($serachvalue) == 17) {
                            $query1->where('ss_card_no', $serachvalue);
                        }
                    });
                    $totalRecords = $query->count();
                    $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get(
                        [
                            'application_id', 'beneficiary_id', 'ben_name', 'mobile_no', 'ss_card_no', 'next_level_role_id_caste', 'rejected_cause', 'is_final', 'is_faulty'

                        ]
                    );
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('ben_name', 'like', $serachvalue . '%');
                    });
                    $totalRecords = $query->count();
                    $data = $query->orderBy('ben_name')->orderBy('gp_ward_name')->offset($offset)->limit($limit)->get(
                        [

                            'application_id', 'beneficiary_id', 'ben_name', 'mobile_no', 'ss_card_no', 'next_level_role_id_caste', 'rejected_cause', 'is_final', 'is_faulty'

                        ]
                    );
                }
                $filterRecords = count($data);
            }
            return datatables()
                ->of($data)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filterRecords)
                ->skipPaging()
                ->addColumn('application_id', function ($data) use ($report_type) {

                    return $data->application_id;
                })->addColumn('beneficiary_id', function ($data) use ($report_type) {

                    return $data->beneficiary_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->ben_name;
                })->addColumn('ss_card_no', function ($data) {
                    return $data->ss_card_no;
                })->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                })->addColumn('applicant_mobile_no', function ($data) use ($report_type) {
                    return $data->mobile_no;
                })->addColumn('Edit', function ($data) use ($report_type, $rejection_cause_list) {
                    $action = '<a href="lb-caste-revert-edit?beneficiary_id=' . $data->beneficiary_id . '&is_faulty=' . intval($data->is_faulty) . '" class="btn btn-info">Edit</a>';
                    return $action;
                })->rawColumns(['Edit', 'id', 'name'])
                ->make(true);
        } else {
            $errormsg = Config::get('constants.errormsg');
            $report_type_name = 'Reverted Applications';
            $download_excel = 0;
            return view(
                'casteManagement/reverted_applications',
                [
                    'district_code'        => $district_code,
                    'is_rural_visible'        => $is_rural_visible,
                    'is_urban'        => $is_urban,
                    'urban_visible'        => $urban_visible,
                    'urban_body_code'        => $urban_body_code,
                    'munc_visible'        => $munc_visible,
                    'gp_ward_visible'        => $gp_ward_visible,
                    'muncList'        => $muncList,
                    'gpwardList'        => $gpwardList,
                    'mappingLevel'        => $mapping_level,
                    'ds_phase_list'        => $ds_phase_list,
                    'sessiontimeoutmessage'        => $errormsg['sessiontimeOut'],
                    'download_excel'        => $download_excel,
                    'report_type_name'        => $report_type_name
                ]
            );
        }
    }
    public function revertedit(Request $request)
    {
        $this->middleware('auth');
        $caste_lb = Config::get('constants.caste_lb');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if ($designation_id != 'Operator') {
            return redirect("/")->with('error', 'Not Allowded');
        }
        $scheme_id = $this->scheme_id;
        $doc_id = 3;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $beneficiary_id = $request->beneficiary_id;
        $is_faulty = $request->is_faulty;
        if (empty($beneficiary_id)) {
            return redirect("/lb-caste-reverted-list")->with('error', 'Beneficiary ID Not Found');
        }
        if (!ctype_digit($beneficiary_id)) {
            return redirect("/lb-caste-reverted-list")->with('error', 'Beneficiary ID Not Valid');
        }
        if (!in_array($is_faulty, array(0, 1))) {
            return redirect("/lb-caste-reverted-list")->with('error', 'Parameter Not Valid');
        }

        $getModelFunc = new getModelFunc();
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
        $TableFaultyContact = $getModelFunc->getTableFaulty($district_code, $this->source_type, 3);
        $TableEnclosermain = $getModelFunc->getTable($district_code, $this->source_type, 6);
        $TableEncloserfaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaultyPersonal);
        $contact_model = new DataSourceCommon;
        $contact_model->setTable('' . $TableContact);
        $contact_model_f = new DataSourceCommon;
        $contact_model_f->setTable('' . $TableFaultyContact);
        $encolser_model = new DataSourceCommon;
        $encolser_model->setConnection('pgsql_encread');
        $personal_model_caste = new DataSourceCommon;
        $personal_model_caste->setTable('lb_scheme.ben_caste_modification_track');
        $encolser_model = new DataSourceCommon;
        $encolser_model->setConnection('pgsql_encread');
        $encolser_model->setTable('lb_scheme.ben_attach_documents_caste_modification');
        $condition = array();
        //$condition['next_level_role_id'] = 0;
        $condition['created_by_dist_code'] = $district_code;
        $condition['created_by_local_body_code'] = $urban_body_code;

        if ($is_faulty == 1) {
            $query = $personal_model_f->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)->where($condition);
            $row = $query->first();
            $row_contact = $contact_model_f->where('beneficiary_id', $beneficiary_id)->where($condition)->first();
        } else if ($is_faulty == 0) {
            $query = $personal_model->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)->where($condition);
            $row = $query->first();
            $row_contact = $contact_model->where('beneficiary_id', $beneficiary_id)->where($condition)->first();
        }
        //dd($row);
        if (empty($row)) {
            return redirect("/lb-caste-reverted-list")->with('error', ' Application Id Not found in Db');
        }
        $row->is_faulty = $is_faulty;
        $query_caste = $personal_model_caste->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id_caste', -50)->where('is_final', FALSE)->where($condition);
        $row_caste = $query_caste->first();
        if (empty($row_caste)) {
            return redirect("/lb-caste-reverted-list")->with('error', ' Application Id Not found in Db');
        }
        if (trim($row->caste) == 'OTHERS') {
            if ($row_caste->caste_change_type == 1) {
                return redirect("/lb-caste-revert-edit")->with('error', 'Not Allowded');
            } else if ($row_caste->caste_change_type == 2) {
                $caste_lb = array_diff($caste_lb, array('OTHERS'));
            }
        } else if (trim($row->caste) == 'SC' || trim($row->caste) == 'ST') {
            if ($row_caste->caste_change_type == 1) {
                if (trim($row->caste) == 'SC') {
                    //$caste_lb = array_diff($caste_lb, array('SC'));
                    $caste_lb = array_diff($caste_lb, array('OTHERS'));
                } else if (trim($row->caste) == 'ST') {
                    $caste_lb = array_diff($caste_lb, array('OTHERS'));
                }
            } else if ($row_caste->caste_change_type == 2) {
                if (trim($row->caste) == 'SC') {
                    $caste_lb = array_diff($caste_lb, array('SC', 'ST'));
                } else if (trim($row->caste) == 'ST') {
                    $caste_lb = array_diff($caste_lb, array('ST', 'SC'));
                }
            }
        }
        //dd($row_caste->toArray());
        $doc_caste_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id)->first();
        $casteEncloserCount = $encolser_model->where('application_id', $row->application_id)->where('document_type', $doc_id)->count('application_id');
        return view(
            'casteManagement/revertEdit',
            [
                'beneficiary_id'        => $beneficiary_id,
                'row'        => $row,
                'row_contact'        => $row_contact,
                'doc_caste_arr'        => $doc_caste_arr,
                'caste_change_type'        => $row_caste->caste_change_type,
                'row_caste'        => $row_caste,
                'caste_lb'        => $caste_lb,
                'casteEncloserCount'        => $casteEncloserCount
            ]
        );
    }
    public function reverteditPost(Request $request)
    {
        $this->middleware('auth');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if ($designation_id != 'Operator') {
            return redirect("/")->with('error', 'Not Allowded');
        }
        $scheme_id = $this->scheme_id;
        $doc_id = 3;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $beneficiary_id = $request->beneficiary_id;
        $is_faulty = $request->is_faulty;
        $caste_change_type = $request->caste_change_type;
        if (empty($beneficiary_id)) {
            return redirect("/lb-caste-reverted-list")->with('error', 'Beneficiary ID Not Found');
        }
        if (!ctype_digit($beneficiary_id)) {
            return redirect("/lb-caste-reverted-list")->with('error', 'Beneficiary ID Not Valid');
        }
        if (!in_array($is_faulty, array(0, 1))) {
            return redirect("/lb-caste-reverted-list")->with('error', 'Parameter Not Valid');
        }
        if (!in_array($caste_change_type, array(2, 1))) {
            return redirect("/lb-caste-reverted-list")->with('error', 'Parameter Not Valid');
        }
        $getModelFunc = new getModelFunc();
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $TableEnclosermain = $getModelFunc->getTable($district_code, $this->source_type, 6);
        $TableEncloserfaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6);
        $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
        $TableContact_f = $getModelFunc->getTableFaulty($district_code, $this->source_type, 3);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaultyPersonal);

        $contact_model = new DataSourceCommon;
        $contact_model->setTable('' . $TableContact);
        $contact_model_f = new DataSourceCommon;
        $contact_model_f->setTable('' . $TableContact_f);

        $encolser_model = new DataSourceCommon;
        $encolser_model->setConnection('pgsql_encwrite');
        $encolser_model->setTable('lb_scheme.ben_attach_documents_caste_modification');
        $modelNameAcceptReject = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
        $modelNameAcceptReject->setTable('' . $Table);

        $modelmain = new DataSourceCommon;
        $modelmain->setTable('lb_scheme.ben_caste_modification_track');
        $modelmain->setConnection('pgsql_appwrite');
        $modelmain->setKeyName('id');
        $encloser_enquiry_model = new DataSourceCommon;
        $encloser_enquiry_model->setTable('lb_scheme.ben_attach_documents_caste_modification');
        $encloser_enquiry_model->setConnection('pgsql_encwrite');
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        $condition['created_by_local_body_code'] = $urban_body_code;
        //$condition['next_level_role_id'] = 0;
        if ($is_faulty == 1) {
            $query = $personal_model_f->where('beneficiary_id', $beneficiary_id)->where($condition)->where('next_level_role_id', 0);
            $row = $query->first();
        } else if ($is_faulty == 0) {
            $query = $personal_model->where('beneficiary_id', $beneficiary_id)->where($condition)->where('next_level_role_id', 0);
            $row = $query->first();
        }
        if (empty($row)) {
            return redirect("/lb-caste-reverted-list")->with('error', ' Application Id Not found in Db');
        }
        $doc_caste_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id)->first();
        $casteEncloserCount = $encolser_model->where('application_id', $row->application_id)->where('document_type', $doc_id)->count('application_id');
        $caste_key =  array_keys(Config::get('constants.caste_lb'));
        $rules = [
            'caste_category' => 'required|in:' . implode(",", $caste_key),
            'caste_certificate_no'     => 'required_if:caste_category,SC,ST',
        ];
        $attributes = array();
        $messages = array();
        $attributes['caste_category'] = 'New Caste';
        $attributes['caste_certificate_no'] = 'New SC/ST Certificate No.';
        if ($request->caste_category == 'SC' || $request->caste_category == 'ST') {
            if ($casteEncloserCount == 0) {
                $required = 'required';
                $rules['doc_3'] = $required . '|mimes:' . $doc_caste_arr['doc_type'] . '|max:' . $doc_caste_arr['doc_size_kb'] . ',';
            } else {
                $rules['doc_3'] = 'nullable|mimes:' . $doc_caste_arr['doc_type'] . '|max:' . $doc_caste_arr['doc_size_kb'] . ',';
            }
            $messages['doc_3.max'] = "The file uploaded for " . $doc_caste_arr['doc_name'] . " size must be less than " . $doc_caste_arr['doc_size_kb'] . " KB";
            $messages['doc_3.mimes'] = "The file uploaded for " . $doc_caste_arr['doc_name'] . " must be of type " . $doc_caste_arr['doc_type'];
            $messages['doc_3.required'] = "Document for " . $doc_caste_arr['doc_name'] . " must be uploaded";
        }
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if (!$validator->passes()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        } else {
            $errormsg = Config::get('constants.errormsg');
            $old_caste = trim($request->old_caste);
            $old_caste_certificate_no = trim($request->old_caste_certificate_no);
            $caste_category = trim($request->caste_category);
            $caste_certificate_no = trim($request->caste_certificate_no);
            if ($caste_change_type == 1) {
                if (trim($row->caste) == 'OTHERS') {
                    $return_text = $errormsg['roolback'];
                    return redirect('lb-caste-revert-edit?beneficiary_id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
                }
            }

            $now = Carbon::now();

            $today = date("Y-m-d h:i:s");
            $new_value = [];

            //dd($casteEncloserCount);
            try {

                DB::connection('pgsql_appwrite')->beginTransaction();
                DB::connection('pgsql_encwrite')->beginTransaction();
                try {
                    $arch_status_main = DB::connection('pgsql_appwrite')->statement("INSERT INTO  lb_scheme.ben_caste_modification_track_arc(
                    id, scheme_id, created_by, created_by_level, created_by_dist_code, 
created_by_local_body_code, created_at, updated_at, deleted_at, ip_address, 
application_id, next_level_role_id_caste, next_level_role_id, beneficiary_id, 
ben_created_at, ben_verified_at, ben_approved_at, effective_yymm, old_caste, 
new_caste, old_caste_certificate_no, new_caste_certificate_no, caste_change_type, 
block_ulb_code, gp_ward_code, rural_urban_id, block_ulb_name, gp_ward_name, is_faulty, 
ben_name, mobile_no, ss_card_no, rejected_cause, comments, ben_rejected_at, 
is_final, ben_reverted_at,approved_arrear,rejected_arrear,approved_due_amt, approved_due_count, rejected_due_amt, rejected_due_count)
                    select id, scheme_id, created_by, created_by_level, created_by_dist_code, 
created_by_local_body_code, created_at, updated_at, deleted_at, ip_address, 
application_id, next_level_role_id_caste, next_level_role_id, beneficiary_id, 
ben_created_at, ben_verified_at, ben_approved_at, effective_yymm, old_caste, 
new_caste, old_caste_certificate_no, new_caste_certificate_no, caste_change_type, 
block_ulb_code, gp_ward_code, rural_urban_id, block_ulb_name, gp_ward_name, is_faulty, 
ben_name, mobile_no, ss_card_no, rejected_cause, comments, ben_rejected_at, 
is_final, ben_reverted_at,approved_arrear,rejected_arrear,approved_due_amt, approved_due_count, rejected_due_amt, rejected_due_count     from ben_caste_modification_track   where  is_final=FALSE and application_id=" . $row->application_id);
                } catch (\Exception $e) {
                    // dd($e);
                    $return_status = 0;
                    $return_msg = $errormsg['roolback'];
                    $return_msg = '5' . $e;
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    return redirect('lb-caste-revert-edit?beneficiary_id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_msg);
                }

                // dd($row->application_id);
                $save_2 = $modelmain->where('is_final', FALSE)->where('application_id', $row->application_id)->update(['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'new_caste_certificate_no' => $caste_certificate_no, 'new_caste' => $caste_category, 'next_level_role_id_caste' => null]);

                $op_type = 'CZ';
                $modelNameAcceptReject->op_type =  $op_type;
                $modelNameAcceptReject->application_id = $row->application_id;
                $modelNameAcceptReject->designation_id = $designation_id;
                $modelNameAcceptReject->scheme_id = $scheme_id;
                $modelNameAcceptReject->mapping_level = $mapping_level;
                $modelNameAcceptReject->created_by = $user_id;
                $modelNameAcceptReject->created_by_level = trim($mapping_level);
                $modelNameAcceptReject->created_by_dist_code = $district_code;
                $modelNameAcceptReject->created_by_local_body_code = $urban_body_code;
                $modelNameAcceptReject->ip_address = request()->ip();
                $modelNameAcceptReject->ip_address = request()->ip();
                $save_1 = $modelNameAcceptReject->save();
                if ($request->hasFile('doc_' . $doc_id)) {
                    if ($casteEncloserCount > 0) {
                        try {
                            $encolser_arch_status_caste = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_caste_modification_arch(
                                application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                                select application_id,  document_type, attched_document, created_by_level, created_at, 
                                updated_at, deleted_at, created_by, ip_address, document_extension, 
                                document_mime_type, created_by_dist_code, created_by_local_body_code ,action_by,action_ip_address,action_type
                                from  ben_attach_documents_caste_modification where application_id=" . $row->application_id);
                            $del_status_enc_caste = DB::connection('pgsql_encwrite')->statement("delete from  ben_attach_documents_caste_modification  where document_type=" . $doc_id . " and application_id=" . $row->application_id);
                        } catch (\Exception $e) {
                            //dd($e);
                            $return_status = 0;
                            $return_msg = $errormsg['roolback'];
                            //$return_msg = $e;
                            DB::connection('pgsql_appwrite')->rollBack();
                            DB::connection('pgsql_encwrite')->rollBack();
                            return redirect('lb-caste-revert-edit?beneficiary_id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_msg);
                        }
                    }
                    $image_file = $request->file('doc_' . $doc_id);
                    $img_data = file_get_contents($image_file);
                    $extension = $image_file->getClientOriginalExtension();
                    $mime_type = $image_file->getMimeType();
                    //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                    $base64 = base64_encode($img_data);
                    $encolser_enquiry = array();
                    $encolser_enquiry['created_by_level'] = $mapping_level;
                    $encolser_enquiry['created_by'] = $user_id;
                    $encolser_enquiry['ip_address'] = $request->ip();
                    $encolser_enquiry['created_by_dist_code'] = $row->created_by_dist_code;
                    $encolser_enquiry['created_by_local_body_code'] = $row->created_by_local_body_code;
                    $encolser_enquiry['document_type'] = $doc_id;
                    $encolser_enquiry['attched_document'] = $base64;
                    $encolser_enquiry['document_extension'] = $extension;
                    $encolser_enquiry['document_mime_type'] = $mime_type;
                    $encolser_enquiry['application_id'] = $row->application_id;
                    $encolser_enquiry['action_by'] = Auth::user()->id;
                    $encolser_enquiry['action_ip_address'] = request()->ip();
                    $encolser_enquiry['action_type'] = class_basename(request()->route()->getAction()['controller']);
                    $encolser_entry_status = $encloser_enquiry_model->insert($encolser_enquiry);
                } else {
                    $encolser_entry_status = 1;
                }




                if ($save_1 && $save_2 &&  $encolser_entry_status) {
                    DB::connection('pgsql_appwrite')->commit();
                    DB::connection('pgsql_encwrite')->commit();
                    $return_text = "Beneficiary Caste Modified informations successfully send to higher level for verfication and approval";
                    return redirect('/lb-caste-reverted-list')->with('success', $return_text)->with('id', $beneficiary_id);
                    // return redirect('lb-caste-application-list')->with('error', $return_text);
                    //return redirect("/dedupBankView?last_ifsc=" . $old_bank_ifsc . "&last_accno=" . $old_bank_code)->with('success', $return_text);
                } else {
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect('lb-caste-revert-edit?beneficiary_id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
                }
            } catch (\Exception $e) {
                DB::connection('pgsql_appwrite')->rollBack();
                DB::connection('pgsql_encwrite')->rollBack();
                //dd($e);
                $return_text = $errormsg['roolback'];
                return redirect('lb-caste-revert-edit?beneficiary_id=' . $beneficiary_id . '&is_faulty=' . $is_faulty . '&caste_change_type=' . $caste_change_type)->with('error', $return_text);
            }
        }
    }
    public function casteInfoList(Request $request)
    {
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
        if ($designation_id == 'Delegated Verifier') {
            $mapArr = MapLavel::where('scheme_id', $duty_obj->scheme_id)->where('role_name','Verifier')->first();
        } else {
            $mapArr = MapLavel::where('scheme_id', $duty_obj->scheme_id)->where('role_name','Verifier')->first();
        }
        // $mapArr = MapLavel::where('scheme_id', $duty_obj->scheme_id)->where('role_name','Verifier')->first();
        $next_level_role_id=$mapArr->parent_id;
        $type_des='SC and ST List';
        
        //dd($type_des);
        $district_code = $duty_obj->district_code;
        $urban_bodys = collect([]);
        $gps = collect([]);
        $district_list_obj = collect([]);
        
        $where_condition=' where 1=1';
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
        }
        if ($duty_obj->mapping_level == "District") {
          $district_list_obj = District::get();
          $verifier_type = 'District';
          $is_rural = NULL;
          $created_by_local_body_code = NULL;
          $where_condition .= " AND A.created_by_dist_code=".$district_code;
          $where_condition .= " AND B.created_by_dist_code=".$district_code;
  
         
        }
        if (request()->ajax()) {
          $query = "select * from
          (select 
          A.next_level_role_id,A.application_id,
          A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name,mobile_no,
          caste_certificate_no,B.gp_ward_code,B.gp_ward_name,B.block_ulb_code,B.block_ulb_name,'0' as is_faulty,A.caste_certificate_checked,
          A.caste_certificate_check_lastdatetime,A.caste_matched_with_certificate_no
          from lb_scheme.ben_personal_details as A JOIN lb_scheme.ben_contact_details as B 
          ON A.application_id=B.application_id
          ".$where_condition." and caste IN ('SC','ST')
          UNION
          select 
          A.next_level_role_id,A.application_id,
          A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name,mobile_no,
          caste_certificate_no,B.gp_ward_code,B.gp_ward_name,B.block_ulb_code,B.block_ulb_name,'0' as is_faulty,A.caste_certificate_checked,
          A.caste_certificate_check_lastdatetime,A.caste_matched_with_certificate_no
          from lb_scheme.faulty_ben_personal_details as A LEFT JOIN lb_scheme.faulty_ben_contact_details as B 
          ON A.application_id=B.application_id
          ".$where_condition." and caste IN ('SC','ST')) as P order by application_id";
        
  
          $data = DB::connection('pgsql_appread')->select($query);
  
          //print_r($data);die;
          return datatables()->of($data)
              ->addIndexColumn()
              ->addColumn('action', function ($data) use ($scheme_id, $designation_id,$next_level_role_id) {
               
                        $action = '';
                    if(is_null($data->caste_certificate_checked) && is_null($data->caste_matched_with_certificate_no)){
               
                    $action = '<button type="button" id="validatebtn_'. $data->application_id.'" value="'. $data->application_id.'_'. $data->is_faulty.'" class="btn btn-xs btn-primary validate">Validate&nbsp; &nbsp;';
                    }
                    else{
                        if($data->caste_certificate_checked==1 && $data->caste_matched_with_certificate_no==1){
                            $action = '<i class="fa fa-check text-success"></i><b>Caste Certificate validated on '.date('d-m-Y',strtotime($data->caste_certificate_check_lastdatetime)).'</b> ';
                        }
                        elseif($data->caste_certificate_checked==1 && $data->caste_matched_with_certificate_no==2){
                            $action = 'Name Not Matched &nbsp; &nbsp;&nbsp; &nbsp;<button type="button" id="validatebtn_'. $data->application_id.'" value="'. $data->application_id.'_'. $data->is_faulty.'" class="btn btn-xs btn-info validate">Revalidate&nbsp; &nbsp;';

                        }
                        elseif($data->caste_certificate_checked==1 && $data->caste_matched_with_certificate_no==0){
                            $action = '<i class="fa fa-close text-danger"></i>&nbsp; &nbsp;&nbsp; &nbsp;<button type="button" id="validatebtn_'. $data->application_id.'" value="'. $data->application_id.'_'. $data->is_faulty.'" class="btn btn-xs btn-info validate">Revalidate&nbsp; &nbsp;';

                        } elseif($data->caste_certificate_checked==1 && $data->caste_matched_with_certificate_no==3){
                            $action = '<i class="fa fa-close text-danger"></i>&nbsp; &nbsp;&nbsp; &nbsp;<button type="button" id="validatebtn_'. $data->application_id.'" value="'. $data->application_id.'_'. $data->is_faulty.'" class="btn btn-xs btn-info validate">Revalidate&nbsp; &nbsp;';

                        }elseif($data->caste_certificate_checked==1 && $data->caste_matched_with_certificate_no==4){
                            $action = '<i class="fa fa-close text-danger"></i>&nbsp; &nbsp;&nbsp; &nbsp;<button type="button" id="validatebtn_'. $data->application_id.'" value="'. $data->application_id.'_'. $data->is_faulty.'" class="btn btn-xs btn-info validate">Revalidate&nbsp; &nbsp;';

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
              ->addColumn('caste_certificate_no', function ($data) {
                  if(!is_null($data->caste_certificate_no)){
                      return $data->caste_certificate_no;
                      }
                      else{
                          return ''; 
                      }
              })
          
              ->addColumn('mobile_no', function ($data) {
  
                  return $data->mobile_no;
              })
              ->addColumn('application_id', function ($data) {
  
                  return $data->application_id;
              })
          
              // ->with('completed', $complete)
              ->rawColumns(['id', 'name', 'block_ulb_name', 'gp_ward_name', 'action', 'mobile_no', 'application_id',  'caste_certificate_no'])
              ->make(true);
            }
    
        return view(
          'casteManagement.casteInfoList',
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
    }
    public function casteInfoValidatePost(Request $request)
    {
        $this->middleware('auth');
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
            $error_found=1;
        }
        $user_id = Auth::user()->id;
        $caste_certificate_no = $request->caste_certificate_no;
        $application_id = $request->application_id;
        $is_faulty = $request->is_faulty;

        if (empty($application_id)) {
            $return_status = 0;
            $return_text = 'Application Id is Required';
            $return_msg = array("" . $return_text);
            $error_found=1;
        }
        if ($is_faulty=='') {
            $return_status = 0;
            $return_text = 'Faulty Type is Required';
            $return_msg = array("" . $return_text);
            $error_found=1;
        }
        
      
        $insert_arr=array();
        $c_time = date('Y-m-d H:i:s', time());
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $TablePersonal = $getModelFunc->getTable($distCode, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($distCode, $this->source_type, 1);
        $Tableaadhaar = $getModelFunc->getTable($distCode, $this->source_type, 2);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaultyPersonal);
        $accept_reject_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 9);
        $accept_reject_model->setTable('' . $Table);
        $aadhaar_model = new DataSourceCommon;
        $aadhaar_model->setTable('' . $Tableaadhaar);
        //dd($is_faulty);
        if ($is_faulty == 1) {
            $row = $personal_model_f->select('ben_fname','ben_mname','ben_lname','caste_certificate_no','application_id','wbpds_ration_card_no')->where('application_id', $application_id)->first();
            $table_main= $TableFaultyPersonal;
        } else if ($is_faulty == 0) {
            $row = $personal_model->select('ben_fname','ben_mname','ben_lname','caste_certificate_no','application_id','wbpds_ration_card_no')->where('application_id', $application_id)->first();
            $table_main= $TablePersonal;
        }
        if (empty($row->application_id)) {
            return redirect("/casteInfoList")->with('error', ' Application Id Not found in Db');
            
        }
        $aadhar_row = $aadhaar_model->select('encoded_aadhar','application_id')->where('application_id', $application_id)->first();
        $ben_fname=trim($row->ben_fname);
        $ben_mname=trim($row->ben_mname);
        $ben_lname=trim($row->ben_lname);
        $ben_fullname=$ben_fname;
        //dd($ben_fullname);
        if(!empty($ben_mname)){
            $ben_fullname= $ben_fullname.$ben_mname;
        }
        if(!empty($ben_lname)){
            $ben_fullname= $ben_fullname.$ben_lname;
        }
        $ben_fullname = str_replace(' ', '', $ben_fullname);
        $insert_arr['created_by_local_body_code']=$blockCode;
        $insert_arr['api_hit_time']=$c_time;
        $insert_arr['loginid']=$user_id;
        $insert_arr['ip_address']= $request->ip();
        if(!empty($request->module_type)){
        $insert_arr['module_type']=$request->module_type;
        }
        if(!empty($request->application_id)){
            $insert_arr['application_id']=$request->application_id;
        }
        DB::beginTransaction();
       
        $validation_arr=$this->validate_with_caste_certificate($caste_certificate_no = $row->caste_certificate_no,$ben_fullname= $ben_fullname); 

        $c_time1=date('Y-m-d H:i:s', time());
        $insert_arr['m_type']=2;
        $insert_arr['response_text']=$validation_arr['response_text'];
        $c_time1=date('Y-m-d H:i:s', time());
        $insert_arr['api_response_time']= $c_time1;
        $insert=DB::table('lb_scheme.ben_caste_api_response_track')->insert($insert_arr);
      
            $update_arr=array();
            $update_arr['caste_certificate_checked']=1;
            $update_arr['caste_matched_with_certificate_no']=$validation_arr['code'];
            $update_arr['caste_certificate_check_lastdatetime']=$c_time1;
            $update_arr['action_by'] = Auth::user()->id;
            $update_arr['action_ip_address'] = request()->ip();
            $update_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
            if($validation_arr['match_found']==1){
                $is_error=0;
                $return_text=$validation_arr['message'];
            }
            else{
                $is_error=1;
                $return_text=$validation_arr['message'];   
                $return_msg = array("" . $return_text);
            }
            $update=DB::table($table_main)->where('application_id',$row->application_id)->update($update_arr);

       
            if($insert && $update){
                DB::commit();
                if( $is_error==0){
                    return redirect('casteInfoList')->with('message',  $return_text);

                }
                else
                return redirect("casteInfoList")->with('errors', $return_msg);
            }
            else{
                DB::rollBack();
                $return_text = $errormsg['roolback'];
                return redirect('casteInfoList')->with('error', $return_text);
            }
           
      
    }
    public function cron(Request $request)
    {
        ini_set('max_execution_time', 20);
        $scheme_id = $this->scheme_id;
        //$user_id = Auth::user()->id;
        $district_code = $request->dist_code;
        $is_faulty = 0;
        $limit = $request->limit;
        $ip_address = request()->ip();
        if (empty($district_code)) {
            return response()->json([
                'status' => 400,
                'errors' => 'District is required',
            ]);
        }
        if (empty($limit)) {
            return response()->json([
                'status' => 400,
                'errors' => 'Limit is required',
            ]);
        }
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $Tableaadhaar = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        $aadhaar_model = new DataSourceCommon;
        $aadhaar_model->setTable('' . $Tableaadhaar);
        $rows = DB::table('lb_scheme.ben_personal_details')->whereNull('caste_certificate_checked')->whereNull('caste_matched_with_certificate_no')
        ->where('created_by_dist_code', $district_code)->whereIn('caste',['SC','ST'])
        ->limit($limit)->get();
        if(count($rows)>0){
        try{
        DB::beginTransaction();
        $i=0;
        $insert_arr=array();
        $insert_arr['created_by_dist_code']=$district_code;
        $insert_arr['loginid']=3378;
        $insert_arr['ip_address']= $request->ip();
        $insert_arr['m_type']=2;
        foreach($rows as $row){
        $c_time = date('Y-m-d H:i:s', time());
        $ben_fname=trim($row->ben_fname);
        $ben_mname=trim($row->ben_mname);
        $ben_lname=trim($row->ben_lname);
        $ben_fullname=$ben_fname;
        //dd($ben_fullname);
        if(!empty($ben_mname)){
            $ben_fullname= $ben_fullname.$ben_mname;
        }
        if(!empty($ben_lname)){
            $ben_fullname= $ben_fullname.$ben_lname;
        }
        $ben_fullname = str_replace(' ', '', $ben_fullname);
        $insert_arr['api_hit_time']=date('Y-m-d H:i:s', time());
        $insert_arr['application_id']=$row->application_id;
        $validation_arr=$this->validate_with_caste_certificate($caste_certificate_no = $row->caste_certificate_no,$ben_fullname= $ben_fullname); 
                $insert_arr['response_text']=$validation_arr['response_text'];
                $c_time1=date('Y-m-d H:i:s', time());
                $insert_arr['api_response_time']= $c_time1;
                $insert=DB::table('lb_scheme.ben_caste_api_response_track')->insert($insert_arr);
                //dd( $validation_arr);
                    if($validation_arr['is_success']==1){
                        $return_text=$validation_arr['message'];  
                        $update_arr=array();
                        $update_arr['caste_certificate_checked']=1;
                        $update_arr['caste_certificate_check_lastdatetime']=$c_time1;
                        $update_arr['caste_matched_with_certificate_no']=$validation_arr['code'];
                        $update_arr['action_by'] = Auth::user()->id;
                        $update_arr['action_ip_address'] = request()->ip();
                        $update_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        $update=DB::table('lb_scheme.ben_personal_details')->where('application_id',$row->application_id)->update($update_arr);
                       
                    }
                    else{
                        $update=1; 
                        
                    }
                

           
            if($insert && $update){
                $i++;

            }
           
        }
         if($i==count($rows)){
            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Total - '.$i.' Applications Updated Successfully',
            ]);
         } 
         else{
            DB::rollBack();
            return response()->json([
                'status' => 200,
                'errors' => 'Someting went wrong..please try again',
            ]);
         }        
          
        
      
    }
    catch (\Exception $e) {
        dd($e);
        DB::rollBack();
        return response()->json([
            'status' => 200,
            'errors' => $e,
        ]);
    }
   }
   else{
    return response()->json([
        'status' => 200,
        'errors' => 'No record found',
    ]);
   }
}        

   
}
