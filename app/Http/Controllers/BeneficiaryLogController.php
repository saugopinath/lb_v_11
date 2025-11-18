<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Configduty;
use App\District;
use App\UrbanBody;
use App\SubDistrict;
use App\Taluka;
use App\Ward;
use App\GP;
use App\User;
use Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Validator;
use DateTime;
use App\Scheme;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\DataSourceCommon;
use App\getModelFunc;
use App\DocumentType;
use App\DsPhase;
use Maatwebsite\Excel\Facades\Excel;

class BeneficiaryLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;
        set_time_limit(300);
    }

    public function index(Request $request)
    {
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
        if (!empty($district_code)) {
            $district_visible = 0;
            $district_code_fk = $district_code;
        } else {
            $district_code_fk = NULL;
        }
        // dd($district_code_fk);
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
            'pensionreport.beneficiaryLogReport',
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
                'gpList' => $gpList,
                'muncList' => $muncList,
                'reactive_reasons' => $reactive_reasons
            ]
        );
    }

    // public function getBeneficiaryLog(Request $request)
    // {
    //     $dist_code = $request->district;
    //     $district_flag = $request->district_flag;
    //     $urban_code = $request->urban_code;
    //     $block = $request->block;
    //     $gp_ward = $request->gp_ward;
    //     $muncid = $request->muncid;
    //     // $perPage = $request->get('length', 10);
    //     // $start   = $request->get('start', 0);
    //     if ($request->ajax()) {
    //         $query = $this->getDataquerys($district_flag, $block, $gp_ward, $muncid, $dist_code);
    //       //   echo $query;die();
    //         $result = DB::connection('pgsql_appwrite')->select($query);
    //         // $result = $data->skip($start)->take($perPage)->get();
    //         // echo '<pre>';print_r($result);die();
    //         return datatables()->of($result)
    //         ->addColumn('entry_details', function($result){
    //             $entryDetails = $this->logDetailsQuery($result->application_id, 'E');
    //             return $entryDetails;
    //         })
    //         ->addColumn('verification_details', function($result){
    //             $verificationDetails = $this->logDetailsQuery($result->application_id, 'V');
    //             return $verificationDetails;
    //         })
    //         ->addColumn('approval_details', function($result){
    //             $approvalDetails = $this->logDetailsQuery($result->application_id, 'A');
    //             return $approvalDetails;
    //         })
    //         ->rawColumns(['entry_details', 'verification_details', 'approval_details'])
    //         ->make(true);
    //     }
    // }

    public function getBeneficiaryLog(Request $request)
    {
        // dd($request->all());
        $dist_code      = $request->district;
        $district_flag  = $request->district_flag;
        $urban_code     = $request->urban_code;
        $block          = $request->block;
        $gp_ward        = $request->gp_ward;
        $muncid         = $request->muncid;
        $searchType     = $request->search_type;
        $isFaulty     = $request->is_faulty;
        $perPage = $request->get('length', 10);
        $start   = $request->get('start', 0);
        $page    = ($start / $perPage) + 1;
        $searchvalue = $request->search['value'];

        if ($request->ajax()) {

            $baseQuery = $this->getDataquerys($district_flag, $block, $gp_ward, $muncid, $dist_code, $isFaulty, $searchType, $searchvalue);


            
            $countQuery = "SELECT COUNT(*) as total FROM ({$baseQuery}) as temp";
            $totalData  = DB::connection('pgsql_appwrite')->selectOne($countQuery);
            $total      = $totalData->total;

            
            $paginatedQuery = $baseQuery . " OFFSET {$start} LIMIT {$perPage}";
            $result = DB::connection('pgsql_appwrite')->select($paginatedQuery);
            // dd($result);
            
            return datatables()
                ->of($result)
                ->addColumn('entry_details', function($result) use ($isFaulty){
                    $op_type = '';
                    if ($isFaulty == 0) {
                        $op_type = 'E';
                    } else {
                        $op_type = 'FE';
                    }
                    return $this->logDetailsQuery($result->application_id, $op_type);
                })
                ->addColumn('verification_details', function($result) use ($isFaulty){
                    $op_type = '';
                    if ($isFaulty == 0) {
                        $op_type = 'V';
                    } else {
                        $op_type = 'FV';
                    }
                    return $this->logDetailsQuery($result->application_id, $op_type);
                })
                ->addColumn('approval_details', function($result) use ($isFaulty){
                    $op_type = '';
                    if ($isFaulty == 0) {
                        $op_type = 'A';
                    } else {
                        $op_type = 'FA';
                    }
                    return $this->logDetailsQuery($result->application_id, $op_type);
                })
                ->rawColumns(['entry_details', 'verification_details', 'approval_details'])
                ->setTotalRecords($total)   // total records without filter
                ->setFilteredRecords($total) // you can change if search applied
                ->skipPaging(false) // allow DT to handle paging
                ->make(true);
        }
    }


    public function getDataquerys($district_flag, $block, $gp_ward, $muncid, $dist_code, $isFaulty, $searchType, $searchvalue)
    {
        $is_reject = 0;
        if ($searchType == 1) {
            $normal_ben_table = "lb_scheme.ben_personal_details";
            $normal_contact_table = "lb_scheme.ben_contact_details";
            $faulty_ben_table = "lb_scheme.faulty_ben_personal_details";
            $faulty_contact_table = "lb_scheme.faulty_ben_contact_details";
            $next_level_role_id = 0;
        } elseif ($searchType == 2) {
            $normal_ben_table = "lb_scheme.draft_ben_personal_details";
            $normal_contact_table = "lb_scheme.draft_ben_contact_details";
            $faulty_ben_table = "lb_scheme.faulty_draft_ben_personal_details";
            $faulty_contact_table = "lb_scheme.faulty_draft_ben_contact_details";
            $next_level_role_id = 43;
        } elseif ($searchType == 3) {
            $is_reject = 1;
        } 
        if ($is_reject == 0) {
            if ($isFaulty == 0) {
                // dump('Normal');
            $query = "SELECT bp.application_id, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(ben_fname,'')||' '||COALESCE(ben_mname,'')||' '||COALESCE(ben_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS name, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(father_fname,'')||' '||COALESCE(father_mname,'')||' '||COALESCE(father_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS father_name, bp.created_by_dist_code, bp.created_by_local_body_code
            FROM ".$normal_ben_table." bp JOIN ".$normal_contact_table." bc ON bp.application_id = bc.application_id WHERE bp.next_level_role_id = ".$next_level_role_id;
            } else {
                // dump('Faulty');
                $query = "SELECT bp.application_id, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(ben_fname,'')||' '||COALESCE(ben_mname,'')||' '||COALESCE(ben_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS name, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(father_fname,'')||' '||COALESCE(father_mname,'')||' '||COALESCE(father_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS father_name, bp.created_by_dist_code, bp.created_by_local_body_code
                FROM ".$faulty_ben_table." bp JOIN ".$faulty_contact_table." bc ON bp.application_id = bc.application_id WHERE bp.next_level_role_id = ".$next_level_role_id;
            }
            if(!empty($dist_code)){
                $query .= " AND bp.created_by_dist_code =". $district_flag;
            }
            if (!empty($block)) {
                $query .= " AND bp.created_by_local_body_code =". $block;
            }
            if (!empty($gp_ward)) {
                $query .= " AND bc.gp_ward_code = ". $gp_ward;
            }
            if (!empty($muncid)) {
                $query .= " AND bc.block_ulb_code = ". $muncid;
            }
            if (!empty($searchvalue)){
                $query .= " AND bp.application_id = ". $searchvalue;
            }
        } else {
            
            $query = "SELECT bp.application_id, bp.beneficiary_id, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(ben_fname,'')||' '||COALESCE(ben_mname,'')||' '||COALESCE(ben_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS name, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(father_fname,'')||' '||COALESCE(father_mname,'')||' '||COALESCE(father_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS father_name, bp.created_by_dist_code, bp.created_by_local_body_code
            FROM lb_scheme.ben_reject_details AS bp WHERE (1 = 1) ";
            if(!empty($dist_code)){
                $query .= " AND bp.created_by_dist_code =". $district_flag;
            }
            if (!empty($block)) {
                $query .= " AND bp.created_by_local_body_code =". $block;
            }
            if (!empty($gp_ward)) {
                $query .= " AND bp.gp_ward_code = ". $gp_ward;
            }
            if (!empty($muncid)) {
                $query .= " AND bp.block_ulb_code = ". $muncid;
            }
            if (!empty($searchvalue)){
                $query .= " AND bp.application_id = ". $searchvalue;
            }
        }
        
        
        
        
        // $query .= "LIMIT 25";
        // dd($query);
        return $query;
    }

    public function logDetailsQuery($application_id, $op_type)
    {
        $returnText = '';
        if ($op_type == 'E' || $op_type == 'FE') {
            $faultyOpType = 'FE';
            $returnText = 'Entry Details=> ';
        } elseif ($op_type == 'V' || $op_type == 'FV') {
            $faultyOpType = 'FV';
            $returnText = 'Verification Details=> ';
        } elseif ($op_type == 'A' || $op_type == 'FA') {
            $faultyOpType = 'FA';
            $returnText = 'Approval Details=> ';
        }
        
        // dd($application_id);
        $dataFound = 0;
        $query = "SELECT op_type, aa.created_at, created_by FROM lb_Scheme.ben_accept_reject_info aa WHERE aa.application_id IN(". $application_id.") AND aa.op_type ='". $op_type."' ORDER BY aa.created_at DESC LIMIT 1";
        $entryDetailsResult = DB::connection('pgsql_appread')->select($query);
        if (empty($entryDetailsResult)) {
            $query = "SELECT op_type, aa.created_at, created_by FROM lb_Scheme.ben_accept_reject_info aa WHERE aa.application_id IN(". $application_id.") AND aa.op_type ='". $faultyOpType."' ORDER BY aa.created_at DESC LIMIT 1";
        $entryDetailsResult = DB::connection('pgsql_appread')->select($query);
        if (empty($entryDetailsResult)) {
            $returnText .= 'Not Available';
        } else {
            $dataFound = 1;
        }
            
        } else {
            $dataFound = 1;
        }
        if ($dataFound == 1) {
            $countData = DB::connection('pgsql_appread')->table('public.users_audit_trail')->where('unique_id', $entryDetailsResult[0]->created_by)->where('operation_time', '>', $entryDetailsResult[0]->created_at)->count();
            if ($countData > 0) {
                $query1 = "SELECT old_user_data::json->>'designation_id' AS designation_id,old_user_data::json->>'mobile_no' AS mobile_no,old_user_data::json->>'username' AS username,old_user_data::json->>'email' AS email FROM public.users_audit_trail WHERE unique_id=".$entryDetailsResult[0]->created_by." AND operation_time>'".$entryDetailsResult[0]->created_at."' ORDER BY operation_time LIMIT 1";
            } else {
                $query1 = "SELECT designation_id,mobile_no,username,email FROM public.users WHERE id=".$entryDetailsResult[0]->created_by." AND is_active=1";
            }
            $entryResult = DB::connection('pgsql_appread')->select($query1);
            if (!empty($entryResult)) {
                $returnText .= 'Role - '.$entryResult[0]->designation_id . ', Mobile No. - ' . $entryResult[0]->mobile_no . ', Username - ' . $entryResult[0]->username . ', Email ID -' . $entryResult[0]->email;
            } else {
                // dd('456');
                $returnText .= 'Not Available';
            }
        }
        /*else {
            $countData = DB::connection('pgsql_appread')->table('public.users_audit_trail')->where('unique_id', $entryDetailsResult[0]->created_by)->where('operation_time', '>', $entryDetailsResult[0]->created_at)->count();
            if ($countData > 0) {
                $query1 = "SELECT old_user_data::json->>'designation_id' AS designation_id,old_user_data::json->>'mobile_no' AS mobile_no,old_user_data::json->>'username' AS username,old_user_data::json->>'email' AS email FROM public.users_audit_trail WHERE unique_id=".$entryDetailsResult[0]->created_by." AND operation_time>'".$entryDetailsResult[0]->created_at."' ORDER BY operation_time LIMIT 1";
            } else {
                $query1 = "SELECT designation_id,mobile_no,username,email FROM public.users WHERE id=".$entryDetailsResult[0]->created_by." AND is_active=1";
            }
            $entryResult = DB::connection('pgsql_appread')->select($query1);
            if (!empty($entryResult)) {
                $returnText .= 'Role - '.$entryResult[0]->designation_id . ', Mobile No. - ' . $entryResult[0]->mobile_no . ', Username - ' . $entryResult[0]->username . ', Email ID -' . $entryResult[0]->email;
            } else {
                // dd('456');
                $returnText .= 'Not Available';
            }
        }*/
        return $returnText;        
    }
}
