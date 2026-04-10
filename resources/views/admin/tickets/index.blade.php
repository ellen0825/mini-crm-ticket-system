@extends('admin.layout')

@section('title', 'Tickets')

@section('content')

<div class="card">
    <div class="card__header">
        <span class="card__title">Tickets</span>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="form-inline">
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="new"         {{ request('status') === 'new'         ? 'selected' : '' }}>New</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ request('email') }}" placeholder="customer@example.com">
            </div>
            <div>
                <label>Phone</label>
                <input type="tel" name="phone" value="{{ request('phone') }}" placeholder="+12025550100">
            </div>
            <div>
                <label>From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div style="display:flex;gap:.4rem;align-items:flex-end;padding-bottom:1px">
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                <a href="{{ route('admin.tickets.index') }}" class="btn btn--outline btn--sm">Reset</a>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td style="color:#9ca3af;font-size:.78rem">TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div style="font-weight:600">{{ $ticket->customer->name }}</div>
                        <div style="font-size:.78rem;color:#9ca3af">{{ $ticket->customer->email }}</div>
                    </td>
                    <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $ticket->subject }}
                    </td>
                    <td>
                        <span class="badge badge--{{ $ticket->status }}">
                            {{ str_replace('_', ' ', $ticket->status) }}
                        </span>
                    </td>
                    <td style="color:#6b7280;white-space:nowrap">
                        {{ $ticket->created_at->format('d M Y, H:i') }}
                    </td>
                    <td>
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn--outline btn--sm">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#9ca3af;padding:2rem">
                        No tickets found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($tickets->hasPages())
        <div class="pagination">
            {!! $tickets->links('admin.pagination') !!}
        </div>
    @endif
</div>

@endsection
