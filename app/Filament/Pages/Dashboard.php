<?php

namespace App\Filament\Pages;

use App\Filament\Display\Widgets\DeliveryStatusTable;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected array $quotes = [
        [
            'text' => 'Kerja keras mengalahkan bakat ketika bakat tidak bekerja keras.',
            'author' => 'Tim Notke'
        ],
        [
            'text' => 'Sukses tidak datang kepada kamu. Kamu yang harus mendatanginya.',
            'author' => 'Marva Collins'
        ],
        [
            'text' => 'Lakukan yang terbaik sampai kamu tahu lebih baik. Saat kamu tahu lebih baik, lakukan lebih baik.',
            'author' => 'Maya Angelou'
        ],
        [
            'text' => 'Jangan menunggu kesempatan. Ciptakan kesempatan itu.',
            'author' => 'George Bernard Shaw'
        ],
        [
            'text' => 'Kualitas pekerjaanmu mencerminkan kualitas dirimu.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Bekerja bukan hanya untuk mencari uang, tapi juga untuk memberikan makna.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Satu-satunya cara untuk melakukan pekerjaan hebat adalah mencintai apa yang kamu lakukan.',
            'author' => 'Steve Jobs'
        ],
        [
            'text' => 'Bangkit, kerja, ulangi. Konsistensi lebih penting dari motivasi.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Fokuslah pada kemajuan, bukan kesempurnaan.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Setiap hari adalah kesempatan baru untuk jadi lebih baik dari kemarin.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Bekerjalah seolah-olah kamu akan hidup selamanya, dan beribadahlah seolah-olah kamu akan mati besok.',
            'author' => 'Ali bin Abi Thalib'
        ],
        [
            'text' => 'Sesungguhnya Allah mencintai hamba yang bekerja dengan baik (ihsan) dan profesional.',
            'author' => 'HR. Al-Baihaqi'
        ],
        [
            'text' => 'Barangsiapa yang bersungguh-sungguh, maka ia akan berhasil.',
            'author' => 'QS. An-Najm: 39'
        ],
        [
            'text' => 'Tangan di atas lebih baik daripada tangan di bawah.',
            'author' => 'HR. Bukhari dan Muslim'
        ],
        [
            'text' => 'Tidak ada makanan yang lebih baik daripada hasil kerja tangannya sendiri.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Jangan lupa scroll Pesbuk.',
            'author' => 'alwizen'
        ],
        [
            'text' => 'Bekerja bukan sekadar mencari nafkah, tapi bagian dari ibadah jika dilakukan dengan niat yang benar.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Rezeki itu bukan soal banyak atau sedikit, tapi soal berkah atau tidak.',
            'author' => 'Ustadz Adi Hidayat'
        ],
        [
            'text' => 'Lelahmu hari ini bisa jadi pahala di sisi Allah, selama niatmu benar dan caramu halal.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Jangan takut miskin karena bekerja keras, takutlah miskin karena malas.',
            'author' => 'Anonim'
        ],
        [
            'text' => 'Percayalah, setiap usaha halal yang kamu lakukan hari ini, Allah pasti lihat dan siapkan balasan terbaik.',
            'author' => 'Anonim'
        ]
    ];

    public function getGreeting(): string
    {
        $hour = Carbon::now()->hour;
        $greeting = '';

        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Selamat pagi';
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = 'Selamat siang';
        } else {
            $greeting = 'Selamat malam';
        }

        $user = Auth::user();
        $name = $user ? $user->name : '';

        return $greeting . ($name ? ', ' . $name . ' 👋' : '');
    }

    public function getRandomQuote(): array
    {
        return $this->quotes[array_rand($this->quotes)];
    }

    // Menghapus DeliveryStatusTable dari header widgets
    //    protected function getHeaderWidgets(): array
    //    {
    //        return [
    //            // DeliveryStatusTable sudah dihapus dari sini
    //        ];
    //    }

    // Tambahkan sebagai content widget untuk digunakan dalam template
    protected function getFooterWidgets(): array
    {
        return [
            DeliveryStatusTable::class
        ];
    }

    protected function getViewData(): array
    {
        return [
            'greeting' => $this->getGreeting(),
            'quote' => $this->getRandomQuote(),
        ];
    }

    protected static string $view = 'filament.pages.dashboard';
}
