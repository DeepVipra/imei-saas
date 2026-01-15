<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IMEI SaaS') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<div id="app">

    {{-- ================= NAVBAR ================= --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">

            <a class="navbar-brand" href="{{ route('dashboard') }}">
                IMEI SaaS
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">

                {{-- LEFT MENU --}}
                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                Dashboard
                            </a>
                        </li>

                        @if(auth()->user()->role === 'admin')

                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/dealers') }}">
                                    Create Dealer
                                </a>
                            </li>

                            {{-- ✅ PHASE-3 INVENTORY --}}
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/imports/inventory') }}">
                                    Upload Inventory File
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/imports/allocation') }}">
                                    Upload Sales to Dealer
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/imports/activation') }}">
                                    Upload Activation File
                                </a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('reports.devices') }}">
                                Reports
                            </a>
                        </li>
                    @endauth
                </ul>

                {{-- RIGHT MENU --}}
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                Login
                            </a>
                        </li>
                    @else
                        <li class="nav-item text-white mt-2 me-3">
                            {{ auth()->user()->email }}
                        </li>

                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-light">
                                    Logout
                                </button>
                            </form>
                        </li>
                    @endguest
                </ul>

            </div>
        </div>
    </nav>

    {{-- ================= CONTENT ================= --}}
    <main class="py-4">
        @yield('content')
    </main>

</div>

{{-- Scripts --}}
@stack('scripts')

</body>
</html>
