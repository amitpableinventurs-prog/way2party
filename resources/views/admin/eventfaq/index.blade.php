@extends('master')

@section('content')
<section class="section">
    @include('admin.layout.breadcrumbs', [
        'title' => __('FAQs'),
        'headerData' => __('Events'),
        'url' => '/events',
    ])

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
            </div>
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                    <div class="row mb-4 mt-2">
                        <div class="col-lg-8"><h2 class="section-title mt-0"> {{__('All FAQs')}}</h2></div>
                        <div class="col-lg-4 text-right">
                            @can('event_edit')
                            <button class="btn btn-primary add-button"><a href="{{route('event-faq.create', $event->id)}}" class="text-white"><i class="fas fa-plus"></i> {{__('Add New')}}</a></button>
                            @endcan
                        </div>
                    </div>
                  <div class="table-responsive">
                    <table class="table" id="report_table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{__('Question')}}</th>
                                <th style="width: 300px">{{__('Answer')}}</th>
                                @if(Gate::check('event_edit'))
                                <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($eventFaq as $item)
                                <tr>
                                    <td></td>
                                    <th> {{$item->question}} </th>
                                    <td >{{$item->answer}}</td>
                                    @if(Gate::check('event_edit'))
                                    <td>
                                        <a href="{{ route('event-faq.edit', $item->id) }}" title="Edit Event FAQ" class="btn-icon"><i class="fas fa-edit"></i></a>
                                        <a href="#"  onclick="deleteData('event-faq','{{$item->id}}');"  title="Delete Event FAQ" class="btn-icon text-danger"><i class="fas fa-trash-alt text-danger"></i></a>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
@endsection
