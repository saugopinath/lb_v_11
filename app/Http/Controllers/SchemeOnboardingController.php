<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapLavel;
use App\Models\Schemetype;
use App\Models\Scheme;
use App\Models\Designation;
use Exception;
use Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;





class SchemeOnboardingController extends Controller
{
    // public function __construct()
    // {

    // }

    public function index(Request $request)
    {
        $scheme_type = Schemetype::orderby('scheme_type')->get();


        if (request()->ajax()) {
            $scheme_id = $request->scheme_id;
            $menuArray = [];

            if (empty($serachvalue)) {
                $menuArray = MapLavel::where('scheme_id', $scheme_id)
                    ->orderBy('id')
                    ->select(['id', 'role_name', 'parent_id', 'is_active', 'is_first', 'first_node', 'stack_level']);
            }

            return datatables()
                ->of($menuArray)

                // is_active Column (Bootstrap 5 + FontAwesome)
                ->addColumn('is_active', function ($menuArray) {
                    if ($menuArray->is_active == 1) {
                        return '<button class="btn btn-sm btn-default" onClick="toggleActivate(' . $menuArray->id . ',1)">
                        <i class="fas fa-check text-success"></i>
                        </button>';
                    } else {
                        return '<button class="btn btn-sm btn-default" onClick="toggleActivate(' . $menuArray->id . ',1)">
                        <i class="fas fa-times text-danger"></i>
                        </button>';
                    }
                })

                // is_first Column
                ->addColumn('is_first', function ($menuArray) {
                    if ($menuArray->is_first == true) {
                        return '<button class="btn btn-sm btn-default" onClick="toggleActivate(' . $menuArray->id . ',2)">
                        <i class="fas fa-check text-success"></i>
                        </button>';
                    } else {
                        return '<button class="btn btn-sm btn-default" onClick="toggleActivate(' . $menuArray->id . ',2)">
                        <i class="fas fa-times text-danger"></i>
                        </button>';
                    }
                })

                // first_node Column
                ->addColumn('first_node', function ($menuArray) {
                    if ($menuArray->first_node == true) {
                        return '<button class="btn btn-sm btn-default" onClick="toggleActivate(' . $menuArray->id . ',3)">
                        <i class="fas fa-check text-success"></i>
                        </button>';
                    } else {
                        return '<button class="btn btn-sm btn-default" onClick="toggleActivate(' . $menuArray->id . ',3)">
                        <i class="fas fa-times text-danger"></i>
                        </button>';
                    }
                })

                // Action Column
                ->addColumn('action', function ($menuArray) {
                    return '<button class="btn btn-warning btn-sm" onClick="addUpdateLevelForm(' . $menuArray->id . ')">
                        <i class="fas fa-edit"></i> Update
                    </button>';
                })

                ->rawColumns(['is_active', 'is_first', 'first_node', 'action'])
                ->make(true);
        }

        return view('scheme_onboard/index')->with('scheme_types', $scheme_type);
    }

    public function workflowListView(Request $request)
    {
        // dd($request->all());
        $scheme_id = $request->scheme_id;

        $leaf_nodes = MapLavel::where('first_node', true)->where('is_active', 1)->where('scheme_id', $scheme_id)->with('parent')->get();

        $workflow_li_view = "";
        $workflows = array();
        $workflow_count = 1;
        foreach ($leaf_nodes as $leaf_node) {
            $approval_flow = array();
            $workflow_li_view = $workflow_li_view . '<div class="col-md-3"><h4>WorkFlow ' . $workflow_count . '</h4><ul class="timeline">';
            $current_node = $leaf_node;
            $workflow_step = 1;
            while ($current_node->is_final != 1) {
                $approval_flow[] = $current_node;
                $workflow_li_view = $workflow_li_view . '<li><a href="#">Step ' . $workflow_step . '</a><br/>' .
                    '<b>User Role: </b><a href="#" class="float-right">' . $current_node->role_name . '</a><br/>' .
                    '<p><b>User Level: </b>' . $current_node->stack_level . '</p></li>';
                $current_node = $current_node->parent;
                $workflow_step++;
            }
            if ($current_node != null && $current_node->is_final == 1) {
                $approval_flow[] = $current_node;
                $workflow_li_view = $workflow_li_view . '<li><a href="#">Step ' . $workflow_step . '</a><br/>' .
                    '<b>User Role: </b><a href="#" class="float-right">' . $current_node->role_name . '</a><br/>' .
                    '<p><b>User Level: </b>' . $current_node->stack_level . '</p></li>';
            }
            $workflow_li_view = $workflow_li_view . '</ul></div>';
            $workflows[] = $approval_flow;
            $workflow_count++;
        }

        // dd($workflow_li_view);

        return $workflow_li_view;
    }

    public function getschemefromtype(Request $request)
    {
        $scheme_type = $request->scheme_type;

        $schemes = Scheme::where('scheme_type', $scheme_type)->where('is_active', 1)->get(['id', 'scheme_name'])->toArray();

        return $schemes;
    }

    public function schemeOnboardToggleActivate(Request $request)
    {
        $level_id = $request[trim('level_id')];
        $action_type = $request[trim('action_type')]; //active - IS_ACTIVE , first - IS_FIRST

        $mytime = Carbon\Carbon::now();

        $menuItem = MapLavel::where('id', $level_id)->first();

        if ($action_type == 1) { //is_active - 1
            $statusCode = 1;
            if ($menuItem->is_active == 1) {
                $statusCode = 0;
            }
            MapLavel::where('id', $level_id)->update(['is_active' => $statusCode]);
        }
        if ($action_type == 2) { //is_first - 2
            $toggleStatus = TRUE;
            if ($menuItem->is_first) {
                $toggleStatus = FALSE;
            }
            MapLavel::where('id', $level_id)->update(['is_first' => $toggleStatus]);
        }
        if ($action_type == 3) { //first_node - 3
            $toggleStatus = TRUE;
            if ($menuItem->first_node) {
                $toggleStatus = FALSE;
            }
            MapLavel::where('id', $level_id)->update(['first_node' => $toggleStatus]);
        }

        return "success";
    }

    public function getAddUpdateLevelInfo(Request $request)
    {
        $scheme_id = $request->scheme_id;
        $id = $request->id;

        $mapLevelController = new MapLevelController;

        // $user_level = Config::get('constants.user_level');

        $user_role = Designation::whereIn('id', [13, 14, 15])->get(['id', 'name'])->toArray();

        $parent_level = MapLavel::where('scheme_id', $scheme_id)->where('is_active', 1)->where('role_id', '>', 0)->get(['id', DB::raw('CONCAT(stack_level,role_name) as name')])->toArray();


        $maplevelnode = MapLavel::where('id', $id)->get(['id', 'role_name', 'stack_level', 'scheme_id', 'parent_id', 'is_active', 'is_first'])->toArray();

        $json_arr = array();
        $json_arr[0] = $user_role;
        $json_arr[1] = $parent_level;
        $json_arr[2] = $maplevelnode;

        return response()->json($json_arr);
    }

    public function addUpdateMap(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scheme_id' => 'required',
            'user_role' => 'required|max:50',
            'parent_id' => 'nullable | integer',
            'usr_level' => 'required|max:50',
            'id' => 'required|integer',
            'is_first' => 'integer'
        ]);
        if ($validator->passes()) {

            $designation = Designation::where('name', $request['user_role'])->first();
            $id = $request['id'];

            if ($id) {

                $is_final = 0;
                if ($request['parent_id'] == 0) {
                    $is_final = 1;
                }
                $updatearrmain = array(
                    'role_name' => $request['user_role'],
                    'role_id' => $designation->id,
                    'parent_id' => $request['parent_id'],
                    'stack_level' => $request['usr_level'],
                    'is_first' => $request['is_first'],
                    'is_final' => $is_final
                );
                $update_status = MapLavel::where('id', $id)->update($updatearrmain);
                $return_msg = "Successfully Updated";
            } else {
                $is_final = 0;
                if ($request['parent_id'] == 0) {
                    $is_final = 1;
                } else if ($id == 0) {

                    MapLavel::where('id', $request['parent_id'])->update(['first_node' => FALSE]);
                }
                $insertarrmain = array(
                    'scheme_id' => $request['scheme_id'],
                    'role_name' => $request['user_role'],
                    'role_id' => $designation->id,
                    'parent_id' => $request['parent_id'],
                    'stack_level' => $request['usr_level'],
                    'is_first' => $request['is_first'],
                    'is_final' => $is_final,
                    'first_node' => TRUE,
                    'is_active' => TRUE
                );
                MapLavel::create($insertarrmain);
                $return_msg = "Successfully Added";
            }

            $return_status = 1;

        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
    }
    public function addNewSchemeType(Request $request)
    {

        $scheme_type = $request['scheme_type'];
        $scheme_type_id = $request['scheme_type_id'];
        $action_type = $request['action_type'];

        $this->validateSchemeTypeInput($request);

        if ($action_type == 'add') {
            Schemetype::create([
                'scheme_type' => $scheme_type
            ]);
        } else if ($action_type == 'edit') {
            Schemetype::where('id', $scheme_type_id)->update(['scheme_type' => $scheme_type]);
        }
        $scheme_types = Schemetype::get(['id', 'scheme_type'])->toArray();


        return response()->json($scheme_types);

    }
    private function validateSchemeTypeInput($request)
    {
        $this->validate($request, [
            'scheme_type' => 'required|max:60'
        ]);
    }

    public function getSchemeDetail(Request $request)
    {
        $scheme_id = $request['scheme_id'];
        $scheme = Scheme::find($scheme_id)->toArray();
        // dd($scheme);

        return response()->json($scheme);
    }

    public function addUpdateScheme(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'scheme_type' => 'required',
            'scheme_id' => 'nullable | integer',
            'scheme_name' => 'required | max:95',
            'description' => 'nullable',
            'shortcode' => 'nullable | max:95'
        ]);
        if ($validator->passes()) {
            $scheme_type = $request['scheme_type'];
            $scheme_id = $request['scheme_id'];
            $scheme_name = $request['scheme_name'];
            $description = $request['description'];
            $shortcode = $request['shortcode'];
            $id = $request['id'];
            $is_active = $request['is_active'];

            //Update
            if ($id) {
                $updatearrmain = array(
                    'scheme_name' => $scheme_name,
                    'description' => $description,
                    'short_code' => $shortcode,
                    'is_active' => $is_active
                );
                $update_status = Scheme::where('id', $scheme_id)->update($updatearrmain);
                $return_msg = "Scheme Successfully Updated";

            } else {
                //Add
                $insertarrmain = array(
                    'scheme_type' => $scheme_type,
                    'scheme_name' => $scheme_name,
                    'description' => $description,
                    'short_code' => $shortcode,
                    'is_active' => $is_active
                );
                Scheme::create($insertarrmain);
                $return_msg = "Scheme Successfully Added";

            }
            $return_status = 1;
        } else {
            $return_status = 0;
            $return_msg = $validator->errors()->all();
        }
        return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
    }

    public function toggleItemStatus(Request $request)
    {
        $item_type = $request['item_type'];
        $item_id = $request['item_id'];

        $modelName = '';
        if ($item_type == 1) {
            $modelName = "App\Models\Schemetype";
        } else if ($item_type == 2) {
            $modelName = "App\Models\Scheme";
        }

        $item = $modelName::find($item_id);
        $status = 0;
        if ($item->is_active == 0) {
            $status = 1;
        }
        $modelName::where('id', $item_id)->update(['is_active' => $status]);

        return "success";
    }

    public function deleteItem(Request $request)
    {
        $item_type = $request['item_type'];
        $item_id = $request['item_id'];

        $modelName = '';
        if ($item_type == 1) {
            $modelName = "App\Models\Schemetype";
        } else if ($item_type == 2) {
            $modelName = "App\Models\Scheme";
        }
        $modelName::where('id', $item_id)->delete();

        return "success";
    }

    public function getAllItemList(Request $request)
    {
        $item_type = $request['item_type'];

        $limit = $request->input('length');
        $offset = $request->input('start');

        $modelName = '';
        if ($item_type == 1) {
            $modelName = "App\Models\Schemetype";
        } else if ($item_type == 2) {
            $modelName = "App\Models\Scheme";
        }

        $data = $modelName::orderby('id')->offset($offset)->limit($limit)->get();
        $totalRecords = $modelName::count();
        $filterRecords = count($data);

        return datatables()
            ->of($data)
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($filterRecords)
            ->skipPaging()
            ->addColumn('item_name', function ($data) use ($item_type) {
                //Scheme Type
                if ($item_type == 1) {
                    return $data->scheme_type;
                }
                //Scheme
                if ($item_type == 2) {
                    return $data->scheme_name;
                }
            })
            ->addColumn('is_active', function ($data) use ($item_type) {
                return ($data->is_active == 1) ? '<button class="glyphicon glyphicon-ok" onClick="toggleStatus(' . $item_type . ', ' . $data->id . ')"></button>'
                    : '<button class="glyphicon glyphicon-remove" onClick="toggleStatus(' . $item_type . ', ' . $data->id . ')"></button>';
            })
            ->addColumn('action', function ($data) use ($item_type) {
                $action = '<button class="btn btn-warning item_delete_button" onClick="deleteItem(' . $item_type . ', ' . $data->id . ')">Delete</button>';

                return $action;
            })
            ->rawColumns(['is_active', 'item_name', 'action'])
            ->make(true);



    }
}