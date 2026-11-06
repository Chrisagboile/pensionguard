@extends('pension-guard.layout')

@section('title', 'Create account | Pension Account Guard')

@section('content')
<div class="panel" style="max-width:560px;margin:20px auto">

    <h2>Create your Pension Guard account</h2>

    <p class="muted">
        Create a separate account to save your pension records and
        security awareness assessment results.
    </p>

    @if ($errors->any())
        <div role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('pension-guard.register.submit') }}">
        @csrf

        <label for="name">Your name</label>
        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name') }}"
            autocomplete="name"
            required
            maxlength="255"
            style="width:100%"
        >

        <label for="username">Username</label>
        <input
            id="username"
            type="text"
            name="username"
            value="{{ old('username') }}"
            autocomplete="username"
            required
            maxlength="255"
            pattern="[A-Za-z0-9_-]+"
            aria-describedby="username-help"
            style="width:100%"
        >

        <small id="username-help" class="muted">
            Use letters, numbers, underscores or hyphens.
        </small>

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

        <label for="password">Create a password</label>
        <input
            id="password"
            type="password"
            name="password"
            autocomplete="new-password"
            required
            minlength="12"
            style="width:100%"
        >

        <small class="muted">
            Use a strong, unique password that you do not use elsewhere.
        </small>

        <label for="password_confirmation">Confirm password</label>
        <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            required
            minlength="12"
            style="width:100%"
        >

        <p>
            <button type="submit">Create account</button>
        </p>
    </form>

    <p>
        Already registered?
        <a href="{{ route('pension-guard.login') }}">Sign in</a>.
    </p>

    <p class="muted">
        Never enter your pension provider password, bank details or
        one-time security codes into Pension Account Guard.
    </p>
</div>
@endsection
