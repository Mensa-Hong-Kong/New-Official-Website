<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidateRequest;
use App\Models\AdmissionTest;
use App\Notifications\AdmissionTest\RescheduleAdmissionTest;
use App\Notifications\AdmissionTest\ScheduleAdmissionTest;
use Illuminate\Support\Facades\DB;

class CandidateController extends Controller
{
    public function store(CandidateRequest $request, AdmissionTest $admissionTest)
    {
        $user = $request->user();
        DB::beginTransaction();
        $admissionTest->candidates()->attach($user->id);
        switch ($request->function) {
            case 'schedule':
                $user->notify(new ScheduleAdmissionTest($admissionTest));
                break;
            case 'reschedule':
                $oldTest = AdmissionTest::whereNot('id', $admissionTest->id)
                    ->where('testing_at', '>', now())
                    ->first();
                $oldTestForDelete = clone $oldTest;
                $oldTestForDelete->delete();
                $user->notify(new RescheduleAdmissionTest($oldTest, $admissionTest));
                break;
        }
        DB::commit();

        return redirect()->route('admission-tests.index');
    }
}
