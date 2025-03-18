<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;
use App\Notifications\AdmissionTest\RescheduleAdmissionTest;
use App\Notifications\AdmissionTest\ScheduleAdmissionTest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CandidateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                function (Request $request, Closure $next) {
                    $user = $request->user();
                    $now = now();
                    $admissionTest = $request->route('admission_test');
                    $errorReturn = redirect()->back(302, [], route('admission-tests.index'));
                    if (! $request->route('admission_test')->is_public) {
                        return $errorReturn->withErrors(['message' => 'The admission test is private.']);
                    }
                    if ($user->futureAdmissionTest && $user->futureAdmissionTest->id == $admissionTest->id) {
                        return $errorReturn->withErrors(['message' => 'You has already schedule this admission test.']);
                    }
                    if ($user->isActiveMember()) {
                        return $errorReturn->withErrors(['message' => 'You has already been member.']);
                    }
                    if ($user->hasQualificationOfMembership()) {
                        return $errorReturn->withErrors(['message' => 'You has already been qualification for membership.']);
                    }
                    if ($user->hasSamePassportAlreadyQualificationOfMembership()) {
                        return $errorReturn->withErrors(['message' => 'Your passport has already been qualification for membership.']);
                    }
                    if ($user->hasOtherSamePassportUserTested()) {
                        return $errorReturn->withErrors(['message' => 'You other same passport user account tested.']);
                    }
                    if ($user->hasTestedWithinDateRange($admissionTest->testing_at->subMonths(6), $now)) {
                        return $errorReturn->withErrors(['message' => 'You has admission test record within 6 months(count from testing at of this test sub 6 months to now).']);
                    }
                    if (! $user->defaultEmail && ! $user->defaultMobile) {
                        return $errorReturn->withErrors(['message' => 'You must at least has default contact.']);
                    }
                    if ($admissionTest->testing_at <= now()->addDays(2)->endOfDay()) {
                        return $errorReturn->withErrors(['message' => 'Cannot register after than before testing date two days.']);
                    }
                    if ($admissionTest->candidates()->count() >= $admissionTest->maximum_candidates) {
                        return $errorReturn->withErrors(['message' => 'The admission test is fulled.']);
                    }

                    return $next($request);
                }
            ),
        ];
    }

    public function create(AdmissionTest $admissionTest)
    {
        return view('admission-tests.confirmation')
            ->with('test', $admissionTest);
    }

    public function store(Request $request, AdmissionTest $admissionTest)
    {
        $user = $request->user();
        DB::beginTransaction();
        $admissionTest->candidates()->attach($user->id);
        if ($user->futureAdmissionTest) {
            $oldTest = clone $user->futureAdmissionTest;
            $oldTest->delete();
            $user->notify(new RescheduleAdmissionTest($user->futureAdmissionTest, $admissionTest));
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
