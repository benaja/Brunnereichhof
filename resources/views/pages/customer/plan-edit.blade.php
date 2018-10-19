@extends("layouts.customer")

@section('style')
    <link rel="stylesheet" href="/css/plan.css?v=1.0">
@endsection

@section('content')
    <h3 class="center-align">Angaben</h3>
    <div class="row">
        <div class="form col s10 offset-s1 edit_form">
            <div class="row">
                <h6 class="center-align col s12">Du kannst deine Angaben jederzeit verändern, indem du einfach auf das entsprechende Feld doppelklickst.</h6>
            </div>
            <div class="row">
                <div class="col s12">
                    <table>
                        <thead>
                            <tr>
                                <th class="week">KW</th>
                                <th class="hours">Stunden</th>
                                <th class="culture">Kultur</th>
                                <th class="bemerkung">Bemerkung</th>
                                <th class="löschen"></th>
                            </tr>
                        </thead>
                
                        <tbody>
                            @foreach($hourrecords as $hourrecord)
                                @include("layouts.customer.weekEdit", ['week' => $hourrecord->week])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a class="waves-effect waves-light btn" href="/plan"><i class="material-icons left ">add</i>Neue Woche erfassen</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        let propertyEditor = new PropertyEditor('plan', {{$customerId}});
    </script>
@endsection