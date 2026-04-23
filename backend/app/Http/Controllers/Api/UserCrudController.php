<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCrudController extends Controller
{
    // CREATE
    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'staff',
        ]);

        return response()->json($user);
    }

    // READ ALL
    public function index()
    {
        return response()->json(User::all());
    }

    // READ SINGLE
    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'role' => $request->role ?? $user->role,
            'password' => $request->password 
                ? Hash::make($request->password) 
                : $user->password,
        ]);

        return response()->json($user);
    }

    // DELETE
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }
}