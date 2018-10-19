<li>
    <div class="collapsible-header">
        <i class="material-icons">account_circle</i>
        {{$customer->firstname}} {{$customer->lastname}}
        <span class="material-icons badge">arrow_drop_down</span>
        <a href="/customer/{{$customer->id}}" class="waves-effect waves-light btn-small badge details">Details</a>
    </div>
    <div class="collapsible-body row">
        <div class="col s12 m6 xl3">
            <h5>Adresse</h5>
            <p>{{$customer->street}}</p>
            <p>{{$customer->place}} {{$customer->plz}}</p>
        </div>
        <div class="col s12 m6 xl3">
            <h5>Telefon</h5>
            <p>Mobile: {{$customer->mobile}}</p>
            <p>Festnetz: {{$customer->phone}}</p>
        </div>
        <div class="col s12 m6 xl3">
            <h5>Username und E-Mail</h5>
            <p>{{$customer->username}}</p>
            <p>{{$customer->email}}</p>
        </div>
        <div class="col s12m6 xl3">
            <h5>Standard-Passwort</h5>
            @if($customer->secret == null)
                <p>Passwort wurde von Kunde gändert</p>
            @else
                <p>{{decrypt($customer->secret)}}</p> 
            @endif
        </div>
    </div>
</li>