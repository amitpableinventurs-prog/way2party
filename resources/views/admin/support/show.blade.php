@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Ticket') . ' #' . $ticket->id,
            'headerData' => __('Support Tickets'),
            'url' => 'support-desk',
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <img class="avatar avatar-lg"
                                        src="{{ url('images/upload/' . ($ticket->appUser->image ?? 'defaultuser.png')) }}">
                                    <div class="ml-3">
                                        <h2 class="section-title mt-0">{{ $ticket->subject }}</h2>
                                        <span class="text-muted">{{ ($ticket->appUser->name ?? '') . ' ' . ($ticket->appUser->last_name ?? '') }}</span>
                                    </div>
                                </div>
                                <form method="post" action="{{ route('supportTicketStatus', $ticket->id) }}" class="form-inline">
                                    @csrf
                                    <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                                        <option value="answered" {{ $ticket->status == 'answered' ? 'selected' : '' }}>{{ __('Answered') }}</option>
                                        <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                    </select>
                                </form>
                            </div>
                            <div class="p-3" style="max-height:60vh; overflow-y:auto; background:#f8f9fa; border-radius:8px;">
                                <div class="d-flex mb-2 justify-content-start">
                                    <div class="p-2 rounded" style="max-width:70%; background:#e9ecef;">
                                        <div>{{ $ticket->message }}</div>
                                        <small style="opacity:.7;">{{ $ticket->created_at->format('d M Y, h:i a') }}</small>
                                    </div>
                                </div>
                                @foreach ($ticket->replies as $reply)
                                    <div class="d-flex mb-2 {{ $reply->sender_type == 'admin' ? 'justify-content-end' : 'justify-content-start' }}">
                                        <div class="p-2 rounded"
                                            style="max-width:70%; {{ $reply->sender_type == 'admin' ? 'background:#007bff;color:#fff;' : 'background:#e9ecef;' }}">
                                            <div>{{ $reply->body }}</div>
                                            <small style="opacity:.7;">{{ $reply->created_at->format('d M Y, h:i a') }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($ticket->status != 'closed')
                                <form method="post" action="{{ route('adminSupportTicketReply', $ticket->id) }}" class="mt-3 form-inline">
                                    @csrf
                                    <input type="text" name="body" required placeholder="{{ __('Type a reply...') }}"
                                        class="form-control mr-2" style="flex:1;">
                                    <button type="submit" class="btn btn-primary">{{ __('Send') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
