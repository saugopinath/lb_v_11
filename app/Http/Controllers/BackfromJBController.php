<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\models\Configduty;
use App\models\MapLavel;
use App\models\District;
use App\models\Taluka;
use App\models\Ward;
use App\models\UrbanBody;
use App\models\GP;
use Auth;
use DB;
use App\Helpers\Helper;
use App\SubDistrict;
use Carbon\Carbon;
use Config;
use App\BlkUrbanlEntryMapping;
use App\RejectRevertReason;
use App\models\Scheme;
use App\models\DocumentType;
use Validator;
use App\models\getModelFunc;
use App\models\DataSourceCommon;
use App\models\DsPhase;
class BackfromJBController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->base_dob_chk_date = date('Y-m-d');
    $this->scheme_id = 20;
    $this->source_type = 'ss_nfsa';
    $this->supporting_dob_type_id = 258;
  }
  public function showApplicantDetails(Request $request)
  {
    // dd('ok');
    //return redirect('/')->with('error', 'Not Allowded');
    $designation_id = Auth::user()->designation_id;
   // dd($designation_id);
    $user_id = Auth::user()->id;
    if ($designation_id == 'Delegated Verifier' ||  $designation_id == 'Delegated Approver' || $designation_id == 'Verifier' || $designation_id == 'Approver') {
      $scheme_id = $this->scheme_id;
      if (empty($request->application_id)) {
        return redirect("/")->with('danger', 'Applicant ID Not Found');
      }
      if (!is_numeric($request->application_id)) {
        return redirect("/")->with('danger', 'Applicant ID Not Valid');
      }
      if ($request->is_faulty=='') {
        return redirect("/")->with('danger', 'Paramater Not Valid');
      }
      if (!in_array($request->is_faulty,array(0,1))) {
        return redirect("/")->with('danger', 'Paramater Not Valid');
      }
      $duty_obj = Configduty::where('user_id', $user_id)->where('scheme_id', $scheme_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $district_code = $duty_obj->district_code;
      $mapArr = MapLavel::where('scheme_id', $duty_obj->scheme_id)->where('role_name','Verifier')->first();
      $next_level_role_id=$mapArr->parent_id;
      $getModelFunc = new getModelFunc();
      $schemaname = $getModelFunc->getSchemaDetails();
      $Table = $getModelFunc->getTable($district_code, $this->source_type, 1);
      $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
      $personal_model = new DataSourceCommon;
      $personal_model->setTable('' . $Table);
      $personal_model_f = new DataSourceCommon;
      $personal_model_f->setTable('' . $TableFaulty);
      if ($request->is_faulty == 1) {
        $row = $personal_model_f->where('application_id', $request->application_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->first();
      } else if ($request->is_faulty == 0) {
          $row = $personal_model->where('application_id', $request->application_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->first();
      }
      if(empty($row)){
        return redirect("/backfromjb")->with('error', ' Application Id Not found');
      }
      //dd($row->ds_phase);
      $phaseArr = DsPhase::where('phase_code',$row->ds_phase)->first();
      // $mydate = $phaseArr->base_dob;
      $mydate = date('Y-m-d');
      $max_date = strtotime("-25 year", strtotime($mydate));
      $max_date = date("Y-m-d", $max_date);
      $min_date = strtotime("-60 year", strtotime($mydate));
      $min_date = date("Y-m-d", $min_date);
      
      $doc_age_dob = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where("id", $this->supporting_dob_type_id)->first();
      $supporting_dob_model = new DataSourceCommon;
      $supporting_db_Table = 'lb_scheme.ben_attach_documents';
      $supporting_dob_model->setConnection('pgsql_encwrite');
      $supporting_dob_model->setTable('' . $supporting_db_Table);
      $supporting_dob_doc_data = $supporting_dob_model->where('application_id', $request->application_id)->where('document_type',$this->supporting_dob_type_id)->first();
      if(!empty($supporting_dob_doc_data)){
      $mime_type = $supporting_dob_doc_data->document_mime_type;
      $image_extension = $supporting_dob_doc_data->document_extension;
      if ($image_extension != 'png' && $image_extension != 'jpg' && $image_extension != 'jpeg') {
          if ($mime_type == 'image/png') {
              $image_extension = 'png';
          } else if ($mime_type == 'image/jpeg') {
              $image_extension = 'jpg';
          }
      }
      $row_image = "data:image/".$image_extension.";base64,".$supporting_dob_doc_data->attched_document;
    }
    else{
      $row_image='';
      $image_extension='';
    }
    //dd($row->toArray());
      return view(
        'BackfromJB.pension_view_details',
        [
          'row' => $row,
          'designation_id' => $designation_id,
          'next_level_role_id' => $next_level_role_id,
          'doc_age_dob' => $doc_age_dob,
          'image'=>$row_image,
          'ext'=> $image_extension,
          'max_dob'=> $max_date,
          'min_dob'=> $min_date,
          'is_faulty'=> $request->is_faulty
        ]
      );
    } else {
      return redirect("/")->with('danger', 'Not Allowed');
    }
  }
  public function marked_list(Request $request)
  {
    //return redirect('/')->with('error', 'Not Allowded');
      $this->middleware('auth');
      $designation_id = Auth::user()->designation_id;
      //dd($designation_id);
      $user_id = Auth::user()->id;
  
      $scheme_id = $this->scheme_id;
      
      $duty_obj = Configduty::where('user_id', $user_id)->where('scheme_id', $scheme_id)->first();
      //dd($duty_obj);
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      
      $mapArr = MapLavel::where('scheme_id', $duty_obj->scheme_id)->where('role_name','Verifier')->first();
      $next_level_role_id=$mapArr->parent_id;
      $type_des='Back from JB';
      
      //dd($type_des);
      $district_code = $duty_obj->district_code;
      $urban_bodys = collect([]);
      $gps = collect([]);
      $district_list_obj = collect([]);
      $where_condition=' where A.wrong_dob=1 ';
      if ($duty_obj->mapping_level == "Subdiv") {
        $created_by_local_body_code = $duty_obj->urban_body_code;
        $is_rural = 1;
        $verifier_type = 'Subdiv';
        $gps = collect([]);
        $urban_body_code = $duty_obj->urban_body_code;
        $urban_bodys = UrbanBody::where('sub_district_code', $urban_body_code)->select('urban_body_code', 'urban_body_name')->get();
        $urban_body_codes = [];
        $i = 0;
        foreach ($urban_bodys as $urban_body) {
  
          $urban_body_codes[$i] = $urban_body->urban_body_code;
          $i++;
        }
        $where_condition .= " AND A.created_by_dist_code=".$district_code." AND A.created_by_local_body_code=" . $created_by_local_body_code . " ";
        $where_condition .= " AND B.created_by_dist_code=".$district_code." AND B.created_by_local_body_code=" . $created_by_local_body_code . " ";

        if (!empty($request->block_ulb_code)) {
            $where_condition .= " AND B.block_ulb_code=" . $request->block_ulb_code . " ";
        }
        if (!empty($request->gp_ward_code)) {
            $where_condition .= " AND  B.gp_ward_code=" . $request->gp_ward_code . "";
        }
      }
      if ($duty_obj->mapping_level == "Block") {
        $created_by_local_body_code = $duty_obj->taluka_code;
        $is_rural = 2;
        $verifier_type = 'Block';
        $urban_bodys = collect([]);
        $taluka_code = $duty_obj->taluka_code;
        $gps = GP::where('block_code', $taluka_code)->select('gram_panchyat_code', 'gram_panchyat_name')->get();
        $where_condition .= " AND A.created_by_dist_code=".$district_code." AND A.created_by_local_body_code=" . $created_by_local_body_code . " ";
        $where_condition .= " AND B.created_by_dist_code=".$district_code." AND B.created_by_local_body_code=" . $created_by_local_body_code . " ";
        if (!empty($request->gp_ward_code)) {
            $where_condition .= " AND  B.gp_ward_code=" . $request->gp_ward_code . "";
        }
      }
      if ($duty_obj->mapping_level == "District") {
        $district_list_obj = District::get();
        $verifier_type = 'District';
        $is_rural = NULL;
        $created_by_local_body_code = NULL;
        $where_condition .= " AND A.created_by_dist_code=".$district_code;
        $where_condition .= " AND B.created_by_dist_code=".$district_code;

       
      }
      if ($request->ajax()) {
        $query = "select 
        A.next_level_role_id_dob,A.application_id,
        A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name,mobile_no,
        dob,jb_dob,B.gp_ward_code,B.gp_ward_name,B.block_ulb_code,B.block_ulb_name,'0' as is_faulty 
        from lb_scheme.ben_personal_details as A JOIN lb_scheme.ben_contact_details as B 
        ON A.application_id=B.application_id
        ".$where_condition."
        UNION 
        select 
        A.next_level_role_id_dob,
        A.application_id,
        A.beneficiary_id,CONCAT(COALESCE(ben_fname,''), ' ', COALESCE(ben_mname,''),' ',COALESCE(ben_lname,'')) AS ben_name,
        mobile_no,dob,jb_dob,B.gp_ward_code,B.gp_ward_name,B.block_ulb_code,B.block_ulb_name,
        '1' as is_faulty 
        from lb_scheme.faulty_ben_personal_details as A LEFT JOIN lb_scheme.faulty_ben_contact_details as B 
        ON A.application_id=B.application_id ".$where_condition."";
      

        $data = DB::connection('pgsql_appread')->select($query);

        //print_r($data);die;
        return datatables()->of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($data) use ($scheme_id, $designation_id,$next_level_role_id) {
           
              if ($designation_id == 'Verifier' || $designation_id == 'Delegated Verifier') {
                if(is_null($data->next_level_role_id_dob)){
                  // $action = '<a href="' . route('showbackfromjb', ['id' => $data->application_id,'is_faulty' => $data->is_faulty]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>&nbsp; &nbsp;';
                   $action = '<a href="' . route('showbackfromjb', [
                      'application_id' => $data->application_id,
                      'is_faulty' => $data->is_faulty
                  ]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';
                 } else if($data->next_level_role_id_dob==$next_level_role_id){
                  $action ='Approval Pending';
                 }
                  else if($data->next_level_role_id_dob==0){
                   $action ='Approved';
                  }
                   else{
                    $action ='';
                  }
                
              }
              if ($designation_id == 'Approver' || $designation_id == 'Delegated Approver') {
                if ($data->next_level_role_id_dob == 0) {
                    $action = 'Approved';
                } else if ($data->next_level_role_id_dob == $next_level_role_id) {
                    $action = '<a href="' . route('showbackfromjb', [
                        'application_id' => $data->application_id,
                        'is_faulty' => $data->is_faulty
                    ]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';
                } else {
                    $action = '';
                }
            }
             
    
              return $action;
            })->addColumn('id', function ($data) {
                return $data->beneficiary_id;
            })
            ->addColumn('name', function ($data) {
                return $data->ben_name;
            })
            ->addColumn('block_ulb_name', function ($data) {
                return $data->block_ulb_name;
            })
            ->addColumn('gp_ward_name', function ($data) {
                return $data->gp_ward_name;
            })
            ->addColumn('dob', function ($data) {
                if(!is_null($data->dob)){
                    return date('d/m/Y', strtotime($data->dob));
                    }
                    else{
                        return ''; 
                    }
            })
            ->addColumn('jb_dob', function ($data) {
                if(!is_null($data->jb_dob)){
                return date('d/m/Y', strtotime($data->jb_dob));
                }
                else{
                    return ''; 
                }
            })
            ->addColumn('mobile_no', function ($data) {

                return $data->mobile_no;
            })
            ->addColumn('application_id', function ($data) {

                return $data->application_id;
            })
        
            // ->with('completed', $complete)
            ->rawColumns(['id', 'name', 'block_ulb_name', 'gp_ward_name', 'action', 'mobile_no', 'application_id', 'dob', 'jb_dob'])
            ->make(true);
    }
  
      return view(
        'BackfromJB.linelisting',
        [
          'designation_id' => $designation_id,
          'verifier_type' => $verifier_type,
          'created_by_local_body_code' => $created_by_local_body_code,
          'is_rural' => $is_rural,
          'scheme_id' => $scheme_id,
          'gps' => $gps,
          'urban_bodys' => $urban_bodys,
          'district_code' => $district_code,
          'type_des' => $type_des
        ]
      );
  }
  public function verifydata(Request $request)
  {
    // dd($request->all());
    $designation_id = Auth::user()->designation_id;
    //dd( $request->application_id);
    $user_id = Auth::user()->id;
    if ($designation_id == 'Delegated Verifier' ||  $designation_id == 'Delegated Approver' || $designation_id == 'Verifier' || $designation_id == 'Approver') {
      if (empty($request->application_id)) {
        return redirect("/")->with('danger', 'Applicant ID Not Found');
      }
      if (!is_numeric($request->application_id)) {
        return redirect("/")->with('danger', 'Applicant ID Not Valid');
      }
     
      $duty_obj = Configduty::where('user_id', $user_id)->where('scheme_id', $this->scheme_id)->first();
      if (empty($duty_obj)) {
        return redirect("/")->with('danger', 'Not Allowed');
      }
      $district_code = $duty_obj->district_code;
      $district_code = $duty_obj->district_code;
      $mapArr = MapLavel::where('scheme_id', $duty_obj->scheme_id)->where('role_name','Verifier')->first();
      $next_level_role_id=$mapArr->parent_id;
      $getModelFunc = new getModelFunc();
      $schemaname = $getModelFunc->getSchemaDetails();
      $Table = $getModelFunc->getTable($district_code, $this->source_type, 1);
      $TableFaulty = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
      $personal_model = new DataSourceCommon;
      $personal_model->setTable('' . $Table);
      $personal_model_f = new DataSourceCommon;
      $personal_model_f->setTable('' . $TableFaulty);
      if ($request->is_faulty == 1) {
        $row = $personal_model_f->where('application_id', $request->application_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->first();
      } else if ($request->is_faulty == 0) {
          $row = $personal_model->where('application_id', $request->application_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->first();
      }
      //dd($row);
      if(empty($row)){
        return redirect("/backfromjb")->with('error', ' Application Id Not found');
      }
      $c_time = date('Y-m-d H:i:s', time());
      if ($request->action_type == 'Verify and Forward to Approver') {
        $phaseArr = DsPhase::where('phase_code',$row->ds_phase)->first();
        $mydate = $phaseArr->base_dob;
        $max_date = strtotime("-25 year", strtotime($mydate));
        $max_date = date("Y-m-d", $max_date);
        $min_date = strtotime("-60 year", strtotime($mydate));
        $min_date = date("Y-m-d", $min_date);
        $rules = [
          'dob' => 'required|date|before_or_equal:' . $max_date . '|after_or_equal:' . $min_date
      ];
      $attributes = array();
      $messages = array();
      $attributes['dob'] = 'Date of Birth';
      // dd($request->dob);
      $validator = Validator::make($request->all(), $rules, $messages, $attributes);
      if ($validator->passes()) {
        $diff = Carbon::parse($request->dob)->diffInYears($phaseArr->base_dob);
            if ($diff < 25 || $diff > 60) {
                $return_text = 'Date of Birth Invalid';
                // return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)->with('error', $return_text);
                return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)
              ->withErrors($return_text);

            }

        $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'next_level_role_id_dob' => $next_level_role_id,'new_dob' => $request->dob];
        $accept_reject_model = new DataSourceCommon;
        $Table_aacpt = $getModelFunc->getTable($district_code, $this->source_type, 9);
        $accept_reject_model->setTable('' . $Table_aacpt);
        $accept_reject_model->created_at = $c_time;
        $accept_reject_model->application_id = $request->application_id;
        $accept_reject_model->scheme_id = $this->scheme_id;
        $accept_reject_model->created_by = $user_id;
        $accept_reject_model->op_type = 'DOBV';
        $accept_reject_model->ip_address = request()->ip();
        $is_saved_log = $accept_reject_model->save();
        DB::beginTransaction();
        DB::connection('pgsql_appwrite')->beginTransaction();
        if ($request->is_faulty == 1) {
          $is_update=$personal_model_f->where('application_id', $request->application_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->update($input);
        } else if ($request->is_faulty == 0) {
          $is_update=$personal_model->where('application_id', $request->application_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->update($input);
        }
        if ($is_update && $is_saved_log) {
          DB::commit();
          DB::connection('pgsql_appwrite')->commit();
          return redirect('backfromjb')->with('message', 'Request has been send to Approver for Approval!');
        } else {
          DB::rollback();
          DB::connection('pgsql_appwrite')->rollback();
          $return_msg = 'Error! Please try again.';
          // return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)->with('error', 'Error! Please try again.');
           return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)
              ->withErrors($return_msg);
        }
      }else {
        $return_status = 0;
        $return_msg = $validator->errors()->all();
            return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)
              ->withErrors($return_msg);
        // return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)->with('errors', $return_msg);
       }
    }
    else if ($request->action_type == 'Approve') {
        $input = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'next_level_role_id_dob' => 0,'dob' => $row->new_dob,'is_sent_jb' => NULL];
        $schemaname = $getModelFunc->getSchemaDetails();
        $model_payment = new DataSourceCommon;
        $model_payment->setConnection('pgsql_payment');
        $model_payment->setTable('' . $schemaname . '.ben_payment_details');
        $payemt_exists=$model_payment->where('application_id', $request->application_id)->first();
        $getModelFunc = new getModelFunc();
        if ($request->is_faulty == 1) {
          $TableBank = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 4);
          $TablePersonal = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 1);
          $TableContact = $getModelFunc->getTableFaultyWOutDoc($district_code, $this->source_type, 3);
        } else {
          $TableBank = $getModelFunc->getTable($district_code, $this->source_type, 4);
          $TablePersonal = $getModelFunc->getTable($district_code, $this->source_type, 1);
          $TableContact = $getModelFunc->getTable($district_code, $this->source_type, 3);
         }
         $bank_model = new DataSourceCommon;
         $bank_model->setTable('' . $TableBank);
         $contact_model = new DataSourceCommon;
         $contact_model->setTable('' . $TableContact);
         $row_bank = $bank_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->first();
         $row_contact = $contact_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->first();
        $accept_reject_model = new DataSourceCommon;
        $Table_aacpt = $getModelFunc->getTable($district_code, $this->source_type, 9);
        $accept_reject_model->setTable($Table_aacpt);
        $accept_reject_model->created_at = $c_time;
        $accept_reject_model->application_id = $request->application_id;
        $accept_reject_model->scheme_id = $this->scheme_id;
        $accept_reject_model->created_by = $user_id;
        $accept_reject_model->op_type = 'DOBA';
        $accept_reject_model->ip_address = request()->ip();
        $is_saved_log = $accept_reject_model->save();
        DB::beginTransaction();
        DB::connection('pgsql_appwrite')->beginTransaction();
        DB::connection('pgsql_payment')->beginTransaction();

        if ($request->is_faulty == 1) {
          $is_update=$personal_model_f->where('application_id', $request->application_id)->where('next_level_role_id_dob',$next_level_role_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->update($input);
        } else if ($request->is_faulty == 0) {
          $is_update=$personal_model->where('application_id', $request->application_id)->where('next_level_role_id_dob',$next_level_role_id)->where('wrong_dob',1)->where('created_by_dist_code', $district_code)->update($input);
        }
        if(empty($payemt_exists)){
          $ben_fullname=trim($row->ben_fname);
          if(trim($row->ben_mname)!=''){
            $ben_fullname=$ben_fullname.' '.$row->ben_mname;
          }
          if(trim($row->ben_lname)!=''){
            $ben_fullname=$ben_fullname.' '.$row->ben_lname;
          }
         // dd($row->created_at);
          $created_at_obj = explode('-', $row->created_at);
          $month = $created_at_obj[1];
          $day   = $created_at_obj[2];
          $year  = substr($created_at_obj[0],2,2);
          $start_yymm=$year.$month;
          if($start_yymm<2109){
            $start_yymm=2109;
          }
          $end_yymm = Carbon::parse($row->new_dob)->addYears(60);
          $end_yymm_obj = explode('-', $end_yymm);
          $end_month = $end_yymm_obj[1];
          $end_day   = $end_yymm_obj[2];
          $end_year  = substr($end_yymm_obj[0],2,2);
          $end_yymm=$end_year.$end_month;
          $payment_insert=array();
          $payment_insert['dist_code']  = $row->created_by_dist_code;
          $payment_insert['ben_id']  = $row->beneficiary_id;
          $payment_insert['apr_lot_type']  = 'R';
          $payment_insert['may_lot_type']  = 'R';
          $payment_insert['jun_lot_type']  = 'R';
          $payment_insert['jul_lot_type']  = 'R';
          $payment_insert['aug_lot_type']  = 'R';
          $payment_insert['sep_lot_type']  = 'R';
          $payment_insert['oct_lot_type']  = 'R';
          $payment_insert['nov_lot_type']  = 'R';
          $payment_insert['dec_lot_type']  = 'R';
          $payment_insert['jan_lot_type']  ='R';
          $payment_insert['feb_lot_type']  = 'R';
          $payment_insert['mar_lot_type']  = 'R';
          $payment_insert['last_accno']  = trim($row_bank->bank_code);
          $payment_insert['last_ifsc']  = trim($row_bank->bank_ifsc);
          $payment_insert['ben_status']  = 1;
          $payment_insert['ben_name']  = $ben_fullname;
          $payment_insert['caste']  = substr($row->caste,0,2);
          $payment_insert['created_at']  = $row->created_at;
          $payment_insert['local_body_code']  = $row->created_by_local_body_code;
          $payment_insert['ss_card_no']  = $row->ss_card_no;
          $payment_insert['mobile_no']  = $row->mobile_no;
          $payment_insert['application_id']  = $row->application_id;
          $payment_insert['start_yymm']  = $start_yymm;
          $payment_insert['end_yymm']  = $end_yymm;
          $payment_insert['rural_urban_id']  = $row_contact->rural_urban_id;
          $payment_insert['block_ulb_code']  = $row_contact->block_ulb_code;
          $payment_insert['gp_ward_code']  = $row_contact->gp_ward_code;
          $payment_insert['ds_phase']  = $row->ds_phase;
          $is_update_payment=DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->insert($payment_insert);
        }
        else{
          $end_yymm = Carbon::parse($row->new_dob)->addYears(60);
          $end_yymm_obj = explode('-', $end_yymm);
          $end_month = $end_yymm_obj[1];
          $end_day   = $end_yymm_obj[2];
          $end_year  = substr($end_yymm_obj[0],2,2);
          $end_yymm=$end_year.$end_month;
          $input_payment = ['end_yymm' => $end_yymm];
          $is_update_payment=$model_payment->where('application_id', $request->application_id)->update($input_payment);

        }
        try {
        if ($is_update && $is_saved_log && $is_update_payment) {
          DB::commit();
          DB::connection('pgsql_payment')->commit();
          DB::connection('pgsql_appwrite')->commit();
          return redirect('backfromjb')->with('message', 'Request has been Approved and DOB has been changed');
        } else {
          DB::connection('pgsql_payment')->rollBack();
          DB::rollback();
          DB::connection('pgsql_appwrite')->rollback();
          // dd('13');
          // return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)->with('error', 'Error! Please try again.');
          $return_msg = 'Error! Please try again.';
          return redirect("/showbackfromjb/".$request->application_id."/".$request->is_faulty)
              ->withErrors($return_msg);
        }
      }catch (\Exception $e) {
        dd($e);
      }
    }
     
    } else {
      return redirect("/")->with('danger', 'Not Allowed');
    }
  }
  
  function ageCalculate($dob)
  {
    $diff = 0;
    if ($dob != '') {
      //$diff = $this->ageCalculate($dob);
      $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
    }
    return $diff;
  }
}
