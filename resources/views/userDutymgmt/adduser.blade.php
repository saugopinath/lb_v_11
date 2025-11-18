    <style>
        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }

        .imageSize {
            font-size: 9px;
            color: #333;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-1px);
        }

        .form-control,
        .form-select {
            border-radius: 6px;
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f1b0b7);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8, #6a42a8);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .card-body {
                padding: 20px;
            }

            .btn-primary {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* Animation for form elements */
        .form-group {
            transition: all 0.3s ease;
        }

        .hidden-field {
            display: none;
        }
    </style>
    @extends('layouts.app-template-datatable')
    @section('content')

        <div class="container-fluid">
            <div class="row">
                <div class="col-12 mt-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center" style="color: #fff;">
                            <span><i class="fas fa-user-plus me-2"></i>Add New User and Assign Role</span>
                            <a href="{{ route('userDutymanagement') }}" class="btn btn-back btn-sm" style="color: #fff;">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Success Message -->
                            @if ($message = Session::get('success'))
                                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>{{ $message }}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Error Message -->
                            @if ($error = Session::get('error'))
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>{{ $error }}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Validation Errors -->
                            @if (count($errors) > 0)
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"
                                    id="crud_msg_Crud1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            <strong>Please fix the following errors:</strong>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                    <ul class="mb-0 mt-2 ps-3">
                                        @foreach ($errors as $error)
                                            <li><strong>{{ $error }}</strong></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form class="form-horizontal" role="form" method="POST" action="{{ route('adduserpost') }}"
                                id="userForm">
                                {{ csrf_field() }}

                                <!-- Personal Information Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Personal Information
                                        </h6>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="full_name" class="form-label required-field">Full Name</label>
                                        <input id="full_name" type="text" class="form-control" name="full_name"
                                            value="{{ old('full_name') }}" autocomplete="off" maxlength="200"
                                            placeholder="Enter full name">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="full_name_as_in_aadhar" class="form-label required-field">Full Name as
                                            in
                                            Aadhaar</label>
                                        <input id="full_name_as_in_aadhar" type="text" class="form-control"
                                            name="full_name_as_in_aadhar" value="{{ old('full_name_as_in_aadhar') }}"
                                            autocomplete="off" maxlength="200" placeholder="Enter name as in Aadhaar">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label required-field">Display Name</label>
                                        <input id="username" type="text" class="form-control" name="username"
                                            value="{{ old('username') }}" autocomplete="off"
                                            placeholder="Enter display name">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label required-field">Email</label>
                                        <input id="email" type="email" class="form-control" name="email"
                                            value="{{ old('email') }}" autocomplete="off"
                                            placeholder="Enter email address">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="mobile" class="form-label required-field">Mobile No.</label>
                                        <input id="mobile" type="text" class="form-control NumOnly" name="mobile"
                                            value="{{ old('mobile') }}" maxlength="10" autocomplete="off"
                                            placeholder="Enter 10-digit mobile number">
                                    </div>
                                </div>

                                <!-- Role and Location Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3"><i class="fas fa-user-tag me-2"></i>Role & Location
                                        </h6>
                                    </div>

                                    <!-- Role Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Role</label>
                                        <select class="form-select" name="designation_id" id="designation_id">
                                            @if ($role_visible)
                                                <option value="">--Select Role--</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}"
                                                        @if ($selected_role == $role->name) selected @endif>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}"
                                                        @if ($selected_role == $role->name) selected @endif>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <!-- District Selection -->
                                    @if ($district_visible)
                                        <div class="col-md-6 mb-3" id="district_div">
                                            <label class="form-label required-field">District</label>
                                            <select name="dist_code" id="dist_code" class="form-select">
                                                <option value="">--Select District--</option>
                                                @foreach ($districts as $district)
                                                    <option value="{{ $district->district_code }}">
                                                        {{ $district->district_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="dist_code" id="dist_code"
                                            value="{{ $district_code }}" />
                                    @endif

                                    <!-- Rural/Urban Selection -->
                                    @if ($is_urban_visible)
                                        <div class="col-md-6 mb-3" id="is_urban_div">
                                            <label class="form-label required-field">Rural/Urban</label>
                                            <select name="is_urban" id="is_urban" class="form-select">
                                                <option value="">--Select Type--</option>
                                                @foreach ($levels as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="is_urban" id="is_urban"
                                            value="{{ $is_urban }}" />
                                    @endif

                                    <!-- Block/Sub Div Selection -->
                                    @if ($block_visible)
                                        <div class="col-md-6 mb-3" id="block_code_div">
                                            <label class="form-label required-field" id="blk_sub_label">Block/Sub
                                                Div</label>
                                            <select name="block_code" id="block_code" class="form-select">
                                                <option value="">--Select--</option>
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="block_code" id="block_code"
                                            value="{{ $block_code }}" />
                                    @endif
                                </div>

                                <!-- Submit Button -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                            <button type="submit" class="btn btn-success btn-md">
                                                <i class="fas fa-user-plus me-2"></i>Create
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        {{-- <!-- jQuery 3.7.1 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script> --}}

        <script>
            $(document).ready(function() {
                // Initialize role-based visibility
                updateRoleBasedVisibility();

                // Numeric only input
                $(".NumOnly").on('input', function(event) {
                    $(this).val($(this).val().replace(/[^\d].+/, ""));
                    if ((event.which < 48 || event.which > 57)) {
                        event.preventDefault();
                    }
                });

                // Role change handler
                $('#designation_id').change(function() {
                    updateRoleBasedVisibility();
                });

                // District change handler
                $('#dist_code').change(function() {
                    var district = $(this).val();
                    $('#is_urban').val('');
                    $('#block_code').html('<option value="">--Please Select--</option>');
                });

                // Urban/Rural change handler
                $('#is_urban').change(function() {
                    var urban_code = $(this).val();
                    if (urban_code == '') {
                        $('#block_code').html('<option value="">--Please Select--</option>');
                        return;
                    }

                    var select_district_code = $('#dist_code').val();
                    if (select_district_code == '') {
                        showAlert('Please Select District First');
                        $('#dist_code').focus();
                        $('#block_code').html('<option value="">--Please Select--</option>');
                        return;
                    }

                    updateBlockOptions(select_district_code, urban_code);
                });

                // Form submission handler
                $('#userForm').submit(function(e) {
                    // Add loading state
                    $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i>Creating...')
                        .prop('disabled', true);
                });

                function updateRoleBasedVisibility() {
                    var role_id = $('#designation_id').val();

                    // Reset all fields first
                    $('#district_div').show();
                    $('#is_urban_div').show();
                    $('#block_code_div').show();
                    $('#block_code').html('<option value="">--Please Select--</option>');

                    switch (role_id) {
                        case 'HOD':
                        case 'MisState':
                        case 'Dashboard':
                            $('#district_div').hide();
                            $('#is_urban_div').hide();
                            $('#block_code_div').hide();
                            break;

                        case 'Approver':
                        case 'Delegated Approver':
                            $('#is_urban_div').hide();
                            $('#block_code_div').hide();
                            $('#block_code').html('<option value="">--All--</option>');
                            break;

                        case 'Verifier':
                        case 'Operator':
                            // All location fields remain visible
                            break;

                        default:
                            // Handle other roles if needed
                            break;
                    }
                }

                function updateBlockOptions(districtCode, urbanCode) {
                    var htmlOption = '<option value="">--Please Select--</option>';

                    if (urbanCode == 2) {
                        $("#blk_sub_label").text('Block');
                        // Assuming 'blocks' is available globally from master-data-v2.js
                        if (typeof blocks !== 'undefined') {
                            $.each(blocks, function(key, value) {
                                if (value.district_code == districtCode) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text +
                                        '</option>';
                                }
                            });
                        }
                    } else if (urbanCode == 1) {
                        $("#blk_sub_label").text('Sub Div');
                        // Assuming 'subDistricts' is available globally from master-data-v2.js
                        if (typeof subDistricts !== 'undefined') {
                            $.each(subDistricts, function(key, value) {
                                if (value.district_code == districtCode) {
                                    htmlOption += '<option value="' + value.id + '">' + value.text +
                                        '</option>';
                                }
                            });
                        }
                    }

                    $('#block_code').html(htmlOption);
                }

                function showAlert(message) {
                    // Create Bootstrap alert
                    const alertDiv = $('<div>', {
                        'class': 'alert alert-warning alert-dismissible fade show',
                        'role': 'alert',
                        'html': `
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `
                    });

                    // Add to form
                    $('#userForm').prepend(alertDiv);

                    // Auto remove after 5 seconds
                    setTimeout(() => {
                        alertDiv.alert('close');
                    }, 5000);
                }

                // Auto-close alerts after 5 seconds
                $('.alert').each(function() {
                    const alert = $(this);
                    setTimeout(() => {
                        alert.alert('close');
                    }, 5000);
                });
            });

            // Close error message function
            function closeError(divId) {
                $('#' + divId).hide();
            }
        </script>

        <!-- Include your existing master data script -->
        {{-- <script src="{{ URL::asset('js/master-data-v2.js') }}"></script> --}}
    @endpush
