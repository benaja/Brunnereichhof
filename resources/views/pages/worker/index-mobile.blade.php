@extends('layouts.worker.time') 
@section('calendar')
    <div class="mobile-day">
        @include('layouts.worker.calendar-day', ['hours' => $timerecord->hours, 'isMobile' => true])
    </div>
@endsection