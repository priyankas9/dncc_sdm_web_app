@extends('layouts.dashboard')
@section('title', 'Supervisory Assessment')
@section('content')
<div class="card border-0">
    <div class="card-header">
    <a href="{{ action('Fsm\SupervisoryAssessmentController@download') }}" class="btn btn-info">Export to CSV
    </a>
    </div><!-- /.card-header -->
    <div class="card-body">
        <div style="overflow: auto; width: 100%;">
                <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Application ID</th>
                        <th>Advance Paid Amount</th>
                        <th>Confirm Emptying Date</th>
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
           
            url: '{!! url("fsm/supervisory-assessment/data") !!}',
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'application_id',
                name: 'application_id'
            },
            {
                data: 'advance_paid_amount',
                name: 'advance_paid_amount'
            },
            {
                data: 'confirmed_emptying_date',
                name: 'confirmed_emptying_date'
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