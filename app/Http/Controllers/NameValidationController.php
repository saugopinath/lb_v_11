<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\UrbanBody;
use App\Models\GP;
use Validator;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\getModelFunc;


class NameValidationController extends Controller
{
    public function __construct()
    {
        set_time_limit(300);
        $this->scheme_id = 20;
        $this->middleware('auth');
    }

    function misReport(Request $request)
    {
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' ||  $designation_id == 'Dashboard' || $designation_id == 'MisState') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Delegated Verifier' ||  $designation_id == 'Delegated Approver' || $designation_id == 'Approver' || $designation_id == 'Verifier') {
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
            'nameValidation.mis',
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
                'c_date' => $c_date,
                'gpList' => $gpList,
                'muncList' => $muncList,
                'designation_id' => $designation_id
            ]
        );
    }
    public function getData(Request $request)
    {
        try {
            $district = $request->district;
            $urban_code = $request->urban_code;
            $block = $request->block;
            $muncid = $request->muncid;
            $gp_ward = $request->gp_ward;
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
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
                $user_msg = "Name Validation Mis Report";
                $title = $user_msg;
                // dd($title);

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
                        $data = $this->getWardWise($district, $block, $muncid, $gp_ward);
                    } else {
                        $column = "GP";
                        $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                        $data = $this->getGpWise($district, $block, NULL, $gp_ward);
                    }
                } else if (!empty($muncid)) {
                    $column = "Ward";
                    $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                    $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                    $data = $this->getWardWise($district, $block, $muncid, NULL);
                } else if (!empty($block)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg =  $user_msg . ' of the Sub Division ' . $blk_munc_name;
                        $data = $this->getSubDivWise($district, $block, NULL, NULL);
                    } else if ($urban_code == 2) {
                        $block_arr = Taluka::where('block_code', '=', $block)->first();
                        $column = "Block";
                        $heading_msg = $user_msg . ' of the Block ' . $block_arr->block_name;
                        $data = $this->getBlockWise($district, $block, NULL, $gp_ward);
                    }
                } else {

                    if (!empty($district)) {
                        if ($urban_code == 1) {
                            $column = "Sub Division";
                            $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                            $data = $this->getSubDivWise($district, NULL, NULL, NULL);
                        } else if ($urban_code == 2) {
                            $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                            $column = "Block";
                            $data = $this->getBlockWise($district, NULL, NULL, NULL);
                        } else {
                            $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                            $column = "Block/Sub Division";
                            $data1 = $this->getBlockWise($district, NULL, NULL, NULL);
                            $data2 = $this->getSubDivWise($district, NULL, NULL, NULL);
                            $data = array_merge($data1, $data2);
                        }
                    } else {
                        $column = "District";
                        $heading_msg = 'District Wise ' . $user_msg;
                        $data = $this->getDistrictWise(NULL, NULL, NULL, NULL);

                        $external = 0;
                    }
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
                'heading_msg' => $heading_msg
            ]);
        } catch (\Exception $e) {
            dd($e);
        }
    }
    public function getWardWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL) {}
    public function getGpWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL) {}
    public function getMuncWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL) {}
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        $whereMain = "where  district_code=" . $district_code;
        $whereCon = "where dist_code=" . $district_code;
        if (!empty($ulb_code)) {
            $whereCon = $whereCon . " and  local_body_code=" . $ulb_code;
            $whereMain = $whereMain . " and  block_code=" . $ulb_code;
        }

        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query1 = "select
        count(1) filter(where f.edited_status <> 11 ) as total,
        count(1) filter(where f.edited_status=0 and f.legacy_validation_failed=false AND f.edited_status <> 11) as verification_pending_non_legacy,
        count(1) filter(where f.edited_status=0 and f.legacy_validation_failed=true AND f.edited_status <> 11) as verification_pending_legacy,
        f.local_body_code from
        lb_main.failed_payment_details as f  JOIN  " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
        where f.dist_code=" . $district_code . " and f.failed_type in(3,4)  and b.ben_status=1
        group by f.local_body_code";
        $result3 = DB::connection('pgsql_payment')->select($query1);
        $result1 = array();
        if (!empty($result3) && count($result3) > 0) {
            foreach ($result3 as $arrt) {
                $result1[$arrt->local_body_code]['total'] = $arrt->total;
                $result1[$arrt->local_body_code]['verification_pending_non_legacy'] = $arrt->verification_pending_non_legacy;
                $result1[$arrt->local_body_code]['verification_pending_legacy'] = $arrt->verification_pending_legacy;
            }
        }
        $query2 = "select main.location_id,main.location_name,
      COALESCE(d.total_same_edited,0) as total_same_edited,
      COALESCE(d.total_same_approved,0) as total_same_approved,
      COALESCE(d.total_differ_edited,0) as total_differ_edited,
      COALESCE(d.total_differ_approved,0) as total_differ_approved,
      COALESCE(d.total_rej_edited,0) as total_rej_edited,
      COALESCE(d.total_rej_approved,0) as total_rej_approved,
      COALESCE(d.total_deactivate,0) as total_deactivate
      from
      (
        select block_code as location_id,'Block-'||block_name as location_name
        from public.m_block  " . $whereMain . "
      ) as main LEFT JOIN
      (
            select
            count(1 )filter(where update_code=11 and A.next_level_role_id=5) as total_same_edited,
            count(1 )filter(where update_code=11 and A.next_level_role_id=0) as total_same_approved,
            count(1 )filter(where update_code=12 and A.next_level_role_id=5) as total_differ_edited,
            count(1 )filter(where update_code=12 and A.next_level_role_id=0) as total_differ_approved,
            count(1 )filter(where update_code=13 and A.next_level_role_id=5) as total_rej_edited,
            count(1 )filter(where update_code=13 and A.next_level_role_id=0) as total_rej_approved,
            count(B.application_id) filter(where B.next_level_role_id=-99 and A.next_level_role_id=5) as total_deactivate,
            A.local_body_code
            from lb_scheme.update_ben_details as A LEFT JOIN lb_scheme.ben_reject_details as B ON A.application_id=B.application_id
            where A.dist_code=" . $district_code . "
            and  A.update_code IN (11,12,13) group by A.local_body_code
      ) as D ON main.location_id=D.local_body_code
      order by main.location_name";
        $result2 = DB::connection('pgsql_appread')->select($query2);

        $return_arr = array();
        $i = 0;
        foreach ($result2 as  $arr) {
            $return_arr[$i]['location_id'] = $arr->location_id;
            $return_arr[$i]['location_name'] = $arr->location_name;
            $return_arr[$i]['total_same_edited'] = $arr->total_same_edited;
            $return_arr[$i]['total_same_approved'] = $arr->total_same_approved;
            $return_arr[$i]['total_differ_edited'] = $arr->total_differ_edited;
            $return_arr[$i]['total_differ_approved'] = $arr->total_differ_approved;
            $return_arr[$i]['total_rej_edited'] = $arr->total_rej_edited;
            $return_arr[$i]['total_rej_approved'] = $arr->total_rej_approved;
            $return_arr[$i]['total_deactivate'] = $arr->total_deactivate;
            if (!empty($result1)) {
                //dump('ok');
                $return_arr[$i]['total'] = $result1[$arr->location_id]['total'];
                $return_arr[$i]['verification_pending_non_legacy'] = $result1[$arr->location_id]['verification_pending_non_legacy'];
                $return_arr[$i]['verification_pending_legacy'] = $result1[$arr->location_id]['verification_pending_legacy'];
            } else {
                // dump('okk');
                $return_arr[$i]['total'] = 0;
                $return_arr[$i]['verification_pending_non_legacy'] = 0;
                $return_arr[$i]['verification_pending_legacy'] = 0;
            }
            $i++;
        }

        return $return_arr;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {


        $whereMain = "where  district_code=" . $district_code;
        $whereCon = "where dist_code=" . $district_code;
        if (!empty($ulb_code)) {
            $whereCon = $whereCon . " and  local_body_code=" . $ulb_code;
            $whereMain = $whereMain . " and  sub_district_code=" . $ulb_code;
        }
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        /*$query1 = "select count(1) as total,f.local_body_code from
        lb_main.failed_payment_details as f
        where f.dist_code=" . $district_code . " AND f.failed_type in(3,4) AND f.edited_status <> 11
        group by f.local_body_code";*/
        $query1 = "select
        count(1) filter(where f.edited_status <> 11 ) as total,
        count(1) filter(where f.edited_status=0 and f.legacy_validation_failed=false AND f.edited_status <> 11) as verification_pending_non_legacy,
        count(1) filter(where f.edited_status=0 and f.legacy_validation_failed=true AND f.edited_status <> 11) as verification_pending_legacy,
        f.local_body_code from
        lb_main.failed_payment_details as f  JOIN  " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
        where f.dist_code=" . $district_code . " and f.failed_type in(3,4)  and b.ben_status=1
        group by f.local_body_code";
        $result3 = DB::connection('pgsql_payment')->select($query1);
        $result1 = array();
        if (!empty($result3) && count($result3) > 0) {
            foreach ($result3 as $arrt) {
                $result1[$arrt->local_body_code]['total'] = $arrt->total;
                $result1[$arrt->local_body_code]['verification_pending_non_legacy'] = $arrt->verification_pending_non_legacy;
                $result1[$arrt->local_body_code]['verification_pending_legacy'] = $arrt->verification_pending_legacy;
            }
        }
        $query2 = "select main.location_id,main.location_name,
      COALESCE(d.total_same_edited,0) as total_same_edited,
      COALESCE(d.total_same_approved,0) as total_same_approved,
      COALESCE(d.total_differ_edited,0) as total_differ_edited,
      COALESCE(d.total_differ_approved,0) as total_differ_approved,
      COALESCE(d.total_rej_edited,0) as total_rej_edited,
      COALESCE(d.total_rej_approved,0) as total_rej_approved,
      COALESCE(d.total_deactivate,0) as total_deactivate
      from
      (
        select sub_district_code as location_id,'SubDivision-'||sub_district_name as location_name
        from public.m_sub_district  " . $whereMain . "
      ) as main LEFT JOIN
      (
            select
            count(1 )filter(where update_code=11 and A.next_level_role_id=5) as total_same_edited,
            count(1 )filter(where update_code=11 and A.next_level_role_id=0) as total_same_approved,
            count(1 )filter(where update_code=12 and A.next_level_role_id=5) as total_differ_edited,
            count(1 )filter(where update_code=12 and A.next_level_role_id=0) as total_differ_approved,
            count(1 )filter(where update_code=13 and A.next_level_role_id=5) as total_rej_edited,
            count(1 )filter(where update_code=13 and A.next_level_role_id=0) as total_rej_approved,
            count(B.application_id) filter(where B.next_level_role_id=-99 and A.next_level_role_id=5) as total_deactivate,
            A.local_body_code
            from lb_scheme.update_ben_details as A LEFT JOIN lb_scheme.ben_reject_details as B ON A.application_id=B.application_id
            where A.dist_code=" . $district_code . "
            and  A.update_code IN (11,12,13) group by A.local_body_code
      ) as D ON main.location_id=D.local_body_code
      order by main.location_name";
        $result2 = DB::connection('pgsql_appread')->select($query2);

        $return_arr = array();
        $i = 0;
        foreach ($result2 as  $arr) {
            $return_arr[$i]['location_id'] = $arr->location_id;
            $return_arr[$i]['location_name'] = $arr->location_name;
            $return_arr[$i]['total_same_edited'] = $arr->total_same_edited;
            $return_arr[$i]['total_same_approved'] = $arr->total_same_approved;
            $return_arr[$i]['total_differ_edited'] = $arr->total_differ_edited;
            $return_arr[$i]['total_differ_approved'] = $arr->total_differ_approved;
            $return_arr[$i]['total_rej_edited'] = $arr->total_rej_edited;
            $return_arr[$i]['total_rej_approved'] = $arr->total_rej_approved;
            $return_arr[$i]['total_deactivate'] = $arr->total_deactivate;
            if (!empty($result1)) {
                //dump('ok');
                $return_arr[$i]['total'] = $result1[$arr->location_id]['total'];
                $return_arr[$i]['verification_pending_non_legacy'] = $result1[$arr->location_id]['verification_pending_non_legacy'];
                $return_arr[$i]['verification_pending_legacy'] = $result1[$arr->location_id]['verification_pending_legacy'];
            } else {
                // dump('okk');
                $return_arr[$i]['total'] = 0;
                $return_arr[$i]['verification_pending_non_legacy'] = 0;
                $return_arr[$i]['verification_pending_legacy'] = 0;
            }
            $i++;
        }

        return $return_arr;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL)
    {
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query1 = "select
        count(1) filter(where f.edited_status <> 11 ) as total,
        count(1) filter(where f.edited_status=0 and f.legacy_validation_failed=false AND f.edited_status <> 11) as verification_pending_non_legacy,
        count(1) filter(where f.edited_status=0 and f.legacy_validation_failed=true AND f.edited_status <> 11) as verification_pending_legacy,
        f.dist_code from
        lb_main.failed_payment_details as f  JOIN  " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
        where f.failed_type in(3,4)  and b.ben_status=1
        group by f.dist_code";
        $result3 = DB::connection('pgsql_payment')->select($query1);
        $result1 = array();
        if (!empty($result3) && count($result3) > 0) {
            foreach ($result3 as $arrt) {
                $result1[$arrt->dist_code]['total'] = $arrt->total;
                $result1[$arrt->dist_code]['verification_pending_non_legacy'] = $arrt->verification_pending_non_legacy;
                $result1[$arrt->dist_code]['verification_pending_legacy'] = $arrt->verification_pending_legacy;
            }
        }
        $query2 = "select main.location_id,main.location_name,
      COALESCE(d.total_same_edited,0) as total_same_edited,
      COALESCE(d.total_same_approved,0) as total_same_approved,
      COALESCE(d.total_differ_edited,0) as total_differ_edited,
      COALESCE(d.total_differ_approved,0) as total_differ_approved,
      COALESCE(d.total_rej_edited,0) as total_rej_edited,
      COALESCE(d.total_rej_approved,0) as total_rej_approved,
      COALESCE(d.total_deactivate,0) as total_deactivate
      from
      (
      select district_code as location_id,district_name as location_name
      from public.m_district
      ) as main LEFT JOIN
      (
            select
            count(1 )filter(where update_code=11 and A.next_level_role_id=5) as total_same_edited,
            count(1 )filter(where update_code=11 and A.next_level_role_id=0) as total_same_approved,
            count(1 )filter(where update_code=12 and A.next_level_role_id=5) as total_differ_edited,
            count(1 )filter(where update_code=12 and A.next_level_role_id=0) as total_differ_approved,
            count(1 )filter(where update_code=13 and A.next_level_role_id=5) as total_rej_edited,
            count(1 )filter(where update_code=13 and A.next_level_role_id=0) as total_rej_approved,
            count(B.application_id) filter(where B.next_level_role_id=-99 and A.next_level_role_id=5) as total_deactivate,
            A.dist_code
            from lb_scheme.update_ben_details as A
            LEFT JOIN lb_scheme.ben_reject_details as B ON A.application_id=B.application_id
            where A.update_code IN (11,12,13) group by A.dist_code
      ) as D ON main.location_id=D.dist_code
      order by main.location_name";
        $result2 = DB::connection('pgsql_appread')->select($query2);

        $return_arr = array();
        $i = 0;
        foreach ($result2 as  $arr) {
            $return_arr[$i]['location_id'] = $arr->location_id;
            $return_arr[$i]['location_name'] = $arr->location_name;
            $return_arr[$i]['total_same_edited'] = $arr->total_same_edited;
            $return_arr[$i]['total_same_approved'] = $arr->total_same_approved;
            $return_arr[$i]['total_differ_edited'] = $arr->total_differ_edited;
            $return_arr[$i]['total_differ_approved'] = $arr->total_differ_approved;
            $return_arr[$i]['total_rej_edited'] = $arr->total_rej_edited;
            $return_arr[$i]['total_rej_approved'] = $arr->total_rej_approved;
            $return_arr[$i]['total_deactivate'] = $arr->total_deactivate;
            // if (!empty($result1)) {
            //     // dump('ok');
            //     $return_arr[$i]['total'] = $result1[$arr->location_id]['total'];
            //     $return_arr[$i]['verification_pending_non_legacy'] = $result1[$arr->location_id]['verification_pending_non_legacy'];
            //     $return_arr[$i]['verification_pending_legacy'] = $result1[$arr->location_id]['verification_pending_legacy'];
            // } else {
            //     // dump('okk');
            //     $return_arr[$i]['total'] = 0;
            //     $return_arr[$i]['verification_pending_non_legacy'] = 0;
            //     $return_arr[$i]['verification_pending_legacy'] = 0;
            // }
            if (!empty($result1) && isset($result1[$arr->location_id])) {
                $return_arr[$i]['total'] = $result1[$arr->location_id]['total'];
                $return_arr[$i]['verification_pending_non_legacy'] = $result1[$arr->location_id]['verification_pending_non_legacy'];
                $return_arr[$i]['verification_pending_legacy'] = $result1[$arr->location_id]['verification_pending_legacy'];
            } else {
                $return_arr[$i]['total'] = 0;
                $return_arr[$i]['verification_pending_non_legacy'] = 0;
                $return_arr[$i]['verification_pending_legacy'] = 0;
            }

            $i++;
        }

        return $return_arr;
    }
}
