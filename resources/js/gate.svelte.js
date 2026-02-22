let user = $state({
    roles: [],
    permissions: [],
});

export function setup(auth) {
    user.roles = auth.user?.roles ?? [];
    user.permissions = auth.user?.permissions ?? [];
}

export function can(permissions) {
    if (user.roles.includes('Super Administrator')) {
        return true;
    }

    if (Array.isArray(permissions)) {
        return permissions.filter(
            function (permission) {
                return user.permissions.includes(permission);
            }
        ).length == permissions.length;
    }

    return user.permissions.includes(permissions);
}

export function canAny(permissions) {
    return permissions.some(permission => user.permissions.includes(permission)) ||
        user.roles.includes('Super Administrator');
}

export function cant(permissions) {
    return ! can(permissions);
}

export function cannot(permissions) {
    return ! can(permissions);
}

export function role(roles) {
    if (user.roles.includes('Super Administrator')) {
        return true;
    }

    if (Array.isArray(roles)) {
        return roles.filter(
            function (role) {
                return user.roles.includes(role);
            }
        ).length == roles.length;
    }

    return user.roles.includes(roles);
}

export function hasAnyRole(roles) {
    return roles.some(role => user.roles.includes(role)) ||
        user.roles.includes('Super Administrator');
}
