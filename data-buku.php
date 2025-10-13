<?php
require_once("connection.php");

$show_upload_no_photo = '0';
$total_image_perpage = 6;

// Ambil parameter prodi
$SET_PRODI = isset($_GET['p']) ? trim($_GET['p']) : "";
$array_urutan = [];

switch ($SET_PRODI) {
    case "hk": $array_urutan = ["Hukum"]; break;
    case "mj": $array_urutan = ["Manajemen"]; break;
    case "ak": $array_urutan = ["Akuntansi"]; break;
    default: $array_urutan = ["Hukum","Manajemen","Akuntansi"];
}
$data_final = [];

foreach ($array_urutan as $prodiforshow) {
    $tableprodi = "tbl_wisudawan";
    if ($tableprodi != "") {
        $q = "SELECT * FROM $tableprodi WHERE prodi='".$prodiforshow."' ORDER BY nama ASC";
        $res = mysqli_query($koneksi, $q);
        while ($r = mysqli_fetch_assoc($res)) {
            $NOURUT = strtoupper($r['urutan']);
            $NIRM = strtoupper($r['nirm']);
            $NAMA = $r['nama'];
            $TMPTTL = $r['tmp_tgl_lahir'];
            $ASAL = ucwords(strtolower($r['asal_sekolah']));
            $ALAMAT = ucwords(strtolower($r['alamat']));
            $NMAYAH = $r['ortu_laki'];
            $NMIBU = $r['ortu_perempuan'];
            $JUDUL = $r['judul'];
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
                "prodi" => $prodiforshow
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($data_final, JSON_UNESCAPED_UNICODE);
