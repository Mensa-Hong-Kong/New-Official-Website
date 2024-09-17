@extends('layouts.app')

@section('main')
    <form method="POST" class="container">
        @csrf
        <h2 class="fw-bold mb-2 text-uppercase">Register</h2>
        <div class="form-floating">
            <input type="text" class="form-control" id="floatingUsername" aria-describedby="usernameFeedback" placeholder="username" name="username" />
            <label for="floatingUsername">Usermame</label>
            <div id="usernameFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingPassword" aria-describedby="passwordFeedback" placeholder="password" name="password" />
            <label for="floatingPassword">Password</label>
            <div id="passwordFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingConfirmPassword" aria-describedby="confirmPPasswordFeedback" placeholder="password" name="password_confirmation" />
            <label for="floatingConfirmPassword">Confirm Password</label>
            <div id="confirmPPasswordFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingFamilyName" aria-describedby="familyNameFeedback" placeholder="familyName" name="family_name" />
            <label for="floatingFamilyName">Family Name</label>
            <div id="familyNameFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingMiddleName" aria-describedby="middleNameFeedback" placeholder="middleName" name="middle_name" />
            <label for="floatingMiddleName">Middle Name</label>
            <div id="middleNameFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingGivenName" aria-describedby="givenNameFeedback" placeholder="givenName" name="given_name" />
            <label for="floatingGivenName">Given Name</label>
            <div id="givenNameFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="text" class="form-control" id="floatingGender" list="genders" aria-describedby="genderFeedback" placeholder="gender" name="gender" />
            <label for="floatingGender">Gender</label>
            <div id="genderFeedback" class="invalid-feedback"></div>
        </div>
        <x-datalist :id="'genders'" :values="$genders"></x-datalist>
        <div class="form-floating">
            <select class="form-select" id="floatingPassportType" aria-describedby="passportTypeFeedback" aria-label="Floating label select passport type">
                <option selected disabled>Please select passport type</option>
                @foreach ($passportTypes as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            <label for="floatingPassportType">Passport Type</label>
            <div id="passportTypeFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="text" class="form-control" id="floatingPassportNumber" aria-describedby="passportNumberFeedback" placeholder="passport_number" name="passport_number" />
            <label for="floatingPassportNumber">Passport Number</label>
            <div id="passportNumberFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="date" class="form-control" id="floatingBirthday" aria-describedby="birthdayFeedback" placeholder="birthday" name="birthday" />
            <label for="floatingBirthday">Date of Birth</label>
            <div id="birthdayFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="email" class="form-control" id="floatingEmail" aria-describedby="emailFeedback" placeholder="email" name="email" />
            <label for="floatingEmail">E-mail</label>
            <div id="emailFeedback" class="invalid-feedback"></div>
        </div>
        <div class="form-floating">
            <input type="tel" class="form-control" id="floatingMobile" aria-describedby="mobileFeedback" placeholder="mobile" name="mobile" />
            <label for="floatingMobile">Mobile</label>
            <div id="mobileFeedback" class="invalid-feedback"></div>
        </div>
        <input type="submit" class="form-control btn btn-primary">
    </form>
@endsection
