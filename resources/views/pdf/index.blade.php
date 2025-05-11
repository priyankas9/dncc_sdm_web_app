@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card border-0">
    <div class="card-header">
    <a href="{{ action('Pdf\PdfController@create') }}" class="btn btn-info">Create New Pdf Data
    </a>
    </div><!-- /.card-header -->
    <div class="modal fade" id="export-single-notice"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" >
          <div class="modal-dialog" role="document">
              <div class="modal-content">
                <form class="form-horizontal" id="pdf-form" action="#">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel1">Choose Subject for PDF Generation</h4>
                    </div>
                    <div class="modal-body">
                      <div class="form-group row">
                        <label for="bodys" class="col-md-3 col-form-label text-right">PDF Subject</label>
                        <div class="col-md-6">
                          {!! Form::select('body',$body, null, ['id' => 'body', 'class' => 'form-control chosen-select', 'multiple'=>'multiple']) !!}
                        </div>
                      </div>
                    </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-default" id="exportSinglePDF">Generate PDF</button>
                  </div>
                </form>
              </div>
          </div>
    </div>
    <div class="card-body">
        <div style="overflow: auto; width: 100%;">
                <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Unique Reference</th>
                        <th>Date of Notice</th>
                        <th>Subject</th>
                        <th>Body</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div><!-- /.card-body -->
</div> <!-- /.card -->
@stop
@push('scripts')
<script>
$(function() {
    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        ajax: {
           
            url: '{!! url("pdf/pdf/data") !!}',
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'unique_ref',
                name: 'unique_ref'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'subject',
                name: 'subject'
            },
            {
                data: 'paragraph',
                name: 'paragraph'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    }).on('draw', function() {
        $('.delete').on('click', function(e) {
            var form = $(this).closest("form");
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    )
                }
            })
        });
    });
    var selectedId = null; // Declare globally

        // When clicking a 'Generate PDF' button in table row
        $(document).on('click', '.generate-pdf-btn', function () {

            selectedId = $(this).data('id'); // Set selectedId from clicked button
            console.log("Selected ID: " + selectedId);
        });

        // When clicking the 'Generate PDF' button inside the modal
        $('#exportSinglePDF').on('click', function (e) {
            e.preventDefault();

            var bod = $('#body').val();
            var searchData = $('#searchData').val(); // optional

            if (!selectedId) {
                alert('No ID selected.');
                return;
            }

            // Redirect for PDF generation
            window.location.href = "/pdf/onsite-sanitation/singlepdf/" + selectedId;
        });


      $('#results_acs').select2({
          dropdownParent: $("#export-notice"),
          placeholder: 'Compliance Status',
          allowClear: true,
          width: '100%'
    });
    $('#bodys').select2({
          placeholder: 'Subject',
          allowClear: true,
          width: '100%'
    });
    $('#body').select2({
          placeholder: 'Subject',
          allowClear: true,
          width: '100%'
    });
});
</script>
@endpush