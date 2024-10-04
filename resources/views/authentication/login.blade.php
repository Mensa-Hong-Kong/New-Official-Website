@extends('layouts.app')

@section('main')
    <section class="container">
        <form method="POST" id="form" class="form-outline mx-auto w-25" novalidate>
            @csrf
            <h2 class="fw-bold mb-2 text-uppercase">Login</h2>
            <div class="form-floating">
                <input type="text" name="username" class="form-control" id="validationUsername" minlength="8" maxlength="16" placeholder="username" required />
                <label for="validationUsername">Username</label>
                <div id="usernameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="form-floating">
                <input type="text" name="password" class="form-control" id="validationPassword" minlength="8" maxlength="16" placeholder="password" required />
                <label for="validationPassword">Password</label>
                <div id="passwordFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="form-check form-control">
                <input class="form-check-input" type="checkbox" value="true" id="rememberMe" name="remember_me" />
                <label class="form-check-label" for="remeberMe">Remember Me</label>
            </div>
            <input type="submit" id="loginButton" class="form-control btn btn-primary" value="Login">
            <div class="alert alert-danger" id="loginFeedback" role="alert" hidden></div>
            <button class="form-control btn btn-primary" id="loggingInButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Logging In...
            </button>
            <div class="form-control text-center">
            <p>Not a member? <a href="{{ route('register') }}">Register</a></p>
            </div>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/login.js')
@endpush
