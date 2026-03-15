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
                    'latitude' => -6.2000000,
                    'longitude' => 106.8166667,
                    'radius' => 200,
                ],
                [
                    'name' => 'Branch Bandung',
                    'latitude' => -6.9174639,
                    'longitude' => 107.6191228,
                    'radius' => 200,
                ],
                [
                    'name' => 'Branch Surabaya',
                    'latitude' => -7.2574719,
                    'longitude' => 112.7520883,
                    'radius' => 200,
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
