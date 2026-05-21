<?php

use App\Providers\AppServiceProvider;
use Inertia\ServiceProvider;
use Kyslik\ColumnSortable\ColumnSortableServiceProvider;
use Laravel\Boost\BoostServiceProvider;
use Reedware\LaravelRelationJoins\LaravelRelationJoinServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Tighten\Ziggy\ZiggyServiceProvider;

return [
    AppServiceProvider::class,
    PermissionServiceProvider::class,
    ColumnSortableServiceProvider::class,
    ZiggyServiceProvider::class,
    ServiceProvider::class,
    LaravelRelationJoinServiceProvider::class,
    BoostServiceProvider::class,
];
