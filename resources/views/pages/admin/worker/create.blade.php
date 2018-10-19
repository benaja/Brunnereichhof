@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/customer.css?v=1.0">
@endsection

@section('content')

<h1 class="center-align">Kunde erstellen</h1>
    <div class="row">
        <form class="col customer-create" action="/worker"  method="POST">
            {{ csrf_field() }}
            <div class="row">
                <div class="input-field col s6">
                    @include('layouts.admin.input-element', ['element' => 'firstname', 'name' => 'Vorname*', 'properties' => 'onkeydown=enterUsername() oninput=enterUsername()', 'required' => true])
                </div>
                <div class="input-field col s6">
                    @include('layouts.admin.input-element', ['element' => 'lastname', 'name' => 'Nachname*', 'properties' => 'onkeydown=enterUsername() oninput=enterUsername()', 'required' => true])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'email', 'name' => 'Email*', 'required' => true, 'type' => 'email'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input id="username" type="text" class="input validate {{$errors->has('username') ? 'invalid' : ''}}" name="username" value="{{ old('username')}}" required>
                    <label for="username" id="username_label">Benutzername</label>
                    <strong>{{$errors->first('username')}}</strong>
                    <p>Nach dem Erstellen des Hofmitarbeiters erhält er automatisch eine E-Mail mit seinem Passwort.</p> 
                </div>
            </div>
            <div class="row">
                <div class="col s4 offset-s4">
                    <button class="btn waves-effect waves-light save_button" type="submit" name="action">Speichern
                        <i class="material-icons right">send</i>
                    </button>
                </div>
            </div>

        </form>
    </div>
@endsection

@section('scripts')
    <script src="/js/customer.js?v=1.0"></script>
@endsection