@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Messages'),
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4 mt-2">
                                <div class="col-lg-8">
                                    <h2 class="section-title mt-0"> {{ __('Messages') }}</h2>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ __('Customer') }}</th>
                                            <th>{{ __('Last Message') }}</th>
                                            <th>{{ __('Unread') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($conversations as $item)
                                            <tr>
                                                <td></td>
                                                <th><img class="avatar avatar-lg"
                                                        src="{{ url('images/upload/' . ($item->appUser->image ?? 'defaultuser.png')) }}"></th>
                                                <td>{{ ($item->appUser->name ?? '') . ' ' . ($item->appUser->last_name ?? '') }}</td>
                                                <td>{{ $item->last_message_at ? \Carbon\Carbon::parse($item->last_message_at)->diffForHumans() : '-' }}</td>
                                                <td>
                                                    @if ($item->unreadCountFor('user') > 0)
                                                        <span class="badge badge-primary">{{ $item->unreadCountFor('user') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('organization/messages/' . $item->id) }}" class="btn btn-primary">
                                                        {{ __('Open') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">{{ __('No conversations yet.') }}</td>
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
