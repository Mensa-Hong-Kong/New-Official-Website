import './bootstrap';
import bootstrap from 'bootstrap/js/index.umd';

// add ucfirst php function to js
Object.defineProperty(String.prototype, 'ucfirst', {
    value: function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    },
    enumerable: false
});

// add range php function to js
window.range = function(start, end, step = undefined) {
    let array = [];
    let isChar = false;

    if (start === '') start = 0;
    if (end === '') end = 0;

    if (typeof start === 'string' || typeof end === 'string') {
        if (typeof start !== 'string') {
            end = 0;
        } else if (typeof end !== 'string') {
            start = 0;
        } else {
            if (step !== undefined && ! Number.isInteger(Number(step))) {
                throw new Error("Invalid value provided");
            }
            isChar = true;
        }
    }

    let current = isChar ? start.charCodeAt(0) : start;
    const last = isChar ? end.charCodeAt(0) : end;

    const isDescending = current > last;
    if (
        step !== undefined && (
            ! isChar || (
                current >= 65 && current <= 90 &&
                last >= 65 && last <= 90
            ) || (
                current >= 97 && current <= 122 &&
                last >= 97 && last <= 122
            )
        ) && (
            (isDescending && step > 0) ||
            (! isDescending && step < 0)
        )
    ) {
        throw new Error("Invalid value provided");
    }
    if (step === undefined) {
        step = 1;
    } else {
        step = Number(step);
    }
    if (step === 0 || Number.isNaN(step) || ! Number.isFinite(step)) {
        throw new Error("Invalid value provided");
    }

    while (isDescending ? current >= last : current <= last) {
        array.push(isChar ? String.fromCharCode(current) : current);
        current = isDescending ? current - step : current + step;
    }
    return array;
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

Array.prototype.shuffle = function() {
    let max = this.length;
    let tempItem;
    let index;
    // 當還有元素需要洗牌時
    while (max) {
        // 隨機選擇一個剩餘元素
        index = Math.floor(Math.random() * max--);
        // 與當前元素交換
        tempItem = this[max];
        this[max] = this[index];
        this[index] = tempItem;
    }

    return this;
};

import { createInertiaApp } from '@inertiajs/svelte';
import { hydrate, mount } from 'svelte';
import Layout from "@/Pages/Layouts/App.svelte";

createInertiaApp({
	resolve: (name) => {
		const pages = import.meta.glob("./Pages/**/*.svelte", { eager: true });
		let page = pages[`./Pages/${name}.svelte`];
		return { default: page.default, layout: Layout };
	},
	setup({ el, App, props }) {
        if (el.dataset.serverRendered === 'true') {
            hydrate(App, { target: el, props })
        } else {
            mount(App, { target: el, props })
        }
	},
});
