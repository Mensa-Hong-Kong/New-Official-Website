<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdmissionTestRequest;
use App\Models\Address;
use App\Models\AdmissionTest;
use App\Models\Area;
use App\Models\Location;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AdmissionTestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Admission Test'))];
    }

    public function index()
    {
        return view('admin.admission-tests.index')
            ->with(
                'tests', AdmissionTest::sortable('testing_at')
                    ->paginate()
            );
    }

    public function create()
    {
        $areas = Area::with([
            'districts' => function ($query) {
                $query->orderBy('display_order');
            },
        ])->orderBy('display_order')
            ->get();
        $districts = [];
        foreach ($areas as $area) {
            $districts[$area->name] = [];
            foreach ($area->districts as $district) {
                $districts[$area->name][$district->id] = $district->name;
            }
        }

        return view('admin.admission-tests.create')
            ->with(
                'locations', Location::distinct()
                    ->get('name')
                    ->pluck('name')
                    ->toArray()
            )->with('districts', $districts)
            ->with(
                'addresses', Address::distinct()
                    ->get('address')
                    ->pluck('address')
                    ->toArray()
            );
    }

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
        $test = AdmissionTest::create([
            'testing_at' => $request->testing_at,
            'location_id' => $location->id,
            'maximum_candidates' => $request->maximum_candidates,
            'is_public' => $request->is_public,
        ]);
        DB::commit();

        return redirect()->route(
            'admin.admission-tests.show',
            ['admission_test' => $test]);
    }

    public function show(AdmissionTest $admissionTest)
    {
        return view('admin.admission-tests.show')
            ->with('test', $admissionTest);
    }
}
