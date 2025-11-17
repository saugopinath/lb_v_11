<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\Configduty;
use App\Models\getModelFunc;
use App\LotMaster;
use App\LotDetails;
use App\AvLotmaster;
use App\AvLotdetails;
use App\FailedBankDetails;
use App\Models\UrbanBody;
use App\Models\GP;
use App\BankDetails;
use App\Models\DataSourceCommon;
use Maatwebsite\Excel\Facades\Excel;
use App\DocumentType;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\District;
use App\SubDistrict;
use App\Taluka;
use App\Ward;
use App\DsPhase;
use App\Helpers\DupCheck;
class BenNameValidationFailedController extends Controller
{
    public function __construct()
    {
        set_time_limit(300);
        $this->scheme_id = 20;
        $this->middleware('auth');
        $this->min_matching_score = 90;
        $this->max_matching_score = 100;
        $this->minMatchingScore = 40;
        $this->maxMatchingScore = 89;
    }
    /*
		Name Validation Landing page
    */
    public function index()
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
        $distCode = $dutyObj->district_code;
        if (Auth::user()->designation_id == 'Verifier' || Auth::user()->designation_id == 'Delegated Verifier') {
            if ($dutyObj->is_urban == 1) {
                $ulb_gp = UrbanBody::select('urban_body_code', 'urban_body_name')->where('district_code', $distCode)->where('sub_district_code', $dutyObj->urban_body_code)->get();
            } else {
                $ulb_gp = GP::select('gram_panchyat_code', 'gram_panchyat_name')->where('block_code', $dutyObj->taluka_code)->get();
            }
            return view('ben-name-validation-failed/linelisting_name_failed', ['duty_level' => $dutyLevel, 'dist_code' => $dutyObj->district_code, 'ulb_gp' => $ulb_gp]);
        } else {
            return redirect('/')->with('success', 'Unauthorized');
        }
    }
    /*
		Get Name Validation Data each Verifier (Block/Sub-divison) wise
    */
    public function getDataNameValidationFailed(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        if ($request->ajax()) {
            $user_id = Auth::user()->id;
            $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
            $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();

            if ($dutyLevel == 'BlockVerifier' || $dutyLevel == 'BlockDelegated Verifier') {
                // $completequery="select count(1) from lb_scheme.update_ben_details where local_body_code=".$dutyObj->taluka_code;
                // $old_query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,mb.block_name block_ulb_name,gp.gram_panchyat_name as gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                //     from lb_main.failed_payment_details f 
                //     JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                //     JOIN public.m_block mb ON mb.block_code=b.local_body_code
                //     JOIN public.m_gp gp ON gp.gram_panchyat_code=b.gp_ward_code
                //     WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status=0 and b.ben_status=1 AND f.failed_type=3 ";
                $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,  f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status=0 and b.ben_status=1 AND f.failed_type IN(3,4) AND f.legacy_validation_failed = false ";
                if (!empty($request->filter_1)) {
                    // $old_query .= " AND b.gp_ward_code=" . $request->filter_1 . "";
                    //  $completequery .= " AND gp_ward_code=".$request->filter_1."";
                    $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status=0 and b.ben_status=1 AND f.failed_type IN(3,4) AND f.legacy_validation_failed = false AND b.gp_ward_code=" . $request->filter_1 . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= "  order by b.ben_id";
            } elseif ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier') {
                //$completequery="select count(1) from lb_scheme.update_ben_details where local_body_code=".$dutyObj->urban_body_code;
                // $old_query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,ub.urban_body_name block_ulb_name,wa.urban_body_ward_name as gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                //     from lb_main.failed_payment_details f 
                //     JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                //     JOIN public.m_urban_body ub ON ub.urban_body_code=b.block_ulb_code
                //     JOIN public.m_urban_body_ward wa ON wa.urban_body_ward_code=b.gp_ward_code
                //     WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1 AND f.failed_type=3 ";
                $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code  
                    WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1 AND f.failed_type IN(3,4) AND f.legacy_validation_failed = false ";
                if (!empty($request->filter_1)  && empty($request->filter_2)) {
                    // $old_query .= " AND b.block_ulb_code=" . $request->filter_1 . " ";
                    // $completequery .=" AND block_ulb_code=".$request->filter_1." ";
                    $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1 AND f.failed_type IN(3,4) AND f.legacy_validation_failed = false  AND b.block_ulb_code=" . $request->filter_1 . " ";
                }
                if (!empty($request->filter_1)  && !empty($request->filter_2)) {
                    // $old_query .= " AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . "";
                    // $completequery .=" AND block_ulb_code=".$request->filter_1." AND b.gp_ward_code=".$request->filter_2."";
                    $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1 AND f.failed_type IN(3,4) AND f.legacy_validation_failed = false  AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . " ";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= "  order by b.ben_id";
            }
            // $complete = DB::connection('pgsql_appread')->select($completequery);

            $data = DB::connection('pgsql_payment')->select($query);

            //print_r($data);die;
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn = "";
                    $btn = '<button class="btn btn-xs btn-primary bank_edit_btn" type="button" id="' . base64_encode($data->ben_id) . '_' . base64_encode($data->id) . '"><i class="glyphicon glyphicon-edit"></i> View</button>';
                    return   $btn;
                })
                ->addColumn('id', function ($data) {
                    return $data->ben_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->ben_name;
                })
                ->addColumn('block_ulb_name', function ($data) {
                    return $data->block_ulb_name;
                })
                // ->addColumn('gp_ward_name', function ($data) {
                //     return $data->gp_ward_name;
                // })
                ->addColumn('accno', function ($data) {
                    return $data->last_accno;
                })
                ->addColumn('ifsc', function ($data) {
                    return $data->last_ifsc;
                })
                ->addColumn('failure_month', function ($data) {
                    if ($data->failed_type == '2') {
                        return Config::get('constants.monthval.' . trim($data->lot_month));
                    } else {
                        return $data->validation_month;
                    }
                })
                ->addColumn('ss_cardno', function ($data) {

                    return $data->ss_card_no;
                })
                ->addColumn('mobile_no', function ($data) {

                    return $data->mobile_no;
                })
                ->addColumn('application_id', function ($data) {

                    return $data->application_id;
                })
                ->addColumn('type', function ($data) {
                    $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
                    return $msg;
                })
                // ->with('completed', $complete)
                ->rawColumns(['ben_id', 'name', 'block_ulb_name', 'accno', 'ifsc', 'action', 'ss_cardno', 'mobile_no', 'application_id', 'type', 'failure_month'])
                ->make(true);
        }
    }
    /*
		Get each beneficiary records for edit
    */
    public function editFailedNameDetails(Request $request)
    {

        $statuscode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statuscode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statuscode);
        }
        try {

            $editvalue = explode("_", $request->editvalue);
            //  print_r($editvalue);die;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $ben_id = base64_decode($editvalue[0]);
            $f_id = base64_decode($editvalue[1]);
            $tableName = Helper::getTable($ben_id);

            $personalDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $ben_id)->first();

            $bankDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $ben_id)->first();
            $contactDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benContactTable'])->where('beneficiary_id', $ben_id)->first();
            $failedReason = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('id', $f_id)->where('edited_status', 0)->first();

            $ben_name = $personalDetails->ben_fname . ' ' . $personalDetails->ben_mname . ' ' . $personalDetails->ben_lname;
            $benfather_name = $personalDetails->father_fname . ' ' . $personalDetails->father_mname . ' ' . $personalDetails->father_lname;
            $mobile_no = $personalDetails->mobile_no;
            $caste = $personalDetails->caste;
            $ben_id = $personalDetails->beneficiary_id;
            $dob = date('d-m-Y', strtotime($personalDetails->dob));
            $gender = $personalDetails->gender;
            $bank_name = $bankDetails->bank_name;
            $bank_ifsc = $bankDetails->bank_ifsc;
            $branch_name = $bankDetails->branch_name;
            $bank_code = $bankDetails->bank_code;
            $fname = $personalDetails->ben_fname;
            $mname = $personalDetails->ben_mname;
            $lname = $personalDetails->ben_lname;
            $status_code = $failedReason->name_status_code;
            $name_response = $failedReason->name_response;
            $matching_score = $failedReason->matching_score;
            $application_id = $personalDetails->application_id;
            $block_ulb_name = $contactDetails->block_ulb_name;
            $gp_ward_name = $contactDetails->gp_ward_name;
            // if ($name_response == '') {
            //   $name_response = 'No name received from bank.';
            // }

            $response = array(
                'ben_name' => $ben_name, 'benfather_name' => $benfather_name, 'mobile_no' => $mobile_no, 'caste' => $caste, 'dob' => $dob,
                'gender' => $gender, 'bank_name' => $bank_name, 'bank_ifsc' => $bank_ifsc, 'branch_name' => $branch_name, 'bank_code' => $bank_code,
                'ben_id' => $ben_id, 'failed_reason' => '', 'failedid' => $f_id, 'status_code' => $status_code, 'name_response' => $name_response,
                'fname' => $fname, 'mname' => $mname, 'lname' => $lname, 'application_id' => $application_id,
                'block_ulb_name' => $block_ulb_name, 'gp_ward_name' => $gp_ward_name, 'matching_score' => $matching_score
            );
            // dd($response);
        } catch (\Exception $e) {
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
            );
            $statuscode = 400;
        } finally {
            return response()->json($response, $statuscode);
        }
    }
    /*
		Final update section (Process with same data / Process with another fresh bank account)
		1. Process with existing data, minor mismatch
		2. Process with fresh bank account
    3. Reject application due to major mismatach
    */
    public function updateFailedNameFromVerifier(Request $request)
    {

        $statuscode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statuscode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statuscode);
        }
        try {
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $pension_details_encloser1 = new DataSourceCommon;
            $Table = $getModelFunc->getTable('', '', 6, 1);
            $pension_details_encloser1->setConnection('pgsql_encwrite');
            $pension_details_encloser1->setTable('' . $Table);
            $beneficiary_id = $request->benId;
            $new_bank_ifsc = $request->bank_ifsc;
            $new_bank_name = $request->bank_name;
            $new_bank_account_number = $request->bank_account_number;
            $new_branch_name = $request->branch_name;
            $old_bank_ifsc = $request->old_bank_ifsc;
            $old_bank_accno = $request->old_bank_accno;
            $remarks  = $request->remarks;
            $application_id = $request->application_id;
            $process_type = $request->process_type;
            $faildTableId = $request->faildTableId;
            $nameStatusCode = $request->nameStatusCode;

            $tableName = Helper::getTable($beneficiary_id);
            $is_update_happens = 0;

            /* Checking New A/c & IFSC and Old A/c & IFSC Same */
            if ($process_type == 2) {
                if (($new_bank_account_number == $old_bank_accno) && ($new_bank_ifsc == $old_bank_ifsc)) {
                    $is_update_happens;
                    $is_update_happens = 0;
                    return $response = array(
                        'status' => 1, 'msg' => 'Given Bank account number and ifsc same as previous one.',
                        'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Warning!'
                    );
                }
                /* Checking duplicate A/c and IFSC */
                // $benPaymentDuplicateAcCount = DB::connection('pgsql_payment')
                // ->table($schemaname . '.ben_payment_details')
                //  ->where('ben_id','<>', $beneficiary_id)
                //  ->whereRaw("trim(last_ifsc)=trim("."'".$new_bank_ifsc."'".")")
                //  ->whereRaw("trim(last_accno)=trim("."'".$new_bank_account_number."'".")")
                //  ->whereIn('ben_status',['1','-97'])
                //  ->count('ben_id');
                $duplicate_row = DB::connection('pgsql_appwrite')->select("select count(1) as cnt from lb_scheme.duplicate_bank_view where trim(bank_code)='" . $new_bank_account_number . "'");
                $benPaymentDuplicateAcCount = $duplicate_row[0]->cnt;
                if(!empty($new_bank_account_number))
                {
                    $DupCheckBankOap = DupCheck::getDupCheckBank(10,$new_bank_account_number);
                    if(!empty($DupCheckBankOap)){
                        $is_update_happens = 0;
                        $msg = 'Duplicate Bank Account Number present in Old Age Pension Scheme with Beneficiary ID- '.$DupCheckBankOap.'';
                        $response = array(
                            'status' => 5, 'msg' => $msg,
                            'type' => 'blue', 'icon' => 'fa fa-info', 'title' => 'Infomation'
                        );
                    }
                    $DupCheckBankJohar = DupCheck::getDupCheckBank(1,$new_bank_account_number);
                    if(!empty($DupCheckBankJohar)){
                        $is_update_happens = 0;
                        $msg = 'Duplicate Bank Account Number present Jai Johar Pension Scheme with Beneficiary ID- '.$DupCheckBankJohar.'';
                        $response = array(
                            'status' => 5, 'msg' => $msg,
                            'type' => 'blue', 'icon' => 'fa fa-info', 'title' => 'Infomation'
                        );
                    }
                    $DupCheckBankBandhu = DupCheck::getDupCheckBank(3,$new_bank_account_number);
                    if(!empty($DupCheckBankBandhu)){
                        $is_update_happens = 0;
                        $msg = 'Duplicate Bank Account Number present Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- '.$DupCheckBankBandhu.'';
                        return   $response = array(
                            'status' => 5, 'msg' => $msg,
                            'type' => 'blue', 'icon' => 'fa fa-info', 'title' => 'Infomation'
                        );
                    }
                }
                if ($benPaymentDuplicateAcCount > 0) {
                    $is_update_happens = 0;
                    $msg = 'Duplicate Bank A/c & IFSC already exist !!';
                    $response = array(
                        'status' => 5, 'msg' => $msg,
                        'type' => 'blue', 'icon' => 'fa fa-info', 'title' => 'Infomation'
                    );
                } else {
                    /* Chacking if Bank name and Branch name are same as IFSC table */
                    $bank_details = BankDetails::where('is_active',1)->where('ifsc', $new_bank_ifsc)->get(['bank', 'branch'])->first();
                    if (!empty($bank_details)) {
                        if ((trim($bank_details->bank) == trim($new_bank_name)) && (trim($bank_details->branch) == trim($new_branch_name))) {
                            $is_update_happens = 1;
                        } else {
                            $is_update_happens = 0;
                            $response = array(
                                'status' => 5, 'msg' => 'Bank account name or bank branch name are not matched',
                                'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Not Match'
                            );
                        }
                    } else {
                        $is_update_happens = 0;
                        $response = array(
                            'status' => 5, 'msg' => 'This ' . $new_bank_ifsc . ' IFSC is not registered in our system.', 'type' => 'blue', 'icon' => 'fa fa-info', 'title' => 'IFSC Not Found'
                        );
                    }
                }
            } else {
                $is_update_happens = 1;
            }

            // echo $is_update_happens;die();
            if ($is_update_happens == 1) {
                $benPaymentObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->first();
                /*-------------- Document Upload Section ----------------*/
                if ($process_type == 2 && empty($request->file('upload_bank_passbook'))) {
                    return $response = array(
                        'status' => 2, 'msg' => 'Please upload bank passbook copy.',
                        'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Required'
                    );
                }
                if (!empty($request->file('upload_bank_passbook'))) {
                    $attributes = array();
                    $pension_details = array();
                    $query = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', 10);
                    $doc_arr = $query->first();
                    $required = 'required';
                    $rules['upload_bank_passbook'] = $required . '|mimes:' . $doc_arr->doc_type . '|max:' . $doc_arr->doc_size_kb . ',';
                    $messages['upload_bank_passbook.max'] = "The file uploaded for " . $doc_arr->doc_name . " size must be less than :max KB";
                    $messages['upload_bank_passbook.mimes'] = "The file uploaded for " . $doc_arr->doc_name . " must be of type " . $doc_arr->doc_type;
                    $messages['upload_bank_passbook.required'] = "Document for " . $doc_arr->doc_name . " must be uploaded";
                    //dd($rules);
                    $validator = Validator::make($request->all(), $rules, $messages, $attributes);
                    if ($validator->passes()) {
                        $valid = 1;
                    } else {
                        $valid = 0;
                        $return_msg = $validator->errors()->all();
                        $return_status = 0;

                        $response = array(
                            'status' => 3, 'msg' => $return_msg,
                            'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
                        );
                    }

                    if ($valid == 1) {
                        $upload_bank_passbook = $request->file('upload_bank_passbook');
                        $img_data = file_get_contents($upload_bank_passbook);
                        $extension = $upload_bank_passbook->getClientOriginalExtension();
                        $mime_type = $upload_bank_passbook->getMimeType();
                        $base64 = base64_encode($img_data);

                        $tableNameDoc = Helper::getTable('', $benPaymentObj->application_id);

                        $insertIntoArchieve = "INSERT INTO lb_scheme.ben_attach_documents_arch(
            application_id, beneficiary_id, document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,doc_status)
            select application_id, beneficiary_id, document_type, attched_document, created_by_level,created_at,updated_at, '" . date('Y-m-d H:i:s') . "', created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,3
            from lb_scheme." . $tableNameDoc['benDocTable'] . " where application_id = " . $benPaymentObj->application_id . " and document_type = " . $doc_arr->id;
                        // echo $insertIntoArchieve;
                        $executeInsert = DB::connection('pgsql_encwrite')->select($insertIntoArchieve);
                        // $executeInsert=1;
                        if ($executeInsert) {
                            $pension_details['attched_document'] = $base64;
                            $pension_details['document_extension'] = $extension;
                            $pension_details['document_mime_type'] = $mime_type;
                            $pension_details['updated_at'] = date('Y-m-d H:i:s');
                            $pension_details['action_by'] = Auth::user()->id;
                            $pension_details['action_ip_address'] = request()->ip();
                            $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                            // print_r($pension_details);
                            $crd_status_2 = $pension_details_encloser1->where('document_type', $doc_arr->id)
                                ->where('application_id',  $benPaymentObj->application_id)->update($pension_details);
                        }
                    }
                }

                // Others Updates
                $fObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                    ->where('id', $faildTableId)->where('edited_status', 0)->first();
                $bankDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $beneficiary_id)->first();
                $contactDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benContactTable'])->where('beneficiary_id', $beneficiary_id)->first();

                $old_bank_name = $bankDetails->bank_name;
                $old_bank_acc_no = $bankDetails->bank_code;
                $old_branch_name = $bankDetails->branch_name;
                $old_bank_ifsc = $bankDetails->bank_ifsc;

                $new_value = [];
                $old_value = [];

                $old_value['bank_name'] = trim($old_bank_name);
                $old_value['branch_name'] = trim($old_branch_name);
                $old_value['bank_ifsc'] = trim($old_bank_ifsc);
                $old_value['bank_code'] = trim($old_bank_acc_no);

                $new_value['bank_name'] = trim($new_bank_name);
                $new_value['branch_name'] = trim($new_branch_name);
                $new_value['bank_ifsc'] = trim($new_bank_ifsc);
                $new_value['bank_code'] = trim($new_bank_account_number);

                $insertUpdateBen = [
                    'failed_tbl_id' => $faildTableId,
                    'beneficiary_id' => $beneficiary_id,
                    'user_id' => Auth::user()->id,
                    'old_data' => json_encode($old_value),
                    'next_level_role_id' => 5,
                    'dist_code' => $bankDetails->created_by_dist_code,
                    'local_body_code' => $bankDetails->created_by_local_body_code,
                    'rural_urban_id' => $contactDetails->rural_urban_id,
                    'block_ulb_code' => $contactDetails->block_ulb_code,
                    'gp_ward_code' => $contactDetails->gp_ward_code,
                    'created_at' => date('Y-m-d H:i:s'),
                    'pmt_mode' => $fObj->pmt_mode,
                    'failed_type' => $fObj->failed_type,
                    'application_id' => $application_id,
                    'remarks' => $remarks,
                    'ip_address' => request()->ip(),
                    'name_resposne_from_bank' => $fObj->name_response,
                    'ben_name' => $benPaymentObj->ben_name,
                    'legacy_validation_update' => $fObj->legacy_validation_failed
                ];
                if ($process_type == 1) {
                    $insertUpdateBenDetails = array_merge($insertUpdateBen, array('update_code' => 11, 'new_data' => json_encode($old_value)));
                } else if ($process_type == 2) {
                    $insertUpdateBenDetails = array_merge($insertUpdateBen, array('update_code' => 12, 'new_data' => json_encode($new_value)));
                }
                if ($process_type == 3) {
                    $insertUpdateBenDetails = array_merge($insertUpdateBen, array('update_code' => 13, 'new_data' => json_encode($old_value)));
                    $otp_table_insert = [
                        'application_id' => $application_id,
                        'verification_otp' => $request->otp_login,
                        'user_id' => $user_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }

                /*------------- Database Operations -----------------*/
                DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')
                    ->insert($insertUpdateBenDetails);
                DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                    ->where('edited_status', '0')->where('ben_id', $beneficiary_id)->whereIn('failed_type', [3,4])->where('legacy_validation_failed', false)
                    ->update(['edited_status' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
		        DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                    ->where('id', '<>', $faildTableId)->where('edited_status', '1')->where('ben_id', $beneficiary_id)->whereIn('failed_type', [3,4])->where('legacy_validation_failed', false)
                    ->update(['edited_status' => '11', 'updated_at' => date('Y-m-d H:i:s')]);
                if ($process_type == 3) {
                    DB::connection('pgsql_appwrite')->table('public.name_validation_reject_otp')->insert($otp_table_insert);
                }
                /*------------- End Database Operations -----------------*/

                $response = array(
                    'status' => 4, 'msg' => 'Bank Details Updated Succesfully.',
                    'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success'
                );
                /*-------------- End Document Upload Section ---------------*/
            }

            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
            DB::connection('pgsql_encwrite')->commit();
        } catch (\Exception $e) {
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            DB::connection('pgsql_encwrite')->rollback();
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
            );
            $statuscode = 400;
        } finally {
            return response()->json($response, $statuscode);
        }
    }
    public function failedNameAjaxViewPassbook(Request $request)
    {
        // dd($request->toArray());
        $scheme_id = 20;
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        $distCode=NULL;
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                $distCode = $roleObj['district_code'];
                $is_urban = $roleObj['is_urban'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                } else {
                    $blockCode = $roleObj['taluka_code'];
                }
                break;
            }
        }
        // dd($roleArray);
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled');
        }
        // dd($request->toArray());
        $getModelFunc = new getModelFunc();

        $DraftEncloserTable = new DataSourceCommon;
        $checkApplicationIdCount = DB::connection('pgsql_encread')->table('lb_scheme.faulty_ben_attach_documents')
            ->where('application_id', $request->application_id)
            ->where('document_type', $request->doc_type)->count();
        if ($checkApplicationIdCount > 0) {
            $Table = $getModelFunc->getTableFaulty($distCode, '', 6, 1);
        } else {
            $Table = $getModelFunc->getTable($distCode, '', 6, 1);
        }

        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);

        if (!empty($request->is_profile_pic))
            $is_profile_pic = $request->is_profile_pic;
        else
            $is_profile_pic = 0;
        $doc_type = $request->doc_type;
        $application_id = $request->application_id;
        if (empty($doc_type) || !ctype_digit($doc_type)) {
            $return_text = 'Parameter Not Valid1';
            return redirect("/")->with('error',  $return_text);
        }
        if (!in_array($is_profile_pic, array(0, 1))) {
            $return_text = 'Parameter Not Valid2';
            return redirect("/")->with('error',  $return_text);
        }
        if (empty($application_id)) {
            $return_text = 'Parameter Not Valid3';
            return redirect("/")->with('error',  $return_text);
        }
        $user_id = Auth::user()->id;

        $encolserData = $DraftEncloserTable->where('document_type', $request->doc_type)->where('application_id', $request->application_id)->first();
        if (empty($encolserData->application_id)) {
            $return_text = 'Parameter Not Valid5';
            return redirect("/")->with('error',  $return_text);
        }
        $file_extension = $encolserData->document_extension;
        $mime_type = $encolserData->document_mime_type;
        if ($file_extension != 'png' && $file_extension != 'jpg' && $file_extension != 'jpeg' && $file_extension != 'pdf') {
            if ($mime_type == 'image/png') {
                $file_extension = 'png';
            } else if ($mime_type == 'image/jpeg') {
                $file_extension = 'jpg';
            } else if ($mime_type == 'application/pdf') {
                $file_extension = 'pdf';
            }
        }
        try {
            if (strtoupper($file_extension) == 'PNG' || strtoupper($file_extension) == 'JPG' || strtoupper($file_extension) == 'JPEG') {
                $htmlText = '<image id="image" width="100%" height="100%" src="data:image/' . $file_extension . ';base64, ' . $encolserData->attched_document . '">';
                echo $htmlText;
            } else if (strtoupper($file_extension) == 'PDF') {
                //dd($encolserData->attched_document);
                $htmlText = '<embed type="text/html" width="100%" height="100%" src="data:application/pdf;base64, ' . $encolserData->attched_document . ' ">';


                echo $htmlText;
            }
        } catch (\Exception $e) {
            return redirect("/")->with('error',  'Some error.please try again ......');
        }
    }
    function misReport(Request $request)
    {
        // return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');


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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' ||  $designation_id == 'Dashboard') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Approver' || $designation_id == 'Verifier' || $designation_id == 'Delegated Approver' || $designation_id == 'Delegated Verifier') {
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

            ]
        );
    }
    public function getData(Request $request)
    {
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        //$ds_phase_list = Config::get('constants.ds_phase.phaselist');
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
            'ds_phase' => 'nullable|integer',
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
            if (!empty($gp_ward)) {
                if ($urban_code == 1) {
                    $column = "Ward";
                    $heading_msg =  $user_msg . ' of the Ward ' . $gp_ward_name;
                    $data = $this->getWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                } else {
                    $column = "GP";
                    $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                }
            } else if (!empty($muncid)) {
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWise($district, $block, $muncid, NULL, $from_date, $to_date, $caste, $ds_phase);
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWise($district, $block, NULL, NULL, $from_date, $to_date, $caste, $ds_phase);
                } else if ($urban_code == 2) {
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
    public function getWardWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
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
        if (!empty($ds_phase)) {
            $whereCon .= " and A.ds_phase=" . $ds_phase;
        }
        $query = "select main.location_id,main.location_name,
      COALESCE(draft.partial,0) as partial,
      COALESCE(draft.full,0) as full,
      COALESCE(draft.verification_pending,0) as verification_pending,
      COALESCE(draft.verified,0) as verified,
      COALESCE(approve.approved,0) as approved,
      COALESCE(draft.reverted,0) as reverted,
      COALESCE(rej.rejected,0) as rejected,
      COALESCE(faulty.total_faulty,0)as total_faulty,
      COALESCE(faulty.verification_pending_faulty,0)as verification_pending_faulty,
      COALESCE(faulty.verified_faulty,0)as verified_faulty,
      COALESCE(approveF.approved,0)as approved_faulty
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
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
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
          select count(1) filter(where is_migrated IS NULL) as total_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.is_final=true and A.next_level_role_id is null) as verification_pending_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.ver_iseligible=1 and A.is_final=true and A.next_level_role_id > 0) as verified_faulty,
          B.gp_ward_code
          from lb_scheme.faulty_draft_ben_personal_details as A 
          LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
           " . $whereCon . " 
          group by B.gp_ward_code
      ) as faulty ON main.location_id=faulty.gp_ward_code 
      left join
      (
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
          B.gp_ward_code
          from lb_scheme.faulty_ben_personal_details as A 
         LEFT JOIN lb_scheme.faulty_ben_contact_details as B ON A.application_id=B.application_id
         " . $whereCon . " 
         group by B.gp_ward_code
      ) as approveF ON main.location_id=approveF.gp_ward_code
      order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getGpWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
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
        if (!empty($ds_phase)) {
            $whereCon .= " and A.ds_phase=" . $ds_phase;
        }
        $query = "select main.location_id,main.location_name,
      COALESCE(draft.partial,0) as partial,
      COALESCE(draft.full,0) as full,
      COALESCE(draft.verification_pending,0) as verification_pending,
      COALESCE(draft.verified,0) as verified,
      COALESCE(approve.approved,0) as approved,
      COALESCE(draft.reverted,0) as reverted,
      COALESCE(rej.rejected,0) as rejected,
      COALESCE(faulty.total_faulty,0)as total_faulty,
      COALESCE(faulty.verification_pending_faulty,0)as verification_pending_faulty,
      COALESCE(faulty.verified_faulty,0)as verified_faulty,
      COALESCE(approveF.approved,0)as approved_faulty
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
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
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
          select count(1) filter(where is_migrated IS NULL) as total_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.is_final=true and A.next_level_role_id is null) as verification_pending_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.ver_iseligible=1 and A.is_final=true and A.next_level_role_id > 0) as verified_faulty,
          B.gp_ward_code
          from lb_scheme.faulty_draft_ben_personal_details as A 
          LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
          " . $whereCon . " 
          group by B.gp_ward_code
      ) as faulty ON main.location_id=faulty.gp_ward_code 
      left join
      (
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
          B.gp_ward_code
          from lb_scheme.faulty_ben_personal_details as A 
         LEFT JOIN lb_scheme.faulty_ben_contact_details as B ON A.application_id=B.application_id
         " . $whereCon . " 
         group by B.gp_ward_code
      ) as approveF ON main.location_id=approveF.gp_ward_code
      order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getMuncWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
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
        if (!empty($ds_phase)) {
            $whereCon .= " and A.ds_phase=" . $ds_phase;
        }
        $query = "select main.location_id,main.location_name,
      COALESCE(draft.partial,0) as partial,
      COALESCE(draft.full,0) as full,
      COALESCE(draft.verification_pending,0) as verification_pending,
      COALESCE(draft.verified,0) as verified,
      COALESCE(approve.approved,0) as approved,
      COALESCE(draft.reverted,0) as reverted,
      COALESCE(rej.rejected,0) as rejected,
      COALESCE(faulty.total_faulty,0)as total_faulty,
      COALESCE(faulty.verification_pending_faulty,0)as verification_pending_faulty,
      COALESCE(faulty.verified_faulty,0)as verified_faulty,
      COALESCE(approveF.approved,0)as approved_faulty
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
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
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
          select count(1) filter(where is_migrated IS NULL) as total_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.is_final=true and A.next_level_role_id is null) as verification_pending_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.ver_iseligible=1 and A.is_final=true and A.next_level_role_id > 0) as verified_faulty,
          B.block_ulb_code
          from lb_scheme.faulty_draft_ben_personal_details as A 
          LEFT JOIN lb_scheme.faulty_draft_ben_contact_details as B ON A.application_id=B.application_id
          " . $whereCon . " 
          group by B.block_ulb_code
      ) as faulty ON main.location_id=faulty.block_ulb_code 
      left join
      (
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
          B.block_ulb_code
          from lb_scheme.faulty_ben_personal_details as A 
         LEFT JOIN lb_scheme.faulty_ben_contact_details as B ON A.application_id=B.application_id
         " . $whereCon . " 
         group by B.block_ulb_code
      ) as approveF ON main.location_id=approveF.block_ulb_code
      order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        // $dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
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
        if (!empty($ds_phase)) {
            $whereCon .= " and A.ds_phase=" . $ds_phase;
        }
        $query = "select main.location_id,main.location_name||'-Block' as location_name,
      COALESCE(draft.partial,0) as partial,
      COALESCE(draft.full,0) as full,
      COALESCE(draft.verification_pending,0) as verification_pending,
      COALESCE(draft.verified,0) as verified,
      COALESCE(approve.approved,0) as approved,
      COALESCE(draft.reverted,0) as reverted,
      COALESCE(rej.rejected,0) as rejected,
      COALESCE(faulty.total_faulty,0)as total_faulty,
      COALESCE(faulty.verification_pending_faulty,0)as verification_pending_faulty,
      COALESCE(faulty.verified_faulty,0)as verified_faulty,
      COALESCE(approveF.approved,0)as approved_faulty
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
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
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
          select count(1) filter(where is_migrated IS NULL) as total_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.is_final=true and A.next_level_role_id is null) as verification_pending_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.ver_iseligible=1 and A.is_final=true and A.next_level_role_id > 0) as verified_faulty,
          created_by_local_body_code
          from lb_scheme.faulty_draft_ben_personal_details as A 
          " . $whereCon . "  group by A.created_by_local_body_code
      ) as faulty ON main.location_id=faulty.created_by_local_body_code 
      left join
      (
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
          created_by_local_body_code
          from lb_scheme.faulty_ben_personal_details as A 
          " . $whereCon . "  group by A.created_by_local_body_code
      ) as approveF ON main.location_id=approveF.created_by_local_body_code
      order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
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
        if (!empty($ds_phase)) {
            $whereCon .= " and A.ds_phase=" . $ds_phase;
        }
        $query = "select main.location_id,main.location_name||'-SubDivision' as location_name,
      COALESCE(draft.partial,0) as partial,
      COALESCE(draft.full,0) as full,
      COALESCE(draft.verification_pending,0) as verification_pending,
      COALESCE(draft.verified,0) as verified,
      COALESCE(approve.approved,0) as approved,
      COALESCE(draft.reverted,0) as reverted,
      COALESCE(rej.rejected,0) as rejected,
      COALESCE(faulty.total_faulty,0)as total_faulty,
      COALESCE(faulty.verification_pending_faulty,0)as verification_pending_faulty,
      COALESCE(faulty.verified_faulty,0)as verified_faulty,
      COALESCE(approveF.approved,0)as approved_faulty
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
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
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
          select count(1) filter(where is_migrated IS NULL) as total_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.is_final=true and A.next_level_role_id is null) as verification_pending_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.ver_iseligible=1 and A.is_final=true and A.next_level_role_id > 0) as verified_faulty,
          created_by_local_body_code
          from lb_scheme.faulty_draft_ben_personal_details as A 
          " . $whereCon . "  group by A.created_by_local_body_code
      ) as faulty ON main.location_id=faulty.created_by_local_body_code 
      left join
      (
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
          created_by_local_body_code
          from lb_scheme.faulty_ben_personal_details as A 
          " . $whereCon . "  group by A.created_by_local_body_code
      ) as approveF ON main.location_id=approveF.created_by_local_body_code
      order by main.location_name";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
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
        if (!empty($ds_phase)) {
            $whereCon .= " and A.ds_phase=" . $ds_phase;
        }
        $query = "select main.location_id,main.location_name,
      COALESCE(draft.partial,0) as partial,
      COALESCE(draft.full,0) as full,
      COALESCE(draft.verification_pending,0) as verification_pending,
      COALESCE(draft.verified,0) as verified,
      COALESCE(approve.approved,0) as approved,
      COALESCE(draft.reverted,0) as reverted,
      COALESCE(rej.rejected,0) as rejected,
      COALESCE(faulty.total_faulty,0)as total_faulty,
      COALESCE(faulty.verification_pending_faulty,0)as verification_pending_faulty,
      COALESCE(faulty.verified_faulty,0)as verified_faulty,
      COALESCE(approveF.approved,0)as approved_faulty
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
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
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
          select count(1) filter(where is_migrated IS NULL) as total_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.is_final=true and A.next_level_role_id is null) as verification_pending_faulty,
          count(1) Filter(where A.enq_iseligible=1 and A.ver_iseligible=1 and A.is_final=true and A.next_level_role_id > 0) as verified_faulty,
          created_by_dist_code
          from lb_scheme.faulty_draft_ben_personal_details as A " . $whereCon . "
          group by A.created_by_dist_code
      ) as faulty ON main.location_id=faulty.created_by_dist_code 
      left join
      (
          select count(1) filter(where next_level_role_id!=9999 AND next_level_role_id = 0) as approved,
          created_by_dist_code
          from lb_scheme.faulty_ben_personal_details as A " . $whereCon . "
          group by A.created_by_dist_code
      ) as approveF ON main.location_id=approveF.created_by_dist_code
      order by main.location_name";

        // echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    /* Start Minor Mismatch 90-100% */ 
    public function selectMatchingScore()
    {
        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
        $distCode = $dutyObj->district_code;
        if (Auth::user()->designation_id == 'Verifier' || Auth::user()->designation_id == 'Delegated Verifier') {
            return view('ben-name-validation-failed/select_mis_match_score');
        } else {
            return redirect('/')->with('success', 'Unauthorized');
        }
    }
    public function editIndex(Request $request)
    {
        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
        $distCode = $dutyObj->district_code;
        $processType = $request->type;
        if (Auth::user()->designation_id == 'Verifier' || Auth::user()->designation_id == 'Delegated Verifier') {
            if ($dutyObj->is_urban == 1) {
                $ulb_gp = UrbanBody::select('urban_body_code', 'urban_body_name')->where('district_code', $distCode)->where('sub_district_code', $dutyObj->urban_body_code)->get();
            } else {
                $ulb_gp = GP::select('gram_panchyat_code', 'gram_panchyat_name')->where('block_code', $dutyObj->taluka_code)->get();
            }
            return view('ben-name-validation-failed/linelisting_name_failed_90_to_100', ['duty_level' => $dutyLevel, 'dist_code' => $dutyObj->district_code, 'ulb_gp' => $ulb_gp, 'processType' => $processType]);
        } else {
            return redirect('/')->with('success', 'Unauthorized');
        }
    }

    public function getDataNameValidationFailed90to100(Request $request)
    {
        if ($request->ajax()) {
            // dd($request->all());
            $user_id = Auth::user()->id;
            $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
            $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $processType = $request->mismatch_type;
            if ($processType == 1) {
                $whereCon = "AND f.matching_score >= 90 AND f.matching_score <= 100";
            } else {
                $whereCon = "AND f.matching_score >= 40 AND f.matching_score <= 89";
            }

            if ($dutyLevel == 'BlockVerifier' || $dutyLevel == 'BlockDelegated Verifier') {
                $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,  f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 ".$whereCon."";
                if (!empty($request->filter_1)) {
                    // $old_query .= " AND b.gp_ward_code=" . $request->filter_1 . "";
                    //  $completequery .= " AND gp_ward_code=".$request->filter_1."";
                    $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 AND b.gp_ward_code=" . $request->filter_1 . " ".$whereCon."";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= "  order by b.ben_id";
            } elseif ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier') {
                
                $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code  
                    WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1  ".$whereCon."";
                if (!empty($request->filter_1)  && empty($request->filter_2)) {
                    // $old_query .= " AND b.block_ulb_code=" . $request->filter_1 . " ";
                    // $completequery .=" AND block_ulb_code=".$request->filter_1." ";
                    $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 AND b.block_ulb_code=" . $request->filter_1 . " ".$whereCon."";
                }
                if (!empty($request->filter_1)  && !empty($request->filter_2)) {
                    // $old_query .= " AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . "";
                    // $completequery .=" AND block_ulb_code=".$request->filter_1." AND b.gp_ward_code=".$request->filter_2."";
                    $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
                    from lb_main.failed_payment_details f 
                    JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=b.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . " ".$whereCon."";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= "  order by b.ben_id";
            }
            // $complete = DB::connection('pgsql_appread')->select($completequery);
            // dd($query);
            $data = DB::connection('pgsql_payment')->select($query);

            // print_r($data);die;
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn = "";
                    if ($data->edited_status == 0) {
                        $btn = '<button class="btn btn-xs btn-primary bank_edit_btn" type="button" id="' . base64_encode($data->ben_id) . '_' . base64_encode($data->id) . '"><i class="glyphicon glyphicon-edit"></i> View</button>';
                    }
                    if ($data->edited_status == 1) {
                        $btn .= '<span style="font-size: 13px;" class="badge badge-warning"><b>Pending For Approval</b></span>';
                    }
                    if ($data->edited_status == 2) {
                        $btn .= '<span style="font-size: 13px;" class="badge bg-success">Approved</span>';
                    }
                    return $btn;
                })
                ->addColumn('id', function ($data) {
                    return $data->ben_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->ben_name;
                })
                ->addColumn('block_ulb_name', function ($data) {
                    return $data->block_ulb_name;
                })
                // ->addColumn('gp_ward_name', function ($data) {
                //     return $data->gp_ward_name;
                // })
                ->addColumn('accno', function ($data) {
                    return $data->last_accno;
                })
                ->addColumn('ifsc', function ($data) {
                    return $data->last_ifsc;
                })
                ->addColumn('failure_month', function ($data) {
                    if ($data->failed_type == '2') {
                        return Config::get('constants.monthval.' . trim($data->lot_month));
                    } else {
                        return $data->validation_month;
                    }
                })
                ->addColumn('ss_cardno', function ($data) {

                    return $data->ss_card_no;
                })
                ->addColumn('mobile_no', function ($data) {

                    return $data->mobile_no;
                })
                ->addColumn('application_id', function ($data) {

                    return $data->application_id;
                })
                ->addColumn('type', function ($data) {
                    $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
                    return $msg;
                })
                // ->with('completed', $complete)
                ->rawColumns(['ben_id', 'name', 'block_ulb_name', 'accno', 'ifsc', 'action', 'ss_cardno', 'mobile_no', 'application_id', 'type', 'failure_month'])
                ->make(true);
        }
    }

    public function updateNameValidationFailed90to100(Request $request)
    {
        // dd($request->all());
        $statuscode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statuscode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statuscode);
        }
        try {
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $beneficiary_id = $request->benId;
            $new_bank_ifsc = $request->bank_ifsc;
            $new_bank_name = $request->bank_name;
            $new_bank_account_number = $request->bank_account_number;
            $new_branch_name = $request->branch_name;
            $old_bank_ifsc = $request->old_bank_ifsc;
            $old_bank_accno = $request->old_bank_accno;
            $remarks  = $request->remarks;
            $matching_type = $request->matchingType;
            $application_id = $request->application_id;
            $process_type = $request->process_type;
            $faildTableId = $request->faildTableId;
            $nameStatusCode = $request->nameStatusCode;

            $tableName = Helper::getTable($beneficiary_id);
            $benPaymentDetails = DB::connection('pgsql_payment')->table('payment.ben_payment_details')->where('ben_id', $beneficiary_id)->first();
            $failedPaymentDetails = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('id', $faildTableId)->where('edited_status', 0)->where('is_minor_mismatch', 1)->first();
            $benNameFailedLogTbl = array();
            $benAcceptRejectInfo = array();
            $msg = 'Beneficiary - '.$beneficiary_id.' has been sent to Approver.';

            if ($matching_type == 2) {
                $pension_details_encloser2 = new DataSourceCommon;
                if ($benPaymentDetails->faulty_status == true) {
                    $Table = $getModelFunc->getTableFaulty('','', 6, '');
                }
                if ($benPaymentDetails->faulty_status == false) {
                    $Table = $getModelFunc->getTable('','', 6, '');
                }
                // echo $Table;die;
                $pension_details_encloser2->setConnection('pgsql_encwrite');
                $pension_details_encloser2->setTable('' . $Table);
                if(!empty($request->file('upload_enquiry_report'))){
                    $attributes = array();
                    $pension_details = array();
                    $query = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', 500);
                    $doc_arr = $query->first();
                    $required = 'required';
                    $rules['upload_enquiry_report'] = $required . '|mimes:' . $doc_arr->doc_type . '|max:' . $doc_arr->doc_size_kb . ',';
                    $messages['upload_enquiry_report.max'] = "The file uploaded for " . $doc_arr->doc_name . " size must be less than :max KB";
                    $messages['upload_enquiry_report.mimes'] = "The file uploaded for " . $doc_arr->doc_name . " must be of type " . $doc_arr->doc_type;
                    $messages['upload_enquiry_report.required'] = "Document for " . $doc_arr->doc_name . " must be uploaded";
                    $validator = Validator::make($request->all(), $rules, $messages, $attributes);
                    if ($validator->passes()) {
                      $valid = 1;
                    } else {
                      $valid = 0;
                      $return_msg = $validator->errors()->all();
                      $return_status = 0;
        
                      $response = array(
                        'status' => 7, 'msg' => $return_msg,
                        'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
                      );
                    }
                    // dd($valid);
                    if ($valid == 1) {
                        $upload_alive_document = $request->file('upload_enquiry_report');
                        $img_data = file_get_contents($upload_alive_document);
                        $extension = $upload_alive_document->getClientOriginalExtension();
                        $mime_type = $upload_alive_document->getMimeType();
                        $base64 = base64_encode($img_data);
                        $c_datetime = date('Y-m-d H:i:s', time());
                        // dump($extension); dump($mime_type); dd($c_datetime);
          
                        $tableNameDoc = Helper::getTable('', $application_id);
                        // dd($tableNameDoc['benDocTable']);
                        $query = "SELECT lb_scheme.ben_docs_insert_archive(
                            in_beneficiary_id => ".$beneficiary_id.",
                            in_application_id => ".$benPaymentDetails->application_id.",
                            in_document_type => ".$doc_arr->id.",
                            in_attched_document => '".$base64."',
                            in_created_by_level => '".$duty->mapping_level."',
                            in_created_by => ".$user_id.",
                            in_ip_address => '".request()->ip()."',
                            in_document_extension => '".$extension."',
                            in_document_mime_type => '".$mime_type."',
                            in_created_by_dist_code => ".$benPaymentDetails->dist_code.",
                            in_created_by_local_body_code => ".$benPaymentDetails->local_body_code.",
                            in_datetime => '".$c_datetime."'
                            )";
                            // dd($query);
                            $fun_call = DB::connection('pgsql_encwrite')->select($query);
                            $doc_upload = $fun_call[0]->ben_docs_insert_archive;
                        // echo $insertIntoArchieve;
                        // $executeInsert = DB::connection('pgsql_encwrite')->select($insertIntoArchieve);
    
                        if ($doc_upload) {
                            // dd($executeInsert);
                            $pension_details['attched_document'] = $base64;
                            $pension_details['document_extension'] = $extension;
                            $pension_details['document_mime_type'] = $mime_type;
                            $pension_details['updated_at'] = date('Y-m-d H:i:s');
                            $pension_details['action_by'] = Auth::user()->id;
                            $pension_details['action_ip_address'] = request()->ip();
                            $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                            if ($benPaymentDetails->faulty_status == true) {
                                $docUpdate = $pension_details_encloser2->where('document_type', $doc_arr->id)->where('application_id',  $application_id)->update($pension_details);
                            } else {
                                $docUpdate = $pension_details_encloser2->where('document_type', $doc_arr->id)->where('application_id',  $application_id)->update($pension_details);
                            }
                        } else {
                            $benAttachmentInsert = [
                                'application_id' => $application_id,
                                'beneficiary_id' => $beneficiary_id,
                                'document_type' => $doc_arr->id,
                                'attched_document' => $base64,
                                'created_by_level' => $duty->mapping_level,
                                'created_at' => date('Y-m-d H:i:s'),
                                'created_by' => $user_id,
                                'document_extension' => $extension,
                                'document_mime_type' => $mime_type,
                                'created_by_dist_code' => $benPaymentDetails->dist_code,
                                'created_by_local_body_code' => $benPaymentDetails->local_body_code,
                                'action_by' => Auth::user()->id,
                                'action_ip_address' => request()->ip(),
                                'action_type' => class_basename(request()->route()->getAction()['controller'])
                            ];
                            // dd($benAttachmentInsert);
                            $executeInsert = $pension_details_encloser2->insert($benAttachmentInsert);
                        }
                    }
                } else {
                    $response = array(
                        'status' => 9, 'msg' => 'Please upload bank passbook copy.',
                        'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Required'
                      );
                }
            }

            $benNameFailedLogTbl['ben_id'] = $beneficiary_id;
            $benNameFailedLogTbl['name'] = $failedPaymentDetails->ben_name;
            $benNameFailedLogTbl['response_name'] = $failedPaymentDetails->name_response;
            $benNameFailedLogTbl['process_type'] = $process_type;
            $benNameFailedLogTbl['created_at'] = date('Y-m-d H:i:s');
            $benNameFailedLogTbl['matching_score'] = $failedPaymentDetails->matching_score;
            $benNameFailedLogTbl['failed_tbl_id'] = $faildTableId;
            $benNameFailedLogTbl['failed_type'] = $failedPaymentDetails->failed_type;

            $benAcceptRejectInfo['ben_id'] = $beneficiary_id;
            $benAcceptRejectInfo['scheme_id'] = 20;
            $benAcceptRejectInfo['created_by'] = $user_id;
            $benAcceptRejectInfo['created_by_level'] = $duty->mapping_level;
            $benAcceptRejectInfo['created_by_dist_code'] = $duty->district_code;
            $benAcceptRejectInfo['created_by_local_body_code'] = $duty->taluka_code;
            $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
            $benAcceptRejectInfo['ip_address'] = request()->ip();
            $benAcceptRejectInfo['designation_id'] = $designation_id;
            $benAcceptRejectInfo['user_id'] = $user_id;
            $benAcceptRejectInfo['mapping_level'] = $duty->mapping_level;
            $benAcceptRejectInfo['application_id'] = $application_id;
            // $benAcceptRejectInfo['failed_tbl_id'] = $faildTableId;
            if ($process_type == 1) {
                $benNameFailedLogTbl['next_level_name_failed_id'] = 6;
                $benAcceptRejectInfo['op_type'] = 'V_MinorMismatch';
                
            }
            if ($process_type == 3) {
                $benNameFailedLogTbl['next_level_name_failed_id'] = 7;
                $benAcceptRejectInfo['op_type'] = 'V_NameValReject';
                $benAcceptRejectInfo['rejected_reverted_cause'] = $remarks;
                $otp_table_insert = [
                    'application_id' => $application_id,
                    'verification_otp' => $request->otp_login,
                    'user_id' => $user_id,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                // $msg = 'Beneficiary rejected successfully.';
            }
            /*----------------- Database Operation -----------------*/
            DB::connection('pgsql_appwrite')->table('lb_scheme.ben_name_failed_log')->insert($benNameFailedLogTbl);
            DB::connection('pgsql_appwrite')->table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
            DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('edited_status', '0')->where('is_minor_mismatch', 1)->where('ben_id', $beneficiary_id)->whereIn('failed_type', [3,4])->where('id', $faildTableId)->update(['edited_status' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
            if ($process_type == 3) {
                DB::connection('pgsql_appwrite')->table('public.name_validation_reject_otp')->insert($otp_table_insert);
            }
            /*----------------- END Database Operation -----------------*/
            $response = array(
                'status' => 4, 'msg' => $msg,
                'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success'
            );
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
        } catch (\Exception $e) {
            dd($e);
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            DB::connection('pgsql_encwrite')->rollback();
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
            );
            $statuscode = 400;
        } finally {
            return response()->json($response, $statuscode);
        }
    }
    /* End Minor Mismatch 90-100% */

    /* Start Minor Mismatch 40-89% */
    // public function editIndex40to89()
    // {
    //     $user_id = Auth::user()->id;
    //     $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    //     $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
    //     $distCode = $dutyObj->district_code;
    //     if (Auth::user()->designation_id == 'Verifier' || Auth::user()->designation_id == 'Delegated Verifier') {
    //         if ($dutyObj->is_urban == 1) {
    //             $ulb_gp = UrbanBody::select('urban_body_code', 'urban_body_name')->where('district_code', $distCode)->where('sub_district_code', $dutyObj->urban_body_code)->get();
    //         } else {
    //             $ulb_gp = GP::select('gram_panchyat_code', 'gram_panchyat_name')->where('block_code', $dutyObj->taluka_code)->get();
    //         }
    //         return view('ben-name-validation-failed/linelisting_name_failed_40_to_89', ['duty_level' => $dutyLevel, 'dist_code' => $dutyObj->district_code, 'ulb_gp' => $ulb_gp]);
    //     } else {
    //         return redirect('/')->with('success', 'Unauthorized');
    //     }
    // }

    // public function getDataNameValidationFailed40to89(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $user_id = Auth::user()->id;
    //         $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    //         $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
    //         $getModelFunc = new getModelFunc();
    //         $schemaname = $getModelFunc->getSchemaDetails();

    //         if ($dutyLevel == 'BlockVerifier' || $dutyLevel == 'BlockDelegated Verifier') {
    //             $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,  f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
    //                 from lb_main.failed_payment_details f 
    //                 JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
    //                 JOIN 
    //                     (
    //                     select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
    //                     union all
    //                     select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
    //                     ) bu ON bu.block_ulb_code=b.block_ulb_code 
    //                 WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND f.legacy_validation_failed = false AND f.is_minor_mismatch = 1";
    //             if (!empty($request->filter_1)) {
    //                 // $old_query .= " AND b.gp_ward_code=" . $request->filter_1 . "";
    //                 //  $completequery .= " AND gp_ward_code=".$request->filter_1."";
    //                 $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
    //                 from lb_main.failed_payment_details f 
    //                 JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
    //                 JOIN 
    //                     (
    //                     select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
    //                     union all
    //                     select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
    //                     ) bu ON bu.block_ulb_code=b.block_ulb_code 
    //                 JOIN 
    //                     (
    //                     select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
    //                     union all
    //                     select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
    //                     ) gw ON gw.gp_ward_code=b.gp_ward_code 
    //                 WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 AND b.gp_ward_code=" . $request->filter_1 . "";
    //             }
    //             // if (!empty($request->pay_mode)) {
    //             //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
    //             // }
    //             $query .= "  order by b.ben_id";
    //         } elseif ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier') {
                
    //             $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
    //                 from lb_main.failed_payment_details f 
    //                 JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
    //                 JOIN 
    //                     (
    //                     select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
    //                     union all
    //                     select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
    //                     ) bu ON bu.block_ulb_code=b.block_ulb_code  
    //                 WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 ";
    //             if (!empty($request->filter_1)  && empty($request->filter_2)) {
    //                 // $old_query .= " AND b.block_ulb_code=" . $request->filter_1 . " ";
    //                 // $completequery .=" AND block_ulb_code=".$request->filter_1." ";
    //                 $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
    //                 from lb_main.failed_payment_details f 
    //                 JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
    //                 JOIN 
    //                     (
    //                     select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
    //                     union all
    //                     select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
    //                     ) bu ON bu.block_ulb_code=b.block_ulb_code 
    //                 JOIN 
    //                     (
    //                     select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
    //                     union all
    //                     select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
    //                     ) gw ON gw.gp_ward_code=b.gp_ward_code 
    //                 WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 AND b.block_ulb_code=" . $request->filter_1 . "";
    //             }
    //             if (!empty($request->filter_1)  && !empty($request->filter_2)) {
    //                 // $old_query .= " AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . "";
    //                 // $completequery .=" AND block_ulb_code=".$request->filter_1." AND b.gp_ward_code=".$request->filter_2."";
    //                 $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name,gw.gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id, f.edited_status
    //                 from lb_main.failed_payment_details f 
    //                 JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
    //                 JOIN 
    //                     (
    //                     select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
    //                     union all
    //                     select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
    //                     ) bu ON bu.block_ulb_code=b.block_ulb_code 
    //                 JOIN 
    //                     (
    //                     select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
    //                     union all
    //                     select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
    //                     ) gw ON gw.gp_ward_code=b.gp_ward_code 
    //                 WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status IN(0,1,2) AND f.is_previous_approved = 0 and b.ben_status=1 AND f.failed_type IN(3,4) AND  f.legacy_validation_failed = false AND f.is_minor_mismatch = 1 AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . "";
    //             }
    //             // if (!empty($request->pay_mode)) {
    //             //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
    //             // }
    //             $query .= "  order by b.ben_id";
    //         }
    //         // $complete = DB::connection('pgsql_appread')->select($completequery);
    //         // dd($query);
    //         $data = DB::connection('pgsql_payment')->select($query);

    //         //print_r($data);die;
    //         return datatables()->of($data)
    //             ->addIndexColumn()
    //             ->addColumn('action', function ($data) {
    //                 $btn = "";
    //                 if ($data->edited_status == 0) {
    //                     $btn = '<button class="btn btn-xs btn-primary bank_edit_btn" type="button" id="' . base64_encode($data->ben_id) . '_' . base64_encode($data->id) . '"><i class="glyphicon glyphicon-edit"></i> View</button>';
    //                 }
    //                 if ($data->edited_status == 1) {
    //                     $btn .= '<span style="font-size: 13px;" class="label label-warning"><b>Pending For Approval</b></span>';
    //                 }
    //                 if ($data->edited_status == 2) {
    //                     $btn .= '<span style="font-size: 13px;" class="label label-success">Approved</span>';
    //                 }
    //                 return $btn;
    //             })
    //             ->addColumn('id', function ($data) {
    //                 return $data->ben_id;
    //             })
    //             ->addColumn('name', function ($data) {
    //                 return $data->ben_name;
    //             })
    //             ->addColumn('block_ulb_name', function ($data) {
    //                 return $data->block_ulb_name;
    //             })
    //             // ->addColumn('gp_ward_name', function ($data) {
    //             //     return $data->gp_ward_name;
    //             // })
    //             ->addColumn('accno', function ($data) {
    //                 return $data->last_accno;
    //             })
    //             ->addColumn('ifsc', function ($data) {
    //                 return $data->last_ifsc;
    //             })
    //             ->addColumn('failure_month', function ($data) {
    //                 if ($data->failed_type == '2') {
    //                     return Config::get('constants.monthval.' . trim($data->lot_month));
    //                 } else {
    //                     return $data->validation_month;
    //                 }
    //             })
    //             ->addColumn('ss_cardno', function ($data) {

    //                 return $data->ss_card_no;
    //             })
    //             ->addColumn('mobile_no', function ($data) {

    //                 return $data->mobile_no;
    //             })
    //             ->addColumn('application_id', function ($data) {

    //                 return $data->application_id;
    //             })
    //             ->addColumn('type', function ($data) {
    //                 $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
    //                 return $msg;
    //             })
    //             // ->with('completed', $complete)
    //             ->rawColumns(['ben_id', 'name', 'block_ulb_name', 'accno', 'ifsc', 'action', 'ss_cardno', 'mobile_no', 'application_id', 'type', 'failure_month'])
    //             ->make(true);
    //     }
    // }

    public function updateNameValidationFailed40to89(Request $request)
    {

    }
    /* End Minor Mismatch 40-89% */
}
