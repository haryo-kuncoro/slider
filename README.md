# 🎓 Slider Wisuda Berbasis Website (PHP + MySql + jsPDF)

Saya ingin berbagi **slider wisuda dan juga disertai generator buku wisuda otomatis** berbasis **PHP Native** dan **jsPDF (JavaScript)**  
yang menghasilkan file **PDF ukuran A5** berisi data wisudawan lengkap dengan foto, identitas, dan judul skripsi.

---

## ✨ Fitur Utama

✅ Slider Wisudawan Berbasis Website  
✅ Cek foto apakah sudah exist/tersedia atau belum    
✅ Menampilkan seluruh data lintas prodi (tanpa parameter)  
✅ Generate Buku Wisuda Otomatis **generate PDF ukuran A5** dengan layout rapi   
✅ Bisa diunduh langsung sebagai file `Buku_Wisuda_A5.pdf`

---

## ⚙️ Cara Menggunakan Slider Wisudawan

1. Pastikan web server lokal aktif (XAMPP / Laragon / dsb)
2. Letakkan seluruh file di folder `htdocs/slider/`
3. Pastikan folder `photo/` berisi foto-foto wisudawan (misal: `photo/2025/20210xxx.jpg`)
4. Pastikan file `db_slider.sql` sudah diimport ke dalam database
5. Anda dapat merubah data melalui phpmyadmin ada dbever mana yg lebih mudah

---

## ⚙️ Cara Menjalankan Slider Wisudawan

http://localhost/slider/index.php

---

## ⚙️ Cara Menggunakan Generator Buku Wisuda

1. Pastikan web server lokal aktif (XAMPP / Laragon / dsb)
2. Letakkan seluruh file di folder `htdocs/slider/`
3. Pastikan folder `photo/` berisi foto-foto wisudawan (misal: `photo/2025/20210xxx.jpg`)
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
```
---

## ⚙️ Cara Menjalankan Generator Buku Wisuda

http://localhost/slider/buku-js.html