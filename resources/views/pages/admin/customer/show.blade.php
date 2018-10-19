@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/customer.css?v=1.1">
@endsection

@section('content')
    <script>
        var customerId = {{$customer->id}};
    </script>
    <div class="row single-customer">
        <form class="col s12 l10 offset-l1">
            <div class="row">
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung">Vorname</p> 
                    <p id="firstname" class="col s8 info_content {{ $customer->firstname == "" ? 'empty' : '' }}">{{$customer->firstname}}</p>
                </div>
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung">Nachname</p>
                    <p id="lastname" class="col s8 info_content {{ $customer->lastname == "" ? 'empty' : '' }}">{{$customer->lastname}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung">Strasse + Nr</p>
                    <p id="street" class="col s8 m10 info_content {{ $customer->street == "" ? 'empty' : '' }}">{{$customer->street}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung">Ortschaft</p> 
                    <p id="place" class="col s8 info_content {{ $customer->place == "" ? 'empty' : '' }}">{{$customer->place}}</p>
                </div>
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung">PLZ</p> 
                    <p id="plz" class="col s8 info_content number {{ $customer->plz == "" ? 'empty' : '' }}">{{$customer->plz}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung">Zusatz</p>
                    <p id="addition" class="col s8 m10 info_content {{ $customer->addition == "" ? 'empty' : '' }}">{{$customer->addition}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung">Mobile</p> 
                    <p id="mobile" class="col s8 info_content {{ $customer->mobile == "" ? 'empty' : '' }}">{{$customer->mobile}}</p>
                </div>
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung">Festnetz</p> 
                    <p id="phone" class="col s8 info_content {{ $customer->phone == "" ? 'empty' : '' }}">{{$customer->phone}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung">E-Mail</p>
                    <p id="email" class="col s8 m10 info_content {{ $customer->user->email == "" ? 'empty' : '' }}">{{$customer->user->email}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung">Kundennummer</p>
                    <p id="customer_number" class="col s8 m10 info_content number{{ $customer->customer_number == "" ? 'empty' : '' }}">{{$customer->customer_number}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="input-field col s12 m6 checkbox">
                    <label class="col s12">
                        <input type="checkbox" class="filled-in" onchange="needsPaymentOrder(this)" name="needs_payment_order"  value="1" {{$customer->needs_payment_order == 1 ? 'checked': ''}}/>
                        <span id="needs_payment_order">Benötigt Einzahlungsschein</span>
                    </label>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="input-field col s12 m6 checkbox">
                    <label class="col s12">
                        <input type="checkbox" id="hasCatering" class="filled-in"  name="hasCatering" value="1" {{$customer->hasCatering == 1 ? 'checked': ''}}/>
                        <span id="verpflegung_edit">Verpflegung</span>
                    </label>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row verpflegung">
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung twolines">Ausstattung der Küche</p> 
                    <p id="kitchen_infrastructure" class="col s8 info_content {{ $customer->kitchen_infrastructure == "" ? 'empty' : '' }}">{{$customer->kitchen_infrastructure}}</p>
                </div>
                <div class="col s12 m6">
                    <p class="col s4 bezeichnung twolines">Max Anzhal Verpflegung</p> 
                    <p id="max_catering" class="col s8 info_content number {{ $customer->max_catering == "" ? 'empty' : '' }}">{{$customer->max_catering}}</p>
                </div>
            </div>
            <div class="divider verpflegung"></div>
            <div class="row verpflegung">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung twolines">Bemerkung zur Verpflegung</p> 
                    <p id="comment_catering" class="col s8 m10 info_content {{ $customer->comment_catering == "" ? 'empty' : '' }}">{{$customer->comment_catering}}</p>
                </div>
            </div>
            <div class="divider verpflegung"></div>
            <div class="row">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung">Fahrerinfo</p>
                    <p id="driver_info" class="col s8 m10 info_content {{ $customer->driver_info == "" ? 'empty' : '' }}">{{$customer->driver_info}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung" id="maps_link">Google-Maps link</p>
                    <p id="maps" class="col s8 m10 info_content {{ $customer->maps == "" ? 'empty' : '' }}" >{{$customer->maps}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12">
                    <p class="col s4 m2 bezeichnung" id="allgemaine_bemerkungen">Allgemeine Bemerkungen</p>
                    <p id="comment" class="col s8 m10 info_content {{ $customer->comment == "" ? 'empty' : '' }}">{{$customer->comment}}</p>
                </div>
            </div>
            <div class="divider"></div>
            <div class="row">
                <div class="col s12">
                    <div class="col s12">
                        <p class="bezeichnung projekt_titel">Projekte</p>
                        <div class="chips chips-autocomplete">
                        </div>     
                    </div>    
                </div>
            </div>
            <div class="row">
                <div class="col s12">
                    <div class="delete_button">
                        <a class="waves-effect waves-light btn red" onclick="deleteItem('/customer/',{{$customer->id}}, 'Der Kunde')"><i class="material-icons left">delete</i>Löschen</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('scripts')
    <script src="/js/customer.js?v=1.1"></script>
    <script src="/js/project.js?v=1.0"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        let projectSeletctor = new ProjectSelector();
        let propertyEditor = new PropertyEditor('customer', {{$customer->id}});
    </script>
@endsection