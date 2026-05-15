<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'IGD',
            'ICU',
            'Rawat Inap',
            'Rawat Jalan',
            'Bedah',
            'Kebidanan',
            'Radiologi',
            'Laboratorium',
            'Farmasi',
            'Gizi',
            'Fisioterapi',
            'Administrasi',
            'Keuangan',
            'SDM',
            'IT',
            'Laundry',
            'Keamanan',
            'Kebersihan',
        ];

        foreach ($units as $nama) {
            Unit::firstOrCreate(['nama' => $nama]);
        }
    }
}
