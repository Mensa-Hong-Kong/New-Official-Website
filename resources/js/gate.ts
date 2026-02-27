import { page } from '@inertiajs/svelte';
import { get } from 'svelte/store';

export function can(permissions: string | string[]):boolean {
    if (get(page).props.auth.user.roles.includes('Super Administrator')) {
        return true;
    }

    if (! Array.isArray(permissions)) {
        permissions = permissions.split("|");
    }

    return permissions.filter(
        function (permission) {
            return get(page).props.auth.user.permissions.includes(permission);
        }
    ).length == permissions.length;
}

export function cant(permissions: string | string[]):boolean {
    return ! can(permissions);
}

export function cannot(permissions: string | string[]):boolean {
    return ! can(permissions);
}

export function canAny(permissions: string[]):boolean {
    return permissions.some(
        permission => get(page).props.auth.user.permissions.includes(permission)
    ) || get(page).props.auth.user.roles.includes('Super Administrator');
}

export function role(roles: string | string[]):boolean {
    if (get(page).props.auth.user.roles.includes('Super Administrator')) {
        return true;
    }

    if (! Array.isArray(roles)) {
        roles = roles.split("|");
    }

    return roles.filter(
        function (role) {
            return get(page).props.auth.user.roles.includes(role);
        }
    ).length == roles.length;
}

export function hasAnyRole(roles : string[]):boolean {
    return roles.some(role => get(page).props.auth.user.roles.includes(role)) ||
        get(page).props.auth.user.roles.includes('Super Administrator');
}
