@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Membership Plans'),
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4 mt-2">
                                <div class="col-lg-8">
                                    <h2 class="section-title mt-0"> {{ __('Membership Plans') }}</h2>
                                </div>
                                <div class="col-lg-4 text-right">
                                    <button class="btn btn-primary add-button"><a href="{{ url('membership-plan/create') }}"><i
                                                class="fas fa-plus"></i> {{ __('Add New') }}</a></button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Price') }}</th>
                                            <th>{{ __('Validity') }}</th>
                                            <th>{{ __('Features') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($membershipPlans as $item)
                                            <tr>
                                                <th>
                                                    @if ($item->imagePath)
                                                        <img class="table-img" src="{{ $item->imagePath }}">
                                                    @endif
                                                </th>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->amount }}</td>
                                                <td>{{ $item->price }}</td>
                                                <td>{{ $item->duration_days ? $item->duration_days . ' ' . __('days') : __('Lifetime') }}</td>
                                                <td>{{ $item->features()->count() }}</td>
                                                <td>
                                                    <h5><span
                                                            class="badge {{ $item->status ? 'badge-success' : 'badge-warning' }} m-1">{{ $item->status ? __('Active') : __('Inactive') }}</span>
                                                    </h5>
                                                </td>
                                                <td>
                                                    <a href="{{ route('membership-plan.edit', $item->id) }}" class="btn-icon"><i
                                                            class="fas fa-edit"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">{{ __('No membership plans yet.') }}</td>
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
