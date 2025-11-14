
@extends('layouts.app-template-datatable')
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

                                                    @if (
                                                        $mapLevel == 'BlockVerifier' ||
                                                            $mapLevel == 'SubdivVerifier' ||
                                                            $mapLevel == 'SubdivDelegated Verifier' ||
                                                            $mapLevel == 'BlockDelegated Verifier')
                                                        <option value="1">Pending</option>
                                                        <option value="2">Marked but Approval Pending</option>
                                                        <option value="3">Marked and Approved but Yet not send to CMO
                                                        </option>
                                                        <option value="5">Sent to Operator for New Entry</option>
                                                        <option value="4">Marked and Approved and Send to CMO</option>
                                                    @endif
                                                    @if ($mapLevel == 'District')
                                                        <option value="1">Pending</option>
                                                        <option value="3">Marked and Approved but Yet not send to CMO
                                                        </option>
                                                        <option value="5">Sent to Operator for New Entry</option>
                                                        <option value="6">Grivance List with No BLock/Municipality LGD
                                                        </option>
                                                        <option value="4">Marked and Approved and Send to CMO</option>
                                                    @endif
                                                    @if ($mapLevel == 'Department')
                                                        <option value="7">Grivance List with No
                                                            District/BLock/Municipality LGD</option>
                                                        <option value="4">Marked and Approved and Send to CMO</option>
                                                    @endif
                                                    {{-- <option value="5">Block Change Request</option> --}}
                                                </select>
                                                <span class="text-danger" id="error_process_type"></span>
                                            </div>
                                            @if ($mapLevel == 'SubdivVerifier')
                                                {{-- <div class="col-md-3">
                                                    <label class=" control-label" >Jai Bangla Municipality</label>
                                                    <select name="filter_1" id="filter_1" class="form-control select2 full-width js-municipality" >
                                                    <option value="">-----All----</option>
                                                    @foreach ($urban_bodys as $urban_body)
                                                    <option value="{{$urban_body->urban_body_code}}" > {{$urban_body->urban_body_name}}</option>
                                        @endforeach
                                        </select>
                                    </div> --}}
                                                {{-- <div class="col-md-3">
                                                    <label class=" control-label" >Jai Bangla Wards</label>
                                                    <select name="filter_2" id="filter_2" class="form-control select2 full-width js-wards" >
                                                    <option value="">-----All----</option>
                                                    </select>
                                                </div>  --}}
                                                <input type="hidden" name="local_body" id="local_body"
                                                    value={{ $local_body_code }}>
                                            @elseif($mapLevel == 'BlockVerifier')
                                                {{-- <div class="col-md-3">
                                                    <label class=" control-label" > Jai Bangla Gram Panchayat</label>
                                                    <select name="filter_1" id="filter_1" class="form-control select2 full-width" >
                                                    <option value="">-----All----</option>
                                                    @foreach ($gps as $gp)
                                                        <option value="{{$gp->gram_panchyat_code}}" > {{$gp->gram_panchyat_name}}</option>
                                    @endforeach
                                    </select>
                                </div> --}}
                                                <input type="hidden" name="local_body" id="local_body"
                                                    value={{ $local_body_code }}>
                                            @elseif($mapLevel == 'District')
                                                <input type="hidden" name="local_body" id="local_body" value="">
                                            @endif
                                            <input type="hidden" name="mapLevel" id="mapLevel" value={{ $mapLevel }}>
                                            @if ($mapLevel != 'Department')
                                                <input type="hidden" name="district_code" id="district_code"
                                                    value="{{ $district_code }}">
                                            @endif
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
    <div class="modal" id="mapbos" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Map this user under <span id="dist-name"></span><button type="button"
                            class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button></h2>
                    <input type="hidden" class="dist_code">
                    <input type="hidden" id="grievance_id">
                </div>
                <form id="mapUserForm">
                    <div class="modal-body">
                        <table class="table table-bordered table-responsive table-condensed table-striped"
                            style="font-size: 14px;">
                            <tr>
                                <td>
                                    <strong>Grievance Id: </strong>
                                    <span id="gri_id_div"></span>
                                </td>
                                <td>
                                    <strong>Grievance No: </strong>
                                    <span id="gri_no_div"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Applicant Name : </strong>
                                    <span id="appli_name_div"></span>
                                </td>
                                <td>
                                    <strong>Contact No: </strong>
                                    <span id="con_div"></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Age.: </strong>
                                    <span id="age_div"></span>
                                </td>
                                <td>
                                    <strong>Description :</strong>
                                    <span id="disc_div"></span>
                                </td>
                            </tr>
                        </table>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="control-label">Rural/Urban<span style="color:#cc0000;">*<span></label>
                                <select name="mapping_type" id="mapping_type" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="1">Rural</option>
                                    <option value="2">Urban</option>
                                </select>
                                <span class="text-danger error-message" id="mapping_type_error"></span>
                            </div>
                            <div class="col-md-6" style="display: none;" id="blk_sub_div">
                                <label class="control-label" id="blk_sub_txt"></label>
                                <select name="blk_sub_value" id="blk_sub_value" class="form-control">
                                </select>
                                <span class="text-danger error-message" id="blk_sub_value_error"></span>
                            </div>
                            <!-- <div class="col-md-3" style="display: none;" id="mun_div">
                                <label class="control-label">Municipality<span style="color:#cc0000;">*<span></label>
                                <select name="municipality" id="municipality" class="form-control">
                                </select>
                                <span class="text-danger error-message" id="municipality_error"></span>
                            </div> -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="submitButton" name='btnSubmit' class="btn btn-primary"
                            style="display:none;"></button>
                        <img style="display:none;" src="{{ asset('images/ZKZg.gif') }}" id="btn_encolser_loader"
                            width="100px">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
{{-- <script src="{{ asset ("/bower_components/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js") }}"></script> --}}
<script src="{{ asset('/bower_components/AdminLTE/plugins/jQuery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ URL::asset('js/confirmation_of_bank_account_validation.js') }}"></script>
<script src="{{ URL::asset('js/master-data-v2.js') }}"></script>
<script>
    $(document).ready(function() {
        var interval = setInterval(function() {
            var momentNow = moment();
            $('.date-part').html(momentNow.format('DD-MMMM-YYYY'));
            $('.time-part').html(momentNow.format('hh:mm:ss A'));
        }, 100);
        $('#loadingDi').hide();
        $('#search_btn').removeAttr('disabled');
        var error_scheme_type = '';
        $('#search_btn').click(function() {
            tableLoaded();
        });

        function tableLoaded() {

            $('#loadingDi').show();
            $('#res_div').show();
            var msg = 'Grievance List';
            $('#panel_head').text(msg);
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            $('#example tbody').empty();
            var table = $('#example').DataTable({
                dom: 'Blfrtip',
                "scrollX": true,
                "paging": true,
                "searchable": true,
                "ordering": false,
                "bFilter": true,
                "bInfo": true,
                "pageLength": 25,
                'lengthMenu': [
                    [10, 20, 25, 50, 100, -1],
                    [10, 20, 25, 50, 100, 'All']
                ],
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,
                "oLanguage": {
                    "sProcessing": '<div class="preloader1" align="center"><font style="font-size: 20px; font-weight: bold; color: green;">Processing...</font></div>'
                },
                "ajax": {
                    url: "{{ url('cmo-grievance-linelisting1') }}",
                    type: "post",
                    data: function(d) {
                        d.filter_1 = $('#filter_1').val(),
                            d.filter_2 = $('#filter_2').val(),
                            d.mapLevel = $('#mapLevel').val(),
                            d.local_body = $('#local_body').val(),
                            d.process_type = $('#process_type').val(),
                            d.district_code = $('#district_code').val(),
                            d._token = "{{ csrf_token() }}"
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#loadingDi').hide();
                        $('.preloader1').hide();
                        ajax_error(jqXHR, textStatus, errorThrown);
                    }
                },
                "initComplete": function() {
                    $('#loadingDi').hide();
                    //console.log('Data rendered successfully');
                },
                "columns": [{
                        "data": "grievance_id"
                    },
                    {
                        "data": "grievance_name"
                    },
                    {
                        "data": "sm_mobile_no"
                    },
                    {
                        "data": "cmo_receive_date"
                    },


                    {
                        "data": "view"
                    }
                ],
                "buttons": [{
                        extend: 'pdf',
                        footer: true,
                        pageSize: 'A4',
                        //orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6],

                        }
                    },
                    {
                        extend: 'excel',
                        footer: true,
                        pageSize: 'A4',
                        //orientation: 'landscape',
                        pageMargins: [40, 60, 40, 60],
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6],
                            stripHtml: false,
                        }
                    },
                ],
            });
        }
        $('.js-municipality').change(function() {
            municipality = $('.js-municipality').val();
            loadGPWard_1(municipality);
            // console.log('on change municipality:'+municipality);   
        });

        function loadGPWard_1(municipality) {
            $('.js-wards').empty().append('<option value="">-- Select --</option>');
            loadwards1(municipality, '../api/gpward/', '.js-wards');
        }

        function loadwards1(municipality, path, selectInputClass) {
            var selectedVal = municipality;
            if (selectedVal == -1) {
                return;
            }
            // alert(path +'1/'+ selectedVal);
            $.ajax({
                type: 'GET',
                url: path + '1/' + selectedVal,
                success: function(datas) {
                    if (!datas || datas.length === 0) {
                        //alert("sucess with 0 data");
                        return;
                    }
                    //alert('success url:'paths);
                    for (var i = 0; i < datas.length; i++) {
                        $(selectInputClass).append($('<option>', {
                            //value: datas[i].name,
                            value: datas[i].id,
                            text: datas[i].name,
                            id: datas[i].id
                        }));
                    }
                },
                error: function(ex) {
                    //alert('error url:'paths);
                }
            });
        }
        $('.modalEncloseClose').click(function() {
            $('.encolser_modal').modal('hide');
        });
        $(document).on('click', '.find_applicant', function() {
            var val = $(this).val();
            var array = val.split("_");
            var grievance_id = array[0];
            var grievance_mobile_no = array[2];
            var data = {
                '_token': '{{ csrf_token() }}',
                'grievance_id': grievance_id,
                'grievance_mobile_no': grievance_mobile_no
            };
            redirectPost('{{ route('cmo-grievance-find1') }}', data, 'post');
        });
        $(document).on('click', '.grivance_tag_applicant', function() {
            var val = $(this).val();
            var array = val.split("_");
            var grievance_id = array[0];
            var grievance_mobile_no = array[2];
            var data = {
                '_token': '{{ csrf_token() }}',
                'grievance_id': grievance_id
            };
            redirectPost('{{ route('cmo-grievance-applicant-tag1') }}', data, 'post');
        });
        tableLoaded();
        $(document).on('click', '.mapbos', function() {
            var val = $(this).val();
            var array = val.split("_");
            var grievance_id = array[0];
            var dist_code = array[3];
            var data = {
                'grievance_id': grievance_id
            };
            $.ajax({
                url: '{{ url('cmo-mapbosget') }}',
                data: data,
                type: "GET",
                success: function(response) {
                    $('#dist-name').text(response.district_name);
                    $('.dist_code').val(dist_code);
                    $('#grievance_id').val(grievance_id);
                    $('#gri_id_div').text(response.grievance_data.grievance_id);
                    $('#gri_no_div').text(response.grievance_data.grievance_no);
                    $('#appli_name_div').text(response.grievance_data.applicant_name);
                    $('#con_div').text(response.grievance_data.pri_cont_no);
                    $('#age_div').text(response.grievance_data.applicant_age);
                    $('#disc_div').text(response.grievance_data.grievance_description);
                    $('#mapbos').modal('show');
                },
                error: function() {}
            });
        });
        $('#mapping_type').on('change', function() {
            var mapping_type = $(this).val();
            var dist_code = $('.dist_code').val();
            // $('#mun_div').hide();
            // $('#municipality').html('');
            if (mapping_type) {
                $('#mapping_type_error').text('');
                $('#blk_sub_value_error').text('');
                // $('#municipality_error').text('');
                $('#submitButton').show();
                $('#blk_sub_div').show();
                var label = (mapping_type == 1) ? 'Block' : 'Subdivision';
                var button = 'Map to ' + label;
                $('#submitButton').text(button);
                $('#blk_sub_txt').html(`${label}<span style="color:#cc0000;">*</span>`);
                $.ajax({
                    url: '{{ url('cmo-getblksublist') }}',
                    method: 'GET',
                    data: {
                        dist_code: dist_code,
                        mapping_type: mapping_type
                    },
                    success: function(response) {
                        let options = '<option value="">-- Select --</option>';
                        $.each(response, function(index, item) {
                            options += '<option value="' + item.id + '">' + item
                                .name + '</option>';
                        });
                        $('#blk_sub_value').html(options);
                    },
                    error: function() {}
                });

            } else {
                $('#blk_sub_div').hide();
                $('#blk_sub_value').html('');
                $('#submitButton').hide();
            }
        });
        $('#blk_sub_value').on('change', function() {
            $('#blk_sub_value_error').text('');
        });
        // $('#blk_sub_value').on('change', function() {
        //     var mapping_type = $('#mapping_type').val();
        //     var blk_sub_value = $(this).val();
        //     if (blk_sub_value && mapping_type == 2) {
        //         $('#municipality_error').text('');
        //         $('#mun_div').show();
        //         $.ajax({
        //             url: '{{ url('getMunicipalityList') }}',
        //             method: 'GET',
        //             data: {
        //                 subdivision_id: blk_sub_value
        //             },
        //             success: function(response) {
        //                 let options = '<option value="">-- Select --</option>';
        //                 $.each(response, function(index, item) {
        //                     options += '<option value="' + item.id + '">' + item.name + '</option>';
        //                 });
        //                 $('#municipality').html(options);
        //             },
        //             error: function() {}
        //         });
        //     } else {
        //         $('#mun_div').hide();
        //         $('#municipality').html('');
        //     }
        // });
        // $('#municipality').on('change', function() {
        //     $('#municipality_error').text('');
        // });
        $('#mapUserForm').on('submit', function(e) {
            e.preventDefault();
            $.confirm({
                title: '',
                type: 'red',
                typeAnimated: true,
                buttons: {
                    yes: {
                        text: 'Yes',
                        btnClass: 'btn-red',
                        action: function() {
                            $('.error-message').text('');
                            $('select').removeClass('is-invalid');
                            var isValid = true;
                            var mapping_type = $('#mapping_type').val();
                            var blk_sub_value = $('#blk_sub_value').val();
                            // var municipality = $('#municipality').val();
                            if (!mapping_type) {
                                $('#mapping_type').addClass('is-invalid');
                                $('#mapping_type_error').text('This field is required');
                                isValid = false;
                            }
                            if ($('#blk_sub_div').is(':visible') && !blk_sub_value) {
                                $('#blk_sub_value').addClass('is-invalid');
                                $('#blk_sub_value_error').text('This field is required');
                                isValid = false;
                            }
                            // if ($('#mun_div').is(':visible') && !municipality) {
                            //     $('#municipality').addClass('is-invalid');
                            //     $('#municipality_error').text('This field is required');
                            //     isValid = false;
                            // }
                            if (isValid) {
                                $('#submitButton').hide();
                                $('#btn_encolser_loader').show();
                                var grievance_id = $('#grievance_id').val();
                                var data = {
                                    '_token': '{{ csrf_token() }}',
                                    'mapping_type': mapping_type,
                                    'blk_sub_value': blk_sub_value,
                                    // 'municipality':municipality,
                                    'grievance_id': grievance_id,
                                };
                                $.ajax({
                                    url: '{{ url('cmo-mapbospost') }}',
                                    method: 'POST',
                                    data: data,
                                    success: function(response) {
                                        if (response.return_status == 1) {
                                            $('#btn_encolser_loader').hide();
                                            $('#mapbos').modal('hide');
                                            $.confirm({
                                                title: response.title,
                                                type: response.type,
                                                icon: response.icon,
                                                content: response
                                                    .return_msg,
                                                buttons: {
                                                    Ok: function() {
                                                        window
                                                            .location
                                                            .reload();
                                                    }
                                                }
                                            });
                                        } else if (response.return_status ==
                                            2) {
                                            $('#btn_encolser_loader').hide();
                                            $.confirm({
                                                title: response.title,
                                                type: response.type,
                                                icon: response.icon,
                                                content: response
                                                    .return_msg,
                                                buttons: {
                                                    Ok: function() {
                                                        $('#mapbos')
                                                            .modal(
                                                                'hide'
                                                                );
                                                    }
                                                }
                                            });
                                        } else {
                                            $('#btn_encolser_loader').hide();
                                            $('#submitButton').show();
                                            $('#blk_sub_value').addClass(
                                                'is-invalid');
                                            $('#blk_sub_value_error').text(
                                                response.return_msg);
                                        }
                                    },
                                    error: function() {}
                                });
                            }
                        }
                    },
                    close: function() {}
                }
            });

        });
        $('#mapbos').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#blk_sub_div').hide();
            $('#submitButton').hide();
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
        $('body').append(form);
        form.submit();
    }
</script>
