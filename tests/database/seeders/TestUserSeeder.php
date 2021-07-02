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
                'phone' => '09123456789',
                'first_name' => 'Omid',
                'last_name' => 'Alizadeh',
                'is_active' => true
            ],
            [
                'id' => 2,
                'phone' => '09987654321',
                'first_name' => 'Ahmad',
                'last_name' => 'Mohammadi',
                'is_active' => true
            ],
            [
                'id' => 3,
                'phone' => '09983334444',
                'first_name' => 'Maryam',
                'last_name' => 'Saremi',
                'is_active' => false
            ],
        ];
        DB::table('users')->insertOrIgnore($data);
    }
}
