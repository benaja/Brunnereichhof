<tr id="{{$hourrecord->id}}" class="week_table">
    <td>{{$hourrecord->week}}</td>
    <td>
        <p id="hours" data-saveid="{{$hourrecord->id}}" class="info_content w100">{{$hourrecord->hours}}</p>
    </td>
    <td class="input-field">
        <p id="culture" data-saveid="{{$hourrecord->id}}" class="info_content w100">{{$hourrecord->culture->name}}</p>
    </td>
    <td>
        <p id="comment" data-saveid="{{$hourrecord->id}}" class="info_content w100">{{$hourrecord->comment}}</p>
    </td>
    <td>
        <a href="/plan/delete/{{$hourrecord->id}}" class="btn red"><i class="material-icons text-red">delete</i></a>
    </td>
</tr>