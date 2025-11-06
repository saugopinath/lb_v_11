@extends('portal.base')

@section('action-content')
    <div class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Select Pension Scheme</h3>
                        </div>

                        <div class="card-body">
                            <form class="form-horizontal" role="form" method="POST" action="">
                                @csrf

                                <div class="form-group row{{ $errors->has('scheme') ? ' has-error' : '' }}">
                                    <label for="scheme" class="col-sm-4 col-form-label">Scheme Type</label>
                                    <div class="col-sm-6">
                                        <select onchange="la(this.value)" class="form-control" name="scheme" id="scheme">
                                            <option value="">--Select--</option>
                                            @foreach ($return_arr as $arr)
                                                @if($arr['active'] == 1)
                                                    <option value="jb-pension?scheme_id={{ encrypt($arr['id']) }}&type={{ 1 }}">
                                                        {{ $arr['display_name'] }}
                                                    </option>
                                                @else
                                                    <option value="#" disabled>{{ $arr['display_name'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <span id="error_construction" class="text-danger"></span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function la(src) {
            if (src && src !== '#') window.location = src;
        }
    </script>
@endsection