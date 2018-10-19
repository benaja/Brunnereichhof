@extends('layouts.worker') 
@section('styles')
<link rel="stylesheet" href="/css/worker.css?v=1.0"> 
@endsection 
@section('content')
<div class="row date-picker-box">
    <div class="col s3 date-icon">
        <a href="/time?date={{$isMobile ? $currentDate->modify('-1 day')->format('d.m.Y') : $currentDate->modify('-7 days')->format('d.m.Y')}}" class="right">
            <i class="medium material-icons">keyboard_arrow_left</i>
        </a>
    </div>
    <div class="input-field col s6">
        <input placeholder="Datum" id="date" type="text" value="{{$isMobile ? $currentDate->modify('+1 day')->format('d.m.Y') : $currentDate->modify('+7 days')->format('d.m.Y')}}" class="datepicker">
    </div>
    <div class="col s3 date-icon">
        <a href="/time?date={{$isMobile ? $currentDate->modify('+1 day')->format('d.m.Y') : $currentDate->modify('+7 days')->format('d.m.Y')}}" class="left">
            <i class="medium material-icons">keyboard_arrow_right</i>
        </a>
    </div>
</div>
<div class="calendar">
    @yield('calendar')
</div>
<div class="footer fixed">
    <div class="row">
        <div class="col s6 footer-button">
            <a class="waves-effect waves-light btn-small today-button" href="/time">Heute</a>
        </div>
        <div class="col s6 total-hours valign-wrapper">
        <p class="totaltime right-align">{{$totalHours}}h</p>
        </div>
    </div>
</div>

<!-- Add Time Popup -->
<div id="add-time" class="time-popup slider animate closed">
    <form method="POST" action="/time?date={{old('date') == '' ? $currentDate->modify('-1 day')->format('d.m.Y') : old('date')}}">
        {{ csrf_field() }}
        <input id="old-date" name="date" value="{{ old('date') }}">
        <div class="time-input-fields">
            <div class="row work-type">
                <div class="input-field col s12">
                    <select name="workType">
                        <option value="productiveHours" selected>Produktivstunden</option>
                        <option value="holidays">Ferien</option>
                        <option value="sick">Krank</option>
                        <option value="accident">Unfall</option>
                    </select>
                    <label>Leistunsart</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <select onchange="changeInputType(this)">
                        <option value="1" selected>Von... - Bis...</option>
                        <option value="2">Ganztägig</option>
                    </select>
                    <label>Erfassungsart</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input required type="text" class="timepicker timepicker-from" value="{{ old('from') }}" placeholder="Startzeit auswählen" name="from">
                    <label>Zeit von</label>
                    @if($errors->has('from'))
                    <span class="invalid-feedback">
                        <strong>Die Startzeit muss vor der Endzeit sein.</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input required type="text" class="timepicker timepicker-to" value="{{ old('to') }}" placeholder="Endzeit auswählen" name="to">
                    <label>Zeit bis</label>
                    @if($errors->has('to'))
                    <span class="invalid-feedback">
                        <strong>{{$errors->first('to')}}</strong>
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="row magrin-0">
                <div class="col s12">
                    <p class="magrin-0 text-small">Unterbruch von / bis</p>
                </div>
            </div>
            <div class="row magrin-0">
                <div class="col s2">
                    <p>
                        <label>
                            <input type="checkbox" class="interrupt-toggle" name="interrupt" 
                            value="1" onchange="toggleInterrupt()" />
                            <span></span>
                        </label>
                    </p>
                </div>
                <div class="col s5">
                    <div class="input-field col s12 magrin-0 padding-0">
                        <input disabled type="text" class="timepicker interrupt" value="{{ old('interruptFrom')  }}" placeholder="Von" name="interruptFrom" value="12:00">
                        <label></label>
                    </div>
                </div>
                <div class="col s5">
                    <div class="input-field col s12 magrin-0 padding-0">
                        <input disabled type="text" class="timepicker interrupt"  value="{{ old('interruptTo') }}" placeholder="Bis" name="interruptTo" value="13:00">
                        <label></label>
                    </div>
                </div>
                
                @if($errors->has('interruptFrom') || $errors->has('interruptTo'))
                <span class="invalid-feedback">
                    <strong>Der Unterbruch muss zwischen der Anfang und Startzeit sein.</strong>
                </span>
                @endif
            </div>
            <div class="row margin-top-2">
                <div class="col s12">
                    <p>
                        <label>
                          <input type="checkbox" name="lunch" value="1" 
                          {{$isMealDefault == 1 ? "checked" : ""}}/>
                          <span>Mittagessen auf dem Eichhof</span>
                        </label>
                      </p>
                </div>
            </div>
            <div class="row comment">
                <div class="col s12 input-field">
                    <textarea id="comment1" name="comment" class="materialize-textarea" value="{{ old('comment') }}"></textarea>
                    <label for="comment1">Bemerkung</label>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="row">
                <div class="col s6 footer-button">
                    <button class="btn waves-effect waves-light red" name="action" type="reset" onclick="toggleTimePupup({{$errors->any() ? true : false}})">Abbrechen
                        <i class="material-icons left">cancel</i>
                    </button>

                </div>
                <div class="col s6 footer-button">
                    <div class="widht-100">
                        <button class="btn waves-effect waves-light save-time-button" type="submit" name="action">Speichern
                                <i class="material-icons right">send</i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Edit Time Popup -->
<div id="edit-time" class="time-popup slider animate closed">
    <form method="POST" action="{{ old('url') }}">
        {!! method_field('patch') !!}
        {{ csrf_field() }}
        <input type="hidden" id="patch_url" name="url" value="{{ old('url') }}">
        <div class="time-input-fields">
            <div class="row work-type">
                <div class="input-field col s12">
                    <select name="workType" id="workType">
                        <option value="productiveHours">Produktivstunden</option>
                        <option value="holidays">Ferien</option>
                        <option value="sick">Krank</option>
                        <option value="accident">Unfall</option>
                    </select>
                    <label>Leistunsart</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <select onchange="changeInputType(this)" id="inputType">
                        <option value="1" selected>Von... - Bis...</option>
                        <option value="2">Ganztägig</option>
                    </select>
                    <label>Erfassungsart</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input id="from" required type="text" class="timepicker" value="{{ old('from') }}" placeholder="Startzeit auswählen" name="from">
                    <label>Zeit von</label>
                    @if($errors->has('from'))
                    <span class="invalid-feedback">
                        <strong>Die Startzeit muss vor der Endzeit sein!</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input id="to" required type="text" class="timepicker" value="{{ old('to') }}" placeholder="Endzeit auswählen" name="to">
                    <label>Zeit bis</label>
                    @if($errors->has('to'))
                    <span class="invalid-feedback">
                        <strong>{{$errors->first('to')}}</strong>
                    </span>
                    @endif
                </div>
            </div>
            <div class="row margin-top-2">
                <div class="col s12">
                    <p>
                        <label>
                            <input id="lunch" type="checkbox" name="lunch" value="1" />
                            <span>Mittagessen auf dem Eichhof</span>
                        </label>
                        </p>
                </div>
            </div>
            <div class="row comment">
                <div class="col s12 input-field">
                    <textarea id="comment" name="comment" class="materialize-textarea" value="{{ old('comment') }}"></textarea>
                    <label for="comment">Bemerkung</label>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="row">
                <div class="col s6 footer-button">
                    <button class="btn waves-effect waves-light red" name="action" type="reset" onclick="toggleEditTimePupup({{$errors->any() ? true : false}})">Abbrechen
                        <i class="material-icons left">cancel</i>
                    </button>

                </div>
                <div class="col s6 footer-button">
                    <div class="widht-100">
                        <button class="btn waves-effect waves-light save-time-button" type="submit">Speichern
                                <i class="material-icons right">send</i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@section('scripts')
<script src="/js/worker.js?v=1.0.2"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    var hoursPerDay = {{$settings->hoursPerDay}};
    @if($errors->any())
        @if($hasUpdated)
            showUpdateError();
        @else
            showError();
        @endif
    @endif
</script>
@endsection
