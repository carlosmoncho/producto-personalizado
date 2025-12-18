<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HdriFile extends Model
{
    protected $fillable = [
        'name',
        'path',
        'original_filename',
        'file_size',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Obtener el HDRI activo
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Obtener la URL del archivo
     */
    public function getUrl(): string
    {
        $disk = config('filesystems.default', 'public');

        if ($disk === 's3') {
            return Storage::disk('s3')->url($this->path);
        }

        return url('/api/storage/' . $this->path);
    }

    /**
     * Activar este HDRI (desactiva los demás)
     */
    public function activate(): void
    {
        // Desactivar todos
        self::query()->update(['is_active' => false]);

        // Activar este
        $this->update(['is_active' => true]);
    }

    /**
     * Obtener tamaño formateado
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Eliminar archivo de storage al eliminar registro
     */
    protected static function booted(): void
    {
        static::deleting(function (HdriFile $hdri) {
            $disk = config('filesystems.default', 'public');
            Storage::disk($disk)->delete($hdri->path);
        });
    }
}
