-- Script untuk menghapus semua data reservasi dan customer
-- Mengembalikan website ke kondisi awal (fresh installation)
-- Jalankan script ini di phpMyAdmin atau MySQL client

USE studio_foto_cekrek;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Hapus semua data pembayaran
DELETE FROM payments;

-- 2. Hapus semua data reservasi
DELETE FROM reservations;

-- 3. Hapus semua customer (kecuali admin)
DELETE FROM users WHERE role = 'customer';

-- 4. Reset AUTO_INCREMENT untuk tabel yang dikosongkan
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE reservations AUTO_INCREMENT = 1;

-- 5. Reset AUTO_INCREMENT untuk users (mulai dari ID 2 karena admin ID = 1)
-- Jangan reset jika ingin mempertahankan ID admin = 1
-- ALTER TABLE users AUTO_INCREMENT = 2;

-- 6. Hapus data jadwal yang mungkin terkait dengan reservasi
DELETE FROM schedules;
ALTER TABLE schedules AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Tampilkan status setelah pembersihan
SELECT 'Data berhasil dibersihkan!' as status;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_reservations FROM reservations;
SELECT COUNT(*) as total_payments FROM payments;
SELECT COUNT(*) as total_schedules FROM schedules;
