<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
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
        return response()->json(Program::paginate(10));
    }

    public function getItem($id)
    {
        return response()->json(Program::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validation = $request->validate([
            'name'        => 'required|string|max:255'
        ]);

        Program::create($validation);
        return response()->json(200);
    }

    public function update(Request $request, $id)
    {
        $validation = $request->validate([
            'name'        => 'required|string|max:255'
        ]);

        Program::findOrFail($id)->update($validation);
        return response()->json(200);
    }

    public function destroy($id)
    {
        Program::findOrFail($id)->delete();
        return response()->json(200);
    }
}
