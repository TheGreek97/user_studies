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
                'password' => Hash::make('uwhfsih89w3hfw' . $i),
                'warning_type' => 'tooltip'
            ]);
        }

        for($i = 10; $i < 20; $i++){
            DB::table('users')->insert([
                'name' => 'User',
                'email' => 'user' . $i . '@mail.com',
                'password' => Hash::make('uwhfsih89w3hfw' . $i),
                'warning_type' => 'popup_email'
            ]);
        }

        for($i = 20; $i < 30; $i++){
            DB::table('users')->insert([
                'name' => 'User',
                'email' => 'user' . $i . '@mail.com',
                'password' => Hash::make('uwhfsih89w3hfw' . $i),
                'warning_type' => 'popup_email',
                'show_explanation' => true
            ]);
        }

    }
}
