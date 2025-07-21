<?php
// app/Http/Controllers/Admin/AvailableColorController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailableColor;
use Illuminate\Http\Request;

class AvailableColorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:available_colors,name',
            'hex_code' => 'required|string|regex:/^#[0-9A-F]{6}$/i'
        ]);

        try {
            $color = AvailableColor::create([
                'name' => $request->name,
                'hex_code' => strtoupper($request->hex_code),
                'active' => true,
                'sort_order' => AvailableColor::max('sort_order') + 1
            ]);

            return response()->json([
                'success' => true,
                'color' => $color,
                'message' => 'Color agregado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el color: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $color = AvailableColor::findOrFail($id);
            
            // Verificar si el color está siendo usado
            if ($color->isInUse()) {
                $count = $color->products()->count();
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar este color porque está siendo usado por {$count} producto(s)"
                ], 400);
            }
            
            $color->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Color eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el color: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'colors' => 'required|array',
            'colors.*.id' => 'required|exists:available_colors,id',
            'colors.*.sort_order' => 'required|integer|min:0'
        ]);

        try {
            foreach ($request->colors as $colorData) {
                AvailableColor::where('id', $colorData['id'])
                    ->update(['sort_order' => $colorData['sort_order']]);
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