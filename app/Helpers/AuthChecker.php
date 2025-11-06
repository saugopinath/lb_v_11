<?php

namespace App\Helpers;

use App\Models\Configduty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Scheme;
use phpDocumentor\Reflection\Types\Self_;

class AuthChecker
{
    /**
     * Checks and processes the operator's data or permissions.
     *
     * @return mixed
     */
    public static function OperatorChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Operator') {
            return true;
        } else {
            return false;
        }
    }

    public static function DelegatedOperatorChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Delegated Operator') {
            return true;
        } else {
            return false;
        }
    }


    public static function VerifierChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Verifier') {
            return true;
        } else {
            return false;
        }
    }

    public static function DelegatedVerifierChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Delegated Verifier') {
            return true;
        } else {
            return false;
        }
    }

    public static function ApproverChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Approver') {
            return true;
        } else {
            return false;
        }
    }

    public static function DelegatedApproverChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Delegated Approver') {
            return true;
        } else {
            return false;
        }
    }

    public static function HODChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'HOD') {
            return true;
        } else {
            return false;
        }
    }

    public static function AdminChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Admin') {
            return true;
        } else {
            return false;
        }
    }
    public static function HOPChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'HOP') {
            return true;
        } else {
            return false;
        }
    }
    public static function CorpChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Corp') {
            return true;
        } else {
            return false;
        }
    }
    public static function SPDashboardChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'SPDashboard') {
            return true;
        } else {
            return false;
        }
    }
    public static function SPNodalChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'SPNodal') {
            return true;
        } else {
            return false;
        }
    }

    public static function DDOChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'DDO') {
            return true;
        } else {
            return false;
        }
    }


    public static function StatusCheckerFieldChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'StatusCheckerField') {
            return true;
        } else {
            return false;
        }
    }

    public static function SpecialStatusCheckChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'SpecialStatusCheck') {
            return true;
        } else {
            return false;
        }
    }

    //AuditOfficerChecker
    public static function AuditOfficerChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'AuditOfficer') {
            return true;
        } else {
            return false;
        }
    }


    //Special LAO Checker


    public static function SpecialLAOChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Special LAO') {
            return true;
        } else {
            return false;
        }
    }


    public static function StatusCheckerDistrictChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'StatusCheckerDistrict') {
            return true;
        } else {
            return false;
        }
    }
    public static function DashboardChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'Dashboard') {
            return true;
        } else {
            return false;
        }
    }
    public static function MisStateChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'MisState') {
            return true;
        } else {
            return false;
        }
    }


    public static function ReportChecker()
    {
        // Fixed logic to call the static methods properly using `self::`
        if (self::OperatorPermission() || self::VerifierPermission() || self::ApproverPermission() || self::HODChecker()) {
            return true;
        } else {
            return false;
        }
    }
    public static function ReportCheckerCommon()
    {
        // Fixed logic to call the static methods properly using `self::`
        if (self::OperatorPermission() || self::VerifierPermission() || self::ApproverPermission() || self::HODChecker() || self::AdminChecker() || self::HOPChecker() || self::StatusCheckerDistrictChecker() || self::SpecialStatusCheckPermission() || self::DashboardChecker() || self::MisStateChecker() || self::MisUserStateChecker() ||self::MisUserBlockChecker()||self::MisUserDistrictChecker() || self::MisUserSubDivChecker() || self::MisUserStateChecker()|| self::WBSPOChecker()) {
            return true;
        } else {
            return false;
        }
    }
    public static function WorkflowChecker()
    {
        if (self::OperatorChecker() || self::VerifierChecker() || self::ApproverChecker()) {
            return true;
        } else {
            return false;
        }
    }
    public static function getUserId()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return $user->id;
        } else {
            return redirect('/login');
        }
    }

    // public static function getDesignationId()
    // {

    //     $designation_id = Auth::user()->designation_id;
    //     return $designation_id;
    // }
    // public static function getDesignation()
    // {
    //     $designation_id = Auth::user()->designation_id;

    //     if (in_array($designation_id, [13, 24])) {
    //         return 'Operator';
    //     } elseif (in_array($designation_id, [14, 25])) {
    //         return 'Verifier';
    //     } elseif (in_array($designation_id, [15, 26])) {
    //         return 'Approver';
    //     } elseif (in_array($designation_id, [17])) {
    //         return 'HOD';
    //     } elseif (in_array($designation_id, [12])) {
    //         return 'Admin';
    //     } elseif (in_array($designation_id, [18])) {
    //         return 'DDO';
    //     } else {
    //         return null;
    //     }
    // }


    public static function OperatorPermission()
    {
        if (self::OperatorChecker() || self::DelegatedOperatorChecker())
            return true;
        else
            return false;
    }

    public static function VerifierPermission()
    {
        if (self::DelegatedVerifierChecker() || self::VerifierChecker())
            return true;
        else
            return false;
    }

    public static function ApproverPermission()
    {
        if (self::DelegatedApproverChecker() || self::ApproverChecker())
            return true;
        else
            return false;
    }
    public static function SpecialStatusCheckPermission()
    {
        if (self::SpecialStatusCheckChecker())
            return true;
        else
            return false;
    }
    public static function WBSPOChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        if ($designation_id === 'WBSPO') {
            return true;
        } else {
            return false;
        }
    }

    public static function MisUserStateChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        $dutyObj = Configduty::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($designation_id === 'MIS User' && $dutyObj->district_code == null && $dutyObj->taluka_code == null && $dutyObj->urban_body_code == null) {
            return true;
        } else {
            return false;
        }
    }

    public static function MisUserDistrictChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        $dutyObj = Configduty::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($designation_id === 'MIS User' && $dutyObj->district_code != null && $dutyObj->taluka_code == null && $dutyObj->urban_body_code == null) {
            return true;
        } else {
            return false;
        }
    }


    public static function MisUserSubDivChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        $dutyObj = Configduty::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($designation_id === 'MIS User' && $dutyObj->district_code != null && $dutyObj->taluka_code == null && $dutyObj->urban_body_code != null) {
            return true;
        } else {
            return false;
        }
    }

    public static function MisUserBlockChecker()
    {
        $user = Auth::user();
        if (empty($user)) {
            return redirect('/login');
        }
        $designation_id = $user->designation_id;
        $dutyObj = Configduty::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($designation_id === 'MIS User' && $dutyObj->district_code != null && $dutyObj->taluka_code != null && $dutyObj->urban_body_code == null) {
            return true;
        } else {
            return false;
        }
    }

}

