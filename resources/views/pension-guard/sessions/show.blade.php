
@extends('pension-guard.layout')

@section('title', 'Session Booking Details')

@section('content')
<section class="panel">
    <h1>Session booking details</h1>

    @if($errors->has('booking'))
        <p class="error">{{ $errors->first('booking') }}</p>
    @endif

    <p><strong>Booking reference:</strong> PAG-{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
    <p><strong>Appointment date:</strong> {{ $booking->starts_at->format('l, d F Y') }}</p>
    <p><strong>Start time:</strong> {{ $booking->starts_at->format('H:i') }}</p>
    <p><strong>End time:</strong> {{ $booking->ends_at->format('H:i') }}</p>
    <p><strong>Duration:</strong> {{ $booking->duration_minutes }} minutes</p>
    <p><strong>Session type:</strong> {{ ucfirst($booking->channel) }}</p>
    <p><strong>Amount:</strong> £{{ number_format($booking->amount_pence / 100, 2) }}</p>
    <p><strong>Booking status:</strong> {{ ucfirst($booking->status) }}</p>
    <p><strong>Payment status:</strong> {{ ucfirst($booking->payment_status) }}</p>

    @if($booking->payment_status === 'paid' && $booking->confirmed_at)
        <p><strong>Payment confirmed:</strong> {{ $booking->paid_at?->format('d M Y, H:i') ?? 'Confirmed' }}</p>
        <p><strong>Appointment confirmed:</strong> {{ $booking->confirmed_at->format('d M Y, H:i') }}</p>
        <p>Your appointment is confirmed. Please retain your booking reference.</p>
    @elseif($booking->status === 'pending')
        <p>Your appointment is temporarily reserved while payment is being completed or verified.</p>
        @if($booking->checkout_expires_at)
            <p><strong>Reservation expiry:</strong> {{ $booking->checkout_expires_at->format('d M Y, H:i') }}</p>
        @endif
    @elseif($booking->status === 'expired')
        <p>This reservation has expired and is no longer held. Please choose another appointment.</p>
        <p><a href="{{ route('pension-guard.sessions.create') }}">Choose another appointment</a></p>
    @elseif($booking->status === 'cancelled')
        <p>This booking has been cancelled.</p>
    @endif

    <p><a href="{{ route('pension-guard.sessions.index') }}">Back to my bookings</a></p>
</section>
@endsection
