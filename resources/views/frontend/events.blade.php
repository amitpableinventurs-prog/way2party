@extends('frontend.master', ['activePage' => 'event'])
@section('title', __('All Events'))
@section('content')
    @php
        // every event is offline now (type is hardcoded server-side), so an
        // Online/Venue split is always Online(0) — show a Party Type filter
        // here instead, built from whatever categories are actually present.
        $partyTypeTabs = $events
            ->flatMap(fn($e) => $e->categories->isNotEmpty() ? $e->categories : ($e->category ? [$e->category] : []))
            ->unique('id')
            ->sortBy('name');
    @endphp

    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">

        {{-- scroll --}}

        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <div
                class="absolute bg-blue blur-3xl opacity-10 s:bg-opacity-10 3xl:w-[370px] 3xl:h-[370px] 2xl:w-[300px] 2xl:h-[300px] 1xl:w-[300px] xmd:w-[300px] xmd:h-[300px] sm:w-[200px] sm:h-[300px] xxsm:w-[300px] xxsm:h-[300px] rounded-full -mt-5 2xl:-ml-20 1xl:-ml-20 sm:ml-2 xxsm:-ml-7">
            </div>
            {{-- Breadcrumb: Home > City > Party Type (only the parts that apply) --}}
            <nav class="pt-5 z-10 relative font-poppins text-sm" aria-label="breadcrumb">
                <a href="{{ url('/') }}" class="text-blue hover:underline">{{ __('Home') }}</a>
                @if (isset($city) && $city)
                    <span class="text-gray-400 mx-1">/</span>
                    <a href="{{ url('/events-city/' . $city->id . '/' . Str::slug($city->name)) }}"
                        class="text-blue hover:underline">{{ $city->name }}</a>
                @endif
                @if (isset($category) && $category)
                    <span class="text-gray-400 mx-1">/</span>
                    <a href="{{ url('/events-category/' . $category->id . '/' . Str::slug($category->name)) }}"
                        class="text-blue hover:underline">{{ $category->name }}</a>
                @endif
            </nav>
            <div class="flex justify-start pt-5 z-10 flex-wrap items-end">
                <p
                    class="font-poppins font-semibold md:text-5xl xxsm:text-2xl xsm:text-2xl sm:text-2xl text-blue leading-10 ">
                    @if (isset($category) && !empty($city))
                        {{ $category->name }} {{ __('Events in') }} {{ $city->name }}
                    @elseif (isset($category))
                        {{ $category->name }} {{ __('Events') }}
                    @elseif (isset($city) && !empty($city))
                        {{ __('Events in') }} {{ $city->name }}
                    @else
                        {{ __('Events') }}
                    @endif
                </p>&nbsp;&nbsp;
                <p
                    class="font-poppins font-medium md:text-2xl xxsm:text-xl sm:text-xl text-blue leading-10 pt-1 sm:pt-3">
                    ( {{ $events->count() }} )</p>
            </div>
            <div class="mb-4 pt-4">
                <ul class="flex flex-wrap -mb-px text-lg font-medium text-center events xmd:space-y-0 md:space-y-2 sm:space-y-2 xxsm:space-y-2"
                    id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                    <li class="mr-2 ">
                        <button
                            class="inline-block p-4 px-6 py-3 rounded-md z-20 font-poppins shadow-md focus:outline-none relative"
                            id="all_events" data-tabs-target="#events" type="button" role="tab" aria-controls="events"
                            aria-selected="false">{{ __('All Events') }}</button>
                    </li>
                    @foreach ($partyTypeTabs as $tabCategory)
                        <li class="mr-2">
                            <button
                                class="inline-block z-20 px-5 py-3 rounded-md font-poppins shadow-md focus:outline-none relative"
                                id="party_type_{{ $tabCategory->id }}" data-tabs-target="#party-type-{{ $tabCategory->id }}"
                                type="button" role="tab" aria-controls="party-type-{{ $tabCategory->id }}"
                                aria-selected="false">{{ $tabCategory->name }}</button>
                        </li>
                    @endforeach
                </ul>
            </div>
            @if (count($events) == 0)
                <div class="font-poppins font-medium text-lg leading-4 text-black mt-10  capitalize">
                    {{ __('There are no events added yet') }}
                </div>
            @endif
            <div id="myTabContent">
                <div class="hidden" id="events" role="tabpanel" aria-labelledby="all_events">
                    <div
                        class="grid gap-x-7 1xl:grid-cols-4 xl:grid-cols-4 xlg:grid-cols-4 xmd:grid-cols-2 xxmd:gap-y-7 xmd:gap-y-7 xxsm:gap-y-7 sm:grid-cols-1 sm:gap-y-7 msm:grid-cols-1 xxsm:grid-cols-1 justify-between pt-10 z-30 relative">
                        @foreach ($events as $item)
                            <div
                                class="shadow-2xl p-5 rounded-lg bg-white hover:scale-110 transition-all duration-500 cursor-pointer">
                                <a href="{{ url('/event/' . $item->id . '/' . Str::slug($item->name)) }}">
                                    <div class="relative capitalize">
                                        <img src="{{ url('images/upload/' . $item->image) }}" alt=""
                                        class="h-40 rounded-lg w-full object-cover bg-cover ">
                                        @switch(strtolower($item->auto_generated_tag))
                                            @case('almost full')
                                                <span class="bg-success text-center text-sm text-white py-1 px-2 rounded-bl-lg rounded-tr-lg absolute top-0 right-0">
                                                    {{ $item->auto_generated_tag }}
                                                </span>
                                                @break
                                            @case('sales ending soon')
                                                <span class="bg-warning text-center text-sm text-white py-1 px-2 rounded-bl-lg rounded-tr-lg absolute top-0 right-0">
                                                    {{ $item->auto_generated_tag }}
                                                @break
                                            @case('sold out')
                                                <span class="bg-danger text-center text-sm text-white py-1 px-2 rounded-bl-lg rounded-tr-lg absolute top-0 right-0">
                                                    {{ $item->auto_generated_tag }}
                                                @break
                                            @default
                                                @break
                                        @endswitch
                                    </div>
                                    <p class="font-popping font-semibold text-xl leading-8 pt-2 w-[90%] truncate">{{ $item->name }}</p>
                                    <p class="font-poppins font-normal text-base leading-6 text-gray pt-1">
                                        {{ Carbon\Carbon::parse($item->start_time)->format('d M Y') }} -
                                        {{ Carbon\Carbon::parse($item->end_time)->format('d M Y') }}
                                    </p>
                                </a>
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    @if ($item->city)
                                        <a href="{{ url('/events-city/' . $item->city_id . '/' . Str::slug($item->city->name)) }}"
                                            class="px-3 py-1 text-xs font-poppins text-primary bg-primary-light rounded-full hover:underline">
                                            {{ $item->city->name }}
                                        </a>
                                    @endif
                                    @if ($item->categories->isNotEmpty())
                                        @foreach ($item->categories as $cat)
                                            <a href="{{ url('/events-category/' . $cat->id . '/' . Str::slug($cat->name)) . ($item->city ? '?city=' . $item->city_id : '') }}"
                                                class="px-3 py-1 text-xs font-poppins text-success bg-success-light rounded-full hover:underline">
                                                {{ $cat->name }}
                                            </a>
                                        @endforeach
                                    @elseif ($item->category)
                                        <a href="{{ url('/events-category/' . $item->category_id . '/' . Str::slug($item->category->name)) . ($item->city ? '?city=' . $item->city_id : '') }}"
                                            class="px-3 py-1 text-xs font-poppins text-success bg-success-light rounded-full hover:underline">
                                            {{ $item->category->name }}
                                        </a>
                                    @endif
                                </div>
                                <div class="flex justify-between mt-7">
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
                                    <a type="button" id="EventDetails{{ $item->id }}"
                                        href="{{ url('/event/' . $item->id . '/' . Str::slug($item->name)) }}"
                                        class="text-primary text-center font-poppins font-medium text-base leading-7 flex">{{ __('View Details') }}
                                        <i class="fa-solid fa-arrow-right w-3 h-3 mt-1.5 ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @foreach ($partyTypeTabs as $tabCategory)
                    <div class="hidden" id="party-type-{{ $tabCategory->id }}" role="tabpanel"
                        aria-labelledby="party_type_{{ $tabCategory->id }}">
                        <div
                            class="grid gap-x-7 1xl:grid-cols-4 xl:grid-cols-4 xlg:grid-cols-4 xmd:grid-cols-2 xxmd:gap-y-7 xmd:gap-y-7 xxsm:gap-y-7 sm:grid-cols-1 sm:gap-y-7 msm:grid-cols-1 xxsm:grid-cols-1 justify-between pt-10 z-30 relative">
                            @foreach ($events as $item)
                                @if ($item->categories->isNotEmpty() ? $item->categories->contains('id', $tabCategory->id) : $item->category_id == $tabCategory->id)
                                    <div class="shadow-2xl p-5 rounded-lg bg-white hover:scale-110 transition-all duration-500 cursor-pointer">
                                        <a href="{{ url('/event/' . $item->id . '/' . Str::slug($item->name)) }}">
                                            <img src="{{ url('images/upload/' . $item->image) }}" alt=""
                                                class="h-40 rounded-lg w-full object-cover bg-cover ">
                                            <p class="font-popping font-semibold text-xl leading-8 pt-2 w-[90%] truncate">
                                                {{ $item->name }}</p>
                                            <p class="font-poppins font-normal text-base leading-6 text-gray pt-1">
                                                {{ Carbon\Carbon::parse($item->start_time)->format('d M Y') }} -
                                                {{ Carbon\Carbon::parse($item->end_time)->format('d M Y') }}</p>
                                        </a>
                                        <div class="flex items-center gap-2 mt-2 flex-wrap">
                                            @if ($item->city)
                                                <a href="{{ url('/events-city/' . $item->city_id . '/' . Str::slug($item->city->name)) }}"
                                                    class="px-3 py-1 text-xs font-poppins text-primary bg-primary-light rounded-full hover:underline">
                                                    {{ $item->city->name }}
                                                </a>
                                            @endif
                                            @if ($item->categories->isNotEmpty())
                                                @foreach ($item->categories as $cat)
                                                    <a href="{{ url('/events-category/' . $cat->id . '/' . Str::slug($cat->name)) . ($item->city ? '?city=' . $item->city_id : '') }}"
                                                        class="px-3 py-1 text-xs font-poppins text-success bg-success-light rounded-full hover:underline">
                                                        {{ $cat->name }}
                                                    </a>
                                                @endforeach
                                            @elseif ($item->category)
                                                <a href="{{ url('/events-category/' . $item->category_id . '/' . Str::slug($item->category->name)) . ($item->city ? '?city=' . $item->city_id : '') }}"
                                                    class="px-3 py-1 text-xs font-poppins text-success bg-success-light rounded-full hover:underline">
                                                    {{ $item->category->name }}
                                                </a>
                                            @endif
                                        </div>
                                        <div class="flex justify-between mt-7">
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
                                            <a type="button" id="EventDetails{{ $item->id }}"
                                                href="{{ url('/event/' . $item->id . '/' . Str::slug($item->name)) }}"
                                                class="text-primary text-center font-poppins font-medium text-base leading-7 flex">{{ __('View Details') }}
                                                <i class="fa-solid fa-arrow-right w-3 h-3 mt-1.5 ml-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
