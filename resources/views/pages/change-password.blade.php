@extends('layouts.'.$authorization)

@section('content')
    <h2 class="center-align">Passwort ändern</h2>
    <div class="row passwort-change-form">
        <form class="col s12 form" action="/password/change"  method="POST">
            {{ csrf_field() }}
            <div class="row password_row">
                <div class="input-field col s12">
                    <input id="password_old" type="password" class="input validate {{$errors->has('password_old') ? 'invalid' : ''}}" name="password_old" required value="{{ old('password_old')}}">
                    <label for="password_old">Altes Passwort</label>
                    @if($errors->first('password_old') != "")
                        <p class="error">{{$errors->first('password_old')}}</p>
                    @endif
                </div>
            </div>
            <div class="row password_row">
                <div class="input-field col s12">
                    <input id="password" type="password" class="input validate {{$errors->has('password') ? 'invalid' : ''}}" name="password" required value="{{ old('password')}}">
                    <label for="password">Neus Passwort</label>
                    @if($errors->first('password') != "")
                        @if($errors->first('password') == "Das password Format ist ungültig.")
                            <p class="error">Passwort muss folgenden Kriterien entsprechen: mindestens 8 Zeichen, Gross- und Kleinbuchstaben, mindestens eine Zahl.</p>
                        @else
                            <p class="error">{{$errors->first('password')}}</p>
                        @endif
                    @endif
                </div>
            </div>
            <div class="row password_row">
                <div class="input-field col s12">
                    <input id="password_confirmation" type="password" class="input validate {{$errors->has('password_confirmation') ? 'invalid' : ''}}" name="password_confirmation" required value="{{ old('password_confirmation')}}">
                    <label for="password_confirmation">Passwort wiederholen</label>
                    @if($errors->first('password_confirmation') != "")
                        <p class="error">{{$errors->first('password_confirmation')}}</p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col s12">
                    <input class="btn col s4 offset-s4" type="submit" value="Speichern">
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="/js/customer.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        @if($initialChange)
            swal('Sie haben sich das erste mal Angemeldet. Bitte ändern sie ihr Passwort.');
        @endif
    </script>
@endsection