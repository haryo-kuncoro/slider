<?php
    require_once("connection.php"); // Assumes you have a connection.php file for database connection

    $show_upload_no_photo = '0';
    $show_data_total = '1';
    
    // Pagination settings
    $limit = 10;
    $offset = isset($_GET['page']) ? ($_GET['page'] - 1) * $limit : 0;
    
    // Get gelombang and jump values from URL
    $SET_GELOMBANG = isset($_GET['g']) && $_GET['g'] != "" ? htmlentities($_GET['g']) : "";
    $SET_JUMP = isset($_GET['jump']) && $_GET['jump'] != "" ? htmlentities($_GET['jump']) : 0;
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
<body>

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
                    $array_urutan = array("Hukum", "Manajemen", "Akuntansi");
                    
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
                                    $NIRM = strtoupper($row['nirm']);
                                    $NAMA = strtoupper($row['nama']);
                                    $ORTULAKI = strtoupper($row['ortu_laki']);
                                    $ORTUPEREMPUAN = strtoupper($row['ortu_perempuan']);
                                    $TMPTTL = strtoupper($row['tmp_tgl_lahir']);
                                    $IPK = number_format(str_replace(",", ".", $row['ipk']), 2);
                                    $JUDUL = strtoupper($row['judul']);
                                    $KETERANGAN = strtoupper($row['keterangan']);
                                    $PRODI = strtoupper($row['prodi']);
                                    
                                    // Lazy load images, use thumbnail if available
                                    if(is_file("photo/2025/".$NIRM."_thumb.jpg")){
                                        $GAMBAR = "photo/2025/".$NIRM."_thumb.jpg";
                                    }else{
                                        if(is_file("photo/2025/".$NIRM.".jpg")){
                                            $GAMBAR = "photo/2025/".$NIRM.".jpg";
                                        }else{
                                            if($show_upload_no_photo=='1'){
                                                echo "<script>console.log('".$NAMA." ".$PRODI."');</script>";
                                            }
                                            $GAMBAR = "photo/alumni.jpg";
                                        }
                                    }

                                    $propercase_string = ucwords(strtolower($KETERANGAN));
    
                                    // Render slide HTML
                                    echo "
                                    <div class='glide__slide' data-index='$i'>
                                        <div class='_container'>
                                            <div class='_row'>
                                                <div class='_column_vh' style='margin-right: 30px'>
                                                    <div class='_row _center_align _logo'>
                                                        <div class=''><img src='./img/new/logo-hukum.png' /></div>   
                                                        <div class=''><img src='./img/new/logo-manajemen.png' /></div>  
                                                        <div class=''><img src='./img/new/logo-yayasan.png' /></div> 
                                                    </div>
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
                                                    <div class='_row'>
                                                        <div class='_column'></div>
                                                        <div class='_kampus'><img src='./img/new/diktisaintek.png' /></div>
                                                    </div>
    
                                                    <div class='_column' style='text-align:center'>
                                                        <div class='_profile'>
                                                            <img class='lazyload' src='$GAMBAR'/>
                                                        </div>
                                                    </div>
    
                                                    <div class='_row _ipk_container'>
                                                        <div class='_column'>
                                                            <div class='_ipk' style='text-align:center'>
                                                                <p>IPK (Indeks Prestasi Kumulatif)</p>
                                                                <h2>$IPK</h2>
                                                            </div>
                                                        </div>
                                                    </div>
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
