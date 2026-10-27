# Sangrai – Landing Page PHP Native

Landing page sederhana (PHP native, tanpa framework) untuk roastery kopi fiktif.

## Struktur
```
index.php          halaman utama + proses form
config.php         data konten & helper
includes/          header.php, footer.php
assets/            css/style.css, js/app.js
db.php             koneksi database (MySQL di Azure, SQLite di lokal)
admin.php          halaman CRUD produk (login password)
storage/           file SQLite lokal (tidak di-commit)
```

## Menjalankan lokal
Butuh PHP 8.0+.
```bash
php -S localhost:8000
```
Buka http://localhost:8000

## Push ke GitHub private
```bash
git init
git add .
git commit -m "Initial commit: landing page PHP native"
git branch -M main
git remote add origin https://github.com/USERNAME/sangrai-landing.git
git push -u origin main
```
(Buat repo dulu di github.com/new, pilih **Private**.)

## Database & CRUD
- Lokal: tanpa konfigurasi, otomatis memakai SQLite. Admin: http://localhost:8000/admin.php (password default `admin123`).
- Azure: isi **Environment variables** pada Web App: `ADMIN_PASSWORD`, dan untuk MySQL `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- Tabel dibuat otomatis saat halaman pertama dibuka.
