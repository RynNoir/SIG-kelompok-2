<?php
require_once 'koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM sekolah WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit;
}

$sekolah = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sekolah['nama']; ?> - Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />
    <style>
        #map { height: 400px; width: 100%; }
        .foto-sekolah { width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <span class="text-white">Detail Sekolah</span>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <!-- FOTO SEKOLAH -->
                        <?php if (!empty($sekolah['foto']) && file_exists('uploads/sekolah/' . $sekolah['foto'])): ?>
                            <img src="uploads/sekolah/<?php echo $sekolah['foto']; ?>" 
                                 class="foto-sekolah mb-4" alt="<?php echo $sekolah['nama']; ?>">
                        <?php else: ?>
                            <div class="alert alert-secondary text-center mb-4">
                                <i class="bi bi-image fs-1"></i>
                                <p class="mb-0">Foto belum tersedia</p>
                            </div>
                        <?php endif; ?>
                        
                        <h2 class="mb-3"><?php echo $sekolah['nama']; ?></h2>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>NPSN:</strong> <?php echo $sekolah['npsn']; ?></p>
                                <p><strong>Alamat:</strong><br><?php echo $sekolah['alamat']; ?></p>
                                <p><strong>Kecamatan:</strong> <?php echo $sekolah['kecamatan']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Akreditasi:</strong> 
                                    <span class="badge bg-primary fs-6"><?php echo $sekolah['akreditasi']; ?></span>
                                </p>
                                <p><strong>Kuota Siswa:</strong> <?php echo $sekolah['kuota']; ?> siswa</p>
                                <p><strong>Telepon:</strong> <?php echo $sekolah['telepon']; ?></p>
                                <p><strong>Radius Zonasi:</strong> <?php echo $sekolah['radius']/1000; ?> km</p>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">Lokasi di Peta</h5>
                        <div id="map" class="mb-3"></div>

                        <a href="index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Info Zonasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Sistem Zonasi</strong>
                            <p class="small mb-0 mt-2">
                                Calon siswa yang bertempat tinggal dalam radius 
                                <strong><?php echo $sekolah['radius']/1000; ?> km</strong> 
                                dari sekolah ini termasuk dalam zona prioritas.
                            </p>
                        </div>

                        <h6>Data Sekolah:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-award text-primary"></i> 
                                Akreditasi <strong><?php echo $sekolah['akreditasi']; ?></strong>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-people text-primary"></i> 
                                Kuota <strong><?php echo $sekolah['kuota']; ?></strong> siswa
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-geo-alt text-primary"></i> 
                                Zona radius <strong><?php echo $sekolah['radius']/1000; ?> km</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        const lat = <?php echo $sekolah['latitude']; ?>;
        const lng = <?php echo $sekolah['longitude']; ?>;
        const radius = <?php echo $sekolah['radius']; ?>;

        const map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        L.circle([lat, lng], {
            color: '#0d6efd',
            fillColor: '#0d6efd',
            fillOpacity: 0.2,
            radius: radius
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup('<b><?php echo $sekolah['nama']; ?></b>').openPopup();
    </script>
</body>
</html>