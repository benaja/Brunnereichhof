@extends('layouts.admin')

@section('scripts')
    <script src="/js/employee.js"></script>
    <link rel="stylesheet" href="/css/employee.css">
@endsection

@section('content')

<h1 class="center-align">Mitarbeiter erstellen</h1>
    <div class="row">
        <form class="col s12 xl10 offset-xl1 employee-create form" action="/employee"  method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="row">
                <div class="input-field col s12 m6">
                        @include('layouts.admin.input-element', ['element' => 'firstname', 'name' => 'Vorname*', 'required' => true])
                </div>
                <div class="input-field col s12 m6">
                        @include('layouts.admin.input-element', ['element' => 'lastname', 'name' => 'Nachname*', 'required' => true])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                        @include('layouts.admin.input-element', ['element' => 'callname', 'name' => 'Rufname'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                        @include('layouts.admin.input-element', ['element' => 'nationality', 'name' => 'Nationalität*', 'required' => true])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6 checkbox">
                    <label>
                        <input type="checkbox" class="filled-in" {{old('isIntern') == 1 ? 'checked': ''}}  name="isIntern" value="1"/>
                        <span id="isIntern">Intern</span>
                    </label>
                </div>
                <div class="input-field col s6 checkbox">
                    <label>
                        <input type="checkbox" class="filled-in" {{old('isDriver') == 1 ? 'checked' : ''}} name="isDriver" value="1"/>
                        <span id="isDriver">Fahrer</span>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <label>
                        <input type="checkbox" class="filled-in" {{old('german_knowledge') == 1 ? 'checked' : ''}} name="german_knowledge" value="1"/>
                        <span id="german_knowledge">Deutschkenntnisse</span>
                    </label>
                </div>
                <div class="input-field col s6">
                    <label>
                        <input type="checkbox" class="filled-in" {{old('english_knowledge') == 1 ? 'checked' : ''}} name="english_knowledge" value="1"/>
                        <span id="english_knowledge">Englischkenntnisse</span>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m6 sex">
                    <select name="sex">
                        <option value="" disabled {{old('sex') == null ? 'selected' : ''}}>Geschlecht</option>
                        <option {{old('sex') == "Mannlich" ? 'selected' : ''}} value="Männlich">Männlich</option>
                        <option {{old('sex') == "Weiblich" ? 'selected' : ''}} value="Weiblich">Weiblich</option>
                    </select>
                    <label>Geschlecht</label>
                    <strong>{{$errors->first('sex') != "" ? "Geben sie das Geschlecht an!" : ""}}</strong>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <textarea id="comment" class="input materialize-textarea {{$errors->has('comment') ? 'invalid' : ''}}" name="comment" value="{{ old('comment')}}"></textarea>
                    <label for="comment">Kommentar</label>
                    <strong>{{$errors->first('comment')}}</strong>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 checkbox">
                    @include('layouts.admin.input-element', ['element' => 'experience', 'name' => 'Erfahrung'])
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    @include('layouts.admin.input-element', ['element' => 'allergy', 'name' => 'Allergie'])
                </div>
            </div>
            <div class="row">
                <div class="col s12 file-field input-field">
                    <div class="btn">
                        <span>Profilbild</span>
                        <input name="profileimage" id="profileimage" type="file">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text">
                    </div>
                    <strong>{{$errors->first('profileimage')}}</strong>
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