<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\District;
use App\Models\Scheme;
use Redirect;
use Auth;
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
use App\Models\UrbanBody;
use App\Models\GP;
use App\Models\DocumentType;
use App\Models\DsPhase;
use Carbon\Carbon;
use App\Helpers\DupCheck;
class DuplicateController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
    public function isAadharValid($num)
    {
        settype($num, "string");
        $expectedDigit = substr($num, -1);
        $actualDigit = $this->CheckSumAadharDigit(substr($num, 0, -1));
        return ($expectedDigit == $actualDigit) ? $expectedDigit == $actualDigit : 0;
    }

    function CheckSumAadharDigit($partial)
    {
        $dihedral = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
            array(1, 2, 3, 4, 0, 6, 7, 8, 9, 5),
            array(2, 3, 4, 0, 1, 7, 8, 9, 5, 6),
            array(3, 4, 0, 1, 2, 8, 9, 5, 6, 7),
            array(4, 0, 1, 2, 3, 9, 5, 6, 7, 8),
            array(5, 9, 8, 7, 6, 0, 4, 3, 2, 1),
            array(6, 5, 9, 8, 7, 1, 0, 4, 3, 2),
            array(7, 6, 5, 9, 8, 2, 1, 0, 4, 3),
            array(8, 7, 6, 5, 9, 3, 2, 1, 0, 4),
            array(9, 8, 7, 6, 5, 4, 3, 2, 1, 0)
        );
        $permutation = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
            array(1, 5, 7, 6, 2, 8, 3, 0, 9, 4),
            array(5, 8, 0, 3, 7, 9, 6, 1, 4, 2),
            array(8, 9, 1, 6, 0, 4, 3, 5, 2, 7),
            array(9, 4, 5, 3, 1, 2, 6, 8, 7, 0),
            array(4, 2, 8, 6, 5, 7, 3, 9, 0, 1),
            array(2, 7, 9, 3, 8, 0, 6, 4, 1, 5),
            array(7, 0, 4, 6, 9, 1, 3, 2, 5, 8)
        );

        $inverse = array(0, 4, 3, 2, 1, 5, 6, 7, 8, 9);
        settype($partial, "string");
        $partial = strrev($partial);
        $digitIndex = 0;
        for ($i = 0; $i < strlen($partial); $i++) {
            $digitIndex = $dihedral[$digitIndex][$permutation[($i + 1) % 8][$partial[$i]]];
        }
        return $inverse[$digitIndex];
    }

    public function dup_aadhar_approved_verifier(Request $request)
    {
        
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $scheme_id = $this->scheme_id;

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
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $designation_id = Auth::user()->designation_id;
        if (!in_array($designation_id, ['Verifier', 'Delegated Verifier'])) {
            return redirect("/")->with('error', 'User Disabled. ');

        }
        $getModelFunc = new getModelFunc();
        $aadhar_table = $getModelFunc->getTable($district_code, '', 2);
        $model = new DataSourceCommon;
        $model->setTable('' . $aadhar_table);
        $condition["is_dup"] = 1;
        $condition["created_by_dist_code"] = $district_code;
        $condition["created_by_local_body_code"] = $urban_body_code;
        $data = $model->where($condition)->whereNotNull('beneficiary_id')->get();
        //   dd($data->toArray());
        $data = $data->each(function ($item) {
            try {
                if (!empty($item['encoded_aadhar'])) {
                    $item['original_aadhar'] = Crypt::decryptString($item['encoded_aadhar']);
                } else {
                    $item['original_aadhar'] = '';
                }
            } catch (\Exception $e) {
                // Optional logging for debugging
                \Log::error("Aadhar decryption failed", [
                    'value' => $item['encoded_aadhar'],
                    'error' => $e->getMessage(),
                ]);

                $item['original_aadhar'] = 'INVALID';
            }
        });
        $grouped = $data->groupBy('original_aadhar')->map(function ($row) {
            return $row->count();
        });
        $errormsg = Config::get('constants.errormsg');
        return view(
            'Duplicate.duplicateAadharApprovedListVerifier',
            [
                'data' => $grouped,
                'scheme_id' => $this->scheme_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
            ]
        );
    }
    public function dedupAadhaarView(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $scheme_id = $this->scheme_id;
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
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $designation_id = Auth::user()->designation_id;
        if (!in_array($designation_id, ['Verifier', 'Delegated Verifier'])) {
            return redirect("/")->with('error', 'User Disabled. ');

        }
        if (empty($request->aadhar_no)) {
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar no. not found');
        }
        try {
            $aadhar_no = Crypt::decryptString($request->aadhar_no);
        } catch (\Exception $e) {
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar no. not valid');
        }
        $explode = explode(':', $aadhar_no);
        $aadhar_no = $explode[1];
        $aadhar_no = (int) $aadhar_no;
        $aadhar_no = (trim($aadhar_no, ';'));
        $aadhar_no = (int) $aadhar_no;
        $getModelFunc = new getModelFunc();
        $aadhar_table = $getModelFunc->getTable($district_code, '', 2);
        $model = new DataSourceCommon;
        $model->setTable('' . $aadhar_table);
        $personal_table = $getModelFunc->getTable($district_code, '', 1);
        $bank_table = $getModelFunc->getTable($district_code, '', 4);
        $condition[$aadhar_table . ".is_dup"] = 1;
        $condition[$aadhar_table . ".created_by_dist_code"] = $district_code;
        $condition[$aadhar_table . ".created_by_local_body_code"] = $urban_body_code;
        $condition[$personal_table . ".created_by_dist_code"] = $district_code;
        $condition[$personal_table . ".created_by_local_body_code"] = $urban_body_code;
        $condition[$bank_table . ".created_by_dist_code"] = $district_code;
        $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
        $query = $model->where($condition);
        $query = $query->join($personal_table, $personal_table . '.application_id', '=', $aadhar_table . '.application_id');
        $query = $query->join($bank_table, $bank_table . '.application_id', '=', $aadhar_table . '.application_id');
        // dd($query->toSql());
        $data = $query->get();
        $data = $data->each(function ($item) {
            try {
                if (!empty($item['encoded_aadhar'])) {
                    $item['original_aadhar'] = Crypt::decryptString($item['encoded_aadhar']);
                } else {
                    $item['original_aadhar'] = '';
                }
            } catch (\Exception $e) {
                // dd($e);
                // If decryption fails, set a safe value
                $item['original_aadhar'] = 'INVALID';
            }
        });

        //dump($aadhar_no);
        // dd($data->toArray());
        $data = $data->where('original_aadhar', $aadhar_no);
        $errormsg = Config::get('constants.errormsg');
        $restrict_age_model = new DataSourceCommon;
        $schemaname = $getModelFunc->getSchemaDetails();
        $restrict_age_model->setTable($schemaname . '.ben_payment_details');
        $restrict_age_model->setConnection('pgsql_payment');
        $curyymm = substr(date('Y'), -2) . date('m');
        // dd($curyymm);
        $condition = array();
        $condition["dist_code"] = $district_code;
        $condition["local_body_code"] = $urban_body_code;
        $query1 = $restrict_age_model->where($condition)->whereRaw("end_yymm<" . "'" . $curyymm . "'");
        $data1 = $query1->get(['application_id']);
        $age_restrict_data = array();
        if (count($data1) > 0) {
            $age_restrict_data = $data1->pluck('application_id')->toArray();
        }
        // dd($data);
        return view(
            'Duplicate.dedupAadhaarView',
            [
                'data' => $data,
                'aadhar_no_encrypt' => $request->aadhar_no,
                'aadhar_no' => $aadhar_no,
                'scheme_id' => $this->scheme_id,
                'age_restrict_data' => $age_restrict_data,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
            ]
        );
    }

    public function dupAadharReject(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        //return redirect("/")->with('error', 'User Disabled. ');
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
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $designation_id = Auth::user()->designation_id;
        if (!in_array($designation_id, ['Verifier', 'Delegated Verifier'])) {
            return redirect("/")->with('error', 'User Disabled. ');

        }
        $application_id = $request->application_id;
        $aadhar_no = $request->aadhar_no;

        if (empty($aadhar_no)) {
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar No.Not Found');
        }
        try {
            $aadhar_no = Crypt::decryptString($request->aadhar_no);
            $explode = explode(':', $aadhar_no);
            $aadhar_no = $explode[1];
            $aadhar_no = (int) $aadhar_no;
            $aadhar_no = (trim($aadhar_no, ';'));
            $aadhar_no = (int) $aadhar_no;
        } catch (\Exception $e) {
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar no. not valid');
        }
        if (empty($application_id)) {
            return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', ' Application Id Not Found');
        }
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $personal_model->setTable('' . $Table);
        $pension_details_aadhar = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $pension_details_aadhar->setTable('' . $Table);
        $row = $personal_model->where('next_level_role_id', 0)->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $urban_body_code)->first();
        //dd($row->beneficiary_id);
        if (empty($row->application_id)) {
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar no. not valid');
        }
        //DB::beginTransaction();
        try {

            $accept_reject_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
            $accept_reject_model->setTable('' . $Table);
            $accept_reject_model->op_type = 'AR';
            $accept_reject_model->application_id = $row->application_id;
            $accept_reject_model->designation_id = $designation_id;
            $accept_reject_model->scheme_id = $scheme_id;
            $accept_reject_model->user_id = $user_id;
            //$accept_reject_model->comment_message = $comments;
            $accept_reject_model->mapping_level = $mapping_level;
            $accept_reject_model->created_by = $user_id;
            $accept_reject_model->created_by_level = $mapping_level;
            $accept_reject_model->created_by_dist_code = $district_code;
            //$accept_reject_model->rejected_reverted_cause = $rejected_cause;
            $accept_reject_model->ip_address = request()->ip();
            $is_saved = $accept_reject_model->save();


            $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
            //$reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_dup(" . $in_pension_id . ")");
            $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final_dup(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '" . request()->ip() . "', in_action_type => '" . class_basename(request()->route()->getAction()['controller']) . "')");
            return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('success', 'Application with Id (' . $application_id . ') aadhar no. has been successfully rejected');
        } catch (\Exception $e) {
            //dd($e);
            DB::rollback();
            return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', $errormsg['roolback']);
        }
    }
    public function dupAadharmodify(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
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
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $designation_id = Auth::user()->designation_id;

        if (!in_array($designation_id, ['Verifier', 'Delegated Verifier'])) {
            return redirect("/")->with('error', 'User Disabled. ');

        }
        $application_id = $request->application_id;
        $aadhar_no = $request->aadhar_no;
        //dd($request);
        if (empty($aadhar_no)) {
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar No.Not Found');
        }
        try {
            $aadhar_no = Crypt::decryptString($request->aadhar_no);
            $explode = explode(':', $aadhar_no);
            $aadhar_no = $explode[1];
            $aadhar_no = (int) $aadhar_no;
            $aadhar_no = (trim($aadhar_no, ';'));
            $aadhar_no = (int) $aadhar_no;
            $post_aadhar_no = $request->new_aadhar_no;
            // dd($post_aadhar_no);
            $aadharDupCheckOap = DupCheck::getDupCheckAadhar(10, $post_aadhar_no);
            if (!empty($aadharDupCheckOap)) {
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Duplicate Aadhaar Number present in Old Age Pension Scheme with Beneficiary ID- ' . $aadharDupCheckOap . '');
            }
            $aadharDupCheckJohar = DupCheck::getDupCheckAadhar(1, $aadhar_no);
            if (!empty($aadharDupCheckJohar)) {
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Duplicate Aadhaar Number present Jai Johar Pension Scheme with Beneficiary ID- ' . $aadharDupCheckJohar . '');
            }
            $aadharDupCheckBandhu = DupCheck::getDupCheckAadhar(3, $aadhar_no);
            if (!empty($aadharDupCheckBandhu)) {
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Duplicate Aadhaar Number present Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- ' . $aadharDupCheckBandhu . '');
            }
            if (empty($post_aadhar_no)) {
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Aadhaar Number Invalid');
            }
            if (strlen($post_aadhar_no) != 12) {
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Aadhaar Number Invalid');
            }
            if (!ctype_digit($post_aadhar_no)) {
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Aadhaar Number Invalid');
            }
            if ($this->isAadharValid($post_aadhar_no) == false) {

                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Aadhaar Number Invalid');
            }
        } catch (\Exception $e) {
            //dd($e);
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar no. not valid');
        }
        //dd($application_id);
        if (empty($application_id)) {
            return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', ' Application Id Not Found');
        }
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $personal_model->setTable('' . $Table);
        $pension_details_aadhar = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $pension_details_aadhar->setTable('' . $Table);
        $pension_details_encloser1 = new DataSourceCommon;
        $TableEnc = $getModelFunc->getTable($district_code, $this->source_type, 6, 1);
        $pension_details_encloser1->setConnection('pgsql_encwrite');
        $pension_details_encloser1->setTable('' . $TableEnc);
        $row = $personal_model->where('next_level_role_id', 0)->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $urban_body_code)->first();
        //dd($row->beneficiary_id);
        if (empty($row->application_id)) {
            return redirect("/lb-dup-aadhar-list-approved-verifier")->with('error', 'Aadhaar no. not valid');
        }
        if ($request->hasFile('aadhar_file')) {
            //dd('ok');
            $document_type = 6;
            $query = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', $document_type);
            $doc_arr = $query->first();
            //dd($doc_arr->toArray());
            $attributes = array();
            $messages = array();
            $rules['aadhar_file'] = 'mimes:' . $doc_arr->doc_type . '|max:' . $doc_arr->doc_size_kb . ',';
            $messages['aadhar_file.max'] = "The file uploaded for " . $doc_arr->doc_name . " size must be less than :max KB";
            $messages['aadhar_file.mimes'] = "The file uploaded for " . $doc_arr->doc_name . " must be of type " . $doc_arr->doc_type;
            $messages['aadhar_file.required'] = "Document for " . $doc_arr->doc_name . " must be uploaded";
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            //dd($validator->passes());
            if ($validator->passes()) {
                $valid = 1;
                $is_aadhar_file = 1;
                $image_file = $request->file('aadhar_file');
                $img_data = file_get_contents($image_file);
                $extension = $image_file->getClientOriginalExtension();
                $mime_type = $image_file->getMimeType();
                //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                $base64 = base64_encode($img_data);
            } else {
                $errors = $validator->errors()->all();
                // dd($errors[0]);
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', $errors[0]);
            }
        } else {
            $is_aadhar_file = 0;
        }
        DB::beginTransaction();
        DB::connection('pgsql_encwrite')->beginTransaction();
        try {
            // Insert accept_reject_info table.
            $accept_reject_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
            $accept_reject_model->setTable('' . $Table);
            $accept_reject_model->op_type = 'AadharModified';
            $accept_reject_model->application_id = $row->application_id;
            $accept_reject_model->designation_id = $designation_id;
            $accept_reject_model->scheme_id = $scheme_id;
            $accept_reject_model->user_id = $user_id;
            //$accept_reject_model->comment_message = $comments;
            $accept_reject_model->mapping_level = $mapping_level;
            $accept_reject_model->created_by = $user_id;
            $accept_reject_model->created_by_level = $mapping_level;
            $accept_reject_model->created_by_dist_code = $district_code;
            //$accept_reject_model->rejected_reverted_cause = $rejected_cause;
            $accept_reject_model->ip_address = request()->ip();
            $is_saved = $accept_reject_model->save();

            if ($is_aadhar_file) {
                $enc1_status2 = DB::connection('pgsql_encwrite')->statement("INSERT INTO  lb_scheme.ben_attach_documents_arch(
                    application_id,  document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type)
                    select application_id,  document_type, attched_document, created_by_level, created_at, 
                    updated_at, deleted_at, created_by, ip_address, document_extension, 
                    document_mime_type, created_by_dist_code, created_by_local_body_code,action_by,action_ip_address,action_type 
                    from  lb_scheme.ben_attach_documents
                 where document_type=" . $document_type . " and application_id=" . $application_id);
                $pension_details = array();
                $pension_details['document_type'] = $doc_arr->id;
                $pension_details['attched_document'] = $base64;
                $pension_details['document_extension'] = $extension;
                $pension_details['document_mime_type'] = $mime_type;
                $pension_details['action_by'] = Auth::user()->id;
                $pension_details['action_ip_address'] = request()->ip();
                $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                $crd_status_2 = $pension_details_encloser1->where('document_type', $document_type)->where('application_id', $application_id)->update($pension_details);
            }
            $update_aadhar_arr = array();
            $update_aadhar_arr['dup_modification'] = 1;
            $update_aadhar_arr['encoded_aadhar'] = Crypt::encryptString($post_aadhar_no);
            $update_aadhar_arr['aadhar_hash'] = md5($post_aadhar_no);
            $update_personal_arr = array();
            $update_personal_arr['aadhar_no'] = '********' . substr($post_aadhar_no, -4);
            $update_personal_arr['action_by'] = Auth::user()->id;
            $update_personal_arr['action_ip_address'] = request()->ip();
            $update_personal_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
            $is_saved1 = $personal_model->where('application_id', $row->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $urban_body_code)->update($update_personal_arr);
            $is_saved2 = $pension_details_aadhar->where('application_id', $row->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $urban_body_code)->whereNull('dup_modification')->update($update_aadhar_arr);
            if ($is_saved1 && $is_saved2) {
                DB::commit();
                DB::connection('pgsql_encwrite')->commit();
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('success', 'Application with Id (' . $application_id . ') aadhar no. has been successfully modified');
            } else {
                DB::rollback();
                DB::connection('pgsql_encwrite')->rollBack();
                return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Duplicate Aadhar No.');
            }
        } catch (\Exception $e) {
            //dd($e);
            DB::rollback();
            DB::connection('pgsql_encwrite')->rollBack();
            return redirect("/dedupAadhaarView?aadhar_no=" . $request->aadhar_no)->with('error', 'Duplicate Aadhar No.');
        }
    }
    public function dup_aadhar_approved_approver(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                break;
            }
        }
        if ($is_active == 0 || empty($district_code)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        $levels = [
            2 => 'Rural',
            1 => 'Urban',
        ];
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        $designation_id = Auth::user()->designation_id;
        if (!in_array($designation_id, ['Approver', 'Delegated Approver'])) {
            return redirect("/")->with('error', 'User Disabled. ');

        }
        if (request()->ajax()) {
            $condition = array();

            $getModelFunc = new getModelFunc();
            $personal_table = $getModelFunc->getTable($district_code, '', 1);
            $personal_modal = new DataSourceCommon;
            $personal_modal->setTable('' . $personal_table);
            $contact_table = $getModelFunc->getTable($district_code, '', 3);
            $aadhar_table = $getModelFunc->getTable($district_code, '', 2);
            $condition[$personal_table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$aadhar_table . ".created_by_dist_code"] = $district_code;
            $condition[$personal_table . ".next_level_role_id"] = 0;
            // $limit = $request->input('length');
            // $offset = $request->input('start');
            // $totalRecords = 0;
            // $filterRecords = 0;
            // $data = array();
            if (!empty($request->search['value']))
                $serachvalue = $request->search['value'];
            else
                $serachvalue = '';
            $query = $personal_modal->where($condition);
            $query = $query->where($personal_table . '.next_level_role_id', '!=', '9999');
            $query = $query->join($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
            $query = $query->join($aadhar_table, $aadhar_table . '.application_id', '=', $personal_table . '.application_id');
            $query = $query->whereNotNull($aadhar_table . '.beneficiary_id')->where('is_dup', 1)->whereIn('dup_modification', array(1, 2));

            if (!empty($request->filter_1)) {
                // $query = $query->where('rural_urban_id', $request->filter_1);
                $query = $query->where($contact_table . '.rural_urban_id', $request->filter_1);
            }
            if (!empty($request->filter_2)) {

                $query = $query->where($personal_table . '.created_by_local_body_code', $request->filter_2);
            }
            if (!empty($request->block_ulb_code)) {

                $query = $query->where($contact_table . '.block_ulb_code', $request->block_ulb_code);
            }
            if (!empty($request->gp_ward_code)) {

                $query = $query->where($contact_table . '.gp_ward_code', $request->gp_ward_code);
            }
            if (!empty($request->caste_category)) {

                $query = $query->where('caste', $request->caste_category);
            }
            if (empty($serachvalue)) {
                //$totalRecords = $query->count();
                // $totalRecords = $query->count($personal_table . '.application_id');
                $data = $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->select('ben_fname', 'ben_mname', 'ben_lname', 'mobile_no', 'age_ason_01012021', 'ss_card_no', 'duare_sarkar_registration_no', 'gp_ward_name', 'block_ulb_name', $personal_table . '.application_id as application_id', $personal_table . '.beneficiary_id as beneficiary_id', $aadhar_table . '.encoded_aadhar', $aadhar_table . '.dup_modification');
                // $filterRecords = count($data);
            } else {
                if (preg_match('/^[0-9]*$/', $serachvalue)) {
                    $query = $query->where(function ($query1) use ($serachvalue, $personal_table) {
                        if (strlen($serachvalue) < 10) {
                            $query1->where($personal_table . '.application_id', $serachvalue);
                        } else if (strlen($serachvalue) == 10) {
                            $query1->where($personal_table . '.mobile_no', $serachvalue);
                        } else if (strlen($serachvalue) == 17) {
                            $query1->where($personal_table . '.ss_card_no', $serachvalue);
                        } else if (strlen($serachvalue) == 20) {
                            $query1->where($personal_table . '.duare_sarkar_registration_no', $serachvalue);
                        }
                    });
                    //$totalRecords = $query->count();
                    // $totalRecords = $query->count($personal_table . '.application_id');
                    $data = $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->select('ben_fname', 'ben_mname', 'ben_lname', 'mobile_no', 'age_ason_01012021', 'ss_card_no', 'duare_sarkar_registration_no', 'gp_ward_name', 'block_ulb_name', $personal_table . '.application_id as application_id', $personal_table . '.beneficiary_id as beneficiary_id', $aadhar_table . '.encoded_aadhar', $aadhar_table . '.dup_modification');
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue, $personal_table, $contact_table) {
                        $query1->where($personal_table . '.ben_fname', 'like', $serachvalue . '%')
                            ->orWhere($contact_table . '.block_ulb_name', 'like', $serachvalue . '%');
                    });
                    //$totalRecords = $query->count();
                    // $totalRecords = $query->count($personal_table . '.application_id');
                    $data = $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->select('ben_fname', 'ben_mname', 'ben_lname', 'mobile_no', 'age_ason_01012021', 'ss_card_no', 'duare_sarkar_registration_no', 'gp_ward_name', 'block_ulb_name', $personal_table . '.application_id as application_id', $personal_table . '.beneficiary_id as beneficiary_id', $aadhar_table . '.encoded_aadhar', $aadhar_table . '.dup_modification');
                }
                // $filterRecords = count($data);
            }
            return datatables()->of($data)
                // ->setTotalRecords($totalRecords)
                // ->setFilteredRecords($filterRecords)
                // ->skipPaging()
                ->addColumn('view', function ($data) {

                    if ($data->dup_modification == 2) {
                        $action = '<span class="label label-success">Aadhaar modification has been successfully approved</span>';
                    } else {
                        $action = '<button class="btn btn-success btn-sm ben_approve_button" id="btnApprove_' . $data->application_id . '" value=' . $data->application_id . '>Approve</button>';
                        $action = $action . '&nbsp;&nbsp;&nbsp;<button class="btn btn-warning btn-sm ben_reject_button" id="btnReject_' . $data->application_id . '" value=' . $data->application_id . '>Revert</button>';

                        $action = $action . '&nbsp;&nbsp;&nbsp;<button class="btn btn-info ben_doc_button" id="btnDoc_' . $data->application_id . '" value="' . $data->application_id . '">View Doc(Aadhaar)</button></td>
                        ';
                    }

                    return $action;
                })->addColumn('id', function ($data) {
                    return $data->application_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->getName();
                })
                ->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                })
                ->addColumn('duare_sarkar_registration_no', function ($data) {
                    return $data->duare_sarkar_registration_no;
                })
                ->addColumn('age', function ($data) {
                    return $data->age_ason_01012021;
                })->addColumn('ss_card_no', function ($data) {
                    return $data->ss_card_no;
                    // })->addColumn('gp_ward_name', function ($data) {
                    //   return trim($data->gp_ward_name);
    
                    //  })
                })
                ->addColumn('aadhar_no', function ($data) {
                    return '********' . substr(Crypt::decryptString($data->encoded_aadhar), -4);
                })->addColumn('check', function ($data) {

                    return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->application_id . '">';
                })
                ->rawColumns(['id', 'name', 'ss_card_no', 'view', 'check'])
                ->make(true);
        }
        $errormsg = Config::get('constants.errormsg');
        return view(
            'Duplicate.duplicateAadharApprovedListApprover',
            [
                'dist_code' => $district_code,
                'levels' => $levels,
                'reject_revert_reason' => $reject_revert_reason,
                'scheme_id' => $this->scheme_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
            ]
        );
    }
    public function dupAadhaarApproved(Request $request)
    {
        //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');
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
                    break;
                }
            }
            if ($is_active == 0 || empty($district_code)) {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            $designation_id = Auth::user()->designation_id;
            if (!in_array($designation_id, ['Approver', 'Delegated Approver'])) {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            $application_id = $request->application_id;
            $is_bulk = $request->is_bulk;
            $applicant_id_post = request()->input('applicantId');
            //dd($applicant_id_post);
            $applicant_id_in = explode(',', $applicant_id_post);
            $accept_reject_type = $request->accept_reject_type;
            $accept_reject_comments = trim($request->accept_reject_comments);
            if ($is_bulk == 0) {
                if (empty($application_id)) {
                    return redirect("/lb-dup-aadhar-list-approved-approver")->with('error', ' Application Id Not Found');
                }
            }
            if (!in_array($accept_reject_type, array('A', 'R'))) {
                return redirect("/")->with('error', 'Input Invalid. ');
            }
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $personal_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1);
            $personal_model->setTable('' . $Table);
            $pension_details_aadhar = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 2);
            $pension_details_aadhar->setTable('' . $Table);
            if ($is_bulk == 0) {
                $row = $personal_model->where('next_level_role_id', 0)->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->first();

                //dd($row->beneficiary_id);
                if (empty($row->application_id)) {
                    return redirect("/lb-dup-aadhar-list-approved-approver")->with('error', 'Application Id not valid');
                }
                $count_query = "select count(1) as cnt from " . $schemaname . ".ben_payment_details where ben_status=0 and ben_id=" . $row->beneficiary_id . " and dist_code=" . $district_code;
                $count_data = DB::connection('pgsql_payment')->select($count_query);
                if (empty($count_data[0]->cnt) || $count_data[0]->cnt == 0) {
                    $ben_update = 0;
                    $ben_update_status = 1;
                } else {
                    $ben_update = 1;
                    $ben_update_status = 0;
                }
            }

            if ($is_bulk == 1) {
                $arr = array();
                $i = 0;
                //dd($applicant_id_in);
                foreach ($applicant_id_in as $app) {

                    $row = $personal_model->where('next_level_role_id', 0)->where('application_id', $app)->where('created_by_dist_code', $district_code)->first();

                    //dd($row->beneficiary_id);
                    if (empty($row->application_id)) {
                        return redirect("/lb-dup-aadhar-list-approved-approver")->with('error', 'Application Id not valid');
                    }
                    $arr[$i]['application_id'] = $app;
                    $arr[$i]['ben_id'] = $row->beneficiary_id;
                    $count_query = "select count(1) as cnt from " . $schemaname . ".ben_payment_details where ben_status=0 and ben_id=" . $row->beneficiary_id . " and dist_code=" . $district_code;
                    $count_data = DB::connection('pgsql_payment')->select($count_query);
                    if (empty($count_data[0]->cnt) || $count_data[0]->cnt == 0) {
                        $ben_update = 0;
                        $ben_update_status = 1;
                        $arr[$i]['ben_update_status'] = 0;
                    } else {
                        $ben_update = 1;
                        $ben_update_status = 0;
                        $arr[$i]['ben_update_status'] = 1;
                    }
                    $i++;
                }
            }
            //dd($arr);
            DB::beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
            try {
                if ($is_bulk == 0) {
                    // Insert accept_reject_info table.
                    $accept_reject_model = new DataSourceCommon;
                    $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                    $accept_reject_model->setTable('' . $Table);
                    $accept_reject_model->op_type = 'AadharApproved';
                    $accept_reject_model->application_id = $row->application_id;
                    $accept_reject_model->designation_id = $designation_id;
                    $accept_reject_model->scheme_id = $scheme_id;
                    $accept_reject_model->user_id = $user_id;
                    //$accept_reject_model->comment_message = $comments;
                    $accept_reject_model->mapping_level = $mapping_level;
                    $accept_reject_model->created_by = $user_id;
                    $accept_reject_model->created_by_level = $mapping_level;
                    $accept_reject_model->created_by_dist_code = $district_code;
                    //$accept_reject_model->rejected_reverted_cause = $rejected_cause;
                    $accept_reject_model->ip_address = request()->ip();
                    $is_saved = $accept_reject_model->save();

                    if ($accept_reject_type == 'A') {
                        $update_aadhar_arr = array();
                        $update_aadhar_arr['dup_modification'] = 2;
                        if (!empty($accept_reject_comments)) {
                            $update_aadhar_arr['dup_accept_reject_remarks'] = $accept_reject_comments;
                        }
                        $update_aadhar_arr['is_dup'] = NULL;
                        $update_aadhar_arr['action_by'] = Auth::user()->id;
                        $update_aadhar_arr['action_ip_address'] = request()->ip();
                        $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        $update_payment_arr = array();
                        $update_payment_arr['ben_status'] = 1;
                        $is_saved1 = $pension_details_aadhar->where('application_id', $row->application_id)->where('created_by_dist_code', $district_code)->where('is_dup', 1)->where('dup_modification', 1)->update($update_aadhar_arr);
                        if ($ben_update)
                            $is_saved2 = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $row->beneficiary_id)->where('ben_status', 0)->where('dist_code', $district_code)->update($update_payment_arr);
                        else
                            $is_saved2 = 1;
                        $is_saved3 = $personal_model->where('application_id', $row->application_id)->where('created_by_dist_code', $district_code)->where('is_aadhar_dup', 1)->update(['action_by' => Auth::user()->id, 'action_ip_address' => request()->ip(), 'action_type' => class_basename(request()->route()->getAction()['controller']), 'is_aadhar_dup' => NULL]);
                    } else if ($accept_reject_type == 'R') {
                        $update_aadhar_arr = array();
                        $update_aadhar_arr['dup_modification'] = NULL;
                        $update_aadhar_arr['action_by'] = Auth::user()->id;
                        $update_aadhar_arr['action_ip_address'] = request()->ip();
                        $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        if (!empty($accept_reject_comments)) {
                            $update_aadhar_arr['dup_accept_reject_remarks'] = $accept_reject_comments;
                        }
                        $is_saved1 = $pension_details_aadhar->where('application_id', $row->application_id)->where('created_by_dist_code', $district_code)->where('is_dup', 1)->where('dup_modification', 1)->update($update_aadhar_arr);
                        $is_saved2 = 1;
                        $is_saved3 = 1;
                    }
                }
                if ($is_bulk == 1) {
                    $k = 0;
                    foreach ($arr as $app) {
                        // Insert accept_reject_info table.
                        $accept_reject_model = new DataSourceCommon;
                        $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                        $accept_reject_model->setTable('' . $Table);
                        $accept_reject_model->op_type = 'AadharApproved';
                        $accept_reject_model->application_id = $row->application_id;
                        $accept_reject_model->designation_id = $designation_id;
                        $accept_reject_model->scheme_id = $scheme_id;
                        $accept_reject_model->user_id = $user_id;
                        //$accept_reject_model->comment_message = $comments;
                        $accept_reject_model->mapping_level = $mapping_level;
                        $accept_reject_model->created_by = $user_id;
                        $accept_reject_model->created_by_level = $mapping_level;
                        $accept_reject_model->created_by_dist_code = $district_code;
                        //$accept_reject_model->rejected_reverted_cause = $rejected_cause;
                        $accept_reject_model->ip_address = request()->ip();
                        $is_saved = $accept_reject_model->save();

                        $update_aadhar_arr = array();
                        $update_aadhar_arr['dup_modification'] = 2;
                        $update_aadhar_arr['is_dup'] = NULL;
                        $update_aadhar_arr['action_by'] = Auth::user()->id;
                        $update_aadhar_arr['action_ip_address'] = request()->ip();
                        $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        $update_payment_arr = array();
                        $update_payment_arr['ben_status'] = 1;
                        $is_saved1_bulk = $pension_details_aadhar->where('application_id', $app['application_id'])->where('created_by_dist_code', $district_code)->where('is_dup', 1)->where('dup_modification', 1)->update($update_aadhar_arr);
                        $is_saved3_bulk = $personal_model->where('application_id', $app['application_id'])->where('created_by_dist_code', $district_code)->where('is_aadhar_dup', 1)->update(['action_by' => Auth::user()->id, 'action_ip_address' => request()->ip(), 'action_type' => class_basename(request()->route()->getAction()['controller']), 'is_aadhar_dup' => NULL]);

                        if ($app['ben_update_status'])
                            $is_saved2_bulk = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $app['ben_id'])->where('ben_status', 0)->where('dist_code', $district_code)->update($update_payment_arr);
                        else
                            $is_saved2_bulk = 1;
                        if ($is_saved1_bulk && $is_saved2_bulk && $is_saved3_bulk) {
                            $k++;
                        }
                    }
                    if (count($arr) == $k) {
                        $is_saved1 = $is_saved2 = $is_saved3 = 1;
                    } else {
                        $is_saved1 = $is_saved2 = $is_saved3 = 0;
                    }
                }
                if ($is_saved1 && $is_saved2) {
                    DB::commit();
                    DB::connection('pgsql_payment')->commit();
                    if ($accept_reject_type == 'A') {
                        if ($is_bulk == 1) {
                            $msg = 'Applications aadhar no. modification has been successfully approved';
                        } else {
                            $msg = 'Application with Id (' . $application_id . ') aadhar no. modification has been successfully approved';
                        }
                    } else if ($accept_reject_type == 'R') {
                        if ($is_bulk == 1) {
                            $msg = 'Applications aadhar no. modification has been successfully reverted';
                        } else {
                            $msg = 'Application with Id (' . $application_id . ') aadhar no. modification has been successfully reverted';
                        }
                    }
                    if ($is_bulk == 1) {
                        return redirect("/lb-dup-aadhar-list-approved-approver")->with('success', $msg);
                    } else {
                        return redirect("/lb-dup-aadhar-list-approved-approver")->with('success', $msg);
                    }
                } else {
                    DB::rollback();
                    DB::connection('pgsql_payment')->rollback();
                    return redirect("/lb-dup-aadhar-list-approved-approver")->with('error', $errormsg['roolback']);
                }
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                DB::connection('pgsql_payment')->rollback();
                return redirect("/lb-dup-aadhar-list-approved-approver")->with('error', $errormsg['roolback']);
            }
        } catch (\Exception $e) {
            // dd($e);
            return redirect("/lb-dup-aadhar-list-approved-approver")->with('error', $errormsg['roolback']);
        }
    }
    public function misAadhar(Request $request)
    {
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $base_date = '2020-01-01';
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' || $designation_id == 'Dashboard') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Delegated Verifier' || $designation_id == 'Delegated Approver' || $designation_id == 'Approver' || $designation_id == 'Verifier') {
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
        //$is_urban_visible=0;
        $block_visible = 0;
        $municipality_visible = 0;
        $gp_ward_visible = 0;
        return view(
            'Duplicate.misreport',
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
                'ds_phase_list' => $ds_phase_list,
                'designation_id' => $designation_id
            ]
        );
    }
    public function misAadharPost(Request $request)
    {
        $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
        $ds_phase = $request->ds_phase;
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;
        $caste = $request->caste_category;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $base_date = '2020-08-16';
        $c_time = Carbon::now();
        $c_date = $c_time->format("Y-m-d");
        $heading_msg = '';
        $title = "";
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
            'from_date' => 'nullable|date|after_or_equal:' . $base_date . '|before_or_equal:' . $c_date,
            'to_date' => 'nullable|date|after_or_equal:from_date|before_or_equal:' . $c_date,
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
            $user_msg = "Aadhaar DeDuplication Mis Report";
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
                    $heading_msg = $user_msg . ' of the Ward ' . $gp_ward_name;
                    $data = $this->getWardWise($district, $block, $muncid, $gp_ward, $from_date, $to_date, $caste, $ds_phase);
                } else {
                    $column = "GP";
                    $heading_msg = $user_msg . ' of the GP ' . $gp_ward_name;
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
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {
        //$dateFromat = 'DD/MM/YYYY';
        $dateFromat = 'YYYY-MM-DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;

        $query = " SELECT main.location_id,
        main.location_name,
        COALESCE(bp_main.total_duplicate, 0) AS total_duplicate,
        COALESCE(bp_main.approval_pending, 0) AS approval_pending,
        COALESCE(bp_main.approved_deduplication, 0) AS approved_deduplication
        FROM
        (
            SELECT block_code AS location_id, block_name AS location_name
            FROM public.m_block " . $whereMain . "
        ) AS main
        LEFT JOIN
        (
        SELECT
            count(1) FILTER(WHERE a.dup_modification IS NULL AND a.is_dup = 1 AND p.next_level_role_id = 0) AS total_duplicate,
            count(1) FILTER(WHERE a.dup_modification = 1 AND a.is_dup = 1 AND p.next_level_role_id = 0) AS approval_pending,
            count(1) FILTER(WHERE a.dup_modification = 2 AND a.is_dup IS NULL AND p.next_level_role_id = 0) AS approved_deduplication,
            a. created_by_local_body_code
            FROM lb_scheme.ben_aadhar_details a
            JOIN lb_scheme.ben_personal_details p ON a.application_id = p.application_id
            JOIN lb_scheme.ben_bank_details b ON a.application_id = b.application_id where p.created_by_dist_code=" . $district_code . "
            GROUP BY a.created_by_local_body_code
            ) AS bp_main ON main.location_id = bp_main.created_by_local_body_code
            ORDER BY main.location_name;";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {

        $dateFromat = 'YYYY-MM-DD';
        $whereCon = "where A.created_by_dist_code=" . $district_code;
        $whereMain = "where  district_code=" . $district_code;

        $query = "SELECT main.location_id,
        main.location_name,
        COALESCE(bp_main.total_duplicate, 0) AS total_duplicate,
        COALESCE(bp_main.approval_pending, 0) AS approval_pending,
        COALESCE(bp_main.approved_deduplication, 0) AS approved_deduplication
        FROM
        (
            SELECT sub_district_code AS location_id, sub_district_name AS location_name
            FROM public.m_sub_district " . $whereMain . "
        ) AS main
        LEFT JOIN
        (
        SELECT
            count(1) FILTER(WHERE a.dup_modification IS NULL AND a.is_dup = 1 AND p.next_level_role_id = 0) AS total_duplicate,
            count(1) FILTER(WHERE a.dup_modification = 1 AND a.is_dup = 1 AND p.next_level_role_id = 0) AS approval_pending,
            count(1) FILTER(WHERE a.dup_modification = 2 AND a.is_dup IS NULL AND p.next_level_role_id = 0) AS approved_deduplication,
            a. created_by_local_body_code
            FROM lb_scheme.ben_aadhar_details a
            JOIN lb_scheme.ben_personal_details p ON a.application_id = p.application_id
            JOIN lb_scheme.ben_bank_details b ON a.application_id = b.application_id where p.created_by_dist_code=" . $district_code . "
            GROUP BY a.created_by_local_body_code
            ) AS bp_main ON main.location_id = bp_main.created_by_local_body_code
            ORDER BY main.location_name;";
        $result = DB::connection('pgsql_appread')->select($query);
        return $result;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $fromdate = NULL, $todate = NULL, $caste = NULL, $ds_phase = NULL)
    {

        $query = "SELECT main.location_id,
        main.location_name,
        COALESCE(bp_main.total_duplicate, 0) AS total_duplicate,
        COALESCE(bp_main.approval_pending, 0) AS approval_pending,
        COALESCE(bp_main.approved_deduplication, 0) AS approved_deduplication
        FROM
        (
            SELECT district_code AS location_id, district_name AS location_name
            FROM public.m_district 
        ) AS main
        LEFT JOIN
        (
        SELECT
            count(1) FILTER(WHERE a.dup_modification IS NULL AND a.is_dup = 1 AND p.next_level_role_id = 0) AS total_duplicate,
            count(1) FILTER(WHERE a.dup_modification = 1 AND a.is_dup = 1 AND p.next_level_role_id = 0) AS approval_pending,
            count(1) FILTER(WHERE a.dup_modification = 2 AND a.is_dup IS NULL AND p.next_level_role_id = 0) AS approved_deduplication,
            a. created_by_dist_code
            FROM lb_scheme.ben_aadhar_details a
            JOIN lb_scheme.ben_personal_details p ON a.application_id = p.application_id
            JOIN lb_scheme.ben_bank_details b ON a.application_id = b.application_id 
            GROUP BY a.created_by_dist_code
            ) AS bp_main ON main.location_id = bp_main.created_by_dist_code
            ORDER BY main.location_name;";

        // echo $query;die;
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    public function dupAadharBenList(Request $request)
    {
        $base_date = '2020-01-01';
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' || $designation_id == 'Dashboard') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
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
            'Duplicate.dedupAadharBenList',
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

    public function dupAadharGetBenList(Request $request)
    {
        $base_date = '2020-01-01';
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' || $designation_id == 'Dashboard') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
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
        $getModelFunc = new getModelFunc();
        $personal_table = $getModelFunc->getTable($district_code, '', 1);
        $personal_modal = new DataSourceCommon;
        $personal_modal->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3);
        $aadhar_table = $getModelFunc->getTable($district_code, '', 2);

        $filter = $request->search_for;
        $block = $request->block_ulb_code;
        $rural_urban = $request->rural_urban;
        $gp_ward = $request->gp_ward_code;
        $muncid = $request->muncid;
        if ($request->ajax()) {
            $whereCon = array();
            if ($filter == 1) {
                $whereCon[$aadhar_table . ".dup_modification"] = null;
                $whereCon[$aadhar_table . ".is_dup"] = 1;
            } elseif ($filter == 2) {
                $whereCon[$aadhar_table . ".dup_modification"] = 1;
                $whereCon[$aadhar_table . ".is_dup"] = 1;
            } elseif ($filter == 3) {
                $whereCon[$aadhar_table . ".dup_modification"] = 2;
                $whereCon[$aadhar_table . ".is_dup"] = null;
            } else {
                //
            }
            $result = $this->getQueryResult($district_code, $blockCode, $block, $gp_ward, $muncid, $rural_urban, $whereCon);
            // dd($result);
            // $result = DB::connection('pgsql_appwrite')->select($query);
            return datatables()->of($result)
                ->rawColumns(['status'])
                ->make(true);
        }
    }

    public function GetdupAadharBenListExcel(Request $request)
    {
        $base_date = '2020-01-01';
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
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' || $designation_id == 'Dashboard') {
            $district_visible = $is_urban_visible = $block_visible = 1;
        } else if ($designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
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
        $getModelFunc = new getModelFunc();
        $personal_table = $getModelFunc->getTable($district_code, '', 1);
        $personal_modal = new DataSourceCommon;
        $personal_modal->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3);
        $aadhar_table = $getModelFunc->getTable($district_code, '', 2);

        $filter = $request->search_for;
        $block = $request->block_ulb_code;
        $rural_urban = $request->rural_urban;
        $gp_ward = $request->gp_ward_code;
        $muncid = $request->muncid;
        $schemeObj = 'Lakshmir Bhandar';
        $user_msg = 'Aadhar De-Duplication Pending Beneficiary List';
        if ($filter == 1) {
            $whereCon[$aadhar_table . ".dup_modification"] = null;
            $whereCon[$aadhar_table . ".is_dup"] = 1;
        } elseif ($filter == 2) {
            $whereCon[$aadhar_table . ".dup_modification"] = 1;
            $whereCon[$aadhar_table . ".is_dup"] = 1;
        } else {
            $whereCon[$aadhar_table . ".dup_modification"] = 2;
            $whereCon[$aadhar_table . ".is_dup"] = null;
        }
        $result = $this->getQueryResult($district_code, $blockCode, $block, $gp_ward, $muncid, $rural_urban, $whereCon);
        $excelarr[] = array(
            'Beneficiary ID',
            'Beneficiary Name',
            'Block/Municipality',
            'GP/Ward',
            'Mobile Number'
        );
        foreach ($result as $arr) {
            $excelarr[] = array(
                'Beneficiary ID' => trim($arr->beneficiary_id),
                'Beneficiary Name' => trim($arr->ben_fname),
                'Block/Municipality' => trim($arr->block_ulb_name),
                'GP/Ward' => trim($arr->gp_ward_name),
                'Mobile Number' => trim($arr->mobile_no),
            );
        }
        $file_name = $schemeObj . ' ' . $user_msg . ' ' . date('d/m/Y');
        Excel::create($file_name, function ($excel) use ($excelarr) {
            $excel->setTitle('Jai Bangla Duplicate Report');
            $excel->sheet('Jai Bangla Duplicate Report', function ($sheet) use ($excelarr) {
                $sheet->fromArray($excelarr, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    private function getQueryResult($district_code, $blockCode, $block, $gp_ward, $muncid, $rural_urban, $whereCon)
    {
        $condition = array();

        $getModelFunc = new getModelFunc();
        $personal_table = $getModelFunc->getTable($district_code, '', 1);
        $personal_modal = new DataSourceCommon;
        $personal_modal->setTable('' . $personal_table);
        $contact_table = $getModelFunc->getTable($district_code, '', 3);
        $aadhar_table = $getModelFunc->getTable($district_code, '', 2);
        $condition[$personal_table . ".created_by_dist_code"] = $district_code;
        $condition[$contact_table . ".created_by_dist_code"] = $district_code;
        $condition[$aadhar_table . ".created_by_dist_code"] = $district_code;
        $condition[$personal_table . ".next_level_role_id"] = 0;

        $query = $personal_modal->where($condition);
        $query = $query->join($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
        $query = $query->join($aadhar_table, $aadhar_table . '.application_id', '=', $personal_table . '.application_id');
        $query = $query->where($whereCon);
        if (!empty($district_code)) {
            $query = $query->where($personal_table . '.created_by_dist_code', $district_code);
        }
        if (!empty($rural_urban)) {
            $query = $query->where($contact_table . '.rural_urban_id', $rural_urban);
        }
        if (!empty($block)) {
            $query = $query->where($contact_table . '.created_by_local_body_code', $block);
        }
        if (!empty($muncid)) {
            $query = $query->where($contact_table . '.block_ulb_code', $muncid);
        }
        if (!empty($gp_ward)) {
            $query = $query->where($contact_table . '.gp_ward_code', $gp_ward);
        }
        $result = $query->get();
        return $result;
    }
}
