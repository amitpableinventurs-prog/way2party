@extends('frontend.master', ['activePage' => 'event'])
@section('title', __('Event Details'))
@php
    $gmapkey = \App\Models\Setting::find(1)->map_key;
@endphp
@section('content')
    {{-- content --}}
    <div class="pb-20 bg-scroll min-h-screen" style="background-image: url('{{ asset('images/events.png') }}')">
        {{-- scroll --}}
        <div class="mr-4 flex justify-end z-30">
            <a type="button" href="{{ url('#') }}"
                class="scroll-up-button bg-primary rounded-full p-4 fixed z-20  2xl:mt-[49%] xl:mt-[59%] xlg:mt-[68%] lg:mt-[75%] xxmd:mt-[83%] md:mt-[90%]
                xmd:mt-[90%] sm:mt-[117%] msm:mt-[125%] xsm:mt-[160%]">
                <img src="{{ asset('images/downarrow.png') }}" alt="" class="w-3 h-3 z-20">
            </a>
        </div>
        <div
            class="mt-5 3xl:mx-52 2xl:mx-28 1xl:mx-28 xl:mx-36 xlg:mx-32 lg:mx-36 xxmd:mx-24 xmd:mx-32 md:mx-28 sm:mx-20 msm:mx-16 xsm:mx-10 xxsm:mx-5 z-10 relative">
            <div
                class="flex sm:space-x-0 msm:space-x-0 xxsm:space-x-0 lg:space-x-3 xlg:space-x-5 xxmd:flex-row xmd:flex-col xxsm:flex-col">
                <div class="xxmd:w-2/3 xmd:w-full xxsm:w-full">
                    <div>
                        @if (Auth::guard('appuser')->user())
                            <div
                                class="shadow-2xl rounded-lg w-10 h-10 text-center absolute bg-white top-8 xxmd:right-[38%] xmd:right-6 md:right-6 sm:right-6 xxsm:right-6">
                                @if (Str::contains($appUser->favorite, $data->id))
                                    <a href="javascript:void(0);" class="like"
                                        onclick="addFavorite('{{ $data->id }}','{{ 'event' }}')"><img
                                            src="{{ url('images/heart-fill.svg') }}" alt=""
                                            class="object-cover bg-cover fillLike bg-white-light p-2 rounded-lg"></a>
                                @else
                                    <a href="javascript:void(0);" class="like"
                                        onclick="addFavorite('{{ $data->id }}','{{ 'event' }}')"><img
                                            src="{{ url('images/heart.svg') }}" alt=""
                                            class="object-cover bg-cover fillLike bg-white-light p-2 rounded-lg"></a>
                                @endif
                            </div>
                        @endif
                        <img src="{{ url('images/upload/' . $data->image) }}" class="w-full h-96 object-cover"
                            id="eventimage" alt="not found">
                    </div>
                    <div class="mt-8 pb-5 bg-white shadow-lg rounded-md">
                        <div
                            class="flex justify-between p-4 lg:flex-wrap sm:flex-wrap msm:flex-wrap xxsm:flex-wrap">
                            <div class="">
                                <p class="font-poppins font-semibold text-3xl leading-9 text-black">
                                    {{ $data->name }}</p>
                                @if ($data->rate > 1)
                                    <div class="flex space-x-2 pt-3 ">
                                        @for ($i = 1; $i <= $data->rate; $i++)
                                            <img src="{{ asset('images/star-fill.png') }}" alt="">
                                        @endfor
                                        @for ($i = 5; $i > $data->rate; $i--)
                                            <img src="{{ asset('images/star.png') }}" alt="">
                                        @endfor
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('organizationDetails', ['id' => $data->organization->id]) }}">
                                <div class="flex msm:flex-wrap xxsm:flex-wrap mt-4 mb-4 lx3:mt-0 lx3:mb-0 px-4">
                                    <div class="">
                                        <img src="{{ url('images/upload/' . $data->organization->image) }}"
                                            class="w-10 h-10 bg-cover object-cover" alt="">
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-poppins font-normal text-xs leading-4 text-gray-100">
                                            {{ __('Organized by') }}</p>
                                        <p class="font-poppins font-normal text-base leading-6 text-gray">
                                            {{ $data->organization->organization_name ?? $data->organization->first_name}}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="px-4">
                            <div class="pt-4 flex space-x-6 md:flex-wrap sm:flex-wrap xxsm:flex-wrap xxsm:space-x-3 md:space-x-6">
                                <img src="{{ asset('images/calender-icon.png') }}" alt=""
                                    class="bg-success-light rounded-md p-2  max-w-9 max-h-9">
                                <div class="flex space-x-2 xsm:ml-10 xsm:mr-5">
                                    <p class="font-poppins font-bold sm:text-4xl leading-10 text-black xxsm:text-lg xxsm:ml-4">
                                        {{ Carbon\Carbon::parse($data->start_time)->format('d') }}
                                    </p>
                                    <p
                                        class="font-poppins font-semibold sm:text-2xl leading-8 text-gray-200 pt-2 xxsm:text-sm">
                                        {{ Carbon\Carbon::parse($data->start_time)->format('M \' y') }}</p>
                                    <p
                                        class="font-poppins font-semibold sm:text-2xl leading-8 text-gray-200 pt-2 xxsm:text-sm">
                                        {{ Carbon\Carbon::parse($data->start_time)->format('h:i A') }}
                                    </p>
                                </div>
                                <div class="font-poppins font-semibold sm:text-2xl leading-8 text-gray-200 pt-2 xxsm:text-sm">To</div>
                                <div class="flex space-x-2">
                                    <p class="font-poppins font-bold sm:text-4xl leading-10 text-black xxsm:text-lg xxsm:ml-12 xl1:ml-0">
                                        {{ Carbon\Carbon::parse($data->end_time)->format('d') }}
                                    </p>
                                    <p
                                        class="font-poppins font-semibold sm:text-2xl leading-8 text-gray-200 pt-2 xxsm:text-sm">
                                        {{ Carbon\Carbon::parse($data->end_time)->format('M \' y') }}</p>
                                    <p
                                        class="font-poppins font-semibold sm:text-2xl leading-8 text-gray-200 pt-2 xxsm:text-sm">
                                        {{ Carbon\Carbon::parse($data->end_time)->format('h:i A') }}
                                    </p>
                                </div>
                            </div>
                            <div class="pt-4 flex space-x-6 md:flex-nowrap sm:flex-wrap">
                                <img src="{{ asset('images/location-icon.png') }}" alt=""
                                    class="p-2 w-auto h-10 rounded-md bg-blue-light max-w-9 max-h-9">
                                    <p class="font-poppins font-normal sm:text-lg leading-7 text-gray lg:mt-1.5 xxsm:mt-0 xxsm:text-sm">
                                    @if ($data->type == 'online')
                                        {{ __('Online Event') }}
                                    @else
                                        {{ $data->address }}
                                    @endif
                                </p>
                            </div>
                            <div class="pt-4 flex space-x-6 sm:flex-wrap xxsm:flex-wrap">
                                @if(count($data->ticket) > 0)
                                <img src="{{ asset('images/user-icon.png') }}" alt="" class="p-2 rounded-md bg-warning-light max-w-9 max-h-9">
                                    <p class="font-poppins font-normal text-lg leading-7 text-gray"> {{ $data->people }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-10 bg-white shadow-lg rounded-md">
                        <div class="p-4">
                            <p class="font-poppins font-semibold text-2xl leading-8 text-black">{{ __('About Event') }}</p>
                            <p class="font-poppins font-normal text-lg leading-7 text-gray pt-5 mb-5">
                                {!! $data->description !!}
                            </p>
                            <div class="flex flex-wrap mt-5">
                                @foreach ($tags as $item)
                                    <a href="{{ url('/user/tag/' . $item) }}">
                                        <div
                                            class="mt-2 mr-3 px-3 py-2 text-success bg-success-light rounded-md font-poppins font-normal text-base leading-6">
                                            {{ $item }}</div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="xxmd:w-1/3 xmd:w-full xxsm:w-full mt-5">
                    <div class="p-4 bg-white shadow-lg rounded-md">
                        <p class="font-poppins font-semibold text-2xl leading-8 text-black pb-3">{{ __('Image Gallery') }}
                        </p>
                        <div
                            class="grid lg:grid-cols-2 xxmd:grid-cols-1 xmd:grid-cols-2 sm:grid-cols-2 msm:grid-cols-2 xxsm:grid-cols-1 sm:mt-3 sm:gap-3">

                            <div id="eventimage" class=" hover:cursor-pointer"
                                onclick="imagegallery('{{ $data->image }}')">

                                <img src="{{ url('images/upload/' . $data->image) }}"
                                    class="imageGallery 1xl:w-40 1xl:h-24 xlg:h-16 xl:h-20 lg:w-[90%] lg:h-10 xxmd:w-full xxmd:h-32 xmd:w-[90%] msm:w-[90%] xxsm:w-full xxsm:h-[80%] rounded-md object-cover bg-cover"
                                    alt="">
                            </div>
                            @foreach ($images as $item)
                                @if (strlen($item) > 0)
                                    <div id="eventimage1" class=" hover:cursor-pointer"
                                        onclick="imagegallery('{{ $item }}')"><img
                                            src="{{ url('images/upload/' . $item) }}"
                                            class="imageGallery 1xl:w-40 1xl:h-24 xlg:h-16 xl:h-20 lg:w-[90%] lg:h-10 xxmd:w-full xxmd:h-32 xmd:w-[90%] msm:w-[90%] xxsm:w-full xxsm:h-[80%] rounded-md object-cover bg-cover"
                                            alt="{{ 'Event Image' }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="p-4 bg-white shadow-lg rounded-md xlg:mt-10 lg:mt-20">
                        <p class="font-poppins font-semibold text-2xl leading-8 text-black pb-3">{{ __('Location') }}
                        </p>
                        <div id="map" class="mt-5" style="width:100%;height:400px;">
                        </div>
                    </div>
                </div>
            </div>
            {{-- tickets --}}
            <div class="bg-white shadow-lg rounded-md p-4 mt-10 w-full" id="tickets">

                <div class="flex justify-between">
                    <p class="font-poppins font-semibold text-2xl leading-9 text-black">{{ __('Tickets') }}</p>
                </div>
                <div
                    class="grid xl:grid-cols-4 xlg:grid-cols-3 xxmd:grid-cols-2 sm:grid-cols-2 msm:grid-cols-1 xxsm:grid-cols-1 pt-5 gap-5">
                    @if (count($data->paid_ticket) != 0)

                        @foreach ($data->paid_ticket as $item)
                            <div class="relative rounded-lg border border-gray-light p-5 ">
                                <div class="!h-auto mb-5" style="height: auto;margin-bottom:100px;">
                                    <div class="flex justify-center">
                                        <p
                                            class="font-poppins font-medium text-sm leading-4 text-danger text-center rounded-full bg-danger-light w-16 py-1">
                                            {{ __('Paid') }}</p>
                                    </div>
                                    <p class="font-poppins font-medium text-xl leading-7 text-primary text-center py-4">
                                        {{ $item->name }}</p>
                                    <div class="flex justify-center space-x-2">
                                        <span
                                            class="font-poppins font-medium text-2xl leading-8 text-center text-black pt-1">{{ __($currency) }}</span>
                                        <p class="font-poppins font-medium text-5xl leading-10 text-black text-center">
                                            {{ $item->price }}</p>
                                    </div>
                                    {{-- when tickets are available --}}
                                    <div class="py-4">
                                        @if ($item->available_qty <= 0)
                                            <p
                                                class="font-poppins font-normal text-lg leading-7 text-danger text-center rounded-full bg-danger-light py-2">
                                                {{ __('No Available tickets') }}</p>
                                        @else
                                            <p
                                                class="font-poppins font-normal text-lg leading-7 text-success text-center bg-success-light rounded-full py-2">
                                                {{ $item->available_qty }}&nbsp{{ __('Available tickets') }}</p>
                                        @endif
                                    </div>
                                    <p class="font-poppins font-normal text-base leading-6 text-gray text-left">
                                        {{ $item->description }}
                                    </p>
                                    <p class="font-poppins font-normal text-base leading-6 text-gray text-left">
                                        {{ __('Ticket Sale starts onwards') }}
                                    </p>
                                    <p class="font-poppins font-normal text-base leading-6 text-gray text-left">
                                        {{ Carbon\Carbon::parse($item->start_time)->format('d M Y') }} {{ __('till') }}
                                        {{ Carbon\Carbon::parse($item->end_time)->format('d M Y') }}
                                    </p>

                                </div>

                                <div class="absolute bottom-5" style="width: 89%">
                                    @if ($item->available_qty <= 0)
                                        <div class="mt-7  w-full border border-primary rounded-lg flex justify-center">
                                            <a href="#"
                                                class="font-poppins font-medium text-base leading-6 text-primary  py-3">{{ __('Sold Out') }}</a>
                                        </div>
                                    @else
                                        <a type="button" href="{{ url('/checkout/' . $item->id) }}"
                                            class="text-primary text-center font-poppins font-medium text-base leading-7 w-full py-3 mt-7 border border-primary rounded-lg flex justify-center hover:text-white hover:bg-primary">{{ __('Book Now') }}
                                            <i class="fa-solid fa-arrow-right w-3 h-3 mt-1.5 ml-2"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @if (count($data->free_ticket) != 0)
                        @foreach ($data->free_ticket as $item)
                            <div class="rounded-lg border border-gray-light p-5">
                                <div class="flex justify-center">
                                    <p
                                        class="font-poppins font-medium text-sm leading-4 text-primary text-center rounded-full bg-primary-light w-16 py-1">
                                        {{ __('free') }}</p>
                                </div>
                                <p class="font-poppins font-medium text-xl leading-7 text-primary text-center py-4">
                                    {{ $item->name }}</p>
                                <div class="flex justify-center space-x-2">
                                    <span
                                        class="font-poppins font-medium text-2xl leading-8 text-center text-black pt-1"></span>
                                    <p class="font-poppins font-medium text-5xl leading-10 text-black text-center">
                                        {{ __('Free') }}</p>
                                </div>
                                {{-- when tickets are available --}}
                                <div class="py-4">
                                    @if ($item->available_qty <= 0)
                                        <p
                                            class="font-poppins font-normal text-lg leading-7 text-danger text-center rounded-full bg-danger-light py-2">
                                            {{ __('No Available tickets') }}</p>
                                    @else
                                        <p
                                            class="font-poppins font-normal text-lg leading-7 text-success text-center bg-success-light rounded-full py-2">
                                            {{ $item->available_qty . ' Available tickets' }}</p>
                                    @endif
                                </div>
                                <p class="font-poppins font-normal text-base leading-6 text-gray text-left">
                                    {{ $item->description }}
                                </p>
                                <p class="font-poppins font-normal text-base leading-6 text-gray text-left">
                                    {{ __('Ticket Date') }}
                                </p>
                                <p class="font-poppins font-normal text-base leading-6 text-gray text-left">
                                    {{ Carbon\Carbon::parse($item->start_time)->format('d M Y') }} -
                                    {{ Carbon\Carbon::parse($item->end_time)->format('d M Y') }}
                                </p>
                                @if ($item->available_qty <= 0)
                                    <div class="mt-7  w-full border border-primary rounded-lg flex justify-center">
                                        <a href="#"
                                            class="font-poppins font-medium text-base leading-6 text-primary  py-3">{{ __('Sold Out') }}</a>
                                    </div>
                                @else
                                    <a type="button" href="{{ url('/checkout/' . $item->id) }}"
                                        class=" text-primary text-center font-poppins font-medium text-base leading-7 w-full  py-3 mt-7 border border-primary rounded-lg flex justify-center">{{ __('Book Now') }}
                                        <i class="fa-solid fa-arrow-right w-3 h-3 mt-1.5 ml-2"></i>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    @endif
                    @if (count($data->free_ticket) == 0 && count($data->paid_ticket) == 0)
                        <div class="mx-auto w-full">
                            <div class="p-5">
                                <img src="{{ url('frontend/images/empty.png') }}">
                                <h6 class="font-poopins text-xl leading-9 text-black mt-2 w-full">
                                    {{ __('Tickets not available for this event') }}!</h6>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
            {{-- review --}}
            <div class="bg-white shadow-lg rounded-md p-4 mt-10 w-full">
                <div class="flex">
                    <p class="font-poppins font-semibold text-2xl leading-7 text-black">{{ __('Reviews') }}</p>&nbsp;
                    <p class="font-poppins font-medium text-base leading-8 text-black">({{ count($data->review) }})</p>
                </div>

                    @forelse ($data->review as $item)
                        <div>
                            <div class="flex justify-between mt-4 sm:flex-wrap xxsm:flex-wrap">
                                <div class="flex sm:flex-wrap xxsm:flex-wrap">
                                    <div class="">
                                        @php
                                            $user = \App\Models\AppUser::find($item->user_id);
                                        @endphp
                                        <img src="{{ asset('images/upload/' . $user->image) }}"
                                            class="w-10 h-10 bg-cover object-cover" alt="">
                                    </div>
                                    <div class="ml-3 ">
                                        <p class="font-poppins font-medium text-lg leading-6 text-black-100">
                                            {{ $user->name }}</p>

                                    </div>
                                </div>
                                <div class="flex">
                                    <p class="font-poppins font-medium text-base leading-4 text-gray-200 pt-1 mr-3">
                                        {{ __('Rating : ' . $item->rate) }}</p>
                                    <div class="flex space-x-1">
                                        @for ($i = 1; $i <= $item->rate; $i++)
                                            <img src="{{ asset('images/star-fill.png') }}"
                                                class="h-5 w-5 bg-cover object-cover" alt="">
                                        @endfor

                                    </div>
                                </div>
                            </div>
                            <div class="ml-12 mt-4">
                                <p class="font-poppins font-normal text-base leading-6 text-gray">
                                    {{ $item->message }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="font-poppins font-normal text-base leading-6 text-gray my-5">{{ __('No reviews available.') }}</p>
                    @endforelse

            </div>
            {{-- FAQs --}}
            <div class="bg-white shadow-lg rounded-md p-4 mt-10 w-full">
                <p class="font-poppins font-semibold text-2xl leading-8 text-black">{{ __('Frequently Asked Questions') }}</p>
                @forelse ($data->faqs as $faq)
                    <div class="my-8">
                        <p class="font-poppins font-medium text-lg leading-6 text-black">{{ $loop->iteration }}. {{ $faq->question }}</p>
                        <p class="font-poppins font-normal text-base leading-6 text-gray ml-5">{{ $faq->answer }}</p>
                    </div>
                @empty
                    <p class="font-poppins font-normal text-base leading-6 text-gray my-5">{{ __('No FAQs available') }}</p>
                @endforelse
            </div>
            {{-- Report Event --}}
            @if ($show_event_report_form == 1 || $show_event_report_form == true)
            <div class="bg-white shadow-lg rounded-md p-4 mt-10 w-full">
                <p class="font-poppins font-semibold text-2xl leading-8 text-black">{{ __('Report Event') }}</p>
                <form class="form-a" method="post" action="{{ url('report-event') }}">
                    @csrf
                    <div class="">
                        <div class="grid md:grid-cols-2 sm:grid-cols-1 xxsm:grid-cols-1 mt-5 gap-3">
                            <div class=" ">
                                <label for="name"
                                    class="font-poppins font-normal text-lg leading-7 text-gray-100 pb-2">{{ __('Name') }}</label>
                                <input type="text" name="name"
                                    class="focus:outline-none text-base leading-4 font-poppins font-normal text-gray-100 block p-3 rounded-md z-20
                            border border-gray-light w-full"
                                    placeholder="{{ __('Name *') }}">
                            </div>
                            <div class="">
                                <label for="name"
                                    class="font-poppins font-normal text-lg leading-7 text-gray-100 pb-2">{{ __('Email address') }}</label>
                                <input type="text" name="email"
                                    class="focus:outline-none text-base leading-4 font-poppins font-normal text-gray-100 block p-3 rounded-md z-20
                            border border-gray-light w-full"
                                    placeholder="{{ __('Email *') }}">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 sm:grid-cols-1 xxsm:grid-cols-1 mt-5 gap-3">
                            <div class="w-full">
                                <label for="report_reason"
                                    class="font-poppins font-normal text-lg leading-7 text-gray-100 pb-2">{{ __('Report Reason') }}</label>
                                <select id="report_reason" name="reason"
                                    class="w-full focus:outline-none text-base leading-4 font-poppins font-normal text-gray-100 block p-3 rounded-md z-20
                            border border-gray-light">
                                    <option class="font-poppins font-normal text-base leading-4 text-gray-100" selected
                                        disabled>
                                        {{ __('Select Reason') }}</option>
                                    <option class="font-poppins font-normal text-base leading-4 text-gray-100"
                                        value="Canceled Event">
                                        {{ __('Canceled Event') }}</option>
                                    <option class="font-poppins font-normal text-base leading-4 text-gray-100"
                                        value="Copyright or Trademark Infringement">
                                        {{ __('Copyright or Trademark Infringement') }}</option>
                                    <option class="font-poppins font-normal text-base leading-4 text-gray-100"
                                        value="Fraudulent of Unauthorized Event">
                                        {{ __('Fraudulent of Unauthorized Event') }}</option>
                                    <option class="font-poppins font-normal text-base leading-4 text-gray-100"
                                        value="Offensive or Illegal Event">
                                        {{ __('Offensive or Illegal Event') }}</option>
                                    <option class="font-poppins font-normal text-base leading-4 text-gray-100"
                                        value="Spam">
                                        {{ __('Spam') }}</option>
                                    <option class="font-poppins font-normal text-base leading-4 text-gray-100"
                                        value="Other">
                                        {{ __('Other') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="w-full mt-5">
                            <textarea id="message" rows="4" required name="message"
                                class="block p-2.5 w-full focus:outline-none text-base leading-4 font-poppins font-normal text-gray-100
                        border border-gray-light rounded-md"
                                placeholder="{{ __('Describe your message...') }}"></textarea>

                        </div>
                        <input type="hidden" name="event_id" id="" value="{{ $data->id }}">
                        <div class="mt-5 flex justify-end">
                            <button
                                class="bg-primary text-white text-right font-poppins font-medium text-lg leading-7 px-5 py-2 rounded-md">{{ __('Send Message') }}</button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
            <div id="myModal" class="modal" style="display: none;">
                <span class="close cursor" onclick="closeModal()">&times;</span>
                <div class="modal-content">
                    <div class="mySlides" id="allSlide">
                        <img src="" id="eventGalleryImg" class="centered-image">
                    </div>
                </div>
                <a class="prev" onclick="minusSlides(-1)">&#10094;</a>
                <a class="next" onclick="plusSlides(1)">&#10095;</a>
            </div>
        </div>
    </div>
    <script>
        function initMap() {
            if ('{{ $data->lat }}' == '' || '{{ $data->lang }}' == '' || '{{ $gmapkey }}' == '' || '{{ $gmapkey }}'== null) {
                document.getElementById('map').innerHTML = '<img src="{{ url('frontend/images/map-placeholder.png') }}" class="w-full h-96 object-cover" alt="not found">';
            } else {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: parseFloat('{{ $data->lat }}'),
                        lng: parseFloat('{{ $data->lang }}')
                    },
                    zoom: 13
                });
                let marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat('{{ $data->lat }}'),
                        lng: parseFloat('{{ $data->lang }}')
                    },
                    map: map
                });
            }
        }
    </script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ $gmapkey }}&loading=async&callback=initMap"></script>
@endsection
