@extends('pension-guard.layout')

@section('title', 'Contact Us - Pension Account Guard')

@section('content')
<section class="panel">
    <h1>Contact Pension Account Guard</h1>
    <p>
        Have a question about the platform, your account or our
        pension protection sessions? Contact our support team.
    </p>

    <p>
        <strong>Email:</strong>
        <a href="mailto:support@tekbiserv.co.uk">
            support@tekbiserv.co.uk
        </a>
    </p>
</section>

<section class="panel" id="session-enquiry">
    <h2>Send us a message</h2>

    <p class="muted">
        Please do not include pension account numbers, passwords,
        one-time codes, bank details or other sensitive financial
        information in this form.
    </p>

    @if ($errors->has('contact'))
        <div class="error" role="alert">
            {{ $errors->first('contact') }}
        </div>
    @endif

    <form method="POST" action="{{ route('contact.submit') }}">
        @csrf

        <label for="name">Your name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name') }}"
            maxlength="150"
            autocomplete="name"
            required
            style="width:100%;"
        >

        @error('name')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="email">Email address</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            maxlength="255"
            autocomplete="email"
            required
            style="width:100%;"
        >

        @error('email')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="subject">Enquiry type</label>
        <select id="subject" name="subject" required style="width:100%;">
            <option value="">Select an enquiry type</option>
            <option value="General enquiry" @selected(old('subject') === 'General enquiry')>
                General enquiry
            </option>
            <option value="Technical support" @selected(old('subject') === 'Technical support')>
                Technical support
            </option>
            <option value="Pension protection session" @selected(old('subject') === 'Pension protection session' || request('subject') === 'session')>
                Pension protection session
            </option>
            <option value="Subscription enquiry" @selected(old('subject') === 'Subscription enquiry')>
                Subscription enquiry
            </option>
        </select>

        @error('subject')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="message">Your message</label>
        <textarea
            id="message"
            name="message"
            rows="7"
            maxlength="5000"
            required
            style="width:100%;"
        >{{ old('message') }}</textarea>

        @error('message')
            <p class="error">{{ $message }}</p>
        @enderror

        <p>
            <button type="submit">Send enquiry</button>
        </p>
    </form>
</section>
@endsection
