@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" class="mx-auto w-25" method="POST" novalidate
            action="{{ route('reset-password') }}">
            <h2 class="fw-bold mb-2 text-uppercase">Forget Password</h2>
            @csrf
            @method('put')
            <div class="mb-4">
                <div class="form-floating">
                    <select class="form-select" id="validationPassportType" name="passport_type_id" required>
                        <option value="" selected disabled>Please select passport type</option>
                        @foreach ($passportTypes as $key => $value)
                            <option value="{{ $key }}" @selected($key == old('passport_type_id'))>{{ $value }}</option>
                        @endforeach
                    </select>
                    <label for="validationPassportType" class="form-label">Passport Type</label>
                    <div id="usernameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="form-floating">
                    <input type="text" name="passport_number" class="form-control" id="validationPassportNumber" minlength="8" maxlength="16" placeholder="Passport Number" required />
                    <label for="validationPassportNumber">Passport Number</label>
                    <div id="passportNumberFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="form-floating">
                    <select class="form-select" id="validationVerifiedContactType" name="verified_contact_type" required>
                        <option value="" selected disabled>Please select Verified Contact Type</option>
                        <option value="email">Email</option>
                        <option value="mobile">Mobile</option>
                    </select>
                    <label for="validationVerifiedContactType" class="form-label">Verified Contact Type</label>
                    <div id="verifiedContactTypeFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="form-floating">
                    <input type="text" name="verified_contact" class="form-control" id="validationVerifiedContact" placeholder="Verified Contact" required />
                    <label for="validationVerifiedContact">Verified Contact</label>
                    <div id="verifiedContactFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="form-floating">
                    <input type="date" name="birthday" class="form-control" id="validationBirthday"  max="{{ $maxBirthday }}" value="{{ old('birthday', $maxBirthday) }}" placeholder="Birthday" required />
                    <label for="validationBirthday">Birthday</label>
                    <div id="birthdayFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <input type="submit" id="resetButton" class="form-control btn btn-primary" value="Reset Password">
                <div class="alert alert-danger" id="resetFeedback" role="alert" hidden></div>
                <button class="form-control btn btn-primary" id="loggingInButton" type="button" disabled hidden>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Resetting...
                </button>
            </div>
            <div class="row mb-4">
                <div class="col d-flex justify-content-center">
                    <a href="{{ route('login') }}" class="form-control btn btn-outline-primary">Login</a>
                </div>
                <div class="col d-flex justify-content-center">
                    <a href="{{ route('register') }}" class="form-control btn btn-outline-success">Register</a>
                </div>
            </div>
        </form>
    </section>
@endsection
