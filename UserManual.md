## **User Manual Aplikasi SPPG**

---

### 1. Login Page

Buka aplikasi SPPG, kemudian masukkan username dan password yang telah diberikan oleh admin. Setelah itu klik tombol **Login**.

![Login page](https://i.imgur.com/xKi0KVR.png)

---

### 2. Dashboard

Tampilan dashboard berisi informasi penting seperti:

- Ringkasan kas masuk dan keluar
- Data pengiriman hari ini
- Barang masuk dan keluar

![Dashboard](https://i.imgur.com/ksYR4eR.png)

---

### 3. Menu Harian

Fitur **Menu Harian** digunakan oleh **Ahli Gizi** untuk membuat, mengedit, menghapus, dan mengekspor data menu harian.

#### a. Membuat Menu Harian

Klik tombol **Buat Menu Harian**, lalu isi form yang tersedia.

![Membuat Menu Harian](https://i.imgur.com/AqbG4Ze.png)

#### b. Input Menu Harian

Jika menu tidak tersedia dalam daftar, klik tombol **+ (plus)** di samping form untuk menambahkan menu baru.

![Input Menu Harian](https://i.imgur.com/MPZPF5f.png)

#### c. Edit dan Hapus Menu Harian

Klik tombol **Edit** pada tabel untuk mengubah data. Form yang muncul sama seperti saat membuat menu harian.  
Untuk menghapus, klik tombol **Hapus**.

![Edit menu harian](https://i.imgur.com/AGutONi.png)

#### d. Ekspor (Download)

Untuk mengunduh data:

- Pilih data satu per satu, atau klik **kotak kecil di atas tabel** untuk memilih semua (Select All).
- Lalu klik tombol **Ekspor Daily Menu**.

![Export menu harian](https://i.imgur.com/PdYUIKQ.png)

---

### 4. Rencana Nutrisi (Daily Nutrition Plan)

Menu **Rencana Nutrisi** digunakan untuk menentukan **Kebutuhan Gizi Harian** berdasarkan data dari **Menu Harian**.  
Pilih tanggal sesuai dengan menu harian yang telah dibuat sebelumnya.

#### a. Membuat Rencana Nutrisi Harian

Klik tombol **Rencana Nutrisi**, lalu lengkapi form sesuai kebutuhan.

![Membuat Nutrisi Harian](https://i.imgur.com/SoxF1pf.png)

![Input Nutrisi Harian](https://i.imgur.com/HjMcZ2X.png)

Pilih tanggal sesuai Manu harian yang di butuhkan, kemudian akan muncul menu harian pada tanggal tersebut, **Ahli Gizi** hanya perlu mengisi form yang tertera

#### b. Edit dan Hapus Rencana Nutrisi

Untuk mengedit rencana nutrisi yang sudah ada, klik tombol **Edit** pada tabel.  
Form yang muncul akan terisi otomatis sesuai data yang dipilih, dan bisa langsung disesuaikan.

Untuk menghapus data rencana nutrisi, klik tombol **Hapus** di tabel.

---

#### c. Ekspor (Download) Rencana Nutrisi

Untuk mendownload data rencana nutrisi:

- Pilih data yang ingin diunduh, atau klik **Select All** untuk memilih semua data.
- Klik tombol **Ekspor Rencana Nutrisi** untuk mendownload dalam format Excel.

![Export Nutrisi Harian](https://i.imgur.com/bDZxDoJ.png)

#### d. Cetak PDF

Untuk mencetak rencana nutrisi ke dalam format PDF:

- Klik ikon **titik tiga (⋮)** di pojok kanan atas tabel.
- Pilih opsi **Cetak PDF** dari menu dropdown yang muncul.
- File akan otomatis didownload dalam format PDF.

![Cetak PDF Nutrisi](https://i.imgur.com/FUwniVC.png)

---

## **5. Pemesanan Supplier (Purchase Order)**

Menu ini digunakan untuk mencatat pemesanan barang dari supplier. Fitur ini hanya bisa digunakan oleh **admin pembelian** dan akan diproses lebih lanjut oleh **PIC/SPV** untuk disetujui, sebelum barang dapat diterima oleh bagian gudang.

### a. Tampilan Daftar Purchase Order

![Daftar PO](https://i.imgur.com/KxX9MGw.png)

1. Klik menu **Pemesanan Supplier (PO)** pada sidebar.
2. Tekan tombol **Tambah Purchase Order (PO)** untuk membuat PO baru.
3. Menu titik tiga (⋮) menyediakan aksi lanjutan:
   - **Tandai Lunas / Sebagian Lunas**
   - **Approve** (hanya bisa dilakukan oleh SPV/PIC)
   - **Edit / Histori**
   - **Hapus**

> **Catatan Penting:**  
> Purchase Order **tidak bisa diproses di menu Penerimaan Barang** sebelum mendapatkan **status Approved** dari PIC/SPV.

---

### b. Form Buat Purchase Order

![Form Buat PO](https://i.imgur.com/RtWM44l.png)

#### **1. Informasi Umum**
- **Nomor Order**: Diisi otomatis oleh sistem.
- **Tanggal Pemesanan**: Pilih tanggal pesanan dibuat.
- **Supplier**: Pilih supplier dari daftar.  
Jika belum tersedia, klik tombol **+ (plus)** di samping form untuk menambahkan supplier baru.
- **Status**: Default `Pending`.

#### **2. Daftar Item Pembelian**
- Pilih **Item** bahan baku yang akan dipesan.
- Isi **Jumlah** yang dibutuhkan.
- **Harga Satuan** diisi `0` terlebih dahulu (harga akan diinput saat barang diterima).
- Klik **Tambahkan ke Items** untuk menambahkan item ke daftar pembelian.

#### **3. Informasi Pembayaran**
- **Status Pembayaran**: Pilih `Belum Lunas` atau `Lunas`.
- **Total**: Kosongkan. Akan dihitung otomatis setelah harga dimasukkan.

> Setelah barang dikirim dan supplier memberikan **nota pembelian**, admin bisa **mengedit PO** untuk mengisi harga satuan sesuai nota, agar total pembelian tercatat dengan benar.

