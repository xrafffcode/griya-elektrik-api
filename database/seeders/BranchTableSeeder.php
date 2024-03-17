<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::factory()->create([
            'code' => 'B001',
            'name' => 'Griya Electric',
            'map_url' => 'https://maps.app.goo.gl/3BGHxdrUDENacCD19',
            'iframe_map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.61678086312!2d109.34921227484024!3d-7.396764792613136!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e65597c851f9dff%3A0xb3ade77bec0af9ef!2sToko%20Griya%20Electric!5e0!3m2!1sid!2sid!4v1709558874159!5m2!1sid!2sid',
            'address' => 'Jl. Ahmad Yani No. 86 ( Depan Taman Makam Pahlawan )',
            'city' => 'Purbalingga',
            'email' => 'griyalimabelasseo@gmail.com',
            'phone' => '0812 8434 5301',
            'facebook' => '',
            'instagram' => 'https://www.instagram.com/griya_electric.15',
            'youtube' => '',
            'sort' => 1,
            'is_main' => false,
            'is_active' => true,
        ]);

        Branch::factory()->create([
            'code' => 'B002',
            'name' => 'Griya Lima Belas',
            'map_url' => 'https://maps.app.goo.gl/Z54FMmfyQf6DkXEKA',
            'iframe_map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.371744510451!2d109.24114937484049!3d-7.424047792586498!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655faedc6ddeb5%3A0xca89509e79a39e9d!2sToko%20Lampu%20Hias%2C%20GRIYA%2015!5e0!3m2!1sid!2sid!4v1709558749425!5m2!1sid!2sid',
            'address' => 'Komplek Pertokoan Kebon Dalem Blok A-17 dan A-18',
            'city' => 'Purwokerto',
            'email' => 'griyalimabelasseo@gmail.com',
            'phone' => '0281 7782456 / 0812 8434 5301',
            'facebook' => '',
            'instagram' => 'https://www.instagram.com/griya_electric.15',
            'youtube' => '',
            'sort' => 2,
            'is_main' => true,
            'is_active' => true,
        ]);
    }
}
