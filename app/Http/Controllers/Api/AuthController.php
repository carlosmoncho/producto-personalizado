<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user registration
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Obtener datos del request (funciona tanto con form-data como JSON)
            $data = $request->isJson() ? $request->json()->all() : $request->all();

            $validated = validator($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ])->validate();

            // Usar transacción para crear User Y Customer juntos
            DB::beginTransaction();

            try {
                // 1. Crear usuario para autenticación
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);

                // 2. Crear customer con los mismos datos básicos
                $customer = Customer::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'active' => true,
                ]);

                DB::commit();

                event(new Registered($user));

                Auth::login($user);

                // Regenerar sesión después de login
                $request->session()->regenerate();

                return response()->json([
                    'message' => 'Usuario registrado exitosamente',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle user login
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Obtener datos del request (funciona tanto con form-data como JSON)
            $data = $request->isJson() ? $request->json()->all() : $request->all();

            $validated = validator($data, [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ])->validate();

            if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $data['remember'] ?? false)) {
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales proporcionadas son incorrectas.'],
                ]);
            }

            $request->session()->regenerate();

            $user = Auth::user();

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Sesión cerrada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'No autenticado',
                ], 401);
            }

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user is authenticated
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'authenticated' => Auth::check(),
        ], 200);
    }

    /**
     * Update user profile (name and/or email)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'No autenticado',
                ], 401);
            }

            // Obtener datos del request (funciona tanto con form-data como JSON)
            $data = $request->isJson() ? $request->json()->all() : $request->all();

            $validated = validator($data, [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            ])->validate();

            // Usar transacción para actualizar User Y Customer juntos
            DB::beginTransaction();

            try {
                $oldEmail = $user->email;

                if (isset($validated['name'])) {
                    $user->name = $validated['name'];
                }

                if (isset($validated['email'])) {
                    $user->email = $validated['email'];
                }

                $user->save();

                // Actualizar también el customer si existe
                $customer = Customer::where('email', $oldEmail)->first();
                if ($customer) {
                    if (isset($validated['name'])) {
                        $customer->name = $validated['name'];
                    }
                    if (isset($validated['email'])) {
                        $customer->email = $validated['email'];
                    }
                    $customer->save();
                }

                DB::commit();

                return response()->json([
                    'message' => 'Perfil actualizado exitosamente',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar perfil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'No autenticado',
                ], 401);
            }

            // Obtener datos del request (funciona tanto con form-data como JSON)
            $data = $request->isJson() ? $request->json()->all() : $request->all();

            $validated = validator($data, [
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ])->validate();

            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['La contraseña actual es incorrecta.'],
                ]);
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json([
                'message' => 'Contraseña actualizada exitosamente',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar contraseña',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'No autenticado',
                ], 401);
            }

            // Usar transacción para eliminar User Y Customer juntos
            DB::beginTransaction();

            try {
                $email = $user->email;

                // Cerrar sesión antes de eliminar
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Eliminar usuario
                $user->delete();

                // Eliminar también el customer si existe
                $customer = Customer::where('email', $email)->first();
                if ($customer) {
                    $customer->delete();
                }

                DB::commit();

                return response()->json([
                    'message' => 'Cuenta eliminada exitosamente',
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar cuenta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer data for authenticated user
     */
    public function getCustomerData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'No autenticado',
                ], 401);
            }

            // Buscar customer por email
            $customer = Customer::where('email', $user->email)->first();

            if (!$customer) {
                return response()->json([
                    'customer' => null,
                ], 200);
            }

            return response()->json([
                'customer' => [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'company' => $customer->company,
                    'tax_id' => $customer->tax_id,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'country' => $customer->country,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener datos del cliente',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
