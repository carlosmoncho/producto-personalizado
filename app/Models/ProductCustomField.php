<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'custom_field_id',
        'value'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}
