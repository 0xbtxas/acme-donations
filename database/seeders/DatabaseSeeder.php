<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(['slug' => 'acme'], ['name' => 'ACME Corp']);
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
            Permission::findOrCreate($perm);
        }

        $adminRole = Role::findOrCreate('admin');
        $employeeRole = Role::findOrCreate('employee');

        $adminRole->givePermissionTo(Permission::all());
        // Employees: create campaigns and donate; can manage their own via policies
        $employeeRole->givePermissionTo(['campaign.create', 'donation.create']);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'tenant_id' => $tenant->id,
        ]);
        $admin->assignRole('admin');

        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'tenant_id' => $tenant->id,
        ]);
        $employee->assignRole('employee');
    }
}
