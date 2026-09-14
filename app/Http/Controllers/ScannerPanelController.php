<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderChild;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketScan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ScannerPanelController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !Auth::user()->hasRole('scanner')) {
                return redirect()->route('scannerPanel.login')->with('error_msg', __('Please log in as a scanner to continue.'));
            }
            if (Auth::user()->status != 1) {
                Auth::logout();
                return redirect()->route('scannerPanel.login')->with('error_msg', __('Your scanner account has been blocked. Contact your organizer.'));
            }
            return $next($request);
        })->except(['showLogin', 'login']);
    }

    public function showLogin()
    {
        if (Auth::check() && Auth::user()->hasRole('scanner')) {
            return redirect()->route('scannerPanel.events');
        }
        return view('scanner.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'bail|required|email',
            'password' => 'bail|required',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->back()->withInput()->with('error_msg', __('Invalid email or password.'));
        }

        if (!Auth::user()->hasRole('scanner')) {
            Auth::logout();
            return redirect()->back()->withInput()->with('error_msg', __('This login is only for scanner accounts.'));
        }

        if (Auth::user()->status != 1) {
            Auth::logout();
            return redirect()->back()->withInput()->with('error_msg', __('Your scanner account has been blocked. Contact your organizer.'));
        }

        return redirect()->route('scannerPanel.events');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('scannerPanel.login');
    }

    public function events()
    {
        (new AppHelper)->eventStatusChange();
        $scannerId = Auth::user()->id;
        $timezone = Setting::find(1)->timezone;
        $date = Carbon::now($timezone);

        $events = Event::where([['status', 1], ['is_deleted', 0]])
            ->where('end_time', '>', $date->copy()->subDay())
            ->orderBy('start_time', 'asc')
            ->get()
            ->filter(fn ($event) => $this->scannerAssignedToEvent($event, $scannerId))
            ->values();

        foreach ($events as $event) {
            $orderIds = Order::where('event_id', $event->id)->pluck('id');
            $event->total_tickets = OrderChild::whereIn('order_id', $orderIds)->count();
            $event->checked_in_count = OrderChild::whereIn('order_id', $orderIds)->where('status', 1)->count();
        }

        return view('scanner.events', compact('events'));
    }

    public function showEvent($id)
    {
        $event = Event::findOrFail($id);
        $this->authorizeEvent($event);

        $orderIds = Order::where('event_id', $event->id)->pluck('id');
        $event->total_tickets = OrderChild::whereIn('order_id', $orderIds)->count();
        $event->checked_in_count = OrderChild::whereIn('order_id', $orderIds)->where('status', 1)->count();

        $recentScans = TicketScan::where('event_id', $event->id)
            ->where('scanner_id', Auth::user()->id)
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('scanner.event', compact('event', 'recentScans'));
    }

    public function scan(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $this->authorizeEvent($event);

        $request->validate(['code' => 'bail|required|string']);
        $code = trim($request->code);

        $result = $this->validateAndCheckIn($event, $code);

        TicketScan::create([
            'scanner_id' => Auth::user()->id,
            'event_id' => $event->id,
            'order_child_id' => $result['order_child_id'] ?? null,
            'ticket_number' => $code,
            'result' => $result['status'],
            'message' => $result['message'],
        ]);

        return response()->json($result);
    }

    public function history($id)
    {
        $event = Event::findOrFail($id);
        $this->authorizeEvent($event);

        $recentScans = TicketScan::where('event_id', $event->id)
            ->where('scanner_id', Auth::user()->id)
            ->orderByDesc('id')
            ->paginate(30);

        return view('scanner.history', compact('event', 'recentScans'));
    }

    /**
     * Runs the same checks the mobile scanner API uses (order confirmed,
     * not cancelled, check-ins remaining, date/time window), but returns a
     * category from success|already_used|cancelled|invalid|expired instead
     * of a single success flag, so the web UI can show a specific reason.
     */
    private function validateAndCheckIn(Event $event, string $code): array
    {
        $child = OrderChild::where('ticket_number', $code)->first();
        if (!$child) {
            return ['status' => 'invalid', 'message' => __('Ticket code not found.')];
        }

        $ticket = Ticket::find($child->ticket_id);
        if (!$ticket || $ticket->event_id != $event->id) {
            return ['status' => 'invalid', 'message' => __('This ticket does not belong to this event.'), 'order_child_id' => $child->id];
        }

        $order = Order::find($child->order_id);
        if (!$order) {
            return ['status' => 'invalid', 'message' => __('Order not found for this ticket.'), 'order_child_id' => $child->id];
        }

        if ($order->order_status == 'Cancelled') {
            return ['status' => 'cancelled', 'message' => __('This booking was cancelled.'), 'order_child_id' => $child->id];
        }

        if ($order->order_status != 'Complete') {
            return ['status' => 'invalid', 'message' => __('This order has not been confirmed yet.'), 'order_child_id' => $child->id];
        }

        if ($child->checkin !== null && $child->checkin <= 0) {
            return ['status' => 'already_used', 'message' => __('This ticket has already been fully checked in.'), 'order_child_id' => $child->id];
        }

        $timezone = Setting::find(1)->timezone;
        $now = Carbon::now($timezone);
        if ($ticket->allday == 0) {
            if ($order->ticket_date && Carbon::parse($order->ticket_date)->format('Y-m-d') != $now->format('Y-m-d')) {
                return ['status' => 'expired', 'message' => __('This ticket is not valid today.'), 'order_child_id' => $child->id];
            }
        } else {
            if (!$now->between(Carbon::parse($event->start_time), Carbon::parse($event->end_time))) {
                return ['status' => 'expired', 'message' => __('This ticket is outside the event\'s valid time window.'), 'order_child_id' => $child->id];
            }
        }

        $customer = AppUser::find($child->customer_id);
        $holderName = trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? '')) ?: __('Guest');

        if ($child->checkin === null) {
            $child->update(['status' => 1, 'paid' => 1]);
            $remaining = __('Unlimited');
        } else {
            $newCheckin = max(0, $child->checkin - 1);
            $child->update(['checkin' => $newCheckin, 'status' => 1, 'paid' => 1]);
            $remaining = $newCheckin;
        }

        return [
            'status' => 'success',
            'message' => __('Checked in successfully.'),
            'order_child_id' => $child->id,
            'holder_name' => $holderName,
            'ticket_type' => $ticket->name,
            'remaining_check_ins' => $remaining,
        ];
    }

    private function scannerAssignedToEvent(Event $event, $scannerId): bool
    {
        $ids = array_filter(array_map('trim', explode(',', (string) $event->scanner_id)));
        return in_array((string) $scannerId, $ids, true);
    }

    private function authorizeEvent(Event $event): void
    {
        abort_unless($this->scannerAssignedToEvent($event, Auth::user()->id), 403, __('You are not assigned to this event.'));
    }
}
