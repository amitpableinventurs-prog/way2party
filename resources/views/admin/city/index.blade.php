@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('City'),
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
                                    <h2 class="section-title mt-0"> {{ __('View City') }}</h2>
                                </div>
                                <div class="col-lg-4 text-right">
                                    @can('city_create')
                                        <button class="btn btn-primary add-button"><a href="{{ url('city/create') }}"><i
                                                    class="fas fa-plus"></i> {{ __('Add New') }}</a></button>
                                    @endcan
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table" id="report_table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Total Events') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            @if (Gate::check('city_edit') || Gate::check('city_delete'))
                                                <th>{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($city as $item)
                                            <tr>
                                                <td></td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ App\Models\Event::where([['is_deleted', 0], ['city_id', $item->id]])->count() }}
                                                </td>
                                                <td>
                                                    <h5><span
                                                            class="badge {{ $item->status == '1' ? 'badge-success' : 'badge-warning' }}  m-1">{{ $item->status == '1' ? 'Active' : 'Inactive' }}</span>
                                                    </h5>
                                                </td>
                                                @if (Gate::check('city_edit') || Gate::check('city_delete'))
                                                    <td>
                                                        @can('city_edit')
                                                            <a href="{{ route('city.edit', $item->id) }}"
                                                                class="btn-icon"><i class="fas fa-edit"></i></a>
                                                        @endcan
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
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
