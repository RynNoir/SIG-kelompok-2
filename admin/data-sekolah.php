<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../koneksi.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$query = "SELECT * FROM sekolah";
if ($search) {
    $query .= " WHERE nama LIKE '%$search%' OR npsn LIKE '%$search%' OR kecamatan LIKE '%$search%'";
}
$query .= " ORDER BY nama";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a href="dashboard.php" class="navbar-brand">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Data Sekolah</h2>
            <a href="tambah-sekolah.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Sekolah
            </a>
        </div>

        <!-- Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cari nama, NPSN, atau kecamatan..." 
                               value="<?php echo $search; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NPSN</th>
                                <th>Nama Sekolah</th>
                                <th>Kecamatan</th>
                                <th>Akreditasi</th>
                                <th>Kuota</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['npsn']; ?></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo $row['kecamatan']; ?></td>
                                <td><span class="badge bg-primary"><?php echo $row['akreditasi']; ?></span></td>
                                <td><?php echo $row['kuota']; ?></td>
                                <td>
                                    <a href="edit-sekolah.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="hapus-sekolah.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Yakin ingin menghapus?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>