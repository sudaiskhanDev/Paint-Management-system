<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // READ all
    public function index() {
        return response()->json(User::all());
    }

    // READ single
    public function show($id) {
        $user = User::find($id);
        if(!$user) return response()->json(['message'=>'User not found'], 404);
        return response()->json($user);
    }

    // UPDATE
    public function update(Request $request, $id) {
        $user = User::find($id);
        if(!$user) return response()->json(['message'=>'User not found'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|min:8',
            'role' => 'sometimes|in:admin,staff'
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        if($request->has('name')) $user->name = $request->name;
        if($request->has('email')) $user->email = $request->email;
        if($request->has('password')) $user->password = Hash::make($request->password);
        if($request->has('role')) $user->role = $request->role;

        $user->save();
        return response()->json($user);
    }

    // DELETE
    public function destroy($id){
        $user = User::find($id);
        if(!$user) return response()->json(['message'=>'User not found'], 404);
        $user->delete();
        return response()->json(['message'=>'User deleted successfully']);
    }
}