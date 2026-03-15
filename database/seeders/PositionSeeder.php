<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $positions = [

                'Information Technology' => [
                    'Backend Developer',
                    'Frontend Developer',
                    'DevOps Engineer',
                    'QA Engineer',
                ],

                'Human Resources' => [
                    'HR Manager',
                    'Recruiter',
                    'HR Administrator',
                ],

                'Finance' => [
                    'Finance Manager',
                    'Accountant',
                    'Finance Staff',
                ],

                'Sales' => [
                    'Sales Manager',
                    'Sales Executive',
                    'Sales Representative',
                ],

                'Operations' => [
                    'Operations Manager',
                    'Operations Staff',
                ],

            ];

            foreach ($positions as $divisionName => $items) {

                $division = Division::where('name', $divisionName)->first();

                if (! $division) {
                    continue;
                }

                foreach ($items as $name) {

                    Position::create([
                        'title' => $name,
                        'division_id' => $division->id,
                    ]);
                }
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
