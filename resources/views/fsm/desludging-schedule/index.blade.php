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

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="confirmEmptyingModal" tabindex="-1" role="dialog" aria-labelledby="confirmEmptyingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="confirmEmptyingModalLabel"> Confirm Emptying Date</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
         <form id="emptyingFormContainer" method="POST">
            @csrf
            <div class="modal-body" id="emptyingForm" style="display: none;">
                        <input type="hidden" id="binId" name="bin">
                        <!-- Owner Details Section -->
                        <div id="ownerDetailsSection">
                            <h5 class="mb-3">Owner Details</h5>
                            <div class="form-group row">
                                {!! Form::label('customer_name', 'Owner Name', ['class' => 'col-sm-4 col-form-label']) !!}
                                <div class="col-sm-8">
                                    {!! Form::text('customer_name', null, ['class' => 'form-control', 'placeholder' => 'Owner Name']) !!}
                                </div>
                            </div>
                            <div class="form-group row">
                                {!! Form::label('customer_contact', 'Owner Contact (Phone)', ['class' => 'col-sm-4 col-form-label']) !!}
                                <div class="col-sm-8">
                                    {!! Form::text('customer_contact', null, ['class' => 'form-control', 'placeholder' => 'Owner Contact (Phone)']) !!}
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Applicant Details Section -->
                        <h5 class="mb-3 d-flex align-items-center justify-content-between">
                            Applicant Details
                            <div class="form-check ml-auto">
                                <input type="checkbox" class="form-check-input" id="sameAsOwnerCheckbox">
                                <label class="form-check-label" for="sameAsOwnerCheckbox" style="font-size: 0.85rem;">Same as Owner</label>
                            </div>
                        </h5>
                        <div class="form-group row">
                            {!! Form::label('applicant_name', 'Applicant Name', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('applicant_name', null, ['class' => 'form-control', 'placeholder' => 'Applicant Name']) !!}
                            </div>
                        </div>
                        <div class="form-group row">
                            {!! Form::label('applicant_gender', 'Applicant Gender', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::select('applicant_gender', array("Male"=>"Male","Female"=>"Female","Other" =>"Other"),null, ['class' => 'form-control', 'placeholder' => 'Applicant Gender']) !!}
                            </div>
                        </div>
                        <div class="form-group row">
                            {!! Form::label('applicant_contact', 'Applicant Contact Number', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('applicant_contact', null, ['class' => 'form-control', 'placeholder' => 'Applicant Contact Number']) !!}
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Application Details Section -->
                        <h5 class="mb-3">Application Details</h5>
                        <div class="form-group row">
                            {!! Form::label('supervisory_assessment_date', 'Supervisory Assessment Date', ['class' => 'col-sm-4 col-form-label']) !!}
                            <div class="col-sm-8">
                                {!! Form::date('supervisory_assessment_date', null, ['class' => 'form-control', 'id' => 'supervisory_assessment_date', 'placeholder' => 'Supervisory Assessment Date']) !!}
                            </div>
                        </div>

                        <div class="form-group row">
                        {!! Form::label('proposed_emptying_date', 'Proposed Emptying Date', ['class' => 'col-sm-4 col-form-label']) !!}
                        <div class="col-sm-8">
                            {!! Form::date('proposed_emptying_date', null, ['class' => 'form-control', 'placeholder' => 'Proposed Emptying Date', 'id' => 'proposed_emptying_date']) !!}
                            <p id="generatedMessage" class="spinning-text" style="display: none; font-size: 14px; color: #18b1c9; margin-top: 5px;">
                            Since you have confirmed emptying, the system has generated the date. You cannot edit it.
                        </p>
                        </div>
                        </div>
                     
                    </div>
                    <div class="modal-footer" style="display: none; justify-content: flex-end;"" id="buttons">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
            <!-- Buttons -->
            <div id="iconbuttons" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn btn-labeled btn-success" id="showConfirmForm">
            <span class="btn-label"> <i class="fa-solid fa-check"></i></span> Confirm Emptying</button>
            <button type="button" class="btn btn-labeled btn-warning" id="btn-reschedule-emptying">
            <span class="btn-label"> <i class="fa-solid fa-clock"></i></span> Reschedule</button>
            <button type="button" class="btn btn-labeled btn-danger" id="disagreeEmptyingForm">
            <span class="btn-label"> <i class="fa-solid fa-xmark"></i></span> Disagree</button>
            </div>
        </form>
            </div>
        </div>
    </div>
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
                        name: 'next_emptying_date'
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

            $('#regenerate-btn').on('click', function(e) {
        e.preventDefault(); // Prevent default link behavior
        // Show the loader overlay
        $('#loader-overlay').show();
        $.ajax({
            url: "{{ action('Fsm\DesludgingScheduleController@setEmptyingDate') }}",
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
            $('#ownerDetailsSection').hide();
            // Toggle the Owner Details section based on the checkbox state
          
            // When the modal is shown
            $(document).on('click', '.btn-confirm-emptying', function() {
                var bin = $(this).data('bin');
                var nextEmptyingDate = $(this).data('next-emptying-date'); // Prefilled value (next emptying date)
                
                var owner_name = $(this).data('owner_name');
                var owner_contact = $(this).data('owner_contact');
                $('#binId').val(bin); // Set bin in hidden input field
                $('#proposed_emptying_date').val(nextEmptyingDate);
                $('#customer_name').val(owner_name);
                $('#customer_contact').val(owner_contact);
                // Prefill the proposed emptying date
                $('#proposed_emptying_date').attr('min', nextEmptyingDate); // Set max date to prefilled next emptying date

                $('#confirmEmptyingModal').modal('show');
                if (nextEmptyingDate) {
                // Convert to Date object and subtract one day
                var maxDate = new Date(nextEmptyingDate);
                maxDate.setDate(maxDate.getDate() - 1); // Subtract one day
                // Format date as YYYY-MM-DD
                var formattedDate = maxDate.toISOString().split('T')[0];
                // Set max attribute
                $('#supervisory_assessment_date').attr('max', formattedDate);
            }
            });
          
            $('#sameAsOwnerCheckbox').change(function() {
                if ($(this).is(':checked')) {
                    $('#ownerDetailsSection').show(); // Hide when checked
                } else {
                    $('#ownerDetailsSection').hide(); // Show when unchecked
                }
            });
            // Handle the form submission with validation
            $('#emptyingFormContainer').on('submit', function(e) {
                e.preventDefault();
                var prefilledEmptyingDate = $('#proposed_emptying_date').attr('max'); 
                var userSelectedDate = $('#proposed_emptying_date').val(); 
                
                if (userSelectedDate < prefilledEmptyingDate) {
                    Swal.fire({
                        title: 'Invalid Date',
                        text: 'The proposed emptying date must be after the prefilled next emptying date.',
                        icon: 'error'
                    });
                    return;
                }
                // Serialize form data manually
                var formData = $('form').serialize();
                $.ajax({
                    url: 'desludging-schedule/submit-application',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success'
                            }).then(function() {
                                $('#confirmEmptyingModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessages = '';

                            $.each(errors, function(key, value) {
                                errorMessages += value + '<br>';
                            });

                            Swal.fire({
                                title: 'Validation Error',
                                html: errorMessages,
                                icon: 'error'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                                icon: 'error'
                            });
                        }
                    }
                });
        });
        document.getElementById("showConfirmForm").addEventListener("click", function() {           
            let proposedDateInput = document.getElementById("proposed_emptying_date");
            let message = document.getElementById("generatedMessage");
            // Disable the input field
            proposedDateInput.readOnly = true;
            // Show the spinning text
            message.style.display = "block";
            document.getElementById("emptyingForm").style.display = "block";
            document.getElementById("buttons").style.display = "block";
            document.getElementById("iconbuttons").style.display = "none";
        });
        document.getElementById("btn-reschedule-emptying").addEventListener("click", function() {
        document.getElementById("emptyingForm").style.display = "block";
        document.getElementById("buttons").style.display = "block";
        document.getElementById("iconbuttons").style.display = "none";
        });
        document.getElementById("disagreeEmptyingForm").addEventListener("click", function() {
        document.getElementById("buttons").style.display = "none";
        });
            document.addEventListener("DOMContentLoaded", function() {
            const emptyingForm = document.getElementById("emptyingForm");
            const proposedDateMessage = document.getElementById("proposedDateMessage");
            const proposedDateInput = document.getElementById("proposedDateInput");
            document.getElementById("confirmEmptyingForm").addEventListener("click", function() {
            proposedDateMessage.style.display = "block"; // Show message
            proposedDateInput.readOnly = true; // Make date unchangeable
            emptyingForm.style.display = "block"; // Show form
        });
            document.getElementById("btn-reschedule-emptying").addEventListener("click", function() {
            proposedDateMessage.style.display = "none"; // Hide message
            proposedDateInput.readOnly = false; // Allow changing date
            emptyingForm.style.display = "block"; // Show form
        });
            document.getElementById("disagreeEmptyingForm").addEventListener("click", function() {
            emptyingForm.style.display = "none"; // Keep form hidden
        });
        }); 
        $('.btn-secondary[data-dismiss="modal"]').click(function () {
            $('#emptyingForm').hide();
            $('#buttons').hide();
            $('#iconbuttons').css('display', 'flex'); // Ensure buttons reappear properly
        });
        
        $('#disagreeEmptyingForm').on('click', function(e)  {
        var bin = $('#binId').val(); // Get the BIN value from the data attribute
        // Display confirmation dialog using SweetAlert2
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to remove from desludging schedule ?',
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
                                // Show success message using SweetAlert2
                                Swal.fire(
                                    'Success',
                                    'You have agreed to remove from desludging schedule',
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