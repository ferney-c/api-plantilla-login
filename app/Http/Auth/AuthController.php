<?php

namespace App\Http\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Login de usuario con Sanctum
    public function login(Request $request)
    {
        $rules = [ // reglas de validación
            'email' => 'required|string|email',
            'password' => 'required|string'
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) // si falla la validación
        {
            return response()->json([
                'errors' => $validator->errors()->all()
            ], 400);
        }
        // si el usuario no existe o la contraseña es incorrecta
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'errors' => 'Credenciales Invalidas'
            ], 401);
        }
        // si el usuario existe y la contraseña es correcta
        $user = User::where('email', $request->email)->first();
        return response()->json([
            'message' => 'Usuario logueado correctamente',
            // se crea un token de autenticación
            'token' => $user->createToken('auth_token')->plainTextToken
        ], 200);
    }

    // Crear un nuevo usuario
    public function createUser(Request $request) // El metodo aun no está listo
    {
        $rules = [ // reglas de validación
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8'
        ];

        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all()
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        event(new Registered($user));
        return response()->json([
            'message' => 'Usuaro creado correctamente',
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    // Cerrar todas las sesiones que usuario tenga activas
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Sesion cerrada correctamente'
        ], 200);
    }

    // Olvidé mi contraseña
    public function forgotPassword(Request $request)
    {
        try {
            // Validar el email
            $request->validate(['email' => 'required|email']);

            // Aquí se enviaría un correo electrónico con un link para restablecer la contraseña
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
                /* ? response()->json(['status' => __($status)])
                : response()->json(['email' => __($status)]); */
        } catch (\Exception $e) { // capturar cualquier excepción
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }
}
