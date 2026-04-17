<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename Tabel Products -> Produks
        Schema::rename('products', 'produks');

        // 2. Rename Foreign Key di tabel lain (misal di tabel stok)
        Schema::table('stoks', function (Blueprint $table) {
            $table->renameColumn('product_id', 'produk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('produks', 'products');
        Schema::table('stoks', function (Blueprint $table) {
            $table->renameColumn('produk_id', 'product_id');
        });
    }
};
