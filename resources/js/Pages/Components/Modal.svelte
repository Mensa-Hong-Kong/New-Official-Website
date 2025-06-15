<script module>
    import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from '@sveltestrap/sveltestrap';

    const toggle = () => (modal.show = !modal.show);

    let modal = $state({
        type: '',
        message: '',
        closedCallback: null,
        confirmCallback: null,
        confirmCallbackPassData: null,
        show: false,
    });

    export function alert(message, closedCallback = null) {
        modal.type = 'alert';
        modal.message = message;
        modal.closedCallback = closedCallback;
        modal.confirmCallbackPassData = null;
        modal.confirmCallbackPassDataPassData = null;
        modal.show = true;
    }
    export function confirm(message, callback, passData) {
        modal.type = 'confirm';
        modal.message = message;
        modal.closedCallback = null;
        modal.confirmCallbackPassData = callback;
        modal.confirmCallbackPassDataPassData = passData;
        modal.show = true;
    }
</script>

<Modal isOpen={modal.show} {toggle}
    on:closing={() => {if(modal.closedCallback) {modal.closedCallback()}}}
    on:close={() => {if(modal.confirmCallback) {modal.confirmCallback(confirmCallbackPassData)}}}
    >
    <ModalHeader {toggle}>{modal.type == 'alert' ? 'Alert' : 'Confirmation'}</ModalHeader>
    <ModalBody>{modal.message}</ModalBody>
    <ModalFooter>
        {#if modal.type == 'confirm'}
            <Button color="success" on:click={toggle}>Confirm</Button>
        {/if}
        <Button color="danger" on:click={toggle}>Cancel</Button>
    </ModalFooter>
</Modal>
