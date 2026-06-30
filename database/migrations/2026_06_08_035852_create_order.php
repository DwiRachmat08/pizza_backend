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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gerobak_id')->constrained('aset')->onDelete('cascade');
            $table->foreignId('penjual_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pembeli_id')->nullable()->constrained('users')->onDelete('cascade');

            $table->text('kode_pesanan')->comment('PPP[kode_gerobak][kode_penjual][tanggal][no_transaksi]');
            $table->integer('no_antrian')->nullable();
            $table->text('metode_pembayaran')->comment('Offline, Online');
            $table->text('tipe_pembayaran')->nullable()->comment('bca, bri, dll');

            $table->text('status_pembayaran')->comment('Belum Melakukan Pembayaran, Pembayaran Berhasil, Pembayaran Gagal');
            $table->text('status_penjual')->nullable()->comment('Diterima, Ditolak');
            $table->text('status_order')->comment('Pending, Selesai, Gagal');

            $table->decimal('lat_pembeli', 10, 8)->nullable();
            $table->decimal('long_pembeli', 11, 8)->nullable();
            $table->text('alamat_pembeli')->nullable();
            $table->text('note_pembeli')->nullable();

            $table->decimal('total', 12, 2);
            $table->decimal('diskon', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->text('token_midtrans')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
