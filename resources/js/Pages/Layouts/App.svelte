<script>
    import { page, Link } from "@inertiajs/svelte";
    import { Navbar, NavbarBrand, NavbarToggler, Collapse, Nav, NavLink, Dropdown, DropdownToggle, Container, NavItem } from '@sveltestrap/sveltestrap';
	import NavDropdown from '@/Pages/Components/NavDropdown.svelte';
	import Alert, { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import Confirm from '@/Pages/Components/Modals/Confirm.svelte';
	import { setCsrfToken } from '@/submitForm.svelte';

    let { children } = $props();
    let isOpenNav = $state(false);

    function handleNavUpdate(event) {
        isOpenNav = event.detail.isOpenNav;
    }

    function navToggle() {
        isOpenNav = !isOpenNav;
    }

    setCsrfToken($page.props.csrf_token);

    if ($page.props.flash.success) {
        alert($page.props.flash.success);
    }
    if ($page.props.flash.error) {
        alert($page.props.flash.error);
    }
</script>

<header>
    <Navbar color="dark" theme="dark" expand="lg" container="xxl" sticky="top" pills fixed="wrap">
        <button class="navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#bdSidebar" aria-label="Toggle admin navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <NavbarBrand href={route('index')} class="me-auto">Mensa</NavbarBrand>
        <NavbarToggler on:click={navToggle} />
        <Collapse {isOpenNav} navbar expand="md" on:update={handleNavUpdate}>
            <Nav class="me-auto" navbar>
                {#each $page.props.navigationNodes.root as itemID}
                    {#if $page.props.navigationNodes[itemID].length}
                        <Dropdown nav inNavbar>
                            <DropdownToggle nav caret>{$page.props.navigationItems[itemID]['name']}</DropdownToggle>
                            <NavDropdown nodes={$page.props.navigationNodes}
                                items={$page.props.navigationItems} id={itemID} />
                        </Dropdown>
                    {:else}
                        <NavLink href={$page.props.navigationItems[itemID]['url'] ?? '#'}>
                            {$page.props.navigationItems[itemID]['name']}
                        </NavLink>
                    {/if}
                {/each}
            </Nav>
            <hr class="d-lg-none text-white-50">
            <Nav class="ms-auto" navbar>
                {#if $page.props.auth.user}
                    {#if
                        $page.props.auth.user.hasProctorTests ||
                        $page.props.auth.user.permissions.length ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <Link href={
                            $page.props.auth.user.permissions.length ||
                            $page.props.auth.user.roles.includes('Super Administrator') ?
                            route('admin.index') :
                            route('admin.admission-tests.index')
                        } class={[
                            'nav-link',
                            {active: route().current().startsWith('admin.')}
                        ]}>Admin</Link>
                        <hr class="d-lg-none text-white-50">
                    {/if}
                    <NavLink href={route('profile.show')}
                        class={[
                            'nav-link',
                            {active: route().current('profile.show')}
                        ]}>Profile</NavLink>
                    <Link href={route('logout')} class="nav-link">Logout</Link>
                {:else}
                    <Link href={route('login')}
                        class={[
                            'nav-link',
                            {active: route().current('login')}
                        ]}>Login</Link>
                    <Link href={route('register')}
                        class={[
                            'nav-link',
                            {active: route().current('register')}
                        ]}>Register</Link>
                {/if}
            </Nav>
        </Collapse>
    </Navbar>
</header>
<Container xxl class={{'d-flex': route().current().startsWith('admin.')}}>
    {#if route().current().startsWith('admin.')}
        <aside class="offcanvas-lg offcanvas-start" tabindex="-1" id="bdSidebar" aria-labelledby="bdSidebarOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 id="bdSidebarOffcanvasLabel">Admin</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#bdSidebar"></button>
            </div>
            <nav class="offcanvas-body">
                <Nav vertical pills class="offcanvas-body">
                    {#if
                        $page.props.auth.user.permissions.length ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <NavItem>
                            <Link href={route('admin.index')}
                                class={[
                                    'nav-link',
                                    {active: route().current('admin.index')}
                                ]}>Dashboard</Link>
                        </NavItem>
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
                                            {active: route().current('admin.users.index')}
                                        ]}>Users</Link>
                                </NavItem>
                            {/if}
                        {/if}
                        <NavItem>
                            <Link href={route('admin.team-types.index')}
                                class={[
                                    'nav-link',
                                    {active: route().current('admin.team-types.index')}
                                ]}>Team Types</Link>
                        </NavItem>
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
                                    <NavItem>
                                        <Link href={route('admin.teams.index')}
                                            class={[
                                                'nav-link',
                                                {active: route().current('admin.teams.index')}
                                            ]}>Index</Link>
                                    </NavItem>
                                    {#if
                                        $page.props.auth.user.permissions.includes('Edit:Permission') ||
                                        $page.props.auth.user.roles.includes('Super Administrator')
                                    }
                                        <NavItem>
                                            <Link href={route('admin.teams.create')}
                                                class={[
                                                    'nav-link',
                                                    {active: route().current('admin.teams.create')}
                                                ]}>Create</Link>
                                        </NavItem>
                                    {/if}
                                    {#if
                                        route().current().startsWith('admin.teams.roles.') ||
                                        ['admin.teams.show', 'admin.teams.edit'].includes(route().current())
                                    }
                                        <NavItem>
                                            <Link href={route('admin.teams.show', {team: route().params.team})}
                                                class={[
                                                    'nav-link',
                                                    {active: route().current('admin.teams.show')}
                                                ]}>Show</Link>
                                        </NavItem>
                                    {/if}
                                    {#if route().current('admin.teams.edit')}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.teams.edit',
                                                    {team: route().params.team}
                                                )
                                            } class="nav-link active">Edit</Link>
                                        </NavItem>
                                    {/if}
                                    {#if route().current('admin.teams.roles.create')}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.teams.roles.create',
                                                    {team: route().params.team}
                                                )
                                            } class="nav-link active">Create Role</Link>
                                        </NavItem>
                                    {/if}
                                    {#if route().current('admin.teams.roles.edit')}
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
                                        {active: route().current('admin.teams.index')}
                                    ]}>Teams</Link>
                            </NavItem>
                        {/if}
                        <NavItem>
                            <Link href={route('admin.modules.index')}
                                class={[
                                    'nav-link',
                                    {active: route().current('admin.modules.index')}
                                ]}>Module</Link>
                        </NavItem>
                        <NavItem>
                            <Link href={route('admin.permissions.index')}
                            class={[
                                'nav-link',
                                {active: route().current('admin.permissions.index')}
                            ]}>Permission</Link>
                        </NavItem>
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
                                <NavItem>
                                    <Link href={route('admin.admission-test.types.index')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.admission-test.types.index')}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.admission-test.types.create')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.admission-test.types.create')}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if route().current('admin.admission-test.types.edit')}
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
                                    {collapsed: ! route().current().startsWith('admin.admission-test.products.')},
                                ]}>
                                Admission Test Products
                            </button>
                            <ul id="asideNavAdminAdmissionTestProduct" class={[
                                'accordion-collapse', 'collapse',
                                {show: route().current().startsWith('admin.admission-test.products.')},
                            ]}>
                                <NavItem>
                                    <Link href={route('admin.admission-test.products.index')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.admission-test.products.index')}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.admission-test.products.create')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.admission-test.products.create')}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if route().current('admin.admission-test.products.show')}
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
                            <NavItem>
                                <Link href={route('admin.admission-tests.index')}
                                    class={[
                                        'nav-link',
                                        {active: route().current('admin.admission-tests.create')}
                                    ]}>Admission Tests</Link>
                            </NavItem>
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
                                    <NavItem>
                                        <Link href={route('admin.admission-tests.index')}
                                            class={[
                                                'nav-link',
                                                {active: route().current('admin.admission-tests.index')}
                                            ]}>Index</Link>
                                    </NavItem>
                                    {#if
                                        $page.props.auth.user.permissions.includes('Edit:Admission Test') ||
                                        $page.props.auth.user.roles.includes('Super Administrator')
                                    }
                                        <NavItem>
                                            <Link href={route('admin.admission-tests.create')}
                                                class={[
                                                    'nav-link',
                                                    {active: route().current('admin.admission-tests.create')}
                                                ]}>Create</Link>
                                        </NavItem>
                                    {/if}
                                    {#if route().current('admin.admission-tests.show')}
                                        <NavItem>
                                            <Link href={
                                                route(
                                                    'admin.admission-tests.show',
                                                    {admission_test: route().params.admission_test}
                                                )
                                            } class="nav-link active">Show</Link>
                                        </NavItem>
                                    {/if}
                                </ul>
                            </li>
                        {/if}
                    {/if}
                    {#if
                        $page.props.auth.user.permissions.includes('Edit:Admission Test Order') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <li class="accordion">
                            <button data-bs-toggle="collapse" aria-expanded="true"
                                data-bs-target="#asideNavAdminAdmissionTestOrder" aria-controls="asideNavAdminAdmissionTestOrder"
                                style="height: 0em" class={[
                                    'nav-item', 'accordion-button',
                                    {collapsed: ! route().current().startsWith('admin.admission-test.orders.')},
                                ]}>Admission Test Orders</button>
                            <ul id="asideNavAdminAdmissionTestOrder" class={[
                                'accordion-collapse', 'collapse',
                                {show: route().current().startsWith('admin.admission-test.orders.')},
                            ]}>
                                <NavItem>
                                    <Link href={route('admin.admission-test.orders.create')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.admission-test.orders.create')}
                                        ]}>Create</Link>
                                </NavItem>
                            </ul>
                        </li>
                    {/if}
                    {#if
                        $page.props.auth.user.permissions.includes('Edit:Other Payment Gateway') ||
                        $page.props.auth.user.roles.includes('Super Administrator')
                    }
                        <NavItem>
                            <Link href={route('admin.other-payment-gateways.index')}
                                class={[
                                    'nav-link',
                                    {active: route().current('admin.other-payment-gateways.index')}
                                ]}>Other Payment Gateway</Link>
                        </NavItem>
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
                                    <NavItem>
                                        <Link href={route('admin.site-contents.index')}>Index</Link>
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
                                        {active: route().current('admin.site-contents.index')}
                                    ]}>Site Content</Link>
                            </NavItem>
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
                                <NavItem>
                                    <Link href={route('admin.custom-web-pages.index')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.custom-web-pages.index')}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.custom-web-pages.create')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.custom-web-pages.create')}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if route().current('admin.custom-web-pages.edit')}
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
                                <NavItem>
                                    <Link href={route('admin.navigation-items.index')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.navigation-items.index')}
                                        ]}>Index</Link>
                                </NavItem>
                                <NavItem>
                                    <Link href={route('admin.navigation-items.create')}
                                        class={[
                                            'nav-link',
                                            {active: route().current('admin.navigation-items.create')}
                                        ]}>Create</Link>
                                </NavItem>
                                {#if route().current('admin.navigation-items.edit')}
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
        {@render children?.()}
    </main>
</Container>
<Alert />
<Confirm />
