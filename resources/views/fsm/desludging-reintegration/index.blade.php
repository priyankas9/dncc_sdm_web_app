<!-- Last Modified Date: 19-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2022) -->
@extends('layouts.dashboard')
@push('style')
@endpush
@section('title', $page_title)
@section('content')
<div class="card">
    <div class="card-body">
        <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                        <th>BIN</th>
                        <th>House Number</th>
                        <th>Area Name</th>
                        <th>Road Number</th>
                        <th>Owner Name</th>
                        <th>Owner Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div><!-- /.box-body -->
    <!-- Bootstrap Modal -->
       <form id="confirmEmptyingForm" method="POST" action="{{ route('schedule.confirm') }}" style="display: none;">
    @csrf

    <input type="hidden" name="action_type" >
    </form>
    @stop
    @push('scripts')
    <!-- Include SweetAlert2 from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            var dataTable = $('#data-table').DataTable({
                bFilter: false,
                processing: true,
                serverSide: true,
                scrollCollapse: true,
                order: [
                    [0, 'asc']
                ], // Add this line to set the default order by the next_emptying_date column
                ajax: {
                    url: '{!! url("fsm/desludging-reintegration/data") !!}',
                },
                columns: [{
                        data: 'bin',
                        name: 'bin'
                    },
                    {
                        data: 'house_number',
                        name: 'house_number'
                    },
                   
                    {
                        data: 'house_locality',
                        name: 'house_locality'
                    },
                    {
                        data: 'road_code',
                        name: 'road_code'
                    },
                    {
                        data: 'owner_name',
                        name: 'owner_name'
                    },
                    {
                        data: 'owner_contact',
                        name: 'owner_contact'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
            resetDataTable(dataTable);

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                dataTable.draw();
            });

            $("#export").on("click", function(e) {
                e.preventDefault();
                var searchData = $('input[type=search]').val();
                window.location.href = "{!! url('fsm/desludging-schedule/export?searchData=') !!}" + searchData;
            });
             $(document).on('click', '.confirm-emptying-btn ', function (e) {
                e.preventDefault();
                const form = document.getElementById('confirmEmptyingForm');
               
                form.querySelector('[name="action_type"]').value = $(this).data('action_type');
                form.submit();
            });

        });
    </script>
    @endpush