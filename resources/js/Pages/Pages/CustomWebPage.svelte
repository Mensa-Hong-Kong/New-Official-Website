<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import "ckeditor5/ckeditor5.css";
    
    let {page} = $props();
</script>

<svelte:head>
    <title>{page.title} | {import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="{page.title} | {import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{page.description ?? import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:description" content="{page.description ?? import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:image" content="{page.og_image_url ?? 'og_image.png'}">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>

<Layout>
    <section class="container">
        <article class="ck-content">
            {@html page.content}
        </article>
    </section>
</Layout>