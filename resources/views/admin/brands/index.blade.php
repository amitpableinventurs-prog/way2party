@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Featured Brands'),
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
                                    <h2 class="section-title mt-0"> {{ __('Featured Brands') }}</h2>
                                </div>
                                <div class="col-lg-4 text-right">
                                        <button class="btn btn-primary add-button"><a href="{{ url('brands/create') }}"><i
                                                class="fas fa-plus"></i> {{ __('Add New') }}</a></button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table" id="report_table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('URL') }}</th>
                                            <th>{{ __('Logo Image') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($brands as $brand)
                                            <tr>
                                                <td></td>
                                                <td>{{ $brand->name }}</td>
                                                <td><a href="{{ $brand->url }}" target="_blank">{{ $brand->url }}</a></td>
                                                <td><img src="{{ asset('images/brands/' . $brand->logo_image) }}"
                                                        alt="{{ $brand->name }}" width="50"></td>
                                                <td> <span
                                                        class="badge {{ $brand->status == 1 ? 'badge-success' : 'badge-danger' }}  m-1">{{ $brand->status == 1 ? 'Active' : 'Inactive' }}</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('brands.edit', $brand->id) }}" class="btn-icon"><i
                                                            class="fas fa-edit"></i></a>
                                                    <a href="#"
                                                        onclick="deleteData('brands','{{ $brand->id }}');"
                                                        title="Delete Brand" class="btn-icon text-danger"><i
                                                            class="fas fa-trash-alt text-danger"></i></a>
                                                </td>
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
