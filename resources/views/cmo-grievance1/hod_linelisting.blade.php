<style type="text/css">
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

    #loadingDi {
        position: absolute;
        top: 0px;
        right: 0px;
        width: 100%;
        height: 100%;
        background-color: #fff;
        background-image: url('../images/ajaxgif.gif');
        background-repeat: no-repeat;
        background-position: center;
        z-index: 10000000;
        opacity: 0.4;
        filter: alpha(opacity=40);
        /* For IE8 and earlier */
    }

    .loadingDivModal {
        position: absolute;
        top: 0px;
        right: 0px;
        width: 100%;
        height: 100%;
        background-color: #fff;
        background-image: url('../images/ajaxgif.gif');
        background-repeat: no-repeat;
        background-position: center;
        z-index: 10000000;
        opacity: 0.4;
        filter: alpha(opacity=40);
        /* For IE8 and earlier */
    }

    #updateDiv {
        border: 1px solid #d9d9d9;
        padding: 8px;
        box-shadow: 0 1px 1px rgb(0 0 0 / 10%);
    }

    #name_div {
        color: #0275d8;
        font-weight: 400;
    }

    #av_name_response {
        color: #5cb85c;
        font-weight: 400;
    }

    /* #failed_reason_id{
      color:#d9534f;
      
  } */
</style>
@extends('layouts.app-template-datatable_new')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Sarasori Mukhyamantri (CMO Grievance) List
            </h1>
            <ol class="breadcrumb">
                <i class="fa fa-clock-o"></i> Date : <span style="font-size: 12px; font-weight: bold;"><span
                        class='date-part'></span>&nbsp;&nbsp;<span class='time-part'></span></span>
            </ol>
        </section>
        <section class="content">
            <div class="box box-default">
                <div class="box-body">
                    <div id="loadingDi"></div>
                    <div class="panel panel-default">
                        <div class="panel-heading" style="font-size: 14px; font-weight: bold; font-style: italic;"><span
                                id="panel-icon">Enter Filter Criteria</div>
                        <div class="panel-body" style="padding: 5px;">
                            <div class="row">
                                <div class="col-md-12">
                                    @if ($message = Session::get('success'))
                                        <div class="alert alert-success alert-block">
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                            <strong>{{ $message }} </strong>
                                        </div>
                                    @endif
                                    @if ($message = Session::get('message'))
                                        <div class="alert alert-danger alert-block">
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @endif
                                    @if ($message = Session::get('msg1'))
                                        <div class="alert alert-danger alert-block">
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-12" style="margin-bottom: 10px;">
                                            <div class="col-md-3">
                                                <label class=" control-label">Process Type</label>
                                                <select class="form-control select2" name="process_type" id='process_type'>




                                                    <option value="2">Pending</option>
                                                    <option value="3">Marked and Approved and Send to CMO</option>



                                                </select>
                                                <span class="text-danger" id="error_process_type"></span>
                                            </div>

                                            <div class="form-group col-md-3" id="district_div">
                                                <label class="">District </label>
                                                <select name="district" id="district" class="form-control" tabindex="6">
                                                    <option value="">--All --</option>
                                                    @foreach ($districts as $district)
                                                        <option value="{{ $district->district_code }}"
                                                            @if (old('district') == $district->district_code) selected @endif>
                                                            {{ $district->district_name }}</option>
                                                    @endforeach
                                                    <option value="100"
                                                        @if (old('district') == '100') selected @endif>Not Available
                                                    </option>
                                                </select>
                                                <span id="error_district" class="text-danger"></span>
                                            </div>




                                            <div class="col-md-3" style="margin-top: 24px;">
                                                <button class="btn btn-primary" name="search_btn" id="search_btn"
                                                    type="button" disabled><i class="fa fa-search"></i>
                                                    Search</button>&nbsp;
                                                {{-- <button class="btn btn-default" name="reset_btn" id="reset_btn" type="button" disabled><i class="fa fa-refresh"></i> Reset</button> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="res_div" style="display: none;">
                        <div class="panel panel-default">
                            <div class="panel-heading" id="panel_head"
                                style="font-size: 14px; font-weight: bold; font-style: italic;">List of Beneficiary</div>
                            <form method="POST" action="#" target="_blank" name="fullForm" id="fullForm"
                                style="text-align: center; align-content: center;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="is_bulk" id="is_bulk" value="1" />
                                <input type="hidden" id="id" name="id" />
                                <input type="hidden" name="applicantId[]" id="applicantId" value="" />
                                <br />
                                <button type="button" class="btn btn-success btn-lg" id="verifyReject"
                                    style="display:none;">Push to CMO</button>
                                <button style="display:none;" type="button" id="submitting" value="Submit"
                                    class="btn btn-success success" disabled>Processing Please Wait</button>

                            </form>
                            <div class="panel-body" style="padding: 5px; font-size: 14px;">
                                <div class="table-responsive">
                                    <table id="example" class="table display" cellspacing="0" width="100%">
                                        <thead style="font-size: 12px;">
                                            <th>Grievance ID</th>
                                            <th>Caller Name</th>
                                            <th>Caller Mobile No</th>
                                            <th>CMO Received Date(YYYY-MM-DD)</th>
                                            <!-- <th >CMO GP/Ward Name</th> -->
                                            {{-- <th> Description</th> --}}
                                            <th>Action</th>
                                            <th>Check <span id="checkbox_all_span"><input type="checkbox"
                                                        id='check_all_btn' style="width:48px;"></span> </th>
                                        </thead>
                                        <tbody style="font-size: 14px;"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
{{-- <script src="{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script> --}}
<script src="{{ asset('/bower_components/AdminLTE/plugins/jQuery/jquery-3.7.1.min.js') }}"></script>
<script>
    $(document).ready(function() {
        var interval = setInterval(function() {
            var momentNow = moment();
            $('.date-part').html(momentNow.format('DD-MMMM-YYYY'));
            $('.time-part').html(momentNow.format('hh:mm:ss A'));
        }, 100);

        $('#loadingDi').hide();
        $('#search_btn').removeAttr('disabled');

        var dataTable = null;

        $('#search_btn').click(function() {
            tableLoaded();
        });

        function tableLoaded() {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }

            $('#loadingDi').show();
            $('#res_div').show();
            $('#panel_head').text('Grievance List');

            $('#example tbody').empty();

            dataTable = $('#example').DataTable({
                dom: 'Blfrtip',
                scrollX: true,
                paging: true,
                searchable: true,
                ordering: false,
                bFilter: true,
                bInfo: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 50, 100],
                    [10, 50, 100]
                ],
                serverSide: true,
                processing: true,
                bRetrieve: true,
                oLanguage: {
                    sProcessing: '<div class="preloader1" align="center"><font style="font-size: 20px; font-weight: bold; color: green;">Processing...</font></div>'
                },
                ajax: {
                    url: "{{ url('cmo-grievance-hod-listing1') }}",
                    type: "post",
                    data: function(d) {
                        d.process_type = $('#process_type').val();
                        d.district_code = $('#district_code').val();
                        d._token = "{{ csrf_token() }}";
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#loadingDi').hide();
                        $('.preloader1').hide();
                        ajax_error(jqXHR, textStatus, errorThrown);
                    }
                },
                initComplete: function() {
                    $('#loadingDi').hide();
                },
                columns: [{
                        data: "grievance_id"
                    },
                    {
                        data: "grievance_name"
                    },
                    {
                        data: "sm_mobile_no"
                    },
                    {
                        data: "cmo_receive_date"
                    },
                    {
                        data: "view"
                    },
                    {
                        data: "check"
                    }
                ],
                buttons: [{
                        extend: 'pdf',
                        footer: true,
                        pageSize: 'A4',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        pageSize: 'A4',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5],
                            stripHtml: false
                        }
                    }
                ]
            });
        }

        $('.modalEncloseClose').click(function() {
            $('.encolser_modal').modal('hide');
        });

        $(document).on('click', '.find_applicant', function() {
            var val = $(this).val();
            var array = val.split("_");
            var data = {
                _token: '{{ csrf_token() }}',
                grievance_id: array[0],
                grievance_mobile_no: array[2]
            };
            redirectPost('{{ route('cmo-grievance-find1') }}', data, 'post');
        });

        $(document).on('click', '.grivance_tag_applicant', function() {
            var val = $(this).val();
            var array = val.split("_");
            var data = {
                _token: '{{ csrf_token() }}',
                grievance_id: array[0]
            };
            redirectPost('{{ route('cmo-grievance-applicant-tag1') }}', data, 'post');
        });

        $('#example').on('length.dt', function() {
            $("#check_all_btn").prop("checked", false);
        });

        $('#check_all_btn').on('change', function() {
            var checked = $(this).prop('checked');

            dataTable.cells(null, 5).every(function() {
                var cell = this.node();
                $(cell).find('input[type="checkbox"][name="chkbx"]').prop('checked', checked);
            });

            updateCheckboxData();
        });

        $(document).on('click', '#verifyReject', function() {
            var is_bulk = $('#is_bulk').val();
            var applicantId = $('#applicantId').val();

            $.confirm({
                title: 'Warning',
                type: 'orange',
                icon: 'fa fa-warning',
                content: '<strong>Are you sure to proceed?</strong>',
                buttons: {
                    Ok: function() {
                        $("#submitting").show();
                        $("#verifyReject").hide();

                        $.ajax({
                            type: 'POST',
                            url: "{{ url('cmo-grievance-hod-post1') }}",
                            data: {
                                is_bulk: is_bulk,
                                applicantId: applicantId,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(data) {
                                var table_renew = $('#example').DataTable();
                                table_renew.ajax.reload(null, false);

                                if (data.status == 1) {
                                    $('#approve_rejdiv').hide();
                                    $.confirm({
                                        title: 'Success',
                                        type: 'green',
                                        icon: 'fa fa-check',
                                        content: data.msg,
                                        buttons: {
                                            Ok: function() {
                                                $("#submitting")
                                                    .hide();
                                                $("#verifyReject")
                                                    .show();
                                                $("html, body")
                                                    .animate({
                                                        scrollTop: 0
                                                    }, "slow");
                                            }
                                        }
                                    });
                                } else {
                                    $("#submitting").hide();
                                    $("#verifyReject").show();
                                    $('.ben_view_modal').modal('hide');
                                    $('#approve_rejdiv').hide();
                                    $.alert({
                                        title: 'Error',
                                        type: 'red',
                                        icon: 'fa fa-warning',
                                        content: data.msg
                                    });
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.log(errorThrown);
                            }
                        });
                    },
                    Cancel: function() {}
                }
            });
        });

        function redirectPost(url, data, method = 'get') {
            var form = document.createElement('form');
            form.method = method;
            form.action = url;
            for (var name in data) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = data[name];
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }

        function updateCheckboxData() {
            var anyBoxesChecked = false;
            var applicantId = [];

            $('input[type="checkbox"][name="chkbx"]').each(function() {
                if ($(this).is(":checked")) {
                    anyBoxesChecked = true;
                    applicantId.push(this.value);
                }
            });

            $("#fullForm #applicantId").val($.unique(applicantId));

            if (anyBoxesChecked) {
                $('#verifyReject').show().prop('disabled', false);
                //$('#check_all_btn').prop('disabled', true);
            } else {
                $('#verifyReject').hide().prop('disabled', true);
                //$('#check_all_btn').prop('disabled', false);
            }
        }

        $(document).on('change', 'input[type="checkbox"][name="chkbx"]', updateCheckboxData);
    });
</script>
