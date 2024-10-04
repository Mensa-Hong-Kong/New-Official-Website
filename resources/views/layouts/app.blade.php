<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')Mensa</title>
    @vite('resources/css/app.scss')
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
                        @auth
                            <li class="nav-item">
                                <a href="{{ route('profile.show') }}" @class([
                                    'nav-link',
                                    'align-items-center',
                                    'active' => Route::current()->getName() == 'profile.show',
                                ])>Profile</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('logout') }}" class='nav-link align-items-center'>Logout</a>
                            </li>
                        @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" @class([
                                'nav-link',
                                'align-items-center',
                                'active' => Route::current()->getName() == 'login',
                            ])>Login</a>
                        </li>
                            <li class="nav-item">
                                <a href="{{ route('register') }}" @class([
                                    'nav-link',
                                    'align-items-center',
                                    'active' => Route::current()->getName() == 'register',
                                ])>Register</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main style="height: 100%">
        @yield('main')
    </main>
    <div class="modal alert" id="alert" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="alertMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>
    @vite('resources/js/app.js')
    <script>
        function bootstrapAlert(message) {
            document.getElementById('alertMessage').innerText = message;
            new bootstrap.Modal(document.getElementById('alert')).show();
        }
    </script>
    @error('message')
        <script>
            document.addEventListener("DOMContentLoaded", (event) => {
                bootstrapAlert('{{ $message }}');
            });
        </script>
    @enderror
    @session('success')
        <script>
            document.addEventListener("DOMContentLoaded", (event) => {
                bootstrapAlert('{{ $value }}');
            });
        </script>
    @endsession
    @stack('after footer')
</body>

</html>
