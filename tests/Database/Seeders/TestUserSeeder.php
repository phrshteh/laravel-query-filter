<?php

namespace Omalizadeh\QueryFilter\Tests\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'gender' => true,
                'phone' => '09123456789',
                'first_name' => 'Omid',
                'last_name' => 'Alizadeh',
                'is_active' => true,
                'created_at' => '2021-06-28 22:00:00',
                'updated_at' => '2021-07-01 23:33:00'
            ],
            [
                'id' => 2,
                'gender' => true,
                'phone' => '09987654321',
                'first_name' => 'Ahmad',
                'last_name' => 'Mohammadi',
                'is_active' => false,
                'created_at' => '2021-07-01 19:30:00',
                'updated_at' => '2021-07-01 19:30:00'
            ],
            [
                'id' => 3,
                'gender' => false,
                'phone' => '09983334444',
                'first_name' => 'Maryam',
                'last_name' => 'Saremi',
                'is_active' => true,
                'created_at' => '2021-07-02 11:20:00',
                'updated_at' => '2021-07-02 11:20:00'
            ],
            [
                'id' => 4,
                'gender' => null,
                'phone' => '09983355555',
                'first_name' => 'Blue',
                'last_name' => 'Sky',
                'is_active' => true,
                'created_at' => '2021-07-02 14:40:00',
                'updated_at' => '2021-07-02 19:25:00'
            ],
        ];
        DB::table('users')->insertOrIgnore($data);
    }
}
