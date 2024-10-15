<?php

namespace App\Http\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
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
    public function forgetPassword(Request $request)
    {

        try {

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el usuario',
                ]);
            }

            $token = Str::random(40);
            $domain = URL::to('/');
            $url = $domain . '/auth/reset-password?token=' . $token;

            $data = [
                'title' => 'Restablecer Contraseña',
                'body' => 'Haga clic en el siguiente enlace para restablecer su contraseña',
                'url' => $url,
                'email' => $user->email,
            ];


            Mail::send('forgetPasswordMail', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email']);
                $message->subject($data['title']);
            });

            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            PasswordReset::updateOrCreate(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => $datetime
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Se ha enviado un correo electrónico con un enlace para restablecer su contraseña',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Cargar la vista para restablecer la contraseña
    public function resetPasswordLoad(Request $request)
    {
        $token = $request->token;
        $passwordReset = PasswordReset::where('token', $token)->get();
        if (!isset($token) && !$passwordReset) {
            return view('404');
        }

        $user = User::where('email', $passwordReset[0]->email)->get();
        return view('resetPassword', compact('user'));
    }


    // Restablecer la contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([ // validación de campos
            'password' => 'required|string|min:8|confirmed',
            //'password_confirmation' => 'required|string|same:password',
        ]);

        $user = User::find($request->id);
        $user->password = $request->password;
        $user->save();

        PasswordReset::where('email', $user->email)->delete();

        return "<h1>Contraseña restablecida correctamente</h1>";

        /* $passwordReset = PasswordReset::where('email', $request->email)->first();
        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'Token no válido',
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        $passwordReset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña restablecida correctamente',
        ]); */
    }
}
