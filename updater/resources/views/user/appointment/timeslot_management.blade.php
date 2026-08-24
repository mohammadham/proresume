@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">
            {{  __('Appointment Settings') }}
            <ul class="breadcrumbs">
                <li class="nav-home">
                    <a href="{{ route('user-dashboard') }}">
                        <i class="flaticon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.appointment.timeslot', ['language' => request('language')]) }}">
                        {{ __('Time Slot Management') }}</a>
                </li>
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('user.appointment.timeslot', ['language' => request('language')]) }}">
                        {{ __('Days') }}
                    </a>
                </li>
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="#">
                        {{ __(@$existDay) }}
                    </a>
                </li>
            </ul>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="text-capitalize float-left">{{ __('Time Slots') }}
                        ({{  __(@$existDay) }})</h3>
                    <a href="{{ route('user.appointment.timeslot') }}"
                        class="btn btn-info btn-sm float-right">{{ __('Back') }}</a>
                    <button class="btn btn-primary addTF float-right btn-sm mr-1 "
                        data-day="monday">{{ __('Add') }}</button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($timeslots) == 0)
                                <h3 class="text-center">
                                    {{  __('NO TIMESLOTS AVAILABLE') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Start Time') }}</th>
                                                <th scope="col">{{ __('End Time') }}</th>
                                                <th scope="col">{{  __('Max Booking') }}
                                                </th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($timeslots as $tf)
                                                <tr>
                                                    <td>{{ $tf->start }}</td>
                                                    <td>{{ $tf->end }}</td>
                                                    <td>{{ $tf->max_booking }}</td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm editbtn" data-toggle="modal"
                                                            data-target="#editModal" data-start="{{ $tf->start }}"
                                                            data-end="{{ $tf->end }}"
                                                            data-max_booking="{{ $tf->max_booking }}"
                                                            data-timeslot_id="{{ $tf->id }}">{{ __('Edit') }}</button>
                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('user.timeslot.delete') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="timeslot_id"
                                                                value="{{ $tf->id }}">
                                                            <button type="submit" class="btn btn-danger btn-sm deletebtn">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-trash"></i>
                                                                </span>
                                                                {{ __('Delete') }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @includeIf('user.appointment.modals.timeslot_create')
    @includeIf('user.appointment.modals.timeslot_edit')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $(".addTF").on('click', function(e) {
                e.preventDefault();
                $("#createModal").modal('show');
                $("input[name='day']").val($(this).data('day'));
            })
        });
    </script>
@endsection
