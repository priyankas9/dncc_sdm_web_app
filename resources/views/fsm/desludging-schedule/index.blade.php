<!-- Last Modified Date: 19-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2022) -->
@extends('layouts.dashboard')
@push('style')

@endpush
<style type="text/css">
    .dataTables_filter {
        display: none;
    }
 /* Fullscreen overlay */
 #loader-overlay {
        display: none; /* Hidden by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6); /* Semi-transparent black */
        z-index: 9999; /* High priority */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Loader content */
    .loader-content {
        text-align: center;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px;
    }

    /* Spinner icon */
    .fa-spinner {
        font-size: 30px;
        margin-bottom: 10px;
    }
    .btn-label {position: relative;left: -12px;display: inline-block;padding: 6px 12px;background: rgba(0,0,0,0.15);border-radius: 3px 0 0 3px;}
.btn-labeled {padding-top: 0;padding-bottom: 0;}
.btn { margin-bottom:10px; }
.static-ping {
  position: relative;
}

.static-ping::before {
  content: "";
  position: absolute;
  top: -4px;
  left: -4px;
  width: 10px;
  height: 10px;
  background-color:rgb(8, 182, 245); /* or whatever color you like */
  border-radius: 50%;
  z-index: 2;
}


</style>

@section('title', $page_title)
@section('content')

<div class="card">
    <div class="card-header">
        <a href="#" id="regenerate-btn" class="btn btn-info">Regenerate Desludging Schedule</a>
        <a href="#" id="export" class="btn btn-info">Export to CSV</a>
    </div><!-- /.card-header -->
    <div id="loader-overlay" style="display: none;">
    <div class="loader-content">
        <i class="fa fa-spinner fa-spin"></i>
        <p>Loading...</p>
    </div>
    </div>

    <div class="card-body">
        <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                        <th>BIN</th>
                        <th>Containment ID</th>
                        <th>House Number</th>
                        <th>Area Name</th>
                        <th>Road Number</th>
                        <th>Owner Name</th>
                        <th>Owner Contact</th>
                        <th>Next Emptying Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div><!-- /.box-body -->
    <form id="confirmEmptyingForm" method="POST" action="{{ route('schedule.confirm') }}" style="display: none;">
    @csrf
    <input type="hidden" name="ward">
    <input type="hidden" name="containment_id">
    <input type="hidden" name="bin">
    <input type="hidden" name="owner_name">
    <input type="hidden" name="owner_contact">
    <input type="hidden" name="next_emptying_date">
    <input type="hidden" name="owner_gender">
    <input type="hidden" name="road_code">
    <input type="hidden" name="household_served">
    <input type="hidden" name="population_served">
    <input type="hidden" name="toilet_count">
    <input type="hidden" name="action_type" >
    </form>
    <!-- Bootstrap Modal -->
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
                    [7, 'asc']
                ], // Add this line to set the default order by the next_emptying_date column
                ajax: {
                    url: '{!! url("fsm/desludging-schedule/data") !!}',
                },
                columns: [{
                        data: 'bin',
                        name: 'bin'
                    },
                   {
                        data: 'id',
                        name: 'id'
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
                        data: 'next_emptying_date',
                        name: 'next_emptying_date',
                        render: function(data, type, row) {
                            if (!data) return '';
                            const date = new Date(data);
                            const day = date.toLocaleDateString('en-US', { weekday: 'long' }); // e.g., Monday
                            const formattedDate = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); // e.g., 22 Apr 2024
                            return `${formattedDate},${day}`;
                        }
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
           
            $(document).on('click', '.confirm-emptying-btn , .reschedule-emptying-btn', function (e) {
                e.preventDefault();
                const form = document.getElementById('confirmEmptyingForm');
                form.querySelector('[name="ward"]').value = $(this).data('ward');
                form.querySelector('[name="containment_id"]').value = $(this).data('containment_id');
                form.querySelector('[name="bin"]').value = $(this).data('bin');
                form.querySelector('[name="owner_name"]').value = $(this).data('owner_name');
                form.querySelector('[name="owner_contact"]').value = $(this).data('owner_contact');
                form.querySelector('[name="owner_gender"]').value = $(this).data('owner_gender');
                form.querySelector('[name="next_emptying_date"]').value = $(this).data('next_emptying_date');
                form.querySelector('[name="road_code"]').value = $(this).data('road_code');
                form.querySelector('[name="population_served"]').value = $(this).data('population_served');
                form.querySelector('[name="household_served"]').value = $(this).data('household_served');
                form.querySelector('[name="toilet_count"]').value = $(this).data('toilet_count');
                form.querySelector('[name="action_type"]').value = $(this).data('action_type');
                form.submit();
            });
          
            $('#regenerate-btn').on('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            // Show the loader overlay
            $('#loader-overlay').show();
            $.ajax({
                url: "{{ action('Fsm\DesludgingScheduleController@set_emptying_date') }}",
                type: "GET",
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to regenerate the next emptying date. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    console.error('Error:', error);
                },
                complete: function() {
                    // Hide the loader overlay after AJAX request completes
                    $('#loader-overlay').hide();
                }
            });
            });
       
        //disagreeemptying
        $(document).on('click', '.btn-unconfirm-emptying', function() {
            var bin = $(this).data('bin');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to removed from desludging schedule ?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If confirmed, send AJAX request to update status in the backend
                        $.ajax({
                            url: 'desludging-schedule/disagreeEmptying/' + bin, // URL for the POST request
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}' // CSRF token for security
                            },
                            success: function(response) {
                                console.log('heyy',response); // Log the response for debugging
                                // Show success message using SweetAlert2
                                Swal.fire(
                                    'Success',
                                    'You have agreed to be removed from desludging schedule',
                                    'success'
                                ).then(() => {
                                    // Reload the page after the SweetAlert confirmation
                                    location.reload(); // Refresh the entire page
                                });
                            },
                            error: function(xhr, status, error) {
                                // Display error message if the request fails
                                Swal.fire(
                                    'Error',
                                    'There was an issue processing your request: ' + xhr.responseText,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

        });
    </script>
    @endpush