@extends("layouts.admin")

@section('scripts')
    <script>
        var createType = "week";
        var customerId = {{$rapport->customer->id}};
    </script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="/js/rapport-create.js?v=1.0"></script>
    <script src="/js/project.js?v=1.0"></script>
    <link rel="stylesheet" href="/css/rapport-create.css?v=1.0">
    <link rel="stylesheet" href="/css/animate.css">
@endsection

@section('content')
    <div class="row">
        <h5 class="center-align choose-customer-title col s10 offset-s1">
            Rapport für "{{$rapport->customer->firstname}} {{$rapport->customer->lastname}}"
            der Woche {{$startdate->format('W')}} 
            ({{$startdate->format('d.m.Y')}} - {{$startdate->modify('+6 days')->format('d.m.Y')}})
        </h5>
    </div>
    <div class="divider"></div>
    <div class="row toolbar">
        <div class="col l3 s12 center-align">
            <a class="waves-effect waves-light btn" onclick="openAddEmployee()"><i class="material-icons left">add</i>Mitarbeiter hinzufügen</a>
        </div>
        <div class="col l3 s12 center-align">
            <a href="/rapport/{{$rapport->id}}/pdf" class="waves-effect waves-light btn"><i class="material-icons left">picture_as_pdf</i>PDF Generieren</a>
        </div>
        <div class="col l3 s12 center-align">
            <a class="waves-effect waves-light btn red" onclick="deleteRapport()"><i class="material-icons left">delete</i>Löschen</a>
        </div>
        <div class="col l3 s12 center-align isfinished">
            <label>
                <input type="checkbox" class="filled-in" onchange="updateCheckbox(this)" {{$rapport->isFinished == 1 ? 'checked': ''}} name="isFinished" value="1"/>
                <span id="isFinished">Abgeschlossen</span>
            </label>
        </div>
    </div>
    <div class="divider"></div>
    <div class="row table">
        <form class="col s12 form">
            <table>
                <thead>
                    <tr>
                        <th>Wochentag</th>
                        <th>Montag</th>
                        <th>Dienstag</th>
                        <th>Mittwoch</th>
                        <th>Donnertag</th>
                        <th>Freitag</th>
                        <th>Samstag</th>
                    </tr>
                </thead>    
                <tbody class="employees">
                    <tr>
                        <th>Bemerkung</th>
                        @foreach($commentDays as $commentDay)
                            <td class="employee-day">
                                <div class="input-field">
                                <textarea id="comment_{{$commentDay}}" class="materialize-textarea" onblur="updateComment('{{$commentDay}}', this)">{{$rapport->$commentDay}}</textarea>
                                    <label for="comment_{{$commentDay}}">Bemerkung</label>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Projekt</th>
                        @for($i = 0; $i < 6; $i++)
                            <td class="employee-day">
                                <div class="input-field">
                                    <select onchange="updateAllProjects({{$i}}, this)">
                                        <option value="" disabled selected>Projekt wählen</option>
                                        @foreach($rapport->customer->projects as $project)
                                            <option value="{{$project->id}}">{{$project->name}}</option>
                                        @endforeach
                                        <option value="0">Projekt hinzufügen</option>
                                    </select>
                                    <label>Projekt</label>
                                </div>
                            </td>
                        @endfor
                    </tr>
                    @php $totalHours = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]; @endphp
                    @foreach($employeesInRapport as $employeeCollection)
                    <tr id="{{$employeeCollection[0]->employee->id}}" class="employee">
                        <th>
                            <p>{{$employeeCollection[0]->employee->firstname}} {{$employeeCollection[0]->employee->lastname}}</p>
                            <a onclick="deleteEmployee({{$employeeCollection[0]->employee->id}})" class="waves-effect waves-light btn-small red delete-button">Entfernen</a>
                        </th>
                        @for($i = 0; $i < 6; $i++)
                        @php $totalHours[$i] += $employeeCollection[$i]->hours @endphp
                        <td class="employee-day">
                            <div class="row small-row">
                                <div class="input-field col s12 small-input">
                                <input id="hours_{{$i}}_{{$employeeCollection[0]->employee->id}}" type="number" class="validate hours_{{$i}}" onblur="updateHours({{$i}}, {{$employeeCollection[0]->employee->id}}, this)" value="{{$employeeCollection[$i]->hours}}">
                                    <label for="hours_{{$i}}_{{$employeeCollection[0]->employee->id}}">Stunden</label>
                                </div>
                            </div> 
                            <div class="row small-row">
                                <div class="input-field col s12">
                                <select onchange="updateFood({{$i}}, {{$employeeCollection[0]->employee->id}}, this)">
                                        <option value="" disabled {{$employeeCollection[$i]->foodtype_id == null ? 'selected' : ''}}>Verpflegung wählen</option>
                                        <option value="1" {{$employeeCollection[$i]->foodtype_id == 1 ? 'selected' : ''}}>Eichhof</option>
                                        <option value="2" {{$employeeCollection[$i]->foodtype_id == 2 ? 'selected' : ''}}>Kunde</option>
                                        <option value="3" {{$employeeCollection[$i]->foodtype_id == 3 ? 'selected' : ''}}>Keine Angabe</option>
                                    </select>
                                    <label>Verpflegung</label>
                                </div>
                            </div>
                            <div class="row small-row">
                                <div class="input-field col s12">
                                <select onchange="updateProject({{$i}}, {{$employeeCollection[0]->employee->id}}, this)" class="project_{{$i}}">
                                        <option value="" disabled {{$employeeCollection[$i]->project_id == null ? 'selected' : ''}}>Projekt wählen</option>
                                        @foreach($rapport->customer->projects as $project)
                                            <option value="{{$project->id}}" {{$employeeCollection[$i]->project_id == $project->id ? 'selected' : ''}}>{{$project->name}}</option>
                                        @endforeach
                                        <option value="0">Projekt hinzufügen</option>
                                    </select>
                                    <label>Projekt</label>
                                </div>
                            </div>
                        </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Stunden Total</th>
                        @for($i = 0; $i < 6; $i++)
                            <td id="totalhours_{{$i}}">{{$totalHours[$i]}}</td>
                        @endfor
                    </tr>
                </tfoot>
            </table>
        <form>
    </div>
    <div class="row">
        <div class="col s12 center-align">
            
        </div>
    </div>
    <div id="popup_background" class="hide">
        <div id="add_employee" class="form hide">
            <h4 class="center-align">Mitarbeiter hinzufügen</h4>
            <div class="row">
                <div class="col s6 offset-s3 employees-checkbox">
                    @foreach($employees as $employee)
                    <p>
                        <label>
                        <input type="checkbox" class="filled-in employee-input" value="{{$employee->id}}"/>
                        <span>{{$employee->firstname}} {{$employee->lastname}}</span>
                        </label>
                    </p>
                    @endforeach
                </div>
            </div>
            <p class="center-align">
            <a class="waves-effect waves-light btn center-aling" onclick="updateEmployees()"><i class="material-icons left">send</i>Speichern</a>
            </p>
        </div>
    </div>
    <div id="project_popup_background" class="hide">
        <div id="add_project" class="form hide">
            <h4 class="center-align">Projekte bearbeiten</h4>
            <div class="row">
                <div class="col s10 offset-s1 employees-checkbox">
                    <p class="bezeichnung projekt_titel">Projekte</p>
                    <div class="chips chips-autocomplete">
                    </div>     
                </div>
            </div>
            <p class="center-align">
            <a class="waves-effect waves-light btn center-aling" onclick="window.location.reload()"><i class="material-icons left">send</i>Speichern</a>
            </p>
        </div>
    </div>
@endsection