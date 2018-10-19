@extends('layouts.worker.time') 
@section('calendar')
    @for($i = 0; $i < 7; $i++)
        <div class="day">
            @include('layouts.worker.calendar-day', ['timerecord' => $timerecords[$i], 'isMobile' => false, 'dayNamesGerman' => $dayNamesGerman])
        </div>
    @endfor
@endsection