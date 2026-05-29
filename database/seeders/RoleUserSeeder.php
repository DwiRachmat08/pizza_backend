<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\Kategori;
use App\Models\KategoriAset;
use App\Models\Pembelian;
use App\Models\Produk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roles = ['admin', 'penjual', 'pembeli', 'mitra'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'slug' => $roleName]);
        }

        // 2. Seed Users (1 per role)
        $roleAdmin = Role::where('slug', 'admin')->first();
        $rolePenjual = Role::where('slug', 'penjual')->first();
        $rolePembeli = Role::where('slug', 'pembeli')->first();
        $roleMitra = Role::where('slug', 'mitra')->first();
        $kategoriAsetGerobak = KategoriAset::where(['slug' => 'gerobak'])->first();

        $user_admin = User::firstOrCreate([
            'name' => 'Admin Pizza',
            'email' => 'admin@pizza.com',
            'password' => Hash::make('password'),
            'role_id' => $roleAdmin->id
        ]);

        $user_penjual = User::firstOrCreate([
            'name' => 'Penjual Pizza',
            'email' => 'penjual@pizza.com',
            'password' => Hash::make('password'),
            'role_id' => $rolePenjual->id
        ]);

        $gerobak_1 = Aset::firstOrCreate([
            'kategori_aset_id'  => $kategoriAsetGerobak->id,
            'nama'              => 'Gerobak A',
            'merk'              => 'Custom Pribadi',
            'qty'               => 1,
            'satuan_id'         => 98,
            'harga'             => 0,
            'qty_ecer'          => 1,
            'satuan_ecer_id'    => 98,
            'harga_ecer'        => 0,
            'keterangan'        => ''
        ]);

        $pembelian_1 = Pembelian::firstOrCreate([
            'aset_id'       => $gerobak_1->id,
            'satuan_id'     => $gerobak_1->satuan_id,
            'qty'           => 1,
            'harga'         => 0,
            'keterangan'    => ''
        ]);

        User::firstOrCreate([
            'name' => 'Pembeli Pizza',
            'email' => 'pembeli@pizza.com',
            'password' => Hash::make('password'),
            'role_id' => $rolePembeli->id
        ]);

        User::firstOrCreate([
            'name' => 'Mitra Pizza',
            'email' => 'mitra@pizza.com',
            'password' => Hash::make('password'),
            'role_id' => $roleMitra->id
        ]);

        // 3. Seed Kategori & Produk
        // $kategoriData = ['sendiri', 'ramean'];
        $kategoriData = [
            [
                'nama_kategori' => 'Paket Sendirian',
                'slug'          => 'paket_sendiri',
                'tipe_kategori' => 'paketan',
                'prioritas'     => 1
            ],
            [
                'nama_kategori' => 'Paket Rame-rame',
                'slug'          => 'paket_ramean',
                'tipe_kategori' => 'paketan',
                'prioritas'     => 2
            ]
        ];

        foreach ($kategoriData as $kat) {
            // $kategori = Kategori::firstOrCreate(['nama_kategori' => $kat]);
            $kategori = Kategori::firstOrCreate($kat);
            $nama_produk_a = 'Pizza ' . ucfirst($kat['nama_kategori']) . ' A';
            $slug_produk_a = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $nama_produk_a)));
            $nama_produk_b = 'Pizza ' . ucfirst($kat['nama_kategori']) . ' B';
            $slug_produk_b = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $nama_produk_b)));


            // Bikin 2 produk per kategori
            for ($i = 1; $i <= 2; $i++) {
                $nama_produk = ($i == 1 ? $nama_produk_a : $nama_produk_b);
                $slug_produk = ($i == 1 ? $slug_produk_a : $slug_produk_b);

                Produk::firstOrCreate([
                    'kategori_id'   => $kategori->id,
                    'nama_produk'   => $nama_produk,
                    'slug'          => $slug_produk,
                    'taste_note'    => 'Taste Note pizza varian ' . $nama_produk,
                    'deskripsi'     => 'Deskripsi pizza varian ' . $nama_produk,
                    'hpp'           => ($kat['slug'] == 'paket-sendiri') ? ($i == 1 ? 11000 : 12000) : ($i == 1 ? 25000 : 30000),
                    'margin'        => ($kat['slug'] == 'paket-sendiri') ? ($i == 1 ? 9000 : 10000) : 10000,
                    'harga'         => ($kat['slug'] == 'paket-sendiri') ? ($i == 1 ? 20000 : 22000) : ($i == 1 ? 35000 : 40000)
                ]);

                // $stok_1 = Stok::firstOrCreate([
                //     'gerobak_id'   => $gerobak_1->id,
                //     'seller_id'   => $user_penjual->id,
                //     'tanggal'   => date('Y-m-d'),
                // ]);

                // $stok_detail_1 = StokDetail::firstOrCreate([
                //     'stokd_id'      => $stok_1->id,
                //     'produk_id'     => $produk_create->id,
                //     'stok'          => rand(10, 50)
                // ]);
            }
        }
    }
}
