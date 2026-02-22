<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { Table, Button } from '@sveltestrap/sveltestrap';
    import { Link } from "@inertiajs/svelte";
    import { post } from "@/submitForm";
	import { alert } from '@/Pages/Components/Modals/Alert.svelte';
    import { formatToDate } from '@/timeZoneDatetime';

    seo.title = 'Administration Show Candidate';

    let { candidate, isPresent, seatNumber } = $props();
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
                            admission_test: route().params.admission_test,
                            candidate: candidate.id,
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
        <Link class="btn btn-primary"
            href={
                route(
                    'admin.admission-tests.candidates.edit',
                    {
                        admission_test: route().params.admission_test,
                        candidate: candidate.id,
                    }
                )
            }>Edit</Link>
    </h2>
    <Table>
        <tbody>
            <tr>
                <th>Gender</th>
                <td>{candidate.gender.name}</td>
            </tr>
            <tr>
                <th>Family Name</th>
                <td>{candidate.family_name}</td>
            </tr>
            <tr>
                <th>Middle Name</th>
                <td>{candidate.middle_name}</td>
            </tr>
            <tr>
                <th>Given Name</th>
                <td>{candidate.given_name}</td>
            </tr>
            <tr>
                <th>Passport Type</th>
                <td>{candidate.passport_type.name}}</td>
            </tr>
            <tr>
                <th>Passport Number</th>
                <td class={[{
                    'text-warning': candidate.has_other_same_passport_user_joined_future_test,
                    'text-danger': candidate.has_same_passport_already_qualification_of_membership ||
                        candidate.last_attended_admission_test_of_other_same_passport_user || (
                            candidate.last_attended_admission_test &&
                            candidate.last_attended_admission_test.testing_at >= new Date(
                                (new Date(candidate.last_attended_admission_test.testing_at)).setMonth(
                                    (new Date(candidate.last_attended_admission_test.testing_at))
                                        .getMonth - candidate.last_attended_admission_test.type.interval_month
                                )
                            ) && new Date(last_attended_admission_test.testing_at) <= new Date
                        ),
                }]}>{candidate.passport_number}</td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td>{formatToDate(candidate.birthday)}</td>
            </tr>
            <tr>
                <th>Seat Number</th>
                <td>{seatNumber}</td>
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
