<?php

namespace App\Http\Controllers\WebHooks;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Webhocks\Stripe\VerifySignature;
use App\Library\Stripe\Models\StripeCustomer;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;

class StripeController extends Controller implements HasMiddleware
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
        $customer = StripeCustomer::find($request['data']['object']['id']);
        if ($customer) {
            try {
                DB::beginTransaction();
                $customerable = $customer->customerable;
                $customer->delete();
                $customerable->stripeCreate();
                DB::commit();
            } catch (RequestException $e) {
                DB::rollBack();
                abort(500, $e->getMessage());
            }
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
