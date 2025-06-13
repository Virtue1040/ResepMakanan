<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Resep;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Resep::create([
            "judul" => "Nasi Goreng",
            "kategori" => "Makanan",
            "deskripsi" => "Nasi goreng dengan rempah-rempah pilihan",
            "imageUrl" => "upload/nasigoreng.jpg"
        ]);

        Resep::create([
            "judul" => "Mie Ayam",
            "kategori" => "Makanan",
            "deskripsi" => "Mie ayam dengan saus kacang",
            "imageUrl" => "upload/mieayam.jpg"
        ]);
    }
}
