export default class ClearInputHistory
{
    constructor(inputs) {
        window.addEventListener(
            "pagehide", function() {
                for(let input of inputs) {
                    input.value = '';
                }
            }
        );
    }
}
