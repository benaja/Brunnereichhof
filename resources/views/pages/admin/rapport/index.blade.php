@extends("layouts.admin") 

@section('scripts')
	<script src="/js/rapport.js"></script>
	<link rel="stylesheet" href="/css/rapport.css"> 
@endsection 

@section('content')

	<h3 class="center-align">Wählen sie die Rapportart!</h3>
	<div class="row">
		<div class="col s12 m10 l8 offset-m1 offset-l2">
			<div class="row">
				<div class="col s12">
					<div class="card teal darken-1">
						<div class="card-content white-text">
							<span class="card-title">Wochenrapport</span>
							<p></p>
						</div>
						<div class="card-action">
							<a href="/rapport/show" class="lime-text text-lighten-3">Wochenrapporte anzeigen</a>
							<a href="/rapport/choosecustomer?type=week" class="lime-text text-lighten-3">Neue Woche erfassen</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<div class="card teal darken-1">
						<div class="card-content white-text">
							<span class="card-title">Monatsrapport</span>
						</div>
						<div class="card-action">
							<a href="#" class="lime-text text-lighten-3">Monatsrapport anzeigen</a>
							<a href="/rapport/choosecustomer?type=month" class="lime-text text-lighten-3">Neuen Monat erfassen</a>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<div class="card teal darken-1">
						<div class="card-content white-text">
							<span class="card-title">Jahresbericht</span>
						</div>
						<div class="card-action">
							<a href="#" class="lime-text text-lighten-3">Jahresberichte anzeigen</a>
							<a href="/rapport/choosecustomer?type=year" class="lime-text text-lighten-3">Jahresbericht erfassen</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection