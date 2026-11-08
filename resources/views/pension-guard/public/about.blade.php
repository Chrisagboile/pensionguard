@extends('pension-guard.layout')

@section('title', 'About Us - Pension Account Guard')

@section('content')
<section class="panel">
    <h1>About Pension Account Guard</h1>

    <p>
        Pension Account Guard is a pension security awareness platform
        designed to help people organise their pension information,
        recognise potential fraud warning signs and take practical
        steps to improve the security of their pension accounts.
    </p>

    <p>
        Pension fraud and impersonation can affect people at different
        stages of their retirement journey. Our aim is to make useful
        security information and practical protection steps easier
        to understand and follow.
    </p>
</section>

<section class="panel">
    <h2>What the platform offers</h2>

    <ul>
        <li>
            <strong>Pension security assessment:</strong>
            educational questions to help users identify areas where
            their pension security practices may need attention.
        </li>
        <li>
            <strong>Pension account organisation:</strong>
            a place to record pension provider details without entering
            pension account passwords or login credentials.
        </li>
        <li>
            <strong>Security checklists:</strong>
            practical steps covering account security and personal
            information protection.
        </li>
        <li>
            <strong>Scam checker:</strong>
            a way to review a suspicious message, call or offer against
            recognised warning signs.
        </li>
        <li>
            <strong>Official guidance:</strong>
            access to relevant government, regulator and pension
            security information.
        </li>
        <li>
            <strong>Trusted contacts:</strong>
            optional features that help users involve someone they trust
            in selected security notifications.
        </li>
    </ul>
</section>

<section class="panel">
    <h2>Our approach to security</h2>

    <p>
        We encourage users to take control of their pension security
        through awareness, careful verification and good digital
        security practices.
    </p>

    <p>
        Pension Account Guard does not ask users for pension provider
        passwords, online banking credentials or one-time security
        codes. Users should always access their pension provider
        directly through independently verified contact details.
    </p>
</section>

<section class="panel">
    <h2>Who is it for?</h2>

    <p>
        The platform is intended for pension savers, particularly
        people approaching retirement and their families or trusted
        supporters who want to understand pension-related fraud risks.
    </p>
</section>

<section class="panel">
    <h2>Important information</h2>

    <p>
        Pension Account Guard provides educational and security
        awareness information only. It does not provide financial,
        investment, legal or regulated pension advice, and it does
        not guarantee that a pension account is secure.
    </p>

    <p>
        Users should contact their pension provider or an appropriately
        authorised professional where they need advice or assistance.
    </p>

    <a class="button" href="{{ route('pension-guard.assessments.create') }}">
        Start a security assessment
    </a>
</section>
@endsection
