@extends('layouts.dashboard')
@section('title', $page_title)


@section('content')

<div class="card border-0">
    <div class="card-header">
        @can('Add Pdf Data')
        <a href="{{ action('PdfGenerationController@create') }}" class="btn btn-info">Create New Pdf Data
            </a>
        @endcan
    </div><!-- /.card-header -->

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
            url: '{!! url("fsm/data") !!}',
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


    
});
</script>


@endpush