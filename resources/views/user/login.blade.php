@extends('layouts.app')

@section('main')
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="username" class="block font-medium text-sm text-gray-700">Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="mb-4">
                    <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                    <input id="password" type="password" name="password" required
                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember Me</label>
                </div>

                <div class="mb-6">
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Login
                    </button>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <a href="{{ route('forget-password') }}" class="text-sm text-blue-500 hover:text-blue-700">Forgot Password?</a>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    Not a member?
                    <a href="{{ route('register') }}" class="font-medium text-blue-500 hover:text-blue-700">Register</a>
                </p>
            </div>
        </div>
    </div>
@endsection

