<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTestUsers extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 0; $i < 10; $i++){
            DB::table('users')->insert([
                'name' => 'User',
                'email' => 'user' . $i . '@mail.com',
                'password' => Hash::make('uwhfsih89w3hfw' . $i)
            ]);
        }

        for($i = 10; $i < 20; $i++){
            DB::table('users')->insert([
                'name' => 'User',
                'email' => 'user' . $i . '@mail.com',
                'password' => Hash::make('uwhfsih89w3hfw' . $i)
            ]);
        }

        for($i = 20; $i < 30; $i++){
            DB::table('users')->insert([
                'name' => 'User',
                'email' => 'user' . $i . '@mail.com',
                'password' => Hash::make('uwhfsih89w3hfw' . $i)
            ]);
        }

        for($i = 30; $i < 40; $i++){
            DB::table('users')->insert([
                'name' => 'User',
                'email' => 'user' . $i . '@mail.com',
                'password' => Hash::make('uwhfsih89w3hfw' . $i)
            ]);
        }

        DB::table('users')->insert([
            'name' => Str::random(10),
            'email' => 'tooltip@mail.com',
            'password' => Hash::make('12345678')
        ]);
        DB::table('users')->insert([
            'name' => Str::random(10),
            'email' => 'popup.email@mail.com',
            'password' => Hash::make('12345678')
        ]);
        DB::table('users')->insert([
            'name' => Str::random(10),
            'email' => 'popup.link@mail.com',
            'password' => Hash::make('12345678')
        ]);
        DB::table('users')->insert([
            'name' => Str::random(10),
            'email' => 'browser.native@mail.com',
            'password' => Hash::make('12345678')
        ]);
    }
}
