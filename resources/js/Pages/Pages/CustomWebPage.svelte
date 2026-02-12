<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import "ckeditor5/ckeditor5.css";

    let { page } = $props();

    seo.title = page.title;
    if(page.description) {
        seo.description = page.description;
    }
    if(page.og_image_url) {
        seo.ogImageUrl = page.og_image_url;
    }
</script>

<section class="container">
    <article class="ck-content">
        {@html page.content}
    </article>
</section>
