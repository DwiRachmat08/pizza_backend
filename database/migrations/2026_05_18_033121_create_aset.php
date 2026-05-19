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
        Schema::create('aset', function (Blueprint $table) {
            $table->id();
            $table->integer('kategori_aset_id')->constrained('kategori_aset')->onDelete('cascade');
            $table->text('nama');
            $table->text('merk')->nullable();
            $table->decimal('qty');
            $table->integer('satuan_id')->constrained('satuan')->onDelete('cascade');
            $table->decimal('harga');
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset');
    }
};
