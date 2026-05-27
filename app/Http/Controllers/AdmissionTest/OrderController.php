<?php

namespace App\Http\Controllers\AdmissionTest;

use App\Http\Controllers\Controller;
use App\Models\AdmissionTestOrder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                function (Request $request, Closure $next) {
                    if ($request->route('order')->user_id != $request->user()->id) {
                        abort(403);
                    }
                    if (in_array($request->route('order')->status, ['succeeded', 'partial refunded', 'full refunded'])) {
                        return redirect()->route('admission-tests.index')
                            ->withErrors(['message' => 'This order has already been completed. Your access is active, no further action is required.']);
                    }

                    return $next($request);
                }
            ),
        ];
    }

    public function leavingPayment(AdmissionTestOrder $order)
    {
        if ($order->status == 'pending') {
            $order->update(['status' => 'canceled']);
        }

        return Inertia::render('AdmissionTest/Orders/LeavingPayment');
    }
}
