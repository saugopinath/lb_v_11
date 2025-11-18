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
                {{-- <form method="post" id="register_form" class="submit-once"> --}}
                {{-- {{ csrf_field() }} --}}


                <div class="tab-content" style="margin-top:16px;">
                    <div class="tab-pane active" id="personal_details">
                        <!-- Card with your design -->
                        <div class="card" id="res_div">
                            <div class="card-header card-header-custom">
                                <h4 class="card-title mb-0"><b> Applications List</b></h4>
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

                                <form name="casteManagement" id="casteManagement" method="post"
                                    action="{{ url('aadhaar-details-update-hod') }}" onsubmit="return validate();">
                                    {{ csrf_field() }}
                                    <!-- Search Section -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-row align-items-end">

                                                <div class="form-group col-md-3">
                                                    <label for="select_type">Search Using <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" name="select_type" id="select_type">
                                                        @foreach (Config::get('globalconstants.search_payment_status') as $key => $search_type)
                                                            @if ($key == 'S')
                                                                @continue
                                                            @endif
                                                            <option value="{{ $key }}"
                                                                @if ($key == $fill_array['select_type']) selected @endif>
                                                                {{ $search_type }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span style="font-size: 14px;" id="error_select_type"
                                                        class="text-danger"></span>
                                                </div>

                                                <div class="form-group col-md-3" id="beneficiary_id_div">
                                                    <label for="beneficiary"><span id="search_text">
                                                            {{ $fill_array['search_text'] }}</span> <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="ben_id" id="ben_id" class="form-control"
                                                        onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;"
                                                        placeholder="Enter Beneficiary ID"
                                                        value="{{ $fill_array['ben_id'] }}" autocomplete="off">
                                                    <span style="font-size: 14px;" id="error_ben_id"
                                                        class="text-danger"></span>
                                                </div>

                                                <div class="form-group col-md-3">
                                                    {{-- <button type="submit" name="submit" value="Submit"
                                                        class="btn btn-success table-action-btn" id="search_sws">
                                                        <i class="fas fa-search"></i> Search
                                                    </button> --}}
                                                    <input class="btn btn-success table-action-btn" type="submit"
                                                        name="btnSubmit" value="Search">
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </form>

                                @if (!empty($errorMsg))
                                    <div class="alert alert-danger alert-block">
                                        <strong> {{ $errorMsg }}</strong></li>

                                    </div>
                                @endif
                                @if (count($result) > 0)
                                    <!-- DataTable Section -->
                                    <div class="table-container">
                                        <div class="table-responsive">
                                            <table id="example" class="display data-table" cellspacing="0" width="100%">
                                                <thead class="table-header-spacing">
                                                    <tr role="row">
                                                        <th style="text-align: center">Application Id</th>
                                                        <th style="text-align: center">Beneficiary ID</th>
                                                        <th style="text-align: center">Beneficiary Name</th>
                                                        <th style="text-align: center">Mobile No.</th>
                                                        <th style="text-align: center">Aadhar No.</th>
                                                        <th style="text-align: center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 14px;">
                                                    <!-- DataTables will populate this dynamically -->
                                                    @foreach ($result as $row)
                                                        <tr>
                                                            <td>{{ $row->application_id }}</td>
                                                            <td>{{ $row->beneficiary_id }}</td>
                                                            <td>{{ $row->ben_name }}</td>
                                                            <td>{{ $row->mobile_no }}</td>
                                                            @php
                                                                $aadhar_no = '';
                                                                if (!empty($row->encoded_aadhar)) {
                                                                    try {
                                                                        $aadhar_no = Crypt::decryptString(
                                                                            $row->encoded_aadhar,
                                                                        );
                                                                    } catch (\Exception $e) {
                                                                        $aadhar_no = '';
                                                                    }
                                                                }
                                                            @endphp

                                                            <td>{{ $aadhar_no }}</td>
                                                            <td>
                                                                @if ($row->status == 10)
                                                                    <span>Already Marked</span>
                                                                @elseif($row->status == 11)
                                                                    <span>Already Marked and Edited</span>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-info confirmBtn table-action-btn"
                                                                        id="mark_change"
                                                                        value="{{ $row->application_id }}_{{ $row->is_faulty }}">Mark</button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                {{-- </form> --}}
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="modalmark" class="modal fade">
        <form method="post" id="commonfield" action="{{ url('mark4edithodpost') }}" class="submit-once">
            {{ csrf_field() }}

            <input type="hidden" name="application_id" id="application_id" value="" />


            <div class="modal-dialog modal-confirm">
                <div class="modal-content">
                    <div class="modal-header flex-column">

                        <h4 class="modal-title w-100" id="lable_text"></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body">


                        <h4>Are you Sure.. You want to Mark <span id="op_text"><?php echo $fill_array['search_text']; ?>: <?php echo $fill_array['ben_id']; ?>
                            </span>?</h4>




                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info modal-submitapprove">OK</button>
                        <button type="button" id="submittingapprove" value="Submit"
                            class="btn btn-success success btn-lg" disabled>Submitting please wait</button>
                    </div>
                </div>
        </form>
    </div>

@endsection

@push('scripts')
    {{-- <script src="{{ URL::asset('js/master-data-v2.js') }}"></script> --}}

    <script>
        $(document).ready(function() {
            $('#loadingDiv').hide();
            $("#submittingapprove").hide();
            $('.sidebar-menu li').removeClass('active');
            //$('.sidebar-menu #lb-caste').addClass("active"); 
            $('.sidebar-menu #updateAadhaarDetails').addClass("active");
            // $('.sidebar-menu #accValTrFailed').addClass("active");
            //$('#submit_btn').removeAttr('disabled');
            $('#select_type').change(function() {
                var select_type = $('#select_type').val();
                if (select_type == 'B') {
                    $('#search_text').text('Beneficiary ID');
                    $("#ben_id").attr("placeholder", 'Beneficiary ID');
                } else if (select_type == 'A') {
                    $('#search_text').text('Application ID');
                    $("#ben_id").attr("placeholder", 'Application ID');
                } else if (select_type == 'S') {
                    $('#search_text').text('Sasthyasathi Card No.');
                    $("#ben_id").attr("placeholder", 'Sasthyasathi Card No.');
                } else {
                    $('#select_type').val('A');
                    $('#search_text').text('Beneficiary ID');
                    $("#ben_id").attr("placeholder", 'Beneficiary ID');
                }
            });
            $('#mark_change').click(function() {
                var clickval = $(this).val();
                $("#application_id").val(clickval);
                $('#modalmark').modal('show');
            });
        });

        function validate() {
            var error_select_type = '';
            var error_ben_id = '';
            if ($.trim($('#select_type').val()).length == 0) {
                error_select_type = 'Please Select';
                $('#error_select_type').text(error_select_type);
                $('#select_type').addClass('has-error');
            } else {
                error_select_type = '';
                $('#error_select_type').text(error_select_type);
                $('#select_type').removeClass('has-error');
            }

            if ($.trim($('#ben_id').val()).length == 0) {
                error_ben_id = 'This field is required';
                $('#error_ben_id').text(error_ben_id);
                $('#ben_id').addClass('has-error');
            } else {
                error_ben_id = '';
                $('#error_ben_id').text(error_ben_id);
                $('#ben_id').removeClass('has-error');
            }
            if (error_select_type == '' && error_ben_id == '') {
                return true;
            } else {
                return false;
            }
        }
    </script>
@endpush
