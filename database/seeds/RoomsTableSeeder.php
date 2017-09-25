<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rooms')->insert([
            'name' => 'Board Room'
        ]);
        DB::table('rooms')->insert([
            'name' => 'Break Room'
        ]);
        DB::table('rooms')->insert([
            'name' => 'Conference Room'
        ]);
    }
}
