import './bootstrap';
import bootstrap from 'bootstrap/js/index.umd';
import { Styles } from '@sveltestrap/sveltestrap';

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

Date.prototype.addYear = function() {
    var date = new Date(this.valueOf());
    date.setFullYear(date.getFullYear() + 1);
    return date;
}

Date.prototype.addYears = function(years) {
    var date = new Date(this.valueOf());
    date.setFullYear(date.getFullYear() + years);
    return date;
}

Date.prototype.subYear = function() {
    var date = new Date(this.valueOf());
    date.setFullYear(date.getFullYear() - 1);
    return date;
}

Date.prototype.subYears = function(years) {
    var date = new Date(this.valueOf());
    date.setFullYear(date.getFullYear() - years);
    return date;
}

Date.prototype.addMonth = function() {
    var date = new Date(this.valueOf());
    date.setMonth(date.getMonth() + 1);
    return date;
}

Date.prototype.addMonths = function(months) {
    var date = new Date(this.valueOf());
    date.setMonth(date.getMonth() + months);
    return date;
}

Date.prototype.subMonth = function() {
    var date = new Date(this.valueOf());
    date.setMonth(date.getMonth() - 1);
    return date;
}

Date.prototype.subMonths = function(months) {
    var date = new Date(this.valueOf());
    date.setMonth(date.getMonth() - months);
    return date;
}

Date.prototype.startOfMonth = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(1);
    return date;
}

Date.prototype.endOfMonth = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(new Date(date.getFullYear, date.getMonth + 1, 0).getDate());
    return date;
}

Date.prototype.addDay = function() {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + 1);
    return date;
}

Date.prototype.addDays = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
}

Date.prototype.subDay = function() {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() - 1);
    return date;
}

Date.prototype.subDays = function(days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() - days);
    return date;
}

Date.prototype.startOfDay = function() {
    var date = new Date(this.valueOf());
    date.setHours(0, 0, 0, 0);
    return date;
}

Date.prototype.endOfDay = function() {
    var date = new Date(this.valueOf());
    date.setHours(23, 59, 59, 999);
    return date;
}

Date.prototype.addHour = function() {
    var date = new Date(this.valueOf());
    date.setHours(date.getHours() + 1);
    return date;
}

Date.prototype.addHours = function(hours) {
    var date = new Date(this.valueOf());
    date.setHours(date.getHours() + hours);
    return date;
}

Date.prototype.subHour = function() {
    var date = new Date(this.valueOf());
    date.setHours(date.getHours() - 1);
    return date;
}

Date.prototype.subHours = function(hours) {
    var date = new Date(this.valueOf());
    date.setHours(date.getHours() - hours);
    return date;
}

Date.prototype.addMinute = function() {
    var date = new Date(this.valueOf());
    date.setMinutes(date.getMinutes() + 1);
    return date;
}

Date.prototype.addMinutes = function(minutes) {
    var date = new Date(this.valueOf());
    date.setMinutes(date.getMinutes() + minutes);
    return date;
}

Date.prototype.subMinute = function() {
    var date = new Date(this.valueOf());
    date.setMinutes(date.getMinutes() - 1);
    return date;
}

Date.prototype.subMinutes = function(minutes) {
    var date = new Date(this.valueOf());
    date.setMinutes(date.getMinutes() - minutes);
    return date;
}

Date.prototype.startOfMinute = function() {
    var date = new Date(this.valueOf());
    date.setHours(0);
    return date;
}

Date.prototype.endOfMinute = function() {
    var date = new Date(this.valueOf());
    date.setSeconds(59);
    return date;
}

import { createInertiaApp } from '@inertiajs/svelte';
import { hydrate, mount } from 'svelte';

createInertiaApp({
	resolve: (name) => {
		const pages = import.meta.glob("./Pages/**/*.svelte", { eager: true });
		let page = pages[`./Pages/${name}.svelte`];
		return { default: page.default };
	},
	setup({ el, App, props }) {
        if (el.dataset.serverRendered === 'true') {
            hydrate(App, { target: el, props })
        } else {
            mount(App, { target: el, props })
        }
	},
});
