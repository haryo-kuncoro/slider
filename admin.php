<?php
session_start();
if(isset($_SESSION['flash_message'])) {
    echo "<script>alert('".$_SESSION['flash_message']."');</script>";
    unset($_SESSION['flash_message']);
}

include("connection.php");

// Ambil daftar prodi unik untuk tombol group
$prodiResult = mysqli_query($koneksi, "SELECT DISTINCT prodi FROM tbl_wisudawan ORDER BY prodi ASC");

// Cek apakah tbl_prodi_urutan kosong
$checkUrutan = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbl_prodi_urutan");
$rowCheck = mysqli_fetch_assoc($checkUrutan);

if ($rowCheck['total'] == 0) {
    // Jika kosong → generate default urutan dari $prodiResult
    $urut = 1;
    while($p = mysqli_fetch_assoc($prodiResult)) {
        $prodiName = $p['prodi'];

        mysqli_query($koneksi, "INSERT INTO tbl_prodi_urutan (nama_prodi, urutan) 
                                VALUES ('".mysqli_real_escape_string($koneksi,$prodiName)."', $urut)");
        $urut++;
    }
}

if(isset($_POST['reset_prodi_urutan'])) {
    session_start();

    // Ambil daftar prodi unik dari tbl_wisudawan
    $prodiResult = mysqli_query($koneksi, "SELECT DISTINCT prodi FROM tbl_wisudawan ORDER BY prodi ASC");
    $currentProdi = [];
    while($p = mysqli_fetch_assoc($prodiResult)) {
        $currentProdi[] = $p['prodi'];
    }

    // Ambil semua prodi di tbl_prodi_urutan
    $existing = [];
    $res = mysqli_query($koneksi, "SELECT nama_prodi FROM tbl_prodi_urutan");
    while($row = mysqli_fetch_assoc($res)) {
        $existing[] = $row['nama_prodi'];
    }

    // Hapus prodi yang tidak ada di tbl_wisudawan
    $toDelete = array_diff($existing, $currentProdi);
    if(!empty($toDelete)) {
        $deleteList = implode("','", array_map(function($v){ return mysqli_real_escape_string($GLOBALS['koneksi'], $v); }, $toDelete));
        mysqli_query($koneksi, "DELETE FROM tbl_prodi_urutan WHERE nama_prodi IN ('$deleteList')");
    }

    // Tambahkan prodi baru yang belum ada
    $urut = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(MAX(urutan),0)+1 AS nextUrutan FROM tbl_prodi_urutan"))['nextUrutan'];
    foreach($currentProdi as $prodiName) {
        if(!in_array($prodiName, $existing)) {
            mysqli_query($koneksi, "INSERT INTO tbl_prodi_urutan (nama_prodi, urutan) 
                                    VALUES ('".mysqli_real_escape_string($koneksi,$prodiName)."', $urut)");
            $urut++;
        }
    }

    $_SESSION['flash_message'] = "Urutan prodi berhasil di-reset.";

    header("Location: admin.php" . ($selectedProdi ? "?prodi=" . urlencode($selectedProdi) : ""));
    exit;
}


// Filter berdasarkan prodi (jika dipilih)
$selectedProdi = isset($_GET['prodi']) ? $_GET['prodi'] : '';

// Konfigurasi pagination
$limit = 30;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query dasar
$where = $selectedProdi ? "WHERE prodi='$selectedProdi'" : "";

// Proses insert/update
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $urutan = $_POST['urutan'];
    $nirm = $_POST['nirm'];
    $nama = $_POST['nama'];
    $ortu_laki = $_POST['ortu_laki'];
    $ortu_perempuan = $_POST['ortu_perempuan'];
    $tmp_tgl_lahir = $_POST['tmp_tgl_lahir'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $alamat = $_POST['alamat'];
    $ipk = $_POST['ipk'];
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];
    $prodi = $_POST['prodi'];
    $gelombang = $_POST['gelombang'];

    if ($id == "") {
        $sql = "INSERT INTO tbl_wisudawan 
        (urutan, nirm, nama, ortu_laki, ortu_perempuan, tmp_tgl_lahir, asal_sekolah, alamat, ipk, judul, keterangan, prodi, gelombang)
        VALUES 
        ('$urutan','$nirm','$nama','$ortu_laki','$ortu_perempuan','$tmp_tgl_lahir','$asal_sekolah','$alamat','$ipk','$judul','$keterangan','$prodi','$gelombang')";
    } else {
        $sql = "UPDATE tbl_wisudawan SET
        urutan='$urutan', nirm='$nirm', nama='$nama', ortu_laki='$ortu_laki', ortu_perempuan='$ortu_perempuan', tmp_tgl_lahir='$tmp_tgl_lahir',
        asal_sekolah='$asal_sekolah', alamat='$alamat', ipk='$ipk', judul='$judul', keterangan='$keterangan', prodi='$prodi', gelombang='$gelombang'
        WHERE id='$id'";
    }

    mysqli_query($koneksi, $sql);
    header("Location: admin.php?prodi=" . urlencode($prodi));
    exit;
}

// ===================== IMPORT EXCEL =====================

if (isset($_POST['import_excel'])) {

    if (!is_dir(__DIR__ . '/vendor')) {

        echo "<script>
            alert('Folder vendor tidak ditemukan!\\nSilakan jalankan: composer install');
            window.location.href = 'admin.php';
        </script>";

        exit;
    }

    require __DIR__ . '/vendor/autoload.php';

    if (
        !isset($_FILES['file_excel']['name']) ||
        $_FILES['file_excel']['name'] == ""
    ) {

        $_SESSION['flash_message'] =
            "Import gagal: File Excel belum dipilih.";

        header("Location: admin.php");
        exit;
    }


    $file_tmp =
        $_FILES['file_excel']['tmp_name'];


    try {

        // =================================================
        // LOAD EXCEL
        // =================================================

        $reader =
            new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();


        // Hanya membaca data
        $reader->setReadDataOnly(true);


        $spreadsheet =
            $reader->load($file_tmp);


        // =================================================
        // AMBIL SHEET PERTAMA
        // =================================================

        $sheet =
            $spreadsheet
                ->getSheet(0)
                ->toArray(
                    null,
                    true,
                    true,
                    true
                );

        $rowStart = 2;

        $inserted = 0;
        $skipped  = 0;

        // =================================================
        // PREPARED STATEMENT
        // =================================================

        $stmt = mysqli_prepare(
            $koneksi,
            "
            INSERT INTO tbl_wisudawan
            (
                urutan,
                nirm,
                nama,
                ortu_laki,
                ortu_perempuan,
                tmp_tgl_lahir,
                asal_sekolah,
                alamat,
                ipk,
                judul,
                keterangan,
                prodi,
                gelombang
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            "
        );


        if (!$stmt) {

            throw new Exception(
                "Gagal membuat prepared statement: " .
                mysqli_error($koneksi)
            );
        }


        foreach ($sheet as $i => $row) {

        // ---------------------------------------------
        // Lewati header
        // ---------------------------------------------

        if ($i < $rowStart) {
            continue;
        }


        $urutan =
            trim((string)($row['A'] ?? ''));

        $nirm =
            trim((string)($row['B'] ?? ''));

        $nama =
            trim((string)($row['C'] ?? ''));

        $ortu_laki =
            trim((string)($row['D'] ?? ''));

        $ortu_perempuan =
            trim((string)($row['E'] ?? ''));

        $tmp_tgl_lahir =
            trim((string)($row['F'] ?? ''));

        $asal_sekolah =
            trim((string)($row['G'] ?? ''));

        $alamat =
            trim((string)($row['H'] ?? ''));

        $ipk =
            trim((string)($row['I'] ?? ''));

        $judul =
            trim((string)($row['J'] ?? ''));

        $keterangan =
            trim((string)($row['K'] ?? ''));

        $prodi =
            trim((string)($row['L'] ?? ''));

        $gelombang =
            trim((string)($row['M'] ?? ''));


        if ($nirm === '') {
            $skipped++;
            continue;
        }

        if ($urutan === '') {
            $urutan = null;
        }

        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssssss",
            $urutan,
            $nirm,
            $nama,
            $ortu_laki,
            $ortu_perempuan,
            $tmp_tgl_lahir,
            $asal_sekolah,
            $alamat,
            $ipk,
            $judul,
            $keterangan,
            $prodi,
            $gelombang
        );


        // =================================================
        // EXECUTE
        // =================================================

        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Gagal import baris $i " .
                "(NIRM: $nirm): " .
                mysqli_stmt_error($stmt)
            );
        }


        $inserted++;
    }

        mysqli_stmt_close($stmt);

        $_SESSION['flash_message'] =
            "Import berhasil! " .
            $inserted .
            " data ditambahkan.";


        if ($skipped > 0) {

            $_SESSION['flash_message'] .=
                " " .
                $skipped .
                " baris kosong/tidak lengkap dilewati.";
        }


        header("Location: admin.php");
        exit;


    } catch (Exception $e) {

        $_SESSION['flash_message'] =
            "Import gagal: " .
            $e->getMessage();


        header("Location: admin.php");
        exit;
    }
}

// Proses delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM tbl_wisudawan WHERE id='$id'");
    header("Location: admin.php" . ($selectedProdi ? "?prodi=" . urlencode($selectedProdi) : ""));
    exit;
}

// Ambil input JSON dari SortableJS
if(file_get_contents('php://input')){
    $data = json_decode(file_get_contents('php://input'), true);
    if(isset($data['update_order'])){
        foreach($data['update_order'] as $o){
            $id = intval($o['id']);
            $urutan = intval($o['urutan']);
            mysqli_query($koneksi, "UPDATE tbl_prodi_urutan SET urutan=$urutan WHERE id=$id");
        }
        echo "success";
        exit;
    }
}

// Hitung total data sesuai filter
$totalRows = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM tbl_wisudawan $where"));
$totalPages = ceil($totalRows / $limit);

// Ambil data dengan limit sesuai filter
$result = mysqli_query($koneksi, "SELECT * FROM tbl_wisudawan $where ORDER BY prodi ASC, urutan ASC, nama ASC LIMIT $offset, $limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Wisudawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4 text-center">🎓 Manajemen Data Wisudawan</h2>

    <!-- Tombol Group Berdasarkan Prodi -->
    <div class="mb-3">
        <a href="admin.php" class="btn btn-secondary btn-sm <?= $selectedProdi=='' ? 'active' : '' ?>">Semua Prodi</a>
        <?php while($p = mysqli_fetch_assoc($prodiResult)): ?>
            <a href="?prodi=<?= urlencode($p['prodi']) ?>" 
               class="btn btn-outline-primary btn-sm <?= $selectedProdi==$p['prodi'] ? 'active' : '' ?>">
               <?= htmlspecialchars($p['prodi']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Info Prodi -->
    <?php
    $prodiLabel = ($selectedProdi && trim($selectedProdi) !== '') ? htmlspecialchars($selectedProdi) : 'Semua Prodi';
    $link_slider = "index.php" .(($selectedProdi && trim($selectedProdi) !== '') ? "?prodi=" . htmlspecialchars($selectedProdi) : '');
    $link_buku = "buku-js.html" .(($selectedProdi && trim($selectedProdi) !== '') ? "?prodi=" . htmlspecialchars($selectedProdi) : '');
    ?>
    <h5 class="mb-3">Menampilkan Prodi: <span class="text-primary"><?= $prodiLabel ?></span></h5>


    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#formModal" onclick="clearForm()"><i class="bi bi-plus"></i> Tambah Data</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="bi bi-file-earmark-excel"></i> Import Excel
            </button>
        </div>
        <div>
            <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#modalFotoCheck"><i class="bi bi-image"></i> Cek Foto</button>
            <a href="logo.php" class="btn btn-info me-2">🛠️ Pengaturan Slider</a>
            <a href="<?= $link_slider ?>" class="btn btn-info me-2" target="_blank">🎬 Play Slider</a>
            <a href="<?= $link_buku ?>" class="btn btn-success" target="_blank">📘 Cetak Buku</a>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>No</th>
                    <th>Urutan</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Prodi</th>
                    <th>IPK</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $no = $offset + 1;
            if (mysqli_num_rows($result) == 0): ?>
                <tr><td colspan="8" class="text-center text-muted">Tidak ada data</td></tr>
            <?php else:
                while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class=" text-center"><?= $no++ ?></td>
                    <td class=" text-center"><?= htmlspecialchars($row['urutan'] ?? '') ?></td>
                    <td class=" text-center"><?= htmlspecialchars($row['nirm'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['nama'] ?? '') ?></td>
                    <td class=" text-center"><?= htmlspecialchars($row['prodi'] ?? '') ?></td>
                    <td class=" text-center"><?= htmlspecialchars($row['ipk'] ?? '') ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#formModal"
                            onclick='editData(<?= json_encode($row) ?>)'><i class="bi bi-pencil-square"></i> Edit</button>
                        <a href="?delete=<?= $row['id'] ?>&prodi=<?= urlencode($selectedProdi) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')"><i class="bi bi-trash"></i> Hapus</a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php for($i=1; $i<=$totalPages; $i++): ?>
            <li class="page-item <?= ($i==$page)?'active':'' ?>">
                <a class="page-link" href="?page=<?= $i ?>&prodi=<?= urlencode($selectedProdi) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <div class="card mb-5 mt-3">
        <div class="card-header bg-success text-white d-flex flex-row align-items-center justify-content-between">Pengaturan Urutan Prodi (Drag & Drop)
            <div class="d-inline ml-auto float-right">
                <form method="post" style="display:inline;">
                    <button type="submit" name="reset_prodi_urutan" class="btn btn-warning btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Reset Urutan Prodi
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div id="prodi-container" class="d-flex flex-wrap gap-2">
              <?php
              $q = mysqli_query($koneksi, "SELECT * FROM tbl_prodi_urutan ORDER BY urutan ASC");
              while ($r = mysqli_fetch_assoc($q)) {
                  echo "<div class='badge bg-info text-white p-3 rounded draggable-item' data-id='{$r['id']}' style='cursor:grab;'>{$r['nama_prodi']}</div>";
              }
              ?>
          </div>
          <button id="save-order" class="btn btn-primary mt-3"><i class="bi bi-floppy"></i> Simpan Urutan</button>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah / Edit Wisudawan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="id">
        <div class="col-md-3">
          <label>Urutan</label>
          <input type="text" name="urutan" id="urutan" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>NIM</label>
          <input type="text" name="nirm" id="nirm" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Nama</label>
          <input type="text" name="nama" id="nama" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Ortu Laki</label>
          <input type="text" name="ortu_laki" id="ortu_laki" class="form-control">
        </div>
        <div class="col-md-6">
          <label>Ortu Perempuan</label>
          <input type="text" name="ortu_perempuan" id="ortu_perempuan" class="form-control">
        </div>
        <div class="col-md-6">
          <label>Tempat / Tgl Lahir</label>
          <input type="text" name="tmp_tgl_lahir" id="tmp_tgl_lahir" class="form-control">
        </div>
        <div class="col-md-6">
          <label>Asal Sekolah</label>
          <input type="text" name="asal_sekolah" id="asal_sekolah" class="form-control">
        </div>
        <div class="col-md-6">
            <label>Alamat</label>
            <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-md-6">
          <label>Prodi</label>
          <input type="text" name="prodi" id="prodi" class="form-control" required>
        </div>
        <div class="col-md-12">
          <label>Judul</label>
          <textarea type="text" name="judul" id="judul" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-md-4">
          <label>IPK</label>
          <input type="text" name="ipk" id="ipk" class="form-control">
        </div>
        <div class="col-md-8">
          <label>Keterangan Cumlaude</label>
          <input type="text" name="keterangan" id="keterangan" class="form-control">
        </div>
        
        <div class="col-md-4">
          <label>Gelombang</label>
          <input type="text" name="gelombang" id="gelombang" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="save" class="btn btn-primary"><i class="bi bi-floppy"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Data Wisudawan dari Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <label>Upload File Excel (.xlsx)</label>
        <input type="file" name="file_excel" class="form-control" accept=".xlsx" required>

        <small class="text-muted">
          Pastikan format kolom: urutan, nirm, nama, ortu_laki, ortu_perempuan, tmp_tgl_lahir, asal_sekolah, alamat, ipk, judul, keterangan, prodi, gelombang
        </small>
      </div>

      <div class="modal-footer">
        <a href="sample_wisudawan.php" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel"></i> Download Sample Excel
        </a>

        <button type="submit" name="import_excel" class="btn btn-sm btn-primary">
          <i class="bi bi-upload"></i> Import
        </button>
      </div>

    </form>
  </div>
</div>

<!-- Modal Cek Foto Wisudawan -->
<div class="modal fade" id="modalFotoCheck" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">📷 Cek Ketersediaan Foto Wisudawan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <label class="mb-2">Foto harus berada di dalam folder "photo/[tahun sekarang]/", contoh : "photo/2025/"</label>

        <table class="table table-bordered table-striped">
          <thead class="table-dark text-center">
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Nama</th>
              <th>Prodi</th>
              <th>Foto</th>
            </tr>
          </thead>
          <tbody>
          <?php
          // Ambil ulang data sesuai prodi agar modal ini tidak terganggu pagination
          $fotoQuery = mysqli_query($koneksi, "SELECT * FROM tbl_wisudawan $where ORDER BY prodi ASC, urutan ASC");

          $n = 1;
          while ($w = mysqli_fetch_assoc($fotoQuery)) {
              // Lokasi file foto
              $year = date("Y");
              $fotoPath = "photo/$year/" . $w['nirm'] . ".jpg";  

              // Cek file
              $exists = file_exists($fotoPath);

              echo "<tr>
                  <td class='text-center'>{$n}</td>
                  <td class='text-center'>{$w['nirm']}</td>
                  <td>{$w['nama']}</td>
                  <td class='text-center'>{$w['prodi']}</td>
                  <td class='text-center'>";

              if ($exists) {
                  echo "<span class='badge bg-success'>✔ Ada</span>";
              } else {
                  echo "<span class='badge bg-danger'>❌ Tidak Ada</span>";
              }

              echo "</td></tr>";

              $n++;
          }
          ?>
          </tbody>
        </table>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editData(data) {
    document.getElementById("id").value = data.id;
    for (let key in data) {
        if (document.getElementById(key)) {
            document.getElementById(key).value = data[key];
        }
    }
}
function clearForm() {
    document.querySelectorAll('#formModal input').forEach(i => i.value = '');
    document.querySelectorAll('#formModal textarea').forEach(i => i.value = '');
}

var container = document.getElementById('prodi-container');
var sortable = Sortable.create(container, {
    animation: 200,
    ghostClass: 'bg-secondary',  // efek saat drag
});

document.getElementById('save-order').addEventListener('click', function(){
    var urutan = [];
    container.querySelectorAll('.draggable-item').forEach(function(el, index){
        urutan.push({id: el.dataset.id, urutan: index+1});
    });

    fetch('admin.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({update_order: urutan})
    }).then(res => res.text())
      .then(data => { alert('Urutan berhasil disimpan!'); location.reload(); });
});
</script>

</body>
</html>
