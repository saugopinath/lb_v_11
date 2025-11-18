<style>
    .card-header-custom {
        font-size: 16px;
        background: linear-gradient(to right, #c9d6ff, #e2e2e2);
        font-weight: bold;
        font-style: italic;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    /* Additional styling for better compatibility */
    /* .table-header-spacing th {
        padding: 12px 10px;
    } */
    #example_wrapper {
        width: 100% !important;
    }

    /* Ensure proper DataTable layout */
    .dt-container {
        display: block !important;
        width: 100% !important;
    }

    .dt-layout-table {
        display: block !important;
        width: 100% !important;
    }

    /* Remove any flex classes that might break the layout */
    .dt-container.d-flex {
        display: block !important;
    }

    .item_header {
        font-weight: bold;
    }

    .blueColor {
        color: #007bff;
    }

    .redColor {
        color: #dc3545;
    }

    .requied {
        color: #dc3545;
    }

    .alert-section .alert {
        margin-bottom: 15px;
    }

    .table-container {
        overflow-x: auto;
    }
</style>
@extends('layouts.app-template-datatable')
@section('content')
    <!-- Main content -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 mt-4">
                <form method="post" id="register_form" class="submit-once">
                    @csrf

                    <div class="tab-content" style="margin-top:16px;">
                        <div class="tab-pane active" id="personal_details">
                            <!-- Card with your design -->
                            <div class="card" id="res_div">
                                <div class="card-header card-header-custom">
                                    <h4 class="card-title mb-0"><b>User Management</b></h4>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <!-- Alert Messages -->
                                    <div class="alert-section">
                                        @if ($message = Session::get('success'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>{{ $message }}</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        @endif

                                        @if ($message = Session::get('error'))
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <strong>{{ $message }}</strong>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        @endif

                                        @if (count($errors) > 0)
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <ul class="mb-0">
                                                    @foreach ($errors as $error)
                                                        <li><strong> {{ $error }}</strong></li>
                                                    @endforeach
                                                </ul>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        @endif

                                        <div class="alert alert-danger print-error-msg" style="display:none;"
                                            id="errorDivMain">
                                            <button type="button" class="btn-close" aria-label="Close"
                                                onclick="closeError('errorDivMain')"></button>
                                            <ul class="mb-0"></ul>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3" id="addButton">
                                        @if (
                                            $designation_id == 'Admin' ||
                                                $designation_id == 'HOD' ||
                                                $designation_id == 'Verifier' ||
                                                $designation_id == 'Approver' ||
                                                $designation_id == 'Delegated Approver')
                                            <a class="btn btn-primary" href="{{ route('adduser') }}">Add User and Assign
                                                Role</a>
                                        @endif
                                    </div>
                                    <hr>

                                    <!-- Search Section -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="row align-items-end">
                                                @if ($mapping_visible)
                                                    <div class="form-group col-md-3 mb-3">
                                                        <label class="form-label">Mapping Level</label>
                                                        <select class="form-control" name="stake_level_home"
                                                            id="stake_level_home">
                                                            <option value="">--ALL--</option>
                                                            @foreach ($user_levels as $stake)
                                                                <option value="{{ $stake->stake_code }}">
                                                                    {{ $stake->stake_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="stake_level_home" id="stake_level_home"
                                                        value="{{ $stake_level_home }}" />
                                                @endif

                                                @if ($role_visible)
                                                    <div class="form-group col-md-3 mb-3" id="designation_id_home_div">
                                                        <label class="form-label">Role</label>
                                                        <select class="form-control" name="designation_id_home"
                                                            id="designation_id_home">
                                                            <option value="">--ALL--</option>
                                                            @foreach ($roles as $role)
                                                                <option value="{{ $role->name }}">{{ $role->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div id="designation_id_home_ajax"></div>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="designation_id_home"
                                                        id="designation_id_home" value="{{ $designation_id_home }}" />
                                                @endif

                                                @if ($district_visible)
                                                    <div class="form-group col-md-3 mb-3" id="district_code_home_div">
                                                        <label for="district_code_home" class="form-label">District</label>
                                                        <select id="district_code_home" class="form-control">
                                                            <option value="">--ALL-</option>
                                                            @foreach ($districts as $district)
                                                                <option value="{{ $district->district_code }}">
                                                                    {{ $district->district_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div id="district_code_home_ajax"></div>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="district_code_home" id="district_code_home"
                                                        value="{{ $district_code }}" />
                                                @endif

                                                @if ($is_urban_visible)
                                                    <div class="form-group col-md-3 mb-3" id="is_urban_home_div">
                                                        <label for="is_urban_home" class="form-label">Rural/Urban</label>
                                                        <select id="is_urban_home" class="form-control"
                                                            name="is_urban_home">
                                                            <option value="">--All--</option>
                                                            @foreach ($levels as $key => $value)
                                                                <option value="{{ $key }}">{{ $value }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div id="is_urban_home_ajax"></div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="row align-items-end">
                                                @if ($block_visible)
                                                    <div class="form-group col-md-3 mb-3" id="block_code_home_div">
                                                        <label for="block_code_home" class="form-label"
                                                            id="blk_sub_txt">Block/Sub Div</label>
                                                        <select id="block_code_home" class="form-control"
                                                            name="block_code_home">
                                                            <option value="">--ALL--</option>
                                                        </select>
                                                        <div id="block_code_home_ajax"></div>
                                                    </div>
                                                @endif

                                                <div class="form-group col-md-3 mb-3">
                                                    <button type="button" name="submit" value="Submit"
                                                        class="btn btn-success table-action-btn" id="search_sws">
                                                        <i class="fas fa-search"></i> Search
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- DataTable Section -->
                                    <div class="table-container">
                                        <div class="table-responsive">
                                            <table id="example" class="display data-table" cellspacing="0"
                                                width="100%">
                                                <thead class="table-header-spacing">
                                                    <tr>
                                                        <th style="text-align: center">ID</th>
                                                        <th style="text-align: center">Status</th>
                                                        <th style="text-align: center">CanUpdate</th>
                                                        <th style="text-align: center">Display Name</th>
                                                        <th style="text-align: center">Role</th>
                                                        <th style="text-align: center">Mobile Number</th>
                                                        <th style="text-align: center">Email</th>
                                                        <th style="text-align: center">Location</th>
                                                        <th style="text-align: center">User Active?</th>
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

    <!-- Start User Active/Deactive Modal -->
    <div class="modal fade" id="ben_view_modal" tabindex="-1" role="dialog" aria-labelledby="benViewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" role="form" id="modal_form"
                    action="{{ route('userDutymanagement/toggleActivate') }}">
                    @csrf
                    <input type="hidden" name="modal_user_id" id="modal_user_id" value="">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="benViewModalLabel">Change User Status(Active and Deactivate from all
                            schemes)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <span id="error_same" class="text-danger"></span><br />
                        <table class="table table-borderless">
                            <tr>
                                <td style="width:30%;"><span class="item_header">Display Name:</span></td>
                                <td><span class="item_value" id="modal_username"></span></td>
                            </tr>
                            <tr>
                                <td><span class="item_header">Present Location:</span></td>
                                <td><span class="item_value" id="modal_location"></span></td>
                            </tr>
                            <tr>
                                <td><span class="item_header">Present Status:</span></td>
                                <td><span class="item_value blueColor" id="modal_pre_status"></span></td>
                            </tr>
                            <tr id="userActiveNew">
                                <td><span class="item_header">New Status:</span></td>
                                <td><span class="item_value redColor" id="modal_new_status"></span></td>
                            </tr>
                            <tr id="userActiveMsg">
                                <td><span class="item_header">Note:</span></td>
                                <td><span class="item_value redColor">User can not be deactivated as schemes of other
                                        departments are assigned.</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" id="change_button">Change Status</button>
                        <button type="button" id="submitting" value="Submit" class="btn btn-success btn-lg" disabled
                            style="display:none;">Submitting please wait</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Active/Deactive Modal Model -->

    <!--Update User Modal-->
    <div id="UserformModal" class="modal fade" role="dialog" aria-labelledby="userFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userFormModalLabel"><span class="crud-txt">Add User</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center"><img src="{{ asset('images/ZKZg.gif') }}" class="submit_loader"
                            width="50px" height="50px" style="display:none;"></div>
                    <div class="alert alert-danger print-error-msg" style="display:none" id="crud_msg_CrudModal">
                        <button type="button" class="btn-close" aria-label="Close"
                            onclick="closeError('crud_msg_CrudModal')"></button>
                        <ul class="mb-0"></ul>
                    </div>
                    <form id="userform" class="form-horizontal">
                        <input type="hidden" name="id" id="id" value="">
                        <input type="hidden" name="must_role_adduser" id="must_role_adduser" value="1">

                        <div class="row mb-3">
                            <div class="form-group col-md-4">
                                <label for="full_name" class="form-label">Full Name <span
                                        class="requied">*</span></label>
                                <input id="full_name" type="text" class="form-control txtOnly" name="full_name"
                                    value="">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="form-group col-md-4">
                                <label for="full_name_as_in_aadhar" class="form-label">Full Name as in Aadhaar <span
                                        class="requied">*</span></label>
                                <input id="full_name_as_in_aadhar" type="text" class="form-control txtOnly"
                                    name="full_name_as_in_aadhar" value="">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="form-group col-md-4">
                                <label for="address" class="form-label">Office Address</label>
                                <input id="address" type="text" class="form-control special-char" name="address"
                                    value="">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="username" class="form-label">Display Name <span
                                        class="requied">*</span></label>
                                <input id="username" type="text" class="form-control" name="username"
                                    value="">
                            </div>

                            <div class="form-group col-md-4">
                                <label for="email" class="form-label">E-Mail Address <span
                                        class="requied">*</span></label>
                                <input id="email" type="text" class="form-control" name="email"
                                    value="">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="form-group col-md-4">
                                <label for="mobile_no" class="form-label">Mobile Number <span
                                        class="requied">*</span></label>
                                <input id="mobile_no" type="text" class="form-control NumOnly" name="mobile_no"
                                    value="" maxlength="10">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btn-submit">
                        <span class="crud-txt">Add</span>
                    </button>
                    <img style="display:none;" src="{{ asset('images/ZKZg.gif') }}" id="btn_addEdit_loader"
                        width="50px" height="50px">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--End of Update User  Modal-->

    <!-- Start Toggle Duty Modal-->
    <div class="modal fade" id="ben_duty_modal" tabindex="-1" aria-labelledby="benDutyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" role="form" id="modal_duty_form"
                    action="{{ route('userDutymanagement/toggleDuty') }}">
                    @csrf
                    <input type="hidden" name="modal_duty_id" id="modal_duty_id" value="">
                    <input type="hidden" name="modal_duty_user_id" id="modal_duty_user_id" value="">
                    <input type="hidden" name="modal_duty_scheme_id" id="modal_duty_scheme_id" value="">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="benDutyModalLabel">Change Duty Assigment Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <span id="error_same" class="text-danger"></span><br />
                        <table class="table table-borderless">
                            <tr>
                                <td style="width:50%;"><span class="item_header">Display Name:</span></td>
                                <td><span class="item_value" id="modal_duty_username"></span></td>
                            </tr>
                            <tr>
                                <td><span class="item_header">Present Location:</span></td>
                                <td><span class="item_value" id="modal_duty_location"></span></td>
                            </tr>
                            <tr>
                                <td><span class="item_header">Actionable Scheme:</span></td>
                                <td><span class="item_value" id="modal_duty_scheme"></span></td>
                            </tr>
                            <tr>
                                <td><span class="item_header">Present Status:</span></td>
                                <td><span class="item_value blueColor" id="modal_duty_pre_status"></span></td>
                            </tr>
                            <tr>
                                <td><span class="item_header">New Status:</span></td>
                                <td><span class="item_value redColor" id="modal_duty_new_status"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" id="change_duty">Change Duty Status</button>
                        <button type="button" id="submitting-duty" value="Submit" class="btn btn-success btn-lg"
                            disabled style="display:none;">Submitting please wait</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Toggle Duty Modal-->
@endsection

@push('scripts')
    <script type="text/javascript">
        // Variables
        var sessiontimeoutmessage = '{{ $sessiontimeoutmessage }}';
        var base_url = '{{ url('/') }}';
        var table;
        var blocks = []; // You'll need to populate this with your block data
        var subDistricts = []; // You'll need to populate this with your subdistrict data

        $(document).ready(function() {
            // Initialize DataTable
            initializeDataTable();

            // Event handlers
            $("#submitting").hide();
            $("#submitting-duty").hide();
            $("#submitting-mapnewscheme").hide();
            $(".btnMap1").hide();

            // Set up CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });

            // Search button click handler
            $("#search_sws").click(function() {
                table.ajax.reload();
            });

            // Form input validation
            $('.txtOnly').keypress(function(e) {
                var regex = new RegExp(/^[a-zA-Z\s]+$/);
                var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
                if (!regex.test(str)) {
                    e.preventDefault();
                    return false;
                }
            });

            $(".NumOnly").keyup(function(event) {
                $(this).val($(this).val().replace(/[^\d].+/, ""));
                if ((event.which < 48 || event.which > 57)) {
                    event.preventDefault();
                }
            });

            $('.special-char').keyup(function() {
                var yourInput = $(this).val();
                var re = /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
            var isSplChar = re.test(yourInput);
            if (isSplChar) {
                var no_spl_char = yourInput.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
                    $(this).val(no_spl_char);
                }
            });

            // Modal event handlers
            $('#change_button').on('click', toggleUserStatus);
            $('#change_duty').on('click', toggleDutyStatus);
            $('#btn-submit').on('click', submitUserForm);

            // Location dropdown handlers
            $('#district_code_home').change(function() {
                var district = $(this).val();
                $('#block_code_home').html('<option value="">--All --</option>');
            });

            $('#is_urban_home').change(function() {
                var urban_code = $(this).val();
                if (urban_code == '') {
                    $('#block_code_home').html('<option value="">--All --</option>');
                    return;
                }

                var select_district_code = $('#district_code_home').val();
                if (select_district_code == '') {
                    alert('Please Select District First');
                    $("#district_code_home").focus();
                    $('#block_code_home').html('<option value="">--All --</option>');
                    return;
                }

                var htmlOption = '<option value="">--All--</option>';
                if (urban_code == 2) {
                    $("#blk_sub_txt").text('Block');
                    $.each(blocks, function(key, value) {
                        if (value.district_code == select_district_code) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                } else if (urban_code == 1) {
                    $("#blk_sub_txt").text('Sub Div');
                    $.each(subDistricts, function(key, value) {
                        if (value.district_code == select_district_code) {
                            htmlOption += '<option value="' + value.id + '">' + value.text +
                                '</option>';
                        }
                    });
                }

                $('#block_code_home').html(htmlOption);
                $("#block_code_home_div").show();
            });

            // DataTable search input handler
            $('#example_filter input')
                .off()
                .on('blur', function() {
                    table.search(this.value).draw();
                });
        });

        function initializeDataTable() {
            if (table) {
                $('#example').DataTable().destroy();
            }

            $("#excel-btn").hide();

            table = $('#example').DataTable({
                "paging": true,
                "pageLength": 10,
                "lengthMenu": [
                    [10, 20, 50, 80, 120, 150, 180, 500, 1000, 2000],
                    [10, 20, 50, 80, 120, 150, 180, 500, 1000, 2000]
                ],
                "serverSide": true,
                "deferRender": true,
                "processing": true,
                "bRetrieve": true,
                "ordering": false,
                "searching": true,
                "language": {
                    "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
                },
                "ajax": {
                    url: "{{ url('userDutymanagement/Search') }}",
                    type: "GET",
                    data: function(d) {
                        d.mapping_level = $('#stake_level_home').val();
                        d.designation_id = $('#designation_id_home').val();
                        d.scheme_id = $('#scheme_home').val();
                        d.district_code = $('#district_code_home').val();
                        d.is_urban = $('#is_urban_home').val();
                        d.block_code = $('#block_code_home').val();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "is_active_db"
                    },
                    {
                        "data": "CanUpdate"
                    },
                    {
                        "data": "username"
                    },
                    {
                        "data": "designation_id"
                    },
                    {
                        "data": "mobile_no"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "location"
                    },
                    {
                        "data": "is_active"
                    },
                    {
                        "data": "action"
                    }
                ],
                "drawCallback": function() {
                    $('.select2').select2();
                    $('#preloader1').hide();
                    $(".btnMap1").hide();
                },
                "columnDefs": [{
                        targets: "_all",
                        "orderable": false
                    },
                    {
                        targets: [0, 1, 2],
                        "visible": false
                    }
                ]
            });

            // DataTable row click handlers
            table.on('click', '.toggleStatus', function() {
                showUserStatusModal($(this));
            });

            table.on('click', '.toggleDuty', function() {
                showDutyStatusModal($(this));
            });

            table.on('click', '.btnMap', function() {
                mapNewScheme($(this));
            });
        }

        function showUserStatusModal(button) {
            $('#modal_form #modal_user_id').val('');
            $('#modal_username').html('');
            $('#modal_location').html('');
            $('#modal_schemes').html('');
            $('#modal_pre_status').html('');
            $('#modal_new_status').html('');

            var $tr = button.closest('tr');
            if ($tr.hasClass('child')) {
                $tr = $tr.prev('parent');
            }

            var data = table.row($tr).data();
            var cur_status = data['is_active_db'] == 1 ? 'Active' : 'InActive';
            var new_status = data['is_active_db'] == 1 ? 'InActive' : 'Active';

            $('#modal_form #modal_user_id').val(data['id']);
            $('#modal_username').html(data['username']);
            $('#modal_location').html(data['location']);
            $('#modal_pre_status').html(cur_status);
            $('#modal_new_status').html(new_status);

            if (data['CanUpdate'] == 1) {
                $("#userActiveNew").show();
                $("#userActiveMsg").hide();
                $("#change_button").show();
            } else {
                $("#change_button").hide();
                $("#userActiveNew").hide();
                $("#userActiveMsg").show();
            }

            $('#ben_view_modal').modal('show');
        }

        function showDutyStatusModal(button) {
            $('#modal_duty_form #modal_duty_id').val('');
            $('#modal_duty_form #modal_duty_user_id').val('');
            $('#modal_duty_form #modal_duty_scheme_id').val('');
            $('#modal_duty_username').html('');
            $('#modal_duty_location').html('');
            $('#modal_duty_scheme').html('');
            $('#modal_duty_pre_status').html('');
            $('#modal_duty_new_status').html('');

            var $tr = button.closest('tr');
            if ($tr.hasClass('child')) {
                $tr = $tr.prev('parent');
            }

            var data = table.row($tr).data();

            $('#modal_duty_form #modal_duty_id').val(button.attr('duty_id'));
            $('#modal_duty_form #modal_duty_user_id').val(data['id']);
            $('#modal_duty_form #modal_duty_scheme_id').val(button.attr('scheme_id'));
            $('#modal_duty_username').html(data['username']);
            $('#modal_duty_location').html(data['location']);
            $('#modal_duty_scheme').html(button.attr('scheme_name'));
            $('#modal_duty_pre_status').html(button.attr('pre_status'));
            $('#modal_duty_new_status').html(button.attr('new_status'));

            $('#ben_duty_modal').modal('show');
        }

        function mapNewScheme(button) {
            var id = button.attr('user_id');
            var scheme_list = $("#schemelistAdd_" + id).val();

            if ($.trim($("#schemelistAdd_" + id).val()).length == 0) {
                alert('Scheme is required');
                $("#schemelistAdd_" + id).focus();
                return;
            }

            $("#btnmap_" + id).hide();
            $("#btnmap_submitting_" + id).show();

            $.ajax({
                type: 'POST',
                url: '{{ url('userDutymanagement/mapNewScheme') }}',
                data: {
                    scheme_id_list: scheme_list,
                    user_id: id,
                    _token: '{{ csrf_token() }}',
                },
                success: function(data) {
                    if (data.return_status) {
                        $(".msg-div").hide();
                        $('#example').DataTable().ajax.reload(null, false);
                        printMsg(data.return_msg, '1', 'crud_msg_Crud');
                    } else {
                        printMsg(data.return_msg, '0', 'crud_msg_Crud');
                    }

                    $("#btnmap_" + id).show();
                    $("#btnmap_submitting_" + id).hide();
                },
                error: function(ex) {
                    $("#btnmap_" + id).show();
                    $("#btnmap_submitting_" + id).hide();
                }
            });
        }

        function toggleUserStatus() {
            // console.log('OK Toggle');

            $("#change_button").hide();
            $("#submitting").show();

            var id = $('#modal_form #modal_user_id').val();
            // alert(id);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            $.ajax({
                url: "{{ url('userDutymanagement/toggleActivate') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function(data) {
                    if (data.return_status) {
                        $(".msg-div").hide();
                        $('#example').DataTable().ajax.reload(null, false);
                        printMsg(data.return_msg, '1', 'crud_msg_Crud');
                    } else {
                        printMsg(data.return_msg, '0', 'crud_msg_Crud');
                    }

                    $("#submitting").hide();
                    $("#change_button").show();
                    $('#toggleActivate_' + id).prop('disabled', false);
                    $('#ben_view_modal').modal('hide');
                    $("html, body").animate({
                        scrollTop: "0"
                    });
                },
                error: function(ex) {
                    // alert(sessiontimeoutmessage);
                    $('#toggleActivate_' + id).prop('disabled', false);
                    $("html, body").animate({
                        scrollTop: "0"
                    });
                    // window.location.href = base_url;
                }
            });
        }

        function toggleDutyStatus() {
            $("#change_duty").hide();
            $("#submitting-duty").show();

            var user_id = $('#modal_duty_form #modal_duty_user_id').val();
            var id = $('#modal_duty_form #modal_duty_id').val();
            var scheme_id = $('#modal_duty_form #modal_duty_scheme_id').val();

            $.ajax({
                url: "{{ url('userDutymanagement/toggleDuty') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    user_id: user_id,
                    scheme_id: scheme_id
                },
                success: function(data) {
                    if (data.return_status) {
                        $(".msg-div").hide();
                        $('#example').DataTable().ajax.reload(null, false);
                        printMsg(data.return_msg, '1', 'crud_msg_Crud');
                    } else {
                        printMsg(data.return_msg, '0', 'crud_msg_Crud');
                    }

                    $("#submitting-duty").hide();
                    $("#change_duty").show();
                    $('#ben_duty_modal').modal('hide');
                    $("html, body").animate({
                        scrollTop: "0"
                    });
                },
                error: function(ex) {
                    alert(sessiontimeoutmessage);
                    $("html, body").animate({
                        scrollTop: "0"
                    });
                    window.location.href = base_url;
                }
            });
        }

        function UpdateUserForm(id) {
            var valid = 1;
            $(".print-error-msg").hide();
            $("#must_role_adduser").val(1);
            $("#full_name").val('');
            $("#full_name_as_in_aadhar").val('');
            $("#address").val('');
            $("#username").val('');
            $("#email").val('');
            $("#mobile_no").val('');
            $(".submit_loader").hide();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            if (id) {
                $(".submit_loader").show();

                $.ajax({
                    type: 'POST',
                    url: '/getUserInfo',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.return_status == 0) {
                            $(".submit_loader").hide();
                            printMsg(data.return_msg, '0', 'crud_msg_Crud');
                        } else {
                            $("#full_name").val(data.userarr.full_name);
                            $("#full_name_as_in_aadhar").val(data.userarr.full_name_as_in_aadhar);
                            $("#address").val(data.userarr.address);
                            $("#username").val(data.userarr.username);
                            $("#email").val(data.userarr.email);
                            $("#mobile_no").val(data.userarr.mobile_no);
                            $("#department_id_adduser").val(data.userarr.department_id);
                            $("#designation_id_adduser").val(data.userarr.designation_id);
                            $("#user_id").val(data.userarr.id);
                            $(".forAddUserOnly").hide();
                            $(".submit_loader").hide();
                            $("#UserformModal").modal('show');
                        }
                    },
                    error: function(ex) {
                        $(".submit_loader").hide();
                        alert(sessiontimeoutmessage);
                        window.location.href = base_url;
                    }
                });
            }

            if (id) {
                $(".forAddUserOnly").hide();
                var crud_txt = 'Update User';
            } else {
                $(".forAddUserOnly").show();
                var crud_txt = 'Add User';
            }

            $("#id").val(id);
            $(".crud-txt").text(crud_txt);
        }

        function submitUserForm() {
            $('#btn-submit').prop('disabled', true);
            $("#btn_addEdit_loader").show();

            var $full_name = $("#full_name").val();
            var $full_name_as_in_aadhar = $("#full_name_as_in_aadhar").val();
            var $address = $("#address").val();
            var $username = $("#username").val();
            var $email = $("#email").val();
            var $mobile_no = $("#mobile_no").val();
            var $id = $('#id').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            $.ajax({
                url: "{{ url('userDutymanagement/Update') }}",
                type: 'POST',
                dataType: "json",
                data: {
                    id: $id,
                    full_name: $full_name,
                    full_name_as_in_aadhar: $full_name_as_in_aadhar,
                    address: $address,
                    username: $username,
                    email: $email,
                    mobile_no: $mobile_no
                },
                success: function(data) {
                    if (data.return_status) {
                        $("#UserformModal").modal('hide');
                        $("html, body").animate({
                            scrollTop: "0"
                        });
                        printMsg(data.return_msg, '1', 'crud_msg_Crud');
                        $('#example').DataTable().ajax.reload(null, false);
                    } else {
                        $('#UserformModal').animate({
                            scrollTop: 0
                        }, 'slow');
                        printMsg(data.return_msg, '0', 'crud_msg_CrudModal');
                    }

                    $("#btn_addEdit_loader").hide();
                    $('#btn-submit').prop('disabled', false);
                },
                error: function(ex) {
                    $('#UserformModal').animate({
                        scrollTop: 0
                    }, 'slow');
                    // alert(sessiontimeoutmessage);
                    $('#btn-submit').prop('disabled', false);
                    // window.location.href = base_url;
                }
            });
        }

        function reset(divid) {
            $('#district_code_' + divid).val('');
            $('#subdiv_code_' + divid).val('');
            $('#block_munc_corp_code_' + divid).val('');
            $('#gp_ward_code_' + divid).val('');
        }

        function closeError(divId) {
            $('#' + divId).hide();
        }

        function printMsg(msg, msgtype, divid) {
            $("#" + divid).find("ul").html('');
            $("#" + divid).css('display', 'block');

            if (msgtype == '0') {
                $("#" + divid).removeClass('alert-success');
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
    </script>
@endpush
