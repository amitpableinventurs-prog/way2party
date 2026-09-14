@extends('frontend.master', ['activePage' => 'support'])
@section('title', __('New Support Ticket'))
@section('content')
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <p class="font-poppins font-semibold text-4xl leading-10 text-black pt-5">{{ __('New Support Ticket') }}</p>
            <form action="{{ route('storeSupportTicket') }}" method="post"
                class="mt-8 space-y-5 1xl:w-[45%] xl:w-[55%] lg:w-[70%] w-full">
                @csrf
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Subject') }}</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">
                    @error('subject')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Related Event (optional)') }}</label>
                    <select name="event_id"
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">
                        <option value="">{{ __('Not related to a specific event') }}</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Priority') }}</label>
                    <select name="priority"
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                    </select>
                </div>
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Message') }}</label>
                    <textarea name="message" rows="6" required
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="bg-primary text-white font-poppins font-medium text-base leading-6 px-8 py-3 rounded-md">{{ __('Submit Ticket') }}</button>
            </form>
        </div>
    </div>
@endsection
