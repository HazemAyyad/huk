<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Governorate;
use Illuminate\Http\Request;

class GovernorateController extends Controller
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
        return response()->json(Governorate::with(['cities'])->paginate(10));
    }

    public function getItem($id)
    {
        return response()->json(Governorate::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validation = $request->validate([
            'name'        => 'required|string|max:255'
        ]);

        Governorate::create($validation);
        return response()->json(200);
    }

    public function update(Request $request, $id)
    {
        $validation = $request->validate([
            'name'        => 'required|string|max:255'
        ]);

        Governorate::findOrFail($id)->update($validation);
        return response()->json(200);
    }

    public function destroy($id)
    {
        Governorate::findOrFail($id)->delete();
        return response()->json(200);
    }
}
