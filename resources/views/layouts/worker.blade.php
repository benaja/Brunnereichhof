<!DOCTYPE html>
<html lang="de">
    <head>
        @include('layouts.head')
        @yield('styles')
    </head>
    <body>
        @include('layouts.worker.nav')
        <div class="container">
            @yield('content')
            <script src="/js/script.js?v=1.1"></script>
            @yield('scripts')
        </div>
    </body>
</html>