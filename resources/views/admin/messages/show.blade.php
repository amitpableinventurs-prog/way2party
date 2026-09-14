@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Conversation'),
            'headerData' => __('Messages'),
            'url' => 'organization/messages',
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <img class="avatar avatar-lg"
                                    src="{{ url('images/upload/' . ($conversation->appUser->image ?? 'defaultuser.png')) }}">
                                <h2 class="section-title mt-0 ml-3">
                                    {{ ($conversation->appUser->name ?? '') . ' ' . ($conversation->appUser->last_name ?? '') }}
                                </h2>
                            </div>
                            <div class="p-3" style="max-height:60vh; overflow-y:auto; background:#f8f9fa; border-radius:8px;">
                                @forelse ($messages as $message)
                                    <div class="d-flex mb-2 {{ $message->sender_type == $viewerType ? 'justify-content-end' : 'justify-content-start' }}">
                                        <div class="p-2 rounded"
                                            style="max-width:70%; {{ $message->sender_type == $viewerType ? 'background:#007bff;color:#fff;' : 'background:#e9ecef;' }}">
                                            <div>{{ $message->body }}</div>
                                            <small style="opacity:.7;">{{ $message->created_at->format('d M Y, h:i a') }}</small>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">{{ __('No messages yet.') }}</p>
                                @endforelse
                            </div>
                            <form method="post" action="{{ url('organization/messages/' . $conversation->id) }}" class="mt-3 form-inline">
                                @csrf
                                <input type="text" name="body" required placeholder="{{ __('Type a message...') }}"
                                    class="form-control mr-2" style="flex:1;">
                                <button type="submit" class="btn btn-primary">{{ __('Send') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
