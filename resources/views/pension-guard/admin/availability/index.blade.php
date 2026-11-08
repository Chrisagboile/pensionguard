@extends('pension-guard.layout')

@section('title', 'Session Availability - Pension Account Guard')

@section('content')
<div class="container">
    <h1>Session Availability</h1>

    <p>
        Manage the dates and times customers can book a Pension Protection Session.
        Customers cannot select an individual expert.
    </p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <section class="panel">
        <h2>Create availability</h2>

        <p>
            Set the beginning and end of a window during which customers may
            choose an appointment. Appointment durations will be in 30-minute
            increments.
        </p>

        <form method="POST"
              action="{{ route('pension-guard.admin.availability.store') }}">
            @csrf

            <div class="form-group">
                <label for="starts_at">Available from</label>
                <input
                    type="datetime-local"
                    id="starts_at"
                    name="starts_at"
                    value="{{ old('starts_at') }}"
                    required
                >
                @error('starts_at')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="ends_at">Available until</label>
                <input
                    type="datetime-local"
                    id="ends_at"
                    name="ends_at"
                    value="{{ old('ends_at') }}"
                    required
                >
                @error('ends_at')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="button">
                Add availability
            </button>
        </form>
    </section>

    <section class="panel">
        <h2>Existing availability</h2>

        @if($availability->isEmpty())
            <p>No availability windows have been created yet.</p>
        @else
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Available from</th>
                            <th>Available until</th>
                            <th>Status</th>
                            <th>Bookings</th>
                            <th>Created by</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availability as $window)
                            <tr>
                                <td>
                                    {{ $window->starts_at->format('d M Y H:i') }}
                                </td>
                                <td>
                                    {{ $window->ends_at->format('d M Y H:i') }}
                                </td>
                                <td>
                                    {{ $window->is_active ? 'Active' : 'Inactive' }}
                                </td>
                                <td>{{ $window->bookings_count }}</td>
                                <td>
                                    {{ $window->creator?->name ?? 'Administrator' }}
                                </td>
                                <td>
                                    <form method="POST"
                                          action="{{ route('pension-guard.admin.availability.toggle', $window) }}">
                                        @csrf
                                        <button type="submit" class="button">
                                            {{ $window->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $availability->links() }}
        @endif
    </section>
</div>
@endsection