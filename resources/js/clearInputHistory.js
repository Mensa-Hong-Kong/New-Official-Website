export default class ClearInputHistory
{
    invoke(event) {
        if(typeof this.inputs === 'object') {
            for(let [key, input] of Object.entries(this.inputs)) {
                if(input.type == 'date') {
                    if(input.max) {
                        input.value = input.max;
                    } else if(input.min) {
                        input.value = input.min;
                    } else {
                        input.value = '';
                    }
                } else {
                    input.value = '';
                }
            }
        } else {
            for(let input of this.inputs) {
                if(input.type == 'date') {
                    if(input.max) {
                        input.value = input.max;
                    } else if(input.min) {
                        input.value = input.min;
                    } else {
                        input.value = '';
                    }
                } else {
                    input.value = '';
                }
            }
        }
    }

    constructor(inputs) {
        this.inputs = inputs;
        this.invoke = this.invoke.bind(this)
        window.addEventListener("pagehide", this.invoke);
    }

    destroy() {
        window.removeEventListener("pagehide", this.invoke);
    }
}
