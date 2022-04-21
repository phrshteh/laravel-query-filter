<?php

namespace Omalizadeh\QueryFilter\Tests\Database\Seeders;

use Illuminate\Database\Seeder;
use Omalizadeh\QueryFilter\Tests\Models\User;

class TestingDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'id' => 1,
            'phone' => '09123456789',
            'paid_amount' => 0,
            'is_active' => true,
            'created_at' => '2021-06-28 22:00:00',
            'updated_at' => '2021-07-01 23:33:00',
        ]);
        $user->profile()->create([
            'gender' => true,
            'first_name' => 'Omid',
            'last_name' => 'Alizadeh',
        ]);
        $user->posts()->create([
            'body' => 'hello world!',
        ]);

        $user = User::create([
            'id' => 2,
            'phone' => '09987654321',
            'paid_amount' => 120000,
            'is_active' => false,
            'created_at' => '2021-07-01 19:30:00',
            'updated_at' => '2021-07-01 19:30:00',
        ]);
        $user->profile()->create([
            'first_name' => 'Ahmad',
            'last_name' => 'Mohammadi',
            'gender' => true,
        ]);
        $user->posts()->create([
            'body' => 'hello'
        ]);

        $user = User::create([
            'id' => 3,
            'phone' => '09983334444',
            'paid_amount' => 69.99,
            'is_active' => true,
            'created_at' => '2021-07-02 11:20:00',
            'updated_at' => '2021-07-02 11:20:00',
        ]);
        $user->profile()->create([
            'gender' => false,
            'first_name' => 'Maryam',
            'last_name' => 'Saremi',
        ]);
        $user->posts()->create([
            'body' => 'bye bye.'
        ]);

        $user = User::create([
            'id' => 4,
            'phone' => '09983355555',
            'paid_amount' => 100.00,
            'is_active' => true,
            'created_at' => '2021-07-02 14:40:00',
            'updated_at' => '2021-07-02 19:25:00'
        ]);
        $user->profile()->create([
            'gender' => null,
            'first_name' => 'Blue',
            'last_name' => 'Sky',
        ]);
    }
}
