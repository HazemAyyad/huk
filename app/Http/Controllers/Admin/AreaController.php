<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware(["role:superadmin"]);
    }

    public function index()
    {
        return view('admin.index');
    }

    public function getItems()
    {
        $items = Area::with(['city'])->paginate(10);
        return response()->json($items, 200);
    }

    public function getCities()
    {
        return response()->json(City::all(['id', 'name']));
    }

    public function getItem($id)
    {
        return response()->json(Area::with(['city'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validation = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id'
        ]);

        Area::create($validation);
        return response()->json(200);
    }

    public function update(Request $request, $id)
    {
        $validation = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id'
        ]);

        Area::findOrFail($id)->update($validation);
        return response()->json(200);
    }

    public function destroy($id)
    {
        Area::findOrFail($id)->delete();
        return response()->json(200);
    }
}
