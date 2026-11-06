@extends('pension-guard.layout')

@section('title', 'Edit Pension Record')

@section('content')
<section class="panel">
    <h2>Edit pension record</h2>

    <p class="muted">
        Update the general information for this pension record.
        Never enter pension account numbers, passwords, balances
        or security codes.
    </p>

    <form method="POST"
          action="{{ route('pension-guard.accounts.update', $account) }}">
        @csrf
        @method('PUT')

        <label for="provider_id">Choose a provider (optional)</label>
        <select name="provider_id" id="provider_id">
            <option value="">Enter provider manually</option>

            @foreach ($providers as $provider)
                <option value="{{ $provider->id }}"
                    @selected(old('provider_id', $account->provider_id) == $provider->id)>
                    {{ $provider->name }}
                </option>
            @endforeach
        </select>

        <label for="provider_name">Provider name if not listed</label>
        <input
            id="provider_name"
            name="provider_name"
            maxlength="150"
            value="{{ old('provider_name', $account->provider_name) }}"
        >

        <label for="display_name">Display name</label>
        <input
            id="display_name"
            name="display_name"
            maxlength="100"
            value="{{ old('display_name', $account->display_name) }}"
        >

        <label for="account_type">Pension type</label>
        <select name="account_type" id="account_type" required>
            @foreach ([
                'workplace' => 'Workplace pension',
                'personal' => 'Personal pension',
                'sipp' => 'Self-invested personal pension (SIPP)',
                'defined_benefit' => 'Defined-benefit pension',
                'other' => 'Other / not sure',
            ] as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('account_type', $account->account_type) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <label for="notes">Optional notes</label>
        <p class="help-text">
            Only enter general reminders. Never enter account numbers,
            passwords, PINs, verification codes, National Insurance
            numbers, bank details or balances.
        </p>

        <textarea
            id="notes"
            name="notes"
            maxlength="500"
            rows="3"
        >{{ old('notes', $account->notes) }}</textarea>

        <button type="submit">Save changes</button>
    </form>

    <p>
        <a href="{{ route('pension-guard.accounts.show', $account) }}">
            Cancel
        </a>
    </p>
</section>
@endsection
