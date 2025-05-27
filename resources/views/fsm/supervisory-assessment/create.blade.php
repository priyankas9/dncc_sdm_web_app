@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

<div class="card card-info">
    {!! Form::open(['url' => 'fsm/supervisory-assessment', 'class' => 'form-horizontal']) !!}
    
    <!-- Hidden input to pass the slug value -->
    <input type="hidden" name="slug" value="{{ $value }}">

    @include('fsm.supervisory-assessment.partial-form', ['submitButtomText' => 'Save'])

    {!! Form::close() !!}
</div><!-- /.card -->
@endsection
@push('scripts')
<script> 
     let tripData = {}; // global store

   // assign proposed date from blade into JS variable
const proposedEmptyingDate = "{{ $application ? $application->proposed_emptying_date : '' }}";

flatpickr('.flatpickr-reschedule', {
    dateFormat: 'Y-m-d',
    allowInput: true,


    onReady: function (selectedDates, dateStr, instance) {
        if (instance.input.id === 'confirmed_emptying_date') {
            

            // Inject legend
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
 onMonthChange: function (selectedDates, dateStr, instance) {
        if (instance.input.id === 'confirmed_emptying_date') {
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

            dayElem.removeAttribute("style");
            dayElem.style.cursor = "pointer";

            let tooltip = `Trips Available: ${trips}`;
            if (is_holiday) tooltip += " (Holiday)";
            if (is_weekend) tooltip += " (Weekend)";
            dayElem.setAttribute("title", tooltip);

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
 document.addEventListener('DOMContentLoaded', function() {
    const displayField = document.getElementById('containment_type_display');
    const selectField = document.getElementById('containment_type_select');
    
    displayField.addEventListener('click', function() {
        this.classList.add('d-none');
        selectField.classList.remove('d-none');
        selectField.focus();
    });
    
    selectField.addEventListener('change', function() {
        displayField.value = this.options[this.selectedIndex].text;
        displayField.classList.remove('d-none');
        this.classList.add('d-none');
    });
    
    // Ensure the select field is submitted even when hidden
    selectField.addEventListener('blur', function() {
        if(!this.classList.contains('d-none')) {
            displayField.value = this.options[this.selectedIndex].text;
            displayField.classList.remove('d-none');
            this.classList.add('d-none');
        }
    });
});
</script>
@endpush
