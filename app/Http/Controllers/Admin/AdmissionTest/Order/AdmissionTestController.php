<?php

namespace App\Http\Controllers\Admin\AdmissionTest\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTest\Order\TestRequest;
use App\Models\AdmissionTest;
use App\Models\AdmissionTestHasCandidate;
use App\Models\AdmissionTestOrder;
use App\Notifications\AdmissionTest\Admin\AssignAdmissionTest;
use App\Notifications\AdmissionTest\Admin\CanceledAdmissionTestAppointment;
use App\Notifications\AdmissionTest\Admin\RemovedAdmissionTestRecord;
use App\Notifications\AdmissionTest\Admin\RescheduleAdmissionTest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AdmissionTestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            (new Middleware([
                'permission:Edit:Admission Test Order',
                'permission:Edit:Admission Test Candidate',
            ])),
            (new Middleware(
                function (Request $request, Closure $next) {
                    $pivot = AdmissionTestHasCandidate::where('test_id', $request->route('admission_test')->id)
                        ->where('order_id', $request->route('order')->id)
                        ->firstOrFail();
                    $request->merge(['pivot' => $pivot]);

                    return $next($request);
                }
            ))->only('destroy'),
        ];
    }

    public function store(TestRequest $request, AdmissionTestOrder $order)
    {
        DB::beginTransaction();
        $request->test->candidates()->attach(
            $order->user->id,
            ['order_id' => $order->id]
        );
        switch ($request->function) {
            case 'schedule':
                $order->user->notify(new AssignAdmissionTest($request->test));
                break;
            case 'reschedule':
                $oldTest = clone $order->user->lastAdmissionTest;
                $order->user->lastAdmissionTest->delete();
                $order->user->notify(new RescheduleAdmissionTest($oldTest, $order->test));
                break;
        }
        DB::commit();

        return [
            'success' => 'The candidate create success',
            'id' => $request->test->id,
            'type' => $request->test->type->name,
            'testing_at' => $request->test->testing_at,
            'location' => $request->test->location->name,
        ];
    }

    public function destroy(Request $request, AdmissionTestOrder $order, AdmissionTest $admissionTest)
    {
        DB::beginTransaction();
        $order->tests()->detach($admissionTest->id);
        if ($request->pivot->is_passed === null) {
            $order->user->notify(new CanceledAdmissionTestAppointment($admissionTest));
        } else {
            $order->user->notify(new RemovedAdmissionTestRecord($admissionTest, $request->pivot));
        }
        DB::commit();

        return ['success' => 'The admission test delete success!'];
    }
}
