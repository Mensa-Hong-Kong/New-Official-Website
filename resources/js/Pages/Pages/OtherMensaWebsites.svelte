<script>
    import { seo } from '@/Pages/Layouts/App.svelte';
    import "flag-icons/css/flag-icons.min.css";
    import flagNations from "flag-icons/country.json";
    import { Row, Col } from '@sveltestrap/sveltestrap';
    import { asset } from "@/asset.svelte.js";

    seo.title = 'Other Mensa Websites';
    seo.description = 'Mensa has members in 90+ countries worldwide. There are active Mensa organizations on every continent except Antarctica.';
    seo.ogImageUrl = asset('og_image.png');

    let { nations } = $props();

    function getFlagNationsIndexByName(name) {
        return flagNations.findIndex(
            function(flagNation) {
                return flagNation.name == name;
            }
        );
    }

    function getCodeByName(name) {
        return flagNations[getFlagNationsIndexByName(name)]['code'];
    }
</script>

<section class="container">
    <h2 class="mb-2 fw-bold text-uppercase">Other Mensa Websites</h2>
    <Row class='g-3'>
        <Col md=3 class="text-center">
            <a href="https://www.mensa.org">
                <img height="150" src={asset('mi.png')} alt="Mensa International" /><br>
                Mensa International
            </a>
        </Col>
        {#each nations as nation}
            <Col md=3 class="text-center">
                <a href="{nation.url}">
                    <img height="150" src="{new URL(`/node_modules/flag-icons/flags/4x3/${getCodeByName(nation.name)}.svg`, import.meta.url).href}" alt="{nation.name}" /><br>
                    {nation.name}
                </a>
            </Col>
        {/each}
    </Row>
</section>
