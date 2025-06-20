<script module>
    import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from '@sveltestrap/sveltestrap';

    let modal = $state({
        message: '',
        callback: null,
        callbackData: null,
        show: false,
    });

    function toggle() {
        modal.callback = null;
        modal.callbackData = null;
        modal.show = false;
    };

    function confirmed() {
        let callback, data;
        if(modal.callback) {
            callback = modal.callback;
            data = modal.callbackData;
        }
        toggle();
        if(callback) {
            callback(data);
        }
    }

    export function confirm(message, callback, callbackData) {
        modal.message = message;
        modal.callback = callback;
        modal.callbackData = callbackData;
        modal.show = true;
    }
</script>

<Modal isOpen={modal.show} {toggle}>
    <ModalHeader {toggle}>Confirmation</ModalHeader>
    <ModalBody>{modal.message}</ModalBody>
    <ModalFooter>
        <Button color="success" on:click={confirmed}>Confirm</Button>
        <Button color="danger" on:click={toggle}>Cancel</Button>
    </ModalFooter>
</Modal>
