@extends('layouts.dashboard')
@section('title', 'Supervisory Assessment')
@section('content')
<div class="card border-0">
    <div class="card-header">
    <a href="{{ action('Fsm\SupervisoryAssessmentController@download') }}" id="export" class="btn btn-info">{{ __('Export to CSV') }}
    </a>
     <a href class="btn btn-info float-right" data-toggle="collapse" data-target="#collapseFilter"
                        aria-expanded="false" aria-controls="collapseFilter">  {{ __('Show Filter') }}</a>

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
                                                    <label for="owner_name" class="col-md-2 col-form-label ">  {{ __('Owner Name') }}</label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="owner_name" placeholder= "{{ __('Owner Name') }}" />
                                                    </div>
                                                    <label for="application_id" class="col-md-2 col-form-label ">{{ __('Application ID') }}
                                                    </label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="application_id" placeholder= "{{ __('Application ID') }}"/>
                                                    </div>
                                                    <label for="holding_num" class="col-md-2 col-form-label ">{{ __('Holding Number') }}
                                                    </label>
                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" id="holding_num" placeholder= "{{ __('Holding Number') }}"/>
                                                    </div>
                                                </div>
                                                <div class="card-footer text-right">
                                                    <button type="submit" class="btn btn-info ">{{ __('Filter') }}</button>
                                                    <button type="reset" id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button>
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
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Application ID') }}</th>
                        <th>{{ __('Area Name') }}</th>
                        <th>{{ __('Owner Name') }}</th>
                        <th>{{ __('Advance Paid Amount') }}</th>
                        <th>{{ __('Confirmed Emptying Date') }}</th>
                        <th>{{ __('Actions') }}</th>
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
                data: 'house_locality',
                name: 'house_locality'
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
        e.preventDefault(); // use 'e' not 'event' unless defined globally
        var form = $(this).closest("form");

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
                // Don't put Swal.fire success here — page will reload.
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
     $("#export").on("click", function(e) {

                            e.preventDefault();
                            var searchData = $('input[type=search]').val();
                            // var trtpltid = $('#trtpltid').val();
                           owner_name = $('#owner_name').val();
                            application_id = $('#application_id').val();
                            holding_num = $('#holding_num').val();
                            window.location.href = "{!! url('fsm/supervisory-assessment/export?searchData=') !!}" + searchData +
                                "&owner_name=" + owner_name +
                                "&application_id=" + application_id + "&holding_num=" +
                                holding_num 
                        });
});
</script>
@endpush