@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Edit City'),
            'headerData' => __('City'),
            'url' => 'city',
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="section-title"> {{ __('Edit City') }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="{{ route('city.update', [$city->id]) }}">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label>{{ __('Name') }}</label>
                                    <input type="text" name="name" placeholder="{{ __('Name') }}"
                                        value="{{ $city->name }}"
                                        class="form-control @error('name') ? is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>{{ __('status') }}</label>
                                    <select name="status" class="form-control select2">
                                        <option value="1" {{ $city->status == '1' ? 'Selected' : '' }}>
                                            {{ __('Active') }}</option>
                                        <option value="0" {{ $city->status == '0' ? 'Selected' : '' }}>
                                            {{ __('Inactive') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary demo-button">{{ __('Submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
