// src/lib/utils/permissions.js or similar
import { page } from '@inertiajs/svelte';
import { get } from 'svelte/store';

export function can(permissions) {
    if (get(page).props.auth.user.roles.includes('Super Administrator')) {
        return true;
    }

    if (Array.isArray(permissions)) {
        return permissions.filter(
            function (permission) {
                return get(page).props.auth.user.permissions.includes(permission);
            }
        ).length == permissions.length;
    }

    return get(page).props.auth.user.permissions.includes(permissions);
}

export function cant(permissions) {
    return ! can(permissions);
}

export function cannot(permissions) {
    return ! can(permissions);
}

export function canAny(permissions) {
    return permissions.some(
        permission => get(page).props.auth.user.permissions.includes(permission)
    ) || get(page).props.auth.user.roles.includes('Super Administrator');
}

export function role(permissions) {
    if (get(page).props.auth.user.roles.includes('Super Administrator')) {
        return true;
    }

    if (Array.isArray(roles)) {
        return roles.filter(
            function (role) {
                return get(page).props.auth.user.roles.includes(role);
            }
        ).length == roles.length;
    }

    return get(page).props.auth.user.roles.includes(roles);
}

export function hasAnyRole(permissions) {
    return roles.some(role => get(page).props.auth.user.roles.includes(role)) ||
        get(page).props.auth.user.roles.includes('Super Administrator');
}
