<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email', '=', $request->email)->firstOrFail();
        $status = 'error';
        $message = "";
        $data = null;
        $code = 401;
        if ($user) {
            // Jika hasil hash dari password yang diinput user sama dengan password yang di database user maka
            if (Hash::check($request->password, $user->password)) {
                // Generate token
                $user->generateToken();
                $status = 'sucsess';
                // Tampilkan data user menggunakan methof toArray
                $data = $user->toArray();
                $code = 200;
            } else {
                $message = "Login gagal, password salah";
            }
        } else {
            $message = "Login gagal, username salah";
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $status = "error";
        $message = "";
        $data = null;
        $code = 400;
        if ($validator->fails()) {
            $errors = $validator->errors();
            $message = $errors;
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roles' => json_encode(['CUSTOMER']),
            ]);
            if ($user) {
                $user->generateToken();
                $status = "success";
                $message = "register successfully";
                $data = $user->toArray();
                $code = 200;
            } else {
                $message = 'register failed';
            }
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->api_token = null;
            $user->save();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'logout berhasil',
            'data' => null
        ], 200);
    }
}
