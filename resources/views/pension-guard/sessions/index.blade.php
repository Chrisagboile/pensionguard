@extends('pension-guard.layout')

@section('title', 'My Pension Protection Sessions')

@section('content')
<section class="panel">
    <h1>My booked sessions</h1>

    <p>
        <a href="{{ route('pension-guard.sessions.create') }}">Book a new session</a>
    </p>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if($bookings->isEmpty())
        <p>You have not made any session bookings yet.</p>
    @else
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Appointment</th>
                        <th>Duration</th>
                        <th>Session type</th>
                        <th>Total</th>
                        <th>Booking status</th>
                        <th>Payment status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->starts_at->format('d M Y, H:i') }}</td>
                            <td>{{ $booking->duration_minutes }} minutes</td>
                            <td>{{ ucfirst($booking->channel) }}</td>
                            <td>£{{ number_format($booking->amount_pence / 100, 2) }}</td>
                            <td>{{ ucfirst($booking->status) }}</td>
                            <td>{{ ucfirst($booking->payment_status) }}</td>
                            <td>
                                <a href="{{ route('pension-guard.sessions.show', $booking) }}">
                                    View booking
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $bookings->links() }}
    @endif
</section>
@endsection
