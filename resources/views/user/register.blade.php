@extends('layouts.app')

@section('main')
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-2xl mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold">{{ __('Register') }}</h2>
            </div>

            <!-- Hint Box -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-semibold text-blue-800 mb-2">Important Information:</h3>
                <ol class="list-decimal list-inside text-sm text-blue-700 space-y-2">
                    <li>
                        Passport number format:
                        <ul class="pl-5 mt-1 space-y-1 list-disc list-inside">
                            <li>Example 1: A123456(7) should be entered as A1234567</li>
                            <li>Example 2: 1234567(8) should be entered as 12345678</li>
                        </ul>
                    </li>
                    <li>The family name, middle name, given name and gender must match your passport</li>
                    <li>Mobile number must include country code without "+" and "-" (e.g., 85298765432)</li>
                </ol>
            </div>

            @if ($errors->any())
                <div class="mb-4">
                    <div class="font-medium text-red-600">
                        {{ __('Whoops! Something went wrong.') }}
                    </div>

                    <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="form" novalidate>
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Username -->
                    <div>
                        <label for="validationUsername" class="block font-medium text-sm text-gray-700">
                            {{ __('Username') }}
                        </label>
                        <input id="validationUsername" type="text" name="username" value="{{ old('username') }}"
                               minlength="8" maxlength="16" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="validationPassword" class="block font-medium text-sm text-gray-700">
                            {{ __('Password') }}
                        </label>
                        <input id="validationPassword" type="password" name="password"
                               minlength="8" maxlength="16" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirmPassword" class="block font-medium text-sm text-gray-700">
                            {{ __('Confirm Password') }}
                        </label>
                        <input id="confirmPassword" type="password" name="password_confirmation"
                               minlength="8" maxlength="16" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Family Name -->
                    <div>
                        <label for="validationFamilyName" class="block font-medium text-sm text-gray-700">
                            {{ __('Family Name') }}
                        </label>
                        <input id="validationFamilyName" type="text" name="family_name" value="{{ old('family_name') }}"
                               maxlength="255" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Middle Name -->
                    <div>
                        <label for="validationMiddleName" class="block font-medium text-sm text-gray-700">
                            {{ __('Middle Name') }}
                        </label>
                        <input id="validationMiddleName" type="text" name="middle_name" value="{{ old('middle_name') }}"
                               maxlength="255"
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Given Name -->
                    <div>
                        <label for="validationGivenName" class="block font-medium text-sm text-gray-700">
                            {{ __('Given Name') }}
                        </label>
                        <input id="validationGivenName" type="text" name="given_name" value="{{ old('given_name') }}"
                               maxlength="255" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Passport Type -->
                    <div>
                        <label for="validationPassportType" class="block font-medium text-sm text-gray-700">
                            {{ __('Passport Type') }}
                        </label>
                        <select id="validationPassportType" name="passport_type_id" required
                                class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="" selected disabled>Please select passport type</option>
                            @foreach ($passportTypes as $key => $value)
                                <option value="{{ $key }}" @selected($key == old('passport_type_id'))>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Passport Number -->
                    <div>
                        <label for="validationPassportNumber" class="block font-medium text-sm text-gray-700">
                            {{ __('Passport Number') }}
                        </label>
                        <input id="validationPassportNumber" type="text" name="passport_number" value="{{ old('passport_number') }}"
                               minlength="8" maxlength="18" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="validationGender" class="block font-medium text-sm text-gray-700">
                            {{ __('Gender') }}
                        </label>
                        <input id="validationGender" type="text" name="gender" value="{{ old('gender') }}"
                               list="genders" maxlength="255" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <datalist id="genders">
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}">
                            @endforeach
                        </datalist>
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="validationBirthday" class="block font-medium text-sm text-gray-700">
                            {{ __('Date of Birth') }}
                        </label>
                        <input id="validationBirthday" type="date" name="birthday"
                               max="{{ $maxBirthday }}" value="{{ old('birthday', $maxBirthday) }}" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="validationEmail" class="block font-medium text-sm text-gray-700">
                            {{ __('Email') }}
                        </label>
                        <input id="validationEmail" type="email" name="email" value="{{ old('email') }}"
                               maxlength="320" placeholder="example@example.com" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <!-- Mobile -->
                    <div>
                        <label for="validationMobile" class="block font-medium text-sm text-gray-700">
                            {{ __('Mobile') }}
                        </label>
                        <input id="validationMobile" type="tel" name="mobile" value="{{ old('mobile') }}"
                               minlength="5" maxlength="15" placeholder="85298765432" required
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <button type="submit" id="submitButton"
                            class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <span class="inline-flex items-center">
                            <span id="buttonText">{{ __('Register') }}</span>
                            <span id="loadingSpinner" class="hidden ml-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('after footer')
    @vite('resources/js/user/register.js')
@endpush

