<nav>
    <div class="nav-wrapper">
        <div id="menu-button">
            <i class="material-icons grey-text text-darken-2 Large" id="menu-icon">menu</i>
        </div>
        <div class="login dropdown-trigger" data-target="dropdown1" >
            <img src="/images/profile.png">
            <p>{{Auth::user()->vorname}}</p>
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
        <a href="/home" class="brand-logo">
            <img src="/images/logo.png">
        </a>
        <div class="nav">
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li>
                    <a href="#!" class="user-administation-dropdown" data-target="user-administation-dropdown">Benutzerverwaltung</a>
                </li>
                <li>
                    <a href="/rapport/show">Wochenrapport</a>
                </li>
                <li>
                    <a href="/overview" >Auswertung</a>
                </li>
                <li>
                    <a href="/evaluation">Stundenangaben</a>
                </li>
            </ul>
        </div>
    </div>
    <ul id="user-administation-dropdown" class="dropdown-content">
        <li><a href="/customer">Kunden</a></li>
        <li class="divider"></li>
        <li><a href="/employee">Mitarbeiter</a></li>
        <li class="divider"></li>
        <li><a href="/worker">Hofmitarbeiter</a></li>
    </ul>
    <ul class="sidenav" id="mobile-sidenav">
        <li>
            <ul class="collapsible collapsible-accordion">
                <li>
                    <a class="collapsible-header">Benutzerverwaltung</a>
                    <div class="collapsible-body">
                        <ul>
                            <li><a href="/customer">Kunden</a></li>
                            <li><a href="/employee">Mitarbeiter</a></li>
                            <li><a href="/worker">Hofmitarbeiter</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </li>
        <li><a href="/rapport/show">Wochenrapport</a></li>
        <li><a href="/overview">Auswertung</a></li>
        <li><a href="/evaluation">Stundenangaben</a></li>
    </ul>
</nav>