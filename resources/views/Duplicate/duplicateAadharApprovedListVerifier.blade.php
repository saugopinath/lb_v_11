@extends('layouts.app-template-datatable')
@push('styles')
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
      padding: 10px;
      color: #555;
      font-size: 14px;
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

    .modal-confirm {
      color: #636363;
      width: 400px;
    }

    .modal-confirm .modal-content {
      padding: 20px;
      border-radius: 5px;
      border: none;
      text-align: center;
      font-size: 14px;
    }

    .modal-confirm .modal-header {
      border-bottom: none;
      position: relative;
    }

    .modal-confirm h4 {
      text-align: center;
      font-size: 26px;
      margin: 30px 0 -10px;
    }

    .modal-confirm .close {
      position: absolute;
      top: -5px;
      right: -2px;
    }

    .modal-confirm .modal-body {
      color: #999;
    }

    .modal-confirm .modal-footer {
      border: none;
      text-align: center;
      border-radius: 5px;
      font-size: 13px;
      padding: 10px 15px 25px;
    }

    .modal-confirm .modal-footer a {
      color: #999;
    }

    .modal-confirm .icon-box {
      width: 80px;
      height: 80px;
      margin: 0 auto;
      border-radius: 50%;
      z-index: 9;
      text-align: center;
      border: 3px solid #f15e5e;
    }

    .modal-confirm .icon-box i {
      color: #f15e5e;
      font-size: 46px;
      display: inline-block;
      margin-top: 13px;
    }

    .modal-confirm .btn,
    .modal-confirm .btn:active {
      color: #fff;
      border-radius: 4px;
      background: #60c7c1;
      text-decoration: none;
      transition: all 0.4s;
      line-height: normal;
      min-width: 120px;
      border: none;
      min-height: 40px;
      border-radius: 3px;
      margin: 0 5px;
    }

    .modal-confirm .btn-secondary {
      background: #c1c1c1;
    }

    .modal-confirm .btn-secondary:hover,
    .modal-confirm .btn-secondary:focus {
      background: #a8a8a8;
    }

    .modal-confirm .btn-danger {
      background: #f15e5e;
    }

    .modal-confirm .btn-danger:hover,
    .modal-confirm .btn-danger:focus {
      background: #ee3535;
    }

    .trigger-btn {
      display: inline-block;
      margin: 100px auto;
    }
  </style>
@endpush
@section('content')
  <!-- <div class="content-wrapper"> -->

    <section class="content">

      <div class="card card-default">
        <div class="card-body">

          <!-- Main Card -->
          <div class="card card-default">
            <div class="card-body" style="padding:5px; font-size:14px;">

              {{-- SUCCESS MESSAGE --}}
              @if ($message = Session::get('success'))
                <div class="row">
                  <div class="alert alert-success alert-dismissible fade show" style="margin:10px 30px;">
                    <strong>{{ $message }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                </div>
              @endif

              {{-- ERROR MESSAGE --}}
              @if ($error = Session::get('error'))
                <div class="row">
                  <div class="alert alert-danger alert-dismissible fade show" style="margin:10px 30px;">
                    <strong>{{ $error }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
                </div>
              @endif

              {{-- PRINT ERROR (AJAX) --}}
              <div class="alert alert-danger print-error-msg" id="errorDiv" style="display:none;">
                <button type="button" class="btn-close" onclick="closeError('errorDiv')"></button>
                <ul></ul>
              </div>

              <!-- TABLE -->
              <div class="table-responsive">
                <table id="example" class="table table-bordered table-striped w-100">

                  <thead style="font-size:12px;">
                    <tr>
                      <th>Aadhaar No.</th>
                      <th>Duplicate Count</th>
                      <th>Action</th>
                    </tr>
                  </thead>

                  <tbody style="font-size:14px;">
                    @if(count($data) > 0)
                      @foreach ($data as $key => $value)
                                      <?php
                        if (!empty($key))
                          $encrpted_aadhar_no = Crypt::encrypt($key);
                        else
                          $encrpted_aadhar_no = '';
                                                                ?>
                                      <tr>
                                        <td>********{{ substr($key, -4) }}</td>
                                        <td>{{ $value }}</td>
                                        <td>
                                          <a href="dedupAadhaarView?aadhar_no={{ $encrpted_aadhar_no }}" class="btn btn-primary btn-sm">
                                            De-duplicate
                                          </a>
                                        </td>
                                      </tr>
                      @endforeach
                    @else
                      <tr>
                        <td colspan="3" class="text-center">No Duplicate Record Found.</td>
                      </tr>
                    @endif
                  </tbody>

                </table>
              </div>

            </div><!-- card-body -->
          </div><!-- card -->

        </div><!-- card-body -->
      </div><!-- card -->

    </section>

  <!-- </div> -->

@endsection
@push('scripts')
  <script>
    $(document).ready(function () {
      $('.sidebar-menu li').removeClass('active');
      $('.sidebar-menu #lk-main').addClass("active");
      $('.sidebar-menu #dup_aadhar_approved').addClass("active");
      var sessiontimeoutmessage = '{{$sessiontimeoutmessage}}';
      var base_url = '{{ url('/') }}';
      $('#example').DataTable({
        "paging": true,
        "searchable": false,
        "paging": false,
        "ordering": false,
        "bFilter": false,
        "bInfo": true,
        "pageLength": 20
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
      }
      else {
        $("#" + divid).removeClass('alert-warning');
        $("#" + divid).addClass('alert-success');
      }
      if (Array.isArray(msg)) {
        $.each(msg, function (key, value) {
          $("#" + divid).find("ul").append('<li>' + value + '</li>');
        });
      }
      else {
        $("#" + divid).find("ul").append('<li>' + msg + '</li>');
      }
    }
    function closeError(divId) {
      $('#' + divId).hide();
    }
  </script>
@endpush