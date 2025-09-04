<script>
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
    import { Row, Col, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    
    let { auth, candidates: initCandidates, submitting = $bindable(), test } = $props();
    let candidates = $state([]);
    let inputs = $state({
        candidates: []
    });
    let booleans = ['0', '1'];

    for(let row of initCandidates) {
        inputs['candidates'].push({});
        candidates.push({
            id: row.id,
            name: row.adorned_name,
            passportType: row.passport_type.name,
            passportNumber: row.passport_number,
            hasOtherSamePassportUserJoinedFutureTest: row.has_other_same_passport_user_joined_future_test,
            lastAttendedAdmissionTestOfOtherSamePassportUser: row.last_attended_admission_test_of_other_same_passport_user,
            hasSamePassportAlreadyQualificationOfMembership: row.has_same_passport_already_qualification_of_membership,
            lastAttendedAdmissionTest: row.last_attended_admission_test,
            isPresent: row.pivot.is_present,
            isPass: row.pivot.is_pass,
            updatingStatue: false,
            deleting: false,
        });
    }

    function getIndexById(id) {
        return candidates.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    function updatePresentStatueSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.candidate;
        let index = getIndexById(id);
        candidates[index]['isPresent'] = response.data.status;
        candidates[index]['updatingStatue'] = false;
        submitting = false;
    }

    function updateStatueFailCallback(error) {
        if(error.status == 422) {
            for(let key in error.response.data.errors) {
                let value = error.response.data.errors[key];
                switch(key) {
                    case 'status':
                        alert(value);
                        break;
                    default:
                        alert(`Undefine Feedback Key: ${key}\nMessage: ${value}`);
                        break;
                }
            }
        }
        let id = route().match(error.request.responseURL, 'put').params.candidate;
        let index = getIndexById(id);
        candidates[index]['updatingStatue'] = false;
        submitting = false;
    }

    function updatePresentStatue(index, status) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updatePresentStatue'+submitAt;
            if(submitting == 'updatePresentStatue'+submitAt) {
                candidates[index]['updatingStatue'] = false;
                post(
                    route(
                        'admin.admission-tests.candidates.present.update',
                        {
                            admission_test: route().params.admission_test,
                            candidate: candidates[index]['id'],
                        }
                    ),
                    updatePresentStatueSuccessCallback,
                    updateStatueFailCallback,
                    'put', {status: status}
                );
            }
        }
    }

    function updateResultSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'put').params.candidate;
        let index = getIndexById(id);
        candidates[index]['isPass'] = response.data.status;
        candidates[index]['updatingStatue'] = false;
        submitting = false;
    }

    function confirmedUpdateResult(args) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updateResult'+submitAt;
            let [index, status] = args;
            if(submitting == 'updateResult'+submitAt) {
                candidates[index]['updatingStatue'] = false;
                post(
                    route(
                        'admin.admission-tests.candidates.result.update',
                        {
                            admission_test: route().params.admission_test,
                            candidate: candidates[index]['id'],
                        }
                    ),
                    updateResultSuccessCallback,
                    updateStatueFailCallback,
                    'put', {status: status}
                );
            }
        }
    }

    function updateResult(index, status) {
        let message = `Are you sure to update candidate of ${candidates[index]['name']}(${candidates[index]['passportNumber']}) result to ${status? 'pass' : 'fail'}?`;
        confirm(message, confirmedUpdateResult, [index, status]);
    }

    function deleteSuccessCallback(response) {
        alert(response.data.success);
        let id = route().match(response.request.responseURL, 'delete').params.candidate;
        let index = getIndexById(id);
        candidates.splice(index, 1);
        submitting = false;
    }

    function deleteFailCallback(error) {
        let id = route().match(error.request.responseURL, 'delete').params.candidate;
        let index = getIndexById(id);
        candidates[index]['deleting'] = false;
        submitting = false;
    }

    function confirmedDelete(index) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'deleteCandidate'+submitAt;
            if(submitting == 'deleteCandidate'+submitAt) {
                candidates[index]['deleting'] = true;
                post(
                    route(
                        'admin.admission-tests.candidates.destroy',
                        {
                            admission_test: route().params.admission_test,
                            candidate: candidates[index]['id'],
                        }
                    ),
                    deleteSuccessCallback,
                    deleteFailCallback,
                    'delete'
                );
            }
        }
    }

    function destroy(index) {
        let message = `Are you sure to delete candidate of ${candidates[index]['name']}(${candidates[index]['passportNumber']})?`;
        confirm(message, confirmedDelete, index);
    }

    let creating = $state(false);

    function validation()
    {
        if(inputs.user.validity.valueMissing) {
            alert('The user id field is required.');
            return false;
        }
        if(inputs.user.validity.patternMismatch) {
            alert('The user id field must be an integer.');
            return false;
        }
        return true;
    }

    function createSuccessCallback(response) {
        alert(response.data.success);
        inputs.candidates.push({});
        candidates.push({
            id: response.data.user_id,
            name: response.data.name,
            passportType: response.data.passport_type,
            passportNumber: response.data.passport_number,
            hasOtherSamePassportUserJoinedFutureTest: response.has_other_same_passport_user_joined_future_test,
            lastAttendedAdmissionTest: null,
            isPresent: null,
            isPass: false,
            updatingStatue: false,
            deleting: false,
        });
        inputs.user.value = '';
        creating = false;
        submitting = false;
    }

    function createFailCallback(error) {
        if(error.status == 422) {
            alert(error.response.data.errors.user_id);
        }
        creating = false;
        submitting  = false;
    }

    function create(event) {
        event.preventDefault();
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'create'+submitAt;
            if(submitting == 'create'+submitAt) {
                if(validation()) {
                    creating = true;
                    post(
                        route(
                            'admin.admission-tests.candidates.store',
                            {admission_test: test.id}
                        ),
                        createSuccessCallback,
                        createFailCallback,
                        'post', {
                            user_id: inputs.user.value,
                            function: event.submitter.value,
                        }
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<article>
    <h3 class="mb-2 fw-bold">Candidates</h3>
    <Row class="g-3">
        <Col md=1>User ID</Col>
        <Col md=2>Name</Col>
        <Col md=2>Passport Type</Col>
        <Col md=2>Passport Number</Col>
        <Col md=4>Control</Col>
    </Row>
    {#each candidates as row, index}
        <Row class="g-3">
            <Col md=1>{row.id}</Col>
            <Col md=2>{row.name}</Col>
            <Col md=2>{row.passportType}</Col>
            <div class={['col-md-2', {
                'text-warning': row.hasOtherSamePassportUserJoinedFutureTest,
                'text-danger': row.lastAttendedAdmissionTestOfOtherSamePassportUser ||
                    row.hasSamePassportAlreadyQualificationOfMembership || (
                        row.lastAttendedAdmissionTest &&
                        row.lastAttendedAdmissionTest.testing_at >= new Date(
                            (new Date(row.lastAttendedAdmissionTest.testing_at)).setMonth(
                                (new Date(row.lastAttendedAdmissionTest.testing_at))
                                    .getMonth - row.lastAttendedAdmissionTest.type.interval_month
                            )
                        ) && new Date(lastAttendedAdmissionTest.testing_at) <= new Date
                    ),
            }]}>{row.passportNumber}</div>
            {#if
                auth.user.permissions.includes('View:User') ||
                auth.user.roles.includes('Super Administrator')
            }
                <Link class="btn btn-primary col-md-1"
                    href={
                        route(
                            'admin.users.show', 
                            {user: row.id}
                        )
                    }>Show</Link>
                <Button color={row.isPresent ? 'success' : 'danger'}
                    name="status" value={! row.isPresent}
                    disabled={test.inTestingTimeRange || booleans.includes(row.isPass)}
                    onclick={() => updatePresentStatue(index, ! row.isPresent)}
                    class="col-md-1">{row.isPresent ? 'Present' : 'Absent'}</Button>
                {#if
                    auth.user.permissions.includes('Edit:Admission Test') ||
                    auth.user.roles.includes('Super Administrator')
                }
                    <Button color="success" name="status" value={true}
                        disabled={row.isPass || ! row.isPresent || new Date(test.expectEndAt) > new Date || submitting}
                        onclick={() => updateResult(index, true)}
                        class="col-md-1">Pass</Button>
                    <Button color="danger" name="status" value={false}
                        disabled={(! row.isPass && row.isPass !== null) || ! row.isPresent || new Date(test.expectEndAt) > new Date || submitting}
                        onclick={() => updateResult(index, false)}
                        class="col-md-1">Fail</Button>
                    <Button color="danger" disabled={submitting} 
                        onclick={() => destroy(index)} class="col-md-1">
                        {#if row.deleting}
                            <Spinner type="border" size="sm" />Deleting...
                        {:else}
                            Delete
                        {/if}
                    </Button>
                {/if}
            {:else}
                <Link class="btn btn-primary col-md-1"
                    href={
                        route(
                            'admin.admission-tests.candidates.show', 
                            {
                                admission_test: route().params.admission_test,
                                candidate: row[index]['id'],
                            }
                        )
                    }>Show</Link>
            {/if}
        </Row>
    {/each}
    {#if
        new Date(test.testingAt) >= new Date && (
            (
                auth.user.permissions.includes('View:User') &&
                auth.user.permissions.includes('Edit:Admission Test')
            ) || auth.user.roles.includes('Super Administrator')
        )
    }
        <form class="row g-3" method="POST" novalidate onsubmit={create}>
            <Col md=1>
                <Input name="user_id" required bind:inner={inputs.user} />
            </Col>
            <Col md=6 />
            <Button color="success" class="col-md-2" disabled={submitting} hidden={creating}
                name="function" value="schedule">Schedule</Button>
            <Button color="success" class="col-md-2" disabled={submitting} hidden={creating}
                name="function" value="reschedule">Reschedule</Button>
            <Button color="success" class="col-md-4" disabled  hidden={! creating}>
                <Spinner type="border" size="sm" />Adding...
            </Button>
        </form>
    {/if}
</article>