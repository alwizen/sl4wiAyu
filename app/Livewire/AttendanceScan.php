<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class AttendanceScan extends Component
{
    use WithPagination;

    public $rfid_uid;
    public $search = '';
    public $filterDate;

    public $alertMessage = '';
    public $alertType = '';
    public $showAlert = false;

    // Konfigurasi jam kerja minimum (dalam jam)
    protected $minimumWorkHours = 3;

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

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['status' => 'masuk'] // Status default jika baru dibuat
        );

        // Jika belum check-in (absen masuk)
        if (!$attendance->check_in) {
            $attendance->check_in = $now;
            $attendance->status = 'masuk';
            $attendance->save();

            $this->showAlert(
                "✅ {$employee->name} berhasil absen masuk pada " . $now->format('H:i'), 
                'success'
            );
        } 
        // Jika sudah check-in tapi belum check-out (absen pulang)
        elseif (!$attendance->check_out) {
            
            // Cek durasi kerja
            $checkInTime = Carbon::parse($attendance->check_in);
            $workDuration = $checkInTime->diffInHours($now);
            
            // Jika belum mencapai jam kerja minimum
            if ($workDuration < $this->minimumWorkHours) {
                $remainingHours = $this->minimumWorkHours - $workDuration;
                $remainingMinutes = ($remainingHours - floor($remainingHours)) * 60;
                
                $timeRemaining = floor($remainingHours) . ' jam';
                if ($remainingMinutes > 0) {
                    $timeRemaining .= ' ' . round($remainingMinutes) . ' menit';
                }
                
                $this->showAlert(
                    "⏰ {$employee->name}, Anda belum bisa check-out. Waktu kerja minimal {$this->minimumWorkHours} jam. Sisa waktu: {$timeRemaining}", 
                    'warning'
                );
            } else {
                // Jika sudah mencapai jam kerja minimum, boleh check-out
                $attendance->check_out = $now;
                $attendance->save();

                // Hitung total jam kerja
                $totalWorkHours = $checkInTime->diffInHours($now);
                $totalWorkMinutes = $checkInTime->diffInMinutes($now) % 60;
                
                $workTimeDisplay = $totalWorkHours . ' jam';
                if ($totalWorkMinutes > 0) {
                    $workTimeDisplay .= ' ' . $totalWorkMinutes . ' menit';
                }

                $this->showAlert(
                    "✅ {$employee->name} berhasil check-out pada " . $now->format('H:i') . 
                    ". Total jam kerja: {$workTimeDisplay}", 
                    'success'
                );
            }
        } 
        // Jika sudah absen penuh (check-in dan check-out)
        else {
            $checkInTime = Carbon::parse($attendance->check_in)->format('H:i');
            $checkOutTime = Carbon::parse($attendance->check_out)->format('H:i');
            
            $this->showAlert(
                "ℹ️ {$employee->name} sudah absen penuh hari ini. Masuk: {$checkInTime}, Pulang: {$checkOutTime}", 
                'warning'
            );
        }

        $this->reset('rfid_uid');
        $this->resetPage();
        $this->dispatch('focusInput');
    }

    /**
     * Mendapatkan jam kerja minimum yang diperlukan
     */
    public function getMinimumWorkHours()
    {
        return $this->minimumWorkHours;
    }

    /**
     * Mengatur jam kerja minimum (untuk konfigurasi dinamis jika diperlukan)
     */
    public function setMinimumWorkHours($hours)
    {
        $this->minimumWorkHours = max(1, $hours); // Minimal 1 jam
    }

    /**
     * Helper function untuk menghitung sisa waktu kerja
     */
    private function calculateRemainingWorkTime($checkInTime, $currentTime)
    {
        $checkIn = Carbon::parse($checkInTime);
        $current = Carbon::parse($currentTime);
        $workedHours = $checkIn->diffInHours($current);
        
        if ($workedHours >= $this->minimumWorkHours) {
            return null; // Sudah memenuhi jam kerja minimum
        }
        
        $remainingMinutes = ($this->minimumWorkHours * 60) - $checkIn->diffInMinutes($current);
        
        return [
            'hours' => floor($remainingMinutes / 60),
            'minutes' => $remainingMinutes % 60,
            'total_minutes' => $remainingMinutes
        ];
    }

    /**
     * Helper function untuk format waktu yang lebih readable
     */
    private function formatWorkDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        $result = '';
        if ($hours > 0) {
            $result .= $hours . ' jam';
        }
        if ($mins > 0) {
            if ($hours > 0) $result .= ' ';
            $result .= $mins . ' menit';
        }
        
        return $result ?: '0 menit';
    }

    private function showAlert($message, $type)
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
        $this->showAlert = true;
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