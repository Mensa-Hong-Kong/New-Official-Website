<script module>
    import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from '@sveltestrap/sveltestrap';

    let modal = $state({
        message: '',
        callback: null,
        show: false,
    });

    function toggle() {
        modal.show = ! modal.show;
        if(modal.callback) {
            let callback = modal.callback;
            modal.callback = null;
            modal.show = false;
            callback();
        }
    };

    export function alert(message, callback = null) {
        modal.message = message;
        modal.callback = callback;
        modal.show = true;
    }
</script>

<Modal isOpen={modal.show} {toggle}>
    <ModalHeader {toggle}>Alert</ModalHeader>
    <ModalBody>{modal.message}</ModalBody>
    <ModalFooter>
        <Button color="danger" on:click={toggle}>Cancel</Button>
    </ModalFooter>
</Modal>
