@extends('master')

@section('content')
    <section class="section">
        @include('admin.layout.breadcrumbs', [
            'title' => __('Event Detail '),
            'headerData' => __('Event') ,
            'url' => 'events' ,
        ])

      <div class="section-body">
         <div class="row event-single">
             <div class="col-lg-8">
                 <div class="card">
                     <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="event-img w-100" style="background: url({{url('images/upload/'.$event->image)}})">
                                </div>
                            </div>
                            <div class="col-12 event-description">
                                <h2 class="mt-3">{{$event->name}} <button type="button" class="btn btn-primary "><a class="text-white" href="{{url($event->id.'/'.preg_replace('/\s+/', '-', $event->name).'/tickets')}}">{{__('Manage Tickets')}}</a></button></h2>
                                <p> {!!$event->description!!}  </p>
                            </div>
                        </div>
                        <div class="row ml-0 mr-0 mt-4">
                            <div class="col-lg-3">
                                <div class="card single-card-light">
                                    <div class="row">
                                        <div class="col-3 text-center">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="col-9">
                                            <p class="mb-0">{{__('People allowed')}}</p>
                                            <span>{{$event->people}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card single-card-light">
                                    <div class="row">
                                        <div class="col-3 text-center">
                                            <i class="far fa-calendar-alt"></i>
                                        </div>
                                        <div class="col-9">
                                            <p class="mb-0">{{__('Date')}}</p>
                                            <span>{{Carbon\Carbon::parse($event->start_time)->format('l').','}}</span>
                                            <span>{{$event->start_time->format('d F Y')}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card single-card-light">
                                    <div class="row">
                                        <div class="col-2 text-center">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="col-9">
                                            <p class="mb-0">{{__('Location')}}</p>
                                            <span> {{ $event->address }} </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-0 mr-0 mt-4">
                            <div class="col-12">
                                <div class="card single-card-light">
                                    <p class="mb-2">{{ __('Share this event') }}</p>
                                    <div class="d-flex flex-wrap" style="gap:8px;">
                                        <input type="text" readonly id="orgEventShareUrl"
                                            value="{{ url('/event/' . $event->id . '/' . \Illuminate\Support\Str::slug($event->name)) }}"
                                            class="form-control" style="max-width:320px;" onclick="this.select();">
                                        <button type="button" class="btn btn-primary" onclick="copyOrgEventShareUrl()">{{ __('Copy Link') }}</button>
                                        <a class="btn btn-info text-white" target="_blank" rel="noopener noreferrer"
                                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/event/' . $event->id . '/' . \Illuminate\Support\Str::slug($event->name))) }}">Facebook</a>
                                        <a class="btn btn-dark" target="_blank" rel="noopener noreferrer"
                                            href="https://twitter.com/intent/tweet?url={{ urlencode(url('/event/' . $event->id . '/' . \Illuminate\Support\Str::slug($event->name))) }}&text={{ urlencode($event->name) }}">Twitter</a>
                                        <a class="btn btn-success" target="_blank" rel="noopener noreferrer"
                                            href="https://api.whatsapp.com/send?text={{ urlencode($event->name . ' ' . url('/event/' . $event->id . '/' . \Illuminate\Support\Str::slug($event->name))) }}">WhatsApp</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            function copyOrgEventShareUrl() {
                                var input = document.getElementById('orgEventShareUrl');
                                input.select();
                                input.setSelectionRange(0, 99999);
                                navigator.clipboard.writeText(input.value);
                            }
                        </script>
                     </div>
                </div>
                <h2 class="section-title"> {{__('Recent Sales')}}</h2>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="">
                                <thead>
                                    <tr>
                                        <th>{{__('Order Id')}}</th>
                                        <th>{{__('Customer Name')}}</th>
                                        <th>{{__('Ticket Name')}}</th>
                                        <th>{{__('Date')}}</th>
                                        <th>{{__('Sold Ticket')}}</th>
                                        <th>{{__('Payment')}}</th>
                                        <th>{{__('Payment Gateway')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($event->sales as $item)
                                        <tr>
                                            <td>{{$item->order_id}}</td>
                                            <td>{{$item->customer->name}}</td>
                                            <th>{{$item->ticket->name}}</th>
                                            <th>{{$item->created_at->format('Y-m-d')}}</th>
                                            <th>{{$item->quantity}}</th>
                                            <th>{{$currency.$item->payment}}</th>
                                            <th>{{$item->payment_type}}</th>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
             </div>
             <div class="col-lg-4">
                 @if(count($event->ticket)>0)
                    @foreach ($event->ticket as $item)
{{--                        <div class="card card-hero">--}}
{{--                            <div class="card-header">--}}
{{--                                <p class="text-right">{{__('Sales end on')}}</p>--}}
{{--                                <p class="text-right">{{$item->start_time->format('Y-m-d').','.$item->end_time->format('h:i a')}}</p>--}}
{{--                                <div class="card-icon">--}}
{{--                                    <i class="fas fa-ticket-alt"></i>--}}
{{--                                </div>--}}
{{--                                <div class="card-description">{{$item->name}} | <span>{{$currency.$item->price}}</span></div>--}}
{{--                                <h4>{{$item->used_ticket.'/'.$item->quantity}} </h4>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                         <div class="card text-white overflow-hidden" style="background-image: linear-gradient(to bottom, var(--primary_color), var(--middle_light_primary_color));">
                             <div class="card-header justify-content-end border-0">
                                 <div class="d-inline-block text-end">
                                     <h4 class="text-white">{{__('Sales end on')}}</h4>
                                     <p>{{$item->start_time->format('Y-m-d').','.$item->end_time->format('h:i a')}}</p>
                                 </div>
                             </div>
                             <div class="card-body pt-0 pb-0" style="z-index: 9999;">
                                 <h1>{{$item->used_ticket.'/'.$item->quantity}} </h1>
                                 <div class="card-icon" style="right: 40px; top: 100px; position: absolute; opacity: 0.3; z-index: -1;">
                                     <i class="fas fa-ticket-alt" style="font-size: 86px;"></i>
                                 </div>
                             </div>
                             <div class="card-footer">
                                 {{$item->name}} | <span>{{$currency.$item->price}}</span>
                             </div>
                         </div>
                    @endforeach
                @else

                <div class="card card-hero">
                    <div class="card-header">

                    <div class="card-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="card-description">{{__('No tickets')}}</div>
                    <a href="{{url($event->id.'/ticket/create')}}"><button type="button" class="btn btn-ticket" ><i class="fas fa-plus"></i> </button>  </a>
                    </div>
                </div>
                @endif

             </div>
         </div>
      </div>
    </section>
@endsection
