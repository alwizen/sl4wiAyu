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

    public $rfid_uid = '';
    public $search = '';
    public $filterDate;

    public $alertMessage = '';
    public $alertType = '';
    public $showAlert = false;

    protected $minimumWorkHours = 0.0833333; // jam
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

        $employee = Employee::where('rfid_uid', $value)->first();

        if (! $employee) {
            $this->showAlert('Kartu tidak dikenali.', 'error');
            $this->reset('rfid_uid');
            $this->dispatch('focusInput');
            return;
        }

        $today = now()->toDateString();
        $now   = now();

        DB::transaction(function () use ($employee, $today, $now) {
            // kunci baris attendance hari ini untuk karyawan tsb
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->lockForUpdate()
                ->first();

            // belum ada record → check-in
            if (! $attendance) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date'        => $today,
                    'check_in'    => $now,
                    'status'      => 'masuk',
                    'status_in'   => 'on_time', // opsional
                ]);

                $this->showAlert("✅ {$employee->name} berhasil absen masuk pada " . $now->format('H:i'), 'success');
                return;
            }

            // safety: ada row tapi belum check_in
            if (! $attendance->check_in) {
                $attendance->check_in  = $now;
                $attendance->status    = 'masuk';
                $attendance->status_in = 'on_time';
                $attendance->save();

                $this->showAlert("✅ {$employee->name} berhasil absen masuk pada " . $now->format('H:i'), 'success');
                return;
            }

            // sudah check-in, belum check-out → coba checkout
            if (! $attendance->check_out) {
                $workedMinutes = Carbon::parse($attendance->check_in)->diffInMinutes($now);
                $minMinutes    = $this->minimumWorkHours * 60; // atau pakai $this->minimumWorkMinutes jika pakai menit

                if ($workedMinutes < $minMinutes) {
                    $remaining = $minMinutes - $workedMinutes;
                    $this->showAlert(
                        "⏰ {$employee->name}, belum bisa check-out. Minimal {$this->minimumWorkHours} jam. Sisa: " .
                            $this->formatWorkDuration($remaining),
                        'warning'
                    );
                    return;
                }

                // ✅ simpan jam pulang saja
                $attendance->check_out  = $now;
                // $attendance->status  = 'pulang';   // ❌ HAPUS / JANGAN SET
                $attendance->status_out = 'normal';   // opsional
                $attendance->save();

                $this->showAlert(
                    "✅ {$employee->name} check-out " . $now->format('H:i') .
                        ". Durasi: " . $this->formatWorkDuration($workedMinutes),
                    'success'
                );
                return;
            }


            // sudah penuh (masuk & pulang)
            $in  = Carbon::parse($attendance->check_in)->format('H:i');
            $out = Carbon::parse($attendance->check_out)->format('H:i');
            $this->showAlert("ℹ️ {$employee->name} sudah absen penuh. Masuk: {$in}, Pulang: {$out}", 'warning');
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
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.attendance-scan', compact('attendances'))
            ->layout('components.layouts.app');
    }

    /* =======================
     * Helpers (alerts & waktu)
     * ======================= */

    private function showAlert(string $message, string $type = 'info'): void
    {
        $this->alertMessage = $message;
        $this->alertType    = $type;
        $this->showAlert    = true;

        // auto-hide di frontend
        $this->dispatch('autoHideAlert');
    }

    public function hideAlert(): void
    {
        $this->showAlert    = false;
        $this->alertMessage = '';
        $this->alertType    = '';
        $this->dispatch('focusInput');
    }

    private function formatWorkDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        $parts = [];
        if ($hours > 0) $parts[] = $hours . ' jam';
        if ($mins  > 0) $parts[] = $mins . ' menit';

        return $parts ? implode(' ', $parts) : '0 menit';
    }
}
