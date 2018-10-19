@extends("layouts.admin")

@section('styles')
    <link rel="stylesheet" href="/css/rapport-create.css">
@endsection

@section('content')
    <h5 class="center-align choose-customer-title">Wähle den Kunden für den du den Wochenrapport erstellen willst!</h5>
    <div class="row">
        <div class="input-field col s12 m8 l6 offset-l3">
            <i class="material-icons prefix">search</i>
            <input type="text" id="autocomplete-input" class="autocomplete">
            <label for="autocomplete-input">Kunde suchen</label>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m10 offset-m1 l8 offset-l2 xl6 offset-xl3">
            <div class="collection">
                @foreach($customers as $customer)
                <a href="/rapport/addcustomer/{{$customer->id}}?date={{$date}}" class="collection-item">{{$customer->firstname}} {{$customer->lastname}}</a>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/js/autocomplete.js"></script>
    <script>
        let autoComplet = new Autocomplete('customer', 'rapport/addcustomer', '?date={{$date}}');
    </script>
@endsection