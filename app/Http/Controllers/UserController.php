<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // ! Check if the user is an admin
        if (Auth::user()->account_type !== 'user') {
            return response()->json(['error' => 'Unauthorized'], 403);
        } else {
            $users = User::all();
        }

        return $users;
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        // Check if the user is authenticated
        if ($request->user()) {
            // Get the user ID from the request parameters
            $id = $request->route('id');

            // Check if the ID from the request parameters matches the authenticated user's ID
            if ($request->user()->id == $id) {
                // If the IDs match, return the user ID
                return $request->user();
            }
        }

        // If the user is not authenticated or the IDs don't match, return an error response
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the authenticated user is the same as the user being updated
        if (!$user || $user->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
        ]);

        // Update the user's name, if provided
        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }

        // Update the user's email, if provided
        if (isset($validatedData['email'])) {
            $user->email = $validatedData['email'];
        }

        // Update the user's password, if provided
        if (isset($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        // Save the updated user
        $user->save();
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Check if the user is authenticated
        if ($request->user()) {
            // Get the user ID from the request parameters
            $id = $request->route('id');

            // Check if the ID from the request parameters matches the authenticated user's ID
            if ($request->user()->id == $id) {
                // If the IDs match, delete the user
                User::destroy($id);
                return response()->json(null, 204);
            }
        }

        // If the user is not authenticated or the IDs don't match, return an unauthorized response
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
