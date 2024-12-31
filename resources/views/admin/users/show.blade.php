@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <form method="POST" class="row g-3" id="form" novalidate>
                <h3 class="fw-bold mb-2">
                    Info
                </h3>
                <div class="alert alert-primary" id="editInfoRemind" role="alert" hidden>
                    <ol>
                        <li>Password only require when you change the username or password</li>
                        <li>New password and confirm password is not require unless you want to change a new password</li>
                    </ol>
                </div>
                @csrf
                @method('put')
                <div class="col-md-4">
                    <label for="validationUsername" class="form-label">Usermame</label>
                    <div id="showUsername" class="showInfo">{{ $user->username }}</div>
                </div>
                <div class="col-md-4">
                    <label for="validationPassword" class="form-label">Password</label>
                    <div id="showPassword">********</div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label for="validationFamilyName" class="form-label">Family Name</label>
                    <div id="showFamilyName">{{ $user->family_name }}</div>
                </div>
                <div class="col-md-4">
                    <label for="validationMiddleName" class="form-label">Middle Name</label>
                    <div id="showMiddleName">{{ $user->middle_name }}</div>
                </div>
                <div class="col-md-4">
                    <label for="validationGivenName" class="form-label">Given Name</label>
                    <div id="showGivenName">{{ $user->given_name }}</div>
                </div>
                <div class="col-md-4">
                    <label for="validationPassportType" class="form-label">Passport Type</label>
                    <div id="showPassportType">{{ $user->passportType->name }}</div>
                </div>
                <div class="col-md-4">
                    <label for="validationPassportNumber">Passport Number</label>
                    <div id="showPassportNumber">{{ $user->passport_number }}</div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label for="validationGender" class="form-label">Gender</label>
                    <div id="showGender">{{ $user->gender->name }}</div>
                </div>
                <div class="col-md-4">
                    <label for="validationBirthday">Date of Birth</label>
                    <div id="showBirthday">{{ $user->birthday }}</div>
                </div>
            </form>
        </article>
        <article id="email">
            <h3 class="fw-bold mb-2"><i class="bi bi-envelope"></i> Email</h3>
            @foreach ($user->emails as $email)
                <div class="row">
                    <div class="col">{{ $email->contact }}</div>
                </div>
            @endforeach
        </article>
        <article id="mobile">
            <h3 class="fw-bold mb-2"><i class="bi bi-phone"></i> Mobile</h3>
            @foreach ($user->mobiles as $mobile)
                <div class="row">
                    <div class="col">{{ $mobile->contact }}</div>
                </div>
            @endforeach
        </article>
    </section>
@endsection
