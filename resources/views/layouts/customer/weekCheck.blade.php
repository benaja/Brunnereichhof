
<p class=" check_week">
    <label>
        <?php 
            $disabled = false;
            foreach($hourrecords as $hourrecord){
                if($hourrecord->week == $week){
                    $disabled = true;
                    break;
                }
            }
        ?>
        @if($disabled)
            <a href="/plan/edit">
        @endif
        <input type="checkbox" class="filled-in" id="{{$week}}_check" {{$disabled ? 'disabled' : ''}} value="{{$week}}"/>
        <span>{{$week}}</span>
        @if($disabled)
            </a>
        @endif
    </label>
</p>
