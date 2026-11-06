@extends('pension-guard.layout')

@section('title', 'Verify Email | Pension Account Guard')

@section('content')
<div class="panel" style="max-width:560px;margin:20px auto">

    <h2>Verify your email address</h2>

    <p>
        Thank you for registering with Pension Account Guard.
    </p>

    <p class="muted">
        Before accessing your saved pension records and security features,
        please verify your email address using the link we sent to
        <strong>{{ auth('pension_guard')->user()->email }}</strong>.
    </p>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <form method="POST"
          action="{{ route('pension-guard.verification.send') }}">
        @csrf

        <button type="submit">Resend verification email</button>
    </form>

    <form method="POST"
          action="{{ route('pension-guard.logout') }}"
          style="margin-top:15px">
        @csrf

        <button type="submit">Sign out</button>
    </form>

</div>
@endsection
