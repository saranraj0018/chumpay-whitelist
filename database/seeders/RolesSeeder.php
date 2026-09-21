<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => 1,
                'name' => 'Super Admin',
                'slug' => 'super_admin',
            ],
            [
                'id' => 2,
                'name' => 'Admin',
                'slug' => 'admin',
            ]
        ];

        foreach ($roles as $role) {
            $exists = Role::where('slug', $role['slug'])->exists();
            if (! $exists) {
                Role::create($role);
            }
        }
    }
}
