# 🎓 Slider Wisuda Berbasis Website (PHP + MySql + jsPDF)

Saya ingin berbagi **slider wisuda dan juga disertai generator buku wisuda otomatis** berbasis **PHP Native** dan **jsPDF (JavaScript)**  
yang menghasilkan file **PDF ukuran A5** berisi data wisudawan lengkap dengan foto, identitas, dan judul skripsi.

---

## ✨ Fitur Utama

✅ Slider Wisudawan Berbasis Website  
✅ Cek foto apakah sudah exist/tersedia atau belum    
✅ Menampilkan seluruh data lintas prodi (tanpa parameter)  
✅ Cek seluruh data foto wisudawan  
✅ Generate Buku Wisuda Otomatis **generate PDF ukuran A5** dengan layout rapi   
✅ Bisa diunduh langsung sebagai file `Buku_Wisuda_A5.pdf`   
✅ Disertai halaman **ADMIN**, untuk mengelola data dan sudah tersedia tombol **Cetak Buku Wisuda** untuk export dalam bentuk pdf, serta **Play Slider** untuk menampilkan slider berdasarkan prodi atau keseluruhan. Juga sudah dilengkapi dengan **Import Data Wisudawan by Excel** untuk mempermudah upload data wisudawan tanpa harus insert ke database langsung. Ada pengaturan **Urutan Prodi** juga (untuk mengurutkan prodi yg akan ditampilkan pada slider ataupun buku wisuda)   
✅ Disertai halaman **PENGATURAN SLIDER**, untuk mengelola logo dan background tanpa harus copy dan ubah code, tinggal upload dan logo tampil   

---

## ⚙️ Masuk ke halaman Admin

http://localhost/slider/admin.php

---

## ⚙️ Cara Menggunakan Slider Wisudawan

1. Pastikan web server lokal aktif (XAMPP / Laragon / dsb)
2. Letakkan seluruh file di folder `slider` pada host anda. `htdocs/slider/` jika XAMPP atau `www/slider/` jika anda menggunakan Laragon
3. Pastikan folder `photo/[tahun sekarang]` berisi foto-foto wisudawan (misal: `photo/2025/20210xxx.jpg`)
4. Pastikan file `db_slider.sql` sudah diimport ke dalam database
5. Anda dapat merubah data melalui phpmyadmin ada dbever mana yg lebih mudah


### Cara Menjalankan Slider Wisudawan

http://localhost/slider/index.php

---

## ⚙️ Cara Menggunakan Import Data Wisudawan by Excel

1. Pastikan composer phpoffice sudah diinstal, jika belum silahkan jalankan `composer install` pada terminal
2. Saat akan import, anda bisa doenload sample excel, dan anda tinggal meletakkan data wisudawan berdasarkan format kolom yg sudah ditetapkan
3. Selnajutnya upload file excel yg sudah disiapkan, tekan `Import`
