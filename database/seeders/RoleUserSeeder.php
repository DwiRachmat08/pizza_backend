<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // // Buat Role
        // $admin = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        // $seller = Role::create(['name' => 'Seller', 'slug' => 'seller']);
        // $pembeli = Role::create(['name' => 'Pembeli', 'slug' => 'pembeli']);

        // // Buat Contoh User
        // User::create([
        //     'name' => 'Budi Seller',
        //     'email' => 'seller@test.com',
        //     'password' => bcrypt('password'),
        //     'role_id' => $seller->id,
        //     'lat' => -7.250445, // Contoh koordinat Surabaya
        //     'long' => 112.768845
        // ]);

        // 1. Seed Roles
        $roles = ['admin', 'penjual', 'pembeli'];
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName, 'slug' => $roleName]);
        }

        // 2. Seed Users (1 per role)
        $roleAdmin = Role::where('slug', 'admin')->first();
        $rolePenjual = Role::where('slug', 'penjual')->first();
        $rolePembeli = Role::where('slug', 'pembeli')->first();

        $user_admin = User::create([
            'name' => 'Admin Pizza',
            'email' => 'admin@pizza.com',
            'password' => Hash::make('password'),
            'role_id' => $roleAdmin->id
        ]);

        User::create([
            'name' => 'Penjual Pizza',
            'email' => 'penjual@pizza.com',
            'password' => Hash::make('password'),
            'role_id' => $rolePenjual->id
        ]);

        User::create([
            'name' => 'Pembeli Pizza',
            'email' => 'pembeli@pizza.com',
            'password' => Hash::make('password'),
            'role_id' => $rolePembeli->id
        ]);

        // 3. Seed Kategori & Produk
        // $kategoriData = ['sendiri', 'ramean'];
        $kategoriData = [
            [
                'nama_kategori' => 'Paket Sendirian',
                'slug'          => 'paket-sendiri',
                'tipe_kategori' => '',
                'prioritas'     => 1
            ],
            [
                'nama_kategori' => 'Paket Rame-rame',
                'slug'          => 'paket-ramean',
                'tipe_kategori' => '',
                'prioritas'     => 2
            ]
        ];

        foreach ($kategoriData as $kat) {
            // $kategori = Kategori::create(['nama_kategori' => $kat]);
            $kategori = Kategori::create($kat);
            $nama_produk_a = 'Pizza ' . ucfirst($kat['nama_kategori']) . ' A';
            $slug_produk_a = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $nama_produk_a)));
            $nama_produk_b = 'Pizza ' . ucfirst($kat['nama_kategori']) . ' B';
            $slug_produk_b = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $nama_produk_b)));


            // Bikin 2 produk per kategori
            for ($i = 1; $i <= 2; $i++) {
                $nama_produk = ($i == 1 ? $nama_produk_a : $nama_produk_b);
                $slug_produk = ($i == 1 ? $slug_produk_a : $slug_produk_b);

                $produk_create = Produk::create([
                    'kategori_id'   => $kategori->id,
                    'nama_produk'   => $nama_produk,
                    'slug'          => $slug_produk,
                    'taste_note'    => 'Taste Note pizza varian ' . $nama_produk,
                    'deskripsi'     => 'Deskripsi pizza varian ' . $nama_produk,
                    'hpp'           => ($kat['slug'] == 'paket-sendiri') ? ($i == 1 ? 11000 : 12000) : ($i == 1 ? 25000 : 30000),
                    'margin'        => ($kat['slug'] == 'paket-sendiri') ? ($i == 1 ? 9000 : 10000) : 10000,
                    'harga'         => ($kat['slug'] == 'paket-sendiri') ? ($i == 1 ? 20000 : 22000) : ($i == 1 ? 35000 : 40000)
                ]);

                Stok::create([
                    'user_id'   => $user_admin->id,
                    'produk_id' => $produk_create->id,
                    'tanggal'   => date('Y-m-d'),
                    'stok'      => rand(10, 50)
                ]);
            }
        }
    }
}
