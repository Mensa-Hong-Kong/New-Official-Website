<script>
    import { asset } from "@/asset.svelte.js";

    let meta = $state({
        title: '',
        description: import.meta.env.VITE_APP_DESCRIPTION,
        ogImage: asset('og_image.png'),
    });

    export function setTitle(title) {
        meta.title = title;
    }

    export function setDescription(description) {
        meta.description = description;
    }

    export function setOGImage(ogImage) {
        meta.ogImage = ogImage;
    }
</script>

<svelte:head>
    <title>{meta.title ? `${meta.title} | ` : ''}{import.meta.env.VITE_APP_NAME}</title>
    <meta name="title" content="{meta.title ? `${meta.title} | ` : ''}{import.meta.env.VITE_APP_NAME}">
    <meta name="description" content="{meta.description}">
    <meta name="og:description" content="{meta.description}">
    <meta name="og:image" content="{meta.ogImage}">
    <meta name="og:url" content="{import.meta.env.VITE_APP_URL}">
    <meta name="og:site_name" content="{import.meta.env.VITE_APP_NAME}">
</svelte:head>