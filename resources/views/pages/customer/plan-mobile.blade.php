@extends("layouts.customer")

@section('styles')
    <link rel="stylesheet" href="/css/plan.css?v=1.0">
@endsection

@section('content')
    <h3 class="center-align">Hier kannst du deine Arbeiten Planen</h3>
    <div class="row">
        <div id="chose_week">
            <div class="row">
                <h6 class="center-align col s10 offset-s1">Wähle bitte alle Kallenderwochen in denen du unsere Unterschtützung auf deinen Feldern brauchen kannst.</h6>
            </div>
            <div class="row">
                <div class="col s3">
                    @for($i = 1; $i <= 13; $i++)
                        @include('layouts.customer.weekCheck', ['week' => $i])
                    @endfor
                </div>
                <div class="col s3">
                    @for($i = 14; $i <= 26; $i++)
                        @include('layouts.customer.weekCheck', ['week' => $i])
                    @endfor
                </div>
                <div class="col s3">
                    @for($i = 27; $i <= 39; $i++)
                        @include('layouts.customer.weekCheck', ['week' => $i])
                    @endfor
                </div>
                <div class="col s3">
                    @for($i = 40; $i <= 52; $i++)
                        @include('layouts.customer.weekCheck', ['week' => $i])
                    @endfor
                </div>
            
            </div>
            <div class="row">
                <div class="col right">
                    <a class="waves-effect waves-light btn" onclick="showWeekTime()"><i class="material-icons right">send</i>weiter</a>
                </div>
                <div class="col left">
                    <a class="waves-effect waves-light btn"><i class="material-icons left">edit</i>bearbeiten</a>
                </div>
            </div>
        </div>
        <div id="select_time">
            <div class="row">
                <h6 class="center-align col s12">Gib nun an wiviele Stunden die Arbeiten in den jeweiligen Wochen ca. brauchen und um was es sich dabei handelt.</h6>
            </div>
            <div class="row white">
                <div class="col s12">
                    @for($i = 1; $i <= 52; $i++)
                        <div id="{{$i}}_time" class="week_table">
                            <h6 class="center-align">Woche {{$i}}</h6>
                            <div class="input-field">
                                <input id="{{$i}}_hours" name="{{$i}}_hours" type="number" class="validate">
                                <label for="{{$i}}_hours">Studen</label>
                            </div>
                            <div class="input-field">
                                <input id="{{$i}}_culture" name="{{$i}}_culture" type="text" class="validate autocomplete">
                                <label for="{{$i}}_culture">Kultur</label>
                            </div>
                            <div class="input-field">
                                <textarea id="{{$i}}_comment" name="{{$i}}_comment" class="materialize-textarea validate"></textarea>
                                <label for="{{$i}}_comment">Beschreibung</label>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="row">
                <div class="col s6">
                    <a class="waves-effect waves-light btn" onclick="goBack()"><i class="material-icons left rotate-180">send</i>zurück</a>
                </div>
                <div class="col s6">
                    <a id="save_button" class="waves-effect waves-light btn" onclick="save()"><i class="material-icons right">send</i>Speichern</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/js/plan.js?v=1.0"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
@endsection