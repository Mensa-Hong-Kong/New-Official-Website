export default class ClearInputHistory
{
    constructor(inputs) {
        window.addEventListener(
            "pagehide", function() {
                for(let input of inputs) {
                    if(input.type == 'date') {
                        if(input.max) {
                            input.value = input.max;
                        } else if(input.min) {
                            input.value = input.max;
                        } else {
                            input.value = '';
                        }
                    } else {
                        input.value = '';
                    }
                }
            }
        );
    }
}
