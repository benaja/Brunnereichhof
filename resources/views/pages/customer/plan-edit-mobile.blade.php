@extends("layouts.customer")

@section('styles')
    <link rel="stylesheet" href="/css/plan.css?v=1.0">
@endsection

@section('content')
    <h3 class="center-align">Angaben</h3>
    <div class="row">
        <div class="edit_form">
            <div class="row">
                <h6 class="center-align col s10 offset-s1">Du kannst deine Angaben jederzeit verändern, indem du einfach auf das entsprechende Feld antippst.</h6>
            </div>
            <div class="row">
                <div class="col s12">
                    @foreach($hourrecords as $hourrecord)
                        <div class="row form" id="{{$hourrecord->id}}">
                            <div class="row week_title ">
                                <h6 class="col s10 offset-s1">KW {{$hourrecord->week}}</h6>
                            </div>
                            <div class="divider lenght90"></div>
                            <div class="row margin-0">
                                <p class="col s3 offset-s1">Stunden</p>
                                <p id="hours" data-saveid="{{$hourrecord->id}}" class="info_content col s8">{{$hourrecord->hours}}</p>
                            </div>
                            <div class="divider lenght90"></div>
                            <div class="row margin-0 input-field">
                                <p class="col s3 offset-s1">Kultur</p>
                                <p id="culture" data-saveid="{{$hourrecord->id}}" class="info_content col s8">{{$hourrecord->culture->name}}</p>
                            </div>
                            <div class="divider lenght90"></div>
                            <div class="row margin-0">
                                <p class="col s3 offset-s1">Bemerkung</p>
                                <p id="comment" data-saveid="{{$hourrecord->id}}" class="info_content col s8">{{$hourrecord->comment}}</p>
                            </div>
                            <div class="row">
                                <a href="/plan/delete/{{$hourrecord->id}}" class="btn red col s4 offset-s4"><i class="material-icons text-red">delete</i></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="row">
                <div class="col s12">
                    <a class="waves-effect waves-light btn col s12" href="/plan"><i class="material-icons left ">add</i>Neue Woche erfassen</a>
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