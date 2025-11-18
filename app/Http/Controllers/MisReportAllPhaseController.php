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

class MisReportAllPhaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
    }

    public function index(Request $request) 
    {
        // dd('123');
        // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get();
        $base_date  = '2020-01-01';
        $c_time = Carbon::now();
        $c_date = $c_time->format("Y-m-d");
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        // dd($designation_id);
        $district_visible = $is_urban_visible = $block_visible = 1;
        $municipality_visible = 0;
        $gp_ward_visible = 0;
        $muncList = collect([]);
        $gpList = collect([]);
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' ||  $designation_id == 'Dashboard' || $designation_id == 'DDO') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } 
        else if ($designation_id == 'Delegated Verifier' ||  $designation_id == 'Delegated Approver' || $designation_id == 'Approver' || $designation_id == 'Verifier' ) 
        {
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
        } 
        else {
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
        // dd($ds_phase_list);
        return view(
            'MisReportWithFaulty.mis_report_all_phase',
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
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;
        // dd($gp_ward);
        $caste = $request->caste_category;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $base_date  = '2020-08-16';
        $c_time = Carbon::now();
        $c_date = $c_time->format("Y-m-d");
        $heading_msg = '';
        $title = "";
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
            // 'ds_phase' => 'required|integer',
            'district' => 'nullable|integer',
            'urban_code' => 'nullable|integer',
            'block' => 'nullable|integer',
            'muncid' => 'nullable|integer',
            'gp_ward' => 'nullable|integer',
            'from_date'    => 'nullable|date|after_or_equal:' . $base_date . '|before_or_equal:' . $c_date,
            'to_date'      => 'nullable|date|after_or_equal:from_date|before_or_equal:' . $c_date,
        ];
        $data = array();
        $column = "";
        $attributes = array();
        $messages = array();
        // $attributes['ds_phase'] = 'Duare Sarkar Phase';
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['block'] = 'Block/Sub Division';
        $attributes['muncid'] = 'Municipality';
        $attributes['gp_ward'] = 'GP/Ward';
        $attributes['from_date'] = 'From Date';
        $attributes['to_date'] = 'To Date';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $user_msg = "Mis Report";
            $title = $user_msg;
            //dd($title);

            $data = array();
            $return_status = 1;
            $return_msg = '';
            $heading_msg = '';
            $external = 0;
            $external_arr = array();
            $external_filter = array();
            $is_address=0;
            if (!empty($gp_ward)) {
                if ($urban_code == 1) {
                    $is_address=1;
                    $column = "Ward";
                    $heading_msg =  $user_msg . ' of the Ward ' . $gp_ward_name;
                    $data = $this->getWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                } else {
                    $is_address=1;
                    $column = "GP";
                    $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                    
                }
            } else if (!empty($muncid)) {
                $is_address=1;
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $caste, $ds_phase);
                
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $is_address=1;
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                    
                } else if ($urban_code == 2) {
                    $is_address=1;
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                    
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $data2 = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $data = array_merge($data1, $data2);
                        
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWise(NULL, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                    
                    $external = 0;
                }
            }
            // if (!empty($caste)) {
            //     $heading_msg = $heading_msg . " for the Caste  " . $caste;
            // }
            // if (!empty($ds_phase)) {
            //     $heading_msg = $heading_msg . " of the " . $ds_phase_list[$ds_phase];
            // }
            // if (!empty($from_date)) {
            //     $form_date_formatted = \Carbon\Carbon::parse($from_date)->format('d-m-Y');
            //     $heading_msg = $heading_msg . " from " . $form_date_formatted;
            // }
            // if (!empty($to_date)) {
            //     $to_date_formatted = \Carbon\Carbon::parse($to_date)->format('d-m-Y');
            //     $heading_msg = $heading_msg . " to  " . $to_date_formatted;
            // }
            if ($is_address==1) {
                $heading_msg = $heading_msg . "<span class='text-danger'> (According to Applicant’s Address)</span>";
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        $dmv_name = 'mv_all_phase_mis_report';
        $query_g ="select max(report_generation_time) as report_generation_time from public.m_phase_report_time where mv_name='".$dmv_name."'";
        $result = DB::connection('pgsql_appwrite')->select($query_g);
        // dd($result);
        $report_geneartion_time=$result[0]->report_generation_time;
        // $report_geneartion_time= date('Y-m-d H:i:s');
        //dd($heading_msg);
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
    // public function getWardWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    // {
    //     $whereCon = "where district_code=" . $district_code;
    //    // $whereCon .= " and created_by_local_body_code=" . $ulb_code;
    //     $whereCon .= " and blk_munc_code=" . $block_ulb_code;
    //     $whereMain = "where  urban_body_code=" . $block_ulb_code;
    //     if (!empty($gp_ward_code)) {
    //         $whereCon .= " and gp_ward_code=" . $gp_ward_code;
    //         $whereMain .= " and urban_body_ward_code=" . $gp_ward_code;
    //     }
    //    if (!empty($caste)) {
    //        if ($caste == 'OTHERS') {
    //            $query ="";
    //        } 
    //        if ($caste == 'SC') {
    //            $query ="";
    //         }
    //         if ($caste == 'ST') {
    //            $query ="";
    //         }
    //    }
    //    else{
    //        $query ="";

    //    }
       
   

    //    // echo $query;die;
    //    //$result = DB::connection('pgsql_appwrite')->select($query);
    //    //return $result;
    // }
    public function getGpWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        $whereCon = "where district_code=" . $district_code;
        //$whereCon .= " and created_by_local_body_code=" . $ulb_code;
       // $whereCon .= " and blk_munc_code=" . $ulb_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and block_code=" . $ulb_code;
        if (!empty($gp_ward_code)) {
            $whereCon .= " and gp_ward_code=" . $gp_ward_code;
            $whereMain .= " and gram_panchyat_code=" . $gp_ward_code;
        }
       if (!empty($caste)) {
           if ($caste == 'OTHERS') {
               $query ="";
           } 
           if ($caste == 'SC') {
               $query ="";
            }
            if ($caste == 'ST') {
               $query ="";
            }
       }
       else{
           $query ="";

       }
       
   

       // echo $query;die;
       //$result = DB::connection('pgsql_appwrite')->select($query);
       //return $result;
    }
    public function getMuncWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        $whereCon = "where district_code=" . $district_code;
        //$whereCon .= " and created_by_local_body_code=" . $ulb_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and sub_district_code=" . $ulb_code;
       if (!empty($caste)) {
           if ($caste == 'OTHERS') {
               $query ="";
           } 
           if ($caste == 'SC') {
               $query ="";
            }
            if ($caste == 'ST') {
               $query ="";
            }
       }
       else{
           $query ="";

       }
       
   

       // echo $query;die;
       //$result = DB::connection('pgsql_appwrite')->select($query);
       //return $result;
    }
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        $whereCon = "where created_by_dist_code=" . $district_code;
        $whereMain = "where district_code=" . $district_code;
       
        $blockQuery =" SELECT m_block.block_code AS location_id,
                    m_block.block_name AS location_name,
                    m_block.district_code AS created_by_dist_code
                   FROM public.m_block " . $whereMain ."
                ";   
                $query = $this->getQuery($blockQuery, $district_code);
    //    echo $query;die;
       $result = DB::connection('pgsql_appwrite')->select($query);
       return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
       
        $whereCon = "where created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
       
       
           $subDivQuery =" SELECT m_sub_district.sub_district_code AS location_id,
           m_sub_district.sub_district_name AS location_name,
           m_sub_district.district_code AS created_by_dist_code
          FROM public.m_sub_district " . $whereMain ."";
        $query = $this->getQuery($subDivQuery, $district_code);

    //    echo $query;die;
       $result = DB::connection('pgsql_appwrite')->select($query);
       return $result;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        
        $districtQuery =" SELECT m_district.district_code AS location_id,
                    m_district.district_name AS location_name,
                    m_district.district_code AS created_by_dist_code
                   FROM public.m_district order by district_code";
        $query = $this->getQuery($districtQuery, NULL);
        // echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    private function getQuery($partQuery, $district_code = NULL)
    {
        $whereCon = '';
        if (!empty($district_code)) {
            $whereCon = "where created_by_dist_code=" . $district_code;
             $query ="select
        location_id,
        location_name,
        MV.*
        from (
            ".$partQuery."
        ) as main LEFT JOIN
        (
        SELECT
        SUM(partial_ot) as partial_ot,
        SUM(partial_sc) as partial_sc,
        SUM(partial_st) as partial_st,
        SUM(reverted_ot) as reverted_ot,
        SUM(reverted_sc) as reverted_sc,
        SUM(reverted_st) as reverted_st,
        SUM(verification_pending_ot) as verification_pending_ot,
        SUM(verification_pending_sc) as verification_pending_sc,
        SUM(verification_pending_st) as verification_pending_st,
        SUM(approval_pending_ot) as approval_pending_ot,
        SUM(approval_pending_sc) as approval_pending_sc,
        SUM(approval_pending_st) as approval_pending_st,
        SUM(approved_ot+approved_faulty_ot) as approved_ot,
        SUM(approved_sc+approved_faulty_sc) as approved_sc,
        SUM(approved_st+approved_faulty_st) as approved_st,
        SUM(rejected_ot+rejected_sc+rejected_st) as rejected,
        created_by_local_body_code
         from lb_scheme.mv_all_phase_mis_report ".$whereCon." group by created_by_local_body_code
         ) as MV ON main.location_id=MV.created_by_local_body_code";
        }
        if (empty($district_code)) {
            $whereCondition = "created_by_dist_code";
             $query ="select
        location_id,
        location_name,
        MV.*
        from (
            ".$partQuery."
        ) as main LEFT JOIN
        (
        SELECT
        SUM(partial_ot) as partial_ot,
        SUM(partial_sc) as partial_sc,
        SUM(partial_st) as partial_st,
        SUM(reverted_ot) as reverted_ot,
        SUM(reverted_sc) as reverted_sc,
        SUM(reverted_st) as reverted_st,
        SUM(verification_pending_ot) as verification_pending_ot,
        SUM(verification_pending_sc) as verification_pending_sc,
        SUM(verification_pending_st) as verification_pending_st,
        SUM(approval_pending_ot) as approval_pending_ot,
        SUM(approval_pending_sc) as approval_pending_sc,
        SUM(approval_pending_st) as approval_pending_st,
        SUM(approved_ot+approved_faulty_ot) as approved_ot,
        SUM(approved_sc+approved_faulty_sc) as approved_sc,
        SUM(approved_st+approved_faulty_st) as approved_st,
        SUM(rejected_ot+rejected_sc+rejected_st) as rejected,
        created_by_dist_code
         from lb_scheme.mv_all_phase_mis_report  group by created_by_dist_code
         ) as MV ON main.location_id=MV.created_by_dist_code order by main.location_id ";
        } else {
            $whereCondition = "created_by_local_body_code";
        }
        
        
        // $whereMain = "where district_code=" . $district_code;
       
       

        //  dd($query); 
        return $query;
        
    }
}
