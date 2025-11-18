<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Configduty;
use App\MapLavel;
use App\District;
use App\Taluka;
use App\SubDistrict;
use App\Ward;
use App\UrbanBody;
use App\GP;

use App\DocumentType;
use App\DataSourceCommon;
use App\getModelFunc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\RejectRevertReason;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\DistrictEntryMapping;
use App\DsPhase;

class RejectDraftController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
    $this->source_type = 'ss_nfsa';
    $this->scheme_id = 20;
    $phaseArr = DsPhase::where('is_current', TRUE)->first();
    $mydate = $phaseArr->base_dob;
    $max_date = strtotime("-25 year", strtotime($mydate));
    $max_date = date("Y-m-d", $max_date);
    $min_date = strtotime("-60 year", strtotime($mydate));
    $min_date = date("Y-m-d", $min_date);
    $this->base_dob_chk_date = $mydate;
    $this->max_dob = $max_date;
    $this->min_dob = $min_date;
  }

  public function list(Request $request)
  {
    $designation_id = Auth::user()->designation_id;
    if ($designation_id != 'HOD') {
      return redirect("/")->with('error', 'Not Allowded');
    }
      $errormsg = Config::get('constants.errormsg');
      $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
      if (!empty($request->dist_code)) {
      $district_code = $request->dist_code;
      }
      else{
        $district_code = '';
      }
      $user_id = Auth::user()->id;
      $reject_revert_reason = RejectRevertReason::where('status', true)->get();
      $getModelFunc = new getModelFunc();
      if($request->faulty_status==1){
        $personal_table = $getModelFunc->getTableFaulty($district_code, '', 1,  1);
        $personal_modal = new DataSourceCommon;
        $personal_modal->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTableFaulty($district_code, '', 3, 1);
      }
      else{
        $personal_table = $getModelFunc->getTable($district_code, '', 1,  1);
        $personal_modal = new DataSourceCommon;
        $personal_modal->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
      }
      //dd($contact_table);
      $ds_phase_list = DsPhase::where('is_current',false)->get();
      $ds_phase_cur = DsPhase::where('is_current',true)->first();
      $district_list = District::all();
      $approveBtnvisible=1;
      $levels = [
            2 => 'Rural',
            1 => 'Urban',
          ];

          if (request()->ajax()) {
            $ds_phase = trim($request->ds_phase);
           // $condition[$personal_table . ".ds_phase"] = $ds_phase;
            $condition = array();
            if (!empty($ds_phase)) {
              $condition[$personal_table . ".ds_phase"] = $ds_phase;
            }
            if (!empty($request->dist_code)) {
            $condition[$personal_table . ".created_by_dist_code"] = $request->dist_code;
            $condition[$contact_table . ".created_by_dist_code"] = $request->dist_code;
            }
           

            $limit = $request->input('length');
            $offset = $request->input('start');
            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();
            if (!empty($request->search['value']))
              $serachvalue = $request->search['value'];
            else
              $serachvalue = '';
            $query = $personal_modal->where($condition)->where($personal_table . '.ds_phase','<',$ds_phase_cur->phase_code);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');       
            if (!empty($request->filter_1)) {
              // $query = $query->where('rural_urban_id', $request->filter_1);
              $query = $query->where($contact_table . '.rural_urban_id', $request->filter_1);
            }
            if (!empty($request->filter_2)) {

              $query = $query->where($personal_table . '.created_by_local_body_code', $request->filter_2);
            }
            
          
            
            if (empty($serachvalue)) {
              //$totalRecords = $query->count();
              $totalRecords = $query->count($personal_table . '.application_id');
              $data = $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->offset($offset)->limit($limit)
              ->select($personal_table . '.ds_phase',$personal_table . '.application_id',$personal_table . '.ss_card_no',$personal_table . '.duare_sarkar_registration_no',$personal_table . '.dob',
              $personal_table . '.ben_fname',$personal_table . '.ben_mname',$personal_table . '.ben_lname',
              $personal_table . '.mobile_no',$personal_table . '.email',$personal_table . '.gender',$personal_table . '.age_ason_01012021',
              $personal_table . '.father_fname',$personal_table . '.father_mname',$personal_table . '.father_lname',
              $personal_table . '.mother_fname',$personal_table . '.mother_mname',$personal_table . '.mother_lname',
              $personal_table . '.caste',$personal_table . '.caste_certificate_no',
              $contact_table . '.dist_code',$contact_table . '.block_ulb_name',$contact_table . '.gp_ward_name',
              $contact_table . '.village_town_city',$contact_table . '.police_station',$contact_table . '.post_office',
              $contact_table . '.pincode',$contact_table . '.house_premise_no'
              )->get();
              $filterRecords = count($data);
            } else {
              if (preg_match('/^[0-9]*$/', $serachvalue)) {
                $query = $query->where(function ($query1) use ($serachvalue, $personal_table) {
                  if (strlen($serachvalue) < 10) {
                    $query1->where($personal_table . '.application_id', $serachvalue);
                  } else if (strlen($serachvalue) == 10) {
                    $query1->where($personal_table . '.mobile_no', $serachvalue);
                  } else if (strlen($serachvalue) == 17) {
                    $query1->where($personal_table . '.ss_card_no', $serachvalue);
                  } else if (strlen($serachvalue) == 20) {
                    $query1->where($personal_table . '.duare_sarkar_registration_no', $serachvalue);
                  }
                });
                //$totalRecords = $query->count();
                $totalRecords = $query->count($personal_table . '.application_id');
                $data = $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->offset($offset)->limit($limit)->select('*')->get();
              } else {
                $query = $query->where(function ($query1) use ($serachvalue, $personal_table, $contact_table) {
                  $query1->where($personal_table . '.ben_fname', 'like', $serachvalue . '%')
                    ->orWhere($contact_table . '.block_ulb_name', 'like', $serachvalue . '%');
                });
                //$totalRecords = $query->count();
                $totalRecords = $query->count($personal_table . '.application_id');
                $data = $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->offset($offset)->limit($limit)->
                select($personal_table . '.ds_phase',$personal_table . '.application_id',$personal_table . '.ss_card_no',$personal_table . '.duare_sarkar_registration_no',$personal_table . '.dob',
                $personal_table . '.ben_fname',$personal_table . '.ben_mname',$personal_table . '.ben_lname',
                $personal_table . '.mobile_no',$personal_table . '.email',$personal_table . '.gender',$personal_table . '.age_ason_01012021',
                $personal_table . '.father_fname',$personal_table . '.father_mname',$personal_table . '.father_lname',
                $personal_table . '.mother_fname',$personal_table . '.mother_mname',$personal_table . '.mother_lname',
                $personal_table . '.caste',$personal_table . '.caste_certificate_no',
                $contact_table . '.dist_code',$contact_table . '.block_ulb_name',$contact_table . '.gp_ward_name',
                $contact_table . '.village_town_city',$contact_table . '.police_station',$contact_table . '.post_office',
                $contact_table . '.pincode',$contact_table . '.house_premise_no'
                )->get();
              }
              $filterRecords = count($data);
            }
            return datatables()->of($data)
              ->setTotalRecords($totalRecords)
              ->setFilteredRecords($filterRecords)
              ->skipPaging()
              ->addColumn('check', function ($data) use ($approveBtnvisible) {

                return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->application_id . '">';
              })->addColumn('view', function ($data) {
                // $action = '<a href="' . route('nhmemployee.showApplicantDetails', $data->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';

                $action = '<button class="btn btn-danger btn-sm ben_view_button" value=' . $data->application_id . '>Reject</button>';


                return $action;
              })->addColumn('id', function ($data) {
                return $data->application_id;
              })
              ->addColumn('name', function ($data) {
                return $data->getName();
              })
              ->addColumn('ds_phase_des', function ($data) use ($ds_phase_list) {
               // dd($ds_phase_list);
                $phase_des_arr=$ds_phase_list->where('phase_code',$data->ds_phase)->first();
                return $phase_des_arr->phase_des;
              })->addColumn('ss_card_no', function ($data) {
                return $data->ss_card_no;
              })
               ->addColumn('address', function ($data) use ($district_list) {
                $address_text='';
                if(!empty($data->dist_code)){
                  $district_row=$district_list->where('district_code',$data->dist_code)->first();
                  if(!empty($district_row)){
                     $address_text=$address_text.' District: '.trim($district_row->district_name);
                  }
                }
                if(!empty($data->rural_urban_id)){
                  if($data->rural_urban_id==1){
                  if(!empty($data->block_ulb_name)){
                     $address_text=$address_text.' Municipality: '.trim($data->block_ulb_name);
                  }
                  if(!empty($data->gp_ward_name)){
                     $address_text=$address_text.' Ward: '.trim($data->gp_ward_name);
                  }
                }
                else if($data->rural_urban_id==2){
                  if(!empty($data->block_ulb_name)){
                    $address_text=$address_text.' Block: '.trim($data->block_ulb_name);
                 }
                 if(!empty($data->gp_ward_name)){
                    $address_text=$address_text.' GP: '.trim($data->gp_ward_name);
                 }
                }
              }
                 return $address_text;
               })
              ->rawColumns(['view', 'check', 'id', 'name', 'ss_card_no','address'])
              ->make(true);
          }
          return view('RejectDraft/linelisting')
            ->with('levels', $levels)
            ->with('district_list', $district_list)
            ->with('sessiontimeoutmessage',  $errormsg['sessiontimeOut'])
            ->with('reject_revert_reason', $reject_revert_reason)
            ->with('dob_base_date', $dob_base_date)
            ->with('ds_phase_list', $ds_phase_list);
        
      
    
  }



  public function getBenViewPersonalData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    $designation_id = Auth::user()->designation_id;

    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }

    if ($designation_id != 'HOD') {
      $statusCode = 400;
      $response = array('error' => 'Not Allowded');
      return response()->json($response, $statusCode);
    }
    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $district_code='';
        $designation_id = Auth::user()->designation_id;
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $Table);
        $condition = array();
        $personaldata = $personal_model->where('application_id', $benid)->where($condition)->first()->toArray();
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
    $designation_id = Auth::user()->designation_id;

    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    if ($designation_id != 'HOD') {
      $statusCode = 400;
      $response = array('error' => 'Not Allowded');
      return response()->json($response, $statusCode);
    }

    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $district_code = '';
        $designation_id = Auth::user()->designation_id;
        $getModelFunc = new getModelFunc();
        $contact_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 3,  1);
        $contact_model->setConnection('pgsql_appread');
        $contact_model->setTable('' . $Table);
        $condition = array();
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
  public function getBenViewBankData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    $designation_id = Auth::user()->designation_id;

    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }

    if ($designation_id != 'HOD') {
      $statusCode = 400;
      $response = array('error' => 'Not Allowded');
      return response()->json($response, $statusCode);
    }
    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {

        $district_code = '';
        
        $designation_id = Auth::user()->designation_id;

        $getModelFunc = new getModelFunc();
        $bank_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 4,  1);
        $bank_model->setConnection('pgsql_appread');
        $bank_model->setTable('' . $Table);
        $condition = array();
       
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
  public function getBenViewPersonalDataFaulty(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    $designation_id = Auth::user()->designation_id;
    if ($designation_id != 'HOD') {
      $statusCode = 400;
      $response = array('error' => 'Not Allowded');
      return response()->json($response, $statusCode);
    }
    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
       
        $district_code = '';
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $Table);
        $condition = array();
        $personaldata = $personal_model->where('application_id', $benid)->first()->toArray();
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
 
  public function getBenViewContactDataFaulty(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    $designation_id = Auth::user()->designation_id;
    if ($designation_id != 'HOD') {
      $statusCode = 400;
      $response = array('error' => 'Not Allowded');
      return response()->json($response, $statusCode);
    }
    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $district_code = '';
        $designation_id = Auth::user()->designation_id;
        $getModelFunc = new getModelFunc();
        $contact_model = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 3,  1);
        $contact_model->setConnection('pgsql_appread');
        $contact_model->setTable('' . $Table);
        $condition = array();
        $contactdata = $contact_model->where('application_id', $benid)->first()->toArray();
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
  public function getBenViewBankDataFaulty(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    $designation_id = Auth::user()->designation_id;
    if ($designation_id != 'HOD') {
      $statusCode = 400;
      $response = array('error' => 'Not Allowded');
      return response()->json($response, $statusCode);
    }

    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $district_code = '';
      
        $getModelFunc = new getModelFunc();
        $bank_model = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 4,  1);
        $bank_model->setConnection('pgsql_appread');
        $bank_model->setTable('' . $Table);
        $condition = array();
        $bankdata = $bank_model->where('application_id', $benid)->first()->toArray();
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
 
  public function reject(Request $request)
  {
    $scheme_id=$this->scheme_id;
    $district_code = '';
    $user_id = Auth::user()->id;
    $designation_id = Auth::user()->designation_id;
    $id = $request->id;
    $Verified = "Verified";
    $Rejected = 1;
    $comments = $request->comments;
    $faulty_status = trim($request->faulty_status);
    $errormsg = Config::get('constants.errormsg');
    $duty = Configduty::where('user_id', '=', $user_id)->where('scheme_id', $scheme_id)->first();
    if ($duty->isEmpty) {
      return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
    }
    if(!in_array($faulty_status,array(0,1))){
      return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
    }
   
    if ($designation_id == 'HOD') {
      $is_bulk = $request->is_bulk;
      if ($is_bulk == 1) {
        $is_bulk = 1;
      } else {
        $is_bulk = 0;
        if (empty($id)) {
          return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Found']);
        }
        if (!ctype_digit($id)) {
          return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid1']);
        }
      }
    } else {
      return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
    }
    //dd( $is_bulk);
    $getModelFunc = new getModelFunc();
    $personal_model = new DataSourceCommon;
   // dd($faulty_status);
    if($faulty_status==1){
    $Table = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1,  1);
    }
    else if($faulty_status==0){
      $Table = $getModelFunc->getTable($district_code, $this->source_type, 1,  1);
      //dd($Table);
    }
    $personal_model->setTable('' . $Table);
    $pension_details_aadhar = new DataSourceCommon;
    if($faulty_status==1){
    $Table = $getModelFunc->getTableFaulty($district_code, $this->source_type, 2, 1);
    }
    else if($faulty_status==0){
      $Table = $getModelFunc->getTable($district_code, $this->source_type, 2, 1);

    }
    $pension_details_aadhar->setTable('' . $Table);



    $opreation_type = $request->opreation_type;
    if ($is_bulk == 1) {
    }
   if ($opreation_type == 'R') {
    //dd($is_bulk);
      if ($is_bulk == 0) {
        $row = $personal_model->select('application_id')->where('application_id', $id)->first();
      } else {
        $applicant_id_post = request()->input('applicantId');

        $applicant_id_in = explode(',', $applicant_id_post);

        $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->get();
        if (count($row_list) != count($applicant_id_in)) {
          return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid2']);
        }
      }
    } 
    if ($is_bulk == 0 && empty($row->application_id)) {
      return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid3']);
    }
    $reject_cause = $request->reject_cause;
    $comments = trim($request->accept_reject_comments);
    if ($opreation_type == 'R') {
      $txt = 'Rejected';
      $next_level_role_id = -100;
      $rejected_cause = $reject_cause;
      $message = 'Rejected Succesfully!';
    } 
    $input = ['next_level_role_id' => $next_level_role_id, 'rejected_cause' => $rejected_cause, 'comments' => $comments];
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
          $accept_reject_model->created_by = $user_id;
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
        $accept_reject_model->created_by = $user_id;
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

      if ($opreation_type == 'A' || $opreation_type == 'R') {
        //echo 1;
        if ($is_bulk == 1) { //echo 1;
          foreach ($row_list as $app_row) {
            array_push($applicationid_arr, $app_row->application_id);
          }
          $implode_application_arr = implode("','", $applicationid_arr);
          $in_pension_id = 'ARRAY[' . "'$implode_application_arr'" . ']';
          if($faulty_status==1){
            $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_faulty_final(" . $in_pension_id . ")");
          }
          else if($faulty_status==0){
            $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_partial(" . $in_pension_id . ")");

            
          }
          
        } else { // echo 4;
            $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
            if($faulty_status==1){
              $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_faulty_final(" . $in_pension_id . ")");
            }
            else if($faulty_status==0){
              $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_partial(" . $in_pension_id . ")");

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
      //dd($e);
      $return_status = 0;
      $return_msg = $errormsg['roolback'];
      //$return_msg = $e;
      DB::rollback();
    }
    return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
  }
  function ageCalculate($dob)
  {
    $diff = 0;
    if ($dob != '') {
      //$diff = $this->ageCalculate($dob);
      $diff = Carbon::parse($dob)->diffInYears('2021-01-01');
    }
    return $diff;
  }

}