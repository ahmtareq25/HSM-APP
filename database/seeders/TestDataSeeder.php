<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        $this->createDummyCompany();
    }

    private function createDummyCompany()
    {
        Company::query()
            ->create([
                'name' => 'Dummy Company',
                'email' => 'dummycompany@example.com'
            ]);
    }
}
