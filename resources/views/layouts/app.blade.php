<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</head>


<style>

nav
{
    height: 70px;
}

.logo
{
    height: 68px;
    width: auto;
    overflow: hidden;
    margin: 0px;
}

a
{
    color: black;
}

.nav-icon
{
    font-size: 2.0rem;
    color: black !important;
}

#navbarDropdown::after {
    display: none;
}

.find-button {
    display: inline-block;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    color: white;
    background-color: #dcbf7d;
    font-weight: bold;
    transition: background-color 0.3s ease;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}



</style>


<body>
    <div id="app">
        @if (!Route::is('register') && !Route::is('login'))
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                        <a href="" class="find-button">Find a Property</a>

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

                            <li class="nav-item dropdown d-flex align-items-center">
                                <i class="fa-solid fa-circle-user nav-icon"></i>
                                <span class="ms-3">{{ Auth::user()->name }}</span>
                                <a class="ms-3"id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fa-solid fa-bars nav-icon"></i>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}

                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @endif

        <main class="pt-4">
            @yield('content')
        </main>

        <footer>
    <div class="row">
        <div class="col-auto">
            <h1>ROOMIFY</h1>
        </div>
        <div class="col-auto">
            <h5>COMPANY</h5>
            <p><a href="#">About Us</a></p>
            <p><a href="#">Contact Us</a></p>
        </div>
        <div class="col-auto">
            <h5>HELP CENTER</h5>
            <p><a href="#">Find a Property</a></p>
            <p><a href="#">How To Host?</a></p>
            <p><a href="#">FAQs</a></p>
            <p><a href="#">Rental Guides</a></p>
        </div>
        <div class="col-auto title">
            <h5>CONTACT INFO</h5>
            <p>Phone: 1234567890</p>
            <p>Email: roomify@email.com</p>
            <p>Location: 100 Smart Street, Tokyo, <br>JAPAN</p>
            <div class="app">
                    <a href="#"><i class="fa-brands fa-square-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-square-x-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col">
            <small class="left">(c) 2025 @roomify | All rights raserved</small>
        </div>
        <div class="col">
            <small class="right">Created with love by @roomify</small>
        </div>
    </div>
</footer>

    </div>
</body>
</html>
