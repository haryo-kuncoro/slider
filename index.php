<?php
    require_once __DIR__ . "/connection.php"; 
    require_once __DIR__ . '/config.php';

    $show_upload_no_photo = '0';
    $show_data_total = '1';
    
    // Pagination settings
    $limit = 10;
    $offset = isset($_GET['page']) ? ($_GET['page'] - 1) * $limit : 0;
    
    // Get Prodi for showing only by prodi
    $SET_PRODI = isset($_GET['prodi']) && $_GET['prodi'] != "" ? htmlentities($_GET['prodi']) : "";

    // Get gelombang and jump values from URL
    $SET_GELOMBANG = isset($_GET['g']) && $_GET['g'] != "" ? htmlentities($_GET['g']) : "";
    $SET_JUMP = isset($_GET['jump']) && $_GET['jump'] != "" ? htmlentities($_GET['jump']) : 0;
?>

<?php
$bg = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT file_name FROM tbl_slider_background WHERE status='active' ORDER BY id DESC LIMIT 1"
));

if ($bg) {
    $path = "img/background/" . $bg['file_name'];

    if (file_exists($path)) {
        $BACKGROUND = $path;
    } else {
        $BACKGROUND = "img/new/bg-default.png";
    }

} else {
    $BACKGROUND = "img/new/bg-default.png";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider Wisuda</title>

    <!-- Using CDN for CSS and JS Libraries -->
    <link rel="stylesheet" href="style/glide/glide.core.min.css">
    <link rel="stylesheet" href="style/glide/glide.theme.min.css">
    <link rel="stylesheet" href="style/new.css"> <!-- Local CSS -->
</head>
<body style="
    background-image: url('<?php echo $BACKGROUND; ?>');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
">

    <!-- Form to select Gelombang and Jump -->
    <div id="_shortcut" class="_shortcut">
        <div class="_shortcut_overlay">
            <form method="get">
                <p>Gelombang</p>
                <input type="text" name="g">
                <p>Jump</p>
                <input type="text" name="jump">
                <button type="submit">Go</button>
            </form>
        </div>
    </div>

    <!-- Carousel for Displaying Graduates -->
    <div class="glide hero">
        <div class="glide__track" data-glide-el="track">
            <div class="glide__slides">
                <?php
                    $i = $offset + 1;
                    if (empty($SET_PRODI)) {
                        $array_urutan = [];
                        $q = mysqli_query($koneksi, "SELECT nama_prodi FROM tbl_prodi_urutan ORDER BY urutan ASC");

                        while ($r = mysqli_fetch_assoc($q)) {
                            $array_urutan[] = $r['nama_prodi'];
                        }
                    } else {
                        $array_urutan = [$SET_PRODI];
                    }
                    
                    foreach($array_urutan as $prodiforshow) {
                        $tableprodi = "tbl_wisudawan";
                        $query_gelombang = $SET_GELOMBANG ? "AND gelombang = '".$SET_GELOMBANG."'" : "";

                        if ($tableprodi) {
                            $query = "SELECT nirm, nama, ortu_laki, ortu_perempuan, tmp_tgl_lahir, ipk, judul, keterangan, prodi 
                                      FROM $tableprodi 
                                      WHERE urutan IS NOT NULL 
                                      AND prodi = '$prodiforshow' 
                                      $query_gelombang 
                                      ORDER BY urutan ASC";

                            $execute = mysqli_query($koneksi, $query);
                            $totalmhs = 0;

                            if(mysqli_num_rows($execute) > 0) {
                                while ($row = mysqli_fetch_array($execute)) {
                                    $NIRM = !empty($row['nirm']) ? strtoupper($row['nirm']) : "";
                                    $NAMA = !empty($row['nama']) ? strtoupper($row['nama']) : "";
                                    $ORTULAKI = !empty($row['ortu_laki']) ? strtoupper($row['ortu_laki']) : "";
                                    $ORTUPEREMPUAN = !empty($row['ortu_perempuan']) ? strtoupper($row['ortu_perempuan']) : "";
                                    $TMPTTL = !empty($row['tmp_tgl_lahir']) ? strtoupper($row['tmp_tgl_lahir']) : "";
                                    $IPK = !empty($row['ipk']) ? number_format((float)str_replace(",", ".", $row['ipk']), 2) : "";
                                    $JUDUL = !empty($row['judul']) ? strtoupper($row['judul']) : "";
                                    $KETERANGAN = !empty($row['keterangan']) ? strtoupper($row['keterangan']) : "";
                                    $PRODI = !empty($row['prodi']) ? strtoupper($row['prodi']) : "";
                                    
                                    // Lazy load images, use thumbnail if available
                                    if (is_file($PATH_GAMBAR_WISUDAWAN . $NIRM . "_thumb.jpg")) {
                                        $GAMBAR = $PATH_GAMBAR_WISUDAWAN . $NIRM . "_thumb.jpg";

                                    } elseif (is_file($PATH_GAMBAR_WISUDAWAN . $NIRM . ".jpg")) {
                                        $GAMBAR = $PATH_GAMBAR_WISUDAWAN . $NIRM . ".jpg";
                                    } else {
                                        if ($show_upload_no_photo == '1') {
                                            echo "<script>console.log('" . $NAMA . " " . $PRODI . "');</script>";
                                        }
                                        // Foto default
                                        $GAMBAR = $FOTO_DEFAULT_WISUDAWAN;
                                    }
    
                                    // Render slide HTML
                                    $showIPK = true; // [true] atau [false], ubah untuk menampilkan ipk dan ket cumlaude                                    // Siapkan logo berdasarkan position
                                    $logoLeft = [];
                                    $logoCenter = [];
                                    $logoRight = [];

                                    $qLogo = mysqli_query(
                                        $koneksi,
                                        "SELECT file_name, position
                                         FROM tbl_slider_logo
                                         WHERE status='active'
                                         ORDER BY id ASC"
                                    );

                                    while ($lg = mysqli_fetch_assoc($qLogo)) {

                                        $path = "img/logo/" . $lg['file_name'];

                                        if (!file_exists($path)) {
                                            continue;
                                        }

                                        $position = strtolower(trim($lg['position'] ?? 'center'));

                                        if ($position === 'left') {
                                            $logoLeft[] = $path;
                                        } elseif ($position === 'right') {
                                            $logoRight[] = $path;
                                        } else {
                                            $logoCenter[] = $path;
                                        }
                                    }

                                    $hasLogo =
                                        count($logoLeft) > 0 ||
                                        count($logoCenter) > 0 ||
                                        count($logoRight) > 0;


                                    // Jika tidak ada logo aktif, gunakan logo default
                                    if (!$hasLogo) {
                                        $logoCenter[] = "img/new/diktisaintek.png";
                                    }


                                    // Render slide HTML
                                    echo "
                                    <div class='glide__slide' data-index='$i'>
                                        <div class='_container'>
                                            <div class='_column'>

                                                <div class='_row _logo'
                                                     style='
                                                         width:100%;
                                                         display:flex;
                                                         align-items:center;
                                                         justify-content:space-between;
                                                     '>

                                                    <!-- LOGO KIRI -->
                                                    <div class='logo-group logo-left'
                                                         style='
                                                             flex:1 1 0;
                                                             display:flex;
                                                             justify-content:flex-start;
                                                             align-items:center;
                                                             gap:10px;
                                                         '>";

                                    foreach ($logoLeft as $logo) {
                                        echo "
                                                        <img
                                                            src='$logo'
                                                            style='
                                                                height:70px;
                                                                width:auto;
                                                                object-fit:contain;
                                                                display:block;
                                                            '
                                                        />";
                                    }

                                    echo "
                                                    </div>

                                                    <!-- LOGO TENGAH -->
                                                    <div class='logo-group logo-center'
                                                         style='
                                                             flex:0 1 auto;
                                                             display:flex;
                                                             justify-content:center;
                                                             align-items:center;
                                                             gap:10px;
                                                         '>";

                                    foreach ($logoCenter as $logo) {
                                        echo "
                                                        <img
                                                            src='$logo'
                                                            style='
                                                                height:70px;
                                                                width:auto;
                                                                object-fit:contain;
                                                                display:block;
                                                            '
                                                        />";
                                    }

                                    echo "
                                                    </div>

                                                    <!-- LOGO KANAN -->
                                                    <div class='logo-group logo-right'
                                                         style='
                                                             flex:1 1 0;
                                                             display:flex;
                                                             justify-content:flex-end;
                                                             align-items:center;
                                                             gap:10px;
                                                         '>";

                                    foreach ($logoRight as $logo) {
                                        echo "
                                                        <img
                                                            src='$logo'
                                                            style='
                                                                height:70px;
                                                                width:auto;
                                                                object-fit:contain;
                                                                display:block;
                                                            '
                                                        />";
                                    }

                                    echo "
                                                    </div>

                                                </div>
                                            </div>
                                            <div class='_row'>
                                                <div class='_column_vh' style='margin-right: 30px'>
                                                    <div class='_selamat'><img src='./img/new/selamat.png' /></div>
                                                    
                                                    <div class='_nama'>
                                                        <p>Kepada :</p>
                                                        <h2>$NAMA</h2>
                                                    </div>
                                                    <div class='_column'>
                                                        <div class='_detail_npm'><h2>NPM. $NIRM</h2></div>
                                                    </div>
                                                    <div class='_row'>
                                                        <div class='_column'>
                                                            <div class='_detail'><p>Anak Dari :</p><h2>$ORTULAKI & $ORTUPEREMPUAN</h2></div>
                                                        </div>
                                                        <div>
                                                            <div class='_prodi'>
                                                                <p>Prodi :</p><h2>$PRODI</h2>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='_judul' style='border-right: 0px solid #eaf6ff;padding-right:15px;'>
                                                        <p>Judul Skripsi :</p><h3>$JUDUL</h3>
                                                    </div>
                                                </div>
    
                                                <div>
                                                    <div class='_column' style='text-align:right'>
                                                        <div class='_profile'>
                                                            <img class='lazyload' src='$GAMBAR'/>
                                                        </div>
                                                    </div>";

                                    if ($showIPK && !empty($IPK)) {
                                        echo "
                                            <div class='_column' style='text-align:right'>
                                                <div class='_ipk' style='text-align:center'>
                                                    <p>IPK (Indeks Prestasi Kumulatif)</p>
                                                    <h2>$IPK</h2>
                                                    <h3>~ $KETERANGAN ~</h3>
                                                </div>
                                            </div>";
                                    }

                                    echo "
                                                </div>
                                            </div>
                                        </div>
                                    </div>";
                                    
                                    $totalmhs++;
                                    $i++;
                                }
                            } else {
                                continue;
                                echo "
                                    <div class='glide__slide' data-index='$i'>
                                        <div class='_container'>
                                            <div class='_column'>
                                                <div class='_logo'><img src='./img/new/logo.png' /></div>
                                                <h1>$prodiforshow tidak ada data.</1>
                                            </div>
                                        </div>
                                    </div>";
                            }
                        }
                    }
                ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="js/glide/glide.min.js"></script>
    <script>
        // Initialize Glide carousel
        var glideHero = new Glide('.hero', {
            type: 'carousel',
            animationDuration: 1000,
            startAt: <?php echo $SET_JUMP; ?>,
            perView: 1,
            lazyLoad: true // Lazy load slides
        });
        glideHero.mount();

        // Keyboard shortcuts for displaying shortcut form
        document.addEventListener("keydown", function(e) {
            if (e.altKey && e.code === "KeyX") {
                document.getElementById("_shortcut").style.display = "block";
                e.preventDefault();
            } 
            if (e.code === 'Escape') {
                document.getElementById("_shortcut").style.display = "none";
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
