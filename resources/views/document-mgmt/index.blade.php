@extends('document-mgmt.base')
@section('action-content')
  <style>
    .select2 {
      width: 300px;
    }
  </style>
  <!-- Main content -->
  <section class="content">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-8">
            <h3 class="card-title">List of Documents</h3>
          </div>

          <div class="col-sm-4 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#documentModel">
              Add New Document
            </button>
          </div>

          <div class="clearfix mt-2"></div>

          <div class="col-sm-12">
            @if(session()->has('message'))
              <div class="alert alert-success mt-2">
                {{ session()->get('message') }}
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="card-body">

        <div id="example2_wrapper" class="col-md-12 dataTables_wrapper dt-bootstrap5">

          <div class="text-end mb-2" id="addButton" hidden>
            <a class="btn btn-primary" href="javascript:void(0)" onClick="addUpdateLevelForm(0)">Add Level</a>
          </div>

          <div class="text-center" id="loaderdiv" hidden>
            <img src="{{ asset('images/ZKZg.gif') }}" width="100" height="100" />
          </div>

          <div id="reportbody" class="mt-3">
            <table id="example" class="table table-bordered table-striped" width="100%">
              <input type="hidden" id="token" value="{{ csrf_token() }}">

              <thead class="table-dark">
                <tr>
                  <th width="6%">Id</th>
                  <th width="25%">Document Name</th>
                  <th width="25%">Document Type</th>
                  <th width="7%">Document Size</th>
                  <th width="6%">Group</th>
                  <th width="6%">Active Status</th>
                  <th width="6%">Is Profile Pic</th>
                  <th width="15%">Action</th>
                </tr>
              </thead>

              <tfoot>
                <tr>
                  <th>Id</th>
                  <th>Role Name</th>
                  <th>User Level</th>
                  <th>Parent Id</th>
                  <th>Entry Level</th>
                  <th>First Verifier</th>
                  <th>Is Active</th>
                  <th>Action</th>
                </tr>
              </tfoot>
            </table>

            <div class="row mt-3">
              <div class="col-sm-7">
                <div id="example2_paginate" class="dataTables_paginate paging_simple_numbers"></div>
              </div>
            </div>

          </div>
        </div>

      </div><!-- card-body -->
    </div><!-- card -->
  </section>


  <!--Add  Document Modal -->
  <div class="modal fade" id="documentModel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">

        <form class="form-horizontal" id="document_create" method="POST" action="#">
          <input type="hidden" id="edit_code">

          <div class="modal-header">
            <h5 class="modal-title" id="document_heading">Add New Document</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">

            <div class="submit_loader d-none">
              <img src="{{ asset('images/ZKZg.gif') }}" width="50" height="50">
            </div>

            <div class="alert alert-danger print-error-msg d-none">
              <ul></ul>
            </div>

            <!-- Document Name -->
            <div class="mb-3">
              <label for="doc_name" class="form-label required">Document Name</label>
              <input id="doc_name" type="text" class="form-control" name="doc_name" value="{{ old('doc_name') }}"
                required>

              @error('doc_name')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            <!-- Document Type -->
            <div class="mb-3">
              <label for="doc_type" class="form-label required">Document Type</label>
              <input id="doc_type" type="text" class="form-control" name="doc_type" value="{{ old('doc_type') }}"
                required>

              @error('doc_type')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            <!-- Size -->
            <div class="mb-3">
              <label for="doc_size_kb" class="form-label required">Max Size (KB)</label>
              <input id="doc_size_kb" type="number" class="form-control" name="doc_size_kb"
                value="{{ old('doc_size_kb') }}" required>

              @error('doc_size_kb')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            <!-- Document Group -->
            <div class="mb-3">
              <label for="doucument_group" class="form-label">Document Group</label>
              <select name="doucument_group[]" id="doucument_group" class="form-select select2" multiple>
                <option value="">-- NA --</option>

                @foreach(Config::get('constants.document_group') as $key => $val)
                  <option value="{{ $key }}" @if(old('doucument_group') == $key) selected @endif>
                    {{ $val }}
                  </option>
                @endforeach
              </select>
            </div>

          </div>

          <div class="modal-footer">
            <button type="submit" id="btn-scheme-submit" class="btn btn-primary">Add</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          </div>

        </form>

      </div>
    </div>
  </div>

  <!--Add Update Document Modal -->
@endsection

@push('scripts')
  <script>
    $(document).ready(function () {

      // RESET MODAL WHEN CLOSED
      $('#documentModel').on('hidden.bs.modal', function () {
        $("#document_create")[0].reset();

        // Remove error messages
        $(".text-danger").remove();

        $('#edit_code').val('');
        $('#doucument_group').val('').change();
        $('#document_heading').text('Add New Document');
        $('#btn-scheme-submit').text('Add');
      });

      // PURE jQuery FORM VALIDATION
      $("#document_create").on("submit", function (e) {
        e.preventDefault();

        let isValid = true;
        $(".text-danger").remove();  // clear previous errors

        const docName = $("#doc_name").val().trim();
        const docType = $("#doc_type").val().trim();
        const docSize = $("#doc_size_kb").val().trim();

        // Validate Document Name
        if (docName === "") {
          $("#doc_name").after('<span class="text-danger">Please enter doc name</span>');
          isValid = false;
        }

        // Validate Document Type
        if (docType === "") {
          $("#doc_type").after('<span class="text-danger">Please enter document type</span>');
          isValid = false;
        }

        // Validate Document Size
        if (docSize === "") {
          $("#doc_size_kb").after('<span class="text-danger">Please enter document size</span>');
          isValid = false;
        } else if (!/^[0-9]+$/.test(docSize)) {
          $("#doc_size_kb").after('<span class="text-danger">Document size must be integer</span>');
          isValid = false;
        }

        if (isValid) {
          document_save();
        }
      });

      fill_datatable();
    });


    // LOAD DATATABLE
    function fill_datatable() {
      table = $('#example').DataTable({
        dom: "Blfrtip",
        paging: true,
        pageLength: 10,
        lengthMenu: [[10, 20, 50, 80, 120, 150, 180, 500, 1000, 2000],
        [10, 20, 50, 80, 120, 150, 180, 500, 1000, 2000]],
        serverSide: true,
        deferRender: true,
        processing: true,
        bRetrieve: true,
        ordering: false,
        language: {
          "processing": '<img src="{{ asset('images/ZKZg.gif') }}" width="150px" height="150px"/>'
        },
        ajax: {
          url: "{{ route('getDocumentList') }}",
          type: "GET",
          data: { _token: "{{csrf_token()}}" }
        },
        columns: [
          { data: "id" },
          { data: "doc_name" },
          { data: "doc_type" },
          { data: "doc_size_kb" },
          { data: "doucument_group" },
          { data: "is_active" },
          { data: "is_profile_pic" },
          { data: "action" }
        ],
      });
    }


    // TOGGLE ACTIVE / PROFILE PIC
    function toggleActivate(id, type) {
      $.ajax({
        url: "{{route('documentToggleActivate')}}",
        type: 'POST',
        data: {
          document_id: id,
          action_type: type,
          _token: "{{csrf_token()}}"
        },
        success: function () {
          table.ajax.reload(null, false);
        },
        error: function (ex) {
          alert('Error: ' + ex);
        }
      });
    }


    // DELETE DOCUMENT
    function deleteDocument(id) {

      $.confirm({
        type: 'red',
        icon: 'fa fa-warning',
        title: 'Warning!!',
        content: 'Are you sure to delete this record?',
        buttons: {
          confirm: function () {
            $.ajax({
              url: "{{route('deleteDocument')}}",
              type: 'POST',
              data: {
                item_id: id,
                _token: "{{csrf_token()}}"
              },
              success: function () {
                table.ajax.reload(null, false);

                $.alert({
                  type: 'green',
                  icon: 'fa fa-check',
                  title: 'Success!!',
                  content: 'Document deleted successfully',
                });
              },
              error: function (ex) {
                alert('Error: ' + ex);
              }
            });
          },
          cancel: function () { }
        }
      });

    }


    // SAVE / UPDATE DOCUMENT
    function document_save() {

      let edit_code = $("#edit_code").val();
      let fd = new FormData();

      fd.append('doc_name', $('#doc_name').val());
      fd.append('doc_type', $('#doc_type').val());
      fd.append('doc_size_kb', $('#doc_size_kb').val());
      fd.append('doucument_group', $('#doucument_group').val());
      fd.append('_token', "{{ csrf_token() }}");

      let action_url = edit_code === ""
        ? "{{route('documentSave')}}"
        : "{{route('documentUpdate')}}";

      if (edit_code !== "") {
        fd.append('edit_code', edit_code);
      }

      $.ajax({
        type: 'POST',
        url: action_url,
        data: fd,
        processData: false,
        contentType: false,
        success: function (response) {

          $.alert({
            type: 'green',
            icon: 'fa fa-check',
            title: 'Success!!',
            content: response.msg
          });

          table.ajax.reload(null, false);
          $('#documentModel').modal("hide");
        },
        error: function (jqXHR, textStatus, errorThrown) {
          ajax_error(jqXHR, textStatus, errorThrown);
        }
      });
    }


    // UPDATE DOCUMENT
    function UpdateDocument(id) {

      $('#doucument_group').val('').change();

      $.ajax({
        type: 'POST',
        url: "{{route('editDocument')}}",
        data: {
          editId: id,
          _token: "{{ csrf_token() }}"
        },
        success: function (data) {

          $("#documentModel").modal('show');
          $('#document_heading').text('Update Document');
          $('#btn-scheme-submit').text('Update');

          $("#document_create")[0].reset();

          $("#doc_name").val(data.docs.doc_name);
          $("#doc_type").val(data.docs.doc_type);
          $("#doc_size_kb").val(data.docs.doc_size_kb);
          $("#edit_code").val(data.docs.id);

          if (data.docs.doucument_group !== "") {
            var cleaned = data.docs.doucument_group.slice(1, -1).split(",");
            $('#doucument_group').val(cleaned).change();
          }
        }
      });
    }

  </script>

@endpush