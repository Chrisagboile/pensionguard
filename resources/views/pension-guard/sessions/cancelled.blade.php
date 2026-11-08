@extends('pension-guard.layout')

@section('title', 'Session Checkout')

@section('content')
<div class="container">
    <section class="panel">
        <h1>Checkout not completed</h1>

        <p>
            You returned from Stripe without completing the checkout.
        </p>

        <p>
            Your appointment is not confirmed. If a payment was
            submitted, allow time for Stripe to notify us before
            attempting another booking.
        </p>

        <p>
            <a href="{{ route('pension-guard.sessions.show', $booking) }}">
                View booking status
            </a>
        </p>

        <p>
            <a href="{{ route('pension-guard.sessions.create') }}">
                Return to available sessions
            </a>
        </p>
    </section>
</div>
@endsection