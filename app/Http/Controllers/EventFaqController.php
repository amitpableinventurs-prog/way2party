<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventFaq;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EventFaqController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $eventFaq = EventFaq::orderBy('id','DESC')->get();
        return view('admin.eventfaq.index', compact('eventFaq'));
    }

    public function indexByEvent($eventId)
    {
        $eventFaq = EventFaq::where('event_id', $eventId)->orderBy('id','DESC')->get();
        $event = Event::find($eventId);
        return view('admin.eventfaq.index', compact('eventFaq', 'event'));
    }

    public function create($eventId)
    {
        $event = Event::select('id', 'name')->find($eventId);
        return view('admin.eventfaq.create', compact('event'));
    }

    public function store(Request $request, $eventId)
    {
        if($eventId == null)
        {
            return redirect()->route('event-faq.index')->withStatus(__('Event FAQ has added failed.'));
        }

        $request->validate([
            'question' => 'bail|required',
            'answer' => 'bail|required',
        ]);

        EventFaq::create([
            'event_id' => $eventId,
            'question' => $request->question,
            'answer' => $request->answer,
        ]);
        return redirect()->route('event-faq.index', $eventId)->withStatus(__('Event FAQ has added successfully.'));
    }

    public function edit($id)
    {
        $faq = EventFaq::find($id);
        $event = Event::find($faq->event_id);
        return view('admin.eventfaq.edit', compact('faq', 'event'));
    }

    public function update(Request $request, $eventId, $id)
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'question' => 'bail|required',
            'answer' => 'bail|required',
        ]);
        $eventFaq = EventFaq::find($id);
        $eventFaq->update(
            [
                'question' => $request->question,
                'answer' => $request->answer,
            ]
        );
        $event = Event::find($eventId);
        return redirect()->route('event-faq.index', $eventId)->withStatus(__('Event FAQ has updated successfully.'));
    }

    public function destroy($id)
    {
        $eventFaq = EventFaq::find($id);
        $eventFaq->delete();
        return response()->json(['success' => true, 'message' => __('Event FAQ has deleted successfully.')]);
    }

}
