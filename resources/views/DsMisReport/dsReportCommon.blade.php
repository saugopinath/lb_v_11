<style>
    .card-header-custom {
        font-size: 16px;
        background: linear-gradient(to right, #c9d6ff, #e2e2e2);
        font-weight: bold;
        font-style: italic;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
</style>
@extends('layouts.app-template-datatable')
@section('content')
    <!-- Main content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mt-4">
                <form method="post" id="register_form" action="{{ url('dsMisReport') }}" class="submit-once">


                    <div class="tab-content" style="margin-top:16px;">
                        <div class="tab-pane active" id="personal_details">
                            <!-- Card with your design -->
                            <div class="card" id="res_div">
                                <div class="card-header card-header-custom">
                                    <h4 class="card-title mb-0"><b>Applications List</b></h4>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <!-- Alert Messages -->
                                    <div class="alert-section">
                                        @if (($message = Session::get('success')) && ($id = Session::get('id')))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>{{ $message }} with Application ID:
                                                    {{ $id }}</strong>
                                                <button type="button" class="close" data-dismiss="alert">×</button>
                                            </div>
                                        @endif

                                        @if ($message = Session::get('error'))
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>{{ $message }}</strong>
                                                <button type="button" class="close" data-dismiss="alert">×</button>
                                            </div>
                                        @endif

                                        @if (count($errors) > 0)
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <ul>
                                                    @foreach ($errors as $error)
                                                        <li><strong> {{ $error }}</strong></li>
                                                    @endforeach
                                                </ul>
                                                <button type="button" class="close" data-dismiss="alert">×</button>
                                            </div>
                                        @endif

                                        <div class="alert print-error-msg" style="display:none;" id="errorDivMain">
                                            <button type="button" class="close" aria-label="Close"
                                                onclick="closeError('errorDivMain')">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <ul></ul>
                                        </div>
                                    </div>

                                    <!-- Search Section -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-row align-items-end">

                                                {{ csrf_field() }}

                                                <div class="form-group col-md-4">
                                                    <label class="required-field">Select Phase</label>
                                                    <select name="phase_code" id="phase_code" class="form-control">
                                                        @foreach ($phase_list as $phase)
                                                            <option value="{{ $phase->phase_code }}"
                                                                @if ($phase->is_current) selected @endif>
                                                                {{ $phase->phase_des }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <span id="error_phase_code" class="text-danger"></span>
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <input class="btn btn-success btn-lg btn-action" type="submit"
                                                        value="Submit">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #lk-main').addClass("active");
            $('.sidebar-menu #mis-report').addClass("active");

            $('.modal-search').on('click', function() {
                var error_phase_code = '';
                if ($.trim($('#phase_code').val()).length == 0) {
                    error_phase_code = 'Phase is required';
                    $('#error_phase_code').text(error_phase_code);
                    $('#phase_code').addClass('has-error');
                } else {
                    error_phase_code = '';
                    $('#error_phase_code').text(error_phase_code);
                    $('#phase_code').removeClass('has-error');
                }

                if (error_phase_code == '') {
                    loadDataTable();
                } else {
                    return false;
                }


            });
        });

        function printMsg(msg, msgtype, divid) {
            $("#" + divid).find("ul").html('');
            $("#" + divid).css('display', 'block');
            if (msgtype == '0') {
                //alert('error');
                $("#" + divid).removeClass('alert-success');
                //$('.print-error-msg').removeClass('alert-warning');
                $("#" + divid).addClass('alert-warning');
            } else {
                $("#" + divid).removeClass('alert-warning');
                $("#" + divid).addClass('alert-success');
            }
            if (Array.isArray(msg)) {
                $.each(msg, function(key, value) {
                    $("#" + divid).find("ul").append('<li>' + value + '</li>');
                });
            } else {
                $("#" + divid).find("ul").append('<li>' + msg + '</li>');
            }
        }

        function closeError(divId) {
            $('#' + divId).hide();
        }
    </script>
@endpush
