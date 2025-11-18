<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\District;
use App\Models\getModelFunc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Helpers\Helper;
use Maatwebsite\Excel\Facades\Excel;

class LotReportController extends Controller
{
    public function __construct()
    {
        set_time_limit(120);
        // $this->middleware('auth');
    }
    public function index()
    {
    }
    // ###############  Function for validation lot report ###########################
    public function reportValidationLot(Request $request)
    {

        $districts = District::select(['district_code', 'district_name'])->get();
        $validationLotPhase = DB::connection('pgsql_master')->table('master_mgmt.phase_month_master')->where('vl_status', 1)->get();
        if ($request->ajax()) {

            $dist_code = $request->district_code;
            $phase_code = $request->phase_code;
            $getModelFunc = new getModelFunc();
             $schemaname = $getModelFunc->getSchemaDetails();
            //$schemaname = 'trx_mgmt_2122_archive';
            $query = "Select district_name	   				District,
            coalesce(sum(total_beneficiary),0) 				as total_beneficiary,
            coalesce(sum(sc_beneficiary),0) 				as sc_beneficiary,
            coalesce(sum(st_beneficiary),0) 				as st_beneficiary,
            coalesce(sum(ot_beneficiary),0) 				as ot_beneficiary,
            coalesce(sum(validation_initiated),0)			as validation_initiated,
            coalesce(sum(validation_not_inititated),0)		as validation_not_inititated,
            coalesce(sum(validation_complete),0)			as validation_complete,
            coalesce(sum(validation_success),0)				as validation_success,
            coalesce(sum(validation_failed),0) 				as validation_failed,
            coalesce(sum(ot_success), 0) as ot_success,
            coalesce(sum(sc_success), 0) as sc_success,
            coalesce(sum(st_success), 0) as st_success,

             sum(total_beneficiary-(validation_success+validation_failed)) as validation_pending,
            dist_code as dist_code
            from public.m_district d left join
            (
                select dist_code,
            count(1) 												as total_beneficiary,
            sum(case when caste='SC' then 1 else 0 end )				as sc_beneficiary,
            sum(case when caste='ST' then 1 else 0 end )				as st_beneficiary,
            sum(case when caste='OT' then 1 else 0 end )				as ot_beneficiary,
            sum(case when acc_validated!='0' then 1 else 0 end ) 	as validation_initiated,
            sum(case when (acc_validated='0' and ben_status=1) then 1 else 0 end )		as validation_not_inititated,
            sum(case when acc_validated in('2','3','6','7') then 1 else 0 end ) as validation_complete,
            sum(case when acc_validated in('2','6') then 1 else 0 end)          as validation_success,
            sum(case when acc_validated in('3','7') then 1 else 0 end)      as validation_failed,
            sum(
                case
                    when acc_validated in('2','6') and caste='OT' then 1
                    else 0
                end
            ) as ot_success,
		  sum(
                case
                    when acc_validated in('2','6') and caste='SC' then 1
                    else 0
                end
            ) as sc_success,
		  sum(
                case
                    when acc_validated in('2','6') and caste='ST' then 1
                    else 0
                end
            ) as st_success


            FROM " . $schemaname . ".ben_payment_details";


            if (!empty($phase_code)) {
                $query .= " where ds_phase =" . $phase_code;
            }
            $query .= " group by dist_code
            )b  on d.district_code = b.dist_code";


            if (!empty($dist_code)) {
                $query .= " where b.dist_code =" . $dist_code;
            }


            $query .= " group by district_name,dist_code order by district_name,dist_code";

            ;

            //echo $query;die;
            $data = DB::connection('pgsql_payment')->select($query);

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('district', function ($data) {

                    return $data->district;
                })
                ->addColumn('total_beneficiary', function ($data) {

                    return $data->total_beneficiary;
                })
                ->addColumn('sc_beneficiary', function ($data) {

                    return $data->sc_beneficiary;
                })
                ->addColumn('st_beneficiary', function ($data) {

                    return $data->st_beneficiary;
                })
                ->addColumn('ot_beneficiary', function ($data) {

                    return $data->ot_beneficiary;
                })
                ->addColumn('validation_initiated', function ($data) {

                    return $data->validation_initiated;
                })
                ->addColumn('validation_complete', function ($data) {

                    return $data->validation_complete;
                })
                ->addColumn('validation_not_inititated', function ($data) {
                    return $data->validation_not_inititated;
                })
                ->addColumn('validation_complete', function ($data) {
                    return $data->validation_complete;
                })
                ->addColumn('validation_success', function ($data) {
                    return $data->validation_success;
                })
                ->addColumn('validation_failed', function ($data) {
                    return $data->validation_failed;
                })
                ->addColumn('validation_failed', function ($data) {
                    return $data->validation_failed;
                })
                ->addColumn('ot_success', function ($data) {
                    return $data->ot_success;
                })
                ->addColumn('sc_success', function ($data) {
                    return $data->sc_success;
                })
                ->addColumn('st_success', function ($data) {
                    return $data->st_success;
                })
                ->addColumn('naming_failed', function ($data) use($schemaname,$phase_code){ //echo $data->dist_code;die;

                    $query_name_validation="select count(b.application_id) as total from  " . $schemaname . ".ben_payment_details b
                    join lb_main.av_lot_details l  on l.ben_id=b.ben_id where l.updated_at::date>='2022-04-12' and l.name_response <>'' and acc_validated in('3','7') and b.dist_code=".$data->dist_code;
                    if(!empty($data->dist_code)){
                    if(!empty($phase_code)){
                    $query_name_validation .= "  and l.av_ds_phase  =".$phase_code;
                    }
                    $executNamingfailedeQuery= DB::connection('pgsql_payment')->select($query_name_validation);

                    return $executNamingfailedeQuery[0]->total;

                }
                    else{
                    return 0;
                    }
                // echo $query_name_validation;die;

                })



                ->rawColumns(['district', 'total_beneficiary','ot_beneficiary','sc_beneficiary','st_beneficiary', 'validation_initiated', 'validation_complete', 'validation_not_inititated', 'validation_complete', 'validation_success', 'validation_failed', 'validation_pending','naming_failed'])
                ->make(true);
        } else {
            return view('lot_report/index_report_validation_lot')->with('districts', $districts)->with('vlp', $validationLotPhase);
        }
    }
    // ###############  Function for payment lot report ###########################
    public function reportPaymentLot(Request $request)
    {

        $districts = District::select(['district_code', 'district_name'])->get();
        $paymentLotPhase = DB::connection('pgsql_master')->table('master_mgmt.phase_month_master')->where('pl_status', 1)->get();
        if ($request->ajax()) {
            $phase_code = $request->phase_code;
            $dist_code = $request->district_code;
            $lot_year  = $request->lot_year;
            $lot_month = $request->lot_month;

            $getModelFunc = new getModelFunc();
            // $schemaname = $getModelFunc->getSchemaDetails();
            if( $lot_year=='2021-2022'){
                $schemaname = 'trx_mgmt_2122_archive';
            }
            else{
                $schemaname = $getModelFunc->getSchemaDetails($lot_year);
            }
            $getMonthColumn = Helper::getMonthColumn($lot_month);
            // New 30-09-2021
            $yearArr = explode('-', $lot_year);
            if ($lot_month >= 4 and  $lot_month <= 12) {
                $final_yymm = substr($yearArr[0], 2) . str_pad($lot_month, 2, "0", STR_PAD_LEFT);
            } else {
                $final_yymm = substr($yearArr[1], 2) . str_pad($lot_month, 2, "0", STR_PAD_LEFT);
            }

            // Dynamic amount month wise
            $amounts = DB::connection('pgsql_payment')->table('master_mgmt.amount_master')
            ->where('wef_yymm', '<=', $final_yymm)
            ->where(function ($query) use($final_yymm) {
                $query->whereNull('upto_yymm')
                ->orWhere('upto_yymm', '>=', $final_yymm);
            })->get();
            // dd($amounts);
            $ot_amount=0;
            $scst_amount=0;
            foreach ($amounts as $key) {
                if($key->caste == 'OT'){
                    $ot_amount = $key->amount;
                }
                else {
                    $scst_amount = $key->amount;
                }
            }

            $query = " Select district_name                             as district,
            sum(total_beneficiary)                                      as total_beneficiary,
            sum(misc_error)                                        		as misc_error,
            sum(age_60_above)                                        	as age_60_above,
            sum(probable_payment)                                       as probable_payment,
            sum(ot_count)                                     			as ot_count,
            sum(sc_count)                                      			as sc_count,
            sum(st_count)                                      			as st_count,
            sum(ot_count *".$ot_amount.")                                          as ot_amount,
            sum(sc_count *".$scst_amount.")                                         as sc_amount,
            sum(st_count *".$scst_amount.")                                         as st_amount,
            sum(ot_count *".$ot_amount.")+sum(sc_count *".$scst_amount.")+sum(st_count *".$scst_amount.")  as total_amount,
            sum(lot_generated)                                          as lot_generated,
            sum(lot_not_generated)                                      as lot_not_generated,

            sum(push_to_bank)                                           as push_to_bank,
            sum(push_to_bank_sc)                                        as push_to_bank_sc,
            sum(push_to_bank_st)                                        as push_to_bank_st,
            sum(push_to_bank_ot)                                        as push_to_bank_ot,
            sum(push_to_bank_sc*".$scst_amount.")                                   as push_to_bank_sc_amount,
            sum(push_to_bank_st*".$scst_amount.")                                   as push_to_bank_st_amount,
            sum(push_to_bank_ot*".$ot_amount.")                                    as push_to_bank_ot_amount,
            sum(push_to_bank_sc*".$scst_amount.")+sum(push_to_bank_st*".$scst_amount.") + sum(push_to_bank_ot*".$ot_amount.")          as push_to_bank_amount,
            sum(response_received)                                      as response_received,
            sum(payment_success)                                        as payment_success,
            sum(payment_failure)                                        as payment_failure,
            sum(bank_edited)                                        	as bank_edited
            ,SUM(deactivate_ben)                                                              AS deactivate_ben
            ,SUM(bank_rejected)                                                               AS bank_rejected
            ,SUM(name_rejected)                                                               AS name_rejected
            ,SUM(under_caste_change)                                                          AS under_caste_change
            ,SUM(under_duplicate_bank)                                                        AS under_duplicate_bank
            ,SUM(under_duplicate_aadhar)                                                      AS under_duplicate_aadhar
            ,SUM(validation_payment_failure)                                                  AS validation_payment_failure

            from public.m_district d left join
            (
            select dist_code,
            count(1) as total_beneficiary,
            sum(case when caste='OT' then 1 else 0 end )	as ot_count,
            sum(case when caste='SC' then 1 else 0 end )	as sc_count,
            sum(case when caste='ST' then 1 else 0 end )	as st_count,
            sum(case when  " . $getMonthColumn['lot_status'] . "<>'R'   then 1 else 0 end )    as lot_generated,
            sum(case when  acc_validated in('2','6') and " . $getMonthColumn['lot_status'] . " in('R','E' )  and ben_status=1 and end_yymm>=".$final_yymm." and (is_caste_changed is null or  effective_yymm<=".$final_yymm." ) and ds_phase in(select ds_phase from master_mgmt.phase_month_master where start_yymm<=".$final_yymm.") then 1 else 0 end )  as lot_not_generated,
            sum(case when  " . $getMonthColumn['lot_status'] . " not in('R','G','H')  then 1 else 0 end )   as push_to_bank,
            sum(case when  caste ='SC' and " . $getMonthColumn['lot_status'] . " not in('R','G','H')   then 1 else 0 end )     as push_to_bank_sc,
            sum(case when  caste ='ST' and " . $getMonthColumn['lot_status'] . " not in('R','G','H')   then 1 else 0 end )     as push_to_bank_st,
            sum(case when  caste ='OT' and " . $getMonthColumn['lot_status'] . " not in('R','G','H')   then 1 else 0 end )     as push_to_bank_ot,
            sum(case when  " . $getMonthColumn['lot_status'] . " in('S','F','E','H') then 1 else 0 end)  as response_received,
            sum(case when  " . $getMonthColumn['lot_status'] . "='S' then 1 else 0 end)        as payment_success,
            sum(case when  " . $getMonthColumn['lot_status'] . "='F' then 1 else 0 end)        as payment_failure,
            sum(case when  " . $getMonthColumn['lot_status'] . "='E' then 1 else 0 end)        as bank_edited,
            sum(case when  end_yymm<".$final_yymm."   and " . $getMonthColumn['lot_status'] . "  ='R' then 1 else 0 end)       				as age_60_above,
            sum(case when  ben_status not in(1,-102,-97,0) and " . $getMonthColumn['lot_status'] . "  ='R' and end_yymm>=".$final_yymm."  then 1 else 0 end) 	as misc_error,

            sum(case when  ben_status in(1,-102,-97,0)  and " . $getMonthColumn['lot_status'] . "  ='R' and end_yymm>=".$final_yymm."  then 1 else 0 end)   as probable_payment
            ,SUM(case WHEN ben_status  in(-99,-30,9,77) AND " . $getMonthColumn['lot_status'] . " ='R' AND end_yymm>=".$final_yymm." THEN 1 else 0 end) AS deactivate_ben
            ,SUM(case WHEN ben_status  =-98 	AND " . $getMonthColumn['lot_status'] . " ='R' AND end_yymm>=".$final_yymm." THEN 1 else 0 end) 	  AS  bank_rejected
            ,SUM(case WHEN ben_status  =-400 	AND " . $getMonthColumn['lot_status'] . " ='R' AND end_yymm>=".$final_yymm." THEN 1 else 0 end) 	  AS  name_rejected,
            SUM(case  WHEN ben_status  =-102 	AND " . $getMonthColumn['lot_status'] . " ='R' AND end_yymm>=".$final_yymm." THEN 1 else 0 end)     AS  under_caste_change,
            SUM(case  WHEN ben_status  =-97 	AND " . $getMonthColumn['lot_status'] . " ='R' AND end_yymm>=".$final_yymm." THEN 1 else 0 end)     AS  under_duplicate_bank,
            SUM(case  WHEN ben_status  =0 		AND " . $getMonthColumn['lot_status'] . " ='R' AND end_yymm>=".$final_yymm." THEN 1 else 0 end)     AS  under_duplicate_aadhar,
            SUM(case  WHEN ben_status  =1 		AND " . $getMonthColumn['lot_status'] . " ='R' AND end_yymm>=".$final_yymm."  THEN 1 else 0 end)     AS  validation_payment_failure
            FROM " . $schemaname . ".ben_payment_details ";
            if(!empty($phase_code)){
                $query .=  " where ds_phase=".$phase_code." ";
            }
            $query .= "  group by dist_code
            )b  on d.district_code = b.dist_code group by district_name
            order by district_name";
            // return $query;
            //and  start_yymm<=". $final_yymm ."
            $data = DB::connection('pgsql_payment')->select($query);
            // print_r($report);
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('district', function ($data) {
                    return $data->district;
                })
                ->addColumn('total_beneficiary', function ($data) {
                    return $data->total_beneficiary;
                })
                ->addColumn('sc_count', function ($data) {
                    return $data->sc_count;
                })
                ->addColumn('st_count', function ($data) {
                    return $data->st_count;
                })
                ->addColumn('ot_count', function ($data) {
                    return $data->ot_count;
                })
                ->addColumn('total_amount', function ($data) {
                    return $data->total_amount;
                })
                ->addColumn('ot_amount', function ($data) {
                    return $data->ot_amount;
                })
                ->addColumn('sc_amount', function ($data) {
                    return $data->sc_amount;
                })
                ->addColumn('st_amount', function ($data) {
                    return $data->st_amount;
                })
                ->addColumn('lot_generated', function ($data) {
                    return $data->lot_generated;
                })
                ->addColumn('lot_not_generated', function ($data) {
                    return $data->lot_not_generated;
                })
                ->addColumn('push_to_bank', function ($data) {
                    return $data->push_to_bank;
                })
                ->addColumn('push_to_bank_amount', function ($data) {
                    return $data->push_to_bank_amount;
                })
                ->addColumn('payment_success', function ($data) {
                    return $data->payment_success;
                })
                ->addColumn('payment_failure', function ($data) {
                    return $data->payment_failure;
                })
                ->addColumn('bank_edited', function ($data) {
                    return $data->bank_edited;
                })
                ->addColumn('misc_error', function ($data) {
                    return $data->misc_error;
                })
                ->addColumn('age_60_above', function ($data) {
                    return $data->age_60_above;
                })
                ->addColumn('probable_payment', function ($data) {
                    return $data->probable_payment;
                })
                 ->addColumn('deactivate_ben', function ($data) {
                    return $data->deactivate_ben;
                })
                ->addColumn('bank_rejected', function ($data) {
                    return $data->bank_rejected;
                })

                ->addColumn('name_rejected', function ($data) {
                    return $data->name_rejected;
                })

                ->addColumn('under_caste_change', function ($data) {
                    return $data->under_caste_change;
                })

                ->addColumn('under_duplicate_bank', function ($data) {
                    return $data->under_duplicate_bank;
                })

                ->addColumn('under_duplicate_aadhar', function ($data) {
                    return $data->under_duplicate_aadhar;
                })

                ->addColumn('validation_payment_failure', function ($data) {
                    return $data->validation_payment_failure;
                })
                ->addColumn('push_to_bank_ot_amount', function ($data) {
                    return $data->push_to_bank_ot_amount;
                })
                ->addColumn('push_to_bank_st_amount', function ($data) {
                    return $data->push_to_bank_st_amount;
                })
                ->addColumn('push_to_bank_sc_amount', function ($data) {
                    return $data->push_to_bank_sc_amount;
                })
                ->rawColumns([
                    'district', 'total_beneficiary', 'sc_count', 'st_count', 'ot_count',
                    'ot_amount', 'sc_amount', 'st_amount', 'total_amount', 'lot_generated', 'lot_not_generated', 'push_to_bank', 'push_to_bank_amount', 'payment_success', 'payment_failure', 'bank_edited',
                    'misc_error','age_60_above','probable_payment','response_received','deactivate_ben','bank_rejected','name_rejected',
                    'under_caste_change','under_duplicate_bank','under_duplicate_aadhar','validation_payment_failure','push_to_bank_ot_amount','push_to_bank_st_amount','push_to_bank_sc_amount'
                ])
                ->make(true);
        } else {
            return view('lot_report/index_report_payment_lot')->with('districts', $districts)->with('plp', $paymentLotPhase);
        }
    }

    private function getPaymentLotQuery($dist_code = '', $lot_year = '', $lot_month = '')
    {

        $getModelFunc = new getModelFunc();
        $schemaname   = $getModelFunc->getSchemaDetails();

        if ($dist_code != '' &&  $lot_year != '' &&  $lot_month != '') {

            $query = "Select district_name	   				district,
            sum(total_beneficiary)  				as total_beneficiary,
            sum(applicable_for_payment)  				as applicable_for_payment,
            sum(payment_inititated)				as payment_inititated,
            sum(payment_not_initiated)			as payment_not_initiated,
            sum(ot_count *500)				as ot_amount,
            sum(sc_st_count *1000)					as sc_st_amount,
            sum(payment_success)					as payment_success,
            sum(payment_failure)					as payment_failure

            from m_district d left join
            (
                select dist_code,
            count(1) 												as total_beneficiary,
            sum(case when acc_validated='2' and sep_lot_status='R' then 1 else 0 end ) 	as applicable_for_payment,
            sum(case when   sep_lot_status!='R' then 1 else 0 end )	as payment_inititated,
            sum(case when   acc_validated='2' and sep_lot_status='R' then 1 else 0 end ) 	as payment_not_initiated,
            sum(case when  acc_validated!='0' and caste ='OT' and sep_lot_status !='R' then 1 else 0 end)  		as ot_count,
            sum(case when  acc_validated!='0' and caste in('SC','ST') and sep_lot_status !='R' then 1 else 0 end)  as sc_st_count,
            sum(case when acc_validated='4' and sep_lot_status='S' then 1 else 0 end)  		as payment_success,
            sum(case when acc_validated='3' and sep_lot_status='F' then 1 else 0 end)  		as payment_failure
            FROM " . $schemaname . ".ben_payment_details
            group by dist_code
            )b  on d.district_code = b.dist_code";

            $query = " group by district_name
            order by district_name";

            return $query;
        } elseif ($dist_code != '' &&  $lot_year != '') {

            $query = "select  m.district_name,  ben_cnt , lot_ben_cnt, succ_count, fail_count, sum_amount_debit
                from
                (
                    select dist_code,count(1) ben_cnt from " . $schemaname . ".ben_payment_details group by dist_code
                )bpd inner join
                (
                 select dist_code,  lot_year,  SUM(ben_count) AS lot_ben_cnt, SUM(success_count) AS succ_count
                , SUM(failed_count) AS fail_count, SUM(amount_debit) AS sum_amount_debit from " . $schemaname . ".lot_master
                 group by dist_code ,  lot_year
                )lm  on lm.dist_code=bpd.dist_code
                inner join public.m_district m on bpd.dist_code=m.district_code ";

            $query .= " where lm.dist_code =" . $dist_code;
            $query .= " and lm.lot_year ='" . $lot_year . "' order by m.district_name";
            //$query .= " order by m.district_name";
            return $query;
        } elseif ($lot_year != '' && $lot_month != '') {

            $query = "select  m.district_name,  ben_cnt , lot_ben_cnt, succ_count, fail_count, sum_amount_debit
                from
                (
                    select dist_code,count(1) ben_cnt from " . $schemaname . ".ben_payment_details group by dist_code
                )bpd inner join
                (
                 select dist_code, lot_year, lot_month , SUM(ben_count) AS lot_ben_cnt, SUM(success_count) AS succ_count
                , SUM(failed_count) AS fail_count, SUM(amount_debit) AS sum_amount_debit from " . $schemaname . ".lot_master
                 group by dist_code , lot_year, lot_month
                )lm  on lm.dist_code=bpd.dist_code
                inner join public.m_district m on bpd.dist_code=m.district_code ";

            $query .= " and lm.lot_year ='" . $lot_year . "' and lm.lot_month =" . $lot_month . " order by m.district_name";
            return $query;
        } elseif ($dist_code != '') {

            $query = "select  m.district_name,  ben_cnt , lot_ben_cnt, succ_count, fail_count, sum_amount_debit
                from
                (
                    select dist_code,count(1) ben_cnt from " . $schemaname . ".ben_payment_details group by dist_code
                )bpd inner join
                (
                 select dist_code,  SUM(ben_count) AS lot_ben_cnt, SUM(success_count) AS succ_count
                , SUM(failed_count) AS fail_count, SUM(amount_debit) AS sum_amount_debit from " . $schemaname . ".lot_master
                 group by dist_code
                )lm  on lm.dist_code=bpd.dist_code
                inner join public.m_district m on bpd.dist_code=m.district_code ";

            $query .= " where lm.dist_code =" . $dist_code;
            $query .= " order by m.district_name";
            return $query;
        } elseif ($lot_year != '') {

            $query = "select  m.district_name,  ben_cnt , lot_ben_cnt, succ_count, fail_count, sum_amount_debit
                from
                (
                    select dist_code,count(1) ben_cnt from " . $schemaname . ".ben_payment_details group by dist_code
                )bpd inner join
                (
                 select dist_code, lot_year,  SUM(ben_count) AS lot_ben_cnt, SUM(success_count) AS succ_count
                , SUM(failed_count) AS fail_count, SUM(amount_debit) AS sum_amount_debit from " . $schemaname . ".lot_master
                 group by dist_code , lot_year
                )lm  on lm.dist_code=bpd.dist_code
                inner join public.m_district m on bpd.dist_code=m.district_code ";

            $query .= " and lm.lot_year ='" . $lot_year . "' order by m.district_name";
            return $query;
        } else {

            $query = "select  m.district_name,  ben_cnt , lot_ben_cnt, succ_count, fail_count, sum_amount_debit
                from
                (
                    select dist_code,count(1) ben_cnt from " . $schemaname . ".ben_payment_details group by dist_code
                )bpd inner join
                (
                 select dist_code,  SUM(ben_count) AS lot_ben_cnt, SUM(success_count) AS succ_count
                , SUM(failed_count) AS fail_count, SUM(amount_debit) AS sum_amount_debit from " . $schemaname . ".lot_master
                 group by dist_code
                )lm  on lm.dist_code=bpd.dist_code
                inner join public.m_district m on bpd.dist_code=m.district_code ";

            $query .= " order by m.district_name";
            return $query;
        }
    }


    public function getNameMismatchValidationList(Request $request)
    {
        $districts = District::select(['district_code', 'district_name'])->get();
        if ($request->ajax()) {

            $dist_code = $request->district_code;
            $phase_code = $request->phase_code;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $query = "select  b.application_id,b.ben_name,b.last_accno,b.last_ifsc,l.name_response from  " . $schemaname . ".ben_payment_details b
           join lb_main.av_lot_details l  on l.ben_id=b.ben_id where l.updated_at::date>='2022-04-12' and l.name_response <>'' and acc_validated in('3','7')  ";
            if (!empty($dist_code)) {
                $query .= " and b.dist_code =" . $dist_code;
            }
          //  $query .= " limit 4";
            //echo $query;die;
            $data = DB::connection('pgsql_payment')->select($query);
            // print_r($report);
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('application_id', function ($data) {

                    return $data->application_id;
                })
                ->addColumn('ben_name', function ($data) {

                    return $data->ben_name;
                })
                ->addColumn('last_accno', function ($data) {

                    return $data->last_accno;
                })
                ->addColumn('last_ifsc', function ($data) {

                    return $data->last_ifsc;
                })
                ->addColumn('name_response', function ($data) {
                    return $data->name_response;
                })


                ->rawColumns(['application_id', 'ben_name', 'last_accno', 'last_ifsc', 'name_response'])
                ->make(true);
        } else {
            return view('lot_report/name_mismatch_validation_list')->with('districts', $districts);
        }
    }

    public function getNameMismatchExcelList(Request $request)
    {
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails();
        $query = "select b.application_id,b.ben_name,b.last_accno,b.last_ifsc,l.name_response from  " . $schemaname . ".ben_payment_details b
        join lb_main.av_lot_details l  on l.ben_id=b.ben_id where l.updated_at::date>='2022-04-12' and l.name_response <>'' and acc_validated in('3','7') ";
        if (!empty($dist_code)) {
            $query .= " and b.dist_code =" . $dist_code;
        }
     //   $query .= " limit 4";
        //echo $query;die;
        $getFailedData = DB::connection('pgsql_payment')->select($query);
        $excelarr[] = array(
            'Application ID',  'Beneficiary Name',  'Account No', 'IFSC Code', 'Name From Bank Validation',
        );
        foreach ($getFailedData as $arr) {


            $excelarr[] = array(
                'Application Id' => trim($arr->application_id),
                'Beneficiary Name' => trim($arr->ben_name),

                'Account No' => trim($arr->last_accno),
                'IFSC Code' => trim($arr->last_ifsc),
                'Name From Bank Validation' => trim($arr->name_response),

            );
        }


        $file_name = 'Name Validation List' . date('d/m/Y');
        Excel::create($file_name, function ($excel) use ($excelarr) {
            $excel->setTitle('Name Validation List');
            $excel->sheet('Name Validation List', function ($sheet) use ($excelarr) {
                $sheet->fromArray($excelarr, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    // Monthwise Payment report
    public function getMonthWisePayReport() {
        return view('lot_report.monthwise_payment_report');
    }
    public function totalMonthwisePaymentReport(Request $request) {
        if ($request->ajax()) {
            $phase_code = $request->phase_code;
            $fin_year = $request->lot_year;
            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();

            // Get Current Financial Year
            $currentyear = date('Y');
            $prevYear = date('Y') - 1;
            $nextyear = date('Y') + 1;
            $month = date('n');
            if ($month > 3) {
                $curfy = $currentyear . '-' . $nextyear;
            } else {
                $curfy = $prevYear . '-' . ($prevYear + 1);
            }

            if ($fin_year == $curfy) {
                $explodeYear = explode("-", $curfy);
                $firstPart = substr($explodeYear[0], 2);
                $secondPart = substr($explodeYear[1], 2);
                $schema = 'lot_details_archive_' . $firstPart . $secondPart;

                $query = "SELECT ld_lot_month,month_name,sum(amount) as amount from (SELECT ld_lot_month,to_char(to_timestamp (ld_lot_month::text, 'MM'), 'Month') AS month_name ,sum(amount_rs)  as amount
                    from bandhan.lot_details ld where status_code='00' group by ld_lot_month
                    union all
                    SELECT ld_lot_month,to_char(to_timestamp (ld_lot_month::text, 'MM'), 'Month') AS month_name ,sum(amount_rs)  as amount
                    from lb_archive." . $schema . " ld where status_code='00' group by ld_lot_month) t
                    group by ld_lot_month,month_name
                    ORDER BY
                    CASE
                        WHEN ld_lot_month = '4' THEN 0
                        WHEN ld_lot_month = '5' THEN 1
                        WHEN ld_lot_month = '6' THEN 2
                        WHEN ld_lot_month = '7' THEN 3
                        WHEN ld_lot_month = '8' THEN 4
                        WHEN ld_lot_month = '9' THEN 5
                        WHEN ld_lot_month = '10' THEN 6
                        WHEN ld_lot_month = '11' THEN 7
                        WHEN ld_lot_month = '12' THEN 8
                        WHEN ld_lot_month = '1' THEN 9
                        WHEN ld_lot_month = '2' THEN 10
                        WHEN ld_lot_month = '3' THEN 11
                        ELSE 99
                    END  ASC ";
            } else {
                $explodeYear = explode("-", $fin_year);
                $firstPart = substr($explodeYear[0], 2);
                $secondPart = substr($explodeYear[1], 2);
                $schema = 'lot_details_archive_' . $firstPart . $secondPart;

                $query = "SELECT ld_lot_month,month_name,sum(amount) as amount from (
                    SELECT ld_lot_month,to_char(to_timestamp (ld_lot_month::text, 'MM'), 'Month') AS month_name ,sum(amount_rs)  as amount
                    FROM lb_archive." . $schema . " ld where status_code='00' group by ld_lot_month
                    ) t
                    group by ld_lot_month,month_name
                    ORDER BY
                    CASE
                        WHEN ld_lot_month = '4' THEN 0
                        WHEN ld_lot_month = '5' THEN 1
                        WHEN ld_lot_month = '6' THEN 2
                        WHEN ld_lot_month = '7' THEN 3
                        WHEN ld_lot_month = '8' THEN 4
                        WHEN ld_lot_month = '9' THEN 5
                        WHEN ld_lot_month = '10' THEN 6
                        WHEN ld_lot_month = '11' THEN 7
                        WHEN ld_lot_month = '12' THEN 8
                        WHEN ld_lot_month = '1' THEN 9
                        WHEN ld_lot_month = '2' THEN 10
                        WHEN ld_lot_month = '3' THEN 11
                        ELSE 99
                    END  ASC ";
            }
            // print $query; die;
            $data = DB::connection('pgsql_payment')->select($query);
            return datatables()->of($data)->make(true);
        }
    }


    public function reportDatewiseLot(Request $request)
    {
        if ($request->ajax()) {
            $dist_code = $request->district_code;
            $phase_code = $request->phase_code;
            $lot_year=$request->lot_year;
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $getModelFunc = new getModelFunc();
             $schemaname = $getModelFunc->getSchemaDetails($lot_year);
             if($schemaname=='trx_mgmt_2122'){
                $schemaname='trx_mgmt_2122_archive';
            } else if($schemaname=='trx_mgmt_2223'){
                $schemaname='trx_mgmt_2223';
            }
            else{
                $schemaname='payment';
            }
            //$schemaname = 'trx_mgmt_2122_archive';
            $query = "select   created_at::date,to_char( created_at::date, 'DD/MM/YYYY') as creation_date, count(1) as total_lot,sum(debit_amount) as total_debit_amount,
            sum(ben_count) as total_bencount, sum(success_count) as total_success,sum(amount_debit) as total_amount_debit from bandhan.lot_master where lot_no>0";

            if(!empty($from_date)){
                $query .= " and created_at::date >='".date('Y-m-d', strtotime(trim(str_replace('/', '-', $from_date))))."'::date";
            }
            if(!empty($to_date)){
                $query .= " and created_at::date <='".date('Y-m-d', strtotime(trim(str_replace('/', '-', $to_date))))."'::date";
            }
            $query .= " group by  to_char( created_at::date, 'DD/MM/YYYY') , created_at::date order by  created_at::date desc";
           // echo $query;die;
            $data = DB::connection('pgsql_payment')->select($query);

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('creation_date', function ($data) {
                    return $data->creation_date;
                })
                ->addColumn('total_lot', function ($data) {
                    return $data->total_lot;
                })
                ->addColumn('total_amount', function ($data) {
                    return $data->total_debit_amount;
                })
                ->addColumn('total_bencount', function ($data) {
                    return $data->total_bencount;
                })
                ->rawColumns([ 'creation_date', 'total_lot', 'total_amount','total_bencount'])
                ->make(true);
        } else {
            return view('lot_report/index_date_wise_lot_report');
        }
    }

    // Financial Assistance Payable
    public function lotGeneratedPendingIndex(Request $request) {
        $phase_master = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')
        ->where('pl_status', 1)
        ->get();
        return view('lot_report/financial_assistance_payble')->with('ds_phase', $phase_master);
    }

    public function lotGeneratedPendingAmountReport(Request $request) {
        if ($request->ajax()) {
            $request_phase = $request->ds_phase;
            // Payment Lot Generation Pending
            $monthConstArr = array(1=>'jan', 2=>'feb', 3=>'mar', 4=>'apr', 5=>'may',6=>'jun', 7=>'jul', 8=>'aug', 9=>'sep', 10=>'oct', 11=>'nov', 12=>'dec');
            $monthArr = array(0=>4, 1=>5, 2=>6, 3=>7, 4=>8, 5=>9, 6=>10, 7=>11, 8=>12, 9=>1, 10=>2, 11=>3);
            if (!is_null($request_phase)) {
                $allPhase = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')->where('ds_phase', $request_phase)->where('pl_status', 1)->get();
            } else {
                $allPhase = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')->where('pl_status', 1)->get();
            }
            $query = '';
            $valQuery ="";
            $totalPayMonthArr=array();
            foreach ($allPhase as $key) {
                $phase = $key->ds_phase;
                $phase_condition = "";
                if ($phase == 0) {
                    $phase_condition = " and ds_phase is null ";
                } else {
                    $phase_condition = " and ds_phase = " . $phase . " ";
                }

                // Get Current Financial Year
                if (date('m') <= 3) {
                    $current_financial_year = (date('Y')-1) . '-' . date('Y');
                } else {
                    $current_financial_year = date('Y') . '-' . (date('Y') + 1);
                }

                $phaseTableFinYear = $key->starting_year;
			    $phaseTableMonth = $key->starting_month;
                $phaseTableMonthIndex = array_search($phaseTableMonth, $monthArr);
			    $currentMonthIndex = array_search(date('n'), $monthArr);
                $total_pay_month = 0;
                if ($phaseTableFinYear == $current_financial_year) {
					if ($phaseTableMonthIndex <= $currentMonthIndex) {
						$total_pay_month = ($currentMonthIndex-$phaseTableMonthIndex)+1;
					}
				}
				else {
					$total_pay_month = $currentMonthIndex+1;
				}
                $totalPayMonthArr[] = ['ds_phase' => $phase, 'pay_month' => $total_pay_month, 'ds_phase_name_val' => $key->ds_phase_name];

                $query .= "select ds_phase,
                caste,
                SUM(apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status) as total_beneficiary_payment,
                apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status,
                (
                    case
                        when caste in('SC', 'ST') then (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1200
                        else (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1000
                    end
                ) as total_amount
                from
                (
                    select
                        ds_phase,
                        caste, ";
                foreach ($monthArr as $key => $val) {
                    $statusColumn = $monthConstArr[$val];
                    $yearArr = explode('-', $current_financial_year);
                    if ($val >= 4 and  $val <= 12) {
                        $final_yymm = substr($yearArr[0], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    } else {
                        $final_yymm = substr($yearArr[1], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    }
                    $query .= " sum(case
                        when  " . $statusColumn . "_lot_status in('R','E' )  and ben_status=1 and end_yymm>=" . $final_yymm . " and
                        (is_caste_changed is null or  effective_yymm<=" . $final_yymm . " )
                        and ";
                        if ($phase == 0) {
                            $query .= " coalesce(ds_phase, 0) in(select ds_phase from master_mgmt.phase_month_master where start_yymm<=" . $final_yymm . ") ";
                        } else {
                            $query .= " ds_phase in(select ds_phase from master_mgmt.phase_month_master where start_yymm<=" . $final_yymm . ") ";
                        }

                    $query .= "  then 1 else 0 end
                    ) as " . $statusColumn . "_status,";
                }
                $query = substr($query, 0, -1);
                $query .= " from
                bandhan.ben_payment_details
                        where
                            acc_validated in('2', '6')
                            and ben_status = 1
                            " . $phase_condition . "
                        group by
                            ds_phase,
                            caste
                    ) t
                group by
                    ds_phase, caste, apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status ";
                $query .= " UNION ALL ";

                // Validation Pending Beneficiary
                $valQuery .= "select ds_phase,
                caste,
                SUM(apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status) as validation_pending,
                apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status,
                (
                    case
                        when caste in('SC', 'ST') then (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1200
                        else (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1000
                    end
                ) as validation_pending_amount
                from
                (
                    select
                        ds_phase,
                        caste, ";
                foreach ($monthArr as $key => $val) {
                    $statusColumn = $monthConstArr[$val];
                    $yearArr = explode('-', $current_financial_year);
                    if ($val >= 4 and  $val <= 12) {
                        $final_yymm = substr($yearArr[0], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    } else {
                        $final_yymm = substr($yearArr[1], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    }
                    $valQuery .= " sum(case
                        when  " . $statusColumn . "_lot_status in('R','E','F' )  and ben_status = 1 and end_yymm>=" . $final_yymm . "
                        and ";
                    if ($phase == 0) {
                        $valQuery .= " coalesce(ds_phase, 0) in(select ds_phase from master_mgmt.phase_month_master where start_yymm<=" . $final_yymm . ") ";
                    } else {
                        $valQuery .= " ds_phase in(select ds_phase from master_mgmt.phase_month_master where start_yymm<=" . $final_yymm . ") ";
                    }

                    $valQuery .= " then 1 else 0 end
                    ) as " . $statusColumn . "_status,";
                }
                $valQuery = substr($valQuery, 0, -1);
                $valQuery .= " from
                bandhan.ben_payment_details
                        where
                            acc_validated not in('2', '6')
                            and ben_status = 1
                            " . $phase_condition . "
                        group by
                            ds_phase,
                            caste
                    ) t
                group by
                    ds_phase, caste, apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status ";
                $valQuery .= " UNION ALL ";


            }
            $query = substr($query, 0, -11);
            $valQuery = substr($valQuery, 0, -11);

            // dump($query);
            // dump($valQuery);die;
            // print_r($totalPayMonthArr); die;
            $data = DB::connection('pgsql_payment')->select($query);

            // print $valQuery; die;
            $valData = DB::connection('pgsql_payment')->select($valQuery);

            // Approval Pending
            $approvalQuery = '';
            $approvalQuery = "select ds_phase, LEFT(caste::varchar,2)::character varying(2) as caste, sum(partial_data+full_data+verification_pending+verified+reverted) as total,
            case when caste='OTHERS' then 1000 when caste='SC' then 1200 when caste='ST' then 1200 else 0 end as phase_amount
            from (
                select ds_phase,caste,
                count(1) filter(where is_final=FALSE) as partial_data,
                count(1) filter(where is_final=TRUE) as full_data,
                count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
                count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999)
                as verified,
                count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted
                from lb_scheme.draft_ben_personal_details ";
            if (!is_null($request_phase)) {
                // $approvalQuery .= "where ds_phase = " . $request_phase . " ";
                if ($request_phase == 0) {
                    $approvalQuery .= "where ds_phase is null ";
                } else {
                    $approvalQuery .= "where ds_phase = " . $request_phase . " ";
                }
            }
            $approvalQuery .= "group by  ds_phase,caste
            ) t group by ds_phase,caste";

            $approvalPendingData = DB::connection('pgsql_appread')->select($approvalQuery);

            // print_r($approvalPendingData);

            // Get Approval Pending Data
            $casteArr = array('OT','SC','ST');
            $finalapprovalPendingData = array();
            foreach ($allPhase as $key) {
                $app_phase = $key->ds_phase;
                foreach ($casteArr as $caste) {
                    $is_matched = 0;
                    foreach ($approvalPendingData as $ap) {
                        if ($caste == $ap->caste && $app_phase == $ap->ds_phase) {
                            $finalapprovalPendingData[] = ['ds_phase' => $app_phase, 'caste' => $caste, 'total' => $ap->total, 'phase_amount' => $ap->phase_amount];
                            $is_matched = 1;
                        }
                    }
                    if ($is_matched == 0) {
                        $finalapprovalPendingData[] = ['ds_phase' => $app_phase, 'caste' => $caste, 'total' => 0, 'phase_amount' => 0];
                    }
                }
            }
            // print_r($finalapprovalPendingData);die;
            $amount_master = DB::connection('pgsql_payment')->table('master_mgmt.amount_master')->get(['caste','amount']);

            return response()->json(
                [
                    'row_data' => $data,
                    'val_data' => $valData,
                    'payble_month' => $totalPayMonthArr,
                    'approval_pending' => $finalapprovalPendingData,
                    'amount_master_data' => $amount_master
                ]
            );
        }
    }

    // Monthly Disbursed Amount Based on the Lot Pushed date to the Bank
    public function monthlyDisbursementIndex()
    {
        return view('lot_report/monthly_disbursement_report');
    }

    public function monthlyDisbursement(Request $request)
    {
        if ($request->ajax()) {
            $lot_year=$request->lot_year;
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $getModelFunc = new getModelFunc();
             $schemaname = $getModelFunc->getSchemaDetails($lot_year);
             if($schemaname=='trx_mgmt_2122'){
                $schemaname='trx_mgmt_2122_archive';
            }else if($schemaname=='trx_mgmt_2223'){
                $schemaname='trx_mgmt_2223';
            }
            else{
                $schemaname = $getModelFunc->getSchemaDetails($lot_year);
                // $schemaname='trx_mgmt_cur_fy';
            }
            $query = "SELECT TO_CHAR(t.pushed_at, 'DD-MM-YYYY') as pushed_date,
            TRIM(TO_CHAR(TO_DATE(TO_CHAR(t.pushed_at, 'MM')::text, 'MM'), 'Month')) || '-' || LEFT(t.pushed_at::varchar,4) as month_year,
                SUM(ben_count) AS ben_count,
                SUM(debit_amount) AS debit_amount,
                SUM(success_count) AS success_count,
                SUM(amount_debit) AS success_amount,
                SUM(failed_count) AS failed_count,
                SUM(CASE WHEN lot_category='OT' THEN failed_count*500
                     WHEN lot_category='ST' THEN failed_count*1000
                     WHEN lot_category='SC' THEN failed_count*1000 END) as failed_amount,
                (SUM(ben_count)-(SUM(success_count)+SUM(failed_count))) as pending_ben_count
            FROM (
                SELECT pushed_at::date,lot_category,
                SUM(ben_count) AS ben_count,
                SUM(debit_amount) AS debit_amount,
                SUM(success_count) AS success_count,
                SUM(amount_debit) AS amount_debit,
                SUM(failed_count) AS failed_count,
                COUNT(1) AS total_lot
                FROM $schemaname.lot_master WHERE lot_status<>'9'";
                if(!empty($from_date)){
                    $query .= " and pushed_at::date >='".date('Y-m-d', strtotime(trim(str_replace('/', '-', $from_date))))."'::date";
                }
                if(!empty($to_date)){
                    $query .= " and pushed_at::date <='".date('Y-m-d', strtotime(trim(str_replace('/', '-', $to_date))))."'::date";
                }
                $query .= " GROUP BY pushed_at::date,lot_category ORDER BY pushed_at::date DESC
            ) t GROUP BY pushed_at ORDER BY pushed_at::date DESC";
            // echo $query;die();
            $data = DB::connection('pgsql_payment')->select($query);
            // print_r($data);die();
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('pushed_date', function ($data) {
                    return $data->pushed_date;
                })
                ->addColumn('month_year', function ($data) {
                    return $data->month_year;
                })
                ->addColumn('ben_count', function ($data) {
                    return $data->ben_count;
                })
                ->addColumn('debit_amount', function ($data) {
                    return $data->debit_amount;
                })
                ->addColumn('success_count', function ($data) {
                    return $data->success_count;
                })
                ->addColumn('success_amount', function ($data) {
                    return $data->success_amount;
                })
                ->addColumn('failed_count', function ($data) {
                    return $data->failed_count;
                })
                ->addColumn('failed_amount', function ($data) {
                    return $data->failed_amount;
                })
                ->addColumn('pending_ben_count', function ($data) {
                    return $data->pending_ben_count;
                })
                ->rawColumns([ 'pushed_date', 'month_year', 'ben_count','debit_amount','success_count','success_amount','failed_count','failed_amount','pending_ben_count'])
                ->make(true);
        }
    }

    // Previous Year financial assistance payble
    public function selectFinancialYearForPaymentAssistance(){
        $designation = Auth::user()->designation_id;
        if ($designation == 'DDO' || $designation == 'HOD' || $designation == 'MisState') {
            $result = DB::connection('pgsql_payment')->select("select financial_year,case when is_current_year=1 then 'financial-assistance-payable' else 'previous-financial-assistance-payable' end as url from master_mgmt.m_financial_master where is_lot_generation_active=1");
            return view('lot_report/index_financial_assistance_payble')->with('fin_year',$result);
        }
        else {
            return redirect("/")->with('error', 'User Unauthorized.');
        }
    }

    public function previousFinancialAssistancePayable(Request $request) {
        $phase_master = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')->where('pl_status', 1)->get();
        $fin_year_master = DB::connection('pgsql_payment')->table('master_mgmt.m_financial_master')->where('is_lot_generation_active', 1)->where('is_current_year', 0)->get();
        return view('lot_report/previous_financial_assistance_payble')->with('fin_year', $fin_year_master)->with('ds_phase', $phase_master);
    }

    public function getPreviousFinancialAssistancePayable(Request $request) {
        if ($request->ajax()) {
            $request_phase = $request->ds_phase;
            $scheme_name = $request->db_scheme_name;
            $pre_fin_year = DB::connection('pgsql_payment')->table('master_mgmt.m_financial_master')->where('db_schema_name', $scheme_name)->value('financial_year');
            // Payment Lot Generation Pending
            $monthConstArr = array(1=>'jan', 2=>'feb', 3=>'mar', 4=>'apr', 5=>'may',6=>'jun', 7=>'jul', 8=>'aug', 9=>'sep', 10=>'oct', 11=>'nov', 12=>'dec');
            $monthArr = array(0=>4, 1=>5, 2=>6, 3=>7, 4=>8, 5=>9, 6=>10, 7=>11, 8=>12, 9=>1, 10=>2, 11=>3);
            if (!is_null($request_phase)) {
                $allPhase = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')->where('ds_phase', $request_phase)->get();
            } else {
                $allPhase = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')->get();
            }
            $query = '';
            $valQuery ="";
            $totalPayMonthArr=array();
            foreach ($allPhase as $key) {
                $phase = $key->ds_phase;
                // Get Current Financial Year
                // if (date('m') <= 3) {
                //     $current_financial_year = (date('Y')-1) . '-' . date('Y');
                // } else {
                //     $current_financial_year = date('Y') . '-' . (date('Y') + 1);
                // }
                $current_financial_year=$pre_fin_year;

                $phaseTableFinYear = $key->starting_year;
			    $phaseTableMonth =$key->starting_month;
                $phaseTableMonthIndex = array_search($phaseTableMonth, $monthArr);
			    $currentMonthIndex = array_search(date('n'), $monthArr);
                $total_pay_month = 0;
                if ($phaseTableFinYear == $current_financial_year) {
					if ($phaseTableMonthIndex <= $currentMonthIndex) {
						$total_pay_month = ($currentMonthIndex-$phaseTableMonthIndex)+1;
					}
				}
				else {
					$total_pay_month = $currentMonthIndex+1;
				}
                $totalPayMonthArr[] = ['ds_phase' => $phase, 'pay_month' => $total_pay_month];

                $query .= "select ds_phase,
                caste,
                SUM(apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status) as total_beneficiary_payment,
                apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status,
                (
                    case
                        when caste in('SC', 'ST') then (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1000
                        else (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 500
                    end
                ) as total_amount
                from
                (
                    select
                        ds_phase,
                        caste, ";
                foreach ($monthArr as $key => $val) {
                    $statusColumn = $monthConstArr[$val];
                    $yearArr = explode('-', $current_financial_year);
                    if ($val >= 4 and  $val <= 12) {
                        $final_yymm = substr($yearArr[0], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    } else {
                        $final_yymm = substr($yearArr[1], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    }
                    $query .= " sum(case
                        when  " . $statusColumn . "_lot_status in('R','E' )  and new_ben_status = 1 and end_yymm>=" . $final_yymm . "
                        and ds_phase in(select ds_phase from master_mgmt.phase_month_master where start_yymm<=" . $final_yymm . ") then 1 else 0 end
                    ) as " . $statusColumn . "_status,";
                }
                $query = substr($query, 0, -1);
                $query .= " from
                " . $scheme_name . ".ben_payment_details
                        where
                            new_acc_validated in('2', '6')
                            and new_ben_status = 1
                            and ds_phase = " . $phase . "
                        group by
                            ds_phase,
                            caste
                    ) t
                group by
                    ds_phase, caste, apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status ";
                $query .= " UNION ALL ";
            }
            $query = substr($query, 0, -11);
            // print_r($query); die;

            $data = DB::connection('pgsql_payment')->select($query);
            // print_r($data);die;
            return response()->json(
                [
                    'row_data' => $data,
                    'payble_month' => $totalPayMonthArr
                ]
            );
        }
    }

    function legacyValidationReport(Request $request) {
        $districts = District::select(['district_code', 'district_name'])->get();
        return view('lot_report/legacy_validation_report', ['districts' => $districts]);
    }

    function legacyValidationLotReport(Request $request) {
        // echo 1;die;
        if ($request->ajax()) {

            $dist_code = $request->district_code;
            $phase_code = $request->phase_code;
            $getModelFunc = new getModelFunc();
             $schemaname = $getModelFunc->getSchemaDetails();
            //$schemaname = 'trx_mgmt_2122_archive';
            $query = "Select district_name	   				District,
            coalesce(sum(total_beneficiary),0) 				as total_beneficiary,
            coalesce(sum(sc_beneficiary),0) 				as sc_beneficiary,
            coalesce(sum(st_beneficiary),0) 				as st_beneficiary,
            coalesce(sum(ot_beneficiary),0) 				as ot_beneficiary,
            coalesce(sum(validation_initiated),0)			as validation_initiated,
            coalesce(sum(validation_not_inititated),0)		as validation_not_inititated,
            coalesce(sum(validation_complete),0)			as validation_complete,
            coalesce(sum(validation_success),0)				as validation_success,
            coalesce(sum(validation_failed),0) 				as validation_failed,
            coalesce(sum(ot_success), 0) as ot_success,
            coalesce(sum(sc_success), 0) as sc_success,
            coalesce(sum(st_success), 0) as st_success,
            sum(total_beneficiary-(validation_success+validation_failed)) as validation_pending,
        dist_code as dist_code
        from public.m_district d left join
        (
            select dist_code,
            sum(case when name_validated is not null then 1 else 0 end ) 	as total_beneficiary,
            sum(case when name_validated is not null and caste='SC' then 1 else 0 end )				as sc_beneficiary,
            sum(case when name_validated is not null and caste='ST' then 1 else 0 end )				as st_beneficiary,
            sum(case when name_validated is not null and caste='OT' then 1 else 0 end )				as ot_beneficiary,
            sum(case when name_validated!='0' then 1 else 0 end ) 	as validation_initiated,
            sum(case when (name_validated='0') then 1 else 0 end )	as validation_not_inititated,
            sum(case when name_validated in('2','3') then 1 else 0 end ) as validation_complete,
            sum(case when name_validated='2' then 1 else 0 end)          as validation_success,
            sum(case when name_validated='3' then 1 else 0 end)      as validation_failed,
            sum(
                case
                    when name_validated='2' and caste='OT' then 1
                    else 0
                end
            ) as ot_success,
            sum(
                case
                    when name_validated='2' and caste='SC' then 1
                    else 0
                end
            ) as sc_success,
            sum(
                case
                    when name_validated='2' and caste='ST' then 1
                    else 0
                end
            ) as st_success


            FROM ".$schemaname.".ben_payment_details ";
            if (!empty($phase_code)) {
                $query .= "where ds_phase =". $phase_code;
            }
            $query .= " group by dist_code
        )b  on d.district_code = b.dist_code";
        if (!empty($dist_code)) {
            $query .= " WHERE b.dist_code =" . $dist_code;
        }
            $query .= " group by district_name,dist_code order by district_name,dist_code";


            // echo $query;die;
            $data = DB::connection('pgsql_payment')->select($query);
            // dd($data);

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('district', function ($data) {

                    return $data->district;
                })
                ->addColumn('total_beneficiary', function ($data) {

                    return $data->total_beneficiary;
                })
                ->addColumn('sc_beneficiary', function ($data) {

                    return $data->sc_beneficiary;
                })
                ->addColumn('st_beneficiary', function ($data) {

                    return $data->st_beneficiary;
                })
                ->addColumn('ot_beneficiary', function ($data) {

                    return $data->ot_beneficiary;
                })
                ->addColumn('validation_initiated', function ($data) {

                    return $data->validation_initiated;
                })
                ->addColumn('validation_complete', function ($data) {

                    return $data->validation_complete;
                })
                ->addColumn('validation_not_inititated', function ($data) {
                    return $data->validation_not_inititated;
                })
                ->addColumn('validation_complete', function ($data) {
                    return $data->validation_complete;
                })
                ->addColumn('validation_success', function ($data) {
                    return $data->validation_success;
                })
                ->addColumn('validation_failed', function ($data) {
                    return $data->validation_failed;
                })
                ->addColumn('validation_failed', function ($data) {
                    return $data->validation_failed;
                })
                ->addColumn('ot_success', function ($data) {
                    return $data->ot_success;
                })
                ->addColumn('sc_success', function ($data) {
                    return $data->sc_success;
                })
                ->addColumn('st_success', function ($data) {
                    return $data->st_success;
                })
                ->addColumn('naming_failed', function ($data) use($phase_code){ //echo $data->dist_code;die;

                    $query_name_validation="SELECT COUNT(1) AS total FROM lb_main.av_lot_details ad
                    JOIN lb_main.av_lot_master am ON ad.lot_no = am.lot_no
                    WHERE ad.dist_code = ".$data->dist_code." AND ad.av_account_status='Y' AND ad.name_status = 'N' AND am.legacy_validation = true";
                    if(!empty($data->dist_code)){
                    if(!empty($phase_code)){
                    $query_name_validation .= " AND ad.av_ds_phase  =".$phase_code;
                    }
                    $executNamingfailedeQuery= DB::connection('pgsql_payment')->select($query_name_validation);
                    // dd($executNamingfailedeQuery[0]->total);
                    return $executNamingfailedeQuery[0]->total;
                }
                    else{
                    return 0;
                    }
                // echo $query_name_validation;die;

                })



                ->rawColumns(['district', 'total_beneficiary','ot_beneficiary','sc_beneficiary','st_beneficiary', 'validation_initiated', 'validation_complete', 'validation_not_inititated', 'validation_complete', 'validation_success', 'validation_failed', 'validation_pending','naming_failed'])
                ->make(true);
        } else {
            return view('lot_report/legacy_validation_report');
        }
    }
}
