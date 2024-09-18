<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')Mensa</title>
    @vite('resources/sass/app.scss')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <header>
        <nav class="navbar nav-pills navbar-expand-sm navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('index') }}">Mensa</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mynavbar">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a href="#" class="nav-link">Dummy</a>
                        </li>
                    </ul>
                    <hr class="my-3">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="{{ route('register') }}" @class([
                                'nav-link',
                                'align-items-center',
                                'active' => Route::current()->getName() == 'register',
                            ])>Register</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main style="height: 100%">
        @yield('main')
    </main>
    @vite('resources/js/app.js')
    @stack('after footer')
</body>

</html>
