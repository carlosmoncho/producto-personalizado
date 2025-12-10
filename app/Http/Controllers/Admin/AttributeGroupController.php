<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeGroupController extends Controller
{
    public function index()
    {
        $groups = AttributeGroup::withCount('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.attribute-groups.index', compact('groups'));
    }

    public function create()
    {
        $types = [
            'color' => 'Colores',
            'size' => 'Tamaños/Dimensiones',
            'material' => 'Materiales',
            'weight' => 'Gramaje',
            'finish' => 'Acabados',
            'style' => 'Estilos',
            'ink' => 'Tintas',
            'ink_color' => 'Colores Tintas',
            'cliche' => 'Cliché',
            'system' => 'Sistemas de Impresión',
            'quantity' => 'Cantidades',
            'packaging' => 'Empaquetado',
            'custom' => 'Personalizado'
        ];

        return view('admin.attribute-groups.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:attribute_groups,slug',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_required' => 'boolean',
            'allow_multiple' => 'boolean',
            'affects_price' => 'boolean',
            'affects_stock' => 'boolean',
            'show_in_filter' => 'boolean',
            'active' => 'boolean'
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_required'] = $request->boolean('is_required');
        $validated['allow_multiple'] = $request->boolean('allow_multiple');
        $validated['affects_price'] = $request->boolean('affects_price');
        $validated['affects_stock'] = $request->boolean('affects_stock');
        $validated['show_in_filter'] = $request->boolean('show_in_filter', true);
        $validated['active'] = $request->boolean('active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $group = AttributeGroup::create($validated);

        return redirect()->route('admin.attribute-groups.show', $group)
            ->with('success', 'Grupo de atributos creado exitosamente.');
    }

    public function show(AttributeGroup $attributeGroup)
    {
        $attributeGroup->load(['attributes' => function($query) {
            $query->orderBy('sort_order')->orderBy('name');
        }]);

        return view('admin.attribute-groups.show', compact('attributeGroup'));
    }

    public function edit(AttributeGroup $attributeGroup)
    {
        $types = [
            'color' => 'Colores',
            'size' => 'Tamaños/Dimensiones',
            'material' => 'Materiales',
            'weight' => 'Gramaje',
            'finish' => 'Acabados',
            'style' => 'Estilos',
            'ink' => 'Tintas',
            'ink_color' => 'Colores Tintas',
            'cliche' => 'Cliché',
            'system' => 'Sistemas de Impresión',
            'quantity' => 'Cantidades',
            'packaging' => 'Empaquetado',
            'custom' => 'Personalizado'
        ];

        return view('admin.attribute-groups.edit', compact('attributeGroup', 'types'));
    }

    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:attribute_groups,slug,' . $attributeGroup->id,
            'description' => 'nullable|string',
            'type' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_required' => 'boolean',
            'allow_multiple' => 'boolean',
            'affects_price' => 'boolean',
            'affects_stock' => 'boolean',
            'show_in_filter' => 'boolean',
            'active' => 'boolean'
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_required'] = $request->boolean('is_required');
        $validated['allow_multiple'] = $request->boolean('allow_multiple');
        $validated['affects_price'] = $request->boolean('affects_price');
        $validated['affects_stock'] = $request->boolean('affects_stock');
        $validated['show_in_filter'] = $request->boolean('show_in_filter');
        $validated['active'] = $request->boolean('active');
        $validated['sort_order'] = $validated['sort_order'] ?? $attributeGroup->sort_order;

        $attributeGroup->update($validated);

        return redirect()->route('admin.attribute-groups.show', $attributeGroup)
            ->with('success', 'Grupo de atributos actualizado exitosamente.');
    }

    public function destroy(AttributeGroup $attributeGroup)
    {
        // Verificar si tiene atributos asociados
        if ($attributeGroup->attributes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el grupo porque tiene atributos asociados.');
        }

        $attributeGroup->delete();

        return redirect()->route('admin.attribute-groups.index')
            ->with('success', 'Grupo de atributos eliminado exitosamente.');
    }

    /**
     * Añadir atributo a un grupo
     */
    public function addAttribute(Request $request, AttributeGroup $attributeGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hex_code' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'price_modifier' => 'nullable|numeric',
            'stock_quantity' => 'nullable|integer',
            'is_recommended' => 'boolean'
        ]);

        $validated['attribute_group_id'] = $attributeGroup->id;
        $validated['type'] = $attributeGroup->type;
        $validated['slug'] = Str::slug($validated['value']);
        $validated['active'] = true;

        // Manejar imagen si se sube
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('attributes', 'public');
            $validated['image_path'] = $path;
            
            // Crear thumbnail
            // Aquí podrías usar una librería como Intervention Image
            $validated['thumbnail_path'] = $path; // Por ahora usar la misma
        }

        $attribute = ProductAttribute::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'attribute' => $attribute->load('attributeGroup')
            ]);
        }

        return redirect()->back()
            ->with('success', 'Atributo añadido exitosamente.');
    }

    /**
     * Reordenar grupos
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'groups' => 'required|array',
            'groups.*.id' => 'required|exists:attribute_groups,id',
            'groups.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($validated['groups'] as $groupData) {
            AttributeGroup::where('id', $groupData['id'])
                ->update(['sort_order' => $groupData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}