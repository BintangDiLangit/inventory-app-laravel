<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Registered');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Autentication failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required',
            ]);
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication failed', 500);
            }
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        if ($request->user()) {
            return ResponseFormatter::success($request->user(), 'Data profile user berhasil diambil');
        }else{
            return ResponseFormatter::error('Data profile user gagal diambil');
        }
        
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', 'unique:users'],
            'oldpassword' => 'max:255',
            'newpassword' => 'max:255',
        ]);

        $data = $request->all();
        $user = Auth::user();
        
        
        $hashedPassword = Auth::user()->password;
        if ($request->oldpassword != null && $request->newpassword != null ) {
            if (Hash::check($request->oldpassword, $hashedPassword)) {

                if (!Hash::check($request->newpassword, $hashedPassword)) {
    
                    $users = User::find(Auth::user()->id);
                    $users->password = bcrypt($request->newpassword);
                    User::where('id', Auth::user()->id)->update(array('password' =>  $users->password));
                    $user->update($data);
                    return ResponseFormatter::success($user, 'Data user berhasil diupdate');
                } else {
                    return ResponseFormatter::error([
                        'message' => 'Password tidak boleh sama dari yang lama',
                    ], 'Data user gagal diupdate', 400);
                }
            } else {
                return ResponseFormatter::error([
                    'message' => 'Password lama tidak cocok',
                ], 'Data user gagal diupdate', 400);
            }
        }

        $user->update($data);
        return ResponseFormatter::success($user, 'Data user berhasil diupdate');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }
}
