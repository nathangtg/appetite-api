<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Get all the users
    public function getUsers()
    {
        // Get all the users
        $users = User::all();

        // Return the users as JSON response
        return response()->json(['users' => $users], 200);
    }

    // Get a specific user
    public function getUser($user_id)
    {
        // Get the user
        $user = User::find($user_id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Return the user as JSON response
        return response()->json(['user' => $user], 200);
    }

    // Update a user
    public function updateUser(Request $request, $user_id)
    {
        // Get the user
        $user = User::find($user_id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update the user
        $user->update($request->all());

        // Return the updated user as JSON response
        return response()->json(['user' => $user], 200);
    }

    // Delete a user
    public function deleteUser($user_id)
    {
        // Get the user
        $user = User::find($user_id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Delete the user
        $user->delete();

        // Return a success response
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // Get all the restaurants
    public function getRestaurants()
    {
        // Get all the restaurants
        $restaurants = Restaurant::all();

        // Return the restaurants as JSON response
        return response()->json(['restaurants' => $restaurants], 200);
    }

    // Get a specific restaurant
    public function getRestaurant($restaurant_id)
    {
        // Get the restaurant
        $restaurant = Restaurant::find($restaurant_id);

        // Check if the restaurant exists
        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        // Return the restaurant as JSON response
        return response()->json(['restaurant' => $restaurant], 200);
    }

    // Update a restaurant
    public function updateRestaurant(Request $request, $restaurant_id)
    {
        // Get the restaurant
        $restaurant = Restaurant::find($restaurant_id);

        // Check if the restaurant exists
        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        // Update the restaurant
        $restaurant->update($request->all());

        // Return the updated restaurant as JSON response
        return response()->json(['restaurant' => $restaurant], 200);
    }

    // Delete a restaurant
    public function deleteRestaurant($restaurant_id)
    {
        // Get the restaurant
        $restaurant = Restaurant::find($restaurant_id);

        // Check if the restaurant exists
        if (!$restaurant) {
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        // Delete the restaurant
        $restaurant->delete();

        // Return a success response
        return response()->json(['message' => 'Restaurant deleted successfully'], 200);
    }

    // Get all the menus
    public function getMenus()
    {
        // Get all the menus
        $menus = Menu::all();

        // Return the menus as JSON response
        return response()->json(['menus' => $menus], 200);
    }

    // Get a specific menu
    public function getMenu($menu_id)
    {
        // Get the menu
        $menu = Menu::find($menu_id);

        // Check if the menu exists
        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        // Return the menu as JSON response
        return response()->json(['menu' => $menu], 200);
    }

    // Update a menu
    public function updateMenu(Request $request, $menu_id)
    {
        // Get the menu
        $menu = Menu::find($menu_id);

        // Check if the menu exists
        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        // Update the menu
        $menu->update($request->all());

        // Return the updated menu as JSON response
        return response()->json(['menu' => $menu], 200);
    }

    // Delete a menu
    public function deleteMenu($menu_id)
    {
        // Get the menu
        $menu = Menu::find($menu_id);

        // Check if the menu exists
        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        // Delete the menu
        $menu->delete();

        // Return a success response
        return response()->json(['message' => 'Menu deleted successfully'], 200);
    }
}
