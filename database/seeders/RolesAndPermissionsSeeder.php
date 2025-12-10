<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Productos
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Pedidos
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'update order status',

            // Clientes
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',

            // Categorías
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Atributos
            'view attributes',
            'create attributes',
            'edit attributes',
            'delete attributes',

            // Dependencias
            'view dependencies',
            'create dependencies',
            'edit dependencies',
            'delete dependencies',

            // Reglas de precio
            'view price rules',
            'create price rules',
            'edit price rules',
            'delete price rules',

            // Dashboard
            'view dashboard',

            // Exportaciones
            'export orders',
            'export customers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles

        // Super Admin - Tiene todos los permisos
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - Puede hacer todo excepto eliminar permanentemente
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view products', 'create products', 'edit products', 'delete products',
            'view orders', 'create orders', 'edit orders', 'update order status',
            'view customers', 'create customers', 'edit customers',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view attributes', 'create attributes', 'edit attributes', 'delete attributes',
            'view dependencies', 'create dependencies', 'edit dependencies', 'delete dependencies',
            'view price rules', 'create price rules', 'edit price rules', 'delete price rules',
            'view dashboard',
            'export orders', 'export customers',
        ]);

        // Editor - Puede editar productos, pedidos y clientes pero no eliminar
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->givePermissionTo([
            'view products', 'edit products',
            'view orders', 'edit orders', 'update order status',
            'view customers', 'edit customers',
            'view categories',
            'view attributes',
            'view dependencies',
            'view price rules',
            'view dashboard',
        ]);

        // Viewer - Solo puede ver
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'view products',
            'view orders',
            'view customers',
            'view categories',
            'view attributes',
            'view dependencies',
            'view price rules',
            'view dashboard',
        ]);

        // Asignar rol super-admin al primer usuario si existe
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasRole('super-admin')) {
            $firstUser->assignRole('super-admin');
            $this->command->info('Rol super-admin asignado al usuario: ' . $firstUser->email);
        }
    }
}
