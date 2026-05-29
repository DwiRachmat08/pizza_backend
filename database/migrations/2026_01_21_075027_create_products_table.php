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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->onDelete('cascade');

            $table->string('nama_produk');
            $table->string('slug')->unique();
            $table->text('taste_note')->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('hpp', 12, 2);
            $table->decimal('margin', 12, 2);
            $table->decimal('harga', 12, 2);

            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
