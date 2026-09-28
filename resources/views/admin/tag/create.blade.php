@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Add Tag'),
            'headerData' => __('Tags'),
            'url' => 'event-tags',
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="section-title"> {{ __('Add Tag') }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="{{ url('event-tags') }}">
                                @csrf

                                <div class="form-group">
                                    <label>{{ __('Name') }}</label>
                                    <input type="text" name="name" placeholder="{{ __('e.g. DJ Night') }}"
                                        value="{{ old('name') }}"
                                        class="form-control @error('name')? is-invalid @enderror">
                                    <small class="form-text text-muted">{{ __('Letters, numbers and spaces only.') }}</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>{{ __('status') }}</label>
                                    <select name="status" class="form-control select2">
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="0" {{ old('status', '1') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                    </select>
                                    <small class="form-text text-muted">{{ __('Inactive tags are hidden from the Add Event form.') }}</small>
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
