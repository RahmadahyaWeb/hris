<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\EmployeeSchedule;
use Exception;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * ============================================================
     * CHECK-IN
     * ============================================================
     *
     * FLOW:
     * 1. Validasi schedule
     * 2. Cek existing attendance
     * 3. Create attendance
     * 4. Hitung state awal (late / on_time)
     *
     * CATATAN:
     * - break belum relevan di tahap ini
     */
    public function checkin($user): Attendance
    {
        DB::beginTransaction();

        try {

            $schedule = EmployeeSchedule::with('shift')
                ->where('user_id', $user->id)
                ->whereDate('date', today())
                ->first();

            if (! $schedule) {
                throw new Exception('No work schedule found for today.');
            }

            $existing = Attendance::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->first();

            if ($existing && $existing->checkin_at) {
                throw new Exception('You have already checked in today.');
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => today(),
                'checkin_at' => now(),
            ]);

            $stateService = new AttendanceStateService;

            $result = $stateService->resolve(
                $attendance->fresh(),
                $schedule
            );

            $attendance->update([
                'state' => $result['state'],
                'late_minutes' => $result['late_minutes'],
            ]);

            DB::commit();

            return $attendance;

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ============================================================
     * START BREAK
     * ============================================================
     *
     * RULE:
     * - harus sudah checkin
     * - belum checkout
     * - tidak boleh ada break aktif
     */
    public function startBreak($user): AttendanceBreak
    {
        DB::beginTransaction();

        try {

            $attendance = Attendance::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->firstOrFail();

            if (! $attendance->checkin_at) {
                throw new Exception('You must check in first.');
            }

            if ($attendance->checkout_at) {
                throw new Exception('Cannot start break after checkout.');
            }

            $active = $attendance->breaks()
                ->whereNull('end_at')
                ->exists();

            if ($active) {
                throw new Exception('Break already started.');
            }

            $break = AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'start_at' => now(),
            ]);

            DB::commit();

            return $break;

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ============================================================
     * END BREAK
     * ============================================================
     *
     * FLOW:
     * 1. Ambil break aktif
     * 2. Hitung durasi
     * 3. Update break
     * 4. Recalculate attendance (PENTING)
     */
    public function endBreak($user): AttendanceBreak
    {
        DB::beginTransaction();

        try {

            $attendance = Attendance::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->firstOrFail();

            $break = $attendance->breaks()
                ->whereNull('end_at')
                ->latest()
                ->firstOrFail();

            $end = now();

            $duration = $break->start_at->diffInMinutes($end);

            $break->update([
                'end_at' => $end,
                'duration_minutes' => $duration,
            ]);

            /**
             * =====================================================
             * RECALCULATE ATTENDANCE (CRITICAL)
             * =====================================================
             *
             * Break hanya mempengaruhi work_minutes
             * Maka setelah break selesai → wajib recalc
             */
            $this->recalculate($attendance);

            DB::commit();

            return $break;

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ============================================================
     * CHECKOUT
     * ============================================================
     *
     * FLOW:
     * 1. Validasi attendance
     * 2. Set checkout
     * 3. Recalculate (include break)
     */
    public function checkout($user): Attendance
    {
        DB::beginTransaction();

        try {

            $attendance = Attendance::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->firstOrFail();

            if (! $attendance->checkin_at) {
                throw new Exception('Check-in record not found.');
            }

            if ($attendance->checkout_at) {
                throw new Exception('You have already checked out today.');
            }

            /**
             * Optional safety:
             * auto close break jika masih aktif
             */
            $activeBreak = $attendance->breaks()
                ->whereNull('end_at')
                ->first();

            if ($activeBreak) {

                $end = now();

                $activeBreak->update([
                    'end_at' => $end,
                    'duration_minutes' => $activeBreak->start_at->diffInMinutes($end),
                ]);
            }

            $attendance->update([
                'checkout_at' => now(),
            ]);

            /**
             * FINAL RECALCULATION
             * (SUDAH INCLUDE BREAK)
             */
            $this->recalculate($attendance);

            DB::commit();

            return $attendance;

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ============================================================
     * RECALCULATE ATTENDANCE (CORE ENGINE)
     * ============================================================
     *
     * Dipakai oleh:
     * - endBreak()
     * - checkout()
     *
     * TUJUAN:
     * memastikan semua metric konsisten:
     * - work_minutes (sudah dikurangi break)
     * - overtime
     * - late
     */
    private function recalculate(Attendance $attendance): void
    {
        $schedule = EmployeeSchedule::with('shift')
            ->where([
                'user_id' => $attendance->user_id,
                'date' => $attendance->date,
            ])->first();

        if (! $schedule) {
            return;
        }

        $stateService = new AttendanceStateService;

        $result = $stateService->resolve(
            $attendance->fresh(),
            $schedule
        );

        $attendance->update([
            'state' => $result['state'],
            'late_minutes' => $result['late_minutes'],
            'work_minutes' => $result['work_minutes'],
            'overtime_minutes' => $result['overtime_minutes'],
        ]);
    }
}
