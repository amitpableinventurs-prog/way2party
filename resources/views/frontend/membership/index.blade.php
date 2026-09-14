@extends('frontend.master', ['activePage' => 'membership'])
@section('title', __('Membership'))
@section('content')
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <p class="font-poppins font-semibold md:text-5xl xxsm:text-2xl xsm:text-2xl sm:text-2xl text-black leading-10 pt-5">
                {{ __('Membership') }}</p>

            <div class="mt-6 bg-white shadow-lg rounded-lg p-6">
                <p class="font-poppins font-normal text-lg text-gray-200">{{ __('Current Plan') }}</p>
                <p class="font-poppins font-semibold text-2xl text-black mt-1">
                    {{ $user->membershipPlan->name ?? __('Free') }}
                </p>
                @if ($user->membershipPlan && $user->membership_expires_at)
                    <p class="font-poppins font-normal text-sm text-gray-200 mt-1">
                        {{ __('Expires on') }} {{ $user->membership_expires_at->format('d M Y') }}
                    </p>
                @elseif ($user->membershipPlan)
                    <p class="font-poppins font-normal text-sm text-gray-200 mt-1">{{ __('Does not expire') }}</p>
                @endif
            </div>

            <p class="font-poppins font-semibold text-2xl leading-6 text-black pt-10">{{ __('Available Plans') }}</p>
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($plans as $plan)
                    <div class="shadow-lg bg-white p-6 rounded-lg flex flex-col justify-between">
                        <div>
                            @if ($plan->imagePath)
                                <img src="{{ $plan->imagePath }}" alt="" class="w-full h-32 object-cover rounded-md mb-3">
                            @endif
                            <p class="font-poppins font-semibold text-xl text-black">{{ $plan->name }}</p>
                            <p class="font-poppins font-semibold text-2xl text-primary mt-2">
                                {{ $currency ?? '' }}{{ number_format($plan->price, 2) }}
                                @if ($plan->amount > $plan->price)
                                    <span class="text-sm font-normal text-gray-200 line-through">{{ $currency ?? '' }}{{ number_format($plan->amount, 2) }}</span>
                                @endif
                                @if ($plan->duration_days)
                                    <span class="text-sm font-normal text-gray-200">/ {{ $plan->duration_days }} {{ __('days') }}</span>
                                @else
                                    <span class="text-sm font-normal text-gray-200">/ {{ __('lifetime') }}</span>
                                @endif
                            </p>
                            @if ($plan->description)
                                <p class="font-poppins font-normal text-sm text-gray-200 mt-3 whitespace-pre-line">{{ $plan->description }}</p>
                            @endif
                            @if ($plan->features->count())
                                <ul class="mt-3 space-y-1">
                                    @foreach ($plan->features as $feature)
                                        <li class="flex items-center font-poppins font-normal text-sm text-black">
                                            <i class="fa-solid fa-check text-green-500 mr-2"></i>{{ $feature->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <form method="post" action="{{ route('membershipUpgrade', $plan->id) }}" class="mt-5"
                            onsubmit="return confirm('{{ __('Upgrade to') }} {{ $plan->name }}?');">
                            @csrf
                            <button type="submit"
                                {{ $user->membership_plan_id == $plan->id && (!$user->membership_expires_at || $user->membership_expires_at->isFuture()) ? 'disabled' : '' }}
                                class="w-full px-6 py-3 text-white bg-primary text-center font-poppins font-normal text-base leading-6 rounded-md disabled:opacity-50">
                                {{ $user->membership_plan_id == $plan->id && (!$user->membership_expires_at || $user->membership_expires_at->isFuture()) ? __('Current Plan') : __('Upgrade') }}
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="font-poppins text-gray-200 col-span-full">{{ __('No membership plans available yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
