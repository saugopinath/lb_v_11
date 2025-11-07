<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\District;
use App\SubDistrict;
use App\Taluka;
use Redirect;
use Auth;
use Config;
use URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\getModelFunc;
use App\DataSourceCommon;
use App\Helpers\Helper;
use App\RejectRevertReason;
use Illuminate\Support\Facades\Crypt;

class TrackApplicantController extends Controller
{

    public function __construct()
    {
      
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
   


    function applicantTrack()
    {
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
            'publicView/publicApplicationTrack',
            [
                'schemelist' => $schemelist,
                'scheme_id' => $scheme_id,
                'application_type_text' => $application_type_text,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'currentFinYear' => $year
            ]
        );
    }
    public function refereshCapcha(){
        return captcha_img('flat');
    } 

    
}
