<x-layouts.login>
    <x-errors class="mt-6" />
    <x-success class="mt-6" />

       <div class="row justify-content-center mt-4">
                <div class="col-md-3 col-sm-6 p-4">
                    <form method="POST" action="{{ route('resetPasswordPost') }}" class="form-horizontal">
                        {{ csrf_field() }}
                        <input type="hidden" name="token_id" value="{{Crypt::encrypt($user_id)}}">
                       <input type="hidden" name="source_type" value="{{Crypt::encrypt($source_type)}}">
                       <div class="mb-3 text-center">
                            <input id="login_password" type="password" class="form-control text-center mx-auto"
                                name="user_password" placeholder="Enter New Password"  autocomplete="off">
                        </div>
                        <div class="mb-3 text-center">
                            <input id="login_password" type="password" class="form-control text-center mx-auto"
                                name="confirm_user_password" placeholder="Confirm  Password"  autocomplete="off">
                        </div>

                        <div class="mb-3 text-center">
                            <div class="captcha d-flex justify-content-center align-items-center gap-2">
                                <span id="captcha-container">{!! captcha_img('flat') !!}</span>
                                <a href="javascript:void(0)" onclick="refreshCaptcha()">
                                    <img src="{{ asset('images/refresh1.png') }}" alt="Refresh Captcha" width="22" height="22">
                                </a>
                            </div>
                        </div>

                        <div class="mb-3 text-center">
                            <input id="captcha" type="text" class="form-control text-center mx-auto" name="captcha"
                                placeholder="Enter Captcha"  autocomplete="off">
                        </div>

                        

                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-success">Reset Password</button>
                        </div>

                        <div class="d-grid">
                            <a href="{{ route('login') }}" class="btn btn-primary">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
            
</x-layouts.login>