@extends('pension-guard.layout')

@section('title', 'Pension Guard Admin Dashboard')

@section('content')
<section class="panel">
    <h2>Pension Account Guard Administration</h2>
    <p>
        Overview of registered users, saved records and official
        guidance source maintenance.
    </p>

    <p>
        <a href="{{ route('pension-guard.admin.sources.index') }}">
            Manage official sources
        </a>

	<a href="{{ route('pension-guard.admin.availability.index') }}"
             class="button">
                 Manage Session Availability
	</a>
    </p>
</section>

<section class="panel">
    <h3>Platform overview</h3>

    <div class="dashboard-metrics">
        <article class="panel">
            <h4>Registered users</h4>
            <p>{{ number_format($metrics['users']) }}</p>
        </article>

        <article class="panel">
            <h4>Pension accounts</h4>
            <p>{{ number_format($metrics['accounts']) }}</p>
        </article>

        <article class="panel">
            <h4>Saved assessments</h4>
            <p>{{ number_format($metrics['assessments']) }}</p>
        </article>

        <article class="panel">
            <h4>Active sources</h4>
            <p>{{ number_format($metrics['active_sources']) }}</p>
        </article>

        <article class="panel">
            <h4>Inactive sources</h4>
            <p>{{ number_format($metrics['inactive_sources']) }}</p>
        </article>

        <article class="panel">
            <h4>Overdue reviews</h4>
            <p>{{ number_format($metrics['overdue_reviews']) }}</p>
        </article>

        <article class="panel">
            <h4>Sources never reviewed</h4>
            <p>{{ number_format($metrics['unreviewed_sources']) }}</p>
        </article>
    </div>
</section>

<section class="panel">
    <h3>Source review schedule</h3>
    <p>
        These are the five earliest scheduled review dates for active
        sources. Past-due dates are included so they remain visible.
    </p>

    @if ($upcomingReviews->isEmpty())
        <p>No source review dates have been scheduled.</p>
    @else
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Next review due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($upcomingReviews as $source)
                        <tr>
                            <td>{{ $source->name }}</td>
                            <td>
                                {{ $source->next_review_due->format('d M Y') }}
                            </td>
                            <td>
                                @if ($source->next_review_due->toDateString() < $today)
                                    Overdue
                                @elseif ($source->next_review_due->toDateString() === $today)
                                    Due today
                                @else
                                    Scheduled
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p>
        <a href="{{ route('pension-guard.admin.sources.index') }}">
            Review and update sources
        </a>
    </p>
</section>

<section class="panel">
    <p>
        <strong>Note:</strong> These figures reflect records currently
        stored in the application. A scheduled or recorded review date
        does not independently verify that external guidance is current.
    </p>
</section>
@endsection
