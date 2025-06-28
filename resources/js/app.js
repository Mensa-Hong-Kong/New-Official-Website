import './bootstrap';
import bootstrap from 'bootstrap/js/index.umd';
import { Styles } from '@sveltestrap/sveltestrap';

// fix multiple levels of nested dropdowns do not collapse when the parent of the current expanded level is clicked
// source form: https://jsfiddle.net/dallaslu/mvk4uhzL/
(
    function($bs) {
        const CLASS_NAME = 'has-child-dropdown-show';
        $bs.Dropdown.prototype.toggle = function(_orginal) {
            return function() {
                document.querySelectorAll(
                    '.' + CLASS_NAME).forEach(function(e) {
                        e.classList.remove(CLASS_NAME);
                    }
                );
                let dd = this._element.closest('.dropdown').parentNode.closest('.dropdown');
                for (; dd && dd !== document; dd = dd.parentNode.closest('.dropdown')) {
                    dd.classList.add(CLASS_NAME);
                }
                return _orginal.call(this);
            }
        }($bs.Dropdown.prototype.toggle);

        document.querySelectorAll('.dropdown').forEach(
            function(dd) {
                dd.addEventListener('hide.bs.dropdown', function(e) {
                    if (this.classList.contains(CLASS_NAME)) {
                        this.classList.remove(CLASS_NAME);
                        e.preventDefault();
                    }
                    e.stopPropagation(); // do not need pop in multi level mode
                });
            }
        );

        // for hover
        document.querySelectorAll('.dropdown-hover, .dropdown-hover-all .dropdown').forEach(
            function(dd) {
                dd.addEventListener('mouseenter', function(e) {
                    let toggle = e.target.querySelector(':scope>[data-bs-toggle="dropdown"]');
                    if (!toggle.classList.contains('show')) {
                        $bs.Dropdown.getOrCreateInstance(toggle).toggle();
                        dd.classList.add(CLASS_NAME);
                        $bs.Dropdown.clearMenus();
                    }
                }
            );
            dd.addEventListener(
                'mouseleave', function(e) {
                    let toggle = e.target.querySelector(':scope>[data-bs-toggle="dropdown"]');
                    if (toggle.classList.contains('show')) {
                        $bs.Dropdown.getOrCreateInstance(toggle).toggle();
                    }
                }
            );
        });
    }
)(bootstrap);

// add ucfirst php function to js
Object.defineProperty(String.prototype, 'ucfirst', {
    value: function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    },
    enumerable: false
});

// add range php function to js
window.range = function(start, stop, step = 1) {
    return Array.from({ length: (stop - start) / step + 1 }, (_, index) => start + index * step);
}

import { createInertiaApp } from '@inertiajs/svelte';
import { hydrate, mount } from 'svelte';
import Layout from "@/Pages/Layouts/App.svelte";

createInertiaApp({
	resolve: (name) => {
		const pages = import.meta.glob("./Pages/**/*.svelte", { eager: true });
		let page = pages[`./Pages/${name}.svelte`];
		return { default: page.default, layout: page.layout || Layout };
	},
	setup({ el, App, props }) {
        if (el.dataset.serverRendered === 'true') {
            hydrate(App, { target: el, props })
        } else {
            mount(App, { target: el, props })
        }
	},
});
