# 🎓 Buku Wisuda Generator (PHP + jsPDF)

Proyek ini adalah aplikasi **pembuatan Buku Wisuda otomatis** berbasis **PHP Native** dan **jsPDF (JavaScript)**  
yang menghasilkan file **PDF ukuran A5** berisi data wisudawan lengkap dengan foto, identitas, dan judul skripsi.

---

## ✨ Fitur Utama

✅ Mengambil data wisudawan dari **file PHP (`data-buku.php`)** berupa JSON  
✅ Menampilkan seluruh data lintas prodi (tanpa parameter)  
✅ Otomatis **generate PDF ukuran A5** dengan layout rapi  
✅ Foto wisudawan muncul di **sebelah kiri**, teks detail di kanan  
✅ Format detail sejajar vertikal dengan titik dua lurus (`:`)  
✅ Judul skripsi otomatis **wrap ke baris baru** jika panjang  
✅ Bisa diunduh langsung sebagai `Buku_Wisuda_A5.pdf`

---

## 📂 Struktur Folder

buku-wisuda/
├── buku_wisuda.html # Halaman utama untuk generate PDF
├── data-buku.php # Output JSON data wisudawan
├── photo/ # Folder foto wisudawan (2025/xxxx.jpg)
├── connection.php # Koneksi database (jika diperlukan)
└── README.md # Dokumentasi proyek (file ini)

---

## ⚙️ Cara Menggunakan

1. Pastikan web server lokal aktif (XAMPP / Laragon / dsb)
2. Letakkan seluruh file di folder `htdocs/buku-wisuda/`
3. Pastikan folder `photo/` berisi foto-foto wisudawan (misal: `photo/2025/2021020006.jpg`)
4. Pastikan file `data-buku.php` mengembalikan JSON seperti contoh berikut:

```json
[
  {
    "nirm": "2021020006",
    "nama": "Ananda Teddy Purba",
    "tmpttl": "Medan / 02 Januari 2001",
    "foto": "photo/2025/2021020006.jpg",
    "prodi": "Sistem Informasi",
    "asalsekolah": "SMAN 1 Medan",
    "ayah": "Bapak Teddy",
    "ibu": "Ibu Sari",
    "alamat": "Jl. Mawar No.10",
    "judul": "Analisis Sistem Informasi Akademik ..."
  }
]

---

## ⚙️ Cara Menjalankan

http://localhost/slider/buku-js.html