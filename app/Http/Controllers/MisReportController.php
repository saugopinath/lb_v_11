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

class MisReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
    }
    function index(Request $request)
    {
        //dd('ok');
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' ||  $designation_id == 'Dashboard' || $designation_id == 'MisState') {
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
            'MisReport.index',
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
            if (!empty($gp_ward)) {
                if ($urban_code == 1) {
                    $column = "Ward";
                    $heading_msg =  $user_msg . ' of the Ward ' . $gp_ward_name;
                    $data = $this->getWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date, $caste);
                } else {
                    $column = "GP";
                    $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste);
                }
            } else if (!empty($muncid)) {
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $caste);
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $caste);
                } else if ($urban_code == 2) {
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste);
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                        $data2 = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                        $data = array_merge($data1, $data2);
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWise(NULL, NULL, NULL, NULL, $from_date, $to_date, $caste);

                    $external = 0;
                }
            }
            if (!empty($caste)) {
                $heading_msg = $heading_msg . " for the Caste  " . $caste;
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
            'heading_msg' => $heading_msg
        ]);
    }
    public function getWardWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereCon .= " and A.created_by_local_body_code=" . $ulb_code;
        $whereCon .= " and block_ulb_code=" . $block_ulb_code;
        $whereMain = "where  urban_body_code=" . $block_ulb_code;
        if (!empty($gp_ward_code)) {
            $whereCon .= " and gp_ward_code=" . $gp_ward_code;
            $whereMain .= " and urban_body_ward_code=" . $gp_ward_code;
        }
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(draft.partial,0) as partial,
        COALESCE(draft.full,0) as full,
        COALESCE(draft.verification_pending,0) as verification_pending,
        COALESCE(draft.verified,0) as verified,
        COALESCE(approve.approved,0) as approved,
        COALESCE(draft.reverted,0) as reverted,
        COALESCE(rej.rejected,0) as rejected,
        COALESCE(faulty.faulty_count,0)as faulty_count
        from
        (
        select urban_body_ward_code as location_id,urban_body_ward_name as location_name
        from public.m_urban_body_ward " . $whereMain . "
        ) as main LEFT JOIN
        (
        select count(1) filter(where is_final=FALSE) as partial,
        count(1) filter(where is_final=TRUE) as full,
        count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
        count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999) 
        as verified,
        count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted,
        B.gp_ward_code
        from lb_scheme.draft_ben_personal_details as A LEFT JOIN
        lb_scheme.draft_ben_contact_details as B ON A.application_id=B.application_id
        " . $whereCon . "       group by B.gp_ward_code
        ) as draft ON main.location_id=draft.gp_ward_code
        left join
        (
            select count(1) filter(where next_level_role_id!=9999) as approved,
            B.gp_ward_code
            from lb_scheme.ben_personal_details as A 
           LEFT JOIN lb_scheme.ben_contact_details as B ON A.application_id=B.application_id
           " . $whereCon . " 
           group by B.gp_ward_code
        ) as approve ON main.location_id=approve.gp_ward_code
        left join
        (
            select count(1) as rejected,
            gp_ward_code
            from lb_scheme.ben_reject_details as A 
            " . $whereCon . "  group by A.gp_ward_code
        ) as rej ON main.location_id=rej.gp_ward_code
        left join
        (
            select count(1) filter(where is_migrated IS NULL) as faulty_count,
            B.gp_ward_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
             " . $whereCon . " 
            group by B.gp_ward_code
        ) as faulty ON main.location_id=faulty.gp_ward_code order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getGpWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereCon .= " and A.created_by_local_body_code=" . $ulb_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and block_code=" . $ulb_code;

        if (!empty($gp_ward_code)) {
            $whereCon .= " and gp_ward_code=" . $gp_ward_code;
            $whereMain .= " and gram_panchyat_code=" . $gp_ward_code;
        }
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(draft.partial,0) as partial,
        COALESCE(draft.full,0) as full,
        COALESCE(draft.verification_pending,0) as verification_pending,
        COALESCE(draft.verified,0) as verified,
        COALESCE(approve.approved,0) as approved,
        COALESCE(draft.reverted,0) as reverted,
        COALESCE(rej.rejected,0) as rejected,
        COALESCE(faulty.faulty_count,0)as faulty_count
        from
        (
        select gram_panchyat_code as location_id,gram_panchyat_name as location_name
        from public.m_gp  " . $whereMain . "
        ) as main LEFT JOIN
        (
        select count(1) filter(where is_final=FALSE) as partial,
        count(1) filter(where is_final=TRUE) as full,
        count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
        count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999) 
        as verified,
        count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted,
        B.gp_ward_code
        from lb_scheme.draft_ben_personal_details as A LEFT JOIN
        lb_scheme.draft_ben_contact_details as B ON A.application_id=B.application_id
        " . $whereCon . " 
        group by gp_ward_code
        ) as draft ON main.location_id=draft.gp_ward_code
        left join
        (
            select count(1) filter(where next_level_role_id!=9999) as approved,
            B.gp_ward_code
            from lb_scheme.ben_personal_details as A 
           LEFT JOIN lb_scheme.ben_contact_details as B ON A.application_id=B.application_id
           " . $whereCon . " 
           group by B.gp_ward_code
        ) as approve ON main.location_id=approve.gp_ward_code
        left join
        (
            select count(1) as rejected,
            gp_ward_code
            from lb_scheme.ben_reject_details as A 
            " . $whereCon . "  group by A.gp_ward_code
        ) as rej ON main.location_id=rej.gp_ward_code
        left join
        (
            select count(1) filter(where is_migrated IS NULL) as faulty_count,
            B.gp_ward_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
            " . $whereCon . " 
            group by B.gp_ward_code
        ) as faulty ON main.location_id=faulty.gp_ward_code order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getMuncWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereCon .= " and A.created_by_local_body_code=" . $ulb_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and sub_district_code=" . $ulb_code;
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(draft.partial,0) as partial,
        COALESCE(draft.full,0) as full,
        COALESCE(draft.verification_pending,0) as verification_pending,
        COALESCE(draft.verified,0) as verified,
        COALESCE(approve.approved,0) as approved,
        COALESCE(draft.reverted,0) as reverted,
        COALESCE(rej.rejected,0) as rejected,
        COALESCE(faulty.faulty_count,0)as faulty_count
        from
        (
        select urban_body_code as location_id,urban_body_name as location_name
        from public.m_urban_body  " . $whereMain . "
        ) as main LEFT JOIN
        (
        select count(1) filter(where is_final=FALSE) as partial,
        count(1) filter(where is_final=TRUE) as full,
        count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
        count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999) 
        as verified,
        count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted,
        B.block_ulb_code
        from lb_scheme.draft_ben_personal_details as A LEFT JOIN
        lb_scheme.draft_ben_contact_details as B ON A.application_id=B.application_id
        " . $whereCon . " 
        group by B.block_ulb_code
        ) as draft ON main.location_id=draft.block_ulb_code
        left join
        (
            select count(1) filter(where next_level_role_id!=9999) as approved,
            B.block_ulb_code
            from lb_scheme.ben_personal_details as A 
           LEFT JOIN lb_scheme.ben_contact_details as B ON A.application_id=B.application_id
           " . $whereCon . " 
           group by B.block_ulb_code
        ) as approve ON main.location_id=approve.block_ulb_code
        left join
        (
            select count(1) as rejected,
            block_ulb_code
            from lb_scheme.ben_reject_details as A 
            " . $whereCon . "  group by A.block_ulb_code
        ) as rej ON main.location_id=rej.block_ulb_code
        left join
        (
            select count(1) filter(where is_migrated IS NULL) as faulty_count,
            B.block_ulb_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
            " . $whereCon . " 
            group by B.block_ulb_code
        ) as faulty ON main.location_id=faulty.block_ulb_code order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        // $dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;

        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name||'-Block' as location_name,
        COALESCE(draft.partial,0) as partial,
        COALESCE(draft.full,0) as full,
        COALESCE(draft.verification_pending,0) as verification_pending,
        COALESCE(draft.verified,0) as verified,
        COALESCE(approve.approved,0) as approved,
        COALESCE(draft.reverted,0) as reverted,
        COALESCE(rej.rejected,0) as rejected,
        COALESCE(faulty.faulty_count,0)as faulty_count
        from
        (
        select block_code as location_id,block_name as location_name
        from public.m_block  " . $whereMain . "
        ) as main LEFT JOIN
        (
        select count(1) filter(where is_final=FALSE) as partial,
        count(1) filter(where is_final=TRUE) as full,
        count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
        count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999) 
        as verified,
        count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted,A.created_by_local_body_code
        from lb_scheme.draft_ben_personal_details as A 
          " . $whereCon . "  group by A.created_by_local_body_code
        ) as draft ON main.location_id=draft.created_by_local_body_code
        left join
        (
            select count(1) filter(where next_level_role_id!=9999) as approved,
            created_by_local_body_code
            from lb_scheme.ben_personal_details as A 
            " . $whereCon . "  group by A.created_by_local_body_code
        ) as approve ON main.location_id=approve.created_by_local_body_code
        left join
        (
            select count(1) as rejected,
            created_by_local_body_code
            from lb_scheme.ben_reject_details as A 
            " . $whereCon . "  group by A.created_by_local_body_code
        ) as rej ON main.location_id=rej.created_by_local_body_code
        left join
        (
            select count(1) filter(where is_migrated IS NULL) as faulty_count,
            created_by_local_body_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            " . $whereCon . "  group by A.created_by_local_body_code
        ) as faulty ON main.location_id=faulty.created_by_local_body_code order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name||'-SubDivision' as location_name,
        COALESCE(draft.partial,0) as partial,
        COALESCE(draft.full,0) as full,
        COALESCE(draft.verification_pending,0) as verification_pending,
        COALESCE(draft.verified,0) as verified,
        COALESCE(approve.approved,0) as approved,
        COALESCE(draft.reverted,0) as reverted,
        COALESCE(rej.rejected,0) as rejected,
        COALESCE(faulty.faulty_count,0)as faulty_count
        from
        (
        select sub_district_code as location_id,sub_district_name as location_name
        from public.m_sub_district  " . $whereMain . " 
        ) as main LEFT JOIN
        (
        select count(1) filter(where is_final=FALSE) as partial,
        count(1) filter(where is_final=TRUE) as full,
        count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
        count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999) 
        as verified,
        count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted,A.created_by_local_body_code
        from lb_scheme.draft_ben_personal_details as A 
        " . $whereCon . "  group by A.created_by_local_body_code
        ) as draft ON main.location_id=draft.created_by_local_body_code
        left join
        (
            select count(1) filter(where next_level_role_id!=9999) as approved,
            created_by_local_body_code
            from lb_scheme.ben_personal_details as A 
            " . $whereCon . "  group by A.created_by_local_body_code
        ) as approve ON main.location_id=approve.created_by_local_body_code
        left join
        (
            select count(1) as rejected,
            created_by_local_body_code
            from lb_scheme.ben_reject_details as A 
            " . $whereCon . "  group by A.created_by_local_body_code
        ) as rej ON main.location_id=rej.created_by_local_body_code
        left join
        (
            select count(1) filter(where is_migrated IS NULL) as faulty_count,
            created_by_local_body_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            " . $whereCon . "  group by A.created_by_local_body_code
        ) as faulty ON main.location_id=faulty.created_by_local_body_code order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where 1=1";
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(draft.partial,0) as partial,
        COALESCE(draft.full,0) as full,
        COALESCE(draft.verification_pending,0) as verification_pending,
        COALESCE(draft.verified,0) as verified,
        COALESCE(approve.approved,0) as approved,
        COALESCE(draft.reverted,0) as reverted,
        COALESCE(rej.rejected,0) as rejected,
        COALESCE(faulty.faulty_count,0)as faulty_count
        from
        (
        select district_code as location_id,district_name as location_name
        from public.m_district  
        ) as main LEFT JOIN
        (
        select count(1) filter(where is_final=FALSE) as partial,
        count(1) filter(where is_final=TRUE) as full,
        count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
        count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999) 
        as verified,
        count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted,
        A.created_by_dist_code
        from lb_scheme.draft_ben_personal_details as A " . $whereCon . "
        group by A.created_by_dist_code
        ) as draft ON main.location_id=draft.created_by_dist_code
        left join
        (
            select count(1) filter(where next_level_role_id!=9999) as approved,
            created_by_dist_code
            from lb_scheme.ben_personal_details as A " . $whereCon . "
            group by A.created_by_dist_code
        ) as approve ON main.location_id=approve.created_by_dist_code
        left join
        (
            select count(1) as rejected,
            created_by_dist_code
            from lb_scheme.ben_reject_details as A 
            " . $whereCon . "  group by A.created_by_dist_code
        ) as rej ON main.location_id=rej.created_by_dist_code
        left join
        (
            select count(1) filter(where is_migrated IS NULL) as faulty_count,
            created_by_dist_code
            from lb_scheme.faulty_draft_ben_personal_details as A " . $whereCon . "
            group by A.created_by_dist_code
        ) as faulty ON main.location_id=faulty.created_by_dist_code order by main.location_name";

        // echo $query;die;
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getTotalPrtial($district_code, $ulb_code, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {

        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable($district_code, '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);

        $condition = array();
        $condition['is_final'] = false;
        $query = $personal_model->where($condition);
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        if (!empty($district_code)) {
            //dd($district_code);
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($block_ulb_code)) {
            $query->where($contact_table . ".block_ulb_code", $block_ulb_code);
        }
        if (!empty($gp_ward_code)) {
            $query->where($contact_table . ".gp_ward_code", $gp_ward_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalPending($district_code, $ulb_code, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {

        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable($district_code, '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);

        $condition = array();
        $condition['is_final'] = true;
        $query = $personal_model->where($condition)->whereNull('next_level_role_id');
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        if (!empty($district_code)) {
            //dd($district_code);
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($block_ulb_code)) {
            $query->where($contact_table . ".block_ulb_code", $block_ulb_code);
        }
        if (!empty($gp_ward_code)) {
            $query->where($contact_table . ".gp_ward_code", $gp_ward_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalVerified($district_code, $ulb_code, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable($district_code, '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);

        $condition = array();
        $condition['is_final'] = true;
        $query = $personal_model->where($condition)->where('next_level_role_id', '>', '0')->where('next_level_role_id', '!=', 9999);
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($block_ulb_code)) {
            $query->where($contact_table . ".block_ulb_code", $block_ulb_code);
        }
        if (!empty($gp_ward_code)) {
            $query->where($contact_table . ".gp_ward_code", $gp_ward_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalApproved($district_code, $ulb_code, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable($district_code, '', 1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3);

        $condition = array();
        $condition['next_level_role_id'] = 0;
        $query = $personal_model->where($condition)->where('next_level_role_id', '0')->where('next_level_role_id', '!=', 9999);
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($block_ulb_code)) {
            $query->where($contact_table . ".block_ulb_code", $block_ulb_code);
        }
        if (!empty($gp_ward_code)) {
            $query->where($contact_table . ".gp_ward_code", $gp_ward_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalReverted($district_code, $ulb_code, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable($district_code, '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);

        $condition = array();
        $condition['is_final'] = true;
        $query = $personal_model->where($condition)->where('next_level_role_id', '-50');;
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($block_ulb_code)) {
            $query->where($contact_table . ".block_ulb_code", $block_ulb_code);
        }
        if (!empty($gp_ward_code)) {
            $query->where($contact_table . ".gp_ward_code", $gp_ward_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalRejected($district_code, $ulb_code, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable($district_code, '', 10);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $condition = array();
        $condition['next_level_role_id'] = -100;
        $query = $personal_model->where($condition);
        if (!empty($district_code)) {
            $query->where("created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($block_ulb_code)) {
            $query->where($personal_table . ".block_ulb_code", $block_ulb_code);
        }
        if (!empty($gp_ward_code)) {
            $query->where($personal_table . ".gp_ward_code", $gp_ward_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate("created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate("created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalFaulty($district_code, $ulb_code, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTableFaulty($district_code, '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTableFaulty($district_code, '', 3, 1);

        $condition = array();
        $query = $personal_model->whereNull('is_migrated');
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($block_ulb_code)) {
            $query->where($contact_table . ".block_ulb_code", $block_ulb_code);
        }
        if (!empty($gp_ward_code)) {
            $query->where($contact_table . ".gp_ward_code", $gp_ward_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalPrtialExternal($external_filter, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable('', '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable('', '', 3, 1);
        $district_code = $external_filter['created_by_dist_code'];
        $ulb_code = $external_filter['created_by_local_body_code'];
        $column = $external_filter['column'];
        $not_in_arr = $external_filter['not_in_arr'];
        $condition = array();
        $condition['is_final'] = false;
        $query = $personal_model->where($condition);
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        $query = $query->whereNotIn($column, $not_in_arr);
        if (!empty($district_code)) {
            //dd($district_code);
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }

        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalPendingExternal($external_filter, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable('', '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable('', '', 3, 1);
        $district_code = $external_filter['created_by_dist_code'];
        $ulb_code = $external_filter['created_by_local_body_code'];
        $column = $external_filter['column'];
        $not_in_arr = $external_filter['not_in_arr'];
        $condition = array();
        $condition['is_final'] = true;
        $query = $personal_model->where($condition);
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        $query = $query->whereNotIn($column, $not_in_arr);

        if (!empty($district_code)) {
            //dd($district_code);
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalVerifiedExternal($external_filter, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable('', '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable('', '', 3, 1);
        $district_code = $external_filter['created_by_dist_code'];
        $ulb_code = $external_filter['created_by_local_body_code'];
        $column = $external_filter['column'];
        $not_in_arr = $external_filter['not_in_arr'];
        $condition = array();
        $condition['is_final'] = true;
        $query = $personal_model->where($condition)->where('next_level_role_id', '>', '0')->where('next_level_role_id', '!=', 9999);
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        $query = $query->whereNotIn($column, $not_in_arr);
        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }

        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalApprovedExternal($external_filter, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable('', '', 1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable('', '', 3);
        $district_code = $external_filter['created_by_dist_code'];
        $ulb_code = $external_filter['created_by_local_body_code'];
        $column = $external_filter['column'];
        $not_in_arr = $external_filter['not_in_arr'];
        $condition = array();
        $condition['next_level_role_id'] = 0;
        $query = $personal_model->where($condition)->where('next_level_role_id', '0')->where('next_level_role_id', '!=', 9999);
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        $query = $query->whereNotIn($column, $not_in_arr);
        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }

        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalRevertedExternal($external_filter, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable('', '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable('', '', 3, 1);
        $district_code = $external_filter['created_by_dist_code'];
        $ulb_code = $external_filter['created_by_local_body_code'];
        $column = $external_filter['column'];
        $not_in_arr = $external_filter['not_in_arr'];
        $condition = array();
        $condition['is_final'] = true;
        $query = $personal_model->where($condition)->where('next_level_role_id', '-50');;
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        $query = $query->whereNotIn($column, $not_in_arr);
        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }

        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalRejectedExternal($external_filter, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTable('', '', 10);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $district_code = $external_filter['created_by_dist_code'];
        $ulb_code = $external_filter['created_by_local_body_code'];
        $column = $external_filter['column'];
        $not_in_arr = $external_filter['not_in_arr'];
        $condition = array();
        $condition['next_level_role_id'] = -100;
        $query = $personal_model->where($condition)->whereNotIn($column, $not_in_arr);
        if (!empty($district_code)) {
            $query->where("created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }

        if (!empty($fromdate)) {
            $query->wheredate("created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate("created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    public function getTotalFaultyExternal($external_filter, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $personal_table = $getModelFunc->getTableFaulty('', '', 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTableFaulty('', '', 3, 1);
        $district_code = $external_filter['created_by_dist_code'];
        $ulb_code = $external_filter['created_by_local_body_code'];
        $column = $external_filter['column'];
        $not_in_arr = $external_filter['not_in_arr'];
        $condition = array();
        $query = $personal_model->whereNull('is_migrated');;
        if (!empty($district_code) || !empty($ulb_code)) {
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        }
        $query = $query->whereNotIn($column, $not_in_arr);

        if (!empty($district_code)) {
            $query->where($personal_table . ".created_by_dist_code", $district_code);
        }
        if (!empty($ulb_code)) {
            $query->where($personal_table . ".created_by_local_body_code", $ulb_code);
        }
        if (!empty($fromdate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $fromdate);
        }
        if (!empty($todate)) {
            $query->wheredate($personal_table . ".created_at", '>=', $todate);
        }
        if (!empty($caste)) {
            $query->where($personal_table . ".caste", $caste);
        }
        $totalRecords = $query->count();
        return $totalRecords;
    }
    function faulty(Request $request)
    {
        //dd('ok');
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' ||  $designation_id == 'Dashboard' || $designation_id == 'MisState') {
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
            'MisReport.faulty',
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
    public function getData_faulty(Request $request)
    {

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
        $attributes['district'] = 'District';
        $attributes['urban_code'] = 'Rural/ Urban';
        $attributes['block'] = 'Block/Sub Division';
        $attributes['muncid'] = 'Municipality';
        $attributes['gp_ward'] = 'GP/Ward';
        $attributes['from_date'] = 'From Date';
        $attributes['to_date'] = 'To Date';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $user_msg = "Faulty Mis Report";
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
                    $data = $this->getWardWisefaulty($district, $block, $muncid, $gp_ward, $from_date, $to_date, $caste);
                } else {
                    $column = "GP";
                    $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getGpWisefaulty($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste);
                }
            } else if (!empty($muncid)) {
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWisefaulty($district, $block, $muncid, NULL, $from_date, $to_date, $caste);
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWisefaulty($district, $block, NULL, NULL, $from_date, $to_date, $caste);
                } else if ($urban_code == 2) {
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getGpWisefaulty($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste);
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getSubDivWisefaulty($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWisefaulty($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getBlockWisefaulty($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                        $data2 = $this->getSubDivWisefaulty($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
                        $data = array_merge($data1, $data2);
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWisefaulty(NULL, NULL, NULL, NULL, $from_date, $to_date, $caste);

                    $external = 0;
                }
            }
            if (!empty($caste)) {
                $heading_msg = $heading_msg . " for the Caste  " . $caste;
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
            'heading_msg' => $heading_msg
        ]);
    }
    public function getWardWisefaulty($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereCon .= " and A.created_by_local_body_code=" . $ulb_code;
        $whereCon .= " and block_ulb_code=" . $block_ulb_code;
        $whereMain = "where  urban_body_code=" . $block_ulb_code;
        if (!empty($gp_ward_code)) {
            $whereCon .= " and gp_ward_code=" . $gp_ward_code;
            $whereMain .= " and urban_body_ward_code=" . $gp_ward_code;
        }
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(faulty.total_faulty,0) as total_faulty,
        COALESCE(faulty_aadhar.faulty_wt_aadhar,0) as faulty_wt_aadhar,
        COALESCE(faulty_bank.faulty_wt_bank_account,0) as faulty_wt_bank_account,
        COALESCE(faulty.faulty_wt_sws_card_no,0) as faulty_wt_sws_card_no,
        COALESCE(faulty.faulty_wt_cast_certificate,0) as faulty_wt_cast_certificate
        from
        (
            select urban_body_ward_code as location_id,urban_body_ward_name as location_name
        from public.m_urban_body_ward " . $whereMain . "
        ) as main  left join
        (
            select count(1) filter(where is_migrated IS NULL) as total_faulty,
            count(1) filter(where ss_card_no!='' and is_migrated IS NULL) as faulty_wt_sws_card_no,
            count(1) filter(where caste!='' and is_migrated IS NULL) as faulty_wt_cast_certificate,
            B.gp_ward_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
            " . $whereCon . "
            group by B.gp_ward_code
        ) as faulty ON main.location_id=faulty.gp_ward_code
        left join
        (
            select count(1) filter(where  A.aadhar_no !='********') as faulty_wt_aadhar,
            C.gp_ward_code
            from lb_scheme.faulty_draft_ben_personal_details as A  
            JOIN lb_scheme.faulty_ben_aadhar_details as B ON A.application_id=B.application_id
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as C ON A.application_id=C.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by C.gp_ward_code
        ) as faulty_aadhar ON main.location_id=faulty_aadhar.gp_ward_code
        left join
        (
            select count(1) filter(where bank_code!='' ) as faulty_wt_bank_account,
            C.gp_ward_code
            from  lb_scheme.faulty_draft_ben_personal_details as A   JOIN 
            lb_scheme.faulty_draft_ben_bank_details as B ON A.application_id=B.application_id 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as C ON A.application_id=C.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by C.gp_ward_code
        ) as faulty_bank ON main.location_id=faulty_bank.gp_ward_code
         order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getGpWisefaulty($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereCon .= " and A.created_by_local_body_code=" . $ulb_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and block_code=" . $ulb_code;

        if (!empty($gp_ward_code)) {
            $whereCon .= " and gp_ward_code=" . $gp_ward_code;
            $whereMain .= " and gram_panchyat_code=" . $gp_ward_code;
        }
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(faulty.total_faulty,0) as total_faulty,
        COALESCE(faulty_aadhar.faulty_wt_aadhar,0) as faulty_wt_aadhar,
        COALESCE(faulty_bank.faulty_wt_bank_account,0) as faulty_wt_bank_account,
        COALESCE(faulty.faulty_wt_sws_card_no,0) as faulty_wt_sws_card_no,
        COALESCE(faulty.faulty_wt_cast_certificate,0) as faulty_wt_cast_certificate
        from
        (
            select gram_panchyat_code as location_id,gram_panchyat_name as location_name
            from public.m_gp  " . $whereMain . "
        ) as main  left join
        (
            select count(1) filter(where is_migrated IS NULL) as total_faulty,
            count(1) filter(where ss_card_no!='' and is_migrated IS NULL) as faulty_wt_sws_card_no,
            count(1) filter(where caste!='' and is_migrated IS NULL) as faulty_wt_cast_certificate,
            B.gp_ward_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
            " . $whereCon . "
            group by B.gp_ward_code
        ) as faulty ON main.location_id=faulty.gp_ward_code
        left join
        (
            select count(1) filter(where  A.aadhar_no !='********') as faulty_wt_aadhar,
            C.gp_ward_code
            from lb_scheme.faulty_draft_ben_personal_details as A  
            JOIN lb_scheme.faulty_ben_aadhar_details as B ON A.application_id=B.application_id
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as C ON A.application_id=C.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by C.gp_ward_code
        ) as faulty_aadhar ON main.location_id=faulty_aadhar.gp_ward_code
        left join
        (
            select count(1) filter(where bank_code!='' ) as faulty_wt_bank_account,
            C.gp_ward_code
            from  lb_scheme.faulty_draft_ben_personal_details as A   JOIN 
            lb_scheme.faulty_draft_ben_bank_details as B ON A.application_id=B.application_id 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as C ON A.application_id=C.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by C.gp_ward_code
        ) as faulty_bank ON main.location_id=faulty_bank.gp_ward_code
         order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getMuncWisefaulty($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereCon .= " and A.created_by_local_body_code=" . $ulb_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and sub_district_code=" . $ulb_code;
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(faulty.total_faulty,0) as total_faulty,
        COALESCE(faulty_aadhar.faulty_wt_aadhar,0) as faulty_wt_aadhar,
        COALESCE(faulty_bank.faulty_wt_bank_account,0) as faulty_wt_bank_account,
        COALESCE(faulty.faulty_wt_sws_card_no,0) as faulty_wt_sws_card_no,
        COALESCE(faulty.faulty_wt_cast_certificate,0) as faulty_wt_cast_certificate
        from
        (
            select urban_body_code as location_id,urban_body_name as location_name
        from public.m_urban_body  " . $whereMain . "
        ) as main  left join
        (
            select count(1) filter(where is_migrated IS NULL) as total_faulty,
            count(1) filter(where ss_card_no!='' and is_migrated IS NULL) as faulty_wt_sws_card_no,
            count(1) filter(where caste!='' and is_migrated IS NULL) as faulty_wt_cast_certificate,
            B.block_ulb_code
            from lb_scheme.faulty_draft_ben_personal_details as A 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
            " . $whereCon . "
            group by B.block_ulb_code
        ) as faulty ON main.location_id=faulty.block_ulb_code
        left join
        (
            select count(1) filter(where  A.aadhar_no !='********') as faulty_wt_aadhar,
            C.block_ulb_code
            from lb_scheme.faulty_draft_ben_personal_details as A  
            JOIN lb_scheme.faulty_ben_aadhar_details as B ON A.application_id=B.application_id
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as C ON A.application_id=C.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by C.block_ulb_code
        ) as faulty_aadhar ON main.location_id=faulty_aadhar.block_ulb_code
        left join
        (
            select count(1) filter(where bank_code!='' ) as faulty_wt_bank_account,
            C.block_ulb_code
            from  lb_scheme.faulty_draft_ben_personal_details as A   JOIN 
            lb_scheme.faulty_draft_ben_bank_details as B ON A.application_id=B.application_id 
            LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as C ON A.application_id=C.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by C.block_ulb_code
        ) as faulty_bank ON main.location_id=faulty_bank.block_ulb_code
         order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getBlockWisefaulty($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        // $dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;

        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(faulty.total_faulty,0) as total_faulty,
        COALESCE(faulty_aadhar.faulty_wt_aadhar,0) as faulty_wt_aadhar,
        COALESCE(faulty_bank.faulty_wt_bank_account,0) as faulty_wt_bank_account,
        COALESCE(faulty.faulty_wt_sws_card_no,0) as faulty_wt_sws_card_no,
        COALESCE(faulty.faulty_wt_cast_certificate,0) as faulty_wt_cast_certificate
        from
        (
            select block_code as location_id,block_name as location_name
            from public.m_block  " . $whereMain . "
        ) as main  left join
        (
            select count(1) filter(where is_migrated IS NULL) as total_faulty,
            count(1) filter(where ss_card_no!='' and is_migrated IS NULL) as faulty_wt_sws_card_no,
            count(1) filter(where caste!='' and is_migrated IS NULL) as faulty_wt_cast_certificate,
            created_by_local_body_code
            from lb_scheme.faulty_draft_ben_personal_details as A " . $whereCon . "
            group by A.created_by_local_body_code
        ) as faulty ON main.location_id=faulty.created_by_local_body_code
        left join
        (
            select count(1) filter(where  A.aadhar_no !='********') as faulty_wt_aadhar,
            B.created_by_local_body_code
            from lb_scheme.faulty_draft_ben_personal_details as A  
            JOIN lb_scheme.faulty_ben_aadhar_details as B ON A.application_id=B.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by B.created_by_local_body_code
        ) as faulty_aadhar ON main.location_id=faulty_aadhar.created_by_local_body_code
        left join
        (
            select count(1) filter(where bank_code!='' ) as faulty_wt_bank_account,
            B.created_by_local_body_code
            from  lb_scheme.faulty_draft_ben_personal_details as A   JOIN 
            lb_scheme.faulty_draft_ben_bank_details as B ON A.application_id=B.application_id " . $whereCon . " and is_migrated IS NULL
            group by B.created_by_local_body_code
        ) as faulty_bank ON main.location_id=faulty_bank.created_by_local_body_code
         order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getSubDivWisefaulty($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(faulty.total_faulty,0) as total_faulty,
        COALESCE(faulty_aadhar.faulty_wt_aadhar,0) as faulty_wt_aadhar,
        COALESCE(faulty_bank.faulty_wt_bank_account,0) as faulty_wt_bank_account,
        COALESCE(faulty.faulty_wt_sws_card_no,0) as faulty_wt_sws_card_no,
        COALESCE(faulty.faulty_wt_cast_certificate,0) as faulty_wt_cast_certificate
        from
        (
            select sub_district_code as location_id,sub_district_name as location_name
            from public.m_sub_district  " . $whereMain . " 
        ) as main  left join
        (
            select count(1) filter(where is_migrated IS NULL) as total_faulty,
            count(1) filter(where ss_card_no!='' and is_migrated IS NULL) as faulty_wt_sws_card_no,
            count(1) filter(where caste!='' and is_migrated IS NULL) as faulty_wt_cast_certificate,
            created_by_local_body_code
            from lb_scheme.faulty_draft_ben_personal_details as A " . $whereCon . "
            group by A.created_by_local_body_code
        ) as faulty ON main.location_id=faulty.created_by_local_body_code
        left join
        (
            select count(1) filter(where  A.aadhar_no !='********') as faulty_wt_aadhar,
            B.created_by_local_body_code
            from lb_scheme.faulty_draft_ben_personal_details as A  
            JOIN lb_scheme.faulty_ben_aadhar_details as B ON A.application_id=B.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by B.created_by_local_body_code
        ) as faulty_aadhar ON main.location_id=faulty_aadhar.created_by_local_body_code
        left join
        (
            select count(1) filter(where bank_code!='' ) as faulty_wt_bank_account,
            B.created_by_local_body_code
            from  lb_scheme.faulty_draft_ben_personal_details as A   JOIN 
            lb_scheme.faulty_draft_ben_bank_details as B ON A.application_id=B.application_id " . $whereCon . " and is_migrated IS NULL
            group by B.created_by_local_body_code
        ) as faulty_bank ON main.location_id=faulty_bank.created_by_local_body_code
         order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getDistrictWisefaulty($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where 1=1";
        if (!empty($fromdate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')>='" . $fromdate . "'";
        }
        if (!empty($todate)) {
            $whereCon .= " and to_char(A.created_at,'" . $dateFromat . "')<='" . $todate . "'";
        }
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $whereCon .= "  and (A.caste='" . $caste . "' or A.caste ='' )";
            } else {
                $whereCon .= " and A.caste='" . $caste . "'";
            }
        }
        $query = "select main.location_id,main.location_name,
        COALESCE(faulty.total_faulty,0) as total_faulty,
        COALESCE(faulty_aadhar.faulty_wt_aadhar,0) as faulty_wt_aadhar,
        COALESCE(faulty_bank.faulty_wt_bank_account,0) as faulty_wt_bank_account,
        COALESCE(faulty.faulty_wt_sws_card_no,0) as faulty_wt_sws_card_no,
        COALESCE(faulty.faulty_wt_cast_certificate,0) as faulty_wt_cast_certificate
        from
        (
        select district_code as location_id,district_name as location_name
        from public.m_district  
        ) as main  left join
        (
            select count(1) filter(where is_migrated IS NULL) as total_faulty,
            count(1) filter(where ss_card_no!='' and is_migrated IS NULL) as faulty_wt_sws_card_no,
            count(1) filter(where caste!='' and is_migrated IS NULL) as faulty_wt_cast_certificate,
            created_by_dist_code
            from lb_scheme.faulty_draft_ben_personal_details as A " . $whereCon . "
            group by A.created_by_dist_code
        ) as faulty ON main.location_id=faulty.created_by_dist_code
        left join
        (
            select count(1) filter(where  B.aadhar_no !='********') as faulty_wt_aadhar,
            A.created_by_dist_code
            from lb_scheme.faulty_ben_aadhar_details as A  
            JOIN lb_scheme.faulty_draft_ben_personal_details as B ON A.application_id=B.application_id
            " . $whereCon . " and is_migrated IS NULL
            group by A.created_by_dist_code
        ) as faulty_aadhar ON main.location_id=faulty_aadhar.created_by_dist_code
        left join
        (
            select count(1) filter(where bank_code!='' ) as faulty_wt_bank_account,
            B.created_by_dist_code
            from  lb_scheme.faulty_draft_ben_personal_details as A   JOIN 
            lb_scheme.faulty_draft_ben_bank_details as B ON A.application_id=B.application_id " . $whereCon . " and is_migrated IS NULL
            group by B.created_by_dist_code
        ) as faulty_bank ON main.location_id=faulty_bank.created_by_dist_code
         order by main.location_name";

        // echo $query;die;
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
}
