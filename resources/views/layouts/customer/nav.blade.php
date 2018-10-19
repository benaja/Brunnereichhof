<nav>
    <div class="nav-wrapper">
        <div id="menu-button">
            <i class="material-icons grey-text text-darken-2 Large" id="menu-icon">menu</i>
        </div>

        <div class="login dropdown-trigger" data-target="dropdown1" >
            <img src="/images/profile.png">
            <p>{{Auth::user()->firstname}}</p>
        </div>
        
        <ul id="dropdown1" class="dropdown_menu dropdown-content">
            <li>
                <a>
                    {{Auth::user()->firstname}} {{Auth::user()->lastname}}
                </a>
            </li>
            <li>
            <a href="/profile/edit">
                Passwort ändern
                </a>
            </li>
            <li> 
                <a class="logout_link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
            </li>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </ul>
        <a href="#" class="brand-logo">
            <img src="/images/logo.png">
        </a>
        <div class="nav-kunde">
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li>
                    <a href="/">Übersicht</a>
                </li>
                <li>
                    <a href="/plan">Arbeiten Planen</a>
                </li>
            </ul>
        </div>
    </div>
    <ul class="sidenav" id="mobile-sidenav">
        <li><a href="/">Übersicht</a></li>
        <li><a href="/plan">Arbeiten Planen</a></li>
        <li><a href="/plan/edit">Arbeiten bearbeiten</a></li>
    </ul>
</nav>