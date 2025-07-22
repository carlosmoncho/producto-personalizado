<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailablePrintColor;
use Illuminate\Http\Request;

class AvailablePrintColorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:available_print_colors,name',
            'hex_code' => 'required|string|regex:/^#[0-9A-F]{6}$/i'
        ]);

        try {
            $color = AvailablePrintColor::create([
                'name' => $request->name,
                'hex_code' => strtoupper($request->hex_code),
                'active' => true,
                'sort_order' => AvailablePrintColor::max('sort_order') + 1
            ]);

            return response()->json([
                'success' => true,
                'color' => $color,
                'message' => 'Color de impresión agregado exitosamente'
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
            $color = AvailablePrintColor::findOrFail($id);
            
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
                'message' => 'Color de impresión eliminado exitosamente'
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
            'colors.*.id' => 'required|exists:available_print_colors,id',
            'colors.*.sort_order' => 'required|integer|min:0'
        ]);

        try {
            foreach ($request->colors as $colorData) {
                AvailablePrintColor::where('id', $colorData['id'])
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