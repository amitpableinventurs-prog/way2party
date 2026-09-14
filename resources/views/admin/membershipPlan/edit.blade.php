@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Edit Membership Plan'),
            'headerData' => __('Membership Plans'),
            'url' => 'membership-plan',
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="{{ route('membership-plan.update', $membershipPlan->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group center">
                                            <label>{{ __('Image') }}</label>
                                            <div id="image-preview" class="image-preview"
                                                style="{{ $membershipPlan->imagePath ? 'background-image:url(' . $membershipPlan->imagePath . ')' : '' }}">
                                                <label for="image-upload" id="image-label"> <i
                                                        class="fas fa-plus"></i></label>
                                                <input type="file" name="image" id="image-upload" />
                                            </div>
                                            @error('image')
                                                <div class="invalid-feedback block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>{{ __('Title') }}</label>
                                                    <input type="text" name="name"
                                                        value="{{ old('name', $membershipPlan->name) }}"
                                                        class="form-control @error('name')? is-invalid @enderror">
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label>{{ __('Amount') }}</label>
                                                    <input type="number" step="0.01" min="0" name="amount"
                                                        value="{{ old('amount', $membershipPlan->amount) }}"
                                                        class="form-control @error('amount')? is-invalid @enderror">
                                                    @error('amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label>{{ __('Price') }}</label>
                                                    <input type="number" step="0.01" min="0" name="price"
                                                        value="{{ old('price', $membershipPlan->price) }}"
                                                        class="form-control @error('price')? is-invalid @enderror">
                                                    @error('price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>{{ __('Validity Days') }}</label>
                                                    <input type="number" min="1" name="duration_days"
                                                        value="{{ old('duration_days', $membershipPlan->duration_days) }}"
                                                        placeholder="{{ __('Blank = lifetime') }}"
                                                        class="form-control @error('duration_days')? is-invalid @enderror">
                                                    @error('duration_days')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label>{{ __('Sort Order') }}</label>
                                                    <input type="number" min="0" name="sort_order"
                                                        value="{{ old('sort_order', $membershipPlan->sort_order) }}"
                                                        class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label>{{ __('Status') }}</label>
                                                    <select name="status" class="form-control select2">
                                                        <option value="1" {{ $membershipPlan->status ? 'selected' : '' }}>{{ __('Active') }}</option>
                                                        <option value="0" {{ !$membershipPlan->status ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('Description') }}</label>
                                            <textarea name="description" rows="4"
                                                class="form-control @error('description')? is-invalid @enderror">{{ old('description', $membershipPlan->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Feature List') }}</label>
                                    <div class="border rounded p-3">
                                        @forelse ($features as $feature)
                                            <div class="custom-checkbox custom-control">
                                                <input type="checkbox" name="features[]" value="{{ $feature->id }}"
                                                    id="feature-{{ $feature->id }}" class="custom-control-input"
                                                    {{ in_array($feature->id, old('features', $selectedFeatures)) ? 'checked' : '' }}>
                                                <label class="custom-control-label"
                                                    for="feature-{{ $feature->id }}">{{ $feature->name }}</label>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0">{{ __('No features defined yet. Add one below.') }}</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary demo-button">{{ __('Submit') }}</button>
                                </div>
                            </form>

                            <hr>
                            <h6 class="text-muted">{{ __('Add a new feature to the catalog') }}</h6>
                            <form method="post" action="{{ route('membership-feature.store') }}" class="form-inline">
                                @csrf
                                <input type="text" name="name" required placeholder="{{ __('e.g. Priority support') }}"
                                    class="form-control mr-2">
                                <button type="submit" class="btn btn-secondary">{{ __('Add Feature') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
