<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintingSystem;
use Illuminate\Http\Request;

class PrintingSystemController extends Controller
{
    public function index()
    {
        $printingSystems = PrintingSystem::orderBy('sort_order')->paginate(10);
        
        $breadcrumbs = [
            ['name' => 'Sistemas de Impresión', 'url' => '#']
        ];

        return view('admin.printing-systems.index', compact('printingSystems', 'breadcrumbs'));
    }

    public function create()
    {
        $breadcrumbs = [
            ['name' => 'Sistemas de Impresión', 'url' => route('admin.printing-systems.index')],
            ['name' => 'Crear', 'url' => '#']
        ];

        return view('admin.printing-systems.create', compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:printing_systems,name',
            'total_colors' => 'required|integer|min:1',
            'min_units' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $printingSystem = PrintingSystem::create([
                'name' => $request->name,
                'total_colors' => $request->total_colors,
                'min_units' => $request->min_units,
                'price_per_unit' => $request->price_per_unit,
                'description' => $request->description,
                'active' => $request->boolean('active', true),
                'sort_order' => PrintingSystem::max('sort_order') + 1
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'printingSystem' => $printingSystem,
                    'message' => 'Sistema de impresión agregado exitosamente'
                ]);
            }

            return redirect()->route('admin.printing-systems.index')
                           ->with('success', 'Sistema de impresión creado exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar el sistema de impresión: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Error al crear el sistema de impresión.'])->withInput();
        }
    }

    public function edit(PrintingSystem $printingSystem)
    {
        $breadcrumbs = [
            ['name' => 'Sistemas de Impresión', 'url' => route('admin.printing-systems.index')],
            ['name' => $printingSystem->name, 'url' => '#']
        ];

        return view('admin.printing-systems.edit', compact('printingSystem', 'breadcrumbs'));
    }

    public function update(Request $request, PrintingSystem $printingSystem)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:printing_systems,name,' . $printingSystem->id,
            'total_colors' => 'required|integer|min:1',
            'min_units' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500'
        ]);

        $printingSystem->update([
            'name' => $request->name,
            'total_colors' => $request->total_colors,
            'min_units' => $request->min_units,
            'price_per_unit' => $request->price_per_unit,
            'description' => $request->description,
            'active' => $request->boolean('active', true)
        ]);

        return redirect()->route('admin.printing-systems.index')
                       ->with('success', 'Sistema de impresión actualizado exitosamente.');
    }

    public function destroy(PrintingSystem $printingSystem)
{
    try {
        // Verificar si el sistema está siendo usado
        if ($printingSystem->isInUse()) {
            $count = $printingSystem->products()->count();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar este sistema porque está siendo usado por {$count} producto(s)"
                ], 400);
            }
            
            return back()->withErrors(['error' => "No se puede eliminar este sistema porque está siendo usado por {$count} producto(s)"]);
        }
        
        $printingSystem->delete();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sistema de impresión eliminado exitosamente'
            ]);
        }
        
        return redirect()->route('admin.printing-systems.index')
                       ->with('success', 'Sistema de impresión eliminado exitosamente.');
    } catch (\Exception $e) {
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el sistema de impresión: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->withErrors(['error' => 'Error al eliminar el sistema de impresión.']);
    }
}
    

    public function updateOrder(Request $request)
    {
        $request->validate([
            'systems' => 'required|array',
            'systems.*.id' => 'required|exists:printing_systems,id',
            'systems.*.sort_order' => 'required|integer|min:0'
        ]);

        try {
            foreach ($request->systems as $systemData) {
                PrintingSystem::where('id', $systemData['id'])
                    ->update(['sort_order' => $systemData['sort_order']]);
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