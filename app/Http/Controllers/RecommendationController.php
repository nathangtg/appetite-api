<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get authenticated user
        $user = auth()->user();

        // Fetch the restaurants with the highest average rating in the preferred cuisine
        $restaurants = Restaurant::select('restaurants.*')
            ->leftJoin('ratings', 'restaurants.id', '=', 'ratings.restaurant_id')
            ->where('restaurants.cuisine', $user->preferred_cuisine)
            ->where('restaurants.is_open', true)
            ->groupBy('restaurants.id')
            ->havingRaw('COALESCE(AVG(ratings.rating), 0) >= ?', [1])
            ->orderByRaw('COALESCE(AVG(ratings.rating), 0) DESC')
            ->limit(5)
            ->get();

        return response()->json($restaurants);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        //
    }
}
