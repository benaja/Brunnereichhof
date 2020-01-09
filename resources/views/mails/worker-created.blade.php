@extends("layouts.mail")
    
@section('content')
    <tr> 
        <td style="height: 180px">
            <img src="http://rapport-brunnereichhof.ch/images/logo.png">
            <h1 style="font-family: sans-serif; text-align: left;">Herrzlich Willkommen beim Brunnereichhof</h1>
        </td>
    </tr>
    <tr>
        <td>
            <p>Neu können sie ihre Arbeitszeit online Erfassen.</p>
        </td>
    </tr>
    <tr style="height: 30px;">
        <td>
        </td>
    </tr>
    <tr>
        <td>
            <h2>Ihr Login:</h2>
            <p>E-Mail: {{$data['mail']}}</br>
                Passwort: {{$data['password']}}</p>
        </td>
    </tr>
    <tr style="height: 30px;">
        <td>
        </td>
    </tr>
    <tr>
        <td>
            <p>Anmelden können Sie sich auf <a href="https://rapport-brunnereichhof.ch">dieser Website</a>.</p>
            <p>Bei Fragen können sie gerne einfach eine E-Mail an <a href="mailto:info@brunnereichhof.ch">info@brunnereichhof.ch</a> schreiben oder sich direkt an Stefan Brunner wenden.</p>
        </td>
    </tr>

@endsection

