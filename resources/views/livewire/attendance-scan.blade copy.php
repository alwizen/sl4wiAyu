{{-- resources/views/livewire/attendance-scan.blade.php --}}
<div>
    <style>
        .rfid-scanner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }
        
        .scanner-input {
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .scanner-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.3);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-on-time { 
            background-color: #dcfce7; 
            color: #166534; 
        }
        .status-late { 
            background-color: #fecaca; 
            color: #991b1b; 
        }
        .status-early-leave { 
            background-color: #fef3c7; 
            color: #92400e; 
        }
        
        .table-hover:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }
        
        .card-shadow {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold gradient-text mb-2">Sistem Absensi RFID</h1>
            <p class="text-gray-600">Sistem Manajemen Kehadiran Karyawan</p>
        </div>

        <!-- RFID Scanner Card -->
        <div class="max-w-md mx-auto mb-8">
            <div class="rfid-scanner rounded-2xl p-8 text-white text-center">
                <div class="mb-6">
                    <svg class="w-16 h-16 mx-auto mb-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-2xl font-bold mb-2">Scan Kartu RFID</h2>
                    <p class="text-blue-100">Tempelkan kartu pada scanner</p>
                </div>

                <!-- Alert Messages -->
                @if (session('success') || session('error') || session('warning'))
                    <div 
                        x-data="{ show: true }" 
                        x-init="setTimeout(() => show = false, 5000)" 
                        x-show="show" 
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-90"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-90"
                        class="mb-4 p-4 rounded-lg font-medium
                            @if(session('success')) bg-green-500 text-white
                            @elseif(session('error')) bg-red-500 text-white
                            @elseif(session('warning')) bg-yellow-500 text-white
                            @endif"
                    >
                        <div class="flex items-center">
                            @if(session('success'))
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            @elseif(session('error'))
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            @elseif(session('warning'))
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                            {{ session('success') ?? session('error') ?? session('warning') }}
                        </div>
                    </div>
                @endif
        
                <!-- RFID Input -->
                <input 
                    type="text"
                    wire:model.live="rfid_uid"
                    wire:key="rfid-input-{{ now()->timestamp }}"
                    id="rfid-input"
                    autofocus
                    placeholder="Tempelkan Kartu RFID..."
                    class="scanner-input w-full bg-white text-gray-800 border-0 p-4 text-center rounded-xl text-lg tracking-widest focus:outline-none focus:ring-4 focus:ring-white focus:ring-opacity-50" 
                    autocomplete="off"
                />
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Data Absensi Hari Ini</h3>
                    
                    <!-- Filters -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="relative">
                            <input type="text" 
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Cari nama atau NIP..."
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        
                        <input type="date" 
                               wire:model.live="filterDate"
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $attendance)
                            <tr class="table-hover cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                                                {{ substr($attendance->employee->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $attendance->employee->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $attendance->employee->nip }}</div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $attendance->employee->department->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ ucfirst($attendance->employee->work_type) }}</div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attendance->check_in)
                                        <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') }}</div>
                                        <span class="status-badge {{ $attendance->status_in === 'on_time' ? 'status-on-time' : 'status-late' }}">
                                            {{ $attendance->status_in === 'on_time' ? 'Tepat Waktu' : 'Terlambat' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">Belum Check In</span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attendance->check_out)
                                        <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') }}</div>
                                        <span class="status-badge {{ $attendance->status_out === 'on_time' ? 'status-on-time' : 'status-early-leave' }}">
                                            {{ $attendance->status_out === 'on_time' ? 'Tepat Waktu' : 'Pulang Cepat' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">Belum Check Out</span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attendance->check_in && $attendance->check_out)
                                        <span class="status-badge status-on-time">Lengkap</span>
                                    @elseif($attendance->check_in)
                                        <span class="status-badge" style="background-color: #dbeafe; color: #1e40af;">Check In</span>
                                    @else
                                        <span class="status-badge" style="background-color: #f3f4f6; color: #374151;">Belum Absen</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data absensi</p>
                                    <p class="text-sm">Data absensi akan muncul setelah karyawan melakukan scan RFID</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($attendances->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-xl p-6 card-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Hadir Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $attendances->where('check_in', '!=', null)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 card-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tepat Waktu</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $attendances->where('status_in', 'on_time')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 card-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Terlambat</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $attendances->where('status_in', 'late')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>