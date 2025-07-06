# Sistem Pengumpulan Tugas

**Deskripsi**

Sistem Pengumpulan Tugas adalah aplikasi web untuk memanajemen praktikum, menghubungkan mahasiswa dan asisten dalam satu platform terpadu. Mahasiswa dapat mendaftar praktikum, mengunduh materi, dan mengumpulkan laporan. Asisten dapat mengelola mata praktikum, modul, serta menilai laporan.

---

## Fitur Utama

### Mahasiswa

* **Pendaftaran Praktikum**: Melihat daftar praktikum dan mendaftar.
* **Praktikum Saya**: Melihat praktikum yang diikuti.
* **Detail Praktikum**: Mengunduh materi modul.
* **Upload Laporan**: Mengumpulkan tugas/laporan.
* **Lihat Nilai & Feedback**: Memantau hasil penilaian asisten.

### Asisten

* **Manajemen Mata Praktikum**: CRUD pada praktikum.
* **Manajemen Modul**: CRUD modul beserta upload materi.
* **Manajemen Pengguna**: CRUD pengguna (mahasiswa & asisten).
* **Laporan Masuk**: Filter & lihat laporan mahasiswa.
* **Penilaian**: Memberi nilai dan feedback pada laporan.
* **Dashboard**: Statistik dan aktivitas laporan terbaru.

---

## Teknologi

* **PHP** (Native)
* **MySQL / MariaDB**
* **Tailwind CSS  (utility-first CSS framework untuk styling) **
* **JavaScript (untuk dynamic behavior seperti modal, AJAX requests) **

---

## Struktur Proyek

```
SistemPengumpulanTugas/
├── asisten/                    # Panel Asisten
│   ├── dashboard.php
│   ├── manajemen_course.php
│   ├── manajemen_modul.php
│   ├── manajemen_user.php
│   ├── submitted_reports.php
│   ├── report_penilaian.php
│   └── get_module_ajax.php
├── mahasiswa/                  # Panel Mahasiswa
│   ├── dashboard.php
│   ├── courses.php
│   ├── my_courses.php
│   └── course_detail.php
├── uploads/                    # Folder Unggahan
│   ├── materi/                 # Materi modul
│   └── laporan/                # Laporan mahasiswa
├── config.php                  # Konfigurasi koneksi DB
├── database.sql                # Skrip pembuatan dan sample data DB
├── index.php                   # Halaman awal / login redirect
├── register.php                # Registrasi mahasiswa
├── logout.php                  # Logout
└── README.md                   # Dokumentasi proyek
```

---

## Instalasi

1. **Clone repository**

   ```bash
   git clone https://github.com/<username>/SistemPengumpulanTugas.git
   cd SistemPengumpulanTugas
   ```
2. **Setup Database**

   * Buat database `pengumpulantugas` di MySQL/MariaDB.
   * Import `database.sql`:

     ```bash
     mysql -u root -p pengumpulantugas < database.sql
     ```
3. **Konfigurasi**

   * Edit `config.php` dengan kredensial database.
   * Pastikan `uploads/` folder memiliki permission write.
4. **Jalankan server**

   ```bash
   php -S localhost:8000
   ```
5. **Akses aplikasi** di `http://localhost:8000` dan login/daftar.

---

## Akun Default (Sample)

* **Asisten**

  * Email: `asisten1@test.com`
  * Password: `password123`
* **Mahasiswa**

  * Email: `mahasiswa1@test.com`
  * Password: `password123`

---

## Lisensi

MIT © 2025
