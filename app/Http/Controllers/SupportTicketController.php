<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Notification;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SupportTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_if(!Auth::user()->hasRole('admin'), Response::HTTP_FORBIDDEN, '403 Forbidden');
            return $next($request);
        })->only(['adminIndex', 'adminShow', 'adminReply', 'updateStatus']);

        $this->middleware(function ($request, $next) {
            abort_if(!Auth::user()->hasRole('Organizer'), Response::HTTP_FORBIDDEN, '403 Forbidden');
            return $next($request);
        })->only(['organizerIndex', 'organizerShow']);
    }

    // Customer side

    public function index()
    {
        $tickets = SupportTicket::where('app_user_id', Auth::guard('appuser')->user()->id)
            ->orderByDesc('id')->get();
        return view('frontend.support.index', compact('tickets'));
    }

    public function create()
    {
        $eventIds = Order::where('customer_id', Auth::guard('appuser')->user()->id)->pluck('event_id')->unique();
        $events = Event::whereIn('id', $eventIds)->get(['id', 'name']);
        return view('frontend.support.create', compact('events'));
    }

    public function store(Request $request)
    {
        $appUserId = Auth::guard('appuser')->user()->id;
        $request->validate([
            'subject' => 'bail|required|string|max:255',
            'message' => 'bail|required|string',
            'priority' => 'bail|required|in:low,medium,high',
            'event_id' => 'bail|nullable|integer|exists:events,id',
        ]);

        $eventId = null;
        if ($request->filled('event_id')) {
            $ownsEvent = Order::where('customer_id', $appUserId)->where('event_id', $request->event_id)->exists();
            $eventId = $ownsEvent ? $request->event_id : null;
        }

        $ticket = SupportTicket::create([
            'app_user_id' => $appUserId,
            'event_id' => $eventId,
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        $this->notifyOrganizerOfTicketActivity($ticket, __('New support ticket about your event') . ': ' . $ticket->subject);

        return redirect('/support-tickets/' . $ticket->id)->with('success', __('Support ticket created successfully.'));
    }

    public function show($id)
    {
        $ticket = SupportTicket::with('replies')->findOrFail($id);
        if ($ticket->app_user_id != Auth::guard('appuser')->user()->id) {
            abort(403);
        }
        return view('frontend.support.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        if ($ticket->app_user_id != Auth::guard('appuser')->user()->id) {
            abort(403);
        }
        $request->validate(['body' => 'bail|required|string']);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'app_user',
            'sender_id' => Auth::guard('appuser')->user()->id,
            'body' => $request->body,
        ]);
        $ticket->update(['status' => 'open', 'last_reply_at' => now()]);

        return redirect()->back();
    }

    // Admin side

    public function adminIndex()
    {
        $tickets = SupportTicket::with('appUser:id,name,last_name,image')->orderByDesc('last_reply_at')->get();
        return view('admin.support.index', compact('tickets'));
    }

    public function adminShow($id)
    {
        $ticket = SupportTicket::with(['replies', 'appUser:id,name,last_name,image'])->findOrFail($id);
        return view('admin.support.show', compact('ticket'));
    }

    public function adminReply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $request->validate(['body' => 'bail|required|string']);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => Auth::user()->id,
            'body' => $request->body,
        ]);
        $ticket->update(['status' => 'answered', 'last_reply_at' => now()]);

        $this->notifyOrganizerOfTicketActivity($ticket, __('Support ticket update for your event') . ': ' . $ticket->subject);

        return redirect()->back();
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'bail|required|in:open,answered,closed']);
        SupportTicket::findOrFail($id)->update(['status' => $request->status]);
        return redirect()->back()->withStatus(__('Ticket status updated.'));
    }

    // Organizer side - read only, admins remain the only ones who can reply

    public function organizerIndex()
    {
        $eventIds = Event::where('user_id', Auth::user()->id)->pluck('id');
        $tickets = SupportTicket::with('appUser:id,name,last_name,image')
            ->whereIn('event_id', $eventIds)
            ->orderByDesc('last_reply_at')->get();
        return view('admin.support.organizerIndex', compact('tickets'));
    }

    public function organizerShow($id)
    {
        $ticket = SupportTicket::with(['replies', 'appUser:id,name,last_name,image'])->findOrFail($id);
        $eventIds = Event::where('user_id', Auth::user()->id)->pluck('id');
        abort_unless($ticket->event_id && $eventIds->contains($ticket->event_id), 403);
        return view('admin.support.organizerShow', compact('ticket'));
    }

    private function notifyOrganizerOfTicketActivity(SupportTicket $ticket, string $message): void
    {
        if (!$ticket->event_id) {
            return;
        }
        $event = Event::find($ticket->event_id);
        if (!$event) {
            return;
        }
        Notification::create([
            'organizer_id' => $event->user_id,
            'type' => 'support_ticket',
            'link' => url('organization/support-tickets/' . $ticket->id),
            'title' => 'Support Ticket',
            'message' => $message,
        ]);
    }
}
