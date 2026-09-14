@extends('frontend.master', ['activePage' => 'messages'])
@section('title', __('My Messages'))
@section('content')
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <div class="flex justify-start pt-5 z-10">
                <p class="font-poppins font-semibold md:text-5xl xxsm:text-2xl xsm:text-2xl sm:text-2xl text-black leading-10">
                    {{ __('Messages') }}</p>
            </div>
            @if (count($conversations) == 0)
                <div class="font-poppins font-medium text-lg leading-4 text-black mt-10 capitalize">
                    {{ __('No conversations yet.') }}
                </div>
            @endif
            <div class="pt-8 space-y-4">
                @foreach ($conversations as $item)
                    <a href="{{ url('/messages/' . $item->id) }}"
                        class="flex items-center justify-between shadow-lg bg-white p-5 rounded-lg hover:scale-[1.01] transition-all duration-300">
                        <div class="flex items-center">
                            <img src="{{ url('images/upload/' . ($item->organizer->image ?? 'defaultuser.png')) }}" alt=""
                                class="h-12 w-12 object-cover bg-cover rounded-full">
                            <div class="ml-4">
                                <p class="font-poppins font-semibold text-lg leading-6 text-black">
                                    {{ $item->organizer->organization_name ?? (($item->organizer->first_name ?? '') . ' ' . ($item->organizer->last_name ?? '')) }}
                                </p>
                                @if ($item->unreadCountFor('app_user') > 0)
                                    <span class="text-xs text-white bg-primary px-2 py-1 rounded-full">{{ $item->unreadCountFor('app_user') }} {{ __('new') }}</span>
                                @endif
                            </div>
                        </div>
                        <p class="font-poppins font-normal text-sm text-gray-200">
                            {{ $item->last_message_at ? \Carbon\Carbon::parse($item->last_message_at)->diffForHumans() : '' }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
