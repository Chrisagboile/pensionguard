@extends('pension-guard.layout')

@section('title', 'Reset Password | Pension Account Guard')

@section('content')
<div class="panel" style="max-width:520px;margin:20px auto">

    <h2>Reset your password</h2>

    <p class="muted">
        Choose a new, strong password for your Pension Guard account.
    </p>

    @if ($errors->any())
        <div role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('pension-guard.password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Email address</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email', $email) }}"
            autocomplete="email"
            required
            style="width:100%"
        >

        <label for="password">New password</label>
        <input
            id="password"
            type="password"
            name="password"
            autocomplete="new-password"
            minlength="12"
            required
            style="width:100%"
        >

        <label for="password_confirmation">Confirm new password</label>
        <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            minlength="12"
            required
            style="width:100%"
        >

        <p>
            <button type="submit">Reset password</button>
        </p>
    </form>
</div>
@endsection
