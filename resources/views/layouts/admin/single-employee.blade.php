<li>
    <div class="collapsible-header">
        @if($employee->profileimage != null)
            <div class="account-image" style="background-image: url(/profileimages/small-{{$employee->profileimage}})">
            </div>
        @else
            <i class="material-icons">account_circle</i>
        @endif
        {{$employee->firstname}} {{$employee->lastname}}
        <span class="material-icons badge">arrow_drop_down</span>
        <a href="/employee/{{$employee->id}}" class="waves-effect waves-light btn-small badge details">Details</a>
    </div>
    <div class="collapsible-body">
        <div class="row details">
            <div class="col s12 xl5">
                <h5>Rufname und Geschlecht</h5>
                <p>{{$employee->callname}}</p>
                <p>{{$employee->sex}}</p>
            </div>
            <div class="col s12 xl3">
                <h5>Sprachkenntnisse</h5>
                @if($employee->german_knowledge == 1)
                    <p>Deutsch</p>
                @endif
                @if($employee->english_knowledge == 1)
                    <p>Englisch</p>
                @endif
            </div>
            <div class="col s12 xl4">
                <h5>Kommentar</h5>
                <p>{{$employee->comment}}</p>
            </div>
        </div>
    </div>
</li>