<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Hanya izinkan admin untuk menambahkan pengguna
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role !== 'karyawan') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Buat pengguna
        $newUser = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        if ($newUser) {


            return response()->json(['success' => true, 'user' => $newUser], 201);
        }

        return response()->json(['success' => false], 409);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        $user->name = $request->name;


        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Sukses update User']);
    }

    public function index()
    {
        $users = User::all();
        return response()->json(['success' => true, 'users' => $users], 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json(['success' => true, 'user' => $user], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User berhasil dihapus'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }
    }
}