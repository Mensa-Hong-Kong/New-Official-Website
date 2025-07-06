<script>
    import { page } from "@inertiajs/svelte";
	import NavDropdown from '@/Pages/Components/NavDropdown.svelte';
	import Alert, { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import Confirm from '@/Pages/Components/Modals/Confirm.svelte';
	import { setCsrfToken } from '@/submitForm.svelte';
	let { children } = $props();

    setCsrfToken($page.props.csrf_token);

    if ($page.props.flash.success) {
        alert($page.props.flash.success);
    }
    if ($page.props.flash.error) {
        alert($page.props.flash.error);
    }
</script>

<svelte:head>
    {#if $page.props.title}
        <title>{$page.props.title} | {import.meta.env.VITE_APP_NAME}</title>
    {:else}
        <title>{import.meta.env.VITE_APP_NAME} </title>
    {/if}
</svelte:head>

<header class="navbar navbar-expand-lg navbar-dark sticky-top bg-dark nav-pills ">
    <nav class="flex-wrap container-xxl" aria-label="Main navigation">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#bdSidebar" aria-label="Toggle admin navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="{route('index')}">Mensa</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bdNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="bdNavbar">
            <ul class="navbar-nav me-auto">
                {#each Object.entries($page.props.nav) as [id, item]}
                    {#if item.children}
                        <li class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle" id="dropdown{id}"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {item.name}
                            </button>
                            <NavDropdown items={item.children} id={id} />
                        </li>
                    {:else}
                        <li class="nav-item">
                            <a href="{item.url ?? '#'}" class="nav-link">{item.name}</a>
                        </li>
                    {/if}
                {/each}
            </ul>
            <hr class="d-lg-none text-white-50">
            <ul class="navbar-nav">
                {#if $page.props.auth.user}
                    {#if
                        $page.props.auth.user.hasProctorTests ||
                        $page.props.auth.user.permissions.length ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <li class="nav-item">
                            <a href="{
                                $page.props.auth.user.permissions.length ||
                                $page.props.auth.user.roles.includes('Super Administrator') ?
                                route('admin.index') :
                                route('admin.admission-tests.index')
                            }" class={[
                                'nav-link', 'align-items-center',
                                {active: route().current().startsWith('admin.')}
                            ]}>Admin</a>
                        </li>
                        <hr class="d-lg-none text-white-50">
                    {/if}
                    <li class="nav-item">
                        <a href="{route('profile.show')}" class={[
                            'nav-link', 'align-items-center',
                            {active: route().current('profile.show')}
                        ]}>Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="{route('logout')}" class='nav-link align-items-center'>Logout</a>
                    </li>
                {:else}
                    <li class="nav-item">
                        <a href="{route('login')}" class={[
                            'nav-link', 'align-items-center',
                            {active: route().current('login')}
                        ]}>Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{route('register')}" class={[
                            'nav-link', 'align-items-center',
                            {active: route().current('register')}
                        ]}>Register</a>
                    </li>
                {/if}
            </ul>
        </div>
    </nav>
</header>
<div class={['container-xxl', {'d-flex': route().current().startsWith('admin.')}]}>
    {#if route().current().startsWith('admin.')}
        <aside class="offcanvas-lg offcanvas-start" tabindex="-1" id="bdSidebar" aria-labelledby="bdSidebarOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 id="bdSidebarOffcanvasLabel">Admin</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#bdSidebar"></button>
            </div>
            <nav class="offcanvas-body">
                <ul class="nav flex-column nav-pills">
                    {#if
                        $page.props.auth.user.permissions.length ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <li class="nav-item">
                            <a href="{route('admin.index')}" class={[
                                'nav-link', 'align-items-center',
                                {active: route().current('admin.index')},
                            ]}>Dashboard</a>
                        </li>
                        {#if
                            $page.props.auth.user.permissions.includes('View:User') ||
                            $page.props.auth.user.roles.includes('Super Administrator')
                        }
                            {#if route().current('admin.users.show')}
                                <li class="accordion">
                                    <button data-bs-toggle="collapse" aria-expanded="true"
                                        data-bs-target="#asideNavAdminUser" aria-controls="asideNavAdminUser"
                                        style="height: 0em" class={[
                                            'nav-item', 'accordion-button',
                                            {collapsed: ! route().current().startsWith('admin.users.')},
                                        ]}>Users</button>
                                    <ul id="asideNavAdminUser" class={[
                                        'accordion-collapse', 'collapse',
                                        {show: route().current().startsWith('admin.users.')},
                                    ]}>
                                        <li>
                                            <a href="{route('admin.users.index')}" class={[
                                                'nav-link', 'align-items-center',
                                                {active: route().current('admin.users.index')},
                                            ]}>Index</a>
                                        </li>
                                        {#if route().current('admin.users.show')}
                                            <a href="{route().current()}"
                                                class="nav-link align-items-center active">Show</a>
                                        {/if}
                                    </ul>
                                </li>
                            {:else}
                                <li class="nav-item">
                                    <a href="{route('admin.users.index')}" class={[
                                        'nav-link',
                                        'align-items-center',
                                        {active: route().current('admin.users.index')},
                                    ]}>Users</a>
                                </li>
                            {/if}
                        {/if}
                        <li class="nav-item">
                            <a href="{route('admin.team-types.index')}" class={[
                                'nav-link', 'align-items-center',
                                {active: route().current('admin.team-types.index')},
                            ]}>Team Types</a>
                        </li>
                        {#if
                            $page.props.auth.user.permissions.includes('Edit:Permission') ||
                            $page.props.auth.user.roles.includes('Super Administrator') ||
                            route().current().startsWith('admin.teams.roles.') ||
                            ['admin.teams.show', 'admin.teams.edit'].includes(route().current())
                        }
                            <li class="accordion">
                                <button data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavAdminTeam" aria-controls="asideNavAdminTeam"
                                    style="height: 0em" class={[
                                        'nav-item', 'accordion-button',
                                        {collapsed: ! route().current().startsWith('admin.teams.')},
                                    ]}>
                                    Teams
                                </button>
                                <ul id="asideNavAdminTeam" class={[
                                    'accordion-collapse', 'collapse',
                                    {show: route().current().startsWith('admin.teams.')},
                                ]}>
                                    <li>
                                        <a href="{route('admin.teams.index')}" class={[
                                            'nav-link', 'align-items-center',
                                            {active: route().current('admin.teams.index')},
                                        ]}>Index</a>
                                    </li>
                                    {#if
                                        $page.props.auth.user.permissions.includes('Edit:Permission') ||
                                        $page.props.auth.user.roles.includes('Super Administrator')
                                    }
                                        <li>
                                            <a href="{route('admin.teams.create')}" class={[
                                                'nav-link', 'align-items-center',
                                                {active: route().current('admin.teams.create')},
                                            ]}>Create</a>
                                        </li>
                                    {/if}
                                    {#if
                                        route().current().startsWith('admin.teams.roles.') ||
                                        ['admin.teams.show', 'admin.teams.edit'].includes(route().current())
                                    }
                                        <li>
                                            <a href="{route('admin.teams.show', {team: route().params.team}) }"
                                                class={[
                                                    'nav-link', 'align-items-center',
                                                    {active: route().current('admin.teams.show')},
                                                ]}>Show</a>
                                        </li>
                                    {/if}
                                    {#if route().current('admin.teams.edit')}
                                        <li>
                                            <a href="{route('admin.teams.edit', {team: route().params.team}) }"
                                                class="nav-link align-items-center active">Edit</a>
                                        </li>
                                    {/if}
                                    {#if route().current('admin.teams.roles.create')}
                                        <li>
                                            <a href="{route('admin.teams.roles.create', {team: route().params.team}) }"
                                                class="nav-link align-items-center active">Create Role</a>
                                        </li>
                                    {/if}
                                    {#if route().current('admin.teams.roles.edit')}
                                        <li>
                                            <a href="{route().current()}"
                                                class="nav-link align-items-center active">Edit Role</a>
                                        </li>
                                    {/if}
                                </ul>
                            </li>
                        {:else}
                            <li class="nav-item">
                                <a href="{route('admin.teams.index')}" class={[
                                    'nav-link', 'align-items-center',
                                    {active: route().current('admin.teams.index')},
                                ]}>Teams</a>
                            </li>
                        {/if}
                        <li class="nav-item">
                            <a href="{route('admin.modules.index')}" class={[
                                'nav-link', 'align-items-center',
                                {active: route().current('admin.modules.index')},
                            ]}>Module</a>
                        </li>
                        <li class="nav-item">
                            <a href="{route('admin.permissions.index')}" class={[
                                'nav-link', 'align-items-center',
                                {active: route().current('admin.permissions.index')},
                            ]}>Permission</a>
                        </li>
                    {/if}
                    {#if
                        $page.props.auth.user.permissions.includes('Edit:Admission Test') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavAdminAdmissionTestType" aria-controls="asideNavAdminAdmissionTestType"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! route().current().startsWith('admin.admission-test.types.')},
                                ]}>Admission Test Types</button>
                            <ul id="asideNavAdminAdmissionTestType" class={[
                                'accordion-collapse', 'collapse',
                                {show: route().current().startsWith('admin.admission-test.types.')},
                            ]}>
                                <li>
                                    <a href="{route('admin.admission-test.types.index')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.admission-test.types.index')},
                                    ]}>Index</a>
                                </li>
                                <li>
                                    <a href="{route('admin.admission-test.types.create')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.admission-test.types.create')},
                                    ]}>Create</a>
                                </li>
                                {#if route().current('admin.admission-test.types.edit')}
                                    <li>
                                        <a href="{route().current()}"
                                            class="nav-link align-items-center active">Edit</a>
                                    </li>
                                {/if}
                            </ul>
                        </li>
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavAdminAdmissionTestProduct" aria-controls="asideNavAdminAdmissionTestProduct"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! route().current().startsWith('admin.admission-test.products.')},
                                ]}>
                                Admission Test Products
                            </button>
                            <ul id="asideNavAdminAdmissionTestProduct" class={[
                                'accordion-collapse', 'collapse',
                                {show: route().current().startsWith('admin.admission-test.products.')},
                            ]}>
                                <li>
                                    <a href="{route('admin.admission-test.products.index')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.admission-test.products.index')},
                                    ]}>Index</a>
                                </li>
                                <li>
                                    <a href="{route('admin.admission-test.products.create')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.admission-test.products.create')},
                                    ]}>Create</a>
                                </li>
                                {#if route().current('admin.admission-test.products.show')}
                                    <li>
                                        <a href="{route().current()}"
                                            class="nav-link align-items-center active">Show</a>
                                    </li>
                                {/if}
                            </ul>
                        </li>
                    {/if}
                    {#if
                        $page.props.auth.user.hasProctorTests ||
                        $page.props.auth.user.permissions.includes('Edit:Admission Test') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        {#if
                            ! (
                                $page.props.auth.user.permissions.includes('Edit:Admission Test') ||
                                $page.props.auth.user.roles.includes('Super Administrator')
                            ) && ! route().current('admin.admission-tests.show')
                        }
                            <li class="nav-item">
                                <a href="{route('admin.admission-tests.index')}" class={[
                                    'nav-link', 'align-items-center',
                                    {active: route().current('admin.admission-tests.index')},
                                ]}>Admission Tests</a>
                            </li>
                        {:else}
                            <li class="accordion">
                                <button data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavAdminAdmissionTest" aria-controls="asideNavAdminAdmissionTest"
                                    style="height: 0em" class={[
                                        'nav-item', 'accordion-button',
                                        {collapsed: ! route().current().startsWith('admin.admission-tests.')},
                                    ]}>
                                    Admission Tests
                                </button>
                                <ul id="asideNavAdminAdmissionTest" class={[
                                    'accordion-collapse', 'collapse',
                                    {show: route().current().startsWith('admin.admission-tests.')},
                                ]}>
                                    <li>
                                        <a href="{route('admin.admission-tests.index')}" class={[
                                            'nav-link', 'align-items-center',
                                            {active: route().current('admin.admission-tests.index')},
                                        ]}>Index</a>
                                    </li>
                                    {#if
                                        $page.props.auth.user.permissions.includes('Edit:Admission Test') ||
                                        $page.props.auth.user.roles.includes('Super Administrator')
                                    }
                                        <li>
                                            <a href="{route('admin.admission-tests.create')}" class={[
                                                'nav-link', 'align-items-center',
                                                {active: route().current('admin.admission-tests.create')},
                                            ]}>Create</a>
                                        </li>
                                    {/if}
                                    {#if route().current('admin.admission-tests.show')}
                                        <li>
                                            <a href="{route().current()}"
                                                class="nav-link align-items-center active">Show</a>
                                        </li>
                                    {/if}
                                </ul>
                            </li>
                        {/if}
                    {/if}
                    {#if
                        $page.props.auth.user.permissions.includes('Edit:Other Payment Gateway') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <li class="nav-item">
                            <a href="{route('admin.other-payment-gateways.index')}" class={[
                                'nav-link', 'align-items-center',
                                {active: route().current('admin.other-payment-gateways.index')},
                            ]}>Other Payment Gateway</a>
                        </li>
                    {/if}
                    {#if
                        $page.props.auth.user.permissions.includes('Edit:Site Content') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        {#if route().current('admin.site-contents.edit')}
                            <li class="accordion">
                                <button data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavSiteContent" aria-controls="asideNavSiteContent"
                                    style="height: 0em" class="accordion-button">
                                    Site Content
                                </button>
                                <ul id="asideNavSiteContent" class="accordion-collapse collapse show">
                                    <li>
                                        <a href="{route('admin.site-contents.index')}"
                                            class="nav-link align-items-center">Index</a>
                                    </li>
                                    <li>
                                        <a href="{route().current()}"
                                            class="nav-link align-items-center active">Edit</a>
                                    </li>
                                </ul>
                            </li>
                        {:else}
                            <li class="nav-item">
                                <a href="{route('admin.site-contents.index')}" class={[
                                    'nav-link', 'align-items-center',
                                    {active: route().current('admin.site-contents.index')},
                                ]}>Site Content</a>
                            </li>
                        {/if}
                    {/if}
                    {#if
                        $page.props.auth.user.permissions.includes('Edit:Custom Web Page') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavCustomWebPage" aria-controls="asideNavCustomWebPage"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! route().current().startsWith('admin.custom-web-pages.')},
                                ]}>
                                Custom Web Pages
                            </button>
                            <ul id="asideNavCustomWebPage" class={[
                                'accordion-collapse', 'collapse',
                                {show: route().current().startsWith('admin.custom-web-pages.')},
                            ]}>
                                <li>
                                    <a href="{route('admin.custom-web-pages.index')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.custom-web-pages.index')},
                                    ]}>Index</a>
                                </li>
                                <li>
                                    <a href="{route('admin.custom-web-pages.create')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.custom-web-pages.create')},
                                    ]}>Create</a>
                                </li>
                                {#if route().current('admin.custom-web-pages.edit')}
                                    <li>
                                        <a href="{route().current()}"
                                            class="nav-link align-items-center active">Edit</a>
                                    </li>
                                {/if}
                            </ul>
                        </li>
                    {/if}
                    {#if
                        $page.props.auth.user.permissions.includes('Edit:Navigation Item') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavNavigationItem" aria-controls="asideNavNavigationItem"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! route().current().startsWith('admin.navigation-items.')},
                                ]}>Navigation Items</button>
                            <ul id="asideNavNavigationItem" class={[
                                'accordion-collapse', 'collapse',
                                {show: route().current().startsWith('admin.navigation-items.')},
                            ]}>
                                <li>
                                    <a href="{route('admin.navigation-items.index')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.navigation-items.index')},
                                    ]}>Index</a>
                                </li>
                                <li>
                                    <a href="{route('admin.navigation-items.create')}" class={[
                                        'nav-link', 'align-items-center',
                                        {active: route().current('admin.navigation-items.create')},
                                    ]}>Create</a>
                                </li>
                                {#if route().current('admin.navigation-items.edit')}
                                    <li>
                                        <a href="{route().current()}"
                                            class="nav-link align-items-center active">Edit</a>
                                    </li>
                                {/if}
                            </ul>
                        </li>
                    {/if}
                </ul>
            </nav>
        </aside>
    {/if}
    <main class="w-100">
        {@render children()}
    </main>
</div>

<Alert />
<Confirm />
