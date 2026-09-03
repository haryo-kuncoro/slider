<?php
require_once __DIR__ . "/connection.php"; 
require_once __DIR__ . '/config.php';

$show_upload_no_photo = '0';
$show_data_total = '1';

// Tentukan direktori folder "ditemukan"
$folder_ditemukan = "ditemukan/";

// Cek apakah folder "ditemukan" ada, jika tidak, buat folder tersebut
if (!is_dir($folder_ditemukan)) {
    mkdir($folder_ditemukan, 0755, true); // Buat folder dengan izin 0755
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salin Foto Wisuda</title>

    <style type="text/css" media="print">
        @page { size: A5 portrait; }
        .pagebreak { page-break-before: always; }
    </style>

    <style>
        body { -webkit-print-color-adjust: exact !important; }
        p { text-align: justify; }
        .font_size_16 { font-size: 16; }
        .font_size_8pt { font-size: 8pt; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .v_top { vertical-align: top; }
        .margin-ul-tb-10 tr td ul { margin: 13px; }
        .watermark { position: fixed; top: 25%; left: 15%; display: block; z-index: -1; }
        .linespace { line-height: 1.6; }
    </style>
</head>
<body style="padding: 0px; margin: 0px;">

<?php
    $i = 1;
    $nomor = 1;

    // Tentukan array urutan prodi
    $array_urutan = array("Hukum", "Manajemen", "Akuntansi");

    $html_show = "";
    $html_show .= "<script>console.log('# Data ditampilkan termasuk yg tidak daftar wisuda #');</script>";
    $html_show .= "<script>console.log('====================================================');</script>";
    $html_show .= '<table style="margin-bottom:10px;" class="font_size_8pt" border="0px">';

    foreach ($array_urutan as $prodiforshow) {

        $tableprodi = "tbl_wisudawan";
        if ($tableprodi !== "") {
            $query = "SELECT * FROM " . $tableprodi . " WHERE prodi = '" . $prodiforshow . "' ORDER BY nama ASC";
            $execute = mysqli_query($koneksi, $query);
            $totalmhs = 0;

            while ($row = mysqli_fetch_array($execute)) {
                $NIRM = strtoupper(strtolower($row['nirm']));
                $NAMA = ucwords(strtolower($row['nama']));
                $NAMA_LOG = ucwords(strtolower(str_replace("'", " ", $row['nama'])));
                $GAMBAR = "";

                if (is_file("photo/2025/" . $NIRM . ".jpg")) {
                    $GAMBAR = $NIRM . ".jpg";

                    // Salin gambar ke folder "ditemukan"
                    $source_path = "photo/2025/" . $GAMBAR;
                    $destination_path = $folder_ditemukan . $GAMBAR;

                    if (!copy($source_path, $destination_path)) {
                        echo "<script>console.log('Gagal menyalin foto: $GAMBAR');</script>";
                    } else {
                        echo "<script>console.log('Berhasil menyalin foto: $GAMBAR');</script>";
                    }
                } else {
                    if ($show_upload_no_photo == '1') {
                        $html_show .= "<script>console.log('".$nomor."');</script>";
                        $html_show .= "<script>console.log('".$NAMA." ".$PRODI."');</script>";
                    }

                    $GAMBAR = "tidak ada";
                }

                if ($GAMBAR == "tidak ada") {
                    $html_show .= '<tr style="color:red">
                                    <td class="v_top">'.$nomor.'</td>
                                    <td class="v_top">'.$NIRM.'</td>
                                    <td class="v_top">'.$NAMA.'</td>
                                    <td class="v_top">'.$GAMBAR.'</td>
                                </tr>';
                } else {
                    $html_show .= '<tr>
                                    <td class="v_top">'.$nomor.'</td>
                                    <td class="v_top">'.$NIRM.'</td>
                                    <td class="v_top">'.$NAMA.'</td>
                                    <td class="v_top">'.$GAMBAR.'</td>
                                </tr>';
                }

                $nomor++;
            }
        }
    }

    $html_show .= '</table>';
    echo $html_show;
    ?>

</body>
</html>
