<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Whether the current request is the customer (AppUser) side of the
     * messaging feature, vs. the organizer (User) side.
     */
    private function isAppUser()
    {
        return Auth::guard('appuser')->check();
    }

    public function index()
    {
        if ($this->isAppUser()) {
            $appUserId = Auth::guard('appuser')->user()->id;
            $conversations = Conversation::with(['organizer:id,first_name,last_name,organization_name,image'])
                ->where('app_user_id', $appUserId)
                ->orderByDesc('last_message_at')
                ->get();
            return view('frontend.messages.index', compact('conversations'));
        }

        $userId = Auth::user()->id;
        $conversations = Conversation::with(['appUser:id,name,last_name,image'])
            ->where('user_id', $userId)
            ->orderByDesc('last_message_at')
            ->get();
        return view('admin.messages.index', compact('conversations'));
    }

    public function show($id)
    {
        $conversation = Conversation::with(['appUser:id,name,last_name,image', 'organizer:id,first_name,last_name,organization_name,image'])->findOrFail($id);
        $viewerType = $this->authorizeAndGetViewerType($conversation);

        $conversation->messages()
            ->where('sender_type', '!=', $viewerType)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->orderBy('created_at')->get();

        if ($viewerType == 'app_user') {
            return view('frontend.messages.show', compact('conversation', 'messages', 'viewerType'));
        }
        return view('admin.messages.show', compact('conversation', 'messages', 'viewerType'));
    }

    public function store(Request $request, $id)
    {
        $request->validate(['body' => 'bail|required|string']);
        $conversation = Conversation::findOrFail($id);
        $viewerType = $this->authorizeAndGetViewerType($conversation);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => $viewerType,
            'sender_id' => $viewerType == 'app_user' ? Auth::guard('appuser')->user()->id : Auth::user()->id,
            'body' => $request->body,
        ]);
        $conversation->update(['last_message_at' => now()]);

        if ($viewerType == 'app_user') {
            $sender = Auth::guard('appuser')->user();
            Notification::create([
                'organizer_id' => $conversation->user_id,
                'type' => 'message',
                'link' => url('organization/messages/' . $conversation->id),
                'title' => 'New Message',
                'message' => trim($sender->name . ' ' . $sender->last_name) . ' sent you a message.',
            ]);
        }

        return redirect()->back();
    }

    public function startWithOrganizer(Request $request, $organizerId)
    {
        if (!$this->isAppUser()) {
            abort(403);
        }
        $organizer = User::findOrFail($organizerId);
        if (!$organizer->hasRole('Organizer')) {
            abort(404);
        }
        $conversation = Conversation::firstOrCreate([
            'app_user_id' => Auth::guard('appuser')->user()->id,
            'user_id' => $organizerId,
            'event_id' => $request->get('event_id'),
        ], [
            'last_message_at' => now(),
        ]);
        return redirect()->to('/messages/' . $conversation->id);
    }

    public function startWithCustomer(Request $request, $appUserId)
    {
        if ($this->isAppUser() || !Auth::check()) {
            abort(403);
        }
        AppUser::findOrFail($appUserId);
        $conversation = Conversation::firstOrCreate([
            'app_user_id' => $appUserId,
            'user_id' => Auth::user()->id,
            'event_id' => $request->get('event_id'),
        ], [
            'last_message_at' => now(),
        ]);
        return redirect()->to('/organization/messages/' . $conversation->id);
    }

    private function authorizeAndGetViewerType(Conversation $conversation)
    {
        if ($this->isAppUser()) {
            if ($conversation->app_user_id != Auth::guard('appuser')->user()->id) {
                abort(403);
            }
            return 'app_user';
        }
        if (!Auth::check() || $conversation->user_id != Auth::user()->id) {
            abort(403);
        }
        return 'user';
    }
}
