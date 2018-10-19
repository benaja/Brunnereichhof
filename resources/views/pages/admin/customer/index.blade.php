@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/customer.css?v=1">
@endsection

@section('content')

    <h1 class="center-align">Kunden-Übersicht</h1>
    <div class="row">
        <div class="input-field col s12 m8 l6 offset-l3">
            <i class="material-icons prefix">search</i>
            <input type="text" id="autocomplete-input" class="autocomplete">
            <label for="autocomplete-input">Kunde suchen</label>
        </div>
    </div> 
    <ul class="collapsible">
        @foreach($customers as $customer)
            @include('layouts.admin.single-customer')
        @endforeach
    </ul>

    <a href="/customer/create" id="addbutton" class="btn-floating btn-large waves-effect waves-light greed"><i class="material-icons">add</i></a>
@endsection

@section('scripts')
<script src="/js/autocomplete.js"></script>
<script type="text/javascript">
    $('.collapsible').collapsible();
    var autocomplete = new Autocomplete('customer');
</script>
@endsection