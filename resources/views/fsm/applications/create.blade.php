<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--Extend the main layout--}}
@extends('layouts.dashboard')

{{--Add sections for the main layout--}}
@section('title', 'Add Application')
{{--Add sections for the index layout--}}

{{--Include the layout inside the main content section--}}
@section('content')
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    {!! Form::open(['url' => route('application.store'), 'class' => 'form-horizontal', 'id' => 'create_application_form']) !!}
    @include('layouts.partial-form',["submitButtonText" => 'Save',"cardForm"=>true])
    {!! Form::close() !!}
@endsection
@php
   $isConfirm = session('action_type') === 'confirm';
  
@endphp
@push('scripts')
<script>
    function autoFillDetails() {
        $(document).ready(function() {
            if ($("input[name='autofill']:checked").val() === 'on') {
                $("input[name='applicant_name']").val($("input[name=customer_name]").val());
                $("#applicant_gender").val($("#customer_gender").val());
                $("input[name='applicant_contact']").val($("input[name=customer_contact]").val());
            } else {
                $("input[name='applicant_name']").val('');
                $("#applicant_gender").val('');
                $("input[name='applicant_contact']").val('');
            }
        });
    }

    function emptyAutoFields() {
        $('#containment_id').val('');
        $('#ward').val('');
        $('#customer_name').val('');
        $('#customer_gender').val('');
        $('#customer_contact').val('');
        $("input[name='applicant_name']").val('');
        $("#applicant_gender").val('');
        $("input[name='applicant_contact']").val('');
        $("input[name='applicant_name']").removeAttr('disabled');
        $("#applicant_gender").removeAttr('disabled');
        $("input[name='applicant_contact']").removeAttr('disabled');
        $("input[name='autofill']").prop('checked', false);
    }

    function onAddressChange() {
        emptyAutoFields();
        if ($('#bin').find(":selected").text() === 'Address Not Found') {
            $('#building-if-address').hide();
            $("#building-if-address :input").each(function () {
                $(this).attr("disabled", true);
            });
            $('#building-if-not-address').show();
            $("#building-if-not-address :input").each(function () {
                $(this).attr("disabled", false);
            });
            $("input[type='submit']").removeAttr('disabled');
        } else {
            $('#building-if-not-address').hide();
            $("#building-if-not-address :input").each(function () {
                $(this).attr("disabled", true);
            });
            $('#building-if-address').show();
            $("#building-if-address :input").each(function () {
                $(this).attr("disabled", false);
            });

            if ($('#bin').val() != '') {
                displayAjaxLoader();
                $.ajax({
                    url: "{{ route('application.get-building-details') }}",
                    data: {
                        "bin": $('#bin').val()
                    },
                    success: function (res) {
                        if (res.status === true) {
                            let containmentOptions = '';
                            res.containments.forEach(function (containment) {
                                containmentOptions += `<option value="${containment}">${containment}</option>`;
                            });

                            $('#customer_name').val(res.customer_name).attr('disabled', true);
                            $('#customer_gender').val(res.customer_gender).attr('disabled', true);
                            $('#customer_contact').val(res.customer_contact).attr('disabled', true);
                            $('#household_served').val(res.household_served).attr('disabled', true);
                            $('#population_served').val(res.population_served).attr('disabled', true);
                            $('#toilet_count').val(res.toilet_count).attr('disabled', true);
                            $('#ward').val(res.ward);

                             localStorage.setItem("selectedOwnerName", res.customer_name);
                            localStorage.setItem("selectedOwnerGender", res.customer_gender);
                            localStorage.setItem("selectedOwnerContact", res.customer_contact);
                            if (res.containments.length === 1) {
                                $('#containment_id').replaceWith(`
                                    <input id="containment_id" name="containment_id" class="form-control" value="${res.containments[0]}" readonly>
                                `);
                            } else {
                                $('#containment_id').replaceWith(`
                                    <select id="containment_id" name="containment_id" class="form-control">
                                        ${containmentOptions}
                                    </select>
                                `);
                            }

                            $("input[type='submit']").removeAttr('disabled');
                        } else if (res.status === false) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: "There is an ongoing application for this address!",
                            });
                            emptyAutoFields();
                            $("input[type='submit']").attr('disabled', 'disabled');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: "Error!",
                            });
                            emptyAutoFields();
                        }
                        removeAjaxLoader();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: err.responseJSON.error,
                        });
                        emptyAutoFields();
                        $("input[type='submit']").attr('disabled', 'disabled');
                    }
                });
            }
        }
    }

    $(document).ready(function() {
        // const today = new Date().toISOString().split('T')[0];
        // document.getElementById('proposed_emptying_date').setAttribute('min', today);
        let sessionBin = @json(session('bin'));
        let sessionRoad = @json(session('road_code'));

        let optionHtmlBIN = sessionBin
            ? `<option selected value="${sessionBin}">${sessionBin}</option>`
            : `<option selected></option>`;
            $('#bin').html(optionHtmlBIN).select2({
                    ajax: {
                        url: "{{ route('building.get-house-numbers-containments') }}",
                        data: function (params) {
                            return {
                                search: params.term,
                                road_code: $('#road_code').val(),
                                page: params.page || 1
                            };
                        },
                    },
                    placeholder: 'House Number / BIN',
                    allowClear: true,
                    closeOnSelect: true,
                    width: '100%'
                });

        let optionHtmlRoadcode = sessionRoad
        ? `<option selected value="${sessionRoad}">${sessionRoad}</option>`
        : `<option selected></option>`;
        $('#road_code').html(optionHtmlRoadcode).select2({
            ajax: {
                url: "{{ route('roadlines.get-road-names') }}",
                data: function (params) {
                    return {
                        search: params.term,
                        bin: $('#bin').val(),
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'Street Name / Street Code',
            allowClear: true,
            closeOnSelect: true,
            width: '100%'
        });

        if ('{{ old('address') }}' !== '') {
            $('#address').select2().val('{{ old('address') }}').trigger('change');
            onAddressChange();
        }

        $('#bin').on('change', onAddressChange);

        $('#create_application_form').on('submit', function (e) {
            $('#containment_id').removeAttr('disabled'); // Ensure the field is enabled for submission
        });

        var serviceProviderId = {{ Auth::user()->service_provider_id ?? 'null' }};
        var url = serviceProviderId 
            ? '{!! url("fsm/service-provider") !!}/' + serviceProviderId 
            : '{!! url("fsm/service-provider") !!}/0';
        
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (response) {
                        $('#service_provider_id').empty();
                        $('#service_provider_id').append('<option value="">Select a Service Provider</option>');
                        $.each(response, function (id, name) {
                            $('#service_provider_id').append('<option value="' + id + '">' + name + '</option>');
                        });
                         const selectedServiceProviderValue = localStorage.getItem("selectedServiceProviderValue");

                        // If a value is found in localStorage, set it as the selected option
                        if (selectedServiceProviderValue) {
                            $('#service_provider_id').val(selectedServiceProviderValue).trigger('change');
                        }
                    },
                    error: function (error) {
                        console.error('Error fetching service provider data:', error);
                    }
                });
        
    let tripData = {}; // global store

  flatpickr('.flatpickr-reschedule', {
    dateFormat: 'Y-m-d',
    allowInput: true,

    onChange: function(selectedDates, dateStr, instance) {
        if (instance.input.id === 'proposed_emptying_date') {
            // Check if a date was selected
            if (selectedDates.length > 0) {
                const selectedDate = selectedDates[0];
                const year = selectedDate.getFullYear();
                const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const day = String(selectedDate.getDate()).padStart(2, '0');
                const dateKey = `${year}-${month}-${day}`;
                
                // Check if the selected date is in tripData
                if (tripData[dateKey]) {
                    const { trips, is_holiday, is_weekend } = tripData[dateKey];
                    
                    if (is_holiday || is_weekend || trips === 0) {
                        // Clear the selection
                        instance.clear();
                        
                        // Show appropriate popup message
                        let message = '';
                        if (is_holiday) {
                            message = 'Cannot select a holiday date.';
                        } else if (is_weekend) {
                            message = 'Cannot select a weekend date.';
                        } else {
                            message = 'No trips available for this date.';
                        }
                        console.log(message);
                        // Use alert or a better notification system
                        alert(message);
                        return;
                    }
                }
                
                // If date is valid, restrict supervisory_assessment_date
                flatpickr("#supervisory_assessment_date").set('maxDate', selectedDate);
            } else {
                flatpickr("#supervisory_assessment_date").set('maxDate', null);
            }
        }
    },

    onReady: function (selectedDates, dateStr, instance) {
        if (instance.input.id === 'proposed_emptying_date') {
            // Inject legend at the top (same as before)
            const legendHTML = `
                <div class="flatpickr-legend" style="padding: 5px 8px; font-size: 12px; border-bottom: 1px solid #ccc;">
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; background-color:rgb(228, 173, 56); border-radius: 50%;"></span> Holiday
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; background-color: #cce5ff; border-radius: 50%;"></span> Weekend
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; background-color:rgb(245, 157, 130); border-radius: 50%;"></span> 1 Trip
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; background-color: #fff3cd; border-radius: 50%;"></span> 2 Trips
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; background-color: #d4edda; border-radius: 50%;"></span> 3+ Trips
                        </span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; background-color: #f8d7da; border-radius: 50%;"></span> 0 Trips (Unavailable)
                        </span>
                    </div>
                </div>
            `;
            const calendarContainer = instance.calendarContainer;
            calendarContainer.insertAdjacentHTML("afterbegin", legendHTML);
            fetchAndDisplayTrips(instance);
        }
    },

    onMonthChange: function (selectedDates, dateStr, instance) {
        if (instance.input.id === 'proposed_emptying_date') {
            fetchAndDisplayTrips(instance);
        }
    },

   onDayCreate: function (dObj, dStr, fp, dayElem) {
    const dateObj = dayElem.dateObj;
    if (!dateObj) return;

    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const dateStrKey = `${year}-${month}-${day}`;

    if (tripData.hasOwnProperty(dateStrKey)) {
        const { trips, is_holiday, is_weekend } = tripData[dateStrKey];

        // Clear previous styles
        dayElem.removeAttribute("style");
        dayElem.style.cursor = "pointer";

        // Set tooltip
        let tooltip = `Trips Available: ${trips}`;
        if (is_holiday) tooltip += " (Holiday)";
        if (is_weekend) tooltip += " (Weekend)";
        dayElem.setAttribute("title", tooltip);

        // Priority coloring: Holiday > Weekend > Trips
        if (is_holiday) {
            dayElem.style.backgroundColor = "rgb(228, 173, 56)";
            dayElem.style.color = "#000000";
        } else if (is_weekend) {
            dayElem.style.backgroundColor = "#cce5ff";
            dayElem.style.color = "#004085";
        } else if (trips === 0) {
            dayElem.style.backgroundColor = "#f8d7da";
            dayElem.style.color = "#721c24";
        } else if (trips === 1) {
            dayElem.style.backgroundColor = "rgb(245, 157, 130)";
            dayElem.style.color = "#856404";
        } else if (trips === 2) {
            dayElem.style.backgroundColor = "#fff3cd";
            dayElem.style.color = "#856404";
        } else {
            dayElem.style.backgroundColor = "#d4edda";
            dayElem.style.color = "#155724";
        }
        dayElem.style.borderRadius = "50%";

        // Add click event to invalid dates
       if (is_holiday || is_weekend || trips === 0) {
    dayElem.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let message = '';
        if (is_holiday) {
            message = 'Cannot select a holiday date.';
        } else if (is_weekend) {
            message = 'Cannot select a weekend date.';
        } else {
            message = 'No trips available for this date.';
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: message,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    });
    }

        }
    },

    disable: [
        function(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const dateKey = `${year}-${month}-${day}`;

            if (tripData[dateKey]) {
                const { trips, is_holiday, is_weekend } = tripData[dateKey];
                return is_holiday || is_weekend || trips === 0;
            }
            return false;
        }
    ]

    });

    // Supervisory assessment date picker initialized separately
    window.isConfirm = {{ $isConfirm ? 'true' : 'false' }};
    if (window.isConfirm === false) {
            flatpickr("#supervisory_assessment_date", {
                dateFormat: 'Y-m-d',
                allowInput: true
            });
        }

    function fetchAndDisplayTrips(instance) {
    const calendarContainer = instance.calendarContainer;
    const dayElements = calendarContainer.querySelectorAll(".flatpickr-day");
    if (dayElements.length === 0) return;

    const firstVisibleDay = new Date(dayElements[0].dateObj);
    firstVisibleDay.setDate(firstVisibleDay.getDate() - firstVisibleDay.getDay()); // start of week (Sunday)

    const lastVisibleDay = new Date(dayElements[dayElements.length - 1].dateObj);
    lastVisibleDay.setDate(lastVisibleDay.getDate() + (6 - lastVisibleDay.getDay())); // end of week (Saturday)

    const startDateFormattedYMD = firstVisibleDay.toISOString().slice(0, 10);
    const endDateFormattedYMD = lastVisibleDay.toISOString().slice(0, 10);

    // Optional UI update
    const formatDMY = (d) => `${String(d.getDate()).padStart(2, '0')}.${String(d.getMonth() + 1).padStart(2, '0')}.${d.getFullYear()}`;
    const displayTarget = document.getElementById("visible-range-display");
    if (displayTarget) {
        displayTarget.innerText = `Calendar Grid: ${formatDMY(firstVisibleDay)} - ${formatDMY(lastVisibleDay)}`;
    }

    $.ajax({
        url: "{{ route('schedule.tripsallocated.range') }}",
        type: 'POST',
        data: {
            start_date: startDateFormattedYMD,
            end_date: endDateFormattedYMD
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (response) {
            tripData = response;
            console.log("✅ Trip data loaded:", tripData);
            instance.redraw(); // re-trigger onDayCreate
        },
        error: function (xhr, status, error) {
            console.error("❌ Failed to fetch trip data:", error);
        }
    });
}
     $('#service_provider_id').on('change', function() {
    var selectedServiceProviderValue = $(this).val();
    localStorage.setItem("selectedServiceProviderValue", selectedServiceProviderValue);
});

// Store owner details when they change
$('#customer_name').on('change', function() {
    localStorage.setItem("selectedOwnerName", $(this).val());
});

$('#customer_gender').on('change', function() {
    localStorage.setItem("selectedOwnerGender", $(this).val());
});

$('#customer_contact').on('change', function() {
    localStorage.setItem("selectedOwnerContact", $(this).val());
});



if ($('.alert.alert-danger.alert-dismissible').length == 0) {
    // Only clear these if you want them to persist across multiple submissions
    localStorage.removeItem("selectedRoadCode");
    localStorage.removeItem("selectedOwnerName");
    localStorage.removeItem("selectedOwnerGender");
    localStorage.removeItem("selectedOwnerContact");
    localStorage.removeItem("selectedBINValue");
    localStorage.removeItem("selectedBINText");
    localStorage.removeItem("containment_id");
    localStorage.removeItem("selectedHouseholdServed");
    localStorage.removeItem("selectedPopulationServed");
    localStorage.removeItem("selectedServiceProviderText");
    localStorage.removeItem("selectedServiceProviderValue");
    localStorage.removeItem("selectedToiletCount");
} else { 
    // Retrieve values from localStorage and populate the form
    const selectedRoadCode = localStorage.getItem("selectedRoadCode");
    const selectedBINValue = localStorage.getItem("selectedBINValue");
    const selectedBINText = localStorage.getItem("selectedBINText");
    const selectedOwnerName = localStorage.getItem("selectedOwnerName");
    const selectedOwnerGender = localStorage.getItem("selectedOwnerGender");
    const selectedOwnerContact = localStorage.getItem("selectedOwnerContact");
    const selectedHouseholdServed = localStorage.getItem("selectedHouseholdServed");
    const selectedPopulationServed = localStorage.getItem("selectedPopulationServed");
    const selectedToiletCount = localStorage.getItem("selectedToiletCount");
    if (selectedRoadCode) {
        var roadCode = selectedRoadCode.split(" - ")[0];
        $('#road_code').val(selectedRoadCode); // Set road code from localStorage
    }
    $('#containment_id').prop('disabled', true);
    // Populate form fields with localStorage data
    if (selectedRoadCode) $('#road_code').val(selectedRoadCode);
    if (selectedBINValue) $('#bin').val(selectedBINValue);
    if (selectedOwnerName) $('#customer_name').val(selectedOwnerName).prop('disabled', true);
    if (selectedOwnerGender) $('#customer_gender').val(selectedOwnerGender).prop('disabled', true);
    if (selectedOwnerContact) $('#customer_contact').val(selectedOwnerContact).prop('disabled', true);
    if (selectedHouseholdServed) $('#household_served').val(selectedHouseholdServed).prop('disabled', true);
    if (selectedPopulationServed) $('#population_served').val(selectedPopulationServed).prop('disabled', true);
    if (selectedToiletCount) $('#toilet_count').val(selectedToiletCount).prop('disabled', true);
    // Update road code select2 with stored value
    optionHtmlRoadCode = selectedRoadCode 
        ? `<option value="${roadCode}" selected="selected">${selectedRoadCode}</option>` 
        : `<option selected=""></option>`;
    $('#road_code').prepend(optionHtmlRoadCode).select2({
        ajax: {
            url: "{{ route('roadlines.get-road-names') }}",
            data: function (params) {
                return {
                    search: params.term,
                    bin: $('#bin').val(),
                    page: params.page || 1
                };
            },
        },
        placeholder: 'Street Name / Street Code',
        allowClear: true,
        closeOnSelect: true,
        width: '100%',
    });
    // Update bin select2 with stored value
    optionHtmlBIN = selectedBINValue 
        ? `<option value="${selectedBINValue}" selected="selected">${selectedBINText}</option>` 
        : `<option selected=""></option>`;
    $('#bin').prepend(optionHtmlBIN).select2({
        ajax: {
            url: "{{ route('building.get-house-numbers-containments') }}",
            data: function (params) {
                return {
                    search: params.term,
                    road_code: $('#road_code').val(),
                    page: params.page || 1
                };
            },
        },
        placeholder: 'House Number / BIN',
        allowClear: true,
        closeOnSelect: true,
        width: '100%',
    });
}
// Store selected values in localStorage
$('#road_code').on('change', function() {
    var selectedRoadCode = $(this).find('option:selected').text();
    localStorage.setItem("selectedRoadCode", selectedRoadCode);
});
$('#bin').on('change', function() {
    var selectedBINValue = $(this).find('option:selected').attr('value');
    var selectedBINText = $(this).find('option:selected').text();
    localStorage.setItem("selectedBINValue", selectedBINValue);
    localStorage.setItem("selectedBINText", selectedBINText);
});
checkDetailsAndUpdateCheckbox();
// Function to check if the Owner and Applicant details are the same
function checkDetailsAndUpdateCheckbox() {
    // Get the values of the Owner and Applicant Details
    const ownerDetails = {
        name: document.getElementById('customer_name').value,
        gender: document.getElementById('customer_gender').value,
        contact: document.getElementById('customer_contact').value
    };
    const applicantDetails = {
        name: document.getElementById('applicant_name').value,
        gender: document.getElementById('applicant_gender').value,
        contact: document.getElementById('applicant_contact').value
    };
    // Get the checkbox element
    const sameAsOwnerCheckbox = document.getElementById('autofill');
    const areOwnerDetailsValid = ownerDetails.name !== "" && ownerDetails.gender !== "" && ownerDetails.contact !== "";
    const areApplicantDetailsValid = applicantDetails.name !== "" && applicantDetails.gender !== "" && applicantDetails.contact !== "";
    // Compare Owner and Applicant details
    const isSame = areOwnerDetailsValid && areApplicantDetailsValid &&
               ownerDetails.name === applicantDetails.name &&
               ownerDetails.gender === applicantDetails.gender &&
               ownerDetails.contact === applicantDetails.contact;
    // Update checkbox state based on comparison
    sameAsOwnerCheckbox.checked = isSame;
}
// Attach event listeners to Applicant fields to check when any field changes
document.getElementById('applicant_name').addEventListener('input', checkDetailsAndUpdateCheckbox);
document.getElementById('applicant_gender').addEventListener('input', checkDetailsAndUpdateCheckbox);
document.getElementById('applicant_contact').addEventListener('input', checkDetailsAndUpdateCheckbox);
    });
</script>

@endpush

