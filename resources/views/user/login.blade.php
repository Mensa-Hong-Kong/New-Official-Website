@extends('layouts.app')

@section('main')
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded shadow-md w-96">
            <h2 class="text-2xl font-bold mb-4">Login</h2>
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label for="username" class="block font-medium text-sm text-gray-700">Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="mt-4">
                    <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                    <input id="password" type="password" name="password" required
                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="mt-4">
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

