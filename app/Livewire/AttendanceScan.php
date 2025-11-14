<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceScan extends Component
{
    use WithPagination;

    public $allowEarlyMinutes = 30; // izinkan 30 menit sebelum shift mulai

    public $rfid_uid = '';
    public $search = '';
    public $filterDate;

    public $alertMessage = '';
    public $alertType = '';
    public $showAlert = false;

    protected $minimumWorkHours = 0.0833333; // 5 menit dalam jam
    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
    }
    public function submitRfid(): void
    {
        $value = trim((string) $this->rfid_uid);

        if ($value === '') {
            $this->dispatch('focusInput');
            return;
        }

        $employee = Employee::with('department')->where('rfid_uid', $value)->first();

        if (! $employee) {
            $this->showAlert('Kartu tidak dikenali.', 'error');
            $this->reset('rfid_uid');
            $this->dispatch('focusInput');
            return;
        }

        // Validasi departemen
        if (! $employee->department) {
            $this->showAlert('Karyawan belum memiliki departemen. Hubungi admin.', 'error');
            $this->reset('rfid_uid');
            $this->dispatch('focusInput');
            return;
        }

        $now = now();
        $department = $employee->department;

        // Tentukan tanggal attendance berdasarkan jam kerja departemen (method di model Department)
        $attendanceDate = $department->getAttendanceDate($now);

        // === VALIDASI: hanya boleh absen di dalam jam shift yang ditetapkan ===
        if ($department->start_time && $department->end_time) {

            $shiftStart = Carbon::parse($attendanceDate . ' ' . $department->start_time);
            $shiftEnd = Carbon::parse($attendanceDate . ' ' . $department->end_time);

            // Handle overnight shift
            if ($department->is_overnight && $shiftEnd->lessThanOrEqualTo($shiftStart)) {
                $shiftEnd->addDay();
            }

            // Izinkan absen sebelum shiftStart (early window)
            $allowedStart = $shiftStart->copy()->subMinutes($this->allowEarlyMinutes);

            // Validasi waktu scan
            if ($now->lt($allowedStart) || $now->gt($shiftEnd)) {

                $startLabel = $shiftStart->format('H:i');
                $endLabel = $shiftEnd->format('H:i');

                $this->showAlert(
                    "⛔ Di luar jam kerja. Jam aktif departemen {$department->name}: {$startLabel} - {$endLabel}.",
                    'error'
                );

                $this->dispatch('playTTS', [
                    'text' => "Maaf, Anda di luar jam kerja. Jam aktif {$department->name} adalah {$startLabel} sampai {$endLabel}.",
                    'type' => 'error'
                ]);

                $this->reset('rfid_uid');
                $this->dispatch('focusInput');
                return;
            }
        }

        DB::transaction(function () use ($employee, $department, $attendanceDate, $now) {
            // Kunci baris attendance untuk tanggal ini
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $attendanceDate)
                ->lockForUpdate()
                ->first();

            // CASE 1: Belum ada record → Check-in
            if (! $attendance) {
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $attendanceDate,
                    'check_in' => $now,
                    'status' => 'masuk',
                    'status_in' => $department->isOnTime($now) ? 'on_time' : 'late',
                    // status_out tetap null sampai checkout
                ]);

                $statusText = $attendance->status_in === 'on_time' ? 'tepat waktu' : 'terlambat';
                $shiftInfo = $department->start_time ? " (Shift: {$department->shift_time})" : "";
                $alertMessage = "✅ {$employee->name} - {$department->name}{$shiftInfo} berhasil absen masuk {$statusText} pada " . $now->format('H:i');
                $this->showAlert($alertMessage, 'success');

                $this->dispatch('playTTS', [
                    'text' => "Selamat datang {$employee->name}, departemen {$department->name}",
                    'type' => 'checkin'
                ]);
                return;
            }

            // CASE 2: Ada record tapi belum check_in (safety case)
            if (! $attendance->check_in) {
                $attendance->check_in = $now;
                $attendance->status = 'masuk';
                $attendance->status_in = $department->isOnTime($now) ? 'on_time' : 'late';
                $attendance->save();

                $statusText = $attendance->status_in === 'on_time' ? 'tepat waktu' : 'terlambat';
                $shiftInfo = $department->start_time ? " (Shift: {$department->shift_time})" : "";
                $alertMessage = "✅ {$employee->name} - {$department->name}{$shiftInfo} berhasil absen masuk {$statusText} pada " . $now->format('H:i');
                $this->showAlert($alertMessage, 'success');

                $this->dispatch('playTTS', [
                    'text' => "Selamat datang {$employee->name}, departemen {$department->name}",
                    'type' => 'checkin'
                ]);
                return;
            }

            // CASE 3: Sudah check-in, belum check-out → Coba checkout
            if (! $attendance->check_out) {
                $workedMinutes = Carbon::parse($attendance->check_in)->diffInMinutes($now);
                $minMinutes = (int) round($this->minimumWorkHours * 60);

                if ($workedMinutes < $minMinutes) {
                    $remaining = $minMinutes - $workedMinutes;
                    $this->showAlert(
                        "⏰ {$employee->name}, belum bisa check-out. Minimal " .
                            $minMinutes . " menit. Sisa: " .
                            $this->formatWorkDuration($remaining),
                        'warning'
                    );
                    return;
                }

                // Simpan checkout
                $attendance->check_out = $now;

                // Tentukan status_out: 'normal' atau 'early'
                $attendance->status_out = 'normal';

                if ($department->end_time) {
                    // Build shift start & end berdasarkan attendanceDate
                    $shiftStart = Carbon::parse($attendanceDate . ' ' . $department->start_time);
                    $shiftEnd = Carbon::parse($attendanceDate . ' ' . $department->end_time);

                    // Jika shift melewati tengah malam, pastikan shiftEnd berada setelah shiftStart
                    if ($department->is_overnight && $shiftEnd->lessThanOrEqualTo($shiftStart)) {
                        $shiftEnd->addDay();
                    }

                    // Jika checkout terjadi sebelum shift end => dianggap pulang lebih awal
                    if ($now->lessThan($shiftEnd)) {
                        $attendance->status_out = 'early';
                    }
                }

                $attendance->save();

                $alertMessage = "✅ {$employee->name} - {$department->name} check-out " . $now->format('H:i') .
                    ". Durasi kerja: " . $this->formatWorkDuration($workedMinutes) .
                    ($attendance->status_out === 'early' ? ' (Pulang lebih awal)' : '');
                $this->showAlert($alertMessage, 'success');

                $this->dispatch('playTTS', [
                    'text' => "Terima kasih {$employee->name}, hati-hati di jalan",
                    'type' => 'checkout'
                ]);
                return;
            }

            // CASE 4: Sudah penuh (check-in & check-out)
            $in = Carbon::parse($attendance->check_in)->format('H:i');
            $out = Carbon::parse($attendance->check_out)->format('H:i');
            $alertMessage = "ℹ️ {$employee->name} - {$department->name} sudah absen penuh. Masuk: {$in}, Pulang: {$out}";
            $this->showAlert($alertMessage, 'warning');

            $this->dispatch('playTTS', [
                'text' => "Sudah melakukan absen penuh {$employee->name}",
                'type' => 'complete'
            ]);
        });

        $this->reset('rfid_uid');
        $this->resetPage();
        $this->dispatch('focusInput');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $attendances = Attendance::with(['employee', 'employee.department'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('nip', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterDate, function ($query) {
                $query->whereDate('date', $this->filterDate);
            })
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(10);

        return view('livewire.attendance-scan', compact('attendances'))
            ->layout('components.layouts.app');
    }

    private function showAlert(string $message, string $type = 'info'): void
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
        $this->showAlert = true;
        $this->dispatch('autoHideAlert');
    }

    public function hideAlert(): void
    {
        $this->showAlert = false;
        $this->alertMessage = '';
        $this->alertType = '';
        $this->dispatch('focusInput');
    }

    private function formatWorkDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($hours > 0) $parts[] = $hours . ' jam';
        if ($mins > 0) $parts[] = $mins . ' menit';

        return $parts ? implode(' ', $parts) : '0 menit';
    }
}
