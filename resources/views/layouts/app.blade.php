<!doctype html>
<html class="h-100" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Font Awesome links -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="d-flex flex-column h-100">
    @auth
    <header>
        <nav class="navbar navbar-lg navbar-expand-lg navbar-dark shadow culoare1">
            <div class="container">
                <a class="navbar-brand me-5" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        @can('access-admin-area')
                        <li class="nav-item me-2">
                            <a class="nav-link rounded-3 {{ request()->routeIs('notificari.index') ? 'shadow shadow-light' : 'text-white' }}" href="{{ route('notificari.index') }}" title="Notificari">
                                <i class="fa-solid fa-envelope"></i>
                            </a>
                        </li>
                        <li class="nav-item me-2 dropdown">
                            <a class="nav-link dropdown-toggle rounded-3 {{ request()->routeIs('apps.*') || request()->routeIs('validsoftware-blog.*') ? 'shadow shadow-light' : 'text-white' }}" href="#" id="navbarAppsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-bars me-1"></i>Apps
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarAppsDropdown">
                                <li><a class="dropdown-item" href="/apps/aplicatii"><i class="fa-solid fa-bars me-1"></i>Aplicatii</a></li>
                                <li><a class="dropdown-item" href="/apps/features"><i class="fa-solid fa-layer-group me-1"></i>Features</a></li>
                                <li><a class="dropdown-item" href="/apps/actualizari"><i class="fa-solid fa-pen-to-square me-1"></i>Actualizari</a></li>
                                <li><a class="dropdown-item" href="/apps/pontaje?searchData={{ \Carbon\Carbon::now()->toDateString(); }}"><i class="fa-solid fa-clock me-1"></i>Pontaje</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/apps/pontaje/statistica"><i class="fa-solid fa-chart-simple me-1"></i>Statistica</a></li>
                                <li><a class="dropdown-item" href="/apps/pontaje/statistica-grafice"><i class="fa-solid fa-chart-column me-1"></i>Statistica - grafice</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/apps/facturi"><i class="fa-solid fa-file-invoice me-1"></i>Facturi</a></li>
                                <li><a class="dropdown-item" href="{{ route('validsoftware-blog.index') }}"><i class="fa-solid fa-newspaper me-1"></i>Articole ValidSoftware</a></li>
                            </ul>
                        </li>
                        @endcan
                        @canany(['access-admin-area', 'access-apartments'])
                        <li class="nav-item me-2 dropdown">
                            <a class="nav-link dropdown-toggle rounded-3 {{ request()->routeIs('apartamente.*') || request()->routeIs('refrains.*') || request()->routeIs('achievements.*') || request()->routeIs('wardrobe.*') ? 'shadow shadow-light' : 'text-white' }}" href="#" id="navbarPersonalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-user me-1"></i>Personal
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarPersonalDropdown">
                                @can('access-apartments')
                                <li><a class="dropdown-item" href="{{ route('apartamente.index') }}"><i class="fa-solid fa-building me-1"></i>Apartamente</a></li>
                                <li><a class="dropdown-item" href="{{ route('apartamente.calendar') }}"><i class="fa-solid fa-calendar-days me-1"></i>Vizionari apartamente</a></li>
                                <li><a class="dropdown-item" href="{{ route('apartamente.tracking.index') }}"><i class="fa-solid fa-chart-line me-1"></i>Monitorizare anunturi</a></li>
                                @endcan
                                @can('access-admin-area')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('refrains.index') }}"><i class="fa-solid fa-ban me-1"></i>Refrains</a></li>
                                <li><a class="dropdown-item" href="{{ route('achievements.index') }}"><i class="fa-solid fa-trophy me-1"></i>Achievements</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('wardrobe.meetings.index') }}"><i class="fa-solid fa-calendar-days me-1"></i>Wardrobe meetings</a></li>
                                <li><a class="dropdown-item" href="{{ route('wardrobe.people.index') }}"><i class="fa-solid fa-users me-1"></i>Wardrobe contacts</a></li>
                                <li><a class="dropdown-item" href="{{ route('wardrobe.clothing-items.index') }}"><i class="fa-solid fa-shirt me-1"></i>Wardrobe clothing</a></li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @can('access-admin-area')
                        <li class="nav-item me-2 dropdown">
                            <a class="nav-link dropdown-toggle rounded-3 {{ request()->routeIs('system.*') ? 'shadow shadow-light' : 'text-white' }}" href="#" id="navbarTechDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-screwdriver-wrench me-1"></i>Tech
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarTechDropdown">
                                <li><a class="dropdown-item" href="{{ route('system.database') }}"><i class="fa-solid fa-database me-1"></i>Database & migrations</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('system.users.index') }}"><i class="fa-solid fa-users-gear me-1"></i>Users</a></li>
                            </ul>
                        </li>
                        @endcan
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link active dropdown-toggle" href="#" id="navbarAuthentication" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>

                                <ul class="dropdown-menu" aria-labelledby="navbarAuthentication">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    @else
    @endauth

    <main class="flex-shrink-0 py-4">
        @yield('content')
    </main>

    <footer class="mt-auto py-2 text-center text-white culoare1">
        <div class="">
            <p class="mb-1">
                © {{ date('Y') }} {{ config('app.name', 'Laravel') }}
            </p>
            <span class="text-white">
                <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank">
                    Aplicatie web</a>
                dezvoltata de
                <a href="https://validsoftware.ro/" class="text-white" target="_blank">
                    validsoftware.ro
                </a>
            </span>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
