<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;

class AttendanceStateService
{
    /**
     * ============================================================
     * RESOLVE ATTENDANCE STATE (WITH BREAK SUPPORT)
     * ============================================================
     *
     * TUJUAN:
     * Menghitung seluruh metrik attendance secara konsisten:
     *
     * - late_minutes       → keterlambatan (actual)
     * - work_minutes       → jam kerja bersih (SUDAH dikurangi break)
     * - overtime_minutes   → lembur
     * - state              → hanya untuk UI
     *
     * ============================================================
     * PRINSIP UTAMA:
     * ============================================================
     *
     * 1. SEMUA PERHITUNGAN BERBASIS DATETIME (BUKAN TIME)
     *    → untuk menghindari bug overnight
     *
     * 2. METRICS ADALAH SOURCE OF TRUTH
     *    → state hanya turunan (UI)
     *
     * 3. BREAK TIDAK MENGUBAH:
     *    - late_minutes
     *    - overtime_minutes
     *
     *    BREAK HANYA MENGURANGI:
     *    - work_minutes
     *
     * ============================================================
     */
    public function resolve(Attendance $attendance, EmployeeSchedule $schedule): array
    {
        $rule = new AttendanceRuleService;

        $lateTolerance = $rule->lateTolerance();
        $earlyTolerance = $rule->earlyCheckoutTolerance();
        $overtimeAfter = $rule->overtimeAfter();

        /*
        |============================================================
        | STEP 1: BASE DATE (ANCHOR TIME)
        |============================================================
        |
        | Semua waktu shift harus ditempel ke tanggal schedule
        | supaya tidak terjadi mismatch antar hari.
        |
        | contoh:
        | shift: 22:00 → 04:00
        | tanpa base date → tidak bisa dibandingkan
        |
        */

        $baseDate = Carbon::parse($schedule->date);

        $shiftStart = $baseDate->copy()
            ->setTimeFromTimeString($schedule->shift->start_time);

        $shiftEnd = $baseDate->copy()
            ->setTimeFromTimeString($schedule->shift->end_time);

        /*
        |============================================================
        | STEP 2: CROSS MIDNIGHT FIX
        |============================================================
        |
        | Jika shift melewati tengah malam:
        |
        | contoh:
        | 22:00 → 04:00
        |
        | maka shiftEnd HARUS ditambah 1 hari
        |
        */

        if ($schedule->shift->cross_midnight && $shiftEnd->lte($shiftStart)) {
            $shiftEnd->addDay();
        }

        /*
        |============================================================
        | STEP 3: PARSE ATTENDANCE TIME
        |============================================================
        */

        $checkin = Carbon::parse($attendance->checkin_at);

        $checkout = $attendance->checkout_at
            ? Carbon::parse($attendance->checkout_at)
            : null;

        /*
        |============================================================
        | STEP 4: NORMALIZE CHECKIN (OVERNIGHT CASE)
        |============================================================
        |
        | contoh:
        | shift: 22:00 (tgl 1) → 04:00 (tgl 2)
        | checkin: 01:00 (tgl 2)
        |
        | maka checkin harus dianggap bagian shift hari sebelumnya
        |
        */

        if ($schedule->shift->cross_midnight && $checkin->lt($shiftStart)) {
            $checkin->addDay();
        }

        /*
        |============================================================
        | STEP 5: NORMALIZE CHECKOUT
        |============================================================
        |
        | Jika checkout < checkin
        | → berarti checkout terjadi di hari berikutnya
        |
        */

        if ($checkout && $checkout->lt($checkin)) {
            $checkout->addDay();
        }

        /*
        |============================================================
        | STEP 6: LATE CALCULATION
        |============================================================
        |
        | Late dihitung berdasarkan:
        | checkin > shiftStart
        |
        | TIDAK dikurangi tolerance di sini
        | karena tolerance hanya untuk UI (state)
        |
        */

        $lateMinutes = 0;

        if ($checkin->greaterThan($shiftStart)) {
            $lateMinutes = $shiftStart->diffInMinutes($checkin);
        }

        /*
        |============================================================
        | STEP 7: RAW WORK MINUTES
        |============================================================
        |
        | Total kerja TANPA memperhitungkan break
        |
        */

        $rawWorkMinutes = 0;

        if ($checkout) {
            $rawWorkMinutes = $checkin->diffInMinutes($checkout);
        }

        /*
        |============================================================
        | STEP 8: BREAK CALCULATION
        |============================================================
        |
        | Mengambil semua break yang SUDAH selesai (end_at tidak null)
        |
        | Kenapa?
        | → supaya break yang masih berjalan tidak dihitung
        |
        */

        $breakMinutes = $attendance->breaks()
            ->whereNotNull('end_at')
            ->sum('duration_minutes');

        /*
        |============================================================
        | STEP 9: FINAL WORK MINUTES
        |============================================================
        |
        | Rumus:
        |
        | work = raw_work - break
        |
        | Tidak boleh negatif
        |
        */

        $workMinutes = max($rawWorkMinutes - $breakMinutes, 0);

        /*
        |============================================================
        | STEP 10: OVERTIME
        |============================================================
        |
        | Overtime dihitung dari:
        | checkout > shiftEnd
        |
        */

        $overtimeMinutes = 0;

        if ($checkout && $checkout->greaterThan($shiftEnd)) {
            $overtimeMinutes = $shiftEnd->diffInMinutes($checkout);
        }

        /*
        |============================================================
        | STEP 11: EARLY CHECKOUT
        |============================================================
        |
        | Pulang sebelum:
        | shiftEnd - tolerance
        |
        */

        $isEarlyCheckout = false;

        if ($checkout && $checkout->lt($shiftEnd->copy()->subMinutes($earlyTolerance))) {
            $isEarlyCheckout = true;
        }

        /*
        |============================================================
        | STEP 12: STATE (UI ONLY)
        |============================================================
        |
        | PRIORITAS:
        | 1. overtime
        | 2. late
        | 3. early_checkout
        | 4. on_time
        |
        | CATATAN:
        | - state tidak boleh dipakai untuk logic bisnis
        | - hanya untuk tampilan
        |
        */

        if ($overtimeMinutes >= $overtimeAfter) {
            $state = 'overtime';
        } elseif ($lateMinutes > $lateTolerance) {
            $state = 'late';
        } elseif ($isEarlyCheckout) {
            $state = 'early_checkout';
        } else {
            $state = 'on_time';
        }

        /*
        |============================================================
        | FINAL RESULT
        |============================================================
        */

        return [
            'state' => $state,
            'late_minutes' => $lateMinutes,
            'work_minutes' => $workMinutes,
            'overtime_minutes' => $overtimeMinutes,
        ];
    }
}
