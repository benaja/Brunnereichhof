@extends("layouts.mail")

@section('content')
<tr>
    <td style="height: 180px">
        <img src="http://rapport-brunnereichhof.ch/images/logo.png">
        <h1 style="font-family: sans-serif; text-align: left;">Guten Tag {{$data['firstname']}} {{$data['lastname']}}</h1>
    </td>
</tr>
<tr>
    <td>
        <p>Sie erhalten diese Email, weil sie auf der Webseite ihr Passwort zurücksetzen wollten.</p>
        <p>Bitte besuchen Sie den unten stehenden Link. Wenn Sie diese Änderung nicht angefordert haben, ignorieren Sie diese E-Mail. </p>

        <p class="button">
            <a href="{{config('app.frontend_url')}}set-password?token={{$data['token']}}&userId={{$data['userId']}}">Passwort zurücksetzen</a>
        </p>
    </td>
</tr>

@endsection