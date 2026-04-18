<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Carpeta;
use App\Models\User;
use Illuminate\Database\Seeder;

class CarpetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        Carpeta::create([
            'nombre' => 'Carpeta 1',
            'user_id' => $user->id,
        ]);

        Carpeta::create([
            'nombre' => 'Carpeta 2',
            'user_id' => $user->id,
        ]);
    }
}
