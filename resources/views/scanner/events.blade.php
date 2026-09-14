@extends('scanner.layout')
@section('title', 'My Events')
@section('body')
    <div class="topbar">
        <h1>{{ __('My Events') }}</h1>
        <a href="{{ route('scannerPanel.logout') }}">{{ __('Logout') }}</a>
    </div>
    <div class="container">
        @if (session('error_msg'))
            <div class="flash error">{{ session('error_msg') }}</div>
        @endif

        @forelse ($events as $event)
            <a href="{{ route('scannerPanel.event', $event->id) }}" style="text-decoration:none; color:inherit;">
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div style="font-weight:600; font-size:16px;">{{ $event->name }}</div>
                            <div class="muted" style="margin-top:4px;">
                                {{ \Carbon\Carbon::parse($event->start_time)->format('d M Y, h:i a') }}
                            </div>
                            @if ($event->address)
                                <div class="muted" style="margin-top:2px;">{{ \Illuminate\Support\Str::limit($event->address, 50) }}</div>
                            @endif
                        </div>
                        <div style="text-align:right; white-space:nowrap;">
                            <div style="font-weight:700; font-size:18px;">{{ $event->checked_in_count }}/{{ $event->total_tickets }}</div>
                            <div class="muted" style="font-size:11px;">{{ __('checked in') }}</div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="card" style="text-align:center;">
                <p class="muted">{{ __('You are not assigned to any events yet. Ask your organizer to add you as a scanner on an event.') }}</p>
            </div>
        @endforelse
    </div>
@endsection
