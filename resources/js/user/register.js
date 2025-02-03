import ClearInputHistory from "../clearInputHistory"
import { post } from "../submitForm"

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form")
    const username = document.getElementById("validationUsername")
    const usernameFeedback = document.getElementById("usernameFeedback")
    const password = document.getElementById("validationPassword")
    const passwordFeedback = document.getElementById("passwordFeedback")
    const confirmPassword = document.getElementById("confirmPassword")
    const familyName = document.getElementById("validationFamilyName")
    const familyNameFeedback = document.getElementById("familyNameFeedback")
    const middleName = document.getElementById("validationMiddleName")
    const middleNameFeedback = document.getElementById("middleNameFeedback")
    const givenName = document.getElementById("validationGivenName")
    const givenNameFeedback = document.getElementById("givenNameFeedback")
    const passportType = document.getElementById("validationPassportType")
    const passportTypeFeedback = document.getElementById("passportTypeFeedback")
    const passportNumber = document.getElementById("validationPassportNumber")
    const passportNumberFeedback = document.getElementById("passportNumberFeedback")
    const gender = document.getElementById("validationGender")
    const genderFeedback = document.getElementById("genderFeedback")
    const birthday = document.getElementById("validationBirthday")
    const birthdayFeedback = document.getElementById("birthdayFeedback")
    const email = document.getElementById("validationEmail")
    const emailFeedback = document.getElementById("emailFeedback")
    const mobile = document.getElementById("validationMobile")
    const mobileFeedback = document.getElementById("mobileFeedback")
    const submitButton = document.getElementById("submitButton")
    const buttonText = document.getElementById("buttonText")
    const loadingSpinner = document.getElementById("loadingSpinner")

    const inputs = [
        username,
        password,
        confirmPassword,
        familyName,
        middleName,
        givenName,
        passportType,
        passportNumber,
        gender,
        birthday,
        email,
        mobile,
    ]

    new ClearInputHistory(inputs)

    const feedbacks = [
        usernameFeedback,
        passwordFeedback,
        familyNameFeedback,
        middleNameFeedback,
        givenNameFeedback,
        passportTypeFeedback,
        passportNumberFeedback,
        genderFeedback,
        birthdayFeedback,
        emailFeedback,
        mobileFeedback,
    ]

    function hasError() {
        return feedbacks.some((feedback) => feedback.className === "invalid-feedback")
    }

    function validation() {
        inputs.forEach((input) => {
            input.classList.remove("is-valid", "is-invalid")
        })
        feedbacks.forEach((feedback) => {
            feedback.className = "valid-feedback"
            feedback.innerText = "Looks good!"
        })

        if (username.validity.valueMissing) {
            setInvalid(username, usernameFeedback, "The username field is required.")
        } else if (username.validity.tooShort) {
            setInvalid(username, usernameFeedback, `The username field must be at least ${username.minLength} characters.`)
        } else if (username.validity.tooLong) {
            setInvalid(
                username,
                usernameFeedback,
                `The username field must not be greater than ${username.maxLength} characters.`,
            )
        }

        if (password.validity.valueMissing) {
            setInvalid(password, passwordFeedback, "The password field is required.")
        } else if (password.validity.tooShort) {
            setInvalid(password, passwordFeedback, `The password field must be at least ${password.minLength} characters.`)
        } else if (password.validity.tooLong) {
            setInvalid(
                password,
                passwordFeedback,
                `The password field must not be greater than ${password.maxLength} characters.`,
            )
        } else if (password.value !== confirmPassword.value) {
            setInvalid(password, passwordFeedback, "The password confirmation does not match.")
            setInvalid(confirmPassword, passwordFeedback, "The password confirmation does not match.")
        }

        if (familyName.validity.valueMissing) {
            setInvalid(familyName, familyNameFeedback, "The family name field is required.")
        } else if (familyName.validity.tooLong) {
            setInvalid(
                familyName,
                familyNameFeedback,
                `The family name must not be greater than ${familyName.maxLength} characters.`,
            )
        }

        if (middleName.value && middleName.validity.tooLong) {
            setInvalid(
                middleName,
                middleNameFeedback,
                `The middle name must not be greater than ${middleName.maxLength} characters.`,
            )
        }

        if (givenName.validity.valueMissing) {
            setInvalid(givenName, givenNameFeedback, "The given name field is required.")
        } else if (givenName.validity.tooLong) {
            setInvalid(
                givenName,
                givenNameFeedback,
                `The given name must not be greater than ${givenName.maxLength} characters.`,
            )
        }

        if (passportType.validity.valueMissing) {
            setInvalid(passportType, passportTypeFeedback, "The passport type field is required.")
        }

        if (passportNumber.validity.valueMissing) {
            setInvalid(passportNumber, passportNumberFeedback, "The passport number field is required.")
        } else if (passportNumber.validity.tooShort) {
            setInvalid(
                passportNumber,
                passportNumberFeedback,
                `The passport number must be at least ${passportNumber.minLength} characters.`,
            )
        } else if (passportNumber.validity.tooLong) {
            setInvalid(
                passportNumber,
                passportNumberFeedback,
                `The passport number must not be greater than ${passportNumber.maxLength} characters.`,
            )
        }

        if (gender.validity.valueMissing) {
            setInvalid(gender, genderFeedback, "The gender field is required.")
        } else if (gender.validity.tooLong) {
            setInvalid(gender, genderFeedback, `The gender must not be greater than ${gender.maxLength} characters.`)
        }

        if (birthday.validity.valueMissing) {
            setInvalid(birthday, birthdayFeedback, "The birthday field is required.")
        } else if (birthday.validity.rangeOverflow) {
            setInvalid(birthday, birthdayFeedback, `The birthday must not be greater than ${birthday.max}.`)
        }

        if (email.value) {
            if (email.validity.tooLong) {
                setInvalid(email, emailFeedback, `The email must not be greater than ${email.maxLength} characters.`)
            } else if (email.validity.typeMismatch) {
                setInvalid(email, emailFeedback, "The email must be a valid email address.")
            }
        }

        if (mobile.value) {
            if (mobile.validity.tooShort) {
                setInvalid(mobile, mobileFeedback, `The mobile must be at least ${mobile.minLength} characters.`)
            } else if (mobile.validity.tooLong) {
                setInvalid(mobile, mobileFeedback, `The mobile must not be greater than ${mobile.maxLength} characters.`)
            } else if (mobile.validity.typeMismatch) {
                setInvalid(mobile, mobileFeedback, "The mobile must be a valid phone number.")
            }
        }

        inputs.forEach((input) => {
            if (!input.classList.contains("is-invalid")) {
                input.classList.add("is-valid")
            }
        })

        return !hasError()
    }

    function setInvalid(input, feedback, message) {
        input.classList.add("is-invalid")
        feedback.className = "invalid-feedback"
        feedback.innerText = message
    }

    function successCallback(response) {
        window.location.href = response.request.responseURL
    }

    function failCallback(error) {
        inputs.forEach((input) => input.classList.remove("is-valid"))
        feedbacks.forEach((feedback) => {
            feedback.className = "valid-feedback"
            feedback.innerText = "Looks good!"
        })

        if (error.status === 422) {
            for (const key in error.response.data.errors) {
                const value = error.response.data.errors[key]
                let input, feedback

                switch (key) {
                    case "username":
                        input = username
                        feedback = usernameFeedback
                        break
                    case "password":
                        input = password
                        feedback = passwordFeedback
                        break
                    case "family_name":
                        input = familyName
                        feedback = familyNameFeedback
                        break
                    case "middle_name":
                        input = middleName
                        feedback = middleNameFeedback
                        break
                    case "given_name":
                        input = givenName
                        feedback = givenNameFeedback
                        break
                    case "passport_type_id":
                        input = passportType
                        feedback = passportTypeFeedback
                        break
                    case "passport_number":
                        input = passportNumber
                        feedback = passportNumberFeedback
                        break
                    case "gender":
                        input = gender
                        feedback = genderFeedback
                        break
                    case "birthday":
                        input = birthday
                        feedback = birthdayFeedback
                        break
                    case "email":
                        input = email
                        feedback = emailFeedback
                        break
                    case "mobile":
                        input = mobile
                        feedback = mobileFeedback
                        break
                }

                if (feedback) {
                    setInvalid(input, feedback, value)
                } else {
                    console.error("Undefined feedback key:", key)
                }
            }
        }

        inputs.forEach((input) => {
            if (!input.classList.contains("is-invalid")) {
                input.classList.add("is-valid")
            }
        })

        resetSubmitButton()
    }

    function resetSubmitButton() {
        submitButton.disabled = false
        buttonText.textContent = "Register"
        loadingSpinner.classList.add("hidden")
    }

    if (form) {
        form.addEventListener("submit", (event) => {
            event.preventDefault()
            if (!submitButton.disabled && validation()) {
                submitButton.disabled = true
                buttonText.textContent = "Submitting..."
                loadingSpinner.classList.remove("hidden")

                const data = {
                    username: username.value,
                    password: password.value,
                    password_confirmation: confirmPassword.value,
                    family_name: familyName.value,
                    middle_name: middleName.value,
                    given_name: givenName.value,
                    gender: gender.value,
                    passport_type_id: passportType.value,
                    passport_number: passportNumber.value,
                    birthday: birthday.value,
                    email: email.value,
                    mobile: mobile.value,
                }

                post(form.action, successCallback, failCallback, "post", data)
            }
        })
    }

    window.addEventListener("pageshow", (event) => {
        if (event.persisted) {
            resetSubmitButton()
        }
    })
})

