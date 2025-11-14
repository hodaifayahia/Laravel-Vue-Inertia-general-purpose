<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        $provinces = Province::with('cities')->orderBy('code')->get();
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
