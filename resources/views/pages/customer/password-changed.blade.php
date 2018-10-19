@extends("layouts." . $authorization)

@section('styles')
    <link rel="stylesheet" href="/css/animate.css">
    <style>
        .success-image{
            width: 250px;
            margin-right: auto;
            margin-left: auto;
            display: block;
        }

        .to-frontpage{
            font-size: 20px;
            margin-top: 100px;
        }

        h3{
            margin-top: 100px;
            margin-bottom: 50px;
        }

        @media only screen and (max-width:600px){
            h3{
                margin-top: 50px;
            }

            .success-image{
                width: 200px;
            }

            .to-frontpage{
                margin-top: 70px;
            }
        }
    </style>
@endsection

@section('content')

<h3 class="center-align">Passwort wurde erfolgreich geändert!</h3>
<img src="/images/checked.png" class="success-image animated bounceIn">

<p class="to-frontpage center-align">Zurück zu <a href="/">Startseite</a>.</p>
@endsection