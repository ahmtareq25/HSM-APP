<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('local')) {
            $this->call(CoreDataSeeder::class);
           // $this->call(TestDataSeeder::class);
        }
    }
}
