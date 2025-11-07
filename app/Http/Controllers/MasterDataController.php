<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\DocumentType;
use App\SchemeDocMap;
use App\Models\Scheme;
use App\Models\District;
use App\Models\Assembly;
use App\Models\Taluka;
use App\Models\UrbanBody;
use App\Models\Ward;
use App\Models\GP;
use App\Models\User;
use App\Models\SchemeCommentInfo;
use App\Models\Configduty;
use App\Models\MapLavel;
use App\Models\BankDetails;
use Redirect;
use Auth;
use Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use DateTime;

class MasterDataController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function getAssemlies(Request $request)
    {
        $assembly_list = Cache::rememberForever('master_assemblies', function () {
            return Assembly::all();
        });
        $district_code = $request->district_code;
        $response = array();
        if (!empty($district_code)  && is_numeric($district_code)) {
            $response = $assembly_list->where('district_code', $district_code)->pluck('ac_name', 'ac_no');
            $statusCode = 200;
        } else
            $statusCode = 400;
        return response()->json($response, $statusCode);
    }
    public function getUrban(Request $request)
    {
        $response = array();
        $district_code = $request->district_code;
        if (!empty($district_code)  && is_numeric($district_code)) {
            $urban_list = UrbanBody::where('district_code', $request->district_code)->get();

            $urban_list = $urban_list->filter(function ($item) use ($district_code) {
                // dump($item->toArray());
                return  $item['district_code'] == $district_code;
            })->map(function ($item) {
                $item['block_name'] = $item['urban_body_name'];
                $item['block_code'] = $item['urban_body_code'];
                return  $item;
            })->pluck('block_name', 'block_code')->toArray();
            $response = $urban_list;
            $statusCode = 200;
        } else
            $statusCode = 400;
        return response()->json($response, $statusCode);
    }
    public function getTaluka(Request $request)
    {
        $district_code = $request->district_code;
        $response = array();
        if (!empty($district_code)  && is_numeric($district_code)) {
            $taluka_list = Taluka::where('district_code', $request->district_code)->get();

            $taluka_list = $taluka_list->filter(function ($item) use ($district_code) {
                // dump($item->toArray());
                return  $item['district_code'] == $district_code;
            })->pluck('block_name', 'block_code')->toArray();
            $response = $taluka_list;
            $statusCode = 200;
        } else
            $statusCode = 400;
        return response()->json($response, $statusCode);
    }
    public function getWard(Request $request)
    {

        $response = array();
        $block = $request->block_code;

        if (!empty($block)  && is_numeric($block)) {
            $ward_list = Ward::where('urban_body_code', $request->block_code)->get();

            $ward_list = $ward_list->filter(function ($item) use ($block) {
                // dump($item->toArray());
                return  $item['urban_body_code'] == $block;
            })->map(function ($item) {
                $item['gp_ward_code'] = $item['urban_body_ward_code'];
                $item['gp_ward_name'] = $item['urban_body_ward_name'];
                return  $item;
            })->pluck('gp_ward_name', 'gp_ward_code')->toArray();
            $response = $ward_list;
            $statusCode = 200;
        } else
            $statusCode = 400;
        return response()->json($response, $statusCode);
    }
    public function getGp(Request $request)
    {
        $block = $request->block_code;

        $response = array();
        if (!empty($block)  && is_numeric($block)) {
            $gp_list = GP::where('block_code', $request->block_code)->get();
            $gp_list = $gp_list->filter(function ($item) use ($block) {
                // dump($item->toArray());
                return  $item['block_code'] == $block;
            })->map(function ($item) {
                $item['gp_ward_code'] = $item['gram_panchyat_code'];
                $item['gp_ward_name'] = $item['gram_panchyat_name'];
                return  $item;
            })->pluck('gp_ward_name', 'gp_ward_code');
            $response = $gp_list;
            $statusCode = 200;
        } else
            $statusCode = 400;
        return response()->json($response, $statusCode);
    }
    public function getMastersfromScheme(Request $request)
    {
        $scheme_code = $request->scheme_code;
        $role_code = $request->role_code;
        $district_list = Cache::rememberForever('master_districts', function () {
            return District::all();
        });

        $taluka_list = Cache::rememberForever('master_talukas', function () {
            return Taluka::all();
        });
        $urban_list = Cache::rememberForever('master_urbanbodies', function () {
            return UrbanBody::all();
        });
        $gp_list = Cache::rememberForever('master_gps', function () {
            return GP::all();
        });
        $ward_list = Cache::rememberForever('master_wards', function () {
            return Ward::all();
        });

        $roleArray = collect(session('role'));
        // dump($roleArray->toArray());
        $roleArray = $roleArray->filter(function ($item) use ($scheme_code, $role_code) {
            if ($item['scheme_id'] == $scheme_code && $item['role_master_fk'] == $role_code) {
                return $item;
            }
        })->first();
        // dd($roleArray);
        $arr = array(
            'is_first' => false,
            'is_last' => false,
            'next_level_role_id' => false,
            'state' =>
            array('is_visible' => false, 'code' => 1, 'array' => array()),
            'district' =>
            array('is_visible' => false, 'code' => null, 'array' => array()),
            'subdiv' =>
            array('is_visible' => false, 'code' => null, 'array' => array()),
            'rural_urban' =>
            array('is_visible' => false, 'code' => 1, 'array' => array()),
            'blockmunccorp' =>
            array('is_visible' => false, 'code' => null, 'array' => array()),
            'gpward' =>
            array('is_visible' => false, 'code' => null, 'array' => array()),
            'mapping' =>
            array('db_column' => null, 'val' => null, 'rural_urban' => null)

        );
        $stake_array = array('1' => 'WEST BENGAL');
        //dd($roleArray);
        if (count($roleArray) > 0) {
            $level = $roleArray['mapping_level'];
            if (!empty($roleArray['role_id']))
                $next_level_role_id = $roleArray['role_id'];
            else
                $next_level_role_id = NULL;
            $arr['next_level_role_id'] = $next_level_role_id;
            $is_urban_fk = $roleArray['is_urban'];
            if ($level == 'Department' || $level == 'State') {
                $arr['district']['is_visible'] = true;
                $arr['subdiv']['is_visible'] = false;
                $district_list = $district_list->pluck('district_name', 'district_code');
                if ($district_list->isEmpty()) {
                    $district_list = array();
                } else {
                    $district_list = $district_list->toArray();
                }
                $arr['district']['array'] = $district_list;
                $arr['rural_urban']['is_visible'] = true;
                $arr['blockmunccorp']['is_visible'] = true;
                $arr['gpward']['is_visible'] = true;
                $arr['mapping']['db_column'] = 'state_code_fk';
                $arr['mapping']['rural_urban'] = 1;
                $arr['mapping']['val'] = 1;
            } else if ($level == 'District') {
                $arr['district']['is_visible'] = false;
                $arr['subdiv']['is_visible'] = false;
                $district_code_fk = $roleArray['district_code'];
                $arr['district']['code'] = $district_code_fk;
                $arr['rural_urban']['is_visible'] = true;
                $arr['blockmunccorp']['is_visible'] = true;
                $arr['gpward']['is_visible'] = true;
                $arr['mapping']['db_column'] = 'created_by_dist_code';
                $arr['mapping']['rural_urban'] = 1;
                $arr['mapping']['val'] = $district_code_fk;
            } else if ($level == 'Subdiv') {
                $district_code_fk = $roleArray['district_code'];
                $subdiv_code_fk = $roleArray['urban_body_code'];
                $arr['district']['is_visible'] = false;
                $arr['subdiv']['is_visible'] = false;
                $arr['district']['code'] = $district_code_fk;
                $arr['subdiv']['code'] = $subdiv_code_fk;
                $arr['rural_urban']['is_visible'] = true;
                $arr['blockmunccorp']['is_visible'] = true;
                $arr['gpward']['is_visible'] = true;
                $arr['mapping']['db_column'] = 'created_by_local_body_code';
                $arr['mapping']['rural_urban'] = 1;
                $arr['mapping']['val'] = $subdiv_code_fk;
            } else if ($level == 'Block') {
                $district_code_fk = $roleArray['district_code'];
                $subdiv_code_fk = $roleArray['subdiv_code'];
                if ($roleArray['is_urban'] == 1) {
                    $block_code_fk = $roleArray['urban_body_code'];
                    $gpward_list = $ward_list->filter(function ($item) use ($block_code_fk) {
                        // dump($item->toArray());
                        return  $item['urban_body_code'] == $block_code_fk;
                    })->map(function ($item) {
                        $item['gp_ward_code'] = $item['urban_body_ward_code'];
                        $item['gp_ward_name'] = $item['urban_body_ward_name'];
                        return  $item;
                    })->pluck('gp_ward_name', 'gp_ward_code')->toArray();
                    $arr['mapping']['db_column'] = 'created_by_local_body_code';
                    $arr['mapping']['rural_urban'] = 1;
                    $arr['mapping']['val'] = $block_code_fk;
                } else {
                    $block_code_fk = $roleArray['taluka_code'];
                    $gpward_list = $gp_list->filter(function ($item) use ($block_code_fk) {
                        // dump($item->toArray());
                        return  $item['block_code'] == $block_code_fk;
                    })->map(function ($item) {
                        $item['gp_ward_code'] = $item['gram_panchyat_code'];
                        $item['gp_ward_name'] = $item['gram_panchyat_name'];
                        return  $item;
                    })->pluck('gp_ward_name', 'gp_ward_code')->toArray();
                    $arr['mapping']['db_column'] = 'created_by_local_body_code';
                    $arr['mapping']['rural_urban'] = 2;
                    $arr['mapping']['val'] = $block_code_fk;
                }

                $arr['district']['is_visible'] = false;
                $arr['subdiv']['is_visible'] = false;
                $arr['district']['code'] = $district_code_fk;

                $arr['rural_urban']['is_visible'] = false;
                $arr['blockmunccorp']['is_visible'] = false;
                $arr['gpward']['is_visible'] = true;
                $arr['gpward']['array'] = $gpward_list;
            }
        }
        $statusCode = 200;
        $response = $arr;
        return response()->json($response, $statusCode);
    }
}
