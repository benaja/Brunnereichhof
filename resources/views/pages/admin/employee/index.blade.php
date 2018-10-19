@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/employee.css">
@endsection

@section('content')

    <h1 class="center-align">Mitarbeiter-Übersicht</h1>
    <div class="row">
        <div class="input-field col s12 m8 l6 offset-l3">
            <i class="material-icons prefix">search</i>
            <input type="text" id="autocomplete-input" class="autocomplete">
            <label for="autocomplete-input">Mitarbeiter suchen</label>
        </div>
    </div>
    <ul class="collapsible">
        @foreach($employees as $employee)
            @include('layouts.admin.single-employee')
        @endforeach
    </ul>
    <a href="/employee/create" id="addbutton" class="btn-floating btn-large waves-effect waves-light greed"><i class="material-icons">add</i></a>

@endsection

@section('scripts')
    <script src="/js/employee.js"></script>
    <script src="/js/autocomplete.js"></script>
    <script>
        var autocomplete = new Autocomplete('employee');
    </script>
@endsection