<script>
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { Table, Button } from '@sveltestrap/sveltestrap';
    import { formatToDate } from '@/timeZoneDatetime';

    let { test, user, isPresent } = $props();
    let submitting = $state(false);

    function updatePresentStatueSuccessCallback(response) {
        alert(response.data.success);
        isPresent = response.data.status;
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
        submitting = false;
    }

    function updatePresentStatue(status) {
        if(! submitting) {
            let submitAt = Date.now();
            submitting = 'updatePresentStatue'+submitAt;
            if(submitting == 'updatePresentStatue'+submitAt) {
                post(
                    route(
                        'admin.admission-tests.candidates.present.update',
                        {
                            admission_test: test.id,
                            candidate: user.id,
                        }
                    ),
                    updatePresentStatueSuccessCallback,
                    updateStatueFailCallback,
                    'put', {status: status}
                );
            }
        }
    }
</script>

<section class="container">
    <h2 class="mb-2 fw-bold">
        Candidate
        <a class="btn btn-primary"
            href="{
                route(
                    'admin.admission-tests.candidates.edit',
                    {
                        admission_test: test.id,
                        candidate: user.id,
                    }
                )
            }">Edit</a>
    </h2>
    <Table>
        <tbody>
            <tr>
                <th>Gender</th>
                <td>{user.gender.name}</td>
            </tr>
            <tr>
                <th>Family Name</th>
                <td>{user.family_name}</td>
            </tr>
            <tr>
                <th>Middle Name</th>
                <td>{user.middle_name}</td>
            </tr>
            <tr>
                <th>Given Name</th>
                <td>{user.given_name}</td>
            </tr>
            <tr>
                <th>Passport Type</th>
                <td>{user.passport_type.name}}</td>
            </tr>
            <tr>
                <th>Passport Number</th>
                <td class={[{
                    'text-warning': user.has_other_user_same_passport_joined_future_test,
                    'text-danger': user.has_same_passport_already_qualification_of_membership ||
                        user.last_attended_admission_test_of_other_same_passport_user || (
                            user.lastAdmissionTest &&
                            row.lastPresentedAdmissionTest.testing_at >= new Date(
                                (new Date(row.lastPresentedAdmissionTest.testing_at)).setMonth(
                                    (new Date(row.lastPresentedAdmissionTest.testing_at))
                                        .getMonth - row.lastPresentedAdmissionTest.type.interval_month
                                )
                            ) && new Date(lastPresentedAdmissionTest.testing_at) <= new Date
                        ),
                }]}>{user.passport_number}</td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td>{formatToDate(user.birthday)}</td>
            </tr>
            <tr>
                <th>Is Present</th>
                <td>
                    <Button color={isPresent ? 'success' : 'danger'}
                        onclick={() => updatePresentStatue(! isPresent)}>
                        {isPresent ? 'Present' : 'Absent'}
                    </Button>
                </td>
            </tr>
        </tbody>
    </Table>
</section>