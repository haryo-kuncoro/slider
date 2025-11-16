<?php
require_once "connection.php";

/* ------------------------------
   ACTION HANDLER (UPLOAD / TOGGLE / DELETE)
--------------------------------*/

// UPLOAD LOGO
if (isset($_POST['upload_logo'])) {
    $dir = "img/logo/";
    $name = time() . "_" . basename($_FILES["logo_file"]["name"]);
    move_uploaded_file($_FILES["logo_file"]["tmp_name"], $dir . $name);

    mysqli_query($koneksi, "INSERT INTO tbl_slider_logo (file_name) VALUES ('$name')");
    header("Location: logo.php"); exit;
}

// UPLOAD BACKGROUND
if (isset($_POST['upload_bg'])) {
    $dir = "img/background/";
    $name = time() . "_" . basename($_FILES["bg_file"]["name"]);
    move_uploaded_file($_FILES["bg_file"]["tmp_name"], $dir . $name);

    mysqli_query($koneksi, "INSERT INTO tbl_slider_background (file_name) VALUES ('$name')");
    header("Location: logo.php"); exit;
}

// TOGGLE LOGO
if (isset($_GET['toggle_logo'])) {
    $id = $_GET['toggle_logo'];
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT status FROM tbl_slider_logo WHERE id=$id"));
    $new = ($r['status']=='active') ? 'inactive' : 'active';
    mysqli_query($koneksi, "UPDATE tbl_slider_logo SET status='$new' WHERE id=$id");
    header("Location: logo.php"); exit;
}

// DELETE LOGO
if (isset($_GET['del_logo'])) {
    $id = $_GET['del_logo'];
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT file_name FROM tbl_slider_logo WHERE id=$id"));
    unlink("img/logo/".$r['file_name']);
    mysqli_query($koneksi, "DELETE FROM tbl_slider_logo WHERE id=$id");
    header("Location: logo.php"); exit;
}

// TOGGLE BACKGROUND
if (isset($_GET['toggle_bg'])) {
    $id = $_GET['toggle_bg'];
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT status FROM tbl_slider_background WHERE id=$id"));
    $new = ($r['status']=='active') ? 'inactive' : 'active';
    mysqli_query($koneksi, "UPDATE tbl_slider_background SET status='$new' WHERE id=$id");
    header("Location: logo.php"); exit;
}

// DELETE BACKGROUND
if (isset($_GET['del_bg'])) {
    $id = $_GET['del_bg'];
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT file_name FROM tbl_slider_background WHERE id=$id"));
    unlink("img/background/".$r['file_name']);
    mysqli_query($koneksi, "DELETE FROM tbl_slider_background WHERE id=$id");
    header("Location: logo.php"); exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Slider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="text-center mb-4">🛠️ Pengaturan Logo & Background</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="admin.php" class="btn btn-info me-2"><i class="bi bi-play-circle"></i>🎓 Data Wisudawan</a>
        </div>
        <div>
            <a href="index.php" class="btn btn-info me-2" target="_blank"><i class="bi bi-play-circle"></i>🎬 Play Slider</a>
        </div>
    </div>

    <!-- ================= UPLOAD LOGO ================= -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Upload Logo Baru</div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="upload_logo" value="1">
                <div class="mb-3">
                    <label class="form-label">Pilih file logo</label>
                    <input type="file" name="logo_file" class="form-control" required>
                </div>
                <button class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>

    <!-- ================= LIST LOGO ================= -->
    <div class="card mb-5">
        <div class="card-header bg-dark text-white">Daftar Logo</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Preview</th><th>Status</th><th>Aksi</th>
                </tr>

                <?php
                $q = mysqli_query($koneksi, "SELECT * FROM tbl_slider_logo ORDER BY id ASC");
                while($r = mysqli_fetch_assoc($q)){
                    echo "
                    <tr>
                        <td><img src='img/logo/$r[file_name]' height='50'></td>
                        <td><span class='badge bg-".($r['status']=='active'?'success':'secondary')."'>$r[status]</span></td>
                        <td>
                            <a href='logo.php?toggle_logo=$r[id]' class='btn btn-warning btn-sm'>Toggle</a>
                            <a href='logo.php?del_logo=$r[id]' class='btn btn-danger btn-sm' onclick='return confirm(\"Hapus logo?\")'>Delete</a>
                        </td>
                    </tr>";
                }
                ?>

            </table>
        </div>
    </div>


    <!-- ================= UPLOAD BACKGROUND ================= -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">Upload Background Baru</div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="upload_bg" value="1">
                <div class="mb-3">
                    <label class="form-label">Pilih file background</label>
                    <input type="file" name="bg_file" class="form-control" required>
                </div>
                <button class="btn btn-success">Upload</button>
            </form>
        </div>
    </div>

    <!-- ================= LIST BACKGROUND ================= -->
    <div class="card">
        <div class="card-header bg-dark text-white">Daftar Background</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Preview</th><th>Status</th><th>Aksi</th>
                </tr>

                <?php
                $q = mysqli_query($koneksi, "SELECT * FROM tbl_slider_background ORDER BY id DESC");
                while($r = mysqli_fetch_assoc($q)){
                    echo "
                    <tr>
                        <td><img src='img/background/$r[file_name]' height='60'></td>
                        <td><span class='badge bg-".($r['status']=='active'?'success':'secondary')."'>$r[status]</span></td>
                        <td>
                            <a href='logo.php?toggle_bg=$r[id]' class='btn btn-warning btn-sm'>Toggle</a>
                            <a href='logo.php?del_bg=$r[id]' class='btn btn-danger btn-sm' onclick='return confirm(\"Hapus background?\")'>Delete</a>
                        </td>
                    </tr>";
                }
                ?>

            </table>
        </div>
    </div>

</div>

</body>
</html>
