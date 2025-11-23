<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

// Load data sekolah
$query = "SELECT * FROM sekolah WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: data-sekolah.php');
    exit;
}

$sekolah = mysqli_fetch_assoc($result);

// Proses update
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
    $kepala_sekolah = mysqli_real_escape_string($conn, $_POST['kepala_sekolah']);
    $operator = mysqli_real_escape_string($conn, $_POST['operator']);
    $email_sekolah = mysqli_real_escape_string($conn, $_POST['email_sekolah']);
    
    $foto = $sekolah['foto']; // Keep old photo
    
    // UPLOAD FOTO BARU
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = 'Format foto harus JPG, PNG, atau GIF!';
        } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran foto maksimal 5MB!';
        } else {
            // Buat folder jika belum ada
            $upload_dir = '../uploads/sekolah/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $newname = uniqid() . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $newname;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada
                if (!empty($sekolah['foto']) && file_exists('../uploads/sekolah/' . $sekolah['foto'])) {
                    unlink('../uploads/sekolah/' . $sekolah['foto']);
                }
                $foto = $newname;
            } else {
                $error = 'Gagal upload foto!';
            }
        }
    }
    
    if (empty($error)) {
        $query = "UPDATE sekolah SET 
                  npsn = '$npsn',
                  nama = '$nama',
                  alamat = '$alamat',
                  kecamatan = '$kecamatan',
                  latitude = $latitude,
                  longitude = $longitude,
                  radius = $radius,
                  akreditasi = '$akreditasi',
                  kuota = $kuota,
                  telepon = '$telepon',
                  kepala_sekolah = '$kepala_sekolah',
                  operator = '$operator',
                  email_sekolah = '$email_sekolah',
                  foto = '$foto'
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Data sekolah berhasil diupdate!';
            // Reload data
            $result = mysqli_query($conn, "SELECT * FROM sekolah WHERE id = $id");
            $sekolah = mysqli_fetch_assoc($result);
        } else {
            $error = 'Gagal update: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />
    <style>
        #map { 
            height: 400px; 
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .foto-existing { 
            max-width: 300px; 
            max-height: 200px; 
            margin-bottom: 10px;
            border-radius: 8px;
        }
        .preview-foto { 
            max-width: 300px; 
            max-height: 200px; 
            margin-top: 10px; 
            display: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a href="data-sekolah.php" class="navbar-brand">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <span class="text-white">Edit Sekolah</span>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4">Edit Sekolah: <?php echo htmlspecialchars($sekolah['nama']); ?></h2>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- Left Column: Data Sekolah -->
                <div class="col-lg-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-building"></i> Data Sekolah
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">NPSN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="npsn" 
                                       value="<?php echo htmlspecialchars($sekolah['npsn']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" 
                                       value="<?php echo htmlspecialchars($sekolah['nama']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="alamat" rows="2" required><?php echo htmlspecialchars($sekolah['alamat']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                                <select class="form-select" name="kecamatan" required>
                                    <?php 
                                    $kecamatans = ['Lubuk Begalung', 'Kuranji', 'Padang Barat', 'Pauh', 'Lubuk Kilangan'];
                                    foreach ($kecamatans as $kec):
                                    ?>
                                        <option value="<?php echo $kec; ?>" 
                                            <?php echo $sekolah['kecamatan'] == $kec ? 'selected' : ''; ?>>
                                            <?php echo $kec; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- FOTO EXISTING -->
                            <div class="mb-3">
                                <label class="form-label">Foto Sekolah Saat Ini</label>
                                <?php if (!empty($sekolah['foto']) && file_exists('../uploads/sekolah/' . $sekolah['foto'])): ?>
                                    <div>
                                        <img src="../uploads/sekolah/<?php echo htmlspecialchars($sekolah['foto']); ?>" 
                                             class="foto-existing img-thumbnail"
                                             alt="Foto Sekolah">
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-secondary py-2 mb-2">
                                        <small><i class="bi bi-image"></i> Belum ada foto</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- UPLOAD FOTO BARU -->
                            <div class="mb-3">
                                <label class="form-label">Upload Foto Baru (opsional)</label>
                                <input type="file" class="form-control" name="foto" accept="image/*" 
                                       onchange="previewFoto(this)">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                                <img id="preview" class="preview-foto img-thumbnail">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Akreditasi</label>
                                    <select class="form-select" name="akreditasi">
                                        <?php foreach (['A', 'B', 'C'] as $akr): ?>
                                            <option value="<?php echo $akr; ?>" 
                                                <?php echo $sekolah['akreditasi'] == $akr ? 'selected' : ''; ?>>
                                                <?php echo $akr; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kuota Siswa</label>
                                    <input type="number" class="form-control" name="kuota" 
                                           value="<?php echo $sekolah['kuota']; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control" name="telepon" 
                                       value="<?php echo htmlspecialchars($sekolah['telepon']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Kepala Sekolah</label>
                                <input type="text" class="form-control" name="kepala_sekolah" 
                                       value="<?php echo htmlspecialchars($sekolah['kepala_sekolah']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Operator Sekolah</label>
                                <input type="text" class="form-control" name="operator" 
                                       value="<?php echo htmlspecialchars($sekolah['operator']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Sekolah</label>
                                <input type="email" class="form-control" name="email_sekolah" 
                                       value="<?php echo htmlspecialchars($sekolah['email_sekolah']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Lokasi Peta -->
                <div class="col-lg-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-map"></i> Lokasi di Peta
                        </div>
                        <div class="card-body">
                            <div id="map"></div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Klik pada peta atau drag marker untuk mengubah lokasi
                            </small>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                    <input type="number" step="0.000001" class="form-control" 
                                           id="latitude" name="latitude" 
                                           value="<?php echo $sekolah['latitude']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                    <input type="number" step="0.000001" class="form-control" 
                                           id="longitude" name="longitude" 
                                           value="<?php echo $sekolah['longitude']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="form-label">Radius Zonasi (meter)</label>
                                <input type="number" class="form-control" name="radius" 
                                       value="<?php echo $sekolah['radius']; ?>">
                                <small class="text-muted">Radius zona prioritas PPDB</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-save"></i> Update Data
                        </button>
                        <a href="data-sekolah.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <a href="hapus-sekolah.php?id=<?php echo $id; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Yakin ingin menghapus sekolah ini?')">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // ===================================
        // PREVIEW FOTO
        // ===================================
        function previewFoto(input) {
            const preview = document.getElementById('preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // ===================================
        // LEAFLET MAP
        // ===================================
        
        // Get initial coordinates from PHP
        const initialLat = <?php echo $sekolah['latitude']; ?>;
        const initialLng = <?php echo $sekolah['longitude']; ?>;
        const initialRadius = <?php echo $sekolah['radius']; ?>;
        
        // Initialize map
        const map = L.map('map').setView([initialLat, initialLng], 15);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);
        
        // Add draggable marker
        let marker = L.marker([initialLat, initialLng], {
            draggable: true,
            title: 'Drag untuk pindahkan'
        }).addTo(map);
        
        // Add radius circle
        let circle = L.circle([initialLat, initialLng], {
            color: '#0d6efd',
            fillColor: '#0d6efd',
            fillOpacity: 0.2,
            radius: initialRadius
        }).addTo(map);
        
        // Update coordinates when marker is dragged
        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat.toFixed(6);
            document.getElementById('longitude').value = pos.lng.toFixed(6);
            
            // Update circle position
            circle.setLatLng(pos);
        });
        
        // Click on map to set location
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
        });
        
        // Update marker and circle when coordinates input changes
        document.getElementById('latitude').addEventListener('change', updateMarker);
        document.getElementById('longitude').addEventListener('change', updateMarker);
        
        function updateMarker() {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const newPos = L.latLng(lat, lng);
                marker.setLatLng(newPos);
                circle.setLatLng(newPos);
                map.setView(newPos);
            }
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>