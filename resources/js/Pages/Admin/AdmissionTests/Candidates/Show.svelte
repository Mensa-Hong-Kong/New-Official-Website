<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Table, Button } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm.svelte";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
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

<svelte:head>
    <title>Administration Show Candidate | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold">
            Candidate
            <Link class="btn btn-primary"
                href={
                    route(
                        'admin.admission-tests.candidates.edit',
                        {
                            admission_test: test.id,
                            candidate: user.id,
                        }
                    )
                }>Edit</Link>
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
                        'text-warning': user.has_other_same_passport_user_joined_future_test,
                        'text-danger': user.has_same_passport_already_qualification_of_membership ||
                            user.last_attended_admission_test_of_other_same_passport_user || (
                                user.last_attended_admission_test &&
                                user.last_attended_admission_test.testing_at >= new Date(
                                    (new Date(user.last_attended_admission_test.testing_at)).setMonth(
                                        (new Date(user.last_attended_admission_test.testing_at))
                                            .getMonth - user.last_attended_admission_test.type.interval_month
                                    )
                                ) && new Date(last_attended_admission_test.testing_at) <= new Date
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
</Layout>