<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mensa Hong Kong</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-white">
<header class="sticky top-0 z-50 border-b bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
        <a href="{{ route('index') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/mensa_hk_logo.jpg') }}" alt="Mensa Logo" class="h-20 w-200">
        </a>
        <nav class="hidden items-center gap-8 md:flex">
            <a href="{{ route('about') }}" class="text-sm font-medium hover:text-primary">About Us</a>
            <a href="{{ route('join') }}" class="text-sm font-medium hover:text-primary">Join Mensa</a>
            <a href="{{ route('events') }}" class="text-sm font-medium hover:text-primary">Events</a>
            <a href="{{ route('contact') }}" class="text-sm font-medium hover:text-primary">Contact</a>
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                    Login
                </a>
            @else
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                        Logout
                    </button>
                </form>
            @endguest
        </nav>
        <button class="rounded p-2 hover:bg-gray-100 md:hidden" onclick="toggleMenu()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
    </div>
    <div id="mobileMenu" class="hidden border-t bg-white px-4 py-4 md:hidden">
        <nav class="flex flex-col gap-4">
            <a href="{{ route('about') }}" class="text-sm font-medium hover:text-primary">About Us</a>
            <a href="{{ route('join') }}" class="text-sm font-medium hover:text-primary">Join Mensa</a>
            <a href="{{ route('events') }}" class="text-sm font-medium hover:text-primary">Events</a>
            <a href="{{ route('contact') }}" class="text-sm font-medium hover:text-primary">Contact</a>
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                    Login
                </a>
            @else
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                        Logout
                    </button>
                </form>
            @endguest
        </nav>
    </div>
</header>

<main>
    @yield('main')
</main>

<footer class="border-t bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 py-12">
        <div class="grid gap-8 md:grid-cols-4">
            <div>
                <h3 class="font-semibold">About Mensa</h3>
                <nav class="mt-4 flex flex-col gap-2">
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">What is Mensa?</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">History</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Constitution</a>
                </nav>
            </div>
            <div>
                <h3 class="font-semibold">Membership</h3>
                <nav class="mt-4 flex flex-col gap-2">
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Join Mensa</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Benefits</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">FAQ</a>
                </nav>
            </div>
            <div>
                <h3 class="font-semibold">Resources</h3>
                <nav class="mt-4 flex flex-col gap-2">
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">News</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Events</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Publications</a>
                </nav>
            </div>
            <div>
                <h3 class="font-semibold">Contact</h3>
                <nav class="mt-4 flex flex-col gap-2">
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Get in Touch</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Location</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-primary">Media Enquiries</a>
                </nav>
            </div>
        </div>
        <div class="mt-8 border-t pt-8 text-center text-sm text-gray-600">
            © {{ date('Y') }} Mensa Hong Kong. All rights reserved.
        </div>
    </div>
</footer>

</body>
</html>

