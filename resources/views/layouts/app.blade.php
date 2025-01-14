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
    <header class="navbar navbar-expand-lg navbar-dark sticky-top bg-dark nav-pills ">
        <nav class="container-xxl flex-wrap" aria-label="Main navigation">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#bdSidebar" aria-controls="bdSidebar" aria-label="Toggle docs navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
            <a class="navbar-brand" href="{{ route('index') }}">Mensa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bdNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="bdNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a href="#" class="nav-link">Dummy</a>
                    </li>
                </ul>
                <hr class="d-lg-none text-white-50">
                <ul class="navbar-nav">
                    @if(auth()->user() && auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a href="{{ route('admin.index') }}" @class([
                                'nav-link',
                                'align-items-center',
                                'active' => str_starts_with(Route::current()->getName(), 'admin.'),
                            ])>Admin</a>
                        </li>
                        <hr class="d-lg-none text-white-50">
                    @endif
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
        </nav>
    </header>
    <div @class([
        'container-xxl',
        'd-flex' => str_starts_with(Route::current()->getName(), 'admin.'),
    ])>
        @if(str_starts_with(Route::current()->getName(), 'admin.'))
            <aside class="flex-column">
                <div class="offcanvas-lg offcanvas-start" tabindex="-1" aria-labelledby="bdSidebarOffcanvasLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title" id="bdSidebarOffcanvasLabel">Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#bdSidebar"></button>
                    </div>
                    <nav class="offcanvas-body">
                        <ul class="nav flex-column nav-pills">
                            <li class="nav-item">
                                <a href="{{ route('admin.index') }}" @class([
                                    'nav-link',
                                    'align-items-center',
                                    'active' => Route::current()->getName() == 'admin.index',
                                ])>Dashboard</a>
                            </li>
                            <li class="nav-item accordion">
                                <button role="button"
                                    data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavAdminUser" aria-controls="asideNavAdminUser"
                                    style="height: 0em"
                                    @class([
                                        'nav-item',
                                        'accordion-button',
                                        'collapsed' => !str_starts_with(
                                            Route::current()->getName(),
                                            'admin.users.'
                                        ),
                                    ])>
                                    Users
                                </button>
                                <ul id="asideNavAdminUser" @class([
                                    'accordion-collapse',
                                    'collapse',
                                    'show' => str_starts_with(
                                        Route::current()->getName(),
                                        'admin.users.'
                                    ),
                                ])>
                                    <li>
                                        <a href="{{ route('admin.users.index') }}" @class([
                                            'nav-link',
                                            'align-items-center',
                                            'active' => Route::current()->getName() == 'admin.users.index',
                                        ])>Index</a>
                                    </li>
                                    @if(Route::current()->getName() == 'admin.users.show')
                                        <a href="#" class="nav-link align-items-center active">Show</a>
                                    @endif
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.team-types.index') }}" @class([
                                    'nav-link',
                                    'align-items-center',
                                    'active' => Route::current()->getName() == 'admin.team-types.index',
                                ])>Team Types</a>
                            </li>
                            <li class="nav-item accordion">
                                <button role="button"
                                    data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavAdminTeam" aria-controls="asideNavAdminTeam"
                                    style="height: 0em"
                                    @class([
                                        'nav-item',
                                        'accordion-button',
                                        'collapsed' => !str_starts_with(
                                            Route::current()->getName(),
                                            'admin.teams.'
                                        ),
                                    ])>
                                    Teams
                                </button>
                                <ul id="asideNavAdminTeam" @class([
                                    'accordion-collapse',
                                    'collapse',
                                    'show' => str_starts_with(
                                        Route::current()->getName(),
                                        'admin.teams.'
                                    ),
                                ])>
                                    <li>
                                        <a href="{{ route('admin.teams.index') }}" @class([
                                            'nav-link',
                                            'align-items-center',
                                            'active' => Route::current()->getName() == 'admin.teams.index',
                                        ])>Index</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.modules.index') }}" @class([
                                    'nav-link',
                                    'align-items-center',
                                    'active' => Route::current()->getName() == 'admin.modules.index',
                                ])>Module</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.permissions.index') }}" @class([
                                    'nav-link',
                                    'align-items-center',
                                    'active' => Route::current()->getName() == 'admin.permissions.index',
                                ])>Permission</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </aside>
        @endif
        <main>
            @yield('main')
        </main>
    </div>
    <div class="modal alert" id="alert" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alert</h5>
                </div>
                <div class="modal-body">
                    <p id="alertMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal alert" id="confirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <button id="confirmButton" type="button" class="btn btn-success">Confirm</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
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
        function bootstrapConfirm(message, callback, passData) {
            const confirmButton = document.getElementById('confirmButton');
            const confirmDiv = document.getElementById('confirm');
            const confirmModal = new bootstrap.Modal(confirmDiv);
            const confirmMessage = document.getElementById('confirmMessage');
            let confirmHandle = function() {
                confirmModal.hide();
                callback(passData);
            }
            confirmButton.addEventListener('click', confirmHandle);
            confirmDiv.addEventListener(
                'hide.bs.modal', function() {
                    confirmButton.removeEventListener('click', confirmHandle);
                }
            );
            confirmMessage.innerText = message;
            confirmModal.show();
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
