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
      <form method="post" id="register_form"  class="submit-once">
        {{ csrf_field() }}

        <div class="tab-content" style="margin-top:16px;">
          <div class="tab-pane active" id="personal_details">
            <!-- Card with your design -->
            <div class="card" id="res_div">
              <div class="card-header card-header-custom">
                <h4 class="card-title mb-0"><b>Sarasori Mukhyamantri (CMO Grievance) Entry List</b></h4>
              </div>
              <div class="card-body" style="padding: 20px;">
                <!-- Alert Messages -->
                <div class="alert-section">
                  @if ( ($message = Session::get('success')) && ($id =Session::get('id')))
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ $message }} with Application ID: {{$id}}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if ($message = Session::get('error') )
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  @if(count($errors) > 0)
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul>
                      @foreach($errors as $error)
                      <li><strong> {{ $error }}</strong></li>
                      @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">×</button>
                  </div>
                  @endif

                  <div class="alert print-error-msg" style="display:none;" id="errorDivMain">
                    <button type="button" class="close" aria-label="Close" onclick="closeError('errorDivMain')">
                      <span aria-hidden="true">&times;</span>
                    </button>
                    <ul></ul>
                  </div>
                </div>

          
               
                <!-- DataTable Section -->
                <div class="table-container">
                  <div class="table-responsive">
                    <table id="example" class="display data-table" cellspacing="0" width="100%">
                      <thead class="table-header-spacing">
                        <th>Grievance ID</th>
                        <th>Caller Name</th>
                        <th>Caller Mobile No</th>
                        <th>CMO Received Date</th>
                        <th>Action</th>
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
<script>
    $(document).ready(function() {
      
        $('#loadingDi').hide();
        $('#search_btn').removeAttr('disabled');
        tableLoaded();
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
                    url: "{{ url('cmo-op_entryList1') }}",
                    type: "get",
                    data: function(d) {
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
            var scheme_id = array[1];
            var grievance_mobile_no = array[2];
            var data = {
                '_token': '{{ csrf_token() }}',
                'grievance_id': grievance_id,
                'scheme_id': scheme_id,
                'grievance_mobile_no': grievance_mobile_no
            };
            redirectPost('{{ url('oap-wcd') }}', data, 'get');
        });
        tableLoaded();
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

@endpush