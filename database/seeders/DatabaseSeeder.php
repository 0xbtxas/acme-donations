<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Config;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(['name' => 'ACME Corp'], ['status' => 'active']);

        // Set the tenant context for the seeder
        Config::set('app.tenant_id', $tenant->id);

        $permissions = [
            'campaign.viewAny',
            'campaign.viewAll',
            'campaign.view',
            'campaign.create',
            'campaign.update',
            'campaign.delete',
            'campaign.publish',
            'donation.viewAny',
            'donation.view',
            'donation.create',
        ];

        foreach ($permissions as $perm) {
            Permission::create([
                'name' => $perm,
                'guard_name' => 'api',
            ]);
        }

        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'api',
            'tenant_id' => $tenant->id
        ]);

        $employeeRole = Role::create([
            'name' => 'employee',
            'guard_name' => 'api',
            'tenant_id' => $tenant->id
        ]);

        $adminRole->givePermissionTo(Permission::all());
        // Employees: create campaigns and donate; can manage their own via policies
        $employeeRole->givePermissionTo(['campaign.create', 'donation.create']);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'tenant_id' => $tenant->id,
        ]);
        $admin->assignRole($adminRole);

        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'tenant_id' => $tenant->id,
        ]);
        $employee->assignRole($employeeRole);

        // Clear the tenant context
        Config::set('app.tenant_id', null);
    }
}
