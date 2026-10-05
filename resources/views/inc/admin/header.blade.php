<header class="bmd-layout-header ">
    <div class="navbar navbar-light bg-faded animate__animated animate__fadeInDown">
        <button class="navbar-toggler animate__animated animate__wobble animate__delay-2s" type="button"
            data-toggle="drawer" data-target="#dw-s1">
            <span class="navbar-toggler-icon"></span>
        </button>
        <ul class="nav navbar-nav">
            <li class="nav-item">
                <div class="dropdown">
                    <button class="btn dropdown-toggle m-0" type="button" id="dropdownMenu2" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-globe fa-lg mx-2"></i>
                    </button>
                    <div aria-labelledby="dropdownMenu2"
                        class="dropdown-menu dropdown-menu-left dropdown-menu dropdown-menu-left-lg"
                        style="left: 0px; right: inherit;">
                        @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a rel="alternate" hreflang="{{ $localeCode }}"
                            href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                            class="dropdown-item" style="min-width: auto">
                            {{ $properties['native'] }}
                        </a>

                        @if (!$loop->last)
                        <div class="dropdown-divider"></div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.search') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-search-icon lucide-search">
                        <path d="m21 21-4.34-4.34" />
                        <circle cx="11" cy="11" r="8" />
                    </svg>
                </a>
            </li>

            <li class="nav-item"> <img
                    src="{{ auth()->user()->photo ? auth()->user()->photo : asset('/files/admin/img/user-profile.jpg') }}"
                    alt="{{ auth()->user()->name }}" class="rounded-circle screen-user-profile"></li>
            <li class="nav-item">
                <div class="dropdown">
                    @auth
                    <button class="btn  dropdown-toggle m-0" type="button" id="dropdownMenu4" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        {{ auth()->user()->name }}
                    </button>
                    @endauth

                    <div aria-labelledby="dropdownMenu4"
                        class="dropdown-menu dropdown-menu-right dropdown-menu dropdown-menu-right"
                        aria-labelledby="dropdownMenu2">
                        <button class="dropdown-item" type="button"><i
                                class="far fa-user fa-sm c-main {{ App::getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}"></i>{{ __('messages.Profile') }}</button>
                        <a href="{{ route('index') }}" class="dropdown-item">{{ __("messages.Home") }}</a>
                        {{-- <button onclick="dark()" class="dropdown-item" type="button"><i
                                class="fas fa-moon fa-sm c-main {{ App::getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}"></i>Dark
                        Mode</button> --}}

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" type="submit"><i
                                    class="fas fa-sign-out-alt c-main fa-sm {{ App::getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}"></i>{{ __('messages.Sign Out') }}</button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</header>