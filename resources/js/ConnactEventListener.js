export class ConnactEventListener {
    cancelVerify() {
        if(
            this.submittingVerifyButton.hidden &&
            !this.renewButton.disabled
        ) {
            this.verifyCodeInput.hidden = true;
            this.verifyCodeInput.disabled = true;
            this.renewButton.hidden = true;
            this.renewButton.disabled = true;
            this.cancelButton.hidden = true;
            this.cancelButton.disabled = true;
            this.submitVerifyButton.hidden = true;
            this.submitVerifyButton.disabled = true;
            this.verifyButton.hidden = false;
            this.verifyButton.disabled = false;
        }
    }

    verify() {
        if(
            this.submitVerifyButton.hidden &&
            this.submittingVerifyButton.hidden &&
            this.renewButton.disabled
        ) {
            this.verifyCodeInput.hidden = false;
            this.verifyCodeInput.disabled = false;
            this.renewButton.hidden = false;
            this.renewButton.disabled = false;
            this.cancelButton.hidden = false;
            this.cancelButton.disabled = false;
            this.submitVerifyButton.hidden = false;
            this.submitVerifyButton.disabled = false;
            this.verifyButton.hidden = true;
            this.verifyButton.disabled = true;
        }
    }

    renewVerifyCode() {
        this.renewButton.disabled = true;
        this.submitVerifyButton.disabled = true;
        this.verifyCodeInput.disabled = true;
        this.renewButton.disabled = true;
        this.cancelButton.disabled = true;
        // ,,,
    }

    submitVerifyCode() {
        this.submittingVerifyButton.hidden = false;
        this.submitVerifyButton.hidden = true;
        this.submitVerifyButton.disabled = true;
        this.verifyCodeInput.disabled = true;
        this.renewButton.disabled = true;
        this.cancelButton.disabled = true;
        // ,,,
    }

    timer() {
        // ...
    }

    constructor(type, id) {
        this.isSubmitting = false;
        this.renewing = false;
        this.apiPathnamePrefix = `/${type}s/${id}/`;
        this.verifyCodeInput = document.getElementById(`${type}VerifyCodeInput${id}`);
        this.renewButton = document.getElementById(`${type}RenewVerifyCodeButton${id}`);
        this.renewCountDown = document.getElementById(`${type}RenewVerifyCodeCountDown${id}`);
        this.cancelButton = document.getElementById(`${type}CancelVerifyButton${id}`);
        this.submitVerifyButton = document.getElementById(`${type}SubmitVerifyButton${id}`);
        this.submittingVerifyButton = document.getElementById(`${type}SubmittingVerifyButton${id}`);
        this.verifyButton = document.getElementById(`${tyoe}VerifyButton${id}`);
        this.renewVerifyCode = this.renewVerifyCode.bind(this);
        this.submitVerifyCode = this.submitVerifyCode.bind(this);
        this.cancelVerify = this.cancelVerify.bind(this);
        this.verify = this.verify.bind(this);
        this.timer = this.timer.bind(this);
        this.renewButton.addEventListener('click', this.renewVerifyCode);
        this.submitVerifyButton.addEventListener('click', this.submitVerifyCode);
        this.cancelButton.addEventListener('click', this.cancelVerify);
        this.verifyButton.addEventListener('click', this.verify);
        setInterval(timer, 1, this.verifyCodeInput);
    }
}
