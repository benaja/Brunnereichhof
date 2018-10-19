@extends("layouts.admin")

@section('scripts')
    <script src="/js/chart.min.js"></script>
    <script src="/js/evaluation.js"></script>
    <link rel="stylesheet" href="/css/evaluation.css">
@endsection
@section('content')
    <h2 class="center-align">Zusammenfassung der Angaben der Kunden</h2>
    <canvas id="myChart" width="10" height="5"></canvas>
    <div class="collection">
        @while($hours = current($hoursPerWeek)) 
            @include('layouts.admin.single-week')
            <?php next($hoursPerWeek) ?>
        @endwhile
    </div>


@endsection