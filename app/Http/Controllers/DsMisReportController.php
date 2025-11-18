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


class DsMisReportController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set('Asia/Kolkata');
        set_time_limit(300);
    }
    function dsReportphaseCommon(Request $request)
    {
        $designationId = Auth::user()->designation_id;
        $userId = Auth::user()->id;
        // $phase_list = DsPhase::where('is_current', true)->get();
        $phase_list = DsPhase::whereIn('phase_code', [12])->get();
        return view(
            'DsMisReport.dsReportCommon',
            [
                'phase_list' => $phase_list
            ]
        );
    }
    function index(Request $request)
    {
        // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
        //$ds_phase_list = DsPhase::get();
        $ds_phase_list = $request->phase_code;
        //var_dump($ds_phase_list); die;
        $scheme_id = 20;
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
        } else if ($designation_id == 'Delegated Verifier' ||  $designation_id == 'Delegated Approver' || $designation_id == 'Approver' || $designation_id == 'Verifier') {
            $district_code = NULL;
            $is_urban = NULL;
            $blockCode = NULL;
            $municipality_visible = 0;
            foreach ($roleArray as $roleObj) {
                if ($roleObj['scheme_id'] == $scheme_id) {
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

        $phase_arr = DsPhase::where('phase_code', $ds_phase_list)->first();
        if(!is_null($phase_arr)){
            $base_date  = $phase_arr->base_date;
            }
            else{
                $base_date  =$c_date;  
            }
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
            'DsMisReport.index',
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
                'ds_phase' => $ds_phase_list,
                'phase_arr' => $phase_arr
            ]
        );
    }


    // public function getPhase(Request $request){
    //     $ds_phase_val = $request->ds_phase_val;
    //     $ds_phase_list = DsPhase::select('base_dob')->where('phase_code', $ds_phase_val)->get();

    //     return response()->json([
    //         'ds_phase_list' => $ds_phase_list
    //     ]);
    // }





    public function getData(Request $request)
    { //dd($request->ds_phase); die;
        $ds_phase = $request->ds_phase;
        //  dd($request->all());
        // $ds_phase = '12';
        //$ds_phase_list = Config::get('constants.ds_phase.phaselist');
        //$ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $ds_phase_list = DsPhase::where('phase_code', $ds_phase)->first();
        //$ds_phase_list = DsPhase::get();
        //var_dump($ds_phase_list->base_dob); die;
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;
        // dd($gp_ward);
        $caste = $request->caste_category;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $base_date  = $ds_phase_list->base_date;
        // $base_date  = '2025-08-01';
        //$base_date  = $ds_phase_list->base_dob;
        $c_time = Carbon::now();
        $c_date = $c_time->format("Y-m-d");
        $heading_msg = '';
        $title = "";
        //$block_condition = "";
        if (!empty($district)) {
            $district_row = District::where('district_code', $district)->first();
        }
        $is_address=0;
        $rules = [
            'ds_phase' => 'required|integer',
            'district' => 'nullable|integer',
            'urban_code' => 'nullable|integer',
            'block' => 'nullable|integer',
            'from_date'    => 'nullable|date|after_or_equal:' . $base_date . '|before_or_equal:' . $c_date,
            'to_date'      => 'nullable|date|after_or_equal:from_date|before_or_equal:' . $c_date,
            'muncid' => 'nullable|integer',
            'gp_ward' => 'nullable|integer',
        ];
        $data = array();
        $column = "";
        $attributes = array();
        $messages = array();
        $attributes['ds_phase'] = 'Duare Sarkar Phase';
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['block'] = 'Block/ Municipality';
        $attributes['from_date'] = 'From Date';
        $attributes['to_date'] = 'To Date';
        $attributes['muncid'] = 'Municipality';
        $attributes['gp_ward'] = 'GP/Ward';
       
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $user_msg = "Duare Sarkar Report";
            $title = $user_msg;
            // dd($title);

            $data = array();
            $return_status = 1;
            $return_msg = '';
            $heading_msg = '';
            $external = 0;
            $external_arr = array();
            $external_filter = array();
            if (!empty($district)) {
                if ($urban_code == 1) {
                    if($district==315 && !empty($block)) {
                        $is_address=1;
                        $column = "Ward";
                        $municipality_row = UrbanBody::where('urban_body_code', $block)->first();
                        $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                        $data = $this->getWardWise($district, $block, $block, NULL, $from_date, $to_date, $caste, $ds_phase, $base_date);
                        $dmv_name="mv_ds_phase_".$ds_phase."_gp_ward";
                    }
                    else{
                    $is_address=1;
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $data = $this->getMuncWise($district, $block, $muncid, NULL, $from_date, $to_date, $caste, $ds_phase, $base_date);
                    $dmv_name="mv_ds_phase_".$ds_phase."_gp_ward";
                    }
                } else if ($urban_code == 2) {
                    $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $column = "Block";
                    $data = $this->getBlockWise($district, $block, NULL, NULL, $from_date, $to_date, $caste, $ds_phase, $base_date);
                    $dmv_name="mv_ds_phase_".$ds_phase."_block_subdiv";
                } else {
                    $is_address=1;
                    $heading_msg = 'Block/Municipality Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $column = "Block/Municipality";
                    $data1 = $this->getBlockWise($district, $block, NULL, NULL, $from_date, $to_date, $caste, $ds_phase, $base_date);
                    $data2 = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $caste, $ds_phase, $base_date);
                    $data = array_merge($data1, $data2);
                    $dmv_name="mv_ds_phase_".$ds_phase."_block_subdiv";
                }
            } else {
                $column = "District";
                $heading_msg = 'District Wise ' . $user_msg;
                $data = $this->getDistrictWise(NULL, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase, $base_date);
                $dmv_name="mv_ds_phase_".$ds_phase."_block_subdiv";
                $external = 0;
            }

            // dd($dmv_name);
            //var_dump($ds_phase_list->phase_des); die;
            if (!empty($ds_phase_list)) {
                $heading_msg = $heading_msg . " of the " . $ds_phase_list->phase_des;
            }
            // if (!empty($from_date)) {
            //     $form_date_formatted = \Carbon\Carbon::parse($from_date)->format('d-m-Y');
            //     $heading_msg = $heading_msg . " from " . $form_date_formatted;
            // }
            if (!empty($to_date)) {
                $to_date_formatted = \Carbon\Carbon::parse($to_date)->format('d-m-Y');
                $heading_msg = $heading_msg . " till  " . $to_date_formatted;
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        if ($is_address==1) {
            $heading_msg = $heading_msg . "<span class='text-danger'> (According to Applicant’s Address)</span>";
        }
        $query_g ="select max(report_generation_time) as report_generation_time from public.m_phase_report_time where mv_name='".$dmv_name."'";
        $result_g = DB::connection('pgsql_appwrite')->select($query_g);
        $report_geneartion_time=$result_g[0]->report_generation_time;
        return response()->json([
            'return_status' => $return_status,
            'return_msg' => $return_msg,
            'row_data' => $data,
            'column' => $column,
            'title' => $title,
            'heading_msg' => $heading_msg,
            'report_geneartion_time' => $report_geneartion_time
        ]);
    }




    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL, $base_date = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        // $dateFromat = 'YYYY-MM-DD';
        $whereCon = "where 1=1";
        // $whereCon .= " and date(created_at)>='" . $base_date . "'::date";
        $whereCon .= " and date(created_at)<='" . $todate . "'::date";

        $query ="select
        location_id,
        location_name,
        MV.*
        from (
        select district_code as location_id,district_name as location_name
        from public.m_district order by district_name
        ) as main
        LEFT JOIN
        (
        select
        sum(application_under_process) as application_under_process,
        sum(application_approved) as application_accepted,
        sum(application_rejected) as application_rejected,
        created_by_dist_code
        from lb_scheme.mv_ds_phase_".$ds_phase."_block_subdiv " . $whereCon . " group by created_by_dist_code
        ) as MV ON main.location_id=MV.created_by_dist_code";

        //echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL, $base_date = NULL)
    {
        if (!empty($ulb_code)) {
            $location_id_block = "  and block_code =" . $ulb_code . " ";
            $block_code_q = "  and created_by_local_body_code=" . $ulb_code . " ";
        } else {
            $location_id_block = "";
            $block_code_q = "";
        }
        $whereCon = "where created_by_dist_code=".$district_code;
        // $whereCon .= " and date(created_at)>='" . $base_date . "'::date";
        $whereCon .= " and date(created_at)<='" . $todate . "'::date";

        $query ="select
        location_id,
        location_name,
        MV.*
        from (
            select block_code as location_id,block_name ||'-Block' as location_name from public.m_block  
            where  district_code=" . $district_code . " " . $location_id_block . "
        ) as main
        LEFT JOIN
        (
        select
        sum(application_under_process) as application_under_process,
        sum(application_approved) as application_accepted,
        sum(application_rejected) as application_rejected,
        created_by_local_body_code
        from lb_scheme.mv_ds_phase_".$ds_phase."_block_subdiv " . $whereCon . " group by created_by_local_body_code
        ) as MV ON main.location_id=MV.created_by_local_body_code";

        //echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    public function getMuncWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL, $base_date = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
       // $whereCon = "where dist_code=" . $district_code;
        // $whereCon = "where date(created_at)>='" . $base_date . "'::date";
        $whereCon = "WHERE date(created_at)<='" . $todate . "'::date";

        if (!empty($ulb_code)) {
            $location_id_munc = "  and urban_body_code =" . $ulb_code . " ";
            if (Auth::user()->designation_id == 'Verifier' || Auth::user()->designation_id == 'Delegated Verifier') {
                if (!empty($ulb_code) && empty($block_ulb_code)) {
                    $location_id_munc = "  and sub_district_code =" . $ulb_code . " ";
                    
                }
                if (!empty($ulb_code) && !empty($block_ulb_code)) {
                    $location_id_munc = "  and urban_body_code =" . $block_ulb_code . " ";
                }
            }
        } else {
            $location_id_munc = "";
            $munc_code_q = "";
            $block_ulb_code_q = "";
            $block_ulb_code_q_reject = "";
        }
        // print $ulb_code.' => '.$block_ulb_code;
        // die;


        $query ="select
        location_id,
        location_name,
        MV.*
        from (
            select urban_body_code as location_id,urban_body_name ||'-Municipality' as location_name from 
        public.m_urban_body where  district_code=" . $district_code . " " . $location_id_munc . "
        ) as main
        LEFT JOIN
        (
        select
        sum(application_under_process) as application_under_process,
        sum(application_approved) as application_accepted,
        sum(application_rejected) as application_rejected,
        block_ulb_code
        from lb_scheme.mv_ds_phase_".$ds_phase."_gp_ward " . $whereCon . " group by block_ulb_code
        ) as MV ON main.location_id=MV.block_ulb_code";

        // echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);

        
        return $result;
    }
    public function getWardWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL, $base_date = NULL)
    {
        //$whereCon = "where dist_code=" . $district_code;
        // $whereCon = "where date(created_at)>='" . $base_date . "'::date";
        $whereCon = "WHERE date(created_at)<='" . $todate . "'::date";
        $whereMain = "where  urban_body_code=" . $block_ulb_code;
        if (!empty($gp_ward_code)) {
            $whereCon .= " and gp_ward_code=" . $gp_ward_code;
            $whereMain .= " and urban_body_ward_code=" . $gp_ward_code;
        }
       
    


        $query ="select
        location_id,
        location_name,
        MV.*
        from (
            select urban_body_ward_code as location_id,urban_body_ward_name as location_name
            from public.m_urban_body_ward " . $whereMain . "
        ) as main
        LEFT JOIN
        (
        select
        sum(application_under_process) as application_under_process,
        sum(application_approved) as application_accepted,
        sum(application_rejected) as application_rejected,
        gp_ward_code
        from lb_scheme.mv_ds_phase_".$ds_phase."_gp_ward " . $whereCon . " group by gp_ward_code
        ) as MV ON main.location_id=MV.gp_ward_code";

        //echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);

        
        return $result;
    }
}
