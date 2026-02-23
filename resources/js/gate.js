// src/lib/utils/permissions.js or similar
import { page } from '@inertiajs/svelte';
import { derived } from 'svelte/store';

export const can = derived(
    page, ($page) => (permissions) => {
        if ($page.props.auth.user.roles.includes('Super Administrator')) {
            return true;
        }

        if (Array.isArray(permissions)) {
            return permissions.filter(
                function (permission) {
                    return $page.props.auth.user.permissions.includes(permission);
                }
            ).length == permissions.length;
        }

        return $page.props.auth.user.permissions.includes(permissions);
    }
);

export const canAny = derived(
    page, ($page) => (permissions) => {
        return permissions.some(
            permission => $page.props.auth.user.permissions.includes(permission)
        ) || $page.props.auth.user.roles.includes('Super Administrator');
    }
);

export const role = derived(
    page, ($page) => (permissions) => {
        if ($page.props.auth.user.roles.includes('Super Administrator')) {
            return true;
        }

        if (Array.isArray(roles)) {
            return roles.filter(
                function (role) {
                    return $page.props.auth.user.roles.includes(role);
                }
            ).length == roles.length;
        }

        return $page.props.auth.user.roles.includes(roles);
    }
);

export const hasAnyRole = derived(
    page, ($page) => (permissions) => {
        return roles.some(role => $page.props.auth.user.roles.includes(role)) ||
            $page.props.auth.user.roles.includes('Super Administrator');
    }
);
