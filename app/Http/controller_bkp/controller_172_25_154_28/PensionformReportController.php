<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Configduty;
use App\District;
use App\Scheme;
use Auth;
use Config;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\DataSourceCommon;
use App\getModelFunc;
use App\UrbanBody;
use App\GP;
use App\RejectRevertReason;
use Carbon\Carbon;
use App\DsPhase;

class PensionformReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set('Asia/Kolkata');
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
        $phaseArr = DsPhase::where('is_current', TRUE)->first();
       // $mydate = $phaseArr->base_dob;
        $mydate =date('Y-m-d');
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
            if ($report_type == 'V') {
                $report_type_name = 'Verified Beneficiary List Report';
            } else if ($report_type == 'A') {
                $report_type_name = 'Approved Beneficiary List Report';
            } else if ($report_type == 'T') {
                $report_type_name = 'Reverted Beneficiary List Report';
            } else if ($report_type == 'R') {
                $report_type_name = 'Rejected Beneficiary List Report';
            } else if ($report_type == 'F') {
                $report_type_name = 'Faulty Application List Report';
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
        // dd('ok');
        // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
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
            $download_excel = 1;
            if ($role_name == 'Approver' || $role_name == 'Delegated Approver') {
                $is_urban = $request->rural_urbanid;
                $district_code = $request->session()->get('distCode');
                $urban_body_code = $request->urban_body_code;
                $block_ulb_code = $request->block_ulb_code;
                $is_rural_visible = 1;
                $urban_visible = 1;
                $munc_visible = 1;
                $gp_ward_visible = 1;
            } else if ($role_name == 'Verifier'|| $role_name == 'Delegated Verifier'  || $role_name == 'Operator') {
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

            $report_type_name = 'Beneficiary List';
            if ($request->has('type')) {
                $report_type = $request->get('type');
                if ($report_type == 'V') {
                    $report_type_name = 'Verified Application List';
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 1;
                    $column = 'ss_ben_id';
                    $column1 = 'ss_ben_id';
                    $column2 = 'ss_ben_id';
                    $column3 = 'ss_ben_id';
                    $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
                } else if ($report_type == 'A') {
                    $is_draft = NULL;
                    $report_type_name = 'Approved Beneficiary List';
                    $condition['next_level_role_id'] = 0;
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, $is_draft);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $column = 'beneficiary_id';
                    $column1 = 'ss_ben_id';
                    $column2 = 'ss_ben_id';
                    $column3 = 'ss_ben_id';
                    $contact_table = $getModelFunc->getTable($district_code, '', 3);
                    if ($role_name == 'Approver' || $role_name == 'Delegated Approver') {
                        $download_excel = 1;
                    } else if ($role_name == 'Verifier' || $role_name == 'Delegated Verifier' || $role_name == 'Operator') {
                        $download_excel = 1;
                    } else {
                        $download_excel = 1;
                    }
                } else if ($report_type == 'R') {
                    $report_type_name = 'Rejected Application List';
                    // $condition['next_level_role_id'] = '-100';
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 10);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 2;
                    $column = 'ss_ben_id';
                    $column1 = 'rejected_cause';
                    $column2 = 'u.mobile_no';
                    $column3 = 'ss_ben_id';
                    $contact_table =  $getModelFunc->getTable($district_code, $this->source_type, 10);
                } else if ($report_type == 'T') {
                    $report_type_name = 'Reverted Application List';
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 1;
                    $condition['next_level_role_id'] = '-50';
                    $column = 'ss_ben_id';
                    $column1 = 'rejected_cause';
                    $column2 = 'u.mobile_no';
                    $column3 = 'ss_ben_id';
                    $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
                } else if ($report_type == 'F') {
                    $report_type_name = 'Faulty Application List';
                    $Table = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1, 1);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 1;
                    $column = 'ss_ben_id';
                    $column1 = 'ss_ben_id';
                    $column2 = 'u.mobile_no';
                    $column3 = 'bank_code';
                    $contact_table = $getModelFunc->getTableFaulty($district_code, '', 3, 1);
                    $bank_table = $getModelFunc->getTableFaulty($district_code, '', 4, 1);
                } else if ($report_type == 'PEL') {
                    $report_type_name = 'Partially Filled Up Application List';
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
                    $modelName->setConnection('pgsql_appread');
                    $modelName->setTable('' . $Table);
                    $is_draft = 1;
                    $column = 'ss_ben_id';
                    $column1 = 'ss_ben_id';
                    $column2 = 'u.mobile_no';
                    $column3 = 'ss_ben_id';
                    $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
                } else {
                    return redirect('/')->with('error', 'Error: Report type invalid');
                }
            } else {
                return redirect('/')->with('error', 'Signature Error: Report Type not selected');
            }
            if (request()->ajax()) {

                 $ds_phase = trim($request->ds_phase);
               //  dd($ds_phase);
                if ($ds_phase != '' && $ds_phase > 0) {
                    $condition[$Table . ".ds_phase"] = $request->ds_phase;
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
                }
                if ($report_type == 'R' || $report_type == 'T' ||  $report_type == 'F' ||  $report_type == 'PEL') {
                    $query = $query->leftjoin('public.users as u', 'u.id', '=', $Table . '.created_by');
                }
                if ($report_type == 'F') {
                    $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
                }
                if ($report_type == 'R') {
                    //$query = $query->where('next_level_role_id', '<', 0);
                    // $query = $query->where('next_level_role_id', '!=', -50);
                    $query = $query->where('is_faulty', false);
                }
                if ($report_type == 'V') { //Verified List
                    $is_draft = 1;
                    $query = $query->where('next_level_role_id', '>', 0);
                    $query = $query->where('next_level_role_id', '!=', 9999);
                }
                if ($report_type == 'F') {
                    $query = $query->whereNull('is_migrated');
                }
                if ($report_type == 'PEL') { //Verified List
                    $query = $query->where('is_final', false);
                    $query = $query->where('is_faulty', false);
                }
                if (empty($serachvalue)) {
                    $totalRecords = $query->count($Table . '.application_id');
                    // dd($query);
                    $data = $query->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->offset($offset)->limit($limit)->get([
                        '' . $Table . '.' . $column . ' as beneficiary_id', '' . $Table . '.application_id as application_id',
                        '' . $column1 . '  as rejected_reason',
                        '' . $column2 . '  as enter_by_mobile_no',
                        '' . $column3 . '  as bank_code',
                        'ben_fname', 'aadhar_no', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname',  '' . $Table . '.mobile_no as applicant_mobile_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id', 'duare_sarkar_registration_no'

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

                                'ben_fname',  'aadhar_no', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', '' . $Table . '.mobile_no as applicant_mobile_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id', 'duare_sarkar_registration_no'

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
                                'ben_fname', 'aadhar_no', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', '' . $Table . '.mobile_no as applicant_mobile_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id',  'duare_sarkar_registration_no'

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
                        return ($data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname);
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
                    ->addColumn('duare_sarkar_registration_no', function ($data) {
                        return $data->duare_sarkar_registration_no;
                    })->addColumn('enter_by_mobile_no', function ($data) use ($report_type) {
                        if ($report_type == 'F' || $report_type == 'R' || $report_type == 'T'  || $report_type == 'PEL')
                            return $data->enter_by_mobile_no;
                        else
                            return NULL;
                    })->addColumn('faulty_reason', function ($data) use ($report_type) {
                        if ($report_type == 'F') {
                            $faulty_reason = '';
                            if (empty($data->ss_card_no)) {
                                $faulty_reason = $faulty_reason . ' Swasthyasathi Card No. Not Found.';
                            }
                            if (empty($data->aadhar_no) || trim($data->aadhar_no) == '********') {
                                $faulty_reason = $faulty_reason . ' Aadhar  No. Not Found,';
                            }
                            if (empty($data->bank_code)) {
                                $faulty_reason = $faulty_reason . ' Bank Information Not Found';
                            }
                            return $faulty_reason;
                        } else
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
                    })
                    ->addColumn('action', function ($data) use ($report_type, $is_draft) {
                        if ($report_type == 'R') {
                            $is_retect = 1;
                        } else {
                            $is_retect = 0;
                        }
                        if ($report_type == 'F') {
                            $val = '';
                        } else if ($report_type == 'PEL') {
                            $val = '';
                        } else
                            $val = '<a href="application-details-view?is_reject=' . $is_retect . '&application_id=' . $data->application_id . '&ben_id=' . $data->beneficiary_id . '&scheme_slug=lb_wcd&is_draft=' . $is_draft . '" class="btn btn-primary ben_view_button" role="button" target="_blank">View</a>';

                        return $val;
                    })->addColumn('rejected_by', function ($data) use ($report_type) {
                        if ($report_type == 'R') {
                            $rejected_by_row = DB::select("select designation_id from lb_scheme.ben_accept_reject_info 
                            where application_id=" . $data->application_id . " and trim(op_type) IN ('PR','RR','RB','R') 
                            order by id limit 1");
                            if (!empty($rejected_by_row)) {
                                return  $rejected_by_row[0]->designation_id;
                            } else {
                                $rejected_by_row = DB::select("select count(1) as cnt from lb_scheme.misc_ben_details 
                                where application_id=" . $data->application_id . " and status=1");
                                $row_count = $rejected_by_row[0]->cnt;
                                if ($row_count > 0) {
                                    return 'Approver';
                                } else {
                                    return 'Verifier/Approved';
                                }
                            }
                        } else
                            return NULL;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            } else {
                $errormsg = Config::get('constants.errormsg');
                $scheme_name_arr = Scheme::select('scheme_name')->where('id', $this->scheme_id)->first();
                return view('pensionreport.index')
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
                    ->with('scheme_name',  $scheme_name_arr->scheme_name)
                    ->with('ds_phase_list',  $ds_phase_list)
                    ->with('download_excel',  $download_excel);
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
        $scheme_code =  $request->rej_scheme_code;
        $user_id = Auth::user()->id;
        $duty = Configduty::where('user_id', '=', $user_id)->first();
        $dist_code = $duty->district_code;

        $district_name = District::where('district_code', $dist_code)->pluck('district_name')->first();
        $scheme_name_row = Scheme::where('id', $scheme_code)->select('scheme_name', 'short_code')->first();
        $scheme_name = $scheme_name_row->scheme_name;
        $scheme_schema_name = $scheme_name_row->short_code;

        $title = $district_name . "_" . $scheme_name . "_Rejected Duplicates";

        $data = array();

        $query = "select block_ulb_name || case when rural_urban_id=1 then ' Municipality' else '' end  \"Block_Municipality\"
        ,id as \"Beneficiary_Id\"
        , ben_fname ||' '|| coalesce(ben_mname||' ','') || coalesce(ben_lname,'') as \"Name\"
        , b.bank_code  as \"Account_No\"
        , b.bank_ifsc as \"IFSC\"
        from " . $scheme_schema_name . ".beneficiary b where next_level_role_id = -3 and created_by_dist_code=" . $dist_code;

        $data_part = DB::connection('pgsql')->select($query);
        $data = array_merge($data, $data_part);

        $excel_data[] = array('Block/Municipality', 'Beneficiary_Id', 'Name', 'Account_No', 'IFSC');
        foreach ($data as $row) {
            $excel_data[] = array(
                'Block/Municipality'  => $row->Block_Municipality,
                'Beneficiary_Id'  => $row->Beneficiary_Id,
                'Name'  => $row->Name,
                'Account_No'  => $row->Account_No,
                'IFSC'  => $row->IFSC
            );
        }

        Excel::create('' . $title, function ($excel) use ($excel_data, $title, $scheme_name) {
            $excel->setTitle('' . $title);
            $excel->sheet('' . $scheme_name, function ($sheet) use ($excel_data) {
                $sheet->fromArray($excel_data, null, 'A1', false, false);
            });
        })->download('xlsx');
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
}
