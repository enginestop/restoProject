# restoProject

Aplikasi restoran berbasis PHP dan MySQL untuk website pelanggan, reservasi, POS, dapur, pembayaran, serta laporan.

![Beranda pelanggan](docs/screenshots/home.png)

## Fitur

- Pelanggan: registrasi, login, profil, poin, dan reservasi meja.
- Waiter dan Chef: POS, pesanan dapur, reservasi, pembayaran, dan struk PDF.
- Manager: akun, staf, member, meja, menu, statistik, serta laporan PDF.
- Tampilan responsif untuk ponsel, tablet, dan desktop.

Mata uang RM, pajak 10%, zona waktu Asia/Kuala_Lumpur. Pembayaran kartu merupakan simulasi pencatatan; tidak terhubung ke bank atau payment gateway.

## Teknologi

PHP 8.3, MySQL 8.0, Apache 2.4, HTML, CSS, JavaScript, dan FPDF 1.86. Skrip instalasi ditujukan untuk server baru Ubuntu 24.04.

## Instalasi

Jalankan di Bash pada server Ubuntu 24.04 yang belum memiliki database `restaurantdb`:

```bash
sudo apt-get update
sudo apt-get install -y git
sudo git clone https://github.com/enginestop/restoProject.git /var/www/restaurant-project
cd /var/www/restaurant-project
sudo bash deployment/install-server.sh
sudo bash deployment/setup-database.sh
sudo php bin/create-manager.php
sudo env RESTAURANT_SERVER_NAME=localhost bash deployment/activate-server.sh
```

Ganti `localhost` dengan IP atau hostname server. Perintah `create-manager.php` meminta nama, email, nomor telepon, dan password, kemudian menampilkan **Staff Account ID** untuk login. Tidak ada akun atau password bawaan. Tambahkan meja, menu, dan staf melalui sesi Manager setelah instalasi.

Konfigurasi database dibuat otomatis di `/etc/restaurant-project/database.php`, di luar direktori aplikasi. Aplikasi juga mendukung path konfigurasi melalui variabel lingkungan `RESTAURANT_CONFIG`. Skrip setup menolak menimpa database yang sudah ada.

| Portal | Alamat |
|---|---|
| Pelanggan | `/` |
| Login pelanggan | `/customerSide/customerLogin/login.php` |
| Login staf | `/adminSide/StaffLogin/login.php` |

Login staf memakai **Staff Account ID** dan password; pelanggan memakai email dan password. Database baru hanya berisi struktur dan catatan migrasi, tanpa data akun atau transaksi dari instalasi lain.

## Screenshot

![Point of Sale](docs/screenshots/pos.png)

[Buka galeri lengkap: 66 screenshot halaman, formulir, status, tampilan ponsel, dan PDF](docs/SCREENSHOTS.md).

Screenshot memakai data contoh dari sesi validasi. Halaman alias menggunakan tampilan yang sama. Endpoint penyimpanan dan pengalihan tidak memiliki antarmuka terpisah.

## Pemeriksaan sumber

```bash
find adminSide customerSide common bin -name '*.php' -print0 | xargs -0 -n1 php -l
bash -n deployment/install-server.sh
bash -n deployment/setup-database.sh
bash -n deployment/activate-server.sh
```

## Lisensi dan kredit

[Apache License 2.0](LICENSE). Dikembangkan dari [RestaurantProject](https://github.com/BryanTheLai/RestaurantProject) oleh Bryan, Yong, Kevin, Edzer, dan kontributor. Lihat [NOTICE](NOTICE) serta [lisensi FPDF](adminSide/posBackend/fpdf186/license.txt).
