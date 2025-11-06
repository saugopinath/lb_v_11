<x-layouts.login>
    <x-errors class="mt-6" />
    <x-success class="mt-6" />

       <div class="row justify-content-center mt-4">
                <div class="col-md-3 col-sm-6 p-4">
                    <form method="POST" action="{{ route('loginPost') }}" class="form-horizontal">
                        {{ csrf_field() }}
                       

                        <div class="mb-3 text-center">
                            <input id="mobile_no" type="text" maxlength="10" class="form-control text-center mx-auto"
                                name="mobile_no" placeholder="Enter Registered Mobile No."  autofocus autocomplete="off">
                        </div>

                        <div class="mb-3 text-center">
                            <div class="captcha d-flex justify-content-center align-items-center gap-2">
                                <span id="captcha-container">{!! captcha_img('flat') !!}</span>
                                <a href="javascript:void(0)" onclick="refreshCaptcha()">
                                    <img  src="{{ asset('images/refresh1.png') }}" alt="Refresh Captcha" width="22" height="22">
                                </a>
                            </div>
                        </div>

                        <div class="mb-3 text-center">
                            <input id="captcha" type="text" class="form-control text-center mx-auto" name="captcha"
                                placeholder="Enter Captcha"  autocomplete="off">
                        </div>

                        <div class="mb-3 text-center">
                            <input id="login_password" type="password" class="form-control text-center mx-auto"
                                name="password" placeholder="Enter Password"  autocomplete="off">
                        </div>

                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-success">Login</button>
                        </div>

                        <div class="d-grid">
                            <a href="{{route('forget-password')}}" class="btn btn-primary">Forget Password</a>
                        </div>
                    </form>
                </div>
            </div>
</x-layouts.login>