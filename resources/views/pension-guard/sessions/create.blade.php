@extends('pension-guard.layout')

@section('title', 'Book a Pension Protection Session')

@section('content')
<section class="panel session-booking-page">

    <h1>Book a Pension Protection Session</h1>

    <p class="booking-intro">
        Select an available date and appointment time. Sessions cost
        £25 per 30 minutes.
    </p>

    @if ($errors->any())
        <div class="booking-errors" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="booking-calendar">

        <div class="calendar-heading">
            <button type="button" id="previous-month" class="calendar-nav">
                &lt;
            </button>

            <h2 id="calendar-month-label">Loading calendar...</h2>

            <button type="button" id="next-month" class="calendar-nav">
                &gt;
            </button>
        </div>

        <p id="calendar-status" class="calendar-status" aria-live="polite">
            Loading available appointments...
        </p>

        <div class="calendar-grid calendar-weekdays" id="calendar-weekdays">
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
            <div>Sun</div>
        </div>

        <div class="calendar-grid" id="calendar-days"></div>

        <div class="calendar-legend">
            <span><i class="legend-box available-legend"></i> Available</span>
            <span><i class="legend-box unavailable-legend"></i> Unavailable</span>
            <span><i class="legend-box selected-legend"></i> Selected</span>
        </div>
    </div>

    <div id="appointment-options" hidden>
        <h2>Available appointment times</h2>
        <p id="selected-date-label"></p>
        <div id="appointment-slots" class="appointment-slots"></div>
    </div>

    <form method="POST"
          action="{{ route('pension-guard.sessions.store') }}"
          id="session-booking-form">

        @csrf

        <input type="hidden"
               name="availability_id"
               id="availability_id">

        <input type="hidden"
               name="starts_at"
               id="starts_at">

        <div class="form-group">
            <label for="duration_minutes">Session duration</label>

            <select name="duration_minutes"
                    id="duration_minutes"
                    required
                    disabled>
                <option value="">Select an appointment time first</option>
            </select>
        </div>

        <div class="form-group">
            <label for="channel">Session type</label>

            <select name="channel" id="channel" required>
                <option value="telephone">Telephone</option>
                <option value="video">Video call</option>
            </select>
        </div>

        <div class="booking-summary" aria-live="polite">
            <h3>Booking summary</h3>

            <p>
                Appointment:
                <strong id="summary-appointment">Not selected</strong>
            </p>

            <p>
                Duration:
                <strong id="summary-duration">Not selected</strong>
            </p>

            <p class="summary-total">
                Total to pay:
                <strong id="session-total">£0.00</strong>
            </p>

            <p class="payment-information">
                Payment is taken securely through Stripe Checkout.
                Your appointment is not confirmed until payment is verified.
            </p>
        </div>

        <button type="submit"
                id="continue-to-payment"
                class="payment-button"
                disabled>
            Continue to secure payment
        </button>
    </form>
</section>

<style>
.session-booking-page {
    color: #142b50;
}

.session-booking-page h1 {
    color: #142b50;
    margin-bottom: 18px;
}

.booking-intro {
    margin-bottom: 24px;
    font-size: 1.05rem;
}

.booking-errors {
    padding: 14px 18px;
    margin: 15px 0;
    border: 1px solid #d99b9b;
    border-radius: 8px;
    background: #fff0f0;
    color: #8b1717;
}

.booking-errors ul {
    margin-bottom: 0;
}

.booking-calendar {
    margin: 20px 0 30px;
}

.calendar-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 15px;
}

.calendar-heading h2 {
    margin: 0;
    font-size: 1.2rem;
    color: #142b50;
    text-align: center;
}

.calendar-nav {
    min-width: 48px;
    min-height: 48px;
    border: 0 !important;
    border-radius: 9px;
    background: #1769aa !important;
    color: #ffffff !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    cursor: pointer;
}

.calendar-nav:hover {
    background: #104d80 !important;
    color: #ffffff !important;
}

.calendar-status {
    min-height: 24px;
    margin: 12px 0;
    color: #34445b;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 7px;
    margin-top: 8px;
}

.calendar-grid > * {
    text-align: center;
    padding: 10px 3px;
}

.calendar-weekdays {
    font-weight: 700;
    color: #142b50;
}

.calendar-day {
    min-height: 48px;
    border: 1px solid #d5dce5 !important;
    border-radius: 7px;
    background: #f1f4f8 !important;
    color: #243247 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    opacity: 1 !important;
    cursor: not-allowed;
}

.calendar-day:disabled {
    background: #f1f4f8 !important;
    color: #687588 !important;
    opacity: 1 !important;
    cursor: not-allowed;
}

.calendar-day.available {
    background: #d9f2e3 !important;
    color: #14532d !important;
    border: 1px solid #75bd91 !important;
    cursor: pointer;
}

.calendar-day.available:hover {
    background: #a9dfbd !important;
    color: #104526 !important;
}

.calendar-day.selected {
    background: #1769aa !important;
    color: #ffffff !important;
    border: 2px solid #104d80 !important;
}

.calendar-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    margin-top: 18px;
    font-size: 0.9rem;
    color: #34445b;
}

.legend-box {
    display: inline-block;
    width: 13px;
    height: 13px;
    margin-right: 5px;
    vertical-align: middle;
    border: 1px solid #aab4c2;
    border-radius: 3px;
}

.available-legend {
    background: #d9f2e3;
    border-color: #75bd91;
}

.unavailable-legend {
    background: #f1f4f8;
}

.selected-legend {
    background: #1769aa;
    border-color: #104d80;
}

#appointment-options {
    margin: 28px 0;
}

#appointment-options h2 {
    color: #142b50;
}

#selected-date-label {
    margin: 12px 0;
    color: #34445b;
}

.appointment-slots {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 12px 0 25px;
}

.appointment-slot {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 85px;
    min-height: 48px;
    padding: 10px 16px !important;
    border: 1px solid #2876b8 !important;
    border-radius: 7px !important;
    background: #e8f4ff !important;
    color: #12345a !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    line-height: 1.4;
    opacity: 1 !important;
    cursor: pointer;
}

.appointment-slot:hover {
    background: #cce5fa !important;
    color: #12345a !important;
}

.appointment-slot.selected {
    background: #1769aa !important;
    border-color: #104d80 !important;
    color: #ffffff !important;
}

.no-slots-message {
    padding: 12px;
    border-radius: 7px;
    background: #f1f4f8;
    color: #526174;
}

.form-group {
    margin: 18px 0;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    font-weight: 700;
    color: #142b50;
}

.form-group select {
    width: 60%;
    max-width: 300px;
    min-height: 46px;
    padding: 10px;
    border: 1px solid #b8c5d6;
    border-radius: 7px;
    background: #ffffff;
    color: #243247;
    font-size: 15px;
}

.form-group select:disabled {
    background: #f1f4f8;
    color: #687588;
}

.booking-summary {
    margin: 24px 0;
    padding: 18px;
    border: 1px solid #d5dce5;
    border-radius: 9px;
    background: #f7f9fc;
    color: #243247;
}

.booking-summary h3 {
    margin-top: 0;
    color: #142b50;
}

.booking-summary p {
    margin: 10px 0;
}

.summary-total {
    font-size: 1.15rem;
}

#session-total {
    color: #14532d;
    font-size: 1.25rem;
}

.payment-information {
    font-size: 0.88rem;
    color: #526174;
}

.payment-button {
    min-height: 48px;
    padding: 12px 22px !important;
    border: 0 !important;
    border-radius: 7px !important;
    background: #1769aa !important;
    color: #ffffff !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    cursor: pointer;
}

.payment-button:hover:not(:disabled) {
    background: #104d80 !important;
    color: #ffffff !important;
}

.payment-button:disabled {
    background: #aeb9c7 !important;
    color: #ffffff !important;
    cursor: not-allowed;
}

@media (max-width: 650px) {
    .calendar-grid {
        gap: 4px;
    }

    .calendar-grid > * {
        padding: 8px 1px;
    }

    .calendar-day {
        min-height: 40px;
        font-size: 13px !important;
    }

    .form-group select {
        width: 100%;
        max-width: none;
    }

    .appointment-slot {
        min-width: 75px;
        padding: 9px 10px !important;
    }

    .calendar-legend {
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const availabilityUrl = @json(route('pension-guard.sessions.availability'));

    const monthLabel = document.getElementById('calendar-month-label');
    const calendarDays = document.getElementById('calendar-days');
    const calendarStatus = document.getElementById('calendar-status');
    const previousButton = document.getElementById('previous-month');
    const nextButton = document.getElementById('next-month');

    const appointmentOptions = document.getElementById('appointment-options');
    const appointmentSlots = document.getElementById('appointment-slots');
    const selectedDateLabel = document.getElementById('selected-date-label');

    const availabilityInput = document.getElementById('availability_id');
    const startsAtInput = document.getElementById('starts_at');

    const durationSelect = document.getElementById('duration_minutes');
    const channelSelect = document.getElementById('channel');

    const summaryAppointment = document.getElementById('summary-appointment');
    const summaryDuration = document.getElementById('summary-duration');
    const sessionTotal = document.getElementById('session-total');
    const paymentButton = document.getElementById('continue-to-payment');
    const bookingForm = document.getElementById('session-booking-form');

    const londonTimeZone = 'Europe/London';
    const pricePerThirtyMinutes = 25;

    let displayedMonth = new Date();
    displayedMonth = new Date(
        displayedMonth.getFullYear(),
        displayedMonth.getMonth(),
        1
    );

    let availableDates = {};
    let selectedDate = null;
    let selectedSlot = null;

    function formatDateKey(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function formatMonth(date) {
        return date.toLocaleDateString('en-GB', {
            month: 'long',
            year: 'numeric'
        });
    }

    function formatAppointmentDate(dateKey) {
        const date = new Date(dateKey + 'T12:00:00');

        return date.toLocaleDateString('en-GB', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    function formatAppointmentDateTime(isoDate) {
        const date = new Date(isoDate);

        return new Intl.DateTimeFormat('en-GB', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: londonTimeZone
        }).format(date);
    }

    function clearSelectedAppointment() {
        selectedSlot = null;

        availabilityInput.value = '';
        startsAtInput.value = '';

        durationSelect.innerHTML =
            '<option value="">Select an appointment time first</option>';

        durationSelect.disabled = true;

        summaryAppointment.textContent = 'Not selected';
        summaryDuration.textContent = 'Not selected';
        sessionTotal.textContent = '£0.00';

        paymentButton.disabled = true;
    }

    function updatePrice() {
        const duration = Number(durationSelect.value);

        if (!selectedSlot || !duration) {
            summaryDuration.textContent = 'Not selected';
            sessionTotal.textContent = '£0.00';
            paymentButton.disabled = true;
            return;
        }

        const total = (duration / 30) * pricePerThirtyMinutes;

        summaryDuration.textContent = duration + ' minutes';
        sessionTotal.textContent = '£' + total.toFixed(2);

        paymentButton.disabled = false;
    }

    function renderCalendar() {
        monthLabel.textContent = formatMonth(displayedMonth);
        calendarDays.innerHTML = '';

        const year = displayedMonth.getFullYear();
        const month = displayedMonth.getMonth();

        const firstDay = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Convert Sunday-first JavaScript weekday to Monday-first calendar.
        const startingOffset = (firstDay.getDay() + 6) % 7;

        for (let i = 0; i < startingOffset; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'calendar-empty';
            calendarDays.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateKey = formatDateKey(date);
            const slots = availableDates[dateKey] || [];

            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'calendar-day';
            button.textContent = day;
            button.setAttribute('aria-label', formatAppointmentDate(dateKey));

            if (slots.length > 0) {
                button.classList.add('available');
                button.disabled = false;

                button.addEventListener('click', function () {
                    selectDate(dateKey);
                });
            } else {
                button.disabled = true;
            }

            if (selectedDate === dateKey) {
                button.classList.add('selected');
                button.setAttribute('aria-pressed', 'true');
            }

            calendarDays.appendChild(button);
        }

        previousButton.disabled = false;
        previousButton.style.opacity = '1';
    }

    function renderAppointmentSlots(dateKey) {
        appointmentSlots.innerHTML = '';

        const slots = availableDates[dateKey] || [];

        if (slots.length === 0) {
            const message = document.createElement('p');
            message.className = 'no-slots-message';
            message.textContent = 'There are no available appointments on this date.';
            appointmentSlots.appendChild(message);
            return;
        }

        slots.forEach(function (slot) {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'appointment-slot';
            button.textContent = slot.time;
            button.setAttribute('aria-label', 'Appointment at ' + slot.time);

            button.addEventListener('click', function () {
                selectAppointmentSlot(slot, button);
            });

            appointmentSlots.appendChild(button);
        });
    }

    function selectDate(dateKey) {
        selectedDate = dateKey;

        clearSelectedAppointment();

        appointmentOptions.hidden = false;
        selectedDateLabel.textContent = formatAppointmentDate(dateKey);

        renderCalendar();
        renderAppointmentSlots(dateKey);
    }

    function selectAppointmentSlot(slot, selectedButton) {
        selectedSlot = slot;

        availabilityInput.value = slot.availability_id;
        startsAtInput.value = slot.starts_at;

        document.querySelectorAll('.appointment-slot').forEach(function (button) {
            button.classList.remove('selected');
            button.setAttribute('aria-pressed', 'false');
        });

        selectedButton.classList.add('selected');
        selectedButton.setAttribute('aria-pressed', 'true');

        summaryAppointment.textContent =
            formatAppointmentDateTime(slot.starts_at);

        durationSelect.innerHTML =
            '<option value="">Choose session duration</option>';

        durationSelect.disabled = false;

        (slot.durations || []).forEach(function (duration) {
            const option = document.createElement('option');

            option.value = duration;
            option.textContent = duration + ' minutes';

            durationSelect.appendChild(option);
        });

        summaryDuration.textContent = 'Not selected';
        sessionTotal.textContent = '£0.00';
        paymentButton.disabled = true;
    }

    async function loadAvailability() {
        const year = displayedMonth.getFullYear();
        const month = String(displayedMonth.getMonth() + 1).padStart(2, '0');
        const monthValue = `${year}-${month}`;

        calendarStatus.textContent = 'Loading available appointments...';
        calendarDays.innerHTML = '';

        appointmentOptions.hidden = true;
        selectedDate = null;
        clearSelectedAppointment();

        try {
            const response = await fetch(
                availabilityUrl + '?month=' + encodeURIComponent(monthValue),
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) {
                throw new Error('Unable to retrieve appointment availability.');
            }

            const result = await response.json();

            availableDates = result.dates || {};

            const hasAvailability = Object.values(availableDates).some(
                function (slots) {
                    return Array.isArray(slots) && slots.length > 0;
                }
            );

            renderCalendar();

            calendarStatus.textContent = hasAvailability
                ? 'Select a highlighted date to view available appointment times.'
                : 'There are no available appointments in this month.';

        } catch (error) {
            availableDates = {};
            renderCalendar();

            calendarStatus.textContent =
                'Unable to load appointments. Please refresh the page and try again.';

            console.error('Pension Guard availability error:', error);
        }
    }

    previousButton.addEventListener('click', function () {
        displayedMonth = new Date(
            displayedMonth.getFullYear(),
            displayedMonth.getMonth() - 1,
            1
        );

        loadAvailability();
    });

    nextButton.addEventListener('click', function () {
        displayedMonth = new Date(
            displayedMonth.getFullYear(),
            displayedMonth.getMonth() + 1,
            1
        );

        loadAvailability();
    });

    durationSelect.addEventListener('change', updatePrice);

    channelSelect.addEventListener('change', function () {
        // Channel selection does not change the session price.
        updatePrice();
    });

    bookingForm.addEventListener('submit', function (event) {
        if (
            !availabilityInput.value ||
            !startsAtInput.value ||
            !durationSelect.value
        ) {
            event.preventDefault();
            alert('Please select an available date, appointment time and session duration.');
            return;
        }

        paymentButton.disabled = true;
        paymentButton.textContent = 'Preparing secure payment...';
    });

    loadAvailability();
});
</script>
@endsection
