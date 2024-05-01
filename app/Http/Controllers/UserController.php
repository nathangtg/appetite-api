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
        // Get all users
        $users = User::all();
        return $users;
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Display the user
        return $user;
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
    public function destroy(User $user)
    {
        // Delete the user
        $user->delete();
        return response()->json(null, 204);
    }
}
