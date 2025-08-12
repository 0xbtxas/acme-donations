<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        // permissions
        Schema::create($tableNames['permissions'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'permissions_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            if ($teams) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        // roles (tenant scoped if teams enabled)
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id');
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        // model_has_permissions (tenant scoped if teams enabled)
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->foreignId($pivotPermission)->constrained($tableNames['permissions'])->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                // Use unique constraint instead of primary key to allow nullable tenant_id
                $table->unique([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_unique');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            }
        });

        // model_has_roles (tenant scoped if teams enabled)
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->foreignId($pivotRole)->constrained($tableNames['roles'])->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                // Use unique constraint instead of primary key to allow nullable tenant_id
                $table->unique([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_unique');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            }
        });

        // role_has_permissions
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->foreignId($pivotPermission)->constrained($tableNames['permissions'])->cascadeOnDelete();
            $table->foreignId($pivotRole)->constrained($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Add foreign key constraints for tenant_id columns
        if ($teams) {
            Schema::table($tableNames['permissions'], function (Blueprint $table) use ($columnNames) {
                $table->foreign($columnNames['team_foreign_key'])->references('id')->on('tenants')->nullOnDelete();
            });
            
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($columnNames) {
                $table->foreign($columnNames['team_foreign_key'])->references('id')->on('tenants')->nullOnDelete();
            });
            
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
                $table->foreign($columnNames['team_foreign_key'])->references('id')->on('tenants')->nullOnDelete();
            });
            
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
                $table->foreign($columnNames['team_foreign_key'])->references('id')->on('tenants')->nullOnDelete();
            });
        }

        app('cache')->forget('spatie.permission.cache');
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
