<?php

namespace Database\Seeders;

use App\Models\Division;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $divisions = [
                ['name' => 'Information Technology'],
                ['name' => 'Human Resources'],
                ['name' => 'Finance'],
                ['name' => 'Sales'],
                ['name' => 'Operations'],
            ];

            foreach ($divisions as $division) {
                Division::create($division);
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
