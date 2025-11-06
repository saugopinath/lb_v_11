<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function refreshCaptcha()
    {
        //return response()->json(['captcha'=> captcha_img()]);
        return captcha_img('flat');
    }
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('login');
    }

   public function loginPost(Request $request)
    {
        try{
   
            $rules = array();
            $messages = array();
            $attributes = array();
            $rules = [
                'mobile_no' => [
                    'required', 
                    'regex:/[0-9]{10}/', 
                    'digits:10'
                ],
                'login_password' => ['required'],
                'captcha' => 'required|captcha'
            ];     
            $attributes = [
                'mobile_no' => 'Mobile Number',
                'login_password' => 'Password',
                'captcha' => 'Captcha'
            ];
            $messages = [
                'captcha.captcha' => 'Invalid captcha code.'
            ];
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if (!$validator->passes()) {
                $error_msg = array();
                foreach ($validator->errors()->all() as $error) {
                    array_push($error_msg, $error);
                }
                 //dd($error_msg);
                return redirect()->back()->with('errors', $error_msg);
            }

     
       

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        // Get user record
        $current_timestamp = Carbon::now()->setTimezone('Asia/Kolkata')->format('Y/m/d H:i:s');
        $num = User::where('mobile_no', $request->get('mobile_no'))->where('is_active', 1)->count();
        if ($num > 0) {
            $user = User::where('mobile_no', $request->get('mobile_no'))->where('is_active', 1)->first();

           
           

            if ($request->get('mobile_no') == $user->mobile_no) {
               //dd(bcrypt($request->login_password));
                //dd('ok');
                if(is_null($user->password) || $user->password==''){
                    return redirect('forget-password-initial')->with('errors', array('Your password yet to set ..please set the passsword'));
                }
                $current_timestamp = Carbon::now()->setTimezone('Asia/Kolkata')->format('Y/m/d H:i:s');
                //dd(Hash::check($request->login_password, $user->password));
                if ( Hash::check($request->login_password, $user->password) && (strtotime($current_timestamp) < strtotime($user->password_expires_at)) ){
                   // dd('ok1');
                    $otp = rand(111111,999999);
                    $message = 'Your OTP for Jai Bangla is '.$otp.'.   
Government of West Bengal.';
                    $mobile_no=$request->get('mobile_no');
                    //$mobile_no='8583035693';
                    $send_otp=$this->initiateSmsActivation($mobile_no, $message);
                    //$send_otp=1;
                    if($send_otp){
                       
                        DB::beginTransaction();
                        $cur_time=Carbon::now()->setTimezone('Asia/Kolkata')->format('Y/m/d H:i:s');
                        $expire_time=Carbon::now()->setTimezone('Asia/Kolkata')->addMinutes($this->otp_expire_time)->format('Y/m/d H:i:s');
                        $otp_hash=md5($otp);
                        $log_insert=DB::table('public.user_otp')->insert([
                            'otp_hash' =>  $otp_hash, 
                            'mobile' => $mobile_no, 
                            'created_at' => $cur_time
                        ]);
                        $update_user = User::where('id', $user->id)->where('mobile_no', $user->mobile_no)
                        ->update([
                            'last_otp' =>  $otp_hash, 
                            'flag_sent_otp' => 1, 
                            'last_otp_generation_time' => $cur_time, 
                            'last_otp_expire_time' =>  $expire_time
                        ]);
                        if ($update_user && $log_insert) {
                            //dd('ok');
                            DB::commit();
                            return redirect('check-otp?source_type=2&token_id='.Crypt::encrypt($user->id))->with('msg', 'OTP has been Send to your Register mobile Number');
                        }
                        else{
                            DB::rollback();
                            return back()->with('msg', 'Error .Please try again');
                        }
                        
                       


                    }
                       
                    
                } else if (Hash::check($request->login_password, $user->password) && (strtotime($current_timestamp) > strtotime($user->password_expires_at))) {
                    //dd('ok2');
                    // Session::put('msg', 'Your OTP has expired. Please re-generate OTP to Login');
                    return redirect('forget-password-initial')->with('errors', array('Your password has expired ..please set the new passsword'));
                } else {
                    //dd('ok3');
                    return back()->with('errors', array('Please Provide the correct Password'));
                }
            }
        } else {
            //dd('ok4');
            return back()->with('errors', array('Your mobile number not match in our system..!!'));
        }
    }
    catch (\Exception $e) {   
        //dd($e);
 
        return redirect('login')->with('errors', array('Something went wrong. Please try again.'));
            
        }
    }
    protected function hasTooManyLoginAttempts($request)
    {
        $maxLoginAttempts = 2;
        $lockoutTime = 5; // 5 minutes
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request),
            $maxLoginAttempts,
            $lockoutTime
        );
    }
   
}
