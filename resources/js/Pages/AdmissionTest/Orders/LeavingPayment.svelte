<script>
	import { onMount } from 'svelte';
    import { seo } from '@/Pages/Layouts/App.svelte';
    import { Link, router } from "@inertiajs/svelte";

    seo.title = 'Leaving Payment Page';

	let count = 5; // Set the countdown duration in seconds

	onMount(() => {
		const interval = setInterval(() => {
			if (count > 1) {
				count -= 1;
			} else {
				clearInterval(interval);
				router.visit(route('admission-tests.index'));
			}
		}, 1000);

		// Cleanup the interval when the component is destroyed
		return () => clearInterval(interval);
	});
</script>

<section class="container">
    <h2 class="mb-2 fw-bold">Leaving Payment Page</h2>
    <article>
        <p>Your payment has not been completed yet. You will be redirected to the admission test index in <span>{count}</span> seconds.</p>
        <p>If you aren't redirected automatically, <Link href={route('admission-tests.index')}>click here to return to the index</Link>.</p>
    </article>
</section>
