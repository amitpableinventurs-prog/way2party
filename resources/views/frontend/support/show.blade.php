@extends('frontend.master', ['activePage' => 'support'])
@section('title', __('Support Ticket'))
@section('content')
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <div class="flex items-center justify-between pt-5">
                <div>
                    <p class="font-poppins font-semibold text-2xl leading-10 text-black">{{ $ticket->subject }}</p>
                    <span
                        class="text-xs px-3 py-1 rounded-full
                        @if ($ticket->status == 'open') bg-yellow-100 text-yellow-700
                        @elseif($ticket->status == 'answered') bg-green-100 text-green-700
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>
            </div>
            <div class="pt-8 bg-white shadow-lg rounded-lg p-6 space-y-4" style="max-height:60vh; overflow-y:auto;">
                <div class="flex justify-start">
                    <div class="max-w-[70%] rounded-lg px-4 py-2 bg-gray-100 text-black">
                        <p class="font-poppins text-sm">{{ $ticket->message }}</p>
                        <p class="font-poppins text-xs opacity-70 mt-1">{{ $ticket->created_at->format('d M Y, h:i a') }}</p>
                    </div>
                </div>
                @foreach ($ticket->replies as $reply)
                    <div class="flex {{ $reply->sender_type == 'app_user' ? 'justify-end' : 'justify-start' }}">
                        <div
                            class="max-w-[70%] rounded-lg px-4 py-2 {{ $reply->sender_type == 'app_user' ? 'bg-primary text-white' : 'bg-gray-100 text-black' }}">
                            <p class="font-poppins text-xs font-semibold mb-1">
                                {{ $reply->sender_type == 'app_user' ? __('You') : __('Support Team') }}</p>
                            <p class="font-poppins text-sm">{{ $reply->body }}</p>
                            <p class="font-poppins text-xs opacity-70 mt-1">{{ $reply->created_at->format('d M Y, h:i a') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($ticket->status != 'closed')
                <form method="post" action="{{ route('replySupportTicket', $ticket->id) }}" class="pt-5 flex space-x-3">
                    @csrf
                    <input type="text" name="body" required placeholder="{{ __('Type a message...') }}"
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-lg border border-gray-light focus:outline-none">
                    <button type="submit"
                        class="px-8 py-3 text-white bg-primary text-center font-poppins font-normal text-base leading-6 rounded-md">{{ __('Send') }}</button>
                </form>
            @else
                <p class="font-poppins text-gray-200 pt-5">{{ __('This ticket is closed.') }}</p>
            @endif
        </div>
    </div>
@endsection
