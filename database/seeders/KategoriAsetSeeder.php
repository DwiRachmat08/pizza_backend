<?php

namespace Database\Seeders;

use App\Models\KategoriAset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriAsetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $asets = ['Bahan Baku', 'Aset Fisik', 'Gerobak'];
        foreach ($asets as $aset) {
            $slug_aset = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $aset)));
            KategoriAset::firstOrCreate(['nama' => $aset, 'slug' => $slug_aset]);
        }
    }
}
