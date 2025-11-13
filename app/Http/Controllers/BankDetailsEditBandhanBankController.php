<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Configduty;
use App\getModelFunc;
use App\LotMaster;
use App\LotDetails;
use App\AvLotmaster;
use App\AvLotdetails;
use App\FailedBankDetails;
use App\UrbanBody;
use App\GP;
use App\BankDetails;
use App\DataSourceCommon;
use Maatwebsite\Excel\Facades\Excel;
use App\DocumentType;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use App\Helpers\DupCheck;
class BankDetailsEditBandhanBankController extends Controller
{
    public $scheme_id;
    public function __construct()
    {
        set_time_limit(300);
        $this->scheme_id = 20;
        $this->middleware('auth');
        // $this->middleware('MaintainMiddleware');
    }
    public function index()
    {
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
            return view('failed-edit-bank-details/linelisting_bank_edit', ['duty_level' => $dutyLevel, 'dist_code' => $dutyObj->district_code, 'ulb_gp' => $ulb_gp]);
        } else {
            return redirect('/')->with('success', 'Unauthorized');
        }
    }
    public function getData(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $user_id = Auth::user()->id;
            $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
            $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();

            if ($dutyLevel == 'BlockVerifier' || $dutyLevel == 'BlockDelegated Verifier') {
                // $completequery="select count(1) from lb_scheme.update_ben_details where local_body_code=".$dutyObj->taluka_code;
                // $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,mb.block_name block_ulb_name,gp.gram_panchyat_name as gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                //     from lb_main.failed_payment_details f 
                //     JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                //     JOIN public.m_block mb ON mb.block_code=b.local_body_code
                //     JOIN public.m_gp gp ON gp.gram_panchyat_code=b.gp_ward_code
                //     WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status=0 and b.ben_status=1 and f.failed_type IN(1,2) AND legacy_validation_failed = false ";
                $query = "select distinct TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name as block_ulb_name, gw.gp_ward_name as gp_ward_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id ,f.edited_status
                    from (select T.* from
                    (
                    SELECT ben_id, MAX(created_at) as max_created_at 
                    FROM lb_main.failed_payment_details  where local_body_code=" . $dutyObj->taluka_code . " and failed_type in(1,2) and edited_status=0 AND legacy_validation_failed = false
                    GROUP BY ben_id
                    ) as S JOIN  lb_main.failed_payment_details  as T ON  S.ben_id=T.ben_id and S.max_created_at=T.created_at) f 
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
                        WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status=0 and b.ben_status=1 and f.failed_type IN(1,2) AND legacy_validation_failed = false ";
                if (!empty($request->filter_1)) {
                    $query .= " AND b.gp_ward_code=" . $request->filter_1 . "";
                    //  $completequery .= " AND gp_ward_code=".$request->filter_1."";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND f.failed_type=" . $request->failed_type . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= "  order by b.ben_id";
            } elseif ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier') {
                //$completequery="select count(1) from lb_scheme.update_ben_details where local_body_code=".$dutyObj->urban_body_code;
                // $query = "select TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,ub.urban_body_name block_ulb_name,wa.urban_body_ward_name as gp_ward_name,f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id 
                //     from lb_main.failed_payment_details f 
                //     JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                //     JOIN public.m_urban_body ub ON ub.urban_body_code=b.block_ulb_code
                //     JOIN public.m_urban_body_ward wa ON wa.urban_body_ward_code=b.gp_ward_code
                //     WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1  and f.failed_type IN(1,2) AND legacy_validation_failed = false ";
                $query = "select distinct TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id,b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name as block_ulb_name, gw.gp_ward_name as gp_ward_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id ,f.edited_status
                    from (select T.* from
                    (
                    SELECT ben_id, MAX(created_at) as max_created_at 
                    FROM lb_main.failed_payment_details  where local_body_code=" . $dutyObj->urban_body_code . " and failed_type in(1,2) and edited_status=0 AND legacy_validation_failed = false
                    GROUP BY ben_id
                    ) as S JOIN  lb_main.failed_payment_details  as T ON  S.ben_id=T.ben_id and S.max_created_at=T.created_at) f 
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
                        WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1 and f.failed_type IN(1,2) AND legacy_validation_failed = false ";
                if (!empty($request->filter_1) && empty($request->filter_2)) {
                    $query .= " AND b.block_ulb_code=" . $request->filter_1 . " ";
                    // $completequery .=" AND block_ulb_code=".$request->filter_1." ";
                }
                if (!empty($request->filter_1) && !empty($request->filter_2)) {
                    $query .= " AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . "";
                    // $completequery .=" AND block_ulb_code=".$request->filter_1." AND b.gp_ward_code=".$request->filter_2."";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND f.failed_type=" . $request->failed_type . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= "  order by b.ben_id";
            }
            // $complete = DB::connection('pgsql_appread')->select($completequery);
            // dd($query);
            $data = DB::connection('pgsql_payment')->select($query);

            //print_r($data);die;
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn = "";
                    $btn = '<button class="btn btn-xs btn-primary bank_edit_btn" type="button" id="' . base64_encode($data->ben_id) . '_' . base64_encode($data->id) . '"><i class="glyphicon glyphicon-edit"></i> View</button>';
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
                ->addColumn('gp_ward_name', function ($data) {
                    return $data->gp_ward_name;
                })
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
                // ->addColumn('reason', function ($data) {
                //     $failed_reason = '';

                //     if ($data->status_code == 'NA') {
                //         $failed_reason = $data->remarks;
                //     } else {
                //         $failed_reason = Config::get('bandhancode.bandhan_response_code.' . trim($data->status_code));
                //     }
                //     return $failed_reason;
                // })
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
                ->rawColumns(['ben_id', 'name', 'block_ulb_name', 'gp_ward_name', 'accno', 'ifsc', 'action', 'ss_cardno', 'mobile_no', 'application_id', 'type', 'failure_month'])
                ->make(true);
        }
    }
    public function editBankDetails(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        //dd($request->all());
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
            // $substrFirstCharacterBenId=substr($ben_id,0,1);
            $tableName = Helper::getTable($ben_id);
            $personalDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $ben_id)->first();

            $bankDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $ben_id)->first();
            $failedReason = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('id', $f_id)->where('edited_status', 0)->first();
            // dd($failedReason);
            //  $imageDetails = DB::connection('pgsql_encread')->table('lb_scheme.ben_attach_documents')->where('beneficiary_id', $ben_id)->where('document_type',10)->first();

            // Showing faliure reason in the modal at the verifier end
            if ($failedReason->pmt_mode == 1) { // Bandhan Bank
                if ($failedReason->failed_type == 1) { // Bandhan Bank Validation 
                    if ($failedReason->status_code == 'NA') {
                        $failed_reason = $failedReason->remarks;
                    } else {
                        $failed_reason = Config::get('bandhancode.bandhan_response_code.' . trim($failedReason->status_code));
                    }
                } else if ($failedReason->failed_type == 2) { // Bandhan Bank Payment
                    $failed_reason = Config::get('bandhancode.bandhan_transaction_response_code.' . trim($failedReason->status_code));
                } else {
                    $failed_reason = '';
                }
            } else if ($failedReason->pmt_mode == 2) { // SBI
                if ($failedReason->failed_type == 1) { // SBI Validation 
                    $failed_reason = Config::get('bandhancode.sbi_response_code.' . trim($failedReason->status_code));
                } else if ($failedReason->failed_type == 2) { // SBI Payment
                    $failed_reason = Config::get('bandhancode.sbi_transaction_response_code.' . trim($failedReason->status_code));
                } else {
                    $failed_reason = '';
                }
            } else {
                $failed_reason = '';
            }
            // Showing resaon end

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
            $status_code = $failedReason->status_code;
            $application_id = $personalDetails->application_id;
            // $status_code ='-7';
            $response = array(
                'ben_name' => $ben_name,
                'benfather_name' => $benfather_name,
                'mobile_no' => $mobile_no,
                'caste' => $caste,
                'dob' => $dob,
                'gender' => $gender,
                'bank_name' => $bank_name,
                'bank_ifsc' => $bank_ifsc,
                'branch_name' => $branch_name,
                'bank_code' => $bank_code,
                'ben_id' => $ben_id,
                'benid' => $failed_reason,
                'failed_reason' => $failed_reason,
                'failedid' => $f_id,
                'status_code' => $status_code,
                'fname' => $fname,
                'mname' => $mname,
                'lname' => $lname,
                'application_id' => $application_id
            );
        } catch (\Exception $e) {
            // dd($e);
            $response = array(
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' => 'Something went wrong..',
            );
            $statuscode = 400;
        } finally {
            return response()->json($response, $statuscode);
        }
    }
    public function updateBankDetails(Request $request)
    {
        // dd($request->all());
        date_default_timezone_set('Asia/Kolkata');
        $statuscode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statuscode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statuscode);
        }
        try {
            DB::beginTransaction();
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
            $this->validateInput($request);
            $getModelFunc = new getModelFunc();
            $pension_details_encloser1 = new DataSourceCommon;
            $pension_details_encloser2 = new DataSourceCommon;
            $Table = $getModelFunc->getTable('', '', 6, 1);
            $Table2 = $getModelFunc->getTableFaulty('', '', 6, 1);
            $pension_details_encloser1->setConnection('pgsql_encwrite');
            $pension_details_encloser2->setConnection('pgsql_encwrite');
            $pension_details_encloser1->setTable('' . $Table);
            $pension_details_encloser2->setTable('' . $Table2);
            $schemaname = $getModelFunc->getSchemaDetails();
            $beneficiary_id = $request->benId;
            $failed_table_id = $request->faildTableId;
            $fObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                ->where('id', $failed_table_id)->where('edited_status', 0)->first();
            $currentyear = date('Y');
            $prevYear = date('Y') - 1;
            $nextyear = date('Y') + 1;
            $month = date('n');
            if ($month > 3) {
                $cur_fin_year = $currentyear . '-' . $nextyear;
            } else {
                $cur_fin_year = $prevYear . '-' . ($prevYear + 1);
            }
            $request_old_bank_ifsc = $request->old_bank_ifsc;
            $request_old_bank_accno = $request->old_bank_accno;
            $new_bank_name = $request->bank_name;
            $new_bank_acc_no = $request->bank_account_number;
            $new_branch_name = $request->branch_name;
            $new_bank_ifsc = $request->bank_ifsc;
            $new_fname = $request->ben_fname_value;
            $new_mname = $request->ben_mname_value;
            $new_lname = $request->ben_lname_value;
            $statusCode = $request->statusCode;
            $application_id = $request->application_id;
            $bank_details = BankDetails::where('is_active', 1)->where('ifsc', $new_bank_ifsc)->get(['bank', 'branch'])->first();
            if (!empty($bank_details)) {
                if ((trim($bank_details->bank) == trim($new_bank_name)) && (trim($bank_details->branch) == trim($new_branch_name))) {
                    $duplicate_row = DB::connection('pgsql_appwrite')->select("select count(1) as cnt from lb_scheme.duplicate_bank_view where trim(bank_code)='" . $new_bank_acc_no . "'  and application_id <> $application_id ");
                    $accountCheck = $duplicate_row[0]->cnt;
                    $dupBankTableCheck = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')
                        ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->count('ben_id');
                    if ($accountCheck > 0) {
                        return $response = array(
                            'status' => 5,
                            'msg' => 'This bank account no is already registered.',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Duplicate!'
                        );
                    }
                    if (!empty($new_bank_acc_no)) {
                        $DupCheckBankOap = DupCheck::getDupCheckBank(10, $new_bank_acc_no);
                        if (!empty($DupCheckBankOap)) {
                            return $response = array(
                                'status' => 5,
                                'msg' => 'Duplicate Bank Account Number present in Old Age Pension Scheme with Beneficiary ID- ' . $DupCheckBankOap . '',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Duplicate!'
                            );
                        }
                        $DupCheckBankJohar = DupCheck::getDupCheckBank(1, $new_bank_acc_no);
                        if (!empty($DupCheckBankJohar)) {
                            return $response = array(
                                'status' => 5,
                                'msg' => 'Duplicate Bank Account Number present Jai Johar Pension Scheme with Beneficiary ID- ' . $DupCheckBankJohar . '',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Duplicate!'
                            );
                        }
                        $DupCheckBankBandhu = DupCheck::getDupCheckBank(3, $new_bank_acc_no);
                        if (!empty($DupCheckBankBandhu)) {
                            return $response = array(
                                'status' => 5,
                                'msg' => 'Duplicate Bank Account Number present Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- ' . $DupCheckBankBandhu . '',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Duplicate!'
                            );
                        }
                    }

                    $tableName = Helper::getTable($beneficiary_id);
                    $personalDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $beneficiary_id)->first();
                    if (($request_old_bank_ifsc != $new_bank_ifsc) || ($request_old_bank_accno != $new_bank_acc_no)) {

                        /* Document Upload for new bank details upload  */
                        if (!empty($request->file('upload_bank_passbook'))) {
                            DB::connection('pgsql_encwrite')->beginTransaction();
                            $attributes = array();
                            $pension_details = array();
                            $query = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', 10);
                            $doc_arr = $query->first();
                            $required = 'required';
                            $rules['upload_bank_passbook'] = $required . '|mimes:' . $doc_arr->doc_type . '|max:' . $doc_arr->doc_size_kb . ',';
                            $messages['upload_bank_passbook.max'] = "The file uploaded for " . $doc_arr->doc_name . " size must be less than :max KB";
                            $messages['upload_bank_passbook.mimes'] = "The file uploaded for " . $doc_arr->doc_name . " must be of type " . $doc_arr->doc_type;
                            $messages['upload_bank_passbook.required'] = "Document for " . $doc_arr->doc_name . " must be uploaded";
                            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
                            if ($validator->passes()) {
                                $valid = 1;
                            } else {
                                $valid = 0;
                                $return_msg = $validator->errors()->all();
                                $return_status = 0;

                                $response = array(
                                    'status' => 3,
                                    'msg' => $return_msg,
                                    'type' => 'red',
                                    'icon' => 'fa fa-warning',
                                    'title' => 'Error'
                                );
                            }
                            if ($valid == 1) { // echo 9;die;
                                $upload_bank_passbook = $request->file('upload_bank_passbook');
                                $img_data = file_get_contents($upload_bank_passbook);
                                $extension = $upload_bank_passbook->getClientOriginalExtension();
                                $mime_type = $upload_bank_passbook->getMimeType();
                                //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                                $base64 = base64_encode($img_data);
                                $tableName = Helper::getTable('', $personalDetails->application_id);
                                // dd($doc_arr->id);
                                $insertIntoArchieve = "INSERT INTO lb_scheme.ben_attach_documents_arch(
                                application_id, beneficiary_id, document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,doc_status)
                                select application_id, beneficiary_id, document_type, attched_document, created_by_level,created_at,updated_at, '" . date('Y-m-d H:i:s') . "', created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,1
                                from lb_scheme." . $tableName['benDocTable'] . " where application_id = " . $personalDetails->application_id . " and document_type = " . $doc_arr->id;
                                // echo $insertIntoArchieve;die;
                                //  if($beneficiary_id == 209032483){
                                //     echo $insertIntoArchieve;die;
                                // }
                                $executeInsert = DB::connection('pgsql_encwrite')->select($insertIntoArchieve);
                                if ($executeInsert) {
                                    $pension_details['attched_document'] = $base64;
                                    $pension_details['document_extension'] = $extension;
                                    $pension_details['document_mime_type'] = $mime_type;
                                    $pension_details['updated_at'] = date('Y-m-d H:i:s');
                                    $pension_details['action_by'] = Auth::user()->id;
                                    $pension_details['action_ip_address'] = request()->ip();
                                    $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                                    if ($personalDetails->is_faulty == false) {
                                        $docBankUpdate = $pension_details_encloser1->where('document_type', $doc_arr->id)
                                            ->where('application_id', $personalDetails->application_id)->update($pension_details);
                                    } else {
                                        $docBankUpdate = $pension_details_encloser2->where('document_type', $doc_arr->id)
                                            ->where('application_id', $personalDetails->application_id)->update($pension_details);
                                    }
                                }
                                //   if($beneficiary_id == 209032483){
                                //         dd($docBankUpdate);
                                //     }
                            }

                        } else {
                            $response = array(
                                'status' => 2,
                                'msg' => 'Please upload bank passbook copy.',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Required'
                            );
                        }
                    }
                    /* End Document Upload */
                    $bankDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $beneficiary_id)->first();
                    $contactDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benContactTable'])->where('beneficiary_id', $beneficiary_id)->first();
                    $old_bank_name = $bankDetails->bank_name;
                    $old_bank_acc_no = $bankDetails->bank_code;
                    $old_branch_name = $bankDetails->branch_name;
                    $old_bank_ifsc = $bankDetails->bank_ifsc;
                    $old_fname = $personalDetails->ben_fname;
                    $old_mname = $personalDetails->ben_mname;
                    $old_lname = $personalDetails->ben_lname;

                    $new_value = [];
                    $old_value = [];

                    $old_value['bank_name'] = trim($old_bank_name);
                    $old_value['branch_name'] = trim($old_branch_name);
                    $old_value['bank_ifsc'] = trim($old_bank_ifsc);
                    $old_value['bank_code'] = trim($old_bank_acc_no);
                    if ($statusCode === '-7') {
                        $old_value['ben_fname'] = trim($old_fname);
                        $old_value['ben_mname'] = trim($old_mname);
                        $old_value['ben_lname'] = trim($old_lname);

                        $new_value['ben_fname'] = trim($new_fname);
                        $new_value['ben_mname'] = trim($new_mname);
                        $new_value['ben_lname'] = trim($new_lname);
                    }
                    $new_value['bank_name'] = trim($new_bank_name);
                    $new_value['branch_name'] = trim($new_branch_name);
                    $new_value['bank_ifsc'] = trim($new_bank_ifsc);
                    $new_value['bank_code'] = trim($new_bank_acc_no);
                    if ($fObj->failed_type == '1') {
                        $bank_update_code = 35;
                    } else if ($fObj->failed_type == '2') {
                        $bank_update_code = 36;
                    }
                    $insert = [
                        'failed_tbl_id' => $failed_table_id,
                        'beneficiary_id' => $beneficiary_id,
                        'user_id' => Auth::user()->id,
                        'old_data' => json_encode($old_value),
                        'new_data' => json_encode($new_value),
                        'next_level_role_id' => 1,
                        'dist_code' => $bankDetails->created_by_dist_code,
                        'local_body_code' => $bankDetails->created_by_local_body_code,
                        'rural_urban_id' => $contactDetails->rural_urban_id,
                        'block_ulb_code' => $contactDetails->block_ulb_code,
                        'gp_ward_code' => $contactDetails->gp_ward_code,
                        'created_at' => date('Y-m-d H:i:s'),
                        'pmt_mode' => $fObj->pmt_mode,
                        'failed_type' => $fObj->failed_type,
                        'ip_address' => request()->ip(),
                        'update_code' => $bank_update_code,
                        'legacy_validation_update' => $fObj->legacy_validation_failed
                    ];
                    $condition = "";
                    if ($fObj->failed_type == '2') {
                        $condition .= "  failed_type='2' and edited_status='0' ";
                    } else if ($fObj->failed_type == '1') {
                        $condition .= "  failed_type='1' and edited_status='0' ";
                    }
                    //     if($beneficiary_id == 210367670){
                    //         dump($request_old_bank_ifsc);dump($new_bank_ifsc);dump($request_old_bank_accno);dd($new_bank_acc_no);
                    //    }
                    if (($request_old_bank_ifsc === $new_bank_ifsc) && ($request_old_bank_accno === $new_bank_acc_no)) {
                        //     if($beneficiary_id == 210367670){
                        //         dd('okey');
                        //    }
                        if ($dupBankTableCheck > 0) {
                            $insertDup = [
                                // 'failed_tbl_id' => $failed_table_id,
                                'beneficiary_id' => $beneficiary_id,
                                'user_id' => Auth::user()->id,
                                'old_data' => json_encode($old_value),
                                'new_data' => json_encode($new_value),
                                'next_level_role_id' => 0,
                                'dist_code' => $bankDetails->created_by_dist_code,
                                'local_body_code' => $bankDetails->created_by_local_body_code,
                                'rural_urban_id' => $contactDetails->rural_urban_id,
                                'block_ulb_code' => $contactDetails->block_ulb_code,
                                'gp_ward_code' => $contactDetails->gp_ward_code,
                                'created_at' => date('Y-m-d H:i:s'),
                                'pmt_mode' => $fObj->pmt_mode,
                                'failed_type' => $fObj->failed_type,
                                'update_code' => 200,
                                'ip_address' => request()->ip(),
                                'legacy_validation_update' => $fObj->legacy_validation_failed
                            ];
                            $payment_dup_update = [
                                'ben_status' => 200,
                                'is_approved' => 1,
                            ];
                            $ben_details_update = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insertDup);
                            $ben_payment_bank_dup = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')
                                ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update($payment_dup_update);
                            $ben_payment_update = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
                                ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update(['ben_status'=> 1]);
                        }
                        $update_ben_track = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insert);

                        $getNpciBankCode = BankDetails::where('ifsc', $request->bank_ifsc)->first();
                        if ($getNpciBankCode) {
                            $newPaymentDetails = [
                                'new_bank_name' => trim( $request->bank_name),
                                'new_bank_branch' => trim($request->branch_name),
                                'new_bank_ifsc' => $request->bank_ifsc,
                                'new_bank_code' => trim($request->bank_account_number),
                                'npci_bank_code' => $getNpciBankCode->bank_code
                            ];
                        }


                        $failed_update_payment = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                            ->whereRaw($condition)
                            ->where('ben_id', $beneficiary_id)->update([
                                    'edited_status' => '1',
                                    'updated_at' => date('Y-m-d H:i:s'),'updated_details' => json_encode($newPaymentDetails)
                                ]);
                        $update_ben_update = [
                            'next_level_role_id' => 0,
                            'update_code' => 1,
                            'remarks' => 'Same bank details. Direct Approved',
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        $ben_payment_paymentServer_update = [
                            'acc_validated' => 0,
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        $failed_tbl_id = array();
                        // if ($fObj->failed_type == '2') {
                        //     $failedTableObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                        //         ->where('ben_id', $beneficiary_id)
                        //         ->where('edited_status', '1')->get();
                        //     foreach ($failedTableObj as $failedTableObjValue) {
                        //         array_push($failed_tbl_id, $failedTableObjValue->id);
                        //         if ($failedTableObjValue->fin_year == $cur_fin_year) {
                        //             $pmtMode = $failedTableObjValue->pmt_mode;
                        //             if ($pmtMode == 1) {
                        //                 $lotMode = DB::connection('pgsql_payment')->table($schemaname . '.lot_master')->where('lot_no', $failedTableObjValue->lot_no)->value('lot_type');
                        //             } else if ($pmtMode == 2) {
                        //                 $lotMode = DB::connection('pgsql_payment')->table($schemaname . '.sbi_lot_master')->where('lot_no', $failedTableObjValue->lot_no)->value('lot_type');
                        //             }
                        //             $lotMonth = $failedTableObjValue->lot_month;
                        //             $ben_status_columns = Helper::getMonthColumn($lotMonth);
                        //             $lotType = $ben_status_columns['lot_type'];
                        //             $lotStatus = $ben_status_columns['lot_status'];

                        //             if (trim($lotMode) == 'R') { // Regular Lot
                        //                 $add_ben_payment_details = array($lotType => 'E', $lotStatus => 'E');
                        //             } else if (trim($lotMode) == 'A') { // Arrear Lot
                        //                 $add_ben_payment_details = array($lotType => 'D', $lotStatus => 'E');
                        //             }
                        //             //echo "<pre>";print_r($add_ben_payment_details);die;
                        //             $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $add_ben_payment_details);
                        //             // echo "<pre>";print_r($add_ben_payment_details);die;
                        //             if ($failedTableObjValue->legacy_validation_failed == TRUE) {
                        //                 $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 10));
                        //             }
                        //         } else {
                        //             $ben_status_columns = Helper::getMonthColumn(13);
                        //             $lotType = $ben_status_columns['lot_type'];
                        //             $lotStatus = $ben_status_columns['lot_status'];
                        //             $add_ben_payment_details = array($lotType => 'D', $lotStatus => 'E');
                        //             $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $add_ben_payment_details);
                        //             if ($failedTableObjValue->legacy_validation_failed == TRUE) {
                        //                 $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 10));
                        //             }
                        //         }
                        //     }
                        // }
                        $update_personal_details = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])
                            ->where('beneficiary_id', $beneficiary_id)->update(['action_by' => Auth::user()->id, 'action_ip_address' => request()->ip(), 'action_type' => class_basename(request()->route()->getAction()['controller']), 'status' => '1']);
                        //  if($beneficiary_id == 210367670){
                        //      dd($update_personal_details);
                        // }
                        $ben_payment_update2 = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
                            ->where('ben_id', $beneficiary_id)->update($ben_payment_paymentServer_update);

                        $update_ben_track2 = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)
                            ->where('next_level_role_id', 1)->update($update_ben_update);

                        // $failed_update_payment2 = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                        //     ->where('edited_status', '1')
                        //     ->where('ben_id', $beneficiary_id)->update([
                        //             'edited_status' => '2',
                        //             'updated_at' => date('Y-m-d H:i:s')
                        //         ]);
                        $response = array(
                            'status' => 4,
                            'msg' => 'Bank Details Updated Succesfully.',
                            'type' => 'green',
                            'icon' => 'fa fa-check',
                            'title' => 'Success'
                        );
                    } else {
                        // dd('ok22');
                        // if($beneficiary_id == 209032483){
                        //     dd($dupBankTableCheck);
                        // }
                        if ($dupBankTableCheck > 0) {
                            $insertDup = [
                                // 'failed_tbl_id' => $failed_table_id,
                                'beneficiary_id' => $beneficiary_id,
                                'user_id' => Auth::user()->id,
                                'old_data' => json_encode($old_value),
                                'new_data' => json_encode($new_value),
                                'next_level_role_id' => 0,
                                'dist_code' => $bankDetails->created_by_dist_code,
                                'local_body_code' => $bankDetails->created_by_local_body_code,
                                'rural_urban_id' => $contactDetails->rural_urban_id,
                                'block_ulb_code' => $contactDetails->block_ulb_code,
                                'gp_ward_code' => $contactDetails->gp_ward_code,
                                'created_at' => date('Y-m-d H:i:s'),
                                'pmt_mode' => $fObj->pmt_mode,
                                'failed_type' => $fObj->failed_type,
                                'update_code' => 101,
                                'ip_address' => request()->ip(),
                                'legacy_validation_update' => $fObj->legacy_validation_failed
                            ];
                            $payment_dup_update = [
                                'new_last_accno' => $new_bank_acc_no,
                                'new_last_ifsc' => $new_bank_ifsc,
                                'ben_status' => 101,
                                'is_approved' => 1,
                            ];
                            $update_ben_track3 = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insertDup);
                            $payment_details_bank_code_dup = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')
                                ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update($payment_dup_update);
                            $ben_payment_update3 = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
                                ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update(['ben_status' => 1]);
                        }
                        $update_ben_track4 = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insert);
                        $failed_update_payment3 = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
                            ->whereRaw($condition)
                            ->where('ben_id', $beneficiary_id)->update([
                                    'edited_status' => '1'
                                ]);
                        // if($beneficiary_id == 209032483){
                        //     dump($ben_payment_update3);dump($update_ben_track4);dd($failed_update_payment3);
                        // }
                        $response = array(
                            'status' => 1,
                            'msg' => 'Bank Details Updated Succesfully.',
                            'type' => 'green',
                            'icon' => 'fa fa-check',
                            'title' => 'Success'
                        );
                    }
                    if (($request_old_bank_ifsc === $new_bank_ifsc) && ($request_old_bank_accno === $new_bank_acc_no)) {
                        if ($update_personal_details && $update_ben_track && $update_ben_track2 && $failed_update_payment  && $ben_payment_update2) {
                            DB::commit();
                            DB::connection('pgsql_appwrite')->commit();
                            DB::connection('pgsql_payment')->commit();
                        }
                    } else if (($request_old_bank_ifsc != $new_bank_ifsc) || ($request_old_bank_accno != $new_bank_acc_no)) {
                        if ($docBankUpdate && $update_ben_track4 && $failed_update_payment3) {
                            DB::commit();
                            DB::connection('pgsql_encwrite')->commit();
                            DB::connection('pgsql_appwrite')->commit();
                            DB::connection('pgsql_payment')->commit();
                        }
                    }
                } else {
                    //     if($beneficiary_id == 210367670){
                    //         dd('okk');
                    //    }
                    $response = array(
                        'status' => 6,
                        'msg' => 'Bank account name or bank branch name are not matched',
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Not Match'
                    );
                }
            } else {

                $response = array(
                    'status' => 5,
                    'msg' => 'This ' . $new_bank_ifsc . ' IFSC is not registered in our system.',
                    'type' => 'blue',
                    'icon' => 'fa fa-info',
                    'title' => 'IFSC Not Found'
                );
            }


        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            DB::connection('pgsql_encwrite')->rollback();
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            $response = array(
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' => 'Oops... Bank Details Not Updated.',
            );
            $statuscode = 400;
        } finally {
            return response()->json($response, $statuscode);
        }
    }
    private function validateInput($request)
    {
        $this->validate($request, [
            //'mobile_no' => 'required:|regex:/[0-9]{10}/',
            'bank_name' => 'required|string|max:200',
            'branch_name' => 'required|string|max:200',
            // 'bank_account_number' => 'required|numeric|between:00000000000000000000,9999999999999999999',
            'bank_account_number' => 'required',
            'bank_ifsc' => 'required|max:20',

        ]);
    }

    public function verified()
    {
        // return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
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
            return view('failed-edit-bank-details.completed_bank_edit', ['duty_level' => $dutyLevel, 'dist_code' => $dutyObj->district_code, 'ulb_gp' => $ulb_gp]);
        } else if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
            $levels = [
                2 => 'Rural',
                1 => 'Urban'
            ];
            return view('failed-edit-bank-details.completed_bank_edited_approved', ['levels' => $levels, 'dist_code' => $distCode, 'dutyLevel' => $dutyLevel]);
        } else {
            return redirect('/')->with('success', 'Unauthorized');
        }
    }
    public function completedBankValidationVerified(Request $request)
    { //dd($request->all());
        if ($request->ajax()) {
            $user_id = Auth::user()->id;
            $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
            $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;

            if (!empty($request->failed_type)) {
                $f_type = $request->failed_type;
                if ($f_type == 3) {
                    $failedWhereCon = "IN (3,4)";
                } else {
                    $failedWhereCon = "IN (" . $f_type . ")";
                }
            }

            if ($dutyLevel == 'BlockVerifier' || $dutyLevel == 'BlockDelegated Verifier') {
                // $query = "(select u.*,b.ben_fname,b.ben_mname,b.ben_lname,mb.block_name block_ulb_name,gp.gram_panchyat_name as gp_ward_name
                //     from lb_scheme.update_ben_details u
                //     JOIN lb_scheme.ben_personal_details b ON u.beneficiary_id=b.beneficiary_id
                //     JOIN public.m_block mb ON mb.block_code=u.local_body_code
                //     JOIN public.m_gp gp ON gp.gram_panchyat_code=u.gp_ward_code
                //     WHERE u.local_body_code=" . $dutyObj->taluka_code . " and u.failed_type in(1,2,3)";
                $query = "(select u.*,b.ben_fname,b.ben_mname,b.ben_lname,bu.block_ulb_name, gw.gp_ward_name 
                    from lb_scheme.update_ben_details u
                    JOIN lb_scheme.ben_personal_details b ON u.beneficiary_id=b.beneficiary_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=u.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=u.gp_ward_code
                    WHERE u.local_body_code=" . $dutyObj->taluka_code . " and b.next_level_role_id = 0 and (u.failed_type=1 OR (u.failed_type in(2,3,4) AND legacy_validation_update = false)) ";
                if (!empty($request->filter_1)) {
                    $query .= " AND u.gp_ward_code=" . $request->filter_1 . "";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND u.failed_type " . $failedWhereCon . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND u.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= " and u.next_level_role_id in(1,2,5) order by b.beneficiary_id)";


                $query .= " union all (select u.*,b.ben_fname,b.ben_mname,b.ben_lname, bu.block_ulb_name, gw.gp_ward_name 
                from lb_scheme.update_ben_details u
                JOIN lb_scheme.faulty_ben_personal_details b ON u.beneficiary_id=b.beneficiary_id
                JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=u.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=u.gp_ward_code
                WHERE u.local_body_code=" . $dutyObj->taluka_code . " and  (u.failed_type=1 OR (u.failed_type in(2,3,4) AND legacy_validation_update = false))";
                if (!empty($request->filter_1)) {
                    $query .= " AND u.gp_ward_code=" . $request->filter_1 . "";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND u.failed_type " . $failedWhereCon . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND u.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= " and u.next_level_role_id in(1,2,5) order by b.beneficiary_id )";
            } elseif ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier') {
                // $query = "(select  u.*,b.beneficiary_id,b.ben_fname,b.ben_mname,b.ben_lname, ub.urban_body_name block_ulb_name,wa.urban_body_ward_name as gp_ward_name
                //     from lb_scheme.update_ben_details u 
                //     JOIN lb_scheme.ben_personal_details b ON u.beneficiary_id=b.beneficiary_id
                //     JOIN public.m_urban_body ub ON ub.urban_body_code=u.block_ulb_code
                //     JOIN public.m_urban_body_ward wa ON wa.urban_body_ward_code=u.gp_ward_code
                //     WHERE u.local_body_code=" . $dutyObj->urban_body_code . " and u.failed_type in(1,2,3)";

                $query = "(select  u.*,b.beneficiary_id,b.ben_fname,b.ben_mname,b.ben_lname, bu.block_ulb_name, gw.gp_ward_name
                    from lb_scheme.update_ben_details u 
                    JOIN lb_scheme.ben_personal_details b ON u.beneficiary_id=b.beneficiary_id
                    JOIN 
                        (
                        select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                        union all
                        select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                        ) bu ON bu.block_ulb_code=u.block_ulb_code 
                    JOIN 
                        (
                        select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                        union all
                        select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                        ) gw ON gw.gp_ward_code=u.gp_ward_code
                    WHERE u.local_body_code=" . $dutyObj->urban_body_code . " and next_level_role_id = 0 and (u.failed_type=1 OR (u.failed_type in(2,3,4) AND legacy_validation_update = false)) ";

                if (!empty($request->filter_1) && empty($request->filter_2)) {
                    $query .= " AND u.block_ulb_code=" . $request->filter_1 . " ";
                }
                if (!empty($request->filter_1) && !empty($request->filter_2)) {
                    $query .= " AND u.block_ulb_code=" . $request->filter_1 . " AND u.gp_ward_code=" . $request->filter_2 . "";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND u.failed_type " . $failedWhereCon . "";
                }

                $query .= " and u.next_level_role_id in(1,2,5)  order by u.beneficiary_id )";

                $query .= " union all (select  u.*,b.beneficiary_id,b.ben_fname,b.ben_mname,b.ben_lname, bu.block_ulb_name, gw.gp_ward_name
                from lb_scheme.update_ben_details u 
                JOIN lb_scheme.faulty_ben_personal_details b ON u.beneficiary_id=b.beneficiary_id
                JOIN 
                    (
                    select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                    union all
                    select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                    ) bu ON bu.block_ulb_code=u.block_ulb_code 
                JOIN 
                    (
                    select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                    union all
                    select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                    ) gw ON gw.gp_ward_code=u.gp_ward_code
                WHERE u.local_body_code=" . $dutyObj->urban_body_code . " and  (u.failed_type=1 OR (u.failed_type in(2,3,4) AND legacy_validation_update = false))";

                if (!empty($request->filter_1) && empty($request->filter_2)) {
                    $query .= " AND u.block_ulb_code=" . $request->filter_1 . " ";
                }
                if (!empty($request->filter_1) && !empty($request->filter_2)) {
                    $query .= " AND u.block_ulb_code=" . $request->filter_1 . " AND u.gp_ward_code=" . $request->filter_2 . "";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND u.failed_type " . $failedWhereCon . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND u.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= " and u.next_level_role_id in(1,2,5)  order by u.beneficiary_id )";
                //return $query;
            }
            // dd($query);
            $data = DB::connection('pgsql_appread')->select($query);

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn = "";
                    $btn = '<button class="btn btn-xs btn-primary bank_edit_btn" type="button" id="' . base64_encode($data->beneficiary_id) . '_' . base64_encode($data->id) . '"><i class="glyphicon glyphicon-edit"></i> View</button>';
                    return $btn;
                })
                ->addColumn('beneficiary_id', function ($data) {
                    return $data->beneficiary_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname;
                })
                ->addColumn('block_ulb_name', function ($data) {
                    return $data->block_ulb_name;
                })
                ->addColumn('gp_ward_name', function ($data) {
                    return $data->gp_ward_name;
                })
                ->addColumn('accno', function ($data) {

                    return json_decode($data->new_data)->bank_code;
                })
                ->addColumn('ifsc', function ($data) {
                    return json_decode($data->new_data)->bank_ifsc;
                })
                ->addColumn('type', function ($data) {
                    $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
                    return $msg;
                })
                ->addColumn('status', function ($data) {
                    $status = '';
                    if ($data->next_level_role_id == '1' || $data->next_level_role_id == '5') {
                        $status = '<span class="label label-success">Approval Pending</span>';
                    } else if ($data->next_level_role_id == '2') {
                        $status = '<span class="label label-danger">Reverted</span>';
                    }
                    return $status;
                })

                ->rawColumns(['beneficiary_id', 'name', 'block_ulb_name', 'gp_ward_name', 'accno', 'ifsc', 'type', 'status'])
                ->make(true);
        }
    }
    public function completedBankValidationApproved(Request $request)
    {
        // dd($request->all());
        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $distCode = $dutyObj->district_code;
        $rural_urban = $request->filter_1;
        $local_body_code = $request->filter_2;
        $block_ulb_code = $request->block_ulb_code;
        $gp_ward_code = $request->gp_ward_code;
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        if ($request->ajax()) {
            if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
                if ($rural_urban == 1) {
                    $query = '';
                    $query .= "select bp.ben_name as name, ms.sub_district_name as block_subdiv_name,
                    bp.ss_card_no, bp.mobile_no, fp.* 
                    from (select T.* from
                    (
                    SELECT ben_id, MAX(created_at) as max_created_at 
                    FROM lb_main.failed_payment_details  where dist_code=" . $distCode . " and failed_type in(1,2,3,4) and edited_status 
                    IN(0,1) AND legacy_validation_failed = false
                    GROUP BY ben_id
                    ) as S JOIN  lb_main.failed_payment_details  as T ON  S.ben_id=T.ben_id and S.max_created_at=T.created_at) fp 
                    JOIN " . $schemaname . ".ben_payment_details bp ON bp.ben_id=fp.ben_id 
                    JOIN public.m_sub_district ms ON ms.sub_district_code=bp.local_body_code 
                    where fp.dist_code=" . $distCode . " and fp.edited_status in(0,1) and bp.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false ";
                    if (!empty($rural_urban)) {
                        $query .= " and bp.rural_urban_id=" . $rural_urban . " ";
                    }
                    if (!empty($local_body_code)) {
                        $query .= " and bp.local_body_code=" . $local_body_code . " ";
                    }
                    if (!empty($request->failed_type)) {
                        $query .= " AND fp.failed_type=" . $request->failed_type . "";
                    }
                    // if (!empty($request->pay_mode)) {
                    //     $query .= " AND fp.pmt_mode=" . $request->pay_mode . "";
                    // }
                    $query .= " order by bp.ben_id";
                    // and pmt_mode=1 and failed_type=1

                } elseif ($rural_urban == 2) {
                    $query = '';
                    $query = "select bp.ben_name as name, mb.block_name as block_subdiv_name,
                    bp.ss_card_no, bp.mobile_no, fp.* 
                    from (select T.* from
                    (
                    SELECT ben_id, MAX(created_at) as max_created_at 
                    FROM lb_main.failed_payment_details  where dist_code=" . $distCode . " and failed_type in(1,2,3,4) and edited_status IN(0,1) AND legacy_validation_failed = false
                    GROUP BY ben_id
                    ) as S JOIN  lb_main.failed_payment_details  as T ON  S.ben_id=T.ben_id and S.max_created_at=T.created_at) fp 
                    JOIN " . $schemaname . ".ben_payment_details bp ON bp.ben_id=fp.ben_id 
                    JOIN public.m_block mb ON mb.block_code=bp.local_body_code
                    where fp.dist_code=" . $distCode . " and fp.edited_status in(0,1) and bp.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false ";
                    if (!empty($rural_urban)) {
                        $query .= " and bp.rural_urban_id=" . $rural_urban . " ";
                    }
                    if (!empty($local_body_code)) {
                        $query .= " and bp.local_body_code=" . $local_body_code . " ";
                    }
                    if (!empty($request->failed_type)) {
                        $query .= " AND fp.failed_type=" . $request->failed_type . "";
                    }
                    // if (!empty($request->pay_mode)) {
                    //     $query .= " AND fp.pmt_mode=" . $request->pay_mode . "";
                    // }
                    $query .= " order by bp.ben_id";

                    // and pmt_mode=1 and failed_type=1
                    // echo $query;die;
                } else {
                    $query = '';
                    $query = "select bp.ben_name as name, bl_ulb.block_ulb_name as block_subdiv_name,
                    bp.ss_card_no, bp.mobile_no, fp.* 
                    from (select T.* from
                    (
                    SELECT ben_id, MAX(created_at) as max_created_at 
                    FROM lb_main.failed_payment_details  where dist_code=" . $distCode . " and failed_type in(1,2,3,4) and edited_status IN (0,1) AND legacy_validation_failed = false
                    GROUP BY ben_id
                    ) as S JOIN  lb_main.failed_payment_details  as T ON  S.ben_id=T.ben_id and S.max_created_at=T.created_at) fp 
                    JOIN " . $schemaname . ".ben_payment_details bp ON bp.ben_id=fp.ben_id 
                    JOIN (select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block UNION ALL
                         select sub_district_code as block_ulb_code, sub_district_name as block_ulb_name from public.m_sub_district
                         ) bl_ulb ON bl_ulb.block_ulb_code=bp.local_body_code
                    where fp.dist_code=" . $distCode . " and fp.edited_status in(0,1) and bp.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false";
                    if (!empty($request->failed_type)) {
                        $query .= " AND fp.failed_type=" . $request->failed_type . "";
                    }
                    $query .= " order by bp.ben_id";
                    //  and pmt_mode=1 and failed_type=1
                }
                // dd($query);
                $data = DB::connection('pgsql_payment')->select($query);
            } else {
                $data = collect([]);
            }
            // print_r($data);
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('view', function ($data) {
                    $action = '<button class="btn btn-primary btn-xs ben_view_button" value="' . $data->id . '_' . $data->ben_id . '"><i class="glyphicon glyphicon-edit"></i>View</button>';
                    return $action;
                })
                // ->addColumn('check', function ($data) {
                //     return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->id . '_' . $data->ben_id . '">';
                // })
                ->addColumn('beneficiary_id', function ($data) {
                    return $data->ben_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->name;
                })
                ->addColumn('block_ulb_name', function ($data) {
                    return $data->block_subdiv_name;
                })
                // ->addColumn('gp_ward_name', function ($data) {
                //     return '';
                // })
                ->addColumn('ss_card_no', function ($data) {
                    return $data->ss_card_no;
                    // return json_decode($data->new_data)->bank_code;
                })
                ->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                    // return json_decode($data->new_data)->bank_ifsc;
                })
                ->addColumn('type', function ($data) {
                    $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
                    return $msg;
                })
                ->addColumn('status', function ($data) {
                    $status = '';

                    // if ($data->next_level_role_id == '0') {
                    //     $status = '<span class="label label-success">Approved</span>';
                    // } else if ($data->next_level_role_id == '2') {
                    //     $status = '<span class="label label-danger">Reverted</span>';
                    // } else {
                    //     $status = '<span class="label label-warning">Pending</span>';
                    // }
                    if ($data->edited_status == '0') {
                        $status = '<span class="label label-warning">Verification Pending</span>';
                    } else if ($data->edited_status == '1') {
                        $status = '<span class="label label-info">Approval Pending</span>';
                    }
                    return $status;
                })

                ->rawColumns(['beneficiary_id', 'name', 'block_ulb_name', 'ss_card_no', 'mobile_no', 'type', 'status'])

                ->make(true);
        }
    }
    public function ajaxViewPassbook(Request $request)
    {
        $scheme_id = $this->scheme_id;

        $roleArray = $request->session()->get('role');
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
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled');
        }
        //dd($request->toArray());
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
            return redirect("/")->with('error', $return_text);
        }
        if (!in_array($is_profile_pic, array(0, 1))) {
            $return_text = 'Parameter Not Valid2';
            return redirect("/")->with('error', $return_text);
        }
        if (empty($application_id)) {
            $return_text = 'Parameter Not Valid3';
            return redirect("/")->with('error', $return_text);
        }
        $user_id = Auth::user()->id;

        $encolserData = $DraftEncloserTable->where('document_type', $request->doc_type)->where('application_id', $request->application_id)->first();
        if (empty($encolserData->application_id)) {
            $return_text = 'Parameter Not Valid5';
            return redirect("/")->with('error', $return_text);
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
            return redirect("/")->with('error', 'Some error.please try again ......');
        }
    }
    public function getBankFailedexcel(Request $request)
    {


        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $contact_table_faulty = $getModelFunc->getTableFaulty('', '', 3);
        $contact_table_main = $getModelFunc->getTable('', '', 3);
        $modelMain = new DataSourceCommon;
        $modelMain->setConnection('pgsql_appwrite');
        $modelMain->setTable('' . $contact_table_main);
        $modelFaulty = new DataSourceCommon;
        $modelFaulty->setConnection('pgsql_appwrite');
        $modelFaulty->setTable('' . $contact_table_faulty);
        $modelfailedpayments = new DataSourceCommon;
        $modelfailedpayments->setConnection('pgsql_payment');
        $modelfailedpayments->setTable('lb_main.failed_payment_details');
        if ((Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') || ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier')) {
            $failed_ben_query = $modelfailedpayments->select('ben_id');
            if (!empty($request->failed_type)) {
                $failed_ben_query = $failed_ben_query->where('failed_type', $request->failed_type);
            }
        }
        if ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier') {
            $failed_ben_query = $failed_ben_query->where('local_body_code', $dutyObj->urban_body_code);
        } elseif (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
            $failed_ben_query = $failed_ben_query->where('dist_code', $dutyObj->district_code);
        }
        if ((Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') || ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier')) {
            $failed_array = $failed_ben_query->get('ben_id')->toArray();
            $in_condition = '';
            $contact_search = 1;
            $failed_ben_ids = array();
            if (count($failed_array) == 0) {
                $contact_search = 0;
            } else {
                foreach ($failed_array as $failed) {
                    array_push($failed_ben_ids, $failed['ben_id']);
                }
                // dd($failed_ben_ids);
                $in_condition = ' and beneficiary_id IN (' . implode(',', $failed_ben_ids) . ')';
                $contact_search = 1;
            }
        } else {
            $contact_search = 0;
        }

        //dd($failed_ben_ids);
        if ($dutyLevel == 'BlockVerifier' || $dutyLevel == 'BlockDelegated Verifier') {
            $distCode = $dutyObj->district_code;
            // $query = "select f.lot_month,TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.id,b.ben_id as beneficiary_id,b.ben_name,b.last_accno,b.last_ifsc,mb.block_name block_ulb_name,gp.gram_panchyat_name as gp_ward_name,f.pmt_mode,f.failed_type,f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id
            //         from lb_main.failed_payment_details f 
            //         JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
            //         JOIN public.m_block mb ON mb.block_code=b.local_body_code
            //         JOIN public.m_gp gp ON gp.gram_panchyat_code=b.gp_ward_code
            //         WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status=0 and b.ben_status=1 and failed_type in(1,2,3) ";
            $query = "select distinct TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id as beneficiary_id, b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name as block_ulb_name, gw.gp_ward_name as gp_ward_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id ,f.edited_status
                from lb_main.failed_payment_details f 
                JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                LEFT JOIN 
                    (
                    select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                    union all
                    select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                    ) bu ON bu.block_ulb_code=b.block_ulb_code 
                LEFT JOIN 
                    (
                    select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                    union all
                    select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                    ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->taluka_code . " and f.edited_status=0 and b.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false ";
            if (!empty($request->filter_1)) {
                $query .= " AND b.gp_ward_code=" . $request->filter_1 . "";
            }
            if (!empty($request->failed_type)) {
                $query .= " AND f.failed_type=" . $request->failed_type . "";
            }
            // if (!empty($request->pay_mode)) {
            //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
            // }
            $query .= "  order by b.ben_id";
            // echo $query;die;
        } elseif ($dutyLevel == 'SubdivVerifier' || $dutyLevel == 'SubdivDelegated Verifier') {
            $distCode = $dutyObj->district_code;
            // $query = "select f.lot_month,TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.id,b.ben_id as beneficiary_id,b.ben_name,b.last_accno,b.last_ifsc,ub.urban_body_name block_ulb_name,wa.urban_body_ward_name as gp_ward_name,f.pmt_mode,f.failed_type,f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id
            //         from lb_main.failed_payment_details f 
            //         JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
            //         JOIN public.m_urban_body ub ON ub.urban_body_code=b.block_ulb_code
            //         JOIN public.m_urban_body_ward wa ON wa.urban_body_ward_code=b.gp_ward_code
            //         WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1 and failed_type in(1,2,3) ";
            $query = "select distinct TO_CHAR(f.created_at::date, 'Month') AS validation_month,f.lot_month,f.id,b.ben_id as beneficiary_id, b.ben_name,b.last_accno,b.last_ifsc,bu.block_ulb_name as block_ulb_name, gw.gp_ward_name as gp_ward_name, f.pmt_mode,f.failed_type, f.remarks,f.status_code,b.ss_card_no,b.mobile_no,b.application_id ,f.edited_status
                from lb_main.failed_payment_details f 
                JOIN " . $schemaname . ".ben_payment_details b ON f.ben_id=b.ben_id
                LEFT JOIN 
                    (
                    select urban_body_code as block_ulb_code,urban_body_name as block_ulb_name from public.m_urban_body ub 
                    union all
                    select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block mb
                    ) bu ON bu.block_ulb_code=b.block_ulb_code 
                LEFT JOIN 
                    (
                    select gram_panchyat_code as gp_ward_code, gram_panchyat_name as gp_ward_name from public.m_gp 
                    union all
                    select urban_body_ward_code as gp_ward_code, urban_body_ward_name as gp_ward_name from public.m_urban_body_ward
                    ) gw ON gw.gp_ward_code=b.gp_ward_code 
                    WHERE f.local_body_code=" . $dutyObj->urban_body_code . " and f.edited_status=0 and b.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false ";
            if (!empty($request->filter_1) && empty($request->filter_2)) {
                $query .= " AND b.block_ulb_code=" . $request->filter_1 . " ";
            }
            if (!empty($request->filter_1) && !empty($request->filter_2)) {
                $query .= " AND b.block_ulb_code=" . $request->filter_1 . " AND b.gp_ward_code=" . $request->filter_2 . "";
            }
            if (!empty($request->failed_type)) {
                $query .= " AND f.failed_type=" . $request->failed_type . "";
            }
            // if (!empty($request->pay_mode)) {
            //     $query .= " AND f.pmt_mode=" . $request->pay_mode . "";
            // }
            $query .= "  order by b.ben_id ";
            $query_contact = "select house_premise_no,application_id from " . $contact_table_main . " 
                where created_by_local_body_code=" . $dutyObj->urban_body_code . " and 
                created_by_dist_code=" . $distCode . " " . $in_condition . "
                UNION
                select house_premise_no,application_id from " . $contact_table_faulty . "  where 
                created_by_local_body_code=" . $dutyObj->urban_body_code . "  
                and created_by_dist_code=" . $distCode . " " . $in_condition;
        }
        if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
            $distCode = $dutyObj->district_code;
            $rural_urban = $request->filter_1;
            $local_body_code = $request->filter_2;
            if ($rural_urban == 1) {
                $query = '';

                $query .= "select fp.lot_month,TO_CHAR(fp.created_at::date, 'Month') AS validation_month,fp.id,fp.pmt_mode,fp.failed_type,fp.remarks,fp.status_code,bp.ben_name as ben_name,bp.ben_id as beneficiary_id, ms.sub_district_name as block_subdiv_name,
                bp.ss_card_no, bp.mobile_no,bp.last_accno,bp.last_ifsc,bp.application_id
                from lb_main.failed_payment_details fp 
                JOIN " . $schemaname . ".ben_payment_details bp ON bp.ben_id=fp.ben_id 
                JOIN public.m_sub_district ms ON ms.sub_district_code=bp.local_body_code 
                where fp.dist_code=" . $distCode . " and fp.edited_status in(0,1) and bp.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false ";
                if (!empty($rural_urban)) {
                    $query .= " and bp.rural_urban_id=" . $rural_urban . " ";
                }
                if (!empty($local_body_code)) {
                    $query .= " and bp.local_body_code=" . $local_body_code . " ";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND fp.failed_type=" . $request->failed_type . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND fp.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= " order by bp.ben_id ";
                // and pmt_mode=1 and failed_type=1

            } elseif ($rural_urban == 2) {
                $query = '';

                $query = "select fp.lot_month,TO_CHAR(fp.created_at::date, 'Month') AS validation_month,fp.id,fp.pmt_mode,fp.failed_type,fp.remarks,fp.status_code,bp.ben_name as ben_name,bp.ben_id as beneficiary_id, mb.block_name as block_subdiv_name,
                bp.ss_card_no, bp.mobile_no, bp.mobile_no,bp.last_accno,bp.last_ifsc,bp.application_id
                from lb_main.failed_payment_details fp 
                JOIN " . $schemaname . ".ben_payment_details bp ON bp.ben_id=fp.ben_id 
                JOIN public.m_block mb ON mb.block_code=bp.local_body_code
                where fp.dist_code=" . $distCode . " and fp.edited_status in(0,1) and bp.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false ";
                if (!empty($rural_urban)) {
                    $query .= " and bp.rural_urban_id=" . $rural_urban . " ";
                }
                if (!empty($local_body_code)) {
                    $query .= " and bp.local_body_code=" . $local_body_code . " ";
                }
                if (!empty($request->failed_type)) {
                    $query .= " AND fp.failed_type=" . $request->failed_type . "";
                }
                // if (!empty($request->pay_mode)) {
                //     $query .= " AND fp.pmt_mode=" . $request->pay_mode . "";
                // }
                $query .= " order by bp.ben_id ";

                // and pmt_mode=1 and failed_type=1
                // echo $query;die;
            } else {
                $query = '';

                $query = "select fp.lot_month,TO_CHAR(fp.created_at::date, 'Month') AS validation_month ,fp.id,fp.pmt_mode,fp.failed_type,fp.remarks,fp.status_code,bp.ben_name as ben_name,bp.ben_id as beneficiary_id, bl_ulb.block_ulb_name as block_subdiv_name,
                bp.ss_card_no, bp.mobile_no, bp.mobile_no,bp.last_accno,bp.last_ifsc,bp.application_id
                from lb_main.failed_payment_details fp 
                JOIN " . $schemaname . ".ben_payment_details bp ON bp.ben_id=fp.ben_id 
                JOIN (select block_code as block_ulb_code,block_name as block_ulb_name from public.m_block UNION ALL
                     select sub_district_code as block_ulb_code, sub_district_name as block_ulb_name from public.m_sub_district
                     ) bl_ulb ON bl_ulb.block_ulb_code=bp.local_body_code
                where fp.dist_code=" . $distCode . " and fp.edited_status in(0,1) and bp.ben_status=1 and failed_type in(1,2,3,4)  AND legacy_validation_failed = false ";
                if (!empty($request->failed_type)) {
                    $query .= " AND fp.failed_type=" . $request->failed_type . "";
                }
                $query .= " order by bp.ben_id";
                //  and pmt_mode=1 and failed_type=1
            }
            $query_contact = "select house_premise_no,application_id from " . $contact_table_main . " 
                where created_by_dist_code=" . $distCode . " " . $in_condition . "
                UNION
                select house_premise_no,application_id from " . $contact_table_faulty . " 
                where created_by_dist_code=" . $distCode . " " . $in_condition;
        }
        if (Auth::user()->designation_id === 'Verifier' || Auth::user()->designation_id === 'Delegated Verifier') {
            $getFailedData = DB::connection('pgsql_payment')->select($query);
            if ($contact_search == 1 && trim($dutyObj->mapping_level) == 'Subdiv') {
                $getContactData = DB::connection('pgsql_appwrite')->select($query_contact);
            }
            if (trim($dutyObj->mapping_level) == 'Subdiv') {
                $ben[] = array(
                    'Application ID',
                    'Beneficiary Id',
                    'Beneficiary Name',
                    'Block/ Municipality Name',
                    'GP/Ward Name',
                    'House/Premise No.',
                    'Swasthya Sathi Card No',
                    'Mobile No',
                    'Account No',
                    'IFSC Code',
                    'Reason',
                    'Faliure Type',
                    'Faliure Month'
                );
            } else {
                $ben[] = array(
                    'Application ID',
                    'Beneficiary Id',
                    'Beneficiary Name',
                    'Block/ Municipality Name',
                    'GP/Ward Name',
                    'Swasthya Sathi Card No',
                    'Mobile No',
                    'Account No',
                    'IFSC Code',
                    'Reason',
                    'Faliure Type',
                    'Faliure Month'
                );
            }
            if (count($getFailedData) > 0) {
                foreach ($getFailedData as $arr) {
                    if ($contact_search == 1 && trim($dutyObj->mapping_level) == 'Subdiv') {
                        $house_premise_no = $this->getContactDetails($arr->application_id, 1, $getContactData);
                    } else {
                        $house_premise_no = '';
                    }
                    if ($arr->pmt_mode == 1) { // Bandhan Bank
                        $failed_reason = ($arr->status_code == 'NA') ? $arr->remarks : Config::get('bandhancode.bandhan_response_code.' . trim($arr->status_code));
                    } else if ($arr->pmt_mode == 2) { // SBI
                        $failed_reason = Config::get('bandhancode.sbi_response_code.' . trim($arr->status_code));
                    } else {
                        $failed_reason = '';
                    }
                    $faliure_type = Config::get('globalconstants.failed_type.' . $arr->failed_type);
                    if ($arr->failed_type == '2') {
                        $faliure_month = Config::get('constants.monthval.' . $arr->lot_month);
                    } else {
                        $faliure_month = $arr->validation_month;
                    }
                    if (trim($dutyObj->mapping_level) == 'Subdiv') {
                        $ben[] = array(
                            'Application Id' => trim($arr->application_id),
                            'Beneficiary Id' => trim($arr->beneficiary_id),
                            'Beneficiary Name' => trim($arr->ben_name),
                            'Block/ Municipality Name' => trim($arr->block_ulb_name),
                            'GP/Ward Name' => trim($arr->gp_ward_name),
                            'House/Premise No.' => trim($house_premise_no),
                            'Swasthya Sathi Card No' => trim($arr->ss_card_no),
                            'Mobile No' => trim($arr->mobile_no),
                            'Account No' => trim($arr->last_accno),
                            'IFSC Code' => trim($arr->last_ifsc),
                            'Reason' => $failed_reason,
                            'Faliure Type' => $faliure_type,
                            'Faliure Month' => $faliure_month,
                        );
                    } else {
                        $ben[] = array(
                            'Application Id' => trim($arr->application_id),
                            'Beneficiary Id' => trim($arr->beneficiary_id),
                            'Beneficiary Name' => trim($arr->ben_name),
                            'Block/ Municipality Name' => trim($arr->block_ulb_name),
                            'GP/Ward Name' => trim($arr->gp_ward_name),
                            'Swasthya Sathi Card No' => trim($arr->ss_card_no),
                            'Mobile No' => trim($arr->mobile_no),
                            'Account No' => trim($arr->last_accno),
                            'IFSC Code' => trim($arr->last_ifsc),
                            'Reason' => $failed_reason,
                            'Faliure Type' => $faliure_type,
                            'Faliure Month' => $faliure_month,
                        );
                    }
                }
            }
        } else {
            $getFailedData = DB::connection('pgsql_payment')->select($query);
            if ($contact_search == 1) {
                $getContactData = DB::connection('pgsql_appwrite')->select($query_contact);
            }
            $ben[] = array(
                'Application ID',
                'Beneficiary Id',
                'Beneficiary Name',
                'Block/ Subdivision Name',
                'House/Premise No.',
                'Swasthya Sathi Card No',
                'Mobile No',
                'Account No',
                'IFSC Code',
                'Reason',
                'Faliure Type',
                'Faliure Month'
            );
            if (count($getFailedData) > 0) {
                foreach ($getFailedData as $arr) {
                    if ($contact_search == 1) {
                        $house_premise_no = $this->getContactDetails($arr->application_id, 1, $getContactData);
                    }
                    if ($arr->pmt_mode == 1) { // Bandhan Bank
                        $failed_reason = ($arr->status_code == 'NA') ? $arr->remarks : Config::get('bandhancode.bandhan_response_code.' . trim($arr->status_code));
                    } else if ($arr->pmt_mode == 2) { //SBI
                        $failed_reason = Config::get('bandhancode.sbi_response_code.' . trim($arr->status_code));
                    } else {
                        $failed_reason = '';
                    }
                    $faliure_type = Config::get('globalconstants.failed_type.' . $arr->failed_type);
                    if ($arr->failed_type == '2') {
                        $faliure_month = Config::get('constants.monthval.' . $arr->lot_month);
                    } else {
                        $faliure_month = $arr->validation_month;
                    }

                    $ben[] = array(
                        'Application Id' => trim($arr->application_id),
                        'Beneficiary Id' => trim($arr->beneficiary_id),
                        'Beneficiary Name' => trim($arr->ben_name),
                        'Block/ Subdivision Name' => trim($arr->block_subdiv_name),
                        'House/Premise No.' => trim($house_premise_no),
                        'Swasthya Sathi Card No' => trim($arr->ss_card_no),
                        'Mobile No' => trim($arr->mobile_no),
                        'Account No' => trim($arr->last_accno),
                        'IFSC Code' => trim($arr->last_ifsc),

                        'Reason' => $failed_reason,
                        'Faliure Type' => $faliure_type,
                        'Faliure Month' => $faliure_month,
                    );
                }
            }
        }

        $file_name = 'Beneficiary Failed Data' . date('d/m/Y');
        Excel::create($file_name, function ($excel) use ($ben) {
            $excel->setTitle('Beneficiary Failed Data');
            $excel->sheet('Beneficiary Failed Data', function ($sheet) use ($ben) {
                $sheet->fromArray($ben, null, 'A1', false, false);
            });
        })->download('xlsx');
    }
    public function getContactDetails($application_id, $is_faulty, $contactList)
    {
        $return_data = '';
        foreach ($contactList as $arr) {
            if ($arr->application_id == $application_id) {
                $return_data = $arr->house_premise_no;
                break;
            }
        }
        return $return_data;
    }
}
