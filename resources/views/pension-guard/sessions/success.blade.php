@extends('pension-guard.layout')

@section('title', 'Session Payment')

@section('content')
<div class="container">
    <section class="panel">
        <h1>Payment return received</h1>

        @if($booking->status === 'confirmed' && $booking->payment_status === 'paid')
            <h2>Booking confirmed</h2>
            <p>Your payment has been verified and your session is confirmed.</p>
        @else
            <h2>Payment verification in progress</h2>
            <p>
                Thank you for returning from Stripe. We are waiting for
                confirmation from the payment provider.
            </p>
            <p>
                Please do not make another booking while your payment
                is being checked.
            </p>
        @endif

        <p>
            <strong>Booking reference:</strong>
            #{{ $booking->id }}
        </p>

        <p>
            <a href="{{ route('pension-guard.sessions.show', $booking) }}">
                View booking details
            </a>
        </p>
    </section>
</div>
@endsection