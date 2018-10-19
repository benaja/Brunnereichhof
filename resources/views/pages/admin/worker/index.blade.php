@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/employee.css">
@endsection

@section('content')

    <h1 class="center-align">Hofmitarbeiter Übersicht</h1>
    <div class="row">
        <div class="input-field col s12 m8 l6 offset-l3">
            <i class="material-icons prefix">search</i>
            <input type="text" id="autocomplete-input" class="autocomplete">
            <label for="autocomplete-input">Mitarbeiter suchen</label>
        </div>
    </div>
    <ul class="collapsible">
        @foreach($workers as $worker)
        <li>
            <div class="collapsible-header">
                <i class="material-icons">account_circle</i>
                {{$worker->firstname}} {{$worker->lastname}}
                <span class="material-icons badge">arrow_drop_down</span>
                <a href="/worker/{{$worker->id}}" class="waves-effect waves-light btn-small badge details">Details</a>
            </div>
            <div class="collapsible-body">
                <div class="row">
                    <div class="col s12 m4">
                        <h5>Aktualler Monat</h5>
                        <p>Arbeitsstunden: {{$worker->totalHoursOfThisMonth()}}h</p>
                        <p>Mittagessen: {{$worker->getNumberOfLunches(new \DateTime('first day of this month'))}}</p>
                    </div>
                    <div class="col s12 m4">
                        <h5>Vergangener Monat</h5>
                        <p>Arbeitsstunden: {{$worker->totalHours(new \DateTime('first day of last month'))}}h</p>
                        <p>Mittagessen: {{$worker->getNumberOfLunches(new \DateTime('first day of last month'))}}</p>
                    </div>
                    <div class="col s12 m4">
                        <h5>Ferien dieses Jahr</h5>
                        <p>Geplant: {{$worker->holydaysPlant(new \DateTime('now'))}} Tage</p>
                        <p>Bezogen: {{$worker->holydaysDone(new \DateTime('now'))}} Tage</p>
                    </div>
                </div>
            </div>
        </li>
        @endforeach
    </ul>

    <a href="/worker/create" id="addbutton" class="btn-floating btn-large waves-effect waves-light greed"><i class="material-icons">add</i></a>


@endsection

@section('scripts')
    <script src="/js/autocomplete.js"></script>
    <script>
        var autocomplete = new Autocomplete('worker');
    </script>
@endsection