<?php
include("connection.php");

// Ambil daftar prodi unik untuk tombol group
$prodiResult = mysqli_query($koneksi, "SELECT DISTINCT prodi FROM tbl_wisudawan ORDER BY prodi ASC");

// Filter berdasarkan prodi (jika dipilih)
$selectedProdi = isset($_GET['prodi']) ? $_GET['prodi'] : '';

// Konfigurasi pagination
$limit = 50;
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

// Proses delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM tbl_wisudawan WHERE id='$id'");
    header("Location: admin.php" . ($selectedProdi ? "?prodi=" . urlencode($selectedProdi) : ""));
    exit;
}

// Hitung total data sesuai filter
$totalRows = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM tbl_wisudawan $where"));
$totalPages = ceil($totalRows / $limit);

// Ambil data dengan limit sesuai filter
$result = mysqli_query($koneksi, "SELECT * FROM tbl_wisudawan $where ORDER BY prodi ASC, urutan ASC LIMIT $offset, $limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Wisudawan per Prodi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
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
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formModal" onclick="clearForm()">+ Tambah Data</button>
        </div>
        <div>
            <a href="<?= $link_slider ?>" class="btn btn-info me-2" target="_blank"><i class="bi bi-play-circle"></i>🎬 Play Slider</a>
            <a href="<?= $link_buku ?>" class="btn btn-success" target="_blank"><i class="bi bi-printer"></i>📘 Cetak Buku</a>
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
                    <td class=" text-center"><?= htmlspecialchars($row['urutan']) ?></td>
                    <td class=" text-center"><?= htmlspecialchars($row['nirm']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td class=" text-center"><?= htmlspecialchars($row['prodi']) ?></td>
                    <td class=" text-center"><?= htmlspecialchars($row['ipk']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#formModal"
                            onclick='editData(<?= json_encode($row) ?>)'>Edit</button>
                        <a href="?delete=<?= $row['id'] ?>&prodi=<?= urlencode($selectedProdi) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">Hapus</a>
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
        <button type="submit" name="save" class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
</script>

</body>
</html>
