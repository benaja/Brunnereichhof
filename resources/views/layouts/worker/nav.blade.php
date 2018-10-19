<nav>
    <div class="nav-wrapper">
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
    </div>
</nav>