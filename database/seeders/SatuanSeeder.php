<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Satuan;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuans = ["%", "Acara", "Alamat", "Ampul", "Bal", "Batang", "Bendel", "Berkas", "Biji", "Bilik", "Botol", "Box", "Buah", "Buku", "Bulan", "Bungkus", "Butir", "Can", "Cm", "Cm2", "Core", "Dokumen", "Dos", "Drum", "Edisi", "Ekor", "Eksemplar", "Galon", "Gelas", "Gram", "Grup", "Gugus", "Ha", "Hari", "Ikat", "Item", "Jam", "Jerigen", "Kaleng", "Kali", "Kali/Tahun", "Kampung", "Kantong", "Kapsul", "Kapsul lunak", "Karton", "Kasus", "Kecamatan", "Kegiatan", "Kelompok", "Kelurahan", "Keping", "Keranjang", "Kg", "Kit", "Koperasi", "Kotak", "Kuesioner", "Kwh", "Lajur", "Lembaga", "Lembar", "Lisensi", "Liter", "Log", "Lokasi", "Lonjor", "Lot", "Ls", "Lsp", "Lusin", "M", "M2", "M2/Bulan", "M2/Hari", "M3", "M3/Hari", "Mata", "Menit", "Mmk", "Modul", "Muat", "Nomor/Butir Soal", "Objek", "Ons", "Orang", "Orang Pertemuan", "Orang/Jam Pelajaran", "Orang/Kunjungan", "Orang/PP", "Orang/Tabel", "Orang/Tahun", "Orang/Topik", "Pail", "Pak", "Paket", "Pasang", "Pcs", "Pengesahan/Tahun", "Per Alamat", "Per kelas", "Per Nomor Daftar", "Per Pemasangan", "Per Penerbitan", "Perkara", "Permohonan", "Persil", "Peserta", "Pihak", "Pilar", "Plat", "Plybag", "Poin", "Pot", "Potong", "Press", "Produk", "Produksi/Kali", "Rean", "Regu", "Rim", "Roll", "Rp", "Rp/Km", "RT", "Ruang", "RW", "Sachet", "Sak", "Sampel", "Seat", "Sekolah", "Set", "Set/Buah", "Set/Hari", "Setel", "Siar", "Sistem", "Siswa", "Sit/Duduk", "SKP", "SKPD", "Slop", "Slot", "SMS", "Spot", "Spot/Bulan", "Stage", "Stan", "Stik", "Strip", "Suppositoria", "Eksemplar Bulan", "Eksemplar Hari", "Orang Bulan", "Orang Grup", "Orang Hari", "Orang Jam", "Orang Kali", "Orang Kedatangan", "Orang Kegiatan", "Orang Modul", "Orang Paket", "Orang Semester", "Orang Unit", "Pot Bulan", "Stan Bulan", "Surat", "Tabel", "Tablet", "Tablet salut", "Tabung", "Tahun", "Tangki", "Tayang", "Tes", "Tim", "Timba", "Titik", "Ton", "Transaksi", "Tube", "UKM", "Unit", "Unit/4 Bulan", "VA", "Vial", "Voucher", "Watt", "Wilayah", "Alamat Hari", "Edisi Bulan", "Titik Bulan", "Titik Tahun", "Unit Bulan", "Unit Hari", "Unit Minggu", "Ton.Km", "Siswa/Mata Ujian", "Naskah/Pelajaran", "Rumpun", "Km", "Porsi", "Unit/Tahun", "PP", "Batang Bulan", "Kaplet", "Rupiah/Tahun", "Rupiah", "Total", "Acress", "Accres", "Suara", "Dalam Decimal", "Dalam Desimal", "Orang Penerima Hari", "Orang Penerima", "Desain", "Pax", "kVA", "Sertifikat", "Unit Sesi", "Sesi", "Ruang Sesi", "Orang Sesi", "Renteng", "Kemasan", "kWp", "Ritase", "Kamar", "Cartridge", "Km2", "SKS", "Semester", "Besek", "Sisir", "Pouch", "Daftar", "Pengguna"];
        foreach ($satuans as $satuanName) {
            Satuan::firstOrCreate(['nama' => $satuanName, 'aktif' => true]);
        }
    }
}
