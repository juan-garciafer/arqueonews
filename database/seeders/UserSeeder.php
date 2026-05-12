<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'email' => 'juan@test.com',
        ], [
            'name' => 'Juan Test',
            'password' => Hash::make('password'),
        ]);
        $user->syncRoles(['admin']);
    }
}
