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
                <form method="post" id="register_form" class="submit-once">
                    {{ csrf_field() }}

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

                                    <div class="col-md-12">


                                        <div class="form-group col-md-3">
                                            <label class="">Duare Sarkar Phase</label>
                                            <select class="form-control" name="ds_phase" id="ds_phase">
                                                <option value="">--All--</option>

                                                <option value="0">Normal Entry</option>
                                            </select>
                                            <span id="error_ds_phase" class="text-danger"></span>
                                        </div>


                                    </div>
                                    <div class="form-group col-md-3 mb-0">
                                        <button type="button" name="submit" value="Submit"
                                            class="btn btn-success table-action-btn" id="search_sws">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- DataTable Section -->
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table id="example" class="display data-table" cellspacing="0" width="100%">
                                        <thead class="table-header-spacing">
                                            <tr role="row">
                                                <th style="text-align: center">Application Id</th>
                                                <th style="text-align: center">Applicant Name</th>
                                                <th style="text-align: center">Mobile Number</th>
                                                <th style="text-align: center">Father's Name</th>
                                                <th style="text-align: center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size: 14px;">
                                            <!-- DataTables will populate this dynamically -->
                                        </tbody>
                                    </table>
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
@endpush
