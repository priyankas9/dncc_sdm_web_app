
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
{!! Form::model($supervisoryassessment, [
    'method' => 'PATCH',
    'action' => ['Fsm\SupervisoryAssessmentController@update', $supervisoryassessment->id],
    'class' => 'form-horizontal',
   
]) !!}
    @include('fsm/supervisory-assessment.partial-form', ['submitButtomText' => 'Update'])
{!! Form::close() !!}
</div><!-- /.card -->
@endsection
@push('scripts')
<script> 
     let tripData = {}; // global store

    flatpickr('.flatpickr-reschedule', {
    dateFormat: 'Y-m-d',
    allowInput: true,
    onReady: function (selectedDates, dateStr, instance) {
        if (instance.input.id === 'confirmed_emptying_date') {
            // Inject legend at the top
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
                            <span style="width: 10px; height: 10px; background-color: #f8d7da; border-radius: 50%;"></span> 0 Trips
                        </span>
                    </div>
                </div>
            `;
            const calendarContainer = instance.calendarContainer;
            calendarContainer.insertAdjacentHTML("afterbegin", legendHTML);
            fetchAndDisplayTrips(instance);
        }
    },


    onDayCreate: function (dObj, dStr, fp, dayElem) {
        const dateObj = dayElem.dateObj;
        if (!dateObj) return;

        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;

        if (tripData.hasOwnProperty(dateStr)) {
            const { trips, is_holiday, is_weekend } = tripData[dateStr];

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
                dayElem.style.backgroundColor = "rgb(228, 173, 56)"; // pink
                dayElem.style.color = "#000000";
            } else if (is_weekend) {
                dayElem.style.backgroundColor = "#cce5ff"; // light blue
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
        }
    }
});

    // Supervisory assessment date picker initialized separately
   

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

</script>
@endpush
