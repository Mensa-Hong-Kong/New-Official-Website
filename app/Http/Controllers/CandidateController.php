<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;
use App\Notifications\AdmissionTest\RescheduleAdmissionTest;
use App\Notifications\AdmissionTest\ScheduleAdmissionTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CandidateController extends Controller
{
    public function store(Request $request, AdmissionTest $admissionTest)
    {
        $user = $request->user();
        DB::beginTransaction();
        $admissionTest->candidates()->attach($user->id);
        if($request->futureTest) {
            $oldTest = clone $request->futureTest;
            $oldTest->delete();
            $user->notify(new RescheduleAdmissionTest($request->futureTest, $admissionTest));
            $success = 'Your reschedule request successfully, the new ticket will be to your default contact(s).';
        } else {
            $user->notify(new ScheduleAdmissionTest($admissionTest));
            $success = 'Your schedule request successfully, the ticket will be to your default contact(s).';
        }
        DB::commit();

        return redirect()->route('admission-tests.index')
            ->with('success', $success);
    }
}
