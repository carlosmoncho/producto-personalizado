<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'field_type',
        'options',
        'required',
        'placeholder',
        'help_text',
        'sort_order',
        'active'
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_custom_fields')
                    ->withPivot('value')
                    ->withTimestamps();
    }
}
