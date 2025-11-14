<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\District;
use App\SubDistrict;
use App\Taluka;
use Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\getModelFunc;
use App\DataSourceCommon;
use App\Helpers\Helper;
use App\RejectRevertReason;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
class PensionCommonController extends Controller
{
    public $scheme_id;
    public $source_type;
    public function __construct()
    {
        //$this->middleware('auth');
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
    public function shemeSessionCheck(Request $request)
    {
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        // $distCode=NULL;
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] ==  $this->scheme_id) {
                $is_active = 1;
                $request->session()->put('level', $roleObj['mapping_level']);
                $distCode = $roleObj['district_code'];
                $request->session()->put('distCode', $roleObj['district_code']);
                $request->session()->put('scheme_id',  $this->scheme_id);
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
            // $ben_table = 'dist_' . $distCode . '.beneficiary';
            return true;
        } else {
            return false;
        }
    }


    function applicantTrack()
    {
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $application_type_text = "Track Applicant";
        $schemelist = collect([]);
        $scheme_id = $this->scheme_id;
        $errormsg = Config::get('constants.errormsg');
        if (date('m') > 3) {
            $year = date('Y') . "-" . (date('Y') + 1);
        } else {
            $year = (date('Y') - 1) . "-" . date('Y');
        }
        return view(
            'pensionForm/ApplicantTrack',
            [
                'schemelist' => $schemelist,
                'scheme_id' => $scheme_id,
                'user_id' => $user_id,
                'application_type_text' => $application_type_text,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'currentFinYear' => $year
            ]
        );
    }
  
}
