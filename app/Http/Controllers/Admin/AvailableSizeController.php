<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailableSize;
use Illuminate\Http\Request;

class AvailableSizeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:available_sizes,name',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $size = AvailableSize::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'active' => true,
                'sort_order' => AvailableSize::max('sort_order') + 1
            ]);

            return response()->json([
                'success' => true,
                'size' => $size,
                'message' => 'Tamaño agregado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el tamaño: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $size = AvailableSize::findOrFail($id);
            
            // Verificar si el tamaño está siendo usado
            if ($size->isInUse()) {
                $count = $size->products()->count();
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar este tamaño porque está siendo usado por {$count} producto(s)"
                ], 400);
            }
            
            $size->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Tamaño eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el tamaño: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'sizes' => 'required|array',
            'sizes.*.id' => 'required|exists:available_sizes,id',
            'sizes.*.sort_order' => 'required|integer|min:0'
        ]);

        try {
            foreach ($request->sizes as $sizeData) {
                AvailableSize::where('id', $sizeData['id'])
                    ->update(['sort_order' => $sizeData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el orden'
            ], 500);
        }
    }
}