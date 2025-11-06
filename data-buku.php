<?php
require_once("connection.php");

$show_upload_no_photo = '0';
$total_image_perpage = 6;

// Ambil parameter prodi
$SET_PRODI = isset($_GET['prodi']) ? trim($_GET['prodi']) : "";
$array_urutan = [];

if (empty($SET_PRODI)) {
    $array_urutan = ["Hukum", "Manajemen", "Akuntansi"];
} else {
    // Jika ada nilai, jadikan array tunggal
    $array_urutan = [$SET_PRODI];
}
$data_final = [];

foreach ($array_urutan as $prodiforshow) {
    $tableprodi = "tbl_wisudawan";
    if ($tableprodi != "") {
        $q = "SELECT * FROM $tableprodi WHERE prodi='".$prodiforshow."' ORDER BY nama ASC";
        $res = mysqli_query($koneksi, $q);
        while ($r = mysqli_fetch_assoc($res)) {
            $NOURUT = strtoupper($r['urutan'] ?? '');
            $NIRM = strtoupper($r['nirm'] ?? '');
            $PRD = $r['prodi'] ?? '';
            $NAMA = $r['nama'] ?? '';
            $TMPTTL = $r['tmp_tgl_lahir'] ?? '';
            $ASAL = $r['asal_sekolah'] ?? '';
            $ALAMAT = $r['alamat'] ?? '';
            $NMAYAH = $r['ortu_laki'] ?? '';
            $NMIBU = $r['ortu_perempuan'] ?? '';
            $JUDUL = $r['judul'] ?? '';
            $GAMBAR = file_exists("photo/2025/$NIRM.jpg") ? "photo/2025/$NIRM.jpg" : "photo/alumni.jpg";

            $data_final[] = [
                "nourut" => $NOURUT,
                "nirm" => $NIRM,
                "nama" => $NAMA,
                "tmpttl" => $TMPTTL,
                "asalsekolah" => $ASAL,
                "alamat" => $ALAMAT,
                "ayah" => $NMAYAH,
                "ibu" => $NMIBU,
                "judul" => $JUDUL,
                "foto" => $GAMBAR,
                "prodi" => $PRD
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($data_final, JSON_UNESCAPED_UNICODE);
