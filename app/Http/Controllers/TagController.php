<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Tag;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('tag_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $tags = Tag::orderBy('name')->get();
        foreach ($tags as $tag) {
            $tag->events_count = Event::where('is_deleted', 0)->withTag($tag->name)->count();
        }
        return view('admin.tag.index', compact('tags'));
    }

    public function create()
    {
        abort_if(Gate::denies('tag_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.tag.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('tag_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request->validate($this->rules());
        Tag::create(['name' => trim($request->name), 'status' => $request->status]);
        return redirect()->route('event-tags.index')->withStatus(__('Tag has added successfully.'));
    }

    public function edit(Tag $event_tag)
    {
        abort_if(Gate::denies('tag_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.tag.edit', ['tag' => $event_tag]);
    }

    public function update(Request $request, Tag $event_tag)
    {
        abort_if(Gate::denies('tag_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $request->validate($this->rules($event_tag->id));
        $oldName = $event_tag->name;
        $newName = trim($request->name);
        $event_tag->update(['name' => $newName, 'status' => $request->status]);

        // events store tag names, so carry a rename over to them
        if ($oldName !== $newName) {
            Event::withTag($oldName)->get(['id', 'tags'])->each(function ($event) use ($oldName, $newName) {
                $list = array_map(fn ($t) => strcasecmp($t, $oldName) === 0 ? $newName : $t, $event->tag_list);
                Event::where('id', $event->id)->update(['tags' => implode(',', $list)]);
            });
        }
        return redirect()->route('event-tags.index')->withStatus(__('Tag has updated successfully.'));
    }

    private function rules($ignoreId = null)
    {
        return [
            // letters, numbers and spaces only: a comma would split the tag in events.tags
            'name' => ['bail', 'required', 'max:50', 'regex:/^[a-zA-Z0-9\s]+$/', Rule::unique('tags', 'name')->ignore($ignoreId)],
            'status' => 'bail|required|in:0,1',
        ];
    }
}
