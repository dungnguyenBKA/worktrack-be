<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\WorkTitle;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [[
            'name_en' => 'user',
            'name_vi' => 'Thành Viên',
            'sort_num' => 1
        ], [
            'name_en' => 'admin',
            'name_vi' => 'Quản Trị',
            'sort_num' => 2
        ]];
        Role::insert($roles);

        $workTitles = [[
            'name_en' => 'Staff',
            'name_vi' => 'Nhân Viên',
            'sort_num' => 1
        ], [
            'name_en' => 'Leader',
            'name_vi' => 'Trưởng Nhóm',
            'sort_num' => 2
        ], [
            'name_en' => 'HR',
            'name_vi' => 'Nhân Sự',
            'sort_num' => 3
        ], [
            'name_en' => 'Manager',
            'name_vi' => 'Quản Lý',
            'sort_num' => 4
        ]];
        WorkTitle::insert($workTitles);
    }
}
