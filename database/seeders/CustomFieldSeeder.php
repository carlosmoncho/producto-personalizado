<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomField;

class CustomFieldSeeder extends Seeder
{
    public function run()
    {
        $customFields = [
            [
                'name' => 'Texto personalizado',
                'field_type' => 'text',
                'required' => false,
                'placeholder' => 'Ingrese el texto personalizado',
                'help_text' => 'Texto que aparecerá en el producto',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Posición del texto',
                'field_type' => 'select',
                'options' => ['Centrado', 'Izquierda', 'Derecha', 'Arriba', 'Abajo'],
                'required' => false,
                'help_text' => 'Seleccione la posición del texto en el producto',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'name' => 'Tamaño de fuente',
                'field_type' => 'select',
                'options' => ['Pequeño', 'Mediano', 'Grande', 'Extra Grande'],
                'required' => false,
                'help_text' => 'Seleccione el tamaño de la fuente',
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'name' => 'Instrucciones especiales',
                'field_type' => 'textarea',
                'required' => false,
                'placeholder' => 'Indique cualquier instrucción especial para la personalización',
                'help_text' => 'Instrucciones adicionales para el diseño',
                'sort_order' => 4,
                'active' => true,
            ],
            [
                'name' => 'Fecha de entrega deseada',
                'field_type' => 'date',
                'required' => false,
                'help_text' => 'Fecha preferida para la entrega (no garantizada)',
                'sort_order' => 5,
                'active' => true,
            ],
            [
                'name' => 'Acabado brillante',
                'field_type' => 'checkbox',
                'required' => false,
                'help_text' => 'Marque si desea acabado brillante',
                'sort_order' => 6,
                'active' => true,
            ],
        ];

        foreach ($customFields as $field) {
            CustomField::create($field);
        }
    }
}
