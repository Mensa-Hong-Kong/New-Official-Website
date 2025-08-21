<script>
    import "ckeditor5/ckeditor5.css";
    
    let {content} = $props();
</script>
<section class="container">
    <article class="ck-content">
        {@html content}
    </article>
</section>