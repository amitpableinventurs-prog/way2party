@extends('frontend.master', ['activePage' => 'messages'])
@section('title', __('Conversation'))
@section('content')
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <div class="flex items-center pt-5 z-10">
                <img src="{{ url('images/upload/' . ($conversation->organizer->image ?? 'defaultuser.png')) }}" alt=""
                    class="h-12 w-12 object-cover bg-cover rounded-full">
                <p class="font-poppins font-semibold text-2xl leading-10 text-black ml-4">
                    {{ $conversation->organizer->organization_name ?? (($conversation->organizer->first_name ?? '') . ' ' . ($conversation->organizer->last_name ?? '')) }}
                </p>
            </div>
            <div class="pt-8 bg-white shadow-lg rounded-lg p-6 space-y-4" style="max-height:60vh; overflow-y:auto;">
                @forelse ($messages as $message)
                    <div class="flex {{ $message->sender_type == $viewerType ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] rounded-lg px-4 py-2 {{ $message->sender_type == $viewerType ? 'bg-primary text-white' : 'bg-gray-100 text-black' }}">
                            <p class="font-poppins text-sm">{{ $message->body }}</p>
                            <p class="font-poppins text-xs opacity-70 mt-1">{{ $message->created_at->format('d M Y, h:i a') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="font-poppins text-gray-200">{{ __('No messages yet. Say hello!') }}</p>
                @endforelse
            </div>
            <form method="post" action="{{ url('/messages/' . $conversation->id) }}" class="pt-5 flex space-x-3">
                @csrf
                <input type="text" name="body" required placeholder="{{ __('Type a message...') }}"
                    class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-lg border border-gray-light focus:outline-none">
                <button type="submit"
                    class="px-8 py-3 text-white bg-primary text-center font-poppins font-normal text-base leading-6 rounded-md">{{ __('Send') }}</button>
            </form>
        </div>
    </div>
@endsection
