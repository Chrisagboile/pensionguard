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

        <label for="name">Your full name</label>
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

        <small class="muted">
            Your username will be generated automatically from your name.
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

        <div style="margin:20px 0;padding:15px;background:#f3f7fc;border:1px solid #dbe5f2;border-radius:8px">

            <label for="terms_accepted" style="display:flex;gap:10px;align-items:flex-start">
                <input
                    id="terms_accepted"
                    type="checkbox"
                    name="terms_accepted"
                    value="1"
                    required
                    {{ old('terms_accepted') ? 'checked' : '' }}
                    style="margin-top:5px"
                >

                <span>
                    I have read and agree to the
                    <a href="{{ route('terms-and-conditions') }}" target="_blank" rel="noopener">
                        Terms and Conditions
                    </a>.
                </span>
            </label>

            <label for="privacy_acknowledged" style="display:flex;gap:10px;align-items:flex-start;margin-top:15px">
                <input
                    id="privacy_acknowledged"
                    type="checkbox"
                    name="privacy_acknowledged"
                    value="1"
                    required
                    {{ old('privacy_acknowledged') ? 'checked' : '' }}
                    style="margin-top:5px"
                >

                <span>
                    I acknowledge that I have read the
                    <a href="{{ route('privacy-policy') }}" target="_blank" rel="noopener">
                        Privacy Policy
                    </a>.
                </span>
            </label>

        </div>

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