<script>
    import { post } from "@/submitForm";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
	import { confirm } from '@/Pages/Components/Modals/Confirm.svelte';
    import { Table, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { formatToDatetime } from '@/timeZoneDatetime';
    import { can, canAny } from "@/gate";

    let { candidates: initCandidates, submitting = $bindable(), test } = $props();
    let candidates = $state([]);
    let inputs = $state({
        candidates: []
    });
    let booleans = ['0', '1'];

    for(let row of initCandidates) {
        inputs['candidates'].push({});
        let data = {
            id: row.id,
            name: row.adorned_name,
            passportType: row.passport_type.name,
            passportNumber: row.passport_number,
            hasOtherSamePassportUserJoinedFutureTest: row.has_other_same_passport_user_joined_future_test,
            lastAttendedAdmissionTestOfOtherSamePassportUser: row.last_attended_admission_test_of_other_same_passport_user,
            hasSamePassportAlreadyQualificationOfMembership: row.has_same_passport_already_qualification_of_membership,
            lastAttendedAdmissionTest: row.last_attended_admission_test,
            seatNumber: row.pivot.seat_number,
            isPresent: row.pivot.is_present,
            isPass: row.pivot.is_pass,
            updatingStatue: false,
            deleting: false,
        };
        if(
            ! test.isFree &&
            canAny(['View:Admission Test Order', 'Edit:Admission Test Order'])
        ) {
            data['isFree'] = row.pivot.order_id == null;
        }
        candidates.push(data);
    }

    function getIndexById(id) {
        return candidates.findIndex(
            function(element) {
                return element.id == id;
            }
        );
    }

    function getIndexBySeatNumber(seatNumber) {
        return candidates.findIndex(
            function(element) {
                return element.seatNumber == seatNumber;
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

    function updatePresentStatueFailCallback(error) {
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
                    updatePresentStatueFailCallback,
                    'put', {status: status}
                );
            }
        }
    }

    function updateResultSuccessCallback(response) {
        alert(response.data.success);
        let seatNumber = route().match(response.request.responseURL, 'put').params.seat_number;
        let index = getIndexBySeatNumber(seatNumber);
        candidates[index]['isPass'] = response.data.status;
        candidates[index]['updatingStatue'] = false;
        submitting = false;
    }

    function updateResultFailCallback(error) {
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
        let seatNumber = route().match(error.request.responseURL, 'put').params.seat_number;
        let index = getIndexBySeatNumber(seatNumber);
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
                            seat_number: candidates[index]['seatNumber'],
                        }
                    ),
                    updateResultSuccessCallback,
                    updateResultFailCallback,
                    'put', {status: status}
                );
            }
        }
    }

    function updateResult(index, status) {
        let message = `Are you sure to update candidate of seat number ${candidates[index]['seatNumber']}) result to ${status? 'pass' : 'fail'}?`;
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
        inputs.isFree.checked = false;
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
                    let data = {
                        user_id: inputs.user.value,
                        function: event.submitter.value,
                    };
                    if(! test.isFree && inputs.isFree.checked) {
                        data['is_free'] = true;
                    }
                    post(
                        route(
                            'admin.admission-tests.candidates.store',
                            {admission_test: test.id}
                        ),
                        createSuccessCallback,
                        createFailCallback,
                        'post', data
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
    <Table responsive hover class="text-nowrap">
        <thead>
            <tr>
                <th style="width: 120ox !important;">User ID</th>
                <th>Name</th>
                <th>Passport Type</th>
                <th>Passport Number</th>
                {#if new Date(formatToDatetime(test.testingAt)) < (new Date).addDays(2).endOfDay()}
                    <th>Seat Number</th>
                {/if}
                {#if
                    ! test.isFree &&
                    canAny(['View:Admission Test Order', 'Edit:Admission Test Order'])
                }
                    <th>Is Free</th>
                {/if}
                {#if new Date(formatToDatetime(test.testingAt)) < (new Date).addDays(2).endOfDay()}
                    <th>Show</th>
                {/if}
                {#if can('Edit:Admission Test')}
                    <th colspan={new Date(formatToDatetime(test.expectEndAt)) < (new Date).subHour(2) ? 3 : 2}>Control</th>
                {:else if
                    new Date(formatToDatetime(test.testingAt)) < (new Date).addHours(2) &&
                    new Date(formatToDatetime(test.expectEndAt)) > (new Date).subHour(2)
                }
                    <th>Control</th>
                {/if}
            </tr>
        </thead>
        <tbody>
            {#each candidates as row, index}
                <tr>
                    <td>
                        {#if can('View:User')}
                            <Link href={
                                route(
                                    'admin.users.show',
                                    {user: row.id}
                                )
                            }>{row.id}</Link>
                        {:else}
                            {row.id}
                        {/if}
                    </td>
                    <td>{row.name}</td>
                    <td>{row.passportType}</td>
                    <td class={{
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
                    }}>{row.passportNumber}</td>
                    {#if new Date(formatToDatetime(test.testingAt)) < (new Date).addDays(2).endOfDay()}
                        <td>{row.seatNumber}</td>
                    {/if}
                    {#if
                        ! test.isFree &&
                        canAny(['View:Admission Test Order', 'Edit:Admission Test Order'])
                    }
                        <td>{row.isFree ? 'Free' : 'Fee'}</td>
                    {/if}
                    {#if
                        new Date(formatToDatetime(test.testingAt)) < (new Date).addHours(2) &&
                        new Date(formatToDatetime(test.expectEndAt)) > (new Date).subHour(2)
                    }
                        <td>
                            <Link class="btn btn-primary"
                                href={
                                    route(
                                        'admin.admission-tests.candidates.show',
                                        {
                                            admission_test: route().params.admission_test,
                                            candidate: row.id,
                                        }
                                    )
                                }>Show</Link>
                        </td>
                    {/if}
                    {#if
                        new Date(formatToDatetime(test.testingAt)) < (new Date).addHours(2) &&
                        new Date(formatToDatetime(test.expectEndAt)) > (new Date).subHour(2)
                    }
                        <td>
                            <Button block color={row.isPresent ? 'success' : 'danger'}
                                name="status" value={! row.isPresent} style="min-width: 85px !important"
                                disabled={test.inTestingTimeRange || booleans.includes(row.isPass)}
                                onclick={() => updatePresentStatue(index, ! row.isPresent)}>
                                {row.isPresent ? 'Present' : 'Absent'}</Button>
                        </td>
                    {/if}
                    {#if can('Edit:Admission Test')}
                        {#if new Date(formatToDatetime(test.expectEndAt)) < (new Date).subHour(2)}
                            <td>
                                <Button block color="success" name="status" value={true} style="min-width: 85px !important"
                                    disabled={row.isPass || ! row.isPresent || new Date(test.expectEndAt) > new Date || submitting}
                                    onclick={() => updateResult(index, true)}>Pass</Button>
                            </td>
                            <td>
                                <Button block color="danger" name="status" value={false} style="min-width: 85px !important"
                                    disabled={(! row.isPass && row.isPass !== null) || ! row.isPresent || new Date(test.expectEndAt) > new Date || submitting}
                                    onclick={() => updateResult(index, false)}>Fail</Button>
                            </td>
                        {/if}
                        <td colspan={new Date(formatToDatetime(test.expectEndAt)) < (new Date).subHour(2) ? 3 : 2}>
                            <Button block color="danger" disabled={submitting} onclick={() => destroy(index)} style="min-width: 85px !important">
                                {#if row.deleting}
                                    <Spinner type="border" size="sm" />Deleting...
                                {:else}
                                    Delete
                                {/if}
                            </Button>
                        </td>
                    {/if}
                </tr>
            {/each}
            {#if
                can(['View:User', 'Edit:Admission Test']) &&
                new Date(formatToDatetime(test.testingAt)) >= (new Date).addDays(2).endOfDay()
            }
                <tr>
                    <td style="width: 150px">
                        <form method="POST" novalidate onsubmit={create} id="createCandidateForm">
                            <Input name="user_id" patten="^\+?[1-9][0-9]*" required
                                bind:inner={inputs.user} />
                        </form>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {#if ! test.isFree}
                        <td>
                            <input type="checkbox" class="btn-check" name="is_free" id="isFree"
                                bind:this={inputs.isFree} disabled={creating} form="createCandidateForm" />
                            <label class="form-control btn btn-outline-success" for='isFree'>Is Free</label>
                        </td>
                    {/if}
                    <td colspan=1 hidden={creating}>
                        <Button block color="success" disabled={submitting} hidden={creating}
                            name="function" value="schedule" form="createCandidateForm">Schedule</Button>
                    </td>
                    <td colspan=1 hidden={creating}>
                        <Button block color="success" disabled={submitting} hidden={creating}
                            name="function" value="reschedule" form="createCandidateForm">Reschedule</Button>
                    </td>
                    <td colspan=2 hidden={! creating}>
                        <Button block color="success" disabled  hidden={! creating}>
                            <Spinner type="border" size="sm" />Adding...
                        </Button>
                    </td>
                </tr>
            {/if}
        </tbody>
    </Table>
</article>
