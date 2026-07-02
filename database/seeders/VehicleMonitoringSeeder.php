<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle\Location;
use App\Models\Vehicle\VehicleItem;
use App\Models\Permission\Permission;
use App\Models\User;
use App\Models\Role;

class VehicleMonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Locations
        $locations = [
            [
                's_loc' => 'TMB',
                'name' => 'Timbangan (Scales)',
                'description' => 'Area timbangan masuk dan keluar untuk penimbangan truk.',
            ],
            [
                's_loc' => 'C001',
                'name' => 'WPM Area',
                'description' => 'Area Warehouse Raw Material WPM untuk bongkar.',
            ],
            [
                's_loc' => 'B006',
                'name' => 'WRM (Raw Material Unloading)',
                'description' => 'Area Warehouse Raw Material untuk proses pembongkaran.',
            ],
            [
                's_loc' => 'A001',
                'name' => 'WFG (Finished Goods Area)',
                'description' => 'Area Warehouse Finished Goods untuk proses bongkar muat.',
            ],
            [
                's_loc' => 'SMU',
                'name' => 'SMU Area',
                'description' => 'Area SMU khusus untuk monitoring data saja.',
            ],
        ];
 
        foreach ($locations as $loc) {
            Location::updateOrCreate(
                ['s_loc' => $loc['s_loc']],
                [
                    'name' => $loc['name'],
                    'description' => $loc['description']
                ]
            );
        }

        // 2. Seed Master SKUs
        $items = [
            ['sku' => 'SKU-001', 'name' => 'Gula Pasir Rafinasi'],
            ['sku' => 'SKU-002', 'name' => 'Kecap Manis Curah (Industrial)'],
            ['sku' => 'SKU-003', 'name' => 'Slipsheet Plastic 120x100'],
            ['sku' => 'SKU-004', 'name' => 'Garam Halus'],
            ['sku' => 'SKU-005', 'name' => 'Karton Box Packaging Bas'],
        ];

        foreach ($items as $item) {
            VehicleItem::updateOrCreate(
                ['name' => $item['name']],
                []
            );
        }

        // 3. Seed Permissions
        $permissions = [
            [
                'name' => 'vehicle-monitoring-menu',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke menu dan dashboard monitoring realtime.'
            ],
            [
                'name' => 'vehicle-monitoring-timbangan',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke Timbangan Check-In/Check-Out.'
            ],
            [
                'name' => 'vehicle-monitoring-qc',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke QC Queue & sampel status.'
            ],
            [
                'name' => 'vehicle-monitoring-wpm',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke WPM Unloading.'
            ],
            [
                'name' => 'vehicle-monitoring-wrm',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke WRM Unloading Queue.'
            ],
            [
                'name' => 'vehicle-monitoring-wfg',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke WFG Loading/Unloading Queue.'
            ],
            [
                'name' => 'vehicle-monitoring-smu',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke SMU Area.'
            ],
            [
                'name' => 'vehicle-monitoring-master',
                'section' => 'Vehicle Monitoring',
                'description' => 'Akses ke data master SKU/Item.'
            ],
        ];

        $permissionIds = [];
        foreach ($permissions as $perm) {
            $p = Permission::updateOrCreate(
                ['name' => $perm['name']],
                [
                    'section' => $perm['section'],
                    'description' => $perm['description']
                ]
            );
            $permissionIds[] = $p->id;
        }

        // 4. Assign Permissions to all Roles & Users for easy testing
        $roles = Role::all();
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        $users = User::all();
        foreach ($users as $user) {
            $user->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
