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
* **Tailwind CSS  (utility-first CSS framework untuk styling)**
* **JavaScript (untuk dynamic behavior seperti modal, AJAX requests)**

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
## Screenshot
* **Dashboard - Assistant Panel**
  ![image](https://github.com/user-attachments/assets/d7bd5ed6-ec52-4935-a7b1-742823f1a999)

* **Manajemen Course - Assistant Panel**
  ![image](https://github.com/user-attachments/assets/e79132e0-032d-4356-9022-9dd2904aa93c)

* **Manajemen Modul - Assistant Panel**
  ![image](https://github.com/user-attachments/assets/76138774-7878-4bbf-ae78-46bba0a06740)

* **Report Penilaian - Assistant Panel**
  ![image](https://github.com/user-attachments/assets/23a9da6f-9f7c-4772-91df-5c3bf3382c88)

* **Manajemen User - Assistant Panel**
  ![image](https://github.com/user-attachments/assets/45e37413-ff9b-4922-83c0-d176266908bb)

* **Dashboard - Mahasiswa**
  ![image](https://github.com/user-attachments/assets/1d092483-f394-438f-be7d-a5f6533e2ba3)

* **Courses - Mahasiswa**
  ![image](https://github.com/user-attachments/assets/cea0bf5e-30ee-487c-b487-c755e609541a)
  
* **Courses detail - Mahasiswa**
  ![image](https://github.com/user-attachments/assets/280e85f4-091b-42d0-9cbb-270bcdb12dbc)

* **Enroll - Mahasiswa**
  ![image](https://github.com/user-attachments/assets/e48bf632-9e09-4bdc-affd-e5217ae74d93)

  















## Lisensi

MIT © 2025
