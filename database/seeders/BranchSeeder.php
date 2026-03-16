<?php

namespace Database\Seeders;

use App\Models\Branch;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $branches = [
                [
                    'name' => 'Head Office',
                    'latitude' => -3.3207340048727865,
                    'longitude' => 114.58132319533456,
                    'radius' => 100,
                ],
            ];

            foreach ($branches as $branch) {
                Branch::create($branch);
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
