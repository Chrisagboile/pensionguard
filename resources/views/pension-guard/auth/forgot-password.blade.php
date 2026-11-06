@extends('pension-guard.layout')

@section('title', 'Forgot Password | Pension Account Guard')

@section('content')
<div class="panel" style="max-width:520px;margin:20px auto">

    <h2>Forgot your password?</h2>

    <p class="muted">
        Enter the email address associated with your Pension Guard account.
        If an account exists, we will send you a password-reset link.
    </p>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('pension-guard.password.email') }}">
        @csrf

        <label for="email">Email address</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            autocomplete="email"
            required
            maxlength="255"
            style="width:100%"
        >

        <p>
            <button type="submit">Send password-reset link</button>
        </p>
    </form>

    <p>
        <a href="{{ route('pension-guard.login') }}">Return to sign in</a>
    </p>
</div>
@endsection
