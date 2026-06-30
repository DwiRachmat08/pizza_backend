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
        Schema::create('rating_penjual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembeli_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('penjual_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating');
            $table->text('pesan');
            $table->timestamps();
        });

        Schema::create('rating_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembeli_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->integer('rating');
            $table->text('pesan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_penjual');
        Schema::dropIfExists('rating_produk');
    }
};
