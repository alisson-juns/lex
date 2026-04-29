<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin        = Role::create(['name' => 'admin',        'guard_name' => 'web']);
        $advogado     = Role::create(['name' => 'advogado',     'guard_name' => 'web']);
        $estagiario   = Role::create(['name' => 'estagiario',   'guard_name' => 'web']);
        $recepcionista = Role::create(['name' => 'recepcionista', 'guard_name' => 'web']);

        $admin->givePermissionTo([
            'view_employee',    'view_any_employee',    'create_employee',    'update_employee',
            'delete_employee',  'delete_any_employee',  'restore_employee',   'restore_any_employee',
            'force_delete_employee', 'force_delete_any_employee',

            'view_lawyer',      'view_any_lawyer',      'create_lawyer',      'update_lawyer',
            'delete_lawyer',    'delete_any_lawyer',    'restore_lawyer',     'restore_any_lawyer',
            'force_delete_lawyer', 'force_delete_any_lawyer',

            'view_occupation',  'view_any_occupation',  'create_occupation',  'update_occupation',
            'delete_occupation', 'delete_any_occupation', 'restore_occupation', 'restore_any_occupation',
            'force_delete_occupation', 'force_delete_any_occupation',

            'view_user',        'view_any_user',        'create_user',        'update_user',
            'delete_user',      'delete_any_user',      'restore_user',       'restore_any_user',
            'force_delete_user', 'force_delete_any_user',

            // legal_case e hearing — admin tem acesso total
            'view_legal_case',  'view_any_legal_case',  'create_legal_case',  'update_legal_case',
            'delete_legal_case', 'delete_any_legal_case', 'restore_legal_case', 'restore_any_legal_case',
            'force_delete_legal_case', 'force_delete_any_legal_case',

            'view_hearing',     'view_any_hearing',     'create_hearing',     'update_hearing',
            'delete_hearing',   'delete_any_hearing',   'restore_hearing',    'restore_any_hearing',
            'force_delete_hearing', 'force_delete_any_hearing',

            // client e enterprise — admin também tem acesso
            'view_client',      'view_any_client',      'create_client',      'update_client',
            'delete_client',    'delete_any_client',    'restore_client',     'restore_any_client',
            'force_delete_client', 'force_delete_any_client',

            'view_enterprise',  'view_any_enterprise',  'create_enterprise',  'update_enterprise',
            'delete_enterprise', 'delete_any_enterprise', 'restore_enterprise', 'restore_any_enterprise',
            'force_delete_enterprise', 'force_delete_any_enterprise',
        ]);

        $advogado->givePermissionTo([
            'view_client',      'view_any_client',      'create_client',      'update_client',
            'delete_client',    'delete_any_client',    'restore_client',     'restore_any_client',
            'force_delete_client', 'force_delete_any_client',

            'view_enterprise',  'view_any_enterprise',  'create_enterprise',  'update_enterprise',
            'delete_enterprise', 'delete_any_enterprise', 'restore_enterprise', 'restore_any_enterprise',
            'force_delete_enterprise', 'force_delete_any_enterprise',

            'view_legal_case',  'view_any_legal_case',  'create_legal_case',  'update_legal_case',
            'delete_legal_case', 'delete_any_legal_case', 'restore_legal_case', 'restore_any_legal_case',
            'force_delete_legal_case', 'force_delete_any_legal_case',

            'view_hearing',     'view_any_hearing',     'create_hearing',     'update_hearing',
            'delete_hearing',   'delete_any_hearing',   'restore_hearing',    'restore_any_hearing',
            'force_delete_hearing', 'force_delete_any_hearing',
        ]);

        $estagiario->givePermissionTo([
            'view_client',      'view_any_client',      'create_client',      'update_client',
            'delete_client',    'delete_any_client',    'restore_client',     'restore_any_client',
            'force_delete_client', 'force_delete_any_client',

            'view_enterprise',  'view_any_enterprise',  'create_enterprise',  'update_enterprise',
            'delete_enterprise', 'delete_any_enterprise', 'restore_enterprise', 'restore_any_enterprise',
            'force_delete_enterprise', 'force_delete_any_enterprise',

            // estagiário: visualiza e cria processos/audiências, mas não deleta
            'view_legal_case',  'view_any_legal_case',  'create_legal_case',  'update_legal_case',
            'view_hearing',     'view_any_hearing',     'create_hearing',     'update_hearing',
        ]);

        $recepcionista->givePermissionTo([
            'view_client',      'view_any_client',      'create_client',      'update_client',
            // recepcionista: apenas visualiza processos/audiências
            'view_legal_case',  'view_any_legal_case',
            'view_hearing',     'view_any_hearing',
        ]);
    }
}