@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Edit Event'),
            'headerData' => __('Event'),
            'url' => 'events',
        ])

        <div class="section-body">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="section-title"> {{ __('Edit Event') }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" class="event-form" action="{{ route('events.update', [$event->id]) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group center">
                                            <label>{{ __('Image') }}</label>
                                            <div id="image-preview" class="image-preview"
                                                style="background-image: url({{ url('images/upload/' . $event->image) }})">
                                                <label for="image-upload" id="image-label"> <i
                                                        class="fas fa-plus"></i></label>
                                                <input type="file" name="image" id="image-upload" />
                                            </div>
                                            @error('image')
                                                <div class="invalid-feedback block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Name') }}</label>
                                            <input type="text" name="name" value="{{ old('name', $event->name) }}"
                                                placeholder="{{ __('Name') }}"
                                                class="form-control @error('name')? is-invalid @enderror">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('Party Type') }} {{ __('(Choose Multiple if required.)') }}</label>
                                            <select name="category_id[]" class="form-control select2" multiple data-live-search="true">
                                                @php
                                                    $selectedCategories = old('category_id', $event->categories->pluck('id')->all());
                                                @endphp
                                                @foreach ($category as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ in_array($item->id, $selectedCategories) ? 'Selected' : '' }}>
                                                        {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <div class="invalid-feedback block">{{ $message }}</div>
                                            @enderror
                                            @error('category_id.*')
                                                <div class="invalid-feedback block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('City') }}</label>
                                            <select name="city_id" class="form-control select2">
                                                <option value="">{{ __('Select City') }}</option>
                                                @foreach ($city as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ $item->id == old('city_id', $event->city_id) ? 'Selected' : '' }}>
                                                        {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('city_id')
                                                <div class="invalid-feedback block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Start Time') }}</label>
                                            <input type="text" name="start_time" id="start_time"
                                                value="{{ old('start_time', $event->start_time) }}"
                                                placeholder="{{ __('Choose Start time') }}"
                                                class="form-control date @error('start_time')? is-invalid @enderror">
                                            @error('start_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('End Time') }}</label>
                                            <input type="text" name="end_time" id="end_time"
                                                value="{{ old('end_time', $event->end_time) }}" placeholder="{{ __('Choose End time') }}"
                                                class="form-control date @error('end_time')? is-invalid @enderror">
                                            @error('end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                @if (Auth::user()->hasRole('admin'))
                                    <div class="form-group">
                                        <label>{{ __('Organizer') }}</label>
                                        <select name="user_id" class="form-control select2" id="org-for-event">
                                            <option value="">{{ __('Choose Organizer') }}</option>
                                            @foreach ($users as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $item->id == old('user_id', $event->user_id) ? 'Selected' : '' }}>
                                                    {{ $item->first_name . ' ' . $item->last_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                                <div class="scanner demo">
                                    <div class="form-group">
                                        <label>{{ __('Scanner') }} {{ __('(Requierd)') }}</label>
                                        <select name="scanner_id[]" class="form-control scanner_id select2 selectpicker"
                                            multiple data-live-search="true">
                                            <option value="" disabled>{{ __('Choose Scanner') }}</option>
                                            @php
                                                $selectedScanners = old('scanner_id')
                                                    ?? array_filter(preg_split('/\s*,\s*/', (string) $event->scanner_id));
                                            @endphp
                                            @foreach ($scanner as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ in_array($item->id, $selectedScanners) ? 'selected' : '' }}>
                                                    {{ $item->first_name . ' ' . $item->last_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('scanner_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Maximum people will join in this event') }}</label>
                                            <input type="number" name="people" id="people"
                                                value="{{ old('people', $event->people) }}"
                                                placeholder="{{ __('Maximum people will join in this event') }}"
                                                class="form-control @error('people')? is-invalid @enderror">
                                            @error('people')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('status') }}</label>
                                            <select name="status" class="form-control select2">
                                                <option value="1" {{ old('status', $event->status) == '1' ? 'selected' : '' }}>
                                                    {{ __('Active') }}</option>
                                                <option value="0" {{ old('status', $event->status) == '0' ? 'selected' : '' }}>
                                                    {{ __('Inactive') }}</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Featured Event') }}</label>
                                            <select name="is_featured" id="is_featured" class="form-control select2">
                                                @php $isFeatured = old('is_featured', $event->is_featured); @endphp
                                                <option value="0" {{ $isFeatured ? '' : 'selected' }}>{{ __('No') }}</option>
                                                <option value="1" {{ $isFeatured ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                            </select>
                                            <small class="text-muted">{{ __('Featured events are highlighted on the home page.') }}</small>
                                            @error('is_featured')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Featured Priority') }}</label>
                                            <input type="number" min="0" name="featured_order" id="featured_order"
                                                value="{{ old('featured_order', $event->featured_order) }}" placeholder="0"
                                                class="form-control @error('featured_order')? is-invalid @enderror">
                                            <small class="text-muted">{{ __('Higher number shows first in the Featured Events section.') }}</small>
                                            @error('featured_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Visibility') }}</label>
                                            <select name="visibility" id="visibility" class="form-control select2">
                                                <option value="everyone" {{ old('visibility', $event->visibility) == 'everyone' ? 'selected' : '' }}>{{ __('Visible to Everyone') }}</option>
                                                <option value="members_only" {{ old('visibility', $event->visibility) == 'members_only' ? 'selected' : '' }}>{{ __('Members Only') }}</option>
                                                <option value="previously_attended" {{ old('visibility', $event->visibility) == 'previously_attended' ? 'selected' : '' }}>{{ __('Previously Attended Users Only') }}</option>
                                                <option value="hidden" {{ old('visibility', $event->visibility) == 'hidden' ? 'selected' : '' }}>{{ __('Hidden from Everyone') }}</option>
                                            </select>
                                            @error('visibility')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Ticket Cancellation') }}</label>
                                            <select name="cancellation_allowed" id="cancellation_allowed" class="form-control select2">
                                                @php $cancellationAllowed = old('cancellation_allowed', $event->cancellation_allowed); @endphp
                                                <option value="0" {{ $cancellationAllowed ? '' : 'selected' }}>{{ __('Not Allowed') }}</option>
                                                <option value="1" {{ $cancellationAllowed ? 'selected' : '' }}>{{ __('Allowed') }}</option>
                                            </select>
                                            @error('cancellation_allowed')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Cancellation Charge') }}</label>
                                            <input type="number" min="0" step="0.01" name="cancellation_charges" id="cancellation_charges"
                                                value="{{ old('cancellation_charges', $event->cancellation_charges) }}" placeholder="0"
                                                class="form-control @error('cancellation_charges')? is-invalid @enderror">
                                            @error('cancellation_charges')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Tags') }}</label>
                                    <input type="text" name="tags" value="{{ old('tags', $event->tags) }}"
                                        class="form-control inputtags @error('tags')? is-invalid @enderror">
                                    @error('tags')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Description') }}</label>
                                    <textarea name="description" Placeholder="{{ __('Description') }}"
                                        class="textarea_editor @error('description')? is-invalid @enderror">
                                {{ old('description', $event->description) }}
                            </textarea>
                                    @error('description')
                                        <div class="invalid-feedback block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <h6 class="text-muted mt-4 mb-4">{{ __('Location Detail') }}</h6>
                                <div class="location-detail">
                                    <div class="form-group">
                                        <label>{{ __('Event Address') }}</label>
                                        <input type="text" name="address" id="address"
                                            value="{{ old('address', $event->address) }}" placeholder="{{ __('Event Address') }}"
                                            class="form-control @error('address')? is-invalid @enderror">
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Latitude') }}</label>
                                                <input type="text" name="lat" id="lat"
                                                    value="{{ old('lat', $event->lat) }}" placeholder="{{ __('Latitude') }}"
                                                    class="form-control @error('lat')? is-invalid @enderror">
                                                @error('lat')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Longitude') }}</label>
                                                <input type="text" name="lang" id="lang"
                                                    value="{{ old('lang', $event->lang) }}" placeholder="{{ __('Longitude') }}"
                                                    class="form-control @error('lang')? is-invalid @enderror">
                                                @error('lang')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit"
                                        class="btn btn-primary demo-button">{{ __('Submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @php
        $gmapkey = App\Models\Setting::find(1)->map_key;
    @endphp
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key={{ $gmapkey }}&libraries=places">
    </script>

    <script>
        google.maps.event.addDomListener(window, 'load', initialize);

        function initialize() {
            var input = document.getElementById('address');
            var autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                $('#lat').val(place.geometry['location'].lat());
                $('#lang').val(place.geometry['location'].lng());
            });
        }
    </script>
    <style>
        .modal-backdrop {
            display: none;
        }
    </style>
@endsection
