@extends("layouts.admin") 

@section('scripts')
	<script src="/js/moment.js"></script>
	<link rel="stylesheet" href="/css/rapport.css"> 
@endsection 

@section('content')
<input type="text" class="datepicker hide">
<h3 class="center-align">Wählen sie den gewünschten Kunden</h3>
<div class="row">
	<div class="col s12 l6 offset-l3">
		<div class="collection">
            <a href="/rapport/choosecustomer?type=week&date={{$rapports[0]->startdate}}" class="collection-item new-rapport">Kunde hinzufügen</a>
            @foreach($rapports as $rapport)
			<a href="/rapport/{{$rapport->id}}" class="collection-item"><p  class="right">{{$rapport->isFinished == 1 ? 'Abgeschlossen' : ''}}</p>{{$rapport->customer->firstname}} {{$rapport->customer->lastname}}</a>
            @endforeach
		</div>
	</div>
</div>
@endsection