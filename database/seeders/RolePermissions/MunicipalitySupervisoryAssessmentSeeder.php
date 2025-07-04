<?php


namespace Database\Seeders\RolePermissions;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class MunicipalitySupervisoryAssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
      public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $roles = [
            [
                'name' => 'Municipality - Supervisory Assessment',
            ],
        ];

        foreach ($roles as $role) {
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name) {
                case 'Municipality - Supervisory Assessment':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Building Structures', 'Containments','Service Providers',
                    'Employee Infos','Desludging Vehicles','Treatment Plants','Desludging Vehicles','Applications','Emptyings','Sludge Collections',
                    'Feedbacks','Help Desks','Schedule Reintegration','Schedule Desludging','Help Desks'])
                        ->whereIn('type', ['View', 'List', 'View on map']));
                         
                    $createdRole->givePermissionTo(
                        Permission::all()->whereIn('group', ['Supervisory Assessment'])
                    );
                     $createdRole->givePermissionTo(Permission::all()->whereIn('group',['API'])
                            ->where('name','Access Supervisory Assessment API'));

                        $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Roads', 'Drain', 'Sewers', 'WaterSupply Network'])
                        ->whereIn('type', ['View', 'List']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Maps'])
                        ->whereIn('name', ['Municipality Map Layer', 'Roads Map Layer', 'Sewers Line Map Layer',
                                           'Places Map Layer', 'Drains Map Layer', 'WaterSupply Network Map Layer', 'Water Body Map Layer', 'Ward Boundary Map Layer',
                                           'Land Use Map Layer', 'Buildings Map Layer', 'Containments Map Layer' ]));

                    break;
            }
        }
    }
}
