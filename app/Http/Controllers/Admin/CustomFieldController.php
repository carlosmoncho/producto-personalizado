<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomField::query();

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('field_type', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por tipo
        if ($request->has('field_type') && $request->field_type) {
            $query->where('field_type', $request->field_type);
        }

        // Filtro por estado
        if ($request->has('status') && $request->status !== '') {
            $query->where('active', $request->status == 'active');
        }

        $customFields = $query->orderBy('sort_order')->paginate(10);

        $breadcrumbs = [
            ['name' => 'Campos Personalizados', 'url' => route('admin.custom-fields.index')]
        ];

        return view('admin.custom-fields.index', compact('customFields', 'breadcrumbs'));
    }

    public function create()
    {
        $breadcrumbs = [
            ['name' => 'Campos Personalizados', 'url' => route('admin.custom-fields.index')],
            ['name' => 'Crear Campo', 'url' => '#']
        ];

        return view('admin.custom-fields.create', compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,number,email,tel,select,radio,checkbox,textarea,file,date,time,datetime',
            'options' => 'nullable|array',
            'options.*' => 'required|string',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'active' => 'boolean'
        ]);

        $customFieldData = [
            'name' => $request->name,
            'field_type' => $request->field_type,
            'options' => $request->options,
            'required' => $request->boolean('required'),
            'placeholder' => $request->placeholder,
            'help_text' => $request->help_text,
            'sort_order' => $request->sort_order ?? 0,
            'active' => $request->boolean('active', true)
        ];

        CustomField::create($customFieldData);

        return redirect()->route('admin.custom-fields.index')
                        ->with('success', 'Campo personalizado creado exitosamente.');
    }

    public function show(CustomField $customField)
    {
        $breadcrumbs = [
            ['name' => 'Campos Personalizados', 'url' => route('admin.custom-fields.index')],
            ['name' => $customField->name, 'url' => '#']
        ];

        return view('admin.custom-fields.show', compact('customField', 'breadcrumbs'));
    }

    public function edit(CustomField $customField)
    {
        $breadcrumbs = [
            ['name' => 'Campos Personalizados', 'url' => route('admin.custom-fields.index')],
            ['name' => $customField->name, 'url' => route('admin.custom-fields.show', $customField)],
            ['name' => 'Editar', 'url' => '#']
        ];

        return view('admin.custom-fields.edit', compact('customField', 'breadcrumbs'));
    }

    public function update(Request $request, CustomField $customField)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|in:text,number,email,tel,select,radio,checkbox,textarea,file,date,time,datetime',
            'options' => 'nullable|array',
            'options.*' => 'required|string',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'active' => 'boolean'
        ]);

        $customFieldData = [
            'name' => $request->name,
            'field_type' => $request->field_type,
            'options' => $request->options,
            'required' => $request->boolean('required'),
            'placeholder' => $request->placeholder,
            'help_text' => $request->help_text,
            'sort_order' => $request->sort_order ?? $customField->sort_order,
            'active' => $request->boolean('active', true)
        ];

        $customField->update($customFieldData);

        return redirect()->route('admin.custom-fields.index')
                        ->with('success', 'Campo personalizado actualizado exitosamente.');
    }

    public function destroy(CustomField $customField)
    {
        try {
            $customField->delete();
            return redirect()->route('admin.custom-fields.index')
                            ->with('success', 'Campo personalizado eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.custom-fields.index')
                            ->with('error', 'No se puede eliminar el campo porque está siendo usado en productos.');
        }
    }
}
