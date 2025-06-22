<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\WorkSchedule;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class AttendanceScan extends Component
{
    use WithPagination;

    public $rfid_uid;
    public $search = '';
    public $filterDate;
    
    // Properties untuk alert
    public $alertMessage = '';
    public $alertType = '';
    public $showAlert = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->filterDate = now()->toDateString();
    }

    public function updatedRfidUid($value)
    {
        $employee = Employee::where('rfid_uid', $value)->first();

        if (!$employee) {
            $this->showAlert('Kartu tidak dikenali.', 'error');
            $this->reset('rfid_uid');
            $this->dispatch('focusInput');
            return;
        }

        $today = now()->toDateString();
        $now = now();

        $schedule = $employee->work_type === 'shift'
            ? WorkSchedule::where('employee_id', $employee->id)->where('date', $today)->first()
            : (object)[
                'start_time' => \Carbon\Carbon::createFromTime(8, 0),
                'end_time' => \Carbon\Carbon::createFromTime(17, 0),
                'type' => 'office',
            ];

        if (!$schedule) {
            $this->showAlert('Shift belum dijadwalkan hari ini.', 'error');
            $this->reset('rfid_uid');
            $this->dispatch('focusInput');
            return;
        }

        $attendance = Attendance::firstOrCreate([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if (!$attendance->check_in) {
            $attendance->check_in = $now;
            $attendance->status_in = $now->gt(\Carbon\Carbon::parse($schedule->start_time)->addMinutes(10)) ? 'late' : 'on_time';
            $attendance->save();

            $this->showAlert("{$employee->name} berhasil absen masuk pada " . now()->format('H:i'), 'success');
        } elseif (!$attendance->check_out) {
            if ($now->lt(\Carbon\Carbon::parse($schedule->end_time)->subHours(1))) {
                $this->showAlert('Terlalu cepat untuk check-out.', 'warning');
            } else {
                $attendance->check_out = $now;
                $attendance->status_out = $now->lt(\Carbon\Carbon::parse($schedule->end_time)->subMinutes(10)) ? 'early_leave' : 'on_time';
                $attendance->save();
                $this->showAlert("{$employee->name} berhasil check-out pada " . now()->format('H:i'), 'success');
            }
        } else {
            $this->showAlert("{$employee->name} sudah absen penuh hari ini.", 'error');
        }

        $this->reset('rfid_uid');
        $this->resetPage();
        $this->dispatch('focusInput');
    }

    private function showAlert($message, $type)
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
        $this->showAlert = true;
        
        // Auto-hide alert setelah 5 detik
        $this->dispatch('autoHideAlert');
    }

    public function hideAlert()
    {
        $this->showAlert = false;
        $this->alertMessage = '';
        $this->alertType = '';
        $this->dispatch('focusInput');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterDate()
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
}