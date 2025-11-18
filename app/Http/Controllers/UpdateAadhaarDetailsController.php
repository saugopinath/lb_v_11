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
use Illuminate\Support\Facades\Storage;
class UpdateAadhaarDetailsController  extends Controller
{
    //use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('auth');
        $this->base_dob_chk_date = '2021-01-01';
        $this->max_dob = '1996-01-01';
        $this->min_dob = '1961-01-01';
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
    public function markhodselect(Request $request)
    {
        try {
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if ($designation_id != 'HOD') {
            return redirect("/")->with('error', 'Not Allowded');
        }
        $scheme_id = $this->scheme_id;
        $errormsg = Config::get('constants.errormsg');
        $fill_array = array();
        $result = collect([]);
        $errorMsg = '';
        $valid = 1;
        $issubmitted = 0;
        $fill_array['select_type'] = 'B';
        $fill_array['search_text'] = 'Beneficiary ID';
        $fill_array['ben_id'] = '';
        if (isset($request->btnSubmit)) {
            
           try{
            if (!empty($request->ben_id)) {
                $fill_array['ben_id'] = $request->ben_id;
            }
            $issubmitted = 1;
            $rules = [
                'ben_id' => 'required|numeric'
            ];
            $attributes = array();
            $messages = array();
            $attributes['select_type'] = 'Search By Selection';
            $attributes['ben_id'] = 'Search By Text';
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
                $select_type = $request->select_type;
                $fill_array['select_type'] = $select_type;
                $ben_id = $request->ben_id;
                $where_cond='';
                if ($select_type == 'B') {
                    $condition['ben_id'] = $ben_id;
                    $where_cond=$where_cond.' A.beneficiary_id='.$ben_id;
                    $fill_array['search_text'] = 'Beneficiary ID';
                } else if ($select_type == 'A') {
                    $condition['application_id'] = $ben_id;
                    $fill_array['search_text'] = 'Application ID';
                    $where_cond=$where_cond.' A.application_id='.$ben_id;
                } 
                $query_pre = "select application_id,status from lb_scheme.ben_aadhar_details_can_update as A  where ".$where_cond."";
                $data_pre = DB::connection('pgsql_appread')->select($query_pre);
                if (!empty($data_pre) && count($data_pre)>0) {
                    $errorMsg = 'Already marked';
                    $is_processed= $data_pre[0]->status;
                    if ($is_processed==10) {
                        $errorMsg = 'Already marked';
                    } elseif ($is_processed==11) {
                        $errorMsg = 'Already marked and processed by Approver';
                    }
                }
                if (empty($errorMsg)) {
                    $query_reject = "select is_faulty,ben_fname as ben_name,beneficiary_id, 
                        mobile_no,ss_card_no,application_id,block_ulb_name,gp_ward_name,bank_code,bank_ifsc,rejected_cause 
                        from lb_scheme.ben_reject_details as A
                        where  ".$where_cond."";
                   
                    $data_reject = DB::connection('pgsql_appread')->select($query_reject);
                    if (!empty($data_reject) && count($data_reject) > 0) {
                        $errorMsg = 'Already Rejected';
                    }
                }
                if (empty($errorMsg)) {
                $query = "select '0' as is_faulty,'0' as status,A.application_id,A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name,mobile_no,B.block_ulb_name,B.gp_ward_name,B.village_town_city,B.house_premise_no,C.encoded_aadhar from lb_scheme.ben_personal_details as A LEFT JOIN lb_scheme.ben_contact_details as B 
                ON A.application_id=B.application_id LEFT JOIN lb_scheme.ben_aadhar_details as C
                ON A.application_id=C.application_id where ".$where_cond."
                UNION
                select '1' as is_faulty,'0' as status,A.application_id,A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name,mobile_no,B.block_ulb_name,B.gp_ward_name,B.village_town_city,B.house_premise_no,C.encoded_aadhar from lb_scheme.faulty_ben_personal_details as A LEFT JOIN lb_scheme.faulty_ben_contact_details as B 
                ON A.application_id=B.application_id LEFT JOIN lb_scheme.ben_aadhar_details as C
                ON A.application_id=C.application_id where  ".$where_cond."";
                $data = DB::connection('pgsql_appread')->select($query);
                if (empty($data)) {
                    $errorMsg = 'No data Found';
                }
                if (count($data) == 0) {
                    $errorMsg = 'No data Found';
                }  
                if (empty($errorMsg)) {
                    
                    $result = $data;

                } 
                //dd($result);            
               
               }
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }
            catch (\Exception $e) {
                //dd($e);
            }
        }
        else{
            $query = "select is_faulty,ben_name,beneficiary_id, 
            mobile_no,application_id,status, block_ulb_name,gp_ward_name,old_encoded_aadhar as encoded_aadhar
        from lb_scheme.ben_aadhar_details_can_update  where status IN (10,11)";
        $data = DB::connection('pgsql_appread')->select($query);
        $result = $data;
       // dd($result);
        }
        return view(
            'aadhaar-details-update/markhod',
            [
                'result'        => $result,
                'fill_array'        => $fill_array,
                'errorMsg'        => $errorMsg,
                'valid'        => $valid,
                'issubmitted'        => $issubmitted,
            ]
        );
    }catch (\Exception $e) {
        //dd($e);
    }
    }
    public function mark4edithodpost(Request $request)
    {
        $application_id = $request->application_id;
        if (empty($application_id)) {
            return redirect("/aadhaar-details-update-hod")->with('error', 'Application ID Not Found');
        }
        $application_id_list = explode('_',$request->application_id);
        $app_id=$application_id_list[0];
        $is_faulty=$application_id_list[1];
        if (!ctype_digit($app_id)) {
            return redirect("/aadhaar-details-update-hod")->with('error', 'Application ID Not Valid');
        }
        if (!in_array($is_faulty, array(0, 1))) {
            return redirect("/aadhaar-details-update-hod")->with('error', 'Parameter Not Valid');
        }

        try {
            $user_id = Auth::user()->id;
            $c_time = Carbon::now();
            $district_code='';
            $errormsg = Config::get('constants.errormsg');
            $getModelFunc = new getModelFunc();
            $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
            $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
            $personal_model = new DataSourceCommon;
            $personal_model->setTable('' . $TablePersonal);
            $personal_model_f = new DataSourceCommon;
            $personal_model_f->setTable('' . $TableFaultyPersonal);

            $TableAadhar= $getModelFunc->getTable($district_code, $this->source_type, 2);
            $aadhar_model = new DataSourceCommon;
            $aadhar_model->setTable('' . $TableAadhar);

            $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
            $TableContact_f = $getModelFunc->getTableFaulty($district_code, $this->source_type, 3);
            $contact_model = new DataSourceCommon;
            $contact_model->setTable('' . $TableContact);
            $contact_model_f = new DataSourceCommon;
            $contact_model_f->setTable('' . $TableContact_f);

            $TableBank = $getModelFunc->getTable($district_code, $this->source_type, 4);
            $TableBank_f = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);
            $bank_model = new DataSourceCommon;
            $bank_model->setTable('' . $TableBank);
            $bank_model_f = new DataSourceCommon;
            $bank_model_f->setTable('' . $TableBank_f);
            //dd($is_faulty);
            if ($is_faulty == 1) {
                $row = $personal_model_f->where('application_id', $app_id)->where('next_level_role_id', 0)->first();
                $row_contact = $contact_model_f->where('application_id', $app_id)->first();
                $row_aadhar = $aadhar_model->where('application_id', $app_id)->first();
                $row_bank = $bank_model_f->where('application_id', $app_id)->first();
            } else if ($is_faulty == 0) {
                $row = $personal_model->where('application_id', $app_id)->where('next_level_role_id', 0)->first();
                $row_contact = $contact_model->where('application_id', $app_id)->first();
                $row_aadhar = $aadhar_model->where('application_id', $app_id)->first();
                $row_bank = $bank_model->where('application_id', $app_id)->first();
            }
            
            if (empty($row)) {
                return redirect("/aadhaar-details-update-hod")->with('error', ' Application Id Not found in Db');
            }
           // dd($row_bank);
            $insert_arr = array();
            $insert_arr['status'] = 10;
            $insert_arr['mark_by'] = $user_id;
            $insert_arr['mark_at'] = $c_time;
            $insert_arr['application_id'] = $row->application_id;
            $insert_arr['beneficiary_id'] = $row->beneficiary_id;
            $insert_arr['ben_name'] = $row->ben_fname.' '.$row->ben_mname.' '.$row->ben_lname;
            $insert_arr['father_name'] = $row->father_fname.' '.$row->father_mname.' '.$row->father_lname;
            $insert_arr['created_by_dist_code'] = $row->created_by_dist_code;
            $insert_arr['created_by_local_body_code'] = $row->created_by_local_body_code;
            $insert_arr['is_faulty'] = $is_faulty;
            $insert_arr['created_at'] = $row->created_at;
            $insert_arr['created_by'] = $row->created_by;
            $insert_arr['mobile_no'] = $row->mobile_no;
            $insert_arr['ss_card_no'] = $row->ss_card_no;
            $insert_arr['caste'] = $row->caste;
            $insert_arr['dob'] = $row->dob;
            $insert_arr['ds_phase'] = $row->ds_phase;
            if(!empty($row_aadhar)){
            $insert_arr['old_encoded_aadhar'] = $row_aadhar->encoded_aadhar;
            $insert_arr['aadhar_hash'] = $row_aadhar->aadhar_hash;
            }
            if(!empty($row_contact)){
            $insert_arr['police_station'] = $row_contact->police_station;
            $insert_arr['block_ulb_name'] = $row_contact->block_ulb_name;
            $insert_arr['block_ulb_code'] = $row_contact->block_ulb_code;
            $insert_arr['gp_ward_name'] = $row_contact->gp_ward_name;
            $insert_arr['gp_ward_code'] = $row_contact->gp_ward_code;
            $insert_arr['village_town_city'] = $row_contact->village_town_city;
            $insert_arr['house_premise_no'] = $row_contact->house_premise_no;
            $insert_arr['pincode'] = $row_contact->pincode;
            }
            if(!empty($row_bank)){

            $insert_arr['bank_ifsc'] = $row_bank->bank_ifsc;
            $insert_arr['bank_code'] = $row_bank->bank_code;
            }
            $mark_model = new DataSourceCommon;
            $Table = 'lb_scheme.ben_aadhar_details_can_update';
            $mark_model->setTable('' . $Table);
            $mark_model->setKeyName('application_id');
            $i_status = $mark_model->insert($insert_arr);
            if($i_status){
                $return_msg = "Beneficiary with Application Id:" . $row->application_id . " has been marked for edit";
                return redirect("/aadhaar-details-update-hod")->with('success', $return_msg);
            }
            else{
                $return_text = $errormsg['roolback'];
               return redirect('aadhaar-details-update-hod')->with('error', $return_text); 
            }
        }catch (\Exception $e) {
            dd($e);
            $return_text = $errormsg['roolback'];
            return redirect('aadhaar-details-update-hod')->with('error', $return_text);
        }
        
    }
    public function ListView(Request $request)
    {
        $district_list_obj = District::get();
        $verifier_type = 'District';
        $is_rural = NULL;
        $created_by_local_body_code = NULL;
        $c_time = date('Y-m-d H:i:s', time());
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $duty_obj = Configduty::where('user_id', $user_id)->where('scheme_id', $this->scheme_id)->first();
        if (empty($duty_obj)) {
          return redirect("/")->with('danger', 'Not Allowed');
        }
        $district_list_obj = District::get();
        //dd($duty_obj->mapping_level);
        $district_code = $duty_obj->district_code;
        $urban_bodys = collect([]);
        $gps = collect([]);
        if (request()->ajax()) {
            $limit = $request->input('length');
            $offset = $request->input('start');
            $query = DB::table('lb_scheme.ben_aadhar_details_can_update')->whereIn('status',array(10,11))->where('created_by_dist_code', $district_code);
            $serachvalue = $request->search['value'];
          if (empty($serachvalue)) {
            $totalRecords = $query->count();
            $data = $query->orderBy('application_id', 'ASC')->offset($offset)->limit($limit)->get();
           
            $filterRecords = count($data);
          } else {
            if (is_numeric($serachvalue)) {
              $query = $query->where(function ($query1) use ($serachvalue) {
                $query1->where('application_id', $serachvalue)
                  ->orWhere('application_id', $serachvalue)->orWhere('bank_code', $serachvalue);
              });
              $totalRecords = $query->count();
              $data = $query->orderBy('application_id', 'ASC')->offset($offset)->limit($limit)->get();
              
            } else {
              $query = $query->where(function ($query1) use ($serachvalue) {
                $query1->where('ben_name', 'like', $serachvalue . '%')
                  ->orWhere('block_ulb_name', 'like', $serachvalue . '%')
                  ->orWhere('gp_ward_name', 'like', $serachvalue . '%')
                  ->orWhere('bank_ifsc', 'like', $serachvalue . '%');
              });
              $totalRecords = $query->count();
             
              $data = $query->orderBy('dob', 'ASC')->offset($offset)->limit($limit)->get();
              
            }
            $filterRecords = count($data);
          }
          return datatables()->of($data)->setTotalRecords($totalRecords)
            ->setFilteredRecords($filterRecords)
            ->skipPaging()
            ->addColumn('application_id', function ($data) {
  
              $app_id = $data->application_id;
  
              return $app_id;
            })->addColumn('view', function ($data) {
                if ($data->status==11){
                    $action ='Already Edited';   
                }
                else if ($data->status==10){
                $action = '<a href="changeAadhar?application_id=' . $data->application_id . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Update</a>';
                }
                return $action;
                         
            })->addColumn('name', function ($data) {
                if ($data->status==11 && !empty($data->new_ben_name)) {
                  $ben_name = $data->new_ben_name;
                } else {
                  $ben_name = $data->ben_name;
                }
                return $ben_name;
              })->addColumn('dob', function ($data) {
                if ($data->status==11 && !empty($data->new_dob)) {
                  $dob = $data->new_dob;
                } else {
                  $dob = $data->dob;
                }
                return $dob;
              })->addColumn('aadhar_no', function ($data) {
                if ($data->status==11 && !empty($data->new_encoded_aadhar)) {
                $aadhar_no = Crypt::decryptString($data->new_encoded_aadhar);

                } else {
                    if(!empty($data->old_encoded_aadhar))
                    $aadhar_no = Crypt::decryptString($data->old_encoded_aadhar);
                    else{
                        $aadhar_no ='';  
                    }
                }
                return $aadhar_no;
              })
            ->addColumn('mobile_no', function ($data) {
              if (!empty($data->mobile_no)) {
                $ben_mobile_no = trim($data->mobile_no);
              } else {
                $ben_mobile_no = '';
              }
              return $ben_mobile_no;
            })
            ->rawColumns(['view'])
            ->make(true);
        }
        
      //dd($district_list_obj);
        return view(
          'aadhaar-details-update.linelisting',
          [
            'designation_id' => $designation_id,
            'verifier_type' => $verifier_type,
            'created_by_local_body_code' => $created_by_local_body_code,
            'is_rural' => $is_rural,
            'gps' => $gps,
            'urban_bodys' => $urban_bodys,
            'gps' => $gps,
            'district_code' => $district_code,
            'district_list_obj' => $district_list_obj
          ]
        );
      
    }
    public function index(Request $request)
    {

        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if (!in_array($designation_id, ['Approver', 'Delegated Approver'])) {
            return redirect("/")->with('error', 'User Disabled. ');

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

            if (!empty($request->ben_id)) {
                $fill_array['ben_id'] = $request->ben_id;
            }
            $issubmitted = 1;
            $rules = [
                'ben_id' => 'required|numeric'
            ];
            $attributes = array();
            $messages = array();
            $attributes['select_type'] = 'Search By Selection';
            $attributes['ben_id'] = 'Search By Text';
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
                $select_type = $request->select_type;
                $fill_array['select_type'] = $select_type;
                $ben_id = $request->ben_id;
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

                $reject_found = 0;

                $query = "select is_faulty,ben_name,beneficiary_id, 
                    mobile_no,ss_card_no,application_id,status, block_ulb_name,gp_ward_name,bank_code,bank_ifsc 
                from lb_scheme.ben_aadhar_details_can_update 
                where  created_by_dist_code=" . $district_code . " ";
                if ($select_type == 'B') {
                    $query .= " and beneficiary_id=" . $ben_id;
                } else if ($select_type == 'A') {
                    $query .= " and application_id=" . $ben_id;
                }

                $data = DB::connection('pgsql_appread')->select($query);
                $return_arr = array();
                if (empty($data)) {
                    $errorMsg = 'Not Eligible for Aadhaar update';
                }
                if (count($data) == 0) {
                    $errorMsg = 'Not Eligible for Aadhaar update';
                } else {
                    // dd('ok');
                    $query1 = "select is_faulty,ben_fname as ben_name,beneficiary_id, 
                        mobile_no,ss_card_no,application_id,block_ulb_name,gp_ward_name,bank_code,bank_ifsc,rejected_cause 
                        from lb_scheme.ben_reject_details 
                        where  created_by_dist_code=" . $district_code . " ";
                    if ($select_type == 'B') {
                        $query1 .= " and beneficiary_id=" . $ben_id;
                    } else if ($select_type == 'A') {
                        $query1 .= " and application_id=" . $ben_id;
                    }
                    $data1 = DB::connection('pgsql_appread')->select($query1);
                    if (!empty($data1) && count($data1) > 0) {
                        $reject_found = 1;
                    }
                }
                if ($reject_found == 1) {
                    $data = $data1;
                }
                if (empty($errorMsg)) {
                    //$return_arr = array();
                    $i = 0;

                    foreach ($data as $my_row) {
                        $return_arr[$i]['msg'] = 'NA';
                        if ($reject_found == 1) {
                            $return_arr[$i]['can_update_edit'] = 0;
                            if (!empty($my_row->rejected_cause)) {
                                $reason_arr = RejectRevertReason::where('id', $my_row->rejected_cause)->first();
                                $return_arr[$i]['msg'] = 'Rejected for the reason ' . $reason_arr->reason;
                            } else {
                                $return_arr[$i]['msg'] = 'Rejected';
                            }
                        } else {
                            if ($my_row->status == 1) {
                                $return_arr[$i]['can_update_edit'] = 0;
                                $return_arr[$i]['msg'] = 'Already Edited';
                            } else {
                                $return_arr[$i]['can_update_edit'] = 1;
                                $return_arr[$i]['msg'] = 'NA';
                            }
                        }
                        $return_arr[$i]['is_faulty'] = $my_row->is_faulty;
                        $return_arr[$i]['beneficiary_id'] = $my_row->beneficiary_id;
                        $return_arr[$i]['ben_name'] = $my_row->ben_name;
                        $return_arr[$i]['ss_card_no'] = $my_row->ss_card_no;
                        $return_arr[$i]['application_id'] = $my_row->application_id;
                        $return_arr[$i]['mobile_no'] = $my_row->mobile_no;
                        $i++;
                    }
                }

                $result = $return_arr;
                //dd($result);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        return view(
            'aadhaar-details-update/index',
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
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if (!in_array($designation_id, ['Approver', 'Delegated Approver'])) {
            return redirect("/")->with('error', 'User Disabled. ');

        }
        $scheme_id = $this->scheme_id;
        $doc_id = 6;
        $doc_id_1 = 118;
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
        $application_id = $request->application_id;
        //dd($is_faulty);
        if (empty($application_id)) {
            return redirect("/aadhaar-details-update")->with('error', 'Application ID Not Found');
        }
        if (!ctype_digit($application_id)) {
            return redirect("/aadhaar-details-update")->with('error', 'Application ID Not Valid');
        }
       

        $getModelFunc = new getModelFunc();
        $Tableaadhaar = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $TableEnclosermain = $getModelFunc->getTable($district_code, $this->source_type, 6);
        $TableEncloserfaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6);
        $aadhaar_model = new DataSourceCommon;
        $aadhaar_model->setTable('' . $Tableaadhaar);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('lb_scheme.ben_aadhar_details_can_update');
        $encolser_model = new DataSourceCommon;
        $encolser_model->setConnection('pgsql_encread');
        $condition = array();
        //$condition['next_level_role_id'] = 0;
        $condition['created_by_dist_code'] = $district_code;
        $row = $personal_model->where('status', 10)->where('application_id', $application_id)->where($condition)->first();
       // dd();
        if (empty($row)) {
            return redirect("/aadhaar-details-update-list-approver")->with('error', ' Application Id Not found in Db');
        }
        if ($row->is_faulty == 1) {
            $encolser_model->setTable('' . $TableEncloserfaulty);
        } else if ($row->is_faulty == 0) {
            $encolser_model->setTable('' . $TableEnclosermain);
        }
        
        $query_aadhaar = $aadhaar_model->where('application_id',  $row->application_id)->where($condition);
        $row_aadhaar = $query_aadhaar->first();
        $row->aadhaar_no_decode = '';
        if (!empty($row_aadhaar)) {
            if (!empty($row_aadhaar->encoded_aadhar)) {
                $aadhar_no = Crypt::decryptString($row_aadhaar->encoded_aadhar);
                $row->aadhaar_no_decode = $aadhar_no;
            }
        }
        $row->is_faulty = $row->is_faulty;
        $doc_aadhaar_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id)->first();
        $doc_aadhaar1_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id_1)->first();
        $phaseArr = DsPhase::where('id', $row->ds_phase)->first();
        //dd($row->ds_phase);
        $mydate = $phaseArr->base_dob;
        $max_date = strtotime("-25 year", strtotime($mydate));
        $max_date = date("Y-m-d", $max_date);
        $min_date = strtotime("-60 year", strtotime($mydate));
        $min_date = date("Y-m-d", $min_date);
        $base_dob_chk_date = $mydate;
        $max_dob = $max_date;
        $min_dob = $min_date;
        $casteEncloserCount = $encolser_model->where('application_id', $row->application_id)->where($condition)->where('document_type', $doc_id)->count('application_id');
        return view(
            'aadhaar-details-update/change',
            [
                'application_id'        => $application_id,
                'casteEncloserCount'        => $casteEncloserCount,
                'row'        => $row,
                'doc_aadhaar_arr'        => $doc_aadhaar_arr,
                'doc_aadhaar1_arr'        => $doc_aadhaar1_arr,
                'max_dob'        => $max_dob,
                'min_dob'        => $min_dob
            ]
        );
    }
    public function changePost(Request $request)
    {
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        if (!in_array($designation_id, ['Approver', 'Delegated Approver'])) {
            return redirect("/")->with('error', 'User Disabled. ');

        }
        
        $scheme_id = $this->scheme_id;
        $doc_id = 6;
        $doc_id1 = 118;
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
        $application_id = $request->application_id;
        $is_faulty = $request->is_faulty;
        if (empty($application_id)) {
            return redirect("/aadhaar-details-update-list-approver")->with('error', 'Application ID Not Found');
        }
        if (!ctype_digit($application_id)) {
            return redirect("/aadhaar-details-update-list-approver")->with('error', 'Application ID Not Valid');
        }
        if (!in_array($is_faulty, array(0, 1))) {
            return redirect("/aadhaar-details-update-list-approver")->with('error', 'Parameter Not Valid');
        }

        $getModelFunc = new getModelFunc();
        $Tableaadhaar = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $TableEnclosermain = $getModelFunc->getTable($district_code, $this->source_type, 6);
        $TableEncloserfaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 6);

        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        $personal_model->setConnection('pgsql_appwrite');

        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaultyPersonal);
        $personal_model_f->setConnection('pgsql_appwrite');

        $main_model = new DataSourceCommon;
        $main_model->setTable('lb_scheme.ben_aadhar_details_can_update');
        $main_model->setConnection('pgsql_appwrite');

        $encolser_model = new DataSourceCommon;
        $accept_reject_model = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
        $accept_reject_model->setTable('' . $Table);
        $accept_reject_model->setConnection('pgsql_appwrite');

        

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        //$condition['next_level_role_id'] = 0;
        if ($is_faulty == 1) {
            $common_model = $personal_model_f;
            $TableEncMain = $TableEncloserfaulty;
            $query = $main_model->where('application_id', $application_id)->where($condition)->where('status', 10);
            $row = $query->first();
            $encolser_model->setTable('' . $TableEncloserfaulty);
            $encolser_model->setConnection('pgsql_encwrite');
        } else if ($is_faulty == 0) {
            $common_model = $personal_model;
            $TableEncMain = $TableEnclosermain;
            $query = $main_model->where('application_id', $application_id)->where($condition)->where('status', 10);
            $row = $query->first();
            $encolser_model->setTable('' . $TableEnclosermain);
            $encolser_model->setConnection('pgsql_encwrite');
        }
        if (empty($row)) {
            return redirect("/aadhaar-details-update")->with('error', ' Application Id Not found in Db');
        }
        $old_name=trim($request->old_name);
        $new_name=trim($request->first_name);
        $old_dob=$request->old_dob;
        $new_dob=$request->dob;
        $old_aadhar=trim($request->old_aadhar);
        $new_aadhar=trim($request->aadhar_no);
        $name_change=0;
        $dob_change=0;
        $aadhar_change=0;
        $doc_aadhar_arr = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id)->first()->toArray();
        $doc_aadhar_arr1 = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->where("id", $doc_id1)->first()->toArray();
        $phaseArr = DsPhase::where('id', $row->ds_phase)->first();
        $mydate = $phaseArr->base_dob;
        $max_date = strtotime("-25 year", strtotime($mydate));
        $max_date = date("Y-m-d", $max_date);
        $min_date = strtotime("-60 year", strtotime($mydate));
        $min_date = date("Y-m-d", $min_date);
        $base_dob_chk_date = $mydate;
        $max_dob = $max_date;
        $min_dob = $min_date;
        $rules = [];
        $attributes = array();
        $messages = array();
        if(!empty($new_name)){
         if($old_name!=$new_name){
            $name_change=1;
         }
        }
        if(!empty($new_dob)){
            if($old_dob!=$new_dob){
               $dob_change=1;
            }
        }
        if(!empty($new_aadhar)){
            if($old_aadhar!=$new_aadhar){
               $aadhar_change=1;
            }
        }
        if($name_change==1){
        $attributes['first_name'] = 'New Name.';
        $rules['first_name'] = 'required|string|max:200';
        }
        if($dob_change==1){
            $attributes['dob'] = 'New DOB.';
            $rules['dob'] = 'required|date|before_or_equal:' . $max_date . '|after_or_equal:' . $min_date;
        }
        if($aadhar_change==1){
            $attributes['aadhar_no'] = 'New Aadhar Number';
            $rules['aadhar_no'] = 'required|numeric|digits:12';
        }
        $rules['doc_6'] = 'required|mimes:' . $doc_aadhar_arr['doc_type'] . '|max:' . $doc_aadhar_arr['doc_size_kb'] . ',';
        $messages['doc_6.max'] = "The file uploaded for " . $doc_aadhar_arr['doc_name'] . " size must be less than " . $doc_aadhar_arr['doc_size_kb'] . " KB";
        $messages['doc_6.mimes'] = "The file uploaded for " . $doc_aadhar_arr['doc_name'] . " must be of type " . $doc_aadhar_arr['doc_type'];
        $messages['doc_6.required'] = "Document for " . $doc_aadhar_arr['doc_name'] . " must be uploaded";
        if($aadhar_change==1){
        $rules['doc_118'] = 'required|mimes:' . $doc_aadhar_arr1['doc_type'] . '|max:' . $doc_aadhar_arr1['doc_size_kb'] . ',';
        $messages['doc_118.max'] = "The file uploaded for " . $doc_aadhar_arr1['doc_name'] . " size must be less than " . $doc_aadhar_arr1['doc_size_kb'] . " KB";
        $messages['doc_118.mimes'] = "The file uploaded for " . $doc_aadhar_arr1['doc_name'] . " must be of type " . $doc_aadhar_arr1['doc_type'];
        $messages['doc_118.required'] = "Document for " . $doc_aadhar_arr1['doc_name'] . " must be uploaded";
        }
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if (!$validator->passes()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        } else {
            $url='changeAadhar?application_id=' . $application_id . '&is_faulty=' . $is_faulty;
            //dump($name_change); dump($dob_change); dd($aadhar_change);
            if($name_change==0 && $dob_change==0 && $aadhar_change==0){
                $return_text = 'You have to change at least one information';
                return redirect($url)->with('error', $return_text);
            }
            $errormsg = Config::get('constants.errormsg');
            if($aadhar_change==1){
            $post_aadhar_no = $request->aadhar_no;
            if ($this->isAadharValid($post_aadhar_no) == false) {
                $return_text = 'Aadhaar Number Invalid';
                return redirect($url)->with('error', $return_text);
            }
            $query_check = "select application_id from " . $Tableaadhaar . " where application_id!=" . $row->application_id . " and aadhar_hash='" . md5($post_aadhar_no) . "'";
            $check_data = DB::connection('pgsql_appread')->select($query_check);
            if (!empty($check_data)) {
                if (!empty($check_data[0]->application_id)) {
                    $return_text = 'Aadhaar Number already tagged with application_id:' . $check_data[0]->application_id . '. Please try different.';
                    return redirect($url)->with('error', $return_text);
                }
            }
            }
            if($dob_change==1){
            $diff = Carbon::parse($request->dob)->diffInYears($phaseArr->base_dob);
            if ($diff < 25 || $diff > 60) {
                $return_text = 'Date of Birth Invalid';
                return redirect($url)->with('error', $return_text);
            }
           }
            $created_at_obj1 = explode('-', $row->created_at);
            $year_obj  = $created_at_obj1[0];
            $month1 = $created_at_obj1[1];
            if(in_array($month1,array('01','02','03'))){
                $year_obj2= $year_obj-1;
            }
            else{
                $year_obj2=  $year_obj; 
            }
            if($year_obj2==date('Y')){
                $payment_year=NULL;

            }
            else{
                $payment_year= $year_obj2.'-' .($year_obj2+1);
            }
           
            $payment_year=NULL;
            $schemaname = $getModelFunc->getSchemaDetails($payment_year);
            $modelfailedpayments = new DataSourceCommon;
            $modelfailedpayments->setConnection('pgsql_payment');
            $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
            //$check_payment_exists = $modelfailedpayments->where('ben_status', 0)->where('ben_id', $beneficiary_id)->count('ben_id');
            $check_payment_exists = 0;
            $pension_details_aadhar = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 2, 1);
            $pension_details_aadhar->setTable('' . $Table);
            $pension_details_aadhar->setKeyName('application_id');
            $encloser_model = new DataSourceCommon;
            $encloser_model->setTable('' . $TableEncMain);
            $encloser_model->setConnection('pgsql_encwrite');

            $encloser_model1 = new DataSourceCommon;
            $encloser_model1->setTable('' . $TableEncMain);
            $encloser_model1->setConnection('pgsql_encwrite');

            $aadhar_exists = $pension_details_aadhar->where('application_id', $row->application_id)->count('application_id');
            $aadhar_doc_exists = $encloser_model->where('document_type', $doc_id)->where('application_id', $row->application_id)->count('application_id');
            $aadhar_doc_exists1 = $encloser_model1->where('document_type', $doc_id1)->where('application_id', $row->application_id)->count('application_id');
            $model_payment = new DataSourceCommon;
            $model_payment->setConnection('pgsql_payment');
            $model_payment->setTable('' . $schemaname . '.ben_payment_details');
            $payemt_exists=$model_payment->where('application_id', $request->application_id)->first();
            try {
                
                $logmessage = "";
                $fileLocation = 'UpdateaadharLog/log.txt';
                $c_time = date('Y-m-d H:i:s', time());
                DB::connection('pgsql_appwrite')->beginTransaction();
                DB::connection('pgsql_encwrite')->beginTransaction();
                DB::connection('pgsql_payment')->beginTransaction();
                $error_found=0;
                if ($aadhar_exists) {
                    try {
                        $arch_status_main = DB::connection('pgsql_appwrite')->statement("INSERT INTO  lb_scheme.ben_aadhar_details_arc(
                    application_id, beneficiary_id, encoded_aadhar, created_by_level, 
created_at, updated_at, deleted_at, created_by, 
ip_address, created_by_dist_code, created_by_local_body_code, 
is_dup, aadhar_hash,  dup_modification,updated_by)
                    select application_id, beneficiary_id, encoded_aadhar, created_by_level, 
created_at, updated_at, deleted_at, created_by, 
ip_address, created_by_dist_code, created_by_local_body_code, 
is_dup, aadhar_hash,  dup_modification,updated_by
                    from  lb_scheme.ben_aadhar_details   where application_id=" . $row->application_id);
                    } catch (\Exception $e) {
                        dd($e);
                        $error_found=1;
                        $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                        Storage::append($fileLocation, $e);
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        $return_text = $errormsg['roolback'];
                        return redirect($url)->with('error', $return_text);
                    }
                }
                if ($aadhar_doc_exists) {
                    try {
                        $encolser_arch_status_main = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_arch(
                    application_id,  document_type, attched_document, created_by_level, 
                    created_at, updated_at, deleted_at, created_by, ip_address, 
                    document_extension, document_mime_type, created_by_dist_code, 
                    created_by_local_body_code,action_by,action_ip_address,action_type)
                    select application_id,  document_type, attched_document, created_by_level, created_at, 
                    updated_at, deleted_at, created_by, ip_address, document_extension, 
                    document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                    from  " . $TableEncMain . " as A  where A.document_type=" . $doc_id . " and A.application_id=" . $row->application_id);
                    $del=$encloser_model->where('document_type',$doc_id)->where('application_id',$row->application_id)->delete();
                    } catch (\Exception $e) {
                        dd($e);
                        $error_found=1;
                        $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                        Storage::append($fileLocation, $e);
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        $return_text = $errormsg['roolback'];
                        return redirect($url)->with('error', $return_text);
                    }
                }
                if ($aadhar_doc_exists1) {
                    try {
                        $encolser_arch_status_main = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_arch(
                    application_id,  document_type, attched_document, created_by_level, 
                    created_at, updated_at, deleted_at, created_by, ip_address, 
                    document_extension, document_mime_type, created_by_dist_code, 
                    created_by_local_body_code,action_by,action_ip_address,action_type)
                    select application_id,  document_type, attched_document, created_by_level, created_at, 
                    updated_at, deleted_at, created_by, ip_address, document_extension, 
                    document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                    from  " . $TableEncMain . " as A  where A.document_type=" . $doc_id1 . " and A.application_id=" . $row->application_id);
                    $del=$encloser_model->where('document_type',$doc_id1)->where('application_id',$row->application_id)->delete();
                    } catch (\Exception $e) {
                        dd($e);
                        $error_found=1;
                        $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                        Storage::append($fileLocation, $e);
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        $return_text = $errormsg['roolback'];
                        return redirect($url)->with('error', $return_text);
                    }
                }
                try {
                    
                    if ($request->hasFile('doc_' . $doc_id)) {
                        $image_file1 = $request->file('doc_' . $doc_id);
                        $img_data1 = file_get_contents($image_file1);
                        $extension = $image_file1->getClientOriginalExtension();
                        $mime_type = $image_file1->getMimeType();
                        //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                        $base64 = base64_encode($img_data1);
                        $encolser_enquiry = array();
                        $encolser_enquiry['attched_document'] = $base64;
                        $encolser_enquiry['document_extension'] = $extension;
                        $encolser_enquiry['document_mime_type'] = $mime_type;
                        if ($aadhar_doc_exists) {
                            // $encolser_enquiry['updated_by'] = $user_id;
                            $encolser_enquiry['updated_at'] = $c_time;
                            $encolser_del_status = $encloser_model->where('document_type', $doc_id)->where('application_id', $row->application_id)->delete();
                            $encolser_enquiry['created_by_level'] = $mapping_level;
                            $encolser_enquiry['created_by'] = $user_id;
                            $encolser_enquiry['ip_address'] = $request->ip();
                            $encolser_enquiry['created_by_dist_code'] = $row->created_by_dist_code;
                            $encolser_enquiry['created_by_local_body_code'] = $row->created_by_local_body_code;
                            $encolser_enquiry['document_type'] = $doc_id;
                            $encolser_enquiry['created_at'] = $c_time;
                            $encolser_enquiry['application_id'] = $row->application_id;
                            $encolser_enquiry['action_by'] = Auth::user()->id;
                            $encolser_enquiry['action_ip_address'] = request()->ip();
                            $encolser_enquiry['action_type'] = class_basename(request()->route()->getAction()['controller']);
                            $encolser_entry_status = $encloser_model->insert($encolser_enquiry);
                        } else {
                            $encolser_enquiry['created_by_level'] = $mapping_level;
                            $encolser_enquiry['created_by'] = $user_id;
                            $encolser_enquiry['ip_address'] = $request->ip();
                            $encolser_enquiry['created_by_dist_code'] = $row->created_by_dist_code;
                            $encolser_enquiry['created_by_local_body_code'] = $row->created_by_local_body_code;
                            $encolser_enquiry['document_type'] = $doc_id;
                            $encolser_enquiry['created_at'] = $c_time;
                            $encolser_enquiry['action_by'] = Auth::user()->id;
                            $encolser_enquiry['action_ip_address'] = request()->ip();
                            $encolser_enquiry['action_type'] = class_basename(request()->route()->getAction()['controller']);
                            $encolser_enquiry['application_id'] = $row->application_id;
                            $encolser_entry_status = $encloser_model->insert($encolser_enquiry);
                        }
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $error_found=1;
                    $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                    Storage::append($fileLocation, $e);
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect($url)->with('error', $return_text);
                }
                try {
                    if ($request->hasFile('doc_' . $doc_id1)) {
                        $image_file2 = $request->file('doc_' . $doc_id1);
                        $img_data2 = file_get_contents($image_file2);
                        $extension = $image_file2->getClientOriginalExtension();
                        $mime_type = $image_file2->getMimeType();
                        //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                        $base64 = base64_encode($img_data2);
                        $encolser_enquiry1 = array();
                        $encolser_enquiry1['created_at'] = $c_time;
                        $encolser_enquiry1['created_by_level'] = $mapping_level;
                        $encolser_enquiry1['created_by'] = $user_id;
                        $encolser_enquiry1['ip_address'] = $request->ip();
                        $encolser_enquiry1['created_by_dist_code'] = $row->created_by_dist_code;
                        $encolser_enquiry1['created_by_local_body_code'] = $row->created_by_local_body_code;
                        $encolser_enquiry1['document_type'] = $doc_id1;
                        $encolser_enquiry1['attched_document'] = $base64;
                        $encolser_enquiry1['document_extension'] = $extension;
                        $encolser_enquiry1['document_mime_type'] = $mime_type;
                        $encolser_enquiry1['application_id'] = $row->application_id;
                        $encolser_enquiry1['action_by'] = Auth::user()->id;
                        $encolser_enquiry1['action_ip_address'] = request()->ip();
                        $encolser_enquiry1['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        $encolser_entry_status2 = $encloser_model1->insert($encolser_enquiry1);
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $error_found=1;
                    $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                    Storage::append($fileLocation, $e);
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect($url)->with('error', $return_text);
                }
                try {
                    $accept_reject_model->op_type = 'AC';
                    $accept_reject_model->created_at = $c_time;
                    $accept_reject_model->application_id = $row->application_id;
                    $accept_reject_model->designation_id = $designation_id;
                    $accept_reject_model->scheme_id = $scheme_id;
                    $accept_reject_model->user_id = $user_id;
                    $accept_reject_model->created_by = $user_id;
                    $accept_reject_model->created_by_level = $mapping_level;
                    $accept_reject_model->created_by_dist_code = $district_code;
                    $accept_reject_model->ip_address = request()->ip();
                    $is_saved = $accept_reject_model->save();
                } catch (\Exception $e) {
                    dd($e);
                    $error_found=1;
                    $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                    Storage::append($fileLocation, $e);
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect($url)->with('error', $return_text);
                }
                $main_arr = array();
                if($name_change==1){
                    $main_arr['ben_fname']=$new_name;
                }
                if($dob_change==1){
                    $main_arr['dob']    = $new_dob;
                }
                if($aadhar_change==1){
                $main_arr['aadhar_no']    = '********' . substr($post_aadhar_no, -4);
                $main_arr['action_by'] = Auth::user()->id;
                $main_arr['action_ip_address'] = request()->ip();
                $main_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                }
                try {
                    $is_saved_1 = $common_model->where('created_by_dist_code', $district_code)->where('application_id', $row->application_id)->update($main_arr);
                } catch (\Exception $e) {
                    dd($e);
                    $error_found=1;
                    $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                    Storage::append($fileLocation, $e);
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text =  $errormsg['roolback'];
                    return redirect($url)->with('error', $return_text);
                }
                if($aadhar_change==1){
                        $update_aadhar_arr = array();
                        $update_aadhar_arr['is_dup'] = NULL;
                        $update_aadhar_arr['dup_modification'] = NULL;
                        $update_aadhar_arr['encoded_aadhar'] = Crypt::encryptString($post_aadhar_no);
                        $update_aadhar_arr['aadhar_hash'] = md5($post_aadhar_no);

                        try {
                            if ($aadhar_exists) {
                                $update_aadhar_arr['updated_at'] = $c_time;
                                $update_aadhar_arr['updated_by'] = $user_id;
                                $update_aadhar_arr['action_by'] = Auth::user()->id;
                                $update_aadhar_arr['action_ip_address'] = request()->ip();
                                $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                                $a_status = $pension_details_aadhar->where('created_by_dist_code', $district_code)->where('application_id', $row->application_id)->update($update_aadhar_arr);
                            } else {
                                $update_aadhar_arr['application_id'] = $row->application_id;
                                $update_aadhar_arr['created_at'] = $c_time;
                                $update_aadhar_arr['created_by_level'] = $mapping_level;
                                $update_aadhar_arr['created_by'] = $user_id;
                                $update_aadhar_arr['ip_address'] = $request->ip();
                                $update_aadhar_arr['created_by_dist_code'] = $row->created_by_dist_code;
                                $update_aadhar_arr['created_by_local_body_code'] = $row->created_by_local_body_code;
                                $a_status = $pension_details_aadhar->insert($update_aadhar_arr);
                            }
                        } catch (\Exception $e) {
                            dd($e);
                            $error_found=1;
                            $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                            Storage::append($fileLocation, $e);
                            DB::connection('pgsql_appwrite')->rollBack();
                            DB::connection('pgsql_encwrite')->rollBack();
                            DB::connection('pgsql_payment')->rollBack();
                            $return_text = 'Duplicate Aadhaar No.';
                            return redirect($url)->with('error', $return_text);
                        }
               }
                if ($check_payment_exists == 1) {
                    try {
                        $payment_status = DB::connection('pgsql_payment')->statement("update " . $schemaname . ".ben_payment_details   set ben_status=1 
                        where   ben_status=0 and application_id=" . $row->application_id . ")");
                    } catch (\Exception $e) {
                        dd($e);
                        $error_found=1;
                        $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                        Storage::append($fileLocation, $e);
                        DB::connection('pgsql_appwrite')->rollBack();
                        DB::connection('pgsql_encwrite')->rollBack();
                        DB::connection('pgsql_payment')->rollBack();
                        $return_text =$errormsg['roolback'];
                        return redirect($url)->with('error', $return_text);
                    }
                }
                try {
                    
                  if(empty($payemt_exists)){
                   
                    if($name_change==1){
                        $ben_fullname=$new_name;
                    }
                    else{
                        $ben_fullname=$old_name;
                    }
                    
                    $created_at_obj = explode('-', $row->created_at);
                    $month = $created_at_obj[1];
                    $day   = $created_at_obj[2];
                    $year  = substr($created_at_obj[0],2,2);
                    $start_yymm=$year.$month;
                    if($start_yymm<2109){
                      $start_yymm=2109;
                    }
                    $end_yymm = Carbon::parse($new_dob)->addYears(60);
                    $end_yymm_obj = explode('-', $end_yymm);
                    $end_month = $end_yymm_obj[1];
                    $end_day   = $end_yymm_obj[2];
                    $end_year  = substr($end_yymm_obj[0],2,2);
                    $end_yymm=$end_year.$end_month;
                    $payment_insert=array();
                    $transaction_insert = array();
                    
                    $transaction_insert['dist_code']  = $row->created_by_dist_code;
                    $transaction_insert['ben_id']  = $row->beneficiary_id;
                    $transaction_insert['rural_urban_id']  = $row->rural_urban_id;
                    $transaction_insert['block_ulb_code']  = $row->block_ulb_code;
                    $transaction_insert['gp_ward_code']  = $row->gp_ward_code;
                    $transaction_insert['application_id']  = $row->application_id;
                    $transaction_insert['local_body_code']  = $row->created_by_local_body_code;
                    $transaction_insert['created_at']  = $row->created_at;
                    $transaction_insert['apr_lot_type']  = 'R';
                    $transaction_insert['may_lot_type']  = 'R';
                    $transaction_insert['jun_lot_type']  = 'R';
                    $transaction_insert['jul_lot_type']  = 'R';
                    $transaction_insert['aug_lot_type']  = 'R';
                    $transaction_insert['sep_lot_type']  = 'R';
                    $transaction_insert['oct_lot_type']  = 'R';
                    $transaction_insert['nov_lot_type']  = 'R';
                    $transaction_insert['dec_lot_type']  = 'R';
                    $transaction_insert['jan_lot_type']  ='R';
                    $transaction_insert['feb_lot_type']  = 'R';
                    $transaction_insert['mar_lot_type']  = 'R';

                    $payment_insert['dist_code']  = $row->created_by_dist_code;
                    $payment_insert['ben_id']  = $row->beneficiary_id;
                    $payment_insert['last_accno']  = trim($row->bank_code);
                    $payment_insert['last_ifsc']  = trim($row->bank_ifsc);
                    $payment_insert['ben_status']  = 1;
                    $payment_insert['ben_name']  = $ben_fullname;
                    $payment_insert['caste']  = substr($row->caste,0,2);
                    $payment_insert['created_at']  = $row->created_at;
                    $payment_insert['local_body_code']  = $row->created_by_local_body_code;
                    $payment_insert['ss_card_no']  = $row->ss_card_no;
                    $payment_insert['mobile_no']  = $row->mobile_no;
                    $payment_insert['application_id']  = $row->application_id;
                    $payment_insert['start_yymm']  = $start_yymm;
                    $payment_insert['end_yymm']  = $end_yymm;
                    $payment_insert['rural_urban_id']  = $row->rural_urban_id;
                    $payment_insert['block_ulb_code']  = $row->block_ulb_code;
                    $payment_insert['gp_ward_code']  = $row->gp_ward_code;
                    $payment_insert['ds_phase']  = $row->ds_phase;
                    $is_update_payment=DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->insert($payment_insert);
                    $is_update_transaction=DB::connection('pgsql_payment')->table($schemaname . '.ben_transaction_details')->insert($transaction_insert);
                  }
                  else{
                    $input_payment=array();
                    if($name_change==1){
                        $input_payment['ben_name'] =  $new_name;
                        $input_payment['acc_validated'] = 0;
                    }
                    if($dob_change==1){
                    $end_yymm = Carbon::parse($row->new_dob)->addYears(60);
                    $end_yymm_obj = explode('-', $end_yymm);
                    $end_month = $end_yymm_obj[1];
                    $end_day   = $end_yymm_obj[2];
                    $end_year  = substr($end_yymm_obj[0],2,2);
                    $end_yymm=$end_year.$end_month;
                    $input_payment['end_yymm'] =  $end_yymm;
                    
                    }
                    if($aadhar_change==1){
                        $input_payment['ben_status'] =  1;
                    }
                    $is_update_payment=$model_payment->where('application_id', $request->application_id)->update($input_payment);
                }
              }
            catch (\Exception $e) {
                dd($e);
                $error_found=1;
                $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                Storage::append($fileLocation, $e);
                DB::connection('pgsql_appwrite')->rollBack();
                DB::connection('pgsql_encwrite')->rollBack();
                DB::connection('pgsql_payment')->rollBack();
                $return_text =  $errormsg['roolback'];
                return redirect($url)->with('error', $return_text);
            }
          
                
                $update_aadhar_arr1 = array();
                if($name_change==1){
                    $update_aadhar_arr1['new_ben_name'] =$new_name;
                }
                if($dob_change==1){
                    $update_aadhar_arr1['new_dob'] =$new_dob;
                }
                if($aadhar_change==1){
                    $update_aadhar_arr1['new_encoded_aadhar'] = Crypt::encryptString($post_aadhar_no);
                    $update_aadhar_arr1['new_aadhar_hash'] = md5($post_aadhar_no);
                }
               
                $update_aadhar_arr1['status'] = 11;
                $update_aadhar_arr1['edited_by'] = $user_id;
                $update_aadhar_arr1['edited_at'] = $c_time;
                $update_aadhar_arr1['action_by'] = Auth::user()->id;
                $update_aadhar_arr1['action_ip_address'] = request()->ip();
                $update_aadhar_arr1['action_type'] = class_basename(request()->route()->getAction()['controller']);

                try {
                    $main_model->where('created_by_dist_code', $district_code)->where('application_id', $row->application_id)->update($update_aadhar_arr1);
                } catch (\Exception $e) {
                    dd($e);
                    $error_found=1;
                    $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
                    Storage::append($fileLocation, $e);
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect('changeAadhar?application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
                }
                if($error_found==0){
                DB::connection('pgsql_appwrite')->commit();
                DB::connection('pgsql_encwrite')->commit();
                DB::connection('pgsql_payment')->commit();
                 $return_msg = "Beneficiary information  with Application Id:" . $row->application_id . " Updated Succesfully";
               return redirect("/aadhaar-details-update-list-approver")->with('success', $return_msg);
                }
                else{
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect('changeAadhar?application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text); 
                }
               
        } catch (\Exception $e) {
            dd($e);
            $error_found=1;
            $logmessage .=  $e . date("Y-m-d h:i:s") . "." . "\n";
            Storage::append($fileLocation, $e);
                DB::connection('pgsql_appwrite')->rollBack();
                DB::connection('pgsql_encwrite')->rollBack();
                DB::connection('pgsql_payment')->rollBack();
                $return_text = $errormsg['roolback'];
                return redirect('changeAadhar?application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
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
}
