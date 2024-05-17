<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ConfigSeeder::class,
            MasterSeeder::class,
        ]);
        User::create([
            'staff_id' => '20180000',
            'first_name' => 'Nguyen Minh',
            'last_name' => 'Dung',
            'email' => 'dung.nm184079@bka.vn',
            'position_id' => 1,
            'role' => config('common.user.role.admin'),
            'password' => bcrypt('12345678'),
            'created_by' => 1,
        ]);
    }
}
