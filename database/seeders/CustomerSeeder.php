<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('es_ES');
        
        // Array de empresas españolas realistas
        $companies = [
            'Textiles Mediterráneos S.L.',
            'Impresiones Digitales Cataluña',
            'Promociones Barcelona S.A.',
            'Marketing Solutions Madrid',
            'Diseño y Publicidad Valencia',
            'Regalos Corporativos Andalucía',
            'Eventos y Merchandising S.L.',
            'Comunicación Visual Bilbao',
            'Serigrafía Profesional Zaragoza',
            'Branding & Publicidad Sevilla',
            'Textil Promocional Galicia',
            'Personalización Extremadura',
            'Regalos de Empresa Murcia',
            'Publicidad Exterior Asturias',
            'Marketing Directo Canarias',
            null, // Algunos clientes sin empresa
            null,
            null
        ];

        $cities = [
            'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza',
            'Málaga', 'Murcia', 'Palma de Mallorca', 'Las Palmas de Gran Canaria',
            'Bilbao', 'Alicante', 'Córdoba', 'Valladolid', 'Vigo',
            'Gijón', 'L\'Hospitalet de Llobregat', 'Granada', 'Vitoria-Gasteiz',
            'A Coruña', 'Elche', 'Oviedo', 'Sabadell', 'Santa Cruz de Tenerife',
            'Pamplona', 'Almería', 'San Sebastián', 'Burgos', 'Albacete'
        ];

        // Crear 50 clientes con datos realistas
        for ($i = 0; $i < 50; $i++) {
            $hasCompany = $faker->boolean(60); // 60% tienen empresa
            $company = $hasCompany ? $faker->randomElement($companies) : null;
            
            // Si tiene empresa, es más probable que tenga NIF/CIF
            $hasTaxId = $hasCompany ? $faker->boolean(90) : $faker->boolean(30);
            $taxId = $hasTaxId ? $this->generateSpanishTaxId($faker) : null;
            
            $customer = Customer::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->email(),
                'phone' => $faker->boolean(80) ? $faker->phoneNumber() : null,
                'company' => $company,
                'address' => $faker->boolean(70) ? $faker->address() : null,
                'city' => $faker->boolean(70) ? $faker->randomElement($cities) : null,
                'postal_code' => $faker->boolean(70) ? $faker->postcode() : null,
                'country' => 'España',
                'tax_id' => $taxId,
                'notes' => $faker->boolean(30) ? $faker->sentence() : null,
                'active' => $faker->boolean(95), // 95% activos
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
            ]);
            
            // Simular algunos clientes con historial de pedidos
            if ($faker->boolean(60)) { // 60% de clientes tienen pedidos
                $ordersCount = $faker->numberBetween(1, 15);
                $totalAmount = 0;
                $lastOrderDate = null;
                
                for ($j = 0; $j < $ordersCount; $j++) {
                    $orderAmount = $faker->randomFloat(2, 25, 2500);
                    $totalAmount += $orderAmount;
                    $orderDate = $faker->dateTimeBetween($customer->created_at, 'now');
                    
                    if (!$lastOrderDate || $orderDate > $lastOrderDate) {
                        $lastOrderDate = $orderDate;
                    }
                }
                
                // Actualizar estadísticas del cliente
                $customer->update([
                    'total_orders_count' => $ordersCount,
                    'total_orders_amount' => $totalAmount,
                    'last_order_at' => $lastOrderDate
                ]);
            }
        }

        // Crear algunos clientes específicos de prueba
        $testCustomers = [
            [
                'name' => 'Hotel Majestic Barcelona',
                'email' => 'compras@hotelmajestic.es',
                'phone' => '+34 93 488 17 17',
                'company' => 'Hotel Majestic Barcelona S.L.',
                'address' => 'Passeig de Gràcia, 68',
                'city' => 'Barcelona',
                'postal_code' => '08007',
                'country' => 'España',
                'tax_id' => 'B12345678',
                'notes' => 'Cliente VIP - Hotel de lujo con pedidos regulares de textil personalizado',
                'active' => true,
                'total_orders_count' => 8,
                'total_orders_amount' => 12450.50,
                'last_order_at' => now()->subDays(15)
            ],
            [
                'name' => 'Ayuntamiento de Valencia',
                'email' => 'eventos@valencia.es',
                'phone' => '+34 96 352 54 78',
                'company' => 'Ayuntamiento de Valencia',
                'address' => 'Plaza del Ayuntamiento, 1',
                'city' => 'Valencia',
                'postal_code' => '46002',
                'country' => 'España',
                'tax_id' => 'P4601300A',
                'notes' => 'Administración pública - Eventos municipales y promoción turística',
                'active' => true,
                'total_orders_count' => 5,
                'total_orders_amount' => 8750.25,
                'last_order_at' => now()->subDays(45)
            ],
            [
                'name' => 'FC Barcelona Foundation',
                'email' => 'merchandising@fcbfoundation.org',
                'phone' => '+34 93 496 36 00',
                'company' => 'Fundació FC Barcelona',
                'address' => 'Camp Nou, Av. Aristides Maillol, s/n',
                'city' => 'Barcelona',
                'postal_code' => '08028',
                'country' => 'España',
                'tax_id' => 'G08266298',
                'notes' => 'Fundación deportiva - Merchandising y regalos institucionales',
                'active' => true,
                'total_orders_count' => 12,
                'total_orders_amount' => 25680.75,
                'last_order_at' => now()->subDays(8)
            ]
        ];

        foreach ($testCustomers as $customerData) {
            Customer::create($customerData);
        }
    }

    private function generateSpanishTaxId($faker)
    {
        $type = $faker->randomElement(['nif', 'cif']);
        
        if ($type === 'nif') {
            // Generar NIF (DNI + letra)
            $dni = $faker->numberBetween(10000000, 99999999);
            $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
            $letter = $letters[$dni % 23];
            return $dni . $letter;
        } else {
            // Generar CIF
            $firstLetter = $faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'N', 'P', 'Q', 'R', 'S', 'U', 'V', 'W']);
            $numbers = $faker->numberBetween(10000000, 99999999);
            $lastChar = $faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']);
            return $firstLetter . $numbers . $lastChar;
        }
    }
}