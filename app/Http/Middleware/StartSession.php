<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Session\Middleware\StartSession as Base;

class StartSession extends Base
{
    protected $ignoreStoreCurrentRoutes = [
        'login',
        'logout',
    ];

    protected function storeCurrentUrl(Request $request, $session)
    {
        if ($request->isMethod('GET') &&
            $request->route() instanceof Route &&
            ! $request->ajax() &&
            ! $request->prefetch() &&
            ! $request->isPrecognitive() &&
            ! in_array(
                $request->route()->getName(),
                $this->ignoreStoreCurrentRoutes
            )
        ) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }
}
