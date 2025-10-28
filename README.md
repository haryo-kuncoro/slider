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
✅ Disertai halaman ADMIN, untuk mengelola data dan sudah tersedia tombol Cetak Buku Wisuda serta Play Slider

---

## ⚙️ Masuk ke halaman **Admin**

http://localhost/slider/admin.php

---

## ⚙️ Cara Menggunakan **Slider Wisudawan**

1. Pastikan web server lokal aktif (XAMPP / Laragon / dsb)
2. Letakkan seluruh file di folder `www/slider/`
3. Pastikan folder `photo/` berisi foto-foto wisudawan (misal: `photo/2025/20210xxx.jpg`)
4. Pastikan file `db_slider.sql` sudah diimport ke dalam database
5. Anda dapat merubah data melalui phpmyadmin ada dbever mana yg lebih mudah

---

## ⚙️ Cara Menjalankan **Slider Wisudawan**

http://localhost/slider/index.php

---

## ⚙️ Cara Menggunakan **Generator Buku Wisuda**

1. Pastikan web server lokal aktif (XAMPP / Laragon / dsb)
2. Letakkan seluruh file di folder `www/slider/`
3. Pastikan folder `photo/` berisi foto-foto wisudawan (misal: `photo/2025/20210xxx.jpg`)
4. Pastikan file `data-buku.php` mengembalikan JSON seperti contoh berikut:

```json
[
  {
    "nourut": "1",
    "nirm": "2021020080",
    "nama": "Wisudawan 01",
    "tmpttl": "Medan / 25 Februari 1995",
    "asalsekolah": "",
    "alamat": "",
    "ayah": "Ayah 01",
    "ibu": "Ibu 01",
    "judul": "Sistem Pendukung Keputusan untuk Menentukan Pemberian Reward Pemasok Terbaik Pada PT. Midi Utama Indonesia Tbk Menggunakan Metode Moosra",
    "foto": "photo/2025/2021020080.jpg",
    "prodi": "Hukum"
  }
]
```
---

## ⚙️ Cara Menjalankan **Generator Buku Wisuda**

http://localhost/slider/buku-js.html