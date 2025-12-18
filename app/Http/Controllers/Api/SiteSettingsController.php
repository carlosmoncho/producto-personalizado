<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HdriFile;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    /**
     * Obtener la URL del HDRI activo
     */
    public function getHdri()
    {
        $activeHdri = HdriFile::getActive();

        return response()->json([
            'success' => true,
            'data' => [
                'hdri_url' => $activeHdri?->getUrl(),
                'hdri_name' => $activeHdri?->name,
                'has_hdri' => !is_null($activeHdri),
            ]
        ]);
    }

    /**
     * Obtener todas las configuraciones 3D
     */
    public function get3dSettings()
    {
        $activeHdri = HdriFile::getActive();

        return response()->json([
            'success' => true,
            'data' => [
                'hdri_url' => $activeHdri?->getUrl(),
                'hdri_name' => $activeHdri?->name,
                'has_hdri' => !is_null($activeHdri),
            ]
        ]);
    }
}
