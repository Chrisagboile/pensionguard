@extends('pension-guard.layout')

@section('title', 'Pension Record')

@section('content')
<section class="panel">
    <p>
        <a href="{{ route('pension-guard.accounts.index') }}">
            &larr; Back to my pensions
        </a>
    </p>

    <h2>{{ $account->display_name ?: $account->provider_name }}</h2>

    <p>
        <strong>Provider:</strong>
        {{ $account->provider_name }}
    </p>

    <p>
        <strong>Type:</strong>
        {{ ucfirst(str_replace('_', ' ', $account->account_type)) }}
    </p>

    @if ($account->notes)
        <p>
            <strong>Notes:</strong>
            {{ $account->notes }}
        </p>
    @endif

    @if ($account->provider?->website_url)
        <p>
            <strong>Official provider website:</strong>
            <a href="{{ $account->provider->website_url }}"
               target="_blank"
               rel="noopener noreferrer">
                {{ $account->provider->name }} website
                <span aria-hidden="true">↗</span>
            </a>
        </p>
    @endif

    <hr>

    <h3>Manage pension record</h3>

    <p>
        <a class="button"
        href="{{ route('pension-guard.accounts.edit', $account) }}">
            Edit pension record
        </a>
    </p>

    <form method="POST"
        action="{{ route('pension-guard.accounts.destroy', $account) }}"
        onsubmit="return confirm('Are you sure you want to delete this pension record? Its security checklist will also be deleted. This action cannot be undone.');">
        @csrf
        @method('DELETE')

        <button type="submit"
                class="button"
                style="background:#b42318;">
            Delete pension record
        </button>
    </form>
    <hr>

    <h3>Security checklist</h3>
    <p>
        Review the protection steps for this pension account and keep track
        of the checks you have completed.
    </p>

    <a class="button"
       href="{{ route('pension-guard.checklist.index', ['account' => $account->id]) }}">
        Open security checklist
    </a>

    <p class="muted">
        Always verify the web address independently before entering
        credentials or personal information.
    </p>

    <hr>

    <p>
        <a href="{{ route('pension-guard.dashboard') }}">
            Return to Pension Account Guard home
        </a>
    </p>
</section>
@endsection
