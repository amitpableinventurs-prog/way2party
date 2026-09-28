@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Edit Tag'),
            'headerData' => __('Tags'),
            'url' => 'event-tags',
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="section-title"> {{ __('Edit Tag') }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="{{ route('event-tags.update', [$tag->id]) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label>{{ __('Name') }}</label>
                                    <input type="text" name="name" placeholder="{{ __('e.g. DJ Night') }}"
                                        value="{{ old('name', $tag->name) }}"
                                        class="form-control @error('name')? is-invalid @enderror">
                                    <small class="form-text text-muted">{{ __('Letters, numbers and spaces only.') }}</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>{{ __('status') }}</label>
                                    <select name="status" class="form-control select2">
                                        <option value="1" {{ old('status', $tag->status) == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="0" {{ old('status', $tag->status) == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
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
