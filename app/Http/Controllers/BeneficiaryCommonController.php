<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configduty;
use App\Models\MapLavel;
use App\Models\District;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\UrbanBody;
use App\Models\GP;

use App\Models\DocumentType;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\RejectRevertReason;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Validator;
use App\Models\DsPhase;

class BeneficiaryCommonController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
    $this->source_type = 'ss_nfsa';
    $this->scheme_id = 20;
    // $phaseArr = DsPhase::where('is_current', TRUE)->first();
    // $mydate = $phaseArr->base_dob;
    $this->base_dob_chk_date = date('Y-m-d');
    // $this->max_dob = $max_date;
    //$this->min_dob = $min_date;
  }

  public function shemeSessionCheck(Request $request)
  {
    $scheme_id = $this->scheme_id;
    $is_active = 0;
    $roleArray = $request->session()->get('role');
    foreach ($roleArray as $roleObj) {
      if ($roleObj['scheme_id'] == $scheme_id) {
        $is_active = 1;
        $request->session()->put('level', $roleObj['mapping_level']);
        $distCode = $roleObj['district_code'];
        $request->session()->put('distCode', $roleObj['district_code']);
        $request->session()->put('scheme_id', $scheme_id);
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

      //  $ben_table = 'dist_' . $distCode . '.beneficiary';
      return true;
    } else {
      return false;
    }
  }
  public function getPersonalApproved(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    $is_faulty = $request->is_faulty;
    if (!in_array($is_faulty, array(0, 1))) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    try {
      $html = '';

      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        $getModelFunc = new getModelFunc();
        $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
        $TableFaultyPersonal = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
        $personal_model = new DataSourceCommon;
        if ($is_faulty == 1) {
          $personal_model->setTable('' . $TableFaultyPersonal);
        } else {
          $personal_model->setTable('' . $TablePersonal);
        }
        $personal_model->setConnection('pgsql_appread');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;

        $personaldata = $personal_model->where('application_id', $benid)->where($condition)->first()->toArray();
        // $contactdata = $contact_table->where('application_id', $benid)->first();
        // $bankdata = $bank_table->where('application_id', $benid)->first();
        // $dist_name = District::where('district_code', $contactdata->dist_code)->value('district_name');
        //print_r( $personaldata);die;
        if (!empty($personaldata['dob'])) {
          $extract_dob = Carbon::parse($personaldata['dob'])->format('d/m/Y');
          $personaldata['formatted_dob'] = $extract_dob;
          $personaldata['age_ason_01012021'] = $this->ageCalculate($personaldata['dob']);
        } else {
          $personaldata['formatted_dob'] = '';
          $personaldata['age_ason_01012021'] = '';
        }
        if (!empty($personaldata['duare_sarkar_date'])) {
          $extract_duare_sarkar_date = Carbon::parse($personaldata['duare_sarkar_date'])->format('d/m/Y');
          $personaldata['formatted_duare_sarkar_date'] = $extract_duare_sarkar_date;
        } else {
          $personaldata['formatted_duare_sarkar_date'] = '';
        }
      }
      $response = array('personaldata' => $personaldata, 'benid' => $benid);
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
  public function getAadhaarApproved(Request $request)
  { //echo 1;die;
    // dd($request->all());
    $statusCode = 200;
    $response = [];
    $benid = $request->benid;
    $scheme_id = $this->scheme_id;
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    try {
      $is_faulty = $request->is_faulty;
      if (!in_array($is_faulty, array(0, 1))) {
        $statusCode = 400;
        $response = array('error' => 'Error occured in form submit.');
        return response()->json($response, $statusCode);
      }
      if (!empty($benid)) {
        // dd($benid);
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        $getModelFunc = new getModelFunc();
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 2);
        $obj_model = new DataSourceCommon;
        if ($is_faulty == 1) {
          $obj_model->setTable('' . $TableFaulty);
        } else {
          $obj_model->setTable('' . $Table);
        }
        $obj_model->setConnection('pgsql_appread');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;

        $aadhardata = $obj_model->where('application_id', $benid)->where($condition)->first();
        //dd($aadhardata);
        $aadhar_no = Crypt::decryptString($aadhardata->encoded_aadhar);
      }
      $response = array('aadhar_no' => $aadhar_no);
    } catch (\Exception $e) {
      dd($e);
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statusCode = 400;
    } finally {
      return response()->json($response, $statusCode);
    }
  }
  public function getContactApproved(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    try {
      $is_faulty = $request->is_faulty;
      if (!in_array($is_faulty, array(0, 1))) {
        $statusCode = 400;
        $response = array('error' => 'Error occured in form submit.');
        return response()->json($response, $statusCode);
      }
      $html = '';
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        $getModelFunc = new getModelFunc();
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 3);
        $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 3);
        $obj_model = new DataSourceCommon;
        if ($is_faulty == 1) {
          $obj_model->setTable('' . $TableFaulty);
        } else {
          $obj_model->setTable('' . $Table);
        }
        $obj_model->setConnection('pgsql_appread');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;
        $contactdata = $obj_model->where('application_id', $benid)->where($condition)->first()->toArray();
        $dist_name = District::select('district_name')->where('district_code', $contactdata['dist_code'])->first();
        $contactdata['dist_name'] = $dist_name->district_name;
      }
      $response = array('contactdata' => $contactdata, 'benid' => $benid);
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
  public function getBankApproved(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    try {
      $html = '';
      $is_faulty = $request->is_faulty;
      if (!in_array($is_faulty, array(0, 1))) {
        $statusCode = 400;
        $response = array('error' => 'Error occured in form submit.');
        return response()->json($response, $statusCode);
      }
      if (!empty($benid)) {

        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        $getModelFunc = new getModelFunc();
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 4);
        $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);
        $obj_model = new DataSourceCommon;
        if ($is_faulty == 1) {
          $obj_model->setTable('' . $TableFaulty);
        } else {
          $obj_model->setTable('' . $Table);
        }
        $obj_model->setConnection('pgsql_appread');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;


        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;
        $bankdata = $obj_model->where('application_id', $benid)->where($condition)->first()->toArray();
      }
      $response = array('bankdata' => $bankdata, 'benid' => $benid);
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
  public function getInvestigatorApproved(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    try {
      $is_faulty = $request->is_faulty;
      $html = '';
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1,  1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $Table);
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;

        $personaldata = $personal_model->where('application_id', $benid)->where($condition)->first()->toArray();
        // $contactdata = $contact_table->where('application_id', $benid)->first();
        // $bankdata = $bank_table->where('application_id', $benid)->first();
        // $dist_name = District::where('district_code', $contactdata->dist_code)->value('district_name');
        //print_r( $personaldata);die;
        if (!empty($personaldata['dob'])) {
          $extract_dob = Carbon::parse($personaldata['dob'])->format('d/m/Y');
          $personaldata['formatted_dob'] = $extract_dob;
        } else {
          $personaldata['formatted_dob'] = '';
        }
        if (!empty($personaldata['duare_sarkar_date'])) {
          $extract_duare_sarkar_date = Carbon::parse($personaldata['duare_sarkar_date'])->format('d/m/Y');
          $personaldata['formatted_duare_sarkar_date'] = $extract_duare_sarkar_date;
        } else {
          $personaldata['formatted_duare_sarkar_date'] = '';
        }
      }
      $response = array('personaldata' => $personaldata, 'benid' => $benid);
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
  public function getEncloserList(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];

    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit1.');
      return response()->json($response, $statusCode);
    }
    $scheme_id = $this->scheme_id;
    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;
        $is_faulty = $request->is_faulty;
        if (!in_array($is_faulty, array(0, 1))) {
          $statusCode = 400;
          $response = array('error' => 'Error occured in form submit.');
          return response()->json($response, $statusCode);
        }
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;
        $is_draft = 1;
        $getModelFunc = new getModelFunc();
        $DraftPfImageTable = new DataSourceCommon;
        if ($is_faulty == 1) {
          $Table = $getModelFunc->getTableFaulty($district_code, $this->source_type, 5);
        } else {
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 5);
        }
        $DraftPfImageTable->setConnection('pgsql_encread');
        $DraftPfImageTable->setTable('' . $Table);
        $DraftEncloserTable = new DataSourceCommon;
        if ($is_faulty == 1) {
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 6);
        } else {
          $Table = $getModelFunc->getTable($district_code, $this->source_type, 6);
        }
        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);
        $doc_arr = array();
        $encloserdata = collect([]);
        $encloserdata_arr = array();
        $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
        $profileImageCnt = $DraftPfImageTable->where('image_type', $doc_profile->id)->where('application_id', $benid)->where($condition)->count();
        if ($profileImageCnt) {
          array_push($doc_arr, $doc_profile->id);
        }
        $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $benid)->where($condition)->get()->pluck('document_type')->toArray();
        if (count($encolserdata) > 0) {
          foreach ($encolserdata as $en) {
            array_push($doc_arr, $en);
          }
        }
        if (count($doc_arr) > 0) {
          $encloserdata = DocumentType::select('id', 'doc_name', 'is_profile_pic')->whereIn('id', $doc_arr)->get();
        }
      }
      if (count($encloserdata) > 0) {
        $p = 0;
        $html = '';
        foreach ($encloserdata as $enc) {
          if ($p == 0 || ($p % 2 == 0)) {
            $html = $html . '<tr>';
          }
          $html = $html . '    
          <th scope="row">' . $enc->doc_name . '</th>
          <td  scope="row" class="encView">&nbsp;&nbsp;&nbsp;<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="View_encolser_modal(\'' . $enc->doc_name . '\',' . $enc->id . ',' . intval($enc->is_profile_pic) . ',' . $benid . ')">View</a></td>';
          if (($p % 2 == 0) && ($p % 2 != 0)) {
            $html = $html . '</tr>';
          }
          $p++;
        }
      }

      $response = array('html' => $html, 'benid' => $benid);
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
  function ajaxGetEncloserApproved(Request $request)
  {
    //dd('ok');
    $scheme_id = $this->scheme_id;
    $roleArray = $request->session()->get('role');
    foreach ($roleArray as $roleObj) {
      if ($roleObj['scheme_id'] == $scheme_id) {
        $is_active = 1;
        $mapping_level = $roleObj['mapping_level'];
        $distCode = $roleObj['district_code'];
        $is_urban = $roleObj['is_urban'];
        if ($roleObj['is_urban'] == 1) {
          $blockCode = $roleObj['urban_body_code'];
        } else {
          $blockCode = $roleObj['taluka_code'];
        }
        break;
      }
    }
    if ($is_active == 0 || empty($distCode)) {
      return redirect("/")->with('error', 'User Disabled');
    }
    $is_faulty = $request->is_faulty;
    if (!in_array($is_faulty, array(0, 1))) {
      $return_text = 'Parameter Not Valid';
      return redirect("/")->with('error',  $return_text);
    }
    $getModelFunc = new getModelFunc();
    $DraftPfImageTable = new DataSourceCommon;
    if ($is_faulty == 1) {
      $Table = $getModelFunc->getTableFaultyWOutDoc($distCode, $this->source_type, 5, 1);
    } else {
      $Table = $getModelFunc->getTable($distCode, $this->source_type, 5, 1);
    }
    $DraftPfImageTable->setConnection('pgsql_encread');

    $DraftPfImageTable->setTable('' . $Table);
    $DraftEncloserTable = new DataSourceCommon;
    if ($is_faulty == 1) {
      $Table = $getModelFunc->getTableFaultyWOutDoc($distCode, $this->source_type, 6, 1);
    } else {
      $Table = $getModelFunc->getTable($distCode, $this->source_type, 6, 1);
    }
    $DraftEncloserTable->setConnection('pgsql_encread');
    $DraftEncloserTable->setTable('' . $Table);

    if (!empty($request->is_profile_pic))
      $is_profile_pic = $request->is_profile_pic;
    else
      $is_profile_pic = 0;
    $doc_type = $request->doc_type;
    $application_id = $request->application_id;
    if (empty($doc_type) || !ctype_digit($doc_type)) {
      $return_text = 'Parameter Not Valid';
      return redirect("/")->with('error',  $return_text);
    }
    if (!in_array($is_profile_pic, array(0, 1))) {
      $return_text = 'Parameter Not Valid';
      return redirect("/")->with('error',  $return_text);
    }
    if (empty($application_id)) {
      $return_text = 'Parameter Not Valid';
      return redirect("/")->with('error',  $return_text);
    }
    $user_id = Auth::user()->id;
    if ($is_profile_pic == 1) {
      $profileImagedata = $DraftPfImageTable->where('image_type', $request->doc_type)->where('application_id', $request->application_id)->first();
      if (empty($profileImagedata->application_id)) {
        $return_text = 'Parameter Not Valid';
        return redirect("/")->with('error',  $return_text);
      }
      $image_extension = $profileImagedata->image_extension;
      $mime_type = $profileImagedata->image_mimetype;
      if ($image_extension != 'png' && $image_extension != 'jpg' && $image_extension != 'jpeg') {
        if ($mime_type == 'image/png') {
          $image_extension = 'png';
        } else if ($mime_type == 'image/jpeg') {
          $image_extension = 'jpg';
        }
      }
      $htmlText = '<image id="image" width="50" height="100" src="data:image/' . $image_extension . ';base64, ' . $profileImagedata->profile_image . '">';
      echo $htmlText;
    } else {
      $encolserData = $DraftEncloserTable->where('document_type', $request->doc_type)->where('application_id', $request->application_id)->first();
      if (empty($encolserData->application_id)) {
        $return_text = 'Parameter Not Valid';
        return redirect("/")->with('error',  $return_text);
      }
      $file_extension = $encolserData->document_extension;
      $mime_type = $encolserData->document_mime_type;
      if ($file_extension != 'png' && $file_extension != 'jpg' && $file_extension != 'jpeg' && $file_extension != 'pdf') {
        if ($mime_type == 'image/png') {
          $file_extension = 'png';
        } else if ($mime_type == 'image/jpeg') {
          $file_extension = 'jpg';
        } else if ($mime_type == 'application/pdf') {
          $file_extension = 'pdf';
        }
      }
      try {
        if (strtoupper($file_extension) == 'PNG' || strtoupper($file_extension) == 'JPG' || strtoupper($file_extension) == 'JPEG') {
          $htmlText = '<image id="image" width="100%" height="100%" src="data:image/' . $file_extension . ';base64, ' . $encolserData->attched_document . '">';
          echo $htmlText;
        } else if (strtoupper($file_extension) == 'PDF') {
          //dd($encolserData->attched_document);
          $htmlText = '<embed type="text/html" width="100%" height="100%" src="data:application/pdf;base64, ' . $encolserData->attched_document . ' ">';


          echo $htmlText;
        }
      } catch (\Exception $e) {
        dd($e);
        return redirect("/")->with('error',  'Some error.please try again ......');
      }
    }
  }
  function ageCalculate($dob)
  {
    $diff = 0;
    if ($dob != '') {
      //$diff = $this->ageCalculate($dob);
      $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
    }
    return intval($diff);
  }
}
