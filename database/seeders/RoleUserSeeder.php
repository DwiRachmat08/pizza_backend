<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat Role
        $admin = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $seller = Role::create(['name' => 'Seller', 'slug' => 'seller']);
        $pembeli = Role::create(['name' => 'Pembeli', 'slug' => 'pembeli']);

        // Buat Contoh User
        User::create([
            'name' => 'Budi Seller',
            'email' => 'seller@test.com',
            'password' => bcrypt('password'),
            'role_id' => $seller->id,
            'lat' => -7.250445, // Contoh koordinat Surabaya
            'long' => 112.768845
        ]);
    }
}
