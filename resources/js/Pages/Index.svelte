<script>
    import Layout from '@/Pages/Layouts/App.svelte';
</script>

<svelte:head>
    <title>Index | {import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="Index | {import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:description" content="{import.meta.env.VITE_APP_DESCRIPTION}">
    <meta name="og:image" content="og_image.png">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>

<Layout></Layout>