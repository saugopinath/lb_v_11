<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\District;
use App\Scheme;
use Redirect;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use DateTime;
use Config;
use App\Configduty;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithProperties;
use App\DataSourceCommon;
use App\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use App\RejectRevertReason;
use App\AadharDuplicateTrail;
use App\SubDistrict;
use App\Taluka;
use App\DocumentType;
use Illuminate\Support\Facades\Storage;
use App\SchemeDocMap;
use File;
use App\BankDetails;
use App\UrbanBody;
use App\Ward;
use App\GP;
use Carbon\Carbon;
use App\Helpers\Helper;

// ini_set('max_execution_time', '300');
// ini_set('memory_limit', '30MB');
class ApprovedVerificationPendingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->scheme_id = 20;

    }

    public function index()
    {
        $designation = Auth::user()->designation_id;
        $userId = Auth::user()->id;
        $duty = Configduty::where('user_id', $userId)->where('district_code', 315)->first();
        if (($designation == 'Approver' || $designation == 'Delegated Approver') && $duty->district_code == 315) {
            $gpWardLists = Ward::where('district_code', 315)
                ->select('urban_body_ward_code', 'urban_body_ward_name')
                ->orderBy('urban_body_ward_no', 'asc')
                ->get();
            return view('Beneficiary-List.beneficiary_list', ['gpWardLists' => $gpWardLists, 'dist_code' => $duty->district_code]);
        } else {
            return redirect("/")->with('success', 'User Disabled. ');
        }
    }

    public function getApprovedVerificationPendingList(Request $request)
    {
        $district_code = $request->district;
        $searchFor = $request->searchFor;
        $gp_ward = $request->gp_ward;

        if ($request->ajax()) {
            $query = $this->getDataquerys($district_code, $searchFor, $gp_ward);
            //   echo $query;die();
            $result = DB::connection('pgsql_appwrite')->select($query);
            // echo '<pre>';print_r($result);die();
            return datatables()->of($result)
                ->addColumn('mask_bank_code', function ($result) {
                    if (!empty($result->bank_code)) {
                        return ('**********' . substr($result->bank_code, 4));
                    } else {
                        return '';
                    }

                })
                ->rawColumns(['mask_bank_code'])
                ->make(true);
        }
    }

    public function generateExcelApprovedVerificationPendingList(Request $request)
    {
        try {
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', 300);
            $district_code = $request->district;
            $searchFor = $request->search_for;
            $gp_ward = $request->gp_ward;
            $download_reason = $request->download_reason;
            //dd($gp_ward);
            // dump($district_code); dump($searchFor); dump($gp_ward); dd($download_reason);
            $schemeObj = 'Lakshmir Bhandar';
            if ($searchFor == 1) {
                $user_msg = 'Approved Beneficiary List';
            } else {
                $user_msg = 'Verification Pending Beneficiary List';
            }
            // echo $block;die;
            $file_name = $schemeObj . ' ' . $user_msg . ' ' . date('d/m/Y');
            $metaData = [
                'Author' => Auth::user()->username,
                'Title' => $file_name,
                'Subject' => 'Download of ' . $user_msg,
                'Comments' => $download_reason,
                'Last Saved By' => Auth::user()->id,
                'Company' => 'WCD & SW',
                'Manager' => 'NIC, WB'
            ];
            //$jsonEncode = json_encode($metaData); dump($metaData); dd($jsonEncode);
            $downloadLog = [
                'user_id' => Auth::user()->id,
                'designation_id' => Auth::user()->designation_id,
                'reason' => $download_reason,
                'ip_address' => $request->ip(),
                'module_name' => class_basename(request()->route()->getAction()['controller']),
                'metadate' => json_encode($metaData),
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $is_saved = DB::connection('pgsql_appwrite')->table('lb_scheme.download_log')->insert($downloadLog);
            $query = $this->getDataquerys($district_code, $searchFor, $gp_ward);
            // dd($query->toSql());
            // $result =array();
            $result = DB::connection('pgsql_appwrite')->select($query);
            $excelarr[] = array(
                'Application ID',
                'Beneficiary Name',
                'Father Name',
                'DOB(YYYY-MM-DD)',
                'Mobile Number',
                'Block/Municipality',
                'GP/Ward',
                'Address',
                'Aadhaar Number',
                'A/C Number',
                'IFSC Code',
                'Bank Name',
                'Branch Name'
            );

            foreach ($result as $arr) {
                $i = 0;
                $aadharNo = '';
                if ($arr->encoded_aadhar != '' && !is_null($arr->encoded_aadhar)) {
                    $aadharNo = Crypt::decryptString($arr->encoded_aadhar);
                }
                $excelarr[] = array(
                    'Application ID' => trim($arr->application_id),
                    'Beneficiary Name' => trim($arr->name),
                    'Father Name' => trim($arr->father_name),
                    'DOB(YYYY-MM-DD)' => trim($arr->dob),
                    'Mobile Number' => trim($arr->mobile_no),
                    'Block/Municipality' => trim($arr->block_ulb_name),
                    'GP/Ward' => trim($arr->gp_ward_name),
                    'Address' => trim($arr->address),
                    'Aadhaar Number' => $aadharNo,
                    'A/C Number' => trim($arr->bank_code),
                    'IFSC Code' => trim($arr->bank_ifsc),
                    'Bank Name' => trim($arr->bank_name),
                    'Branch Name' => trim($arr->branch_name),
                );
            }
            if ($is_saved) {
                Excel::create($file_name, function ($excel) use ($excelarr, $file_name, $user_msg, $download_reason, $request) {
                    $excel->setTitle($file_name);
                    $excel->setCreator(Auth::user()->username);
                    $excel->setCompany('WCD & SW');
                    $excel->setManager('WCD & SW');
                    $excel->setSubject('Download of ' . $user_msg);
                    $excel->setDescription($download_reason);
                    $excel->setKeywords('Download from IP Address:' . $request->ip());
                    $excel->setLastModifiedBy('User ID of the user:' . Auth::user()->id);
                    $excel->sheet('Approved & Verified List Report', function ($sheet) use ($excelarr) {
                        $sheet->fromArray($excelarr, null, 'A1', false, false);
                    });
                })->download('xlsx');
            }
        } catch (\Exception $e) {
            //dd($e);
        }
    }

    private function getDataquerys($district_code, $searchFor, $gp_ward)
    {
        if ($searchFor == 1) {
            $query = "select * from
(
 SELECT  application_id, 
			CONCAT('House Premise No:-',' ',trim(bc.house_premise_no),' ','P.S:-',' ',trim(bc.police_station),', ', 'Village_Town_City:-',' ',trim(bc.village_town_city),', ','P.O:-',' ',trim(bc.post_office),', ','Pincode:-',' ',
			bc.pincode) AS address, bc.block_ulb_name, bc.gp_ward_name from lb_Scheme.ben_contact_details as bc 
			where gp_ward_code=" . $gp_ward . "
) as A JOIN
(
SELECT bp.beneficiary_id, bp.application_id, 
TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(bp.ben_fname,'')||' '||COALESCE(bp.ben_mname,'')||' '||COALESCE(bp.ben_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS name, 
TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(bp.father_fname,'')||' '||COALESCE(bp.father_mname,'')||' '||COALESCE(bp.father_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS father_name, dob, mobile_no, aadhar_no
FROM lb_Scheme.ben_personal_details as bp where created_by_dist_code = " . $district_code . " and next_level_role_id=0  and payment_suspended IS null
) as B ON A.application_id=B.application_id 
JOIN
(
SELECT bb.beneficiary_id, bb.application_id,bb.bank_code, bb.bank_ifsc, bank_name, branch_name
FROM lb_Scheme.ben_bank_details as bb where created_by_dist_code = " . $district_code . "
) as C ON A.application_id=C.application_id
LEFT JOIN
(
SELECT ba.beneficiary_id, ba.application_id, ba.encoded_aadhar
FROM lb_scheme.ben_aadhar_details as ba where created_by_dist_code = " . $district_code . "
) as D ON A.application_id=D.application_id
UNION 
select * from
(
 SELECT  application_id, 
			CONCAT('House Premise No:-',' ',trim(bc.house_premise_no),' ','P.S:-',' ',trim(bc.police_station),', ', 'Village_Town_City:-',' ',trim(bc.village_town_city),', ','P.O:-',' ',trim(bc.post_office),', ','Pincode:-',' ',
			bc.pincode) AS address, bc.block_ulb_name, bc.gp_ward_name from lb_Scheme.faulty_ben_contact_details as bc 
			where  gp_ward_code=" . $gp_ward . "
) as AA JOIN
(
SELECT bp.beneficiary_id, bp.application_id, 
TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(bp.ben_fname,'')||' '||COALESCE(bp.ben_mname,'')||' '||COALESCE(bp.ben_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS name, 
TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(bp.father_fname,'')||' '||COALESCE(bp.father_mname,'')||' '||COALESCE(bp.father_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS father_name, dob, mobile_no, aadhar_no
FROM lb_Scheme.faulty_ben_personal_details as bp where created_by_dist_code = " . $district_code . " and next_level_role_id=0  and payment_suspended IS null
) as BB ON AA.application_id=BB.application_id
JOIN
(
SELECT bb.beneficiary_id, bb.application_id,bb.bank_code, bb.bank_ifsc, bank_name, branch_name
FROM lb_Scheme.faulty_ben_bank_details as bb where created_by_dist_code = " . $district_code . "
) as CC ON AA.application_id=CC.application_id
LEFT JOIN
(
SELECT ba.beneficiary_id, ba.application_id, ba.encoded_aadhar
FROM lb_scheme.ben_aadhar_details as ba where created_by_dist_code = " . $district_code . "
) as DD ON AA.application_id=DD.application_id";
        } else {
            $query = "SELECT bp.application_id, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(bp.ben_fname,'')||' '||COALESCE(bp.ben_mname,'')||' '||COALESCE(bp.ben_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS name, TRIM(regexp_replace((regexp_replace(COALESCE(REPLACE(COALESCE(bp.father_fname,'')||' '||COALESCE(bp.father_mname,'')||' '||COALESCE(bp.father_lname,''),CHR(160),''),''), '\r|\n|', '', 'g')), '\s+', ' ', 'g')) AS father_name,bp.dob as dob, bp.mobile_no as mobile_no, bp.aadhar_no as aadhar_no, CONCAT('House Premise No:-',' ',trim(bc.house_premise_no),' ','P.S:-',' ',trim(bc.police_station),', ', 'Village_Town_City:-',' ',trim(bc.village_town_city),', ','P.O:-',' ',trim(bc.post_office),', ','Pincode:-',' ',bc.pincode) as address, bc.block_ulb_name, bc.gp_ward_name, bb.bank_code, bb.bank_ifsc, ba.encoded_aadhar, bp.dob, bb.bank_name, bb.branch_name, bp.mobile_no
            FROM lb_Scheme.draft_ben_personal_details bp JOIN lb_scheme.draft_ben_contact_details bc ON bp.application_id = bc.application_id
            JOIN lb_scheme.draft_ben_bank_details bb ON bp.application_id = bb.application_id
            LEFT JOIN lb_Scheme.ben_aadhar_details ba ON bp.application_id = ba.application_id
            WHERE bp.created_by_dist_code = " . $district_code . " AND bc.created_by_dist_code = " . $district_code . " AND bb.created_by_dist_code = " . $district_code . " AND ba.created_by_dist_code = " . $district_code . " AND bp.next_level_role_id IS null";
            if (!empty($gp_ward)) {
                $query .= " AND bc.gp_ward_code = " . $gp_ward . " ";
            }
            $query .= " ORDER BY gp_ward_name";
        }
        return $query;
    }
}
