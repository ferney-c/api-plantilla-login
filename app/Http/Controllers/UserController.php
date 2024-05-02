<?php

namespace App\Http\Controllers;

use App\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    public function index(){
        // obtener todos los usuarios en tipo json
        return response()->json([
            'data' => User::all(),
        ]);
    }

    /*public function login(Request $request)
    {
        $credentials = $request->only('name', 'password');

        if (User::attempt($credentials)) {
            // Si las credenciales son válidas, devuelve el usuario autenticado
            return User::user();
        }

        // Si las credenciales no son válidas, devuelve un mensaje de error
        return response()->json(['error' => 'Credenciales inválidas'], 401);
    }*/

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validar los datos del formulario
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
            ]);

            // Crear un nuevo usuario
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save(); // Guardar el usuario en la base de datos

            return response()->json([
                'message' => 'Usuario creado correctamente',
                'data' => User::find($request->id),
            ]);
        } catch (\Exception $e) { // Capturar cualquier error
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Obter un usuario por su id
        // Retornar la respuesta en formato JSON
        $user = User::find($id);
        if ($user != null) { // Verificar si el usuario existe
            return response()->json([
                'data' => $user,
            ]);
        }else{ // Mostrar mensaje de error si el usuario no existe
            return response()->json([
                'error' => 'Usuario no encontrado',
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Actualizar un usuario por su id
        try {
            // Validar los datos que se estan ingresando para reemplazar los anteriores
            $request->validate([
                'name' => 'string',
                'email' => 'email|unique:users',
                'password' => 'string|min:8',
                // el nombre y el apellido tambien se pueden modificar
            ]);

            $user = User::find($id);
            if ($user != null) { // Verificar si el usuario existe
                // Actualizar los datos del usuario
                $user->update($request->all());
                return response()->json([
                    'message' => 'Usuario actualizado correctamente',
                    'data' => $user,
                ]);
            }else{ // Si el usuario no existe
                return response()->json([
                    'error' => 'Usuario no encontrado',
                ], 200);
            }
        } catch (\Exception $e) { // Capturar cualquier error que se genere
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Eliminar un usuario por su id
        try {
            $user = User::find($id);
            if ($user != null) { // Verificar si el usuario existe
                // Eliminar el usuario
                $user->delete();
                return response()->json([
                    'message' => sprintf('El usuario con la Identificacion: %s, ha sido eliminado correctamente', $id),
                ]);
            }else{ // Si el usuario no existe
                return response()->json([
                    'error' => 'Usuario no encontrado',
                ]);
            }
        } catch (\Exception $e) { // Capturar cualquier error que se genere
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }
}
