@extends('frontend.master', ['activePage' => 'support'])
@section('title', __('Support Tickets'))
@section('content')
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <div class="flex justify-between items-center pt-5 z-10">
                <p class="font-poppins font-semibold md:text-5xl xxsm:text-2xl xsm:text-2xl sm:text-2xl text-black leading-10">
                    {{ __('Support Tickets') }}</p>
                <a href="{{ route('createSupportTicket') }}"
                    class="px-6 py-3 text-white bg-primary text-center font-poppins font-normal text-base leading-6 rounded-md">
                    {{ __('New Ticket') }}</a>
            </div>
            @if (count($tickets) == 0)
                <div class="font-poppins font-medium text-lg leading-4 text-black mt-10 capitalize">
                    {{ __('You have not raised any support tickets yet.') }}
                </div>
            @endif
            <div class="pt-8 space-y-4">
                @foreach ($tickets as $ticket)
                    <a href="{{ route('showSupportTicket', $ticket->id) }}"
                        class="flex items-center justify-between shadow-lg bg-white p-5 rounded-lg hover:scale-[1.01] transition-all duration-300">
                        <div>
                            <p class="font-poppins font-semibold text-lg leading-6 text-black">{{ $ticket->subject }}</p>
                            <p class="font-poppins font-normal text-sm text-gray-200 mt-1">
                                {{ \Illuminate\Support\Str::limit($ticket->message, 80) }}</p>
                        </div>
                        <div class="text-right">
                            <span
                                class="text-xs px-3 py-1 rounded-full
                                @if ($ticket->status == 'open') bg-yellow-100 text-yellow-700
                                @elseif($ticket->status == 'answered') bg-green-100 text-green-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($ticket->status) }}
                            </span>
                            <p class="font-poppins font-normal text-xs text-gray-200 mt-2">
                                {{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
