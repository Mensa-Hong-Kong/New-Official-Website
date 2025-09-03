<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { Accordion, AccordionItem, Table } from '@sveltestrap/sveltestrap';
    
    let {pages} = $props();
</script>

<svelte:head>
    <title>Administration Site Contents | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <section class="container">
        <div class="accordion">
            {#each pages as page}
                <Accordion>
                    <AccordionItem header={page.name}>
                        <Table hover>
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Control</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each page.contents as content}
                                    <tr>
                                        <td>{content.id}</td>
                                        <td>{content.name}</td>
                                        <td>
                                            <a class="btn btn-primary" href="{
                                                route(
                                                    'admin.site-contents.edit',
                                                    {site_content: content.id}
                                                )
                                            }">Edit</a>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </Table>
                    </AccordionItem>
                </Accordion>
            {/each}
        </div>
    </section>
</Layout>