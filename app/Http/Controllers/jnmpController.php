<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GP;
use App\Models\Configduty;
use App\Models\District;
use App\Models\UrbanBody;
use App\Models\SubDistrict;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\User;
use Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Validator;
use DateTime;
use App\Models\Scheme;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use App\Models\DocumentType;
use App\Models\DsPhase;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BankFailedExport;

class jnmpController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
        set_time_limit(300);
    }

    public function index(Request $request)
    {
        // $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
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
            'JnmpWithLb.index',
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

    public function getJnmpData(Request $request)
    {
        // echo 1;die;
        // $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
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
                    // echo $district_code;die;
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                        $muncList = UrbanBody::select('urban_body_code', 'urban_body_name')->where('sub_district_code', $blockCode)->get();
                        $municipality_visible = 1;
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                        // echo $blockCode;die;
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
        // echo $district_code;die;
        $dist_code = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $gp_ward = $request->gp_ward;
        $muncid = $request->muncid;
        $is_faulty = $request->is_faulty;
        // echo $is_faulty;die;
        if ($request->ajax()) {
            $query = $this->getDataquerys($district_code, $blockCode, $block, $gp_ward, $muncid, $dist_code, $is_faulty);
            //   echo $query;die();
            $result = DB::connection('pgsql_appwrite')->select($query);
            // echo '<pre>';print_r($result);die();
            return datatables()->of($result)
                ->addColumn('action', function ($result) {
                    $btn = '';
                    $btn .= '<button onclick=viewModalFunction(' . $result->application_id . ') class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> Activate as alive</button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function modalDataView(Request $request)
    {
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statusCode);
        }
        try {
            $app_id = $request->id;
            $is_faulty = $request->is_faulty;
            // echo $is_faulty;die;
            if ($is_faulty == 1) {
                $row = "SELECT j.deceasedfullname,j.dateofdeath,CONCAT(b.father_fname,' ',b.father_mname,' ',b.father_lname) AS father_name,b.* FROM jnmp.jnmp_data j JOIN lb_scheme.faulty_ben_personal_details b ON b.application_id = j.lb_application_id
                WHERE b.jnmp_marked = 1 AND b.payment_suspended = 1 AND application_id = " . $app_id;
            }
            if ($is_faulty == 2) {
                $row = "SELECT j.deceasedfullname,j.dateofdeath,CONCAT(b.father_fname,' ',b.father_mname,' ',b.father_lname) AS father_name,b.* FROM jnmp.jnmp_data j JOIN lb_scheme.ben_personal_details b ON b.application_id = j.lb_application_id
                WHERE b.jnmp_marked = 1 AND b.payment_suspended = 1 AND application_id = " . $app_id;
            }
            // echo $row;die;
            $query = DB::connection('pgsql_appwrite')->select($row);
            // dd($query);
            if ($query == null) {
                return  $response = array(
                    'status' => 1,
                    'msg' => 'Somethimg went wrong.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!'
                );
            } else {
                $ben_arr = array(
                    'ben_name' => trim($query[0]->ben_fname) . ' ' . trim($query[0]->ben_mname) . ' ' . trim($query[0]->ben_lname),
                    'beneficiary_id' => $query[0]->beneficiary_id,
                    'mobile_no' => $query[0]->mobile_no,
                    'application_id' => $query[0]->application_id,
                    'father_name' => trim($query[0]->father_fname) . ' ' . trim($query[0]->father_mname) . ' ' . trim($query[0]->father_lname),
                    'caste' => trim($query[0]->caste),
                    'gender' => trim($query[0]->gender),
                    'dob' => date('d-m-Y', strtotime($query[0]->dob)),
                    'aadhar_no' => trim($query[0]->aadhar_no),
                    // 'doc_name' => $doc_list->doc_name, 'doc_id' => $doc_list->id, 'doc_type' => $doc_list->doc_type, 'doc_size_kb' => $doc_list->doc_size_kb,
                    'jnmp_fullname' => $query[0]->deceasedfullname,
                    'jnmp_date_of_death' => $query[0]->dateofdeath
                );
                $response = $ben_arr;
            }
        } catch (\Exception $e) {
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
            );
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function activeBeneficiary(Request $request)
    {
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statusCode);
        }
        try {
            // dd($request->all());
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
            $beneficiary_id = $request->id;
            $application_id = $request->application_id;
            $file_stop_payment = $request->file('file_stop_payment');
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            // dd($beneficiary_id);
            $tableName = Helper::getTable($beneficiary_id);
            // dd($tableName);
            $remarks = $request->remarks;
            $reactive_reason = $request->reactive_reason;
            $is_faulty = $request->is_faulty;
            // dd($is_faulty);
            $doc_list = DocumentType::where('id', 250)->value('id');
            // DB::enableQueryLog();
            if ($is_faulty == 1) {
                $getBenDetailsObj = DB::connection('pgsql_appread')
                    ->table('lb_scheme.' . $tableName['benTable'] . ' AS bp')
                    ->join('lb_scheme.' . $tableName['benContactTable'] . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
                    ->where('bp.beneficiary_id', $beneficiary_id)
                    ->where('bp.application_id', $application_id)
                    ->get(['bp.ben_fname', 'bp.ben_mname', 'bp.ben_lname', 'bp.ss_card_no', 'bp.mobile_no', 'bp.created_by_dist_code', 'bp.created_by_local_body_code', 'bc.block_ulb_code', 'bc.gp_ward_code', 'bc.rural_urban_id']);
            }
            if ($is_faulty == 2) {
                $getBenDetailsObj = DB::connection('pgsql_appread')
                    ->table('lb_scheme.' . $tableName['benTable'] . ' AS bp')
                    ->join('lb_scheme.' . $tableName['benContactTable'] . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
                    ->where('bp.beneficiary_id', $beneficiary_id)
                    ->where('bp.application_id', $application_id)
                    ->get(['bp.ben_fname', 'bp.ben_mname', 'bp.ben_lname', 'bp.ss_card_no', 'bp.mobile_no', 'bp.created_by_dist_code', 'bp.created_by_local_body_code', 'bc.block_ulb_code', 'bc.gp_ward_code', 'bc.rural_urban_id']);
            }
            // print_r($getBenDetailsObj);die;
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_encwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
            $ben_personal_details = new DataSourceCommon;
            if ($is_faulty == 1) {
                $Table = $getModelFunc->getTableFaulty('', '', 1, '');
            }
            if ($is_faulty == 2) {
                $Table = $getModelFunc->getTable('', '', 1, '');
            }
            // echo $Table;die;
            $ben_personal_details->setConnection('pgsql_appwrite');
            $ben_personal_details->setTable('' . $Table);

            $pension_details_encloser2 = new DataSourceCommon;
            if ($is_faulty == 1) {
                $Table = $getModelFunc->getTableFaulty('', '', 6, '');
            }
            if ($is_faulty == 2) {
                $Table = $getModelFunc->getTable('', '', 6, '');
            }
            // echo $Table;die;
            $pension_details_encloser2->setConnection('pgsql_encwrite');
            $pension_details_encloser2->setTable('' . $Table);

            $ben_payment_details = new DataSourceCommon;
            $TableBen = 'payment.ben_payment_details';
            $ben_payment_details->setConnection('pgsql_payment');
            $ben_payment_details->setTable('' . $TableBen);

            $update_ben_details = new DataSourceCommon;
            $TableBen = 'lb_scheme.update_ben_details';
            $update_ben_details->setConnection('pgsql_appwrite');
            $update_ben_details->setTable('' . $TableBen);

            /*  Document Upload Section  */
            if (!empty($request->file('file_stop_payment'))) {
                $attributes = array();
                $pension_details = array();
                $query = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', 250);
                $doc_arr = $query->first();
                $required = 'required';
                $rules['file_stop_payment'] = $required . '|mimes:' . $doc_arr->doc_type . '|max:' . $doc_arr->doc_size_kb . ',';
                $messages['file_stop_payment.max'] = "The file uploaded for " . $doc_arr->doc_name . " size must be less than :max KB";
                $messages['file_stop_payment.mimes'] = "The file uploaded for " . $doc_arr->doc_name . " must be of type " . $doc_arr->doc_type;
                $messages['file_stop_payment.required'] = "Document for " . $doc_arr->doc_name . " must be uploaded";
                $validator = Validator::make($request->all(), $rules, $messages, $attributes);
                if ($validator->passes()) {
                    $valid = 1;
                } else {
                    $valid = 0;
                    $return_msg = $validator->errors()->all();
                    $return_status = 0;

                    $response = array(
                        'status' => 7,
                        'msg' => $return_msg,
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Error'
                    );
                }
                // dd($valid);
                if ($valid == 1) {
                    $upload_alive_document = $request->file('file_stop_payment');
                    $img_data = file_get_contents($upload_alive_document);
                    $extension = $upload_alive_document->getClientOriginalExtension();
                    $mime_type = $upload_alive_document->getMimeType();
                    $base64 = base64_encode($img_data);

                    $tableNameDoc = Helper::getTable('', $application_id);
                    // dd($tableNameDoc['benDocTable']);
                    $insertIntoArchieve = "INSERT INTO lb_scheme.ben_attach_documents_arch(
                    application_id, beneficiary_id, document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,doc_status,action_by,action_ip_address,action_type)
                    select application_id, beneficiary_id, document_type, attched_document, created_by_level,created_at,updated_at, '" . date('Y-m-d H:i:s') . "', created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,2,action_by,action_ip_address,action_type
                    from lb_scheme." . $tableNameDoc['benDocTable'] . " where application_id = " . $application_id . " and document_type = " . $doc_arr->id;
                    // echo $insertIntoArchieve;
                    $executeInsert = DB::connection('pgsql_encwrite')->select($insertIntoArchieve);

                    if ($executeInsert) {
                        // dd($executeInsert);
                        $pension_details['attched_document'] = $base64;
                        $pension_details['document_extension'] = $extension;
                        $pension_details['document_mime_type'] = $mime_type;
                        $pension_details['updated_at'] = date('Y-m-d H:i:s');
                        $pension_details['action_by'] = Auth::user()->id;
                        $pension_details['action_ip_address'] = request()->ip();
                        $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                        if ($is_faulty == 1) {
                            // dd('faulty');
                            $docUpdate = $pension_details_encloser2->where('document_type', $doc_arr->id)->where('application_id',  $application_id)->update($pension_details);
                        } else {
                            // dd('Normal');
                            $docUpdate = $pension_details_encloser2->where('document_type', $doc_arr->id)->where('application_id',  $application_id)->update($pension_details);
                        }
                    } else {
                        $benAttachmentInsert = [
                            'application_id' => $application_id,
                            'beneficiary_id' => $beneficiary_id,
                            'document_type' => $doc_list,
                            'attched_document' => $base64,
                            'created_by_level' => $duty->mapping_level,
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => $user_id,
                            'document_extension' => $extension,
                            'document_mime_type' => $mime_type,
                            'created_by_dist_code' => $getBenDetailsObj[0]->created_by_dist_code,
                            'created_by_local_body_code' => $getBenDetailsObj[0]->created_by_local_body_code,
                            'action_by' => Auth::user()->id,
                            'action_ip_address' => request()->ip(),
                            'action_type' => class_basename(request()->route()->getAction()['controller'])
                        ];
                        // dd($benAttachmentInsert);
                        $executeInsert = $pension_details_encloser2->insert($benAttachmentInsert);
                    }
                    $updateBenPersonalDetails = [
                        'payment_suspended' => null,
                        'jnmp_remarks' => $remarks,
                        'reactive_reason' => $reactive_reason,
                    ];
                    // dd($updateBenPersonalDetails);
                    $updateBenPaymentDetails = ['ben_status' => 1];
                    $benRows = $ben_personal_details->where('beneficiary_id', $beneficiary_id)->get();
                    // dd($benRows);
                    $old_value = [];
                    $new_value = [];
                    $old_value['application_id'] = $benRows[0]->application_id;
                    $old_value['payment_suspended'] = $benRows[0]->payment_suspended;
                    $new_value['application_id'] = $benRows[0]->application_id;
                    $new_value['payment_suspended'] = null;

                    $updateBenDeatails = [
                        'beneficiary_id' => $beneficiary_id,
                        'old_data' => json_encode($old_value),
                        'new_data' => json_encode($new_value),
                        'user_id' => Auth::user()->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'update_code' => 17,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'remarks' => $remarks,
                        'reactive_reason' => $reactive_reason,
                        'next_level_role_id' => $benRows[0]->next_level_role_id,
                        'dist_code' => $benRows[0]->created_by_dist_code,
                        'local_body_code' => $benRows[0]->created_by_local_body_code,
                        'application_id' => $application_id
                    ];
                    // dd($updateBenDeatails);
                    // $benDocInsert = $pension_details_encloser2->insert($benAttachmentInsert);
                    $benPersonalUpdate = $ben_personal_details->where('beneficiary_id', $beneficiary_id)->where('application_id', $application_id)->update($updateBenPersonalDetails);
                    $benPaymentUpdate = $ben_payment_details->where('ben_id', $beneficiary_id)->where('application_id', $application_id)->where('ben_status', -94)->update($updateBenPaymentDetails);
                    $insertLog = $update_ben_details->insert($updateBenDeatails);
                }
            } else {
                $response = array(
                    'status' => 9,
                    'msg' => 'Please upload bank passbook copy.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Required'
                );
            }
            // dump($executeInsert); dump($benPersonalUpdate); dump($benPaymentUpdate); dump($insertLog); die;
            if ($executeInsert && $benPersonalUpdate && $benPaymentUpdate && $insertLog) {
                $response = array('return_status' => 1, 'title' => 'Success', 'msg' => 'Activated Successfully', 'type' => 'green', 'icon' => 'fa fa-check');
                DB::connection('pgsql_appwrite')->commit();
                DB::connection('pgsql_encwrite')->commit();
                DB::connection('pgsql_payment')->commit();
            } else {
                $response = array('return_status' => 2, 'title' => 'Error', 'msg' => 'Something Went Wrong', 'type' => 'red', 'icon' => 'fa fa-check');
                DB::connection('pgsql_appwrite')->rollback();
                DB::connection('pgsql_encwrite')->rollback();
                DB::connection('pgsql_payment')->rollback();
            }
        } catch (\Exception $e) {
            dd($e);
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_encwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            $statusCode = 400;
            $return_text = 'Error. Please try again';
            $return_msg = array("" . $return_text);
            $response = array(
                'status' => $statusCode,
                'msg' => $return_msg,
                'type' => 'red',
                'icon' => 'fa fa-warning',
                'title' => 'Warning!!'
            );
            //   $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function generateExcel(Request $request)
    {
        // echo 1;die;
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
                    // echo $district_code;die;
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                        $muncList = UrbanBody::select('urban_body_code', 'urban_body_name')->where('sub_district_code', $blockCode)->get();
                        $municipality_visible = 1;
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                        // echo $blockCode;die;
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
        $dist_code = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $gp_ward = $request->gp_ward;
        $muncid = $request->urban_code;
        $is_faulty = $request->is_faulty;
        $schemeObj = 'Lakshmir Bhandar';
        $user_msg = 'Re-activate Death Incident Beneficiary List';
        // echo $block;die;
        $query = $this->getDataquerys($district_code, $blockCode, $block, $gp_ward, $muncid, $dist_code, $is_faulty);
        $result = DB::connection('pgsql_mis')->select($query);
        // print_r($result);die;
        $excelarr[] = array(
            'Application ID',
            'Beneficiary ID',
            'Name',
            'Father Name',
            'Block/Municipality',
            'GP/Ward',
            'Aadhar Number',
            'Mobile Number',
        );

        foreach ($result as $arr) {
            $excelarr[] = array(
                'Application ID' => trim($arr->application_id),
                'Beneficiary ID' => trim($arr->beneficiary_id),
                'Name' => trim($arr->ben_fname),
                'Father Name' => trim($arr->father_name),
                'Block/Municipality' => trim($arr->block_ulb_name),
                'GP/Ward' => trim($arr->gp_ward_name),
                'Aadhar Number' => trim($arr->aadhar_no),
                'Mobile Number' => trim($arr->mobile_no),
            );
        }
        /*  $file_name = $schemeObj.' '.$user_msg .' '.  date('d/m/Y');
        Excel::create($file_name, function ($excel) use ($excelarr) {
            $excel->setTitle('Jai Bangla Duplicate Report');
            $excel->sheet('Jai Bangla Duplicate Report', function ($sheet) use ($excelarr) {
                $sheet->fromArray($excelarr, null, 'A1', false, false);
            });
        })->download('xlsx'); */

        $file_name = $schemeObj . '_' . $user_msg . '_' . date('d_m_Y');
        return Excel::download(new BankFailedExport($excelarr), $file_name . '.xlsx');
    }

    // HOD
    public function jnmpMarkedDataAtHOD()
    {
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
        $district = District::orderBy('district_name')->get();
        if ($designation_id == 'HOD') {
            return view('JnmpWithLb.linelisting_at_hod', ['districts' => $district]);
        } else {
            return redirect("/")->with('success', 'User Disabled. ');
        }
    }

    public function jnmpMarkedData(Request $request)
    {
        if ($request->ajax()) {
            $dist_code = $request->district_code;
            $query = $this->getHodDataQuery($dist_code);
            // echo $query;die();
            $result = DB::connection('pgsql_appwrite')->select($query);
            // echo '<pre>';print_r($result);die();
            return datatables()->of($result)
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function generateJnmpDataHodExcel(Request $request)
    {
        $dist_code = $request->district_code;
        $schemeObj = 'Lakshmir Bhandar';
        $user_msg = 'Re-activate Death Incident Beneficiary List';
        // echo $block;die;
        $query = $this->getHodDataQuery($dist_code);
        $result = DB::connection('pgsql_mis')->select($query);
        // print_r($result);die;
        $excelarr[] = array(
            'Sl No',
            'Application ID',
            'Beneficiary ID',
            'Name',
            'District',
            'Block/Municipality',
            'GP/Ward',
            'Mobile Number'
        );

        foreach ($result as $arr) {
            $excelarr[] = array(
                'Sl No' => $arr->sl_no,
                'Application ID' => trim($arr->application_id),
                'Beneficiary ID' => trim($arr->beneficiary_id),
                'Name' => trim($arr->fullname),
                'District' => $arr->district,
                'Block/Municipality' => trim($arr->block_ulb_name),
                'GP/Ward' => trim($arr->gp_ward_name),
                'Mobile Number' => trim($arr->mobile_no)
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

    public function getDataquerys($district_code, $blockCode, $block, $gp_ward, $muncid, $dist_code, $is_faulty)
    {
        // echo $is_faulty;die;
        if ($is_faulty == 1) {
            $query = "SELECT b.application_id AS application_id,b.beneficiary_id AS beneficiary_id,ben_fname,CONCAT(father_fname,' ',father_mname,' ',father_lname) AS father_name,block_ulb_name,gp_ward_name,aadhar_no,mobile_no FROM lb_scheme.faulty_ben_personal_details b JOIN lb_scheme.faulty_ben_contact_details c ON b.application_id = c.application_id
            WHERE b.jnmp_marked = 1 AND b.payment_suspended = 1";
        }
        if ($is_faulty == 2) {
            $query = "SELECT b.application_id AS application_id,b.beneficiary_id AS beneficiary_id,ben_fname,CONCAT(father_fname,' ',father_mname,' ',father_lname) AS father_name,block_ulb_name,gp_ward_name,aadhar_no,mobile_no FROM lb_scheme.ben_personal_details b JOIN lb_scheme.ben_contact_details c ON b.application_id = c.application_id
            WHERE b.jnmp_marked = 1 AND b.payment_suspended = 1";
        }
        if (!empty($district_code)) {
            $query .= " AND b.created_by_dist_code = " . $district_code;
        }
        if (!empty($block)) {
            $query .= " AND b.created_by_local_body_code = " . $block;
        }
        if (!empty($gp_ward)) {
            $query .= " AND c.gp_ward_code = " . $gp_ward;
        }
        if (!empty($muncid)) {
            $query .= " AND block_ulb_code = " . $muncid;
        }
        $query .= " ORDER BY ben_fname";
        // echo $query;die();
        return $query;
    }

    private function getHodDataQuery($dist_code)
    {
        if ($dist_code) {
            $whereCon = "WHERE created_by_dist_code = " . $dist_code;
        } else {
            $whereCon = "";
        }

        $query = "SELECT row_number() over () AS sl_no, t.application_id, t.beneficiary_id, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(ben_fname,'')||' '||COALESCE(ben_mname,'')||' '||COALESCE(ben_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS fullname, d.district_name AS district, m.block_ulb_name, m.gp_ward_name, t.mobile_no
        FROM (
            SELECT application_id, beneficiary_id, ben_fname, ben_mname, ben_lname, created_by_dist_code, mobile_no FROM lb_scheme.ben_personal_details WHERE payment_suspended = 1
            UNION ALL
            SELECT application_id, beneficiary_id, ben_fname, ben_mname, ben_lname, created_by_dist_code, mobile_no FROM lb_scheme.faulty_ben_personal_details WHERE payment_suspended = 1
        ) t
        JOIN (
            SELECT application_id, block_ulb_name, gp_ward_name FROM lb_scheme.ben_contact_details
            UNION ALL
            SELECT application_id, block_ulb_name, gp_ward_name FROM lb_scheme.faulty_ben_contact_details
        ) m ON m.application_id = t.application_id
        JOIN public.m_district d ON t.created_by_dist_code = d.district_code " . $whereCon . " ORDER BY d.district_name";
        return $query;
    }
}
