<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @hasSection('title')
        <title>@yield('title') | Mensa Hong Kong</title>
        <meta name="title" content="@yield('title')">
    @else
        <title>Mensa Hong Kong</title>
    @endif
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    @hasSection('og image url')
        <meta name="og:image" content="@yield('og_image_url')">
    @endif
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
                <x-navbar-root></x-navbar-root>
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
                    @if(auth()->user() && count(auth()->user()->proctorTests))
                        <li class="nav-item">
                            <a href="{{ route('admin.admission-tests.index') }}" @class([
                                'nav-link',
                                'align-items-center',
                                'active' => str_starts_with(Route::current()->getName(), 'admin.admission-tests.'),
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
                            @if(auth()->user() && auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a href="{{ route('admin.index') }}" @class([
                                        'nav-link',
                                        'align-items-center',
                                        'active' => Route::current()->getName() == 'admin.index',
                                    ])>Dashboard</a>
                                </li>
                                @can('View:User')
                                    @if(Route::current()->getName() == 'admin.users.show')
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
                                                    <a href="{{ route('admin.users.show', ['user' => $user]) }}" class="nav-link align-items-center active">Show</a>
                                                @endif
                                            </ul>
                                        </li>
                                    @else
                                        <li class="nav-item">
                                            <a href="{{ route('admin.users.index') }}" @class([
                                                'nav-link',
                                                'align-items-center',
                                                'active' => Route::current()->getName() == 'admin.users.index',
                                            ])>Users</a>
                                        </li>
                                    @endif
                                @endcan
                                <li class="nav-item">
                                    <a href="{{ route('admin.team-types.index') }}" @class([
                                        'nav-link',
                                        'align-items-center',
                                        'active' => Route::current()->getName() == 'admin.team-types.index',
                                    ])>Team Types</a>
                                </li>
                                @if(
                                    in_array(
                                        Route::current()->getName(),
                                        [
                                            'admin.teams.show',
                                            'admin.teams.edit',
                                        ]
                                    ) ||
                                    str_starts_with(Route::current()->getName(), 'admin.teams.roles.') ||
                                    (auth()->user() && auth()->user()->can('Edit:Permission'))
                                )
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
                                            @can('Edit:Permission')
                                                <li>
                                                    <a href="{{ route('admin.teams.create') }}" @class([
                                                        'nav-link',
                                                        'align-items-center',
                                                        'active' => Route::current()->getName() == 'admin.teams.create',
                                                    ])>Create</a>
                                                </li>
                                            @endcan
                                            @if(
                                                in_array(
                                                    Route::current()->getName(),
                                                    [
                                                        'admin.teams.show',
                                                        'admin.teams.edit',
                                                    ]
                                                ) ||
                                                str_starts_with(Route::current()->getName(), 'admin.teams.roles.')
                                            )
                                                <li>
                                                    <a href="{{ route('admin.teams.show', ['team' => $team]) }}"
                                                        @class([
                                                            'nav-link',
                                                            'align-items-center',
                                                            'active' => Route::current()->getName() == 'admin.teams.show',
                                                        ])>Show</a>
                                                </li>
                                            @endif
                                            @if(Route::current()->getName() == 'admin.teams.edit')
                                                <li>
                                                    <a href="{{ route('admin.teams.edit', ['team' => $team]) }}"
                                                        class="nav-link align-items-center active">Edit</a>
                                                </li>
                                            @endif
                                            @if(Route::current()->getName() == 'admin.teams.roles.create')
                                                <li>
                                                    <a href="{{ route('admin.teams.roles.create', ['team' => $team]) }}"
                                                        class="nav-link align-items-center active">Create Role</a>
                                                </li>
                                            @endif
                                            @if(Route::current()->getName() == 'admin.teams.roles.edit')
                                                <li>
                                                    <a href="{{ route('admin.teams.roles.edit', ['team' => $team, 'role' => $role]) }}"
                                                        class="nav-link align-items-center active">Edit Role</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ route('admin.teams.index') }}" @class([
                                            'nav-link',
                                            'align-items-center',
                                            'active' => Route::current()->getName() == 'admin.teams.index',
                                        ])>Teams</a>
                                    </li>
                                @endif
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
                            @endif
                            @can('Edit:Admission Test')
                                <li class="nav-item accordion">
                                    <button role="button"
                                        data-bs-toggle="collapse" aria-expanded="true"
                                        data-bs-target="#asideNavAdminAdmissionTestType" aria-controls="asideNavAdminAdmissionTestType"
                                        style="height: 0em"
                                        @class([
                                            'nav-item',
                                            'accordion-button',
                                            'collapsed' => !str_starts_with(
                                                Route::current()->getName(),
                                                'admin.admission-test-types.'
                                            ),
                                        ])>
                                        Admission Test Types
                                    </button>
                                    <ul id="asideNavAdminAdmissionTestType" @class([
                                        'accordion-collapse',
                                        'collapse',
                                        'show' => str_starts_with(
                                            Route::current()->getName(),
                                            'admin.admission-test-types.'
                                        ),
                                    ])>
                                        <li>
                                            <a href="{{ route('admin.admission-test-types.index') }}" @class([
                                                'nav-link',
                                                'align-items-center',
                                                'active' => Route::current()->getName() == 'admin.admission-test-types.index',
                                            ])>Index</a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                            @if(
                                auth()->user() && (
                                    count(auth()->user()->proctorTests) ||
                                    auth()->user()->can('Edit:Admission Test')
                                )
                            )
                                @if(
                                    auth()->user() &&
                                    !auth()->user()->can('Edit:Admission Test') &&
                                    !Route::current()->getName() == 'admin.admission-tests.show'
                                )
                                    <li class="nav-item">
                                        <a href="{{ route('admin.admission-tests.index') }}" @class([
                                            'nav-link',
                                            'align-items-center',
                                            'active' => Route::current()->getName() == 'admin.admission-tests.index',
                                        ])>Admission Tests</a>
                                    </li>
                                @else
                                    <li class="nav-item accordion">
                                        <button role="button"
                                            data-bs-toggle="collapse" aria-expanded="true"
                                            data-bs-target="#asideNavAdminAdmissionTest" aria-controls="asideNavAdminAdmissionTest"
                                            style="height: 0em"
                                            @class([
                                                'nav-item',
                                                'accordion-button',
                                                'collapsed' => !str_starts_with(
                                                    Route::current()->getName(),
                                                    'admin.admission-tests.'
                                                ),
                                            ])>
                                            Admission Tests
                                        </button>
                                        <ul id="asideNavAdminAdmissionTest" @class([
                                            'accordion-collapse',
                                            'collapse',
                                            'show' => str_starts_with(
                                                Route::current()->getName(),
                                                'admin.admission-tests.'
                                            ),
                                        ])>
                                            <li>
                                                <a href="{{ route('admin.admission-tests.index') }}" @class([
                                                    'nav-link',
                                                    'align-items-center',
                                                    'active' => Route::current()->getName() == 'admin.admission-tests.index',
                                                ])>Index</a>
                                            </li>
                                            @can('Edit:Admission Test')
                                                <li>
                                                    <a href="{{ route('admin.admission-tests.create') }}" @class([
                                                        'nav-link',
                                                        'align-items-center',
                                                        'active' => Route::current()->getName() == 'admin.admission-tests.create',
                                                    ])>Create</a>
                                                </li>
                                            @endcan
                                            @if(Route::current()->getName() == 'admin.admission-tests.show')
                                                <li>
                                                    <a href="{{ route('admin.admission-tests.show', ['admission_test' => $test]) }}"
                                                        class="nav-link align-items-center active">Show</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            @endif
                            @can('Edit:Site Content')
                                @if(Route::current()->getName() == 'admin.site-contents.edit')
                                    <li class="nav-item accordion">
                                        <button role="button"
                                            data-bs-toggle="collapse" aria-expanded="true"
                                            data-bs-target="#asideNavSiteContent" aria-controls="asideNavSiteContent"
                                            style="height: 0em" class="nav-item accordion-button">
                                            Site Content
                                        </button>
                                        <ul id="asideNavSiteContent" class="accordion-collapse collapse show">
                                            <li>
                                                <a href="{{ route('admin.site-contents.index') }}" class="nav-link align-items-center">Index</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.site-contents.edit', ['site_content' => $content]) }}"
                                                    class="nav-link align-items-center active">Edit</a>
                                            </li>
                                        </ul>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a href="{{ route('admin.site-contents.index') }}" @class([
                                            'nav-link',
                                            'align-items-center',
                                            'active' => Route::current()->getName() == 'admin.site-contents.index',
                                        ])>Site Content</a>
                                    </li>
                                @endif
                            @endcan
                            @can('Edit:Custom Page')
                                <li class="nav-item accordion">
                                    <button role="button"
                                        data-bs-toggle="collapse" aria-expanded="true"
                                        data-bs-target="#asideNavCustomPage" aria-controls="asideNavCustomPage"
                                        style="height: 0em"
                                        @class([
                                            'nav-item',
                                            'accordion-button',
                                            'collapsed' => !str_starts_with(
                                                Route::current()->getName(),
                                                'admin.custom-pages.'
                                            ),
                                        ])>
                                        Custom Pages
                                    </button>
                                    <ul id="asideNavCustomPage" @class([
                                        'accordion-collapse',
                                        'collapse',
                                        'show' => str_starts_with(
                                            Route::current()->getName(),
                                            'admin.custom-pages.'
                                        ),
                                    ])>
                                        <li>
                                            <a href="{{ route('admin.custom-pages.index') }}" @class([
                                                'nav-link',
                                                'align-items-center',
                                                'active' => Route::current()->getName() == 'admin.custom-pages.index',
                                            ])>Index</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.custom-pages.create') }}" @class([
                                                'nav-link',
                                                'align-items-center',
                                                'active' => Route::current()->getName() == 'admin.custom-pages.create',
                                            ])>Create</a>
                                        </li>
                                        @if(Route::current()->getName() == 'admin.custom-pages.edit')
                                            <li>
                                                <a href="{{ route('admin.custom-pages.edit', ['custom_page' => $page]) }}"
                                                    class="nav-link align-items-center active">Edit</a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endcan
                            @can('Edit:Navigation Item')
                                <li class="nav-item accordion">
                                    <button role="button"
                                        data-bs-toggle="collapse" aria-expanded="true"
                                        data-bs-target="#asideNavNavigationItem" aria-controls="asideNavNavigationItem"
                                        style="height: 0em"
                                        @class([
                                            'nav-item',
                                            'accordion-button',
                                            'collapsed' => !str_starts_with(
                                                Route::current()->getName(),
                                                'admin.navigation-items.'
                                            ),
                                        ])>
                                        Navigation Items
                                    </button>
                                    <ul id="asideNavNavigationItem" @class([
                                        'accordion-collapse',
                                        'collapse',
                                        'show' => str_starts_with(
                                            Route::current()->getName(),
                                            'admin.navigation-items.'
                                        ),
                                    ])>
                                        <li>
                                            <a href="{{ route('admin.navigation-items.index') }}" @class([
                                                'nav-link',
                                                'align-items-center',
                                                'active' => Route::current()->getName() == 'admin.navigation-items.index',
                                            ])>Index</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.navigation-items.create') }}" @class([
                                                'nav-link',
                                                'align-items-center',
                                                'active' => Route::current()->getName() == 'admin.navigation-items.create',
                                            ])>Create</a>
                                        </li>
                                        @if(Route::current()->getName() == 'admin.navigation-items.edit')
                                            <li>
                                                <a href="{{ route('admin.navigation-items.edit', ['navigation_item' => $item]) }}"
                                                    class="nav-link align-items-center active">Edit</a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endcan
                        </ul>
                    </nav>
                </div>
            </aside>
        @endif
        <main class="w-100">
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
        function bootstrapAlert(message, closedEvent = null) {
            document.getElementById('alertMessage').innerText = message;
            let alertDiv = document.getElementById('alert')
            let alertModal = new bootstrap.Modal(alertDiv);
            if(closedEvent) {
                let closedHandle = function() {
                    alertDiv.removeEventListener('hide.bs.modal', closedHandle);
                    closedEvent();
                }
                alertDiv.addEventListener('hide.bs.modal', closedHandle);
            }
            alertModal.show();
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
