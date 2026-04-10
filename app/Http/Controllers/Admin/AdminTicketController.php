<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::with('customer')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->ofStatus($request->status);
        }

        if ($request->filled('email')) {
            $query->forCustomerEmail($request->email);
        }

        if ($request->filled('phone')) {
            $query->forCustomerPhone($request->phone);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->paginate(20)->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['customer', 'assignedTo']);

        $attachments = $ticket->getMedia('attachments')->map(fn ($m) => [
            'id'       => $m->id,
            'name'     => $m->file_name,
            'url'      => $m->getUrl(),
            'mime'     => $m->mime_type,
            'size'     => $m->size,
        ]);

        return view('admin.tickets.show', compact('ticket', 'attachments'));
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,in_progress,completed'],
        ]);

        $ticket->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('success', 'Status updated successfully.');
    }
}
