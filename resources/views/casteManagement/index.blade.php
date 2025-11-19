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
      <div class="tab-content" style="margin-top:16px;">
        <div class="tab-pane active" id="personal_details">
          <!-- Card with AdminLTE3 design -->
          <div class="card" id="res_div">
            <div class="card-header card-header-custom">
              <h4 class="card-title mb-0"><b>Applications List</b></h4>
            </div>
            <div class="card-body" style="padding: 20px;">
              <!-- Alert Messages -->
              <div class="alert-section">
                @if ( ($message = Session::get('success')) && ($id = Session::get('id')))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>{{ $message }} with Application ID: {{$id}}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if ($message = Session::get('error') )
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>{{ $message }}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(count($errors) > 0)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li><strong>{{ $error }}</strong></li>
                    @endforeach
                  </ul>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="alert alert-danger print-error-msg" style="display:none;" id="errorDivMain">
                  <button type="button" class="btn-close" aria-label="Close" onclick="closeError('errorDivMain')"></button>
                  <ul class="mb-0"></ul>
                </div>
              </div>

              <!-- Search Section -->
              <form name="casteManagement" id="casteManagement" method="post" action="{{url('casteManagement')}}" onsubmit="return validate();">
                {{ csrf_field() }}
                <div class="row mb-4">
                  <div class="col-md-12">
                    <div class="row ">
                      <div class="col-md-3 mb-3">
                        <label for="select_type" class="form-label required-field">Search Using</label>
                        <select class="form-select" name="select_type" id="select_type">
                          @foreach(Config::get('globalconstants.search_payment_status') as $key=> $search_type)
                          <option value="{{$key}}" @if($key==$fill_array['select_type']) selected @endif>{{$search_type}}</option>
                          @endforeach
                        </select>
                        <span id="error_select_type" class="text-danger small"></span>
                      </div>

                      <div class="col-md-3 mb-3" id="beneficiary_id_div">
                        <label for="ben_id" class="form-label required-field">
                          <span id="search_text">{{$fill_array['search_text']}}</span>
                        </label>
                        <input type="text" name="ben_id" id="ben_id" class="form-control"
                          onkeypress="if ( isNaN(String.fromCharCode(event.keyCode) )) return false;"
                          placeholder="Enter Beneficiary ID" value="{{$fill_array['ben_id']}}">
                        <span id="error_ben_id" class="text-danger small"></span>
                      </div>
                    </div>
                    <div class="col-md-3 mb-3">
                      <input class="btn btn-success" type="submit" name="btnSubmit" value="Search">

                    </div>
                  </div>
                </div>
              </form>

              @if(!empty($errorMsg))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ $errorMsg }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif

              @if(count($result) > 0)
              <!-- Results Section -->
              <div class="card mt-4" id="listing_div">
                <div class="card-header card-header-custom">
                  <h5 class="card-title mb-0">List of beneficiaries</h5>
                </div>
                <div class="card-body">
                  <div id="loadingDiv" class="text-center">
                    <div class="spinner-border" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="example" class="table table-bordered table-striped data-table">
                      <thead>
                        <tr>
                          <th>Beneficiary ID</th>
                          <th>Beneficiary Name</th>
                          <th>Mobile No.</th>
                          <th>Application Id</th>
                          <th>Message</th>
                          <th width="30%">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($result as $row)
                        <tr>
                          <td>{{$row['beneficiary_id']}}</td>
                          <td>{{$row['ben_fname']}}</td>
                          <td>{{$row['mobile_no']}}</td>
                          <td>{{$row['application_id']}}</td>
                          <td>{{$row['msg']}}</td>
                          <td class="text-center">
                            @if($row['can_update_edit']==1)
                            <a class="btn btn-info btn-sm table-action-btn" href="{{url('changeCaste')}}?id={{$row['beneficiary_id']}}&is_faulty={{intval($row['is_faulty'])}}&caste_change_type=1">
                              <i class="fas fa-edit"></i> Caste Info Change
                            </a>
                            @endif

                            @if($row['can_update_switch']==1)
                            <a class="btn btn-danger btn-sm table-action-btn mb-2" href="{{url('changeCaste')}}?id={{$row['beneficiary_id']}}&is_faulty={{intval($row['is_faulty'])}}&caste_change_type=2">
                              <i class="fas fa-exchange-alt"></i> Caste Change
                            </a>
                            @endif
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('#loadingDiv').hide();
    $('.sidebar-menu li').removeClass('active');
    $('.sidebar-menu #lb-caste').addClass("active");
    $('.sidebar-menu #caste_search').addClass("active");

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
  });

  function validate() {
    var error_select_type = '';
    var error_ben_id = '';

    // Reset errors
    $('#error_select_type, #error_ben_id').text('');
    $('#select_type, #ben_id').removeClass('is-invalid');

    if ($.trim($('#select_type').val()).length == 0) {
      error_select_type = 'Please Select';
      $('#error_select_type').text(error_select_type);
      $('#select_type').addClass('is-invalid');
    }

    if ($.trim($('#ben_id').val()).length == 0) {
      error_ben_id = 'This field is required';
      $('#error_ben_id').text(error_ben_id);
      $('#ben_id').addClass('is-invalid');
    }

    if (error_select_type != '' || error_ben_id != '') {
      return false;
    }

    return true;
  }

  function closeError(divId) {
    $('#' + divId).hide();
  }
</script>
@endpush