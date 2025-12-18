<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Obtener un valor de configuración por clave
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("site_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Establecer un valor de configuración
     */
    public static function setValue(string $key, $value, string $type = 'string', string $group = 'general'): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        // Limpiar caché
        Cache::forget("site_setting_{$key}");

        return $setting;
    }

    /**
     * Obtener URL del archivo HDRI
     */
    public static function getHdriUrl(): ?string
    {
        $path = self::getValue('hdri_environment');

        if (!$path) {
            return null;
        }

        $disk = config('filesystems.default', 'public');

        if ($disk === 's3') {
            return Storage::disk('s3')->url($path);
        }

        return url('/api/storage/' . $path);
    }

    /**
     * Obtener todas las configuraciones de un grupo
     */
    public static function getByGroup(string $group): array
    {
        return Cache::remember("site_settings_group_{$group}", 3600, function () use ($group) {
            return self::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}
