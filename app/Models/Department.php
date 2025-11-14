<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Department extends Model
{
    protected $fillable = [
        'name',
        'allowance',
        'salary',
        'bonus',
        'absence_deduction',
        'permit_amount',
        'start_time',
        'end_time',
        'is_overnight',
        'tolerance_late_minutes',
    ];

    protected $casts = [
        'permit_amount' => 'integer',
        'is_overnight' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Tentukan tanggal attendance berdasarkan waktu tap dan shift departemen
     */
    public function getAttendanceDate(Carbon $tapTime): string
    {
        // Jika tidak ada jam kerja di-set, gunakan tanggal hari ini
        if (!$this->start_time || !$this->end_time) {
            return $tapTime->toDateString();
        }

        // Jika shift tidak melewati tengah malam
        if (!$this->is_overnight) {
            return $tapTime->toDateString();
        }

        // Shift melewati tengah malam
        $startHour = Carbon::parse($this->start_time)->hour;
        $endHour = Carbon::parse($this->end_time)->hour;
        $tapHour = $tapTime->hour;

        // Jika tap di antara jam 00:00 - end_time
        // Anggap sebagai lanjutan shift kemarin
        if ($tapHour >= 0 && $tapHour < $endHour) {
            return $tapTime->copy()->subDay()->toDateString();
        }

        // Jika tap di jam start_time - 23:59
        // Gunakan tanggal hari ini
        return $tapTime->toDateString();
    }

    /**
     * Cek apakah waktu tap masih dalam toleransi keterlambatan
     */
    public function isOnTime(\Illuminate\Support\Carbon $checkInTime): bool
    {
        // Jika tidak ada jam kerja, anggap on time
        if (! $this->start_time) {
            return true;
        }

        // Pastikan tolerance terisi (fallback 0)
        $tolerance = (int) ($this->tolerance_late_minutes ?? 0);

        // Tentukan tanggal attendance (menggunakan method existing)
        $attendanceDate = $this->getAttendanceDate($checkInTime);

        // Jika attendanceDate gagal/tidak valid, anggap tidak on time (safety)
        if (! $attendanceDate) {
            return false;
        }

        // Build shiftStart & shiftEnd dari attendanceDate + start/end time
        try {
            $shiftStart = \Carbon\Carbon::parse($attendanceDate . ' ' . $this->start_time);
        } catch (\Throwable $e) {
            return false;
        }

        // Jika end_time tersedia, buat shiftEnd
        $shiftEnd = null;
        if ($this->end_time) {
            $shiftEnd = \Carbon\Carbon::parse($attendanceDate . ' ' . $this->end_time);

            // Jika shift melewati tengah malam, shiftEnd harus berada setelah shiftStart
            if ($this->is_overnight && $shiftEnd->lessThanOrEqualTo($shiftStart)) {
                $shiftEnd->addDay();
            }
        }

        // Hitung selisih dalam menit (positif = terlambat, negatif = lebih awal)
        $diffInMinutes = $checkInTime->diffInMinutes($shiftStart, false);

        // --- tambahan safety checks ---
        // 1) Jika check-in terlalu jauh lebih awal dari shiftStart => bukan on time untuk shift ini.
        //    Contoh: check-in 20:28 untuk shift start 02:30 → diff sangat besar positif atau besar negatif tergantung tanggal.
        //    Kita definisikan ambang "maks early" (mis. 6 jam) — sesuaikan bila perlu.
        $maxEarlyMinutes = 6 * 60; // 6 jam

        if ($diffInMinutes < -$maxEarlyMinutes) {
            // terlalu awal (mis. check-in beberapa hari atau jam sebelum shift)
            return false;
        }

        // 2) Jika check-in sangat jauh setelah shiftEnd (mis. > 24 jam), jangan anggap on time
        if ($shiftEnd && $checkInTime->greaterThan($shiftEnd->copy()->addHours(24))) {
            return false;
        }

        // Terakhir: on time jika diffInMinutes <= tolerance
        return $diffInMinutes <= $tolerance;
    }


    /**
     * Get formatted shift time untuk display
     */
    public function getShiftTimeAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return 'Belum diatur';
        }

        $start = Carbon::parse($this->start_time)->format('H:i');
        $end = Carbon::parse($this->end_time)->format('H:i');

        return "{$start} - {$end}" . ($this->is_overnight ? ' (melewati malam)' : '');
    }
}
