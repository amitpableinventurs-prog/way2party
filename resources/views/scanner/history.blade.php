@extends('scanner.layout')
@section('title', 'Scan History')
@section('body')
    <div class="topbar">
        <a href="{{ route('scannerPanel.event', $event->id) }}">&larr; {{ __('Back') }}</a>
        <h1 style="font-size:14px;">{{ __('Scan History') }}</h1>
        <a href="{{ route('scannerPanel.logout') }}">{{ __('Logout') }}</a>
    </div>
    <div class="container">
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Result') }}</th>
                        <th>{{ __('Message') }}</th>
                        <th>{{ __('Time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentScans as $scan)
                        <tr>
                            <td>{{ $scan->ticket_number }}</td>
                            <td><span class="badge {{ $scan->result }}">{{ ucfirst(str_replace('_', ' ', $scan->result)) }}</span></td>
                            <td class="muted">{{ $scan->message }}</td>
                            <td class="muted">{{ $scan->created_at->format('d M, h:i a') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">{{ __('No scans yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="color:#9aa0ab;">
            {{ $recentScans->links() }}
        </div>
    </div>
@endsection
