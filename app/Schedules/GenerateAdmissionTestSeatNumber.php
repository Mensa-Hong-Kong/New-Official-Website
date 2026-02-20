<?php

namespace App\Schedules;

use App\Models\AdmissionTestHasCandidate;
use Illuminate\Support\Facades\DB;

class GenerateAdmissionTestSeatNumber
{
    public function __invoke()
    {
        $testHasCandidateIDs = AdmissionTestHasCandidate::whereHas(
            'test', function($query) {
                $query->whereDate('testing_at', today());
            }
        )->get()
        ->groupBy('test_id')
        ->map(
            function($rows) {
                return $rows->shuffle()->pluck('id');
            }
        );
        $IDs = [];
        $seatNumberCase = [];
        foreach($testHasCandidateIDs as $testID => $candidatesIDs) {
            foreach($candidatesIDs as $index => $candidatesID) {
                $seatNumberCase[] = "WHEN id = $candidatesID THEN ".($index + 1);
                $IDs[] = $candidatesID;
            }
        }
        if (count($IDs)) {
            $seatNumberCase = implode(' ', $seatNumberCase);
            AdmissionTestHasCandidate::whereIn('id', $IDs)
                ->update([
                    'seat_number' => DB::raw("(CASE $seatNumberCase ELSE seat_number END)"),
                ]);
        }
    }
}
