<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->first();
     
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil login',
                    'data' => $user->createToken('main')->plainTextToken
                ]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        return response()->json([
            'success' => true,
            'message' => 'Berhasil registrasi',
            'data' => $user->createToken('main')->plainTextToken
        ]);
    }
}
