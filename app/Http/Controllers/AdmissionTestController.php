<?php

namespace App\Http\Controllers;

use App\Models\AdmissionTest;

class AdmissionTestController extends Controller
{
    public function index()
    {
        return view('admission-tests.index')
            ->with(
                'tests', AdmissionTest::where('testing_at', '>=', now())
                    ->where(
                        function ($query) {
                            $query->where('is_public', true);
                            $user = auth()->user();
                            if ($user) {
                                $query->orWhereHas(
                                    'candidates', function ($query) {
                                        $query->where('user_id', auth()->user()->id);
                                    }
                                );
                            }
                        }
                    )->orderBy('testing_at')
                    ->withCount('candidates')
                    ->get()
            );
    }
}
