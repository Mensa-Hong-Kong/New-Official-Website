@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <form id="resetPassword" method="POST"
                action="{{ route('admin.users.reset-password', ['user' => $user]) }}">
                @csrf
                @method('put')
            </form>
            <form method="POST" class="row g-3" id="form" novalidate>
                @csrf
                @method('put')
                <h3 class="fw-bold mb-2">
                    Info
                        <button class="btn btn-primary" id="savingButton" type="button" disabled hidden>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Saving...
                        </button>
                        <button onclick="return false" class="btn btn-outline-primary" id="editButton">Edit</button>
                        <button type="submit" class="btn btn-outline-primary submitButton" id="saveButton" hidden>Save</button>
                        <button onclick="return false" class="btn btn-outline-danger" id="cancelButton" hidden>Cancel</button>
                </h3>
                <div class="col-md-4">
                    <label for="validationUsername" class="form-label">Username</label>
                    <div id="showUsername" class="showInfo">{{ $user->username }}</div>
                    <input type="text" class="form-control" id="validationUsername" minlength="8" maxlength="16" value="{{ old('username', $user->username) }}" placeholder="username" required hidden />
                    <div id="usernameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-5">
                    <label for="validationPassword" class="form-label">Password</label>
                    <div id="showPassword row">
                        <span class="col-2">********</span>
                        @can('Edit:User')
                            <button id="emailResetPassword" form="resetPassword" name="contact_type" value="email"
                                @disabled(!$user->defaultEmail) @class([
                                    "btn",
                                    "btn-danger" => $user->defaultEmail,
                                    'btn-secondary' => !$user->defaultEmail,
                                    "submitButton",
                                    "resetPassword",
                                    "col-4"
                                ])>
                                Reset by Email
                            </button>
                            <button id="resettingPassword" class="btn btn-danger col-4" hidden disabled>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Resetting...
                            </button>
                            <button id="mobileResetPassword" form="resetPassword" name="contact_type" value="mobile"
                                @disabled(!$user->defaultMobile) @class([
                                    "btn",
                                    "btn-danger" => $user->defaultMobile,
                                    'btn-secondary' => !$user->defaultMobile,
                                    "submitButton",
                                    "resetPassword",
                                    "col-4"
                                ])>
                                Reset by Mobile
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-4">
                    <label class="form-label">Family Name</label>
                    <div>{{ $user->family_name }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Middle Name</label>
                    <div>{{ $user->middle_name }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Given Name</label>
                    <div id="showGivenName">{{ $user->given_name }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Passport Type</label>
                    <div>{{ $user->passportType->name }}</div>
                </div>
                <div class="col-md-4">
                    <label>Passport Number</label>
                    <div>{{ $user->passport_number }}</div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label for="validationGender" class="form-label">Gender</label>
                    <div id="showGender">{{ $user->gender->name }}</div>
                    <input type="text" class="form-control" id="validationGender" list="genders" maxlength="255" value="{{ old('genders', $user->gender->name) }}" placeholder="genders" name="genders" required hidden />
                    <div id="genderFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <x-datalist :id="'genders'" :values="$genders"></x-datalist>
                <div class="col-md-4">
                    <label for="validationBirthday">Date of Birth</label>
                    <div id="showBirthday">{{ $user->birthday }}</div>
                    <input type="date" class="form-control" id="validationBirthday" name="birthday" max="{{ $maxBirthday }}" value="{{ old('birthday', $user->birthday) }}" required hidden />
                    <div id="birthdayFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </form>
        </article>
        <article id="email">
            <h3 class="fw-bold mb-2"><i class="bi bi-envelope"></i> Email</h3>
            @include('admin.users.contacts', ['contacts' => $user->emails, 'type' => 'email', 'userID' => $user->id])
        </article>
        <article id="mobile">
            <h3 class="fw-bold mb-2"><i class="bi bi-phone"></i> Mobile</h3>
            @include('admin.users.contacts', ['contacts' => $user->mobiles, 'type' => 'mobile', 'userID' => $user->id])
        </article>
    </section>
@endsection
@can('Edit:User')
    @push('after footer')
        @vite('resources/js/admin/users/show.js')
    @endpush
@endcan
