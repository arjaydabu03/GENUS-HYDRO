<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'account_code' => '10791',
            'account_name' => 'Limayy Ducut',
            'mobile_no' => '09000000000',

            'location_id' => 382,
            'location_code' => 'D718',
            'location' => 'system application and automation development',

            'department_id' => 8,
            'department_code' => '700',
            'department' => 'management information system',

            'company_id' => 1,
            'company_code' => '10',
            'company' => 'rdf corporate services',

            'username' => 'admin',
            'password' => Hash::make('admin'),
            'role_id' => 1
        ]);
    }
}
