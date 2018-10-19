@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/customer.css?v=1.0">
@endsection

@section('content')

<h1 class="center-align">Hofmitarbeiter erstellen</h1>
    <div class="row">
        <form class="col customer-create" action="/customer"  method="POST">
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
                    @include('layouts.admin.input-element', ['element' => 'street', 'name' => 'Strasse + Nr*', 'required' => true])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s8">
                    @include('layouts.admin.input-element', ['element' => 'place', 'name' => 'Ort*', 'required' => true])
                </div>
                <div class="input-field col s4">
                    @include('layouts.admin.input-element', ['element' => 'plz', 'name' => 'PLZ*', 'type' => 'number', 'required' => true])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'addition', 'name' => 'Zusatz'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    @include('layouts.admin.input-element', ['element' => 'mobile', 'name' => 'Mobile'])
                </div>
                <div class="input-field col s6">
                    @include('layouts.admin.input-element', ['element' => 'phone', 'name' => 'Festnetz'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'email', 'name' => 'E-Mail'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'customer_number','type' => 'number', 'name' => 'Kundennummer'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m6 checkbox">
                    <label>
                        <input type="checkbox" class="filled-in" {{old('needs_payment_order') == 1 ? 'checked': ''}} name="needs_payment_order" value="1"/>
                        <span id="needs_payment_order">Benötigt Einzahlungsschein</span>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m6 checkbox">
                    <label>
                        <input id="hasCatering" type="checkbox" class="filled-in" {{old('hasCatering') == 1 ? 'checked': ''}} name="hasCatering" value="1"/>
                        <span>Verpflegung</span>
                    </label>
                </div>
            </div>
            <div class="row verpflegung">
                <div class="input-field col s8 citchen_col">
                    @include('layouts.admin.input-element', ['element' => 'kitchen_infrastructure', 'name' => 'Ausstattung der Küche'])
                </div>
                <div class="input-field col s4 max_catering_col">
                    @include('layouts.admin.input-element', ['element' => 'max_catering', 'name' => 'Max Anzahl Verpflegung', 'type' => 'number'])
                </div>
            </div>
            <div class="row verpflegung" id="verpflegung">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'comment_catering', 'name' => 'Bemerkungen zur Verpflegung'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'driver_info', 'name' => 'Fahrerinfo'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'maps', 'name' => 'Google-Maps Link'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'comment', 'name' => 'Allgemeine Bemerkungen'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input id="username" type="text" class="input validate {{$errors->has('username') ? 'invalid' : ''}}" name="username" value="{{ old('username')}}">
                    <label for="username" id="username_label">Benutzername</label>
                    <strong>{{$errors->first('username')}}</strong>
                    <p>Nach dem Erstellen des Kunden erhält er automatisch eine E-Mail mit seinem Passwort. Wenn die E-Mail nicht vorhanden ist, wird das Passwort und den Benutzernamen ihnen angezeigt, welches sie dann dem Kunden persönlich übermitteln können.</p> 
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