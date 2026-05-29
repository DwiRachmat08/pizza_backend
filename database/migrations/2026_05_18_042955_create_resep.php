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
        Schema::create('resep', function (Blueprint $table) {
            $table->id();
            $table->integer('produk_id')->constrained('produks')->onDelete('cascade');
            $table->integer('aset_id')->constrained('aset')->onDelete('cascade');
            $table->integer('satuan_id')->constrained('satuan')->onDelete('cascade');
            $table->decimal('qty', 12, 2);
            $table->decimal('harga', 12, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
