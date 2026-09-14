@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Support Tickets'),
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4 mt-2">
                                <div class="col-lg-8">
                                    <h2 class="section-title mt-0"> {{ __('Support Tickets') }}</h2>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ __('Customer') }}</th>
                                            <th>{{ __('Subject') }}</th>
                                            <th>{{ __('Priority') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Last Reply') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($tickets as $ticket)
                                            <tr>
                                                <td></td>
                                                <th><img class="avatar avatar-lg"
                                                        src="{{ url('images/upload/' . ($ticket->appUser->image ?? 'defaultuser.png')) }}"></th>
                                                <td>{{ $ticket->subject }}</td>
                                                <td>{{ ucfirst($ticket->priority) }}</td>
                                                <td>
                                                    <span
                                                        class="badge {{ $ticket->status == 'open' ? 'badge-warning' : ($ticket->status == 'answered' ? 'badge-success' : 'badge-secondary') }}">{{ ucfirst($ticket->status) }}</span>
                                                </td>
                                                <td>{{ $ticket->last_reply_at ? \Carbon\Carbon::parse($ticket->last_reply_at)->diffForHumans() : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('adminSupportTicketShow', $ticket->id) }}" class="btn btn-primary">
                                                        {{ __('Open') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">{{ __('No support tickets yet.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
