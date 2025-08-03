# Studio Foto Cekrek - Sistem Informasi Manajemen Reservasi

Sistem manajemen reservasi studio foto yang komprehensif dengan fitur booking online, manajemen pelanggan, dan dashboard admin.

## Fitur Utama

### Untuk Pelanggan
- **Registrasi & Login** - Sistem autentikasi pengguna
- **Booking Online** - Reservasi sesi foto dengan berbagai paket
- **Manajemen Profil** - Update informasi pribadi
- **Riwayat Reservasi** - Lihat dan kelola booking
- **Galeri Portfolio** - Lihat hasil karya studio

### Untuk Admin
- **Dashboard Admin** - Statistik dan overview bisnis
- **Manajemen Reservasi** - Kelola semua booking
- **Manajemen Paket** - CRUD paket fotografi
- **Manajemen Pelanggan** - Data customer
- **Laporan** - Analisis bisnis

### Fitur Teknis
- **Responsive Design** - Kompatibel dengan semua device
- **Database MySQL** - Penyimpanan data yang aman
- **Session Management** - Keamanan login
- **Form Validation** - Validasi input client & server side
- **Image Gallery** - Lightbox untuk portfolio

## Teknologi yang Digunakan

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache (XAMPP)
- **Libraries**: 
  - Font Awesome (Icons)
  - Lightbox2 (Image Gallery)
  - jQuery (Enhanced Interactions)

## Instalasi

### Prasyarat
- XAMPP atau server Apache dengan PHP 7.4+
- MySQL 5.7+
- Web browser modern

### Langkah Instalasi

1. **Clone atau Download Project**
   ```bash
   git clone [repository-url]
   # atau download dan extract ke folder htdocs
   ```

2. **Setup Database**
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Import file `database/setup.sql`
   - Database `studio_foto_cekrek` akan dibuat otomatis

3. **Konfigurasi Database**
   - Edit file `config/database.php` jika perlu
   - Default settings:
     - Host: localhost
     - Database: studio_foto_cekrek
     - Username: root
     - Password: (kosong)

4. **Jalankan Aplikasi**
   - Pastikan XAMPP Apache dan MySQL running
   - Buka browser: `http://localhost/STUDIO_FOTO_CEKREK`

### Login Default
- **Admin**
  - Email: admin@studiofotocekrek.com
  - Password: password

## Struktur Project

```
STUDIO_FOTO_CEKREK/
├── admin/                  # Admin panel
│   └── dashboard.php      # Dashboard admin
├── assets/                # Static files
│   ├── css/
│   │   └── style.css     # Custom styles
│   └── js/
│       └── main.js       # JavaScript functions
├── config/                # Configuration files
│   └── database.php      # Database config
├── database/              # Database files
│   └── setup.sql         # Database schema
├── index.php             # Homepage
├── login.php             # Login page
├── register.php          # Registration page
├── booking.php           # Booking form
├── my-reservations.php   # User reservations
├── profile.php           # User profile
├── gallery.php           # Portfolio gallery
├── logout.php            # Logout handler
└── README.md             # Documentation
```

## Database Schema

### Tabel Utama
- **users** - Data pengguna (customer, admin, photographer)
- **packages** - Paket fotografi
- **reservations** - Data booking
- **payments** - Riwayat pembayaran
- **gallery** - Portfolio images
- **schedules** - Jadwal ketersediaan
- **settings** - Pengaturan sistem

## Penggunaan

### Untuk Pelanggan
1. **Registrasi** - Daftar akun baru di halaman register
2. **Login** - Masuk dengan email dan password
3. **Booking** - Pilih paket, tanggal, dan waktu
4. **Pembayaran** - Lakukan pembayaran sesuai instruksi
5. **Konfirmasi** - Tunggu konfirmasi dari admin

### Untuk Admin
1. **Login** - Gunakan akun admin
2. **Dashboard** - Lihat statistik dan overview
3. **Kelola Reservasi** - Konfirmasi, ubah status booking
4. **Kelola Paket** - Tambah, edit, hapus paket
5. **Laporan** - Generate laporan bisnis

## Kustomisasi

### Mengubah Informasi Studio
Edit file `database/setup.sql` bagian settings:
```sql
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('studio_name', 'Nama Studio Anda', 'Nama studio foto'),
('studio_address', 'Alamat Studio', 'Alamat studio'),
-- dst...
```

### Menambah Paket Baru
1. Login sebagai admin
2. Masuk ke menu Paket
3. Klik "Tambah Paket Baru"
4. Isi detail paket dan simpan

### Mengubah Tampilan
- Edit file `assets/css/style.css` untuk styling
- Ubah warna di CSS variables (`:root`)
- Modifikasi layout di file PHP

## Troubleshooting

### Error Database Connection
- Pastikan MySQL running
- Cek konfigurasi di `config/database.php`
- Pastikan database sudah dibuat

### Error 404 Not Found
- Pastikan file ada di folder yang benar
- Cek URL dan path file
- Pastikan Apache running

### Session Error
- Pastikan session_start() dipanggil
- Cek permission folder untuk session
- Clear browser cache

## Pengembangan Lanjutan

### Fitur yang Bisa Ditambahkan
- **Payment Gateway** - Integrasi dengan Midtrans/OVO/GoPay
- **Email Notification** - Notifikasi otomatis via email
- **SMS Gateway** - Notifikasi via SMS
- **Calendar Integration** - Sinkronisasi dengan Google Calendar
- **Multi-language** - Support bahasa Indonesia dan Inggris
- **Mobile App** - Aplikasi mobile dengan React Native
- **Advanced Reporting** - Dashboard analytics yang lebih detail

### API Development
- RESTful API untuk mobile app
- Authentication dengan JWT
- Rate limiting dan security

## Kontribusi

Untuk berkontribusi pada project ini:
1. Fork repository
2. Buat branch baru untuk fitur
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

Project ini menggunakan lisensi MIT. Silakan gunakan dan modifikasi sesuai kebutuhan.

## Support

Untuk bantuan dan pertanyaan:
- Email: support@studiofotocekrek.com
- WhatsApp: +62-xxx-xxxx-xxxx

---

**Studio Foto Cekrek** - Mengabadikan Momen Terbaik Anda
