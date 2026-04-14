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
            // USERS (REALISTIC NAMES)
            // ========================
            $budi = User::create([
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@mail.com',
                'password' => Hash::make('password'),
            ]);

            $siti = User::create([
                'name' => 'Siti Rahmawati',
                'email' => 'siti.rahmawati@mail.com',
                'password' => Hash::make('password'),
            ]);

            $andi = User::create([
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@mail.com',
                'password' => Hash::make('password'),
            ]);

            $rina = User::create([
                'name' => 'Rina Kurniawati',
                'email' => 'rina.kurniawati@mail.com',
                'password' => Hash::make('password'),
            ]);

            $dedi = User::create([
                'name' => 'Dedi Saputra',
                'email' => 'dedi.saputra@mail.com',
                'password' => Hash::make('password'),
            ]);

            $maya = User::create([
                'name' => 'Maya Putri',
                'email' => 'maya.putri@mail.com',
                'password' => Hash::make('password'),
            ]);

            // ========================
            // DEPARTMENTS
            // ========================
            $finance = Department::create([
                'branch_id' => $branch->id,
                'name' => 'Finance',
                'code' => 'FIN',
                'head_user_id' => $budi->id,
            ]);

            $hr = Department::create([
                'branch_id' => $branch->id,
                'name' => 'Human Resource',
                'code' => 'HR',
                'head_user_id' => $siti->id,
            ]);

            // ========================
            // POSITIONS
            // ========================
            $financeManager = Position::create([
                'department_id' => $finance->id,
                'name' => 'Finance Manager',
                'code' => 'FIN-MGR',
                'level' => 1,
                'head_user_id' => $budi->id,
            ]);

            $financeStaff = Position::create([
                'department_id' => $finance->id,
                'name' => 'Finance Staff',
                'code' => 'FIN-STF',
                'level' => 2,
                'parent_id' => $financeManager->id,
            ]);

            $hrManager = Position::create([
                'department_id' => $hr->id,
                'name' => 'HR Manager',
                'code' => 'HR-MGR',
                'level' => 1,
                'head_user_id' => $siti->id,
            ]);

            $hrStaff = Position::create([
                'department_id' => $hr->id,
                'name' => 'HR Staff',
                'code' => 'HR-STF',
                'level' => 2,
                'parent_id' => $hrManager->id,
            ]);

            // ========================
            // TEAMS
            // ========================
            $financeTeam = Team::create([
                'department_id' => $finance->id,
                'name' => 'Finance Team',
                'code' => 'FIN-T1',
                'lead_user_id' => $budi->id,
            ]);

            $hrTeam = Team::create([
                'department_id' => $hr->id,
                'name' => 'HR Team',
                'code' => 'HR-T1',
                'lead_user_id' => $siti->id,
            ]);

            // ========================
            // ASSIGNMENTS
            // ========================
            EmployeeAssignment::insert([
                [
                    'user_id' => $budi->id,
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
                    'user_id' => $andi->id,
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
                    'user_id' => $rina->id,
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
                    'user_id' => $siti->id,
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
                    'user_id' => $dedi->id,
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
                    'user_id' => $maya->id,
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
