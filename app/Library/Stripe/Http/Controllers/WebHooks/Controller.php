<?php

namespace App\Library\Stripe\Http\Controllers\WebHooks;

use App\Library\Stripe\Http\Middleware\Webhooks\VerifySignature;
use App\Library\Stripe\Jobs\CreateCustomer;
use App\Library\Stripe\Models\StripeCustomer;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;

class Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [VerifySignature::class];
    }

    protected function success()
    {
        return 'Webhook Handled';
    }

    protected function customerDeleted(Request $request)
    {
        $request->validate(['data.object.id' => 'required|string']);
        $customer = StripeCustomer::with('customerable')
            ->find($request['data']['object']['id']);
        if ($customer) {
            DB::beginTransaction();
            $customerable = $customer->customerable;
            if ($customerable) {
                CreateCustomer::dispatch(
                    $customer->customerable_type,
                    $customer->customerable_id,
                );
            }
            $customer->delete();
            DB::commit();
        }

        return $this->success();
    }

    public function handle(Request $request)
    {
        switch ($request->post('type')) {
            case 'customer.deleted':
                return $this->customerDeleted($request);
            default:
                return;
        }
    }
}
