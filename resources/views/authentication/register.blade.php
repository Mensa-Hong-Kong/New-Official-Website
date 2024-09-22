@extends('layouts.app')

@section('main')
    <section class="container">
        <div class="alert alert-primary" role="alert">
            <ol>
                <li>
                    Passport number include inside brackets number but withour all symbol<br>
                    Example 1: A123456(7) should type A1234567
                    Example 1: 1234567(8) should type 12345678
                </li>
                <li>The family name, middle name, given name and gender must match passport</li>
                <li>Mobile number include country code without "+" and "-"</li>
            </ol>
        </div>
        <form method="POST" class="row g-3" id="form" novalidate>
            @csrf
            <h2 class="fw-bold mb-2 text-uppercase">Register</h2>
            <div class="col-md-4">
                <label for="validationUsername" class="form-label">Usermame</label>
                <input type="text" class="form-control" id="validationUsername" minlength="8" maxlength="16" value="{{ old('username') }}" placeholder="username" required>
                <div id="usernameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="col-md-4">
                <label for="validationPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="validationPassword" minlength="8" maxlength="16" placeholder="password" name="password" required>
                <div id="passwordFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="col-md-4">
                <label for="validationConfirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="validationConfirmPassword" minlength="8" maxlength="16" placeholder="confirm password" name="password_confirmation" required>
                <div id="confirmPasswordFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="col-md-4">
                <label for="validationFamilyName" class="form-label">Family Name</label>
                <input type="text" class="form-control" id="validationFamilyName" maxlength="255" value="{{ old('family_name') }}" placeholder="family name" name="family_name" required>
                <div id="familyNameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="col-md-4">
                <label for="validationMiddleName" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="validationMiddleName" maxlength="255" value="{{ old('middle_name') }}" placeholder="middle name" name="middle_name">
                <div id="middleNameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="col-md-4">
                <label for="validationGivenName" class="form-label">Given Name</label>
                <input type="text" class="form-control" id="validationGivenName" maxlength="255" value="{{ old('given_name') }}" placeholder="given name" name="given_name" required>
                <div id="givenNameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="col-md-4">
                <label for="validationPassportType" class="form-label">Passport Type</label>
                <select class="form-select" id="validationPassportType" name="passport_type_id" required>
                    <option value="" selected disabled>Please select passport type</option>
                    @foreach ($passportTypes as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                <div id="passportTypeFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="col-md-4">
                <label for="validationPassportNumber">Passport Number</label>
                <input type="text" class="form-control" id="validationPassportNumber" minlength="8" maxlength="18" placeholder="passport_number" name="passport_number" required />
                <div id="passportNumberFeedback" class="valid-feedback"></div>
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <label for="validationGender" class="form-label">Gender</label>
                <input type="text" class="form-control" id="validationGender" list="genders" maxlength="255" value="{{ old('genders') }}" placeholder="genders" name="genders" required>
                <div id="genderFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <x-datalist :id="'genders'" :values="$genders"></x-datalist>
            <div class="col-md-4">
                <label for="validationBirthday">Date of Birth</label>
                <input type="date" class="form-control" id="validationBirthday" name="birthday" max="{{ $maxBirthday }}" value="{{ old('birthday', $maxBirthday) }}" required />
                <div id="birthdayFeedback" class="valid-feedback"></div>
            </div>
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <label for="validationEmail">Email</label>
                <input type="email" class="form-control" id="validationEmail" maxlength="320" placeholder="dammy@example.com" name="email" value="{{ old('email') }}" required />
                <div id="emailFeedback" class="valid-feedback"></div>
            </div>
            <div class="col-md-4">
                <label for="validationMobile">Mobile</label>
                <input type="tel" class="form-control" id="validationMobile" minlength="5" maxlength="15" placeholder="85298765432" name="mobile" value="{{ old('mobile') }}" required />
                <div id="mobileFeedback" class="valid-feedback"></div>
            </div>
            <input type="submit" id="submitButton" class="form-control btn btn-primary" value="Submit">
            <button class="form-control btn btn-primary" id="submittingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Submitting...
            </button>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/register.js')
@endpush
