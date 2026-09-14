@extends('frontend.master', ['activePage' => 'orgDetail'])
@section('title', __('Organizer Details'))
@section('content')

    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">

        {{-- scroll --}}
        {{-- <div class="mr-4 flex justify-end z-20">
            <a type="button" href="{{ url('#') }}" class="back-to-top bg-primary rounded-full p-4 fixed z-20  mt-72">
                <img src="{{ asset('images/downarrow.png') }}" alt="" class="w-3 h-3 z-20">
            </a>
        </div> --}}

        {{-- main --}}
        <div
            class=" 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-0 z-10 relative mt-10">
            <div class="shadow-2xl p-5 rounded-lg bg-white flex sm:flex-nowrap msm:flex-wrap xxsm:flex-wrap mb-5">
                <div class="flex sm:flex-nowrap msm:flex-wrap xxsm:flex-wrap">
                    <img src="{{ asset('images/upload/' . $data->image) }}" alt=""
                        class="h-32 w-32 object-cover bg-cover  rounded-full">
                    <div class="mt-4 ml-3 mr-3">
                        <p class="font-poppins font-semibold text-4xl leading-7 text-black">{{ ($data->first_name ?? '') . ' ' .( $data->last_name ?? '')}}</p>
                        <div class="flex flex-col mt-8 xl:flex-row xxsm:w-[99%] md:w-full">
                            <div class="mb-4 sm:mb-0">
                                <p class="font-poppins font-normal text-lg leading-7 text-gray-200">{{ __('Past Events Organized') }}</p>
                                <p class="font-poppins font-medium text-xl leading-7 text-black">{{ $data->past_events_count }}</p>
                            </div>
                            @if ($data->country)
                                <div class="sm:mx-10 sm:border-l sm:border-r sm:border-gray-light sm:px-10 w-full sm:w-auto mb-4 sm:mb-0">
                                    <p class="font-poppins font-normal text-lg leading-7 text-gray-200">{{ __('From') }}</p>
                                    <p class="font-poppins font-medium text-xl leading-7 text-black">{{ $data->country }}</p>
                                </div>
                            @else
                                <div class="sm:mx-10 sm:border-l sm:border-gray-light w-full sm:w-auto mb-4 sm:mb-0"></div>
                            @endif
                            <div class="">
                                <p class="font-poppins font-normal text-lg leading-7 text-gray-200 md:mt-5 xl:mt-0">{{ __('Rating') }}</p>
                                <p class="font-poppins font-medium text-xl leading-7 text-black">
                                    @if ($data->avg_rating)
                                        {{ $data->avg_rating }} / 5 <span class="text-gray-200 text-base">({{ count($data->reviews) }} {{ __('reviews') }})</span>
                                    @else
                                        {{ __('No ratings yet') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if ( $data->bio)
                            <div class="mt-8 w-[95%] md:w-full">
                                <p class="font-poppins font-normal text-sm leading-7 text-gray-200">{{ __('Bio') }}</p>
                                <p class="font-poppins font-normal text-base leading-6 text-gray">{{ $data->bio }}</p>
                            </div>
                        @endif
                    </div>
                    @if (Auth::guard('appuser')->user())
                        <div class="flex flex-col space-y-2">
                            <button type="button" onclick="follow({{ $data->id }})"
                                class="px-10 py-3 text-white bg-primary text-center font-poppins font-normal text-base leading-6 rounded-md">{{ in_array($data->id, array_filter(explode(',', Auth::guard('appuser')->user()->following))) == true ? __('Unfollow') : __('Follow') . ' +' }}</button>
                            <a href="{{ url('/message-organizer/' . $data->id) }}"
                                class="px-10 py-3 text-primary border border-primary text-center font-poppins font-normal text-base leading-6 rounded-md">{{ __('Message') }}</a>
                        </div>
                    @endif
                </div>
                <div class="px-4 pb-2 pt-4 w-full">
                    <p class="font-poppins font-normal text-sm leading-5 text-gray-200 pb-2">{{ __('Share this organizer') }}</p>
                    <div class="flex items-center space-x-2 flex-wrap">
                        <input type="text" readonly id="orgShareUrl" value="{{ url()->current() }}"
                            class="text-xs font-poppins text-gray-300 border border-gray-light rounded-md px-3 py-2 w-64 max-w-full"
                            onclick="this.select();">
                        <button type="button" onclick="copyOrgShareUrl()"
                            class="px-3 py-2 text-xs text-white bg-primary rounded-md font-poppins">{{ __('Copy Link') }}</button>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="px-3 py-2 text-xs text-white bg-blue rounded-md font-poppins">Facebook</a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode(($data->first_name ?? '') . ' ' . ($data->last_name ?? '')) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="px-3 py-2 text-xs text-white bg-black rounded-md font-poppins">Twitter</a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode(($data->first_name ?? '') . ' ' . ($data->last_name ?? '') . ' ' . url()->current()) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="px-3 py-2 text-xs text-white bg-success rounded-md font-poppins">WhatsApp</a>
                    </div>
                    <script>
                        function copyOrgShareUrl() {
                            var input = document.getElementById('orgShareUrl');
                            input.select();
                            input.setSelectionRange(0, 99999);
                            navigator.clipboard.writeText(input.value);
                        }
                    </script>
                </div>
            </div>
            {{-- Reviews --}}
            <p class="font-poppins font-semibold text-2xl leading-6 text-black pt-5">{{ __('Reviews') }}&nbsp;( {{ count($data->reviews) }} )</p>
            <div class="mt-5">
                @forelse ($data->reviews as $review)
                    <div class="shadow-2xl p-5 rounded-lg bg-white mb-4">
                        <div class="flex items-center justify-between">
                            <p class="font-poppins font-semibold text-lg leading-6 text-black">{{ $review->user->name ?? '' }} {{ $review->user->last_name ?? '' }}</p>
                            <p class="font-poppins font-medium text-base leading-6 text-black">{{ $review->rate }} / 5</p>
                        </div>
                        @if ($review->message)
                            <p class="font-poppins font-normal text-base leading-6 text-gray pt-2">{{ $review->message }}</p>
                        @endif
                    </div>
                @empty
                    <div class="font-poppins font-medium text-lg leading-4 text-black capitalize">
                        {{ __('No reviews yet') }}
                    </div>
                @endforelse
            </div>
            {{-- Latest Events --}}
            <p class="font-poppins font-semibold text-2xl leading-6 text-black pt-10">{{ __('Events') }}&nbsp;( {{count($data->events)}} )</p>
            <div
                class="grid gap-x-7 lx3:grid-cols-4 xl:grid-cols-3 xlg:grid-cols-2 xxmd:grid-cols-2 xxmd:gap-y-7 xmd:gap-y-7 xxsm:gap-y-7 sm:grid-cols-1 sm:gap-y-7 msm:grid-cols-1 xxsm:grid-cols-1 justify-between pt-5">
                @foreach ($data->events as $item)
                    <div class="shadow-2xl p-5 rounded-lg bg-white">
                        <img src="{{ asset('images/upload/' . $item->image) }}" alt=""
                            class="rounded-lg w-full h-40 bg-cover object-cover ">
                        <p class="font-popping font-semibold text-xl leading-8 pt-2">{{ $item->name }}</p>
                        <p class="font-poppins  font-normal text-base leading-6 text-gray pt-1">
                            {{ Carbon\Carbon::parse($item->start_time)->format('d M Y') }} -
                            {{ Carbon\Carbon::parse($item->end_time)->format('d M Y') }}</p>
                        <div class="flex justify-between mt-7">
                            @php
                                $user = Auth::guard('appuser')->user();
                            @endphp
                            @if (Auth::guard('appuser')->user())
                                @if (Str::contains($user->favorite, $item->id))
                                    <a href="javascript:void(0);" class="like"
                                        onclick="addFavorite('{{ $item->id }}','{{ 'event' }}')"><img
                                            src="{{ url('images/heart-fill.svg') }}" alt=""
                                            class="object-cover bg-cover fillLike bg-white-light p-2 rounded-lg"></a>
                                @else
                                    <a href="javascript:void(0);" class="like"
                                        onclick="addFavorite('{{ $item->id }}','{{ 'event' }}')"><img
                                            src="{{ url('images/heart.svg') }}" alt=""
                                            class="object-cover bg-cover fillLike bg-white-light p-2 rounded-lg"></a>
                                @endif
                            @endif
                            <a type="button"
                                href="{{ url('/event/' . $item->id . '/' . Str::slug($item->name)) }}"
                                class="text-primary text-center font-poppins font-medium text-base leading-7 flex">{{ __('View Details') }}
                                <i class="fa-solid fa-arrow-right w-3 h-3 mt-1.5 ml-2"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
