@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" href="/css/overview.css">
    <link rel="stylesheet" href="/js/tiny-date-picker-master/tiny-date-picker.css">
    <link rel="stylesheet" href="/css/sweetalert2.min.css">
@endsection

@section('content')
<div class="row">
    <div class="col s12 xl8 offset-xl2">
        <ul class="collapsible">
            <li>
                <div class="collapsible-header">
                    <i class="material-icons">perm_identity</i>Hofmitarbeiter
                </div>
                <div class="collapsible-body">
                    <div class="row">
                        <div class="row margin-0">
                            <div class="col s12">
                                <h5>Stundenangaben</h5>
                            </div>
                        </div>
                        <div class="row margin-0">
                            <div class="col s12 input-field">
                                <i class="material-icons prefix">search</i>
                                <input type="text" id="search_worker" class="autocomplete-worker">
                                <label for="search_worker">Mitarbeiter</label>
                            </div>
                        </div>
                        <div class="row margin-0">
                            <div class="col s12 input-field">
                                <i class="material-icons prefix">date_range</i>
                                <input type="text" id="pick_month_worker">
                                <label for="pick_month_worker">Monat</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn disabled" id="button_month_worker"><i class="material-icons left">picture_as_pdf</i>PDF erstellen</a>
                                </p> 
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div class="collapsible-header">
                    <i class="material-icons">account_circle</i>Mitarbeiter
                </div>
                <div class="collapsible-body">
                    <div class="row">
                        <div class="row margin-0">
                            <div class="col s12">
                                <h5>Monatsrapport</h5>
                            </div>
                        </div>
                        <div class="row margin-0">
                            <div class="col s12 input-field">
                                <i class="material-icons prefix">date_range</i>
                                <input type="text" id="pick_month_employee">
                                <label for="pick_month_employee">Monat</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn disabled" id="button_month_employee"><i class="material-icons left">picture_as_pdf</i>PDF erstellen</a>
                                </p> 
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row margin-0">
                            <div class="col s12">
                                <h5>Jahresrapport</h5>
                            </div>
                        </div>
                        <div class="row margin-0">
                            <div class="col s12 input-field">
                                <i class="material-icons prefix">search</i>
                                <input type="text" id="search_employee" class="autocomplete-employee">
                                <label for="search_employee">Mitarbeiter</label>
                            </div>
                        </div>
                        <div class="row margin-0">
                            <div class="col s12 input-field">
                                <i class="material-icons prefix">date_range</i>
                                <input type="text" id="pick_year_employee">
                                <label for="pick_year_employee">Jahr</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn disabled" id="button_year_employee"><i class="material-icons left">picture_as_pdf</i>PDF erstellen</a>
                                </p> 
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row margin-0">
                            <div class="col s12 center-align">
                                <h5>Mitarbeiterliste</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn" href="/overview/employees"><i class="material-icons left">picture_as_pdf</i>PDF erstellen</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <div class="collapsible-header">
                    <i class="material-icons">supervisor_account</i>Kunde
                </div>
                <div class="collapsible-body">
                    <div class="row">
                        <div class="row margin-0">
                            <div class="col s12">
                                <h5>Jahresrapport</h5>
                            </div>
                        </div>
                        <div class="row margin-0">
                            <div class="col s12 input-field">
                                <i class="material-icons prefix">search</i>
                                <input type="text" id="search_customer" class="autocomplete">
                                <label for="search_customer">Kunde</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12 input-field">
                                <i class="material-icons prefix">date_range</i>
                                <input type="text" class="month-picker" id="pick_year_customer">
                                <label for="pick_year_customer">Jahr</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <p class="center-align">
                                    <a class="waves-effect waves-light btn disabled" id="button_customer_year"><i class="material-icons left">picture_as_pdf</i>PDF erstellen</a>
                                </p> 
                            </div>
                        </div>
                    </div>
                </div>  
            </li>
        </ul>
    </div>
</div>
@endsection

@section('scripts')
    <script src="/js/sweetalert2.min.js"></script>
    <script src="/js/tiny-date-picker-master/dist/tiny-date-picker.js"></script>
    <script src="/js/overview.js"></script>
@endsection