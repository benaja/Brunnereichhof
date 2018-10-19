@extends("layouts.admin")

@section('scripts')
    <script src="/js/chart.min.js"></script>
    <script src="/js/evaluation.js"></script>
    <link rel="stylesheet" href="/css/evaluation.css">
@endsection
@section('content')
    <h2 class="center-align">Übersicht der Woche {{$weeks[0]->week}}</h2>
    <div class="form">
        <div class="row">
            <table>
                <thead>
                    <tr>
                        <th>Kunde</th>
                        <th>Kultur</th>
                        <th>Anzahl Stunden</th>
                        <th>Bemerkung</th>
                    </tr>
                </thead>
        
                <tbody>
                    @foreach($weeks as $week)
                        <tr>
                            <td><a href="/customer/{{$week->customer->id}}">{{$week->customer->firstname}} {{$week->customer->lastname}}</a></td>
                            <td>{{$week->culture->name}}</td>
                            <td class="table-hours">{{$week->hours}}</td>
                            <td class="table-comment">{{$week->comment}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    


@endsection