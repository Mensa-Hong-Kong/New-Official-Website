@extends('layouts.app')

@section('main')
    <section class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 to-blue-900"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 text-white md:py-32">
            <h1 class="max-w-2xl text-4xl font-bold md:text-5xl">Welcome to Mensa Hong Kong</h1>
            <p class="mt-6 max-w-2xl text-lg text-white/90">
                Mensa is an international society whose only qualification for membership is a score in the top 2% of the
                general population on an approved intelligence test.
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('test') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8">
                    Take the Test
                </a>
                <a href="{{ route('about') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-11 px-8">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 md:py-24">
        <div class="grid gap-12 md:grid-cols-3">
            <div class="space-y-4">
                <h3 class="text-xl font-semibold">Join Mensa</h3>
                <p class="text-gray-600">
                    Take our supervised IQ test to qualify for Mensa membership. Tests are regularly held in Hong Kong.
                </p>
                <a href="{{ route('join') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 underline-offset-4 hover:underline text-primary">
                    Learn More
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
            <div class="space-y-4">
                <h3 class="text-xl font-semibold">Events & Activities</h3>
                <p class="text-gray-600">
                    Participate in our regular events, from casual gatherings to intellectual discussions and special interest
                    groups.
                </p>
                <a href="{{ route('events') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 underline-offset-4 hover:underline text-primary">
                    View Calendar
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
            <div class="space-y-4">
                <h3 class="text-xl font-semibold">Member Benefits</h3>
                <p class="text-gray-600">
                    Enjoy exclusive benefits including our magazine, special interest groups, and international networking
                    opportunities.
                </p>
                <a href="{{ route('benefits') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 underline-offset-4 hover:underline text-primary">
                    Discover Benefits
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>
    </section>

    <section class="bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-16 md:py-24">
            <div class="grid gap-8 md:grid-cols-2 md:gap-12">
                <div>
                    <h2 class="text-3xl font-bold">About Mensa Hong Kong</h2>
                    <p class="mt-4 text-gray-600">
                        Mensa Hong Kong was founded in 1984 and is part of Mensa International, the high IQ society. Our members
                        come from all walks of life and share one common characteristic: a high IQ in the top 2% of the
                        population.
                    </p>
                    <a href="{{ route('about') }}" class="mt-6 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8">
                        About Us
                    </a>
                </div>
                <div class="aspect-video overflow-hidden rounded-lg bg-gray-100">
                    <img src="{{ asset('images/mensa-event.jpg') }}" alt="Mensa Hong Kong Event" class="h-full w-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 md:py-24">
        <div class="text-center">
            <h2 class="text-3xl font-bold">Latest News & Events</h2>
            <p class="mt-4 text-gray-600">Stay updated with the latest happenings at Mensa Hong Kong</p>
        </div>
        <div class="mt-12 grid gap-8 md:grid-cols-3">
            @foreach(range(1, 3) as $i)
                <div class="group cursor-pointer">
                    <div class="aspect-video overflow-hidden rounded-lg bg-gray-100">
                        <img src="{{ asset('images/event-' . $i . '.jpg') }}" alt="Event {{ $i }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">January {{ $i }}, 2024</p>
                        <h3 class="mt-2 text-lg font-semibold group-hover:text-primary">
                            Monthly Gathering: Intellectual Discussion
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Join us for an evening of stimulating conversation and networking with fellow Mensans.
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection

