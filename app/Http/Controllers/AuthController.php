<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use App\Http\Requests\AdminAddRequest;
use App\Http\Resources\AdminsResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;

class AuthController extends Controller
{
    public function create(AdminAddRequest $request){
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'programmer'
        ]);
        return response()->json([
            'success' => true,
            'message' =>'Successfully created'
        ],201);
    }

    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
        if(!$user or !Hash::check($request->password, $user->password)){
            return response()->json([
                'success' =>false,
                'message' =>"Invalid password or email",
            ],404);
        }
        $token = $user->createToken('user')->plainTextToken;
        return response()->json([
            'success' =>true,
            'token' =>$token
        ]);
    }
    public function update(Request $request,User $user){
        if($user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' =>Hash::make($request->password) ?? $user->password
        ])) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully updated'
            ],200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Update failed'
            ]);
        }
    }
    public function getme(){
        $user = auth()->user();
        return $user;
    }
    public function logOut(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response([
            'success' =>true,
            'message' => "You successfully logged out",
        ]);
    }

    public function delete(User $user){
        if($user){
            $user->delete();
            return response()->json([
                'success' => true,
                'message' =>'Successfully deleted'
            ]);
        }
    }

    public function all(Request $request){
        $users = User::paginate($request->get('per_page', 10));
        $collection  = [
            'last_page' =>$users->lastPage(),
            'users' => []
        ];
        foreach($users as $user){
            $collection['users'][] = new AdminsResource($user);
        }
        return response()->json([
            'success' => true,
            'data' => $collection
        ]);
    }
}
