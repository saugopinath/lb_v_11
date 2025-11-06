<x-layouts.login>
    <x-errors class="mt-6" />
    <x-success class="mt-6" />

       <div class="row justify-content-center mt-4">
                <div class="col-md-3 col-sm-6 p-4">
                    <form method="POST" action="{{ route('login1Post') }}" class="form-horizontal">
                        {{ csrf_field() }}
                       

                        <div class="mb-3 text-center">
                            <input id="mobile_no" type="text" maxlength="10" class="form-control text-center mx-auto"
                                name="mobile_no" placeholder="Enter Registered Mobile No."  autofocus autocomplete="off">
                        </div>

                       

                     
                        <div class="mb-3 text-center">
                            <input id="login_password" type="text" class="form-control text-center mx-auto"
                                name="login_otp" placeholder="Enter OTP"  autocomplete="off">
                        </div>

                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-success">Login</button>
                        </div>

                     
                    </form>
                </div>
            </div>
</x-layouts.login>