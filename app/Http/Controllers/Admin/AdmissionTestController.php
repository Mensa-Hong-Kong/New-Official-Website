<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTestRequest;
use App\Models\Address;
use App\Models\AdmissionTest;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class AdmissionTestController extends Controller
{
    public function store(AdmissionTestRequest $request)
    {
        DB::beginTransaction();
        $address = Address::firstOrCreate([
            'district_id' => $request->district_id,
            'address' => $request->address,
        ]);
        $location = Location::firstOrCreate([
            'address_id' => $address->id,
            'name' => $request->location,
        ]);
        AdmissionTest::create([
            'testing_at' => $request->testing_at,
            'location_id' => $location->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_public' => $request->is_public,
        ]);
        DB::commit();

        return redirect()->route('admin.index');
    }
}
