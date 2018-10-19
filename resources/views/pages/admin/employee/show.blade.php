@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/employee.css">
    <link rel="stylesheet" href="/css/sweetalert2.min.css">
@endsection

@section('content')
    <script>
        var employeeId = {{$employee->id}};
    </script>
    <div class="row single-employee">
        <form class="col s12 xl10 offset-xl1 form">
            <div class="row">
                <div class="col s12 m6">
                    <div class="row simpel-row">
                        <div class="col s12">
                            <div class="row">
                                <p class="col s4 bezeichnung">Vorname</p> 
                                <p id="firstname" class="col s8 info_content {{ $employee->firstname == "" ? 'empty' : '' }}">{{$employee->firstname}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <div class="row simpel-row">
                        <div class="col s12">
                            <div class="row">
                                <p class="col s4 bezeichnung">Nachname</p>
                                <p id="lastname" class="col s8 info_content {{ $employee->lastname == "" ? 'empty' : '' }}">{{$employee->lastname}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <div class="row simpel-row">
                        <div class="col s12">
                            <div class="row">
                                <p class="col s4 bezeichnung">Ruffname</p>
                                <p id="callname" class="col s8 info_content {{ $employee->callname == "" ? 'empty' : '' }}">{{$employee->callname}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <div class="row simpel-row">
                        <div class="col s12">
                            <div class="row">
                                <p class="col s4 bezeichnung">Nationalität</p> 
                                <p id="nationality" class="col s8 info_content {{ $employee->nationality == "" ? 'empty' : '' }}">{{$employee->nationality}}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col s12 m6 profileimage-container">
                    @if($employee->profileimage == null)
                        <img src="" class="profileimage materialboxed" id="profileimage">
                        <div class="new-image">
                            <a class="waves-effect waves-light btn create-image left"><i class="material-icons right">add</i>Bild hinzufügen</a>
                        </div>
                    @else
                        <img src="/profileimages/{{$employee->profileimage}}" class="profileimage materialboxed" id="profileimage">
                        <a class="waves-effect waves-light btn edit-image"><i class="material-icons right">edit</i>Bild ändern</a>
                        <a class="waves-effect waves-light btn delete-image"><i class="material-icons right">delete</i>Bild entfernen</a>
                    @endif
                    <input type="file" name="profileimage" id="profileimage-edit">
                </div>
            </div>
            <div class="divider"></div>
            <div class="row checkboxes simpel-row">
                <div class="input-field col s6 checkbox">
                    <label>
                        <input type="checkbox" class="filled-in" {{$employee->isIntern == 1 ? 'checked' : ''}} name="isIntern" value="1"/>
                        <span id="isIntern">Intern</span>
                    </label>
                </div>
                <div class="input-field col s6 checkbox">
                    <label>
                        <input type="checkbox" class="filled-in" {{$employee->isDriver == 1 ? 'checked' : ''}} name="isDriver" value="1"/>
                        <span id="isDriver">Fahrer</span>
                    </label>
                </div>
            </div>
            <div class="row checkboxes simpel-row">
                <div class="input-field col s6 checkbox">
                    <label>
                        <input type="checkbox" class="filled-in" {{$employee->german_knowledge == 1 ? 'checked' : ''}} name="german_knowledge" value="1"/>
                        <span id="german_knowledge" >Deutschkenntnisse</span>
                    </label>
                </div>
                <div class="input-field col s6 checkbox">
                    <label>
                        <input type="checkbox" class="filled-in" {{ $employee->english_knowledge == 1 ? 'checked' : ''}}  name="english_knowledge" value="1"/>
                        <span id="english_knowledge">Englischkenntnisse</span>
                    </label>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row simpel-row">
                <div class="col s12 m6 input-field geschlecht">
                    <select name="sex">
                        <option {{$employee->sex ==  "Männlich" ? "selected" : ""}} value="Männlich">Männlich</option>
                        <option {{$employee->sex ==  "Weiblich" ? "selected" : ""}} value="Weiblich">Weiblich</option>
                    </select>
                    <label>Geschlecht</label>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row simpel-row">
                <div class="col s12">
                    <div class="row">
                        <p class="col s4 m2 bezeichnung">Kommentar</p>
                        <p id="comment" class="col s8 m10 info_content {{ $employee->comment == "" ? 'empty' : '' }}">{{$employee->comment}}</p>
                    </div>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row simpel-row">
                <div class="col s12">
                    <div class="row">
                        <p class="col s4 m2 bezeichnung">Erfahrung</p>
                        <p id="experience" class="col s8 m10 info_content {{ $employee->experience == "" ? 'empty' : '' }}">{{$employee->experience}}</p>
                    </div>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row simpel-row">
                <div class="col s12">
                    <div class="row">
                        <p class="col s4 bezeichnung">Allergie</p>
                        <p id="allergy" class="col s8 m10 info_content {{ $employee->allergy == "" ? 'empty' : '' }}">{{$employee->allergy}}</p>
                    </div>
                </div>
            </div>
            <div class="row simpel-row">
                <div class="input-field col s12 checkbox">
                    <label>
                        <input type="checkbox" {{ $employee->isActive == 1 ? 'checked' : ''}} class="filled-in"  name="isActive" value="1"/>
                        <span id="isActive">Aktiv</span>
                    </label>
                </div>
            </div>
            <div class="row simpel-row">
                <div class="col s12">
                    <div class="delete_button">
                        <a class="waves-effect waves-light btn red" onclick="deleteItem('/employee/',{{$employee->id}}, 'Der Mitarbeiter')"><i class="material-icons left">delete</i>Löschen</a>
                    </div>
                </div>
            </div>
        </from>
    </div>

@endsection

@section('scripts')
    <script src="/js/employee.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        let propertyEditor = new PropertyEditor('employee', {{$employee->id}});
    </script>
@endsection