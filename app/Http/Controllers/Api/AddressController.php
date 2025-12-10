<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    /**
     * Obtener el customer_id del usuario autenticado
     */
    private function getCustomerId(Request $request): ?int
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        $customer = Customer::where('email', $user->email)->first();
        return $customer?->id;
    }

    /**
     * Listar todas las direcciones del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customerId = $this->getCustomerId($request);

            if (!$customerId) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $addresses = Address::where('customer_id', $customerId)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'addresses' => $addresses,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener direcciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear una nueva dirección
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $customerId = $this->getCustomerId($request);

            if (!$customerId) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $data = $request->isJson() ? $request->json()->all() : $request->all();

            $validated = validator($data, [
                'type' => ['required', 'in:shipping,billing'],
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
                'company' => ['nullable', 'string', 'max:255'],
                'address_line_1' => ['required', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'max:255'],
                'postal_code' => ['required', 'string', 'max:255'],
                'country' => ['required', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'is_default' => ['boolean'],
            ])->validate();

            DB::beginTransaction();

            try {
                // Si esta dirección se marca como predeterminada, desmarcar las demás del mismo tipo
                if (isset($validated['is_default']) && $validated['is_default']) {
                    Address::where('customer_id', $customerId)
                        ->where('type', $validated['type'])
                        ->update(['is_default' => false]);
                }

                $validated['customer_id'] = $customerId;
                $address = Address::create($validated);

                DB::commit();

                return response()->json([
                    'message' => 'Dirección creada exitosamente',
                    'address' => $address,
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
                'message' => 'Error al crear dirección',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar una dirección específica
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $customerId = $this->getCustomerId($request);

            if (!$customerId) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $address = Address::where('id', $id)
                ->where('customer_id', $customerId)
                ->first();

            if (!$address) {
                return response()->json([
                    'message' => 'Dirección no encontrada',
                ], 404);
            }

            return response()->json([
                'address' => $address,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener dirección',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar una dirección existente
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $customerId = $this->getCustomerId($request);

            if (!$customerId) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $address = Address::where('id', $id)
                ->where('customer_id', $customerId)
                ->first();

            if (!$address) {
                return response()->json([
                    'message' => 'Dirección no encontrada',
                ], 404);
            }

            $data = $request->isJson() ? $request->json()->all() : $request->all();

            $validated = validator($data, [
                'type' => ['sometimes', 'required', 'in:shipping,billing'],
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
                'company' => ['nullable', 'string', 'max:255'],
                'address_line_1' => ['sometimes', 'required', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'city' => ['sometimes', 'required', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'max:255'],
                'postal_code' => ['sometimes', 'required', 'string', 'max:255'],
                'country' => ['sometimes', 'required', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'is_default' => ['boolean'],
            ])->validate();

            DB::beginTransaction();

            try {
                // Si esta dirección se marca como predeterminada, desmarcar las demás del mismo tipo
                if (isset($validated['is_default']) && $validated['is_default']) {
                    $type = $validated['type'] ?? $address->type;
                    Address::where('customer_id', $customerId)
                        ->where('type', $type)
                        ->where('id', '!=', $id)
                        ->update(['is_default' => false]);
                }

                $address->update($validated);

                DB::commit();

                return response()->json([
                    'message' => 'Dirección actualizada exitosamente',
                    'address' => $address->fresh(),
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
                'message' => 'Error al actualizar dirección',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una dirección
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $customerId = $this->getCustomerId($request);

            if (!$customerId) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $address = Address::where('id', $id)
                ->where('customer_id', $customerId)
                ->first();

            if (!$address) {
                return response()->json([
                    'message' => 'Dirección no encontrada',
                ], 404);
            }

            $address->delete();

            return response()->json([
                'message' => 'Dirección eliminada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar dirección',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Establecer una dirección como predeterminada
     */
    public function setDefault(Request $request, string $id): JsonResponse
    {
        try {
            $customerId = $this->getCustomerId($request);

            if (!$customerId) {
                return response()->json([
                    'message' => 'Cliente no encontrado',
                ], 404);
            }

            $address = Address::where('id', $id)
                ->where('customer_id', $customerId)
                ->first();

            if (!$address) {
                return response()->json([
                    'message' => 'Dirección no encontrada',
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Desmarcar todas las direcciones del mismo tipo
                Address::where('customer_id', $customerId)
                    ->where('type', $address->type)
                    ->update(['is_default' => false]);

                // Marcar esta como predeterminada
                $address->update(['is_default' => true]);

                DB::commit();

                return response()->json([
                    'message' => 'Dirección establecida como predeterminada',
                    'address' => $address->fresh(),
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al establecer dirección predeterminada',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
