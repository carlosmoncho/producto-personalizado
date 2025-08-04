<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailableMaterial;
use Illuminate\Http\Request;

class AvailableMaterialController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:available_materials,name',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $material = AvailableMaterial::create([
                'name' => $request->name,
                'description' => $request->description,
                'active' => true,
                'sort_order' => AvailableMaterial::max('sort_order') + 1
            ]);

            return response()->json([
                'success' => true,
                'material' => $material,
                'message' => 'Material agregado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $material = AvailableMaterial::findOrFail($id);
            
            if ($material->isInUse()) {
                $count = $material->products()->count();
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar este material porque está siendo usado por {$count} producto(s)"
                ], 400);
            }
            
            $material->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Material eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'materials' => 'required|array',
            'materials.*.id' => 'required|exists:available_materials,id',
            'materials.*.sort_order' => 'required|integer|min:0'
        ]);

        try {
            foreach ($request->materials as $materialData) {
                AvailableMaterial::where('id', $materialData['id'])
                    ->update(['sort_order' => $materialData['sort_order']]);
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