<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class CityController extends Controller
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
        $items = City::with(['governorate'])->paginate(10);
        return response()->json($items, 200);
    }

    public function getGovernorates()
    {
        return response()->json(Governorate::all(['id', 'name']));
    }

    public function getItem($id)
    {
        return response()->json(City::with(['governorate'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validation = $request->validate([
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id'
        ]);

        City::create($validation);
        return response()->json(200);
    }

    public function update(Request $request, $id)
    {
        $validation = $request->validate([
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id'
        ]);

        City::findOrFail($id)->update($validation);
        return response()->json(200);
    }

    public function destroy($id)
    {
        City::findOrFail($id)->delete();
        return response()->json(200);
    }
}
