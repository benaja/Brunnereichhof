@extends("layouts.customer")
    <link rel="stylesheet" href="/css/customer-index.css">
@section('content')

<h1>Guten Tag {{Auth::user()->firstname}}!</h1>

<div class="card teal darken-1">
    <div class="card-content white-text">
        <span class="card-title">Arbeiten erfassen</span>
        <p>Erfasse deine geplanten Arbeiten direkt online, damit wir besser Planen können.</p>
    </div>
    <div class="card-action">
        <div class="first-link">
            <a href="/plan" class="lime-text text-lighten-3">Hier gehts zum erfassen</a>
        </div>
        <div class="second-link">
            <a href="/plan/edit" class="lime-text text-lighten-3">Bereits erfasstes wieder ändern</a>
        </div>
    </div>
</div>

@endsection