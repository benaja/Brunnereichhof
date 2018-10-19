@extends("layouts.admin") 

@section('styles')
	<link rel="stylesheet" href="/js/tiny-date-picker-master/tiny-date-picker.css">
	<link rel="stylesheet" href="/css/rapport.css?v=1.0"> 
@endsection 

@section('content')
<input type="text" class="datepicker hide">
<h3 class="center-align">Wählen sie die gewünschte Rapportwoche</h3>
<div class="row">
	<div class="col s12 l6 offset-l3">
		<div class="collection">
		<a href="/rapport/choosecustomer?type=week&date={{$newWeek->format('d.m.Y')}}" class="collection-item new-rapport">Rapport für Woche {{$newWeek->format('W')}} erstellen</a>
		@foreach($rapportWeeks as $rapportWeek)
			<a href="/rapport/week/{{$rapportWeek['date']->format('d.m.Y')}}" class="collection-item">
				<p  class="right">{{$rapportWeek['isFinished'] ? 'Abgeschlossen' : ''}}</p>
				Woche {{$rapportWeek['date']->format('W')}} ({{$rapportWeek['date']->format('d.m.Y')}}-{{$rapportWeek['date']->modify('+6 days')->format('d.m.Y')}})
			</a>
		@endforeach
		</div>
	</div>
</div>

<a onclick="selectDate()"  id="add_rapport_button"class="btn-floating btn-large waves-effect waves-light greed addbutton"><i class="material-icons">add</i></a>

<input id="select_date"></input>
@endsection

@section('scripts')
	<script src="/js/tiny-date-picker-master/dist/tiny-date-picker.js"></script>
	<script src="/js/rapport.js?v=1.0"></script>
@endsection