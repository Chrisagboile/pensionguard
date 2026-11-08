@extends('pension-guard.layout')

@section('title', 'Pricing - Pension Account Guard')

@section('content')
<section class="panel">
    <h1>Simple, transparent pricing</h1>
    <p>
        Choose a membership option or arrange an individual
        pension protection session.
    </p>
    <p class="muted">
        All prices are in pounds sterling (GBP).
    </p>
</section>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;">

    <section class="panel">
        <h2>Monthly Premium</h2>
        <p style="font-size:2rem;font-weight:bold;margin:12px 0;">
            £4 <span style="font-size:1rem;font-weight:normal;">/ month</span>
        </p>
        <p>Premium access billed monthly.</p>

        @auth('pension_guard')
            <a class="button" href="{{ route('pension-guard.subscriptions.index') }}">
                View subscription options
            </a>
        @else
            <a class="button" href="{{ route('pension-guard.login') }}">
                Sign in to subscribe
            </a>
        @endauth
    </section>

    <section class="panel">
        <h2>Annual Premium</h2>
        <p style="font-size:2rem;font-weight:bold;margin:12px 0;">
            £48 <span style="font-size:1rem;font-weight:normal;">/ year</span>
        </p>
        <p>Premium access billed annually.</p>

        @auth('pension_guard')
            <a class="button" href="{{ route('pension-guard.subscriptions.index') }}">
                View subscription options
            </a>
        @else
            <a class="button" href="{{ route('pension-guard.login') }}">
                Sign in to subscribe
            </a>
        @endauth
    </section>

    <section class="panel" id="session-booking">
        <h2>Pension Protection Session</h2>
        <p style="font-size:2rem;font-weight:bold;margin:12px 0;">
            £25
        </p>
        <p><strong>30-minute individual session</strong></p>
        <ul>
            <li>Telephone or video session.</li>
            <li>Help understanding pension security risks.</li>
            <li>Practical security awareness guidance.</li>
            <li>No need to select an individual expert.</li>
        </ul>
        <a class="button" href="{{ route('contact') }}?subject=session#session-enquiry">
            Enquire about a session
        </a>
    </section>

</div>

<section class="panel">
    <h2>Subscription and payment information</h2>
    <p>
        Premium subscriptions are subject to the subscription terms
        displayed during checkout. Payments are processed through
        the configured payment provider when checkout is enabled.
    </p>
    <p>
        The £25 protection session is separate from Premium
        membership and is not a recurring subscription.
    </p>
</section>

<section class="panel">
    <h2>Need help choosing?</h2>
    <p>
        Contact our support team if you have questions about the
        platform or the available services.
    </p>
    <a class="button" href="{{ route('contact') }}">Contact us</a>
</section>
@endsection
