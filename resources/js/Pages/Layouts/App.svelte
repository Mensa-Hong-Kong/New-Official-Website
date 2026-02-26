<script>
    import { page, Link } from "@inertiajs/svelte";
    import { useColorMode, Navbar, NavbarToggler, Collapse, Nav, Dropdown, DropdownToggle, Container, NavItem } from '@sveltestrap/sveltestrap';
	import NavDropdown from '@/Pages/Components/NavDropdown.svelte';
	import Alert, { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import Confirm from '@/Pages/Components/Modals/Confirm.svelte';
    import { can, canAny } from "@/gate.ts";

    let isOpenNav = $state(false);

    let theme = $state(
        localStorage.getItem('theme') !== null ?
            localStorage.getItem('theme') :
            import.meta.env.VITE_APP_ENV != 'production' ? 'light' :
                window.matchMedia('(prefers-color-scheme: dark)').matches ?
                    'dark' : 'light'
    );
    useColorMode(theme);

    function handleThemeUpdate(event) {
        localStorage.setItem('theme', theme === 'dark' ? 'light' : 'dark');
        theme = localStorage.getItem('theme');
        useColorMode(theme);
    }

    function handleNavUpdate(event) {
        isOpenNav = event.detail;
    }

    function navToggle() {
        isOpenNav = !isOpenNav;
    }

    if ($page.props.flash.success) {
        alert($page.props.flash.success);
    }
    if ($page.props.flash.error) {
        alert($page.props.flash.error);
    }

    let navigationNodes = $state(Object.fromEntries(
        $page.props.navigationItems.map(
            row => [row.master_id ?? 'root', []]
        )
    ));
    for (let data of $page.props.navigationItems ) {
        navigationNodes[data.master_id ?? 'root'].push(data);
    }
</script>
<script module>
    import { asset } from "@/asset.svelte.js";
    export let seo = $state({
        title: '',
        description: import.meta.env.VITE_APP_DESCRIPTION,
        ogImageUrl: asset('og_image.png'),
    });
</script>

<svelte:head>
    <title>{seo.title ? `${seo.title} | ` : ''}{import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="{seo.title ? `${seo.title} | ` : ''}{import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{seo.description}">
    <meta name="og:description" content="{seo.description}">
    <meta name="og:image" content="{seo.ogImage}">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>

<header>
    <Navbar color="black" theme="dark" expand="lg" container="xxl" sticky="top" pills fixed="wrap">
        {#if route().current().startsWith('admin.')}
            <button class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#bdSidebar" aria-label="Toggle admin navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        {/if}
        <Link class="navbar-brand me-auto" href={route('index')}>
            Mensa
        </Link>
        <NavbarToggler on:click={navToggle} />
        <Collapse isOpen={isOpenNav} navbar expand="md" on:update={handleNavUpdate}>
            <Nav class="me-auto" navbar>
                {#each navigationNodes.root as item}
                    {#if navigationNodes[item.id] && navigationNodes[item.id].length}
                        <Dropdown nav inNavbar>
                            <DropdownToggle nav caret>{item.name}</DropdownToggle>
                            <NavDropdown nodes={navigationNodes} id={item.id} />
                        </Dropdown>
                    {:else}
                        <NavItem>
                            <Link class={["nav-link", {active: $page.url == `/${item.url}`}]}
                                href={item.url ?? '#'}>
                                {item.name}
                            </Link>
                        </NavItem>
                    {/if}
                {/each}
            </Nav>
            <hr class="d-lg-none text-white-50">
            <Nav class="ms-auto" navbar>
                <NavItem>
                    <button class="nav-link" onclick={handleThemeUpdate} aria-label="Toggle {theme === 'dark' ? 'light' : 'dark'} mode" >
                        Theme: {theme === 'dark' ? 'Dark' : 'Light'}
                    </button>
                </NavItem>
                {#if $page.props.auth.user}
                    {#if
                        $page.props.auth.user.permissions.length ||
                        $page.props.auth.user.hasProctorTests ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <NavItem>
                            <Link href={
                                $page.props.auth.user.permissions.length ||
                                $page.props.auth.user.roles.includes('Super Administrator') ?
                                route('admin.index') :
                                route('admin.admission-tests.index')
                            } class={[
                                'nav-link',
                                {active: $page.component.startsWith('Admin/')}
                            ]}>Admin</Link>
                        </NavItem>
                        <hr class="d-lg-none text-white-50">
                    {/if}
                    <NavItem>
                        <Link href={route('profile.show')}
                            class={[
                                'nav-link',
                                {active: $page.component == 'User/Profile'}
                            ]}>Profile</Link>
                    </NavItem>
                    <NavItem>
                        <Link href={route('logout')} class="nav-link">Logout</Link>
                    </NavItem>
                {:else}
                    <NavItem>
                        <Link href={route('login')}
                            class={[
                                'nav-link',
                                {active: $page.component == 'User/Login'}
                            ]}>Login</Link>
                    </NavItem>
                    <NavItem>
                        <Link href={route('register')}
                            class={[
                                'nav-link',
                                {active: $page.component == 'User/Register'}
                            ]}>Register</Link>
                    </NavItem>
                {/if}
            </Nav>
        </Collapse>
    </Navbar>
</header>
<Container xxl class={{'d-flex': $page.component.startsWith('Admin/')}}>
    {#if $page.component.startsWith('Admin/')}
        <aside class="offcanvas-lg offcanvas-start" tabindex="-1" id="bdSidebar" aria-labelledby="bdSidebarOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 id="bdSidebarOffcanvasLabel">Admin</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#bdSidebar"></button>
            </div>
            <nav class="offcanvas-body">
                <Nav vertical pills class="offcanvas-body">
                    {#if
                        $page.props.auth.user.permissions.length ||
                        $page.props.auth.user.hasProctorTests ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <NavItem>
                            <Link href={route('admin.index')}
                                class={[
                                    'nav-link',
                                    {active: $page.component == 'Admin/Index'}
                                ]}>Dashboard</Link>
                        </NavItem>
                        {#if can('View:User')}
                            {#if $page.component == 'Admin/Users/Show'}
                                <li class="accordion">
                                    <button data-bs-toggle="collapse" aria-expanded="true"
                                        data-bs-target="#asideNavAdminUser" aria-controls="asideNavAdminUser"
                                        style="height: 0em" class={[
                                            'nav-item', 'accordion-button',
                                            {collapsed: ! $page.component.startsWith('Admin/Users/')},
                                        ]}>Users</button>
                                    <ul id="asideNavAdminUser" class="accordion-collapse collapse show">
                                        <NavItem>
                                            <Link href={route('admin.users.index')}
                                                class="nav-link">Index</Link>
                                        </NavItem>
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    "admin.users.show",
                                                    {user: route().params.user}
                                                )
                                            } class="nav-link active">Show</Link>
                                        </NavItem>
                                    </ul>
                                </li>
                            {:else}
                                <NavItem>
                                    <Link href={route('admin.users.index')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/Users/Index'}
                                        ]}>Users</Link>
                                </NavItem>
                            {/if}
                        {/if}
                        <NavItem>
                            <Link href={route('admin.team-types.index')}
                                class={[
                                    'nav-link',
                                    {active: $page.component == 'Admin/TeamTypes'}
                                ]}>Team Types</Link>
                        </NavItem>
                        {#if
                            can('Edit:Permission') || $page.component.startsWith('Admin/Teams/Roles/') ||
                            ['Admin/Teams/Show', 'Admin/Teams/Edit'].includes($page.component)
                        }
                            <li class="accordion">
                                <button data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavAdminTeam" aria-controls="asideNavAdminTeam"
                                    style="height: 0em" class={[
                                        'nav-item', 'accordion-button',
                                        {collapsed: ! $page.component.startsWith('Admin/Teams/')},
                                    ]}>
                                    Teams
                                </button>
                                <ul id="asideNavAdminTeam" class={[
                                    'accordion-collapse', 'collapse',
                                    {show: $page.component.startsWith('Admin/Teams/')},
                                ]}>
                                    <NavItem>
                                        <Link href={route('admin.teams.index')}
                                            class={[
                                                'nav-link',
                                                {active: $page.component == 'Admin/Teams/Index'}
                                            ]}>Index</Link>
                                    </NavItem>
                                    {#if can('Edit:Permission')}
                                        <NavItem>
                                            <Link href={route('admin.teams.create')}
                                                class={[
                                                    'nav-link',
                                                    {active: $page.component == 'Admin/Teams/Create'}
                                                ]}>Create</Link>
                                        </NavItem>
                                    {/if}
                                    {#if
                                        $page.component.startsWith('Admin/Teams/Roles/') ||
                                        ['Admin/Teams/Show', 'Admin/Teams/Edit'].includes($page.component)
                                    }
                                        <NavItem>
                                            <Link href={route('admin.teams.show', {team: route().params.team})}
                                                class={[
                                                    'nav-link',
                                                    {active: $page.component == 'Admin/Teams/Show'}
                                                ]}>Show</Link>
                                        </NavItem>
                                    {/if}
                                    {#if $page.component == 'Admin/Teams/Edit'}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.teams.edit',
                                                    {team: route().params.team}
                                                )
                                            } class="nav-link active">Edit</Link>
                                        </NavItem>
                                    {/if}
                                    {#if $page.component == 'Admin/Teams/Roles/Create'}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.teams.roles.create',
                                                    {team: route().params.team}
                                                )
                                            } class="nav-link active">Create Role</Link>
                                        </NavItem>
                                    {/if}
                                    {#if $page.component == 'Admin/Teams/Roles/Edit'}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.teams.roles.edit',
                                                    {
                                                        team: route().params.team,
                                                        role: route().params.role,
                                                    }
                                                )
                                            } class="nav-link active">Edit Role</Link>
                                        </NavItem>
                                    {/if}
                                </ul>
                            </li>
                        {:else}
                            <NavItem>
                                <Link href={route('admin.teams.index')}
                                    class={[
                                        'nav-link',
                                        {active: $page.component == 'Admin/Teams/Index'}
                                    ]}>Teams</Link>
                            </NavItem>
                        {/if}
                        <NavItem>
                            <Link href={route('admin.modules.index')}
                                class={[
                                    'nav-link',
                                    {active: $page.component == 'Admin/Modules/Index'}
                                ]}>Module</Link>
                        </NavItem>
                        <NavItem>
                            <Link href={route('admin.permissions.index')}
                            class={[
                                'nav-link',
                                {active: $page.component == 'Admin/Permissions'}
                            ]}>Permission</Link>
                        </NavItem>
                    {/if}
                    {#if can('Edit:Admission Test')}
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavAdminAdmissionTestType" aria-controls="asideNavAdminAdmissionTestType"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! $page.component.startsWith('Admin/AdmissionTest/Types/')},
                                ]}>Admission Test Types</button>
                            <ul id="asideNavAdminAdmissionTestType" class={[
                                'accordion-collapse', 'collapse',
                                {show: $page.component.startsWith('Admin/AdmissionTest/Types/')},
                            ]}>
                                <NavItem>
                                    <Link href={route('admin.admission-test.types.index')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/AdmissionTest/Types/Index'}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.admission-test.types.create')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/AdmissionTest/Types/Create'}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if $page.component == 'Admin/AdmissionTest/Types/Edit'}
                                    <NavItem>
                                        <Link href={
                                            route(
                                                'admin.admission-test.types.edit',
                                                {type: route().params.type}
                                            )
                                        } class="nav-link active">Edit</Link>
                                    </NavItem>
                                {/if}
                            </ul>
                        </li>
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavAdminAdmissionTestProduct" aria-controls="asideNavAdminAdmissionTestProduct"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! $page.component.startsWith('Admin/AdmissionTest/Products/')},
                                ]}>
                                Admission Test Products
                            </button>
                            <ul id="asideNavAdminAdmissionTestProduct" class={[
                                'accordion-collapse', 'collapse',
                                {show: $page.component.startsWith('Admin/AdmissionTest/Products/')},
                            ]}>
                                <NavItem>
                                    <Link href={route('admin.admission-test.products.index')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/AdmissionTest/Products/Index'}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.admission-test.products.create')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/AdmissionTest/Products/Create'}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if $page.component == 'Admin/AdmissionTest/Products/Show'}
                                    <NavItem>
                                        <Link href={
                                            route(
                                                'admin.admission-test.products.show',
                                                {product: route().params.product}
                                            )
                                        } class="nav-link active">Show</Link>
                                    </NavItem>
                                {/if}
                            </ul>
                        </li>
                    {/if}
                    {#if
                        canAny([
                            'Edit:Admission Test',
                            'Edit:Admission Test Proctor',
                            'View:Admission Test Candidate',
                            'Edit:Admission Test Candidate',
                            'View:Admission Test Result',
                            'Edit:Admission Test Result',
                        ]) || $page.props.auth.user.hasProctorTests
                    }
                        {#if
                            ! can('Edit:Admission Test') &&
                            (
                                ! $page.component.startsWith('Admin/AdmissionTests/') ||
                                $page.component == 'Admin/AdmissionTests/Index'
                            )
                        }
                            <NavItem>
                                <Link href={route('admin.admission-tests.index')}
                                    class={[
                                        'nav-link',
                                        {active: $page.component == 'Admin/AdmissionTests/Create'}
                                    ]}>Admission Tests</Link>
                            </NavItem>
                        {:else}
                            <li class="accordion">
                                <button data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavAdminAdmissionTest" aria-controls="asideNavAdminAdmissionTest"
                                    style="height: 0em" class={[
                                        'nav-item', 'accordion-button',
                                        {collapsed: ! $page.component.startsWith('Admin/AdmissionTests/')},
                                    ]}>
                                    Admission Tests
                                </button>
                                <ul id="asideNavAdminAdmissionTest" class={[
                                    'accordion-collapse', 'collapse',
                                    {show: $page.component.startsWith('Admin/AdmissionTests/')},
                                ]}>
                                    <NavItem>
                                        <Link href={route('admin.admission-tests.index')}
                                            class={[
                                                'nav-link',
                                                {active: $page.component == 'Admin/AdmissionTests/Index'}
                                            ]}>Index</Link>
                                    </NavItem>
                                    {#if can('Edit:Admission Test')}
                                        <NavItem>
                                            <Link href={route('admin.admission-tests.create')}
                                                class={[
                                                    'nav-link',
                                                    {active: $page.component == 'Admin/AdmissionTests/Create'}
                                                ]}>Create</Link>
                                        </NavItem>
                                    {/if}
                                    {#if
                                        $page.component == 'Admin/AdmissionTests/Show' ||
                                        $page.component.startsWith('Admin/AdmissionTests/Candidates/')
                                    }
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.admission-tests.candidates.show',
                                                    {admission_test: route().params.admission_test}
                                                )
                                            } class="nav-link active">Show</Link>
                                        </NavItem>
                                    {/if}
                                    {#if $page.component == 'Admin/AdmissionTests/Candidates/Show'}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.admission-tests.candidate.candidates.edit',
                                                    {
                                                        admission_test: route().params.admission_test,
                                                        candidate: route().params.candidate,
                                                    }
                                                )
                                            } class="nav-link active">Show Candidate</Link>
                                        </NavItem>
                                    {/if}
                                    {#if $page.component == 'Admin/AdmissionTests/Candidates/Edit'}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.admission-tests.candidate.show',
                                                    {
                                                        admission_test: route().params.admission_test,
                                                        candidate: route().params.candidate,
                                                    }
                                                )
                                            } class="nav-link active">Edit Candidate</Link>
                                        </NavItem>
                                    {/if}
                                </ul>
                            </li>
                        {/if}
                    {/if}
                    {#if can('Edit:Admission Test Order')}
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavAdminAdmissionTestOrder" aria-controls="asideNavAdminAdmissionTestOrder"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! $page.component.startsWith('Admin/AdmissionTest/Orders/')},
                                ]}>Admission Test Orders</button>
                            <ul id="asideNavAdminAdmissionTestOrder" class={[
                                'accordion-collapse', 'collapse',
                                {show: $page.component.startsWith('Admin/AdmissionTest/Orders/')},
                            ]}>
                                <NavItem>
                                    <Link href={route('admin.admission-test.orders.index')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/AdmissionTest/Orders/Index'}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.admission-test.orders.create')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/AdmissionTest/Orders/Create'}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if $page.component == 'Admin/AdmissionTest/Orders/Show'}
                                    <NavItem>
                                        <Link href={
                                            route(
                                                'admin.admission-test.orders.show',
                                                {order: route().params.order}
                                            )
                                        } class="nav-link active">Show</Link>
                                    </NavItem>
                                {/if}
                            </ul>
                        </li>
                    {/if}
                    {#if can('Edit:Other Payment Gateway')}
                        <NavItem>
                            <Link href={route('admin.other-payment-gateways.index')}
                                class={[
                                    'nav-link',
                                    {active: $page.component == 'Admin/OtherPaymentGateways'}
                                ]}>Other Payment Gateway</Link>
                        </NavItem>
                    {/if}
                    {#if can('Edit:Site Content')}
                        {#if $page.component == 'Admin/SiteContents/Edit'}
                            <li class="accordion">
                                <button data-bs-toggle="collapse" aria-expanded="true"
                                    data-bs-target="#asideNavSiteContent" aria-controls="asideNavSiteContent"
                                    style="height: 0em" class="accordion-button">
                                    Site Content
                                </button>
                                <ul id="asideNavSiteContent" class="accordion-collapse collapse show">
                                    <NavItem>
                                        <Link class="nav-link" href={route('admin.site-contents.index')}>Index</Link>
                                    </NavItem>
                                    <NavItem>
                                        <Link href={
                                            route(
                                                'admin.site-contents.edit',
                                                {site_content: route().params.site_content}
                                            )
                                        } class="nav-link active">Edit</Link>
                                    </NavItem>
                                </ul>
                            </li>
                        {:else}
                            <NavItem>
                                <Link href={route('admin.site-contents.index')}
                                    class={[
                                        'nav-link',
                                        {active: $page.component == 'Admin/SiteContents/Index'}
                                    ]}>Site Content</Link>
                            </NavItem>
                        {/if}
                    {/if}
                    {#if can('Edit:Custom Web Page')}
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavCustomWebPage" aria-controls="asideNavCustomWebPage"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! $page.component.startsWith('Admin/CustomWebPages/')},
                                ]}>
                                Custom Web Pages
                            </button>
                            <ul id="asideNavCustomWebPage" class={[
                                'accordion-collapse', 'collapse',
                                {show: $page.component.startsWith('Admin/CustomWebPages/')},
                            ]}>
                                <NavItem>
                                    <Link href={route('admin.custom-web-pages.index')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/CustomWebPages/Index'}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.custom-web-pages.create')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/CustomWebPages/Create'}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if $page.component == 'Admin/CustomWebPages/Edit'}
                                    <NavItem>
                                        <Link href={
                                            route(
                                                'admin.custom-web-pages.edit',
                                                {custom_web_page: route().params.custom_web_page}
                                            )
                                        } class="nav-link active">Edit</Link>
                                    </NavItem>
                                {/if}
                            </ul>
                        </li>
                    {/if}
                    {#if can('Edit:Navigation Item')}
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavNavigationItem" aria-controls="asideNavNavigationItem"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! $page.component.startsWith('Admin/NavigationItems/')},
                                ]}>Navigation Items</button>
                            <ul id="asideNavNavigationItem" class={[
                                'accordion-collapse', 'collapse',
                                {show: $page.component.startsWith('Admin/NavigationItems/')},
                            ]}>
                                <NavItem>
                                    <Link href={route('admin.navigation-items.index')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/NavigationItems/Index'}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.navigation-items.create')}
                                        class={[
                                            'nav-link',
                                            {active: $page.component == 'Admin/NavigationItems/Create'}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if $page.component == 'Admin/NavigationItems/Edit'}
                                    <NavItem>
                                        <Link href={
                                            route(
                                                'admin.navigation-items.edit',
                                                {navigation_item: route().params.navigation_item}
                                            )
                                        } class="nav-link active">Edit</Link>
                                    </NavItem>
                                {/if}
                            </ul>
                        </li>
                    {/if}
                </Nav>
            </nav>
        </aside>
    {/if}
    <main class="w-100">
        <slot />
    </main>
</Container>
<Alert />
<Confirm />
