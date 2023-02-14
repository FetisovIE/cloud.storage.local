<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request): ?JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid Credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        setcookie('access_token', $token, time() + 3600);
        return null;
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        setcookie('access_token', "");

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function create(Request $request): JsonResponse
    {
        $name = $request->get('name');
        $surname = $request->get('surname');
        $email = $request->get('email');
        $password = $request->get('password');
        $role = ($request->get('role') !== null) ? $request->get('role') : 'ROLE_USER';
        $patronymic = ($request->get('patronymic') !== null) ? $request->get('patronymic') : null;


        $user = new User();
        $user->name = $name;
        $user->surname = $surname;
        $user->patronymic = $patronymic;
        $user->role = $role;
        $user->email = $email;
        $user->password = Hash::make($password);

        $user->save();

        return response()->json(['message' => 'Successfully registration!']);
    }

    public function update(Request $request): JsonResponse
    {
        $name = $request->get('name');
        $surname = $request->get('surname');
        $email = $request->get('email');
        $patronymic = ($request->get('patronymic') !== null) ? $request->get('patronymic') : null;
        $role = ($request->get('role') !== null) ? $request->get('role') : 'ROLE_USER';
        $id = $request->get('id');

        User::find($id)->update([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'patronymic' => $patronymic,
            'role' => $role
        ]);

        return response()->json(['message' => 'User successfully changed']);
    }

    public function delete($id): JsonResponse
    {
        User::where('id', '=', $id)->delete();
        return response()->json(['message' => 'User successfully deleted']);
    }

    public function listUsers(): JsonResponse
    {
        $users = User::all();
        return response()->json(['users' => $users]);
    }

    public function listUser($id): JsonResponse
    {
        return response()->json(User::find($id));
    }

    public function reset_password(Request $request): JsonResponse
    {
        $email = $request->get('email');
        $user = User::where('email', 'like', $email)->first();
        $mail = new PHPMailerController($email, $user);
        return $mail->sentMail();
    }
}
