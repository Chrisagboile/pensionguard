@extends('pension-guard.layout')

@section('title', 'Privacy Policy | Pension Account Guard')

@section('content')
<div class="panel">
    <h1>Privacy Policy</h1>

    <p><strong>Effective date:</strong> 7 November 2026</p>
    <p><strong>Version:</strong> 1.0</p>

    <p>
        This Privacy Policy explains how Pension Account Guard collects,
        uses, stores and protects personal information when you use the
        service.
    </p>

    <h2>1. Who operates Pension Account Guard?</h2>

    <p>
        Pension Account Guard is operated by Tekbiserv LTD
        ("we", "us" or "our").
    </p>

  <!---  <p>
        Registered address: [INSERT REGISTERED OFFICE ADDRESS]<br>
        Company registration number: [INSERT COMPANY NUMBER]<br>
        Privacy enquiries: [INSERT PRIVACY CONTACT EMAIL]
    </p>
-->
    <h2>2. Information we may collect</h2>

    <p>Depending on the features you use, we may process:</p>

    <ul>
        <li>Account information, including your name, username and email address.</li>
        <li>Pension organisation information and notes you choose to save.</li>
        <li>Security assessment answers and results.</li>
        <li>Security checklist information and progress.</li>
        <li>Trusted contact details that you choose to provide.</li>
        <li>Subscription, payment status and transaction-related information.</li>
        <li>Technical information needed to operate and secure the service.</li>
        <li>Information you submit when using the scam or threat-checking features.</li>
    </ul>

    <h2>3. Information we do not request</h2>

    <p>
        Pension Account Guard does not require your pension provider
        username or password, online banking credentials, bank card
        security codes or one-time authentication codes.
    </p>

    <p>
        Do not submit these credentials or security codes into the service.
    </p>

    <h2>4. How we use information</h2>

    <p>We may use personal information to:</p>

    <ul>
        <li>Create and manage your account.</li>
        <li>Save pension records and security assessment results.</li>
        <li>Provide security awareness information and checklists.</li>
        <li>Operate trusted contact features where you choose to use them.</li>
        <li>Manage subscriptions and payment-related records.</li>
        <li>Send account, verification and service notifications.</li>
        <li>Protect the service, investigate misuse and maintain security.</li>
        <li>Meet applicable legal and regulatory obligations.</li>
    </ul>

    <h2>5. Lawful bases for processing</h2>

    <p>
        Where UK data protection law applies, we rely on the appropriate
        lawful basis for each processing activity. This may include:
    </p>

    <ul>
        <li>
            <strong>Contract:</strong> processing needed to provide the
            service you request.
        </li>
        <li>
            <strong>Legitimate interests:</strong> operating, maintaining
            and securing the service, where those interests are not
            overridden by your rights.
        </li>
        <li>
            <strong>Legal obligation:</strong> processing required by law.
        </li>
        <li>
            <strong>Consent:</strong> where we specifically ask for consent
            and rely on it for a particular activity.
        </li>
    </ul>

    <p>
        Acknowledging this Privacy Policy during registration is not
        blanket consent to every type of data processing.
    </p>

    <h2>6. Sharing information</h2>

    <p>
        We do not sell your personal information. We may share information
        with service providers where necessary to operate the service,
        including hosting, email delivery, payment processing and
        technical support providers.
    </p>

    <p>
        We may also disclose information where required by law or where
        necessary to protect the rights, safety and security of users
        and the service.
    </p>

    <h2>7. Payments</h2>

    <p>
        If you purchase a subscription, payment processing may be provided
        by a third-party payment provider. Payment card information is
        handled in accordance with that provider's processes and privacy
        terms. We may retain subscription and transaction records needed
        to manage your account.
    </p>

    <h2>8. Data security</h2>

    <p>
        We use appropriate technical and organisational measures intended
        to protect personal information against unauthorised access,
        alteration, disclosure or loss.
    </p>

    <p>
        No internet-based service can guarantee absolute security.
        You should use a strong, unique password and protect access to
        your email account.
    </p>

    <h2>9. Data retention</h2>

    <p>
        We retain personal information for as long as reasonably
        necessary for the purposes described in this policy, including
        providing the service, maintaining appropriate records,
        resolving disputes and meeting legal obligations.
    </p>

    <p>
        Retention periods may differ depending on the type of information
        and the reason it is held.
    </p>

    <h2>10. International transfers</h2>

    <p>
        Some service providers may process information outside the United
        Kingdom. Where this happens, appropriate safeguards will be used
        where required by applicable data protection law.
    </p>

    <h2>11. Your data protection rights</h2>

    <p>
        Depending on the circumstances, you may have the right to request
        access to your personal information, correction, erasure,
        restriction of processing, data portability or to object to
        certain processing.
    </p>

    <p>
        You may also have the right to complain to the UK Information
        Commissioner's Office (ICO).
        Visit
        <a href="https://ico.org.uk/" target="_blank" rel="noopener">
            the ICO website
        </a>
        for further information.
    </p>

    <h2>12. Cookies and essential technologies</h2>

    <p>
        The service may use essential session and security technologies
        to support login, navigation and protection against misuse.
        Any additional non-essential cookies or tracking technologies
        will be handled in accordance with applicable requirements.
    </p>

    <h2>13. Children's information</h2>

    <p>
        Pension Account Guard is intended for adults. It is not designed
        for children to create accounts or submit personal information.
    </p>

    <h2>14. Changes to this policy</h2>

    <p>
        We may update this Privacy Policy when our service or legal
        obligations change. The current version and effective date will
        be displayed on this page.
    </p>

    <h2>15. Contact</h2>

    <p>
        For privacy questions or requests, contact:
        [INSERT PRIVACY CONTACT EMAIL].
    </p>

    <p>
        <a href="{{ route('terms-and-conditions') }}">Read our Terms and Conditions</a>
    </p>
</div>
@endsection