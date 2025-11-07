<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\Taluka;
use Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\getModelFunc;
use App\Models\DataSourceCommon;
use App\Helpers\Helper;
use App\Models\RejectRevertReason;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
class PensionCommonController extends Controller
{
    public $scheme_id;
    public $source_type;
    public function __construct()
    {
        //$this->middleware('auth');
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
    public function shemeSessionCheck(Request $request)
    {
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        // $distCode=NULL;
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] ==  $this->scheme_id) {
                $is_active = 1;
                $request->session()->put('level', $roleObj['mapping_level']);
                $distCode = $roleObj['district_code'];
                $request->session()->put('distCode', $roleObj['district_code']);
                $request->session()->put('scheme_id',  $this->scheme_id);
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
            // $ben_table = 'dist_' . $distCode . '.beneficiary';
            return true;
        } else {
            return false;
        }
    }


    function applicantTrack()
    {
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $application_type_text = "Track Applicant";
        $schemelist = collect([]);
        $scheme_id = $this->scheme_id;
        $errormsg = Config::get('constants.errormsg');
        if (date('m') > 3) {
            $year = date('Y') . "-" . (date('Y') + 1);
        } else {
            $year = (date('Y') - 1) . "-" . date('Y');
        }
        return view(
            'pensionForm/ApplicantTrack',
            [
                'schemelist' => $schemelist,
                'scheme_id' => $scheme_id,
                'user_id' => $user_id,
                'application_type_text' => $application_type_text,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'currentFinYear' => $year
            ]
        );
    }
    public function ajaxApplicationTrack(Request $request)
    {
       // dd($request);
        $is_public = $request['is_public'];
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        if($is_public==1){
            $server_valiation=0;
            $rules = [
                'captcha' => 'required|captcha',
            ];
            $attributes = array();
            $messages = array();
            $attributes['captcha'] = 'captcha';
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
                $server_valiation=1;
            }
            else{
                $error_msg = array();
                foreach ($validator->errors()->all() as $error) {
                array_push($error_msg, $error);
                }
                //dd( $error_msg);
            }
            $designation_id='Public';
            $scheme_id =  $this->scheme_id;
            
        }
        else{
            $server_valiation=1;
            $this->shemeSessionCheck($request);
            $scheme_id =  $this->scheme_id;
            $mappingLevel = $request->session()->get('level');
            $district_code = $request->session()->get('distCode');
            $is_first = $request->session()->get('is_first');
            $is_urban = $request->session()->get('is_urban');
            $urban_body_code = $request->session()->get('bodyCode');
            $taluka_code = $request->session()->get('bodyCode');
            $role_id = $request->session()->get('role_id');
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
        }
       if( $server_valiation==1){
       
        $applicant_id = $request['applicant_id'];
        $track_type = $request['trackType'];
        $district_code='';
        $getModelFunc = new getModelFunc();
        $acceptRejectTable = $getModelFunc->getTable($district_code, $this->source_type, 9);
        $personalTableDraft = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
        $personalTableMain = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $bankTableMainDraft = $getModelFunc->getTable($district_code, $this->source_type, 4, 1);
        $bankTableMain = $getModelFunc->getTable($district_code, $this->source_type, 4);
        $aadharTable = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $rejectTable = $getModelFunc->getTable($district_code, $this->source_type, 10);

        $faultyPersonalTableDraft = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1, 1);
        $faultyPersonalTableMain = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $faultybankTableMainDraft = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4, 1);
        $faultybankTableMain = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);

        $personal_model = new DataSourceCommon;
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personalTableMain);
        $personal_model_draft = new DataSourceCommon;
        $personal_model_draft->setConnection('pgsql_appread');
        $personal_model_draft->setTable('' . $personalTableDraft);
        $model_aadhar = new DataSourceCommon;
        $model_aadhar->setConnection('pgsql_appread');
        $model_aadhar->setTable('' . $aadharTable);
        $reject_model = new DataSourceCommon;
        $reject_model->setConnection('pgsql_appread');
        $reject_model->setTable('' . $rejectTable);
        $whereCon = "where 1=1";
        /*if ($is_urban == 1) {
            $created_by_local_body_code = $urban_body_code;
        } else if ($is_urban == 2) {
            $created_by_local_body_code = $taluka_code;
        }*/
        $condition = array();
        // if ($designation_id == 'Operator' || $designation_id == 'Verifier' || $designation_id == 'Approver') {
        //     $condition['created_by_dist_code'] = $district_code;
        //     $whereCon .= " and created_by_dist_code=" . $district_code;
        // }
        // if ($designation_id == 'Operator' || $designation_id == 'Verifier') {
        //     $condition['created_by_local_body_code'] = $created_by_local_body_code;
        //     $whereCon .= " and created_by_local_body_code=" . $created_by_local_body_code;
        // }
        $s_application_id = '';
        $aadhar_search = 0;
        $s_application_id_in = array();
        $reject_found = 0;
        if (preg_match('/^[0-9]*$/', $applicant_id) && $track_type != '') {
            // Application ID
            if ($track_type == 1) {
                $row_reject = $reject_model->select('application_id', 'rejected_cause', 'ben_fname', 'rejection_date')->where('application_id', $applicant_id)->where($condition)->get();
                if (!empty($row_reject) && count($row_reject) > 0) {
                    $reject_found = 1;
                    $s_application_id = $applicant_id;
                    array_push($s_application_id_in, $s_application_id);
                } else {
                    $whereCon .= " and application_id='" . $applicant_id . "'";
                    $query_with_application = "select application_id from $personalTableDraft  " . $whereCon . "
                    UNION 
                    select application_id from $personalTableMain " . $whereCon . "
                    UNION
                    select application_id from $faultyPersonalTableDraft  " . $whereCon . "
                    UNION 
                    select application_id from $faultyPersonalTableMain " . $whereCon . "";
                // dd($query);
                    $s_result_with_application = DB::connection('pgsql_appread')->select($query_with_application);
                    if (count($s_result_with_application) > 0) {
                    $s_application_id = $applicant_id;
                    array_push($s_application_id_in, $s_application_id);
                    }
                    else{
                        $whereCon .= " and beneficiary_id='" . $applicant_id . "'";
                        $query_with_benid = "select application_id from $personalTableMain " . $whereCon . "
                        UNION 
                        select application_id from $faultyPersonalTableMain " . $whereCon . "";
                        $in_app_id = array();
                        $s_result_with_ben = DB::connection('pgsql_appread')->select($query_with_benid);
                        if (count($s_result_with_ben) > 0) {
                            foreach ($s_result_with_ben as $arr_p) {
                                array_push($in_app_id, $arr_p->application_id);
                            }
                            $s_application_id_in = $in_app_id;
                            } 
                    }
                }
            } 
            // Beneficiary ID
            else if ($track_type == 2) {
                $row_reject = $reject_model->select('application_id', 'rejected_cause', 'ben_fname', 'rejection_date')->where('beneficiary_id', $applicant_id)->where($condition)->get();
                if (!empty($row_reject) && count($row_reject) > 0) {
                    $reject_found = 1;
                    $in_app_id = array();
                    if (count($row_reject) > 0) {
                        foreach ($row_reject as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                } else {
                    $whereCon .= " and beneficiary_id='" . $applicant_id . "'";
                    $query = "select application_id from $personalTableMain " . $whereCon . "
                    UNION 
                    select application_id from $faultyPersonalTableMain " . $whereCon . "";
                // dd($query);
                    $s_result = DB::connection('pgsql_appread')->select($query);
                    $in_app_id = array();
                    if (count($s_result) > 0) {
                        foreach ($s_result as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                }
            } 
            // Mobile Number
            else if ($track_type == 3) {
                $row_reject = $reject_model->select('application_id', 'rejected_cause', 'ben_fname', 'rejection_date')->where('mobile_no', $applicant_id)->where($condition)->get();
                if (!empty($row_reject) && count($row_reject) > 0) {
                    $reject_found = 1;
                    $in_app_id = array();
                    if (count($row_reject) > 0) {
                        foreach ($row_reject as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                } else {
                    $whereCon .= " and mobile_no='" . $applicant_id . "'";
                    $query = "select application_id from $personalTableDraft  " . $whereCon . "
                UNION 
                select application_id from $personalTableMain " . $whereCon . "
                UNION
                select application_id from $faultyPersonalTableDraft  " . $whereCon . "
                UNION 
                select application_id from $faultyPersonalTableMain " . $whereCon . "";
                // dd($query);
                    $s_result = DB::connection('pgsql_appread')->select($query);
                    $in_app_id = array();
                    if (count($s_result) > 0) {
                        foreach ($s_result as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                }
            } 
            // Aadhaar Number
            else if ($track_type == 4) {
                $encrpt_aadhar = Crypt::encryptString($applicant_id);
                $row_reject = $reject_model->select('application_id', 'rejected_cause', 'ben_fname', 'rejection_date')->where('encoded_aadhar',  $encrpt_aadhar)->get();
                if (!empty($row_reject) && count($row_reject) > 0) {
                    $reject_found = 1;
                    $row = $model_aadhar->select('application_id')->where('aadhar_hash', md5($applicant_id))->first();
                    if (empty($row->application_id)) {
                        $row = $model_aadhar->select('application_id')->where('aadhar_hash_adj', md5($applicant_id))->first();
                        if (!empty($row->application_id)) {
                            $s_application_id = $row->application_id;
                            // $aadhar_search = 1;
                            array_push($s_application_id_in, $s_application_id);
                        }
                    } else {
                        $s_application_id = $row->application_id;
                        // $aadhar_search = 1;
                        array_push($s_application_id_in, $s_application_id);
                    }
                } else {
                    $row = $model_aadhar->select('application_id')->where('aadhar_hash', md5($applicant_id))->first();
                    if (empty($row->application_id)) {
                        $row = $model_aadhar->select('application_id')->where('aadhar_hash_adj', md5($applicant_id))->first();
                        if (!empty($row->application_id)) {
                            $s_application_id = $row->application_id;
                            $aadhar_search = 1;
                            array_push($s_application_id_in, $s_application_id);
                        }
                    } else {
                        $s_application_id = $row->application_id;
                        $aadhar_search = 1;
                        array_push($s_application_id_in, $s_application_id);
                    }
                }
            } 

            // Bank Account Number
            else if ($track_type == 5) {
                $row_reject = $reject_model->select('application_id', 'rejected_cause', 'ben_fname', 'rejection_date')->where('bank_code', $applicant_id)->where($condition)->get();
                if (!empty($row_reject) && count($row_reject) > 0) {
                    $reject_found = 1;
                    $in_app_id = array();
                    if (count($row_reject) > 0) {
                        foreach ($row_reject as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                } else {
                    $whereCon .= " and bank_code='" . $applicant_id . "'";
                    $query = "select application_id from $bankTableMainDraft  " . $whereCon . "
                    UNION 
                    select application_id from $bankTableMain " . $whereCon . "
                    UNION 
                    select application_id from $faultybankTableMainDraft " . $whereCon . "
                    UNION 
                    select application_id from $faultybankTableMain " . $whereCon . "";
                // dd($query);
                    $s_result = DB::connection('pgsql_appread')->select($query);
                    $in_app_id = array();
                    if (count($s_result) > 0) {
                        foreach ($s_result as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                }
            } 
            // Sasthya Sathi Card Number
            else if ($track_type == 6) {
                $row_reject = $reject_model->select('application_id', 'rejected_cause', 'ben_fname', 'rejection_date')->where('ss_card_no', $applicant_id)->where($condition)->get();
                if (!empty($row_reject) && count($row_reject) > 0) {
                    $reject_found = 1;
                    if (count($row_reject) > 0) {
                        $in_app_id = array();
                        foreach ($row_reject as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                } else {
                    $whereCon .= " and ss_card_no='" . $applicant_id . "'";
                    $query = "select application_id from $personalTableDraft  " . $whereCon . "
                UNION 
                select application_id from  $personalTableMain " . $whereCon . "
                UNION
                select application_id from $faultyPersonalTableDraft  " . $whereCon . "
                UNION 
                select application_id from $faultyPersonalTableMain " . $whereCon . "";
                    $s_result = DB::connection('pgsql_appread')->select($query);
                    if (count($s_result) > 0) {
                        $in_app_id = array();
                        foreach ($s_result as $arr_p) {
                            array_push($in_app_id, $arr_p->application_id);
                        }
                        $s_application_id_in = $in_app_id;
                    }
                }
            } 
            // Duare Sarkar Registration Number
            else {
                $row = $personal_model_draft->select('application_id')->where('duare_sarkar_registration_no', $applicant_id)->where($condition)->first();
                if (empty($row->application_id)) {
                    $row = $personal_model->select('application_id')->where('duare_sarkar_registration_no', $applicant_id)->where($condition)->first();
                    if (!empty($row->application_id)) {
                        $s_application_id = $row->application_id;
                    }
                } else {
                    $s_application_id = $row->application_id;
                }
            }
            //dump($s_application_id_in);
            if (count($s_application_id_in) > 0 && $reject_found == 0) {
                if ($aadhar_search == 1 || ($designation_id == 'HOD' ||  $designation_id == 'HOP' || $designation_id == 'StatusChecker' || $designation_id == 'MisState' || $designation_id == 'Public')) {
                    $query = DB::connection('pgsql_appread')->table("$acceptRejectTable as a")->select(
                        "a.op_type as op_type",
                        "a.designation_id as designation_id",
                        "a.created_at",
                        "a.created_by_level",
                        "a.created_by_dist_code",
                        "a.rejected_reverted_cause",
                        "a.created_by_local_body_code",
                        "a.application_id",
                        "b.mobile_no"
                    )->where('scheme_id', $scheme_id)->whereIn('op_type',['F','E','V','A','U','T','FE','FU','FR','FV','FA'])->whereIn('application_id', $s_application_id_in);
                } else {
                    $query = DB::connection('pgsql_appread')->table("$acceptRejectTable as a")->select(
                        "a.op_type as op_type",
                        "a.designation_id as designation_id",
                        "a.created_at",
                        "a.created_by_level",
                        "a.created_by_dist_code",
                        "a.rejected_reverted_cause",
                        "a.created_by_local_body_code",
                        "a.application_id",
                        "b.mobile_no"
                    )
                    // ->where('created_by_dist_code', $district_code)
                    ->where('scheme_id', $scheme_id)->whereIn('op_type',['F','E','V','A','U','T','FE','FU','FR','FV','FA'])->whereIn('application_id', $s_application_id_in);
                }
                $query = $query->join('public.users as b', 'a.created_by', '=', 'b.id');
                $result = $query->orderBy('created_at')->get();
                $grouped = $result->groupBy('application_id')->toArray();
                //dd($grouped);
                $arr = array();
                $i = 0;

                if (!empty($grouped)) {
                    foreach ($grouped as $key => $row1) {
                        $i = 0;
                        foreach ($row1 as $row) {
                            $arr[$key][$i]['created_at'] = $row->created_at;
                           // $arr[$key][$i]['mobile_no'] = $row->mobile_no;
                             $arr[$key][$i]['mobile_no'] = '******' . substr($row->mobile_no, -4);

                            $location_description = '';
                            if ($row->designation_id == 'Operator') {
                                $arr[$key][$i]['role_description'] = 'Operator:' . '******' . substr($row->mobile_no, -4);
                            } else if ($row->designation_id == 'Verifier') {
                                $arr[$key][$i]['role_description'] = 'Verifier';
                            } else if ($row->designation_id == 'Approver') {
                                $arr[$key][$i]['role_description'] = 'Approver';
                            } else {
                                $arr[$key][$i]['role_description'] = 'Others';
                            }
                            $arr[$key][$i]['action_description'] = '';
                            if (trim($row->op_type) == 'F') {
                                $arr[$key][$i]['action_description'] = 'Temporary Saved';
                            } else if (trim($row->op_type) == 'E') {
                                $arr[$key][$i]['action_description'] = 'Final Submitted';
                            } else if (trim($row->op_type) == 'V') {
                                $arr[$key][$i]['action_description'] = 'Verified';
                            } else if (trim($row->op_type) == 'A') {
                                $arr[$key][$i]['action_description'] = 'Approved';
                            } else if (trim($row->op_type) == 'U') {
                                $arr[$key][$i]['action_description'] = 'Updated';
                            } else if (trim($row->op_type) == 'T') {
                                $cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $arr[$key][$i]['action_description'] = 'Reverted for the reason ' . $cause_description;
                            }else if (trim($row->op_type) == 'FE') {
                                //$cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $arr[$key][$i]['action_description'] = 'Faulty Entry';
                            } else if (trim($row->op_type) == 'FU') {
                                //$cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $arr[$key][$i]['action_description'] = ' Move from Faulty to Normal';
                            } else if (trim($row->op_type) == 'FR') {
                                //$cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $arr[$key][$i]['action_description'] = 'Rejected for the reason ' . $cause_description;
                            } else if (trim($row->op_type) == 'FV') {
                                $arr[$key][$i]['action_description'] = 'Verified';
                            } else if (trim($row->op_type) == 'FA') {
                                $arr[$key][$i]['action_description'] = 'Approved';
                            }
                            if ($row->created_by_level == 'District') {
                                $district_arr = District::where('district_code', $row->created_by_dist_code)->first();
                                if (!empty($district_arr))
                                    $location_description = $district_arr->district_name;
                                $mapping_level = 'District Officer';
                            } else if ($row->created_by_level == 'Subdiv') {
                                $sdo_arr = SubDistrict::where('sub_district_code', $row->created_by_local_body_code)->first();
                                if (!empty($sdo_arr))
                                    $location_description = $sdo_arr->sub_district_name;
                                $mapping_level = 'Sub Division Officer';
                            } else if ($row->created_by_level == 'Block') {
                                $block_arr = Taluka::where('block_code', $row->created_by_local_body_code)->first();
                                if (!empty($block_arr))
                                    $location_description = $block_arr->block_name;

                                $mapping_level = 'Block Development Officer';
                            } else if ($row->mapping_level == 'State') {
                                $location_description = 'West Bengal';
                                $mapping_level = 'State Level Officer';
                            } else if ($row->mapping_level == 'Department') {
                                $location_description = 'West Bengal';
                                $mapping_level = 'State Level Officer';
                            }
                            $arr[$key][$i]['location_description'] = $location_description;
                            $arr[$key][$i]['mapping_level'] = $mapping_level;
                            $i++;
                        }
                    }
                    //dd($arr);
?>
                    <div class="">
                        <div class="table-responsive">
                            <div class="col-md-12">
                                <?php
                                foreach ($arr as $key => $row1) {

                                ?>
                                    <fieldset>
                                        <legend>Application Id:<?php echo $key; ?></legend>

                                        <ul class="timeline timeline-horizontal">
                                            <?php
                                            if (count($row1) > 0) {
                                                foreach ($row1 as $row) {

                                            ?>
                                                    <li class="timeline-item">
                                                        <div class="timeline-badge primary"><i class="glyphicon glyphicon-check"></i></div>
                                                        <div class="timeline-panel">
                                                            <div class="timeline-heading">
                                                                <h4 class="timeline-title">
                                                                    <?php echo date('d-m-Y H:i:s', strtotime($row['created_at'])); ?>
                                                                </h4>
                                                            </div>
                                                            <div class="timeline-body">
                                                                <p style="font-size: 12px;">Application <?php echo $row['action_description']; ?> by <?php echo $row['location_description']; ?> <?php echo $row['mapping_level']; ?> (<?php echo $row['role_description']; ?>) .</p>
                                                            </div>
                                                        </div>
                                                    </li>
                                            <?php
                                                }
                                            } else {
                                                echo "<p style='font-size:16px; font-weight: bold;' class='text-danger'>No data Found</p>";
                                            }
                                            ?>

                                        </ul>

                                    </fieldset>
                                    <hr />
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $app_id_arr = [];
                    $app_id_arr[] = $s_application_id;
                    // $app_id_arr = array(121163750,122906865,125040381,125288133);
                    $row1 = $personal_model->select('beneficiary_id', 'ben_fname', 'ben_mname', 'ben_lname', 'application_id')->distinct()->whereIn('application_id', $s_application_id_in)->where($condition);
                    $row = DB::connection('pgsql_appread')->table('lb_scheme.faulty_ben_personal_details')->select('beneficiary_id', 'ben_fname', 'ben_mname', 'ben_lname', 'application_id')->distinct()->whereIn('application_id', $s_application_id_in)->where($condition)->union($row1)->get();
                    // echo $row;
                    if (date('m') > 3) {
                        $currentFinYear = date('Y') . "-" . (date('Y') + 1);
                    } else {
                        $currentFinYear = (date('Y') - 1) . "-" . date('Y');
                    }
                    if (count($row) > 0) {
                    ?>
                        <br />
                        <h4 align="center" style="margin: 5px; font-weight: bold; font-size: 20px; color: seagreen;">Payment Status</h4>
                        <div class="col-md-12">
                            <?php foreach ($row as $k) {
                                $beneficiary_id = $k->beneficiary_id;
                                $ben_name = $k->ben_fname . ' ' . $k->ben_mname . ' ' . $k->ben_lname;
                                $application_id = $k->application_id;
                                // echo '<h4>'.$beneficiary_id.'</h4>';
                            ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title" data-toggle="collapse" data-target="#collapseOne_<?php echo $beneficiary_id; ?>" onclick="changeFinancialYear('<?php echo $currentFinYear; ?>','<?php echo $beneficiary_id; ?>')">
                                            <a>Name - <?php echo $ben_name; ?> , Beneficiary Id- <?php echo $beneficiary_id; ?> , Application Id - <?php echo $application_id; ?></a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne_<?php echo $beneficiary_id; ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="hidden" name="ben_id_hidden" id="ben_id_hidden">
                                                    <input type="hidden" name="current_fin_year" id="current_fin_year" value="<?php echo $currentFinYear; ?>">
                                                    <div class="col-md-6">
                                                        <label>Which financial year you want to view payment status ?</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <select class="" name="select_financial_year" id="select_financial_year" onchange="changeFinancialYear(this.value,<?php echo $beneficiary_id; ?>)" style="font-size: 16px; width: 150px;">
                                                        
                                                            <?php
                                                            foreach (Config::get('constants.fin_year') as $key => $fin_year) {
                                                                //echo $fin_year;
                                                                if ($key == $currentFinYear) {
                                                                    $selected = 'selected';
                                                                } else {
                                                                    $selected = '';
                                                                }
                                                                echo '<option value="' . $key . '" ' . $selected . '>' . $fin_year . '</option>';
                                                            }

                                                            ?>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="payment_details_<?php echo $beneficiary_id; ?>"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <br/> -->
                            <?php } ?>
                        </div>
                        <!-- <div align="center" style="margin: 10px;">
                            <button type="button" class="btn btn-success" id="viewPaymentStatusbtn">View Payment Status</button>
                        </div> -->

                    <?php
                    } ?>


                <?php
                } else {
                    echo "<p style='font-size:16px; font-weight: bold;' class='text-danger'>No data Found</p>";
                }
            } else {
                if ($reject_found == 1) {
                ?>
                    <div class="">
                        <div class="">
                            <div class="col-md-12">
                                <?php
                                foreach ($row_reject as  $row1) {
                                    $cause_description = $this->causeDescription($row1->rejected_cause);
                                ?>
                                    <fieldset>
                                        <legend>Application Id:<?php echo $row1->application_id; ?></legend>

                                        <ul class="timeline timeline-horizontal">

                                            <li class="timeline-item">
                                                <div class="timeline-badge primary"><i class="glyphicon glyphicon-check"></i></div>
                                                <div class="timeline-panel">
                                                    <div class="timeline-heading">
                                                        <h4 class="timeline-title">
                                                            <?php echo date('d-m-Y H:i:s', strtotime($row1->rejection_date)); ?>
                                                        </h4>
                                                    </div>
                                                    <div class="timeline-body">
                                                        <p style="font-size: 12px;">Application Rejected for the reason-<?php echo $cause_description; ?></p>
                                                    </div>
                                                </div>
                                            </li>


                                        </ul>

                                    </fieldset>
                                <?php
                                }
                                ?>

                    <?php
                    // print_r($s_application_id_in);
                    // print_r($condition);die();
                    $row = DB::connection('pgsql_appread')->table('lb_scheme.ben_reject_details')->select('beneficiary_id', 'ben_fname', 'ben_mname', 'ben_lname', 'application_id')->distinct()->whereIn('application_id', $s_application_id_in)->where($condition)->get();
                    // dd($row);
                    if (date('m') > 3) {
                        $currentFinYear = date('Y') . "-" . (date('Y') + 1);
                    } else {
                        $currentFinYear = (date('Y') - 1) . "-" . date('Y');
                    }
                    if (count($row) > 0) {
                    ?>
                        <br />
                        <h4 align="center" style="margin: 5px; font-weight: bold; font-size: 20px; color: seagreen;">Payment Status</h4>
                        <div class="col-md-12">
                            <?php foreach ($row as $k) {
                                $beneficiary_id = $k->beneficiary_id;
                                $ben_name = $k->ben_fname . ' ' . $k->ben_mname . ' ' . $k->ben_lname;
                                $application_id = $k->application_id;
                                // echo '<h4>'.$beneficiary_id.'</h4>';
                            ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title" data-toggle="collapse" data-target="#collapseOne_<?php echo $beneficiary_id; ?>" onclick="changeFinancialYear('<?php echo $currentFinYear; ?>','<?php echo $beneficiary_id; ?>')">
                                            <a>Name - <?php echo $ben_name; ?> , Beneficiary Id- <?php echo $beneficiary_id; ?> , Application Id - <?php echo $application_id; ?></a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne_<?php echo $beneficiary_id; ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="hidden" name="ben_id_hidden" id="ben_id_hidden">
                                                    <input type="hidden" name="current_fin_year" id="current_fin_year" value="<?php echo $currentFinYear; ?>">
                                                    <div class="col-md-6">
                                                        <label>Which financial year you want to view payment status ?</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <select class="" name="select_financial_year" id="select_financial_year" onchange="changeFinancialYear(this.value,<?php echo $beneficiary_id; ?>)" style="font-size: 16px; width: 150px;">
                                                            <?php
                                                            foreach (Config::get('constants.fin_year') as $key => $fin_year) {
                                                                //echo $fin_year;
                                                                if ($key == $currentFinYear) {
                                                                    $selected = 'selected';
                                                                } else {
                                                                    $selected = '';
                                                                }
                                                                echo '<option value="' . $key . '" ' . $selected . '>' . $fin_year . '</option>';
                                                            }

                                                            ?>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="payment_details_<?php echo $beneficiary_id; ?>"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <br/> -->
                            <?php } ?>
                        </div>
                        <!-- <div align="center" style="margin: 10px;">
                            <button type="button" class="btn btn-success" id="viewPaymentStatusbtn">View Payment Status</button>
                        </div> -->

                    <?php
                    } ?>

            <?php
                } else {
                    echo "<p style='font-size:16px; font-weight: bold;' class='text-danger'>No data Found</p>";
                }
            }
        } else {
            echo "<p style='font-size:16px; font-weight: bold;' class='text-danger'>No data Found</p>";
        }
    }
    else{
        echo "<p style='font-size:16px; font-weight: bold;' class='text-danger'>Invalid captcha </p>";
    }
    }
    function causeDescription($code)
    {
        $description = 'NA';
        $rejection_cause_row = RejectRevertReason::where('id', $code)->first();
        if (!empty($rejection_cause_row))
            return $rejection_cause_row->reason;
        else
            return $description;
    }

    // beneficiary payment status section  --------------  27/10/2021
    public function getPaymentDetailsFinYearWiseInTrackApplication(Request $request)
    {
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statusCode);
        }
        try {
            $fin_year = $request->fin_year;
            $ben_id = $request->ben_id;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails($fin_year);
            if ($fin_year == '2021-2022') {
                $schemaname = 'trx_mgmt_2122_archive';
            } else if ($fin_year == '2022-2023') {
                $schemaname = 'trx_mgmt_2223';
            }else if ($fin_year == '2023-2024') {
                $schemaname = $getModelFunc->getSchemaDetails($fin_year);
               // dd($schemaname);
            }
            $benStatusObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $ben_id)->first();
            $paymentDetails = '';
            $paymentDetails = $this->getFinYearWisePaymentDetailsInTrackApplication($fin_year, $ben_id);
            if (!empty($benStatusObj)) {
                $benArr = array('ben_id'=> $ben_id);
                $benIdObj = (object) $benArr;
                $response = array('personalDetails' => $benIdObj, 'paymentDetails' => $paymentDetails);
            }
            else {
                $benArr = array('ben_id'=> $ben_id);
                $benIdObj = (object) $benArr;
                $response = array('personalDetails' => $benIdObj, 'paymentDetails' => $paymentDetails);
            }
        } catch (\Exception $e) {
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
            );
            echo $response;
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }

    public function getFinYearWisePaymentDetailsInTrackApplication($fin_year, $ben_id)
    {
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails($fin_year);
        if ($fin_year == '2021-2022') {
            $schemaname = 'trx_mgmt_2122_archive';
        } else if ($fin_year == '2022-2023') {
            $schemaname = 'trx_mgmt_2223';
        }else if ($fin_year == '2023-2024') {
            $schemaname = $getModelFunc->getSchemaDetails($fin_year);
        }
        $schemanameCur = $getModelFunc->getSchemaDetails();
        $benStatusObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $ben_id)->first();

        $paymentDetails = '';

        if (!empty($benStatusObj)) {
            // New For Showing Validation Error (Date : 16/12/2021)
            $paymentDetails .= '<hr>';
            $paymentDetails .= '<h5 class="text-success"><b>Bank Account Status : ' . Config::get('globalconstants.acc_validated.' . $benStatusObj->acc_validated) . '</b></h5>';
            if ($benStatusObj->ben_status == 1) {
                $paymentDetails .= '<h5 class="text-success"><b>Beneficiary Status : ' . Config::get('globalconstants.ben_status.' . $benStatusObj->ben_status) . '</b></h5>';
            }
            else {
                $paymentDetails .= '<h5 class="text-warning"><b>Beneficiary Status : ' . Config::get('globalconstants.ben_status.' . $benStatusObj->ben_status) . '</b></h5>';
            }
            
            $paymentDetails .= '<h5 class="text-primary"><b>Bank A/c No : ' . $this->maskString(trim($benStatusObj->last_accno),2,3) . ', IFSC : ' . $this->maskString($benStatusObj->last_ifsc, 4, 2). '</b></h5>';
            $current_yymm = date('ym');
            if ($benStatusObj->end_yymm <= $current_yymm) {
                $paymentDetails .= '<h5 class="text-primary"><b>From ' . Config::get('constants.month_list.' . substr($benStatusObj->end_yymm, 2, 2)) . ' - 20' . substr($benStatusObj->end_yymm, 0, 2) . ' beneficiary payment will be stopped';
            }
            $failedObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $ben_id)->where('failed_type', 1)->where('legacy_validation_failed',false)->orderBy('id', 'DESC')->first();
            $viewError = '';
            if (isset($failedObj)) {
                if ($failedObj->edited_status == 0) {
                    if ($failedObj->pmt_mode == 1) {
                        if ($failedObj->status_code == 'NA') {
                            $viewError = $failedObj->remarks;
                        } else {
                            $viewError = Config::get('bandhancode.bandhan_response_code.' . $failedObj->status_code);
                        }
                    } else {
                        $viewError = Config::get('bandhancode.sbi_response_code.' . $failedObj->status_code);
                    }
                }
                if ($viewError != '') {
                    $paymentDetails .= '<h5 class="text-danger"><b>Account Validation Error : ' . $viewError . '</b></h5>';
                }
                if ($failedObj->edited_status == 1) {
                    $paymentDetails .= '<h5 class="text-danger"><b>Account validation error edited from the verifier end</b></h5>';
                }
                if ($failedObj->edited_status == 2 && $benStatusObj->acc_validated == '0') {
                    $paymentDetails .= '<h5 class="text-danger"><b>Account validation error edited but Validation lot creation pending</b></h5>';
                }
            }
            // New end

            $paymentDetails .= '<table class="table table-bordered table-condensed table-striped" id="paymentTable" cellspacing="0" style="font-size: 14px;" width="100%">  
                  <thead>
                    <tr role="row">
                      <th>Month</th>
                      <th>Payment Status</th>
                    </tr>
                  </thead>
                  <tbody>';
            if ($fin_year == '2021-2022') {
                $startmonth = 9;
            } else {
                $startmonth = 4;
            }
            $endmonth = 3;
            $finalendmonth = 0;
            $loopcount = '';
            if ($endmonth == 1 || $endmonth == 2 || $endmonth == 3) {
                $finalendmonth += $endmonth;
                $loopcount = (12 + $finalendmonth);
            } else {

                $loopcount = ($endmonth - $startmonth) + 4;
            }

            $count = $startmonth;
            $flag = 0;
            for ($i = $startmonth; $i <= $loopcount; $i++) {
                if ($i == 13) {
                    $count = 1;
                }
                $getMonthColumn = Helper::getMonthColumn($count);
                $lot_status = $getMonthColumn['lot_status'];
                $lot_column = $getMonthColumn['lot_column'];
                $lot_type = $getMonthColumn['lot_type'];
                // echo $benStatusObj->$lot_status;die();
                if ($benStatusObj->$lot_status == 'G' || $benStatusObj->$lot_status == 'P' || $benStatusObj->$lot_status == 'S' || $benStatusObj->$lot_status == 'F' || $benStatusObj->$lot_status == 'H') {
                    //$lot_no = $benStatusObj->$lot_column;
                    $lotStatus = $benStatusObj->$lot_status;
                    $lot_month = Config::get('constants.monthval.' . $count);
                    $paymentDetails .= '  
                        <tr>    
                            <td>' . $lot_month . '</td>';
                    if ($lotStatus == 'S' || $lotStatus == 'F') {
                        $paymentDetails .= '<td>' . Config::get('globalconstants.lot_status.' . $lotStatus) . '</td>';
                    } else {
                        $paymentDetails .= '<td>Payment Under Process</td>';
                    }

                    $paymentDetails .= '</tr>';
                    $flag = 1;
                } else {
                }
                $count++;
            }
            if ($flag == 0) {
                $paymentDetails .= '<tr><td colspan="2">Payment process yet to start.</td></tr>';
            }
            $paymentDetails .= '</tbody>
                        </table>';
        }
        else {
            $paymentDetails .= '<table class="table table-bordered table-condensed table-striped" id="paymentTable" cellspacing="0" style="font-size: 14px;" width="100%">  
              <thead>
                <tr role="row">
                  <th>Month</th>
                  <th>Payment Status</th>
                </tr>
              </thead>
              <tbody>';
            $paymentDetails .= '<tr><td colspan="2">No payment record found.</td></tr>';
            $paymentDetails .= '</tbody></table>';
        }
        return $paymentDetails;
    }

    function maskString($inputString, $first_show, $last_show) {
        if (strlen($inputString) <= ($first_show+$last_show)) {
            return $inputString;
        } else {
            $firstTwo = substr($inputString, 0, $first_show);
            $lastTwo = substr($inputString, -$last_show);
            $masked = str_repeat('x', strlen($inputString) - ($first_show+$last_show));
            return $firstTwo . $masked . $lastTwo;
        }
    }
}
