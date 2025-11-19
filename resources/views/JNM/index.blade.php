<style type="text/css">
    .required-field::after {
        content: "*";
        color: red;
    }

    .has-error {
        border-color: #cc0000;
        background-color: #ffff99;
    }

    .preloader1 {
        position: fixed;
        top: 40%;
        left: 52%;
        z-index: 999;
    }

    .preloader1 {
        background: transparent !important;
    }

    .panel-heading {
        padding: 0;
        border: 0;
    }

    .panel-title>a,
    .panel-title>a:active {
        display: block;
        padding: 5px;
        color: #555;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        word-spacing: 3px;
        text-decoration: none;
    }

    .panel-heading a:before {
        font-family: 'Glyphicons Halflings';
        content: "\e114";
        float: right;
        transition: all 0.5s;
    }

    .panel-heading.active a:before {
        -webkit-transform: rotate(180deg);
        -moz-transform: rotate(180deg);
        transform: rotate(180deg);
    }

    #enCloserTable tbody tr td {
        padding: 10px 10px 10px 10px;
    }

    .modal-open {
        overflow: visible !important;
    }

    .required:after {
        color: red;
        content: '*';
        font-weight: bold;
        margin-left: 5px;
        float: right;
        margin-top: 5px;
    }

    #loadingDivModal {
        position: absolute;
        top: 0px;
        right: 0px;
        width: 100%;
        height: 100%;
        background-color: #fff;
        background-image: url('images/ajaxgif.gif');
        background-repeat: no-repeat;
        background-position: center;
        z-index: 10000000;
        opacity: 0.4;
        filter: alpha(opacity=40);
        /* For IE8 and earlier */
    }

    .disabledcontent {
        pointer-events: none;
        opacity: 0.4;
    }
</style>

@extends('layouts.app-template-datatable')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header mb-3">
        <h1>
            Importing data from Jonmo Mrityu Tothyo portal
        </h1>
    </section>

    <section class="content">

        <div class="card" id="full-content">
            <div id="loadingDiv"></div>

            <div class="card-body">

                {{-- Importing Data --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <b>Importing data from Jonmo Mrityu Tothyo portal
                            <span>(Next data fetch is scheduled for: {{$lastFetchingDate}})</span>
                        </b>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="from_date" class="form-label">From Date : <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="from_date" name="from_date" class="form-control" autocomplete="off"
                                    placeholder="Enter From Date(DD/MM/YYYY)">
                                <span id="error_from_date" class="text-danger"></span>
                            </div>
                            <div class="col-md-3">
                                <label for="to_date" class="form-label">To Date : <span class="text-danger">*</span></label>
                                <input type="text" id="to_date" name="to_date" class="form-control" autocomplete="off"
                                    placeholder="Enter To Date(DD/MM/YYYY)">
                                <span id="error_to_date" class="text-danger"></span>
                            </div>
                            <div class="col-md-3">
                                <label for="index" class="form-label">Index : <span class="text-danger">*</span></label>
                                <input type="text" id="index" class="limit form-control" placeholder="Enter Index">
                                <span id="error_index" class="text-danger"></span>
                            </div>
                            <div class="col-md-3">
                                <label for="page_size" class="form-label">Page Size (No. of records) : <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="page_size" class="limit form-control" placeholder="Enter Page Size">
                                <span id="error_page_size" class="text-danger"></span>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" name="btn_import" id="btn_import" value="approve"
                                class="btn btn-primary btn_import" disabled>
                                <i class="fa fa-download"></i> Import
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Callback Data --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <b>Calling Back data to Jonmo Mrityu Tothyo portal</b>
                    </div>
                    <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">Data Captured : <span id="total_captured"></span></div>
                            <div class="col-md-4">Data CallBack Done : <span id="total_done"></span></div>
                            <div class="col-md-4">Data Callback Pending : <span id="total_pending"></span></div>
                        </div>

                        <div class="row align-items-end mt-3">
                            <div class="col-md-6">
                                <label for="limit_jnm" class="form-label">Enter Limit:</label>
                                <input type="text" id="limit_jnm" class="limit form-control" placeholder="Enter limit!!">
                                <span id="error_jnm_id" class="text-danger"></span>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="go" id="go" value="approve" class="btn btn-info limit_data"
                                    disabled>
                                    <i class="fa fa-upload"></i> Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mark as Death --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <b>Marking Beneficiaries as Death case to Lakshmir Bhandar Portal (As per the data from Jonmo Mrityu
                            Tothyo Portal)</b>
                    </div>
                    <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">Total Beneficiary Marked as Death : <span id="total_mark_death"></span>
                            </div>
                            <div class="col-md-4">Current Beneficiary Marked as Death : <span
                                    id="total_cur_marked_death"></span></div>
                            <div class="col-md-4">Re-activate Death Incident : <span id="total_reactive_death"></span></div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="submit" name="final_marking" id="final_marking" value="approve"
                                    class="btn btn-warning" disabled>
                                    <i class="fa fa-thumb-tack"></i> Mark as Death to Lakshmir Bhandar Portal
                                </button>
                            </div>
                            {{-- Uncomment if needed in future
                            <div class="col-md-6">
                                <button type="submit" name="migrated_data" id="migrated_data" value="approve"
                                    class="btn btn-warning" disabled>
                                    <i class="fa fa-upload"></i> Migrated to Payment Server
                                </button>
                            </div>
                            --}}
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section>

@endsection
@push("scripts")

    <script src="{{ asset('js/master-data-v2.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#loadingDiv').hide();
            $('.sidebar-menu li').removeClass('active');
            $('.sidebar-menu #importJNMPData').addClass("active");
            // $('.sidebar-menu #accValTrFailed').addClass("active");
            $('#btn_import').removeAttr('disabled');
            $('#go').removeAttr('disabled');
            $('#final_marking').removeAttr('disabled');
            $('#migrated_data').removeAttr('disabled');
            $('#from_date').datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                "setDate": "today",
                "endDate": "today+1",
                //   "maxDate":  new Date(),

            });
            $('#to_date').datepicker({
                format: "dd/mm/yyyy",
                todayHighlight: true,
                autoclose: true,
                "setDate": "today",
                "endDate": "today+1",
                //   "maxDate":  new Date(),
            });
            updateJnmp();

            // Importing datas
            $(document).on('click', '#btn_import', function () {
                var error_from_date = '';
                var error_to_date = '';
                var error_index = '';
                var error_page_size = '';

                if ($.trim($('#from_date').val()).length == 0) {
                    error_from_date = 'From date is required';
                    $('#error_from_date').text(error_from_date);
                } else {
                    error_from_date = '';
                    $('#error_from_date').text(error_from_date);
                }

                if ($.trim($('#to_date').val()).length == 0) {
                    error_to_date = 'TO date is required';
                    $('#error_to_date').text(error_to_date);
                } else {
                    error_to_date = '';
                    $('#error_to_date').text(error_to_date);
                }

                if ($.trim($('#index').val()).length == 0) {
                    error_index = 'Index is required';
                    $('#error_index').text(error_index);
                } else {
                    error_index = '';
                    $('#error_index').text(error_index);
                }

                if ($.trim($('#page_size').val()).length == 0) {
                    error_page_size = 'Page size is required';
                    $('#error_page_size').text(error_page_size);
                } else {
                    error_page_size = '';
                    $('#error_page_size').text(error_page_size);
                }
                if (error_from_date != '' && error_to_date != '' && error_index != '' && error_page_size != '') {
                    return false;
                } else {
                    jnmpInsertData();
                }
            });

            // Callback datas
            $(document).on('click', '.limit_data', function (e) {
                e.preventDefault();
                var limit = $('#limit_jnm').val();
                callingback(limit);
            });

            $(document).on('click', '#final_marking', function () {
                $('#loadingDiv').show();
                $.ajax({
                    url: "{{ route('jnmpDataMarkasDeathInLB') }}",
                    type: "POST",
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        $('#loadingDiv').hide();
                        if (response.status == 1) {
                            // updateJnmp();
                            // $('#total_captured').text(response.totalData);
                            $.alert({
                                title: response.title,
                                type: response.type,
                                icon: response.icon,
                                content: response.msg,
                                buttons: {
                                    formSubmit: {
                                        text: 'Done',
                                        btnClass: 'btn-blue',
                                        action: function () {
                                            updateJnmp();
                                        }
                                    },
                                    cancel: function () {
                                        updateJnmp();
                                    }
                                }
                            });
                            $("html, body").animate({
                                scrollTop: 0
                            }, "slow");
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#loadingDiv').hide();
                        ajax_error(jqXHR, textStatus, errorThrown);
                    }
                });
            });
        });

        // Getting Pending datas
        function updateJnmp() {
            $('#loadingDiv').show();
            $.ajax({
                url: "{{ route('totalJnmp') }}",
                type: "POST",
                dataType: 'json',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    $('#loadingDiv').hide();
                    $('#total_captured').text(response.totalJnmp);
                    $('#total_pending').text(response.remainingJnmp);
                    $('#total_done').text(response.updatedJnmp);

                    $('#total_mark_death').text(response.data1);
                    $('#total_cur_marked_death').text(response.data2);
                    $('#total_reactive_death').text(response.data3);

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingDiv').hide();
                    ajax_error(jqXHR, textStatus, errorThrown);
                }
            });
        }

        // insert data
        function jnmpInsertData() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var index = $('#index').val();
            var page_size = $('#page_size').val();

            $('#loadingDiv').show();
            $.ajax({
                url: "{{ route('jnmpInsertData') }}",
                type: "POST",
                dataType: 'json',
                data: {
                    from_date: from_date,
                    to_date: to_date,
                    index: index,
                    page_size: page_size,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    $('#loadingDiv').hide();
                    $('#from_date').val('');
                    $('#to_date').val('');
                    $('#index').val('');
                    $('#page_size').val('');
                    if (response.status == 1) {
                        // updateJnmp();
                        // $('#total_captured').text(response.totalData);
                        $.alert({
                            title: response.title,
                            type: response.type,
                            icon: response.icon,
                            content: response.msg,
                            buttons: {
                                formSubmit: {
                                    text: 'Send Response',
                                    btnClass: 'btn-blue',
                                    action: function () {
                                        callingback(response.totalData);
                                    }
                                },
                                cancel: function () {
                                    updateJnmp();
                                }
                            }
                        });
                        $("html, body").animate({
                            scrollTop: 0
                        }, "slow");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingDiv').hide();
                    ajax_error(jqXHR, textStatus, errorThrown);
                }
            });
        }

        // Calling Back
        function callingback(limit) {
            $('#loadingDiv').show();
            $.ajax({
                type: "POST",
                url: "{{ route('jnmpDataCallbackDetails') }}",
                data: {
                    limit: limit,
                    _token: "{{ csrf_token() }}"
                },
                // dataType: "json",
                success: function (response) {
                    $('#loadingDiv').hide();
                    updateJnmp();
                    if (response.status == 400) {
                        alert(response.message);
                        if ($.trim($('#limit_jnm').val()).length == 0) {
                            error_Jnmp_id = 'Limit is required';
                            $('#error_jnm_id').text(error_Jnmp_id);
                            $('#limit_jnm').addClass('has-error');
                        } else {
                            error_scheme_id = '';
                            $('#error_jnm_id').text(error_Jnmp_id);
                            $('#limit_jnm').removeClass('has-error');
                        }
                    } else {
                        $("#limit_jnm").val('');
                        alert(response.message);
                    }
                },
                complete: function () {
                    $('#spinner-div').hide();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#loadingDiv').hide();
                    ajax_error(jqXHR, textStatus, errorThrown);
                }
            });
        }

        function ajax_error(jqXHR, textStatus, errorThrown) {
            var msg = "<strong>Failed to Load data.</strong><br/>";
            if (jqXHR.status !== 422 && jqXHR.status !== 400) {
                msg += "<strong>" + jqXHR.status + ": " + errorThrown + "</strong>";
            } else {
                if (jqXHR.responseJSON.hasOwnProperty('exception')) {
                    msg += "Exception: <strong>" + jqXHR.responseJSON.exception_message + "</strong>";
                } else {
                    msg += "Error(s):<strong><ul>";
                    $.each(jqXHR.responseJSON, function (key, value) {
                        msg += "<li>" + value + "</li>";
                    });
                    msg += "</ul></strong>";
                }
            }
            alert(msg);
        }
    </script>

@endpush
