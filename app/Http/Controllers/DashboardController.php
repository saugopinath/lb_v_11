<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\Configduty;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\MapLavel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Show the login form.
     */
    
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;

        $role = [];

        $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->get();
        foreach ($duty as $dutyObj) {
        
        $mapArr = MapLavel::where('scheme_id', $dutyObj->scheme_id)->where('role_name', Auth::user()->designation_id)->where('stack_level', $dutyObj->mapping_level)->get(['id', 'role_name', 'scheme_id', 'parent_id', 'is_final', 'stack_level', 'is_first', 'role_id'])->toArray();
        if (count($mapArr) > 0) {
            $newArr = array_merge($mapArr[0], ['district_code' => $dutyObj->district_code, 'mapping_level' => $dutyObj->mapping_level, 'taluka_code' => $dutyObj->taluka_code, 'urban_body_code' => $dutyObj->urban_body_code, 'is_urban' => $dutyObj->is_urban]);
            array_push($role, $newArr);
        }
        }
        $request->session()->put('role', $role);
        return view('dashboard',['type' => '1','op_type' => 1,'row' => null]);
    }
   
}
