<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npsn = mysqli_real_escape_string($conn, $_POST['npsn']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kecamatan = $_POST['kecamatan'];
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $radius = intval($_POST['radius']);
    $akreditasi = $_POST['akreditasi'];
    $kuota = intval($_POST['kuota']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    
    // UPLOAD FOTO
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $filetype = $_FILES['foto']['type'];
        $filesize = $_FILES['foto']['size'];
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = 'Format foto harus JPG, PNG, atau GIF!';
        } elseif ($filesize > 5 * 1024 * 1024) { // Max 5MB
            $error = 'Ukuran foto maksimal 5MB!';
        } else {
            $newname = uniqid() . '_' . time() . '.' . $ext;
            $upload_path = '../uploads/sekolah/' . $newname;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto = $newname;
            } else {
                $error = 'Gagal upload foto!';
            }
        }
    }
    
    if (empty($error)) {
        $query = "INSERT INTO sekolah (npsn, nama, alamat, kecamatan, latitude, longitude, radius, akreditasi, kuota, telepon, foto) 
                  VALUES ('$npsn', '$nama', '$alamat', '$kecamatan', $latitude, $longitude, $radius, '$akreditasi', $kuota, '$telepon', '$foto')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Sekolah berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />
    <style>
        #map { height: 400px; }
        .preview-foto { max-width: 300px; max-height: 200px; margin-top: 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a href="data-sekolah.php" class="navbar-brand">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4">Tambah Sekolah Baru</h2>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">Data Sekolah</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>NPSN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="npsn" required>
                            </div>
                            <div class="mb-3">
                                <label>Nama Sekolah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" required>
                            </div>
                            <div class="mb-3">
                                <label>Alamat <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="alamat" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Kecamatan <span class="text-danger">*</span></label>
                                <select class="form-select" name="kecamatan" required>
                                    <option value="">Pilih...</option>
                                    <option value="Lubuk Begalung">Lubuk Begalung</option>
                                    <option value="Kuranji">Kuranji</option>
                                    <option value="Padang Barat">Padang Barat</option>
                                    <option value="Pauh">Pauh</option>
                                    <option value="Lubuk Kilangan">Lubuk Kilangan</option>
                                </select>
                            </div>
                            
                            <!-- UPLOAD FOTO -->
                            <div class="mb-3">
                                <label>Foto Sekolah</label>
                                <input type="file" class="form-control" name="foto" accept="image/*" id="fotoInput" onchange="previewFoto(this)">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                                <img id="preview" class="preview-foto" style="display:none;">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Akreditasi</label>
                                    <select class="form-select" name="akreditasi">
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Kuota Siswa</label>
                                    <input type="number" class="form-control" name="kuota" value="256">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Telepon</label>
                                <input type="text" class="form-control" name="telepon">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">Lokasi di Peta</div>
                        <div class="card-body">
                            <div id="map" class="mb-3"></div>
                            <small class="text-muted">Klik pada peta untuk set lokasi</small>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label>Latitude</label>
                                    <input type="number" step="0.000001" class="form-control" 
                                           id="latitude" name="latitude" value="-0.9371" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Longitude</label>
                                    <input type="number" step="0.000001" class="form-control" 
                                           id="longitude" name="longitude" value="100.3600" required>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label>Radius Zonasi (meter)</label>
                                <input type="number" class="form-control" name="radius" value="2000">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-save"></i> Simpan Sekolah
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Preview foto
        function previewFoto(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('preview').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Map
        const map = L.map('map').setView([-0.9371, 100.3600], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        let marker = L.marker([-0.9371, 100.3600], {draggable: true}).addTo(map);
        
        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat.toFixed(6);
            document.getElementById('longitude').value = pos.lng.toFixed(6);
        });
        
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
        });
    </script>
</body>
</html>