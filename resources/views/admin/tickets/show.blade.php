@extends('admin.layout')

@section('title', 'Ticket #' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT))

@section('content')

<div style="margin-bottom:1rem">
    <a href="{{ route('admin.tickets.index') }}" class="btn btn--outline btn--sm">← Back to list</a>
</div>

<div class="card" style="margin-bottom:1.25rem">
    <div class="card__header">
        <div>
            <span class="card__title">{{ $ticket->subject }}</span>
            <span style="margin-left:.75rem;font-size:.78rem;color:#9ca3af">
                TKT-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
            </span>
        </div>
        <span class="badge badge--{{ $ticket->status }}">
            {{ str_replace('_', ' ', $ticket->status) }}
        </span>
    </div>

    <div style="padding:1.25rem">
        <div class="detail-grid">

            {{-- Left column --}}
            <div>
                <div class="detail-section">
                    <div class="detail-section__title">Customer</div>
                    <div class="detail-row">
                        <span class="detail-row__label">Name</span>
                        <span>{{ $ticket->customer->name }}</span>
                    </div>
                    @if($ticket->customer->email)
                    <div class="detail-row">
                        <span class="detail-row__label">Email</span>
                        <a href="mailto:{{ $ticket->customer->email }}">{{ $ticket->customer->email }}</a>
                    </div>
                    @endif
                    @if($ticket->customer->phone)
                    <div class="detail-row">
                        <span class="detail-row__label">Phone</span>
                        <a href="tel:{{ $ticket->customer->phone }}">{{ $ticket->customer->phone }}</a>
                    </div>
                    @endif
                </div>

                <div class="detail-section">
                    <div class="detail-section__title">Ticket info</div>
                    <div class="detail-row">
                        <span class="detail-row__label">Created</span>
                        <span>{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-row__label">Updated</span>
                        <span>{{ $ticket->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if($ticket->responded_at)
                    <div class="detail-row">
                        <span class="detail-row__label">Responded</span>
                        <span>{{ $ticket->responded_at->format('d M Y, H:i') }}</span>
                    </div>
                    @endif
                    @if($ticket->assignedTo)
                    <div class="detail-row">
                        <span class="detail-row__label">Assigned to</span>
                        <span>{{ $ticket->assignedTo->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Right column: status change --}}
            <div>
                <div class="detail-section">
                    <div class="detail-section__title">Change Status</div>
                    <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                        @csrf
                        @method('PATCH')
                        <select name="status" style="padding:.45rem .7rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:.84rem;outline:none">
                            <option value="new"         {{ $ticket->status === 'new'         ? 'selected' : '' }}>New</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed"   {{ $ticket->status === 'completed'   ? 'selected' : '' }}>Completed</option>
                        </select>
                        <button type="submit" class="btn btn--primary btn--sm">Save</button>
                    </form>
                    @error('status')
                        <div style="font-size:.78rem;color:#ef4444;margin-top:.35rem">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>

        {{-- Message --}}
        <div class="detail-section">
            <div class="detail-section__title">Message</div>
            <div class="content-box">{{ $ticket->content }}</div>
        </div>

        {{-- Admin response --}}
        @if($ticket->admin_response)
        <div class="detail-section">
            <div class="detail-section__title">Admin Response</div>
            <div class="content-box" style="border-color:#e0e7ff;background:#f5f3ff">{{ $ticket->admin_response }}</div>
        </div>
        @endif

        {{-- Attachments --}}
        <div class="detail-section" style="margin-bottom:0">
            <div class="detail-section__title">
                Attachments ({{ count($attachments) }})
            </div>
            @if(count($attachments))
                <div class="file-list">
                    @foreach($attachments as $file)
                        <div class="file-item">
                            <svg width="14" height="14" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <span class="file-item__name" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                            <span class="file-item__size">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                            <a href="{{ $file['url'] }}" download="{{ $file['name'] }}" class="btn btn--outline btn--sm">
                                Download
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="font-size:.84rem;color:#9ca3af">No attachments.</p>
            @endif
        </div>

    </div>
</div>

@endsection
