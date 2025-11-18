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

class MisReportWithFaultyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
    }
    function index(Request $request)
    {
        // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get();
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
        $districts = District::get();
        // dd($ds_phase_list);
        return view(
            'MisReportWithFaulty.index',
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
            'ds_phase' => 'required|integer',
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
                    $dmv_name="mv_phase_".$ds_phase."_gp_ward";
                }
            } else if (!empty($muncid)) {
                $is_address=1;
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $caste, $ds_phase);
                $dmv_name="mv_phase_".$ds_phase."_gp_ward";
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $is_address=1;
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                    $dmv_name="mv_phase_".$ds_phase."_gp_ward";
                } else if ($urban_code == 2) {
                    $is_address=1;
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                    $dmv_name="mv_phase_".$ds_phase."_gp_ward";
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $data2 = $this->getSubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                        $data = array_merge($data1, $data2);
                        $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWise(NULL, NULL, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                    $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    $external = 0;
                }
            }
            if (!empty($caste)) {
                $heading_msg = $heading_msg . " for the Caste  " . $caste;
            }
            if (!empty($ds_phase)) {
                $heading_msg = $heading_msg . " of the " . $ds_phase_list[$ds_phase];
            }
            if (!empty($from_date)) {
                $form_date_formatted = \Carbon\Carbon::parse($from_date)->format('d-m-Y');
                $heading_msg = $heading_msg . " from " . $form_date_formatted;
            }
            if (!empty($to_date)) {
                $to_date_formatted = \Carbon\Carbon::parse($to_date)->format('d-m-Y');
                $heading_msg = $heading_msg . " to  " . $to_date_formatted;
            }
            if ($is_address==1) {
                $heading_msg = $heading_msg . "<span class='text-danger'> (According to Applicant’s Address)</span>";
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        $query_g ="select max(report_generation_time) as report_generation_time from public.m_phase_report_time where mv_name='".$dmv_name."'";
        $result = DB::connection('pgsql_appwrite')->select($query_g);
        $report_geneartion_time=$result[0]->report_generation_time;
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
    public function NormalEntryIndex(Request $request)
    {
        // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get();
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
        } else if ($designation_id == 'Delegated Approver' || $designation_id == 'Approver') {
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
        // dd($ds_phase_list);
        return view(
            'MisReportWithFaulty.mis_report_normal_entry',
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
    public function NormalEntryGetData(Request $request)
    {
        // dd($request->all());
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;
        // dd($gp_ward);
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $ds_phase = $request->ds_phase;
        // dd($ds_phase);
        $base_date  = '2025-01-24';
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
            $user_msg = "Applications Statistics";
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
                    $data = $this->getNormalEntryWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date, $ds_phase);
                } else {
                    $is_address=1;
                    $column = "GP";
                    $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getNormalEntryGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $ds_phase);
                    $dmv_name="mv_phase_".$ds_phase."_gp_ward";
                }
            } else if (!empty($muncid)) {
                $is_address=1;
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getNormalEntryWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $ds_phase);
                $dmv_name="mv_phase_".$ds_phase."_gp_ward";
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $is_address=1;
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getNormalEntryMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $ds_phase);
                    $dmv_name="mv_phase_".$ds_phase."_gp_ward";
                } else if ($urban_code == 2) {
                    $is_address=1;
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getNormalEntryGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $ds_phase);
                    $dmv_name="mv_phase_".$ds_phase."_gp_ward";
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getNormalEntrySubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $ds_phase);
                        $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getNormalEntryBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $ds_phase);
                        $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getNormalEntryBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $ds_phase);
                        $data2 = $this->getNormalEntrySubDivWise($district, NULL, NULL, NULL, $from_date, $to_date, $ds_phase);
                        $data = array_merge($data1, $data2);
                        $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getNormalEntryDistrictWise($district, NULL, NULL, NULL, $from_date, $to_date, $ds_phase);
                    $dmv_name="mv_phase_".$ds_phase."_block_subdiv";
                    $external = 0;
                }
            }
            if (!empty($ds_phase)) {
                $heading_msg = $heading_msg . " of the " . $ds_phase_list[$ds_phase];
            }
            if (!empty($from_date)) {
                $form_date_formatted = \Carbon\Carbon::parse($from_date)->format('d-m-Y');
                $heading_msg = $heading_msg . " from " . $form_date_formatted;
            }
            if (!empty($to_date)) {
                $to_date_formatted = \Carbon\Carbon::parse($to_date)->format('d-m-Y');
                $heading_msg = $heading_msg . " to  " . $to_date_formatted;
            }
            if ($is_address==1) {
                $heading_msg = $heading_msg . "<span class='text-danger'> (According to Applicant’s Address)</span>";
            }
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        $query_g ="select max(report_generation_time) as report_generation_time from public.m_phase_report_time where mv_name='".$dmv_name."'";
        $result = DB::connection('pgsql_appwrite')->select($query_g);
        $report_geneartion_time=$result[0]->report_generation_time;
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
    public function getWardWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        $whereCon = "where district_code=" . $district_code;
       // $whereCon .= " and created_by_local_body_code=" . $ulb_code;
        $whereCon .= " and blk_munc_code=" . $block_ulb_code;
        $whereMain = "where  urban_body_code=" . $block_ulb_code;
        if (!empty($gp_ward_code)) {
            $whereCon .= " and gp_ward_code=" . $gp_ward_code;
            $whereMain .= " and urban_body_ward_code=" . $gp_ward_code;
        }
       if (!empty($caste)) {
           if ($caste == 'OTHERS') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select urban_body_ward_code as location_id,urban_body_ward_name as location_name
                from public.m_urban_body_ward " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_OT) as partial,
               sum(full_OT) as full,
               sum(verification_pending_OT) as verification_pending,
               sum(verified_OT) as verified,
               sum(approved_OT) as approved,
               sum(reverted_OT) as reverted,
               sum(rejected_OT) as rejected,
               sum(total_faulty_OT) as total_faulty,
               sum(verification_pending_faulty_OT) verification_pending_faulty,
               sum(verified_faulty_OT) as verified_faulty,
               sum(approved_faulty_OT) as approved_faulty,
               gp_ward_code
                 from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
                 ) as MV ON main.location_id=MV.gp_ward_code";
           } 
           if ($caste == 'SC') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select urban_body_ward_code as location_id,urban_body_ward_name as location_name
                from public.m_urban_body_ward " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_SC) as partial,
               sum(full_SC) as full,
               sum(verification_pending_SC) as verification_pending,
               sum(verified_SC) as verified,
               sum(approved_SC) as approved,
               sum(reverted_SC) as reverted,
               sum(rejected_SC) as rejected,
               sum(total_faulty_SC) as total_faulty,
               sum(verification_pending_faulty_SC) verification_pending_faulty,
               sum(verified_faulty_SC) as verified_faulty,
               sum(approved_faulty_SC) as approved_faulty,
               gp_ward_code
               from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
               ) as MV ON main.location_id=MV.gp_ward_code";
            }
            if ($caste == 'ST') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select urban_body_ward_code as location_id,urban_body_ward_name as location_name
                from public.m_urban_body_ward " . $whereMain . "
               ) as main  LEFT JOIN
               (
               select 
               sum(partial_ST) as partial,
               sum(full_ST) as full,
               sum(verification_pending_ST) as verification_pending,
               sum(verified_ST) as verified,
               sum(approved_ST) as approved,
               sum(reverted_ST) as reverted,
               sum(rejected_ST) as rejected,
               sum(total_faulty_ST) as total_faulty,
               sum(verification_pending_faulty_ST) verification_pending_faulty,
               sum(verified_faulty_ST) as verified_faulty,
               sum(approved_faulty_ST) as approved_faulty,
               gp_ward_code
               from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
               )  as MV ON main.location_id=MV.gp_ward_code";
            }
       }
       else{
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
           sum(partial_OT+partial_SC+partial_ST) as partial,
           sum(full_OT+full_SC+full_ST) as full,
           sum(verification_pending_OT+verification_pending_SC+verification_pending_ST) as verification_pending,
           sum(verified_OT+verified_SC+verified_ST) as verified,
           sum(approved_OT+approved_SC+approved_ST) as approved,
           sum(reverted_OT+reverted_SC+reverted_ST) as reverted,
           sum(rejected_OT+rejected_SC+rejected_ST) as rejected,
           sum(total_faulty_OT+total_faulty_SC+total_faulty_ST) as total_faulty,
           sum(verification_pending_faulty_OT+verification_pending_faulty_SC+verification_pending_faulty_ST) as verification_pending_faulty,
           sum(verified_faulty_OT+verified_faulty_SC+verified_faulty_ST) as verified_faulty,
           sum(approved_faulty_OT+approved_faulty_SC+approved_faulty_ST) as approved_faulty,
           gp_ward_code
           from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
           ) as MV ON main.location_id=MV.gp_ward_code";

       }
       
   

       // echo $query;die;
       $result = DB::connection('pgsql_appwrite')->select($query);
       return $result;
    }
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
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select gram_panchyat_code as location_id,gram_panchyat_name as location_name
        from public.m_gp  " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_OT) as partial,
               sum(full_OT) as full,
               sum(verification_pending_OT) as verification_pending,
               sum(verified_OT) as verified,
               sum(approved_OT) as approved,
               sum(reverted_OT) as reverted,
               sum(rejected_OT) as rejected,
               sum(total_faulty_OT) as total_faulty,
               sum(verification_pending_faulty_OT) verification_pending_faulty,
               sum(verified_faulty_OT) as verified_faulty,
               sum(approved_faulty_OT) as approved_faulty,
               gp_ward_code
                 from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
                 ) as MV ON main.location_id=MV.gp_ward_code";
           } 
           if ($caste == 'SC') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select gram_panchyat_code as location_id,gram_panchyat_name as location_name
        from public.m_gp  " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_SC) as partial,
               sum(full_SC) as full,
               sum(verification_pending_SC) as verification_pending,
               sum(verified_SC) as verified,
               sum(approved_SC) as approved,
               sum(reverted_SC) as reverted,
               sum(rejected_SC) as rejected,
               sum(total_faulty_SC) as total_faulty,
               sum(verification_pending_faulty_SC) verification_pending_faulty,
               sum(verified_faulty_SC) as verified_faulty,
               sum(approved_faulty_SC) as approved_faulty,
               gp_ward_code
               from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
               ) as MV ON main.location_id=MV.gp_ward_code";
            }
            if ($caste == 'ST') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select gram_panchyat_code as location_id,gram_panchyat_name as location_name
        from public.m_gp  " . $whereMain . "
               ) as main  LEFT JOIN
               (
               select 
               sum(partial_ST) as partial,
               sum(full_ST) as full,
               sum(verification_pending_ST) as verification_pending,
               sum(verified_ST) as verified,
               sum(approved_ST) as approved,
               sum(reverted_ST) as reverted,
               sum(rejected_ST) as rejected,
               sum(total_faulty_ST) as total_faulty,
               sum(verification_pending_faulty_ST) verification_pending_faulty,
               sum(verified_faulty_ST) as verified_faulty,
               sum(approved_faulty_ST) as approved_faulty,
               gp_ward_code
               from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
               )  as MV ON main.location_id=MV.gp_ward_code";
            }
       }
       else{
           $query ="select
           location_id,
           location_name,
           MV.*
           from (
            select gram_panchyat_code as location_id,gram_panchyat_name as location_name
            from public.m_gp  " . $whereMain . "
           ) as main
           LEFT JOIN
           (
           select
           sum(partial_OT+partial_SC+partial_ST) as partial,
           sum(full_OT+full_SC+full_ST) as full,
           sum(verification_pending_OT+verification_pending_SC+verification_pending_ST) as verification_pending,
           sum(verified_OT+verified_SC+verified_ST) as verified,
           sum(approved_OT+approved_SC+approved_ST) as approved,
           sum(reverted_OT+reverted_SC+reverted_ST) as reverted,
           sum(rejected_OT+rejected_SC+rejected_ST) as rejected,
           sum(total_faulty_OT+total_faulty_SC+total_faulty_ST) as total_faulty,
           sum(verification_pending_faulty_OT+verification_pending_faulty_SC+verification_pending_faulty_ST) as verification_pending_faulty,
           sum(verified_faulty_OT+verified_faulty_SC+verified_faulty_ST) as verified_faulty,
           sum(approved_faulty_OT+approved_faulty_SC+approved_faulty_ST) as approved_faulty,
           gp_ward_code
           from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by gp_ward_code
           ) as MV ON main.location_id=MV.gp_ward_code";

       }
       
   

       // echo $query;die;
       $result = DB::connection('pgsql_appwrite')->select($query);
       return $result;
    }
    public function getMuncWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        $whereCon = "where district_code=" . $district_code;
        //$whereCon .= " and created_by_local_body_code=" . $ulb_code;
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and sub_district_code=" . $ulb_code;
       if (!empty($caste)) {
           if ($caste == 'OTHERS') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select urban_body_code as location_id,urban_body_name as location_name
        from public.m_urban_body  " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_OT) as partial,
               sum(full_OT) as full,
               sum(verification_pending_OT) as verification_pending,
               sum(verified_OT) as verified,
               sum(approved_OT) as approved,
               sum(reverted_OT) as reverted,
               sum(rejected_OT) as rejected,
               sum(total_faulty_OT) as total_faulty,
               sum(verification_pending_faulty_OT) verification_pending_faulty,
               sum(verified_faulty_OT) as verified_faulty,
               sum(approved_faulty_OT) as approved_faulty,
               blk_munc_code
                 from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by blk_munc_code
                 ) as MV ON main.location_id=MV.blk_munc_code";
           } 
           if ($caste == 'SC') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select urban_body_code as location_id,urban_body_name as location_name
                from public.m_urban_body  " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_SC) as partial,
               sum(full_SC) as full,
               sum(verification_pending_SC) as verification_pending,
               sum(verified_SC) as verified,
               sum(approved_SC) as approved,
               sum(reverted_SC) as reverted,
               sum(rejected_SC) as rejected,
               sum(total_faulty_SC) as total_faulty,
               sum(verification_pending_faulty_SC) verification_pending_faulty,
               sum(verified_faulty_SC) as verified_faulty,
               sum(approved_faulty_SC) as approved_faulty,
               blk_munc_code
               from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by blk_munc_code
               ) as MV ON main.location_id=MV.blk_munc_code";
            }
            if ($caste == 'ST') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select urban_body_code as location_id,urban_body_name as location_name
                from public.m_urban_body  " . $whereMain . "
               ) as main  LEFT JOIN
               (
               select 
               sum(partial_ST) as partial,
               sum(full_ST) as full,
               sum(verification_pending_ST) as verification_pending,
               sum(verified_ST) as verified,
               sum(approved_ST) as approved,
               sum(reverted_ST) as reverted,
               sum(rejected_ST) as rejected,
               sum(total_faulty_ST) as total_faulty,
               sum(verification_pending_faulty_ST) verification_pending_faulty,
               sum(verified_faulty_ST) as verified_faulty,
               sum(approved_faulty_ST) as approved_faulty,
               blk_munc_code
               from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by blk_munc_code
               )  as MV ON main.location_id=MV.blk_munc_code";
            }
       }
       else{
           $query ="select
           location_id,
           location_name,
           MV.*
           from (
            select urban_body_code as location_id,urban_body_name as location_name
            from public.m_urban_body  " . $whereMain . "
           ) as main
           LEFT JOIN
           (
           select
           sum(partial_OT+partial_SC+partial_ST) as partial,
           sum(full_OT+full_SC+full_ST) as full,
           sum(verification_pending_OT+verification_pending_SC+verification_pending_ST) as verification_pending,
           sum(verified_OT+verified_SC+verified_ST) as verified,
           sum(approved_OT+approved_SC+approved_ST) as approved,
           sum(reverted_OT+reverted_SC+reverted_ST) as reverted,
           sum(rejected_OT+rejected_SC+rejected_ST) as rejected,
           sum(total_faulty_OT+total_faulty_SC+total_faulty_ST) as total_faulty,
           sum(verification_pending_faulty_OT+verification_pending_faulty_SC+verification_pending_faulty_ST) as verification_pending_faulty,
           sum(verified_faulty_OT+verified_faulty_SC+verified_faulty_ST) as verified_faulty,
           sum(approved_faulty_OT+approved_faulty_SC+approved_faulty_ST) as approved_faulty,
           blk_munc_code
           from lb_scheme.mv_phase_".$ds_phase."_gp_ward  " . $whereCon . " group by blk_munc_code
           ) as MV ON main.location_id=MV.blk_munc_code";

       }
       
   

       // echo $query;die;
       $result = DB::connection('pgsql_appwrite')->select($query);
       return $result;
    }
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        $whereCon = "where created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
       if (!empty($caste)) {
           if ($caste == 'OTHERS') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select block_code as location_id,block_name as location_name
        from public.m_block  " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_OT) as partial,
               sum(full_OT) as full,
               sum(verification_pending_OT) as verification_pending,
               sum(verified_OT) as verified,
               sum(approved_OT) as approved,
               sum(reverted_OT) as reverted,
               sum(rejected_OT) as rejected,
               sum(total_faulty_OT) as total_faulty,
               sum(verification_pending_faulty_OT) verification_pending_faulty,
               sum(verified_faulty_OT) as verified_faulty,
               sum(approved_faulty_OT) as approved_faulty,
               created_by_local_body_code
                 from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
                 ) as MV ON main.location_id=MV.created_by_local_body_code";
           } 
           if ($caste == 'SC') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select block_code as location_id,block_name as location_name
                from public.m_block  " . $whereMain . "
               ) as main LEFT JOIN
               (
               select 
               sum(partial_SC) as partial,
               sum(full_SC) as full,
               sum(verification_pending_SC) as verification_pending,
               sum(verified_SC) as verified,
               sum(approved_SC) as approved,
               sum(reverted_SC) as reverted,
               sum(rejected_SC) as rejected,
               sum(total_faulty_SC) as total_faulty,
               sum(verification_pending_faulty_SC) verification_pending_faulty,
               sum(verified_faulty_SC) as verified_faulty,
               sum(approved_faulty_SC) as approved_faulty,
               created_by_local_body_code
               from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
               ) as MV ON main.location_id=MV.created_by_local_body_code";
            }
            if ($caste == 'ST') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select block_code as location_id,block_name as location_name
                from public.m_block  " . $whereMain . "
               ) as main  LEFT JOIN
               (
               select 
               sum(partial_ST) as partial,
               sum(full_ST) as full,
               sum(verification_pending_ST) as verification_pending,
               sum(verified_ST) as verified,
               sum(approved_ST) as approved,
               sum(reverted_ST) as reverted,
               sum(rejected_ST) as rejected,
               sum(total_faulty_ST) as total_faulty,
               sum(verification_pending_faulty_ST) verification_pending_faulty,
               sum(verified_faulty_ST) as verified_faulty,
               sum(approved_faulty_ST) as approved_faulty,
               created_by_local_body_code
               from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
               )  as MV ON main.location_id=MV.created_by_local_body_code";
            }
       }
       else{
           $query ="select
           location_id,
           location_name,
           MV.*
           from (
            select block_code as location_id,block_name as location_name
            from public.m_block  " . $whereMain . "
           ) as main
           LEFT JOIN
           (
           select
           sum(partial_OT+partial_SC+partial_ST) as partial,
           sum(full_OT+full_SC+full_ST) as full,
           sum(verification_pending_OT+verification_pending_SC+verification_pending_ST) as verification_pending,
           sum(verified_OT+verified_SC+verified_ST) as verified,
           sum(approved_OT+approved_SC+approved_ST) as approved,
           sum(reverted_OT+reverted_SC+reverted_ST) as reverted,
           sum(rejected_OT+rejected_SC+rejected_ST) as rejected,
           sum(total_faulty_OT+total_faulty_SC+total_faulty_ST) as total_faulty,
           sum(verification_pending_faulty_OT+verification_pending_faulty_SC+verification_pending_faulty_ST) as verification_pending_faulty,
           sum(verified_faulty_OT+verified_faulty_SC+verified_faulty_ST) as verified_faulty,
           sum(approved_faulty_OT+approved_faulty_SC+approved_faulty_ST) as approved_faulty,
           created_by_local_body_code
           from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
           ) as MV ON main.location_id=MV.created_by_local_body_code";

       }
       
   

       // echo $query;die;
       $result = DB::connection('pgsql_appwrite')->select($query);
       return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
       
        $whereCon = "where created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
       if (!empty($caste)) {
           if ($caste == 'OTHERS') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select sub_district_code as location_id,sub_district_name||'-SubDivision' as location_name
                from public.m_sub_district  " . $whereMain . " 
               ) as main LEFT JOIN
               (
               select 
               sum(partial_OT) as partial,
               sum(full_OT) as full,
               sum(verification_pending_OT) as verification_pending,
               sum(verified_OT) as verified,
               sum(approved_OT) as approved,
               sum(reverted_OT) as reverted,
               sum(rejected_OT) as rejected,
               sum(total_faulty_OT) as total_faulty,
               sum(verification_pending_faulty_OT) verification_pending_faulty,
               sum(verified_faulty_OT) as verified_faulty,
               sum(approved_faulty_OT) as approved_faulty,
               created_by_local_body_code
                 from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
                 ) as MV ON main.location_id=MV.created_by_local_body_code";
           } 
           if ($caste == 'SC') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select sub_district_code as location_id,sub_district_name||'-SubDivision' as location_name
                from public.m_sub_district  " . $whereMain . " 
               ) as main LEFT JOIN
               (
               select 
               sum(partial_SC) as partial,
               sum(full_SC) as full,
               sum(verification_pending_SC) as verification_pending,
               sum(verified_SC) as verified,
               sum(approved_SC) as approved,
               sum(reverted_SC) as reverted,
               sum(rejected_SC) as rejected,
               sum(total_faulty_SC) as total_faulty,
               sum(verification_pending_faulty_SC) verification_pending_faulty,
               sum(verified_faulty_SC) as verified_faulty,
               sum(approved_faulty_SC) as approved_faulty,
               created_by_local_body_code
               from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
               ) as MV ON main.location_id=MV.created_by_local_body_code";
            }
            if ($caste == 'ST') {
               $query ="select
               location_id,
               location_name,
               MV.*
               from (
                select sub_district_code as location_id,sub_district_name||'-SubDivision' as location_name
                from public.m_sub_district  " . $whereMain . " 
               ) as main  LEFT JOIN
               (
               select 
               sum(partial_ST) as partial,
               sum(full_ST) as full,
               sum(verification_pending_ST) as verification_pending,
               sum(verified_ST) as verified,
               sum(approved_ST) as approved,
               sum(reverted_ST) as reverted,
               sum(rejected_ST) as rejected,
               sum(total_faulty_ST) as total_faulty,
               sum(verification_pending_faulty_ST) verification_pending_faulty,
               sum(verified_faulty_ST) as verified_faulty,
               sum(approved_faulty_ST) as approved_faulty,
               created_by_local_body_code
               from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
               )  as MV ON main.location_id=MV.created_by_local_body_code";
            }
       }
       else{
           $query ="select
           location_id,
           location_name,
           MV.*
           from (
            select sub_district_code as location_id,sub_district_name||'-SubDivision' as location_name
            from public.m_sub_district  " . $whereMain . " 
           ) as main
           LEFT JOIN
           (
           select
           sum(partial_OT+partial_SC+partial_ST) as partial,
           sum(full_OT+full_SC+full_ST) as full,
           sum(verification_pending_OT+verification_pending_SC+verification_pending_ST) as verification_pending,
           sum(verified_OT+verified_SC+verified_ST) as verified,
           sum(approved_OT+approved_SC+approved_ST) as approved,
           sum(reverted_OT+reverted_SC+reverted_ST) as reverted,
           sum(rejected_OT+rejected_SC+rejected_ST) as rejected,
           sum(total_faulty_OT+total_faulty_SC+total_faulty_ST) as total_faulty,
           sum(verification_pending_faulty_OT+verification_pending_faulty_SC+verification_pending_faulty_ST) as verification_pending_faulty,
           sum(verified_faulty_OT+verified_faulty_SC+verified_faulty_ST) as verified_faulty,
           sum(approved_faulty_OT+approved_faulty_SC+approved_faulty_ST) as approved_faulty,
           created_by_local_body_code
           from lb_scheme.mv_phase_".$ds_phase."_block_subdiv where created_by_dist_code=" . $district_code." group by created_by_local_body_code
           ) as MV ON main.location_id=MV.created_by_local_body_code";

       }
       
   

       // echo $query;die;
       $result = DB::connection('pgsql_appwrite')->select($query);
       return $result;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
        $whereCon = "where 1=1";
        if (!empty($caste)) {
            if ($caste == 'OTHERS') {
                $query ="select
                location_id,
                location_name,
                MV.*
                from (
                select district_code as location_id,district_name as location_name
                from public.m_district
                ) as main LEFT JOIN
                (
                select 
                sum(partial_OT) as partial,
                sum(full_OT) as full,
                sum(verification_pending_OT) as verification_pending,
                sum(verified_OT) as verified,
                sum(approved_OT) as approved,
                sum(reverted_OT) as reverted,
                sum(rejected_OT) as rejected,
                sum(total_faulty_OT) as total_faulty,
                sum(verification_pending_faulty_OT) verification_pending_faulty,
                sum(verified_faulty_OT) as verified_faulty,
                sum(approved_faulty_OT) as approved_faulty,
                created_by_dist_code
                  from lb_scheme.mv_phase_".$ds_phase."_block_subdiv group by created_by_dist_code
                  ) as MV ON main.location_id=MV.created_by_dist_code";
            } 
            if ($caste == 'SC') {
                $query ="select
                location_id,
                location_name,
                MV.*
                from (
                select district_code as location_id,district_name as location_name
                from public.m_district
                ) as main LEFT JOIN
                (
                select 
                sum(partial_SC) as partial,
                sum(full_SC) as full,
                sum(verification_pending_SC) as verification_pending,
                sum(verified_SC) as verified,
                sum(approved_SC) as approved,
                sum(reverted_SC) as reverted,
                sum(rejected_SC) as rejected,
                sum(total_faulty_SC) as total_faulty,
                sum(verification_pending_faulty_SC) verification_pending_faulty,
                sum(verified_faulty_SC) as verified_faulty,
                sum(approved_faulty_SC) as approved_faulty,
                created_by_dist_code
                from lb_scheme.mv_phase_".$ds_phase."_block_subdiv group by created_by_dist_code
                ) as MV ON main.location_id=MV.created_by_dist_code";
             }
             if ($caste == 'ST') {
                $query ="select
                location_id,
                location_name,
                MV.*
                from (
                    select district_code as location_id,district_name as location_name
                    from public.m_district
                ) as main  LEFT JOIN
                (
                select 
                sum(partial_ST) as partial,
                sum(full_ST) as full,
                sum(verification_pending_ST) as verification_pending,
                sum(verified_ST) as verified,
                sum(approved_ST) as approved,
                sum(reverted_ST) as reverted,
                sum(rejected_ST) as rejected,
                sum(total_faulty_ST) as total_faulty,
                sum(verification_pending_faulty_ST) verification_pending_faulty,
                sum(verified_faulty_ST) as verified_faulty,
                sum(approved_faulty_ST) as approved_faulty,
                created_by_dist_code
                from lb_scheme.mv_phase_".$ds_phase."_block_subdiv group by created_by_dist_code
                )  as MV ON main.location_id=MV.created_by_dist_code";
             }
        }
        else{
            $query ="select
            location_id,
            location_name,
            MV.*
            from (
            select district_code as location_id,district_name as location_name
            from public.m_district
            ) as main
            LEFT JOIN
            (
            select
            sum(partial_OT+partial_SC+partial_ST) as partial,
            sum(full_OT+full_SC+full_ST) as full,
            sum(verification_pending_OT+verification_pending_SC+verification_pending_ST) as verification_pending,
            sum(verified_OT+verified_SC+verified_ST) as verified,
            sum(approved_OT+approved_SC+approved_ST) as approved,
            sum(reverted_OT+reverted_SC+reverted_ST) as reverted,
            sum(rejected_OT+rejected_SC+rejected_ST) as rejected,
            sum(total_faulty_OT+total_faulty_SC+total_faulty_ST) as total_faulty,
            sum(verification_pending_faulty_OT+verification_pending_faulty_SC+verification_pending_faulty_ST) as verification_pending_faulty,
            sum(verified_faulty_OT+verified_faulty_SC+verified_faulty_ST) as verified_faulty,
            sum(approved_faulty_OT+approved_faulty_SC+approved_faulty_ST) as approved_faulty,
            created_by_dist_code
            from lb_scheme.mv_phase_".$ds_phase."_block_subdiv group by created_by_dist_code
            ) as MV ON main.location_id=MV.created_by_dist_code";

        }
        
    

        // echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    // DS 11
    public function getNormalEntryBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $ds_phase = NULL)
    {
        $whereCon = "created_by_dist_code =".$district_code;
        $whereMain = " WHERE district_code =".$district_code;
        if ($ds_phase == 0) {
            $dsPhaseCon = "a.ds_phase IS null";
        } else {
            $dsPhaseCon = "a.ds_phase = ".$ds_phase;
        }
        $query = " SELECT main.location_id AS created_by_local_body_code,
            main.location_name AS block_subdiv_name,
            main.created_by_dist_code,
            COALESCE(draft.total_application_submitted, 0::bigint) AS total_application_submitted,
            COALESCE(draft.partial, 0::bigint) AS partial,
            COALESCE(draft.full, 0::bigint) AS full,
            COALESCE(draft.verification_pending, 0::bigint) AS verification_pending,
            COALESCE(draft.verified, 0::bigint) AS verified,
            COALESCE(approve.approved, 0::bigint) AS approved,
            COALESCE(approvef.approved, 0::bigint) AS approved_f,
            COALESCE(rej.rejected, 0::bigint) AS rejected
        FROM (
            SELECT m_block.block_code AS location_id,
                m_block.block_name AS location_name,
                m_block.district_code AS created_by_dist_code
            FROM public.m_block".$whereMain.") main
        LEFT JOIN ( SELECT COUNT(1) AS total_application_submitted,
                count(1) FILTER (WHERE a.is_final = false) AS partial,
                count(1) FILTER (WHERE a.is_final = true) AS full,
                count(1) FILTER (WHERE a.is_final = true AND a.next_level_role_id IS NULL) AS verification_pending,
                count(1) FILTER (WHERE a.is_final = true AND a.next_level_role_id > 0 AND a.next_level_role_id <> 9999) AS verified,
                a.created_by_local_body_code
            FROM lb_scheme.draft_ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) draft ON main.location_id = draft.created_by_local_body_code
        LEFT JOIN ( SELECT count(1) FILTER (WHERE a.next_level_role_id <> 9999) AS approved,
                a.created_by_local_body_code
            FROM lb_scheme.ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) approve ON main.location_id = approve.created_by_local_body_code
        LEFT JOIN ( SELECT count(1) AS rejected,
                a.created_by_local_body_code
            FROM lb_scheme.ben_reject_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) rej ON main.location_id = rej.created_by_local_body_code
        LEFT JOIN ( SELECT count(1) FILTER (WHERE a.next_level_role_id <> 9999) AS approved,
                a.created_by_local_body_code
            FROM lb_scheme.faulty_ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) approvef ON main.location_id = approvef.created_by_local_body_code";
        // dd($query);
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    public function getNormalEntrySubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $ds_phase = NULL)
    {
        $whereCon = "created_by_dist_code =".$district_code;
        $whereMain = " WHERE district_code =".$district_code;
        if ($ds_phase == 0) {
            $dsPhaseCon = "a.ds_phase IS null";
        } else {
            $dsPhaseCon = "a.ds_phase = ".$ds_phase;
        }
        $query = " SELECT main.location_id AS created_by_local_body_code,
            main.location_name AS block_subdiv_name,
            main.created_by_dist_code,
            COALESCE(draft.total_application_submitted, 0::bigint) AS total_application_submitted,
            COALESCE(draft.partial, 0::bigint) AS partial,
            COALESCE(draft.full, 0::bigint) AS full,
            COALESCE(draft.verification_pending, 0::bigint) AS verification_pending,
            COALESCE(draft.verified, 0::bigint) AS verified,
            COALESCE(approve.approved, 0::bigint) AS approved,
            COALESCE(approvef.approved, 0::bigint) AS approved_f,
            COALESCE(rej.rejected, 0::bigint) AS rejected
        FROM (
            SELECT m_sub_district.sub_district_code AS location_id,
                m_sub_district.sub_district_name AS location_name,
                m_sub_district.district_code AS created_by_dist_code
            FROM public.m_sub_district".$whereMain.") main
        LEFT JOIN ( SELECT COUNT(1) AS total_application_submitted,
                count(1) FILTER (WHERE a.is_final = false) AS partial,
                count(1) FILTER (WHERE a.is_final = true) AS full,
                count(1) FILTER (WHERE a.is_final = true AND a.next_level_role_id IS NULL) AS verification_pending,
                count(1) FILTER (WHERE a.is_final = true AND a.next_level_role_id > 0 AND a.next_level_role_id <> 9999) AS verified,
                a.created_by_local_body_code
            FROM lb_scheme.draft_ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) draft ON main.location_id = draft.created_by_local_body_code
        LEFT JOIN ( SELECT count(1) FILTER (WHERE a.next_level_role_id <> 9999) AS approved,
                a.created_by_local_body_code
            FROM lb_scheme.ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) approve ON main.location_id = approve.created_by_local_body_code
        LEFT JOIN ( SELECT count(1) AS rejected,
                a.created_by_local_body_code
            FROM lb_scheme.ben_reject_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) rej ON main.location_id = rej.created_by_local_body_code
        LEFT JOIN ( SELECT count(1) FILTER (WHERE a.next_level_role_id <> 9999) AS approved,
                a.created_by_local_body_code
            FROM lb_scheme.faulty_ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date AND a.".$whereCon."
            GROUP BY a.created_by_local_body_code) approvef ON main.location_id = approvef.created_by_local_body_code";
        // dd($query);
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    public function getNormalEntryDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $ds_phase = NULL)
    {
        $whereCon = "created_by_dist_code =".$district_code;
        $whereMain = " WHERE district_code =".$district_code;
        if ($ds_phase == 0) {
            $dsPhaseCon = "a.ds_phase IS null";
        } else {
            $dsPhaseCon = "a.ds_phase = ".$ds_phase;
        }
        $query = " SELECT main.location_id AS created_by_local_body_code,
            main.location_name AS block_subdiv_name,
            main.created_by_dist_code,
            COALESCE(draft.total_application_submitted, 0::bigint) AS total_application_submitted,
            COALESCE(draft.partial, 0::bigint) AS partial,
            COALESCE(draft.full, 0::bigint) AS full,
            COALESCE(draft.verification_pending, 0::bigint) AS verification_pending,
            COALESCE(draft.verified, 0::bigint) AS verified,
            COALESCE(approve.approved, 0::bigint) AS approved,
            COALESCE(approvef.approved, 0::bigint) AS approved_f,
            COALESCE(rej.rejected, 0::bigint) AS rejected
        FROM (
            SELECT m_district.district_code AS location_id,
                m_district.district_name AS location_name,
                m_district.district_code AS created_by_dist_code
            FROM public.m_district) main
        LEFT JOIN ( SELECT COUNT(1) AS total_application_submitted,
                count(1) FILTER (WHERE a.is_final = false) AS partial,
                count(1) FILTER (WHERE a.is_final = true) AS full,
                count(1) FILTER (WHERE a.is_final = true AND a.next_level_role_id IS NULL) AS verification_pending,
                count(1) FILTER (WHERE a.is_final = true AND a.next_level_role_id > 0 AND a.next_level_role_id <> 9999) AS verified,
                a.created_by_dist_code
            FROM lb_scheme.draft_ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date
            GROUP BY a.created_by_dist_code) draft ON main.location_id = draft.created_by_dist_code
        LEFT JOIN ( SELECT count(1) FILTER (WHERE a.next_level_role_id <> 9999) AS approved,
                a.created_by_dist_code
            FROM lb_scheme.ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date
            GROUP BY a.created_by_dist_code) approve ON main.location_id = approve.created_by_dist_code
        LEFT JOIN ( SELECT count(1) AS rejected,
                a.created_by_dist_code
            FROM lb_scheme.ben_reject_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date
            GROUP BY a.created_by_dist_code) rej ON main.location_id = rej.created_by_dist_code
        LEFT JOIN ( SELECT count(1) FILTER (WHERE a.next_level_role_id <> 9999) AS approved,
                a.created_by_dist_code
            FROM lb_scheme.faulty_ben_personal_details a
            WHERE ".$dsPhaseCon." AND created_at::date >= '".$fromdate."'::date AND created_at::date <= '".$todate."'::date
            GROUP BY a.created_by_dist_code) approvef ON main.location_id = approvef.created_by_dist_code";
        // dd($query);
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }
}
