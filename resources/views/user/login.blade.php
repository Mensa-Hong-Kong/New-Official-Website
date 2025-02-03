@extends('layouts.app')

@section('main')
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                @if ($errors->has('failed'))
                    <div class="mb-4 text-red-500">
                        {{ $errors->first('failed') }}
                    </div>
                @endif

                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('failed') border-red-500 @enderror">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input id="password" type="password" name="password" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline @error('failed') border-red-500 @enderror">
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="remember" class="form-checkbox">
                        <span class="ml-2 text-sm text-gray-600">Remember Me</span>
                    </label>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Login
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="{{ route('forget-password') }}">
                        Forgot Password?
                    </a>
                </div>
            </form>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Not a member?
                    <a href="{{ route('register') }}" class="font-bold text-blue-500 hover:text-blue-800">Register</a>
                </p>
            </div>
        </div>
    </div>
@endsection

