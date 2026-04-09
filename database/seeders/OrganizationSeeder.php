<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeAssignment;
use App\Models\Position;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            // ========================
            // BRANCH
            // ========================
            $branch = Branch::create([
                'name' => 'Head Office',
                'code' => 'HO',
                'latitude' => -3.319437,
                'longitude' => 114.590752,
                'radius' => 100,
            ]);

            // ========================
            // USERS
            // ========================
            $deptHead1 = User::create([
                'name' => 'Head Finance',
                'email' => 'finance.head@mail.com',
                'password' => Hash::make('password'),
            ]);

            $deptHead2 = User::create([
                'name' => 'Head HR',
                'email' => 'hr.head@mail.com',
                'password' => Hash::make('password'),
            ]);

            $employee1 = User::create([
                'name' => 'Finance Staff 1',
                'email' => 'finance.staff1@mail.com',
                'password' => Hash::make('password'),
            ]);

            $employee2 = User::create([
                'name' => 'Finance Staff 2',
                'email' => 'finance.staff2@mail.com',
                'password' => Hash::make('password'),
            ]);

            $employee3 = User::create([
                'name' => 'HR Staff 1',
                'email' => 'hr.staff1@mail.com',
                'password' => Hash::make('password'),
            ]);

            $employee4 = User::create([
                'name' => 'HR Staff 2',
                'email' => 'hr.staff2@mail.com',
                'password' => Hash::make('password'),
            ]);

            // ========================
            // DEPARTMENTS
            // ========================
            $finance = Department::create([
                'branch_id' => $branch->id,
                'name' => 'Finance',
                'code' => 'FIN',
                'head_user_id' => $deptHead1->id,
            ]);

            $hr = Department::create([
                'branch_id' => $branch->id,
                'name' => 'Human Resource',
                'code' => 'HR',
                'head_user_id' => $deptHead2->id,
            ]);

            // ========================
            // POSITIONS
            // ========================
            $financeManager = Position::create([
                'department_id' => $finance->id,
                'name' => 'Finance Manager',
                'code' => 'FIN-MGR',
                'level' => 1,
                'head_user_id' => $deptHead1->id,
            ]);

            $financeStaff = Position::create([
                'department_id' => $finance->id,
                'name' => 'Finance Staff',
                'code' => 'FIN-STF',
                'level' => 2,
            ]);

            $hrManager = Position::create([
                'department_id' => $hr->id,
                'name' => 'HR Manager',
                'code' => 'HR-MGR',
                'level' => 1,
                'head_user_id' => $deptHead2->id,
            ]);

            $hrStaff = Position::create([
                'department_id' => $hr->id,
                'name' => 'HR Staff',
                'code' => 'HR-STF',
                'level' => 2,
            ]);

            // ========================
            // TEAMS
            // ========================
            $financeTeam = Team::create([
                'department_id' => $finance->id,
                'name' => 'Finance Team A',
                'code' => 'FIN-A',
                'lead_user_id' => $deptHead1->id,
            ]);

            $hrTeam = Team::create([
                'department_id' => $hr->id,
                'name' => 'HR Team A',
                'code' => 'HR-A',
                'lead_user_id' => $deptHead2->id,
            ]);

            // ========================
            // ASSIGNMENTS
            // ========================
            EmployeeAssignment::insert([
                [
                    'user_id' => $deptHead1->id,
                    'branch_id' => $branch->id,
                    'department_id' => $finance->id,
                    'position_id' => $financeManager->id,
                    'team_id' => $financeTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $employee1->id,
                    'branch_id' => $branch->id,
                    'department_id' => $finance->id,
                    'position_id' => $financeStaff->id,
                    'team_id' => $financeTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $employee2->id,
                    'branch_id' => $branch->id,
                    'department_id' => $finance->id,
                    'position_id' => $financeStaff->id,
                    'team_id' => $financeTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $deptHead2->id,
                    'branch_id' => $branch->id,
                    'department_id' => $hr->id,
                    'position_id' => $hrManager->id,
                    'team_id' => $hrTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $employee3->id,
                    'branch_id' => $branch->id,
                    'department_id' => $hr->id,
                    'position_id' => $hrStaff->id,
                    'team_id' => $hrTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $employee4->id,
                    'branch_id' => $branch->id,
                    'department_id' => $hr->id,
                    'position_id' => $hrStaff->id,
                    'team_id' => $hrTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
