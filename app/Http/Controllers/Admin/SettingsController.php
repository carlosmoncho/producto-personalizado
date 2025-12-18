<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HdriFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Mostrar página de configuración 3D
     */
    public function index3d()
    {
        $hdriFiles = HdriFile::orderBy('name')->get();
        $activeHdri = HdriFile::getActive();

        return view('admin.settings.3d', compact('hdriFiles', 'activeHdri'));
    }

    /**
     * Subir archivo HDRI
     */
    public function uploadHdri(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hdri' => 'required|file|max:51200', // Max 50MB
        ]);

        $file = $request->file('hdri');
        $extension = strtolower($file->getClientOriginalExtension());

        // Validar extensión
        if (!in_array($extension, ['hdr', 'exr'])) {
            return back()->with('error', 'El archivo debe ser HDR o EXR');
        }

        // Guardar archivo
        $disk = config('filesystems.default', 'public');
        $filename = 'hdri_' . time() . '_' . uniqid() . '.' . $extension;
        $path = $file->storeAs('hdri', $filename, $disk);

        // Crear registro
        $hdri = HdriFile::create([
            'name' => $request->input('name'),
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'is_active' => HdriFile::count() === 0, // Activar si es el primero
        ]);

        return back()->with('success', "HDRI '{$hdri->name}' subido correctamente");
    }

    /**
     * Activar un HDRI
     */
    public function activateHdri(HdriFile $hdri)
    {
        $hdri->activate();

        return back()->with('success', "HDRI '{$hdri->name}' activado");
    }

    /**
     * Desactivar HDRI (ninguno activo)
     */
    public function deactivateHdri()
    {
        HdriFile::query()->update(['is_active' => false]);

        return back()->with('success', 'HDRI desactivado. Se usará iluminación básica.');
    }

    /**
     * Eliminar un HDRI
     */
    public function deleteHdri(HdriFile $hdri)
    {
        $name = $hdri->name;
        $hdri->delete();

        return back()->with('success', "HDRI '{$name}' eliminado");
    }
}
