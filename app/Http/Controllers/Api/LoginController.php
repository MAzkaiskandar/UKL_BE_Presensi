<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Set validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get credentials from request
        $credentials = $request->only('email', 'password');

        // Attempt authentication
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        // Get the authenticated user
        $user = JWTAuth::user();

        // Check if user is not an employee
        if ($user->role !== 'karyawan') { // Assuming 'employee' is the role for employees
            return response()->json([
                'success' => false,
                'message' => 'Hanya karyawan yang diizinkan login dan mendapatkan token'
            ], 403);
        }

        // If auth success and user is an employee
        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ], 200);
    }
}