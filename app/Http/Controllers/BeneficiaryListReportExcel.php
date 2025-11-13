<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configduty;
use App\Models\District;
use App\Models\Scheme;
use Auth;
use Config;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use App\Models\UrbanBody;
use App\Models\GP;
use App\Models\RejectRevertReason;
use Carbon\Carbon;
use App\Models\DsPhase;

class BeneficiaryListReportExcel extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
        $phaseArr = DsPhase::where('is_current', TRUE)->first();
        $mydate = $phaseArr->base_dob;
        $this->base_dob_chk_date = $mydate;
    }
   
    public function download_excel(Request $request)
    {
        //dd('ok');
        try {
        ini_set('memory_limit','-1');
        ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
           foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                $is_urban = $roleObj['is_urban'];
                $distCode = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                } else if ($roleObj['is_urban'] == 2) {
                    $blockCode = $roleObj['taluka_code'];
                    $block_ulb_code=$blockCode;
                }
                break;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
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
         if($request->ds_phase=='undefined' || trim($request->ds_phase)=='')
            {
                $ds_phase='';
            }
            else{
                $ds_phase=$request->ds_phase;
            }
           
            if($request->rural_urbanid=='undefined' || trim($request->rural_urbanid)=='')
            {
                $rural_urbanid='';
            }
            else{
                $rural_urbanid=$request->rural_urbanid;
            }
            
            if($request->block_ulb_code=='undefined' || trim($request->block_ulb_code)=='')
            {
                $block_ulb_code='';
            }
            else{
                $block_ulb_code=$request->block_ulb_code;
            }
            if($request->gp_ward_code=='undefined' || trim($request->gp_ward_code)=='')
            {
                $gp_ward_code='';
            }
            else{
                $gp_ward_code=$request->gp_ward_code;
            }
        $scheme_name_row = Scheme::where('id', $scheme_id)->first();
        $scheme_name = $scheme_name_row->scheme_name;
        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $personal_table =  'lb_scheme.'.$report_type_arr->p_table_name;
        $contact_table =  'lb_scheme.'.$report_type_arr->c_table_name;
        $bank_table =  'lb_scheme.'.$report_type_arr->bank_table_name;
        $report_type = $report_type_arr->report_name;
        $base_condition = $report_type_arr->base_condition;
        $modelName->setConnection('pgsql_appread');
        $modelName->setTable('' . $personal_table);
        $condition = array();
        $condition[$personal_table . ".created_by_dist_code"] = $distCode;
        if(in_array($designation_id, array('Operator','Verifier','Delegated Verifier'))){
            $condition[$personal_table . ".created_by_local_body_code"] = $blockCode;
        }
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
                    $personal_table . '.ds_phase as ds_phase',
                    $personal_table . '.ben_fname as ben_fname',
                    $personal_table . '.ben_mname as ben_mname',
                    $personal_table . '.ben_lname as ben_lname',
                    $personal_table . '.father_fname as father_fname',
                    $personal_table . '.father_mname as father_mname',
                    $personal_table . '.father_lname as father_lname',
                    $personal_table . '.mobile_no as mobile_no',
                    $personal_table . '.caste as caste',
                    $personal_table . '.block_ulb_name as block_ulb_name',
                    $personal_table . '.gp_ward_name as gp_ward_name',
                    $personal_table . '.village_town_city as village_town_city',
                    $personal_table . '.bank_ifsc as bank_ifsc',
                    $personal_table . '.bank_code as bank_code',

                ]);
            }
           else{
                $query = $modelName
                ->where($condition)
                ->leftJoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id')
                ->leftJoin($bank_table, $bank_table . '.application_id', '=', $personal_table . '.application_id')
                ->select([
                    $personal_table . '.created_by_dist_code as created_by_dist_code',
                    $personal_table . '.application_id as application_id',
                    $personal_table . '.ds_phase as ds_phase',
                    $personal_table . '.ben_fname as ben_fname',
                    $personal_table . '.ben_mname as ben_mname',
                    $personal_table . '.ben_lname as ben_lname',
                    $personal_table . '.father_fname as father_fname',
                    $personal_table . '.father_mname as father_mname',
                    $personal_table . '.father_lname as father_lname',
                    $personal_table . '.mobile_no as mobile_no',
                    $personal_table . '.caste as caste',
                    $contact_table . '.block_ulb_name as block_ulb_name',
                    $contact_table . '.gp_ward_name as gp_ward_name',
                    $contact_table . '.village_town_city as village_town_city',
                    $bank_table . '.bank_ifsc as bank_ifsc',
                    $bank_table . '.bank_code as bank_code',

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
            if (in_array($list_type, array(7,10))) {
             $data=$query->orderBy($personal_table . '.ben_fname')->orderBy($personal_table . '.gp_ward_name')->get();
            }
            else{
            $data=$query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            }
            $filename = $scheme_name . "-" . $report_type . "-" . date('d/m/Y') ."-" . time(). ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Caste</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
               if (count($data) > 0) {
                foreach ($data as $row) {
                   
                    $bank_code = (string) $row->bank_code;
                    $bank_code_enc =substr_replace($bank_code, str_repeat('*', strlen($bank_code)-4), 0, -4);
                    if (!empty($bank_code_enc))
                        $f_bank_code = $bank_code_enc;
                    else
                        $f_bank_code = '';
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) .  "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->caste . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="11">No Records found</td></tr>';
            }
        }catch (\Exception $e) {
            dd($e);
        }

       
    }
    function ageCalculate($dob)
    {
        $diff = 0;
        if ($dob != '') {
            //$diff = $this->ageCalculate($dob);
            $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
        }
        return $diff;
    }
    function getPhaseDes($phase_code)
    {
        $phaseArr = DsPhase::where('phase_code', $phase_code)->first();
        //$phaselist = Config::get('constants.ds_phase.phaselist');
        $des = 'Phase II';
        if (!empty($phaseArr)) {
            $des = $phaseArr->phase_des;
        }
        return $des;
    }
}
