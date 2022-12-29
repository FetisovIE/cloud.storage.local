<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminController extends UserController
{
    public function listUser($id): User|JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'User is not login'], 403);
        }
        $idAdmin = Auth::id();
        $user = User::find($idAdmin);
        if ($user->role === 'ROLE_ROOT') {
            return parent::listUser($id); // TODO: Change the autogenerated stub
        }
        return response()->json(['message' => 'User is not an administrator!']);
    }

    public function listUsers(): array|JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'User is not login'], 403);
        }
        $idAdmin = Auth::id();
        $user = User::find($idAdmin);
        if ($user->role === 'ROLE_ROOT') {
            return parent::listUsers(); // TODO: Change the autogenerated stub
        }
        return response()->json(['message' => 'User is not an administrator!']);
    }

    public function delete($id): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'User is not login'], 403);
        }
        $idAdmin = Auth::id();
        $user = User::find($idAdmin);
        if ($user->role === 'ROLE_ROOT') {
            return parent::delete($id); // TODO: Change the autogenerated stub
        }
        return response()->json(['message' => 'User is not an administrator!']);
    }

    public function update(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'User is not login'], 403);
        }
        $idAdmin = Auth::id();
        $user = User::find($idAdmin);
        if ($user->role === 'ROLE_ROOT') {
            return parent::update($request); // TODO: Change the autogenerated stub
        }
        return response()->json(['message' => 'User is not an administrator!']);
    }
}
