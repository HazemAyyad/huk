<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Review;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('index', [
            'reviews' => Review::where('status', true)->get(),
        ]);
    }

    public function about()
    {
        return view('about-us.index');
    }

    public function getGovernorates()
    {
        return response()->json(Governorate::all(), 200);
    }

    public function getCities($id)
    {
        return response()->json(City::where('governorate_id', $id)->get(), 200);
    }

    public function getAreas($id)
    {
        return response()->json(Area::where('city_id', $id)->get(), 200);
    }

    public function reviewIndex()
    {
        return view('reviews.index', [
            'reviews' => Review::where('status', true)->get(),
        ]);
    }

    public function reviewStore(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'name' => 'required|string',
        ]);

        Review::create($validated);
        return response()->json('Done', 200);
    }
}
