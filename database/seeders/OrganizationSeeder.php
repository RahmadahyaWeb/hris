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
            $financeHead = User::create([
                'name' => 'Budi Santoso',
                'email' => 'budi.finance@mail.com',
                'password' => Hash::make('password'),
            ]);

            $financeManager = User::create([
                'name' => 'Andi Pratama',
                'email' => 'andi.finance@mail.com',
                'password' => Hash::make('password'),
            ]);

            $financeStaff = User::create([
                'name' => 'Rina Kurniawati',
                'email' => 'rina.finance@mail.com',
                'password' => Hash::make('password'),
            ]);

            $itHead = User::create([
                'name' => 'Dedi Saputra',
                'email' => 'dedi.it@mail.com',
                'password' => Hash::make('password'),
            ]);

            $itManager = User::create([
                'name' => 'Maya Putri',
                'email' => 'maya.it@mail.com',
                'password' => Hash::make('password'),
            ]);

            $itStaff = User::create([
                'name' => 'Rizky Hidayat',
                'email' => 'rizky.it@mail.com',
                'password' => Hash::make('password'),
            ]);

            // HR (2 orang saja)
            $hrManager = User::create([
                'name' => 'Siti Rahmawati',
                'email' => 'siti.hr@mail.com',
                'password' => Hash::make('password'),
            ]);

            $hrStaff = User::create([
                'name' => 'Fajar Nugroho',
                'email' => 'fajar.hr@mail.com',
                'password' => Hash::make('password'),
            ]);

            // ========================
            // DEPARTMENTS
            // ========================
            $finance = Department::create([
                'branch_id' => $branch->id,
                'name' => 'Finance',
                'code' => 'FIN',
                'head_user_id' => $financeHead->id,
            ]);

            $it = Department::create([
                'branch_id' => $branch->id,
                'name' => 'IT',
                'code' => 'IT',
                'head_user_id' => $itHead->id,
            ]);

            $hr = Department::create([
                'branch_id' => $branch->id,
                'name' => 'Human Resource',
                'code' => 'HR',
                'head_user_id' => $hrManager->id,
            ]);

            // ========================
            // POSITIONS
            // ========================
            // Finance
            $financeMgr = Position::create([
                'department_id' => $finance->id,
                'name' => 'Finance Manager',
                'code' => 'FIN-MGR',
                'level' => 1,
                'head_user_id' => $financeManager->id,
            ]);

            $financeStf = Position::create([
                'department_id' => $finance->id,
                'name' => 'Finance Staff',
                'code' => 'FIN-STF',
                'level' => 2,
                'parent_id' => $financeMgr->id,
            ]);

            // IT
            $itMgr = Position::create([
                'department_id' => $it->id,
                'name' => 'IT Manager',
                'code' => 'IT-MGR',
                'level' => 1,
                'head_user_id' => $itManager->id,
            ]);

            $itStf = Position::create([
                'department_id' => $it->id,
                'name' => 'IT Staff',
                'code' => 'IT-STF',
                'level' => 2,
                'parent_id' => $itMgr->id,
            ]);

            // HR
            $hrMgr = Position::create([
                'department_id' => $hr->id,
                'name' => 'HR Manager',
                'code' => 'HR-MGR',
                'level' => 1,
                'head_user_id' => $hrManager->id,
            ]);

            $hrStf = Position::create([
                'department_id' => $hr->id,
                'name' => 'HR Staff',
                'code' => 'HR-STF',
                'level' => 2,
                'parent_id' => $hrMgr->id,
            ]);

            // ========================
            // TEAMS
            // ========================
            $financeTeam = Team::create([
                'department_id' => $finance->id,
                'name' => 'Finance Team',
                'code' => 'FIN-T1',
                'lead_user_id' => $financeManager->id,
            ]);

            $itTeam = Team::create([
                'department_id' => $it->id,
                'name' => 'IT Team',
                'code' => 'IT-T1',
                'lead_user_id' => $itManager->id,
            ]);

            $hrTeam = Team::create([
                'department_id' => $hr->id,
                'name' => 'HR Team',
                'code' => 'HR-T1',
                'lead_user_id' => $hrManager->id,
            ]);

            // ========================
            // ASSIGNMENTS
            // ========================
            EmployeeAssignment::insert([
                // Finance
                [
                    'user_id' => $financeHead->id,
                    'branch_id' => $branch->id,
                    'department_id' => $finance->id,
                    'position_id' => $financeMgr->id,
                    'team_id' => $financeTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $financeManager->id,
                    'branch_id' => $branch->id,
                    'department_id' => $finance->id,
                    'position_id' => $financeMgr->id,
                    'team_id' => $financeTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $financeStaff->id,
                    'branch_id' => $branch->id,
                    'department_id' => $finance->id,
                    'position_id' => $financeStf->id,
                    'team_id' => $financeTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // IT
                [
                    'user_id' => $itHead->id,
                    'branch_id' => $branch->id,
                    'department_id' => $it->id,
                    'position_id' => $itMgr->id,
                    'team_id' => $itTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $itManager->id,
                    'branch_id' => $branch->id,
                    'department_id' => $it->id,
                    'position_id' => $itMgr->id,
                    'team_id' => $itTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $itStaff->id,
                    'branch_id' => $branch->id,
                    'department_id' => $it->id,
                    'position_id' => $itStf->id,
                    'team_id' => $itTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // HR
                [
                    'user_id' => $hrManager->id,
                    'branch_id' => $branch->id,
                    'department_id' => $hr->id,
                    'position_id' => $hrMgr->id,
                    'team_id' => $hrTeam->id,
                    'start_date' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $hrStaff->id,
                    'branch_id' => $branch->id,
                    'department_id' => $hr->id,
                    'position_id' => $hrStf->id,
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
