<div class="calendar-day">
    @if(!$isMobile)
    <p class="date-of-day">{{$dayNamesGerman[$timerecord->date()->format('w')]}} {{$timerecord->date()->format('d.m.Y')}}</p>
    @endif
    <div class="calendar-container">
        @for($i = 0; $i<=24; $i++) 
        <div class="row calendar-row">
            <div class="col s2">
                @if($isMobile || $timerecord->date()->format('w') == 1)
                    <p class="time">{{$i}}:00</p>
                @else
                    <p class="center-time"> l</p>
                @endif
            </div>
            <div class="col s10">

            </div>
        </div>
        @endfor
        @foreach($timerecord->hours as $hour)
        <div class="time-element {{$hour->worktype->name != 'productiveHours' ? 'amber' : ''}}" 
            style="top:{{$hour->getMarginTop()}}px; height:{{$hour->getHeight()}}px;" 
            onclick="toggleSettingsButtons(this)" 
            >
            <div class="settings-buttons {{$hour->worktype->name != 'productiveHours' ? 'amber' : ''}}">
                <div class="edit-button " onclick="editHour({{$hour->id}}, '{{$hour->from()->format('H:i')}}', 
                    '{{$hour->to()->format('H:i')}}', '{{$hour->worktype->name}}', '{{$hour->comment}}', {{$timerecord->lunch}})">
                    <i class="material-icons medium white-text">edit</i>
                </div>
                <div class="delete-button" onclick="deleteHour({{$hour->id}})">
                    <i class="material-icons medium white-text">delete</i>
                </div>
            </div>
            <div class="time-element-header">
            <p class="white-text bold">{{$hour->from()->format('H:i')}} - {{$hour->to()->format('H:i')}} / {{$hour->duration()}}h</p>
                <p class="white-text">{{$hour->worktype->name_de}}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
<div class="add-button-container">
    <a class="btn-floating btn-large waves-effect waves-light add-button" onclick="openTimePopup(`{{$timerecord->date()->format('d.m.Y')}}`)">
        <i class="material-icons">add</i>
    </a>
</div>