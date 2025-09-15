<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserForStudy extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Andrea',
            'email' => 'andrea1994@livemail.it',
            'password' => Hash::make('prolific'),
            'warning_type' => 'tooltip',
            'show_explanation' => false
        ]);
        DB::table('users')->insert([
            'name' => 'Andrea',
            'email' => 'andrea_1994@livemail.it',
            'password' => Hash::make('prolific'),
            'warning_type' => 'popup_email',
            'show_explanation' => false
        ]);
        DB::table('users')->insert([
            'name' => 'Andrea',
            'email' => 'andrea_1994_@livemail.it',
            'password' => Hash::make('prolific'),
            'warning_type' => 'popup_email',
            'show_explanation' => true
        ]);
    }
}
