@extends('layouts.dashboard')
@section('title', 'Supervisory Assessment')
@section('content')
<div class="card border-0">
    <div class="card-header">
    <a href="{{ action('Fsm\SupervisoryAssessmentController@download') }}" class="btn btn-info">Export to CSV
    </a>
     <a href class="btn btn-info float-right" data-toggle="collapse" data-target="#collapseFilter"
                        aria-expanded="false" aria-controls="collapseFilter">Show Filter</a>

    </div><!-- /.card-header -->
    <div class="card-body">
          <div class="row">
                        <div class="col-12">
                            <div class="accordion" id="accordionFilter">
                                <div class="accordion-item">
                                    <div id="collapseFilter" class="collapse" aria-labelledby="filter"
                                        data-parent="#accordionFilter">
                                        <div class="accordion-body">
                                            <form class="form-horizontal" id="filter-form">
                                                <div class="form-group row">
                                                    <label for="owner_name" class="col-md-2 col-form-label ">Owner Name</label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="owner_name" placeholder= "Owner Name" />
                                                    </div>
                                                    <label for="application_id" class="col-md-2 col-form-label ">Application ID
                                                    </label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="application_id" placeholder= "Application ID"/>
                                                    </div>
                                                    <label for="holding_num" class="col-md-2 col-form-label ">Holding Number
                                                    </label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="holding_num" placeholder= "Holding Number"/>
                                                    </div>
                                                </div>
                                                <div class="card-footer text-right">
                                                    <button type="submit" class="btn btn-info ">Filter</button>
                                                    <button type="reset" id="reset-filter" class="btn btn-info">Reset</button>
                                                </div>
                                                <div class="clearfix"></div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>
        <div style="overflow: auto; width: 100%;">
                <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Application ID</th>
                        <th>Holding Number</th>
                        <th>Owner Name</th>
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
            data: function(d) {
                d.owner_name = $('#owner_name').val();
                d.application_id = $('#application_id').val();
                d.holding_num = $('#holding_num').val();
            }
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
                data: 'holding_number',
                name: 'holding_number'
            },
            {
                data: 'owner_name',
                name: 'owner_name'
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
     resetDataTable(dataTable);
     var owner_name = '',
        application_id = '',
        holding_num = '';
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        owner_name = $('#owner_name').val();
        application_id = $('#application_id').val();
        holding_num = $('#holding_num').val();
        dataTable.draw();
    });
});
</script>
@endpush