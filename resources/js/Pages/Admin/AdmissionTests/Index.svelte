<script>
    import { Table, Button, Spinner, Alert } from '@sveltestrap/sveltestrap';
    import SortableLink from '@/Pages/Components/SortableLink.svelte';
    import Pagination from '@/Pages/Components/Pagination.svelte';
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';

    let { auth, tests: initTests } = $props();
    let submitting = $state();
    let tests = $state(initTests.data);

    function getIndexById(id) {
        return tests.findIndex(
            function(row) {
                return row.id == id;
            }
        );
    }

    function alertCallback() {
        window.location.reload();
    }

    function deleteSuccessCallback(response) {
        let id = route().match(response.request.responseURL, 'delete').params.admission_test;
        let index = getIndexById(id);
        tests[index]['deleting'] = false;
        submitting = false;
        alert(response.data.success, alertCallback);
    }

    function deleteFailCallback(error) {
        let id = route().match(error.request.responseURL, 'delete').params.admission_test;
        let index = getIndexById(id);
        tests[index]['deleting'] = false;
        submitting = false;
    }

    function confirmedDelete(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'delete'+submitAt;
            if(submitting == 'delete'+submitAt) {
                tests[index]['deleting'] = true;
                post(
                    route(
                        'admin.admission-tests.destroy',
                        {admission_test: tests[index]['id']}
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(index) {
        let message = `Are you sure to delete the ${tests[index]['location']['name']}(${(new Date(tests[index]['testing_at'])).toISOString().split('.')[0].replace('T', ' ')})?`;
        confirm(message, confirmedDelete, index);
    }
</script>
<section class="container">
    <h2 class="mb-2 fw-bold text-uppercase">Admission Tests</h2>
    {#if tests.length}
        <Table hover>
            <thead>
                <tr>
                    <th scope="col"><SortableLink column="id" title="#" /></th>
                    <th scope="col"><SortableLink column="testing_at" title="Testing At" /></th>
                    <th scope="col">Location</th>
                    <th scope="col">Candidates</th>
                    <th scope="col">Is Public</th>
                    <th scope="col">Control</th>
                </tr>
            </thead>
            <tbody>
                {#each tests as row, index}
                    <tr>
                        <th scope="row">{row.id}</th>
                        <td>{(new Date(row.testing_at)).toISOString().split('.')[0].replace('T', ' ')}</td>
                        <td>{row.location.name}</td>
                        <td>{row.candidates_count}/{row.maximum_candidates}</td>
                        <td>{row.is_public ? 'Public' : 'Private'}</td>
                        <td>
                            {#if
                                row.in_testing_time_range ||
                                auth.user.permissions.includes('Edit:Admission Test') ||
                                auth.user.roles.includes('Super Administrator')
                            }
                                <a class="btn btn-primary" href={
                                    route(
                                        'admin.admission-tests.show',
                                        {admission_test: row.id}
                                    )
                                }>Show</a>
                                <Button color="danger"
                                    disabled={submitting} onclick={() => destroy(index)}>
                                    {#if row.deleting}
                                        <Spinner type="border" size="sm" />
                                        Deleting...
                                    {:else}
                                        Delete
                                    {/if}
                                </Button>
                            {:else}
                                <Button color="secondary" disabled={submitting}>Show</Button>
                            {/if}
                        </td>
                    </tr>
                {/each}
            </tbody>
        </Table>
        <Pagination total={tests.last_page} current={tests.current_page} />
    {:else}
        <Alert color="danger">
            No Result
        </Alert>
    {/if}
</section>