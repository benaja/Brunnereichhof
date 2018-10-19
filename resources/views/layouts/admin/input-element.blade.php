<input id="{{$element}}" type="{{isset($type) ? $type : 'text'}}" class="input validate {{$errors->has($element) ? 'invalid' : ''}}" name="{{$element}}" {{isset($required) ? 'required' : ''}} {{isset($properties) ? $properties : ''}} value="{{old($element)}}">
<label for="{{$element}}">{{$name}}</label>
<strong>{{$errors->first($element)}}</strong>