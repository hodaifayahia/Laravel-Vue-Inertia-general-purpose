<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\City;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        $provinces = Province::with('cities')->orderBy('code')->get();
        
        // Check if request wants JSON (for API)
        if (request()->wantsJson()) {
            return response()->json([
                'provinces' => $provinces->map(fn($p) => [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name_en,
                    'name_en' => $p->name_en,
                    'name_ar' => $p->name_ar,
                ]),
                'cities' => City::all()->map(fn($c) => [
                    'id' => $c->id,
                    'province_id' => $c->province_id,
                    'name' => $c->name_en,
                    'name_en' => $c->name_en,
                    'name_ar' => $c->name_ar,
                ]),
                'specializations' => Specialization::all()->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'name_en' => $s->name_en ?? $s->name,
                    'name_ar' => $s->name_ar ?? $s->name,
                ]),
            ]);
        }
        
        return Inertia::render('Locations/Index', compact('provinces'));
    }

    public function storeProvince(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:provinces,code',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $province = Province::updateOrCreate(['code' => $data['code']], $data);
        return redirect()->back()->with('success', 'Province saved.');
    }

    public function storeCity(Request $request)
    {
        $data = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $city = City::updateOrCreate(
            ['province_id' => $data['province_id'], 'name_ar' => $data['name_ar']],
            $data
        );

        return redirect()->back()->with('success', 'City saved.');
    }

    public function destroyProvince(Province $province)
    {
        $province->delete();
        return redirect()->back()->with('success', 'Province removed.');
    }

    public function destroyCity(City $city)
    {
        $city->delete();
        return redirect()->back()->with('success', 'City removed.');
    }
}
