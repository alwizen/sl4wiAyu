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
        ],
        [
            'text' => 'Gizi seimbang adalah kunci tubuh sehat dan pikiran cerdas.',
            'author' => 'Dr. Sari Nila'
        ],
        [
            'text' => 'Kita adalah apa yang kita makan — jadi makanlah dengan bijak.',
            'author' => 'Michael Pollan'
        ],
        [
            'text' => 'Anak sehat lahir dari makanan bergizi dan kasih sayang yang cukup.',
            'author' => 'Martha Stewart'
        ],
        [
            'text' => 'Piringmu hari ini, cerminan kesehatanmu esok hari.',
            'author' => 'Ibu Sehat'
        ],
        [
            'text' => 'Gizi bukan soal mahal, tapi soal pilihan dan pengetahuan.',
            'author' => 'Dr. Anthony Fauci'
        ],
        [
            'text' => 'Makan secukupnya, gizi seimbang, hidup jadi tenang.',
            'author' => 'Dian Sastrowardoyo'
        ],
        [
            'text' => 'Isi piringmu dengan warna-warni sayur dan buah, bukan hanya karbohidrat.',
            'author' => 'Jamie Oliver'
        ],
        [
            'text' => 'Makanan sehat hari ini adalah investasi kesehatan masa depan.',
            'author' => 'Prof. Dr. Endang Rahayu'
        ],
        [
            'text' => 'Badan kuat, otak cerdas, dimulai dari gizi yang berkualitas.',
            'author' => 'Dr. Oz'
        ],
        [
            'text' => 'Gizi baik adalah hak setiap anak bangsa.',
            'author' => 'Sri Mulyani Indrawati'
        ],
        [
            'text' => 'Gizi bukan hanya kebutuhan jasmani, tapi juga bentuk rasa syukur.',
            'author' => 'KH. Abdul Jalil'
        ],
        [
            'text' => 'Makan bukan hanya kenyang, tapi juga harus bergizi.',
            'author' => 'Ayu Lestari'
        ],
        [
            'text' => 'Satu sendok sayur lebih berarti daripada satu kantong obat.',
            'author' => 'Dr. John Smith'
        ],
        [
            'text' => 'Keluarga sehat berawal dari dapur yang bergizi.',
            'author' => 'Susi Pudjiastuti'
        ],
        [
            'text' => 'Jangan tunggu sakit untuk sadar pentingnya gizi.',
            'author' => 'Dr. Andi Wijaya'
        ],
        [
            'text' => 'Ilmu tanpa gizi, seperti motor tanpa bensin.',
            'author' => 'B.J. Habibie'
        ],
        [
            'text' => 'Anak bergizi, prestasi pasti.',
            'author' => 'Najwa Shihab'
        ],
        [
            'text' => 'Gizi buruk bisa dicegah dengan kepedulian kita bersama.',
            'author' => 'Dr. Nila Moeloek'
        ],
        [
            'text' => 'Sayangi diri, mulai dari isi piring sendiri.',
            'author' => 'Ibu Kartini'
        ],
        [
            'text' => 'Gizi itu cinta yang terlihat dalam bentuk makanan sehat.',
            'author' => 'Tere Liye'
        ],
        [
            'text' => 'Orang hebat bukan hanya pintar, tapi juga sehat.',
            'author' => 'Albert Einstein'
        ],
        [
            'text' => 'Isi piringku hari ini menentukan kualitas hidupku nanti.',
            'author' => 'Dewi Hughes'
        ],
        [
            'text' => 'Makanan sehat bukan gaya hidup mahal, tapi kebutuhan mendasar.',
            'author' => 'Dr. Anand Kumar'
        ],
        [
            'text' => 'Tubuh yang kuat berasal dari makanan yang tepat.',
            'author' => 'Soekarno'
        ],
        [
            'text' => 'Gizi seimbang menciptakan generasi yang tangguh.',
            'author' => 'Menteri Kesehatan RI'
        ],
        [
            'text' => 'Bukan soal banyaknya makan, tapi lengkapnya gizi.',
            'author' => 'Dr. Hilda'
        ],
        [
            'text' => 'Buah dan sayur adalah sahabat sejati tubuh.',
            'author' => 'Rachel Carson'
        ],
        [
            'text' => 'Minum air putih yang cukup adalah bagian dari pola makan sehat.',
            'author' => 'Dr. James Levine'
        ],
        [
            'text' => 'Kesehatan adalah nikmat, dan gizi adalah jalannya.',
            'author' => 'Ustadz Adi Hidayat'
        ],
        [
            'text' => 'Gizi baik itu bukan tren, tapi kebutuhan sepanjang hidup.',
            'author' => 'Prof. Ani Suryani'
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
