@extends('frontend.master', ['activePage' => 'home'])
@section('title', __('Send Funds'))
@section('content')
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        <div class="container mx-auto mt-8">
            <p class="font-poppins font-semibold md:text-5xl xxsm:text-2xl xsm:text-2xl sm:text-2xl text-blue leading-10">
                {{ __('Send Funds') }}</p>
            <p class="font-poppins font-semibold text-lg mt-2">
                {{ __('Available Balance') }} : <span class="text-green-500">{{ $balance }}</span>
            </p>

            <form method="post" action="{{ route('sendFundsPost') }}" class="mt-8 space-y-5 md:w-1/2 w-full">
                @csrf
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Send To') }}</label>
                    <select name="recipient_type" required
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">
                        <option value="customer" {{ old('recipient_type') == 'customer' ? 'selected' : '' }}>{{ __('Another Customer') }}</option>
                        <option value="organizer" {{ old('recipient_type') == 'organizer' ? 'selected' : '' }}>{{ __('An Organizer') }}</option>
                    </select>
                </div>
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Recipient Email') }}</label>
                    <input type="email" name="recipient_email" value="{{ old('recipient_email') }}" required
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">
                    @error('recipient_email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Amount') }}</label>
                    <input type="number" step="0.01" min="1" name="amount" value="{{ old('amount') }}" required
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="font-poppins font-medium text-base leading-6 text-black">{{ __('Note (optional)') }}</label>
                    <input type="text" name="note" value="{{ old('note') }}"
                        class="w-full text-sm font-poppins font-normal text-black block p-3 rounded-md border border-gray-light focus:outline-none">
                </div>
                <button type="submit"
                    class="bg-primary text-white font-poppins font-medium text-base leading-6 px-8 py-3 rounded-md">{{ __('Send Funds') }}</button>
            </form>
        </div>
    </div>
@endsection
