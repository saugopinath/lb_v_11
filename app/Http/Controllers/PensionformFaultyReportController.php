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

class PensionformFaultyReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
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
    public function schemeSelection(Request $request)
    {
        $report_type = '';

        if ($request->has('type')) {
            $report_type = $request->get('type');
            if ($report_type == 'A') {
                $report_type_name = 'Faulty Approved List';
            } else if ($report_type == 'V') {
                $report_type_name = 'Faulty Pending for Approval';
            } else if ($report_type == 'VP') {
                $report_type_name = 'Faulty Pending for Verification';
            } else if ($report_type == 'T') {
                $report_type_name = 'Faulty Reverted List';
            } else if ($report_type == 'R') {
                $report_type_name = 'Faulty Rejected List';
            } else {
                return redirect('/')->with('error', 'Error: Report type invalid');
            }
        } else {
            return redirect('/')->with('error', 'Signature Error: Report Type not selected');
        }
    }
    public function schemeSessionCheck(Request $request)
    {
        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $request->session()->put('level', $roleObj['mapping_level']);
                $distCode = $roleObj['district_code'];
                $request->session()->put('distCode', $roleObj['district_code']);
                $request->session()->put('scheme_id', $scheme_id);
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
            return true;
        } else {
            return false;
        }
    }

    public function applicationStatusList(Request $request)
    {
        //$ds_phase_list = Config::get('constants.ds_phase.phaselist');
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        if ($this->schemeSessionCheck($request)) {
            $mappingLevel = $request->session()->get('level');
            $role_name = Auth::user()->designation_id;
            //$rejection_cause_list = Config::get('constants.rejection_cause');
            $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
            $is_rural_visible = 0;
            $urban_visible = 0;
            $munc_visible = 0;
            $gp_ward_visible = 0;
            $muncList = collect([]);
            $gpwardList = collect([]);
            $modelName = new DataSourceCommon;
            $getModelFunc = new getModelFunc();
            $caste = $request->caste;
            $block_ulb_code = $request->block_ulb_code;
            $gp_ward_code = $request->gp_ward_code;
            if ($role_name == 'Approver' || $role_name == 'Delegated Approver') {
                $is_urban = $request->rural_urbanid;
                $district_code = $request->session()->get('distCode');
                $urban_body_code = $request->urban_body_code;
                $block_ulb_code = $request->block_ulb_code;
                $is_rural_visible = 1;
                $urban_visible = 1;
                $munc_visible = 1;
                $gp_ward_visible = 1;
            } else if ($role_name == 'Verifier' || $role_name == 'Delegated Verifier' || $role_name == 'Operator') {
                $district_code = $request->session()->get('distCode');
                if ($mappingLevel == 'Block') {
                    $block_ulb_code = NULL;
                    $is_rural_visible = 0;
                    $is_urban = 2;
                    $munc_visible = 0;
                    $urban_body_code = $request->session()->get('bodyCode');
                    $block_ulb_code = NULL;
                    $gpwardList = GP::where('block_code', $urban_body_code)->get();
                    $gp_ward_visible = 1;
                } else if ($mappingLevel == 'Subdiv') {
                    $block_ulb_code = $request->block_ulb_code;
                    $urban_body_code = $request->session()->get('bodyCode');
                    $is_rural_visible = 0;
                    $is_urban = 1;
                    $munc_visible = 1;
                    $gp_ward_visible = 1;
                    $muncList = UrbanBody::where('sub_district_code', $urban_body_code)->get();
                    $block_ulb_code = $request->block_ulb_code;
                }
            }
            $condition = array();

            //$report_type N - Total List, V-Verified List, R-Recomender List, A-Approved List, T- Rejected List 
            $report_type = 'N';
            $report_type_name = 'Faulty Beneficiary List';
            if ($request->has('type')) {
                $report_type = $request->get('type');
                if ($report_type == 'V') {
                    $report_type_name = 'Faulty without Doc Pending for Approval';
                    $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1, 1);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 1;
                    $column = 'ss_ben_id';
                    $column1 = 'ss_ben_id';
                    $column2 = 'ss_ben_id';
                    $column3 = 'ss_ben_id';
                    $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3, 1);
                    $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4, 1);
                } else if ($report_type == 'A') {
                    $is_draft = NULL;
                    $report_type_name = 'Faulty without Doc Approved Beneficiary List';
                    $condition['next_level_role_id'] = 0;
                    $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1, $is_draft);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $column = 'beneficiary_id';
                    $column1 = 'ss_ben_id';
                    $column2 = 'ss_ben_id';
                    $column3 = 'ss_ben_id';
                    $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3);
                    $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4, $is_draft);
                } else if ($report_type == 'R') {
                    $report_type_name = 'Faulty without Doc Rejected Application List';
                    //$condition['next_level_role_id'] = '-100';
                    $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 10);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 2;
                    $column = 'ss_ben_id';
                    $column1 = 'rejected_cause';
                    $column2 = 'u.mobile_no';
                    $column3 = 'ss_ben_id';
                    $contact_table =  $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 10);
                } else if ($report_type == 'T') {
                    $report_type_name = 'Faulty without Doc Reverted Application List';
                    $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1, 1);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 1;
                    $condition['next_level_role_id'] = '-50';
                    $column = 'ss_ben_id';
                    $column1 = 'rejected_cause';
                    $column2 = 'u.mobile_no';
                    $column3 = 'ss_ben_id';
                    $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3, 1);
                    $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4, 1);
                } else if ($report_type == 'VP') {
                    $report_type_name = 'Faulty without Doc Pending for Verification';
                    $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1, 1);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 1;
                    $column = 'ss_ben_id';
                    $column1 = 'ss_ben_id';
                    $column2 = 'ss_ben_id';
                    $column3 = 'bank_code';
                    $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3, 1);
                    $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4, 1);
                } else {
                    return redirect('/')->with('error', 'Error: Report type invalid');
                }
            } else {
                return redirect('/')->with('error', 'Signature Error: Report Type not selected');
            }
            if (request()->ajax()) {

                if (!empty($request->ds_phase)) {
                    $condition["ds_phase"] = $request->ds_phase;
                }

                // District Filter
                if (!empty($district_code)) {
                    $condition[$Table . ".created_by_dist_code"] = $district_code;
                }
                //dd();
                if (!empty($is_urban)) {
                    // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
                    if ($is_urban == 2) {
                        if (!empty($urban_body_code)) {
                            //$condition["rural_urban_id"] = 2;
                            $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                        }
                    }
                    //'Urban'
                    if ($is_urban == 1) {
                        if (!empty($urban_body_code)) {
                            //$condition["rural_urban_id"] = 1;
                            $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                        }
                        if (!empty($block_ulb_code)) {
                            $condition[$contact_table . ".block_ulb_code"] = $block_ulb_code;
                        }
                    }
                }
                if (!empty($gp_ward_code)) {
                    $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;
                }
                if (!empty($caste)) {
                    $condition[$Table . ".caste"] = $caste;
                }
                //For Operator



                $serachvalue = $request->search['value'];
                $limit = $request->input('length');
                $offset = $request->input('start');

                $totalRecords = 0;
                $filterRecords = 0;
                $data = array();
                // $model_name = $request->session()->get('model_name');
                $query = $modelName->where($condition);

                if ($report_type != 'R') {
                    $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
                    $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
                }
                if ($report_type == 'R' || $report_type == 'T') {
                    $query = $query->leftjoin('public.users as u', 'u.id', '=', $Table . '.created_by');
                }


                if ($report_type == 'V') { //Verified List
                    $is_draft = 1;
                    $query = $query->where('next_level_role_id', '>', 0);
                    $query = $query->where('next_level_role_id', '!=', 9999);
                    $query = $query->where('is_final', true);
                    $query = $query->where('enq_iseligible', 1);
                    $query = $query->where('ver_iseligible', 1);
                    $query = $query->where('is_migrated', true);
                }
                if ($report_type == 'A') {
                    $query = $query->where('next_level_role_id', 0);
                    $query = $query->where('next_level_role_id', '!=', 9999);
                    $query = $query->where('enq_iseligible', 1);
                    $query = $query->where('ver_iseligible', 1);
                }
                if ($report_type == 'T') {
                    $query = $query->where('next_level_role_id', -50);
                }
                if ($report_type == 'VP') {
                    $query = $query->whereNull('next_level_role_id');
                    $query = $query->where('is_final', true);
                    $query = $query->where('enq_iseligible', 1);
                    $query = $query->where('is_migrated', true);
                }
                if ($report_type == 'R') {
                    $query = $query->where('is_faulty', true);
                }
                if (empty($serachvalue)) {
                    $totalRecords = $query->count($Table . '.application_id');
                    // dd($query);
                    $data = $query->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->offset($offset)->limit($limit)->get([
                        '' . $Table . '.' . $column . ' as beneficiary_id', '' . $Table . '.application_id as application_id',
                        '' . $column1 . '  as rejected_reason',
                        '' . $column2 . '  as enter_by_mobile_no',
                        '' . $column3 . '  as bank_code',
                        'ben_fname', 'aadhar_no', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname',  '' . $Table . '.mobile_no as applicant_mobile_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id', 'bank_ifsc', 'bank_code'

                    ]);
                    // dd($data);
                } else {
                    if (preg_match('/^[0-9]*$/', $serachvalue)) {
                        $query = $query->where(function ($query1) use ($serachvalue, $Table) {
                            if (strlen($serachvalue) < 10) {
                                $query1->where($Table . '.application_id', $serachvalue);
                            } else if (strlen($serachvalue) == 10) {
                                $query1->where($Table . '.mobile_no', $serachvalue);
                            } else if (strlen($serachvalue) == 17) {
                                $query1->where('ss_card_no', $serachvalue);
                            } else if (strlen($serachvalue) == 20) {
                                $query1->where('duare_sarkar_registration_no', $serachvalue);
                            }
                        });
                        $totalRecords = $query->count($Table . '.application_id');
                        $data = $query->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->offset($offset)->limit($limit)->get(
                            [
                                '' . $Table . '.' . $column . ' as beneficiary_id', '' . $Table . '.application_id as application_id',
                                '' . $column1 . '  as rejected_reason',
                                '' . $column2 . '  as enter_by_mobile_no',
                                '' . $column3 . '  as bank_code',

                                'ben_fname',  'aadhar_no', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', '' . $Table . '.mobile_no as applicant_mobile_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id', 'bank_ifsc', 'bank_code'

                            ]
                        );
                    } else {
                        $query = $query->where(function ($query1) use ($serachvalue) {
                            $query1->where('ben_fname', 'like', $serachvalue . '%');
                        });
                        $totalRecords = $query->count($Table . '.application_id');
                        $data = $query->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->offset($offset)->limit($limit)->get(
                            [
                                '' . $Table . '.' . $column . ' as beneficiary_id', '' . $Table . '.application_id as application_id',
                                '' . $column1 . '  as rejected_reason',
                                '' . $column2 . '  as enter_by_mobile_no',
                                '' . $column3 . '  as bank_code',
                                'ben_fname', 'aadhar_no', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', '' . $Table . '.mobile_no as applicant_mobile_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id',  'bank_ifsc', 'bank_code'

                            ]
                        );
                    }
                    $filterRecords = count($data);
                }

                return datatables()
                    ->of($data)
                    ->setTotalRecords($totalRecords)
                    ->setFilteredRecords($filterRecords)
                    ->skipPaging()
                    ->addColumn('application_id', function ($data) use ($report_type) {

                        return $data->application_id;
                    })->addColumn('beneficiary_id', function ($data) use ($report_type) {
                        if ($report_type == 'A')
                            return $data->beneficiary_id;
                        else
                            return NULL;
                    })
                    ->addColumn('name', function ($data) {
                        return $data->getName();
                    })->addColumn('ss_card_no', function ($data) {
                        return $data->ss_card_no;
                    })
                    ->addColumn('ben_fname', function ($data) {
                        return $data->ben_fname;
                    })
                    ->addColumn('ben_mname', function ($data) {
                        return $data->ben_mname;
                    })
                    ->addColumn('ben_lname', function ($data) {
                        return $data->ben_lname;
                    })->addColumn('father_fname', function ($data) {
                        return $data->father_fname;
                    })->addColumn('father_mname', function ($data) {
                        return $data->father_mname;
                    })->addColumn('father_lname', function ($data) {
                        return $data->father_lname;
                    })->addColumn('father_name', function ($data) {
                        return ($data->father_fname . ' ' . $data->father_mname . ' ' . $data->father_lname);
                    })->addColumn('mobile_no', function ($data) {
                        return $data->applicant_mobile_no;
                    })->addColumn('ben_age', function ($data) {
                        //return $data->age_ason_01012021;
                        $ben_age = $this->ageCalculate($data->dob);
                        return $ben_age;
                    })
                    ->addColumn('bank_ifsc', function ($data) {
                        return $data->bank_ifsc;
                    })->addColumn('bank_code', function ($data) {
                        return $data->bank_code;
                    })->addColumn('enter_by_mobile_no', function ($data) use ($report_type) {
                        if ($report_type == 'R' || $report_type == 'T')
                            return $data->enter_by_mobile_no;
                        else
                            return NULL;
                    })->addColumn('applicant_mobile_no', function ($data) use ($report_type) {
                        if ($report_type == 'F')
                            return $data->applicant_mobile_no;
                        else
                            return NULL;
                    })->addColumn('rejected_reason', function ($data) use ($report_type, $rejection_cause_list) {
                        if ($report_type == 'R' || $report_type == 'T') {
                            $description = '';
                            foreach ($rejection_cause_list as $rejArr) {
                                if ($rejArr['id'] == $data->rejected_reason) {
                                    $description = $rejArr['reason'];
                                    break;
                                }
                            }
                            return $description;
                        } else
                            return NULL;
                    })->make(true);
            } else {
                $download_excel=0;
                $errormsg = Config::get('constants.errormsg');
                $scheme_name_arr = Scheme::select('scheme_name')->where('id', $this->scheme_id)->first();
                return view('pensionreportFaulty.index')
                    ->with('district_code', $request->session()->get('distCode'))
                    ->with('scheme', $request->session()->get('scheme_id'))
                    // ->with('schemetype','$schemetype')
                    ->with('report_type_name', $report_type_name)
                    ->with('type', $request->get('type'))
                    ->with('is_rural_visible', $is_rural_visible)
                    ->with('is_urban', $is_urban)
                    ->with('urban_visible', $urban_visible)
                    ->with('urban_body_code', $urban_body_code)
                    ->with('urban_visible', $urban_visible)
                    ->with('munc_visible', $munc_visible)
                    ->with('gp_ward_visible', $gp_ward_visible)
                    ->with('muncList', $muncList)
                    ->with('gpwardList', $gpwardList)
                    ->with('mappingLevel', $mappingLevel)
                    ->with('sessiontimeoutmessage', $errormsg['sessiontimeOut'])
                    ->with('ds_phase_list',  $ds_phase_list)
                     ->with('download_excel',  $download_excel)
                    ->with('scheme_name',  $scheme_name_arr->scheme_name);
            }
        } else {
            return redirect('/')->with('error', 'User not Authorized for this scheme');
        }
    }

    public function rejectApplication(Request $request)
    {
        $ben_id = $request->ben_id;

        $model_name = $request->session()->get('model_name');

        $role_id = $request->session()->get('role_id');
        $scheme_id = $request->session()->get('scheme_id');

        $user_id = Auth::user()->id;
        //$reject_reason = $request->reject_reason;
        $reject_reason = 'Rejected by user: ' . $user_id;
        DB::beginTransaction();
        try {

            $input_update = ['next_level_role_id' => -1, 'comments' => $reject_reason];
            $model_name::where('id', $ben_id)->where('lot_generated', 0)->where('payment_count', 0)->update($input_update);
        } catch (\Exception $e) {
            DB::rollback();
        }
        DB::commit();
    }

    public function revertApplication(Request $request)
    {
        $ben_id = $request->ben_id;

        $model_name = $request->session()->get('model_name');

        $role_id = $request->session()->get('role_id');
        $scheme_id = $request->session()->get('scheme_id');

        $user_id = Auth::user()->id;
        //$reject_reason = $request->reject_reason;
        $revert_reason = 'Reverted by user: ' . $user_id;
        DB::beginTransaction();
        try {

            $input_update = ['next_level_role_id' => null, 'comments' => $revert_reason];
            $model_name::where('id', $ben_id)->where('lot_generated', 0)->where('payment_count', 0)->update($input_update);
        } catch (\Exception $e) {
            DB::rollback();
        }
        DB::commit();
    }

    public function reject_duplicates(Request $request)
    {
        $scheme_code = $request->input('scheme_code');
        $user_id = Auth::user()->id;
        $duty = Configduty::where('user_id', '=', $user_id)->first();
        $dist_code = $duty->district_code;

        $scheme_prefix = "";
        if ($scheme_code == 10)
            $scheme_prefix = "oap_wcd";
        else if ($scheme_code == 11)
            $scheme_prefix = "wp_wcd";
        $query = "update " . $scheme_prefix . ".beneficiary set next_level_role_id=-3, av_status=-3 where id = ANY(
            select id from " . $scheme_prefix . ".mv_beneficiary_duplicate where rk>1 and lot_generated=0 and payment_count=0 
            and created_by_dist_code=" . $dist_code . ") and lot_generated=0 and payment_count=0";

        DB::connection('pgsql')->select($query);

        return "true";
    }


    public function generate_excel(Request $request)
    {

        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                $district_code = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }

        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $report_type = $request->get('type');
        $condition = array();
        $role_name = Auth::user()->designation_id;
        $scheme_name_row = Scheme::where('id', $scheme_id)->first();
        $scheme_name = $scheme_name_row->scheme_name;
        //dd($scheme_name);
        if ($report_type == 'V') {
            $report_type_name = 'Faulty without Doc Pending for Approval';
            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3, 1);
            $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4, 1);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier' || $role_name == 'Delegated Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->where('next_level_role_id', '>', 0);
            $query = $query->where('next_level_role_id', '!=', 9999);
            $query = $query->where('is_final', true);
            $query = $query->where('enq_iseligible', 1);
            $query = $query->where('ver_iseligible', 1);
            $query = $query->where('is_migrated', true);
            $data = $query->select(
                '' . $Table . '.application_id as application_id',
                'ben_fname',
                'ben_mname',
                'ben_lname',
                'father_fname',
                'father_mname',
                'father_lname',
                'mother_fname',
                'mother_mname',
                'mother_lname',
                'mobile_no',
                'dob',
                'age_ason_01012021',
                'caste',
                'ss_card_no',
                'next_level_role_id',
                'duare_sarkar_registration_no',
                'block_ulb_name',
                'gp_ward_name',
                'bank_ifsc',
                'bank_code',
                'ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $sws_card_no = (string) $row->ss_card_no;
                    if (!empty($sws_card_no))
                        $ss_card_no = "'$sws_card_no'";
                    else
                        $ss_card_no = '';
                    $bank_code = (string) $row->bank_code;
                    if (!empty($bank_code))
                        $f_bank_code = "'$bank_code'";
                    else
                        $f_bank_code = '';
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="12">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'A') {
            $report_type_name = 'Faulty without Doc Approved Beneficiary List';
            $condition['next_level_role_id'] = 0;
            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3);
            $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier' || $role_name == 'Delegated Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->where('next_level_role_id', 0);
            $query = $query->where('next_level_role_id', '!=', 9999);
            $query = $query->where('enq_iseligible', 1);
            $query = $query->where('ver_iseligible', 1);
            $data = $query->select(
                '' . $Table . '.application_id as application_id',
                '' . $Table . '.beneficiary_id as beneficiary_id',
                'ben_fname',
                'ben_mname',
                'ben_lname',
                'father_fname',
                'father_mname',
                'father_lname',
                'mother_fname',
                'mother_mname',
                'mother_lname',
                'mobile_no',
                'dob',
                'age_ason_01012021',
                'caste',
                'ss_card_no',
                'next_level_role_id',
                'duare_sarkar_registration_no',
                'block_ulb_name',
                'gp_ward_name',
                'bank_ifsc',
                'bank_code',
                'ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            //dd($data);
            /*$excel_data[] = array(
                    'Application ID', 'Beneficiary ID', 'Applicant Name', 'Applicant Mobile No.', 'Father\'s Name', 'Age', 'Caste',
                    'Swasthyasathi Card No.', 'Block/Municipality', 'GP/WARD', 'Bank IFSC', 'Bank Account No.'
                );*/
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Beneficiary ID</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $sws_card_no = (string) $row->ss_card_no;
                    if (!empty($sws_card_no))
                        $ss_card_no = "'$sws_card_no'";
                    else
                        $ss_card_no = '';
                    $bank_code = (string) $row->bank_code;
                    if (!empty($bank_code))
                        $f_bank_code = "'$bank_code'";
                    else
                        $f_bank_code = '';
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . $row->beneficiary_id . "</td><td>" . trim($row->ben_fname) . " " . trim($row->ben_mname) .  "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="13">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'R') {
            $report_type_name = 'Faulty without Doc Rejected Application List';
           // $condition['next_level_role_id'] = '-100';
            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 10);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier' || $role_name == 'Delegated Verifier' ) {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin('public.users as u', 'u.id', '=', $Table . '.created_by');
            $query = $query->leftjoin('public.m_reject_revert_reason_master as rm', 'rm.id', '=', $Table . '.rejected_cause');
            $query = $query->where('is_faulty', true);
            $data = $query->select(
                'application_id',
                'ben_fname',
                'ben_mname',
                'ben_lname',
                'father_fname',
                'father_mname',
                'father_lname',
                'mother_fname',
                'mother_mname',
                'mother_lname',
                '' . $Table . '.mobile_no as mobile_no',
                'u.mobile_no as enter_by_mobile_no',
                'rm.reason as reason',
                'dob',
                'age_ason_01012021',
                'caste',
                'ss_card_no',
                'next_level_role_id',
                'duare_sarkar_registration_no',
                'block_ulb_name',
                'gp_ward_name',
                'bank_ifsc',
                'bank_code',
                'ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($Table . '.gp_ward_name')->get();
            //dd($data);
            /* $excel_data[] = array(
                    'Application ID', 'Applicant Name', 'Applicant Mobile No.', 'Father\'s Name', 'Age', 'Caste',
                    'Swasthyasathi Card No.', 'Operator Mobile NO.', 'Rejected Reason', 'Block/Municipality', 'GP/WARD', 'Bank IFSC', 'Bank Account No.'
                );*/
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Operator Mobile NO.</th><th>Rejected Reason</th><th>Block/Municipality</th><th>GP/WARD</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $sws_card_no = (string) $row->ss_card_no;
                    if (!empty($sws_card_no))
                        $ss_card_no = "'$sws_card_no'";
                    else
                        $ss_card_no = '';
                    $bank_code = (string) $row->bank_code;
                    if (!empty($bank_code))
                        $f_bank_code = "'$bank_code'";
                    else
                        $f_bank_code = '';
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . $row->enter_by_mobile_no . "</td><td>" . $row->reason . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="14">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'T') {
            $report_type_name = 'Faulty without Doc Reverted Application List';
            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3, 1);
            $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4, 1);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier' || $role_name == 'Delegated Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin('public.users as u', 'u.id', '=', $Table . '.created_by');
            $query = $query->leftjoin('public.m_reject_revert_reason_master as rm', 'rm.id', '=', $Table . '.rejected_cause');
            $query = $query->where('next_level_role_id', -50);
            $data = $query->select(
                '' . $Table . '.application_id as application_id',
                'ben_fname',
                'ben_mname',
                'ben_lname',
                'father_fname',
                'father_mname',
                'father_lname',
                'mother_fname',
                'mother_mname',
                'mother_lname',
                '' . $Table . '.mobile_no as mobile_no',
                'u.mobile_no as enter_by_mobile_no',
                'rm.reason as reason',
                'dob',
                'age_ason_01012021',
                'caste',
                'ss_card_no',
                'next_level_role_id',
                'duare_sarkar_registration_no',
                'block_ulb_name',
                'gp_ward_name',
                'bank_ifsc',
                'bank_code',
                'ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            //dd($data->toArray());

            $excel_data[] = array(
                'Application ID', 'Applicant Name', 'Applicant Mobile No.', 'Fathers Name', 'Age', 'Caste',
                'Swasthyasathi Card No.', 'Operator Mobile NO.', 'Reverted Reason', 'Block/Municipality', 'GP/WARD', 'Bank IFSC', 'Bank Account No.'
            );
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Operator Mobile NO.</th><th>Reverted Reason</th><th>Block/Municipality</th><th>GP/WARD</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $sws_card_no = (string) $row->ss_card_no;
                    if (!empty($sws_card_no))
                        $ss_card_no = "'$sws_card_no'";
                    else
                        $ss_card_no = '';
                    $bank_code = (string) $row->bank_code;
                    if (!empty($bank_code))
                        $f_bank_code = "'$bank_code'";
                    else
                        $f_bank_code = '';
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . $row->enter_by_mobile_no . "</td><td>" . $row->reason . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="14">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'VP') {
            $report_type_name = 'Faulty without Doc Pending for Verification';
            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 3, 1);
            $bank_table = $getModelFunc->getTableFaultyWOutDoc($district_code, '', 4, 1);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier'  || $role_name == 'Delegated Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->whereNull('next_level_role_id');
            $query = $query->where('is_final', true);
            $query = $query->where('enq_iseligible', 1);
            $query = $query->where('is_migrated', true);
            $data = $query->select(
                '' . $Table . '.application_id as application_id',
                'ben_fname',
                'ben_mname',
                'ben_lname',
                'father_fname',
                'father_mname',
                'father_lname',
                'mother_fname',
                'mother_mname',
                'mother_lname',
                'mobile_no',
                'dob',
                'age_ason_01012021',
                'caste',
                'ss_card_no',
                'next_level_role_id',
                'duare_sarkar_registration_no',
                'block_ulb_name',
                'gp_ward_name',
                'bank_ifsc',
                'bank_code',
                'ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $sws_card_no = (string) $row->ss_card_no;
                    if (!empty($sws_card_no))
                        $ss_card_no = "'$sws_card_no'";
                    else
                        $ss_card_no = '';
                    $bank_code = (string) $row->bank_code;
                    if (!empty($bank_code))
                        $f_bank_code = "'$bank_code'";
                    else
                        $f_bank_code = '';
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="12">No Records found</td></tr>';
            }
            echo '</table>';
        }
    }
    function ageCalculate($dob)
    {
        $diff = 0;
        if ($dob != '') {
            // $diff = $this->ageCalculate($dob);
            $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
        }
        return $diff;
    }
    function getPhaseDes($phase_code)
    {
        $des = 'Phase-II';
        $phaseArr = DsPhase::where('phase_code', $phase_code)->first();
        if (!empty($phaseArr)) {
            $des = $phaseArr->phase_des;
        }
        return $des;
    }
}
