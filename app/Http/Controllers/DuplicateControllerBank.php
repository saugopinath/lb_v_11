<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\District;
use App\Models\Scheme;
use Redirect;
// use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use DateTime;
use Config;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use App\Models\RejectRevertReason;
use App\Models\AadharDuplicateTrail;
use App\Models\SubDistrict;
use App\Models\Taluka;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\SchemeDocMap;
use App\Models\BankDetails;
use Carbon\Carbon;
use App\Models\Configduty;
use App\Models\UrbanBody;
use App\Models\Ward;
use App\Models\GP;
use App\Helpers\DupCheck;
class DuplicateControllerBank extends Controller
{

    public function __construct()
    {

        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
        $this->ben_status = -97;
    }
    function dedupBankCron(Request $request)
    {
        $logmessage = "";
        $fileLocation = 'DuplicateBank/log.txt';
        try {

            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $logmessage .=  "Duplicate Bank Controller Cron has been started on " . date("Y-m-d h:i:s") . "." . "\n";
            $query = "insert into lb_main.ben_payment_details_bank_code_dup(dist_code, ben_id, apr_lot_no, apr_lot_type, apr_lot_status, may_lot_no, 
            may_lot_type, may_lot_status, jun_lot_no, jun_lot_type, jun_lot_status, jul_lot_no, 
            jul_lot_type, jul_lot_status, aug_lot_no, aug_lot_type, aug_lot_status, sep_lot_no, 
            sep_lot_type, sep_lot_status, oct_lot_no, oct_lot_type, oct_lot_status, nov_lot_no, 
            nov_lot_type, nov_lot_status, dec_lot_no, dec_lot_type, dec_lot_status, jan_lot_no, 
            jan_lot_type, jan_lot_status, feb_lot_no, feb_lot_type, feb_lot_status, mar_lot_no, 
            mar_lot_type, mar_lot_status, start_yymm, fin_year, openning_due_amt, openning_due_count, 
            present_amt, present_count, last_accno,  ben_status, ben_name, updated_at, 
            caste, created_at, acc_validated, local_body_code, rural_urban_id, block_ulb_code, 
            gp_ward_code, ss_card_no, mobile_no, application_id, end_yymm, faulty_status, 
            faulty_to_main_date,m_date)
         select bp.dist_code, bp.ben_id, bt.apr_lot_no, bt.apr_lot_type, bt.apr_lot_status, bt.may_lot_no, 
         bt.may_lot_type, bt.may_lot_status, bt.jun_lot_no, bt.jun_lot_type, bt.jun_lot_status, bt.jul_lot_no, 
         bt.jul_lot_type, bt.jul_lot_status, bt.aug_lot_no, bt.aug_lot_type, bt.aug_lot_status, bt.sep_lot_no, 
         bt.sep_lot_type, bt.sep_lot_status, bt.oct_lot_no, bt.oct_lot_type, bt.oct_lot_status, bt.nov_lot_no, 
         bt.nov_lot_type, bt.nov_lot_status, bt.dec_lot_no, bt.dec_lot_type, bt.dec_lot_status, bt.jan_lot_no, 
         bt.jan_lot_type, bt.jan_lot_status, bt.feb_lot_no, bt.feb_lot_type, bt.feb_lot_status, bt.mar_lot_no, 
         bt.mar_lot_type, bt.mar_lot_status, bp.start_yymm, bt.fin_year, bp.openning_due_amt, bp.openning_due_count, 
         bt.present_amt, bt.present_count, bp.last_accno,  bp.ben_status, bp.ben_name, bp.updated_at, 
         bp.caste, bp.created_at, bp.acc_validated, bp.local_body_code, bp.rural_urban_id, bp.block_ulb_code, 
         bp.gp_ward_code, bp.ss_card_no, bp.mobile_no, bp.application_id, bp.end_yymm, bp.faulty_status, 
         bp.faulty_to_main_date,  '" . date("Y-m-d h:i:s") . "' from " . $schemaname . ".ben_payment_details bp JOIN " . $schemaname . ".ben_transaction_details bt ON bp.ben_id = bt.ben_id where bt.fin_year = '2025-2026' bp.ben_id 
        IN
        (
        select ben_id  from " . $schemaname . ".ben_payment_details  where ben_status=" . $this->ben_status . "
        except
        select ben_id from lb_main.ben_payment_details_bank_code_dup where ben_status=" . $this->ben_status . "
        ) on conflict(application_id) do nothing";
            DB::connection('pgsql_payment')->statement($query);
            $logmessage .=  " Duplicate Bank Controller Cron has been completed on " . date("Y-m-d h:i:s") . "." . "\n";
            Storage::append($fileLocation, $logmessage);
            //Storage::put($fileLocation);
        } catch (\Exception $e) {
            // dd($e);
            $logmessage .= " Exception:- " . $e->getMessage() . " on " . date("Y-m-d h:i:s") . "." . "\n";
            Storage::append($fileLocation, $logmessage);
            //Storage::put($fileLocation);
        }
    }

    function dedupBankListView(Request $request)
    {

        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $errormsg = Config::get('constants.errormsg');
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $verifier_condition = ' and p.local_body_code=' . $urban_body_code;
        } else {
            $verifier_condition = '';
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }

        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select A.last_accno,A.cnt
            from
            (
            select last_accno,count(1) as cnt
            from lb_main.ben_payment_details_bank_code_dup where ben_status=" . $this->ben_status . " 
            group  by last_accno 
            ) as A WHERE EXISTS
                (SELECT 1
                 FROM lb_main.ben_payment_details_bank_code_dup p
                 WHERE p.ben_status=" . $this->ben_status . "  and p.last_accno = A.last_accno
                   AND p.dist_code=" . $district_code . " $verifier_condition) order by cnt desc";
        // dd($query);
        $rows = DB::connection('pgsql_payment')->select($query);
        $errormsg = Config::get('constants.errormsg');
        // dd($errormsg);
        return view(
            'DuplicateBank.duplicateBankListView',
            [
                'district_code' => $district_code,
                'data' => $rows,
                'scheme_id' => $this->scheme_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
            ]
        );
    }
    public function dedupBankView(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $designation_id = Auth::user()->designation_id;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $verifier_condition = ' and p.local_body_code=' . $urban_body_code;
        } else {
            $verifier_condition = '';
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }

        if (empty($request->last_accno)) {
            return redirect("/dedupBankListView")->with('error', 'Account No.not found');
        }
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        $block = Taluka::get();
        $UrbanBody = UrbanBody::get();
        $Ward = Ward::get();
        $GP = GP::get();
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select A.dist_code,A.local_body_code,A.block_ulb_code,A.gp_ward_code,A.rural_urban_id,A.ben_id,A.application_id,A.ben_name,A.mobile_no,A.ss_card_no,A.faulty_status
                 FROM lb_main.ben_payment_details_bank_code_dup A
                 WHERE ben_status=" . $this->ben_status . "  and trim(A.last_accno) = '" . $request->last_accno . "' order by ben_name";

        $rows = DB::connection('pgsql_payment')->select($query);
        //  dd($rows);
        $ben_list = array();
        $i = 0;
        foreach ($rows as $arr) {
            $allowed = 0;
            $ben_list[$i]['dist_code'] = $arr->dist_code;
            $ben_list[$i]['local_body_code'] = $arr->local_body_code;
            $ben_list[$i]['application_id'] = $arr->application_id;
            $ben_list[$i]['ben_id'] = $arr->ben_id;
            $ben_list[$i]['ben_name'] = $arr->ben_name;
            $ben_list[$i]['mobile_no'] = $arr->mobile_no;
            $ben_list[$i]['ss_card_no'] = $arr->ss_card_no;
            $ben_list[$i]['faulty_status'] = intval($arr->faulty_status);
            $local_body = '';
            if ($arr->rural_urban_id == 1) {
                $local_body = $UrbanBody->where('urban_body_code', $arr->block_ulb_code)->first();
                $gp_ward = $Ward->where('urban_body_ward_code', $arr->gp_ward_code)->first();
                $ben_list[$i]['local_body_name'] =   trim($local_body->urban_body_name);
                $ben_list[$i]['gp_ward_name'] =   trim($gp_ward->urban_body_ward_name);
            } else {
                $local_body = $block->where('block_code', $arr->block_ulb_code)->first();
                $gp_ward = $GP->where('gram_panchyat_code', $arr->gp_ward_code)->first();
                $ben_list[$i]['local_body_name'] =   trim($local_body->block_name);
                $ben_list[$i]['gp_ward_name'] =   trim($gp_ward->gram_panchyat_name);
            }
            if ($designation_id = 'Approver' ||  $designation_id == 'Delegated Approver') {
                if ($arr->dist_code == $district_code) {
                    $allowed = 1;
                } else {
                    $allowed = 0;
                }
            } else if ($designation_id = 'Verifier' || $designation_id == 'Delegated Verifier') {
                if ($arr->dist_code == $district_code && $arr->local_body_code == $urban_body_code) {
                    $allowed = 1;
                } else {
                    $allowed = 0;
                }
            }
            $ben_list[$i]['allowed'] = $allowed;
            $i++;
        }

        $errormsg = Config::get('constants.errormsg');
        return view(
            'DuplicateBank.dedupBankView',
            [
                'reject_revert_reason' => $reject_revert_reason,
                'district_code' => $district_code,
                'data' => $ben_list,
                'bank_code' => $request->last_accno,
                'scheme_id' => $this->scheme_id,
                'designation_id' => $designation_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
            ]
        );
    }
    public function dupBankReject(Request $request)
    {
        // dd($request->all());
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $errormsg = Config::get('constants.errormsg');
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $verifier_condition = ' and p.local_body_code=' . $urban_body_code;
        } else {
            $verifier_condition = '';
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $bank_code = trim($request->bank_code);
        $application_id = $request->application_id;
        $is_bulk = $request->is_bulk;
        $applicant_id_post = request()->input('applicantId');
        $comments = $request->comments;
        $rejected_cause = $request->reject_cause;
        //  dd($comments);
        if (empty($comments))
            $comments = NULL;

        if (empty($bank_code)) {
            return redirect("/dedupBankListView")->with('error', 'Bank Account No.Not Found');
        }
        if (empty($rejected_cause)) {
            return redirect("/dedupBankListView")->with('error', 'Rejected Cause Not Valid');
        }
        if (!ctype_digit($rejected_cause)) {
            return redirect("/dedupBankListView")->with('error', 'Rejected Cause Not Valid');
        }
        $r_count = RejectRevertReason::where('id', $rejected_cause)->count();
        if ($r_count == 0) {
            return redirect("/dedupBankListView")->with('error', 'Rejected Cause Not Valid');
        }
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 4);
        $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $Table);
        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaulty);
        //  dd($personal_model_f);
        $accept_reject_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
        $accept_reject_model->setTable('' . $Table);
        // dd($accept_reject_model);
        $today = date("Y-m-d h:i:s");
        if ($is_bulk == 0) {
            // dd($application_id);
            if (empty($application_id)) {
                return redirect("/dedupBankListView")->with('error', ' Application Id Not Found');
            }
            $application_id_arr = explode('_', $application_id);
            $app_id = $application_id_arr[0];
            $is_faulty = $application_id_arr[1];
            if (!ctype_digit($app_id)) {
                return redirect("/dedupBankListView")->with('error', ' Application Id Not Valid');
            }
            // dd($is_faulty);
            if ($is_faulty == 1) {
                $row_count = $personal_model_f->whereraw("trim(bank_code)='$bank_code'")->where('application_id', $app_id)->where('created_by_dist_code', $district_code)->count();
            } else if ($is_faulty == 0) {
                $row_count = $personal_model->whereraw("trim(bank_code)='$bank_code'")->where('application_id', $app_id)->where('created_by_dist_code', $district_code)->count();
            }
            // dd($row_count);
            if ($row_count == 0) {
                return redirect("/dedupBankListView")->with('error', ' Application Id Not found in Db!');
            }
            try {

                DB::beginTransaction();
                DB::connection('pgsql_payment')->beginTransaction();
                // $in_pension_id = 'ARRAY[' . "'$app_id'" . ']';
                // dd($in_pension_id);
                $update_arr = array();
                $update_arr['ben_status'] = -98;
                $update_arr['rejected_date'] =  $today;
                $update_arr['is_approved'] = 0;
                $update_arr['rejected_cause'] = $rejected_cause;
                $update_arr['comments'] = $comments;
                // dd($update_arr);
                $is_saved2 = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')->whereraw("trim(last_accno)='$bank_code'")->where('application_id', $app_id)->where('dist_code', $district_code)->update($update_arr);
                if ($is_saved2) {
                    $accept_reject_model->op_type = 'DupBankReject';
                    $accept_reject_model->application_id = $app_id;
                    $accept_reject_model->designation_id = $designation_id;
                    $accept_reject_model->scheme_id = $scheme_id;
                    $accept_reject_model->user_id = $user_id;
                    $accept_reject_model->comment_message = $comments;
                    $accept_reject_model->mapping_level = $mapping_level;
                    $accept_reject_model->created_by = $user_id;
                    $accept_reject_model->created_by_level = $mapping_level;
                    $accept_reject_model->created_by_dist_code = $district_code;
                    $accept_reject_model->rejected_reverted_cause = $rejected_cause;
                    $accept_reject_model->ip_address = request()->ip();
                    $is_saved3 = $accept_reject_model->save();
                    if ($is_saved3) {
                        DB::commit();
                        DB::connection('pgsql_payment')->commit();
                        return redirect("/dedupBankView?last_accno=" . $bank_code)->with('success', 'Application with Id (' . $app_id . ') has been successfully forward to Approver');
                    } else {
                        DB::rollback();
                        DB::connection('pgsql_payment')->rollback();
                        return redirect("/dedupBankView?last_accno=" . $bank_code)->with('error', $errormsg['roolback']);
                    }
                } else {
                    DB::rollback();
                    DB::connection('pgsql_payment')->rollback();
                    return redirect("/dedupBankView?last_accno=" . $bank_code)->with('error', $errormsg['roolback']);
                }
            } catch (\Exception $e) {
                DB::rollback();
                DB::connection('pgsql_payment')->rollback();
                //dd($e);
                return redirect("/dedupBankView?last_accno=" . $bank_code)->with('error', $errormsg['roolback']);
            }
        } else if ($is_bulk == 1) {
            $applicant_id_post = request()->input('applicantId');
            $applicant_id_in = explode(',', $applicant_id_post);
            //  dd($applicant_id_in);
            $arry_list = array();
            $i = 0;
            $faulty_arr = array();
            $main_arr = array();
            $all_arr = array();
            foreach ($applicant_id_in as $app) {
                $application_id_arr = explode('_', $app);
                $app_id = $application_id_arr[0];
                $is_faulty = $application_id_arr[1];
                array_push($all_arr, $app_id);
                if (!ctype_digit($app_id)) {
                    return redirect("/dedupBankListView")->with('error', ' Application Id Not Valid');
                }
                if ($is_faulty == 1) {
                    array_push($faulty_arr, $app_id);
                    $row_count = $personal_model_f->whereraw("trim(bank_code)='$bank_code'")->where('application_id', $app_id)->where('created_by_dist_code', $district_code)->count();
                } else if ($is_faulty == 0) {
                    array_push($main_arr, $app_id);
                    $row_count = $personal_model->whereraw("trim(bank_code)='$bank_code'")->where('application_id', $app_id)->where('created_by_dist_code', $district_code)->count();
                }
                //  dd($row_count);
                if ($row_count == 0) {
                    return redirect("/dedupBankListView")->with('error', ' Application Id Not found in Db. ' . $app_id . $is_faulty);
                }

                $i++;
            }
            // $input = ['next_level_role_id' => -100, 'rejected_cause' => $rejected_cause, 'comments' => $comments];
            try {
                DB::beginTransaction();
                DB::connection('pgsql_payment')->beginTransaction();
                if (count($faulty_arr) > 0) {
                    $update_arr = array();
                    $update_arr['ben_status'] = -98;
                    $update_arr['rejected_date'] =  $today;
                    $update_arr['is_approved'] =  0;
                    // $is_saved1 = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->whereraw("trim(last_accno)='$bank_code'")->whereIn('application_id', $faulty_arr)->where('dist_code', $district_code)->where('faulty_status', TRUE)->update($update_arr);
                    $is_saved2 = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')->whereraw("trim(last_accno)='$bank_code'")->whereIn('application_id', $faulty_arr)->where('dist_code', $district_code)->where('faulty_status', TRUE)->update($update_arr);
                    // $implode_application_arr = implode("','", $faulty_arr);
                    // $in_pension_id = 'ARRAY[' . "'$implode_application_arr'" . ']';
                    //$is_status_updated = $personal_model_f->whereIn('application_id', $faulty_arr)->update($input);
                    // $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_faulty_final_dup_bank(" . $in_pension_id . "," . $rejected_cause . ",'" . $comments . "')");
                }
                if (count($main_arr) > 0) {
                    $update_arr = array();
                    $update_arr['ben_status'] = -98;
                    $update_arr['rejected_date'] =  $today;
                    $update_arr['is_approved'] =  0;

                    // $is_saved1 = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->whereraw("trim(last_accno)='$bank_code'")->whereIn('application_id', $main_arr)->where('dist_code', $district_code)->where('faulty_status', FALSE)->update($update_arr);
                    $is_saved2 = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')->whereraw("trim(last_accno)='$bank_code'")->whereIn('application_id', $main_arr)->where('dist_code', $district_code)->where('faulty_status', FALSE)->update($update_arr);
                    //$is_status_updated = $personal_model_f->whereIn('application_id', $main_arr)->update($input);
                    // $implode_application_arr = implode("','", $main_arr);
                    // $in_pension_id = 'ARRAY[' . "'$implode_application_arr'" . ']';
                    // $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_dup_bank(" . $in_pension_id . "," . $rejected_cause . ",'" . $comments . "')");
                }
                foreach ($all_arr as $app_row) {
                    $accept_reject_model = new DataSourceCommon;
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                    $accept_reject_model->setTable('' . $Table);
                    $accept_reject_model->op_type = 'DupBankReject';
                    $accept_reject_model->application_id = $app_row;
                    $accept_reject_model->designation_id = $designation_id;
                    $accept_reject_model->scheme_id = $scheme_id;
                    $accept_reject_model->user_id = $user_id;
                    $accept_reject_model->comment_message = $comments;
                    $accept_reject_model->mapping_level = $mapping_level;
                    $accept_reject_model->created_by = $user_id;
                    $accept_reject_model->created_by_level = $mapping_level;
                    $accept_reject_model->created_by_dist_code = $district_code;
                    $accept_reject_model->rejected_reverted_cause = $rejected_cause;
                    $accept_reject_model->ip_address = request()->ip();
                    $is_saved = $accept_reject_model->save();
                }
                //$is_saved3 = $accept_reject_model::create('', $all_arr);
                DB::commit();
                DB::connection('pgsql_payment')->commit();
                return redirect("/dedupBankView?last_accno=" . $bank_code)->with('success', 'Applications  has been successfully forward to Approver');
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                DB::connection('pgsql_payment')->rollback();
                return redirect("/dedupBankView?last_accno=" . $bank_code)->with('error', $errormsg['roolback']);
            }
        }
    }
    public function generate_excel_list(Request $request)
    {

        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $designation_id = Auth::user()->designation_id;
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
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $verifier_condition = ' and p.local_body_code=' . $urban_body_code;
        } else {
            $verifier_condition = '';
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $subdistrict = SubDistrict::get();
        $block = Taluka::get();
        $district = District::get();
        $munc_list = UrbanBody::get();
        $Ward = Ward::get();
        $GP = GP::get();
        $ben_list = array();
        $i = 0;
        $query = "select P.last_accno,Q.dist_code,Q.local_body_code,Q.block_ulb_code,Q.gp_ward_code,Q.rural_urban_id,Q.ben_id,Q.ben_status,Q.application_id,Q.ben_name,Q.mobile_no,Q.ss_card_no,Q.faulty_status from 
        (select A.last_accno,A.cnt
            from
            (
            select trim(last_accno) as last_accno,count(1) as cnt
            from lb_main.ben_payment_details_bank_code_dup 
            group  by trim(last_accno) 
            ) as A WHERE EXISTS
                (SELECT 1
                 FROM lb_main.ben_payment_details_bank_code_dup p
                 WHERE   trim(p.last_accno) = A.last_accno
                   AND p.dist_code=" . $district_code . " $verifier_condition) order by cnt desc
                   ) as P JOIN  lb_main.ben_payment_details_bank_code_dup Q 
                   ON   trim(P.last_accno)=trim(Q.last_accno)  order by Q.last_accno,Q.ben_name";
        //  dd($query);
        $rows = DB::connection('pgsql_payment')->select($query);
        // dd($rows);
        foreach ($rows as $arr) {
            $allowed = 0;
            $ben_list[$i]['rural_urban_id'] = $arr->rural_urban_id;
            $ben_list[$i]['gp_ward_code'] = $arr->gp_ward_code;
            $ben_list[$i]['bank_code'] = $arr->last_accno;
            $ben_list[$i]['local_body_code'] = $arr->local_body_code;
            $ben_list[$i]['application_id'] = $arr->application_id;
            $ben_list[$i]['ben_id'] = $arr->ben_id;
            $ben_list[$i]['ben_name'] = $arr->ben_name;
            $ben_list[$i]['mobile_no'] = $arr->mobile_no;
            $ben_list[$i]['ss_card_no'] = $arr->ss_card_no;
            $ben_list[$i]['faulty_status'] = intval($arr->faulty_status);
            $district_row = $district->where('district_code', $arr->dist_code)->first();
            $ben_list[$i]['district_name'] =  $district_row->district_name;
            $local_body = '';
            if (strlen($arr->local_body_code) == 5) {
                $local_body = $subdistrict->where('sub_district_code', $arr->local_body_code)->first();
                $ben_list[$i]['local_body_name'] =  'SubDivision-' . $local_body->sub_district_name;
            } else {
                $local_body = $block->where('block_code', $arr->local_body_code)->first();
                $ben_list[$i]['local_body_name'] =  'Block-' . $local_body->block_name;
            }
            if (!empty($arr->rural_urban_id)) {
                if ($arr->rural_urban_id == 1) {
                    if (!empty($arr->block_ulb_code)) {
                        $munc_row = $munc_list->where('urban_body_code', $arr->block_ulb_code)->first();
                        if (!empty($munc_row)) {
                            $ben_list[$i]['municipality_name'] =   trim($munc_row->urban_body_name);
                        } else {
                            $ben_list[$i]['municipality_name'] =  'NA';
                        }
                    } else {
                        $ben_list[$i]['municipality_name'] =  'NA';
                    }
                    if (!empty($arr->gp_ward_code)) {
                        $gp_ward = $Ward->where('urban_body_ward_code', $arr->gp_ward_code)->first();
                        $ben_list[$i]['gp_ward_name'] =   trim($gp_ward->urban_body_ward_name);
                    } else {
                        $ben_list[$i]['gp_ward_name'] =  '-';
                    }
                } else {

                    $ben_list[$i]['municipality_name'] =  'NA';
                    if (!empty($arr->gp_ward_code)) {
                        $gp_ward = $GP->where('gram_panchyat_code', $arr->gp_ward_code)->first();
                        $ben_list[$i]['gp_ward_name'] =   trim($gp_ward->gram_panchyat_name);
                    } else {
                        $ben_list[$i]['gp_ward_name'] =  '-';
                    }
                }
            } else {
                $ben_list[$i]['municipality_name'] =  '-';
                $ben_list[$i]['gp_ward_name'] =  '-';
            }
            if ($designation_id = 'Approver' ||  $designation_id == 'Delegated Approver') {
                if ($arr->dist_code == $district_code) {
                    $allowed = 1;
                } else {
                    $allowed = 0;
                }
            } else if ($designation_id = 'Verifier' || $designation_id == 'Delegated Verifier') {
                if ($arr->dist_code == $district_code && $arr->local_body_code == $urban_body_code) {
                    $allowed = 1;
                } else {
                    $allowed = 0;
                }
            }
            $ben_list[$i]['allowed'] = $allowed;
            if ($allowed == 1) {
                if ($arr->ben_status == -98) {
                    $ben_list[$i]['status_des'] = 'rejected';
                } else if ($arr->ben_status == 101) {
                    $ben_list[$i]['status_des'] = 'Bank Information has been updated with new one';
                } else if ($arr->ben_status == 200) {
                    $ben_list[$i]['status_des'] = 'Bank Information has been updated with old one';
                } else if ($arr->ben_status == -99) {
                    $ben_list[$i]['status_des'] = 'rejected';
                } else {
                    $ben_list[$i]['status_des'] = 'Need to modify';
                }
            } else {
                $ben_list[$i]['status_des'] = 'related to other.no action required.';
            }
            $i++;
        }

        $filename = "Bank_Account_Duplicate" .  "-" . date('d/m/Y') . ".xls";
        header("Content-Type: application/xls");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<table border="1">';
        echo '<tr><th>Bank Account No.</th><th>Applicant Id</th><th>Beneficiary Id</th><th>Beneficiary name</th><th>Mobile No.</th><th>District</th><th>Block/SubDivision</th><th>Municipality</th><th>GP/WARD</th><th>Swasthyasathi Card No.</th><th>Status</th></tr>';
        if (count($ben_list) > 0) {
            foreach ($ben_list as $row) {
                $sws_card_no = (string) $row['ss_card_no'];
                if (!empty($sws_card_no))
                    $ss_card_no = "'$sws_card_no'";
                else
                    $ss_card_no = $sws_card_no;

                $bank_code = (string) $row['bank_code'];
                if (!empty($bank_code))
                    $f_bank_code = "'$bank_code'";
                else
                    $f_bank_code = $bank_code;
                echo "<tr><td>" . $f_bank_code . "</td><td>" . $row['application_id'] . "</td><td>" . $row['ben_id'] . "</td><td>" . $row['ben_name'] . "</td><td>" . $row['mobile_no'] . "</td><td>" . $row['district_name'] . "</td><td>" . $row['local_body_name'] . "</td><td>" . $row['municipality_name'] . "</td><td>" . $row['gp_ward_name'] . "</td><td>" . $ss_card_no . "</td><td>" . $row['status_des'] . "</td></tr>";
            }
        } else {
            echo '<tr><td colspan="11">No Records found</td></tr>';
        }
        echo '</table>';
    }
    public function generate_excel_list_state(Request $request)
    {
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $designation_id = Auth::user()->designation_id;
        if ($designation_id != 'HOD' || $designation_id == 'HOP' || $designationId == 'MisState') {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $districts = District::get();
        return view(
            'DuplicateBank.excelState',
            [
                'districts' => $districts,
                'scheme_id' => $this->scheme_id
            ]
        );
    }
    public function generate_excel_list_state_download(Request $request)
    {
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $designation_id = Auth::user()->designation_id;
        if ($designation_id != 'HOD' || $designation_id == 'HOP' || $designationId == 'MisState') {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $districts = District::get();
        $district_code = $request->district_code;
        if (empty($district_code)) {
            return redirect("/DupBankAccounttExcelState")->with('error', 'District Code Not Found');
        }
        if (!ctype_digit($district_code)) {
            return redirect("/DupBankAccounttExcelState")->with('error', 'District Not Valid');
        }
        $district_row = District::where('district_code', $district_code)->first();
        if (empty($district_row)) {
            return redirect("/dedupBankListView")->with('error', 'District Not Valid');
        }
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $subdistrict = SubDistrict::get();
        $block = Taluka::get();
        $ben_list = array();
        $i = 0;
        $query = "select P.last_accno,Q.dist_code,Q.local_body_code,Q.ben_id,Q.application_id,Q.ben_name,Q.mobile_no,Q.ss_card_no,Q.faulty_status from 
            (select A.last_accno,A.cnt
                from
                (
                select trim(last_accno) as last_accno,count(1) as cnt
                from lb_main.ben_payment_details_bank_code_dup where ben_status=" . $this->ben_status . "
                group  by trim(last_accno) having(count(1)>1)
                ) as A WHERE EXISTS
                    (SELECT 1
                     FROM lb_main.ben_payment_details_bank_code_dup p
                     WHERE p.ben_status=" . $this->ben_status . " and trim(p.last_accno) = A.last_accno
                       AND p.dist_code=" . $district_code . ") order by cnt desc
                       ) as P JOIN  lb_main.ben_payment_details_bank_code_dup Q 
                       ON  trim(P.last_accno)=trim(Q.last_accno) where Q.ben_status=" . $this->ben_status . " order by Q.last_accno,Q.ben_name";
        $rows = DB::connection('pgsql_payment')->select($query);

        foreach ($rows as $arr) {

            $ben_list[$i]['bank_code'] = $arr->last_accno;
            $ben_list[$i]['local_body_code'] = $arr->local_body_code;
            $ben_list[$i]['application_id'] = $arr->application_id;
            $ben_list[$i]['ben_id'] = $arr->ben_id;
            $ben_list[$i]['ben_name'] = $arr->ben_name;
            $ben_list[$i]['mobile_no'] = $arr->mobile_no;
            $ben_list[$i]['ss_card_no'] = $arr->ss_card_no;
            $ben_list[$i]['faulty_status'] = intval($arr->faulty_status);
            $district_row = $districts->where('district_code', $arr->dist_code)->first();
            $ben_list[$i]['district_name'] =  $district_row->district_name;
            $local_body = '';
            if (strlen($arr->local_body_code) == 5) {
                $local_body = $subdistrict->where('sub_district_code', $arr->local_body_code)->first();
                $ben_list[$i]['local_body_name'] =  'SubDivision-' . $local_body->sub_district_name;
            } else {
                $local_body = $block->where('block_code', $arr->local_body_code)->first();
                $ben_list[$i]['local_body_name'] =  'Block-' . $local_body->block_name;
            }
            $i++;
        }

        $filename = "Bank_Account_Duplicate" . "-" . trim($district_row->district_name) . "-" . date('d/m/Y') . ".xls";
        header("Content-Type: application/xls");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        echo '<table border="1">';
        echo '<tr><th>Bank Account No.</th><th>Applicant Id</th><th>Beneficiary Id</th><th>Beneficiary name</th><th>Mobile No.</th><th>District</th><th>Block/SubDivision</th><th>Swasthyasathi Card No.</th></tr>';
        if (count($ben_list) > 0) {
            foreach ($ben_list as $row) {
                $sws_card_no = (string) $row['ss_card_no'];
                if (!empty($sws_card_no))
                    $ss_card_no = "'$sws_card_no'";
                else
                    $ss_card_no = $sws_card_no;

                $bank_code = (string) $row['bank_code'];
                if (!empty($bank_code))
                    $f_bank_code = "'$bank_code'";
                else
                    $f_bank_code = $bank_code;
                echo "<tr><td>" . $f_bank_code . "</td><td>" . $row['application_id'] . "</td><td>" . $row['ben_id'] . "</td><td>" . $row['ben_name'] . "</td><td>" . $row['mobile_no'] . "</td><td>" . $row['district_name'] . "</td><td>" . $row['local_body_name'] . "</td><td>" . $ss_card_no . "</td></tr>";
            }
        } else {
            echo '<tr><td colspan="9">No Records found</td></tr>';
        }
        echo '</table>';
    }
    public function dedupBankUpdate(Request $request)
    {
        // dd($request->all());
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $errormsg = Config::get('constants.errormsg');
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $verifier_condition = ' and p.local_body_code=' . $urban_body_code;
        } else {
            $verifier_condition = '';
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }

        $bank_code = $request->bank_code;
        $application_id = $request->application_id;
        $is_faulty = $request->is_faulty;

        if (empty($bank_code)) {
            return redirect("/dedupBankListView")->with('error', 'Bank Account No.Not Found');
        }
        if (empty($application_id)) {
            return redirect("/dedupBankView?last_accno=" . $bank_code)->with('error', 'Application ID Not Found');
        }
        if (!ctype_digit($application_id)) {
            return redirect("/dedupBankView?last_accno=" . $bank_code)->with('error', 'Application ID Not Valid');
        }

        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $TableBank = $getModelFunc->getTable($district_code, $this->source_type, 4);
        $TableFaultyBank = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $personal_model = new DataSourceCommon;
        $personal_model->setTable('' . $TablePersonal);
        // dd($personal_model);
        $personal_model_f = new DataSourceCommon;
        $personal_model_f->setTable('' . $TableFaultyPersonal);
        $accept_reject_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
        $accept_reject_model->setTable('' . $Table);
        if ($is_faulty == 1) {
            $query = $personal_model_f->where($TableFaultyPersonal . '.application_id', $application_id)->where($TableFaultyBank . '.application_id', $application_id)->whereraw("trim(bank_code)='$bank_code'");
            $query = $query->join($TableFaultyBank, $TableFaultyBank . '.application_id', '=', $TableFaultyPersonal . '.application_id');
            $row = $query->first();
        } else if ($is_faulty == 0) {

            $query = $personal_model->where($TablePersonal . '.application_id', $application_id)->where($TableBank . '.application_id', $application_id)->whereraw("trim(bank_code)='$bank_code'");
            $query = $query->join($TableBank, $TableBank . '.application_id', '=', $TablePersonal . '.application_id');
            // if($application_id == 127200281){
            //      dd($bank_code);
            // }
            
            $row = $query->first();
        }
        // dd($row );
        if (empty($row->application_id)) {
            return redirect("/dedupBankListView")->with('error', ' Application Id Not found in Db..');
        }
        $DraftPfImageTable = new DataSourceCommon;
        if ($is_faulty == 1) {
            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 5, 1);
        } else {
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 5, 1);
        }
        $DraftPfImageTable->setConnection('pgsql_encread');
        $DraftPfImageTable->setTable('' . $Table);

        $DraftEncloserTable = new DataSourceCommon;
        if ($is_faulty == 1) {
            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 6, 1);
        } else {
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 6, 1);
        }
        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);
        $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
        $profileImagedata = $DraftPfImageTable->select('image_type', 'application_id')->where('image_type', $doc_profile->id)->where('application_id', $application_id)->first();
        $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type');
        //dd($profileImagedata->toArray());
        if (!empty($profileImagedata) || count($encolserdata) > 0) {
            $encolserCount = 1;
        }
        $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first()->toArray();
        // dd($doc_id_list['doc_list_man']);
        if (isset($doc_id_list['doc_list_man']) && $doc_id_list['doc_list_man'] != 'null') {
            // dd($doc_id_list);
            $doc_list_man = DocumentType::selectRaw('\'1\' as required,id,is_profile_pic,doc_size_kb,doc_name,doc_type,doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_man']))->get()->toArray();
        } else
            $doc_list_man = array();
        if (isset($doc_id_list['doc_list_opt']) && $doc_id_list['doc_list_opt'] != 'null') {
            $doc_list_opt = DocumentType::selectRaw('\'0\' as required,id,is_profile_pic,doc_size_kb,doc_name,doc_type,doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_opt']))->get()->toArray();
        } else
            $doc_list_opt = array();
        if (count($doc_list_man) > 0 || count($doc_list_opt) > 0) {
            $doc_list = array_merge($doc_list_man, $doc_list_opt);
        } else {
            $doc_list = array();
        }
        $encloser_list = array();
        $i = 0;
        $bankEncloserCount = 0;
        if (count($doc_list) > 0) {
            foreach ($doc_list as $doc) {
                $encloser_list[$i]['application_id'] = $application_id;
                $encloser_list[$i]['id'] = $doc['id'];
                $encloser_list[$i]['is_profile_pic'] = intval($doc['is_profile_pic']);
                $encloser_list[$i]['doc_size_kb'] = $doc['doc_size_kb'];
                $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                $encloser_list[$i]['doc_type'] = $doc['doc_type'];
                if ($doc['is_profile_pic']) {
                    if (!empty($profileImagedata->application_id)) {
                        $encloser_list[$i]['can_download'] = 1;
                        $encloser_list[$i]['required'] = 0;
                    } else {
                        $encloser_list[$i]['can_download'] = 0;
                        if ($doc['required'] == 1 && $is_faulty == 0) {
                            $encloser_list[$i]['required'] = 1;
                        } else
                            $encloser_list[$i]['required'] = 0;
                    }
                } else {
                    //dd($encolserdata);

                    if (in_array($doc['id'], $encolserdata->toArray())) {
                        $encloser_list[$i]['can_download'] = 1;
                        if ($doc['id'] == 10) {
                            $encloser_list[$i]['required'] = 1;
                        } else {
                            $encloser_list[$i]['required'] = 0;
                        }
                    } else {
                        $encloser_list[$i]['can_download'] = 0;
                        if ($is_faulty == 1) {
                            if ($doc['id'] == 10) {
                                $encloser_list[$i]['required'] = 1;
                            } else {
                                $encloser_list[$i]['required'] = 0;
                            }
                        } else {
                            if ($doc['required'] == 1) {
                                $encloser_list[$i]['required'] = 1;
                            } else {
                                if ($doc['id'] == 2 &&  (trim($row->caste) == 'SC' || trim($row->caste) == 'ST')) {
                                    $encloser_list[$i]['required'] = 1;
                                } else
                                    $encloser_list[$i]['required'] = 0;
                            }
                        }
                    }
                }
                $i++;
            }
        }
        // dd($row->toArray());
        return view(
            'DuplicateBank.dedupBankUpdate',
            [
                'application_id' => $application_id,
                'is_faulty' => $is_faulty,
                'row' => $row,
                'bank_code' => $bank_code,
                'scheme_id' => $this->scheme_id,
                'encloser_list' => $encloser_list,
            ]
        );
    }
    public function dedupBankUpdatePost(Request $request)
    {
        // return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        //   dd($request->all());
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $errormsg = Config::get('constants.errormsg');
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $verifier_condition = ' and p.local_body_code=' . $urban_body_code;
        } else {
            $verifier_condition = '';
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }

        $old_bank_ifsc = trim($request->old_bank_ifsc);
        // dd($old_bank_ifsc);
        $old_bank_code = trim($request->old_bank_code);
        $bank_ifsc = trim($request->bank_ifsc_code);
        $bank_code = trim($request->bank_account_number);
        $application_id = $request->application_id;
        $is_faulty = $request->is_faulty;

        if (empty($old_bank_code)) {
            return redirect("/dedupBankListView")->with('error', 'Bank Account No.Not Found');
        }
        if (empty($application_id)) {
            return redirect("/dedupBankView?last_accno=" . $old_bank_code)->with('error', 'Application ID Not Found');
        }
        if (!ctype_digit($application_id)) {
            return redirect("/dedupBankView?last_accno=" . $old_bank_code)->with('error', 'Application ID Not Valid');
        }

        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $PfImageModel = new DataSourceCommon;
        $EncloserModel = new DataSourceCommon;
        $BankModel = new DataSourceCommon;
        $ProfileModel = new DataSourceCommon;
        $modelNameAcceptReject = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
        $modelNameAcceptReject->setTable('' . $Table);


        $sws_check = 1;
        if ($is_faulty == 1) {
            $TableProfileImage = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 5);
            $TableEncolserTable = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 6);
            $TableBank = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 4);
            $TablePersonal = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1);
            $TableContact = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 3);
            $sws_check = 0;
        } else {
            $TableProfileImage = $getModelFunc->getTable($district_code, $this->source_type, 5);
            $TableEncolserTable = $getModelFunc->getTable($district_code, $this->source_type, 6);
            $TableBank = $getModelFunc->getTable($district_code, $this->source_type, 4);
            $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
            $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
            $sws_check = 1;
        }
        $PfImageModel->setTable('' . $TableProfileImage);
        $PfImageModel->setConnection('pgsql_encwrite');
        $EncloserModel->setTable('' . $TableEncolserTable);
        $EncloserModel->setConnection('pgsql_encwrite');
        $BankModel->setTable('' . $TableBank);
        $BankModel->setConnection('pgsql_appwrite');
        $ProfileModel->setTable('' . $TablePersonal);
        // dd($ProfileModel);
        // dd($application_id);
        $ProfileModel->setConnection('pgsql_appwrite');
        $query = $ProfileModel->where($TablePersonal . '.application_id', $application_id)->where($TableBank . '.application_id', $application_id)->whereraw("trim(bank_code)='$old_bank_code'");
        $query = $query->join($TableBank, $TableBank . '.application_id', '=', $TablePersonal . '.application_id');
        $query = $query->leftjoin($TableContact, $TableContact . '.application_id', '=', $TablePersonal . '.application_id');
        // dd($query->toSql());
        $row = $query->first();
        //    dd($row->toArray());
        if (empty($row->application_id)) {
            return redirect("/dedupBankListView")->with('error', ' Application Id Not found in Db!!');
        }

        $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
        $profileImagedata = $PfImageModel->select('image_type', 'application_id')->where('image_type', $doc_profile->id)->where('application_id', $application_id)->first();
        $encolserdata = $EncloserModel->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type');
        //dd($encolserdata->toArray());
        if (!empty($profileImagedata) || count($encolserdata) > 0) {
            $encolserCount = 1;
        }
        $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first()->toArray();
        // dd($doc_id_list['doc_list_man']);
        if (isset($doc_id_list['doc_list_man']) && $doc_id_list['doc_list_man'] != 'null') {
            // dd($doc_id_list);
            $doc_list_man = DocumentType::selectRaw('\'1\' as required,id,is_profile_pic,doc_size_kb,doc_name,doc_type,doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_man']))->get()->toArray();
        } else
            $doc_list_man = array();
        if (isset($doc_id_list['doc_list_opt']) && $doc_id_list['doc_list_opt'] != 'null') {
            $doc_list_opt = DocumentType::selectRaw('\'0\' as required,id,is_profile_pic,doc_size_kb,doc_name,doc_type,doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_opt']))->get()->toArray();
        } else
            $doc_list_opt = array();
        if (count($doc_list_man) > 0 || count($doc_list_opt) > 0) {
            $doc_list = array_merge($doc_list_man, $doc_list_opt);
        } else {
            $doc_list = array();
        }
        $encloser_list = array();
        $i = 0;

        if (count($doc_list) > 0) {
            foreach ($doc_list as $doc) {
                $encloser_list[$i]['application_id'] = $application_id;
                $encloser_list[$i]['id'] = $doc['id'];
                $encloser_list[$i]['is_profile_pic'] = intval($doc['is_profile_pic']);
                $encloser_list[$i]['doc_size_kb'] = $doc['doc_size_kb'];
                $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                $encloser_list[$i]['doc_type'] = $doc['doc_type'];
                if ($doc['is_profile_pic']) {
                    if (!empty($profileImagedata->application_id)) {
                        $encloser_list[$i]['required'] = 0;
                        $encloser_list[$i]['can_download'] = 1;
                    } else {
                        $encloser_list[$i]['can_download'] = 0;
                        if ($doc['required'] == 1 && $is_faulty == 0) {
                            $encloser_list[$i]['required'] = 1;
                        } else
                            $encloser_list[$i]['required'] = 0;
                    }
                } else {
                    //dd($encolserdata);

                    if (in_array($doc['id'], $encolserdata->toArray())) {
                        $encloser_list[$i]['can_download'] = 1;
                        if ($doc['id'] == 10) {
                            if ($is_faulty == 1) {
                                $encloser_list[$i]['required'] = 1;
                            } else {
                                $encloser_list[$i]['required'] = 1;
                            }
                        } else {
                            $encloser_list[$i]['required'] = 0;
                        }
                    } else {
                        $encloser_list[$i]['can_download'] = 0;
                        if ($doc['id'] == 10) {
                            if ($is_faulty == 1) {
                                $encloser_list[$i]['required'] = 1;
                            } else {
                                $encloser_list[$i]['required'] = 1;
                            }
                        } else {
                            if ($is_faulty == 1) {
                                $encloser_list[$i]['required'] = 0;
                            } else {
                                if ($doc['required'] == 1) {
                                    $encloser_list[$i]['required'] = 1;
                                } else {
                                    $encloser_list[$i]['required'] = 0;
                                }
                            }
                        }
                    }
                }
                $i++;
            }
        }
        //  dd($encloser_list);
        $rules = [
            'bank_ifsc_code' => 'required',
            'name_of_bank' => 'required|string|max:200',
            'bank_branch' => 'required|string|max:200',
            'bank_account_number' => 'required|numeric|required_with:confirm_bank_account_number|same:confirm_bank_account_number',
            'confirm_bank_account_number' => 'required|numeric',
        ];
        $attributes = array();
        $messages = array();
        $attributes['aadhar_no'] = 'Applicant Aadhaar Number';
        $attributes['mobile_no'] = 'Mobile Number';
        $attributes['bank_ifsc_code'] = 'IFS Code';
        $attributes['name_of_bank'] = 'Bank Name';
        $attributes['bank_branch'] = 'Bank Branch Name';
        $attributes['bank_account_number'] = 'Bank Account Number';
        if (count($encloser_list) > 0) {
            foreach ($encloser_list as  $value) {
                if ($value['required'] == 1) {
                    $required = 'required';
                } else
                    $required = 'nullable';
                $rules['doc_' . $value['id']] = $required . '|mimes:' . $value['doc_type'] . '|max:' . $value['doc_size_kb'] . ',';
                $messages['doc_' . $value['id'] . '.max'] = "The file uploaded for " . $value['doc_name'] . " size must be less than " . $value['doc_size_kb'] . " KB";
                $messages['doc_' . $value['id'] . '.mimes'] = "The file uploaded for " . $value['doc_name'] . " must be of type " . $value['doc_type'];
                $messages['doc_' . $value['id'] . '.required'] = "Document for " . $value['doc_name'] . " must be uploaded";
            }
        }
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if (!$validator->passes()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        } else {
            $check_ifsc_count = BankDetails::where('is_active', 1)->where('ifsc', trim($request->bank_ifsc_code))->where('is_active', 1)->count();
            if ($check_ifsc_count == 0) {
                $return_text = 'IFSC not Found in our System..Please try different';
                return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
            }
            $modelmainArch = new DataSourceCommon;
            $modelmainArch->setTable('lb_scheme.update_ben_details');
            $modelmainArch->setConnection('pgsql_appwrite');
            $modelfailedpayments = new DataSourceCommon;
            $modelfailedpayments->setConnection('pgsql_payment');
            $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
            $modelfailedpayments1 = new DataSourceCommon;
            $modelfailedpayments1->setConnection('pgsql_payment');
            $modelfailedpayments1->setTable('lb_main.ben_payment_details_bank_code_dup');
            $duplicate_row = DB::connection('pgsql_appread')->select("select count(1) as cnt from lb_scheme.duplicate_bank_view where trim(bank_code)='" . $bank_code . "'");
            $row_count = $duplicate_row[0]->cnt;
            // dd($row_count);
            if ($row_count > 0) {
                $return_text = 'Duplicate Bank Account Details.';
                return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
            }

            if(!empty($bank_code))
            {
                $DupCheckBankOap = DupCheck::getDupCheckBank(10,$bank_code);
                if(!empty($DupCheckBankOap)){
                    $return_text = 'Duplicate Bank Account Number present in Old Age Pension Scheme with Beneficiary ID- '.$DupCheckBankOap.'';
                    return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
                }
                $DupCheckBankJohar = DupCheck::getDupCheckBank(1,$bank_code);
                if(!empty($DupCheckBankJohar)){
                    $return_text = 'Duplicate Bank Account Number present Jai Johar Pension Scheme with Beneficiary ID- '.$DupCheckBankJohar.'';
                    return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
                }
                $DupCheckBankBandhu = DupCheck::getDupCheckBank(3,$bank_code);
                if(!empty($DupCheckBankBandhu)){
                    $return_text = 'Duplicate Bank Account Number present Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- '.$DupCheckBankBandhu.'';
                    return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
                }
            }
            if ($sws_check == 1) {
                if (!empty($row->ss_card_no)  && !empty($row->ss_ben_id)) {
                    $row_count_sws = $ProfileModel->where('ss_card_no', $row->ss_card_no)->where('ss_ben_id', $row->ss_ben_id)->count('application_id');
                    if ($row_count_sws > 1) {
                        $return_text = 'Duplicate Swastha Sathi Card No. or Aadhaar details or Name.. You must have to reject duplicate first';
                        return redirect('dedupBankView?last_accno=' . $old_bank_code)->with('error', $return_text);
                    }
                }
            }

            $today = date("Y-m-d h:i:s");
            $new_value = [];
            try {
                DB::connection('pgsql_appwrite')->beginTransaction();
                DB::connection('pgsql_encwrite')->beginTransaction();
                DB::connection('pgsql_payment')->beginTransaction();
                $new_value['bank_name'] = trim($request->name_of_bank);
                $new_value['branch_name'] = trim($request->bank_branch);
                $new_value['bank_ifsc'] =  trim($request->bank_ifsc_code);
                $new_value['bank_code'] = trim($request->bank_account_number);
                $modelmainArch->update_code  = 101;
                $modelmainArch->application_id  = $application_id;
                $modelmainArch->beneficiary_id  = $row->beneficiary_id;
                $modelmainArch->new_data  = $application_id;
                $modelmainArch->old_data  =  json_encode($row);
                $modelmainArch->new_data  =  json_encode($new_value);
                $modelmainArch->next_level_role_id  =  0;
                $modelmainArch->dist_code  =  $row->created_by_dist_code;
                $modelmainArch->local_body_code  =  $row->created_by_local_body_code;
                $modelmainArch->rural_urban_id  =  $row->rural_urban_id;
                $modelmainArch->block_ulb_code  =  $row->block_ulb_code;
                $modelmainArch->gp_ward_code  =  $row->gp_ward_code;
                $modelmainArch->created_at  =  $today;
                $modelmainArch->user_id  =  $user_id;
                $modelmainArch->ip_address  =  request()->ip();
                $modelmainArchStatus = $modelmainArch->save();
                // $pension_details_bank_arr = array();
                // $pension_details_bank_arr['bank_name']  = trim($request->name_of_bank);
                // $pension_details_bank_arr['branch_name']    = trim($request->bank_branch);
                // $pension_details_bank_arr['bank_code']    = trim($request->bank_account_number);
                // $pension_details_bank_arr['bank_ifsc']   = trim($request->bank_ifsc_code);
                // $pension_details_bank_arr['created_by_level'] = $mapping_level;
                // $pension_details_bank_arr['created_by'] = $user_id;
                // $pension_details_bank_arr['ip_address'] = $request->ip();
                // $pension_details_bank_arr['created_by_dist_code'] = $district_code;

                // $payments_arr = array();
                // $payments_arr['last_accno']    = trim($request->bank_account_number);
                // $payments_arr['last_ifsc']    = trim($request->bank_ifsc_code);

                // $payments_arr['ben_status']    = 1;
                // $payments_arr['acc_validated']    = 0;
                // $is_saved_bank = $BankModel->where('created_by_dist_code', $district_code)->where('application_id', $application_id)->update($pension_details_bank_arr);
                // $is_saved_bank_payment = $modelfailedpayments->where('dist_code', $district_code)->where('ben_status', $this->ben_status)->where('application_id', $application_id)->update($payments_arr);
                $payments_arr_new = array();
                $payments_arr_new['new_last_accno']    = trim($request->bank_account_number);
                $payments_arr_new['new_last_ifsc']   = trim($request->bank_ifsc_code);
                $payments_arr_new['ben_status']    = 101;
                $payments_arr_new['is_approved']    = 0;
                $is_saved_bank_payment1 = $modelfailedpayments1->where('dist_code', $district_code)->where('ben_status', $this->ben_status)->where('application_id', $application_id)->update($payments_arr_new);
                $k = 0;
                $doc_type_in = array();
                $profile_arc_status = 0;
                if (count($encloser_list) > 0) {
                    foreach ($encloser_list as $enc_row) {
                        if ($request->hasFile('doc_' . $enc_row['id'])) {
                            if ($enc_row['can_download']) {
                                if ($enc_row['is_profile_pic']) {
                                    $profile_arc_status = 1;
                                } else {
                                    array_push($doc_type_in, $enc_row['id']);
                                }
                            }
                        }
                    }
                }
                if (count($doc_type_in) > 0) {
                    $arch_status_1 = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_arch(
                        application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                        select application_id,  document_type, attched_document, created_by_level, created_at, 
                        updated_at, deleted_at, created_by, ip_address, document_extension, 
                        document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                        from  lb_scheme.ben_attach_documents
                     where document_type IN (" . implode(',', $doc_type_in) . ") and application_id=" . $application_id);
                } else {
                    $arch_status_1 = 1;
                }
                if ($profile_arc_status == 1) {
                    $arch_status_2 = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_profile_image_arch(
                        application_id, beneficiary_id, profile_image, ip_address, image_extension, 
	image_mime_type, image_type, created_by_level, created_at, updated_at, 
	deleted_at, created_by, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                        select application_id, beneficiary_id, profile_image, ip_address, image_extension, 
	image_mime_type, image_type, created_by_level, created_at, updated_at, 
	deleted_at, created_by, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type from  lb_scheme.ben_profile_image
                     where  application_id=" . $application_id);
                } else {
                    $arch_status_2 = 1;
                }
                if (count($encloser_list) > 0) {
                    foreach ($encloser_list as $enc_row) {
                        if ($request->hasFile('doc_' . $enc_row['id'])) {
                            $image_file = $request->file('doc_' . $enc_row['id']);
                            $img_data = file_get_contents($image_file);
                            $extension = $image_file->getClientOriginalExtension();
                            $mime_type = $image_file->getMimeType();
                            //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                            $base64 = base64_encode($img_data);
                            $encolser_row = array();
                            if ($enc_row['is_profile_pic']) {
                                $encolser_row['image_type'] = $enc_row['id'];
                                $encolser_row['profile_image'] = $base64;
                                $encolser_row['image_extension'] = $extension;
                                $encolser_row['image_mime_type'] = $mime_type;
                            } else {
                                $encolser_row['document_type'] = $enc_row['id'];
                                $encolser_row['attched_document'] = $base64;
                                $encolser_row['document_extension'] = $extension;
                                $encolser_row['document_mime_type'] = $mime_type;
                            }
                            $encolser_row['updated_at'] = $today;
                            $encolser_row['created_by_level'] = $mapping_level;
                            $encolser_row['created_by'] = $user_id;
                            $encolser_row['ip_address'] = $request->ip();
                            $encolser_row['created_by_dist_code'] = $district_code;
                            if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
                                $encolser_row['created_by_local_body_code'] = $urban_body_code;
                            }
                        }
                        if ($request->hasFile('doc_' . $enc_row['id'])) {
                            if ($enc_row['can_download']) {
                                if ($enc_row['is_profile_pic']) {
                                    $encolser_status = $PfImageModel->where('image_type', $enc_row['id'])->where('application_id', $application_id)->update($encolser_row);
                                } else {
                                    $encolser_status = $EncloserModel->where('document_type', $enc_row['id'])->where('application_id', $application_id)->update($encolser_row);
                                }
                            } else {
                                $encolser_row['application_id'] = $application_id;
                                if ($enc_row['is_profile_pic'])
                                    $encolser_status =  $PfImageModel->insert($encolser_row);
                                else
                                    $encolser_status = $EncloserModel->insert($encolser_row);
                            }
                        } else {
                            $encolser_status = 1;
                        }
                        if ($encolser_status == 1) {
                            $k++;
                        }
                    }
                }
                //dd($k);
                if ($k == count($encloser_list)) {
                    $encolser_crud_status = 1;
                } else {
                    $encolser_crud_status = 0;
                }
                $op_type = 'BankUpdate';
                $modelNameAcceptReject->op_type =  $op_type;
                $modelNameAcceptReject->application_id = $application_id;
                $modelNameAcceptReject->designation_id = $designation_id;
                $modelNameAcceptReject->scheme_id = $scheme_id;
                $modelNameAcceptReject->mapping_level = $mapping_level;
                $modelNameAcceptReject->created_by = $user_id;
                $modelNameAcceptReject->created_by_level = trim($mapping_level);
                $modelNameAcceptReject->created_by_dist_code = $district_code;
                $modelNameAcceptReject->created_by_local_body_code = NULL;
                $modelNameAcceptReject->ip_address = request()->ip();
                $is_accept_reject = $modelNameAcceptReject->save();


                //dd($is_saved_bank_payment1);
                if ($modelmainArchStatus && $arch_status_1 &&  $arch_status_2 && $encolser_crud_status &&  $is_accept_reject && $is_saved_bank_payment1) {
                    DB::connection('pgsql_appwrite')->commit();
                    DB::connection('pgsql_encwrite')->commit();
                    DB::connection('pgsql_payment')->commit();
                    $return_text = "Beneficiary informations forward to Approver successfully with Application Id:" . $row->application_id;
                    return redirect("/dedupBankView?last_accno=" . $old_bank_code)->with('success', $return_text);
                } else {
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_encwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
                }
            } catch (\Exception $e) {
                DB::connection('pgsql_appwrite')->rollBack();
                DB::connection('pgsql_encwrite')->rollBack();
                DB::connection('pgsql_payment')->rollBack();
                // dd($e);
                $return_text = $errormsg['roolback'];
                return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
            }
        }
    }
    public function dedupBankSamePost(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $this->middleware('auth');
        $scheme_id = $this->scheme_id;
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $errormsg = Config::get('constants.errormsg');
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                $mapping_level = $roleObj['mapping_level'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' || $designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
            $is_active = 1;
        } else {
            $is_active = 0;
        }
        if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
            $verifier_condition = ' and p.local_body_code=' . $urban_body_code;
        } else {
            $verifier_condition = '';
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }

        $old_bank_ifsc = trim($request->old_bank_ifsc);
        $old_bank_code = trim($request->old_bank_code);
        // dd($old_bank_code);
        $application_id = $request->application_id;
        $is_faulty = $request->is_faulty;

        if (empty($old_bank_code)) {
            return redirect("/dedupBankListView")->with('error', 'Bank Account No.Not Found');
        }
        if (empty($application_id)) {
            return redirect("/dedupBankView?last_accno=" . $old_bank_code)->with('error', 'Application ID Not Found');
        }
        if (!ctype_digit($application_id)) {
            return redirect("/dedupBankView?last_accno=" . $old_bank_code)->with('error', 'Application ID Not Valid');
        }

        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $PfImageModel = new DataSourceCommon;
        $EncloserModel = new DataSourceCommon;
        $BankModel = new DataSourceCommon;
        $ProfileModel = new DataSourceCommon;
        $modelNameAcceptReject = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
        $modelNameAcceptReject->setTable('' . $Table);
        $sws_check = 1;
        if ($is_faulty == 1) {
            $TableBank = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 4);
            $TablePersonal = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1);
            $TableContact = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 3);
            $sws_check = 0;
        } else {

            $TableBank = $getModelFunc->getTable($district_code, $this->source_type, 4);
            $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
            $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
            $sws_check = 1;
        }
        $BankModel->setTable('' . $TableBank);
        $BankModel->setConnection('pgsql_appwrite');
        $ProfileModel->setTable('' . $TablePersonal);
        $ProfileModel->setConnection('pgsql_appwrite');
        $query = $ProfileModel->where($TablePersonal . '.application_id', $application_id)->where($TableBank . '.application_id', $application_id)->whereraw("trim(bank_code)='$old_bank_code'");
        $query = $query->join($TableBank, $TableBank . '.application_id', '=', $TablePersonal . '.application_id');
        $query = $query->leftjoin($TableContact, $TableContact . '.application_id', '=', $TablePersonal . '.application_id');
        $row = $query->first();
        // dd($row->toArray());
        if (empty($row->application_id)) {
            return redirect("/dedupBankListView")->with('error', ' Application Id Not found in Db.!');
        }
        //dd('ok');

        $rules = [
            'old_bank_ifsc' => 'required',
            'old_bank_code' => 'required|numeric',
        ];
        $attributes = array();
        $messages = array();
        $attributes['old_bank_ifsc'] = 'IFS Code';
        $attributes['old_bank_code'] = 'Bank Account Number';

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if (!$validator->passes()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        } else {
            $check_ifsc_count = BankDetails::where('is_active', 1)->where('ifsc', trim($old_bank_ifsc))->where('is_active', 1)->count();
            // $check_ifsc_count = 1;
            if ($check_ifsc_count == 0) {
                $return_text = 'IFSC not Found in our System..Please try different';
                return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
            }
            $modelmainArch = new DataSourceCommon;
            $modelmainArch->setTable('lb_scheme.update_ben_details');
            $modelmainArch->setConnection('pgsql_appwrite');
            $modelfailedpayments = new DataSourceCommon;
            $modelfailedpayments->setConnection('pgsql_payment');
            $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
            $modelfailedpayments1 = new DataSourceCommon;
            $modelfailedpayments1->setConnection('pgsql_payment');
            $modelfailedpayments1->setTable('lb_main.ben_payment_details_bank_code_dup');
            $duplicate_row = DB::connection('pgsql_appread')->select("select count(1) as cnt from lb_scheme.duplicate_bank_view where application_id!=" . $application_id . " and trim(bank_code)='" . $old_bank_code . "'");
            //dd($duplicate_row);
            $row_count = $duplicate_row[0]->cnt;
            if ($row_count > 0) {
                //dd($row_count);
                $return_text = 'Duplicate Bank Account Details.';
                return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
            }
            if ($sws_check == 1) {
                if (!empty($row->ss_card_no)  && !empty($row->ss_ben_id)) {
                    $row_count_sws = $ProfileModel->where('ss_card_no', $row->ss_card_no)->where('ss_ben_id', $row->ss_ben_id)->count('application_id');
                    if ($row_count_sws > 1) {
                        $return_text = 'Duplicate Swastha Sathi Card No. or Aadhaar details or Name.. You must have to reject duplicate first';
                        return redirect('dedupBankView?last_accno=' . $old_bank_code)->with('error', $return_text);
                    }
                }
            }
            $today = date("Y-m-d h:i:s");
            $new_value = [];
            try {
                DB::connection('pgsql_appwrite')->beginTransaction();
                DB::connection('pgsql_payment')->beginTransaction();
                $modelmainArch->update_code  = 200;
                $modelmainArch->application_id  = $application_id;
                $modelmainArch->beneficiary_id  = $row->beneficiary_id;
                $modelmainArch->next_level_role_id  =  0;
                $modelmainArch->dist_code  =  $row->created_by_dist_code;
                $modelmainArch->local_body_code  =  $row->created_by_local_body_code;
                $modelmainArch->rural_urban_id  =  $row->rural_urban_id;
                $modelmainArch->block_ulb_code  =  $row->block_ulb_code;
                $modelmainArch->gp_ward_code  =  $row->gp_ward_code;
                $modelmainArch->created_at  =  $today;
                $modelmainArch->user_id  = $user_id;
                $modelmainArch->ip_address  = request()->ip();
                $modelmainArchStatus = $modelmainArch->save();
                // $payments_arr = array();
                // $payments_arr['ben_status']    = 1;
                // $is_saved_bank_payment = $modelfailedpayments->where('dist_code', $district_code)->where('ben_status', $this->ben_status)->where('application_id', $application_id)->update($payments_arr);
                $payments_arr_new = array();
                $payments_arr_new['ben_status']    = 200;
                $payments_arr_new['is_approved']    = 0;
                $is_saved_bank_payment1 = $modelfailedpayments1->where('dist_code', $district_code)->where('ben_status', $this->ben_status)->where('application_id', $application_id)->update($payments_arr_new);
                $op_type = 'BankUpdateSame';
                $modelNameAcceptReject->op_type =  $op_type;
                $modelNameAcceptReject->application_id = $application_id;
                $modelNameAcceptReject->designation_id = $designation_id;
                $modelNameAcceptReject->scheme_id = $scheme_id;
                $modelNameAcceptReject->mapping_level = $mapping_level;
                $modelNameAcceptReject->created_by = $user_id;
                $modelNameAcceptReject->created_by_level = trim($mapping_level);
                $modelNameAcceptReject->created_by_dist_code = $district_code;
                $modelNameAcceptReject->created_by_local_body_code = NULL;
                $modelNameAcceptReject->ip_address = request()->ip();
                $is_accept_reject = $modelNameAcceptReject->save();

                //dd($is_saved_bank_payment1);
                if ($modelmainArchStatus && $is_saved_bank_payment1 && $is_accept_reject) {
                    DB::connection('pgsql_appwrite')->commit();
                    DB::connection('pgsql_payment')->commit();
                    $return_text = "Beneficiary informations forward to Approver Successfully with Application Id:" . $row->application_id;
                    return redirect("/dedupBankView?last_accno=" . $old_bank_code)->with('success', $return_text);
                } else {
                    DB::connection('pgsql_appwrite')->rollBack();
                    DB::connection('pgsql_payment')->rollBack();
                    $return_text = $errormsg['roolback'];
                    return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
                }
            } catch (\Exception $e) {
                DB::connection('pgsql_appwrite')->rollBack();
                DB::connection('pgsql_payment')->rollBack();
                //dd($e);
                $return_text = $errormsg['roolback'];
                return redirect('dedupBankUpdate?bank_code=' . $old_bank_code . '&application_id=' . $application_id . '&is_faulty=' . $is_faulty)->with('error', $return_text);
            }
        }
    }
    function dedupBankMis(Request $request)
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
            'DuplicateBank.misreport',
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
        $attributes['from_date'] = 'From Date';
        $attributes['to_date'] = 'To Date';
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $user_msg = "Duplicate Bank Account No. and IFSC Report";
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
                    $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $column = "Block";
                    $data = $this->getBlockWise($district, NULL, NULL, NULL, $from_date, $to_date, $caste);
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
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY/MM/DD';
        $whereCon = "where 1=1";
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select main.location_id,main.location_name,
        COALESCE(dup.tot_dup,0) as tot_dup,
        COALESCE(dup.total_edit_differ,0) as total_edit_differ,
        COALESCE(dup.total_edit_same,0) as total_edit_same,
        COALESCE(dup.total_rejected,0) as total_rejected
        from
        (
        select district_code as location_id,district_name as location_name
        from public.m_district  
        ) as main LEFT JOIN
        (
            select count(1) tot_dup,
            count(1) filter(where ben_status=101 AND is_approved IN(0,1) ) as total_edit_differ,
            count(1) filter(where ben_status=200 AND is_approved IN(0,1) ) as total_edit_same,
            count(1) filter(where ben_status IN (-98,-99) AND is_approved IN(0,1)) as total_rejected,
            dist_code 
            from lb_main.ben_payment_details_bank_code_dup    
            group by dist_code
        ) as dup ON main.location_id=dup.dist_code";

        // echo $query;die;
        $result = DB::connection('pgsql_payment')->select($query);
        return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$whereCon = "where A.dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select main.location_id,main.location_name||'-SubDivision' as location_name,
        COALESCE(dup.tot_dup,0) as tot_dup,
        COALESCE(dup.total_edit_differ,0) as total_edit_differ,
        COALESCE(dup.total_edit_same,0) as total_edit_same,
        COALESCE(dup.total_rejected,0) as total_rejected
        from
        (
            select sub_district_code as location_id,sub_district_name as location_name
            from public.m_sub_district  " . $whereMain . " 
        ) as main LEFT JOIN
        (
            select count(1) tot_dup,
            count(1) filter(where ben_status=101 AND is_approved IN(0,1) ) as total_edit_differ,
            count(1) filter(where ben_status=200 AND is_approved IN(0,1) ) as total_edit_same,
            count(1) filter(where ben_status IN (-98,-99) AND is_approved IN(0,1) ) as total_rejected,
            local_body_code 
            from lb_main.ben_payment_details_bank_code_dup where dist_code=" . $district_code . "   
            group by local_body_code
        ) as dup ON main.location_id=dup.local_body_code";

        // echo $query;die;
        $result = DB::connection('pgsql_payment')->select($query);
        return $result;
    }
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        //$whereCon = "where A.dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select main.location_id,main.location_name||'-Block' as location_name,
       COALESCE(dup.tot_dup,0) as tot_dup,
       COALESCE(dup.total_edit_differ,0) as total_edit_differ,
       COALESCE(dup.total_edit_same,0) as total_edit_same,
       COALESCE(dup.total_rejected,0) as total_rejected
       from
       (
           select block_code as location_id,block_name as location_name
           from public.m_block  " . $whereMain . " 
       ) as main LEFT JOIN
       (
           select count(1) tot_dup,
           count(1) filter(where ben_status=101 AND is_approved IN(0,1) ) as total_edit_differ,
           count(1) filter(where ben_status=200 AND is_approved IN(0,1) ) as total_edit_same,
           count(1) filter(where ben_status IN (-98,-99) AND is_approved IN(0,1) ) as total_rejected,
           local_body_code 
           from lb_main.ben_payment_details_bank_code_dup where dist_code=" . $district_code . "   
           group by local_body_code
       ) as dup ON main.location_id=dup.local_body_code";

        // echo $query;die;
        $result = DB::connection('pgsql_payment')->select($query);
        return $result;
    }
    public function getMuncWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and sub_district_code=" . $ulb_code;
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select main.location_id,,main.location_name,
        COALESCE(dup.tot_dup,0) as tot_dup,
        COALESCE(dup.total_edit_differ,0) as total_edit_differ,
        COALESCE(dup.total_edit_same,0) as total_edit_same,
        COALESCE(dup.total_rejected,0) as total_rejected
        from
        (
            select urban_body_code as location_id,urban_body_name as location_name
        from public.m_urban_body  " . $whereMain . "
        ) as main LEFT JOIN
        (
            select count(1) tot_dup,
            count(1) filter(where ben_status=101 AND is_approved = 1 ) as total_edit_differ,
            count(1) filter(where ben_status=200 AND is_approved = 1 ) as total_edit_same,
            count(1) filter(where ben_status IN (-98,-99) AND is_approved = 1 ) as total_rejected,
            block_ulb_code 
            from lb_main.ben_payment_details_bank_code_dup where 
            dist_code=" . $district_code . "   and local_body_code=" . $ulb_code . "
            group by block_ulb_code
        ) as dup ON main.location_id=dup.block_ulb_code";

        // echo $query;die;
        $result = DB::connection('pgsql_payment')->select($query);
        return $result;
    }
    public function getGpWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and block_code=" . $ulb_code;
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select main.location_id,main.location_name,
        COALESCE(dup.tot_dup,0) as tot_dup,
        COALESCE(dup.total_edit_differ,0) as total_edit_differ,
        COALESCE(dup.total_edit_same,0) as total_edit_same,
        COALESCE(dup.total_rejected,0) as total_rejected
        from
        (
            select gram_panchyat_code as location_id,gram_panchyat_name as location_name
            from public.m_gp  " . $whereMain . "
        ) as main LEFT JOIN
        (
            select count(1) tot_dup,
            count(1) filter(where ben_status=101 AND is_approved = 1) as total_edit_differ,
            count(1) filter(where ben_status=200 AND is_approved = 1 ) as total_edit_same,
            count(1) filter(where ben_status IN (-98,-99) AND is_approved = 1 ) as total_rejected,
            gp_ward_code 
            from lb_main.ben_payment_details_bank_code_dup where 
            dist_code=" . $district_code . "   and local_body_code=" . $ulb_code . "
            group by gp_ward_code
        ) as dup ON main.location_id=dup.gp_ward_code";

        $result = DB::connection('pgsql_payment')->select($query);
        return $result;
    }
    public function getWardWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL)
    {
        $whereMain = "where  district_code=" . $district_code;
        $whereMain .= " and sub_district_code=" . $ulb_code;
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select main.location_id,main.location_name,
        COALESCE(dup.tot_dup,0) as tot_dup,
        COALESCE(dup.total_edit_differ,0) as total_edit_differ,
        COALESCE(dup.total_edit_same,0) as total_edit_same,
        COALESCE(dup.total_rejected,0) as total_rejected
        from
        (
            select urban_body_code as location_id,urban_body_name as location_name
        from public.m_urban_body  " . $whereMain . "
        ) as main LEFT JOIN
        (
            select count(1) tot_dup,
            count(1) filter(where ben_status=101 AND is_approved = 1) as total_edit_differ,
            count(1) filter(where ben_status=200 AND is_approved = 1) as total_edit_same,
            count(1) filter(where ben_status IN (-98,-99) AND is_approved = 1) as total_rejected,
            gp_ward_code 
            from lb_main.ben_payment_details_bank_code_dup where 
            dist_code=" . $district_code . "   and local_body_code=" . $ulb_code . "
            group by gp_ward_code
        ) as dup ON main.location_id=dup.gp_ward_code";

        // echo $query;die;
        $result = DB::connection('pgsql_payment')->select($query);
        return $result;
    }

    // Approver End
    public function dedupBankApproverListView(Request $request)
    {

        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $distCode = $dutyObj->district_code;

        if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
            $levels = [
                2 => 'Rural',
                1 => 'Urban'
            ];
        }
        return view('DuplicateBank.dedupBankApproverList', ['levels' => $levels, 'dist_code' => $distCode]);
    }
    public function dedupBankApproverList(Request $request)
    {
        // dd($request->all());
        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $distCode = $dutyObj->district_code;
        // dd($distCode);
        $rural_urban = $request->filter_1;
        $local_body_code = $request->filter_2;
        $search_for = $request->search_for;
        if ($request->ajax()) {
            if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
                $query = '';
                $query = "SELECT ben_id AS beneficiary_id, ben_name AS name, last_accno AS old_acc_no, last_ifsc AS old_ifsc, new_last_accno AS new_acc_no, new_last_ifsc AS new_ifsc, b.block_name FROM lb_main.ben_payment_details_bank_code_dup lb
                LEFT JOIN (
                    SELECT block_code, block_name FROM public.m_block
                    UNION ALL
                    SELECT sub_district_code AS block_code, sub_district_name AS block_name FROM public.m_sub_district
                )b ON b.block_code = lb.block_ulb_code
                WHERE is_approved = 0 and dist_code=" . $distCode . "";
                if ($search_for == 1) {
                    $query .= "AND ben_status = 101";
                }
                if ($search_for == 2) {
                    $query .= "AND ben_status = 200";
                }
                if ($search_for == 3) {
                    $query .= "AND ben_status = -98";
                }
                if (!empty($rural_urban)) {
                    $query .= "and rural_urban_id=" . $rural_urban . " ";
                }
                if (!empty($local_body_code)) {
                    $query .= "and local_body_code=" . $local_body_code . " ";
                }
                // dd($query);
                $data = DB::connection('pgsql_payment')->select($query);
                //   dd( $data->old_data);
            } else {
                $data = collect([]);
            }
            //  dd($data);
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('view', function ($data) {
                    $action = '<button class="btn btn-primary btn-xs ben_view_button" value="' . $data->beneficiary_id . '"><i class="glyphicon glyphicon-edit"></i>View</button>';
                    return $action;
                })
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->beneficiary_id . '">';
                })
                ->rawColumns(['view', 'check'])
                ->make(true);
        }
    }
    public function getApproverModalView(Request $request)
    {
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }
        try {
            $benid = $request->benid;

            $query = '';
            $query = "SELECT ben_id AS beneficiary_id, ben_name AS name, last_accno AS old_acc_no, last_ifsc AS old_ifsc, new_last_accno AS new_acc_no, new_last_ifsc AS new_ifsc, b.block_name,mobile_no,caste FROM lb_main.ben_payment_details_bank_code_dup lb
            LEFT JOIN (
                SELECT block_code, block_name FROM public.m_block
                UNION ALL
                SELECT sub_district_code AS block_code, sub_district_name AS block_name FROM public.m_sub_district
            )b ON b.block_code = lb.block_ulb_code
            WHERE is_approved = 0 AND ben_id = " . $benid . " ";
            $ben_details = DB::connection('pgsql_payment')->select($query);
            //  dd($ben_details);
            if ($ben_details == null) {
                return $response = [
                    'status' => 1,
                    'msg' => 'Somethimg went wrong.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            } else {
                $ben_arr = [
                    'ben_name' => $ben_details[0]->name,
                    'id' => $ben_details[0]->beneficiary_id,
                    'bank_code' => trim($ben_details[0]->old_acc_no),
                    'bank_ifsc' => trim($ben_details[0]->old_ifsc),
                    'new_bank_code' => trim($ben_details[0]->new_acc_no),
                    'new_bank_ifsc' => trim($ben_details[0]->new_ifsc),
                    'application_id' => $ben_details[0]->beneficiary_id,
                    'mobile_no' => $ben_details[0]->mobile_no,
                    'caste' => $ben_details[0]->caste,
                ];
                //  dd($ben_arr);
                $response = array_merge($ben_arr, [
                    'status' => 2,
                    // 'pay_mode' => $pay_mode
                ]);
            }
        } catch (\Exception $e) {
            //    dd($e);
            $response = [
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' =>
                //     'Something went wrong. May be session time out logout and login again.',
            ];
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function updateDuplicateBankApprover(Request $request)
    {
        //   dd($request->all());
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }
        $is_bulk = $request->is_bulk;
        $accept_reject_comments = $request->accept_reject_comments;
        $opreation_type = $request->opreation_type;
        $applicant_id = $request->applicantId;
        //   dd($applicant_id);

        if ($is_bulk == 0) {
            $single_app_id = $request->single_app_id;
            // dd($single_app_id);
            if ($opreation_type == 'A') {
                try {
                    $scheme_id = $this->scheme_id;
                    $user_id = Auth::user()->id;
                    $designation_id = Auth::user()->designation_id;
                    $errormsg = Config::get('constants.errormsg');
                    $roleArray = $request->session()->get('role');
                    foreach ($roleArray as $roleObj) {
                        if ($roleObj['scheme_id'] == $scheme_id) {
                            $is_active = 1;
                            $is_urban = $roleObj['is_urban'];
                            $district_code = $roleObj['district_code'];
                            $mapping_level = $roleObj['mapping_level'];
                            if ($roleObj['is_urban'] == 1) {
                                $urban_body_code = $roleObj['urban_body_code'];
                            } else {
                                $urban_body_code = $roleObj['taluka_code'];
                            }
                            break;
                        }
                    }
                    if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
                        $is_active = 1;
                    } else {
                        $is_active = 0;
                    }
                    if ($is_active == 0 || empty($district_code)) {
                        return redirect("/")->with('error', 'User Disabled. ');
                    }

                    $query = '';
                    $query = "SELECT dist_code, application_id, faulty_status, ben_id AS beneficiary_id, ben_name AS name, last_accno AS old_acc_no, last_ifsc AS old_ifsc, new_last_accno AS new_acc_no, new_last_ifsc AS new_ifsc, b.block_name,ben_status,faulty_status,rejected_cause,comments FROM lb_main.ben_payment_details_bank_code_dup lb
                    LEFT JOIN (
                        SELECT block_code, block_name FROM public.m_block
                        UNION ALL
                        SELECT sub_district_code AS block_code, sub_district_name AS block_name FROM public.m_sub_district
                    )b ON b.block_code = lb.block_ulb_code
                    WHERE is_approved = 0 AND ben_id = " . $single_app_id . " ";
                    $ben_details = DB::connection('pgsql_payment')->select($query);
                    //   dd($ben_details[0]->ben_status);
                    // if ($single_app_id == 208593435) {
                    //     dd($ben_details);
                    // }
                      
                    if ($ben_details == null) {
                        return $response = [
                            'status' => 1,
                            'msg' => 'Somethimg went wrong.',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    } else {
                        DB::connection('pgsql_appwrite')->beginTransaction();
                        DB::connection('pgsql_encwrite')->beginTransaction();
                        DB::connection('pgsql_payment')->beginTransaction();
                        $getModelFunc = new getModelFunc();
                        $schemaname = $getModelFunc->getSchemaDetails();
                        // dd($schemaname);
                        $PfImageModel = new DataSourceCommon;
                        $EncloserModel = new DataSourceCommon;
                        $BankModel = new DataSourceCommon;
                        $ProfileModel = new DataSourceCommon;
                        
                        $modelfailedpayments = new DataSourceCommon;
                        $modelmainArch = new DataSourceCommon;
                        $modelfailedpayments1 = new DataSourceCommon;
                        
                        
                        $personal_model = new DataSourceCommon;
                        $Table = $getModelFunc->getTable($district_code, $this->source_type, 4);
                        $personal_model->setConnection('pgsql_appwrite');
                        $personal_model->setTable('' . $Table);
                        // dump($personal_model);


                        $personal_model_f = new DataSourceCommon;
                        $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);
                        $personal_model_f->setConnection('pgsql_appwrite');
                        $personal_model_f->setTable('' . $TableFaulty);
                        // dump($personal_model_f); die;


                        $modelNameAcceptReject = new DataSourceCommon;
                        $TableAcceptReject = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
                        $modelNameAcceptReject->setTable('' . $TableAcceptReject);

                        // $modelfailedpayments->setConnection('pgsql_payment');
                        // $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');

                        $modelmainArch->setConnection('pgsql_appwrite');
                        $modelmainArch->setTable('lb_scheme.update_ben_details');
                        $modelfailedpayments1->setConnection('pgsql_payment');
                        $modelfailedpayments1->setTable('lb_main.ben_payment_details_bank_code_dup');
                        $last_accno = trim($ben_details[0]->old_acc_no);
                        $rejected_cause = $ben_details[0]->rejected_cause;
                        // dd($rejected_cause);
                        $comments = $ben_details[0]->comments;
                        if (empty($comments)) {
                            $comments = NULL;
                        }
                        $application_id = $ben_details[0]->application_id;
                        $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';
                        if ($ben_details[0]->faulty_status == true) {
                            $row_count = $personal_model_f->whereraw("trim(bank_code)='$last_accno'")->where('beneficiary_id', $single_app_id)->where('created_by_dist_code', $district_code)->count();
                        } else if ($ben_details[0]->faulty_status == false) {
                            $row_count = $personal_model->whereraw("trim(bank_code)='$last_accno'")->where('beneficiary_id', $single_app_id)->where('created_by_dist_code', $district_code)->count();
                        }
                        // dd($row_count);
                        // if ($single_app_id == 208593435) {
                        //     dd($row_count);
                        // }
                        if ($row_count == 0) {
                            return redirect("/dedupBankListView")->with('error', ' Application Id Not found in Db..!');
                        }

                        $updateBankTable = [];
                        $updateBankTable['action_by'] = Auth::user()->id;
                        $updateBankTable['action_ip_address'] = request()->ip();
                        $updateBankTable['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        $updatePaymentTable = [];
                        $input = ['next_level_role_id' => -100, 'rejected_cause' => $rejected_cause, 'comments' => $comments];

                        $is_reject_enabled = 0;
                        if ($ben_details[0]->ben_status == 101) {

                            $getIfscDetails = "SELECT bank, branch FROM ifsc.bank_details WHERE ifsc = '" . $ben_details[0]->new_ifsc . "' ";
                            $ifsc_details = DB::connection('pgsql_appwrite')->select($getIfscDetails);

                            if (empty($ifsc_details)) {
                                return $response = [
                                    'status' => 1,
                                    'msg' => 'IFSC does not exist.',
                                    'type' => 'red',
                                    'icon' => 'fa fa-warning',
                                    'title' => 'Warning!!',
                                ];
                            }

                            $updateBankTable['bank_code'] = trim($ben_details[0]->new_acc_no);
                            $updateBankTable['updated_at'] = date("Y-m-d");
                            $updateBankTable['bank_ifsc'] = trim($ben_details[0]->new_ifsc);
                            $updateBankTable['bank_name'] = trim($ifsc_details[0]->bank);
                            $updateBankTable['branch_name'] = trim($ifsc_details[0]->branch);
                            $updateBankTable['created_by_level'] = $mapping_level;
                            $updateBankTable['created_by'] = $user_id;
                            $updateBankTable['ip_address'] = $request->ip();
                            $updateBankTable['is_dup'] = 0;

                            $updatePaymentTable['last_accno'] = trim($ben_details[0]->new_acc_no);
                            $updatePaymentTable['last_ifsc'] = trim($ben_details[0]->new_ifsc);
                            $updatePaymentTable['ben_status'] = 1;
                            $updatePaymentTable['acc_validated'] = 0;
                            // dd($updatePaymentTable);
                        }
                        if ($ben_details[0]->ben_status == 200) {
                            // dd($ben_details[0]->ben_status);
                            
                            $updateBankTable['created_by_level'] = $mapping_level;
                            $updateBankTable['updated_at'] = date("Y-m-d");
                            $updateBankTable['created_by'] = $user_id;
                            $updateBankTable['ip_address'] = $request->ip();
                            $updateBankTable['is_dup'] = 0;

                            $updatePaymentTable['ben_status'] = 1;
                            $updatePaymentTable['acc_validated'] = 0;
                        }

                        if ($ben_details[0]->ben_status == -98) {
                            $is_reject_enabled = 1;
                            $updateBankTable['updated_at'] = date("Y-m-d");
                            $updateBankTable['created_by_level'] = $mapping_level;
                            $updateBankTable['created_by'] = $user_id;
                            $updateBankTable['ip_address'] = $request->ip();
                            $updateBankTable['is_dup'] = 0;

                            $updatePaymentTable['ben_status'] = -98;
                            $updatePaymentTable['acc_validated'] = 0;
                            // dd($updateBankTable);
                            // if ($single_app_id == 208593435) {
                            //     dump($updateBankTable); dump($updatePaymentTable); die;
                            // }
                            
                        }
                        


                        $dupTable = [];
                        $dupTable['is_approved'] = 1;
                        $dupTable['revert_remarks'] = $accept_reject_comments;

                        $modelNameAcceptReject->op_type =  'DupBankUpdateApprove';
                        $modelNameAcceptReject->updated_at = date("Y-m-d");
                        $modelNameAcceptReject->application_id = $ben_details[0]->application_id;
                        $modelNameAcceptReject->designation_id = $designation_id;
                        $modelNameAcceptReject->scheme_id = $scheme_id;
                        $modelNameAcceptReject->mapping_level = $mapping_level;
                        $modelNameAcceptReject->created_by = $user_id;
                        $modelNameAcceptReject->created_by_level = trim($mapping_level);
                        $modelNameAcceptReject->created_by_dist_code = $district_code;
                        $modelNameAcceptReject->created_by_local_body_code = NULL;
                        $modelNameAcceptReject->ip_address = request()->ip();
                        $is_accept_reject = $modelNameAcceptReject->save();

                        if ($ben_details[0]->faulty_status == true) {
                        //      if($single_app_id == 600874470){
                        //     dump($updateBankTable);dd($district_code);
                        //  }
                            $updateBankDetails = DB::connection('pgsql_appwrite')->table('lb_scheme.faulty_ben_bank_details')->where('beneficiary_id', $single_app_id)->where('created_by_dist_code', $district_code)->update($updateBankTable);
                        } else {
                            $updateBankDetails = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_bank_details')->where('beneficiary_id', $single_app_id)->where('created_by_dist_code', $district_code)->update($updateBankTable);
                        }

                        $updatePaymentDetails = DB::connection('pgsql_payment')->table('payment.ben_payment_details')->where('ben_id', $single_app_id)->where('dist_code', $district_code)->update($updatePaymentTable);

                        $updateDupTable = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')->where('ben_id', $single_app_id)->where('dist_code', $district_code)->update($dupTable);
                        
                        if ($is_reject_enabled == 1) {
                            if ($ben_details[0]->faulty_status == true) {
                                //  $is_status_updated = $personal_model_f->where('beneficiary_id', $single_app_id)->update($input);
                                //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.beneficiary_rejected_faulty_final_dup_bank(" . $in_pension_id . "," . $rejected_cause . ",'" . $comments . "')");
                                $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.beneficiary_rejected_faulty_final_dup_bank(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."',in_rejected_cause => ".$rejected_cause.",in_comment_message => '".$comments."')");

                                $is_reject_fun = $reject_fun[0]->beneficiary_rejected_faulty_final_dup_bank;
                            } else if ($ben_details[0]->faulty_status == false) {
                                //    $is_status_updated = $personal_model->where('beneficiary_id', $single_app_id)->update($input);
                               // $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.beneficiary_rejected_final_dup_bank(" . $in_pension_id . "," . $rejected_cause . ",'" . $comments . "')");
                               $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.beneficiary_rejected_final_dup_bank(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."',in_rejected_cause => ".$rejected_cause.",in_comment_message => '".$comments."')");

                                $is_reject_fun = $reject_fun[0]->beneficiary_rejected_final_dup_bank;
                            }
                            
                        } else {
                            $is_reject_fun = 1;
                        }
                        
                        

                        // dump($updateBankDetails); dump($updatePaymentDetails); dump($is_accept_reject); dump($updateDupTable);die;
                        // if ($single_app_id == 601059087) {
                        //     dump($updateBankDetails); dump($updatePaymentDetails); dump($is_accept_reject); dump($updateDupTable); dump($is_reject_fun);die;
                        // }
                        // if($single_app_id == 600874470){
                        //     dump($updateBankDetails);dump($updatePaymentDetails);dump($is_accept_reject);dump($updateDupTable);dd($is_reject_fun);
                        // }
                        if ($updateBankDetails && $updatePaymentDetails && $is_accept_reject && $updateDupTable && $is_reject_fun) {
                            // dd('success');
                            DB::connection('pgsql_appwrite')->commit();
                            DB::connection('pgsql_encwrite')->commit();
                            DB::connection('pgsql_payment')->commit();
                            return $response = [
                                'status' => 1,
                                'msg' => 'Bank details updated successfully',
                                'type' => 'green',
                                'icon' => 'fa fa-check',
                                'title' => 'Success',
                            ];
                        } else {
                            // dd('fail');
                            DB::connection('pgsql_appwrite')->rollback();
                            DB::connection('pgsql_encwrite')->rollback();
                            DB::connection('pgsql_payment')->rollback();
                            return $response = [
                                'status' => 1,
                                'msg' => 'Something went wrong',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    dd($e);
                    DB::connection('pgsql_appwrite')->rollback();
                    DB::connection('pgsql_encwrite')->rollback();
                    DB::connection('pgsql_payment')->rollback();
                    $response = [
                        'exception' => true,
                        'exception_message' => $e->getMessage(),
                        // 'exception_message' =>
                        //     'Something went wrong. May be session time out logout and login again.',
                    ];
                    $statusCode = 400;
                } finally {
                    return response()->json($response, $statusCode);
                }
            } elseif ($opreation_type == 'T') {
                try {
                    $scheme_id = $this->scheme_id;
                    $user_id = Auth::user()->id;
                    $designation_id = Auth::user()->designation_id;
                    $errormsg = Config::get('constants.errormsg');
                    $roleArray = $request->session()->get('role');
                    foreach ($roleArray as $roleObj) {
                        if ($roleObj['scheme_id'] == $scheme_id) {
                            $is_active = 1;
                            $is_urban = $roleObj['is_urban'];
                            $district_code = $roleObj['district_code'];
                            $mapping_level = $roleObj['mapping_level'];
                            if ($roleObj['is_urban'] == 1) {
                                $urban_body_code = $roleObj['urban_body_code'];
                            } else {
                                $urban_body_code = $roleObj['taluka_code'];
                            }
                            break;
                        }
                    }
                    if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
                        $is_active = 1;
                    } else {
                        $is_active = 0;
                    }
                    if ($is_active == 0 || empty($district_code)) {
                        return redirect("/")->with('error', 'User Disabled. ');
                    }
                    $query = '';
                    $query = "SELECT dist_code, application_id, faulty_status, ben_id AS beneficiary_id, ben_name AS name, last_accno AS old_acc_no, last_ifsc AS old_ifsc, new_last_accno AS new_acc_no, new_last_ifsc AS new_ifsc, b.block_name FROM lb_main.ben_payment_details_bank_code_dup lb
                LEFT JOIN (
                    SELECT block_code, block_name FROM public.m_block
                    UNION ALL
                    SELECT sub_district_code AS block_code, sub_district_name AS block_name FROM public.m_sub_district
                )b ON b.block_code = lb.block_ulb_code
                WHERE is_approved = 0 AND ben_id = " . $single_app_id . " ";
                    $ben_details = DB::connection('pgsql_payment')->select($query);
                    // $getIfscDetails = "SELECT bank, branch FROM ifsc.bank_details WHERE ifsc = '" . $ben_details[0]->new_ifsc . "' ";
                    // $ifsc_details = DB::connection('pgsql_appwrite')->select($getIfscDetails);
                    // // dd($ifsc_details);
                    // if ($ifsc_details == null) {
                    //     return $response = [
                    //         'status' => 1,
                    //         'msg' => 'IFSC does not exist.',
                    //         'type' => 'red',
                    //         'icon' => 'fa fa-warning',
                    //         'title' => 'Warning!!',
                    //     ];
                    // }
                    if ($ben_details == null) {
                        return $response = [
                            'status' => 1,
                            'msg' => 'Somethimg went wrong.',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    } else {
                        DB::connection('pgsql_appwrite')->beginTransaction();
                        DB::connection('pgsql_encwrite')->beginTransaction();
                        DB::connection('pgsql_payment')->beginTransaction();
                        $getModelFunc = new getModelFunc();
                        $schemaname = $getModelFunc->getSchemaDetails();
                        $PfImageModel = new DataSourceCommon;
                        $EncloserModel = new DataSourceCommon;
                        $BankModel = new DataSourceCommon;
                        $ProfileModel = new DataSourceCommon;
                        $modelNameAcceptReject = new DataSourceCommon;
                        $modelfailedpayments = new DataSourceCommon;
                        $modelmainArch = new DataSourceCommon;
                        $modelfailedpayments1 = new DataSourceCommon;
                        $Table = $getModelFunc->getTable($district_code, $this->source_type, 4);
                        $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);
                        $personal_model = new DataSourceCommon;
                        $personal_model->setTable('' . $Table);
                        // dd($personal_model);
                        $personal_model_f = new DataSourceCommon;
                        $personal_model_f->setTable('' . $TableFaulty);

                        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
                        $modelNameAcceptReject->setTable('' . $Table);

                        $modelfailedpayments->setConnection('pgsql_payment');
                        $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
                        $modelmainArch->setConnection('pgsql_appwrite');
                        $modelmainArch->setTable('lb_scheme.update_ben_details');
                        $modelfailedpayments1->setConnection('pgsql_payment');
                        $modelfailedpayments1->setTable('lb_main.ben_payment_details_bank_code_dup');

                        $dupTable = [];
                        $dupTable['ben_status'] = -97;
                        $dupTable['revert_remarks'] = $accept_reject_comments;
                        $dupTable['is_approved'] = 2;

                        // Insert ben accept reject info table
                        $modelNameAcceptReject->op_type =  'DupBankRevertApprove';
                        $modelNameAcceptReject->application_id = $ben_details[0]->application_id;
                        $modelNameAcceptReject->designation_id = $designation_id;
                        $modelNameAcceptReject->scheme_id = $scheme_id;
                        $modelNameAcceptReject->mapping_level = $mapping_level;
                        $modelNameAcceptReject->created_by = $user_id;
                        $modelNameAcceptReject->created_by_level = trim($mapping_level);
                        $modelNameAcceptReject->created_by_dist_code = $district_code;
                        $modelNameAcceptReject->created_by_local_body_code = NULL;
                        $modelNameAcceptReject->ip_address = request()->ip();
                        $is_accept_reject = $modelNameAcceptReject->save();

                        $updateDupTable = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')->where('ben_id', $single_app_id)->where('dist_code', $district_code)->update($dupTable);

                        if ($is_accept_reject && $updateDupTable) {
                            // dd('success');
                            DB::connection('pgsql_appwrite')->commit();
                            DB::connection('pgsql_encwrite')->commit();
                            DB::connection('pgsql_payment')->commit();
                            return $response = [
                                'status' => 1,
                                'msg' => 'Bank details reverted successfully',
                                'type' => 'green',
                                'icon' => 'fa fa-check',
                                'title' => 'Success',
                            ];
                        } else {
                            // dd('fail');
                            DB::connection('pgsql_appwrite')->rollback();
                            DB::connection('pgsql_encwrite')->rollback();
                            DB::connection('pgsql_payment')->rollback();
                            return $response = [
                                'status' => 1,
                                'msg' => 'Something went wrong',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // dd($e);
                    DB::connection('pgsql_appwrite')->rollback();
                    DB::connection('pgsql_encwrite')->rollback();
                    DB::connection('pgsql_payment')->rollback();
                    $response = [
                        'exception' => true,
                        'exception_message' => $e->getMessage(),
                        // 'exception_message' =>
                        //     'Something went wrong. May be session time out logout and login again.',
                    ];
                    $statusCode = 400;
                } finally {
                    return response()->json($response, $statusCode);
                }
            }
        }
        if ($is_bulk == 1) {
            if ($opreation_type == 'A') {
                $bulk_id_arr = explode(',', $applicant_id);
                try {
                    DB::connection('pgsql_appwrite')->beginTransaction();
                    DB::connection('pgsql_encwrite')->beginTransaction();
                    DB::connection('pgsql_payment')->beginTransaction();
                    $scheme_id = $this->scheme_id;
                    $user_id = Auth::user()->id;
                    $designation_id = Auth::user()->designation_id;
                    $errormsg = Config::get('constants.errormsg');
                    $roleArray = $request->session()->get('role');
                    foreach ($roleArray as $roleObj) {
                        if ($roleObj['scheme_id'] == $scheme_id) {
                            $is_active = 1;
                            $is_urban = $roleObj['is_urban'];
                            $district_code = $roleObj['district_code'];
                            $mapping_level = $roleObj['mapping_level'];
                            if ($roleObj['is_urban'] == 1) {
                                $urban_body_code = $roleObj['urban_body_code'];
                            } else {
                                $urban_body_code = $roleObj['taluka_code'];
                            }
                            break;
                        }
                    }
                    if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
                        $is_active = 1;
                    } else {
                        $is_active = 0;
                    }
                    if ($is_active == 0 || empty($district_code)) {
                        return redirect("/")->with('error', 'User Disabled. ');
                    }
                    $loopcount = 0;
                    $updatecount = 0;
                    foreach ($bulk_id_arr as $key => $value) {
                        $loopcount++;
                        $ip_address = request()->ip();
                        $query = '';
                        $query = "SELECT dist_code, application_id, faulty_status, ben_id AS beneficiary_id, ben_name AS name, last_accno AS old_acc_no, last_ifsc AS old_ifsc, new_last_accno AS new_acc_no, new_last_ifsc AS new_ifsc, b.block_name,ben_status,faulty_status,rejected_cause,comments FROM lb_main.ben_payment_details_bank_code_dup lb
                        LEFT JOIN (
                            SELECT block_code, block_name FROM public.m_block
                            UNION ALL
                            SELECT sub_district_code AS block_code, sub_district_name AS block_name FROM public.m_sub_district
                        )b ON b.block_code = lb.block_ulb_code
                        WHERE is_approved = 0 AND ben_id = " . $value . " ";
                        $ben_details = DB::connection('pgsql_payment')->select($query);
                        if ($ben_details == null) {
                            return $response = [
                                'status' => 1,
                                'msg' => 'Somethimg went wrong.',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        } else {

                            $getModelFunc = new getModelFunc();
                            $schemaname = $getModelFunc->getSchemaDetails();
                            $PfImageModel = new DataSourceCommon;
                            $EncloserModel = new DataSourceCommon;
                            $BankModel = new DataSourceCommon;
                            $ProfileModel = new DataSourceCommon;
                            $modelNameAcceptReject = new DataSourceCommon;
                            $modelfailedpayments = new DataSourceCommon;
                            $modelmainArch = new DataSourceCommon;
                            $modelfailedpayments1 = new DataSourceCommon;
                            $Table = $getModelFunc->getTable($district_code, $this->source_type, 4);
                            $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);
                            $personal_model = new DataSourceCommon;
                            $personal_model->setTable('' . $Table);
                            // dd($personal_model);
                            $personal_model_f = new DataSourceCommon;
                            $personal_model_f->setTable('' . $TableFaulty);
                            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
                            $modelNameAcceptReject->setTable('' . $Table);

                            $modelfailedpayments->setConnection('pgsql_payment');
                            $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
                            $modelmainArch->setConnection('pgsql_appwrite');
                            $modelmainArch->setTable('lb_scheme.update_ben_details');
                            $modelfailedpayments1->setConnection('pgsql_payment');
                            $modelfailedpayments1->setTable('lb_main.ben_payment_details_bank_code_dup');
                            $last_accno = trim($ben_details[0]->old_acc_no);
                            $rejected_cause = $ben_details[0]->rejected_cause;
                            $comments = $ben_details[0]->comments;
                            if (empty($comments)) {
                                $comments = NULL;
                            }
                            $in_pension_id = 'ARRAY[' . "'$value'" . ']';
                            if ($ben_details[0]->faulty_status == true) {
                                $row_count = $personal_model_f->whereraw("trim(bank_code)='$last_accno'")->where('beneficiary_id', $value)->where('created_by_dist_code', $district_code)->count();
                            } else if ($ben_details[0]->faulty_status == false) {
                                $row_count = $personal_model->whereraw("trim(bank_code)='$last_accno'")->where('beneficiary_id', $value)->where('created_by_dist_code', $district_code)->count();
                            }

                            if ($row_count == 0) {
                                return redirect("/dedupBankListView")->with('error', ' Application Id Not found in Db');
                            }


                            $updateBankTable = [];
                            $updateBankTable['action_by'] = Auth::user()->id;
                            $updateBankTable['action_ip_address'] = request()->ip();
                            $updateBankTable['action_type'] = class_basename(request()->route()->getAction()['controller']);
                            $updatePaymentTable = [];
                            $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'next_level_role_id' => -100, 'rejected_cause' => $rejected_cause, 'comments' => $comments];
                            $is_reject_enabled = 0;
                            if ($ben_details[0]->ben_status == 101) {
                                // dd('ok');
                                $getIfscDetails = "SELECT bank, branch FROM ifsc.bank_details WHERE ifsc = '" . $ben_details[0]->new_ifsc . "' ";
                                $ifsc_details = DB::connection('pgsql_appwrite')->select($getIfscDetails);
                                //  dd($ifsc_details);
                                if (empty($ifsc_details)) {
                                    return $response = [
                                        'status' => 1,
                                        'msg' => 'IFSC does not exist.',
                                        'type' => 'red',
                                        'icon' => 'fa fa-warning',
                                        'title' => 'Warning!!',
                                    ];
                                }

                                $updateBankTable['bank_code'] = trim($ben_details[0]->new_acc_no);

                                $updateBankTable['bank_ifsc'] = trim($ben_details[0]->new_ifsc);
                                $updateBankTable['bank_name'] = trim($ifsc_details[0]->bank);
                                $updateBankTable['branch_name'] = trim($ifsc_details[0]->branch);
                                $updateBankTable['created_by_level'] = $mapping_level;
                                $updateBankTable['created_by'] = $user_id;
                                $updateBankTable['ip_address'] = $request->ip();

                                $updatePaymentTable['last_accno'] = trim($ben_details[0]->new_acc_no);
                                $updatePaymentTable['last_ifsc'] = trim($ben_details[0]->new_ifsc);
                                $updatePaymentTable['ben_status'] = 1;
                                $updatePaymentTable['acc_validated'] = 0;
                                // dd($updatePaymentTable);
                            }
                            if ($ben_details[0]->ben_status == 200) {
                                $updateBankTable['created_by_level'] = $mapping_level;
                                $updateBankTable['created_by'] = $user_id;
                                $updateBankTable['ip_address'] = $request->ip();

                                $updatePaymentTable['ben_status'] = 1;
                                $updatePaymentTable['acc_validated'] = 0;
                            }
                            if ($ben_details[0]->ben_status == -98) {
                                $is_reject_enabled = 1;
                                $updateBankTable['created_by_level'] = $mapping_level;
                                $updateBankTable['created_by'] = $user_id;
                                $updateBankTable['ip_address'] = $request->ip();

                                $updatePaymentTable['ben_status'] = -98;
                                $updatePaymentTable['acc_validated'] = 0;
                            }

                            $dupTable = [];
                            $dupTable['is_approved'] = 1;
                            $dupTable['revert_remarks'] = $accept_reject_comments;

                            // Insert ben accept reject info table
                            $modelNameAcceptReject->op_type =  'DupBankUpdateApprove';
                            $modelNameAcceptReject->application_id = $ben_details[0]->application_id;
                            $modelNameAcceptReject->designation_id = $designation_id;
                            $modelNameAcceptReject->scheme_id = $scheme_id;
                            $modelNameAcceptReject->mapping_level = $mapping_level;
                            $modelNameAcceptReject->created_by = $user_id;
                            $modelNameAcceptReject->created_by_level = trim($mapping_level);
                            $modelNameAcceptReject->created_by_dist_code = $district_code;
                            $modelNameAcceptReject->created_by_local_body_code = NULL;
                            $modelNameAcceptReject->ip_address = request()->ip();

                            $is_accept_reject = $modelNameAcceptReject->save();

                            if ($ben_details[0]->faulty_status == true) {
                                $updateBankDetails = DB::connection('pgsql_appwrite')->table('lb_scheme.faulty_ben_bank_details')->where('beneficiary_id', $value)->where('created_by_dist_code', $district_code)->update($updateBankTable);
                            } else {
                                $updateBankDetails = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_bank_details')->where('beneficiary_id', $value)->where('created_by_dist_code', $district_code)->update($updateBankTable);
                            }

                            $updatePaymentDetails = DB::connection('pgsql_payment')->table('payment.ben_payment_details')->where('ben_id', $value)->where('dist_code', $district_code)->update($updatePaymentTable);

                            $updateDupTable = $modelfailedpayments1->where('ben_id', $value)->where('dist_code', $district_code)->update($dupTable);

                            if ($is_reject_enabled == 1) {
                                if ($ben_details[0]->faulty_status == true) {
                                    $is_status_updated = $personal_model_f->where('beneficiary_id', $value)->update($input);
                                   // $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_faulty_final_dup_bank(" . $in_pension_id . "," . $rejected_cause . ",'" . $comments . "')");
                                   $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_faulty_final_dup_bank(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                                    $is_reject_fun = $reject_fun[0]->beneficiary_rejected_faulty_final_dup_bank;
                                } else if ($ben_details[0]->faulty_status == false) {
                                    $is_status_updated = $personal_model->where('beneficiary_id', $value)->update($input);
                                    //$reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_dup_bank(" . $in_pension_id . "," . $rejected_cause . ",'" . $comments . "')");
                                    $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_faulty_final_dup_bank(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                                    $is_reject_fun = $reject_fun[0]->beneficiary_rejected_faulty_final_dup_bank;
                                }
                            } else {
                                $is_reject_fun = 1;
                            }

                            if ($updateBankDetails && $updatePaymentDetails && $is_accept_reject && $updateDupTable && $is_reject_fun) {
                                $updatecount++;
                            }
                        }
                    }
                    if ($loopcount == $updatecount) {
                        DB::connection('pgsql_appwrite')->commit();
                        DB::connection('pgsql_encwrite')->commit();
                        DB::connection('pgsql_payment')->commit();
                        $response = [
                            'status' => 1,
                            'msg' => 'Bank details updated successfully',
                            'type' => 'green',
                            'icon' => 'fa fa-check',
                            'title' => 'Success',
                        ];
                    } else {
                        DB::connection('pgsql_appwrite')->rollback();
                        DB::connection('pgsql_encwrite')->rollback();
                        DB::connection('pgsql_payment')->rollback();
                        $response = [
                            'status' => 1,
                            'msg' => 'Something went wrong',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    }
                } catch (\Exception $e) {
                    // dd($e);
                    DB::connection('pgsql_appwrite')->rollback();
                    DB::connection('pgsql_encwrite')->rollback();
                    DB::connection('pgsql_payment')->rollback();
                    $response = [
                        'exception' => true,
                        'exception_message' => $e->getMessage(),
                        // 'exception_message' =>
                        //     'Something went wrong. May be session time out logout and login again.',
                    ];
                    $statusCode = 400;
                } finally {
                    return response()->json($response, $statusCode);
                }
            } elseif ($opreation_type == 'T') {
                $bulk_id_arr = explode(',', $applicant_id);
                // dd($bulk_id_arr);
                try {
                    DB::connection('pgsql_appwrite')->beginTransaction();
                    DB::connection('pgsql_encwrite')->beginTransaction();
                    DB::connection('pgsql_payment')->beginTransaction();
                    $scheme_id = $this->scheme_id;
                    $user_id = Auth::user()->id;
                    $designation_id = Auth::user()->designation_id;
                    $errormsg = Config::get('constants.errormsg');
                    $roleArray = $request->session()->get('role');
                    foreach ($roleArray as $roleObj) {
                        if ($roleObj['scheme_id'] == $scheme_id) {
                            $is_active = 1;
                            $is_urban = $roleObj['is_urban'];
                            $district_code = $roleObj['district_code'];
                            $mapping_level = $roleObj['mapping_level'];
                            if ($roleObj['is_urban'] == 1) {
                                $urban_body_code = $roleObj['urban_body_code'];
                            } else {
                                $urban_body_code = $roleObj['taluka_code'];
                            }
                            break;
                        }
                    }
                    if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
                        $is_active = 1;
                    } else {
                        $is_active = 0;
                    }
                    if ($is_active == 0 || empty($district_code)) {
                        return redirect("/")->with('error', 'User Disabled. ');
                    }
                    $loopCount = 0;
                    $updateCount = 0;

                    foreach ($bulk_id_arr as $key => $value) {
                        $loopCount++;
                        $ip_address = request()->ip();
                        $query = '';
                        $query = "SELECT dist_code, application_id, faulty_status, ben_id AS beneficiary_id, ben_name AS name, last_accno AS old_acc_no, last_ifsc AS old_ifsc, new_last_accno AS new_acc_no, new_last_ifsc AS new_ifsc, b.block_name,ben_status,faulty_status,rejected_cause,comments FROM lb_main.ben_payment_details_bank_code_dup lb
                        LEFT JOIN (
                            SELECT block_code, block_name FROM public.m_block
                            UNION ALL
                            SELECT sub_district_code AS block_code, sub_district_name AS block_name FROM public.m_sub_district
                        )b ON b.block_code = lb.block_ulb_code
                        WHERE is_approved = 0 AND ben_id = " . $value . " ";
                        $ben_details = DB::connection('pgsql_payment')->select($query);
                        $getIfscDetails = "SELECT bank, branch FROM ifsc.bank_details WHERE ifsc = '" . $ben_details[0]->new_ifsc . "' ";
                        $ifsc_details = DB::connection('pgsql_appwrite')->select($getIfscDetails);
                        // dd($ifsc_details);
                        if ($ifsc_details == null) {
                            return $response = [
                                'status' => 1,
                                'msg' => 'IFSC does not exist.',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        }
                        if ($ben_details == null) {
                            return $response = [
                                'status' => 1,
                                'msg' => 'Somethimg went wrong.',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        } else {
                            $getModelFunc = new getModelFunc();
                            $schemaname = $getModelFunc->getSchemaDetails();
                            $PfImageModel = new DataSourceCommon;
                            $EncloserModel = new DataSourceCommon;
                            $BankModel = new DataSourceCommon;
                            $ProfileModel = new DataSourceCommon;
                            $modelNameAcceptReject = new DataSourceCommon;
                            $modelfailedpayments = new DataSourceCommon;
                            $modelmainArch = new DataSourceCommon;
                            $modelfailedpayments1 = new DataSourceCommon;

                            $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 9);
                            $modelNameAcceptReject->setTable('' . $Table);

                            $modelfailedpayments->setConnection('pgsql_payment');
                            $modelfailedpayments->setTable('' . $schemaname . '.ben_payment_details');
                            $modelmainArch->setConnection('pgsql_appwrite');
                            $modelmainArch->setTable('lb_scheme.update_ben_details');
                            $modelfailedpayments1->setConnection('pgsql_payment');
                            $modelfailedpayments1->setTable('lb_main.ben_payment_details_bank_code_dup');

                            $dupTable = [];
                            $dupTable['ben_status'] = -97;
                            $dupTable['revert_remarks'] = $accept_reject_comments;
                            $dupTable['is_approved'] = 2;
                            // Insert ben accept reject info table
                            $modelNameAcceptReject->op_type =  'DupBankUpdateApprove';
                            $modelNameAcceptReject->application_id = $ben_details[0]->application_id;
                            $modelNameAcceptReject->designation_id = $designation_id;
                            $modelNameAcceptReject->scheme_id = $scheme_id;
                            $modelNameAcceptReject->mapping_level = $mapping_level;
                            $modelNameAcceptReject->created_by = $user_id;
                            $modelNameAcceptReject->created_by_level = trim($mapping_level);
                            $modelNameAcceptReject->created_by_dist_code = $district_code;
                            $modelNameAcceptReject->created_by_local_body_code = NULL;
                            $modelNameAcceptReject->ip_address = request()->ip();

                            $is_accept_reject = $modelNameAcceptReject->save();


                            $updateDupTable = $modelfailedpayments1->where('ben_id', $value)->where('dist_code', $district_code)->update($dupTable);
                            if ($is_accept_reject && $updateDupTable) {
                                // dd('success');
                                $updateCount++;
                            }
                        }
                    }
                    if ($loopCount == $updateCount) {
                        DB::connection('pgsql_appwrite')->commit();
                        DB::connection('pgsql_encwrite')->commit();
                        DB::connection('pgsql_payment')->commit();
                        return $response = [
                            'status' => 1,
                            'msg' => 'Bank details reverted successfully',
                            'type' => 'green',
                            'icon' => 'fa fa-check',
                            'title' => 'Success',
                        ];
                    } else {
                        DB::connection('pgsql_appwrite')->rollback();
                        DB::connection('pgsql_encwrite')->rollback();
                        DB::connection('pgsql_payment')->rollback();
                        return $response = [
                            'status' => 1,
                            'msg' => 'Something went wrong',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    }
                } catch (\Exception $e) {
                    // dd($e);
                    DB::connection('pgsql_appwrite')->rollback();
                    DB::connection('pgsql_encwrite')->rollback();
                    DB::connection('pgsql_payment')->rollback();
                    $response = [
                        'exception' => true,
                        'exception_message' => $e->getMessage(),
                        // 'exception_message' =>
                        //     'Something went wrong. May be session time out logout and login again.',
                    ];
                    $statusCode = 400;
                } finally {
                    return response()->json($response, $statusCode);
                }
            }
        }
    }

    public function deDuplicateBankList(Request $request)
    {
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
        } else if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
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
        $reactive_reasons = DB::table('jnmp.reactive_reason')->get();
        // dd($reactive_reason);
        return view(
            'DuplicateBank.dedupBankBenList',
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
                'reactive_reasons' => $reactive_reasons
            ]
        );
    }

    public function getDeduplicationList(Request $request)
    {
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
        } else if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
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
        $filter = $request->search_for;
        $block = $request->block_ulb_code;
        $rural_urban = $request->rural_urban;
        $gp_ward = $request->gp_ward_code;
        $muncid = $request->muncid;
        if ($request->ajax()) {
            if ($filter == 1) {
                $whereCon = " WHERE ben_status IN(101, 200, -98, -99) AND is_approved = 0";
            } elseif ($filter == 2) {
                $whereCon = " WHERE ben_status = -97 AND is_approved = 0";
            } elseif ($filter == 3) {
                $whereCon = " WHERE ben_status = -98 AND is_approved = 1";
            } else {
                $whereCon = " WHERE ben_status IN(101, 200, -98, -99) AND is_approved = 1";
            }
            $query = $this->getQueryResult($district_code, $blockCode, $block, $gp_ward, $muncid, $rural_urban, $whereCon);
            // dd($query);
            $result = DB::connection('pgsql_payment')->select($query);
            return datatables()->of($result)
                ->addColumn('status', function ($result) {
                    $status = '';
                    if ($result->ben_status == -97 && $result->is_approved == 0) {
                        $status .= '<span class="text-warning" style="font-weight: bold;">Pending</span>';
                    } elseif ($result->ben_status == 101 && $result->is_approved == 0) {
                        $status .= '<span class="text-primary" style="font-weight: bold;">Process with Different Account No.</span>';
                    } elseif ($result->ben_status == 200 && $result->is_approved == 0) {
                        $status .= '<span class="text-primary" style="font-weight: bold;">Process with Keep Same</span>';
                    } elseif (($result->ben_status == -98 || $result->ben_status == -99) && $result->is_approved == 0) {
                        $status .= '<span class="text-primary" style="font-weight: bold;">Process with Reject</span>';
                    } elseif (($result->ben_status == -98 || $result->ben_status == -99) && $result->is_approved == 1) {
                        $status .= '<span class="text-danger" style="font-weight: bold;">Rejected</span>';
                    } elseif ($result->ben_status == 101 && $result->is_approved == 1) {
                        $status .= '<span class="text-success" style="font-weight: bold;">Approved with Different Account No.</span>';
                    } elseif ($result->ben_status == 200 && $result->is_approved == 1) {
                        $status .= '<span class="text-success" style="font-weight: bold;">Approved with Keep Same</span>';
                    } else {
                        $status .= '';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
    }

    public function getBankDeduplicationListexcel(Request $request)
    {
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
        } else if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
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
        $filter = $request->search_for;
        $block = $request->block_ulb_code;
        $rural_urban = $request->rural_urban;
        $gp_ward = $request->gp_ward_code;
        $muncid = $request->muncid;
        $schemeObj = 'Lakshmir Bhandar';
        $user_msg = 'Bank De-Duplication Pending Beneficiary List';

        if ($filter == 1) {
            $whereCon = " WHERE ben_status IN(101, 200, -98, -99) AND is_approved = 0";
        } elseif ($filter == 2) {
            $whereCon = " WHERE ben_status = -97 AND is_approved = 0";
        } elseif ($filter == 3) {
            $whereCon = " WHERE ben_status = -98 AND is_approved = 1";
        } else {
            $whereCon = " WHERE ben_status IN(101, 200, -98, -99) AND is_approved = 1";
        }
        $query = $this->getQueryResult($district_code, $blockCode, $block, $gp_ward, $muncid, $rural_urban, $whereCon);
        $result = DB::connection('pgsql_payment')->select($query);

        $excelarr[] = array(
            'Beneficiary ID', 'Beneficiary Name', 'Block/Municipality', 'GP/Ward', 'Old Account No.', 'Old IFSC', 'New Account No.', 'New IFSC', 'Mobile Number', 'Status'
        );
        foreach ($result as $arr) {
            $status = '';
            if ($arr->ben_status == -97 && $arr->is_approved == 0) {
                $status .= 'Pending';
            } elseif ($arr->ben_status == 101 && $arr->is_approved == 0) {
                $status .= 'Process with Different Account No.';
            } elseif ($arr->ben_status == 200 && $arr->is_approved == 0) {
                $status .= 'Process with Keep Same';
            } elseif (($arr->ben_status == -98 || $arr->ben_status == -99) && $arr->is_approved == 0) {
                $status .= 'Process with Reject';
            } elseif (($arr->ben_status == -98 || $arr->ben_status == -99) && $arr->is_approved == 1) {
                $status .= 'Rejected';
            } elseif ($arr->ben_status == 101 && $arr->is_approved == 1) {
                $status .= 'Approved with Different Account No.';
            } elseif ($arr->ben_status == 200 && $arr->is_approved == 1) {
                $status .= 'Approved with Keep Same';
            } else {
                $status .= '';
            }
            $excelarr[] = array(
                'Beneficiary ID' => trim($arr->ben_id),
                'Beneficiary Name' => trim($arr->ben_name),
                'Block/Municipality' => trim($arr->block_name),
                'GP/Ward' => trim($arr->gram_panchyat_name),
                'Old Account No.' => trim($arr->last_accno),
                'Old IFSC' => trim($arr->last_ifsc),
                'New Account No.' => trim($arr->new_last_accno),
                'New IFSC' => trim($arr->new_last_ifsc),
                'Mobile Number' => trim($arr->mobile_no),
                'Status' => $status,
            );
        }
        $file_name = $schemeObj . ' ' . $user_msg . ' ' .  date('d/m/Y');
        Excel::create($file_name, function ($excel) use ($excelarr) {
            $excel->setTitle('Jai Bangla Duplicate Report');
            $excel->sheet('Jai Bangla Duplicate Report', function ($sheet) use ($excelarr) {
                $sheet->fromArray($excelarr, null, 'A1', false, false);
            });
        })->download('xlsx');
    }
    private function getQueryResult($district_code, $blockCode, $block, $gp_ward, $muncid, $rural_urban, $whereCon)
    {
        $query = "SELECT d.ben_id, d.ben_name, b.block_name, g.gram_panchyat_name, d.new_last_accno, d.new_last_ifsc, d.last_accno, d.last_ifsc, d.mobile_no, ben_status, is_approved
                    FROM lb_main.ben_payment_details_bank_code_dup d 
                    LEFT JOIN 
                    (
                        SELECT block_code, block_name FROM public.m_block
                        UNION ALL
                        SELECT urban_body_code AS block_code, urban_body_name AS block_name FROM public.m_urban_body
                    ) b ON d.block_ulb_code = b.block_code
                    LEFT JOIN
                    (
                        SELECT gram_panchyat_code, gram_panchyat_name FROM public.m_gp
                        UNION ALL
                        SELECT urban_body_ward_code AS gram_panchyat_code, urban_body_ward_name AS gram_panchyat_name FROM public.m_urban_body_ward
                    ) g ON g.gram_panchyat_code = d.gp_ward_code" . $whereCon;
        if (!empty($rural_urban)) {
            $query .= " AND rural_urban_id = " . $rural_urban;
        }
        if (!empty($district_code)) {
            $query .= " AND dist_code = " . $district_code;
        }
        if (!empty($blockCode)) {
            $query .= " AND block_ulb_code = " . $blockCode;
        }
        if (!empty($gp_ward)) {
            $query .= " AND gp_ward_code = " . $gp_ward;
        }
        $query .= " ORDER BY d.last_accno";
        return $query;
    }
}
