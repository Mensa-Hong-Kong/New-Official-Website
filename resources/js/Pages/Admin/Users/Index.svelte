<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Accordion, AccordionItem, Col, Label, Input, Button, Table, Alert } from '@sveltestrap/sveltestrap';
    import SortableLink from '@/Pages/Components/SortableLink.svelte';
    import { Link } from "@inertiajs/svelte";
    import Pagination from '@/Pages/Components/Pagination.svelte';
    import { formatToDatetime } from '@/timeZoneDatetime';

    let {isSearch = false, users, passportTypes, maxBirthday, genders, append = {}} = $props();
    let inputs = $state({});
    let feedbacks = $state({
        familyName: '',
        middleName: '',
        givenName: '',
        passportType: '',
        passportNumber: '',
        gender: '',
        birthday: '',
        email: '',
        mobile: '',
    });

    function hasError() {
        for(let [key, feedback] of Object.entries(feedbacks)) {
            if(feedback != 'Looks good!') {
                return true;
            }
        }
        return false;
    }

    function validation() {
        for(let key in feedbacks) {
            feedbacks[key] = 'Looks good!';
        }
        if(inputs.familyName.value && inputs.familyName.validity.tooLong) {
            feedbacks.familyName = `The family name must not be greater than ${inputs.familyName.maxLength} characters.`;
        }
        if(inputs.middleName.value && inputs.middleName.validity.tooLong) {
            feedbacks.middleName = `The middle name must not be greater than ${inputs.middleName.maxLength} characters.`;
        }
        if(inputs.givenName.value && inputs.givenName.validity.tooLong) {
            feedbacks.givenName = `The given name must not be greater than ${inputs.givenName.maxLength} characters.`;
        }
        if(inputs.passportType.value && inputs.passportNumber.value) {
            if(inputs.passportNumber.validity.tooShort) {
                feedbacks.passportNumber = `The passport number must be at least ${inputs.passportNumber.minLength} characters.`;
            } else if(inputs.passportNumber.validity.tooLong) {
                feedbacks.passportNumber = `The passport number must not be greater than ${inputs.passportNumber.maxLength} characters.`;
            }
        }
        if(inputs.birthday.value && inputs.birthday.validity.rangeOverflow) {
            feedbacks.birthday = `The birthday not be greater than ${inputs.birthday.max} characters.`;
        }
        if(inputs.email.value) {
            if(inputs.email.validity.tooLong) {
                feedbacks.email = `The email must not be greater than ${inputs.email.maxLength} characters.`;
            } else if(inputs.email.validity.typeMismatch) {
                feedbacks.email = `The email must be a valid email address.`;
            }
        }
        if(inputs.mobile.value) {
            if(inputs.mobile.validity.tooShort) {
                feedbacks.mobile = `The mobile must be at least ${inputs.mobile.minLength} characters.`;
            } else if(mobile.validity.tooLong) {
                feedbacks.mobile = `The mobile must not be greater than ${inputs.mobile.maxLength} characters.`;
            } else if(mobile.validity.typeMismatch) {
                feedbacks.mobile = `The email must be a valid email address.`;
            }
        }
        return !hasError();
    }

    function search(event) {
        if(! validation()) {
            event.preventDefault();
        }
    }
</script>

<svelte:head>
    <title>Administration Users | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <h2 class="mb-2 fw-bold text-uppercase">Users</h2>
        <Accordion>
            <AccordionItem header="Search" active={isSearch}>
                <form class="row g-3" novalidate onsubmit="{search}">
                    <Col md="4">
                        <Label>Family Name</Label>
                        <Input name="family_name" maxlength="255"
                            value={append.family_name} placeholder="family name"
                            valid={feedbacks.familyName == 'Looks good!'}
                            invalid={feedbacks.familyName != '' && feedbacks.familyName != 'Looks good!' }
                            feedback={feedbacks.familyName} bind:inner={inputs.familyName} />
                    </Col>
                    <Col md="4">
                        <Label>Middle Name</Label>
                        <Input name="middle_name" maxlength="255"
                            value={append.middle_name} placeholder="middle name"
                            valid={feedbacks.middleName == 'Looks good!'}
                            invalid={feedbacks.middleName != '' && feedbacks.middleName != 'Looks good!' }
                            feedback={feedbacks.middleName} bind:inner={inputs.middleName} />
                    </Col>
                    <Col md="4">
                        <Label>Given Name</Label>
                        <Input name="given_name" maxlength="255"
                            value={append.given_name} placeholder="given name"
                            valid={feedbacks.givenName == 'Looks good!'}
                            invalid={feedbacks.givenName != '' && feedbacks.givenName != 'Looks good!' }
                            feedback={feedbacks.givenName} bind:inner={inputs.givenName} />
                    </Col>
                    <Col md="4">
                        <Label>Passport Type</Label>
                        <Input name="passport_type_id" type="select"
                            valid={feedbacks.passportType == 'Looks good!'}
                            invalid={feedbacks.passportType != '' && feedbacks.passportType != 'Looks good!' }
                            feedback={feedbacks.passportType} bind:inner={inputs.passportType}>
                            <option value="" selected="{typeof append.passport_type_id == 'undefined'}">
                                Please select passport type
                            </option>
                            {#each Object.entries(passportTypes) as [key, value]}
                                <option value="{key}" selected={key == append.passport_type_id}>{value}</option>
                            {/each}
                        </Input>
                    </Col>
                    <Col md="4">
                        <Label>Passport Number</Label>
                        <Input name="passport_number" minlength="8" maxlength="18"
                            value={append.passport_number} placeholder="passport number"
                            valid={feedbacks.passportNumber == 'Looks good!'}
                            invalid={feedbacks.passportNumber != '' && feedbacks.passportNumber != 'Looks good!' }
                            feedback={feedbacks.passportNumber} bind:inner={inputs.passportNumber} />
                    </Col>
                    <Col md="4"></Col>
                    <Col md="4">
                        <Label>Gender</Label>
                        <Input name="gender_id" type="select"
                            valid={feedbacks.gender == 'Looks good!'}
                            invalid={feedbacks.gender != '' && feedbacks.gender != 'Looks good!' }
                            feedback={feedbacks.gender} bind:inner={inputs.gender}>
                            <option value="" selected="{typeof append.gender_id == 'undefined'}">
                                Please select passport type
                            </option>
                            {#each Object.entries(genders) as [key, value]}
                                <option value="{key}" selected={key == append.gender_id}>{value}</option>
                            {/each}
                        </Input>
                    </Col>
                    <Col md="4">
                        <Label>Birthday</Label>
                        <Input name="birthday" type="date" max={maxBirthday}
                            value={append.birthday} placeholder="birthday"
                            valid={feedbacks.birthday == 'Looks good!'}
                            invalid={feedbacks.birthday != '' && feedbacks.birthday != 'Looks good!' }
                            feedback={feedbacks.birthday} bind:inner={inputs.birthday} />
                    </Col>
                    <Col md="4"></Col>
                    <Col md="4">
                        <Label>Email</Label>
                        <Input name="email" type="email"
                            value={append.email} placeholder="ab@c.d"
                            valid={feedbacks.email == 'Looks good!'}
                            invalid={feedbacks.email != '' && feedbacks.email != 'Looks good!' }
                            feedback={feedbacks.email} bind:inner={inputs.email} />
                    </Col>
                    <Col md="4">
                        <Label>Mobile</Label>
                        <Input name="mobile" type="tel"
                            minlength="5" maxlength="15"
                            value={append.mobile} placeholder="85298765432"
                            valid={feedbacks.mobile == 'Looks good!'}
                            invalid={feedbacks.mobile != '' && feedbacks.mobile != 'Looks good!' }
                            feedback={feedbacks.mobile} bind:inner={inputs.mobile} />
                    </Col>
                    <Col md="6">
                        <Button block color="primary">Search</Button>
                    </Col>
                    <Col md="6">
                        <Link class="form-control btn btn-danger"
                            href={route('admin.users.index')}>Clear</Link>
                    </Col>
                </form>
            </AccordionItem>
        </Accordion>
        {#if users.data.length}
            <Table hover>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Gender</th>
                        <th scope="col"><SortableLink column="created_at" title="Created At" /></th>
                        <th scope="col"><SortableLink column="updated_at" title="Updated At" /></th>
                        <th scope="col"><SortableLink column="lastLoginLog.created_at" title="Last Login Time" /></th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    {#each users.data as row}
                        <tr>
                            <th scope="row">{row.id}</th>
                            <td>
                                {#if row.adorned_name.length > 32}
                                    {row.adorned_name.substr(0, 29)}...
                                {:else}
                                    {row.adorned_name}
                                {/if}
                            </td>
                            <td>
                                {#if genders[row.gender_id].length > 32}
                                    {genders[row.gender_id].substr(0, 29)}...
                                {:else}
                                    {genders[row.gender_id]}
                                {/if}
                            </td>
                            <td>{formatToDatetime(row.created_at)}</td>
                            <td>{formatToDatetime(row.updated_at)}</td>
                            <td>
                                {#if row.last_login_logs}
                                    {formatToDatetime(row.last_login_logs.created_at)}
                                {:else}
                                    --
                                {/if}
                            </td>
                            <td>
                                <Link href={route('admin.users.show', {user: row.id})}
                                   class="btn btn-primary">Show</Link>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </Table>
            <Pagination total={users.last_page} current={users.current_page} />
        {:else}
            <Alert color="danger">
                No Result
            </Alert>
        {/if}
    </section>
</Layout>