# Setup WhatsApp Notification - Studio Foto Cekrek

## 🎯 Yang Sudah Dibuat
Sistem WhatsApp notification yang mengirim pesan ke customer setelah admin konfirmasi pembayaran.

## ⚙️ Konfigurasi (Hanya 2 Langkah)

### 1. Daftar di Fonnte.com
- Buka https://fonnte.com
- Daftar gratis dan dapatkan token

### 2. Edit Konfigurasi
Edit file `config/whatsapp_notification.php` baris 6:
```php
define('WHATSAPP_TOKEN', 'YOUR_FONNTE_TOKEN'); // Ganti dengan token Anda
```

## 🔄 Cara Kerja
1. Customer bayar → Admin konfirmasi di halaman Payments
2. Sistem otomatis kirim WhatsApp ke customer
3. Customer terima notifikasi dengan detail reservasi

## 🧪 Test
- Login admin → Menu "Test WhatsApp" 
- Test dengan nomor: **6285709982869**

## 📱 Template Pesan
```
🎉 PEMBAYARAN DIKONFIRMASI 🎉

Halo [Nama],
Pembayaran Anda telah berhasil dikonfirmasi!

📋 Detail Reservasi:
• ID: #000001
• Paket: Solo
• Tanggal: 13/07/2025
• Total: Rp 30.000

✅ Status: DIKONFIRMASI
Silakan datang sesuai jadwal.

📸 Studio Foto Cekrek
```

## 📁 File yang Dibuat
- `config/whatsapp_notification.php` - Konfigurasi WhatsApp
- `admin/test_whatsapp.php` - Test WhatsApp
- `admin/payments.php` - Sudah terintegrasi notifikasi

## ✅ Siap Digunakan
Setelah konfigurasi token Fonnte, sistem langsung bisa digunakan!
