<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configduty;
use App\Models\District;
use App\Models\UrbanBody;
use App\Models\SubDistrict;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\GP;
use App\Models\User;
use Redirect;
use Auth;
use Illuminate\Support\Facades\DB;
use Validator;
use DateTime;
use App\Models\Scheme;
use Config;
use Carbon\Carbon;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use App\Models\DsPhase;

class AgeCohortReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
    }
    function index(Request $request)
    {
        // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' ||  $designation_id == 'Dashboard') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Approver' || $designation_id == 'Verifier') {
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
        $districts = District::get();
        return view(
            'AgeCohort.index',
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
                'muncList' => $muncList,
                'ds_phase_list' => $ds_phase_list
            ]
        );
    }
    public function getData(Request $request)
    {
        //$ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $ds_phase = $request->ds_phase;
        if (!empty($district)) {
            $district_row = District::where('district_code', $district)->first();
        }
        $rules = [
            'ds_phase' => 'nullable|integer'
        ];
        $data = array();
        $column = "";
        $attributes = array();
        $messages = array();
        $attributes['ds_phase'] = 'Duare Sarkar Phase';
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['block'] = 'Block/Sub Division';
        $attributes['muncid'] = 'Municipality';
        $attributes['gp_ward'] = 'GP/Ward';
        $attributes['from_date'] = 'From Date';
        $attributes['to_date'] = 'To Date';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $user_msg = "Age Cohort Report";
            $title = $user_msg;
            //dd($title);

            $data = array();
            $return_status = 1;
            $return_msg = '';
            $heading_msg = '';
            $external = 0;
            $external_arr = array();
            $external_filter = array();
           
            if (!empty($ds_phase)) {
                $heading_msg = $heading_msg . " of the " . $ds_phase_list[$ds_phase];
            }
            $column = "District";
            $heading_msg = 'District Wise ' . $user_msg;
            $data = $this->getDistrictWise($ds_phase);
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
            'heading_msg' => $heading_msg
        ]);
    }
    public function getDistrictWise($ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
        $whereCon = "where next_level_role_id=0";
        if (!empty($ds_phase)) {
            $whereCon .= " and ds_phase=" . $ds_phase;
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(approve.age_25_35,0) as age_25_35,
        COALESCE(approve.age_35_45,0) as age_35_45,
        COALESCE(approve.age_45_55,0) as age_45_55,
        COALESCE(approve.age_55_60,0) as age_55_60,
        COALESCE(approve.age_60,0) as age_60
        from
        (
        select district_code as location_id,district_name as location_name
        from public.m_district
        ) as main LEFT JOIN
        (
        select
        sum(age_25_35) as age_25_35,
        sum(age_35_45) as age_35_45,
        sum(age_45_55) as age_45_55,
        sum(age_55_60) as age_55_60,
        sum(age_60) as age_60,
        created_by_dist_code
        from
        (
        select
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=25 and  (EXTRACT(year FROM age(current_date,dob)) :: int<=34 or age(current_date,dob)='35 years') then 1 else 0 end) as age_25_35,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=35 and age(current_date,dob)!='35 years' and  (EXTRACT(year FROM age(current_date,dob)) :: int<=44 or age(current_date,dob)='45 years') then 1 else 0 end) as age_35_45,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=45 and age(current_date,dob)!='45 years' and  (EXTRACT(year FROM age(current_date,dob)) :: int<=54 or age(current_date,dob)='55 years') then 1 else 0 end) as age_45_55,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=55 and age(current_date,dob)!='55 years' and  (EXTRACT(year FROM age(current_date,dob)) :: int<=59 or age(current_date,dob)='60 years') then 1 else 0 end) as age_55_60,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=60  then 1 else 0 end) as age_60,
        created_by_dist_code
        from lb_scheme.ben_personal_details " . $whereCon . " group by created_by_dist_code
        UNION
        select
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=25 and  (EXTRACT(year FROM age(current_date,dob)) :: int<=34 or age(current_date,dob)='35 years') then 1 else 0 end) as age_25_35,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=35 and age(current_date,dob)!='35 years' and  (EXTRACT(year FROM age(current_date,dob)) :: int<=44 or age(current_date,dob)='45 years') then 1 else 0 end) as age_35_45,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=45 and age(current_date,dob)!='45 years' and  (EXTRACT(year FROM age(current_date,dob)) :: int<=54 or age(current_date,dob)='55 years') then 1 else 0 end) as age_45_55,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=55 and age(current_date,dob)!='55 years' and  (EXTRACT(year FROM age(current_date,dob)) :: int<=59 or age(current_date,dob)='60 years') then 1 else 0 end) as age_55_60,
        sum(case when EXTRACT(year FROM age(current_date,dob)) :: int>=60  then 1 else 0 end) as age_60,
        created_by_dist_code
        from lb_scheme.faulty_ben_personal_details " . $whereCon . " group by created_by_dist_code
        ) as P group by created_by_dist_code
        ) as approve ON main.location_id=approve.created_by_dist_code
        order by main.location_name";
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }
}
